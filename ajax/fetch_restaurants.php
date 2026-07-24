<?php
session_start();

if (isset($_POST['lat']) && isset($_POST['long'])) {
    $_SESSION['user_lat'] = $_POST['lat'];
    $_SESSION['user_long'] = $_POST['long'];
    
    echo "saved";
} else {
    echo "no_data";
}
?>
