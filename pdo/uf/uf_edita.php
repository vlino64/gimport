<?php
session_start();
require_once('../bbdd/connect.php');
require_once('../func/constants.php');
require_once('../func/generic.php');
require_once('../func/seguretat.php');
$db->exec("set names utf8");

$id     = intval($_REQUEST['idunitats_formatives']);
$nom_uf = str_replace("'","\'",$_REQUEST['nom_uf']);
$hores  = $_REQUEST['hores'];

$sql = "update unitats_formatives set nom_uf='$nom_uf',hores='$hores' where idunitats_formatives=$id";

$result = $db->query($sql);
if ($result){
	echo json_encode(array('success'=>true));
} else {
	echo json_encode(array('msg'=>'Algunos errores ocurrieron.'));
}

//mysql_close();
?>