<?php
include "config.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $emp_id = trim($_POST['emp_id']);

    if (empty($emp_id)) {
        echo json_encode(["status" => "error", "message" => "Employee ID is empty"]);
        exit;
    }

    $query = "SELECT name, phone, department, is_verified, email FROM staffs WHERE emp_id = ?";
    $stmt = $conn->prepare($query);

    if (!$stmt) {
        echo json_encode(["status" => "error", "message" => "Query preparation failed"]);
        exit;
    }

    $stmt->bind_param("s", $emp_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
        echo json_encode([
            "status" => "success",
            "name" => $row["name"],
            "phone" => $row["phone"],
            "department" => $row["department"],
            "email" => $row["email"],
            "is_verified" => $row["is_verified"]
        ]);
    } else {
        echo json_encode(["status" => "error", "message" => "No user found!"]);
    }
}
?>
