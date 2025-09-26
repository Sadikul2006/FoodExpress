<?php
session_start();
require 'database_connection.php';

if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login_register.php");
    exit();
}



if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Validate and sanitize inputs
    $name = trim($_POST['name']);
    $description = trim($_POST['description']);
    $price = floatval($_POST['price']);
    $category = $_POST['category'];
    $status = $_POST['status'];
    $restaurant_id = $_SESSION['admin_id'];
    
    // File upload handling
    $image = '';
    $uploadOk = 1;
    
    if (isset($_FILES['image']) && $_FILES['image']['error'] == UPLOAD_ERR_OK) {
        $target_dir = "uploads/";
        if (!file_exists($target_dir)) {
            mkdir($target_dir, 0777, true);
        }
        
        $file_name = basename($_FILES['image']['name']);
        $file_extension = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
        $new_file_name = uniqid() . '.' . $file_extension;
        $target_file = $target_dir . $new_file_name;
        
        // Check if image file is a actual image
        $check = getimagesize($_FILES['image']['tmp_name']);
        if ($check === false) {
            $_SESSION['error'] = "File is not an image.";
            $uploadOk = 0;
        }
        
        // Check file size (max 2MB)
        if ($_FILES['image']['size'] > 2000000) {
            $_SESSION['error'] = "Sorry, your file is too large (max 2MB).";
            $uploadOk = 0;
        }
        
        // Allow certain file formats
        $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif'];
        if (!in_array($file_extension, $allowed_extensions)) {
            $_SESSION['error'] = "Sorry, only JPG, JPEG, PNG & GIF files are allowed.";
            $uploadOk = 0;
        }
        
        if ($uploadOk == 1) {
            if (move_uploaded_file($_FILES['image']['tmp_name'], $target_file)) {
                $image = $target_file;
            } else {
                $_SESSION['error'] = "Sorry, there was an error uploading your file.";
                $uploadOk = 0;
            }
        }
    } else {
        $_SESSION['error'] = "Please select an image file.";
        $uploadOk = 0;
    }
    
    // Validate required fields
    if (empty($name) || empty($price) || $uploadOk == 0) {
        if (!isset($_SESSION['error'])) {
            $_SESSION['error'] = "All fields are required.";
        }
        header("Location: menu.php");
        exit();
    }
    
    // Insert into database
    try {
        $stmt = $conn->prepare("INSERT INTO items (restaurant_id, name, description, price, category, status, image) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("issdsss",$restaurant_id, $name, $description, $price, $category, $status, $image);
        
        if ($stmt->execute()) {
            $_SESSION['success'] = "Menu item added successfully!";
            header("Location: menu.php");
        } else {
            $_SESSION['error'] = "Error adding menu item: " . $conn->error;
            header("Location: menu.php");
        }
        
        $stmt->close();
    } catch (Exception $e) {
        $_SESSION['error'] = "Database error: " . $e->getMessage();
        header("Location: menu.php");
    }
    
    $conn->close();
    exit();
} else {
    // Not a POST request, redirect to form
    header("Location: menu.php");
    exit();
}
?>