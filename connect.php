<?php 
$connection = new mysqli('localhost', 'root', '', 'dbg8java');

if ($connection->connect_error) {
    die("Connection failed: " . $connection->connect_error);
}
?>