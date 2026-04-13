<style>
  .formulario-fechas-container {
    max-width: 300px;
    padding: 15px;
    border-radius: 10px;
    background-color: #ffffff;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    margin-bottom: 20px;
  }

  .formulario-fechas label {
    font-weight: 600;
    margin-top: 10px;
  }

  .formulario-fechas select,
  .formulario-fechas input[type="date"] {
    border-radius: 8px;
    margin-bottom: 10px;
  }

  .d-none {
    display: none !important;
  }





  /* 1. LÓGICA DESKTOP-FIRST: Ocultar botón de expansión por defecto en Facturas */
  .tablaFacturasListado td.dtr-control:before,
  .tablaFacturasListado th.dtr-control:before {
    display: none !important;
    content: "" !important;
  }

  .tablaFacturasListado td.dtr-control,
  .tablaFacturasListado th.dtr-control {
    padding-left: 8px !important;
    cursor: default !important;
  }

  /* 2. ACTIVACIÓN EXCLUSIVA PARA MÓVIL (Menos de 767px) */
  @media (max-width: 767px) {
    .tablaFacturasListado td.dtr-control {
      position: relative !important;
      padding-left: 30px !important;
      cursor: pointer !important;
    }

    .tablaFacturasListado td.dtr-control:before {
      top: 50% !important;
      left: 5px !important;
      height: 18px !important;
      width: 18px !important;
      margin-top: -9px !important;
      display: block !important;
      position: absolute !important;
      color: white !important;
      border: 2px solid white !important;
      border-radius: 14px !important;
      box-shadow: 0 0 3px #444 !important;
      box-sizing: content-box !important;
      text-align: center !important;
      text-indent: 0 !important;
      font-family: 'Courier New', Courier, monospace !important;
      font-weight: bold !important;
      line-height: 18px !important;
      content: '+' !important;
      background-color: #3c8dbc !important; /* Azul al estar contraído (+) */
    }

    .tablaFacturasListado tr.parent td.dtr-control:before {
      content: '-' !important;
      background-color: #dd4b39 !important; /* Rojo al estar expandido (-) */
    }

    /* Botones de acción compactos en móvil */
    .tablaFacturasListado td:last-child .btn {
      padding: 1px 5px !important;
      font-size: 12px !important;
      line-height: 1.5 !important;
    }

    .tablaFacturasListado td:last-child .btn-group {
      display: flex;
      gap: 2px;
    }

    /* Forzar que las acciones tengan espacio */
    .tablaFacturasListado td:last-child {
      width: 1% !important;
      white-space: nowrap !important;
      text-align: right !important;
    }
  }
</style>

<!-- Estilos para campo observación -->
<style>
  .celda-observacion {
    background: #fff9e6;
    padding: 8px;
    border-radius: 3px;
    font-size: 12px;
    color: #666;
    border-left: 2px solid #f39c12;
    cursor: text;
    min-height: 30px;
  }

  .celda-observacion:empty:before {
    content: "Escribe una observación...";
    color: #999;
    font-style: italic;
  }

  .celda-observacion:focus {
    outline: 2px solid #f39c12;
    background: #fffef5;
  }
</style>









<!-- DateRangePicker -->
<link rel="stylesheet" href="vistas/bower_components/bootstrap-daterangepicker/daterangepicker.css">


<?php

// Obtener configuración del sistema
$configuracion = ControladorConfiguracion::ctrObtenerConfiguracion();
$moneda = !empty($configuracion["moneda"]) ? $configuracion["moneda"] : "$";
$formatoCodigoVenta = !empty($configuracion["formato_codigo_venta"]) ? $configuracion["formato_codigo_venta"] : "";


// Obtener prefijo de Factus para borradores
$rangoActivoFactus = ModeloFactus::mdlObtenerRangoActivo();
$prefijoDian = $rangoActivoFactus ? $rangoActivoFactus["prefijo"] : "";

$xml = ControladorVentas::ctrDescargarXML();

if ($xml) {

  rename($_GET["xml"] . ".xml", "xml/" . $_GET["xml"] . ".xml");
  echo '<a class="btn btn-block btn-success abrirXML" archivo="xml/' . $_GET["xml"] . '.xml" href="ventas">Se ha creado correctamente el archivo XML<span class="fa fa-times pull-right"></span></a>';
}
?>

<div class="content-wrapper">
  <section class="content-header">


    <h1>
      Administrar Facturas electronicas
    </h1>

    <ol class="breadcrumb">
      <li><a href="inicio"><i class="fa fa-dashboard"></i> Inicio</a></li>
      <li class="active">Administrar Facturas electronicas</li>
    </ol>

  </section>

  <section class="content">

    <div class="box">

      <div class="box-header with-border">


        <?php if (puedeAccion('factura_electronica', 'crear')): ?>
          <a href="crear-factura-electronica">
            <button class="btn btn-primary">
              <i class="fa fa-plus"></i> Crear Factura Electrónica
            </button>
          </a>
        <?php endif; ?>


        <div class="pull-right contenedor-filtros">

          <form method="GET" action="index.php" style="display: flex; align-items: center; gap: 15px; flex-wrap: wrap;">

            <input type="hidden" name="ruta" value="facturas-electronicas">
            <input type="hidden" name="fechaInicial" id="fechaInicial"
              value="<?php echo isset($_GET["fechaInicial"]) ? $_GET["fechaInicial"] : null; ?>">
            <input type="hidden" name="fechaFinal" id="fechaFinal"
              value="<?php echo isset($_GET["fechaFinal"]) ? $_GET["fechaFinal"] : null; ?>">

            <?php CSRF::insertToken(); ?>

            <!-- Filtro por cliente -->
            <div style="display: flex; align-items: center; gap: 8px;">
              <span class="hidden-xs"><b>Filtrar por Cliente:</b></span>
              <div class="input-group" style="width: 200px;">
                <span class="input-group-addon" style="background: #fcfcfc; border-color: #d2d6de;">
                  <i class="fa fa-search text-primary"></i>
                </span>
                <select name="cliente" class="form-control select2 select-cliente" style="width: 100%;">
                  <option value="">Seleccionar cliente...</option>
                  <?php
                  $item = null;
                  $valor = null;
                  $clientes = ControladorClientes::ctrMostrarClientes($item, $valor);

                  foreach ($clientes as $key => $valueCliente) {
                    $selected = (isset($_GET['cliente']) && $_GET['cliente'] == $valueCliente["id"]) ? 'selected' : '';
                    echo '<option value="' . e($valueCliente["id"]) . '" ' . $selected . '>' . e($valueCliente["nombre"]) . '</option>';
                  }
                  ?>
                </select>
              </div>
            </div>

            <!-- Filtro por usuario -->
            <div style="display: flex; align-items: center; gap: 8px;">
              <span class="hidden-xs"><b>Filtrar por Vendedor:</b></span>
              <div class="input-group" style="width: 200px;">
                <span class="input-group-addon" style="background: #fcfcfc; border-color: #d2d6de;">
                  <i class="fa fa-search text-primary"></i>
                </span>
                <select name="usuario" class="form-control select2 select-usuario" style="width: 100%;">
                  <option value="">Seleccionar usuario...</option>
                  <?php
                  $item = null;
                  $valor = null;
                  $usuarios = ControladorUsuarios::ctrMostrarUsuarios($item, $valor);

                  foreach ($usuarios as $key => $valueUsuario) {
                    $selected = (isset($_GET['usuario']) && $_GET['usuario'] == $valueUsuario["id"]) ? 'selected' : '';
                    echo '<option value="' . e($valueUsuario["id"]) . '" ' . $selected . '>' . e($valueUsuario["nombre"]) . '</option>';
                  }
                  ?>
                </select>
              </div>
            </div>


            <!-- Botón Rango de Fecha -->
            <button type="button" class="btn btn-default" id="daterange-btn-factus">
              <span>
                <i class="fa fa-calendar"></i> Rango de fecha
              </span>
              <i class="fa fa-caret-down"></i>
            </button>

            <!-- Botón Buscar -->
            <button type="submit" class="btn btn-primary" title="Filtrar">
              <i class="fa fa-search"></i>
            </button>

            <!-- Botón Limpiar -->
            <a href="index.php?ruta=facturas-electronicas" class="btn btn-default" title="Limpiar">
              <i class="fa fa-refresh"></i>
            </a>

          </form>

        </div>

        <style>
          @media (max-width: 767px) {
            .box-header .btn-primary:not([type="submit"]) {
              width: 100%;
              margin-bottom: 10px;
            }

            .pull-right.contenedor-filtros {
              float: none !important;
              width: 100%;
            }

            .pull-right.contenedor-filtros form {
              flex-direction: column;
              align-items: stretch !important;
              width: 100%;
              gap: 10px !important;
            }

            .pull-right.contenedor-filtros form .input-group,
            .pull-right.contenedor-filtros form div {
              width: 100% !important;
            }

            #daterange-btn-factus,
            [type="submit"],
            .btn-default {
              width: 100% !important;
              margin-bottom: 5px;
            }
          }
        </style>


      </div>

      <div class="box-body">

        <style>
          .loader-container {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 60px;
            background: #fff;
            margin-bottom: 20px;
            transition: opacity 0.3s ease;
          }

          .loader-container i {
            font-size: 45px;
            color: #3c8dbc;
            margin-bottom: 15px;
          }

          .loader-container span {
            font-size: 16px;
            color: #666;
            font-weight: 500;
          }

          /* Ocultar tabla mientras carga para evitar saltos */
          .tablaFacturasListado:not(.datatable-ready) {
            visibility: hidden;
            height: 0;
            overflow: hidden;
            opacity: 0;
          }

          .tablaFacturasListado.datatable-ready {
            transition: opacity 0.5s ease;
            opacity: 1;
          }
        </style>

        <div class="box-body">

          <div id="loader-table" class="loader-container">
            <i class="fa fa-refresh fa-spin"></i>
            <span>Cargando Facturas Electrónicas...</span>
          </div>

          <div class="tabla-facturas table-responsive">
            <table id="tablaFacturasElectronicas" class="table table-bordered table-striped tablaFacturasListado display nowrap" width="100%">

            <thead>
              <tr>
                <th>Código</th>
                <th>Cliente</th>
                <th>Vendedor</th>
                <th>Forma de pago</th>
                <th>Imagen</th>

                <th>Total</th>
                <th>Estado DIAN</th>
                <th><i class="fa fa-magic"></i> Notas</th>
                <th><i class="fa fa-pencil-square"></i> Observación</th>
                <th>Fecha</th>
                <th>Acciones</th>
              </tr>
            </thead>

            <tbody>

              <?php

              // Determinar filtros activos
              $fechaInicial = null;
              $fechaFinal = null;
              $clienteId = null;
              $usuarioId = null;
              $mensajeFiltro = "";

              // Filtro por fechas
              if (isset($_GET["fechaInicial"]) && isset($_GET["fechaFinal"])) {
                $fechaInicial = $_GET["fechaInicial"];
                $fechaFinal = $_GET["fechaFinal"];
                $mensajeFiltro .= "Filtrando desde $fechaInicial hasta $fechaFinal";
              }

              // Filtro por cliente
              if (isset($_GET["cliente"]) && !empty($_GET["cliente"])) {
                $clienteId = $_GET["cliente"];

                // Obtener nombre del cliente para mostrar
                $clienteInfo = ControladorClientes::ctrMostrarClientes("id", $clienteId);
                $nombreClienteFiltro = $clienteInfo["nombre"];

                if ($mensajeFiltro != "") {
                  $mensajeFiltro .= " | ";
                }
                $mensajeFiltro .= "Cliente: $nombreClienteFiltro";
              }

              // Filtro por usuario
              if (isset($_GET["usuario"]) && !empty($_GET["usuario"])) {
                $usuarioId = $_GET["usuario"];

                // Obtener nombre del usuario para mostrar
                $usuarioInfo = ControladorUsuarios::ctrMostrarUsuarios("id", $usuarioId);
                $nombreUsuarioFiltro = $usuarioInfo["nombre"];

                if ($mensajeFiltro != "") {
                  $mensajeFiltro .= " | ";
                }
                $mensajeFiltro .= "Usuario: $nombreUsuarioFiltro";
              }

              // Mostrar mensaje de filtros activos
              if ($mensajeFiltro != "") {
                echo "<p style='background: #d9edf7; padding: 10px; border-left: 4px solid #31708f; color: #31708f;'><i class='fa fa-filter'></i> $mensajeFiltro</p>";
              } else {
                echo "<p style='background: #d4edda; padding: 10px; border-left: 4px solid #28a745; color: #155724;'><i class='fa fa-file-text'></i> Mostrando solo ventas con factura electrónica generada</p>";
              }

              // Obtener ventas según filtros
              $respuesta = ControladorVentas::ctrRangoFechasVentasPorEstado($fechaInicial, $fechaFinal, "venta");

              // 🔹 FILTRO ESPECÍFICO PARA FACTURAS ELECTRÓNICAS
              // Solo mostrar ventas con factura emitida (numero_factura) O borradores FE (tienen resolucion_id)
              // Las ventas regulares NO tienen resolucion_id ni numero_factura
              $respuesta = array_filter($respuesta, function ($venta) {
                return !empty($venta["numero_factura"]) || !empty($venta["resolucion_id"]);
              });

              // Si hay filtro por cliente, filtrar el resultado
              if ($clienteId !== null) {
                $respuesta = array_filter($respuesta, function ($venta) use ($clienteId) {
                  return $venta["id_cliente"] == $clienteId;
                });
              }

              // Si hay filtro por usuario, filtrar el resultado
              if ($usuarioId !== null) {
                $respuesta = array_filter($respuesta, function ($venta) use ($usuarioId) {
                  return $venta["id_vendedor"] == $usuarioId;
                });
              }


              // Pre-procesar borradores para numeración predictiva
              $borradoresTotal = 0;
              foreach ($respuesta as $v) {
                if (empty($v["numero_factura"]) && in_array(($v["estado_dian"] ?? 'pendiente'), ['pendiente', 'creada', 'borrador'])) {
                  $borradoresTotal++;
                }
              }

              $siguienteConsecutivoBase = ModeloFactus::mdlObtenerSiguienteConsecutivoFactus();
              $borradoresEncontrados = 0;

              // Obtener pre-cargado las IDs de ventas que tienen nota crédito para evitar N+1
              $idsVentas = array_column($respuesta, 'id');
              $ventasConNC = ModeloFactus::mdlObtenerVentasConNotaCredito($idsVentas);

              $contador = 1;
              foreach ($respuesta as $key => $value) {

                // Lógica de numeración predictiva para borradores
                if (!empty($value["numero_factura"])) {
                  $numeroMostrar = $value["numero_factura"];
                  $esBorrador = false;
                } else {
                  // Es borrador: Calculamos número predicho si es un estado de borrador real
                  if (in_array(($value["estado_dian"] ?? 'pendiente'), ['pendiente', 'creada', 'borrador'])) {
                    $numeroMostrar = $prefijoDian . ($siguienteConsecutivoBase - 1 - $borradoresEncontrados);
                    $esBorrador = true;
                    $borradoresEncontrados++;
                  } else {
                    // Otros casos (ej. rechazada sin numero): Mostramos codigo local o marcador
                    $numeroMostrar = $prefijoDian . $value["codigo"];
                    $esBorrador = false;
                  }
                }

                echo '<tr data-fe-id="' . e($value['id']) . '">';
                echo '<td' . ($esBorrador ? ' class="text-yellow" style="font-weight:bold"' : '') . '>' . e($numeroMostrar) . '</td>';

                // Usar nombres que ya vienen del JOIN en la consulta SQL
                $nombreCliente = !empty($value["nombre_cliente"]) ? $value["nombre_cliente"] : "Cliente no encontrado";
                $nombreVendedor = !empty($value["nombre_vendedor"]) ? $value["nombre_vendedor"] : "Vendedor no encontrado";

                echo '<td>

                                  <span class="btnVerClienteDesdeVenta"
                                        data-toggle="modal"
                                        data-target="#modalEditarCliente"
                                        idCliente="' . e($value["id_cliente"]) . '"
                                        style="cursor: pointer; color: #337ab7; text-decoration: underline;">
                                      ' . e($nombreCliente) . '
                                  </span>
                              </td>';

                echo '<td>' . e($nombreVendedor) . '</td> 

                        <td>' . e($moneda) . ' ' . e($value["metodo_pago"]) . '</td>';

                // Validación de la foto
                if ($value["imagen"] != "") {
                  echo '<td><img src="' . $value["imagen"] . '" class="img-thumbnail img-ampliar-venta" width="40px" style="cursor: pointer;" data-imagen="' . $value["imagen"] . '" data-idventa="' . $value["id"] . '"></td>';
                } else {
                  echo '<td><img src="vistas/img/ventas/default/sinventa.png" class="img-thumbnail img-ampliar-venta" width="40px" style="cursor: pointer;" data-imagen="vistas/img/ventas/default/sinventa.png" data-idventa="' . $value["id"] . '"></td>';
                }

                echo '<td>' . e($moneda) . ' ' . e(number_format($value["total"], 2)) . '</td>';

                // Estado DIAN
                $estadoDian = isset($value["estado_dian"]) ? $value["estado_dian"] : 'pendiente';
                $badgeDian = '';
                if ($estadoDian == 'aceptada' || $estadoDian == 'enviada') {
                  $badgeDian = '<button class="btn btn-success btn-xs">Exitosa</button>';
                } elseif ($estadoDian == 'borrador' || $estadoDian == 'creada' || $estadoDian == 'pendiente') {
                  $badgeDian = '<button class="btn btn-warning btn-xs">Borrador</button>';
                } elseif ($estadoDian == 'rechazada') {
                  $badgeDian = '<button class="btn btn-danger btn-xs">Rechazada</button>';
                } else {
                  $badgeDian = '<button class="btn btn-danger btn-xs">Pendiente</button>';
                }
                echo '<td>' . $badgeDian . '</td>';

                echo '<td>' . e($value['notas']) . '</td>';

                $estadoDian = isset($value["estado_dian"]) ? $value["estado_dian"] : "";
                $esEditable = ($estadoDian != "enviada" && $estadoDian != "aceptada");

                $contentEditableAttr = $esEditable ? 'contenteditable="true"' : '';
                $claseEditable = $esEditable ? 'celda-observacion' : '';

                echo '<td ' . $contentEditableAttr . ' class="' . $claseEditable . '" data-id="' . e($value["id"]) . '">' . e($value["observacion"]) . '</td>

                        
                       <td>' . e($value["fecha"]) . '</td>

                        <td>
                          <div class="btn-group col-acciones">

                               <button class="btn btn-info btnEditarVenta" idVenta="' . $value["id"] . '" title="Ver Detalles">
                                <i class="fa fa-eye"></i>
                               </button>

                             ' . (!empty($value["qr_data"]) ? '<a class="btn btn-success" href="' . $value["qr_data"] . '" target="_blank" data-toggle="tooltip" title="Ver en DIAN"><i class="fa fa-external-link"></i></a>' : '') . '

                             ';
                if (puedeAccion('factura_electronica', 'editar')) {
                  echo ((isset($value["estado_dian"]) && $value["estado_dian"] == "creada") ?
                    '<button class="btn btnFirmarFactura" style="background-color: black; color: white;" idVenta="' . $value["id"] . '" title="Firmar y Enviar a DIAN">
                                        <i class="fa fa-paper-plane"></i>
                                    </button>' : '') . '
                                    ' . ((isset($value["estado_dian"]) && in_array($value["estado_dian"], ['creada', 'pendiente'])) ?
                    '<a class="btn btn-warning" href="index.php?ruta=editar-factura-electronica&idVenta=' . $value["id"] . '" title="Editar Borrador">
                                        <i class="fa fa-pencil"></i>
                                    </a>' : '');
                }
                echo ' ';

                if ($estadoDian == 'aceptada' || $estadoDian == 'enviada') {
                  echo ' <button class="btn btn-primary btnEnviarEmail" idVenta="' . $value["id"] . '" nombreCliente="' . $nombreCliente . '" emailCliente="' . $value["email_cliente"] . '" title="Enviar por Correo">
                                <i class="fa fa-envelope"></i>
                              </button>';
                }

                echo ' ';

                // Botón de las Notas Crédito (solo si la factura tiene NC)
                if (in_array($value["id"], $ventasConNC)) {
                  echo '<button class="btn btn-warning btnVerNotasCredito" idVenta="' . $value["id"] . '" data-toggle="modal" data-target="#modalNotasCredito" title="Ver Notas Crédito">
                                       <i class="fa fa-list"></i>
                                     </button>';
                }

                if (puedeAccion('factura_electronica', 'eliminar')) {
                  // Solo mostrar botón eliminar si la factura NO ha sido firmada/aceptada
                  $estadosNoEliminables = ['enviada', 'aceptada'];
                  if (!in_array($value["estado_dian"], $estadosNoEliminables)) {
                    echo '<button class="btn btn-danger btnEliminarVenta" idVenta="' . $value["id"] . '" title="Eliminar Borrador">
                                        <i class="fa fa-trash"></i>
                                      </button>';
                  }
                }

                echo '</div>
                        </td>


                      </tr>';
              }
              ?>

            </tbody>

          </table>
        </div>
        <?php
        $eliminarVenta = new ControladorVentas();
        $eliminarVenta->ctrEliminarVenta();
        ?>



        <!-- Modal para ampliar/editar imagen de venta -->
        <div class="modal fade" id="modalAmpliarImagenVenta" tabindex="-1" role="dialog">
          <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
              <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                  <span aria-hidden="true">&times;</span>
                </button>
                <h4 class="modal-title">Imagen de la Venta</h4>
              </div>
              <div class="modal-body text-center">
                <img id="imagenVentaAmpliada" src="" class="img-responsive"
                  style="max-width: 100%; margin: 0 auto; margin-bottom: 20px;">

                <hr>

                <div class="form-group">
                  <label>Cambiar Imagen de la Venta</label>
                  <input type="file" class="form-control nuevaImagenVenta" accept="image/*">
                  <p class="help-block">Peso máximo de la imagen 2MB</p>
                </div>

                <input type="hidden" id="idVentaImagen">
              </div>
              <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary btnGuardarImagenVenta">Guardar Imagen</button>
              </div>
            </div>
          </div>
        </div>




      </div>
    </div>
  </section>
</div>



<!--==========================================================================
MODAL GENERAR NOTA CRÉDITO
===========================================================================-->
<div class="modal fade" id="modalNotaCredito" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header bg-danger">
        <button type="button" class="close" data-dismiss="modal">&times;</button>
        <h4 class="modal-title"><i class="fa fa-undo"></i> Generar Nota Crédito</h4>
      </div>
      <div class="modal-body">
        <input type="hidden" id="ncIdVenta">

        <div class="alert alert-info">
          <p><strong>Factura:</strong> <span id="ncNumeroFactura"></span></p>
          <p><strong>Cliente:</strong> <span id="ncCliente"></span></p>
          <p><strong>Total:</strong> $<span id="ncTotal"></span></p>
        </div>

        <div class="form-group">
          <label>Tipo de Nota Crédito:</label>
          <select class="form-control" id="ncTipo">
            <option value="anulacion_total">Anulación Total</option>
            <option value="devolucion_parcial">Devolución Parcial</option>
            <option value="ajuste_precio">Ajuste de Precio</option>
            <option value="descuento_posterior">Descuento Posterior</option>
          </select>
        </div>

        <div class="form-group">
          <label>Motivo <span class="text-danger">*</span>:</label>
          <textarea class="form-control" id="ncMotivo" rows="3"
            placeholder="Ej: Error en digitación de precio, producto defectuoso, etc."></textarea>
        </div>

        <div class="alert alert-warning">
          <i class="fa fa-warning"></i> Esta acción generará una Nota Crédito oficial ante la DIAN y <strong>no puede
            revertirse</strong>.
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">Cancelar</button>
        <?php if (puedeAccion('factura_electronica', 'crear')): ?>
          <button type="button" class="btn btn-danger" id="btnConfirmarNC">
            <i class="fa fa-check"></i> Generar Nota Crédito
          </button>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>

<!--==========================================================================
MODAL EDITAR CLIENTE
===========================================================================-->

<!-- Modal -->
<div id="modalEditarCliente" class="modal fade" role="dialog">

  <div class="modal-dialog">

    <div class="modal-content">

      <form role="form" method="post">

        <!--=====================================
      CABEZA DEL MODAL
      ======================================-->

        <div class="modal-header" style="background:#3c8dbc; color: white">

          <button type="button" class="close" data-dismiss="modal">&times;</button>
          <h4 class="modal-title">Ver cliente</h4>

        </div>

        <!--=====================================
      CUERPO DEL MODAL
      ======================================-->

        <div class="modal-body">
          <div class="box-body">

            <!-- FILA 1: DATOS PERSONALES -->
            <div class="row">
              <div class="col-xs-12 col-md-6">
                <!-- entrada para nombre -->
                <div class="form-group">
                  <label>Nombre:</label>
                  <div class="input-group">
                    <span class="input-group-addon"><i class="fa fa-user"></i></span>
                    <input type="text" class="form-control input-lg" name="editarCliente" id="editarCliente" readonly>
                    <input type="hidden" id="idCliente" name="idCliente">
                  </div>
                </div>
              </div>

              <div class="col-xs-12 col-md-6">
                <!-- entrada para documento ID -->
                <div class="form-group">
                  <label>Documento:</label>
                  <div class="input-group">
                    <span class="input-group-addon"><i class="fa fa-key"></i></span>
                    <input type="number" min="0" class="form-control input-lg" name="editarDocumentoId"
                      id="editarDocumentoId" placeholder="Documento" readonly>
                  </div>
                </div>
              </div>
            </div>

            <!-- FILA 2: CONTACTO -->
            <div class="row">
              <div class="col-xs-12 col-md-6">
                <!-- entrada para Email -->
                <div class="form-group">
                  <label>Email:</label>
                  <div class="input-group">
                    <span class="input-group-addon"><i class="fa fa-envelope"></i></span>
                    <input type="email" class="form-control input-lg" name="editarEmail" id="editarEmail"
                      placeholder="Correo Electrónico" readonly>
                  </div>
                </div>
              </div>

              <div class="col-xs-12 col-md-6">
                <!-- entrada para telefono -->
                <div class="form-group">
                  <label>Teléfono:</label>
                  <div class="input-group">
                    <span class="input-group-addon"><i class="fa fa-phone"></i></span>
                    <input type="text" class="form-control input-lg" name="editarTelefono" id="editarTelefono"
                      data-inputmask="'mask':'(999) 999-9999'" data-mask placeholder="Celular" readonly>
                  </div>
                </div>
              </div>
            </div>

            <hr style="margin-top: 5px; margin-bottom: 15px;">

            <!-- FILA 3: UBICACIÓN Y ESTADO -->
            <div class="row">
              <div class="col-xs-12 col-md-6">
                <!-- entrada para la direccion -->
                <div class="form-group">
                  <label>Dirección:</label>
                  <div class="input-group">
                    <span class="input-group-addon"><i class="fa fa-home"></i></span>
                    <input type="text" class="form-control input-lg" name="editarDireccion" id="editarDireccion"
                      placeholder="Dirección" required readonly>
                  </div>
                </div>
              </div>

              <div class="col-xs-12 col-md-6">
                <!-- entrada para la ciudad (Municipio) -->
                <div class="form-group">
                  <label>Municipio:</label>
                  <div class="input-group">
                    <span class="input-group-addon"><i class="fa fa-map-marker"></i></span>
                    <input type="text" class="form-control input-lg" name="editarCiudad" id="editarCiudad"
                      placeholder="Municipio" readonly>
                  </div>
                </div>
              </div>
            </div>

            <!-- FILA 4: ESTADO Y NOTAS -->
            <div class="row">
              <div class="col-xs-12 col-md-6">
                <!-- entrada para estado -->
                <div class="form-group">
                  <label>Estado:</label>
                  <div class="input-group">
                    <span class="input-group-addon"><i class="fa fa-flag"></i></span>
                    <input type="text" class="form-control input-lg" id="editarEstado" name="editarEstado" readonly
                      style="background-color: #f4f4f4; cursor: not-allowed;">
                  </div>
                </div>
              </div>

              <div class="col-xs-12 col-md-12">
                <!-- entrada para nota -->
                <div class="form-group">
                  <label>Notas:</label>
                  <div class="input-group">
                    <span class="input-group-addon"><i class="fa fa-sticky-note"></i></span>
                    <textarea class="form-control input-lg" name="editarNota" id="editarNota" placeholder="Notas"
                      readonly style="height: 80px; resize: none;"></textarea>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!--=====================================
        PIE DEL MODAL
        ======================================-->

        <div class="modal-footer">
          <button type="button" class="btn btn-default pull-left" data-dismiss="modal">Salir</button>
          <!--<button type="submit" class="btn btn-primary">Guardar cambios</button>-->
        </div>

      </form>
    </div>
  </div>
</div>


<!--Ruta Clientes.js-->
<script src="vistas/js/ventas.js"></script>
<script src="vistas/js/notas-credito.js"></script>

<!-- DateRangePicker -->
<script src="vistas/bower_components/moment/min/moment.min.js"></script>
<script src="vistas/bower_components/bootstrap-daterangepicker/daterangepicker.js"></script>


<!-- Filtro de Fechas -->
<script>
  $(function () {
    // 1. Inicializar fechas
    var start = moment();
    var end = moment();
    var textoBoton = '<i class="fa fa-calendar"></i> Rango de fecha';

    // Verificar si hay fechas en los inputs ocultos (URL)
    if ($('#fechaInicial').val() && $('#fechaFinal').val()) {
      start = moment($('#fechaInicial').val());
      end = moment($('#fechaFinal').val());
      textoBoton = start.format('MMMM D, YYYY') + ' - ' + end.format('MMMM D, YYYY');
      // Actualizar localStorage para mantener sincronía
      localStorage.setItem("capturarRangoFactus", textoBoton);
    }
    // Fallback a localStorage si existe
    else if (localStorage.getItem("capturarRangoFactus") != null) {
      textoBoton = localStorage.getItem("capturarRangoFactus");
      // Intentar parsear las fechas del texto si es posible, o dejar hoy por defecto en el picker
      // (Sería complejo parsear el texto "Month D, YYYY - ...", así que dejamos moment() por defecto en el picker visual, pero el texto correcto)
    }

    $("#daterange-btn-factus span").html(textoBoton);

    // 2. Configurar DateRangePicker
    $('#daterange-btn-factus').daterangepicker(
      {
        ranges: {
          'Hoy': [moment(), moment()],
          'Ayer': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
          'Últimos 7 días': [moment().subtract(6, 'days'), moment()],
          'Últimos 30 días': [moment().subtract(29, 'days'), moment()],
          'Este mes': [moment().startOf('month'), moment().endOf('month')],
          'Mes pasado': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')]
        },
        startDate: start,
        endDate: end
      },
      function (start, end) {
        // Actualizar texto del botón visualmente
        var textoRango = start.format('MMMM D, YYYY') + ' - ' + end.format('MMMM D, YYYY');
        $('#daterange-btn-factus span').html(textoRango);

        // Actualizar inputs ocultos que se enviarán con el form
        var fechaInicialFormato = start.startOf('day').format('YYYY-MM-DD HH:mm:ss');
        var fechaFinalFormato = end.endOf('day').format('YYYY-MM-DD HH:mm:ss');

        $('#fechaInicial').val(fechaInicialFormato);
        $('#fechaFinal').val(fechaFinalFormato);
      }
    );

    // 3. Manejar Cancelar/Limpiar Rango
    $('#daterange-btn-factus').on('cancel.daterangepicker', function (ev, picker) {
      $('#daterange-btn-factus span').html('<i class="fa fa-calendar"></i> Rango de fecha');
      $('#fechaInicial').val('');
      $('#fechaFinal').val('');
    });
  });
</script>

<!--Guardar observaciones-->
<script>
  $(document).on('blur', '.celda-observacion', function () {
    const idVenta = $(this).attr('data-id'); // .attr() para elementos dinámicos
    const nuevaObservacion = $(this).text().trim();
    console.log("Guardando observación:", nuevaObservacion, "para ID:", idVenta);
    $.ajax({
      url: "ajax/datatable-ventas.ajax.php",
      method: "POST",
      data: {
        idVentaObservacion: idVenta,
        nuevaObservacion: nuevaObservacion
      },
      success: function (respuesta) {
        console.log("Respuesta del servidor:", respuesta);
      },
      error: function () {
        alert("Hubo un error al guardar la observación.");
      }
    });
  });
</script>


<!-- Ampliar foto -->
<script>
  // Previsualizar nueva imagen cuando se selecciona
  $(".nuevaImagenVenta").change(function () {
    var imagen = this.files[0];

    if (imagen) {
      if (imagen["type"] != "image/jpeg" && imagen["type"] != "image/png") {
        $(".nuevaImagenVenta").val("");
        swal({
          title: "Error al subir la imagen",
          text: "¡La imagen debe estar en formato JPG o PNG!",
          type: "error",
          confirmButtonText: "¡Cerrar!"
        });
      } else if (imagen["size"] > 2000000) {
        $(".nuevaImagenVenta").val("");
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
          $("#imagenVentaAmpliada").attr("src", rutaImagen);
        });
      }
    }
  });

  // Guardar la nueva imagen de la venta
  $(document).on("click", ".btnGuardarImagenVenta", function () {

    var idVenta = $("#idVentaImagen").val();
    var imagen = $(".nuevaImagenVenta")[0].files[0];

    console.log("ID al guardar:", idVenta); // Para debug
    console.log("Imagen al guardar:", imagen); // Para debug

    if (!imagen) {
      swal({
        title: "Advertencia",
        text: "No has seleccionado ninguna imagen",
        type: "warning",
        confirmButtonText: "¡Cerrar!"
      });
      return;
    }

    if (!idVenta) {
      swal({
        title: "Error",
        text: "No se pudo obtener el ID de la venta",
        type: "error",
        confirmButtonText: "¡Cerrar!"
      });
      return;
    }

    var datos = new FormData();
    datos.append("idVentaImagen", idVenta);
    datos.append("nuevaImagenVenta", imagen);

    // Mostrar loading
    swal({
      title: 'Cargando...',
      allowOutsideClick: false,
      onBeforeOpen: () => {
        swal.showLoading()
      }
    });

    $.ajax({
      url: "ajax/ventas.ajax.php",
      method: "POST",
      data: datos,
      cache: false,
      contentType: false,
      processData: false,
      dataType: "json",
      success: function (respuesta) {
        console.log("Respuesta del servidor:", respuesta); // Para debug

        if (respuesta == "ok") {
          swal({
            type: "success",
            title: "¡La imagen ha sido actualizada correctamente!",
            showConfirmButton: true,
            confirmButtonText: "Cerrar"
          }).then(function (result) {
            if (result.value) {
              $("#modalAmpliarImagenVenta").modal("hide");
              window.location = "facturas-electronicas";
            }
          });
        } else {
          swal({
            type: "error",
            title: "Error al actualizar la imagen",
            text: JSON.stringify(respuesta),
            confirmButtonText: "Cerrar"
          });
        }
      },
      error: function (jqXHR, textStatus, errorThrown) {
        console.log("Error AJAX:", textStatus, errorThrown); // Para debug
        console.log("Respuesta:", jqXHR.responseText); // Para debug

        swal({
          type: "error",
          title: "Error en la petición",
          text: "Por favor revisa la consola para más detalles",
          confirmButtonText: "Cerrar"
        });
      }
    });
  });


  // Ampliar imagen de venta al hacer clic
  $(document).on("click", ".img-ampliar-venta", function () {
    var rutaImagen = $(this).attr("data-imagen");
    var idVenta = $(this).attr("data-idventa");

    console.log("ID Venta:", idVenta); // Para debug
    console.log("Ruta Imagen:", rutaImagen); // Para debug

    $("#imagenVentaAmpliada").attr("src", rutaImagen);
    $("#idVentaImagen").val(idVenta);
    $(".nuevaImagenVenta").val("");
    $("#modalAmpliarImagenVenta").modal("show");
  });
</script>



<!-- Abrir modal de clientes desde ordenes -->
<script>
  $(document).on("click", ".btnVerClienteDesdeVenta", function () {

    var idCliente = $(this).attr("idCliente");

    var datos = new FormData();
    datos.append("idCliente", idCliente);

    $.ajax({
      url: "ajax/clientes.ajax.php",
      method: "POST",
      data: datos,
      cache: false,
      contentType: false,
      processData: false,
      dataType: "text",
      success: function (respuesta) {

        // Extraer solo el JSON
        var jsonStart = respuesta.indexOf('{');
        var jsonString = respuesta.substring(jsonStart);
        var data = JSON.parse(jsonString);

        // Llenar el modal
        $("#idCliente").val(data["id"]);
        $("#editarCliente").val(data["nombre"]);
        $("#editarDocumentoId").val(data["documento"]);
        $("#editarEmail").val(data["email"]);
        $("#editarTelefono").val(data["telefono"]);
        $("#editarDireccion").val(data["direccion"]);
        $("#editarNota").val(data["notas"]);

        // AGREGAR ESTA LÍNEA para preseleccionar el estado
        $("#editarEstado").val(data["estatus"]);

        // Si tienes más campos, agrégalos aquí:
        // $("#editarDepartamento").val(data["departamento"]); // Eliminado visualmente
        $("#editarCiudad").val(data["ciudad"]);

        // Abrir el modal
        $('#modalEditarCliente').modal('show');
      }
    });
  });
</script>

<!--=====================================
MODAL VER NOTAS DE CRÉDITO
======================================-->
<div id="modalNotasCredito" class="modal fade" role="dialog">
  <div class="modal-dialog">
    <div class="modal-content">

      <!-- CABEZA DEL MODAL -->
      <div class="modal-header" style="background:#f39c12; color:white">
        <button type="button" class="close" data-dismiss="modal">&times;</button>
        <h4 class="modal-title"><i class="fa fa-list"></i> Notas Crédito Asociadas</h4>
      </div>

      <!-- CUERPO DEL MODAL -->
      <div class="modal-body">
        <div class="box-body">

          <!-- TABLA NOTAS CRÉDITO -->
          <table class="table table-bordered table-striped dt-responsive" width="100%">
            <thead>
              <tr>
                <th style="width:10px">#</th>
                <th>Código</th>
                <th>Fecha</th>
                <th>Monto</th>
                <th>Estado</th>
                <th>Acciones</th>
              </tr>
            </thead>
            <tbody id="tbodyNotasCredito">
              <!-- Filas inyectadas por AJAX -->
            </tbody>
          </table>

        </div>
      </div>

      <!-- PIE DEL MODAL -->
      <div class="modal-footer">
        <button type="button" class="btn btn-default pull-right" data-dismiss="modal">Cerrar</button>
      </div>

    </div>
  </div>
</div>

<!--=====================================
MODAL ENVIAR EMAIL
======================================-->

<div id="modalEnviarEmail" class="modal fade" role="dialog">
  <div class="modal-dialog">
    <div class="modal-content">
      <form role="form" method="post" id="formEnviarEmail">

        <!-- CABEZA DEL MODAL -->
        <div class="modal-header" style="background:#3c8dbc; color:white">
          <button type="button" class="close" data-dismiss="modal">&times;</button>
          <h4 class="modal-title">Enviar Factura por Correo</h4>
        </div>

        <!-- CUERPO DEL MODAL -->
        <div class="modal-body">
          <div class="box-body">

            <!-- ENTRADA PARA EL NOMBRE DEL CLIENTE -->
            <div class="form-group">
              <label>Cliente</label>
              <div class="input-group">
                <span class="input-group-addon"><i class="fa fa-user"></i></span>
                <input type="text" class="form-control input-lg" id="emailNombreCliente" readonly>
                <input type="hidden" id="emailIdVenta">
              </div>
            </div>

            <!-- ENTRADA PARA EL EMAIL -->
            <div class="form-group">
              <label>Correo Electrónico</label>
              <div class="input-group">
                <span class="input-group-addon"><i class="fa fa-envelope"></i></span>
                <input type="email" class="form-control input-lg" id="emailDestino" placeholder="Ingresar correo"
                  required>
              </div>
            </div>

          </div>
        </div>

        <!-- PIE DEL MODAL -->
        <div class="modal-footer">
          <button type="button" class="btn btn-default pull-left" data-dismiss="modal">Salir</button>
          <button type="submit" class="btn btn-primary">Enviar PDF</button>
        </div>

      </form>
    </div>
  </div>
</div>

</div>
<!-- DataTables Personalizado para Facturas -->
<script>
$(document).ready(function () {
  setTimeout(function () {
    if ($("#tablaFacturasElectronicas").length > 0) {
      if ($.fn.DataTable.isDataTable('#tablaFacturasElectronicas')) {
        $('#tablaFacturasElectronicas').DataTable().destroy();
      }

      $("#tablaFacturasElectronicas").DataTable({
        "autoWidth": false,
        "initComplete": function(settings, json) {
           $(this.api().table().node()).addClass('datatable-ready');
           if (typeof quitarLoaderGlobal === 'function') {
              quitarLoaderGlobal();
           }
        },
        "order": [[10, "desc"]], // Fecha
        "responsive": {
          "details": {
            "type": "column",
            "target": 0, // En la columna #
            "renderer": function (api, rowIdx, columns) {
              if ($(window).width() >= 768) return false;

              // Mapeo corregido por índices tras eliminar la columna #
              var codigo = columns[0].data || '';
              var cliente = columns[1].data || '';
              var vendedor = columns[2].data || '';
              var formaPago = columns[3].data || '';
              var imagen = columns[4].data || '';
              var total = columns[5].data || '';
              var estadoDian = columns[6].data || '';
              var notas = columns[7].data || '';
              var obs = columns[8].data || '';
              var fecha = columns[9].data || '';

              // Extraer ruta de imagen
              var imagenSrc = 'vistas/img/ventas/default/sinventa.png';
              var matchImagen = imagen.match(/src=["']([^"']+)["']/i);
              if (matchImagen) imagenSrc = matchImagen[1];
              
              var idVenta = $(api.row(rowIdx).node()).attr('data-fe-id') || '';
              var finalHtml = '';

              // SECCION 1: Datos de Facturación
              finalHtml += '<div class="col-xs-12" style="margin-top:10px; margin-bottom:5px; border-bottom: 2px solid #3c8dbc; text-align: left;">';
              finalHtml += '<h5 style="font-weight:bold; color:#3c8dbc; margin:0; text-align: left;">Información de Facturación</h5></div>';

              finalHtml += '<div class="col-xs-12 col-sm-6" style="padding: 8px 0; border-bottom: 1px solid #eee; text-align: left;">';
              finalHtml += '<span class="text-bold">Cliente: </span><span class="pull-right">' + cliente + '</span></div>';

              finalHtml += '<div class="col-xs-12 col-sm-6" style="padding: 8px 0; border-bottom: 1px solid #eee; text-align: left;">';
              finalHtml += '<span class="text-bold">Pago: </span><span class="pull-right">' + formaPago + '</span></div>';

              finalHtml += '<div class="col-xs-12 col-sm-6" style="padding: 8px 0; border-bottom: 1px solid #eee; text-align: left;">';
              finalHtml += '<span class="text-bold">Total: </span><span class="pull-right">' + total + '</span></div>';

              finalHtml += '<div class="col-xs-12" style="padding: 10px 0; border-bottom: 1px solid #eee; display: flex; justify-content: space-between; align-items: center; text-align: left;">';
              finalHtml += '<span class="text-bold">Imagen: </span>';
              finalHtml += '<button class="btn btn-info btn-xs img-ampliar-venta" data-imagen="' + imagenSrc + '" data-idventa="' + idVenta + '"><i class="fa fa-image"></i> Ver imagen</button></div>';

              // SECCION 2: Estado y Fecha
              finalHtml += '<div class="col-xs-12" style="margin-top:15px; margin-bottom:5px; border-bottom: 2px solid #3c8dbc; text-align: left;">';
              finalHtml += '<h5 style="font-weight:bold; color:#3c8dbc; margin:0; text-align: left;">Estado y Fecha</h5></div>';

              finalHtml += '<div class="col-xs-12" style="padding: 8px 0; border-bottom: 1px solid #eee; text-align: left;">';
              finalHtml += '<span class="text-bold">Estado DIAN: </span><span class="pull-right">' + estadoDian + '</span></div>';

              finalHtml += '<div class="col-xs-12" style="padding: 8px 0; border-bottom: 1px solid #eee; text-align: left;">';
              finalHtml += '<span class="text-bold">Fecha: </span><span class="pull-right">' + fecha + '</span></div>';

              // SECCION 3: Notas y Observaciones
              finalHtml += '<div class="col-xs-12" style="margin-top:15px; margin-bottom:5px; border-bottom: 2px solid #3c8dbc; text-align: left;">';
              finalHtml += '<h5 style="font-weight:bold; color:#3c8dbc; margin:0; text-align: left;">Notas y Observaciones</h5></div>';

              finalHtml += '<div class="col-xs-12" style="padding: 8px 0; border-bottom: 1px solid #eee; text-align: left;">';
              finalHtml += '<span class="text-bold" style="display:block; margin-bottom:4px;">Notas:</span>';
              finalHtml += '<span>' + (notas || '<em style="color:#999;">Sin notas</em>') + '</span></div>';

              finalHtml += '<div class="col-xs-12" style="padding: 8px 0; border-bottom: 1px solid #eee; text-align: left;">';
              finalHtml += '<span class="text-bold" style="display:block; margin-bottom:4px;">Observación:</span>';
              finalHtml += '<div class="celda-observacion" contenteditable="true" data-id="' + idVenta + '" style="min-height:30px; text-align: left;">' + obs + '</div></div>';

              return finalHtml ? $('<div class="row" style="padding: 10px; background-color: #fcfcfc; margin: 0; text-align: left;">').append(finalHtml) : false;
            }
          }
        },
        "columnDefs": [
          { "targets": 0, "className": 'dtr-control', "responsivePriority": 1 },
          { "targets": 10, "responsivePriority": 1 }, // Acciones con máxima prioridad
          { "targets": 1, "responsivePriority": 2 }, // Cliente con prioridad 2
          { "targets": [2, 3, 4, 5, 6, 7, 8, 9], "responsivePriority": 3 }
        ],
        "language": {
          "sProcessing": "Procesando...",
          "sLengthMenu": "Mostrar _MENU_ registros",
          "sZeroRecords": "No se encontraron resultados",
          "sEmptyTable": "Ningún dato disponible en esta tabla",
          "sInfo": "Mostrando registros del _START_ al _END_ de un total de _TOTAL_",
          "sSearch": "Buscar:",
          "oPaginate": { "sFirst": "Primero", "sLast": "Último", "sNext": "Siguiente", "sPrevious": "Anterior" }
        }
      });
    }
  }, 200);
});
</script>

<script>
  /*=============================================
  RANGO DE FECHAS FACTUS
  =============================================*/
  $('#daterange-btn-factus').daterangepicker(
    {
      ranges: {
        'Hoy': [moment(), moment()],
        'Ayer': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
        'Últimos 7 días': [moment().subtract(6, 'days'), moment()],
        'Últimos 30 días': [moment().subtract(29, 'days'), moment()],
        'Este mes': [moment().startOf('month'), moment().endOf('month')],
        'Mes pasado': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')]
      },
      startDate: moment(),
      endDate: moment()
    },
    function (start, end) {
      $('#fechaInicial').val(start.format('YYYY-MM-DD'));
      $('#fechaFinal').val(end.format('YYYY-MM-DD'));
      $('#daterange-btn-factus span').html(start.format('MMMM D, YYYY') + ' - ' + end.format('MMMM D, YYYY'));
    }
  );
</script>
