<?php //
/*---------------------------------------------------------------
* Aplicatiu: programa d'importació de dades a gassist
* Fitxer:funcions_saga.php
* Autor: Víctor Lino
* Descripció: Funcions relacionades amb tasques d'importació de dades de SAGA
* Pre condi.:
* Post cond.:
* 
----------------------------------------------------------------*/

// @@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@2
//                   	PROFESSORAT/ALUMNES/FAMILIES
// @@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@2


function intro_prof_equivalencies_gp($exportsagaxml,$exporthorarixml)
    {
    
    	
   
    $resultatconsulta=simplexml_load_file($exporthorarixml);
    if ((!$resultatconsulta )AND (extreu_fase('app_horaris')!=4)) // no es carrega l'xml si es tracta d'un csv
         {
         echo "Carrega fallida horaris >> ".$exporthorarixml;
         }
   else
      {
      if((extreu_fase('app_horaris')==0) && (extreu_fase('professorat')==0))
         {
         foreach ($resultatconsulta->teachers->teacher as $professorgp)
            {
            //$sql="SELECT COUNT(codi_prof_gp) FROM equivalencies WHERE nom_prof_gp='".$professorgp->surname."' AND codi_prof_gp='".$professorgp['id']."';";
            $sql="SELECT COUNT(codi_prof_gp) FROM equivalencies WHERE codi_prof_gp='".$professorgp['id']."';";
            $result=mysql_query($sql); if (!$result) {die(_ENPARELLAMENTS_GP_.mysql_error());}
            $resultat = mysql_fetch_row($result);
            if ($resultat[0] == 0)
               {
               $sql="INSERT INTO equivalencies(nom_prof_gp,codi_prof_gp) VALUES('".$professorgp->surname."','".$professorgp['id']."');";
               echo "<br>".$sql;
               $result=mysql_query($sql); if (!$result) {die(_ENPARELLAMENTS_GP_.mysql_error());}
               }
            }
         }
      else if((extreu_fase('app_horaris')==1) && (extreu_fase('professorat')==0))
         {
         foreach ($resultatconsulta->profesores->profesor as $professorgp)
            {
            $abreviatura=$professorgp->abreviatura;
              if ($professorgp->abreviatura =='') {$abreviatura=$professorgp->nombre;}
            //$sql="SELECT COUNT(codi_prof_gp) FROM equivalencies WHERE nom_prof_gp='".$professorgp->nombreCompleto."' AND codi_prof_gp='".$abreviatura."';";
            $sql="SELECT COUNT(codi_prof_gp) FROM equivalencies WHERE codi_prof_gp='".$abreviatura."';";
            //echo "<br>".$sql;
            $result=mysql_query($sql); if (!$result) {die(_ENPARELLAMENTS_GP2_.mysql_error());}
            $resultat = mysql_fetch_row($result);
            if ($resultat[0] == 0)
               {
               $sql="INSERT INTO equivalencies(nom_prof_gp,codi_prof_gp) VALUES('".$professorgp->nombreCompleto."','".$abreviatura."');";
               //echo "<br>".$sql;
               $result=mysql_query($sql); if (!$result) {die(_ENPARELLAMENTS_GP2_.mysql_error());}
               }
            }
         }
      else if((extreu_fase('app_horaris')==2) && (extreu_fase('professorat')==0))
         {
         foreach ($resultatconsulta->PROFT->PROFF as $professorgp)
            {
            
            $abreviatura=$professorgp['ABREV'];
              if ($professorgp['ABREV'] =='') {$abreviatura=$professorgp['id'];}
            //$sql="SELECT COUNT(codi_prof_gp) FROM equivalencies WHERE nom_prof_gp='".$professorgp['NOMBRE']."' AND codi_prof_gp='".$abreviatura."';";
            $sql="SELECT COUNT(codi_prof_gp) FROM equivalencies WHERE codi_prof_gp='".$abreviatura."';";
            //echo "<br>".$sql;
            $result=mysql_query($sql); if (!$result) {die(_ENPARELLAMENTS_GP3_.mysql_error());}
            $resultat = mysql_fetch_row($result);
            if ($resultat[0] == 0)
               {
               $sql="INSERT INTO equivalencies(nom_prof_gp,codi_prof_gp) VALUES('".$professorgp['NOMBRE']."','".$abreviatura."');";
               //echo "<br>>>>".$sql;
               $result=mysql_query($sql); if (!$result) {die(_ENPARELLAMENTS_GP3_.mysql_error());}
               }
            }
         }	
      else if((extreu_fase('app_horaris')==3) && (extreu_fase('professorat')==0))
         {
         foreach ($resultatconsulta->DATOS->PROFESORES->PROFESOR as $professorgp)
            {
            $abreviatura=$professorgp['num_int_pr'];
            //$sql="SELECT COUNT(codi_prof_gp) FROM equivalencies WHERE nom_prof_gp='".$professorgp['nombre']."' AND codi_prof_gp='".$abreviatura."';";
            $sql="SELECT COUNT(codi_prof_gp) FROM equivalencies WHERE codi_prof_gp='".$abreviatura."';";
            $result=mysql_query($sql); if (!$result) {die(_ENPARELLAMENTS_GP2_.mysql_error());}
            $resultat = mysql_fetch_row($result);
            if ($resultat[0] == 0)
               {
               $sql="INSERT INTO equivalencies(nom_prof_gp,codi_prof_gp) VALUES('".$professorgp['nombre']."','".$abreviatura."');";
               $result=mysql_query($sql); if (!$result) {die(_ENPARELLAMENTS_GP2_.mysql_error());}
               }
            }
         }      
      else if((extreu_fase('app_horaris')==4) && (extreu_fase('professorat')==0))
         {
         $professorat=array();
         $professorat = extreuProfessoratCsv();
         foreach ($professorat as $professorgp)
            {
            $sql="INSERT INTO equivalencies(nom_prof_gp,codi_prof_gp) VALUES('".$professorgp."','".$professorgp."');";
            //echo "<br>>>>".$sql;
            $result=mysql_query($sql); if (!$result) {die(_ENPARELLAMENTS_GP2_.mysql_error());}
            }
         }
         
      }   
   }      




function select_professorat($exportsagaxml,$exporthorarixml)
	{

	$camps=array();
	recuperacampdedades($camps);

   // Desactivem tot el professorat
	$sql="UPDATE `professors` SET `activat`='N' WHERE activat='S';";
	$result=mysql_query($sql);
	if (!$result) {	die(_ERR_DEACT_PROF . mysql_error());}
	 
	// Tornem a activar l'administrador
	$sql="UPDATE `professors` SET `activat`='S' WHERE codi_professor='admin';";
	$result=mysql_query($sql);
	if (!$result) {	die(_ERR_ACT_ADMIN . mysql_error());}

   // Tornem a activar l'usuari vlino
	$sql="UPDATE `professors` SET `activat`='S' WHERE codi_professor='vlino';";
	$result=mysql_query($sql);
	if (!$result) {	die(_ERR_ACT_VLINO . mysql_error());}
   
   print("<form method=\"post\" action=\"./act_prof.php\" enctype=\"multipart/form-data\" id=\"profform\">");
   //echo extreu_fase('app_horaris');
   $resultatconsulta=simplexml_load_file($exportsagaxml);
   if (extreu_fase('app_horaris') < 4) {$resultatconsulta2=simplexml_load_file($exporthorarixml);}
   if ( !$resultatconsulta ) {echo "Carrega fallida Saga >> ".$exportsagaxml;}
   else 
      {
      if ((!$resultatconsulta2 ) AND (extreu_fase('app_horaris') < 4)) // No té en compte asc i saga sol
         {
         echo "Carrega fallida Horaris >> ".$exporthorarixml;
         }
      else
         { 	
         if (extreu_fase('app_horaris')!=5) {$columnes=7;}
         else  {$columnes=6;}
         echo "<br>Carregues  correctes !";
         print("<table align=\"center\">");
         print("<tr align = \"center\"><td colspan=\"".$columnes."\"><h2>Selecció de professorat</h2><br><font color=\"black\">S'esbrinarà quin professorat encara no ha estat donat d'alta o té les dades incompletes . ");
         print("<br><font color=\"red\"><b>És important</b></font> que seleccionis de la darrera columna, el professor corresponent per tal de fer l'equivalència<br> i que marquis el checkbox per crear l'usuari en el programa d'assistència.</font></td></tr>");
         print("<tr><td></td><td></td><td></td><td><td>Usuaris gassist</td>");
         if (extreu_fase('app_horaris')!=5) {print("<td>APP HORARIS</td>");}
         print("</td><td>Nou professor </td></tr>");
         $pos=1;
         foreach ($resultatconsulta->personal->personal as $professor)
            {
            print("<tr ");
            if ((($pos/5)%2)=="0") 
               {print("bgcolor=\"#ffbf6d\"");}
            // Imprimeix informacions de SAGA
            print("><td><input type=\"text\" name=\"nomprofsaga".$pos."\" value=\"".$professor['nom']."\" READONLY></td>");
            print("<td><input type=\"text\" name=\"cog1profsaga".$pos."\" value=\"".$professor['cognom1']."\" READONLY></td>");
            print("<td><input type=\"text\" name=\"cog2profsaga".$pos."\" value=\"".$professor['cognom2']."\" READONLY></td>");
            print("<td><input type=\"text\" name=\"idprofsaga".$pos."\" value=\"".$professor['id']."\" size=\"12\" HIDDEN></td>");



      // **********************   Imprimeix la informació de GASSIST

            // Mirem si ja té l'id de SAGA assignat
            $sql="SELECT A.idprofessors FROM professors A,contacte_professor B ";
            $sql.="WHERE ((A.idprofessors=B.id_professor) AND (B.id_tipus_contacte='".$camps['iden_ref']."') ";
            $sql.=" AND (B.Valor='".$professor['id']."'));";//echo $sql;
            $result=mysql_query($sql);
            if (!$result) {die(_ERR_LOOK_FOR_PROF . mysql_error());}
            $fila_prof=  mysql_fetch_row($result);
            $id_professor=$fila_prof[0];
            $present=mysql_num_rows($result);
            
            // Si està present, l'activa
            if ($present!=0)
               {
               $sql="UPDATE `professors` SET `activat`='S' WHERE idprofessors='".$id_professor."';";
               $result=mysql_query($sql);
               if (!$result) {	die(_ERR_ACT_PROF . mysql_error());}                      
               //echo $sql;
               }
               
            // Seleccionem tot el professorat de gassist per emplenar el dropdown
            if ((!extreu_fase('geisoft')) AND (extreu_fase('carrega')==0))
               {print("<td>Creació automàtica</td>");}
            else   
               {
               $sql="SELECT A.idprofessors, B.Valor FROM professors A,contacte_professor B ";
               $sql.="WHERE ((A.idprofessors=B.id_professor) AND (B.id_tipus_contacte='".$camps['nom_complet']."') ";
               //$sql.="AND (A.activat='S') AND (historic='N')) ORDER BY B.Valor";
               $sql.=") ORDER BY B.Valor";
               //echo $sql;
               $result=mysql_query($sql); if (!$result) {die(_ERR_INSERT_GROUPS_SUBJECTS_PUPIL . mysql_error());}
               print("<td><select name=\"id_gass".$pos."\" ");
               //if ($present>0) {print("DISABLED ");}
               print(">");
               print("<option value=\"\">No hi ha equivalència</option>");
               while ($fila=mysql_fetch_row($result))
                  {
                  $sql3="SELECT Valor FROM contacte_professor ";
                  $sql3.="WHERE ((id_tipus_contacte='".$camps[iden_ref]."') AND (Valor='".$professor[id]."') AND (id_professor='".$fila[0]."'));";
                  $result3=mysql_query($sql3); if (!$result3) {die(mysql_error());};$fila3=mysql_num_rows($result3);

//$myFile = "/home/vlino/Dropbox/public_html/import1617/import_v3.0/uploads/testFile.txt";
//$fh = fopen($myFile, 'a') or die("can't open file");
//$stringData = $fila[0].">>".$fila[1].">>".$fila3."\n";
//fwrite($fh, $stringData);
//fclose($fh);                  
                  print("<option value=\"".$fila[0]."\" ");
                  if ($fila3 == 1) 
                      {print("SELECTED ");
                      
                      
                      }
                  print(">".$fila[1]."</option>");
                  }
               print("</select></td>");

               }
      // **********************************  Imprimeix la informació de gpuntis

             if (extreu_fase('app_horaris')!=5)
               {
               // Comprovem si té equivalència a la base de dades ja posem l'emparellament
               if ($id_professor != '')
                    {
                    $sql="SELECT codi_prof_gp FROM equivalencies WHERE prof_ga='".$id_professor."';";
                    $result=mysql_query($sql); if (!$result) {die(_ERROR1_.mysql_error());}			
                    $prof_gp=mysql_result($result,0);
                    }
                else
                    {$prof_gp = '';}

               print("<td><select name=\"id_gp".$pos."\" ");
               print(">");
               print("<option value=\"\">No hi ha equivalència</option>");
               $sql="SELECT nom_prof_gp,codi_prof_gp FROM equivalencies WHERE nom_prof_gp!='' ORDER BY nom_prof_gp;";
               //echo $sql."<br>";
               $result=mysql_query($sql); if (!$result) {die(_ERROR2_.mysql_error());}
               while ($fila=mysql_fetch_row($result))
                  {
                  print("<option value=\"".$fila[1]."\" ");
                  if (!strcmp($prof_gp,$fila[1])) {print("SELECTED ");}
                  print(">".$fila[0]."</option>");
                  }
               print("</select></td>");
               
            // Checkbox d'alta

               if ($present == 0)
                    {   
                    print("<td><input type=\"checkbox\" name=\"alta".$pos."\" ");
                    if (!extreu_fase('geisoft'))
                       {
                       if (extreu_fase('carrega')==0)
                          {print("CHECKED ");}
                       }
                    else 
                       {
                       //Està desactivat si utilitzo gestio centralitzada
                       print("DISABLED ");
                       }   

                    print("> Crea'l</td>");
                    }
                print("</tr> ");
                }
            else 
                {
                if ($present == 0)
                    {   
                    print("<td><input type=\"checkbox\" name=\"alta".$pos."\" ");
                    if (!extreu_fase('geisoft'))
                       {
                       if (extreu_fase('carrega')==0)
                          {print("CHECKED ");}
                       }
                    else 
                       {
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
         if ($pos!="0")
            {
            print("<tr><td align=\"center\" colspan=\"8\"><input name=\"boton\" type=\"submit\" id=\"boton\" value=\"Enviar\">");
            print("<tr><td align=\"center\" colspan=\"8\"><input type=\"text\" name=\"recompte\" value=\"".$pos."\" HIDDEN ></td></tr>");
            print("</table>");
            print("</form>");
            }
         else
            {
            introduir_fase('professorat',1);
            $page = "./menu.php";
            $sec="0";
            header("Refresh: $sec; url=$page");
            }
         
         }
      }
   }   

function select_alumnat($exportsagaxml,$fase)
	{
	
   	$camps=array();
		recuperacampdedades($camps);

		// Desactivem tot l'alumnat que està activat a la base de dades. No els pasem de moment a l'històric
		// ja que volem que al desplegable apareguin
		$sql="UPDATE `alumnes` SET `activat`='N' WHERE activat='S';";
		$result=mysql_query($sql);
		if (!$result) {	die(_ERR_DEACT_ALUM . mysql_error());}
			
		// Comprovem i actualitzem el l'alumnat
		
		$resultatconsulta=simplexml_load_file($exportsagaxml);
		if ( !$resultatconsulta ) {echo "Carrega fallida";}
		
		else 
			{
			echo "<br>Carrega correcta";
			
			foreach ($resultatconsulta->alumnes->alumne as $alumne)
				{
				// Cerca un per un quin alumne ja té l'id correcte
				// Si el localitza, l'activa
				$sql="SELECT count(*) FROM alumnes ";
                                $sql.="WHERE codi_alumnes_saga='".$alumne['id']."';";
				//echo "<br>".$sql."<br>";
				$result=mysql_query($sql);
				if (!$result) {	die(_ERR_LOOK_FOR_ALUM . mysql_error());}
				$fila=  mysql_fetch_row($result);$present=$fila[0];
				if ($present)
					{
					// Activem l'alumnat en questió i si és un alumne que era historic , el torna a habilitar
					$sql="UPDATE `alumnes` SET `activat`='S',historic='N'  WHERE codi_alumnes_saga='".$alumne['id']."' ;";
					//echo "<br>".$sql;
					$result=mysql_query($sql);
					if (!$result) {	die(_ERR_ACT_ALUM1 . mysql_error());}
               
                                       // 16/17. Li afegimn la data de naixement
                                       $sql = "SELECT id_alumne FROM contacte_alumne WHERE id_tipus_contacte = '".$camps[iden_ref]."' AND Valor = '".$alumne[id]."';";
                                       $result=mysql_query($sql);
                                       if (!$result) {	die(_ERR_ACT_ALUM2 . mysql_error());}
                                       $fila=mysql_fetch_row($result);$id_saga=$fila[0];

                                       $sql = "INSERT INTO contacte_alumne(id_alumne,id_tipus_contacte,Valor) ";
                                       $sql.= "VALUES ('".$id_saga."','".$camps[data_naixement]."','".$alumne[datanaixement]."')";
                                       $result=mysql_query($sql);
                                       if (!$result) {	die(_ERR_ACT_ALUM3 . mysql_error());}
                                       // Final afegir data naixement alumnes antics              

               
               
					}
				}
			// Ja no mostra el que s'ha activat per tenir l'id de saga correcte
			// Apareix:
			// Alumnat nou al centre
			// Alumnat que l'any anterior no se li va posar bé l'id
			// Alumnat que torna després de haver anat a l'històric
			
			print("<form method=\"post\" action=\"./act_alum.php\" enctype=\"multipart/form-data\" id=\"profform\">");
			
			print("<table align = 'center'>");
			print("<tr><td colspan=\"7\"><h2>Selecció de l'alumnat</h2><br><font color=\"white\">S'esbrinarà quin alumnat encara no ha estat donat d'alta o té les dades incompletes . ");
			print("</td></tr>");
			print("<tr align=\"center\" bgcolor=\"orange\" ><td>Nom(S)</td><td>Cognom1(S)</td><td>Cognom2(S)</td><td>Codi a Saga</td><td></td>");
			print("<td>Alta nou <br><input type=\"checkbox\" onclick=\"marcar(this);\" /> </td>");
         
         //if ($fase!="1") {print("<td colspan=\"2\"></td></tr>");}
         //else {print("<td> Alumnes desactivats</td><td>Actualització</td></tr>");}
			
         $pos=1;
			foreach ($resultatconsulta->alumnes->alumne as $alumne)
				{
				// Cerca l'alumne per identificador
				// Si no hi és apareix al llistat
				$sql="SELECT COUNT(*) FROM alumnes ";
				$sql.="WHERE codi_alumnes_saga='".$alumne['id']."';";
				$result=mysql_query($sql); if (!$result) {	die(mysql_error());}
				$present=mysql_result($result,0);
				//echo "<br>".$sql." >> ".$present;
				if ($present==0)
					{
					print("<tr ");
					if ((($pos/5)%2)=="0") 
						{print("bgcolor=\"orange\"");}
					print("><td><input type=\"text\" name=\"nomalumsaga".$pos."\" value=\"".$alumne['nom']."\" READONLY></td>");
					print("<td><input type=\"text\" name=\"cog1alumsaga".$pos."\" value=\"".$alumne['cognom1']."\" READONLY></td>");
					print("<td><input type=\"text\" name=\"cog2alumsaga".$pos."\" value=\"".$alumne['cognom2']."\" READONLY></td>");
					print("<td><input type=\"text\" name=\"idalumsaga".$pos."\" value=\"".$alumne['id']."\" size=\"12\" READONLY></td>");
					print("<td><input type=\"text\" name=\"naixement".$pos."\" value=\"".$alumne['datanaixement']."\" size=\"12\" HIDDEN></td>");
					print("<td><input type=\"checkbox\" value=\"1\" name=\"alta".$pos."\" CHECKED ");
					print("> Alta</td>");
//					if ($fase!=0)
//						{				
//                  
//                  // Consulta per emplenar el desplegable
//                  $sql="SELECT A.idalumnes, B.Valor FROM alumnes A,contacte_alumne B WHERE ((A.idalumnes=B.id_alumne) AND (B.id_tipus_contacte='1') ";
//                  $sql.="AND (A.activat='N') AND (historic='N')) order by B.Valor;";
//                  //echo $sql;
//                  $result=mysql_query($sql); if (!$result) {	die(mysql_error());}
//                  
//                  
//                  print("<td><select name=\"id_gass".$pos."\">");
//						print("<option value=\"\"></option>");
//						while ($fila=mysql_fetch_row($result))
//							{
//							print("<option value=\"".$fila[0]."\">".$fila[1]."</option>");
//							}
//						print("</select></td>");
//						print("<td><input type=\"checkbox\" name=\"actualitzacio".$pos."\" ");
//						print(">Actualització</td>");
//						}
					print("</tr> ");
					$pos++;
					}
				}
			$pos--;
			if ($pos!=0)
				{
				print("<tr><td align=\"center\" colspan=\"7\"><input name=\"boton\" type=\"submit\" id=\"boton\" value=\"Enviar\"></td></tr>");
				print("<tr><td align=\"center\" colspan=\"7\"><input type=\"text\" name=\"recompte\" value=\"".$pos."\" HIDDEN ></td></tr>");
				print("</table>");
				print("</form>");
				mysql_close($conexion);
				}
			else
				{
				introduir_fase('families',1);
				introduir_fase('alumnat',1);
				$page = "./menu.php";
				$sec="0";
				header("Refresh: $sec; url=$page");
				}
			}
		}   
   
function actualitzar_alumnat($exportsagaxml)
	{
	
   	$camps=array();
        recuperacampdedades($camps);

        // Desactivem tot l'alumnat que està activat a la base de dades. No els pasem de moment a l'històric
        // ja que volem que al desplegable apareguin
        $sql="UPDATE `alumnes` SET `activat`='N' WHERE activat='S';";
        $result=mysql_query($sql);
        if (!$result) {	die(_ERR_DEACT_ALUM . mysql_error());}

        // Comprovem i actualitzem el l'alumnat

        $resultatconsulta=simplexml_load_file($exportsagaxml);
        if ( !$resultatconsulta ) {echo "Carrega fallida";}

        else 
                {
                
            foreach ($resultatconsulta->alumnes->alumne as $alumne)
                        {
                        // Cerca un per un quin alumne ja té l'id correcte
                        // Si el localitza, l'activa
                        $sql="SELECT count(*) FROM alumnes ";
                        $sql.="WHERE codi_alumnes_saga='".$alumne['id']."';";
                        //echo "<br>".$sql."<br>";
                        $result=mysql_query($sql);
                        if (!$result) {	die(_ERR_LOOK_FOR_ALUM . mysql_error());}
                        $fila=  mysql_fetch_row($result);$present=$fila[0];
                        if ($present)
                                {
                                // Activem l'alumnat en questió i si és un alumne que era historic , el torna a habilitar
                                $sql="UPDATE `alumnes` SET `activat`='S',historic='N'  WHERE codi_alumnes_saga='".$alumne['id']."' ;";
                                //echo "<br>".$sql;
                                $result=mysql_query($sql);
                                if (!$result) {	die(_ERR_ACT_ALUM1 . mysql_error());}

                               // 16/17. Li afegimn la data de naixement
                               $sql = "SELECT id_alumne FROM contacte_alumne WHERE id_tipus_contacte = '".$camps[iden_ref]."' AND Valor = '".$alumne[id]."';";
                               $result=mysql_query($sql);
                                                        if (!$result) {	die(_ERR_ACT_ALUM2 . mysql_error());}
                               $fila=mysql_fetch_row($result);$id_saga=$fila[0];

                               $sql = "INSERT INTO contacte_alumne(id_alumne,id_tipus_contacte,Valor) ";
                               $sql.= "VALUES ('".$id_saga."','".$camps[data_naixement]."','".$alumne[datanaixement]."')";
                               $result=mysql_query($sql);
                               if (!$result) {	die(_ERR_ACT_ALUM3 . mysql_error());}
                               // Final afegir data naixement alumnes antics              



                                }
                        }
                // Ja no mostra el que s'ha activat per tenir l'id de saga correcte
                // Apareix:
                // Alumnat nou al centre
                // Alumnat que l'any anterior no se li va posar bé l'id
                // Alumnat que torna després de haver anat a l'històric

                print("<form method=\"post\" action=\"./act_alum_SAGA.php\" enctype=\"multipart/form-data\" id=\"profform\">");

                print("<table align = 'center'>");
                print("<tr><td colspan=\"8\"><h2>Selecció de l'alumnat</h2><br><font color=\"white\">S'esbrinarà quin alumnat encara no ha estat donat d'alta o té les dades incompletes . ");
                print("</td></tr>");
                print("<tr align=\"center\" bgcolor=\"orange\" ><td>Nom(S)</td><td>Cognom1(S)</td><td>Cognom2(S)</td><td>Codi a Saga</td><td></td>");
                print("<td>Matricular <br><input type=\"checkbox\" onclick=\"marcar(this);\" /> </td><td>grups Saga on està assignat</td><td>Grup on matricular</td>");
         
         //if ($fase!="1") {print("<td colspan=\"2\"></td></tr>");}
         //else {print("<td> Alumnes desactivats</td><td>Actualització</td></tr>");}
			
         $pos=1;
                foreach ($resultatconsulta->alumnes->alumne as $alumne)
                        {
                        // Cerca l'alumne per identificador
                        // Si no hi és apareix al llistat
                        $sql="SELECT COUNT(*) FROM alumnes ";
                        $sql.="WHERE codi_alumnes_saga='".$alumne['id']."';";
                        $result=mysql_query($sql); if (!$result) {	die(mysql_error());}
                        $present=mysql_result($result,0);
                        //echo "<br>".$sql." >> ".$present;
                        if ($present==0)
                                {
                                print("<tr ");
                                if ((($pos/5)%2)=="0") 
                                        {print("bgcolor=\"orange\"");}
                                print("><td><input type=\"text\" name=\"nomalumsaga".$pos."\" value=\"".$alumne['nom']."\" READONLY></td>");
                                print("<td><input type=\"text\" name=\"cog1alumsaga".$pos."\" value=\"".$alumne['cognom1']."\" READONLY></td>");
                                print("<td><input type=\"text\" name=\"cog2alumsaga".$pos."\" value=\"".$alumne['cognom2']."\" READONLY></td>");
                                print("<td><input type=\"text\" name=\"idalumsaga".$pos."\" value=\"".$alumne['id']."\" size=\"12\" READONLY></td>");
                                print("<td><input type=\"text\" name=\"naixement".$pos."\" value=\"".$alumne['datanaixement']."\" size=\"12\" HIDDEN></td>");
                                print("<td><input type=\"checkbox\" value=\"1\" name=\"alta".$pos."\" CHECKED ");
                                print("> Matricula'l</td>");
                                $grup_inscrit=extreuGrupsSaga($exportsagaxml,$alumne['id']);
                                print("<td><input type=\"text\" name=\"grupalumsaga".$pos."\" value=\"".$grup_inscrit."\" READONLY></td>");

                                print("<td><select name=\"id_grup_".$pos."\" ");
                                print(">");
                                print("<option value=\"0\">Cap equivalència</option>");
                                $sql="SELECT idgrups,nom FROM grups WHERE 1 ORDER BY nom; ";
                                $result=mysql_query($sql); if (!$result) {	die(mysql_error());}
                                while ($fila=mysql_fetch_row($result))
                                    {
                                    print("<option value=\"".$fila[0]."\" ");
                                    print(">".$fila[1]."</option>");
                                    }
                                print("</select></td>");
                                print("</tr> ");
                                $pos++;
                                }
                        }
                $pos--;
                if ($pos!=0)
                        {
                        print("<tr><td align=\"center\" colspan=\"7\"><input name=\"boton\" type=\"submit\" id=\"boton\" value=\"Enviar\"></td></tr>");
                        print("<tr><td align=\"center\" colspan=\"7\"><input type=\"text\" name=\"recompte\" value=\"".$pos."\" HIDDEN ></td></tr>");
                        print("</table>");
                        print("</form>");
                        mysql_close($conexion);
                        }
                else
                        {
                        introduir_fase('families',1);
                        introduir_fase('alumnat',1);
                        $page = "./menu.php";
                        $sec="0";
                        header("Refresh: $sec; url=$page");
                        }
			}
		}


function extreuGrupsSaga($exportsagaxml,$id_alumne)
    {
    $string_grups = "";
    $resultatconsulta=simplexml_load_file($exportsagaxml);
    if ( !$resultatconsulta ) {echo "Carrega fallida";}
    else 
        {
        //echo "<br>Carrega correcta";
        foreach ($resultatconsulta-> grups -> grup  as $grup)    
            {
            foreach ($grup -> alumnes -> alumne as $alumne)
                {
                if (!strcmp($alumne['id'],$id_alumne)) 
                    {
                    $string_grups= $string_grups.$grup['nom']."/";
                    
                    }
                }
            
            }
        }    
    return $string_grups;
    }                
                
                
// Aquesta funció és per regenerar tota la informació de les families en funció del contingut de saga
// Tot el contingut anterior s'esborrarà

function carrega_dades_families($id,$id_saga)
		{
		include("../config.php");
		//include("../funcions/funcions_generals.php");
			
		$dades=array();
		$exportsagaxml=$_SESSION['upload_saga'];
		$resultatconsulta=simplexml_load_file($exportsagaxml);
		if ( !$resultatconsulta ) {echo "Carrega fallida";}
		else 
			{
			//echo "<br>Carrega inicial correcta<br>";
			// Fem el recorrgut alumne per alumne
			foreach ($resultatconsulta->alumnes->alumne as $alumne)
				{
				//Inicialitzem la matriu de dades
				for ($j=0;$j<14;$j++) {$dades[$j]="";}
				//echo "<br>".$alumne[id]." >> ".$id_saga;
				if ($alumne['id']==$id_saga)
					{
					//echo "<br>Dins";
					// Li exteriem l'id de gassist
					$dades[0]=$id;
					// Comprovem si es tracta d'un alumne que tot i estar al SAGA encara no s'ha donat d'alta 
					// per no haver netejat tots els alumnes de la llista
					if ($dades[0]!="")
						{
						$correu="";$telefon="";$movil_sms="";
						foreach ($alumne->contacte as $contacte)
							{
							if ($contacte[tipus]=="EMAIL")
								{
								$correu=$correu." / ".$contacte['contacte'];
								}
							
							if ($contacte['tipus']=="TELEFON")
								{// Genera cadena de telefons
								$telefon=$telefon." / ".$contacte['contacte'];
								if ($movil_sms=="") {$movil_sms=esmovilsms($contacte['contacte']);}
								}
							
							}
						$dades[10]=$correu;
						$dades[9]=$telefon;
						$dades[11]=$movil_sms;
						//echo "<br>>>".$telefon;
						// Extreiem quants tutors  legals té l'alumne
						$nomb_tutors=0;
						if ($alumne['tutor1']!="") {$nomb_tutors++;}
						if ($alumne['tutor2']!="") {$nomb_tutors++;}
						//echo "EL NOMBRE DE TUTORS ÉS:".$nomb_tutors; 
						//echo "<br>";		
						for ($k=1;$k<=$nomb_tutors;$k++)
							{
							$tutor_mod="tutor".$k;
							//echo "<br>".$tutor_mod;
							if ($alumne[$tutor_mod]!="")
								{
								$id_tutor=$alumne[$tutor_mod];
								//echo "<br>".$id_tutor;
								
								//Comprova si hi ha alguna família que ja té aquest tutor
								// Si hi ha un germà, surt del bucle
								$germa=hi_ha_germa($id_tutor);
								//echo "<br>Hi ha germà... ".$germa;
								if ($germa=="") 
									{
									$resultatconsulta3=simplexml_load_file($exportsagaxml);
									if ( !$resultatconsulta3 ) {echo "Carrega fallida";}
									else 
										{
										foreach ($resultatconsulta3->{'tutors-legals'}->{'tutor-legal'} as $tutor)
											{
											if (!strcmp($tutor['id'],$id_tutor))
												{
												//echo "<br>".$nomb_tutors;
												if ($k=="1")
													{
													$dades[12]=$id_tutor;
													$dades[1]=$tutor['nom'];$dades[2]=$tutor['cognom1'];$dades[3]=$tutor['cognom2'];
													//echo "<br>".$dades[2]." >> ".$dades[3]." >> ".$dades[4];
													$dades[7]=$tutor['adreca'];$dades[8]=$tutor['nomlocalitat'];
													}
												else
													{$dades[13]=$id_tutor;$dades[4]=$tutor['nom'];$dades[5]=$tutor['cognom1'];$dades[6]=$tutor['cognom2'];
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
						crea_families($id_families);
						
						$camps=array();
						recuperacampdedades($camps);
						
						// Segon si té o no germans es modifica la sql	
						$sql="INSERT INTO `alumnes_families`(idalumnes,idfamilies) ";
						$sql.="VALUES ";
						if ($germa=="")
							{$sql.="('".$dades[0]."','".$id_families."'); ";}
						else
							{$sql.="('".$dades[0]."','".$germa."'); ";}
						//echo $sql;echo "<br>";
						$result=mysql_query($sql);
						if (!$result) 
							{
							die(_ERR_INSERT_FAMILY.(2). mysql_error());
							}		
						
						/*
						0>id gassist // 1>nom_pare // 2>cognoms1_pare // 3>cognom2_pare // 4>Nom_mare
						5>cognom1_mare // 6>cognom2_mare // 7>adreça // 8>localitat // 9>telefon
						10>correu // 11>mobil // 12>id_tutor1 // 13>id_tutor2
						*/
						
						//Inserim les dades de contacte de la família si no té german que ja s'hagin donat s'alta
						
						for($l=1;$l<=13;$l++)
							{$dades[$l]=neteja_apostrofs($dades[$l]);}
											
						if ($germa=="")
							{
							$nom_complet_pare=$dades[1]." ".$dades[2]." ".$dades[3];
							$nom_complet_mare=$dades[4]." ".$dades[5]." ".$dades[6];
							
							$sql="INSERT INTO `contacte_families`(id_families,id_tipus_contacte,Valor) ";
							$sql.="VALUES ";
							$sql.="('".$id_families."','".$camps["nom_pare"]."','".$dades[1]."') ";
							$sql.=",('".$id_families."','".$camps["cognom1_pare"]."','".$dades[2]."') ";
							$sql.=",('".$id_families."','".$camps["cognom2_pare"]."','".$dades[3]."') ";
							$sql.=",('".$id_families."','".$camps["nom_complet"]."','".$nom_complet_pare."') ";
							if ($nomb_tutors=="2")
								{
								$sql.=",('".$id_families."','".$camps["nom_mare"]."','".$dades[4]."') ";
								$sql.=",('".$id_families."','".$camps["cognom1_mare"]."','".$dades[5]."') ";
								$sql.=",('".$id_families."','".$camps["cognom2_mare"]."','".$dades[6]."') ";
								$sql.=",('".$id_families."','".$camps["nom_complet"]."','".$nom_complet_mare."') ";
								}
							$sql.=",('".$id_families."','".$camps["adreca"]."','".$dades[7]."') ";
							$sql.=",('".$id_families."','".$camps["nom_municipi"]."','".$dades[8]."') ";
							$sql.=",('".$id_families."','".$camps["telefon"]."','".$dades[9]."') ";
							$sql.=",('".$id_families."','".$camps["email1"]."','".$dades[10]."') ";
							$sql.=",('".$id_families."','".$camps["mobil_sms"]."','".$dades[11]."'); ";
//							$sql.=",('".$id_families."','".$camps["iden_ref"]."','".$dades[12]."') ";
//							$sql.=",('".$id_families."','".$camps["iden_ref"]."','".$dades[13]."'); ";
							//echo $sql."<br>";
							$result=mysql_query($sql);
							if (!$result) 
								{
								die(_ERROR_INSERT_FAMILY.(3). mysql_error());
								}
							}
						}
					//break; // Com que ja l'ha trobat, surt de la cerca del foreach
					}// Comprovació si s'ha de carregar o descartar
				}//foreach principal per cercar l'alumne
			}
		//mysql_close($conexion);
	}

function hi_ha_germa($id_tutor)
	{
	include("../config.php");
	
	//Insereix l'autoincrement que serà l'identificador unívoc de les famílies
	$sql="SELECT id_families FROM contacte_families WHERE Valor='".$id_tutor."' LIMIT 1;";
	//echo $sql;echo"<br>";
	$result=mysql_query($sql);
	if (!$result) 
		{die(_ERR_LOOKFOR_FAMILY . mysql_error());}	
	$germa=mysql_result($result,0);
	return $germa;
	}



function crea_families(&$idfamilies)
	{
	include("../config.php");
	
   //Insereix l'autoincrement que serà l'identificador unívoc de les famílies
	$sql="INSERT INTO `families`(idfamilies) VALUE (null);";
	//echo $sql;echo"<br>";
	$result=mysql_query($sql);
	if (!$result) 
		{die(_ERR_INSERT_FAMILY . mysql_error());}
	else
		{
		$sql="SELECT MAX(idfamilies) AS Darrer FROM families;";
		//echo $sql;echo"<br>";
		$result=mysql_query($sql);
		if (!$result) 
			{die(_ERR_SELECT.(1). mysql_error());}
		$idfamilies=mysql_result($result,0);
		}
	}

function profe_ja_existeix($user)
	{
	
	$camps=array();
	recuperacampdedades($camps);
	
	$sql="SELECT COUNT(*) FROM contacte_professor WHERE id_tipus_contacte='".$camps[login]."' AND Valor='".$user."';";
	//echo $sql;echo"<br>";
	$result=mysql_query($sql);
	return mysql_result($result,0);
	if (!$result) 
		{die(_ERR_SELECT_DUPLICATED_TEACHER . mysql_error());}	
	}   

        


function genera(&$user,&$pass,$nom,$cognom1,$cognom2)
	
	{
	//include("../config.php");
	
	$existeix=1;
	while ($existeix)
		{
		$user= limpia_cadena($user,$nom,$cognom1,$cognom2);
		$user=substr($user, 0, 12);
		$existeix=profe_ja_existeix($user);
		//echo "<br>      ",$existeix."<br>";
		}
	$pass=randomPassword();
	//echo $user."<br>";
	}

function randomPassword() 
	{
    $alphabet = "abcdefghijklmnopqrstuwxyzABCDEFGHIJKLMNOPQRSTUWXYZ0123456789";
    $pass = array(); //remember to declare $pass as an array
    $alphaLength = strlen($alphabet) - 1; //put the length -1 in cache
    for ($i = 0; $i < 5; $i++) {
        $n = rand(0, $alphaLength);
        $pass[] = $alphabet[$n];
    }
    return implode($pass); //turn the array into a string
	}

function randomPassword_numbers() 
	{
    $alphabet = "0123456789";
    $pass = array(); //remember to declare $pass as an array
    $alphaLength = strlen($alphabet) - 1; //put the length -1 in cache
    for ($i = 0; $i < 5; $i++) {
        $n = rand(0, $alphaLength);
        $pass[] = $alphabet[$n];
    }
    return implode($pass); //turn the array into a string
	}
   
function limpia_cadena(&$cadena,$nom,$cognom1,$cognom2)
	{
	$nom=substr($nom, 0, 1);
	if (strlen($cognom1) > 6) {$cognom1=substr($cognom1, 0, 6);}
	$cadena=$nom.$cognom1;
	if (strlen($cadena) < 6)
		{$cadena=$cadena.substr($cognom2, 0, 3);}
	$cadena=mb_strtolower($cadena, 'UTF-8');
	$cadena = str_replace("ñ","n",$cadena);
	//$string=explode(' ',$cadena);
	//$cadena="";
	//foreach ($string as $word)
	//	{$word=substr($word, 0, 3);
	//	$cadena=$cadena."".$word;
	//	}
	//echo "cadena abans.. ".$cadena;
	$cadena = ereg_replace("[áàäâ]","a",$cadena);
	$cadena = ereg_replace("[éèëê]","e",$cadena);
	$cadena = ereg_replace("[íìïî]","i",$cadena);
	$cadena = ereg_replace("[óòöô]","o",$cadena);
	$cadena = ereg_replace("[úùüû]","u",$cadena);
	$cadena = str_replace("ç","c",$cadena);
	//$cadena = str_replace("ñ","n",$cadena);
	$cadena = str_replace('\'', '', $cadena);
	$cadena = str_replace(' ', '', $cadena);
	//echo "cadena segon.. ".$cadena;
	$parametres=10-strlen($cadena);
	$aleat=randomPassword_numbers();
	$aleat=substr($aleat, 0, $parametres);
	$cadena=$cadena."".$aleat;
	//echo "cadena abans de sortir.. ".$cadena;
	return $cadena;
	}   

function limpia_telefonos($cadena)
	{
    $cadena = str_replace('-', '', $cadena);
    $cadena = str_replace(' ', '', $cadena);
    $cadena = str_replace('.', '', $cadena);
    return $cadena;
	}

function esmovilsms($telefon)
	
	{
	//echo "dins de esmovilsms<br>";
	$movil_sms = "no_mobil";
	$assignat = 0;
	$telf=limpia_telefonos($telefon);
	//echo "<br><br> ---- ".$telf;
	if(substr($telf, 3, 1)=="6")
		{
		$movil_sms=substr(substr($telf,3),0,9);
		$assignat=1;
		//echo "<br>primera ".$movil_sms;
		//return $movil_sms;
		}
	if((substr($telf, 0, 1)=="6") OR (substr($telf, 0, 1)=="7"))
		{
		$movil_sms=$telf;
		$assignat=1;
		//echo "<br>primera ".$movil_sms;
		//return $movil_sms;
		}
	if ($assignat==1) {return $movil_sms;}
	}

   
   
// @@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@2
// 				ASSISGNACIÓ ALUMNES A GRUPS MATÈRIA
// @@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@2

	
function select_grups_per_matricular($exportsagaxml)
    {
    include("../config.php");
    $resultatconsulta=simplexml_load_file($exportsagaxml);
    if (!$resultatconsulta) {echo "Càrrega fallida.";}
    else
        {
    
        print("<form method=\"post\" action=\"./assignacions_act.php\" enctype=\"multipart/form-data\" id=\"profform\">");

        print("<table align=\"center\" width=\"60%\">");
        print("<tr><td align=\"center\" colspan=\"3\"><h3>INSTRUCCIONS</h3></td></tr>");
        print("<tr><td align=\"center\" colspan=\"3\">Si selecciones un grup de SAGA, tots els alumnes que consten al saga com alumnes ");
        print("d'aquest grup es matricularan a totes les matèries vinculades al grup. <br>   ");
        print("Tingues present que els professors poden inscriure els alumnes als seus grups i també disposes de l'opció ");
        print("de l'automatrícula. <br> <b>Clicant en els enllaços del part superior pots veure els alumnes que consten a cada grup de saga</b>");
        print("<font color =\"red\"><br>Si es tracta d'una segona càrrega amb un segon fitxer d'horaris, no tornis a matricular esls alumnes ja matriculats</font>");
        print("<tr align=\"center\" bgcolor=\"#ffbf6d\" ><td>Grup app horaris </td><td></td><td>Grups SAGA</td></tr>");
        $pos=1;
        //echo "<br>".$sql;
        $sql="SELECT idgrups,nom FROM grups WHERE 1 ORDER BY nom; ";
        $result=mysql_query($sql); if (!$result) {	die(mysql_error());}
        while ($fila=mysql_fetch_row($result))
            {
            print("<tr ");
            if ((($pos/5)%2)=="0") 
                {print("bgcolor=\"#3f3c3c\"");}
            print("><td><input type=\"text\" name=\"nom_grup_".$pos."\" value=\"".$fila[1]."\" SIZE=\"50\" READONLY></td>");
            print("<td><input type=\"text\" name=\"id_grup_".$pos."\" value=\"".$fila[0]."\" SIZE=\"6\" HIDDEN></td>");

            print("<td><select name=\"id_grup_saga_".$pos."\" ");
            print(">");
            print("<option value=\"0\">Cap equivalència</option>");
            foreach ( $resultatconsulta -> grups -> grup as $grupssaga)
                {
                print("<option value=\"".$grupssaga[id]."\">(".$grupssaga[codi].") ".$grupssaga[nom]."</option>");
                }
            print("</select></td>");
            
          print("</tr> ");
          $pos++;

                    }	
        $pos--;
        print("<tr><td align=\"center\" colspan=\"3\"><input name=\"boton\" type=\"submit\" id=\"boton\" value=\"Enviar\">");
        print("&nbsp&nbsp<input type=button onClick=\"location.href='./menu.php'\" value=\"Torna al menú!\" ></td></tr>");
        print("<tr><td align=\"center\" colspan=\"3\"><input type=\"text\" name=\"recompte\" value=\"".$pos."\" HIDDEN ></td></tr>");
        print("</table>");
        print("</form>");
        }	

    }

function select_grups_per_matricular_cali($exportsagaxml)
    {
    include("../config.php");
    $resultatconsulta=simplexml_load_file($exportsagaxml);
    if (!$resultatconsulta) {echo "Càrrega fallida.";}
    else
        {
    
        print("<form method=\"post\" action=\"./assignacions_act.php\" enctype=\"multipart/form-data\" id=\"profform\">");

        print("<table align=\"center\" width=\"60%\">");
        print("<tr><td align=\"center\" colspan=\"3\"><h3>INSTRUCCIONS</h3></td></tr>");
        print("<tr><td align=\"center\" colspan=\"3\">Comprova els emparellaments ");

        print("<tr align=\"center\" bgcolor=\"#ffbf6d\" ><td>Grup app horaris </td><td></td><td>Grups SAGA</td></tr>");
        $pos=1;
        //echo "<br>".$sql;
        $sql="SELECT idgrups,nom FROM grups WHERE 1 ORDER BY nom; ";
        $result=mysql_query($sql); if (!$result) {	die(mysql_error());}
        while ($fila=mysql_fetch_row($result))
            {
            print("<tr ");
            if ((($pos/5)%2)=="0") 
                {print("bgcolor=\"#3f3c3c\"");}
            print("><td><input type=\"text\" name=\"nom_grup_".$pos."\" value=\"".$fila[1]."\" SIZE=\"50\" READONLY></td>");
            print("<td><input type=\"text\" name=\"id_grup_".$pos."\" value=\"".$fila[0]."\" SIZE=\"6\" HIDDEN></td>");

            print("<td><select name=\"id_grup_saga_".$pos."\" ");
            print(">");
            print("<option value=\"0\">Cap equivalència</option>");
            foreach ( $resultatconsulta -> grups -> grup as $grupssaga)
                {
                print("<option value=\"".$grupssaga[id]."\" ");
                $arraygrup1 = explode(" ",$grupssaga[nom]);
                $arraygrup2 = explode("-",$arraygrup1[0]);
                $grupssaga2 = $arraygrup2[0].$arraygrup2[1];
                if ($grupssaga2 == $fila[1]) print(" SELECTED ");
                print(">(".$grupssaga[codi].") ".$grupssaga[nom]."</option>");
                }
            print("</select></td>");
            
          print("</tr> ");
          $pos++;

                    }	
        $pos--;
        print("<tr><td align=\"center\" colspan=\"3\"><input name=\"boton\" type=\"submit\" id=\"boton\" value=\"Enviar\">");
        print("&nbsp&nbsp<input type=button onClick=\"location.href='./menu.php'\" value=\"Torna al menú!\" ></td></tr>");
        print("<tr><td align=\"center\" colspan=\"3\"><input type=\"text\" name=\"recompte\" value=\"".$pos."\" HIDDEN ></td></tr>");
        print("</table>");
        print("</form>");
        }	

    }    
    
    
function mostra_grups($exportsagaxml)
    {
    $resultatconsulta=simplexml_load_file($exportsagaxml);
    $resultatconsulta2=simplexml_load_file($exportsagaxml);  
    
    print('<table border ="0" align="center"');
    print("<tr><td colspan = \"5\"><h3>Alumnes de cada grup dels que consten al fitxer de SAGA</h3></td></tr>");
    print('<tr>');
    $i=0;
    foreach ($resultatconsulta -> grups -> grup as $grup)
        {
        //print('<td><a <input type="button value="'.$grup[nom].'" onClick="ccalert1('.$grup[id].')">fsdgdsf</td>');
        //print('<td><a id="myLink" title="Visualitza els alumnes del grup" href="alumnes_grup.php?idgrup='.$grup[id].'" >'.$grup[nom].'</a></td>');
        print('<td><a href="javascript:window.open(\'alumnes_grup.php?idgrup='.$grup[id].'\',\'mywindowtitle\',\'width=400,height=600\')">'.$grup[nom].'</a></td>');
        $i++;
        if ($i%5 == 0) {print('</tr><tr>');}
        
        }    
    
    
    
    }

   
   ?>