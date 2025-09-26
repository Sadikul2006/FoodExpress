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
    <title>Analytics | Restaurant Dashboard</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        :root {
            --primary: #4a6bff;
            --primary-light: #eef1ff;
            --secondary: #ff6b6b;
            --dark: #1e293b;
            --light: #f8fafc;
            --gray: #94a3b8;
            --success: #10b981;
            --warning: #f59e0b;
            --danger: #ef4444;
            --sidebar: #1a1a2e;
            --sidebar-active: #4a6bff;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        body {
            background-color: #f5f7fa;
            color: var(--dark);
            display: flex;
            min-height: 100vh;
        }

        /* Sidebar */
        

        /* Main Content */
        .main-content {
            flex: 1;
            margin-left: 250px;
            padding: 2rem;
        }

        /* Header */
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
            padding-bottom: 1rem;
            border-bottom: 1px solid #e2e8f0;
        }

        .header h1 {
            font-size: 1.8rem;
            color: var(--dark);
        }

        .date-range-selector {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .date-range-btn {
            padding: 0.5rem 1rem;
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            background: white;
            cursor: pointer;
            font-size: 0.9rem;
            transition: all 0.3s ease;
        }

        .date-range-btn.active {
            background: var(--primary);
            color: white;
            border-color: var(--primary);
        }

        .custom-date-range {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 0.9rem;
        }

        .custom-date-range input {
            padding: 0.5rem;
            border: 1px solid #e2e8f0;
            border-radius: 6px;
        }

        .user-profile {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .user-profile img {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            object-fit: cover;
        }

        .user-info {
            display: flex;
            flex-direction: column;
        }

        .user-name {
            font-weight: 600;
            font-size: 0.9rem;
        }

        .user-role {
            font-size: 0.8rem;
            color: var(--gray);
        }

        /* Stats Cards */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .stat-card {
            background: white;
            border-radius: 10px;
            padding: 1.5rem;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.05);
            transition: transform 0.3s ease;
        }

        .stat-card:hover {
            transform: translateY(-5px);
        }

        .stat-title {
            font-size: 0.9rem;
            color: var(--gray);
            margin-bottom: 0.5rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .stat-value {
            font-size: 1.8rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
        }

        .stat-change {
            display: flex;
            align-items: center;
            font-size: 0.85rem;
        }

        .stat-change.positive {
            color: var(--success);
        }

        .stat-change.negative {
            color: var(--danger);
        }

        /* Charts Section */
        .charts-grid {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        @media (max-width: 1200px) {
            .charts-grid {
                grid-template-columns: 1fr;
            }
        }

        .chart-card {
            background: white;
            border-radius: 10px;
            padding: 1.5rem;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.05);
        }

        .chart-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
        }

        .chart-title {
            font-size: 1.1rem;
            font-weight: 600;
        }

        .chart-legend {
            display: flex;
            gap: 1rem;
        }

        .legend-item {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 0.85rem;
        }

        .legend-color {
            width: 12px;
            height: 12px;
            border-radius: 3px;
        }

        .chart-container {
            position: relative;
            height: 300px;
            width: 100%;
        }

        /* Top Items Table */
        .top-items-card {
            background: white;
            border-radius: 10px;
            padding: 1.5rem;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.05);
            margin-bottom: 2rem;
        }

        .table-responsive {
            overflow-x: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th, td {
            padding: 0.75rem 1rem;
            text-align: left;
            border-bottom: 1px solid #e2e8f0;
        }

        th {
            font-weight: 600;
            color: var(--gray);
            font-size: 0.85rem;
            text-transform: uppercase;
        }

        .progress-bar {
            height: 8px;
            background: #e2e8f0;
            border-radius: 4px;
            overflow: hidden;
        }

        .progress-value {
            height: 100%;
            background: var(--primary);
            border-radius: 4px;
        }

        /* Responsive Design */
        @media (max-width: 992px) {
            .sidebar {
                width: 70px;
                overflow: hidden;
            }
            
            .sidebar-header h2, 
            .menu-item span {
                display: none;
            }
            
            .menu-item a {
                justify-content: center;
                padding: 0.8rem 0;
            }
            
            .menu-item i {
                margin-right: 0;
                font-size: 1.3rem;
            }
            
            .main-content {
                margin-left: 70px;
            }
        }

        @media (max-width: 768px) {
            .header {
                flex-direction: column;
                align-items: flex-start;
                gap: 1rem;
            }
            
            .date-range-selector {
                width: 100%;
                overflow-x: auto;
                padding-bottom: 10px;
            }
            
            .custom-date-range {
                flex-direction: column;
                align-items: flex-start;
            }
        }

        @media (max-width: 576px) {
            .main-content {
                padding: 1.5rem;
            }
            
            .stats-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <!-- Sidebar Navigation -->
    <?php include 'sideber.php' ?>

    <!-- Main Content -->
    <main class="main-content">
        <!-- Header -->
        <div class="header">
            <h1>Analytics Dashboard</h1>
            <div class="user-profile">
                <img src="uploads\<?php echo $_SESSION['restaurant_logo']; ?>" alt="Admin User">
                <div class="user-info">
                    <span class="user-name"><?php echo $_SESSION['admin_name']; ?></span>
                    <span class="user-role">Super Admin</span>
                </div>
            </div> 
        </div>
        <div class="header">
            <div class="date-range-selector">
                <button class="date-range-btn">Today</button>
                <button class="date-range-btn">Week</button>
                <button class="date-range-btn active">Month</button>
                <button class="date-range-btn">Year</button>
                <div class="custom-date-range">
                    <span>Custom:</span>
                    <input type="date" value="2023-06-01">
                    <span>to</span>
                    <input type="date" value="2023-06-30">
                </div>
            </div>
        </div>

        <!-- Stats Cards -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-title">
                    <span>Total Revenue</span>
                    <i class="fas fa-dollar-sign"></i>
                </div>
                <div class="stat-value">$24,568</div>
                <div class="stat-change positive">
                    <i class="fas fa-arrow-up"></i> 12.5% from last month
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-title">
                    <span>Total Orders</span>
                    <i class="fas fa-shopping-bag"></i>
                </div>
                <div class="stat-value">1,245</div>
                <div class="stat-change positive">
                    <i class="fas fa-arrow-up"></i> 8.3% from last month
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-title">
                    <span>Average Order Value</span>
                    <i class="fas fa-calculator"></i>
                </div>
                <div class="stat-value">$19.73</div>
                <div class="stat-change positive">
                    <i class="fas fa-arrow-up"></i> 3.9% from last month
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-title">
                    <span>New Customers</span>
                    <i class="fas fa-user-plus"></i>
                </div>
                <div class="stat-value">342</div>
                <div class="stat-change negative">
                    <i class="fas fa-arrow-down"></i> 2.1% from last month
                </div>
            </div>
        </div>

        <!-- Charts Section -->
        <div class="charts-grid">
            <div class="chart-card">
                <div class="chart-header">
                    <h3 class="chart-title">Revenue Overview</h3>
                    <div class="chart-legend">
                        <div class="legend-item">
                            <div class="legend-color" style="background: #4a6bff;"></div>
                            <span>This Month</span>
                        </div>
                        <div class="legend-item">
                            <div class="legend-color" style="background: #e2e8f0;"></div>
                            <span>Last Month</span>
                        </div>
                    </div>
                </div>
                <div class="chart-container">
                    <canvas id="revenueChart"></canvas>
                </div>
            </div>
            
            <div class="chart-card">
                <div class="chart-header">
                    <h3 class="chart-title">Order Types</h3>
                </div>
                <div class="chart-container">
                    <canvas id="orderTypeChart"></canvas>
                </div>
            </div>
        </div>

        <!-- Additional Charts Row -->
        <div class="charts-grid">
            <div class="chart-card">
                <div class="chart-header">
                    <h3 class="chart-title">Customer Traffic</h3>
                    <div class="chart-legend">
                        <div class="legend-item">
                            <div class="legend-color" style="background: #4a6bff;"></div>
                            <span>Visits</span>
                        </div>
                        <div class="legend-item">
                            <div class="legend-color" style="background: #ff6b6b;"></div>
                            <span>Orders</span>
                        </div>
                    </div>
                </div>
                <div class="chart-container">
                    <canvas id="trafficChart"></canvas>
                </div>
            </div>
            
            <div class="chart-card">
                <div class="chart-header">
                    <h3 class="chart-title">Peak Hours</h3>
                </div>
                <div class="chart-container">
                    <canvas id="peakHoursChart"></canvas>
                </div>
            </div>
        </div>

        <!-- Top Items Table -->
        <div class="top-items-card">
            <div class="chart-header">
                <h3 class="chart-title">Top Selling Menu Items</h3>
                <div class="chart-legend">
                    <span>Last 30 Days</span>
                </div>
            </div>
            
            <div class="table-responsive">
                <table>
                    <thead>
                        <tr>
                            <th>Item</th>
                            <th>Category</th>
                            <th>Orders</th>
                            <th>Revenue</th>
                            <th>Popularity</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>Classic Burger</td>
                            <td>Burgers</td>
                            <td>287</td>
                            <td>$2,587.50</td>
                            <td>
                                <div class="progress-bar">
                                    <div class="progress-value" style="width: 85%"></div>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td>Margherita Pizza</td>
                            <td>Pizza</td>
                            <td>198</td>
                            <td>$2,376.00</td>
                            <td>
                                <div class="progress-bar">
                                    <div class="progress-value" style="width: 72%"></div>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td>Sushi Platter</td>
                            <td>Japanese</td>
                            <td>156</td>
                            <td>$2,496.00</td>
                            <td>
                                <div class="progress-bar">
                                    <div class="progress-value" style="width: 65%"></div>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td>Caesar Salad</td>
                            <td>Salads</td>
                            <td>132</td>
                            <td>$1,056.00</td>
                            <td>
                                <div class="progress-bar">
                                    <div class="progress-value" style="width: 58%"></div>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td>Chocolate Sundae</td>
                            <td>Desserts</td>
                            <td>121</td>
                            <td>$726.00</td>
                            <td>
                                <div class="progress-bar">
                                    <div class="progress-value" style="width: 52%"></div>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </main>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Revenue Chart
            const revenueCtx = document.getElementById('revenueChart').getContext('2d');
            const revenueChart = new Chart(revenueCtx, {
                type: 'bar',
                data: {
                    labels: ['Week 1', 'Week 2', 'Week 3', 'Week 4'],
                    datasets: [
                        {
                            label: 'This Month',
                            data: [4500, 5200, 6800, 8068],
                            backgroundColor: '#4a6bff',
                            borderRadius: 6,
                            borderSkipped: false,
                        },
                        {
                            label: 'Last Month',
                            data: [3800, 4500, 5200, 6100],
                            backgroundColor: '#e2e8f0',
                            borderRadius: 6,
                            borderSkipped: false,
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false,
                        },
                        tooltip: {
                            mode: 'index',
                            intersect: false,
                        }
                    },
                    scales: {
                        x: {
                            grid: {
                                display: false
                            }
                        },
                        y: {
                            beginAtZero: true,
                            grid: {
                                drawBorder: false
                            },
                            ticks: {
                                callback: function(value) {
                                    return '$' + value.toLocaleString();
                                }
                            }
                        }
                    }
                }
            });

            // Order Type Chart
            const orderTypeCtx = document.getElementById('orderTypeChart').getContext('2d');
            const orderTypeChart = new Chart(orderTypeCtx, {
                type: 'doughnut',
                data: {
                    labels: ['Dine-in', 'Takeaway', 'Delivery'],
                    datasets: [{
                        data: [45, 30, 25],
                        backgroundColor: [
                            '#4a6bff',
                            '#ff6b6b',
                            '#10b981'
                        ],
                        borderWidth: 0,
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    cutout: '70%',
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: {
                                usePointStyle: true,
                                pointStyle: 'circle',
                                padding: 20
                            }
                        }
                    }
                }
            });

            // Traffic Chart
            const trafficCtx = document.getElementById('trafficChart').getContext('2d');
            const trafficChart = new Chart(trafficCtx, {
                type: 'line',
                data: {
                    labels: ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'],
                    datasets: [
                        {
                            label: 'Visits',
                            data: [120, 150, 180, 130, 200, 250, 220],
                            borderColor: '#4a6bff',
                            backgroundColor: 'rgba(74, 107, 255, 0.05)',
                            borderWidth: 2,
                            tension: 0.3,
                            fill: true
                        },
                        {
                            label: 'Orders',
                            data: [80, 90, 110, 85, 120, 150, 130],
                            borderColor: '#ff6b6b',
                            backgroundColor: 'rgba(255, 107, 107, 0.05)',
                            borderWidth: 2,
                            tension: 0.3,
                            fill: true
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false,
                        }
                    },
                    scales: {
                        x: {
                            grid: {
                                display: false
                            }
                        },
                        y: {
                            beginAtZero: true,
                            grid: {
                                drawBorder: false
                            }
                        }
                    }
                }
            });

            // Peak Hours Chart
            const peakHoursCtx = document.getElementById('peakHoursChart').getContext('2d');
            const peakHoursChart = new Chart(peakHoursCtx, {
                type: 'bar',
                data: {
                    labels: ['11AM', '12PM', '1PM', '2PM', '3PM', '4PM', '5PM', '6PM', '7PM', '8PM', '9PM'],
                    datasets: [{
                        label: 'Orders',
                        data: [15, 45, 60, 40, 20, 25, 50, 70, 85, 60, 30],
                        backgroundColor: '#4a6bff',
                        borderRadius: 6,
                        borderSkipped: false,
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false,
                        }
                    },
                    scales: {
                        x: {
                            grid: {
                                display: false
                            }
                        },
                        y: {
                            beginAtZero: true,
                            grid: {
                                drawBorder: false
                            }
                        }
                    }
                }
            });

            // Date range selector
            const dateRangeBtns = document.querySelectorAll('.date-range-btn');
            dateRangeBtns.forEach(btn => {
                btn.addEventListener('click', function() {
                    dateRangeBtns.forEach(b => b.classList.remove('active'));
                    this.classList.add('active');
                });
            });
        });
    </script>
</body>
</html>