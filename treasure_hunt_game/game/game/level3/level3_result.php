<?php
session_start();
include '../../config.php';

$user_id = $_SESSION['user_id'];

$query = "SELECT status, passcode FROM level3_scores WHERE user_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$userData = $result->fetch_assoc();

if (!$userData) {
    echo "No record found!";
    exit();
}

$status = $userData['status'];
$passcode = $userData['passcode'];

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quiz Result</title>
    <style>
        body { font-family: Arial, sans-serif; text-align: center; }
        .container { margin-top: 50px; }
        .win { color: green; font-size: 20px; }
        .fail { color: red; font-size: 20px; }
    </style>
</head>
<body>
    <div class="container">
        <?php if ($status === 'win') { ?>
            <h2 class="win">Congratulations! You Passed Level 3</h2>
            <p>Your Passcode: <strong><?php echo $passcode; ?></strong></p>
            <p>Use this passcode to unlock the next level.</p>
        <?php } else { ?>
            <h2 class="fail">You did not pass. Try again!</h2>
        <?php } ?>
    </div>

    <script>
        setTimeout(function() {
            window.location.href = "../../game_start.php";
        }, 10000); // Redirect after 10 seconds
    </script>
</body>
</html>
