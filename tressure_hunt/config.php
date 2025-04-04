<?php
$host = "localhost";
$user = "root";
$pass = "403035Abhi#";
$dbname = "smec_tressure_hunt";

$conn = new mysqli($host, $user, $pass, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}


$mail_host = "smtp.puthiyidathu.in";
$mail_username = "abhijithps@puthiyidathu.in";
$mail_password = "403035Abhi#";
$mail_secure = "tls";
$mail_port = 587;
$mail_from = "abhijithps@puthiyidathu.in";
$mail_from_name = "Quiz App";
?>
