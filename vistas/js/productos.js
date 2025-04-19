/*=============================================
CARGAR TABLA DINAMICA
=============================================*/

	var table = $(".tablaProductos").DataTable({


		"ajax":"ajax/datatable-productos.ajax.php",
		"columnDefs": [

		  {
				"targets": -9,
				"data": null,
				"defaultContent": '<img class="img-thumbnail imgTabla" width="40px">'
		  },


		  {
				"targets": -1,
				"data": null,
				"defaultContent": '<div class="btn-group"><button class="btn btn-warning btnEditarProducto" idProducto data-toggle="modal" data-target="#modalEditarProducto"><i class="fa fa-pencil"></i></button><button class="btn btn-danger btnEliminarProducto" idProducto codigo imagen><i class="fa fa-times"></i></button></div>'
		  }


		],

		"language": {

			"sProcessing":     "Procesando...",
			"sLengthMenu":     "Mostrar _MENU_ registros",
			"sZeroRecords":    "No se encontraron resultados",
			"sEmptyTable":     "Ningún dato disponible en esta tabla",
			"sInfo":           "Mostrando registros del _START_ al _END_ de un total de _TOTAL_",
			"sInfoEmpty":      "Mostrando registros del 0 al 0 de un total de 0",
			"sInfoFiltered":   "(filtrado de un total de _MAX_ registros",
			"sInfoPostFix":    "",
			"sSearch":         "Buscar",
			"sUrl":            "",
			"sInfoThousands":  ",",
			"sLoadingRecords": "Cargando...",
			"oPaginate":       {
			"sFirst":          "Primero",
			"sLast":           "Último",
			"sNext":           "Siguiente",
			"sPrevious":       "Anterior"
				},
			"oAria":  {
				"sSortAscending":  ": Activar para ordenar la columna de manera ascendente",
				"sSortDescending": ": Activar para ordenar la columna de manera descendente"
			}

		}

	})

/*=============================================
ACTIVAR LOS BOTONES CON LOS ID CORRESPONDIENTES
=============================================*/

$('.tablaProductos tbody').on( 'click', 'button', function () {
var data = table.row( $(this).parents('tr') ).data();

$(this).attr("idProducto", data[9])
$(this).attr("codigo", data[2])
$(this).attr("imagen", data[1])

} );


/*=============================================
FUNCION PARA CARGAR IMAGENES
=============================================*/

function cargarImagenes(){

		var imgTabla = $(".imgTabla");

//	for(var i = 0; i < imgTabla.length; i ++){

//		var data = table.row( $(imgTabla[i]).parents("tr")).data();

//		$(imgTabla[i]).attr("src", data[1]);
//	}


	//hecho por mi -colocar colores dependiendo de la cantidad del stock
	var limiteStock2 = $(".limiteStock2");

	for(var i = 0; i < imgTabla.length; i ++){

		var data = table.row( $(imgTabla[i]).parents("tr")).data();

		$(imgTabla[i]).attr("src", data[1]);


		if(data[5] <= 10){

			$(limiteStock2[i]).addClass("btn-danger");
			$(limiteStock2[i]).html(data[5]);
		}
		else if(data[5] >= 11 && data[5] <= 15){

			$(limiteStock2[i]).addClass("btn-warning");
			$(limiteStock2[i]).html(data[5]);
		}
		else{
			$(limiteStock2[i]).addClass("btn-success");
			$(limiteStock2[i]).html(data[5]);
		}
	}



}


//CARGAMOS LAS IMAGENES CUANDO ENTRAMOS A LA PAGINA POR PRIMERA VEZ
setTimeout(function(){

	cargarImagenes();

	/*if($(".perfilUsuario").val() != "Administrador"){
		$('.btnEliminarProducto').remove();
	}*/

},300)


//CARGAMOS LAS IMAGENES CUANDO INTERACTUAMOS CON  EL FILTRO DE CANTIDAD
$("select[name='DataTables_Table_0_length']").change(function(){

	cargarImagenes();

	/*if($(".perfilUsuario").val() != "Administrador"){
		$('.btnEliminarProducto').remove();
	}*/
})


//CARGAMOS LAS IMAGENES CUANDO INTERACTUAMOS CON  EL PAGINADOR 
$(".dataTables_paginate").click(function(){

	cargarImagenes();

	/*if($(".perfilUsuario").val() != "Administrador"){
		$('.btnEliminarProducto').remove();
	}*/

})


//CARGAMOS LAS IMAGENES CUANDO INTERACTUAMOS CON  EL BUSCADOR
$("input[aria-controls='DataTables_Table_0']").focus(function(){

	$(document).keyup(function(event){

		event.preventDefault();

		cargarImagenes();

		/*if($(".perfilUsuario").val() != "Administrador"){
		$('.btnEliminarProducto').remove();
	    }*/

	})

})


//CARGAMOS LAS IMAGENES CUANDO INTERACTUAMOS CON  EL FILTRO DE ORDENADOR 
$(".sorting").click(function(){

	cargarImagenes();

	/*if($(".perfilUsuario").val() != "Administrador"){
		$('.btnEliminarProducto').remove();
	}*/
})


/*=============================================
CAPTURANDO LA CATEGORIA PARA ASIGNAR CODIGO
=============================================*/
$("#nuevaCategoria").change(function(){

	var idCategoria = $(this).val();

	var datos = new FormData();
	datos.append("idCategoria", idCategoria);

	$.ajax({

		url:"ajax/productos.ajax.php",
		method: "POST",
		data: datos,
		cache: false,
		contentType: false,
		processData: false,
		dataType:"json",
		success:function(respuesta){

			if(!respuesta){

				var nuevoCodigo = idCategoria+"01";
				$("#nuevoCodigo").val(nuevoCodigo);
			}

			else{

				var nuevoCodigo = Number(respuesta["codigo"]) + 1;
				$("#nuevoCodigo").val(nuevoCodigo);
			}
			

		}


	})

})


/*=============================================
AGREGANDO PRECIO DE VENTA
=============================================*/

$("#nuevoPrecioCompra, #editarPrecioCompra").change(function(){

	if($(".porcentaje").prop("checked")){

		var valorPorcentaje = $(".nuevoPorcentaje").val();
		
		var porcentaje = Number(($("#nuevoPrecioCompra").val()*valorPorcentaje/100))+Number($("#nuevoPrecioCompra").val());

		var editarPorcentaje = Number(($("#editarPrecioCompra").val()*valorPorcentaje/100))+Number($("#editarPrecioCompra").val());

		$("#nuevoPrecioVenta").val(porcentaje);
		$("#nuevoPrecioVenta").prop("readonly",true);

		$("#editarPrecioVenta").val(editarPorcentaje);
		$("#editarPrecioVenta").prop("readonly",true);
	}
	
})


/*=============================================
CAMBIO DE PORCENTAJE
=============================================*/

$(".nuevoPorcentaje").change(function(){

	if($(".porcentaje").prop("checked")){

		var valorPorcentaje = $(this).val();
		
		var porcentaje = Number(($("#nuevoPrecioCompra").val()*valorPorcentaje/100))+Number($("#nuevoPrecioCompra").val());

		var editarPorcentaje = Number(($("#editarPrecioCompra").val()*valorPorcentaje/100))+Number($("#editarPrecioCompra").val());

		$("#nuevoPrecioVenta").val(porcentaje);
		$("#nuevoPrecioVenta").prop("readonly",true);

		$("#editarPrecioVenta").val(editarPorcentaje);
		$("#editarPrecioVenta").prop("readonly",true);
	}

})

$(".porcentaje").on("ifUnchecked",function(){

	$("#nuevoPrecioVenta").prop("readonly",false);
	$("#editarPrecioVenta").prop("readonly",false);
})


$(".porcentaje").on("ifChecked",function(){

	$("#nuevoPrecioVenta").prop("readonly",true);
	$("#editarPrecioVenta").prop("readonly",true);
})



/*=============================================
SUBIENDO FOTO DEL PRODUCTO
=============================================*/

$(".nuevaImagen").change(function(){

	var imagen = this.files[0];


/*=============================================
VALIDAMOS EL FORMATO DE LA IMAGEN QUE SEA JPG O PNG
=============================================*/

	if (imagen["type"] != "image/jpeg" && imagen["type"] != "image/png") {

		$(".nuevaImagen").val("");

		swal({
			title: "Error al subir la imagenn",
			text: "¡La imagen debe estar en formato jpg o png!",
			type: "error",
			confirmButtonText: "¡Cerrar!"
		});
	}

	else if(imagen["size"] > 2000000){

		$(".nuevaImagen").val("");

		swal({
			title: "Error al subir la imagen",
			text: "¡La imagen no debe pesar mas de 2MB!",
			type: "error",
			confirmButtonText: "¡Cerrar!"
		});

	}

	else{

		var datosImagen = new FileReader;
		datosImagen.readAsDataURL(imagen);

		$(datosImagen).on("load", function(event){

			var rutaImagen = event.target.result;

			$(".previsualizar").attr("src", rutaImagen);
		})
	}


})


/*=============================================
EDITAR PRODUCTO
=============================================*/

$(".tablaProductos tbody").on("click", "button.btnEditarProducto", function(){

	var idProducto = $(this).attr("idProducto");
	
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

			var datosCategoria = new FormData();
			datosCategoria.append("idCategoria",respuesta["id_categoria"]);

			$.ajax({

				url:"ajax/categorias.ajax.php",
				method: "POST",
				data: datosCategoria,
				cache: false,
				contentType: false,
				processData: false,
				dataType:"json",
				success:function(respuesta){					

					$("#editarCategoria").val(respuesta["id"]);
					$("#editarCategoria").html(respuesta["categoria"]);
				}
			 })	
			 	

			$("#editarCodigo").val(respuesta["codigo"]);
			$("#editarDescripcion").val(respuesta["descripcion"]);
			$("#editarStock").val(respuesta["stock"]);
			$("#editarPrecioCompra").val(respuesta["precio_compra"]);
			$("#editarPrecioVenta").val(respuesta["precio_venta"]);

			if(respuesta["imagen"] != ""){

				$("#imagenActual").val(respuesta["imagen"]);
				$(".previsualizar").attr("src", respuesta["imagen"]);
			}


		}
	})


})


/*=============================================
ELIMINAR PRODUCTO
=============================================*/

$(".tablaProductos tbody").on("click", "button.btnEliminarProducto", function(){

	var idProducto = $(this).attr("idProducto");
	var codigo = $(this).attr("codigo");
	var imagen = $(this).attr("imagen");

	
	swal({

		title: '¿Esta seguro de borrar el producto?',
		text: "¡Si no lo está puede cancelar la acción!",
		type: 'warning',
		showCancelButton: true,
		confirmButtonColor: '#3085d6',
		cancelButtonColor: '#d33',
		cancelButtonText: 'Cancelar',
		confirmButtonText: 'Si, borrar producto!'
	    }).then((result)=>{

		if(result.value){

			window.location = "index.php?ruta=productos&idProducto="+idProducto+"&imagen="+imagen+"&codigo="+codigo;

		}

	})


})



/*=============================================
HPM REVISAR SI EL PRODUCTO YA ESTA REGISTRADO
=============================================*/

$("#nuevaDescripcion").change(function(){

	$(".alert").remove();

	var descripcion = $(this).val();

	var datos = new FormData();
	datos.append("validarDescripcion", descripcion);

	$.ajax({
		url:"ajax/productos.ajax.php",
		method: "POST",
		data: datos,
		cache: false,
		contentType: false,
		processData: false,
		dataType: "json",
		success: function(respuesta){

			if(respuesta){

				$("#nuevaDescripcion").parent().after('<div class="alert alert-warning">Este producto ya existe en la base de datos!</div>');

				$("#nuevaDescripcion").val("");

				
			}

		}
	})
})