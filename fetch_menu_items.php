<?php
include 'database_connection.php';
session_start();

$restaurant_id = $_SESSION['restaurant_id'] ?? null;
$category = $_POST['category'] ?? 'All';

if (!$restaurant_id) exit();

if ($category !== 'All') {
    $stmt = $conn->prepare("SELECT * FROM items WHERE restaurant_id = ? AND category = ?");
    $stmt->bind_param("is", $restaurant_id, $category);
} else {
    $stmt = $conn->prepare("SELECT * FROM items WHERE restaurant_id = ?");
    $stmt->bind_param("i", $restaurant_id);
}
$stmt->execute();
$result = $stmt->get_result();

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        echo '
        <div class="menu-item">
            <div class="image-container">
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
                    <div class="item-price">₹' . number_format($row['price'], 2) . '</div>
                    <button class="add-btn" data-item-id="' . $row['id'] . '" data-user-id="' . $_SESSION['user_id'] . '" ' . ($row['status'] != 'available' ? 'disabled' : '') . '>+ Add</button>
                </div>
            </div>
        </div>';
    }
} else {
    echo '<p>No menu items found for this category.</p>';
}
$stmt->close();
?>
