# User como entidad unica de empleado - Plan maestro

## Objetivo

Definir la simplificacion del dominio de HRFlow para que la entidad User represente tanto la identidad autenticable como el perfil laboral del empleado en el MVP.

Este documento deja cerrado el diseno para que el agente desarrollador pueda ejecutar el refactor sin tomar decisiones arquitectonicas adicionales.

## Decision aprobada

HRFlow deja de modelar Employee como entidad de negocio independiente.

En el MVP:

- User es la identidad autenticable.
- User tambien es el empleado operativo del sistema.
- Tenant y rol siguen viviendo en User.
- Los datos laborales basicos tambien viven en User.
- Las relaciones de negocio que hoy dependen de Employee pasaran a depender de User.

Esta decision prevalece sobre el modelo dual User + Employee mientras dure la fase MVP del proyecto.

## Justificacion

La separacion entre User y Employee introduce complejidad que no aporta valor suficiente en una demo academica:

- duplica relaciones y policies
- complica seeders y factories
- hace menos directa la explicacion del dominio
- obliga a resolver con joins una realidad que en el MVP es 1 usuario = 1 empleado

Para HRFlow es mas importante un modelo simple, mantenible y facil de defender que una separacion preparada para escenarios empresariales avanzados.

## Alcance funcional

La simplificacion afecta a los siguientes modulos:

- identidad y acceso
- departamentos
- empleados
- turnos
- fichajes
- solicitudes de vacaciones y ausencias
- documentos
- seeders demo
- policies y filtros tenant-aware
- Filament backoffice
- portal del empleado

## Modelo final objetivo

### 1. User

User pasa a ser la entidad central del dominio operativo.

Responsabilidades:

- autenticacion
- pertenencia a tenant
- asignacion de rol
- datos personales basicos
- datos laborales basicos
- propietario de turnos asignados, fichajes, solicitudes y documentos

Campos minimos esperados en User:

- id
- tenant_id
- name
- email
- password
- department_id
- employee_code
- hire_date
- employment_status
- job_title
- email_verified_at
- remember_token
- timestamps

Notas:

- name puede mantenerse como nombre visible unico para el MVP.
- No es obligatorio dividir first_name y last_name si no aporta valor real a la demo.
- Si ya existen first_name y last_name en Employee, pueden migrarse a un unico campo name para simplificar.

### 2. Department

Department se mantiene como entidad independiente.

Campos objetivo:

- id
- tenant_id
- name
- manager_user_id

Relaciones objetivo:

- Department belongsTo Tenant
- Department hasMany User
- Department belongsTo User como manager mediante manager_user_id

### 3. Turno

Turno se mantiene como catalogo de turnos reutilizables por tenant.

Sin cambios conceptuales.

### 4. TurnoAssignment

TurnoAssignment se mantiene como entidad propia para preservar la vigencia temporal.

Campos objetivo:

- id
- tenant_id
- turno_id
- user_id
- valid_from
- valid_until
- timestamps

Motivo:

La vigencia del turno sigue perteneciendo a la asignacion, no al catalogo Turno.

### 5. TimeEntry

TimeEntry pasa a depender de User.

Campos objetivo:

- id
- tenant_id
- user_id
- work_date
- check_in_time
- check_out_time
- duration_minutes
- status
- notes
- timestamps

### 6. LeaveRequest

LeaveRequest pasa a depender de User como solicitante.

Campos objetivo:

- id
- tenant_id
- user_id
- start_date
- end_date
- total_days
- status
- resolved_by_user_id
- decided_at
- decision_reason
- timestamps

### 7. Document

Document pasa a depender de User.

Campos objetivo:

- id
- tenant_id
- user_id
- category
- title
- file_path
- issued_at
- expires_at
- timestamps

## Relaciones finales esperadas

- Tenant 1:N User
- Tenant 1:N Department
- Tenant 1:N Turno
- Tenant 1:N TurnoAssignment
- Department 1:N User
- User 1:N TimeEntry
- User 1:N LeaveRequest
- User 1:N Document
- User 1:N TurnoAssignment
- User 1:N Department como manager indirecto mediante manager_user_id
- Turno 1:N TurnoAssignment

## Regla de negocio clave

Durante el MVP se asume una equivalencia directa:

- un usuario pertenece a un solo tenant
- un usuario tiene un solo rol principal
- un usuario representa a un solo empleado

No se disenan escenarios adicionales como:

- empleados sin acceso al sistema
- multiples cuentas para una misma persona
- multiples perfiles laborales para un mismo usuario

## Matriz de acceso objetivo

### Super-admin

- acceso a Filament: si
- alcance: global
- puede gestionar tenants, usuarios, departamentos, turnos y configuracion demo

### Administrador de tenant

- acceso a Filament: si
- acceso al portal del empleado: si
- alcance: solo su tenant
- puede gestionar usuarios, departamentos, turnos, documentos y solicitudes del tenant

### Jefe de departamento

- acceso a Filament: si
- acceso al portal del empleado: si
- alcance: solo su tenant y solo los usuarios de su departamento cuando aplique
- puede consultar y operar sobre equipo propio segun policy

### Empleado

- acceso a Filament: no
- acceso al portal del empleado: si
- alcance: solo sus propios datos y operaciones

## Decision UX de acceso

- El portal del empleado y el backoffice mantienen accesos separados.
- El portal se abre desde una URL tenant-aware para preservar el contexto de empresa.
- El login administrativo se reserva al backoffice.
- Si un usuario tiene permisos administrativos y tambien opera como empleado, puede entrar al portal y saltar desde ahi a la zona administrativa.

## Reglas de autorizacion obligatorias

- Toda policy debe seguir tenant_id como primer filtro.
- El rol decide el nivel de acceso dentro del tenant.
- Las acciones de autopropiedad deben comprobar user_id directo, sin pasar por Employee.
- No debe quedar ninguna policy dependiendo de employee.user_id tras el refactor.
- Department manager debe resolverse por manager_user_id.

## Impacto tecnico exacto

### 1. Base de datos

Cambios esperados:

- ampliar tabla users con columnas laborales
- mover department_id a users si aun no existe ahi
- sustituir manager_employee_id por manager_user_id en departments
- sustituir employee_id por user_id en time_entries
- sustituir employee_id por user_id en leave_requests
- sustituir employee_id por user_id en documents
- sustituir employee_id por user_id en turno_assignments
- eliminar tabla employees cuando el refactor este completamente cerrado
- ignorar o eliminar la tabla heredada employee_turno si ya ha quedado obsoleta frente a turno_assignments

Regla:

No modificar migraciones historicas. Crear nuevas migraciones de refactor.

### 2. Modelos

Modelos a ajustar:

- User: nueva entidad laboral central
- Department: manager_user_id y users()
- TurnoAssignment: belongsTo User
- TimeEntry: belongsTo User
- LeaveRequest: belongsTo User
- Document: belongsTo User
- Turno: filtros de visibilidad basados en User directo

Modelo a retirar:

- Employee

Regla:

No dejar helpers duplicados del tipo managesEmployee si ya no existe Employee como concepto operativo.

### 3. Policies

Policies a revisar de forma obligatoria:

- TenantPolicy
- DepartmentPolicy
- UserPolicy o equivalente del recurso principal
- TimeEntryPolicy
- LeaveRequestPolicy
- DocumentPolicy
- TurnoPolicy
- TurnoAssignmentPolicy

Cambio esperado:

Todas las comprobaciones de propiedad deben pasar de employee.user_id a record.user_id o relacion directa con User.

### 4. Filament

Cambios esperados:

- el recurso principal de gestion interna deja de ser Employees y pasa a ser Users
- el relation manager de turnos asignados se mueve al recurso de Users
- los formularios que seleccionan employee_id deben pasar a seleccionar user_id
- los listados por manager deben usar manager_user_id y department_id sobre users
- el panel debe bloquear a empleados y permitir solo super-admin, administrador de tenant y jefe de departamento

### 5. API y Form Requests

Cambios esperados:

- StoreEmployeeRequest y UpdateEmployeeRequest deben desaparecer o reconvertirse a requests de usuario laboral
- StoreDepartmentRequest y UpdateDepartmentRequest deben validar manager_user_id
- cualquier controlador que filtre por manager_employee_id debe migrar a manager_user_id

### 6. Seeders y factories

Cambios esperados:

- los seeders demo deben crear directamente usuarios con datos laborales completos
- no debe existir el paso adicional de crear Employee asociado
- los roles se asignan directamente al usuario final
- las factories de documentos, fichajes, solicitudes y asignaciones deben reciclar User, no Employee

### 7. Tests

Cobertura minima obligatoria tras el refactor:

- un administrador de tenant solo ve usuarios de su empresa
- un jefe de departamento solo ve usuarios de su departamento cuando aplique
- un empleado solo accede a sus propios fichajes, solicitudes, documentos y turnos
- los turnos asignados se resuelven por user_id
- ninguna consulta permite fuga entre tenants
- Filament deniega acceso al rol empleado

## Estrategia de migracion recomendada

Para reducir riesgo, el refactor debe ejecutarse en dos fases.

### Fase 1. Convivencia corta

Objetivo:

mover datos y relaciones sin romper el sistema de golpe.

Orden:

1. anadir columnas laborales a users
2. poblar users a partir de employees existentes
3. anadir manager_user_id a departments
4. anadir user_id a time_entries, leave_requests, documents y turno_assignments
5. backfill de datos desde employee_id hacia user_id
6. adaptar modelos y policies para leer desde user_id
7. adaptar Filament, requests y seeders
8. actualizar tests

### Fase 2. Limpieza

Objetivo:

eliminar la estructura antigua cuando todo funcione ya con User.

Orden:

1. eliminar dependencias de Employee en codigo
2. eliminar employee_id donde ya no se use
3. eliminar manager_employee_id
4. eliminar tabla employees
5. eliminar tabla employee_turno si sigue presente
6. limpiar factories, seeders y knowledge obsoletos

## Orden de implementacion para el agente desarrollador

1. Crear documento tecnico de trabajo basado en este plan si necesita checklist operativo.
2. Crear migraciones nuevas para llevar datos laborales a users y propagar user_id al resto de tablas.
3. Ajustar modelo User con nuevas relaciones y helpers.
4. Ajustar Department, TurnoAssignment, TimeEntry, LeaveRequest y Document.
5. Reescribir policies con tenant + rol + user_id directo.
6. Reemplazar el recurso Employees por Users en Filament.
7. Mover la gestion de turnos asignados al recurso Users.
8. Actualizar Form Requests y controladores afectados.
9. Actualizar seeders y factories.
10. Ejecutar y corregir tests minimos del dominio afectado.
11. Eliminar Employee y restos heredados solo al final.

## Criterios de aceptacion

El refactor se considera terminado solo si se cumple todo lo siguiente:

- no existe ninguna dependencia funcional obligatoria de Employee
- no existe ninguna policy que navegue por employee.user_id
- todos los flujos operativos usan User como actor y propietario
- departamentos usan manager_user_id
- turnos asignados usan user_id
- empleados no acceden a Filament
- los tests de tenant isolation y ownership estan en verde
- la documentacion viva ya no describe el modelo dual

## Riesgos y limites

Riesgos:

- refactor transversal con impacto alto
- posibilidad de dejar filtros o policies a medio migrar
- riesgo de inconsistencias temporales si conviven employee_id y user_id mas tiempo del necesario

Limites aceptados del MVP:

- no se soportan empleados sin cuenta separada
- no se soporta una persona con multiples perfiles laborales
- no se modela una capa HR separada de la identidad

Estos limites son aceptables para una demo academica y simplifican de forma clara la implementacion y la defensa del proyecto.
