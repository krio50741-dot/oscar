PLANIFICADOR GANTT DRALA — V6.0
SINCRONIZACIÓN EXCEL OFFLINE → APLICACIÓN

Nueva opción: Herramientas > Sincronizar Excel.

Flujo:
1. Trabaje sin conexión en la plantilla Excel.
2. Al recuperar conexión, cargue el .xlsx.
3. La aplicación presenta una vista previa.
4. Compara cada fila por ID.
5. ID nuevo = crea registro. ID existente = actualiza solo si cambió.
6. Los registros ausentes del Excel NO se eliminan.
7. Los cambios de fechas quedan en historial.
8. La auditoría registra quién importó, cuándo y qué cambió.

Solo administradores pueden ejecutar la sincronización masiva.

INSTALACIÓN
/htdocs:
- reemplazar header.php
- agregar import_excel.php
- reemplazar audit.php

/htdocs/assets:
- reemplazar style.css

Crear /htdocs/templates y subir:
- Carta_Gantt_DRALA_Offline.xlsx

NO modificar config.php, app_core.php ni MySQL.
No requiere migración.
