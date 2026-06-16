<?php
if (!puedeAccion("traslados", "crear")) {
  echo '<script>
    window.location = "inicio";
  </script>';
  return;
}
?>
<div class="content-wrapper">

  <section class="content-header">
    
    <h1>
      Crear traslado entre bodegas
    </h1>

    <ol class="breadcrumb">
      <li><a href="inicio"><i class="fa fa-dashboard"></i> Inicio</a></li>
      <li class="active">Crear traslado</li>
    </ol>

  </section>

  <section class="content">

    <div class="row">

      <!--=====================================
      EL FORMULARIO
      ======================================-->
      
      <div class="col-lg-5 col-xs-12">
        
        <div class="box box-success">
          
          <div class="box-header with-border"></div>

          <form role="form" method="post" class="formularioTraslado">

            <div class="box-body">
  
              <div class="box">

                <!--=====================================
                ENTRADA DEL USUARIO
                ======================================-->
            
                <div class="form-group">
                
                  <div class="input-group">
                    
                    <span class="input-group-addon"><i class="fa fa-user"></i></span> 

                    <input type="text" class="form-control" id="nuevoVendedor" value="<?php echo $_SESSION["nombre"]; ?>" readonly>

                    <input type="hidden" name="idVendedor" value="<?php echo $_SESSION["id"]; ?>">

                  </div>

                </div> 

                <!--=====================================
                ENTRADA DEL CÓDIGO
                ======================================--> 

                <div class="form-group">
                  
                  <div class="input-group">
                    
                    <span class="input-group-addon"><i class="fa fa-key"></i></span>

                    <?php
                      $item = null;
                      $valor = null;
                      $traslados = ControladorTraslados::ctrMostrarTraslados($item, $valor);

                      if(!$traslados){
                        echo '<input type="text" class="form-control" id="nuevoCodigoTraslado" name="nuevoCodigoTraslado" value="10001" readonly>';
                      } else {
                        foreach ($traslados as $key => $value) { }
                        $codigo = $value["codigo"] + 1;
                        echo '<input type="text" class="form-control" id="nuevoCodigoTraslado" name="nuevoCodigoTraslado" value="'.$codigo.'" readonly>';
                      }
                    ?>
                    
                  </div>
                
                </div>

                <!--=====================================
                BODEGA ORIGEN
                ======================================--> 

                <div class="form-group">
                  
                  <div class="input-group">
                    
                    <span class="input-group-addon"><i class="fa fa-building"></i></span>

                    <select class="form-control" name="nuevaBodegaOrigen" id="nuevaBodegaOrigen" required>
                      <option value="">Seleccionar bodega origen</option>
                      <?php
                        $bodegas = ControladorBodegas::ctrMostrarBodegas(null, null);
                        foreach ($bodegas as $key => $value) {
                          if ($value["estado"] == 0) {
                            continue;
                          }
                          $selected = ($value["id"] == $_SESSION["id_bodega"]) ? "selected" : "";
                          echo '<option value="'.$value["id"].'" '.$selected.'>'.$value["nombre"].'</option>';
                        }
                      ?>
                    </select>

                  </div>
                
                </div>

                <!--=====================================
                BODEGA DESTINO
                ======================================--> 

                <div class="form-group">
                  
                  <div class="input-group">
                    
                    <span class="input-group-addon"><i class="fa fa-arrow-right"></i></span>

                    <select class="form-control" name="nuevaBodegaDestino" id="nuevaBodegaDestino" required>
                      <option value="">Seleccionar bodega destino</option>
                      <?php
                        foreach ($bodegas as $key => $value) {
                          if ($value["estado"] == 0) {
                            continue;
                          }
                          echo '<option value="'.$value["id"].'">'.$value["nombre"].'</option>';
                        }
                      ?>
                    </select>

                  </div>
                
                </div>

                <!--=====================================
                ENTRADA PARA AGREGAR PRODUCTO
                ======================================--> 

                <div class="form-group row nuevoProducto">
                  <!-- Aquí se agregan los productos -->
                </div>

                <input type="hidden" id="listaProductos" name="listaProductos">

                <!--=====================================
                BOTÓN PARA AGREGAR PRODUCTO (MÓVIL)
                ======================================-->

                <button type="button" class="btn btn-default hidden-lg btnAgregarProductoTraslado">Agregar producto</button>

                <hr>

                <!--=====================================
                ENTRADA DE NOTAS
                ======================================-->

                <div class="form-group">
                  <textarea class="form-control" name="nuevasNotas" placeholder="Notas adicionales sobre el traslado"></textarea>
                </div>

              </div>

          </div>

          <div class="box-footer">

            <button type="submit" class="btn btn-primary pull-right">Guardar traslado</button>

          </div>

        </form>

        <?php
          $crearTraslado = new ControladorTraslados();
          $crearTraslado -> ctrCrearTraslado();
        ?>

        </div>
            
      </div>

      <!--=====================================
      LA TABLA DE PRODUCTOS
      ======================================-->

      <div class="col-lg-7 hidden-md hidden-sm hidden-xs">
        
        <div class="box box-warning">

          <div class="box-header with-border"></div>

          <div class="box-body">
            
            <table class="table table-bordered table-striped dt-responsive tablaTraslados" width="100%">
              
               <thead>

                 <tr>
                  <th style="width: 10px">#</th>
                  <th>Imagen</th>
                  <th>Código</th>
                  <th>Descripción</th>
                  <th>Stock</th>
                  <th>Acciones</th>
                </tr>

              </thead>

            </table>

          </div>

        </div>


      </div>

    </div>
   
  </section>

</div>


