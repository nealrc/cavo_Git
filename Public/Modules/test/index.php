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
mysql_select_db($database_cavoconnection, $cavoconnection);

//get number of questions in each level and number of levels
$query_NumOfQuestions = "SELECT sum(`NumOfQuestions`) AS qtot, count(*) as size FROM `base_level_def`";
$NumOfQuestions = mysql_query($query_NumOfQuestions, $cavoconnection) or die(mysql_error());
$row_NumOfQuestions = mysql_fetch_assoc($NumOfQuestions);
$sum=$row_NumOfQuestions['qtot'];
$level_count = $row_NumOfQuestions['size'];
mysql_free_result($NumOfQuestions);


// user is native speaker or not
$query = "SELECT `native` FROM `user` WHERE `Userid` =".$_SESSION['MM_Userid'];
$result = mysql_query($query, $cavoconnection) or die(mysql_error());
$rows = mysql_fetch_assoc($result);
$n = mysql_num_rows($result);
if($n>0){
	$native = $rows['native'];
}
mysql_free_result($result);

// check for custom test
$query = "SELECT `University` FROM `user` WHERE `Userid` = ".$_SESSION['MM_Userid'];
$result = mysql_query($query) or die(mysql_error());
$row = mysql_fetch_assoc($result);
$school =$row['University'];	
mysql_free_result($result);

/*
$query = "SELECT a.`test` AS test_id, a.`date_start`, a.`date_end`, a.`active`, c.`name` AS test_name, c.`description` FROM `school_test` AS a 
		  LEFT JOIN `base_test` AS c ON (c.`id` = a.`test`)
		  WHERE  a.`school` = $school ORDER BY a.`date_create` DESC";
 */
$query = "Select * from get_active_customtests Where school = ".$school;
$result = mysql_query($query, $cavoconnection) or die(mysql_error());
$rows = mysql_fetch_assoc($result);
$n = mysql_num_rows($result);
if($n>0){
	$ctest = array();
	$c =0;
	do {
		$ctest[$c][$rows['test_id']]['name']= $rows['test_name'];
		$ctest[$c][$rows['test_id']]['description']= $rows['description'];
		$ctest[$c][$rows['test_id']]['start']= $rows['date_start'];
		$ctest[$c][$rows['test_id']]['end']= $rows['date_end'];
		$ctest[$c][$rows['test_id']]['active']= $rows['active'];
		$c++;
	} while($rows = mysql_fetch_assoc($result));
	
	//print_r($ctest);
}
mysql_free_result($result);
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml"><!-- InstanceBegin template="/Templates/functions.dwt" codeOutsideHTMLIsLocked="false" -->
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<!-- InstanceBeginEditable name="doctitle" -->
<title>CAVO - Vocabulary Tests</title>
<!-- InstanceEndEditable -->
<link rel="stylesheet" type="text/css" href="../../../Assets/css/style.css" />
<!-- InstanceBeginEditable name="head" -->
<link rel="stylesheet" type="text/css" href="../../../Assets/css/test_main.css" />
<link rel="stylesheet" type="text/css" href="../../../Assets/js/jqModal/jqModal.css" />
<link rel="stylesheet" type="text/css" href="../../../Assets/css/test_components.css" />
<link rel="stylesheet" type="text/css" href="../../../Assets/css/test_exit.css" />
<script type="text/javascript" src="../../../Assets/js/jquery-1.4.2.min.js"></script>
<script type="text/javascript" src="../../../Assets/js/jquery.form.min.js"></script>
<script type="text/javascript" src="../../../Assets/js/jqModal/jqModal.js"></script>
<script type="text/javascript" src="../logic/test.pack.js"></script>
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
                  <li><a href="index.php">Vocabulary Test</a></li>
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
        <div id="sidebar">
          <h1>Vocabulary TEST</h1>
          <p>Each of the following tests contains <?php echo $level_count;?> difficulty levels. The test will start with easiest level then move to higher levels based on test performance. During the test, the full range of difficulties will be utilized to score your language proficiency. Your final score will be calculated based on both the time taken and the accuracy of your responses.</p>
          <p>There are  <?php echo $sum; ?> questions in each CAVO standard test.</p>
          <p>Good luck!</p>
        </div><!--end SIDEBAR-->
        
        <div class="col2">
            <div class="fcolumn">
                <div class="tag test" id="1">
                    <h3>PINYIN TEST</h3><img src="../../../Assets/images/test_py.gif" /> 
                </div>
                <p>Select the most appropriate pinyin for the question.</p>
            </div>
            
            <div class="fcolumn">
                <div class="tag test" id="2">
                    <h3>CHINESE-ENGLISH TEST</h3><img src="../../../Assets/images/test_cnen.gif" /> 
                </div>
                <p>Select the most appropriate English interpretation for the question.</p>
            </div>
            
            <div class="fcolumn last">          
                <div class="tag test" id="3">
                    <h3>CHINESE-CHINESE TEST</h3><img src="../../../Assets/images/test_cncn.gif" /> 
                </div>
                <p>Select the most appropriate Chinese interpretation for the question.</p>
            </div>          
        </div><!--end COL2-->
		
		<?php if(isset($ctest) && count($ctest)>0){ ?>
		<div class="ccol">
		<h2>Custom Test(s)</h2>
        <?php
		foreach($ctest as $k => $arr){
			foreach($arr as $tid => $sarr){ 
				if( $sarr['active']==1 && ( ( ( !is_null($sarr['start']) && $sarr['start'] <= date('Y-m-d') ) && ( !is_null($sarr['end']) && $sarr['end'] >= date('Y-m-d') ) ) || 							
					( is_null($sarr['start']) && is_null($sarr['end']) ) ||							
					( !is_null($sarr['start']) && is_null($sarr['end']) && $sarr['start'] <= date('Y-m-d') ) ||
					( !is_null($sarr['end']) && is_null($sarr['start']) && $sarr['end'] >= date('Y-m-d') ) ) )
				{
			?>
            <ul><li>            
            <div class='ctesttp'>
				<div class='ctestlt'>
					<a href='#' class='test' id='<?php echo $tid;?>'><?php echo $sarr['name'];?> </a>
                </div>
				<div class='ctestrt'>
				<?php
					$s = is_null($sarr['start'])?'na':date('Y/m/d', strtotime($sarr['start']));
					$e = is_null($sarr['end'])?'na':date('Y/m/d', strtotime($sarr['end']));
					if($s != 'na'  || $e != 'na'){echo $s.' ~ '.$e;}
				?>
                </div>
			</div>
            
			<div class='ctestbt'>
				<?php echo is_null($sarr['description'])?'':$sarr['description']; ?>
			</div>
            </li></ul>
			<?php }}} ?> 
		</div>
		<?php } ?>
		<!--Test box related contents-->
        <!--Modal Dialog-->
        <div id="diabox" class="jqmDialog jqmdWide">
            <div class="jqmdTL"><div class="jqmdTR">
            	<div class="jqmdTC"></div><!--Title of Modal-->
            </div></div>
                        
          <div class="jqmdBL"><div class="jqmdBR">
				<div class="jqmdBC">
                    <div class="jqmdMSG"></div><!--body of Modal-->
                </div>
            </div></div>
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
				<div class='txt'> of </div>
				<div id='qtot'></div>
			</div>
            <div id="testerror" class="errorText"></div>
            <div id="testdisplay">
				<form action="#" method="post" name="testform" id="testform">							
					<div id="testsubmit"><input type="submit" name="submitbtn" value="Next >" /></div>
                    <div id='choices'></div>
                </form>
            </div>
        </div>

        
        <?php if(!$native) { ?>

        <div id="userinfo">
            <h2>Your learning history</h2>
            <h3>Please tell us about your learning experience:</h3>
            <div id="r" class="errorText"></div>
            <div id="f">
                <form action="userinfovalidate.php" method="post" name="form1" id="form1">
                <p><label>1: How long have you learned Chinese?</label> <input type="text" name="learn" id="learn" class="short" /> (month)</p>
                <p><label>2: How long have you spent in China?</label> <input type="text" name="speak" id="speak" class="short" /> (month)</p>
                <div id='starttest'><input type="submit" name="submit1" id="submit1" value="Start Test!" /></div>
                </form>
            </div>
        </div>
        
        <?php } ?>
		
		<!--Test Cache Location-->
		<div id='testcache'>
			<div id='tmpquestioncount'></div>
			<div id='tmpqtot'></div>
			<div id='tmptestsubject'></div>
			<div id='tmptestdisplay'></div>			
		</div>
		
		<!--Wrap up test-->
        <div id="finishtest">
            <h2>Test finished!</h2>
			<div class="black_h3"><h3>What do you want to do next?</h3></div>
            <ol>
                <li><a href='#' name='review'>Submit the test and review your score</a></li>
                <li><a href='#' name='cancel'>Cancel the test</a></li>
            </ol>
        </div>
		<!--Wrap up test-->
        <div id="score"></div>		
        <!--Exit Command - Overlay Class-->
        <div class="exit" id="exit">        
            <div id="ex3b" class="jqmConfirmWindow">
                <div class="jqmConfirmTitle clearfix"><h1>Confirmation exit</h1></div>
                <div class="jqmConfirmContent"><p class="jqmConfirmMsg"></p><p>Are you sure want to cancel the test?</p></div>        
                <p><input type="button" id="exit_yes" class='yes' value="Yes" />
				<input type="button" id="exit_no" class='no' value="No" /></p>
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
