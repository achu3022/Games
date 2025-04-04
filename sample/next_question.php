<?php
session_start();
include '../../config.php';

// Ensure user is logged in
if (!isset($_SESSION['email']) || !isset($_SESSION['questions']) || !isset($_SESSION['current_question'])) {
    header("Location: index.php");
    exit();
}

$email = $_SESSION['email'];

// Fetch user ID
$query = "SELECT id FROM users WHERE email = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

if (!$user) {
    die("User not found.");
}

$user_id = $user['id'];

// Initialize session variables if not set
if (!isset($_SESSION['correct_answers'])) {
    $_SESSION['correct_answers'] = 0;
}

if (!isset($_SESSION['quiz_completed'])) {
    $_SESSION['quiz_completed'] = false;
}

// Process the answer if the quiz is not finished
if ($_SESSION['current_question'] < count($_SESSION['questions'])) {
    $current_question = $_SESSION['questions'][$_SESSION['current_question']];

    // Check if an answer was submitted
    if (isset($_POST['answer'])) {
        $user_answer = $_POST['answer'];

        // If the answer is correct, increment correct answers
        if ($user_answer === $current_question['correct_option']) {
            $_SESSION['correct_answers']++;
        }
    }

    // Move to the next question
    $_SESSION['current_question']++;
}

// If quiz is completed, store the final score **only once**
if ($_SESSION['current_question'] >= count($_SESSION['questions']) && !$_SESSION['quiz_completed']) {
    $_SESSION['quiz_completed'] = true; // Prevent multiple entries

    $correct_answers = $_SESSION['correct_answers'];
    $total_questions = count($_SESSION['questions']);

    // ✅ Check if user already has a result
    $check_query = "SELECT id FROM results WHERE user_id = ?";
    $check_stmt = $conn->prepare($check_query);
    $check_stmt->bind_param("i", $user_id);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();

    if ($check_result->num_rows > 0) {
        // ✅ Update the existing result instead of inserting a new one
        $update_query = "UPDATE results SET correct_answers = ?, total_questions = ?, score = (? / ? * 100) WHERE user_id = ?";
        $update_stmt = $conn->prepare($update_query);
        $update_stmt->bind_param("iiiii", $correct_answers, $total_questions, $correct_answers, $total_questions, $user_id);
        $update_stmt->execute();
    } else {
        // ✅ Insert only if no record exists for the user
        $insert_query = "INSERT INTO results (user_id, correct_answers, total_questions, score) VALUES (?, ?, ?, (? / ? * 100))";
        $insert_stmt = $conn->prepare($insert_query);
        $insert_stmt->bind_param("iiiii", $user_id, $correct_answers, $total_questions, $correct_answers, $total_questions);
        $insert_stmt->execute();
    }

    // Redirect to result page
    header("Location: result.php");
    exit();
}

// Redirect to next question
header("Location: question.php");
exit();
?>
