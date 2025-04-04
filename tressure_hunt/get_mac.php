<?php
session_start();

// Get MAC address (only works on Windows servers)
$mac = exec('getmac');
$mac_address = strtok($mac, ' ');

// Store MAC address in session
$_SESSION['mac_address'] = $mac_address;

echo json_encode(["mac" => $mac_address]);
?>
