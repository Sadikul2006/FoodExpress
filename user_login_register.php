<?php
session_start();
include 'database_connection.php';

if (isset($_GET['logout'])) {
    session_unset();
    session_destroy();
    header("Location: index.php");
    exit();
}

if (isset($_SESSION['user_id'])) {
    header("Location: dashboard.php");
    exit();
}

function data_check($data)
{
    $data = htmlentities($data);
    $data = trim($data);
    $data = stripslashes($data);
    return $data;
}
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    if (empty($email) || empty($password)) {
        $_SESSION['error'] = "Email and password are required";
        header("Location: user_login_register.php");
        exit();
    }

    $stmt = $conn->prepare("SELECT id, name, email, image, password FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();

        // Verify password
        if (password_verify($password, $user['password'])) {
            // Login successful
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['name'];
            $_SESSION['email'] = $user['email'];
            $_SESSION['user_img'] = $user['image'];
            $_SESSION['success'] = "Login successful";

            header("Location: dashboard.php");
            exit();
        } else {
            $_SESSION['error'] = "Invalid password";
            header("Location: user_login_register.php");
            exit();
        }
    } else {
        $_SESSION['error'] = "User not found";
        header("Location: user_login_register.php");
        exit();
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
    <link href="app_style/user_login_regiester.css" rel="stylesheet">
</head>

<body>
    <div class="floating-container" id="floatingContainer">
        <!-- Icons will be added dynamically by JavaScript -->
    </div>

    <?php $tab = isset($_GET['tab']) ? $_GET['tab'] : 'login'; ?>
    
    <div class="container">
        <div class="auth-container">
            <div class="auth-header">
                <div class="auth-logo">
                    <i class="fas fa-utensils"></i>
                </div>
                <h2 class="mb-0"><?php echo ($tab == 'login') ? 'Welcome Back!' : 'Create Account'; ?></h2>
                <p class="mb-0"><?php echo ($tab == 'login') ? 'Sign in to order faster and save your favorites' : 'Join us to enjoy faster checkout and save your favorites'; ?></p>
            </div>


            <div class="auth-body">
                <ul class="nav nav-tabs nav-justified mb-4" id="authTab" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button
                            class="nav-link <?php echo ($tab == 'login') ? 'active' : ''; ?>"
                            id="login-tab"
                            data-bs-toggle="tab"
                            data-bs-target="#login"
                            type="button"
                            role="tab">Login</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button
                            class="nav-link <?php echo ($tab == 'register') ? 'active' : ''; ?>"
                            id="register-tab"
                            data-bs-toggle="tab"
                            data-bs-target="#register"
                            type="button"
                            role="tab">Register</button>
                    </li>
                </ul>



                <div class="tab-content" id="authTabContent">
                    <!-- Login Form -->
                    <div class="tab-pane fade <?php echo ($tab == 'login') ? 'show active' : ''; ?>" id="login" role="tabpanel">
                        <form action="" method="POST">
                            <div class="form-group">
                                <label for="loginEmail"><i class="fas fa-envelope"></i> Email Address</label>
                                <div class="input-group">
                                    <input type="email" class="form-control" id="loginEmail" name="email" placeholder="your@email.com" required>
                                </div>
                            </div>

                            <div class="form-group password-container">
                                <label for="loginPassword"><i class="fas fa-lock"></i> Password</label>
                                <div class="input-group">
                                    <input type="password" class="form-control" id="loginPassword" name="password" placeholder="Enter your password" required>
                                </div>
                                <i class="fas fa-eye toggle-password" id="toggleLoginPassword"></i>
                            </div>

                            <div class="d-flex justify-content-between mb-4">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="rememberMe">
                                    <label class="form-check-label" for="rememberMe">Remember me</label>
                                </div>
                                <a href="forgot-password.php" class="text-decoration-none">Forgot password?</a>
                            </div>

                            <button type="submit" class="btn btn-primary">
                                <span>Login</span>
                            </button>
                        </form>
                    </div>

                    <!-- Registration Form -->
                    <div class="tab-pane fade <?php echo ($tab == 'register') ? 'show active' : ''; ?>" id="register" role="tabpanel">
                        <form action="register_action.php" method="POST">
                            <div class="form-group">
                                <label for="regName"><i class="fas fa-user"></i> Full Name</label>
                                <div class="input-group">
                                    <input type="text" class="form-control" id="regName" name="name" placeholder="Your full name" required>
                                </div>
                            </div>

                            <div class="form-group">
                                <label for="regEmail"><i class="fas fa-envelope"></i> Email Address</label>
                                <div class="input-group">
                                    <input type="email" class="form-control" id="regEmail" name="email" placeholder="your@email.com" required>
                                </div>
                            </div>

                            <div class="form-group password-container">
                                <label for="regPassword"><i class="fas fa-lock"></i> Password</label>
                                <div class="input-group">
                                    <input type="password" class="form-control" id="regPassword" name="password" placeholder="Create password" required>
                                </div>
                                <i class="fas fa-eye toggle-password" id="toggleRegPassword"></i>
                            </div>

                            <div class="form-group password-container">
                                <label for="regConfirmPassword"><i class="fas fa-lock"></i> Confirm Password</label>
                                <div class="input-group">
                                    <input type="password" class="form-control" id="regConfirmPassword" name="confirm_password" placeholder="Confirm password" required>
                                </div>
                                <i class="fas fa-eye toggle-password" id="toggleRegConfirmPassword"></i>
                            </div>

                            <button type="submit" class="btn btn-primary">
                                <span>Create Account</span>
                            </button>

                            <!-- <div class="login-link">
                                Already have an account? <a href="#" id="switchToLogin">Login here</a>
                            </div> -->
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
                this.parentElement.parentElement.style.transform = 'translateY(-5px)';
            });

            input.addEventListener('blur', function() {
                this.parentElement.parentElement.style.transform = 'translateY(0)';
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