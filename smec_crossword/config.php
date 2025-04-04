<?php
$host = "localhost";
$user = "root";
$pass = "403035Abhi#";
$dbname = "smec_crossword";

$conn = new mysqli($host, $user, $pass, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
