<?php
require_once('../../../Private/config/config.php');
require_once('../../../Private/class/function.php');
?>
<?php
function prepareString($str) {
	$str = preg_replace("/[0-9a-zA-Z]/",'',$str);
	$str = mb_strtolower(trim(preg_replace('#[^\p{L}\p{N}]+#u', ' ', $str)));	
	return $str;
	//return trim(preg_replace('#\s\s+#u', ' ', preg_replace('#[^\12544-\65519]#u', ' ', $str) . ' ' . implode(' ', preg_split('#[\12544-\65519\s]?#u', $str, -1, PREG_SPLIT_NO_EMPTY))));
	//return trim(preg_replace('#\s\s+#u', '', preg_replace('#[^\12544-\65519]#u', '', $str) . '' . implode('', preg_split('#[\12544-\65519\s]?#u', $str, -1, PREG_SPLIT_NO_EMPTY))));
}
if (!function_exists("gensqlstring")){
function gensqlstring(&$val){
	$val = "'".$val."'";
}
}
?>
<?php
if(!isset($_POST['contents']) || trim($_POST['contents']) == ''){
	print 100;
	exit;
}else{
	$encoding="UTF-8"; 
	$max_article_length = 20000;
	
	$lib_tiku = (isset($_POST['dict'])&&$_POST['dict']=='test')?'tiku_cavo_test':'tiku_cavo_dict';
	
	$org_str = $_POST['contents'];
	$parse_str = $org_str;	
	
	// maximum matching threshold
	if(isset($_POST['maxg']) && is_numeric($_POST['maxg']) && $_POST['maxg'] > 0){
		$maxgram = $_POST['maxg'];
	}else{
		$maxgram = 8;
	}

	if(isset($_POST['ming']) && is_numeric($_POST['ming']) && $_POST['ming'] > 0){
		$mingram = $_POST['ming'];
	}else{
		$mingram = 2;
	}
	
	//fitering	
	$str = prepareString($org_str);
		
	$total_len = mb_strlen($str);
	$num_whitespace = mb_substr_count($str, ' ');
	$num_cn = $total_len - $num_whitespace;

	$info = '';	
	$info .= sprintf("Number of Chinese characters in original content: %s <br />", number_format($num_cn));

	if($num_cn > $max_article_length){
		$str = mb_substr($str,0,($max_article_length+$num_whitespace),$encoding);
		$trimmed = 1;
		$info .= "Only the first ".number_format($max_article_length)." Chinese characters will be processed.<br />";
	}else{
		$trimmed = 0;
	}
	
	$process_start = microtime(true);
	##############################################################

	/* check connection */
	if (mysqli_connect_errno()) {
		printf("Connect failed: %s\n", mysqli_connect_error());
		exit();
	}
	$icavoconnection->query("SET NAMES UTF8");

	// build lexicon
	// $query = "SELECT DISTINCT TRIM(`Vocabulary`) AS voc FROM `tiku` WHERE `Vocabulary` IS NOT NULL AND `CN` IS NOT NULL";
	// $query = "SELECT DISTINCT TRIM(`Vocabulary`) AS voc FROM `".$lib_tiku."` WHERE `Vocabulary` IS NOT NULL";
	$query = "SELECT DISTINCT TRIM(`word`) AS voc FROM `".$lib_tiku."` WHERE char_length(`word`)>=$mingram AND char_length(`word`)<=$maxgram";
	if($result = $icavoconnection->query($query)) {
		$nlex = $result->num_rows;
		$c=0;
		while ($rows = $result->fetch_assoc()) {
			$lib[$rows['voc']]=$c;
			$c++;
		}
		$result->close();
	}
	
	// matching
	$fstr=explode(" ", $str);
	foreach($fstr as $kk => $string){
		if(mb_strlen(trim($string),$encoding)>0){
			$len = mb_strlen($string, $encoding);
			if($len > 1){
				$pos = 0;
				while($pos < $len){
					$find = false;
					$gram = ($len-$pos)>=$maxgram?$maxgram:($len-$pos);
					while($gram >= $mingram){
						$seg = mb_substr($string,$pos,$gram,$encoding);						
						if(isset($lib[$seg]) ){
							$find = true;
							$matchs[] = $seg;
							break;
						}
						$gram--;
					}
					$pos = $find?($pos+$gram):($pos+1);
				}
			}
		}
	}
	
	// fetching	
	if(count($matchs)>0){		
		$freq = array_count_values($matchs);
		$checks = array_keys($freq);
		array_walk($checks, 'gensqlstring');
		$vocs = implode($checks,',');
		
		$sql = "SELECT `id`, `word`, TRIM(`py`) AS 'PY', TRIM(`cn`) AS 'CN', TRIM(`en`) AS 'EN' FROM `".$lib_tiku."` WHERE `word` IN ($vocs)";
		if($result = $icavoconnection->query($sql)) {
			while ($rows = $result->fetch_assoc()) {
				$len = mb_strlen($rows['word'],$encoding);
				$f = $freq[$rows['word']];
				
				if(!isset($data[$rows['word']])){
					$r = 0;
				}else{
					$r = count($data[$rows['word']]['v']);
				}
				$data[$rows['word']]['len']= mb_strlen($rows['word'],$encoding);
				$data[$rows['word']]['freq']= $freq[$rows['word']];
				$data[$rows['word']]['ids'][] = cavo_encrypt($rows['id']);
			}			
			$num = $result->num_rows;
			$result->close();
		}
	}
	
	// parsing
	if(isset($data) && count($data)>0){		
		foreach($data as $v => $row){
			$vfreq[$v] = $row['freq'];
			$vlen[$v] = $row['len'];
		}
		array_multisort($vfreq,SORT_DESC,$vlen,SORT_DESC,$data);
		
		$cc=0;
		foreach($data as $voc => $iinfo){
			$dd[$cc]['voc'] = $voc;
			$dd[$cc]['freq'] = $iinfo['freq'];
			$dd[$cc]['len'] = $iinfo['len'];
			$dd[$cc]['ids'] = implode('_', $iinfo['ids']);
			$cc++;
		}
	}
#######################################################
	$process_end = microtime(true);
	$time = round($process_end - $process_start, 2);
	$info .= "Total process time = $time (s)<br />";

	if(isset($dd) && count($dd) > 0){
		$sum=0;		
		$info .= sprintf("Number of unique keywords found: %d<br />", count($dd));
		$dat['status'] = $info;
		$dat['alen'] = mb_strlen($str,$encoding);
		$dat['trimmed'] = $trimmed;
		$dat['max'] = max(array_values($freq));
		$dat['data'] = $dd;
		print json_encode($dat);
	} else {
		$dat['status'] = "<h1>No keyword found.</h1><br />".$info;
		print json_encode($dat);
	}
}
?>