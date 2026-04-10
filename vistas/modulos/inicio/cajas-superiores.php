<!-- Estilos para mostrar en el titulo del Perfil Visitante-->
<style>
  h1 {
    text-align: center;
    margin-bottom: 10px;
    color: #0000003f;
    text-shadow: 0px 0px 10px #074552ff;
  }

  h2 {
    text-align: center;
    margin-bottom: 20px;
    color: #;
    text-shadow: 0px 0px 8px #0e78a8ff;
  }

  p {
    font-size: 1.2rem;
    opacity: 0.9;
  }

  button {
    margin-top: 20px;
    padding: 12px 25px;
    font-size: 1rem;
    border: none;
    border-radius: 25px;
    background: #00d4ff;
    color: #000;
    font-weight: bold;
    cursor: pointer;
    transition: 0.3s;
  }

  button:hover {
    background: #00a6c7;
    transform: scale(1.05);
  }

  .small-box-footer {
    display: block;
    text-align: center;
    padding: 18px 40px;
    font-size: 20px;
    font-weight: bold;
    text-decoration: none;
    border-radius: 5px;
    transition: all 0.3s ease;
  }

  .small-box-footer:hover {
    transform: scale(1.05);
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
  }

  .small-box-footer i {
    margin-left: 10px;
    font-size: 22px;
  }
</style>


<?php

$item = null;
$valor = null;
$orden = "id";

// Obtener cantidad de ventas (en lugar de suma total)
$ventas = ControladorVentas::ctrContarVentas();
$totalVentas = $ventas["total"];

// Obtener cantidad de órdenes (en lugar de categorías)
$ordenes = ControladorVentas::ctrContarOrdenes();
$totalOrdenes = $ordenes["total"];

$clientes = ControladorClientes::ctrMostrarClientes($item, $valor);
$totalClientes = count($clientes);

$productos = ControladorProductos::ctrMostrarProductos($item, $valor, $orden);
$totalProductos = count($productos);

?>
<?php
if ($_SESSION["perfil"] != "Visitante") {
  ?>



  <div class="col-lg-3 col-xs-6">

    <div class="small-box bg-aqua">


      <div class="inner">
        <h3><?php echo number_format($totalVentas); ?></h3>
        <p>Ventas</p>
      </div>
      <div class="icon">
        <!--<i class="ion ion-social-usd"></i>-->
        <i class="ion ion-stats-bars"></i>
      </div>


      <?php
      if ($_SESSION["perfil"] == "Administrador") {
        ?>
        <a href="ventas" class="small-box-footer">
          Más info <i class="fa fa-arrow-circle-right"></i>
        </a>

        <?php
      }
      ?>

    </div>
  </div>


  <!-- ./col -->
  <div class="col-lg-3 col-xs-6">

    <div class="small-box bg-yellow">

      <div class="inner">

        <h3><?php echo number_format($totalClientes); ?></h3>

        <p>Clientes</p>

      </div>

      <div class="icon">

        <i class="ion ion-person-add"></i>

      </div>

      <?php
      if ($_SESSION["perfil"] == "Administrador") {
        ?>

        <a href="clientes" class="small-box-footer">
          Más info <i class="fa fa-arrow-circle-right"></i>
        </a>

        <?php
      }
      ?>

    </div>

  </div>


  <!-- ./col -->
  <div class="col-lg-3 col-xs-6">

    <div class="small-box bg-red">

      <div class="inner">

        <h3><?php echo number_format($totalProductos); ?></h3>

        <p>Productos</p>

      </div>

      <div class="icon">

        <i class="ion ion-ios-cart"></i>

      </div>

      <?php
      if ($_SESSION["perfil"] == "Administrador") {
        ?>

        <a href="productos" class="small-box-footer">
          Más info <i class="fa fa-arrow-circle-right"></i>
        </a>

        <?php
      }
      ?>

    </div>

  </div>


  <!-- ./col -->
  <div class="col-lg-3 col-xs-6">

    <div class="small-box bg-green">

      <div class="inner">

        <h3><?php echo number_format($totalOrdenes); ?></h3>

        <p>Órdenes</p>

      </div>

      <div class="icon">

        <!--<i class="ion ion-ios-cart"></i>-->
        <i class="ion ion-document-text"></i>

      </div>

      <?php
      if ($_SESSION["perfil"] == "Administrador") {
        ?>

        <a href="ordenes" class="small-box-footer">
          Más info <i class="fa fa-arrow-circle-right"></i>
        </a>

        <?php
      }
      ?>

    </div>

  </div>




  <?php
}
?>


<?php
if ($_SESSION["perfil"] == "Visitante") {
  ?>

  <h1>Bienvenido a Grupo Fej Technologies</h1>
  <h2>Consulta el estado de tu orden con tu código del pedido</h2>

  <a href="ordenes-visita" class="small-box-footer">
    Consulta <i class="fa fa-arrow-circle-right"></i>
  </a>

  <?php
}
?>