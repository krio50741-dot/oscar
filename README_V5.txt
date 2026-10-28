PLANIFICADOR GANTT DRALA V5 — CONTROL, TRAZABILIDAD Y SEGURIDAD

NUEVAS FUNCIONES
- Registra quién creó cada tarea, fecha/hora de creación, quién la modificó y fecha/hora.
- Usuarios: nombre, correo, departamento/unidad, usuario, rol y estado.
- Permite múltiples administradores.
- Administrador: modifica datos sensibles, usuarios, roles y listas desplegables.
- Usuario: crea nuevas tareas/procesos/etapas. En tareas ya creadas por él, solo puede modificar fechas.
- Cada cambio de fecha exige motivo y guarda fecha anterior/nueva, usuario y fecha del cambio.
- Conserva fecha término original y marca extensión de plazo y fuera del plazo original.
- Alertas por vencimiento: 7 días, 3 días, vence hoy y atrasada.
- Destinatarios: creador de la tarea + todos los administradores activos con correo.
- Recuperación de contraseña por enlace seguro al correo de la persona solicitante.
- Nunca se envían contraseñas en texto plano por correo.
- Catálogos editables por administrador: estado, prioridad, área, riesgo, hito, departamento y responsable.

INSTALACIÓN SOBRE V4
1) Haga respaldo de la base de datos y de /htdocs.
2) Suba los archivos de esta actualización a /htdocs, reemplazando los existentes cuando corresponda.
3) NO reemplace ni elimine config.php.
4) Abra una sola vez: /migrate_v5.php estando conectado como administrador.
5) Verifique que indique actualización correcta.
6) Elimine migrate_v5.php de /htdocs después de usarlo.
7) Complete correo y departamento de los usuarios desde Administración.

CORREO
- mail_config.php viene con enabled=false.
- Para activar correos, cambie enabled a true y configure un remitente válido.
- Esta versión usa mail() de PHP. Si el hosting no permite mail(), será necesario configurar un proveedor SMTP/API en una siguiente integración.
- Las alertas se revisan automáticamente cuando un usuario entra al sistema y también con el botón “Enviar alertas ahora”.
- Para alertas sin visitas al sistema, se recomienda un cron externo que invoque alert_runner.php con un token configurado en el entorno. InfinityFree puede imponer restricciones a tareas programadas/correo.

SEGURIDAD DE CONTRASEÑAS
Por seguridad, el sistema NO envía la contraseña actual ni una nueva contraseña por correo. El correo contiene un enlace temporal de 2 horas para que el propio usuario defina una contraseña nueva. Después del cambio se envía una notificación al mismo correo.
