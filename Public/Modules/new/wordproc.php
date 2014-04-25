<?php 
require_once('../../../Private/config/config.php');
require_once("../../../Private/class/function.php");
?>
<?php
if (!isset($_SESSION)) {
  session_start();
}

$method=$_REQUEST['method'];

if(null==$method || ''==$method){
	die('illegal submit!');
}

mysql_select_db($database_cavoconnection, $cavoconnection);
mysql_query("SET NAMES UTF8");
if('add'==$method){	
	$userid=$_SESSION['MM_Userid'];
	$word=trim($_POST["Vocabulary"]);
	$py=trim($_POST["PY"]);
	$en=trim($_POST["EN"]);
	$cn=trim($_POST["CN"]);	
	
	$level=0;
	if(empty($_POST['level'])){
		$query= "SELECT `level` FROM `tiku_hsk` WHERE `word` = '$word' LIMIT 1";
		$result = mysql_query($query, $cavoconnection);
		$num = mysql_num_rows($result);
		if($num > 0){
			$rows = mysql_fetch_assoc($result);			
			$level = $rows['level'];
		}else{
			$query2= "SELECT `level` FROM `tiku_freq` WHERE `word` = '$word' LIMIT 1";
			$result2 = mysql_query($query2, $cavoconnection);
			$num = mysql_num_rows($result2);
			if($num > 0){
				$rows = mysql_fetch_assoc($result2);			
				$level = $rows['level'];
			}
			mysql_free_result($result2);
		}
		mysql_free_result($result);
	}else{
		$level=$_POST['level'];
	}
	
	if($level==0){
		$level = mt_rand(1,4);
	}
	
	$sql = sprintf('INSERT INTO `newword` (`word`, `py`, `en`, `cn`, `level`, `user`, `time`) 
					VALUES (%s, %s, %s, %s, %s, %s, NOW())', 
					GetSQLValueString($word, 'text'), 
					GetSQLValueString($py, 'text'),
					GetSQLValueString($en, 'text'),
					GetSQLValueString($cn, 'text'),
					GetSQLValueString($level, 'int'),
					GetSQLValueString($userid, 'int')
			);
	
	$result = mysql_query($sql, $cavoconnection) or die(mysql_error());
	$num_inc=mysql_affected_rows();
	if(1==$num_inc){
		echo 'ok';
	}
}
if('edit'==$method){
	$id=intval($_POST["id"]);
	$Vocabulary=trim($_POST["Vocabulary"]);
	$PY=trim($_POST["PY"]);
	$EN=trim($_POST["EN"]);
	$CN=trim($_POST["CN"]);
	$sql = 'UPDATE `newword` SET `word`=\''.$Vocabulary.'\', `py`=\''.$PY.'\', `en`=\''.$EN.'\', `cn`=\''.$CN.'\' WHERE `id`='.$id;
	
	$result = mysql_query($sql, $cavoconnection) or die(mysql_error());
	echo('ok');
}
if('check'==$method){
	$Vocabulary=trim($_GET['Vocabulary']);
	$inDB=false;
	// check if the word is already in Tiku
	$sql = 'SELECT * FROM `tiku_cavo_test` WHERE `word` = \''.$Vocabulary.'\''; 
	$result = mysql_query($sql, $cavoconnection) or die(mysql_error());
	$num_rows=mysql_num_rows($result);
	
	if($num_rows>0){
		$inDB=true;
		echo('<div class="alert">'.$Vocabulary.' in the Tiku:</div>');
		while($row=mysql_fetch_assoc($result)){
			showMeanings($row);
		}
	}
	if(!$inDB){
		echo('no');
	}
}

function showMeanings($row){
	echo('<div class="meaning">');
			echo("<div class='py'><span class='t'>中文拼音:</span> <span class='d'>".$row['py']."</span></div>");
			echo("<div class='en'><span class='t'>英文解释:</span> <span class='d'>".str_replace(";", ";&nbsp; ", $row['en'])."</span></div>");
			echo("<div class='cn'><span class='t'>中文解释:</span> <span class='d'>".str_replace(";", ";&nbsp; ", $row['cn'])."</span></div>");
			echo('</div>');
}
?>