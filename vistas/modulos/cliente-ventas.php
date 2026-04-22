<style>
.formulario-fechas-container {
  max-width: 300px;
  padding: 15px;
  border-radius: 10px;
  background-color: #ffffff;
  box-shadow: 0 2px 8px rgba(0,0,0,0.1);
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
</style>


<!-- Muestra los campos: 2, 6 y 8 en movil -->
<style> 
@media (max-width: 767px) {
  .tablasvent td,
  .tablasvent th {
    display: none;
  }
  .tablasvent td:nth-child(2),
  .tablasvent td:nth-child(6),
  .tablasvent td:nth-child(8),
  .tablasvent th:nth-child(2),
  .tablasvent th:nth-child(6),
  .tablasvent th:nth-child(8) {
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


<!-- DateRangePicker -->
<link rel="stylesheet" href="vistas/bower_components/bootstrap-daterangepicker/daterangepicker.css">

<?php

$xml = ControladorVentas::ctrDescargarXML();

if($xml){
  rename($_GET["xml"].".xml", "xml/".$_GET["xml"].".xml");
  echo '<a class="btn btn-block btn-success abrirXML" archivo="xml/'.$_GET["xml"].'.xml" href="ventas">Se ha creado correctamente el archivo XML<span class="fa fa-times pull-right"></span></a>';
}
?>

<div class="content-wrapper">
  <section class="content-header">

    <?php
      // Obtén el idCliente de la URL y el nombre del cliente:
      $idCliente = isset($_GET["idCliente"]) ? $_GET["idCliente"] : null;
      $nombreCliente = "";
      if ($idCliente) {
          $itemCliente = "id";
          $valorCliente = $idCliente;
          $respuestaCliente = ControladorClientes::ctrMostrarClientes($itemCliente, $valorCliente);
          if ($respuestaCliente) {
              $nombreCliente = $respuestaCliente["nombre"];
          }
      }
    ?>
    <?php if ($nombreCliente): ?>
      <h3>Ventas de: <?php echo htmlspecialchars($nombreCliente); ?></h3>
    <?php endif; ?>

    <ol class="breadcrumb">
      <li><a href="inicio"><i class="fa fa-dashboard"></i> Inicio</a></li>
      <li class="active">Administrar ventas</li>
    </ol>

  </section>

  <section class="content">
    <div class="box">
      <div class="box-header with-border">

        <a href="crear-venta">
          <button class="btn btn-primary">              
             Agregar venta
          </button>
        </a>

        <div class="pull-right">
          <button class="btn btn-default" id="daterange-btn">
            <span>
              <i class="fa fa-calendar"></i> Rango de fecha
            </span>
            <i class="fa fa-caret-down"></i>
          </button>
          <!-- Mostrar Todo para este cliente -->
          <a href="index.php?ruta=cliente-ventas&idCliente=<?php echo urlencode($idCliente); ?>" class="btn btn-default">Mostrar Todo</a>
        </div>
      </div>


       <!-- FILTRO DE FECHAS -->
      <script>
        $(function () {
          $('#daterange-btn').daterangepicker(
            {
              ranges   : {
                'Hoy'       : [moment(), moment()],
                'Ayer'      : [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
                'Últimos 7 días' : [moment().subtract(6, 'days'), moment()],
                'Últimos 30 días': [moment().subtract(29, 'days'), moment()],
                'Este mes'  : [moment().startOf('month'), moment().endOf('month')],
                'Mes pasado': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')]
              },
              startDate: moment(),
              endDate  : moment()
            },
            function (start, end) {
              var fechaInicial = start.format('YYYY-MM-DD');
              var fechaFinal = end.format('YYYY-MM-DD');
              var params = new URLSearchParams(window.location.search);
              var idCliente = params.get('idCliente');
              var nuevaURL = 'index.php?ruta=cliente-ventas';
              if (idCliente) {
                nuevaURL += '&idCliente=' + encodeURIComponent(idCliente);
              }
              nuevaURL += '&fechaInicial=' + encodeURIComponent(fechaInicial) + '&fechaFinal=' + encodeURIComponent(fechaFinal);
              window.location.href = nuevaURL;
            }
          );
        });
      </script>



      <div class="box-body table-responsive">
        <table id="example" class="table table-bordered table-striped tablas tablasvent display nowrap">
          <thead>
            <tr>
              <th style="width: 10px">#</th>
              <th>Código</th>
              <!--<th>Cliente</th>-->
              <th>Vendedor</th>
              <th>Forma de pago</th>
              <th>Neto</th>
              <th>Total</th>
              <th>Notas</th>
              <th>Fecha de creación</th>
              <th>Acciones</th>
            </tr>
          </thead>
          <tbody>
            <?php 
              // Fechas desde URL
              $fechaInicial = isset($_GET["fechaInicial"]) ? $_GET["fechaInicial"] : null;
              $fechaFinal   = isset($_GET["fechaFinal"]) ? $_GET["fechaFinal"] : null;

              if ($fechaInicial && $fechaFinal) {
                echo "<p>Filtrando desde $fechaInicial hasta $fechaFinal</p>";
              } else {
                echo "<p>Mostrando todas las ventas</p>";
              }

              // Si no hay cliente, no mostrar nada
              if (!$idCliente) {
                echo "<tr><td colspan='10'>Seleccione un cliente para ver sus ventas.</td></tr>";
              } else {
                // Trae TODAS las ventas según rango de fechas
                $respuesta = ControladorVentas::ctrRangoFechasVentasPorEstado($fechaInicial, $fechaFinal, "venta");


                // Filtrar solo ventas del cliente seleccionado
                $ventasCliente = [];
                foreach ($respuesta as $venta) {
                  if ($venta["id_cliente"] == $idCliente) {
                    $ventasCliente[] = $venta;
                  }
                }

                // Renderiza ventas filtradas
                foreach ($ventasCliente as $key => $value) {
                  echo '<tr>
                      <td>'.($key+1).'</td>
                      <td>'.$value["codigo"].'</td>';

                      $itemUsuario = "id";
                      $valorUsuario = $value["id_vendedor"];
                      $respuestaUsuario = ControladorUsuarios::ctrMostrarUsuarios($itemUsuario, $valorUsuario);

                      echo '<td>'.$respuestaUsuario["nombre"].'</td>
                      <td>'.$value["metodo_pago"].'</td>
                      <td>$ '.number_format($value["neto"]).'</td>
                      <td>$ '.number_format($value["total"]).'</td>
                      <td contenteditable="true" class="celda-nota" data-id="'.$value['id'].'">'.$value['notas'].'</td>
                      
                      <td>' . $value["fecha"] . '
                        <!-- **********BTN VERSION MOVIL******** -->
                        <button class="btn btn-warning btn-xs solo-movil btnEditarVenta" idVenta="' . $value["id"] . '">
                            <i class="fa fa-eye"></i>
                          </button>
                      </td>

                      <td>
                        <div class="btn-group">';

                        if (puedeAccion('ventas', 'imprimir')) {
                          echo '<button class="btn btn-info btnImprimirFactura" codigoVenta="' . $value["codigo"] . '">
                                  <i class="fa fa-print"></i>
                                </button>';
                        }

                        echo '<button class="btn btn-warning btnEditarVenta" idVenta="' . $value["id"] . '">
                                <i class="fa fa-eye"></i>
                              </button>';

                        if (puedeAccion('ventas', 'eliminar')) {
                          echo '<button class="btn btn-danger btnEliminarVenta" idVenta="' . $value["id"] . '">
                                  <i class="fa fa-times"></i>
                                </button>';
                        }

                        echo '</div>
                      </td>
                    </tr>';
                }
              }
            ?>
          </tbody>
        </table>

        <button class="btn btn-primary pull-left" onclick="location.href='clientes'">Regresar</button>

        <?php
          $eliminarVenta = new ControladorVentas(); 
          $eliminarVenta -> ctrEliminarVenta();
        ?>
      </div>
    </div>
  </section>
</div>


  <!-- Modal para mostrar ventas en móvil -->
<div class="modal fade" id="detalleClienteModal" tabindex="-1" role="dialog" aria-labelledby="detalleClienteLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="detalleClienteLabel">Ventas del Cliente</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body" id="detalleClienteBody">
        <!-- Aquí se cargan los detalles -->
      </div>
    </div>
  </div>
</div>



<!--========================================
=            SCRIPTS           =-->
<!--=====================================-->

<script src="vistas/js/ventas.js"></script>

<!-- DateRangePicker -->
<script src="vistas/bower_components/moment/min/moment.min.js"></script>
<script src="vistas/bower_components/bootstrap-daterangepicker/daterangepicker.js"></script>


<!--Guardar notas-->
<script>
$(document).on('blur', '.celda-nota', function() {
  const idVenta = $(this).data('id');
  const nuevaNota = $(this).text().trim();

  console.log("Guardando nota:", nuevaNota, "para ID:", idVenta); // <== prueba

  $.ajax({
    url: "ajax/datatable-ventas.ajax.php",
    method: "POST",
    data: {
      idVentaNota: idVenta,
      nuevaNota: nuevaNota
    },
    success: function(respuesta) {
      console.log("Respuesta del servidor:", respuesta);
    },
    error: function() {
      alert("Hubo un error al guardar la nota.");
    }
  });
});
</script> 


<!-- Mostrar modal de ventas en móvil -->
<script>
$(document).ready(function() {
  $('.btn-ver-venta').on('click', function() {
    var $row = $(this).closest('tr');
    var tds = $row.find('td');
    var detalle = `
      <p><strong>Codigo:</strong> ${tds.eq(1).text()}</p>
      <p><strong>Vendedor:</strong> ${tds.eq(2).text()}</p>
      <p><strong>Forma de pago:</strong> ${tds.eq(3).text()}</p>
      <p><strong>Neto:</strong> ${tds.eq(4).text()}</p>
      <p><strong>Total:</strong> ${tds.eq(5).text()}</p>
      <p><strong>Notas:</strong> ${tds.eq(6).text()}</p>
      <p><strong>Fecha Venta:</strong> ${tds.eq(7).text()}</p>
    `;
    $('#detalleClienteBody').html(detalle);
    $('#detalleClienteModal').modal('show');
  });
});
</script>