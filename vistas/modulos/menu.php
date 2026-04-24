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

      if (puedeVer('inicio')) {
        echo '<li class="active">
            <a href="inicio">
              <i class="fa fa-home"></i>
              <span>Inicio</span>
            </a>
          </li>';
      }

      if (puedeVer('usuarios')) {
        echo '<li>
            <a href="usuarios">
              <i class="fa fa-user"></i>
              <span>Usuarios</span>
            </a>
          </li>';
      }

      if (puedeVer('productos') || puedeVer('categorias') || puedeVer('variantes') || puedeVer('proveedores')) {
        echo '<li class="treeview">
                <a href="">
                    <i class="fa fa-product-hunt"></i>
                    <span>Catálogo</span>
                    <span class="pull-right-container">
                    <i class="fa fa-angle-left pull-right"></i>
                    </span>
                </a>
                <ul class="treeview-menu">';

        if (puedeVer('productos')) {
          echo '<li>
                      <a href="productos">
                        <i class="fa fa-circle-o"></i>
                        <span>Productos</span>
                      </a>
                    </li>';
        }

        if (puedeVer('categorias')) {
          echo '<li>
                      <a href="categorias">
                        <i class="fa fa-circle-o"></i>
                        <span>Categorías</span>
                      </a>
                    </li>';
        }

        if (puedeVer('variantes')) {
          echo '<li>
                      <a href="variantes">
                        <i class="fa fa-circle-o"></i>
                        <span>Variantes</span>
                      </a>
                    </li>';
        }

        if (puedeVer('proveedores')) {
          echo '<li>
                      <a href="proveedores">
                        <i class="fa fa-circle-o"></i>
                        <span>Proveedores</span>
                      </a>
                    </li>';
        }

        echo '</ul>
              </li>';
      }

      if (puedeVer('clientes')) {
        echo '<li>
            <a href="clientes">
              <i class="fa fa-users"></i>
              <span>Clientes</span>
            </a>
          </li>';
      }

      if (puedeVer('actividades')) {
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
                  </li>
                </ul>
              </li>';
      }

      if (puedeVer('ordenes')) {
        echo '<li>
            <a href="ordenes">
              <i class="fa fa-shopping-cart"></i>
              <span>Ordenes</span>
            </a>
          </li>';
      }

      if (puedeVer('consulta-ventas')) {
        echo '<li>
                      <a href="consulta-ventas">
                        <i class="fa fa-search"></i>
                        <span>Consulta de ventas</span>
                      </a>
                    </li>';
      }

      if (puedeVer('ventas')) {
        echo '<li>
                      <a href="ventas">
                        <i class="fa fa-line-chart"></i>
                        <span>Ventas</span>
                      </a>
                    </li>';
      }

      if (puedeVer('factura_electronica') || puedeVer('notas_credito')) {
        echo '<li class="treeview">
            <a href="">
                <i class="fa fa-file-text-o"></i>
                <span>Factura Electrónica</span>
                <span class="pull-right-container">
                    <i class="fa fa-angle-left pull-right"></i>
                </span>
            </a>
            <ul class="treeview-menu">';

        if (puedeVer('factura_electronica')) {
          echo '<li>
                    <a href="facturas-electronicas">
                        <i class="fa fa-circle-o"></i>
                        <span>Administrar Facturas</span>
                    </a>
                </li>';
        }

        if (puedeVer('notas_credito')) {
          echo '<li>
                    <a href="notas-credito">
                        <i class="fa fa-circle-o"></i>
                        <span>Notas Crédito</span>
                    </a>
                </li>';
        }

        echo '</ul>
        </li>';
      }

      if (puedeVer('documento_soporte') || puedeVer('notas_ajuste')) {
        echo '<li class="treeview">
            <a href="">
                <i class="fa fa-file-powerpoint-o"></i>
                <span>Documento Soporte</span>
                <span class="pull-right-container">
                    <i class="fa fa-angle-left pull-right"></i>
                </span>
            </a>
            <ul class="treeview-menu">';

        if (puedeVer('documento_soporte')) {
          echo '<li>
                    <a href="documentos-soporte">
                        <i class="fa fa-circle-o"></i>
                        <span>Documentos soporte</span>
                    </a>
                </li>';
        }

        if (puedeVer('notas_ajuste')) {
          echo '<li>
                    <a href="notas-ajuste-ds">
                        <i class="fa fa-circle-o"></i>
                        <span>Notas de ajuste</span>
                    </a>
                </li>';
        }

        echo '</ul>
        </li>';
      }

      if (puedeVer('reporte_ventas')) {
        echo '<li>
                <a href="reportes">
                  <i class="fa fa-bar-chart"></i>
                  <span>Reporte de ventas</span>
                </a>
              </li>';
      }

      if (puedeVer('seguimiento_leads')) {
        echo '<li>
            <a href="seguimiento-leads">
                <i class="fa fa-eye"></i>
                <span>Seguimiento a leads</span>
            </a>
        </li>';
      }

      if (puedeVer('historial_stock')) {
        echo '<li>
          <a href="historial-stock">
            <i class="fa fa-history"></i>
            <span>Historial de Stock</span>
          </a>
        </li>';
      }

      if (puedeVer('gastos')) {
        echo '<li>
          <a href="gastos">
            <i class="fa fa-money"></i>
            <span>Gastos</span>
          </a>
        </li>';
      }

      if (puedeVer('notificaciones')) {
        echo '<li>
            <a href="notificaciones">
              <i class="fa fa-bell"></i>
              <span>Notificaciones</span>
            </a>
        </li>';
      }

      if (puedeVer('configuracion')) {
        echo '<li>
          <a href="configuracion">
            <i class="fa fa-cogs"></i>
            <span>Configuración</span>
          </a>
        </li>';
      }

      ?>

    </ul>

  </section>

</aside>



<!-- scripts de AdminLTE -->
<!-- <script src="bower_components/jquery/dist/jquery.min.js"></script> -->

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