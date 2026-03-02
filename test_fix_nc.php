<?php
require_once "controladores/plantilla.controlador.php";
require_once "modelos/conexion.php";
Conexion::conectar()->exec("UPDATE notas_credito SET numero_nota_credito = 'NC33' WHERE id = 37");
echo "Updated";
?>