PLANIFICADOR GANTT DRALA – ACTUALIZACIÓN V5.2
PLAN DE TAREAS COMPLETO + CARTA GANTT SEMANAL TIPO EXCEL

INSTALACIÓN:
1. Mantenga su config.php actual.
2. En /htdocs reemplace:
   - tasks.php
   - gantt.php
   - task_form.php
3. En /htdocs/assets reemplace:
   - style.css
4. No ejecute migrate_v5.php. Esta actualización NO requiere migración.
5. Actualice el navegador con Cmd + Shift + R.

PLAN DE TAREAS:
Mantiene los 25 campos operativos solicitados:
ID, Nivel, ID Proceso Padre, Área/Unidad, Proceso/Proyecto, Etapa/Tarea,
Responsable, Prioridad, Estado, Fecha inicio, Fecha término planificada,
Duración, % Avance Automático, Días restantes, Alerta, Semáforo,
% Participación automática, Dependencia/Observación, Hito Compra Pública,
N° Requerimiento/Licitación/OC, Monto estimado, Proveedor, Próximo hito,
Fecha próximo hito y Riesgo.

Además conserva trazabilidad V5: ingresado por, fecha ingreso, última modificación y acciones.

CARTA GANTT:
Vista semanal tipo Excel con columnas fijas:
ID | Etapa/Tarea | Responsable | Inicio | Término | % Avance
y semanas dinámicas desde la fecha mínima a la máxima del plan.
Las barras usan el semáforo de cada registro.

LÓGICA:
- Etapas del mismo proceso reciben participación igual.
- Proceso calcula avance promedio de sus etapas.
- Duración incluye inicio y término.
- Tareas terminadas/completadas muestran 0 días restantes.
- Se conserva historial de cambios de fechas y plazo original de V5.
