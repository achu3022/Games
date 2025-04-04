<?php
include 'config.php';
session_start();

// Redirect if not logged in
if (!isset($_SESSION['phone'])) {
    header("Location: index.php");
    exit();
}

$phone = $_SESSION['phone'];

// Fetch user ID and Name securely
$query = "SELECT id, name FROM users WHERE phone = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("s", $phone);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    die("Error: User not found.");
}

$user = $result->fetch_assoc();
$user_id = $user['id'];
$user_name = $user['name'];

// Check if the user has already taken the quiz
$check_attempt_query = "SELECT SUM(score) AS total_score, MAX(correct_answers) AS last_score FROM results WHERE user_id = ?";
$check_stmt = $conn->prepare($check_attempt_query);
$check_stmt->bind_param("i", $user_id);
$check_stmt->execute();
$attempt_result = $check_stmt->get_result();
$attempt_data = $attempt_result->fetch_assoc();

$total_score = $attempt_data['total_score'] ?? 0;
$previous_score = $attempt_data['last_score'] ?? 0;

// If user has already attempted the quiz, display their score and exit
if ($previous_score > 0) {
    echo "<div style='text-align: center; margin-top: 100px; font-size: 20px;'>";
    echo "<h2>Welcome, <strong>$user_name</strong>! 🎉</h2>";
    echo "<p>You have already attempted the quiz.</p>";
    echo "<p><strong>Last Quiz Score:</strong> $previous_score / 25</p>";
    echo "<p><strong>Total Score from all attempts:</strong> $total_score</p>";
    
    echo "<a href='result.php' style='display: inline-block; padding: 10px 20px; background-color: green; color: white; text-decoration: none; border-radius: 5px; margin-top: 10px;'>View Detailed Results</a>";
    
    echo "<br><br><a href='logout.php' style='color: red; text-decoration: none;'>Logout</a>";
    echo "</div>";
    exit();
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
