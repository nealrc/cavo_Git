<?php 
require_once('../../../Private/config/config.php');
require_once("../../../Private/class/function.php");
?>
<?php
if (!isset($_SESSION)) {
  session_start();
}

$method=$_GET['method'];

if(null==$method || ''==$method){
	die('illegal submit!');
}


mysql_select_db($database_cavoconnection, $cavoconnection);
mysql_query("SET NAMES UTF8");

if('add'==$method){
	//print_r($_SESSION);
	$userid=$_SESSION['MM_Userid'];
	$time=date('Y-m-d H:i:s');
	$opinion=$_POST['opinion'];
	if(null==$opinion || ''==$opinion){
		die('illegal submit!');
	}
	$opinion=htmlspecialchars($opinion);
	$sql = sprintf("INSERT INTO `feedback` (`userid`, `opinion`, `time`)VALUES(%s,%s,%s)",
			GetSQLValueString($userid,'int'),
			GetSQLValueString($opinion,'text'),
			GetSQLValueString($time,'text'));
	//echo($sql);
	
	$result = mysql_query($sql, $cavoconnection) or die(mysql_error());
	$num_inc=mysql_affected_rows();
	if(1==$num_inc){
		echo('added');
	}
}

if('del'==$method){
	$id=$_GET['id'];
	$sql= 'DELETE FROM `feedback` WHERE `id`='.$id;
	//echo($sql);
	$result = mysql_query($sql, $cavoconnection) or die(mysql_error());
	$num_del=mysql_affected_rows();
	if(1==$num_del){
		echo('deleted');
	}
	
}
?>