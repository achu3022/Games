<?php
session_start();
include "../config.php";

$emp_id = $_SESSION['emp_id'];

// Get total score
$query = "SELECT SUM(is_correct) AS total_score FROM th_quiz_answers WHERE emp_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $emp_id);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();
$total_score = $row['total_score'];

// Check if user passed
if ($total_score >= 3) {
    echo "<h2>Congratulations! You passed Stage 1. Proceed to Stage 2.</h2>";
    echo "<a href='stage2.php'>Next Stage</a>";
} else {
    echo "<h2>Sorry, you did not qualify for Stage 2.</h2>";
}
?>
