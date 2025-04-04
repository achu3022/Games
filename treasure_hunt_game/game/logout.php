<?php
include '../config.php';
session_start();
session_destroy();
header("Location: $baseurl");
exit();
?>