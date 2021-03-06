<?php require_once("../../../../Private/config/config.php"); ?>
<?php require_once('authm.php'); ?>
<?php
mysql_select_db($database_cavoconnection, $cavoconnection);
$query = "SELECT `id`, `name` FROM `base_test_category` WHERE `id`!=1";
$result = mysql_query($query, $cavoconnection) or die(mysql_error());
$rows = mysql_fetch_assoc($result);
$total_nums = mysql_num_rows($result);	
if($total_nums >0){
	do{
		$test_cat[$rows['id']]=$rows['name'];
	}while($rows = mysql_fetch_assoc($result));
}
mysql_free_result($result);

$query = "SELECT `id`, `name` FROM `base_test_type`";
$result = mysql_query($query, $cavoconnection) or die(mysql_error());
$rows = mysql_fetch_assoc($result);
$total_nums = mysql_num_rows($result);	
if($total_nums >0){
	do{
		$test[$rows['id']]=$rows['name'];
	}while($rows = mysql_fetch_assoc($result));
}
mysql_free_result($result);
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml"><!-- InstanceBegin template="/Templates/control.dwt" codeOutsideHTMLIsLocked="false" -->
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<!-- InstanceBeginEditable name="doctitle" -->
<title>CAVO</title>
<!-- InstanceEndEditable -->
<link rel="stylesheet" type="text/css" href="../../../../Assets/css/style.css" />
<link rel="stylesheet" type="text/css" href="../../../../Assets/css/control_main.css" />
<!-- InstanceBeginEditable name="head" -->
<link rel="stylesheet" type="text/css" href="ctest.css" />
<link rel="stylesheet" type="text/css" href="../../../../Assets/js/jquery.ui/css/flick/jquery-ui-1.8.5.custom.css"/>
<script type="text/javascript" src="../../../../Assets/js/jquery-1.4.4.min.js"></script>
<script type="text/javascript" src="../../../../Assets/js/jquery.ui/js/jquery-ui-1.8.5.custom.min.js"></script>
<script type="text/javascript" src="../../../../Assets/js/jquery.form.min.js"></script>
<script type="text/javascript" src="../../../../Assets/js/jquery.blockui.min.js"></script>
<script type="text/javascript" src="ctdock.pack.js"></script>
<!-- InstanceEndEditable -->
</head>
<body>
<div id="container">
    <div id='content-wrapper'>
        <div id="header-wrapper">
            <div id="header">
                <div id="section1">
              <div id="logo"> <img src="../../../../Assets/images/logo.gif" /> </div>
              <div id="menu">
                <ul>
                    <!--<li><a href="../Public/Modules/feedback/fb.php">Feedback</a></li>-->
                    <li><a href="../index.php">Control Panel</a></li>
                    <li><a href="../../test/index.php">Vocabulary Test</a></li>
                    <li><a href="../../demo/index.php">Demo</a></li>
                    <li><a href="../../../../index.php">Home</a></li>              
                </ul>
              </div>
            </div>
            <!--end SECTION1-->
            
            <?php if(isset($_SESSION['MM_Username'])){ ?>
            <div id="loginstatus">
              Welcome <?php echo $_SESSION['MM_Username']; ?> : <a href="../profile.php">My Account</a> | <a href="<?php echo $logoutAction; ?>">Sign Out</a>        
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
                    <img src="../../../../Assets/images/reports.png" />
                    <p>View your test data including detailed test records, charts and comprehensive report. If you are an instructor, view your institute report here.</p>
                </li>
                
                <li id='profile' title='Click to view or modify your personal information'>
                    <h2>My Profile</h2>
                    <img src="../../../../Assets/images/profile.png" />
                    <p>Update your account information and password here.</p>                        
                </li>
                
                <?php  if($_SESSION['MM_UserGroup']==$role['admin'] || $_SESSION['MM_UserGroup']==$role['instructor']){ ?>
                <li id='user' title='Click to manage user information'>
                    <h2>User Test Records and Profiles</h2>
                    <img src="../../../../Assets/images/students.png" />
                    <p>Manage your students' test records and profiles.</p>
                </li>                   
                <?php } ?>
               
                <?php  if($_SESSION['MM_UserGroup']== $role['admin'] || $_SESSION['MM_UserGroup']== $role['instructor'] ){ ?>
                <li id='settings'>
                    <h2>Custom Test &amp; Management</h2>
                    <img src="../../../../Assets/images/tests.png" />
                    <p>Manage custom tests and test settings.</p>
                </li> 
                <?php } ?>
                
                <?php  if($_SESSION['MM_UserGroup']== $role['admin']){ ?>
                <li id='stats'>
                    <h2>System Statistics</h2>
                    <img src="../../../../Assets/images/data.png" />
                    <p>Information about  system statistics and usage.</p>
                </li>              
                <?php } ?>      
                
                </ul>
            </div><!--end SIDEBAR-left-->
            
            <div id="content-right">
                <!-- InstanceBeginEditable name="content" -->
        <h1>Create Custom Test</h1>
        <div id='result'></div>
        <div id='inputform'>
        <form action="ctestimport.php" method="post" id='ctest'>
        	<h2>Your custom test information</h2>
            <div class='alignleft'>Name of your test<span class="thighlight">*</span></div>            
			<div class='alignright'>
            	<input name='testname' type='text' id='testname' />
            </div>

            <div class='alignleft'>Test description</div>
            <div class='alignright'>
            	<input name='testdescription' type='text' id='testdescription' />
            </div>

            <div class='alignleft'>What kind of test?<span class="thighlight">*</span></div>
            <div class='alignright'>
                <select name='testtype'>
                	<option value=''>-Select-</option>
					<?php foreach($test as $id => $name){ ?>
                      <option value=<?php echo $id;?>><?php echo $name;?></option>
                    <?php } ?>
                </select></div>
				
            <div class='alignleft'>What is this test for?<span class="thighlight">*</span></div>
            <div class='alignright'>
                <select name='testcat'>
                <option value=''>-Select-</option>
                <?php foreach($test_cat as $id => $name){ ?>
                  <option value=<?php echo $id;?>><?php echo $name;?></option>
                <?php } ?>
                </select></div>

            <h2>Is this a time limited test?</h2>
            <div class='alignleft'>
            	<label for='tl_y'><input type="radio" name="time_limit" id='tl_y'  value=1 />Yes</label>
            	<label for='tl_n'><input type="radio" name="time_limit" id='tl_n' value=0 />No</label>
            </div>

            <h2>Test time frame</h2>
            <div class='formtextarea'>
                <label>Start:</label><input type='text' name='start_time' id='start_time' />
                <label>End:</label><input type='text' name='end_time' id='end_time' />
            </div>

            <h2>Vocabulary Criteria</h2>
            <div class='alignleft'>Minimum number of characters in a vocabulary</div>
            <div class='alignright'>
            	<input name='mingram' type='text' size="5" maxlength="3" /></div>

            <div class='alignleft'>Maximum number of characters in a vocabulary</div>
            <div class='alignright'>
            	<input name='maxgram' type='text' size="5" maxlength="3" /></div>

            <div class='alignleft notes'>Default minimum = 2, maximum = 4</div>
            <div class="alignright">&nbsp;</div>
            
            <h2>Article</h2>
            <div class='alignleft notes'>paste your article below<span class="thighlight">*</span></div>
            <div class="alignright">&nbsp;</div>
            
            <div class='formtextarea'>
            	<textarea name="article" rows="14" cols="60"></textarea></div>            
            
            <div class='alignleft notes'>
            	Article length is limited to 3000 words.</div>
            <div class="alignright">&nbsp;</div>
            
            <div class='alignleft'>&nbsp;</div>
            <div class="alignright">
            	<input type="submit" class="ui-button" value="Search Vocabulary" /></div>
        </form>
        </div>
        <div class='push'>&nbsp;</div>
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
            <img src="../../../../Assets/images/NEALRClogo.jpg" width="50" height="50" />            </p>
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
			<a href="http://alpps.org/"><img src='../../../../Assets/images/logo-alpps.gif' /></a>&nbsp;
            <a href="http://chineseflagship.osu.edu/"><img src='../../../../Assets/images/logo-cfp.gif' /></a>&nbsp;
            <a href="http://flpubs.osu.edu/"><img src='../../../../Assets/images/FLPlogo.gif' /></a>&nbsp;
            <a href="http://www.osu.edu/"><img src='../../../../Assets/images/logo-osu.gif' /></a>&nbsp;
            
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
