---
name: Implementar Livewire
description: Implementa funcionalidades en Livewire para el portal del empleado con enfoque simple, seguro y coherente con la arquitectura del proyecto.
---

# Implementar Livewire

## Objetivo

Este Skill tiene como objetivo implementar o modificar componentes Livewire orientados a flujos del portal del empleado.

Debe entregar experiencias claras y mantenibles, minimizando complejidad técnica.

---

# Filosofía

En componentes Livewire:

- Prioriza simplicidad en estado y acciones.
- Mantén reglas de validación explícitas.
- Evita lógica de dominio pesada en la vista.
- Reutiliza servicios y modelos existentes.

Si una solución requiere JavaScript complejo, revisa primero si Livewire ya la cubre de forma nativa.

---

# Flujo de trabajo

## 1. Entender la interacción

Define claramente:

- Qué tarea realiza el usuario.
- Qué datos visualiza o modifica.
- Qué acciones ejecuta en pantalla.

---

## 2. Revisar componentes existentes

Antes de crear código nuevo revisa:

- Componentes Livewire similares.
- Blade views relacionadas.
- Rutas y navegación asociadas.
- Policies y reglas de acceso.

---

## 3. Diseñar estado y acciones

Mantén un estado mínimo y bien nombrado.

Para cada acción define:

- Entradas.
- Validación.
- Efecto esperado.
- Mensaje de feedback al usuario.

---

## 4. Implementar UI con Blade + Tailwind

Construye la interfaz con foco en claridad.

Prioriza:

- Estructura legible.
- Inputs y acciones consistentes.
- Mensajes de error comprensibles.
- Estados de carga donde sea necesario.

---

## 5. Seguridad

Valida siempre:

- Autorización antes de leer o escribir datos.
- Restricción de alcance por tenant.
- Protección de datos sensibles en la vista.
- Reglas de validación en todas las acciones.

---

## 6. Revisar comportamiento

Antes de cerrar verifica:

- El flujo funciona de inicio a fin.
- Los errores se muestran correctamente.
- El componente no mezcla demasiadas responsabilidades.
- La solución sigue convenciones del proyecto.

---

# Restricciones

No introducir:

- Frameworks frontend adicionales.
- JavaScript complejo sin necesidad.
- Lógica de negocio central en Blade.
- Abstracciones innecesarias para un flujo simple.

---

# Buenas prácticas

Siempre intenta:

- Dividir lógica compleja en métodos pequeños.
- Mantener nombres de propiedades y métodos descriptivos.
- Reutilizar validaciones y reglas ya definidas.
- Mantener experiencia de usuario consistente.

---

# Formato de salida

Al finalizar, responde con:

## Funcionalidad Livewire implementada

Descripción breve del flujo.

---

## Archivos creados

Lista de archivos nuevos.

---

## Archivos modificados

Lista de archivos actualizados.

---

## Estado y acciones del componente

Resumen de propiedades, métodos y validaciones.

---

## Consideraciones de UI/UX

Decisiones principales de interacción y feedback.

---

## Seguridad aplicada

Autorización, aislamiento tenant y protección de datos.

---

## Trabajo pendiente

Tareas no incluidas y motivo.
