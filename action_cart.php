<?php
session_start();
include 'database_connection.php';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $item_id = isset($_POST['items_id']) ? (int)$_POST['items_id'] : null;

    if (!isset($_SESSION['user_id']) || !isset($_SESSION['restaurant_id']) || !$item_id) {
        http_response_code(400);
        echo "Missing data.";
        exit;
    }

    $user_id = $_SESSION['user_id'];
    $restaurant_id = $_SESSION['restaurant_id'];

    $stmt = $conn->prepare("SELECT * FROM cart WHERE item_id = ? AND user_id = ? AND restaurant_id = ?");
    $stmt->bind_param("iii", $item_id, $user_id, $restaurant_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $_SESSION['success'] = "Already added to your cart.";
    } else {
        $insert_stmt = $conn->prepare("INSERT INTO cart (user_id, item_id, restaurant_id, quantity) VALUES (?, ?, ?, 1)");
        $insert_stmt->bind_param("iii", $user_id, $item_id, $restaurant_id);
        if ($insert_stmt->execute()) {
            $_SESSION['success'] =  "Added to cart.";
        } else {
            http_response_code(500);
            echo "Error adding to cart.";
        }
        $insert_stmt->close();
    }

    $stmt->close();
    $conn->close();
}
?>
