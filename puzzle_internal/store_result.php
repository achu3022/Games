<?php
session_start();
include 'config.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *'); // Allow AJAX requests from any domain
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

// Prevent PHP from outputting errors in response
error_reporting(0);
ini_set('display_errors', 0);

// Log received POST data (for debugging)
error_log("Received POST data: " . json_encode($_POST));

if (!isset($_POST["user_id"]) || !isset($_POST["status"])) {
    echo json_encode(["error" => "Missing required fields", "received" => $_POST]);
    exit();
}

$user_id = $_POST["user_id"];
$status = $_POST["status"];

$stmt_update = $conn->prepare("UPDATE scores SET status = ? WHERE user_id = ? AND status = 'Failed' ORDER BY id DESC LIMIT 1");

if (!$stmt_update) {
    echo json_encode(["error" => "SQL Prepare Failed", "sql_error" => $conn->error]);
    exit();
}

$stmt_update->bind_param("si", $status, $user_id);

if ($stmt_update->execute()) {
    if ($stmt_update->affected_rows > 0) {
        echo json_encode(["success" => "Game result updated"]);
    } else {
        echo json_encode(["error" => "No matching 'Failed' record found or update failed"]);
    }
} else {
    echo json_encode(["error" => "SQL Execute Failed", "sql_error" => $stmt_update->error]);
}

$stmt_update->close();
$conn->close();
?>
