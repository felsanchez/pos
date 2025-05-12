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
		//window.location = "ventas";
    window.location = "index.php?ruta=" + getRutaActual();
	})


   /*=============================================
	Filtro tipos de pagos
	=============================================*/
 document.getElementById("form-filtro-tipos-pago").addEventListener("submit", function (e) {
  e.preventDefault();

  const form = e.target;
  const formData = new FormData(form);

  fetch("vistas/modulos/reportes/filtro_tipos_pago.php", {
    method: "POST",
    body: formData
  })
    .then(res => res.json())
    .then(data => {
      actualizarGraficoTiposPago(data);
    })
    .catch(err => {
      console.error("Error al cargar los datos de tipos de pago:", err);
    });
});

function actualizarGraficoTiposPago(datosMetodoPago) {
  const contenedor = document.getElementById("contenedor-tipos-pago");
  contenedor.innerHTML = ""; // Limpia contenido anterior

  let maxTotal = 1;
  for (let key in datosMetodoPago) {
    if (datosMetodoPago[key] > maxTotal) {
      maxTotal = datosMetodoPago[key];
    }
  }

  const colores = {
    "Efectivo": "primary",
    "TD": "info",
    "TC": "success",
    "Transferencia": "warning",
    "Cheque": "danger"
  };

  for (let metodo in datosMetodoPago) {
    const total = datosMetodoPago[metodo];
    const porcentaje = Math.round((total / maxTotal) * 100);
    const nombre = metodo === "TD" ? "Tarjeta Débito" :
                   metodo === "TC" ? "Tarjeta Crédito" : metodo;

    contenedor.innerHTML += `
      <div class="progress-group">
        <span class="progress-text">${nombre}</span>
        <span class="float-end"><b>${total.toLocaleString()}</b>/${maxTotal.toLocaleString()}</span>
        <div class="progress progress-sm">
          <div class="progress-bar text-bg-${colores[metodo] || 'secondary'}" style="width: ${porcentaje}%"></div>
        </div>
      </div>
    `;
  }
}
