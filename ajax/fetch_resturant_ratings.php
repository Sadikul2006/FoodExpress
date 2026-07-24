<?php
include '../config/database_connection.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $restaurant_id = $_POST['restaurant_id'];
    $rating = $_POST['rating'];
    $user_id = $_SESSION['user_id'] ?? null;

    if (!$user_id) {
        echo "User not logged in";
        exit;
    }

    // Check if rating exists
    $check = $conn->prepare("SELECT * FROM ratings WHERE user_id=? AND restaurant_id=?");
    $check->bind_param("ii", $user_id, $restaurant_id);
    $check->execute();
    $result = $check->get_result();

    if ($result->num_rows > 0) {
        $stmt = $conn->prepare("UPDATE ratings SET rating=? WHERE user_id=? AND restaurant_id=?");
        $stmt->bind_param("iii", $rating, $user_id, $restaurant_id);
        $stmt->execute();
        echo "Rating updated";
    } else {
        $stmt = $conn->prepare("INSERT INTO ratings (user_id, restaurant_id, rating) VALUES (?, ?, ?)");
        $stmt->bind_param("iii", $user_id, $restaurant_id, $rating);
        $stmt->execute();
        echo "Rating saved";
    }
}
?>
