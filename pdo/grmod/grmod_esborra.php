<?php
session_start();
require_once('../bbdd/connect.php');
require_once('../func/constants.php');
require_once('../func/generic.php');
require_once('../func/seguretat.php');
$db->exec("set names utf8");

$id_grups             = isset($_REQUEST['id_grups']) ? $_REQUEST['id_grups'] : 0 ;
$idunitats_formatives = isset($_REQUEST['idunitats_formatives']) ? $_REQUEST['idunitats_formatives'] : 0 ;
$data_inici	      = isset($_REQUEST['data_inici']) ? $_REQUEST['data_inici'] : 0 ;
$data_fi	      = isset($_REQUEST['data_fi']) ? $_REQUEST['data_fi'] : 0 ;
$afegir               = isset($_REQUEST['afegir']) ? $_REQUEST['afegir'] : 0 ;

$pos = 0;
foreach ($idunitats_formatives as $id_uf) {
		$id_di = substr($data_inici[$pos],6,4)."-".substr($data_inici[$pos],3,2)."-".substr($data_inici[$pos],0,2);
		$id_df = substr($data_fi[$pos],6,4)."-".substr($data_fi[$pos],3,2)."-".substr($data_fi[$pos],0,2);
		
		if ($id_uf != 0) {
			//esborrar grup_materia
			$sql = "DELETE FROM grups_materies WHERE id_grups=$id_grups AND id_mat_uf_pla='$id_uf'";
			$result = $db->query($sql);
							
		}
		$pos++;
}

echo json_encode(array('success'=>true));
?>