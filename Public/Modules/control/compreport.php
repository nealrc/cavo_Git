<?php require_once("../../../Private/config/config.php"); ?>
<?php require_once('auth.php'); ?>
<?php 
$id=isset($_REQUEST['id'])?$_REQUEST['id']:$_SESSION['MM_Userid'];

mysql_select_db($database_cavoconnection, $cavoconnection);

$query = "SELECT a.`Firstname`, a.`Lastname`, a.`University`, b.`name` from `user` AS a LEFt JOIN `base_school` AS b ON (a.`University`=b.`id`) WHERE `Userid`=".$id;
$result = mysql_query($query, $cavoconnection) or die(mysql_error());
$rows = mysql_fetch_assoc($result);
$total_nums = mysql_num_rows($result);	
if($total_nums >0){
	do{
		$user = ucfirst(strtolower($rows['Firstname'])).' '.ucfirst(strtolower($rows['Lastname']));
		$sid = $rows['University'];
		$s = explode(' ', $rows['name']);
		$ss = '';
		foreach($s as $k => $v){
			$ss.=ucfirst($v).' ';
		}
		$school = $ss;

	}while($rows = mysql_fetch_assoc($result));
}
mysql_free_result($result);
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml"><!-- InstanceBegin template="/Templates/control.dwt" codeOutsideHTMLIsLocked="false" -->
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<!-- InstanceBeginEditable name="doctitle" -->
<title>CAVO Comprehensive Report</title>
<!-- InstanceEndEditable -->
<link rel="stylesheet" type="text/css" href="../../../Assets/css/style.css" />
<link rel="stylesheet" type="text/css" href="../../../Assets/css/control_main.css" />
<!-- InstanceBeginEditable name="head" -->
<link rel="stylesheet" type="text/css" href="../../../Assets/js/jquery.ui/css/flick/jquery-ui-1.8.5.custom.css"/>
<!--<link rel="stylesheet" type="text/css" href="../../../Assets/js/jquery.ui/css/south-street/jquery-ui-1.8.custom.css"/>-->
<script type="text/javascript" src="../../../Assets/js/jquery-1.4.2.min.js"></script>
<script type="text/javascript" src="../../../Assets/js/jquery.form.min.js"></script>
<script type="text/javascript" src="../../../Assets/js/jquery.blockui.min.js"></script>
<script type="text/javascript" src="../../../Assets/js/Highcharts-2.1.4/highcharts.js"></script>
<script type="text/javascript" src="../logic/cdock.main.pack.js"></script>
<script type="text/javascript" src="../logic/cdock.rate.pack.js"></script>
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
        <h1>CAVO Comprehensive Report for <span class='thighlight'><?php echo $user; ?></span></h1>
        <?php if(isset($_REQUEST['id'])){?>
        <p style="text-align:right">
        	<a href='compireport.php'>Select institute</a> >
            <a href='compiireport.php?id=<?php echo $sid; ?>'><?php echo $school;?></a>
        </p>
		<?php } ?>
        <div class='ccr'>
          <?php include('understandccr.php'); ?>
        </div>    
        
        <div class='chartwrapper' id='i_<?php echo $id;?>'></div>
        
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
