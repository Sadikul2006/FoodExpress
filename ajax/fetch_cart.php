<?php
session_start();
include "../config/database_connection.php";

$user_id = intval($_SESSION['user_id'] ?? 0);
$restaurant_id = intval($_SESSION['restaurant_id'] ?? 0);

if (!$user_id || !$restaurant_id) exit(json_encode(["status" => "error", "message" => "User or restaurant not valid"]));

$settings_sql = "SELECT delivery_fee FROM restaurant_settings WHERE restaurant_id = ?";
$stmt2 = $conn->prepare($settings_sql);
$stmt2->bind_param("i", $restaurant_id);
$stmt2->execute();
$settings_result = $stmt2->get_result();
$settings = $settings_result->fetch_assoc();
$stmt2->close();

$delivery_fee = $settings['delivery_fee'] ?? 0;

function calculateSubtotal($conn, $user_id, $restaurant_id)
{
    $sql = "SELECT c.quantity, m.price, m.discount
            FROM cart c
            JOIN items m ON c.item_id = m.id
            WHERE c.user_id = ? AND c.restaurant_id = ?";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $user_id, $restaurant_id);
    $stmt->execute();
    $result = $stmt->get_result();

    $subtotal = 0;

    while ($row = $result->fetch_assoc()) {

        $final_price = $row['price'] - ($row['price'] * $row['discount'] / 100);

        $subtotal += $final_price * $row['quantity'];
    }

    $stmt->close();

    return $subtotal;
}


// Quantity update or remove
if (isset($_POST['item_id']) && isset($_POST['action'])) {
    $item_id = intval($_POST['item_id']);
    $action = $_POST['action'];

    if ($action === "increase" || $action === "decrease") {
        $sql = "SELECT quantity FROM cart WHERE item_id = ? AND user_id = ? AND restaurant_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("iii", $item_id, $user_id, $restaurant_id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($row = $result->fetch_assoc()) {
            $quantity = $row['quantity'];
            if ($action === "increase") $quantity++;
            elseif ($action === "decrease" && $quantity > 1) $quantity--;

            $update = $conn->prepare("UPDATE cart SET quantity = ? WHERE item_id = ? AND user_id = ? AND restaurant_id = ?");
            $update->bind_param("iiii", $quantity, $item_id, $user_id, $restaurant_id);
            $update->execute();
            $update->close();
            $stmt->close();

            echo json_encode(["status" => "success", "quantity" => $quantity]);
        } else {
            echo json_encode(["status" => "error"]);
        }
        exit;
    }

    if ($action === "remove") {
        $stmt = $conn->prepare("DELETE FROM cart WHERE item_id = ? AND user_id = ? AND restaurant_id = ?");
        $stmt->bind_param("iii", $item_id, $user_id, $restaurant_id);
        $stmt->execute();

        $status = ($stmt->affected_rows > 0) ? "success" : "error";

        $stmt->close();

        echo json_encode([
            "status" => $status
        ]);

        exit();
    }
}

// Get cart summary (JSON response)
if (isset($_GET['action']) && $_GET['action'] === "get_summary") {

    $subtotal = calculateSubtotal($conn, $user_id, $restaurant_id);

    $total = $subtotal + $delivery_fee;

    $response = [
        "subtotal"      => $subtotal,
        "taxes"         => 0,
        "delivery_fee"  => $delivery_fee,
        "total"         => $total
    ];

    header("Content-Type: application/json");
    echo json_encode($response);
    exit();
}


// check min order amount 
if (isset($_POST['min_amount'])) {
    $total_amount = floatval($_POST['total']);


    $query = "SELECT min_order_amount FROM restaurant_settings WHERE restaurant_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $restaurant_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();

    $min_order = floatval($row['min_order_amount'] ?? 0);

    if ($total_amount < $min_order) {
        echo json_encode(['success' => false, 'min_order' => $min_order]);
    } else {
        echo json_encode(['success' => true]);
    }
    exit();
}

// check restaurant Open/Close
if (isset($_POST['check_restaurant_status'])) {
    $response = ['status' => false, 'opening_time' => null];

    $stmt = $conn->prepare("SELECT opening_time, closing_time FROM restaurant_settings WHERE restaurant_id = ?");
    $stmt->bind_param("i", $restaurant_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
        date_default_timezone_set('Asia/Kolkata');
        $current_time = date('H:i:s');
        $opening_time = $row['opening_time'];
        $closing_time = $row['closing_time'];

        if ($current_time >= $opening_time && $current_time <= $closing_time) {
            $response['status'] = true;
        } else {
            $response['status'] = false;
            $response['opening_time'] = date("h:i A", strtotime($opening_time));
        }
    }
    $stmt->close();

    echo json_encode($response);
    exit();
}

// check user address given or not
if (isset($_POST['is_address'])) {
    $response = ['status' => false];
    $stmt = $conn->prepare("SELECT id FROM address WHERE user_id = ? AND is_default = 1 LIMIT 1");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $response['status'] = true;
    }
    $stmt->close();
    echo json_encode($response);
    exit();
}


if (isset($_POST['checkOut'])) {

    $subtotal = calculateSubtotal($conn, $user_id, $restaurant_id);

    $total = $subtotal + $delivery_fee;

    echo $total;
    exit();
}
