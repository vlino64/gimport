<?php
/* ---------------------------------------------------------------
 * Aplicatiu: programa d'importació de dades a gassist
 * Fitxer:grups_act.php
 * Autor: Víctor Lino
 * Descripció: Carrega els grups del fitxer de saga
 * Pre condi.:
 * Post cond.:
 * 
  ---------------------------------------------------------------- */
require_once('../../pdo/bbdd/connect.php');
include("../funcions/funcions_generals.php");
include("../funcions/func_grups_materies.php");
ini_set("display_errors", 1);

session_start();
//Check whether the session variable SESS_MEMBER is present or not
if ((!isset($_SESSION['SESS_MEMBER'])) || ($_SESSION['SESS_MEMBER'] != "access_ok")) {
    header("location: ../login/access-denied.php");
    exit();
}
?>
<html>
    <head>
        <title>Càrrega automàtica SAGA</title>
        <meta http-equiv="Content-Type" content="text/html; charset=utf8">
        <LINK href="../estilos/oceanis/style.css" rel="stylesheet" type="text/css">
    </head>

    <body>
        <?php
        introduir_fase('grups', 0, $db);
        introduir_fase('alumne_grups', 0, $db);
        introduir_fase('materies_saga', 0, $db);
        introduir_fase('materies_gp', 0, $db);
        introduir_fase('lessons', 0, $db);
        introduir_fase('assig_alumnes', 0, $db);

        if (!extreu_fase('segona_carrega', $db)) {
            buidatge('desdegrups', $db);
        }
        // Eliminem tots els carrecs del curs anterior excepte el de superadministrador
        $sql = "DELETE FROM professor_carrec WHERE ((idcarrecs='1') OR (idcarrecs='2'));";
        $result = $db->prepare($sql);
        $result->execute();
        
        // Elimina  equivalendies de grups anteriors
        // El que realment s'ha de mantenir és elprofessorat
        $sql = "DELETE FROM equivalencies WHERE grup_gp!='' AND grup_gp IS NOT NULL;";
        $result = $db->prepare($sql);
        $result->execute();

        $recompte = $_POST['recompte'];
        // Carreguem els grups i el seu torn
        for ($i = 1; $i <= $recompte; $i++) {
            if (isset($_POST['crea_' . $i])) {$crea = $_POST['crea_' . $i];}
            $id_grup_gp = $_POST['id_grup_gp_' . $i];
            $codi_grup_gp = $_POST['codi_grup_gp_' . $i];
            if ($codi_grup_gp == '') {
                $codi_grup_gp = $id_grup_gp;
            }
            $nom_grup_gp = $_POST['name_grup_gp_' . $i];
            if ($nom_grup_gp == '') {
                $nom_grup_gp = $id_grup_gp;
            }
            $nom_grup_gp = neteja_apostrofs($nom_grup_gp);
            $id_torn = $_POST['id_torn_' . $i];
            $id_pla = $_POST['id_pla_' . $i];
            //echo $crea.">>".$id_grup_gp.">>".$nom_grup_gp.">>".$id_torn."<br>";
            if (($id_torn != "0") AND ( $id_torn != "") AND $crea) {
                $sql = "INSERT grups(codi_grup,nom,idtorn) ";
                $sql .= "VALUES ('" . $id_grup_gp . "','" . $nom_grup_gp . "','" . $id_torn . "');";
                //echo $sql;
                $result = $db->prepare($sql);
                $result->execute();

                //Extreiem l'identificador
                $id_grup = extreu_id('grups', 'codi_grup', 'idgrups', $id_grup_gp, $db);

                //Desem l'emparellament a la taula equivalencies per quan s'hagin de carregat els alumnes i matèries
                $sql = "INSERT INTO equivalencies(grup_gp,grup_ga,pla_saga) VALUES ('" . $codi_grup_gp . "','" . $id_grup . "','" . $id_pla . "');";
                $result = $db->prepare($sql);
                $result->execute();
            }
        }

	$exportsagaxml=$_SESSION['upload_saga'];
	$exporthorarixml=$_SESSION['upload_horaris'];

        if (extreu_fase('app_horaris', $db) == 0) {
            crea_agrupaments_GP($exporthorarixml,$db);
        } else if (extreu_fase('app_horaris', $db) == 1) {
            crea_agrupaments_PN($exporthorarixml,$db);
        }
        // Els agrupament , amb kronowin i aSc es generaran  al crear les unitats classe.
        //Fer-ho abasn és complicat
        //else if (extreu_fase('app_horaris') == 2) {crea_agrupaments_KW($exporthorarixml);}
        else
        if (extreu_fase('app_horaris', $db) == 3) {
            crea_agrupaments_HW($exporthorarixml,$db);
        }

        introduir_fase('grups', 1,$db);
        $page = "./menu.php";
        $sec = "0";
        header("Refresh: $sec; url=$page");
        ?>
    </body>
