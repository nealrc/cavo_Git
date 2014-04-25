<?php require_once('../../../Private/config/config.php');?>
<?php require_once('authm.php'); ?>
<?php
$level_parameters = array();
mysql_select_db($database_cavoconnection, $cavoconnection);
$level_query="SELECT * FROM base_level_def"; 
$level = mysql_query($level_query, $cavoconnection) or die(mysql_error());
$row_level = mysql_fetch_assoc($level);
$totalRows_level = mysql_num_rows($level);
if($totalRows_level > 0){
	do{
		$level_parameters[$row_level['Level']]['Passrate'] = $row_level['Passrate'];
		$level_parameters[$row_level['Level']]['TimeWeight'] = $row_level['TimeWeight'];
		$level_parameters[$row_level['Level']]['NumOfQuestions'] = $row_level['NumOfQuestions'];
		$level_parameters[$row_level['Level']]['Lowerbound'] = $row_level['Lowerbound'];
	} while ($row_level = mysql_fetch_assoc($level));
}
mysql_free_result($level);

$query = "SELECT `id`, `name` FROM `base_test`";
$result =mysql_query($query, $cavoconnection) or die(mysql_error());;
$rows = mysql_fetch_assoc($result);
do{
	$TT[$rows['id']] = $rows['name'];
}while($rows = mysql_fetch_assoc($result));
mysql_free_result($result);

// set a limit to calibrate, otherwise takes too long
$query = "SELECT `test`, count(`QuestionID`) AS 'num' FROM `test_answers` GROUP BY `test`";
$result = mysql_query($query, $cavoconnection) or die(mysql_error());
$rows = mysql_fetch_assoc($result);
do{
	$record[$rows['test']] = $rows['num'];
}while($rows = mysql_fetch_assoc($result));
mysql_free_result($result);

$c=0;
$lsize = 100;
foreach($record as $test => $num){
	$multi = floor($num/$lsize);
	$rem = fmod($num, $lsize);
	$steps[$c]['test'] = $test;
	$steps[$c]['tname'] = $TT[$test];
	$steps[$c]['offset'] = $lsize;
	$steps[$c]['mul'] = $multi;
	$steps[$c]['rem'] = $rem;
	$c++;
}
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
<script type="text/javascript" src="../../../Assets/js/jquery.ajaxq-0.0.1.js"></script>
<script type="text/javascript" src="../../../Assets/js/jquery.blockui.min.js"></script>
<script type="text/javascript" src="../../../Assets/js/jquery.jeditable.pack.js"></script>
<script type="text/javascript" src="../logic/cdock.main.pack.js"></script>
<script type="text/javascript" src="../logic/cdock.settings.min.js"></script>
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
                
        <h1>Custom Test</h1>
        <div class="setting_info">
        <p><strong>Get started with custom test</strong>:</p>        
        <ul>
            <li>
            <div class='divleft'><span class="ui-icon ui-icon-star"></span></div>
            <div class='divright'><a href="customtest/index.php">Create a custom test</a></div>
            </li>
            
            <li>
            <div class='divleft'><span class="ui-icon ui-icon-star"></span></div>
            <div class='divright'><a href="customtest/mag.php">Manage custom tests</a></div>
            </li>
                        
        </ul>
        </div>
                
		<?php  if($_SESSION['MM_UserGroup']==$role['admin'] ){ ?>			
        <p>&nbsp;</p>
        <h1>Test Setting Management</h1>
        <div class="setting_info">
		  <ul>
                <li>
                	<div class='divleft'><span class="ui-icon ui-icon-star"></span></div>
                    <div class='divright'>
                    <strong>Passrate:</strong> this value determines if test should upgrade difficulty level. <strong>During the vocabulary test</strong>, program will dynamically calculate the tester's accuracy ratio. If the ratio is large or equal to the specified passrate for current level, the difficulty level will then be increased.
                    </div></li>
                    
                <li>
                	<div class='divleft'><span class="ui-icon ui-icon-star"></span></div>
                    <div class='divright'>                
                <strong>Time Weight:</strong> this value determines the <strong>percentage of the time-effect over the final score</strong>. For example, 0.15 means 15% out of 800 (total points), equivalent to 120 points, will be awarded for high time efficiency. High time efficiency is measured based on average test duration for each specific one.
                </div></li>                
                
                <li>
                	<div class='divleft'><span class="ui-icon ui-icon-star"></span></div>
                    <div class='divright'>
                <strong>Num. Of Questions:</strong> number of questions assigned for each level. Generally, you are recommended to assign equal number of questions for each level.
                </div></li>
                
                <li>
                	<div class='divleft'><span class="ui-icon ui-icon-star"></span></div>
                    <div class='divright'>
                <strong>Lower Bound: </strong> the lower bound for <strong>test level calibration process</strong>. After the system has been run for some period, you will need to calibrate the difficulty level of each vocabulary entry. The calibration process will automatically calculate the average accuracy ratio for each entry. if the accuracy ratio is large or equal to the lower bound, the difficulty level for this entry will be upgraded.</div></li>
			</ul>            
            
			<table class='prettytable' id='mytable' cellspacing='0'>
            <tr>
                <th scope='col' class='nobg'>Settings</th>
                <th scope='col'>Passrate</th>
                <th scope='col'>Time Weight</th>
                <th scope='col'>Num. Of Questions</th>
                <th scope='col'>Lower Bound</th>
            </tr>
         	<tbody>
			<?php
				foreach($level_parameters as $l => $arr){
					$a = $arr['Passrate'];
					$b = $arr['TimeWeight'];
					$c = $arr['NumOfQuestions'];
					$d = $arr['Lowerbound'];					
					$av = $l."_Passrate";
					$bv = $l."_TimeWeight";
					$cv = $l."_NumOfQuestions";
					$dv = $l."_Lowerbound";					
            ?>  
            <tr>
            <th scope='row' class='spec'>Level <?php echo $l; ?></th>
            <td class='editable' id="<?php echo $av; ?>"><?php echo $a; ?></td>
            <td class='editable' id="<?php echo $bv; ?>"><?php echo $b; ?></td>
            <td class='editable' id="<?php echo $cv; ?>"><?php echo $c; ?></td>
            <td class='editable' id="<?php echo $dv; ?>"><?php echo $d; ?></td>
            </tr>
            <?php } ?>                      
			</tbody></table>
        </div>
        
        <h1>Difficulty level calibration</h1>
        <div class="setting_info">
        <h2>About test calibration</h2>
        <ul>
            <li>
                <div class='divleft'><span class="ui-icon ui-icon-star"></span></div>
                <div class='divright'><strong>Vocabulary Difficulty Index (VDI) Management:</strong> this value represents the <em>"true"</em> difficulty level for each vocabulary entry. When more and more tests have been taken, the difficulty levels need to be adjusted to reflect the user's performance.</div></li>
			<li>
                <div class='divleft'><span class="ui-icon ui-icon-star"></span></div>
                <div class='divright'><strong>Table Synchronization:</strong> when VDIs are obtained, synchronization will then be performed to pair the vocabulary table with difficulty level table.</div></li>        
        </ul>
        <p>Hit the start calibrate button to start calibration. This process might take a while.</p>
		<br />
        
        <div style="float:left; margin:20px 10px 0 0; width:150px;"><input type="button" id="startcali" class="ui-button" value="Start Calibration" /></div>
        <div style="float:left; margin:20px 0 0 10px; width:400px;"><div id='caliresult'></div></div>
        <div id='caliinfo'><?php echo json_encode($steps); ?></div>        
        </div>
        
        
        <?php } ?>
                
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
