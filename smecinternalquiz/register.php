<?php
ob_start(); // Start output buffering
session_start();
include 'config.php'; // Database connection

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = trim($_POST["name"]);
    $phone = trim($_POST["phone"]);
    $department = trim($_POST["department"]);

    // Check if phone number already exists
    $check_stmt = $conn->prepare("SELECT id, name FROM users WHERE phone = ?");
    $check_stmt->bind_param("s", $phone);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();
    
    if ($check_result->num_rows > 0) {
        // ✅ User already exists, fetch details
        $user = $check_result->fetch_assoc();
        $user_id = $user['id'];
        $user_name = $user['name'];

        // ✅ Check if user has already attempted the quiz
        $check_attempt_stmt = $conn->prepare("SELECT SUM(score) AS total_score, MAX(correct_answers) AS last_score FROM results WHERE user_id = ?");
        $check_attempt_stmt->bind_param("i", $user_id);
        $check_attempt_stmt->execute();
        $attempt_result = $check_attempt_stmt->get_result();
        $attempt_data = $attempt_result->fetch_assoc();

        $total_score = $attempt_data['total_score'] ?? 0;
        $previous_score = $attempt_data['last_score'] ?? 0;

        // ✅ If user has already attempted, show score & exit
        if ($previous_score > 0) {
            echo "<div style='text-align: center; margin-top: 100px; font-size: 20px;'>";
            echo "<h2>Welcome back, <strong>$user_name</strong>! 🎉</h2>";
            echo "<p>You have already attempted the quiz.</p>";
            echo "<p><strong>Last Quiz Score:</strong> $previous_score / 25</p>";
            echo "<p><strong>Total Score from all attempts:</strong> $total_score</p>";
            
            echo "<a href='result.php' style='display: inline-block; padding: 10px 20px; background-color: green; color: white; text-decoration: none; border-radius: 5px; margin-top: 10px;'>View Detailed Results</a>";
            
            echo "<br><br><a href='logout.php' style='color: red; text-decoration: none;'>Logout</a>";
            echo "</div>";
            exit();
        } else {
            // ✅ If user exists but has not attempted quiz, continue to quiz
            $_SESSION['phone'] = $phone;
            $_SESSION['name'] = $user_name;
            header("Location: quiz.php");
            exit();
        }
    } else {
        // ✅ Register new user
        $stmt = $conn->prepare("INSERT INTO users (name, phone, department) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $name, $phone, $department);

        if ($stmt->execute()) {
            $_SESSION['phone'] = $phone;
            $_SESSION['name'] = $name;
            header("Location: quiz.php");
            exit();
        } else {
            echo "Registration failed! Please try again.";
        }
    }

    $check_stmt->close();
    $stmt->close();
    $conn->close();
}

ob_end_flush(); // End output buffering
?>
