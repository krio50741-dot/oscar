<?php
require 'header.php';if(!admin()){http_response_code(403);die('Solo administrador');}
$p=db();$category=trim($_GET['category']??'');$actor=(int)($_GET['actor']??0);$from=trim($_GET['from']??'');$to=trim($_GET['to']??'');$q=trim($_GET['q']??'');
$where=['1=1'];$params=[];
if($actor){$where[]='a.user_id=?';$params[]=$actor;}
if($from!==''){$where[]='DATE(a.created_at)>=?';$params[]=$from;}
if($to!==''){$where[]='DATE(a.created_at)<=?';$params[]=$to;}
if($q!==''){$where[]='(a.action LIKE ? OR a.detail LIKE ? OR u.name LIKE ?)';$x='%'.$q.'%';array_push($params,$x,$x,$x);}
if($category==='usuarios')$where[]="a.action IN ('CREAR_USUARIO','EDITAR_USUARIO','ESTADO_USUARIO','RECUPERACION_CLAVE','RESET_CLAVE','CAMBIO_CLAVE')";
elseif($category==='listas')$where[]="a.action LIKE 'CATALOGO_%'";
elseif($category==='tareas')$where[]="a.action IN ('CREAR_TAREA','EDITAR_TAREA','CAMBIO_PLAZO')";
$sql='SELECT a.id,a.user_id,a.action,a.detail,a.created_at,u.name,u.username,u.role FROM audit a LEFT JOIN users u ON u.id=a.user_id WHERE '.implode(' AND ',$where).' ORDER BY a.id DESC LIMIT 500';
$st=$p->prepare($sql);$st->execute($params);$rows=$st->fetchAll();
$users=$p->query('SELECT id,name,username FROM users ORDER BY name')->fetchAll();
function action_label($a){$m=['CREAR_USUARIO'=>'Usuario creado','EDITAR_USUARIO'=>'Usuario modificado','ESTADO_USUARIO'=>'Estado de usuario','RECUPERACION_CLAVE'=>'Recuperación de contraseña','RESET_CLAVE'=>'Recuperación de contraseña','CAMBIO_CLAVE'=>'Cambio de contraseña','CATALOGO_AGREGAR'=>'Opción de lista agregada','CATALOGO_EDITAR'=>'Opción de lista modificada','CATALOGO_ELIMINAR'=>'Opción de lista eliminada','CREAR_TAREA'=>'Tarea creada','EDITAR_TAREA'=>'Tarea modificada','CAMBIO_PLAZO'=>'Plazo modificado','LOGIN'=>'Ingreso al sistema','EJECUTAR_ALERTAS'=>'Alertas ejecutadas','IMPORTAR_EXCEL'=>'Sincronización Excel','IMPORTAR_EXCEL_NUEVO'=>'Registro creado desde Excel','IMPORTAR_EXCEL_ACTUALIZAR'=>'Registro actualizado desde Excel'];return $m[$a]??str_replace('_',' ',$a);}
function action_group($a){if(strpos($a,'IMPORTAR_EXCEL')!==false)return 'excel';if(strpos($a,'USUARIO')!==false||strpos($a,'CLAVE')!==false||strpos($a,'RECUPERACION')!==false)return 'user';if(strpos($a,'CATALOGO')!==false)return 'list';if(strpos($a,'TAREA')!==false||strpos($a,'PLAZO')!==false)return 'task';return 'system';}
?>
<div class="page-title"><div><span class="eyebrow">TRAZABILIDAD</span><h1>Historial de cambios</h1><p>Registro independiente de quién realizó cada acción, cuándo la realizó y qué fue modificado.</p></div><div class="page-actions"><a class="btn ghost" href="admin.php">← Administración</a></div></div>
<div class="audit-summary"><div><b><?=count($rows)?></b><span>registros mostrados</span></div><div><b>500</b><span>máximo por consulta</span></div><div><b>Activo</b><span>registro de trazabilidad</span></div></div>
<form class="audit-filters" method="get">
<div><label>Tipo</label><select name="category"><option value="">Todos los cambios</option><option value="usuarios" <?=$category==='usuarios'?'selected':''?>>Usuarios y administradores</option><option value="tareas" <?=$category==='tareas'?'selected':''?>>Tareas y plazos</option><option value="listas" <?=$category==='listas'?'selected':''?>>Listas desplegables</option></select></div>
<div><label>Realizado por</label><select name="actor"><option value="0">Todos</option><?php foreach($users as $u):?><option value="<?=$u['id']?>" <?=$actor===$u['id']?'selected':''?>><?=h($u['name'].' · '.$u['username'])?></option><?php endforeach;?></select></div>
<div><label>Desde</label><input type="date" name="from" value="<?=h($from)?>"></div><div><label>Hasta</label><input type="date" name="to" value="<?=h($to)?>"></div>
<div class="audit-search"><label>Buscar</label><input name="q" value="<?=h($q)?>" placeholder="Usuario, tarea, acción..."></div><button class="btn primary">Aplicar</button><a class="btn ghost" href="audit.php">Limpiar</a>
</form>
<section class="panel audit-panel"><div class="panel-head"><div><h2>Modificaciones registradas</h2><small>Los registros más recientes aparecen primero.</small></div></div>
<div class="audit-table-wrap"><table class="audit-table"><thead><tr><th>Fecha y hora</th><th>Realizado por</th><th>Perfil</th><th>Acción</th><th>Detalle de la modificación</th></tr></thead><tbody>
<?php foreach($rows as $r):?><tr><td class="audit-date"><?=h(date('d-m-Y',strtotime($r['created_at'])))?><small><?=h(date('H:i:s',strtotime($r['created_at'])))?></small></td><td><b><?=h($r['name']??'Sistema')?></b><small><?=h($r['username']??'Automático')?></small></td><td><span class="role-pill"><?=h(($r['role']??'')==='admin'?'Administrador':(($r['role']??'')==='user'?'Usuario':'Sistema'))?></span></td><td><span class="action-pill <?=action_group($r['action'])?>"><?=h(action_label($r['action']))?></span><small class="block-muted"><?=h($r['action'])?></small></td><td class="audit-detail"><?=h($r['detail']?:'Sin detalle adicional')?></td></tr><?php endforeach;?>
<?php if(!$rows):?><tr><td colspan="5" class="empty">No hay registros que coincidan con los filtros seleccionados.</td></tr><?php endif;?></tbody></table></div></section>
<div class="soft-info"><span>i</span><div><b>Qué queda registrado</b><p>El historial identifica al usuario conectado que ejecutó la acción, la fecha y hora, el tipo de acción y su detalle. Los cambios de plazo de tareas conservan además las fechas anteriores y nuevas en el historial específico de la tarea.</p></div></div>
<?php require 'footer.php';?>