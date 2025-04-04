<?php
session_start();
include "../config.php";

$emp_id = $_SESSION['emp_id'] ?? null;
if (!$emp_id) {
    header("Location: ../index.php");
    exit;
}

$clue = "Look under the table where knowledge is stored.";
$_SESSION['clue2'] = $clue;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Stage 2 - Clue</title>
</head>
<body>
    <h2>Stage 2: Solve the Puzzle</h2>
    <p>Find the answer using the given clue.</p>
    <p><strong>Clue:</strong> <?php echo $clue; ?></p>

    <form action="stage3.php" method="POST">
        <input type="hidden" name="emp_id" value="<?php echo $emp_id; ?>">
        <input type="text" name="answer" placeholder="Enter your answer">
        <button type="submit">Submit Answer</button>
    </form>
</body>
</html>
