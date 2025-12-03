<?php
require_once 'db.php';
require_once 'functions.php';

// Start secure session
secure_session_start();

$msg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['email'], $_POST['csrf_token'])) {
    
    // CSRF token check
    if (!validateCSRFToken($_POST['csrf_token'])) {
        $msg = "Invalid request. Please refresh the page and try again.";
    } else {
        $email = sanitize_input($_POST['email']);

        // Check if email exists
        $stmt = $conn->prepare("SELECT id, name FROM users WHERE email=? LIMIT 1");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();
        $stmt->bind_result($user_id, $user_name);

        if ($stmt->num_rows > 0) {
            $stmt->fetch();

            // Generate OTP and expiry
            $otp = generateOTP();
            $expiry = date('Y-m-d H:i:s', strtotime('+10 minutes'));

            $stmt2 = $conn->prepare("UPDATE users SET otp=?, otp_expiry=? WHERE email=?");
            $stmt2->bind_param("sss", $otp, $expiry, $email);
            $stmt2->execute();
            $stmt2->close();

            // Send OTP email with type 'forgot' to indicate forgot password
            $email_sent = sendOTP($email, $otp, $user_name, 'forgot');

            if ($email_sent) {
                $_SESSION['reset_email'] = $email;
                header("Location: reset_password.php");
                exit;
            } else {
                $msg = "Failed to send OTP. Please try again later.";
            }

        } else {
            $msg = "Email not found.";
        }
        $stmt->close();
    }
}

// Generate new CSRF token for the form
$csrf_token = generateCSRFToken();
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>Ludo Wallet — Forgot Password</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">

  <!-- Font Awesome for icons -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

  <style>
  /* ===== Base ===== */
  *{margin:0;padding:0;box-sizing:border-box;}
  body{
      font-family:'Poppins',sans-serif;
      background:linear-gradient(135deg,#43cea2,#185a9d);
      display:flex;
      justify-content:center;
      align-items:center;
      min-height:100vh;
      overflow-x:hidden;
      transition:.3s ease;
  }
  .wrapper{
      width:100%;
      display:flex;
      justify-content:center;
      align-items:center;
      padding:15px;
  }
  .card{
      width:100%;
      max-width:400px;
      background:rgba(255,255,255,0.15);
      backdrop-filter:blur(15px);
      border-radius:20px;
      padding:40px 30px;
      text-align:center;
      box-shadow:0 8px 30px rgba(0,0,0,.25);
      animation:fadeIn .8s ease-in-out;
  }
  .form-title{
      margin-bottom:25px;
      font-size:2rem;
      font-weight:700;
      color:#fff;
  }

  .input-group{
      position:relative;
      margin-bottom:15px;
      width:100%;
  }
  .input-group i{
      position:absolute;
      left:15px;
      top:50%;
      transform:translateY(-50%);
      color:#555;
  }
  .form-control{
      width:100%;
      padding:14px 40px;
      border:none;
      outline:none;
      border-radius:12px;
      background:rgba(255,255,255,0.25);
      font-size:1rem;
      color:#000;
      transition:all .3s;
      box-shadow:inset 0 3px 6px rgba(0,0,0,.2);
  }
  .form-control:focus{
      background:rgba(255,255,255,0.35);
      transform:scale(1.02);
      box-shadow:0 0 8px rgba(0,120,255,.8);
  }

  .btn-custom{
      width:100%;
      padding:14px;
      border:none;
      border-radius:12px;
      background:linear-gradient(135deg,#ff416c,#ff4b2b);
      color:#fff;
      font-size:1.1rem;
      font-weight:600;
      cursor:pointer;
      transition:.3s;
  }
  .btn-custom:hover{transform:translateY(-2px);}

  .links{
      margin-top:15px;
      font-size:.9rem;
  }
  .links a{
      color:#fff;
      text-decoration:underline;
  }
  .links a:hover{color:#38f9d7;}

  .dark-mode{
      background:linear-gradient(135deg,#121212,#1c1c1c);
  }
  .dark-mode .card{background:rgba(30,30,30,.85);}
  .dark-mode .form-title{color:#ddd;}
  .dark-mode .form-control{background:rgba(255,255,255,.08);color:#fff;}
  .dark-mode .btn-custom{background:linear-gradient(135deg,#444,#666);}
  .dark-mode .links a{color:#ddd;}

  .dark-mode-toggle{
      position:fixed;
      bottom:20px;
      right:20px;
      padding:12px 18px;
      border:none;
      border-radius:30px;
      background:#ff416c;
      color:#fff;
      cursor:pointer;
      font-size:.95rem;
      box-shadow:0 5px 15px rgba(0,0,0,.25);
      transition:.3s;
  }
  .dark-mode-toggle:hover{
      background:#ff2b2b;
      transform:scale(1.05);
  }

  .alert{
      padding:10px 15px;
      border-radius:10px;
      margin-bottom:15px;
  }
  .alert-error{background:rgba(255,0,0,0.3);color:#000;}

  @keyframes fadeIn{
      from{opacity:0;transform:translateY(20px);}
      to{opacity:1;transform:translateY(0);}
  }

  @media (max-width:480px){
      .card{padding:30px 20px;}
      .form-title{font-size:1.6rem;}
  }
  </style>
</head>
<body>
<div class="wrapper">
  <div class="card">
    <h2 class="form-title">Forgot Password</h2>

    <?php if(!empty($msg)): ?>
      <div class="alert alert-error"><?php echo htmlspecialchars($msg); ?></div>
    <?php endif; ?>

    <form method="POST" action="">
      <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
      <div class="input-group">
        <i class="fa fa-envelope"></i>
        <input type="email" class="form-control" name="email" placeholder="Enter your registered email" required>
      </div>
      <button class="btn-custom">Send OTP</button>
    </form>

    <div class="links" style="margin-top:15px;">
      <a href="index.php"><i class="fa fa-arrow-left"></i> Back to Login</a>
    </div>
  </div>
</div>

<button class="dark-mode-toggle" onclick="toggleDarkMode()">🌙 Dark Mode</button>

<script>
function toggleDarkMode(){
    document.body.classList.toggle('dark-mode');
}
</script>
</body>
</html>
