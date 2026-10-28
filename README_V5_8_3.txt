PLANIFICADOR GANTT DRALA – V5.8.3
ADMINISTRACIÓN + HISTORIAL DE CAMBIOS

CORRECCIONES
1. La ayuda de Administración ahora usa un control nativo Abrir/Cerrar.
   No depende de JavaScript, por lo que funciona en InfinityFree y navegadores actuales.

2. Nueva pantalla: Historial de cambios (audit.php)
   Solo visible para administradores.
   Muestra:
   - Fecha y hora
   - Quién realizó la acción
   - Nombre de usuario
   - Perfil (Administrador / Usuario)
   - Acción realizada
   - Detalle de lo modificado

3. Filtros del historial:
   - Usuarios y administradores
   - Tareas y plazos
   - Listas desplegables
   - Persona que realizó el cambio
   - Fecha desde / hasta
   - Búsqueda libre

4. Mejor trazabilidad de Administración:
   - Al modificar usuario registra valor anterior → valor nuevo.
   - Al activar/desactivar registra estado anterior → nuevo.
   - Recuperación de contraseña queda registrada.
   - Alta, edición y eliminación de opciones de listas quedan detalladas.
   - Ejecución manual de alertas queda registrada.

INSTALACIÓN
En /htdocs reemplazar:
- admin.php
- header.php

Agregar:
- audit.php

En /htdocs/assets reemplazar:
- style.css

NO MODIFICAR:
- config.php
- app_core.php
- base de datos MySQL

NO requiere migración de base de datos.
Después recargue con Cmd + Shift + R.
