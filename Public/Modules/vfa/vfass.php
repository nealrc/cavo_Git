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
/* check connection */
if (mysqli_connect_errno()) {
	printf("Connect failed: %s\n", mysqli_connect_error());
	exit();
}

$lib_tiku = (isset($_GET['dict'])&&$_GET['dict']=='test')?'tiku_cavo_test':'tiku_cavo_dict';

$l = explode('_',$_GET['l']);
$ll=array();
foreach($l as $k => $v){	
	$ll[] = cavo_decrypt($v);
}


$icavoconnection->query("SET NAMES UTF8");

$sql = "SELECT `id`, `word`, `py`, `en`, `cn` FROM `".$lib_tiku."` WHERE `id` IN (".implode(',',$ll).")";
if($result = $icavoconnection->query($sql)) {
	$c=0;
	while ($rows = $result->fetch_assoc()) {
		if(isset($data[$rows['word']])){
			$c++;
		}
		$data[$rows['word']][$c]['id'] = $rows['id'];
		$data[$rows['word']][$c]['py'] = $rows['py'];
		$data[$rows['word']][$c]['en'] = $rows['en'];
		$data[$rows['word']][$c]['cn'] = $rows['cn'];		
	}
	$result->close();
}

$tikuids=array();
$tiku_test=array();

if($lib_tiku != 'tiku_cavo_test'){
	$words = array_keys($data);
	array_walk($words, 'gensqlstring');	
	$sql = "SELECT `id`, `word`, `py`, `en`, `cn` FROM `tiku_cavo_test` WHERE `word` IN (".implode(',', $words).")";
	if($result = $icavoconnection->query($sql)) {
		while ($rows = $result->fetch_assoc()) {
			$tikuids[] = $rows['id'];			
			$tiku_test[$rows['id']]['word'] = $rows['word'];
			$tiku_test[$rows['id']]['py'] = $rows['py'];
			$tiku_test[$rows['id']]['en'] = $rows['en'];
			$tiku_test[$rows['id']]['cn'] = $rows['cn'];
		}
		$result->close();		
	}	
}else{
	$tikuids = $ll;	
}

if(count($tikuids)>0){
	$sql2 = "SELECT a.`tiku_id`, c.`name` AS test, a.`level` FROM `cavo_level` AS a 
			LEFT JOIN `base_test` AS b ON (a.`test`=b.`id`) 
			LEFT JOIN `base_test_type` AS c ON (b.`test_type` = c.`id`)
			WHERE `tiku_id` in (".implode(',',$tikuids).")";
	if($result = $icavoconnection->query($sql2)) {
		while ($rows = $result->fetch_assoc()) {			
			if(count($tiku_test)>0){			
				foreach($data[$tiku_test[$rows['tiku_id']]['word']] as $k => $arr){
					if($arr['py'] == $tiku_test[$rows['tiku_id']]['py'] &&
					   $arr['en'] == $tiku_test[$rows['tiku_id']]['en'] && 
					   $arr['cn'] == $tiku_test[$rows['tiku_id']]['cn'])
					{
						$data[$tiku_test[$rows['tiku_id']]['word']][$k]['level'][$rows['test']] = $rows['level'];
					}
				}
			}else{
				foreach($data as $w => $arr){
					foreach($arr as $k => $subarr){
						if($subarr['id'] == $rows['tiku_id']){
							$data[$w][$k]['level'][$rows['test']] = $rows['level'];
						}
					}
				}
				
			}
		}
		$result->close();
	}
}
$icavoconnection->close();	

if(isset($data) && count($data)>0){
	$ct = '';
	foreach($data as $w => $a){
		foreach($a as $k => $arr){
		$ct.='<div class="tooltip-content"><ul class="ui-widget-content ui-corner-all">';
		$ct.='<li class="py">'.$arr['py'].'</li>';
		if(strlen($arr['cn'])>0){
			$ct.='<li>'.$arr['cn'].'</li>';
		}
		$ct.='<li>'.$arr['en'].'</li>';
		
		if(isset($arr['level'])){
			$ct.='<li><strong>CAVO Level:</strong> ';
			$ct.='<span class="dl'.$arr['level']['py'].'">py('.$arr['level']['py'].')</span>&nbsp;';
			$ct.='<span class="dl'.$arr['level']['en'].'">en('.$arr['level']['en'].')</span>&nbsp;';
			$ct.='<span class="dl'.$arr['level']['cn'].'">cn('.$arr['level']['cn'].')</span>&nbsp;';			
			$ct.='</li>';
		}		
		$ct.='</ul></div>';
		$ct.='';
	}
	}
	
	print $ct;
}else{
	print 'Nothing found.';
}
exit;
?>