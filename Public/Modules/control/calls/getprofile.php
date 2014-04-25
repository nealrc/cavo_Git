<?php require_once('../../../../Private/config/config.php');?>
<?php require_once('auth.php'); ?>
<?php

$uid = $_SESSION['MM_Userid'];

mysql_select_db($database_cavoconnection, $cavoconnection);
mysql_query("SET NAMES UTF8");

//get user id, test identifier
$query = "SELECT  
			a.`Firstname`, 
			a.`Lastname`, 
			b.`name` AS role, 
			a.`EnrollmentYear` As year, 
			c.`name` AS school, 
			a.`Email`, 
			a.`age`,
			a.`native`,
			a.`Active` 
		FROM `user` AS a 
		LEFT JOIN `base_membership` AS b ON (b.`id` = a.`membership`) 
		LEFT JOIN `base_school` AS c ON (c.`id` = a.`University`)
		WHERE a.`Userid` = $uid";
$result = mysql_query($query, $cavoconnection) or die(mysql_error());
$rows = mysql_fetch_assoc($result);
$num = mysql_num_rows($result);
mysql_free_result($result);
	
if($num >0){
	$data[0]['firstname'] = ucfirst($rows['Firstname']);
	$data[0]['lastname'] = ucfirst($rows['Lastname']);
	$data[0]['university'] = ucfirst($rows['school']);
	$data[0]['year'] = $rows['year'];
	$data[0]['role'] = $rows['role'];
	$data[0]['email'] = $rows['Email'];
	$data[0]['age'] = $rows['age'];
	$data[0]['native'] = $rows['native']==1?'y':'n';
	$data[0]['active'] = ((int)$rows['Active']== 1 || strtolower($rows['Active'])=='y')?'Yes':'No';
	
	print json_encode($data);
}else{
	print 'no';
}
?>