<?php require_once('../../../../Private/config/config.php');?>
<?php require_once('auth.php'); ?>
<?php
function sortAssoc($arr, $order_by, $descendent=true, $flags=0){
    $named_hash = array();
     foreach($arr as $key => $fields){
             $named_hash[$key] = $fields[$order_by];
	} 
    if($descendent){
		arsort($named_hash,$flags=0);
    }else{ 
		asort($named_hash, $flags=0);
	}
    $sorted_records = array();
    foreach($named_hash as $key => $val){
           $sorted_records[$key]= $arr[$key];
 	}	
	return $sorted_records;
}
function Strip($value){
	if(get_magic_quotes_gpc() != 0)  	{
    	if(is_array($value))  
			if ( array_is_associative($value) )
			{
				foreach( $value as $k=>$v)
					$tmp_val[$k] = stripslashes($v);
				$value = $tmp_val; 
			}				
			else  
				for($j = 0; $j < sizeof($value); $j++)
        			$value[$j] = stripslashes($value[$j]);
		else
			$value = stripslashes($value);
	}
	return $value;
}
function array_is_associative ($array){
    if ( is_array($array) && ! empty($array) )
    {
        for ( $iterator = count($array) - 1; $iterator; $iterator-- )
        {
            if ( ! array_key_exists($iterator, $array) ) { return true; }
        }
        return ! array_key_exists(0, $array);
    }
    return false;
}
?>
<?php
//
//use $_SESSION['MM_Userid'] for user's own preview
//use $_GET['id'] for instructor review student
//
$id = isset($_REQUEST['id'])?$_REQUEST['id']:NULL;

if(!isset($id)){
	echo "illegal connection, will quit.";
	exit;
}else{	
	$msg = array();	
	$msgco = 0;
	
	mysql_select_db($database_cavoconnection, $cavoconnection);
	mysql_query("SET NAMES UTF8");

	//get user id, test identifier
	$query = "SELECT a.`user`, a.`test`, a.`identifier`, b.`Firstname`, b.`Lastname` 
	FROM `test_records` AS a LEFT JOIN `user` AS b ON (a.`user` = b.`Userid`) WHERE `id` = $id";
	$result = mysql_query($query, $cavoconnection) or die(mysql_error());
	$rows = mysql_fetch_assoc($result);
	$num = mysql_num_rows($result);
	mysql_free_result($result);
	
	if($num >0){
		$user = $rows['user'];
		$test = $rows['test'];
		$idn = $rows['identifier'];
		
		//$stdname = ucfirst($rows['Firstname']).' '.ucfirst($rows['Lastname']);
		
		//testtype - Index 	Testid 	Name Description
		$query = "SELECT a.`id`, a.`name` AS TestName, b.`name` AS TestType FROM `base_test` AS a 
				  LEFT JOIN `base_test_type` AS b ON (a.`test_type`=b.`id`) WHERE a.`id` = $test";
		$result2 = mysql_query($query, $cavoconnection) or die(mysql_error());
		if($result2){
			$rows = mysql_fetch_assoc($result2);
			do{					
				$type = $rows['TestType'];
				$title = $rows['TestName'];
			}while($rows = mysql_fetch_assoc($result2));
		}
		mysql_free_result($result2);
		
		// get answers
		$query_answer = "SELECT 
							a.`QuestionID`, a.`AnswerID`,
							b.`word` AS question, 
							b.`$type` AS correctanswer, 
							c.`word` AS useranswer, 
							c.`$type` AS definition 
						FROM `test_answers` AS a  
							INNER JOIN `tiku_cavo_test` AS b ON (a.`QuestionID` = b.`id`) 
							INNER JOIN `tiku_cavo_test` AS c ON (a.`AnswerID` = c.`id`) 
						WHERE a.`identifier` = '$idn'";

		$answer = mysql_query($query_answer, $cavoconnection) or die(mysql_error());
		$row_answer = mysql_fetch_assoc($answer);
		$totalRows_answer = mysql_num_rows($answer);

		if($totalRows_answer > 0){
			$data1=array();
			$data2=array();			
			$co1 = 0;
			$co2=0;			
			do {
				if($row_answer['QuestionID'] != $row_answer['AnswerID']){
					$data1[$co1]['q'] = $row_answer['question'];
					$data1[$co1]['u'] = $row_answer['useranswer'];
					$data1[$co1]['a'] = $row_answer['correctanswer'];
					$data1[$co1]['d'] = $row_answer['definition'];
					$co1++;
				}else{
					$data2[$co2]['q'] = $row_answer['question'];
					$data2[$co2]['u'] = $row_answer['useranswer'];
					$data2[$co2]['a'] = $row_answer['correctanswer'];
					$data2[$co2]['d'] = $row_answer['definition'];
					$co2++;
				}
			}while($row_answer = mysql_fetch_assoc($answer));
		}else{			
			$msg['msg'][$msgco]['note'] = '<h2>Not question has been missed for this test.</h2>';
			$msgco++;
		}
		
		mysql_free_result($answer);
		
	}else{
		$msg['msg'][$msgco]['note'] = "No test record found by record id = $id";
		$msgco++;
	}
		
	if(count($msg) > 0){
		print json_encode($msg);
	}else{
		$data['t'] = $title;
		$data['m'] = count($data1);
		$data['c'] = count($data2);
		$data['wrong'] = $data1;
		$data['right'] = $data2;
		print json_encode($data);
	}
}
?>