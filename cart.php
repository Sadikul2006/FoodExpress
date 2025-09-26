<?php
session_start();
include 'database_connection.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$restaurant_id = $_SESSION['restaurant_id'] ?? null;

// Initialize variables
$delivery_fee = 0;
$subtotal = 0;
$total = 0;
$cart_result = null;

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $item_id = isset($_POST['items_id']) ? (int)$_POST['items_id'] : null;

    if (!isset($_SESSION['user_id']) || !isset($_SESSION['restaurant_id']) || !$item_id) {
        http_response_code(400);
        echo "Missing data.";
        exit;
    }

    $user_id = $_SESSION['user_id'];
    $restaurant_id = $_SESSION['restaurant_id'];

    $stmt = $conn->prepare("SELECT * FROM cart WHERE item_id = ? AND user_id = ? AND restaurant_id = ?");
    $stmt->bind_param("iii", $item_id, $user_id, $restaurant_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $_SESSION['success'] = "Already added to your cart.";
    } else {
        $insert_stmt = $conn->prepare("INSERT INTO cart (user_id, item_id, restaurant_id, quantity) VALUES (?, ?, ?, 1)");
        $insert_stmt->bind_param("iii", $user_id, $item_id, $restaurant_id);
        if ($insert_stmt->execute()) {
            $_SESSION['success'] =  "Added to cart.";
        } else {
            http_response_code(500);
            echo "Error adding to cart.";
        }
        $insert_stmt->close();
    }
    $stmt->close();
}

if (!$restaurant_id) {
    die("Restaurant not selected.");
}

$fee_sql = "SELECT value FROM settings WHERE name = 'delivery_fee' LIMIT 1";
$fee_result = $conn->query($fee_sql);
if ($fee_result && $fee_result->num_rows > 0) {
    $delivery_fee = (float)$fee_result->fetch_assoc()['value'];
}

// Get cart items for display
if (isset($_SESSION['user_id']) && isset($_SESSION['restaurant_id'])) {
    $restaurant_id = $_SESSION['restaurant_id'];
    $user_id = $_SESSION['user_id'];

    $cart_stmt = $conn->prepare("SELECT * FROM cart WHERE user_id = ? AND restaurant_id = ?");
    $cart_stmt->bind_param("ii", $user_id, $restaurant_id);
    $cart_stmt->execute();
    $cart_result = $cart_stmt->get_result();

    // Calculate subtotal
    if ($cart_result->num_rows > 0) {
        $cart_items = $cart_result->fetch_all(MYSQLI_ASSOC);
        foreach ($cart_items as $item) {
            $item_stmt = $conn->prepare("SELECT * FROM items WHERE id = ?");
            $item_stmt->bind_param("i", $item['item_id']);
            $item_stmt->execute();
            $item_result = $item_stmt->get_result();

            if ($item_result->num_rows > 0) {
                $item_row = $item_result->fetch_assoc();
                $subtotal += ($item_row['price'] * $item['quantity']);
            }
            $item_stmt->close();
        }
        // Reset pointer for later use
        $cart_result->data_seek(0);
    }
    $cart_stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Your Cart</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
    <link rel="stylesheet" href="app_style/cart.css">
</head>

<body>
    <?php include 'navber.php' ?>

    <div class="container">
        <!-- <h1 class="title">Your Shopping Cart</h1> -->

        <div class="cart-container">
            <div class="cart-items">
                <?php
                if (isset($_SESSION['user_id']) && isset($_SESSION['restaurant_id'])) {
                    if ($cart_result && $cart_result->num_rows > 0) {
                        // Reset pointer to beginning
                        $cart_result->data_seek(0);

                        while ($row = $cart_result->fetch_assoc()) {
                            $item_id = $row['item_id'];
                            $item_quantity = $row['quantity'];

                            $item_stmt = $conn->prepare("SELECT * FROM items WHERE id = ?");
                            $item_stmt->bind_param("i", $item_id);
                            $item_stmt->execute();
                            $item_result = $item_stmt->get_result();

                            if ($item_result->num_rows > 0) {
                                $item_row = $item_result->fetch_assoc();
                                echo '
                                <div class="cart-item" id="item-' . $row['item_id'] . '">
                                    <img src="admin/' . htmlspecialchars($item_row['image']) . '" alt="' . htmlspecialchars($item_row['name']) . '" class="item-image">
                                    <div class="item-details">
                                        <div class="item-name">' . htmlspecialchars($item_row['name']) . '</div>
                                        <div class="item-price">₹' . number_format($item_row['price'], 2) . '</div>
                                        <div class="display">
                                            <div class="quantity-control"> 
                                                <button type="button" class="quantity-btn minus" data-id="' . $row['item_id'] . '" data-action="decrease">-</button>
                                                <input type="number" value="' . $item_quantity . '" min="1" class="quantity-input" readonly id="qty-' . $row['item_id'] . '">
                                                <button type="button" class="quantity-btn plus" data-id="' . $row['item_id'] . '" data-action="increase">+</button>
                                            </div>
                                            <button class="remove-btn" data-id="' . $row['item_id'] . '">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>';
                            }
                            $item_stmt->close();
                        }
                    } else {
                        echo '
                        <div class="empty-cart">
                            <i class="fas fa-shopping-cart" style="font-size: 50px; color: #ccc; margin-bottom: 20px;"></i>
                            <p>Your cart is empty</p>
                            <a href="dashboard.php" class="continue-shopping">Continue Shopping</a>
                        </div>';
                    }
                }
                ?>

                <?php if ($cart_result && $cart_result->num_rows > 0): ?>
                    <div>
                        <a href="dashboard.php" class="continue-shopping">Continue Shopping</a>

                        <?php
                        $user_id = $_SESSION['user_id'];
                        $sql = "SELECT * FROM address WHERE user_id = $user_id AND is_default = 1 LIMIT 1";
                        $result = $conn->query($sql);

                        if ($result && $result->num_rows > 0) {
                            $row = $result->fetch_assoc();
                        ?>
                            <div class="address-box">
                                <strong id="add_type">
                                    <?php echo htmlspecialchars($row['address_type']); ?>
                                </strong>
                                <a href="address.php?edit_id=<?php echo $row['id'] ?>" class="menu-dots"><i class="fa-solid fa-pen-to-square" id="edit_address"></i></a>
                                <p class="address-text">
                                    <?php echo htmlspecialchars($row['name']); ?><br>
                                    <?php echo htmlspecialchars($row['street']); ?>,<br>
                                    <?php echo htmlspecialchars($row['city']); ?><br>
                                    <?php echo htmlspecialchars($row['phone']); ?>
                                </p>
                            </div>
                        <?php
                        } else {
                            echo "<p>No default address found. <a href='user_info.php'>Add an address</a></p>";
                        }
                        ?>
                    </div>
                <?php endif; ?>


                <?php if ($cart_result && $cart_result->num_rows > 0): ?>
                    <div class="card">
                        <h3 style="margin-top:0">Special instructions</h3>
                        <textarea id="instructions" rows="4" style="width:100%;padding:10px;border-radius:10px;border:1px solid rgba(15,23,42,0.06)" placeholder="Add notes for the restaurant (e.g., no onions, extra spicy)"></textarea>
                    </div>
            </div>
            <div class="cart-summary" id="cart-summary">
                <h3 class="summary-title">Order Summary</h3>
                <div class="coupon-row">
                    <input id="coupon" placeholder="Coupon code" />
                    <button id="apply-coupon">Apply</button>
                </div>

                <div class="summary-row">
                    <span>Subtotal:</span>
                    <span id="subtotal">₹<?php echo number_format($subtotal, 2); ?></span>
                </div>
                <div class="summary-row">
                    <span>Taxes:</span>
                    <span id="tax">₹0.00</span>
                </div>
                <div class="summary-row">
                    <span>Delivery Fee:</span>
                    <span id="delivery-fee">₹<?php echo number_format($delivery_fee, 2); ?></span>
                </div>
                <div class="summary-row total-row">
                    <?php $total = $subtotal + $delivery_fee; ?>
                    <span>Total:</span>
                    <span id="total">₹<?php echo number_format($total, 2); ?></span>
                </div>
            </div>
        </div>
    </div>
    <button type="button" class="checkout-btn" id="checkoutButton">Place Order</button>


    <!-- Confirm Order Modal -->
    <div class="modal-overlay" id="orderModalOverlay">
        <div class="modal" id="orderModal">
            <div class="modal-header">
                <h2 class="modal-title">Confirm Your Order</h2>
                <button class="x" id="closeModal">✕</button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to proceed with this order?</p>
                <div class="summary">
                    <div class="info">
                        <span class="name" id="total">Total Amount: ₹<?php echo number_format($total, 2); ?></span>
                        <span class="muted">Delivery to your address</span>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-outline" id="cancelOrder">Cancel</button>
                <form action="order.php" method="POST" id="confirmOrderForm">
                    <input type="hidden" name="subtotal" id="formSubtotal" value="200.00">
                    <input type="hidden" name="delivery_fee" id="formDeliveryFee" value="20.00">
                    <input type="hidden" name="total" id="formTotal" value="220.00">
                    <button type="submit" class="btn btn-success" id="confirmOrder">Confirm Order</button>
                </form>
            </div>
        </div>
    </div>
<?php endif; ?>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    let ajaxInProgress = false; // flag to block multiple requests

    // Quantity update
    $(document).on("click", ".quantity-btn", function() {
        if (ajaxInProgress) return; // block if previous request not finished
        ajaxInProgress = true;

        let itemId = $(this).data("id");
        let action = $(this).data("action");

        $.ajax({
            url: "fetch_cart.php",
            type: "POST",
            data: {
                item_id: itemId,
                action: action
            },
            success: function(response) {
                let res = JSON.parse(response);
                if (res.status === "success") {
                    $("#qty-" + itemId).val(res.quantity);
                    updateCartSummary();
                } else {
                    alert("Something went wrong!");
                }
            },
            error: function() {
                alert("Server error!");
            },
            complete: function() {
                ajaxInProgress = false; // allow next request
            }
        });
    });

    // Remove item
    $(document).on("click", ".remove-btn", function(e) {
        e.preventDefault();
        if (ajaxInProgress) return;
        ajaxInProgress = true;

        let itemId = $(this).data("id");

        $.ajax({
            url: "fetch_cart.php",
            type: "POST",
            data: {
                action: "remove",
                item_id: itemId
            },
            success: function(response) {
                let res = JSON.parse(response);
                if (res.status === "success") {
                    $("#item-" + itemId).remove();
                    updateCartSummary();

                    // Navbar cart count update
                    $.ajax({
                        url: "fetch_cart_count.php",
                        method: "GET",
                        success: function(count) {
                            $(".cart-badge").text(count);
                            if (count > 0) {
                                $(".cart-badge").removeClass("remove");
                            } else {
                                location.reload();
                            }
                        },
                        error: function(xhr, status, error) {
                            console.error("Cart count update failed:", error);
                        }
                    });
                } else {
                    alert("Remove failed!");
                }
            },
            error: function() {
                alert("Server error!");
            },
            complete: function() {
                ajaxInProgress = false;
            }
        });
    });

    // Update cart summary
    function updateCartSummary() {
        $.ajax({
            url: "fetch_cart.php",
            type: "GET",
            data: {
                action: "get_summary"
            },
            success: function(data) {
                $("#cart-summary").html(data);
            }
        });
    }

    $(document).on("click", "#checkoutButton", function() {
        $.ajax({
            url: "fetch_cart.php",
            type: "POST",
            data: {
                checkOut: true
            },
            success: function(data) {
                $("#total").text("Total Amount: ₹" + data);
            }
        });
    });
</script>


<script>
    // Get elements
    const orderModalOverlay = document.getElementById("orderModalOverlay");
    const orderModal = document.getElementById("orderModal");
    const closeModal = document.getElementById("closeModal");
    const cancelOrder = document.getElementById("cancelOrder");

    // Open modal (event delegation)
    document.addEventListener("click", function(e) {
        if (e.target && e.target.id === "checkoutButton") {
            orderModalOverlay.setAttribute("data-open", "true");
            setTimeout(() => orderModal.setAttribute("data-ready", "true"), 10);
        }
    });

    // Close modal function
    function closeOrderModal() {
        orderModal.removeAttribute("data-ready");
        setTimeout(() => orderModalOverlay.removeAttribute("data-open"), 200);
    }

    // Close events
    closeModal.addEventListener("click", closeOrderModal);
    cancelOrder.addEventListener("click", closeOrderModal);

    // Also close when clicking outside modal
    orderModalOverlay.addEventListener("click", (e) => {
        if (e.target === orderModalOverlay) closeOrderModal();
    });
</script>

</html>