<?php require_once('../../../Private/config/config.php'); ?>
<?php require_once('auth.php'); ?>
<?php
if (!function_exists("GetSQLValueString")) {
function GetSQLValueString($theValue, $theType, $theDefinedValue = "", $theNotDefinedValue = "") {
  $theValue = get_magic_quotes_gpc() ? stripslashes($theValue) : $theValue;
  $theValue = function_exists("mysql_real_escape_string") ? mysql_real_escape_string($theValue) : mysql_escape_string($theValue);

  switch ($theType) {
    case "text":
      $theValue = ($theValue != "") ? "'" . $theValue . "'" : "NULL";
      break;    
    case "long":
    case "int":
      $theValue = ($theValue != "") ? intval($theValue) : "NULL";
      break;
    case "double":
      $theValue = ($theValue != "") ? "'" . doubleval($theValue) . "'" : "NULL";
      break;
    case "date":
      $theValue = ($theValue != "") ? "'" . $theValue . "'" : "NULL";
      break;
    case "defined":
      $theValue = ($theValue != "") ? $theDefinedValue : $theNotDefinedValue;
      break;
  }
  return $theValue;
}
}
?>
<?php
$tmp = explode("__", $_POST['id']);
$tikuid = $tmp[0];
$field  = $tmp[1];
$newvalue = $_POST['value'];

//$time = date("Y-m-d, H:i:s");
//$user = $_SESSION['MM_Username'];

//$table = 'test';
$table = 'tiku_cavo_test';
$logtable = 'log_tiku_edit';

mysql_select_db($database_cavoconnection, $cavoconnection);
mysql_query("SET NAMES utf8");


if($field == 'cn' || $field == 'en'){
	$newvalue = GetSQLValueString($newvalue, 'text');
	// update
	$query_update = "UPDATE `$table` SET `$field`=$newvalue WHERE `id`=$tikuid";
	$update = mysql_query($query_update, $cavoconnection) or die(mysql_error());
}else{	
	if($field == 'flag'){
		$lvalue = $newvalue=='Yes'?1:0;
		$lvalue = GetSQLValueString($newvalue, 'int');
	}elseif($field == 'comment'){
		$lvalue = GetSQLValueString($newvalue, 'text');
	}
	//log
	$query_log = "INSERT INTO `$logtable` (`tiku_id`,`user`,`$field`,`date`) VALUES 
				  ($tikuid,".$_SESSION['MM_Userid'].",$lvalue, NOW())";
	$update = mysql_query($query_log, $cavoconnection) or die(mysql_error());
}

if($field == 'cn' || $field == 'en'){
	$query_display = "SELECT `$field` FROM `$table` WHERE `id`=$tikuid";
}else{
	$query_display = "SELECT `$field` FROM `$logtable` WHERE `tiku_id`=$tikuid ORDER BY `date` DESC Limit 1";
}

// refresh
$display = mysql_query($query_display, $cavoconnection) or die(mysql_error());
$totalRows_display = mysql_num_rows($display);
do{
	$current = $row_display[$field];
}while($row_display = mysql_fetch_assoc($display));


if($field == 'flag'){
	print $current==1?'Yes':'No';
}else{
	print $current;
}
?>