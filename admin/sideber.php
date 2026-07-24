<?php 
if (session_status() === PHP_SESSION_NONE) {
    session_start();
    include '../config/database_connection.php';
}

if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login_register.php");
    exit();
}

$restaurant_id = $_SESSION['restaurant_id'] ?? 0;

if ($restaurant_id > 0 && !isset($_SESSION['restaurant_name'])) {
    $sql = "SELECT restaurant_name FROM restaurant_info WHERE restaurant_id = ?";
    $stmt = $conn->prepare($sql);

    if ($stmt) {
        $stmt->bind_param("i", $restaurant_id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result && $result->num_rows === 1) {
            $row = $result->fetch_assoc();
            $_SESSION['restaurant_name'] = $row['restaurant_name'] ?? '';
        }
        $stmt->close();
    }
}
?>




<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
    <link rel="stylesheet" href="style/sideber.css">
    <link rel="stylesheet" href="style/admin.css"> 
</head>
<body>
    <div class="sidebar">
        <div class="sidebar-header">
            <h3><i class="fa-solid fa-shop"></i> <?php echo $_SESSION['restaurant_name']; ?></h3>
        </div>
        <div class="sidebar-menu">
            <ul>
                <li><a href="dashboard.php" class="active"><i class="fas fa-tachometer-alt"></i> <span>Dashboard</span></a></li>
                <li><a href="menu.php"><i class="fas fa-utensils"></i> <span>Menu Items</span></a></li>
                <li><a href="order.php"><i class="fas fa-shopping-cart"></i> <span>Orders</span></a></li>
                <li><a href="analytics.php"><i class="fas fa-chart-bar"></i> <span>Analytics</span></a></li>
                <li><a href="#"><i class="fas fa-users"></i> <span>Users</span></a></li>
                <li id="setting"><a href="settings.php"><i class="fas fa-cog"></i> <span>Settings</span></a></li>
                <li><a href="#" class="menu-item"><i class="fas fa-question-circle"></i><span class="menu-text">Help & Support</span></li></a>
                <!-- <img id="restaurant-image" src="uploads\<?php echo $_SESSION['restaurant_logo']; ?>" alt="Admin"> -->
            </ul>
        </div>

        <!-- <div class="user-profile">
            <img src="uploads\<?php echo $_SESSION['restaurant_logo']; ?>" alt="Admin">
            <span><?php echo $_SESSION['admin_name']; ?></span>
        </div> -->
    </div>
    <script>
        // Example for responsive sidebar toggle
        function toggleSidebar() {
            const sidebar = document.querySelector('.sidebar');
            const mainContent = document.querySelector('.main-content');
            
            if (sidebar.style.width === '250px') {
                sidebar.style.width = '80px';
                mainContent.style.marginLeft = '80px';
            } else {
                sidebar.style.width = '250px';
                mainContent.style.marginLeft = '250px';
            }
        }
    </script>
    <script>
    // Get all sidebar menu links
    const sidebarLinks = document.querySelectorAll('.sidebar-menu a');

    // Get current page URL (excluding domain)
    const currentPath = window.location.pathname.split('/').pop();

    sidebarLinks.forEach(link => {
        const linkPath = link.getAttribute('href');

        if (linkPath === currentPath) {
            link.classList.add('active');
        } else {
            link.classList.remove('active');
        }
    });
</script>

</body>
</html>