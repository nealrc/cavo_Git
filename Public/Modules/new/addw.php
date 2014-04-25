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
?>
<?php
if (!isset($_SESSION)) {
  session_start();
}
$MM_authorizedUsers = $role['admin'].','.$role['instructor'].','.$role['student'];
$MM_donotCheckaccess = "true";

// *** Restrict Access To Page: Grant or deny access to this page
function isAuthorized($strUsers, $strGroups, $UserName, $UserGroup) { 
  // For security, start by assuming the visitor is NOT authorized. 
  $isValid = False; 

  // When a visitor has logged into this site, the Session variable MM_Username set equal to their username. 
  // Therefore, we know that a user is NOT logged in if that Session variable is blank. 
  if (!empty($UserName)) { 
    // Besides being logged in, you may restrict access to only certain users based on an ID established when they login. 
    // Parse the strings into arrays. 
    $arrUsers = Explode(",", $strUsers); 
    $arrGroups = Explode(",", $strGroups); 
    if (in_array($UserName, $arrUsers)) { 
      $isValid = true; 
    } 
    // Or, you may restrict access to only certain users based on their username. 
    if (in_array($UserGroup, $arrGroups)) { 
      $isValid = true; 
    } 
    if (($strUsers == "") && true) { 
      $isValid = true; 
    } 
  } 
  return $isValid; 
}

$MM_restrictGoTo = "../../../signin.php";
if (!((isset($_SESSION['MM_Username'])) && (isAuthorized("",$MM_authorizedUsers, $_SESSION['MM_Username'], $_SESSION['MM_UserGroup'])))) {   
  $MM_qsChar = "?";
  $MM_referrer = $_SERVER['PHP_SELF'];
  if (strpos($MM_restrictGoTo, "?")) $MM_qsChar = "&";
  if (isset($QUERY_STRING) && strlen($QUERY_STRING) > 0) 
  $MM_referrer .= "?" . $QUERY_STRING;
  $MM_restrictGoTo = $MM_restrictGoTo. $MM_qsChar . "accesscheck=" . urlencode($MM_referrer);
  header("Location: ". $MM_restrictGoTo); 
  exit;
}
$profileLink = "../control/profile.php";
?>
<?php
$m=$_GET['m'];
if(!$m)
	$m='add'; // default: add a new word
if('add'==$m){
	$v=$_GET['v'];
	$tip='Add a new word';
}
if('edit'==$m){ // edit the new added vocabulary in the temp table
	$id=intval($_GET['id']);
	require_once("../../../Private/config/config.php");
	mysql_select_db($database_cavoconnection, $cavoconnection);
	mysql_query("SET NAMES UTF8");
	$sql='SELECT * FROM newword WHERE id='.$id;
	$result = mysql_query($sql, $cavoconnection) or die(mysql_error());
	$row=mysql_fetch_assoc($result);
	$v=$row['word'];
	$py=$row['py'];
	$en=$row['en'];
	$cn=$row['cn'];
	$tip='Edit the word';
}
if('modify'==$m){ //  modify the  vocabulary in tiku
}
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
<link rel="stylesheet" type="text/css" href="../../../Assets/css/word-edit-style.css"/>
<script type="text/javascript" src="../../../Assets/js/jquery-1.4.2.min.js"></script>
<script type="text/javascript" src="../../../Assets/js/jquery.form.min.js"></script>
<script type="text/javascript" src="../../../Assets/js/jquery.blockui.min.js"></script>
<script type="text/javascript">
$(document).ready(function(){
	var submitOptions = {
		beforeSubmit:  submitRequest,
        success:       submitResponse,
    };
	// bind to the form's submit event 
    $('#newWord').submit(function() { 
        $(this).ajaxSubmit(submitOptions); 
        return false; 
    });
	
	// enable the PY input assistant
	$('.pyAss').click(insertPY);
	$('#Vocabulary').blur(checkExist);
});
function submitRequest(formData, jqForm, submitOptions) {
	for (var i=0; i < formData.length; i++) {
		formData[i].value=formData[i].value.trim();
        if (!formData[i].value) { 
            alert('No value should be left blank!'); 
            return false; 
        } 
    }
	if(!isChn($('#Vocabulary').val())){
		alert('中文单词必需全部为中文！');
		return false;
	}
	$('#newWord').block({
		message: '<img src="../../../Assets/images/loader.gif" />', 
		css:{width: '25px', height: '50px', border: '3px solid #989898', backgroundColor: "#999999"}
	});	
	return true; 
} 
function submitResponse(responseText, statusText)  {
	$('#wordAlert').hide();
	$('#newWord').unblock();
	if('ok'==responseText){
		$('#newWord').clearForm();
		alert('New word is added to our system and is waiting to be approved by CAVO specialist!');
	}
} 
function insertPY(){
	$('#PY').val($('#PY').val()+$(this).text());
	$('#PY').focus();
}
function checkExist(){
	if(!isChn($('#Vocabulary').val())){
		$('#wordAlert').text('请输入中文单词！').fadeIn();
		$('#Vocabulary').focus();
		return false;
	}
	$('#r').empty();
	$.get("wordproc.php", { method: "check", Vocabulary: $('#Vocabulary').val() },
	function(data){
		if('no'==data){
			$('#r').html('');
			$('#wordAlert').hide();
		}else{
			$('#r').html(data);
			$('#wordAlert').text('We have this word already:').show();
		}
	} );
}

function isChn(str){ 
	var reg = /^[\u4E00-\u9FA5]+$/; 
	if(!reg.test(str)){  
		return false; 
	}  
	return true; 
}
</script>
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
        <div id="addwrapper">
        <h1>Add a New Word</h1>

        <form id="newWord" action="wordproc.php" method="post">
        	<label for="Vocabulary">中文单词：</label>
            <input id="Vocabulary" type="text" name="Vocabulary" />
            <span id="wordAlert" style="display: inline;">请输入中文单词！</span>
            <br/>
            
            <label>Difficulty level</label>
            <select name='level'>
            <option value=0>Let system decide</option>
            <option value=1>1</option>
            <option value=2>2</option>
            <option value=3>3</option>
            <option value=4>4</option>
            </select><br />
            <div class='notes'>If you are not sure about this, system will help match the word with the most appropriate level.</div>
            
            <label for="PY">中文拼音：</label>
            <input id="PY" type="text" name="PY" <?php if($py){ echo('value="'.$py.'"');}?> />
                <div id="pyInput">
                    <span class="pyAss">ā</span>
                    <span class="pyAss">á</span>
                    <span class="pyAss">ǎ</span>
                    <span class="pyAss">à</span>
                    <span class="pyAss">ō</span>
                    <span class="pyAss">ó</span>
                    <span class="pyAss">ǒ</span>
                    <span class="pyAss">ò</span>
                    <span class="pyAss">ē</span>
                    <span class="pyAss">é</span>
                    <span class="pyAss">ě</span>
                    <span class="pyAss">è</span>
                    <span class="pyAss">ī</span>
                    <span class="pyAss">í</span>
                    <span class="pyAss">ǐ</span>
                    <span class="pyAss">ì</span>
                    <span class="pyAss">ū</span>
                    <span class="pyAss">ú</span>
                    <span class="pyAss">ǔ</span>
                    <span class="pyAss">ù</span>
                    <span class="pyAss">ǖ</span>
                    <span class="pyAss">ǘ</span>
                    <span class="pyAss">ǚ</span>
                    <span class="pyAss">ǜ</span>
                    <span class="pyAss">ü</span>
                </div>
            <label for="EN">英文解释：</label>
            <textarea name="EN" id="EN" cols="45" rows="5"><?php
				if($en){
					echo($en);
				}
			?></textarea>
            <label for="CN">中文解释：</label>
            <textarea name="CN" id="CN" cols="45" rows="5"><?php
				if($cn){
					echo($cn);
				}
			?></textarea>
            
			<input type="hidden" id="method" name="method" value="<?php
				if($m){
					echo($m);
				}else{
					echo('add');
				}
			?>"/>
			<?php
				if($id){
					echo('<input type="hidden" name="id" value="'.$id.'" />');
				}
			?>
            <br/>
            <input type="submit" value="Submit" id="submit" style="margin: 10px 0px;"/>
        </form>
        <div id="r"></div>
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
