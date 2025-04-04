<?php
$servername = "localhost"; // Change if necessary
$username = "root"; // Your database username
$password = "403035Abhi#"; // Your database password
$dbname = "treasure_hunt"; // Your database name

$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Email Configuration
$mail_host = "smtp.puthiyidathu.in";
$mail_username = "abhijithps@puthiyidathu.in";
$mail_password = "403035Abhi#";
$mail_secure = "tls";
$mail_port = 587;
$mail_from = "abhijithps@puthiyidathu.in";
$mail_from_name = "Quiz App";

$baseurl = 'http://127.0.0.1/games/treasure_hunt_game/';
?>
