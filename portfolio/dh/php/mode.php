
<?php 
    if (isset($_COOKIE["mode"]))
    {
        if($_COOKIE["mode"] == "dark"){
            $cookie_name = "mode";
            $cookie_value = "light";
            setcookie($cookie_name, $cookie_value,  time() + (10 * 365 * 24 * 60 * 60), "/"); // 86400 = 1 day
        }
        else{
            $cookie_name = "mode";
            $cookie_value = "dark";
            setcookie($cookie_name, $cookie_value,  time() + (10 * 365 * 24 * 60 * 60), "/"); // 86400 = 1 day
        }
    }
    else 
    {
        $cookie_name = "mode";
        $cookie_value = "dark";
        setcookie($cookie_name, $cookie_value,  time() + (10 * 365 * 24 * 60 * 60), "/"); // 86400 = 1 day
    }
    
    header( "Location: ../index.php" );
    exit;    

?>