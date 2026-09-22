<?php
session_start();
include 'config/database_connection.php';
include 'config/pusher.php';

// Auth guard
if (!isset($_SESSION['user_id'])) {
    header("Location: user_login_register.php");
    exit();
}

$user_id         = $_SESSION['user_id'] ?? null;
$restaurant_id   = $_SESSION['restaurant_id'] ?? null;
$restaurant_name = $_SESSION['restaurant_name'] ?? null;


// Trigger order mail (background, fire & forget)

function triggerOrderMail($order_id)
{
    $url = 'http://127.0.0.1/Restaurant/send_order_mail.php';

    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => http_build_query(['order_id' => $order_id]),
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT        => 1,   
        CURLOPT_NOSIGNAL       => 1,
    ]);
    curl_exec($ch);
    curl_close($ch);
}

// Handle order confirmation (AJAX)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['order'] ?? '') === 'confirm') {

    header('Content-Type: application/json');

    $response = ['status' => 'error', 'message' => 'Unknown error'];

    if (!$user_id || !$restaurant_id) {
        echo json_encode(['status' => 'error', 'message' => 'User or restaurant not set']);
        exit();
    }

    // ---- Inputs ----
    $subtotal     = (float) ($_POST['subtotal'] ?? 0);
    $taxes        = (float) ($_POST['taxes'] ?? 0);
    $delivery_fee = (float) ($_POST['delivery_fee'] ?? 0);
    $total        = (float) ($_POST['total'] ?? 0);
    $instructions = trim($_POST['instructions'] ?? '');

    // ---- Default address ----
    $addr_stmt = $conn->prepare("SELECT id FROM address WHERE user_id = ? AND is_default = 1 LIMIT 1");
    $addr_stmt->bind_param("i", $user_id);
    $addr_stmt->execute();
    $address = $addr_stmt->get_result()->fetch_assoc();
    $addr_stmt->close();

    if (!$address) {
        echo json_encode(['status' => 'error', 'message' => 'No default address found']);
        exit();
    }

    $address_id = (int) $address['id'];

    // ---- Transaction ----
    $conn->begin_transaction();

    try {
        // Insert order
        $stmt = $conn->prepare("
            INSERT INTO orders 
                (user_id, restaurant_id, subtotal, taxes, delivery_fee, total, instructions, address_id, order_date, status) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW(), 'Pending')
        ");
        $stmt->bind_param(
            "iiddddsi",
            $user_id, $restaurant_id, $subtotal, $taxes,
            $delivery_fee, $total, $instructions, $address_id
        );

        if (!$stmt->execute()) throw new Exception('Failed to create order');

        $order_id = $stmt->insert_id;
        $stmt->close();

        // Copy cart → order_items
        $cart_stmt = $conn->prepare("
            SELECT c.item_id, c.quantity, i.name, i.price, i.discount, i.image, i.description 
            FROM cart c 
            JOIN items i ON c.item_id = i.id 
            WHERE c.user_id = ? AND c.restaurant_id = ?
        ");
        $cart_stmt->bind_param("ii", $user_id, $restaurant_id);
        $cart_stmt->execute();
        $cart_result = $cart_stmt->get_result();

        if ($cart_result->num_rows === 0) throw new Exception('Cart is empty');

        $item_stmt = $conn->prepare("
            INSERT INTO order_items 
                (order_id, item_id, item_name, item_image, description, quantity, price, discount) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)
        ");

        while ($row = $cart_result->fetch_assoc()) {
            $item_stmt->bind_param(
                "iisssidi",
                $order_id, $row['item_id'], $row['name'], $row['image'],
                $row['description'], $row['quantity'], $row['price'], $row['discount']
            );
            if (!$item_stmt->execute()) throw new Exception('Failed to insert order item');
        }

        $item_stmt->close();
        $cart_stmt->close();

        // Clear cart
        $del_stmt = $conn->prepare("DELETE FROM cart WHERE user_id = ? AND restaurant_id = ?");
        $del_stmt->bind_param("ii", $user_id, $restaurant_id);
        $del_stmt->execute();
        $del_stmt->close();

        // Commit
        $conn->commit();

        // Pusher notification
        try {
            $pusher->trigger('foodexpress', 'new-order', [
                'order_id'      => $order_id,
                'restaurant_id' => $restaurant_id,
                'user_id'       => $user_id,
                'status'        => 'Pending',
                'total'         => $total
            ]);
        } catch (Exception $e) {
            error_log("Pusher Error: " . $e->getMessage());
        }

        // Fire mail in background
        triggerOrderMail($order_id);

        $response = [
            'status'   => 'success',
            'message'  => 'Order placed successfully',
            'order_id' => $order_id
        ];

    } catch (Exception $e) {
        $conn->rollback();
        error_log("Order Error: " . $e->getMessage());
        $response = ['status' => 'error', 'message' => 'Order failed: ' . $e->getMessage()];
    }

    echo json_encode($response);
    exit();
}

// ===========================
// Fetch user's orders
// ===========================
$orders_stmt = $conn->prepare("
    SELECT o.*, a.restaurant_name
    FROM orders o
    JOIN restaurant_info a ON o.restaurant_id = a.restaurant_id
    WHERE o.user_id = ?
    ORDER BY o.order_date DESC
");
$orders_stmt->bind_param("i", $user_id);
$orders_stmt->execute();
$orders_result = $orders_stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order History</title>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/order.css">
</head>

<body>

    <!-- Top nav -->
    <div class="nav">
        <a href="restaurant_menu.php" class="back-btn">
            <i class="fas fa-arrow-left"></i>
        </a>

        <h1 class="page-title">My Orders</h1>

        <div style="display: flex; gap: 10px;">
            <button class="action-btn btn-outline">
                <i class="fas fa-filter"></i> Filter
            </button>
        </div>
    </div>

    <!-- Orders container -->
    <div class="container">

        <?php if ($orders_result->num_rows > 0): ?>

            <?php while ($order_row = $orders_result->fetch_assoc()): ?>

                <?php
                    $order_id       = (int) $order_row['id'];
                    $subtotal       = $order_row['subtotal'];
                    $delivery_fee   = $order_row['delivery_fee'];
                    $taxes          = $order_row['taxes'] ?? 0;
                    $total          = $order_row['total'];
                    $status         = $order_row['status'];
                    $restaurant_nm  = $order_row['restaurant_name'];
                    $status_class   = strtolower($status);
                    $date_time      = date("M d, Y", strtotime($order_row['order_date']));
                ?>

                <div class="order-card">

                    <!-- Header -->
                    <div class="order-header">
                        <div style="display:flex;align-items:center;flex-wrap:wrap;gap:10px;">

                            <span class="restaurant-name">
                                <i class="fa-solid fa-utensils"></i>
                                <?= htmlspecialchars($restaurant_nm, ENT_QUOTES, 'UTF-8') ?>
                            </span>

                            <span class="order-status status-<?= $status_class ?>">
                                <?php if ($status === "Completed"): ?>
                                    <i class="fas fa-check-circle"></i>
                                <?php elseif ($status === "Processing"): ?>
                                    <i class="fas fa-spinner"></i>
                                <?php elseif ($status === "Cancelled"): ?>
                                    <i class="fas fa-times-circle"></i>
                                <?php elseif ($status === "Pending"): ?>
                                    <i class="fas fa-clock"></i>
                                <?php endif; ?>
                                <?= htmlspecialchars($status, ENT_QUOTES, 'UTF-8') ?>
                            </span>

                            <span class="order-date">
                                <?= $date_time ?>
                            </span>
                        </div>
                    </div>

                    <!-- Body -->
                    <div class="order-body">
                        <div class="order-details">
                            <div class="order-items">
                                <table class="item-list">
                                    <thead>
                                        <tr>
                                            <th style="width:70px;"></th>
                                            <th>Item</th>
                                            <th>Qty</th>
                                            <th>Price</th>
                                        </tr>
                                    </thead>

                                    <tbody>
                                        <?php
                                        $items_stmt = $conn->prepare("SELECT * FROM order_items WHERE order_id = ?");
                                        $items_stmt->bind_param("i", $order_id);
                                        $items_stmt->execute();
                                        $items_result = $items_stmt->get_result();

                                        while ($item_row = $items_result->fetch_assoc()):
                                            $item_name  = $item_row['item_name'];
                                            $item_desc  = $item_row['description'];
                                            $item_img   = $item_row['item_image'];
                                            $item_qty   = (int) $item_row['quantity'];
                                            $item_price = $item_row['price'] - ($item_row['price'] * $item_row['discount'] / 100);
                                        ?>
                                            <tr>
                                                <td>
                                                    <img
                                                        src="/Restaurant/admin/<?= htmlspecialchars($item_img, ENT_QUOTES, 'UTF-8') ?>"
                                                        alt="<?= htmlspecialchars($item_name, ENT_QUOTES, 'UTF-8') ?>"
                                                        class="item-image"
                                                    >
                                                </td>

                                                <td>
                                                    <div class="item-name">
                                                        <?= htmlspecialchars($item_name, ENT_QUOTES, 'UTF-8') ?>
                                                    </div>
                                                    <div class="item-description">
                                                        <?= htmlspecialchars($item_desc, ENT_QUOTES, 'UTF-8') ?>
                                                    </div>
                                                </td>

                                                <td class="item-quantity"><?= $item_qty ?></td>

                                                <td class="item-price">
                                                    ₹<?= number_format($item_price, 2) ?>
                                                </td>
                                            </tr>
                                        <?php endwhile; ?>

                                        <?php $items_stmt->close(); ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <!-- Actions -->
                        <div class="order-actions">
                            <a href="order_details.php?order_id=<?= $order_id ?>" class="action-btn btn-outline">
                                <i class="fas fa-eye"></i> View Details
                            </a>

                            <?php if ($status === "Completed" || $status === "Cancelled"): ?>
                                <button class="action-btn btn-primary">
                                    <i class="fas fa-redo"></i> Reorder
                                </button>
                            <?php elseif ($status === "Processing"): ?>
                                <button class="action-btn btn-outline">
                                    <i class="fas fa-times"></i> Cancel Order
                                </button>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

            <?php endwhile; ?>

        <?php else: ?>

            <!-- Empty state -->
            <div class="order-card">
                <div class="empty-state">
                    <i class="fas fa-clipboard-list"></i>
                    <h3>No Orders Yet</h3>
                    <p>
                        You haven't placed any orders yet.
                        Start exploring restaurants to place
                        your first order!
                    </p>
                    <a href="restaurant_menu.php">
                        <button class="action-btn btn-primary">Order Now</button>
                    </a>
                </div>
            </div>

        <?php endif; ?>

        <?php $orders_stmt->close(); ?>
    </div>

    <?php include 'includes/footer_nav.php'; ?>

</body>

</html>