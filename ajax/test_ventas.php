<?php
$_SERVER['REQUEST_METHOD'] = 'POST';
$_POST['draw'] = 1;
$_POST['start'] = 0;
$_POST['length'] = 10;
// Ignoramos CSRF para esta prueba comentando temporalmente o simulando un token válido
$_SESSION['csrf_token'] = 'test';
$_POST['csrf_token'] = 'test';
require 'datatable-ventas.ajax.php';
