/*=============================================
VARIABLE LOCAL STORAGE
=============================================*/
if(localStorage.getItem("capturarRango2") != null){

	$("#daterange-btn2 span").html(localStorage.getItem("capturarRango2"));
}
else{
	$("#daterange-btn2 span").html('<i class="fa fa-calendar"></i>Rango de fecha')	
}


/*=============================================
RANGO DE FECHAS
=============================================*/
    $('#daterange-btn2').daterangepicker(
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
        $('#daterange-btn2 span').html(start.format('MMMM D, YYYY') + ' - ' + end.format('MMMM D, YYYY'));

        var fechaInicial = start.format('YYYY-MM-DD');

        var fechaFinal = end.format('YYYY-MM-DD');

        var capturarRango = $("#daterange-btn2 span").html();
        
        localStorage.setItem("capturarRango2", capturarRango);

        window.location = "index.php?ruta=reportes&fechaInicial="+fechaInicial+"&fechaFinal="+fechaFinal;
      }
    )

    /*=============================================
	CANCELAR RANGO DE FECHAS
	=============================================*/

	$(".daterangepicker.opensright .range_inputs .cancelBtn").on("click", function(){

		localStorage.removeItem("capturarRango2");
		localStorage.clear();
		window.location = "reportes";
	})