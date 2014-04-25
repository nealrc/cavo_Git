<?php require_once('../../../../Private/config/config.php');?>
<?php require_once('authm.php'); ?>
<?php
$user_member = $_SESSION['MM_UserGroup'];

if($user_member == 1){
	$query = "SELECT `id`, `name` FROM `base_membership`";	
}else{
	$query = "SELECT `id`, `name` FROM `base_membership` WHERE `name` != 'admin'";	
}

mysql_select_db($database_cavoconnection, $cavoconnection);
mysql_query("SET NAMES UTF8");

//get user id, test identifier

$result = mysql_query($query, $cavoconnection) or die(mysql_error());
$rows = mysql_fetch_assoc($result);
$num = mysql_num_rows($result);

if($num >0){
	$str='<select>';
	do{
		$str.="<option value='".$rows['id']."'>".$rows['name']."</option>";
	}while($rows = mysql_fetch_assoc($result));
	
	print $str;
}else{
	print 'no';
}
?>