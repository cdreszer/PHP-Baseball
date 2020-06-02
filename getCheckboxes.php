<?php
include 'HTMLHelpers.php';

$stats = $_POST['stats'];
$fantasy = $_POST['fantasy'];

HTMLHelpers::populate_checkboxes($stats, $fantasy);
?>