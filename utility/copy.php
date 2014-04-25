<?php
require_once('../Private/config/config.php');

if (mysqli_connect_errno()) {
	printf("Connect failed: %s\n", mysqli_connect_error());
	exit();
}
$icavoconnection->query("SET NAMES UTF8");

$query = "SELECT `tiku_id`, `cavo` from `cavo_level_init`";
if($result = $icavoconnection->query($query)){	
	while($rows = $result->fetch_assoc()){
		$data[$rows['tiku_id']] = $rows['cavo'];
	}
	$result->close();
}

foreach($data as $idd => $level){
	$qq="INSERT INTO `cavo_level` (`tiku_id`, `test`, `level`) VALUES 
		($idd, 1, $level),($idd, 2, $level),($idd, 3, $level)";
	$icavoconnection->query($qq);
	
	
	//echo sprintf("%s<br />\n", $qq);
}

echo count($data);
echo '  |  Done!';
?>