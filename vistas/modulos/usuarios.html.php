  <div class="content-wrapper">
    <section class="content-header">

      <h1>
        Administrar usuarios
      </h1>

      <ol class="breadcrumb">
        <li><a href="inicio"><i class="fa fa-dashboard"></i> Inicio</a></li>
        <li class="active">Administrar usuarios</li>
      </ol>

    </section>

    <section class="content">

      <div class="box">

        <div class="box-header with-border">

          <button class="btn btn-primary" data-toggle="modal" data-target="#modalAgregarUsuario">
            
             Agregar usuario

          </button>

        </div>


        <div class="box-body">

          <table class="table table-bordered table-striped tablas">
              
            <thead>
              <tr>
                <th style="width: 10px">#</th>
                <th>Nombre</th>
                <th>Usuario</th>
                <th>Foto</th>
                <th>Perfil</th>
                <th>Estado</th>
                <th>Ultimo login</th>
                <th>Acciones</th>
              </tr>             
            </thead>

              <tbody>
                <tr>
                  <td>1</td>
                  <td>usuario administrador</td>
                  <td>admin</td>
                  <td><img src="vistas/img/usuarios/default/anonymous.png" class="img-thumbnail" width="40px"></td>
                  <td>Administrador</td>
                  <td><button class="btn btn-success btn-xs">Activado</button></td>
                  <td>2018-11-24 06:04:30</td>

                  <td>
                    <div class="btn-group">
                      <button class="btn btn-warning"><i class="fa fa-pencil"></i></button>
                      <button class="btn btn-danger"><i class="fa fa-times"></i></button>                    
                    </div>
                  </td>

                </tr>


                <!--Otra fila borrar-->
                <tr>
                  <td>1</td>
                  <td>usuario administrador</td>
                  <td>admin</td>
                  <td><img src="vistas/img/usuarios/default/anonymous.png" class="img-thumbnail" width="40px"></td>
                  <td>Administrador</td>
                  <td><button class="btn btn-danger btn-xs">Desactivado</button></td>
                  <td>2018-11-24 06:04:30</td>

                  <td>
                    <div class="btn-group">
                      <button class="btn btn-warning"><i class="fa fa-pencil"></i></button>
                      <button class="btn btn-danger"><i class="fa fa-times"></i></button>                    
                    </div>
                  </td>
                </tr>


              </tbody>

          </table>

        </div>

      </div>

    </section>

  </div>


<!--=====================================
MODAL AGREGAR USUARIO
======================================-->
  
<!-- Modal -->
<div id="modalAgregarUsuario" class="modal fade" role="dialog">

  <div class="modal-dialog">

    <div class="modal-content">

      <form role="form" method="post" enctype="multipart/form-data">

      <!--=====================================
      CABEZA DEL MODAL
      ======================================-->

      <div class="modal-header" style="background:#3c8dbc; color: white">

        <button type="button" class="close" data-dismiss="modal">&times;</button>
        <h4 class="modal-title">Agregar usuario</h4>

      </div>

      <!--=====================================
      CUERPO DEL MODAL
      ======================================-->

      <div class="modal-body">
        
        <div class="box-body">

            <!-- entrada para nombre -->
            
          <div class="form-group">
          
            <div class="input-group">
              
              <span class="input-group-addon"><i class="fa fa-user"></i></span>

              <input type="text" class="form-control input-lg" name="nuevoNombre" placeholder="Ingresar nombre" required>

             </div>

           </div>

           <!-- entrada para usuario -->
            
          <div class="form-group">
          
            <div class="input-group">
              
              <span class="input-group-addon"><i class="fa fa-key"></i></span>

              <input type="text" class="form-control input-lg" name="nuevoUsuario" placeholder="Ingresar usuario" required>

             </div>

           </div>

            <!-- entrada para contraseña -->
            
          <div class="form-group">
          
            <div class="input-group">
              
              <span class="input-group-addon"><i class="fa fa-lock"></i></span>

              <input type="password" class="form-control input-lg" name="nuevoPassword" placeholder="Ingresar contraseña" required>

             </div>

           </div>

           <!-- entrada para seleccionar perfil -->

              <div class="form-group">
          
                <div class="input-group">
            
                   <span class="input-group-addon"><i class="fa fa-users"></i></span>

                        <select class="form-control input-lg" name="nuevoPerfil">
              
                          <option value="">Seleccionar perfil</option>
                          <option value="Administrador">Administrador</option>
                          <option value="Especial">Especial</option>
                          <option value="Vendedor">Vendedor</option>

                         </select>

                  </div>

                 </div>

                 <!-- entrada para foto -->

                  <div class="form-group">
                    
                    <div class="panel">SUBIR FOTO</div>

                    <input type="file" id="nuevaFoto" name="nuevaFoto">

                    <p class="help-block">Peso máximo de la foto 200MB</p>

                    <img src="vistas/img/usuarios/default/anonymous.png" class="img-thumbnail" width="100px">

                  </div>

         </div>  

       </div>

        <!--=====================================
        PIE DEL MODAL
        ======================================-->

        <div class="modal-footer">

          <button type="button" class="btn btn-default pull-left" data-dismiss="modal">Salir</button>
          <button type="submit" class="btn btn-primary">Guardar usuario</button>

        </div>

     </form>

    </div>


  </div>

</div>