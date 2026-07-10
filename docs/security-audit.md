# Auditoría de Seguridad — HRFlow

**Fecha:** 2026-07-10  
**Alcance:** Revisión estática completa del código fuente (Laravel 13, Filament 4, Livewire 3, Spatie Permission, stancl/tenancy)  
**Metodología:** OWASP Top 10 (2021), OWASP ASVS, Laravel Security Best Practices, Security by Design, Principle of Least Privilege  
**Auditor:** GitHub Copilot — Senior Application Security Engineer mode  

---

## Resumen ejecutivo

HRFlow presenta una base de seguridad **sólida** para un proyecto académico de estas características. La arquitectura aplica defensa en profundidad con Policies por modelo, aislamiento multi-tenant verificado en múltiples capas, validaciones server-side, protección CSRF correcta y rate limiting implementado.

Respecto a la auditoría anterior (2026-07-07), se confirma que los hallazgos críticos detectados entonces han sido **completamente resueltos**: el tenant_id se normaliza server-side en todos los recursos, el cifrado de PII sensible está activo, el rate limiting está implementado y la API sin uso ha sido eliminada.

Esta auditoría identifica **8 hallazgos nuevos**, ninguno de nivel crítico. El riesgo más importante es una misconfiguration de despliegue con `APP_DEBUG=true` en el template de producción.

---

## Tabla resumen

| Severidad     | Total |
|---------------|-------|
| 🔴 Crítico    | 0     |
| 🟠 Alto       | 1     |
| 🟡 Medio      | 2     |
| 🟢 Bajo       | 2     |
| 🔵 Informativo | 3    |
| **Total**     | **8** |

---

## Estado de la auditoría anterior (2026-07-07)

Los siguientes hallazgos de la auditoría previa han sido **completamente corregidos**:

| # | Hallazgo anterior | Estado |
|---|------------------|--------|
| 1 | 🔴 Alto: tenant_id manipulable en formularios Filament | ✅ Corregido — `BelongsToTenant` normaliza `tenant_id` en `creating` y `updating` |
| 2 | 🟠 Medio: inconsistencia de autorización en API | ✅ Resuelto — controladores API eliminados, directorio vacío |
| 3 | 🟠 Medio: falta de rate limiting en login y API | ✅ Corregido — `AppServiceProvider` implementa limitadores `login` y `api-authenticated`; rutas usan `throttle:login` |
| 4 | 🟠 Medio: PII sin cifrado en reposo | ✅ Corregido — `national_id` y `social_security_number` usan cast `encrypted`; métodos `maskedNationalId()` / `maskedSocialSecurityNumber()` implementados |
| 5 | 🟢 Bajo: renderizado HTML raw en vistas | ✅ Verificado — no se encuentran `{!! !!}` con datos de usuario; el único `HtmlString` es el logotipo de marca (contenido estático del desarrollador) |

---

## Hallazgos actuales

### HAL-01 — APP_DEBUG=true en template de despliegue a producción

**Severidad:** 🟠 Alto  
**Categoría OWASP:** A05:2021 — Security Misconfiguration  
**Ubicación:** [.deploy-now/hrflow/.env.template](.deploy-now/hrflow/.env.template) — líneas 3–4

**Descripción:**  
El template de variables de entorno para producción (IONOS) tiene configurados simultáneamente `APP_ENV=production` y `APP_DEBUG=true`, así como `LOG_LEVEL=debug`. Esta combinación hace que Laravel muestre stack traces completos, rutas internas, variables de entorno y detalles del framework en cualquier respuesta de error en producción.

**Riesgo:**  
Un error no capturado en producción expondría: rutas de archivos del servidor, versión exacta del framework, estructura de base de datos, variables de configuración y posiblemente fragmentos de credenciales en la traza. Facilita el reconocimiento para un atacante.

**Recomendación:**  
Cambiar en el template de producción:

```dotenv
APP_DEBUG=false
LOG_LEVEL=error
```

---

### HAL-02 — Ruta física de documento construida con `tenant_id` no normalizado

**Severidad:** 🟡 Medio  
**Categoría OWASP:** A01:2021 — Broken Access Control (integridad de datos)  
**Ubicación:**  
- [app/Filament/Resources/Documents/Pages/CreateDocument.php](app/Filament/Resources/Documents/Pages/CreateDocument.php#L17) — `mutateFormDataBeforeCreate`  
- [app/Filament/Resources/Documents/Pages/EditDocument.php](app/Filament/Resources/Documents/Pages/EditDocument.php#L54) — `mutateFormDataBeforeSave`  
- [app/Filament/Resources/Users/RelationManagers/DocumentsRelationManager.php](app/Filament/Resources/Users/RelationManagers/DocumentsRelationManager.php#L149)  

**Descripción:**  
Cuando un usuario no super-admin crea un documento, el campo `tenant_id` del formulario (deshabilitado en UI pero deshidratado) se usa directamente para construir la ruta física del archivo en `mutateFormDataBeforeCreate` **antes** de que el trait `BelongsToTenant` normalice el valor en base de datos.

```php
// CreateDocument.php — $data['tenant_id'] puede ser el valor manipulado del payload
$data['file_path'] = $this->moveUploadedFileToFinalPath(
    $data['tenant_id'],  // ← sin normalizar todavía
    $data['user_id'],
    ...
);
```

El registro en base de datos termina con el `tenant_id` correcto (normalizado), pero `file_path` apunta a un directorio de otro tenant. Un atacante con acceso al panel que conozca el `tenant_id` e `user_id` de otra empresa podría contaminar el directorio de almacenamiento de ese tenant.

La verificación `ensureUserBelongsToTenant` no protege contra esto: si el atacante envía un `user_id` que realmente pertenece al `tenant_id` manipulado (IDs son enteros secuenciales y por tanto adivinables), la comprobación pasa.

**Riesgo:**  
Contaminación del directorio de almacenamiento de otro tenant. No hay filtración de datos entre tenants (el documento sigue perteneciendo al tenant del atacante), pero los ficheros físicos se depositan en el espacio de otro cliente.

**Recomendación:**  
Normalizar `$data['tenant_id']` al tenant del usuario autenticado antes de construir la ruta:

```php
protected function mutateFormDataBeforeCreate(array $data): array
{
    $actingUser = Auth::user();

    // Normalizar tenant_id server-side antes de construir la ruta
    if ($actingUser instanceof User && ! $actingUser->isSuperAdmin()) {
        $data['tenant_id'] = (string) $actingUser->tenant_id;
    }

    $this->ensureUserBelongsToTenant($data);
    // ...resto del método
}
```

Aplicar el mismo patrón en `EditDocument` y `DocumentsRelationManager`.

---

### HAL-03 — Acciones de descarga Filament sin verificación explícita de Policy

**Severidad:** 🟡 Medio  
**Categoría OWASP:** A01:2021 — Broken Access Control (defensa en profundidad)  
**Ubicación:**  
- [app/Filament/Resources/Documents/Pages/ViewDocument.php](app/Filament/Resources/Documents/Pages/ViewDocument.php#L19)  
- [app/Filament/Resources/Documents/Pages/EditDocument.php](app/Filament/Resources/Documents/Pages/EditDocument.php#L29)  
- [app/Filament/Resources/Documents/Tables/DocumentsTable.php](app/Filament/Resources/Documents/Tables/DocumentsTable.php#L105)  
- [app/Filament/Resources/Users/RelationManagers/DocumentsRelationManager.php](app/Filament/Resources/Users/RelationManagers/DocumentsRelationManager.php#L136)  

**Descripción:**  
Las acciones de descarga en el panel de administración usan `response()->download()` directamente, sin invocar `$user->can('download', $document)`. La protección es implícita: el modelo `Document::scopeVisibleToUser` filtra la consulta del recurso y solo devuelve registros para `super-admin`, `company-admin` y `hr`. En contraste, el portal sí verifica explícitamente la Policy:

```php
// PortalDocumentDownloadController — explícito ✅
abort_unless($user->can('download', $document), 403);

// DocumentsTable — solo protección implícita por scope ⚠️
Action::make('download')
    ->action(fn (Document $record) => response()->download(...))
```

La protección real es suficiente actualmente, pero la inconsistencia entre portal y panel dificulta el mantenimiento y puede convertirse en vulnerabilidad si el scope cambia en el futuro.

**Riesgo:**  
Inconsistencia de capas de autorización. Baja probabilidad de explotación actualmente, pero introduce deuda de seguridad.

**Recomendación:**  
Añadir `->authorize('download')` a la acción personalizada de descarga en todas las ubicaciones:

```php
Action::make('download')
    ->authorize(fn (Document $record): bool => auth()->user()?->can('download', $record) ?? false)
    ->action(fn (Document $record) => response()->download(...))
```

---

### HAL-04 — Cookie de sesión sin flag `Secure` en template de producción

**Severidad:** 🟢 Bajo  
**Categoría OWASP:** A02:2021 — Cryptographic Failures  
**Ubicación:**  
- [.deploy-now/hrflow/.env.template](.deploy-now/hrflow/.env.template) — líneas 31–35  
- [config/session.php](config/session.php#L172)  

**Descripción:**  
El template de producción no establece `SESSION_SECURE_COOKIE=true`. La configuración por defecto en `config/session.php` usa `env('SESSION_SECURE_COOKIE')` sin valor por defecto, lo que resulta en `null` (false). En un entorno HTTPS esto permite que las cookies de sesión se transmitan también por HTTP, exponiéndolas a intercepción.

**Riesgo:**  
Si la aplicación en producción atiende por HTTP (incluso como redirección), la cookie de sesión puede ser capturada mediante ataques de red (man-in-the-middle).

**Recomendación:**  
Añadir al template de producción:

```dotenv
SESSION_SECURE_COOKIE=true
```

---

### HAL-05 — Sesiones sin cifrado en reposo

**Severidad:** 🟢 Bajo  
**Categoría OWASP:** A02:2021 — Cryptographic Failures  
**Ubicación:**  
- [.deploy-now/hrflow/.env.template](.deploy-now/hrflow/.env.template) — línea 32  
- [.env](.env) — línea (SESSION_ENCRYPT=false)  

**Descripción:**  
`SESSION_ENCRYPT=false` en ambos entornos (desarrollo y producción). El driver de sesión es `database` en desarrollo y `file` en el template de producción. El contenido de sesión se almacena en texto plano.

**Riesgo:**  
Si un atacante obtiene acceso al sistema de ficheros o base de datos del servidor, los datos de sesión (incluyendo identificadores de usuario y tokens CSRF) son legibles directamente. Impacto limitado si el servidor en sí está comprometido (el problema sería mayor que las sesiones).

**Recomendación:**  
Habilitar cifrado de sesiones en producción:

```dotenv
SESSION_ENCRYPT=true
```

---

### HAL-06 — LOG_LEVEL=debug en template de producción

**Severidad:** 🔵 Informativo  
**Categoría OWASP:** A09:2021 — Security Logging and Monitoring Failures  
**Ubicación:** [.deploy-now/hrflow/.env.template](.deploy-now/hrflow/.env.template) — línea 16  

**Descripción:**  
El nivel de log en el template de producción está configurado en `debug`. Esto genera logs muy verbosos que incluyen detalles de consultas SQL, valores de variables y trazas completas. Además del volumen innecesario, expone información interna si los logs son accesibles.

**Recomendación:**  
Usar `LOG_LEVEL=error` o `LOG_LEVEL=warning` en producción.

---

### HAL-07 — Sin mecanismo de recuperación de contraseña para empleados

**Severidad:** 🔵 Informativo  
**Categoría OWASP:** A07:2021 — Identification and Authentication Failures  
**Ubicación:** [routes/web.php](routes/web.php) / [routes/tenant.php](routes/tenant.php)  

**Descripción:**  
No existe flujo de `forgot password` ni `password reset` para el portal del empleado. El restablecimiento de contraseña requiere intervención de un administrador a través del panel Filament (acción `resetPassword` en `EditUser`).

**Riesgo:**  
Experiencia de usuario degradada. No representa un riesgo de seguridad en sí mismo, pero implica que los administradores deben gestionar activamente el acceso, lo que puede derivar en prácticas informales menos seguras (compartir contraseñas por mensajería, etc.).

**Recomendación:**  
Para una demo académica es aceptable. Si se planea un despliegue real, implementar el flujo estándar de `Password::reset` de Laravel limitado al portal del tenant correspondiente.

---

### HAL-08 — Dirección IP almacenada en logs de actividad sin análisis de necesidad

**Severidad:** 🔵 Informativo  
**Categoría OWASP:** A02:2021 — Cryptographic Failures (privacidad de datos)  
**Ubicación:** [app/Models/Concerns/LogsTenantActivity.php](app/Models/Concerns/LogsTenantActivity.php#L28)  

**Descripción:**  
El trait `LogsTenantActivity` almacena `Request::ip()` en cada registro de actividad:

```php
$activity->ip_address = Request::ip();
```

La dirección IP es dato personal bajo RGPD. Se almacena sin limitación de tiempo de retención explícita y para todos los eventos auditados.

**Riesgo:**  
Riesgo de privacidad en caso de obligación de cumplimiento RGPD. Para un proyecto académico es irrelevante, pero debe tenerse en cuenta antes de un despliegue real con usuarios reales.

**Recomendación:**  
Para un entorno de producción real, documentar la base legal para el almacenamiento de IPs, implementar una política de retención y ofrecer la posibilidad de pseudonimización.

---

## Análisis de aislamiento multi-tenant

El aislamiento entre tenants es el aspecto más crítico del sistema y presenta un nivel de madurez **alto**:

| Mecanismo | Estado |
|-----------|--------|
| `BelongsToTenant` normaliza `tenant_id` en `creating` + `updating` | ✅ Implementado |
| `scopeVisibleTo` en todos los modelos principales | ✅ Implementado |
| `scopeVisibleToUser` con filtrado por rol en recursos Filament | ✅ Implementado |
| Middleware `EnsureUserBelongsToTenant` en portal | ✅ Implementado |
| Policy con verificación de `sharesTenantWithModel` en todos los modelos | ✅ Implementado |
| Descarga de documentos protegida por policy en portal | ✅ Implementado |
| Disco de documentos privado (fuera de public/) | ✅ Implementado |
| Ruta física de ficheros no normalizada server-side (ver HAL-02) | ⚠️ Pendiente |

---

## Análisis por superficie de ataque

### Autenticación
- Login con `Auth::attempt` + `session()->regenerate()` (prevención de session fixation) ✅
- Rate limiting en login: 5 intentos/minuto por email+IP, 15/minuto por IP ✅
- `Remember me` correctamente validado como booleano ✅
- Contraseñas con bcrypt, 12 rondas, reglas de complejidad ✅
- Verificación de cuenta activa antes de permitir acceso ✅
- Sin flujo de reset de contraseña self-service (ver HAL-07) ℹ️

### Autorización
- Policies por modelo con `#[UsePolicy]` en todos los modelos ✅
- `canAccessPanel()` protege el acceso al panel Filament ✅
- `EnsureUserBelongsToTenant` protege el portal ✅
- `before()` en todas las policies para super-admin ✅
- Super-admin no puede eliminarse a sí mismo ✅
- Acciones de descarga sin verificación explícita de policy (ver HAL-03) ⚠️

### Validación y mass assignment
- `#[Fillable]` declarado explícitamente en todos los modelos ✅
- `#[Hidden]` en campos sensibles (`password`, `remember_token`) ✅
- Form Requests con `authorize()` para acciones del perfil ✅
- Validación de MIME type y tamaño en subida de ficheros ✅
- `->preserveFilenames()` en uploads (nombres originales en temp), movidos a ruta segura ✅

### XSS
- No se encontra ningún uso de `{!! !!}` con datos de usuario ✅
- `HtmlString` en AdminPanelProvider es contenido estático del desarrollador ✅
- Toda salida de usuario pasa por el sistema de escape de Blade ✅

### CSRF
- `@csrf` en todos los formularios HTML ✅
- `PreventRequestForgery` incluido en el middleware de Filament ✅

### SQL Injection
- `whereRaw('1 = 0')` y `selectRaw` con valores literales, sin interpolación de usuario ✅
- No se detectan consultas con concatenación de strings de usuario ✅

### Gestión documental
- Disco `documents` configurado como privado (`storage/app/private/documents`) ✅
- Nombre final del archivo aleatorizado (`buildStoragePath` usa `Str::random(8)`) ✅
- Descarga verificada por policy en portal ✅
- Ruta construida con tenant_id no normalizado (ver HAL-02) ⚠️

### Configuración
- `.env` en `.gitignore` ✅
- `APP_KEY` generado y no vacío ✅
- `APP_DEBUG=true` en template de producción (ver HAL-01) 🔴
- `SESSION_ENCRYPT=false` (ver HAL-05) ⚠️
- `SESSION_SECURE_COOKIE` no forzado a `true` (ver HAL-04) ⚠️

---

## Riesgos más importantes por orden de prioridad

1. **HAL-01** — Corregir `APP_DEBUG=false` y `LOG_LEVEL=error` en el template de producción. Riesgo real si se despliega con esta configuración.
2. **HAL-02** — Normalizar `tenant_id` server-side antes de construir rutas de ficheros en operaciones de creación/edición de documentos.
3. **HAL-03** — Añadir `->authorize('download')` a las acciones de descarga en Filament para consistencia y defensa en profundidad.
4. **HAL-04 / HAL-05** — Activar `SESSION_SECURE_COOKIE=true` y `SESSION_ENCRYPT=true` en el template de producción.

---

## Recomendaciones generales

1. **Separar templates de entorno por propósito.** Mantener un `.env.production.example` con todos los valores de seguridad ya configurados de forma segura como punto de partida para despliegues reales.

2. **Añadir tests de seguridad de aislamiento tenant.** Crear tests que verifiquen que un `company-admin` autenticado no puede crear/modificar registros en otro tenant, aunque manipule el payload Livewire. Esto confirmaría que HAL-02 y la normalización de tenant_id funcionan correctamente.

3. **Documentar la base legal para almacenamiento de IPs** si el proyecto se despliega en un entorno real con usuarios europeos (RGPD).

4. **Revisar la política de retención de logs de actividad.** Implementar una purga periódica para evitar acumulación indefinida de datos de auditoría.

5. **Consistencia de autorización portal vs. panel.** Mantener el patrón del portal (verificación explícita con `can()`) también en el panel para facilitar auditorías futuras.

---

## Conclusión

El estado de seguridad de HRFlow es **bueno** para los objetivos del proyecto. La arquitectura multi-tenant aplica aislamiento en múltiples capas, las Policies cubren todos los modelos principales, las validaciones son correctas y los riesgos más graves de la auditoría anterior han sido corregidos.

**El proyecto está en condiciones de ser presentado** en un entorno académico. Los hallazgos actuales no comprometen la seguridad fundamental del sistema.

Antes de un hipotético despliegue en producción real, deberían resolverse obligatoriamente HAL-01 (debug en producción), HAL-02 (rutas de ficheros) y HAL-04 (cookie segura). El resto son mejoras de calidad que no bloquean la funcionalidad.

---

*Auditoría realizada sobre commit activo en rama `07072026`. Los hallazgos son válidos para el estado del código en la fecha indicada.*
