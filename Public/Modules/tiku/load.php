<?php require_once('../../../Private/config/config.php'); ?>
<?php require_once('auth.php'); ?>
<?php
$start = $_POST['s'];
$end = $_POST['e'];

mysql_select_db($database_cavoconnection, $cavoconnection);
mysql_query("SET NAMES UTF8");

$tiku_table = 'tiku_cavo_test';

$query = "SELECT a.`id` AS tikuid, a.`word`, a.`py`, a.`en`, a.`cn`, b.`id` AS edit_id, b.`flag`, b.`comment`, b.`date`, c.`Firstname`, c.`Lastname` FROM `$tiku_table` AS a LEFT JOIN `log_tiku_edit` AS b ON (a.`id` = b.`tiku_id`) LEFT JOIN `user` AS c On (b.`user` = c.`Userid`) WHERE a.`id`>=$start AND a.`id`<=$end";

$result = mysql_query($query) or die(mysql_error());
$row_p = mysql_fetch_array($result); 
$totalRows_r = mysql_num_rows($result);

if($totalRows_r>0){
	do{
		$g[$row_p['tikuid']]['word']=$row_p['word'];
		$g[$row_p['tikuid']]['py']=$row_p['py'];
		$g[$row_p['tikuid']]['cn']=$row_p['cn'];
		$g[$row_p['tikuid']]['en']=$row_p['en'];
		
		if(!is_null($row_p['edit_id'])){
		$g[$row_p['tikuid']]['edit'][$row_p['edit_id']]['user'] 	= $row_p['Firstname'].' '.$row_p['Lastname'];
		$g[$row_p['tikuid']]['edit'][$row_p['edit_id']]['flag'] 	= $row_p['flag'];		
		$g[$row_p['tikuid']]['edit'][$row_p['edit_id']]['comment'] 	= $row_p['comment'];
		$g[$row_p['tikuid']]['edit'][$row_p['edit_id']]['date'] 	= $row_p['date'];
		}		
	} while ($row_p = mysql_fetch_assoc($result));
}
mysql_free_result($result);


$str="";
$str .= "<table border='0' cellpadding='5' cellspacing='0'>\n";
$str .="<tr><th>ID</th><th>Word</th><th>Pinyin</th><th>Chinese</th><th>English</th><th>Dict.cn</th><th>User</th><th>Flag</th><th>Comment</th>\n</tr>\n";

foreach($g as $tid => $arr){
	$str .="<tr>\n";
	$str .="<td class='tdid'><a class='imodal' href='content.php?tikuid=".$tid."'>".$tid."</a></td>\n";	
	$str .="<td class='tdid'><a class='imodal' href='content.php?tikuid=".$tid."'>".$arr['word']."</a></td>\n";	
	$str .="<td class='tdo'>".$arr['py']."</td>\n";
	$str .="<td class='tdo'>".$arr['cn']."</td>\n";
	$str .="<td class='tdo'>".$arr['en']."</td>\n";
	$str .="<td><a class='omodal' href='http://www.dict.cn/".$arr['word']."'>Link</a>\n</td>\n";
	
	if(isset($arr['edit'])){		
		$count_comment=0;
		
		$last_flag = strtotime('1900-01-01');
		$last_user = 'NA';
		$last_flag_action = 'NA';
		
		foreach($arr['edit'] as $eid => $info){
			if(!is_null($info['date'])&&$last_flag < strtotime($info['date'])){
				$last_flag = $info['date'];
				$last_user = $info['user'];
				if(!is_null($info['flag'])){
					$last_flag_action = $info['flag']==1?'added':'removed';
				}
			}
			if(!is_null($info['comment'])){
				$count_comment++;
			}
		}
		
		
		if($last_user != 'NA'){
			$str .="<td class='tdo'>".$last_user."</td>\n";
		}else{
			$str .="<td class='tdo'>&nbsp;</td>\n";
		}		
		
		if($last_flag != strtotime('1900-01-01') && $last_flag_action != 'NA'){
			$action_icon = $last_flag_action=='added'?'check2.gif':'remove.gif';   
			   
			$str .="<td class='tdo'><img src='css/".$action_icon."' /></td>\n";
		}else{
			$str .="<td class='tdo'>&nbsp;</td>\n";
		}
		
		if($count_comment > 0){
			$str .="<td class='tdo'><a class='cmodal' href='getcomment.php?tikuid=".$tid."'> There ";
			if($count_comment > 1){ $str.="are ";}else{$str.="is ";}
			$str.= $count_comment." comment";
			if($count_comment > 1){ $str.="s";}
			$str.="</a></td>\n";		
		}else{		
			$str .="<td class='tdo'>&nbsp;</td>\n";
		}
		
	}else{
		$str .="<td class='tdo'>&nbsp;</td>\n";
		$str .="<td class='tdo'>&nbsp;</td>\n";
		$str .="<td class='tdo'>&nbsp;</td>\n";
	}		
	$str .="</tr>\n";
}
$str .= "</table> ";

print $str;
?>