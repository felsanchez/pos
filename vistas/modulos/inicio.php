 <div class="content-wrapper">
    <section class="content-header">

      <h1>
        Tablero
        <small>Panel de Control</small>
      </h1>

      <ol class="breadcrumb">

        <li><a href="salir"><i class="fa fa-dashboard"></i> Salir</a></li>

        <li class="active">Tablero</li>

      </ol>
      
    </section>

    <section class="content">

      <div class="row">
        
        <?php

          //if($_SESSION["perfil"] =="Administrador"){

            include "inicio/cajas-superiores.php";

          //}

        ?>

      </div>


       <div class="row">

        <div class="col-lg-12">
        
          <?php
              if($_SESSION["perfil"] =="Administrador" || $_SESSION["perfil"] =="Vendedor"){
            ?>
              <!--include "reportes/grafico-ventas.php";-->
              <div id="contenedor-barras-formas-pago">
                  <div class="col-12 col-md-12">
                    <?php include "reportes/analisis-ventas1.php"; ?>
                  </div>
                </div> 
          <?php
                }
            ?>        

        </div>


        <div class="col-lg-6">
        
          <?php

            if($_SESSION["perfil"] =="Administrador" || $_SESSION["perfil"] =="Especial"){

              include "reportes/productos-mas-vendidos.php";

            }

          ?>

        </div>


        <div class="col-lg-6">
        
          <?php

            if($_SESSION["perfil"] =="Administrador" || $_SESSION["perfil"] =="Especial"){

              include "inicio/productos-recientes.php";

            }

          ?>

        </div>


        <div class="col-lg-12">
          
          <?php

            if($_SESSION["perfil"] =="Especial" || $_SESSION["perfil"] =="Vendedor"){

               echo '<div class="box box-success">

               <div class="box-header">

               <h1>Bienvenid@ ' .$_SESSION["nombre"].'</h1>

               </div>

               </div>';
            }

          ?>

        </div>


      </div>

    </section>

  </div>
