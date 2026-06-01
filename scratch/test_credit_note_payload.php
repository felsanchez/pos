<?php
require_once "controladores/factus.controlador.php";
require_once "modelos/factus.modelo.php";
require_once "modelos/conexion.php";

$auth = ControladorFactus::ctrAutenticar();
if ($auth['error']) {
    die("Autenticación fallida: " . $auth['mensaje']);
}
$token = $auth['token'];

$db = Conexion::conectar();
// Buscar venta que tenga factus_bill_id
$stmt = $db->prepare("SELECT * FROM ventas WHERE factus_bill_id IS NOT NULL AND factus_bill_id != '' LIMIT 1");
$stmt->execute();
$venta = $stmt->fetch();

if (!$venta) {
    echo "No se encontró una venta con factus_bill_id real. Usando venta ID 12 con un factus_bill_id simulado para la prueba.\n";
    $stmt = $db->prepare("SELECT * FROM ventas WHERE id = 12");
    $stmt->execute();
    $venta = $stmt->fetch();
    $venta['factus_bill_id'] = '33547'; // ID simulado
    $venta['numero_factura'] = 'SETT12345';
} else {
    echo "Usando venta ID: " . $venta['id'] . " | Factura: " . $venta['numero_factura'] . " | Bill ID: " . $venta['factus_bill_id'] . "\n";
}

// Simularemos 4 tipos de clientes para la Nota de Crédito:
$clientesPrueba = [
    [
        "desc" => "Natural No Responsable",
        "tipo_persona" => "natural",
        "responsabilidades_fiscales" => "R-99-PN",
        "tipo_documento_id" => 3, // Cédula
        "documento" => "1018223344",
        "nombre" => "Luisa Fernanda Garcia",
        "razon_social" => "",
        "nombre_comercial" => "",
        "direccion" => "Calle 10 # 5-40",
        "email" => "luisa@gmail.com",
        "telefono" => "3124567890",
        "digito_verificacion" => ""
    ],
    [
        "desc" => "Natural Responsable (O-23)",
        "tipo_persona" => "natural",
        "responsabilidades_fiscales" => "O-23, R-99-PN",
        "tipo_documento_id" => 6, // NIT
        "documento" => "79998887",
        "nombre" => "Carlos Perez",
        "razon_social" => "",
        "nombre_comercial" => "",
        "direccion" => "Av 19 # 100-30",
        "email" => "carlos@gmail.com",
        "telefono" => "3201234567",
        "digito_verificacion" => "5"
    ],
    [
        "desc" => "Juridica No Responsable (ZY)",
        "tipo_persona" => "juridica",
        "responsabilidades_fiscales" => "ZY",
        "tipo_documento_id" => 6, // NIT
        "documento" => "900555666",
        "nombre" => "Comercializadora SAS",
        "razon_social" => "Comercializadora SAS",
        "nombre_comercial" => "Comercializadora del Sur",
        "direccion" => "Zona Industrial",
        "email" => "contacto@comercializadora.com",
        "telefono" => "6017654321",
        "digito_verificacion" => "4"
    ],
    [
        "desc" => "Juridica Responsable (O-23)",
        "tipo_persona" => "juridica",
        "responsabilidades_fiscales" => "O-23",
        "tipo_documento_id" => 6, // NIT
        "documento" => "901222333",
        "nombre" => "Tecnologia e Innovacion SAS",
        "razon_social" => "Tecnologia e Innovacion SAS",
        "nombre_comercial" => "TecnoIn",
        "direccion" => "Carrera 7 # 71-21",
        "email" => "factura@tecnoin.com",
        "telefono" => "6018889999",
        "digito_verificacion" => "2"
    ]
];

// Guardar cliente ID 3 original para restaurarlo
$stmtOriginal = $db->prepare("SELECT * FROM clientes WHERE id = 3");
$stmtOriginal->execute();
$clienteOriginal = $stmtOriginal->fetch(PDO::FETCH_ASSOC);

if (!$clienteOriginal) {
    die("No se encontró el cliente con ID 3 para realizar la prueba.");
}

try {
    foreach ($clientesPrueba as $i => $cp) {
        echo "\n=== Test NC " . ($i + 1) . ": " . $cp['desc'] . " ===\n";
        
        // Actualizar cliente ID 3 en la BD con los datos del caso de prueba
        $stmtUpdate = $db->prepare("UPDATE clientes SET 
            tipo_persona = :tipo_persona,
            responsabilidades_fiscales = :responsabilidades_fiscales,
            tipo_documento_id = :tipo_documento_id,
            documento = :documento,
            nombre = :nombre,
            razon_social = :razon_social,
            nombre_comercial = :nombre_comercial,
            direccion = :direccion,
            email = :email,
            telefono = :telefono,
            digito_verificacion = :digito_verificacion
            WHERE id = 3");
            
        $stmtUpdate->execute([
            ':tipo_persona' => $cp['tipo_persona'],
            ':responsabilidades_fiscales' => $cp['responsabilidades_fiscales'],
            ':tipo_documento_id' => $cp['tipo_documento_id'],
            ':documento' => $cp['documento'],
            ':nombre' => $cp['nombre'],
            ':razon_social' => $cp['razon_social'],
            ':nombre_comercial' => $cp['nombre_comercial'],
            ':direccion' => $cp['direccion'],
            ':email' => $cp['email'],
            ':telefono' => $cp['telefono'],
            ':digito_verificacion' => $cp['digito_verificacion']
        ]);
        
        // Usar reflexión para llamar al método privado prepararDatosNotaCredito
        $method = new ReflectionMethod('ControladorFactus', 'prepararDatosNotaCredito');
        $method->setAccessible(true);
        $payload = $method->invoke(null, $venta, 2, null, 3, "Anulación de prueba");
        
        if (isset($payload['error'])) {
            echo "Error generando datos: " . $payload['mensaje'] . "\n";
            continue;
        }
        
        echo "Customer Payload Mapeado:\n";
        echo "  legal_organization_id: " . $payload['customer']['legal_organization_id'] . "\n";
        echo "  tribute_id: " . $payload['customer']['tribute_id'] . "\n";
        echo "  dv: " . $payload['customer']['dv'] . "\n";
        echo "  company: \"" . $payload['customer']['company'] . "\"\n";
        echo "  trade_name: \"" . $payload['customer']['trade_name'] . "\"\n";
        echo "  names: \"" . $payload['customer']['names'] . "\"\n";
        echo "  identification_document_id: " . $payload['customer']['identification_document_id'] . "\n";
        echo "  fiscal_responsibilities: " . json_encode($payload['customer']['fiscal_responsibilities']) . "\n";
        
        // Validar contra Factus Sandbox API
        $configFactus = ModeloFactus::mdlObtenerConfiguracion();
        $apiUrl = $configFactus['api_url'] ?? "https://api-sandbox.factus.com.co";
        $url = $apiUrl . '/v1/credit-notes/validate';
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $token,
            'Content-Type: application/json',
            'Accept: application/json'
        ]);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        echo "HTTP CODE: $httpCode\n";
        $res = json_decode($response, true);
        if ($res) {
            echo "Status: " . ($res['status'] ?? 'N/A') . "\n";
            echo "Message: " . ($res['message'] ?? 'N/A') . "\n";
            if (isset($res['errors'])) {
                print_r($res['errors']);
            }
            if (isset($res['data']['errors'])) {
                print_r($res['data']['errors']);
            }
        } else {
            echo "Raw Response: " . substr($response, 0, 300) . "\n";
        }
    }
} finally {
    // Restaurar cliente ID 3 original
    $stmtRestore = $db->prepare("UPDATE clientes SET 
        tipo_persona = :tipo_persona,
        responsabilidades_fiscales = :responsabilidades_fiscales,
        tipo_documento_id = :tipo_documento_id,
        documento = :documento,
        nombre = :nombre,
        razon_social = :razon_social,
        nombre_comercial = :nombre_comercial,
        direccion = :direccion,
        email = :email,
        telefono = :telefono,
        digito_verificacion = :digito_verificacion
        WHERE id = 3");
        
    $stmtRestore->execute([
        ':tipo_persona' => $clienteOriginal['tipo_persona'],
        ':responsabilidades_fiscales' => $clienteOriginal['responsabilidades_fiscales'],
        ':tipo_documento_id' => $clienteOriginal['tipo_documento_id'],
        ':documento' => $clienteOriginal['documento'],
        ':nombre' => $clienteOriginal['nombre'],
        ':razon_social' => $clienteOriginal['razon_social'],
        ':nombre_comercial' => $clienteOriginal['nombre_comercial'],
        ':direccion' => $clienteOriginal['direccion'],
        ':email' => $clienteOriginal['email'],
        ':telefono' => $clienteOriginal['telefono'],
        ':digito_verificacion' => $clienteOriginal['digito_verificacion']
    ]);
    
    echo "\nCliente ID 3 restaurado a su estado original.\n";
}
