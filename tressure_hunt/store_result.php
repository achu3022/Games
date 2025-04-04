<?php
session_start();
require 'db_connection.php';

$emp_id = $_SESSION['emp_id'] ?? null;

if (!$emp_id) {
    echo "Unauthorized access!";
    exit;
}

// Mark the game as completed
$update_query = "UPDATE player_tracking SET completed = 1 WHERE emp_id = ?";
$stmt = $conn->prepare($update_query);
$stmt->bind_param("s", $emp_id);
$stmt->execute();

echo "Game completed!";
?>
