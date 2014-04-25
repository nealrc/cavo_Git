<?php require_once('../../../../Private/config/config.php');?>
<?php require_once('auth.php'); ?>
<?php
mysql_select_db($database_cavoconnection, $cavoconnection);
mysql_query("SET NAMES UTF8");

$query  = "SELECT * FROM `base_age`";
$result = mysql_query($query, $cavoconnection) or die(mysql_error());
$rows = mysql_fetch_assoc($result);
$num = mysql_num_rows($result);

if($num >0){
	$str='<select>';
	do{
		$str.="<option value='".$rows['id']."'>".$rows['range']."</option>";
	}while($rows = mysql_fetch_assoc($result));
	
	print $str;
}else{
	print 'no';
}
?>