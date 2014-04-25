<?php 
require_once('../../../../Private/config/config.php');
require_once('../../../../Private/class/function.php');
?>
<?php require_once('auth.php'); ?>
<?php
//
//use $_SESSION['MM_Userid'] for user's own preview
//use $_GET['id'] for instructor review student
//
$uid = isset($_GET['userid'])?$_GET['userid']:$_SESSION['MM_Userid'];

$limit = 50;

mysql_select_db($database_cavoconnection, $cavoconnection);
mysql_query("SET NAMES UTF8");

//get user test record
$query_testrecord = "SELECT  a.`id`, a.`date` AS Testdate, DATE(a.`date`) AS date, a.`score` AS score, a.`duration` AS time, b.`name` AS testname FROM `test_records` AS a LEFT JOIN `base_test` AS b ON (a.`test` = b.`id`) WHERE a.`user` = $uid ORDER BY a.`date` DESC LIMIT 0, 50";

$testrecord = mysql_query($query_testrecord, $cavoconnection) or die(mysql_error());
$row_testrecord = mysql_fetch_assoc($testrecord);
$totalRows_testrecord = mysql_num_rows($testrecord);

if($totalRows_testrecord > 0){

	$ii=0;
	do{
		$ddate = $row_testrecord['Testdate'];
		$d[$row_testrecord['testname']][$ddate][$ii]['date'] = date('m/d/Y', strtotime($ddate));
		$d[$row_testrecord['testname']][$ddate][$ii]['score'] = $row_testrecord['score'];
		$d[$row_testrecord['testname']][$ddate][$ii]['time'] = $row_testrecord['time'];
		$ii++;		
	}while ($row_testrecord = mysql_fetch_assoc($testrecord));
	
	$jj=0;
	foreach($d as $tt => $arr){
		$data[$jj]['test'] = $tt;
		$rarr=$arr;
		ksort($rarr);
		
		$rd = array();
		$jk=0;
		foreach($rarr as $tdate => $info){			
			foreach($info as $k => $subarr){
				$rd[$subarr['date']][$jk]['mdate'] = $subarr['date'];
				$rd[$subarr['date']][$jk]['score'] = $subarr['score'];
				$rd[$subarr['date']][$jk]['time'] = $subarr['time'];				
			}
			$jk++;
		}
		
		foreach($rd as $gdate => $garr){
			foreach($garr as $gk => $gkarr){
				$data[$jj]['data'][] = $gkarr;	
			}
		}
		$jj++;	
	}
}
mysql_free_result($testrecord);

if(isset($data)){
	print json_encode($data);
}else{	
	print 100;
}
?>