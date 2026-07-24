<?php
session_start();
include "config/database_connection.php";
$found = false;
// ---------------- DISTANCE FUNCTION ----------------
function distance($lat1, $lon1, $lat2, $lon2) {
    $earthRadius = 6371; // KM
    $dLat = deg2rad($lat2 - $lat1);
    $dLon = deg2rad($lon2 - $lon1);

    $a = sin($dLat/2) * sin($dLat/2) +
         cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
         sin($dLon/2) * sin($dLon/2);

    $c = 2 * atan2(sqrt($a), sqrt(1-$a));
    return $earthRadius * $c;
}

// ---------------- USER SESSION ----------------
$restaurant_id = $_SESSION['restaurant_id'] ?? null;
$user_id = $_SESSION['user_id'] ?? null;
$user_lat = $_SESSION['user_lat'] ?? 0;
$user_long = $_SESSION['user_long'] ?? 0;

// ---------------- FETCH ALL RESTAURANTS RANDOMLY ----------------
$sql = "SELECT * FROM restaurant_info ORDER BY RAND()";
$result = $conn->query($sql);

if (!$result) {
    die("Query Failed: " . $conn->error);
}

// ---------------- CONVERT TO ARRAY ----------------
$restaurants = [];
while ($row = $result->fetch_assoc()) {
    // fetch settings for distance check
    $settings_sql = "SELECT delivery_radius FROM restaurant_settings WHERE restaurant_id = ?";
    $stmt = $conn->prepare($settings_sql);
    $stmt->bind_param("i", $row['restaurant_id']);
    $stmt->execute();
    $settings = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    $delivery_radius = $settings['delivery_radius'] ?? 5; // default 5 km

    $distance = distance($user_lat, $user_long, $row['latitude'], $row['longitude']);

    if ($distance <= $delivery_radius) {
        $row['distance'] = $distance;
        $restaurants[] = $row;
    }
}

// ---------------- REORDER ACTIVE RESTAURANT ON TOP ----------------
if ($restaurant_id) {
    usort($restaurants, function($a, $b) use ($restaurant_id) {
        if ($a['restaurant_id'] == $restaurant_id) return -1;
        if ($b['restaurant_id'] == $restaurant_id) return 1;
        return 0;
    });
}

// ---------------- CURRENT TIME ----------------
date_default_timezone_set("Asia/Kolkata");
$current_time = date("H:i:s");

// ---------------- DISPLAY RESTAURANTS ----------------
foreach ($restaurants as $row) {
    $is_active = ($restaurant_id == $row['restaurant_id']) ? 'active' : '';

    // fetch cuisines
    $cuisine_sql = "SELECT name FROM categories WHERE restaurant_id = ?";
    $stmt = $conn->prepare($cuisine_sql);
    $stmt->bind_param("i", $row['restaurant_id']);
    $stmt->execute();
    $cuisine_result = $stmt->get_result();
    $cuisines = [];
    while ($cat = $cuisine_result->fetch_assoc()) {
        $cuisines[] = $cat['name'];
    }
    $stmt->close();
    $cuisine_text = empty($cuisines) ? 'Not Set' : implode(' | ', array_slice($cuisines, 0, 4)) . (count($cuisines) > 4 ? '...more' : '');

    // fetch settings
    $settings_sql = "SELECT preparation_time, opening_time, closing_time, delivery_fee FROM restaurant_settings WHERE restaurant_id = ?";
    $stmt2 = $conn->prepare($settings_sql);
    $stmt2->bind_param("i", $row['restaurant_id']);
    $stmt2->execute();
    $settings = $stmt2->get_result()->fetch_assoc();
    $stmt2->close();

    $preparation_time = $settings['preparation_time'] ?? "30-45";
    $opening_time = $settings['opening_time'] ?? "09:00:00";
    $closing_time = $settings['closing_time'] ?? "22:00:00";
    $delivery_fee = ($settings['delivery_fee'] ?? 0) > 0 ? "₹" . $settings['delivery_fee'] . " delivery" : "free delivery";

    // status
    if ($current_time >= $opening_time && $current_time <= $closing_time) {
        $status_class = "open";
        $status_text = "Open Now";
    } else {
        $status_class = "closed";
        $status_text = "Closed";
    }

    // rating
    $rating_sql = "SELECT ROUND(AVG(rating), 1) AS avg_rating FROM ratings WHERE restaurant_id = ?";
    $stmt3 = $conn->prepare($rating_sql);
    $stmt3->bind_param("i", $row['restaurant_id']);
    $stmt3->execute();
    $rating_data = $stmt3->get_result()->fetch_assoc();
    $stmt3->close();
    $avg_rating = $rating_data['avg_rating'] ?? 0.0;

    $found = true;
    // output
    echo '
    <a href="restaurant_menu.php?restaurant_id=' . (int)$row['restaurant_id'] . '" 
       class="restaurant-card ' . $is_active . '" 
       data-restaurant-id="' . (int)$row['restaurant_id'] . '">

        <div class="restaurant-image">
            <img src="admin/uploads/' . htmlspecialchars($row['restaurant_img']) . '" alt="' . htmlspecialchars($row['restaurant_name']) . '">
            <div class="restaurant-rating">
                <i class="fas fa-star"></i><span>' . htmlspecialchars($avg_rating) . '</span>
            </div>
            ' . ($is_active ? '<div class="current-badge">Currently Viewing</div>' : '') . '
        </div>

        <div class="restaurant-info">
            <div class="restaurant-header">
                <h3>' . htmlspecialchars($row['restaurant_name']) . '</h3>
                <span class="restaurant-status ' . $status_class . '">' . $status_text . '</span>
            </div>
            <div class="restaurant-cuisine">
                <i class="fas fa-tag"></i>
                <span>' . htmlspecialchars($cuisine_text) . '</span>
            </div>
            <div class="restaurant-cuisine">
                <i class="fas fa-map-marker-alt"></i>
                <span>' . htmlspecialchars($row['restaurant_place']) . '</span>
            </div>
            <div class="restaurant-footer">
                <div class="restaurant-delivery">
                    <i class="fas fa-clock"></i>
                    <span>' . htmlspecialchars($preparation_time) . ' min</span>
                </div>
                <span id="delivery">' . htmlspecialchars($delivery_fee) . '</span>
            </div>
        </div>

    </a>';
}

if (!$found) {
    include 'unavailable_restaurant.php';
}
?>
