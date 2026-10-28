<?php
require 'app_core.php';require_login();
if(!admin()){http_response_code(403);die('Solo administradores.');}
$pdo=db();
header('Content-Type: application/sql; charset=utf-8');
header('Content-Disposition: attachment; filename="DRALA_backup_'.date('Y-m-d_H-i-s').'.sql"');
echo "-- Planificador Gantt DRALA V6.0\n-- Copia de datos generada: ".date('Y-m-d H:i:s')."\nSET NAMES utf8mb4;\nSET FOREIGN_KEY_CHECKS=0;\n\n";
$tables=$pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
foreach($tables as $table){
    $safe=str_replace('`','``',$table);
    $create=$pdo->query("SHOW CREATE TABLE `$safe`")->fetch(PDO::FETCH_NUM);
    echo "DROP TABLE IF EXISTS `$safe`;\n".$create[1].";\n\n";
    $rows=$pdo->query("SELECT * FROM `$safe`");
    while($r=$rows->fetch(PDO::FETCH_ASSOC)){
        $cols=array_map(fn($c)=>'`'.str_replace('`','``',$c).'`',array_keys($r));
        $vals=[];
        foreach($r as $v)$vals[]=$v===null?'NULL':$pdo->quote((string)$v);
        echo "INSERT INTO `$safe` (".implode(',',$cols).") VALUES (".implode(',',$vals).");\n";
    }
    echo "\n";
}
echo "SET FOREIGN_KEY_CHECKS=1;\n";
exit;
?>