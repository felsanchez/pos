/*=============================================
VARIABLES GLOBALES PARA VARIANTES
=============================================*/
var tiposVariantesSeleccionados = [];
var opcionesVariantesSeleccionadas = {};
var datosVariantes = {};

$(document).ready(function () {

    /*=============================================
    MOSTRAR/OCULTAR SECCIÓN DE VARIANTES
    =============================================*/
    $('#checkTieneVariantes').on('change', function () {
        if ($(this).is(':checked')) {
            $('#seccionVariantes').removeClass('collapsed-box');
            $('#seccionVariantes').slideDown();
            cargarTiposVariantes();

            // Deshabilitar stock global y mostrar mensaje
            $("#stock").prop("readonly", true);
            $("#stock").css("background-color", "#e9ecef");
            $("#stock").attr("title", "El stock se calcula automáticamente como la suma de las variantes");

        } else {
            $('#seccionVariantes').slideUp();
            $('#seccionVariantes').addClass('collapsed-box');
            limpiarVariantes();

            // Habilitar stock global
            $("#stock").prop("readonly", false);
            $("#stock").css("background-color", "#fff");
            $("#stock").removeAttr("title");
        }
    });

    // Verificar al cargar la página
    if ($('#checkTieneVariantes').is(':checked') && !$('#checkTieneVariantes').prop('disabled')) {
        $('#seccionVariantes').show();
        $('#seccionVariantes').removeClass('collapsed-box');

        // Solo cargar tipos de variantes si NO estamos en modo edición
        if ($('input[name="idProducto"]').length === 0) {
            cargarTiposVariantes();
        }

        // Deshabilitar stock global
        $("#stock").prop("readonly", true);
        $("#stock").css("background-color", "#e9ecef");
    }

    /*=============================================
    MOSTRAR/OCULTAR SECCIÓN DE FACTURACIÓN
    =============================================*/
    $('#checkHabilitarFacturacion').on('change', function () {
        if ($(this).is(':checked')) {
            $('#seccionFacturacion').slideDown();
            $('#seccionFacturacion').removeClass('collapsed-box');
        } else {
            $('#seccionFacturacion').slideUp();
            $('#seccionFacturacion').addClass('collapsed-box');
        }
    });

    /*=============================================
    CALCULAR PRECIO DE VENTA CON PORCENTAJE
    =============================================*/
    $('#usarPorcentaje').on('change', function () {
        if ($(this).is(':checked')) {
            $('#porcentaje').prop('disabled', false);
            calcularPrecioVenta();
        } else {
            $('#porcentaje').prop('disabled', true);
        }
    });

    $('#precioCompra, #porcentaje').on('input', function () {
        if ($('#usarPorcentaje').is(':checked')) {
            calcularPrecioVenta();
        }
    });

    function calcularPrecioVenta() {
        var precioCompra = parseFloat($('#precioCompra').val()) || 0;
        var porcentaje = parseFloat($('#porcentaje').val()) || 0;

        if (precioCompra > 0 && porcentaje > 0) {
            var precioVenta = precioCompra * (1 + (porcentaje / 100));
            $('#precioVenta').val(precioVenta.toFixed(2));
        }
    }

    /*=============================================
    SINCRONIZAR CHECKBOX EXCLUIDO CON TASA DE IMPUESTO
    =============================================*/
    $('#esExcluido').on('change', function () {
        if ($(this).is(':checked')) {
            $('#tasaImpuesto').val('0.00');
            $('#tasaImpuesto').prop('disabled', true);
        } else {
            $('#tasaImpuesto').prop('disabled', false);
            $('#tasaImpuesto').val('19.00');
        }
    });

    /*=============================================
    GENERAR CÓDIGO AUTOMÁTICO AL SELECCIONAR CATEGORÍA
    =============================================*/
    $('#categoria').on('change', function () {
        // Solo si el tipo de código es automático
        if (typeof tipoCodigoProducto !== 'undefined' && tipoCodigoProducto === 'automatico') {
            var idCategoria = $(this).val();

            if (idCategoria) {
                // Generar código basado en categoría
                var data = new FormData();
                data.append("idCategoria", idCategoria);
                // csrf_token removido - manejado por csrf-helper.js

                $.ajax({
                    url: "ajax/productos.ajax.php",
                    method: "POST",
                    data: data,
                    cache: false,
                    contentType: false,
                    processData: false,
                    dataType: "json",
                    success: function (respuesta) {
                        console.log("Respuesta al generar código:", respuesta);

                        if (!respuesta) {
                            // No hay códigos numéricos previos en esta categoría
                            var nuevoCodigo = idCategoria + "01";
                            $("#codigo").val(nuevoCodigo);
                        } else {
                            // Existe un código numérico, incrementarlo
                            var nuevoCodigo = Number(respuesta["codigo"]) + 1;
                            $("#codigo").val(nuevoCodigo);
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error("Error al generar código:", error);
                        console.log("Respuesta del servidor:", xhr.responseText);
                    }
                });
            } else {
                // Si no hay categoría seleccionada, limpiar el código
                $("#codigo").val('');
            }
        }
    });

    /*=============================================
    PREVIEW DE IMAGEN
    =============================================*/
    $('#imagen').on('change', function () {
        var file = this.files[0];

        if (file) {
            // Validar tamaño (2MB)
            if (file.size > 2000000) {
                swal({
                    title: "Error",
                    text: "La imagen no debe pesar más de 2MB",
                    icon: "error",
                    confirmButtonText: "¡Cerrar!"
                });
                $(this).val('');
                return;
            }

            // Validar tipo
            if (file.type != "image/jpeg" && file.type != "image/png") {
                swal({
                    title: "Error",
                    text: "La imagen debe estar en formato JPG o PNG",
                    icon: "error",
                    confirmButtonText: "¡Cerrar!"
                });
                $(this).val('');
                return;
            }

            // Preview (opcional - puedes agregar un elemento img para mostrar preview)
            var reader = new FileReader();
            reader.onload = function (e) {
                // Aquí puedes mostrar un preview si lo deseas
                console.log('Imagen cargada correctamente');
            }
            reader.readAsDataURL(file);
        }
    });

    /*=============================================
    VALIDACIÓN DEL FORMULARIO
    =============================================*/
    $('#formProducto').on('submit', function (e) {
        // Validaciones básicas
        if ($('#codigo').val() == '') {
            e.preventDefault();
            swal({
                title: "Error",
                text: "El código del producto es obligatorio",
                icon: "error",
                confirmButtonText: "¡Cerrar!"
            });
            return false;
        }

        if ($('#descripcion').val() == '') {
            e.preventDefault();
            swal({
                title: "Error",
                text: "La descripción del producto es obligatoria",
                icon: "error",
                confirmButtonText: "¡Cerrar!"
            });
            return false;
        }

        if ($('#categoria').val() == '') {
            e.preventDefault();
            swal({
                title: "Error",
                text: "Debe seleccionar una categoría",
                icon: "error",
                confirmButtonText: "¡Cerrar!"
            });
            return false;
        }

        // Si tiene variantes, validar que haya combinaciones generadas
        // SOLO si el checkbox NO está deshabilitado (es decir, si estamos creando un producto nuevo)
        if ($('#checkTieneVariantes').is(':checked') && !$('#checkTieneVariantes').prop('disabled')) {
            if ($('.filaCombinacion').length === 0) {
                e.preventDefault();
                swal({
                    title: "Error",
                    text: "Debe generar al menos una combinación de variantes",
                    icon: "error",
                    confirmButtonText: "¡Cerrar!"
                });
                return false;
            }
        }

        // Si todas las validaciones pasan, permitir el envío normal del formulario
        // No se usa AJAX, se envía de forma tradicional para que PHP lo procese
        return true;
    });

    /*=============================================
    BOTÓN RECARGAR VARIANTES
    =============================================*/
    $('#btnRecargarVariantes').on('click', function () {
        cargarTiposVariantes();
    });

});


/*=============================================
FUNCIONES PARA VARIANTES
=============================================*/

// Cargar tipos de variantes disponibles
function cargarTiposVariantes() {
    var datos = new FormData();
    datos.append("obtenerTiposVariantes", "ok");
    // csrf_token removido - manejado por csrf-helper.js

    $.ajax({
        url: "ajax/productos.ajax.php",
        method: "POST",
        data: datos,
        cache: false,
        contentType: false,
        processData: false,
        dataType: "json",
        success: function (respuesta) {

            $("#tiposVariantesContainer").html('');

            if (respuesta && respuesta.length > 0) {

                respuesta.forEach(function (tipo) {
                    var checked = tiposVariantesSeleccionados.indexOf(tipo.id) > -1 ? 'checked' : '';

                    var html = '<div class="checkbox" style="display:inline-block; margin-right: 15px;">';
                    html += '<label>';
                    html += '<input type="checkbox" class="minimal checkTipoVariante" value="' + tipo.id + '" data-nombre="' + tipo.nombre + '" ' + checked + '>';
                    html += tipo.nombre;
                    html += '</label>';
                    html += '</div>';

                    $("#tiposVariantesContainer").append(html);
                });

                // Inicializar iCheck
                $('.checkTipoVariante').iCheck({
                    checkboxClass: 'icheckbox_minimal-blue',
                    radioClass: 'iradio_minimal-blue'
                });

                // Eventos iCheck
                $('.checkTipoVariante').on('ifChecked', function () {
                    var idTipo = $(this).val();
                    var nombreTipo = $(this).attr('data-nombre');
                    agregarTipoVariante(idTipo, nombreTipo);
                });

                $('.checkTipoVariante').on('ifUnchecked', function () {
                    var idTipo = $(this).val();
                    removerTipoVariante(idTipo);
                });

            } else {
                $("#tiposVariantesContainer").html('<div class="alert alert-warning">No hay tipos de variantes configurados. <a href="variantes">Crear tipos</a></div>');
            }
        }
    });
}

function agregarTipoVariante(idTipo, nombreTipo) {
    if (tiposVariantesSeleccionados.indexOf(idTipo) === -1) {
        tiposVariantesSeleccionados.push(idTipo);
        datosVariantes['tipo_' + idTipo] = {
            id: idTipo,
            nombre: nombreTipo,
            opciones: []
        };

        cargarOpcionesVariante(idTipo, nombreTipo);
    }
}

function removerTipoVariante(idTipo) {
    var index = tiposVariantesSeleccionados.indexOf(idTipo);
    if (index > -1) {
        tiposVariantesSeleccionados.splice(index, 1);
    }

    // Remover contenedor de opciones
    $("#opcionesTipo_" + idTipo).remove();

    // Limpiar datos
    delete datosVariantes['tipo_' + idTipo];
    delete opcionesVariantesSeleccionadas[idTipo];

    // Regenerar combinaciones si quedan tipos
    if (tiposVariantesSeleccionados.length > 0) {
        generarCombinaciones();
    } else {
        $("#opcionesVariantesContainer").hide();
        $("#combinacionesContainer").hide();
    }
}

function cargarOpcionesVariante(idTipo, nombreTipo) {
    var datos = new FormData();
    datos.append("obtenerOpcionesVariante", idTipo);
    // csrf_token removido - manejado por csrf-helper.js

    $.ajax({
        url: "ajax/productos.ajax.php",
        method: "POST",
        data: datos,
        cache: false,
        contentType: false,
        processData: false,
        dataType: "json",
        success: function (respuesta) {

            $("#opcionesVariantesContainer").show();

            var html = '<div id="opcionesTipo_' + idTipo + '" style="border: 1px solid #ddd; padding: 10px; margin-bottom: 10px; background-color: #f9f9f9; border-radius: 5px;">';
            html += '<h5 style="margin-top: 0; border-bottom: 1px solid #eee; padding-bottom: 5px;"><strong>' + nombreTipo + ':</strong></h5>';

            if (respuesta && respuesta.length > 0) {
                respuesta.forEach(function (opcion) {
                    if (opcion.estado == 1) {
                        html += '<div class="checkbox" style="display:inline-block; margin-right: 15px;">';
                        html += '<label>';
                        html += '<input type="checkbox" class="minimal checkOpcionVariante" data-idtipo="' + idTipo + '" data-idopcion="' + opcion.id + '" data-nombreopcion="' + opcion.nombre + '">';
                        html += opcion.nombre;
                        html += '</label>';
                        html += '</div>';
                    }
                });
            } else {
                html += '<p class="text-muted">No hay opciones disponibles.</p>';
            }

            html += '</div>';

            $("#opcionesVariantesContainer").append(html);

            // Inicializar iCheck
            $('.checkOpcionVariante[data-idtipo="' + idTipo + '"]').iCheck({
                checkboxClass: 'icheckbox_minimal-blue',
                radioClass: 'iradio_minimal-blue'
            });

            // Eventos
            $('.checkOpcionVariante[data-idtipo="' + idTipo + '"]').on('ifChecked', function () {
                var idOpcion = $(this).attr("data-idopcion");
                var nombreOpcion = $(this).attr("data-nombreopcion");
                agregarOpcionVariante(idTipo, idOpcion, nombreOpcion);
            });

            $('.checkOpcionVariante[data-idtipo="' + idTipo + '"]').on('ifUnchecked', function () {
                var idOpcion = $(this).attr("data-idopcion");
                removerOpcionVariante(idTipo, idOpcion);
            });
        }
    });
}

function agregarOpcionVariante(idTipo, idOpcion, nombreOpcion) {
    if (!opcionesVariantesSeleccionadas[idTipo]) {
        opcionesVariantesSeleccionadas[idTipo] = [];
    }

    var existe = opcionesVariantesSeleccionadas[idTipo].find(function (op) {
        return op.id === idOpcion;
    });

    if (!existe) {
        opcionesVariantesSeleccionadas[idTipo].push({
            id: idOpcion,
            nombre: nombreOpcion
        });
    }

    generarCombinaciones();
}

function removerOpcionVariante(idTipo, idOpcion) {
    if (opcionesVariantesSeleccionadas[idTipo]) {
        opcionesVariantesSeleccionadas[idTipo] = opcionesVariantesSeleccionadas[idTipo].filter(function (op) {
            return op.id !== idOpcion;
        });

        if (opcionesVariantesSeleccionadas[idTipo].length === 0) {
            delete opcionesVariantesSeleccionadas[idTipo];
        }
    }

    generarCombinaciones();
}

function generarCombinaciones() {
    var tiposConOpciones = Object.keys(opcionesVariantesSeleccionadas);

    if (tiposConOpciones.length === 0) {
        $("#combinacionesContainer").hide();
        return;
    }

    // Verificar que todos los tipos seleccionados tengan al menos una opción
    // Si no, no generamos combinaciones parciales para evitar confusión
    /*
    if (tiposConOpciones.length < tiposVariantesSeleccionados.length) {
        return;
    }
    */

    var combinaciones = generarProductoCartesiano(opcionesVariantesSeleccionadas);

    if (combinaciones.length === 0) {
        $("#combinacionesContainer").hide();
        return;
    }

    $("#combinacionesContainer").show();

    var html = '<table class="table table-bordered table-striped table-condensed">';
    html += '<thead>';
    html += '<tr>';
    html += '<th width="40px"><input type="checkbox" id="checkTodasCombinaciones" checked></th>';
    html += '<th>Variante</th>';
    html += '<th width="150px">Precio (+/-)</th>';
    html += '<th width="120px">Stock</th>';
    html += '</tr>';
    html += '</thead>';
    html += '<tbody>';

    for (var i = 0; i < combinaciones.length; i++) {
        var nombreCombinacion = combinaciones[i].map(function (opt) { return opt.nombre; }).join(' - ');
        var idsCombinacion = combinaciones[i].map(function (opt) { return opt.id; }).join('_');

        html += '<tr class="filaCombinacion">';
        html += '<td><input type="checkbox" class="checkCombinacion" data-index="' + i + '" checked></td>';
        html += '<td>' + nombreCombinacion + '</td>';
        html += '<td>';
        html += '<div class="input-group input-group-sm">';
        html += '<span class="input-group-addon">$</span>';
        html += '<input type="number" class="form-control" name="precioVariante_' + idsCombinacion + '" placeholder="0" step="0.01" value="0">';
        html += '</div>';
        html += '</td>';
        html += '<td>';
        html += '<input type="number" class="form-control input-sm" name="stockVariante_' + idsCombinacion + '" placeholder="0" min="0" value="0">';
        html += '</td>';

        // Inputs hidden para enviar datos
        html += '<input type="hidden" name="combinacion_' + i + '_ids" value="' + idsCombinacion + '" class="hiddenCombinacion" data-index="' + i + '">';
        html += '<input type="hidden" name="combinacion_' + i + '_nombre" value="' + nombreCombinacion + '" class="hiddenCombinacion" data-index="' + i + '">';

        html += '</tr>';
    }

    html += '</tbody>';
    html += '</table>';
    html += '<input type="hidden" name="totalCombinaciones" value="' + combinaciones.length + '">';

    $("#listaCombinaciones").html(html);

    // Eventos de la tabla
    $("#checkTodasCombinaciones").on('change', function () {
        var checked = $(this).prop('checked');
        $(".checkCombinacion").prop('checked', checked).trigger('change');
    });

    $(".checkCombinacion").on('change', function () {
        var index = $(this).attr('data-index');
        var checked = $(this).prop('checked');
        var row = $(this).closest('tr');

        if (checked) {
            row.find('input[type="number"]').prop('disabled', false);
            $('.hiddenCombinacion[data-index="' + index + '"]').prop('disabled', false);
            row.removeClass('text-muted');
        } else {
            row.find('input[type="number"]').prop('disabled', true);
            $('.hiddenCombinacion[data-index="' + index + '"]').prop('disabled', true);
            row.addClass('text-muted');
        }
    });
}

function generarProductoCartesiano(opciones) {
    var keys = Object.keys(opciones);
    if (keys.length === 0) return [];

    var result = [[]];

    for (var i = 0; i < keys.length; i++) {
        var key = keys[i];
        var opcionesDelTipo = opciones[key];
        var temp = [];

        for (var j = 0; j < result.length; j++) {
            for (var k = 0; k < opcionesDelTipo.length; k++) {
                temp.push(result[j].concat([opcionesDelTipo[k]]));
            }
        }
        result = temp;
    }
    return result;
}

function limpiarVariantes() {
    tiposVariantesSeleccionados = [];
    opcionesVariantesSeleccionadas = {};
    datosVariantes = {};
    $("#tiposVariantesContainer").html('');
    $("#opcionesVariantesContainer").html('').hide();
    $("#combinacionesContainer").hide();
}

function cargarVariantesExistentes() {
    // Esta función requeriría una llamada AJAX adicional para obtener 
    // la configuración guardada de variantes del producto y pre-llenar el formulario.
    // Por ahora, cargamos los tipos básicos.
    cargarTiposVariantes();
    console.log("TODO: Implementar carga de variantes existentes para edición");
}

