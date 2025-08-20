<?php 
    require_once("connection.php");

    if(isset($_GET["all"]))
    {
        $sql = "UPDATE  `Sql1230675_2`.`Events` SET  `ack` =  '1' WHERE  `Events`.`ack` = 0";
        $res = $mysqli->query($sql);
    }else{
        $sql = "UPDATE  `Sql1230675_2`.`Events` SET  `ack` =  '1' WHERE  `Events`.`ID` =".$_GET["id"];
        $res = $mysqli->query($sql);
    }
    
    header( "Location: ../index.php" );
    exit; 
    
?>