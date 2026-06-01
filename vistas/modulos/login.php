<style>
  body.login-page {
    background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%) !important;
    display: flex;
    align-items: center;
    justify-content: center;
    min-height: 100vh;
    margin: 0;
    overflow: hidden;
    font-family: 'Source Sans Pro', sans-serif;
  }

  /* Eliminamos el overlay oscuro */
  body.login-page::before {
    display: none;
  }

  .login-box {
    position: relative;
    z-index: 1;
    width: 420px;
    margin: 0;
    background: #ffffff;
    border-radius: 24px;
    box-shadow: 0 20px 60px rgba(0, 0, 0, 0.08);
    padding: 40px;
    color: #333;
  }

  .login-logo {
    margin-bottom: 30px;
    text-align: center;
  }

  .login-logo img {
    max-width: 200px;
  }

  .login-box-body {
    background: transparent;
    padding: 0;
    color: #333;
  }

  .login-box-msg {
    font-size: 1.3em;
    font-weight: 600;
    margin-bottom: 30px;
    color: #2c3e50;
    text-align: center;
  }

  .form-group.has-feedback .form-control {
    background: #f8f9fa;
    border: 1px solid #e9ecef;
    border-radius: 12px;
    color: #495057;
    height: 50px;
    padding-left: 15px;
    font-size: 15px;
    transition: all 0.3s ease;
    box-shadow: none;
  }

  .form-group.has-feedback .form-control:focus {
    background: #fff;
    border-color: #007bff;
    box-shadow: 0 0 0 4px rgba(0, 123, 255, 0.1);
  }

  .form-group.has-feedback .form-control::placeholder {
    color: #adb5bd;
  }

  .form-control-feedback {
    color: #6c757d !important;
    line-height: 50px;
    font-size: 18px;
    margin-right: 5px;
  }

  .btn-primary {
    background: #007bff;
    border: none;
    border-radius: 12px;
    height: 50px;
    font-size: 16px;
    font-weight: 600;
    margin-top: 10px;
    transition: all 0.3s ease;
    box-shadow: 0 8px 20px rgba(0, 123, 255, 0.15);
  }

  .btn-primary:hover {
    background: #0069d9;
    transform: translateY(-2px);
    box-shadow: 0 12px 25px rgba(0, 123, 255, 0.25);
  }

  .login-box a {
    color: #6c757d;
    transition: color 0.3s ease;
    font-size: 0.95em;
    font-weight: 400;
  }

  .login-box a:hover {
    color: #007bff;
    text-decoration: none;
  }

  .register-link {
    display: block;
    margin-top: 15px;
    padding-top: 15px;
    border-top: 1px solid #eee;
  }

  /* Animación de entrada */
  @keyframes fadeInScale {
    from {
      opacity: 0;
      transform: scale(0.95);
    }
    to {
      opacity: 1;
      transform: scale(1);
    }
  }

  .login-box {
    animation: fadeInScale 0.6s ease-out;
  }

  /* Ajustes para móviles */
  @media (max-width: 480px) {
    .login-box {
      width: 90%;
      padding: 30px 20px;
    }
  }
</style>

<div class="login-box">

  <div class="login-logo">
    <img src="vistas/img/plantilla/logo-ppal.png" class="img-responsive">
  </div>

  <div class="login-box-body">

    <p class="login-box-msg">Acceso al Sistema</p>

    <?php
    if (isset($_GET['timeout']) && $_GET['timeout'] == '1') {
      echo '<div class="alert alert-warning alert-dismissible" style="border-radius:12px; background-color:#fff3cd; color:#856404; border:1px solid #ffeeba;">
                <button type="button" class="close" data-dismiss="alert">&times;</button>
                <i class="fa fa-clock-o"></i> Sesión expirada.
              </div>';
    }
    ?>

    <form method="post">

      <?php CSRF::insertToken(); ?>

      <div class="form-group has-feedback">
        <input type="text" class="form-control" placeholder="Usuario" name="ingUsuario" required>
        <span class="glyphicon glyphicon-user form-control-feedback"></span>
      </div>

      <div class="form-group has-feedback" style="margin-bottom: 25px;">
        <input type="password" class="form-control" placeholder="Contraseña" name="ingPassword" required>
        <span class="glyphicon glyphicon-lock form-control-feedback"></span>
      </div>

      <div class="row">
        <div class="col-xs-12">
          <button type="submit" class="btn btn-primary btn-block">Entrar</button>
        </div>
      </div>

      <?php
      $login = new ControladorUsuarios();
      $login->ctrIngresoUsuario();
      ?>

    </form>

    <div class="text-center" style="margin-top: 25px;">
      <a href="#" data-toggle="modal" data-target="#modalRecuperarPassword">¿Olvidaste tu contraseña?</a>
      <div class="register-link">
        <a href="#" data-toggle="modal" data-target="#modalRegistro">¿No tienes cuenta? <strong style="color:#007bff">Regístrate aquí</strong></a>
      </div>
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