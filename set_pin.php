<?php
require_once 'db.php';
session_start();
$email = $_GET['email'] ?? null;
if(!$email) { header("Location:index.php"); exit; }
$msg='';
if($_SERVER['REQUEST_METHOD']==='POST' && isset($_POST['pin'])){
    $pin = trim($_POST['pin']);
    if(!preg_match('/^\d{4,6}$/',$pin)) $msg = "PIN must be 4-6 digits.";
    else {
        $stmt = $conn->prepare("UPDATE users SET pin=? WHERE email=?");
        $stmt->bind_param("ss",$pin,$email);
        if($stmt->execute()){
            header("Location:index.php?msg=" . urlencode("Account created, please login."));
            exit;
        } else $msg = "DB error.";
    }
}
?>
<!doctype html><html><head><meta charset="utf-8"><title>Set PIN</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head><body class="bg-light">
<div class="container py-5">
  <div class="col-md-5 mx-auto card p-4">
    <h4>Set Wallet PIN</h4>
    <?php if($msg) echo "<div class='alert alert-danger'>$msg</div>"; ?>
    <form method="POST">
      <input name="pin" class="form-control mb-2" placeholder="4-6 digit PIN" required>
      <button class="btn btn-success">Save PIN</button>
    </form>
  </div>
</div>
</body></html>
