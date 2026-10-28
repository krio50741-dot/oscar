<?php
require 'app_core.php'; require_login(); $p=db();
$q=trim($_GET['q']??''); $status=trim($_GET['status']??''); $area=trim($_GET['area']??'');
$sql="SELECT t.*,uc.name creator_name,uc.email creator_email,uu.name updater_name FROM tasks t LEFT JOIN users uc ON uc.id=t.created_by LEFT JOIN users uu ON uu.id=t.updated_by WHERE 1=1";$args=[];
if($q!==''){$sql.=" AND (t.code LIKE ? OR t.task LIKE ? OR t.project LIKE ? OR t.responsible LIKE ? OR t.reference_no LIKE ? OR t.provider LIKE ?)";$v='%'.$q.'%';array_push($args,$v,$v,$v,$v,$v,$v);}
if($status!==''){$sql.=" AND t.status=?";$args[]=$status;} if($area!==''){$sql.=" AND t.area=?";$args[]=$area;}
$sql.=" ORDER BY COALESCE(NULLIF(t.parent_code,''),t.code), CASE t.level WHEN 'PROCESO' THEN 0 ELSE 1 END, t.start_date,t.id";
$st=$p->prepare($sql);$st->execute($args);$rows=$st->fetchAll();
$areas=catalog_values('area');$statuses=catalog_values('status');
$childCounts=[];$childProgress=[];
foreach($rows as $rr){if(($rr['level']??'')==='ETAPA' && !empty($rr['parent_code'])){$pc=$rr['parent_code'];$childCounts[$pc]=($childCounts[$pc]??0)+1;$childProgress[$pc]=($childProgress[$pc]??0)+calc($rr)['progress'];}}
function v52_duration($r){if(empty($r['start_date'])||empty($r['end_date']))return null;return max(1,(int)(new DateTime($r['start_date']))->diff(new DateTime($r['end_date']))->format('%a')+1);}
function v52_days_left($r){if(empty($r['end_date']))return null;if(in_array($r['status']??'', ['Terminada','Completada'],true))return 0;return (int)(new DateTime('today'))->diff(new DateTime($r['end_date']))->format('%r%a');}
function v52_date($d){return $d?date('d-m-Y',strtotime($d)):'—';}
require 'header.php';?>
<div class="page-title"><div><h1>Plan de tareas</h1><p>Vista completa tipo planilla. Mantiene todos los campos operativos, cálculos automáticos, trazabilidad y alertas.</p></div><a class="btn primary" href="task_form.php?level=PROCESO">＋ Nueva tarea / proceso</a></div>
<form class="excel-toolbar"><div class="search"><span>⌕</span><input name="q" value="<?=h($q)?>" placeholder="Buscar ID, proceso, tarea, responsable, OC, proveedor..."></div><select name="area"><option value="">Todas las áreas</option><?php foreach($areas as $x):?><option <?=$area===$x?'selected':''?>><?=h($x)?></option><?php endforeach;?></select><select name="status"><option value="">Todos los estados</option><?php foreach($statuses as $x):?><option <?=$status===$x?'selected':''?>><?=h($x)?></option><?php endforeach;?></select><button class="btn">Filtrar</button><a class="btn ghost" href="tasks.php">Limpiar</a><a class="btn ghost" href="gantt.php">Ver Carta Gantt</a></form>
<div class="sheet-card full-sheet"><div class="table-scroll"><table class="excel-table plan-table"><thead><tr>
<th>ID</th><th>Nivel</th><th>ID Proceso Padre</th><th>Área / Unidad</th><th>Proceso / Proyecto</th><th>Etapa / Tarea</th><th>Responsable</th><th>Prioridad</th><th>Estado</th><th>Fecha inicio</th><th>Fecha término planificada</th><th>Duración<br>(días)</th><th>% Avance<br>Automático</th><th>Días restantes</th><th>Alerta</th><th>Semáforo</th><th>% Participación<br>automática</th><th>Dependencia / Observación</th><th>Hito Compra Pública</th><th>N° Requerimiento / Licitación / OC</th><th>Monto estimado</th><th>Proveedor</th><th>Próximo hito</th><th>Fecha próximo hito</th><th>Riesgo</th><th>Ingresado por</th><th>Fecha ingreso</th><th>Última modificación</th><th>Acciones</th>
</tr></thead><tbody>
<?php foreach($rows as $r):
$c=calc($r);$ex=extension_info($r);$dur=v52_duration($r);$left=v52_days_left($r);
if(($r['level']??'')==='PROCESO' && !empty($childCounts[$r['code']])){$prog=(int)round($childProgress[$r['code']]/$childCounts[$r['code']]);}else{$prog=(int)$c['progress'];}
$part=(($r['level']??'')==='ETAPA'&&!empty($r['parent_code'])&&!empty($childCounts[$r['parent_code']]))?100/$childCounts[$r['parent_code']]:null;
?>
<tr class="<?=$r['level']==='PROCESO'?'process-row':'stage-row'?>">
<td class="mono"><b><?=h($r['code'])?></b></td><td><span class="level-badge <?=strtolower($r['level'])?>"><?=h($r['level'])?></span></td><td><?=h($r['parent_code']?:'—')?></td><td><?=h($r['area']?:'—')?></td><td><?=h($r['project']?:'—')?></td>
<td class="taskcell"><?=$r['level']==='ETAPA'?'<span class="tree">↳</span>':''?><strong><?=h($r['task'])?></strong></td><td><?=h($r['responsible']?:'—')?></td><td><?=h($r['priority']?:'—')?></td><td><?=h($r['status']?:'—')?></td><td><?=v52_date($r['start_date'])?></td><td><?=v52_date($r['end_date'])?></td><td class="num"><?=$dur===null?'—':$dur?></td>
<td><div class="progress-cell"><div><i style="width:<?=$prog?>%"></i></div><b><?=$prog?>%</b></div></td><td class="num"><?=$left===null?'—':$left?></td><td><span class="alert <?=strtolower($c['light'])?>"><?=h($c['alert'])?></span><?php if($ex['extended']):?><br><span class="alert amarillo">EXTENDIDA +<?=$ex['days']?> DÍAS</span><?php endif;?><?php if($ex['wasOverdue']):?><br><span class="alert rojo">FUERA PLAZO ORIGINAL</span><?php endif;?></td>
<td><span class="traffic <?=$c['light']?>" title="<?=h($c['light'])?>"></span><small><?=h($c['light'])?></small></td><td class="num"><?=$part===null?'—':number_format($part,2,',','.').'%'?></td><td><?=h($r['dependency']?:'—')?></td><td><?=h($r['purchase_milestone']?:'—')?></td><td><?=h($r['reference_no']?:'—')?></td><td class="money"><?=$r['amount']!==null&&$r['amount']!==''?'$'.number_format((float)$r['amount'],0,',','.'):'—'?></td><td><?=h($r['provider']?:'—')?></td><td><?=h($r['next_milestone']?:'—')?></td><td><?=v52_date($r['next_date'])?></td><td><?=h($r['risk']?:'—')?></td>
<td><?=h($r['creator_name']??'—')?><small><?=h($r['creator_email']??'')?></small></td><td><?=h($r['created_at']??'—')?></td><td><?=h($r['updater_name']??$r['creator_name']??'—')?><small><?=h($r['updated_at']??'')?></small></td>
<td class="actions"><?php if(admin()||((int)$r['created_by']===current_user_id())):?><a href="task_form.php?id=<?=$r['id']?>"><?=admin()?'Editar':'Modificar fechas'?></a><?php endif;?><?php if($r['level']==='PROCESO'):?><a class="add" href="task_form.php?level=ETAPA&parent=<?=urlencode($r['code'])?>">＋ Etapa</a><?php endif;?></td>
</tr><?php endforeach;?></tbody></table></div></div>
<div class="sheet-help"><b>Lectura rápida:</b> el proceso calcula su avance desde sus etapas; la participación de cada etapa se reparte automáticamente en partes iguales. Las fechas ampliadas conservan el plazo original y su historial.</div>
<?php require 'footer.php';?>