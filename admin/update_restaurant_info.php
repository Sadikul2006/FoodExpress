<?php
session_start();
include '../config/database_connection.php';

// Restaurant ID (from session)
$restaurant_id = $_SESSION['admin_id'] ?? 0;

if ($restaurant_id == 0) {
    echo "Session expired! Please login again.";
    exit;
}

// Receive POST Data
$admin_name        = mysqli_real_escape_string($conn, $_POST['admin_name'] ?? '');
$restaurant_name   = mysqli_real_escape_string($conn, $_POST['restaurant_name'] ?? '');
$admin_phone       = mysqli_real_escape_string($conn, $_POST['admin_phone'] ?? '');
$admin_email       = mysqli_real_escape_string($conn, $_POST['admin_email'] ?? '');
$restaurant_place  = mysqli_real_escape_string($conn, $_POST['restaurant_places'] ?? '');
$latitude          = mysqli_real_escape_string($conn, $_POST['latitude'] ?? '');
$longitude         = mysqli_real_escape_string($conn, $_POST['longitude'] ?? '');

$image_name = "";

// Handle Image Upload
if (!empty($_FILES['restaurant_image']['name']) && $_FILES['restaurant_image']['error'] === UPLOAD_ERR_OK) {

    $file_name = $_FILES['restaurant_image']['name'];
    $file_tmp  = $_FILES['restaurant_image']['tmp_name'];
    $file_size = $_FILES['restaurant_image']['size'];

    $ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
    $allowed = ['jpg', 'jpeg', 'png'];

    if (!in_array($ext, $allowed)) {
        echo "Invalid image format! Only JPG, JPEG, PNG allowed.";
        exit;
    }

    if ($file_size > 2 * 1024 * 1024) {
        echo "Image must be under 2MB.";
        exit;
    }

    $image_name = "restaurant_" . time() . "_" . $restaurant_id . "." . $ext;
    $upload_path = "uploads/" . $image_name;

    if (!is_dir('uploads')) {
        mkdir('uploads', 0777, true);
    }

    move_uploaded_file($file_tmp, $upload_path);
}

// CHECK IF record exists in restaurant_info
$sql = "SELECT restaurant_img FROM restaurant_info WHERE restaurant_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $restaurant_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {

    // Existing record found → UPDATE
    $row = $result->fetch_assoc();

    // delete old image if new uploaded
    if (!empty($image_name) && !empty($row['restaurant_img']) && file_exists("uploads/" . $row['restaurant_img'])) {
        unlink("uploads/" . $row['restaurant_img']);
    }

    $final_image_name = empty($image_name) ? $row['restaurant_img'] : $image_name;

    // UPDATE restaurant_info
    $update = "UPDATE restaurant_info SET email = ?, phone = ?, restaurant_name = ?, restaurant_place = ?, latitude = ?, longitude = ?, restaurant_img = ? WHERE restaurant_id = ?";

    $stmt_update = $conn->prepare($update);
    $stmt_update->bind_param("sssssssi", $admin_email, $admin_phone, $restaurant_name, $restaurant_place, $latitude, $longitude, $final_image_name, $restaurant_id);

    if ($stmt_update->execute()) {
        echo "Business info updated successfully!";
    } else {
        echo "Error updating: " . $conn->error;
    }

} else {

    // No record → INSERT new restaurant_info
    $insert = "INSERT INTO restaurant_info (restaurant_id, email, phone, restaurant_name, restaurant_place, restaurant_img, latitude, longitude) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";

    $stmt_insert = $conn->prepare($insert);
    $stmt_insert->bind_param("isssssss", $restaurant_id, $admin_email, $admin_phone, $restaurant_name, $restaurant_place, $image_name, $latitude, $longitude);

    if ($stmt_insert->execute()) {
        echo "Business info saved successfully!";
    } else {
        echo "Error saving: " . $conn->error;
    }
}

$conn->close();
?>
