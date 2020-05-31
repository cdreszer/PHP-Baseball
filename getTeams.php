<?php
include 'DBConnection.php';

// Create connection
$db = new BaseballDB();

$yearStart = $_POST['yearStart'];
$yearEnd = $_POST['yearEnd'];

$teams = $db->get_team_names($yearStart, $yearEnd);

echo json_encode($teams);

$db->conn->close();
?>