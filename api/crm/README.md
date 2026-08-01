# CRM - KONTROL POS

## Objetivo

Este módulo es responsable de administrar el CRM del sistema.

No contiene lógica relacionada con el Agente de Ventas ni con el catálogo de productos.

## Responsabilidades

- Sincronizar clientes del CRM.
- Crear y actualizar Leads.
- Gestionar el Pipeline.
- Generar resúmenes de IA.
- Gestionar seguimientos.

## Arquitectura

controllers/
    Reciben las peticiones HTTP.

models/
    Contienen toda la lógica de negocio.

tests/
    Pruebas independientes de cada modelo.

## Regla

Toda la lógica debe implementarse en los Models.

Los Controllers únicamente reciben datos, llaman al Model y devuelven la respuesta en JSON.

Nunca implementar lógica de negocio en los Controllers.

## Flujo

n8n

↓

Controllers

↓

Models

↓

MySQL