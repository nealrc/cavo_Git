<?php 
require_once('Private/config/config.php'); 
require_once('Private/class/function.php'); 
?>
<?php
// *** Validate request to login to this site.
if (!isset($_SESSION)) {
  session_start();
}

$homelink = array(1 => "Public/Modules/control/index.php",
				  2 => "Public/Modules/tiku/index.php",
				  3 => "Public/Modules/control/index.php",
				  4 => "Public/Modules/control/index.php");

if(isset($_SESSION['MM_UserGroup'])) {
	header("Location: ".$homelink[$_SESSION['MM_UserGroup']]);
}

$profileLink = "Public/Modules/control/profile.php";
$loginFormAction = $_SERVER['PHP_SELF'];

if(isset($_GET['accesscheck'])) {
  $_SESSION['PrevUrl'] = $_GET['accesscheck'];
}

if(isset($_POST['username'])&&isset($_POST['submit'])){
  $loginUsername = $_POST['username'];
  $password = $_POST['password'];
  
  $MM_fldUserAuthorization = "Membership";  
  $MM_redirectLoginFailed = "Public/auth/loginfailed.php";
  $MM_redirecttoReferrer = false;
  
  mysql_select_db($database_cavoconnection, $cavoconnection);
  	
  $LoginRS__query=sprintf("SELECT `Userid`, CONCAT(UCASE(SUBSTRING(`Firstname`,1,1)),SUBSTRING(`Firstname`,2), ' ', UCASE(SUBSTRING(`Lastname`,1,1)), SUBSTRING(`Lastname`,2)) AS name, `Email`, `Password`, `Membership`, `Active` FROM `user` WHERE `Email`=%s AND (`Password`=%s OR `Password`=sha1(%s) )",
  GetSQLValueString($loginUsername, "text"), GetSQLValueString($password, "text"), GetSQLValueString($password, "text")); 
   
  $LoginRS = mysql_query($LoginRS__query, $cavoconnection) or die(mysql_error());
  $loginFoundUser = mysql_num_rows($LoginRS);
  
  if($loginFoundUser){
    $loginStrGroup  = mysql_result($LoginRS,0,'Membership');
	$loginStrName  = mysql_result($LoginRS,0,'name');
	$loginStrID  = mysql_result($LoginRS,0,'Userid');
	$loginStrActive  = mysql_result($LoginRS,0,'Active');
    
	if (PHP_VERSION >= 5.1) {session_regenerate_id(true);} else {session_regenerate_id();}
    //declare session variables and assign them    
	$_SESSION['MM_Userid'] = $loginStrID;
	$_SESSION['MM_Username'] = $loginStrName;
    $_SESSION['MM_UserGroup'] = $loginStrGroup;
	$_SESSION['MM_UserActive'] = $loginStrActive;
	
	// a default location
	$MM_redirectLoginSuccess = "Public/Modules/test/index.php";
	switch($_SESSION['MM_UserGroup']){
		case 1: $MM_redirectLoginSuccess = "Public/Modules/control/index.php"; break;
		case 2: $MM_redirectLoginSuccess = "Public/Modules/tiku/index.php"; break;
		case 3: $MM_redirectLoginSuccess = "Public/Modules/control/index.php"; break;
		case 4: $MM_redirectLoginSuccess = "Public/Modules/control/index.php"; break;
	}
	
    if(isset($_SESSION['PrevUrl']) ){
      $MM_redirectLoginSuccess = $_SESSION['PrevUrl'];	
    }
	
    header("Location: " . $MM_redirectLoginSuccess );
  }else {
    header("Location: ". $MM_redirectLoginFailed );
  }
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml"><!-- InstanceBegin template="/Templates/functions.dwt" codeOutsideHTMLIsLocked="false" -->
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<!-- InstanceBeginEditable name="doctitle" -->
<title>CAVO</title>
<!-- InstanceEndEditable -->
<link rel="stylesheet" type="text/css" href="Assets/css/style.css" />
<!-- InstanceBeginEditable name="head" -->
<link rel="stylesheet" type="text/css" href="Assets/css/sub.css" />
<!-- InstanceEndEditable -->
</head>
<body>
<div id="container">
    <div id='content-wrapper'>
        <div id="header-wrapper">
            <div id="header">
                <div id="section1">
            <div id="logo"> <img src="Assets/images/logo.gif" /> </div>
            <div id="menu">
              <ul>
                  <!--<li><a href="../Public/Modules/feedback/fb.php">Feedback</a></li>-->
                  <li><a href="Public/Modules/control/index.php">Control Panel</a></li>
                  <li><a href="Public/Modules/test/index.php">Vocabulary Test</a></li>
                  <li><a href="Public/Modules/demo/index.php">Demo</a></li>
                  <li><a href="index.php">Home</a></li>
				  <li><a href="nealrc.org">Hanning</a></li>
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
   			<div class="fancybox-wrapper">
            	<div class="fancybox-top"><h1>Sign IN</h1></div>
				<div class="fancybox-content">
                    <div id="signin-form">
                		<form action="<?php echo $loginFormAction; ?>" method="POST" name="signin">
                          <div class="inputwrapper">
                              <label>User Name:</label><input name="username" type="text" width="140" height="14" />
                          </div>                          
                          <div class="inputwrapper">
                              <label>Password:</label><input name="password" type="password" width="140" height="14" />
                          </div>
                          <p class="forgotpassword"><!--<a href="#">forgot password?</a>-->&nbsp;</p>
                          <div class='submitwrapper'><input type="submit" name="submit" value="SIGN IN" class="btn_red" /></div>
                      </form>                    
           		  </div>
                  <div id="register">Not registered? <a href="Public/Modules/register/register.php">Create an account</a></div>
				</div>                
				<div class="fancybox-bottom" id='signin-formfooter'></div>                
       	</div>
        <div style="width:500px; margin:20px auto;">
        <h2>Demo users</h2>
        <p>demo student: student@demo.edu / demo</p>        
        </div>
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
            <img src="Assets/images/NEALRClogo.jpg" width="50" height="50" />            </p>
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
			<a href="http://alpps.org/"><img src='Assets/images/logo-alpps.gif' /></a>&nbsp;
            <a href="http://chineseflagship.osu.edu/"><img src='Assets/images/logo-cfp.gif' /></a>&nbsp;
            <a href="http://flpubs.osu.edu/"><img src='Assets/images/FLPlogo.gif' /></a>&nbsp;
            <a href="http://www.osu.edu/"><img src='Assets/images/logo-osu.gif' /></a>&nbsp;
            
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