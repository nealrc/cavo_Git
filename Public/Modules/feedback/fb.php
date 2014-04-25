<?php
//initialize the session
if (!isset($_SESSION)) {
  session_start();
}

// ** Logout the current user. **
$logoutAction = $_SERVER['PHP_SELF']."?doLogout=true";
if ((isset($_SERVER['QUERY_STRING'])) && ($_SERVER['QUERY_STRING'] != "")){
  $logoutAction .="&". htmlentities($_SERVER['QUERY_STRING']);
}

if ((isset($_GET['doLogout'])) &&($_GET['doLogout']=="true")){
  //to fully log out a visitor we need to clear the session varialbles
  $_SESSION['MM_Userid'] = NULL;
  $_SESSION['MM_Username'] = NULL;
  $_SESSION['MM_UserGroup'] = NULL;
  $_SESSION['PrevUrl'] = NULL;
  unset($_SESSION['MM_Userid']);
  unset($_SESSION['MM_Username']);
  unset($_SESSION['MM_UserGroup']);
  unset($_SESSION['PrevUrl']);
	
  $logoutGoTo = "../../auth/logoutsuccess.php";
  if ($logoutGoTo) {
    header("Location: $logoutGoTo");
    exit;
  }
}

$MM_restrictGoTo = "../../../signin.php";
$MM_qsChar = "?";
$MM_referrer = $_SERVER['PHP_SELF'];
if (strpos($MM_restrictGoTo, "?")) $MM_qsChar = "&";
if (isset($QUERY_STRING) && strlen($QUERY_STRING) > 0) 
$MM_referrer .= "?" . $QUERY_STRING;
$signinlink = $MM_restrictGoTo. $MM_qsChar . "accesscheck=" . urlencode($MM_referrer);
$profileLink = "../control/profile.php";
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml"><!-- InstanceBegin template="/Templates/functions.dwt" codeOutsideHTMLIsLocked="false" -->
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<!-- InstanceBeginEditable name="doctitle" -->
<title>CAVO</title>
<!-- InstanceEndEditable -->
<link rel="stylesheet" type="text/css" href="../../../Assets/css/style.css" />
<!-- InstanceBeginEditable name="head" -->
<link rel="stylesheet" type="text/css" href="../../../Assets/js/jquery.ui/css/flick/jquery-ui-1.8.5.custom.css"/>
<link rel="stylesheet" type="text/css" href="feedback.css"/>
<script type="text/javascript" src="../../../Assets/js/jquery-1.4.2.min.js"></script>
<script type="text/javascript" src="../../../Assets/js/jquery.form.min.js"></script>
<script type="text/javascript" src="../../../Assets/js/jquery.blockui.min.js"></script>

<script type="text/javascript">
$(document).ready(function(){
    var options = { beforeSubmit: showRequest, success: showResponse};
    $('#feedback').submit(function() { 
        $(this).ajaxSubmit(options); 
        return false; 
    }); 
});
function showRequest() { 
	if($("#opinion").val()==""){
		return false;
	}
	$.blockUI({
		message: '<img src="../management/css/ajax-loader.gif" />', 
		css:{ 
			border: 'none', 
			background: 'none',							
			width: '220px', 
			height: '19px'
		}
	}); 					
    return true; 
} 
function showResponse(r)  {
	//$.unblockUI();
	if(r == "added"){
		location.reload();
	}else{
		$("#output").empty().append(r);
	}
}

function delfb(id){
	if(!confirm("Are you sure want to delete?"))
		return false;
	showRequest();
	$.get("feedback.php",{method: "del", id: id}, function(r){
		$.unblockUI();
		if(r=="deleted"){
			$("#feedback"+id).remove();
		}else{
			$("#output").empty().append(r);
		}
	});
}
</script>

<style type="text/css">
<!--
strong {
	color: #F6F;
}
#fbwrapper{width:980px;margin:0 auto;}
#fbwrapper ul{list-style:none;}
-->
</style>
<!-- InstanceEndEditable -->
</head>
<body>
<div id="container">
    <div id='content-wrapper'>
        <div id="header-wrapper">
            <div id="header">
                <div id="section1">
            <div id="logo"> <img src="../../../Assets/images/logo.gif" /> </div>
            <div id="menu">
              <ul>
                  <!--<li><a href="../Public/Modules/feedback/fb.php">Feedback</a></li>-->
                  <li><a href="../control/index.php">Control Panel</a></li>
                  <li><a href="../test/index.php">Vocabulary Test</a></li>
                  <li><a href="../demo/index.php">Demo</a></li>
                  <li><a href="../../../index.php">Home</a></li>              
              </ul>
            </div>
            </div><!--end SECTION1-->
              
            <?php if(isset($_SESSION['MM_Username'])){ ?>
            <div id="loginstatus">
            Welcome <?php echo $_SESSION['MM_Username']; ?> : <a href="<?php echo $profileLink;?>">My Account</a> | <a href="<?php echo $logoutAction; ?>">Sign Out</a>        
            </div>
            <?php } ?>
            </div><!--end HEADER-->
        </div><!--end HEADER-WRAPPER-->
        
        <div id="maincontent">
        <!-- InstanceBeginEditable name="content" -->
        <div id="fbwrapper">
        <h1>User Feedback</h1>
		<?php if(!$_SESSION['MM_Username']){ ?>
        <p>Please <a href="../register/register.php"><strong>Register</strong></a> or <a href="<?php echo $signinlink; ?>"><strong>Sign in</strong></a> to leave a feedback!</p>
        <?php } ?>
        
		<?php
		require_once("../../../Private/config/config.php");
		mysql_select_db($database_cavoconnection, $cavoconnection);
		mysql_query("SET NAMES UTF8");
		$sql = "SELECT a.`id`, b.`Firstname`, b.`Lastname`, b.`Email`, c.`name` AS school, a.`opinion`, a.`time`
		FROM `feedback` AS a
		LEFT JOIN `user` AS b ON (a.`userid` = b.`Userid`)
		LEFT JOIN `base_school` AS c ON (b.`University` = c.`id`)
		ORDER BY a.`time` DESC";
		$result = mysql_query($sql, $cavoconnection) or die(mysql_error());
		$num_rows=mysql_num_rows($result);
		if(0==$num_rows) 
			echo('Be the first one to leave a message! <br />');
		else{
			for($i=0; $i<$num_rows; $i++){
				$row = mysql_fetch_assoc($result);
				if(isset($_SESSION['MM_Userid'])){
					$email=$row['Email'];
				}else{
					$email='';
				}
				if($row['school']!=''||!is_null($row['school'])){
				$s = explode(' ', $row['school']);
				$ss = '';
				foreach($s as $k => $v){
					$ss.=ucfirst($v).' ';
				}
				}else{
					$ss='Unknown School';
				}
				
		?>
		<div id="feedback<?php echo($row['id']);?>" class="currFeedback">
        	<div class='fleft'>
            	<div class='fltop'>
                	<a href="mailto:<?php echo $email; ?>"><?php  echo ucfirst($row['Firstname']).' '.ucfirst($row['Lastname']); ?></a>
                </div>
                <div class='flbottom'><?php echo $ss; ?></div>
            </div>
            
            <div class='fmiddle'>
            	<?php echo nl2br(stripslashes($row['opinion']));	?>
            </div>            
            
            <div class='fright'>
            	<div class='frl'>
				<?php  if($_SESSION['MM_UserGroup']==$role['admin']){ ?>
                	<a href="javascript:void()" onclick="delfb(<?php echo($row['id']);?>);"><span class="ui-icon ui-icon-trash"></span></a>
				<?php } ?>				
                </div>                
                <div class='frr'>
                <?php echo($row['time']); ?>
                </div>
            </div>
        </div>
        
		<?php }} mysql_free_result($result); ?>
        <?php if($_SESSION['MM_Username']){ ?>
        <div id="feedbackForm">
            <form id="feedback" action="feedback.php?method=add" method="post">
                Your Message:<br />
                <textarea id="opinion" name="opinion" cols="60" rows="9"></textarea><br />
                <input type="submit" value="Submit" />
        	</form>
        </div>
		<?php } ?>
    	<div id="output"></div>
    </div>
    <div class="push">&nbsp;</div>
          <!-- InstanceEndEditable -->
        </div><!--end MAINCONTENT-WRAPPER-->
        <div class="push">&nbsp;</div>
    </div><!--content-wrapper-->
  
    <div id="footer-wrapper">
        <div id="footer">
        
        <div class="column">
          <h2>NEALRC</h2>          
            <p><a href="http://nealrc.osu.edu">National East Asian Languages Resource Center</a><br />
              <br />
            <img src="../../../Assets/images/NEALRClogo.jpg" width="50" height="50" />            </p>
        </div>
        
        <div class="column">
          <h2>Contact Us:</h2>
          <p>The Ohio State University<br />
100 Hagerty Hall, 1775 College Road<br />
Columbus, OH 43210-1340 U.S.A.<br />
<br />
Phone: (614) 688-3080<br />
Fax: (614) 688-3355<br />
Email: li.28@osu.edu          </p>
        </div>
        
        
        <div class="column">
          <h2>Our Friends:</h2>
          <p>
          	<a href="http://alpps.org/">Advanced Performance Portfolio System</a><br />
            <a href="http://chineseflagship.osu.edu/">Chinese Flagship Program</a><br />
            <a href="http://flpubs.osu.edu/">Foreign Language Publications</a><br />
            <a href="http://www.osu.edu/">The Ohio State University</a><br /><br />
			<a href="http://alpps.org/"><img src='../../../Assets/images/logo-alpps.gif' /></a>&nbsp;
            <a href="http://chineseflagship.osu.edu/"><img src='../../../Assets/images/logo-cfp.gif' /></a>&nbsp;
            <a href="http://flpubs.osu.edu/"><img src='../../../Assets/images/FLPlogo.gif' /></a>&nbsp;
            <a href="http://www.osu.edu/"><img src='../../../Assets/images/logo-osu.gif' /></a>&nbsp;
            
          </p>
        </div>
        
        
        <div class="column last">
          <p>Copyright © 2013 National East Asian Languages Resource Center <br/>
            <!--<a href="http://validator.w3.org/check/referer">XHTML</a> | <a href="http://jigsaw.w3.org/css-validator/check/referer">CSS</a></p>-->
        </div>
        
        </div><!--end FOOTER-->
    </div><!--end FOOTER-WRAPPER-->
</div>
</body>
<!-- InstanceEnd --></html>