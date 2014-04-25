<?php
# FileName="Connection_php_mysql.htm"
# Type="MYSQL"
# HTTP="true"
$hostname_cavoconnection = "localhost";
$database_cavoconnection = "nealrc5_cavo";
$username_cavoconnection = "nealrc5_root";
$password_cavoconnection = "liuxiong";
$cavoconnection = mysql_pconnect($hostname_cavoconnection, $username_cavoconnection, $password_cavoconnection) or trigger_error(mysql_error(),E_USER_ERROR); 

$icavoconnection = new mysqli($hostname_cavoconnection, $username_cavoconnection, $password_cavoconnection, $database_cavoconnection);

mysql_select_db($database_cavoconnection, $cavoconnection);
mysql_query("SET NAMES UTF8");

$query = "SELECT `id`, `name` FROM `base_membership`";	
$result = mysql_query($query, $cavoconnection) or die(mysql_error());
$rows = mysql_fetch_assoc($result);
$num = mysql_num_rows($result);
if($num>0){
do{
$role[$rows['name']]=$rows['id'];
}while($rows = mysql_fetch_assoc($result));
}
mysql_free_result($result);
?>