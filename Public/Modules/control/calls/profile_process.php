<?php 
require_once('../../../../Private/config/config.php');
require_once('../../../../Private/class/function.php');
?>
<?php require_once('auth.php'); ?>
<?php
$uid = $_SESSION['MM_Userid'];

$str='';
if(!isset($_POST["firstname"]) || isEmpty($_POST["firstname"])){
	$str .= '<li><strong><i>"Firstname"</i></strong> is required. </li>';
}else{
	$firstname = $_POST["firstname"];
}

if(!isset($_POST["lastname"]) || isEmpty($_POST["lastname"])){
	$str .= '<li><strong><i>"Lastname"</i></strong> is required. </li>';
}else{
	$lastname = $_POST["lastname"];
}

if(!isset($_POST["email"]) || isEmpty($_POST["email"])){
	$str .= '<li><strong><i>"Email"</i></strong> is required. </li>';
}elseif(!checkEmail($_POST["email"])){
	$str .= '<li><strong><i>"Email"</i></strong> is invalide. </li>';
}else{	
	$email = $_POST["email"];
}

if(!isset($_POST["university"]) || isEmpty($_POST["university"])){
	$str .= '<li><strong><i>School</i></strong> is required. </li>';
}else{
	$school = $_POST["university"];
}

if(!isset($_POST["enroll"]) || isEmpty($_POST["enroll"])){
	$str .= '<li>You must enter your <strong><i>Enrollment year</i></strong>. </li>';
}elseif(!checkYear($_POST["enroll"])){
	$str .= '<li><strong><i>Enrollment year</i></strong> is invalid. </li>';
}else{
	$enroll = $_POST["enroll"];
}


if(!isset($_POST["age"]) || isEmpty($_POST["age"])){
	$str .= '<li><strong><i>Age Range</i></strong> is required. </li>';
}else{
	$age = $_POST["age"];
}

if(!isset($_POST["native"]) || isEmpty($_POST["native"])){
	$str .= '<li><strong><i>Native Speaker or not</i></strong> is required. </li>';
}else{
	$native = $_POST["native"]=='y'?1:0;
}


if($str==''){
	mysql_select_db($database_cavoconnection, $cavoconnection);
	mysql_query("SET NAMES UTF8");

	// check email
	$query = sprintf("SELECT * FROM `user` WHERE `Email`=%s AND `Userid` != %s",GetSQLValueString($email, "text"),GetSQLValueString($uid, "int"));
	$result = mysql_query($query, $cavoconnection) or die(mysql_error());
	$num = mysql_num_rows($result);
	mysql_free_result($result);	
	
	if($num >0){
		$str = '<ol><li>The email <strong>"'.$email.'"</strong> has already been registered with CAVO. Please choose another email address. </li></ol>';
		print $str;		
	}else{
            /*
		// check school
		$query_check = "SELECT * FROM `base_school` WHERE LOWER(`name`)='".strtolower($school)."'";
		$result = mysql_query($query_check, $cavoconnection) or die(mysql_error());
		$num = mysql_num_rows($result);
		if($num > 0){
			$rows = mysql_fetch_assoc($result);			
			$sid=$rows['id'];
		}else{
			$query_insert = "INSERT INTO`base_school` SET `name`='".strtolower($school)."'";
			$result2 = mysql_query($query_insert, $cavoconnection) or die(mysql_error());
			$sid = mysql_insert_id();
		}
		mysql_free_result($result);		
		*/
            
		//get user id, test identifier
            /*
		$query = sprintf("UPDATE `user` SET `Firstname` = %s, `Lastname` = %s, `Email` = %s, `University`=%s, `EnrollmentYear` = %s,
                    `age` = %s, `native` = %s WHERE `Userid` = %s",
                     GetSQLValueString($firstname, 'text'),
                     GetSQLValueString($lastname, 'text'),
                     GetSQLValueString($email, 'text'),
                    GetSQLValueString($sid, 'int'),
                    GetSQLValueString($enroll, 'int'),
                    GetSQLValueString($age, 'int'),
                    GetSQLValueString($native, 'int'),
                    GetSQLValueString($uid, 'int'));
             */  
		$query = sprintf("UPDATE `user` SET `Firstname` = %s, `Lastname` = %s, `Email` = %s, `EnrollmentYear` = %s,
                    `age` = %s, `native` = %s WHERE `Userid` = %s",
                     GetSQLValueString($firstname, 'text'),
                     GetSQLValueString($lastname, 'text'),
                     GetSQLValueString($email, 'text'),
                    GetSQLValueString($enroll, 'int'),
                    GetSQLValueString($age, 'int'),
                    GetSQLValueString($native, 'int'),
                    GetSQLValueString($uid, 'int'));
		$result = mysql_query($query, $cavoconnection) or die(mysql_error());
                
		if($result){
			print 'success';
		}else{
			print 'no';
		}
	}
}else{
	print $str;
}
exit;
?>