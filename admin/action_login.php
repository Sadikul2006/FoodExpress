<?php
// action_login.php
session_start();
include '../config/database_connection.php';

// logout.php
if (isset($_GET['logout'])) {
    session_unset();
    session_destroy();
    header("Location: index.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    die("Invalid Request");
}

$action = $_POST['action'] ?? '';


// SIGNUP SECTION
if ($action === 'signup') {
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = mysqli_real_escape_string($conn, $_POST['password']);
    $confirmPassword = mysqli_real_escape_string($conn, $_POST['confirmPassword']);

    // Validation
    if (empty($name) || empty($email) || empty($password)) {
        die("<script>alert('All fields are required!'); window.history.back();</script>");
    }

    if ($password !== $confirmPassword) {
        die("<script>alert('Password does not match!'); window.history.back();</script>");
    }

    // Email validation
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        die("<script>alert('Invalid email format!'); window.history.back();</script>");
    }

    // Email Exists Check
    $checkEmail = $conn->prepare("SELECT email FROM admin WHERE email = ?");
    $checkEmail->bind_param("s", $email);
    $checkEmail->execute();
    $checkResult = $checkEmail->get_result();

    if ($checkResult->num_rows > 0) {
        die("<script>alert('Email already exists!'); window.history.back();</script>");
    }

    // Hash Password
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    // Insert new restaurant owner
    $insert = $conn->prepare("
        INSERT INTO admin (name, email, password)
        VALUES (?, ?, ?)
    ");
    $insert->bind_param("sss", $name, $email, $hashedPassword);

    if ($insert->execute()) {
        echo "<script>alert('Account created successfully! Please login.'); window.location='index.php';</script>";
        exit();
    } else {
        die("<script>alert('Signup failed. Please try again.'); window.history.back();</script>");
    }
}

// LOGIN SECTION
if ($action === 'login') {
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = $_POST['password'];

    // Validation
    if (empty($email) || empty($password)) {
        die("<script>alert('Email and password are required!'); window.history.back();</script>");
    }

    $query = $conn->prepare("SELECT * FROM admin WHERE email = ?");
    $query->bind_param("s", $email);
    $query->execute();
    $result = $query->get_result();

    if ($result->num_rows == 0) {
        die("<script>alert('Email not found!'); window.history.back();</script>");
    }

    $admin = $result->fetch_assoc();

    // Verify Password
    if (!password_verify($password, $admin['password'])) {
        die("<script>alert('Incorrect Password!'); window.history.back();</script>");
    }

    // Login success → create session
    $_SESSION['admin_id'] = $admin['admin_id'];
    $_SESSION['restaurant_id'] = $admin['restaurant_id'];
    $_SESSION['admin_name'] = $admin['name'];

    echo "<script>alert('Login Successful!'); window.location='dashboard.php';</script>";
    exit();
}

// Close connection
$conn->close();
