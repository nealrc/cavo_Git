<?php require_once('../../../../Private/config/config.php');?>
<?php require_once('authm.php'); ?>
<?php
$user_id = $_SESSION['MM_Userid'];
$user_member = $_SESSION['MM_UserGroup'];

if($user_member == 1){
	$query = "SELECT `id`, `name` FROM `base_test` ORDER BY `name` ASC";	
}else{
	// get school id
	$query = "SELECT a.`id`, a.`name` FROM `base_test` AS a 
			  LEFT JOIN `school_test` AS b ON (a.`id` = b.`test`)
			  LEFT JOIN `user` AS c ON (c.`University` = b.`school`)
			  WHERE a.`test_category` = 1 OR c.`Userid` = $user_id
			  ORDER BY a.`name` ASC";
}

mysql_select_db($database_cavoconnection, $cavoconnection);
mysql_query("SET NAMES UTF8");

$result = mysql_query($query, $cavoconnection) or die(mysql_error());
$rows = mysql_fetch_assoc($result);
$total = mysql_num_rows($result);
if($total > 0){
	$str='<select>';
	do{
		$str .="<option value='".$rows['id']."'>".$rows['name']."</option>";
	}while($rows = mysql_fetch_assoc($result));
	$str.="</select>";
}else{
	$str=100;
}
mysql_free_result($result);

print $str;
?>