<!--Estilo Filtro de fechas -->
  <style>
    .formulario-fechas-container {
      max-width: 300px;
      padding: 15px;
      border-radius: 10px;
      background-color: #ffffff;
      box-shadow: 0 2px 8px rgba(0,0,0,0.1);
      margin-bottom: 20px;
    }
    .formulario-fechas label {
      font-weight: 600;
      margin-top: 10px;
    }
    .formulario-fechas select,
    .formulario-fechas input[type="date"] {
      border-radius: 8px;
      margin-bottom: 10px;
    }
    .d-none {
      display: none !important;
    }
  </style>  
  

  <div class="content-wrapper">
    <section class="content-header">

      <h1>
        Reportes de ventas
      </h1>

      <ol class="breadcrumb">
        <li><a href="inicio"><i class="fa fa-dashboard"></i> Inicio</a></li>
        <li class="active">Reportes de ventas</li>
      </ol>

    </section>

    <section class="content">

       <div class="box">

          <div class="box-header with-border">


            <div class="box-tools pull-right">

              <?php
                if(isset($_GET["fechaInicial"])){

                  echo '<a href="vistas/modulos/descargar-reporte.php?reporte=reporte&fechaInicial='.$_GET["fechaInicial"].'&fechaFinal='.$_GET["fechaFinal"].'">';
                }
                else{

                    echo '<a href="vistas/modulos/descargar-reporte.php?reporte=reporte">';
                  }  
              ?>
              
                <button class="btn btn-success" style="margin-top:5px">Descargar reporte en excel</button>

              </a>

            </div>
                     
          </div>

         <div class="box-body">

            <div class="row">
              
              <div class="col-xs-12">
              <?php
                  include "reportes/grafico-ventas.php";
                ?>              
              </div>

              
             <!-- HPM1 Filtro de fechas -->
             <div class="formulario-fechas-container">
              <form id="filtro-fechas" class="formulario-fechas">
                <label for="tipo-fecha">Filtrar por</label>
                <select id="tipo-fecha" name="tipo" class="form-control">
                  <option value="hoy">Hoy</option>
                  <option value="ayer">Ayer</option>
                  <option value="mes">Mes actual</option>
                  <option value="personalizado">Personalizado</option>
                </select>

                <div id="campo-desde" class="form-group d-none">
                  <label for="fecha-desde">Desde</label>
                  <input type="date" id="fecha-desde" name="fecha_inicio" class="form-control">
                </div>

                <div id="campo-hasta" class="form-group d-none">
                  <label for="fecha-hasta">Hasta</label>
                  <input type="date" id="fecha-hasta" name="fecha_fin" class="form-control">
                </div>

                <button type="submit" class="btn btn-primary w-100 mt-2">Aplicar filtro</button>
              </form>
            </div>

            <!-- Análisis de ventas -->
          <div id="contenedor-barras-formas-pago">
            <div class="col-12 col-md-12">
              <?php include "reportes/analisis-ventas1.php"; ?>
            </div>
          </div>


              <div class="col-md-6 col-xs-12">      
                  <?php
                  include "reportes/productos-mas-vendidos.php";
                  ?>
              </div>

              <div class="col-md-6 col-xs-12">        
                  <?php
                  include "reportes/vendedores.php";
                  ?>
              </div>

               <div class="col-md-6 col-xs-12">             
                  <?php
                  include "reportes/compradores.php";
                  ?>
              </div>

            </div>
         
         </div>
       
       </div>


    </section>

  </div>


  

