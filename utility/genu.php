<?php
require_once('../Private/config/config.php');
require_once('../Private/class/procs.php');
?>
<?php
/*
	get time weight and passrate
*/
function get_test_timeweight($db,$level){
	$query_timeweight = "SELECT `TimeWeight` FROM `base_level_def` WHERE `Level`=$level";
	$timeweight = mysql_query($query_timeweight, $db) or die(mysql_error());
	$row_timeweight = mysql_fetch_assoc($timeweight);

	$tw = $row_timeweight['TimeWeight'];
	mysql_free_result($timeweight);
	
	//echo 'time weight: '.$tw; 
	
	return $tw;	
}
?>
<?php
mysql_select_db($database_cavoconnection, $cavoconnection);
mysql_query("SET NAMES utf8");

$n = 20;


$query = "SELECT `firstname`, `lastname` FROM `base_demo_names` WHERE `id` >= (SELECT FLOOR( MAX(`id`) * RAND()) FROM `base_demo_names` ) ORDER BY `id` LIMIT $n";

$result = mysql_query($query, $cavoconnection) or die(mysql_error());
$rows = mysql_fetch_assoc($result);
do{
	$fnames[] = ucfirst(strtolower($rows['firstname']));
	$lnames[] = ucfirst(strtolower($rows['lastname']));
	
	$emails[] =ucfirst(strtolower($rows['firstname'])).'_'.ucfirst(strtolower($rows['lastname'])); 
}while($rows = mysql_fetch_assoc($result));
mysql_free_result($result);


foreach($emails as $k => $d){
	$e = $d.'@demo.edu';
	$age = mt_rand(4,6);
	$p = 'demo';

	$insertSQL = sprintf("INSERT INTO `user` (`Password`, `Firstname`, `Lastname`, `University`, `EnrollmentYear`, `Email`, `Membership`, `Active`, `age`, `native`, `date`) 
					VALUES (%s, %s, %s, %s, %s, %s, %s, %s, %s, %s, NOW())",
					 GetSQLValueString(sha1($p), "text"),
					 GetSQLValueString($fnames[$k], "text"),
					 GetSQLValueString($lnames[$k], "text"),
					 GetSQLValueString(15, "int"),
					 GetSQLValueString(2011, "int"),
					 GetSQLValueString($e, "text"),
					 GetSQLValueString(4, "int"),
					 GetSQLValueString(1, "int"),
					 GetSQLValueString($age, "int"),
					 GetSQLValueString(0, "int"));
	mysql_query($insertSQL, $cavoconnection) or die(mysql_error());
		
}

echo ' Done!';
?>