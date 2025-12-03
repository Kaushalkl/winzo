<?php
/**
 * =====================================
 * WALLET APP CONFIGURATION FILE
 * =====================================
 */

define('APP_NAME', 'winzo');
define('APP_ENV', 'development'); // production for live

// ------------------ RAZORPAY CONFIG ------------------
define('RAZORPAY_KEY', 'rzp_test_RQvP9DNo3fIN0C');
define('RAZORPAY_SECRET', 'Sq7mbIoLp8gSAqqrCEgutZmc');

// ------------------ RECHARGE LIMITS ------------------
define('MIN_RECHARGE', 10);
define('MAX_RECHARGE', 50000);

// ------------------ BUSINESS INFO ------------------
define('BUSINESS_NAME', 'winzo');

// ------------------ OPTIONAL .ENV SUPPORT ------------------
if (file_exists(__DIR__ . '/.env')) {
    $lines = file(__DIR__ . '/.env', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) continue;
        [$name, $value] = explode('=', $line, 2);
        $_ENV[trim($name)] = trim($value);
    }
}
?>
