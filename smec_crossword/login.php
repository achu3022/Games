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
    <style>
        body {
            font-family: Arial, sans-serif;
            text-align: center;
            background: #f4f4f4;
            margin: 0;
            padding: 0;
        }
        .login-container {
            width: 350px;
            margin: 10% auto;
            padding: 20px;
            background: white;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
            border-radius: 10px;
        }
        h2 {
            margin-bottom: 20px;
            color: #333;
        }
        input {
            display: block;
            width: 90%;
            padding: 10px;
            margin: 10px auto;
            border: 1px solid #ccc;
            border-radius: 5px;
            font-size: 16px;
        }
        .info {
            text-align: left;
            font-size: 16px;
            color: #333;
            margin: 10px 0;
            padding: 8px;
            border-bottom: 1px solid #ddd;
        }
        .error {
            color: red;
            font-size: 14px;
        }
        #next-btn {
            display: none;
            padding: 10px 20px;
            background: linear-gradient(135deg, #314755, #26a0da);
            color: white;
            border: none;
            cursor: pointer;
            font-size: 16px;
            border-radius: 5px;
            width: 100%;
            transition: 0.3s;
        }
        #next-btn:hover {
            background: linear-gradient(135deg, #26a0da, #314755);
        }
    </style>
</head>
<body>
    <div class="login-container">
        <h2>Employee Login</h2>
        <input type="text" id="emp_id" placeholder="Enter Employee ID">
        <p id="error-msg" class="error"></p>

        <div id="user-info" style="display: none;">
            <div class="info"><strong>Name:</strong> <span id="name"></span></div>
            <div class="info"><strong>Phone:</strong> <span id="phone"></span></div>
            <div class="info"><strong>Designation:</strong> <span id="department"></span></div>
        </div>

        <button id="next-btn" onclick="proceed()">Next</button>
    </div>

    <script>
        $(document).ready(function() {
            $("#emp_id").on("input", function() {
                let emp_id = $(this).val();
                if (emp_id.length > 5) {  
                    $.ajax({
                        url: "fetch_employee.php",
                        method: "POST",
                        data: { emp_id: emp_id },
                        dataType: "json",
                        success: function(response) {
                            if (response.status === "success") {
                                $("#name").text(response.name);
                                $("#phone").text(response.phone);
                                $("#department").text(response.department);
                                $("#error-msg").text("");
                                $("#user-info").slideDown();
                                $("#next-btn").fadeIn();
                            } else {
                                $("#name, #phone, #department").text("");
                                $("#user-info").slideUp();
                                $("#error-msg").text("No user found!").css("color", "red");
                                $("#next-btn").fadeOut();
                            }
                        }
                    });
                } else {
                    $("#name, #phone, #department").text("");
                    $("#user-info").slideUp();
                    $("#error-msg").text("");
                    $("#next-btn").fadeOut();
                }
            });
        });

        function proceed() {
            let emp_id = $("#emp_id").val();
            window.location.href = "game.php?emp_id=" + emp_id;
        }
    </script>
</body>
</html>
