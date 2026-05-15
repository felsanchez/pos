/*=============================================
CARGAR LA TABLA DINÁMICA DE TRASLADOS
=============================================*/

// Usaremos el mismo endpoint de productos para mostrar los disponibles
// pero con un filtro de bodega si es necesario

var tableTraslados = $('.tablaTraslados').DataTable({
    "ajax": {
        "url": "ajax/datatable-productos-traslados.ajax.php?idBodega=" + $("#nuevaBodegaOrigen").val(),
        "type": "POST",
        "data": function (d) {
            d.idBodega = $("#nuevaBodegaOrigen").val();
        }
    },
    "deferRender": true,
    "retrieve": true,
    "processing": true,
    "serverSide": true,
    "columnDefs": [
        {
            "targets": 1, // Imagen
            "render": function (data, type, row) {
                return '<img src="' + row[1] + '" class="img-thumbnail" width="40px">';
            }
        },
        {
            "targets": 4, // Stock
            "render": function (data, type, row) {
                var stock = row[4];
                var btnClass = "btn-success";
                if (stock <= 10) btnClass = "btn-danger";
                else if (stock >= 11 && stock <= 15) btnClass = "btn-warning";
                return '<div class="btn-group"><button class="btn ' + btnClass + ' btn-xs">' + stock + '</button></div>';
            }
        },
        {
            "targets": 5, // Acciones
            "render": function (data, type, row) {
                if (row[6] == "1") {
                    return '<div class="btn-group"><button class="btn btn-warning btn-xs btnVariantesTraslado recuperarBoton" data-id-producto="' + row[5] + '"><i class="fa fa-list"></i> Variantes</button></div>';
                } else {
                    return '<div class="btn-group"><button class="btn btn-primary btn-xs agregarProducto recuperarBoton" idProducto="' + row[5] + '">Agregar</button></div>';
                }
            }
        }
    ],
    "language": {
        "sProcessing":     "Procesando...",
        "sLengthMenu":     "Mostrar _MENU_ registros",
        "sZeroRecords":    "No se encontraron resultados",
        "sEmptyTable":     "Ningún dato disponible en esta tabla",
        "sInfo":           "Mostrando registros del _START_ al _END_ de un total de _TOTAL_",
        "sInfoEmpty":      "Mostrando registros del 0 al 0 de un total de 0",
        "sInfoFiltered":   "(filtrado de un total de _MAX_ registros)",
        "sSearch":         "Buscar:",
        "sLoadingRecords": "Cargando...",
        "oPaginate": {
            "sFirst":    "Primero",
            "sLast":     "Último",
            "sNext":     "Siguiente",
            "sPrevious": "Anterior"
        },
        "oAria": {
            "sSortAscending":  ": Activar para ordenar la columna de manera ascendente",
            "sSortDescending": ": Activar para ordenar la columna de manera descendente"
        }
    }
});

/*=============================================
AGREGAR PRODUCTOS AL TRASLADO DESDE LA TABLA
=============================================*/

$(".tablaTraslados tbody").on("click", "button.agregarProducto", function(){

	var idProducto = $(this).attr("idProducto");
	var esVariante = $(this).attr("esVariante");
	var idVariante = $(this).attr("idVariante");

	$(this).removeClass("btn-primary agregarProducto");
	$(this).addClass("btn-default");

	var datos = new FormData();
	datos.append("idProducto", idProducto);

	$.ajax({
		url:"ajax/productos.ajax.php",
		method: "POST",
		data: datos,
		cache: false,
		contentType: false,
		processData: false,
		dataType:"json",
		success:function(respuesta){

			var descripcion = respuesta["descripcion"];
			var stock = respuesta["stock"];
            var id_p = respuesta["id"];

            // Si es variante, necesitamos obtener el stock y nombre específico de la variante
            if(esVariante == "1"){
                var datosV = new FormData();
                datosV.append("idVariante", idVariante);
                datosV.append("idBodega", $("#nuevaBodegaOrigen").val());

                $.ajax({
                    url:"ajax/productos.ajax.php",
                    method: "POST",
                    data: datosV,
                    cache: false,
                    contentType: false,
                    processData: false,
                    dataType:"json",
                    success:function(respV){
                        
                        descripcion = descripcion + " - " + respV["nombre"];
                        stock = respV["stock"];

                        agregarItem(id_p, descripcion, stock, esVariante, idVariante);
                    }
                })
            } else {
                agregarItem(id_p, descripcion, stock, "0", null);
            }
		}
	})
});

function agregarItem(idProducto, descripcion, stock, esVariante, idVariante){

    $(".nuevoProducto").append(
        '<div class="row" style="padding:5px 15px">' +
        '<!-- Descripción del producto -->' +
        '<div class="col-xs-6" style="padding-right:0px">' +
        '<div class="input-group">' +
        '<span class="input-group-addon"><button type="button" class="btn btn-danger btn-xs quitarProducto" idProducto="'+idProducto+'" idVariante="'+idVariante+'"><i class="fa fa-times"></i></button></span>' +
        '<input type="text" class="form-control nuevaDescripcionProducto" idProducto="'+idProducto+'" name="agregarProducto" value="'+descripcion+'" readonly required>' +
        '</div>' +
        '</div>' +
        '<!-- Cantidad del producto -->' +
        '<div class="col-xs-3">' +
        '<input type="number" class="form-control nuevaCantidadProducto" name="nuevaCantidadProducto" min="1" value="1" stock="'+stock+'" nuevoStock="'+(stock-1)+'" required>' +
        '</div>' +
        '<!-- Stock disponible -->' +
        '<div class="col-xs-3" style="padding-left:0px">' +
        '<div class="input-group">' +
        '<span class="input-group-addon"><i class="fa fa-check"></i></span>' +
        '<input type="text" class="form-control nuevoStock" value="'+stock+'" readonly>' +
        '</div>' +
        '</div>' +
        '<input type="hidden" class="esVariante" value="'+esVariante+'">' +
        '<input type="hidden" class="idVariante" value="'+idVariante+'">' +
        '</div>'
    );

    listarProductosTraslado();
}

/*=============================================
QUITAR PRODUCTO DEL TRASLADO Y RECUPERAR BOTÓN
=============================================*/

$(".formularioTraslado").on("click", "button.quitarProducto", function(){

	$(this).parent().parent().parent().parent().remove();

	var idProducto = $(this).attr("idProducto");
	var idVariante = $(this).attr("idVariante");

    // Volver a habilitar el botón en la tabla
    if(idVariante != "null" && idVariante != ""){
        $('button.recuperarBoton[idVariante="'+idVariante+'"]').addClass("btn-primary agregarProducto");
	    $('button.recuperarBoton[idVariante="'+idVariante+'"]').removeClass("btn-default");
    } else {
        $('button.recuperarBoton[idProducto="'+idProducto+'"]').addClass("btn-primary agregarProducto");
	    $('button.recuperarBoton[idProducto="'+idProducto+'"]').removeClass("btn-default");
    }

	if($(".nuevoProducto").children().length == 0){
		$("#listaProductos").val("");
	} else {
		listarProductosTraslado();
	}
});

/*=============================================
MODIFICAR LA CANTIDAD
=============================================*/

$(".formularioTraslado").on("change", "input.nuevaCantidadProducto", function(){

	var stock = Number($(this).attr("stock"));
	var cantidad = Number($(this).val());

	if(cantidad > stock){
		$(this).val(1);
		swal({
			title: "La cantidad supera el Stock",
			text: "¡Sólo hay "+stock+" unidades!",
			type: "error",
			confirmButtonText: "¡Cerrar!"
		});
	}

	listarProductosTraslado();
});

/*=============================================
LISTAR TODOS LOS PRODUCTOS
=============================================*/

function listarProductosTraslado(){

	var listaProductos = [];

	var descripcion = $(".nuevaDescripcionProducto");
	var cantidad = $(".nuevaCantidadProducto");
    var esVariante = $(".esVariante");
    var idVariante = $(".idVariante");

	for(var i = 0; i < descripcion.length; i++){

		listaProductos.push({ "id" : $(descripcion[i]).attr("idProducto"), 
							  "descripcion" : $(descripcion[i]).val(),
							  "cantidad" : $(cantidad[i]).val(),
                              "esVariante" : $(esVariante[i]).val(),
                              "idVariante" : $(idVariante[i]).val()
                            })
	}

	$("#listaProductos").val(JSON.stringify(listaProductos));
}

/*=============================================
CAMBIO DE BODEGA ORIGEN - RECARGAR TABLA
=============================================*/
$("#nuevaBodegaOrigen").change(function(){
    var idBodega = $(this).val();
    tableTraslados.ajax.url("ajax/datatable-productos-traslados.ajax.php?idBodega=" + idBodega).load();

    // Evitar misma bodega en destino
    $("#nuevaBodegaDestino option").prop("disabled", false);
    if(idBodega != ""){
        $("#nuevaBodegaDestino option[value='"+idBodega+"']").prop("disabled", true);
    }
});

/*=============================================
EVITAR MISMA BODEGA EN ORIGEN AL CAMBIAR DESTINO
=============================================*/
$("#nuevaBodegaDestino").change(function(){
    var idBodega = $(this).val();
    
    $("#nuevaBodegaOrigen option").prop("disabled", false);
    if(idBodega != ""){
        $("#nuevaBodegaOrigen option[value='"+idBodega+"']").prop("disabled", true);
    }
});

// Ejecutar al cargar para inicializar estados si ya hay valores
$(document).ready(function(){
    if($("#nuevaBodegaOrigen").val() != ""){
        $("#nuevaBodegaDestino option[value='"+$("#nuevaBodegaOrigen").val()+"']").prop("disabled", true);
    }
    if($("#nuevaBodegaDestino").val() != ""){
        $("#nuevaBodegaOrigen option[value='"+$("#nuevaBodegaDestino").val()+"']").prop("disabled", true);
    }
});

/*=============================================
EXPANDIR VARIANTES EN TRASLADOS
=============================================*/
function formatearTablaVariantesTraslado(variantes) {
	if (!variantes || variantes.length === 0) {
		return '<div class="alert alert-info">No hay variantes para este producto</div>';
	}

	var html = '<div style="padding: 10px; background-color: #f9f9f9;">';
	html += '<table class="table table-condensed table-bordered table-striped" style="background-color: white; margin-bottom: 0;">';
	html += '<thead><tr><th>Variante</th><th width="80px">Stock</th><th width="100px">Acción</th></tr></thead>';
	html += '<tbody>';

	for (var i = 0; i < variantes.length; i++) {
		var variante = variantes[i];
		if (variante.estado != 1) continue;

		var stockBadge = (variante.stock <= 0) ? '<span class="badge bg-red">' + variante.stock + '</span>' : 
                         (variante.stock <= 10) ? '<span class="badge bg-yellow">' + variante.stock + '</span>' : 
                         '<span class="badge bg-green">' + variante.stock + '</span>';

		var botonAgregar = (variante.stock > 0) ? 
            '<button class="btn btn-primary btn-xs agregarVarianteTraslado" idVariante="' + variante.id + '" idProductoBase="' + variante.id_producto + '" nombreVariante="' + variante.nombre + '" stockVariante="' + variante.stock + '">Agregar</button>' : 
            '<button class="btn btn-default btn-xs" disabled>Sin stock</button>';

		html += '<tr><td>' + variante.nombre + '</td><td class="text-center">' + stockBadge + '</td><td class="text-center">' + botonAgregar + '</td></tr>';
	}
	html += '</tbody></table></div>';
	return html;
}

$(document).on('click', '.btnVariantesTraslado', function () {
	var boton = $(this);
	var tr = boton.closest('tr');
	var row = tableTraslados.row(tr);
	var idProducto = boton.attr('data-id-producto');
	var icono = boton.find('i');

	if (row.child.isShown()) {
		row.child.hide();
		tr.removeClass('shown');
		icono.removeClass('fa-minus').addClass('fa-list');
		boton.removeClass('btn-danger').addClass('btn-warning');
	} else {
		boton.prop('disabled', true);
		icono.removeClass('fa-list').addClass('fa-spinner fa-spin');

		var datos = new FormData();
		datos.append("obtenerVariantesProducto", idProducto);
        datos.append("idBodega", $("#nuevaBodegaOrigen").val());

		$.ajax({
			url: "ajax/productos.ajax.php",
			method: "POST",
			data: datos,
			cache: false,
			contentType: false,
			processData: false,
			dataType: "json",
			success: function (variantes) {
				var tablaVariantes = formatearTablaVariantesTraslado(variantes);
				row.child(tablaVariantes).show();
				tr.addClass('shown');
				icono.removeClass('fa-spinner fa-spin').addClass('fa-minus');
				boton.removeClass('btn-warning').addClass('btn-danger').prop('disabled', false);
			}
		});
	}
});

/*=============================================
AGREGAR VARIANTE AL TRASLADO
=============================================*/
$(document).on("click", ".agregarVarianteTraslado", function () {
	var idVariante = $(this).attr("idVariante");
	var idProductoBase = $(this).attr("idProductoBase");
	var nombreVariante = $(this).attr("nombreVariante");
	var stockVariante = $(this).attr("stockVariante");

    // Obtener descripción base del producto
    var datos = new FormData();
    datos.append("idProducto", idProductoBase);

    $.ajax({
        url:"ajax/productos.ajax.php",
        method: "POST",
        data: datos,
        cache: false,
        contentType: false,
        processData: false,
        dataType:"json",
        success:function(respuesta){
            var descripcionCompleta = respuesta["descripcion"] + " - " + nombreVariante;
            agregarItem(idProductoBase, descripcionCompleta, stockVariante, "1", idVariante);
        }
    });

	$(this).removeClass("btn-primary").addClass("btn-default").prop("disabled", true);
});

/*=============================================
VER DETALLE TRASLADO (MODAL)
=============================================*/
$(".tablas").on("click", ".btnVerTraslado", function(){
    var idTraslado = $(this).attr("idTraslado");
    
    var datos = new FormData();
    datos.append("idTraslado", idTraslado);
    datos.append("tipo", "obtenerDetalle");

    $.ajax({
        url:"ajax/traslados.ajax.php",
        method: "POST",
        data: datos,
        cache: false,
        contentType: false,
        processData: false,
        dataType:"json",
        success:function(respuesta){
            
            $("#detalleTrasladoBody").empty();
            $("#verNotasTraslado").text(respuesta.header.notas);

            respuesta.items.forEach(function(item){
                var variante = item.nombre_variante ? item.nombre_variante : "-";
                $("#detalleTrasladoBody").append(
                    '<tr>' +
                    '<td>'+item.nombre_producto+'</td>' +
                    '<td>'+variante+'</td>' +
                    '<td>'+item.cantidad+'</td>' +
                    '</tr>'
                );
            });
        }
    })
});

/*=============================================
COMPLETAR TRASLADO
=============================================*/
$(".tablas").on("click", ".btnCompletarTraslado", function(){

    var idTraslado = $(this).attr("idTraslado");

    swal({
        title: '¿Está seguro de completar este traslado?',
        text: "¡El inventario se moverá físicamente entre las bodegas!",
        type: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        cancelButtonText: 'Cancelar',
        confirmButtonText: 'Sí, completar!'
    }).then(function(result){
        if (result.value) {
            
            var datos = new FormData();
            datos.append("idTraslado", idTraslado);
            datos.append("tipo", "completar");

            $.ajax({
                url:"ajax/traslados.ajax.php",
                method: "POST",
                data: datos,
                cache: false,
                contentType: false,
                processData: false,
                dataType:"text",
                success:function(respuesta){
                    if(respuesta == "ok"){
                        swal({
                            type: "success",
                            title: "El traslado ha sido completado y el stock actualizado",
                            showConfirmButton: true,
                            confirmButtonText: "Cerrar"
                        }).then(function(result){
                            if (result.value) {
                                window.location = "traslados";
                            }
                        })
                    } else {
                        swal({
                            type: "error",
                            title: "Error",
                            text: respuesta
                        });
                    }
                }
            })
        }
    })
});

/*=============================================
CANCELAR TRASLADO
=============================================*/
$(".tablas").on("click", ".btnCancelarTraslado", function(){

    var idTraslado = $(this).attr("idTraslado");

    swal({
        title: '¿Está seguro de cancelar este traslado?',
        text: "¡No se realizará ningún movimiento de stock!",
        type: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        cancelButtonText: 'Cancelar',
        confirmButtonText: 'Sí, cancelar!'
    }).then(function(result){
        if (result.value) {
            window.location = "index.php?ruta=traslados&idTrasladoCancelar="+idTraslado;
        }
    })
});

/*=============================================
ACTUALIZAR NOTAS TRASLADO INLINE
=============================================*/
$(document).on("blur", ".celda-notas-traslado", function () {
    var idTraslado = $(this).data("id");
    var notas = $(this).text().trim();
    var celda = $(this);

    var datos = new FormData();
    datos.append("idTraslado", idTraslado);
    datos.append("notas", notas);
    datos.append("tipo", "actualizarNotas");

    $.ajax({
        url: "ajax/traslados.ajax.php",
        method: "POST",
        data: datos,
        cache: false,
        contentType: false,
        processData: false,
        success: function (respuesta) {
            if (respuesta == "ok") {
                celda.css("background", "#d4edda"); // Verde éxito temporal
                setTimeout(function () {
                    celda.css("background", "");
                }, 500);
            }
        }
    });
});
