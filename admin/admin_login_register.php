<?php
session_start();
include 'database_connection.php';

function test_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // ------------------ Login ------------------
    if (isset($_POST['login'])) {
        $email = filter_input(INPUT_POST, 'login_email', FILTER_SANITIZE_EMAIL);
        $password = $_POST['login_password'];
        
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $_SESSION['error'] = "Invalid email format";
            header("Location: admin_login_register.php");
            exit();
        }

        $stmt = $conn->prepare("SELECT * FROM admin WHERE email = ?");
        if (!$stmt) {
            $_SESSION['error'] = "Database error";
            header("Location: admin_login_register.php");
            exit();
        }

        $stmt->bind_param("s", $email);
        if (!$stmt->execute()) {
            $_SESSION['error'] = "Database error";
            header("Location: admin_login_register.php");
            exit();
        }

        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $admin = $result->fetch_assoc();

            if (password_verify($password, $admin['password'])) {
                session_regenerate_id(true);
                
                $_SESSION['admin_id'] = $admin['restaurant_id'];        
                $_SESSION['admin_name'] = $admin['name'];
                $_SESSION['admin_email'] = $admin['email'];
                $_SESSION['admin_phone'] = $admin['phone'];
                $_SESSION['restaurant_name'] = $admin['restaurant_name'];
                $_SESSION['restaurant_logo'] = $admin['restaurant_logo'];
                $_SESSION['restaurant_places'] = $admin['restaurant_place'];
                
                // $_SESSION['last_activity'] = time();
                // $_SESSION['ip_address'] = $_SERVER['REMOTE_ADDR'];
                // $_SESSION['user_agent'] = $_SERVER['HTTP_USER_AGENT'];
                
                $_SESSION['success'] = "Login successful";
                
                unset($_SESSION['error']);
                
                header("Location: admin_dashboard.php");
                exit();
            } else {
                $_SESSION['error'] = "Invalid email or password";
                header("Location: admin_login_register.php");
                exit();
            }
        } else {
            $_SESSION['error'] = "Invalid email or password";
            header("Location: admin_login_register.php");
            exit();
        }
        
        $stmt->close();
    }



    // ------------------ Register ------------------
    if (isset($_POST['register'])) {
        $errors = [];

        // Sanitize input
        $name = test_input($_POST['owner_name']);
        $email = test_input($_POST['reg_email']);
        $phone = test_input($_POST['phone']);
        $restaurant_name = test_input($_POST['restaurant_name']);
        $restaurant_place = test_input($_POST['restaurant_place']);
        $password = $_POST['password'];
        $confirm_password = $_POST['confirm_password'];

        // Validate
        if ($password !== $confirm_password) {
            $errors[] = "Passwords do not match";
        }
        if (strlen($password) < 8) {
            $errors[] = "Password must be at least 8 characters";
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = "Invalid email format";
        }

        // Check if email exists
        $stmt = $conn->prepare("SELECT id FROM admin WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();
        if ($stmt->num_rows > 0) {
            $errors[] = "Email already exists";
        }
        $stmt->close();

        // Upload logo
        $restaurant_logo = null;
        if (!empty($_FILES['restaurant_logo']['name']) && $_FILES['restaurant_logo']['error'] === UPLOAD_ERR_OK) {
            $logo_name = $_FILES['restaurant_logo']['name'];
            $logo_tmp = $_FILES['restaurant_logo']['tmp_name'];
            $logo_size = $_FILES['restaurant_logo']['size'];

            $allowed_extensions = ['jpg', 'jpeg', 'png', 'webp'];
            $file_extension = strtolower(pathinfo($logo_name, PATHINFO_EXTENSION));
            $max_size = 2 * 1024 * 1024; // 2MB

            if (!in_array($file_extension, $allowed_extensions)) {
                $errors[] = "Only JPG, JPEG, PNG & WEBP files are allowed";
            } elseif ($logo_size > $max_size) {
                $errors[] = "File size must be less than 2MB";
            } else {
                $upload_dir = "uploads/";
                if (!is_dir($upload_dir)) {
                    mkdir($upload_dir, 0755, true);
                }

                $restaurant_logo = uniqid() . '_' . date('Ymd') . '.' . $file_extension;
                $destination = $upload_dir . $restaurant_logo;

                if (!move_uploaded_file($logo_tmp, $destination)) {
                    $errors[] = "Failed to upload file";
                }
            }
        } else {
            $errors[] = "Restaurant logo is required";
        }

        // Final registration
        if (empty($errors)) {
            $password_hash = password_hash($password, PASSWORD_DEFAULT);

            $stmt = $conn->prepare("INSERT INTO admin (name, email, phone, restaurant_name, restaurant_place, password, restaurant_logo) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("sssssss", $name, $email, $phone, $restaurant_name, $restaurant_place, $password_hash, $restaurant_logo);

            if ($stmt->execute()) {
                $_SESSION['success'] = "Registration successful!";
                header("Location: admin_login_register.php");
                exit();
            } else {
                $errors[] = "Registration failed: " . $conn->error;
            }
            $stmt->close();
        }

        // If errors, redirect back
        if (!empty($errors)) {
            $_SESSION['error'] = implode("<br>", $errors);
            header("Location: register.php");
            exit();
        }
    }
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Restaurant Management Portal</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="admin_style/login_register.css" rel="stylesheet">
</head>
<body>
    <div class="floating-container" id="floatingContainer">
        <!-- Icons will be added dynamically by JavaScript -->
    </div>

    <div class="container">
        <div class="auth-container">
            <div class="auth-header">
                <div class="auth-logo">
                    <i class="fas fa-utensils"></i>
                </div>
                <h1>Create Restaurant Admin Account</h1>
                <p>Register to manage your restaurant business with our powerful admin dashboard</p>
            </div>

            <div class="auth-body">
                <ul class="nav nav-tabs nav-justified mb-4" id="authTab" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="login-tab" data-bs-toggle="tab" data-bs-target="#login"
                            type="button" role="tab">Login</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="register-tab" data-bs-toggle="tab" data-bs-target="#register"
                            type="button" role="tab">Register</button>
                    </li>
                </ul>

                <div class="tab-content" id="authTabContent">
                    <!-- Login Form -->
                    <div class="tab-pane fade show active" id="login" role="tabpanel">
                        <form action="" method="POST">
                            <div class="form-group">
                                <label for="loginEmail"><i class="fas fa-envelope"></i> Email Address</label>
                                <input type="email" id="loginEmail" name="login_email" class="form-control" placeholder="your@email.com" required />
                            </div>

                            <div class="form-group password-container">
                                <label for="loginPassword"><i class="fas fa-lock"></i> Password</label>
                                <input type="password" id="loginPassword" name="login_password" class="form-control" placeholder="Enter your password" required />
                                <i class="fas fa-eye toggle-password" id="toggleLoginPassword"></i>
                            </div>

                            <div class="d-flex justify-content-between mb-4">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="rememberMe">
                                    <label class="form-check-label" for="rememberMe">Remember me</label>
                                </div>
                                <a href="forgot-password.php" class="text-decoration-none">Forgot password?</a>
                            </div>

                            <button type="submit" name="login" class="btn btn-primary">
                                <span>Login</span>
                            </button>
                        </form>
                    </div>

                    <!-- Registration Form -->
                    <div class="tab-pane fade" id="register" role="tabpanel">
                        <form class="form-grid" action="" method="POST" enctype="multipart/form-data">
                            <div class="form-group">
                                <label for="restaurantName"><i class="fas fa-store"></i> Restaurant Name</label>
                                <input type="text" id="restaurantName" name="restaurant_name" class="form-control" placeholder="e.g. Gourmet Bistro" required />
                            </div>

                            <div class="form-group">
                                <label for="ownerName"><i class="fas fa-user-tie"></i> Owner Name</label>
                                <input type="text" id="ownerName" name="owner_name" class="form-control" placeholder="e.g. John Smith" required />
                            </div>

                            <div class="form-group">
                                <label for="place"><i class="fas fa-map-marker-alt"></i> Restaurant Location</label>
                                <input type="text" id="place" name="restaurant_place" class="form-control" placeholder="City or Area" required />
                            </div>

                            <div class="form-group">
                                <label for="logo"><i class="fas fa-camera"></i> Restaurant Logo</label>
                                <div class="file-input-container">
                                    <input type="file" id="logo" name="restaurant_logo" class="form-control file-input" required />
                                </div>
                            </div>

                            <div class="form-group">
                                <label for="regEmail"><i class="fas fa-envelope"></i> Email Address</label>
                                <input type="email" id="regEmail" name="reg_email" class="form-control" placeholder="your@email.com" required />
                            </div>

                            <div class="form-group">
                                <label for="phone"><i class="fas fa-phone"></i> Phone Number</label>
                                <input type="tel" id="phone" name="phone" class="form-control" placeholder="+1 (123) 456-7890" required />
                            </div>

                            <div class="form-group password-container">
                                <label for="regPassword"><i class="fas fa-lock"></i> Password</label>
                                <input type="password" id="regPassword" name="password" class="form-control" placeholder="Create password" required />
                                <i class="fas fa-eye toggle-password" id="toggleRegPassword"></i>
                            </div>

                            <div class="form-group password-container">
                                <label for="regConfirmPassword"><i class="fas fa-lock"></i> Confirm Password</label>
                                <input type="password" id="regConfirmPassword" name="confirm_password" class="form-control" placeholder="Confirm password" required />
                                <i class="fas fa-eye toggle-password" id="toggleRegConfirmPassword"></i>
                            </div>

                            <div class="form-group full">
                                <label for="address"><i class="fas fa-map-marked-alt"></i> Restaurant Address</label>
                                <textarea id="address" class="form-control" name="restaurant_address" rows="3" placeholder="Enter full address with street details" required></textarea>
                            </div>

                            <div class="form-group full">
                                <button type="submit" name="register" class="btn btn-primary">Register Restaurant</button>
                            </div>

                            <div class="login-link full width">
                                Already have an account? <a href="#" id="switchToLogin">Login here</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Generate floating food icons
        const container = document.getElementById('floatingContainer');
        const icons = [
            'fa-utensils', 'fa-hamburger', 'fa-pizza-slice',
            'fa-ice-cream', 'fa-cocktail', 'fa-bread-slice',
            'fa-cheese', 'fa-drumstick-bite', 'fa-fish',
            'fa-apple-alt', 'fa-lemon', 'fa-wine-glass-alt'
        ];

        // Create 20 food icons with random properties
        for (let i = 0; i < 20; i++) {
            const icon = document.createElement('i');
            const randomIcon = icons[Math.floor(Math.random() * icons.length)];
            const size = Math.floor(Math.random() * 30) + 30; // 30-60px
            const left = Math.random() * 100; // 0-100%
            const delay = Math.random() * 15; // 0-15s
            const duration = 10 + Math.random() * 20; // 10-30s

            icon.className = `fas ${randomIcon} food-icon`;
            icon.style.fontSize = `${size}px`;
            icon.style.left = `${left}%`;
            icon.style.animationDelay = `${delay}s`;
            icon.style.animationDuration = `${duration}s`;

            container.appendChild(icon);
        }

        // Toggle password visibility
        function setupPasswordToggle(eyeIconId, inputId) {
            const eyeIcon = document.getElementById(eyeIconId);
            const passwordInput = document.getElementById(inputId);

            if (eyeIcon && passwordInput) {
                eyeIcon.addEventListener('click', function() {
                    const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
                    passwordInput.setAttribute('type', type);
                    this.classList.toggle('fa-eye-slash');

                    // Add animation
                    this.style.transform = 'scale(1.3)';
                    setTimeout(() => {
                        this.style.transform = 'scale(1)';
                    }, 300);
                });
            }
        }

        // Initialize all password toggles
        setupPasswordToggle('toggleLoginPassword', 'loginPassword');
        setupPasswordToggle('toggleRegPassword', 'regPassword');
        setupPasswordToggle('toggleRegConfirmPassword', 'regConfirmPassword');

        // Add animation to form inputs when focused
        document.querySelectorAll('.form-control').forEach(input => {
            input.addEventListener('focus', function() {
                this.parentElement.style.transform = 'translateY(-5px)';
            });

            input.addEventListener('blur', function() {
                this.parentElement.style.transform = 'translateY(0)';
            });
        });

        // Tab switch animation
        document.querySelectorAll('[data-bs-toggle="tab"]').forEach(tab => {
            tab.addEventListener('click', function() {
                document.querySelectorAll('.nav-link').forEach(link => {
                    link.style.transform = 'translateY(0)';
                });
                this.style.transform = 'translateY(-3px)';
            });
        });

        // Switch to login tab from register link
        document.getElementById('switchToLogin').addEventListener('click', function(e) {
            e.preventDefault();
            const loginTab = new bootstrap.Tab(document.getElementById('login-tab'));
            loginTab.show();
        });
    </script>
</body>

</html>