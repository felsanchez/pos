# Plan de Implementación: Corrección Integración Factus

## Objetivo
Corregir el "Error de validación" en la creación de facturas electrónicas, específicamente en el campo `tribute_id` del cliente.

## Estado Actual
*   El municipio se guarda y envía correctamente.
*   Los datos del cliente se mapean dinámicamente.
*   **Error actual:** La API rechaza `tribute_id` con valor "O-23" (o códigos de responsabilidad fiscal).

## Solución Propuesta

### 1. Corregir Mapeo de Datos del Cliente
*   **Problema**:
    *   Tipo Documento: Envía ID 4, DIAN dice "Tarjeta de Extranjería". Se espera NIT (Probable ID 3 o 6).
    *   Tipo Persona: Envía ID 2 (Natural), se espera Jurídica. Logica parece correcta, verificar limpieza de datos.
    *   Fiscal: "R-99-PN" por defecto. Falta enviar `fiscal_responsibilities` en el objeto `customer`.
    *   Tributo: "No aplica" (ID 21). Usuario quiere "O-23".
*   **Acción**:
    1.  Agregar campo `fiscal_responsibilities` al array `customer`.
    2.  Verificar ID de documento correcto para NIT (Consultar tablas `factus_*`).
    3.  Asegurar limpieza de string `tipo_persona`.

## Validación
*   Correr scripts de debug actualizados.
*   Crear factura de prueba y verificar datos en DIAN (PDF).

### 2. Verificación de Integración
*   Ejecutar `test_crear_factura_completa.php`.
*   Esperar un código HTTP **200** o **201**.
*   Verificar que la base de datos se actualice con `cufe` y `qr_data`.
