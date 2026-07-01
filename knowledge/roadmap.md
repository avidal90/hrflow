# HRFlow - Roadmap academica (simulada + entrega real)

## 1. Objetivo

Definir una planificacion con doble utilidad:
- Mostrar una roadmap profesional simulada (6 semanas) para posicionamiento del proyecto.
- Guiar una entrega academica realista y usable con fecha limite 20/07/2026.

## 2. Supuestos

- Fase previa documental cerrada en baseline v1.
- El objetivo es una entrega academica profesional y usable, no una salida comercial completa.
- Se priorizan modulos Must sobre cobertura total de alcance empresarial.

## 3. Hoja profesional simulada (6 semanas)

## Semana 1 - Fundacion de dominio y acceso

Objetivos:
- Definir entidades nucleares en codigo.
- Establecer control de acceso base.

Entregables:
- Modelos/migraciones base de tenant, roles, empleados, departamentos.
- Policies iniciales para recursos core.
- Tests iniciales de autorizacion y tenancy.

Riesgos:
- Ambiguedad en permisos.

## Semana 2 - HR Core operativo

Objetivos:
- Tener gestion de empleados y departamentos funcional.

Entregables:
- CRUD empleados/departamentos en backoffice.
- Auditoria basica de operaciones core.
- Feature tests de flujos principales.

Riesgos:
- Cambios de alcance en campos y reglas de negocio.

## Semana 3 - Control horario MVP

Objetivos:
- Implementar ciclo entrada/pausa/salida.

Entregables:
- Flujos de fichaje en portal empleado.
- Reglas de secuencia y bloqueo basico.
- Tests de casos validos e invalidos.

Riesgos:
- Complejidad en excepciones temporales.

## Semana 4 - Vacaciones y ausencias

Objetivos:
- Implementar solicitudes y aprobaciones.

Entregables:
- Flujo de vacaciones end-to-end.
- Flujo de ausencias con estados.
- Integracion inicial con calendario.

Riesgos:
- Reglas de saldo y solapes.

## Semana 5 - Documental, reporting y endurecimiento

Objetivos:
- Cubrir documental basico y reportes operativos.

Entregables:
- Carga/consulta documental con controles de acceso.
- Reportes principales (horas, vacaciones, ausencias).
- Mejoras de seguridad y observabilidad.

Riesgos:
- Rendimiento en consultas agregadas.

## Semana 6 - Estabilizacion y cierre

Objetivos:
- Preparar entrega de FTM con calidad verificable.

Entregables:
- Correccion de incidencias criticas.
- Regresion de pruebas en modulos Must.
- Cierre de documentacion final y demo preparada.

Riesgos:
- Deuda tecnica acumulada por cambios tardios.

## 4. Plan real de entrega al 20/07/2026

Este plan comprime el trabajo para llegar a una version demostrable, estable y bien documentada.

## Tramo A - 27/06 a 03/07

Objetivos:
- Base funcional del dominio y acceso.
- Primeras piezas de backoffice operativas.

Entregables:
- Modelado inicial de tenant, roles, empleados y departamentos.
- Control de acceso base (policies y validaciones criticas).
- Pruebas iniciales de tenancy y autorizacion.

## Tramo B - 04/07 a 10/07

Objetivos:
- HR Core usable de extremo a extremo.

Entregables:
- CRUD estable de empleados/departamentos.
- Auditoria basica de cambios criticos.
- Primer flujo funcional de portal empleado.

## Tramo C - 11/07 a 16/07

Objetivos:
- Flujos de negocio visibles en demo.

Entregables:
- Control horario MVP (entrada, pausa, salida).
- Vacaciones MVP (solicitud y decision).
- Integracion minima en calendario.

## Tramo D - 17/07 a 20/07

Objetivos:
- Cierre de calidad para defensa del trabajo.

Entregables:
- Correccion de defectos criticos y regresion de pruebas del alcance Must.
- Endurecimiento minimo de seguridad (acceso, auditoria, trazabilidad).
- Documentacion final coherente y guion de demo.

## Regla de priorizacion para el tramo final

- Prioridad 1: que los flujos Must funcionen sin errores bloqueantes.
- Prioridad 2: evidencia de seguridad y tenancy en pruebas.
- Prioridad 3: acabado UX suficiente para uso real en demo.
- Prioridad 4: funcionalidades Should/Could solo si no comprometen estabilidad.

## 5. Dependencias

- Semana 2 depende de Semana 1.
- Semana 3 depende de modelos/roles de semanas previas.
- Semana 4 depende de empleados/departamentos y reglas de acceso.
- Semana 5 depende de datos consolidados de semanas 2-4.
- Semana 6 depende de estabilidad general y cobertura de pruebas.

## 6. Criterios de exito

- Modulos Must operativos de extremo a extremo.
- Aislamiento tenant verificado en pruebas.
- Flujos criticos con auditoria activa.
- Defectos criticos abiertos igual a cero en cierre.
- Demo funcional continua de los modulos Must sin intervenciones manuales fuera de flujo.

## 7. Definicion de terminado por tramo

- Funcionalidad implementada.
- Pruebas minimas del alcance en verde.
- Riesgos y decisiones registrados.
- Documentacion actualizada.

## 8. Criterios de aceptacion del documento

- Diferencia explicitamente roadmap simulada y plan real de entrega.
- Entregables verificables con fecha limite 20/07/2026.
- Coherencia con architecture, domain-model, modules y security.
