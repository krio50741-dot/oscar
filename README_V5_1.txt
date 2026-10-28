PLANIFICADOR GANTT DRALA - ACTUALIZACIÓN V5.1 ADMINISTRACIÓN AMIGABLE

Esta actualización NO reemplaza config.php ni requiere una nueva migración de base de datos.

ARCHIVOS A REEMPLAZAR:
1) /htdocs/admin.php
2) /htdocs/assets/style.css

MEJORAS:
- Administración completamente rotulada en español.
- Ayuda desplegable dentro de la misma página.
- Explicación de Usuario, Administrador, Lista desplegable y Orden.
- Ejemplo visual para Orden: negativos primero, 0 normal, positivos después.
- Recomendación de ordenar 10, 20, 30... para insertar elementos posteriormente.
- Posibilidad de crear una categoría de lista personalizada desde Administración.
- Nombres amigables de listas estándar: Estado, Prioridad, Área / Unidad, Riesgo, Hito de compra pública, Departamento / Unidad y Responsable.
- Ayuda debajo de cada campo para facilitar el llenado.

NOTA:
Las listas estándar están conectadas a los formularios actuales. Las nuevas categorías personalizadas se pueden administrar y quedan disponibles para futuras ampliaciones/campos del sistema.

INSTALACIÓN:
- Subir admin.php a /htdocs y reemplazar el existente.
- Subir style.css a /htdocs/assets y reemplazar el existente.
- No tocar config.php.
- No ejecutar migrate_v5.php.
- Recargar el navegador con Cmd + Shift + R.
