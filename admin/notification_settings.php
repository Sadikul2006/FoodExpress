<?php

include '../config/database_connection.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

header("Content-Type: application/json");

if (!isset($_SESSION['admin_id']) || !is_numeric($_SESSION['admin_id'])) {
    echo json_encode([
        "type" => "error",
        "msg" => "Unauthorized access!"
    ]);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode([
        "type" => "error",
        "msg" => "Invalid request method."
    ]);
    exit();
}

$restaurant_id = (int) $_SESSION['admin_id'];

$new_orders = isset($_POST['new_orders']) ? 1 : 0;
$order_cancellations = isset($_POST['order_cancellations']) ? 1 : 0;
$new_reservations = isset($_POST['new_reservations']) ? 1 : 0;
$customer_reviews = isset($_POST['customer_reviews']) ? 1 : 0;

$notification_emails = trim($_POST['notification_emails'] ?? '');

if ($notification_emails !== '') {

    $lines = preg_split('/\r\n|\r|\n/', $notification_emails);
    $validEmails = [];

    foreach ($lines as $line) {

        $line = trim($line);

        if ($line === '') {
            continue;
        }

        if (!filter_var($line, FILTER_VALIDATE_EMAIL)) {
            echo json_encode([
                "type" => "error",
                "msg" => "Invalid email address: " . $line
            ]);
            exit();
        }

        $validEmails[] = $line;
    }

    $notification_emails = implode("\n", $validEmails);
}

$check = $conn->prepare("
    SELECT id
    FROM notification_settings
    WHERE restaurant_id = ?
    LIMIT 1
");

if (!$check) {
    echo json_encode([
        "type" => "error",
        "msg" => "Database error."
    ]);
    exit();
}

$check->bind_param("i", $restaurant_id);
$check->execute();

$result = $check->get_result();

if ($result->num_rows > 0) {

    $row = $result->fetch_assoc();

    $check->close();

    $stmt = $conn->prepare("
        UPDATE notification_settings
        SET
            new_orders = ?,
            order_cancellations = ?,
            new_reservations = ?,
            customer_reviews = ?,
            notification_emails = ?
        WHERE id = ?
        AND restaurant_id = ?
    ");

    if (!$stmt) {
        echo json_encode([
            "type" => "error",
            "msg" => "Database error."
        ]);
        exit();
    }

    $stmt->bind_param(
        "iiiisii",
        $new_orders,
        $order_cancellations,
        $new_reservations,
        $customer_reviews,
        $notification_emails,
        $row['id'],
        $restaurant_id
    );

} else {

    $check->close();

    $stmt = $conn->prepare("
        INSERT INTO notification_settings
        (
            restaurant_id,
            new_orders,
            order_cancellations,
            new_reservations,
            customer_reviews,
            notification_emails
        )
        VALUES (?, ?, ?, ?, ?, ?)
    ");

    if (!$stmt) {
        echo json_encode([
            "type" => "error",
            "msg" => "Database error."
        ]);
        exit();
    }

    $stmt->bind_param(
        "iiiiss",
        $restaurant_id,
        $new_orders,
        $order_cancellations,
        $new_reservations,
        $customer_reviews,
        $notification_emails
    );
}

if ($stmt->execute()) {

    echo json_encode([
        "type" => "success",
        "msg" => "Notification settings saved successfully.",
        "data" => [
            "new_orders" => $new_orders,
            "order_cancellations" => $order_cancellations,
            "new_reservations" => $new_reservations,
            "customer_reviews" => $customer_reviews,
            "notification_emails" => $notification_emails
        ]
    ]);

} else {

    echo json_encode([
        "type" => "error",
        "msg" => "Failed to save settings. Please try again."
    ]);
}

$stmt->close();
$conn->close();

exit();