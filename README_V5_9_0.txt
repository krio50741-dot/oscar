PLANIFICADOR GANTT DRALA — ACTUALIZACIÓN V5.9.0
IDENTIDAD VISUAL INSTITUCIONAL

Incluye:
- Nueva pantalla de ingreso profesional.
- Identidad visual Aduanas Chile en el acceso.
- Logo institucional visible también dentro del sistema.
- Encabezado superior más elegante y ordenado.
- Diseño responsive para computador y teléfono.
- Logo guardado localmente: no depende de Internet para mostrarse.

INSTALACIÓN
En /htdocs reemplazar:
1. login.php
2. header.php

En /htdocs/assets:
3. reemplazar style.css
4. agregar aduanas-chile.svg

NO reemplazar:
- config.php
- app_core.php
- base de datos
- archivos de tareas/Gantt/Administración

Después:
- cerrar sesión para ver la nueva portada de ingreso
- recargar con Cmd + Shift + R

Esta actualización NO requiere migración de base de datos.
