<?php
session_start();
require 'database_connection.php';

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
    <link rel="stylesheet" href="admin_style/menu_items.css">
</head>

<body>
    <?php include 'sideber.php' ?>
    <div class="main-content">

        <link rel="stylesheet" href="admin_style/navber.css">
        </head>

        <body>
            <div class="top-nav">
                <h1 class="page-title">Menu Management</h1>
                <!-- <div class="search-bar">
                
            </div> -->
                <div class="user-profile">
                    <div class="notification">
                        <i class="fas fa-bell"></i>
                        <span class="notification-count">3</span>
                    </div>
                    <img src="uploads\<?php echo $_SESSION['restaurant_logo']; ?>" alt="Admin">
                    <span><?php echo $_SESSION['admin_name']; ?></span>
                </div>
            </div>
        </body>

</html>
<!-- Content Area -->
<div class="content-area">
    <div class="page-header">
        <div class="categories">
            <a href="menu.php"><div class="category active">All</div></a>

            <?php
            $sql_1 = "SELECT * FROM categories WHERE restaurant_id = ?";
            $stmt_1 = $conn->prepare($sql_1);
            $stmt_1->bind_param("i", $restaurant_id);
            $stmt_1->execute();
            $result_1 = $stmt_1->get_result();

            if ($result_1 && $result_1->num_rows > 0) {
                while ($row = $result_1->fetch_assoc()) {
                    echo '<div class="category" data-category="' . htmlspecialchars($row['name']) . '">' . htmlspecialchars($row['name']) . '</div>';
                }
            }

            $stmt_1->close();
            ?>
        </div>
        <div class="btnBox">
            <a href="edit_category.php">
                <button class="btn btn-primary" id="add-menu-btn">
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

    <!-- Menu Items Grid -->
    <div class="menu-items" id="menu-items-container">
        <?php
        if (isset($_GET['category'])) {
            $category = $_GET['category'];
            $sql = "SELECT * FROM items WHERE restaurant_id = ? AND category = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("is", $restaurant_id, $category);
            $stmt->execute();
            $result = $stmt->get_result();
        } else {
            $sql = "SELECT * FROM items WHERE restaurant_id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $restaurant_id);
            $stmt->execute();
            $result = $stmt->get_result();
        }

        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {

                echo '<div class="menu-card">
                        <img src="' . $row['image'] . '" alt="' . $row['name'] . '" class="menu-image">
                        <div class="menu-details">
                            <div class="menu-header">
                                <h3 class="menu-name">' . $row['name'] . '</h3>
                                <span class="menu-status ' . ($row['status'] == 'available' ? 'status-available' : 'status-unavailable') . '">
                                    ' . ucfirst($row['status']) . '
                                </span>
                            </div>
                            <p class="menu-description">' . $row['description'] . '</p>
                            <div class="menu-footer">
                                <span class="menu-price">₹' . number_format($row['price'], 2) . '</span>
                                <div class="menu-actions">
                                    <a href="action_edit_items.php?id=' . $row['id'] . '" class="btn btn-primary">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <a href="action_delete_items.php?id=' . $row['id'] . '" class="btn btn-danger" onclick="return confirm(\'Are you sure you want to delete this item?\')">
                                        <i class="fas fa-trash"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>';
            }
        } else {
            echo '<p>No menu items found. Add your first item!</p>';
        }
        $conn->close();
        ?>
    </div>
</div>
</div>
<script>
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(alert => {
        setTimeout(() => {
            alert.style.display = 'none';
        }, 4000);
    });

    // Category selection functionality
    const categories = document.querySelectorAll('.category');
    categories.forEach(category => {
        category.addEventListener('click', () => {
            categories.forEach(c => c.classList.remove('active'));
            category.classList.add('active');
            // Here you would add filtering logic
        });
    });
</script>
</body>

</html>