<?php require_once('../../../../Private/config/config.php');?>
<?php require_once('auth.php'); ?>
<?php
mysql_select_db($database_cavoconnection, $cavoconnection);
mysql_query("SET NAMES UTF8");

$type=$_GET['type'];
if($type=='user'){
	$query = "SELECT a.`Userid`, a.`Firstname`, a.`Lastname`, a.`Email`, a.`Active`,
					b.`name` AS role, c.`name` AS school
					FROM `user` AS a
					LEFT JOIN `base_membership` AS b ON (a.`Membership` = b.`id`)
					LEFT JOIN `base_school` AS c ON (a.`University` = c.`id`) WHERE `Active`=0 
					ORDER BY school ASC";
	$result = mysql_query($query, $cavoconnection) or die(mysql_error());
	$rows = mysql_fetch_assoc($result);
	$num_act = mysql_num_rows($result);	
	if($num_act >0){
		do{
			$data[$rows['Userid']]['name'] = ucfirst($rows['Firstname']).' '.ucfirst($rows['Lastname']);
			$data[$rows['Userid']]['role'] = ucfirst($rows['role']);
			$s = explode(' ',$rows['school']);
			$ss='';
			foreach($s as $kk => $vv){
				$ss .=ucfirst($vv).' ';
			}
			$data[$rows['Userid']]['school'] =$ss;
		}while($rows = mysql_fetch_assoc($result));
		
		$str="
            <table id='tbl_actuser' border='0' cellpadding='0' cellspacing='0' class='ui-widget'>
            <thead class='ui-widget-header'>
                <tr>
                    <th class='firstcell'>Activate</th>
                    <th>Name</th>
                    <th>Role</th>
                    <th class='lastcell'>School</th>
                </tr>
            </thead>
            <tbody class='ui-widget-content'>		
		";
        foreach($data as $id => $arr){
			$str.="
                <tr>
                    <td class='firstcell'><input type='checkbox' id='new_user_$id' name='user[]' value='$id' /></td>
                    <td ><label for='new_user_$id'>".$arr['name']."</label></td>
                    <td ><label for='new_user_$id'>".$arr['role']."</label></td>
                    <td class='lastcell'><label for='new_user_$id'>".$arr['school']."</label></td>
                </tr>";
        }
		
		$str.="
            </tbody>
            <tfoot class='ui-widget-footer'>
                <tr>
                	<td class='firstcell'>&nbsp;</td>
                    <td><input type='button' id='btn_actuser' value='Activate' /></td>
                    <td>&nbsp;</td>
                    <td class='lastcell'><input type='button' id='btn_deluser' value='Delete' /></td>                    
                </tr>            
            </tfoot>
            </table>		
		";
		
		print $str;
	}else{
		print 100;
	}
	mysql_free_result($result);

}else{
	$query = "SELECT a.`id`, a.`word`, a.`py`, a.`en`, a.`cn`, a.`time`,
					b.`Firstname`, b.`Lastname`, 
					c.`name` AS school,
					d.`name` AS role 
					FROM `newword` AS a
					LEFT JOIN `user` AS b ON (a.`user` = b.`Userid`)					
					LEFT JOIN `base_school` AS c ON (b.`University` = c.`id`)
					LEFT JOIN `base_membership` AS d ON (b.`Membership` = d.`id`)
					WHERE a.`validated`=0
					ORDER BY  b.`Firstname` ASC";
	$result = mysql_query($query, $cavoconnection) or die(mysql_error());
	$rows = mysql_fetch_assoc($result);
	$num_act = mysql_num_rows($result);	
	if($num_act >0){
		do{
			$data[$rows['id']]['word'] = $rows['word'];
			$data[$rows['id']]['py'] = $rows['py'];
			$data[$rows['id']]['en'] = $rows['en'];
			$data[$rows['id']]['cn'] = $rows['cn'];
			$data[$rows['id']]['date'] = $rows['time'];
			$data[$rows['id']]['role'] = $rows['role'];
			$data[$rows['id']]['school'] = $rows['school'];
			$data[$rows['id']]['user'] = ucfirst($rows['Firstname']).' '.ucfirst($rows['Lastname']);
		}while($rows = mysql_fetch_assoc($result));
		
		$str="
            <table id='tbl_actword' border='0' cellpadding='0' cellspacing='0' class='ui-widget'>
            <thead class='ui-widget-header'>
                <tr>
                    <th class='firstcell'>Select</th>
                    <th>Word</th>
                    <th>PY</th>
                    <th>EN</th>
                    <th>CN</th>
                    <th>User</th>
                    <th>Role</th>
                    <th>School</th>
                    <th class='lastcell'>Date</th>
                </tr>
            </thead>
            <tbody class='ui-widget-content'>";
		foreach($data as $id => $arr){
			$str.="
                <tr>
                    <td class='firstcell'><input type='checkbox' id='new_word_$id' name='word[]' value='$id' /></td>
                    <td ><label for='new_word_$id'>".$arr['word']."</label></td>
                    <td ><label for='new_word_$id'>".$arr['py']."</label></td>
                    <td ><label for='new_word_$id'>".$arr['en']."</label></td>
                    <td ><label for='new_word_$id'>".$arr['cn']."</label></td>
                    <td ><label for='new_word_$id'>".$arr['user']."</label></td>
                    <td ><label for='new_word_$id'>".$arr['role']."</label></td>
                    <td ><label for='new_word_$id'>".$arr['school']."</label></td>
                    <td class='lastcell'><label for='new_user_$id'>".$arr['date']."</label></td>
                </tr>";
		}
		$str.="            </tbody>
            <tfoot class='ui-widget-footer'>
                <tr>
                	<td class='firstcell'>&nbsp;</td>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                    <td><input type='button' id='btn_actword' value='Activate' /></td>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                    <td class='lastcell'><input type='button' id='btn_delword' value='Delete' /></td>                    
                </tr>            
            </tfoot>
            </table>";
		
		print $str;
	}else{
		print 100;		
	}
	mysql_free_result($result);
}

exit;
?>