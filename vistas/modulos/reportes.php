<style>
  .excelbtn{
    z-index: 9999;
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


            <div class="box-tools pull-right excelbtn">
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

  
             <!-- Análisis de ventas -->
                <div id="contenedor-barras-formas-pago">
                  <div class="col-12 col-md-12">
                    <?php include "reportes/analisis-ventas1.php"; ?>
                  </div>
                </div>                          
                     
          </div>

         <div class="box-body">

            <div class="row">


            <div class="col-md-6 col-xs-12">        
                  <?php
                  include "reportes/tipos-pagos.php";
                  ?>
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
