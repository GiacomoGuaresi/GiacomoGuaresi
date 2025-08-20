<?php 
    session_start();
    require_once("connection.php");
    if($_SESSION["username"] == NULL){
        header( "Location: login.php" );
        exit ;
    }

    if($_GET["action"] == "mdown"){
        $src = $_GET["id"];
        $dest = $_GET["id"] + 1;
        
        $sql = "UPDATE  `Sql1230675_2`.`pagePart` Left join users on pagePart.user = users.id SET  `orderNumber` =  '999' WHERE `username` = '".$_SESSION["username"]."' AND `pagePart`.`orderNumber` = '$dest'";
        $res = $mysqli->query($sql);
        var_dump($sql); echo("<br>");
        
        $sql = "UPDATE  `Sql1230675_2`.`pagePart` Left join users on pagePart.user = users.id SET  `orderNumber` =  '$dest' WHERE `username` = '".$_SESSION["username"]."' AND `pagePart`.`orderNumber` = '$src'";
        $res = $mysqli->query($sql);
        var_dump($sql); echo("<br>");
    
        $sql = "UPDATE  `Sql1230675_2`.`pagePart` Left join users on pagePart.user = users.id SET  `orderNumber` =  '$src' WHERE `username` = '".$_SESSION["username"]."' AND `pagePart`.`orderNumber` = '999'";
        $res = $mysqli->query($sql);
        var_dump($sql); echo("<br>");
    }
    else if($_GET["action"] == "mup"){
        $src = $_GET["id"];
        $dest = $_GET["id"] - 1;
    
    
        $sql = "UPDATE  `Sql1230675_2`.`pagePart` Left join users on pagePart.user = users.id SET  `orderNumber` =  '999' WHERE `username` = '".$_SESSION["username"]."' AND `pagePart`.`orderNumber` = '$dest'";
        $res = $mysqli->query($sql);
        var_dump($sql); echo("<br>");
        
        $sql = "UPDATE  `Sql1230675_2`.`pagePart` Left join users on pagePart.user = users.id SET  `orderNumber` =  '$dest' WHERE `username` = '".$_SESSION["username"]."' AND `pagePart`.`orderNumber` = '$src'";
        $res = $mysqli->query($sql);
        var_dump($sql); echo("<br>");
    
        $sql = "UPDATE  `Sql1230675_2`.`pagePart` Left join users on pagePart.user = users.id SET  `orderNumber` =  '$src' WHERE `username` = '".$_SESSION["username"]."' AND `pagePart`.`orderNumber` = '999'";
        $res = $mysqli->query($sql);
        var_dump($sql); echo("<br>");
    }
    else if($_GET["action"] == "remove"){
        $src = $_GET["id"];
        
        $sql  = "SELECT * FROM `users` WHERE `username` = '".$_SESSION["username"]."'"; 
        $res = $mysqli->query($sql);
        $row = $res->fetch_assoc();
        $userID = $row["ID"];


        $sql = "DELETE FROM `Sql1230675_2`.`pagePart` WHERE `user` = '".$userID."' AND `pagePart`.`orderNumber` = '$src'";
        $res = $mysqli->query($sql);
        var_dump($sql); echo("<br>");
        
    }
    else if($_GET["action"] == "add"){
        $src = $_GET["id"];
        
        $sql  = "SELECT * FROM `users` WHERE `username` = '".$_SESSION["username"]."'"; 
        $res = $mysqli->query($sql);
        $row = $res->fetch_assoc();
        $userID = $row["ID"];

        $sql  = "SELECT max(orderNumber) as maxOrderNumber FROM `pagePart` WHERE user = ".$userID; 
        $res = $mysqli->query($sql);
        $row = $res->fetch_assoc();
        $maxUserNumber = $row["maxOrderNumber"] + 1;

        
        $sql  = "INSERT INTO `Sql1230675_2`.`pagePart` (`ID`, `user`, `fileName`, `orderNumber`) VALUES (NULL, '".$userID."', '".$src."', '".$maxUserNumber."');";
        $res = $mysqli->query($sql);
        var_dump($sql); echo("<br>");
    }


    echo("<hr>");
    $newID = 1; 
    $sql = "SELECT * FROM `pagePart` Left join users on pagePart.user = users.id WHERE `username` = '".$_SESSION["username"]."' order by `orderNumber`";
    $res = $mysqli->query($sql);
    while($row = $res->fetch_assoc()){
        $sql2 = "UPDATE  `Sql1230675_2`.`pagePart` Left join users on pagePart.user = users.id SET  `orderNumber` =  '".$newID."' WHERE `username` = '".$_SESSION["username"]."' AND `pagePart`.`orderNumber` = '".$row["orderNumber"]."'";
        $res2 = $mysqli->query($sql2);
        var_dump($sql2);
        echo("<br>");
        $newID ++; 
    }



    header( "Location: ../index.php?edit" );
    exit; 
    
?>