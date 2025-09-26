<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
    include 'database_connection.php';
}

$user_name = htmlspecialchars($_SESSION['user_name'] ?? 'Guest');
// $profile_image = 'images/user_profile.jpg';
if (isset($_SESSION['user_img'])) {
    $profile_image = htmlspecialchars($_SESSION['user_img']);
}

$user_id = $_SESSION['user_id'];
$restaurant_id = $_SESSION['restaurant_id'] ?? null;

$stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $profile_image = $row['image'];
}
$stmt->close();


$stmt = $conn->prepare("SELECT COUNT(*) AS total FROM cart WHERE user_id = ? AND restaurant_id = ?");
$stmt->bind_param("ii", $user_id, $restaurant_id);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $total_cart_items = $row['total'];
}
$stmt->close();

$profile_image = !empty($profile_image) ? $profile_image : 'images/user_profile.jpg';

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Restaurant Dashboard</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary-color: #ff6b6b;
            --secondary-color: #3f37c9;
            --accent-color: #f72585;
            --light-color: #f8f9fa;
            --dark-color: #212529;
            --text-color: #495057;
            --shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            --transition: all 0.3s cubic-bezier(0.25, 0.8, 0.25, 1);
            --gradient: linear-gradient(45deg, #f72585, #7209b7, #3a0ca3, #ff6b6b);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Poppins', sans-serif;
            padding-top: 70px;
            background-color: #f5f7fa;
            color: var(--text-color);
        }

        .nav {
            height: 60px;
            width: 100%;
            background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%);
            display: flex;
            justify-content: space-between;
            align-items: center;
            position: fixed;
            top: 0;
            left: 0;
            z-index: 1000;
            box-shadow: var(--shadow);
            padding: 0 20px;
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            border-bottom: 1px solid rgba(255, 255, 255, 0.7);
        }

        .user {
            display: flex;
            align-items: center;

        }

        .profile-container {
            position: relative;
            width: 56px;
            height: 56px;
            display: flex;
            justify-content: center;
            align-items: center;
            cursor: pointer;
        }

        .profile-ring {
            position: absolute;
            width: 100%;
            height: 100%;
            border-radius: 50%;
            background: var(--gradient);
            opacity: 0;
            transition: var(--transition);
        }

        .profile-container:hover .profile-ring {
            opacity: 1;
            animation: rotateGradient 3s linear infinite;
        }

        @keyframes rotateGradient {
            0% {
                transform: rotate(0deg);
            }

            100% {
                transform: rotate(360deg);
            }
        }

        #profile_img {
            height: 50px;
            width: 50px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid white;
            box-shadow: var(--shadow);
            position: relative;
            z-index: 2;
            transition: var(--transition);
        }

        #setting {
            position: absolute;
            bottom: -2px;
            right: -2px;
            background: white;
            width: 20px;
            height: 20px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.7rem;
            color: var(--primary-color);
            border: 2px solid white;
            z-index: 3;
            transition: var(--transition);
        }

        .profile-container:hover #setting {
            transform: rotate(90deg);
            color: var(--accent-color);
        }

        .user-name {
            font-size: 1.1rem;
            font-weight: 600;
            color: var(--primary-color);
            margin-left: 10px;
            transition: var(--transition);
        }

        .user:hover .user-name {
            color: var(--accent-color);
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

        .nav-items {
            display: flex;
            align-items: center;
            gap: 30px;
        }

        a {
            text-decoration: none;
        }

        .nav-link {
            position: relative;
            font-size: 1.2rem;
            font-weight: 500;
            color: var(--text-color);
            padding: 10px 0 5px 0;
            transition: var(--transition);
        }

        .nav-link:hover {
            color: var(--primary-color);
        }

        .nav-link::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 0;
            height: 3px;
            background-color: var(--primary-color);
            transition: var(--transition);
        }

        .nav-link:hover::after {
            width: 100%;
        }

        .cart-icon {
            position: relative;
        }

        .fa-solid {
            font-size: 2rem;
        }

        .cart-badge {
            position: absolute;
            top: -1px;
            right: -8px;
            background-color: var(--primary-color);
            color: white;
            border-radius: 50%;
            width: 20px;
            height: 20px;
            font-size: 0.7rem;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
        }

        .remove {
            display: none;
        }

        @media (max-width: 768px) {
            body {
                margin-top: 65px;
            }

            .nav {
                padding: 0 10px;
                height: 65px;
            }

            .user-name {
                display: none;
            }

            .nav-items {
                gap: 15px;
            }
        }

        @media (max-width: 480px) {
            .nav {
                height: 60px;
                padding: 0 10px;
            }

            #logo_img {
                max-height: 40px;
            }
        }
    </style>
</head>

<body>
    <div class="nav">
        <div class="user">
            <a href="user_info.php">
                <div class="profile-container">
                    <div class="profile-ring"></div>
                    <img src="<?php echo htmlspecialchars($profile_image); ?>" id="profile_img" alt="User Profile">
                    <i id="setting" class="fa-solid fa-gear"></i>
                </div>
            </a>
            <span class="user-name"><?php echo $user_name ?></span>
        </div>

        <div class="logo-container">
            <img src="images/logo.png" id="logo_img" alt="Restaurant Logo">
        </div>

        <div class="nav-items">
            <a href="dashboard.php" class="nav-link">Menu</a>
            <a href="order.php" class="nav-link">Orders</a>
            <a href="cart.php" class="nav-link cart-icon">
                <i class="fa-solid fa-cart-shopping"></i>
                <span class="cart-badge <?php echo $total_cart_items < 1 ? "remove" : "" ?>" id="cart-count"><?php echo $total_cart_items; ?></span>
            </a>
        </div>
    </div>
</body>

</html>