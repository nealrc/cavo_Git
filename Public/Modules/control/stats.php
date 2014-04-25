<?php require_once('../../../Private/config/config.php');?>
<?php require_once('autha.php'); ?>
<?php
$level_parameters = array();
mysql_select_db($database_cavoconnection, $cavoconnection);
$level_query="SELECT `Level`, `Passrate` FROM `base_level_def`"; 
$level = mysql_query($level_query, $cavoconnection) or die(mysql_error());
$row_level = mysql_fetch_assoc($level);
$totalRows_level = mysql_num_rows($level);
do{
	$levels[] = $row_level['Level'];
	// $passrate[] = $row_level['Passrate'];
} while ($row_level = mysql_fetch_assoc($level));
mysql_free_result($level);

$query = "SELECT `id`, `name`, `description` FROM `base_test`";
$result =mysql_query($query, $cavoconnection) or die(mysql_error());;
$rows = mysql_fetch_assoc($result);
do{
	$TT[$rows['id']]['name'] = $rows['name'];
	//$TT[$rows['id']]['desc'] = $rows['description'];
}while($rows = mysql_fetch_assoc($result));
mysql_free_result($result);

//level info
foreach($TT as $tid => $arr){
	$query = "SELECT count(*) AS 'num' FROM `cavo_level` WHERE `test` = $tid";
	$result = mysql_query($query, $cavoconnection) or die(mysql_error());
	$num_rows = mysql_num_rows($result);	
	if($num_rows >0){	
		$query2 = "SELECT `test`, `level`, count(*) AS 'num' FROM `cavo_level` WHERE `test` = $tid GROUP BY `level`";
		$result2 = mysql_query($query2, $cavoconnection) or die(mysql_error());
		$num_rows2 = mysql_num_rows($result2);
		$rows2 = mysql_fetch_assoc($result2);
		if($num_rows2 > 0){
			do{
				$info[$arr['name']][$rows2['level']] = $rows2['num'];
			}while($rows2 = mysql_fetch_assoc($result2));
		}
		mysql_free_result($result2);
	}
	mysql_free_result($result);
}

// capacity
$query = "SELECT count(*) AS 'num' FROM `tiku_cavo_test`";
$result = mysql_query($query, $cavoconnection) or die(mysql_error());
$rows = mysql_fetch_assoc($result);
$capacity = $rows['num'];
mysql_free_result($result);

// pecentage tested
$query = "SELECT `test`, count(DISTINCT (`QuestionID`)) AS 'num' FROM `test_answers` GROUP BY `test`";
$result = mysql_query($query, $cavoconnection) or die(mysql_error());
$rows = mysql_fetch_assoc($result);
do{
	$coverage[$TT[$rows['test']]['name']] = $rows['num'];
}while($rows = mysql_fetch_assoc($result));
mysql_free_result($result);

// passrate info
$query = "SELECT `test`, count(`QuestionID`) AS 'num' FROM `test_answers` WHERE `QuestionID`= `AnswerID` GROUP BY `test`";
$result = mysql_query($query, $cavoconnection) or die(mysql_error());
$rows = mysql_fetch_assoc($result);
do{
	$correct_answer[$TT[$rows['test']]['name']] = $rows['num'];
}while($rows = mysql_fetch_assoc($result));
mysql_free_result($result);

$query = "SELECT `test`, count(`QuestionID`) AS 'num' FROM `test_answers` GROUP BY `test`";
$result = mysql_query($query, $cavoconnection) or die(mysql_error());
$rows = mysql_fetch_assoc($result);
do{
	$total_ans[$TT[$rows['test']]['name']] = $rows['num'];
}while($rows = mysql_fetch_assoc($result));
mysql_free_result($result);
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml"><!-- InstanceBegin template="/Templates/control.dwt" codeOutsideHTMLIsLocked="false" -->
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<!-- InstanceBeginEditable name="doctitle" -->
<title>CAVO</title>
<!-- InstanceEndEditable -->
<link rel="stylesheet" type="text/css" href="../../../Assets/css/style.css" />
<link rel="stylesheet" type="text/css" href="../../../Assets/css/control_main.css" />
<!-- InstanceBeginEditable name="head" -->
<link rel="stylesheet" type="text/css" href="../../../Assets/js/jquery.ui/css/flick/jquery-ui-1.8.5.custom.css"/>
<link rel="stylesheet" type="text/css" href="../../../Assets/css/control_settings.css" />
<script type="text/javascript" src="../../../Assets/js/jquery-1.4.2.min.js"></script>
<script type="text/javascript" src="../logic/cdock.main.pack.js"></script>
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
                    <li><a href="index.php">Control Panel</a></li>
                    <li><a href="../test/index.php">Vocabulary Test</a></li>
                    <li><a href="../demo/index.php">Demo</a></li>
                    <li><a href="../../../index.php">Home</a></li>              
                </ul>
              </div>
            </div>
            <!--end SECTION1-->
            
            <?php if(isset($_SESSION['MM_Username'])){ ?>
            <div id="loginstatus">
              Welcome <?php echo $_SESSION['MM_Username']; ?> : <a href="profile.php">My Account</a> | <a href="<?php echo $logoutAction; ?>">Sign Out</a>        
            </div>
            <?php } ?>
            </div><!--end HEADER-->
        </div><!--end HEADER-WRAPPER-->
    
        <div id="maincontent">
            <div id="sidebar-left">
                <h1>CONTROL PANEL NAVIGATION</h1>
                <ul>                
                <li id='report' title='Access your CAVO report'>
                    <h2>CAVO Reports</h2>
                    <img src="../../../Assets/images/reports.png" />
                    <p>View your test data including detailed test records, charts and comprehensive report. If you are an instructor, view your institute report here.</p>
                </li>
                
                <li id='profile' title='Click to view or modify your personal information'>
                    <h2>My Profile</h2>
                    <img src="../../../Assets/images/profile.png" />
                    <p>Update your account information and password here.</p>                        
                </li>
                
                <?php  if($_SESSION['MM_UserGroup']==$role['admin'] || $_SESSION['MM_UserGroup']==$role['instructor']){ ?>
                <li id='user' title='Click to manage user information'>
                    <h2>User Test Records and Profiles</h2>
                    <img src="../../../Assets/images/students.png" />
                    <p>Manage your students' test records and profiles.</p>
                </li>                   
                <?php } ?>
               
                <?php  if($_SESSION['MM_UserGroup']== $role['admin'] || $_SESSION['MM_UserGroup']== $role['instructor'] ){ ?>
                <li id='settings'>
                    <h2>Custom Test &amp; Management</h2>
                    <img src="../../../Assets/images/tests.png" />
                    <p>Manage custom tests and test settings.</p>
                </li> 
                <?php } ?>
                
                <?php  if($_SESSION['MM_UserGroup']== $role['admin']){ ?>
                <li id='stats'>
                    <h2>System Statistics</h2>
                    <img src="../../../Assets/images/data.png" />
                    <p>Information about  system statistics and usage.</p>
                </li>              
                <?php } ?>      
                
                </ul>
            </div><!--end SIDEBAR-left-->
            
            <div id="content-right">
                <!-- InstanceBeginEditable name="content" -->
        <h1>System Statistics</h1>
        
        <div class="setting_info">
			<h2>Vocabulary Levels</h2>
			<table class='prettytable' cellspacing='0'>
            <thead>
                <th scope='col' class='nobg'>Out of <?php echo number_format($capacity); ?> Vocabularies</th>
				<?php foreach($levels as $kk => $l){ ?>
                <th scope='col'>Level <?php echo $l; ?></th>
                <?php } ?>
            </thead>
         	<tbody>
            <?php foreach($info as $tt => $arr){ ?>
            <tr><th scope='row' class='spec'><?php echo $tt; ?></th>
			<?php foreach($levels as $kk => $l){ ?>
            <td><?php if(isset($arr[$l])){echo number_format($arr[$l]);}else{echo 0;} ?></td>
            <?php } ?>
            </tr>
            <?php } ?>
            </tbody></table>            
        </div>

        <div class="setting_info">
			<h2>Vocabulary Coverage</h2>
			<table class='prettytable' cellspacing='0'>
            <thead>
                <th scope='col' class='nobg'>Out of <?php echo number_format($capacity); ?> Vocabularies</th>
                <th scope="col">Num Vocabulary Tested</th>
                <th scope='col'>Percentage</th>
            </thead>
         	<tbody>            
            <?php foreach($coverage as $tt => $n){ ?>
            <tr><th scope='row' class='spec'><?php echo $tt; ?></th>
            <td><?php echo number_format($n); ?></td>
            <td><?php echo (round($n/$capacity, 4)*100).'%'; ?></td>
            </tr>
            <?php } ?>
            </tbody></table>            
        </div>

        <div class="setting_info">
			<h2>Test Passrate</h2>
			<table class='prettytable' cellspacing='0'>
            <thead>
                <th scope='col' class='nobg'>Out of <?php echo number_format($capacity); ?> Vocabularies</th>
                <th scope='col'>Num Tested Questions</th>
                <th scope='col'>Num Correct Answers</th>
                <th scope='col'>Percentage</th>
            </thead>
         	<tbody>
            <?php foreach($correct_answer as $tt => $n){ ?>
			<tr>
            	<th scope='row' class='spec'><?php echo $tt; ?></th>
                <td><?php echo number_format($total_ans[$tt]); ?></td>
                <td><?php echo number_format($n); ?></td>
                <td><?php echo (round($n/$total_ans[$tt], 4)*100).'%'; ?></td>
            </tr>
            <?php } ?>
            </tbody></table>            
        </div>
        <div class="setting_info">&nbsp;</div>
        
		<!-- InstanceEndEditable -->
            </div>
        </div><!--MAINCONTENT-->
        <div class='push'>&nbsp;</div>
    </div><!--CONTENT-WRAPPER-->
  
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
