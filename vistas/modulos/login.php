<div id="back"></div>

<div class="login-box">

  <div class="login-logo">

    <!--PC<img src="vistas/img/plantilla/logo-blanco-bloque.png" class="img-responsive" style="padding: 30px 100px 0px 100px">-->

  </div>

  <!-- /.login-logo -->

  <div class="login-box-body">



    <p class="login-box-msg">Ingresar al sistema</p>

    <?php
    // Mostrar mensaje si la sesión expiró por inactividad
    if (isset($_GET['timeout']) && $_GET['timeout'] == '1') {
      echo '<div class="alert alert-warning alert-dismissible" style="margin: 10px 0;">
                <button type="button" class="close" data-dismiss="alert">&times;</button>
                <i class="fa fa-clock-o"></i> Tu sesión expiró por inactividad. Por favor, inicia sesión nuevamente.
              </div>';
    }
    ?>



    <form method="post">

      <?php CSRF::insertToken(); // Token CSRF ?>

      <div class="form-group has-feedback">

        <input type="text" class="form-control" placeholder="Usuario" name="ingUsuario" required>

        <span class="glyphicon glyphicon-user form-control-feedback"></span>

      </div>



      <div class="form-group has-feedback">

        <input type="password" class="form-control" placeholder="Contraseña" name="ingPassword" required>

        <span class="glyphicon glyphicon-lock form-control-feedback"></span>

      </div>


      <div class="row">

        <div class="col-xs-4">

          <button type="submit" class="btn btn-primary btn-block btn-flat">Ingresar</button>

        </div>


      </div>

      <?php

      $login = new ControladorUsuarios();
      $login->ctrIngresoUsuario();
      ?>

    </form>

    <br>

    <div class="text-center">
      <a href="#" data-toggle="modal" data-target="#modalRecuperarPassword">¿Olvidaste tu contraseña?</a><br>
      <a href="#" data-toggle="modal" data-target="#modalRegistro">¿No tienes cuenta? Regístrate aquí</a>

    </div>

  </div>

</div>

<!--=====================================
MODAL REGISTRO
======================================-->

<div id="modalRegistro" class="modal fade" role="dialog">

  <div class="modal-dialog">

    <div class="modal-content">

      <form role="form" method="post">

        <?php CSRF::insertToken(); // Token CSRF ?>

        <!--=====================================
        CABEZA DEL MODAL
        ======================================-->

        <div class="modal-header" style="background:#3c8dbc; color:white">

          <button type="button" class="close" data-dismiss="modal">&times;</button>

          <h4 class="modal-title">Registrarse</h4>

        </div>

        <!--=====================================
        CUERPO DEL MODAL
        ======================================-->

        <div class="modal-body">

          <div class="box-body">

            <!-- ENTRADA PARA EL NOMBRE -->

            <div class="form-group">

              <div class="input-group">

                <span class="input-group-addon"><i class="fa fa-user"></i></span>

                <input type="text" class="form-control input-lg" name="registroNombre"
                  placeholder="Ingresar nombre completo" required>

              </div>

            </div>

            <!-- ENTRADA PARA EL EMAIL -->

            <div class="form-group">

              <div class="input-group">

                <span class="input-group-addon"><i class="fa fa-envelope"></i></span>

                <input type="email" class="form-control input-lg" name="registroEmail"
                  placeholder="Ingresar correo electrónico" required>

              </div>

            </div>

            <!-- ENTRADA PARA EL USUARIO -->

            <div class="form-group">

              <div class="input-group">

                <span class="input-group-addon"><i class="fa fa-key"></i></span>

                <input type="text" class="form-control input-lg" name="registroUsuario" placeholder="Ingresar usuario"
                  required>

              </div>

            </div>

            <!-- ENTRADA PARA LA CONTRASEÑA -->

            <div class="form-group">

              <div class="input-group">

                <span class="input-group-addon"><i class="fa fa-lock"></i></span>

                <input type="password" class="form-control input-lg" name="registroPassword"
                  placeholder="Ingresar contraseña" required>

              </div>

            </div>

          </div>

        </div>

        <!--=====================================
        PIE DEL MODAL
        ======================================-->

        <div class="modal-footer">

          <button type="button" class="btn btn-default pull-left" data-dismiss="modal">Cancelar</button>

          <button type="submit" class="btn btn-primary">Registrarse</button>

        </div>

        <?php

        $registro = new ControladorUsuarios();
        $registro->ctrRegistroUsuario();

        ?>

      </form>

    </div>

  </div>

</div>

<!--=====================================
MODAL RECUPERAR CONTRASEÑA
======================================-->

<div id="modalRecuperarPassword" class="modal fade" role="dialog">

  <div class="modal-dialog">

    <div class="modal-content">

      <form role="form" method="post">

        <?php CSRF::insertToken(); // Token CSRF ?>

        <!--=====================================
        CABEZA DEL MODAL
        ======================================-->

        <div class="modal-header" style="background:#3c8dbc; color:white">

          <button type="button" class="close" data-dismiss="modal">&times;</button>

          <h4 class="modal-title">Recuperar Contraseña</h4>

        </div>

        <!--=====================================
        CUERPO DEL MODAL
        ======================================-->

        <div class="modal-body">

          <div class="box-body">

            <p>Ingresa tu correo electrónico y te enviaremos una nueva contraseña.</p>

            <!-- ENTRADA PARA EL EMAIL -->

            <div class="form-group">

              <div class="input-group">

                <span class="input-group-addon"><i class="fa fa-envelope"></i></span>

                <input type="email" class="form-control input-lg" name="resetEmail"
                  placeholder="Ingresar correo electrónico" required>

              </div>

            </div>

          </div>

        </div>

        <!--=====================================
        PIE DEL MODAL
        ======================================-->

        <div class="modal-footer">

          <button type="button" class="btn btn-default pull-left" data-dismiss="modal">Cancelar</button>

          <button type="submit" class="btn btn-primary">Enviar Contraseña</button>

        </div>

        <?php

        $reset = new ControladorUsuarios();
        $reset->ctrRestablecerPassword();

        ?>

      </form>

    </div>

  </div>

</div>