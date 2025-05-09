/*=============================================
VARIABLE LOCAL STORAGE
=============================================*/
if(localStorage.getItem("capturarRango") != null){

	$("#daterange-btn span").html(localStorage.getItem("capturarRango"));
}
else{
	$("#daterange-btn span").html('<i class="fa fa-calendar"></i>Rango de fecha')	
}


/*=============================================  
CARGAR TABLA DINAMICA
=============================================*/  

var table2 = $(".tablaVentas").DataTable({

	"ajax":"ajax/datatable-ventas.ajax.php",
	"columnDefs": [


	  {
			"targets": -5,
			"data": null,
			"defaultContent": '<img class="img-thumbnail imgTablaVenta" width="40px">'
	  },


	  {
			"targets": -2,
			"data": null,
			"defaultContent": '<div class="btn-group"><button class="btn btn-success limiteStock"></button></div>'
	  },

	  {
			"targets": -1,
			"data": null,
			"defaultContent": '<div class="btn-group"><button class="btn btn-primary agregarProducto recuperarBoton" idProducto>Agregar</button></div>'
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

$('.tablaVentas tbody').on( 'click', 'button.agregarProducto', function () {

	var data = table2.row( $(this).parents('tr') ).data();

	$(this).attr("idProducto", data[5]);
})


/*=============================================
FUNCION PARA CARGAR CON EL PAGINADOR Y CON EL FILTRO
=============================================*/

function cargarImagenesProductos(){

	var imgTabla = $(".imgTablaVenta");

	var limiteStock = $(".limiteStock");

	for(var i = 0; i < imgTabla.length; i ++){

		var data = table2.row( $(imgTabla[i]).parents("tr")).data();

		$(imgTabla[i]).attr("src", data[1]);


		if(data[4] <= 10){

			$(limiteStock[i]).addClass("btn-danger");
			$(limiteStock[i]).html(data[4]);
		}
		else if(data[4] >= 11 && data[4] <= 15){

			$(limiteStock[i]).addClass("btn-warning");
			$(limiteStock[i]).html(data[4]);
		}
		else{
			$(limiteStock[i]).addClass("btn-success");
			$(limiteStock[i]).html(data[4]);
		}

	}

}


setTimeout(function(){

	cargarImagenesProductos();

},300)


//CARGAMOS LAS IMAGENES CUANDO INTERACTUAMOS CON  EL FILTRO DE CANTIDAD
$("select[name='DataTables_Table_0_length']").change(function(){

	cargarImagenesProductos();
})


//CARGAMOS LAS IMAGENES CUANDO INTERACTUAMOS CON  EL PAGINADOR 
$(".dataTables_paginate").click(function(){

	cargarImagenesProductos();
})


//CARGAMOS LAS IMAGENES CUANDO INTERACTUAMOS CON  EL BUSCADOR
$("input[aria-controls='DataTables_Table_0']").focus(function(){

	$(document).keyup(function(event){

		event.preventDefault();

		cargarImagenesProductos();
	})
})


//CARGAMOS LAS IMAGENES CUANDO INTERACTUAMOS CON  EL FILTRO DE ORDENADOR 
$(".sorting").click(function(){

	cargarImagenesProductos();
})


/*=============================================
AGREGANDO PRODUCTOS A LA VENTA DESDE A TABLA
=============================================*/
$('.tablaVentas tbody').on("click", "button.agregarProducto", function () {

	var idProducto = $(this).attr("idProducto");

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
			var precio = respuesta["precio_venta"];

			$(".nuevoProducto").append(

			'<div class="row" style="padding:5px 15px">'+

			'<!--Descripcion del producto-->'+
                          
                '<div class="col-xs-6" style="padding-right:0px">'+ 

                	'<div class="input-group">'+

               			 '<span class="input-group-addon"><button type="button" class="btn btn-danger btn-xs quitarProducto" idProducto="'+idProducto+'"><i class="fa fa-times"></i></button></span>'+

               			 '<input type="text" class="form-control nuevaDescripcionProducto" idProducto="'+idProducto+'" name="agregarProducto" value="'+descripcion+'" readonly required>'+
                              
              		'</div>'+

                '</div>'+ 

                      
                '<!--Cantidad del producto-->'+

                '<div class="col-xs-3">'+
                           
                    '<input type="number" class="form-control nuevaCantidadProducto" name="nuevaCantidadProducto" min="1" value="1" stock="'+stock+'" nuevoStock="'+Number(stock-1)+'" required>'+

                '</div>'+

                '<!--Precio del producto-->'+

                '<div class="col-xs-3 ingresoPrecio" style="padding-left:0px">'+
                            
                    '<div class="input-group">'+

                        //'<span class="input-group-addon"><i class="ion ion-social-usd"></i></span>'+
                              
                        '<input type="text" class="form-control nuevoPrecioProducto" precioReal="'+precio+'" name="nuevoPrecioProducto" value="'+precio+'" readonly required>'+

                     '</div>'+

                '</div>'+

            '</div>')

			//Sumar total de precios
            sumarTotalPrecios()

            //Agregar impuesto
            agregarImpuesto()

            //Agrupar productos en formato Json
            listarProductos()

            //Poner formato number al precio de los productos
            $(".nuevoPrecioProducto").number(true,2);
		}

	})		


});


/*=============================================
QUITAR PRODUCTOS DE LA VENTA Y RECUPERAR BOTON
=============================================*/

$(".formularioVenta").on("click", "button.quitarProducto", function(){

	$(this).parent().parent().parent().parent().remove();

	var idProducto = $(this).attr("idProducto");

	$("button.recuperarBoton[idProducto='"+idProducto+"']").removeClass('btn-default');

	$("button.recuperarBoton[idProducto='"+idProducto+"']").addClass('btn-primary agregarProducto');


	if($(".nuevoProducto").children().length == 0){

		$("#nuevoImpuestoVenta").val(0);
		$("#nuevoTotalVenta").val(0);
		$("#totalVenta").val(0);
		$("#nuevoTotalVenta").attr("total",0);
	}
	else{
		//Sumar total de precios
   		 sumarTotalPrecios()

   		 //Agregar impuesto
         agregarImpuesto()

         //Agrupar productos en formato Json
         listarProductos()
	}
	
})


/*==========================================================================================
AGREGANDO PRODUCTO DESDE EL BOTON PARA DISPOSITIVOS
==========================================================================================*/

$(".btnAgregarProducto").click(function(){

	var datos = new FormData();
	datos.append("traerProductos", "ok");

	$.ajax({

		url:"ajax/productos.ajax.php",
		method: "POST",
		data: datos,
		cache: false,
		contentType: false,
		processData: false,
		dataType:"json",
		success:function(respuesta){
			

			$(".nuevoProducto").append(

			'<div class="row" style="padding:5px 15px">'+

			'<!--Descripcion del producto-->'+
                          
                '<div class="col-xs-6" style="padding-right:0px">'+ 

                	'<div class="input-group">'+

               			 '<span class="input-group-addon"><button type="button" class="btn btn-danger btn-xs quitarProducto" idProducto><i class="fa fa-times"></i></button></span>'+

               			 '<select class="form-control nuevaDescripcionProducto" idProducto name="nuevaDescripcionProducto" required>'+

               				 '<option>Seleccione el producto</option>'+

               			 '</select>'+
                              
              		'</div>'+

                '</div>'+ 

                      
                '<!--Cantidad del producto-->'+

                '<div class="col-xs-3 ingresoCantidad">'+
                           
                    '<input type="number" class="form-control nuevaCantidadProducto" name="nuevaCantidadProducto" min="1" value="1" stock nuevoStock required>'+

                '</div>'+

                '<!--Precio del producto-->'+

                '<div class="col-xs-3 ingresoPrecio" style="padding-left:0px">'+
                            
                    '<div class="input-group">'+

                        '<span class="input-group-addon"><i class="ion ion-social-usd"></i></span>'+
                              
                        '<input type="text" class="form-control nuevoPrecioProducto" precioReal="" name="nuevoPrecioProducto" readonly required>'+

                     '</div>'+

                '</div>'+

            '</div>');


            /*=============================================
			AGREGAR LOS PRODUCTOS AL SELECT
			=============================================*/

			respuesta.forEach(funcionForEach);

			function funcionForEach(item, index){

				$(".nuevaDescripcionProducto").append(

					'<option idProducto="'+item.id+'" value="'+item.descripcion+'">'+item.descripcion+'</option>' )
			}

			//Sumar total de precios
      		//sumarTotalPrecios()

      	    //Agregar impuesto
            agregarImpuesto()

            //Poner formato number al precio de los productos
            $(".nuevoPrecioProducto").number(true,2);
            

		}

	})

})


/*=============================================
SELECCIONAR PRODUCTOS (dispositivos)
=============================================*/

$(".formularioVenta").on("change", "select.nuevaDescripcionProducto", function(){

	var nombreProducto = $(this).val();
	
	var nuevoPrecioProducto = $(this).parent().parent().parent().children(".ingresoPrecio").children().children(".nuevoPrecioProducto");

	var nuevaCantidadProducto = $(this).parent().parent().parent().children(".ingresoCantidad").children(".nuevaCantidadProducto");

	var datos = new FormData();
	datos.append("nombreProducto", nombreProducto);

	$.ajax({

		url:"ajax/productos.ajax.php",
		method: "POST",
		data: datos,
		cache: false,
		contentType: false,
		processData: false,
		dataType:"json",
		success:function(respuesta){
			
			$(nuevaCantidadProducto).attr("stock", respuesta["stock"]);
			$(nuevaCantidadProducto).attr("nuevoStock", Number(respuesta["stock"])-1);
			$(nuevoPrecioProducto).val(respuesta["precio_venta"]);
			$(nuevoPrecioProducto).attr("precioReal", respuesta["precio_venta"]);

		    //Agrupar productos en formato Json
            listarProductos()

			//Sumar total de precios
			sumarTotalPrecios()

		}

	})

})


/*=============================================
MODIFICAR LA CANTIDAD
=============================================*/

$(".formularioVenta").on("change", "input.nuevaCantidadProducto", function(){

	var precio = $(this).parent().parent().children(".ingresoPrecio").children().children(".nuevoPrecioProducto");

	var precioFinal = $(this).val() * precio.attr("precioReal");

	precio.val(precioFinal);


	var nuevoStock = Number($(this).attr("stock")) - $(this).val();

	$(this).attr("nuevoStock", nuevoStock);

	if(Number($(this).val()) > Number($(this).attr("stock"))){

		$(this).val(0);

		swal({
			title: "La cantidad supera el Stock",
			text: "¡Solo hay "+$(this).attr("stock")+" unidades!",
			type: "error",
			confirmButtonText: "¡Cerrar!"
		});

		//$("#nuevaCantidadProducto").val(2);

	}

	//Sumar total de precios
    sumarTotalPrecios()

    //Agregar impuesto
    agregarImpuesto()

    //Agrupar productos en formato Json
    listarProductos()

})


/*=============================================
SUMAR TODOS LOS PRECIOS
=============================================*/

function sumarTotalPrecios(){

	var precioItem = $(".nuevoPrecioProducto");
	var arraySumaPrecio = [];

	for(var i = 0; i < precioItem.length; i++){

		arraySumaPrecio.push(Number($(precioItem[i]).val()));
		
	}

	function sumaArrayPrecios(total, numero){

		return total + numero;
	}

	var sumaTotalPrecio = arraySumaPrecio.reduce(sumaArrayPrecios);
	
	$("#nuevoTotalVenta").val(sumaTotalPrecio);
	$("#totalVenta").val(sumaTotalPrecio);
	$("#nuevoTotalVenta").attr("total", sumaTotalPrecio);
}


/*=============================================
FUNCION AGREGAR IMPUESTO
=============================================*/

function agregarImpuesto(){

	var impuesto = $("#nuevoImpuestoVenta").val();
	var precioTotal = $("#nuevoTotalVenta").attr("total");

	var precioImpuesto = Number(precioTotal * impuesto/100);

	var totalConImpuesto = Number(precioImpuesto) + Number(precioTotal);

	$("#nuevoTotalVenta").val(totalConImpuesto);

	$("#totalVenta").val(totalConImpuesto);

	$("#nuevoPrecioImpuesto").val(precioImpuesto);

	$("#nuevoPrecioNeto").val(precioTotal);
}


/*=============================================
CUANDO CAMBIA EL IMPUESTO
=============================================*/
$("#nuevoImpuestoVenta").change(function(){

	agregarImpuesto();
})


//Poner formato number al precio final
$("#nuevoTotalVenta").number(true,2);


/*=============================================
SELECCIONAR METODO DE PAGO
=============================================*/

$("#nuevoMetodoPago").change(function(){

	var metodo = $(this).val();

	if(metodo =="Efectivo"){

		$(this).parent().parent().removeClass("col-xs-6");

		$(this).parent().parent().addClass("col-xs-4");

		$(this).parent().parent().parent().children(".cajasMetodoPago").html(

			'<div class="col-xs-4">'+

				'<div class="input-group">'+

					'<span class="input-group-addon"><i class="ion ion-social-usd"></i></span>'+

					'<input type="text" class="form-control" id="nuevoValorEfectivo" placeholder="00000" required>'+

				'</div>'+

			'</div>'+

			'<div class="col-xs-4" id="capturarCambioEfectivo" style="padding-left:0px">'+

				'<div class="input-group">'+

					'<span class="input-group-addon"><i class="ion ion-social-usd"></i></span>'+

					'<input type="text" class="form-control" id="nuevoCambioEfectivo" name="nuevoCambioEfectivo" placeholder="00000" readonly required>'+

				'</div>'+			

			'</div>'
		)

		//Agregar formato number al precio 
		$("#nuevoValorEfectivo").number(true,2);
		$("#nuevoCambioEfectivo").number(true,2);

		//Listar metodo en la entrada
		listarMetodos()
	}


	//Hecho por mi else if
	else if(metodo ==""){

		$(this).parent().parent().removeClass("col-xs-4");

		$(this).parent().parent().addClass("col-xs-6");

		$(this).parent().parent().parent().children(".cajasMetodoPago").html("")

	}

	else{

		$(this).parent().parent().removeClass("col-xs-4");

		$(this).parent().parent().addClass("col-xs-6");

		$(this).parent().parent().parent().children(".cajasMetodoPago").html(

			'<div class="col-xs-6" style="padding-left:0px">'+
                            
                '<div class="input-group">'+

                    '<input type="text" class="form-control" id="nuevoCodigoTransaccion" name="nuevoCodigoTransaccion" placeholder="Código transacción" required>'+

                    '<span class="input-group-addon"><i class="fa fa-lock"></i></span>'+                             

                '</div>'+

            '</div>'
		)

	}

})


/*=============================================
CAMBIO EN EFECTIVO
=============================================*/

$(".formularioVenta").on("change", "input#nuevoValorEfectivo", function(){

	var efectivo = $(this).val();

	var cambio = Number(efectivo) - Number($('#nuevoTotalVenta').val());

	var nuevoCambioEfectivo = $(this).parent().parent().parent().children('#capturarCambioEfectivo').children().children('#nuevoCambioEfectivo');

	nuevoCambioEfectivo.val(cambio);

})


/*=============================================
CAMBIO TRANSACCION
=============================================*/

$(".formularioVenta").on("change", "input#nuevoCodigoTransaccion", function(){

	//Listar metodo en la entrada
	listarMetodos()

})


/*=============================================
AGRUPAR TODOS LOS PRODUCTOS
=============================================*/

function listarProductos(){

	var listaProductos = [];

	var descripcion = $(".nuevaDescripcionProducto");

	var cantidad = $(".nuevaCantidadProducto");  

	var precio = $(".nuevoPrecioProducto");

	for(var i = 0; i < descripcion.length; i++){


		var idProducto = "";

        // Detectar si el campo es un <select> (crear) o un <input> (editar)
        if($(descripcion[i]).is("select") || $(descripcion[i]).prop("tagName").toLowerCase() === "select") {
            idProducto = $(descripcion[i]).find("option:selected").attr("idProducto");
        } else {
            idProducto = $(descripcion[i]).attr("idProducto");
        }

		listaProductos.push({ 
			
							//"id" : $(descripcion[i]).find("option:selected").attr("idProducto"),
							//"id" : $(descripcion[i]).attr("idProducto"),

								"id" : idProducto,
							  "descripcion" : $(descripcion[i]).val(),
							  "cantidad" : $(cantidad[i]).val(),
							  "stock" : $(cantidad[i]).attr("nuevoStock"),
							  "precio" : $(precio[i]).attr("precioReal"),
							  "total" : $(precio[i]).val()})
	}

		$("#listaProductos").val(JSON.stringify(listaProductos));

}

$(".formularioVenta").submit(function(e){
    listarProductos();
});


/*=============================================
LISTAR METODO DE PAGO
=============================================*/

function listarMetodos(){

	var listarMetodos = "";

	if($("#nuevoMetodoPago").val() == "Efectivo"){

		$("#listaMetodoPago").val("Efectivo");
	}

	else{

		$("#listaMetodoPago").val($("#nuevoMetodoPago").val()+"-"+$("#nuevoCodigoTransaccion").val());
	}	


}


/*=============================================
BOTON EDITAR VENTA
=============================================*/

$(".tablas").on("click", ".btnEditarVenta", function(){

	var idVenta = $(this).attr("idVenta");

	window.location = "index.php?ruta=editar-venta&idVenta="+idVenta;
})


/*=============================================
BORRAR VENTA
=============================================*/

$(".tablas").on("click", ".btnEliminarVenta", function(){

	var idVenta = $(this).attr("idVenta");
	
	swal({

		title: '¿Esta seguro de borrar la venta?',
		text: "¡Si no lo está puede cancelar la acción!",
		type: 'warning',
		showCancelButton: true,
		confirmButtonColor: '#3085d6',
		cancelButtonColor: '#d33',
		cancelButtonText: 'Cancelar',
		confirmButtonText: 'Si, borrar venta!'
	}).then((result)=>{

		if(result.value){

			//window.location = "index.php?ruta=ventas&idVenta="+idVenta;

			// Primero intentar con el parámetro 'ruta'
			let ruta = new URLSearchParams(window.location.search).get('ruta');

			// Si no existe 'ruta', obtener el nombre del archivo
			if (!ruta) {
				const path = window.location.pathname;
				const archivo = path.substring(path.lastIndexOf("/") + 1);
				ruta = archivo.split(".php")[0]; // ejemplo: ordenes.php -> ordenes
			}

			let url = "index.php?ruta=" + ruta + "&idVenta=" + idVenta;

			if (ruta === "ordenes") {
				url += "&estado=orden";
			}

			window.location = url;


		}

	})


})


/*=============================================
IMPRIMIR FACTURA
=============================================*/

$(".tablas").on("click", ".btnImprimirFactura", function(){

	var codigoVenta = $(this).attr("codigoVenta");

	window.open("extensiones/tcpdf/pdf/factura.php?codigo="+codigoVenta, "_blank");
})


/*=============================================
RANGO DE FECHAS
=============================================*/
    $('#daterange-btn').daterangepicker(
      {
        ranges   : {
          '': [moment().subtract(29, 'days'), moment()],
          'Hoy'       : [moment(), moment()],
          'Ayer'   : [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
          'Últimos 7 días' : [moment().subtract(6, 'days'), moment()],
          //'Últimos 30 días': [moment().subtract(29, 'days'), moment()],
          'Este mes'  : [moment().startOf('month'), moment().endOf('month')],
          'Mes pasado'  : [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')]
        },
        startDate: moment().subtract(29, 'days'),
        endDate  : moment()
      },
      function (start, end) {
        $('#daterange-btn span').html(start.format('MMMM D, YYYY') + ' - ' + end.format('MMMM D, YYYY'));

        //var fechaInicial = start.format('YYYY-MM-DD');

        //var fechaFinal = end.format('YYYY-MM-DD');

		var fechaInicial = start.startOf('day').format('YYYY-MM-DD HH:mm:ss');
		var fechaFinal = end.endOf('day').format('YYYY-MM-DD HH:mm:ss');

        var capturarRango = $("#daterange-btn span").html();
        
        localStorage.setItem("capturarRango", capturarRango);


		 // 1. Intentar obtener 'ruta' desde los parámetros
		 const urlParams = new URLSearchParams(window.location.search);
		 let ruta = urlParams.get('ruta');
	 
		 // 2. Si no existe 'ruta', deducirlo desde el pathname
		 if (!ruta) {
		   const path = window.location.pathname;
		   const archivo = path.substring(path.lastIndexOf('/') + 1).replace('.php', '');
		   ruta = archivo; // Por ejemplo: "orden" si estás en orden.php
		 }
	 
		 window.location = "index.php?ruta=" + ruta + "&fechaInicial=" + fechaInicial + "&fechaFinal=" + fechaFinal;


      }
    )

    /*=============================================
	CANCELAR RANGO DE FECHAS
	=============================================*/

	/*$(".daterangepicker.opensleft .range_inputs .cancelBtn").on("click", function(){

		localStorage.removeItem("capturarRango");
		localStorage.clear();
		//window.location = "ventas";
		window.location = "index.php?ruta=" + getRutaActual();
	})*/

	$(".daterangepicker.opensright .range_inputs .cancelBtn").on("click", function(){

		localStorage.removeItem("capturarRango");
		localStorage.clear();
		window.location = "ventas";
    	window.location = "index.php?ruta=" + getRutaActual();
	})


/*=============================================
CAPTURAR HOY este bloque no sirvio
=============================================*/

/*$(".daterangepicker.opensleft .ranges li").on("click", function(){

	var textoHoy = $(this).attr("data-range-key");

	if(textoHoy == "Hoy"){

		var d = new Date();
		
		var dia = d.getDate();
		var mes = d.getMonth()+1;
		var año = d.getFullYear();

		var fechaInicial = año+"-"+mes+"-"+dia;
		var fechaFinal = año+"-"+mes+"-"+dia;	

    	localStorage.setItem("capturarRango", "Hoy");

    	window.location = "index.php?ruta=ventas&fechaInicial="+fechaInicial+"&fechaFinal="+fechaFinal;
	}
})*/


/*=============================================
ABRIR ARCHIVO XML EN NUEVA PESTAÑA
=============================================*/

$(".abrirXML").click(function(){

	var archivo = $(this).attr("archivo");
	window.open(archivo, "_blank");

})


/*HPM Boton de cancelar en ventas 
$(".btnCancelarVenta").click(function(){

	window.location = "index.php?ruta=ventas";
}*/


//FILTRO DE VENTAS
/*
let minDate, maxDate; 
// Custom filtering function which will search data in column four between two values
DataTable.ext.search.push(function (settings, data, dataIndex) {
    let min = minDate.val();
    let max = maxDate.val();
    let date = new Date(data[4]);
 
    if (
        (min === null && max === null) ||
        (min === null && date <= max) ||
        (min <= date && max === null) ||
        (min <= date && date <= max)
    ) {
        return true;
    }
    return false;
});
 
// Create date inputs
minDate = new DateTime('#min', {
    format: 'MMMM Do YYYY'
});
maxDate = new DateTime('#max', {
    format: 'MMMM Do YYYY'
});
 
// DataTables initialisation
let table = new DataTable('#example');
 
// Refilter the table
document.querySelectorAll('#min, #max').forEach((el) => {
    el.addEventListener('change', () => table.draw());
});
*/