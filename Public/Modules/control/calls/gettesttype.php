<?php require_once('../../../../Private/config/config.php');?>
<?php require_once('authm.php'); ?>
<?php
mysql_select_db($database_cavoconnection, $cavoconnection);
mysql_query("SET NAMES UTF8");

//Index 	Testid 	Name 	Description
//$query = "SELECT a.`id`, b.`name` FROM `base_test` AS a LEFT JOIN `base_test_type` AS b ON (a.`test_type` = b.`id`)";
$query = "SELECT `id`, `name`, `description` FROM `base_test_type`";

$result = mysql_query($query, $cavoconnection) or die(mysql_error());
$rows = mysql_fetch_assoc($result);
$total = mysql_num_rows($result);
if($total > 0){
	$str='<select>';
	do{
		$str .="<option value='".$rows['id']."'>".$rows['description']."</option>";
	}while($rows = mysql_fetch_assoc($result));
	$str.="</select>";
}else{
	$str=100;
}
mysql_free_result($result);

print $str;
?>