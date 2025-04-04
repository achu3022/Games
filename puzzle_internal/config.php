<?php
$servername = "localhost"; // Change if necessary
$username = "root"; // Your database username
$password = "403035Abhi#"; // Your database password
$dbname = "holi_puzzle_internal"; // Your database name

$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}


