<?php
use PHPMailer\PHPMailer\PHPMailer;
require 'vendor/autoload.php';
include "config.php";
session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $emp_id = $_POST['emp_id'];
    $otp = rand(100000, 999999);
    $_SESSION["otp"] = $otp;
    $_SESSION["emp_id"] = $emp_id;

    $mail = new PHPMailer(true);
    $mail->isSMTP();
    $mail->Host = 'smtp.puthiyidathu.in';
    $mail->SMTPAuth = true;
    $mail->Username = 'abhijithps@puthiyidathu.in';
    $mail->Password = '403035Abhi#';
    $mail->SMTPSecure = "tls";
    $mail->Port = 587;

    $mail->setFrom('abhijithps@puthiyidathu.in', 'SMEC Verification');
    $mail->addAddress($email);
    $mail->Subject = "Your Verification OTP";
    $mail->Body = "Your OTP is: " . $otp;

    echo json_encode(["status" => $mail->send() ? "otp_sent" : "error"]);
}
?>
