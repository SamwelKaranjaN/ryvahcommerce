<?php
$servername = "localhost";
$username = "root";
$password = "@X6js1488";
$dbname = "ryvahcommerce";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}