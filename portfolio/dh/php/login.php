<?php 
    require_once("connection.php");

    $sql = "SELECT * FROM  `users` WHERE  `username` = '".$_POST["username"]."' AND  `password` = MD5('".$_POST["password"]."')";
    $res = $mysqli->query($sql);

    if(mysqli_num_rows($res) == 1){
        $row = $res->fetch_assoc();
        var_dump($row);

        $sql = "INSERT INTO `Sql1230675_2`.`Events` (`ID`, `nome`, `tipo`, `descrizione`, `ack`, `data`) VALUES (NULL, 'Accesso', '0', '".$_POST["username"]." ha eseguito l\'accesso', true, NOW());";
        var_dump($sql);
        $res = $mysqli->query($sql);
        session_start();
        $_SESSION["username"] = $_POST["username"];
        header( "Location: ../index.php" );
        exit ;
    }
    else {
        $sql = "INSERT INTO `Sql1230675_2`.`Events` (`ID`, `nome`, `tipo`, `descrizione`, `ack`, `data`) VALUES (NULL, 'Accesso Negato', '2', '".$_POST["username"]." ha provato ad accedere', false, NOW());";
        var_dump($sql);
        $res = $mysqli->query($sql);
        session_start();
        session_unset();
        session_destroy();
        header( "Location: ../login.php" );
        exit ;
    }

    
?>