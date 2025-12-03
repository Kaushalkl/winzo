<?php
session_start();
require_once 'db.php';
require_once 'functions.php';

// Redirect if logged in
if (!empty($_SESSION['user_email'])) {
    header("Location: dashboard.php");
    exit;
}

$errors = [];
$showOtp = !empty($_SESSION['pending_email']);

// ===============================
// ✅ Handle Registration & OTP
// ===============================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // ---- Registration Form ----
    if (isset($_POST['register'])) {
        $name = trim($_POST['name']);
        $username = trim($_POST['username']);
        $email = trim($_POST['email']);
        $phone = trim($_POST['phone']);
        $password = $_POST['password'];
        $retype_password = $_POST['retype_password'];

        // Validation
        if (!$name || !$username || !$email || !$password || !$retype_password) {
            $errors[] = "Please fill all required fields.";
        }
        if ($password !== $retype_password) {
            $errors[] = "Passwords do not match.";
        }

        // Duplicate check
        $stmt = $conn->prepare("SELECT id FROM users WHERE username=? OR email=? LIMIT 1");
        $stmt->bind_param("ss", $username, $email);
        $stmt->execute();
        $stmt->store_result();
        if ($stmt->num_rows > 0) $errors[] = "Username or Email already registered.";
        $stmt->close();

        // Insert user
        if (empty($errors)) {
            $hashed = password_hash($password, PASSWORD_BCRYPT);
            $otp = generateOTP();
            $expiry = date('Y-m-d H:i:s', strtotime('+10 minutes'));

            $stmt = $conn->prepare("INSERT INTO users (name, username, email, phone, password, otp, otp_expiry, status) VALUES (?, ?, ?, ?, ?, ?, ?, 'pending')");
            $stmt->bind_param("sssssss", $name, $username, $email, $phone, $hashed, $otp, $expiry);

            if ($stmt->execute()) {
                // ✅ Send OTP with type 'login' (registration/login)
                sendOTP($email, $otp, $name, 'login');

                $_SESSION['pending_email'] = $email;
                $showOtp = true;
            } else {
                $errors[] = "Database error: " . $conn->error;
            }
            $stmt->close();
        }
    }

    // ---- OTP Verification ----
    if (isset($_POST['verify_otp'])) {
        $otp_input = trim($_POST['otp']);
        $email = $_SESSION['pending_email'] ?? '';

        if (!$email) {
            $errors[] = "No pending verification found.";
        } else {
            $stmt = $conn->prepare("SELECT otp, otp_expiry, name FROM users WHERE email=? LIMIT 1");
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $stmt->bind_result($otp_db, $otp_expiry, $name);
            if ($stmt->fetch()) {
                if ($otp_input === $otp_db) {
                    if (new DateTime() <= new DateTime($otp_expiry)) {
                        $stmt->close();
                        $stmt = $conn->prepare("UPDATE users SET otp=NULL, otp_expiry=NULL, status='active' WHERE email=?");
                        $stmt->bind_param("s", $email);
                        $stmt->execute();
                        $stmt->close();

                        unset($_SESSION['pending_email']);
                        $_SESSION['user_email'] = $email;
                        header("Location: dashboard.php");
                        exit;
                    } else {
                        $errors[] = "OTP expired. Please resend.";
                    }
                } else {
                    $errors[] = "Invalid OTP.";
                }
            } else {
                $errors[] = "User not found.";
            }
            $stmt->close();
        }
    }

    // ---- Resend OTP ----
    if (isset($_POST['resend_otp'])) {
        $email = $_SESSION['pending_email'] ?? '';
        if (!$email) {
            $errors[] = "No pending verification found.";
        } else {
            $stmt = $conn->prepare("SELECT name, status FROM users WHERE email=? LIMIT 1");
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $stmt->bind_result($name, $status);
            if ($stmt->fetch()) {
                if ($status === 'active') {
                    $errors[] = "User already verified.";
                } else {
                    // Generate new OTP
                    $otp = generateOTP();
                    $expiry = date('Y-m-d H:i:s', strtotime('+10 minutes'));
                    $stmt->close();

                    // Update in database
                    $stmt = $conn->prepare("UPDATE users SET otp=?, otp_expiry=? WHERE email=?");
                    $stmt->bind_param("sss", $otp, $expiry, $email);
                    if ($stmt->execute()) {
                        // Send OTP with type 'login'
                        sendOTP($email, $otp, $name, 'login');
                        $errors[] = "New OTP sent to your email.";
                    } else {
                        $errors[] = "Database error while resending OTP.";
                    }
                    $stmt->close();
                }
            } else {
                $errors[] = "User not found.";
                $stmt->close();
            }
        }
        $showOtp = true;
    }
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Ludo Wallet — Register</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<style>
*{box-sizing:border-box;margin:0;padding:0;font-family:'Poppins',sans-serif;}
body{background:linear-gradient(135deg,#43cea2,#185a9d);display:flex;justify-content:center;align-items:center;min-height:100vh;transition:.3s ease;overflow-x:hidden;}
body.dark-mode{background:linear-gradient(135deg,#121212,#1c1c1c);}
.wrapper{width:100%;display:flex;justify-content:center;align-items:center;padding:15px;}
.card{width:100%;max-width:450px;background:rgba(255,255,255,0.15);backdrop-filter:blur(15px);border-radius:20px;padding:40px 30px;text-align:center;box-shadow:0 8px 30px rgba(0,0,0,.25);animation:fadeIn .8s ease-in-out;transition:.3s;}
.form-title{margin-bottom:25px;font-size:2rem;font-weight:700;color:#fff;}
.alert{padding:10px 15px;border-radius:10px;margin-bottom:15px;}
.alert-error{background:rgba(255,0,0,0.3);color:#000;}
.input-group{position:relative;width:100%;margin-bottom:12px;}
.input-group i{position:absolute;left:15px;top:50%;transform:translateY(-50%);color:#555;font-size:1rem;}
.form-control{width:100%;padding:14px 40px 14px 40px;border:none;outline:none;font-size:1rem;background:rgba(255,255,255,0.25);color:#000;transition: all 0.3s ease;box-shadow:inset 0 3px 6px rgba(0,0,0,.2);border-radius:12px;}
.form-control:focus{background:rgba(255,255,255,0.35);transform:scale(1.02);box-shadow:0 0 8px rgba(0,120,255,.8);}
.btn-custom{width:100%;padding:14px;margin-top:10px;border:none;border-radius:12px;background:linear-gradient(135deg,#ff416c,#ff4b2b);color:#fff;font-size:1.1rem;font-weight:600;cursor:pointer;transition:.3s;}
.btn-custom:hover{transform:translateY(-2px);}
.links{margin-top:15px;display:flex;justify-content:space-between;font-size:.9rem;}
.links a{color:#fff;text-decoration:underline;transition:.3s;}
.links a:hover{color:#38f9d7;}
.dark-mode .card{background:rgba(30,30,30,.85);}
.dark-mode .form-title,.dark-mode .links a{color:#ddd;}
.dark-mode .form-control{background:rgba(255,255,255,0.08);color:#fff;}
.dark-mode .btn-custom{background:linear-gradient(135deg,#444,#666);}
.dark-mode-toggle{position:fixed;bottom:20px;right:20px;padding:12px 18px;border:none;border-radius:30px;background:#ff416c;color:#fff;cursor:pointer;font-size:.95rem;box-shadow:0 5px 15px rgba(0,0,0,.25);transition:.3s;}
.dark-mode-toggle:hover{background:#ff2b2b;transform:scale(1.05);}
.password-group{position:relative;width:100%;}
.password-group .form-control{padding-right:45px;}
.password-group .btn-eye{position:absolute;right:40px;top:50%;transform:translateY(-50%);border:none;background:transparent;cursor:pointer;color:#555;font-size:1.1rem;padding:5px;transition: all 0.3s ease;}
.password-group .btn-eye:hover{color:#000;}
.password-group.show-password .btn-eye i{transform: rotateY(180deg);color: #ff416c;transition: all 0.3s ease;}
.password-group.show-password .form-control{background:rgba(255,255,255,0.35);color:#000;}
.dark-mode .password-group.show-password .form-control{background:rgba(255,255,255,0.12);color:#fff;}
.otp-boxes{display:flex;justify-content:space-between;margin-bottom:15px;}
.otp-boxes input{width:45px;height:50px;border-radius:10px;border:none;outline:none;font-size:1.6rem;text-align:center;background: rgba(255,255,255,0.2);color:#000;transition:0.2s;}
.otp-boxes input:focus{background: rgba(255,255,255,0.35);}
@keyframes fadeIn{from{opacity:0;transform:translateY(20px);}to{opacity:1;transform:translateY(0);}}

/* Responsive */
@media (max-width:480px){
.password-group .btn-eye{font-size:1rem;right:40px;padding:4px;}
.password-group .form-control{padding-right:40px;font-size:0.95rem;}
.card{padding:30px 20px;}
.form-title{font-size:1.6rem;}
}
</style>
</head>
<body>
<div class="wrapper">
<div class="card">
<h2 class="form-title"><?= $showOtp ? "Verify OTP" : "Create Account" ?></h2>

<?php foreach($errors as $e): ?>
<div class="alert alert-error"><?= htmlspecialchars($e) ?></div>
<?php endforeach; ?>

<?php if(!$showOtp): ?>
<form method="POST">
  <div class="input-group">
    <i class="fa fa-user"></i>
    <input type="text" name="name" class="form-control" placeholder="Full Name" required>
  </div>
  <div class="input-group">
    <i class="fa fa-user-circle"></i>
    <input type="text" name="username" class="form-control" placeholder="Username" required>
  </div>
  <div class="input-group">
    <i class="fa fa-envelope"></i>
    <input type="email" name="email" class="form-control" placeholder="Email" required>
  </div>
  <div class="input-group">
    <i class="fa fa-phone"></i>
    <input type="text" name="phone" class="form-control" placeholder="Phone">
  </div>
  <div class="input-group password-group">
    <i class="fa fa-lock"></i>
    <input type="password" id="password" name="password" class="form-control" placeholder="Password" required>
    <button type="button" class="btn-eye" onclick="togglePassword('password', this)"><i class="fa fa-eye"></i></button>
  </div>
  <div class="input-group password-group">
    <i class="fa fa-lock"></i>
    <input type="password" id="retype_password" name="retype_password" class="form-control" placeholder="Retype Password" required>
    <button type="button" class="btn-eye" onclick="togglePassword('retype_password', this)"><i class="fa fa-eye"></i></button>
  </div>
  <button name="register" class="btn-custom">Send OTP & Register</button>
</form>
<div class="links">
<a href="index.php"><i class="fa fa-arrow-left"></i> Back to Login</a>
</div>

<?php else: ?>
<form method="POST" style="text-align:center;">
<div class="otp-boxes">
  <?php for($i=0;$i<6;$i++): ?>
    <input type="text" maxlength="1" name="otp_char_<?=$i?>" pattern="[0-9]" required oninput="autoTab(this, <?=$i?>)">
  <?php endfor; ?>
</div>
<input type="hidden" name="otp" id="otp-combined">
<button name="verify_otp" class="btn-custom">Verify OTP</button>
<button name="resend_otp" class="btn-custom" style="margin-top:10px;background:#38f9d7;">Resend OTP</button>
</form>
<div class="links">
<a href="index.php"><i class="fa fa-arrow-left"></i> Back to Login</a>
</div>
<?php endif; ?>

</div>
</div>

<button class="dark-mode-toggle" onclick="toggleDarkMode()">🌙 Dark Mode</button>

<script>
function toggleDarkMode(){ document.body.classList.toggle('dark-mode'); }
function togglePassword(id, btn){
    let input=document.getElementById(id);
    let icon=btn.querySelector('i');
    let group=btn.closest('.password-group');
    if(input.type==='password'){ input.type='text'; icon.classList.remove('fa-eye'); icon.classList.add('fa-eye-slash'); group.classList.add('show-password'); }
    else{ input.type='password'; icon.classList.remove('fa-eye-slash'); icon.classList.add('fa-eye'); group.classList.remove('show-password'); }
}
function autoTab(ele,index){
    if(ele.value.length==1 && index<5){ ele.nextElementSibling.focus(); }
    let otp=''; document.querySelectorAll('.otp-boxes input').forEach(i=>otp+=i.value);
    document.getElementById('otp-combined').value=otp;
}
</script>
</body>
</html>
