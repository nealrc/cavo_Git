<?php
if (!function_exists("GetSQLValueString")) {
function GetSQLValueString($theValue, $theType, $theDefinedValue = "", $theNotDefinedValue = "") {
  $theValue = get_magic_quotes_gpc() ? stripslashes($theValue) : $theValue;
  $theValue = function_exists("mysql_real_escape_string") ? mysql_real_escape_string($theValue) : mysql_escape_string($theValue);

  switch ($theType) {
    case "text":
      $theValue = ($theValue != "") ? "'" . $theValue . "'" : "NULL";
      break;    
    case "long":
    case "int":
      $theValue = ($theValue != "") ? intval($theValue) : "NULL";
      break;
    case "double":
      $theValue = ($theValue != "") ? "'" . doubleval($theValue) . "'" : "NULL";
      break;
    case "date":
      $theValue = ($theValue != "") ? "'" . $theValue . "'" : "NULL";
      break;
    case "defined":
      $theValue = ($theValue != "") ? $theDefinedValue : $theNotDefinedValue;
      break;
  }
  return $theValue;
}
}
/*
	Array functions
*/
//sort associate array based on key
if (!function_exists("sortAssoc")){
function sortAssoc($arr, $order_by, $reverse=true, $flags=0){
    $named_hash = array();
     foreach($arr as $key => $fields){
             $named_hash["$key"] = $fields[$order_by];
	}
 
    if($reverse){
		arsort($named_hash,$flags=0) ;
    }else{ 
		asort($named_hash, $flags=0);
	}
    $sorted_records = array();
    foreach($named_hash as $key => $val){
           $sorted_records["$key"]= $arr[$key];
 	}
	
	return $sorted_records;
}
}

function ShuffleAssocArr($arr, $n=NULL){
	$numarg = func_num_args();
	$aux = array();
	$keys = array_keys($arr);
	shuffle($keys);
	$c=0;
	foreach($keys as $key){
		if($numarg >1){
			if($c==$n){ break;}
		}
		$aux[$key] = $arr[$key];		
		$c++;
	}	
	return $aux;
}
function arrray_subtract_key($arr, $key){
	$tmp=array();
	foreach($arr as $k => $v){
		if($k != $key){
			$tmp[] = $v;
		}
	}
	return $tmp;
}
?>
<?php
//customer design of similarity between vocabulary.
//based on levenshtein score of both vocabulary and pinyin
//impose different weight of py and vocabulary for different test
//weight is the weight of pinyin in all test
function simScore($basevoc,$basepy,$strvoc,$strpy,$weight){	
	//check length of vocabulary
	if(strlen($basevoc) != strlen($strvoc)){ 
		return 0; 
	}
	if($basevoc == $strvoc){ 
		return 0;
	}
	if($basepy == $strpy){ 
		return 0;
	}
	//number of matching characters
	$matchvoc = strlen($basevoc) - Levenshtein($basevoc, $strvoc);
	$matchpy = strlen($basepy) - Levenshtein($basepy, $strpy);	
	
	$total_match = $matchvoc+$matchpy;
	
	if($total_match>0){
		//percentage of each based on weight
		$vocp = $matchvoc*(1-$weight);
		$pyp = $matchpy*$weight;
		
		$score = round(($vocp+$pyp)/$total_match, 4);
	}else{
		$score=0;
	}
	
	return $score;
}
/*
	Test generator
*/
// relax similarity criteria if simscale = 0
function generator($dbhandle,$level,$testid,$testtype,$pyweight,$simscale,&$rid,&$tq,&$questionIdCheck,$viewlog=false,$debug=false,$log=NULL){	

	//tiku library table
	$tikutable = "tiku_cavo_test";
	$leveltable = "cavo_level";
	
	$str='';
	
	$activity= "BGN: generator";
	
	if($debug && isset($log)) $log->debug($activity);
	
	try{
	
		$activity = "Get pool size";
		/*
			pool size
		*/
		$tikupool = array();
		if(count($questionIdCheck)>0){
			$tested = array_keys($questionIdCheck);	
			$query_pool = "SELECT `tiku_id` FROM `$leveltable` WHERE `test`=$testid AND `level`=$level AND `tiku_id` NOT IN (".implode(",",$tested).")";
		}else{
			$query_pool = "SELECT `tiku_id` FROM `$leveltable` WHERE `test`=$testid AND `level`=$level";
		}
		
		if($debug && isset($log)) $log->debug($query_pool);
		
		//echo $query_pool;
		$pool = mysql_query($query_pool, $dbhandle) or die(mysql_error());
		$pool_size = mysql_num_rows($pool); 
		$row = mysql_fetch_assoc($pool);	
		do{
			//if(!in_array($row["Tikuid"], $questionIdCheck)){ $tikupool[]= $row["Tikuid"]; }		
			$tikupool[]= $row["tiku_id"];
		}while($row = mysql_fetch_assoc($pool));
		mysql_free_result($pool);
		
		$activity = "calculate partition size";	
		/*
			Partition		
			seperate the tikupool into partitions
			so that we can equally randomly select a test topic	 
		*/	
		$partition = array();	
		$parsize = 50;
		// number of partition
		$numpar = floor($pool_size / $parsize);	
		// reminder
		$parreminder = fmod($pool_size, $parsize);
		//build partition
		for($ii=0; $ii<$numpar; $ii++){
			$partition[] = array_slice($tikupool, $ii*$parsize, $parsize);
		}	
		if($parreminder > 0 ){		
			// index start with 0, size start with 1 !!
			$partition[]= array_slice($tikupool, $parsize*$numpar, $pool_size-$parsize*$numpar);
			$numpar=$numpar+1;
		}
		
		$str.=sprintf("partition size: %d<br />",$numpar);
		
		/*
			Settings
		*/	
		//keep copy of vocabulary attributes of the choices pool
		$vocyy = array();
		$enyy = array();
		$cnyy = array();
	
		$activity = "Get random id";
		
		// currently available tiku words
		// we ignored the ones have been taken already
		$queued_tikuId = $tikupool;	
		$find = false;	
		while(!$find && count($queued_tikuId)>0){
			#################################
			#       get random id           #
			#################################
			do{
				
				$activity = "get random id";
				
				// get next random tiku id
				$tikupool_rand_key = mt_rand(0,(count($queued_tikuId)-1));
				$rid = $queued_tikuId[$tikupool_rand_key];
				$queued_tikuId = arrray_subtract_key($queued_tikuId, $tikupool_rand_key);
				
				$str.=sprintf("check rand tiku id: %d<br />",$rid);		
						
				// get info
				$seedid_query = "SELECT * FROM `$tikutable` WHERE `id` = $rid";
					
					if($debug && isset($log)) $log->debug($query_pool);
					
				$seedid = mysql_query($seedid_query, $dbhandle) or die(mysql_error());
				$row1 = mysql_fetch_assoc($seedid);
				
				$tvoc = format($row1["word"]);
				$tpy = format($row1["py"]);
				$ten = format($row1["en"]);
				$tcn = format($row1["cn"]);			
				$testqtype = ("PE"==$testtype)? $ten : format($row1[$testtype]);			
				mysql_free_result($seedid);
			}while(isset($vocyy[$tvoc]) && count($queued_tikuId)>0); // make sure the vocabulary has not been tested before
	
			$vocyy[$tvoc]=1;
			$enyy[$ten] = 1;
			$cnyy[$tcn] = 1;
			
			$activity = "calculate similarity score";
			/*
				set similarity criteria
			*/
			$voclen = mb_strlen($tvoc, "UTF-8");
			
			if($simscale > 0){		
				$firsthalf = mb_substr($tvoc, 0, floor($voclen/2), "UTF-8");
				$sechalf = mb_substr($tvoc, floor($voclen/2), $voclen-floor($voclen/2), "UTF-8");
				$str.=sprintf("word found:%s, len:% 3d, 1st 1/2:%s. 2nd 1/2: %s<br />",$tvoc,$voclen,$firsthalf,$sechalf);
			}else{
				$str.=sprintf("word found:%s, len:% 3d<br />",$tvoc,$voclen);
			}
			
			$activity = "get random partition";
			
			##################################
			#       search random partition  #
			##################################		
			// initialize choicepool when start over a new random id
			$choicepool = array();
			$queued_partition = $partition;		
			while(!$find && count($queued_partition)>0){
				// get next random partition
				$parindex = mt_rand(0,(count($queued_partition)-1));
				$curr_par = $queued_partition[$parindex];
				$curr_searchable_ids = implode(",",$curr_par);
	
				$str.=sprintf("Rand par selected: % 3d, Queued par left:% 3d<br />",
					   $parindex,count($queued_partition),$tvoc);
	
				// do search
				if($simscale > 0){			
					$choiceid_query = "SELECT * FROM `$tikutable` WHERE 
						(`word` LIKE '$firsthalf%' OR `word` LIKE '%$sechalf') AND 
						`id` IN (".$curr_searchable_ids.")";	
				}else{
					$choiceid_query = "SELECT * FROM `$tikutable` WHERE CHAR_LENGTH(`word`) = $voclen	
						AND `id` IN (".$curr_searchable_ids.")";	
				}
				
					if($debug && isset($log)) $log->debug($choiceid_query);
				
				$choiceid = mysql_query($choiceid_query, $dbhandle) or die(mysql_error());
				$row2 = mysql_fetch_assoc($choiceid);
				$totalRows_choiceid = mysql_num_rows($choiceid); 		
				
				if($totalRows_choiceid > 0){
					$str.=sprintf("possible # of candidate:%3d<br />",$totalRows_choiceid);				
					do{				
						$ccid 	= $row2["id"];
						$ccvoc 	= format($row2["word"]);
						$ccpy 	= format($row2["py"]);
						$ccen 	= format($row2["en"]);
						$cccn 	= format($row2["cn"]);
						$cctype = ("PE"==$testtype)? $ccen : format($row2[$testtype]);
						
						if(!isset($questionIdCheck[$ccid])&&!isset($cnyy[$cccn])&&!isset($enyy[$ccen])&&!isset($vocyy[$ccvoc])){
							
							if($simscale > 0){						
								$simScore = simScore($tvoc,$tpy,$ccvoc,$ccpy,$pyweight);						
								$str.=sprintf("candidate:%s, score: %3.4f, ",$ccvoc,$simScore);
								
								if($simScore >= $simscale){
									$choicepool[$ccid] = array('voc'=>$ccvoc,'py'=>$ccpy,'testtype' => $cctype,'sim'=>$simScore);							
									$vocyy[$ccvoc] = 1;
									$enyy[$ccen] = 1;
									$cnyy[$cccn] = 1;	
									
									$str.="accept<br />";
								}else{
									$str.="reject<br />";
								}						
							}else{
								$choicepool[$ccid] = array('voc'=>$ccvoc,'py'=>$ccpy,'testtype' => $cctype,'sim'=>1);							
								$vocyy[$ccvoc] = 1;
								$enyy[$ccen] = 1;
								$cnyy[$cccn] = 1;	
							}
						}
					}while($row2=mysql_fetch_assoc($choiceid));
					
					$find = (count($choicepool)>=4)?true:false;
				}else{
					$find = false;
				}
				mysql_free_result($choiceid);
				
				// reset queued partition
				$queued_partition = arrray_subtract_key($queued_partition, $parindex);			
			} // end - while(!$find && count($queued_partition)>0)
			
			$str.='<hr />';
		} // end -- while(!$find && count($queued_tikuId)>0)
	
	
		if($viewlog){
			printf('Generator Log<br />%s<br />', $str);
		}
		
		if($debug && isset($log)) $log->debug($str);
	
		if(count($queued_tikuId)==0){
			printf("Fatal error: Tikupool find no appropriate word for current difficulty level %d", $level);
			exit;		
		}else{
			$questionIdCheck[$rid] = 1;	
			$tq = $tvoc;
			//build multiple choices
			// only going to use a random selection again
			// since all choices are above the threshold
			// we shoudl accept all
			// no need to sort by simScore
			$choice = ShuffleAssocArr($choicepool, 4);
			$choice[$rid] = array('voc'=>$tvoc,'py'=>$tpy,'testtype' => $testqtype,'sim'=>1);
			$choice = ShuffleAssocArr($choice);
			
			$c=0;
			$finalch = array();
			foreach($choice as $id => $arr){
				$finalch[$c]['id'] = $id;
				$finalch[$c]['testtype'] = $arr['testtype'];
				$c++;
			}
			if(4==$testid){ // If it's a py-en test, question should be py @tony
				$tq = $testq['py'];
			}
			return $finalch;	
		}
		
		if($debug && isset($log)) $log->debug("END: generator");
		
	}catch(Exception $e){
		if($debug && isset($log)) {
			$log->debug(sprintf("Exception occurred while doing %s. Details: %s", $activity, $e->getMessage()));
			if($debug && isset($log)) $log->debug("END: generator");
		}				
		throw new Exception("Exception occurred while doing ", $activity, ". Details: ",  $e->getMessage());		
	}
}

// custom test generator 
function cgenerator($dbhandle,$testid,$testtype,&$rid,&$tq,&$questionIdCheck,$debug=false,$log=NULL){

	//tiku library table
	$tikutable = "tiku_cavo_test";
	$leveltable = "cavo_level";

	$activity= sprintf("BGN: cgenerator");
	
	try{
		if($debug && isset($log)) $log->debug("---------------------------");
		if($debug && isset($log)) $log->debug($activity);
		
		$activity = "get test pool size";
			
		// test subject
		$items = array();
		if(count($questionIdCheck)>0){
			$tested = array_keys($questionIdCheck);
			sort($tested);
			$query_pool = "SELECT `tiku_id` FROM `$leveltable` WHERE `test`=$testid AND `tiku_id` NOT IN (".implode(",",$tested).")";
		} else {
			$query_pool = "SELECT `tiku_id` FROM `$leveltable` WHERE `test`=$testid";
		}
		
		if($debug && isset($log)) $log->debug($query_pool);
			
		$pool = mysql_query($query_pool, $dbhandle) or die(mysql_error()."\r\n activity = ".$activity.". query=".$query);
		$pool_size = mysql_num_rows($pool); 
		$row = mysql_fetch_assoc($pool);	
		do{
			$items[]= $row["tiku_id"];
		}while($row = mysql_fetch_assoc($pool));
		mysql_free_result($pool);	
		
		
		if($debug && isset($log)) $log->debug("test pool size: ".$pool_size);
		
		$activity = "get overall tiku size";
		
		/*
			find total size
		*/
		$query_pool = "SELECT count(*) AS size FROM `$tikutable`";
		//echo $query_pool;
		if($debug && isset($log)) $log->debug($query_pool);
		
		$pool = mysql_query($query_pool, $dbhandle) or die(mysql_error()."\r\n activity = ".$activity.". query=".$query);
		$row = mysql_fetch_assoc($pool);
		$tot = $row['size'];
		mysql_free_result($pool);
		
		if($debug && isset($log)) $log->debug("overall pool size: ".$tot);
	
		
		$activity = "get random test item";
		
		// currently available tiku words
		// we ignored the ones have been taken already
		$queued_tikuId = $items;	
		$choice = array();
		$find = false;	
				
		while(!$find){
			$tikupool_rand_key = NULL;
			
			if($debug && isset($log)) $log->debug("queued_tikuId = ".implode(",",$queued_tikuId));
			
			do{
				$tikupool_rand_key = array_rand($queued_tikuId);				
				if($debug && isset($log)) $log->debug("tikupool_rand_key = ".$tikupool_rand_key);				
			}while(!isset($queued_tikuId[$tikupool_rand_key]));
			
			if(isset($tikupool_rand_key)){				
				$rid = $queued_tikuId[$tikupool_rand_key];
				if($debug && isset($log)) $log->debug("rid = ".$rid);

				$queued_tikuId = arrray_subtract_key($queued_tikuId, $tikupool_rand_key);
				if($debug && isset($log)) $log->debug("removing rid ".$rid." from queued_tikuid");
				
				$seedid_query = "SELECT * FROM `$tikutable` WHERE `id` = $rid";
				if($debug && isset($log)) $log->debug($seedid_query);
				
				$seedid = mysql_query($seedid_query, $dbhandle) or die(mysql_error()."\r\n activity = ".$activity.". query=".$query);
				$row1 = mysql_fetch_assoc($seedid);
				
				$tvoc = format($row1["word"]);
				$tpy = format($row1["py"]);
				$ten = format($row1["en"]);
				$tcn = format($row1["cn"]);	
				$ttype = format($row1[$testtype]);
				$voclen = mb_strlen($tvoc, "UTF-8");
				
				mysql_free_result($seedid);	
				
				$limit = 100;
				$loop = 0;
				
				while($loop < $limit){
					// get random search starting point
					do{
						$start = mt_rand(1, $tot);	
					}while($tot - $start < 2*$limit);
					$query = "SELECT `id`, `word`, `$testtype` FROM `$tikutable` WHERE CHAR_LENGTH(`word`) = $voclen AND `word` != '$tvoc' AND `id`>=$start*RAND() ";
		
					if(count($questionIdCheck)>0){
						$tested = array_merge($items, array_keys($questionIdCheck)); 
					}else{
						// do not use test questions as choices
						$tested = $items;	
					}					
					sort($tested);
					$query.= "AND `id` NOT IN (".implode(',', $tested).")"; 
					
					
					if($debug && isset($log)) $log->debug($query);
					
					$result = mysql_query($query, $dbhandle) or die(mysql_error()."\r\n activity = ".$activity.". query=".$query);
					$row2 = mysql_fetch_assoc($result);
					$totalRows_choiceid = mysql_num_rows($result);
					if($totalRows_choiceid > 0 ){
						do{
							if(count($choice) >= 4){
								$find = true;
								break;
							}
							$choice[$row2["id"]] = array('voc'=>$row2["word"], $testtype => format($row2[$testtype]));					
						}while($row2=mysql_fetch_assoc($result));
					}
					mysql_free_result($result);
					
					$loop++;
					
					if(count($choice) >= 4){ 
						$choice[$rid] = array('voc'=>$tvoc, $testtype => $ttype);
						$find = true;
						break;
					}
				}
			}
		}
	
		// update
		$tq = $tvoc;
		
		$activity = "shuffle array";
		
		$choice = ShuffleAssocArr($choice);
		
		
		$activity ="build output";
		$c=0;
		$finalch = array();
		foreach($choice as $id => $arr){
			$finalch[$c]['id'] = $id;
			$finalch[$c]['testtype'] = $arr[$testtype];
			$questionIdCheck[$id] = 1;
			$c++;
		}
		return $finalch;	
		
		if($debug && isset($log)) $log->debug("END: cgenerator");
	
	}catch(Exception $e){
		if($debug && isset($log)) {
			$log->debug(sprintf("Exception occurred while doing %s. Details: %s", $activity, $e->getMessage()));
			if($debug && isset($log)) $log->debug("END: cgenerator");
		}				
		throw new Exception("Exception occurred while doing ", $activity, ". Details: ",  $e->getMessage());		
	}	
	
}

function format($str){
	$str = trim($str);
	if(strpos($str, ";")!==false){
		$str = str_replace(";", "; ", $str);
	}
	return $str;
}
/*
	The following functions used in formal test
*/
// evaluate passrate
function getPassPercentage($dbhandle, $uid, $testid, $numOfQuestionsRequired, $level, $stage, $identifier){
	//get number of correct answers
	$query_rightanswer = "SELECT count(*) as num FROM `test_answers` WHERE `user` = $uid AND `QuestionID` = AnswerID AND `stage` = $stage AND `test` = $testid AND `Identifier` = '$identifier'";
	$rightanswer = mysql_query($query_rightanswer, $dbhandle) or die(mysql_error());
	$row_rightanswer = mysql_fetch_assoc($rightanswer);
	$total = $row_rightanswer['num'];

	//get designed passrate
	$query_passrate = "SELECT `Passrate` FROM `base_level_def` WHERE `Level` = $level";
	$passrate = mysql_query($query_passrate, $dbhandle) or die(mysql_error());
	$row_passrate = mysql_fetch_assoc($passrate);		
	$mypassrate = $row_passrate['Passrate'];
	
	
	$percentage = round($total/$numOfQuestionsRequired,4);
	$t = ($percentage >= $mypassrate) ? true : false;	
	
	mysql_free_result($passrate);	
	mysql_free_result($rightanswer);
		
	return $t;
}
//generate test identifier
function RandTestIdentifier($length) {
	$chars = "1234567890abcdefghijkmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ";
	$i = 0;
	$str = "";
	while ($i <= $length) {
		$str .= $chars{mt_rand(0,strlen($chars))};
		$i++;
	}
	return $str;
}
//calculate final score
function getScore($accuracy, $basetimeweight, $timefactor, $level){
	$timeweight = $basetimeweight * $timefactor;
	$timepoints = 800 * $timeweight;
	$accuracypoints = 800 * (1 - $timeweight) * $accuracy;
	$score = $timepoints + $accuracypoints;	
	return round($score, 0);
}
?>
