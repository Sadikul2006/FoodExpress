<?php

include '../config/database_connection.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_POST['order_id'])) {
    exit;
}

$order_id = (int)$_POST['order_id'];

$sql = "
SELECT
    oi.*,
    o.subtotal,
    o.delivery_fee,
    o.taxes,
    o.total,
    o.instructions,
    o.address_id,
    u.name
FROM orders o
JOIN users u
    ON o.user_id = u.id
JOIN order_items oi
    ON oi.order_id = o.id
WHERE o.id = ?
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $order_id);
$stmt->execute();

$result = $stmt->get_result();

if ($result->num_rows == 0) {
    exit;
}

$order = $result->fetch_assoc();

/* Address */
$address_stmt = $conn->prepare("
SELECT *
FROM address
WHERE id = ?
LIMIT 1
");

$address_stmt->bind_param("i", $order['address_id']);
$address_stmt->execute();
$address_result = $address_stmt->get_result();
$address = $address_result->fetch_assoc();

echo '

<tr class="order-details-row">
<td colspan="7">

<div class="order-details-box">

<div class="details-wrapper">

<div class="items-section">

<table class="table">

<thead>

<tr>
<th>Image</th>
<th>Item</th>
<th>Qty</th>
<th>Price</th>
</tr>

</thead>

<tbody>

';

mysqli_data_seek($result,0);

while($row=$result->fetch_assoc()){

    $final_price=$row['price']-($row['price']*$row['discount']/100);

    echo '

<tr>

<td>

<img
src="'.htmlspecialchars($row['item_image']).'"
class="item-img">

</td>

<td>

<b>'.htmlspecialchars($row['item_name']).'</b><br>

<small>
'.htmlspecialchars($row['description']).'
</small>

</td>

<td>'.$row['quantity'].'</td>

<td>';

    if($row['discount']>0){

        echo '

<del>₹'.number_format($row['price'],2).'</del><br>

<span class="price-green">

₹'.number_format($final_price,2).'

</span>

';

    }else{

        echo '

₹'.number_format($row['price'],2).'

';

    }

    echo '

</td>

</tr>

';

}

echo '

</tbody>

</table>

</div>

<div class="summary-section">

<div class="summary-card">

<h4>Order Summary</h4>

<p>
<span>Subtotal</span>
<span>₹'.number_format($order['subtotal'],2).'</span>
</p>

<p>
<span>Delivery Fee</span>
<span>₹'.number_format($order['delivery_fee'],2).'</span>
</p>

<p>
<span>Taxes</span>
<span>₹'.number_format($order['taxes'],2).'</span>
</p>

<hr>

<p class="grand-total">
<span>Total</span>
<span>₹'.number_format($order['total'],2).'</span>
</p>

</div> ';
if($order['instructions'] != '') {
    echo '
    <div class="summary-card">
        <h4>Instruction</h4>
        <p>'.$order['instructions'].'</p>
    </div>';
}
if($address){

echo '

<div class="summary-card">

<h4>Delivery Address</h4>

<p>

'.htmlspecialchars($address['name']).'<br>

'.htmlspecialchars($address['street']).'<br>

'.htmlspecialchars($address['city']).'<br>

'.htmlspecialchars($address['phone']).'

</p>

</div>

';

}

echo '

</div>

</div>

</div>

</td>

</tr>

';

$stmt->close();
$address_stmt->close();
$conn->close();

?>