<?php 
require_once('../../../Private/config/config.php'); 
require_once('../../../Private/class/function.php');
require_once('../../../Private/class/procs.php');
?>
<?php
if (!isset($_SESSION)) {
  session_start();
}
$MM_authorizedUsers = $role['admin'].','.$role['instructor'].','.$role['student'];
$MM_donotCheckaccess = "true";

// *** Restrict Access To Page: Grant or deny access to this page
function isAuthorized($strUsers, $strGroups, $UserName, $UserGroup) { 
  // For security, start by assuming the visitor is NOT authorized. 
  $isValid = False; 

  // When a visitor has logged into this site, the Session variable MM_Username set equal to their username. 
  // Therefore, we know that a user is NOT logged in if that Session variable is blank. 
  if (!empty($UserName)) { 
    // Besides being logged in, you may restrict access to only certain users based on an ID established when they login. 
    // Parse the strings into arrays. 
    $arrUsers = Explode(",", $strUsers); 
    $arrGroups = Explode(",", $strGroups); 
    if (in_array($UserName, $arrUsers)) { 
      $isValid = true; 
    } 
    // Or, you may restrict access to only certain users based on their username. 
    if (in_array($UserGroup, $arrGroups)) { 
      $isValid = true; 
    } 
    if (($strUsers == "") && true) { 
      $isValid = true; 
    } 
  } 
  return $isValid; 
}

$MM_restrictGoTo = "../../../signin.php";
if (!((isset($_SESSION['MM_Username'])) && (isAuthorized("",$MM_authorizedUsers, $_SESSION['MM_Username'], $_SESSION['MM_UserGroup'])))) {   
  $MM_qsChar = "?";
  $MM_referrer = $_SERVER['PHP_SELF'];
  if (strpos($MM_restrictGoTo, "?")) $MM_qsChar = "&";
  if (isset($QUERY_STRING) && strlen($QUERY_STRING) > 0) 
  $MM_referrer .= "?" . $QUERY_STRING;
  $MM_restrictGoTo = $MM_restrictGoTo. $MM_qsChar . "accesscheck=" . urlencode($MM_referrer);
  header("Location: ". $MM_restrictGoTo); 
  exit;
}
?>
<?php
$error="";
//$update = false;

if(isset($_POST['learn']) && isset($_POST['speak'])){
	if(is_numeric($_POST['learn'])){
		$learn = $_POST['learn'];
	}else{
		$error .= "error => You must enter numeric value for question 1!";
		$error .="<br />\n";
	}
	
	if(is_numeric($_POST['speak'])){
		$speak = $_POST['speak'];
	}else{
		$error .= "error => You must enter numeric value for question 2!";
		$error .="<br />\n";
	}
}else{
	$error .= "error => You must finish both questions to proceed!";
	$error .="<br />\n";
}

if(!$_SESSION['MM_Userid']){
	$error .= "error => You must sign in to take the test!";
	$error .="<br />\n";
}

if($error == ""){
	//clear previous test related session
	//for test to start
	require_once('clearsession.php');
	
	//get the new test id and name
	$_SESSION['Testid'] = $_POST['mytestid'];
	$_SESSION['Testname'] = $_POST['mytestname'];
	
	$_SESSION['LearningPeriod'] = $learn;
	$_SESSION['SpeakingPeriod'] = $speak;
	
	print "success";
}else{
	print $error;
}
?>