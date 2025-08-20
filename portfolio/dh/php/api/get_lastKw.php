<?php 
    include_once("../connection.php");

    $entityBody = file_get_contents('php://input');
    $entityBody = json_decode($entityBody);

    
    //$file = file("../dataStored/".date("Y")."_kwInUso.txt");
    // var_dump($entityBody->data);
   

    $fp = fopen("../dataStored/".date("Y")."_kwInUso.txt", 'r');
    $pos = -2; // Skip final new line character (Set to -1 if not present)
    $lines = array();
    $currentLine = '';


    $labels = array();
    $data = array();


    while (-1 !== fseek($fp, $pos, SEEK_END)) {
        $char = fgetc($fp);
        if (PHP_EOL == $char) {
          $tmp = explode(' ',$currentLine);
          if($tmp[0] > $_GET["lastDate"])
          {               
            $labels[] = $tmp[0];
            $data[] = $tmp[1];
            
            $currentLine = '';
          }
          else 
            break;
        } else {
                $currentLine = $char . $currentLine;
        }
        $pos--;



    }
    $output["labels"] = array_reverse($labels); 
    $output["data"] = array_reverse($data); 

    echo json_encode($output);

        
?>