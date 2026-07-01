# HRFlow - Checklist semanal de tareas e hitos

Este documento sirve como lista de control de ejecucion semanal para la roadmap de 6 semanas.

## Semana 1 - Fundacion de dominio y acceso

### Tareas

- [x] Definir modelos base: Tenant, Role, Permission, Department, Employee.
- [x] Crear migraciones iniciales y relaciones principales.
- [x] Implementar policies base para recursos core.
- [x] Configurar validaciones iniciales con Form Requests en flujos de alta/edicion.
- [x] Crear pruebas iniciales de autorizacion y aislamiento por tenant.

### Hitos

- [x] Modelo de dominio base persistido y coherente con [docs/domain-model.md](docs/domain-model.md).
- [x] Control de acceso base operativo en recursos core.
- [x] Evidencia de tenancy en pruebas iniciales.

## Semana 2 - HR Core operativo

### Tareas

- [ ] Implementar CRUD de empleados.
- [ ] Implementar CRUD de departamentos.
- [ ] Conectar flujos del backoffice con reglas de acceso.
- [ ] Registrar eventos de auditoria basica en create/update/delete.
- [ ] Cubrir flujos principales con feature tests.

### Hitos

- [ ] Gestion de empleados y departamentos usable de extremo a extremo.
- [ ] Auditoria basica activa en operaciones criticas.
- [ ] Pruebas funcionales de HR Core en verde.

## Semana 3 - Control horario MVP

### Tareas

- [ ] Implementar fichaje de entrada.
- [ ] Implementar inicio y fin de pausas.
- [ ] Implementar fichaje de salida y cierre de jornada.
- [ ] Validar secuencia temporal y estados (Open/Closed/Locked).
- [ ] Crear pruebas de casos validos e invalidos del flujo horario.

### Hitos

- [ ] Flujo entrada-pausa-salida funcional en portal empleado.
- [ ] Reglas de negocio horarias aplicadas y auditables.
- [ ] Cobertura minima de escenarios criticos del modulo.

## Semana 4 - Vacaciones y ausencias

### Tareas

- [ ] Implementar solicitud de vacaciones.
- [ ] Implementar aprobacion y rechazo con motivo.
- [ ] Implementar flujo base de ausencias/permisos.
- [ ] Validar saldo, solapes y permisos de aprobacion.
- [ ] Integrar calendario con eventos de vacaciones y ausencias.

### Hitos

- [ ] Flujo de vacaciones de extremo a extremo operativo.
- [ ] Flujo de ausencias con estados operativos.
- [ ] Calendario muestra informacion minima util para demo.

## Semana 5 - Documental, reporting y endurecimiento

### Tareas

- [ ] Implementar carga y consulta de documentos con control de acceso.
- [ ] Registrar trazabilidad de accesos y cambios documentales.
- [ ] Implementar reportes operativos base (horas, vacaciones, ausencias).
- [ ] Revisar y reforzar controles de seguridad en flujos criticos.
- [ ] Revisar observabilidad minima (logs y eventos clave).

### Hitos

- [ ] Gestion documental basica usable y segura.
- [ ] Reportes principales disponibles para demo.
- [ ] Endurecimiento minimo de seguridad aplicado.

## Semana 6 - Estabilizacion y cierre

### Tareas

- [ ] Corregir defectos criticos y altos pendientes.
- [ ] Ejecutar regresion de pruebas en modulos Must.
- [ ] Revisar coherencia final de documentacion funcional y tecnica.
- [ ] Preparar guion de demo y escenarios de presentacion.
- [ ] Revisar backlog de deuda tecnica y registrar decisiones de cierre.

### Hitos

- [ ] Defectos criticos abiertos igual a cero.
- [ ] Modulos Must estables para demostracion.
- [ ] Entrega final lista para defensa del FTM.

## Checklist transversal por semana

- [ ] Cumplimiento SOLID en diseno de cambios relevantes.
- [ ] Security by Design: validacion + autorizacion + auditoria.
- [ ] Aislamiento tenant verificado en flujos nuevos.
- [ ] Pruebas del alcance semanal en verde.
- [ ] Decisiones tecnicas no triviales sustentadas con Laravel Boost.
