<?php

session_start();
header('Content-Type: application/json');

include "config/database_connection.php";

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'phpmailer/Exception.php';
require 'phpmailer/PHPMailer.php';
require 'phpmailer/SMTP.php';
require 'vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

if (!isset($_SESSION['reset_email'])) {
    echo json_encode([
        "status" => "error",
        "message" => "Cannot resend OTP. Please start again."
    ]);
    exit;
}

$email = $_SESSION['reset_email'];

$otp = rand(100000, 999999);

$_SESSION['reset_otp'] = $otp;
$_SESSION['otp_time'] = time();

$mail = new PHPMailer(true);

try {

    $mail->isSMTP();
    $mail->Host = 'smtp.gmail.com';
    $mail->SMTPAuth = true;

    $mail->Username = $_ENV['MAIL_USERNAME'];
    $mail->Password = $_ENV['MAIL_PASSWORD'];

    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port = 587;

    $mail->setFrom(
        $_ENV['MAIL_USERNAME'],
        'FoodExpress'
    );

    $mail->addAddress($email);

    $mail->isHTML(true);
    $mail->Subject = 'Resent Password Reset OTP';

    $mail->Body = "
        <h3>Your new OTP Code</h3>
        <p>Your OTP is: <b>$otp</b></p>
        <p>This OTP is valid for 5 minutes.</p>
    ";

    $mail->send();

    echo json_encode([
        "status" => "success"
    ]);

} catch (Exception $e) {

    echo json_encode([
        "status" => "error",
        "message" => "Failed to send OTP. Try again."
    ]);
}

exit;