<?php
require_once 'controladores/factus.controlador.php';
require_once 'modelos/factus.modelo.php';
require_once 'modelos/conexion.php';

session_start();
$_SESSION['id'] = 14;

$auth = ControladorFactus::ctrAutenticar();
$token = $auth['token'];

// Notas con problemas (ID 1 y 2)
$notasAFijar = [
    ["id" => 1, "ref" => "NA-SEDS984000003-1772082334"],
    ["id" => 2, "ref" => "NA-SEDS984000000-1772082389"]
];

foreach ($notasAFijar as $n) {
    echo "Procesando ID {$n['id']} ... ";

    // Intentar buscar por referencia en Factus
    // Como no hay un endpoint directo de "buscar por referencia", 
    // intentamos ver si el listado general nos da la info o si podemos usar el endpoint de validación (que a veces devuelve el existente)

    $apiUrl = "https://api-sandbox.factus.com.co/v1/adjustment-notes"; // Ajustar si es diferente

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $apiUrl . "?filter[reference_code]=" . $n['ref']);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Authorization: Bearer ' . $token,
        'Accept: application/json'
    ]);

    $res = curl_exec($ch);
    $http = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($http == 200) {
        $json = json_decode($res, true);
        $data = $json['data']['data'] ?? []; // Factus suele paginar

        if (!empty($data)) {
            $item = $data[0];
            $numero = $item['number'] ?? '';
            $cuds = $item['cuds'] ?? '';
            $qr = $item['qr'] ?? '';
            $pdf = $item['public_url'] ?? $item['pdf_url'] ?? '';
            $xml = $item['xml_url'] ?? '';

            if (!empty($numero)) {
                $db = Conexion::conectar();
                $stmt = $db->prepare("UPDATE notas_ajuste_ds SET 
                    numero_nota_ajuste = :num,
                    cuds_ajuste = :cuds,
                    qr_data = :qr,
                    pdf_dian = :pdf,
                    xml_dian = :xml
                    WHERE id = :id");

                $stmt->bindParam(":num", $numero, PDO::PARAM_STR);
                $stmt->bindParam(":cuds", $cuds, PDO::PARAM_STR);
                $stmt->bindParam(":qr", $qr, PDO::PARAM_STR);
                $stmt->bindParam(":pdf", $pdf, PDO::PARAM_STR);
                $stmt->bindParam(":xml", $xml, PDO::PARAM_STR);
                $stmt->bindParam(":id", $n['id'], PDO::PARAM_INT);

                if ($stmt->execute()) {
                    echo "OK ({$numero})\n";
                } else {
                    echo "ERROR BD\n";
                }
            } else {
                echo "No se encontró número en el item\n";
            }
        } else {
            echo "No se encontró el documento en el listado por referencia\n";
        }
    } else {
        echo "Error API ($http)\n";
    }
}
