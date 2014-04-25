<?php 
require_once('../../../../Private/config/config.php');
require_once('../../../../Private/class/function.php');
?>
<?php require_once('auth.php'); ?>
<?php
$uid = $_SESSION['MM_Userid'];
$cpass = $_POST['currentpass'];
$npass = $_POST['newpass'];
$npass2 = $_POST['renewpass'];

//printf("%s, %s<br />", $npass, $npass2);

mysql_select_db($database_cavoconnection, $cavoconnection);
mysql_query("SET NAMES UTF8");

$query = "SELECT * FROM `user` WHERE `Userid` = $uid AND (`Password` = sha1('$cpass') OR `Password` = '$cpass')";
$result = mysql_query($query, $cavoconnection) or die(mysql_error());
$num = mysql_num_rows($result);
if($num >0){	
	if(!empty($npass) && !empty($npass2)){
		if(sha1($npass) != sha1($npass2)){
			print "New password entered mis-match. Please try again.";
			exit;
		}else{
			$query = sprintf("UPDATE `user` SET `Password` = %s WHERE `Userid` = %s", GetSQLValueString(sha1($npass), 'text'), GetSQLValueString($uid, 'int'));
			$result2 = mysql_query($query, $cavoconnection) or die(mysql_error());		
			if($result2){
				print 'success';
				exit;
			}else{
				print 'Update failed. Please contact administrator.';
				exit;
			}	
		}
	}else{
		print 'You must enter a new password!';
		exit;
	}
}else{
	print 'Wrong current password. Please try again.';
	exit;
}
?>