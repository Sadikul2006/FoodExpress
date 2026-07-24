<?php
include '../config/database_connection.php';
session_start();

header("Content-Type: application/json");

$restaurant_id = $_SESSION['admin_id'] ?? 1;

$min_order_amount = $_POST['min_order_amount'] ?? 0;
$delivery_fee = $_POST['delivery_fee'] ?? 0;
$delivery_radius = $_POST['delivery_radius'] ?? 0;
$prep_time = $_POST['prep_time'] ?? 45;
$enable_ordering = isset($_POST['enable_ordering']) ? 1 : 0;
$opening_time = $_POST['opening_time'] ?? '09:00';
$closing_time = $_POST['closing_time'] ?? '22:00';

$response = ["type" => "error", "msg" => "Something went wrong!"];

// Check if settings exists
$result = $conn->query("SELECT id FROM restaurant_settings WHERE restaurant_id = $restaurant_id");

if ($result->num_rows > 0) {

    // UPDATE
    $stmt = $conn->prepare("UPDATE restaurant_settings 
        SET min_order_amount=?, delivery_fee=?, delivery_radius=?, preparation_time=?, enable_ordering=?, opening_time=?, closing_time=? 
        WHERE restaurant_id=?");

    if (!$stmt) {
        echo json_encode(['type' => 'error', 'msg' => 'Prepare failed: '.$conn->error]);
        exit();
    }

    $stmt->bind_param("dddiissi",
        $min_order_amount,
        $delivery_fee,
        $delivery_radius,
        $prep_time,
        $enable_ordering,
        $opening_time,
        $closing_time,
        $restaurant_id
    );

    if ($stmt->execute()) {
        $response = ['type' => 'success', 'msg' => 'Settings updated successfully!'];
    } else {
        $response = ['type' => 'error', 'msg' => 'Update failed: '.$stmt->error];
    }

} else {

    // INSERT
    $stmt = $conn->prepare("INSERT INTO restaurant_settings 
        (restaurant_id, min_order_amount, delivery_fee, delivery_radius, preparation_time, enable_ordering, opening_time, closing_time) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?)");

    if (!$stmt) {
        echo json_encode(['type' => 'error', 'msg' => 'Prepare failed: '.$conn->error]);
        exit();
    }

    $stmt->bind_param("idddisss",
        $restaurant_id,
        $min_order_amount,
        $delivery_fee,
        $delivery_radius,
        $prep_time,
        $enable_ordering,
        $opening_time,
        $closing_time
    );

    if ($stmt->execute()) {
        $response = ['type' => 'success', 'msg' => 'Settings saved successfully!'];
    } else {
        $response = ['type' => 'error', 'msg' => 'Insert failed: '.$stmt->error];
    }
}

echo json_encode($response);
exit();
?>
