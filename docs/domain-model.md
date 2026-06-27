# HRFlow - Modelo de dominio

## 1. Objetivo

Definir entidades, relaciones, reglas e invariantes del dominio HRFlow para alinear implementacion, seguridad y pruebas.

## 2. Bounded contexts

### 2.1 Tenancy

Gestion de empresa cliente (tenant), aislamiento de datos y configuracion por empresa.

### 2.2 Identity & Access

Usuarios, roles, permisos y contexto de acceso.

### 2.3 HR Core

Empleados, departamentos, puestos, contratos y ciclo laboral.

### 2.4 Timekeeping

Fichajes de entrada/salida/pausas y consolidacion de jornada.

### 2.5 Absences & Vacations

Solicitudes de vacaciones, permisos y ausencias.

### 2.6 Documents

Expediente documental del empleado.

### 2.7 Calendar & Org Chart

Calendario laboral y organigrama empresarial.

### 2.8 Audit & Reporting

Registro de acciones y explotacion analitica.

## 3. Entidades principales

## 3.1 Tenant

Representa una empresa cliente.

Atributos minimos:
- id
- name
- status
- timezone
- locale

Invariantes:
- Todo dato de negocio depende de un tenant.
- Un usuario de tenant no opera sobre otro tenant.

## 3.2 User

Actor autenticable del sistema.

Atributos minimos:
- id
- tenant_id
- name
- email
- password
- status

## 3.3 Role / Permission

Modelo de autorizacion.

Roles iniciales:
- Superadministrador
- Administrador de empresa
- Recursos humanos
- Responsable de departamento
- Empleado

## 3.4 Department

Unidad organizativa interna del tenant.

Atributos minimos:
- id
- tenant_id
- name
- manager_employee_id

## 3.5 Employee

Persona trabajadora vinculada a tenant.

Atributos minimos:
- id
- tenant_id
- user_id (nullable en etapas previas)
- department_id
- employee_code
- hire_date
- employment_status

## 3.6 Contract

Condiciones laborales del empleado.

Atributos minimos:
- id
- tenant_id
- employee_id
- contract_type
- start_date
- end_date (nullable)
- weekly_hours

## 3.7 Document

Archivo del expediente del empleado.

Atributos minimos:
- id
- tenant_id
- employee_id
- type
- file_path
- issued_at
- expires_at (nullable)

## 3.8 TimeEntry

Registro de actividad horaria.

Atributos minimos:
- id
- tenant_id
- employee_id
- work_date
- check_in_at
- check_out_at (nullable)
- status

Estados:
- Open
- Closed
- Locked

## 3.9 BreakEntry

Subregistro de pausa dentro de un fichaje.

Atributos minimos:
- id
- tenant_id
- time_entry_id
- start_at
- end_at (nullable)

## 3.10 VacationRequest

Solicitud de vacaciones.

Atributos minimos:
- id
- tenant_id
- employee_id
- start_date
- end_date
- total_days
- status
- requested_by
- decided_by (nullable)
- decided_at (nullable)
- decision_reason (nullable)

Estados:
- Pending
- Approved
- Rejected
- Cancelled

## 3.11 AbsenceRequest

Solicitud de ausencia o permiso.

Estados:
- Pending
- Approved
- Rejected
- Cancelled

## 3.12 AuditLog

Evidencia de acciones relevantes.

Atributos minimos:
- id
- tenant_id
- actor_user_id
- action
- entity_type
- entity_id
- old_values (json)
- new_values (json)
- ip
- user_agent
- created_at

## 4. Relaciones clave

- Tenant 1:N User
- Tenant 1:N Department
- Tenant 1:N Employee
- Department 1:N Employee
- Employee 1:N Contract
- Employee 1:N Document
- Employee 1:N TimeEntry
- TimeEntry 1:N BreakEntry
- Employee 1:N VacationRequest
- Employee 1:N AbsenceRequest
- Tenant 1:N AuditLog

## 5. Reglas de negocio transversales

- Toda consulta de negocio requiere tenant_id.
- Todo cambio de estado debe auditarse.
- Todo flujo critico exige autorizacion explicita.
- No se permite modificar registros bloqueados sin rol habilitado y evidencia.

## 6. Invariantes por agregado

### 6.1 Employee

- employee_code unico por tenant.
- Un empleado inactivo no puede generar nuevas solicitudes operativas.

### 6.2 TimeEntry

- No puede existir check_out_at previo a check_in_at.
- No puede cerrarse si hay pausa abierta.
- Locked implica inmutabilidad funcional.

### 6.3 VacationRequest

- start_date <= end_date.
- total_days > 0.
- Aprobacion requiere saldo disponible.

## 7. Eventos de dominio sugeridos

- EmployeeCreated
- ContractUpdated
- TimeEntryOpened
- TimeEntryClosed
- VacationRequested
- VacationApproved
- VacationRejected
- AbsenceRequested
- SensitiveActionAudited

## 8. Criterios de aceptacion del documento

- Entidades y relaciones completas para todos los modulos del overview.
- Reglas e invariantes claras y testables.
- Estados definidos en procesos criticos.
- Consistencia terminologica con architecture, modules, security y roadmap.
