<?php
session_start();
include '../../config.php'; // Database connection

$user_id = $_SESSION['user_id'];
$answers = $_POST['answers'];
$questions = $_SESSION['level3_questions'];

$score = 0;

// Check Answers
foreach ($questions as $q) {
    $q_id = $q['id'];
    if (isset($answers[$q_id]) && $answers[$q_id] === $q['correct_option']) {
        $score++;
    }
}

// Check if user has already played
$query = "SELECT * FROM level3_scores WHERE user_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$userData = $result->fetch_assoc();

if ($userData) {
    echo "You have already played this level!";
    header("refresh:5;url=../../game_start.php");
    exit();
}

// Check if user won (10/10 score)
$status = ($score === 10) ? 'win' : 'fail';
$passcode = ($status === 'win') ? strtoupper(substr(md5(uniqid()), 0, 6)) : null;

// Store Score
$insertQuery = "INSERT INTO level3_scores (user_id, score, status, passcode) VALUES (?, ?, ?, ?)";
$stmt = $conn->prepare($insertQuery);
$stmt->bind_param("iiss", $user_id, $score, $status, $passcode);
$stmt->execute();

// Redirect Based on Result
if ($status === 'win') {
    $_SESSION['passcode'] = $passcode;
    header("Location: level3_result.php");
} else {
    echo "Sorry, you didn't pass. Try again!";
    header("refresh:5;url=../../game_start.php");
}
exit();
