<?php
session_start();
include 'config/database_connection.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: user_login_register.php");
    exit();
}
if (isset($_GET['restaurant_id'])) {
    $_SESSION['restaurant_id'] = (int)$_GET['restaurant_id'];
}

$restaurant_id = $_SESSION['restaurant_id'] ?? ($_GET['restaurant_id'] ?? null);
$user_id = $_SESSION['user_id'] ?? null;



if (!$restaurant_id) {
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
        <i class="fa-solid fa-utensils" style="font-size:50px;color:#ff6b6b;margin-bottom:20px;"></i>
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

$restaurant_name = "";
$stmt_restaurant = $conn->prepare("SELECT restaurant_name FROM restaurant_info WHERE restaurant_id = ?");
$stmt_restaurant->bind_param("i", $restaurant_id);
$stmt_restaurant->execute();
$result_restaurant = $stmt_restaurant->get_result();

if ($row_restaurant = $result_restaurant->fetch_assoc()) {
    $restaurant_name = $row_restaurant['restaurant_name'];
}

$stmt_restaurant->close();
?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/dashboard.css">
</head>

<body>
    <?php include 'includes/alert_box.php'?>
    <div class="nav">
        <div class="search-bar">
            <input type="text" id="live-search" placeholder="Search for food...">
            <button><i class="fas fa-search"></i></button>
        </div>
        <p id="restaurant_name"><i class="fa-solid fa-utensils"></i><?php echo htmlspecialchars($restaurant_name ?: "Restaurant"); ?></p>
    </div>

    <?php if ($restaurant_id): ?>
        <div class="categories" id="category-container">
            <div class="category active" data-category="All">All</div>
            <?php
            $stmt_1 = $conn->prepare("SELECT * FROM categories WHERE restaurant_id = ?");
            $stmt_1->bind_param("i", $restaurant_id);
            $stmt_1->execute();
            $result = $stmt_1->get_result();
            while ($row = $result->fetch_assoc()) {
                echo '<div class="category" data-category="' . htmlspecialchars($row['name']) . '">' . htmlspecialchars($row['name']) . '</div>';
            }
            $stmt_1->close();
            ?>
        </div>
        <div class="app-container">

            <div class="menu-container" id="menu-container">
                <?php
                $sql = "SELECT * FROM items WHERE restaurant_id = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("i", $restaurant_id);
                $stmt->execute();
                $result = $stmt->get_result();

                if ($result && $result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        $final_price = $row['price'] - ($row['price'] *($row['discount'] / 100));
                        echo '
                        <div class="menu-item ' . ($row['status'] == 'available' ? '' : 'unavailable') . '">
                            <div class="image-container">';
                                if ($row['discount'] > 0) {
                                    echo '
                                    <div class="discount">
                                        <span>' . htmlspecialchars($row['discount']) . '%</span>
                                        <span>OFF</span>
                                    </div>';
                                }
                                echo '
                                <img src="admin/' . htmlspecialchars($row['image']) . '" alt="' . htmlspecialchars($row['name']) . '" class="item-image">
                            </div>
                            <div class="item-details">
                                <div class="item-header">
                                    <div class="item-name">
                                        <p>' . htmlspecialchars($row['name']) . '</p>
                                        <i class="fa-solid fa-circle-' . ($row['status'] == 'available' ? 'check' : 'xmark') . '"></i>
                                    </div>
                                </div>
                                <div class="item-portion">' . htmlspecialchars($row['description']) . '</div>
                                <div class="item-footer">
                                    <div>';
                                        if ($row['discount'] > 0) {
                                            echo '<p class="old_price">₹' . number_format($row['price'], 0) . '</p>';
                                        }
                                        echo '
                                        <div class="item-price">₹' . number_format($final_price, 0) . '</div>
                                    </div>
                                    <button class="add-btn" data-item-id="' . $row['id'] . '" data-item-name="' . htmlspecialchars($row['name']) . '" data-user-id="' . $user_id . '" ' . ($row['status'] != 'available' ? 'disabled' : '') . '>+ Add</button>
                                </div>
                            </div>
                        </div>';
                    }
                } else {
                    echo '<p>No menu items found.</p>';
                }
                $stmt->close();
                ?>
            </div>
            <?php include 'includes/footer.php' ?>
        </div>
        <?php endif; ?>
    <?php include 'includes/footer_nav.php' ?>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        // Function to show alert message
        // function showAlert(message, type = 'success') {
        //     const alertBox = document.getElementById('customAlert');
        //     const alertMessage = document.getElementById('alertMessage');
        //     const icon = alertBox.querySelector('i');

        //     // Set message and type
        //     alertMessage.textContent = message;
        //     alertBox.className = `custom-alert ${type}`;
        //     icon.className = type === 'success' ? 'fas fa-check-circle' : 'fas fa-exclamation-circle';

        //     // Show alert
        //     alertBox.classList.add('show');

        //     // Hide after 3 seconds
        //     setTimeout(() => {
        //         alertBox.classList.remove('show');
        //     }, 3000);
        // }

        document.addEventListener('DOMContentLoaded', function() {
            const restaurantId = <?php echo json_encode($restaurant_id); ?>;

            if (restaurantId) {
                document.querySelectorAll('.restaurant-card, .view-menu-btn').forEach(el => {
                    el.classList.remove('active', 'active-card');
                });

                const activeCard = document.querySelector(`.restaurant-card[data-restaurant-id="${restaurantId}"]`);
                if (activeCard) {
                    activeCard.classList.add('active-card');
                    const activeBtn = activeCard.querySelector('.view-menu-btn');
                    if (activeBtn) activeBtn.classList.add('active');

                    activeCard.scrollIntoView({
                        behavior: 'smooth',
                        block: 'nearest',
                        inline: 'center'
                    });
                }
            }


            // live search food
            $(document).on("keyup", "#live-search", function() {
                let text = $(this).val();

                $.ajax({
                    url: "ajax/fetch_menu_items.php",
                    type: "POST",
                    data: {
                        liveSearch: text
                    },
                    success: function(data) {
                        $("#menu-container").html(data);
                    }
                })
            });


            // Add to cart button with alert
            $(document).on("click", ".add-btn", function() {
                const button = $(this);
                const itemId = button.data("item-id");
                const itemName = button.data("item-name");

                if (button.prop("disabled")) return;

                button.prop("disabled", true).addClass("added-effect");
                const originalText = button.html();

                $.ajax({
                    url: "cart.php",
                    method: "POST",
                    data: {
                        items_id: itemId
                    },
                    success: function(response) {
                        showAlert(itemName + " added to cart!", "success");
                        button.html('<i class="fas fa-check"></i> Added');
                        setTimeout(function() {
                            button.html(originalText).prop("disabled", false).removeClass("added-effect");
                        }, 1500);

                        // Navbar cart count update
                        $.ajax({
                            url: "ajax/fetch_cart_count.php",
                            method: "GET",
                            success: function(count) {
                                $(".cart-badge").text(count);
                                if (count > 0) {
                                    $(".cart-badge").removeClass("remove");
                                } else {
                                    $(".cart-badge").addClass("remove");
                                }
                            },
                            error: function(xhr, status, error) {
                                console.error("Cart count update failed:", error);
                            }
                        });
                    },
                    error: function() {
                        showAlert("Failed to add item to cart", "error");

                        button.html("Error");
                        setTimeout(function() {
                            button.html(originalText).prop("disabled", false).removeClass("added-effect");
                        }, 1500);
                    }
                });
            });


            // Alert hide
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(alert => {
                setTimeout(() => alert.style.display = 'none', 4500);
            });

            // Category filter AJAX
            $(document).ready(function() {
                $(".category").on("click", function() {
                    const categoryName = $(this).data("category");

                    $(".category").removeClass("active");
                    $(this).addClass("active");

                    $.ajax({
                        url: "ajax/fetch_menu_items.php",
                        method: "POST",
                        data: {
                            category: categoryName,
                            restaurant_id: "<?php echo $restaurant_id ?? 0; ?>"
                        },
                        success: function(data) {
                            $("#menu-container").html(data);
                        },
                        error: function(xhr, status, error) {
                            console.error("AJAX Error:", error);
                        }
                    });
                });
            });
        });
    </script>

</body>

</html>