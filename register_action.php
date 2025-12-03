<?php
require_once 'db.php';
session_start();

if($_SERVER['REQUEST_METHOD']=='POST'){
    $username = $conn->real_escape_string($_POST['username']);
    $email = $conn->real_escape_string($_POST['email']);
    $password = password_hash($_POST['password'], PASSWORD_BCRYPT);

    // OTP generate
    $otp = rand(100000,999999);

    // Save user with pending status
    $conn->query("INSERT INTO users (username,email,password,otp,status) VALUES ('$username','$email','$password','$otp','pending')");

    // Send OTP via email
    $subject = "Wallet App OTP Verification";
    $message = "Hi $username,\n\nYour OTP code is: $otp\n\nPlease enter this to verify your account.";
    $headers = "From: no-reply@yourdomain.com";

    if(mail($email,$subject,$message,$headers)){
        $_SESSION['email_verify'] = $email;
        header("Location: verify_otp.php");
        exit;
    }else{
        echo "Failed to send OTP. Try again!";
    }
}
?>
