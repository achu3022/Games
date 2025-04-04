<?php
session_start();
require 'config.php';

$emp_id = $_SESSION['emp_id'] ?? null;
$mac_address = $_SESSION['mac_address'] ?? null;

if (!$emp_id || !$mac_address) {
    echo "Unauthorized access!";
    exit;
}

// Check if the user has already completed the game OR using a different device
$query = "SELECT completed, mac_address FROM player_tracking WHERE emp_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("s", $emp_id);
$stmt->execute();
$result = $stmt->get_result();
$data = $result->fetch_assoc();

if ($data && $data['mac_address'] && $data['mac_address'] !== $mac_address) {
    // 🚨 MAC mismatch, redirect to warning page
    $_SESSION['warning_user'] = $emp_id;  // Store user details for warning
    header("Location: device_warning.php");
    exit;
} 
else if ($data['completed'] == 1) {
    // 🚨 User already played
    echo "<h2>You have already played the game.</h2>";
    exit;
}

// ✅ Allow access to the game
header("Location: treasurehunt/stage1_quiz.php");
?>
