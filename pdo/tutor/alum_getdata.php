<?php
session_start();
require_once('../bbdd/connect.php');
require_once('../func/constants.php');
require_once('../func/generic.php');
require_once('../func/seguretat.php');
$db->exec("set names utf8");

$idgrups = $_REQUEST['idgrups'];

$sql  = "SELECT CONCAT('0') AS idalumnes,CONCAT('  Tots els alumnes ...') AS Valor UNION ";
$sql .= "(SELECT DISTINCT(agm.idalumnes),ca.Valor ";
$sql .= "FROM alumnes_grup_materia agm ";
$sql .= "INNER JOIN alumnes            a ON agm.idalumnes          = a.idalumnes ";
$sql .= "INNER JOIN contacte_alumne ca ON agm.idalumnes=ca.id_alumne ";
$sql .= "INNER JOIN grups_materies    gm ON agm.idgrups_materies  = gm.idgrups_materies ";	 
$sql .= "INNER JOIN grups             g ON gm.id_grups            = g.idgrups ";
//$sql .= "INNER JOIN materia           m ON gm.id_mat_uf_pla       = m.idmateria ";
$sql .= "WHERE a.activat='S' AND g.idgrups=".$idgrups." AND ca.id_tipus_contacte=".TIPUS_nom_complet;	
$sql .= ") ";

/*$sql .= "UNION ";

$sql .= "SELECT CONCAT('0') AS idalumnes,CONCAT('  Tots els alumnes ...') AS Valor UNION ";
$sql .= "(SELECT DISTINCT(agm.idalumnes),ca.Valor ";
$sql .= "FROM alumnes_grup_materia agm ";
$sql .= "INNER JOIN contacte_alumne ca ON agm.idalumnes=ca.id_alumne ";
$sql .= "INNER JOIN grups_materies    gm ON agm.idgrups_materies  = gm.idgrups_materies ";	 
$sql .= "INNER JOIN grups             g ON gm.id_grups            = g.idgrups ";
$sql .= "INNER JOIN unitats_formatives uf ON gm.id_mat_uf_pla     = uf.idunitats_formatives ";
$sql .= "INNER JOIN moduls_ufs         mu ON gm.id_mat_uf_pla     = mu.id_ufs ";
$sql .= "INNER JOIN moduls              m ON mu.id_moduls         = m.idmoduls ";
$sql .= "WHERE g.idgrups=".$idgrups." AND ca.id_tipus_contacte=".TIPUS_nom_complet;	
$sql .= ") ";*/

$sql .= " ORDER BY 2  ";


$rs = $db->query($sql);

$result = array();
foreach($rs->fetchAll() as $row) {
	array_push($result, $row);
}
echo json_encode($result);

$rs->closeCursor();
//mysql_close();
?>


