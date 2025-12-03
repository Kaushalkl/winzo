<?php
// ==========================================
// ✅ CONFIGURATION & DEPENDENCIES
// ==========================================

// Load database configuration
require_once 'db.php';

// Load PHPMailer
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once __DIR__ . '/phpmailer/src/Exception.php';
require_once __DIR__ . '/phpmailer/src/PHPMailer.php';
require_once __DIR__ . '/phpmailer/src/SMTP.php';

// ==========================================
// ✅ SECURITY & VALIDATION FUNCTIONS
// ==========================================

/**
 * Generate a secure numeric OTP
 */
function generateOTP($length = 6) {
    if ($length < 4 || $length > 8) {
        $length = 6; // Default to 6 if invalid length
    }
    
    $min = pow(10, $length - 1);
    $max = pow(10, $length) - 1;
    
    return str_pad(random_int($min, $max), $length, '0', STR_PAD_LEFT);
}

/**
 * Sanitize user input
 */
function sanitize_input($data) {
    if (is_array($data)) {
        return array_map('sanitize_input', $data);
    }
    
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
    return $data;
}

/**
 * Validate email format
 */
function validateEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

/**
 * Validate phone number (Indian format)
 */
function validatePhone($phone) {
    return preg_match('/^[6-9]\d{9}$/', $phone);
}

/**
 * Validate IFSC code format
 */
function validateIFSC($ifsc) {
    return preg_match('/^[A-Z]{4}0[A-Z0-9]{6}$/', $ifsc);
}

/**
 * Generate CSRF token
 */
function generateCSRFToken() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Validate CSRF token
 */
function validateCSRFToken($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

// ==========================================
// ✅ DATABASE HELPER FUNCTIONS
// ==========================================

/**
 * Execute database query with parameters
 */
function executeQuery($conn, $sql, $params = []) {
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        throw new Exception("Database prepare failed: " . $conn->error);
    }
    
    if (!empty($params)) {
        $types = '';
        foreach ($params as $param) {
            if (is_int($param)) {
                $types .= 'i';
            } elseif (is_float($param)) {
                $types .= 'd';
            } else {
                $types .= 's';
            }
        }
        $stmt->bind_param($types, ...$params);
    }
    
    if (!$stmt->execute()) {
        throw new Exception("Database execute failed: " . $stmt->error);
    }
    
    return $stmt;
}

/**
 * Check database connection health
 */
function checkDatabaseHealth($conn) {
    try {
        $result = $conn->query("SELECT 1");
        return $result !== false;
    } catch (Exception $e) {
        error_log("Database health check failed: " . $e->getMessage());
        return false;
    }
}

/**
 * Log activity to database
 */
function logActivity($user_id, $action, $description = '') {
    global $conn;
    
    $ip_address = getClientIP();
    $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown';
    
    try {
        $stmt = $conn->prepare("INSERT INTO audit_log (user_id, action, description, ip_address, user_agent) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("issss", $user_id, $action, $description, $ip_address, $user_agent);
        $stmt->execute();
        $stmt->close();
        return true;
    } catch (Exception $e) {
        error_log("Activity logging failed: " . $e->getMessage());
        return false;
    }
}

// ==========================================
// ✅ EMAIL FUNCTIONS
// ==========================================

/**
 * Send OTP via Gmail SMTP (PHPMailer)
 */
function sendOTP($email, $otp, $name = 'User', $type = 'login') {
    $mail = new PHPMailer(true);

    try {
        // ---- SMTP Configuration ----
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'rrfincobihar@gmail.com';
        $mail->Password   = 'zsyr tfto cdle dlfc';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;
        $mail->Timeout    = 30;
        $mail->SMTPOptions = [
            'ssl' => [
                'verify_peer' => false,
                'verify_peer_name' => false,
                'allow_self_signed' => true
            ]
        ];

        // ---- Sender & Recipient ----
        $mail->setFrom('rrfincobihar@gmail.com', 'Wallet App');
        $mail->addAddress($email, $name);
        $mail->addReplyTo('rrfincobihar@gmail.com', 'Wallet App Support');

        // ---- Determine OTP type message ----
        if ($type === 'forgot') {
            $subject = 'Wallet App - Forgot Password OTP Verification';
            $otp_message = 'YOUR FORGOT PASSWORD VERIFICATION OTP';
            $header_title = 'Forgot Password Verification';
        } else {
            $subject = 'Wallet App - OTP Verification';
            $otp_message = 'YOUR LOGIN VERIFICATION OTP';
            $header_title = 'Verification Required';
        }

        // ---- Email Content ----
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body    = "
            <!DOCTYPE html>
            <html>
            <head>
                <meta charset='UTF-8'>
                <style>
                    body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); margin:0; padding:40px 20px;}
                    .container { max-width:600px; margin:0 auto; background:white; padding:40px; border-radius:20px; box-shadow:0 20px 60px rgba(0,0,0,0.1);}
                    .header { text-align:center; color:#333; border-bottom:3px solid #10b981; padding-bottom:20px; margin-bottom:30px;}
                    .otp-code { font-size:42px; font-weight:bold; color:#10b981; text-align:center; margin:30px 0; padding:20px; background:#f0fdf4; border-radius:15px; letter-spacing:8px; border:2px dashed #10b981;}
                    .warning { background:#fffbeb; color:#92400e; padding:15px; border-radius:10px; margin:20px 0; border-left:4px solid #f59e0b;}
                    .footer { margin-top:30px; padding-top:20px; border-top:1px solid #e5e7eb; color:#6b7280; font-size:14px; text-align:center;}
                </style>
            </head>
            <body>
                <div class='container'>
                    <div class='header'>
                        <h1 style='margin:0; color:#10b981;'>$header_title</h1>
                    </div>
                    
                    <p style='font-size:16px; line-height:1.6; color:#4b5563;'>
                        Hello <strong style='color:#6366f1;'>$name</strong>,
                    </p>
                    
                    <p style='font-size:16px; line-height:1.6; color:#4b5563;'>
                        $otp_message
                    </p>
                    
                    <div class='otp-code'>$otp</div>
                    
                    <div class='warning'>
                        <strong>⚠️ Important Security Notice:</strong><br>
                        This OTP will expire in <strong>10 minutes</strong>. Never share this code with anyone, including Wallet App support.
                    </div>
                    
                    <p style='font-size:14px; color:#6b7280; text-align:center;'>
                        If you didn't request this, please contact our support team immediately.
                    </p>
                    
                    <div class='footer'>
                        <p>Stay secure,<br><strong>The Wallet App Team</strong></p>
                        <p>&copy; " . date('Y') . " Wallet App. All rights reserved.</p>
                    </div>
                </div>
            </body>
            </html>
        ";

        $mail->AltBody = "$subject\n\nHello $name,\n\n$otp_message: $otp\n\nThis OTP will expire in 10 minutes.\n\nIf you didn't request this, please ignore this email.";

        $mail->send();
        error_log("OTP email sent successfully to: $email | Type: $type");
        return true;

    } catch (Exception $e) {
        error_log("Mailer Error: " . $mail->ErrorInfo . " | Email: $email");
        return false;
    }
}



































/**
 * Send transaction notification email
 */
function sendTransactionEmail($email, $name, $type, $amount, $balance, $transaction_id) {
    $mail = new PHPMailer(true);

    try {
        // ---------- SMTP CONFIGURATION ----------
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'rrfincobihar@gmail.com';
        $mail->Password   = 'zsyr tfto cdle dlfc'; // Use App Password, not Gmail password
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;

        $mail->setFrom('rrfincobihar@gmail.com', 'Wallet App');
        $mail->addAddress($email, $name);

        // ---------- EMAIL CONTENT ----------
        $transaction_type = ucfirst($type); // "Credit" or "Debit"
        $amount_formatted = '₹' . number_format($amount, 2);
        $balance_formatted = '₹' . number_format($balance, 2);
        $color = ($type === 'credit') ? '#28a745' : '#dc3545'; // green for credit, red for debit

        $mail->isHTML(true);
        $mail->Subject = "Wallet App - $transaction_type Notification";

        $mail->Body = "
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset='UTF-8'>
            <style>
                body { font-family: Arial, sans-serif; background-color: #f0f2f5; margin: 0; padding: 20px; }
                .container { max-width: 600px; margin: 0 auto; background: #ffffff; padding: 30px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
                .header { text-align: center; color: #333; border-bottom: 2px solid #007bff; padding-bottom: 15px; }
                .transaction-details { background: #f8f9fa; padding: 20px; border-radius: 5px; margin: 20px 0; }
                .amount { font-size: 24px; font-weight: bold; color: $color; text-align: center; margin-bottom: 10px; }
                .footer { margin-top: 20px; padding-top: 15px; border-top: 1px solid #ddd; color: #666; font-size: 12px; text-align: center; }
                p { line-height: 1.5; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h2>Wallet $transaction_type</h2>
                </div>

                <p>Hello <strong>$name</strong>,</p>
                <p>Your wallet has been <strong>{$type}ed</strong> successfully.</p>

                <div class='transaction-details'>
                    <div class='amount'>$amount_formatted</div>
                    <p><strong>Transaction ID:</strong> $transaction_id</p>
                    <p><strong>Type:</strong> $transaction_type</p>
                    <p><strong>Current Balance:</strong> $balance_formatted</p>
                    <p><strong>Date:</strong> " . date('Y-m-d H:i:s') . "</p>
                </div>

                <p>If you did not perform this transaction, please contact support immediately.</p>

                <div class='footer'>
                    <p>This is an automated transaction notification.</p>
                    <p>&copy; " . date('Y') . " Wallet App. All rights reserved.</p>
                </div>
            </div>
        </body>
        </html>
        ";

        $mail->send();
        return true;

    } catch (Exception $e) {
        error_log("Transaction email failed to $email: " . $mail->ErrorInfo);
        return false;
    }
}


// ==========================================
// ✅ SESSION & AUTHENTICATION FUNCTIONS
// ==========================================

/**
 * Secure session start with validation
 */
function secure_session_start() {
    if (session_status() === PHP_SESSION_NONE) {
        // Session configuration
        ini_set('session.cookie_httponly', 1);
        ini_set('session.cookie_secure', 1); // Enable in production with HTTPS
        ini_set('session.use_strict_mode', 1);
        ini_set('session.cookie_samesite', 'Strict');
        
        session_start();
        
        // Regenerate session ID periodically
        if (!isset($_SESSION['created'])) {
            $_SESSION['created'] = time();
        } else if (time() - $_SESSION['created'] > 1800) { // 30 minutes
            session_regenerate_id(true);
            $_SESSION['created'] = time();
        }
    }
}

/**
 * Check if user is logged in with additional security checks
 */
function ensureLoggedIn() {
    secure_session_start();

    // Check if user is logged in
    if (empty($_SESSION['user_email']) || empty($_SESSION['user_id'])) {
        // Store intended URL for redirect after login
        $_SESSION['redirect_url'] = $_SERVER['REQUEST_URI'];
        header('Location: index.php');
        exit;
    }

    // Additional security checks
    $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? '';
    $ip_address = $_SERVER['REMOTE_ADDR'] ?? '';
    
    // Verify session consistency
    if (empty($_SESSION['user_agent'])) {
        $_SESSION['user_agent'] = $user_agent;
    } else if ($_SESSION['user_agent'] !== $user_agent) {
        // Possible session hijacking
        session_destroy();
        header('Location: index.php?error=session_invalid');
        exit;
    }

    // Check session expiration (8 hours)
    if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > 28800)) {
        session_destroy();
        header('Location: index.php?error=session_expired');
        exit;
    }

    // Update last activity time
    $_SESSION['last_activity'] = time();

    // Force HTTPS in production
    if ($_SERVER['HTTP_HOST'] != 'localhost' && $_SERVER['HTTP_HOST'] != '127.0.0.1') {
        if (empty($_SERVER['HTTPS']) || $_SERVER['HTTPS'] === 'off') {
            $redirect_url = "https://" . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
            header("Location: $redirect_url");
            exit;
        }
    }
}

/**
 * Login user and set session
 */
function loginUser($user_data) {
    secure_session_start();
    
    $_SESSION['user_id'] = $user_data['id'];
    $_SESSION['user_email'] = $user_data['email'];
    $_SESSION['user_name'] = $user_data['name'];
    $_SESSION['user_agent'] = $_SERVER['HTTP_USER_AGENT'];
    $_SESSION['ip_address'] = $_SERVER['REMOTE_ADDR'];
    $_SESSION['last_activity'] = time();
    $_SESSION['login_time'] = time();
    
    // Update last login in database
    global $conn;
    $stmt = $conn->prepare("UPDATE users SET last_login = NOW() WHERE id = ?");
    $stmt->bind_param("i", $user_data['id']);
    $stmt->execute();
    $stmt->close();
}

/**
 * Logout user securely
 */
function logoutUser() {
    secure_session_start();
    
    // Clear all session variables
    $_SESSION = array();
    
    // Delete session cookie
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }
    
    // Destroy session
    session_destroy();
}

// ==========================================
// ✅ UTILITY FUNCTIONS
// ==========================================

/**
 * Format currency with Indian numbering system
 */
function formatCurrency($amount) {
    return '₹' . number_format($amount, 2);
}

/**
 * Generate random string for various purposes
 */
function generateRandomString($length = 10) {
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $charactersLength = strlen($characters);
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[random_int(0, $charactersLength - 1)];
    }
    return $randomString;
}

/**
 * Validate password strength
 */
function validatePassword($password) {
    // Minimum 8 characters, at least one letter and one number
    return preg_match('/^(?=.*[A-Za-z])(?=.*\d).{8,}$/', $password);
}

/**
 * Hash password using bcrypt
 */
function hashPassword($password) {
    return password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
}

/**
 * Verify password against hash
 */
function verifyPassword($password, $hash) {
    return password_verify($password, $hash);
}

/**
 * Get client IP address
 */
function getClientIP() {
    if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
        return $_SERVER['HTTP_CLIENT_IP'];
    } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        return $_SERVER['HTTP_X_FORWARDED_FOR'];
    } else {
        return $_SERVER['REMOTE_ADDR'];
    }
}

/**
 * Mask account number for display
 */
function maskAccountNumber($account_number) {
    if (strlen($account_number) <= 4) {
        return $account_number;
    }
    return substr($account_number, 0, 4) . 'XXXX' . substr($account_number, -4);
}

// ==========================================
// ✅ ERROR HANDLING FUNCTIONS
// ==========================================

/**
 * Custom error handler
 */
function customErrorHandler($errno, $errstr, $errfile, $errline) {
    if (!(error_reporting() & $errno)) {
        return false;
    }
    
    $error_types = [
        E_ERROR => 'Error',
        E_WARNING => 'Warning',
        E_PARSE => 'Parse Error',
        E_NOTICE => 'Notice',
        E_USER_ERROR => 'User Error',
        E_USER_WARNING => 'User Warning',
        E_USER_NOTICE => 'User Notice',
    ];
    
    $error_type = $error_types[$errno] ?? 'Unknown Error';
    
    $error_message = "[$error_type] $errstr in $errfile on line $errline";
    error_log($error_message);
    
    // Don't display errors in production
    if ($_SERVER['HTTP_HOST'] == 'localhost' || $_SERVER['HTTP_HOST'] == '127.0.0.1') {
        echo "<div style='background: #f8d7da; color: #721c24; padding: 10px; margin: 10px; border: 1px solid #f5c6cb; border-radius: 5px;'>
                <strong>$error_type:</strong> $errstr<br>
                <small>File: $errfile (Line: $errline)</small>
              </div>";
    }
    
    return true;
}

/**
 * Custom exception handler
 */
function customExceptionHandler($exception) {
    error_log("Uncaught Exception: " . $exception->getMessage() . " in " . $exception->getFile() . " on line " . $exception->getLine());
    
    // Display user-friendly error message
    if ($_SERVER['HTTP_HOST'] == 'localhost' || $_SERVER['HTTP_HOST'] == '127.0.0.1') {
        echo "<div style='background: #f8d7da; color: #721c24; padding: 15px; margin: 10px; border: 1px solid #f5c6cb; border-radius: 5px;'>
                <h3>Application Error</h3>
                <p><strong>Error:</strong> " . $exception->getMessage() . "</p>
                <p><strong>File:</strong> " . $exception->getFile() . " (Line: " . $exception->getLine() . ")</p>
              </div>";
    } else {
        echo "<div style='background: #f8d7da; color: #721c24; padding: 15px; margin: 10px; border: 1px solid #f5c6cb; border-radius: 5px;'>
                <h3>Something went wrong</h3>
                <p>Please try again later or contact support if the problem persists.</p>
              </div>";
    }
}

// Set custom error handlers
set_error_handler('customErrorHandler');
set_exception_handler('customExceptionHandler');

// ==========================================
// ✅ INITIALIZATION
// ==========================================

// Start secure session
secure_session_start();

// Generate CSRF token for forms
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = generateCSRFToken();
}

?>