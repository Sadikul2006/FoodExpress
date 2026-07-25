<?php

include '../config/database_connection.php';
include '../config/pusher.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

header("Content-Type: application/json");

// ===========================
// Check Admin Login
// ===========================
if (!isset($_SESSION['admin_id'])) {
    echo json_encode([
        "type" => "error",
        "msg" => "Unauthorized access!"
    ]);
    exit();
}

$restaurant_id = (int) $_SESSION['admin_id'];

// ===========================
// Validate Order ID
// ===========================
if (!isset($_POST['order_id']) || !is_numeric($_POST['order_id'])) {
    echo json_encode([
        "type" => "error",
        "msg" => "Invalid Order ID."
    ]);
    exit();
}

$order_id = (int) $_POST['order_id'];

// ===========================
// Determine Action
// ===========================
$actions = [
    'process_order'  => 'Processing',
    'complete_order' => 'Completed',
    'cancel_order'   => 'Cancelled'
];

$status = null;

foreach ($actions as $action => $value) {
    if (isset($_POST[$action])) {
        $status = $value;
        break;
    }
}

if ($status === null) {
    echo json_encode([
        "type" => "error",
        "msg" => "Invalid action."
    ]);
    exit();
}

// ===========================
// Check Order Exists
// ===========================
$check = $conn->prepare("
    SELECT id
    FROM orders
    WHERE id = ? AND restaurant_id = ?
");

$check->bind_param("ii", $order_id, $restaurant_id);
$check->execute();

$result = $check->get_result();

if ($result->num_rows === 0) {

    $check->close();

    echo json_encode([
        "type" => "error",
        "msg" => "Order not found."
    ]);

    exit();
}

$check->close();

// ===========================
// Update Order Status
// ===========================
$stmt = $conn->prepare("
    UPDATE orders
    SET status = ?
    WHERE id = ? AND restaurant_id = ?
");

$stmt->bind_param(
    "sii",
    $status,
    $order_id,
    $restaurant_id
);

if ($stmt->execute() && $stmt->affected_rows > 0) {

    // ===========================
    // Send Pusher Notification
    // ===========================
    try {

        $pusher->trigger(
            'foodexpress',
            'order-status-updated',
            [
                'order_id' => $order_id,
                'status' => $status
            ]
        );

    } catch (Exception $e) {

        error_log("Pusher Error: " . $e->getMessage());

    }

    echo json_encode([
        "type" => "success",
        "msg" => "Order status updated successfully.",
        "status" => $status,
        "order_id" => $order_id
    ]);

} else {

    echo json_encode([
        "type" => "error",
        "msg" => "Failed to update order."
    ]);

}

$stmt->close();
$conn->close();

exit();