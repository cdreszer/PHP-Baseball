<?php
include 'DBConnection.php';

// Create connection
$db = new BaseballDB();

$team = $_POST['team'];
$year = $_POST['year'];
$yearTo = $_POST['yearTo'];
$isHitter = $_POST['isHitter'];
$isSimple = $_POST['isSimple'];

$categories = $_POST['categories'];

// Get categories from checkboxes
//$categories = $db->get_categories($power, $speed, $contact, $eye, $fantasy);

// Result
$result = $db->get_preferred_player_from_cats($team, $year, $yearTo, $categories, $isHitter, $isSimple);

$db->conn->close();
?>