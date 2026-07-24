<?php
session_start();
include 'config/database_connection.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

if (isset($_GET['restaurant_id'])) {
    $_SESSION['restaurant_id'] = (int)$_GET['restaurant_id'];
    header("Location: restaurant_menu.php");
    exit();
}

$restaurant_id = $_SESSION['restaurant_id'] ?? null;
$user_id = $_SESSION['user_id'] ?? null;
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Restaurant Dashboard</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="assets/css/restaurants.css" rel="stylesheet">


    <!-- <style>
        /* .title{
      text-align:center;
      margin-bottom:30px;
      font-size:32px;
      color:#333;
    } */

        .category-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(70px, 1fr));
            gap: 20px;
            padding: 15px;
        }

        .category-card {
            background: #fff;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
            transition: 0.3s;
            cursor: pointer;
        }

        .category-card:hover {
            transform: translateY(-5px);
        }

        .category-card img {
            width: 100%;
            height: 55px;
            object-fit: cover;
        }

        .category-name {
            text-align: center;
            padding: 6px;
            font-size: 13px;
            font-weight: bold;
            color: #444;
        }
    </style> -->
</head>

<body>
    <!-- Hidden Inputs -->
    <input type="hidden" id="user_lat" name="user_lat" value="">
    <input type="hidden" id="user_long" name="user_long" value="">


    <div class="nav">
        <div class="logo-container">
            <img src="images/logo.png" id="logo_img" alt="Restaurant Logo">
        </div>

        <div class="search-nav">
            <div class="search-bar">
                <input type="text" id="live-search" placeholder="Search for Restaurant...">
                <button><i class="fas fa-search"></i></button>
            </div>
        </div>

        <div class="nav-action-item">
            <i class="fa-solid fa-location-dot"></i>
        </div>
    </div>

    <!-- Filters -->
    <div class="filters">
        <div class="filter-btn active">
            <i class="fas fa-fire"></i>
            <span>Popular</span>
        </div>
        <div class="filter-btn">
            <i class="fas fa-star"></i>
            <span>Top Rated</span>
        </div>
        <div class="filter-btn">
            <i class="fas fa-bolt"></i>
            <span>Fast Delivery</span>
        </div>
        <div class="filter-btn">
            <i class="fas fa-heart"></i>
            <span>Favourite</span>
        </div>
        <div class="filter-btn">
            <i class="fas fa-rupee-sign"></i>
            <span>Budget</span>
        </div>
    </div>


    <section class="restaurant-section">
        <div class="section-header">
            <h3>Available Restaurants Near You</h3>
        </div>
        <div id="restaurant-container" class="all-restaurants-grid"></div>
    </section>

    <?php include 'includes/footer_nav.php' ?>
</body>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<script>
    function distance(lat1, lon1, lat2, lon2) {
        const earthRadius = 6371; // KM
        const dLat = (lat2 - lat1) * Math.PI / 180;
        const dLon = (lon2 - lon1) * Math.PI / 180;

        const a = Math.sin(dLat / 2) * Math.sin(dLat / 2) +
            Math.cos(lat1 * Math.PI / 180) * Math.cos(lat2 * Math.PI / 180) *
            Math.sin(dLon / 2) * Math.sin(dLon / 2);
        const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
        return earthRadius * c;
    }

    // Load restaurants after location fetch
    function loadRestaurants() {
        $.ajax({
            url: "restaurant_list.php",
            method: "GET",
            success: function(data) {
                $("#restaurant-container").html(data);
            },
            error: function() {
                $("#restaurant-container").html("<p class='no-restaurants'>Error loading restaurants.</p>");
            }
        });
    }

    function requestUserLocation() {
        if (navigator.geolocation) {
            navigator.geolocation.getCurrentPosition(
                function(position) {
                    let lat = position.coords.latitude;
                    let long = position.coords.longitude;

                    // hidden input set
                    document.getElementById("user_lat").value = lat;
                    document.getElementById("user_long").value = long;

                    // AJAX call to save location in session
                    let formData = new FormData();
                    formData.append('lat', lat);
                    formData.append('long', long);

                    fetch('location_store.php', {
                            method: 'POST',
                            body: formData
                        })
                        .then(response => response.text())
                        .then(data => {
                            console.log(data);
                            // Load restaurants only after location is saved
                            loadRestaurants();
                        });

                },
                function(error) {
                    if (error.code === error.PERMISSION_DENIED) {
                        console.log("User denied location permission.");
                        $(".restaurant-container").html('<p class="no-restaurants">Please allow location to see nearby restaurants.</p>');
                    }
                }
            );
        } else {
            console.log("Browser does not support location.");
            $(".restaurant-container").html('<p class="no-restaurants">Browser does not support location services.</p>');
        }
    }

    // Call location request on page load
    window.onload = requestUserLocation;

    // Optional: Filter buttons
    document.addEventListener('DOMContentLoaded', function() {
        const filterBtns = document.querySelectorAll('.filter-btn');
        filterBtns.forEach(btn => {
            btn.addEventListener('click', function() {
                filterBtns.forEach(b => b.classList.remove('active'));
                this.classList.add('active');
                const filterType = this.querySelector('span').textContent;
                console.log('Filter selected:', filterType);
            });
        });
    });
</script>

</html>