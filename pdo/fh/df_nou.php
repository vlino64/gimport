<?php
session_start();
require_once('../bbdd/connect.php');
require_once('../func/constants.php');
require_once('../func/generic.php');
require_once('../func/seguretat.php');
$db->exec("set names utf8");

$idfranges_horaries = $_REQUEST['idfranges_horaries'];
$iddies_setmana     = $_REQUEST['iddies_setmana'];
$curs_escolar       = $_SESSION['curs_escolar'];

$sql = "insert into dies_franges (idfranges_horaries,iddies_setmana,idperiode_escolar) values ($idfranges_horaries,$iddies_setmana,$curs_escolar)";

$result = $db->query($sql);
if ($result){
	echo json_encode(array('success'=>true));
} else {
	echo json_encode(array('msg'=>'Algunos errores ocurrieron.'));
}

//mysql_close();
?>