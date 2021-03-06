<?php
session_start();
require_once('../bbdd/connect.php');
require_once('../func/constants.php');
require_once('../func/generic.php');
require_once('../func/seguretat.php');
$db->exec("set names utf8");

$page    = isset($_POST['page']) ? intval($_POST['page']) : 1;  
$rows    = isset($_POST['rows']) ? intval($_POST['rows']) : 20;  
$sort    = isset($_POST['sort']) ? strval($_POST['sort']) : '10';  
$order   = isset($_POST['order']) ? strval($_POST['order']) : 'desc';

$idprofessor  = isset($_SESSION['professor']) ? $_SESSION['professor'] : 0 ;
$data_inici   = isset($_REQUEST['data_inici']) ? substr($_REQUEST['data_inici'],6,4)."-".substr($_REQUEST['data_inici'],3,2)."-".substr($_REQUEST['data_inici'],0,2) : getCursActual($db)["data_inici"];
$data_fi      = isset($_REQUEST['data_fi'])    ? substr($_REQUEST['data_fi'],6,4)."-".substr($_REQUEST['data_fi'],3,2)."-".substr($_REQUEST['data_fi'],0,2)          : getCursActual($db)["data_fi"];

$offset = ($page-1)*$rows;
$result  = array();

$sql  = "SELECT COUNT(idccc_taula_principal) ";
$sql .= "FROM ccc_taula_principal ";
$sql .= "WHERE idprofessor=".$idprofessor." AND data BETWEEN '".$data_inici."' AND '".$data_fi."'";
  
$rs = $db->query($sql);
foreach($rs->fetchAll() as $row) {
    $result["total"] = $row[0]; 
}

$sql  = "SELECT tp.*,t.nom_falta,tm.ccc_nom,ca.Valor AS alumne, ";
$sql .= "CONCAT(SUBSTR(tp.data,9,2),'-',SUBSTR(tp.data,6,2),'-',SUBSTR(tp.data,1,4)) AS data_ccc, ";
$sql .= "CONCAT(SUBSTR(tp.data_inici_sancio,9,2),'-',SUBSTR(tp.data_inici_sancio,6,2),'-',SUBSTR(tp.data_inici_sancio,1,4)) AS data_inici_sancio_ccc, ";
$sql .= "CONCAT(SUBSTR(tp.data_fi_sancio,9,2),'-',SUBSTR(tp.data_fi_sancio,6,2),'-',SUBSTR(tp.data_fi_sancio,1,4)) AS data_fi_sancio_ccc, ";
$sql .= "CONCAT(LEFT(fh.hora_inici,5),'-',LEFT(fh.hora_fi,5)) AS hora,tm.ccc_nom AS mesura,tp.data_inici_sancio,tp.data_fi_sancio ";
$sql .= "FROM ccc_taula_principal tp ";
$sql .= "LEFT JOIN ccc_tipus           t ON t.idccc_tipus          = tp.id_falta ";
$sql .= "LEFT JOIN ccc_tipus_mesura    tm ON tm.idccc_tipus_mesura  = tp.id_tipus_sancio ";
$sql .= "LEFT JOIN franges_horaries   fh ON fh.idfranges_horaries  = tp.idfranges_horaries ";
$sql .= "INNER JOIN contacte_alumne    ca ON ca.id_alumne           = tp.idalumne ";
$sql .= "WHERE tp.idprofessor=".$idprofessor." AND ca.id_tipus_contacte=".TIPUS_nom_complet." AND tp.data BETWEEN '".$data_inici."' AND '".$data_fi."' ";
$sql .= " ORDER BY $sort $order LIMIT $offset,$rows";

$rs = $db->query($sql);

/*$fp = fopen("log.txt","a");
fwrite($fp, $sql . PHP_EOL);
fclose($fp);*/

$items = array();  
foreach($rs->fetchAll() as $row) {  
    array_push($items, $row);  
}  
$result["rows"] = $items;  
  
echo json_encode($result);

$rs->closeCursor();
?>
