# Turnos - Diseño técnico

## Objetivo

Gestionar turnos reutilizables y asignarlos a empleados con vigencia temporal, permitiendo tanto periodos cerrados como asignaciones permanentes.

## Decisión de diseño

Nota vigente del MVP:

La asignacion de turnos debe evolucionar de Employee a User segun el plan definido en knowledge/user-as-employee-refactor.md. La decision estructural sobre vigencia por asignacion se mantiene, pero el propietario final de la asignacion pasa a ser User.

- Modelo principal: `Turno`.
- La asignación empleado-turno se modela como una entidad propia: `TurnoAssignment`.
- La tabla principal sigue siendo `turnos`.
- La tabla de asignaciones es `turno_assignments`.
- La duración total del turno se calcula al guardar a partir de `start_time`, `end_time` y `break_minutes`.
- La vigencia de la asignación vive en `valid_from` y `valid_until`.
- Si ambas fechas están vacías, la asignación se considera permanente.
- La gestión de asignaciones se realiza desde el recurso de Empleados en Filament.
- La visibilidad sigue el patrón multi-tenant del proyecto.

## Campos

### Turno

- `name`: nombre del turno.
- `start_time`: hora de inicio.
- `end_time`: hora de finalización.
- `break_minutes`: descanso en minutos.
- `total_hours`: horas netas de jornada.

### TurnoAssignment

- `tenant_id`: empresa propietaria de la asignación.
- `turno_id`: turno asignado.
- `employee_id`: empleado asignado.
- `valid_from`: inicio de vigencia.
- `valid_until`: fin de vigencia.

## Relaciones

- `Turno` pertenece a `Tenant`.
- `Turno` tiene muchas `TurnoAssignment`.
- `Employee` tiene muchas `TurnoAssignment`.
- `TurnoAssignment` pertenece a `Turno`.
- `TurnoAssignment` pertenece a `Employee`.
- `TurnoAssignment` pertenece a `Tenant`.

## Consulta futura para calendario

Para mostrar el turno correcto según una fecha, la base ya dispone del scope `activeOn()` en `TurnoAssignment`, que filtra por el rango de vigencia o por asignaciones permanentes.

## Archivos creados o ajustados

- `app/Models/Turno.php`
- `app/Models/Employee.php`
- `app/Models/TurnoAssignment.php`
- `app/Policies/TurnoPolicy.php`
- `app/Policies/TurnoAssignmentPolicy.php`
- `app/Filament/Resources/Employees/RelationManagers/TurnoAssignmentsRelationManager.php`
- `app/Filament/Resources/Turnos/TurnoResource.php`
- `app/Filament/Resources/Turnos/Schemas/TurnoForm.php`
- `app/Filament/Resources/Turnos/Schemas/TurnoInfolist.php`
- `app/Filament/Resources/Turnos/Tables/TurnosTable.php`
- `database/migrations/2026_07_01_104409_create_turno_assignments_table.php`
- `database/factories/TurnoAssignmentFactory.php`

## Nota de implementación

Se ha mantenido una solución KISS: la vigencia no se mezcla con el turno, sino con la asignación. Eso simplifica el calendario futuro y permite asignaciones estacionales o permanentes sin introducir servicios o lógica distribuida innecesaria.
