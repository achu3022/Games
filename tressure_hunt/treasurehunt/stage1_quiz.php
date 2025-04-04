<?php
session_start();
include "../config.php";

$emp_id = $_SESSION['emp_id'];

// Fetch 5 random questions from `th_quiz_questions` only if not set in session
if (!isset($_SESSION['questions'])) {
    $query = "SELECT * FROM th_quiz_questions ORDER BY RAND() LIMIT 5";
    $result = $conn->query($query);

    $questions = [];
    while ($row = $result->fetch_assoc()) {
        $questions[] = $row;
    }

    // Store questions in session
    $_SESSION['questions'] = $questions;
    $_SESSION['current_question'] = 0;
    $_SESSION['score'] = 0;
}

// Check current score
$query = "SELECT SUM(is_correct) AS total_score FROM th_quiz_answers WHERE emp_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("s", $emp_id);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();
$total_score = $row['total_score'] ?? 0;

// If user has scored 5, generate code and store in DB
if ($total_score >= 5 && !isset($_SESSION['game_code'])) {
    // Generate 10-digit alphanumeric code
    $game_code = strtoupper(substr(md5(uniqid(rand(), true)), 0, 10));

    // Store code in database
    $insert = "INSERT INTO th_game_codes (emp_id, code) VALUES (?, ?)";
    $stmt = $conn->prepare($insert);
    $stmt->bind_param("ss", $emp_id, $game_code);
    $stmt->execute();

    // Store in session to prevent multiple insertions
    $_SESSION['game_code'] = $game_code;
}

// Display quiz or results
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Stage 1 - Quiz</title>
    <script>
        let timer = 20;

        function startTimer() {
            let timerElement = document.getElementById("timer");
            let interval = setInterval(() => {
                if (timer <= 0) {
                    clearInterval(interval);
                    submitAnswer('none'); // Auto-submit if time runs out
                } else {
                    timerElement.innerText = timer;
                    timer--;
                }
            }, 1000);
        }

        function submitAnswer(option) {
            let emp_id = "<?php echo $_SESSION['emp_id']; ?>";
            let question_id = document.getElementById("question_id").value;

            fetch("submit_answer.php", {
                method: "POST",
                body: JSON.stringify({ emp_id, question_id, selected_option: option }),
                headers: { "Content-Type": "application/json" }
            })
            .then(response => response.json())
            .then(data => {
                if (data.stop_quiz) {
                    window.location.href = "stage1_result.php"; // Go to result page
                } else {
                    location.reload(); // Load next question
                }
            });
        }
    </script>
</head>
<body onload="startTimer()">
    <h2>Stage 1: Quiz</h2>
    <p>Time left: <span id="timer">20</span> seconds</p>
    <p>Your Score: <strong><?php echo $total_score; ?></strong></p>

    <?php 
    if ($total_score >= 3) { 
        // Show Next Stage button and first two digits of game code
        $first_two_digits = substr($_SESSION['game_code'], 0, 2);
        header("Location: treasurehunt/stage2.php");

    ?>
        <h3>Your first key to the treasure: <strong><?php echo $first_two_digits; ?></strong></h3>
        <a href="stage2.php">Next Stage</a>
    <?php 
    } else if ($_SESSION['current_question'] < count($_SESSION['questions'])) {
        $q = $_SESSION['questions'][$_SESSION['current_question']];
    ?>
        <p><?php echo $q['question']; ?></p>
        <input type="hidden" id="question_id" value="<?php echo $q['id']; ?>">
        <button onclick="submitAnswer('A')"><?php echo $q['option_a']; ?></button>
        <button onclick="submitAnswer('B')"><?php echo $q['option_b']; ?></button>
        <button onclick="submitAnswer('C')"><?php echo $q['option_c']; ?></button>
        <button onclick="submitAnswer('D')"><?php echo $q['option_d']; ?></button>
    <?php } ?>
</body>
</html>
