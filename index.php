<?php
session_start();
include 'config/database_connection.php';

if (isset($_SESSION['user_id'])) {
    header("Location: restaurant_menu.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FoodExpress - Order from Multiple Restaurants</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
    <style>
        :root {
            --primary: #FF6B6B;
            --primary-dark: #e05555;
            --secondary: #FFA500;
            --secondary-dark: #e69500;
            --dark: #1E293B;
            --darker: #0F172A;
            --light: #F8FAFC;
            --gray: #94A3B8;
            --light-gray: #E2E8F0;
            --success: #10B981;
            --border-radius: 12px;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f9f9f9;
            color: var(--dark);
            line-height: 1.6;
        }

        a {
            text-decoration: none;
            color: var(--primary-dark);
        }

        /* Header */
        header {
            background-color: white;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            position: sticky;
            top: 0;
            z-index: 1000;
            padding: 0 1rem;
        }

        .navbar {
            height: 70px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0.8rem 0;
            max-width: 1300px;
            margin: 0 auto;
        }

        .logo-container {
            display: flex;
            align-items: center;
        }

        #logo_img {
            height: 50px;
            width: auto;
            transition: 0.3s ease;
        }

        #logo_img:hover {
            transform: scale(1.05) rotate(-5deg);
        }

        /* Auth Buttons */
        .auth-buttons button {
            padding: 9px 18px;
            border-radius: 30px;
            border: 2px solid;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
        }

        .login {
            background: transparent;
            color: #333;
            margin-right: 10px;
            border: 1px solid #ddd;
        }

        .signup {
            background: #e74c3c;
            color: white;
            border-color: #e74c3c;
        }

        .auth-buttons button:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        /* Hero Section */
        .hero {
            background: linear-gradient(rgba(0, 0, 0, 0.7), rgba(0, 0, 0, 0.7)),
                url('https://images.unsplash.com/photo-1517248135467-4c7edcad34c4?auto=format&fit=crop&w=1350&q=80');
            background-size: cover;
            background-position: center;
            color: white;
            padding: 4rem 1.5rem 0;
            text-align: center;
            min-height: 65vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .hero-content {
            max-width: 800px;
            margin: 0 auto;
        }

        .hero h1 {
            font-size: 1.8rem;
            margin-bottom: 1rem;
            line-height: 1.3;
        }

        .hero p {
            font-size: 1rem;
            margin-bottom: 1.5rem;
            opacity: 0.9;
        }

        /* Modal */
        .modal {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(0, 0, 0, 0.7);
            z-index: 1000;
            justify-content: center;
            align-items: center;
        }

        .modal-content {
            background: white;
            border-radius: 10px;
            max-width: 400px;
            width: 90%;
            padding: 40px;
            position: relative;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
        }

        .close-btn {
            position: absolute;
            top: 15px;
            right: 15px;
            font-size: 24px;
            cursor: pointer;
            color: #777;
        }

        .form-title {
            text-align: center;
            margin-bottom: 30px;
            color: #333;
        }

        .input-group {
            margin-bottom: 20px;
        }

        .input-group label {
            display: block;
            margin-bottom: 8px;
            color: #333;
            font-weight: 500;
        }

        .input-group input {
            width: 100%;
            padding: 12px 15px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 16px;
            transition: border 0.3s;
        }

        .input-group input:focus {
            border-color: #e74c3c;
            outline: none;
        }

        .submit-btn {
            width: 100%;
            background: #e74c3c;
            color: white;
            border: none;
            padding: 12px;
            border-radius: 5px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.3s;
        }

        .submit-btn:hover {
            background: #c0392b;
        }

        .form-footer,
        .form-switch {
            text-align: center;
            margin-top: 20px;
        }

        .form-footer a,
        .form-switch a {
            color: #e74c3c;
            text-decoration: none;
            font-weight: 500;
        }

        .form-footer a:hover,
        .form-switch a:hover {
            text-decoration: underline;
        }

        .form-switch {
            padding-top: 20px;
            border-top: 1px solid #eee;
        }

        /* Footer */
        footer {
            background-color: var(--darker);
            color: white;
            padding: 3rem 1rem 1.5rem;
        }

        .footer-column {
            text-align: center;
        }

        .footer-column h3 {
            font-size: 1.1rem;
            margin-bottom: 1rem;
            position: relative;
            display: inline-block;
        }

        .footer-column h3::after {
            content: '';
            position: absolute;
            bottom: -5px;
            left: 50%;
            transform: translateX(-50%);
            width: 30px;
            height: 2px;
            background-color: var(--primary);
        }

        .footer-links {
            display: flex;
            flex-direction: column;
            gap: 0.6rem;
        }

        .footer-link {
            color: var(--light-gray);
            text-decoration: none;
            font-size: 0.85rem;
        }

        .social-links {
            display: flex;
            justify-content: center;
            gap: 0.8rem;
            margin-top: 1.2rem;
        }

        .social-link {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 35px;
            height: 35px;
            border-radius: 50%;
            background-color: rgba(255, 255, 255, 0.1);
            color: white;
        }

        .copyright {
            text-align: center;
            padding-top: 1.5rem;
            margin-top: 1.5rem;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
            color: var(--gray);
            font-size: 0.8rem;
        }

        /* Buttons */
        .btn {
            display: inline-block;
            background: #e74c3c;
            color: white;
            padding: 12px 30px;
            border-radius: 30px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s;
            border: none;
        }

        .btn:hover {
            background: #c0392b;
            transform: translateY(-3px);
        }

        .admin-login {
            margin-top: 40px;
        }

        /* Scrollbar */
        ::-webkit-scrollbar {
            display: none;
        }

        html {
            scrollbar-width: none;
        }

        /* Responsive */
        @media (min-width: 600px) {
            .hero h1 {
                font-size: 2rem;
            }
        }

        /* Forgot Password Specific Styles */
        .forgot-password-step {
            transition: all 0.3s ease;
        }

        .forgot-password-instructions {
            margin-bottom: 20px;
            padding: 10px;
            background-color: #f8f9fa;
            border-radius: 5px;
            font-size: 14px;
            color: #666;
        }

        .user-email {
            font-weight: 600;
            color: #333;
        }

        .timer {
            color: #e74c3c;
            font-weight: 600;
        }

        /* OTP Input Styles */
        .otp-container {
            margin: 20px 0;
        }

        .otp-inputs {
            display: flex;
            justify-content: center;
            gap: 10px;
            margin-bottom: 20px;
        }

        .otp-input {
            width: 50px;
            height: 60px;
            text-align: center;
            font-size: 24px;
            font-weight: bold;
            border: 2px solid #ddd;
            border-radius: 8px;
            background: white;
            transition: all 0.3s ease;
        }

        .otp-input:focus {
            border-color: #4CAF50;
            box-shadow: 0 0 0 3px rgba(76, 175, 80, 0.2);
            outline: none;
        }

        .otp-input.filled {
            border-color: #4CAF50;
            background-color: #f8fff8;
        }

        /* Form Actions */
        .form-actions {
            display: flex;
            flex-direction: column;
            gap: 10px;
            margin-top: 20px;
        }

        .submit-btn.secondary {
            background-color: #6c757d;
        }

        .submit-btn.secondary:hover {
            background-color: #5a6268;
        }

        .submit-btn:disabled {
            opacity: 0.6;
            cursor: not-allowed;
        }

        /* Password Strength Meter */
        .password-strength {
            margin: 15px 0;
        }

        .strength-bar {
            height: 6px;
            background: #ddd;
            border-radius: 3px;
            overflow: hidden;
            margin-bottom: 5px;
        }

        .strength-fill {
            height: 100%;
            width: 0%;
            background: #e74c3c;
            transition: all 0.3s ease;
        }

        .strength-text {
            font-size: 12px;
            color: #666;
        }

        /* Success State */
        .success-state {
            text-align: center;
            padding: 20px;
        }

        .success-icon {
            margin-bottom: 20px;
        }

        .success-icon i {
            font-size: 60px;
            color: #4CAF50;
        }

        /* Messages */
        .message {
            margin: 15px 0;
            padding: 10px;
            border-radius: 5px;
            text-align: center;
            font-size: 14px;
        }

        .message.success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .message.error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        .message.info {
            background-color: #d1ecf1;
            color: #0c5460;
            border: 1px solid #bee5eb;
        }

        .small-text {
            font-size: 13px;
            color: #666;
            margin-top: 5px;
        }

        .form-text {
            display: block;
            margin-top: 5px;
            font-size: 12px;
            color: #666;
        }
    </style>
</head>

<body>
    <header>
        <nav class="navbar">
            <a href="#" class="logo-container logo animate__animated animate__fadeIn">
                <img src="assets/images/logo.png" id="logo_img" alt="Restaurant Logo">
            </a>
            <div class="auth-buttons">
                <button class="login" id="loginBtn">Login</button>
                <button class="signup" id="signupBtn">Sign Up</button>
            </div>
        </nav>
    </header>

    <!-- Hero Section -->
    <section class="hero">
        <div class="hero-content">
            <h1 class="animate__animated animate__fadeInDown">Order from Your Favorite Restaurants</h1>
            <p class="animate__animated animate__fadeIn animate__delay-1s">
                Discover the best dining options in your area and get food delivered to your doorstep
            </p>

            <button class="btn" id="heroLoginBtn" type="button">Order Now</button>
            <p class="animate__animated animate__fadeIn animate__delay-1s admin-login">
                Are you Restaurant Owner ? <a href="admin/index.php">Click here</a>
            </p>
        </div>
    </section>

    <!-- Login Modal -->
    <div class="modal" id="loginModal">
        <div class="modal-content">
            <span class="close-btn" id="closeLogin">&times;</span>
            <h2 class="form-title">Login</h2>

            <form id="loginForm" action="action_login_register.php" method="POST">
                <input type="hidden" name="action" value="login">

                <div class="input-group">
                    <label for="loginEmail">Email</label>
                    <input type="email" id="loginEmail" name="email" placeholder="Enter your email" required>
                </div>

                <div class="input-group">
                    <label for="loginPassword">Password</label>
                    <input type="password" id="loginPassword" name="password" placeholder="Enter your password" required>
                </div>

                <button type="submit" class="submit-btn">Login</button>

                <div class="form-footer">
                    <a href="#" id="showForgotPassword">Forgot your password?</a>
                </div>

                <div class="form-switch">
                    <p>Don't have an account? <a href="#" id="switchToSignup">Sign up</a></p>
                </div>
            </form>
        </div>
    </div>

    <!-- Signup Modal -->
    <div class="modal" id="signupModal">
        <div class="modal-content">
            <span class="close-btn" id="closeSignup">&times;</span>
            <h2 class="form-title">Register</h2>

            <form id="signupForm" action="action_login_register.php" method="POST">
                <input type="hidden" name="action" value="signup">

                <div class="input-group">
                    <label for="signupName">Full Name</label>
                    <input type="text" id="signupName" name="name" placeholder="Enter your full name" required>
                </div>

                <div class="input-group">
                    <label for="signupEmail">Email</label>
                    <input type="email" id="signupEmail" name="email" placeholder="Enter your email" required>
                </div>

                <div class="input-group">
                    <label for="signupPassword">Password</label>
                    <input type="password" id="signupPassword" name="password" placeholder="Create a password" required>
                </div>

                <div class="input-group">
                    <label for="confirmPassword">Confirm Password</label>
                    <input type="password" id="confirmPassword" name="confirmPassword" placeholder="Confirm your password" required>
                </div>

                <button type="submit" class="submit-btn">Create Account</button>

                <div class="form-switch">
                    <p>Already have an account? <a href="#" id="switchToLogin">Login</a></p>
                </div>
            </form>
        </div>
    </div>

    <!-- Forgot Password Modal with OTP Verification -->
    <div class="modal" id="forgotPasswordModal">
        <div class="modal-content">
            <span class="close-btn" id="closeForgotPassword">&times;</span>

            <!-- Step 1: Enter Email -->
            <div id="forgotPasswordStep1" class="forgot-password-step">
                <h2 class="form-title">Reset Your Password</h2>

                <div class="forgot-password-instructions">
                    <p>Enter your email address and we'll send you an OTP to reset your password.</p>
                </div>

                <form id="forgotPasswordForm">
                    <div class="input-group">
                        <label for="forgotPasswordEmail">Email Address</label>
                        <input type="email" id="forgotPasswordEmail" name="email" placeholder="Enter your registered email" required>
                        <small class="form-text">We'll send a 6-digit OTP to this email</small>
                    </div>

                    <button type="submit" class="submit-btn" id="sendOtpBtn">Send OTP</button>

                    <div id="forgotPasswordMessage" class="message" style="display: none;"></div>

                    <div class="form-switch">
                        <p>Remember your password? <a href="#" id="backToLogin">Back to Login</a></p>
                    </div>
                </form>
            </div>

            <!-- Step 2: Enter OTP -->
            <div id="forgotPasswordStep2" class="forgot-password-step" style="display: none;">
                <h2 class="form-title">Verify OTP</h2>

                <div class="forgot-password-instructions">
                    <p>Enter the 6-digit OTP sent to <span id="userEmailDisplay" class="user-email"></span></p>
                    <p class="small-text">OTP expires in <span id="otpTimer" class="timer">05:00</span></p>
                </div>

                <form id="verifyOtpForm">
                    <div class="otp-container">
                        <div class="otp-inputs">
                            <input type="text" class="otp-input" maxlength="1" data-index="1" inputmode="numeric" pattern="[0-9]*">
                            <input type="text" class="otp-input" maxlength="1" data-index="2" inputmode="numeric" pattern="[0-9]*">
                            <input type="text" class="otp-input" maxlength="1" data-index="3" inputmode="numeric" pattern="[0-9]*">
                            <input type="text" class="otp-input" maxlength="1" data-index="4" inputmode="numeric" pattern="[0-9]*">
                            <input type="text" class="otp-input" maxlength="1" data-index="5" inputmode="numeric" pattern="[0-9]*">
                            <input type="text" class="otp-input" maxlength="1" data-index="6" inputmode="numeric" pattern="[0-9]*">
                        </div>
                        <input type="hidden" id="otpCode" name="otp">
                    </div>

                    <div id="otpMessage" class="message" style="display: none;"></div>

                    <div class="form-actions">
                        <button type="submit" class="submit-btn" id="verifyOtpBtn" disabled>Verify OTP</button>
                        <button type="button" class="submit-btn secondary" id="resendOtpBtn" disabled>Resend OTP (<span id="resendTimer">60</span>s)</button>
                    </div>

                    <div class="form-switch">
                        <p><a href="#" id="backToEmailStep">Use different email</a></p>
                    </div>
                </form>
            </div>

            <!-- Step 3: Reset Password -->
            <div id="forgotPasswordStep3" class="forgot-password-step" style="display: none;">
                <h2 class="form-title">Create New Password</h2>

                <div class="forgot-password-instructions">
                    <p>Create a new password for your account.</p>
                </div>

                <form id="resetPasswordForm">
                    <div class="input-group">
                        <label for="newPassword">New Password</label>
                        <input type="password" id="newPassword" name="new_password" placeholder="Enter new password" required>
                        <small class="form-text">Must be at least 8 characters with letters and numbers</small>
                    </div>

                    <div class="input-group">
                        <label for="confirmNewPassword">Confirm Password</label>
                        <input type="password" id="confirmNewPassword" name="confirm_new_password" placeholder="Confirm new password" required>
                    </div>

                    <div class="password-strength">
                        <div class="strength-bar">
                            <div class="strength-fill"></div>
                        </div>
                        <span class="strength-text">Password strength: <span id="strengthText">Weak</span></span>
                    </div>

                    <div id="resetPasswordMessage" class="message" style="display: none;"></div>

                    <div class="form-actions">
                        <button type="submit" class="submit-btn" id="resetPasswordBtn">Reset Password</button>
                    </div>

                    <div class="form-switch">
                        <p><a href="#" id="backToLoginFromReset">Back to Login</a></p>
                    </div>
                </form>
            </div>

            <!-- Step 4: Success Message -->
            <div id="forgotPasswordStep4" class="forgot-password-step" style="display: none;">
                <div class="success-state">
                    <div class="success-icon">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <h2>Password Reset Successful!</h2>
                    <p>Your password has been reset successfully.</p>
                    <p class="small-text">You can now login with your new password.</p>

                    <div class="form-actions">
                        <button class="submit-btn" id="goToLoginBtn">Login Now</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer>
        <div class="footer-column">
            <h3>Contact Us</h3>
            <div class="footer-links">
                <a href="#" class="footer-link">Newtown, Kolkata</a>
                <a href="tel:+1234567890" class="footer-link">+91 7872408254</a>
                <a href="mailto:info@foodexpress.com" class="footer-link">sadikulseikh56@gmail.com</a>
            </div>

            <div class="social-links">
                <a href="#" class="social-link"><i class="fab fa-facebook-f"></i></a>
                <a href="#" class="social-link"><i class="fab fa-instagram"></i></a>
                <a href="#" class="social-link"><i class="fab fa-twitter"></i></a>
                <a href="#" class="social-link"><i class="fab fa-youtube"></i></a>
            </div>
        </div>
        <p class="copyright">© 2023 FoodExpress. All rights reserved.</p>
    </footer>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        $(document).ready(function() {
            // Modal elements using jQuery selectors
            const $loginBtn = $('#loginBtn');
            const $signupBtn = $('#signupBtn');
            const $loginModal = $('#loginModal');
            const $signupModal = $('#signupModal');
            const $forgotPasswordModal = $('#forgotPasswordModal');
            const $closeLogin = $('#closeLogin');
            const $closeSignup = $('#closeSignup');
            const $closeForgotPassword = $('#closeForgotPassword');
            const $switchToSignup = $('#switchToSignup');
            const $switchToLogin = $('#switchToLogin');
            const $heroLoginBtn = $('#heroLoginBtn');
            const $showForgotPassword = $('#showForgotPassword');

            // Open modals using jQuery click handlers
            $heroLoginBtn.on('click', function() {
                $loginModal.css('display', 'flex');
            });

            $loginBtn.on('click', function() {
                $loginModal.css('display', 'flex');
            });

            $signupBtn.on('click', function() {
                $signupModal.css('display', 'flex');
            });

            // Close modals
            $closeLogin.on('click', function() {
                $loginModal.css('display', 'none');
            });

            $closeSignup.on('click', function() {
                $signupModal.css('display', 'none');
            });

            $closeForgotPassword.on('click', function() {
                $forgotPasswordModal.css('display', 'none');
            });

            // Switch between login and signup
            $switchToSignup.on('click', function(e) {
                e.preventDefault();
                $loginModal.css('display', 'none');
                $signupModal.css('display', 'flex');
            });

            $switchToLogin.on('click', function(e) {
                e.preventDefault();
                $signupModal.css('display', 'none');
                $loginModal.css('display', 'flex');
            });

            // Close modal when clicking outside
            $(window).on('click', function(e) {
                if ($(e.target).is($loginModal)) {
                    $loginModal.css('display', 'none');
                }
                if ($(e.target).is($signupModal)) {
                    $signupModal.css('display', 'none');
                }
                if ($(e.target).is($forgotPasswordModal)) {
                    $forgotPasswordModal.css('display', 'none');
                }
            });

            // Forgot Password Functionality
            // Elements
            const $backToLogin = $('#backToLogin');
            const $backToEmailStep = $('#backToEmailStep');
            const $backToLoginFromReset = $('#backToLoginFromReset');
            const $goToLoginBtn = $('#goToLoginBtn');

            // Step containers
            const $step1 = $('#forgotPasswordStep1');
            const $step2 = $('#forgotPasswordStep2');
            const $step3 = $('#forgotPasswordStep3');
            const $step4 = $('#forgotPasswordStep4');

            // Forms
            const $forgotPasswordForm = $('#forgotPasswordForm');
            const $verifyOtpForm = $('#verifyOtpForm');
            const $resetPasswordForm = $('#resetPasswordForm');

            // Inputs
            const $forgotPasswordEmail = $('#forgotPasswordEmail');
            const $userEmailDisplay = $('#userEmailDisplay');
            const $otpInputs = $('.otp-input');
            const $otpCodeInput = $('#otpCode');
            const $newPasswordInput = $('#newPassword');
            const $confirmNewPasswordInput = $('#confirmNewPassword');

            // Buttons
            const $sendOtpBtn = $('#sendOtpBtn');
            const $verifyOtpBtn = $('#verifyOtpBtn');
            const $resendOtpBtn = $('#resendOtpBtn');
            const $resetPasswordBtn = $('#resetPasswordBtn');

            // Messages
            const $forgotPasswordMessage = $('#forgotPasswordMessage');
            const $otpMessage = $('#otpMessage');
            const $resetPasswordMessage = $('#resetPasswordMessage');

            // Timers
            const $otpTimer = $('#otpTimer');
            const $resendTimerElement = $('#resendTimer');

            // Variables
            let otpTimerInterval;
            let resendTimerInterval;
            let currentEmail = '';
            let otpExpiryTime = 0;
            let resendCooldown = 60; // 60 seconds cooldown for resend

            // Open Forgot Password Modal
            if ($showForgotPassword.length) {
                $showForgotPassword.on('click', function(e) {
                    e.preventDefault();
                    $loginModal.css('display', 'none');
                    $forgotPasswordModal.css('display', 'flex');
                    resetForgotPasswordForm();
                });
            }

            // Back to Login
            $backToLogin.on('click', function(e) {
                e.preventDefault();
                $forgotPasswordModal.css('display', 'none');
                $loginModal.css('display', 'flex');
            });

            // Back to Email Step
            $backToEmailStep.on('click', function(e) {
                e.preventDefault();
                showStep(1);
                clearTimers();
            });

            // Back to Login from Reset
            $backToLoginFromReset.on('click', function(e) {
                e.preventDefault();
                $forgotPasswordModal.css('display', 'none');
                $loginModal.css('display', 'flex');
            });

            // Go to Login after success
            $goToLoginBtn.on('click', function() {
                $forgotPasswordModal.css('display', 'none');
                $loginModal.css('display', 'flex');
            });

            // Step 1: Send OTP
            $forgotPasswordForm.on('submit', function(e) {
                e.preventDefault();

                const email = $forgotPasswordEmail.val().trim();

                // Validate email
                if (!email || !isValidEmail(email)) {
                    showMessage($forgotPasswordMessage, 'Please enter a valid email address', 'error');
                    return;
                }

                // Show loading state
                const originalText = $sendOtpBtn.text();
                $sendOtpBtn.text('Sending...').prop('disabled', true);

                $.ajax({
                    url: 'action_forgot_password.php',
                    type: 'POST',
                    dataType: 'json',
                    data: {
                        email: email
                    },

                    success: function(response) {

                        if (response.status === 'error') {
                            showMessage($forgotPasswordMessage, response.message, 'error');
                            return;
                        }

                        // Email exists → OTP sent
                        currentEmail = email;
                        $userEmailDisplay.text(email);

                        // Start timers
                        startOtpTimer(300); // 5 minutes
                        startResendTimer();

                        // Move to step 2
                        showStep(2);
                    },

                    error: function() {
                        showMessage($forgotPasswordMessage, 'Something went wrong. Try again.', 'error');
                    },

                    complete: function() {
                        $sendOtpBtn.text(originalText).prop('disabled', false);
                    }
                });
            });


            // OTP Input Handling
            $otpInputs.each(function(index) {
                const $input = $(this);

                $input.on('input', function(e) {
                    const value = $(this).val();

                    // Only allow numbers
                    if (!/^\d*$/.test(value)) {
                        $(this).val('');
                        return;
                    }

                    // Move to next input if a digit is entered
                    if (value.length === 1 && index < $otpInputs.length - 1) {
                        $otpInputs.eq(index + 1).focus();
                    }

                    // Update OTP code and verify button state
                    updateOtpCode();

                    // Style filled inputs
                    $(this).toggleClass('filled', value.length === 1);
                });

                $input.on('keydown', function(e) {
                    // Handle backspace
                    if (e.key === 'Backspace' && !$(this).val() && index > 0) {
                        $otpInputs.eq(index - 1).focus();
                        $otpInputs.eq(index - 1).removeClass('filled');
                    }
                });

                $input.on('paste', function(e) {
                    e.preventDefault();
                    const pasteData = e.originalEvent.clipboardData.getData('text').trim();

                    if (/^\d{6}$/.test(pasteData)) {
                        const digits = pasteData.split('');
                        $otpInputs.each(function(idx) {
                            const $currentInput = $(this);
                            if (digits[idx]) {
                                $currentInput.val(digits[idx]).addClass('filled');
                            }
                        });
                        updateOtpCode();
                        $verifyOtpBtn.focus();
                    }
                });
            });

            // Step 2: Verify OTP
            $verifyOtpForm.on('submit', function(e) {
                e.preventDefault();

                const otp = $otpCodeInput.val();

                if (otp.length !== 6) {
                    showMessage($otpMessage, 'Please enter the complete 6-digit OTP', 'error');
                    return;
                }

                const originalText = $verifyOtpBtn.text();
                $verifyOtpBtn.text('Verifying...').prop('disabled', true);

                $.ajax({
                    url: 'verify_otp.php',
                    type: 'POST',
                    dataType: 'json',
                    data: {
                        otp: otp
                    },

                    success: function(response) {

                        if (response.status === 'success') {
                            clearTimers();
                            showStep(3);
                        } else {
                            showMessage($otpMessage, response.message, 'error');
                            $otpInputs.val('').removeClass('filled');
                            updateOtpCode();
                            $otpInputs.first().focus();
                        }
                    },

                    error: function() {
                        showMessage($otpMessage, 'Server error. Try again.', 'error');
                    },

                    complete: function() {
                        $verifyOtpBtn.text(originalText).prop('disabled', false);
                    }
                });
            });


            // Resend OTP
            $resendOtpBtn.on('click', function() {
                if ($resendOtpBtn.prop('disabled')) return;

                const originalText = $resendOtpBtn.text();
                $resendOtpBtn.text('Sending...').prop('disabled', true);

                $.ajax({
                    url: 'resend_otp.php',
                    type: 'POST',
                    dataType: 'json',
                    success: function(response) {
                        if (response.status === 'success') {
                            // Clear OTP inputs
                            $otpInputs.val('').removeClass('filled');
                            updateOtpCode();

                            // Restart timers
                            clearTimers();
                            startOtpTimer(300); // 5 min
                            startResendTimer();

                            showMessage($otpMessage, 'New OTP sent successfully!', 'success');
                            $otpInputs.first().focus();
                        } else {
                            showMessage($otpMessage, response.message, 'error');
                        }
                    },
                    error: function() {
                        showMessage($otpMessage, 'Server error. Try again.', 'error');
                    },
                    complete: function() {
                        $resendOtpBtn.text(originalText).prop('disabled', false);
                    }
                });
            });


            // Step 3: Reset Password
            $resetPasswordForm.on('submit', function(e) {
                e.preventDefault();

                const newPassword = $newPasswordInput.val();
                const confirmPassword = $confirmNewPasswordInput.val();

                // Validate passwords
                if (newPassword.length < 8) {
                    showMessage($resetPasswordMessage, 'Password must be at least 8 characters long', 'error');
                    return;
                }

                if (!/(?=.*[a-zA-Z])(?=.*\d)/.test(newPassword)) {
                    showMessage($resetPasswordMessage, 'Password must contain both letters and numbers', 'error');
                    return;
                }

                if (newPassword !== confirmPassword) {
                    showMessage($resetPasswordMessage, 'Passwords do not match', 'error');
                    return;
                }

                const originalText = $resetPasswordBtn.text();
                $resetPasswordBtn.text('Resetting...').prop('disabled', true);

                $.ajax({
                    url: 'reset_password.php',
                    type: 'POST',
                    dataType: 'json',
                    data: {
                        password: newPassword
                    },

                    success: function(response) {
                        if (response.status === 'success') {
                            showStep(4); // Success step
                        } else {
                            showMessage($resetPasswordMessage, response.message, 'error');
                        }
                    },

                    error: function() {
                        showMessage($resetPasswordMessage, 'Server error. Try again.', 'error');
                    },

                    complete: function() {
                        $resetPasswordBtn.text(originalText).prop('disabled', false);
                    }
                });
            });

            // Password Strength Check
            $newPasswordInput.on('input', function() {
                checkPasswordStrength($(this).val());
            });

            // Helper Functions
            function showStep(stepNumber) {
                const steps = [$step1, $step2, $step3, $step4];
                steps.forEach(function($step, index) {
                    $step.css('display', (index + 1 === stepNumber) ? 'block' : 'none');
                });
            }

            function resetForgotPasswordForm() {
                showStep(1);
                $forgotPasswordForm.trigger('reset');
                $verifyOtpForm.trigger('reset');
                $resetPasswordForm.trigger('reset');
                $otpInputs.val('').removeClass('filled');
                updateOtpCode();
                clearTimers();
                $forgotPasswordMessage.hide();
                $otpMessage.hide();
                $resetPasswordMessage.hide();
                currentEmail = '';
            }

            function updateOtpCode() {
                let otp = '';
                $otpInputs.each(function() {
                    otp += $(this).val();
                });
                $otpCodeInput.val(otp);
                $verifyOtpBtn.prop('disabled', otp.length !== 6);
            }

            function startOtpTimer(seconds) {
                clearInterval(otpTimerInterval);
                otpExpiryTime = Date.now() + seconds * 1000;

                otpTimerInterval = setInterval(function() {
                    const now = Date.now();
                    const remaining = Math.max(0, Math.floor((otpExpiryTime - now) / 1000));

                    if (remaining <= 0) {
                        clearInterval(otpTimerInterval);
                        $otpTimer.text('00:00');
                        showMessage($otpMessage, 'OTP has expired. Please request a new one.', 'error');
                        $verifyOtpBtn.prop('disabled', true);
                        return;
                    }

                    const minutes = Math.floor(remaining / 60);
                    const secs = remaining % 60;
                    $otpTimer.text(`${minutes.toString().padStart(2, '0')}:${secs.toString().padStart(2, '0')}`);
                }, 1000);
            }

            function startResendTimer() {
                clearInterval(resendTimerInterval);
                let cooldown = resendCooldown;
                $resendOtpBtn.prop('disabled', true);
                $resendTimerElement.text(cooldown);

                resendTimerInterval = setInterval(function() {
                    cooldown--;
                    $resendTimerElement.text(cooldown);

                    if (cooldown <= 0) {
                        clearInterval(resendTimerInterval);
                        $resendOtpBtn.prop('disabled', false);
                        $resendOtpBtn.html('Resend OTP');
                    }
                }, 1000);
            }

            function clearTimers() {
                clearInterval(otpTimerInterval);
                clearInterval(resendTimerInterval);
            }

            function checkPasswordStrength(password) {
                const $strengthBar = $('.strength-fill');
                const $strengthText = $('#strengthText');

                let strength = 0;
                let color = '#e74c3c';
                let text = 'Weak';

                if (password.length >= 8) strength += 25;
                if (/[a-z]/.test(password)) strength += 25;
                if (/[A-Z]/.test(password)) strength += 25;
                if (/[0-9]/.test(password)) strength += 25;
                if (/[^A-Za-z0-9]/.test(password)) strength += 25;

                strength = Math.min(strength, 100);

                if (strength >= 75) {
                    color = '#4CAF50';
                    text = 'Strong';
                } else if (strength >= 50) {
                    color = '#ff9800';
                    text = 'Medium';
                }

                $strengthBar.css({
                    'width': strength + '%',
                    'background-color': color
                });
                $strengthText.text(text).css('color', color);
            }

            function showMessage($element, text, type) {
                $element.text(text)
                    .attr('class', `message ${type}`)
                    .show();

                // Auto-hide success messages
                if (type === 'success') {
                    setTimeout(function() {
                        $element.hide();
                    }, 5000);
                }
            }

            function isValidEmail(email) {
                const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                return re.test(email);
            }
        });
    </script>
</body>

</html>