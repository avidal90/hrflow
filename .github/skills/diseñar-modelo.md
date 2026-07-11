---
name: Diseñar Modelo
description: Diseña el modelo de dominio de una funcionalidad definiendo entidades, relaciones, responsabilidades y reglas de negocio antes de su implementación.
---

# Diseñar Modelo

## Objetivo

Este Skill tiene como objetivo diseñar el modelo de dominio de una funcionalidad antes de comenzar su implementación.

Su misión consiste en transformar un requisito funcional en un conjunto de entidades, relaciones y responsabilidades bien definidas, manteniendo siempre una arquitectura limpia, sencilla y mantenible.

Este Skill **NO debe generar código** salvo que el usuario lo solicite expresamente.

---

# Contexto del proyecto

Estás desarrollando **HRFlow**, una plataforma SaaS de Recursos Humanos desarrollada como proyecto final de un curso de desarrollo con Inteligencia Artificial.

HRFlow es una demostración técnica.

No pretende convertirse en un ERP completo de Recursos Humanos.

El diseño del dominio debe ser:

- Profesional.
- Coherente.
- Fácil de mantener.
- Fácil de ampliar.
- Fácil de explicar.

Siempre debe evitarse la sobreingeniería.

---

# Filosofía

El modelo de dominio debe representar el negocio, no la tecnología.

Cada entidad debe existir porque aporta valor funcional.

No deben crearse entidades por anticipación.

Cada clase debe tener una única responsabilidad claramente definida.

La simplicidad siempre tiene prioridad.

---

# Objetivos del diseño

Para cada funcionalidad debes:

- Identificar las entidades.
- Definir sus responsabilidades.
- Diseñar las relaciones.
- Determinar la propiedad de los datos.
- Mantener un modelo normalizado.
- Evitar duplicidad.
- Facilitar futuras ampliaciones sin complicar el presente.

---

# Proceso de diseño

## 1. Identificar entidades

Determina todas las entidades necesarias.

Para cada una indica:

- Nombre
- Propósito
- Responsabilidad

No propongas entidades innecesarias.

---

## 2. Definir atributos

Para cada entidad define únicamente los atributos que aporten valor al negocio.

Evita añadir campos "por si acaso".

Cada atributo debe responder a una necesidad funcional.

---

## 3. Diseñar relaciones

Define claramente:

- belongsTo
- hasMany
- belongsToMany
- morphOne
- morphMany

cuando realmente sean necesarias.

Explica brevemente el motivo de cada relación.

---

## 4. Responsabilidades

Cada entidad debe cumplir una única responsabilidad.

Evita modelos "gigantes".

Si una entidad empieza a asumir demasiadas funciones, propón una separación lógica.

---

## 5. Reglas de negocio

Identifica las reglas principales.

Por ejemplo:

- Estados permitidos.
- Restricciones.
- Validaciones.
- Dependencias.
- Flujo de vida.

No implementes todavía la lógica.

Simplemente documenta las reglas.

---

## 6. Multi-tenancy

Comprueba siempre:

- ¿Quién es propietario de la información?
- ¿A qué empresa pertenece?
- ¿Debe aislarse por tenant?
- ¿Puede acceder otro tenant?

Todas las entidades deben respetar el aislamiento entre empresas.

---

## 7. Seguridad

Evalúa:

- Información sensible.
- Accesos.
- Policies necesarias.
- Restricciones de lectura.
- Restricciones de escritura.

---

## 8. Escalabilidad

Valora únicamente la escalabilidad razonable para un proyecto académico.

No diseñes para millones de registros.

No propongas optimizaciones prematuras.

---

# Restricciones

Nunca propongas:

- Event Sourcing.
- CQRS.
- Arquitecturas distribuidas.
- DDD complejo.
- Agregados sofisticados.
- Value Objects innecesarios.
- DTOs.
- Repositories.
- Interfaces sin utilidad inmediata.

Laravel ya proporciona un modelo de dominio excelente mediante Eloquent.

Aprovéchalo.

---

# Buenas prácticas

Siempre intenta:

- Reducir el número de entidades.
- Reducir el número de relaciones.
- Reutilizar modelos existentes.
- Mantener nombres claros.
- Utilizar terminología del negocio.
- Diseñar entidades fáciles de comprender.

---

# Validación del diseño

Antes de finalizar comprueba:

- ¿Existe alguna entidad innecesaria?
- ¿Hay atributos duplicados?
- ¿Las relaciones son coherentes?
- ¿Cada entidad tiene una única responsabilidad?
- ¿Puede simplificarse el modelo?

Si la respuesta es sí, simplifica el diseño.

---

# Formato de respuesta

Responde siempre utilizando la siguiente estructura.

## Resumen

Descripción general del modelo.

---

## Entidades

Para cada entidad indicar:

- Nombre
- Responsabilidad
- Atributos principales

---

## Relaciones

Lista de relaciones entre entidades.

Indicar cardinalidad.

---

## Reglas de negocio

Resumen de las reglas más importantes.

---

## Consideraciones de seguridad

Aspectos relacionados con autorización y protección de datos.

---

## Multi-tenancy

Cómo afecta el aislamiento entre empresas.

---

## Complejidad

Valoración:

- Muy baja
- Baja
- Media
- Alta

Justificación.

---

## Recomendaciones

Buenas prácticas específicas para este modelo.

---

# Regla de oro

El mejor modelo de dominio no es el más complejo.

Es aquel que consigue representar correctamente el negocio utilizando el menor número posible de entidades, relaciones y reglas.

Cada entidad debe ser fácilmente comprensible por cualquier desarrollador Laravel.

Si una decisión arquitectónica requiere una explicación extensa para justificarse, probablemente sea demasiado compleja para el alcance de HRFlow.
