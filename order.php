<?php
session_start();
include 'database_connection.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: user_login_register.php");
    exit();
}

$user_id = $_SESSION['user_id'] ?? null;
$restaurant_id  = $_SESSION['restaurant_id'] ?? null;
$restaurant_name = $_SESSION['restaurant_name'] ?? null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $subtotal     = (float) $_POST['subtotal'];
    $delivery_fee = (float) $_POST['delivery_fee'];
    $total        = (float) $_POST['total'];

    // --- Orders table এ insert ---
    $sql = "INSERT INTO orders (user_id, restaurant_id, subtotal, delivery_fee, total, order_date, status) 
            VALUES (?, ?, ?, ?, ?, NOW(), 'Processing')";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iiddd", $user_id, $restaurant_id, $subtotal, $delivery_fee, $total);

    if ($stmt->execute()) {
        $order_id = $stmt->insert_id;

        // --- Cart থেকে items নিয়ে আসা ---
        $result = $conn->query("
            SELECT c.item_id, c.quantity, i.name, i.price, i.image
            FROM cart c
            JOIN items i ON c.item_id = i.id
            WHERE c.user_id = '$user_id' AND c.restaurant_id = '$restaurant_id'
        ");

        while ($row = $result->fetch_assoc()) {
            $item_id   = $row['item_id'];
            $qty       = $row['quantity'];
            $price     = $row['price'];
            $item_name = $row['name'];
            $item_img  = $row['image'];

            $sql2 = "INSERT INTO order_items (order_id, item_id, item_name, item_image, quantity, price, status, created_at)
                     VALUES (?, ?, ?, ?, ?, ?, 'Pending', NOW())";
            $stmt2 = $conn->prepare($sql2);
            $stmt2->bind_param("iissid", $order_id, $item_id, $item_name, $item_img, $qty, $price);
            $stmt2->execute();
        }

        // --- Cart খালি করা ---
        $conn->query("DELETE FROM cart WHERE user_id='$user_id' AND restaurant_id='$restaurant_id'");
        header("Location: order.php");
        exit();
    } else {
        echo "Order failed. Please try again.";
    }
}
?>

<?php include 'navber.php' ?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order History</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="app_style/order.css">
</head>

<body>
    <div class="container">
        <div class="page-header">
            <h1 class="page-title">Order History</h1>
            <div style="display: flex; gap: 10px;">
                <button class="action-btn btn-outline">
                    <i class="fas fa-filter"></i> Filter
                </button>
                <button class="action-btn btn-outline">
                    <i class="fas fa-search"></i> Search
                </button>
            </div>
        </div>

        <?php
        // Get all orders for the user
        $orders_stmt = $conn->prepare("SELECT o.*, a.restaurant_name 
                                    FROM orders o
                                    JOIN admin a ON o.restaurant_id = a.restaurant_id
                                    WHERE o.user_id = ?
                                    ORDER BY o.order_date DESC");
        $orders_stmt->bind_param("i", $user_id);
        $orders_stmt->execute();
        $orders_result = $orders_stmt->get_result();

        if ($orders_result->num_rows > 0) {
            while ($order_row = $orders_result->fetch_assoc()) {
                $order_id = $order_row['id'];
                $subtotal = $order_row['subtotal'];
                $delivery_fee = $order_row['delivery_fee'];
                $taxes = $order_row['taxes']?? 0;
                $total = $order_row['total'];
                $date_time = $order_row['order_date'];
                $status = $order_row['status'];
                $restaurant_name = $order_row['restaurant_name'];

                // Determine status class
                $status_class = "";
                if ($status == "Completed") {
                    $status_class = "status-completed";
                } elseif ($status == "Processing") {
                    $status_class = "status-processing";
                } elseif ($status == "Cancelled") {
                    $status_class = "status-cancelled";
                }
        ?>
                <div class="order-card">
                    <div class="order-header">
                        <div style="display: flex; align-items: center; flex-wrap: wrap; gap:10px;">
                            <span class="order-id">Order #<?php echo $order_id + 1000; ?></span>
                            <span class="order-date"><?php echo $date_time; ?></span>
                            <span class="restaurant-name">• <?php echo $restaurant_name; ?></span>
                            <span class="order-status <?php echo $status_class; ?>">
                                <?php if ($status == "Completed") { ?>
                                    <i class="fas fa-check-circle"></i>
                                <?php } elseif ($status == "Processing") { ?>
                                    <i class="fas fa-spinner"></i>
                                <?php } elseif ($status == "Cancelled") { ?>
                                    <i class="fas fa-times-circle"></i>
                                <?php } ?>
                                <?php echo $status; ?>
                            </span>
                        </div>
                    </div>
                    <div class="order-body">
                        <div class="order-details">
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
                                        <?php
                                        // Get items for this order
                                        $items_stmt = $conn->prepare("SELECT * FROM order_items WHERE order_id = ?");
                                        $items_stmt->bind_param("i", $order_id);
                                        $items_stmt->execute();
                                        $items_result = $items_stmt->get_result();

                                        if ($items_result->num_rows > 0) {
                                            while ($item_row = $items_result->fetch_assoc()) {
                                                $item_name = $item_row['item_name'];
                                                $item_description = $item_row['description'];
                                                $item_img = $item_row['item_image'];
                                                $item_price = $item_row['price'];
                                                $item_qty = $item_row['quantity'];
                                        ?>
                                                <tr>
                                                    <td>
                                                        <img src="/Restaurant/admin/<?php echo $item_img; ?>" alt="<?php echo $item_name; ?>" class="item-image">
                                                    </td>
                                                    <td>
                                                        <div class="item-name"><?php echo $item_name; ?></div>
                                                        <div class="item-description"><?php echo $item_description; ?></div>
                                                    </td>
                                                    <td class="item-quantity"><?php echo $item_qty; ?></td>
                                                    <td class="item-price">₹<?php echo $item_price; ?></td>
                                                </tr>
                                        <?php
                                            }
                                        }
                                        ?>
                                    </tbody>
                                </table>
                            </div>
                            <div class="order-summary">
                                <div class="summary-row">
                                    <span class="summary-label">Subtotal</span>
                                    <span class="summary-value">₹<?php echo $subtotal; ?></span>
                                </div>
                                <div class="summary-row">
                                    <span class="summary-label">Delivery Fee</span>
                                    <span class="summary-value">₹<?php echo $delivery_fee; ?></span>
                                </div>
                                <div class="summary-row">
                                    <span class="summary-label">Taxes</span>
                                    <span class="summary-value">₹<?php echo $taxes; ?></span>
                                </div>
                                <div class="summary-row total-row">
                                    <span class="summary-label">Total</span>
                                    <span class="summary-value">₹<?php echo $total; ?></span>
                                </div>
                            </div>
                        </div>
                        <div class="order-actions">
                            <button class="action-btn btn-outline">
                                <i class="fas fa-eye"></i> View Details
                            </button>
                            <?php if ($status == "Completed" || $status == "Cancelled") { ?>
                                <button class="action-btn btn-primary">
                                    <i class="fas fa-redo"></i> Reorder
                                </button>
                            <?php } else if ($status == "Processing") { ?>
                                <button class="action-btn btn-outline">
                                    <i class="fas fa-times"></i> Cancel Order
                                </button>
                            <?php } ?>
                        </div>
                    </div>
                </div>
            <?php
            }
        } else {
            // No orders found
            ?>
            <div class="order-card">
                <div class="empty-state">
                    <i class="fas fa-clipboard-list"></i>
                    <h3>No Orders Yet</h3>
                    <p>You haven't placed any orders yet. Start exploring restaurants to place your first order!</p>
                    <button class="action-btn btn-primary">
                        Browse Restaurants
                    </button>
                </div>
            </div>
        <?php
        }
        ?>
    </div>
</body>

</html>