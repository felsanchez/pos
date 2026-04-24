<?php
// Obtener configuración del sistema
$configuracion = ControladorConfiguracion::ctrObtenerConfiguracion();
$configFactus = ControladorFactus::ctrObtenerConfiguracion();

$nombreEmpresa = !empty($configFactus["nombre_empresa"]) ? $configFactus["nombre_empresa"] : (!empty($configuracion["nombre_empresa"]) ? $configuracion["nombre_empresa"] : "Tablero");
?>

<div class="content-wrapper">

  <section class="content-header">

    <h1>
      <?php echo $nombreEmpresa; ?>
      <small>Panel de Control</small>
    </h1>

    <ol class="breadcrumb">
      <li><a href="salir"><i class="fa fa-dashboard"></i> Salir</a></li>
      <li class="active"><?php echo $nombreEmpresa; ?></li>
    </ol>

  </section>


  <section class="content">

    <div class="row">

      <?php
      
      include "inicio/cajas-superiores.php";
      
      ?>

    </div>


    <div class="row">

      <div class="col-lg-12">

        <?php if (puedeAccion('inicio', 'ver')): ?>
          <!--include "reportes/grafico-ventas.php";-->
          <div id="contenedor-barras-formas-pago">
            <div class="col-12 col-md-12">
              <?php include "reportes/analisis-ventas1.php"; ?>
            </div>
          </div>
        <?php endif; ?>

      </div>


      <div class="col-lg-6">

        <?php if (puedeAccion('inicio', 'ver')): ?>
          <?php include "reportes/productos-mas-vendidos.php"; ?>
        <?php endif; ?>

      </div>


      <div class="col-lg-6">

        <?php if (puedeAccion('inicio', 'ver')): ?>
          <?php include "inicio/productos-recientes.php"; ?>
        <?php endif; ?>

      </div>


      <div class="col-lg-12">

        <?php if (puedeAccion('inicio', 'ver')): ?>
          <div class="box-bienvenida">
            <h1 class="titulo-bienvenida">Bienvenid@ <?php echo $_SESSION["nombre"]; ?></h1>
          </div>
        <?php endif; ?>

      </div>


    </div>

  </section>

</div>