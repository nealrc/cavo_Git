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
	
  $logoutGoTo = "Public/auth/logoutsuccess.php";
  if ($logoutGoTo) {
    header("Location: $logoutGoTo");
    exit;
  }
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml"><!-- InstanceBegin template="/Templates/index.dwt" codeOutsideHTMLIsLocked="false" -->
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<!-- InstanceBeginEditable name="doctitle" -->
<title>CAVO</title>
<!-- InstanceEndEditable -->
<link rel="stylesheet" type="text/css" href="Assets/css/style.css" />
<!-- InstanceBeginEditable name="head" -->
<link rel="stylesheet" href="Assets/js/lightbox/css/jquery.lightbox-0.5.css" media="all" type="text/css" />
<script type="text/javascript" src="Assets/js/jquery-1.4.2.min.js"></script>
<script type="text/javascript" src="Assets/js/lightbox/jquery.lightbox-0.5.min.js"></script>
<script type="text/javascript">
$(document).ready( function(){
	$('#screenshots a').lightBox({
		imageLoading: 	'Assets/js/lightbox/images/lightbox-ico-loading.gif',
		imageBtnPrev: 	'Assets/js/lightbox/images/lightbox-btn-prev.gif',
		imageBtnNext: 	'Assets/js/lightbox/images/lightbox-btn-next.gif',
		imageBtnClose: 	'Assets/js/lightbox/images/lightbox-btn-close.gif',
		imageBlank: 	'Assets/js/lightbox/images/lightbox-blank.gif'
	});
})
</script>
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
                </ul>
              </div>        
            </div><!--end SECTION1-->
        
              <?php  if(isset($_SESSION['MM_Username'])){ ?>      
              <div id="loginstatus">
                  Welcome <?php echo $_SESSION['MM_Username']; ?> : <a href="Public/Modules/control/profile.php">My Account</a> | <a href="<?php echo $logoutAction; ?>">Sign Out</a>        
            </div>
              <?php } ?>
            
            <div id="section2">
              <div class="main-img"> <img src="Assets/images/main-img.png" /> </div>
              <div id="header-box">
                <div id="header-box-content">            
                  <div id="tleft"><!-- InstanceBeginEditable name="EditRegion3" -->
            <h1>WHAT IS CAVO?</h1>
            <p><span>CAVO</span> stands for <span>C</span>omputer <span>A</span>daptive <span>VO</span>cabulary Assessment</p>
            <p class='paragraph'>CAVO is an intelligent Chinese vocabulary testing tool which will adaptively refine the test difficulty based on test taker's performance.</p>
            <!--<div id="toolbox"><a href="register.php"><img src="images/join-now.gif" /></a></div>-->
            <div id="toolbox">
              <input class="btn_red" value='JOIN NOW' type="button" onclick="window.location='Public/Modules/register/register.php'" />
            </div>
            <div id="toolbox-signin"> or<a href="signin.php">Sign In</a></div>
            <!-- InstanceEndEditable --></div>
                  
                  <div id="tright"><!-- InstanceBeginEditable name="EditRegion4" -->
            <h2>User friendly</h2>
            <p>CAVO is  designed for ease of use. Comprehen- sive reporting systems are provided for both individual users and program administrators.</p>
            <h2>Smart</h2>
            <p>CAVO monitors test taker's performance  in real-time. It automatically selects the test topics from the  most appropriate  difficulty levels from CAVO database.</p>
            <h2>Flexible</h2>
            <p>Vocabulary  in a custom test can be easily defined to fit your class needs..</p>
            <!-- InstanceEndEditable --></div>
                  
                </div><!--end HEADER-BOX-CONTENT-->
              </div><!--end HEADER-BOX-->
            </div><!--end SECTION2-->
        
          </div><!--end HEADER-->            
        </div><!--end HEADER-WRAPPER-->
        
        
        <div id="features-wrapper">
          <div id="features">
            <div class="col1">
              <h1>SCREENSHOTS</h1>
              <div id="screenshots">
                  <ul>
                    <!-- InstanceBeginEditable name="EditRegion5" -->
        	    <li><a href='Assets/images/screenshots/1.jpg' title='Screenshots - Detailed Test Info'><img src="Assets/images/screenshots/tb/tb_1.jpg" /></a></li>
        	    <li><a href='Assets/images/screenshots/2.jpg' title='Screenshots - Performance Charts'><img src="Assets/images/screenshots/tb/tb_2.jpg" /></a></li>
        	    <li><a href='Assets/images/screenshots/3.jpg' title='Screenshots - Vocabulary Frequency Analyzer'><img src="Assets/images/screenshots/tb/tb_3.jpg" /></a></li>
        	    <li><a href='Assets/images/screenshots/5.jpg' title='Screenshots - Dictionary with Wildcard Search'><img src="Assets/images/screenshots/tb/tb_5.jpg" /></a></li>
       	      <!-- InstanceEndEditable -->
                </ul>
              </div>
            <!-- InstanceBeginEditable name="content in sidebar" -->
      <div id='home_sidebar_contents'>
<!--      <ul>
          <li id='digest'><a href="Public/Modules/digest/topw.php">CAVO Digest</a></li>         
      </ul>  -->
      </div>
	  <!-- InstanceEndEditable --> </div>
            <!--end COL1-->
            
            <div class="col2">
              <h1>USEFUL TOOLS</h1>
              <!-- InstanceBeginEditable name="EditRegion6" -->
        <div class="fcolumn">
          <div class="tag">
            <h3>VOCABULARY ANALYZER</h3>
            <a href='Public/Modules/vfa/vfa.php' title='vocabulary analyzer'><img src="Assets/images/home-vocabanalyzer.gif" /></a> 
            <p>Explore our useful Chinese article segmentation, annotation and profiling tool.</p>
          </div>          	
        </div>
        
        <div class="fcolumn">
          <div class="tag">
            <h3>CHINESE DICTIONARY</h3>
            <a href='Public/Modules/dictionary/index.php' title='Chinese dictionary'><img src="Assets/images/home-onlinedictionary.gif" /></a> 
            <p>A handy Chinese dictionary with &quot;fuzzy&quot; search options to  enhance your experience.</p>
          </div>          	
        </div>
        
        <div class="fcolumn last">
          <div class="tag">
            <h3>ADD NEW VOCABULARY</h3>
            <a href='Public/Modules/new/addw.php' title='Add vocabulary to database'><img src="Assets/images/home-addvocabulary.gif" /></a>
            <p>Want to contribute? Help us improve our Chinese vocabulary database!</p>
          </div>          	
        </div>
        <!-- InstanceEndEditable -->        
              </div><!--end COL2-->      
          </div><!--end FEATURES-->
        </div><!--end FEATURES-WRAPPER-->
        
        <div class="push">&nbsp;</div>
	</div>
  
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
 <!--           <a href="http://validator.w3.org/check/referer">XHTML</a> | <a href="http://jigsaw.w3.org/css-validator/check/referer">CSS</a></p>-->
        </div>
        
        
      </div><!--end FOOTER-->
    </div><!--end FOOTER-WRAPPER-->
</div>
</body>
<!-- InstanceEnd --></html>
