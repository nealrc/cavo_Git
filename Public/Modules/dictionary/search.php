<?php
require_once('../../../Private/config/config.php');
require_once('../../../Private/class/function.php');
?>
<?php
if(!isset($_POST['search']) || trim($_POST['search']) == ''){
	print 100;
}else{	
	$search = $_POST['search'];
	
	// maximum matching threshold
	if(isset($_POST['ming']) && is_numeric($_POST['ming']) && $_POST['ming'] > 0){
		$min = $_POST['ming'];
	}else{
		$min = 2;
	}

	if(isset($_POST['maxg']) && is_numeric($_POST['maxg']) && $_POST['maxg'] > 0){
		$max = $_POST['maxg'];
	}else{
		$max = 4;
	}
	
	if(strpos($search,'*')!==false){
		$search = str_replace('*','%',$search);
	}else{
		$search = "%".$search."%";
	}
	
	
	$lib_tiku = (isset($_POST['dict'])&&$_POST['dict']=='test')?'tiku_cavo_test':'tiku_cavo_dict';

	/* check connection */
	if (mysqli_connect_errno()) {
		printf("Connect failed: %s\n", mysqli_connect_error());
		exit();
	}
	$icavoconnection->query("SET NAMES UTF8");

	// build lexicon
	// $query = "SELECT `Tikuid`, TRIM(`Vocabulary`) AS 'voc', TRIM(`PY`) AS 'py', TRIM(`CN`) AS 'cn', TRIM(`EN`) AS 'en' FROM `".$lib_tiku."` WHERE `Vocabulary` LIKE '%$search%'";
	$query = "SELECT `id`, TRIM(`word`) AS 'voc', TRIM(`py`) AS 'py', TRIM(`cn`) AS 'cn', TRIM(`en`) AS 'en' FROM `".$lib_tiku."` 
			  WHERE `word` LIKE '$search' AND CHAR_LENGTH(TRIM(`word`))>=$min AND CHAR_LENGTH(TRIM(`word`)) <= $max ORDER BY CHAR_LENGTH(TRIM(`word`)) DESC";
	if($result = $icavoconnection->query($query)){	
		$c=0;
		while($rows = $result->fetch_assoc()){
			$data[$c]['v'] = $rows['voc'];
			$data[$c]['py'] = $rows['py'];
			$data[$c]['en'] = $rows['en'];
			$data[$c]['cn'] = $rows['cn'];
			$c++;
		}
		$result->close();
	}
	
	if(isset($data) && count($data)>0){
		print json_encode($data);
	}else{
		print 200;
	}
	
}
?>