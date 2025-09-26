<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
    include 'database_connection.php';
}

if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login_register.php");
    exit();
}
?>

<?php
$sql = "SELECT COUNT(*) AS total FROM users";
$result = $conn->query($sql);

if ($result) {
    $row = $result->fetch_assoc();
    $total_users = $row['total'];
} else {
    echo "Error: " . $conn->error;
}
?>

<?php
$restaurant_id = $_SESSION['admin_id'];
$sql = "SELECT COUNT(*) AS total FROM items WHERE restaurant_id = $restaurant_id";
$result = $conn->query($sql);

if ($result) {
    $row = $result->fetch_assoc();
    $total_menu = $row['total'];
} else {
    echo "Error: " . $conn->error;
}
?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
    <link rel="stylesheet" href="admin_style/dashbord.css">
</head>

<body>
    <?php if (isset($_SESSION['success'])): ?>
        <div class="alert success">
            <i class="fa-solid fa-circle-check"></i>
            <?php echo $_SESSION['success'];
            unset($_SESSION['success']); ?>
        </div>
    <?php endif ?>

    <?php include 'sideber.php' ?>

    <div class="main-content">
        <div class="top-nav">
            <div class="search-bar">
                <h1 class="page-title">Dashboard</h1>
            </div>
            <div class="user-profile">
                <div class="notification">
                    <i class="fas fa-bell"></i>
                    <span class="notification-count">3</span>
                </div>
                <img src="uploads\<?php echo $_SESSION['restaurant_logo']; ?>" alt="Admin">
                <span><?php echo $_SESSION['admin_name']; ?></span>
            </div>
        </div>

        <!-- Content Area -->
        <div class="content-area">
            <!-- Dashboard Cards -->
            <div class="dashboard-cards">
                <div class="card">
                    <div class="card-header">
                        <div>
                            <div class="card-title">Total Users</div>
                            <div class="card-value"><?php echo $total_users ?></div>
                        </div>
                        <div class="card-icon users">
                            <i class="fas fa-users"></i>
                        </div>
                    </div>
                    <div class="card-footer">
                        <span class="text-success">+12% from last month</span>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header">
                        <div>
                            <div class="card-title">Total Orders</div>
                            <div class="card-value">568</div>
                        </div>
                        <div class="card-icon orders">
                            <i class="fas fa-shopping-cart"></i>
                        </div>
                    </div>
                    <div class="card-footer">
                        <span class="text-success">+8% from last month</span>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header">
                        <div>
                            <div class="card-title">Total Revenue</div>
                            <div class="card-value">₹45,320</div>
                        </div>
                        <div class="card-icon revenue">
                            <i class="fas fa-yen-sign"></i>
                        </div>
                    </div>
                    <div class="card-footer">
                        <span class="text-success">+15% from last month</span>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header">
                        <div>
                            <div class="card-title">Menu Items</div>
                            <div class="card-value"><?php echo $total_menu ?></div>
                        </div>
                        <div class="card-icon menu">
                            <i class="fas fa-utensils"></i>
                        </div>
                    </div>
                    <div class="card-footer">
                        <span class="text-success">+3 new items</span>
                    </div>
                </div>
            </div>

            <!-- Charts Section -->
            <div class="charts">
                <div class="chart-container">
                    <h3 class="chart-title">Sales Overview</h3>
                    <div id="sales-chart" style="height: 300px;">
                        <!-- Chart will be rendered here -->
                        <img src="https://via.placeholder.com/800x300?text=Sales+Chart" alt="Sales Chart" style="width: 100%; height: 100%; object-fit: cover;">
                    </div>
                </div>

                <div class="chart-container">
                    <h3 class="chart-title">Revenue Sources</h3>
                    <div id="revenue-chart" style="height: 300px;">
                        <!-- Chart will be rendered here -->
                        <img src="https://via.placeholder.com/400x300?text=Revenue+Chart" alt="Revenue Chart" style="width: 100%; height: 100%; object-fit: cover;">
                    </div>
                </div>
            </div>

            <!-- Recent Orders Table -->
            <div class="recent-orders">
                <h3 class="chart-title">Recent Orders</h3>
                <table class="table">
                    <thead>
                        <tr>
                            <th>Order ID</th>
                            <th>Customer</th>
                            <th>Items</th>
                            <th>Amount</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>#ORD-001</td>
                            <td>John Doe</td>
                            <td>3</td>
                            <td>¥210</td>
                            <td><span class="status completed">Completed</span></td>
                            <td>
                                <button class="action-btn view-btn">View</button>
                                <button class="action-btn delete-btn">Delete</button>
                            </td>
                        </tr>
                        <tr>
                            <td>#ORD-002</td>
                            <td>Jane Smith</td>
                            <td>2</td>
                            <td>¥140</td>
                            <td><span class="status processing">Processing</span></td>
                            <td>
                                <button class="action-btn view-btn">View</button>
                                <button class="action-btn edit-btn">Edit</button>
                            </td>
                        </tr>
                        <tr>
                            <td>#ORD-003</td>
                            <td>Robert Johnson</td>
                            <td>5</td>
                            <td>¥350</td>
                            <td><span class="status pending">Pending</span></td>
                            <td>
                                <button class="action-btn view-btn">View</button>
                                <button class="action-btn edit-btn">Edit</button>
                            </td>
                        </tr>
                        <tr>
                            <td>#ORD-004</td>
                            <td>Emily Davis</td>
                            <td>1</td>
                            <td>¥70</td>
                            <td><span class="status cancelled">Cancelled</span></td>
                            <td>
                                <button class="action-btn view-btn">View</button>
                                <button class="action-btn delete-btn">Delete</button>
                            </td>
                        </tr>
                        <tr>
                            <td>#ORD-005</td>
                            <td>Michael Wilson</td>
                            <td>4</td>
                            <td>¥280</td>
                            <td><span class="status completed">Completed</span></td>
                            <td>
                                <button class="action-btn view-btn">View</button>
                                <button class="action-btn delete-btn">Delete</button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>
<script>
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(alert => {
        setTimeout(() => {
            alert.style.display = 'none';
        }, 4500);
    });
</script>

</html>