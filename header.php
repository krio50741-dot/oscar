<?php
require_once 'app_core.php';
require_login();
$current = basename($_SERVER['PHP_SELF'] ?? '');
function navactive($files){
    global $current;
    return in_array($current,(array)$files,true)?'active':'';
}
try{ run_due_alerts(); }catch(Throwable $e){}
?>
<!doctype html>
<html lang="es">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<meta name="theme-color" content="#0b3a63">
<title><?=h(APP_NAME)?></title>
<link rel="stylesheet" href="assets/style.css?v=600">
</head>
<body class="app-body">
<header class="topbar">
  <div class="brand institutional-brand">
    <div class="brand-logo-box"><img src="assets/aduanas-chile-transparente.png" alt="Aduanas Chile"></div>
    <div class="brand-divider"></div>
    <div class="brand-system"><b>Planificador Gantt DRALA</b><small>Dirección Regional Aduana Los Andes</small></div>
  </div>
  <div class="top-user">
    <div class="avatar"><?=h(strtoupper(substr($_SESSION['name']??'U',0,1)))?></div>
    <div class="user-copy"><b><?=h($_SESSION['name']??'Usuario')?></b><small><?=admin()?'Administrador':'Usuario'?></small></div>
    <a class="top-btn" href="profile.php">Mi cuenta</a>
    <a class="top-btn" href="logout.php">Salir</a>
  </div>
</header>
<div class="app-shell">
<aside class="sidebar">
  <div class="nav-label">GESTIÓN</div>
  <a class="nav-item <?=navactive(['index.php'])?>" href="index.php"><span>▦</span><b>Dashboard</b></a>
  <a class="nav-item <?=navactive(['tasks.php','task_form.php'])?>" href="tasks.php"><span>▤</span><b>Plan de tareas</b></a>
  <a class="nav-item <?=navactive(['gantt.php'])?>" href="gantt.php"><span>▥</span><b>Carta Gantt</b></a>

  <div class="nav-label">HERRAMIENTAS</div>
  <a class="nav-item <?=navactive(['tools.php','export.php'])?>" href="tools.php"><span>⇩</span><b>Informes y descargas</b></a>
  <a class="nav-item <?=navactive(['import_excel.php'])?>" href="import_excel.php"><span>⇄</span><b>Sincronizar Excel</b></a>
  <a class="nav-item <?=navactive(['help.php'])?>" href="help.php"><span>?</span><b>Ayuda y guía</b></a>

  <?php if(admin()):?>
  <div class="nav-label">SISTEMA</div>
  <a class="nav-item <?=navactive(['admin.php'])?>" href="admin.php"><span>⚙</span><b>Administración</b></a>
  <a class="nav-item <?=navactive(['audit.php'])?>" href="audit.php"><span>↺</span><b>Historial de cambios</b></a>
  <?php endif;?>

  <div class="sidebar-note"><b>Trazabilidad activa</b><span>Registra creación, cambios y ampliaciones de plazo.</span></div>
</aside>
<main class="main-content">