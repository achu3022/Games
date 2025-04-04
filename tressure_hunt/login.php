<?php
session_start();
include "config.php";
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Employee Login</title>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>
    <div class="login-container">
        <h2>Employee Login</h2>
        <input type="text" id="emp_id" placeholder="Enter Employee ID">
        <p id="error-msg" class="error"></p>

        <div id="user-info" style="display: none;">
            <p><strong>Name:</strong> <span id="name"></span></p>
            <p><strong>Phone:</strong> <span id="phone"></span></p>
            <p><strong>Department:</strong> <span id="department"></span></p>
        </div>

        <div id="verification-section" style="display:none;">
            <p>Verification required. Enter your email:</p>
            <input type="email" id="email-input" placeholder="Enter your email">
            <button id="verify-btn" onclick="sendOTP()">Verify</button>
        </div>

        <div id="otp-section" style="display:none;">
            <input type="text" id="otp-input" placeholder="Enter OTP">
            <button onclick="verifyOTP()">Submit OTP</button>
        </div>

        <button id="next-btn" onclick="proceed()">Next</button>
    </div>

    <script src="assets/script.js"></script>
</body>
</html>
