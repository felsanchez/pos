<?php
// Incluir gestión segura de sesiones
require_once "modelos/session-manager.php";
SessionManager::startSecure();

// Incluir clase CSRF para protección contra ataques CSRF
require_once "modelos/csrf.php";
CSRF::generateToken(); // Generar token al cargar la página

// Incluir sanitizador XSS
require_once "modelos/sanitizer.php";
?>

<!DOCTYPE html>
<html>

<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <title>Grupo Fej Technologies</title>
  <!-- Tell the browser to be responsive to screen width -->
  <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">

  <?php echo CSRF::getMetaTag(); // Token CSRF para JavaScript ?>

  <!--PC-->
  <link rel="icon" href="vistas/img/plantilla/icono-negro.png">

  <!--=====================================
  PLUGINS DE CSS
  ======================================-->

  <!-- responsive- para que salga el scroll solo en la tabla productos -->
  <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">

  <!-- Bootstrap 3.3.7 -->
  <link rel="stylesheet" href="vistas/bower_components/bootstrap/dist/css/bootstrap.min.css">

  <!-- Font Awesome -->
  <link rel="stylesheet" href="vistas/bower_components/font-awesome/css/font-awesome.min.css">

  <!-- Ionicons -->
  <link rel="stylesheet" href="vistas/bower_components/Ionicons/css/ionicons.min.css">

  <!-- Theme style -->
  <link rel="stylesheet" href="vistas/dist/css/AdminLTE.css">

  <!-- AdminLTE Skins-->
  <link rel="stylesheet" href="vistas/dist/css/skins/_all-skins.min.css">

  <!-- Google Font -->
  <link rel="stylesheet"
    href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,600,700,300italic,400italic,600italic">

  <!-- DataTables -->
  <link rel="stylesheet" href="vistas/bower_components/datatables.net-bs/css/dataTables.bootstrap.min.css">
  <!-- DataTables responsive-->
  <link rel="stylesheet" href="vistas/bower_components/datatables.net-bs/css/responsive.bootstrap.min.css">

  <!-- DataTables Buttons -->
  <link rel="stylesheet" href="https://cdn.datatables.net/buttons/1.5.2/css/buttons.bootstrap.min.css">

  <!-- iCheck for checkboxes and radio inputs -->
  <link rel="stylesheet" href="vistas/plugins/iCheck/all.css">

  <!--Daterange Picker -->
  <link rel="stylesheet" href="vistas/bower_components/bootstrap-daterangepicker/daterangepicker.css">

  <!--Morris chart -->
  <link rel="stylesheet" href="vistas/bower_components/morris.js/morris.css">



  <!--=====================================
  PLUGINS DE JAVASCRIPT
  ======================================-->

  <!-- responsive- para que salga el scroll solo en la tabla productos -->
  <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
  <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>

  <!-- jQuery 3 -->
  <script src="vistas/bower_components/jquery/dist/jquery.min.js"></script>

  <!-- CSRF Helper - debe cargarse inmediatamente después de jQuery -->
  <script src="vistas/js/csrf-helper.js"></script>

  <!-- Bootstrap 3.3.7 -->
  <script src="vistas/bower_components/bootstrap/dist/js/bootstrap.min.js"></script>

  <!-- FastClick -->
  <script src="vistas/bower_components/fastclick/lib/fastclick.js"></script>

  <!-- AdminLTE App -->
  <script src="vistas/dist/js/adminlte.min.js"></script>

  <!-- DataTables -->
  <script src="vistas/bower_components/datatables.net/js/jquery.dataTables.min.js"></script>
  <script src="vistas/bower_components/datatables.net-bs/js/dataTables.bootstrap.min.js"></script>
  <!-- DataTables responsive-->
  <script src="vistas/bower_components/datatables.net-bs/js/dataTables.responsive.min.js"></script>
  <script src="vistas/bower_components/datatables.net-bs/js/responsive.bootstrap.min.js"></script>

  <!-- DataTables Buttons -->
  <script src="https://cdn.datatables.net/buttons/1.5.2/js/dataTables.buttons.min.js"></script>
  <script src="https://cdn.datatables.net/buttons/1.5.2/js/buttons.bootstrap.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.1.3/jszip.min.js"></script>
  <script src="https://cdn.datatables.net/buttons/1.5.2/js/buttons.html5.min.js"></script>

  <!-- SweetAlert2 -->
  <script src="vistas/plugins/sweetalert2/sweetalert2.all.min.js"></script>

  <!-- iCheck 1.0.1 -->
  <script src="vistas/plugins/iCheck/icheck.min.js"></script>

  <!-- InputMask -->
  <script src="vistas/plugins/input-mask/jquery.inputmask.js"></script>
  <script src="vistas/plugins/input-mask/jquery.inputmask.date.extensions.js"></script>
  <script src="vistas/plugins/input-mask/jquery.inputmask.extensions.js"></script>

  <!-- Jquery Number-->
  <script src="vistas/plugins/jqueryNumber/jquerynumber.min.js"></script>

  <!--Daterange Picker -->
  <script src="vistas/bower_components/moment/min/moment.min.js"></script>
  <script src="vistas/bower_components/bootstrap-daterangepicker/daterangepicker.js"></script>

  <!--Morris.js chart -->
  <script src="vistas/bower_components/raphael/raphael.min.js"></script>
  <script src="vistas/bower_components/morris.js/morris.min.js"></script>

  <!--Chart Js www.chartjs.org-->
  <script src="vistas/bower_components/chart.js/Chart.js"></script>




</head>


<!--=====================================
CUERPO DOCUMENTO

<!--<body class="hold-transition skin-blue sidebar-collapse sidebar-mini login-page">-->
<!-- Site wrapper -->

<body class="hold-transition skin-blue sidebar-collapse sidebar-mini <?php if (!isset($_SESSION["iniciarSesion"]) || $_SESSION["iniciarSesion"] != "ok")
  echo 'login-page'; ?>">


  <?php

  if (isset($_SESSION["iniciarSesion"]) && $_SESSION["iniciarSesion"] == "ok") {

    echo '<div class="wrapper">';

    /*=============================================
    CABEZOTE
    =============================================*/
    include "modulos/cabezote.php";

    /*=============================================
    MENU
    =============================================*/
    include "modulos/menu.php";

    /*=============================================
    INICIO
    =============================================*/

    if (isset($_GET["ruta"])) {

      if (
        $_GET["ruta"] == "inicio" ||
        $_GET["ruta"] == "usuarios" ||
        $_GET["ruta"] == "categorias" ||
        $_GET["ruta"] == "productos" ||
        $_GET["ruta"] == "producto-detalle" ||
        $_GET["ruta"] == "ventas" ||
        $_GET["ruta"] == "crear-venta" ||
        $_GET["ruta"] == "crear-factura-electronica" ||
        $_GET["ruta"] == "editar-factura-electronica" ||
        $_GET["ruta"] == "facturas-electronicas" ||
        $_GET["ruta"] == "editar-venta" ||
        $_GET["ruta"] == "reportes" ||
        $_GET["ruta"] == "actividades" ||
        $_GET["ruta"] == "clientes" ||
        $_GET["ruta"] == "contactos" ||
        $_GET["ruta"] == "cliente-detalle" ||
        $_GET["ruta"] == "ordenes" ||
        $_GET["ruta"] == "crear-orden" ||
        $_GET["ruta"] == "editar-orden" ||
        $_GET["ruta"] == "cliente-ventas" ||
        $_GET["ruta"] == "actividades-cuadro" ||
        $_GET["ruta"] == "ordenes-visita" ||
        $_GET["ruta"] == "editarordenes-visita" ||
        $_GET["ruta"] == "proveedores" ||
        $_GET["ruta"] == "variantes" ||
        $_GET["ruta"] == "historial-stock" ||
        $_GET["ruta"] == "estados-clientes" ||
        $_GET["ruta"] == "estados-actividades" ||
        $_GET["ruta"] == "tipos-actividades" ||
        $_GET["ruta"] == "gastos" ||
        $_GET["ruta"] == "configuracion" ||
        $_GET["ruta"] == "configuracion-factus" ||
        $_GET["ruta"] == "notificaciones" ||
        $_GET["ruta"] == "logs" ||
        $_GET["ruta"] == "seguimiento-leads" ||
        $_GET["ruta"] == "notas-credito" ||
        $_GET["ruta"] == "ver-nota-credito" ||
        $_GET["ruta"] == "crear-nota-credito" ||
        $_GET["ruta"] == "documentos-soporte" ||
        $_GET["ruta"] == "crear-documento-soporte" ||
        $_GET["ruta"] == "ver-documento-soporte" ||
        $_GET["ruta"] == "crear-nota-ajuste-ds" ||
        $_GET["ruta"] == "ver-nota-ajuste-ds" ||
        $_GET["ruta"] == "notas-ajuste-ds" ||
        $_GET["ruta"] == "reportes-facturacion" ||
        $_GET["ruta"] == "salir"
      ) {

        include "modulos/" . $_GET["ruta"] . ".php";
      } else {
        include "modulos/404.php";
      }
    } else {
      include "modulos/inicio.php";
    }

    /*=============================================
    FOOTER
    =============================================*/
    include "modulos/footer.php";

    echo '</div>';

  } else {
    include "modulos/login.php";
  }

  ?>

  <!-- CSRF Helper para AJAX -->
  <script src="vistas/js/csrf-helper.js"></script>
  <script src="vistas/js/plantilla.js"></script>
  <script src="vistas/js/usuarios.js?v=<?php echo time(); ?>"></script>
  <script src="vistas/js/categorias.js"></script>
  <script src="vistas/js/productos.js"></script>
  <script src="vistas/js/clientes.js?v=<?php echo time(); ?>"></script>
  <script src="vistas/js/ventas.js?v=<?php echo time(); ?>"></script>
  <script src="vistas/js/actividades.js"></script>
  <script src="vistas/js/proveedores.js"></script>
  <script src="vistas/js/variantes.js"></script>
  <script src="vistas/js/documentos-soporte.js"></script>

  <script src="vistas/js/reportes.js"></script>
  <script src="vistas/js/historial-stock.js?v=<?php echo time(); ?>"></script>
  <script src="vistas/js/tipos-actividades.js"></script>
  <script src="vistas/js/gastos.js?v=<?php echo time(); ?>"></script>
  <script src="vistas/js/logs.js"></script>
  <script src="vistas/js/seguimiento-leads.js"></script>

  <!-- Prevenir que los enlaces se abran en nueva pestaña -->
  <script>
    $(document).ready(function () {
      // Remover target="_blank" de todos los enlaces del menú
      $('.sidebar-menu a').removeAttr('target');

      // Asegurar que los clics en enlaces del menú se abran en la misma pestaña
      $('.sidebar-menu a').on('click', function (e) {
        var href = $(this).attr('href');
        if (href && href !== '' && href !== '#' && !$(this).attr('data-toggle')) {
          e.preventDefault();
          window.location.href = href;
        }
      });   });
  </script>

</body>

</html>