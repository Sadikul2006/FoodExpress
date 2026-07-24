<?php
session_start();
header('Content-Type: application/json');

include "config/database_connection.php";

// PHPMailer files
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'phpmailer/Exception.php';
require 'phpmailer/PHPMailer.php';
require 'phpmailer/SMTP.php';

// Safety check
if (!isset($_POST['email'])) {
    echo json_encode(["status" => "error", "message" => "Invalid request"]);
    exit;
}

$email = trim($_POST['email']);

// Check email exists
$stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode(["status" => "error", "message" => "Email not registered"]);
    exit;
}

// Generate OTP
$otp = rand(100000, 999999);

// Save in session
$_SESSION['reset_otp']   = $otp;
$_SESSION['reset_email'] = $email;
$_SESSION['otp_time']    = time();

$mail = new PHPMailer(true);

try {
    // SMTP settings
    $mail->isSMTP();
    $mail->Host       = 'smtp.gmail.com';
    $mail->SMTPAuth   = true;
    $mail->Username   = 'sadikulseikh56@gmail.com';
    $mail->Password   = 'aokw eprt fxzs ehxt';
    $mail->SMTPSecure = 'tls';
    $mail->Port       = 587;

    // Email settings
    $mail->setFrom('sadikulseikh56@gmail.com', 'FoodExpress');
    $mail->addAddress($email);

    $mail->isHTML(true);
    $mail->Subject = 'Password Reset OTP';
    $mail->Body    = "
        <h3>Your OTP Code</h3>
        <p>Your OTP is: <b>$otp</b></p>
        <p>This OTP is valid for 5 minutes.</p>
    ";

    $mail->send();

    echo json_encode(["status" => "success"]);

} catch (Exception $e) {
    echo json_encode([
        "status" => "error",
        "message" => "Email sending failed"
    ]);
}
exit;
