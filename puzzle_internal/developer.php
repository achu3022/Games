<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Find the Developer Name (Secure)</title>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/crypto-js/4.1.1/crypto-js.min.js"></script>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #121212;
            color: white;
            text-align: center;
            padding: 50px;
        }
        h1 { font-size: 2.5rem; }
        #scrambled {
            font-size: 2rem;
            letter-spacing: 5px;
            font-weight: bold;
            background-color: #222;
            padding: 10px;
            border-radius: 10px;
            display: inline-block;
        }
        input, button {
            padding: 10px;
            font-size: 18px;
            border: none;
            border-radius: 5px;
            margin-top: 10px;
        }
        button {
            background-color: #4CAF50;
            color: white;
            cursor: pointer;
        }
        button:disabled {
            background-color: #888;
            cursor: not-allowed;
        }
        #message { margin-top: 20px; font-size: 20px; }
        #timer {
            font-size: 22px;
            color: #ff3333;
            margin-top: 15px;
        }
        .correct { color: #4CAF50; }
        .wrong { color: #FF5733; }
    </style>
</head>
<body>

<h1>Find the Developer Name</h1>
<p>Unscramble the name:</p>
<div id="scrambled"></div>
<p><input type="text" id="developerName" placeholder="Type Developer Name"></p>
<button id="submitBtn" onclick="checkName()">Submit</button>

<p id="timer">⏳ Time Left: <span id="time">45</span> seconds</p>
<p id="message"></p>

<script>
    const correctName = "abhijith somasekharan";
    const encryptedName = CryptoJS.SHA256(correctName).toString(); // Hash of the correct name
    let timeLeft = 45;
    let timerInterval;

    // Scramble the correct name (changes every reload)
    function scrambleName(name) {
        return name.split('').sort(() => Math.random() - 0.5).join('');
    }

    // Display scrambled name
    document.getElementById('scrambled').innerText = scrambleName(correctName);

    // Start Timer
    function startTimer() {
        timerInterval = setInterval(() => {
            timeLeft--;
            document.getElementById('time').innerText = timeLeft;

            if (timeLeft <= 0) {
                clearInterval(timerInterval);
                disableInput();
                document.getElementById('message').innerHTML = "💀 Time's up! Game Over!";
                document.getElementById('message').className = "wrong";
            }
        }, 1000);
    }

    // Check User Input
    function checkName() {
        let inputName = document.getElementById('developerName').value.trim().toLowerCase();
        let hashedInput = CryptoJS.SHA256(inputName).toString(); // Hash user input

        if (hashedInput === encryptedName) {
            clearInterval(timerInterval);
            document.getElementById('message').innerHTML = "🎉 Correct! You found the Developer Name ✅";
            document.getElementById('message').className = "correct";
            disableInput();
        } else {
            document.getElementById('message').innerHTML = "❌ Incorrect! Try Again.";
            document.getElementById('message').className = "wrong";
        }
    }

    // Disable Input and Button
    function disableInput() {
        document.getElementById('developerName').disabled = true;
        document.getElementById('submitBtn').disabled = true;
    }

    // Start the game
    startTimer();
</script>

</body>
</html>
