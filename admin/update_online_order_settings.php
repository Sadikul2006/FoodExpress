<?php
include '../config/database_connection.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

header("Content-Type: application/json");

// ===========================
// Auth check
// ===========================
if (!isset($_SESSION['admin_id'])) {
    echo json_encode([
        "type" => "error",
        "msg"  => "Unauthorized access!"
    ]);
    exit();
}

$restaurant_id = (int) $_SESSION['admin_id'];

// ===========================
// Only POST
// ===========================
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode([
        "type" => "error",
        "msg"  => "Invalid request method."
    ]);
    exit();
}

// ===========================
// Collect inputs
// ===========================
$min_order_amount = isset($_POST['min_order_amount']) ? (float) $_POST['min_order_amount'] : 0;
$delivery_fee     = isset($_POST['delivery_fee'])     ? (float) $_POST['delivery_fee']     : 0;
$delivery_radius  = isset($_POST['delivery_radius'])  ? (float) $_POST['delivery_radius']  : 0;
$prep_time        = isset($_POST['prep_time'])        ? (string) $_POST['prep_time']       : '30';
$opening_time     = $_POST['opening_time'] ?? '09:00';
$closing_time     = $_POST['closing_time'] ?? '22:00';
$enable_ordering  = isset($_POST['enable_ordering']) ? 1 : 0;

// ===========================
// Validate
// ===========================
if ($min_order_amount < 0 || $delivery_fee < 0 || $delivery_radius < 0) {
    echo json_encode([
        "type" => "error",
        "msg"  => "Amounts and radius cannot be negative."
    ]);
    exit();
}

// Prep time must be one of allowed values
if (!in_array($prep_time, ['15', '30', '45', '60'], true)) {
    echo json_encode([
        "type" => "error",
        "msg"  => "Invalid preparation time."
    ]);
    exit();
}

// Time format HH:MM
function isValidTime($t) {
    return (bool) preg_match('/^([01]\d|2[0-3]):[0-5]\d$/', $t);
}

if (!isValidTime($opening_time) || !isValidTime($closing_time)) {
    echo json_encode([
        "type" => "error",
        "msg"  => "Invalid business hours."
    ]);
    exit();
}

if ($opening_time === $closing_time) {
    echo json_encode([
        "type" => "error",
        "msg"  => "Opening and closing time cannot be the same."
    ]);
    exit();
}

// Time inputs need seconds for MySQL TIME column
$opening_time_db = $opening_time . ':00';
$closing_time_db = $closing_time . ':00';

// ===========================
// Check if row exists
// ===========================
$check = $conn->prepare("
    SELECT id FROM restaurant_settings
    WHERE restaurant_id = ?
    LIMIT 1
");
$check->bind_param("i", $restaurant_id);
$check->execute();
$result = $check->get_result();

if ($result->num_rows > 0) {

    // ---------- UPDATE ----------
    $row = $result->fetch_assoc();
    $check->close();

    $stmt = $conn->prepare("
        UPDATE restaurant_settings
        SET min_order_amount = ?,
            delivery_fee     = ?,
            delivery_radius  = ?,
            preparation_time = ?,
            opening_time     = ?,
            closing_time     = ?,
            enable_ordering  = ?
        WHERE id = ? AND restaurant_id = ?
    ");

    $stmt->bind_param(
        "dddsssiii",
        $min_order_amount,
        $delivery_fee,
        $delivery_radius,
        $prep_time,
        $opening_time_db,
        $closing_time_db,
        $enable_ordering,
        $row['id'],
        $restaurant_id
    );

} else {

    // ---------- INSERT ----------
    $check->close();

    $stmt = $conn->prepare("
        INSERT INTO restaurant_settings
            (restaurant_id, min_order_amount, delivery_fee, delivery_radius,
             preparation_time, opening_time, closing_time, enable_ordering)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?)
    ");

    $stmt->bind_param(
        "idddsssi",
        $restaurant_id,
        $min_order_amount,
        $delivery_fee,
        $delivery_radius,
        $prep_time,
        $opening_time_db,
        $closing_time_db,
        $enable_ordering
    );
}

// ===========================
// Execute
// ===========================
if ($stmt->execute()) {

    echo json_encode([
        "type" => "success",
        "msg"  => "Online ordering settings saved successfully.",
        "data" => [
            "min_order_amount" => $min_order_amount,
            "delivery_fee"     => $delivery_fee,
            "delivery_radius"  => $delivery_radius,
            "prep_time"        => $prep_time,
            "opening_time"     => $opening_time,
            "closing_time"     => $closing_time,
            "enable_ordering"  => $enable_ordering
        ]
    ]);

} else {

    echo json_encode([
        "type" => "error",
        "msg"  => "Failed to save settings. Please try again."
    ]);

}

$stmt->close();
$conn->close();
exit();