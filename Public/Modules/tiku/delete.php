<?php require_once('../../../Private/config/config.php'); ?>
<?php require_once('auth.php'); ?>
<?php
if(isset($_POST['action'])){
	if($_POST['action']=="yes"){	
		$id = $_POST['id'];
		$table = 'tiku_cavo_test';
		require_once('../../../Private/config/config.php');
		mysql_select_db($database_cavoconnection, $cavoconnection);
		mysql_query("SET NAMES utf8");
		$query_entry = "DELETE FROM $table WHERE id=$id";
		$entry = mysql_query($query_entry, $cavoconnection) or die(mysql_error());
		
		echo "<h1>删除成功！请返回继续操作</h1>\n";
		echo "<p><a href=\"index.php\">返回</a></p>\n";
	}
}else{
	echo "you must make a selection";
	exit;
}
?>