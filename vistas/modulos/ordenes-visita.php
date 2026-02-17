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

        <div class="box-body table-responsive">


         <table id="example" class="table table-bordered table-striped tablas display nowrap">
              
            <thead>
              <tr>
                <th>Código</th>
                <th>Cliente</th>
                <th>Forma de pago</th>
                <th>Total</th>
                <th>Fecha</th>
                <th>Acciones</th>
              </tr>             
            </thead>

              <tbody>
                
         
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
$("#example").on("click", ".btnVerClienteDesdeVenta", function(){
    
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



<!-- Codigo para el buscador para que busque solo por código exacto -->
<script>
$(document).ready(function() {
    
    // PASO 1: Limpiar completamente la tabla antes de inicializar
    $('#example tbody').empty();
    
    var tabla = $('#example').DataTable({
        "retrieve": true,
        "destroy": true,
        "processing": true,
        "serverSide": false,
        "ajax": null,
        "data": [],
        "columns": [
            { "data": 0 },
            { "data": 1 },
            { "data": 2 },
            { "data": 3 },
            { "data": 4 },
            { "data": 5 }
        ],
        "language": {
            "sProcessing":     "Procesando...",
            "sLengthMenu":     "Mostrar _MENU_ registros",
            "sZeroRecords":    "❌ No se encontró el pedido",
            "sEmptyTable":     "👆 Escribe el código para buscar",
            "sInfo":           "Mostrando _START_ a _END_ de _TOTAL_",
            "sInfoEmpty":      "0 pedidos",
            "sInfoFiltered":   "(de _MAX_ totales)",
            "sSearch":         "🔍 Código:",
            "oPaginate": {
                "sFirst":    "Primero",
                "sLast":     "Último",
                "sNext":     "Siguiente",
                "sPrevious": "Anterior"
            }
        }
    });
    
    // Obtener el input del buscador
    var inputBuscador = $('#example_filter input');
    
    // Desactivar búsqueda automática de DataTable
    inputBuscador.off('keyup.DT input.DT search.DT');
    
    // PASO 3: Control manual del buscador
    inputBuscador.on('keyup', function(e) {
        console.log('Tecla presionada, código: ' + e.keyCode);
        
        var busqueda = $(this).val().trim();
        console.log('Valor actual: ' + busqueda);
        
        // Si está vacío, limpiar tabla
        if(busqueda.length === 0) {
            tabla.clear().draw();
            return;
        }
        
        // Solo buscar al presionar Enter (keyCode 13)
        if(e.keyCode === 13) {
            console.log('ENTER presionado, buscando: ' + busqueda);
            
            $.ajax({
                url: "ajax/datatable-ventas-visita.ajax.php",
                type: "POST",
                dataType: "json",
                data: {
                    draw: 1,
                    search: { value: busqueda }
                },
                success: function(response) {
                    console.log('✓ Respuesta recibida:', response);
                    
                    tabla.clear();
                    if(response.data && response.data.length > 0) {
                        console.log('✓ Agregando ' + response.data.length + ' filas');
                        tabla.rows.add(response.data).draw();
                    } else {
                        console.log('✗ Sin datos en respuesta');
                        tabla.draw();
                    }
                },
                error: function(xhr, status, error) {
                    console.error('✗ Error AJAX:', status, error);
                    console.error('Respuesta:', xhr.responseText);
                    tabla.clear().draw();
                }
            });
        }
    });
    
    setTimeout(function() {
        inputBuscador.attr('placeholder', 'Código para venta (Enter para buscar)');
    }, 100);
});
</script>



<!-- Estilos personalizados para el buscador -->
<style>
/* Contenedor del buscador de DataTable */
.dataTables_filter {
    text-align: center !important;
    margin: 30px 0 !important;
    padding: 20px;
    background: linear-gradient(135deg, #3b82f6 0%, #1e3a8a 100%);
    border-radius: 15px;
    box-shadow: 0 15px 40px rgba(59, 130, 246, 0.4);
}

/* Label del buscador */
.dataTables_filter label {
    display: inline-flex;
    align-items: center;
    gap: 15px;
    font-size: 18px;
    font-weight: 600;
    color: white !important;
    text-transform: uppercase;
    letter-spacing: 1px;
}

/* Input del buscador */
.dataTables_filter input {
    width: 500px !important;
    padding: 18px 30px !important;
    font-size: 18px !important;
    border: none !important;
    border-radius: 50px !important;
    background: white !important;
    color: #1e3a8a !important;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2) !important;
    transition: all 0.3s ease !important;
    outline: none !important;
}

/* Placeholder del input */
.dataTables_filter input::placeholder {
    color: #64748b !important;
    font-style: italic;
}

/* Efecto hover en el input */
.dataTables_filter input:hover {
    box-shadow: 0 15px 40px rgba(0, 0, 0, 0.3) !important;
    transform: translateY(-2px) !important;
}

/* Efecto focus en el input */
.dataTables_filter input:focus {
    background: #f0f9ff !important;
    box-shadow: 0 15px 50px rgba(59, 130, 246, 0.5) !important;
    transform: scale(1.02) !important;
}

/* Animación al escribir */
@keyframes pulse {
    0%, 100% {
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
    }
    50% {
        box-shadow: 0 15px 50px rgba(59, 130, 246, 0.5);
    }
}

.dataTables_filter input:not(:placeholder-shown) {
    animation: pulse 2s infinite;
}

/* Responsive */
@media (max-width: 768px) {
    .dataTables_filter input {
        width: 100% !important;
        max-width: 350px !important;
    }
    
    .dataTables_filter label {
        flex-direction: column;
        gap: 10px;
    }
}
</style>