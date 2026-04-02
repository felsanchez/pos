<!-- Librería de estilos de Choices.js -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/choices.js/public/assets/styles/choices.min.css">

<!-- Ruta clientes.css -->
<link rel="stylesheet" href="assets/css/clientes.css">


<!-- Centrar filtro -->
<style>
  @media (max-width: 767px) {
    .filtro-estatus-wrapper {
      float: none !important;
      /* anula el pull-right */
      justify-content: center !important;
      /* centra con flex */
      text-align: center;
      /* por si acaso */
      width: 100%;
      /* ocupa todo el ancho */
    }

    .filtro-estatus-wrapper label {
      margin-bottom: 5px;
      /* pequeño espacio si se apila */
    }
  }
</style>


<style>
  td.details-control {
    background: url('https://cdn.datatables.net/1.13.6/images/details_open.png') no-repeat center center;
    cursor: pointer;
  }

  tr.shown td.details-control {
    background: url('https://cdn.datatables.net/1.13.6/images/details_close.png') no-repeat center center;
  }
</style>


<!-- Estilos para que el dropdown de Choices.js no se corte -->
<style>
  /* Permitir que el dropdown se muestre fuera del contenedor de la tabla */
  .table-responsive {
    overflow: visible !important;
  }

  /* Asegurar que el dropdown de Choices.js tenga z-index alto y se posicione correctamente */
  .choices__list--dropdown {
    position: absolute !important;
    z-index: 9999 !important;
    overflow-y: auto !important;
  }

  /* Asegurar que el contenedor box-body permita overflow visible */
  .box-body {
    overflow: visible !important;
  }

  /* Pero mantener scroll horizontal solo si es necesario en pantallas pequeñas */
  @media (max-width: 991px) {
    .table-responsive {
      overflow-x: auto !important;
      overflow-y: visible !important;
    }
  }
</style>

<style>
  /* Asegurar que los modales estén por encima del backdrop */
  #modalGestionarEstados,
  #modalEditarEstado,
  #modalImportarClientes {
    z-index: 10050 !important;
    opacity: 1 !important;
  }

  #modalGestionarEstados .modal-dialog,
  #modalEditarEstado .modal-dialog,
  #modalImportarClientes .modal-dialog {
    z-index: 10051 !important;
  }

  /* Ajustar z-index del backdrop */
  .modal-backdrop.in {
    z-index: 10040 !important;
  }
</style>

<!-- Solo muestra 2 campos en movil en la Tabla 1-->
<style>
  @media (max-width: 767px) {

    /* Ocultar todas las columnas excepto las especificadas */
    .tablas1 td,
    .tablas1 th {
      display: none;
    }

    /* Mostrar Responsive (+), Nombre (2), Teléfono (5), Acciones (10) */
    .tablas1 td:first-child,
    .tablas1 th:first-child,
    .tablas1 td:nth-child(2),
    .tablas1 th:nth-child(2),
    .tablas1 td:nth-child(5),
    .tablas1 th:nth-child(5),
    .tablas1 td:nth-child(10),
    .tablas1 th:nth-child(10) {
      display: table-cell !important;
    }
  }
</style>

<style>
  @media (max-width: 767px) {

    .tablas1 td:nth-child(10) .btn,
    .tablas1 th:nth-child(10) .btn {
      padding: 1px 5px !important;
      font-size: 12px !important;
      line-height: 1.5 !important;
    }
  }

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


<!-- Solo muestra 2 campos en movil en la Tabla 2-->
<style>
  @media (max-width: 767px) {

    .tablas2 td:nth-child(n+3),
    .tablas2 th:nth-child(n+3) {
      display: none;
    }

    .tablas2 td:first-child,
    .tablas2 td:nth-child(2),
    .tablas2 th:first-child,
    .tablas2 th:nth-child(2) {
      display: table-cell;
    }
  }
</style>


<!-- Estilos dinámicos para colores de estados -->
<style>
  <?php
  $estadosParaEstilos = ControladorEstadosClientes::ctrMostrarEstadosClientes(null, null);
  foreach ($estadosParaEstilos as $estadoEstilo) {
    $nombreLimpio = str_replace(" ", "-", strtolower($estadoEstilo["nombre"]));

    $color = $estadoEstilo["color"];

    // Estilos para el contenedor cerrado de Choices.js (select cuando NO está abierto)
    echo '.estatus-' . $nombreLimpio . ' .choices__inner,';
    echo '.estatus-' . $nombreLimpio . '.choices .choices__inner {';
    echo '  background-color: ' . $color . ' !important;';
    echo '  border-color: ' . $color . ' !important;';
    echo '  color: #fff !important;';
    echo '}';


    // Estilos para cada opción individual en el dropdown (basado SOLO en data-value)
    echo '.choices__list--dropdown .choices__item--selectable[data-value="' . $estadoEstilo["nombre"] . '"] {';
    echo '  background-color: ' . $color . ' !important;';
    echo '  color: #fff !important;';
    echo '}';

    echo '.choices__list--dropdown .choices__item--selectable[data-value="' . $estadoEstilo["nombre"] . '"]:hover {';
    echo '  background-color: ' . adjustBrightness($color, -20) . ' !important;';
    echo '  color: #fff !important;';
    echo '}';
  }

  // Función helper para ajustar brillo (hover más oscuro)
  function adjustBrightness($hex, $steps)
  {
    $steps = max(-255, min(255, $steps));
    $hex = str_replace('#', '', $hex);
    $r = hexdec(substr($hex, 0, 2));
    $g = hexdec(substr($hex, 2, 2));
    $b = hexdec(substr($hex, 4, 2));
    $r = max(0, min(255, $r + $steps));
    $g = max(0, min(255, $g + $steps));
    $b = max(0, min(255, $b + $steps));
    return '#' . str_pad(dechex($r), 2, '0', STR_PAD_LEFT) . str_pad(dechex($g), 2, '0', STR_PAD_LEFT) . str_pad(dechex($b), 2, '0', STR_PAD_LEFT);
  }
  ?>
</style>


<!-- Mapa de colores de estados para JavaScript -->
<script>
  window.estadosColores = {
    <?php
    foreach ($estadosParaEstilos as $key => $estadoEstilo) {
      $coma = ($key < count($estadosParaEstilos) - 1) ? ',' : '';
      echo '"' . strtolower($estadoEstilo["nombre"]) . '": "' . $estadoEstilo["color"] . '"' . $coma . "\n";
    }
    ?>
  };

  console.log("Colores de estados cargados:", window.estadosColores);
</script>

<?php
$editarCliente = new ControladorClientes();
$editarCliente->ctrEditarCliente();
?>

<div class="content-wrapper">
  <section class="content-header">

    <h1>
      Administrar Clientes
    </h1>

    <ol class="breadcrumb">
      <li><a href="inicio"><i class="fa fa-dashboard"></i> Inicio</a></li>
      <li class="active">Administrar Clientes</li>
    </ol>

  </section>

  <section class="content">

    <div class="box">

      <div class="box-header with-border">
        <?php if (puedeAccion('clientes', 'crear')): ?>
          <a href="cliente-detalle" class="btn btn-primary">
            <i class="fa fa-plus"></i> Agregar Nuevo
          </a>

          <button class="btn btn-default" data-toggle="modal" data-target="#modalGestionarEstados">
            <i class="fa fa-flag"></i> Gestionar estados
          </button>

          <button class="btn btn-success" data-toggle="modal" data-target="#modalImportarClientes">
            <i class="fa fa-upload"></i> Exportar / Importar Clientes
          </button>
        <?php endif; ?>
      </div>


      <!--CODIGO PARA LLAMAR AL WEBHOOK DE n8n -->
      <!--
        <form id="formN8N" action="https://c610c962d42e.ngrok-free.app/webhook/mipos" method="POST" target="_blank">

        <?php
        echo '<script>console.log("DEBUG: Entrando a CSRF::insertToken");</script>';
        CSRF::insertToken();
        echo '<script>console.log("DEBUG: Saliendo de CSRF::insertToken");</script>';
        ?>
          <input type="hidden" name="origen" value="clientes">
          <button type="submit" class="btn btn-success">Enviar a n8n</button>
        </form>
        -->


      <?php
      $filtroEstatus1 = isset($_GET['filtroEstatus1']) ? $_GET['filtroEstatus1'] : ''; // Captura el valor del filtro de estatus si existe.
      
      // Aquí aplica el filtro de estatus desde el GET para obtener los clientes correctos
      $item = "estatus";
      $valor = $filtroEstatus1;
      $clientes = ControladorClientes::ctrMostrarClientes($item, $valor);
      ?>

      <h3
        style="text-align: center; font-weight: bold; margin: 20px 0; color: #4A4A4A; padding-bottom: 10px; border-bottom: 2px solid #4A4A4A;">
        Lista de Clientes
      </h3>


      <div class="box-body table-responsive">

        <!-- filtro estatus-->
        <div class="clearfix mb-2">
          <div class="pull-right filtro-estatus-wrapper d-flex align-items-center" style="gap: 8px;">
            <label for="filtroEstatus1" class="control-label mb-0">Filtra por ESTADOS:</label>
            <select id="filtroEstatus1" onchange="filterTable1()" class="form-control filtro-estatus">

              <option value="">Todos</option>
              <?php
              $estadosDisponibles = ControladorEstadosClientes::ctrMostrarEstadosClientes(null, null);
              foreach ($estadosDisponibles as $estado) {
                $selected = ($filtroEstatus1 == $estado["nombre"]) ? "selected" : "";
                echo '<option value="' . $estado["nombre"] . '" ' . $selected . '>' . ucfirst($estado["nombre"]) . '</option>';
              }
              ?>

            </select>
          </div>
        </div>

        <br><br>


        <table class="table table-bordered table-striped tablas1">
          <thead>
            <tr>
              <th style="width:10px">#</th>
              <th>Nombre</th>
              <th>Documento</th>
              <th>Email</th>
              <th>Teléfono</th>
              <!--<th>Departamento</th>-->
              <!--<th>Ciudad</th>-->
              <th>Dirección</th>
              <th>Estado</th>
              <th><i class="fa fa-pencil-square"></i> Notas</th>
              <!--<th>Total compras</th>-->
              <th>Última compra</th>
              <th>Acciones</th>
              <th>Ingreso al sistema</th>
            </tr>
            </tr>
          </thead>
          <tbody>
            <?php
            $item = null;
            $valor = null;
            $clientes = ControladorClientes::ctrMostrarClientes($item, $valor);

            if (is_array($clientes) && count($clientes) > 0):
              $key = 1;
              // Pre-fetch states for efficiency
              $estadosDisponibles = ControladorEstadosClientes::ctrMostrarEstadosClientes(null, null);
              // Pre-cargar conteo de facturas electrónicas por cliente (evita N+1 queries)
              $feMapClientes = ModeloVentas::mdlContarFacturasElectronicasPorCliente("ventas");

              foreach ($clientes as $value):
                // if (isset($value["compras"]) && $value["compras"] > 0):
                $estatus = $value["estatus"] ?? "";
                $estatusClass = "estatus-" . str_replace(" ", "-", strtolower($estatus));

                // Calcular variables de botones (deben estar antes del primer uso en móvil y desktop)
                $tieneVentas = (isset($value["compras"]) && $value["compras"] > 0);
                $styleVentas = $tieneVentas ? "" : "opacity: 0.6;";
                $claseVentas = $tieneVentas ? "btnVerVentasCliente" : "btnSinVentas";
                $linkVentas  = $tieneVentas ? "index.php?ruta=cliente-ventas&idCliente=" . $value['id'] : "#";

                $tieneFE = isset($feMapClientes[$value['id']]) && $feMapClientes[$value['id']] > 0;
                $styleFE = $tieneFE ? "" : "opacity: 0.6;";
                $claseFE = $tieneFE ? "" : "btnSinFacturas";
                $linkFE  = $tieneFE ? "index.php?ruta=facturas-electronicas&cliente=" . $value['id'] : "#";
                ?>

                <tr>
                  <td data-order="<?php echo $value["id"]; ?>"><?php echo ($key + 1); ?></td>

                  <td><?php echo $value["nombre"]; ?></td>
                  <!-- BTN VERSION MOVIL-->
                  <td>

                    <?php echo $value["documento"]; ?>

                    <a href="<?php echo $linkVentas; ?>"
                      class="btn btn-success btn-xs <?php echo $claseVentas; ?> solo-movil" style="float: right; <?php echo $styleVentas; ?>"
                      title="Ver ventas de este cliente">
                      <i class="fa fa-line-chart"></i>
                    </a>

                    <a href="<?php echo $linkFE; ?>"
                      class="btn btn-info btn-xs <?php echo $claseFE; ?> solo-movil" style="float: right; margin-right: 3px; <?php echo $styleFE; ?>"
                      title="Ver facturas electrónicas de este cliente">
                      <i class="fa fa-file-text"></i>
                    </a>

                    <?php if (puedeAccion('clientes', 'editar')): ?>
                      <a href="cliente-detalle?id=<?php echo $value['id']; ?>" class="btn btn-warning btn-xs solo-movil"
                        style="float: right;" title="Editar cliente">
                        <i class="fa fa-pencil"></i>
                      </a>
                    <?php endif; ?>
                  </td>
                  <!-- FIN BTN MOVIL-->

                  <td><?php echo $value["email"]; ?></td>
                  <td><?php echo $value["telefono"]; ?></td>
                  <!--<td><?php echo $value["departamento"]; ?></td>-->
                  <!--<td><?php echo $value["ciudad"]; ?></td>-->
                  <td><?php echo $value["direccion"]; ?></td>

                  <td>
                    <?php
                    $estatus = $value["estatus"] ?? "";
                    $colorEstado = "#999"; // Default color
                
                    foreach ($estadosDisponibles as $estado) {
                      if (strcasecmp($estado["nombre"], $estatus) == 0) {
                        $colorEstado = $estado["color"];
                        break;
                      }
                    }

                    if (!empty($estatus)) {
                      echo '<span class="badge" style="background-color: ' . $colorEstado . '">' . ucfirst($estatus) . '</span>';
                    } else {
                      echo '<span class="text-muted">Sin estado</span>';
                    }
                    ?>
                  </td>

                  <td contenteditable="true" class="celda-notas" data-id="<?= $value['id']; ?>">
                    <?= $value['notas'] ?? ''; ?>
                  </td>

                  <!--<td><?php //echo $value["compras"]; ?></td>-->
                  <td><?php echo $value["ultima_compra"]; ?></td>

                  <td>
                    <div class="btn-group">
                      <?php if (puedeAccion('clientes', 'editar')): ?>
                        <a href="cliente-detalle?id=<?php echo $value["id"]; ?>" class="btn btn-warning"
                          title="Editar cliente">
                          <i class="fa fa-pencil"></i>
                        </a>
                      <?php endif; ?>

                      <?php // Variables calculadas al inicio del loop, disponibles aquí. ?>

                      <a href="<?php echo $linkVentas; ?>" class="btn btn-success <?php echo $claseVentas; ?>"
                        title="Ver ventas de este cliente" style="<?php echo $styleVentas; ?>">
                        <i class="fa fa-line-chart"></i>
                      </a>

                      <a href="<?php echo $linkFE; ?>" class="btn btn-info <?php echo $claseFE; ?>"
                        title="Ver facturas electrónicas de este cliente" style="<?php echo $styleFE; ?>">
                        <i class="fa fa-file-text"></i>
                      </a>

                      <?php if (puedeAccion('clientes', 'eliminar')): ?>
                        <button class="btn btn-danger btnEliminarCliente" idCliente="<?php echo $value["id"]; ?>"
                          title="Eliminar cliente">
                          <i class="fa fa-times"></i>
                        </button>
                      <?php endif; ?>
                    </div>
                  </td>

                  <td><?php echo $value["fecha"]; ?></td>
                </tr>
                <?php
                $key++;
                // endif;
              endforeach;
            else:
              ?>
              <tr>
                <td colspan="14" class="text-center">No hay clientes registrados</td>
              </tr>
              <?php
            endif; ?>
          </tbody>
        </table>

      </div>

    </div>

  </section>

</div>


<!--=====================================
MODAL AGREGAR CLIENTE
======================================-->

<!-- Modal -->
<div id="modalAgregarCliente" class="modal fade" role="dialog">

  <div class="modal-dialog">

    <div class="modal-content">

      <form role="form" method="post">

        <?php CSRF::insertToken(); ?>
        <!--=====================================
      CABEZA DEL MODAL
      ======================================-->

        <div class="modal-header" style="background:#3c8dbc; color: white">

          <button type="button" class="close" data-dismiss="modal">&times;</button>
          <h4 class="modal-title">Agregar cliente</h4>

        </div>

        <!--=====================================
      CUERPO DEL MODAL
      ======================================-->

        <div class="modal-body">

          <div class="box-body">

            <!-- entrada para nombre -->

            <div class="form-group">

              <div class="input-group">

                <span class="input-group-addon"><i class="fa fa-user"></i></span>

                <input type="text" class="form-control input-lg" name="nuevoCliente" id="nuevoCliente"
                  placeholder="Ingresar nombre *" required>

              </div>

            </div>


            <!-- entrada para documento ID -->

            <div class="form-group">

              <div class="input-group">

                <span class="input-group-addon"><i class="fa fa-key"></i></span>

                <input type="number" min="0" max="9999999999" class="form-control input-lg" name="nuevoDocumentoId"
                  placeholder="Ingresar documento *" required>

              </div>

            </div>


            <!-- entrada para telefono -->

            <div class="form-group">

              <div class="input-group">

                <span class="input-group-addon"><i class="fa fa-phone"></i></span>

                <input type="text" class="form-control input-lg" name="nuevoTelefono" placeholder="Ingresar teléfono *"
                  data-inputmask="'mask':'(999) 999-9999'" data-mask required>

              </div>

            </div>


            <!-- entrada para Email -->

            <div class="form-group">

              <div class="input-group">

                <span class="input-group-addon"><i class="fa fa-envelope"></i></span>

                <!--<input type="email" class="form-control input-lg" name="nuevoEmail" placeholder="Ingresar email" required>-->
                <input type="email" class="form-control input-lg" name="nuevoEmail" placeholder="Ingresar email">

              </div>

            </div>


            <!-- entrada para departamento -->

            <div class="form-group">

              <div class="input-group">

                <span class="input-group-addon"><i class="fa fa-building"></i></span>

                <input type="text" class="form-control input-lg" name="nuevoDepartamento"
                  placeholder="Ingresar departamento">

              </div>

            </div>


            <!-- entrada para ciudad -->

            <div class="form-group">

              <div class="input-group">

                <span class="input-group-addon"><i class="fa fa-map-marker"></i></span>

                <input type="text" class="form-control input-lg" name="nuevoCiudad" placeholder="Ingresar Ciudad">

              </div>

            </div>


            <!-- entrada para la direccion -->

            <div class="form-group">

              <div class="input-group">

                <span class="input-group-addon"><i class="fa fa-home"></i></span>

                <input type="text" class="form-control input-lg" name="nuevaDireccion"
                  placeholder="Ingresar dirección *" required>

              </div>

            </div>


            <!-- entrada para Tipo de Documento (DIAN) -->

            <div class="form-group">

              <div class="input-group">

                <span class="input-group-addon"><i class="fa fa-id-card"></i></span>

                <select class="form-control input-lg" name="nuevoTipoDocumento" id="nuevoTipoDocumento">
                  <option value="">Seleccione tipo de documento</option>
                  <option value="3" selected>NIT - Número de Identificación Tributaria</option>
                  <option value="1">Cédula de Ciudadanía (CC)</option>
                  <option value="2">Cédula de Extranjería (CE)</option>
                  <option value="4">Pasaporte</option>
                  <option value="5">Tarjeta de Identidad</option>
                  <option value="6">Registro Civil</option>
                </select>

              </div>
              <small class="text-muted">Tipo de documento para facturación electrónica (DIAN)</small>

            </div>


            <!-- entrada para Municipio (DIAN) -->

            <div class="form-group">

              <div class="input-group">

                <span class="input-group-addon"><i class="fa fa-map-marker"></i></span>

                <select class="form-control input-lg" name="nuevoMunicipio" id="nuevoMunicipio">
                  <option value="">Seleccione municipio</option>
                  <option value="11001" selected>Bogotá D.C.</option>
                  <option value="05001">Medellín - Antioquia</option>
                  <option value="76001">Cali - Valle del Cauca</option>
                  <option value="08001">Barranquilla - Atlántico</option>
                  <option value="13001">Cartagena - Bolívar</option>
                  <option value="68001">Bucaramanga - Santander</option>
                  <option value="66001">Pereira - Risaralda</option>
                  <option value="17001">Manizales - Caldas</option>
                  <option value="54001">Cúcuta - Norte de Santander</option>
                  <option value="63001">Armenia - Quindío</option>
                  <option value="73001">Ibagué - Tolima</option>
                  <option value="20001">Valledupar - Cesar</option>
                  <option value="50001">Villavicencio - Meta</option>
                  <option value="19001">Popayán - Cauca</option>
                  <option value="52001">Pasto - Nariño</option>
                </select>

              </div>
              <small class="text-muted">Municipio para facturación electrónica (DIAN)</small>

            </div>


            <!-- entrada para estatus -->
            <input type="hidden" name="nuevoEstatus" value="nuevo">

            <!-- crear estado clientes -->
            <input type="hidden" name="vistaOrigen" value="clientes">



            <!-- Estatus 
            <div class="form-group"> 
              <label for="editarEstatus">Estatus</label>
              <select class="form-control" name="editarEstatus" id="editarEstatus">
                <option value="contactado">Contactado</option>
                <option value="en espera">En espera</option>
                <option value="interesado">Interesado</option>
                <option value="no interesado">No interesado</option>
              </select>
            </div>
            -->

            <!-- entrada para la fecha naciminiento -->
            <!--
            <div class="form-group">          
            <div class="input-group">              
              <span class="input-group-addon"><i class="fa fa-calendar"></i></span>
              <input type="text" class="form-control input-lg" name="nuevaFechaNacimiento" placeholder="Ingresar fecha de nacimiento" data-inputmask="'alias': 'yyyy/mm/dd'" data-mask>
             </div>
           </div>
           -->

            <!-- entrada para notas -->

            <div class="form-group">

              <div class="input-group">

                <span class="input-group-addon"><i class="fa fa-pencil-square-o"></i></span>

                <input type="text" class="form-control input-lg" name="nuevaNota" placeholder="Ingresar Nota">

              </div>

            </div>


          </div>

        </div>

        <!--=====================================
        PIE DEL MODAL
        ======================================-->

        <div class="modal-footer">

          <button type="button" class="btn btn-default pull-left" data-dismiss="modal">Salir</button>
          <button type="submit" class="btn btn-primary">Guardar cliente</button>

        </div>

      </form>


      <?php

      $crearCliente = new ControladorClientes();
      $crearCliente->ctrCrearCliente();

      ?>

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

        <?php CSRF::insertToken(); ?>

        <!--=====================================
      CABEZA DEL MODAL
      ======================================-->

        <div class="modal-header" style="background:#3c8dbc; color: white">

          <button type="button" class="close" data-dismiss="modal">&times;</button>
          <h4 class="modal-title">Editar cliente</h4>

        </div>

        <!--=====================================
      CUERPO DEL MODAL
      ======================================-->

        <div class="modal-body">

          <div class="box-body">

            <!-- entrada para nombre -->

            <div class="form-group">

              <div class="input-group">

                <span class="input-group-addon"><i class="fa fa-user"></i></span>

                <input type="text" class="form-control input-lg" name="editarCliente" id="editarCliente" required>
                <input type="hidden" id="idCliente" name="idCliente">

              </div>

            </div>


            <!-- entrada para documento ID -->

            <div class="form-group">

              <div class="input-group">

                <span class="input-group-addon"><i class="fa fa-key"></i></span>

                <input type="number" min="0" class="form-control input-lg" name="editarDocumentoId"
                  id="editarDocumentoId" placeholder="Documento" required>

              </div>

            </div>


            <!-- entrada para telefono -->

            <div class="form-group">

              <div class="input-group">

                <span class="input-group-addon"><i class="fa fa-phone"></i></span>

                <input type="text" class="form-control input-lg" name="editarTelefono" id="editarTelefono"
                  data-inputmask="'mask':'(999) 999-9999'" data-mask placeholder="Celular" required>

              </div>

            </div>


            <!-- entrada para Email -->

            <div class="form-group">

              <div class="input-group">

                <span class="input-group-addon"><i class="fa fa-envelope"></i></span>

                <!--<input type="email" class="form-control input-lg" name="editarEmail" id="editarEmail" required>-->
                <input type="email" class="form-control input-lg" name="editarEmail" id="editarEmail"
                  placeholder="Correo Electrónico">

              </div>

            </div>


            <!-- entrada para la departamento -->

            <div class="form-group">

              <div class="input-group">

                <span class="input-group-addon"><i class="fa fa-building"></i></span>

                <input type="text" class="form-control input-lg" name="editarDepartamento" id="editarDepartamento"
                  placeholder="Departamento">

              </div>

            </div>


            <!-- entrada para la ciudad -->

            <div class="form-group">

              <div class="input-group">

                <span class="input-group-addon"><i class="fa fa-map-marker"></i></span>

                <input type="text" class="form-control input-lg" name="editarCiudad" id="editarCiudad"
                  placeholder="Ciudad">

              </div>

            </div>


            <!-- entrada para la direccion -->

            <div class="form-group">

              <div class="input-group">

                <span class="input-group-addon"><i class="fa fa-home"></i></span>

                <input type="text" class="form-control input-lg" name="editarDireccion" id="editarDireccion"
                  placeholder="Dirección" required>

              </div>

            </div>


            <!-- entrada para Tipo de Documento (DIAN) -->

            <div class="form-group">

              <div class="input-group">

                <span class="input-group-addon"><i class="fa fa-id-card"></i></span>

                <select class="form-control input-lg" name="editarTipoDocumento" id="editarTipoDocumento">
                  <option value="">Seleccione tipo de documento</option>
                  <option value="3">NIT - Número de Identificación Tributaria</option>
                  <option value="1">Cédula de Ciudadanía (CC)</option>
                  <option value="2">Cédula de Extranjería (CE)</option>
                  <option value="4">Pasaporte</option>
                  <option value="5">Tarjeta de Identidad</option>
                  <option value="6">Registro Civil</option>
                </select>

              </div>
              <small class="text-muted">Tipo de documento para facturación electrónica (DIAN)</small>

            </div>


            <!-- entrada para Municipio (DIAN) -->

            <div class="form-group">

              <div class="input-group">

                <span class="input-group-addon"><i class="fa fa-map-marker"></i></span>

                <select class="form-control input-lg" name="editarMunicipio" id="editarMunicipio">
                  <option value="">Seleccione municipio</option>
                  <option value="11001">Bogotá D.C.</option>
                  <option value="05001">Medellín - Antioquia</option>
                  <option value="76001">Cali - Valle del Cauca</option>
                  <option value="08001">Barranquilla - Atlántico</option>
                  <option value="13001">Cartagena - Bolívar</option>
                  <option value="68001">Bucaramanga - Santander</option>
                  <option value="66001">Pereira - Risaralda</option>
                  <option value="17001">Manizales - Caldas</option>
                  <option value="54001">Cúcuta - Norte de Santander</option>
                  <option value="63001">Armenia - Quindío</option>
                  <option value="73001">Ibagué - Tolima</option>
                  <option value="20001">Valledupar - Cesar</option>
                  <option value="50001">Villavicencio - Meta</option>
                  <option value="19001">Popayán - Cauca</option>
                  <option value="52001">Pasto - Nariño</option>
                </select>

              </div>
              <small class="text-muted">Municipio para facturación electrónica (DIAN)</small>

            </div>


            <!-- entrada para la fecha naciminiento -->
            <!--
            <div class="form-group">
            <div class="input-group">
              <span class="input-group-addon"><i class="fa fa-calendar"></i></span>
              <input type="text" class="form-control input-lg" name="editarFechaNacimiento" id="editarFechaNacimiento" data-inputmask="'alias': 'yyyy/mm/dd'" data-mask required>
             </div>
           </div>
           -->


            <!-- entrada para estado -->
            <div class="form-group">
              <div class="input-group">
                <span class="input-group-addon"><i class="fa fa-flag"></i></span>
                <select class="form-control input-lg" name="editarEstado" id="editarEstado" required>
                  <option value="">Seleccionar estado</option>

                  <?php
                  $item = null;
                  $valor = null;
                  $estadosS = ControladorEstadosClientes::ctrMostrarEstadosClientes($item, $valor);
                  foreach ($estadosS as $key => $v) {
                    echo '<option value="' . e($v["id"]) . '">' . e($v["nombre"]) . '</option>';
                  }
                  ?>

                </select>
              </div>
            </div>

            <!-- entrada para nota -->

            <div class="form-group">

              <div class="input-group">

                <span class="input-group-addon"><i class="fa fa-pencil-square-o"></i></span>

                <input type="text" class="form-control input-lg" name="editarNota" id="editarNota" placeholder="Notas">

              </div>

            </div>


            <!-- entrada para estatus -->
            <!--<input type="hidden" name="editarEstatus" id="editarEstatus">-->



          </div>

        </div>

        <!--=====================================
        PIE DEL MODAL
        ======================================-->

        <div class="modal-footer">

          <button type="button" class="btn btn-default pull-left" data-dismiss="modal">Salir</button>
          <button type="submit" class="btn btn-primary">Guardar cambios</button>

        </div>

      </form>

    </div>

  </div>

</div>


<?php
/*
$eliminarCliente = new ControladorClientes();
$eliminarCliente->ctrEliminarCliente();
*/
?>



<!-- Choices.js para Campo estatus-->
<script src="https://cdn.jsdelivr.net/npm/choices.js/public/assets/scripts/choices.min.js"></script>


<!-- Filtro estatus tabla 1 -->
<script>
  function filterTable1() {
    var filtro = $('#filtroEstatus1').val();
    var $rows = $('.tablas1 tbody tr').not('.fila-detalle-row');
    $rows.each(function () {
      var $mainRow = $(this);
      var $detalleRow = $mainRow.next('.fila-detalle-row');
      var estado = $mainRow.find('select.cambiarEstatus').val();
      if (filtro === "" || estado === filtro) {
        $mainRow.show();
        $detalleRow.show();
      } else {
        $mainRow.hide();
        $detalleRow.hide();
      }
    });
  }
  $(document).ready(function () {
    // Ejecutar filtro al cargar si hay valor
    filterTable1();
    // Si usas AJAX para cambiar estatus, llama a filterTable1() después de actualizar
  });
  $(document).ready(function () {
    // Mover modales al body para evitar conflictos de posicionamiento
    if ($('#modalImportarClientes').length) $('#modalImportarClientes').appendTo('body');
    if ($('#modalGestionarEstados').length) $('#modalGestionarEstados').appendTo('body');
    if ($('#modalEditarEstado').length) $('#modalEditarEstado').appendTo('body');
  });
</script>





<!--=====================================
MODAL IMPORTAR CLIENTES DESDE CSV
======================================-->

<div id="modalImportarClientes" class="modal fade" role="dialog">

  <div class="modal-dialog">

    <div class="modal-content">

      <form role="form" method="post" enctype="multipart/form-data">

        <?php CSRF::insertToken(); ?>

        <!--=====================================
        CABEZA DEL MODAL
        ======================================-->

        <div class="modal-header" style="background:#3c8dbc; color:white">

          <button type="button" class="close" data-dismiss="modal">&times;</button>

          <h4 class="modal-title">Exportar / Importar Clientes</h4>

        </div>

        <!--=====================================
        CUERPO DEL MODAL
        ======================================-->

        <div class="modal-body">

          <div class="box-body">

            <div class="alert alert-info">

              <h4><i class="icon fa fa-info"></i> Instrucciones:</h4>

              <ol>
                <li>Descarga la plantilla CSV haciendo clic en el botón de abajo</li>
                <li>Completa los datos de los clientes (Tipo de Persona, Tipo Doc, Documento, Nombre, Teléfono,
                  Dirección y Municipio son obligatorios)</li>
                <li><strong>Tipo Persona:</strong> Use "Persona natural" o "Persona juridica"</li>
                <li><strong>Tipo Documento:</strong> Use CC, CE, DE, NIT, NUIP o PA</li>
                <li><strong>Persona Jurídica:</strong> Requiere que el tipo de documento sea NIT obligatoriamente</li>
                <li><strong>Municipio:</strong> Use el formato "Municipio - Departamento" (Ej: Medellin - Antioquia)
                </li>
                <li>Sube el archivo CSV completado</li>
              </ol>

            </div>


            <!-- BOTÓN PARA DESCARGAR PLANTILLA -->

            <div class="form-group text-center">

              <a href="vistas/modulos/descargar-plantilla-clientes.php" class="btn btn-info">

                <i class="fa fa-download"></i> Descargar Plantilla CSV para Clientes

              </a>

            </div>

            <hr>

            <!-- ENTRADA PARA SUBIR ARCHIVO CSV -->

            <div class="form-group">

              <label>Seleccionar archivo CSV:</label>

              <input type="file" class="form-control" name="archivoCSV" accept=".csv" required>

            </div>

          </div>

        </div>

        <!--=====================================
        PIE DEL MODAL
        ======================================-->

        <div class="modal-footer">

          <button type="button" class="btn btn-default pull-left" data-dismiss="modal">Cancelar</button>

          <button type="submit" class="btn btn-success">Importar Clientes</button>

        </div>

        <?php
        $importar = new ControladorClientes();
        $importar->ctrImportarClientes();
        ?>

      </form>

    </div>

  </div>

</div>


<!--=====================================
MODAL GESTIONAR ESTADOS
======================================-->

<div id="modalGestionarEstados" class="modal fade" role="dialog">

  <div class="modal-dialog modal-lg">

    <div class="modal-content">

      <!--=====================================
      CABEZA DEL MODAL
      ======================================-->

      <div class="modal-header" style="background:#3c8dbc; color: white">
        <button type="button" class="close" data-dismiss="modal">&times;</button>
        <h4 class="modal-title">Gestionar Estados de Clientes</h4>
      </div>

      <!--=====================================
      CUERPO DEL MODAL
      ======================================-->

      <div class="modal-body">

        <!-- Formulario agregar estado -->
        <div class="panel panel-primary">
          <div class="panel-heading">
            <h3 class="panel-title">Agregar Nuevo Estado</h3>
          </div>
          <div class="panel-body">
            <form role="form" method="post" id="formAgregarEstado">

              <?php CSRF::insertToken(); ?>

              <!-- Campo oculto para indicar origen -->
              <input type="hidden" name="origenModal" value="clientes">

              <div class="row">
                <div class="col-md-5">
                  <div class="form-group">
                    <input type="text" class="form-control" name="nuevoEstadoNombre" placeholder="Nombre del estado *"
                      required>
                  </div>
                </div>
                <div class="col-md-3">
                  <div class="form-group">
                    <input type="color" class="form-control" name="nuevoEstadoColor" value="#3c8dbc"
                      style="height: 34px;">
                  </div>
                </div>
                <div class="col-md-4">
                  <button type="submit" class="btn btn-primary btn-block">
                    <i class="fa fa-plus"></i> Agregar
                  </button>
                </div>
              </div>

            </form>
          </div>
        </div>

        <!-- Lista de estados -->
        <div class="panel panel-default">
          <div class="panel-heading">
            <h3 class="panel-title">Estados Existentes</h3>
          </div>
          <div class="panel-body">
            <div class="table-responsive">
              <table class="table table-bordered table-striped tablaEstadosClientes">
                <thead>
                  <tr>
                    <th>#</th>
                    <th>Nombre</th>
                    <th>Color</th>
                    <th>Acciones</th>
                  </tr>
                </thead>
                <tbody>
                  <?php
                  $estados = ControladorEstadosClientes::ctrMostrarEstadosClientes(null, null);
                  foreach ($estados as $key => $value) {
                    echo '<tr>
                      <td>' . e($key + 1) . '</td>
                      <td><span class="badge" style="background-color: ' . e($value["color"]) . '">' . e($value["nombre"]) . '</span></td>
                      <td><input type="color" value="' . e($value["color"]) . '" disabled style="width: 50px;"></td>
                      <td>
                        <button class="btn btn-warning btn-xs btnEditarEstado"
                          data-nombre="' . e($value["nombre"]) . '"
                          data-color="' . e($value["color"]) . '"
                          data-orden="' . e($value["orden"]) . '"
                          data-toggle="modal"
                          data-target="#modalEditarEstado">
                          <i class="fa fa-pencil"></i>
                        </button>
                        <button class="btn btn-danger btn-xs btnEliminarEstado" idEstado="' . e($value["id"]) . '" nombreEstado="' . e($value["nombre"]) . '"><i class="fa fa-times"></i></button>
                      </td>
                    </tr>';
                  }
                  ?>
                </tbody>
              </table>
            </div>
          </div>
        </div>

      </div>

      <!--=====================================
      PIE DEL MODAL
      ======================================-->

      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>
      </div>

    </div>

  </div>

</div>

<!--=====================================
MODAL EDITAR ESTADO
======================================-->

<div id="modalEditarEstado" class="modal fade" role="dialog">

  <div class="modal-dialog">

    <div class="modal-content">

      <form role="form" method="post">

        <?php CSRF::insertToken(); ?>

        <!--=====================================
        CABEZA DEL MODAL
        ======================================-->

        <div class="modal-header" style="background:#3c8dbc; color: white">
          <button type="button" class="close" data-dismiss="modal">&times;</button>
          <h4 class="modal-title">Editar Estado</h4>
        </div>

        <!--=====================================
        CUERPO DEL MODAL
        ======================================-->

        <div class="modal-body">

          <div class="box-body">

            <!-- ENTRADA PARA EL NOMBRE -->
            <div class="form-group">
              <div class="input-group">
                <span class="input-group-addon"><i class="fa fa-flag"></i></span>
                <input type="text" class="form-control input-lg" name="editarEstadoNombre" id="editarEstadoNombre"
                  required>
                <input type="hidden" name="idEstado" id="idEstado">
              </div>
            </div>

            <!-- ENTRADA PARA EL COLOR -->
            <div class="form-group">
              <div class="input-group">
                <span class="input-group-addon"><i class="fa fa-paint-brush"></i></span>
                <input type="color" class="form-control input-lg" name="editarEstadoColor" id="editarEstadoColor"
                  required style="height: 46px;">
              </div>
            </div>

            <!-- Campo oculto para el orden -->
            <input type="hidden" name="editarEstadoOrden" id="editarEstadoOrden">

            <!-- Campo oculto para indicar origen clientes -->
            <input type="hidden" name="origenEdicion" value="clientes">

          </div>

        </div>

        <!--=====================================
        PIE DEL MODAL
        ======================================-->

        <div class="modal-footer">
          <button type="button" class="btn btn-default pull-left" data-dismiss="modal">Salir</button>
          <button type="submit" class="btn btn-primary">Guardar cambios</button>
        </div>

      </form>

    </div>

  </div>

</div>

<!-- Script para recargar la página después de agregar un estado -->
<script>
  $(document).ready(function () {
    // Detectar si se agregó un estado exitosamente
    <?php if (isset($_GET["estadoCreado"]) && $_GET["estadoCreado"] == "ok"): ?>
      swal({
        type: "success",
        title: "¡El estado ha sido guardado correctamente!",
        showConfirmButton: true,
        confirmButtonText: "Cerrar"
      }).then(function (result) {
        if (result.value) {
          window.location = "clientes";
        }
      });
      <?php
    endif; ?>

    <?php if (isset($_GET["estadoCreado"]) && $_GET["estadoCreado"] == "error"): ?>
      swal({
        type: "error",
        title: "¡El estado no pudo ser guardado!",
        showConfirmButton: true,
        confirmButtonText: "Cerrar"
      });
      <?php
    endif; ?>
  });
</script>

<?php
// Procesar acciones de estados AL FINAL de la página para evitar romper el HTML
$crearEstado = new ControladorEstadosClientes();
$crearEstado->ctrCrearEstado();

$editarEstado = new ControladorEstadosClientes();
$editarEstado->ctrEditarEstado();

$eliminarEstado = new ControladorEstadosClientes();
$eliminarEstado->ctrEliminarEstado();

$eliminarCliente = new ControladorClientes();
$eliminarCliente->ctrEliminarCliente();
?>