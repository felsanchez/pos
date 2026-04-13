<style>
  /* Espaciado automático para el botón de expansión en modo inline */
  .tablaUsuarios.collapsed tbody td:first-child {
    padding-left: 35px !important;
    position: relative;
    cursor: pointer;
  }

  /* Posicionamiento del botón + (estilo variantes) */
    background-color: #3c8dbc !important; /* Azul al estar contraído (+) */
    box-shadow: none !important;
  }

  /* Color rojo cuando está expandido (-) */
  .tablaUsuarios.collapsed tbody tr.parent td:first-child::before {
    background-color: #dd4b39 !important;
  }

  /* Resize action buttons on mobile */
  @media (max-width: 767px) {
    .tablaUsuarios .btn-group .btn {
      padding: 1px 5px;
      font-size: 12px;
      line-height: 1.5;
      border-radius: 3px;
    }
  }

  /* Limitar altura del modal de ampliar imagen y agregar scroll */
  #modalAmpliarImagenUsuario .modal-body {
    max-height: 70vh;
    overflow-y: auto;
  }


  /* Asegurar que el footer del modal esté siempre visible */
  #modalAmpliarImagenUsuario .modal-footer {
    position: relative;
    z-index: 10;
  }
</style>

<div class="content-wrapper">
  <section class="content-header">

    <h1>
      Administrar usuarios
    </h1>

    <ol class="breadcrumb">
      <li><a href="inicio"><i class="fa fa-dashboard"></i> Inicio</a></li>
      <li class="active">Administrar usuarios</li>
    </ol>

  </section>

  <section class="content">

    <div class="box">

      <div class="box-header with-border">
        <?php if (puedeAccion('usuarios', 'crear')): ?>
          <button class="btn btn-primary" data-toggle="modal" data-target="#modalAgregarUsuario">
            <i class="fa fa-plus"></i> Agregar usuario
          </button>
        <?php endif; ?>

        <!-- Filtro por Perfil Estandarizado -->
        <div class="pull-right" style="display: flex; align-items: center; gap: 10px; flex-wrap: wrap;">
          <span class="hidden-xs"><b>Filtrar por Perfil:</b></span>
          <div class="input-group" style="width: 200px;">
            <span class="input-group-addon" style="background: #fcfcfc; border-color: #d2d6de;">
              <i class="fa fa-search text-primary"></i>
            </span>
            <select class="form-control select2" id="seleccionarPerfilFiltro" style="width: 100%;">
              <option value="">Seleccionar perfil...</option>
              <?php foreach (ModeloPerfiles::mdlObtenerPerfiles() as $p): ?>
                <option value="<?php echo htmlspecialchars($p['nombre']); ?>">
                  <?php echo htmlspecialchars($p['nombre']); ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>
        </div>
      </div>


      <div class="box-body table-responsive">

        <!-- Variable oculta para que JS sepa si el usuario actual puede editar la columna estado -->
        <input type="hidden" id="puedeEditarUsuarios"
          value="<?php echo puedeAccion('usuarios', 'editar') ? '1' : '0'; ?>">

        <table class="table table-bordered table-striped dt-responsive tablaUsuarios" style="width: 100%">

          <thead>
            <tr>
              <th>Usuario</th>
              <th>Nombre</th>
              <th>Email</th>
              <th>Foto</th>
              <th>Perfil</th>
              <th>Estado</th>
              <th>Ultimo login</th>
              <th>Acciones</th>
            </tr>
          </thead>

          <tbody>

            <?php

            $item = null;
            $valor = null;

            $usuarios = ControladorUsuarios::ctrMostrarUsuarios($item, $valor);

            // Obtener el usuario logueado actualmente
            $usuarioLogueado = isset($_SESSION["usuario"]) ? $_SESSION["usuario"] : '';

            $i = 1;

            foreach ($usuarios as $key => $value) {

              // Saltar el usuario que está logueado actualmente
              if ($value["usuario"] == $usuarioLogueado) {
                continue;
              }

              echo '<tr>
                        <td>' . e($value["usuario"]) . '</td>
                        <td>' . e($value["nombre"]) . '</td>

                        <td>' . e($value["email"]) . '</td>';


              /*if($value["foto"] != ""){ 
                echo '<td><img src="'.$value["foto"].'" class="img-thumbnail" width="40px"></td>';
              }
              else{
                echo '<td><img src="vistas/img/usuarios/default/anonymous.png" class="img-thumbnail" width="40px"></td>';
              } */

              if ($value["foto"] != "") {
                echo '<td><img src="' . $value["foto"] . '" class="img-thumbnail img-usuario-clickeable" width="40px" style="cursor: pointer;" data-foto="' . $value["foto"] . '" data-idusuario="' . $value["id"] . '" data-usuario="' . $value["usuario"] . '"></td>';
              } else {
                echo '<td><img src="vistas/img/usuarios/default/anonymous.png" class="img-thumbnail img-usuario-clickeable" width="40px" style="cursor: pointer;" data-foto="vistas/img/usuarios/default/anonymous.png" data-idusuario="' . $value["id"] . '" data-usuario="' . $value["usuario"] . '"></td>';
              }


              echo '<td>' . e($value["perfil"]) . '</td>';

              if (puedeAccion('usuarios', 'editar')) {
                if ($value["estado"] != 0) {

                  echo '<td><button class="btn btn-success btn-xs btnActivar" idUsuario="' . $value["id"] . '" estadoUsuario="0">Activado</button></td>';
                } else {

                  echo '<td><button class="btn btn-danger btn-xs btnActivar" idUsuario="' . $value["id"] . '" estadoUsuario="1">Desactivado</button></td>';
                }
              } else {
                if ($value["estado"] != 0) {
                  echo '<td><button class="btn btn-success btn-xs">Activado</button></td>';
                } else {
                  echo '<td><button class="btn btn-danger btn-xs">Desactivado</button></td>';
                }
              }


              echo '<td>' . $value["ultimo_login"] . '</td>

                    <td>
                      <div class="btn-group">';

              if (puedeAccion('usuarios', 'editar')) {
                echo '<button class="btn btn-warning btnEditarUsuario" idUsuario="' . $value["id"] . '"><i class="fa fa-pencil"></i></button>';
              }

              if (puedeAccion('usuarios', 'eliminar')) {
                echo '<button class="btn btn-danger btnEliminarUsuario" idUsuario="' . $value["id"] . '" fotoUsuario="' . $value["foto"] . '" usuario="' . $value["usuario"] . '"><i class="fa fa-times"></i></button>';
              }

              echo '</div>
                    </td>

                  </tr>';
              $i++;
            }

            ?>


          </tbody>

        </table>

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

            <!-- Fila 3: Contraseña -->
            <div class="row">
              <div class="col-md-12">
                <div class="form-group">
                  <label>Contraseña:</label>
                  <div class="input-group">
                    <span class="input-group-addon"><i class="fa fa-lock"></i></span>
                    <input type="password" class="form-control input-lg" name="nuevoPassword"
                      placeholder="Ingresar contraseña segura" required>
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
                      placeholder="Nombre de usuario" readonly>
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



            <!-- Fila 4: Foto -->
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