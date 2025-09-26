<?php
session_start();
include "database_connection.php";

$user_id = intval($_SESSION['user_id'] ?? 0);
$restaurant_id = intval($_SESSION['restaurant_id'] ?? 0);

if (!$user_id || !$restaurant_id) exit(json_encode(["status"=>"error","message"=>"User or restaurant not valid"]));


$fee_sql = "SELECT value FROM settings WHERE name = 'delivery_fee' LIMIT 1";
$fee_result = $conn->query($fee_sql);
if ($fee_result && $fee_result->num_rows > 0) {
    $delivery_fee = (float)$fee_result->fetch_assoc()['value'];
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

            echo json_encode(["status"=>"success","quantity"=>$quantity]);
        } else {
            echo json_encode(["status"=>"error"]);
        }
        exit;
    }

    if ($action === "remove") {
        $stmt = $conn->prepare("DELETE FROM cart WHERE item_id = ? AND user_id = ? AND restaurant_id = ?");
        $stmt->bind_param("iii", $item_id, $user_id, $restaurant_id);
        $stmt->execute();
        echo json_encode(["status"=>($stmt->affected_rows>0?"success":"error")]);
        exit;
    }
}

// Get cart summary
if (isset($_GET['action']) && $_GET['action'] === "get_summary") {
    $sql = "SELECT c.quantity, m.price 
            FROM cart c 
            JOIN items m ON c.item_id = m.id 
            WHERE c.user_id = ? AND c.restaurant_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $user_id, $restaurant_id);
    $stmt->execute();
    $result = $stmt->get_result();

    $subtotal = 0;
    while ($row = $result->fetch_assoc()) {
        $subtotal += $row['quantity'] * $row['price'];
    }

    $total = $subtotal + $delivery_fee;

    echo '
    <h3 class="summary-title">Order Summary</h3>
    <div class="coupon-row">
        <input id="coupon" placeholder="Coupon code" />
        <button id="apply-coupon">Apply</button>
    </div>
    <div class="summary-row">
        <span>Subtotal:</span>
        <span>₹'.number_format($subtotal,2).'</span>
    </div>
    <div class="summary-row">
        <span>Taxes:</span>
        <span>₹0.00</span>
    </div>
    <div class="summary-row">
        <span>Delivery Fee:</span>
        <span>₹'.number_format($delivery_fee,2).'</span>
    </div>
    <div class="summary-row total-row">
        <span>Total:</span>
        <span>₹'.number_format($total,2).'</span>
    </div>
    <button type="button" class="checkout-btn" id="checkoutButton">Proceed to Checkout</button>';
    exit;
}


if (isset($_POST['checkOut'])) {
    $sql = "SELECT c.quantity, m.price 
            FROM cart c 
            JOIN items m ON c.item_id = m.id 
            WHERE c.user_id = ? AND c.restaurant_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $user_id, $restaurant_id);
    $stmt->execute();
    $result = $stmt->get_result();

    $subtotal = 0;
    while ($row = $result->fetch_assoc()) {
        $subtotal += $row['quantity'] * $row['price'];
    }

    $total = $subtotal + $delivery_fee;

    echo $total;
}
?>
