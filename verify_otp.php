<?php
require_once 'db.php';
require_once 'functions.php';
session_start();
if(empty($_SESSION['pending_email'])) {
    header("Location: register.php"); exit;
}
$email = $_SESSION['pending_email'];
$msg = '';

if($_SERVER['REQUEST_METHOD']==='POST' && isset($_POST['otp'])) {
    $otp = trim($_POST['otp']);
    $stmt = $conn->prepare("SELECT id, otp, otp_expiry FROM users WHERE email=? LIMIT 1");
    $stmt->bind_param("s",$email);
    $stmt->execute();
    $res = $stmt->get_result();
    $u = $res->fetch_assoc();
    if($u && $u['otp']==$otp && strtotime($u['otp_expiry']) > time()){
        $stmt2 = $conn->prepare("UPDATE users SET otp=NULL, otp_expiry=NULL WHERE id=?");
        $stmt2->bind_param("i",$u['id']);
        $stmt2->execute();
        unset($_SESSION['pending_email']);
        header("Location:set_pin.php?email=".urlencode($email)); exit;
    } else { 
        $msg = "Invalid or expired OTP."; 
    }
}
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>Wallet App — Verify OTP</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">

  <!-- Font Awesome -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

  <style>
  /* ===== Base ===== */
  *{box-sizing:border-box;margin:0;padding:0;}
  body{
      font-family:'Poppins',sans-serif;
      background:linear-gradient(135deg,#43cea2,#185a9d);
      display:flex;justify-content:center;align-items:center;
      min-height:100vh;padding:15px;transition:.3s;
  }
  body.dark-mode{background:linear-gradient(135deg,#121212,#1c1c1c);color:#fff;}
  .app-wrapper{width:100%;display:flex;justify-content:center;align-items:center;}
  .login-card{
      width:100%;max-width:420px;
      background:rgba(255,255,255,0.15);
      backdrop-filter:blur(15px);
      border-radius:20px;
      padding:40px 30px;
      text-align:center;
      box-shadow:0 8px 30px rgba(0,0,0,.25);
      animation:fadeIn .8s ease-in-out;
  }
  .form-title{
      margin-bottom:20px;
      font-size:1.9rem;
      font-weight:700;
      color:#fff;
  }

  /* ===== Alerts ===== */
  .alert{
      background:rgba(255,0,0,0.3);
      color:#000;
      padding:10px 15px;
      border-radius:10px;
      margin-bottom:15px;
      text-align:center;
  }
  body.dark-mode .alert{background:rgba(255,0,0,0.25);color:#fff;}

  /* ===== OTP Boxes ===== */
  .otp-info{margin-bottom:20px;color:#fff;font-size:1rem;}
  .otp-boxes{
      display:flex;justify-content:space-between;
      margin-bottom:25px;width:100%;gap:10px;
  }
  .otp-boxes input{
      flex:1;width:50px;height:55px;
      border-radius:12px;border:none;outline:none;
      font-size:1.6rem;text-align:center;
      background:rgba(255,255,255,0.25);color:#000;
      transition:.2s;box-shadow:inset 0 3px 6px rgba(0,0,0,.2);
  }
  .otp-boxes input:focus{
      background:rgba(255,255,255,0.35);
      box-shadow:0 0 8px #38f9d7;
  }
  body.dark-mode .otp-boxes input{background:rgba(255,255,255,0.08);color:#fff;}

  /* ===== Buttons ===== */
  button{
      width:100%;padding:14px;margin-top:10px;
      font-size:1.1rem;border:none;border-radius:12px;
      background:linear-gradient(135deg,#ff416c,#ff4b2b);
      color:#fff;font-weight:600;cursor:pointer;
      transition:.3s;
  }
  button:hover{transform:translateY(-2px);}
  body.dark-mode button{background:linear-gradient(135deg,#444,#666);}

  /* ===== Dark Mode Toggle ===== */
  .dark-mode-toggle{
      position:fixed;bottom:20px;right:20px;
      padding:12px 18px;border:none;border-radius:30px;
      background:#ff416c;color:#fff;cursor:pointer;
      font-size:.95rem;box-shadow:0 5px 15px rgba(0,0,0,.25);
      transition:.3s;
  }
  .dark-mode-toggle:hover{background:#ff2b2b;transform:scale(1.05);}

  /* ===== Animations ===== */
  @keyframes fadeIn{from{opacity:0;transform:translateY(20px);}to{opacity:1;transform:translateY(0);}}

  /* ===== Mobile ===== */
  @media(max-width:480px){
      .login-card{padding:30px 20px;}
      .form-title{font-size:1.6rem;}
      .otp-boxes input{width:40px;height:45px;font-size:1.4rem;}
      button{padding:12px;font-size:1rem;}
  }
  </style>
</head>
<body>
  <div class="app-wrapper">
    <div class="login-card">
      <h2 class="form-title">Verify OTP</h2>

      <?php if($msg): ?>
        <div class="alert"><i class="fa fa-exclamation-triangle"></i> <?= htmlspecialchars($msg) ?></div>
      <?php endif; ?>

      <div class="otp-info">OTP sent to <strong><?= htmlspecialchars($email) ?></strong></div>

      <form method="POST" style="width:100%;">
        <div class="otp-boxes">
          <?php for($i=0;$i<6;$i++): ?>
            <input type="text" maxlength="1" pattern="[0-9]" required oninput="moveNext(this, <?= $i ?>)">
          <?php endfor; ?>
        </div>
        <input type="hidden" name="otp" id="otp-combined">
        <button type="submit">Verify OTP</button>
      </form>

      <div style="margin-top:15px;">
        <a href="forgot_password.php" style="color:#fff;text-decoration:underline;">← Back to Forgot Password</a>
      </div>
    </div>
  </div>

  <button class="dark-mode-toggle" onclick="document.body.classList.toggle('dark-mode')">🌙 Dark Mode</button>

  <script>
  function moveNext(ele,index){
      ele.value = ele.value.replace(/[^0-9]/g,'');
      let boxes = document.querySelectorAll('.otp-boxes input');
      if(ele.value.length==1 && index<boxes.length-1){ boxes[index+1].focus(); }
      let otp=''; boxes.forEach(b=>otp+=b.value);
      document.getElementById('otp-combined').value=otp;
  }
  </script>
</body>
</html>
