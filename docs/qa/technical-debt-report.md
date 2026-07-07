# Informe de deuda técnica - HRFlow

## Resumen ejecutivo

Ejecución actualizada: 2026-07-07.

Alcance aplicado en esta revisión:

- PHP/Laravel: app, routes, database/factories, database/seeders
- Frontend: resources/js
- Excluido explícitamente: vendor, node_modules, public/build, public/hot, storage, bootstrap/cache, coverage, docs y artefactos generados o compilados

Estado global de deuda técnica en código propio:

- Errores reales PHPStan/Larastan: 195
- Problemas reales de formato Pint: 7 archivos
- ESLint en resources/js: limpio

Comparativa frente a la iteración anterior:

- PHPStan/Larastan: de 228 a 195 (mejora de 33 incidencias)
- Pint: de 11 a 7 archivos pendientes (mejora de 4 archivos)
- ESLint: se mantiene limpio en resources/js

## Alcance y configuración validados

Comprobaciones realizadas antes de ejecutar herramientas:

- package.json ya define lint como eslint resources/js
- package.json mantiene lint:fix como eslint resources/js --fix
- composer.json incluye scripts lint (pint --test) y analyse (phpstan analyse)
- phpstan.neon ya limita el análisis a:
  - app
  - routes
  - database/factories
  - database/seeders
- phpstan.neon excluye vendor, node_modules, storage, bootstrap/cache, public/build, coverage y docs

No fue necesario ajustar scripts ni rutas de análisis para cumplir el alcance solicitado.

## Estado de herramientas

### ESLint

Comando ejecutado:

- npx eslint resources/js

Resultado:

- Sin incidencias en resources/js.

### Pint

Comandos ejecutados:

- vendor/bin/pint --test
- composer lint

Resultado:

- Fallo por deuda real de estilo en 7 archivos.

Archivos pendientes de formato:

1. database/migrations/2026_06_27_150526_add_event_column_to_activity_log_table.php
2. database/migrations/2026_06_27_150527_add_batch_uuid_column_to_activity_log_table.php
3. database/migrations/2026_06_27_150525_create_activity_log_table.php
4. app/Http/Controllers/Portal/PortalDocumentDownloadController.php
5. app/Models/Permission.php
6. app/Models/Role.php
7. app/Providers/TenancyServiceProvider.php

Nota:

- Tal como se pidió, esta deuda de formato no se corrige en esta iteración.

### PHPStan/Larastan

Comandos ejecutados:

- vendor/bin/phpstan analyse
- composer analyse

Resultado:

- Fallo con 195 errores reales sobre código propio dentro del alcance definido.

## Errores reales de PHPStan/Larastan

El análisis completo mantiene 195 errores. A continuación se listan los grupos con mayor impacto funcional y de mantenibilidad observados en la salida actual:

1. Tipado y modelo base en recursos Filament
   - instanceof.alwaysTrue en tablas de recursos
   - property.notFound en relation managers al acceder a tenant_id sobre Model genérico

2. Tipado de fechas y enums en portal
   - method.nonObject al invocar métodos de Carbon o enums sobre tipos inferidos como string
   - argument.type por firmas que esperan modelos concretos pero reciben Model genérico

3. Uso de nullsafe innecesario y señales de tipado inconsistente
   - nullsafe.neverNull en controladores donde el tipo ya es no nullable

Archivos representativos con incidencias:

- app/Filament/Resources/Departments/Tables/DepartmentsTable.php
- app/Filament/Resources/Documents/Tables/DocumentsTable.php
- app/Filament/Resources/Users/RelationManagers/DocumentsRelationManager.php
- app/Filament/Resources/Users/RelationManagers/TurnoAssignmentsRelationManager.php
- app/Http/Controllers/Auth/PortalAuthenticatedSessionController.php
- app/Http/Controllers/Portal/PortalCalendarEventsController.php
- app/Http/Controllers/Portal/PortalDashboardController.php

## Comprobación de deudas anteriores

Estado de las deudas reportadas en la iteración previa:

1. Deuda de estilo
   - Antes: 11 archivos
   - Ahora: 7 archivos
   - Estado: parcialmente corregida

2. Deuda de análisis estático
   - Antes: 228 errores
   - Ahora: 195 errores
   - Estado: parcialmente corregida

3. Deuda de lint frontend
   - Antes: limpio en resources/js
   - Ahora: limpio en resources/js
   - Estado: sin regresiones

Conclusión de seguimiento:

- Sí se han corregido deudas anteriores, pero sigue existiendo deuda técnica relevante en PHPStan/Larastan y una cola pequeña de formato en Pint.

## Ruido descartado

Se descartó de forma intencional cualquier resultado procedente de:

- vendor/
- node_modules/
- public/build/
- public/hot
- storage/
- bootstrap/cache/
- coverage/
- docs/
- archivos generados o compilados

Impacto del descarte:

- El informe queda centrado en deuda técnica real del código propio de HRFlow.
- Se evita mezclar ruido de terceros o artefactos de build con incidencias mantenibles por el equipo.

## Recomendaciones inmediatas

1. Priorizar la reducción de errores de tipado en controladores portal y relation managers de Filament.
2. Mantener Pint como deuda separada de estilo para no mezclar cambios funcionales.
3. Mantener ESLint acotado a resources/js para evitar ruido fuera del frontend propio.
4. Repetir esta misma batería de comandos tras cada bloque de correcciones para medir reducción real.

## Comandos ejecutados en esta iteración

- npx eslint resources/js
- vendor/bin/pint --test
- composer lint
- vendor/bin/phpstan analyse
- composer analyse
