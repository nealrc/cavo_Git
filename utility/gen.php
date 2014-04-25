<?php
require_once('../Private/config/config.php');
require_once('../Private/class/procs.php');
?>
<?php
/*
	get time weight and passrate
*/
function get_test_timeweight($db,$level){
	$query_timeweight = "SELECT `TimeWeight` FROM `base_level_def` WHERE `Level`=$level";
	$timeweight = mysql_query($query_timeweight, $db) or die(mysql_error());
	$row_timeweight = mysql_fetch_assoc($timeweight);

	$tw = $row_timeweight['TimeWeight'];
	mysql_free_result($timeweight);
	
	//echo 'time weight: '.$tw; 
	
	return $tw;	
}

function get_test_name($db,$test){
	$query = "SELECT `name` FROM `base_test_type` WHERE `id`=$test";
	$result = mysql_query($query, $db) or die(mysql_error());
	$row = mysql_fetch_assoc($result);

	$name = $row['name'];
	mysql_free_result($result);
	
	//echo 'test name: '.$name;
	
	return $name;	
}

function get_demo_user($db){
	$query="SELECT `Userid` FROM `user` WHERE `Membership` = 4 AND `University` = 15";
	$result = mysql_query($query, $db) or die(mysql_error());
	$rows = mysql_fetch_assoc($result);
	do{
		$users[] = $rows['Userid'];
	}while($rows = mysql_fetch_assoc($result));
	
	mysql_free_result($result);	
	
	//print_r($users);
	
	return $users;
}

function get_rand_date($start,$end){
    $days = round((strtotime($end) - strtotime($start)) / (60 * 60 * 24));
    $n = rand(0,$days);
    return date("Y-m-d",strtotime("$start + $n days"));
}

function get_level_totalq($db,$level){
	$query="SELECT `NumOfQuestions` FROM `base_level_def` WHERE `Level` = $level";
	$result = mysql_query($query, $db) or die(mysql_error());
	$rows = mysql_fetch_assoc($result);
	$numq = $rows['NumOfQuestions'];
	mysql_free_result($result);	
	
	//echo 'num q in level: '.$numq;
	
	return $numq;	
}

function get_level_passrate($db,$level){
	$query="SELECT `Passrate` FROM `base_level_def` WHERE `Level` = $level";
	$result = mysql_query($query, $db) or die(mysql_error());
	$rows = mysql_fetch_assoc($result);
	$rate = $rows['Passrate'];
	mysql_free_result($result);	
	
	//echo 'passrate: '.$rate;
	
	return $rate;
}
?>
<?php
mysql_select_db($database_cavoconnection, $cavoconnection);
mysql_query("SET NAMES utf8");

$tikutable = "tiku_cavo_test";
$leveltable = "cavo_level";
$answertable = "test_answers";

// num. of test to generate
$n = 50;

//num of test stages
$m = 4;


$difficulty = 0.2;

// weight
$pyweight = ($testtype == 'PY')?0.9:0.25;	
$threshold = 0;

// which user
$users = get_demo_user($cavoconnection);


$start_date = '2000-01-01';
$end_date = date('Y-m-d');

// one test/type/date
$test_dates=array(); 

$i = 0;
while($i<$n){
	
	$questionid=NULL; 
	$question = NULL;
	$testedID=array();
	
	$overall_test_total = 0;
	$overall_test_correct = 0;

	$starttime = time()+ microtime();

	// select random test
	$test_id = mt_rand(1,3);
	
	// get test name (type)
	$test_name = get_test_name($cavoconnection, $test_id);

	// get random user
	$current_user = $users[array_rand($users)];
		
	// generate random dates	
	do{
		$current_test_date = get_rand_date($start_date, $end_date);
	}while((isset($test_dates[$current_user][$test_id][$current_test_date])));
	
	$test_dates[$current_user][$test_id][$current_test_date]=1;
		
	//generate random test identifier
	$randstr = RandTestIdentifier(mt_rand(8, 16));

	$current_level = 1;
	
	for($z = 0; $z < $m; $z++){	//4 stages
		$level_total_questions = get_level_totalq($cavoconnection,$current_level);
		$level_pass_rate = get_level_passrate($cavoconnection,$current_level);		
				
		$overall_test_total+=$level_total_questions;
		
		$level_correct=0;
		
		$j=0;
		while($j < $level_total_questions){
			// generate test
			$choice = generator($cavoconnection, $current_level, $test_id, $test_name, $pyweight, $threshold, $questionid, $question, $testedID);
			
			// generate answer
			$a = mt_rand(1,10)/10;
			if($a >= $difficulty){
				$answer_id = $questionid;
			}else{
				$answer = $choice[array_rand($choice)];				
				$answer_id = $answer['id'];
			}


			$insertSQL = sprintf("INSERT INTO `$answertable` (`user`, `QuestionID`, `AnswerID`, `stage`, `level`, `test`,  `identifier`) VALUES (%d, %d, %d, %d, %d, %d, '%s')", $current_user, $questionid, $answer_id, ($z+1), $current_level,$test_id, $randstr);
			
			 mysql_query($insertSQL, $cavoconnection) or die(mysql_error());
			
			if($answer_id == $questionid){
				$level_correct++;
			}
			
			$j++;
		}	
				
		if($level_correct/$level_total_questions >= $level_pass_rate){
			$current_level = $current_level >=4?4:$current_level+1;
		}
		
		//printf('%d, %d,%f,level=%d<br />',$level_correct,$level_total_questions,$level_pass_rate,$current_level);
		
		$overall_test_correct += $level_correct;
	}
	
	//calculate score
	$endtime = time()+ microtime();;
	$duration = round($endtime - $starttime,0);	
	$datetime = date("Y-m-d H:i:s");
	
	
	// get time weight
	$timeweight = get_test_timeweight($cavoconnection, $current_level);
		
	//get test time for the specific
	$query_duration = "SELECT Max(`duration`) as max, Min(`duration`) as min 
						FROM `test_records` WHERE `test` = $test_id";
	$duration = mysql_query($query_duration, $cavoconnection) or die(mysql_error());
	$row_duration = mysql_fetch_assoc($duration);
	$totalRows_duration = mysql_num_rows($duration);	
	$MaxDuration = $row_duration['max'];
	$MinDuration = $row_duration['min'];
	mysql_free_result($duration);
	
	// calculate ratio
	$norm = ($duration - $MinDuration)/($MaxDuration - $MinDuration);
	$TimeFactor = ($norm > 0.5) ? 1 : abs($norm); // above average or not
	
	// overall accuracy
	$overall_accuracy = $overall_test_correct/$overall_test_total;
	
	//score
	$finalscore = getScore($overall_accuracy, $timeweight, $TimeFactor, $current_level);
	$finalscore = ($finalscore >=800) ? 800 : $finalscore;
	
	
	// store score into database
	$speak = mt_rand(0,10);
	$learn = mt_rand(0,10);	
	$insertSQL = sprintf("INSERT INTO `test_records` (`user`, `date`, `score`, `duration`, `test`, `LearningPeriod`, `SpeakingPeriod`, `identifier`) VALUES (%d, '%s', %d, %d, %d, %d, %d, '%s')",$current_user, $datetime, $finalscore, $duration, $test_id, $learn, $speak,$randstr);
	 mysql_query($insertSQL, $cavoconnection) or die(mysql_error());		
	
	
	printf("n=%d, Test = %s(%d), Date=%s, Accuracy=%f, Duration=%d, Score =%d, CAVO Level=%d<br />",($i+1), $test_name, $test_id, $current_test_date,$overall_accuracy,$duration,$finalscore,$current_level);
	
	$i++;
}
echo ' Done!';
?>