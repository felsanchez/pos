<?php
require_once "../../modelos/session-manager.php";
SessionManager::startSecure();

require_once "../../controladores/clientes.controlador.php";
require_once "../../modelos/clientes.modelo.php";

if (!isset($_SESSION["iniciarSesion"]) || $_SESSION["iniciarSesion"] != "ok") {
    die("Acceso denegado.");
}

$rangoFiltro = $_GET["rango"] ?? "todos";
$clientes = ControladorClientes::ctrMostrarClientesInactividad();

$nombreArchivo = "Reporte_Inactividad_Clientes_" . date("Ymd_His") . ".xls";

header('Expires: 0');
header('Cache-control: private');
header("Content-type: application/vnd.ms-excel; charset=utf-8");
header("Cache-Control: cache, must-revalidate");
header('Content-Description: File Transfer');
header('Last-Modified: ' . date('D, d M Y H:i:s'));
header("Pragma: public");
header('Content-Disposition: attachment; filename="' . $nombreArchivo . '"');
header("Content-Transfer-Encoding: binary");

echo "\xEF\xBB\xBF"; // UTF-8 BOM
?>
<table border="1" cellpadding="5" cellspacing="0" style="font-family: Arial, sans-serif; font-size: 12px;">
    <thead>
        <tr style="background-color: #3c8dbc; color: #ffffff; font-weight: bold; text-align: center;">
            <th>#</th>
            <th>Nombre del Cliente</th>
            <th>Documento</th>
            <th>Tel&eacute;fono</th>
            <th>Email</th>
            <th>Ciudad</th>
            <th>&Uacute;ltima Compra</th>
            <th>D&iacute;as Transcurridos</th>
            <th>Total Compras</th>
            <th>Total Gastado ($)</th>
            <th>Estado Inactividad</th>
        </tr>
    </thead>
    <tbody>
    <?php
    $i = 1;
    foreach ($clientes as $cli) {
        $dias = $cli["dias_inactivo"] !== null ? intval($cli["dias_inactivo"]) : null;
        
        $rangoSlug = 'sin_compras';
        $estadoTexto = 'Sin Compras';
        if ($cli["ultima_compra"] === null || $dias === null) {
            $rangoSlug = 'sin_compras';
            $estadoTexto = 'Sin Compras';
            $diasTexto = 'Sin compras';
            $fechaUltima = 'Nunca';
        } elseif ($dias < 15) {
            $rangoSlug = 'menos_15';
            $estadoTexto = 'Activo (< 15 días)';
            $diasTexto = $dias;
            $fechaUltima = date("d/m/Y", strtotime($cli["ultima_compra"]));
        } elseif ($dias <= 30) {
            $rangoSlug = '15_a_30';
            $estadoTexto = 'Activo (15 a 30 días)';
            $diasTexto = $dias;
            $fechaUltima = date("d/m/Y", strtotime($cli["ultima_compra"]));
        } elseif ($dias <= 60) {
            $rangoSlug = '30_a_60';
            $estadoTexto = 'En Riesgo (31 a 60 días)';
            $diasTexto = $dias;
            $fechaUltima = date("d/m/Y", strtotime($cli["ultima_compra"]));
        } elseif ($dias <= 90) {
            $rangoSlug = '60_a_90';
            $estadoTexto = 'Alerta (61 a 90 días)';
            $diasTexto = $dias;
            $fechaUltima = date("d/m/Y", strtotime($cli["ultima_compra"]));
        } else {
            $rangoSlug = 'mas_90';
            $estadoTexto = 'Inactivo (> 90 días)';
            $diasTexto = $dias;
            $fechaUltima = date("d/m/Y", strtotime($cli["ultima_compra"]));
        }

        // Filtro por rango si fue especificado
        if ($rangoFiltro !== "todos") {
            if ($rangoFiltro === "activos" && !($rangoSlug === "menos_15" || $rangoSlug === "15_a_30")) {
                continue;
            } elseif ($rangoFiltro !== "activos" && $rangoSlug !== $rangoFiltro) {
                continue;
            }
        }

        $docDisplay = $cli['documento'];
        if (!empty($cli['tipo_documento_id']) && $cli['tipo_documento_id'] == 6 && isset($cli['digito_verificacion']) && $cli['digito_verificacion'] !== '') {
            $docDisplay .= '-' . $cli['digito_verificacion'];
        }

        echo '<tr>';
        echo '<td align="center">' . $i++ . '</td>';
        echo '<td>' . htmlspecialchars($cli["nombre"]) . '</td>';
        echo '<td style="mso-number-format:\'@\';">' . htmlspecialchars($docDisplay) . '</td>';
        echo '<td style="mso-number-format:\'@\';">' . htmlspecialchars($cli["telefono"] ?? '') . '</td>';
        echo '<td>' . htmlspecialchars($cli["email"] ?? '') . '</td>';
        echo '<td>' . htmlspecialchars($cli["ciudad"] ?? '') . '</td>';
        echo '<td align="center">' . $fechaUltima . '</td>';
        echo '<td align="center">' . $diasTexto . '</td>';
        echo '<td align="center">' . intval($cli["total_compras"]) . '</td>';
        echo '<td align="right">' . number_format($cli["total_gastado"], 2, '.', '') . '</td>';
        echo '<td align="center">' . $estadoTexto . '</td>';
        echo '</tr>';
    }
    ?>
    </tbody>
</table>
