<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

header('Content-Type: application/json');

require 'config/database_connection.php';

require 'phpmailer/Exception.php';
require 'phpmailer/PHPMailer.php';
require 'phpmailer/SMTP.php';

require 'vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

// Only POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request']);
    exit();
}

// Order ID validation
$order_id = (int) ($_POST['order_id'] ?? 0);

if ($order_id <= 0) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid order ID']);
    exit();
}

try {

    // Fetch order + customer + address
    $stmt = $conn->prepare("
        SELECT 
            o.id, o.user_id, o.restaurant_id,
            o.subtotal, o.taxes, o.delivery_fee, o.total,
            o.instructions, o.order_date, o.status,
            u.name AS customer_name,
            a.name AS address_name,
            a.street, a.city,
            a.phone AS address_phone
        FROM orders o
        LEFT JOIN users u   ON o.user_id = u.id
        LEFT JOIN address a ON o.address_id = a.id
        WHERE o.id = ?
        LIMIT 1
    ");
    $stmt->bind_param("i", $order_id);
    $stmt->execute();
    $order = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$order) throw new Exception('Order not found');

    $restaurant_id = (int) $order['restaurant_id'];

    // Notification settings
    $stmt = $conn->prepare("
        SELECT new_orders, notification_emails 
        FROM notification_settings 
        WHERE restaurant_id = ? 
        LIMIT 1
    ");
    $stmt->bind_param("i", $restaurant_id);
    $stmt->execute();
    $settings = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$settings) {
        echo json_encode(['status' => 'success', 'message' => 'No notification settings found']);
        exit();
    }

    if ((int) $settings['new_orders'] !== 1) {
        echo json_encode(['status' => 'success', 'message' => 'New order email notification is disabled']);
        exit();
    }

    // Parse notification emails
    $email_text = trim($settings['notification_emails'] ?? '');

    if ($email_text === '') {
        echo json_encode(['status' => 'success', 'message' => 'No notification email configured']);
        exit();
    }

    $emails = [];
    foreach (preg_split('/\r\n|\r|\n/', $email_text) as $email) {
        $email = trim($email);
        if ($email !== '' && filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $emails[] = $email;
        }
    }
    $emails = array_unique($emails);

    if (empty($emails)) throw new Exception('No valid notification email found');

    // Fetch order items
    $stmt = $conn->prepare("
        SELECT item_name, quantity, price, discount 
        FROM order_items 
        WHERE order_id = ? 
        ORDER BY id ASC
    ");
    $stmt->bind_param("i", $order_id);
    $stmt->execute();
    $items_result = $stmt->get_result();

    $order_items_html = '';
    while ($it = $items_result->fetch_assoc()) {
        $item_name = htmlspecialchars($it['item_name'], ENT_QUOTES, 'UTF-8');
        $qty       = (int) $it['quantity'];
        $price     = (float) $it['price'];
        $discount  = (float) $it['discount'];
        $final     = $price - ($price * $discount / 100);
        $lineTotal = $final * $qty;

        $order_items_html .= "
            <tr>
                <td style='padding:10px;border-bottom:1px solid #eee;'>{$item_name}</td>
                <td style='padding:10px;text-align:center;border-bottom:1px solid #eee;'>{$qty}</td>
                <td style='padding:10px;text-align:right;border-bottom:1px solid #eee;'>₹" . number_format($lineTotal, 2) . "</td>
            </tr>
        ";
    }
    $stmt->close();

    // Prepare display values
    $customer_name  = htmlspecialchars($order['customer_name'] ?? 'Customer', ENT_QUOTES, 'UTF-8');
    $customer_phone = htmlspecialchars($order['address_phone'] ?? 'N/A', ENT_QUOTES, 'UTF-8');

    $delivery_address = htmlspecialchars(
        trim(($order['address_name'] ?? '') . ', ' . ($order['street'] ?? '') . ', ' . ($order['city'] ?? '')),
        ENT_QUOTES, 'UTF-8'
    );

    $subtotal     = number_format((float) $order['subtotal'], 2);
    $taxes        = number_format((float) $order['taxes'], 2);
    $delivery_fee = number_format((float) $order['delivery_fee'], 2);
    $total        = number_format((float) $order['total'], 2);

    $status = htmlspecialchars($order['status'], ENT_QUOTES, 'UTF-8');

    $instructions = trim($order['instructions'] ?? '');
    if ($instructions === '') $instructions = 'No special instructions';
    $instructions = htmlspecialchars($instructions, ENT_QUOTES, 'UTF-8');

    $order_date = date('d M Y, h:i A', strtotime($order['order_date']));

    // Send email via PHPMailer
    $mail = new PHPMailer(true);

    $mail->isSMTP();
    $mail->Host       = 'smtp.gmail.com';
    $mail->SMTPAuth   = true;
    $mail->Username   = $_ENV['MAIL_USERNAME'];
    $mail->Password   = $_ENV['MAIL_PASSWORD'];
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port       = 587;
    $mail->Timeout    = 10;
    $mail->SMTPKeepAlive = false;

    $mail->setFrom($_ENV['MAIL_USERNAME'], 'FoodExpress');

    foreach ($emails as $email) {
        $mail->addAddress($email);
    }

    $mail->isHTML(true);
    $mail->Subject = "New Order Received - FoodExpress #{$order_id}";

    // Email HTML body
    $mail->Body = "
<!DOCTYPE html>
<html>
<head><meta charset='UTF-8'><title>New Order</title></head>
<body style='margin:0;padding:0;background:#f5f5f5;font-family:Arial,Helvetica,sans-serif;'>

<div style='max-width:650px;margin:30px auto;background:#fff;border-radius:10px;overflow:hidden;box-shadow:0 2px 10px rgba(0,0,0,0.08);'>

    <div style='background:#ff6b6b;color:#fff;padding:25px;text-align:center;'>
        <h1 style='margin:0;'>New Order!</h1>
        <p style='margin:8px 0 0;font-size:14px;'>FoodExpress</p>
    </div>

    <div style='padding:25px;'>

        <h2 style='margin-top:0;color:#333;'>Order #{$order_id}</h2>
        <p style='color:#666;'>A new order has been received.</p>

        <table width='100%' cellpadding='0' cellspacing='0' style='margin-top:20px;'>
            <tr><td style='padding:6px 0;'><strong>Customer:</strong></td><td style='padding:6px 0;'>{$customer_name}</td></tr>
            <tr><td style='padding:6px 0;'><strong>Phone:</strong></td><td style='padding:6px 0;'>{$customer_phone}</td></tr>
            <tr><td style='padding:6px 0;'><strong>Date:</strong></td><td style='padding:6px 0;'>{$order_date}</td></tr>
            <tr><td style='padding:6px 0;'><strong>Status:</strong></td><td style='padding:6px 0;'>{$status}</td></tr>
        </table>

        <h3 style='margin-top:25px;color:#333;'>Delivery Address</h3>
        <div style='background:#f8f8f8;padding:15px;border-radius:6px;color:#555;'>{$delivery_address}</div>

        <h3 style='margin-top:25px;color:#333;'>Order Items</h3>
        <table width='100%' cellpadding='0' cellspacing='0' style='border-collapse:collapse;'>
            <thead>
                <tr style='background:#f5f5f5;'>
                    <th style='padding:10px;text-align:left;'>Item</th>
                    <th style='padding:10px;text-align:center;'>Qty</th>
                    <th style='padding:10px;text-align:right;'>Price</th>
                </tr>
            </thead>
            <tbody>{$order_items_html}</tbody>
        </table>

        <h3 style='margin-top:25px;color:#333;'>Order Summary</h3>
        <table width='100%' cellpadding='0' cellspacing='0'>
            <tr><td style='padding:6px 0;'>Subtotal</td><td style='padding:6px 0;text-align:right;'>₹{$subtotal}</td></tr>
            <tr><td style='padding:6px 0;'>Taxes</td><td style='padding:6px 0;text-align:right;'>₹{$taxes}</td></tr>
            <tr><td style='padding:6px 0;'>Delivery Fee</td><td style='padding:6px 0;text-align:right;'>₹{$delivery_fee}</td></tr>
            <tr>
                <td style='padding:12px 0;border-top:2px solid #eee;font-size:18px;'><strong>Total</strong></td>
                <td style='padding:12px 0;border-top:2px solid #eee;text-align:right;font-size:18px;'><strong>₹{$total}</strong></td>
            </tr>
        </table>

        <h3 style='margin-top:25px;color:#333;'>Special Instructions</h3>
        <div style='background:#f8f8f8;padding:15px;border-radius:6px;color:#555;'>{$instructions}</div>

    </div>

    <div style='background:#f8f8f8;padding:15px;text-align:center;color:#888;font-size:12px;'>
        This is an automated notification from FoodExpress.
    </div>

</div>
</body>
</html>";

    // Plain-text fallback
    $mail->AltBody = "New Order #{$order_id}\n\n"
        . "Customer: {$order['customer_name']}\n"
        . "Phone: {$order['address_phone']}\n"
        . "Total: ₹{$total}\n"
        . "Status: {$order['status']}";

    $mail->send();

    echo json_encode([
        'status'  => 'success',
        'message' => 'Order notification email sent successfully'
    ]);

} catch (Exception $e) {
    error_log('FoodExpress Order Mail Error: ' . $e->getMessage());
    echo json_encode([
        'status'  => 'error',
        'message' => 'Mail sending failed'
    ]);
}

exit();