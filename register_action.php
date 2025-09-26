<?php
session_start();
include 'database_connection.php';

function data_check($data) {
    $data = trim($data);
    $data = htmlspecialchars($data);
    $data = stripslashes($data);
    return $data;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = data_check($_POST['name']);
    $email = data_check($_POST['email']);
    $password = data_check($_POST['password']);
    $confirm_password = data_check($_POST['confirm_password']);

    if (empty($name) || empty($email) || empty($password) || empty($confirm_password)) {
        $_SESSION['error'] = "All fields are required";
        header("Location: dashboard.php");
        exit();
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['error'] = "Invalid email format";
        header("Location: register.php");
        exit();
    }

    if ($password !== $confirm_password) {
        $_SESSION['error'] = "Passwords do not match";
        header("Location: register.php");
        exit();
    }

    $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $_SESSION['error'] = "Email already exists";
        header("Location: register.php");
        exit();
    }

    $password_en = password_hash($password, PASSWORD_DEFAULT);

    $stmt = $conn->prepare("INSERT INTO users (name, email, password) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $name, $email, $password_en);

    if ($stmt->execute()) {
        $_SESSION['success'] = "Registration successful!";
        header("Location: user_login_register.php");
    } else {
        $_SESSION['error'] = "Registration failed";
        header("Location: user_login_register.php");
    }

    $stmt->close();
    $conn->close();
}
?>
