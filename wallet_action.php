<?php
require_once 'db.php';
require_once 'functions.php';
require_once 'config.php';
secure_session_start();
header('Content-Type: application/json');

// ------------------ SESSION CHECK ------------------
if (empty($_SESSION['user_email'])) {
    echo json_encode(['success'=>false,'message'=>'Not logged in']);
    exit;
}

// Get user ID
if (empty($_SESSION['user_id'])) {
    $email = $_SESSION['user_email'];
    $userData = $conn->query("SELECT id FROM users WHERE email='".$conn->real_escape_string($email)."'")->fetch_assoc();
    if (!$userData) {
        echo json_encode(['success'=>false,'message'=>'User not found']);
        exit;
    }
    $_SESSION['user_id'] = $userData['id'];
}

$uid = intval($_SESSION['user_id']);
$action = $_GET['action'] ?? '';

// ------------------ HELPER ------------------
function json_err($msg){
    echo json_encode(['success'=>false,'message'=>$msg]);
    exit;
}

function fetchIfscDetails($ifsc){
    $ifsc = strtoupper(trim($ifsc));
    $url = "https://ifsc.razorpay.com/".urlencode($ifsc);
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 5,
        CURLOPT_FAILONERROR => false
    ]);
    $resp = curl_exec($ch);
    $http = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    if ($http !== 200 || !$resp) return false;
    $data = json_decode($resp, true);
    return is_array($data) && !empty($data) ? $data : false;
}

// ------------------ FETCH USER ------------------
function fetchUser($uid, $conn){
    $stmt = $conn->prepare("SELECT * FROM users WHERE id = ? LIMIT 1");
    $stmt->bind_param("i", $uid);
    $stmt->execute();
    $user = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    return $user;
}

$user = fetchUser($uid, $conn);
if (!$user) json_err('User not found');

// ------------------ ACTIONS ------------------
switch($action){

    // -------- CREATE RAZORPAY ORDER --------
    case 'create_recharge_order':
        $amount = floatval($_POST['amount'] ?? 0);
        if ($amount < MIN_RECHARGE || $amount > MAX_RECHARGE) {
            json_err("Amount must be between ".MIN_RECHARGE." and ".MAX_RECHARGE);
        }

        require_once 'razorpay-php/Razorpay.php';
        $api = new Razorpay\Api\Api(RAZORPAY_KEY, RAZORPAY_SECRET);

        try {
            $orderData = [
                'receipt' => 'rcg_'.$uid.'_'.time(),
                'amount' => $amount*100, // paise
                'currency' => 'INR',
                'payment_capture' => 1
            ];
            $razorpayOrder = $api->order->create($orderData);
            echo json_encode([
                'success'=>true,
                'key_id'=>RAZORPAY_KEY,
                'amount'=>$amount,
                'currency'=>'INR',
                'order_id'=>$razorpayOrder['id'],
                'name'=>$user['name'],
                'description'=>'Wallet Recharge',
                'prefill'=>['name'=>$user['name'],'email'=>$user['email'],'contact'=>$user['phone']??'']
            ]);
        } catch(Exception $e){
            json_err('Razorpay error: '.$e->getMessage());
        }
        exit;

    // -------- VERIFY PAYMENT --------
    case 'verify_payment':
        $payment_id = $_POST['razorpay_payment_id'] ?? '';
        $order_id = $_POST['razorpay_order_id'] ?? '';
        $signature = $_POST['razorpay_signature'] ?? '';

        require_once 'razorpay-php/Razorpay.php';
        $api = new Razorpay\Api\Api(RAZORPAY_KEY, RAZORPAY_SECRET);

        try {
            $attributes = [
                'razorpay_order_id' => $order_id,
                'razorpay_payment_id' => $payment_id,
                'razorpay_signature' => $signature
            ];
            $api->utility->verifyPaymentSignature($attributes);

            // Fetch amount from order
            $order = $api->order->fetch($order_id);
            $amount = $order['amount']/100;

            // Update wallet balance
            $conn->query("UPDATE users SET wallet_balance = wallet_balance + $amount WHERE id = $uid");

            // Insert transaction
            $remark = 'Wallet Recharge';
            $type = 'credit';
            $stmt = $conn->prepare("INSERT INTO transactions (user_id,type,amount,remark,created_at,razorpay_order_id) VALUES (?,?,?,?,NOW(),?)");
            $stmt->bind_param("isdss", $uid, $type, $amount, $remark, $order_id);
            $stmt->execute();
            $stmt->close();

            // Fetch updated balance live
            $userUpdated = fetchUser($uid, $conn);
            $new_balance = $userUpdated['wallet_balance'];

            // Send email notification with correct balance
            sendTransactionEmail($userUpdated['email'], $userUpdated['name'], $type, $amount, $new_balance, $payment_id);

            echo json_encode(['success'=>true,'message'=>'Recharge successful','new_balance'=>$new_balance]);
        } catch(Exception $e){
            json_err('Payment verification failed: '.$e->getMessage());
        }
        exit;

    // -------- WITHDRAW --------
    case 'withdraw':
        $amount = floatval($_POST['amount'] ?? 0);

        if ($amount < 10) json_err('Minimum withdrawal amount is ₹10');
        if ($amount <= 0) json_err('Enter a valid amount');

        $user = fetchUser($uid, $conn); // refresh balance
        if ($user['wallet_balance'] < $amount) json_err('Insufficient balance');
        if (empty($user['bank_name']) || empty($user['bank_ifsc']) || empty($user['account_number'])) {
            json_err('Add bank details before withdrawing');
        }

        try {
            $conn->begin_transaction();

            $stmt = $conn->prepare("UPDATE users SET wallet_balance = wallet_balance - ? WHERE id = ?");
            $stmt->bind_param("di", $amount, $uid);
            $stmt->execute();
            $stmt->close();

            $remark = 'Withdraw to bank';
            $type = 'debit';
            $stmt2 = $conn->prepare("INSERT INTO transactions (user_id,type,amount,remark,created_at) VALUES (?,?,?,?,NOW())");
            $stmt2->bind_param("isds", $uid, $type, $amount, $remark);
            $stmt2->execute();
            $stmt2->close();

            $conn->commit();

            $userUpdated = fetchUser($uid, $conn);
            $new_bal = $userUpdated['wallet_balance'];

            sendTransactionEmail($userUpdated['email'], $userUpdated['name'], $type, $amount, $new_bal, generateRandomString(10));

            echo json_encode([
                'success' => true,
                'message' => "Withdrawn ₹" . number_format($amount, 2),
                'new_balance' => $new_bal
            ]);
        } catch (Exception $e) {
            $conn->rollback();
            json_err("Withdrawal failed: " . $e->getMessage());
        }
        exit;

    // -------- ADD/UPDATE BANK --------
    case 'add_bank':
        $bank_name = trim($_POST['bank_name'] ?? '');
        $bank_ifsc = strtoupper(trim($_POST['bank_ifsc'] ?? ''));
        $account_number = trim($_POST['account_number'] ?? '');
        $re_account_number = trim($_POST['re_account_number'] ?? '');
        $account_holder = trim($_POST['account_holder'] ?? '');

        if (!$bank_name || !$bank_ifsc || !$account_number || !$re_account_number || !$account_holder) {
            json_err('All bank fields are required');
        }
        if ($account_number !== $re_account_number) json_err('Account numbers do not match');
        if (!validateIFSC($bank_ifsc)) json_err('Invalid IFSC format');

        $ifscData = fetchIfscDetails($bank_ifsc);
        $fetchedBankName = $ifscData['BANK'] ?? $bank_name;
        $branch = $ifscData['BRANCH'] ?? '';

        $stmt = $conn->prepare("UPDATE users SET bank_name=?, bank_ifsc=?, account_number=?, account_holder=? WHERE id=?");
        $stmt->bind_param("ssssi", $fetchedBankName, $bank_ifsc, $account_number, $account_holder, $uid);
        if (!$stmt->execute()) json_err("Failed to save bank details");
        $stmt->close();

        echo json_encode(['success'=>true,'message'=>'Bank details saved','bank'=>['bank_name'=>$fetchedBankName,'branch'=>$branch,'ifsc'=>$bank_ifsc]]);
        exit;

    // -------- FETCH BALANCE --------
    case 'balance':
        $userUpdated = fetchUser($uid, $conn);
        echo json_encode(['balance'=>number_format($userUpdated['wallet_balance'],2)]);
        exit;

    // -------- FETCH TRANSACTIONS --------
    case 'transactions':
        $txRes = $conn->query("SELECT type, amount, remark, created_at FROM transactions WHERE user_id=$uid ORDER BY id DESC LIMIT 50");
        $transactions = [];
        while($tx = $txRes->fetch_assoc()) $transactions[] = $tx;
        echo json_encode(['transactions'=>$transactions]);
        exit;

    default:
        json_err('Unknown action');
}
