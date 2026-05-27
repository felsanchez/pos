<?php
if (!puedeVer('cierres-caja')) {
  echo '<script>window.location = "inicio";</script>';
  return;
}

$configuracion = ControladorConfiguracion::ctrObtenerConfiguracion();
$moneda = !empty($configuracion["moneda"]) ? $configuracion["moneda"] : "$";
?>

<div class="content-wrapper">
  <section class="content-header">
    <h1>
      Historial de Cajas
      <small>Cierres y Arqueos de Turno</small>
    </h1>
    <ol class="breadcrumb">
      <li><a href="inicio"><i class="fa fa-dashboard"></i> Inicio</a></li>
      <li class="active">Historial de Cajas</li>
    </ol>
  </section>

  <section class="content">
    <div class="box">
      <div class="box-header with-border">
        
        <!-- FILTROS -->
        <div class="row">
          <form method="POST" id="formFiltrosCajas" style="display: flex; align-items: center; gap: 15px; flex-wrap: wrap; padding: 10px 15px;">
            
            <!-- Filtro Rango de Fechas -->
            <div style="display: flex; align-items: center; gap: 8px;">
              <span><b>Fecha:</b></span>
              <button type="button" class="btn btn-default" id="daterange-btn-cajas">
                <span>
                  <i class="fa fa-calendar"></i> Rango de fecha
                </span>
                <i class="fa fa-caret-down"></i>
              </button>
              <input type="hidden" id="fechaInicialCaja">
              <input type="hidden" id="fechaFinalCaja">
            </div>

            <!-- Filtro Sucursal (Bodega) -->
            <div style="display: flex; align-items: center; gap: 8px;">
              <span><b>Sucursal:</b></span>
              <div style="width: 180px;">
                <select class="form-control select2" id="filtroBodegaCaja" style="width: 100%;">
                  <option value="">Todas las sucursales</option>
                  <?php
                  $bodegas = ControladorBodegas::ctrMostrarBodegas(null, null);
                  foreach ($bodegas as $key => $value) {
                    echo '<option value="' . $value["id"] . '">' . $value["nombre"] . '</option>';
                  }
                  ?>
                </select>
              </div>
            </div>

            <!-- Filtro Cajero -->
            <div style="display: flex; align-items: center; gap: 8px;">
              <span><b>Cajero:</b></span>
              <div style="width: 180px;">
                <select class="form-control select2" id="filtroUsuarioCaja" style="width: 100%;">
                  <option value="">Todos los cajeros</option>
                  <?php
                  $usuarios = ControladorUsuarios::ctrMostrarUsuarios(null, null);
                  foreach ($usuarios as $key => $value) {
                    echo '<option value="' . $value["id"] . '">' . $value["nombre"] . '</option>';
                  }
                  ?>
                </select>
              </div>
            </div>

            <!-- Botones -->
            <button type="button" class="btn btn-default" id="btnLimpiarFiltrosCajas" title="Limpiar">
              <i class="fa fa-refresh"></i>
            </button>

          </form>
        </div>

      </div>

      <div class="box-body">
        <table class="table table-bordered table-striped tablaHistorialCajas display nowrap" width="100%">
          <thead>
            <tr>
              <th style="width:10px">#</th>
              <th>Sucursal</th>
              <th>Cajero</th>
              <th>Apertura</th>
              <th>Cierre</th>
              <th>Monto Apertura (Base)</th>
              <th>Efectivo Esperado</th>
              <th>Efectivo Real (Contado)</th>
              <th>Diferencia</th>
              <th>Acciones</th>
            </tr>
          </thead>
          <tbody>
            <!-- Carga por DataTable Server-Side -->
          </tbody>
        </table>
      </div>
    </div>
  </section>
</div>

<!--=====================================
MODAL VER DETALLE DE CIERRE DE CAJA
======================================-->
<div id="modalDetalleCaja" class="modal fade" role="dialog">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header" style="background:#3c8dbc; color: white">
        <button type="button" class="close" data-dismiss="modal">&times;</button>
        <h4 class="modal-title"><i class="fa fa-eye"></i> Detalle de Turno y Arqueo de Caja</h4>
      </div>
      <div class="modal-body">
        
        <div class="row">
          <!-- Datos Generales -->
          <div class="col-md-6">
            <h4 style="border-bottom: 2px solid #3c8dbc; padding-bottom: 5px; font-weight: bold; color: #3c8dbc;">
              <i class="fa fa-info-circle"></i> Información General
            </h4>
            <table class="table table-condensed table-striped" style="font-size: 14px;">
              <tr>
                <td style="font-weight: bold; width: 40%">Turno #</td>
                <td id="detTurno"></td>
              </tr>
              <tr>
                <td style="font-weight: bold">Sucursal (Bodega)</td>
                <td id="detSucursal"></td>
              </tr>
              <tr>
                <td style="font-weight: bold">Cajero (Usuario)</td>
                <td id="detCajero"></td>
              </tr>
              <tr>
                <td style="font-weight: bold">Fecha Apertura</td>
                <td id="detApertura"></td>
              </tr>
              <tr>
                <td style="font-weight: bold">Fecha Cierre</td>
                <td id="detCierre"></td>
              </tr>
              <tr>
                <td style="font-weight: bold">Estado</td>
                <td id="detEstado"></td>
              </tr>
            </table>
          </div>

          <!-- Auditoría de Efectivo -->
          <div class="col-md-6">
            <h4 style="border-bottom: 2px solid #dd4b39; padding-bottom: 5px; font-weight: bold; color: #dd4b39;">
              <i class="fa fa-dollar"></i> Auditoría de Efectivo (Gaveta)
            </h4>
            <table class="table table-condensed table-striped" style="font-size: 14px;">
              <tr>
                <td style="font-weight: bold; width: 50%">(+) Base Apertura</td>
                <td id="detBase" class="text-right text-bold"></td>
              </tr>
              <tr>
                <td style="font-weight: bold">(+) Ventas Efectivo</td>
                <td id="detVentasEfectivo" class="text-right text-bold text-green"></td>
              </tr>
              <tr>
                <td style="font-weight: bold">(+) Ingresos Manuales</td>
                <td id="detIngresosManuales" class="text-right text-bold text-green"></td>
              </tr>
              <tr>
                <td style="font-weight: bold">(-) Egresos Manuales</td>
                <td id="detEgresosManuales" class="text-right text-bold text-red"></td>
              </tr>
              <tr>
                <td style="font-weight: bold">(-) Gastos Efectivo</td>
                <td id="detGastosEfectivo" class="text-right text-bold text-red"></td>
              </tr>
              <tr style="background-color: #f5f5f5;">
                <td style="font-weight: bold; font-size: 15px;">(=) Efectivo Esperado</td>
                <td id="detEsperado" class="text-right text-bold" style="font-size: 15px;"></td>
              </tr>
              <tr style="background-color: #fcf8e3;">
                <td style="font-weight: bold; font-size: 15px;">(=) Efectivo Real Contado</td>
                <td id="detReal" class="text-right text-bold" style="font-size: 15px;"></td>
              </tr>
              <tr style="background-color: #f5f5f5;">
                <td style="font-weight: bold; font-size: 15px;">Diferencia</td>
                <td id="detDiferencia" class="text-right text-bold" style="font-size: 15px;"></td>
              </tr>
            </table>
          </div>
        </div>

        <div class="row" style="margin-top: 20px;">
          <!-- Resumen Ventas Electrónicas (Bancos/Tarjetas) -->
          <div class="col-md-6">
            <h4 style="border-bottom: 2px solid #00a65a; padding-bottom: 5px; font-weight: bold; color: #00a65a;">
              <i class="fa fa-credit-card"></i> Medios Electrónicos (Bancos/Tarjetas)
            </h4>
            <div style="max-height: 180px; overflow-y: auto;">
              <table class="table table-condensed table-bordered table-striped" style="font-size: 14px;" id="tablaMediosElectronicos">
                <thead>
                  <tr style="background: #f9f9f9;">
                    <th style="font-weight: bold; width: 60%;">Medio Electrónico</th>
                    <th style="font-weight: bold; width: 40%;" class="text-right">Monto Recaudado</th>
                  </tr>
                </thead>
                <tbody>
                  <!-- Inyectado dinámicamente por JS -->
                </tbody>
              </table>
            </div>
            
            <table class="table table-condensed" style="font-size: 14px; margin-top: 10px;">
              <tr style="background-color: #f5f5f5; font-size: 15px; font-weight: bold;">
                <td style="width: 60%;">Total Recaudado (Ventas)</td>
                <td id="detTotalRecaudado" class="text-right text-bold" style="width: 40%;"></td>
              </tr>
            </table>

            <!-- Observaciones de Apertura -->
            <div style="margin-top: 15px;">
              <label><i class="fa fa-commenting-o"></i> Observaciones de Apertura:</label>
              <div id="detObservacionesApertura" style="padding: 10px; background: #fafafa; border: 1px solid #eee; border-radius: 4px; min-height: 50px; font-style: italic;"></div>
            </div>

            <!-- Observaciones del Cierre -->
            <div style="margin-top: 10px;">
              <label><i class="fa fa-commenting-o"></i> Observaciones del Cierre:</label>
              <div id="detObservaciones" style="padding: 10px; background: #fafafa; border: 1px solid #eee; border-radius: 4px; min-height: 50px; font-style: italic;"></div>
            </div>
          </div>

          <!-- Historial de Movimientos de Caja Chica -->
          <div class="col-md-6">
            <h4 style="border-bottom: 2px solid #f39c12; padding-bottom: 5px; font-weight: bold; color: #f39c12;">
              <i class="fa fa-exchange"></i> Detalle Movimientos Caja Chica
            </h4>
            <div style="max-height: 180px; overflow-y: auto;">
              <table class="table table-condensed table-bordered table-striped" style="font-size: 13px;" id="tablaDetMovimientos">
                <thead>
                  <tr style="background: #f9f9f9;">
                    <th>Hora</th>
                    <th>Tipo</th>
                    <th>Monto</th>
                    <th>Concepto/Motivo</th>
                  </tr>
                </thead>
                <tbody>
                  <!-- Inyectado por JS -->
                </tbody>
              </table>
            </div>
          </div>
        </div>

      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>
      </div>
    </div>
  </div>
</div>

<script src="vistas/js/cierres-caja.js?v=<?php echo time(); ?>"></script>
