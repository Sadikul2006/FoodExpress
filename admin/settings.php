<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
    include '../config/database_connection.php';
}

if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login_register.php");
    exit();
}

$restaurant_id = $_SESSION['restaurant_id'];
// Fetch restaurant info
$sql_restaurant_info = "SELECT * FROM restaurant_info WHERE restaurant_id = ?";
$stmt = $conn->prepare($sql_restaurant_info);
$stmt->bind_param("i", $restaurant_id);
$stmt->execute();
$info_result = $stmt->get_result();
$info = $info_result->fetch_assoc();

$restaurant_id     = $info['restaurant_id'] ?? '';
$email             = $info['email'] ?? '';
$phone             = $info['phone'] ?? '';
$restaurant_name   = $info['restaurant_name'] ?? '';
$restaurant_place  = $info['restaurant_place'] ?? '';
$restaurant_img    = $info['restaurant_img'] ?? '';
$latitude          = $info['latitude'] ?? '';
$longitude         = $info['longitude'] ?? '';



// Fetch current settings
$sql = "SELECT * FROM restaurant_settings WHERE restaurant_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $restaurant_id);
$stmt->execute();
$result = $stmt->get_result();
$settings = $result->fetch_assoc();

$min_order_amount = $settings['min_order_amount'] ?? 0.00;
$delivery_fee = $settings['delivery_fee'] ?? 0.00;
$delivery_radius = $settings['delivery_radius'] ?? 0.00;
$prep_time = $settings['preparation_time'] ?? 45;
$enable_ordering = $settings['enable_ordering'] ?? 1;

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
            padding: 1rem 2rem;
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
            background: #f8f9fa;
            color: #dc3545;
            border: 1px solid #dc3545;
            padding: 0.4rem 0.6rem;
            border-radius: 6px;
            font-weight: 500;
            cursor: pointer;
            font-size: 0.8rem;
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
            align-items: center;
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

        .location-btn {
            height: 40px;
            padding: 10px;
            background-color: var(--success-color);
            color: white;
            border: none;
            border-radius: 7px;
        }

        .location-status {
            background: #00f50630;
            padding: 5px;
            font-size: 12px;
            border-radius: 4px;
            display: none;
        }
    </style>
</head>

<body>
    <!-- Sidebar Navigation -->
    <?php include 'sideber.php' ?>

    <!-- Main Content -->
    <main class="main-content">
        <div class="top-nav">
            <div class="search-bar">
                <h1 class="page-title">Settings</h1>
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
                <a class="logout-btn" href='action_login.php?logout'>
                    <i class="fas fa-sign-out-alt"></i> Logout
                </a>
            </div>
        </div>

        <!-- Settings Card -->
        <div class="settings-card">
            <!-- Logout Button -->
            <!-- <a class="logout-btn" href='action_login.php?logout'>
                <i class="fas fa-sign-out-alt"></i> Logout
            </a> -->

            <!-- Settings Tabs -->
            <div class="settings-tabs">
                <div class="tab active" data-tab="business">Restaurant Info</div>
                <div class="tab" data-tab="online">Online Ordering</div>
                <div class="tab" data-tab="notifications">Notifications</div>
                <div class="tab" data-tab="security">Security</div>
            </div>

            <!-- Business Info Settings -->
            <div class="settings-section active" id="business-settings">
                <form id="business-info-form">
                    <!-- Row 1 -->
                    <div class="form-row">
                        <div class="form-col">
                            <div class="form-group">
                                <label class="form-label">Your Name</label>
                                <input name="admin_name" type="text" class="form-control" placeholder="Enter your full name"
                                    value="<?php echo $_SESSION['admin_name'] ?>">
                            </div>
                        </div>

                        <div class="form-col">
                            <div class="form-group">
                                <label class="form-label">Restaurant Name</label>
                                <input name="restaurant_name" type="text" class="form-control" placeholder="Enter restaurant name"
                                    value="<?php echo $restaurant_name ?>">
                            </div>
                        </div>

                        <div class="form-col">
                            <div class="form-group">
                                <label class="form-label">Phone Number</label>
                                <input name="admin_phone" type="tel" class="form-control" placeholder="Phone number (10 digits)"
                                    value="<?php echo $phone ?>">
                            </div>
                        </div>
                    </div>

                    <!-- Row 2 -->
                    <div class="form-row">
                        <div class="form-col">
                            <div class="form-group">
                                <label class="form-label">Contact Email</label>
                                <input name="admin_email" type="email" class="form-control" value="<?php echo $email ?>">
                            </div>
                        </div>

                        <div class="form-col">
                            <div class="form-group">
                                <label class="form-label">Restaurant Places</label>
                                <input name="restaurant_places" type="text" class="form-control"
                                    placeholder="Village / Post / District"
                                    value="<?php echo $restaurant_place ?>">
                            </div>
                        </div>

                        <div class="form-col">
                            <div class="form-group">
                                <label class="form-label">Restaurant Image</label>
                                <input name="restaurant_image" type="file" class="form-control" id="restImage"
                                    accept="image/png, image/jpeg, image/jpg">
                                <small class="text-muted">Only JPG, JPEG, PNG | Max 2MB</small>
                            </div>
                        </div>
                    </div>

                    <!-- Row 3: Latitude Longitude -->
                    <div class="form-row">
                        <div class="form-col">
                            <div class="form-group">
                                <label class="form-label">Latitude</label>
                                <input name="latitude" type="text" id="lat" class="form-control"
                                    placeholder="Auto-detected latitude" value="<?php echo $latitude ?>">
                            </div>
                        </div>

                        <div class="form-col">
                            <div class="form-group">
                                <label class="form-label">Longitude</label>
                                <input name="longitude" type="text" id="long" class="form-control"
                                    placeholder="Auto-detected longitude" value="<?php echo $longitude ?>">
                            </div>
                        </div>

                        <div class="form-col" style="display:flex; align-items:end;">
                            <button type="button" class="location-btn" id="getLocationBtn">
                                <i class="fa-solid fa-location-crosshairs"></i> Get Current Location
                            </button>
                        </div>
                    </div>

                    <div id="locationStatus" class="location-status"></div>

                    <!-- Image Preview -->
                    <div id="previewBox" style="margin-top:10px;">
                        <?php
                        $imagePath = "";
                        $display = "none";

                        // check if image exists
                        if (!empty($restaurant_img)) {
                            $filePath = "uploads/" . $restaurant_img;
                            if (file_exists($filePath)) {
                                $imagePath = $filePath;
                                $display = "block";
                            }
                        }
                        ?>
                        <img id="previewImg" src="<?= htmlspecialchars($imagePath) ?>" style="display: <?= $display ?>; width:180px; height:120px; object-fit:cover; border-radius:10px; border:1px solid #ddd;">
                    </div>



                    <!-- Buttons -->
                    <div class="btn-group">
                        <button type="submit" class="btn btn-primary">Save Changes</button>
                        <button type="button" class="btn btn-outline" onclick="resetForm()">Cancel</button>
                    </div>
                </form>
            </div>
            <!-- Online Ordering Settings -->
            <div class="settings-section" id="online-settings">
                <form id="online-settings-form" action="action_online_order.php" method="POST">
                    <div class="form-row">
                        <div class="form-col">
                            <div class="form-group">
                                <label class="form-label">Minimum Order Amount</label>
                                <input name="min_order_amount" type="number" class="form-control" value="<?= $min_order_amount ?>" step="0.01">
                            </div>
                        </div>
                        <div class="form-col">
                            <div class="form-group">
                                <label class="form-label">Delivery Fee</label>
                                <input name="delivery_fee" type="number" class="form-control" value="<?= $delivery_fee ?>" step="0.01">
                            </div>
                        </div>
                        <div class="form-col">
                            <div class="form-group">
                                <label class="form-label">Delivery Radius (km)</label>
                                <input name="delivery_radius" type="number" class="form-control" value="<?= $delivery_radius ?>" step="0.01" min="0">
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <div class="form-col">
                            <label class="form-label">Order Preparation Time</label>
                            <select name="prep_time" class="form-control form-select">
                                <option value="15" <?= ($prep_time == 15) ? "selected" : "" ?>>15 minutes</option>
                                <option value="30" <?= ($prep_time == 30) ? "selected" : "" ?>>30 minutes</option>
                                <option value="45" <?= ($prep_time == 45) ? "selected" : "" ?>>45 minutes</option>
                                <option value="60" <?= ($prep_time == 60) ? "selected" : "" ?>>60 minutes</option>
                            </select>
                        </div>
                        <div class="form-col">
                            <label class="form-label">Business Hours</label>
                            <div style="display: flex; flex-direction: column; gap: 1rem;">
                                <div style="display: flex; align-items: center; gap: 1rem;">
                                    <input type="time" name="opening_time" class="form-control" value="<?= $settings['opening_time'] ?? '09:00' ?>" style="width: 130px;">
                                    <span>to</span>
                                    <input type="time" name="closing_time" class="form-control" value="<?= $settings['closing_time'] ?? '22:00' ?>" style="width: 130px;">
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label" style="display: flex; align-items: center; gap: 1rem;">
                            <label class="switch">
                                <input type="checkbox" name="enable_ordering" <?= ($enable_ordering == 1) ? "checked" : "" ?>>
                                <span class="slider"></span>
                            </label>
                            <span>Enable Online Ordering</span>
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

            // Location detection functionality
            const getLocationBtn = document.getElementById('getLocationBtn');
            const latInput = document.getElementById('lat');
            const longInput = document.getElementById('long');
            const locationStatus = document.getElementById('locationStatus');

            getLocationBtn.addEventListener('click', function() {
                getLocation();
            });

            function getLocation() {
                if (navigator.geolocation) {
                    // Show loading state
                    getLocationBtn.disabled = true;
                    getLocationBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Getting Location...';
                    locationStatus.style.display = 'none';

                    // Options for better accuracy on mobile
                    const options = {
                        enableHighAccuracy: true,
                        timeout: 10000,
                        maximumAge: 0
                    };

                    navigator.geolocation.getCurrentPosition(
                        showPosition,
                        showError,
                        options
                    );
                } else {
                    showError({
                        code: 0,
                        message: "Geolocation is not supported by this browser."
                    });
                }
            }

            function showPosition(position) {
                const lat = position.coords.latitude;
                const lng = position.coords.longitude;

                // Update input fields with high precision
                latInput.value = lat.toFixed(7);
                longInput.value = lng.toFixed(7);

                // Show success message
                locationStatus.textContent = 'Location detected successfully!';
                locationStatus.className = 'location-status location-success';
                locationStatus.style.display = 'block';

                // Reset button
                getLocationBtn.disabled = false;
                getLocationBtn.innerHTML = '<i class="fa-solid fa-location-crosshairs"></i> Get Current Location';

                console.log("Latitude: " + lat + ", Longitude: " + lng);
            }

            function showError(error) {
                let errorMessage = 'Error getting location: ';

                switch (error.code) {
                    case error.PERMISSION_DENIED:
                        errorMessage = "Location access denied. Please allow location access in your browser settings.";
                        break;
                    case error.POSITION_UNAVAILABLE:
                        errorMessage = "Location information is unavailable.";
                        break;
                    case error.TIMEOUT:
                        errorMessage = "The request to get your location timed out.";
                        break;
                    case error.UNKNOWN_ERROR:
                        errorMessage = "An unknown error occurred.";
                        break;
                    default:
                        errorMessage = error.message || "An error occurred while getting your location.";
                        break;
                }

                // Show error message
                locationStatus.textContent = errorMessage;
                locationStatus.className = 'location-status location-error';
                locationStatus.style.display = 'block';

                // Reset button
                getLocationBtn.disabled = false;
                getLocationBtn.innerHTML = '<i class="fas fa-map-marker-alt"></i> Get Current Location';

                console.error("Geolocation error: " + errorMessage);
            }
        });
    </script>



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


        document.getElementById("restImage").addEventListener("change", function() {
            const file = this.files[0];

            if (!file) return;

            // Allowed file types
            const allowedTypes = ["image/jpeg", "image/jpg", "image/png"];
            if (!allowedTypes.includes(file.type)) {
                alert("Only JPG, JPEG, and PNG images are allowed.");
                this.value = "";
                return;
            }

            // Max size check (2MB)
            if (file.size > 2 * 1024 * 1024) {
                alert("File size must be under 2MB.");
                this.value = "";
                return;
            }

            // Show real-time preview
            let imgURL = URL.createObjectURL(file);
            previewImg.src = imgURL;
            previewImg.style.display = "block";
        });
    </script>


    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        $(document).ready(function() {
            // Image preview functionality
            $('#restImage').change(function() {
                const file = this.files[0];
                if (file) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        $('#previewImg').attr('src', e.target.result).show();
                    }
                    reader.readAsDataURL(file);
                }
            });

            // Location detection
            $('#getLocationBtn').click(function() {
                getCurrentLocation();
            });

            // Form submission
            $('#business-info-form').on('submit', function(e) {
                e.preventDefault();
                submitBusinessForm();
            });

            // Load existing coordinates if available
            loadExistingCoordinates();
        });

        function getCurrentLocation() {
            const btn = $('#getLocationBtn');
            const status = $('#locationStatus');

            if (navigator.geolocation) {
                btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Getting Location...');
                status.hide();

                const options = {
                    enableHighAccuracy: true,
                    timeout: 15000,
                    maximumAge: 0
                };

                navigator.geolocation.getCurrentPosition(
                    function(position) {
                        const lat = position.coords.latitude;
                        const lng = position.coords.longitude;

                        $('#lat').val(lat.toFixed(6));
                        $('#long').val(lng.toFixed(6));

                        status.removeClass('location-error').addClass('location-success')
                            .text('Location detected successfully!').show();

                        btn.prop('disabled', false).html('<i class="fa-solid fa-location-crosshairs"></i> Get Current Location');
                    },
                    function(error) {
                        let errorMessage = 'Error getting location: ';
                        switch (error.code) {
                            case error.PERMISSION_DENIED:
                                errorMessage = "Location access denied. Please allow location access in your browser settings.";
                                break;
                            case error.POSITION_UNAVAILABLE:
                                errorMessage = "Location information unavailable.";
                                break;
                            case error.TIMEOUT:
                                errorMessage = "Location request timed out.";
                                break;
                            default:
                                errorMessage = "An unknown error occurred.";
                                break;
                        }

                        status.removeClass('location-success').addClass('location-error')
                            .text(errorMessage).show();

                        btn.prop('disabled', false).html('<i class="fa-solid fa-location-crosshairs"></i> Get Current Location');
                    },
                    options
                );
            } else {
                status.removeClass('location-success').addClass('location-error')
                    .text('Geolocation is not supported by this browser').show();
            }
        }
    </script>



    <script>
        // restaurant info settings form submit (AJAX)
        document.getElementById("business-info-form").addEventListener("submit", function(e) {
            e.preventDefault();

            const formData = new FormData(this);
            const submitBtn = document.querySelector('#business-info-form button[type="submit"]');

            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Saving...';

            $.ajax({
                url: "action_restaurant_info.php",
                type: "POST",
                data: formData,
                contentType: false,
                processData: false,
                success: function(response) {
                    showNotification(response, "success");
                },
                error: function(xhr, status, error) {
                    showNotification("Something went wrong: " + error, "error");
                },
                complete: function() {
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = 'Save Changes';
                }
            });
        });


        // Online order settings form submit (AJAX)
        document.getElementById("online-settings-form").addEventListener("submit", function(e) {
            e.preventDefault();

            const formData = new FormData(this);
            const submitBtn = document.querySelector('#online-settings-form button[type="submit"]');

            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Saving...';

            $.ajax({
                url: "action_online_order.php",
                type: "POST",
                data: formData,
                contentType: false,
                processData: false,
                dataType: "json",
                success: function(response) {

                    let type = response.type || "error";
                    let msg = response.msg || "Unknown response!";

                    showNotification(msg, type);
                },
                error: function(xhr, status, error) {
                    showNotification("Something went wrong: " + error, "error");
                },
                complete: function() {
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = 'Save Changes';
                }
            });
        });



        // =======================
        // 📌 SHOW NOTIFICATION (10 sec)
        // =======================
        function showNotification(message, type) {

            $('.custom-notification').remove();

            const notification = $(`
        <div class="custom-notification ${type}">
            <i class="fas ${type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle'}"></i>
            <span>${message}</span>
        </div>
    `);

            notification.css({
                'position': 'fixed',
                'top': '20px',
                'right': '20px',
                'background': type === 'success' ? '#10b981' : '#ef4444',
                'color': 'white',
                'padding': '1rem 1.5rem',
                'border-radius': '8px',
                'box-shadow': '0 4px 12px rgba(0,0,0,0.15)',
                'display': 'flex',
                'align-items': 'center',
                'gap': '0.5rem',
                'z-index': '100000',
                'animation': 'slideInRight 0.3s ease'
            });

            $('body').append(notification);

            // REMOVE AFTER 10 seconds ⏳
            setTimeout(() => {
                notification.css('animation', 'slideOutRight 0.3s ease');
                setTimeout(() => notification.remove(), 300);
            }, 3000);
        }


        // =======================
        // 📌 LIVE IMAGE PREVIEW
        // =======================
        $("#restImage").on("change", function() {
            const file = this.files[0];

            if (file) {
                const url = URL.createObjectURL(file);
                $("#previewImg").attr("src", url).show();
            }
        });


        // =======================
        // 📌 GET CURRENT LOCATION
        // =======================
        $("#getLocationBtn").click(function() {
            $("#locationStatus").text("Detecting location...");

            if (navigator.geolocation) {
                navigator.geolocation.getCurrentPosition(function(position) {

                    $("#lat").val(position.coords.latitude);
                    $("#long").val(position.coords.longitude);

                    $("#locationStatus").text("Location detected ✔️");
                }, function() {
                    $("#locationStatus").text("Unable to detect location ❌");
                });
            } else {
                $("#locationStatus").text("Your device does not support location.");
            }
        });


        // =======================
        // 📌 RESET FORM
        // =======================
        function resetForm() {
            document.getElementById('business-info-form').reset();
            $("#previewImg").hide();
        }
    </script>


</body>

</html>