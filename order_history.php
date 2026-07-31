<?php
session_start();
include 'config/database_connection.php';
include 'config/pusher.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: user_login_register.php");
    exit();
}

$user_id = $_SESSION['user_id'] ?? null;
$restaurant_id  = $_SESSION['restaurant_id'] ?? null;
$restaurant_name = $_SESSION['restaurant_name'] ?? null;

// Handle AJAX order request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['order']) && $_POST['order'] === 'confirm') {
    header('Content-Type: application/json');

    $response = ['status' => 'error', 'message' => 'Unknown error'];

    // Validate required data
    if (!$user_id || !$restaurant_id) {
        $response['message'] = 'User or restaurant not set';
        echo json_encode($response);
        exit();
    }

    $subtotal     = floatval($_POST['subtotal'] ?? 0);
    $taxes        = floatval($_POST['taxes'] ?? 0);
    $delivery_fee = floatval($_POST['delivery_fee'] ?? 0);
    $total        = floatval($_POST['total'] ?? 0);
    $instructions = trim($_POST['instructions'] ?? '');
    
    // Get user's default address
    $address_sql = "SELECT * FROM address WHERE user_id = ? AND is_default = 1 LIMIT 1";
    $address_stmt = $conn->prepare($address_sql);
    $address_stmt->bind_param("i", $user_id);
    $address_stmt->execute();
    $address_result = $address_stmt->get_result();
    $address = $address_result->fetch_assoc();
    $address_stmt->close();

    if (!$address) {
        $response['message'] = 'No default address found';
        echo json_encode($response);
        exit();
    }

    // Start transaction
    $conn->begin_transaction();

    try {
        // Insert into orders table with address and instructions
        $sql = "INSERT INTO orders (user_id, restaurant_id, subtotal, taxes, delivery_fee, total, instructions, address_id, order_date, status) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW(), 'Pending')";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("iiddddsi", $user_id, $restaurant_id, $subtotal, $taxes, $delivery_fee, $total, $instructions, $address['id']);

        if ($stmt->execute()) {
            $order_id = $stmt->insert_id;
            $stmt->close();

            // Get cart items
            $cart_sql = "SELECT c.item_id, c.quantity, i.name, i.price, i.discount, i.image, i.description
                        FROM cart c
                        JOIN items i ON c.item_id = i.id
                        WHERE c.user_id = ? AND c.restaurant_id = ?";
            $cart_stmt = $conn->prepare($cart_sql);
            $cart_stmt->bind_param("ii", $user_id, $restaurant_id);
            $cart_stmt->execute();
            $cart_result = $cart_stmt->get_result();

            // Insert order items
            while ($row = $cart_result->fetch_assoc()) {
                $item_id   = $row['item_id'];
                $qty       = $row['quantity'];
                $price     = $row['price'];
                $discount  = $row['discount'];
                $item_name = $row['name'];
                $item_img  = $row['image'];
                $item_description  = $row['description'];

                $item_sql = "INSERT INTO order_items (order_id, item_id, item_name, item_image, description, quantity, price, discount) 
                            VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
                $item_stmt = $conn->prepare($item_sql);
                $item_stmt->bind_param("iisssidi", $order_id, $item_id, $item_name, $item_img, $item_description, $qty, $price, $discount);
                $item_stmt->execute();
                $item_stmt->close();
            }
            $cart_stmt->close();

            // Clear cart
            $delete_sql = "DELETE FROM cart WHERE user_id = ? AND restaurant_id = ?";
            $delete_stmt = $conn->prepare($delete_sql);
            $delete_stmt->bind_param("ii", $user_id, $restaurant_id);
            $delete_stmt->execute();
            $delete_stmt->close();

            // Commit transaction
            $conn->commit();

            // Pusher event
            $pusher->trigger(
                'foodexpress',
                'new-order',
                [
                    'order_id' => $order_id,
                    'restaurant_id' => $restaurant_id,
                    'user_id' => $user_id,
                    'status' => 'Pending',
                    'total' => $total
                ]
            );

            $response['status'] = 'success';
            $response['message'] = 'Order placed successfully';
            $response['order_id'] = $order_id;
        } else {
            throw new Exception("Failed to create order");
        }
    } catch (Exception $e) {
        // Rollback transaction on error
        $conn->rollback();
        $response['message'] = 'Order failed: ' . $e->getMessage();
    }

    echo json_encode($response);
    exit();
}

// Regular page load - show order history
?>

<!-- <?php include 'navber.php' ?> -->
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

    <div class="container">
        <?php
        $orders_stmt = $conn->prepare("SELECT o.*, a.restaurant_name 
                                    FROM orders o
                                    JOIN restaurant_info a ON o.restaurant_id = a.restaurant_id
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
                $taxes = $order_row['taxes'] ?? 0;
                $total = $order_row['total'];
                $date_time = date("M d, Y", strtotime($order_row['order_date']));
                $status = $order_row['status'];
                $restaurant_name = $order_row['restaurant_name'];

                $status_class = strtolower($status);
        ?>
                <div class="order-card">
                    <div class="order-header">
                        <div style="display: flex; align-items: center; flex-wrap: wrap; gap:10px;">
                            <span class="restaurant-name"><i class="fa-solid fa-utensils"></i> <?php echo $restaurant_name; ?></span>
                            <span class="order-status status-<?php echo $status_class; ?>">
                                <?php if ($status == "Completed") { ?>
                                    <i class="fas fa-check-circle"></i>
                                <?php } elseif ($status == "Processing") { ?>
                                    <i class="fas fa-spinner"></i>
                                <?php } elseif ($status == "Cancelled") { ?>
                                    <i class="fas fa-times-circle"></i>
                                <?php } elseif ($status == "Pending") { ?>
                                    <i class="fas fa-solid fa-clock"></i>
                                <?php } ?>
                                <?php echo $status; ?>
                            </span>
                            <span class="order-date"><?php echo $date_time; ?></span>
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
                                                $item_price = $item_row['price'] - ($item_row['price'] * $item_row['discount'] / 100);
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
                        </div>
                        <div class="order-actions">
                            <a href="order_details.php?order_id=<?php echo $order_id; ?>" class="action-btn btn-outline">
                                <i class="fas fa-eye"></i> View Details
                            </a>
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
            ?>
            <div class="order-card">
                <div class="empty-state">
                    <i class="fas fa-clipboard-list"></i>
                    <h3>No Orders Yet</h3>
                    <p>You haven't placed any orders yet. Start exploring restaurants to place your first order!</p>
                    <a href="restaurant_menu.php">
                        <button class="action-btn btn-primary">
                            Order Now
                        </button>
                    </a>
                </div>
            </div>
        <?php
        }
        ?>
    </div>
    <?php include 'includes/footer_nav.php' ?>
</body>

</html>