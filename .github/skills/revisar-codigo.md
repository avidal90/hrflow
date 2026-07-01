---
name: Revisar Código
description: Revisa código del proyecto para detectar riesgos reales, defectos funcionales y problemas de mantenibilidad con un enfoque práctico.
---

# Revisar Código

## Objetivo

Este Skill tiene como objetivo realizar revisiones de código enfocadas en calidad, seguridad y coherencia arquitectónica.

Debe priorizar hallazgos accionables y evitar comentarios subjetivos sin impacto técnico.

---

# Filosofía

La revisión debe centrarse en:

- Defectos reales.
- Riesgos de regresión.
- Seguridad y autorización.
- Mantenibilidad del código.

Si no hay hallazgos relevantes, indícalo explícitamente.

---

# Reglas obligatorias

Cuando la revisión requiera generar un reporte en archivo:

- Debe guardarse en la carpeta /docs.
- Debe usar formato .md.

No crear reportes fuera de /docs ni en otro formato.

---

# Flujo de trabajo

## 1. Comprender el alcance

Define:

- Qué archivos o módulo se revisan.
- Qué cambio funcional se esperaba.
- Qué riesgos son más probables.

---

## 2. Inspeccionar cambios

Revisa:

- Lógica de negocio.
- Validaciones y autorizaciones.
- Integridad de datos.
- Coherencia con patrones existentes.

---

## 3. Evaluar impacto

Para cada hallazgo, determina:

- Severidad.
- Probabilidad de fallo.
- Alcance de impacto.
- Recomendación mínima de corrección.

---

## 4. Verificar pruebas

Comprueba:

- Si hay cobertura para el cambio.
- Si faltan tests de regresión.
- Si hay rutas críticas sin validar.

---

## 5. Consolidar resultado

Entrega un resultado ordenado por severidad y con referencias claras para facilitar corrección rápida.

---

# Criterios de revisión

Prioriza revisión sobre:

- Errores funcionales.
- Fallos de seguridad.
- Violaciones de aislamiento tenant.
- Validaciones insuficientes.
- Consultas ineficientes evitables.
- Complejidad innecesaria.

---

# Restricciones

No incluir:

- Opiniones de estilo sin impacto real.
- Recomendaciones de sobreingeniería.
- Cambios de arquitectura no solicitados.
- Críticas sin propuesta concreta.

---

# Buenas prácticas

Siempre intenta:

- Explicar el problema con precisión.
- Proponer correcciones simples.
- Priorizar quick wins de alto impacto.
- Mantener un tono técnico y objetivo.

---

# Formato de salida

Al finalizar, responde con:

## Resultado general

Resumen breve del estado del código revisado.

---

## Hallazgos

Listado ordenado por severidad.

Para cada hallazgo incluir:

- Severidad.
- Archivo o área afectada.
- Problema detectado.
- Riesgo asociado.
- Recomendación concreta.

---

## Cobertura de pruebas

Breve estado de tests existentes y faltantes.

---

## Riesgos residuales

Problemas no resueltos o pendientes de validación.

---

## Reporte en archivo (si aplica)

Ruta final siempre en /docs y con extensión .md.
