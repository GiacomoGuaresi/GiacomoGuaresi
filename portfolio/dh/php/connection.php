<?php
$mysqli = new mysqli("89.46.111.68", "Sql1230675", "77313b804b", "Sql1230675_2");
if ($mysqli->connect_errno) {
    echo "Failed to connect to MySQL: (" . $mysqli->connect_errno . ") " . $mysqli->connect_error;
}
?>