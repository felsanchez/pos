/*=============================================
EDITAR Actividades
=============================================*/

//console log
//console.log("Datos completos:", datos);

/*=============================================
EDITAR Actividades
=============================================*/
$(document).on("click", ".btnEditarActividad", function(){
	var idActividad = $(this).attr("idActividad");
    console.log("ID Actividad: " + idActividad);

	// Rellenar el input hidden
    $('#modalEditarActividad input[name="idActividad"]').val(idActividad);

	var datos = new FormData();
	datos.append("idActividad", idActividad);

	$.ajax({

		url:"ajax/actividades.ajax.php",
		method: "POST",
		data: datos,
		cache: false,
		contentType: false,
		processData: false,
		dataType: "json",
		success: function(respuesta){

            //console.log("Respuesta AJAX:", respuesta);

			//$("#idActividad").val(respuesta["id"]);
			$("#editarActividad").val(respuesta["descripcion"]);
            $("#editarTipo").val(respuesta["tipo"]);
            $("#editarUsuario").val(respuesta["id_user"]);
            //$("#editarFecha").val(respuesta["fecha"]);

			if (respuesta.fecha) {
				$("#editarFecha").val(respuesta.fecha.substring(0, 10));
			} else {
				console.error("Fecha no válida:", respuesta.fecha);
			}

            $("#editarEstado").val(respuesta["estado"]);

             // Si no hay cliente, seleccionar "Sin cliente" (value="0")
            if(!respuesta["id_cliente"] || respuesta["id_cliente"] == 0 || respuesta["id_cliente"] == null || respuesta["id_cliente"] == ""){
                $("#editarCliente").val("0");
            } else {
                $("#editarCliente").val(respuesta["id_cliente"]);
            }

            $("#editarObservacion").val(respuesta["observacion"]);

            // ✅ mostrar el modal
			$('#modalEditarActividad').modal('show');


		},

        //error: function(xhr, status, error){
            //console.error("Error en AJAX:", xhr.responseText);
        //}

	})

});



/*=============================================
ELIMINAR Actividad
=============================================*/
$(document).on("click", ".btnEliminarActividad", function(){
	
	var idActividad = $(this).attr("idActividad");

	//var_dump($idActividad);

	swal({

		title: '¿Esta seguro de borrar la actividad?',
		text: "¡Si no lo está puede cancelar la acción!",
		type: 'warning',
		showCancelButton: true,
		confirmButtonColor: '#3085d6',
		cancelButtonColor: '#d33',
		cancelButtonText: 'Cancelar',
		confirmButtonText: 'Si, borrar actividad!'
	}).then((result)=>{

		if(result.value){

			window.location = "index.php?ruta=actividades&idActividad="+idActividad;
		}
	})
})


/*=============================================
Guardar Tipo - DESACTIVADO (campo ahora es solo lectura)
=============================================*/
/*
$(".tablas").on("change", ".cambiarTipo", function() {
    var idActividad = $(this).data("id");
    var nuevoTipo = $(this).val();

	console.log("Voy a enviar AJAX con id:", idActividad, "y nuevoTipo:", nuevoTipo);

    $.ajax({
        url: "ajax/actividades.ajax.php",
        method: "POST",
        data: { idActividad: idActividad, nuevoTipo: nuevoTipo },
        success: function(respuesta) {

			//console.log("Respuesta RAW:", respuesta);

			var datos = JSON.parse(respuesta);
			//console.log("Respuesta al cambiar tipo:", datos);
			if (datos.status === "error") {
				alert("Hubo un error al actualizar");
			} else {
				alert("Tipo actualizado correctamente a: " + datos.tipo);
				// Aquí puedes actualizar el valor mostrado en la tabla, si quieres
			}
		}

    });
});
*/


/*=============================================
Guardar Estado - DESACTIVADO (campo ahora es solo lectura)
=============================================*/
/*
$(".tablas").on("change", ".cambiarEstado", function() {
    var idActividad = $(this).data("id");
    var nuevoEstado = $(this).val();

	console.log("Voy a enviar AJAX con id:", idActividad, "y nuevoEstado:", nuevoEstado);

    $.ajax({
        url: "ajax/actividades.ajax.php",
        method: "POST",
        data: { idActividad: idActividad, nuevoEstado: nuevoEstado },
        success: function(respuesta) {

			//console.log("Respuesta RAW:", respuesta);

			var datos = JSON.parse(respuesta);
			//console.log("Respuesta al cambiar tipo:", datos);
			if (datos.status === "error") {
				alert("Hubo un error al actualizar");
			} else {
				alert("Estado actualizado correctamente a: " + datos.estado);
				// Aquí puedes actualizar el valor mostrado en la tabla, si quieres
			}
		}

    });
});
*/


/*=============================================
Editar Estado desde Modal de Gestión
=============================================*/
$("#modalGestionarEstados").on("click", ".btnEditarEstadoActividad", function(){

	var idEstado = $(this).attr("idEstado");
    console.log("ID Estado: " + idEstado);

	// Rellenar el input hidden
	$('#modalEditarEstadoActividad input[name="idEstado"]').val(idEstado);

	var datos = new FormData();
	datos.append("idEstado", idEstado);

	$.ajax({

		url:"ajax/estados-actividades.ajax.php",
		method: "POST",
		data: datos,
		cache: false,
		contentType: false,
		processData: false,
		dataType: "json",
		success: function(respuesta){

			console.log("Respuesta AJAX Estado:", respuesta);

			$("#editarEstadoNombre").val(respuesta["nombre"]);
			$("#editarEstadoColor").val(respuesta["color"]);
			$("#editarEstadoOrden").val(respuesta["orden"]);

		},
		error: function(xhr, status, error){
			console.error("Error AJAX:", error);
			console.error("Status:", status);
			console.error("Respuesta completa:", xhr.responseText);

			var mensajeError = "No se pudieron cargar los datos del estado";

			// Intentar parsear la respuesta como JSON
			try {
				var respuestaJSON = JSON.parse(xhr.responseText);
				if(respuestaJSON.error){
					mensajeError = respuestaJSON.error;
				}
			} catch(e) {
				// Si no es JSON válido, mostrar la respuesta tal cual
				if(xhr.responseText){
					mensajeError = xhr.responseText.substring(0, 200);
				}
			}

			swal({
				type: "error",
				title: "Error",
				text: mensajeError
			});
		}

	})

})

/*=============================================
Restaurar scroll al cerrar modal de edición
=============================================*/
$("#modalEditarEstadoActividad").on("hidden.bs.modal", function(){
	// Si el modal de gestión sigue abierto, restaurar la clase modal-open
	if($("#modalGestionarEstados").hasClass("in")){
		$("body").addClass("modal-open");
	}
});

// Debug: Detectar eventos en el campo de edición
$(document).ready(function(){
	$(document).on("keydown keypress keyup input", "#editarEstadoNombre", function(e){
		console.log("Evento detectado en editarEstadoNombre:", e.type, "Key:", e.key);
		return true; // Permitir que el evento continúe
	});

	$(document).on("click", "#editarEstadoNombre", function(e){
		console.log("Click en editarEstadoNombre detectado");
		console.log("Elemento:", this);
		console.log("Es editable?:", !$(this).prop("disabled") && !$(this).prop("readonly"));
	});
});


/*=============================================
Eliminar Estado
=============================================*/
$(".btnEliminarEstadoActividad").click(function(){

	var idEstado = $(this).attr("idEstado");
	var nombreEstado = $(this).attr("nombreEstado");

	swal({
		title: '¿Está seguro de borrar el estado "'+nombreEstado+'"?',
		text: "¡Si no lo está puede cancelar la acción!",
		type: 'warning',
		showCancelButton: true,
		confirmButtonColor: '#3085d6',
		cancelButtonColor: '#d33',
		cancelButtonText: 'Cancelar',
		confirmButtonText: 'Sí, borrar estado!'
	}).then(function(result){

		if(result.value){

			window.location = "index.php?ruta=actividades&idEstado="+idEstado+"&nombreEstado="+nombreEstado+"&origen=actividades";

		}

	})

})


/*=============================================
Editar Tipo desde Modal de Gestión
=============================================*/
$("#modalGestionarTipos").on("click", ".btnEditarTipoActividad", function(){

	var idTipo = $(this).attr("idTipo");
    console.log("ID Tipo: " + idTipo);

	// Rellenar el input hidden
	$('#modalEditarTipoActividad input[name="idTipo"]').val(idTipo);

	var datos = new FormData();
	datos.append("idTipo", idTipo);

	$.ajax({

		url:"ajax/tipos-actividades.ajax.php",
		method: "POST",
		data: datos,
		cache: false,
		contentType: false,
		processData: false,
		dataType: "json",
		success: function(respuesta){

			console.log("Respuesta AJAX Tipo:", respuesta);

			$("#editarTipoNombre").val(respuesta["nombre"]);
			$("#editarTipoOrden").val(respuesta["orden"]);

		},
		error: function(xhr, status, error){
			console.error("Error AJAX:", error);
			console.error("Status:", status);
			console.error("Respuesta completa:", xhr.responseText);

			swal({
				type: "error",
				title: "Error al cargar el tipo",
				text: "No se pudieron cargar los datos del tipo"
			});
		}

	})

})

/*=============================================
Restaurar scroll al cerrar modal de edición de tipos
=============================================*/
$("#modalEditarTipoActividad").on("hidden.bs.modal", function(){
	// Si el modal de gestión sigue abierto, restaurar la clase modal-open
	if($("#modalGestionarTipos").hasClass("in")){
		$("body").addClass("modal-open");
	}
});

/*=============================================
Eliminar Tipo
=============================================*/
$(document).on("click", ".btnEliminarTipoActividad", function(){

	var idTipo = $(this).attr("idTipo");
	var nombreTipo = $(this).attr("nombreTipo");

	swal({
		title: '¿Está seguro de borrar el tipo "'+nombreTipo+'"?',
		text: "¡Si no lo está puede cancelar la acción!",
		type: 'warning',
		showCancelButton: true,
		confirmButtonColor: '#3085d6',
		cancelButtonColor: '#d33',
		cancelButtonText: 'Cancelar',
		confirmButtonText: 'Sí, borrar tipo!'
	}).then(function(result){

		if(result.value){

			window.location = "index.php?ruta=actividades&idTipo="+idTipo+"&nombreTipo="+nombreTipo+"&origen=actividades";

		}

	})

})