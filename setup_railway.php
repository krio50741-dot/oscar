<?php
require_once __DIR__.'/config.php';
$enabled=(getenv('SETUP_ENABLED')?:'1')==='1';
if(!$enabled){header('Location:login.php');exit;}
$msg='';$err='';$connected=false;
try{$p=db();$p->query('SELECT 1');$connected=true;}catch(Throwable $e){$err=$e->getMessage();}
if($_SERVER['REQUEST_METHOD']==='POST' && $connected){
 try{
  verify_csrf();
  $sql=file_get_contents(__DIR__.'/database_railway.sql');
  $p->exec($sql);
  $name=trim($_POST['name']??'');$user=trim($_POST['username']??'');$email=trim($_POST['email']??'');$pass=$_POST['password']??'';
  if(!$name||!$user||!filter_var($email,FILTER_VALIDATE_EMAIL)||strlen($pass)<10)throw new RuntimeException('Revise los datos. La contraseña debe tener al menos 10 caracteres.');
  $st=$p->prepare("INSERT INTO users(name,username,email,department,password,role,active) VALUES(?,?,?,?,?,'admin',1)
  ON DUPLICATE KEY UPDATE name=VALUES(name),email=VALUES(email),department=VALUES(department),password=VALUES(password),role='admin',active=1");
  $st->execute([$name,$user,$email,'Departamento Administrativo',password_hash($pass,PASSWORD_DEFAULT)]);
  $msg='Instalación completada. Ingrese al sistema y luego cambie SETUP_ENABLED a 0 en Railway.';
 }catch(Throwable $e){$err=$e->getMessage();}
}
?><!doctype html><html lang="es"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Instalar Planificador Gantt DRALA</title><link rel="stylesheet" href="assets/style.css?v=6"></head><body class="login"><div class="login-card" style="max-width:680px"><img src="assets/aduanas-chile-transparente.png" alt="Aduanas" style="max-width:220px;max-height:100px;object-fit:contain"><h1>Planificador Gantt DRALA</h1><p>Instalación para Railway</p><?php if($err):?><div class="bad"><?=h($err)?></div><?php endif;?><?php if($msg):?><div class="ok"><?=h($msg)?></div><p><a class="login-button" style="display:block;text-align:center" href="login.php">Ingresar</a></p><?php elseif($connected):?><form method="post"><input type="hidden" name="csrf" value="<?=h(csrf())?>"><label>Nombre administrador<input name="name" value="Oscar Arias" required></label><label>Usuario<input name="username" value="admin" required></label><label>Correo<input type="email" name="email" required></label><label>Contraseña<input type="password" name="password" minlength="10" required></label><button class="login-button">Crear base y administrador</button></form><?php else:?><div class="bad">No existe conexión con MySQL. Agregue MySQL al proyecto Railway y vincule sus variables al servicio web.</div><?php endif;?></div></body></html>