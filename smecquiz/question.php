<?php
session_start();
include 'config.php';

// Check if user is logged in
if (!isset($_SESSION['email'])) {
    header("Location: index.php");
    exit();
}

$email = $_SESSION['email'];

// Fetch user name from database
$query = $conn->prepare("SELECT name FROM users WHERE email = ?");
$query->bind_param("s", $email);
$query->execute();
$result = $query->get_result();
$user = $result->fetch_assoc();
$user_name = $user['name'] ?? 'User';

// Check if quiz is completed
if ($_SESSION['current_question'] >= count($_SESSION['questions'])) {
    header("Location: result.php");
    exit();
}

// Fetch current question (DO NOT increment yet)
$question_number = $_SESSION['current_question'] + 1;
$question = $_SESSION['questions'][$_SESSION['current_question']];

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quiz</title>
    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>
    
    <style>
            .timer { font-size: 18px; font-weight: bold; color: #ff4444; }
        @import url('https://fonts.googleapis.com/css2?family=Montserrat&display=swap');

*{
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

body {
    background-color:rgb(206, 173, 151); /* Subtle light background */
    color: #753802; /* Updated text color */
}
.container .text-light {
    color: #753802; /* Updated color for light text */
}
.container h3 {
    color: #cd9e74; /* Updated heading color */
}
.container {
    background-color: #fff;
    color: #753802; /* Updated container text color */
    border-radius: 10px;
    padding: 20px;
    font-family: 'Montserrat', sans-serif;
    box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.1);
    max-width: 700px;
}
.container > p {
    font-size: 32px;
}
.question {
    width: 75%;
    color:#753802; /* Updated question text color */
}
.options {
    position: relative;
    padding-left: 40px;
    color: #753802; /* Updated options text color */
}
#options label {
    display: block;
    margin-bottom: 15px;
    font-size: 14px;
    cursor: pointer;
}
.options input {
    opacity: 0;
}
.checkmark {
    position: absolute;
    top: -1px;
    left: 0;
    height: 25px;
    width: 25px;
    background-color: #555;
    border: 1px solid #ddd;
    border-radius: 50%;
}
.options input:checked ~ .checkmark:after {
    display: block;
}
.options .checkmark:after {
    content: "";
    width: 10px;
    height: 10px;
    display: block;
    background: white;
    position: absolute;
    top: 50%;
    left: 50%;
    border-radius: 50%;
    transform: translate(-50%, -50%) scale(0);
    transition: 300ms ease-in-out 0s;
}
.options input[type="radio"]:checked ~ .checkmark {
    background:#753802; /* Updated selected checkmark color */
    transition: 300ms ease-in-out 0s;
}
.options input[type="radio"]:checked ~ .checkmark:after {
    transform: translate(-50%, -50%) scale(1);
}
.btn-primary {
    background-color: #555;
    color: #ddd;
    border: 1px solid #ddd;
}
.btn-primary:hover {
    background-color: #cd9e74; /* Updated hover background */
    border: 1px solid #cd9e74; /* Updated hover border color */
}
.btn-success {
    padding: 5px 25px;
    background-color: #d3ad6e; /* Updated success button background */
}
@media(max-width:576px) {
    .question {
        width: 100%;
        word-spacing: 2px;
    }
}
    </style>
</head>

<body>
<div class="container text-center mt-3">
    <h3 class="text-lights" style="color :rgb(107, 37, 5);">Welcome, <?= htmlspecialchars($user_name) ?>! Get Ready for Your Quiz</h3>
    <p><a href="logout.php" class="btn btn-danger">Logout</a></p>
</div>

<div class="container mt-sm-5 my-1">
    <div class="d-flex justify-content-between">
        <div>Question <?= $question_number ?> / <?= count($_SESSION['questions']) ?></div>
        <div class="timer" id="timer">20</div>
    </div>

    <div class="question">
        <form method="post" action="next_question.php">
            <div class="py-2 h5"><b><?= htmlspecialchars($question['question']) ?></b></div>
            <div id="options">
                <label class="options"><?= htmlspecialchars($question['option_a']) ?>
                    <input type="radio" name="answer" value="A" required>
                    <span class="checkmark"></span>
                </label>
                <label class="options"><?= htmlspecialchars($question['option_b']) ?>
                    <input type="radio" name="answer" value="B">
                    <span class="checkmark"></span>
                </label>
                <label class="options"><?= htmlspecialchars($question['option_c']) ?>
                    <input type="radio" name="answer" value="C">
                    <span class="checkmark"></span>
                </label>
                <label class="options"><?= htmlspecialchars($question['option_d']) ?>
                    <input type="radio" name="answer" value="D">
                    <span class="checkmark"></span>
                </label>
            </div>

            <button type="submit" class="btn btn-success mt-3">Next</button>
        </form>
    </div>
</div>

<script>
    let totalTime = 20; // Total countdown time
let timerElement = document.getElementById("timer");

// Reset timer when the page loads (new question)
sessionStorage.setItem("quiz_start_time", Date.now());

function getRemainingTime() {
    let startTime = sessionStorage.getItem("quiz_start_time");

    if (startTime) {
        let elapsedTime = Math.floor((Date.now() - startTime) / 1000);
        return Math.max(totalTime - elapsedTime, 0);
    } else {
        sessionStorage.setItem("quiz_start_time", Date.now()); // Reset on new question
        return totalTime;
    }
}

let timeLeft = getRemainingTime();

function updateTimer() {
    if (timeLeft > 0) {
        timeLeft--;
        timerElement.textContent = timeLeft;
    } else {
        sessionStorage.removeItem("quiz_start_time"); // Remove session storage when time is up
        document.forms[0].submit(); // Auto-submit if time runs out
    }
}

timerElement.textContent = timeLeft; // Set initial time
let timerInterval = setInterval(updateTimer, 1000);

// Reset timer when the form is submitted
document.forms[0].addEventListener("submit", function () {
    sessionStorage.removeItem("quiz_start_time"); // Clear old time
});

</script>

</body>
</html>
