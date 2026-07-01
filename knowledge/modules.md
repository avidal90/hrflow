# HRFlow - Modulos funcionales

## 1. Objetivo

Describir el comportamiento funcional de cada modulo: actores, casos de uso, flujo principal, excepciones y reglas de negocio.

## 2. Modulo: Gestion de empleados

Actores:
- Administrador de empresa
- Recursos humanos

Casos de uso clave:
- Crear empleado.
- Editar perfil laboral.
- Asignar departamento y puesto.
- Cambiar estado laboral.

Flujo principal (alta):
1. Actor autorizado inicia alta.
2. Sistema valida datos.
3. Sistema crea empleado en tenant actual.
4. Sistema registra auditoria.

Excepciones:
- Codigo de empleado duplicado.
- Actor sin permisos.

Reglas:
- Todo empleado pertenece a un departamento.
- Toda alta queda auditada.

## 3. Modulo: Departamentos y organigrama

Actores:
- Administrador de empresa
- Recursos humanos

Casos de uso:
- Crear departamento.
- Designar responsable.
- Reasignar miembros.
- Consultar estructura.

Reglas:
- Un departamento solo contiene empleados del mismo tenant.
- Cambios de responsable generan evento de auditoria.

## 4. Modulo: Gestion documental

Actores:
- Recursos humanos
- Empleado (lectura limitada y subida segun politica)

Casos de uso:
- Subir contrato/nomina/certificado.
- Consultar historial documental.
- Definir vigencia de documentos.

Reglas:
- El acceso al documento se controla por rol y pertenencia.
- Los metadatos deben incluir tipo y propietario.

## 5. Modulo: Control horario

Actores:
- Empleado
- Responsable de departamento
- Recursos humanos

Casos de uso:
- Fichar entrada.
- Iniciar/finalizar pausa.
- Fichar salida.
- Revisar historial.

Flujo principal:
1. Empleado ficha entrada.
2. Sistema abre TimeEntry.
3. Empleado registra pausas.
4. Empleado ficha salida.
5. Sistema cierra y consolida jornada.

Excepciones:
- Intento de salida sin entrada abierta.
- Pausa inconsistente.

Reglas:
- Secuencia temporal valida obligatoria.
- Registros cerrados/locked no se editan sin control especial.

## 6. Modulo: Vacaciones

Actores:
- Empleado
- Responsable de departamento
- Recursos humanos

Casos de uso:
- Solicitar vacaciones.
- Aprobar/rechazar solicitud.
- Consultar saldo y calendario.

Flujo principal:
1. Empleado solicita rango de fechas.
2. Sistema valida solapes y saldo.
3. Responsable decide.
4. Sistema actualiza estado y audita.

Reglas:
- Rechazo requiere motivo.
- No se aprueba sin saldo.

## 7. Modulo: Ausencias y permisos

Actores:
- Empleado
- Responsable de departamento
- Recursos humanos

Casos de uso:
- Solicitar ausencia.
- Adjuntar justificante.
- Aprobar/rechazar.

Reglas:
- Tipologia de ausencia obligatoria.
- Trazabilidad de decision obligatoria.

## 8. Modulo: Calendario laboral

Actores:
- Empleado
- Responsable
- Recursos humanos

Casos de uso:
- Ver festivos, vacaciones, ausencias y turnos.
- Filtrar por equipo/departamento.

Reglas:
- Vista acotada por rol y tenant.

## 9. Modulo: Reporting

Actores:
- Recursos humanos
- Administrador de empresa
- Superadministrador (global)

Casos de uso:
- Informes de horas trabajadas.
- Informes de vacaciones y ausencias.
- Exportaciones controladas.

Reglas:
- Reportes tenant-level por defecto.
- Exportaciones registradas en auditoria.

## 10. Modulo: Auditoria

Actores:
- Superadministrador
- Administrador de empresa

Casos de uso:
- Consultar eventos de seguridad y negocio.
- Filtrar por actor, fecha, entidad o accion.

Reglas:
- Inmutabilidad funcional de logs.
- Contenido minimo para trazabilidad legal.

## 11. Prioridad MoSCoW

Must:
- Empleados
- Departamentos
- Roles y permisos
- Control horario
- Vacaciones
- Auditoria basica

Should:
- Documental avanzada
- Ausencias con workflow completo
- Reporting operativo

Could:
- Mejoras UX avanzadas
- IA opcional

Wont (fase inicial):
- Automatizaciones no criticas de alto coste.

## 12. Criterios de aceptacion del documento

- Cada modulo incluye actores, casos de uso, flujo y reglas.
- Se cubren todos los modulos del overview.
- Reglas son coherentes con domain-model, security y roadmap.
