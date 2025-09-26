<?php
session_start();

if ($_SERVER["REQUEST_METHOD"] != "POST") {
    $_SESSION['error'] = "Invalid request method";
    header("Location: forgot.php");
    exit();
}

include 'database_connection.php';

// Check if user came through proper forgot password flow
if (!isset($_SESSION['reset_email'])) {
    $_SESSION['error'] = "Invalid password reset request";
    header("Location: forgot.php");
    exit();
}

// Get form data
$email = $_SESSION['reset_email'];
$new_password = $_POST['new_password'] ?? '';
$confirm_password = $_POST['confirm_password'] ?? '';

// Validate inputs
if (empty($new_password) || empty($confirm_password)) {
    $_SESSION['error'] = "Both password fields are required";
    header("Location: reset.php");
    exit();
}

if ($new_password !== $confirm_password) {
    $_SESSION['error'] = "Passwords do not match";
    header("Location: reset.php");
    exit();
}

if (strlen($new_password) < 8) {
    $_SESSION['error'] = "Password must be at least 8 characters long";
    header("Location: reset.php");
    exit();
}

// Hash the new password
$hashed_password = password_hash($new_password, PASSWORD_DEFAULT);

// Update password in database
$stmt = $conn->prepare("UPDATE users SET password = ? WHERE email = ?");
$stmt->bind_param("ss", $hashed_password, $email);

if ($stmt->execute()) {
    // Password updated successfully
    unset($_SESSION['reset_email']);
    $_SESSION['success'] = "Password updated successfully! Please login.";
    header("Location: login.php");
} else {
    $_SESSION['error'] = "Error updating password. Please try again.";
    header("Location: reset.php");
}

$stmt->close();
$conn->close();
exit();
?>