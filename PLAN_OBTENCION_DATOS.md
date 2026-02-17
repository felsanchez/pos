# Plan de Obtención y Sincronización de Datos Factus

## Objetivo
Configurar el sistema POS para que sincronice automáticamente los datos maestros de Factus (municipios, unidades, rangos) y configure manualmente aquellos que son estáticos o no tienen endpoint público claro (tributos, formas de pago).

## 1. Datos a Sincronizar Automáticamente (Endpoints Disponibles)
Estos datos se actualizarán mediante tareas programadas (Cron Jobs) o botón manual en el panel de configuración.

| Recurso | Endpoint | Estrategia de Sincronización |
| :--- | :--- | :--- |
| **Municipios** | `/v1/municipalities` | **Full Sync**: Descargar todos y guardar en tabla `factus_municipios`. Se usa 'code' e 'id'. |
| **Unidades de Medida** | `/v1/measurement-units` | **Full Sync**: Guardar en `factus_unidades_medida` para mapear con productos. |
| **Rangos de Numeración** | `/v1/numbering-ranges` | **Critical Sync**: Obtener el ID activo (ej. `8`) y guardarlo en `factus_config` automáticamente. |
| **Países** | `/v1/countries` | **On Demand**: Descargar una vez al configurar, raramente cambia. |

## 2. Datos de Configuración Estática (Sin Endpoint Público Claro)
Basado en las pruebas, estos recursos retornan 404, por lo que se recomienda usar los estándares DIAN definidos en la documentación oficial.

### A. Tributos (Impuestos)
Se debe crear una tabla semilla (`seed`) con los códigos DIAN estándar:
*   **01**: IVA
*   **04**: INC (Impuesto Nacional al Consumo)
*   **03**: ICA

### B. Formas de Pago (`payment_form`)
Valores fijos requeridos por la API:
*   **1**: Contado
*   **2**: Crédito

### C. Medios de Pago (`payment_method_code`)
Lista estándar DIAN (tabla local):
*   **10**: Efectivo
*   **31**: Transferencia Débito
*   **41**: Cheque
*   **47**: Transferencia Crédito

### D. Tipos de Identificación
*   **13**: Cédula de ciudadanía
*   **31**: NIT
*   **41**: Pasaporte
*   **42**: Documento de identificación extranjero

## 3. Estrategia de Implementación

### Fase 1: Tablas de Base de Datos
1.  Crear tablas para `factus_municipios`, `factus_unidades`, `factus_tributos` (ya existente), `factus_rangos`.
2.  Poblar tablas estáticas (Tributos, Medios de Pago) via SQL INSERT.

### Fase 2: Módulo de Configuración (`configuracion-factus`)
1.  Añadir botones "Sincronizar Municipios" y "Sincronizar Unidades".
2.  Añadir selector para "Rango de Numeración Activo" llenado desde la API.

### Fase 3: Mapeo en Productos y Clientes
1.  **Clientes**: Campo `municipio_id` debe ser un select buscador sobre `factus_municipios`.
2.  **Productos**: Campos `unidad_medida` y `tributo` deben ser selects sobre sus respectivas tablas sync/estáticas.

## Script Sugerido para Sincronización Incial
Se puede extender `test_reference_data.php` para que realice los `INSERT` en la base de datos inmediatamente.
