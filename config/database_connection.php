<?php 
  $servername = "localhost";
  $username = "root";
  $password = "";
  $dbname = "login_register";

  // Create connection
  $conn = new mysqli($servername, $username, $password, $dbname);
  // Check connection
  if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
  }
?>


<?php
// $servername = "sql302.infinityfree.com";
// $username = "if0_39910336";
// $password = "G8TLNTRY0Ci";
// $dbname = "if0_39910336_foodexpress_db";

// // Create connection
// $conn = new mysqli($servername, $username, $password, $dbname);

// // Check connection
// if ($conn->connect_error) {
//     die("Connection failed: " . $conn->connect_error);
// }
?>