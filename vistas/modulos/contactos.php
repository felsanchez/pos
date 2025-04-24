<!-- Librería de estilos de Choices.js -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/choices.js/public/assets/styles/choices.min.css">

<style>
/*Campo estatus*/
/* Aplica color según clase del contenedor */
.choices.estatus-nuevo .choices__inner {
  background-color: #ffffff !important;
  color: rgba(0, 0, 0, 0.7) !important;
}

.choices.estatus-contactado .choices__inner {
  background-color: #d4edda !important;
  color: rgba(12, 37, 10, 0.69) !important;
}

.choices.estatus-en-espera .choices__inner {
  background-color: #fff3cd !important;
  color: #856404 !important;
}

.choices.estatus-interesado .choices__inner {
  background-color: #cce5ff !important;
  color: #004085 !important;
}

.choices.estatus-no-interesado .choices__inner {
  background-color: #f8d7da !important;
  color: #721c24 !important;
}


/* Opciones del menú desplegable en Choices.js según estatus */
.choices__list--dropdown .choices__item--selectable[data-value="nuevo"] {
  background-color: #ffffff !important;
  color: rgba(0, 0, 0, 0.7) !important;
}

.choices__list--dropdown .choices__item--selectable[data-value="contactado"] {
  background-color: #d4edda !important;
  color: rgba(12, 37, 10, 0.69) !important;
}

.choices__list--dropdown .choices__item--selectable[data-value="en espera"] {
  background-color: #fff3cd !important;
  color: #856404 !important;
}

.choices__list--dropdown .choices__item--selectable[data-value="interesado"] {
  background-color: #cce5ff !important;
  color: #004085 !important;
}

.choices__list--dropdown .choices__item--selectable[data-value="no interesado"] {
  background-color: #f8d7da !important;
  color: #721c24 !important;
}


/* Aumentar grosor del borde del dropdown al abrir */
.choices.is-open .choices__list--dropdown {
  border-width: 2px !important; /* Puedes ajustar el valor */
  border-color: #999 !important; /* Opcional: cambia el color si quieres */
  border-radius: 5px;
  border-style: inset;
}
</style>


<?php
      $editarCliente = new ControladorClientes();
      $editarCliente -> ctrEditarCliente();
?>

  
  <div class="content-wrapper">
    <section class="content-header">

      <h1>
        Administrar Contactos
      </h1>

      <ol class="breadcrumb">
        <li><a href="inicio"><i class="fa fa-dashboard"></i> Inicio</a></li>
        <li class="active">Administrar Contactos</li>
      </ol>

    </section>

    <section class="content">

      <div class="box">

        <div class="box-header with-border">

          <button class="btn btn-primary" data-toggle="modal" data-target="#modalAgregarCliente">
            
             Agregar Contacto

          </button>

        </div>

        <!--buscador estatus-->
        <select id="filtroEstatus" class="form-control filtro-estatus">
          <option value="">Todos</option>
          <option value="nuevo">Nuevo</option>
          <option value="contactado">Contactado</option>
          <option value="en espera">En espera</option>
          <option value="interesado">Interesado</option>
          <option value="no interesado">No interesado</option>
        </select>

        <div class="box-body table-responsive">

          <table class="table table-bordered table-striped tablas">
              
            <thead>
              <tr>
                <th style="width: 10px">#</th>
                <th>Nombre</th>
                <th>Documento ID</th>
                <th>Email</th>
                <th>Teléfono</th>
                <th>Departamento</th>
                <th>Ciudad</th>
                <th>Dirección</th>
                <th>Estado</th>
                <th>Notas</th>
                <th>Fecha de nacimiento</th>
                <th>Total compras</th>
                <th>Ultima compra</th>
                <th>Ingreso al sistema</th>
                <th>Acciones</th>
              </tr>             
            </thead>

              <tbody>

              <?php
                $item = null;
                $valor = null;
                $clientes = ControladorClientes::ctrMostrarClientes($item, $valor);
              ?>

              <?php foreach ($clientes as $key => $value): 
                $estatusClass = "estatus-" . str_replace(" ", "-", strtolower($value["estatus"]));
              ?>
                <tr>
                  <td><?php echo $key + 1; ?></td>
                  <td><?php echo $value["nombre"]; ?></td>
                  <td><?php echo $value["documento"]; ?></td>
                  <td><?php echo $value["email"]; ?></td>
                  <td><?php echo $value["telefono"]; ?></td>
                  <td><?php echo $value["departamento"]; ?></td>
                  <td><?php echo $value["ciudad"]; ?></td>
                  <td><?php echo $value["direccion"]; ?></td>
                  <td>
                    <select class="form-control cambiarEstatus <?php echo $estatusClass; ?>" data-id="<?php echo $value["id"]; ?>">
                      <option value="nuevo" <?php if($value["estatus"] == "nuevo") echo "selected"; ?>>Nuevo</option>
                      <option value="contactado" <?php if($value["estatus"] == "contactado") echo "selected"; ?>>Contactado</option>
                      <option value="en espera" <?php if($value["estatus"] == "en espera") echo "selected"; ?>>En espera</option>
                      <option value="interesado" <?php if($value["estatus"] == "interesado") echo "selected"; ?>>Interesado</option>
                      <option value="no interesado" <?php if($value["estatus"] == "no interesado") echo "selected"; ?>>No interesado</option>
                    </select>
                  </td>

                   <td><?php echo $value["notas"]; ?></td>
                  <td><?php echo $value["fecha_nacimiento"]; ?></td>
                  <td><?php echo $value["compras"]; ?></td>
                  <td><?php echo $value["ultima_compra"]; ?></td>
                  <td><?php echo $value["fecha"]; ?></td>
                  <td>
                    <div class="btn-group">
                      <button class="btn btn-warning btnEditarCliente" data-toggle="modal" data-target="#modalEditarCliente" idCliente="<?php echo $value["id"]; ?>"><i class="fa fa-pencil"></i></button>
                      <button class="btn btn-danger btnEliminarCliente" idCliente="<?php echo $value["id"]; ?>"><i class="fa fa-times"></i></button>
                    </div>
                  </td>
                </tr>
              <?php endforeach; ?>

                
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

              <input type="text" class="form-control input-lg" name="nuevoCliente" id="nuevoCliente" placeholder="Ingresar nombre" required>

             </div>

           </div>


            <!-- entrada para documento ID -->
            
            <div class="form-group">
          
            <div class="input-group">
              
              <span class="input-group-addon"><i class="fa fa-key"></i></span>

              <input type="number" min="0" max="9999999999" class="form-control input-lg" name="nuevoDocumentoId" placeholder="Ingresar documento" required>

             </div>

           </div>


           <!-- entrada para telefono -->
            
            <div class="form-group">
          
            <div class="input-group">
              
              <span class="input-group-addon"><i class="fa fa-phone"></i></span>

              <input type="text" class="form-control input-lg" name="nuevoTelefono" placeholder="Ingresar teléfono" data-inputmask="'mask':'(999) 999-9999'" data-mask required>

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

            <input type="text" class="form-control input-lg" name="nuevoDepartamento" placeholder="Ingresar departamento" required>

           </div>

         </div>


         <!-- entrada para ciudad -->
            
         <div class="form-group">
          
          <div class="input-group">
            
            <span class="input-group-addon"><i class="fa fa-map-marker"></i></span>

            <input type="text" class="form-control input-lg" name="nuevoCiudad" placeholder="Ingresar Ciudad" required>

           </div>

         </div>


           <!-- entrada para la direccion -->
            
            <div class="form-group">
          
            <div class="input-group">
              
              <span class="input-group-addon"><i class="fa fa-home"></i></span>

              <input type="text" class="form-control input-lg" name="nuevaDireccion" placeholder="Ingresar dirección" required>

             </div>

           </div>


           <!-- entrada para estatus -->
           <input type="hidden" name="nuevoEstatus" value="nuevo">


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
            
            <div class="form-group">
          
            <div class="input-group">
              
              <span class="input-group-addon"><i class="fa fa-calendar"></i></span>

              <input type="text" class="form-control input-lg" name="nuevaFechaNacimiento" placeholder="Ingresar fecha de nacimiento" data-inputmask="'alias': 'yyyy/mm/dd'" data-mask>

             </div>

           </div>


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
      $crearCliente -> ctrCrearCliente();

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

              <input type="number" min="0" class="form-control input-lg" name="editarDocumentoId" id="editarDocumentoId" required>

             </div>

           </div>


           <!-- entrada para telefono -->
            
           <div class="form-group">
          
          <div class="input-group">
            
            <span class="input-group-addon"><i class="fa fa-phone"></i></span>

            <input type="text" class="form-control input-lg" name="editarTelefono"  id="editarTelefono" data-inputmask="'mask':'(999) 999-9999'" data-mask required>

           </div>

         </div>


           <!-- entrada para Email -->
            
            <div class="form-group">
          
            <div class="input-group">
              
              <span class="input-group-addon"><i class="fa fa-envelope"></i></span>

              <!--<input type="email" class="form-control input-lg" name="editarEmail" id="editarEmail" required>-->
              <input type="email" class="form-control input-lg" name="editarEmail" id="editarEmail">

             </div>

           </div>


           <!-- entrada para la departamento -->
            
           <div class="form-group">
          
          <div class="input-group">
            
            <span class="input-group-addon"><i class="fa fa-building"></i></span>

            <input type="text" class="form-control input-lg" name="editarDepartamento" id="editarDepartamento" required>

           </div>

         </div>


         <!-- entrada para la ciudad -->
            
         <div class="form-group">
          
          <div class="input-group">
            
            <span class="input-group-addon"><i class="fa fa-map-marker"></i></span>

            <input type="text" class="form-control input-lg" name="editarCiudad" id="editarCiudad" required>

           </div>

         </div>


           <!-- entrada para la direccion -->
            
            <div class="form-group">
          
            <div class="input-group">
              
              <span class="input-group-addon"><i class="fa fa-home"></i></span>

              <input type="text" class="form-control input-lg" name="editarDireccion" id="editarDireccion" required>

             </div>

           </div>


           <!-- entrada para la fecha naciminiento -->
            
            <div class="form-group">
          
            <div class="input-group">
              
              <span class="input-group-addon"><i class="fa fa-calendar"></i></span>

              <input type="text" class="form-control input-lg" name="editarFechaNacimiento" id="editarFechaNacimiento" data-inputmask="'alias': 'yyyy/mm/dd'" data-mask required>

             </div>

           </div>


           <!-- entrada para nota -->
            
           <div class="form-group">
          
          <div class="input-group">
            
            <span class="input-group-addon"><i class="fa fa-pencil-square-o"></i></span>

            <input type="text" class="form-control input-lg" name="editarNota" id="editarNota">

           </div>

         </div>

          

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

 $eliminarCliente = new ControladorClientes();
 $eliminarCliente -> ctrEliminarCliente();

 ?>


<!--Campo estatus-->
<script>
$(document).on("click", ".btnEditarCliente", function () {
  var idCliente = $(this).attr("idCliente");

  var datos = new FormData();
  datos.append("idClienteEditar", idCliente);

  $.ajax({
    url: "ajax/clientes.ajax.php",
    method: "POST",
    data: datos,
    cache: false,
    contentType: false,
    processData: false,
    dataType: "json",
    success: function (respuesta) {

      $("#editarCliente").val(respuesta.nombre); // mantiene tu variable personalizada
      $("#editarDocumentoId").val(respuesta.documento); // mantiene tu variable personalizada
      $("#editarEmail").val(respuesta.email);
      $("#editarTelefono").val(respuesta.telefono);
      $("#editarDepartamento").val(respuesta.departamento);
      $("#editarCiudad").val(respuesta.ciudad);
      $("#editarDireccion").val(respuesta.direccion);
      $("#editarFechaNacimiento").val(respuesta.fecha_nacimiento);
      $("#editarEstatus").val(respuesta.estatus);
      $("#editarNota").val(respuesta.notas);

      // Input oculto para el ID del cliente
      $("#idCliente").val(respuesta.id);
    }
  });
});
</script>


<!-- Choices.js -->
<script src="https://cdn.jsdelivr.net/npm/choices.js/public/assets/scripts/choices.min.js"></script>


<!--Campo estatus-->
<script src="https://cdn.jsdelivr.net/npm/choices.js/public/assets/scripts/choices.min.js"></script>
<script>
  // Función para aplicar el color del estatus al contenedor de Choices.js
  function aplicarColorEstatus(select) {
    const value = select.value;
    const container = select.closest('.choices');

    if (!container) return;

    // Eliminar clases anteriores que empiecen con "estatus-"
    container.className = container.className
      .split(" ")
      .filter(cls => !cls.startsWith("estatus-"))
      .join(" ");

    // Agregar la nueva clase
    container.classList.add("estatus-" + value.replace(/ /g, "-").toLowerCase());
  }

  document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.cambiarEstatus').forEach(function (select) {
      const choices = new Choices(select, {
        searchEnabled: false,
        itemSelectText: ''
      });

      // Esperar a que Choices.js genere su HTML (usa setTimeout como fallback)
      setTimeout(() => {
        aplicarColorEstatus(select);
      }, 100);

      // Cambiar color dinámicamente al cambiar el valor
      select.addEventListener('change', function () {
        aplicarColorEstatus(select);
      });
    });
  });

  // Script para guardar el estatus por AJAX
  $(document).on("change", ".cambiarEstatus", function () {
    var idCliente = $(this).data("id");
    var nuevoEstatus = $(this).val();
    var select = $(this)[0]; // Para usarlo en aplicarColorEstatus

    $.ajax({
      url: "ajax/clientes.ajax.php",
      method: "POST",
      data: {
        idCliente: idCliente,
        nuevoEstatus: nuevoEstatus
      },
      success: function (respuesta) {
        console.log("RESPUESTA:", respuesta);
        if (respuesta === "ok") {
          aplicarColorEstatus(select); // Asegura aplicar color correcto después del cambio
        } else {
          alert("Error al guardar el estatus");
        }
      },
      error: function (xhr, status, error) {
        console.error("AJAX Error:", error);
      }
    });
  });
</script>

<!-- buscador estatus-->
<script>
  document.addEventListener('DOMContentLoaded', function () {
    const tabla = $('.tablas').DataTable(); // Asegúrate que DataTable esté inicializado

    const filtroSelect = document.getElementById('filtroEstatus');
    const choicesFiltro = new Choices(filtroSelect, {
      searchEnabled: false,
      itemSelectText: '',
      classNames: {
        containerOuter: 'choices filtro-estatus'
      }
    });

    const container = filtroSelect.closest('.choices');

    function aplicarColor() {
      container.classList.forEach(cls => {
        if (cls.startsWith('estatus-')) {
          container.classList.remove(cls);
        }
      });

      const valor = filtroSelect.value;
      if (valor) {
        container.classList.add('estatus-' + valor.replace(/ /g, '-').toLowerCase());
      }
    }

    aplicarColor(); // Al cargar
    filtroSelect.addEventListener('change', function () {
      aplicarColor();
      tabla.column(8).search(this.value).draw(); // Asegúrate de que la columna 8 es la del estatus
    });
  });
</script>







