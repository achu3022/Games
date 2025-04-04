<?php
session_start();
if (isset($_POST['timeLeft'])) {
    $_SESSION['timer'] = time() + intval($_POST['timeLeft']);
}
?>
