<?php
session_start();
include 'database_connection.php'; // Fixed typo in filename

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email']);

    // Validate email input
    if (empty($email)) {
        $_SESSION['error'] = "Email address is required";
        header("Location: forgot.php");
        exit();
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['error'] = "Invalid email format";
        header("Location: forgot.php");
        exit();
    }

    // Check if email exists in database
    $stmt = $conn->prepare("SELECT id, email FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();
        
        // Store email in session for reset.php
        $_SESSION['reset_email'] = $user['email'];
        
        // Redirect to password reset page
        header("Location: reset.php");
        exit();
    } else {
        $_SESSION['error'] = "Email address not found";
        header("Location: forgot.php");
        exit();
    }
} else {
    // If not a POST request, redirect back
    header("Location: forgot.php");
    exit();
}
?>