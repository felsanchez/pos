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
        Administrar orden de venta
      </h1>

      <ol class="breadcrumb">
        <li><a href="inicio"><i class="fa fa-dashboard"></i> Inicio</a></li>
        <li class="active">Administrar Ordenes de Venta</li>
      </ol>

    </section>

    <section class="content">

      <div class="box">

        <div class="box-header with-border">


          <a href="crear-orden">
            <button class="btn btn-primary">              
               Agregar orden
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

            <a href="index.php?ruta=ordenes" class="btn btn-default">Todas</a>
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
                <!--<th>Forma de pago</th>-->
                <th>Neto</th>
                <th>Total</th>
                <th>Fecha</th>
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
                  $respuesta = ControladorVentas::ctrRangoFechasVentasPorEstado($fechaInicial, $fechaFinal, "orden");
                  

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

                        
                        <td>$ '.number_format($value["neto"],2).'</td>

                        <td>$ '.number_format($value["total"],2).'</td>

                        <td>'.$value["fecha"].'</td>

                        <td>
                          <div class="btn-group">

                          <a class="btn btn-success" href="index.php?ruta=ventas&xml='.$value["codigo"].'">xml</a>

                            <button class="btn btn-info btnImprimirFactura" codigoVenta="'.$value["codigo"].'">

                              <i class="fa fa-print"></i>

                            </button>';

                          if($_SESSION["perfil"] =="Administrador"){

                            //echo'<button class="btn btn-warning btnEditarVenta" idVenta='.$value["id"].'"><i class="fa fa-pencil"></i></button>
                            echo '<a href="index.php?ruta=editar-orden&idVenta='.$value["id"].'" class="btn btn-warning"><i class="fa fa-line-chart"></i></a>
                             
                            <button class="btn btn-danger btnEliminarVenta" idVenta="'.$value["id"].'"><i class="fa fa-times"></i></button>';
                      
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
    var fechaFinal = end.format('YYYY-MM-DD');

    var nuevaURL = 'index.php?ruta=ordenes&fechaInicial=' + fechaInicial + '&fechaFinal=' + fechaFinal;
    window.location.href = nuevaURL;
  }
);

</script>


  