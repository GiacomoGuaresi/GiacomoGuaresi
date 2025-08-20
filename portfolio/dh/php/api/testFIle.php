<?php
// SCRIVI FILE TEST 
$file = "../dataStored/".date("Y")."_kwInUso.txt";
if (file_exists($file)) {
    unlink($file);
}
 
$myfile = fopen($file, "w") or die("Unable to open file!");


$lastRnd = rand(0,5);

for($c = 525600; $c > 0; $c--){
    $lastRnd = $lastRnd + rand(-1,1);
    $txt = (Time()-($c*60))." ".purebell(0,5,5,0.01)."\n";    
    fwrite($myfile, $txt);    
}

fclose($myfile);


function purebell($min,$max,$std_deviation,$step=1) {
    $rand1 = (float)mt_rand()/(float)mt_getrandmax();
    $rand2 = (float)mt_rand()/(float)mt_getrandmax();
    $gaussian_number = sqrt(-2 * log($rand1)) * cos(2 * M_PI * $rand2);
    $mean = ($max + $min) / 2;
    $random_number = ($gaussian_number * $std_deviation) + $mean;
    $random_number = round($random_number / $step) * $step;
    if($random_number < $min || $random_number > $max) {
      $random_number = purebell($min, $max,$std_deviation);
    }
    return $random_number;
  }


// APPENDI A FILE ESISTENTE
// $fp = fopen('2020.txt', 'a');//opens file in append mode  
// $txt = (Time())." ".rand(0,5000)."\n";    
// fwrite($fp, $txt);  
// fclose($fp);  



// CREA FILE ARCHIVIO 
// $file = "../dataStored/".date("Y")."_test.txt";
// if (!file_exists($file)) {
//     $myfile = fopen($file, "w") or die("Unable to open file!");
//     fclose($myfile);    
// }
// $fp = fopen('2020.txt', 'a');//opens file in append mode  
// $txt = (Time())." ".rand(0,5000)."\n";    
// fwrite($fp, $txt);  
// fclose($fp);  


// LEGGI ULTIMA RIGA
// $line = '';
// $file = "../dataStored/".date("Y")."_test.txt";

// $f = fopen($file, 'r');
// $cursor = -1;

// fseek($f, $cursor, SEEK_END);
// $char = fgetc($f);

// /**
//  * Trim trailing newline chars of the file
//  */
// while ($char === "\n" || $char === "\r") {
//     fseek($f, $cursor--, SEEK_END);
//     $char = fgetc($f);
// }

// /**
//  * Read until the start of file or first newline char
//  */
// while ($char !== false && $char !== "\n" && $char !== "\r") {
//     /**
//      * Prepend the new char
//      */
//     $line = $char . $line;
//     fseek($f, $cursor--, SEEK_END);
//     $char = fgetc($f);
// }

// $line = explode ( " " , $line )[0];


// echo time() - $line;

?>