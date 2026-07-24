<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
    include 'config/database_connection.php';
}

$user_id = $_SESSION['user_id'] ?? null;
$restaurant_id = $_SESSION['restaurant_id'] ?? null;

if ($restaurant_id) {
    $stmt = $conn->prepare("SELECT COUNT(*) AS total FROM cart WHERE user_id = ? AND restaurant_id = ?");
    $stmt->bind_param("ii", $user_id, $restaurant_id);
} else {
    $stmt = $conn->prepare("SELECT COUNT(*) AS total FROM cart WHERE user_id = ?");
    $stmt->bind_param("i", $user_id);
}

$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();
$total_cart_items = $row['total'] ?? 0;
$stmt->close();

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<style>
    :root {
        --primary: #ff6b6b;
        --primary-light: #faa6a6;
        --primary-lightest: #ffdbdb;
        --dark: #1e293b;
        --gray: #64748b;
        --light-gray: #e2e8f0;
        --lighter-gray: #f1f5f9;
        --light-gray: #e2e8f0;
        --lighter-gray: #f1f5f9;
        --success: #10b981;
        --success-light: #d1fae5;
        --warning: #f59e0b;
        --warning-light: #fef3c7;
        --danger: #ef4444;
        --danger-light: #fee2e2;
        --text: #334155;
    }

    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    }

    body {
        padding-bottom: 60px;
        background-color: #f5f5f5;
        color: var(--text);
        padding-top: 60px;
    }

    a {
        text-decoration: none;
        color: var(--text);
    }

    /* 🧭 Navbar */
    .nav {
        height: 60px;
        width: 100%;
        background: linear-gradient(to right, #ff7e5f, #feb47b);
        color: white;
        display: flex;
        justify-content: space-around;
        align-items: center;
        position: fixed;
        top: 0;
        left: 0;
        z-index: 10;
        box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        padding: 0 1rem;
        gap: 10px;
    }

    p#restaurant_name {
        display: flex;
        justify-content: center;
        align-items: center;
        flex-direction: column;
    }

    .bottom-nav {
        position: fixed;
        bottom: 0;
        left: 0;
        width: 100%;
        background: white;
        display: flex;
        justify-content: space-around;
        padding: 12px 0;
        box-shadow: 0 -2px 10px rgba(0, 0, 0, 0.1);
        z-index: 900000;
    }

    .nav-item {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 4px;
        text-decoration: none;
        color: var(--gray);
        font-size: 0.7rem;
        flex: 1;
    }

    .nav-item.active {
        color: var(--primary);
    }

    .nav-item i {
        font-size: 1.2rem;
    }

    #live-search {
        color: black;
    }

    .cart-badge {
        position: absolute;
        background-color: var(--primary);
        color: white;
        border-radius: 50%;
        width: 16px;
        height: 16px;
        font-size: 0.6rem;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 600;
        top: -6px;
        right: -8px;
        padding: 2px 6px;
        line-height: 1;
    }

    .nav-item div {
        position: relative;
        display: inline-block;
    }

    .cart-badge.remove {
        display: none;
    }

    ::-webkit-scrollbar {
        display: none;
    }

    html {
        scrollbar-width: none;
    }
</style>

<style>
    ::-webkit-scrollbar {
        display: none;
    }

    html {
        scrollbar-width: none;
    }

    .back-btn {
        color: white;
        height: 40px;
        width: 40px;
        display: flex;
        justify-content: center;
        align-items: center;
    }

    .back-btn i {
        font-size: 20px !important;
    }
</style>


<body>
    <nav class="bottom-nav">
        <a href="restaurants.php" class="nav-item active">
            <i class="fas fa-home"></i>
            <span>Home</span>
        </a>
        <a href="restaurant_menu.php" class="nav-item">
            <i class="fa-solid fa-utensils"></i>
            <span>Menu</span>
        </a>
        <a href="account.php" class="nav-item">
            <i class="fas fa-user"></i>
            <span>Account</span>
        </a>
        <a href="order_history.php" class="nav-item">
            <i class="fa-solid fa-box-open"></i>
            <span>Orders</span>
        </a>
        <a href="cart.php" class="nav-item">
            <div>
                <i class="fas fa-shopping-cart"></i>
                <span class="cart-badge <?php echo $total_cart_items < 1 ? "remove" : "" ?>" id="cart-count"><?php echo $total_cart_items; ?></span>
            </div>
            <span>Cart</span>
        </a>
    </nav>
</body>
<?php
    if (isset($_SESSION['success'])) {
        echo "<script>showAlert('{$_SESSION['success']}', 'success');</script>";
        unset($_SESSION['success']);
    }

    if (isset($_SESSION['error'])) {
        echo "<script>showAlert('{$_SESSION['error']}', 'error');</script>";
        unset($_SESSION['error']);
    }
?>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    $(document).ready(function() {
        const currentPage = window.location.pathname.split("/").pop();
        $('.nav-item').each(function() {
            const linkPage = $(this).attr('href');
            if (linkPage === currentPage) {
                $(this).addClass('active');
            } else {
                $(this).removeClass('active');
            }
        });
    });
</script>

</html>