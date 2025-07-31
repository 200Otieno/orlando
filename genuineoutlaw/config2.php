<?php
require_once __DIR__ . '/config.php';

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die("MySQLi Connection failed: " . $conn->connect_error);
}
?>