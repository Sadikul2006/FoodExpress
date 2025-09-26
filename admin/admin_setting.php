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
    <title>Admin Settings | Restaurant Dashboard</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
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

        .display {
            width: 100%;
            display: flex;
            justify-content: space-between;
        }

        /* Main Content */
        .main-content {
            flex: 1;
            margin-left: 250px;
            padding: 2rem;
            padding-top: 1rem;
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

        /* Settings Card */
        .settings-card {
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);
            padding: 2rem;
            margin-bottom: 2rem;
            position: relative;
        }

        .settings-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
        }

        .settings-title {
            font-size: 1.5rem;
            color: var(--dark);
        }

        .settings-tabs {
            display: flex;
            border-bottom: 1px solid #e2e8f0;
            margin-bottom: 2rem;
        }

        .tab {
            padding: 0.8rem 1.5rem;
            cursor: pointer;
            font-weight: 500;
            color: var(--gray);
            position: relative;
            transition: all 0.3s ease;
        }

        .tab.active {
            color: var(--primary);
        }

        .tab.active::after {
            content: '';
            position: absolute;
            bottom: -1px;
            left: 0;
            width: 100%;
            height: 2px;
            background: var(--primary);
        }

        .tab:hover {
            color: var(--primary);
        }

        .logout-btn {
            position: absolute;
            top: 2rem;
            right: 2rem;
            background: #f8f9fa;
            color: #dc3545;
            border: 1px solid #dc3545;
            padding: 0.5rem 1rem;
            border-radius: 6px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .logout-btn:hover {
            background: #dc3545;
            color: white;
        }

        /* Form Elements */
        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-row {
            display: flex;
            gap: 1.5rem;
        }

        .form-col {
            flex: 1;
        }

        .form-label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 500;
            color: var(--dark);
        }

        .form-control {
            width: 100%;
            padding: 0.8rem 1rem;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            margin-bottom: 10px;
            font-size: 0.9rem;
            transition: all 0.3s ease;
        }

        .form-control:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 3px var(--primary-light);
            outline: none;
        }

        .form-select {
            appearance: none;
            background-image: url("data:image/svg+xml;charset=UTF-8,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='currentColor' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3e%3cpolyline points='6 9 12 15 18 9'%3e%3c/polyline%3e%3c/svg%3e");
            background-repeat: no-repeat;
            background-position: right 0.75rem center;
            background-size: 1em;
        }

        .form-textarea {
            min-height: 120px;
            resize: vertical;
        }

        .form-check {
            display: flex;
            align-items: center;
            margin-bottom: 0.5rem;
        }

        .form-check-input {
            margin-right: 0.5rem;
            width: 18px;
            height: 18px;
            accent-color: var(--primary);
        }

        /* Buttons */
        .btn {
            padding: 0.8rem 1.5rem;
            border-radius: 8px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s ease;
            border: none;
            font-size: 0.9rem;
        }

        .btn-primary {
            background: var(--primary);
            color: white;
        }

        .btn-primary:hover {
            background: #3a5bef;
        }

        .btn-outline {
            background: transparent;
            border: 1px solid var(--primary);
            color: var(--primary);
        }

        .btn-outline:hover {
            background: var(--primary-light);
        }

        .btn-danger {
            background: var(--danger);
            color: white;
        }

        .btn-danger:hover {
            background: #dc2626;
        }

        .btn-group {
            display: flex;
            gap: 1rem;
            margin-top: 2rem;
        }

        /* Switch Toggle */
        .switch {
            position: relative;
            display: inline-block;
            width: 50px;
            height: 24px;
        }

        .switch input {
            opacity: 0;
            width: 0;
            height: 0;
        }

        .slider {
            position: absolute;
            cursor: pointer;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: #ccc;
            transition: .4s;
            border-radius: 24px;
        }

        .slider:before {
            position: absolute;
            content: "";
            height: 18px;
            width: 18px;
            left: 3px;
            bottom: 3px;
            background-color: white;
            transition: .4s;
            border-radius: 50%;
        }

        input:checked+.slider {
            background-color: var(--primary);
        }

        input:checked+.slider:before {
            transform: translateX(26px);
        }

        /* Settings Sections */
        .settings-section {
            display: none;
        }

        .settings-section.active {
            display: block;
        }

        .settings-section.active {
            display: block;
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
            .form-row {
                flex-direction: column;
                gap: 0;
            }

            .settings-tabs {
                overflow-x: auto;
                white-space: nowrap;
                padding-bottom: 5px;
            }
        }

        @media (max-width: 576px) {
            .main-content {
                padding: 1.5rem;
            }

            .header {
                flex-direction: column;
                align-items: flex-start;
                gap: 1rem;
            }

            .user-profile {
                width: 100%;
                justify-content: space-between;
            }

            .btn-group {
                flex-direction: column;
            }

            .btn {
                width: 100%;
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
            <h1>Settings</h1>

            <div class="user-profile">
                <img src="uploads\<?php echo $_SESSION['restaurant_logo']; ?>" alt="Admin User">
                <div class="user-info">
                    <span class="user-name"><?php echo $_SESSION['admin_name']; ?></span>
                    <span class="user-role">Super Admin</span>
                </div>
            </div>
        </div>

        <!-- Settings Card -->
        <div class="settings-card">
            <!-- Logout Button -->
            <button class="logout-btn" onclick="window.location.href='logout.php'">
                <i class="fas fa-sign-out-alt"></i> Logout
            </button>

            <!-- Settings Tabs -->
            <div class="settings-tabs">
                <div class="tab active" data-tab="business">Business Info</div>
                <div class="tab" data-tab="online">Online Ordering</div>
                <div class="tab" data-tab="notifications">Notifications</div>
                <div class="tab" data-tab="security">Security</div>
            </div>

            <!-- Business Info Settings -->
            <div class="settings-section active" id="business-settings">
                <form>
                    <div class="form-row">
                        <div class="form-col">
                            <div class="form-group">
                                <label class="form-label">Name</label>
                                <input type="text" class="form-control" value="<?php echo $_SESSION['admin_name'] ?>">
                            </div>
                        </div>
                        <div class="form-col">
                            <div class="form-group">
                                <label class="form-label">Contact Email</label>
                                <input type="email" class="form-control" value="<?php echo $_SESSION['admin_email'] ?>">
                            </div>
                        </div>
                        <div class="form-col">
                            <div class="form-group">
                                <label class="form-label">Phone Number</label>
                                <input type="tel" class="form-control" value="<?php echo $_SESSION['admin_phone'] ?>">
                            </div>
                        </div>
                    </div>


                    <div class="form-row">
                        <div class="form-col">
                            <div class="form-group">
                                <label class="form-label">Restaurant Logo</label>
                                <input type="file" class="form-control">
                                <small class="text-muted">Recommended size: 300x300 pixels</small>
                            </div>
                        </div>
                        <div class="form-col">
                            <div class="form-group">
                                <label class="form-label">Restaurant Name</label>
                                <input type="text" class="form-control" value="<?php echo $_SESSION['restaurant_name'] ?>">
                            </div>
                        </div>
                        <div class="form-col">
                            <div class="form-group">
                                <label class="form-label">Restaurant Places</label>
                                <input type="text" class="form-control" value="<?php echo $_SESSION['restaurant_places'] ?>">
                            </div>
                        </div>

                    </div>

                    <div class="form-row">
                        <div class="form-col">
                            <div class="form-group">
                                <label class="form-label">City</label>
                                <input type="text" class="form-control" value="Culinary City">
                            </div>
                        </div>
                        <div class="form-col">
                            <div class="form-group">
                                <label class="form-label">State/Province</label>
                                <input type="text" class="form-control" value="Food State">
                            </div>
                        </div>
                        <div class="form-col">
                            <div class="form-group">
                                <label class="form-label">ZIP/Postal Code</label>
                                <input type="text" class="form-control" value="12345">
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Business Hours</label>
                        <div style="display: flex; flex-direction: column; gap: 1rem;">
                            <div style="display: flex; align-items: center; gap: 1rem;">
                                <input type="time" class="form-control" value="09:00" style="width: 130px;">
                                <span>to</span>
                                <input type="time" class="form-control" value="22:00" style="width: 130px;">
                            </div>
                        </div>
                    </div>

                    <div class="btn-group">
                        <button type="button" class="btn btn-primary">Save Changes</button>
                        <button type="button" class="btn btn-outline">Cancel</button>
                    </div>
                </form>
            </div>
            <!-- Online Ordering Settings -->
            <div class="settings-section" id="online-settings">
                <form action="action_setting.php" method="post">
                    <div class="form-col">
                        <div class="form-group">
                            <label class="form-label">Minimum Order Amount</label>
                            <input name="min_order_amount" type="number" class="form-control" value="10.00" step="0.01">
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-col">
                            <div class="form-group">
                                <label class="form-label">Minimum Order Amount</label>
                                <input name="min_order_amount" type="number" class="form-control" value="10.00" step="0.01">
                            </div>
                        </div>
                        <div class="form-col">
                            <div class="form-group">
                                <label class="form-label">Delivery Fee</label>
                                <input name="delivery_fee" type="number" class="form-control" value="3.99" step="0.01">
                            </div>
                        </div>
                        <div class="form-col">
                            <div class="form-group">
                                <label class="form-label">Delivery Radius (miles)</label>
                                <input name="delivery_radius" type="number" class="form-control" value="5">
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Order Preparation Time</label>
                        <select name="prep_time" class="form-control form-select">
                            <option value="15 minutes">15 minutes</option>
                            <option value="30 minutes">30 minutes</option>
                            <option value="45 minutes" selected>45 minutes</option>
                            <option value="60 minutes">60 minutes</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="form-label" style="display: flex; align-items: center; gap: 1rem;">
                            <label class="switch">
                                <input type="checkbox" name="enable_ordering" checked>
                                <span class="slider"></span>
                            </label>
                            <span>Enable Online Ordering</span>
                        </label>
                    </div>

                    <div class="form-group">
                        <label class="form-label" style="display: flex; align-items: center; gap: 1rem;">
                            <label class="switch">
                                <input type="checkbox" name="require_account">
                                <span class="slider"></span>
                            </label>
                            <span>Require Customer Account</span>
                        </label>
                    </div>


                    <div class="btn-group">
                        <button type="submit" class="btn btn-primary">Save Changes</button>
                        <button type="button" class="btn btn-outline">Cancel</button>
                    </div>
                </form>

            </div>

            <!-- Notifications Settings -->
            <div class="settings-section" id="notifications-settings">
                <form>
                    <div class="form-group">
                        <label class="form-label">Email Notifications</label>
                        <div class="form-check">
                            <input type="checkbox" class="form-check-input" id="new-orders" checked>
                            <label for="new-orders">New orders</label>
                        </div>
                        <div class="form-check">
                            <input type="checkbox" class="form-check-input" id="cancellations" checked>
                            <label for="cancellations">Order cancellations</label>
                        </div>
                        <div class="form-check">
                            <input type="checkbox" class="form-check-input" id="reservations" checked>
                            <label for="reservations">New reservations</label>
                        </div>
                        <div class="form-check">
                            <input type="checkbox" class="form-check-input" id="reviews">
                            <label for="reviews">Customer reviews</label>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">SMS Notifications</label>
                        <div class="form-check">
                            <input type="checkbox" class="form-check-input" id="sms-new-orders" checked>
                            <label for="sms-new-orders">New orders</label>
                        </div>
                        <div class="form-check">
                            <input type="checkbox" class="form-check-input" id="sms-cancellations">
                            <label for="sms-cancellations">Order cancellations</label>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Notification Email Addresses</label>
                        <textarea class="form-control form-textarea">manager@gourmet.com
admin@gourmet.com</textarea>
                        <small class="text-muted">Enter one email address per line</small>
                    </div>

                    <div class="form-group">
                        <label class="form-label">SMS Notification Numbers</label>
                        <input type="text" class="form-control" value="+11234567890, +10987654321">
                        <small class="text-muted">Separate numbers with commas</small>
                    </div>

                    <div class="btn-group">
                        <button type="button" class="btn btn-primary">Save Changes</button>
                        <button type="button" class="btn btn-outline">Cancel</button>
                    </div>
                </form>
            </div>

            <!-- Security Settings -->
            <div class="settings-section" id="security-settings">
                <form>
                    <div class="form-group">
                        <label class="form-label">Change Password</label>
                        <input type="password" class="form-control" placeholder="Current password">
                        <input type="password" class="form-control mt-2" placeholder="New password">
                        <input type="password" class="form-control mt-2" placeholder="Confirm new password">
                        <small class="text-muted">Password must be at least 8 characters long</small>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Two-Factor Authentication</label>
                        <div style="display: flex; align-items: center; gap: 1rem; margin-bottom: 1rem;">
                            <label class="switch">
                                <input type="checkbox">
                                <span class="slider"></span>
                            </label>
                            <span>Disabled</span>
                        </div>
                        <button type="button" class="btn btn-outline">Set Up 2FA</button>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Active Sessions</label>
                        <div style="background: var(--light); border-radius: 8px; padding: 1rem;">
                            <div style="display: flex; justify-content: space-between; align-items: center; padding: 0.5rem 0; border-bottom: 1px solid #e2e8f0;">
                                <div>
                                    <div style="font-weight: 500;">Chrome on Windows</div>
                                    <div style="font-size: 0.8rem; color: var(--gray);">192.168.1.1 - Just now</div>
                                </div>
                                <button type="button" class="btn btn-outline" style="padding: 0.3rem 0.8rem; font-size: 0.8rem;">Logout</button>
                            </div>
                            <div style="display: flex; justify-content: space-between; align-items: center; padding: 0.5rem 0;">
                                <div>
                                    <div style="font-weight: 500;">Safari on iPhone</div>
                                    <div style="font-size: 0.8rem; color: var(--gray);">192.168.1.2 - 2 hours ago</div>
                                </div>
                                <button type="button" class="btn btn-outline" style="padding: 0.3rem 0.8rem; font-size: 0.8rem;">Logout</button>
                            </div>
                        </div>
                    </div>

                    <div class="btn-group">
                        <button type="button" class="btn btn-primary">Save Changes</button>
                        <button type="button" class="btn btn-outline">Cancel</button>
                    </div>
                </form>
            </div>
        </div>
    </main>

    <script>
        // Tab switching functionality
        document.addEventListener('DOMContentLoaded', function() {
            const tabs = document.querySelectorAll('.tab');
            const sections = document.querySelectorAll('.settings-section');

            tabs.forEach(tab => {
                tab.addEventListener('click', function() {
                    // Remove active class from all tabs and sections
                    tabs.forEach(t => t.classList.remove('active'));
                    sections.forEach(s => s.classList.remove('active'));

                    // Add active class to clicked tab
                    this.classList.add('active');

                    // Show corresponding section
                    const tabId = this.getAttribute('data-tab');
                    document.getElementById(`${tabId}-settings`).classList.add('active');
                });
            });

        });
    </script>
</body>

</html>