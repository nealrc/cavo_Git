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

$tiku_table = 'tiku_cavo_test';
$log_table = "log_tiku_edit";

$a = array();
$query_entry = "SELECT a.`id` AS tikuid, a.`word`, a.`py`, a.`en`, a.`cn`, b.`id` AS edit_id, b.`flag`, b.`comment`, b.`date`, c.`Firstname`, c.`Lastname` FROM `$tiku_table` AS a LEFT JOIN `$log_table` AS b ON (a.`id` = b.`tiku_id`) LEFT JOIN `user` AS c On (b.`user` = c.`Userid`) WHERE a.`id`=$tikuid";

$entry = mysql_query($query_entry, $cavoconnection) or die(mysql_error());
$row_entry = mysql_fetch_assoc($entry);
$totalRows_entry = mysql_num_rows($entry);

if($totalRows_entry>0){
	do{
		$a['word'] = $row_entry['word'];
		$a['py']  = $row_entry['py'];
		$a['cn']  = $row_entry['cn'];
		$a['en']  = $row_entry['en'];
		
		if(!is_null($row_entry['edit_id'])){
			$a['edit'][$row_entry['edit_id']]['d']=$row_entry['date'];
			$a['edit'][$row_entry['edit_id']]['u']=$row_entry['Firstname'].' '.$row_entry['Lastname'];
			if(is_null($row_entry['flag'])){
				$a['edit'][$row_entry['edit_id']]['f']='NA';
			}else{
				if($row_entry['flag'] == 1){
					$a['edit'][$row_entry['edit_id']]['f']=' Added ';
				}else{
					$a['edit'][$row_entry['edit_id']]['f']=' Removed ';
				}
			}
			//$a['edit'][$row_entry['edit_id']]['f'] = is_null($row_entry['flag'])?($row_entry['flag'] == 1?' Removed ':' Added '):'NA';
			$a['edit'][$row_entry['edit_id']]['c'] = !is_null($row_entry['comment'])?$row_entry['comment']:"N/A";
		}
	}while($row_entry = mysql_fetch_assoc($entry));	
}
mysql_free_result($entry);
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
<head>
<title>Word Processing</title>
<link href="css/style.css" type="text/css" rel="stylesheet" />
</head>
<body>
<div id="wrapper">
	<h1>词汇编辑记录</h1>
	<div id="ccontent">
		<h2><?php echo $a['word']; ?></h2>
		<ul>
		<li><label>编号 </label><?php echo $tikuid; ?></li>        
		<li><label>拼音 </label><?php echo $a['py']; ?></li>
		<li><label>中文解释 </label><?php echo $a['cn']; ?></li>
		<li><label>英文解释 </label><?php echo $a['en']; ?></li>
		</ul>
        
        <?php if(isset($a['edit'])){?>
        <table border='0' cellpadding='5' cellspacing='0'>
        <tr><th>Date</th><th>User</th><th>Flag</th><th>Comment</th></tr>
        <?php foreach($a['edit'] as $eid => $aa){ ?>
        <tr>
        	<td><?php echo $aa['d'];?></td>
            <td><?php echo $aa['u'];?></td>
            <td><?php echo $aa['f'];?></td>
            <td><?php echo $aa['c'];?></td>
        </tr>
        <?php } ?>
		</table>
        <?php } ?>        
	</div>	
</div>
</body>
</html>
