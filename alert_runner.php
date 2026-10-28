<?php require 'app_core.php';
$token=$_GET['token']??'';$expected=getenv('GANTT_ALERT_TOKEN')?:'';
if(!logged() && (!$expected || !hash_equals($expected,$token))){http_response_code(403);die('No autorizado');}
if(logged()&&!admin()){http_response_code(403);die('Solo administrador');}
$n=run_due_alerts();header('Content-Type: text/plain; charset=UTF-8');echo "Alertas enviadas: $n\n";
