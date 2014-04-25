<?php 
require_once('../../../../Private/config/config.php');
require_once('../../../../Private/class/function.php');
?>
<?php require_once('authm.php'); ?>
<?php
$oper = $_POST['oper'];
$id = $_POST['id'];
$error='';

$fields = array(
	"test_name"=>array(
		'req' => 1, 
		'full' => 'Test name',
		'val' => NULL		
	),
	"test_category"=>array(
		'req'=>1, 
		'full' => 'Test category',
		'val' => NULL
	),
	"test_description"=>array(
		'req'=>0, 
		'full' => 'Test description',
		'val' => NULL
	),
	"date_start"=>array(
		'req'=>0, 
		'full' => 'Test start date',
		'val' => NULL
	),
	"date_end"=>array(
		'req'=>0, 
		'full' => 'Test end date',
		'val' => NULL
	),
	"test_active"=>array(
		'req'=>1, 
		'full' => 'Test activated',
		'val' => NULL
	)
);

foreach($fields as $field => $arr){
	if($arr['req'] == 1){
		if(isset($_POST[$field]) && ( ( is_numeric($_POST[$field]) && $_POST[$field] >=0 ) ||  (!is_numeric($_POST[$field]) && !empty($_POST[$field])) ) ){
			$fields[$field]['val'] = $_POST[$field];
			//$arr['val'] = $_POST[$field];
		}else{
			$error.=$arr['full'].' is required. <br />';
		}		
	}else{
		$fields[$field]['val'] = $_POST[$field];
	}
}

//print_r($fields);

if($error==''){
	mysql_select_db($database_cavoconnection, $cavoconnection);
	mysql_query("SET NAMES UTF8");

	//school test 
	$query = sprintf("UPDATE `school_test` SET `date_start`=%s,`date_end`=%s,`active`=%s WHERE `id` = %s ",
					GetSQLValueString($fields['date_start']['val'], 'text'),
					GetSQLValueString($fields['date_end']['val'], 'text'),
					GetSQLValueString($fields['test_active']['val'], 'int'), $id);
	$records = mysql_query($query, $cavoconnection) or die(mysql_error());
	
	$query = "SELECT `test` FROM `school_test` WHERE `id` = $id";
	$result = mysql_query($query, $cavoconnection) or die(mysql_error());
	$rows = mysql_fetch_assoc($result);
	$test_id = $rows['test'];
	
	// base test
	$query = sprintf("UPDATE `base_test` SET `test_category`=%s,`name`=%s,`description`=%s WHERE `id` = %s ",
					GetSQLValueString($fields['test_category']['val'], 'int'),
					GetSQLValueString($fields['test_name']['val'], 'text'),
					GetSQLValueString($fields['test_description']['val'], 'text'), $test_id);
	$records = mysql_query($query, $cavoconnection) or die(mysql_error());
	
	
	if($records){
		echo "success";
	}else{
		echo "Update failed";
	}
}else{
	echo $error;
}
?>
