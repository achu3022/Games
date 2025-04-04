<?php
session_start();
include "../config.php";

// Read JSON input
$data = json_decode(file_get_contents("php://input"), true);

$emp_id = $_SESSION['emp_id'];
$question_id = intval($data['question_id']);
$selected_option = $data['selected_option'];

// Fetch the correct answer
$query = "SELECT correct_option FROM th_quiz_questions WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $question_id);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();
$correct_option = $row['correct_option'] ?? '';

// Determine if the answer is correct
$score = ($selected_option == $correct_option) ? 1 : 0;

// Insert answer into `th_quiz_answers`
$insert = "INSERT INTO th_quiz_answers (emp_id, question_id, selected_option, is_correct) VALUES (?, ?, ?, ?)";
$stmt = $conn->prepare($insert);
$stmt->bind_param("sisi", $emp_id, $question_id, $selected_option, $score);
$stmt->execute();

// Get total score
$query = "SELECT SUM(is_correct) AS total_score FROM th_quiz_answers WHERE emp_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("s", $emp_id);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();
$total_score = $row['total_score'] ?? 0;

// Move to next question
$_SESSION['current_question']++;

// Stop quiz if user has 5 correct answers
$response = [
    "stop_quiz" => ($total_score >= 5)
];

echo json_encode($response);
?>
