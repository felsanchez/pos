with open(r'c:\xampp\htdocs\pos\vistas\js\reportes-facturacion.js', 'r', encoding='utf-8') as f:
    lines = f.readlines()

good_block = (
    '    $("#btnLimpiarFiltrosReportes").click(function () {\r\n'
    '        // Restablecer fecha a los \u00faltimos 30 d\u00edas\r\n'
    '        fechaInicial = moment().subtract(29, \'days\').format(\'YYYY-MM-DD\');\r\n'
    '        fechaFinal = moment().format(\'YYYY-MM-DD\');\r\n'
    '        $("#daterange-btn-reportes span").html(\'<i class="fa fa-calendar"></i> Rango de fecha\');\r\n'
    '        localStorage.removeItem("capturarRangoReportes");\r\n'
    '\r\n'
    '        // Restablecer selects\r\n'
    '        $("#seleccionarCategoriaReporte").val("todos").trigger("change");\r\n'
    '        $("#seleccionarClienteReporte").val("todos").trigger("change");\r\n'
    '        $("#seleccionarProveedorReporte").val("todos").trigger("change");\r\n'
    '        if ($.fn.select2) {\r\n'
    '            $("#seleccionarUsuarioReporte").val(null).trigger("change");\r\n'
    '        } else {\r\n'
    '            $("#seleccionarUsuarioReporte").val("todos");\r\n'
    '        }\r\n'
    '\r\n'
    '        // Recargar datos sin filtros\r\n'
    '        cargarDashboard(fechaInicial, fechaFinal, "todos", "todos", "todos");\r\n'
    '    });\r\n'
)

# lines 87-106 are 0-indexed 86-105
new_lines = lines[:86] + [good_block] + lines[106:]

with open(r'c:\xampp\htdocs\pos\vistas\js\reportes-facturacion.js', 'w', encoding='utf-8') as f:
    f.writelines(new_lines)

print('Done. Total lines:', len(new_lines))
