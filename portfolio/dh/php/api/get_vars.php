<?php 
    include_once("../connection.php");
    $sql = "SELECT * FROM  `currentVariable`";
    $res = $mysqli->query($sql);
    
    while($row = $res->fetch_assoc()){
        $jsonObj[$row["Key"]] = $row["Value"]; 
    }

    $sql = "SELECT * FROM  `currentString`";
    $res = $mysqli->query($sql);
    
    while($row = $res->fetch_assoc()){
        $jsonObj[$row["Key"]] = $row["Value"]; 
    }

    echo json_encode($jsonObj);
      
?>