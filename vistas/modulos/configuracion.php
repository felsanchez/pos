<?php

$configuracion = ControladorConfiguracion::ctrObtenerConfiguracion();
$configFactus = ControladorFactus::ctrObtenerConfiguracion();
$municipios = ModeloFactus::mdlObtenerMunicipios();

?>

<div class="content-wrapper">

  <section class="content-header">
    <h1>
      Configuración del Sistema
    </h1>
    <ol class="breadcrumb">
      <li><a href="inicio"><i class="fa fa-dashboard"></i> Inicio</a></li>
      <li class="active">Configuración</li>
    </ol>
  </section>

  <section class="content">

    <div class="box">

      <form role="form" method="post" enctype="multipart/form-data">

        <div class="box-body">

          <!--=====================================
          SECCIÓN 1: DATOS PARA LA FACTURA
          ======================================-->

          <div class="box box-primary collapsed-box">
            <div class="box-header with-border">
              <h3 class="box-title"><i class="fa fa-file-text"></i> Datos para la Factura</h3>
              <div class="box-tools pull-right">
                <button type="button" class="btn btn-box-tool" data-widget="custom-collapse"><i
                    class="fa fa-plus"></i></button>
              </div>
            </div>
            <div class="box-body" style="display: none;">

              <div class="row">

                <!-- Logo de la Empresa -->
                <div class="col-md-3">
                  <div class="form-group text-center">
                    <label>Logo de la Empresa</label>
                    <div class="panel panel-default">
                      <div class="panel-body">
                        <?php if (!empty($configuracion["logo"]) && file_exists($configuracion["logo"])): ?>
                          <img src="<?php echo $configuracion["logo"]; ?>" class="img-responsive" id="previsualizarLogo"
                            style="max-width: 200px; margin: 0 auto;">
                        <?php else: ?>
                          <img src="vistas/img/plantilla/logo-blanco-bloque.png" class="img-responsive"
                            id="previsualizarLogo" style="max-width: 200px; margin: 0 auto;">
                        <?php endif; ?>
                      </div>
                    </div>
                    <input type="file" class="form-control" name="nuevoLogo" id="nuevoLogo" accept="image/*">
                    <input type="hidden" name="logoActual" value="<?php echo $configuracion["logo"]; ?>">
                    <p class="help-block">Formatos: JPG, PNG (Máx: 500x500px)</p>
                  </div>
                </div>

                <!-- Datos de la Empresa -->
                <div class="col-md-9">

                  <div class="row">

                    <!-- Nombre de la Empresa -->
                    <div class="col-md-6">
                      <div class="form-group">
                        <label>Nombre de la Empresa *</label>
                        <div class="input-group">
                          <span class="input-group-addon"><i class="fa fa-building"></i></span>
                          <input type="text" class="form-control" name="nombreEmpresa"
                            value="<?php echo $configuracion["nombre_empresa"]; ?>" required>
                        </div>
                      </div>
                    </div>

                    <!-- NIT / RUT -->
                    <div class="col-md-6">
                      <div class="form-group">
                        <label>NIT / RUT / Identificación Fiscal</label>
                        <div class="input-group">
                          <span class="input-group-addon"><i class="fa fa-id-card"></i></span>
                          <input type="text" class="form-control" name="nitEmpresa"
                            value="<?php echo $configuracion["nit"]; ?>" placeholder="Ej: 123456789-0">
                        </div>
                      </div>
                    </div>

                  </div>

                  <div class="row">

                    <!-- Dirección -->
                    <div class="col-md-12">
                      <div class="form-group">
                        <label>Dirección</label>
                        <div class="input-group">
                          <span class="input-group-addon"><i class="fa fa-map-marker"></i></span>
                          <input type="text" class="form-control" name="direccionEmpresa"
                            value="<?php echo $configuracion["direccion"]; ?>" placeholder="Dirección completa">
                        </div>
                      </div>
                    </div>

                  </div>

                  <div class="row">

                    <!-- Teléfono -->
                    <div class="col-md-6">
                      <div class="form-group">
                        <label>Teléfono</label>
                        <div class="input-group">
                          <span class="input-group-addon"><i class="fa fa-phone"></i></span>
                          <input type="text" class="form-control" name="telefonoEmpresa"
                            value="<?php echo $configuracion["telefono"]; ?>" placeholder="Ej: +56 9 1234 5678">
                        </div>
                      </div>
                    </div>

                    <!-- Correo -->
                    <div class="col-md-6">
                      <div class="form-group">
                        <label>Correo Electrónico</label>
                        <div class="input-group">
                          <span class="input-group-addon"><i class="fa fa-envelope"></i></span>
                          <input type="email" class="form-control" name="correoEmpresa"
                            value="<?php echo $configuracion["correo"]; ?>" placeholder="contacto@empresa.com">
                        </div>
                      </div>
                    </div>

                  </div>

                </div>

              </div>

              <hr>

              <!-- Colores de Factura -->
              <h5 class="text-muted"><i class="fa fa-paint-brush"></i> Colores de Factura</h5>

              <div class="row">

                <!-- Color Principal -->
                <div class="col-md-6">
                  <div class="form-group">
                    <label>Color Principal</label>
                    <div class="input-group">
                      <span class="input-group-addon"><i class="fa fa-square"></i></span>
                      <input type="color" class="form-control" name="colorPrincipal"
                        value="<?php echo !empty($configuracion["color_principal"]) ? $configuracion["color_principal"] : '#667eea'; ?>"
                        style="height: 40px;">
                    </div>
                    <p class="help-block">Color de cabecera y borde de "Información del Cliente"</p>
                  </div>
                </div>

                <!-- Color Secundario -->
                <div class="col-md-6">
                  <div class="form-group">
                    <label>Color Secundario</label>
                    <div class="input-group">
                      <span class="input-group-addon"><i class="fa fa-square"></i></span>
                      <input type="color" class="form-control" name="colorSecundario"
                        value="<?php echo !empty($configuracion["color_secundario"]) ? $configuracion["color_secundario"] : '#764ba2'; ?>"
                        style="height: 40px;">
                    </div>
                    <p class="help-block">Color del borde de "Detalles de la Venta"</p>
                  </div>
                </div>

              </div>

              <hr>

              <!-- Mensaje de Ticket -->
              <h5 class="text-muted"><i class="fa fa-comment"></i> Mensaje de Ticket</h5>

              <div class="row">
                <div class="col-md-12">
                  <div class="form-group">
                    <label>Mensaje de Pie de Ticket</label>
                    <div class="input-group">
                      <span class="input-group-addon"><i class="fa fa-print"></i></span>
                      <textarea class="form-control" name="mensajeTicket" rows="2"
                        placeholder="Mensaje que aparecerá al final del ticket"><?php echo $configuracion["mensaje_ticket"]; ?></textarea>
                    </div>
                    <p class="help-block">Ej: ¡Gracias por su compra! Vuelva pronto.</p>
                  </div>
                </div>
              </div>

            </div>
          </div>

          <!--=====================================
          SECCIÓN 2: CONFIGURACIÓN DE VENTAS
          ======================================-->

          <div class="box box-success collapsed-box">
            <div class="box-header with-border">
              <h3 class="box-title"><i class="fa fa-shopping-cart"></i> Configuración de Ventas</h3>
              <div class="box-tools pull-right">
                <button type="button" class="btn btn-box-tool" data-widget="custom-collapse"><i
                    class="fa fa-plus"></i></button>
              </div>
            </div>
            <div class="box-body" style="display: none;">

              <div class="row">



                <!-- Moneda -->
                <div class="col-md-4">
                  <div class="form-group">
                    <label>Símbolo de Moneda</label>
                    <div class="input-group">
                      <span class="input-group-addon"><i class="fa fa-money"></i></span>
                      <input type="text" class="form-control" name="moneda"
                        value="<?php echo $configuracion["moneda"]; ?>" maxlength="10">
                    </div>
                    <p class="help-block">Ej: $, USD, CLP</p>
                  </div>
                </div>

                <!-- Formato Código Venta -->
                <div class="col-md-4">
                  <div class="form-group">
                    <label>Formato Código Venta</label>
                    <div class="input-group">
                      <span class="input-group-addon"><i class="fa fa-barcode"></i></span>
                      <input type="text" class="form-control" name="formatoCodigoVenta"
                        value="<?php echo $configuracion["formato_codigo_venta"]; ?>" maxlength="50">
                    </div>
                    <p class="help-block">Ej: VTA-, VENTA-</p>
                  </div>
                </div>

              </div>

              <hr>

              <!-- Medios de Pago -->
              <h5 class="text-muted"><i class="fa fa-credit-card"></i> Medios de Pago</h5>

              <div class="row">
                <div class="col-md-12">
                  <div class="form-group">
                    <label>Medios de Pago Disponibles</label>
                    <div class="input-group">
                      <span class="input-group-addon"><i class="fa fa-list"></i></span>
                      <textarea class="form-control" name="mediosPago" rows="3" readonly
                        placeholder="Ingrese los medios de pago separados por comas"><?php echo !empty($configuracion["medios_pago"]) ? $configuracion["medios_pago"] : 'Efectivo,Tarjeta Débito,Tarjeta Crédito,Nequi,Bancolombia,Cheque'; ?></textarea>
                    </div>
                    <p class="help-block" style="color:#d9534f;"><i class="fa fa-lock"></i> Lista estandarizada para
                      Facturación Electrónica DIAN (No editable)</p>
                  </div>
                </div>
              </div>

              <!-- Mensajes de Seguimiento de Pedidos -->
              <div class="row">

                <!-- Mensaje Pedido Recibido -->
                <div class="col-md-6">
                  <div class="form-group">
                    <label>Mensaje Pedido Recibido</label>
                    <div class="input-group">
                      <span class="input-group-addon"><i class="fa fa-check-circle"></i></span>
                      <input type="text" class="form-control" name="mensajeRecibido"
                        value="<?php echo !empty($configuracion["mensaje_recibido"]) ? $configuracion["mensaje_recibido"] : 'Su pedido ha sido recibido'; ?>"
                        placeholder="Su pedido ha sido recibido">
                    </div>
                    <p class="help-block">Mensaje al enviar confirmación de pedido recibido</p>
                  </div>
                </div>

                <!-- Mensaje Pedido Procesado -->
                <div class="col-md-6">
                  <div class="form-group">
                    <label>Mensaje Pedido Procesado</label>
                    <div class="input-group">
                      <span class="input-group-addon"><i class="fa fa-cog"></i></span>
                      <input type="text" class="form-control" name="mensajeProcesado"
                        value="<?php echo !empty($configuracion["mensaje_procesado"]) ? $configuracion["mensaje_procesado"] : 'Su pedido ha sido procesado'; ?>"
                        placeholder="Su pedido ha sido procesado">
                    </div>
                    <p class="help-block">Mensaje al enviar confirmación de pedido procesado</p>
                  </div>
                </div>

              </div>

              <!-- Nueva fila para el tercer mensaje -->
              <div class="row">

                <!-- Mensaje Pedido Confirmado -->
                <div class="col-md-12">
                  <div class="form-group">
                    <label>Mensaje Pedido Confirmado</label>
                    <div class="input-group">
                      <span class="input-group-addon"><i class="fa fa-thumbs-up"></i></span>
                      <input type="text" class="form-control" name="mensajeConfirmado"
                        value="<?php echo !empty($configuracion["mensaje_confirmado"]) ? $configuracion["mensaje_confirmado"] : 'Su pedido ha sido confirmado'; ?>"
                        placeholder="Su pedido ha sido confirmado">
                    </div>
                    <p class="help-block">Mensaje al confirmar el pedido (tercer botón de seguimiento)</p>
                  </div>
                </div>

              </div>

            </div>
          </div>

          <!--=====================================
          SECCIÓN 3: CONFIGURACIÓN DE PRODUCTOS
          ======================================-->

          <div class="box box-warning collapsed-box">
            <div class="box-header with-border">
              <h3 class="box-title"><i class="fa fa-cube"></i> Configuración de Productos</h3>
              <div class="box-tools pull-right">
                <button type="button" class="btn btn-box-tool" data-widget="custom-collapse"><i
                    class="fa fa-plus"></i></button>
              </div>
            </div>
            <div class="box-body" style="display: none;">

              <!-- Tipo de Código de Producto -->
              <div class="row">
                <div class="col-md-12">
                  <div class="form-group">
                    <label>Tipo de Código de Producto</label>
                    <div class="radio">
                      <label style="font-weight: normal; cursor: pointer;">
                        <input type="radio" name="tipoCodigoProducto" value="automatico" <?php echo (!empty($configuracion["tipo_codigo_producto"]) && $configuracion["tipo_codigo_producto"] == "automatico") || empty($configuracion["tipo_codigo_producto"]) ? "checked" : ""; ?>>
                        <strong>Automático</strong> - El sistema genera el código automáticamente
                      </label>
                    </div>
                    <div class="radio">
                      <label style="font-weight: normal; cursor: pointer;">
                        <input type="radio" name="tipoCodigoProducto" value="manual" <?php echo (!empty($configuracion["tipo_codigo_producto"]) && $configuracion["tipo_codigo_producto"] == "manual") ? "checked" : ""; ?>>
                        <strong>Manual</strong> - El usuario ingresa el código manualmente
                      </label>
                    </div>
                  </div>
                </div>
              </div>

            </div>
          </div>

          <!--=====================================
          SECCIÓN 4: ALERTAS Y NOTIFICACIONES
          ======================================-->

          <div class="box box-danger collapsed-box">
            <div class="box-header with-border">
              <h3 class="box-title"><i class="fa fa-bell"></i> Alertas y Notificaciones</h3>
              <div class="box-tools pull-right">
                <button type="button" class="btn btn-box-tool" data-widget="custom-collapse"><i
                    class="fa fa-plus"></i></button>
              </div>
            </div>
            <div class="box-body" style="display: none;">

              <!-- Alertas de Stock -->
              <h5 class="text-muted"><i class="fa fa-cubes"></i> Alertas de Stock</h5>

              <div class="row">

                <!-- Alerta de Stock Bajo -->
                <div class="col-md-6">
                  <div class="form-group">
                    <div class="checkbox">
                      <label style="font-weight: normal; cursor: pointer;">
                        <input type="checkbox" name="alertaStockBajo" value="1" <?php echo (!empty($configuracion["alerta_stock_bajo"]) && $configuracion["alerta_stock_bajo"] == 1) || !isset($configuracion["alerta_stock_bajo"]) ? "checked" : ""; ?>>
                        <strong>Activar alerta de stock bajo</strong>
                      </label>
                    </div>
                    <div class="input-group">
                      <span class="input-group-addon"><i class="fa fa-cubes"></i></span>
                      <input type="number" class="form-control" name="umbralStockMinimo" min="1"
                        value="<?php echo !empty($configuracion["umbral_stock_minimo"]) ? $configuracion["umbral_stock_minimo"] : '5'; ?>">
                      <span class="input-group-addon">unidades</span>
                    </div>
                    <p class="help-block">Alertar cuando el stock esté por debajo de esta cantidad</p>
                  </div>
                </div>

                <!-- Alerta de Stock Agotado -->
                <div class="col-md-6">
                  <div class="form-group">
                    <div class="checkbox">
                      <label style="font-weight: normal; cursor: pointer;">
                        <input type="checkbox" name="alertaStockAgotado" value="1" <?php echo (!empty($configuracion["alerta_stock_agotado"]) && $configuracion["alerta_stock_agotado"] == 1) || !isset($configuracion["alerta_stock_agotado"]) ? "checked" : ""; ?>>
                        <strong>Activar alerta de stock agotado</strong>
                      </label>
                    </div>
                    <p class="help-block">Notificar cuando un producto se agote completamente (stock = 0)</p>
                  </div>
                </div>

              </div>

              <hr>

              <!-- Alertas de Actividades y Gastos -->
              <h5 class="text-muted"><i class="fa fa-calendar"></i> Alertas de Actividades y Gastos</h5>

              <div class="row">

                <!-- Alerta de Actividades Pendientes -->
                <div class="col-md-6">
                  <div class="form-group">
                    <div class="checkbox">
                      <label style="font-weight: normal; cursor: pointer;">
                        <input type="checkbox" name="alertaActividadesPendientes" value="1" <?php echo (!empty($configuracion["alerta_actividades_pendientes"]) && $configuracion["alerta_actividades_pendientes"] == 1) || !isset($configuracion["alerta_actividades_pendientes"]) ? "checked" : ""; ?>>
                        <strong>Activar alerta de actividades próximas</strong>
                      </label>
                    </div>
                    <div class="input-group">
                      <span class="input-group-addon"><i class="fa fa-calendar-check-o"></i></span>
                      <input type="number" class="form-control" name="diasAntesActividad" min="1"
                        value="<?php echo !empty($configuracion["dias_antes_actividad"]) ? $configuracion["dias_antes_actividad"] : '3'; ?>">
                      <span class="input-group-addon">días antes</span>
                    </div>
                    <p class="help-block">Alertar X días antes de la fecha de la actividad</p>
                  </div>
                </div>

                <!-- Alerta de Gastos Próximos -->
                <div class="col-md-6">
                  <div class="form-group">
                    <div class="checkbox">
                      <label style="font-weight: normal; cursor: pointer;">
                        <input type="checkbox" name="alertaGastosProximos" value="1" <?php echo (!empty($configuracion["alerta_gastos_proximos"]) && $configuracion["alerta_gastos_proximos"] == 1) || !isset($configuracion["alerta_gastos_proximos"]) ? "checked" : ""; ?>>
                        <strong>Activar alerta de gastos próximos a vencer</strong>
                      </label>
                    </div>
                    <div class="input-group">
                      <span class="input-group-addon"><i class="fa fa-money"></i></span>
                      <input type="number" class="form-control" name="diasAntesGasto" min="1"
                        value="<?php echo !empty($configuracion["dias_antes_gasto"]) ? $configuracion["dias_antes_gasto"] : '5'; ?>">
                      <span class="input-group-addon">días antes</span>
                    </div>
                    <p class="help-block">Alertar X días antes del vencimiento del gasto</p>
                  </div>
                </div>

              </div>

              <hr>

              <!-- Alerta de Agente IA -->
              <h5 class="text-muted"><i class="fa fa-robot"></i> Alerta de Agente IA</h5>

              <div class="row">
                <div class="col-md-12">
                  <div class="form-group">
                    <div class="checkbox">
                      <label style="font-weight: normal; cursor: pointer;">
                        <input type="checkbox" name="alertaAgenteIA" value="1" <?php echo (!empty($configuracion["alerta_agente_ia"]) && $configuracion["alerta_agente_ia"] == 1) || !isset($configuracion["alerta_agente_ia"]) ? "checked" : ""; ?>>
                        <strong>Notificar cuando una orden de venta proviene del Agente IA</strong>
                      </label>
                    </div>
                    <p class="help-block">Se creará una notificación cuando el campo 'extra' de la orden contenga 'n8n'
                    </p>
                  </div>

                  <!-- Botón Webhook Seguimiento -->
                  <button type="button" id="btnEnviarSeguimientoN8N" class="btn btn-info pull-right"
                    style="margin-bottom: 15px;">
                    <i class="fa fa-paper-plane"></i> Enviar Seguimiento
                  </button>

                </div>
              </div>

            </div>
          </div>

          <!--=====================================
          SECCIÓN 5: CONFIGURACIÓN FACTUS
          ======================================-->

          <div class="box box-primary collapsed-box">
            <div class="box-header with-border">
              <h3 class="box-title"><i class="fa fa-file-code-o"></i> Facturación Electrónica (Factus)</h3>
              <div class="box-tools pull-right">
                <button type="button" class="btn btn-box-tool" data-widget="custom-collapse"><i
                    class="fa fa-plus"></i></button>
              </div>
            </div>
            <div class="box-body" style="display: none;">
              <div class="row">
                <div class="col-md-12">
                  <p>Configura la conexión con Factus para emitir facturas electrónicas, sincronizar municipios,
                    unidades y tributos.</p>

                  <hr>
                  <h4 class="text-primary"><i class="fa fa-building"></i> Datos del Emisor (Sandbox/Factus)</h4>

                  <?php
                  $bloqueado = (isset($configFactus['bloqueo_datos_emisor']) && $configFactus['bloqueo_datos_emisor'] == 1);
                  $readonly = $bloqueado ? 'readonly' : '';
                  $disabled = $bloqueado ? 'disabled' : '';

                  if ($bloqueado) {
                    echo '<div class="alert alert-info alert-dismissible">
                              <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                              <h4><i class="icon fa fa-lock"></i> Campos Bloqueados</h4>
                              Contacta a tu proveedor para editar los campos del Emisor de Facturas electrónicas
                            </div>';
                  }
                  ?>

                  <p class="text-muted">Estos datos se usarán para generar la factura electrónica. Si están vacíos, se
                    usarán los datos generales de la empresa.</p>

                  <div class="row">
                    <!-- Tipo Persona -->
                    <div class="col-md-4">
                      <div class="form-group">
                        <label>Tipo Persona</label>
                        <div class="input-group">
                          <span class="input-group-addon"><i class="fa fa-users"></i></span>
                          <select class="form-control" name="tipopersonafactus" id="tipoPersonaFactus" <?php echo $disabled; ?>>
                            <?php
                            $tipoPersonaActual = isset($configFactus['tipo_persona']) ? $configFactus['tipo_persona'] : '2';
                            ?>
                            <option value="2" <?php echo $tipoPersonaActual == '2' ? 'selected' : ''; ?>>Persona Natural
                            </option>
                            <option value="1" <?php echo $tipoPersonaActual == '1' ? 'selected' : ''; ?>>Persona Jurídica
                            </option>
                          </select>
                        </div>
                      </div>
                    </div>

                    <!-- Nombre Empresa / Razón Social -->
                    <div class="col-md-8">
                      <div class="form-group">
                        <label id="labelNombreFactus">Nombre Empresa (Factus) </label>
                        <div class="input-group">
                          <span class="input-group-addon"><i class="fa fa-building"></i></span>
                          <input type="text" class="form-control" name="nombrefactus" <?php echo $readonly; ?>
                            value="<?php echo isset($configFactus['nombre_empresa']) ? $configFactus['nombre_empresa'] : ''; ?>"
                            placeholder="Nombre registrado en Sandbox">
                        </div>
                      </div>
                    </div>
                  </div>

                  <div class="row">
                    <!-- NIT -->
                    <div class="col-md-5">
                      <div class="form-group">
                        <label>NIT / Documento</label>
                        <div class="input-group">
                          <span class="input-group-addon"><i class="fa fa-id-card"></i></span>
                          <input type="text" class="form-control" name="nitfactus" <?php echo $readonly; ?>
                            value="<?php echo isset($configFactus['nit_empresa']) ? $configFactus['nit_empresa'] : ''; ?>"
                            placeholder="Ej: 900123456">
                        </div>
                      </div>
                    </div>

                    <!-- DV (Digito Verificación) -->
                    <div class="col-md-1">
                      <div class="form-group">
                        <label>DV</label>
                        <input type="text" class="form-control" name="dvfactus" <?php echo $readonly; ?>
                          value="<?php echo isset($configFactus['dv']) ? $configFactus['dv'] : ''; ?>" placeholder="0"
                          maxlength="1" style="padding: 6px;">
                      </div>
                    </div>
                  </div>

                  <div class="row">
                    <!-- Dirección -->
                    <div class="col-md-6">
                      <div class="form-group">
                        <label>Dirección</label>
                        <div class="input-group">
                          <span class="input-group-addon"><i class="fa fa-map-marker"></i></span>
                          <input type="text" class="form-control" name="direccionfactus"
                            value="<?php echo isset($configFactus['direccion_empresa']) ? $configFactus['direccion_empresa'] : ''; ?>"
                            placeholder="Dirección completa">
                        </div>
                      </div>
                    </div>
                    <!-- Teléfono -->
                    <div class="col-md-6">
                      <div class="form-group">
                        <label>Teléfono</label>
                        <div class="input-group">
                          <span class="input-group-addon"><i class="fa fa-phone"></i></span>
                          <input type="text" class="form-control" name="telefonofactus"
                            value="<?php echo isset($configFactus['telefono_empresa']) ? $configFactus['telefono_empresa'] : ''; ?>"
                            placeholder="Ej: 3001234567">
                        </div>
                      </div>
                    </div>
                  </div>

                  <div class="row">
                    <!-- Email -->
                    <div class="col-md-6">
                      <div class="form-group">
                        <label>Email Facturación</label>
                        <div class="input-group">
                          <span class="input-group-addon"><i class="fa fa-envelope"></i></span>
                          <input type="email" class="form-control" name="emailfactus"
                            value="<?php echo isset($configFactus['email_empresa']) ? $configFactus['email_empresa'] : ''; ?>"
                            placeholder="email@empresa.com">
                        </div>
                      </div>
                    </div>
                    <!-- Municipio ID -->
                    <div class="col-md-6">
                      <div class="form-group">
                        <label>Municipio (Factus)</label>
                        <div class="input-group">
                          <span class="input-group-addon"><i class="fa fa-map"></i></span>
                          <select class="form-control select2" name="municipiofactus" style="width: 100%;">
                            <?php
                            $municipioIdActual = isset($configFactus['municipio_id']) ? $configFactus['municipio_id'] : '169';
                            foreach ($municipios as $municipio) {
                              $selected = ($municipioIdActual == $municipio['id_factus']) ? 'selected' : '';
                              echo "<option value='{$municipio['id_factus']}' $selected>{$municipio['nombre']} - {$municipio['departamento']}</option>";
                            }
                            ?>
                          </select>
                        </div>
                      </div>
                    </div>
                  </div>

                  <!-- NUEVOS CAMPOS: Tributo, Actividad, Registro -->
                  <div class="row">

                    <!-- Tributo -->
                    <div class="col-md-6">
                      <div class="form-group">
                        <label>Tributo (Responsabilidad IVA)</label>
                        <div class="input-group">
                          <span class="input-group-addon"><i class="fa fa-money"></i></span>
                          <select class="form-control" name="tributofactus" <?php echo $disabled; ?>>
                            <?php
                            $tributoActual = isset($configFactus['tributo_emisor']) ? $configFactus['tributo_emisor'] : 'no_responsable';
                            ?>
                            <option value="responsable_iva" <?php echo $tributoActual == 'responsable_iva' ? 'selected' : ''; ?>>Responsable de IVA</option>
                            <option value="no_responsable" <?php echo $tributoActual == 'no_responsable' ? 'selected' : ''; ?>>No Responsable de IVA</option>
                          </select>
                        </div>
                      </div>
                    </div>

                    <!-- Registro Mercantil -->
                    <div class="col-md-6">
                      <div class="form-group">
                        <label>Registro Mercantil</label>
                        <div class="input-group">
                          <span class="input-group-addon"><i class="fa fa-registered"></i></span>
                          <input type="text" class="form-control" name="registrofactus" <?php echo $readonly; ?>
                            value="<?php echo isset($configFactus['registro_mercantil']) ? $configFactus['registro_mercantil'] : ''; ?>"
                            placeholder="Número de Registro">
                        </div>
                      </div>
                    </div>
                  </div>

                  <div class="row">
                    <!-- Actividad Económica -->
                    <div class="col-md-6">
                      <div class="form-group">
                        <label>Actividad Económica (Código)</label>
                        <div class="input-group">
                          <span class="input-group-addon"><i class="fa fa-briefcase"></i></span>
                          <input type="text" class="form-control" name="actividadfactus" <?php echo $readonly; ?>
                            value="<?php echo isset($configFactus['actividad_economica']) ? $configFactus['actividad_economica'] : ''; ?>"
                            placeholder="Ej: 4711">
                        </div>
                      </div>
                    </div>
                  </div>

                  <!-- Responsabilidades Fiscales -->
                  <div class="row">
                    <div class="col-md-12">
                      <div class="form-group">
                        <label>Responsabilidades Fiscales</label>
                        <div style="border: 1px solid #d2d6de; padding: 10px; border-radius: 5px; background: #f9f9f9;">
                          <?php
                          $respFiscales = isset($configFactus['responsabilidades_fiscales']) ? json_decode($configFactus['responsabilidades_fiscales'], true) : [];
                          if (!is_array($respFiscales))
                            $respFiscales = [];

                          $opcionesResponsabilidad = [
                            "O-13" => "O-13 Gran contribuyente",
                            "O-15" => "O-15 Autorretenedor",
                            "O-23" => "O-23 Agente de retención de IVA",
                            "O-47" => "O-47 Régimen simple de tributación",
                            "R-99-PN" => "R-99-PN No responsable"
                          ];

                          foreach ($opcionesResponsabilidad as $codigo => $texto) {
                            $checked = in_array($codigo, $respFiscales) ? 'checked' : '';
                            echo '<div class="checkbox" style="margin-top: 5px; margin-bottom: 5px;">
                                      <label>
                                        <input type="checkbox" name="responsabilidadesfactus[]" value="' . $codigo . '" ' . $checked . ' ' . $disabled . '> ' . $texto . '
                                      </label>
                                    </div>';
                          }
                          ?>
                        </div>
                      </div>
                    </div>
                  </div>

                  <a href="configuracion-factus" class="btn btn-primary btn-block">
                    <i class="fa fa-cogs"></i> Ir a Configuración Completa de Factus
                  </a>
                </div>
              </div>
            </div>
          </div>

        </div>

        <!-- Pie del Formulario -->
        <div class="box-footer">
          <button type="submit" class="btn btn-primary btn-lg">
            <i class="fa fa-save"></i> Guardar Configuración
          </button>
          <a href="inicio" class="btn btn-default btn-lg">
            <i class="fa fa-times"></i> Cancelar
          </a>
        </div>

        <?php

        $actualizarConfiguracion = new ControladorConfiguracion();
        $actualizarConfiguracion->ctrActualizarConfiguracion();

        ?>

      </form>

    </div>

  </section>

</div>

<script>
  $(document).ready(function () {
    // Label dinámico para Tipo Persona
    function toggleLabelName() {
      var tipo = $("#tipoPersonaFactus").val();
      if (tipo == "1") {
        $("#labelNombreFactus").text("Razón Social");
      } else {
        $("#labelNombreFactus").text("Nombre Empresa (Factus)");
      }
    }

    $("#tipoPersonaFactus").change(function () {
      toggleLabelName();
    });

    // Run on init
    toggleLabelName();

    // Al enviar el formulario, habilitar campos deshabilitados para que se envíen
    // Esto es necesario porque los campos disabled no se envían en el POST
    $("form").submit(function () {
      $('select[name="tipopersonafactus"], select[name="municipiofactus"], select[name="tributofactus"]').prop("disabled", false);
      $('input[name="responsabilidadesfactus[]"]').prop("disabled", false);
    });

  });
</script>

<script>
  $(document).ready(function () {

    $("#nuevoLogo").change(function () {

      var imagen = this.files[0];

      // Validar formato
      if (imagen["type"] != "image/jpeg" && imagen["type"] != "image/png") {

        $("#nuevoLogo").val("");

        swal({
          title: "Error al subir la imagen",
          text: "¡La imagen debe estar en formato JPG o PNG!",
          type: "error",
          confirmButtonText: "¡Cerrar!"
        });

      } else if (imagen["size"] > 2000000) {

        $("#nuevoLogo").val("");

        swal({
          title: "Error al subir la imagen",
          text: "¡La imagen no debe pesar más de 2MB!",
          type: "error",
          confirmButtonText: "¡Cerrar!"
        });

      } else {

        var datosImagen = new FileReader;
        datosImagen.readAsDataURL(imagen);

        $(datosImagen).on("load", function (event) {

          var rutaImagen = event.target.result;

          $("#previsualizarLogo").attr("src", rutaImagen);

        })

      }

    })

  });
</script>

<script>
  $(document).ready(function () {
    // Custom collapse handler
    $(document).on('click', '[data-widget="custom-collapse"]', function () {
      var box = $(this).closest('.box');
      var boxBody = box.find('.box-body');
      var icon = $(this).find('i');

      if (box.hasClass('collapsed-box')) {
        boxBody.slideDown();
        box.removeClass('collapsed-box');
        icon.removeClass('fa-plus').addClass('fa-minus');
      } else {
        boxBody.slideUp();
        box.addClass('collapsed-box');
        icon.removeClass('fa-minus').addClass('fa-plus');
      }
    });
  });
</script>

<!-- Script para el botón de Seguimiento Webhook n8n -->
<script>
  $(document).on("click", "#btnEnviarSeguimientoN8N", function () {

    // URL Webhook explícita
    const webhookUrl = "https://demo-ppal-n8n.lhs6l6.easypanel.host/webhook/251471f2-eea7-4425-a847-7ee17583f03a";

    // Datos a enviar
    const dataToSend = new URLSearchParams();
    dataToSend.append("origen", "seguimiento");

    console.log("Intentando enviar a:", webhookUrl);

    // Mostrar loading
    swal({
      title: 'Enviando seguimiento...',
      text: 'Conectando con n8n...',
      type: 'info',
      showConfirmButton: false,
      allowOutsideClick: false
    });

    fetch(webhookUrl, {
      method: 'POST',
      mode: 'no-cors',
      cache: 'no-cache',
      credentials: 'omit',
      headers: {
        'Content-Type': 'application/x-www-form-urlencoded'
      },
      body: dataToSend
    })
      .then(response => {
        console.log('Petición finalizada (respuesta opaca)');
        swal({
          title: '¡Enviado!',
          text: 'La solicitud de seguimiento se ha enviado a n8n correctamente.',
          type: 'success',
          timer: 2000
        });
      })
      .catch(error => {
        console.error('Error FETCH:', error);
        swal({
          title: 'Error de Conexión',
          text: 'No se pudo contactar con el servidor. Revisa tu conexión a internet.',
          type: 'error'
        });
      });
  });
</script>