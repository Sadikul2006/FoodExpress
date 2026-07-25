<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
    include '../config/database_connection.php';
}

if (!isset($_SESSION['admin_id'])) {
    header("Location: index.php");
    exit();
}

$restaurant_id = $_SESSION['admin_id'];
$total_revenue = 0;
$total_orders = 0;
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
$sql = "SELECT COUNT(*) AS total FROM orders WHERE restaurant_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $restaurant_id);
$stmt->execute();
$result = $stmt->get_result();
if ($result) {
    $row = $result->fetch_assoc();
    $total_orders = $row['total'];
} else {
    echo "Error: " . $conn->error;
}
?>

<?php

$sql = "SELECT total FROM orders WHERE restaurant_id = ? AND status = ?";
$stmt = $conn->prepare($sql);
$status = 'Completed';
$stmt->bind_param("is", $restaurant_id, $status);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $total_revenue += $row['total'];
    }
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
    <link rel="stylesheet" href="style/dashbord.css">
    <style>
        :root {
            --primary: #2a9d8f;
            --secondary: #264653;
            --accent: #e9c46a;
            --light: #f8f9fa;
            --dark: #212529;
            --success: #28a745;
            --danger: #dc3545;
        }

        /* Orders Table */
        .orders-table {
            width: 100%;
            border-collapse: collapse;
            background-color: white;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.05);
        }

        .orders-table th,
        .orders-table td {
            padding: 15px;
            text-align: left;
            border-bottom: 1px solid #f0f0f0;
        }

        .orders-table th {
            background-color: var(--primary);
            color: white;
            font-weight: 500;
        }

        .orders-table tr:hover {
            background-color: #f9f9f9;
        }

        .order-status {
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 500;
            display: inline-block;
        }

        .status-pending {
            background-color: #fff3cd;
            color: #856404;
        }

        .status-processing {
            background-color: #cce5ff;
            color: #004085;
        }

        .status-completed {
            background-color: #d4edda;
            color: #155724;
        }

        .status-cancelled {
            background-color: #f8d7da;
            color: #721c24;
        }

        .action-btn {
            padding: 5px 10px;
            border-radius: 5px;
            border: none;
            cursor: pointer;
            font-size: 0.8rem;
            transition: all 0.2s;
            margin-right: 5px;
        }

        .btn-view {
            background-color: var(--primary);
            color: white;
        }

        .btn-process {
            background-color: #f8961e;
            color: white;
        }

        .btn-complete {
            background-color: var(--success);
            color: white;
        }

        .btn-cancel {
            background-color: var(--danger);
            color: white;
        }
    </style>
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
                <div class="notification">
                    <i class="fa-solid fa-envelope"></i>
                    <span class="notification-count">3</span>
                </div>
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
                    <div class="card-footer positive">
                        <i class="fa-solid fa-up-long"></i>
                        <span class="text-success">12% from last month</span>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header">
                        <div>
                            <div class="card-title">Total Orders</div>
                            <div class="card-value"><?php echo $total_orders ?></div>
                        </div>
                        <div class="card-icon orders">
                            <i class="fas fa-shopping-cart"></i>
                        </div>
                    </div>
                    <div class="card-footer negative">
                        <i class="fa-solid fa-down-long"></i>
                        <span class="text-success">8% from last month</span>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header">
                        <div>
                            <div class="card-title">Total Revenue</div>
                            <div class="card-value">₹<?php echo $total_revenue; ?></div>
                        </div>
                        <div class="card-icon revenue">
                            <i class="fa-solid fa-indian-rupee-sign"></i>
                        </div>
                    </div>
                    <div class="card-footer negative">
                        <i class="fa-solid fa-down-long"></i>
                        <span class="text-success">15% from last month</span>
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
                    <div class="card-footer positive">
                        <span class="text-success">+3 new items</span>
                    </div>
                </div>
            </div>

            <!-- Recent Orders Table -->
            <table class="orders-table">
                <thead>
                    <tr>
                        <th>Order ID</th>
                        <th>Customer</th>
                        <th>Time</th>
                        <th>Items</th>
                        <th>Total</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="recent_order">

                </tbody>
            </table>

        </div>

    </div>
</body>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://js.pusher.com/8.2/pusher.min.js"></script>
<script>
    const pusher = new Pusher("c1756ac2bb163dfeacbf", {
        cluster: "ap2"
    });

    const channel = pusher.subscribe("foodexpress");

    // Notification Sound
    const notificationSound = new Audio("../assets/sounds/new-order.wav");

    let audioUnlocked = false;

    function unlockAudio() {
        if (audioUnlocked) return;

        notificationSound.play()
            .then(() => {
                notificationSound.pause();
                notificationSound.currentTime = 0;
                audioUnlocked = true;
                console.log("✅ Audio Unlocked");
            })
            .catch(err => console.log(err));
    }

    document.addEventListener("click", unlockAudio, {
        once: true
    });

    // 🔊 Voice Function
    function speakNewOrder() {

        const msg = new SpeechSynthesisUtterance();

        msg.text = "New order received";

        msg.lang = "en-US";

        msg.rate = 1;

        msg.pitch = 1;

        window.speechSynthesis.speak(msg);
    }

    channel.bind("new-order", function(data) {

        console.log("New Order:", data);

        if (audioUnlocked) {

            notificationSound.currentTime = 0;
            notificationSound.play();

            speakNewOrder();
        }

        load_data();
    });

    function load_data() {
        $.ajax({
            url: "fetch_dashboard.php",
            type: "POST",
            data: {
                recent_order: true
            },
            success: function(data) {
                $("#recent_order").html(data);
            }
        });
    }

    $(document).ready(function() {
        load_data();
    });


    // ============================
    // Process Order
    // ============================
    $(document).on("click", ".btn-process", function() {

        let order_id = $(this).data("id");

        if (!confirm("Start processing this order?")) {
            return;
        }

        $.ajax({
            url: "action_online_order.php",
            type: "POST",
            data: {
                process_order: true,
                order_id: order_id
            },
            success: function(response) {
                load_data();

            }
        });

    });


    // ============================
    // Complete Order
    // ============================
    $(document).on("click", ".btn-complete", function() {
        let order_id = $(this).data("id");

        if (!confirm("Mark this order as completed?")) {
            return;
        }

        $.ajax({
            url: "action_online_order.php",
            type: "POST",
            data: {
                complete_order: true,
                order_id: order_id
            },
            success: function(response) {
                load_data();
            }
        });

    });


    // ============================
    // Cancel Order
    // ============================
    $(document).on("click", ".btn-cancel", function() {

        let order_id = $(this).data("id");

        if (!confirm("Are you sure you want to cancel this order?")) {
            return;
        }
        

        $.ajax({
            url: "action_online_order.php",
            type: "POST",
            data: {
                cancel_order: true,
                order_id: order_id
            },
            success: function(response) {
                load_data();
            },
        });

    });


    // ============================
    // View Order
    // ============================
    $(document).on("click", ".btn-view", function() {

        let order_id = $(this).data("id");

        window.location.href = "order_details.php?id=" + order_id;

    });
</script>

<script>
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(alert => {
        setTimeout(() => {
            alert.style.display = 'none';
        }, 4500);
    });
</script>

</html>