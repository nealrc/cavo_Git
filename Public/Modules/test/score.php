<?php 
require_once('../../../Private/config/config.php');
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
$finishtime = date("Y-m-d H:i:s");
$TestIdentifier = $_SESSION["testidentifer"];
$UserID = $_SESSION['MM_Userid'];	//get user id
$UserName = $_SESSION["MM_Username"];
$UserOrg = $_SESSION["MM_UserUniversity"];
$TestID = $_SESSION['Testid'];
$TestDuration = round($_SESSION['Duration'], 0);

$_SESSION['LEVEL'] = ($_SESSION['LEVEL'] >4) ? 4 : $_SESSION['LEVEL'];
?>
<?php
/*
	fetch data
*/

mysql_select_db($database_cavoconnection, $cavoconnection);
/*
	get number of correct answers
*/
$query_rightanswer = "SELECT count(*) AS num FROM `test_answers` WHERE  `user` = $UserID AND `test` = $TestID AND `identifier` = '$TestIdentifier' AND `QuestionID` = `AnswerID`";
$rightanswer = mysql_query($query_rightanswer, $cavoconnection) or die(mysql_error());
$row_rightanswer = mysql_fetch_assoc($rightanswer);
$TotalCorrectness = $row_rightanswer['num'];
mysql_free_result($rightanswer);
/*
	get time weight and passrate
*/
$query_timeweight = "SELECT `TimeWeight` FROM `base_level_def`";
$timeweight = mysql_query($query_timeweight, $cavoconnection) or die(mysql_error());
$row_timeweight = mysql_fetch_assoc($timeweight);
$totalRows_timeweight = mysql_num_rows($timeweight);
do {
	$TimeBaseWeightArr[] = $row_timeweight['TimeWeight'];
} while ($row_timeweight = mysql_fetch_assoc($timeweight));
mysql_free_result($timeweight);

$CurrentTimeWeight= $TimeBaseWeightArr[$_SESSION['LEVEL']+1];

/*
	get test time for the specific
*/
$query_duration = "SELECT `duration` FROM `test_records` WHERE `test` = '$TestID'";
$duration = mysql_query($query_duration, $cavoconnection) or die(mysql_error());
$row_duration = mysql_fetch_assoc($duration);
$totalRows_duration = mysql_num_rows($duration);

$durationset = array();
$durationset[] = $TestDuration;
do {
	$durationset[] = $row_duration['duration'];
} while ($row_duration = mysql_fetch_assoc($duration));
mysql_free_result($duration);

sort($durationset);
//$TestDurationAverage = array_sum($durationset) /count($durationset);
$MaxDuration = $durationset[count($durationset)-1];
$MinDuration = $durationset[0];
?>
 <?php
/*
	 calculate ratio
*/
//normalize time current duration
$norm = ($TestDuration - $MinDuration) / ($MaxDuration - $MinDuration + 1e-10);
$TimeFactor = ($norm > 0.5) ? 1 : abs($norm);
$Accuracy = $TotalCorrectness/$_SESSION['TotalQuestionsRequired'];
$finalscore = getScore($Accuracy, $CurrentTimeWeight, $TimeFactor, $_SESSION['LEVEL']);
$finalscore = ($finalscore >=800) ? 800 : $finalscore;

/*
	update new score into database
*/

$speak = isset($_SESSION['SpeakingPeriod'])?$_SESSION['SpeakingPeriod']:'';
$learn = isset($_SESSION['LearningPeriod'])?$_SESSION['LearningPeriod']:'';

$insertSQL = sprintf("INSERT INTO `test_records` (`user`,`date`,`score`,`duration`,`test`,`LearningPeriod`,`SpeakingPeriod`,`identifier`) VALUES (%s, %s, %s, %s, %s, %s, %s, %s)",
				 GetSQLValueString($_SESSION['MM_Userid'], "int"),
				 GetSQLValueString($finishtime, "date"),
				 GetSQLValueString($finalscore, "float"),
				 GetSQLValueString($_SESSION['Duration'], "int"),
				 GetSQLValueString($_SESSION['Testid'], "int"),
				 GetSQLValueString($learn, "int"),
				 GetSQLValueString($speak, "int"),
				 GetSQLValueString($_SESSION['testidentifer'], "text"));
	mysql_select_db($database_cavoconnection, $cavoconnection);
	mysql_query($insertSQL, $cavoconnection) or die(mysql_error());
?>
<?php
/*
	output message
*/

$t = "
<h2>Test Score</h2>
<ul>
	<li>Test duration: $TestDuration seconds</li>
	<li>Your score: <div class='score'>$finalscore(out of 800)</div></li>";


/*	

if($Accuracy >= 0.8){	
$t .="<li><div class='score'>Good Job!</div></li>"; 
}else{
}

//Let them go view details no matter what.

*/   

	$t .= "<br /><li><a href=\"../control/archive.php\">Go to Test Report to view questions you have missed</a></li>"; 


$t .='</ul>';

print $t;
?>