<?php
// config.php
$host = 'localhost';
$user = 'root';
$pass = '';
$dbname = 'vgr';

$conn = new mysqli($host, $user, $pass, $dbname);
if ($conn->connect_error) {
    die("Falha na conexão: " . $conn->connect_error);
}
$conn->set_charset("utf8mb4");
