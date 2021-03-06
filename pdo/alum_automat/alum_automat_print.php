<?php 
session_start();
require_once('../bbdd/connect.php');
require_once('../func/constants.php');
require_once('../func/generic.php');
require_once('../func/seguretat.php');

# Cargamos la librería dompdf.
require_once('../dompdf/autoload.inc.php');
 
# Contenido HTML del documento que queremos generar en PDF.

$mode_impresio      = 1;
$data_inici         = $_REQUEST['data_inici'];
$data_fi            = $_REQUEST['data_fi'];
$c_alumne           = $_REQUEST['c_alumne'];

$box_dg             = isset($_REQUEST['box_dg'])             ? $_REQUEST['box_dg']             : '';
$box_faltes         = isset($_REQUEST['box_faltes'])         ? $_REQUEST['box_faltes']         : '';
$box_retards        = isset($_REQUEST['box_retards'])        ? $_REQUEST['box_retards']        : '';
$box_justificacions = isset($_REQUEST['box_justificacions']) ? $_REQUEST['box_justificacions'] : '';
$box_incidencies    = isset($_REQUEST['box_incidencies'])    ? $_REQUEST['box_incidencies']    : '';
$box_CCC            = isset($_REQUEST['box_CCC'])            ? $_REQUEST['box_CCC']            : '';

$fitxer_sortida  = "http://".$_SERVER['SERVER_NAME'].substr($_SERVER['PHP_SELF'],0,strlen($_SERVER['PHP_SELF'])-22)."alum_automat_see.php?hr=1&data_inici=";
$fitxer_sortida .= $data_inici."&data_fi=".$data_fi."&mode_impresio=".$mode_impresio;
$fitxer_sortida .= "&c_alumne=".$c_alumne."&box_dg=".$box_dg."&box_faltes=".$box_faltes."&box_retards=".$box_retards;
$fitxer_sortida .= "&box_justificacions=".$box_justificacions."&box_incidencies=".$box_incidencies."&box_CCC=".$box_CCC;

$html = file_get_contents($fitxer_sortida);

// reference the Dompdf namespace
use Dompdf\Dompdf;

// instantiate and use the dompdf class
$dompdf = new Dompdf();
$dompdf->loadHtml($html);

// (Optional) Setup the paper size and orientation
$dompdf->setPaper('A4', 'landscape');

// Render the HTML as PDF
$dompdf->render();

// Output the generated PDF to Browser
$dompdf->stream('Informe.pdf');
?>
