<style>
  @media (max-width: 767px) {
    /* Apilar elementos de la cabecera de la caja */
    .box-header {
      display: flex !important;
      flex-direction: column !important;
      gap: 12px !important;
      align-items: stretch !important;
    }

    .box-header::before,
    .box-header::after {
      display: none !important;
    }
    
    .box-header > .btn {
      width: 100% !important;
      text-align: center !important;
    }
    
    /* Mantener etiqueta y filtro de Perfil en la misma línea */
    .box-header .pull-right {
      float: none !important;
      width: 100% !important;
      display: flex !important;
      flex-direction: row !important;
      flex-wrap: nowrap !important;
      align-items: center !important;
      margin: 0 !important;
      gap: 10px !important;
    }

    .box-header .pull-right > span {
      white-space: nowrap !important;
    }
    
    .box-header .pull-right .input-group {
      width: 100% !important;
    }

    /* Forzar al contenedor de Select2 a ocupar el 100% del ancho del input-group */
    .box-header .pull-right .input-group .select2-container {
      width: 100% !important;
    }
  }
</style>

<div class="content-wrapper">
  <section class="content-header">

    <h1>
      Administrar usuarios
      <small>Control de personal</small>
    </h1>

    <ol class="breadcrumb">
      <li><a href="inicio"><i class="fa fa-dashboard"></i> Inicio</a></li>
      <li class="active">Usuarios</li>
    </ol>

  </section>

  <section class="content">

    <div class="box">

      <div class="box-header with-border">
        <?php if (puedeAccion('usuarios', 'crear')): ?>
          <button class="btn btn-primary" data-toggle="modal" data-target="#modalAgregarUsuario">
            <i class="fa fa-plus"></i> Agregar usuario
          </button>
        <?php else: ?>
          <button class="btn btn-primary" disabled style="opacity: 0.5; cursor: not-allowed;" title="No tiene permisos para crear usuarios">
            <i class="fa fa-plus"></i> Agregar usuario
          </button>
        <?php endif; ?>

        <!-- Filtro por Perfil Estandarizado -->
        <div class="pull-right" style="display: flex; align-items: center; gap: 10px; flex-wrap: wrap;">
          <span><b>Perfil:</b></span>
          <div class="input-group" style="width: 200px;">
            <span class="input-group-addon" style="background: #fcfcfc; border-color: #d2d6de;">
              <i class="fa fa-search text-primary"></i>
            </span>
            <select class="form-control select2" id="seleccionarPerfilFiltro" style="width: 100%;">
              <option value="">Mostrar Todos</option>
              <?php foreach (ModeloPerfiles::mdlObtenerPerfiles() as $p): ?>
                <option value="<?php echo htmlspecialchars($p['nombre']); ?>">
                  <?php echo htmlspecialchars($p['nombre']); ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>
        </div>
      </div>


      <div class="box-body">

        <!-- Variable oculta para que JS sepa si el usuario actual puede editar la columna estado -->
        <input type="hidden" id="puedeEditarUsuarios"
          value="<?php echo puedeAccion('usuarios', 'editar') ? '1' : '0'; ?>">

        <!-- Variable oculta para saber si las sucursales están activas -->
        <?php
        $configuracionGlobal = ControladorConfiguracion::ctrObtenerConfiguracion();
        $sucursalesActivas = !isset($configuracionGlobal["activar_sucursales"]) || $configuracionGlobal["activar_sucursales"] == 1;
        ?>
        <input type="hidden" id="activarSucursales" value="<?php echo $sucursalesActivas ? '1' : '0'; ?>">

        <div class="tabla-usuarios tablaUsuarios table-responsive">
          <table id="tablaListaUsuarios" class="table table-bordered table-striped tablaUsuariosListado display nowrap"
            style="width: 100%">

            <thead>
              <tr>
                <th>Usuario</th>
                <th>Nombre</th>
                <th>Email</th>
                <th>Foto</th>
                <th>Perfil</th>
                <th>Sucursal</th>
                <th>Estado</th>
                <th>Ultimo login</th>
                <th>Acciones</th>
                <th>ID</th>
              </tr>
            </thead>

            <tbody>

              <!-- Los datos se cargan dinámicamente mediante DataTables Server-Side -->

            </tbody>

          </table>

        </div><!-- /.tabla-usuarios -->

      </div>

    </div>

  </section>

</div>



<!-- Modal para ampliar/editar imagen de usuario -->
<div class="modal fade" id="modalAmpliarImagenUsuario" tabindex="-1" role="dialog">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
        <h4 class="modal-title">Foto de Usuario</h4>
      </div>
      <div class="modal-body text-center">
        <img id="imagenUsuarioAmpliada" src="" class="img-responsive"
          style="max-width: 100%; margin: 0 auto; margin-bottom: 20px;">
        <hr>
        <div class="form-group">
          <label>Cambiar Foto del Usuario</label>
          <input type="file" class="form-control nuevaImagenUsuario" accept="image/*">
          <p class="help-block">Peso máximo de la imagen 2MB</p>
        </div>
        <input type="hidden" id="idUsuarioImagen">
        <input type="hidden" id="usuarioNombre">
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">Cancelar</button>
        <button type="button" class="btn btn-primary btnGuardarImagenUsuario">Guardar Imagen</button>
      </div>
    </div>
  </div>
</div>


<!--=====================================
MODAL AGREGAR USUARIO
======================================-->

<!-- Modal -->
<div id="modalAgregarUsuario" class="modal fade" role="dialog">

  <div class="modal-dialog">

    <div class="modal-content">

      <form role="form" method="post" enctype="multipart/form-data">

        <?php CSRF::insertToken(); // Token CSRF ?>

        <!--=====================================
        CABEZA DEL MODAL
        ======================================-->

        <div class="modal-header" style="background:#3c8dbc; color: white">

          <button type="button" class="close" data-dismiss="modal">&times;</button>
          <h4 class="modal-title">Agregar usuario</h4>

        </div>

        <!--=====================================
        CUERPO DEL MODAL
        ======================================-->

        <div class="modal-body">
          <div class="box-body">

            <!-- Fila 1: Nombre y Usuario -->
            <div class="row">
              <div class="col-md-6">
                <div class="form-group">
                  <label>Nombre:</label>
                  <div class="input-group">
                    <span class="input-group-addon"><i class="fa fa-user"></i></span>
                    <input type="text" class="form-control input-lg" name="nuevoNombre" placeholder="Nombre completo"
                      required>
                  </div>
                </div>
              </div>

              <div class="col-md-6">
                <div class="form-group">
                  <label>Usuario (Login):</label>
                  <div class="input-group">
                    <span class="input-group-addon"><i class="fa fa-key"></i></span>
                    <input type="text" class="form-control input-lg" name="nuevoUsuario"
                      placeholder="Usuario (sin espacios)" id="nuevoUsuario" required>
                  </div>
                </div>
              </div>
            </div>

            <!-- Fila 2: Email y Perfil -->
            <div class="row">
              <div class="col-md-6">
                <div class="form-group">
                  <label>Correo Electrónico:</label>
                  <div class="input-group">
                    <span class="input-group-addon"><i class="fa fa-envelope"></i></span>
                    <input type="email" class="form-control input-lg" name="nuevoEmail" placeholder="correo@ejemplo.com"
                      required>
                  </div>
                </div>
              </div>

              <div class="col-md-6">
                <div class="form-group">
                  <label>Perfil de Acceso:</label>
                  <div class="input-group">
                    <span class="input-group-addon"><i class="fa fa-users"></i></span>
                    <select class="form-control input-lg" name="nuevoPerfil" required>
                      <option value="">Seleccionar perfil</option>
                      <?php foreach (ModeloPerfiles::mdlObtenerPerfiles() as $p): ?>
                        <option value="<?php echo htmlspecialchars($p['nombre']); ?>">
                          <?php echo htmlspecialchars($p['nombre']); ?>
                        </option>
                      <?php endforeach; ?>
                    </select>
                  </div>
                </div>
              </div>
            </div>

            <!-- Fila 3: Contraseña y Sucursal -->
            <div class="row">
              <div class="col-md-6">
                <div class="form-group">
                  <label>Contraseña:</label>
                  <div class="input-group">
                    <span class="input-group-addon"><i class="fa fa-lock"></i></span>
                    <input type="password" class="form-control input-lg" name="nuevoPassword"
                      placeholder="Ingresar contraseña" required>
                  </div>
                </div>
              </div>

              <div class="col-md-6">
                <div class="form-group">
                  <label>Sucursal / Bodega:</label>
                  <div class="input-group">
                    <span class="input-group-addon"><i class="fa fa-building"></i></span>
                    <select class="form-control input-lg" name="nuevoIdBodega">
                      <option value="">Seleccionar sucursal</option>
                      <?php 
                        $bodegas = ControladorBodegas::ctrMostrarBodegas(null, null);
                        foreach ($bodegas as $key => $value) {
                          if ($value["estado"] == 0) {
                            continue;
                          }
                          echo '<option value="'.$value["id"].'">'.$value["nombre"].'</option>';
                        }
                      ?>
                    </select>
                  </div>
                </div>
              </div>
            </div>

            <!-- Fila 4: Foto -->
            <div class="row">
              <div class="col-md-12">
                <div class="form-group">
                  <label>Foto de Perfil:</label>
                  <div class="panel text-center" style="border: 1px dashed #ccc; padding: 10px;">
                    <input type="file" class="nuevaFoto" name="nuevaFoto" style="margin: 0 auto;">
                    <p class="help-block">Peso máximo de la foto 2MB</p>
                    <img src="vistas/img/usuarios/default/anonymous.png" class="img-thumbnail previsualizar"
                      width="100px">
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
          <button type="submit" class="btn btn-primary">Guardar usuario</button>

        </div>

        <?php

        $crearUsuario = new ControladorUsuarios();
        $crearUsuario->ctrCrearUsuario();

        ?>

      </form>

    </div>


  </div>

</div>




<!--==========================================================================================================
MODAL EDITAR USUARIO
===========================================================================================================-->

<!-- Modal -->
<div id="modalEditarUsuario" class="modal fade" role="dialog">

  <div class="modal-dialog">

    <div class="modal-content">

      <form role="form" method="post" enctype="multipart/form-data">

        <?php CSRF::insertToken(); // Token CSRF ?>

        <!--=====================================
      CABEZA DEL MODAL
      ======================================-->

        <div class="modal-header" style="background:#3c8dbc; color: white">

          <button type="button" class="close" data-dismiss="modal">&times;</button>
          <h4 class="modal-title">Editar usuario</h4>

        </div>

        <!--=====================================
      CUERPO DEL MODAL
      ======================================-->

        <div class="modal-body">
          <div class="box-body">

            <!-- Fila 1: Nombre y Usuario -->
            <div class="row">
              <div class="col-md-6">
                <div class="form-group">
                  <label>Nombre:</label>
                  <div class="input-group">
                    <span class="input-group-addon"><i class="fa fa-user"></i></span>
                    <input type="text" class="form-control input-lg" id="editarNombre" name="editarNombre" value=""
                      placeholder="Nombre completo del usuario" required>
                  </div>
                </div>
              </div>

              <div class="col-md-6">
                <div class="form-group">
                  <label>Usuario (Login):</label>
                  <div class="input-group">
                    <span class="input-group-addon"><i class="fa fa-key"></i></span>
                    <input type="text" class="form-control input-lg" id="editarUsuario" name="editarUsuario" value=""
                      placeholder="Nombre de usuario" required>
                    <input type="hidden" name="idUsuario" id="idUsuario">
                  </div>
                </div>
              </div>
            </div>

            <!-- Fila 2: Email y Perfil -->
            <div class="row">
              <div class="col-md-6">
                <div class="form-group">
                  <label>Correo Electrónico:</label>
                  <div class="input-group">
                    <span class="input-group-addon"><i class="fa fa-envelope"></i></span>
                    <input type="email" class="form-control input-lg" id="editarEmail" name="editarEmail" value=""
                      placeholder="correo@ejemplo.com" required>
                  </div>
                </div>
              </div>

              <div class="col-md-6">
                <div class="form-group">
                  <label>Perfil de Acceso:</label>
                  <div class="input-group">
                    <span class="input-group-addon"><i class="fa fa-users"></i></span>
                    <select class="form-control input-lg" name="editarPerfil">
                      <option value="" id="editarPerfil"></option>
                      <?php foreach (ModeloPerfiles::mdlObtenerPerfiles() as $p): ?>
                        <option value="<?php echo htmlspecialchars($p['nombre']); ?>">
                          <?php echo htmlspecialchars($p['nombre']); ?>
                        </option>
                      <?php endforeach; ?>
                    </select>
                  </div>
                </div>
              </div>
            </div>
            <!-- Fila 3: Sucursal -->
            <div class="row">
              <div class="col-md-12">
                <div class="form-group">
                  <label>Sucursal / Bodega:</label>
                  <div class="input-group">
                    <span class="input-group-addon"><i class="fa fa-building"></i></span>
                    <select class="form-control input-lg" name="editarIdBodega" id="editarIdBodega">
                      <option value="">Seleccionar sucursal</option>
                      <?php 
                        $bodegas = ControladorBodegas::ctrMostrarBodegas(null, null);
                        foreach ($bodegas as $key => $value) {
                          if ($value["estado"] == 0) {
                            continue;
                          }
                          echo '<option value="'.$value["id"].'">'.$value["nombre"].'</option>';
                        }
                      ?>
                    </select>
                  </div>
                </div>
              </div>
            </div>
            <div class="row">
              <div class="col-md-12">
                <div class="form-group">
                  <label>Foto de Perfil:</label>
                  <div class="panel text-center" style="border: 1px dashed #ccc; padding: 10px;">
                    <input type="file" class="nuevaFoto" name="editarFoto" style="margin: 0 auto;">
                    <p class="help-block">Peso máximo de la foto 2MB</p>
                    <img src="vistas/img/usuarios/default/anonymous.png"
                      class="img-thumbnail previsualizar img-ampliar-usuario" width="100px" style="cursor: pointer;">
                    <input type="hidden" name="fotoActual" id="fotoActual">
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
          <button type="submit" class="btn btn-primary">Modificar usuario</button>

        </div>

        <?php

        $editarUsuario = new ControladorUsuarios();
        $editarUsuario->ctrEditarUsuario();

        ?>

      </form>

    </div>


  </div>

</div>


<!-- Modal para ampliar imagen de usuario, desde editar usuario-->
<div class="modal fade" id="modalAmpliarFotoUsuario" tabindex="-1" role="dialog">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
        <h4 class="modal-title">Foto de Usuario</h4>
      </div>
      <div class="modal-body text-center">
        <img id="fotoUsuarioAmpliada" src="" class="img-responsive" style="max-width: 100%; margin: 0 auto;">
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>
      </div>
    </div>
  </div>
</div>


<?php

$borrarUsuario = new ControladorUsuarios();
$borrarUsuario->ctrBorrarUsuario();

?>


<!-- Ampliar foto de usuario, desde el modal editar usuario -->
<script>
  $(document).on("click", ".img-ampliar-usuario", function () {
    var rutaImagen = $(this).attr("src");
    $("#fotoUsuarioAmpliada").attr("src", rutaImagen);
    $("#modalAmpliarFotoUsuario").modal("show");
  });
  // Cuando se sube una nueva foto, actualizar la imagen
  $(".nuevaFoto").change(function () {
    var imagen = this.files[0];

    if (imagen) {
      if (imagen["type"] != "image/jpeg" && imagen["type"] != "image/png") {
        $(".nuevaFoto").val("");
        swal({
          title: "Error al subir la imagen",
          text: "¡La imagen debe estar en formato JPG o PNG!",
          type: "error",
          confirmButtonText: "¡Cerrar!"
        });
      } else if (imagen["size"] > 2000000) {
        $(".nuevaFoto").val("");
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
          $(".previsualizar").attr("src", rutaImagen);
        });
      }
    }
  });
</script>



<!--=============================================
AMPLIAR Y EDITAR IMAGEN DE USUARIO DESDE LA TABLA
=============================================-->
<script>
  // Ampliar imagen de usuario al hacer clic
  $(document).on("click", ".img-usuario-clickeable", function () {
    var rutaImagen = $(this).attr("data-foto");
    var idUsuario = $(this).attr("data-idusuario");
    var usuario = $(this).attr("data-usuario");

    console.log("ID Usuario:", idUsuario);
    console.log("Usuario:", usuario);
    console.log("Ruta Imagen:", rutaImagen);

    $("#imagenUsuarioAmpliada").attr("src", rutaImagen);
    $("#idUsuarioImagen").val(idUsuario);
    $("#usuarioNombre").val(usuario);
    $(".nuevaImagenUsuario").val("");
    $("#modalAmpliarImagenUsuario").modal("show");
  });

  // Previsualizar nueva imagen cuando se selecciona
  $(".nuevaImagenUsuario").change(function () {
    var imagen = this.files[0];

    if (imagen) {
      if (imagen["type"] != "image/jpeg" && imagen["type"] != "image/png") {
        $(".nuevaImagenUsuario").val("");
        swal({
          title: "Error al subir la imagen",
          text: "¡La imagen debe estar en formato JPG o PNG!",
          type: "error",
          confirmButtonText: "¡Cerrar!"
        });
      } else if (imagen["size"] > 2000000) {
        $(".nuevaImagenUsuario").val("");
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
          $("#imagenUsuarioAmpliada").attr("src", rutaImagen);
        });
      }
    }
  });

  // Guardar la nueva imagen del usuario
  $(document).on("click", ".btnGuardarImagenUsuario", function () {

    var idUsuario = $("#idUsuarioImagen").val();
    var usuario = $("#usuarioNombre").val();
    var imagen = $(".nuevaImagenUsuario")[0].files[0];

    console.log("ID al guardar:", idUsuario);
    console.log("Usuario al guardar:", usuario);
    console.log("Imagen al guardar:", imagen);

    if (!imagen) {
      swal({
        title: "Advertencia",
        text: "No has seleccionado ninguna imagen",
        type: "warning",
        confirmButtonText: "¡Cerrar!"
      });
      return;
    }

    if (!idUsuario || !usuario) {
      swal({
        title: "Error",
        text: "No se pudo obtener el ID o nombre del usuario",
        type: "error",
        confirmButtonText: "¡Cerrar!"
      });
      return;
    }

    var datos = new FormData();
    datos.append("idUsuarioImagen", idUsuario);
    datos.append("usuarioNombre", usuario);
    datos.append("nuevaImagenUsuario", imagen);

    // Mostrar loading
    swal({
      title: 'Cargando...',
      allowOutsideClick: false,
      didOpen: () => {
        swal.showLoading()
      }
    });

    $.ajax({
      url: "ajax/usuarios.ajax.php",
      method: "POST",
      data: datos,
      cache: false,
      contentType: false,
      processData: false,
      dataType: "json",
      success: function (respuesta) {
        console.log("Respuesta del servidor:", respuesta);

        if (respuesta == "ok") {
          swal({
            type: "success",
            title: "¡La imagen ha sido actualizada correctamente!",
            showConfirmButton: true,
            confirmButtonText: "Cerrar"
          }).then(function (result) {
            if (result.value) {
              $("#modalAmpliarImagenUsuario").modal("hide");
              window.location = "usuarios";
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



<!-- La inicialización de DataTables se maneja en vistas/js/usuarios.js -->