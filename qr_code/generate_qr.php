<?php
// generate_qr.php
// If you install phpqrcode library, you can generate real QR images.
// For now, we output a placeholder PNG or data URI.

$userId = intval($_GET['u'] ?? 0);
if(!$userId){
    // generate blank placeholder
    header('Content-Type: image/png');
    // create small PNG with text
    $im = imagecreatetruecolor(250,250);
    $bg = imagecolorallocate($im,255,255,255);
    $textcolor = imagecolorallocate($im,0,0,0);
    imagefilledrectangle($im,0,0,250,250,$bg);
    imagestring($im,5,40,110,"QR Placeholder",$textcolor);
    imagepng($im);
    imagedestroy($im);
    exit;
}

// Alternatively, redirect to a QR API (quick)
$data = "wallet://user/".$userId;
$size = "200x200";
$src = "https://api.qrserver.com/v1/create-qr-code/?size={$size}&data=".urlencode($data);
header("Location: $src");
exit;
