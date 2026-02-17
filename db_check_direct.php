<?php
$host = '127.0.0.1';
$dbname = 'pos';
$user = 'root';
$pass = '';

try {
    $dsn = "mysql:host=$host;dbname=$dbname";
    $con = new PDO($dsn, $user, $pass);
    $con->exec("set names utf8");
    echo "Conexión exitosa a 127.0.0.1";
} catch (PDOException $e) {
    echo "Error conectando a 127.0.0.1: " . $e->getMessage();
}
?>