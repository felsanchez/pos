<?php
// Test AJAX response
$_POST["accion"] = "mostrarNotasAjusteDSServerSide";
$_POST["draw"] = 1;
$_POST["start"] = 0;
$_POST["length"] = 10;
$_POST["search"] = array("value" => "");
$_POST["order"] = array(array("column" => 4, "dir" => "desc"));

error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once "ajax/factus.ajax.php";
?>
