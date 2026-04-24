<?php
// Obtener configuración del sistema
$configuracion = ControladorConfiguracion::ctrObtenerConfiguracion();
$nombreEmpresa = !empty($configuracion["nombre_empresa"]) ? $configuracion["nombre_empresa"] : "Tablero";
?>

<style>
  .content-header h1 {
    font-family: 'Source Sans Pro', sans-serif;
    font-weight: 300 !important;
    color: #2c3e50 !important;
    letter-spacing: 1.5px;
    text-transform: uppercase;
    font-size: 26px !important;
    display: flex;
    align-items: center;
    gap: 12px;
    margin-top: 5px;
    text-shadow: none !important;
  }

  .content-header h1 small {
    font-weight: 400 !important;
    text-transform: none;
    letter-spacing: 0;
    color: #7f8c8d !important;
    font-size: 16px !important;
    border-left: 2px solid #bdc3c7;
    padding-left: 12px;
    display: inline-block;
    text-shadow: none !important;
  }

  .box-bienvenida {
    background: transparent !important;
    box-shadow: none !important;
    border: none !important;
    margin-top: 30px;
  }

  .titulo-bienvenida {
    font-weight: 300 !important;
    color: #34495e !important;
    font-size: 28px !important;
    text-shadow: none !important;
    letter-spacing: 0.5px;
    margin: 0;
  }

  @media (max-width: 767px) {
    .content-header h1 {
      flex-direction: column;
      align-items: flex-start;
      gap: 5px;
      font-size: 22px !important;
    }
    .content-header h1 small {
      border-left: none;
      padding-left: 0;
    }
    .titulo-bienvenida {
      font-size: 22px !important;
    }
  }
</style>

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