<?php
require_once __DIR__ . "/../extensiones/tcpdf/pdf/phpqrcode/qrlib.php";

$data = isset($_GET["data"]) ? $_GET["data"] : (isset($_GET["texto"]) ? $_GET["texto"] : "");
if (empty($data)) {
    $data = "https://kontrolpos.com";
}

// Si se solicita en formato base64 JSON
if (isset($_GET["formato"]) && $_GET["formato"] == "base64") {
    ob_start();
    QRcode::png($data, false, QR_ECLEVEL_M, 6, 2);
    $png = ob_get_clean();
    header("Content-Type: application/json; charset=utf-8");
    echo json_encode(["status" => "ok", "base64" => "data:image/png;base64," . base64_encode($png)]);
    exit();
}

header("Content-Type: image/png");
header("Cache-Control: max-age=86400, public");
QRcode::png($data, false, QR_ECLEVEL_M, 6, 2);
exit();
