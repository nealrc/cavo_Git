<?php require_once('../../../Private/config/config.php'); ?>
<?php require_once('auth.php'); ?>
<?php
mysql_select_db($database_cavoconnection, $cavoconnection);
$query = "SELECT count(*) as size FROM tiku_cavo_test";
$result = mysql_query($query);
$row = mysql_fetch_array($result); 
$size=$row['size'];
$rperp = 50;
$nump = ceil($size/$rperp);
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Tiku Management</title>
<link rel="stylesheet" type="text/css" href="../../../Assets/js/colorbox/example1/colorbox.css" />
<script type="text/javascript" src="../../../Assets/js/jquery-1.4.2.min.js"></script>
<script type="text/javascript" src="../../../Assets/js/colorbox/colorbox/jquery.colorbox-min.js"></script>
<script type="text/javascript" src="../../../Assets/js/jquery.jeditable.pack.js"></script>
<style type="text/css">
.loading{ 
	background-image:url("../../../Assets/images/loader-bigcircle.gif");  
	background-position:center center;  background-repeat:no-repeat; 
}
#content{ margin:20px auto; width:1024px; font-size:12px; }
#content table{width:1024px;}
#content table th, #content table td{border-bottom:1px solid #9c0c0a; border-right:1px solid #9c0c0a;}
#content table th{
	font-size:1.3em; 
	font-weight:bold; 
	text-transform:uppercase; 
	background-color:#3B3013; 
	color:#FFF;
	border-bottom-width:5px; 
	border-right-width:0px;	
}
#content table td{text-align:center;}
#content table td.tdid{width:30px; background-color:#3B3013;}
#content table td.tdid a{color:#FFF; font-size:1.2em;}
#content table td.tdvoc a{font-size:1.2em;}
#content table td a{color:#E8480A;}

#nav a {padding:2px; border: solid 1px #AAE; color: #15B; font-size:12px; margin:2px 10px;}
#nav a:hover{ background: #26B; color:#fff} 
h1{ font-size:20px; margin:5px;}
</style>
<script type="text/javascript">
$(document).ready( function() {
	var JumpToPage=1, RecordsPerPage, max, numPages, current_page;
	RecordsPerPage = <?php echo $rperp; ?>;
	max = <?php echo $size; ?>;
	numPages = <?php echo $nump; ?>;

	var s = (JumpToPage-1)*RecordsPerPage,
		e = s + RecordsPerPage,
		current_page = JumpToPage;

	loadContent(s, e);
	
	//loading by designated page id
	$("select#jumpto").change( function() {
			JumpToPage = $("#jumpto").val();

			current_page = JumpToPage;
			
			s = (JumpToPage-1)*RecordsPerPage;
			e = s + RecordsPerPage;
			loadContent(s, e );
	});

	$("a#next").click( function (){
		if(current_page == numPages){current_page=1} else {current_page++}		
			JumpToPage = current_page;
			s = (JumpToPage-1)*RecordsPerPage;
			e = s + RecordsPerPage;
			loadContent(s, e );		
	});
	
	$("a#pre").click( function (){
		if(current_page == 1){ current_page = numPages} else { current_page--}
			JumpToPage = current_page;
			s = (JumpToPage-1)*RecordsPerPage;
			e = JumpToPage*RecordsPerPage + RecordsPerPage;	
			loadContent(s, e );
	});
		
			
	function loadContent(start, end){
		str = "Page "+JumpToPage+" | Record id " +  start  + " to  " + end;	
		$("#tl").html('<h1>'+str+'</h1>');
		$("#content").empty().css('height', '300px').addClass("loading");
		$.ajax({
			type: "POST",
			url: "load.php",
			data: "s=" +  start + "&e=" + end,
			dataType: "html",
			success: function(r) {
				$("#content").css('height', 'auto').removeClass("loading").html(r);
				$('#jumpto').val(JumpToPage);				
				$("a.imodal").colorbox({width:550, height:700, iframe:true});
				$("a.cmodal").colorbox({width:550, height:700, iframe:true});
				$("a.omodal").colorbox({width:"80%", height:"80%", iframe:true});
			}
		});
	}	
	
});
</script>
</head>
<body>
<p><a href="<?php echo $logoutAction; ?>">Sign Out</a>  </p>
<div id="tl"></div>
<div id="nav">
 <label for="jumpto">Jump to page: <label>
 <select id="jumpto">
 	<?php for($i=1;$i<=$nump; $i++){ echo "<option value=\"$i\">$i</option>";} ?>
 </select>
 <a id="pre">|< Previous Page</a> Total <?php echo $nump; ?> pages <a id="next">Next Page >|</a> 
 
</div>

<div id= "content"></div>
</body>
</html>