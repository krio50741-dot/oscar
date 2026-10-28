<?php
require 'app_core.php';require_login();$p=db();$area=trim($_GET['area']??'');$level=trim($_GET['level']??'');$where=["start_date IS NOT NULL","end_date IS NOT NULL"];$params=[];
if($area!==''){$where[]='area=?';$params[]=$area;}if($level!==''){$where[]='level=?';$params[]=$level;}
$st=$p->prepare('SELECT * FROM tasks WHERE '.implode(' AND ',$where).' ORDER BY COALESCE(NULLIF(parent_code,""),code),CASE level WHEN "PROCESO" THEN 0 ELSE 1 END,start_date,code');$st->execute($params);$rows=$st->fetchAll();
$areas=catalog_values('area');
$childCounts=[];$childProgress=[];foreach($rows as $rr){if(($rr['level']??'')==='ETAPA'&&!empty($rr['parent_code'])){$pc=$rr['parent_code'];$childCounts[$pc]=($childCounts[$pc]??0)+1;$childProgress[$pc]=($childProgress[$pc]??0)+calc($rr)['progress'];}}
$min=$rows?min(array_column($rows,'start_date')):date('Y-m-d');$max=$rows?max(array_column($rows,'end_date')):date('Y-m-d',strtotime('+42 days'));
$start=new DateTime($min);$start->modify('monday this week');$end=new DateTime($max);$end->modify('sunday this week');$total=max(1,(int)$start->diff($end)->format('%a')+1);
$weeks=[];$w=clone $start;while($w<=$end){$weeks[]=clone $w;$w->modify('+7 days');}
function gdate($d){return $d?date('d-m-Y',strtotime($d)):'—';}
require 'header.php';?>
<div class="page-title"><div><h1>Carta Gantt</h1><p>Vista semanal tipo Excel, alineada con el Plan de tareas.</p></div><a class="btn primary" href="task_form.php?level=PROCESO">＋ Nuevo proceso</a></div>
<form class="excel-toolbar"><select name="area"><option value="">Todas las áreas</option><?php foreach($areas as $v):?><option <?=$area===$v?'selected':''?>><?=h($v)?></option><?php endforeach;?></select><select name="level"><option value="">Procesos + etapas</option><option value="PROCESO" <?=$level==='PROCESO'?'selected':''?>>PROCESO</option><option value="ETAPA" <?=$level==='ETAPA'?'selected':''?>>ETAPA</option></select><button class="btn">Aplicar</button><a class="btn ghost" href="gantt.php">Limpiar</a><a class="btn ghost" href="tasks.php">Plan de tareas</a></form>
<div class="sheet-card"><div class="table-scroll"><table class="excel-table gantt-sheet"><thead><tr><th>ID</th><th>Etapa / Tarea</th><th>Responsable</th><th>Inicio</th><th>Término</th><th>% Avance</th><?php foreach($weeks as $wk):?><th class="week"><?=date('d-M', $wk->getTimestamp())?></th><?php endforeach;?></tr></thead><tbody>
<?php foreach($rows as $r):$c=calc($r);$prog=(($r['level']??'')==='PROCESO'&&!empty($childCounts[$r['code']]))?(int)round($childProgress[$r['code']]/$childCounts[$r['code']]):(int)$c['progress'];?>
<tr class="<?=$r['level']==='PROCESO'?'process-row':'stage-row'?>"><td class="mono"><b><?=h($r['code'])?></b></td><td class="gantt-name"><?=$r['level']==='ETAPA'?'<span class="tree">↳</span>':''?><?=h($r['task'])?></td><td><?=h($r['responsible']?:'—')?></td><td><?=gdate($r['start_date'])?></td><td><?=gdate($r['end_date'])?></td><td><b><?=$prog?>%</b></td>
<?php $rs=new DateTime($r['start_date']);$re=new DateTime($r['end_date']);foreach($weeks as $wk):$we=(clone $wk)->modify('+6 days');$active=($rs<=$we && $re>=$wk);?><td class="week-cell <?=$active?'active '.$c['light']:''?>"><?php if($active):?><span title="<?=h($r['task'])?> · <?=$prog?>%"></span><?php endif;?></td><?php endforeach;?></tr>
<?php endforeach;?></tbody></table></div></div>
<div class="gantt-legend"><span><i class="traffic VERDE"></i>Normal / terminada</span><span><i class="traffic AMARILLO"></i>Próxima</span><span><i class="traffic ROJO"></i>Urgente / atrasada</span></div>
<?php require 'footer.php';?>