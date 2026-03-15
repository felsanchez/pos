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

  /* Ocultar tabla de ventas hasta que DataTables termine de procesarla */
  #example:not(.datatable-ready) {
    visibility: hidden;
  }

  /* Mostrar un indicador de carga mientras se procesa */
  #example:not(.datatable-ready)+.dataTables_wrapper {
    position: relative;
  }

  /* Cards para móvil */
  .cards-ventas {
    display: none;
  }

  .card-venta {
    background: #fff;
    border-radius: 6px;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
    margin-bottom: 10px;
    padding: 10px;
    position: relative;
    border-left: 4px solid #00a65a;
  }

  .card-venta-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 8px;
    padding-bottom: 8px;
    border-bottom: 1px solid #eee;
  }

  .card-venta-codigo {
    font-size: 14px;
    font-weight: bold;
    color: #00a65a;
  }

  .card-venta-acciones .btn-group {
    display: flex;
    gap: 3px;
  }

  .card-venta-info-principal {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 8px;
    gap: 10px;
  }

  .card-venta-cliente {
    font-size: 15px;
    font-weight: bold;
    color: #333;
    flex: 1;
    margin: 0;
  }

  .card-venta-total {
    font-size: 16px;
    font-weight: bold;
    color: #00a65a;
    white-space: nowrap;
    margin: 0;
  }

  .card-venta-info {
    display: flex;
    flex-direction: column;
    gap: 5px;
    margin-bottom: 8px;
  }

  .card-venta-info-fila {
    display: flex;
    justify-content: space-between;
    gap: 10px;
  }

  .card-venta-info-item {
    display: flex;
    align-items: center;
    font-size: 12px;
    color: #666;
    flex: 1;
  }

  .card-venta-info-item i {
    margin-right: 5px;
    width: 15px;
    text-align: center;
  }

  .card-venta-notas {
    background: #f9f9f9;
    padding: 8px;
    border-radius: 3px;
    margin-top: 8px;
    font-size: 12px;
    color: #666;
    border-left: 2px solid #3c8dbc;
  }

  .card-venta-observacion {
    background: #fff9e6;
    padding: 8px;
    border-radius: 3px;
    margin-top: 8px;
    font-size: 12px;
    color: #666;
    border-left: 2px solid #f39c12;
    cursor: text;
    min-height: 30px;
  }

  .card-venta-observacion:empty:before {
    content: "Escribe una observación...";
    color: #999;
    font-style: italic;
  }

  .card-venta-observacion:focus {
    outline: 2px solid #f39c12;
    background: #fffef5;
  }

  .card-venta-imagen-icono {
    display: inline-block;
    padding: 4px 8px;
    background: #3c8dbc;
    color: white;
    border-radius: 3px;
    cursor: pointer;
    font-size: 11px;
  }

  .card-venta-imagen-icono:hover {
    background: #2e6da4;
  }

  /* Responsive */
  @media (max-width: 767px) {
    .tabla-ventas {
      display: none !important;
    }

    .cards-ventas {
      display: block !important;
    }
  }

  @media (min-width: 768px) {
    .tabla-ventas {
      display: block !important;
    }

    .cards-ventas {
      display: none !important;
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


<!-- Ocultar todas las columnas por defecto -->
<style>
  @media (max-width: 767px) {

    .tablas td,
    .tablas th {
      display: none;
    }

    /* Mostrar solo las columnas 2, 6, 7 y 8 en MOVIL*/
    .tablas td:nth-child(2),
    .tablas td:nth-child(3),
    .tablas td:nth-child(6),
    .tablas td:nth-child(8),
    .tablas td:nth-child(9),
    .tablas td:nth-child(10),
    .tablas th:nth-child(2),
    .tablas th:nth-child(3),
    .tablas th:nth-child(6),
    .tablas th:nth-child(8),
    .tablas th:nth-child(9),
    .tablas th:nth-child(10) {
      display: table-cell;
    }
  }
</style>

<style>
  /* Solo muestra el botón en móvil */
  .solo-movil {
    display: none;
  }

  @media (max-width: 767px) {
    .solo-movil {
      display: inline-block !important;
    }
  }
</style>

<!--Agregar espacio entre los btones en móvil-->
<style>
  @media (max-width: 767px) {
    .solo-movil {
      margin-left: 3px !important;
    }
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


        <a href="crear-factura-electronica">
          <button class="btn btn-primary">
            Crear Factura Electrónica
          </button>
        </a>


        <div class="pull-right contenedor-filtros">

          <form method="GET" action="index.php"
            style="display: inline-flex; gap: 5px; align-items: center; flex-wrap: wrap;">

            <input type="hidden" name="ruta" value="facturas-electronicas">
            <input type="hidden" name="fechaInicial" id="fechaInicial"
              value="<?php echo isset($_GET["fechaInicial"]) ? $_GET["fechaInicial"] : null; ?>">
            <input type="hidden" name="fechaFinal" id="fechaFinal"
              value="<?php echo isset($_GET["fechaFinal"]) ? $_GET["fechaFinal"] : null; ?>">

            <!-- Filtro por cliente -->
            <div class="filtro-cliente">
              <select name="cliente" class="form-control select-cliente" style="width: 200px;">
                <option value="">Todos los clientes</option>
                <?php
                $item = null;
                $valor = null;
                $clientes = ControladorClientes::ctrMostrarClientes($item, $valor);

                foreach ($clientes as $key => $valueCliente) {
                  $selected = (isset($_GET['cliente']) && $_GET['cliente'] == $valueCliente["id"]) ? 'selected' : '';
                  echo '<option value="' . $valueCliente["id"] . '" ' . $selected . '>' . $valueCliente["nombre"] . '</option>';
                }
                ?>
              </select>
            </div>

            <!-- Filtro por usuario -->
            <div class="filtro-usuario">
              <select name="usuario" class="form-control select-usuario" style="width: 200px;">
                <option value="">Todos los usuarios</option>
                <?php
                $item = null;
                $valor = null;
                $usuarios = ControladorUsuarios::ctrMostrarUsuarios($item, $valor);

                foreach ($usuarios as $key => $valueUsuario) {
                  $selected = (isset($_GET['usuario']) && $_GET['usuario'] == $valueUsuario["id"]) ? 'selected' : '';
                  echo '<option value="' . $valueUsuario["id"] . '" ' . $selected . '>' . $valueUsuario["nombre"] . '</option>';
                }
                ?>
              </select>
            </div>

            <!-- Botón Rango de Fecha -->
            <button type="button" class="btn btn-default" id="daterange-btn-factus">
              <span>
                <i class="fa fa-calendar"></i> Rango de fecha
              </span>
              <i class="fa fa-caret-down"></i>
            </button>

            <!-- Botón Buscar -->
            <button type="submit" class="btn btn-info">
              <i class="fa fa-search"></i> Buscar
            </button>

            <!-- Botón Limpiar -->
            <a href="index.php?ruta=facturas-electronicas" class="btn btn-default" title="Limpiar filtros">
              <i class="fa fa-refresh"></i>
            </a>

          </form>

        </div>

        <style>
          @media (max-width: 767px) {
            .box-header .btn-primary {
              width: 100%;
              margin-bottom: 10px;
            }

            .pull-right.contenedor-filtros form {
              flex-direction: column;
              align-items: stretch !important;
              width: 100%;
            }

            .filtro-cliente,
            .filtro-usuario,
            #daterange-btn,
            .btn-info,
            .btn-default {
              width: 100% !important;
              margin-bottom: 5px;
            }

            .select-cliente,
            .select-usuario {
              width: 100% !important;
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

          /* Hide table while loading to prevent layout jump */
          .tablas:not(.datatable-ready) {
            visibility: hidden;
            height: 0;
            overflow: hidden;
            opacity: 0;
          }

          .tablas.datatable-ready {
            transition: opacity 0.5s ease;
            opacity: 1;
          }
        </style>

        <div id="loader-table-fe" class="loader-container">
          <i class="fa fa-refresh fa-spin"></i>
          <span>Cargando Facturas Electrónicas...</span>
        </div>

        <div class="tabla-ventas table-responsive">
          <table id="example" class="table table-bordered table-striped tablas display nowrap">

            <thead>
              <tr>
                <th style="width: 10px">#</th>
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

                echo '<td>' . ($key + 1) . '</td>
                      <td' . ($esBorrador ? ' class="text-yellow" style="font-weight:bold"' : '') . '>' . $numeroMostrar . '</td>';

                // Usar nombres que ya vienen del JOIN en la consulta SQL
                $nombreCliente = !empty($value["nombre_cliente"]) ? $value["nombre_cliente"] : "Cliente no encontrado";
                $nombreVendedor = !empty($value["nombre_vendedor"]) ? $value["nombre_vendedor"] : "Vendedor no encontrado";

                echo '<td>

                                  <span class="btnVerClienteDesdeVenta"
                                        data-toggle="modal"
                                        data-target="#modalEditarCliente"
                                        idCliente="' . $value["id_cliente"] . '"
                                        style="cursor: pointer; color: #337ab7; text-decoration: underline;">
                                      ' . $nombreCliente . '
                                  </span>
                              </td>';

                echo '<td>' . $nombreVendedor . '</td> 

                        <td>' . $moneda . ' ' . $value["metodo_pago"] . '</td>';

                // Validación de la foto
                if ($value["imagen"] != "") {
                  echo '<td><img src="' . $value["imagen"] . '" class="img-thumbnail img-ampliar-venta" width="40px" style="cursor: pointer;" data-imagen="' . $value["imagen"] . '" data-idventa="' . $value["id"] . '"></td>';
                } else {
                  echo '<td><img src="vistas/img/ventas/default/sinventa.png" class="img-thumbnail img-ampliar-venta" width="40px" style="cursor: pointer;" data-imagen="vistas/img/ventas/default/sinventa.png" data-idventa="' . $value["id"] . '"></td>';
                }

                echo '<td>' . $moneda . ' ' . number_format($value["total"], 2) . '</td>';

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

                echo '<td>' . $value['notas'] . '</td>';

                $estadoDian = isset($value["estado_dian"]) ? $value["estado_dian"] : "";
                $esEditable = ($estadoDian != "enviada" && $estadoDian != "aceptada");

                $contentEditableAttr = $esEditable ? 'contenteditable="true"' : '';
                $claseEditable = $esEditable ? 'celda-observacion' : '';

                echo '<td ' . $contentEditableAttr . ' class="' . $claseEditable . '" data-id="' . $value["id"] . '">' . $value["observacion"] . '</td>

                        
                       <td>' . $value["fecha"];

                // 1. Botón Eliminar - Solo Administrador
                // 1. Botón Eliminar - Solo Administrador
                if ($_SESSION["perfil"] == "Administrador") {
                  echo '<button class="btn btn-danger btn-xs solo-movil btnEliminarVenta" style="float: right;" idVenta="' . $value["id"] . '">
                                      <i class="fa fa-times"></i>
                                    </button>';
                }

                // 2. Botón Imprimir
                echo '<button class="btn btn-info btn-xs solo-movil btnImprimirFactura" style="float: right;" codigoVenta="' . $value["codigo"] . '">
                              <i class="fa fa-print"></i>
                            </button>
                            ';

                // 3. Botón Editar (Ver)
                echo '<button class="btn btn-warning btn-xs solo-movil btnEditarVenta" style="float: right;" idVenta="' . $value["id"] . '">
                              <i class="fa fa-eye"></i>
                            </button>';

                echo '</td>

                        <td>
                          <div class="btn-group">

                               <button class="btn btn-info btnEditarVenta" idVenta="' . $value["id"] . '" title="Ver Detalles">
                                <i class="fa fa-eye"></i>
                               </button>

                             ' . (!empty($value["qr_data"]) ? '<a class="btn btn-success" href="' . $value["qr_data"] . '" target="_blank" data-toggle="tooltip" title="Ver en DIAN"><i class="fa fa-external-link"></i></a>' : '') . '

                             ' . ((isset($value["estado_dian"]) && $value["estado_dian"] == "creada") ?
                  '<button class="btn btn-success btnFirmarFactura" idVenta="' . $value["id"] . '" title="Firmar y Enviar a DIAN">
                                <i class="fa fa-paper-plane"></i>
                             </button>' : '') . '
                             ' . ((isset($value["estado_dian"]) && in_array($value["estado_dian"], ['creada', 'pendiente'])) ?
                  '<a class="btn btn-warning" href="index.php?ruta=editar-factura-electronica&idVenta=' . $value["id"] . '" title="Editar Borrador">
                                <i class="fa fa-pencil"></i>
                             </a>' : '') . '
                             ';

                if ($estadoDian == 'aceptada' || $estadoDian == 'enviada') {
                  echo ' <button class="btn btn-primary btnEnviarEmail" idVenta="' . $value["id"] . '" nombreCliente="' . $nombreCliente . '" emailCliente="' . $value["email_cliente"] . '" title="Enviar por Correo">
                                <i class="fa fa-envelope"></i>
                              </button>';
                }

                echo ' ';

                // Botón de las Notas Crédito (solo si la factura tiene NC)
                if (ModeloFactus::mdlTieneNotaCredito($value["id"])) {
                  echo '<button class="btn btn-warning btnVerNotasCredito" idVenta="' . $value["id"] . '" data-toggle="modal" data-target="#modalNotasCredito" title="Ver Notas Crédito">
                                       <i class="fa fa-list"></i>
                                     </button>';
                }

                if ($_SESSION["perfil"] == "Administrador") {
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

        <!-- CARDS PARA MÓVIL -->
        <div class="cards-ventas">

          <?php
          // Reutilizar la misma consulta de la tabla para evitar duplicar carga
          // $respuesta ya contiene las ventas, no hacer nueva consulta
          
          foreach ($respuesta as $key => $value) {

            // Usar nombres que ya vienen del JOIN en la consulta SQL
            $nombreCliente = !empty($value["nombre_cliente"]) ? $value["nombre_cliente"] : "Cliente no encontrado";
            $nombreVendedor = !empty($value["nombre_vendedor"]) ? $value["nombre_vendedor"] : "Vendedor no encontrado";

            // Imagen
            $imagenVenta = !empty($value["imagen"]) ? $value["imagen"] : "vistas/img/ventas/default/sinventa.png";

            // Estado DIAN Badge
            $estadoDian = isset($value["estado_dian"]) ? $value["estado_dian"] : 'pendiente';
            $badgeDian = '';
            if ($estadoDian == 'aceptada') {
              $badgeDian = '<span class="badge bg-green">DIAN: Aceptada</span>';
            } elseif ($estadoDian == 'rechazada') {
              $badgeDian = '<span class="badge bg-red">DIAN: Rechazada</span>';
            } elseif ($estadoDian == 'enviada') {
              $badgeDian = '<span class="badge bg-yellow">DIAN: Enviada</span>';
            } else {
              // Opcional: No mostrar nada si es pendiente para no saturar, o mostrar gris
              //$badgeDian = '<span class="badge bg-gray">DIAN: Pendiente</span>';
            }

            // Numeración predictiva para móvil
            if (!empty($value["numero_factura"])) {
              $numeroMostrarMovil = $value["numero_factura"];
            } else {
              // Buscar el índice del borrador actual en la lista filtrada de borradores reales
              $mismaVenta = array_filter($respuesta, function ($v) {
                return empty($v["numero_factura"]) && in_array(($v["estado_dian"] ?? 'pendiente'), ['pendiente', 'creada', 'borrador']);
              });
              $idsBorradores = array_values(array_map(function ($v) {
                return $v["id"];
              }, $mismaVenta));
              $indexBorrador = array_search($value["id"], $idsBorradores);

              if ($indexBorrador !== false) {
                $numeroMostrarMovil = $prefijoDian . ($siguienteConsecutivoBase - 1 - $indexBorrador);
              } else {
                $numeroMostrarMovil = $prefijoDian . $value["codigo"];
              }
            }

            echo '<div class="card-venta">

                      <div class="card-venta-header">
                        <div class="card-venta-codigo">
                          ' . $numeroMostrarMovil . ' ' . $badgeDian . '
                        </div>
                        <div class="card-venta-acciones">
                          <div class="btn-group">
                            <button class="btn btn-info btn-xs btnEditarVenta" idVenta="' . $value["id"] . '" title="Ver Detalles">
                              <i class="fa fa-eye"></i>
                            </button>

                            ' . (!empty($value["qr_data"]) ? '<a class="btn btn-success btn-xs" href="' . $value["qr_data"] . '" target="_blank" title="Ver en DIAN"><i class="fa fa-external-link"></i></a>' : '') . '';

            if ($estadoDian == 'aceptada' || $estadoDian == 'enviada') {
              echo ' <button class="btn btn-primary btn-xs btnEnviarEmail" idVenta="' . $value["id"] . '" nombreCliente="' . $nombreCliente . '" emailCliente="' . $value["email_cliente"] . '" title="Enviar por Correo">
                                <i class="fa fa-envelope"></i>
                              </button>';
            }

            // Botón de las Notas Crédito movil
            if (ModeloFactus::mdlTieneNotaCredito($value["id"])) {
              echo '<button class="btn btn-warning btn-xs btnVerNotasCredito" idVenta="' . $value["id"] . '" data-toggle="modal" data-target="#modalNotasCredito" title="Ver Notas Crédito">
                                   <i class="fa fa-list"></i>
                                 </button>';
            }

            echo ' ' . ((isset($value["estado_dian"]) && $value["estado_dian"] == "creada") ?
              '<button class="btn btn-success btn-xs btnFirmarFactura" idVenta="' . $value["id"] . '" title="Firmar y Enviar a DIAN">
                                <i class="fa fa-paper-plane"></i>
                             </button>' : '') . '
                             ' . ((isset($value["estado_dian"]) && in_array($value["estado_dian"], ['creada', 'pendiente'])) ?
              '<a class="btn btn-warning btn-xs" href="index.php?ruta=editar-factura-electronica&idVenta=' . $value["id"] . '" title="Editar Borrador">
                                <i class="fa fa-pencil"></i>
                             </a>' : '') . '
';

            if ($_SESSION["perfil"] == "Administrador") {
              // Solo mostrar botón eliminar si la factura NO ha sido firmada/aceptada
              $estadosNoEliminables = ['enviada', 'aceptada'];
              if (!in_array($value["estado_dian"], $estadosNoEliminables)) {
                echo '<button class="btn btn-danger btn-xs btnEliminarVenta" idVenta="' . $value["id"] . '" title="Eliminar Borrador">
                          <i class="fa fa-trash"></i>
                        </button>';
              }
            }

            echo '      </div>
                        </div>
                      </div>

                      <div class="card-venta-info-principal">
                        <div class="card-venta-cliente">
                          <span class="btnVerClienteDesdeVenta"
                                data-toggle="modal"
                                data-target="#modalEditarCliente"
                                idCliente="' . $value["id_cliente"] . '"
                                style="cursor: pointer; color: #337ab7; text-decoration: underline;">
                            ' . $nombreCliente . '
                          </span>
                        </div>
                        <div class="card-venta-total">
                          ' . $moneda . ' ' . number_format($value["total"], 2) . '
                        </div>
                      </div>

                      <div class="card-venta-info">
                        <div class="card-venta-info-fila">
                          <div class="card-venta-info-item">
                            <i class="fa fa-calendar"></i> ' . $value["fecha"] . '
                          </div>
                          <div class="card-venta-info-item">
                            <i class="fa fa-credit-card"></i> ' . $value["metodo_pago"] . '
                          </div>
                        </div>
                        <div class="card-venta-info-fila">
                          <div class="card-venta-info-item">
                            <i class="fa fa-user"></i> ' . $nombreVendedor . '
                          </div>

                        </div>
                      </div>

                      <div class="card-venta-imagen-icono img-ampliar-venta"
                           data-imagen="' . $imagenVenta . '"
                           data-idventa="' . $value["id"] . '">
                        <i class="fa fa-image"></i> Ver comprobante
                      </div>';

            // Notas solo visualización
            if (!empty($value["notas"])) {
              echo '<div class="card-venta-notas">
                        <i class="fa fa-magic"></i> ' . $value["notas"] . '
                      </div>';
            }

            // Observación editable
            echo '<div class="card-venta-observacion celda-observacion" contenteditable="true" data-id="' . $value["id"] . '">' . $value["observacion"] . '</div>';

            echo '</div>';
          }
          ?>

        </div>


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


        <?php
        $eliminarVenta = new ControladorVentas();
        $eliminarVenta->ctrEliminarVenta();
        ?>

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
        <button type="button" class="btn btn-danger" id="btnConfirmarNC">
          <i class="fa fa-check"></i> Generar Nota Crédito
        </button>
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
    const idVenta = $(this).data('id');
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