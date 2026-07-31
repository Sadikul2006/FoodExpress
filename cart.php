<?php
session_start();
include 'config/database_connection.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

if (isset($_GET['restaurant_id'])) {
    $_SESSION['restaurant_id'] = (int)$_GET['restaurant_id'];
}

$restaurant_id = $_SESSION['restaurant_id'] ?? ($_GET['restaurant_id'] ?? null);
$user_id = $_SESSION['user_id'] ?? null;


$restaurant_name = "";
$stmt_restaurant = $conn->prepare("SELECT restaurant_name FROM restaurant_info WHERE restaurant_id = ?");
$stmt_restaurant->bind_param("i", $restaurant_id);
$stmt_restaurant->execute();
$result_restaurant = $stmt_restaurant->get_result();

if ($row_restaurant = $result_restaurant->fetch_assoc()) {
    $restaurant_name = $row_restaurant['restaurant_name'];
}
$stmt_restaurant->close();


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

if (!isset($_SESSION['restaurant_id'])) {
    echo '
    <div style="
        display:flex;
        justify-content:center;
        align-items:center;
        height:80vh;
        flex-direction:column;
        color:#555;
        text-align:center;
        font-family:\'Segoe UI\',sans-serif;
    ">
        <i class="fa-solid fa-utensils" style="font-size:60px;color:#ff6b6b;margin-bottom:20px;"></i>
        <h3>Please select a restaurant first</h3>
        <p style="color:#777;">Go back to the home page and choose a restaurant to see its menu.</p>
        <a href="restaurants.php" style="
            margin-top:20px;
            background:#ff6b6b;
            color:white;
            padding:10px 20px;
            border-radius:8px;
            text-decoration:none;
            font-weight:600;
            transition:background 0.2s;
        ">Go to Restaurants</a>
    </div>
    ';
}

$settings_sql = "SELECT delivery_fee, min_order_amount FROM restaurant_settings WHERE restaurant_id = ?";
$stmt2 = $conn->prepare($settings_sql);
$stmt2->bind_param("i", $_SESSION['restaurant_id']);
$stmt2->execute();
$settings_result = $stmt2->get_result();
$settings = $settings_result->fetch_assoc();
$stmt2->close();

$delivery_fee = $settings['delivery_fee'] ?? 0;
$min_order_amount = $settings['min_order_amount'] ?? 0;


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
                $final_price = $item_row['price'] - ($item_row['price'] * $item_row['discount'] / 100);
                $subtotal += ($final_price * $item['quantity']);
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
    <link rel="stylesheet" href="assets/css/cart.css">
</head>

<body>
    <div class="nav">
        <a href="restaurant_menu.php" class="back-btn">
            <i class="fas fa-arrow-left"></i>
        </a>
        <h3>Your Cart</h3>
        <p id="restaurant_name"><i class="fa-solid fa-utensils"></i><?php echo htmlspecialchars($restaurant_name ?: "Restaurant"); ?></p>
    </div>

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
                                $final_price = $item_row['price'] - ($item_row['price'] * $item_row['discount'] / 100);
                                echo '
                                <div class="cart-item" id="item-' . $row['item_id'] . '">
                                    <img src="admin/' . htmlspecialchars($item_row['image']) . '" alt="' . htmlspecialchars($item_row['name']) . '" class="item-image">
                                    <div class="item-details">
                                        <div class="item-name">' . htmlspecialchars($item_row['name']) . '</div>
                                        <div class="item-price">₹' . number_format($final_price, 2) . '</div>
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
                            <a href="restaurant_menu.php" id="continue-shopping" class="continue-shopping">Add Items</a>
                        </div>';
                    }
                }
                ?>

                <?php if ($cart_result && $cart_result->num_rows > 0): ?>
                    <div class="card">
                        <h3 style="margin-top:0">Special instructions</h3>
                        <textarea id="instructions" rows="4" style="width:100%;padding:10px;border-radius:10px;border:1px solid rgba(15,23,42,0.06)" placeholder="Add notes for the restaurant (e.g., no onions, extra spicy)"></textarea>
                    </div>

                    <div>
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
                            echo '<a href="address.php">
                                    <div class="add-address">
                                        <i class="fas fa-plus"></i>
                                        <span>Add New Address</span>
                                    </div>
                                </a>';
                        }
                        ?>
                    </div>
                <?php endif; ?>
            </div>


            <?php if ($cart_result && $cart_result->num_rows > 0): ?>
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

    <div id="cart_action_btn">
        <a href="restaurant_menu.php" class="continue-shopping">Add Items</a>
        <button type="button" class="checkout-btn" id="checkoutButton">
            <p>Place Order</p>
            <p id="total_amount">Total Amount : ₹<?php echo number_format($total, 2); ?></p>
        </button>
    </div>

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
                        <span class="name" id="total_text">Total Amount: ₹<?php echo number_format($total, 2); ?></span>
                        <span class="muted">Delivery to your address</span>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-outline" id="cancelOrder">Cancel</button>
                <input type="hidden" id="subtotal">
                <input type="hidden" id="taxes">
                <input type="hidden" id="delivery_fee">
                <input type="hidden" id="total_val">
                <button class="btn btn-success" id="confirmOrder">Confirm Order</button>
            </div>
        </div>
    </div>
<?php endif; ?>
<?php include 'includes/footer_nav.php' ?>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    let ajaxInProgress = false;

    // Quantity update
    $(document).on("click", ".quantity-btn", function() {
        if (ajaxInProgress) return;
        ajaxInProgress = true;

        let itemId = $(this).data("id");
        let action = $(this).data("action");

        $.ajax({
            url: "ajax/fetch_cart.php",
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
                ajaxInProgress = false;
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
            url: "ajax/fetch_cart.php",
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
                        url: "ajax/fetch_cart_count.php",
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
            url: "ajax/fetch_cart.php",
            type: "GET",
            data: {
                action: "get_summary"
            },
            dataType: "json",
            success: function(data) {
                let subtotal = parseFloat(data.subtotal).toFixed(2);
                let taxes = parseFloat(data.taxes).toFixed(2);
                let delivery_fee = parseFloat(data.delivery_fee).toFixed(2);
                let total = parseFloat(data.total).toFixed(2);

                // Update hidden fields
                $("#subtotal").val(subtotal);
                $("#taxes").val(taxes);
                $("#delivery_fee").val(delivery_fee);
                $("#total_val").val(total);

                // Update displayed values
                $("#subtotal").text("₹" + subtotal);
                $("#tax").text("₹" + taxes);
                $("#delivery-fee").text("₹" + delivery_fee);
                $("#total").text("₹" + total);
                $("#total_text").text("Total Amount: ₹" + total);

                $("#total_amount").text("Total Amount: ₹" + total);
            },
            error: function(xhr, status, error) {
                console.error("Summary update failed:", error);
            }
        });
    }

    // Place order
    function placeOrder() {
        if (ajaxInProgress) return;
        ajaxInProgress = true;

        let subtotal = $("#subtotal").val();
        let taxes = $("#taxes").val();
        let delivery_fee = $("#delivery_fee").val();
        let total = $("#total_val").val();
        let instructions = $("#instructions").val();

        $.ajax({
            url: "order_history.php",
            type: "POST",
            dataType: "json",
            data: {
                subtotal: subtotal,
                taxes: taxes,
                delivery_fee: delivery_fee,
                total: total,
                instructions: instructions,
                order: "confirm"
            },
            success: function(response) {
                if (response.status === 'success') {
                    window.location.href = "order_history.php";
                } else {
                    alert('Failed to place order: ' + response.message);
                }
            },
            error: function(xhr, status, error) {
                console.error("Order failed:", error);
                alert("Failed to place order. Please try again.");
            },
            complete: function() {
                ajaxInProgress = false;
            }
        });
    }



    $(document).on("click", "#checkoutButton", function() {
        if (ajaxInProgress) return;
        ajaxInProgress = true;

        // Update cart summary first
        $.ajax({
            url: "ajax/fetch_cart.php",
            type: "GET",
            data: {
                action: "get_summary"
            },
            dataType: "json",
            success: function(data) {
                let subtotal = parseFloat(data.subtotal) || 0;
                let taxes = parseFloat(data.taxes) || 0;
                let delivery_fee = parseFloat(data.delivery_fee) || 0;
                let total = parseFloat(data.total) || 0;

                // Update hidden fields and display
                $("#subtotal").val(subtotal.toFixed(2));
                $("#taxes").val(taxes.toFixed(2));
                $("#delivery_fee").val(delivery_fee.toFixed(2));
                $("#total_val").val(total.toFixed(2));

                $("#subtotal").text("₹" + subtotal.toFixed(2));
                $("#tax").text("₹" + taxes.toFixed(2));
                $("#delivery-fee").text("₹" + delivery_fee.toFixed(2));
                $("#total").text("₹" + total.toFixed(2));
                $("#total_text").text("Total Amount: ₹" + total.toFixed(2));

                // **Check restaurant status first**
                $.ajax({
                    url: "ajax/fetch_cart.php",
                    type: "POST",
                    dataType: "json",
                    data: {
                        check_restaurant_status: true
                    },
                    success: function(response) {
                        if (!response.status) {
                            // Restaurant closed
                            $("#orderModal .modal-title").text("Restaurant Closed");
                            $("#orderModal .modal-body").html(`<p>Sorry, the restaurant is currently closed. It will be open at ${response.opening_time}</p>`);
                            $("#orderModal .modal-footer").html(`<a href="restaurants.php"><button class="btn btn-success">Change Restaurant</button></a>`);
                            $("#orderModalOverlay").attr("data-open", "true");
                            setTimeout(() => $("#orderModal").attr("data-ready", "true"), 10);
                        } else {
                            // Restaurant open, check minimum order
                            $.ajax({
                                url: "ajax/fetch_cart.php",
                                type: "POST",
                                dataType: "json",
                                data: {
                                    min_amount: true,
                                    total: total
                                },
                                success: function(minResp) {
                                    if (total < minResp.min_order) {
                                        $("#orderModal .modal-title").text("Minimum Order Required");
                                        $("#orderModal .modal-body").html(`<p>Minimum order amount is ₹${minResp.min_order}. Please add more items to your cart.</p>`);
                                        $("#orderModal .modal-footer").html(`<a href="restaurant_menu.php"><button class="btn btn-success">Add Items</button></a>`);
                                    } else {
                                        $.ajax({
                                            url: "ajax/fetch_cart.php",
                                            type: "POST",
                                            dataType: "json",
                                            data: { is_address: true },
                                            success: function(res) {
                                                if (res.status === true) {
                                                    // User address found
                                                    $("#orderModal .modal-title").text("Confirm Your Order");
                                                    $("#orderModal .modal-body").html(`<p>Are you sure you want to proceed with this order?</p>
                                                    <div class="summary">
                                                        <div class="info">
                                                            <span class="name">Total Amount: ₹${total.toFixed(2)}</span>
                                                            <span class="muted">Delivery to your address</span>
                                                        </div>
                                                    </div>`);
                                                    $("#orderModal .modal-footer").html(`
                                                    <button class="btn btn-outline" id="cancelOrder">Cancel</button>
                                                    <input type="hidden" id="subtotal" value="${subtotal.toFixed(2)}">
                                                    <input type="hidden" id="taxes" value="${taxes.toFixed(2)}">
                                                    <input type="hidden" id="delivery_fee" value="${delivery_fee.toFixed(2)}">
                                                    <input type="hidden" id="total_val" value="${total.toFixed(2)}">
                                                    <button class="btn btn-success" id="confirmOrder">Confirm Order</button>
                                                    `);
                                                    $("#confirmOrder").off("click").on("click", placeOrder);
                                                } else {
                                                    // No default address found
                                                    $("#orderModal .modal-title").text("Address Required");
                                                    $("#orderModal .modal-body").html(`<p>Please add your delivery address before placing the order.</p>`);
                                                    $("#orderModal .modal-footer").html(`<a href="address.php"><button class="btn btn-success">Add Address</button></a>`);  
                                                }
                                            }   
                                        });
                                    }
                                    $("#orderModalOverlay").attr("data-open", "true");
                                    setTimeout(() => $("#orderModal").attr("data-ready", "true"), 10);
                                }
                            });
                        }
                    }
                });

            },
            error: function() {
                alert("Failed to fetch cart summary.");
            },
            complete: function() {
                ajaxInProgress = false;
            }
        });
        // Close modal: X, Cancel, or overlay click
        $(document).on("click", "#closeModal, #cancelOrder, #orderModalOverlay", function(e) {
            if ($(e.target).is("#orderModalOverlay") || $(e.target).is("#closeModal") || $(e.target).is("#cancelOrder")) {
                $("#orderModal").removeAttr("data-ready");
                setTimeout(() => $("#orderModalOverlay").removeAttr("data-open"), 200);
            }
        });
    });
</script>



</html>