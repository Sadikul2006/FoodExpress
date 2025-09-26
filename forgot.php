<?php session_start() ?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        body {
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            background-image: url("https://images.unsplash.com/photo-1707343844152-6d33a0bb32c3");
            background-size: cover;
            background-position: center;
            background-attachment: fixed;
        }

        .container {
            width: 400px;
            padding: 40px;
            border-radius: 15px;
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border: 2px solid rgba(255, 255, 255, 0.2);
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.2);
            text-align: center;
            color: white;
        }

        .icon {
            font-size: 60px;
            margin-bottom: 20px;
            color: rgba(255, 255, 255, 0.9);
        }

        h1 {
            font-size: 24px;
            margin-bottom: 15px;
        }

        p {
            margin-bottom: 25px;
            color: rgba(255, 255, 255, 0.8);
        }

        a {
            color: rgba(255, 255, 255, 0.8);
            text-decoration: none;
            transition: color 0.3s;
        }

        a:hover {
            color: white;
            text-decoration: underline;
        }

        .input {
            position: relative;
            margin-bottom: 25px;
        }

        .input i {
            position: absolute;
            left: 15px;
            top: 53%;
            transform: translateY(-50%);
            color: rgba(255, 255, 255, 0.7);
        }

        .input input {
            width: 100%;
            padding: 15px 15px 15px 35px;
            background: rgba(255, 255, 255, 0.1);
            border: 2px solid rgba(255, 255, 255, 0.3);
            border-radius: 30px;
            color: white;
            font-size: 16px;
            outline: none;
            transition: all 0.3s;
        }

        .input input:focus {
            border-color: white;
        }

        .input input::placeholder {
            color: rgba(255, 255, 255, 0.6);
        }

        button {
            width: 100%;
            padding: 15px;
            background: white;
            color: #333;
            border: none;
            border-radius: 30px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            margin-bottom: 20px;
        }

        button:hover {
            background: rgba(255, 255, 255, 0.9);
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }

        .back-link {
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        /* Alert messages */
        .alert {
            position: fixed;
            top: 20px;
            left: 50%;
            transform: translateX(-50%);
            padding: 15px 25px;
            border-radius: 5rem;
            background-color: rgba(0, 0, 0, 0.7);
            color: white;
            z-index: 100;
            display: flex;
            align-items: center;
            box-shadow: 0 3px 10px rgba(0, 0, 0, 0.2);
            background-color: rgba(255, 0, 0, 0.7);
        }

        .alert i {
            margin-right: 10px;
        }

        /* Autofill styling */
        input:-webkit-autofill,
        input:-webkit-autofill:hover,
        input:-webkit-autofill:focus,
        input:-webkit-autofill:active {
            -webkit-text-fill-color: white !important;
            -webkit-box-shadow: 0 0 0 1000px transparent inset !important;
            transition: background-color 5000s ease-in-out 0s !important;
            caret-color: white !important;
        }

        input:autofill,
        input:autofill:hover,
        input:autofill:focus,
        input:autofill:active {
            -webkit-text-fill-color: white !important;
            box-shadow: 0 0 0 1000px transparent inset !important;
            caret-color: white !important;
        }
    </style>
</head>

<body>
    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert error">
            <i class="fas fa-exclamation-circle"></i>
            <?php echo $_SESSION['error'];
            unset($_SESSION['error']); ?>
        </div>
    <?php endif ?>

    <form action="forgot_action.php" method="POST">
        <div class="container" id="login-page">
            <div class="icon">
                <i class="fas fa-key"></i>
            </div>
            <h1>Forgot Password</h1>
            <p>Enter your email address to receive a password reset link</p>

            <div class="input">
                <i class="fa-solid fa-envelope"></i>
                <input type="email" placeholder="Enter your email address" name="email" required>
            </div>

            <button type="submit">Send Reset Link</button>

            <p>Don't have an account? <a href="register.php">Register</a></p>

            <div>
                <i class="fa-solid fa-arrow-left"></i>
                <a href="login.php" class="back-link">Back to login</a>
            </div>
        </div>
    </form>
    <script>
        const alerts = document.querySelectorAll('.alert');
        alerts.forEach(alert => {
            setTimeout(() => {
                alert.style.display = 'none';
            }, 4000);
        });
    </script>
</body>

</html>