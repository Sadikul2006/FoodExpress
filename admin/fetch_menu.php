<?php
require '../config/database_connection.php';

if (isset($_POST['restaurant_id']) && isset($_POST['category'])) {
    $restaurant_id = $_POST['restaurant_id'];
    $category = $_POST['category'];

    if ($category === 'All') {
        $stmt = $conn->prepare("SELECT * FROM items WHERE restaurant_id = ?");
        $stmt->bind_param("i", $restaurant_id);
    } else {
        $stmt = $conn->prepare("SELECT * FROM items WHERE restaurant_id = ? AND category = ?");
        $stmt->bind_param("is", $restaurant_id, $category);
    }

    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $final_price = $row['price'] - ($row['price'] * $row['discount'] / 100);
            echo '
            <div class="menu-card"> ';
                if ($row['discount'] > 0) {
                    echo '
                    <div class="discount">
                        <span>' . htmlspecialchars($row['discount']) . '%</span>
                        <span>OFF</span>
                    </div>';
                }
                echo '
                <img src="' . htmlspecialchars($row['image']) . '" alt="' . htmlspecialchars($row['name']) . '" class="menu-image">
                <div class="menu-details">
                    <div class="menu-header">
                        <h3 class="menu-name">' . htmlspecialchars($row['name']) . '</h3>
                        <span class="menu-status ' . ($row['status'] == 'available' ? 'status-available' : 'status-unavailable') . '">
                            ' . ucfirst(htmlspecialchars($row['status'])) . '
                        </span>
                    </div>
                    <p class="menu-description">' . htmlspecialchars($row['description']) . '</p>
                    <div class="menu-footer">
                        <div>';
                            if ($row['discount'] > 0) {
                                echo '<p class="old_price">₹' . number_format($row['price'], 0) . '</p>';
                            };
                        echo '
                            <span class="menu-price">₹' . number_format($final_price, 2) . '</span>
                        </div>
                        <div class="menu-actions">
                            <a href="action_edit_items.php?id=' . $row['id'] . '" class="btn btn-primary">
                                <i class="fas fa-edit"></i>
                            </a>
                            <a href="action_delete_items.php?id=' . $row['id'] . '" class="btn btn-danger" onclick="return confirm(\'Are you sure you want to delete this item?\')">
                                <i class="fas fa-trash"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </div>';
        }
    } else {
        echo '
        <div class="empty-state">
            <div class="icon">
                <i class="fas fa-utensils"></i>
            </div>
            <h3>No Menu Items Found</h3>
            <p>No items found under this category. Try adding new ones!</p>
            <a href="additems.php" class="btn">
                <i class="fas fa-plus"></i> Add New Item
            </a>
        </div>';
    }

    $stmt->close();
    $conn->close();
}
?>
