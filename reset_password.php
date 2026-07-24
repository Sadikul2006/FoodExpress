<?php
session_start();
header('Content-Type: application/json');
include "config/database_connection.php";

// Check if OTP was verified
if (!isset($_SESSION['otp_verified']) || $_SESSION['otp_verified'] !== true) {
    echo json_encode([
        "status" => "error",
        "message" => "Unauthorized action. Please verify OTP first."
    ]);
    exit;
}

// Safety check
if (!isset($_POST['password']) || empty(trim($_POST['password']))) {
    echo json_encode([
        "status" => "error",
        "message" => "Password cannot be empty"
    ]);
    exit;
}

$password = trim($_POST['password']);

// Validate password server-side (same as frontend)
if (strlen($password) < 8 || !preg_match('/[a-zA-Z]/', $password) || !preg_match('/\d/', $password)) {
    echo json_encode([
        "status" => "error",
        "message" => "Password must be at least 8 characters and contain letters & numbers"
    ]);
    exit;
}

// Get user email from session
if (!isset($_SESSION['reset_email'])) {
    echo json_encode([
        "status" => "error",
        "message" => "Session expired. Please start again."
    ]);
    exit;
}

$email = $_SESSION['reset_email'];

// Hash the password
$hashedPassword = password_hash($password, PASSWORD_DEFAULT);

// Update password in DB
$stmt = $conn->prepare("UPDATE users SET password = ? WHERE email = ?");
$stmt->bind_param("ss", $hashedPassword, $email);

if ($stmt->execute()) {
    // Clear all session data related to reset
    unset($_SESSION['otp_verified'], $_SESSION['reset_email'], $_SESSION['reset_otp'], $_SESSION['otp_time']);

    echo json_encode([
        "status" => "success"
    ]);
} else {
    echo json_encode([
        "status" => "error",
        "message" => "Failed to reset password. Try again."
    ]);
}
exit;
