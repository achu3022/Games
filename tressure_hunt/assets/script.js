
$(document).ready(function() {
    $("#emp_id").on("input", function() {
        let emp_id = $(this).val();
        if (emp_id.length > 5) {
            $.post("fetch_employee.php", { emp_id: emp_id }, function(response) {
                if (response.status === "success") {
                    $("#name").text(response.name);
                    $("#phone").text(response.phone);
                    $("#department").text(response.department);
                    $("#user-info").show();

                    if (response.is_verified == 1) {
                        $("#next-btn").show();
                    } else {
                        $("#verification-section").show();
                    }
                } else {
                    $("#error-msg").text("No user found!").show();
                }
            }, "json");
        }
    });
});
function proceed() {
    let emp_id = $("#emp_id").val();

    if (emp_id.length > 5) {
        fetchIPAddress(emp_id);
    } else {
        alert("Please enter a valid Employee ID");
    }
}

function fetchIPAddress(emp_id) {
    $.getJSON("https://api64.ipify.org?format=json", function (data) {
        let ip = data.ip;

        $.getJSON("get_mac.php", function (macData) {
            let mac = macData.mac;

            // Send data to PHP for validation
            $.post("check_ip_mac.php", { emp_id: emp_id, ip: ip, mac: mac }, function (response) {
                if (response.status === "warning") {
                    alert(response.message);
                } else {
                    window.location.href = "game.php?emp_id=" + emp_id;
                }
            }, "json");
        });
    });
}
