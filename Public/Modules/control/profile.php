<?php require_once('../../../Private/config/config.php');?>
<?php require_once('auth.php'); ?>
<?php
mysql_select_db($database_cavoconnection, $cavoconnection);

$query = "SELECT * FROM `base_age`";
$result = mysql_query($query, $cavoconnection) or die(mysql_error());
$rows = mysql_fetch_assoc($result);
$totalRows = mysql_num_rows($result);
do {
	$agerange[$rows['id']]= $rows['range'];
} while ($rows = mysql_fetch_assoc($result));
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
<script type="text/javascript" src="../../../Assets/js/jquery-1.4.2.min.js"></script>
<script type="text/javascript" src="../../../Assets/js/jquery.blockui.min.js"></script>
<script type="text/javascript" src="../../../Assets/js/jquery.form.min.js"></script>
<script type="text/javascript" src="../logic/cdock.main.pack.js"></script>
<script type="text/javascript" src="../logic/cdock.profile.min.js"></script>
<style>
#profile-wrapper{widht:700px; margin:0; padding:0;}
.formwrapper{display:none; margin-top:20px; width:320px; float:left;}
	.formwrapper h1{margin-bottom:20px;}
	.profileitems {float:left; margin-top:10px; width:320px;}
	.profileitems label{float:left; width:120px; margin-right:10px; font-weight:bold;}
	.profileitems input{float:left; width:170px;}	
	.profileitems .fm-button{margin-right:20px; margin-top:20px; float:right;}
	input#native_yes, input#native_no{width:10px;}
	label#for_native_yes, label#for_native_no{width:50px; float:left;}
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
        
        <div id='profile-wrapper'>
        
        	<div class='formwrapper' id='f1w'>
            <h1>Your Profile</h1>
            <form id='editprofile' name='editprofile' method="post" action="calls/profile_process.php">
            	<div class='profileitems' id='result1'></div>
                <div class='profileitems'><label>First name</label><input type="text" name="firstname" /></div>            
                <div class='profileitems'><label>Last name</label><input type="text" name="lastname" /></div>
                <div class='profileitems'><label>Email</label><input type="text" name="email" /></div>
                <div class='profileitems'><label>University</label><input type="text" name="university" disabled="disabled" /></div>
                <div class='profileitems'><label>Enrollment year</label><input type="text" name="enroll" /></div>
                
                <div class='profileitems'><label>Age Range</label>
                    <select name="age" id="age">
                    <?php
                        foreach($agerange as $k => $v){
                            echo "<option value=\"$k\">$v</option>";
                        }
                    ?>
                    </select><br />
                
                </div>
                <div class='profileitems'><label>Native Speaker</label>
                    <label id="for_native_yes" for='native_yes'>
                        <input type="radio" name="native" id='native_yes' value="y" />Yes
                    </label>
                    <label id="for_native_no" for='native_no'>
                        <input type="radio" name="native" id='native_no' value="n" />No
                    </label>
                </div>
                
                <div class='profileitems'><label>Role</label><input type="text" name="role" disabled="disabled" /></div>
                <div class='profileitems'><label>Active</label><input type="text" name="active" disabled="disabled" /></div>                <div class='profileitems'><input type='submit' name='submit1' class="fm-button" value="Save Changes" /></div>            
                <div class='profileitems'><a id="resetpass" class="fm-button">Change password</a></div>
            </form>
            </div>
            
            <div class='formwrapper' id='f2w'>
            <h1>Change password</h1>
            <form id='resetpassf' name='resetpassf' method="post" action="calls/profile_rp_process.php">            
            	<div class='profileitems' id='result2'></div>
                <div class='profileitems'><label>Current password</label><input type="password" name="currentpass" /></div>
                <div class='profileitems'><label>New password</label><input type="password" name="newpass" /></div>
                <div class='profileitems'><label>Re-type new password</label><input type="password" name="renewpass" /></div>
                <div class='profileitems'><input type='submit' name='submit2' class="fm-button" value="Save password" /></div>
            </form>        
            </div>            
        </div>
        
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
