<?php
session_start();
require '../config/database_connection.php';

if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login_register.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    // Sanitize inputs
    $name = trim($_POST['name']);
    $description = trim($_POST['description']);
    $price = isset($_POST['price']) ? floatval($_POST['price']) : 0;
    $discount = isset($_POST['discount']) ? floatval($_POST['discount']) : 0;
    $category = trim($_POST['category']);
    $status = trim($_POST['status']);
    $restaurant_id = $_SESSION['admin_id'];

    // Calculate final price & old price
    $old_price = $price;
    $final_price = ($discount > 0) ? $price - ($price * ($discount / 100)) : $price;

    // Image upload
    $image = '';
    $uploadOk = true;

    if (isset($_FILES['image']) && $_FILES['image']['error'] == UPLOAD_ERR_OK) {
        $target_dir = "uploads/";
        if (!file_exists($target_dir)) {
            mkdir($target_dir, 0777, true);
        }

        $file_name = basename($_FILES['image']['name']);
        $file_extension = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
        $new_file_name = uniqid("IMG_", true) . '.' . $file_extension;
        $target_file = $target_dir . $new_file_name;

        // Validate image
        $check = getimagesize($_FILES['image']['tmp_name']);
        if ($check === false) {
            $_SESSION['error'] = "File is not a valid image.";
            $uploadOk = false;
        }

        // Max 2MB
        if ($_FILES['image']['size'] > 2 * 1024 * 1024) {
            $_SESSION['error'] = "Image too large (max 2MB).";
            $uploadOk = false;
        }

        // Allowed types
        $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        if (!in_array($file_extension, $allowed)) {
            $_SESSION['error'] = "Only JPG, JPEG, PNG, GIF & WEBP allowed.";
            $uploadOk = false;
        }

        if ($uploadOk) {
            if (move_uploaded_file($_FILES['image']['tmp_name'], $target_file)) {
                $image = $target_file;
            } else {
                $_SESSION['error'] = "Error uploading image.";
                $uploadOk = false;
            }
        }
    } else {
        $_SESSION['error'] = "Please select an image.";
        $uploadOk = false;
    }

    if (empty($name) || empty($price) || !$uploadOk) {
        if (!isset($_SESSION['error'])) {
            $_SESSION['error'] = "All fields are required.";
        }
        header("Location: menu.php");
        exit();
    }

    try {
        $stmt = $conn->prepare("
            INSERT INTO items 
            (restaurant_id, name, description, old_price, price, discount, category, status, image)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");

        $stmt->bind_param(
            "issddssss",
            $restaurant_id,
            $name,
            $description,
            $old_price,
            $final_price,
            $discount,
            $category,
            $status,
            $image
        );

        if ($stmt->execute()) {
            $_SESSION['success'] = "Menu item added successfully!";
        } else {
            $_SESSION['error'] = "Error adding item: " . $stmt->error;
        }

        $stmt->close();
    } catch (Exception $e) {
        $_SESSION['error'] = "Database error: " . $e->getMessage();
    }

    $conn->close();
    header("Location: menu.php");
    exit();
} else {
    header("Location: menu.php");
    exit();
}
?>
