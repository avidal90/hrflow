# HRFlow - Arquitectura y capas

## 1. Objetivo

Definir la arquitectura funcional y tecnica de HRFlow para guiar una implementacion consistente, mantenible y segura en Laravel.

Este documento no describe detalle de codigo; establece decisiones de diseno y limites entre capas.

## 2. Principios de arquitectura

- Multi-tenant por diseno desde el inicio.
- Separacion estricta entre dominio, aplicacion e infraestructura.
- Seguridad integrada en cada flujo (Security by Design).
- Trazabilidad completa de acciones criticas.
- Escalabilidad horizontal para crecer por tenants y por modulos.
- Aplicacion practica de SOLID en diseno de casos de uso y componentes.
- Laravel Boost como MCP de referencia para decisiones tecnicas guiadas por documentacion oficial.

## 2.1 Aplicacion operativa de SOLID

- Single Responsibility: controllers y services con una unica responsabilidad de negocio.
- Open/Closed: reglas de negocio extensibles via policies, events y estrategias, sin romper implementaciones existentes.
- Liskov Substitution: contratos estables para repositorios/servicios con comportamiento intercambiable.
- Interface Segregation: interfaces pequenas y especificas por contexto de uso.
- Dependency Inversion: dependencia de abstracciones en capa de aplicacion, no de detalles de infraestructura.

## 2.2 Laravel Boost como MCP de referencia

- El flujo de trabajo tecnico se apoya en Boost para buscar documentacion versionada antes de cambios relevantes.
- Se priorizan herramientas Boost para inspeccion de base de datos, logs y diagnostico sobre alternativas manuales.
- Las decisiones de implementacion deben trazarse a una fuente oficial o guideline recuperada mediante Boost.

## 3. Contexto de solucion

### 3.1 Frontends

- Backoffice: Filament para administracion.
- Portal empleado: Blade + Livewire para experiencia simple y directa.

### 3.2 Backend

- Laravel 13 + PHP 8.3.
- MariaDB como base de datos principal.
- Redis para cache, colas y optimizacion de sesiones/locks.
- Queue Jobs para procesos asincronos.
- Scheduler para tareas periodicas de negocio.

## 4. Modelo de capas

## 4.1 Capa de Presentacion

Responsabilidad:
- Recibir interacciones de usuario (web/backoffice/API interna).
- Validar formato y reglas de entrada mediante Form Requests.
- Delegar logica a capa de aplicacion.

Elementos:
- Controllers HTTP.
- Componentes Livewire.
- Recursos de presentacion en Filament.

No debe contener:
- Reglas de negocio complejas.
- Acceso directo complejo a infraestructura.

## 4.2 Capa de Aplicacion

Responsabilidad:
- Orquestar casos de uso.
- Coordinar transacciones.
- Disparar eventos y jobs.
- Aplicar politicas de autorizacion.

Elementos:
- Services por caso de uso.
- DTOs para datos de entrada/salida de casos de uso.
- Handlers de comandos internos (si se adopta estilo CQRS ligero).

## 4.3 Capa de Dominio

Responsabilidad:
- Reglas de negocio puras.
- Invariantes de entidades.
- Estado y transiciones validas.

Elementos:
- Entidades y Value Objects.
- Reglas de negocio (metodos de dominio).
- Eventos de dominio.

## 4.4 Capa de Infraestructura

Responsabilidad:
- Persistencia.
- Integraciones externas.
- Adaptadores tecnicos.

Elementos:
- Modelos Eloquent y repositorios concretos.
- Sistema de archivos/documentos.
- Notificaciones, correo, colas.

## 5. Multi-tenancy

## 5.1 Requisito principal

Todo dato de negocio debe estar aislado por tenant.

## 5.2 Estrategia funcional recomendada

- Aislamiento por columna tenant_id en entidades de negocio.
- Scope global/trait de tenant para consultas.
- Resolucion de tenant en middleware de entrada.
- Policies con validacion tenant + rol + permiso.
- Base de datos unica (single database) para la simulacion FTM, evitando complejidad de database-per-tenant.

## 5.3 Reglas obligatorias

- Ningun flujo de lectura/escritura sin contexto tenant.
- Ninguna exportacion o reporte cruza tenants.
- Auditoria siempre incluye tenant_id.

## 6. Seguridad transversal

- Autenticacion de usuarios y sesiones seguras.
- Autorizacion basada en Policies y roles/permisos.
- Validacion de entradas con Form Requests.
- Proteccion CSRF/XSS/SQL Injection por estandares Laravel y controles adicionales.
- Registro de eventos de seguridad y accesos.

## 7. Eventos, jobs y scheduler

## 7.1 Eventos

Se emiten en acciones relevantes: altas, aprobaciones, rechazos, cambios de estado, eliminaciones.

## 7.2 Jobs

Procesos asincronos:
- Notificaciones.
- Generacion de reportes.
- Procesamiento documental.

## 7.3 Scheduler

Tareas periodicas:
- Recalculo de saldos de vacaciones.
- Limpieza de artefactos temporales.
- Verificacion de integridad y alertas.

## 8. Observabilidad

- Logging estructurado por tenant, modulo y actor.
- Trazabilidad de flujos criticos (auditoria funcional).
- Metricas operativas: latencia por modulo, tasa de errores, colas pendientes.

## 9. Calidad y pruebas

- Feature tests para casos de uso completos.
- Unit tests para reglas de dominio.
- Tests de autorizacion y aislamiento tenant en cada modulo.
- Pruebas de regresion para workflows criticos.

## 10. Riesgos arquitectonicos

- Fuga de datos entre tenants por omision de filtros.
- Logica de negocio distribuida en controllers.
- Acoplamiento excesivo entre modulos.
- Falta de auditoria en operaciones sensibles.

Mitigacion:
- Checklist de tenancy en PR.
- Politicas obligatorias por recurso.
- Reglas de arquitectura en coding-standards.

## 11. Criterios de aceptacion del documento

- Define claramente limites de capas.
- Describe estrategia multi-tenant aplicable.
- Cubre eventos/jobs/scheduler con uso funcional.
- Incluye riesgos y mitigaciones.
- Es consistente con domain-model, modules, security y roadmap.
- Declara explicitamente SOLID, Security by Design y Boost MCP como pilares de ejecucion.
