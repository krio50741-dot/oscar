<?php
require 'app_core.php';$err='';
if($_SERVER['REQUEST_METHOD']==='POST'){
 $login=trim($_POST['username']??'');
 $st=db()->prepare('SELECT * FROM users WHERE (username=? OR email=?) AND active=1 LIMIT 1');
 $st->execute([$login,$login]);$u=$st->fetch();
 if($u&&password_verify($_POST['password']??'',$u['password'])){
  $_SESSION['uid']=$u['id'];$_SESSION['name']=$u['name'];$_SESSION['role']=$u['role'];
  audit('LOGIN','Ingreso al sistema');header('Location:index.php');exit;
 }
 $err='Usuario/correo o contraseña incorrectos.';
}
?><!doctype html><html lang="es"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<meta name="theme-color" content="#0b3f6b"><link rel="stylesheet" href="assets/style.css?v=592">
<title>Ingreso · Planificador Gantt DRALA</title></head>
<body class="login institutional-login">
<div class="login-shell">
 <section class="login-hero">
   <div class="login-hero-inner">
     <img class="login-official-logo" src="assets/aduanas-chile-transparente.png" alt="Aduanas Chile">
     <div class="hero-rule"></div>
     <span class="login-kicker">DIRECCIÓN REGIONAL ADUANA LOS ANDES</span>
     <h1>Planificador<br><strong>Gantt DRALA</strong></h1>
     <p>Plataforma de planificación, seguimiento y control de procesos, tareas, plazos y compromisos.</p>
     <div class="hero-features">
       <div><span>✓</span><b>Control de plazos</b><small>Alertas y seguimiento preventivo</small></div>
       <div><span>✓</span><b>Trazabilidad</b><small>Registro de modificaciones y responsables</small></div>
       <div><span>✓</span><b>Gestión visual</b><small>Dashboard y Carta Gantt dinámica</small></div>
     </div>
   </div>
   <div class="login-hero-footer">Servicio Nacional de Aduanas · Chile</div>
 </section>
 <section class="login-access">
  <form method="post" class="login-card login-card-v590">
    <div class="mobile-login-logo"><img src="assets/aduanas-chile-transparente.png" alt="Aduanas Chile"></div>
    <span class="access-eyebrow">ACCESO SEGURO</span>
    <h2>Bienvenido</h2>
    <p class="access-copy">Ingrese sus credenciales para acceder al sistema de gestión.</p>
    <?php if($err):?><div class="bad"><?=h($err)?></div><?php endif;?>
    <label>Usuario o correo electrónico
      <div class="login-input-wrap"><span>●</span><input name="username" required autofocus autocomplete="username" placeholder="Ingrese usuario o correo"></div>
    </label>
    <label>Contraseña
      <div class="login-input-wrap"><span>◆</span><input type="password" name="password" required autocomplete="current-password" placeholder="Ingrese su contraseña"></div>
    </label>
    <div class="login-options"><span>Acceso autorizado</span><a href="forgot_password.php">¿Olvidó su contraseña?</a></div>
    <button class="login-button">Ingresar al sistema <span>→</span></button>
    <div class="login-security"><span>▣</span><p><b>Acceso protegido</b><small>Las acciones realizadas dentro del sistema quedan sujetas a trazabilidad.</small></p></div>
  </form>
 </section>
</div>
</body></html>