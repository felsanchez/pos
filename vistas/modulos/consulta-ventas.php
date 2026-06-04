<?php
$xml = ControladorVentas::ctrDescargarXML();
if ($xml) {
    rename($_GET["xml"] . ".xml", "xml/" . $_GET["xml"] . ".xml");
    echo '<a class="btn btn-block btn-success abrirXML" archivo="xml/' . $_GET["xml"] . '.xml" href="ventas">Se ha creado correctamente el archivo XML<span class="fa fa-times pull-right"></span></a>';
}
?>

<style>
/* ── Contenedor principal centrado ── */
.consulta-wrapper {
    display: flex;
    flex-direction: column;
    align-items: center;
    padding: 40px 20px;
    min-height: 65vh;
}

/* ── Hero del buscador ── */
.search-hero {
    text-align: center;
    margin-bottom: 35px;
}
.search-hero h2 {
    font-size: 26px;
    font-weight: 700;
    color: #2c3e50;
    margin-bottom: 6px;
}
.search-hero p {
    color: #7f8c8d;
    font-size: 14px;
    margin: 0;
}

/* ── Caja de búsqueda ── */
.search-box-card {
    background: #fff;
    border-radius: 12px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.09);
    padding: 28px 32px;
    width: 100%;
    max-width: 560px;
}
.search-input-group {
    display: flex;
    gap: 10px;
}
.search-input-group input {
    flex: 1;
    border: 2px solid #dfe6e9;
    border-radius: 8px;
    padding: 11px 16px;
    font-size: 15px;
    transition: border-color .2s;
    outline: none;
}
.search-input-group input:focus {
    border-color: #3498db;
}
.search-input-group .btn-buscar {
    background: #3498db;
    color: #fff;
    border: none;
    border-radius: 8px;
    padding: 0 22px;
    font-size: 15px;
    font-weight: 600;
    cursor: pointer;
    transition: background .2s;
    white-space: nowrap;
}
.search-input-group .btn-buscar:hover {
    background: #2980b9;
}
.search-input-group .btn-buscar i {
    margin-right: 6px;
}

/* ── Resultado ── */
#resultadoConsulta {
    width: 100%;
    max-width: 760px;
    margin-top: 30px;
}
.resultado-card {
    background: #fff;
    border-radius: 12px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.09);
    overflow: hidden;
}
.resultado-header {
    background: linear-gradient(135deg, #2c3e50, #3498db);
    color: #fff;
    padding: 18px 24px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 10px;
}
.resultado-header h4 {
    margin: 0;
    font-size: 17px;
    font-weight: 700;
}
.resultado-header .badge-estado {
    padding: 5px 14px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: .5px;
}
.badge-venta   { background: rgba(46,213,115,.25); color: #2ecc71; }
.badge-anulada { background: rgba(231,76,60,.25);  color: #e74c3c; }
.badge-orden   { background: rgba(241,196,15,.25); color: #f39c12; }

.resultado-body {
    padding: 24px;
}
.info-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 16px 24px;
    margin-bottom: 20px;
}
@media(max-width:500px){ .info-grid { grid-template-columns: 1fr; } }
.info-item label {
    font-size: 11px;
    font-weight: 700;
    color: #95a5a6;
    text-transform: uppercase;
    letter-spacing: .5px;
    display: block;
    margin-bottom: 3px;
}
.info-item span {
    font-size: 14px;
    color: #2c3e50;
    font-weight: 600;
}

/* ── Tabla de productos ── */
.productos-titulo {
    font-size: 13px;
    font-weight: 700;
    color: #7f8c8d;
    text-transform: uppercase;
    letter-spacing: .5px;
    margin-bottom: 10px;
    padding-top: 10px;
    border-top: 1px solid #ecf0f1;
}
.tabla-productos {
    width: 100%;
    border-collapse: collapse;
    font-size: 13px;
}
.tabla-productos thead th {
    background: #f8f9fa;
    padding: 8px 12px;
    text-align: left;
    font-weight: 700;
    color: #6c757d;
    border-bottom: 2px solid #dee2e6;
    font-size: 11px;
    text-transform: uppercase;
    letter-spacing: .4px;
}
.tabla-productos tbody td {
    padding: 9px 12px;
    border-bottom: 1px solid #f1f3f5;
    color: #2c3e50;
    vertical-align: middle;
}
.tabla-productos tbody tr:last-child td { border-bottom: none; }
.tabla-productos tbody tr:hover td { background: #f8f9fa; }
.txt-right { text-align: right; }

/* ── Totales ── */
.totales-section {
    margin-top: 16px;
    padding-top: 16px;
    border-top: 2px solid #ecf0f1;
    display: flex;
    justify-content: flex-end;
}
.totales-table { min-width: 220px; }
.totales-table tr td { padding: 4px 8px; font-size: 13px; color: #555; }
.totales-table tr td:last-child { text-align: right; font-weight: 600; color: #2c3e50; }
.totales-table .total-final td { font-size: 16px; font-weight: 700; color: #2c3e50; border-top: 1px solid #dee2e6; padding-top: 8px; }

/* ── Acciones ── */
.acciones-consulta {
    padding: 16px 24px;
    background: #f8f9fa;
    border-top: 1px solid #ecf0f1;
    display: flex;
    gap: 10px;
    flex-wrap: wrap;
}

/* ── Estado vacío / error ── */
.estado-vacio {
    text-align: center;
    padding: 30px 20px;
    color: #95a5a6;
}
.estado-vacio i { font-size: 42px; margin-bottom: 12px; display: block; }
.estado-vacio p { font-size: 14px; margin: 0; }
.estado-error { color: #e74c3c; }
.estado-error i { color: #e74c3c; }
</style>

<div class="content-wrapper">
  <section class="content-header">
    <h1>
      Consulta de Ventas
      <small>Buscar por código</small>
    </h1>
    <ol class="breadcrumb">
      <li><a href="inicio"><i class="fa fa-dashboard"></i> Inicio</a></li>
      <li class="active">Consulta de Ventas</li>
    </ol>
  </section>

  <section class="content">
    <div class="consulta-wrapper">

      <!-- Hero -->
      <div class="search-hero">
        <h2><i class="fa fa-search" style="color:#3498db; margin-right:8px;"></i>Consultar Venta</h2>
        <p>Ingresa el código de la venta para ver todos sus detalles</p>
      </div>

      <!-- Caja de búsqueda -->
      <div class="search-box-card">
        <div class="search-input-group">
          <input type="text"
                 id="codigoConsulta"
                 placeholder="Ej: 990000301"
                 autocomplete="off"
                 autofocus>
          <button class="btn-buscar" id="btnConsultarVenta">
            <i class="fa fa-search"></i> Consultar
          </button>
        </div>
        <div style="margin-top:10px; font-size:12px; color:#b2bec3; text-align:center;">
          Presiona <kbd>Enter</kbd> o haz clic en Consultar
        </div>
      </div>

      <!-- Resultado (oculto inicialmente) -->
      <div id="resultadoConsulta" style="display:none;"></div>

    </div>
  </section>
</div>

<?php
$eliminarVenta = new ControladorVentas();
$eliminarVenta->ctrEliminarVenta();
?>

<script>
$(document).ready(function () {

    var moneda = '<?php
        $cfg = ControladorConfiguracion::ctrObtenerConfiguracion();
        echo !empty($cfg["moneda"]) ? htmlspecialchars($cfg["moneda"]) : "$";
    ?>';

    // ── Buscar al presionar Enter ──
    $('#codigoConsulta').on('keypress', function (e) {
        if (e.which === 13) $('#btnConsultarVenta').trigger('click');
    });

    // ── Acción del botón ──
    $('#btnConsultarVenta').on('click', function () {
        var codigo = $('#codigoConsulta').val().trim();
        if (!codigo) {
            $('#codigoConsulta').focus();
            return;
        }
        consultarVenta(codigo);
    });

    function consultarVenta(codigo) {
        var $resultado = $('#resultadoConsulta');
        $resultado.html('<div class="estado-vacio"><i class="fa fa-spinner fa-spin"></i><p>Buscando venta...</p></div>').show();

        var datos = new FormData();
        datos.append('codigo', codigo);
        datos.append('csrf_token', $('meta[name="csrf-token"]').attr('content'));

        $.ajax({
            url: 'ajax/consulta-ventas.ajax.php',
            method: 'POST',
            data: datos,
            cache: false,
            contentType: false,
            processData: false,
            dataType: 'json',
            success: function (resp) {
                if (!resp || resp.error) {
                    $resultado.html(
                        '<div class="resultado-card"><div class="estado-vacio estado-error">' +
                        '<i class="fa fa-times-circle"></i>' +
                        '<p>' + (resp && resp.error ? resp.error : 'No se encontró ninguna venta con ese código.') + '</p>' +
                        '</div></div>'
                    );
                    return;
                }
                renderResultado(resp);
            },
            error: function () {
                $resultado.html(
                    '<div class="resultado-card"><div class="estado-vacio estado-error">' +
                    '<i class="fa fa-exclamation-triangle"></i>' +
                    '<p>Error al conectar con el servidor. Intenta de nuevo.</p>' +
                    '</div></div>'
                );
            }
        });
    }

    function renderResultado(v) {
        // Estado badge
        var estadoClass = 'badge-venta';
        var estadoLabel = 'Venta';
        if (v.estado === 'anulada') { estadoClass = 'badge-anulada'; estadoLabel = 'Anulada'; }
        else if (v.estado === 'orden') { estadoClass = 'badge-orden'; estadoLabel = 'Orden'; }

        // Productos
        var productos = [];
        try { productos = JSON.parse(v.productos || '[]'); } catch(e) {}

        var filasProductos = '';
        var subtotalCheck = 0;
        if (Array.isArray(productos) && productos.length > 0) {
            productos.forEach(function(p) {
                var nombre = p.nombre || p.descripcion || '–';
                var qty    = parseFloat(p.cantidad || p.qty || 1);
                var precio = parseFloat(p.precio || p.price || 0);
                var total  = parseFloat(p.total || p.subtotal || (qty * precio));
                subtotalCheck += total;
                filasProductos +=
                    '<tr>' +
                    '<td>' + escHtml(nombre) + '</td>' +
                    '<td class="txt-right">' + qty + '</td>' +
                    '<td class="txt-right">' + moneda + ' ' + formatNum(precio) + '</td>' +
                    '<td class="txt-right"><strong>' + moneda + ' ' + formatNum(total) + '</strong></td>' +
                    '</tr>';
            });
        } else {
            filasProductos = '<tr><td colspan="4" style="text-align:center; color:#aaa;">Sin detalle de productos</td></tr>';
        }

        // Totales
        var neto      = parseFloat(v.neto || 0);
        var impuesto  = parseFloat(v.impuesto || 0);
        var descuento = parseFloat(v.monto_descuento || 0);
        var total     = parseFloat(v.total || 0);

        var filasTotales = '';
        if (neto > 0) {
            filasTotales += '<tr><td>Subtotal</td><td>' + moneda + ' ' + formatNum(neto) + '</td></tr>';
        }
        if (impuesto > 0) {
            filasTotales += '<tr><td>Impuesto</td><td>' + moneda + ' ' + formatNum(impuesto) + '</td></tr>';
        }
        if (descuento > 0) {
            filasTotales += '<tr><td>Descuento</td><td>- ' + moneda + ' ' + formatNum(descuento) + '</td></tr>';
        }
        filasTotales += '<tr class="total-final"><td><strong>TOTAL</strong></td><td><strong>' + moneda + ' ' + formatNum(total) + '</strong></td></tr>';

        // Fecha formateada
        var fecha = v.fecha ? v.fecha.substring(0, 10) : '–';

        // Botones de acción
        var acciones = '';
        <?php if (puedeAccion('ventas', 'ver') || puedeVer('ventas')): ?>
        acciones += '<a href="index.php?ruta=ver-detalle-orden&idVenta=' + v.id + '" class="btn btn-warning btn-sm"><i class="fa fa-eye"></i> Ver detalle</a>';
        <?php endif; ?>
        acciones += '<a href="extensiones/tcpdf/pdf/descargar-pdf-orden.php?idVenta=' + v.id + '" target="_blank" class="btn btn-danger btn-sm"><i class="fa fa-file-pdf-o"></i> Descargar PDF</a>';

        var html =
            '<div class="resultado-card">' +

            // Header
            '<div class="resultado-header">' +
            '<h4><i class="fa fa-file-text-o" style="margin-right:8px;"></i>Código: ' + escHtml(v.codigo) + '</h4>' +
            '<span class="badge-estado ' + estadoClass + '">' + estadoLabel + '</span>' +
            '</div>' +

            // Body info
            '<div class="resultado-body">' +
            '<div class="info-grid">' +
            '<div class="info-item"><label>Cliente</label><span>' + escHtml(v.nombre_cliente || '–') + '</span></div>' +
            '<div class="info-item"><label>Vendedor</label><span>' + escHtml(v.nombre_vendedor || '–') + '</span></div>' +
            '<div class="info-item"><label>Fecha</label><span>' + escHtml(fecha) + '</span></div>' +
            '<div class="info-item"><label>Método de Pago</label><span>' + escHtml(v.metodo_pago || '–') + '</span></div>' +
            (v.notas ? '<div class="info-item" style="grid-column:1/-1"><label>Notas</label><span>' + escHtml(v.notas) + '</span></div>' : '') +
            '</div>' +

            // Productos
            '<div class="productos-titulo">Productos</div>' +
            '<table class="tabla-productos">' +
            '<thead><tr><th>Producto</th><th class="txt-right">Cant.</th><th class="txt-right">Precio Unit.</th><th class="txt-right">Total</th></tr></thead>' +
            '<tbody>' + filasProductos + '</tbody>' +
            '</table>' +

            // Totales
            '<div class="totales-section"><table class="totales-table"><tbody>' + filasTotales + '</tbody></table></div>' +
            '</div>' +

            // Acciones
            (acciones ? '<div class="acciones-consulta">' + acciones + '</div>' : '') +
            '</div>';

        $('#resultadoConsulta').html(html);
    }

    function escHtml(str) {
        if (!str) return '';
        return String(str)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;');
    }

    function formatNum(n) {
        return parseFloat(n || 0).toLocaleString('es-CO', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }

});
</script>