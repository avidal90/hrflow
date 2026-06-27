# HRFlow

Plataforma SaaS multiempresa para gestion de recursos humanos, desarrollada como Trabajo de Fin de Master (FTM).

## Finalidad del proyecto

HRFlow nace para demostrar, en un entorno realista, como construir una aplicacion empresarial moderna con:

- Arquitectura limpia y mantenible.
- Buenas practicas (SOLID).
- Enfoque Security by Design.
- Aislamiento de datos por empresa (multi-tenant).

El objetivo no es un producto comercial completo, sino un MVP profesional, estable y defendible academicamente.

## Resumen de caracteristicas

- Multi-tenancy: cada empresa opera con sus datos completamente aislados.
- Gestion de empleados y departamentos.
- Gestion documental del empleado (contratos, nominas, certificados, etc.).
- Control horario (entrada, salida, pausas e historico).
- Flujo de vacaciones y ausencias (solicitud, aprobacion, rechazo).
- Roles y permisos por perfil de usuario.
- Registro de auditoria para trazabilidad de acciones.
- Base para reporting operativo.

## Perfiles de usuario

- Superadministrador.
- Administrador de empresa.
- Recursos humanos.
- Responsable de departamento.
- Empleado.

## Stack tecnologico

- Backend: Laravel 13 + PHP 8.3.
- Frontend: Blade + Livewire.
- Panel de administracion: Filament.
- Base de datos: MariaDB.
- Infraestructura complementaria: Redis, colas y scheduler.

## Principios de implementacion

- Calidad y legibilidad del codigo por encima de soluciones rapidas.
- Seguridad y autorizacion explicita en flujos criticos.
- Trazabilidad mediante auditoria.
- Enfoque incremental orientado a un MVP robusto.

## Alcance MVP

Incluye los modulos funcionales prioritarios:

- Empleados.
- Departamentos.
- Control horario.
- Vacaciones.
- Roles y permisos.
- Auditoria basica.

## Documentacion del proyecto

Para mas detalle funcional y tecnico:

- [Vision general](docs/project-overview.md)
- [Modulos funcionales](docs/modules.md)
- [Modelo de dominio](docs/domain-model.md)
- [Arquitectura](docs/architecture.md)
- [Seguridad](docs/security.md)

## Estado

Proyecto en desarrollo activo con enfoque academico-profesional.
