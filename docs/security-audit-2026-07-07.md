# Auditoría de Seguridad HRFlow

Fecha: 2026-07-07  
Alcance: revisión estática de código fuente (Laravel 13, Filament 4, Livewire 3, Sanctum)  
Metodología: OWASP Top 10, Laravel Security Best Practices, Security by Design, Least Privilege, Defense in Depth.

## Resumen ejecutivo

La base de seguridad del proyecto es razonable para una demo académica: existen Policies por modelo, aislamiento por tenant en múltiples consultas, validación en Form Requests para API y uso correcto de CSRF en formularios web.

Sin embargo, se detecta una vulnerabilidad de **riesgo alto** que afecta directamente al aislamiento multi-tenant en Filament: varios formularios confían en campos tenant_id deshabilitados en UI, pero siguen hidratándose y persistiéndose sin una normalización server-side robusta. Esto abre la puerta a manipulación de payload para crear o mover registros entre tenants.

Además, hay debilidades de control de acceso en API (inconsistencia viewAny), ausencia de rate limiting en login/API y exposición de datos personales sensibles sin cifrado en reposo.

## Número de vulnerabilidades por severidad

- 🔴 Alto: 1
- 🟠 Medio: 3
- 🟢 Bajo: 1
- Total: 5

## Hallazgos críticos

No se identificaron hallazgos de criticidad superior a Alto.

## Hallazgos importantes

### 1) 🔴 Alto - Posible fuga entre tenants por manipulación de tenant_id en formularios Filament

- Nivel de riesgo: 🔴 Alto
- Descripción: múltiples recursos Filament dependen de tenant_id deshabilitado en frontend, pero el dato sigue deshidratándose y puede ser forzado en la petición Livewire.
- Archivos afectados:
  - [app/Filament/Resources/Users/Schemas/UserForm.php](app/Filament/Resources/Users/Schemas/UserForm.php#L44)
  - [app/Filament/Resources/Departments/Schemas/DepartmentForm.php](app/Filament/Resources/Departments/Schemas/DepartmentForm.php#L25)
  - [app/Filament/Resources/Documents/Schemas/DocumentForm.php](app/Filament/Resources/Documents/Schemas/DocumentForm.php#L30)
  - [app/Filament/Resources/LeaveRequests/Schemas/LeaveRequestForm.php](app/Filament/Resources/LeaveRequests/Schemas/LeaveRequestForm.php#L30)
  - [app/Filament/Resources/TimeEntries/Schemas/TimeEntryForm.php](app/Filament/Resources/TimeEntries/Schemas/TimeEntryForm.php#L27)
  - [app/Filament/Resources/Turnos/Schemas/TurnoForm.php](app/Filament/Resources/Turnos/Schemas/TurnoForm.php#L24)
  - [app/Filament/Resources/Festivos/Schemas/FestivoForm.php](app/Filament/Resources/Festivos/Schemas/FestivoForm.php#L22)
  - [app/Models/Concerns/BelongsToTenant.php](app/Models/Concerns/BelongsToTenant.php#L24)
- Explicación del problema: el patrón disabled + dehydrated protege la UI, no la capa servidor. Si tenant_id llega relleno, el trait BelongsToTenant no lo sobrescribe y permite persistir el valor enviado.
- Riesgo asociado: creación/modificación de registros en tenant ajeno, fuga de datos indirecta y ruptura de aislamiento multi-tenant (Broken Access Control, Insecure Design).
- Recomendación: forzar tenant_id en servidor para cualquier usuario no super-admin, ignorando completamente el valor entrante.
- Propuesta de solución sencilla compatible con HRFlow:
  - En cada Create/Edit Page de recursos tenant-aware, normalizar tenant_id con el tenant del usuario autenticado cuando no sea super-admin.
  - Alternativamente, reforzar BelongsToTenant para que en creating y updating imponga tenant_id del usuario no super-admin.
  - Añadir tests de manipulación de payload Livewire para confirmar que un company-admin no puede crear/editar registros en otro tenant.

### 2) 🟠 Medio - Inconsistencia de autorización en listados API (viewAny no aplicado)

- Nivel de riesgo: 🟠 Medio
- Descripción: los endpoints index de API no ejecutan Gate::authorize para viewAny, mientras que show sí autoriza explícitamente.
- Archivos afectados:
  - [app/Http/Controllers/Api/UserController.php](app/Http/Controllers/Api/UserController.php#L18)
  - [app/Http/Controllers/Api/DepartmentController.php](app/Http/Controllers/Api/DepartmentController.php#L19)
  - Referencia de contraste: [app/Http/Controllers/Api/UserController.php](app/Http/Controllers/Api/UserController.php#L46), [app/Http/Controllers/Api/DepartmentController.php](app/Http/Controllers/Api/DepartmentController.php#L47)
- Explicación del problema: se confía en visibleTo para filtrar resultados, pero no se deniega el acceso cuando la policy viewAny debería bloquearlo.
- Riesgo asociado: bypass parcial de reglas de autorización previstas y comportamiento inconsistente entre endpoints.
- Recomendación: aplicar autorización explícita en index con viewAny para cada recurso.
- Propuesta de solución sencilla compatible con HRFlow:
  - En index de UserController y DepartmentController, añadir Gate::authorize('viewAny', Model::class) antes de construir la query.

### 3) 🟠 Medio - Falta de rate limiting en login y API autenticada

- Nivel de riesgo: 🟠 Medio
- Descripción: no se observan límites de frecuencia en rutas de login ni en rutas API protegidas.
- Archivos afectados:
  - [routes/web.php](routes/web.php#L15)
  - [routes/tenant.php](routes/tenant.php#L34)
  - [routes/api.php](routes/api.php#L12)
  - [app/Http/Controllers/Auth/PortalAuthenticatedSessionController.php](app/Http/Controllers/Auth/PortalAuthenticatedSessionController.php#L48)
- Explicación del problema: Auth::attempt se ejecuta sin throttling explícito en login; las rutas API usan auth:sanctum pero sin limitador dedicado.
- Riesgo asociado: mayor exposición a fuerza bruta y abuso de endpoints.
- Recomendación: aplicar RateLimiter nativo de Laravel en autenticación y middleware throttle para API.
- Propuesta de solución sencilla compatible con HRFlow:
  - Añadir middleware throttle a login y portal login.
  - Definir un rate limiter para API autenticada (por user_id y/o IP) en bootstrap/app.php y aplicarlo en routes/api.php.

### 4) 🟠 Medio - Datos personales sensibles sin cifrado en reposo

- Nivel de riesgo: 🟠 Medio
- Descripción: campos de alta sensibilidad (DNI/NIF y número de seguridad social) se almacenan como texto plano y se muestran completos en el panel.
- Archivos afectados:
  - [app/Models/User.php](app/Models/User.php#L29)
  - [app/Filament/Resources/Users/Schemas/UserInfolist.php](app/Filament/Resources/Users/Schemas/UserInfolist.php#L55)
- Explicación del problema: no hay casts encrypted para estos atributos ni enmascaramiento de visualización.
- Riesgo asociado: impacto elevado si hay acceso indebido a base de datos o a vistas administrativas.
- Recomendación: cifrado de campos sensibles y enmascarado parcial en vistas.
- Propuesta de solución sencilla compatible con HRFlow:
  - Aplicar cast encrypted para national_id y social_security_number.
  - Mostrar únicamente los últimos dígitos en listados/infolists salvo necesidad estricta de lectura completa.

### 5) 🟢 Bajo - Uso de renderizado HTML sin escape (controlado, pero endurecible)

- Nivel de riesgo: 🟢 Bajo
- Descripción: se usan bloques HTML sin escape.
- Archivos afectados:
  - [resources/views/auth/login.blade.php](resources/views/auth/login.blade.php#L61)
  - [resources/views/livewire/portal/documents.blade.php](resources/views/livewire/portal/documents.blade.php#L52)
- Explicación del problema: actualmente el contenido parece controlado por código interno, pero el patrón facilita introducir XSS futuro si el origen cambia.
- Riesgo asociado: deuda de seguridad preventiva (XSS potencial por evolución).
- Recomendación: minimizar uso de salida raw y encapsular HTML estático en componentes Blade.
- Propuesta de solución sencilla compatible con HRFlow:
  - Sustituir HTML raw por componentes/partials para iconos y enlaces de pie de login.
  - Mantener salida escapada por defecto para cualquier dato que pueda evolucionar a contenido dinámico.

## Auditoría específica de aislamiento entre tenants

### Resultado

Aislamiento parcialmente sólido, pero **no robusto** frente a manipulación activa del payload en Filament.

### Verificaciones realizadas

- Policies con comprobación de pertenencia tenant en modelos principales: correcto en general.
- Middleware de portal para coherencia usuario-tenant en ruta: correcto.
- Scopes visibleTo/forTenant en listados y recursos: ampliamente presentes.
- Descarga de documentos en portal protegida por policy download: correcto.

### Riesgo principal de fuga inter-tenant detectado

- Hallazgo 1 (Alto): manipulación de tenant_id en formularios Filament sin normalización estricta server-side.

## Mejoras recomendadas (priorizadas)

1. Corregir inmediatamente la normalización server-side de tenant_id en recursos Filament tenant-aware.
2. Añadir tests de seguridad de aislamiento tenant para creación/edición manipulando payload Livewire.
3. Aplicar Gate::authorize viewAny en index de API.
4. Introducir throttling nativo en login y API.
5. Cifrar y enmascarar PII sensible en usuarios.
6. Reducir uso de salida Blade raw en vistas.

## Valoración general del estado de seguridad

Estado actual: **Aceptable con reservas** para demostración académica.

Fortalezas:
- Policies y controles de tenant bien distribuidos.
- CSRF correctamente aplicado en formularios.
- Validaciones razonables en Form Requests y formularios.

Debilidad clave:
- Un vector de manipulación de tenant_id en Filament compromete el principio de aislamiento, que es el requisito más crítico del proyecto.

Conclusión:
- El proyecto puede defenderse en una presentación técnica si se corrige primero el hallazgo de riesgo Alto y se aplican los ajustes medios de autorización/rate limiting.
