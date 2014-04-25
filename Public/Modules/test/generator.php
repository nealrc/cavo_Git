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
require_once('../../../Private/config/config.php');
require_once('../../../Private/class/function.php');
require_once('../../../Private/class/procs.php');

# check debug option
$debug = !isset($debug)? false:$debug;
$log = NULL;
if($debug)
{
	require_once('log4php/Logger.php');

	// Tell log4php to use our configuration file.	
	Logger::configure('../../../Private/config/cavo_config.xml');
	 
	// Fetch a logger, it will inherit settings from the root logger
	$log = Logger::getLogger('myLogger');	
}
?>
<?php
/*
	variables
*/

//the following 2 variables will be updated by the generator function
$questionid=0;				//ID of test subject
$question='';				//DISPLAY QUESTION IN FORM

//pinyin weight
$pyweight = ($testtype == 'PY')?0.9 : 0.25;	
//similarity threshold
// $threshold = 0.45;		
$threshold = 0;

$tikutable = "tiku_cavo_test";
$leveltable = "cavo_level";
$answertable = "test_answers";

mysql_select_db($database_cavoconnection, $cavoconnection);
mysql_query("SET NAMES UTF8");

?>
<?php
if(isset($_REQUEST['mytestid'])){
	require_once('clearsession.php');
}

/*
	Initialize Session Variables
*/
//get the new test id and name
$_SESSION['Testid'] = isset($_SESSION['Testid'])?$_SESSION['Testid']:$_REQUEST['mytestid'];

if(!isset($_SESSION['Testtype'])){
	$query = "SELECT a.`name` FROM `base_test_type` AS a LEFT JOIN `base_test` AS b ON (a.`id` = b.`test_type`) WHERE b.`id` = ".$_SESSION['Testid'];
	$result = mysql_query($query) or die(mysql_error());
	$row = mysql_fetch_assoc($result);
	//$_SESSION['Testtype'] = isset($_SESSION['Testtype'])?$_SESSION['Testtype']:$row['name'];
	$_SESSION['Testtype'] = $row['name'];
	mysql_free_result($result);
}

//count number of questions been tested
$_SESSION['TotalQuestionTested'] = isset($_SESSION['TotalQuestionTested'])?$_SESSION['TotalQuestionTested']:1;	

//store all the tikuid been tested during test
$_SESSION['TestedIDs'] = isset($_SESSION['TestedIDs'])?$_SESSION['TestedIDs']:array();	

//number of correct answer
$_SESSION['Correct'] = isset($_SESSION['Correct'])?$_SESSION['Correct']:0;

//control if this is the first time we generate question
//set to 1 = IS the first time
$_SESSION['initial'] = isset($_SESSION['initial'])?$_SESSION['initial']:1;

	
$_SESSION['Level'] =  isset($_SESSION['Level'])?$_SESSION['Level']:1;
$_SESSION['Stage'] =  isset($_SESSION['Stage'])?$_SESSION['Stage']:1;
$_SESSION['Starttime'] = isset($_SESSION['Starttime'])?$_SESSION['Starttime']:time()+ microtime();

$_SESSION['testidentifer'] = isset($_SESSION['testidentifer'])?$_SESSION['testidentifer']:RandTestIdentifier(mt_rand(8, 16));


if($_SESSION['Testid']>3){
	if(!isset($_SESSION['TotalQuestionsRequired'])){
		$query = "SELECT count(*) AS size FROM `cavo_level` WHERE `test` = ".$_SESSION['Testid'];
		
		if($debug && isset($log)) $log->debug($query);
			
		$result = mysql_query($query) or die(mysql_error());
		$row = mysql_fetch_assoc($result);
		$_SESSION['TotalQuestionsRequired'] = $row['size'];
		mysql_free_result($result);
	}
}else{
	$_SESSION['LevelQuestionTested'] = isset($_SESSION['LevelQuestionTested'])?$_SESSION['LevelQuestionTested']:1;
	
	if(!isset($_SESSION['TotalQuestionsRequired'])){
		//get number of questions in each level and number of levels
		$query_NumOfQuestions = "SELECT `NumOfQuestions`, `Level` FROM `base_level_def`";
		
		if($debug && isset($log)) $log->debug($query_NumOfQuestions);
				
		$NumOfQuestions = mysql_query($query_NumOfQuestions, $cavoconnection) or die(mysql_error());
		$row_NumOfQuestions = mysql_fetch_assoc($NumOfQuestions);
		$totalRows_NumOfQuestions = mysql_num_rows($NumOfQuestions);
	
		$QinLevel = array();
		$sum=0;
		do {
			$sum += $row_NumOfQuestions['NumOfQuestions'];
			$QinLevel[$row_NumOfQuestions['Level']] = $row_NumOfQuestions['NumOfQuestions'];
		} while ($row_NumOfQuestions = mysql_fetch_assoc($NumOfQuestions));
		mysql_free_result($NumOfQuestions);
		
		$_SESSION['QuestionInLevel'] = $QinLevel;
		$_SESSION['TotalQuestionsRequired'] = $sum;	
	}
}
?>
<?php
/*
	Answer submitted ? 
*/
if(!isset($_POST['studentanswer']) && $_SESSION['initial'] != 1 ){
	print 1;
	exit;
}

if(isset($_POST['studentanswer'])){	
	$insertSQL = sprintf("INSERT INTO `$answertable` (`user`, `QuestionID`, `AnswerID`, `stage`, `level`, `test`, `identifier`) VALUES (%s, %s, %s, %s, %s, %s, %s)",
				 GetSQLValueString($_SESSION['MM_Userid'], "int"),
				 GetSQLValueString($_SESSION['qid'], "int"),
				 GetSQLValueString($_POST['studentanswer'], "int"),
				 GetSQLValueString($_SESSION['Stage'], "int"),
				 GetSQLValueString($_SESSION['Level'], "int"),
				 GetSQLValueString($_SESSION['Testid'], "int"),
				 GetSQLValueString($_SESSION['testidentifer'], "text"));
	
	if($debug && isset($log)) $log->debug($insertSQL);
	
	mysql_query($insertSQL, $cavoconnection) or die(mysql_error());	
	
	unset($_SESSION['qid']);

	$_SESSION['TotalQuestionTested'] = $_SESSION['TotalQuestionTested']+1;	
	
	if($_SESSION['Testid']<=3){
		$_SESSION['LevelQuestionTested'] = $_SESSION['LevelQuestionTested']+1;
	}
}

/*
	Test complete?
*/

if($debug && isset($log)) $log->debug("TotalQuestionTested = ".$_SESSION['TotalQuestionTested']);


if($_SESSION['TotalQuestionTested'] > $_SESSION['TotalQuestionsRequired']){
	$_SESSION['Endtime'] = time() + microtime();
	$_SESSION['Duration'] = $_SESSION['Endtime'] - $_SESSION['Starttime'];
	unset($_SESSION['Endtime']); unset($_SESSION['Starttime']);
	print 2;
	exit;
}

$testedID = $_SESSION['TestedIDs'];
$TestIdentifier = $_SESSION["testidentifer"];

if($_SESSION['Testid']<=3){
/*
	Level Upgrading ?
*/
//Get number of required questions in current level
$Zt = $_SESSION['QuestionInLevel'];
$LevelQuestionsRequired = $Zt[$_SESSION['Level']];
if($_SESSION['LevelQuestionTested'] > $LevelQuestionsRequired){
	unset($_SESSION['LevelQuestionTested']);
	
	//level upgrade. true or false?
	$levelup = getPassPercentage($cavoconnection, $_SESSION['MM_Userid'], $_SESSION['Testid'], $LevelQuestionsRequired, $_SESSION['Level'], $_SESSION['Stage'], $TestIdentifier);
	
	$_SESSION['Level'] = ($levelup)? ($_SESSION['Level'] +1) : $_SESSION['Level'];
	$_SESSION['Stage'] = $_SESSION['Stage'] + 1;
	$_SESSION['LevelQuestionTested'] = 1;
}

/*
	Generate questions
*/
$viewlog=false;
$choice = generator($cavoconnection,$_SESSION['Level'],$_SESSION['Testid'],$_SESSION['Testtype'], $pyweight, $threshold, $questionid, $question, $testedID,$viewlog,$deubg,$log);
}else{
$choice = cgenerator($cavoconnection,$_SESSION['Testid'],$_SESSION['Testtype'],$questionid,$question,$testedID,$debug,$log);	
}


$_SESSION['TestedIDs'] = $testedID;
$_SESSION['qid'] = $questionid;

//control if this is the first time we generate question
//set to 0 = not the first time
$_SESSION['initial'] = 0;
?>
<?php
/*
	Output json data
*/
$data=array();
$data['num'] = $_SESSION['TotalQuestionTested'];
$data['question'] = $question;
$data['choice'] = $choice;
$data['qtot'] = $_SESSION['TotalQuestionsRequired'];


print json_encode($data);;
?>