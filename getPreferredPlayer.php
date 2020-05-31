<?php
include 'DBConnection.php';

// Create connection
$db = new BaseballDB();

$team = $_POST['team'];
$year = $_POST['year'];

// Category preferences (true / false)
$power = $_POST['power'];
$speed = $_POST['speed'];
$contact = $_POST['contact'];
$eye = $_POST['eye'];

// Result
$result = $db->get_preferred_player($team, $year, $power, $contact, $speed, $eye);

$db->conn->close();
?>