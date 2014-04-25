<?php 
require_once('../../../../Private/config/config.php');
require_once('../../../../Private/class/function.php');
?>
<?php require_once('autha.php'); ?>
<?php
mysql_select_db($database_cavoconnection, $cavoconnection);
mysql_query("SET NAMES UTF8");

$data = urldecode($_GET['data']);
if(strpos($data, '&')!==false){
	$tt = explode('&', $data);
	foreach($tt as $kk => $vv){
		$t = explode('=', $vv);
		foreach($t as $k => $v){
			if(is_numeric($v)){
				$ids[] = $v;
			}
		}	
	}
}else{
	$t = explode('=', $data);
	foreach($t as $k => $v){
		if(is_numeric($v)){
			$ids[] = $v;
		}
	}	
}


$type=$_GET['type'];

if($type=='user'){
	$table = 'user';
	$field = 'Active';
	$id = 'Userid';
}elseif($type=='word'){
	$table = 'newword';
	$field = 'validated';
	$id = 'id';
}else{
}

$action = $_GET['action'];

if($action == 'activate'){
	if($type == 'word'){
		foreach($ids as $kk => $ii){
			$query = "SELECT * FROM `newword` WHERE `id` = $ii";
			$result = mysql_query($query, $cavoconnection) or die(mysql_error());
			$num = mysql_num_rows($result);
			if($num >0){
				$rows = mysql_fetch_assoc($result);
				$word = $rows['word'];
				$py = $rows['py'];
				$cn = $rows['cn'];
				$en = $rows['en'];
				$level = $rows['level'];

				if(is_null($level)){
					$queryh= "SELECT `level` FROM `tiku_hsk` WHERE `word` = '$word' LIMIT 1";
					$resulth = mysql_query($queryh, $cavoconnection);
					$num = mysql_num_rows($resulth);
					if($num > 0){
						$rowsh = mysql_fetch_assoc($resulth);			
						$level = $rowsh['level'];
					}else{
						$queryf= "SELECT `level` FROM `tiku_freq` WHERE `word` = '$word' LIMIT 1";
						$resultf = mysql_query($queryf, $cavoconnection);
						$num = mysql_num_rows($resultf);
						if($num > 0){
							$rowsf = mysql_fetch_assoc($resultf);			
							$level = $rowsf['level'];
						}
						mysql_free_result($resultf);
					}
					mysql_free_result($resulth);
				}
				
				if(is_null($level)){
					$level = mt_rand(1,4);
				}

				$query2 = sprintf("INSERT INTO `tiku_cavo_test`(`word`,`py`,`en`,`cn`) VALUES (%s,%s,%s,%s)", GetSQLValueString($word, 'text'), GetSQLValueString($py, 'text'), GetSQLValueString($en, 'text'), GetSQLValueString($cn, 'text'));
				$result2 = mysql_query($query2, $cavoconnection) or die(mysql_error());
				$ri = mysql_insert_id();
				
				$query3  = "INSERT INTO `cavo_level_init` (`tiku_id`, `cavo`) VALUES ($ri, $level)";
				$result3 = mysql_query($query3, $cavoconnection) or die(mysql_error());
				
				$query4  = "INSERT INTO `cavo_level` (`tiku_id`,`test`, `level`) VALUES 
							($ri,1,$level),($ri,2,$level),($ri,3,$level)";
				$result4 = mysql_query($query4, $cavoconnection) or die(mysql_error());
			}else{
				exit('record not found!');
			}
			mysql_free_result($result);
		}
	}
	
	$query  = "UPDATE `$table` SET `$field` = 1 WHERE `$id` IN (".implode(',', $ids).")";
	$result = mysql_query($query, $cavoconnection) or die(mysql_error());
}else{
	$query = "DELETE FROM `$table` WHERE `$id` IN (".implode(',', $ids).")";
	$result = mysql_query($query, $cavoconnection) or die(mysql_error());
}
print 'OK';
?>