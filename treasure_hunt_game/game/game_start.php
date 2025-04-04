<?php
session_start();
include_once '../config.php';

$user_id = $_SESSION['user_id'];
if (!isset($user_id)) {
    header("Location: ../index.php");
    exit();
}

// Ensure Level 1 is unlocked by default
$conn->query("INSERT IGNORE INTO user_levels (user_id, level, status) VALUES ($user_id, 1, 'unlocked')");

// Fetch user's level statuses
$levels = [];
$result = $conn->query("SELECT level, status FROM user_levels WHERE user_id = $user_id");
while ($row = $result->fetch_assoc()) {
    $levels[$row['level']] = $row['status'];
}

// Handle password submission for levels
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['level'], $_POST['password'])) {
    $level = intval($_POST['level']);
    $password = $_POST['password'];

    // Fetch correct password
    $stmt = $conn->prepare("SELECT passcode FROM passwords WHERE level = ?");
    $stmt->bind_param("i", $level);
    $stmt->execute();
    $stmt->bind_result($correct_password);
    $stmt->fetch();
    $stmt->close();

    if ($correct_password && $password === $correct_password) {
        // Mark level as completed
        $conn->query("UPDATE user_levels SET status='completed' WHERE user_id=$user_id AND level=$level");

        // Lock all other levels & unlock only the next level
        $conn->query("UPDATE user_levels SET status='locked' WHERE user_id=$user_id AND level <> $level + 1");
        $conn->query("INSERT INTO user_levels (user_id, level, status) VALUES ($user_id, $level + 1, 'unlocked') 
                      ON DUPLICATE KEY UPDATE status='unlocked'");

        echo json_encode(["status" => "success", "redirect" => "game/level$level/index.php"]);
    } else {
        echo json_encode(["status" => "error", "message" => "Incorrect password"]);
    }
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Treasure Hunt Levels</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: url('https://source.unsplash.com/1600x900/?treasure,map') no-repeat center center/cover;
            text-align: center;
            color: white;
            margin: 0;
            padding: 0;
        }
        .game-container {
            max-width: 600px;
            margin: 50px auto;
            background: rgba(0, 0, 0, 0.7);
            padding: 20px;
            border-radius: 15px;
            box-shadow: 0px 0px 15px rgba(255, 215, 0, 0.6);
        }
        h1 {
            color: #FFD700;
            text-shadow: 2px 2px 10px black;
        }
        .level-btn {
            width: 80%;
            padding: 15px;
            margin: 10px;
            font-size: 18px;
            font-weight: bold;
            border: none;
            border-radius: 8px;
            transition: 0.3s;
            display: block;
            text-decoration: none;
            color: white;
        }
        .unlocked {
            background: #4CAF50;
            cursor: pointer;
        }
        .unlocked:hover {
            background: #45a049;
            box-shadow: 0px 0px 10px rgba(72, 239, 128, 0.9);
        }
        .locked {
            background: gray;
            cursor: not-allowed;
        }
        .completed {
            background: darkgray;
            cursor: pointer;
        }
        .disabled {
            background: red;
            cursor: not-allowed;
        }
    </style>
    <script>
        function unlockLevel(level, status) {
            if (status === "locked") {
                alert("You must complete the previous level first!");
                return;
            }
            if (status === "completed") {
                window.location.href = "game/level" + level + "/index.php";
                return;
            }

            let password = prompt("Enter the password for Level " + level + ":");
            if (password !== null) {
                fetch("game_start.php", {
                    method: "POST",
                    headers: { "Content-Type": "application/x-www-form-urlencoded" },
                    body: "level=" + level + "&password=" + encodeURIComponent(password)
                })
                .then(response => response.json())
                .then(data => {
                    if (data.status === "success") {
                        document.querySelector(".level-btn[data-level='" + level + "']").disabled = true;
                        window.location.href = data.redirect;
                    } else {
                        alert(data.message);
                    }
                });
            }
        }
    </script>
</head>
<body>

    <div class="game-container">
        <h1>🏆 Treasure Hunt</h1>
        <h3>Only one level can be unlocked at a time.</h3>

        <?php
        for ($level = 1; $level <= 5; $level++) {
            $status = $levels[$level] ?? "locked";
            $btnClass = ($status === "locked") ? "locked" : (($status === "completed") ? "completed" : "unlocked");
            $disabled = ($status !== "unlocked") ? "disabled" : "";

            echo "<button class='level-btn $btnClass' data-level='$level' onclick='unlockLevel($level, \"$status\")' $disabled>Level $level</button>";
        }
        ?>
    </div>

    <div style="margin-top: 20px;">
        <a href="../logout.php" style="color: #FFD700; text-decoration: none; font-size: 18px;">Logout</a>
    </div>

</body>
</html>
