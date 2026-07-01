---
name: Implementar Módulo
description: Implementa un módulo completo de HRFlow siguiendo la arquitectura del proyecto, las convenciones Laravel y la filosofía de desarrollo establecida.
---

# Implementar Módulo

## Objetivo

Este Skill tiene como objetivo implementar un módulo funcional completo de forma simple, coherente y mantenible.

Debe generar únicamente el código necesario para cumplir los requisitos funcionales solicitados, respetando siempre la arquitectura existente.

---

# Filosofía

Todo módulo debe cumplir:

- KISS
- DRY
- SOLID (de forma pragmática)
- Clean Code
- Security by Design

Si existen dos soluciones técnicamente válidas:

**Siempre elige la más simple.**

---

# Flujo de trabajo

Antes de implementar cualquier funcionalidad sigue este proceso.

## 1. Comprender el objetivo

Analiza:

- Qué funcionalidad debe implementarse.
- Qué problema resuelve.
- Qué usuarios participan.
- Qué módulos existentes pueden reutilizarse.

No empieces a programar inmediatamente.

---

## 2. Analizar el proyecto

Inspecciona previamente:

- Modelos existentes.
- Migraciones.
- Policies.
- Resources.
- Componentes Livewire.
- Convenciones del proyecto.
- Estructura del dominio.

No dupliques funcionalidades ya existentes.

---

## 3. Planificar la implementación

Determina qué elementos deben desarrollarse.

Por ejemplo:

- Migración.
- Modelo.
- Relaciones.
- Policy.
- Form Request.
- Resource de Filament.
- Página Livewire.
- Tests.

Implementa únicamente los necesarios.

---

## 4. Implementar

Genera código siguiendo las convenciones Laravel.

Prioriza:

- Eloquent.
- Form Requests.
- Policies.
- Validaciones nativas.
- Relaciones bien definidas.

Evita crear clases adicionales cuando Laravel ya proporciona una solución.

---

## 5. Revisar

Antes de finalizar verifica:

- El código compila.
- Los nombres son coherentes.
- No existe código duplicado.
- No se ha roto la arquitectura existente.
- No se ha introducido complejidad innecesaria.

---

# Laravel

Siempre prioriza:

- Eloquent ORM
- Resource Controllers cuando sean necesarios
- Form Requests
- Policies
- Notifications
- Jobs únicamente cuando aporten valor
- Eventos únicamente cuando simplifiquen el diseño

Evita introducir:

- Repositories
- DTOs
- Interfaces
- Managers
- Helpers globales
- Patrones Enterprise

Salvo petición expresa del usuario.

---

# Filament

Para cualquier módulo administrativo utiliza siempre Filament.

Prioriza:

- Resources
- Tables
- Forms
- Relation Managers
- Actions
- Widgets

No reinventes funcionalidades ya incluidas en Filament.

---

# Livewire

Cuando el módulo pertenezca al Portal del Empleado:

Utiliza:

- Blade
- Livewire
- Tailwind CSS

Evita JavaScript innecesario.

No propongas React, Vue o Angular salvo petición expresa.

---

# Seguridad

Comprueba siempre:

- Validaciones.
- Policies.
- Mass Assignment.
- Tenant Isolation.
- Protección de documentos.
- Restricciones de acceso.

Nunca expongas información de otro tenant.

---

# Multi-tenancy

Todas las entidades deben pertenecer a un tenant.

Comprueba:

- Relaciones.
- Consultas.
- Permisos.
- Propiedad de los datos.

El aislamiento entre empresas es obligatorio.

---

# Calidad del código

El código generado debe ser:

- Claro.
- Legible.
- Mantenible.
- Fácil de explicar.
- Fácil de ampliar.

Prefiere varios métodos pequeños frente a uno muy complejo.

Cada clase debe tener una única responsabilidad.

---

# Restricciones

No implementes:

- Funcionalidades futuras.
- Integraciones externas.
- Complejidad innecesaria.
- Arquitecturas Enterprise.
- Configuraciones excesivamente parametrizables.

El objetivo es construir un MVP profesional.

---

# Buenas prácticas

Siempre intenta:

- Reutilizar código existente.
- Aprovechar Laravel.
- Mantener coherencia visual.
- Mantener coherencia arquitectónica.

---

# Validación final

Antes de finalizar responde internamente a estas preguntas:

- ¿Existe una solución más simple?
- ¿Laravel ofrece una alternativa nativa?
- ¿He creado clases innecesarias?
- ¿La funcionalidad será fácil de demostrar?
- ¿La implementación respeta la filosofía del proyecto?

Si alguna respuesta es negativa, simplifica la implementación.

---

# Formato de salida

Al finalizar resume:

## Funcionalidad implementada

Descripción breve.

---

## Archivos creados

Lista de nuevos archivos.

---

## Archivos modificados

Lista de archivos modificados.

---

## Decisiones tomadas

Explica brevemente las decisiones arquitectónicas relevantes.

---

## Validaciones aplicadas

Indica las validaciones implementadas.

---

## Seguridad

Describe las medidas de autorización y protección aplicadas.

---

## Trabajo pendiente

Indica únicamente aquello que realmente haya quedado fuera del alcance.

---

# Regla de oro

Implementa únicamente aquello que aporte valor al proyecto.

No desarrolles funcionalidades pensando en necesidades futuras.

El código debe transmitir profesionalidad mediante su claridad, no mediante su complejidad.

Recuerda siempre:

HRFlow es una demostración técnica.

La mejor implementación será aquella que cualquier desarrollador Laravel pueda comprender en pocos minutos y que pueda explicarse fácilmente durante la defensa del proyecto.
