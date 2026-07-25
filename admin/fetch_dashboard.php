<?php
include '../config/database_connection.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$restaurant_id = $_SESSION['admin_id'] ?? 0;

if (isset($_POST['recent_order'])) {
    // ✅ Check for valid restaurant_id
    if ($restaurant_id <= 0) {
        echo "<tr><td colspan='7'>Invalid restaurant session.</td></tr>";
        exit;
    }

    $stmt_orders = $conn->prepare("
        SELECT id, user_id, total, order_date, status 
        FROM orders 
        WHERE restaurant_id = ? AND DATE(order_date) = CURDATE() 
        ORDER BY id DESC
    ");
    $stmt_orders->bind_param("i", $restaurant_id);
    $stmt_orders->execute();
    $result_orders = $stmt_orders->get_result();

    if ($result_orders->num_rows > 0) {
        while ($row = $result_orders->fetch_assoc()) {
            $order_id = $row['id'];
            $total = $row['total'];
            $user_id = $row['user_id'];
            $status = $row['status'];
            $order_date = date("h:i A", strtotime($row['order_date']));
            $st_color = strtolower($status);

            // ✅ Fetch user name safely
            $user_name = 'Unknown';
            $stmt_user = $conn->prepare("SELECT name FROM users WHERE id = ?");
            $stmt_user->bind_param("i", $user_id);
            $stmt_user->execute();
            $result_user = $stmt_user->get_result();
            if ($result_user && $result_user->num_rows > 0) {
                $user_row = $result_user->fetch_assoc();
                $user_name = $user_row['name'];
            }
            $stmt_user->close();

            // ✅ Fetch total items
            $total_items = 0;
            $stmt_items = $conn->prepare("SELECT COUNT(*) AS total FROM order_items WHERE order_id = ?");
            $stmt_items->bind_param("i", $order_id);
            $stmt_items->execute();
            $result_items = $stmt_items->get_result();
            if ($result_items && $row_items = $result_items->fetch_assoc()) {
                $total_items = $row_items['total'];
            }
            $stmt_items->close();

            // ✅ Output table row
            echo '
            <tr>
                <td>#ORD-' . htmlspecialchars($order_id + 1000) . '</td>
                <td>' . htmlspecialchars($user_name) . '</td>
                <td>' . htmlspecialchars($order_date) . '</td>
                <td>' . htmlspecialchars($total_items) . '</td>
                <td>₹' . htmlspecialchars($total) . '</td>
                <td><span class="order-status status-' . $st_color . '">' . htmlspecialchars($status) . '</span></td>
                <td>
                    <button class="action-btn btn-view" data-id="' . $order_id . '">
                        <i class="fas fa-eye"></i> View
                    </button>';

            if ($status === "Processing") {
                echo '
                    <button class="action-btn btn-complete" data-id="' . $order_id . '">
                        <i class="fas fa-check"></i> Complete
                    </button>';
            }

            if ($status === "Pending") {
                echo '
                    <button class="action-btn btn-process" data-id="' . $order_id . '">
                        <i class="fas fa-spinner"></i> Process
                    </button>
                    <button class="action-btn btn-cancel" data-id="' . $order_id . '">
                        <i class="fas fa-times"></i> Cancel
                    </button>';
            }

            echo '</td></tr>';
        }
    } else {
        echo "<tr><td colspan='7' style='text-align:center; color:#666;'>
                <i class='fas fa-box-open' style='font-size:18px; color:#999; margin-right:5px;'></i> 
                No orders found for today.
              </td></tr>";
    }

    $stmt_orders->close();
    $conn->close();
}
