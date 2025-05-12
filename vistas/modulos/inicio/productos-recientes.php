<?php

$item = null;
$valor = null;
$orden = "id";

$productos = ControladorProductos::ctrMostrarProductos($item, $valor, $orden);

// Asegurar que sea un array
$productos = is_array($productos) ? $productos : [];

// Calcular cuántos productos mostrar (máximo 6)
$limite = min(6, count($productos));

?>

<div class="box box-primary">

  <div class="box-header with-border">
    <h3 class="box-title">Últimos productos añadidos</h3>
    <div class="box-tools pull-right">
      <button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i></button>
      <button type="button" class="btn btn-box-tool" data-widget="remove"><i class="fa fa-times"></i></button>
    </div>
  </div>

  <div class="box-body">

    <ul class="products-list product-list-in-box">

      <?php if ($limite > 0): ?>
        <?php for ($i = 0; $i < $limite; $i++): ?>
          <li class="item">
            <div class="product-img">
              <img src="<?php echo $productos[$i]["imagen"]; ?>" alt="Product Image">
            </div>
            <div class="product-info">
              <a href="#" class="product-title">
                <?php echo $productos[$i]["descripcion"]; ?>
                <span class="label label-warning pull-right">
                 <h6>$<?php echo number_format($productos[$i]["precio_venta"], 2); ?> </h6>
                </span>
              </a>
            </div>
          </li>
        <?php endfor; ?>
      <?php else: ?>
        <li class="item">
          <div class="product-info text-center">
            <span class="text-muted">No hay productos registrados aún.</span>
          </div>
        </li>
      <?php endif; ?>

    </ul>

  </div>

  <div class="box-footer text-center">
    <a href="productos" class="uppercase">Ver todos los productos</a>
  </div>

</div>
