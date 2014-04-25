<?php
# FileName="Connection_php_mysql.htm"
# Type="MYSQL"
# HTTP="true"
$hostname_cavoconnection = "localhost";
$database_cavoconnection = "nealrc5_cavo1";
$username_cavoconnection = "nealrc5_cavo1";
$password_cavoconnection = "cavo1111111";
$cavoconnection = mysql_pconnect($hostname_cavoconnection, $username_cavoconnection, $password_cavoconnection) or trigger_error(mysql_error(),E_USER_ERROR); 

$icavoconnection = new mysqli($hostname_cavoconnection, $username_cavoconnection, $password_cavoconnection, $database_cavoconnection);

mysql_select_db($database_cavoconnection, $cavoconnection);
mysql_query("SET NAMES UTF8");
try{
	$query = "SELECT `id`, `name` FROM `base_membership`";	
	$result = mysql_query($query, $cavoconnection) or die(mysql_error());
	$rows = mysql_fetch_assoc($result);
	$num = mysql_num_rows($result);
	if($num>0){
		//echo "base_membership size ", $num;
		do{
			$role[$rows['name']]=$rows['id'];
		}while($rows = mysql_fetch_assoc($result));
	}
	mysql_free_result($result);
}
catch(Exception $e)
{
	echo "Exception - " , $e->getMessage(), "\n";
}

# debug option. If enabled, logging will be turned on
$debug = true;
if($debug)
{
	ini_set('include_path', '/home/nealrc5');
}
?>