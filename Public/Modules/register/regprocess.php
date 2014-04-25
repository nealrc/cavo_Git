<?php
require_once("../../../Private/class/function.php");

$str = '';

$active = 0;

if(!isset($_POST["firstname"]) || isEmpty($_POST["firstname"])){
	$str .= '<li><strong><i>Firstname</i></strong> is required. </li>';
}else{
	$firstname = $_POST["firstname"];
}

if(!isset($_POST["lastname"]) || isEmpty($_POST["lastname"])){
	$str .= '<li><strong><i>Lastname</i></strong> is required. </li>';
}else{
	$lastname = $_POST["lastname"];
}

if(!isset($_POST["email"]) || isEmpty($_POST["email"])){
	$str .= '<li><strong><i>Email</i></strong> is required. </li>';
}elseif(!checkEmail($_POST["email"])){
	$str .= '<li><strong><i>Email</i></strong> is invalide. </li>';
}else{	
	$email = $_POST["email"];
}

if(!isset($_POST["password"]) || isEmpty($_POST["password"])){
	$str .= '<li><strong><i>Password</i></strong> is required. </li>';
}else{
	$password = $_POST["password"];
}

if(!isset($_POST["repassword"]) || isEmpty($_POST["repassword"])){
	$str .= '<li><strong><i>Re-enter your password</i></strong> is required. </li>';
}else{
	$repassword = $_POST["repassword"];
}

if($password != $repassword){
	$str .= '<li><strong><i>Passwords</i></strong> entered do not match. </li>';
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


if(!isset($_POST["school"]) || isEmpty($_POST["school"])){
	$str .= '<li><strong><i>School</i></strong> is required. </li>';
}else{
	$school = $_POST["school"];
}

$new_school = false;

if($school == 'Other'){
	if(!isset($_POST["from"]) || isEmpty($_POST["from"])){
		$str .= '<li>You must enter your <strong><i>school</i></strong>. </li>'; 
	}else{
		$school = $_POST["from"];
		$new_school = true;
	}
}

$membership = intval($_POST["membership"]);

if($membership == 4){
	if(!isset($_POST["enroll"]) || isEmpty($_POST["enroll"])){
		$str .= '<li><strong><i>Enrollment year</i></strong> is required. </li>';
	}elseif(!checkYear($_POST["enroll"])){
		$str .= '<li><strong><i>Enrollment year</i></strong> is invalid. </li>';
	}else{
		$enrollment = $_POST["enroll"];
		$active = 1;
	}
}else{
	$enrollment = '0000';
}

// TODO: remove this later
if($membership == 3){ $active = 1; }

if($str==""){
	require_once('../../../Private/config/config.php');
	mysql_select_db($database_cavoconnection, $cavoconnection);	
	
	// check email
	$query = sprintf("SELECT * FROM `user` WHERE `Email`=%s", GetSQLValueString($email, "text"));
	$result = mysql_query($query, $cavoconnection) or die(mysql_error());
	$num = mysql_num_rows($result);
	mysql_free_result($result);	
	
	if($num >0){
		$str = '<ol><li>The email <strong>"'.$email.'"</strong> has already been registered with CAVO. Please choose another email address. </li></ol>';
	}else{
		
		// check school
		
		if($new_school){
			$query_check = "SELECT * FROM `base_school` WHERE LOWER(`name`)='".strtolower($school)."'";
			$result = mysql_query($query_check, $cavoconnection) or die(mysql_error());
			$num = mysql_num_rows($result);
			mysql_free_result($result);
			
			if($num > 0){
				$rows = mysql_fetch_assoc($result);			
				$sid=$rows['id'];
			}else{
				$query_insert = "INSERT INTO`base_school` SET `name`='".strtolower($school)."'";
				$result2 = mysql_query($query_insert, $cavoconnection) or die(mysql_error());
				$sid = mysql_insert_id();
			}
			
		}else{
			$sid = $school;
		}
	
		$insertSQL = sprintf("INSERT INTO `user` (`Password`, `Firstname`, `Lastname`, `University`, `EnrollmentYear`, `Email`, `Membership`, `Active`, `age`, `native`, `date`) 
					VALUES (%s, %s, %s, %s, %s, %s, %s, %s, %s, %s, NOW())",
					 GetSQLValueString(sha1($password), "text"),
					 GetSQLValueString($firstname, "text"),
					 GetSQLValueString($lastname, "text"),
					 GetSQLValueString($sid, "int"),
					 GetSQLValueString($enrollment, "int"),
					 GetSQLValueString($email, "text"),
					 GetSQLValueString($membership, "int"),
					 GetSQLValueString($active, "int"),
					 GetSQLValueString($age, "int"),
					 GetSQLValueString($native, "int"));
		//print $insertSQL;
		$result3 = mysql_query($insertSQL, $cavoconnection) or die(mysql_error());
		
		$str = 1;		
	}	
	print $str;
	exit;
}else{
	$str = "<ol>$str</ol>";
	print $str;
	exit;
}	
?>
