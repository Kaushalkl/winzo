<?php
// test_sdk.php - SDK check के लिए
$razorpay_path = __DIR__ . '/razorpay-php/src';

echo "Checking Razorpay SDK...<br>";

if (!file_exists($razorpay_path)) {
    die("❌ ERROR: razorpay-php folder not found at: " . $razorpay_path);
}

echo "✅ razorpay-php folder found<br>";

$files = [
    '/Api.php',
    '/Request.php', 
    '/Resource.php',
    '/Errors/Error.php',
    '/Errors/SignatureVerificationError.php'
];

foreach ($files as $file) {
    if (file_exists($razorpay_path . $file)) {
        echo "✅ " . $file . " found<br>";
    } else {
        echo "❌ " . $file . " MISSING<br>";
    }
}

echo "<br>SDK check completed!";
?>