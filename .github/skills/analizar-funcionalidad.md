---
name: Analizar Funcionalidad
description: Analiza una nueva funcionalidad antes de implementarla, definiendo objetivos, impacto en el dominio, arquitectura y estrategia de desarrollo.
---

# Analizar Funcionalidad

## Objetivo

Este Skill tiene como objetivo analizar una funcionalidad antes de comenzar cualquier implementación.

Su misión consiste en comprender completamente el problema funcional, estudiar el impacto sobre la arquitectura existente y proponer la solución más sencilla posible alineada con la filosofía del proyecto.

Este Skill **NO debe generar código** salvo que el usuario lo solicite expresamente.

---

# Contexto del proyecto

Estás trabajando en **HRFlow**, una plataforma SaaS de Recursos Humanos desarrollada como proyecto final de un curso de desarrollo con Inteligencia Artificial.

HRFlow es una **demostración técnica**, no un producto comercial.

El objetivo es demostrar conocimientos sobre:

- Laravel
- Filament
- Livewire
- Arquitectura de software
- Buenas prácticas
- Seguridad
- Organización del código

No debe intentarse reproducir toda la complejidad de una aplicación empresarial real.

---

# Filosofía

Antes de analizar cualquier funcionalidad recuerda siempre:

- La solución más simple suele ser la mejor.
- No diseñes pensando en requisitos futuros.
- No propongas funcionalidades que el usuario no ha solicitado.
- Evita cualquier tipo de sobreingeniería.
- Aprovecha siempre las funcionalidades nativas de Laravel.

La arquitectura debe parecer profesional, pero mantenerse sencilla y fácilmente explicable.

---

# Proceso de análisis

Para cada funcionalidad sigue exactamente el siguiente proceso.

## 1. Comprender el objetivo funcional

Explica brevemente:

- Qué problema resuelve.
- Qué necesidad cubre.
- Qué usuarios la utilizarán.
- Qué valor aporta al sistema.

No hagas suposiciones innecesarias.

---

## 2. Identificar el dominio

Determina:

- Qué entidades participan.
- Qué entidades ya existen.
- Qué entidades nuevas serían necesarias.
- Qué relaciones aparecen entre ellas.

Si una entidad ya existe, reutilízala.

No dupliques responsabilidades.

---

## 3. Analizar impacto arquitectónico

Evalúa qué componentes del proyecto deberán modificarse.

Por ejemplo:

- Modelos
- Migraciones
- Policies
- Form Requests
- Filament Resources
- Livewire
- Blade
- Servicios
- Jobs
- Eventos

Incluye únicamente aquellos que realmente sean necesarios.

---

## 4. Seguridad

Analiza siempre:

- Validaciones necesarias.
- Permisos.
- Policies.
- Aislamiento entre tenants.
- Protección frente a acceso no autorizado.
- Riesgos relacionados con documentos o información sensible.

La seguridad forma parte del análisis funcional.

---

## 5. Riesgos

Identifica posibles riesgos como:

- Duplicidad de datos.
- Relaciones complejas.
- Validaciones especiales.
- Integridad referencial.
- Experiencia de usuario.
- Dependencias con otros módulos.

No propongas soluciones todavía.

Simplemente identifica los riesgos.

---

## 6. Complejidad

Valora la complejidad de implementación.

Clasifica la funcionalidad como:

- Muy baja
- Baja
- Media
- Alta

Justifica brevemente la clasificación.

---

## 7. Estrategia de implementación

Propón un orden lógico de desarrollo.

Por ejemplo:

1. Migración
2. Modelo
3. Relaciones
4. Policy
5. Resource de Filament
6. Página Livewire
7. Tests

Siempre en pequeños pasos.

Nunca propongas implementar todo simultáneamente.

---

# Restricciones

Nunca propongas:

- Microservicios.
- CQRS.
- Event Sourcing.
- DDD complejo.
- Arquitecturas distribuidas.
- Patrones Enterprise.
- Capas adicionales sin necesidad.
- Integraciones externas no solicitadas.

La solución debe ser proporcional al alcance del proyecto.

---

# Buenas prácticas

Durante el análisis intenta siempre:

- Reutilizar componentes existentes.
- Reducir duplicidad.
- Minimizar el número de clases.
- Mantener responsabilidades claras.
- Favorecer la mantenibilidad.

Si existen dos soluciones válidas, elige siempre la más sencilla.

---

# Formato de respuesta

Responde utilizando siempre la siguiente estructura.

## Resumen

Explicación breve de la funcionalidad.

---

## Objetivo de negocio

¿Qué necesidad resuelve?

---

## Usuarios implicados

¿Qué perfiles utilizarán esta funcionalidad?

---

## Entidades implicadas

Lista de modelos involucrados.

---

## Impacto sobre la arquitectura

Qué componentes deberán desarrollarse o modificarse.

---

## Riesgos identificados

Problemas que deben tenerse en cuenta durante el desarrollo.

---

## Nivel de complejidad

Muy baja / Baja / Media / Alta

Justificación.

---

## Plan de implementación

Lista ordenada de tareas recomendadas.

---

## Recomendaciones

Buenas prácticas específicas para esta funcionalidad.

---

# Regla de oro

Antes de finalizar el análisis, verifica siempre lo siguiente:

- ¿Existe una solución más simple?
- ¿Laravel ya ofrece una solución nativa?
- ¿Se está evitando la sobreingeniería?
- ¿La implementación será fácil de mantener?
- ¿La funcionalidad podrá explicarse fácilmente durante la presentación del proyecto?

Si alguna respuesta es negativa, simplifica la propuesta antes de responder.

Recuerda:

El objetivo de HRFlow no es desarrollar el software de Recursos Humanos más completo.

El objetivo es construir una aplicación profesional, coherente y mantenible que demuestre buenas prácticas de ingeniería del software.
