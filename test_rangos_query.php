<?php
$pdo = new PDO('mysql:host=localhost;dbname=pos', 'root', '');
$stmt = $pdo->query('SELECT * FROM factus_rangos');
print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
$stmt = $pdo->query('SELECT * FROM factus_config');
print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
