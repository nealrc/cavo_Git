<?php
/*
	set up tiku item relation with database
*/
require_once('../Private/config/config.php');

if (mysqli_connect_errno()) {
	printf("Connect failed: %s\n", mysqli_connect_error());
	exit();
}

ini_set('max_execution_time', 300); 

$icavoconnection->query("SET NAMES UTF8");

$query = "SELECT `id`, `word` from `tiku_cavo_test`";
if($result = $icavoconnection->query($query)){	
	while($rows = $result->fetch_assoc()){
		$cavo[$rows['id']] = $rows['word'];
	}
}
$result->free();

$query = "SELECT `id`, `word` from `tiku_hsk`";
if($result = $icavoconnection->query($query)){	
	while($rows = $result->fetch_assoc()){
		$hsk[$rows['id']] = $rows['word'];
	}
}
$result->free();

$query = "SELECT `id`, `word` from `tiku_freq`";
if($result = $icavoconnection->query($query)){	
	while($rows = $result->fetch_assoc()){
		$freq[$rows['id']] = $rows['word'];
	}
}
$result->free();

foreach($cavo as $id => $word){
	$tmp=array();
	if(in_array($word, $hsk)){
		$tmp[]="($id, 1)";
	}
	if(in_array($word, $freq)){
		$tmp[]="($id, 2)";
	}
	$tmp[]="($id, 3)";
	
	$query = "INSERT INTO `base_database_relation` (`tiku_id`, `database`) 
			  VALUES ".implode(",", $tmp);
	$icavoconnection->query($query);
}

echo ' Done!';
?>