<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . "/../../../modelos/conexion.php";

$gruposPago = [
  "Efectivo" => "Efectivo",
  "TD" => "TD-%",
  "TC" => "TC-%",
  "Transferencia" => "Transferencia%",
  "Cheque" => "Cheque%"
];

$datosMetodoPago = [];

foreach ($gruposPago as $nombre => $patron) {
  $stmt = Conexion::conectar()->prepare("
    SELECT SUM(total) as total 
    FROM ventas 
    WHERE metodo_pago LIKE :patron 
    AND estado = 'venta'
  ");
  $stmt->bindParam(":patron", $patron, PDO::PARAM_STR);
  $stmt->execute();
  $resultado = $stmt->fetch();
  $datosMetodoPago[$nombre] = $resultado["total"] ?? 0;
}

$maxTotal = max($datosMetodoPago) ?: 1; // Evita división por cero
$colores = [
  "Efectivo" => "primary",
  "TD" => "info",
  "TC" => "success",
  "Transferencia" => "warning",
  "Cheque" => "danger"
];
?>

<div class="row">
  <!--<div class="col-md-6">-->
    <div class="box box-primary">

      <!-- Header con filtro -->
      <div class="box-header with-border d-flex justify-content-between align-items-center">
        <h3 class="box-title">Tipos de Pago</h3>
        <form id="form-filtro-tipos-pago" class="form-inline">
          <div class="input-group input-group-sm">
            <input type="date" name="fecha_inicio" class="form-control" required>
            <input type="date" name="fecha_fin" class="form-control" required>
            <div class="input-group-btn">
              <button type="submit" class="btn btn-primary btn-sm">Filtrar</button>
            </div>
          </div>
        </form>
      </div>

      <!-- Cuerpo con gráfica -->
      <div class="box-body">
        <div id="contenedor-tipos-pago">
          <?php foreach ($datosMetodoPago as $metodo => $total):
            $porcentaje = round(($total / $maxTotal) * 100);
            $nombre = match ($metodo) {
              "TD" => "Tarjeta Débito",
              "TC" => "Tarjeta Crédito",
              default => $metodo
            };
          ?>
            <div class="progress-group">
              <span class="progress-text"><?= $nombre ?></span>
              <span class="float-end">
                <b><?= number_format($total, 0) ?></b>/<?= number_format($maxTotal, 0) ?>
              </span>
              <div class="progress progress-sm">
                <div class="progress-bar text-bg-<?= $colores[$metodo] ?>" style="width: <?= $porcentaje ?>%"></div>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      </div>

    </div>
  <!--</div>-->
</div>

<style>
  .form-inline .form-control {
    margin-right: 5px;
  }
</style>
