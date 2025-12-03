    <?php
    require_once 'config.php';
    require_once 'db_connect.php';

    // Razorpay library include karein
    require_once 'razorpay-php/Razorpay.php';

    use Razorpay\Api\Api;

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $amount = $_POST['amount'] * 100; // Razorpay expects amount in paise
        
        // Input validation
        if ($amount < 8000) { // Minimum ₹80
            echo json_encode(['success' => false, 'message' => 'Minimum recharge amount is ₹80']);
            exit;
        }
        try {
            $api = new Api(RAZORPAY_KEY_ID, RAZORPAY_KEY_SECRET);
            
            $orderData = [
                'receipt' => 'rcpt_' . time(),
                'amount' => $amount,
                'currency' => 'INR',
                'payment_capture' => 1
            ];
            
            $razorpayOrder = $api->order->create($orderData);
            
            $response = [
                'success' => true,
                'order_id' => $razorpayOrder['id'],
                'amount' => $amount,
                'key_id' => RAZORPAY_KEY_ID,
                'name' => SITE_NAME,
                'prefill' => [
                    'name' => 'Customer Name', // Yahan user ka name aayega
                    'email' => 'customer@email.com', // Yahan user ka email aayega
                ]
            ];
            
            echo json_encode($response);
            
        } catch (Exception $e) {
            error_log("Razorpay Error: " . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Payment gateway error. Please try again.']);
        }
    }
    ?>