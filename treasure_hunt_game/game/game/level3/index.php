<?php
session_start();
include '../../config.php'; // Database connection

//need to check if user is previuosly played or not
// Fetch user ID securely
$user_id = $_SESSION['user_id'];
if (!isset($user_id)) {
    header("Location: ../../../index.php");
    exit();
}
// Check if the user has already taken the quiz
// Check if user has already played Level 3
$check_attempt_query = "SELECT MAX(score) AS last_score FROM level3_scores WHERE user_id = ?";
$check_stmt = $conn->prepare($check_attempt_query);
$check_stmt->bind_param("i", $user_id);
$check_stmt->execute();
$attempt_result = $check_stmt->get_result();
$attempt_data = $attempt_result->fetch_assoc();
$previous_score = $attempt_data['last_score'] ?? 0;

// Fetch password for the next level (Level 4)
$password_query = "SELECT passcode FROM passwords WHERE level = 4 AND user_id = ?";
$password_stmt = $conn->prepare($password_query);
$password_stmt->bind_param("i", $user_id);
$password_stmt->execute();
$password_result = $password_stmt->get_result();
$password_data = $password_result->fetch_assoc();
$passcode = $password_data['passcode'] ?? null; // Missing semicolon added ✅

// If user has already attempted the quiz, show their score (no new quiz attempt)
if ($previous_score > 0) {
    echo "<div style='text-align: center; margin-top: 100px; font-size: 20px; color: red;'>";
    echo "You have already attempted the quiz!<br>";
    echo "Your last quiz score: <strong>$previous_score / 10</strong><br>";

    if ($previous_score >= 8) {
        echo "Congratulations! You passed the quiz!<br>";
        echo "Your passcode to unlock the next level: <strong>$passcode</strong><br>";
    } else {
        echo "Sorry, you didn't pass. Try again!<br>";
    }

    echo "<a href='../../game_start.php' style='color: blue; text-decoration: none;'>Go to Dashboard</a>";
    echo "</div>";
    exit();
}


// Fetch 10 random questions
$query = "SELECT * FROM level3_questions ORDER BY RAND() LIMIT 10";
$result = $conn->query($query);

$questions = [];
while ($row = $result->fetch_assoc()) {
    $questions[] = $row;
}

// Store the questions in session
$_SESSION['level3_questions'] = $questions;
$_SESSION['start_time'] = time(); // Track start time

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Level 3 Quiz</title>
    <style>
        body { font-family: Arial, sans-serif; text-align: center; }
        .question { margin-bottom: 20px; }
        .timer { font-size: 18px; color: red; }
    </style>
    <script>
        // Countdown Timer (15 minutes)
        var timeLeft = 900;
        function countdown() {
            if (timeLeft <= 0) {
                document.getElementById("quizForm").submit();
            } else {
                var minutes = Math.floor(timeLeft / 60);
                var seconds = timeLeft % 60;
                document.getElementById("timer").innerHTML = minutes + "m " + seconds + "s";
                timeLeft--;
                setTimeout(countdown, 1000);
            }
        }
        window.onload = countdown;
    </script>
</head>
<body>
    <h2>Level 3 Quiz</h2>
    <p class="timer">Time Left: <span id="timer">15:00</span></p>

    <form id="quizForm" action="level3_submit.php" method="post">
        <?php foreach ($questions as $index => $q) { ?>
            <div class="question">
                <p><strong><?php echo ($index + 1) . ". " . htmlspecialchars($q['question']); ?></strong></p>
                <input type="radio" name="answers[<?php echo $q['id']; ?>]" value="A" required> <?php echo htmlspecialchars($q['option_a']); ?><br>
                <input type="radio" name="answers[<?php echo $q['id']; ?>]" value="B"> <?php echo htmlspecialchars($q['option_b']); ?><br>
                <input type="radio" name="answers[<?php echo $q['id']; ?>]" value="C"> <?php echo htmlspecialchars($q['option_c']); ?><br>
                <input type="radio" name="answers[<?php echo $q['id']; ?>]" value="D"> <?php echo htmlspecialchars($q['option_d']); ?><br>
            </div>
        <?php } ?>
        <button type="submit">Submit Answers</button>
    </form>
</body>
</html>
