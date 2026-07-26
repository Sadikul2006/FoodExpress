<?php
session_start();
include 'config/database_connection.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: user_login_register.php");
    exit();
}

$user_id = $_SESSION['user_id'] ?? null;
$order_id = $_GET['order_id'] ?? null;

if (!$order_id) {
    echo "<p>Invalid order ID</p>";
    exit();
}

// Fetch order details
$order_sql = "SELECT o.*, a.restaurant_name
              FROM orders o
              JOIN restaurant_info a ON o.restaurant_id = a.restaurant_id
              WHERE o.id = ? AND o.user_id = ?";
$order_stmt = $conn->prepare($order_sql);
$order_stmt->bind_param("ii", $order_id, $user_id);
$order_stmt->execute();
$order_result = $order_stmt->get_result();

if ($order_result->num_rows == 0) {
    echo "<p>Order not found</p>";
    exit();
}

$order = $order_result->fetch_assoc();
$order_stmt->close();

// Assign order values
$restaurant_name = $order['restaurant_name'];
$status = $order['status'];
$status_class = strtolower($status);
$date_time = date("M d, Y h:i A", strtotime($order['order_date']));
$subtotal = $order['subtotal'];
$delivery_fee = $order['delivery_fee'];
$taxes = $order['taxes'];
$total = $order['total'];
$special_instructions = $order['instructions'];

// Fetch order items
$items_sql = "SELECT * FROM order_items WHERE order_id = ?";
$items_stmt = $conn->prepare($items_sql);
$items_stmt->bind_param("i", $order_id);
$items_stmt->execute();
$items_result = $items_stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Details</title>
    <link rel="stylesheet" href="assets/css/order.css">
    <link rel="stylesheet" href="assets/css/order_details.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>

<body>
    <div class="nav">
        <a href="order_history.php" class="back-btn">
            <i class="fas fa-arrow-left"></i>
        </a>
        <h1 class="page-title">Order Details</h1>
        <div style="display: flex; gap: 10px;">
            <button class="action-btn btn-outline">
                <i class="fas fa-info-circle"></i> Help
            </button>
        </div>
    </div>

    <div class="container">
        <div class="order-header">
            <div style="display: flex; align-items: center; flex-wrap: wrap; gap:10px;">
                <span class="order-id">Order #<?php echo $order_id + 1000; ?></span>
                <span class="restaurant-name"><i class="fa-solid fa-utensils"></i> <?= htmlspecialchars($restaurant_name) ?></span>
                <span class="order-status status-<?php echo $status_class; ?>">
                    <?php if ($status == "Completed") { ?>
                        <i class="fas fa-check-circle"></i>
                    <?php } elseif ($status == "Processing") { ?>
                        <i class="fas fa-spinner"></i>
                    <?php } elseif ($status == "Cancelled") { ?>
                        <i class="fas fa-times-circle"></i>
                    <?php } elseif ($status == "Pending") { ?>
                        <i class="fas fa-clock"></i>
                    <?php } ?>
                    <?php echo $status; ?>
                </span>
                <span class="order-date"><?php echo $date_time; ?></span>
            </div>
        </div>

        <div class="order-items">
            <table class="item-list">
                <thead>
                    <tr>
                        <th style="width: 70px;"></th>
                        <th>Item</th>
                        <th>Qty</th>
                        <th>Price</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($item = $items_result->fetch_assoc()) { 
                        $item_price = $item['price'] - ($item['price'] * $item['discount'] / 100);?>
                        <tr>
                            <td>
                                <img src="/Restaurant/admin/<?= htmlspecialchars($item['item_image']) ?>" alt="<?php echo $item['item_name']; ?>" class="item-image">
                            </td>
                            <td>
                                <div class="item-name"><?= htmlspecialchars($item['item_name']) ?></div>
                                <div class="item-description"><?= htmlspecialchars($item['description']) ?></div>
                            </td>
                            <td class="item-quantity"><?php echo $item['quantity']; ?></td>
                            <td class="item-price">₹<?= number_format($item_price,2); ?></td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>

        <div class="order-summary">
            <div class="summary-row">
                <span class="summary-label">Subtotal</span>
                <span class="summary-value">₹<?= number_format($subtotal,2) ?></span>
            </div>
            <div class="summary-row">
                <span class="summary-label">Delivery Fee</span>
                <span class="summary-value">₹<?= number_format($delivery_fee,2) ?></span>
            </div>
            <div class="summary-row">
                <span class="summary-label">Taxes</span>
                <span class="summary-value">₹<?= number_format($taxes,2) ?></span>
            </div>
            <div class="summary-row total-row">
                <span class="summary-label">Total</span>
                <span class="summary-value">₹<?= number_format($total,2) ?></span>
            </div>
        </div>

        <div class="order-info-container">
            <div class="card">
                <h3><i class="fas fa-sticky-note"></i> Special Instructions</h3>
                <div class="instruction-box">
                    <?php echo htmlspecialchars($special_instructions); ?>
                </div>
            </div>
            <div class="card">
                <h3><i class="fas fa-map-marker-alt"></i> Delivery Address</h3>
                <div class="instruction-box">
                    <?php
                    $address_stmt = $conn->prepare(" SELECT * FROM address WHERE id = ? LIMIT 1");

                    $address_stmt->bind_param("i", $order['address_id']);
                    $address_stmt->execute();

                    $address_result = $address_stmt->get_result();

                    if ($address_result->num_rows > 0) {

                        $row = $address_result->fetch_assoc();
                    ?>
                        <div class="address-box">
                            <p class="address-text">
                                <?= htmlspecialchars($row['name']); ?><br>
                                <?= htmlspecialchars($row['street']); ?><br>
                                <?= htmlspecialchars($row['city']); ?><br>
                                <?= htmlspecialchars($row['phone']); ?>
                            </p>
                        </div>
                    <?php
                    } else {
                        echo "<p>Address not found.</p>";
                    }

                    $address_stmt->close();
                    ?>

                </div>
            </div>

        </div>

        <div class="order-actions">
            <a href="order_history.php" class="action-btn btn-primary"><i class="fas fa-arrow-left"></i> Back</a>
            <?php if ($order['status'] == "Completed" || $order['status'] == "Cancelled") { ?>
                <button class="action-btn btn-primary"><i class="fas fa-redo"></i> Reorder</button>
            <?php } ?>
        </div>
    </div>

    <?php include 'includes/footer_nav.php'; ?>

</body>

</html>


