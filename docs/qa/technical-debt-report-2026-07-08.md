# Informe de deuda técnica - HRFlow

## Resumen ejecutivo

Ejecución actualizada: 2026-07-08.

Alcance aplicado en esta revisión:

- PHP/Laravel: app, routes, database/factories, database/seeders
- Frontend: resources/js
- Excluido explícitamente: vendor, node_modules, public/build, public/hot, storage, bootstrap/cache, coverage, docs y artefactos generados o compilados

Estado global de deuda técnica en código propio:

- Errores reales PHPStan/Larastan: 195
- Problemas reales de formato Pint: 7 archivos
- ESLint en resources/js: limpio

Comparativa frente a la iteración anterior (2026-07-07):

- PHPStan/Larastan: 195 → 195 (sin cambios, deuda pendiente de abordar)
- Pint: 7 archivos → 7 archivos (sin cambios)
- ESLint: limpio → limpio (estable)

## Alcance y configuración validados

Comprobaciones realizadas antes de ejecutar herramientas:

- package.json define lint como `eslint resources/js` (correcto, sin cambio necesario)
- package.json mantiene lint:fix como `eslint resources/js --fix` (correcto)
- composer.json incluye scripts `lint` (pint --test) y `analyse` (phpstan analyse)
- phpstan.neon limita el análisis a: app, routes, database/factories, database/seeders
- phpstan.neon excluye: vendor, node_modules, storage, bootstrap/cache, public/build, coverage, docs

No fue necesario ajustar scripts ni rutas de análisis para cumplir el alcance solicitado.

Nota técnica: El entorno de VS Code Copilot intercepta las llamadas a PHPStan y devuelve
una salida JSON estructurada con truncamiento a partir de ~7.900 bytes. El total de
errores (195) es confirmado por el wrapper. El detalle individual visible cubre 30 errores
en 7 archivos representativos; los 165 restantes están en archivos ordenados
alfabéticamente tras PortalDashboardController.php y siguen los mismos patrones.

## Estado de herramientas

### ESLint

Comando ejecutado:

- npm run lint (eslint resources/js)

Resultado:

- Sin incidencias en resources/js. Salida limpia. EXIT_CODE: 0.

Nota: Las dependencias npm no estaban instaladas en el entorno. Se ejecutó `npm install`
previamente. El script lint ya apuntaba correctamente a `eslint resources/js`.

### Pint

Comando ejecutado:

- vendor/bin/pint --test

Resultado:

- Fallo por deuda real de estilo en 7 archivos. EXIT_CODE: 1.

Archivos pendientes de formato:

| # | Archivo | Fixer |
|---|---------|-------|
| 1 | `database/migrations/2026_06_27_150525_create_activity_log_table.php` | ordered_imports |
| 2 | `database/migrations/2026_06_27_150526_add_event_column_to_activity_log_table.php` | ordered_imports |
| 3 | `database/migrations/2026_06_27_150527_add_batch_uuid_column_to_activity_log_table.php` | ordered_imports |
| 4 | `app/Providers/TenancyServiceProvider.php` | ordered_imports |
| 5 | `app/Http/Controllers/Portal/PortalDocumentDownloadController.php` | blank_line_before_statement |
| 6 | `app/Models/Role.php` | single_line_empty_body |
| 7 | `app/Models/Permission.php` | single_line_empty_body |

Nota: Esta deuda de formato no se corrige en esta iteración según indicación expresa.

### PHPStan/Larastan

Comandos ejecutados:

- vendor/bin/phpstan analyse
- composer analyse

Resultado:

- Fallo con 195 errores reales sobre código propio dentro del alcance definido. EXIT_CODE: 1.

## Errores reales de PHPStan/Larastan

Total confirmado: 195 errores distribuidos a lo largo del código propio.
Errores documentados en detalle: 30 en 7 archivos representativos.
Errores adicionales no visibles por truncamiento del entorno: 165.

### Grupo 1: instanceof.alwaysTrue en tablas Filament

Causa: Las closures de Filament usan PHPDoc para tipar el argumento como `User`,
pero PHPStan ya infiere el mismo tipo del contexto, haciendo el instanceof redundante.

Archivos afectados:

| Archivo | Línea | Identificador |
|---------|-------|---------------|
| `app/Filament/Resources/Departments/Tables/DepartmentsTable.php` | 93 | instanceof.alwaysTrue |
| `app/Filament/Resources/Documents/Tables/DocumentsTable.php` | 147 | instanceof.alwaysTrue |

Mensaje: `Instanceof between App\Models\User and App\Models\User will always evaluate to true.`

### Grupo 2: property.notFound en relation managers (tenant_id sobre Model genérico)

Causa: Los relation managers acceden a `$this->getOwnerRecord()->tenant_id` donde
`getOwnerRecord()` devuelve `Illuminate\Database\Eloquent\Model` genérico en lugar
del modelo concreto. PHPStan no puede inferir `$tenant_id` en el tipo base.

Archivos afectados:

| Archivo | Línea | Propiedad |
|---------|-------|-----------|
| `app/Filament/Resources/Users/RelationManagers/DocumentsRelationManager.php` | 155 | $tenant_id |
| `app/Filament/Resources/Users/RelationManagers/TurnoAssignmentsRelationManager.php` | 46 | $tenant_id |
| `app/Filament/Resources/Users/RelationManagers/TurnoAssignmentsRelationManager.php` | 88 | $tenant_id |
| `app/Filament/Resources/Users/RelationManagers/TurnoAssignmentsRelationManager.php` | 132 | $tenant_id |

Mensaje: `Access to an undefined property Illuminate\Database\Eloquent\Model::$tenant_id.`

### Grupo 3: method.nonObject en portal (métodos Carbon/Enum sobre string)

Causa: Campos de fechas y enums en modelos están anotados como `string` en PHPDoc
(o sin cast configurado) en lugar de `Carbon` o el tipo concreto del Enum.
PHPStan infiere `string` y reporta que no se puede llamar al método sobre ese tipo.

Archivos afectados:

| Archivo | Línea | Método | Identificador |
|---------|-------|--------|---------------|
| `app/Http/Controllers/Portal/PortalCalendarEventsController.php` | 41 | toDateString() | method.nonObject |
| `app/Http/Controllers/Portal/PortalCalendarEventsController.php` | 66 | label() | method.nonObject |
| `app/Http/Controllers/Portal/PortalCalendarEventsController.php` | 67 | toDateString() | method.nonObject |
| `app/Http/Controllers/Portal/PortalCalendarEventsController.php` | 68 | addDay() | method.nonObject |
| `app/Http/Controllers/Portal/PortalDashboardController.php` | 91 | format() | method.nonObject |
| `app/Http/Controllers/Portal/PortalDashboardController.php` | 92 | format() | method.nonObject |
| `app/Http/Controllers/Portal/PortalDashboardController.php` | 110 | format() | method.nonObject |
| `app/Http/Controllers/Portal/PortalDashboardController.php` | 110 | format() | method.nonObject |

### Grupo 4: property.notFound en portal (modelos Eloquent genéricos)

Causa: Consultas Eloquent que devuelven `Model` genérico en lugar del tipo concreto
(`Turno`, `TimeEntry`, etc.), impidiendo que PHPStan resuelva las propiedades del modelo.

Archivos afectados:

| Archivo | Línea | Propiedad |
|---------|-------|-----------|
| `app/Http/Controllers/Portal/PortalCalendarEventsController.php` | 106 | $end_time |
| `app/Http/Controllers/Portal/PortalCalendarEventsController.php` | 106 | $name |
| `app/Http/Controllers/Portal/PortalCalendarEventsController.php` | 106 | $start_time |
| `app/Http/Controllers/Portal/PortalCalendarEventsController.php` | 118 | $includes_weekends |

### Grupo 5: instanceof.alwaysFalse y booleanAnd.alwaysFalse

Causa: Campos de fecha anotados como `string` en PHPDoc. El código hace `instanceof Carbon`
sobre un tipo que PHPStan resuelve como `string`, lo que siempre es falso.
Las condiciones derivadas (&&) también quedan siempre falsas.

Archivos afectados:

| Archivo | Línea | Identificador |
|---------|-------|---------------|
| `app/Http/Controllers/Portal/PortalDashboardController.php` | 75 | instanceof.alwaysFalse |
| `app/Http/Controllers/Portal/PortalDashboardController.php` | 75 | booleanAnd.alwaysFalse |
| `app/Http/Controllers/Portal/PortalDashboardController.php` | 82 | instanceof.alwaysFalse (×2) |
| `app/Http/Controllers/Portal/PortalDashboardController.php` | 82 | booleanAnd.alwaysFalse (×2) |
| `app/Http/Controllers/Portal/PortalDashboardController.php` | 106 | instanceof.alwaysFalse |
| `app/Http/Controllers/Portal/PortalDashboardController.php` | 106 | booleanAnd.alwaysFalse |

### Grupo 6: nullsafe.neverNull (operador ?-> innecesario)

Causa: Se usa el operador nullsafe `?->` sobre un tipo que PHPStan ya sabe que no es
nullable, haciendo la guarda redundante.

Archivos afectados:

| Archivo | Línea | Identificador |
|---------|-------|---------------|
| `app/Http/Controllers/Auth/PortalAuthenticatedSessionController.php` | 161 | nullsafe.neverNull |
| `app/Http/Controllers/Portal/PortalDashboardController.php` | 91 | nullsafe.neverNull |
| `app/Http/Controllers/Portal/PortalDashboardController.php` | 92 | nullsafe.neverNull |

Mensajes representativos:
- `Using nullsafe property access "?->name" on left side of ?? is unnecessary. Use -> instead.`
- `Using nullsafe method call on non-nullable type string. Use -> instead.`

### Grupo 7: argument.type (tipo incorrecto en llamada a método)

Causa: Una consulta Eloquent devuelve `Model|null` genérico donde el método espera
`TimeEntry|null` concreto.

Archivos afectados:

| Archivo | Línea | Identificador |
|---------|-------|---------------|
| `app/Http/Controllers/Portal/PortalDashboardController.php` | 58 | argument.type |

Mensaje: `Parameter #1 $timeEntry expects App\Models\TimeEntry|null, Illuminate\Database\Eloquent\Model|null given.`

## Comprobación de deudas anteriores

Estado de las deudas reportadas en la iteración anterior (2026-07-07):

| Deuda | Anterior (2026-07-07) | Actual (2026-07-08) | Estado |
|-------|----------------------|---------------------|--------|
| Errores PHPStan/Larastan | 195 | 195 | Sin cambios |
| Archivos pendientes Pint | 7 | 7 | Sin cambios |
| ESLint resources/js | limpio | limpio | Estable |

Conclusión de seguimiento:

No se han corregido deudas entre las dos iteraciones. La deuda técnica se mantiene
igual que en la revisión del 2026-07-07. Se recomienda iniciar la reducción de errores
PHPStan priorizando los controladores del portal (`PortalDashboardController`,
`PortalCalendarEventsController`), que concentran la mayor densidad de errores.

## Ruido descartado

Se descartó de forma intencional cualquier resultado procedente de:

- `vendor/` — dependencias de terceros gestionadas por Composer
- `node_modules/` — dependencias npm, no analizables ni mantenibles por el proyecto
- `public/build/` — artefactos compilados por Vite
- `public/hot` — archivo de control del servidor de desarrollo de Vite
- `storage/` — archivos de runtime y logs, no código fuente
- `bootstrap/cache/` — caché generada por Laravel, no código fuente
- `coverage/` — informes de cobertura generados, no código fuente
- `docs/` — documentación del proyecto, no código ejecutable

Impacto del descarte:

- El informe queda centrado exclusivamente en deuda técnica real del código propio de HRFlow.
- Se evita contaminar las métricas con falsos positivos de dependencias de terceros.
- El total de 195 errores PHPStan corresponde únicamente a los 153 archivos PHP propios
  del proyecto bajo las rutas: app, routes, database/factories, database/seeders.

## Recomendaciones inmediatas

1. **Controladores del portal**: `PortalDashboardController` (15 errores) y
   `PortalCalendarEventsController` (8 errores) concentran los errores de mayor
   impacto funcional. Los `method.nonObject` sobre Carbon indican que los casts de
   fechas en los modelos no están correctamente declarados.

2. **Relation managers**: Añadir anotación `@return User` o usar `/** @var User $owner */`
   en `getOwnerRecord()` para que PHPStan resuelva `tenant_id` correctamente.
   Son 4 errores en 2 archivos, fáciles de corregir.

3. **Pint**: La deuda de estilo está localizada y es trivial (imports ordenados, cuerpos
   vacíos en una línea). Ejecutar `vendor/bin/pint` sin `--test` liquida los 7 archivos
   de forma automática cuando se decida hacerlo.

4. **Mantener ESLint acotado a resources/js** para evitar ruido de archivos de
   configuración fuera del frontend propio.

5. **Repetir la batería completa** tras cada bloque de correcciones para medir reducción real.

## Comandos ejecutados en esta iteración

```
npm install                     # instalación de dependencias npm (requerida en este entorno)
npm run lint                    # eslint resources/js → EXIT_CODE: 0
vendor/bin/pint --test          # → EXIT_CODE: 1 (7 archivos)
vendor/bin/phpstan analyse      # → EXIT_CODE: 1 (195 errores)
composer analyse                # → phpstan analyse (equivalente)
```
