<?php
include 'database_connection.php';
if (isset($_GET['id'])) {
    $id = $_GET['id'];

    $sql = "DELETE FROM items WHERE id='$id'";

    if ($conn->query($sql) === TRUE) {
        header("Location: menu.php");
        exit();
    } else {
        echo "Error deleting record: " . $conn->error;
    }
}
?>