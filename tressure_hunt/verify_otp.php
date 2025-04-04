<?php
session_start();
include "config.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if ($_SESSION["otp"] == $_POST['otp'] && $_SESSION["emp_id"] == $_POST['emp_id']) {
        $query = "UPDATE staffs SET is_verified = 1 WHERE emp_id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("s", $_SESSION["emp_id"]);
        $stmt->execute();
        echo json_encode(["status" => "verified"]);
    } else {
        echo json_encode(["status" => "error", "message" => "Invalid OTP"]);
    }
}
?>
