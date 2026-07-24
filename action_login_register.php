<?php
session_start();
include 'config/database_connection.php';


function data_check($data)
{
    $data = htmlentities($data);
    $data = trim($data);
    $data = stripslashes($data);
    return $data;
}

// Helper function for redirecting with message
function redirect($page, $msg_type = '', $msg = '')
{
    if ($msg_type && $msg) $_SESSION[$msg_type] = $msg;
    header("Location: $page");
    exit();
}

// Logout functionality
if (isset($_GET['logout'])) {
    session_unset();
    session_destroy();
    header("Location: index.php");
    exit();
}

// If user already logged in, redirect to restaurants page
if (isset($_SESSION['user_id'])) {
    header("Location: restaurants.php");
    exit();
}

// ======================== LOGIN PROCESS ========================
if (isset($_POST['action']) && $_POST['action'] === 'login') {
    $email = filter_var(trim($_POST['email']), FILTER_SANITIZE_EMAIL);
    $password = $_POST['password'];

    if (empty($email) || empty($password)) {
        redirect("user_login_register.php", "error", "Email and password are required");
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        redirect("user_login_register.php", "error", "Invalid email format");
    }

    $stmt = $conn->prepare("SELECT id, name, email, image, password FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();

        if (password_verify($password, $user['password'])) {
            // Login successful
            session_regenerate_id(true);
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['name'];
            $_SESSION['email'] = $user['email'];
            $_SESSION['user_img'] = $user['image'];
            redirect("restaurants.php", "success", "Login successful");
        } else {
            redirect("index.php", "error", "Invalid password");
        }
    } else {
        redirect("index.php", "error", "User not found");
    }
}

// ======================== SIGNUP PROCESS ========================
if (isset($_POST['action']) && $_POST['action'] === 'signup') {
    $name = data_check($_POST['name']);
    $email = filter_var(trim($_POST['email']), FILTER_SANITIZE_EMAIL);
    $password = $_POST['password'];
    $confirmPassword = $_POST['confirmPassword'];

    if (empty($name) || empty($email) || empty($password) || empty($confirmPassword)) {
        redirect("index.php", "error", "All fields are required");
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        redirect("index.php", "error", "Invalid email format");
    }

    if ($password !== $confirmPassword) {
        redirect("index.php", "error", "Passwords do not match");
    }

    // Check if email already exists
    $checkEmail = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $checkEmail->bind_param("s", $email);
    $checkEmail->execute();
    $checkEmail->store_result();

    if ($checkEmail->num_rows > 0) {
        redirect("index.php", "error", "Email already registered");
    }

    // Hash password
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    // Insert user into database
    $insert = $conn->prepare("INSERT INTO users (name, email, password) VALUES (?, ?, ?)");
    $insert->bind_param("sss", $name, $email, $hashedPassword);

    if ($insert->execute()) {
        redirect("index.php", "success", "Account created successfully! Please login.");
    } else {
        redirect("index.php", "error", "Something went wrong. Try again.");
    }
}

?>
