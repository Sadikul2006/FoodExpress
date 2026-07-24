<?php
include '../config/database_connection.php';

$status = $_POST['status'] ?? 'All';
$restaurant_id = $_POST['restaurant_id'] ?? 0;

$query = "SELECT id, user_id, total, order_date, status 
          FROM orders 
          WHERE restaurant_id = ?";

if ($status != "All") {
    $query .= " AND status = ?";
}

$query .= " ORDER BY id DESC";

$stmt = $conn->prepare($query);
if ($status != "All") {
    $stmt->bind_param("is", $restaurant_id, $status);
} else {
    $stmt->bind_param("i", $restaurant_id);
}
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    echo '
    <table class="orders-table">
        <thead>
            <tr>
                <th>Order ID</th>
                <th>Customer</th>
                <th>Date</th>
                <th>Items</th>
                <th>Total</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>';

    while ($row = $result->fetch_assoc()) {
        $order_id = $row['id'];
        $total = $row['total'];
        $status = $row['status'];
        $order_date = date("M d, Y", strtotime($row['order_date']));

        // fetch user name
        $stmt_user = $conn->prepare("SELECT name FROM users WHERE id = ?");
        $stmt_user->bind_param("i", $row['user_id']);
        $stmt_user->execute();
        $user_row = $stmt_user->get_result()->fetch_assoc();
        $user_name = $user_row['name'] ?? 'Unknown';
        $stmt_user->close();

        // total items
        $stmt_items = $conn->prepare("SELECT COUNT(*) AS total FROM order_items WHERE order_id = ?");
        $stmt_items->bind_param("i", $order_id);
        $stmt_items->execute();
        $total_items = $stmt_items->get_result()->fetch_assoc()['total'];
        $stmt_items->close();

        echo '
            <tr>
                <td>#ORD-' . ($order_id + 1000) . '</td>
                <td>' . htmlspecialchars($user_name) . '</td>
                <td>' . htmlspecialchars($order_date) . '</td>
                <td>' . htmlspecialchars($total_items) . '</td>
                <td>₹' . htmlspecialchars($total) . '</td>
                <td><span class="order-status status-' . strtolower($status) . '">' . $status . '</span></td>
                <td>
                    <button class="action-btn btn-view">
                        <i class="fas fa-eye"></i> View
                    </button>';

        if ($status == "Processing") {
            echo '
                    <button class="action-btn btn-complete">
                        <i class="fas fa-check"></i> Complete
                    </button>';
        }

        if ($status == "Pending") {
            echo '
                    <button class="action-btn btn-process">
                        <i class="fas fa-spinner"></i> Process
                    </button>
                    <button class="action-btn btn-cancel">
                        <i class="fas fa-times"></i> Cancel
                    </button>';
        }

        echo '
                </td>
            </tr>';
    }

    echo '</tbody></table>';
} else {
    echo "<p>No orders found for this filter.</p>";
}

$stmt->close();
$conn->close();
