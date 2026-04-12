<?php
if (!puedeVer('proveedores')) {
  echo '<script>window.location = "inicio";</script>';
  return;
}
?>
<!-- Solo muestra 2 campos en movil en la Tabla 1-->
<style>
  /* Espaciado automático para el botón de expansión en modo inline */
  .tablaProveedores.collapsed tbody td:first-child {
    padding-left: 35px !important;
    position: relative;
    cursor: pointer;
  }

  /* Posicionamiento del botón + de DataTables en modo inline */
  .tablaProveedores.collapsed tbody td:first-child::before {
    left: 8px !important;
    top: 50% !important;
    transform: translateY(-50%) !important;
    box-shadow: none !important;
    background-color: #3b8ab8 !important; /* Estilo azul AdminLTE */
  }

  /* Estilos para el botón de expansión en móvil */
  @media (max-width: 767px) {
    .tablaProveedores td.control {
      cursor: pointer;
    }

    /* Resize action buttons on mobile */
    .tablaProveedores .btn-group .btn {
      padding: 1px 5px;
      font-size: 12px;
      line-height: 1.5;
      border-radius: 3px;
    }
  }

  /* Estilos para campo notas editable */
  .celda-notas-proveedor {
    background: #fff9e6;
    padding: 8px;
    border-radius: 3px;
    cursor: text;
    font-size: 13px;
    color: #333;
    min-height: 30px;
    position: relative;
  }

  /* Placeholder para cuando está vacío */
  .celda-notas-proveedor:empty:before,
  .celda-notas-proveedor[data-placeholder]:before {
    content: "Escribe una nota...";
    color: #999;
    font-style: italic;
  }

  /* Ocultar placeholder cuando tiene foco */
  .celda-notas-proveedor:focus:before {
    content: none;
  }

  .celda-notas-proveedor:focus {
    outline: 2px solid #f39c12;
    background: #fffef5;
  }
</style>

<div class="content-wrapper">
  <section class="content-header">

    <h1>
      Administrar proveedores
    </h1>

    <ol class="breadcrumb">
      <li><a href="inicio"><i class="fa fa-dashboard"></i> Inicio</a></li>
      <li class="active">Administrar proveedores</li>
    </ol>

  </section>

  <section class="content">

    <div class="box">

      <div class="box-header with-border">
        <?php if (puedeAccion('proveedores', 'crear')): ?>
          <button class="btn btn-primary" data-toggle="modal" data-target="#modalAgregarProveedor">

            <i class="fa fa-plus"></i> Agregar proveedor

          </button>
        <?php endif; ?>
      </div>


      <div class="box-body table-responsive">

        <table class="table table-bordered table-striped dt-responsive tablaProveedores" width="100%">

          <thead>
            <tr>
              <th>Nombre</th>
              <th>Nombre Comercial</th>
              <th>Celular</th>
              <th>Correo</th>
              <th>Dirección</th>
              <th>Productos</th>
              <th>Notas</th>
              <th>Acciones</th>
            </tr>
          </thead>

          <tbody>

            <?php

            $item = null;
            $valor = null;

            $proveedores = ControladorProveedores::ctrMostrarProveedores($item, $valor);


            foreach ($proveedores as $key => $value) {

              // Contar productos asociados a este proveedor
            
              $totalProductos = ModeloProveedores::mdlContarProductosPorProveedor($value["id"]);

              echo '<tr> 
                        <td>' . $value["nombre"] . '</td>
                        <td>' . $value["marca"] . '</td>';

              echo '<td>' . $value["celular"] . '</td>';
              echo '<td>' . $value["correo"] . '</td>';
              echo '<td>' . $value["direccion"] . '</td>';

              echo '<td><span class="badge bg-blue">' . $totalProductos . '</span></td>';

              // Columna notas editable
              $notas = isset($value["notas"]) ? $value["notas"] : '';
              echo '<td contenteditable="true" class="celda-notas-proveedor" data-id="' . $value['id'] . '">' . $notas . '</td>';

              echo '<td>
                      <div class="btn-group">';

              if (puedeAccion('proveedores', 'editar')) {
                echo '<button class="btn btn-warning btnEditarProveedor" idProveedor="' . $value["id"] . '" data-toggle="modal" data-target="#modalEditarProveedor"><i class="fa fa-pencil"></i></button>';
              }

              if (puedeAccion('proveedores', 'eliminar')) {
                echo '<button class="btn btn-danger btnEliminarProveedor" idProveedor="' . $value["id"] . '"><i class="fa fa-times"></i></button>';
              }

              echo '</div>
                    </td>

                  </tr>';
            }
            ?>


          </tbody>

        </table>

      </div>

    </div>

  </section>

</div>


<!--=====================================
MODAL AGREGAR Proveedor
======================================-->

<!-- Modal -->
<div id="modalAgregarProveedor" class="modal fade" role="dialog">

  <div class="modal-dialog">

    <div class="modal-content">

      <form role="form" method="post" enctype="multipart/form-data">

        <?php CSRF::insertToken(); ?>

        <!--=====================================
      CABEZA DEL MODAL
      ======================================-->

        <div class="modal-header" style="background:#3c8dbc; color: white">

          <button type="button" class="close" data-dismiss="modal">&times;</button>
          <h4 class="modal-title">Agregar Proveedor</h4>

        </div>

        <!--=====================================
      CUERPO DEL MODAL
      ======================================-->

        <div class="modal-body">
          <div class="box-body">

            <?php
            $tiposDocumento = ControladorFactus::ctrMostrarTiposDocumento();
            $municipios = ModeloFactus::mdlObtenerMunicipios();
            ?>

            <div class="row">
              <!-- Tipo Documento -->
              <div class="col-md-6">
                <div class="form-group">
                  <label>Tipo de Documento *</label>
                  <div class="input-group">
                    <span class="input-group-addon"><i class="fa fa-id-card"></i></span>
                    <select class="form-control" name="nuevoTipoDocumento" required>
                      <option value="">Seleccionar tipo</option>
                      <?php foreach ($tiposDocumento as $key => $value): ?>
                        <option value="<?php echo $value["id"]; ?>"><?php echo $value["nombre"]; ?></option>
                      <?php endforeach; ?>
                    </select>
                  </div>
                </div>
              </div>

              <!-- Documento -->
              <div class="col-md-6">
                <div class="form-group">
                  <label>Documento *</label>
                  <div class="input-group">
                    <span class="input-group-addon"><i class="fa fa-hashtag"></i></span>
                    <input type="text" class="form-control" name="nuevoDocumento" placeholder="Número de documento"
                      required>
                  </div>
                </div>
              </div>
            </div>

            <div class="row">

              <!-- Nombre -->
              <div class="col-md-6">
                <div class="form-group">
                  <label>Nombre / Razón Social *</label>
                  <div class="input-group">
                    <span class="input-group-addon"><i class="fa fa-user"></i></span>
                    <input type="text" class="form-control" name="nuevoProveedor" placeholder="Nombre completo"
                      required>
                  </div>
                </div>
              </div>

              <!-- Nombre Comercial -->
              <div class="col-md-6">
                <div class="form-group">
                  <label>Nombre Comercial / Marca</label>
                  <div class="input-group">
                    <span class="input-group-addon"><i class="fa fa-tag"></i></span>
                    <input type="text" class="form-control" name="nuevaMarca" placeholder="Nombre comercial (opcional)">
                  </div>
                </div>
              </div>

            </div>

            <div class="row">

              <!-- Organización -->
              <div class="col-md-6">
                <div class="form-group">
                  <label>Tipo de Organización *</label>
                  <div class="input-group">
                    <span class="input-group-addon"><i class="fa fa-building"></i></span>
                    <select class="form-control" name="nuevaOrganizacion" required>
                      <option value="1">Persona Jurídica</option>
                      <option value="2" selected>Persona Natural</option>
                    </select>
                  </div>
                </div>
              </div>

              <!-- Municipio -->
              <div class="col-md-6">
                <div class="form-group">
                  <label>Municipio *</label>
                  <div class="input-group">
                    <span class="input-group-addon"><i class="fa fa-map-marker"></i></span>
                    <select class="form-control select2" name="nuevoMunicipio" style="width: 100%;" required>
                      <option value="">Seleccionar municipio</option>
                      <?php foreach ($municipios as $key => $value): ?>
                        <option value="<?php echo $value["id_factus"]; ?>">
                          <?php echo $value["nombre"] . ' - ' . $value["departamento"]; ?>
                        </option>
                      <?php endforeach; ?>
                    </select>
                  </div>
                </div>
              </div>

            </div>

            <div class="row">

              <!-- Celular -->
              <div class="col-md-6">
                <div class="form-group">
                  <label>Celular *</label>
                  <div class="input-group">
                    <span class="input-group-addon"><i class="fa fa-phone"></i></span>
                    <input type="text" class="form-control" name="nuevoCelular" placeholder="Número de celular"
                      required>
                  </div>
                </div>
              </div>

              <!-- Correo -->
              <div class="col-md-6">
                <div class="form-group">
                  <label>Correo</label>
                  <div class="input-group">
                    <span class="input-group-addon"><i class="fa fa-envelope"></i></span>
                    <input type="email" class="form-control" name="nuevoCorreo" placeholder="Correo electrónico">
                  </div>
                </div>
              </div>

            </div>

            <!-- Dirección -->
            <div class="form-group">
              <label>Dirección</label>
              <div class="input-group">
                <span class="input-group-addon"><i class="fa fa-home"></i></span>
                <input type="text" class="form-control" name="nuevaDireccion" placeholder="Dirección del proveedor">
              </div>
            </div>

          </div>

        </div>

        <!--=====================================
        PIE DEL MODAL
        ======================================-->

        <div class="modal-footer">

          <button type="button" class="btn btn-default pull-left" data-dismiss="modal">Salir</button>
          <button type="submit" class="btn btn-primary">Guardar proveedor</button>

        </div>

        <?php

        $crearProveedor = new ControladorProveedores();
        $crearProveedor->ctrCrearProveedor();

        ?>

      </form>

    </div>


  </div>

</div>




<!--==========================================================================================================
MODAL EDITAR Proveedor
===========================================================================================================-->

<!-- Modal -->
<div id="modalEditarProveedor" class="modal fade" role="dialog">

  <div class="modal-dialog">

    <div class="modal-content">

      <form role="form" method="post" enctype="multipart/form-data">

        <?php CSRF::insertToken(); ?>

        <!--=====================================
      CABEZA DEL MODAL
      ======================================-->

        <div class="modal-header" style="background:#3c8dbc; color: white">

          <button type="button" class="close" data-dismiss="modal">&times;</button>
          <h4 class="modal-title">Editar Proveedor</h4>

        </div>

        <!--=====================================
      CUERPO DEL MODAL
      ======================================-->

        <div class="modal-body">
          <div class="box-body">

            <div class="row">
              <!-- Tipo Documento -->
              <div class="col-md-6">
                <div class="form-group">
                  <label>Tipo de Documento *</label>
                  <div class="input-group">
                    <span class="input-group-addon"><i class="fa fa-id-card"></i></span>
                    <select class="form-control" name="editarTipoDocumento" id="editarTipoDocumento" required>
                      <option value="">Seleccionar tipo</option>
                      <?php foreach ($tiposDocumento as $key => $value): ?>
                        <option value="<?php echo $value["id"]; ?>"><?php echo $value["nombre"]; ?></option>
                      <?php endforeach; ?>
                    </select>
                  </div>
                </div>
              </div>

              <!-- Documento -->
              <div class="col-md-6">
                <div class="form-group">
                  <label>Documento *</label>
                  <div class="input-group">
                    <span class="input-group-addon"><i class="fa fa-hashtag"></i></span>
                    <input type="text" class="form-control" name="editarDocumento" id="editarDocumento"
                      placeholder="Número de documento" required>
                  </div>
                </div>
              </div>
            </div>

            <div class="row">

              <!-- Nombre -->
              <div class="col-md-6">
                <div class="form-group">
                  <label>Nombre / Razón Social *</label>
                  <div class="input-group">
                    <span class="input-group-addon"><i class="fa fa-user"></i></span>
                    <input type="text" class="form-control" name="editarProveedor" id="editarProveedor"
                      placeholder="Nombre del proveedor" required>
                    <input type="hidden" id="idProveedor" name="idProveedor">
                  </div>
                </div>
              </div>

              <!-- Nombre Comercial -->
              <div class="col-md-6">
                <div class="form-group">
                  <label>Nombre Comercial / Marca</label>
                  <div class="input-group">
                    <span class="input-group-addon"><i class="fa fa-tag"></i></span>
                    <input type="text" class="form-control" name="editarMarca" id="editarMarca"
                      placeholder="Nombre comercial (opcional)">
                  </div>
                </div>
              </div>

            </div>

            <div class="row">

              <!-- Organización -->
              <div class="col-md-6">
                <div class="form-group">
                  <label>Tipo de Organización *</label>
                  <div class="input-group">
                    <span class="input-group-addon"><i class="fa fa-building"></i></span>
                    <select class="form-control" name="editarOrganizacion" id="editarOrganizacion" required>
                      <option value="1">Persona Jurídica</option>
                      <option value="2">Persona Natural</option>
                    </select>
                  </div>
                </div>
              </div>

              <!-- Municipio -->
              <div class="col-md-6">
                <div class="form-group">
                  <label>Municipio *</label>
                  <div class="input-group">
                    <span class="input-group-addon"><i class="fa fa-map-marker"></i></span>
                    <select class="form-control select2" name="editarMunicipio" id="editarMunicipio"
                      style="width: 100%;" required>
                      <option value="">Seleccionar municipio</option>
                      <?php foreach ($municipios as $key => $value): ?>
                        <option value="<?php echo $value["id_factus"]; ?>">
                          <?php echo $value["nombre"] . ' - ' . $value["departamento"]; ?>
                        </option>
                      <?php endforeach; ?>
                    </select>
                  </div>
                </div>
              </div>

            </div>

            <div class="row">

              <!-- Celular -->
              <div class="col-md-6">
                <div class="form-group">
                  <label>Celular *</label>
                  <div class="input-group">
                    <span class="input-group-addon"><i class="fa fa-phone"></i></span>
                    <input type="text" class="form-control" name="editarCelular" id="editarCelular"
                      placeholder="Número de celular" required>
                  </div>
                </div>
              </div>

              <!-- Correo -->
              <div class="col-md-6">
                <div class="form-group">
                  <label>Correo</label>
                  <div class="input-group">
                    <span class="input-group-addon"><i class="fa fa-envelope"></i></span>
                    <input type="email" class="form-control" name="editarCorreo" id="editarCorreo"
                      placeholder="Correo electrónico">
                  </div>
                </div>
              </div>

            </div>

            <!-- Dirección -->
            <div class="form-group">
              <label>Dirección</label>
              <div class="input-group">
                <span class="input-group-addon"><i class="fa fa-home"></i></span>
                <input type="text" class="form-control" name="editarDireccion" id="editarDireccion"
                  placeholder="Dirección del proveedor">
              </div>
            </div>

          </div>

        </div>

        <!--=====================================
        PIE DEL MODAL
        ======================================-->

        <div class="modal-footer">

          <button type="button" class="btn btn-default pull-left" data-dismiss="modal">Salir</button>
          <button type="submit" class="btn btn-primary">Modificar proveedor</button>

        </div>

        <?php

        $editarProveedor = new ControladorProveedores();
        $editarProveedor->ctrEditarProveedor();

        ?>

      </form>

    </div>


  </div>

</div>


<?php

$borrarProveedor = new ControladorProveedores();
$borrarProveedor->ctrBorrarProveedor();

?>