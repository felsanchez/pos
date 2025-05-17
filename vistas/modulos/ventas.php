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


<!-- DateRangePicker -->
<link rel="stylesheet" href="vistas/bower_components/bootstrap-daterangepicker/daterangepicker.css">
  
  
  
  <?php

/*echo "<pre>";
var_dump($_GET);
echo "</pre>";
*/

    $xml = ControladorVentas::ctrDescargarXML();

    if($xml){

      rename($_GET["xml"].".xml", "xml/".$_GET["xml"].".xml");
      echo '<a class="btn btn-block btn-success abrirXML" archivo="xml/'.$_GET["xml"].'.xml" href="ventas">Se ha creado correctamente el archivo XML<span class="fa fa-times pull-right"></span></a>';
    }
  ?>

  <div class="content-wrapper">
    <section class="content-header">

      <h1>
        Administrar ventas
      </h1>

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

          <!-- Formulario de filtro de fechas -->
           <!--
          <div class="formulario-fechas-container">
            <form id="filtro-fechas" class="formulario-fechas">
              <label for="tipo-fecha">Filtrar por</label>
              <select id="tipo-fecha" name="tipo" class="form-control">
                <option value="hoy">Hoy</option>
                <option value="ayer">Ayer</option>
                <option value="mes">Mes actual</option>
                <option value="personalizado">Personalizado</option>
              </select>

              <div id="campo-desde" class="form-group d-none">
                <label for="fecha-desde">Desde</label>
                <input type="date" id="fecha-desde" name="fecha_inicio" class="form-control">
              </div>

              <div id="campo-hasta" class="form-group d-none">
                <label for="fecha-hasta">Hasta</label>
                <input type="date" id="fecha-hasta" name="fecha_fin" class="form-control">
              </div>

              <button type="submit" class="btn btn-primary w-100 mt-2">Aplicar filtro</button>
            </form>
          </div>
          -->

          <div class="pull-right">
            <button class="btn btn-default" id="daterange-btn">
              <span>
                <i class="fa fa-calendar"></i> Rango de fecha
              </span>
              <i class="fa fa-caret-down"></i>
            </button>

            <a href="index.php?ruta=ventas" class="btn btn-default">Mostrar Todo</a>
            <!--<a href="index.php?ruta=ventas&fechaInicial=<?php echo date('Y-m-d', strtotime('-1 day')); ?>&fechaFinal=<?php echo date('Y-m-d', strtotime('-1 day')); ?>" class="btn btn-default">Hoy</a>
            <a href="index.php?ruta=ventas&fechaInicial=<?php echo date('Y-m-d', strtotime('-2 day')); ?>&fechaFinal=<?php echo date('Y-m-d', strtotime('-2 day')); ?>" class="btn btn-default">Ayer</a>
            <a href="index.php?ruta=ventas&fechaInicial=<?php echo date('Y-m-01'); ?>&fechaFinal=<?php echo date('Y-m-d'); ?>" class="btn btn-default">Mes actual</a>-->
          </div>

   
        </div>

        <div class="box-body table-responsive">

          <table id="example" class="table table-bordered table-striped tablas display nowrap">
              
            <thead>
              <tr>
                <th style="width: 10px">#</th>
                <th>Código factura</th>
                <th>Cliente</th>
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

                  if (isset($_GET["fechaInicial"]) && isset($_GET["fechaFinal"])) {
                    $fechaInicial = $_GET["fechaInicial"];
                    $fechaFinal = $_GET["fechaFinal"];
                    echo "<p>Filtrando desde $fechaInicial hasta $fechaFinal</p>";
                  } else {
                    $fechaInicial = null;
                    $fechaFinal = null;
                    echo "<p>Mostrando todas las ventas</p>";
                  }

                  //$respuesta = ControladorVentas::ctrRangoFechasVentas($fechaInicial, $fechaFinal);
                  $respuesta = ControladorVentas::ctrRangoFechasVentasPorEstado($fechaInicial, $fechaFinal, "venta");


                  foreach ($respuesta as $key => $value) {
                    
                    echo '<tr>
                        <td>'.($key+1).'</td>

                        <td>'.$value["codigo"].'</td>';

                        $itemCliente = "id";
                        $valorCliente = $value["id_cliente"];
                        $respuestaCliente = ControladorClientes::ctrMostrarClientes($itemCliente, $valorCliente);
                        echo'<td>'.$respuestaCliente["nombre"].'</td>';

                        $itemUsuario = "id";
                        $valorUsuario = $value["id_vendedor"];
                        $respuestaUsuario = ControladorUsuarios::ctrMostrarUsuarios($itemUsuario, $valorUsuario);
                        echo'<td>'.$respuestaUsuario["nombre"].'</td>

                        <td>'.$value["metodo_pago"].'</td>

                        <td>$ '.number_format($value["neto"],2).'</td>

                        <td>$ '.number_format($value["total"],2).'</td>

                        <td contenteditable="true" class="celda-nota" data-id="'.$value['id'].'">'.$value['notas'].'</td>


                        <td>'.$value["fecha"].'</td>

                        <td>
                          <div class="btn-group">

                            <a class="btn btn-success" href="index.php?ruta=ventas&xml=' . $value["codigo"] . '">xml</a>

                            <button class="btn btn-info btnImprimirFactura" codigoVenta="' . $value["codigo"] . '">
                              <i class="fa fa-print"></i>
                            </button>

                            <button class="btn btn-warning btnEditarVenta" idVenta="' . $value["id"] . '">
                              <i class="fa fa-eye"></i>
                            </button>';

                            if ($_SESSION["perfil"] == "Administrador") {
                              echo '<button class="btn btn-danger btnEliminarVenta" idVenta="' . $value["id"] . '">
                                      <i class="fa fa-times"></i>
                                    </button>';
                            }

                            echo '</div>
                        </td>


                      </tr>';
                  }

                ?>


              </tbody>

          </table>

          <?php

            $eliminarVenta = new ControladorVentas(); 
            $eliminarVenta -> ctrEliminarVenta();

          ?>

        </div>

      </div>

    </section>

  </div>

<!--Ruta Clientes.js-->
<script src="vistas/js/ventas.js"></script>


<!-- DateRangePicker -->
<script src="vistas/bower_components/moment/min/moment.min.js"></script>
<script src="vistas/bower_components/bootstrap-daterangepicker/daterangepicker.js"></script>


<!-- Filtro de Fechas -->
<script>
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
    //var fechaFinal = end.format('YYYY-MM-DD');
    var fechaFinal = end.endOf('day').format('YYYY-MM-DD HH:mm:ss');
    var fechaInicial = start.startOf('day').format('YYYY-MM-DD HH:mm:ss');

    var nuevaURL = 'index.php?ruta=ventas&fechaInicial=' + encodeURIComponent(fechaInicial) + '&fechaFinal=' + encodeURIComponent(fechaFinal);
    //var nuevaURL = 'index.php?ruta=ventas&fechaInicial=' + fechaInicial + '&fechaFinal=' + fechaFinal;
    window.location.href = nuevaURL;
  }
);
</script>

<!--Guarddar notas-->
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



  