<?php
include '../config/database_connection.php';
session_start();

$restaurant_id = $_SESSION['restaurant_id'] ?? null;
if (!$restaurant_id) exit();

$category = $_POST['category'] ?? 'All';
$search   = $_POST['liveSearch'] ?? '';

// base query
$query  = "SELECT * FROM items WHERE restaurant_id = ?";
$params = [$restaurant_id];
$types  = "i";

// category check
if ($category !== 'All') {
    $query .= " AND category = ?";
    $params[] = $category;
    $types   .= "s";
}

// live search check
if (!empty($search)) {
    $query .= " AND name LIKE ?";
    $params[] = "%" . $search . "%";
    $types   .= "s";
}

$stmt = $conn->prepare($query);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$result = $stmt->get_result();

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $final_price = $row['price'] - ($row['price'] * $row['discount'] / 100);
        echo '
        <div class="menu-item ' . ($row['status'] == 'available' ? '' : 'unavailable') . '">
            <div class="image-container">';
                if ($row['discount'] > 0) {
                    echo '
                    <div class="discount">
                        <span>' . htmlspecialchars($row['discount']) . '%</span>
                        <span>OFF</span>
                    </div>';
                }
                echo '
                <img src="admin/' . htmlspecialchars($row['image']) . '" alt="' . htmlspecialchars($row['name']) . '" class="item-image">
            </div>
            <div class="item-details">
                <div class="item-header">
                    <div class="item-name">
                        <p>' . htmlspecialchars($row['name']) . '</p>
                        <i class="fa-solid fa-circle-' . ($row['status'] == 'available' ? 'check' : 'xmark') . '"></i>
                    </div>
                </div>
                <div class="item-portion">' . htmlspecialchars($row['description']) . '</div>
                <div class="item-footer">
                    <div>';
                        if ($row['discount'] > 0) {
                            echo '<p class="old_price">₹' . number_format($row['price'], 0) . '</p>';
                        }
                        echo '
                        <div class="item-price">₹' . number_format($final_price, 0) . '</div>
                    </div>
                    <button class="add-btn" data-item-id="' . $row['id'] . '" data-user-id="' . $_SESSION['user_id'] . '" ' . ($row['status'] != 'available' ? 'disabled' : '') . '>+ Add</button>
                </div>
            </div>
        </div>';
    }
} else {
    echo '<p>No items found.</p>';
}

$stmt->close();
$conn->close();
?>
