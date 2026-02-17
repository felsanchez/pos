/**
 * Script JavaScript para incluir token CSRF en todas las peticiones AJAX
 * 
 * Este script debe ser incluido en la plantilla principal para que
 * todas las peticiones AJAX incluyan automáticamente el token CSRF
 */

$(document).ready(function () {

    // Obtener token CSRF del meta tag
    var csrfToken = $('meta[name="csrf-token"]').attr('content');

    if (!csrfToken) {
        console.warn('⚠️ Token CSRF no encontrado en meta tag');
        return;
    }

    // Configurar AJAX para incluir token CSRF en todas las peticiones
    $.ajaxSetup({
        beforeSend: function (xhr, settings) {
            // Solo agregar token en peticiones POST
            if (settings.type === 'POST') {

                // Si los datos son FormData, agregar el token
                if (settings.data instanceof FormData) {
                    settings.data.append('csrf_token', csrfToken);
                }
                // Si los datos son un objeto, agregar el token
                else if (typeof settings.data === 'object' && settings.data !== null) {
                    settings.data.csrf_token = csrfToken;
                }
                // Si los datos son string, agregar el token
                else if (typeof settings.data === 'string') {
                    settings.data += (settings.data ? '&' : '') + 'csrf_token=' + encodeURIComponent(csrfToken);
                }
                // Si no hay datos, crear objeto con token
                else if (!settings.data) {
                    settings.data = { csrf_token: csrfToken };
                }
            }
        }
    });

    console.log('✅ Protección CSRF activada para peticiones AJAX');
});
