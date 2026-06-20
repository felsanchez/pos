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

        <?php CSRF::insertToken(); ?>
        <input type="hidden" name="actualizarConfiguracion" value="ok">
        <input type="hidden" name="logoActual" value="<?php echo $configuracion["logo"]; ?>">
        <input type="hidden" name="nombreEmpresa" value="<?php echo $configuracion["nombre_empresa"]; ?>">
        <input type="hidden" name="nitEmpresa" value="<?php echo $configuracion["nit"]; ?>">
        <input type="hidden" name="direccionEmpresa" value="<?php echo $configuracion["direccion"]; ?>">
        <input type="hidden" name="telefonoEmpresa" value="<?php echo $configuracion["telefono"]; ?>">
        <input type="hidden" name="correoEmpresa" value="<?php echo $configuracion["correo"]; ?>">
        <input type="hidden" name="colorPrincipal" value="<?php echo $configuracion["color_principal"]; ?>">
        <input type="hidden" name="colorSecundario" value="<?php echo $configuracion["color_secundario"]; ?>">
        <input type="hidden" name="mensajeTicket" value="<?php echo $configuracion["mensaje_ticket"]; ?>">




        <div class="box-body">

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

              <?php if (!isset($configuracion["columna_seguimiento_activa"]) || $configuracion["columna_seguimiento_activa"] == 1): ?>
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
              <?php endif; ?>

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

              <!-- Alertas de Actividades -->
              <h5 class="text-muted"><i class="fa fa-calendar"></i> Alertas de Actividades</h5>

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


              </div>

              <hr>



            </div>
          </div>

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
                <div class="col-md-12">
                  <p>Configura la conexión con Factus para emitir facturas electrónicas, sincronizar municipios,
                    unidades y tributos.</p>

                  <hr>
                  <h4 class="text-primary"><i class="fa fa-building"></i> Datos del Emisor</h4>

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
                    <!-- Logo Factus -->
                    <div class="col-md-12">
                      <div class="form-group text-center">
                        <label>Logo para Facturación Electrónica</label>
                        <div class="panel panel-default">
                          <div class="panel-body">
                            <?php if (isset($configFactus["logo_empresa"]) && !empty($configFactus["logo_empresa"]) && file_exists($configFactus["logo_empresa"])): ?>
                              <img src="<?php echo $configFactus["logo_empresa"]; ?>" class="img-responsive"
                                id="previsualizarLogoFactus" style="max-width: 200px; margin: 0 auto;">
                            <?php else: ?>
                              <img src="vistas/img/plantilla/logo-blanco-bloque.png" class="img-responsive"
                                id="previsualizarLogoFactus" style="max-width: 200px; margin: 0 auto;">
                            <?php endif; ?>
                          </div>
                        </div>
                        <input type="file" class="form-control" name="nuevoLogoFactus" id="nuevoLogoFactus"
                          accept="image/*">
                        <p class="help-block">Formatos: JPG, PNG (Máx: 500x500px). Este logo se usará solo para Factus.
                        </p>
                      </div>
                    </div>
                  </div>

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
                        <label id="labelNombreFactus">Nombre Empresa </label>
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
                    <!-- Nombre Comercial -->
                    <div class="col-md-12">
                      <div class="form-group">
                        <label>Nombre Comercial</label>
                        <div class="input-group">
                          <span class="input-group-addon"><i class="fa fa-tag"></i></span>
                          <input type="text" class="form-control" name="nombrecomercialfactus" <?php echo $readonly; ?>
                            value="<?php echo isset($configFactus['nombre_comercial']) ? $configFactus['nombre_comercial'] : ''; ?>"
                            placeholder="Nombre comercial de la empresa">
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
                        <label id="labelDvFactus">DV</label>
                        <input type="text" class="form-control" name="dvfactus" id="dvFactus" <?php echo $readonly; ?>
                          value="<?php echo isset($configFactus['dv']) ? $configFactus['dv'] : ''; ?>" placeholder="0"
                          maxlength="1" style="padding: 6px;">
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
                        <label>Municipio</label>
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

                  <!-- Botón Autenticar rápido -->
                  <div class="callout callout-success" style="margin-top: 15px;">
                    <h4><i class="fa fa-key"></i> Autenticación Rápida</h4>
                    <p>Obtén nuevos tokens de acceso usando las credenciales guardadas actualmente.</p>
                    <style>
                      #btnAutenticarConfig {
                        background-color: #10b981 !important;
                        border-color: #059669 !important;
                        font-weight: bold;
                        padding: 10px 20px;
                        font-size: 16px;
                        border-radius: 4px;
                        transition: background-color 0.2s ease, transform 0.1s ease;
                      }
                      #btnAutenticarConfig:hover, #btnAutenticarConfig:focus, #btnAutenticarConfig:active {
                        background-color: #059669 !important;
                        border-color: #047857 !important;
                        color: #fff !important;
                      }
                      #btnAutenticarConfig:active {
                        transform: scale(0.98);
                      }
                    </style>
                    <button type="button" class="btn btn-success" id="btnAutenticarConfig">
                      <i class="fa fa-key"></i> Autenticar y Obtener Tokens
                    </button>
                    <div id="resultadoAutenticarConfig" style="margin-top: 12px;"></div>
                  </div>

                  <!--
                  <a href="configuracion-factus" class="btn btn-primary btn-block">
                    <i class="fa fa-cogs"></i> Ir a Configuración Completa de Factus
                  </a>
                    -->

                  <script>
                  $(document).ready(function () {
                    // Credenciales guardadas inyectadas desde PHP
                    var factusConfig = {
                      apiUrl:          '<?php echo addslashes($configFactus['api_url'] ?? ''); ?>',
                      clientId:        '<?php echo addslashes($configFactus['client_id'] ?? ''); ?>',
                      clientSecret:    '<?php echo addslashes($configFactus['client_secret'] ?? ''); ?>',
                      username:        '<?php echo addslashes($configFactus['username'] ?? ''); ?>',
                      password:        '<?php echo addslashes($configFactus['password'] ?? ''); ?>',
                      ambiente:        '<?php echo addslashes($configFactus['ambiente'] ?? 'sandbox'); ?>',
                      rangoNumeracionId: '<?php echo addslashes($configFactus['rango_numeracion_id'] ?? ''); ?>'
                    };

                    $('#btnAutenticarConfig').on('click', function () {
                      var btn = $(this);
                      var resultado = $('#resultadoAutenticarConfig');

                      if (!factusConfig.apiUrl || !factusConfig.clientId || !factusConfig.clientSecret) {
                        resultado.html('<div class="alert alert-warning"><i class="fa fa-warning"></i> Faltan credenciales. Configúralas en <a href="configuracion-factus">Configuración de Factus</a>.</div>');
                        return;
                      }

                      swal({
                        title: '¿Autenticar con Factus?',
                        text: 'Esto obtendrá nuevos tokens de acceso y los guardará en el sistema.',
                        type: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#3c8dbc',
                        confirmButtonText: 'Sí, autenticar',
                        cancelButtonText: 'Cancelar'
                      }).then(function (result) {
                        if (result.value) {
                          btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Autenticando...');
                          resultado.html('<div class="alert alert-info"><i class="fa fa-spinner fa-spin"></i> Obteniendo tokens de Factus...</div>');

                          $.ajax({
                            url: 'ajax/factus.ajax.php',
                            method: 'POST',
                            data: {
                              accion: 'autenticar',
                              apiUrl: factusConfig.apiUrl,
                              clientId: factusConfig.clientId,
                              clientSecret: factusConfig.clientSecret,
                              username: factusConfig.username,
                              password: factusConfig.password,
                              ambiente: factusConfig.ambiente,
                              rangoNumeracionId: factusConfig.rangoNumeracionId,
                              csrf_token: $('meta[name="csrf-token"]').attr('content')
                            },
                            dataType: 'json',
                            success: function (response) {
                              if (response.error) {
                                resultado.html('<div class="alert alert-danger"><i class="fa fa-times"></i> <strong>Error:</strong> ' + response.mensaje +
                                  (response.detalles ? '<br><small>' + response.detalles + '</small>' : '') + '</div>');
                              } else {
                                resultado.html('<div class="alert alert-success"><i class="fa fa-check"></i> <strong>¡Éxito!</strong> ' + response.mensaje +
                                  '<br><small>Token válido hasta: ' + response.expiracion + '</small></div>');
                                setTimeout(function () { location.reload(); }, 2000);
                              }
                            },
                            error: function () {
                              resultado.html('<div class="alert alert-danger"><i class="fa fa-times"></i> Error al conectar con el servidor.</div>');
                            },
                            complete: function () {
                              btn.prop('disabled', false).html('<i class="fa fa-key"></i> Autenticar y Obtener Tokens');
                            }
                          });
                        }
                      });
                    });
                  });
                  </script>
                </div>
              </div>
            </div>
          </div>


          <!--=====================================
          SECCIÓN 5: GESTIÓN DE PERFILES
          ======================================-->

          <?php
          $listaPerfiles = ControladorPerfiles::ctrObtenerPerfiles();
          $modulosSistema = ControladorPerfiles::ctrObtenerModulos();
          ?>

          <div class="box box-info collapsed-box" id="seccion-perfiles">
            <div class="box-header with-border">
              <h3 class="box-title"><i class="fa fa-shield"></i> Gestión de Perfiles de Usuario</h3>
              <div class="box-tools pull-right">
                <button type="button" class="btn btn-box-tool" data-widget="custom-collapse"><i class="fa fa-plus"></i></button>
              </div>
            </div>
            <div class="box-body" style="display: none;">

              <p class="text-muted">Crea y administra los perfiles de acceso. Cada perfil define qué módulos y acciones puede usar un usuario.</p>

              <button type="button" class="btn btn-success btn-sm mb-3" id="btnNuevoPerfil" style="margin-bottom:15px;">
                <i class="fa fa-plus"></i> Nuevo Perfil
              </button>

              <div class="table-responsive">
                <table class="table table-bordered table-hover" id="tablaPerfiles">
                  <thead>
                    <tr>
                      <th>Perfil</th>
                      <th>Descripción</th>
                      <th>Usuarios</th>
                      <th>Acciones</th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php foreach ($listaPerfiles as $perfil): ?>
                    <tr>
                      <td>
                        <strong><?php echo htmlspecialchars($perfil['nombre']); ?></strong>
                        <?php if ($perfil['es_sistema']): ?>
                          <span class="label label-warning" style="margin-left:5px;"><i class="fa fa-lock"></i> Sistema</span>
                        <?php endif; ?>
                      </td>
                      <td><?php echo htmlspecialchars($perfil['descripcion'] ?? ''); ?></td>
                      <td><span class="badge bg-blue"><?php echo $perfil['total_usuarios']; ?></span></td>
                      <td>
                        <?php if (!$perfil['es_sistema']): ?>
                          <button type="button" class="btn btn-xs btn-primary btn-editar-perfil"
                            data-id="<?php echo $perfil['id']; ?>"
                            data-nombre="<?php echo htmlspecialchars($perfil['nombre']); ?>"
                            data-descripcion="<?php echo htmlspecialchars($perfil['descripcion'] ?? ''); ?>">
                            <i class="fa fa-edit"></i> Editar
                          </button>
                          <?php if ($perfil['total_usuarios'] == 0): ?>
                          <button type="button" class="btn btn-xs btn-danger btn-eliminar-perfil"
                            data-id="<?php echo $perfil['id']; ?>"
                            data-nombre="<?php echo htmlspecialchars($perfil['nombre']); ?>">
                            <i class="fa fa-trash"></i> Eliminar
                          </button>
                          <?php else: ?>
                          <button type="button" class="btn btn-xs btn-default" disabled title="No se puede eliminar porque tiene usuarios asociados">
                            <i class="fa fa-trash"></i> Eliminar
                          </button>
                          <?php endif; ?>
                        <?php else: ?>
                          <button type="button" class="btn btn-xs btn-default btn-ver-permisos-admin"
                            data-id="<?php echo $perfil['id']; ?>">
                            <i class="fa fa-eye"></i> Ver Permisos
                          </button>
                        <?php endif; ?>
                      </td>
                    </tr>
                    <?php endforeach; ?>
                  </tbody>
                </table>
              </div>

            </div>
          </div>

        </div> <!-- /.box-body -->

        <!-- ===============================================
        MODAL: CREAR / EDITAR PERFIL
        ================================================ -->



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

</div>

<script>
  $(document).ready(function () {
    // Label dinámico para Tipo Persona
    function toggleLabelName() {
      var tipo = $("#tipoPersonaFactus").val();
      if (tipo == "1") {
        $("#labelNombreFactus").text("Razón Social");
      } else {
        $("#labelNombreFactus").text("Nombre Empresa");
      }
    }

    // Campo DV requerido dinámicamente para Persona Jurídica
    function toggleDvRequired() {
      var tipo = $("#tipoPersonaFactus").val();
      var isEditable = !$("#dvFactus").prop("readonly") && !$("#dvFactus").prop("disabled");
      if (tipo == "1" && isEditable) {
        $("#labelDvFactus").html('DV <span class="text-danger">*</span>');
        $("#dvFactus").prop("required", true);
      } else {
        $("#labelDvFactus").html('DV');
        $("#dvFactus").prop("required", false);
      }
    }

    $("#tipoPersonaFactus").change(function () {
      toggleLabelName();
      toggleDvRequired();
    });

    // Run on init
    toggleLabelName();
    toggleDvRequired();

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

    $("#nuevoLogoFactus").change(function () {

      var imagen = this.files[0];

      // Validar formato
      if (imagen["type"] != "image/jpeg" && imagen["type"] != "image/png") {

        $("#nuevoLogoFactus").val("");

        swal({
          title: "Error al subir la imagen",
          text: "¡La imagen debe estar en formato JPG o PNG!",
          type: "error",
          confirmButtonText: "¡Cerrar!"
        });

      } else if (imagen["size"] > 2000000) {

        $("#nuevoLogoFactus").val("");

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

          $("#previsualizarLogoFactus").attr("src", rutaImagen);

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

<!-- ===============================================
MODAL: CREAR / EDITAR PERFIL (FUERA DEL FORM)
================================================ -->
<div class="modal fade" id="modalPerfil" role="dialog">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header" style="background:#17a2b8;color:white;">
        <button type="button" class="close" data-dismiss="modal" style="color:white;">&times;</button>
        <h4 class="modal-title"><i class="fa fa-shield"></i> <span id="modalPerfilTitulo">Nuevo Perfil</span></h4>
      </div>
      <div class="modal-body">
        <input type="hidden" id="perfilId" value="">
        <input type="hidden" id="perfilEsSistema" value="0">

        <div class="row">
          <div class="col-md-6">
            <div class="form-group">
              <label>Nombre del Perfil *</label>
              <input type="text" class="form-control" id="perfilNombre" placeholder="Ej: Cajero, Supervisor...">
            </div>
          </div>
          <div class="col-md-6">
            <div class="form-group">
              <label>Descripción</label>
              <input type="text" class="form-control" id="perfilDescripcion" placeholder="Breve descripción del rol">
            </div>
          </div>
        </div>

        <hr>
        <h5><i class="fa fa-table"></i> Matriz de Permisos</h5>
        <p class="text-muted small">Marca las acciones permitidas para cada módulo. El permiso <strong>Ver</strong> es requerido para los demás.</p>

        <div id="adminFullAccessAlert" class="alert alert-warning" style="display:none;">
          <i class="fa fa-lock"></i> <strong>Administrador</strong> siempre tiene acceso total. Los permisos no son editables.
        </div>

        <div class="table-responsive">
          <table class="table table-bordered table-condensed" id="tablaMatrizPermisos">
            <thead>
              <tr style="background:#f4f4f4;">
                <th style="min-width:160px;">Módulo</th>
                <th class="text-center">Ver</th>
                <th class="text-center">Crear</th>
                <th class="text-center">Editar</th>
                <th class="text-center">Eliminar</th>
              </tr>
            </thead>
            <tbody>
              <?php
              $modulosMatriz = ControladorPerfiles::ctrObtenerModulos();
              $accionesNoAplica = [
                'inicio'               => ['crear','editar','eliminar','imprimir'],
                'usuarios'             => ['imprimir'],
                'productos'            => ['imprimir'],
                'proveedores'          => ['imprimir'],
                'clientes'             => ['imprimir'],
                'actividades'          => ['imprimir'],
                'seguimiento_leads'    => ['crear', 'editar', 'imprimir'],
                'gastos'               => ['imprimir'],
                'notificaciones'       => ['crear','editar','eliminar','imprimir'],
                'configuracion'        => ['crear','editar','eliminar','imprimir'],
                'reporte_ventas'       => ['crear','editar','eliminar'],
                'historial_stock'      => ['crear','editar','eliminar'],
                'traslados'            => ['editar', 'eliminar', 'imprimir'],
                'ordenes-visita'       => ['crear', 'editar', 'eliminar', 'imprimir'],
                'ordenes'              => ['imprimir'],
                'ventas'               => ['imprimir'],
                'cierres-caja'         => ['crear','editar','eliminar'],
                'documento_soporte'    => ['editar'],
                'notas_credito'        => ['editar'],
                'notas_ajuste'         => ['editar'],
                // factura_electronica, documento_soporte, notas_credito, notas_ajuste conservan 'imprimir' (Descargar)
              ];
              foreach ($modulosMatriz as $slug => $nombreModulo):
                // Ocultar Cierres de Caja si el control de caja está desactivado
                if ($slug == 'cierres-caja' && (!isset($configuracion["control_caja"]) || $configuracion["control_caja"] != 1)) {
                  continue;
                }

                // Ocultar Consulta de Ventas si está desactivado
                if ($slug == 'ordenes-visita' && (!isset($configuracion["consulta_ventas"]) || $configuracion["consulta_ventas"] != 1)) {
                  continue;
                }

                // Ocultar Documento Soporte y Notas de Ajuste si están desactivados
                if (($slug == 'documento_soporte' || $slug == 'notas_ajuste') && (!isset($configuracion["documento_soporte_activo"]) || $configuracion["documento_soporte_activo"] != 1)) {
                  continue;
                }

                // Ocultar Facturas Electrónicas y Notas Crédito si están desactivados
                if (($slug == 'factura_electronica' || $slug == 'notas_credito') && (!isset($configuracion["facturacion_electronica_activa"]) || $configuracion["facturacion_electronica_activa"] != 1)) {
                  continue;
                }

                // Ocultar Seguimiento a Leads y CRM si está desactivado
                if (($slug == 'seguimiento_leads' || $slug == 'crm') && (!isset($configuracion["seguimiento_leads_activo"]) || $configuracion["seguimiento_leads_activo"] != 1)) {
                  continue;
                }
                
                // Ocultar Traslados entre Bodegas si Sucursales está desactivado
                if ($slug == 'traslados' && isset($configuracion["activar_sucursales"]) && $configuracion["activar_sucursales"] == 0) {
                  continue;
                }
                
                $noAplica = $accionesNoAplica[$slug] ?? [];
              ?>
              <tr data-modulo="<?php echo $slug; ?>">
                <td><strong><?php echo $nombreModulo; ?></strong></td>
                <?php foreach (['ver','crear','editar','eliminar'] as $accion): ?>
                <td class="text-center">
                  <?php if (in_array($accion, $noAplica)): ?>
                    <span class="text-muted">—</span>
                  <?php else: ?>
                    <input type="checkbox"
                      class="perm-check perm-<?php echo $accion; ?>"
                      data-accion="<?php echo $accion; ?>"
                      data-modulo="<?php echo $slug; ?>"
                      style="width:18px;height:18px;cursor:pointer;">
                  <?php endif; ?>
                </td>
                <?php endforeach; ?>
              </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">Cancelar</button>
        <button type="button" class="btn btn-primary" id="btnGuardarPerfil">
          <i class="fa fa-save"></i> Guardar Perfil
        </button>
      </div>
    </div>
  </div>
</div>

<script>
$(document).ready(function() {
  console.log("Script Gestión de Perfiles cargado.");
  
  var ajaxUrl = 'ajax/perfiles.ajax.php';
  
  function getCsrfToken() {
    return $('meta[name="csrf-token"]').attr('content') || '';
  }

  function desmarcarTodo() {
    $('#tablaMatrizPermisos input[type=checkbox]').prop('checked', false).prop('disabled', false);
  }

  function abrirModalNuevo() {
    console.log("Abriendo modal para nuevo perfil.");
    $('#perfilId').val('');
    $('#perfilEsSistema').val('0');
    $('#modalPerfilTitulo').text('Nuevo Perfil');
    $('#perfilNombre').val('').prop('readonly', false);
    $('#perfilDescripcion').val('');
    $('#adminFullAccessAlert').hide();
    $('#btnGuardarPerfil').show();
    desmarcarTodo();
    $('#modalPerfil').modal('show');
  }

  function abrirModalEditar(id, nombre, descripcion) {
    console.log("Abriendo modal para editar perfil:", id);
    $('#perfilId').val(id);
    $('#perfilEsSistema').val('0');
    $('#modalPerfilTitulo').text('Editar Perfil: ' + nombre);
    $('#perfilNombre').val(nombre).prop('readonly', false);
    $('#perfilDescripcion').val(descripcion);
    $('#adminFullAccessAlert').hide();
    $('#btnGuardarPerfil').show();
    desmarcarTodo();

    $.post(ajaxUrl, {accion: 'obtenerPermisos', id_perfil: id}, function(res) {
      if (res.permisos) {
        $.each(res.permisos, function(modulo, acciones) {
          $.each(acciones, function(accion, val) {
            if (val) {
              $('[data-modulo="' + modulo + '"][data-accion="' + accion + '"]').prop('checked', true);
            }
          });
        });
      }
      $('#modalPerfil').modal('show');
    }, 'json');
  }

  function abrirModalAdmin(id) {
    $('#perfilId').val(id);
    $('#perfilEsSistema').val('1');
    $('#modalPerfilTitulo').text('Perfil: Administrador (Solo lectura)');
    $('#perfilNombre').val('Administrador').prop('readonly', true);
    $('#perfilDescripcion').val('Acceso total al sistema. Permisos no editables.');
    $('#adminFullAccessAlert').show();
    $('#btnGuardarPerfil').hide();
    $('#tablaMatrizPermisos input[type=checkbox]').prop('checked', true).prop('disabled', true);
    $('#modalPerfil').modal('show');
  }

  // --- EVENTOS (DELEGADOS) ---
  
  $(document).on('click', '#btnNuevoPerfil', abrirModalNuevo);

  $(document).on('click', '.btn-editar-perfil', function() {
    abrirModalEditar($(this).data('id'), $(this).data('nombre'), $(this).data('descripcion'));
  });

  $(document).on('click', '.btn-ver-permisos-admin', function() {
    abrirModalAdmin($(this).data('id'));
  });

  $(document).on('change', '.perm-ver', function() {
    var modulo = $.trim($(this).attr('data-modulo') || $(this).data('modulo') || '');
    var checked = $(this).is(':checked');
    if (!checked) {
      if (modulo === 'traslados' || modulo === 'traslado' || modulo === 'productos' || modulo === 'producto' || modulo === 'clientes' || modulo === 'cliente' || modulo === 'ordenes' || modulo === 'orden' || modulo === 'ventas' || modulo === 'venta' || modulo === 'gastos' || modulo === 'gasto') {
        $('[data-modulo="' + modulo + '"]').not(this).not('[data-accion="crear"]').prop('checked', false);
      } else {
        $('[data-modulo="' + modulo + '"]').not(this).prop('checked', false);
      }
    }
  });

  $(document).on('change', '.perm-check:not(.perm-ver)', function() {
    if ($(this).is(':checked')) {
      var modulo = $.trim($(this).attr('data-modulo') || $(this).data('modulo') || '');
      var accion = $(this).attr('data-accion') || '';
      if (accion === 'crear' && (modulo === 'traslados' || modulo === 'traslado' || modulo === 'productos' || modulo === 'producto' || modulo === 'clientes' || modulo === 'cliente' || modulo === 'ordenes' || modulo === 'orden' || modulo === 'ventas' || modulo === 'venta' || modulo === 'gastos' || modulo === 'gasto')) {
        // 'crear' is independent of 'ver' for these modules
      } else {
        $('[data-modulo="' + modulo + '"][data-accion="ver"]').prop('checked', true);
      }
    }
  });


  $(document).on('click', '.btn-eliminar-perfil', function() {
    var id = $(this).data('id');
    var nombre = $(this).data('nombre');
    swal({
      title: '¿Eliminar perfil "' + nombre + '"?',
      text: 'Esta acción no se puede deshacer.',
      type: 'warning',
      showCancelButton: true,
      confirmButtonText: 'Sí, eliminar',
      cancelButtonText: 'Cancelar',
      confirmButtonColor: '#d9534f'
    }).then(function(result) {
      if (result.value) {
        $.post(ajaxUrl, {accion: 'eliminarPerfil', id_perfil: id, csrf_token: getCsrfToken()}, function(res) {
          if (res.resultado === 'ok') {
            swal({ title: 'Eliminado', text: 'El perfil fue eliminado.', type: 'success' }).then(function() { location.reload(); });
          } else if (res.resultado === 'error_usuarios') {
            swal({ title: 'Error', text: 'No se puede eliminar: hay usuarios con este perfil.', type: 'error' });
          } else {
            swal({ title: 'Error', text: 'No se pudo eliminar el perfil.', type: 'error' });
          }
        }, 'json');
      }
    });
  });

  $(document).on('click', '#btnGuardarPerfil', function() {
    console.log("Iniciando guardado de perfil...");
    var id = $('#perfilId').val();
    var nombre = $('#perfilNombre').val().trim();
    var descripcion = $('#perfilDescripcion').val().trim();

    if (!nombre) {
      swal({ title: 'Atención', text: 'El nombre del perfil es requerido.', type: 'warning' });
      return;
    }

    var permisos = {};
    $('#tablaMatrizPermisos tr[data-modulo]').each(function() {
      var modulo = $(this).data('modulo');
      permisos[modulo] = {};
      $(this).find('input[type=checkbox]').each(function() {
        var accion = $(this).data('accion');
        permisos[modulo][accion] = $(this).is(':checked') ? 1 : 0;
      });

      // Automatización: Si puede VER, habilitar automáticamente IMPRIMIR (Descargar)
      permisos[modulo]['imprimir'] = (permisos[modulo]['ver'] == 1) ? 1 : 0;
    });

    var accion = id ? 'actualizarPerfil' : 'crearPerfil';
    var data = {
      accion: accion,
      nombre: nombre,
      descripcion: descripcion,
      csrf_token: getCsrfToken()
    };
    if (id) data.id_perfil = id;

    $.each(permisos, function(modulo, acciones) {
      $.each(acciones, function(accionKey, val) {
        data['permisos[' + modulo + '][' + accionKey + ']'] = val;
      });
    });

    console.log("Datos a enviar:", data);

    $.post(ajaxUrl, data, function(res) {
      console.log("Respuesta recibida:", res);
      if (res.resultado === 'ok') {
        $('#modalPerfil').modal('hide');
        swal({ title: 'Guardado', text: 'El perfil fue guardado correctamente.', type: 'success' }).then(function() { location.reload(); });
      } else if (res.resultado === 'error_nombre') {
        swal({ title: 'Error', text: 'El nombre del perfil es requerido.', type: 'error' });
      } else if (res.resultado && res.resultado.startsWith('error')) {
        swal({ title: 'Error', text: 'No se pudo guardar: ' + res.resultado, type: 'error' });
      } else {
        swal({ title: 'Error', text: 'Respuesta inesperada del servidor.', type: 'error' });
      }
    }, 'json').fail(function(xhr) {
      console.error("Fallo AJAX:", xhr.responseText);
      swal({ title: 'Error', text: 'No se pudo conectar con el servidor.', type: 'error' });
    });
  });

  $('#modalPerfil').on('hidden.bs.modal', function() {
    desmarcarTodo();
    $('#perfilId').val('');
    $('#perfilNombre').prop('readonly', false);
    $('#btnGuardarPerfil').show();
    $('#adminFullAccessAlert').hide();
  });
});
</script>
  </section>

</div>
