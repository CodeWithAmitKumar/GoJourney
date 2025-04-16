<?php
// for authentication 

    $server = "localhost";
    $username = "root";
    $password = "";
    $database = "go_journey";

    $conn = mysqli_connect($server, $username, $password , $database);
    if ($conn === false){
        die("ERROR: Could not connect. " . mysqli_connect_error());
    }
   

?>