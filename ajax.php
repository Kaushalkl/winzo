<?php
require_once 'db.php';
session_start();
header('Content-Type: application/json');

// Check login
if(empty($_SESSION['user_email'])){
    echo json_encode(['success'=>false,'message'=>'Not logged in']);
    exit;
}

$email = $_SESSION['user_email'];

// Fetch user info
$stmt = $conn->prepare("SELECT * FROM users WHERE email=? LIMIT 1");
$stmt->bind_param("s", $email);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$stmt->close();

$action = $_GET['action'] ?? '';

if($action === 'add_bank' && $_SERVER['REQUEST_METHOD'] === 'POST'){
    $bank_name = trim($_POST['bank_name'] ?? '');
    $bank_ifsc = trim($_POST['bank_ifsc'] ?? '');
    $account_number = trim($_POST['account_number'] ?? '');
    $re_account_number = trim($_POST['re_account_number'] ?? '');
    $account_holder = trim($_POST['account_holder'] ?? '');

    // Validation
    if(!$bank_name || !$bank_ifsc || !$account_number || !$re_account_number || !$account_holder){
        echo json_encode(['success'=>false,'message'=>'All fields are required']);
        exit;
    }

    if($account_number !== $re_account_number){
        echo json_encode(['success'=>false,'message'=>'Account numbers do not match']);
        exit;
    }

    // Update bank details securely
    $stmt = $conn->prepare("UPDATE users SET bank_name=?, bank_ifsc=?, account_number=?, account_holder=? WHERE email=?");
    $stmt->bind_param("sssss", $bank_name, $bank_ifsc, $account_number, $account_holder, $email);

    if($stmt->execute()){
        echo json_encode(['success'=>true,'message'=>'Bank details updated successfully']);
    } else {
        echo json_encode(['success'=>false,'message'=>'Failed to update bank details']);
    }
    $stmt->close();
    exit;
}

// Future: Fetch transactions or wallet actions can go here
?>
