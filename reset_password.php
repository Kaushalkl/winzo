<?php
require_once 'db.php';
require_once 'functions.php';
secure_session_start();

if(empty($_SESSION['reset_email'])){
    header("Location: forgot_password.php");
    exit;
}

$msg = '';
$email = $_SESSION['reset_email'];
$showOtp = true; // always showing OTP input on this page

if($_SERVER['REQUEST_METHOD']==='POST'){

    // ---- Resend OTP ----
    if(isset($_POST['resend_otp'])){
        $stmt = $conn->prepare("SELECT name FROM users WHERE email=? LIMIT 1");
        $stmt->bind_param("s",$email);
        $stmt->execute();
        $stmt->bind_result($user_name);
        if($stmt->fetch()){
            $otp = generateOTP();
            $expiry = date('Y-m-d H:i:s', strtotime('+10 minutes'));
            $stmt->close();

            $stmt2 = $conn->prepare("UPDATE users SET otp=?, otp_expiry=? WHERE email=?");
            $stmt2->bind_param("sss",$otp,$expiry,$email);
            $stmt2->execute();
            $stmt2->close();

            sendOTP($email,$otp,$user_name,'forgot');
            $msg = "A new OTP has been sent to your email.";
        }else{
            $msg = "User not found!";
        }
    }

    // ---- Verify OTP and update password ----
    if(isset($_POST['verify_otp'])){
        $otp = trim($_POST['otp']);
        $new = $_POST['new_password'];
        $confirm = $_POST['confirm_password'];

        $stmt = $conn->prepare("SELECT otp, otp_expiry FROM users WHERE email=? LIMIT 1");
        $stmt->bind_param("s",$email);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();

        if(!$user){
            $msg = "User not found!";
        } elseif($otp !== $user['otp']){
            $msg = "Invalid OTP!";
        } elseif(strtotime($user['otp_expiry']) < time()){
            $msg = "OTP expired!";
        } elseif($new !== $confirm){
            $msg = "Passwords do not match!";
        } elseif(strlen($new) < 6){
            $msg = "Password must be at least 6 characters!";
        } else {
            $hashed = password_hash($new,PASSWORD_BCRYPT);
            $stmt2 = $conn->prepare("UPDATE users SET password=?, otp=NULL, otp_expiry=NULL WHERE email=?");
            $stmt2->bind_param("ss",$hashed,$email);
            if($stmt2->execute()){
                unset($_SESSION['reset_email']);
                header("Location:index.php?msg=Password reset successful! Please login.");
                exit;
            } else {
                $msg = "Something went wrong, try again!";
            }
        }
    }
}
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<title>Reset Password</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<style>
*{margin:0;padding:0;box-sizing:border-box;}
body{font-family:'Poppins',sans-serif;background:linear-gradient(135deg,#43cea2,#185a9d);display:flex;justify-content:center;align-items:center;min-height:100vh;overflow-x:hidden;transition:.3s ease;}
body.dark-mode{background:linear-gradient(135deg,#121212,#1c1c1c);}
.wrapper{width:100%;display:flex;justify-content:center;align-items:center;padding:15px;}
.card{width:100%;max-width:400px;background:rgba(255,255,255,0.15);backdrop-filter:blur(15px);border-radius:20px;padding:40px 30px;text-align:center;box-shadow:0 8px 30px rgba(0,0,0,.25);animation:fadeIn .8s ease-in-out;}
.form-title{margin-bottom:25px;font-size:2rem;font-weight:700;color:#fff;}
.input-group{position:relative;margin-bottom:15px;width:100%;}
.input-group i{position:absolute;left:15px;top:50%;transform:translateY(-50%);color:#555;}
.form-control{width:100%;padding:14px 45px 14px 40px;border:none;outline:none;border-radius:12px;background:rgba(255,255,255,0.25);font-size:1rem;color:#000;transition:all .3s;box-shadow:inset 0 3px 6px rgba(0,0,0,.2);}
.form-control:focus{background:rgba(255,255,255,0.35);transform:scale(1.02);box-shadow:0 0 8px rgba(0,120,255,.8);}
.btn-eye{position:absolute;right:40px;top:50%;transform:translateY(-50%);border:none;background:transparent;cursor:pointer;color:#555;font-size:1.1rem;padding:5px;}
.btn-eye:hover{color:#000;}
.password-group.show-password .btn-eye i{color:#ff416c;}
.btn-custom{width:100%;padding:14px;border:none;border-radius:12px;background:linear-gradient(135deg,#ff416c,#ff4b2b);color:#fff;font-size:1.1rem;font-weight:600;cursor:pointer;transition:.3s;margin-top:10px;}
.btn-custom:hover{transform:translateY(-2px);}
.links{margin-top:15px;font-size:.9rem;}
.links a{color:#fff;text-decoration:underline;}
.links a:hover{color:#38f9d7;}
.dark-mode .card{background:rgba(30,30,30,.85);}
.dark-mode .form-title{color:#ddd;}
.dark-mode .form-control{background:rgba(255,255,255,.08);color:#fff;}
.dark-mode .btn-custom{background:linear-gradient(135deg,#444,#666);}
.dark-mode .links a{color:#ddd;}
.dark-mode-toggle{position:fixed;bottom:20px;right:20px;padding:12px 18px;border:none;border-radius:30px;background:#ff416c;color:#fff;cursor:pointer;font-size:.95rem;box-shadow:0 5px 15px rgba(0,0,0,.25);transition:.3s;}
.dark-mode-toggle:hover{background:#ff2b2b;transform:scale(1.05);}
.alert{padding:10px 15px;border-radius:10px;margin-bottom:15px;}
.alert-error{background:rgba(255,0,0,0.3);color:#000;}
.otp-boxes{display:flex;justify-content:space-between;margin-bottom:15px;}
.otp-boxes input{width:45px;height:50px;border-radius:10px;border:none;outline:none;font-size:1.6rem;text-align:center;background: rgba(255,255,255,0.2);color:#000;transition:0.2s;}
.otp-boxes input:focus{background: rgba(255,255,255,0.35);}
@keyframes fadeIn{from{opacity:0;transform:translateY(20px);}to{opacity:1;transform:translateY(0);}}
</style>
</head>
<body>
<div class="wrapper">
<div class="card">
<h2 class="form-title">Reset Password</h2>

<?php if(!empty($msg)): ?>
<div class="alert alert-error"><?= htmlspecialchars($msg) ?></div>
<?php endif; ?>

<form method="POST" style="text-align:center;">
  <div class="otp-boxes">
    <?php for($i=0;$i<6;$i++): ?>
      <input type="text" maxlength="1" name="otp_char_<?=$i?>" pattern="[0-9]" required oninput="autoTab(this,<?=$i?>)">
    <?php endfor; ?>
  </div>
  <input type="hidden" name="otp" id="otp-combined">

  <div class="input-group password-group">
    <i class="fa fa-lock"></i>
    <input type="password" class="form-control" id="new_password" name="new_password" placeholder="New Password" required>
    <button type="button" class="btn-eye" onclick="togglePassword('new_password',this)"><i class="fa fa-eye"></i></button>
  </div>

  <div class="input-group password-group">
    <i class="fa fa-lock"></i>
    <input type="password" class="form-control" id="confirm_password" name="confirm_password" placeholder="Confirm Password" required>
    <button type="button" class="btn-eye" onclick="togglePassword('confirm_password',this)"><i class="fa fa-eye"></i></button>
  </div>

  <button name="verify_otp" class="btn-custom">Update Password</button>
</form>

<form method="POST">
  <button name="resend_otp" class="btn-custom" style="background:#38f9d7;">Resend OTP</button>
</form>

<div class="links" style="margin-top:15px;">
<a href="index.php"><i class="fa fa-arrow-left"></i> Back to Login</a>
</div>
</div>
</div>

<button class="dark-mode-toggle" onclick="toggleDarkMode()">🌙 Dark Mode</button>

<script>
function toggleDarkMode(){document.body.classList.toggle('dark-mode');}

function togglePassword(id,btn){
  let input=document.getElementById(id);
  let icon=btn.querySelector('i');
  if(input.type==='password'){
      input.type='text';icon.classList.remove('fa-eye');icon.classList.add('fa-eye-slash');
      btn.closest('.password-group').classList.add('show-password');
  }else{
      input.type='password';icon.classList.remove('fa-eye-slash');icon.classList.add('fa-eye');
      btn.closest('.password-group').classList.remove('show-password');
  }
}

function autoTab(ele,index){
    if(ele.value.length==1 && index<5){ ele.nextElementSibling.focus(); }
    let otp=''; document.querySelectorAll('.otp-boxes input').forEach(i=>otp+=i.value);
    document.getElementById('otp-combined').value=otp;
}
</script>
</body>
</html>
