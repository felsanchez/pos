/*=============================================
VARIABLES GLOBALES PARA VARIANTES
=============================================*/
var tiposVariantesSeleccionados = [];
var opcionesVariantesSeleccionadas = {};
var datosVariantes = {};
var variantesExistentesData = {}; // Guardar IDs de variantes existentes

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
    if ($('#checkTieneVariantes').is(':checked')) {
        $('#seccionVariantes').show();
        $('#seccionVariantes').removeClass('collapsed-box');

        // Cargar tipos de variantes siempre que esté chequeado
        cargarTiposVariantes();

        // Deshabilitar stock global
        $("#stock").prop("readonly", true);
        $("#stock").css("background-color", "#e9ecef");

        // Cargar datos de variantes si ya existen y luego generar la tabla
        cargarConfiguracionVariantesExistentes();
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
                    type: "error",
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
                    type: "error",
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
                type: "error",
                confirmButtonText: "¡Cerrar!"
            });
            return false;
        }

        if ($('#descripcion').val() == '') {
            e.preventDefault();
            swal({
                title: "Error",
                text: "La descripción del producto es obligatoria",
                type: "error",
                confirmButtonText: "¡Cerrar!"
            });
            return false;
        }

        if ($('#categoria').val() == '') {
            e.preventDefault();
            swal({
                title: "Error",
                text: "Debe seleccionar una categoría",
                type: "error",
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
                    type: "error",
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

function cargarConfiguracionVariantesExistentes() {
    return new Promise(function(resolve, reject) {
        var idProducto = $('input[name="idProducto"]').val();
        if (!idProducto) return resolve();

        var datos = new FormData();
        datos.append("obtenerVariantesParaEditar", idProducto);

        $.ajax({
            url: "ajax/productos.ajax.php",
            method: "POST",
            data: datos,
            cache: false,
            contentType: false,
            processData: false,
            dataType: "json",
            success: function (respuesta) {
                if (respuesta && respuesta.length > 0) {
                    respuesta.forEach(function(varExistente) {
                        variantesExistentesData[varExistente.opciones] = {
                            id: varExistente.id,
                            precio_adicional: varExistente.precio_adicional,
                            stock: varExistente.stock,
                            tipos: varExistente.tipos // [1, 2] por ejemplo
                        };
                    });
                    
                    // Disparar la selección automática de tipos involucrados
                    var todosLosTipos = [];
                    $.each(variantesExistentesData, function(key, val) {
                        val.tipos.forEach(function(tid) {
                            if(todosLosTipos.indexOf(tid) === -1) todosLosTipos.push(tid);
                        });
                    });
                    
                    if(todosLosTipos.length > 0) {
                        setTimeout(function() {
                            todosLosTipos.forEach(function(tid) {
                                $('.checkTipoVariante[value="' + tid + '"]').iCheck('check');
                            });
                        }, 500);
                    }
                }
                resolve();
            },
            error: function() {
                resolve();
            }
        });
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
                        // Verificar si esta opción debe estar chequeada (existe en alguna variante cargada)
                        var isChecked = "";
                        $.each(variantesExistentesData, function(key, val) {
                            var idsOpciones = key.split("_");
                            if(idsOpciones.indexOf(opcion.id.toString()) !== -1) {
                                isChecked = "checked";
                                return false;
                            }
                        });

                        html += '<div class="checkbox" style="display:inline-block; margin-right: 15px;">';
                        html += '<label>';
                        html += '<input type="checkbox" class="minimal checkOpcionVariante" data-idtipo="' + idTipo + '" data-idopcion="' + opcion.id + '" data-nombreopcion="' + opcion.nombre + '" ' + isChecked + '>';
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
    var modoEdicion = $('input[name="idProducto"]').length > 0 && $('input[name="idProducto"]').val() !== "";

    if (tiposConOpciones.length === 0) {
        $("#combinacionesContainer").hide();
        return;
    }

    var combinaciones = generarProductoCartesiano(opcionesVariantesSeleccionadas);

    if (combinaciones.length === 0) {
        $("#combinacionesContainer").hide();
        return;
    }

    $("#combinacionesContainer").show();

    // Nombres de campos dinámicos según modo
    var prefixPrecio = modoEdicion ? "precioAdicionalEditar_" : "precioAdicional_";
    var prefixStock = modoEdicion ? "stockVarianteEditar_" : "stockVariante_";
    var prefixCombinacionIds = modoEdicion ? "combinacionEditar_" : "combinacion_";
    var nameTotal = modoEdicion ? "totalCombinacionesEditar" : "totalCombinaciones";

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

        // 🛡️ CRITICAL FIX: Siempre ordenar los IDs numéricamente para que el SKU y los nombres de campos 
        // sean consistentes con la base de datos, sin importar el orden de selección.
        var arrayIds = combinaciones[i].map(function (opt) { return parseInt(opt.id, 10); });
        arrayIds.sort(function(a, b){ return a - b; });
        
        var idsCombinacion = arrayIds.join('_');
        var strBusqueda = idsCombinacion; // Ahora son lo mismo y siempre están ordenados

        // 🛡️ BÚSQUEDA ULTRA-ROBUSTA: Iterar sobre las keys para evitar fallos de tipos o espacios
        var idExistente = null;
        var datosExistentes = null;
        var busquedaLimpia = strBusqueda.toString().trim();

        if (variantesExistentesData) {
            Object.keys(variantesExistentesData).forEach(function(key) {
                if (key.toString().trim() === busquedaLimpia) {
                    datosExistentes = variantesExistentesData[key];
                    idExistente = datosExistentes.id;
                }
            });
        }

        if (idExistente) {
            console.log("✅ MATCH DETECTADO: " + busquedaLimpia + " -> ID " + idExistente);
        }

        var valorPrecio = datosExistentes ? datosExistentes.precio_adicional : 0;
        var valorStock = datosExistentes ? datosExistentes.stock : 0;

        // 🔓 REVERTIDO: Permitir edición de nuevo según solicitud del usuario
        // Pero mantenemos una marca visual sutil para saber que ya existe
        var estiloExistente = idExistente ? 'style="border-left: 3px solid #00a65a;"' : '';

        html += '<tr class="filaCombinacion" ' + estiloExistente + '>';
        html += '<td><input type="checkbox" class="checkCombinacion" data-index="' + i + '" checked></td>';
        html += '<td>' + nombreCombinacion + (idExistente ? ' <br><span class="label label-success">EXISTENTE</span>' : '') + '</td>';
        html += '<td>';
        html += '<div class="input-group input-group-sm">';
        html += '<span class="input-group-addon">$</span>';
        html += '<input type="number" class="form-control" name="' + prefixPrecio + idsCombinacion + '" placeholder="0" step="0.01" value="' + valorPrecio + '">';
        html += '</div>';
        html += '</td>';
        html += '<td>';
        html += '<input type="number" class="form-control input-sm" name="' + prefixStock + idsCombinacion + '" placeholder="0" min="0" value="' + valorStock + '">';
        html += '</td>';

        // Inputs hidden para enviar datos
        html += '<input type="hidden" name="' + prefixCombinacionIds + i + '_ids" value="' + idsCombinacion + '" class="hiddenCombinacion" data-index="' + i + '">';
        html += '<input type="hidden" name="' + prefixCombinacionIds + i + '_nombre" value="' + nombreCombinacion + '" class="hiddenCombinacion" data-index="' + i + '">';
        
        if (idExistente) {
            html += '<input type="hidden" name="idVarianteExistente_' + idsCombinacion + '" value="' + idExistente + '">';
        }

        html += '</tr>';
    }

    html += '</tbody>';
    html += '</table>';
    html += '<input type="hidden" name="' + nameTotal + '" value="' + combinaciones.length + '">';

    $("#listaCombinaciones").html(html);

    // Eventos de la tabla
    $("#checkTodasCombinaciones").on('change', function () {
        var checked = $(this).prop('checked');
        $(".checkCombinacion:not([disabled])").prop('checked', checked).trigger('change');
    });

    $(".checkCombinacion").on('change', function () {
        if ($(this).is(':disabled')) return; // 🛡️ NO HACER NADA SI ESTÁ BLOQUEADA (Variante existente)

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
        
        recalcularStockTotal();
    });

    // Recalcular stock al cambiar el stock de una variante
    $(document).on("input", ".stockVariante", function() {
        recalcularStockTotal();
    });
}

function recalcularStockTotal() {
    var total = 0;
    $(".stockVariante").each(function() {
        // Solo sumar si la fila está habilitada (checkbox marcado)
        var row = $(this).closest('tr');
        var checkbox = row.find(".checkCombinacion");
        
        if (checkbox.length === 0 || checkbox.is(":checked")) {
            var val = parseFloat($(this).val()) || 0;
            total += val;
        }
    });
    
    $("#stock").val(total);
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

// Controlar si el stock base debe ser editable o no
function actualizarEstadoStockBase() {
    if ($("#checkTieneVariantes").is(":checked")) {
        $("#stock").prop("readonly", true).css("background-color", "#eee");
    } else {
        $("#stock").prop("readonly", false).css("background-color", "#fff");
    }
}

$(document).on("change", "#checkTieneVariantes", function() {
    actualizarEstadoStockBase();
});

// Ejecutar al inicio (con pequeño delay para que iCheck procese)
setTimeout(actualizarEstadoStockBase, 500);

// Eliminar la función duplicada cargarVariantesExistentes que estaba aquí

