<?php 
require_once('../../../Private/config/config.php');
require_once('clearsession.php');
?>
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
$profileLink = "../control/profile.php";
?>
<?php
mysql_select_db($database_cavoconnection, $cavoconnection);

//get number of questions in each level and number of levels
$query_NumOfLevels = "SELECT count(*) as num FROM base_level_def";
$NumOfLevels = mysql_query($query_NumOfLevels, $cavoconnection) or die(mysql_error());
$row_NumOfLevels = mysql_fetch_assoc($NumOfLevels);
$level_count = $row_NumOfLevels['num'];
mysql_free_result($NumOfLevels);

$_SESSION['TotalQuestionsRequired'] = 5;
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml"><!-- InstanceBegin template="/Templates/functions.dwt" codeOutsideHTMLIsLocked="false" -->
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<!-- InstanceBeginEditable name="doctitle" -->
<title>CAVO - DEMO</title>
<!-- InstanceEndEditable -->
<link rel="stylesheet" type="text/css" href="../../../Assets/css/style.css" />
<!-- InstanceBeginEditable name="head" -->
<link rel="stylesheet" type="text/css" href="../../../Assets/css/test_main.css" />
<link rel="stylesheet" type="text/css" href="../../../Assets/js/jqModal/jqModal.css" />
<link rel="stylesheet" type="text/css" href="../../../Assets/css/test_components.css" />
<link rel="stylesheet" type="text/css" href="../../../Assets/css/test_exit.css" />
<link rel="stylesheet" type="text/css" href="../../../Assets/css/demo.css"/>
<script type="text/javascript" src="../../../Assets/js/jquery-1.4.2.min.js"></script>
<script type="text/javascript" src="../../../Assets/js/jquery.form.min.js"></script>
<script type="text/javascript" src="../../../Assets/js/jqModal/jqModal.js"></script>
<script type="text/javascript" src="../logic/demo.pack.js"></script>
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
                  <li><a href="index.php">Demo</a></li>
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
        <div id="sidebar">
          <h1>DEMO TEST</h1>
          <p>The demo tests have the exact same look and feel as the standard tests.  However, demo tests:</p>
          <ol>
            <li>do NOT  monitor your test in progress, thus do NOT smartly adjust test difficulties,</li>
            <li>do NOT record your test results, thus do NOT provide performance charts showing your progresses over time,</li>
            <li>do NOT monitor your time efficiency,</li>
            <li>only have <?php echo $_SESSION['TotalQuestionsRequired']; ?> questions in each test.</li>
          </ol>
<p>To  enjoy more features of our vocabulary test, <strong><a href="http://cavo.nealrc.org/Public/Modules/register/register.php">Join Now!</a></strong>          </p>
<p><b>Good luck!</b></p>
        </div><!--end SIDEBAR-->
        
        
        <div class="col2">   
            <div class="fcolumn">
                <div class="tag" id="py">
                    <h3>PINYIN DEMO</h3>
                    <img src="../../../Assets/images/test_py.gif" /> 
                    <p>Select the most appropriate pinyin for the question.</p>
                </div>
            </div>
            
            <div class="fcolumn">
                <div class="tag" id="en">
                    <h3>CHINESE-ENGLISH DEMO</h3>
                    <img src="../../../Assets/images/test_cnen.gif" /> 
                    <p>Select the most appropriate English interpretation for the question.</p>
                </div>            
            </div>
            
            <div class="fcolumn last">          
                <div class="tag" id="cn">
                    <h3>CHINESE-CHINESE DEMO</h3>
                    <img src="../../../Assets/images/test_cncn.gif" /> 
                    <p>Select the most appropriate Chinese interpretation for the question.</p>
                </div>
            </div>
        </div><!--end COL2-->
        
        <div style="margin:20px 0; width:100%; float:left;"></div>        
        

		<!--Test box related contents-->
        <!--Modal Dialog-->
        <div id="diabox" class="jqmDialog jqmdWide">
            <div class="jqmdTL">
                <div class="jqmdTR">
                	<div class="jqmdTC"></div><!--Title of Modal-->
                </div>
            </div>        
            <div class="jqmdBL">
                <div class="jqmdBR">
                    <div class="jqmdBC">
                        <div class="jqmdMSG"></div><!--body of Modal-->
                    </div>
                </div>
            </div>
            <input type="image" src="../../../Assets/images/dialog/close.gif" class="jqmdX jqmClose" />
        </div>

        <!--Test area-->
        <div id="testing">
            <div id="qwrapper">
                <div class='txt'>Question :</div>
                <div id='testsubject'></div>
            </div>
            <div id="indicator">
                <div class='txt'>Question</div>
                <div id='questioncount'></div>
                <div class='txt'>of <?php echo $_SESSION['TotalQuestionsRequired']; ?></div>
            </div>
            <div id="testerror" class="errorText"></div>
            <div id="testdisplay">
                <form action="demogenerator.php" method="post" name="testform" id="testform">
                    <div id="testsubmit"><input type="submit" name="testsubmit" value="Next >" /></div>                
                    <div id='choices'></div>
                </form>
            </div>
        </div>
        
        <!--Test Cache Location-->
        <div id='testcache'>
            <div id='tmpquestioncount'></div>
            <div id='tmptestsubject'></div>
            <div id='tmptestdisplay'></div>			
        </div>

        <!--Wrap up test-->
        <div id="finishtest" class='demoreportwrapper'>
            <h2>Demo Test Report</h2>
            <h3>Your accuracy: <span class='score'></span></h3>            
            <div id='result_details'></div>
            <h3>Demo Tests:</h3>
            <ul>
                <li>do NOT monitor your test in progress, thus do NOT smartly adjust test difficulties,</li>
                <li>do NOT record your test results, thus do NOT provide performance charts showing your progresses over time,</li>
                <li>do NOT monitor your time efficiency,</li>
                <li>only have <?php echo $_SESSION['TotalQuestionsRequired']; ?> questions in each test.</li>
            </ul>        
            <p>To enjoy more features of our vocabulary tests, <a href="../register/register.php">join us now!</a></p>
            <p>&nbsp; </p>
            
        </div>
    
        <!--Exit Command - Overlay Class-->
        <div class="exit" id="exit">        
            <div id="ex3b" class="jqmConfirmWindow">
                <div class="jqmConfirmTitle clearfix">
                    <h1>Confirmation exit</h1>
                </div>
            
                <div class="jqmConfirmContent">
                    <p class="jqmConfirmMsg"></p>
                    <p>Are you sure want to exit the demo test?</p>
                </div>
            
                <p><input type="submit" name="yes" class='yes' id='exit_yes' value="Yes" />
                	<input type="submit" name="yes" class='no' id='exit_no' value="No" /></p>
            </div>
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
