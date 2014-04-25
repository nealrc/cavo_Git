<?php require_once("../../../../Private/config/config.php"); ?>
<?php require_once('auth.php'); ?>
<?php
// number of levels
$nlevels = 4;

mysql_select_db($database_cavoconnection, $cavoconnection);

$type=explode('_',$_REQUEST['d']);
if($type[0] =='i'){
	$user_id = $type[1];	
}else{
	$query = "SELECT `Userid` from `user` WHERE `Membership`=4 AND `University`=".$type[1];
	
	$result = mysql_query($query, $cavoconnection) or die(mysql_error());
	$rows = mysql_fetch_assoc($result);
	$total_nums = mysql_num_rows($result);	
	if($total_nums >0){
		do{
			$user_id[] = $rows['Userid'];
		}while($rows = mysql_fetch_assoc($result));
	}
	mysql_free_result($result);	
}

if($user_id != NULL || (is_array($user_id) && count($user_id)>0)){

$query = "SELECT `id`, `name`, `description` from `base_test`";
$result = mysql_query($query, $cavoconnection) or die(mysql_error());
$rows = mysql_fetch_assoc($result);
$total_Qnums = mysql_num_rows($result);	
if($total_Qnums >0){
	do{
		$test_types[$rows['id']] = $rows['name'];
	}while($rows = mysql_fetch_assoc($result));
}
mysql_free_result($result);

// record data
if(is_array($user_id)){
	$wh = ' WHERE a.`user` IN ('.implode(',', $user_id).')';
}else{
	$wh = ' WHERE a.`user`='.$user_id;
}
$query = "SELECT a.`QuestionID`, a.`AnswerID`, 	a.`test`	
	FROM `test_answers` AS a".$wh;

$result = mysql_query($query, $cavoconnection) or die(mysql_error());
$rows = mysql_fetch_assoc($result);
$total_Qnums = mysql_num_rows($result);	
if($total_Qnums >0){
	do{
		if(!isset($ids[$rows['QuestionID']])){
			$ids[$rows['QuestionID']] = 1;
		}
		$data[$rows['test']][$rows['QuestionID']][] = $rows['AnswerID'];		
	}while($rows = mysql_fetch_assoc($result));
}
mysql_free_result($result);

}

if(count($ids)>0){
// hsk and freq levels
$query = "SELECT e.`tiku_id`, e.`hsk` AS hsk_level, e.`freq` AS freq_level 
		FROM `cavo_level_init` AS e WHERE e.`tiku_id` IN (".
		implode(',', array_keys($ids)).")";
$result = mysql_query($query, $cavoconnection) or die(mysql_error());
$rows = mysql_fetch_assoc($result);
$total_Qnums = mysql_num_rows($result);	
if($total_Qnums >0){
	do{
		if($rows['hsk_level'] != 0 && 
			!is_null($rows['hsk_level']) && 
			!isset($levels['hsk'][$rows['hsk_level']][$rows['tiku_id']]) ){
			$levels['hsk'][$rows['hsk_level']][$rows['tiku_id']] = 1;
		}
		
		if($rows['freq_level'] != 0 && 
			!is_null($rows['freq_level']) && 
			!isset($levels['freq'][$rows['freq_level']][$rows['tiku_id']])){
			$levels['freq'][$rows['freq_level']][$rows['tiku_id']] = 1;
		}
		
	}while($rows = mysql_fetch_assoc($result));
}
mysql_free_result($result);
}

if(count($data)>0){
foreach($data as $test => $arr){
	// cavo levels
	$query = "SELECT e.`tiku_id`, e.`test`, e.`level`
			FROM `cavo_level` AS e WHERE e.`test` = $test AND e.`tiku_id` IN (".
			implode(',', array_keys($arr)).")";
	$result = mysql_query($query, $cavoconnection) or die(mysql_error());
	$rows = mysql_fetch_assoc($result);
	$total_Qnums = mysql_num_rows($result);	
	if($total_Qnums >0){
		do{
			if(!isset($levels['cavo'][$rows['level']][$rows['tiku_id']]) ){
				$levels['cavo'][$rows['level']][$rows['tiku_id']] = 1;
			}
		}while($rows = mysql_fetch_assoc($result));
	}
	mysql_free_result($result);
}
}

//print_r($levels);


if(count($levels)>0){
$pass_counts = array();
$totals = array();

foreach($levels as $type => $level_arr){
	foreach($level_arr as $level_val => $ids_in_level){		
		foreach($ids_in_level as $id => $dummy){
			
			foreach($data as $test => $arr){
				if(isset($arr[$id])){
					$size = count($arr[$id]);
					
					if($type != 'cavo'){						
						if(!isset($totals[$type][$level_val])){
							$totals[$type][$level_val] = $size;
						}else{
							$totals[$type][$level_val] = $totals[$type][$level_val] + $size;
						}
					}else{
						if(!isset($totals[$type][$test][$level_val])){
							$totals[$type][$test][$level_val] = $size;
						}else{
							$totals[$type][$test][$level_val] = $totals[$type][$test][$level_val] + $size;
						}
					}
					
					foreach($arr[$id] as $k => $a){
						if($id == $a){
							if($type != 'cavo'){
								if(!isset($pass_counts[$type][$level_val])){
									$pass_counts[$type][$level_val]=1;
								}else{
									$pass_counts[$type][$level_val]=$pass_counts[$type][$level_val]+1;
								}
							}else{								
								if(!isset($pass_counts[$type][$test][$level_val])){
									$pass_counts[$type][$test][$level_val]=1;
								}else{
									$pass_counts[$type][$test][$level_val]=$pass_counts[$type][$test][$level_val]+1;
								}
							}
						}
					}
				}
			}
		}
	}
}
}

//print_r($totals);
//print_r($pass_counts);

if(count($totals)>0){
$p=array();

$c=0;
foreach($totals as $db => $arr){
	if($db != 'cavo'){
		$p[$c]['db'] = strtoupper($db);
		$b = 0;
		
		// $totals    [$type][$level_val]
		//$pass_counts[$type][$level_val]		
		for($i=0;$i<$nlevels;$i++){
			if(isset($arr[$i+1]) && isset($pass_counts[$db][$i+1]) ){
				$p[$c]['rate'][$b]['level'] = $i+1;				
				$p[$c]['rate'][$b]['value'] = round($pass_counts[$db][$i+1]/$arr[$i+1], 4)*100;
				$b++;				
			}else{
				$p[$c]['rate'][$b]['level'] = $i+1;				
				$p[$c]['rate'][$b]['value'] = 0;
				$b++;
			}
		}
		$c++;
	}else{
		foreach($arr as $test => $subarr){
			$p[$c]['db'] = $test_types[$test];			
			$b = 0;

			// $totals    [$type][$test][$level_val]
			//$pass_counts[$type][$test][$level_val]
			for($i=0;$i<$nlevels;$i++){
				if(isset($subarr[$i+1]) && isset($pass_counts[$db][$test][$i+1]) ){
					$p[$c]['rate'][$b]['level'] = $i+1;				
					$p[$c]['rate'][$b]['value'] = round($pass_counts[$db][$test][$i+1]/$subarr[$i+1], 4)*100;
					$b++;
				}else{
					$p[$c]['rate'][$b]['level'] = $i+1;				
					$p[$c]['rate'][$b]['value'] = 0;
					$b++;
				}
			}	
			$c++;
		}
		
	}	
}

//print_r($pass_counts);
//print_r($totals);

print json_encode($p);
}else{
	print 100;
}
?>