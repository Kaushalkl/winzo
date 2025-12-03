<?php
require_once 'db.php';
//session_start();
$email = $_GET['email'] ?? null;
$msg = '';
if($_SERVER['REQUEST_METHOD']==='POST' && isset($_POST['password'])){
    $password = password_hash($_POST['password'], PASSWORD_BCRYPT);
    $stmt = $conn->prepare("UPDATE users SET password=?, otp=NULL, otp_expiry=NULL WHERE email=?");
    $stmt->bind_param("ss",$password,$_POST['email']); $stmt->execute();
    header("Location: index.php?msg=" . urlencode("Password updated. Please login."));
    exit;
}
?>
<!doctype html><html><head><meta charset="utf-8"><title>New Password</title>
<!--<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet"></head><body class="bg-light">-->
<div class="container py-4"><div class="col-md-6 mx-auto card p-3">
  <h4>Set New Password</h4>
  <form method="POST">
    <input type="hidden" name="email" value="<?php echo htmlspecialchars($email); ?>">
    <input type="password" name="password" class="form-control mb-2" placeholder="New password" required>
    <button class="btn btn-success">Reset Password</button>
  </form>
</div></div></body></html>
