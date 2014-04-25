<?php
/*
	This function merges tiku_cavo_freq and tiku_cavo_hsk database into tiku_cavo_test database
	It also initializes levels for cavo_level_init table. All test type will have same cavo level 
	at initial stage. After cavo collects enough test data, we can update cavo levels for cavo_level table.
*/
require_once('../Private/config/config.php');

if (mysqli_connect_errno()) {
	printf("Connect failed: %s\n", mysqli_connect_error());
	exit();
}
$icavoconnection->query("SET NAMES UTF8");

$query = "SELECT * from `tiku_cavo_hsk`";
if($result = $icavoconnection->query($query)){	
	while($rows = $result->fetch_assoc()){
		if(!isset($data[$rows['word']]) || 
			(!in_array($rows['cn'],$data[$rows['word']]['cn']) && 
			!in_array($rows['en'], $data[$rows['word']]['en']))){
			$data[$rows['word']]['py'][] = $rows['py'];
			$data[$rows['word']]['cn'][] = $rows['cn'];
			$data[$rows['word']]['en'][] = $rows['en'];
			$data[$rows['word']]['level']['hsk'] = $rows['level'];
		}
	}
	$result->close();
}

$query = "SELECT * from `tiku_cavo_freq`";
if($result = $icavoconnection->query($query)){	
	while($rows = $result->fetch_assoc()){
		if(!isset($data[$rows['word']]) || 
			(!in_array($rows['cn'],$data[$rows['word']]['cn']) && 
			!in_array($rows['en'], $data[$rows['word']]['en']))){
			$data[$rows['word']]['py'][] = $rows['py'];
			$data[$rows['word']]['cn'][] = $rows['cn'];
			$data[$rows['word']]['en'][] = $rows['en'];
			$data[$rows['word']]['level']['freq'] = $rows['level'];
		}else{
			$data[$rows['word']]['level']['freq'] = $rows['level'];
		}
	}
	$result->close();
}


/*

$rand_id=array();
do{	
	$n = mt_rand(0, count($data)-1);
	if(!in_array($n, $rand_id)){
		$rand_id[] = $n;
	}	
}while(count($rand_id)<101);

$voc = array_keys($data);

$display_data=array();
for($j=0;$j<100;$j++){
	$word = $voc[$rand_id[$j]];
	
	$display_data[$word] = $data[$word];
}

echo count($data).'<br />';

print_r($display_data);

*/

foreach($data as $voc => $arr){
	$size = count($arr['cn']);	
	for($i=0;$i<$size;$i++){
		$querys = sprintf("INSERT INTO `tiku_cavo_test` (`word`, `py`, `cn`, `en`) VALUES ('%s','%s','%s','%s')",$voc,$arr['py'][$i],$arr['cn'][$i],$arr['en'][$i]);
		$result = $icavoconnection->query($querys);
		if($result){
			$tiku_id = $icavoconnection->insert_id;
			
			$hsk = isset($arr['level']['hsk'])?$arr['level']['hsk']:0;
			$freq = isset($arr['level']['freq'])?$arr['level']['freq']:0;
			$cavol = $hsk!=0?$hsk:$freq;
			// initialize levels
			$queryl = sprintf("INSERT INTO `cavo_level_init` (`tiku_id`, `hsk`, `freq`, `cavo`) VALUES (%d, %d, %d, %d)", $tiku_id, $hsk, $freq, $cavol);
			$icavoconnection->query($queryl);
		}
	}	
}

echo 'Done!';
?>