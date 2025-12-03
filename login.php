<?php
require_once 'db.php';
session_start();
if($_SERVER['REQUEST_METHOD']==='POST'){
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    $stmt = $conn->prepare("SELECT * FROM users WHERE username=? LIMIT 1");
    $stmt->bind_param("s",$username); $stmt->execute(); $res = $stmt->get_result();
    $u = $res->fetch_assoc();
    if($u && password_verify($password, $u['password'])){
        // login success
        $_SESSION['user_email'] = $u['email'];
        $_SESSION['user_id'] = $u['id'];
        header("Location: dashboard.php");
        exit;
    } else {
        header("Location: index.php?msg=" . urlencode("Invalid credentials"));
        exit;
    }
}
?>
