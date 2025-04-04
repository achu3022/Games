<?php
session_start();
include "config.php";

// Check if emp_id is passed via URL and set session
if (!isset($_GET['emp_id'])) {
    header("Location: login.php");
    exit();
}

$emp_id = $_GET['emp_id'];
$_SESSION['emp_id'] = $emp_id;

// Fetch employee details
$query = "SELECT * FROM staffs WHERE emp_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("s", $emp_id);
$stmt->execute();
$result = $stmt->get_result();

if ($row = $result->fetch_assoc()) {
    $_SESSION['user_id'] = $row['id'];
    $name = $row['name'];
    $phone = $row['phone'];
    $department = $row['department'];
} else {
    header("Location: login.php?error=invalid_id");
    exit();
}

// Fetch 10 to 12 random crossword questions
$questionCount = rand(10, 12);
$questionsQuery = "SELECT * FROM crossword_questions ORDER BY RAND() LIMIT $questionCount";
$questionsResult = $conn->query($questionsQuery);

$gridSize = 10; // Define crossword grid size
$grid = array_fill(0, $gridSize, array_fill(0, $gridSize, '')); // Empty grid
$clueNumbers = [];
$clueList = [];
$clueIndex = 1;

// Place words in grid
while ($question = $questionsResult->fetch_assoc()) {
    $word = strtoupper(trim($question['answer']));
    $x = intval($question['position_x']);
    $y = intval($question['position_y']);
    $direction = $question['direction'];

    // Ensure word fits inside the grid boundaries
    if (($direction === 'horizontal' && ($x + strlen($word) > $gridSize)) ||
        ($direction === 'vertical' && ($y + strlen($word) > $gridSize))) {
        continue; // Skip this word if it exceeds grid size
    }

    // Assign clue number if this position is empty
    if (!isset($clueNumbers["$y-$x"])) {
        $clueNumbers["$y-$x"] = $clueIndex;
        $clueList[$clueIndex] = $question['question'];
        $clueIndex++;
    }

    // Place word in grid
    for ($i = 0; $i < strlen($word); $i++) {
        if ($direction == 'horizontal') {
            $grid[$y][$x + $i] = $word[$i];
        } else {
            $grid[$y + $i][$x] = $word[$i];
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Crossword Game</title>
    <link rel="stylesheet" href="style.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            text-align: center;
        }
        .crossword-grid {
            display: grid;
            grid-template-columns: repeat(<?= $gridSize ?>, 40px);
            gap: 3px;
            justify-content: center;
            margin: 20px auto;
            background-color: #ddd;
            padding: 10px;
            border-radius: 8px;
        }
        .cell {
            width: 40px;
            height: 40px;
            text-align: center;
            font-size: 18px;
            text-transform: uppercase;
            border: 1px solid black;
            background-color: white;
            position: relative;
        }
        .shaded {
            background-color: #888;
        }
        .clue-number {
            font-size: 10px;
            position: absolute;
            top: 2px;
            left: 2px;
            color: black;
        }
        .input-box {
            width: 100%;
            height: 100%;
            border: none;
            text-align: center;
            font-size: 18px;
            background: transparent;
        }
        #submit-btn {
            margin-top: 20px;
            padding: 10px 20px;
            font-size: 18px;
            background-color: green;
            color: white;
            border: none;
            cursor: pointer;
            border-radius: 5px;
        }
    </style>
</head>
<body>
    <h1>Crossword Puzzle</h1>
    <p>Time Left: <span id="timer">10:00</span></p>

    <div class="crossword-grid">
        <?php for ($y = 0; $y < $gridSize; $y++): ?>
            <?php for ($x = 0; $x < $gridSize; $x++): ?>
                <div class="cell <?= $grid[$y][$x] ? '' : 'shaded' ?>">
                    <?php if ($grid[$y][$x]): ?>
                        <?php if (isset($clueNumbers["$y-$x"])): ?>
                            <span class="clue-number"><?= $clueNumbers["$y-$x"] ?></span>
                        <?php endif; ?>
                        <input type="text" class="input-box" maxlength="1" name="cell_<?= $y ?>_<?= $x ?>">
                    <?php endif; ?>
                </div>
            <?php endfor; ?>
        <?php endfor; ?>
    </div>

    <h2>Clues</h2>
    <ul>
        <?php foreach ($clueList as $number => $clue): ?>
            <li><strong><?= $number ?>.</strong> <?= $clue ?></li>
        <?php endforeach; ?>
    </ul>

    <button id="submit-btn">Submit</button>
</body>
</html>
