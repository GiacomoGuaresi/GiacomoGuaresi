<?php 
    require_once("connection.php");

    $sql = "SELECT * FROM  `users` WHERE  `pin` = '".$_POST["pin"]."'";
    $res = $mysqli->query($sql);

    if(mysqli_num_rows($res) == 1){
        
        $row = $res->fetch_assoc();
        
        var_dump($row);

        $user = $row["username"]; 
        $sql = "INSERT INTO `Sql1230675_2`.`Events` (`ID`, `nome`, `tipo`, `descrizione`, `ack`, `data`) VALUES (NULL, 'Accesso', '0', '".$user." ha eseguito l\'accesso', true, NOW());";
        var_dump($sql);
        $res = $mysqli->query($sql);
        session_start();
        $_SESSION["username"] = $user;
        header( "Location: ../index.php" );
        exit ;
    }
    else {

        $sql = "INSERT INTO `Sql1230675_2`.`Events` (`ID`, `nome`, `tipo`, `descrizione`, `ack`, `data`) VALUES (NULL, 'Accesso Negato', '2', 'qualcuno ha provato ad accedere attraverso un pin non valido', false, NOW());";
        var_dump($sql);
        $res = $mysqli->query($sql);
        session_start();
        session_unset();
        session_destroy();
        header( "Location: ../login.php" );
        exit ;
    }

    
?>