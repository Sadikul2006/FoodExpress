<?php
session_start();
include 'database_connection.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: user_login_register.php");
    exit();
}

$user_id = $_SESSION['user_id'];

if (isset($_FILES['image']) && $_FILES['image']['error'] === 0) {
    $fileTmpPath = $_FILES['image']['tmp_name'];
    $fileName = time() . '_' . preg_replace("/[^a-zA-Z0-9.]/", "_", $_FILES['image']['name']);
    $uploadFileDir = 'admin/uploads/';
    $dest_path = $uploadFileDir . $fileName;

    $allowedExt = ['jpg','jpeg','png','gif'];
    $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

    if (in_array($fileExt, $allowedExt)) {
        if (move_uploaded_file($fileTmpPath, $dest_path)) {
            // Database update
            $stmt = $conn->prepare("UPDATE users SET image = ? WHERE id = ?");
            $stmt->bind_param("si", $dest_path, $user_id);
            $stmt->execute();
            $stmt->close();

            // Update session
            $_SESSION['image'] = $dest_path;
        } else {
            echo "File move failed.";
        }
    } else {
        echo "Only JPG, JPEG, PNG, GIF allowed.";
    }
}

$conn->close();
header("Location: user_info.php");
exit();
?>
