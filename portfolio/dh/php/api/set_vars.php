<?php 
    include_once("../connection.php");
    
    $entityBody = file_get_contents('php://input');

    // $file = 'apiLog.txt';
    // $current = file_get_contents($file);
    // $current .= $entityBody."\n";
    // file_put_contents($file, $current);

    $entityBody = json_decode($entityBody);

    
    if($entityBody->head->user == "EPS8266" && $entityBody->head->password == "Password123"){
        foreach ($entityBody->data as $key => $value) {   


            // Aggiorna il valore nel db
            $sql = "UPDATE  `currentVariable` SET  `Value` =  '$value' WHERE  `currentVariable`.`Key` ='$key';";
            $mysqli->query($sql);

            if(($key == "kwInUso")||($key == "Umidita")||($key == "Temperatura")){
                
                if($key == "kwInUso"){
                    if(($value/GetKeyValue("KwMax",$mysqli)*100 > GetKeyValue("percAllarmeKw",$mysqli)) && GetKeyValue("InAllarmeKw",$mysqli) == 0){
                        $sql = "UPDATE  `currentVariable` SET  `Value` =  1 WHERE  `currentVariable`.`Key` ='InAllarmeKw';";
                        $mysqli->query($sql);
    
                        $sql = "INSERT INTO `Sql1230675_2`.`Events` (`ID`, `nome`, `tipo`, `descrizione`, `ack`, `data`) VALUES (NULL, 'Superamento Soglia kw', '2', 'è stata superata la soglia impostata', false, NOW());";
                        $res = $mysqli->query($sql);
                    }
                    else {
                        $sql = "UPDATE  `currentVariable` SET  `Value` = 0 WHERE  `currentVariable`.`Key` ='InAllarmeKw';";
                        $mysqli->query($sql);
                    }
                }
                
                // Aggiorna scrive record su file 
                $file = "../dataStored/".date("Y")."_".$key.".txt";
                if (!file_exists($file)) {
                    $myfile = fopen($file, "w") or die("Unable to open file!");
                    fclose($myfile);    
    
                    $fp = fopen($file, 'a');//opens file in append mode  
                    $txt = Time()." ".$value."\n";    
                    fwrite($fp, $txt);  
                    fclose($fp);  
                }
                else{
                    $line = '';
                    
                    $f = fopen($file, 'r');
                    $cursor = -1;
    
                    fseek($f, $cursor, SEEK_END);
                    $char = fgetc($f);
    
                    while ($char === "\n" || $char === "\r") {
                        fseek($f, $cursor--, SEEK_END);
                        $char = fgetc($f);
                    }
    
                    while ($char !== false && $char !== "\n" && $char !== "\r") {
                        $line = $char . $line;
                        fseek($f, $cursor--, SEEK_END);
                        $char = fgetc($f);
                    }
    
                    $line = explode ( " " , $line )[0];
                    if(time()-$line > 60){
                        $fp = fopen($file, 'a');//opens file in append mode  
                        $txt = Time()." ".$value."\n";    
                        fwrite($fp, $txt);  
                        fclose($fp);  
                    }
                } 
            }            
        }
    
        
        $key = "regDati_".$entityBody->head->agent;
        $sql = "UPDATE  `currentString` SET  `Value` =  '".time()."' WHERE  `Key` ='$key';";
        $mysqli->query($sql);

        echo("okay");
    }
    else 
    {
        echo("unautorized");
    }
    

    function GetKeyValue($key,$mysqli){
        $sql = "SELECT Value from `currentVariable` WHERE  `currentVariable`.`Key` ='$key';";
        $res = $mysqli->query($sql);
        $row = $res->fetch_assoc(); 
        return $row["Value"];
    }
    
?>