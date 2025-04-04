<?php
session_start();
include 'config.php';

// Ensure user is logged in
if (!isset($_SESSION['phone'])) {
    die("Error: User phone number not found in session. Please log in again.");
}

$phone = $_SESSION['phone'];

// Fetch user ID securely
$query = "SELECT id FROM users WHERE phone = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("s", $phone);
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
                        </div>
                        <p class="text-center"><a href="logout.php">Log Out</a></p>
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
