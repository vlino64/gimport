<?php
session_start();
require_once('../bbdd/connect.php');
require_once('../func/constants.php');
require_once('../func/generic.php');
require_once('../func/seguretat.php');
$db->exec("set names utf8");

$idprofpermanencia = intval($_REQUEST['id']);
$id_dies_franges   = $_REQUEST['id_dies_franges'];
$idespais_centre   = $_REQUEST['idespais_centre'];

$sql = "UPDATE prof_permanencies SET id_dies_franges='$id_dies_franges',idespais_centre='$idespais_centre' WHERE idprofpermanencia=$idprofpermanencia";

$result = $db->query($sql);
if ($result){
	echo json_encode(array('success'=>true));
} else {
	echo json_encode(array('msg'=>$sql));
}

//mysql_close();
?>