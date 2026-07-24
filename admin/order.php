<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
    include '../config/database_connection.php';
}

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
    <title>Admin Panel - Order Management</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
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

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        body {
            display: flex;
            min-height: 100vh;
            background-color: #f5f7fb;
            color: var(--dark);
        }


        .menu-item {
            padding: 12px 20px;
            display: flex;
            align-items: center;
            gap: 10px;
            cursor: pointer;
            transition: all 0.2s;
        }

        .menu-item:hover,
        .menu-item.active {
            background-color: var(--sidebar-hover);
        }

        .menu-item i {
            width: 20px;
            text-align: center;
        }

        /* Main Content Styles */
        .main-content {
            flex: 1;
            margin-left: 250px;
            padding: 20px;
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 1px solid #e0e0e0;
        }

        .header h1 {
            font-size: 1.8rem;
            color: var(--dark);
        }

        .user-profile {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .user-profile img {
            width: 40px;
            height: 40px;
            border-radius: 50%;
        }

        /* Order Management Styles */
        .order-filters {
            display: flex;
            justify-content: space-between;
            margin-bottom: 20px;
            flex-wrap: wrap;
            gap: 10px;
        }

        .filter-group {
            display: flex;
            gap: 10px;
            align-items: center;
        }

        .filter-btn {
            padding: 8px 15px;
            border-radius: 5px;
            border: none;
            cursor: pointer;
            font-weight: 500;
            transition: all 0.2s;
        }

        .filter-btn.active {
            background-color: var(--primary);
            color: white;
        }

        .filter-btn.inactive {
            background-color: #e0e0e0;
            color: var(--dark);
        }

        .search-box {
            position: relative;
            min-width: 250px;
        }

        .search-box input {
            width: 100%;
            padding: 10px 15px 10px 40px;
            border-radius: 5px;
            border: 1px solid #ddd;
            outline: none;
        }

        .search-box i {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: #777;
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

        /* Order Details Modal */
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            z-index: 1000;
            justify-content: center;
            align-items: center;
        }

        .modal-content {
            background-color: white;
            border-radius: 10px;
            width: 80%;
            max-width: 900px;
            max-height: 90vh;
            overflow-y: auto;
            box-shadow: 0 0 30px rgba(0, 0, 0, 0.2);
        }

        .modal-header {
            padding: 20px;
            border-bottom: 1px solid #eee;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .modal-header h2 {
            font-size: 1.5rem;
        }

        .close-btn {
            background: none;
            border: none;
            font-size: 1.5rem;
            cursor: pointer;
            color: #777;
        }

        .modal-body {
            padding: 20px;
        }

        .order-details-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }

        .order-section {
            margin-bottom: 20px;
        }

        .order-section h3 {
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 1px solid #eee;
            color: var(--primary);
        }

        .detail-row {
            display: flex;
            margin-bottom: 10px;
        }

        .detail-label {
            font-weight: 600;
            width: 150px;
            color: #555;
        }

        .order-items {
            margin-top: 20px;
        }

        .order-item {
            display: flex;
            padding: 10px 0;
            border-bottom: 1px solid #eee;
            align-items: center;
        }

        .item-image {
            width: 60px;
            height: 60px;
            border-radius: 5px;
            object-fit: cover;
            margin-right: 15px;
        }

        .item-details {
            flex: 1;
        }

        .item-name {
            font-weight: 600;
            margin-bottom: 5px;
        }

        .item-price {
            color: var(--primary);
            font-weight: 500;
        }

        .modal-footer {
            padding: 15px 20px;
            border-top: 1px solid #eee;
            display: flex;
            justify-content: flex-end;
            gap: 10px;
        }

        /* Responsive Styles */
        @media (max-width: 992px) {

            /* .sidebar {
                width: 70px;
                overflow: hidden;
            }
            .sidebar-header span, .menu-item span {
                display: none;
            } */
            .menu-item {
                justify-content: center;
            }

            .main-content {
                margin-left: 70px;
            }

            .order-details-grid {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 768px) {
            .orders-table {
                display: block;
                overflow-x: auto;
            }

            .filter-group {
                flex-wrap: wrap;
            }
        }
    </style>
</head>

<body>
    <?php include 'sideber.php' ?>

    <!-- Main Content -->
    <div class="main-content">
        <div class="header">
            <h1>Order Management</h1>
            <div class="user-profile">
                <img src="uploads/<?php echo $_SESSION['restaurant_logo']; ?>" alt="User Profile">
                <span><?php echo $_SESSION['admin_name']; ?></span>
            </div>
        </div>

        <!-- Order Filters -->
        <div class="order-filters">
            <div class="filter-group">
                <button class="filter-btn active" data-status="All">All Orders</button>
                <button class="filter-btn inactive" data-status="Pending">Pending</button>
                <button class="filter-btn inactive" data-status="Processing">Processing</button>
                <button class="filter-btn inactive" data-status="Completed">Completed</button>
                <button class="filter-btn inactive" data-status="Cancelled">Cancelled</button>
            </div>
            <div class="filter-group">
                <div class="search-box">
                    <i class="fas fa-search"></i>
                    <input type="text" placeholder="Search orders...">
                </div>
            </div>
        </div>

        <div id="order-table-container">
            <table class="orders-table">
                <thead>
                    <tr>
                        <th>Order ID</th>
                        <th>Customer</th>
                        <th>Date</th>
                        <th>Items</th>
                        <th>Total</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td colspan="7" style="text-align:center;">Loading orders...</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Order Details Modal -->
    <div class="modal" id="orderModal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Order Details - #ORD-1001</h2>
                <button class="close-btn" onclick="closeOrderModal()">&times;</button>
            </div>
            <div class="modal-body">
                <!-- (keep your existing order detail structure here as is) -->
            </div>
            <div class="modal-footer">
                <button class="action-btn btn-process">
                    <i class="fas fa-cog"></i> Process Order
                </button>
                <button class="action-btn btn-complete">
                    <i class="fas fa-check"></i> Mark as Completed
                </button>
                <button class="action-btn btn-cancel">
                    <i class="fas fa-times"></i> Cancel Order
                </button>
                <button class="action-btn" onclick="closeOrderModal()" style="background-color: #6c757d; color: white;">
                    <i class="fas fa-times"></i> Close
                </button>
            </div>
        </div>
    </div>

    <!-- ✅ jQuery for AJAX -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <script>
        $(document).ready(function() {
            // Load orders dynamically
            function loadOrders(status = "All") {
                $.ajax({
                    url: "fetch_orders.php",
                    method: "POST",
                    data: {
                        status: status,
                        restaurant_id: "<?php echo $restaurant_id; ?>"
                    },
                    beforeSend: function() {
                        $("#order-table-container").html('<p style="text-align:center;"><i class="fas fa-spinner fa-spin"></i> Loading...</p>');
                    },
                    success: function(data) {
                        $("#order-table-container").html(data);
                    },
                    error: function() {
                        $("#order-table-container").html('<p style="color:red; text-align:center;">Failed to load data.</p>');
                    }
                });
            }

            loadOrders();

            // Filter button click
            $(".filter-btn").click(function() {
                $(".filter-btn").removeClass("active").addClass("inactive");
                $(this).addClass("active").removeClass("inactive");
                const status = $(this).data("status");
                loadOrders(status);
            });
        });
    </script>
</body>
</html>