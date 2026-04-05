/*=============================================
EDITAR CATEGORIA
=============================================*/

$(".tablas").on("click", ".btnEditarCategoria", function(){

	var idCategoria = $(this).attr("idCategoria");

	var datos = new FormData();
	datos.append("idCategoria", idCategoria);
	// csrf_token removido - manejado por csrf-helper.js

	$.ajax({

		url:"ajax/categorias.ajax.php",
		method: "POST",
		data: datos,
		cache: false,
		contentType: false,
		processData: false,
		dataType: "json",
		success: function(respuesta){

			$("#editarCategoria").val(respuesta["categoria"]);
			$("#idCategoria").val(respuesta["id"]);

		}

	})


})


$(".tablas").on("click", ".btnEliminarCategoria", function () {

	var idCategoria = $(this).attr("idCategoria");

	swal({

		title: '¿Esta seguro de borrar la categoría?',
		text: "¡Si no lo está puede cancelar la acción!",
		type: 'warning',
		showCancelButton: true,
		confirmButtonColor: '#3085d6',
		cancelButtonColor: '#d33',
		cancelButtonText: 'Cancelar',
		confirmButtonText: 'Si, borrar categoría!'
	}).then((result) => {

		if (result.value) {

			var datos = new FormData();
			datos.append("idCategoriaEliminar", idCategoria);
			// csrf_token removido - manejado por csrf-helper.js

			$.ajax({
				url: "ajax/categorias.ajax.php",
				method: "POST",
				data: datos,
				cache: false,
				contentType: false,
				processData: false,
				success: function (respuesta) {
					if (respuesta == "ok") {
						swal({
							type: "success",
							title: "¡Borrado correctamente!",
							text: "La categoría ha sido borrada correctamente.",
							showConfirmButton: true,
							confirmButtonText: "Cerrar"
						}).then((result) => {
							if (result.value) {
								window.location.reload();
							}
						});
					} else if (respuesta == "error_productos_asociados") {
						swal({
							type: "error",
							title: "¡No se puede eliminar!",
							text: "La categoría tiene productos asociados.",
							showConfirmButton: true,
							confirmButtonText: "Cerrar"
						});
					} else {
						swal({
							type: "error",
							title: "Error",
							text: "No se pudo eliminar. " + respuesta,
							showConfirmButton: true,
							confirmButtonText: "Cerrar"
						});
					}
				}
			})
		}

	})

})


/*=============================================
HPM REVISAR SI LA CATEGORIA YA ESTA REGISTRADA
=============================================*/

$("#nuevaCategoria").change(function(){

	$(".alert").remove();

	var categoria = $(this).val();

	var datos = new FormData();
	datos.append("validarCategoria", categoria);
	// csrf_token removido - manejado por csrf-helper.js

	$.ajax({
		url:"ajax/categorias.ajax.php",
		method: "POST",
		data: datos,
		cache: false,
		contentType: false,
		processData: false,
		dataType: "json",
		success: function(respuesta){

			if(respuesta){

				$("#nuevaCategoria").parent().after('<div class="alert alert-warning">Esta categoría ya existe en la base de datos!</div>');

				$("#nuevaCategoria").val("");
			}

		}
	})
})