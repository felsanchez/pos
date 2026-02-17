# Análisis de Integración Factus - Facturación Electrónica

## 1. Arquitectura del Sistema
La integración sigue un patrón **MVC (Modelo-Vista-Controlador)** bien estructurado:

*   **Base de Datos**:
    *   `factus_config`: Almacena credenciales (Client ID, Secret, Token) y configuración del entorno (Production/Sandbox).
    *   `factus_municipios`, `factus_tributos`: Tablas locales para caché de datos de referencia de Factus.
    *   `ventas`: Se han añadido campos (`cufe`, `qr_data`, `estado_dian`) para tracking.
*   **Modelo (`modelos/factus.modelo.php`)**:
    *   Encargado de la comunicación HTTP pura (cURL).
    *   Maneja endpoints clave: `/oauth/token`, `/v1/bills`, `/municipalities`.
*   **Controlador (`controladores/factus.controlador.php`)**:
    *   Orquestador de la lógica de negocio.
    *   `ctrGenerarFacturaElectronica`: Método principal que coordina obtención de datos, autenticación y envío.
    *   `prepararDatosFactura`: **(Punto Crítico)** Transforma los datos de la venta local al esquema JSON requerido por Factus.

## 2. Estado Actual
*   ✅ **Infraestructura**: Tablas y archivos base creados y funcionales.
*   ✅ **Autenticación**: El sistema obtiene y refresca tokens OAuth2 correctamente.
*   ❌ **Generación de Factura**: Fallando con error **HTTP 500 (Internal Server Error)**.
    *   Esto indica que aunque llegamos al servidor (no 404), el servidor rechaza el formato de los datos enviados (`JSON Payload`).
*   ❌ **Código**: Error de sintaxis actual en `controladores/factus.controlador.php` (líneas 440-450) que bloquea la ejecución.

## 3. Diagnóstico del Error 500
El error 500 en Factus usualmente se debe a datos mal formados o inválidos en el JSON enviado.

**Discrepancias Detectadas:**
1.  **Endpoints**: Se corrigió de `/v1/invoices` a `/v1/bills` basado en pruebas recientes.
2.  **Rangos de Numeración**: El sistema enviaba `1`, pero el ejemplo funcional usa `8`.
3.  **Tipos de Datos**: La API de Factus es estricta con tipos (Strings vs Integers).
    *   Ejemplo: `municipality_id` debe ser String ("980"), `numbering_range_id` Integer (8).

## 4. Conclusiones y Próximos Pasos
La integración está completa en un 90%. El "esqueleto" funciona, pero falla el "contenido" (el JSON exacto).

**Plan de Acción Inmediato:**
1.  **Corregir Sintaxis**: Eliminar el error de paréntesis/llaves en `factus.controlador.php` que bloquea las pruebas.
2.  **Auditoría de Payload**: Ejecutar el script `test_payload_debug.php` para extraer el JSON exacto que genera el sistema.
3.  **Refinamiento**: Ajustar `prepararDatosFactura` hasta que el JSON generado sea **idéntico estructura y tipos** al ejemplo de Postman validado.
