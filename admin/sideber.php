<?php 
if (session_status() === PHP_SESSION_NONE) {
    session_start();
    include 'database_connection.php';
}

?>



<?php

// $restaurant_name = "";
// $restaurant_logo = ""; 
// $admin_name = "";

// if (isset($_SESSION['admin_id'])) {
//     $admin_id = $_SESSION['admin_id'];

//     $stmt = $conn->prepare("SELECT * FROM admin WHERE id = ?");
//     $stmt->bind_param("i", $admin_id);
//     $stmt->execute();
//     $result = $stmt->get_result();

//     if ($result->num_rows === 1) {
//         $admin = $result->fetch_assoc();
//         $restaurant_name = $admin['restaurant_name'];
//         $restaurant_logo = $admin['restaurant_logo'];
//         $admin_name = $admin['name'];
//     } else {
//         echo "Admin data not found.";
//     }
// } else {
//     echo "Please login first.";
// }
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
    <link rel="stylesheet" href="admin_style/sideber.css">
    <link rel="stylesheet" href="admin_style/admin.css"> 
</head>
<body>
    <div class="sidebar">
        <div class="sidebar-header">
            <img src="uploads\<?php echo $_SESSION['restaurant_logo']; ?>" alt="Admin">
            <h3><?php echo $_SESSION['restaurant_name']; ?></h3>
        </div>
        <div class="sidebar-menu">
            <ul>
                <li><a href="admin_dashboard.php" class="active"><i class="fas fa-tachometer-alt"></i> <span>Dashboard</span></a></li>
                <li><a href="menu.php"><i class="fas fa-utensils"></i> <span>Menu Items</span></a></li>
                <li><a href="admin_order.php"><i class="fas fa-shopping-cart"></i> <span>Orders</span></a></li>
                <li><a href="admin_analytics.php"><i class="fas fa-chart-bar"></i> <span>Analytics</span></a></li>
                <li><a href="#"><i class="fas fa-users"></i> <span>Users</span></a></li>
                <li><a href="admin_setting.php"><i class="fas fa-cog"></i> <span>Settings</span></a></li>
            </ul>
        </div>
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