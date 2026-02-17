<?php

class ControladorFacturacion
{

    /*=============================================
    MOSTRAR CONFIGURACION
    =============================================*/

    static public function ctrMostrarConfiguracion()
    {

        $tabla = "facturacion_configuracion";

        $respuesta = ModeloFacturacion::mdlMostrarConfiguracion($tabla);

        return $respuesta;

    }

    /*=============================================
    GUARDAR CONFIGURACION
    =============================================*/

    static public function ctrGuardarConfiguracion()
    {

        if (isset($_POST["guardarConfiguracion"])) {

            $tabla = "facturacion_configuracion";

            $datos = array(
                "token" => $_POST["token"],
                "refresh_token" => $_POST["refresh_token"],
                "resolucion" => $_POST["resolucion"],
                "prefijo" => $_POST["prefijo"],
                "consecutivo_actual" => $_POST["consecutivo_actual"],
                "fecha_desde" => $_POST["fecha_desde"],
                "fecha_hasta" => $_POST["fecha_hasta"],
                "clave_tecnica" => $_POST["clave_tecnica"],
                "ambiente" => $_POST["ambiente"],
                "api_url" => $_POST["api_url"],
                "email_contacto" => $_POST["email_contacto"]
            );

            $respuesta = ModeloFacturacion::mdlGuardarConfiguracion($tabla, $datos);

            if ($respuesta == "ok") {

                echo '<script>

					swal({
						  type: "success",
						  title: "La configuración ha sido guardada correctamente",
						  showConfirmButton: true,
						  confirmButtonText: "Cerrar"
						  }).then(function(result){
									if (result.value) {

									window.location = "configuracion-facturacion";

									}
								})

					</script>';

            } else {

                echo '<script>

					swal({
						  type: "error",
						  title: "¡Error al guardar la configuración!",
						  showConfirmButton: true,
						  confirmButtonText: "Cerrar"
						  }).then(function(result){
									if (result.value) {

									window.location = "configuracion-facturacion";

									}
								})

					</script>';

            }

        }

    }

}
