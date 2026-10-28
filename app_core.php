<?php
require_once __DIR__.'/config.php';

function is_admin(): bool { return admin(); }
function current_user_id(): int { return (int)($_SESSION['uid'] ?? 0); }
function base_url(): string {
    $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $dir = rtrim(str_replace('\\','/',dirname($_SERVER['SCRIPT_NAME'] ?? '/')), '/');
    return $scheme.'://'.$host.($dir ? $dir : '');
}
function catalog_values(string $type): array {
    $st=db()->prepare('SELECT value FROM catalogs WHERE type=? ORDER BY sort_order,value');
    try {$st->execute([$type]);} catch(Throwable $e) {$st=db()->prepare('SELECT value FROM catalogs WHERE type=? ORDER BY value');$st->execute([$type]);}
    return $st->fetchAll(PDO::FETCH_COLUMN);
}
function user_can_edit_task(array $task): bool {
    return admin() || ((int)($task['created_by']??0) === current_user_id());
}
function user_can_edit_sensitive_task_fields(): bool { return admin(); }
function task_original_end(array $t): ?string { return $t['original_end_date'] ?? $t['end_date'] ?? null; }
function extension_info(array $t): array {
    $orig = task_original_end($t); $cur = $t['end_date'] ?? null; $days=0; $extended=false;
    if($orig && $cur && $cur > $orig){$days=(int)((new DateTime($orig))->diff(new DateTime($cur))->format('%a'));$extended=true;}
    $today=date('Y-m-d'); $wasOverdue = $orig && $orig < $today && !in_array($t['status']??'', ['Terminada','Completada'],true);
    return compact('orig','cur','days','extended','wasOverdue');
}
function mail_config(): array {
    $f=__DIR__.'/mail_config.php';
    if(file_exists($f)){ $cfg=require $f; if(is_array($cfg)) return $cfg; }
    return ['enabled'=>false,'from_email'=>'','from_name'=>'Planificador Gantt DRALA'];
}
function send_app_mail(string $to,string $subject,string $html): bool {
    $cfg=mail_config(); if(empty($cfg['enabled']) || !filter_var($to,FILTER_VALIDATE_EMAIL)) return false;
    $from=$cfg['from_email']??''; $name=$cfg['from_name']??'Planificador Gantt DRALA';
    $headers="MIME-Version: 1.0\r\nContent-type: text/html; charset=UTF-8\r\n";
    if($from) $headers.='From: '.$name.' <'.$from.">\r\n";
    return @mail($to,'=?UTF-8?B?'.base64_encode($subject).'?=',$html,$headers);
}
function active_admin_emails(): array {
    $st=db()->query("SELECT email FROM users WHERE role='admin' AND active=1 AND email IS NOT NULL AND email<>''");
    return array_values(array_unique(array_filter($st->fetchAll(PDO::FETCH_COLUMN))));
}
function task_creator(array $task): ?array {
    if(empty($task['created_by'])) return null;
    $st=db()->prepare('SELECT id,name,email,department,role FROM users WHERE id=?');$st->execute([(int)$task['created_by']]);return $st->fetch()?:null;
}
function notify_task_deadline(array $task,string $kind,string $label): int {
    $creator=task_creator($task);$emails=active_admin_emails(); if($creator && !empty($creator['email']))$emails[]=$creator['email'];$emails=array_values(array_unique($emails));
    $sent=0; foreach($emails as $email){
        $key=$task['id'].'|'.$kind.'|'.$email.'|'.date('Y-m-d');
        $chk=db()->prepare('SELECT COUNT(*) FROM alert_log WHERE alert_key=?');$chk->execute([$key]); if((int)$chk->fetchColumn())continue;
        $subject='Alerta de plazo · '.$task['code'].' · '.$label;
        $body='<h2>Planificador Gantt DRALA</h2><p><b>'.h($label).'</b></p><p>Tarea: <b>'.h($task['task']).'</b><br>ID: '.h($task['code']).'<br>Fecha término: '.h($task['end_date']).'</p><p>Revise el sistema para gestionar el plazo.</p>';
        if(send_app_mail($email,$subject,$body)){$ins=db()->prepare('INSERT INTO alert_log(alert_key,task_id,email,alert_type) VALUES(?,?,?,?)');$ins->execute([$key,$task['id'],$email,$kind]);$sent++;}
    } return $sent;
}
function run_due_alerts(): int {
    $rows=db()->query("SELECT * FROM tasks WHERE end_date IS NOT NULL AND status NOT IN ('Terminada','Completada','Cancelada')")->fetchAll();$sent=0;$today=new DateTime('today');
    foreach($rows as $t){$end=new DateTime($t['end_date']);$d=(int)$today->diff($end)->format('%r%a');
        if($d<0)$sent+=notify_task_deadline($t,'overdue','Actividad fuera de plazo');
        elseif($d===0)$sent+=notify_task_deadline($t,'today','Actividad vence hoy');
        elseif($d<=3)$sent+=notify_task_deadline($t,'3days','Actividad próxima a vencer ('.$d.' días)');
        elseif($d<=7)$sent+=notify_task_deadline($t,'7days','Actividad próxima a vencer ('.$d.' días)');
    } return $sent;
}
function create_reset_token(int $uid,int $hours=2): string {
    $raw=bin2hex(random_bytes(32));$hash=hash('sha256',$raw);$exp=(new DateTime('+'.$hours.' hours'))->format('Y-m-d H:i:s');
    db()->prepare('INSERT INTO password_resets(user_id,token_hash,expires_at) VALUES(?,?,?)')->execute([$uid,$hash,$exp]);return $raw;
}
function send_reset_email(array $u): bool {
    if(empty($u['email'])) return false;$token=create_reset_token((int)$u['id']);$url=base_url().'/reset_password.php?token='.urlencode($token);
    $body='<h2>Planificador Gantt DRALA</h2><p>Hola '.h($u['name']).',</p><p>Se solicitó crear o modificar su contraseña.</p><p><a href="'.h($url).'">Definir nueva contraseña</a></p><p>El enlace vence en 2 horas. Si usted no solicitó este cambio, ignore este mensaje.</p>';
    return send_app_mail($u['email'],'Acceso / recuperación de contraseña', $body);
}
