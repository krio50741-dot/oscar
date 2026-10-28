<?php
require 'header.php';
if(!admin()){http_response_code(403);die('Solo administrador');}
$p=db();$msg='';$err='';

function catalog_label($type){
 $map=['status'=>'Estado','priority'=>'Prioridad','area'=>'Área / Unidad','risk'=>'Riesgo','milestone'=>'Hito de compra pública','department'=>'Departamento / Unidad','responsible'=>'Responsable'];
 return $map[$type]??ucwords(str_replace(['_','-'],' ',$type));
}
function catalog_key($name){
 $name=trim(mb_strtolower($name,'UTF-8'));
 $name=strtr($name,['á'=>'a','é'=>'e','í'=>'i','ó'=>'o','ú'=>'u','ü'=>'u','ñ'=>'n']);
 return trim(preg_replace('/[^a-z0-9]+/','_',$name),'_');
}
function user_snapshot($u){
 return 'Nombre: '.($u['name']??'').' | Usuario: '.($u['username']??'').' | Correo: '.($u['email']??'').' | Departamento: '.($u['department']??'').' | Rol: '.($u['role']??'').' | Estado: '.(((int)($u['active']??1))?'Activo':'Inactivo');
}
if($_SERVER['REQUEST_METHOD']==='POST'){
 verify_csrf();$act=$_POST['act']??'';
 try{
  if($act==='user'){
   $name=trim($_POST['name']);$username=trim($_POST['username']);$email=trim($_POST['email']);$dept=trim($_POST['department']);
   $role=in_array($_POST['role']??'',['admin','user'],true)?$_POST['role']:'user';
   $temp=bin2hex(random_bytes(12));
   $p->prepare('INSERT INTO users(name,username,email,department,password,role) VALUES(?,?,?,?,?,?)')->execute([$name,$username,$email,$dept,password_hash($temp,PASSWORD_DEFAULT),$role]);
   $uid=(int)$p->lastInsertId();
   audit('CREAR_USUARIO','Usuario creado · ID '.$uid.' · Nombre: '.$name.' · Usuario: '.$username.' · Correo: '.$email.' · Departamento: '.$dept.' · Rol: '.($role==='admin'?'Administrador':'Usuario'));
   $u=['id'=>$uid,'name'=>$name,'email'=>$email];$mail=send_reset_email($u);
   $msg='Usuario creado.'.($mail?' Se envió un enlace privado para definir su contraseña.':' Revise la configuración de correo para enviar el acceso.');
  }
  elseif($act==='user_edit'){
   $uid=(int)$_POST['uid'];$st=$p->prepare('SELECT * FROM users WHERE id=?');$st->execute([$uid]);$before=$st->fetch();
   if(!$before)throw new RuntimeException('Usuario no encontrado.');
   $role=in_array($_POST['role']??'',['admin','user'],true)?$_POST['role']:'user';
   $after=['name'=>trim($_POST['name']),'username'=>trim($_POST['username']),'email'=>trim($_POST['email']),'department'=>trim($_POST['department']),'role'=>$role,'active'=>$before['active']];
   $changes=[];
   $labels=['name'=>'Nombre','username'=>'Usuario','email'=>'Correo','department'=>'Departamento','role'=>'Rol'];
   foreach($labels as $k=>$lab){if((string)$before[$k] !== (string)$after[$k])$changes[]=$lab.': «'.$before[$k].'» → «'.$after[$k].'»';}
   $p->prepare('UPDATE users SET name=?,username=?,email=?,department=?,role=? WHERE id=?')->execute([$after['name'],$after['username'],$after['email'],$after['department'],$after['role'],$uid]);
   audit('EDITAR_USUARIO','ID '.$uid.' · '.($changes?implode(' | ',$changes):'Guardar sin cambios de datos'));
   $msg=$changes?'Usuario actualizado y modificación registrada.':'No había cambios en los datos del usuario.';
  }
  elseif($act==='reset'){
   $uid=(int)$_POST['uid'];$st=$p->prepare('SELECT id,name,email,username FROM users WHERE id=?');$st->execute([$uid]);$u=$st->fetch();
   $sent=$u&&send_reset_email($u);
   audit('RECUPERACION_CLAVE','Solicitud para '.$u['name'].' ('.$u['username'].') · correo destino: '.$u['email'].' · '.($sent?'enlace enviado':'envío no completado'));
   $msg=$sent?'Enlace de recuperación enviado únicamente al correo del usuario.':'No se pudo enviar. Revise el correo y la configuración de notificaciones.';
  }
  elseif($act==='toggle'&&(int)$_POST['uid']!==current_user_id()){
   $uid=(int)$_POST['uid'];$st=$p->prepare('SELECT id,name,username,active FROM users WHERE id=?');$st->execute([$uid]);$u=$st->fetch();
   if(!$u)throw new RuntimeException('Usuario no encontrado.');
   $new=(int)$u['active']?0:1;$p->prepare('UPDATE users SET active=? WHERE id=?')->execute([$new,$uid]);
   audit('ESTADO_USUARIO',$u['name'].' ('.$u['username'].') · '.($u['active']?'Activo → Inactivo':'Inactivo → Activo'));
   $msg='Estado del usuario actualizado y registrado.';
  }
  elseif($act==='catalog_add'){
   $type=trim($_POST['type']??'');if($type==='__custom__')$type=catalog_key($_POST['custom_type']??'');
   $value=trim($_POST['value']??'');$order=(int)($_POST['sort_order']??0);
   if($type===''||$value==='')throw new RuntimeException('Debe indicar la lista y la opción.');
   $p->prepare('INSERT INTO catalogs(type,value,sort_order) VALUES(?,?,?)')->execute([$type,$value,$order]);
   audit('CATALOGO_AGREGAR',catalog_label($type).' · opción «'.$value.'» · orden '.$order);
   $msg='Opción agregada correctamente.';
  }
  elseif($act==='catalog_edit'){
   $cid=(int)$_POST['cid'];$st=$p->prepare('SELECT * FROM catalogs WHERE id=?');$st->execute([$cid]);$before=$st->fetch();
   if(!$before)throw new RuntimeException('Opción no encontrada.');
   $value=trim($_POST['value']);$order=(int)$_POST['sort_order'];
   $p->prepare('UPDATE catalogs SET value=?,sort_order=? WHERE id=?')->execute([$value,$order,$cid]);
   audit('CATALOGO_EDITAR',catalog_label($before['type']).' · «'.$before['value'].'» (orden '.$before['sort_order'].') → «'.$value.'» (orden '.$order.')');
   $msg='Opción actualizada y registrada.';
  }
  elseif($act==='catalog_delete'){
   $cid=(int)$_POST['cid'];$st=$p->prepare('SELECT * FROM catalogs WHERE id=?');$st->execute([$cid]);$before=$st->fetch();
   if($before){$p->prepare('DELETE FROM catalogs WHERE id=?')->execute([$cid]);audit('CATALOGO_ELIMINAR',catalog_label($before['type']).' · opción eliminada «'.$before['value'].'» · orden '.$before['sort_order']);}
   $msg='Opción eliminada y registrada.';
  }
  elseif($act==='alerts'){$n=run_due_alerts();audit('EJECUTAR_ALERTAS','Ejecución manual · correos enviados: '.$n);$msg='Proceso de alertas ejecutado. Correos enviados: '.$n;}
 }catch(Throwable $e){$err='No fue posible completar la operación: '.$e->getMessage();}
}
$users=$p->query('SELECT id,name,username,email,department,role,active,created_at FROM users ORDER BY name')->fetchAll();
$cats=$p->query('SELECT id,type,value,sort_order FROM catalogs ORDER BY type,sort_order,value')->fetchAll();
$catalogTypes=[];foreach($cats as $cc)$catalogTypes[$cc['type']]=catalog_label($cc['type']);
foreach(['status'=>'Estado','priority'=>'Prioridad','area'=>'Área / Unidad','risk'=>'Riesgo','milestone'=>'Hito de compra pública','department'=>'Departamento / Unidad','responsible'=>'Responsable'] as $k=>$v)$catalogTypes[$k]=$v;
asort($catalogTypes);
?>
<div class="page-title"><div><span class="eyebrow">SISTEMA</span><h1>Administración</h1><p>Usuarios, permisos, listas desplegables y seguridad.</p></div><div class="page-actions"><a class="btn ghost" href="audit.php">↺ Historial de cambios</a><form method="post" class="inline"><input type="hidden" name="csrf" value="<?=h(csrf())?>"><input type="hidden" name="act" value="alerts"><button class="btn">Enviar alertas ahora</button></form></div></div>
<?php if($msg):?><div class="ok"><?=h($msg)?></div><?php endif;?><?php if($err):?><div class="bad"><?=h($err)?></div><?php endif;?>

<details class="admin-guide">
<summary><span class="guide-q">?</span><div><b>Ayuda para completar la Administración</b><small>Usuarios, listas y orden de aparición</small></div><span class="guide-action">Abrir / cerrar</span></summary>
<div class="guide-body">
<div class="guide-grid">
<div><b>Usuario</b><p>Puede ingresar nuevas tareas y gestionar los plazos permitidos según su perfil.</p></div>
<div><b>Administrador</b><p>Puede administrar usuarios, permisos, listas, datos sensibles y trazabilidad.</p></div>
<div><b>Lista desplegable</b><p>Conjunto de opciones que aparece en los formularios: Estado, Prioridad, Área, Riesgo, etc.</p></div>
<div><b>Orden</b><p>Menor número aparece primero. Ejemplo: -10, 0, 10, 20. Si no necesita un orden especial, deje 0.</p></div>
</div>
<div class="help-tip"><b>Sugerencia:</b> use 10, 20, 30… para dejar espacio e insertar nuevas opciones entre ellas posteriormente.</div>
</div>
</details>

<div class="grid2">
<section class="panel admin-card"><div class="panel-head"><div><h2>Nuevo usuario / administrador</h2><small>El rol define los permisos dentro del sistema.</small></div></div>
<form method="post" class="stack form-pad"><input type="hidden" name="csrf" value="<?=h(csrf())?>"><input type="hidden" name="act" value="user">
<label>Nombre completo <span class="field-help">Nombre y apellidos del funcionario.</span><input name="name" required></label>
<label>Correo electrónico <span class="field-help">Recuperación de acceso y notificaciones.</span><input type="email" name="email" required></label>
<label>Departamento / Unidad <span class="field-help">Ej.: Bienes y Servicios, Finanzas, RR.HH.</span><input name="department" required></label>
<label>Nombre de usuario <span class="field-help">Identificador para iniciar sesión.</span><input name="username" required></label>
<label>Rol <select name="role"><option value="user">Usuario</option><option value="admin">Administrador</option></select></label>
<button class="btn primary">Crear usuario y enviar acceso</button><small>La contraseña no se envía en texto plano. La persona recibe un enlace privado para crear su clave.</small>
</form></section>

<section class="panel admin-card"><div class="panel-head"><div><h2>Agregar opción a una lista</h2><small>Amplíe los desplegables sin modificar el código.</small></div></div>
<form method="post" class="stack form-pad"><input type="hidden" name="csrf" value="<?=h(csrf())?>"><input type="hidden" name="act" value="catalog_add">
<label>Lista <select name="type" id="catalogType" onchange="document.getElementById('customListWrap').style.display=this.value==='__custom__'?'flex':'none'"><?php foreach($catalogTypes as $key=>$label):?><option value="<?=h($key)?>"><?=h($label)?></option><?php endforeach;?><option value="__custom__">＋ Crear nueva lista…</option></select></label>
<label id="customListWrap" style="display:none">Nombre de la nueva lista<input name="custom_type" placeholder="Ej.: Tipo de documento"></label>
<label>Nueva opción<input name="value" required placeholder="Ej.: Complejo Los Libertadores"></label>
<label>Orden de aparición <span class="field-help">Menor número = aparece antes.</span><input type="number" name="sort_order" value="0"></label>
<button class="btn primary">Agregar opción</button></form></section>
</div>

<section class="panel"><div class="panel-head"><div><h2>Usuarios y permisos</h2><small>Cualquier cambio queda registrado con autor, fecha y detalle.</small></div><a href="audit.php?category=usuarios">Ver historial de usuarios →</a></div>
<div class="table-scroll"><table class="excel-table"><thead><tr><th>Nombre</th><th>Usuario</th><th>Correo</th><th>Departamento</th><th>Rol</th><th>Estado</th><th>Acciones</th></tr></thead><tbody>
<?php foreach($users as $u):?><tr><form method="post"><input type="hidden" name="csrf" value="<?=h(csrf())?>"><input type="hidden" name="act" value="user_edit"><input type="hidden" name="uid" value="<?=$u['id']?>"><td><input name="name" value="<?=h($u['name'])?>"></td><td><input name="username" value="<?=h($u['username'])?>"></td><td><input type="email" name="email" value="<?=h($u['email']??'')?>"></td><td><input name="department" value="<?=h($u['department']??'')?>"></td><td><select name="role"><option value="user" <?=$u['role']==='user'?'selected':''?>>Usuario</option><option value="admin" <?=$u['role']==='admin'?'selected':''?>>Administrador</option></select></td><td><span class="state-pill <?=$u['active']?'on':'off'?>"><?=$u['active']?'ACTIVO':'INACTIVO'?></span></td><td><button class="btn">Guardar</button></form><form method="post" class="inline"><input type="hidden" name="csrf" value="<?=h(csrf())?>"><input type="hidden" name="act" value="reset"><input type="hidden" name="uid" value="<?=$u['id']?>"><button class="btn ghost">Recuperación</button></form><?php if((int)$u['id']!==current_user_id()):?><form method="post" class="inline"><input type="hidden" name="csrf" value="<?=h(csrf())?>"><input type="hidden" name="act" value="toggle"><input type="hidden" name="uid" value="<?=$u['id']?>"><button class="btn ghost"><?=$u['active']?'Desactivar':'Activar'?></button></form><?php endif;?></td></tr><?php endforeach;?>
</tbody></table></div></section>

<section class="panel"><div class="panel-head"><div><h2>Listas desplegables editables</h2><small>Cambie texto y orden; todas las modificaciones quedan en el historial.</small></div><a href="audit.php?category=listas">Ver historial de listas →</a></div>
<div class="table-scroll"><table class="excel-table"><thead><tr><th>Lista</th><th>Opción visible</th><th>Orden</th><th>Acciones</th></tr></thead><tbody>
<?php foreach($cats as $c):?><tr><form method="post"><input type="hidden" name="csrf" value="<?=h(csrf())?>"><input type="hidden" name="act" value="catalog_edit"><input type="hidden" name="cid" value="<?=$c['id']?>"><td><b><?=h(catalog_label($c['type']))?></b><small class="block-muted"><?=h($c['type'])?></small></td><td><input name="value" value="<?=h($c['value'])?>"></td><td><input type="number" name="sort_order" value="<?=$c['sort_order']?>" style="width:75px"></td><td><button class="btn">Guardar</button></form><form method="post" class="inline" onsubmit="return confirm('¿Eliminar esta opción?')"><input type="hidden" name="csrf" value="<?=h(csrf())?>"><input type="hidden" name="act" value="catalog_delete"><input type="hidden" name="cid" value="<?=$c['id']?>"><button class="btn ghost">Eliminar</button></form></td></tr><?php endforeach;?>
</tbody></table></div></section>
<?php require 'footer.php';?>