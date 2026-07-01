# HRFlow - Estandares de desarrollo Laravel

## 1. Objetivo

Definir convenciones de implementacion para asegurar consistencia, calidad y mantenibilidad.

## 2. Principios base

- SOLID
- Clean Code
- DRY
- KISS
- Security by Design
- PSR-12

## 2.1 Regla metodologica del proyecto

- Toda implementacion debe justificar su diseno con SOLID y controles de Security by Design.
- Laravel Boost se usa como MCP de referencia para validar enfoque tecnico contra documentacion oficial del stack.
- Antes de cambios estructurales, se consulta documentacion relevante mediante Boost y se registra la decision tecnica en PR/nota de trabajo.

## 3. Estructura y responsabilidades

- Controllers: delgados, solo orquestacion HTTP.
- Form Requests: validacion de entrada obligatoria.
- Services: logica de casos de uso.
- Policies: autorizacion por recurso/accion.
- Models: persistencia y relaciones.
- Jobs/Events: procesos asincronos y desacople.

## 4. Convenciones de naming

- Clases: PascalCase.
- Metodos/variables: camelCase.
- Campos booleanos: prefijo is/has/can.
- Eventos: pasado semantico (VacationApproved).
- Servicios: sufijo Service (CreateEmployeeService).

## 5. Reglas de calidad de codigo

- Tipado estricto en parametros y retornos.
- Metodos pequenos con una responsabilidad.
- Evitar logica de negocio en vistas y controladores.
- Preferir expresividad sobre abreviaturas ambiguas.

## 6. Eloquent y consultas

- Definir relaciones explicitamente.
- Evitar N+1 con eager loading.
- Usar scopes reutilizables para filtros comunes.
- Aplicar siempre restriccion tenant en consultas de negocio.

## 7. Validacion y DTOs

- Todo endpoint mutante usa Form Request.
- DTOs para transferir datos entre capas cuando el caso de uso lo justifique.
- Mensajes de validacion claros y orientados al usuario.

## 8. Autorizacion

- Toda accion sobre recursos de negocio pasa por Policy.
- Validar simultaneamente rol, permiso y tenant.
- Negar por defecto cuando exista duda de acceso.

## 9. Manejo de errores

- Excepciones de dominio claras y especificas.
- Respuestas de error consistentes.
- Registrar errores sin filtrar informacion sensible.

## 10. Auditoria y trazabilidad

- Registrar operaciones sensibles de lectura/escritura criticas.
- Incluir actor, tenant, entidad y cambios relevantes.

## 11. Testing

- Feature tests para workflows completos.
- Unit tests para reglas de dominio.
- Tests de autorizacion y de aislamiento tenant como obligatorios.
- Cobertura minima orientativa por modulo critico: 80 por ciento.

## 12. Reglas de PR

- Sin cambios de estilo no relacionados.
- Incluir pruebas del comportamiento modificado.
- Confirmar checklist de tenancy, seguridad y auditoria.
- Confirmar evidencia de alineacion SOLID en la solucion propuesta.
- Confirmar consulta previa de documentacion/guia via Laravel Boost en cambios de arquitectura, seguridad o patrones.

## 12.1 Checklist minimo de calidad (obligatorio)

- SRP: la clase o metodo cambia una sola responsabilidad.
- OCP: la extension no exige modificar multiples clases existentes sin necesidad.
- DIP: la logica de aplicacion no depende directamente de detalles de infraestructura.
- Seguridad: existe validacion + autorizacion + auditoria en el flujo.
- Tenancy: no hay lectura/escritura fuera de contexto tenant.
- Boost: existe referencia a documentacion consultada para sustentar decisiones no triviales.

## 13. Criterios de aceptacion del documento

- Define convenciones claras y aplicables.
- Refuerza practicas Laravel consistentes con AGENTS.md.
- Es util como guia de onboarding tecnico.
- Establece de forma verificable el uso de SOLID, Security by Design y Laravel Boost MCP.
