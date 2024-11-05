<?php
$servername = "localhost";
$username = "root";   
$password = ""; 
$dbname = "auction"; 

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("fail to connect: " . $conn->connect_error);
}
?>
