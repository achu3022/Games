<?php
session_start();
include "config.php";

if (!isset($_SESSION['emp_id'])) {
    die("Unauthorized access");
}

$query = "SELECT * FROM crossword_questions ORDER BY RAND() LIMIT 12";
$result = $conn->query($query);

$questions = [];
while ($row = $result->fetch_assoc()) {
    $questions[] = $row;
}

echo json_encode($questions);
?>
