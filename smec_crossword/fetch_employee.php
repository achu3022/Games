<?php
include "config.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $emp_id = $_POST['emp_id'];
    $query = "SELECT name, phone, department FROM staffs WHERE emp_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $emp_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($row = $result->fetch_assoc()) {
        echo json_encode(["status" => "success", "name" => $row["name"], "phone" => $row["phone"], "department" => $row["department"]]);
    } else {
        echo json_encode(["status" => "error"]);
    }
}
?>
