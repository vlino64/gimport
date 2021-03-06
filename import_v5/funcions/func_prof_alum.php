<?php

//
/* ---------------------------------------------------------------
 * Aplicatiu: programa d'importació de dades a gassist
 * Fitxer:funcions_saga.php
 * Autor: Víctor Lino
 * Descripció: Funcions relacionades amb tasques d'importació de dades de SAGA
 * Pre condi.:
 * Post cond.:
 * 
  ---------------------------------------------------------------- */

// @@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@2
//                   	PROFESSORAT/ALUMNES/FAMILIES
// @@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@2

function profe_ja_existeix($user, $db) {

    $camps = array();
    $camps = recuperacampdedades($camps, $db);

    $sql = "SELECT * FROM contacte_professor WHERE id_tipus_contacte='" . $camps['login'] . "' AND Valor='" . $user . "';";
    //echo $sql;echo"<br>";
    $result = $db->prepare($sql);
    $result->execute();
    $present = $result->rowCount();
    if ($present == 0)
        return false;
    else
        return true;
}

function update_professorat_gassist($arrayProfessorat, $db) {
    // Crearem el csv
    $data = date("Ymd");
    $hora = date("Hi");
    $myFile = "../uploads/professorat.csv";
    $myNewFile = "../uploads/" . $data . '_' . $hora . "_professorat.csv";
    $fh = fopen($myFile, 'w') or die("can't open file");
    $stringData = "nom_i_cognoms,usuari,login,password\n";
    fwrite($fh, $stringData);

    foreach ($arrayProfessorat as $professor) {
        if ($professor[2] == 0) {
            $id_saga = $professor[0];
            $nom = $professor[1];
            $cognom1 = "";
            $cognom2 = "";
            $user = $professor[1];
            $nom_complet = $user;
            $pass = "";
            genera($user, $pass, $nom, $cognom1, $cognom2, false, $db);
            // En aquest cas no utiltzem a generació del password. Agafem com password el nom d'usuari
            // i que actualitzen la password qun facin login
            $pass = md5($user);
//                echo $user.">>>".$pass;

            $sql = "INSERT INTO `professors`(codi_professor,activat) ";
            $sql .= "VALUES ('" . $user . "','S');";
            //echo $sql."<br>";
            $result = $db->prepare($sql);
            $result->execute();
            //echo "S'ha insertat ".$nom;echo "<br>";

            $camps = array();
            $camps = recuperacampdedades($camps, $db);

            $id = extreu_id('professors', 'codi_professor', 'idprofessors', $user, $db);
            //echo "<br>".$id;

            $sql = "INSERT INTO `contacte_professor`(id_professor,id_tipus_contacte,Valor) ";
            $sql .= "VALUES ('" . $id . "','" . $camps['nom_complet'] . "','" . $nom_complet . "'),";
            $sql .= "('" . $id . "','" . $camps['login'] . "','" . $user . "'),";
            $sql .= "('" . $id . "','" . $camps['iden_ref'] . "','" . $id_saga . "'),";
            $sql .= "('" . $id . "','" . $camps['nom_profe'] . "',''),";
            $sql .= "('" . $id . "','" . $camps['cognoms_profe'] . "',''),";
            $sql .= "('" . $id . "','" . $camps['contrasenya'] . "','" . $pass . "');";
//                echo $sql."<br>";
            $result = $db->prepare($sql);
            $result->execute();

            //print("Professor donat d'alta: ".$nom." ".$cognoms.". Usuari d'accès: ".$user."<br>---<br>");
            //Escrivim en el csv
            $stringData = $nom . "," . $nom_complet . "," . $user . "," . $user . "\n";
            fwrite($fh, $stringData);


            $sql = "UPDATE equivalencies SET prof_ga='" . $id . "' WHERE codi_prof_gp = '" . $professor[0] . "';";
            //echo $sql."<br>";
            $result = $db->prepare($sql);
            $result->execute();
        }
    }

    fclose($fh);
    if (!copy($myFile, $myNewFile)) {
        echo "failed to copy";
    }
    // De la taula equivalencies, eliminem els professors als que nos se'la ha assignat identificador gassist
    $sql = "DELETE FROM `equivalencies` WHERE prof_ga IS NULL AND nom_prof_gp != '' AND codi_prof_gp != '';";
    //echo "<br>".$sql;
    $result = $db->prepare($sql);
    $result->execute();

    // Activem admin i vlino
    $sql = "UPDATE professors SET activat ='S' , historic = 'N' WHERE codi_professor = 'admin';";
    $result = $db->prepare($sql);
    $result->execute();
    $sql = "UPDATE professors SET activat ='S' , historic = 'N' WHERE codi_professor = 'vlino';";
    $result = $db->prepare($sql);
    $result->execute();


    // Passem a historic tot el professorat que està desactivat
    $sql = "UPDATE `professors` SET `historic`='S' WHERE activat='N';";
    $result = $db->prepare($sql);
    $result->execute();
    //echo "\nS'ha passat a històric tot el professorat que a hores d'ara estava desactivat !<br><br>";
    introduir_fase('professorat', '1', $db);

    die("<script>location.href = './menu.php'</script>");
}

function extreu_professorat($fitxerXml, $app) {
    //echo ">>".$fitxerXml." >> ".$app;
    if ($app != 4) { // Entrem si no és aSc
        $resultatconsulta2 = simplexml_load_file($fitxerXml);
        $arrayProfessorat = array();
        if (!$resultatconsulta2) { // no es carrega l'xml si es tracta d'un csv
            echo "Carrega fallida saga >>> " . $fitxerXml;
        } else {
            $i = 0;
            switch ($app) {
                case 5:
                    foreach ($resultatconsulta2->personal->personal as $professor) {
                        $codi = $professor['id'];
                        $nomComplet = $professor['nom'] . " " . $professor['cognom1'] . " " . $professor['cognom2'];
                        if ($nomComplet == "") {
                            $nomComplet = $codi;
                        }
                        $arrayProfessorat[$i][0] = $codi;
                        $arrayProfessorat[$i][1] = $nomComplet;
                        $i++;
                    }
                    break;
                case 0:
                    foreach ($resultatconsulta2->teachers->teacher as $professorgp) {
                        //$sql="SELECT COUNT(codi_prof_gp) FROM equivalencies WHERE nom_prof_gp='".$professorgp->surname."' AND codi_prof_gp='".$professorgp['id']."';";
                        $codi = $professorgp['id'];
                        $nomComplet = $professorgp->surname;
                        if ($nomComplet == "") {
                            $nomComplet = $codi;
                        }
                        $arrayProfessorat[$i][0] = $codi;
                        $arrayProfessorat[$i][1] = $nomComplet;
                        $i++;
                    }
                    break;
                case 1:
                    foreach ($resultatconsulta2->profesores->profesor as $professorgp) {
                        $codi = $professorgp->abreviatura;
                        if ($professorgp->abreviatura == '') {
                            $codi = $professorgp->nombre;
                        }
                        $nomComplet = $professorgp->nombreCompleto;
                        if ($nomComplet == "") {
                            $nomComplet = $codi;
                        }
                        $arrayProfessorat[$i][0] = $codi;
                        $arrayProfessorat[$i][1] = $nomComplet;
                        $i++;
                    }
                    break;
                case 2:
                    foreach ($resultatconsulta2->PROFT->PROFF as $professorgp) {

                        $codi = $professorgp['ABREV'];
                        if ($professorgp['ABREV'] == '') {
                            $codi = $professorgp['id'];
                        }
                        $nomComplet = $professorgp['NOMBRE'];
                        if ($nomComplet == "") {
                            $nomComplet = $codi;
                        }
                        $arrayProfessorat[$i][0] = $codi;
                        $arrayProfessorat[$i][1] = $nomComplet;
                        $i++;
                    }
                    break;
                case 3:
                    foreach ($resultatconsulta2->DATOS->PROFESORES->PROFESOR as $professorgp) {
                        $codi = $professorgp['abreviatura'];
                        $nomComplet = $professorgp['nombre'];
                        if ($nomComplet == "") {
                            $nomComplet = $codi;
                        }
                        $arrayProfessorat[$i][0] = $codi;
                        $arrayProfessorat[$i][1] = $nomComplet;
                        $i++;
                    }
                    break;
            }
        }
    } else {
        // Entre si és asC
        $i = 0;
        $array_from_csv = array();
        $array_from_csv = extreuProfessoratCsv();
        foreach ($array_from_csv as $prof) {
            $arrayProfessorat[$i][0] = $prof;
            $arrayProfessorat[$i][1] = $prof;
            $i++;
        }
    }
    return $arrayProfessorat;
}

function update_professorat($exporthorarixml, $db) {

    $exportsagaxml = $_SESSION['upload_saga'];

    $i = 0;
    $arrayProfessorat = array();
    if (!extreu_fase('segona_carrega', $db) && !extreu_fase('geisoft', $db)) {
        // Desactivem tot el professorat
        $sql = "UPDATE `professors` SET `activat`='N' WHERE activat='S';";
        $result = $db->prepare($sql);
        $result->execute();
    }
    // Sense programa d'horaris  
    if ((extreu_fase('app_horaris', $db) == 5) && (extreu_fase('professorat', $db) == 0)) {
        $resultatconsulta2 = simplexml_load_file($exportsagaxml);
        if (!$resultatconsulta2) { // no es carrega l'xml si es tracta d'un csv
            echo "Carrega fallida saga >>> " . $exportsagaxml;
        } else {
            // Si tens gestió centralitzada
            if (extreu_fase('geisoft', $db) == 1) {
                // Si tenim gestió centalitzada necessitarem emparellar profes donats 
                // d'alta  a gassist per assignar-los l'id a la taula equivalències
                die("<script>location.href = './act_prof_form_GEISoft.php?app=5'</script>");
            } else {
                foreach ($resultatconsulta2->personal->personal as $professor) {
                    $codi = $professor['id'];
                    $nomComplet = $professor['nom'] . " " . $professor['cognom1'] . " " . $professor['cognom2'];
                    if ($nomComplet == "") {
                        $nomComplet = $codi;
                    }
                    $arrayProfessorat[$i][0] = $codi;
                    $arrayProfessorat[$i][1] = $nomComplet;

                    $sql = "SELECT COUNT(codi_prof_gp) AS compte FROM equivalencies WHERE codi_prof_gp='" . $codi . "';";
                    //echo "<br>".$sql;
                    $result = $db->prepare($sql);
                    $result->execute();
                    $fila = $result->fetch();
//                echo "<br>>>>".$fila['compte'];
                    if ($fila['compte'] == 0) {
                        $sql = "INSERT INTO equivalencies(nom_prof_gp,codi_prof_gp) VALUES ('" . $nomComplet . "','" . $codi . "');";
//                   echo "<br>".$sql;
                        $result = $db->prepare($sql);
                        $result->execute();
                        $arrayProfessorat[$i][2] = 0; // S'ha de crear 
                    } else {
                        // Extreiem l'id
                        $idGassist = extreu_id('equivalencies', 'codi_prof_gp', 'prof_ga', $codi, $db);
                        // L'activem
                        $sql = "UPDATE professors SET activat = 'S' WHERE idprofessors = $idGassist;";
//                    echo "<br>".$sql;
                        $result = $db->prepare($sql);
                        $result->execute();
                        $arrayProfessorat[$i][2] = $idGassist; // No s'ha de crear                  
                    }
                    $i++;
                }
            }
        }
    } else {
        // Amb programa d'horaris
        echo "<br>>>>".$exporthorarixml;
        $resultatconsulta = simplexml_load_file($exporthorarixml);
        if ((!$resultatconsulta )AND ( extreu_fase('app_horaris', $db) != 4)) { // no es carrega l'xml si es tracta d'un csv
            echo "Carrega fallida horaris >>> " . $exporthorarixml;
        } else {
            if ((extreu_fase('app_horaris', $db) == 0) && (extreu_fase('professorat', $db) == 0)) {
                if (extreu_fase('geisoft', $db) == 1) {
                    // Si tenim gestió centalitzada necessitarem emparellar profes donats 
                    // d'alta  a gassist per assignar-los l'id a la taula equivalències
                    die("<script>location.href = './act_prof_form_GEISoft.php?app=0'</script>");
                } else {
                    foreach ($resultatconsulta->teachers->teacher as $professorgp) {
                        //echo "<br>Ha entrat";
                        $codi = $professorgp['id'];
                        $nomComplet = $professorgp->surname;
                        if ($nomComplet == "") {
                            $nomComplet = $codi;
                        }
                        $arrayProfessorat[$i][0] = $codi;
                        $arrayProfessorat[$i][1] = $nomComplet;

                        $sql = "SELECT COUNT(codi_prof_gp) AS compte FROM equivalencies WHERE codi_prof_gp='" . $codi . "';";
                        $result = $db->prepare($sql);
                        $result->execute();
                        $fila = $result->fetch();
                        if ($fila['compte'] == 0) {
                            $sql = "INSERT INTO equivalencies(nom_prof_gp,codi_prof_gp) VALUES('" . $nomComplet . "','" . $codi . "');";
                            //echo "<br>".$sql;
                            $result = $db->prepare($sql);
                            $result->execute();
                            $arrayProfessorat[$i][2] = 0; // S'ha de crear
                        } else {
                            // Extreiem l'id
                            $idGassist = extreu_id('equivalencies', 'codi_prof_gp', 'prof_ga', $codi, $db);
                            // L'activem
                            $sql = "UPDATE professors SET activat = 'S' WHERE idprofessors = '" . $idGassist . "';";
                            //echo "<br>".$sql;
                            $result = $db->prepare($sql);
                            $result->execute();
                            $arrayProfessorat[$i][2] = $idGassist; // No s'ha de crear                  
                        }

                        $i++;
                    }
                }
            } else if ((extreu_fase('app_horaris', $db) == 1) && (extreu_fase('professorat', $db) == 0)) {
                if (extreu_fase('geisoft', $db) == 1) {
                    // Si tenim gestió centalitzada necessitarem emparellar profes donats 
                    // d'alta  a gassist per assignar-los l'id a la taula equivalències
                    die("<script>location.href = './act_prof_form_GEISoft.php?app=1'</script>");
                } else {
                    foreach ($resultatconsulta->profesores->profesor as $professorgp) {
                        $codi = $professorgp->abreviatura;
                        if ($professorgp->abreviatura == '') {
                            $codi = $professorgp->nombre;
                        }
                        $nomComplet = $professorgp->nombreCompleto;
                        if ($nomComplet == "") {
                            $nomComplet = $codi;
                        }
                        $arrayProfessorat[$i][0] = $codi;
                        $arrayProfessorat[$i][1] = $nomComplet;
                        //$sql="SELECT COUNT(codi_prof_gp) FROM equivalencies WHERE nom_prof_gp='".$professorgp->nombreCompleto."' AND codi_prof_gp='".$abreviatura."';";
                        $sql = "SELECT COUNT(codi_prof_gp) AS compte FROM equivalencies WHERE codi_prof_gp='" . $codi . "';";
                        //echo "<br>".$sql;
                        $result = $db->prepare($sql);
                        $result->execute();
                        $fila = $result->fetch();
                        if ($fila['compte'] == 0) {
                            $sql = "INSERT INTO equivalencies(nom_prof_gp,codi_prof_gp) VALUES('" . $nomComplet . "','" . $codi . "');";
                            //echo "<br>".$sql;
                            $result = $db->prepare($sql);
                            $result->execute();
                            $arrayProfessorat[$i][2] = 0; // S'ha de crear
                        } else {
                            // Extreiem l'id
                            $idGassist = extreu_id('equivalencies', 'codi_prof_gp', 'prof_ga', $codi, $db);
                            // L'activem
                            $sql = "UPDATE professors SET activat = 'S' WHERE idprofessors = $idGassist;";
                            //echo "<br>".$sql;
                            $result = $db->prepare($sql);
                            $result->execute();
                            $arrayProfessorat[$i][2] = $idGassist;
                            ; // No s'ha de crear  
                        }
                        $i++;
                    }
                }
            } else if ((extreu_fase('app_horaris', $db) == 2) && (extreu_fase('professorat', $db) == 0)) {
                if (extreu_fase('geisoft', $db) == 1) {
                    // Si tenim gestió centalitzada necessitarem emparellar profes donats 
                    // d'alta  a gassist per assignar-los l'id a la taula equivalències
                    die("<script>location.href = './act_prof_form_GEISoft.php?app=2'</script>");
                } else {
                    foreach ($resultatconsulta->PROFT->PROFF as $professorgp) {

                        $codi = $professorgp['ABREV'];
                        if ($professorgp['ABREV'] == '') {
                            $codi = $professorgp['id'];
                        }
                        $nomComplet = $professorgp['NOMBRE'];
                        if ($nomComplet == "") {
                            $nomComplet = $codi;
                        }
                        $arrayProfessorat[$i][0] = $codi;
                        $arrayProfessorat[$i][1] = $nomComplet;

                        $sql = "SELECT COUNT(codi_prof_gp) AS compte FROM equivalencies WHERE codi_prof_gp='" . $codi . "';";
                        //echo "<br>".$sql;
                        $result = $db->prepare($sql);
                        $result->execute();
                        $fila = $result->fetch();
                        if ($fila['compte'] == 0) {
                            $sql = "INSERT INTO equivalencies(nom_prof_gp,codi_prof_gp) VALUES('" . $nomComplet . "','" . $codi . "');";
                            //echo "<br>>>>".$sql;
                            $result = $db->prepare($sql);
                            $result->execute();
                            $arrayProfessorat[$i][2] = 0; // S'ha de crear
                        } else {
                            // Extreiem l'id
                            $idGassist = extreu_id('equivalencies', 'codi_prof_gp', 'prof_ga', $codi, $db);
                            // L'activem
                            $sql = "UPDATE professors SET activat = 'S' WHERE idprofessors = $idGassist;";
                            //echo "<br>".$sql;
                            $result = $db->prepare($sql);
                            $result->execute();
                            $arrayProfessorat[$i][2] = $idGassist; // No s'ha de crear                  
                        }
                        $i++;
                    }
                }
            } else if ((extreu_fase('app_horaris', $db) == 3) && (extreu_fase('professorat', $db) == 0)) {
                if (extreu_fase('geisoft', $db) == 1) {
                    // Si tenim gestió centalitzada necessitarem emparellar profes donats 
                    // d'alta  a gassist per assignar-los l'id a la taula equivalències
                    die("<script>location.href = './act_prof_form_GEISoft.php?app=3'</script>");
                } else {
                    foreach ($resultatconsulta->DATOS->PROFESORES->PROFESOR as $professorgp) {
                        $codi = $professorgp['num_int_pr'];
                        $nomComplet = $professorgp['nombre'];
                        if ($nomComplet == "") {
                            $nomComplet = $codi;
                        }
                        $arrayProfessorat[$i][0] = $codi;
                        $arrayProfessorat[$i][1] = $nomComplet;

                        $sql = "SELECT COUNT(codi_prof_gp)AS compte FROM equivalencies WHERE codi_prof_gp='" . $codi . "';";
                        $result = $db->prepare($sql);
                        $result->execute();
                        $fila = $result->fetch();
                        if ($fila['compte'] == 0) {
                            $sql = "INSERT INTO equivalencies(nom_prof_gp,codi_prof_gp) VALUES('" . $nomComplet . "','" . $codi . "');";
                            $result = $db->prepare($sql);
                            $result->execute();
                            $arrayProfessorat[$i][2] = 0; // S'ha de crear 
                        } else {
                            // Extreiem l'id
                            $idGassist = extreu_id('equivalencies', 'codi_prof_gp', 'prof_ga', $codi, $db);
                            // L'activem
                            $sql = "UPDATE professors SET activat = 'S' WHERE idprofessors = $idGassist;";
                            //echo "<br>".$sql;
                            $result = $db->prepare($sql);
                            $result->execute();
                            $arrayProfessorat[$i][2] = $idGassist; // No s'ha de crear                  
                        }
                        $i++;
                    }
                }
            } else if ((extreu_fase('app_horaris', $db) == 4) && (extreu_fase('professorat', $db) == 0)) {
                if (extreu_fase('geisoft', $db) == 1) {
                    // Si tenim gestió centalitzada necessitarem emparellar profes donats 
                    // d'alta  a gassist per assignar-los l'id a la taula equivalències
                    die("<script>location.href = './act_prof_form_GEISoft.php?app=4'</script>");
                } else {
                    $professorat = array();
                    $professorat = extreuProfessoratCsv();
                    foreach ($professorat as $professorgp) {
                        $codi = $professorgp;
                        $nomComplet = $professorgp;
                        $arrayProfessorat[$i][0] = $codi;
                        $arrayProfessorat[$i][1] = $nomComplet;

                        $sql = "SELECT COUNT(codi_prof_gp) AS compte FROM equivalencies WHERE codi_prof_gp='" . $codi . "';";
                        echo "<br>" . $sql;
                        $result = $db->prepare($sql);
                        $result->execute();
                        $fila = $result->fetch();

                        if ($fila['compte'] == 0) {
                            $sql = "INSERT INTO equivalencies(nom_prof_gp,codi_prof_gp) VALUES('" . $nomComplet . "','" . $codi . "');";
                            $result = $db->prepare($sql);
                            $result->execute();
                            $arrayProfessorat[$i][2] = 0; // S'ha de crear
                        } else {
                            // Extreiem l'id
                            $idGassist = extreu_id('equivalencies', 'codi_prof_gp', 'prof_ga', $codi, $db);
                            // L'activem
                            $sql = "UPDATE professors SET activat = 'S' WHERE idprofessors = $idGassist;";

                            $result = $db->prepare($sql);
                            $result->execute();
                            $arrayProfessorat[$i][2] = $idGassist; // No s'ha de crear                  
                        }
                        $i++;
                    }
                }
            }
        }
    }

    update_professorat_gassist($arrayProfessorat, $db);
}

function _select_professorat($exportsagaxml, $exporthorarixml) {
    $camps = array();
    recuperacampdedades($camps, $db);

    // Desactivem tot el professorat
    $sql = "UPDATE `professors` SET `activat`='N' WHERE activat='S';";
    $result = mysql_query($sql);
    if (!$result) {
        die(_ERR_DEACT_PROF . mysql_error());
    }

    // Tornem a activar l'administrador
    $sql = "UPDATE `professors` SET `activat`='S' WHERE codi_professor='admin';";
    $result = mysql_query($sql);
    if (!$result) {
        die(_ERR_ACT_ADMIN . mysql_error());
    }

    // Tornem a activar l'usuari vlino
    $sql = "UPDATE `professors` SET `activat`='S' WHERE codi_professor='vlino';";
    $result = mysql_query($sql);
    if (!$result) {
        die(_ERR_ACT_VLINO . mysql_error());
    }

    print("<form method=\"post\" action=\"./act_prof.php\" enctype=\"multipart/form-data\" id=\"profform\">");
    //echo extreu_fase('app_horaris');
    $resultatconsulta = simplexml_load_file($exportsagaxml);
    if (extreu_fase('app_horaris') < 4) {
        $resultatconsulta2 = simplexml_load_file($exporthorarixml);
    }
    if (!$resultatconsulta) {
        echo "Carrega fallida Saga > " . $exportsagaxml;
    } else {
        if ((!$resultatconsulta2 ) AND ( extreu_fase('app_horaris') < 4)) { // No té en compte asc i saga sol
            echo "Carrega fallida Horaris > " . $exporthorarixml;
        } else {
            if (extreu_fase('app_horaris') != 5) {
                $columnes = 7;
            } else {
                $columnes = 6;
            }
            echo "<br>Carregues  correctes !";
            print("<table align=\"center\">");
            print("<tr align = \"center\"><td colspan=\"" . $columnes . "\"><h2>Selecció de professorat</h2><br><font color=\"black\">S'esbrinarà quin professorat encara no ha estat donat d'alta o té les dades incompletes . ");
            print("<br><font color=\"red\"><b>És important</b></font> que seleccionis de la darrera columna, el professor corresponent per tal de fer l'equivalència<br> i que marquis el checkbox per crear l'usuari en el programa d'assistència.</font></td></tr>");
            print("<tr><td></td><td></td><td></td><td><td>Usuaris gassist</td>");
            if (extreu_fase('app_horaris') != 5) {
                print("<td>APP HORARIS</td>");
            }
            print("</td><td>Nou professor </td></tr>");
            $pos = 1;
            foreach ($resultatconsulta->personal->personal as $professor) {
                print("<tr ");
                if ((($pos / 5) % 2) == "0") {
                    print("bgcolor=\"#ffbf6d\"");
                }
                // Imprimeix informacions de SAGA
                print("><td><input type=\"text\" name=\"nomprofsaga" . $pos . "\" value=\"" . $professor['nom'] . "\" READONLY></td>");
                print("<td><input type=\"text\" name=\"cog1profsaga" . $pos . "\" value=\"" . $professor['cognom1'] . "\" READONLY></td>");
                print("<td><input type=\"text\" name=\"cog2profsaga" . $pos . "\" value=\"" . $professor['cognom2'] . "\" READONLY></td>");
                print("<td><input type=\"text\" name=\"idprofsaga" . $pos . "\" value=\"" . $professor['id'] . "\" size=\"12\" HIDDEN></td>");



                // **********************   Imprimeix la informació de GASSIST
                // Mirem si ja té l'id de SAGA assignat
                $sql = "SELECT A.idprofessors FROM professors A,contacte_professor B ";
                $sql .= "WHERE ((A.idprofessors=B.id_professor) AND (B.id_tipus_contacte='" . $camps['iden_ref'] . "') ";
                $sql .= " AND (B.Valor='" . $professor['id'] . "'));"; //echo $sql;
                $result = mysql_query($sql);
                if (!$result) {
                    die(_ERR_LOOK_FOR_PROF . mysql_error());
                }
                $fila_prof = mysql_fetch_row($result);
                $id_professor = $fila_prof[0];
                $present = mysql_num_rows($result);

                // Si està present, l'activa
                if ($present != 0) {
                    $sql = "UPDATE `professors` SET `activat`='S' WHERE idprofessors='" . $id_professor . "';";
                    $result = mysql_query($sql);
                    if (!$result) {
                        die(_ERR_ACT_PROF . mysql_error());
                    }
                    //echo $sql;
                }

                // Seleccionem tot el professorat de gassist per emplenar el dropdown
                if ((!extreu_fase('geisoft')) AND ( extreu_fase('carrega') == 0)) {
                    print("<td>Creació automàtica</td>");
                } else {
                    $sql = "SELECT A.idprofessors, B.Valor FROM professors A,contacte_professor B ";
                    $sql .= "WHERE ((A.idprofessors=B.id_professor) AND (B.id_tipus_contacte='" . $camps['nom_complet'] . "') ";
                    //$sql.="AND (A.activat='S') AND (historic='N')) ORDER BY B.Valor";
                    $sql .= ") ORDER BY B.Valor";
                    //echo $sql;
                    $result = mysql_query($sql);
                    if (!$result) {
                        die(_ERR_INSERT_GROUPS_SUBJECTS_PUPIL . mysql_error());
                    }
                    print("<td><select name=\"id_gass" . $pos . "\" ");
                    //if ($present>0) {print("DISABLED ");}
                    print(">");
                    print("<option value=\"\">No hi ha equivalència</option>");
                    while ($fila = mysql_fetch_row($result)) {
                        $sql3 = "SELECT Valor FROM contacte_professor ";
                        $sql3 .= "WHERE ((id_tipus_contacte='" . $camps[iden_ref] . "') AND (Valor='" . $professor['id'] . "') AND (id_professor='" . $fila[0] . "'));";
                        $result3 = mysql_query($sql3);
                        if (!$result3) {
                            die(mysql_error());
                        };
                        $fila3 = mysql_num_rows($result3);

//$myFile = "/home/vlino/Dropbox/public_html/import1617/import_v3.0/uploads/testFile.txt";
//$fh = fopen($myFile, 'a') or die("can't open file");
//$stringData = $fila[0].">>".$fila[1].">>".$fila3."\n";
//fwrite($fh, $stringData);
//fclose($fh);                  
                        print("<option value=\"" . $fila[0] . "\" ");
                        if ($fila3 == 1) {
                            print("SELECTED ");
                        }
                        print(">" . $fila[1] . "</option>");
                    }
                    print("</select></td>");
                }
                // **********************************  Imprimeix la informació de gpuntis

                if (extreu_fase('app_horaris') != 5) {
                    // Comprovem si té equivalència a la base de dades ja posem l'emparellament
                    if ($id_professor != '') {
                        $sql = "SELECT codi_prof_gp FROM equivalencies WHERE prof_ga='" . $id_professor . "';";
                        $result = mysql_query($sql);
                        if (!$result) {
                            die(_ERROR1_ . mysql_error());
                        }
                        $prof_gp = mysql_result($result, 0);
                    } else {
                        $prof_gp = '';
                    }

                    print("<td><select name=\"id_gp" . $pos . "\" ");
                    print(">");
                    print("<option value=\"\">No hi ha equivalència</option>");
                    $sql = "SELECT nom_prof_gp,codi_prof_gp FROM equivalencies WHERE nom_prof_gp!='' ORDER BY nom_prof_gp;";
                    //echo $sql."<br>";
                    $result = mysql_query($sql);
                    if (!$result) {
                        die(_ERROR2_ . mysql_error());
                    }
                    while ($fila = mysql_fetch_row($result)) {
                        print("<option value=\"" . $fila[1] . "\" ");
                        if (!strcmp($prof_gp, $fila[1])) {
                            print("SELECTED ");
                        }
                        print(">" . $fila[0] . "</option>");
                    }
                    print("</select></td>");

                    // Checkbox d'alta

                    if ($present == 0) {
                        print("<td><input type=\"checkbox\" name=\"alta" . $pos . "\" ");
                        if (!extreu_fase('geisoft')) {
                            if (extreu_fase('carrega') == 0) {
                                print("CHECKED ");
                            }
                        } else {
                            //Està desactivat si utilitzo gestio centralitzada
                            print("DISABLED ");
                        }

                        print("> Crea'l</td>");
                    }
                    print("</tr> ");
                } else {
                    if ($present == 0) {
                        print("<td><input type=\"checkbox\" name=\"alta" . $pos . "\" ");
                        if (!extreu_fase('geisoft')) {
                            if (extreu_fase('carrega') == 0) {
                                print("CHECKED ");
                            }
                        } else {
                            //Està desactivat si utilitzo gestio centralitzada
                            print("DISABLED ");
                        }

                        print("> Crea'l</td>");
                    }
                    print("</tr> ");
                }
                $pos++;
            }

            $pos--;
            if ($pos != "0") {
                print("<tr><td align=\"center\" colspan=\"8\"><input name=\"boton\" type=\"submit\" id=\"boton\" value=\"Enviar\">");
                print("<tr><td align=\"center\" colspan=\"8\"><input type=\"text\" name=\"recompte\" value=\"" . $pos . "\" HIDDEN ></td></tr>");
                print("</table>");
                print("</form>");
            } else {
                introduir_fase('professorat', 1);
                $page = "./menu.php";
                $sec = "0";
                header("Refresh: $sec; url=$page");
            }
        }
    }
}

function altaAlumne($db) {
    $camps = array();
    $camps = recuperacampdedades($camps, $db);

    $sql = "ALTER TABLE `contacte_families` CHANGE `Valor` `Valor` VARCHAR(400) CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL DEFAULT NULL;";
    $result = $db->prepare($sql);
    $result->execute();


    //Desactivem tots els alumnes
    $sql = "UPDATE `alumnes` SET activat = 'N';";
    //echo $sql."<br>";
    $result = $db->prepare($sql);
    $result->execute();

    // Preparem el fitxer
    $data = date("Ymd");
    $hora = date("Hi");
    $myFile = "../uploads/alumnat.csv";
    $myNewFile = "../uploads/" . $data . $hora . "alumnat.csv";
    $fh = fopen($myFile, 'w') or die("can't open file");
    $stringData = "nom_i_cognoms,usuari,login,password\n";
    fwrite($fh, $stringData);

    $alumnat = extreuAlumnatCsv();
    for ($l = 1; $l < count($alumnat); $l++) {
        $idAlumne = $alumnat[$l][3];
        $cognom1 = $alumnat[$l][4];
        $cognom2 = $alumnat[$l][5];
        $nom = $alumnat[$l][6];
        $dataNaixement = $alumnat[$l][7];
        $adressa = $alumnat[$l][8];
        $localitat = $alumnat[$l][9];
        $tutor1Nom = $alumnat[$l][10];
        $tutor1Cognom1 = $alumnat[$l][11];
        $tutor1Cognom2 = $alumnat[$l][12];
        $tutor1mobil = $alumnat[$l][13];
        $tutor1email = $alumnat[$l][14];
        $tutor2Nom = $alumnat[$l][15];
        $tutor2Cognom1 = $alumnat[$l][16];
        $tutor2Cognom2 = $alumnat[$l][17];
        $tutor2mobil = $alumnat[$l][18];
        $tutor2email = $alumnat[$l][19];
        $altres = $alumnat[$l][20];

        $user = $cognom1 . " " . $cognom2 . ", " . $nom;
        $nom_complet = $user;
        $pass = "";
        //echo "<br>".$idAlumne;
        if ($idAlumne != "") {
            if (alumne_ja_existeix($idAlumne, $db)) {
                //Hem d'activar l'alumne
                $sql = "UPDATE `alumnes` SET activat = 'S' WHERE codi_alumnes_saga = '" . $idAlumne . "' ;";
                //echo $sql."<br>";
                $result = $db->prepare($sql);
                $result->execute();
            } else {
                // Hem de crear l'alumne

                genera($user, $pass, $nom, $cognom1, $cognom2, true, $db);
                // En aquest cas no utiltzem a generació del password. Agafem com password el nom d'usuari
                // i que actualitzen la password qun facin login
                $pass = md5($user);
                //echo $user.">>>".$pass;

                $sql = "INSERT INTO `alumnes`(codi_alumnes_saga,activat) ";
                $sql .= "VALUES ('" . $idAlumne . "','S');";
                //echo $sql."<br>";

                $result = $db->prepare($sql);
                $result->execute();
                //echo "S'ha insertat ".$nom;echo "<br>";



                $id = extreu_id('alumnes', 'codi_alumnes_saga', 'idalumnes', $idAlumne, $db);
                //echo "<br>".$id;

                $sql = "INSERT INTO `contacte_alumne`(id_alumne,id_tipus_contacte,Valor) ";
                $sql .= "VALUES ('" . $id . "','" . $camps['nom_complet'] . "','" . $nom_complet . "'),";
                $sql .= "('" . $id . "','" . $camps['login'] . "','" . $user . "'),";
                $sql .= "('" . $id . "','" . $camps['iden_ref'] . "','" . $idAlumne . "'),";
                $sql .= "('" . $id . "','" . $camps['nom_alumne'] . "','" . $nom . "'),";
                $sql .= "('" . $id . "','" . $camps['cognom1_alumne'] . "','" . $cognom1 . "'),";
                $sql .= "('" . $id . "','" . $camps['cognom2_alumne'] . "','" . $cognom2 . "'),";
                $sql .= "('" . $id . "','" . $camps['data_naixement'] . "','" . $dataNaixement . "'),";
                $md5pass = md5($user);
                $sql .= "('" . $id . "','" . $camps['contrasenya'] . "','" . $md5pass . "');";
                //            echo $sql."<br>";
                $result = $db->prepare($sql);
                $result->execute();
                //print("L'alumne/a d'alta: ".$nom_complet." Nom d'usuari: ".$user."<br>");
                //Escrivim en el csv
                $stringData = $nom . "," . $cognom1 . " " . $cognom2 . "," . $user . "," . $user . "\n";
                fwrite($fh, $stringData);

                // Generem famílies
                // Crea una família sense dades i retorna el seu id
                $id_families = crea_families($db);

                // Segon si té o no germans es modifica la sql	
                $sql = "INSERT INTO `alumnes_families`(idalumnes,idfamilies) ";
                $sql .= "VALUES ";
                $sql .= "('" . $id . "','" . $id_families . "'); ";
                //            echo $sql."<br>";
                $result = $db->prepare($sql);
                $result->execute();

                //Inserim les dades de contacte de la família si no té german que ja s'hagin donat s'alta

                $nom_complet_pare = $tutor1Nom . " " . $tutor1Cognom1 . " " . $tutor1Cognom2;
                $nom_complet_mare = $tutor2Nom . " " . $tutor2Cognom1 . " " . $tutor2Cognom2;

                $sql = "INSERT INTO `contacte_families`(id_families,id_tipus_contacte,Valor) ";
                $sql .= "VALUES ";
                $sql .= "('" . $id_families . "','" . $camps["nom_pare"] . "','" . $tutor1Nom . "') ";
                $sql .= ",('" . $id_families . "','" . $camps["cognom1_pare"] . "','" . $tutor1Cognom1 . "') ";
                $sql .= ",('" . $id_families . "','" . $camps["cognom2_pare"] . "','" . $tutor1Cognom2 . "') ";
                $sql .= ",('" . $id_families . "','" . $camps["nom_complet"] . "','" . $nom_complet_pare . "') ";
                $sql .= ",('" . $id_families . "','" . $camps["nom_complet"] . "','" . $nom_complet_pare . "') ";
                $sql .= ",('" . $id_families . "','" . $camps["mobil_sms"] . "','" . $tutor1mobil . "') ";
                $sql .= ",('" . $id_families . "','" . $camps["email1"] . "','" . $tutor1email . "') ";
                if ($tutor2Nom != "") {
                    $sql .= ",('" . $id_families . "','" . $camps["nom_mare"] . "','" . $tutor2Nom . "') ";
                    $sql .= ",('" . $id_families . "','" . $camps["cognom1_mare"] . "','" . $tutor2Cognom1 . "') ";
                    $sql .= ",('" . $id_families . "','" . $camps["cognom2_mare"] . "','" . $tutor2Cognom2 . "') ";
                    $sql .= ",('" . $id_families . "','" . $camps["nom_complet"] . "','" . $nom_complet_mare . "') ";
                    $sql .= ",('" . $id_families . "','" . $camps["mobil_sms2"] . "','" . $tutor2mobil . "') ";
                    $sql .= ",('" . $id_families . "','" . $camps["email1"] . "','" . $tutor2email . "') ";
                }
                $sql .= ",('" . $id_families . "','" . $camps["adreca"] . "','" . $adressa . "') ";
                $sql .= ",('" . $id_families . "','" . $camps["nom_municipi"] . "','" . $localitat . "') ";
                $sql .= ",('" . $id_families . "','" . $camps["telefon"] . "','" . $altres . "') ";
                //echo $sql."<br>";
                $result = $db->prepare($sql);
                $result->execute();
            }
        }
    }
    fclose($fh);
    if (!copy($myFile, $myNewFile)) {
        echo "failed to copy";
    }
    introduir_fase('families', 1, $db);
    introduir_fase('alumnat', 1, $db);

    die("<script>location.href = './menu.php'</script>");
}

function select_alumnat($db) {
    // NOMÉS S'UTILITZA PER DONAR D'ALTA DES DE FITXER DE SAGA
    $exportsagaxml = $_SESSION['upload_saga'];
    $camps = array();
    $camps = recuperacampdedades($camps, $db);

    // Desactivem tot l'alumnat que està activat a la base de dades. No els pasem de moment a l'històric
    // ja que volem que al desplegable apareguin
    $sql = "UPDATE `alumnes` SET `activat`='N' WHERE activat='S';";
    $result = $db->prepare($sql);
    $result->execute();

    // Comprovem i actualitzem el l'alumnat

    $resultatconsulta = simplexml_load_file($exportsagaxml);
    if (!$resultatconsulta) {
        echo "Carrega fallida";
    } else {
        echo "<br>Carrega correcta";

        print("<form method=\"post\" action=\"./act_alum.php\" enctype=\"multipart/form-data\" id=\"profform\">");

        print("<table align = 'center'>");
        print("<tr><td colspan=\"7\"><h2>Selecció de l'alumnat</h2><br><font color=\"white\">S'esbrinarà quin alumnat encara no ha estat donat d'alta o té les dades incompletes . ");
        print("</td></tr>");
        print("<tr align=\"center\" bgcolor=\"orange\" ><td>Nom(S)</td><td>Cognom1(S)</td><td>Cognom2(S)</td><td>Codi a Saga</td><td></td>");
        print("<td>Alta nou <br><input type=\"checkbox\" onclick=\"marcar(this);\" /> </td>");

        //if ($fase!="1") {print("<td colspan=\"2\"></td></tr>");}
        //else {print("<td> Alumnes desactivats</td><td>Actualització</td></tr>");}

        $pos = 1;
        foreach ($resultatconsulta->alumnes->alumne as $alumne) {
            // Cerca l'alumne per identificador
            // Si no hi és apareix al llistat
            $sql = "SELECT COUNT(*) AS compte FROM alumnes ";
            $sql .= "WHERE codi_alumnes_saga='" . $alumne['id'] . "';";
            $result = $db->prepare($sql);
            $result->execute();
            $fila = $result->fetch();
            $present = $fila['compte'];
            if ($present != 0) {
                // Activem l'alumnat en questió i si és un alumne que era historic , el torna a habilitar
                $sql = "UPDATE `alumnes` SET `activat`='S',historic='N'  WHERE codi_alumnes_saga='" . $alumne['id'] . "' ;";
                //echo "<br>".$sql;
                $result = $db->prepare($sql);
                $result->execute();
            } else {
                print("<tr ");
                if ((($pos / 5) % 2) == "0") {
                    print("bgcolor=\"orange\"");
                }
                print("><td><input type=\"text\" name=\"nomalumsaga" . $pos . "\" value=\"" . $alumne['nom'] . "\" READONLY></td>");
                print("<td><input type=\"text\" name=\"cog1alumsaga" . $pos . "\" value=\"" . $alumne['cognom1'] . "\" READONLY></td>");
                print("<td><input type=\"text\" name=\"cog2alumsaga" . $pos . "\" value=\"" . $alumne['cognom2'] . "\" READONLY></td>");
                print("<td><input type=\"text\" name=\"idalumsaga" . $pos . "\" value=\"" . $alumne['id'] . "\" size=\"12\" READONLY></td>");
                print("<td><input type=\"text\" name=\"naixement" . $pos . "\" value=\"" . $alumne['datanaixement'] . "\" size=\"12\" HIDDEN></td>");
                print("<td><input type=\"checkbox\" value=\"1\" name=\"alta" . $pos . "\" CHECKED ");
                print("> Alta</td>");

                print("</tr> ");
                $pos++;
            }
        }
        $pos--;
        if ($pos != 0) {
            print("<tr><td align=\"center\" colspan=\"7\"><input name=\"boton\" type=\"submit\" id=\"boton\" value=\"Enviar\"></td></tr>");
            print("<tr><td align=\"center\" colspan=\"7\"><input type=\"text\" name=\"recompte\" value=\"" . $pos . "\" HIDDEN ></td></tr>");
            print("</table>");
            print("</form>");
        } else {
            introduir_fase('families', 1, $db);
            introduir_fase('alumnat', 1, $db);
            $page = "./menu.php";
            $sec = "0";
            header("Refresh: $sec; url=$page");
        }
    }
}

function actualitzar_alumnat($exportsagaxml) {
    require_once('../../pdo/bbdd/connect.php');

    $camps = array();
    $camps = recuperacampdedades($camps, $db);

    // Desactivem tot l'alumnat que està activat a la base de dades. No els pasem de moment a l'històric
    // ja que volem que al desplegable apareguin
    $sql = "UPDATE `alumnes` SET `activat`='N' WHERE activat='S';";
    $result = $db->prepare($sql);
    $result->execute();

    // Comprovem i actualitzem el l'alumnat

    $resultatconsulta = simplexml_load_file($exportsagaxml);
    if (!$resultatconsulta) {
        echo "Carrega fallida";
    } else {

        print("<form method=\"post\" action=\"./act_alum_SAGA.php\" enctype=\"multipart/form-data\" id=\"profform\">");

        print("<table align = 'center'>");
        print("<tr><td colspan=\"8\"><h2>Selecció de l'alumnat</h2><br><font color=\"white\">S'esbrinarà quin alumnat encara no ha estat donat d'alta o té les dades incompletes . ");
        print("</td></tr>");
        print("<tr align=\"center\" bgcolor=\"orange\" ><td>Nom(S)</td><td>Cognom1(S)</td><td>Cognom2(S)</td><td>Codi a Saga</td><td></td>");
        print("<td>Matricular <br><input type=\"checkbox\" onclick=\"marcar(this);\" /> </td><td>grups Saga on està assignat</td><td>Grup on matricular</td>");

        //if ($fase!="1") {print("<td colspan=\"2\"></td></tr>");}
        //else {print("<td> Alumnes desactivats</td><td>Actualització</td></tr>");}

        $pos = 1;
        foreach ($resultatconsulta->alumnes->alumne as $alumne) {
            // Cerca l'alumne per identificador
            // Si no hi és apareix al llistat
            $sql = "SELECT idalumnes FROM alumnes ";
            $sql .= "WHERE codi_alumnes_saga='" . $alumne['id'] . "';";
            //echo "<br>".$alumne['id'];
            $result = $db->prepare($sql);
            $result->execute();
            $present = $result->rowCount();
            if ($present > 0) {
                // Activem l'alumnat en questió i si és un alumne que era historic , el torna a habilitar
                $sql = "UPDATE `alumnes` SET `activat`='S',historic='N'  WHERE codi_alumnes_saga='" . $alumne['id'] . "' ;";
                //echo "<br>".$sql;
                $result = $db->prepare($sql);
                $result->execute();
            } else {
                print("<tr ");
                if ((($pos / 5) % 2) == "0") {
                    print("bgcolor=\"orange\"");
                }
                print("><td><input type=\"text\" name=\"nomalumsaga" . $pos . "\" value=\"" . $alumne['nom'] . "\" READONLY></td>");
                print("<td><input type=\"text\" name=\"cog1alumsaga" . $pos . "\" value=\"" . $alumne['cognom1'] . "\" READONLY></td>");
                print("<td><input type=\"text\" name=\"cog2alumsaga" . $pos . "\" value=\"" . $alumne['cognom2'] . "\" READONLY></td>");
                print("<td><input type=\"text\" name=\"idalumsaga" . $pos . "\" value=\"" . $alumne['id'] . "\" size=\"12\" READONLY></td>");
                print("<td><input type=\"text\" name=\"naixement" . $pos . "\" value=\"" . $alumne['datanaixement'] . "\" size=\"12\" HIDDEN></td>");
                print("<td><input type=\"checkbox\" value=\"1\" name=\"alta" . $pos . "\" CHECKED ");
                print("> Matricula'l</td>");
                $grup_inscrit = extreuGrupsSaga($exportsagaxml, $alumne['id']);
                print("<td><input type=\"text\" name=\"grupalumsaga" . $pos . "\" value=\"" . $grup_inscrit . "\" READONLY></td>");

                print("<td><select name=\"id_grup_" . $pos . "\" ");
                print(">");
                print("<option value=\"0\">Cap equivalència</option>");
                $sql = "SELECT idgrups,nom FROM grups WHERE 1 ORDER BY nom; ";
                $result = $db->prepare($sql);
                $result->execute();
                foreach ($result->fetchAll() as $fila) {
                    print("<option value=\"" . $fila['idgrups'] . "\" ");
                    print(">" . $fila['nom'] . "</option>");
                }
                print("</select></td>");
                print("</tr> ");
                $pos++;
            }
        }
        $pos--;
        if ($pos != 0) {
            print("<tr><td align=\"center\" colspan=\"7\"><input name=\"boton\" type=\"submit\" id=\"boton\" value=\"Enviar\"></td></tr>");
            print("<tr><td align=\"center\" colspan=\"7\"><input type=\"text\" name=\"recompte\" value=\"" . $pos . "\" HIDDEN ></td></tr>");
            print("</table>");
            print("</form>");
        } else {

            $page = "./index.php";
            $sec = "0";
            header("Refresh: $sec; url=$page");
        }
    }
}

function extreuGrupsSaga($exportsagaxml, $id_alumne) {
    $string_grups = "";
    $resultatconsulta = simplexml_load_file($exportsagaxml);
    if (!$resultatconsulta) {
        echo "Carrega fallida";
    } else {
        //echo "<br>Carrega correcta";
        foreach ($resultatconsulta->grups->grup as $grup) {
            foreach ($grup->alumnes->alumne as $alumne) {
                if (!strcmp($alumne['id'], $id_alumne)) {
                    $string_grups = $string_grups . $grup['nom'] . "/";
                }
            }
        }
    }
    return $string_grups;
}

// Aquesta funció és per regenerar tota la informació de les families en funció del contingut de saga
// Tot el contingut anterior s'esborrarà

function carrega_dades_families($id, $id_saga, $db) {
    $dades = array();
    $exportsagaxml = $_SESSION['upload_saga'];
    echo "<br>>>>>" . $exportsagaxml;
    $resultatconsulta = simplexml_load_file($exportsagaxml);
    if (!$resultatconsulta) {
        echo "Carrega fallidaa";
    } else {
        //echo "<br>Carrega inicial correcta<br>";
        // Fem el recorrgut alumne per alumne
        foreach ($resultatconsulta->alumnes->alumne as $alumne) {
            //Inicialitzem la matriu de dades
            for ($j = 0; $j < 14; $j++) {
                $dades[$j] = "";
            }
            //echo "<br>".$alumne[id]." >> ".$id_saga;
            if ($alumne['id'] == $id_saga) {
                //echo "<br>Dins";
                // Li exteriem l'id de gassist
                $dades[0] = $id;
                // Comprovem si es tracta d'un alumne que tot i estar al SAGA encara no s'ha donat d'alta 
                // per no haver netejat tots els alumnes de la llista
                if ($dades[0] != "") {
                    $correu = "";
                    $telefon = "";
                    $movil_sms = "";
                    foreach ($alumne->contacte as $contacte) {
                        if ($contacte['tipus'] == "EMAIL") {
                            $correu = $correu . " / " . $contacte['contacte'];
                        }

                        if ($contacte['tipus'] == "TELEFON") {// Genera cadena de telefons
                            $telefon = $telefon . " / " . $contacte['contacte'];
                            if ($movil_sms == "") {
                                $movil_sms = esmovilsms($contacte['contacte']);
                            }
                        }
                    }
                    $dades[10] = $correu;
                    $dades[9] = $telefon;
                    $dades[11] = $movil_sms;
                    //echo "<br>>>".$telefon;
                    // Extreiem quants tutors  legals té l'alumne
                    $nomb_tutors = 0;
                    if ($alumne['tutor1'] != "") {
                        $nomb_tutors++;
                    }
                    if ($alumne['tutor2'] != "") {
                        $nomb_tutors++;
                    }
                    //echo "EL NOMBRE DE TUTORS ÉS:".$nomb_tutors; 
                    //echo "<br>";		
                    for ($k = 1; $k <= $nomb_tutors; $k++) {
                        $tutor_mod = "tutor" . $k;
                        //echo "<br>".$tutor_mod;
                        if ($alumne[$tutor_mod] != "") {
                            $id_tutor = $alumne[$tutor_mod];
                            //echo "<br>".$id_tutor;
                            //Comprova si hi ha alguna família que ja té aquest tutor
                            // Si hi ha un germà, surt del bucle
                            //$germa=hi_ha_germa($id_tutor);
                            //echo "<br>Hi ha germà... ".$germa;
                            if (true) {
                                $resultatconsulta3 = simplexml_load_file($exportsagaxml);
                                if (!$resultatconsulta3) {
                                    echo "Carrega fallida";
                                } else {
                                    foreach ($resultatconsulta3->{'tutors-legals'}->{'tutor-legal'} as $tutor) {
                                        if (!strcmp($tutor['id'], $id_tutor)) {
                                            //echo "<br>".$nomb_tutors;
                                            if ($k == "1") {
                                                $dades[12] = $id_tutor;
                                                $dades[1] = $tutor['nom'];
                                                $dades[2] = $tutor['cognom1'];
                                                $dades[3] = $tutor['cognom2'];
                                                //echo "<br>".$dades[2]." >> ".$dades[3]." >> ".$dades[4];
                                                $dades[7] = $tutor['adreca'];
                                                $dades[8] = $tutor['nomlocalitat'];
                                            } else {
                                                $dades[13] = $id_tutor;
                                                $dades[4] = $tutor['nom'];
                                                $dades[5] = $tutor['cognom1'];
                                                $dades[6] = $tutor['cognom2'];
                                                //echo "<br>".$dades[5]." >> ".$dades[6]." >> ".$dades[7];
                                            }
                                            break;
                                        }
                                    }
                                }
                            }
                        }
                    }

                    // Crea una família sense dades i retorna el seu id
                    $id_families = crea_families($db);

                    $camps = array();
                    $camps = recuperacampdedades($camps, $db);

                    // Segon si té o no germans es modifica la sql	
                    $sql = "INSERT INTO `alumnes_families`(idalumnes,idfamilies) ";
                    $sql .= "VALUES ";
                    if (true) {
                        $sql .= "('" . $dades[0] . "','" . $id_families . "'); ";
                    } else {
                        $sql .= "('" . $dades[0] . "','" . $germa . "'); ";
                    }
                    //echo $sql;echo "<br>";
                    $result = $db->prepare($sql);
                    $result->execute();


                    /*
                      0>id gassist // 1>nom_pare // 2>cognoms1_pare // 3>cognom2_pare // 4>Nom_mare
                      5>cognom1_mare // 6>cognom2_mare // 7>adreça // 8>localitat // 9>telefon
                      10>correu // 11>mobil // 12>id_tutor1 // 13>id_tutor2
                     */

                    //Inserim les dades de contacte de la família si no té german que ja s'hagin donat s'alta

                    for ($l = 1; $l <= 13; $l++) {
                        $dades[$l] = neteja_apostrofs($dades[$l]);
                    }

                    if (true) {
                        $nom_complet_pare = $dades[1] . " " . $dades[2] . " " . $dades[3];
                        $nom_complet_mare = $dades[4] . " " . $dades[5] . " " . $dades[6];

                        $sql = "INSERT INTO `contacte_families`(id_families,id_tipus_contacte,Valor) ";
                        $sql .= "VALUES ";
                        $sql .= "('" . $id_families . "','" . $camps["nom_pare"] . "','" . $dades[1] . "') ";
                        $sql .= ",('" . $id_families . "','" . $camps["cognom1_pare"] . "','" . $dades[2] . "') ";
                        $sql .= ",('" . $id_families . "','" . $camps["cognom2_pare"] . "','" . $dades[3] . "') ";
                        $sql .= ",('" . $id_families . "','" . $camps["nom_complet"] . "','" . $nom_complet_pare . "') ";
                        if ($nomb_tutors == "2") {
                            $sql .= ",('" . $id_families . "','" . $camps["nom_mare"] . "','" . $dades[4] . "') ";
                            $sql .= ",('" . $id_families . "','" . $camps["cognom1_mare"] . "','" . $dades[5] . "') ";
                            $sql .= ",('" . $id_families . "','" . $camps["cognom2_mare"] . "','" . $dades[6] . "') ";
                            $sql .= ",('" . $id_families . "','" . $camps["nom_complet"] . "','" . $nom_complet_mare . "') ";
                        }
                        $sql .= ",('" . $id_families . "','" . $camps["adreca"] . "','" . $dades[7] . "') ";
                        $sql .= ",('" . $id_families . "','" . $camps["nom_municipi"] . "','" . $dades[8] . "') ";
                        $sql .= ",('" . $id_families . "','" . $camps["telefon"] . "','" . $dades[9] . "') ";
                        $sql .= ",('" . $id_families . "','" . $camps["email1"] . "','" . $dades[10] . "') ";
                        $sql .= ",('" . $id_families . "','" . $camps["mobil_sms"] . "','" . $dades[11] . "'); ";
//							$sql.=",('".$id_families."','".$camps["iden_ref"]."','".$dades[12]."') ";
//							$sql.=",('".$id_families."','".$camps["iden_ref"]."','".$dades[13]."'); ";
                        //echo $sql."<br>";
                        $result = $db->prepare($sql);
                        $result->execute();
                    }
                }
                //break; // Com que ja l'ha trobat, surt de la cerca del foreach
            }// Comprovació si s'ha de carregar o descartar
        }//foreach principal per cercar l'alumne
    }
    //mysql_close($conexion);
}

function hi_ha_germa($id_tutor, $db) {

    //Insereix l'autoincrement que serà l'identificador unívoc de les famílies
    $sql = "SELECT id_families FROM contacte_families WHERE Valor='" . $id_tutor . "' LIMIT 1;";
    //echo $sql;echo"<br>";
    $result = $db->prepare($sql);
    $result->execute();
    $germa = $result->fetch();
    return $germa['id_families'];
}

function crea_families($db) {

    //Insereix l'autoincrement que serà l'identificador unívoc de les famílies
    $sql = "INSERT INTO `families`(idfamilies) VALUE (null);";
    //echo $sql;echo"<br>";
    $result = $db->prepare($sql);
    $result->execute();

    $sql = "SELECT MAX(idfamilies) AS Darrer FROM families;";
    //echo $sql;echo"<br>";
    $result = $db->prepare($sql);
    $result->execute();
    $idfamilies = $result->fetch();

    return $idfamilies['Darrer'];
}

function alumne_ja_existeix($user, $db) {

    $camps = array();
    recuperacampdedades($camps, $db);

    $sql = "SELECT idalumnes AS compta FROM alumnes WHERE codi_alumnes_saga='" . $user . "';";
    //echo $sql;echo"<br>";
    $result = $db->prepare($sql);
    $result->execute();
    $present = $result->rowCount();
    if ($present == 0)
        return false;
    else
        return true;
}

function genera(&$user, &$pass, $nom, $cognom1, $cognom2, $alumne, $db) {

    $existeix = 1;
    while ($existeix) {
        $cadena = limpia_cadena($user, $nom, $cognom1, $cognom2);
//		$user=substr($user, 0, 12);
        if (!$alumne) {
            $existeix = profe_ja_existeix($cadena, $db);
        } else {
            $existeix = alumne_ja_existeix($cadena, $db);
        }
        //echo "<br>      ",$existeix."<br>";
    }
    $pass = randomPassword();
    $user = $cadena;
//	echo $user."<br>";
}

function randomPassword() {
    $alphabet = "abcdefghijklmnopqrstuwxyzABCDEFGHIJKLMNOPQRSTUWXYZ0123456789";
    $pass = array(); //remember to declare $pass as an array
    $alphaLength = strlen($alphabet) - 1; //put the length -1 in cache
    for ($i = 0; $i < 5; $i++) {
        $n = rand(0, $alphaLength);
        $pass[] = $alphabet[$n];
    }
    return implode($pass); //turn the array into a string
}

function randomPassword_numbers() {
    $alphabet = "0123456789";
    $pass = array(); //remember to declare $pass as an array
    $alphaLength = strlen($alphabet) - 1; //put the length -1 in cache
    for ($i = 0; $i < 5; $i++) {
        $n = rand(0, $alphaLength);
        $pass[] = $alphabet[$n];
    }
    return implode($pass); //turn the array into a string
}

function limpia_cadena($cadena, $nom, $cognom1, $cognom2) {

    //echo "<br> Nom sense netejar: ".$cadena;
    $cadena = mb_strtolower($cadena, 'UTF-8');
    $cadena = str_replace(".", "", $cadena);
    $cadena = str_replace("á", "a", $cadena);
    $cadena = str_replace("ª", "", $cadena);
    $cadena = str_replace("º", "", $cadena);
    $cadena = str_replace("à", "a", $cadena);
    $cadena = str_replace("é", "e", $cadena);
    $cadena = str_replace("è", "e", $cadena);
    $cadena = str_replace("í", "i", $cadena);
    $cadena = str_replace("ó", "o", $cadena);
    $cadena = str_replace("ò", "o", $cadena);
    $cadena = str_replace("ú", "u", $cadena);
    $cadena = str_replace("ü", "u", $cadena);
    $cadena = str_replace("ï", "i", $cadena);
    $cadena = str_replace(".", "", $cadena);
    $cadena = str_replace("ñ", "n", $cadena);
    $cadena = str_replace(",", "", $cadena);
    $cadena = str_replace("ç", "c", $cadena);
    $cadena = str_replace('\'', '', $cadena);

    $number = randomPassword_numbers();
    $cadena_arr = explode(" ", $cadena);
    $paraules = count($cadena_arr);
    if ($paraules == 1) {
        $cadena = $cadena_arr[0];
    } else if ($paraules == 2) {
        $cadena = substr($cadena_arr[0], 0, 1) . substr($cadena_arr[1], 0, 5);
    } else if ($paraules == 3) {
        $cadena = substr($cadena_arr[0], 0, 1) . substr($cadena_arr[1], 0, 2) . substr($cadena_arr[2], 0, 3);
    } else if ($paraules >= 4) {
        $cadena = substr($cadena_arr[0], 0, 1) . substr($cadena_arr[1], 0, 2) . substr($cadena_arr[2], 0, 2) . substr($cadena_arr[3], 0, 2);
    }
    $longitud = strlen($cadena);
    //echo "<br>".$cadena." ".$longitud." ".$number;
    $cadena = $cadena . substr($number, 0, 7 - $longitud);

    return $cadena;
}

function _limpia_telefonos($cadena) {
    $cadena = str_replace('-', '', $cadena);
    $cadena = str_replace(' ', '', $cadena);
    $cadena = str_replace('.', '', $cadena);
    return $cadena;
}

function esmovilsms($telefon) {
    
}

// @@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@2
// 				ASSISGNACIÓ ALUMNES A GRUPS MATÈRIA
// @@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@2

function select_grups_per_matricular_csv_materies($db) {

    $arr_grups_csv = extreuGrupsCsv2();

    print("<form method=\"post\" action=\"./assignacions_act_csv_materies.php\" enctype=\"multipart/form-data\" id=\"profform\">");

    print("<table align=\"center\" width=\"60%\">");
    print("<tr><td align=\"center\" colspan=\"3\"><h3>INSTRUCCIONS</h3></td></tr>");
    print("<tr><td align=\"center\" colspan=\"3\">");
    print("<font color =\"red\"><br>Es tracta d'una assignació per matèries que s'està provant de cara a futures importacions</font><br>");
    print("Per poder realitzar aquesta càrrega: <br>");
    print("S'ha hagut de realitzar una exportació específica molt concreta de  SAGA per obtenir les matèries a les que està matriculat un alumne.<br>");
    print("Que les maeries i UFs tinguin correspondencia completa entre SAGA i el programa d'horaris.<br>");
    print("Les matèries del programa d'horaris  no han de tenir la xifra final corresponent al curs  que tenen a SAGA.<br>");
    print("S'haurà d'etablir el paral.lelisme entre grups de SAGA i el program d'horaris.<br></td></tr>");
    print("<tr><td align=\"center\" colspan=\"3\"><input name=\"boton\" type=\"submit\" id=\"boton\" value=\"Seguim!\">");
    print("&nbsp&nbsp<input type=button onClick=\"location.href='./menu.php'\" value=\"Torna al menú!\" ></td></tr>");
    print("</table>");
    print("</form>");
}

function select_grups_per_matricular_csv($db) {

    $arr_grups_csv = extreuGrupsCsv2();

    print("<form method=\"post\" action=\"./assignacions_act_csv.php\" enctype=\"multipart/form-data\" id=\"profform\">");

    print("<table align=\"center\" width=\"60%\">");
    print("<tr><td align=\"center\" colspan=\"3\"><h3>INSTRUCCIONS</h3></td></tr>");
    print("<tr><td align=\"center\" colspan=\"3\">Si selecciones un grup del fitxer CSV, tots els alumnes que consten com alumnes ");
    print("d'aquest grup es matricularan a totes les matèries vinculades al grup. <br>   ");
    print("<b>Tingues present que els professors poden inscriure els alumnes als seus grups des de l'aplicació<b>");
    print(". <br>");
    print("<font color =\"red\"><br>Si es tracta d'una segona càrrega amb un segon fitxer d'horaris, no tornis a matricular els alumnes ja matriculats</font>");
    print("<tr align=\"center\" bgcolor=\"#ffbf6d\" ><td>Grup app horaris </td><td></td><td>Grups fitxer csv</td></tr>");
    $pos = 1;
    //echo "<br>".$sql;
    $sql = "SELECT idgrups,nom FROM grups WHERE 1 ORDER BY nom; ";
    $result = $db->prepare($sql);
    $result->execute();
    foreach ($result->fetchAll() as $fila) {
        print("<tr ");
        if ((($pos / 5) % 2) == "0") {
            print("bgcolor=\"#3f3c3c\"");
        }
        print("><td><input type=\"text\" name=\"nom_grup_" . $pos . "\" value=\"" . $fila[1] . "\" SIZE=\"50\" READONLY></td>");
        print("<td><input type=\"text\" name=\"id_grup_" . $pos . "\" value=\"" . $fila[0] . "\" SIZE=\"6\" HIDDEN></td>");

        print("<td><select name=\"id_grup_saga_" . $pos . "\" ");
        print(">");
        print("<option value=\"0\">Cap equivalència</option>");
        foreach ($arr_grups_csv as $grupscsv) {
            print("<option value=\"" . $grupscsv . "\">" . $grupscsv . "</option>");
        }
        print("</select></td>");

        print("</tr> ");
        $pos++;
    }
    $pos--;
    print("<tr><td align=\"center\" colspan=\"3\"><input name=\"boton\" type=\"submit\" id=\"boton\" value=\"Enviar\">");
    print("&nbsp&nbsp<input type=button onClick=\"location.href='./menu.php'\" value=\"Torna al menú!\" ></td></tr>");
    print("<tr><td align=\"center\" colspan=\"3\"><input type=\"text\" name=\"recompte\" value=\"" . $pos . "\" HIDDEN ></td></tr>");
    print("</table>");
    print("</form>");
}

function select_grups_per_matricular($exportsagaxml, $db) {

    $resultatconsulta = simplexml_load_file($exportsagaxml);
    if (!$resultatconsulta) {
        echo "Càrrega fallida.";
    } else {

        print("<form method=\"post\" action=\"./assignacions_act.php\" enctype=\"multipart/form-data\" id=\"profform\">");

        print("<table align=\"center\" width=\"60%\">");
        print("<tr><td align=\"center\" colspan=\"3\"><h3>INSTRUCCIONS</h3></td></tr>");
        print("<tr><td align=\"center\" colspan=\"3\">Si selecciones un grup de SAGA, tots els alumnes que consten al saga com alumnes ");
        print("d'aquest grup es matricularan a totes les matèries vinculades al grup. <br>   ");
        print("Tingues present que els professors poden inscriure els alumnes als seus grups i també disposes de l'opció ");
        print("de l'automatrícula. <br> <b>Clicant en els enllaços del part superior pots veure els alumnes que consten a cada grup de saga</b>");
        print("<font color =\"red\"><br>Si es tracta d'una segona càrrega amb un segon fitxer d'horaris, no tornis a matricular esls alumnes ja matriculats</font>");
        print("<tr align=\"center\" bgcolor=\"#ffbf6d\" ><td>Grup app horaris </td><td></td><td>Grups SAGA</td></tr>");
        $pos = 1;
        //echo "<br>".$sql;
        $sql = "SELECT idgrups,nom FROM grups WHERE 1 ORDER BY nom; ";
        $result = $db->prepare($sql);
        $result->execute();
        foreach ($result->fetchAll() as $fila) {
            print("<tr ");
            if ((($pos / 5) % 2) == "0") {
                print("bgcolor=\"#3f3c3c\"");
            }
            print("><td><input type=\"text\" name=\"nom_grup_" . $pos . "\" value=\"" . $fila['nom'] . "\" SIZE=\"50\" READONLY></td>");
            print("<td><input type=\"text\" name=\"id_grup_" . $pos . "\" value=\"" . $fila['idgrups'] . "\" SIZE=\"6\" HIDDEN></td>");

            print("<td><select name=\"id_grup_saga_" . $pos . "\" ");
            print(">");
            print("<option value=\"0\">Cap equivalència</option>");
            foreach ($resultatconsulta->grups->grup as $grupssaga) {
                print("<option value=\"" . $grupssaga['id'] . "\">(" . $grupssaga['codi'] . ") " . $grupssaga['nom'] . "</option>");
            }
            print("</select></td>");

            print("</tr> ");
            $pos++;
        }
        $pos--;
        print("<tr><td align=\"center\" colspan=\"3\"><input name=\"boton\" type=\"submit\" id=\"boton\" value=\"Enviar\">");
        print("&nbsp&nbsp<input type=button onClick=\"location.href='./menu.php'\" value=\"Torna al menú!\" ></td></tr>");
        print("<tr><td align=\"center\" colspan=\"3\"><input type=\"text\" name=\"recompte\" value=\"" . $pos . "\" HIDDEN ></td></tr>");
        print("</table>");
        print("</form>");
    }
}

function _select_grups_per_matricular_cali($exportsagaxml) {
    require_once('../../bbdd/connect.php');
    $resultatconsulta = simplexml_load_file($exportsagaxml);
    if (!$resultatconsulta) {
        echo "Càrrega fallida.";
    } else {

        print("<form method=\"post\" action=\"./assignacions_act.php\" enctype=\"multipart/form-data\" id=\"profform\">");

        print("<table align=\"center\" width=\"60%\">");
        print("<tr><td align=\"center\" colspan=\"3\"><h3>INSTRUCCIONS</h3></td></tr>");
        print("<tr><td align=\"center\" colspan=\"3\">Comprova els emparellaments ");

        print("<tr align=\"center\" bgcolor=\"#ffbf6d\" ><td>Grup app horaris </td><td></td><td>Grups SAGA</td></tr>");
        $pos = 1;
        //echo "<br>".$sql;
        $sql = "SELECT idgrups,nom FROM grups WHERE 1 ORDER BY nom; ";
        $result = mysql_query($sql);
        if (!$result) {
            die(mysql_error());
        }
        while ($fila = mysql_fetch_row($result)) {
            print("<tr ");
            if ((($pos / 5) % 2) == "0") {
                print("bgcolor=\"#3f3c3c\"");
            }
            print("><td><input type=\"text\" name=\"nom_grup_" . $pos . "\" value=\"" . $fila[1] . "\" SIZE=\"50\" READONLY></td>");
            print("<td><input type=\"text\" name=\"id_grup_" . $pos . "\" value=\"" . $fila[0] . "\" SIZE=\"6\" HIDDEN></td>");

            print("<td><select name=\"id_grup_saga_" . $pos . "\" ");
            print(">");
            print("<option value=\"0\">Cap equivalència</option>");
            foreach ($resultatconsulta->grups->grup as $grupssaga) {
                print("<option value=\"" . $grupssaga['id'] . "\" ");
                $arraygrup1 = explode(" ", $grupssaga['nom']);
                $arraygrup2 = explode("-", $arraygrup1[0]);
                $grupssaga2 = $arraygrup2[0] . $arraygrup2[1];
                if ($grupssaga2 == $fila[1])
                    print(" SELECTED ");
                print(">(" . $grupssaga[codi] . ") " . $grupssaga[nom] . "</option>");
            }
            print("</select></td>");

            print("</tr> ");
            $pos++;
        }
        $pos--;
        print("<tr><td align=\"center\" colspan=\"3\"><input name=\"boton\" type=\"submit\" id=\"boton\" value=\"Enviar\">");
        print("&nbsp&nbsp<input type=button onClick=\"location.href='./menu.php'\" value=\"Torna al menú!\" ></td></tr>");
        print("<tr><td align=\"center\" colspan=\"3\"><input type=\"text\" name=\"recompte\" value=\"" . $pos . "\" HIDDEN ></td></tr>");
        print("</table>");
        print("</form>");
    }
}

function mostra_grups($exportsagaxml) {
    $resultatconsulta = simplexml_load_file($exportsagaxml);
    $resultatconsulta2 = simplexml_load_file($exportsagaxml);

    print('<table border ="0" align="center"');
    print("<tr><td colspan = \"5\"><h3>Alumnes de cada grup dels que consten al fitxer de SAGA</h3></td></tr>");
    print('<tr>');
    $i = 0;
    foreach ($resultatconsulta->grups->grup as $grup) {
        //print('<td><a <input type="button value="'.$grup[nom].'" onClick="ccalert1('.$grup[id].')">fsdgdsf</td>');
        //print('<td><a id="myLink" title="Visualitza els alumnes del grup" href="alumnes_grup.php?idgrup='.$grup[id].'" >'.$grup[nom].'</a></td>');
        print('<td><a href="javascript:window.open(\'alumnes_grup.php?idgrup=' . $grup['id'] . '\',\'mywindowtitle\',\'width=400,height=600\')">' . $grup['nom'] . '</a></td>');
        $i++;
        if ($i % 5 == 0) {
            print('</tr><tr>');
        }
    }
}

?>