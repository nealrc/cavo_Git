<?php require_once('../../../Private/config/config.php'); ?>
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
$profileLink="../control/profile.php";
?>
<?php
mysql_select_db($database_cavoconnection, $cavoconnection);
mysql_query("SET NAMES UTF8");

// tiku
// Tikuid 	Vocabulary 	PY 	CN 	EN

// tikulevel
// Index 	Tikuid 	Currentlevel 	Newlevel 	Testid

$N = 10;
for($ii=1;$ii<5;$ii++){
$query = "SELECT DISTINCT a.`tiku_id`,a.`level`, b.`word`, b.`py`, b.`en`, b.`cn` FROM `cavo_level` AS a INNER JOIN `tiku_cavo_test` AS b ON (a.`tiku_id` = b.`id`) WHERE a.`level`=$ii ORDER BY RAND() LIMIT $N";
$result = mysql_query($query, $cavoconnection) or die(mysql_error());
$num = mysql_num_rows($result);
$rows = mysql_fetch_assoc($result);
if($num >0){
	$cc=0;
	do{
		if(isset($cavo[$cc][$rows['level']])){$cc++;}
		$cavo[$cc][$rows['level']]['wd'] = $rows['word'];
		$cavo[$cc][$rows['level']]['id'] = $rows['tiku_id'];
		$cavo[$cc][$rows['level']]['py'] = $rows['py'];
		$cavo[$cc][$rows['level']]['en'] = $rows['en'];
		$cavo[$cc][$rows['level']]['cn'] = $rows['cn'];
	}while($rows = mysql_fetch_assoc($result));
}
mysql_free_result($result);
}

// print_r($cavo);


$query = "SELECT COUNT(`QuestionID`) AS 'size' FROM `test_answers`";
$result = mysql_query($query, $cavoconnection) or die(mysql_error());
$num = mysql_num_rows($result);
$rows = mysql_fetch_assoc($result);
if($num >0){
	$total_records = number_format($rows['size']);
}
mysql_free_result($result);

// answerrecord
// Index Userid QuestionID AnswerID Teststage Testid Identifier
$query = "SELECT COUNT(a.`QuestionID`) AS 'size' FROM `test_answers` AS a WHERE a.`QuestionID` != a.`AnswerID`";
$result = mysql_query($query, $cavoconnection) or die(mysql_error());
$num = mysql_num_rows($result);
$rows = mysql_fetch_assoc($result);
if($num >0){
	$total_wrong = $rows['size'];
}
mysql_free_result($result);

$query = "SELECT a.`QuestionID`, COUNT(a.`QuestionID`) AS 'size', b.`word`, b.`py`, b.`en`, b.`cn` FROM `test_answers` AS a 
 INNER JOIN `tiku_cavo_test` AS b ON (a.`QuestionID` = b.`id`) 
 WHERE a.`QuestionID` != a.`AnswerID` 
 GROUP BY a.`QuestionID` ORDER BY 2 DESC LIMIT $N";
$result = mysql_query($query, $cavoconnection) or die(mysql_error());
$num = mysql_num_rows($result);
$rows = mysql_fetch_assoc($result);
if($num >0){
	do{
		$wrong[$rows['QuestionID']]['wd'] = $rows['word'];
		$wrong[$rows['QuestionID']]['py'] = $rows['py'];
		$wrong[$rows['QuestionID']]['en'] = $rows['en'];
		$wrong[$rows['QuestionID']]['cn'] = $rows['cn'];
		$wrong[$rows['QuestionID']]['rate'] = round($rows['size'] / $total_wrong, 4)*100;
	}while($rows = mysql_fetch_assoc($result));
}
mysql_free_result($result);


$query = "SELECT COUNT(a.`QuestionID`) AS 'size' FROM `test_answers` AS a WHERE a.`QuestionID` = a.`AnswerID`";
$result = mysql_query($query, $cavoconnection) or die(mysql_error());
$num = mysql_num_rows($result);
$rows = mysql_fetch_assoc($result);
if($num >0){
	$total_right = $rows['size'];
}
mysql_free_result($result);

$query = "SELECT a.`QuestionID`, COUNT(a.`QuestionID`) AS 'size', b.`word`, b.`py`, b.`en`, b.`cn` 
	FROM `test_answers` AS a 
	INNER JOIN `tiku_cavo_test` AS b ON (a.`QuestionID` = b.`id`) 
	WHERE a.`QuestionID` = a.`AnswerID` 
	GROUP BY a.`QuestionID` ORDER BY 2 DESC LIMIT $N";
$result = mysql_query($query, $cavoconnection) or die(mysql_error());
$num = mysql_num_rows($result);
$rows = mysql_fetch_assoc($result);
if($num >0){
	do{
		$correct[$rows['QuestionID']]['wd'] = $rows['word'];
		$correct[$rows['QuestionID']]['py'] = $rows['py'];
		$correct[$rows['QuestionID']]['en'] = $rows['en'];
		$correct[$rows['QuestionID']]['cn'] = $rows['cn'];		
		$correct[$rows['QuestionID']]['rate'] = round($rows['size'] / $total_right, 4)*100;
	}while($rows = mysql_fetch_assoc($result));
}
mysql_free_result($result);

$M = 5;
// testrecord
// Index 	Userid 	Testdate 	Score 	Duration 	Testid 	Identifier
$query = "SELECT a.`user`,a.`score`, b.`Firstname`, b.`Lastname`, b.`University`, c.`name` AS 'Test' FROM `test_records` AS a 
INNER JOIN `user` AS b ON (a.`user` = b.`Userid`)
INNER JOIN `base_test` AS c ON (a.`test` = c.`id`)
GROUP BY a.`user` ORDER BY 2 DESC LIMIT $M";
$result = mysql_query($query, $cavoconnection) or die(mysql_error());
$num = mysql_num_rows($result);
$rows = mysql_fetch_assoc($result);
if($num >0){
	do{
		$user[$rows['user']]['test'] = $rows['Test'];
		$user[$rows['user']]['py'] = $rows['py'];
		$user[$rows['user']]['en'] = $rows['en'];
		$user[$rows['user']]['cn'] = $rows['cn'];
	}while($rows = mysql_fetch_assoc($result));
}
mysql_free_result($result);
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
<link rel="stylesheet" type="text/css" href="../../../Assets/css/info.css"/>
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
            <div id='infowrapper'>
                <h1>CAVO Digest<span class='titledate'><script>var d=new Date();document.write(d);</script></span></h1>
                
                <table class='prettytable' cellspacing='0'>
                <caption>Random 10 words FROM each 4 CAVO difficulty levels</caption> 
                <thead>
                <tr>
                <?php for($ii=0;$ii<4;$ii++){ ?>
                	<th scope="col">Level <?php echo $ii+1;?></th>
                <?php } ?>
                </tr>
                </thead>
                <tbody>
                <?php for($ii=0;$ii<count($cavo);$ii++){ ?>
                <tr class='spec'>
					<?php for($jj=0; $jj<4; $jj++){ ?>
                	<td><?php echo $cavo[$ii][$jj+1]['wd']?></td>
                <?php } ?>
                </tr>
                <?php } ?>
                </tbody>
                </table>
                
                                
                <table class='prettytable' cellspacing='0'>
                <caption>Top 20 words people got right (out of <?php echo $total_records; ?> test records)</caption>
                <thead>
                <tr>
                	<th scope='col'>Vocabulary</th>
                    <th scope='col'>Success Rate (%)</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach($correct as $id => $arr){ ?>
                	<tr><td><?php echo $arr['wd'];?></td><td><?php echo $arr['rate'];?></td></tr>
                <?php } ?>
                </tbody>
                </table>

                <table class='prettytable' cellspacing='0'>
                <caption>Top 20 words people got wrong (out of <?php echo $total_records; ?> test records)</caption>
                <thead>
                <tr>
                    <th scope='col'>Vocabulary</th>
                    <th scope='col'>Failure Rate (%)</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach($wrong as $id => $arr){ ?>
                	<tr><td><?php echo $arr['wd'];?></td><td><?php echo $arr['rate'];?></td></tr>
                <?php } ?>
                </tbody>
                </table>
                </ol>
                
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
