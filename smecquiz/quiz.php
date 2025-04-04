<?php
include 'config.php';
session_start();

// Redirect if not logged in
if (!isset($_SESSION['email'])) {
    header("Location: index.php");
    exit();
}

$email = $_SESSION['email'];

// Fetch user ID securely
$query = "SELECT id FROM users WHERE email = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    die("Error: User not found.");
}

$user = $result->fetch_assoc();
$user_id = $user['id'];

// Check if the user has already taken the quiz
$check_attempt_query = "SELECT SUM(score) AS total_score, MAX(correct_answers) AS last_score FROM results WHERE user_id = ?";
$check_stmt = $conn->prepare($check_attempt_query);
$check_stmt->bind_param("i", $user_id);
$check_stmt->execute();
$attempt_result = $check_stmt->get_result();
$attempt_data = $attempt_result->fetch_assoc();

$total_score = $attempt_data['total_score'] ?? 0;
$previous_score = $attempt_data['last_score'] ?? 0;

// If user has already attempted the quiz, just show their scores (no new insertion)
if ($previous_score > 0) {
    echo "<div style='text-align: center; margin-top: 100px; font-size: 20px; color: red;'>";
    echo "You have already attempted the quiz!<br>";
    echo "Your last quiz score: <strong>$previous_score / 25</strong><br>";
    
    echo "<a href='logout.php' style='color: blue; text-decoration: none;'>Logout</a>";
    echo "</div>";
    exit(); // Stop execution
}

// If user has not attempted, allow them to start
$questions_query = "SELECT * FROM quiz_questions ORDER BY RAND() LIMIT 25";
$questions = $conn->query($questions_query);
$_SESSION['questions'] = $questions->fetch_all(MYSQLI_ASSOC);
$_SESSION['current_question'] = 0;
$_SESSION['score'] = 0;

header("Location: question.php");
exit();
?>
