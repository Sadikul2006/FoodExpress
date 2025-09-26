<?php
session_start();

// Check if email is verified from forgot password process
if (!isset($_SESSION['reset_email'])) {
    header("Location: forgot.php");
    exit();
}
?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            color: white;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        body {
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            background-image: url("body.jpeg");
            background-size: cover;
            background-position: center;
        }

        .container {
            width: 400px;
            padding: 40px;
            border-radius: 1rem;
            border: 2px solid rgba(255, 255, 255, 0.445);
            background-color: rgba(255, 255, 255, 0.1);
            box-shadow: 0 0 2rem rgba(0, 0, 0, 0.5);
            backdrop-filter: blur(10px);
            text-align: center;
        }

        .icon {
            font-size: 50px;
            margin-bottom: 20px;
            color: rgba(255, 255, 255, 0.9);
        }

        h1 {
            margin-bottom: 25px;
            font-size: 1.8rem;
        }

        .input {
            position: relative;
            margin: 15px 0;
            display: flex;
            align-items: center;
        }

        .input i {
            position: absolute;
            left: 20px;
            color: rgba(255, 255, 255, 0.7);
        }

        .input input {
            width: 100%;
            height: 50px;
            padding: 0 20px 0 40px;
            background-color: transparent;
            border: 2px solid rgba(255, 255, 255, 0.445);
            border-radius: 5rem;
            font-size: 1rem;
            outline: none;
            margin: 5px 0 5px;
        }

        input::placeholder {
            color: white;
            opacity: 1;
        }

        button {
            width: 100%;
            height: 50px;
            margin: 20px 0 15px;
            font-size: 1.1rem;
            border-radius: 5rem;
            background-color: rgba(255, 255, 255, 0.867);
            color: black;
            border: none;
            cursor: pointer;
            font-weight: 600;
            transition: all 0.3s;
        }

        button:hover {
            background-color: white;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
        }

        p {
            color: rgba(255, 255, 255, 0.8);
        }

        a {
            color: rgba(255, 255, 255, 0.9);
            text-decoration: none;
            transition: color 0.3s;
        }

        a:hover {
            color: white;
            text-decoration: underline;
        }

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
        }

        .alert.error {
            background-color: rgba(255, 0, 0, 0.7);
        }

        .alert.success {
            background-color: rgba(0, 255, 0, 0.7);
        }

        .alert i {
            margin-right: 10px;
        }

        /* Password toggle styling */
        .password-toggle {
            position: absolute;
            right: 20px;
            cursor: pointer;
            color: rgba(255, 255, 255, 0.7);
        }

        /* Autofill styling */
        input:-webkit-autofill,
        input:-webkit-autofill:hover,
        input:-webkit-autofill:focus,
        input:-webkit-autofill:active {
            -webkit-text-fill-color: white !important;
            -webkit-box-shadow: 0 0 0 1000px transparent inset !important;
            transition: background-color 5000s ease-in-out 0s !important;
        }
    </style>
</head>

<body>
<?php if (isset($_SESSION['error'])): ?>
        <div class="alert error">
            <i class="fas fa-exclamation-circle"></i>
            <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
        </div>
    <?php endif; ?>
    <form action="reset_action.php" method="POST">
        <div class="container">
            <div class="icon">
                <i class="fas fa-key"></i>
            </div>
            <h1>Reset Password</h1>

            <div class="input">
                <i class="fa-solid fa-lock"></i>
                <input type="password" placeholder="New Password" name="new_password" id="new-password" required>
                <!-- <i class="fa-solid fa-eye-slash password-toggle" id="toggle-new-password"></i> -->
            </div>

            <div class="input">
                <i class="fa-solid fa-lock"></i>
                <input type="password" placeholder="Confirm Password" name="confirm_password" id="confirm-password" required>
                <!-- <i class="fa-solid fa-eye-slash password-toggle" id="toggle-confirm-password"></i> -->
            </div>

            <button type="submit">Reset Password</button>

            <p>Remember your password? <a href="login.php">Sign in</a></p>
        </div>
    </form>
    <script>
// Auto-hide error messages after 5 seconds
document.addEventListener('DOMContentLoaded', function() {
    const alert = document.querySelector('.alert');
    if (alert) {
        setTimeout(() => {
            alert.style.animation = 'slideOut 0.3s ease-out';
            setTimeout(() => alert.remove(), 300);
        }, 5000);
    }
});
</script>

<style>
@keyframes slideOut {
    from { top: 20px; opacity: 1; }
    to { top: -50px; opacity: 0; }
}
</style>
</body>

</html>