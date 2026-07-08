# HRFlow — Visión técnica del proyecto

Documento de referencia técnica destinado a desarrolladores. Para una visión de alto nivel consulta el [README](../README.md).

---

## 1. Contexto y objetivos

HRFlow es una aplicación web SaaS multi-tenant de gestión de recursos humanos desarrollada como Trabajo de Fin de Máster. Su alcance no es comercial, pero el código sigue estándares de producción para demostrar competencias profesionales.

**Objetivo técnico principal:** demostrar que un desarrollador puede construir, desde cero, una aplicación empresarial real con Laravel aplicando SOLID, Security by Design, arquitectura en capas, testing sistemático e integración continua.

---

## 2. Stack tecnológico verificado

| Paquete | Versión | Rol |
|---|---|---|
| `laravel/framework` | ^13.8 | Framework base |
| `filament/filament` | ^4.0 | Panel de administración |
| `livewire/livewire` | 3 (transitive) | Componentes reactivos |
| `stancl/tenancy` | ^3.10 | Multi-tenancy |
| `spatie/laravel-permission` | ^8.0 | RBAC |
| `spatie/laravel-activitylog` | ^4.12 | Auditoría |
| `laravel/sanctum` | ^4.3 | Autenticación API |
| `tailwindcss` | ^4.0 | CSS utilitario |
| `vite` | ^8.0 | Bundler |
| `phpunit/phpunit` | ^12.5 | Tests |
| `@playwright/test` | ^1.54 | E2E |
| `larastan/larastan` | ^3.10 | Análisis estático |
| `laravel/pint` | ^1.29 | Formateador PHP |
| `eslint` | ^10.6 | Linter JS |
| `laravel/boost` | ^2.4 | MCP server de asistencia |

---

## 3. Arquitectura

### 3.1 Capas

```
HTTP / Livewire / Filament
         │
    Form Requests (validación)
         │
    Controllers / Components (orquestación)
         │
    Services (lógica de aplicación)
         │
    Models + Policies (dominio)
         │
    Eloquent / Storage (infraestructura)
```

### 3.2 Multi-tenancy

- **Estrategia:** single-database con columna `tenant_id` en todas las entidades de negocio.
- **Resolución de tenant:** por ruta `/portal/{tenant}` usando `stancl/tenancy` con `InitializeTenancyByPath`.
- **Scope automático:** trait `BelongsToTenant` en modelos tenant-aware con `creating`/`updating` listeners.
- **Verificación adicional:** middleware `EnsureUserBelongsToTenant` en el portal.
- **Policies:** toda policy verifica `$user->tenant_id === $resource->tenant_id` además del rol/permiso.

### 3.3 Decisión de dominio: User como empleado unificado

En el MVP, `User` modela tanto la identidad autenticable como el perfil laboral del empleado. No existe entidad `Employee` separada. Esta decisión está documentada en `knowledge/user-as-employee-refactor.md`.

Campos laborales en `users`:
- `tenant_id`, `department_id`
- `employee_code`, `job_title`, `hire_date`, `employment_status`
- `annual_vacation_days`, `avatar_path`

---

## 4. Módulos implementados

### 4.1 Backoffice (Filament v4)

Recursos disponibles en `app/Filament/Resources/`:

| Recurso | Modelo | Estado |
|---|---|---|
| `UserResource` | `User` | ✅ Implementado |
| `DepartmentResource` | `Department` | ✅ Implementado |
| `DocumentResource` | `Document` | ✅ Implementado |
| `LeaveRequestResource` | `LeaveRequest` | ✅ Implementado |
| `TimeEntryResource` | `TimeEntry` | ✅ Implementado |
| `TurnoResource` | `Turno` | ✅ Implementado |
| `TenantResource` | `Tenant` | ✅ Implementado |
| `FestivoResource` | `Festivo` | ✅ Implementado |

Cada recurso sigue la estructura: `*Resource.php`, `Pages/`, `Schemas/`, `Tables/`, `RelationManagers/`.

### 4.2 Portal del empleado (Livewire)

Rutas en `routes/tenant.php` bajo el prefijo `/portal/{tenant}/`:

| Ruta | Controlador / Componente | Estado |
|---|---|---|
| `/dashboard` | `PortalDashboardController` | ✅ Implementado |
| `/control-horario` | `PortalTimeTrackingController` + `TimeTracker` | ✅ Implementado |
| `/solicitudes` | `PortalRequestsController` + `LeaveRequests` | ✅ Implementado |
| `/documentacion` | `PortalDocumentsController` + `Documents` | ✅ Implementado |
| `/documentacion/descargar/{document}` | `PortalDocumentDownloadController` | ✅ Implementado |
| `/calendario` | `PortalCalendarController` + `PortalCalendarEventsController` | ✅ Implementado |

Componentes Livewire: `TimeTracker`, `LeaveRequests`, `Documents`, `NotificationBell`.

### 4.3 API REST

Endpoints en `routes/api.php`, protegidos con `auth:sanctum` y throttle `api-authenticated`:

```
GET|POST   /api/users
GET|PUT|DELETE /api/users/{user}
GET|POST   /api/departments
GET|PUT|DELETE /api/departments/{department}
```

### 4.4 Módulo de turnos

- `Turno`: plantilla de turno con `start_time`, `end_time`, `break_minutes`, `total_hours`.
- `TurnoAssignment`: asignación de turno a usuario con vigencia (`valid_from`, `valid_until`). Sin fechas = permanente.
- Scope `activeOn($date)` para consulta de turno vigente en una fecha.
- Gestionado desde el Relation Manager de empleados en Filament.

### 4.5 Gestión documental

- Carpetas tipadas con enum `DocumentFolder`: `nominas`, `contratos`, `normativas`, `otros`.
- Almacenamiento privado (visibilidad no pública por defecto).
- Descarga autorizada via Policy verificando tenant + propiedad.

### 4.6 Solicitudes de ausencia

- Tipos: `vacation` (vacaciones) y `paid_leave` (permiso retribuido) — enum `LeaveRequestType`.
- Estados: `pending`, `approved`, `rejected` — enum `LeaveRequestStatus`.
- Notificación `LeaveRequestSubmitted` al responsable.

### 4.7 Control horario

- Ciclo: entrada → salida registrado en `TimeEntry`.
- Estados: `complete` / `incomplete` — enum `TimeEntryStatus`.
- Componente `TimeTracker` con polling automático cada 30 segundos.

---

## 5. Modelos y relaciones clave

```
Tenant
  ├── hasMany User
  ├── hasMany Department
  ├── hasMany Document
  ├── hasMany LeaveRequest
  ├── hasMany TimeEntry
  ├── hasMany Turno
  ├── hasMany TurnoAssignment
  └── hasMany Festivo

User (= empleado)
  ├── belongsTo Tenant
  ├── belongsTo Department
  ├── hasMany Document
  ├── hasMany LeaveRequest
  ├── hasMany TimeEntry
  └── hasMany TurnoAssignment

Department
  ├── belongsTo Tenant
  ├── hasMany User (miembros)
  └── belongsTo User (manager_user_id)

Turno
  ├── belongsTo Tenant
  └── hasMany TurnoAssignment

TurnoAssignment
  ├── belongsTo Tenant
  ├── belongsTo Turno
  └── belongsTo User
```

---

## 6. Autorización

### Políticas por modelo

| Policy | Guard | Métodos cubiertos |
|---|---|---|
| `UserPolicy` | web | viewAny, view, create, update, delete |
| `DepartmentPolicy` | web | viewAny, view, create, update, delete |
| `DocumentPolicy` | web | viewAny, view, create, update, delete, download |
| `LeaveRequestPolicy` | web | viewAny, view, create, update, delete |
| `TimeEntryPolicy` | web | viewAny, view, create, update, delete |
| `TurnoPolicy` | web | viewAny, view, create, update, delete |
| `TurnoAssignmentPolicy` | web | viewAny, view, create, update, delete |
| `TenantPolicy` | web | viewAny, view, update |
| `FestivoPolicy` | web | viewAny, view, create, update, delete |

### Roles y permisos

Permisos granulares del tipo `{recurso}.{acción}` (ej. `employee.create`, `leave-request.update`).

5 roles: `super-admin`, `company-admin`, `hr`, `department-manager`, `employee`.

---

## 7. Testing

### PHPUnit — Suites de Feature Tests

| Archivo | Cobertura |
|---|---|
| `Authorization/PolicyAuthorizationTest` | Autorización por role/permiso/tenant |
| `Portal/TenantIsolationTest` | Aislamiento entre tenants en portal |
| `Portal/PortalTimeTrackingTest` | Ciclo de fichaje |
| `Portal/PortalLeaveRequestsTest` | Flujo de solicitudes |
| `Portal/PortalDocumentDownloadTest` | Descarga autorizada de documentos |
| `Portal/PortalCalendarEventsTest` | Eventos del calendario |
| `Portal/PortalShellTest` | Estructura del portal |
| `Portal/TodayOffReasonTest` | Razón de ausencia del día |
| `Filament/TenantManagementTest` | Gestión de tenants en Filament |
| `Filament/ResourceQueryTest` | Consultas de recursos con tenant |
| `Filament/ResourceLabelsTest` | Etiquetas de recursos |
| `Filament/TableFiltersTest` | Filtros de tablas |
| `Filament/DepartmentMembersRelationManagerTest` | Relaciones de departamento |
| `Filament/UserRelationManagersTest` | Relation managers de usuario |
| `Api/UserApiTest` | API REST de usuarios |
| `DocumentManagementTest` | Ciclo completo documental |
| `TurnoModuleTest` | Módulo de turnos |
| `FestivoManagementTest` | Gestión de festivos |
| `FestivosSeederTest` | Idempotencia del seeder |
| `UserSensitiveDataProtectionTest` | Protección de datos sensibles |
| `Feature/UserProfileAndSecurityTest` | Perfil de usuario y seguridad |

### Playwright — Suites E2E

| Spec | Cobertura |
|---|---|
| `auth.spec.ts` | Login y logout en el portal |
| `time-tracking.spec.ts` | Fichaje de entrada y salida |
| `leave-requests.spec.ts` | Solicitud de ausencia |

### Ejecución

```bash
# PHPUnit completo
php artisan test --compact

# PHPUnit filtrado
php artisan test --compact --filter=TenantIsolation

# Playwright
npm run e2e
```

---

## 8. Calidad de código

### PHPStan

Configurado en `phpstan.neon`. Nivel 6 con extensión Larastan.

Rutas analizadas: `app/`, `routes/`, `database/factories/`, `database/seeders/`.

```bash
vendor/bin/phpstan analyse
```

### Laravel Pint

Basado en PHP-CS-Fixer con el preset Laravel. Se ejecuta en modo `--test` en CI.

```bash
vendor/bin/pint --test     # solo verificar
vendor/bin/pint            # corregir
```

### ESLint

Configurado para `resources/js/`.

```bash
npm run lint
npm run lint:fix
```

---

## 9. CI/CD

### Workflows

| Archivo | Trigger | Función |
|---|---|---|
| `quality-gate.yaml` | push (main/develop), PR | Calidad completa |
| `hrflow-build.yaml` | — | Compilación de assets |
| `hrflow-orchestration.yaml` | — | Orquestación de despliegue |
| `deploy-to-ionos.yaml` | workflow_dispatch | Despliegue en IONOS |

### Quality Gate detallado

```yaml
steps:
  - Checkout
  - Setup PHP 8.3 + extensions
  - Setup Node 22
  - composer install
  - npm ci
  - cp .env.example + key:generate + sqlite
  - pint --test          # continue-on-error: true
  - phpstan analyse      # continue-on-error: true
  - php artisan test     # continue-on-error: true (sqlite)
  - npm run lint         # continue-on-error: true
  - composer audit       # continue-on-error: true
  - npm audit            # continue-on-error: true
```

---

## 10. Seeders

Los tres seeders son **idempotentes**: solo insertan datos si no existen.

| Seeder | Guard de idempotencia |
|---|---|
| `RolesAndPermissionsSeeder` | Verifica roles y permisos por nombre |
| `DemoTenantsSeeder` | Verifica tenants y usuarios demo |
| `FestivosSeeder` | Verifica festivos por tenant y fecha |

```bash
php artisan db:seed --force -n
```

---

## 11. Agentes de IA

Configurados en `.github/agents/`:

| Agente | Archivo | Especialidad |
|---|---|---|
| HRFlow Code Reviewer | `hrflow-code-reviewer.md` | Revisión de código con criterios de seguridad y arquitectura |
| HRFlow Full Stack Developer | `hrflow-full-stack-developer.md` | Desarrollo de funcionalidades |
| HRFlow Software Architect | `hrflow-software-architect.md` | Diseño arquitectónico y modelo de datos |
| HRFlow Test Engineer | `hrflow-tests-engineer.md` | Creación y revisión de tests |

**Laravel Boost** actúa como MCP server que expone herramientas de consulta de base de datos, schema, logs, URLs y documentación oficial del stack instalado, permitiendo que los agentes trabajen con contexto preciso del proyecto.

---

## 12. Vulnerabilidades conocidas (auditoría 2026-07-07)

| Severidad | Descripción | Estado |
|---|---|---|
| 🔴 Alto | Posible fuga de `tenant_id` en formularios Filament si el campo se envía manipulado en el payload Livewire | Pendiente de corrección (normalización server-side) |
| 🟠 Medio | `viewAny` no aplicado explícitamente en endpoints `index` de API | Pendiente |
| 🟠 Medio | Ausencia de rate limiting global en endpoints de listado | Pendiente |
| 🟠 Medio | Datos personales sin cifrado en reposo | Pendiente |
| 🟢 Bajo | Sin headers de seguridad HTTP explícitos | Pendiente |

La auditoría completa está en `knowledge/security-audit-2026-07-07.md`.

---

## 13. Documentación adicional

| Archivo | Contenido |
|---|---|
| `knowledge/architecture.md` | Decisiones arquitectónicas y capas |
| `knowledge/domain-model.md` | Entidades, relaciones e invariantes de dominio |
| `knowledge/modules.md` | Casos de uso y reglas de negocio por módulo |
| `knowledge/security.md` | Amenazas, controles y OWASP aplicado |
| `knowledge/roadmap.md` | Plan de entrega y priorización |
| `knowledge/coding-standards.md` | Estándares de desarrollo Laravel |
| `knowledge/turnos.md` | Diseño técnico del módulo de turnos |
| `knowledge/user-as-employee-refactor.md` | Decisión de dominio User = Empleado |
| `knowledge/security-audit-2026-07-07.md` | Auditoría de seguridad estática |
