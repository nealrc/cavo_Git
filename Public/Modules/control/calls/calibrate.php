<?php 
require_once('../../../../Private/config/config.php');
require_once('../../../../Private/class/function.php');
?>
<?php require_once('autha.php'); ?>
<?php
$test = $_REQUEST['test'];
$par = $_REQUEST['par'];
$par_size = $_REQUEST['par_size'];
$row_count = $_REQUEST['N_rows'];

$start = $par*$par_size;
$end = $start+$row_count-1;

$recordtable = 'test_answers';
$leveltable = "cavo_level";

mysql_select_db($database_cavoconnection, $cavoconnection);

/*
//get different tests
$query ="SELECT `id`, `name` FROM `base_test` WHERE `id`=$test";
//echo $query.'<br />';
$result = mysql_query($query, $cavoconnection) or die(mysql_error());
$row_test = mysql_fetch_assoc($result); 
$totalRows_test = mysql_num_rows($result); 
$testname = $row_test['name'];
mysql_free_result($result);
*/

//get level lower bound
$query_dlevel ="SELECT `Level`, `Lowerbound` FROM `base_level_def`"; 
$dlevel = mysql_query($query_dlevel, $cavoconnection) or die(mysql_error());
$row_dlevel = mysql_fetch_assoc($dlevel); 
$totalRows_dlevel = mysql_num_rows($dlevel); 
do{ 
	$levelDef[$row_dlevel['Level']] =$row_dlevel['Lowerbound'];
}while($row_dlevel=mysql_fetch_assoc($dlevel)); 
mysql_free_result($dlevel); 

//calculate difficulty level
$ans=array();

$query_num = "SELECT `QuestionID`, `AnswerID` FROM `$recordtable` WHERE `test` = $test LIMIT $start, $row_count";
//echo $query_num.'<br />';
$num = mysql_query($query_num, $cavoconnection) or die(mysql_error());
$row_num = mysql_fetch_assoc($num);
$totalRows_num = mysql_num_rows($num);
if($totalRows_num > 0){
	do{
		if(!isset($ans[$row_num['QuestionID']])){
			$ans[$row_num['QuestionID']]['ans'][] = $row_num['AnswerID'];
			$ans[$row_num['QuestionID']]['miss'] = 0;
		}			
		if($row_num['QuestionID'] != $row_num['AnswerID']){
			$ans[$row_num['QuestionID']]['miss'] = $ans[$row_num['QuestionID']]['miss']+1;
		}
	}while($row_num=mysql_fetch_assoc($num)); 
}
mysql_free_result($num); 

if(count($ans)>0){
foreach($ans as $qid => $info){
	$size = count($info['ans']);
	$wrong = $info['miss'];		
	$rate = 1- round($wrong/$size,4);
	$level = AssignLevel($rate, $levelDef);
	
	$ans[$qid]['level'] = $level;
	
	$query = "SELECT count(*) as N from `$leveltable` WHERE `tiku_id`= $qid";
	$result = mysql_query($query, $cavoconnection) or die(mysql_error());
	$rows = mysql_fetch_assoc($result);
	$nrows = $rows['N'];
	mysql_free_result($result);
	
	if($nrows > 0){
		//update to level table
		$updatelevel="UPDATE `$leveltable` SET `level` = $level WHERE `tiku_id`= $qid AND `test` = $test";
		$result = mysql_query($updatelevel, $cavoconnection) or die(mysql_error());
	}
}
}
		
// print "Record $start ~ $end --Done";
print 'ok';


/*
	return the closest level, measured by euclidean distance
*/
function AssignLevel($v, $bounds){
	if(!is_array($bounds)){
		return "AssignLevel error => $arr is not array.";
	}	
	$z = count($bounds);
	if($z == 1){
		return 1;
	}	
	foreach($bounds as $k => $p){
		$tmp[$k] = abs($p - $v);
	}	
	asort($tmp);
	return key($tmp);
}
?>