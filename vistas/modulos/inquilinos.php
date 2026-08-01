<?php
if ($_SESSION["perfil"] !== "_SystemMaster_") {
  echo '<script>window.location = "inicio";</script>';
  return;
}
?>

<div class="content-wrapper">

  <section class="content-header">
    
    <h1>
      Administrar Inquilinos (SaaS Multi-Tenant)
    </h1>

    <ol class="breadcrumb">
      <li><a href="inicio"><i class="fa fa-dashboard"></i> Inicio</a></li>
      <li class="active">Administrar Inquilinos</li>
    </ol>

  </section>

  <section class="content">

    <!--=====================================
    ALERTA DE INSTRUCCIONES
    ======================================-->
    <div class="alert alert-info alert-dismissible" style="background-color: #ebf8fc !important; color: #31708f !important; border-color: #bce8f1 !important; border-left: 5px solid #31708f !important;">
      <button type="button" class="close" data-dismiss="alert" aria-hidden="true" style="color: #31708f !important; opacity: 0.8;">&times;</button>
      <h4><i class="icon fa fa-info-circle" style="color: #31708f !important;"></i> Pasos obligatorios:</h4>
      <ul style="padding-left: 20px; font-size: 15px; margin-top: 5px;">
        <li style="margin-bottom: 5px;"><strong>Paso 1:</strong> Crea la BD en Hostinger (incluyendo el nombre, usuario y clave).</li>
        <li><strong>Paso 2:</strong> Crea la MISMA BD en este módulo (Para llenar la tabla "clientes_tenants" en la BD Master).</li>
        <li><strong>Paso 3:</strong> Ingresa a la BD creada en Hostinger e IMPORTAMOS su contenido (Completo y limpio).</li>
        <li><strong>Paso 4:</strong> En Hostinger ingresa a "gestion.kontrolpos.com/Panel/Dominios/Subdominios" para crear el respectivo SUBDOMINIO configurando la ruta hacia <strong><code>/public_html/gestion</code></strong>.</li>
        <li><strong>Paso 5:</strong> Aca puedes editar o eliminar los inquilinos existentes. Puedes colocar y "activo" o "suspendido" en sus estados.</li>
      </ul>
    </div>

    <div class="box">

      <div class="box-header with-border">
  
        <button class="btn btn-primary" data-toggle="modal" data-target="#modalAgregarTenant">
          
          Agregar Inquilino

        </button>

      </div>

      <div class="box-body">
        
       <table class="table table-bordered table-striped dt-responsive tablas" width="100%">
         
        <thead>
         
         <tr>
           
           <th style="width:10px">#</th>
           <th>Subdominio</th>
           <th>Base de Datos</th>
           <th>Host</th>
           <th>Usuario</th>
           <th>Celular</th>
           <th>Estado</th>
           <th>Acciones</th>

         </tr> 

        </thead>

        <tbody>

        <?php

        $item = null;
        $valor = null;

        $tenants = ControladorTenants::ctrMostrarTenants($item, $valor);

        foreach ($tenants as $key => $value) {
          
          echo '<tr>
                  <td>'.($key+1).'</td>
                  <td><strong>'.$value["subdominio"].'</strong>.kontrolpos.com</td>
                  <td>'.$value["db_name"].'</td>
                  <td>'.$value["db_host"].'</td>
                  <td>'.$value["db_user"].'</td>
                  <td>'.(!empty($value["celular"]) ? $value["celular"] : '-').'</td>';

                  if($value["estado"] == "activo"){
                    echo '<td><span class="label label-success">Activo</span></td>';
                  } else {
                    echo '<td><span class="label label-danger">Suspendido</span></td>';
                  }

                  echo '<td>
                    <div class="btn-group">
                        
                      <button class="btn btn-warning btnEditarTenant" idTenant="'.$value["id"].'" data-toggle="modal" data-target="#modalEditarTenant"><i class="fa fa-pencil"></i></button>

                      <button class="btn btn-danger btnEliminarTenant" idTenant="'.$value["id"].'"><i class="fa fa-times"></i></button>

                    </div>  
                  </td>
                </tr>';
        }

        ?>

        </tbody>

       </table>

      </div>

    </div>

  </section>

</div>

<!--=====================================
MODAL AGREGAR INQUILINO
======================================-->
<div id="modalAgregarTenant" class="modal fade" role="dialog">
  
  <div class="modal-dialog">

    <div class="modal-content">

      <form role="form" method="post">
        
        <!-- Token CSRF -->
        <?php CSRF::insertToken(); ?>

        <!--=====================================
        CABEZA DEL MODAL
        ======================================-->
        <div class="modal-header" style="background:#3c8dbc; color:white">

          <button type="button" class="close" data-dismiss="modal">&times;</button>

          <h4 class="modal-title">Agregar Inquilino</h4>

        </div>

        <!--=====================================
        CUERPO DEL MODAL
        ======================================-->
        <div class="modal-body">

          <div class="box-body">

            <!-- ENTRADA PARA EL SUBDOMINIO -->
            <div class="form-group">
              <label for="nuevoSubdominio">Subdominio (Solo letras, números y guiones)</label>
              <div class="input-group">
                <span class="input-group-addon"><i class="fa fa-globe"></i></span> 
                <input type="text" class="form-control input-lg" id="nuevoSubdominio" name="nuevoSubdominio" placeholder="ej: hotel" required style="text-transform: lowercase;">
                <span class="input-group-addon">.kontrolpos.com</span>
              </div>
            </div>

            <!-- ENTRADA PARA EL NOMBRE DE BASE DE DATOS -->
            <div class="form-group">
              <label for="nuevaDbName">Nombre de la Base de Datos</label>
              <div class="input-group">
                <span class="input-group-addon"><i class="fa fa-database"></i></span> 
                <input type="text" class="form-control input-lg" name="nuevaDbName" placeholder="ej: u933614678_hotel" required>
              </div>
            </div>

            <!-- ENTRADA PARA EL HOST DE BD -->
            <div class="form-group">
              <label for="nuevoDbHost">Servidor / Host (Por defecto 127.0.0.1)</label>
              <div class="input-group">
                <span class="input-group-addon"><i class="fa fa-server"></i></span> 
                <input type="text" class="form-control input-lg" name="nuevoDbHost" placeholder="127.0.0.1" value="127.0.0.1">
              </div>
            </div>

            <!-- ENTRADA PARA EL USUARIO BD -->
            <div class="form-group">
              <label for="nuevoDbUser">Usuario de MySQL</label>
              <div class="input-group">
                <span class="input-group-addon"><i class="fa fa-user"></i></span> 
                <input type="text" class="form-control input-lg" name="nuevoDbUser" placeholder="ej: u933614678_root" required>
              </div>
            </div>

            <!-- ENTRADA PARA LA CONTRASEÑA BD -->
            <div class="form-group">
              <label for="nuevoDbPass">Contraseña de MySQL</label>
              <div class="input-group">
                <span class="input-group-addon"><i class="fa fa-key"></i></span> 
                <input type="password" class="form-control input-lg" name="nuevoDbPass" placeholder="Contraseña de BD">
              </div>
            </div>

            <!-- ENTRADA PARA EL CELULAR -->
            <div class="form-group">
              <label for="nuevoCelular">Celular</label>
              <div class="input-group">
                <span class="input-group-addon"><i class="fa fa-phone"></i></span> 
                <input type="text" class="form-control input-lg" name="nuevoCelular" id="nuevoCelular" placeholder="ej: +57 300 123 4567">
              </div>
            </div>

            <!-- ENTRADA PARA EL ESTADO -->
            <div class="form-group">
              <label for="nuevoEstado">Estado de Cuenta</label>
              <div class="input-group">
                <span class="input-group-addon"><i class="fa fa-toggle-on"></i></span> 
                <select class="form-control input-lg" name="nuevoEstado" required>
                  <option value="activo">Activo</option>
                  <option value="suspendido">Suspendido</option>
                </select>
              </div>
            </div>

          </div>

        </div>

        <!--=====================================
        PIE DEL MODAL
        ======================================-->
        <div class="modal-footer">

          <button type="button" class="btn btn-default pull-left" data-dismiss="modal">Salir</button>

          <button type="submit" class="btn btn-primary">Guardar Inquilino</button>

        </div>

        <?php
          $crearTenant = new ControladorTenants();
          $crearTenant->ctrCrearTenant();
        ?>

      </form>

    </div>

  </div>

</div>

<!--=====================================
MODAL EDITAR INQUILINO
======================================-->
<div id="modalEditarTenant" class="modal fade" role="dialog">
  
  <div class="modal-dialog">

    <div class="modal-content">

      <form role="form" method="post">
        
        <!-- Token CSRF -->
        <?php CSRF::insertToken(); ?>

        <!--=====================================
        CABEZA DEL MODAL
        ======================================-->
        <div class="modal-header" style="background:#f39c12; color:white">

          <button type="button" class="close" data-dismiss="modal">&times;</button>

          <h4 class="modal-title">Editar Inquilino</h4>

        </div>

        <!--=====================================
        CUERPO DEL MODAL
        ======================================-->
        <div class="modal-body">

          <div class="box-body">

            <input type="hidden" id="idTenant" name="idTenant">

            <!-- ENTRADA PARA EL SUBDOMINIO -->
            <div class="form-group">
              <label for="editarSubdominio">Subdominio</label>
              <div class="input-group">
                <span class="input-group-addon"><i class="fa fa-globe"></i></span> 
                <input type="text" class="form-control input-lg" id="editarSubdominio" name="editarSubdominio" placeholder="ej: hotel" required style="text-transform: lowercase;">
                <span class="input-group-addon">.kontrolpos.com</span>
              </div>
            </div>

            <!-- ENTRADA PARA EL NOMBRE DE BASE DE DATOS -->
            <div class="form-group">
              <label for="editarDbName">Nombre de la Base de Datos</label>
              <div class="input-group">
                <span class="input-group-addon"><i class="fa fa-database"></i></span> 
                <input type="text" class="form-control input-lg" id="editarDbName" name="editarDbName" placeholder="ej: u933614678_hotel" required>
              </div>
            </div>

            <!-- ENTRADA PARA EL HOST DE BD -->
            <div class="form-group">
              <label for="editarDbHost">Servidor / Host</label>
              <div class="input-group">
                <span class="input-group-addon"><i class="fa fa-server"></i></span> 
                <input type="text" class="form-control input-lg" id="editarDbHost" name="editarDbHost" placeholder="127.0.0.1" required>
              </div>
            </div>

            <!-- ENTRADA PARA EL USUARIO BD -->
            <div class="form-group">
              <label for="editarDbUser">Usuario de MySQL</label>
              <div class="input-group">
                <span class="input-group-addon"><i class="fa fa-user"></i></span> 
                <input type="text" class="form-control input-lg" id="editarDbUser" name="editarDbUser" placeholder="ej: u933614678_root" required>
              </div>
            </div>

            <!-- ENTRADA PARA LA CONTRASEÑA BD -->
            <div class="form-group">
              <label for="editarDbPass">Contraseña de MySQL</label>
              <div class="input-group">
                <span class="input-group-addon"><i class="fa fa-key"></i></span> 
                <input type="password" class="form-control input-lg" id="editarDbPass" name="editarDbPass" placeholder="Dejar en blanco si no deseas cambiarla">
              </div>
            </div>

            <!-- ENTRADA PARA EL CELULAR -->
            <div class="form-group">
              <label for="editarCelular">Celular</label>
              <div class="input-group">
                <span class="input-group-addon"><i class="fa fa-phone"></i></span> 
                <input type="text" class="form-control input-lg" id="editarCelular" name="editarCelular" placeholder="ej: +57 300 123 4567">
              </div>
            </div>

            <!-- ENTRADA PARA EL ESTADO -->
            <div class="form-group">
              <label for="editarEstado">Estado de Cuenta</label>
              <div class="input-group">
                <span class="input-group-addon"><i class="fa fa-toggle-on"></i></span> 
                <select class="form-control input-lg" id="editarEstado" name="editarEstado" required>
                  <option value="activo">Activo</option>
                  <option value="suspendido">Suspendido</option>
                </select>
              </div>
            </div>

          </div>

        </div>

        <!--=====================================
        PIE DEL MODAL
        ======================================-->
        <div class="modal-footer">

          <button type="button" class="btn btn-default pull-left" data-dismiss="modal">Salir</button>

          <button type="submit" class="btn btn-warning">Guardar Cambios</button>

        </div>

        <?php
          $editarTenant = new ControladorTenants();
          $editarTenant->ctrEditarTenant();
        ?>

      </form>

    </div>

  </div>

</div>

<?php
  $eliminarTenant = new ControladorTenants();
  $eliminarTenant->ctrEliminarTenant();
?>

<!--=====================================
CÓDIGO JAVASCRIPT / JQUERY
======================================-->
<script>
console.log("Inquilinos JS Loaded");

$(document).ready(function() {
  console.log("Inquilinos Document Ready");

  // Validar formato de subdominio al escribir (solo permitir letras, números, guiones y guión bajo)
  $(document).on("input", "#nuevoSubdominio, #editarSubdominio", function() {
    this.value = this.value.toLowerCase().replace(/[^a-z0-9_-]/g, "");
  });

  // Editar Inquilino - Cargar datos vía AJAX
  $(document).on("click", ".btnEditarTenant", function() {
    var idTenant = $(this).attr("idTenant");
    console.log("Click Editar Tenant ID:", idTenant);

    var datos = new FormData();
    datos.append("idTenant", idTenant);
    datos.append("csrf_token", $("[name='csrf_token']").val()); // Token CSRF para peticiones seguras

    $.ajax({
      url: "ajax/tenants.ajax.php",
      method: "POST",
      data: datos,
      cache: false,
      contentType: false,
      processData: false,
      dataType: "json",
      success: function(respuesta) {
        console.log("Respuesta AJAX:", respuesta);
        $("#idTenant").val(respuesta["id"]);
        $("#editarSubdominio").val(respuesta["subdominio"]);
        $("#editarDbName").val(respuesta["db_name"]);
        $("#editarDbHost").val(respuesta["db_host"]);
        $("#editarDbUser").val(respuesta["db_user"]);
        $("#editarDbPass").val(respuesta["db_pass"]);
        $("#editarCelular").val(respuesta["celular"]);
        $("#editarEstado").val(respuesta["estado"]);
      },
      error: function(xhr, status, error) {
        console.error("Error AJAX:", xhr.responseText, status, error);
      }
    });
  });

  // Eliminar Inquilino
  $(document).on("click", ".btnEliminarTenant", function() {
    var idTenant = $(this).attr("idTenant");
    console.log("Click Eliminar Tenant ID:", idTenant);

    if (typeof swal === "function") {
      swal({
        title: '¿Está seguro de borrar el inquilino?',
        text: "¡Si no lo está puede cancelar la acción!",
        type: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        cancelButtonText: 'Cancelar',
        confirmButtonText: 'Sí, borrar inquilino'
      }).then((result) => {
        if (result.value) {
          window.location = "index.php?ruta=inquilinos&idTenant=" + idTenant;
        }
      });
    } else {
      console.warn("La función swal no está disponible, usando confirm tradicional.");
      if (confirm("¿Está seguro de borrar el inquilino?")) {
        window.location = "index.php?ruta=inquilinos&idTenant=" + idTenant;
      }
    }
  });

});
</script>
