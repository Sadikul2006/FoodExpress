<?php
session_start();
header('Content-Type: application/json');
include '../config/database_connection.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode([
        "status" => "error",
        "message" => "Invalid request method"
    ]);
    exit();
}

if (!isset($_SESSION['user_id']) || !is_numeric($_SESSION['user_id'])) {
    echo json_encode([
        "status" => "error",
        "message" => "User not logged in"
    ]);
    exit();
}

$user_id = (int) $_SESSION['user_id'];

if (!isset($_POST['update_profile'])) {
    echo json_encode([
        "status" => "error",
        "message" => "Invalid request"
    ]);
    exit();
}

$name  = trim($_POST['name']  ?? '');
$phone = trim($_POST['phone'] ?? '');

if ($name === '' || $phone === '') {
    echo json_encode([
        "status"  => "error",
        "message" => "Name and phone are required"
    ]);
    exit();
}

// Validate name (letters, spaces, and basic punctuation)
if (!preg_match("/^[a-zA-Z\s\.\-']{2,100}$/", $name)) {
    echo json_encode([
        "status"  => "error",
        "message" => "Invalid name format"
    ]);
    exit();
}

// Validate phone (10–15 digits, optional leading +)
if (!preg_match("/^\+?[0-9]{10,15}$/", $phone)) {
    echo json_encode([
        "status"  => "error",
        "message" => "Invalid phone number"
    ]);
    exit();
}

// Update the user's profile
$updateSql  = "UPDATE users SET name = ?, phone = ? WHERE id = ?";
$updateStmt = $conn->prepare($updateSql);

if (!$updateStmt) {
    echo json_encode([
        "status"  => "error",
        "message" => "Database error"
    ]);
    exit();
}

$updateStmt->bind_param("ssi", $name, $phone, $user_id);

if ($updateStmt->execute()) {
    // Update session name if stored there
    $_SESSION['user_name'] = $name;

    echo json_encode([
        "status"  => "success",
        "message" => "Profile updated successfully",
        "data"    => [
            "name"  => $name,
            "phone" => $phone
        ]
    ]);
} else {
    echo json_encode([
        "status"  => "error",
        "message" => "Failed to update profile"
    ]);
}

$updateStmt->close();
$conn->close();