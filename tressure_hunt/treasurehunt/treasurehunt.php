<?php
session_start();
include "../config.php";

$emp_id = $_SESSION['emp_id'] ?? null;
if (!$emp_id) {
    header("Location: ../index.php");
    exit;
}

// Get current stage
$current_stage = $_SESSION['current_stage'] ?? 2;

// Fetch the clue and answer from the database
$stmt = $conn->prepare("SELECT clue, answer FROM th_clues WHERE stage = ?");
$stmt->bind_param("i", $current_stage);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header("Location: game_complete.php"); // Redirect if all stages are completed
    exit;
}

$row = $result->fetch_assoc();
$clue = $row['clue'];
$correct_answer = strtolower($row['answer']);

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $user_answer = strtolower(trim($_POST['answer'] ?? ''));

    // Start timing
    if (!isset($_SESSION['start_time'])) {
        $_SESSION['start_time'] = time();
    }

    // Validate answer
    if ($user_answer === $correct_answer) {
        $end_time = time();
        $time_taken = $end_time - $_SESSION['start_time'];

        // Store result
        $stmt = $conn->prepare("INSERT INTO th_game_progress (emp_id, stage, clue, answer, time_taken) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("sisss", $emp_id, $current_stage, $clue, $user_answer, $time_taken);
        $stmt->execute();

        // Move to next stage
        $_SESSION['current_stage'] = ++$current_stage;
        $_SESSION['start_time'] = time();  // Reset timer for next stage
        header("Location: treasurehunt.php");
        exit;
    } else {
        $error = "Incorrect answer! Try again.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Stage <?php echo $current_stage; ?> - Treasure Hunt</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            text-align: center;
            background-color: #f4f4f4;
            padding: 20px;
        }
        .container {
            max-width: 500px;
            background: white;
            padding: 20px;
            margin: auto;
            box-shadow: 2px 2px 10px rgba(0,0,0,0.1);
            border-radius: 10px;
        }
        h2 {
            color: #333;
        }
        p {
            font-size: 18px;
            color: #555;
        }
        input {
            width: 80%;
            padding: 10px;
            margin: 10px 0;
            border: 1px solid #ccc;
            border-radius: 5px;
        }
        button {
            padding: 10px 20px;
            background: #28a745;
            color: white;
            border: none;
            cursor: pointer;
            font-size: 16px;
            border-radius: 5px;
        }
        button:hover {
            background: #218838;
        }
        .error {
            color: red;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Stage <?php echo $current_stage; ?>: Solve the Puzzle</h2>
        <p><strong>Clue:</strong> <?php echo $clue; ?></p>
        <?php if (isset($error)) { echo "<p class='error'>$error</p>"; } ?>

        <form method="POST">
            <input type="text" name="answer" placeholder="Enter your answer" required>
            <button type="submit">Submit Answer</button>
        </form>
    </div>
</body>
</html>
