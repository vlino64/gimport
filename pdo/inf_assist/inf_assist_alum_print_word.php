<?php
  session_start();
  header("Content-type: application/vnd.ms-word");
  header("Content-Disposition: attachment;Filename=Informe.doc");
  header("Pragma: no-cache");
  header("Expires: 0");
  
  require_once('../bbdd/connect.php');
  require_once('../func/constants.php');
  require_once('../func/generic.php');
  require_once('../func/seguretat.php');
  
  if (strrpos($_SERVER['HTTP_USER_AGENT'], 'Linux') === false){
  }
  else {
      $db->exec("set names utf8");
  }
  
  $data_inici = isset($_REQUEST['data_inici']) ? substr($_REQUEST['data_inici'],6,4)."-".substr($_REQUEST['data_inici'],3,2)."-".substr($_REQUEST['data_inici'],0,2) : getCursActual($db)["data_inici"];
  if ($data_inici=='--') {
  	  $data_inici = getCursActual($db)["data_inici"];
  }
  $txt_inici  = isset($_REQUEST['data_inici']) ? $_REQUEST['data_inici'] : '';
  
  $data_fi    = isset($_REQUEST['data_fi'])    ? substr($_REQUEST['data_fi'],6,4)."-".substr($_REQUEST['data_fi'],3,2)."-".substr($_REQUEST['data_fi'],0,2)          : getCursActual($db)["data_fi"];
  if ($data_fi=='--') {
  	  $data_fi = getCursActual($db)["data_fi"];
  }
  $txt_fi     = isset($_REQUEST['data_fi'])    ? $_REQUEST['data_fi'] : '';
  
  if ( isset($_REQUEST['idalumne']) && ($_REQUEST['idalumne']==0) ) {
  	$idalumne = 0;
  }
  else if ( isset($_REQUEST['idalumne']) ) {
    $idalumne = $_REQUEST['idalumne'];
  }
  if (! isset($idalumne)) {
    $idalumne = 0;
  }
  
  $box_faltes         = isset($_REQUEST['box_faltes'])         ? $_REQUEST['box_faltes']         : '';
  $box_retards        = isset($_REQUEST['box_retards'])        ? $_REQUEST['box_retards']        : '';
  $box_justificacions = isset($_REQUEST['box_justificacions']) ? $_REQUEST['box_justificacions'] : '';
  $box_incidencies    = isset($_REQUEST['box_incidencies'])    ? $_REQUEST['box_incidencies']    : '';
  $box_CCC            = isset($_REQUEST['box_CCC'])            ? $_REQUEST['box_CCC']            : '';
 
  $mode_impresio      = isset($_REQUEST['mode_impresio'])      ? $_REQUEST['mode_impresio']      : 0;
?>

 <?php
  	if (! $mode_impresio) {
  ?>
  <h4 style="margin-bottom:0px">
  <form id="ff" name="ff" method="post">
  Alumne  
  <input id="idalumne" name="idalumne" class="easyui-combobox" style="width:380px" data-options="
                	required: false,
                    panelWidth: 380
  ">
  <br /><br />
  <input id="box_faltes" name="box_faltes" type="checkbox" value="falta" /> Faltes 
  <input id="box_retards" name="box_retards" type="checkbox" value="retard" /> Retards 
  <input id="box_justificacions" name="box_justificacions" type="checkbox" value="justificacio" /> Justificacions 
  <input id="box_incidencies" name="box_incidencies" type="checkbox" value="incidencia" /> Seguiments 
  <input id="box_CCC" name="box_CCC" type="checkbox" value="CCC" /> CCC 
  <br />
  Desde <input id="data_inici" class="easyui-datebox" data-options="formatter:myformatter,parser:myparser"></input>
  Fins a <input id="data_fi" class="easyui-datebox" data-options="formatter:myformatter,parser:myparser"></input>
  </h4>
  <p align="right" style=" border:0px solid #0C6; height:32px; background:whitesmoke;">
  <a href="#" onclick="doSearch()">
  <img src="./images/icons/icon_search.png" height="32"/></a>
  <a href="#" onclick="javascript:imprimirPDF()">
  <img src="./images/icons/icon_pdf.png" height="32"/></a>
  <a href="#" onclick="javascript:imprimirWord()">
  <img src="./images/icons/icon_word.png" height="32"/></a>
  <a href="#" onclick="javascript:imprimirExcel()">
  <img src="./images/icons/icon_excel.png" height="32"/></a>
  </form>
  </p>
  <?php
  	}
  ?>
  
 <div id="resultDiv">
  
  <h2>
  Informe d'assist&egrave;ncia
  <a>
  <?php 
  	if ($idalumne != 0) {
		echo getAlumne($db,$idalumne,TIPUS_nom_complet);
	}
   ?></a>
    (<a><?= $txt_inici ?></a>
   - <a><?= $txt_fi ?></a>)
  </h2>

 <br />
 
 <div class="right">
 
 <?php
  if ($idalumne != 0) {
 ?>
 <table>
    <tr>
        <td><strong>NUM. FALTES</strong></td>
        <td><strong>NUM. RETARDS</strong></td>
        <td><strong>NUM. JUSTIFICADES</strong></td>
        <td><strong>NUM. SEGUIMENTS</strong></td>
        <td><strong>NUM. CCC</strong></td>
    </tr>
    <tr>
        <td class='drop'><?=getTotalIncidenciasAlumne($db,$idalumne,TIPUS_FALTA_ALUMNE_ABSENCIA,$data_inici,$data_fi)?></td>
        <td class='drop'><?=getTotalIncidenciasAlumne($db,$idalumne,TIPUS_FALTA_ALUMNE_RETARD,$data_inici,$data_fi)?></td>
        <td class='drop'><?=getTotalIncidenciasAlumne($db,$idalumne,TIPUS_FALTA_ALUMNE_JUSTIFICADA,$data_inici,$data_fi)?></td>
        <td class='drop'><?=getTotalIncidenciasAlumne($db,$idalumne,TIPUS_FALTA_ALUMNE_SEGUIMENT,$data_inici,$data_fi)?></td>
        <td class='drop'><?=getTotalCCCAlumne($db,$idalumne,$data_inici,$data_fi)?></td>
    </tr>
 </table>
 <?php
  }
 ?>
  
 <?php
  if ($box_faltes != '') {
 ?>
 <h5>Relaci&oacute; de faltes</h5>
 <table>
            <tr>
                <td> </td>
                <td><strong>DATA</strong></td>
                <td><strong>F. HOR&Agrave;RIA</strong></td>
                <td><strong>PROFESSOR/A</strong></td>
                <td><strong>MAT&Egrave;RIA</strong></td>
            </tr>
            
                <?php
				   $linea         = 1;
				   $rsIncidencias = getIncidenciasAlumne($db,$idalumne,TIPUS_FALTA_ALUMNE_ABSENCIA,$data_inici,$data_fi);
                                   foreach($rsIncidencias->fetchAll() as $row) {
						  echo "<tr>";
						  echo "<td valign='top' width='30'>".$linea."</td>";
						  echo "<td valign='top' width='100' class='drop'>".substr($row["data"],8,2)."-".substr($row["data"],5,2)."-".substr($row["data"],0,4)."</td>";
						  echo "<td valign='top' width='80' class='drop'>".substr(getFranjaHoraria($db,$row["idfranges_horaries"])["hora_inici"],0,5)."-".substr(getFranjaHoraria($db,$row["idfranges_horaries"])["hora_fi"],0,5)."</td>";
						  echo "<td valign='top' class='drop'>".getProfessor($db,$row["idprofessors"],TIPUS_nom_complet)."</td>";
						  echo "<td valign='top' class='drop'>".getMateria($db,$row["id_mat_uf_pla"])["nom_materia"]."</td></tr>";
						  $linea++;
				   }
				?>          
  </table>
  <br />   
  <?php
  }
  ?> 
        
  <?php
  if ($box_retards != '') {
  ?> 
  <h5>Relaci&oacute; de retards</h5>
  <table>
            <tr>
                <td> </td>
                <td><strong>DATA</strong></td>
                <td><strong>F. HOR&Agrave;RIA</strong></td>
                <td><strong>PROFESSOR/A</strong></td>
                <td><strong>MAT&Egrave;RIA</strong></td>
            </tr>
            
                <?php
				   $linea         = 1;
				   $rsIncidencias = getIncidenciasAlumne($db,$idalumne,TIPUS_FALTA_ALUMNE_RETARD,$data_inici,$data_fi);
				   foreach($rsIncidencias->fetchAll() as $row) {
						  echo "<tr>";
						  echo "<td valign='top' width='30'>".$linea."</td>";
						  echo "<td valign='top' width='100' class='drop'>".substr($row["data"],8,2)."-".substr($row["data"],5,2)."-".substr($row["data"],0,4)."</td>";
						  echo "<td valign='top' width='80' class='drop'>".substr(getFranjaHoraria($db,$row["idfranges_horaries"])["hora_inici"],0,5)."-".substr(getFranjaHoraria($db,$row["idfranges_horaries"])["hora_fi"],0,5)."</td>";
						  echo "<td valign='top' class='drop'>".getProfessor($db,$row["idprofessors"],TIPUS_nom_complet)."</td>";
						  echo "<td valign='top' class='drop'>".getMateria($db,$row["id_mat_uf_pla"])["nom_materia"]."</td></tr>";
						  $linea++;
				   }
				?>          
		</table>
  <br />   
  <?php
  }
  ?>
  
  <?php
  if ($box_justificacions != '') {
  ?>     
  <h5>Relaci&oacute; de justificacions</h5>
  <table>
            <tr>
                <td> </td>
                <td><strong>DATA</strong></td>
                <td><strong>F. HOR&Agrave;RIA</strong></td>
                <td><strong>PROFESSOR/A</strong></td>
                <td><strong>MAT&Egrave;RIA</strong></td>
                <td><strong>OBSERVACIONS</strong></td>
            </tr>
            
                <?php
				   $linea         = 1;
				   $rsIncidencias = getIncidenciasAlumne($db,$idalumne,TIPUS_FALTA_ALUMNE_JUSTIFICADA,$data_inici,$data_fi);
				   foreach($rsIncidencias->fetchAll() as $row) {
						  echo "<tr>";
						  echo "<td valign='top' width='20'>".$linea."</td>";
						  echo "<td valign='top' width='100' class='drop'>".substr($row["data"],8,2)."-".substr($row["data"],5,2)."-".substr($row["data"],0,4)."</td>";
						  echo "<td valign='top' width='80' class='drop'>".substr(getFranjaHoraria($db,$row["idfranges_horaries"])["hora_inici"],0,5)."-".substr(getFranjaHoraria($db,$row["idfranges_horaries"])["hora_fi"],0,5)."</td>";
						  echo "<td valign='top' class='drop'>".getProfessor($db,$row["idprofessors"],TIPUS_nom_complet)."</td>";
						  echo "<td valign='top' class='drop'>".getMateria($db,$row["id_mat_uf_pla"])["nom_materia"]."</td>";
						  echo "<td valign='top' width='300' class='drop'>".nl2br($row["comentari"])."</td></tr>";
						  $linea++;
				   }
				?>          
  </table>
  <br />   
  <?php
  }
  ?>
   
  <?php
  if ($box_incidencies != '') {
  ?>
  <h5>Relaci&oacute; de seguiments</h5>
  <table>
            <tr>
                <td> </td>
                <td><strong>TIPUS</strong></td>
                <td><strong>DATA</strong></td>
                <td><strong>F. HOR&Agrave;RIA</strong></td>
                <td><strong>PROFESSOR/A</strong></td>
                <td><strong>MAT&Egrave;RIA</strong></td>
                <td><strong>OBSERVACIONS</strong></td>
            </tr>
            
                <?php
				   $linea         = 1;
				   $rsIncidencias = getIncidenciasAlumne($db,$idalumne,TIPUS_FALTA_ALUMNE_SEGUIMENT,$data_inici,$data_fi);
				   foreach($rsIncidencias->fetchAll() as $row) {
						  echo "<tr>";
						  echo "<td valign='top' width='20'>".$linea."</td>";
						  echo "<td valign='top' width='40' class='drop'>".getLiteralTipusIncident($db,$row["id_tipus_incident"])["tipus_incident"]."</td>";
						  echo "<td valign='top' width='70' class='drop'>".substr($row["data"],8,2)."-".substr($row["data"],5,2)."-".substr($row["data"],0,4)."</td>";
						  echo "<td valign='top' width='80' class='drop'>".substr(getFranjaHoraria($db,$row["idfranges_horaries"])["hora_inici"],0,5)."-".substr(getFranjaHoraria($db,$row["idfranges_horaries"])["hora_fi"],0,5)."</td>";
						  echo "<td valign='top' class='drop'>".getProfessor($db,$row["idprofessors"],TIPUS_nom_complet)."</td>";
						  echo "<td valign='top' width='50' class='drop'>".getMateria($db,$row["id_mat_uf_pla"])["nom_materia"]."</td>";
						  echo "<td valign='top' width='300' class='drop'>".nl2br($row["comentari"])."</td></tr>";
						  $linea++;
				   }
				?>          
	</table>
    <br />   
	<?php
    }
    ?>
        
	<?php
    if ($box_CCC != '') {
    ?>
    <h5>Relaci&oacute; de CCC</h5>
 	<table>
            <tr>
                <td> </td>
                <td><strong>TIPUS CCC</strong></td>
                <td><strong>DATA</strong></td>
                <td><strong>EXPULSI&Oacute;</strong></td>
                <td><strong>PROFESSOR/A</strong></td>
                <td><strong>MAT&Egrave;RIA</strong></td>
                <td><strong>DESCRIPCI&Oacute;</strong></td>
            </tr>
            
                <?php
				   $linea         = 1;
				   $rsIncidencias = getCCCAlumne($idalumne,$data_inici,$data_fi);
				   foreach($rsIncidencias->fetchAll() as $row) {
						  echo "<tr>";
						  echo "<td valign='top' width='20'>".$linea."</td>";
						  echo "<td valign='top' width='40' class='drop'>".getLiteralTipusCCC($db,$row["id_falta"])["nom_falta"]."</td>";
						  echo "<td valign='top' width='70' class='drop'>".substr($row["data"],8,2)."-".substr($row["data"],5,2)."-".substr($row["data"],0,4)."</td>";
						  echo "<td valign='top' width='40' class='drop'>".$row["expulsio"]."</td>";
						  echo "<td valign='top' width='120' class='drop'>".getProfessor($db,$row["idprofessor"],TIPUS_nom_complet)."</td>";
						  echo "<td valign='top' width='50' class='drop'>".(intval($row["idmateria"]!=0) ? getMateria($db,$row["idmateria"])["nom_materia"] : '')."</td>";
						  echo "<td valign='top' width='320' class='drop'><strong>Desc. breu</strong><br>".getLiteralMotiusCCC($db,$row["id_motius"])["nom_motiu"];
						  echo "<br><strong>Desc. detallada</strong><br>".nl2br($row["descripcio_detallada"])."</td></tr>";
						  $linea++;
				   }
				?>          
		</table>
    <?php
    }
    ?>
        
 </div>
    
<?php
	if (isset($rsAlumnes)) {
    	//mysql_free_result($rsAlumnes);
	}
	if (isset($rsIncidencias)) {
    	//mysql_free_result($rsIncidencias);
	}
	if (isset($rsEquipDocent)) {
    	//mysql_free_result($rsEquipDocent);
	}
?>

</div>

<iframe id="fitxer_pdf" scrolling="yes" frameborder="0" style="width:10px;height:10px; visibility:hidden" src=""></iframe>

<script type="text/javascript">  		
		var url;
		
		function myformatter(date){  
            var y = date.getFullYear();  
            var m = date.getMonth()+1;  
            var d = date.getDate();  
            return (d<10?('0'+d):d)+'-'+(m<10?('0'+m):m)+'-'+y;
        }
		
		function myparser(s){  
            if (!s) return new Date();  
            var ss = (s.split('-'));  
            var y = parseInt(ss[0],10);  
            var m = parseInt(ss[1],10);  
            var d = parseInt(ss[2],10);  
            if (!isNaN(y) && !isNaN(m) && !isNaN(d)){  
                return new Date(d,m-1,y);
            } else {  
                return new Date();  
            }  
        }
		
		function doSearch(){  
			d_inici  = $('#data_inici').datebox('getValue');
			d_fi     = $('#data_fi').datebox('getValue');
			idalumne = $('#idalumne').combobox('getValue');
			
			url = './inf_assist/inf_assist_alum_see.php?data_inici='+d_inici+'&data_fi='+d_fi+'&idalumne='+idalumne+'&mode_impresio=1';
			
			$('#ff').form('submit',{  
						url: url, 
						onSubmit: function(){  
							return $(this).form('validate');  
						},  
						success: function(result){
							$('#resultDiv').html(result);
							$('#idalumne').combobox('setValue', idalumne);
						}  
			}); 			 
        }  
		
		function imprimirPDF(idgrup){  
			d_inici  = $('#data_inici').datebox('getValue');
			d_fi     = $('#data_fi').datebox('getValue');
			idalumne = $('#idalumne').combobox('getValue');
			
			box_faltes         = '<?= $box_faltes ?>';
			box_retards        = '<?= $box_retards ?>';
			box_justificacions = '<?= $box_justificacions ?>';
			box_incidencies    = '<?= $box_incidencies ?>';
			box_CCC            = '<?= $box_CCC ?>';
			
			url  = './inf_assist/inf_assist_alum_print.php?data_inici='+d_inici+'&data_fi='+d_fi+'&idalumne='+idalumne+'&mode_impresio=1';
			url += '&box_faltes='+box_faltes+'&box_retards='+box_retards;
			url += '&box_justificacions='+box_justificacions+'&box_incidencies='+box_incidencies+'&box_CCC='+box_CCC;
			
			$('#fitxer_pdf').attr('src', url);
		}
		
		function imprimirWord(idgrup){  
			d_inici  = $('#data_inici').datebox('getValue');
			d_fi     = $('#data_fi').datebox('getValue');
			idalumne = $('#idalumne').combobox('getValue');
			
			box_faltes         = '<?= $box_faltes ?>';
			box_retards        = '<?= $box_retards ?>';
			box_justificacions = '<?= $box_justificacions ?>';
			box_incidencies    = '<?= $box_incidencies ?>';
			box_CCC            = '<?= $box_CCC ?>';
			
			url  = './inf_assist/inf_assist_alum_print_word.php?data_inici='+d_inici+'&data_fi='+d_fi+'&idalumne='+idalumne+'&mode_impresio=1';
			url += '&box_faltes='+box_faltes+'&box_retards='+box_retards;
			url += '&box_justificacions='+box_justificacions+'&box_incidencies='+box_incidencies+'&box_CCC='+box_CCC;
			
			$('#fitxer_pdf').attr('src', url);
		}
		
		function imprimirExcel(idgrup){  
			d_inici  = $('#data_inici').datebox('getValue');
			d_fi     = $('#data_fi').datebox('getValue');
			idalumne = $('#idalumne').combobox('getValue');
			
			box_faltes         = '<?= $box_faltes ?>';
			box_retards        = '<?= $box_retards ?>';
			box_justificacions = '<?= $box_justificacions ?>';
			box_incidencies    = '<?= $box_incidencies ?>';
			box_CCC            = '<?= $box_CCC ?>';
			
			url  = './inf_assist/inf_assist_alum_print_excel.php?data_inici='+d_inici+'&data_fi='+d_fi+'&idalumne='+idalumne+'&mode_impresio=1';
			url += '&box_faltes='+box_faltes+'&box_retards='+box_retards;
			url += '&box_justificacions='+box_justificacions+'&box_incidencies='+box_incidencies+'&box_CCC='+box_CCC;
			
			$('#fitxer_pdf').attr('src', url);
		}
		
</script>

<script type="text/javascript">
	$('#header').css('visibility', 'hidden');
	$('#footer').css('visibility', 'hidden');
	
	$('#idalumne').combobox({
		url:'./almat_tree/alum_getdata.php',
		valueField:'id_alumne',
		textField:'alumne'
	});
</script>
