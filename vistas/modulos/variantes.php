<div class="content-wrapper">

  <section class="content-header">
    
    <h1>
      Administrar Variantes
      <small>Tipos y Opciones</small>
    </h1>

    <ol class="breadcrumb">
      <li><a href="inicio"><i class="fa fa-dashboard"></i> Inicio</a></li>
      <li class="active">Variantes</li>
    </ol>

  </section>

  <section class="content">

    <!-- =====================================
    TIPOS DE VARIANTES
    ====================================== -->
    <div class="box">
      
      <div class="box-header with-border">
        <button class="btn btn-primary btnAbrirModalTipo" data-toggle="modal" data-target="#modalAgregarTipoVariante">
        Agregar Tipo de Variante
        </button>
      </div>

      <div class="box-body">
        
        <table class="table table-bordered table-striped dt-responsive tablas" width="100%">
          
          <thead>
            <tr>
              <th style="width:10px">#</th>
              <th>Nombre</th>
              <th>Orden</th>
              <th>Estado</th>
              <th>Acciones</th>
            </tr> 
          </thead>

          <tbody>

          <?php

          $item = null;
          $valor = null;

          $tiposVariantes = ControladorVariantes::ctrMostrarTiposVariantes($item, $valor);

          foreach ($tiposVariantes as $key => $value) {
            
            echo '<tr>

                    <td>'.($key+1).'</td>

                    <td>'.$value["nombre"].'</td>

                    <td>'.$value["orden"].'</td>';

                    if($value["estado"] != 0){
                      echo '<td><button class="btn btn-success btn-xs btnActivarTipo" idTipo="'.$value["id"].'" estadoTipo="0">Activado</button></td>';

                    }else{
                      echo '<td><button class="btn btn-danger btn-xs btnActivarTipo" idTipo="'.$value["id"].'" estadoTipo="1">Desactivado</button></td>';
                    }

                    echo '<td>

                      <div class="btn-group">
                          
                       <button class="btn btn-warning btnEditarTipoVariante" idTipo="'.$value["id"].'" data-toggle="modal" data-target="#modalEditarTipoVariante"><i class="fa fa-pencil"></i></button> 

                        <button class="btn btn-info btnVerOpciones" idTipo="'.$value["id"].'" nombreTipo="'.$value["nombre"].'"><i class="fa fa-list"></i> Opciones</button> 

                        <button class="btn btn-danger btnEliminarTipo" idTipo="'.$value["id"].'" nombreTipo="'.$value["nombre"].'"><i class="fa fa-times"></i></button> 

                      </div>

                    </td>

                  </tr>';
          }

          ?>

          </tbody>

        </table>

      </div>

    </div>

    <!-- =====================================
    OPCIONES DE VARIANTES (se muestra al hacer clic en "Opciones")
    ====================================== -->
    <div class="box box-info" id="boxOpciones" style="display:none;">
      
      <div class="box-header with-border">
        <h3 class="box-title">Opciones de: <span id="nombreTipoVariante"></span></h3>
        <input type="hidden" id="idTipoVarianteActual">
        <button class="btn btn-primary pull-right" data-toggle="modal" data-target="#modalAgregarOpcion">
          Agregar Opción
        </button>
      </div>

      <div class="box-body">
        
        <table class="table table-bordered table-striped" id="tablaOpciones">
          <thead>
            <tr>
              <th style="width:10px">#</th>
              <th>Nombre</th>
              <th>Orden</th>
              <th>Estado</th>
              <th>Acciones</th>
            </tr> 
          </thead>
          <tbody id="bodyOpciones">
            <!-- Se carga dinámicamente con AJAX -->
          </tbody>
        </table>

      </div>

    </div>

  </section>

</div>

<!-- =====================================
MODAL AGREGAR TIPO DE VARIANTE
====================================== -->

<div id="modalAgregarTipoVariante" class="modal fade" role="dialog">
  
  <div class="modal-dialog">

    <div class="modal-content">

      <form role="form" method="post">

        <!-- CABEZA DEL MODAL -->
        <div class="modal-header" style="background:#3c8dbc; color:white">
          <button type="button" class="close" data-dismiss="modal">&times;</button>
          <h4 class="modal-title">Agregar Tipo de Variante</h4>
        </div>

        <!-- CUERPO DEL MODAL -->
        <div class="modal-body">

          <div class="box-body">

            <!-- ENTRADA PARA EL NOMBRE -->
            <div class="form-group">
              <div class="input-group">
                <span class="input-group-addon"><i class="fa fa-th"></i></span> 
                <input type="text" class="form-control input-lg" name="nuevoTipoVariante" placeholder="Ingresar nombre (ej: Color, Talla, Material)" required>
              </div>
            </div>

            <!-- ENTRADA PARA EL ORDEN -->
            <div class="form-group">
            <div class="input-group">
                <span class="input-group-addon"><i class="fa fa-sort-numeric-asc"></i></span> 
                <input type="number" class="form-control input-lg" id="nuevoOrdenTipo" name="nuevoOrdenTipo" placeholder="Orden de visualización" value="1" min="1" required>
            </div>
            <p class="help-block">El orden se autocompletará con el siguiente disponible</p>
            </div>
  
          </div>

        </div>

        <!-- PIE DEL MODAL -->
        <div class="modal-footer">
          <button type="button" class="btn btn-default pull-left" data-dismiss="modal">Salir</button>
          <button type="submit" class="btn btn-primary">Guardar</button>
        </div>

        <?php

          $crearTipo = new ControladorVariantes();
          $crearTipo -> ctrCrearTipoVariante();

        ?>

      </form>

    </div>

  </div>

</div>

<!-- =====================================
MODAL AGREGAR OPCIÓN
====================================== -->

<div id="modalAgregarOpcion" class="modal fade" role="dialog">
  
  <div class="modal-dialog">

    <div class="modal-content">

      <form role="form" method="post">

        <!-- CABEZA DEL MODAL -->
        <div class="modal-header" style="background:#3c8dbc; color:white">
          <button type="button" class="close" data-dismiss="modal">&times;</button>
          <h4 class="modal-title">Agregar Opción</h4>
        </div>

        <!-- CUERPO DEL MODAL -->
        <div class="modal-body">

          <div class="box-body">

            <!-- ID TIPO VARIANTE (OCULTO) -->
            <input type="hidden" name="idTipoVarianteOpcion" id="idTipoVarianteOpcion">

            <!-- ENTRADA PARA EL NOMBRE -->
            <div class="form-group">
              <div class="input-group">
                <span class="input-group-addon"><i class="fa fa-tag"></i></span> 
                <input type="text" class="form-control input-lg" name="nuevaOpcion" placeholder="Nombre de la opción (ej: Rojo, M, Algodón)" required>
              </div>
            </div>


            <!-- ENTRADA PARA EL ORDEN -->
            <div class="form-group">
            <div class="input-group">
                <span class="input-group-addon"><i class="fa fa-sort-numeric-asc"></i></span> 
                <input type="number" class="form-control input-lg" id="nuevoOrdenOpcion" name="nuevoOrdenOpcion" placeholder="Orden" value="1" min="1" required>
            </div>
            <p class="help-block">El orden se autocompletará con el siguiente disponible</p>
            </div>
  
          </div>

        </div>

        <!-- PIE DEL MODAL -->
        <div class="modal-footer">
          <button type="button" class="btn btn-default pull-left" data-dismiss="modal">Salir</button>
          <button type="submit" class="btn btn-primary">Guardar</button>
        </div>

        <?php

          $crearOpcion = new ControladorVariantes();
          $crearOpcion -> ctrCrearOpcionVariante();

        ?>

      </form>

    </div>

  </div>

</div>




<!-- =====================================
MODAL EDITAR TIPO DE VARIANTE
====================================== -->

<div id="modalEditarTipoVariante" class="modal fade" role="dialog">
  
  <div class="modal-dialog">

    <div class="modal-content">

      <form role="form" method="post">

        <!-- CABEZA DEL MODAL -->
        <div class="modal-header" style="background:#3c8dbc; color:white">
          <button type="button" class="close" data-dismiss="modal">&times;</button>
          <h4 class="modal-title">Editar Tipo de Variante</h4>
        </div>

        <!-- CUERPO DEL MODAL -->
        <div class="modal-body">

          <div class="box-body">

            <!-- ENTRADA PARA EL NOMBRE -->
            <div class="form-group">
              <div class="input-group">
                <span class="input-group-addon"><i class="fa fa-th"></i></span> 
                <input type="text" class="form-control input-lg" id="editarTipoVariante" name="editarTipoVariante" required>
                <input type="hidden" id="idTipo" name="idTipo">
              </div>
            </div>

            <!-- ENTRADA PARA EL ORDEN -->
            <div class="form-group">
              <div class="input-group">
                <span class="input-group-addon"><i class="fa fa-sort-numeric-asc"></i></span> 
                <input type="number" class="form-control input-lg" id="editarOrdenTipo" name="editarOrdenTipo" min="1" required>
              </div>
            </div>
  
          </div>

        </div>

        <!-- PIE DEL MODAL -->
        <div class="modal-footer">
          <button type="button" class="btn btn-default pull-left" data-dismiss="modal">Salir</button>
          <button type="submit" class="btn btn-primary">Guardar cambios</button>
        </div>

        <?php

          $editarTipo = new ControladorVariantes();
          $editarTipo -> ctrEditarTipoVariante();

        ?>

      </form>

    </div>

  </div>

</div>


<!-- =====================================
MODAL EDITAR OPCIÓN
====================================== -->

<div id="modalEditarOpcion" class="modal fade" role="dialog">
  
  <div class="modal-dialog">

    <div class="modal-content">

      <form role="form" method="post">

        <!-- CABEZA DEL MODAL -->
        <div class="modal-header" style="background:#3c8dbc; color:white">
          <button type="button" class="close" data-dismiss="modal">&times;</button>
          <h4 class="modal-title">Editar Opción</h4>
        </div>

        <!-- CUERPO DEL MODAL -->
        <div class="modal-body">

          <div class="box-body">

            <!-- ID OPCION (OCULTO) -->
            <input type="hidden" id="idOpcion" name="idOpcion">

            <!-- ENTRADA PARA EL NOMBRE -->
            <div class="form-group">
              <div class="input-group">
                <span class="input-group-addon"><i class="fa fa-tag"></i></span> 
                <input type="text" class="form-control input-lg" id="editarOpcion" name="editarOpcion" required>
              </div>
            </div>

            <!-- ENTRADA PARA EL ORDEN -->
            <div class="form-group">
              <div class="input-group">
                <span class="input-group-addon"><i class="fa fa-sort-numeric-asc"></i></span> 
                <input type="number" class="form-control input-lg" id="editarOrdenOpcion" name="editarOrdenOpcion" min="1" required>
              </div>
            </div>
  
          </div>

        </div>

        <!-- PIE DEL MODAL -->
        <div class="modal-footer">
          <button type="button" class="btn btn-default pull-left" data-dismiss="modal">Salir</button>
          <button type="submit" class="btn btn-primary">Guardar cambios</button>
        </div>

        <?php

          $editarOpcion = new ControladorVariantes();
          $editarOpcion -> ctrEditarOpcionVariante();

        ?>

      </form>

    </div>

  </div>

</div>