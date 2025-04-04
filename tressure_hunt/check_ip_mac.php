<?php
session_start();
require 'config.php'; // Include DB connection

$emp_id = $_POST['emp_id'] ?? null;
$ip = $_POST['ip'] ?? null;
$mac = $_POST['mac'] ?? null;

if (!$emp_id || !$ip || !$mac) {
    echo json_encode(["status" => "error", "message" => "Missing parameters"]);
    exit;
}
$_SESSION['ip_address'] = $ip; // Store employee ID in session
// Check if the device (IP & MAC) is already used by another employee
$query = "SELECT emp_id, completed FROM player_tracking WHERE ip_address = ? AND mac_address = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("ss", $ip, $mac);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $existing = $result->fetch_assoc();
    
    if ($existing['emp_id'] != $emp_id) {
        // ❌ Device already used by another employee → Redirect
        $_SESSION['warning_user'] = $existing['emp_id']; // Store the conflicting user
        header("Location: device_warning.php");
        exit;
    }

    if ($existing['completed'] == 1) {
        // ❌ The same user already played the game → Show message
        echo json_encode(["status" => "warning", "message" => "You have already played the game."]);
        exit;
    }
}

// If new login → Store in DB
$insert_query = "INSERT INTO player_tracking (emp_id, ip_address, mac_address, completed) 
                 VALUES (?, ?, ?, 0) 
                 ON DUPLICATE KEY UPDATE emp_id = emp_id"; // Ensures no duplicate device use
$stmt = $conn->prepare($insert_query);
$stmt->bind_param("sss", $emp_id, $ip, $mac);
$stmt->execute();

$_SESSION['emp_id'] = $emp_id; // Store in session for tracking

echo json_encode(["status" => "success"]);
?>
