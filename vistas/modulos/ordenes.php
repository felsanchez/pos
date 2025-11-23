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

/* Cards para móvil - Órdenes */
.cards-ordenes {
  display: none;
}

.card-orden {
  background: #fff;
  border-radius: 6px;
  box-shadow: 0 1px 3px rgba(0,0,0,0.1);
  margin-bottom: 10px;
  padding: 10px;
  position: relative;
  border-left: 4px solid #dd4b39;
}

.card-orden-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 8px;
  padding-bottom: 8px;
  border-bottom: 1px solid #eee;
}

.card-orden-codigo {
  font-size: 14px;
  font-weight: bold;
  color: #dd4b39;
}

.card-orden-acciones .btn-group {
  display: flex;
  gap: 3px;
}

.card-orden-info-principal {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 8px;
  gap: 10px;
}

.card-orden-cliente {
  font-size: 15px;
  font-weight: bold;
  color: #333;
  flex: 1;
  margin: 0;
}

.card-orden-total {
  font-size: 16px;
  font-weight: bold;
  color: #dd4b39;
  white-space: nowrap;
  margin: 0;
}

.card-orden-info {
  display: flex;
  flex-direction: column;
  gap: 5px;
  margin-bottom: 8px;
}

.card-orden-info-fila {
  display: flex;
  justify-content: space-between;
  gap: 10px;
}

.card-orden-info-item {
  display: flex;
  align-items: center;
  font-size: 12px;
  color: #666;
  flex: 1;
}

.card-orden-info-item i {
  margin-right: 5px;
  width: 15px;
  text-align: center;
}

.card-orden-notas {
  background: #f9f9f9;
  padding: 8px;
  border-radius: 3px;
  margin-top: 8px;
  font-size: 12px;
  color: #666;
  border-left: 2px solid #3c8dbc;
}

.card-orden-observacion {
  background: #fff9e6;
  padding: 8px;
  border-radius: 3px;
  margin-top: 8px;
  font-size: 12px;
  color: #666;
  border-left: 2px solid #f39c12;
  cursor: pointer;
}

.card-orden-observacion:hover {
  background: #fff3d9;
}

.card-orden-imagen-icono {
  display: inline-block;
  padding: 4px 8px;
  background: #3c8dbc;
  color: white;
  border-radius: 3px;
  cursor: pointer;
  font-size: 11px;
}

.card-orden-imagen-icono:hover {
  background: #2e6da4;
}

/* Responsive */
@media (max-width: 767px) {
  .tabla-ordenes {
    display: none !important;
  }
  .cards-ordenes {
    display: block !important;
  }
}

@media (min-width: 768px) {
  .tabla-ordenes {
    display: block !important;
  }
  .cards-ordenes {
    display: none !important;
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

        <div class="box-body">

          <div class="tabla-ordenes table-responsive">
            <table id="example" class="table table-bordered table-striped tablas display nowrap">
              
            <thead>
              <tr>
                <th style="width: 10px">#</th>
                <th>Código</th>
                <th>Cliente</th>
                <th>Vendedor</th>
                <th>Imagen</th>
                <th>Forma de pago</th>
                <th>Neto</th>
                <th>Total</th>
                <th><i class="fa fa-magic"></i> Notas</th>
                <th><i class="fa fa-pencil-square"></i> Observación</th>
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

                        <td>'.$formatoCodigoVenta.$value["codigo"].'</td>'; 

                        /*
                        $itemCliente = "id";
                        $valorCliente = $value["id_cliente"];
                        $respuestaCliente = ControladorClientes::ctrMostrarClientes($itemCliente, $valorCliente);
                        echo'<td>'.$respuestaCliente["nombre"].'</td>';
                        */

                        $itemCliente = "id";
                        $valorCliente = $value["id_cliente"];
                        $respuestaCliente = ControladorClientes::ctrMostrarClientes($itemCliente, $valorCliente);

                          echo'<td>

                                  <span class="btnVerClienteDesdeVenta"
                                        data-toggle="modal"
                                        data-target="#modalEditarCliente"
                                        idCliente="'.$value["id_cliente"].'"
                                        style="cursor: pointer; color: #337ab7; text-decoration: underline;">
                                      '.$respuestaCliente["nombre"].'
                                  </span>
                              </td>';

                        $itemUsuario = "id";
                        $valorUsuario = $value["id_vendedor"];
                        $respuestaUsuario = ControladorUsuarios::ctrMostrarUsuarios($itemUsuario, $valorUsuario);
                        echo'<td>'.$respuestaUsuario["nombre"].'</td>';

                        // Validación de la foto
                        if($value["imagen"] != ""){
                              echo '<td><img src="'.$value["imagen"].'" class="img-thumbnail img-ampliar-orden" width="40px" style="cursor: pointer;" data-imagen="'.$value["imagen"].'" data-idventa="'.$value["id"].'"></td>';
                          }
                          else{
                              echo '<td><img src="vistas/img/ventas/default/sinventa.png" class="img-thumbnail img-ampliar-orden" width="40px" style="cursor: pointer;" data-imagen="vistas/img/ventas/default/sinventa.png" data-idventa="'.$value["id"].'"></td>';
                          }
                        
                         echo '<td>'.$moneda.' '.$value["metodo_pago"].'</td> 

                        <td>'.$moneda.' '.number_format($value["neto"],2).'</td> 

                        <td>'.$moneda.' '.number_format($value["total"],2).'</td>

                        <td contenteditable="true" class="celda-nota" data-id="'.$value['id'].'">'.$value['notas'].'</td>

                        <td contenteditable="true" class="celda-observacion" data-id="'.$value['id'].'">'.$value['observacion'].'</td>

                         <td>'.$value["fecha"];

                            // MUESTRA LOS BTN EN MOVIL
                            if ($_SESSION["perfil"] == "Administrador") {
                              echo '<button class="btn btn-danger btn-xs solo-movil btnEliminarVenta" style="float: right;" idVenta="'.$value["id"].'">
                                      <i class="fa fa-times"></i>
                                    </button>';
                            }

                            echo '<button class="btn btn-info btn-xs solo-movil btnImprimirFactura" style="float: right;" codigoVenta="'.$value["codigo"].'">
                              <i class="fa fa-print"></i>
                            </button>';

                            echo '<a href="index.php?ruta=editar-orden&idVenta='.$value["id"].'" class="btn btn-warning btn-xs solo-movil" style="float: right;">
                              <i class="fa fa-line-chart"></i>
                            </a>';

                            echo '</td>
                            
                        <td> 
                          <div class="btn-group">

                            <a class="btn btn-success" href="index.php?ruta=ventas&xml=' . $value["codigo"] . '">xml</a>

                            <button class="btn btn-info btnImprimirFactura" codigoVenta="' . $value["codigo"] . '">
                              <i class="fa fa-print"></i>
                            </button>

                            <a href="index.php?ruta=editar-orden&idVenta=' . $value["id"] . '" class="btn btn-warning">
                              <i class="fa fa-line-chart"></i>
                            </a>';

                            // Mostrar el botón solo si el usuario es Administrador
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

          </div>

          <!-- Cards para móvil -->
          <div class="cards-ordenes">

            <?php
            // Reutilizar la misma consulta de la tabla para evitar duplicar carga
            // $respuesta ya contiene las órdenes, no hacer nueva consulta

            foreach ($respuesta as $key => $value) {

              // Usar nombres que ya vienen del JOIN en la consulta SQL
              $nombreCliente = !empty($value["nombre_cliente"]) ? $value["nombre_cliente"] : "Cliente no encontrado";
              $nombreVendedor = !empty($value["nombre_vendedor"]) ? $value["nombre_vendedor"] : "Vendedor no encontrado";

              // Imagen
              $imagenOrden = !empty($value["imagen"]) ? $value["imagen"] : "vistas/img/ventas/default/sinventa.png";

              echo '<div class="card-orden">

                      <div class="card-orden-header">
                        <div class="card-orden-codigo">
                          '.$formatoCodigoVenta.$value["codigo"].'
                        </div>
                        <div class="card-orden-acciones">
                          <div class="btn-group">
                            <button class="btn btn-info btn-xs btnImprimirFactura" codigoVenta="'.$value["codigo"].'">
                              <i class="fa fa-print"></i>
                            </button>
                            <a href="index.php?ruta=editar-orden&idVenta='.$value["id"].'" class="btn btn-warning btn-xs">
                              <i class="fa fa-line-chart"></i>
                            </a>';

              if ($_SESSION["perfil"] == "Administrador") {
                echo '<button class="btn btn-danger btn-xs btnEliminarVenta" idVenta="'.$value["id"].'">
                        <i class="fa fa-times"></i>
                      </button>';
              }

              echo '      </div>
                        </div>
                      </div>

                      <div class="card-orden-info-principal">
                        <div class="card-orden-cliente">
                          <span class="btnVerClienteDesdeVenta"
                                data-toggle="modal"
                                data-target="#modalEditarCliente"
                                idCliente="'.$value["id_cliente"].'"
                                style="cursor: pointer; color: #dd4b39; text-decoration: underline;">
                            '.$nombreCliente.'
                          </span>
                        </div>
                        <div class="card-orden-total">
                          '.$moneda.' '.number_format($value["total"],2).'
                        </div>
                      </div>

                      <div class="card-orden-info">
                        <div class="card-orden-info-fila">
                          <div class="card-orden-info-item">
                            <i class="fa fa-calendar"></i> '.$value["fecha"].'
                          </div>
                          <div class="card-orden-info-item">
                            <i class="fa fa-credit-card"></i> '.$value["metodo_pago"].'
                          </div>
                        </div>
                        <div class="card-orden-info-fila">
                          <div class="card-orden-info-item">
                            <i class="fa fa-user"></i> '.$nombreVendedor.'
                          </div>
                          <div class="card-orden-info-item">
                            <i class="fa fa-money"></i> '.$moneda.' '.number_format($value["neto"],2).'
                          </div>
                        </div>
                      </div>

                      <div class="card-orden-imagen-icono img-ampliar-orden"
                           data-imagen="'.$imagenOrden.'"
                           data-idventa="'.$value["id"].'">
                        <i class="fa fa-image"></i> Ver comprobante
                      </div>';

              // Notas
              if(!empty($value["notas"])){
                echo '<div class="card-orden-notas">
                        <i class="fa fa-magic"></i> '.$value["notas"].'
                      </div>';
              }

              // Observación editable
              if(!empty($value["observacion"])){
                echo '<div class="card-orden-observacion celda-observacion" data-id="'.$value['id'].'" contenteditable="true">
                       '.$value["observacion"].'
                      </div>';
              } else {
                echo '<div class="card-orden-observacion celda-observacion" data-id="'.$value['id'].'" contenteditable="true">
                        <span style="color: #999;">Agregar observación...</span>
                      </div>';
              }

              echo '</div>';
            }
            ?>

          </div>

          <?php

            $eliminarVenta = new ControladorVentas();
            $eliminarVenta -> ctrEliminarVenta();

          ?>

        </div>

      </div>

    </section>

  </div>
 


<!-- Modal para ampliar/editar imagen de orden de venta -->
<div class="modal fade" id="modalAmpliarImagenOrden" tabindex="-1" role="dialog">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
        <h4 class="modal-title">Imagen de la Orden de Venta</h4>
      </div>
      <div class="modal-body text-center">
        <img id="imagenOrdenAmpliada" src="" class="img-responsive" style="max-width: 100%; margin: 0 auto; margin-bottom: 20px;">
        
        <hr>
        
        <div class="form-group">
          <label>Cambiar Imagen de la Orden</label>
          <input type="file" class="form-control nuevaImagenOrden" accept="image/*">
          <p class="help-block">Peso máximo de la imagen 2MB</p>
        </div>
        
        <input type="hidden" id="idOrdenImagen">
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">Cancelar</button>
        <button type="button" class="btn btn-primary btnGuardarImagenOrden">Guardar Imagen</button>
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

          <!-- entrada para nombre -->            
          <div class="form-group">          
            <div class="input-group">              
              <span class="input-group-addon"><i class="fa fa-user"></i></span>
              <input type="text" class="form-control input-lg" name="editarCliente" id="editarCliente" readonly>
              <input type="hidden" id="idCliente" name="idCliente">
             </div>
           </div>

            <!-- entrada para documento ID -->            
            <div class="form-group">          
            <div class="input-group">              
              <span class="input-group-addon"><i class="fa fa-key"></i></span>
              <input type="number" min="0" class="form-control input-lg" name="editarDocumentoId" id="editarDocumentoId" placeholder="Documento" readonly>
             </div>
           </div>

           <!-- entrada para telefono -->            
           <div class="form-group">          
          <div class="input-group">            
            <span class="input-group-addon"><i class="fa fa-phone"></i></span>
            <input type="text" class="form-control input-lg" name="editarTelefono"  id="editarTelefono" data-inputmask="'mask':'(999) 999-9999'" data-mask placeholder="Celular" readonly>
           </div>
         </div>

           <!-- entrada para Email -->            
            <div class="form-group">          
            <div class="input-group">              
              <span class="input-group-addon"><i class="fa fa-envelope"></i></span>
              <!--<input type="email" class="form-control input-lg" name="editarEmail" id="editarEmail" required>-->
              <input type="email" class="form-control input-lg" name="editarEmail" id="editarEmail" placeholder="Correo Electrónico" readonly>
             </div>
           </div>

           <!-- entrada para la departamento -->            
           <div class="form-group">          
          <div class="input-group">            
            <span class="input-group-addon"><i class="fa fa-building"></i></span>
            <input type="text" class="form-control input-lg" name="editarDepartamento" id="editarDepartamento" placeholder="Departamento" readonly>
           </div>
         </div>

         <!-- entrada para la ciudad -->            
         <div class="form-group">          
          <div class="input-group">            
            <span class="input-group-addon"><i class="fa fa-map-marker"></i></span>
            <input type="text" class="form-control input-lg" name="editarCiudad" id="editarCiudad" placeholder="Ciudad" readonly>
           </div>
         </div>

           <!-- entrada para la direccion -->            
            <div class="form-group">          
            <div class="input-group">              
              <span class="input-group-addon"><i class="fa fa-home"></i></span>
              <input type="text" class="form-control input-lg" name="editarDireccion" id="editarDireccion" placeholder="Dirección" required readonly>
             </div>
           </div>

          <!-- entrada para estado -->
         <div class="form-group">
          <div class="input-group">
            <span class="input-group-addon"><i class="fa fa-flag"></i></span>
            <input type="text" 
                  class="form-control input-lg" 
                  id="editarEstado" 
                  name="editarEstado" 
                  readonly
                  style="background-color: #f4f4f4; cursor: not-allowed;">
          </div>
        </div>

           <!-- entrada para nota -->            
           <div class="form-group">          
            <div class="input-group">              
              <span class="input-group-addon"><i class="fa fa-pencil-square-o"></i></span>
              <input type="text" class="form-control input-lg" name="editarNota" id="editarNota" placeholder="Notas" readonly>
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

<!--Guardar observaciones-->
<script>
$(document).on('blur', '.celda-observacion', function() {
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
    success: function(respuesta) {
      console.log("Respuesta del servidor:", respuesta);
    },
    error: function() {
      alert("Hubo un error al guardar la observación.");
    }
  });
});
</script>

<!-- Ampliar foto al hacer clic -->
<script>
$(document).on("click", ".img-ampliar-orden", function(){
    var rutaImagen = $(this).attr("data-imagen");
    var idVenta = $(this).attr("data-idventa");
    
    console.log("ID Orden:", idVenta);
    console.log("Ruta Imagen:", rutaImagen);
    
    $("#imagenOrdenAmpliada").attr("src", rutaImagen);
    $("#idOrdenImagen").val(idVenta);
    $(".nuevaImagenOrden").val("");
    $("#modalAmpliarImagenOrden").modal("show");
});

// Previsualizar nueva imagen cuando se selecciona
$(".nuevaImagenOrden").change(function(){
    var imagen = this.files[0];
    
    if(imagen){
        if(imagen["type"] != "image/jpeg" && imagen["type"] != "image/png"){
            $(".nuevaImagenOrden").val("");
            swal({
                title: "Error al subir la imagen",
                text: "¡La imagen debe estar en formato JPG o PNG!",
                type: "error",
                confirmButtonText: "¡Cerrar!"
            });
        }else if(imagen["size"] > 2000000){
            $(".nuevaImagenOrden").val("");
            swal({
                title: "Error al subir la imagen",
                text: "¡La imagen no debe pesar más de 2MB!",
                type: "error",
                confirmButtonText: "¡Cerrar!"
            });
        }else{
            var datosImagen = new FileReader;
            datosImagen.readAsDataURL(imagen);
            
            $(datosImagen).on("load", function(event){
                var rutaImagen = event.target.result;
                $("#imagenOrdenAmpliada").attr("src", rutaImagen);
            });
        }
    }
});

// Guardar la nueva imagen de la orden
$(document).on("click", ".btnGuardarImagenOrden", function(){
    
    var idVenta = $("#idOrdenImagen").val();
    var imagen = $(".nuevaImagenOrden")[0].files[0];
    
    console.log("ID al guardar:", idVenta);
    console.log("Imagen al guardar:", imagen);
    
    if(!imagen){
        swal({
            title: "Advertencia",
            text: "No has seleccionado ninguna imagen",
            type: "warning",
            confirmButtonText: "¡Cerrar!"
        });
        return;
    }
    
    if(!idVenta){
        swal({
            title: "Error",
            text: "No se pudo obtener el ID de la orden",
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
        success: function(respuesta){
            console.log("Respuesta del servidor:", respuesta);
            
            if(respuesta == "ok"){
                swal({
                    type: "success",
                    title: "¡La imagen ha sido actualizada correctamente!",
                    showConfirmButton: true,
                    confirmButtonText: "Cerrar"
                }).then(function(result){
                    if(result.value){
                        $("#modalAmpliarImagenOrden").modal("hide");
                        window.location = "ordenes";
                    }
                });
            }else{
                swal({
                    type: "error",
                    title: "Error al actualizar la imagen",
                    text: JSON.stringify(respuesta),
                    confirmButtonText: "Cerrar"
                });
            }
        },
        error: function(jqXHR, textStatus, errorThrown){
            console.log("Error AJAX:", textStatus, errorThrown);
            console.log("Respuesta:", jqXHR.responseText);
            
            swal({
                type: "error",
                title: "Error en la petición",
                text: "Por favor revisa la consola para más detalles",
                confirmButtonText: "Cerrar"
            });
        }
    });
});
</script>


<!-- Abrir modal de clientes desde ordenes -->
<script>
$(document).on("click", ".btnVerClienteDesdeVenta", function(){
    
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
        success: function(respuesta){
            
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
            $("#editarNotas").val(data["notas"]);
            
            // AGREGAR ESTA LÍNEA para preseleccionar el estado
            $("#editarEstado").val(data["estatus"]);
            
            // Si tienes más campos, agrégalos aquí:
            $("#editarDepartamento").val(data["departamento"]);
            $("#editarCiudad").val(data["ciudad"]);
            
            // Abrir el modal
            $('#modalEditarCliente').modal('show');
        }
    });
});
</script>