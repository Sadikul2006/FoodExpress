<?php
session_start();
include 'database_connection.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode(["status" => "error", "message" => "User not logged in"]);
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
            $stmt = $conn->prepare("UPDATE users SET image = ? WHERE id = ?");
            $stmt->bind_param("si", $dest_path, $user_id);
            $stmt->execute();
            $stmt->close();

            $_SESSION['image'] = $dest_path;

            echo json_encode(["status" => "success", "new_image" => $dest_path]);
            $_SESSION['user_img'] = $dest_path;
            exit();
        } else {
            echo json_encode(["status" => "error", "message" => "File move failed"]);
        }
    } else {
        echo json_encode(["status" => "error", "message" => "Invalid file type"]);
    }
} else {
    echo json_encode(["status" => "error", "message" => "No file uploaded"]);
}





// Check if request is update profile
if (isset($_POST['update_profile'])) {
    $user_id = $_SESSION['user_id'];

    $name  = trim($_POST['name'] ?? '');
    $phone = trim($_POST['phone'] ?? '');

    if ($name === '' || $phone === '') {
        echo json_encode(["status" => "error", "message" => "All fields required"]);
        exit();
    }

    $stmt = $conn->prepare("UPDATE users SET name = ?, phone = ? WHERE id = ?");
    $stmt->bind_param("ssi", $name, $phone, $user_id);

    if ($stmt->execute()) {
        $_SESSION['user_name'] = $name;
        $_SESSION['user_phone'] = $phone;

        echo json_encode(["status" => "success", "message" => "Profile updated"]);
    } else {
        echo json_encode(["status" => "error", "message" => "DB update failed"]);
    }

    $stmt->close();
    $conn->close();
} else {
    echo json_encode(["status" => "error", "message" => "Invalid request"]);
}






// if (isset($_POST['address_id']) && isset($_SESSION['user_id'])) {
//     $address_id = intval($_POST['address_id']);
//     $user_id    = intval($_SESSION['user_id']);

//     // Reset all defaults for this user
//     $sql1 = "UPDATE address SET is_default = 0 WHERE user_id = ?";
//     $stmt1 = $conn->prepare($sql1);
//     $stmt1->bind_param("i", $user_id);
//     $stmt1->execute();
//     $stmt1->close();

//     // Set new default
//     $sql2 = "UPDATE address SET is_default = 1 WHERE id = ? AND user_id = ?";
//     $stmt2 = $conn->prepare($sql2);
//     $stmt2->bind_param("ii", $address_id, $user_id);

//     if ($stmt2->execute()) {
//         echo "success";
//     } else {
//         echo "error";
//     }

//     $stmt2->close();
//     $conn->close();
//     exit;
// } else {
//     echo "error";
//     exit;
// }
?>

