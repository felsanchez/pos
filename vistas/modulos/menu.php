<head>
  <!-- estilos de AdminLTE -->
  <link rel="stylesheet" href="dist/css/AdminLTE.min.css">
  <link rel="stylesheet" href="dist/css/skins/_all-skins.min.css">

  <!-- tu css personalizado (sobrescribe a AdminLTE) -->
  <style>
    @media (max-width: 767px) {

      /* ocultar el submenu original de AdminLTE */
      .sidebar-menu .treeview-menu {
        display: none !important;
      }

      /* estilos para el submenu móvil clonado */
      .sidebar-menu .mobile-submenu {
        background: #2c3b41;
        margin: 0;
        padding: 0;
        list-style: none;
      }

      .sidebar-menu .mobile-submenu>li>a {
        display: block;
        padding: 10px 15px 10px 40px;
        line-height: 1.4;
        color: #b8c7ce;
      }
    }
  </style>
</head>



<aside class="main-sidebar">

  <section class="sidebar">

    <ul class="sidebar-menu">

      <?php


      if ($_SESSION["perfil"] == "Administrador") {

        echo '<li class="active">

            <a href="inicio">   

              <i class="fa fa-home"></i>
              <span>Inicio</span>

            </a>   

          </li>


          <li>

            <a href="usuarios">   

              <i class="fa fa-user"></i>
              <span>Usuarios</span>

            </a>   

          </li>';
      }


      if ($_SESSION["perfil"] == "Administrador" || $_SESSION["perfil"] == "Especial" || $_SESSION["perfil"] == "Vendedor") {


        echo '<li class="treeview">
                <a href="">         
                    <i class="fa fa-product-hunt"></i>
                    <span>Productos</span>
                    <span class="pull-right-container">
                    <i class="fa fa-angle-left pull-right"></i>
                    </span>
                </a>

                <ul class="treeview-menu">
                  <li>
                    <a href="productos">
                      <i class="fa fa-circle-o"></i>
                      <span>Productos</span>
                    </a>
                  </li>

                  <li>
                    <a href="categorias">
                      <i class="fa fa-circle-o"></i>
                      <span>Categorias</span>
                    </a>
                  </li>

                  <li>
                    <a href="variantes">
                      <i class="fa fa-circle-o"></i>
                      <span>Variantes</span>
                    </a>
                  </li>

                  <li>
                    <a href="proveedores">
                      <i class="fa fa-circle-o"></i>
                      <span>Proveedores</span>
                    </a>
                  </li>';

        echo '</ul> 

        </li>';

      }



      if ($_SESSION["perfil"] == "Administrador" || $_SESSION["perfil"] == "Vendedor" || $_SESSION["perfil"] == "Especial") {

        echo '<li>

            <a href="clientes">   

              <i class="fa fa-users"></i>
              <span>Clientes</span>

            </a>   

          </li>';

      }



      if ($_SESSION["perfil"] == "Administrador" || $_SESSION["perfil"] == "Vendedor" || $_SESSION["perfil"] == "Especial") {

        echo '<li class="treeview">
                <a href="">         
                    <i class="fa fa-calendar"></i>
                    <span>Actividades</span>
                    <span class="pull-right-container">
                    <i class="fa fa-angle-left pull-right"></i>
                    </span>
                </a>

                <ul class="treeview-menu">
                  <li>
                    <a href="actividades">
                      <i class="fa fa-circle-o"></i>
                      <span>Actividades</span>
                    </a>
                  </li>

                  <li>
                    <a href="actividades-cuadro">
                      <i class="fa fa-circle-o"></i>
                      <span>Calendario</span>
                    </a>
                  </li>';

        echo '</ul> 

        </li>';
      }



      if ($_SESSION["perfil"] == "Administrador" || $_SESSION["perfil"] == "Vendedor") {

        echo '<li class="treeview">
                <a href="">         
                    <i class="fa fa-shopping-cart"></i>
                    <span>Ordenes</span>
                    <span class="pull-right-container">
                    <i class="fa fa-angle-left pull-right"></i>
                    </span>
                </a>

                <ul class="treeview-menu">
                  <li>
                    <a href="ordenes">
                      <i class="fa fa-circle-o"></i>
                      <span>Administrar ordenes</span>
                    </a>
                  </li>

                  <li>
                    <a href="crear-orden">
                      <i class="fa fa-circle-o"></i>
                      <span>Crear ordenes</span>
                    </a>
                  </li>';

        echo '</ul> 

        </li>';
      }




      if ($_SESSION["perfil"] == "Visitante") {

        echo '<li>
                      <a href="ordenes-visita">
                        <i class="fa fa-circle-o"></i>
                        <span>Ordenes de Venta</span>
                      </a>
                    </li>';
      }



      if ($_SESSION["perfil"] == "Administrador" || $_SESSION["perfil"] == "Vendedor") {

        echo '<li class="treeview">
                  <a href="">         
                      <i class="fa fa-line-chart"></i>
                      <span>Ventas</span>
                      <span class="pull-right-container">
                      <i class="fa fa-angle-left pull-right"></i>
                      </span>
                  </a>

                  <ul class="treeview-menu">
                    <li>
                      <a href="ventas">
                        <i class="fa fa-circle-o"></i>
                        <span>Administrar ventas</span>
                      </a>
                    </li>

                    <li>
                      <a href="crear-venta">
                        <i class="fa fa-circle-o"></i>
                        <span>Crear ventas</span>
                      </a>
                    </li>';

        if ($_SESSION["perfil"] == "Administrador") {

          echo '<li>
                      <a href="reportes">
                        <i class="fa fa-circle-o"></i>
                        <span>Reporte de ventas</span>
                      </a>
                    </li>';
        }

        echo '</ul> 

          </li>';
      }

      if ($_SESSION["perfil"] == "Administrador" || $_SESSION["perfil"] == "Vendedor") {

        echo '<li class="treeview">
            <a href="">
                <i class="fa fa-file-text-o"></i>
                <span>Factura Electrónica</span>
                <span class="pull-right-container">
                    <i class="fa fa-angle-left pull-right"></i>
                </span>
            </a>
            <ul class="treeview-menu">
                <li>
                    <a href="facturas-electronicas">
                        <i class="fa fa-circle-o"></i>
                        <span>Administrar Facturas</span>
                    </a>
                </li>
                <li>
                    <a href="crear-factura-electronica">
                        <i class="fa fa-circle-o"></i>
                        <span>Crear Factura</span>
                    </a>
                </li>
            </ul>
        </li>';
      }

      if ($_SESSION["perfil"] == "Administrador" || $_SESSION["perfil"] == "Vendedor") {

        echo '<li class="treeview">
            <a href="">
                <i class="fa fa-file-powerpoint-o"></i>
                <span>Documento Soporte</span>
                <span class="pull-right-container">
                    <i class="fa fa-angle-left pull-right"></i>
                </span>
            </a>
            <ul class="treeview-menu">
                <li>
                    <a href="documentos-soporte">
                        <i class="fa fa-circle-o"></i>
                        <span>Administrar Doc. Soporte</span>
                    </a>
                </li>
                <li>
                    <a href="crear-documento-soporte">
                        <i class="fa fa-circle-o"></i>
                        <span>Emitir Doc. Soporte</span>
                    </a>
                </li>
            </ul>
        </li>';

      }

      if ($_SESSION["perfil"] == "Administrador" || $_SESSION["perfil"] == "Vendedor") {

        echo '<li>
            <a href="seguimiento-leads">
                <i class="fa fa-eye"></i>
                <span>Seguimiento a leads</span>
            </a>
        </li>';

      }


      if ($_SESSION["perfil"] == "Administrador") {

        echo '<li>

          <a href="historial-stock">   

            <i class="fa fa-history"></i>
            <span>Historial de Stock</span>

          </a>   

        </li>';
      }



      if ($_SESSION["perfil"] == "Administrador") {

        echo '<li> 

          <a href="gastos"> 

            <i class="fa fa-money"></i>

            <span>Gastos</span> 

          </a> 

        </li>';

      }



      if ($_SESSION["perfil"] == "Administrador" || $_SESSION["perfil"] == "Vendedor") {

        echo '<li> 

            <a href="notificaciones"> 

              <i class="fa fa-bell"></i>

              <span>Notificaciones</span> 

            </a> 

        </li>';

      }



      if ($_SESSION["perfil"] == "Administrador") {

        echo '<li> 

          <a href="configuracion">

            <i class="fa fa-cogs"></i>

            <span>Configuración</span> 

          </a> 

        </li>';

      }


      /*
      if ($_SESSION["perfil"] == "Administrador") {

        echo '<li class="treeview">
                <a href="">
                    <i class="fa fa-file-text-o"></i>
                    <span>Logs y Fact. Electronica</span>
                    <span class="pull-right-container">
                    <i class="fa fa-angle-left pull-right"></i>
                    </span>
                </a>

                <ul class="treeview-menu">
                  <li>
                    <a href="index.php?ruta=logs">
                      <i class="fa fa-circle-o"></i>
                      <span>Logs del sistema</span>
                    </a>
                  </li>

                  <li>
                    <a href="configuracion-facturacion">
                      <i class="fa fa-circle-o"></i>
                      <span>Factus (Fact. Electrónica)</span>
                    </a>
                  </li>
                </ul>
              </li>';
      }

      */



      ?>

    </ul>

  </section>

</aside>



<!-- scripts de AdminLTE -->
<script src="bower_components/jquery/dist/jquery.min.js"></script>
<script src="bower_components/bootstrap/dist/js/bootstrap.min.js"></script>
<script src="dist/js/adminlte.min.js"></script>

<!-- tu script personalizado -->
<script>
  (function ($) {
    var PROCESSED = 'mobile-tree-processed';
    var MOBILE_MAX = 767;

    function toMobile() {
      $('.sidebar-menu li.treeview').each(function () {
        var $li = $(this);
        if ($li.data(PROCESSED)) return;

        var $submenu = $li.children('ul.treeview-menu');
        if ($submenu.length === 0) return;

        // mover hijos originales a un nuevo contenedor (no clonarlos)
        var $container = $('<ul class="mobile-submenu"></ul>').hide();
        $submenu.children('li').appendTo($container);

        // insertar contenedor después del padre
        $li.after($container);

        // esconder el submenu original vacío
        $submenu.remove();

        // toggle en el padre
        $li.children('a').off('click.mobile').on('click.mobile', function (e) {
          e.preventDefault();
          $container.slideToggle(200);
        });

        $li.data(PROCESSED, true);
      });
    }

    function toDesktop() {
      $('.sidebar-menu ul.mobile-submenu').each(function () {
        var $container = $(this);
        var $li = $container.prev('li.treeview');

        // devolver los hijos al submenu original
        var $submenu = $('<ul class="treeview-menu"></ul>');
        $container.children('li').appendTo($submenu);

        $li.append($submenu); // volver a meter al padre
        $container.remove();

        $li.children('a').off('click.mobile');
        $li.removeData(PROCESSED);
      });
    }

    function applyTransform() {
      if ($(window).width() <= MOBILE_MAX) {
        toMobile();
      } else {
        toDesktop();
      }
    }

    $(document).ready(function () {
      applyTransform();
      var resizeTimer = null;
      $(window).on('resize', function () {
        clearTimeout(resizeTimer);
        resizeTimer = setTimeout(applyTransform, 150);
      });
    });
  })(jQuery);
</script>


</body>