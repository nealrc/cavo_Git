<?php 
require_once('../../../../Private/config/config.php');
require_once('../../../../Private/class/function.php');
?>
<?php require_once('autha.php'); ?>
<?php
$newvalue = $_POST['value'];
$z = $_POST['id'];
$t = explode('_', $z);
$level = $t[0];
$type = $t[1];

mysql_select_db($database_cavoconnection, $cavoconnection);
//update changes
$query_update = "UPDATE `base_level_def` SET `$type`='$newvalue' WHERE `Level`=$level";
$update = mysql_query($query_update, $cavoconnection) or die(mysql_error());

//display new value
$query_display = "SELECT `$type` FROM `base_level_def` WHERE `Level`='$level'";
$display = mysql_query($query_display, $cavoconnection) or die(mysql_error());
do{
	$current = $row_display[$type];
}while($row_display = mysql_fetch_assoc($display));

print $current;
?>