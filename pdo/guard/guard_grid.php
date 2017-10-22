<?php
   session_start();
   require_once('../bbdd/connect.php');
   require_once('../func/constants.php');
   require_once('../func/generic.php');
   require_once('../func/seguretat.php');
   $db->exec("set names utf8");
   
   unset($_SESSION['hora_guardia']);
   $hora = strtotime(isset($_REQUEST['hora']) ? $_REQUEST['hora'] : date('H:i'));
   if (isset($_REQUEST['ad'])) {
       $_SESSION['hora_guardia'] = date("H:i", strtotime('+1 minute', $hora));
   }
   
   $idprofessors  = isset($_SESSION['professor']) ? $_SESSION['professor'] : 0;
   $strNoCache = "";
   
   if ($_REQUEST['act'] == 1) {
   	if (validEntryLogProfessor($db,$idprofessors,TIPUS_ACCIO_ENTRAGUARDIA)) {
            insertaLogProfessor($db,$idprofessors,TIPUS_ACCIO_ENTRAGUARDIA);
   	}
   }
?>
    <div id="tt" class="easyui-tabs" border="true" style="width:auto;height:auto">
        <div id="classes_actual" title="Qui est&agrave; en classe" style="padding:5px">
            <?php
            	include('../ass_servei_adm/quies_enclasse.php');
            ?>
        </div>
        
        <div id="guardies_actual" title="Qui est&agrave; de gu&agrave;rdia" style="padding:5px">
            <?php
            	include('../ass_servei_adm/quies_enguardia.php');
            ?>
        </div>      
        
        <!--
        <div id="guardies_actual" title="Professorat absent" style="padding:5px">
            
            	//include('../ass_servei_adm/professorat_absent.php');
            
        </div>
        -->
        
    </div>
    
    <script type="text/javascript"> 
	
	$('#tt').tabs({
	  plain:true,
	  onSelect: function(title){
	  	   $("#llistaClasses").load("../ass_servei_adm/llista_classes_actuals.php?rf=1000");
		   $("#llistaGuardies").load("../ass_servei_adm/llista_guardies_actuals.php");
		   //$("#llistaProfessorsAbsents").load("../ass_servei_adm/llista_professors_absents.php");
		   editIndex = undefined;
	  }
	});
   
    </script>