<?php
session_start();
include 'config.php';

if (!isset($_SESSION["user_id"])) {
    header("Location: index.php");
    exit();
}

$user_id = $_SESSION["user_id"];

// Fetch the user's name from the `users` table
$query = $conn->prepare("SELECT name FROM users WHERE id = ?");
$query->bind_param("i", $user_id);
$query->execute();
$result = $query->get_result();
$user = $result->fetch_assoc();

if (!$user) {
    echo "User not found!";
    exit();
}

$name = $user["name"];

//code for fetch the next level password if user win this task 
$query = $conn->prepare("SELECT passcode FROM passwords WHERE user_id = ? AND level = 2"); 
$query->bind_param("i", $user_id);
$query->execute();
$result = $query->get_result();
if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $password = htmlspecialchars($row['passcode']);
} else {
    $password = "No password found for the next level.";
}
//fetch only user status


// Fetch the game status and passcode for the user
$query = $conn->prepare("
    SELECT s.played, s.status, p.passcode 
    FROM scores s 
    LEFT JOIN passwords p ON s.user_id = p.user_id 
    WHERE s.user_id = ? && p.level = 3
    LIMIT 1
");

$query->bind_param("i", $user_id);
$query->execute();
$result = $query->get_result();
$row = $result->fetch_assoc();

if ($row && intval($row["played"]) === 1) {
    $status = htmlspecialchars($row["status"] ?? "Unknown Status"); // Prevent XSS
    $passcode = isset($row["passcode"]) ? htmlspecialchars($row["passcode"]) : ""; // Prevent XSS

    echo '
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Game Over</title>
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
        <style>
            * {
                margin: 0;
                padding: 0;
                box-sizing: border-box;
                font-family: Arial, sans-serif;
            }
            body {
                display: flex;
                justify-content: center;
                align-items: center;
                height: 100vh;
                background-color: #f8f9fa;
                text-align: center;
            }
            .container {
                display: flex;
                align-items: center;
                justify-content: center;
                background: #fff;
                padding: 20px;
                border-radius: 10px;
                box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.2);
                max-width: 600px;
                width: 90%;
                height: auto;
            }
            .image-section {
                flex: 1;
                padding: 10px;
            }
            .image-section img {
                width: 100px;
                height: auto;
            }
            .message-section {
                flex: 2;
                text-align: left;
                padding-left: 20px;
            }
            .message-section h2 {
                color: red;
                font-size: 20px;
                margin-bottom: 10px;
            }
            .message-section p {
                font-size: 14px;
                color: #333;
            }
            .footer {
                position: fixed;
                bottom: 0;
                width: 100%;
                height: 40px;
                background: linear-gradient(135deg,#314755,#26a0da);
                text-align: center;
                padding: 5px 0;
                font-size: 14px;
                color: white;
                box-shadow: 0 -2px 10px rgba(0, 0, 0, 0.2);
            }
            .footer i {
                color: red;
                margin-left: 5px;
                animation: heartbeat 1.5s infinite;
            }
            @keyframes heartbeat {
                0% { transform: scale(1); }
                50% { transform: scale(1.3); }
                100% { transform: scale(1); }
            }
        </style>
    </head>
    <body>
        <div class="container">
            
            <div class="message-section">
                <h2>⚠ You have already played this level</h2>
                <p>Your status: <strong>' . $status . '</strong></p>';

                if ($status !== "win") {
                    echo '<p>Next Level Passcode: <strong>' . $passcode . '</strong></p>';
                }

    echo '      <p>You will be redirected to the main page shortly.</p>
            </div>
        </div>
        <script>
            setTimeout(function() {
                window.location.href = "../../game_start.php";
            }, 10000); // Redirect in 10 seconds
        </script>
    </body>
    </html>';
    
    exit();
}



// If session does not have `played`, initialize it
if (!isset($_SESSION["played"]) || $_SESSION["played"] === false) {
    $moves = 0; // Default moves
    $status = "Failed"; // Default status
    $played = 1; // Mark as played

    $stmt = $conn->prepare("INSERT INTO scores (user_id, moves, completion_time, status, played) 
                            VALUES (?, ?, NOW(), ?, ?)
                            ON DUPLICATE KEY UPDATE played = 1, status = 'Failed', completion_time = NOW()");
    $stmt->bind_param("iisi", $user_id, $moves, $status, $played);
    $stmt->execute();

    $_SESSION["played"] = true;
}
?>

<input type="hidden" id="user_id" value="<?php echo htmlspecialchars($user_id); ?>">

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sliding Puzzle</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

    <style>
        @import url('https://fonts.googleapis.com/css2?family=Ubuntu&display=swap');

        body {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Ubuntu', sans-serif;
            background-color: rgb(246, 246, 195);
            color: white;
        }

        span {
            font-family: 'Ubuntu', sans-serif;
        }

        .board-container {
            display: grid;
            place-items: center;
            justify-content: center;
            height: 100vh;
            width: 100vw;
        }



        .sidebar {
            width: 250px;
            height: 100vh;
            background-color: rgb(159, 190, 159);
        }

        table td {
            text-align: center;
            vertical-align: middle;
            padding: 10px 0;
        }

        .number-switch {
            display: flex;
            align-items: center;
            justify-content: space-around;
        }

        .board {
            width: 500px;
            height: 500px;
            margin: auto;
            position: relative;
            border-style: solid;
            border-width: 7px;
            border-color: rgb(80, 139, 80);
        }

        .tile {

            display: grid;
            place-items: center;
        }

        .number {
            /* opacity: 0; */
            visibility: hidden;
            color: rgb(255, 255, 255);
            font-size: 3rem;
            -webkit-text-stroke-width: 1px;
            -webkit-text-stroke-color: rgb(80, 139, 80);
        }

        .switch {
            position: relative;
            display: inline-block;
            width: 48px;
            height: 24px;
        }

        /* Hide default HTML checkbox */
        .switch input {
            opacity: 0;
            width: 0;
            height: 0;
        }

        /* The slider */
        .slider {
            position: absolute;
            cursor: pointer;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: #ccc;
            -webkit-transition: .3s;
            transition: .3s;
        }

        .slider:before {
            position: absolute;
            content: "";
            height: 16px;
            width: 16px;
            left: 4px;
            bottom: 4px;
            background-color: white;
            -webkit-transition: .3s;
            transition: .3s;
        }

        input:checked+.slider {
            background-color: rgb(80, 139, 80);
        }

        input:checked+p {
            color: black;
            background-color: aqua;
        }

        input:focus+.slider {
            box-shadow: 0 0 1px rgb(80, 139, 80);
        }

        input:checked+.slider:before {
            -webkit-transform: translateX(26px);
            -ms-transform: translateX(26px);
            transform: translateX(23px);
        }

        /* Rounded sliders */
        .slider.round {
            border-radius: 34px;
        }

        .slider.round:before {
            border-radius: 50%;
        }

        .button {
            font-size: 16px;
            border: none;
            background: none;
            color: white;
            background-color: rgb(80, 139, 80);
            border-radius: 9px;
            cursor: pointer;
            width: 100%;
            padding: 12px 28px;
        }

        button:disabled {
            cursor: not-allowed;
        }

        .large {
            padding: 18px 44px;
            width: fit-content;

        }

        .button:hover {
            background-color: rgb(94, 160, 94);
        }

        .move {
            border-radius: 9px;
            padding: 5px;
            width: 50px;
            background-color: rgb(53, 92, 53);
        }

        @media screen and (max-width: 760px) {
            .container {
                flex-direction: column-reverse;
            }

            .sidebar {
                width: 100%;
                height: 400px;
            }
        }
        #timer-container {
    position: absolute;
    top: 20px;
    left: 50%;
    transform: translateX(-50%);
    display: flex;
    align-items: center;
    justify-content: center;
    background: #2c3e50;
    color: white;
    font-size: 24px;
    font-weight: bold;
    padding: 12px 20px;
    border-radius: 10px;
    box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.2);
    gap: 10px;
}

#timer-icon {
    font-size: 28px;
}
.footer {
          position: fixed;
          bottom: 0;
          width: 100%;
          height: 40px;
          background: linear-gradient(135deg,#314755,#26a0da);
          text-align: center;
          padding: 5px 0;
          font-size: 14px;
          
          color: white;
          box-shadow: 0 -2px 10px rgba(0, 0, 0, 0.2);
      }
      .footer i {
    color: red;
    margin-left: 5px;
    animation: heartbeat 1.5s infinite;
}

@keyframes heartbeat {
    0% { transform: scale(1); }
    50% { transform: scale(1.3); }
    100% { transform: scale(1); }
}

    </style>
</head>

<body>
  

    <div class="container" style="display: flex;">
      
            <div style="display:none;position:fixed;">
                <img id="art" src="" alt="puzzle image" /><a id="download"></a>
                <input id="img_file" type="file" accept="image/png,image/gif,image/jpeg,image/webp" />
            </div>
     
        <div class="board-container">
            <div id="board" class="board">
                <!--populated by divs-->
            </div>
        </div>

        <div class="image">
           
        </div>
        
        <div id="timer-container">
        <?php echo "<h3>Welcome, $name</h3><br>"; ?>
            <span id="timer-icon">⏳</span>
            <span id="timer" style="color:red;">10:00</span>
        </div>
    </div>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"
        integrity="sha512-v2CJ7UaYy4JwqLDIrZUI/4hqeoQieOmAZNXBeQyjo21dadnwR+8ZaIJVT8EE2iyI61OV8e6M8PP2/4hpQINQ/g=="
        crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    <script src="./FastPriorityQueue.js"></script>
    <script src="./boardstate.js"></script>
    <script src="./solver.js"></script>
    <script src="./script.js"></script>
    <script>
    var userId = "<?php echo $_SESSION['user_id']; ?>";
</script>

    <script>
        let uploaded_image = null; //stores the previously uploaed image

        var img_file = $('#img_file'), img = $("#art"), size_el = $("#size"), checkbox_el = $("#checkbox"), file, url
        img_file.on('change', configure);
        size_el.on('change', configure);

        checkbox_el.on('click', check)
        function check() {
            $('#checkbox').is(":checked") ? $(".number").css("visibility", "visible") : $(".number").css("visibility", "hidden")
        }

        function configure() {
            clearAllAnimation()
            if (!img_file.prop('files')[0] && uploaded_image) return;
            file = img_file.prop('files')[0] ? img_file.prop('files')[0] : uploaded_image;
            uploaded_image = file;
            url = file ? window.URL.createObjectURL(file) : uploaded_image ? window.URL.createObjectURL(uploaded_image) : null;
            size = size_el.find(":selected").val();
            img.src = url ?? DEFAULT_IMAGE;
            updateBoard()
            check()
            $('#solve-btn').prop('disabled', size > 3 ? true : false)
        }

        function startTimer(duration, display) {
        let timer = duration, minutes, seconds;
        let countdown = setInterval(function () {
            minutes = Math.floor(timer / 60);
            seconds = timer % 60;

            // Add leading zero if seconds < 10
            seconds = seconds < 10 ? "0" + seconds : seconds;

            display.textContent = minutes + ":" + seconds;

            if (--timer < 0) {
                clearInterval(countdown);
                alert("Time is up! You will be logged out.");
                window.location.href = "../../game_start.php"; // Change this to your homepage
            }
        }, 1000);
    }

    window.onload = function () {
        let display = document.getElementById("timer");
        startTimer(600, display); // 600 seconds = 10 minutes
    };




    </script>


</body>

</html>