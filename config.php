<?php
    $host = '127.0.0.1';    
    $user = 'root';
    $password = '';
    $db = 'db_Cinema';
    $con = mysqli_connect($host, $user, $password, $db);

    if(!$con){
        die(mysqli_error());
    }
?>