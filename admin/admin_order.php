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

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel - Order Management</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* :root {
            --primary: #4361ee;
            --secondary: #3f37c9;
            --success: #4cc9f0;
            --warning: #f8961e;
            --danger: #f72585;
            --light: #f8f9fa;
            --dark: #212529;
        } */
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

        /* Sidebar Styles */
        /* .sidebar {
            width: 250px;
            background-color: var(--sidebar);
            color: white;
            transition: all 0.3s;
            height: 100vh;
            position: fixed;
        }

        .sidebar-header {
            padding: 20px;
            background-color: rgba(0,0,0,0.2);
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .sidebar-header img {
            width: 40px;
            height: 40px;
            border-radius: 50%;
        }

        .sidebar-menu {
            padding: 20px 0;
        } */

        .menu-item {
            padding: 12px 20px;
            display: flex;
            align-items: center;
            gap: 10px;
            cursor: pointer;
            transition: all 0.2s;
        }

        .menu-item:hover, .menu-item.active {
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
            box-shadow: 0 0 20px rgba(0,0,0,0.05);
        }

        .orders-table th, .orders-table td {
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
            background-color: var(--warning);
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
            background-color: rgba(0,0,0,0.5);
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
            box-shadow: 0 0 30px rgba(0,0,0,0.2);
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
    <!-- Sidebar -->
    <!-- <div class="sidebar">
        <div class="sidebar-header">
            <img src="https://via.placeholder.com/40" alt="Admin Logo">
            <span>Admin Panel</span>
        </div>
        <div class="sidebar-menu">
            <div class="menu-item">
                <i class="fas fa-tachometer-alt"></i>
                <span>Dashboard</span>
            </div>
            <div class="menu-item active">
                <i class="fas fa-shopping-cart"></i>
                <span>Orders</span>
            </div>
            <div class="menu-item">
                <i class="fas fa-utensils"></i>
                <span>Menu Items</span>
            </div>
            <div class="menu-item">
                <i class="fas fa-users"></i>
                <span>Customers</span>
            </div>
            <div class="menu-item">
                <i class="fas fa-chart-line"></i>
                <span>Analytics</span>
            </div>
            <div class="menu-item">
                <i class="fas fa-cog"></i>
                <span>Settings</span>
            </div>
        </div>
    </div> -->
    <?php include 'sideber.php' ?>

    <!-- Main Content -->
    <div class="main-content">
        <div class="header">
            <h1>Order Management</h1>
            <div class="user-profile">
                <img src="uploads\<?php echo $_SESSION['restaurant_logo']; ?>" alt="User Profile">
                <span><?php ECHO $_SESSION['admin_name']; ?></span>
            </div>
        </div>

        <!-- Order Filters -->
        <div class="order-filters">
            <div class="filter-group">
                <button class="filter-btn active">All Orders</button>
                <button class="filter-btn inactive">Pending</button>
                <button class="filter-btn inactive">Processing</button>
                <button class="filter-btn inactive">Completed</button>
                <button class="filter-btn inactive">Cancelled</button>
            </div>
            <div class="filter-group">
                <div class="search-box">
                    <i class="fas fa-search"></i>
                    <input type="text" placeholder="Search orders...">
                </div>
                <button class="filter-btn active">
                    <i class="fas fa-filter"></i> Filter
                </button>
            </div>
        </div>

        <!-- Orders Table -->
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
                    <td>#ORD-1001</td>
                    <td>John Doe</td>
                    <td>Jun 15, 2023</td>
                    <td>3</td>
                    <td>¥1,250.00</td>
                    <td><span class="order-status status-pending">Pending</span></td>
                    <td>
                        <button class="action-btn btn-view" onclick="openOrderModal()">
                            <i class="fas fa-eye"></i> View
                        </button>
                        <button class="action-btn btn-process">
                            <i class="fas fa-cog"></i> Process
                        </button>
                    </td>
                </tr>
                <tr>
                    <td>#ORD-1002</td>
                    <td>Jane Smith</td>
                    <td>Jun 14, 2023</td>
                    <td>5</td>
                    <td>¥2,150.00</td>
                    <td><span class="order-status status-processing">Processing</span></td>
                    <td>
                        <button class="action-btn btn-view" onclick="openOrderModal()">
                            <i class="fas fa-eye"></i> View
                        </button>
                        <button class="action-btn btn-complete">
                            <i class="fas fa-check"></i> Complete
                        </button>
                    </td>
                </tr>
                <tr>
                    <td>#ORD-1003</td>
                    <td>Robert Johnson</td>
                    <td>Jun 14, 2023</td>
                    <td>2</td>
                    <td>¥850.00</td>
                    <td><span class="order-status status-completed">Completed</span></td>
                    <td>
                        <button class="action-btn btn-view" onclick="openOrderModal()">
                            <i class="fas fa-eye"></i> View
                        </button>
                    </td>
                </tr>
                <tr>
                    <td>#ORD-1004</td>
                    <td>Emily Davis</td>
                    <td>Jun 13, 2023</td>
                    <td>4</td>
                    <td>¥1,750.00</td>
                    <td><span class="order-status status-cancelled">Cancelled</span></td>
                    <td>
                        <button class="action-btn btn-view" onclick="openOrderModal()">
                            <i class="fas fa-eye"></i> View
                        </button>
                    </td>
                </tr>
                <tr>
                    <td>#ORD-1005</td>
                    <td>Michael Wilson</td>
                    <td>Jun 13, 2023</td>
                    <td>1</td>
                    <td>¥450.00</td>
                    <td><span class="order-status status-pending">Pending</span></td>
                    <td>
                        <button class="action-btn btn-view" onclick="openOrderModal()">
                            <i class="fas fa-eye"></i> View
                        </button>
                        <button class="action-btn btn-process">
                            <i class="fas fa-cog"></i> Process
                        </button>
                        <button class="action-btn btn-cancel">
                            <i class="fas fa-times"></i> Cancel
                        </button>
                    </td>
                </tr>
            </tbody>
        </table>

        <!-- Pagination would go here -->
    </div>

    <!-- Order Details Modal -->
    <div class="modal" id="orderModal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Order Details - #ORD-1001</h2>
                <button class="close-btn" onclick="closeOrderModal()">&times;</button>
            </div>
            <div class="modal-body">
                <div class="order-details-grid">
                    <div class="order-section">
                        <h3><i class="fas fa-user"></i> Customer Information</h3>
                        <div class="detail-row">
                            <div class="detail-label">Name:</div>
                            <div class="detail-value">John Doe</div>
                        </div>
                        <div class="detail-row">
                            <div class="detail-label">Email:</div>
                            <div class="detail-value">john.doe@example.com</div>
                        </div>
                        <div class="detail-row">
                            <div class="detail-label">Phone:</div>
                            <div class="detail-value">+1 (555) 123-4567</div>
                        </div>
                        <div class="detail-row">
                            <div class="detail-label">Order Date:</div>
                            <div class="detail-value">June 15, 2023 - 6:30 PM</div>
                        </div>
                    </div>

                    <div class="order-section">
                        <h3><i class="fas fa-map-marker-alt"></i> Delivery Information</h3>
                        <div class="detail-row">
                            <div class="detail-label">Address:</div>
                            <div class="detail-value">
                                123 Main Street, Apt 4B<br>
                                New York, NY 10001<br>
                                United States
                            </div>
                        </div>
                        <div class="detail-row">
                            <div class="detail-label">Delivery Note:</div>
                            <div class="detail-value">Ring the bell twice</div>
                        </div>
                        <div class="detail-row">
                            <div class="detail-label">Payment Method:</div>
                            <div class="detail-value">Credit Card (**** **** **** 4242)</div>
                        </div>
                    </div>
                </div>

                <div class="order-section order-items">
                    <h3><i class="fas fa-utensils"></i> Order Items</h3>
                    <div class="order-item">
                        <img src="https://via.placeholder.com/300x200?text=Chicken+Biryani" alt="Chicken Biryani" class="item-image">
                        <div class="item-details">
                            <div class="item-name">Chicken Biryani</div>
                            <div class="item-price">¥350.00</div>
                            <div>Quantity: 2</div>
                        </div>
                    </div>
                    <div class="order-item">
                        <img src="https://via.placeholder.com/300x200?text=Garlic+Naan" alt="Garlic Naan" class="item-image">
                        <div class="item-details">
                            <div class="item-name">Garlic Naan</div>
                            <div class="item-price">¥80.00</div>
                            <div>Quantity: 3</div>
                        </div>
                    </div>
                    <div class="order-item">
                        <img src="https://via.placeholder.com/300x200?text=Mango+Lassi" alt="Mango Lassi" class="item-image">
                        <div class="item-details">
                            <div class="item-name">Mango Lassi</div>
                            <div class="item-price">¥120.00</div>
                            <div>Quantity: 1</div>
                        </div>
                    </div>
                </div>

                <div class="order-section">
                    <h3><i class="fas fa-receipt"></i> Order Summary</h3>
                    <div class="detail-row">
                        <div class="detail-label">Subtotal:</div>
                        <div class="detail-value">¥1,090.00</div>
                    </div>
                    <div class="detail-row">
                        <div class="detail-label">Delivery Fee:</div>
                        <div class="detail-value">¥0.00</div>
                    </div>
                    <div class="detail-row">
                        <div class="detail-label">Tax:</div>
                        <div class="detail-value">¥87.20</div>
                    </div>
                    <div class="detail-row" style="font-weight: 600; font-size: 1.1rem;">
                        <div class="detail-label">Total:</div>
                        <div class="detail-value">¥1,177.20</div>
                    </div>
                </div>

                <div class="order-section">
                    <h3><i class="fas fa-history"></i> Order Status History</h3>
                    <div class="detail-row">
                        <div class="detail-label">Current Status:</div>
                        <div class="detail-value"><span class="order-status status-pending">Pending</span></div>
                    </div>
                    <div class="detail-row">
                        <div class="detail-label">Status History:</div>
                        <div class="detail-value">
                            <div>Jun 15, 6:30 PM - Order placed</div>
                            <div>Jun 15, 6:35 PM - Payment confirmed</div>
                        </div>
                    </div>
                </div>
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

    <script>
        // Modal functions
        function openOrderModal() {
            document.getElementById('orderModal').style.display = 'flex';
            document.body.style.overflow = 'hidden';
        }

        function closeOrderModal() {
            document.getElementById('orderModal').style.display = 'none';
            document.body.style.overflow = 'auto';
        }

        // Close modal when clicking outside
        window.onclick = function(event) {
            const modal = document.getElementById('orderModal');
            if (event.target == modal) {
                closeOrderModal();
            }
        }

        // Filter button functionality
        document.querySelectorAll('.filter-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                document.querySelectorAll('.filter-btn').forEach(b => {
                    b.classList.remove('active');
                    b.classList.add('inactive');
                });
                this.classList.remove('inactive');
                this.classList.add('active');
            });
        });

        // In a real application, you would have more JavaScript
        // to handle order processing, status changes, etc.
    </script>
</body>
</html>