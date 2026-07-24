<?php
session_start();
include '../config/database_connection.php';

$restaurant_id = $_SESSION['admin_id'];
$data = json_decode(file_get_contents('php://input'), true);

if($data && is_array($data)){
    $stmt = $conn->prepare("UPDATE categories SET display_order = ? WHERE id = ? AND restaurant_id = ?");
    foreach($data as $item){
        $id = intval($item['id']);
        $pos = intval($item['position']);
        $stmt->bind_param("iii", $pos, $id, $restaurant_id);
        $stmt->execute();
    }
    $stmt->close();
    echo json_encode(['success'=>true]);
} else {
    echo json_encode(['success'=>false]);
}

$conn->close();
