<?php
    
    //create a mySQL DB connection:
    $conn = new mysqli("127.0.0.1", "root", "", "search_engine"); 

    //testing connection success, die() - equivalent to exit
    if ($conn->connect_error) {
    die('Connect Error (' . $conn->connect_errno . ') '
            . $conn->connect_error);
    }
            
    //echo "Connected successfully";

?>