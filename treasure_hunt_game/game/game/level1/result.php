<?php
session_start();
include '../../config.php';

// Ensure user is logged in
if (!isset($_SESSION['email'])) {
    die("Error: User email not found in session. Please log in again.");
}

$email = $_SESSION['email'];

// Fetch user ID securely
$query = "SELECT id FROM users WHERE email = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    die("Error: User not found in the database.");
}

$user = $result->fetch_assoc();
$user_id = $user['id'];

// Check if user already has a record
$check_query = "SELECT SUM(correct_answers) AS total_score, COUNT(*) AS attempt_count FROM results WHERE user_id = ?";
$check_stmt = $conn->prepare($check_query);
$check_stmt->bind_param("i", $user_id);
$check_stmt->execute();
$check_result = $check_stmt->get_result();
$score_data = $check_result->fetch_assoc();

$total_score = $score_data['total_score'] ?? 0;
$attempt_count = $score_data['attempt_count'] ?? 0;

// Insert only if no previous record exists
if ($attempt_count == 0) {
    if (!isset($_SESSION['score'])) {
        $_SESSION['score'] = 0;
    }

    $score = $_SESSION['score'];

    $insert_query = "INSERT INTO results (user_id, correct_answers, total_questions, score) VALUES (?, ?, 25, ?)";
    $insert_stmt = $conn->prepare($insert_query);
    $insert_stmt->bind_param("iii", $user_id, $score, $score);
    $insert_stmt->execute();

    // Update total score after insertion
    $total_score += $score;
}

$levelquery = "SELECT level FROM user_levels WHERE user_id = ?";
$levelstmt = $conn->prepare($levelquery);
$levelstmt->bind_param("i", $user_id);
$levelstmt->execute();
$levelresult = $levelstmt->get_result();
$level = $levelresult->fetch_assoc();
if ($level) {
    $level = $level['level'];
} else {
    $level = 1; // Default to level 1 if not found
}
// Unlock next level
$next_level = $level + 1;   
$unlock_query = "INSERT IGNORE INTO user_levels (user_id, level, status) VALUES (?, ?, 'unlocked')";
$unlock_stmt = $conn->prepare($unlock_query);
$unlock_stmt->bind_param("ii", $user_id, $next_level);
$unlock_stmt->execute();
$unlock_stmt->close();
$levelstmt->close();
//update current level as locked
$update_query = "UPDATE user_levels SET status = 'locked' WHERE user_id = ? AND level = ?";
$update_stmt = $conn->prepare($update_query);
$update_stmt->bind_param("ii", $user_id, $level);
$update_stmt->execute();
$update_stmt->close();  

//display 2nd level password
$level2query = "SELECT passcode FROM passwords WHERE level = 2 && user_id = ?";
$level2stmt = $conn->prepare($level2query);
$level2stmt->bind_param("i", $next_level);
$level2stmt->execute();
$level2result = $level2stmt->get_result();
$level2 = $level2result->fetch_assoc();
if ($level2) {
    $level2 = $level2['passcode'];
} else {
    $level2 = "No password found for this level.";
}
$level2stmt->close();
$level2result->close();
?>
<!doctype html>
<html lang="en">
<head>
    <title>Result</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <link href="https://fonts.googleapis.com/css?family=Lato:300,400,700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css">
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
<section class="ftco-section">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-6 text-center mb-5">
                <h2 class="heading-section">Result</h2>
            </div>
        </div>
        <div class="row justify-content-center">
            <div class="col-md-12 col-lg-10">
                <div class="wrap d-md-flex">
                    <div class="img" style="background-image: url(images/result.png);"></div>
                    <div class="login-wrap p-4 p-md-5">
                        <div class="d-flex">
                            <div class="w-100">
                                <h3 class="mb-4">Your Score</h3>
                            </div>
                        </div>
                        <div class="form-group mb-3">
                            <h3>Your Total Score: <?php echo $total_score; ?></h3>
                            <h3>Your Level: <?php echo $level; ?></h3>

                            <?php if($total_score >= 8) { 
                                echo "<h3 class='text-success'>Congratulations! You passed the level.</h3>";
                                echo "<h3>Your Next Level Password: $level2</h3>";
                            } else {
                                echo "<h3 class='text-danger'>Sorry! You failed the level.</h3>";
                            }
                              ?>
                        </div>
                        <p class="text-center"><a href="../logout.php">Log Out</a></p>
                        <p class="text-center"><a href="../../game_start.php">Start Next Level</a></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
<script src="js/jquery.min.js"></script>
<script src="js/popper.js"></script>
<script src="js/bootstrap.min.js"></script>
<script src="js/main.js"></script>
</body>
</html>
