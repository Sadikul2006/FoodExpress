<?php
session_start();
include 'database_connection.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: user_login_register.php");
    exit();
}

if (isset($_GET['restaurant_id'])) {
    $_SESSION['restaurant_id'] = (int)$_GET['restaurant_id'];
    header("Location: dashboard.php");
    exit();
}

$restaurant_id = $_SESSION['restaurant_id'] ?? null;
$user_id = $_SESSION['user_id'] ?? null;
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
    <link rel="stylesheet" href="app_style/dashboard.css">
    <style>
        .custom-alert {
            position: fixed;
            top: 20px;
            right: 20px;
            background-color: #4CAF50;
            color: white;
            padding: 15px 25px;
            border-radius: 5px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            display: flex;
            align-items: center;
            gap: 10px;
            z-index: 10000;
            transform: translateX(200%);
            transition: transform 0.3s ease;
        }

        .custom-alert.show {
            transform: translateX(0);
        }

        .custom-alert i {
            font-size: 1.2rem;
        }

        .custom-alert.error {
            background-color: #f44336;
        }

        @keyframes buttonSuccess {
            0% {
                background-color: #FF6B6B;
            }

            50% {
                background-color: #4CAF50;
            }

            100% {
                background-color: #FF6B6B;
            }
        }

        .added-effect {
            animation: buttonSuccess 0.5s ease;
        }
    </style>
</head>

<body>
    <div id="customAlert" class="custom-alert">
        <i class="fas fa-check-circle"></i>
        <span id="alertMessage"><?php echo $_SESSION['success'];
                                unset($_SESSION['success']); ?></span>
    </div>

    <?php include 'navber.php'; ?>


    <div class="app-container">
        <div class="search-nav">
            <div class="search-bar">
                <input type="text" placeholder="Search for food...">
                <button><i class="fas fa-search"></i></button>
            </div>
            <div class="icon-filter"><i class="fa-solid fa-filter"></i></div>
        </div>
        <div class="search-categories-container">

            <section class="restaurant-section" id="restaurants">
                <!-- <div class="section-title">
                    <h2>Popular Restaurants</h2>
                    <p>Choose from our partner restaurants</p>
                </div> -->
                <div class="restaurant-scroll-container">
                    <div class="restaurant-scroll">
                        <?php
                        $sql = "SELECT * FROM admin";
                        $result = $conn->query($sql);

                        if ($result && $result->num_rows > 0) {
                            while ($row = $result->fetch_assoc()) {
                                $is_active = ($restaurant_id == $row['restaurant_id']) ? 'active' : '';
                                $card_active = ($restaurant_id == $row['restaurant_id']) ? 'active-card' : '';

                                // Fetch cuisines
                                $cuisine_sql = "SELECT name FROM categories WHERE restaurant_id = ?";
                                $stmt = $conn->prepare($cuisine_sql);
                                $stmt->bind_param("i", $row['restaurant_id']);
                                $stmt->execute();
                                $cuisine_result = $stmt->get_result();

                                $cuisines = [];
                                while ($cat = $cuisine_result->fetch_assoc()) {
                                    $cuisines[] = $cat['name'];
                                }
                                $stmt->close();

                                if (empty($cuisines)) {
                                    $cuisine_text = 'Not Set';
                                } else {
                                    $cuisine_display = array_slice($cuisines, 0, 4);
                                    $cuisine_text = implode(', ', $cuisine_display);
                                    if (count($cuisines) > 4) {
                                        $cuisine_text .= '...more';
                                    }
                                }

                                echo '
                                <a href="dashboard.php?restaurant_id=' . (int)$row['restaurant_id'] . '" class="restaurant-card ' . $card_active . '" data-restaurant-id="' . (int)$row['restaurant_id'] . '">
                                    <div class="restaurant-image">
                                        <img src="admin/uploads/' . htmlspecialchars($row['restaurant_logo']) . '" alt="' . htmlspecialchars($row['restaurant_name']) . '">
                                        <div class="restaurant-rating">
                                            <i class="fas fa-star"></i>
                                            <span>4.8</span>
                                        </div>
                                    </div>
                                    <div class="restaurant-info">
                                        <h3>' . htmlspecialchars($row['restaurant_name']) . '</h3>
                                        <div class="restaurant-cuisine">
                                            <i class="fas fa-tag"></i>
                                            <span>' . htmlspecialchars($cuisine_text) . '</span>
                                        </div>
                                        <div class="restaurant-cuisine">
                                            <i class="fas fa-map-marker-alt"></i>
                                            <span>' . htmlspecialchars($row['restaurant_place']) . '</span>
                                        </div>
                                        <div class="restaurant-footer">
                                            <div class="restaurant-delivery">
                                                <i class="fas fa-clock"></i>
                                                <span>30-40 min</span>
                                            </div>
                                            <span class="view-menu-btn ' . $is_active . '">View Menu</span>
                                        </div>
                                    </div>
                                </a>';
                            }
                        } else {
                            echo '<p>No restaurants found.</p>';
                        }
                        ?>
                    </div>
                </div>
            </section>

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

                <div class="menu-container" id="menu-container">
                    <?php
                    $sql = "SELECT * FROM items WHERE restaurant_id = ?";
                    $stmt = $conn->prepare($sql);
                    $stmt->bind_param("i", $restaurant_id);
                    $stmt->execute();
                    $result = $stmt->get_result();

                    if ($result && $result->num_rows > 0) {
                        while ($row = $result->fetch_assoc()) {
                            echo '
                            <div class="menu-item">
                                <div class="image-container">
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
                                        <div class="item-price">₹' . number_format($row['price'], 2) . '</div>
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
            <?php endif; ?>
        </div>
    </div>

    <?php if ($restaurant_id) include 'footer.php'; ?>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        // Function to show alert message
        function showAlert(message, type = 'success') {
            const alertBox = document.getElementById('customAlert');
            const alertMessage = document.getElementById('alertMessage');
            const icon = alertBox.querySelector('i');

            // Set message and type
            alertMessage.textContent = message;
            alertBox.className = `custom-alert ${type}`;
            icon.className = type === 'success' ? 'fas fa-check-circle' : 'fas fa-exclamation-circle';

            // Show alert
            alertBox.classList.add('show');

            // Hide after 3 seconds
            setTimeout(() => {
                alertBox.classList.remove('show');
            }, 3000);
        }

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
                            url: "fetch_cart_count.php",
                            method: "GET",
                            success: function(count) {
                                $(".cart-badge").text(count);
                                if(count > 0) {
                                   $(".cart-badge").removeClass("remove");
                                }else {
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
                        url: "fetch_menu_items.php",
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