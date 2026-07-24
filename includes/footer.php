<?php
include 'config/database_connection.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$restaurant_id = $_SESSION['restaurant_id'] ?? null;

if ($restaurant_id) {
    $stmt = $conn->prepare("SELECT restaurant_place, phone, email FROM restaurant_info WHERE restaurant_id = ?");
    $stmt->bind_param("i", $restaurant_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
        $restaurant_location = $row['restaurant_place'];
        $restaurant_phone = $row['phone'];
        $restaurant_email = $row['email'];
    }

    $stmt->close();
}

$user_rating = 0;
if ($restaurant_id && $user_id) {
    $stmt = $conn->prepare("SELECT rating FROM ratings WHERE restaurant_id = ? AND user_id = ?");
    $stmt->bind_param("ii", $restaurant_id, $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($row = $result->fetch_assoc()) {
        $user_rating = $row['rating'];
    }
    $stmt->close();
}
?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Simple Footer</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .simple-footer {
            background-color: #c6d3e5;
            padding-top: 10px;
            color: var(--text-color);
            font-family: 'Poppins', sans-serif;
            display: flex;
            flex-direction: column;
            justify-content: space-around;
            align-items: center;
        }

        .contact-info {
            list-style: none;
            padding: 10px;
            text-align: center;
        }

        .footer_icon {
            padding-right: 5px;
        }

        .footer_li {
            padding-top: 5px;
        }

        .footer-bottom {
            text-align: center;
            margin-top: 1rem;
            font-size: 0.75rem;
            color: var(--text-color);
            padding: 8px;
        }

        .order-rating {
            text-align: center;
        }

        .order-rating i {
            color: #ffa200de;
            font-size: 16px;
            margin: 0 5px;
            transition: 0.3s;
        }

        .order-rating i:hover {
            transform: scale(1.2);
        }
    </style>
</head>

<body>
    <footer class="simple-footer">
        <h4>Contact</h4>
        <ul class="contact-info">
            <li class="footer_li"><i class="fas fa-map-marker-alt footer_icon"></i><?php echo $restaurant_location ?></li>
            <li class="footer_li"><i class="fas fa-phone footer_icon"></i><?php echo $restaurant_phone ?></li>
            <li class="footer_li"><i class="fas fa-envelope footer_icon"></i><?php echo $restaurant_email ?></li>
        </ul>
        <div class="order-rating" data-restaurant-id="<?php echo $restaurant_id; ?>">
            <?php
            for ($i = 1; $i <= 5; $i++) {
                if ($i <= $user_rating) {
                    echo '<i class="fas fa-star"></i>';
                } else {
                    echo '<i class="far fa-star"></i>';
                }
            }
            ?>
        </div>
        <div class="footer-bottom">
            <p>&copy; 2025 Feed More. All rights reserved.</p>
            <p>&copy; Developed with ❤️ by Sadikul</p>
        </div>
    </footer>
</body>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    $(document).ready(function() {
        $('.order-rating i').on('click', function() {
            const index = $(this).index();
            const parent = $(this).closest('.order-rating');
            const restaurantId = parent.data('restaurant-id');
            const ratingValue = index + 1;

            parent.find('i').each(function(i) {
                if (i <= index) {
                    $(this).removeClass('far').addClass('fas');
                } else {
                    $(this).removeClass('fas').addClass('far');
                }
            });

            $.ajax({
                url: 'ajax/fetch_resturant_ratings.php',
                type: 'POST',
                data: {
                    restaurant_id: restaurantId,
                    rating: ratingValue
                },
                success: function(response) {
                    console.log(response);
                },
                error: function(xhr, status, error) {
                    console.error(error);
                }
            });
        });
    });
</script>

</html>