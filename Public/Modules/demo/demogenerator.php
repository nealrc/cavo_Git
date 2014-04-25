<?php 
require_once('../../../Private/config/config.php');
require_once('../../../Private/class/procs.php');
if(!session_start()){
	session_start();
}
?>
<?php
/*
	Initialize Session Variables
*/

if(isset($_REQUEST['mytestid'])){
	require_once('clearsession.php');
}

//get the new test id and name
$_SESSION['Testid'] = isset($_SESSION['Testid'])?$_SESSION['Testid']:$_REQUEST['mytestid'];
$_SESSION['Testname'] = isset($_SESSION['Testname'])?$_SESSION['Testname']:$_REQUEST['mytestname'];

//count number of questions been tested
$_SESSION['TotalQuestionTested'] = isset($_SESSION['TotalQuestionTested'])?$_SESSION['TotalQuestionTested']:0;

//store all the tikuid been tested during test
$_SESSION['TestedIDs'] = isset($_SESSION['TestedIDs'])?$_SESSION['TestedIDs']:array();

//number of correct answer
$_SESSION['Correct'] = isset($_SESSION['Correct'])?$_SESSION['Correct']:0;

//control if this is the first time we generate question
//set to 1 = IS the first time
$_SESSION['initial'] = isset($_SESSION['initial'])?$_SESSION['initial']:1;

// store test information
$_SESSION['rqid'] = isset($_SESSION['rqid'])?$_SESSION['rqid']:array();
$_SESSION['raid'] = isset($_SESSION['raid'])?$_SESSION['raid']:array();

/*
	Settigns
*/

//the following 2 variables will be updated by the generator function
//ID of test subject
$questionid = 0;
//DISPLAY QUESTION IN FORM
$question 	= '';

//multiple choices array
$choice 	= array();
$testedID 	= array();
$Zt 		= array();

//pinyin weight
$pyweight = ($testtype == 'PY')?0.9 : 0.25;	

//similarity threshold
// $threshold = 0.45;
$threshold = 0;

$tikutable = "tiku_cavo_test";
$leveltable = "cavo_level";
$answertable = "test_answers";

$response = '';
?>
<?php
/*
	Answer submitted ? 
*/
if(!isset($_POST['studentanswer'])&&$_SESSION['initial']!= 1){
	$r['status'] = 1;
	print json_encode($r);
	exit;
}
if(isset($_POST['studentanswer'])){
	$rqid = $_SESSION['rqid'];
	$raid = $_SESSION['raid'];
	
	$rqid[] = $_SESSION['qid'];
	$raid[] = $_POST['studentanswer'];
	
	$_SESSION['rqid'] = $rqid;
	$_SESSION['raid'] = $raid;
	
	$_SESSION['Correct'] = ($_SESSION['qid']==$_POST['studentanswer'])?($_SESSION['Correct']+1):$_SESSION['Correct'];	
	unset($_SESSION['qid']);	
}

mysql_select_db($database_cavoconnection, $cavoconnection);
mysql_query("SET NAMES utf8");

/*
	Test complete?
*/
if($_SESSION['TotalQuestionTested'] >= $_SESSION['TotalQuestionsRequired']){
	$accuracy = round($_SESSION['Correct'] / $_SESSION['TotalQuestionsRequired'], 4)*100;
	
	$ids = implode(",", $_SESSION['rqid']).",".implode(",", $_SESSION['raid']);
	switch($_SESSION['Testid']){
		case 1: $type = 'py'; break;
		case 2: $type = 'en'; break;
		case 3: $type = 'cn'; break;
	}
	$query = "SELECT `id`, `word`, `$type` FROM `tiku_cavo_test` WHERE `id` in ($ids)";
	$result = mysql_query($query, $cavoconnection) or die(mysql_error());
	$rows = mysql_fetch_assoc($result);
	$nrows = mysql_num_rows($result);
	if($nrows > 0){
		do{
			$o[$rows['id']]['v'] = $rows['word'];
			$o[$rows['id']][$type] = $rows[$type];
		}while($rows = mysql_fetch_assoc($result));
	}	
	mysql_free_result($result);
	$n = count($_SESSION['rqid']);
	$pair=array();
	for($ii=0;$ii<$n;$ii++){
		if($_SESSION['rqid'][$ii] != $_SESSION['raid'][$ii])
			$pair[$ii] = '<li>Question (<b>'.($ii+1).'</b>) <b>'.
						$o[$_SESSION['rqid'][$ii]]['v'].'</b> : '.$o[$_SESSION['rqid'][$ii]][$type].
						"<br />Your Answer <b>".$o[$_SESSION['raid'][$ii]]['v'].'</b> : '.
							$o[$_SESSION['raid'][$ii]][$type]."</li>\n";
	}
	$r['status'] = 2;
	$r['accuracy'] = $accuracy;	
	if(count($pair)>0){
		$r['info']= "<ul>".implode("\n",$pair)."</ul>";
	}
	require_once('clearsession.php');	
	
	print_r($_SESSION['rqid']);
	print_r($_SESSION['raid']);
	
	print json_encode($r);
	exit;
}else{
	$_SESSION['TotalQuestionTested'] = $_SESSION['TotalQuestionTested']+1;	
}


/*
	Generate questions
*/

//generate random level
$level = mt_rand(1, 4);

// pool of items have been tested already
$testedID = $_SESSION['TestedIDs'];

//function generator($dbhandle, $level, $testid, $testtype, $pyweight, $similarityscale, &$rid, &$tq, &$questionIdCheck){
$choice = generator($cavoconnection, $level, $_SESSION['Testid'], $_SESSION['Testname'], $pyweight, $threshold, $questionid, $question, $testedID);

$_SESSION['TestedIDs'] = $testedID;
$_SESSION['qid'] = $questionid;

//control if this is the first time we generate question
//set to 0 = not the first time
$_SESSION['initial'] = 0;

/*
	Output json data
*/
$data = array();
$data['num'] = $_SESSION['TotalQuestionTested'];
$data['question'] = $question;
$data['choice'] = $choice;

$r['testing'] = 1;
$r['status'] = 3;
$r['data'] = $data;

print json_encode($r);;
?>