<?php
include 'DBConnection.php';

// Create connection
$db = new BaseballDB();

$team = $_POST['team'];
$year = $_POST['year'];
$yearTo = $_POST['yearTo'];

// Category preferences (true / false)
$power = $_POST['power'];
$speed = $_POST['speed'];
$contact = $_POST['contact'];
$eye = $_POST['eye'];
$fantasy = $_POST['fantasy'];

// Get categories from checkboxes
$categories = $db->get_categories($power, $speed, $contact, $eye, $fantasy);

// Result
$result = $db->get_preferred_player_from_cats($team, $year, $yearTo, $categories);

$db->conn->close();
?>