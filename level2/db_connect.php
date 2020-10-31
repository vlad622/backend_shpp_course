<?php
function OpenConnection()
{
    $dbhost = "localhost";
    $dbuser = "root";
    $dbpass = "";
    $db = "todo_db";
    $connection = new mysqli($dbhost, $dbuser, $dbpass, $db) or die("connectionect failed: %s\n". $connection -> error);

    return $connection;
}
 
function CloseConnection($connection)
{
    $connection -> close();
}
   
?>