<?php require_once('../../../Private/config/config.php'); ?>
<?php require_once('auth.php'); ?>
<?php
if(isset($_POST['tikuid'])){
	$tikuid = $_POST['tikuid'];
}elseif(isset($_GET['tikuid'])){
	$tikuid = $_GET['tikuid'];
}else{
	return "error => no tikuid defined";
}

$find=false;

$table = 'tiku_cavo_test';

mysql_select_db($database_cavoconnection, $cavoconnection);
mysql_query("SET NAMES utf8");
$query_entry = "SELECT a.* FROM `$table` AS a WHERE a.`id`=$tikuid";
$entry = mysql_query($query_entry, $cavoconnection) or die(mysql_error());
$row_entry = mysql_fetch_assoc($entry);
$totalRows_entry = mysql_num_rows($entry);

if($totalRows_entry>0){
	do{
		$voc = $row_entry['word'];
		$py  = $row_entry['py'];
		$cn  = $row_entry['cn'];
		$en  = $row_entry['en'];
	}while($row_entry = mysql_fetch_assoc($entry));
	
	$find=true;	
	
	$a['Yes']='Yes';
	$a['No'] = 'No';
	$a['selected'] = $flag==1?'Yes':'No';
}
mysql_free_result($entry);

if(!$find){
	header("Content-Type: text/html; charset=utf-8");
	echo "您输入的词汇编号不正确! 请返回重新输入";
	exit;
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
<head>
<title>Word Processing</title>
<link href="css/style.css" type="text/css" rel="stylesheet" />
<script type="text/javascript" src="../../../Assets/js/jquery-1.4.2.min.js"></script>
<script type="text/javascript" src="../../../Assets/js/jquery.jeditable.pack.js"></script>
<script type="text/javascript">
$(document).ready(function() {
    $(".editable_textarea").editable("save.php", { 
        indicator : "<img src='css/indicator.gif'>",
		tooltip: "Click to edit",
		type: 'textarea',
		rows: 3,
		cols: 30,
        submit: 'save'
    });

    $(".editable_select").editable("save.php", { 
        indicator : "<img src='css/indicator.gif'>",
		tooltip: "Click to add flag",
		data: '<?php echo json_encode($a); ?>',
		type: 'select',
        submit: 'save'
    });
});
</script>
</head>
<body>
<div id="wrapper">
	<h1>词汇编辑</h1>
	<blockquote>[ 请点击红色字体进行编辑 ]</blockquote>
	
	<div id="ccontent">
		<h2><?php echo $voc; ?></h2>
		<ul>
		<li><label>编号 </label><?php echo $tikuid; ?></li>
		<li><label>拼音 </label><?php echo $py; ?></li>
		<li><label>中文解释 </label><br /><p class="editable_textarea" id="<?php echo $tikuid."__cn"; ?>"><?php echo $cn; ?></p></li>
		<li><label>英文解释 </label><br /><p class="editable_textarea" id="<?php echo $tikuid."__en"; ?>"><?php echo $en; ?></p></li>        
		</ul>
        
        <div style="float:left; border-top:1px solid #ccc; padding:20px 0 10px 0;">
        
            <p><label>Flag?</label>
            	<b class='editable_select' style="display: inline" id="<?php echo $tikuid."__flag"; ?>"></b></p><br />
            
            <p><label>Comments</label><br /><p class="editable_textarea" id="<?php echo $tikuid."__comment"; ?>">Leave comment?</p>
            </p>
        </div>
        
	</div>	
</div>
</body>
</html>