<?php
session_start();
header('Content-Type: application/json');

// Safety checks
if (!isset($_POST['otp'])) {
    echo json_encode([
        "status" => "error",
        "message" => "Invalid request"
    ]);
    exit;
}

if (!isset($_SESSION['reset_otp'], $_SESSION['otp_time'])) {
    echo json_encode([
        "status" => "error",
        "message" => "OTP expired. Please request again."
    ]);
    exit;
}

$enteredOtp = trim($_POST['otp']);
$storedOtp  = $_SESSION['reset_otp'];

// OTP expiry: 5 minutes
if (time() - $_SESSION['otp_time'] > 300) {
    unset($_SESSION['reset_otp'], $_SESSION['otp_time']);
    echo json_encode([
        "status" => "error",
        "message" => "OTP expired. Please request again."
    ]);
    exit;
}

// Verify OTP
if ($enteredOtp !== (string)$storedOtp) {
    echo json_encode([
        "status" => "error",
        "message" => "Invalid OTP"
    ]);
    exit;
}

$_SESSION['otp_verified'] = true;

unset($_SESSION['reset_otp'], $_SESSION['otp_time']);

echo json_encode([
    "status" => "success"
]);

exit;