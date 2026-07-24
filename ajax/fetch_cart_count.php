<?php
session_start();
include '../config/database_connection.php';

// User & Restaurant ID session থেকে
$user_id = $_SESSION['user_id'] ?? 0;
$restaurant_id = $_SESSION['restaurant_id'] ?? 0;

$total_cart_items = 0;

if ($user_id && $restaurant_id) {
    $stmt = $conn->prepare("SELECT COUNT(*) AS total FROM cart WHERE user_id = ? AND restaurant_id = ?");
    $stmt->bind_param("ii", $user_id, $restaurant_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($row = $result->fetch_assoc()) {
        $total_cart_items = $row['total'];
    }
    $stmt->close();
}

echo $total_cart_items;
?>