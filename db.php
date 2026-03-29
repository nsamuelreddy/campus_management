<?php
$host = "localhost";
$user = "root";        // change if needed
$password = "";        // your MySQL password
$database = "project";

$conn = new mysqli($host, $user, $password, $database);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
