/*=============================================
EDITAR Actividades
=============================================*/

$(document).ready(function() {
    console.log("El archivo JS está cargado correctamente.");
});



$(".tablas").on("click", ".btnEditarActividad", function(){
   

	var idActividad = $(this).attr("idActividad");

    console.log("ID Actividad: " + idActividad); 


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

            //console.log("Entró al success");

			$("#editarActividad").val(respuesta["descripcion"]);
            $("#editarTipo").val(respuesta["tipo"]);
            $("#editarUsuario").val(respuesta["id_user"]);
            $("#editarFecha").val(respuesta["fecha"]);
            $("#editarEstado").val(respuesta["estado"]);
            $("#editarCliente").val(respuesta["id_cliente"]);
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
$(".tablas").on("click", ".btnEliminarActividad", function(){

	var idActividad = $(this).attr("idActividad");

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

