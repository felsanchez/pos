<?php

if ($_SESSION["perfil"] != "Administrador") {

    echo '<script>

    window.location = "inicio";

  </script>';

    return;

}

$configuracion = ControladorFacturacion::ctrMostrarConfiguracion();

?>

<div class="content-wrapper">

    <section class="content-header">

        <h1>

            Configuración de Facturación Electrónica (Factus)

        </h1>

        <ol class="breadcrumb">

            <li><a href="inicio"><i class="fa fa-dashboard"></i> Inicio</a></li>

            <li class="active">Configuración Facturación</li>

        </ol>

    </section>

    <section class="content">

        <div class="row">

            <div class="col-lg-12">

                <div class="box box-primary">

                    <div class="box-header with-border">
                        <h3 class="box-title">Credenciales y Parámetros</h3>
                    </div>

                    <form role="form" method="post">

        <?php CSRF::insertToken(); ?>

                        <div class="box-body">

                            <div class="row">

                                <!--=====================================
                AMBIENTE
                ======================================-->
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Ambiente:</label>
                                        <select class="form-control" name="ambiente" required>
                                            <option value="pruebas" <?php if ($configuracion["ambiente"] == "pruebas")
                                                echo "selected"; ?>>Pruebas (Sandbox)</option>
                                            <option value="produccion" <?php if ($configuracion["ambiente"] == "produccion")
                                                echo "selected"; ?>>
                                                Producción</option>
                                        </select>
                                    </div>
                                </div>

                                <!--=====================================
                URL API
                ======================================-->
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>URL API:</label>
                                        <input type="text" class="form-control" name="api_url"
                                            value="<?php echo $configuracion["api_url"]; ?>" required>
                                    </div>
                                </div>

                            </div>

                            <hr>

                            <div class="row">

                                <!--=====================================
                TOKEN
                ======================================-->
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label>Token de Acceso (Bearer Token):</label>
                                        <textarea class="form-control" name="token" rows="3"
                                            placeholder="Ingrese el token largo proporcionado por Factus"><?php echo $configuracion["token"]; ?></textarea>
                                    </div>
                                </div>

                                <!--=====================================
                REFRESH TOKEN
                ======================================-->
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label>Refresh Token:</label>
                                        <textarea class="form-control" name="refresh_token" rows="2"
                                            placeholder="Ingrese el refresh token"><?php echo $configuracion["refresh_token"]; ?></textarea>
                                    </div>
                                </div>

                            </div>

                            <hr>

                            <div class="row">

                                <!--=====================================
                RESOLUCION
                ======================================-->
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>Número de Resolución:</label>
                                        <input type="text" class="form-control" name="resolucion"
                                            value="<?php echo $configuracion["resolucion"]; ?>"
                                            placeholder="Ej: 18760000001">
                                    </div>
                                </div>

                                <!--=====================================
                PREFIJO
                ======================================-->
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>Prefijo:</label>
                                        <input type="text" class="form-control" name="prefijo"
                                            value="<?php echo $configuracion["prefijo"]; ?>" placeholder="Ej: SETT">
                                    </div>
                                </div>

                                <!--=====================================
                CONSECUTIVO ACTUAL
                ======================================-->
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>Consecutivo Actual:</label>
                                        <input type="number" class="form-control" name="consecutivo_actual"
                                            value="<?php echo $configuracion["consecutivo_actual"]; ?>"
                                            placeholder="Ej: 1">
                                    </div>
                                </div>

                            </div>

                            <div class="row">

                                <!--=====================================
                FECHA DESDE
                ======================================-->
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>Fecha Desde:</label>
                                        <input type="date" class="form-control" name="fecha_desde"
                                            value="<?php echo $configuracion["fecha_desde"]; ?>">
                                    </div>
                                </div>

                                <!--=====================================
                FECHA HASTA
                ======================================-->
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>Fecha Hasta:</label>
                                        <input type="date" class="form-control" name="fecha_hasta"
                                            value="<?php echo $configuracion["fecha_hasta"]; ?>">
                                    </div>
                                </div>

                                <!--=====================================
                CLAVE TECNICA
                ======================================-->
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>Clave Técnica:</label>
                                        <input type="text" class="form-control" name="clave_tecnica"
                                            value="<?php echo $configuracion["clave_tecnica"]; ?>">
                                    </div>
                                </div>

                            </div>

                            <div class="row">
                                <!--=====================================
                EMAIL CONTACTO
                ======================================-->
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Email de Contacto (Para notificaciones):</label>
                                        <input type="email" class="form-control" name="email_contacto"
                                            value="<?php echo $configuracion["email_contacto"]; ?>">
                                    </div>
                                </div>
                            </div>

                        </div>

                        <div class="box-footer">
                            <button type="submit" class="btn btn-primary pull-right" name="guardarConfiguracion">Guardar
                                Configuración</button>
                        </div>

                        <?php

                        $guardarConfiguracion = new ControladorFacturacion();
                        $guardarConfiguracion->ctrGuardarConfiguracion();

                        ?>

                    </form>

                </div>

            </div>

        </div>

    </section>

</div>