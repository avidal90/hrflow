# HRFlow - Seguridad y Security by Design

## 1. Objetivo

Documentar amenazas, controles y criterios de seguridad para proteger datos de RRHH en un entorno SaaS multi-tenant.

## 2. Principios

- Minimo privilegio.
- Defensa en profundidad.
- Denegacion por defecto.
- Trazabilidad completa.
- Privacidad y proteccion de datos personales por diseno.
- Security by Design aplicado desde analisis hasta pruebas.

## 2.1 Gobernanza de seguridad en el ciclo de desarrollo

- Cada historia funcional debe incluir amenaza principal, control preventivo y control detectivo.
- Ningun flujo critico se considera terminado sin evidencia de validacion, autorizacion y auditoria.
- Los cambios de seguridad se apoyan en documentacion oficial consultada via Laravel Boost cuando el riesgo no es trivial.
- Las decisiones deben mantener cohesion con SOLID para evitar disenos inseguros por acoplamiento excesivo.

## 3. Superficies de riesgo

- Acceso a datos de empleados.
- Flujos de aprobacion (vacaciones/ausencias).
- Control horario y modificaciones retroactivas.
- Acceso a documentos sensibles.
- Exportaciones e informes.

## 4. Matriz de amenazas y controles

### 4.1 Fuga entre tenants

Riesgo:
- Acceso horizontal a datos de otra empresa.

Controles:
- Contexto tenant obligatorio.
- Scope de tenant en consultas.
- Policies tenant-aware.
- Pruebas de aislamiento.

### 4.2 Escalada de privilegios

Riesgo:
- Usuario con rol bajo ejecuta acciones de aprobacion/admin.

Controles:
- RBAC con permisos granulares.
- Policies por accion.
- Auditoria de intentos denegados.

### 4.3 Manipulacion de registros horarios

Riesgo:
- Edicion fraudulenta de fichajes.

Controles:
- Estados Closed/Locked.
- Flujo de correccion con justificacion y auditoria.
- Alertas para cambios retroactivos.

### 4.4 Exposicion de documentos

Riesgo:
- Descarga o subida no autorizada.

Controles:
- ACL por rol + tenant + propiedad.
- Almacenamiento privado.
- Enlaces temporales cuando aplique.

## 5. OWASP Top 10 aplicado

- Broken Access Control: policies estrictas, pruebas de autorizacion.
- Cryptographic Failures: hashes robustos, cifrado en transito y en reposo segun criticidad.
- Injection: uso de ORM/query builder, validacion y saneado.
- Insecure Design: revisiones de flujo y threat modeling por modulo.
- Security Misconfiguration: configuraciones seguras por entorno.
- Identification and Authentication Failures: sesiones seguras y controles de login.
- Software and Data Integrity Failures: pipeline controlado y dependencias auditadas.
- Security Logging and Monitoring Failures: logs accionables con alertas.

## 6. Controles por flujo critico

## 6.1 Solicitud de vacaciones

- Validar pertenencia tenant.
- Validar saldo y solapes.
- Autorizar aprobador segun politica.
- Registrar decision con motivo.

## 6.2 Registro horario

- Respetar secuencia temporal.
- Evitar ediciones fuera de politica.
- Registrar cambios manuales con evidencia.

## 6.3 Gestion documental

- Validar tipo y tamano de archivo.
- Controlar acceso por rol y propietario.
- Auditar descarga/subida de documentos sensibles.

## 7. Datos personales y cumplimiento

- Minimizar datos almacenados.
- Retencion segun tipo documental y marco legal aplicable.
- Derecho de acceso y trazabilidad de operaciones.

## 8. Operacion segura

- Gestion de secretos por entorno.
- Rotacion de credenciales y claves.
- Backups cifrados y pruebas de restauracion.
- Monitoreo de eventos de seguridad.

## 9. Criterios de aceptacion del documento

- Amenazas criticas identificadas y mapeadas a controles.
- Controles por flujo critico definidos.
- Coherencia con architecture, modules y coding-standards.
- Incluye reglas operativas para verificar Security by Design durante implementacion y PR.
