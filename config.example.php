<?php
// CONFIGURACIÓN DE BASE DE DATOS - COPIAR COMO config.php EN EL NUEVO SERVIDOR
// No incluya contraseñas reales en copias compartidas.
session_start();
date_default_timezone_set('America/Santiago');

const DB_HOST = 'SERVIDOR_MYSQL';
const DB_PORT = '3306';
const DB_NAME = 'NOMBRE_BASE_DATOS';
const DB_USER = 'USUARIO_BASE_DATOS';
const DB_PASS = 'CONTRASENA_BASE_DATOS';

function db(): PDO {
    static $pdo = null;
    if ($pdo === null) {
        $dsn = 'mysql:host='.DB_HOST.';port='.DB_PORT.';dbname='.DB_NAME.';charset=utf8mb4';
        $pdo = new PDO($dsn, DB_USER, DB_PASS, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]);
    }
    return $pdo;
}
function h($v): string { return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }
function require_login(): void { if(empty($_SESSION['uid'])){header('Location:login.php');exit;} }
function admin(): bool { return ($_SESSION['role'] ?? '') === 'admin'; }
function csrf(): string { if(empty($_SESSION['csrf'])) $_SESSION['csrf']=bin2hex(random_bytes(24)); return $_SESSION['csrf']; }
function verify_csrf(): void { if(!hash_equals($_SESSION['csrf']??'', $_POST['csrf']??'')) die('Solicitud inválida.'); }
function audit(string $action,string $detail=''): void {
    try { db()->prepare('INSERT INTO audit(user_id,action,detail) VALUES(?,?,?)')->execute([$_SESSION['uid']??null,$action,$detail]); } catch(Throwable $e) {}
}
