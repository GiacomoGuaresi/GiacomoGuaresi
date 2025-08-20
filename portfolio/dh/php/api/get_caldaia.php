<?php 
    include_once("../connection.php");
    $sql = "SELECT * FROM  `currentVariable` where `Key` = 'Caldaia'";
    $res = $mysqli->query($sql);
    $row = $res->fetch_assoc();
    $jsonObj[$row["Key"]] = $row["Value"]; 
    echo json_encode($jsonObj);
?>