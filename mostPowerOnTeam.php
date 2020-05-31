<?php
include 'DBConnection.php';

// Create connection
$db = new BaseballDB();

$team = $_POST['team'];
$year = $_POST['year'];

// Result
$result = $db->most_power_on_team($team, $year);

$db->conn->close();
?>