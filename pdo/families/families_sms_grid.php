<?php
   session_start();
   //require_once('../func/seguretat.php');
   //$db->exec("set names utf8");
     
   $idalumne      = $_REQUEST['idalumne']; 
   $strNoCache    = "";
?>        
    <table id="dg" class="easyui-datagrid" title="SMS Rebuts" 
	data-options="
		singleSelect: true,
                pagination: true,
                rownumbers: true,
		toolbar: '#toolbar',
		url: './families/families_sms_getdata.php?idalumne=<?=$idalumne?>',
		onClickRow: onClickRow
	">    
        <thead>  
            <tr>
		<th field="data" width="100" sortable="true">Data</th>
                <th field="hora" width="70" sortable="true">Hora</th>
                <th field="telefon" width="125" sortable="true">Tel&egrave;fon</th>
                <th field="content" width="600" sortable="true">Contingut</th>
            </tr>  
        </thead>  
    </table> 
    
    <div id="toolbar" style="padding:5px;height:auto"> 
          <form id="ff" name="ff" method="post">
          Desde <input id="data_inici" class="easyui-datebox" data-options="formatter:myformatter,parser:myparser"></input>
          Fins a <input id="data_fi" class="easyui-datebox" data-options="formatter:myformatter,parser:myparser"></input>
          &nbsp;&nbsp;<a href="#" class="easyui-linkbutton" iconCls="icon-search" onclick="doSearch()">Cercar</a>
          </form>
    </div>
    
    <iframe id="fitxer_pdf" scrolling="yes" frameborder="0" style="width:10px;height:10px; visibility:hidden" src=""></iframe>

    <script type="text/javascript">  
        var url;
	var editIndex    = undefined;
	var nou_registre = 0;
	var today        = new Date();
		
	$('#idalumne').combobox({
			url:'./almat_tree/alum_getdata.php',
			valueField:'id_alumne',
			textField:'alumne'
	});
        
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
								
	$(function(){
            $('#dg').datagrid({  
				view: detailview,
				detailFormatter:function(index,row){
					return '<div class="ddv" style="padding:5px 0"></div>';
				},
				onExpandRow: function(index,row){
					var ddv = $(this).datagrid('getRowDetail',index).find('div.ddv');
					ddv.panel({
						border:false,
						cache:false,
						href:'./families/families_sms_getdetail.php?id='+row.id_env,
						onLoad:function(){
							$('#dg').datagrid('fixDetailRowHeight',index);
						}
					});
					$('#dg').datagrid('fixDetailRowHeight',index);
				},
				rowStyler:function(index,row){
				    return 'color:#333;';
				}
            });  
        });
				
		function doSearch(){					 
			$('#dg').datagrid('load',{  
				data_inici: $('#data_inici').datebox('getValue'),
				data_fi   : $('#data_fi').datebox('getValue')
			});
		}
		
		function onClickRow(index){
				var row = $('#dg').datagrid('getSelected');
				
				if (editIndex != index){
					if (endEditing()){
						$('#dg').datagrid('selectRow', index)
								.datagrid('beginEdit', index);
						editIndex = index;
					} else {
						$('#dg').datagrid('selectRow', editIndex);
					}
				}
		}
					
		function endEditing(){
			if (editIndex == undefined){return true}
			if ($('#dg').datagrid('validateRow', editIndex)){
				$('#dg').datagrid('acceptChanges');
				$('#dg').datagrid('endEdit', editIndex);
								
				editIndex = undefined;
				return true;
			} else {				
				return false;
			}
		}
				
		function reject(){
		    $('#dg').datagrid('rejectChanges');
			editIndex = undefined;
		}
		
	</script>
        
    <style type="text/css">  
        #fm{  
            margin:0;  
            padding:10px 30px;  
        }  
        .ftitle{  
            font-size:14px;  
            font-weight:bold;  
            padding:5px 0;  
            margin-bottom:10px;  
            border-bottom:1px solid #ccc;  
        }  
        .fitem{  
            margin-bottom:5px;  
        }  
        .fitem label{  
            display:inline-block;  
            width:80px;  
        }  
    </style>