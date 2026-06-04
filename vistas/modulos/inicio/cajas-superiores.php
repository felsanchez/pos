<!-- Estilos modernos para cajas métricas (Dashboard SaaS Premium) -->
<style>
  .card-metric {
    position: relative;
    display: block;
    padding: 22px 20px;
    margin-bottom: 20px;
    border-radius: 12px;
    color: #ffffff !important;
    overflow: hidden;
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.06);
    transition: transform 0.3s cubic-bezier(0.25, 0.8, 0.25, 1), box-shadow 0.3s ease;
    text-decoration: none !important;
  }

  .card-metric:hover {
    transform: translateY(-6px);
    box-shadow: 0 10px 22px rgba(0, 0, 0, 0.12);
  }

  /* Gradientes Modernos */
  .metric-grad-1 {
    background: linear-gradient(135deg, #7f00ff, #e100ff); /* Violeta a Rosa */
  }
  .metric-grad-2 {
    background: linear-gradient(135deg, #00c6ff, #0072ff); /* Celeste a Azul */
  }
  .metric-grad-3 {
    background: linear-gradient(135deg, #fe8c00, #f83600); /* Naranja a Rojo */
  }
  .metric-grad-4 {
    background: linear-gradient(135deg, #11998e, #38ef7d); /* Esmeralda a Menta */
  }

  .card-metric .inner h3 {
    font-size: 26px;
    font-weight: 700;
    margin: 0 0 5px 0;
    white-space: nowrap;
    letter-spacing: -0.5px;
  }

  .card-metric .inner p {
    font-size: 13px;
    font-weight: 500;
    opacity: 0.85;
    margin: 0;
    text-transform: uppercase;
    letter-spacing: 0.5px;
  }

  .card-metric .icon {
    position: absolute;
    top: 15px;
    right: 15px;
    font-size: 44px;
    opacity: 0.18;
    transition: transform 0.3s ease, opacity 0.3s ease;
  }

  .card-metric:hover .icon {
    transform: scale(1.15) rotate(8deg);
    opacity: 0.28;
  }

  .card-metric-footer {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-top: 15px;
    padding-top: 10px;
    border-top: 1px solid rgba(255, 255, 255, 0.15);
    font-size: 12px;
    font-weight: 600;
    color: rgba(255, 255, 255, 0.95);
  }

  .card-metric-footer i {
    transition: transform 0.2s ease;
  }

  .card-metric:hover .card-metric-footer i {
    transform: translateX(4px);
  }

  h1.panel-title {
    font-weight: 700;
    color: #333;
    text-shadow: none;
    margin-bottom: 25px;
  }
</style>

<?php
// Obtener conexión para las estadísticas iniciales del mes actual (por defecto en el dashboard)
$db = Conexion::conectar();
$idBodegaActiva = isset($_SESSION["id_bodega"]) && !empty($_SESSION["id_bodega"]) ? intval($_SESSION["id_bodega"]) : 1;

// 1. Obtener ventas del mes actual
$sqlVentasMes = "
  SELECT 
    SUM(total) as total_monto, 
    COUNT(*) as total_cantidad 
  FROM ventas 
  WHERE estado = 'venta' 
    AND id_bodega = :id_bodega
    AND ( ( (resolucion_id IS NULL OR resolucion_id = 0) AND (estado_dian IS NULL OR estado_dian = '') ) OR ( resolucion_id IS NOT NULL AND resolucion_id != 0 AND estado_dian IN ('aceptada', 'enviada') ) )
    AND MONTH(fecha) = MONTH(CURDATE()) 
    AND YEAR(fecha) = YEAR(CURDATE())
";
$stmtVMes = $db->prepare($sqlVentasMes);
$stmtVMes->bindParam(":id_bodega", $idBodegaActiva, PDO::PARAM_INT);
$stmtVMes->execute();
$resVMes = $stmtVMes->fetch(PDO::FETCH_ASSOC);

$inicialMontoVentas = $resVMes ? (float)$resVMes["total_monto"] : 0;
$inicialCantVentas = $resVMes ? (int)$resVMes["total_cantidad"] : 0;
$inicialTicketPromedio = $inicialCantVentas > 0 ? ($inicialMontoVentas / $inicialCantVentas) : 0;

// 2. Obtener órdenes del mes actual
$sqlOrdenesMes = "
  SELECT COUNT(*) as total_cantidad 
  FROM ventas 
  WHERE estado = 'orden' 
    AND id_bodega = :id_bodega
    AND MONTH(fecha) = MONTH(CURDATE()) 
    AND YEAR(fecha) = YEAR(CURDATE())
";
$stmtOMes = $db->prepare($sqlOrdenesMes);
$stmtOMes->bindParam(":id_bodega", $idBodegaActiva, PDO::PARAM_INT);
$stmtOMes->execute();
$resOMes = $stmtOMes->fetch(PDO::FETCH_ASSOC);
$inicialCantOrdenes = $resOMes ? (int)$resOMes["total_cantidad"] : 0;
?>

<?php if (puedeAccion('inicio', 'ver')): ?>

  <!-- Tarjeta 1: Total Ventas ($) -->
  <div class="col-lg-3 col-xs-6">
    <div class="card-metric metric-grad-2">
      <div class="inner">
        <h3 id="caja-total-ventas">$<?php echo number_format($inicialMontoVentas, 0, ',', '.'); ?></h3>
        <p>Total Facturado</p>
      </div>
      <div class="icon">
        <i class="fa fa-dollar"></i>
      </div>
      <?php if (puedeAccion('ventas', 'ver')): ?>
        <a href="ventas" class="card-metric-footer">
          Ver Ventas <i class="fa fa-arrow-circle-right"></i>
        </a>
      <?php else: ?>
        <div class="card-metric-footer"><span>Mes Actual</span></div>
      <?php endif; ?>
    </div>
  </div>

  <!-- Tarjeta 2: Ticket Promedio ($) -->
  <div class="col-lg-3 col-xs-6">
    <div class="card-metric metric-grad-1">
      <div class="inner">
        <h3 id="caja-ticket-promedio">$<?php echo number_format($inicialTicketPromedio, 0, ',', '.'); ?></h3>
        <p>Ticket Promedio</p>
      </div>
      <div class="icon">
        <i class="fa fa-calculator"></i>
      </div>
      <?php if (puedeAccion('ventas', 'ver')): ?>
        <a href="ventas" class="card-metric-footer">
          Analizar Ventas <i class="fa fa-arrow-circle-right"></i>
        </a>
      <?php else: ?>
        <div class="card-metric-footer"><span>Valor Promedio</span></div>
      <?php endif; ?>
    </div>
  </div>

  <!-- Tarjeta 3: Cantidad de Ventas -->
  <div class="col-lg-3 col-xs-6">
    <div class="card-metric metric-grad-3">
      <div class="inner">
        <h3 id="caja-cant-ventas"><?php echo number_format($inicialCantVentas, 0, ',', '.'); ?></h3>
        <p>Transacciones</p>
      </div>
      <div class="icon">
        <i class="fa fa-shopping-cart"></i>
      </div>
      <?php if (puedeAccion('ventas', 'ver')): ?>
        <a href="ventas" class="card-metric-footer">
          Ver Facturas <i class="fa fa-arrow-circle-right"></i>
        </a>
      <?php else: ?>
        <div class="card-metric-footer"><span>Facturas Emitidas</span></div>
      <?php endif; ?>
    </div>
  </div>

  <!-- Tarjeta 4: Cantidad de Órdenes -->
  <div class="col-lg-3 col-xs-6">
    <div class="card-metric metric-grad-4">
      <div class="inner">
        <h3 id="caja-cant-ordenes"><?php echo number_format($inicialCantOrdenes, 0, ',', '.'); ?></h3>
        <p>Órdenes Registradas</p>
      </div>
      <div class="icon">
        <i class="fa fa-file-text"></i>
      </div>
      <?php if (puedeAccion('ordenes', 'ver')): ?>
        <a href="ordenes" class="card-metric-footer">
          Ver Órdenes <i class="fa fa-arrow-circle-right"></i>
        </a>
      <?php else: ?>
        <div class="card-metric-footer"><span>Pedidos de Visita</span></div>
      <?php endif; ?>
    </div>
  </div>

<?php endif; ?>

<?php if (!puedeAccion('inicio', 'ver')): ?>
  <div class="text-center" style="padding: 40px 15px;">
    <h1 class="panel-title">Bienvenido a Kontrol Pos</h1>
    <h3 style="color:#666; margin-bottom:30px;">Consulta el estado de tu orden con tu código del pedido</h3>
    <a href="consulta-ventas" class="btn btn-info btn-lg" style="border-radius:25px; padding: 12px 30px; font-weight:700;">
      Realizar Consulta <i class="fa fa-search" style="margin-left:8px;"></i>
    </a>
  </div>
<?php endif; ?>