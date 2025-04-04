<?php
session_start();
include "config.php";

if (!isset($_SESSION['emp_id'])) {
    die("Unauthorized access");
}

$data = json_decode(file_get_contents("php://input"), true);
$score = $data['score'];
$time_taken = $data['time_taken'];
$emp_id = $_SESSION['emp_id'];

$query = "INSERT INTO crossword_results (emp_id, score, time_taken) VALUES ('$emp_id', '$score', '$time_taken')";
if ($conn->query($query)) {
    echo json_encode(["status" => "success"]);
} else {
    echo json_encode(["status" => "error", "message" => $conn->error]);
}
?>
