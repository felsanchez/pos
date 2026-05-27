<?php
// Modales globales para la apertura, movimiento y cierre de caja chica.
// Este archivo es cargado dinámicamente en la plantilla si el control de caja está activo.
$configuracion = ControladorConfiguracion::ctrObtenerConfiguracion();
$moneda = !empty($configuracion["moneda"]) ? $configuracion["moneda"] : "$";
?>

<!--=====================================
MODAL APERTURA DE CAJA
======================================-->
<div id="modalAperturaCaja" class="modal fade" role="dialog" data-backdrop="static" data-keyboard="false">
  <div class="modal-dialog">
    <div class="modal-content">
      <form role="form" method="post" id="formularioAperturaCaja">
        <div class="modal-header" style="background:#00a65a; color: white">
          <h4 class="modal-title"><i class="fa fa-cash-register"></i> Apertura de Caja</h4>
        </div>
        <div class="modal-body">
          <div class="box-body">
            <div class="form-group text-center">
              <label style="font-size: 16px; color: #555;">Ingrese el monto base inicial:</label>
              <div class="input-group input-group-lg" style="margin-top: 10px;">
                <span class="input-group-addon"><b><?php echo $moneda; ?></b></span>
                <input type="number" min="0" step="0.01" class="form-control text-center" id="montoApertura" name="montoApertura" placeholder="0.00" required style="font-weight: bold; font-size: 24px; height: 50px;">
              </div>
              <p class="help-block" style="margin-top: 10px; font-style: italic;">Dinero inicial en efectivo en el cajón monedero para dar cambio.</p>
            </div>
            
            <!-- Observaciones de Apertura -->
            <div class="form-group text-left" style="margin-top: 15px;">
              <label style="font-weight: normal; color: #555;">Observaciones de Apertura (Opcional):</label>
              <textarea class="form-control" id="observacionesApertura" name="observacionesApertura" placeholder="Escribe notas sobre el estado de apertura, billetes disponibles, etc." style="resize: none; height: 70px;"></textarea>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-default pull-left" data-dismiss="modal">Cancelar</button>
          <button type="submit" class="btn btn-success"><i class="fa fa-unlock"></i> Abrir Caja</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!--=====================================
MODAL REGISTRAR MOVIMIENTO MANUAL (CAJA CHICA)
======================================-->
<div id="modalMovimientoCaja" class="modal fade" role="dialog">
  <div class="modal-dialog">
    <div class="modal-content">
      <form role="form" method="post" id="formularioMovimientoCaja">
        <div class="modal-header" style="background:#f39c12; color: white">
          <button type="button" class="close" data-dismiss="modal">&times;</button>
          <h4 class="modal-title"><i class="fa fa-exchange"></i> Registrar Movimiento de Caja</h4>
        </div>
        <div class="modal-body">
          <div class="box-body">
            
            <!-- Tipo de Movimiento -->
            <div class="form-group">
              <label>Tipo de Operación:</label>
              <div class="input-group">
                <span class="input-group-addon"><i class="fa fa-sliders"></i></span>
                <select class="form-control input-lg" id="tipoMovimiento" name="tipoMovimiento" required>
                  <option value="">Seleccione tipo de movimiento</option>
                  <option value="ingreso">Entrada (Inyección de efectivo para cambio)</option>
                  <option value="egreso">Salida (Gasto menor / Retiro de efectivo)</option>
                </select>
              </div>
            </div>

            <!-- Monto -->
            <div class="form-group">
              <label>Monto:</label>
              <div class="input-group">
                <span class="input-group-addon"><b><?php echo $moneda; ?></b></span>
                <input type="number" min="0.01" step="0.01" class="form-control input-lg" id="montoMovimiento" name="montoMovimiento" placeholder="0.00" required>
              </div>
            </div>

            <!-- Motivo -->
            <div class="form-group">
              <label>Motivo / Concepto:</label>
              <div class="input-group">
                <span class="input-group-addon"><i class="fa fa-pencil"></i></span>
                <input type="text" class="form-control input-lg" id="motivoMovimiento" name="motivoMovimiento" placeholder="Ej. Pago de bombillo, recarga de caja menor" required maxlength="255">
              </div>
            </div>

          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-default pull-left" data-dismiss="modal">Cerrar</button>
          <button type="submit" class="btn btn-warning">Registrar Movimiento</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!--=====================================
MODAL CIERRE DE CAJA (ARQUEO CIEGO)
======================================-->
<div id="modalCerrarCaja" class="modal fade" role="dialog">
  <div class="modal-dialog modal-sm">
    <div class="modal-content">
      <form role="form" method="post" id="formularioCerrarCaja">
        <div class="modal-header" style="background:#dd4b39; color: white">
          <button type="button" class="close" data-dismiss="modal">&times;</button>
          <h4 class="modal-title"><i class="fa fa-lock"></i> Cierre de Turno y Arqueo</h4>
        </div>
        <div class="modal-body">
          <div class="box-body">
            <div class="form-group text-center">
              <label style="font-size: 14px; color: #555; display: block; margin-bottom: 12px; font-weight: normal;">
                Cuentas el dinero físico de la registradora e ingresa el total de efectivo contado:
              </label>
              <div class="input-group input-group-lg">
                <span class="input-group-addon"><b><?php echo $moneda; ?></b></span>
                <input type="number" min="0" step="0.01" class="form-control text-center" id="montoCierreReal" name="montoCierreReal" placeholder="0.00" required style="font-weight: bold; font-size: 24px; height: 50px;">
              </div>
            </div>

            <!-- Observaciones -->
            <div class="form-group text-left" style="margin-top: 15px;">
              <label style="font-weight: normal; color: #555;">Observaciones (Opcional):</label>
              <textarea class="form-control" id="observacionesCierre" name="observacionesCierre" placeholder="Escribe notas sobre diferencias, egresos, etc." style="resize: none; height: 70px;"></textarea>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-default pull-left" data-dismiss="modal">Cancelar</button>
          <button type="submit" class="btn btn-danger"><i class="fa fa-lock"></i> Cerrar Caja</button>
        </div>
      </form>
    </div>
  </div>
</div>
