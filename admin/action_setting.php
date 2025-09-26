<?php
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    include 'database_connection.php';
    session_start();

    $settings = [
        "min_order_amount" => $_POST["min_order_amount"],
        "delivery_fee" => $_POST["delivery_fee"],
        "delivery_radius" => $_POST["delivery_radius"],
        "prep_time" => $_POST["prep_time"]
    ];

    $stmt = $conn->prepare("
        INSERT INTO settings (name, value) VALUES (?, ?)
        ON DUPLICATE KEY UPDATE value = VALUES(value)
    ");

    if (!$stmt) {
        $_SESSION['error'] = "Prepare failed: " . $conn->error;
        header("Location: admin_setting.php");
        exit;
    }

    foreach ($settings as $name => $value) {
        $stmt->bind_param("ss", $name, $value);
        $stmt->execute();
    }

    $stmt->close();

    $_SESSION['success'] = "Settings saved successfully!";
    header("Location: admin_setting.php");
}
?>
