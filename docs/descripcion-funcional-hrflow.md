# HRFlow

HRFlow es una aplicacion de gestion de recursos humanos orientada a empresas que necesitan centralizar su operativa diaria en un unico entorno. Combina un panel de administracion para la empresa con un portal de autoservicio para la plantilla.

Su proposito dentro de la aplicacion es cubrir el ciclo operativo principal de RR. HH.: estructura organizativa, empleados, control horario, solicitudes, documentacion, calendario laboral y trazabilidad de acciones.

# Objetivo

HRFlow resuelve la necesidad de gestionar la informacion y los procesos habituales de personas desde una unica plataforma, evitando la dispersion entre hojas de calculo, correos, carpetas compartidas y herramientas aisladas.

Dentro del negocio cubre necesidades clave como la organizacion de empleados por empresa y departamento, el seguimiento de la jornada, la gestion de vacaciones y permisos, la publicacion de documentacion laboral y la supervision de la actividad con trazabilidad.

# Usuarios

- Superadministrador
  Perfil con vision global de la plataforma. Puede supervisar todas las empresas dadas de alta y acceder a la administracion completa del sistema.
- Administrador
  Perfil de gestion interna de una empresa. Opera sobre la configuracion y los datos de su organizacion, con capacidad amplia de administracion dentro de su propio entorno.
- Recursos Humanos
  Perfil operativo especializado en la gestion de personas. Puede administrar informacion laboral, documental, solicitudes y seguimiento interno dentro de su empresa, sin alcance global sobre la plataforma.
- Responsable
  Perfil de supervision operativa. Su actividad se centra en el seguimiento del equipo o departamento que tiene asignado, especialmente en solicitudes, calendario y control diario.
- Empleado
  Perfil de autoservicio. Utiliza el portal de su empresa para registrar jornada, consultar informacion propia, revisar documentacion y gestionar solicitudes personales.

Las diferencias entre perfiles vienen marcadas por el alcance de los datos que pueden consultar y por el tipo de acciones que pueden ejecutar. El superadministrador tiene alcance global, los perfiles de administracion y RR. HH. operan dentro de su organizacion, el responsable actua sobre su ambito asignado y el empleado solo gestiona su propia informacion.

# Funcionalidades principales

- Gestionar empresas y mantener separada la informacion de cada una.
- Dar de alta y mantener perfiles laborales de empleados.
- Organizar empleados por departamentos y responsables.
- Asignar turnos de trabajo y definir festivos laborales.
- Registrar entradas y salidas de jornada.
- Consultar historico de fichajes y resumenes de horas.
- Solicitar vacaciones y permisos retribuidos.
- Revisar, aprobar o rechazar solicitudes segun el perfil autorizado.
- Gestionar documentacion asociada al empleado.
- Permitir la consulta y descarga de documentacion personal cuando este habilitada.
- Mostrar un calendario laboral con festivos, turnos y ausencias aprobadas.
- Enviar notificaciones sobre eventos relevantes del flujo operativo.
- Mantener trazabilidad de las acciones administrativas mas importantes.

# Flujo funcional

1. Un usuario con perfil de administracion accede al area de gestion de su empresa.
2. Da de alta o actualiza la estructura organizativa, los empleados, los departamentos, los turnos y los festivos necesarios.
3. Los empleados acceden al portal de su empresa con sus credenciales.
4. Desde el portal registran su jornada, consultan su calendario, revisan sus documentos y presentan solicitudes.
5. Los responsables o perfiles autorizados revisan las solicitudes recibidas y toman una decision.
6. El sistema valida los datos introducidos y aplica las restricciones funcionales correspondientes.
7. La informacion aprobada o confirmada pasa a estar disponible en el resto de modulos relacionados, como calendario, historicos y seguimiento operativo.
8. Las acciones relevantes quedan registradas para facilitar control y trazabilidad.

# Reglas de negocio

- Cada empresa gestiona exclusivamente sus propios datos.
- Un usuario no puede operar sobre informacion de otra empresa.
- El empleado solo puede consultar y gestionar su propia informacion en el portal.
- El responsable actua sobre el equipo o ambito que tiene asignado, no sobre toda la empresa.
- Los perfiles de administracion operan sobre la informacion de su empresa segun su nivel de permiso.
- El identificador interno del empleado debe ser unico dentro de cada empresa.
- Las solicitudes deben indicar tipo y rango de fechas valido.
- La fecha de fin de una solicitud no puede ser anterior a la fecha de inicio.
- Las vacaciones no pueden solicitarse por encima del saldo disponible.
- El saldo disponible se calcula teniendo en cuenta las solicitudes ya aprobadas.
- Solo las ausencias aprobadas impactan en la planificacion visible del calendario.
- Un empleado no puede mantener dos jornadas activas al mismo tiempo.
- Los documentos personales solo son accesibles para el propio empleado cuando la empresa los ha marcado como visibles para el portal.
- La trazabilidad de acciones administrativas forma parte del control funcional del sistema.

# Permisos

## Visualizar

- El superadministrador puede visualizar la informacion global de la plataforma.
- El administrador puede visualizar la informacion completa de su empresa, incluida la trazabilidad disponible para su organizacion.
- El perfil de Recursos Humanos puede visualizar la informacion operativa de personas dentro de su empresa.
- El responsable puede visualizar la informacion operativa de su equipo o departamento dentro de su ambito.
- El empleado puede visualizar unicamente su informacion personal, sus solicitudes, sus fichajes, su calendario y su documentacion habilitada.

## Crear

- El superadministrador puede crear empresas y registros operativos en cualquier entorno.
- El administrador puede crear registros operativos de su empresa, como empleados, departamentos, turnos, festivos, documentos, solicitudes o registros relacionados.
- El perfil de Recursos Humanos puede crear y mantener informacion operativa de personas dentro de su empresa, siempre dentro de su ambito autorizado.
- El responsable no dispone de un alta estructural general; su funcion principal es la supervision y gestion del ambito asignado.
- El empleado puede generar sus propios fichajes y presentar sus propias solicitudes.

## Modificar

- El superadministrador puede modificar la informacion global y operativa de todas las empresas.
- El administrador puede modificar la informacion operativa de su empresa y mantener su configuracion funcional.
- El perfil de Recursos Humanos puede modificar informacion operativa de empleados, solicitudes, control horario, documentacion y estructura interna dentro de su empresa.
- El responsable puede modificar informacion limitada a su ambito de responsabilidad, especialmente en procesos de seguimiento y resolucion.
- El empleado puede modificar o completar interacciones propias permitidas por el portal, siempre dentro de su informacion personal y operativa.

## Eliminar

- El superadministrador puede eliminar registros dentro de su alcance global cuando la funcionalidad lo permita.
- El administrador puede eliminar determinados registros operativos de su empresa segun la politica funcional del modulo.
- El perfil de Recursos Humanos no dispone de una capacidad general de eliminacion sobre los modulos operativos.
- El responsable no dispone de capacidad general de eliminacion.
- El empleado no dispone de capacidad general de eliminacion.

# Datos gestionados

- Empresas y su contexto de operacion.
- Empleados y su informacion laboral basica.
- Departamentos, responsables y estructura organizativa.
- Roles funcionales y nivel de acceso.
- Turnos de trabajo y sus asignaciones.
- Festivos aplicables a cada empresa.
- Registros diarios de jornada y resumenes de horas.
- Solicitudes de vacaciones y permisos, con su estado de tramitacion.
- Documentacion laboral asociada a cada empleado.
- Notificaciones derivadas de acciones relevantes.
- Evidencias de trazabilidad sobre acciones administrativas.

# Integracion con otros modulos

- Empresas
  Define el contexto de trabajo y el aislamiento funcional de la informacion.
- Usuarios y permisos
  Determina que puede hacer cada perfil y sobre que datos puede operar.
- Departamentos
  Organiza la estructura interna y condiciona parte del circuito de supervision y aprobacion.
- Turnos
  Alimenta la planificacion diaria y complementa la lectura del calendario y del control horario.
- Festivos
  Se integran en la vision de calendario para mejorar la planificacion laboral.
- Solicitudes
  Impactan en calendario, notificaciones y seguimiento de ausencias.
- Documentacion
  Se vincula al empleado y depende del nivel de acceso definido por la empresa.
- Auditoria
  Recoge acciones administrativas relevantes realizadas desde los distintos ambitos de gestion.
- Notificaciones
  Informa a empleados y responsables sobre cambios de estado y eventos operativos.

# Validaciones

- Los datos principales de cada proceso deben informarse de forma obligatoria.
- Los formatos de fechas deben ser validos y coherentes con el proceso iniciado.
- Las relaciones entre empleados, departamentos, documentos, solicitudes y otros registros deben pertenecer a la misma empresa.
- Los identificadores internos del empleado no pueden duplicarse dentro de la misma empresa.
- Las solicitudes deben incluir un tipo valido y un rango temporal coherente.
- Las vacaciones deben respetar el saldo disponible.
- No se permite iniciar una nueva jornada si ya existe una jornada activa del mismo empleado.
- Un empleado no puede actuar sobre fichajes, solicitudes o documentos de otras personas.
- La descarga de documentos personales esta condicionada a su visibilidad para el portal.


# Observaciones

- El MVP actual no expone una API operativa para integraciones externas.
- La interacción funcional del sistema se realiza exclusivamente a través del backoffice y del portal del empleado.
- La exposición programática del dominio se considera una evolución futura y no forma parte del alcance actual.
