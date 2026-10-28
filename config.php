<?php
declare(strict_types=1);
if(session_status()!==PHP_SESSION_ACTIVE) session_start();
date_default_timezone_set(getenv('APP_TIMEZONE') ?: 'America/Santiago');

function envv(string $k,string $default=''): string {
    $v=getenv($k);
    return ($v===false || $v==='') ? $default : (string)$v;
}
function db(): PDO {
    static $pdo=null;
    if($pdo===null){
        $host=envv('MYSQLHOST',envv('DB_HOST','127.0.0.1'));
        $port=envv('MYSQLPORT',envv('DB_PORT','3306'));
        $name=envv('MYSQLDATABASE',envv('DB_NAME','railway'));
        $user=envv('MYSQLUSER',envv('DB_USER','root'));
        $pass=envv('MYSQLPASSWORD',envv('DB_PASS',''));
        $dsn="mysql:host={$host};port={$port};dbname={$name};charset=utf8mb4";
        $pdo=new PDO($dsn,$user,$pass,[
            PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE=>PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES=>false,
        ]);
    }
    return $pdo;
}
function h($v): string { return htmlspecialchars((string)$v,ENT_QUOTES,'UTF-8'); }
function require_login(): void { if(empty($_SESSION['uid'])){header('Location:login.php');exit;} }
function admin(): bool { return ($_SESSION['role']??'')==='admin'; }
function csrf(): string { if(empty($_SESSION['csrf']))$_SESSION['csrf']=bin2hex(random_bytes(24));return $_SESSION['csrf']; }
function verify_csrf(): void { if(!hash_equals($_SESSION['csrf']??'',$_POST['csrf']??'')){http_response_code(419);die('Solicitud inválida.');} }
function audit(string $action,string $detail=''): void {
    try{db()->prepare('INSERT INTO audit(user_id,action,detail) VALUES(?,?,?)')->execute([$_SESSION['uid']??null,$action,$detail]);}catch(Throwable $e){}
}
/* Compatibilidad con el Dashboard del paquete portable. */
function calc(array $r): array {
    $status=(string)($r['status']??'');
    $today=new DateTimeImmutable('today');
    $start=!empty($r['start_date'])?new DateTimeImmutable($r['start_date']):null;
    $end=!empty($r['end_date'])?new DateTimeImmutable($r['end_date']):null;
    if(in_array($status,['Terminada','Completada'],true)) $progress=100;
    elseif(!$start||!$end) $progress=0;
    elseif($today<$start) $progress=0;
    elseif($today>=$end) $progress=95;
    else {
        $total=max(1,(int)$start->diff($end)->format('%a'));
        $used=max(0,(int)$start->diff($today)->format('%a'));
        $progress=(int)max(0,min(95,round(($used/$total)*100)));
    }
    $remaining=$end?(int)$today->diff($end)->format('%r%a'):null;
    if(in_array($status,['Terminada','Completada'],true)) $light='VERDE';
    elseif($remaining!==null && $remaining<0) $light='ROJO';
    elseif($remaining!==null && $remaining<=7) $light='AMARILLO';
    else $light='VERDE';
    return ['progress'=>$progress,'remaining'=>$remaining,'light'=>$light];
}
