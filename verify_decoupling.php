<?php
require_once "modelos/conexion.php";
require_once "modelos/ventas.modelo.php";
require_once "modelos/factus.modelo.php";

echo "<h1>Verificación de Desacople de Facturación</h1>";
echo "<pre>";

$codigoTest = 999999;
$tabla = "ventas";

try {
    $db = Conexion::conectar();

    // 1. Limpiar test anterior
    $db->exec("DELETE FROM ventas WHERE codigo = $codigoTest");

    // 2. Insertar Venta (Simulando ctrCrearVenta)
    echo "1. Insertando venta de prueba...\n";
    $datosVenta = array(
        "codigo" => $codigoTest,
        "numero_factura" => null,
        "id_cliente" => 52, // ID cliente válido
        "id_vendedor" => 14, // ID vendedor válido (admin1)
        "productos" => "[]",
        "impuesto" => 0,
        "neto" => 1000,
        "total" => 1000,
        "metodo_pago" => "Efectivo",
        "notas" => "Test Decoupling",
        "estado" => "venta",
        "imagen" => "",
        "fecha" => date('Y-m-d H:i:s'),
        "tipo_descuento" => "",
        "valor_descuento" => 0,
        "monto_descuento" => 0,
        "recibe" => 0,
        "extra" => "",
        "retenciones" => "",
        "resolucion_id" => 0,
        "fecha_vencimiento" => null,
        "orden_compra" => "",
        "forma_pago_dian" => "",
        "metodo_pago_dian_id" => "",
        "estado_dian" => "pendiente",
        "cufe" => "",
        "qr_data" => "",
        "xml_dian" => "",
        "pdf_dian" => "",
        "mensaje_dian" => "",
        "fecha_envio_dian" => null
    );

    $respuesta = ModeloVentas::mdlIngresarVenta($tabla, $datosVenta);
    echo "Resultado Insert: " . $respuesta . "\n";

    // Verificar Insert
    $venta = ModeloVentas::mdlMostrarVentas($tabla, "codigo", $codigoTest);
    echo "Numero Factura (debe ser NULL): " . var_export($venta['numero_factura'], true) . "\n";
    $idVenta = $venta['id'];

    if ($venta['numero_factura'] !== null) {
        throw new Exception("Error: numero_factura debería ser NULL al inicio");
    }

    // 3. Simular Respuesta API (mdlActualizarDatosFactura)
    echo "\n2. Simulando respuesta API (SETT-999)...\n";
    $datosFactura = array(
        "estado_dian" => "Enviado",
        "cufe" => "cufe_test",
        "qr_data" => "qr_test",
        "xml_dian" => "xml_test",
        "pdf_dian" => "pdf_test",
        "mensaje_dian" => "Exito",
        "fecha_envio_dian" => date('Y-m-d H:i:s'),
        "numero_factura" => "SETT-999"
    );

    ModeloFactus::mdlActualizarDatosFactura($idVenta, $datosFactura);

    // Verificar Update Factura
    $venta = ModeloVentas::mdlMostrarVentas($tabla, "codigo", $codigoTest);
    echo "Numero Factura (debe ser SETT-999): " . $venta['numero_factura'] . "\n";

    if ($venta['numero_factura'] !== "SETT-999") {
        throw new Exception("Error: numero_factura no se actualizó correctamente");
    }

    // 4. Simular Edición Venta (mdlEditarVenta) - Preservando numero_factura
    echo "\n3. Simulando edición de venta (manteniendo numero_factura)...\n";
    $datosVentaObj = $datosVenta; // Copiar
    $datosVentaObj["notas"] = "Test Decoupling Editado";
    $datosVentaObj["numero_factura"] = $venta['numero_factura']; // El controlador pasará el valor existente

    $respuestaEdit = ModeloVentas::mdlEditarVenta($tabla, $datosVentaObj);
    echo "Resultado Edit: " . $respuestaEdit . "\n";

    // Verificar Edit
    $venta = ModeloVentas::mdlMostrarVentas($tabla, "codigo", $codigoTest);
    echo "Notas: " . $venta['notas'] . "\n";
    echo "Numero Factura (debe seguir siendo SETT-999): " . $venta['numero_factura'] . "\n";

    if ($venta['numero_factura'] !== "SETT-999") {
        throw new Exception("Error: numero_factura se perdió al editar");
    }

    echo "\nVERIFICACIÓN EXITOSA: Desacople funcionando correctamente.\n";

    // Limpieza final
    //$db->exec("DELETE FROM ventas WHERE codigo = $codigoTest");
    //echo "Limpieza completada.";

} catch (Exception $e) {
    echo "EXCEPCIÓN: " . $e->getMessage();
}

echo "</pre>";
