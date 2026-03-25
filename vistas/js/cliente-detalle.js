/*=============================================
VALIDACIONES Y LÓGICA PARA CLIENTE DETALLE
=============================================*/

$(document).ready(function () {

    /*=============================================
    MOSTRAR/OCULTAR CAMPOS SEGÚN TIPO DE PERSONA
    =============================================*/
    function actualizarCamposSegunTipoPersona() {
        var tipoPersona = $('input[name="nuevoTipoPersona"]:checked, input[name="editarTipoPersona"]:checked').val();

        if (tipoPersona === 'juridica') {
            // Mostrar campos de empresa
            $('#contenedorRazonSocial').show();
            $('#contenedorNombreComercial').show();
            $('#labelNombre').text('Nombre del Representante Legal *');

            // Hacer razón social obligatoria
            $('#razonSocial').prop('required', true);
        } else {
            // Ocultar campos de empresa
            $('#contenedorRazonSocial').hide();
            $('#contenedorNombreComercial').hide();
            $('#labelNombre').text('Nombre Completo *');

            // Razón social no obligatoria
            $('#razonSocial').prop('required', false);
        }
    }

    // Ejecutar al cargar y al cambiar
    actualizarCamposSegunTipoPersona();
    $('input[name="nuevoTipoPersona"], input[name="editarTipoPersona"]').on('change', actualizarCamposSegunTipoPersona);

    /*=============================================
    MOSTRAR/OCULTAR DÍGITO DE VERIFICACIÓN SEGÚN TIPO DE DOCUMENTO
    =============================================*/
    function actualizarCamposSegunTipoDocumento() {
        var tipoDocumento = $('#tipoDocumento').val();

        if (tipoDocumento == '6') { // NIT (ID 6 in DB)
            $('#contenedorDV').show();
            $('#digitoVerificacion').prop('required', true);

            // Habilitar opción Persona Jurídica
            $('input[name="nuevoTipoPersona"][value="juridica"], input[name="editarTipoPersona"][value="juridica"]')
                .prop('disabled', false)
                .parent().removeClass('disabled');
        } else {
            $('#contenedorDV').hide();
            $('#digitoVerificacion').prop('required', false);
            $('#digitoVerificacion').val('');

            // Deshabilitar opción Persona Jurídica y forzar Persona Natural
            $('input[name="nuevoTipoPersona"][value="juridica"], input[name="editarTipoPersona"][value="juridica"]')
                .prop('disabled', true)
                .prop('checked', false)
                .parent().addClass('disabled');

            // Seleccionar automáticamente Persona Natural
            $('input[name="nuevoTipoPersona"][value="natural"], input[name="editarTipoPersona"][value="natural"]')
                .prop('checked', true)
                .trigger('change');
        }
    }

    // Ejecutar al cargar y al cambiar
    actualizarCamposSegunTipoDocumento();
    $('#tipoDocumento').on('change', actualizarCamposSegunTipoDocumento);

    /*=============================================
    VALIDACIÓN DEL FORMULARIO
    =============================================*/
    var formValidado = false;

    $('#formCliente').on('submit', function (e) {
        // Si ya pasó la validación AJAX, dejar pasar el evento
        if (formValidado) {
            return true;
        }

        // Validar que si es NIT, tenga dígito de verificación
        var tipoDocumento = $('#tipoDocumento').val();
        var digitoVerificacion = $('#digitoVerificacion').val();

        if (tipoDocumento == '6' && !digitoVerificacion) {
            e.preventDefault();
            swal({
                title: "Error",
                text: "El dígito de verificación es obligatorio para NIT",
                icon: "error",
                confirmButtonText: "Cerrar"
            });
            return false;
        }

        // Validar que si es persona jurídica, tenga razón social
        var tipoPersona = $('input[name="nuevoTipoPersona"]:checked, input[name="editarTipoPersona"]:checked').val();
        var razonSocial = $('#razonSocial').val();

        if (tipoPersona === 'juridica' && !razonSocial) {
            e.preventDefault();
            swal({
                title: "Error",
                text: "La razón social es obligatoria para personas jurídicas",
                icon: "error",
                confirmButtonText: "Cerrar"
            });
            return false;
        }

        // Validar email si está presente
        var email = $('input[name="nuevoEmail"], input[name="editarEmail"]').val();
        if (email && !validarEmail(email)) {
            e.preventDefault();
            swal({
                title: "Error",
                text: "El formato del email no es válido",
                icon: "error",
                confirmButtonText: "Cerrar"
            });
            return false;
        }

        // Validar duplicados vía AJAX
        e.preventDefault();

        // DEBUG: Verificar valor del municipio antes de enviar
        var municipioSelect = document.querySelector('select[name="editarMunicipio"], select[name="nuevoMunicipio"]');
        console.log("=== DEBUG MUNICIPIO ===");
        console.log("Select encontrado:", municipioSelect);
        console.log("Valor del select:", municipioSelect ? municipioSelect.value : "NO ENCONTRADO");
        console.log("Texto seleccionado:", municipioSelect ? municipioSelect.options[municipioSelect.selectedIndex].text : "NO ENCONTRADO");

        var documento = $('#documento').val();
        var telefono = $('#telefono').val();
        var idCliente = $('input[name="idCliente"]').val();

        var datos = new FormData();
        datos.append("validarDocumento", documento);
        // csrf_token removido - manejado por csrf-helper.js
        datos.append("validarTelefono", telefono);
        if (idCliente) {
            datos.append("idClienteValidacion", idCliente);
        }

        $.ajax({
            url: "ajax/clientes.ajax.php",
            method: "POST",
            data: datos,
            cache: false,
            contentType: false,
            processData: false,
            dataType: "json",
            success: function (respuesta) {
                if (respuesta && respuesta.existe) {
                    swal({
                        title: "¡Advertencia!",
                        text: respuesta.mensaje,
                        icon: "warning",
                        confirmButtonText: "Cerrar"
                    });
                } else {
                    // DEBUG: Verificar valor del municipio antes del segundo submit
                    console.log("=== ANTES DEL SEGUNDO SUBMIT ===");
                    console.log("Valor del municipio:", municipioSelect ? municipioSelect.value : "NO ENCONTRADO");

                    // Marcar como validado y reenviar
                    formValidado = true;
                    $('#formCliente').submit();
                }
            },
            error: function (jqXHR, textStatus, errorThrown) {
                console.error("Error en validación AJAX:", textStatus, errorThrown);
                swal({
                    title: "Error técnico",
                    text: "No se pudo validar la información. Detalle: " + jqXHR.status + " " + errorThrown + (jqXHR.responseText ? "\n" + jqXHR.responseText.substring(0, 100) : ""),
                    icon: "error",
                    confirmButtonText: "Cerrar"
                });
            }
        });
    });

    /*=============================================
    FUNCIÓN AUXILIAR PARA VALIDAR EMAIL
    =============================================*/
    function validarEmail(email) {
        var regex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return regex.test(email);
    }

    /*=============================================
    CALCULAR DÍGITO DE VERIFICACIÓN (OPCIONAL)
    =============================================*/
    $('#documento').on('blur', function () {
        var tipoDocumento = $('#tipoDocumento').val();
        var documento = $(this).val();

        // Solo calcular si es NIT (ID 3) y tiene documento
        if (tipoDocumento == '6' && documento && documento.length > 0) {
            var dv = calcularDigitoVerificacion(documento);
            if (dv !== null) {
                $('#digitoVerificacion').val(dv);
            }
        }
    });

    /*=============================================
    FUNCIÓN PARA CALCULAR DÍGITO DE VERIFICACIÓN
    Algoritmo estándar de la DIAN para Colombia
    =============================================*/
    function calcularDigitoVerificacion(nit) {
        if (!nit || nit.length == 0) return null;

        var vpri = [3, 7, 13, 17, 19, 23, 29, 37, 41, 43, 47, 53, 59, 67, 71];
        var x = 0;
        var y = 0;
        var z = nit.length;

        for (var i = 0; i < z; i++) {
            y = parseInt(nit.substr(i, 1));
            x += (y * vpri[z - i - 1]);
        }

        y = x % 11;

        return (y > 1) ? 11 - y : y;
    }

    /*=============================================
    TOOLTIP PARA AYUDA
    =============================================*/
    $('[data-toggle="tooltip"]').tooltip();

    /*=============================================
    INPUT MASK PARA TELÉFONO
    =============================================*/
    $('[data-mask]').inputmask();

});
