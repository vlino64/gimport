<?php
session_start();
require_once('../bbdd/connect.php');
require_once('../func/constants.php');
require_once('../func/generic.php');
require_once('../func/seguretat.php');
$db->exec("set names utf8");

//$rs = $db->query('select * from plans_estudis where Acronim_pla_estudis in ("ESO","BAT")');
$rs = $db->query('select * from plans_estudis');
$result = array();
foreach($rs->fetchAll() as $row) {
	array_push($result, $row);
}
echo json_encode($result);

$rs->closeCursor();
//mysql_close();
?>