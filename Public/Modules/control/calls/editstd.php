<?php 
require_once('../../../../Private/config/config.php');
require_once('../../../../Private/class/function.php');
?>
<?php require_once('authm.php'); ?>
<?php
$oper = $_POST['oper'];
$id = $_POST['id'];
$error='';

if($oper != 'del'){	
	// input checking
	//Userid Firstname Lastname Email Password EnrollmentYear Membership University Active
	if(isset($_POST['Firstname']) && $_POST['Firstname']!=''){
		$firstname = $_POST['Firstname'];
	}else{
		$error.='First name is required. <br />';
	}
	if(isset($_POST['Lastname']) && $_POST['Lastname']!=''){
		$lastname = $_POST['Lastname'];
	}else{
		$error.='Last name is required. <br />';
	}
	
	if($oper == 'add'){
		if(isset($_POST['Password']) && $_POST['Password']!=''){
			$password = sha1($_POST['Password']);
		}else{
			$error = "Password is required. <br />";
		}
		
		if(isset($_POST['Password2']) && $_POST['Password2']!=''){
			$password2 = sha1($_POST['Password2']);
		}else{
			$error = "Re-type Password is required. <br />";
		}

		if($password != $password2){
			$error.='Password doesn\'t match. <br />';
		}
	}else{
		if(isset($_POST['Password']) && $_POST['Password']!=''){
			$password = sha1($_POST['Password']);
		}
		if(isset($_POST['Password2']) && $_POST['Password2']!=''){
			$password2 = sha1($_POST['Password2']);		
			if($password != $password2){
				$error.='Password doesn\'t match. <br />';
			}
		}
	}

	if(isset($_POST['Active']) && $_POST['Active']!=''){
		$active = $_POST['Active'];
	}else{
		$error.='Active is required. <br />';
	}

	if(isset($_POST['native']) && $_POST['native']!=''){
		$native = $_POST['native'];
	}else{
		$error.='Native is required. <br />';
	}

	if(isset($_POST['age']) && $_POST['age']!=''){
		$age = $_POST['age'];
	}else{
		$error.='Age is required. <br />';
	}


	if(isset($_POST['EnrollmentYear']) && $_POST['EnrollmentYear']!=''){
		$enrollment = $_POST['EnrollmentYear'];
	}else{
		$error.='EnrollmentYear is required. <br />';
	}

	if(isset($_POST['Membership']) && $_POST['Membership']!=''){
		$membership = $_POST['Membership'];
	}else{
		$error.='Membership is required. <br />';
	}	

	if(isset($_POST['University']) && $_POST['University']!=''){
		$school = $_POST['University'];
	}else{
		$error.='University is required. <br />';
	}
	if(isset($_POST['Email']) && $_POST['Email']!=''){
		if(checkEmail($_POST['Email'])){	
			$email = $_POST['Email'];
		}else{
			$error.='Email entered is not valid. <br />';
		}
	}else{
		$error.='Email is required. <br />';
	}
}

if($error==''){
	mysql_select_db($database_cavoconnection, $cavoconnection);
	mysql_query("SET NAMES UTF8");

	switch($oper){
		case 'add':
			// check email
			$query = sprintf("SELECT * FROM `user` WHERE `Email`=%s", GetSQLValueString($email, "text"));
			$result = mysql_query($query, $cavoconnection) or die(mysql_error());
			$num = mysql_num_rows($result);
			mysql_free_result($result);			
			if($num >0){
				$error .= '<ol><li>The email <strong>"'.$email.'"</strong> has already been registered with CAVO. Please choose another email address. </li></ol>';
				print $error; exit;
			}else{
				//Userid Firstname Lastname Email Password EnrollmentYear Membership University Active
				$query = sprintf("INSERT INTO `user` (`Firstname`, `Lastname`, `Email`, `Password`, `EnrollmentYear`, 
					`Membership`, `University`, `age`, `native`, `Active`, `date`) VALUES ('%s', '%s', '%s', '%s', %s, %s, %s, %s, %s, %s, NOW())", $firstname, $lastname, $email, $password, $enrollment, $membership, $school, $age, $native, $active);
			}
			break;
		case 'edit':
			// check email
			$query = sprintf("SELECT * FROM `user` WHERE `Email`=%s AND `Userid` != %s",GetSQLValueString($email, "text"),GetSQLValueString($id, "int"));
			$result = mysql_query($query, $cavoconnection) or die(mysql_error());
			$num = mysql_num_rows($result);
			mysql_free_result($result);				
			if($num >0){
				$error .= '<ol><li>The email <strong>"'.$email.'"</strong> has already been registered with CAVO. Please choose another email address. </li></ol>';
				print $error; exit;
			}else{
				//UPDATE t SET id = id + 1;
				if(isset($password) && isset($password2)){			
					$query = sprintf("UPDATE `user` SET `Firstname`='%s', `Lastname`='%s', `Email`='%s', `Password`='%s', `EnrollmentYear`=%s, `Membership`=%s, `University`=%s, `age`=%s, `native`=%s, `Active`=%s WHERE `Userid` = $id",  $firstname, $lastname, $email, $password, $enrollment, $membership, $school, $age, $native, $active);				
				}else{
					$query = sprintf("UPDATE `user` SET `Firstname`='%s', `Lastname`='%s', `Email`='%s', `EnrollmentYear`=%s, `Membership`=%s,`University`=%s, `age`=%s, `native`=%s, `Active`=%s WHERE `Userid` = $id", $firstname, $lastname, $email, $enrollment, $membership, $school, $age, $native, $active);
				}
			}
			break;
		case 'del':
			$query = "DELETE FROM `user`, `test_records`, `test_answers` USING `user` LEFT JOIN `test_records` ON (`user`.`Userid` = `test_records`.`user`) LEFT JOIN `test_answers` ON (`user`.`Userid` = `test_answers`.`user`) WHERE `user`.`Userid`=$id";
			break;
	}	
	
	//print $query;
	
	$records = mysql_query($query, $cavoconnection) or die(mysql_error());
	if($records){
		echo "success";
	}else{
		echo "Update failed";
	}
}else{
	echo $error;
}
?>