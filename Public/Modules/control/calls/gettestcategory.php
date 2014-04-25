<?php require_once('../../../../Private/config/config.php');?>
<?php require_once('authm.php'); ?>
<?php
mysql_select_db($database_cavoconnection, $cavoconnection);
mysql_query("SET NAMES UTF8");

//Index 	Testid 	Name 	Description
$query = "SELECT `id`, `name` FROM `base_test_category` WHERE `id` !=1";

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