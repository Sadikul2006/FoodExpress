<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include '../config/database_connection.php';

header('Content-Type: text/html; charset=utf-8');

// ---------- Auth ----------
if (!isset($_SESSION['admin_id'])) {
    http_response_code(401);
    echo '<p style="color:red;text-align:center;">Not authenticated.</p>';
    exit();
}

$restaurant_id = (int) $_SESSION['admin_id'];
$status = $_POST['status'] ?? 'All';
$page   = max(1, (int) ($_POST['page'] ?? 1));
$limit  = 10;
$offset = ($page - 1) * $limit;

$allowed_statuses = ['All', 'Pending', 'Processing', 'Completed', 'Cancelled', 'Delivered'];
if (!in_array($status, $allowed_statuses, true)) {
    $status = 'All';
}

// ---------- Count total ----------
$countQuery = "SELECT COUNT(*) AS total FROM orders WHERE restaurant_id = ?";
if ($status !== "All") {
    $countQuery .= " AND status = ?";
}
$countStmt = $conn->prepare($countQuery);
if ($status !== "All") {
    $countStmt->bind_param("is", $restaurant_id, $status);
} else {
    $countStmt->bind_param("i", $restaurant_id);
}
$countStmt->execute();
$total_orders = (int) $countStmt->get_result()->fetch_assoc()['total'];
$countStmt->close();

$total_pages = max(1, (int) ceil($total_orders / $limit));
if ($page > $total_pages) {
    $page   = $total_pages;
    $offset = ($page - 1) * $limit;
}

// ---------- Fetch page ----------
$query = "SELECT id, user_id, total, order_date, status 
          FROM orders 
          WHERE restaurant_id = ?";
if ($status !== "All") {
    $query .= " AND status = ?";
}
$query .= " ORDER BY id DESC LIMIT ? OFFSET ?";

$stmt = $conn->prepare($query);
if ($status !== "All") {
    $stmt->bind_param("isii", $restaurant_id, $status, $limit, $offset);
} else {
    $stmt->bind_param("iii", $restaurant_id, $limit, $offset);
}
$stmt->execute();
$result = $stmt->get_result();

// ---------- Collect rows FIRST ----------
$rows     = [];
$orderIds = [];
while ($row = $result->fetch_assoc()) {
    $rows[]     = $row;
    $orderIds[] = (int) $row['id'];
}

// ✅ close $stmt exactly once, right after draining the result set
$stmt->close();

// ---------- No results ----------
if (empty($rows)) {
    echo '<p style="text-align:center;padding:20px;">No orders found for this filter.</p>';
    $conn->close();
    exit();
}

// ---------- Batch fetch user names ----------
$userIds = array_unique(array_column($rows, 'user_id'));
$userMap = [];
if (!empty($userIds)) {
    $ph    = implode(',', array_fill(0, count($userIds), '?'));
    $types = str_repeat('i', count($userIds));
    $uStmt = $conn->prepare("SELECT id, name FROM users WHERE id IN ($ph)");
    $uStmt->bind_param($types, ...$userIds);
    $uStmt->execute();
    $uRes = $uStmt->get_result();
    while ($u = $uRes->fetch_assoc()) {
        $userMap[(int)$u['id']] = $u['name'];
    }
    $uStmt->close();
}

// ---------- Batch fetch item counts ----------
$itemCounts = [];
if (!empty($orderIds)) {
    $ph    = implode(',', array_fill(0, count($orderIds), '?'));
    $types = str_repeat('i', count($orderIds));
    $iStmt = $conn->prepare("SELECT order_id, COUNT(*) AS cnt FROM order_items WHERE order_id IN ($ph) GROUP BY order_id");
    $iStmt->bind_param($types, ...$orderIds);
    $iStmt->execute();
    $iRes = $iStmt->get_result();
    while ($ir = $iRes->fetch_assoc()) {
        $itemCounts[(int)$ir['order_id']] = (int)$ir['cnt'];
    }
    $iStmt->close();
}

// ---------- Render ----------
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

foreach ($rows as $row) {
    $order_id     = (int) $row['id'];
    $total        = $row['total'];
    $order_status = $row['status'];
    $order_date   = date("M d, Y", strtotime($row['order_date']));
    $user_name    = $userMap[(int)$row['user_id']] ?? 'Unknown';
    $total_items  = $itemCounts[$order_id] ?? 0;

    echo '
    <tr>
        <td>#ORD-' . ($order_id + 1000) . '</td>
        <td>' . htmlspecialchars($user_name) . '</td>
        <td>' . htmlspecialchars($order_date) . '</td>
        <td>' . $total_items . '</td>
        <td>₹' . htmlspecialchars($total) . '</td>
        <td><span class="order-status status-' . strtolower(htmlspecialchars($order_status)) . '">'
            . htmlspecialchars($order_status) . '</span></td>
        <td>
            <button class="action-btn btn-view" data-order-id="' . $order_id . '">
                <i class="fas fa-eye"></i> View
            </button>';

    if ($order_status === "Processing") {
        echo '
            <button class="action-btn btn-complete" data-order-id="' . $order_id . '">
                <i class="fas fa-check"></i> Complete
            </button>';
    }

    if ($order_status === "Pending") {
        echo '
            <button class="action-btn btn-process" data-order-id="' . $order_id . '">
                <i class="fas fa-spinner"></i> Process
            </button>
            <button class="action-btn btn-cancel" data-order-id="' . $order_id . '">
                <i class="fas fa-times"></i> Cancel
            </button>';
    }

    echo '
        </td>
    </tr>';
}

echo '</tbody></table>';

// ---------- Pagination ----------
if ($total_pages > 1) {
    echo '<div class="pagination" data-current="' . $page . '" data-total="' . $total_pages . '">';

    $prev_disabled = ($page <= 1) ? 'disabled' : '';
    echo '<button class="page-btn" data-page="' . ($page - 1) . '" ' . $prev_disabled . '>
            <i class="fas fa-chevron-left"></i>
          </button>';

    $window = 2;
    for ($i = 1; $i <= $total_pages; $i++) {
        if ($i == 1 || $i == $total_pages || ($i >= $page - $window && $i <= $page + $window)) {
            $active = ($i === $page) ? 'active' : '';
            echo '<button class="page-btn ' . $active . '" data-page="' . $i . '">' . $i . '</button>';
        } elseif ($i == $page - $window - 1 || $i == $page + $window + 1) {
            echo '<span class="page-ellipsis">…</span>';
        }
    }

    $next_disabled = ($page >= $total_pages) ? 'disabled' : '';
    echo '<button class="page-btn" data-page="' . ($page + 1) . '" ' . $next_disabled . '>
            <i class="fas fa-chevron-right"></i>
          </button>';

    echo '</div>';
}

$conn->close();