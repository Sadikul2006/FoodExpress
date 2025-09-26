<?php
session_start();
include 'database_connection.php';

if (isset($_SESSION['user_id'])) {
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

        /* Header Styles */
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

        .logo {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-weight: 700;
            font-size: 1.3rem;
            color: var(--primary);
            text-decoration: none;
        }

        .logo i {
            font-size: 1.5rem;
        }

        .auth-buttons {
            height: 50px;
            width: auto;
            display: flex;
            gap: 0.8rem;
        }

        .btn {
            padding: 0.5rem 1rem;
            border-radius: 50px;
            font-weight: 600;
            font-size: 0.85rem;
            text-decoration: none;
            transition: all 0.3s ease;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 0.3rem;
            white-space: nowrap;
        }

        .btn-primary {
            background-color: var(--primary);
            color: white;
        }

        .btn-primary:hover {
            background-color: var(--primary-dark);
        }

        .btn-outline {
            border: 2px solid var(--primary);
            color: var(--primary);
            background: transparent;
        }

        .btn-outline:hover {
            background-color: rgba(255, 107, 107, 0.1);
        }

        /* Hero Section */
        .hero {
            background: linear-gradient(rgba(0, 0, 0, 0.7), rgba(0, 0, 0, 0.7)), url('https://images.unsplash.com/photo-1517248135467-4c7edcad34c4?ixlib=rb-1.2.1&auto=format&fit=crop&w=1350&q=80');
            background-size: cover;
            background-position: center;
            color: white;
            padding: 4rem 1.5rem;
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

        .order-btn {
            background-color: var(--secondary);
            color: white;
            padding: 0.8rem 1.8rem;
            font-size: 1rem;
            border-radius: 50px;
            text-decoration: none;
            font-weight: 600;
            display: inline-block;
            transition: all 0.3s ease;
        }

        .order-btn:hover {
            background-color: var(--secondary-dark);
        }

        /* Restaurant Selection Section
        .restaurant-section {
            padding: 3rem 1rem;
            max-width: 1300px;
            margin: 0 auto;
        }

        .section-title {
            text-align: center;
            margin-bottom: 2rem;
        }

        .section-title h2 {
            font-size: 1.6rem;
            color: var(--darker);
        }

        .section-title p {
            font-size: 0.95rem;
            color: var(--gray);
        }

        .restaurant-grid {
            display: grid;
            grid-template-columns: 1fr;
            gap: 1.5rem;
        }

        .restaurant-card {
            background: white;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 3px 10px rgba(0, 0, 0, 0.08);
            transition: all 0.3s ease;
            text-decoration: none;
            color: var(--dark);
        }

        .restaurant-image {
            height: 160px;
            overflow: hidden;
            position: relative;
        }

        .restaurant-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .restaurant-rating {
            position: absolute;
            top: 8px;
            right: 8px;
            background-color: rgba(0, 0, 0, 0.7);
            color: white;
            padding: 0.2rem 0.5rem;
            border-radius: 15px;
            font-size: 0.7rem;
            display: flex;
            align-items: center;
            gap: 0.2rem;
        }

        .restaurant-rating i {
            color: var(--secondary);
            font-size: 0.7rem;
        }

        .restaurant-info {
            padding: 1rem;
        }

        .restaurant-info h3 {
            font-size: 1.1rem;
            margin-bottom: 0.3rem;
        }

        .restaurant-cuisine {
            color: var(--gray);
            font-size: 0.8rem;
            margin-bottom: 0.8rem;
            display: flex;
            align-items: center;
            gap: 0.3rem;
        }

        .restaurant-cuisine i {
            color: var(--primary);
            font-size: 0.8rem;
        }

        .restaurant-footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .restaurant-delivery {
            font-size: 0.8rem;
            color: var(--gray);
            display: flex;
            align-items: center;
            gap: 0.2rem;
        }

        .restaurant-delivery i {
            color: var(--success);
            font-size: 0.8rem;
        }

        .view-menu-btn {
            background-color: var(--primary);
            color: white;
            border: none;
            border-radius: 5px;
            padding: 0.4rem 0.8rem;
            font-size: 0.8rem;
            cursor: pointer;
        } */

        /* Login Options */
        .login-options {
            background-color: white;
            padding: 3rem 1rem;
        }

        .login-container {
            max-width: 800px;
            margin: 0 auto;
        }

        .login-grid {
            display: flex;
            flex-direction: column;
            gap: 1.5rem;
        }

        .login-option {
            background-color: var(--light);
            border-radius: 10px;
            padding: 1.5rem;
            box-shadow: 0 3px 10px rgba(0, 0, 0, 0.05);
            text-align: center;
        }

        .login-option i {
            font-size: 2rem;
            color: var(--primary);
            margin-bottom: 0.8rem;
        }

        .login-option h3 {
            font-size: 1.2rem;
            margin-bottom: 0.5rem;
        }

        .login-option p {
            font-size: 0.9rem;
            color: var(--gray);
            margin-bottom: 1.2rem;
        }

        .login-buttons {
            display: flex;
            gap: 0.8rem;
            justify-content: center;
        }

        /* Footer */
        footer {
            background-color: var(--darker);
            color: white;
            padding: 3rem 1rem 1.5rem;
        }

        .footer-content {
            max-width: 1300px;
            margin: 0 auto;
        }

        .footer-logo {
            font-size: 1.5rem;
            font-weight: 700;
            color: white;
            margin-bottom: 1.5rem;
            display: block;
            text-align: center;
        }

        .footer-grid {
            display: grid;
            grid-template-columns: 1fr;
            gap: 2rem;
            margin-bottom: 2rem;
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

        /* Tablet View */
        @media (min-width: 600px) {
            .restaurant-grid {
                grid-template-columns: repeat(2, 1fr);
            }

            .footer-grid {
                grid-template-columns: repeat(2, 1fr);
            }

            .hero h1 {
                font-size: 2rem;
            }
        }

        /* Desktop View */
        @media (min-width: 900px) {
            header {
                padding: 0 2rem;
            }

            .hero {
                padding: 5rem 2rem;
            }

            .hero h1 {
                font-size: 2.5rem;
            }

            .restaurant-section {
                padding: 4rem 2rem;
            }

            .restaurant-grid {
                grid-template-columns: repeat(4, 1fr);
            }

            .login-grid {
                flex-direction: row;
                gap: 2rem;
            }

            .footer-grid {
                grid-template-columns: repeat(3, 1fr);
                text-align: left;
            }

            .footer-column {
                text-align: left;
            }

            .social-links {
                justify-content: flex-start;
            }
        }

        /* Animation Classes */
        .animate__animated {
            opacity: 0;
        }

        .animate__fadeIn {
            animation: fadeIn 1s forwards;
        }

        .animate__fadeInDown {
            animation: fadeInDown 1s forwards;
        }

        .animate__fadeInUp {
            animation: fadeInUp 1s forwards;
        }

        .animate__fadeInLeft {
            animation: fadeInLeft 1s forwards;
        }

        .animate__fadeInRight {
            animation: fadeInRight 1s forwards;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
            }

            to {
                opacity: 1;
            }
        }

        @keyframes fadeInDown {
            from {
                opacity: 0;
                transform: translateY(-20px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes fadeInLeft {
            from {
                opacity: 0;
                transform: translateX(-20px);
            }

            to {
                opacity: 1;
                transform: translateX(0);
            }
        }

        @keyframes fadeInRight {
            from {
                opacity: 0;
                transform: translateX(20px);
            }

            to {
                opacity: 1;
                transform: translateX(0);
            }
        }

        .logo-container {
            display: flex;
            align-items: center;
        }

        #logo_img {
            height: 50px;
            width: auto;
            transition: var(--transition);
        }

        #logo_img:hover {
            transform: scale(1.05) rotate(-5deg);
        }

        .loginReg {
            height: 50px;
            width: 70px;
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            flex-direction: column;
        }

        #loginReg {
            height: 45px;
            width: 80px;
        }

        small {
            font-size: 10px;
        }

        a {
            text-decoration: none;
        }
    </style>
</head>

<body>

    <!-- Header with Login/Register -->
    <header>
        <nav class="navbar">
            <a href="#" class="logo-container logo animate__animated animate__fadeIn">
                <img src="images/logo.png" id="logo_img" alt="Restaurant Logo">
            </a>
            <div class="auth-buttons">
                <a href="#user-login" id="loginReg" class="btn btn-primary animate__animated animate__fadeIn animate__delay-1s">
                    <div class="loginReg">
                        <i class="fas fa-sign-in-alt"></i><small>LOGIN</small>
                    </div>
                </a>
                <a href="#user-login" id="loginReg" class="btn btn-outline animate__animated animate__fadeIn animate__delay-1-2s">
                    <div class="loginReg">
                        <i class="fas fa-user-plus"></i><small>REGISTER</small>
                    </div>
                </a>
            </div>
        </nav>
    </header>

    <!-- Hero Section -->
    <section class="hero">
        <div class="hero-content">
            <h1 class="animate__animated animate__fadeInDown">Order from Your Favorite Restaurants</h1>
            <p class="animate__animated animate__fadeIn animate__delay-1s">Discover the best dining options in your area and get food delivered to your doorstep</p>
        </div>
    </section>

    <!-- Restaurant Selection Section -->
   

    <!-- Login Options Section -->
    <section class="login-options" id="user-login">
        <div class="login-container">
            <!-- <div class="section-title">
                <h2 class="animate__animated animate__fadeIn">Login Options</h2>
                <p class="animate__animated animate__fadeIn animate__delay-1s">Choose how you want to access our platform</p>
            </div> -->

            <div class="login-grid">
                <div class="login-option animate__animated animate__fadeInLeft">
                    <i class="fas fa-user"></i>
                    <h3>Customer Login</h3>
                    <p>Order food from your favorite restaurants</p>
                    <div class="login-buttons">
                        <a href="user_login_register.php?tab=login" class="btn btn-primary">Login</a>
                        <a href="user_login_register.php?tab=register" class="btn btn-outline">Register</a>
                    </div>
                </div>

                <div class="login-option animate__animated animate__fadeInRight">
                    <i class="fas fa-store"></i>
                    <h3>Restaurant Owner</h3>
                    <p>Manage your restaurant and orders</p>
                    <div class="login-buttons">
                        <a href="admin/admin_login_register.php" class="btn btn-primary">Login</a>
                        <a href="admin/admin_login_register.php" class="btn btn-outline">Register</a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer>
        <div class="footer-content">
            <a href="#" class="footer-logo">FoodExpress</a>

            <div class="footer-grid">
                <div class="footer-column">
                    <h3>Quick Links</h3>
                    <div class="footer-links">
                        <a href="#" class="footer-link">Home</a>
                        <a href="#restaurants" class="footer-link">Restaurants</a>
                        <a href="#" class="footer-link">About Us</a>
                        <a href="#" class="footer-link">Special Offers</a>
                    </div>
                </div>

                <div class="footer-column">
                    <h3>Information</h3>
                    <div class="footer-links">
                        <a href="#" class="footer-link">Contact Us</a>
                        <a href="#" class="footer-link">Privacy Policy</a>
                        <a href="#" class="footer-link">Terms of Service</a>
                        <a href="#" class="footer-link">FAQs</a>
                    </div>
                </div>

                <div class="footer-column">
                    <h3>Contact Us</h3>
                    <div class="footer-links">
                        <a href="#" class="footer-link">123 Food Street, City</a>
                        <a href="tel:+1234567890" class="footer-link">+1 (234) 567-890</a>
                        <a href="mailto:info@foodexpress.com" class="footer-link">info@foodexpress.com</a>
                    </div>

                    <div class="social-links">
                        <a href="#" class="social-link"><i class="fab fa-facebook-f"></i></a>
                        <a href="#" class="social-link"><i class="fab fa-instagram"></i></a>
                        <a href="#" class="social-link"><i class="fab fa-twitter"></i></a>
                        <a href="#" class="social-link"><i class="fab fa-youtube"></i></a>
                    </div>
                </div>
            </div>

            <p class="copyright">© 2023 FoodExpress. All rights reserved.</p>
        </div>
    </footer>

    <script>
        // Smooth scrolling for anchor links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function(e) {
                e.preventDefault();

                const targetId = this.getAttribute('href');
                if (targetId === '#') return;

                const targetElement = document.querySelector(targetId);
                if (targetElement) {
                    window.scrollTo({
                        top: targetElement.offsetTop - 80,
                        behavior: 'smooth'
                    });
                }
            });
        });

        // Animation on scroll
        const animateOnScroll = () => {
            const elements = document.querySelectorAll('.animate__animated');

            elements.forEach(element => {
                const elementPosition = element.getBoundingClientRect().top;
                const windowHeight = window.innerHeight;

                if (elementPosition < windowHeight - 100) {
                    const animationClass = element.classList.item(1);
                    if (animationClass) {
                        element.classList.add(animationClass);
                    }
                }
            });
        };

        // Run on initial load
        animateOnScroll();

        // Run on scroll
        window.addEventListener('scroll', animateOnScroll);

        // Add click animation to buttons
        const buttons = document.querySelectorAll('.btn, .order-btn, .restaurant-card');
        buttons.forEach(button => {
            button.addEventListener('click', function() {
                this.style.transform = 'scale(0.95)';
                setTimeout(() => {
                    this.style.transform = '';
                }, 200);
            });
        });
    </script>
</body>

</html>