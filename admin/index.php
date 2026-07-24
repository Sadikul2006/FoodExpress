<?php
session_start();
include '../config/database_connection.php';

if (isset($_SESSION['admin_id'])) {
    header("Location: dashboard.php");
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

        #heroSignupBtn {
            color: var(--primary-dark);
            background-color: transparent;
            border: none;
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
            padding: 30px;
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

        /* Responsive */
        @media (min-width: 600px) {
            .hero h1 {
                font-size: 2rem;
            }
        }


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

        ::-webkit-scrollbar {
            display: none;
        }

        html {
            scrollbar-width: none;
        }
    </style>

</head>

<body>
    <header>
        <nav class="navbar">
            <a href="#" class="logo-container logo animate__animated animate__fadeIn">
                <img src="../images/logo.png" id="logo_img" alt="Restaurant Logo">
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
            <h1 class="animate__animated animate__fadeInDown">Manage Your Restaurant Easily</h1>
            <p class="animate__animated animate__fadeIn animate__delay-1s">
                Add your menu, update food items, track orders and grow your business with FoodExpress.
            </p>
            <button class="btn" id="heroLoginBtn" type="button">Go to Dashboard</button>
            <p class="animate__animated animate__fadeIn animate__delay-1s admin-login">
                New to FoodExpress? <button id="heroSignupBtn" type="button">Create Account</button>
            </p>
            <a href="../index.php"><i class="fa-solid fa-arrow-left-long"></i> Back</a>
        </div>
    </section>

    <!-- Login Modal -->
    <div class="modal" id="loginModal">
        <div class="modal-content">
            <span class="close-btn" id="closeLogin">&times;</span>
            <h2 class="form-title">Login</h2>

            <!-- ✅ Added action, method, and hidden input -->
            <form id="loginForm" action="action_login.php" method="POST">
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
                    <a href="#">Forgot your password?</a>
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
            <h2 class="form-title">Create Account</h2>

            <!-- ✅ Added action, method, name attributes -->
            <form id="signupForm" action="action_login.php" method="POST">
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


    <!-- Footer -->
    <footer>
        <div class="footer-column">
            <h3>Contact Us</h3>
            <div class="footer-links">
                <a href="#" class="footer-link">Newtwon, Kolkata</a>
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

    <script>
        const loginBtn = document.getElementById('loginBtn');
        const signupBtn = document.getElementById('signupBtn');
        const loginModal = document.getElementById('loginModal');
        const signupModal = document.getElementById('signupModal');
        const closeLogin = document.getElementById('closeLogin');
        const closeSignup = document.getElementById('closeSignup');
        const switchToSignup = document.getElementById('switchToSignup');
        const switchToLogin = document.getElementById('switchToLogin');
        const heroSignupBtn = document.getElementById('heroSignupBtn');
        const heroLoginBtn = document.getElementById('heroLoginBtn');

        heroSignupBtn.onclick = () => signupModal.style.display = 'flex';
        heroLoginBtn.onclick = () => loginModal.style.display = 'flex';


        // Open & close modals
        loginBtn.onclick = () => loginModal.style.display = 'flex';
        signupBtn.onclick = () => signupModal.style.display = 'flex';
        closeLogin.onclick = () => loginModal.style.display = 'none';
        closeSignup.onclick = () => signupModal.style.display = 'none';
        heroSignupBtn.onclick = () => signupModal.style.display = 'flex';

        // Switch between login and signup
        switchToSignup.onclick = (e) => {
            e.preventDefault();
            loginModal.style.display = 'none';
            signupModal.style.display = 'flex';
        };
        switchToLogin.onclick = (e) => {
            e.preventDefault();
            signupModal.style.display = 'none';
            loginModal.style.display = 'flex';
        };

        // Close modal when clicking outside
        window.addEventListener('click', (e) => {
            if (e.target === loginModal) loginModal.style.display = 'none';
            if (e.target === signupModal) signupModal.style.display = 'none';
        });
    </script>
</body>

</html>