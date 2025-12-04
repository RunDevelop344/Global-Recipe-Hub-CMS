<?php
session_start();

// Generate random 5-character captcha text
$captcha_text = substr(str_shuffle("ABCDEFGHJKLMNPQRSTUVWXYZ23456789"), 0, 5);

// Store captcha in session
$_SESSION['captcha_code'] = $captcha_text;

// Create image
header("Content-Type: image/png");
$image = imagecreate(120, 40);

// Colors
$bg = imagecolorallocate($image, 240, 240, 240);
$text_color = imagecolorallocate($image, 20, 40, 100);

// Add text
imagestring($image, 5, 22, 10, $captcha_text, $text_color);

// Add noise (optional)
for ($i = 0; $i < 50; $i++) {
    $noise_color = imagecolorallocate($image, rand(150,200), rand(150,200), rand(150,200));
    imagesetpixel($image, rand(1,119), rand(1,39), $noise_color);
}

imagepng($image);
imagedestroy($image);
?>
