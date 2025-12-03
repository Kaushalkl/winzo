<?php
require_once 'db.php';
session_start();

// Redirect if already logged in
if (!empty($_SESSION['user_email'])) {
    header("Location: dashboard.php");
    exit;
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $login_input = $conn->real_escape_string($_POST['login_input']); // email or username
    $password = $_POST['password'];

    // Query: check email OR username
    $stmt = $conn->prepare("SELECT * FROM users WHERE (email=? OR username=?) AND status='active' LIMIT 1");
    $stmt->bind_param("ss", $login_input, $login_input);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_email'] = $user['email']; // save email in session
        header("Location: dashboard.php");
        exit;
    } else {
        $error = "Invalid User Name or Email !";
    }
}
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<title>Ludo Wallet  — Login</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<!-- Font Awesome for icons -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

<style>
/* ===== Base & Reset ===== */
*{box-sizing:border-box;margin:0;padding:0;}
body{
    font-family:'Poppins',sans-serif;
    background:linear-gradient(135deg,#43cea2,#185a9d);
    display:flex;
    justify-content:center;
    align-items:center;
    min-height:100vh;
    transition:.3s ease;
    overflow-x:hidden;
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

/* ===== Inputs & Icons ===== */
.input-group{
    position:relative;
    width:100%;
    margin-bottom:12px;
}
.input-group i{
    position:absolute;
    left:15px;
    top:50%;
    transform:translateY(-50%);
    color:#555;
    font-size:1rem;
}
.form-control{
    width:100%;
    padding:14px 40px 14px 40px;
    border:none;
    outline:none;
    font-size:1rem;
    background:rgba(255,255,255,0.25);
    color:#000;
    transition: all 0.3s ease;
    box-shadow:inset 0 3px 6px rgba(0,0,0,.2);
    border-radius:12px;
}
.form-control:focus{
    background:rgba(255,255,255,0.35);
    transform:scale(1.02);
    box-shadow:0 0 8px rgba(0,120,255,.8);
}

/* ===== Buttons ===== */
.btn-custom{
    width:100%;
    padding:14px;
    margin-top:10px;
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

/* ===== Links ===== */
.links{
    margin-top:15px;
    display:flex;
    justify-content:space-between;
    font-size:.9rem;
}
.links a{
    color:#fff;
    text-decoration:underline;
    transition:.3s;
}
.links a:hover{color:#38f9d7;}

/* ===== Dark Mode ===== */
.dark-mode{
    background:linear-gradient(135deg,#121212,#1c1c1c);
}
.dark-mode .card{background:rgba(30,30,30,.85);}
.dark-mode .form-title,
.dark-mode .links a{color:#ddd;}
.dark-mode .form-control{background:rgba(255,255,255,.08);color:#fff;}
.dark-mode .btn-custom{background:linear-gradient(135deg,#444,#666);}

/* ===== Dark Mode Toggle Button ===== */
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

/* ===== Alerts ===== */
.alert{
    padding:10px 15px;
    border-radius:10px;
    margin-bottom:15px;
}
.alert-error{
    background:rgba(255,0,0,0.3);
    color:#000;
}

/* ===== Password Eye Button ===== */
.password-group{
    position:relative;
    width:100%;
}
.password-group .form-control{
    padding-right:45px;
    transition: all 0.3s ease;
}
.password-group .btn-eye{
    position:absolute;
    right:40px;
    top:50%;
    transform:translateY(-50%);
    border:none;
    background:transparent;
    cursor:pointer;
    color:#555;
    font-size:1.1rem;
    padding:5px;
    transition: all 0.3s ease;
}
.password-group .btn-eye:hover{color:#000;}
.password-group.show-password .btn-eye i{
    transform: rotateY(180deg);
    color:#ff416c;
    transition: all 0.3s ease;
}
.password-group.show-password .form-control{
    background:rgba(255,255,255,0.35);
    color:#000;
}
.dark-mode .password-group.show-password .form-control{
    background:rgba(255,255,255,0.12);
    color:#fff;
}

/* ===== Animations ===== */
@keyframes fadeIn{
    from{opacity:0;transform:translateY(20px);}
    to{opacity:1;transform:translateY(0);}
}

/* Mobile responsiveness */
@media (max-width: 480px){
    .password-group .btn-eye{
        font-size:1rem;
        right:40px;
        padding:4px;
    }
    .password-group .form-control{
        padding-right:40px;
        font-size:0.95rem;
    }
    .card{padding:30px 20px;}
    .form-title{font-size:1.6rem;}
}
</style>
</head>
<body>
<div class="wrapper">
<div class="card">
<h2 class="form-title">Ludo Wallet</h2>

<?php if(!empty($error)) echo '<div class="alert alert-error">'.$error.'</div>'; ?>

<form method="POST" action="">
  <div class="input-group">
    <i class="fa fa-user"></i>
    <input type="text" class="form-control" name="login_input" placeholder="Email or Username" required>
  </div>

  <div class="input-group password-group">
    <i class="fa fa-lock"></i>
    <input type="password" class="form-control" id="password" name="password" placeholder="Password" required>
    <button type="button" class="btn-eye" onclick="togglePassword('password', this)">
      <i class="fa fa-eye"></i>
    </button>
  </div>

  <button type="submit" class="btn-custom">Login</button>
</form>

<div class="links">
<a href="register.php"><i class="fa fa-user-plus"></i> Create Account</a>
<a href="forgot_password.php"><i class="fa fa-key"></i> Forgot Password?</a>
</div>
</div>
</div>

<button class="dark-mode-toggle" onclick="toggleDarkMode()">🌙 Dark Mode</button>

<script>
function toggleDarkMode(){
    document.body.classList.toggle('dark-mode');
}

function togglePassword(id, btn){
    let input = document.getElementById(id);
    let icon = btn.querySelector('i');
    let group = btn.closest('.password-group');

    if(input.type === 'password'){
        input.type = 'text';
        icon.classList.remove('fa-eye');
        icon.classList.add('fa-eye-slash');
        group.classList.add('show-password');
    } else {
        input.type = 'password';
        icon.classList.remove('fa-eye-slash');
        icon.classList.add('fa-eye');
        group.classList.remove('show-password');
    }
}
</script>
</body>
</html>
