---
name: Implementar Resource
description: Implementa un Filament Resource completo y coherente, reutilizando capacidades nativas de Filament y respetando las convenciones del proyecto.
---

# Implementar Resource

## Objetivo

Este Skill tiene como objetivo implementar o modificar un Resource de Filament para administración interna de forma clara, segura y mantenible.

Debe resolver el caso solicitado sin añadir complejidad fuera del alcance.

---

# Filosofía

Al trabajar con un Resource:

- Prioriza funcionalidades nativas de Filament.
- Mantén formularios y tablas simples de usar.
- Evita lógica de negocio pesada dentro del Resource.
- Reutiliza relaciones y validaciones ya existentes.

Si existen dos soluciones posibles, elige la más simple y explícita.

---

# Flujo de trabajo

## 1. Entender el caso de uso

Identifica:

- Qué entidad administra el Resource.
- Qué operaciones necesita el usuario.
- Qué restricciones de acceso aplican.

---

## 2. Revisar estructura existente

Antes de implementar revisa:

- Modelo y relaciones.
- Policies y permisos.
- Resources similares ya implementados.
- Convenciones de nombres y organización.

---

## 3. Diseñar formulario

Define solo campos necesarios.

Para cada campo verifica:

- Tipo de componente adecuado.
- Validaciones mínimas requeridas.
- Experiencia de uso en create/edit.

Evita formularios sobrecargados.

---

## 4. Diseñar tabla

Incluye únicamente columnas y acciones con valor real.

Prioriza:

- Columnas legibles.
- Filtros útiles.
- Búsqueda en campos relevantes.
- Ordenación donde tenga sentido.

---

## 5. Integrar relaciones

Cuando aplique, usa componentes nativos para relaciones y evita lógica manual innecesaria.

Comprueba siempre:

- Consistencia de claves foráneas.
- Carga eficiente de datos.
- Restricciones por tenant.

---

## 6. Seguridad

Verifica siempre:

- Autorización por Policy.
- Restricción de datos por tenant.
- Protección frente a acciones no permitidas.
- Reglas de validación de entrada.

---

## 7. Revisar resultado

Antes de cerrar:

- El flujo create/edit/list funciona.
- No hay acciones expuestas sin permiso.
- El código es claro y mantenible.
- El Resource mantiene el estilo del proyecto.

---

# Restricciones

No introducir en un Resource:

- Patrones enterprise innecesarios.
- Lógica de negocio compleja embebida.
- Componentes personalizados sin necesidad real.
- Integraciones externas no solicitadas.

---

# Buenas prácticas

Siempre intenta:

- Mantener formularios cortos y claros.
- Mostrar información útil en tabla.
- Reutilizar scopes y relaciones del modelo.
- Mantener consistencia visual con otros Resources.

---

# Formato de salida

Al finalizar, responde con:

## Resource implementado

Nombre del Resource y objetivo.

---

## Archivos creados

Lista de archivos nuevos.

---

## Archivos modificados

Lista de archivos actualizados.

---

## Componentes del formulario

Resumen de campos y validaciones clave.

---

## Componentes de la tabla

Resumen de columnas, filtros y acciones.

---

## Seguridad aplicada

Policies, permisos y reglas de aislamiento aplicadas.

---

## Trabajo pendiente

Tareas no incluidas y motivo.
