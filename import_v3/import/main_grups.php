<?php
/*---------------------------------------------------------------
* Aplicatiu: programa d'importació de dades a gassist
* Fitxer:prof-act.php
* Autor: Víctor Lino
* Descripció: Actualització o càrrega de professorat
* Pre condi.:
* Post cond.:
* 
----------------------------------------------------------------*/
include("../config.php");
include("../funcions/func_grups_materies.php");
include("../funcions/funcions_generals.php");
include("../funcions/funcionsCsv.php");

session_start();
//Check whether the session variable SESS_MEMBER is present or not
if((!isset($_SESSION['SESS_MEMBER'])) || ($_SESSION['SESS_MEMBER']!="access_ok")) 
	{
	header("location: ../login/access-denied.php");
	exit();
	}

//foreach($_FILES as $campo => $texto)
//eval("\$".$campo."='".$texto."';");

?>
<html>
<head>
<title>Càrrega automàtica SAGA</title>
<meta http-equiv="Content-Type" content="text/html; charset=utf8">
<LINK href="../estilos/oceanis/style.css" rel="stylesheet" type="text/css">
</head>

<body>
<?php

	$exportsagaxml=$_SESSION['upload_saga'];
	$exporthorarixml=$_SESSION['upload_horaris'];
	if (extreu_fase('app_horaris')==5)
      {relaciona_grups_torns_sol_saga($exportsagaxml);}
   else
      {relaciona_grups_torns($exportsagaxml,$exporthorarixml,$exporthorarixml2);	}
	
?>
</body>

	




