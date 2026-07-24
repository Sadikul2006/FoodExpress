<?php
session_start();
require '../config/database_connection.php';

if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login_register.php");
    exit();
}

$restaurant_id = $_SESSION['admin_id'];
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Menu Management</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
    <link rel="stylesheet" href="style/menu_items.css">
    <link rel="stylesheet" href="style/navber.css">
</head>

<body>
    <?php include 'sideber.php'; ?>

    <div class="main-content">

        <!-- Top Navbar -->
        <div class="top-nav">
            <h1 class="page-title">Menu Management</h1>
            <div class="btnBox">
                <a href="edit_category.php">
                    <button class="btn btn-primary" id="edit-cetagory-btn">
                        <i class="fas fa-edit"></i> Edit Categories
                    </button>
                </a>
                <a href="additems.php">
                    <button class="btn btn-primary" id="add-menu-btn">
                        <i class="fas fa-plus"></i> Add New Item
                    </button>
                </a>
            </div>
        </div>

        <!-- Categories -->
        <div class="categories" id="category-container">
            <?php
            $stmt_1 = $conn->prepare("SELECT * FROM categories WHERE restaurant_id = ?");
            $stmt_1->bind_param("i", $restaurant_id);
            $stmt_1->execute();
            $result = $stmt_1->get_result();

            // "All" Category (Always show first)
            echo '<div class="category active" data-category="All">All</div>';

            while ($row = $result->fetch_assoc()) {
                echo '<div class="category" data-category="' . htmlspecialchars($row['name']) . '">' . htmlspecialchars($row['name']) . '</div>';
            }
            $stmt_1->close();
            ?>
        </div>

        <!-- Content Area -->
        <div class="content-area">

            <?php
            // Display success/error messages
            if (isset($_SESSION['success'])) {
                echo '<div class="alert alert-success">
                        <i class="fas fa-check-circle"></i> ' . $_SESSION['success'] . '
                    </div>';
                unset($_SESSION['success']);
            }
            if (isset($_SESSION['error'])) {
                echo '<div class="alert alert-danger">
                        <i class="fas fa-exclamation-circle"></i> ' . $_SESSION['error'] . '
                    </div>';
                unset($_SESSION['error']);
            }
            ?>

            <!-- AJAX Menu Items -->
            <div class="menu-items" id="menu-items-container">
                <!-- Items will load here dynamically via AJAX -->
                <p class="loading"><i class="fas fa-spinner fa-spin"></i> Loading menu items...</p>
            </div>
        </div>
    </div>

    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <!-- AJAX Logic -->
    <script>
        $(document).ready(function() {

            // Function to load menu items
            function loadMenu(categoryName = "All") {
                $.ajax({
                    url: "fetch_menu.php",
                    method: "POST",
                    data: {
                        category: categoryName,
                        restaurant_id: "<?php echo $restaurant_id; ?>"
                    },
                    beforeSend: function() {
                        $("#menu-items-container").html('<p class="loading"><i class="fas fa-spinner fa-spin"></i> Loading...</p>');
                    },
                    success: function(data) {
                        $("#menu-items-container").html(data);
                    },
                    error: function(xhr, status, error) {
                        $("#menu-items-container").html('<p class="error"><i class="fas fa-exclamation-triangle"></i> Something went wrong!</p>');
                        console.error("AJAX Error:", error);
                    }
                });
            }

            // Load All items when page first loads
            loadMenu("All");

            // Handle category clicks
            $(document).on("click", ".category", function() {
                const categoryName = $(this).data("category");
                $(".category").removeClass("active");
                $(this).addClass("active");
                loadMenu(categoryName);
            });

            // Auto-hide success/error alerts
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(alert => {
                setTimeout(() => {
                    alert.style.display = 'none';
                }, 4000);
            });
        });
    </script>

</body>
</html>
