<?php require_once("../../../Private/config/config.php"); ?>
<?php require_once('auth.php'); ?>
<?php
mysql_select_db($database_cavoconnection, $cavoconnection);
mysql_query("SET NAMES UTF8");

if($_SESSION['MM_UserGroup']==$role['admin']){

   
	//require_once('../../../Private/config/config.php');
/*	
	$query = "SELECT a.`Userid`, a.`Firstname`, a.`Lastname`, a.`Email`, a.`Active`, Date(a.`date`) AS date,
					b.`name` AS role, c.`name` AS school
					FROM `user` AS a
					LEFT JOIN `base_membership` AS b ON (a.`Membership` = b.`id`)
					LEFT JOIN `base_school` AS c ON (a.`University` = c.`id`) WHERE `Active`=0 
					ORDER BY school ASC";
*/
        $query = "Select * from get_inactive_users";
        $result = mysql_query($query, $cavoconnection) or die(mysql_error());
	$rows = mysql_fetch_assoc($result);
	$num_act = mysql_num_rows($result);	
	if($num_act >0){
		do{
			$new_user[$rows['Userid']]['name'] = ucfirst($rows['Firstname']).' '.ucfirst($rows['Lastname']);
			$new_user[$rows['Userid']]['role'] = ucfirst($rows['role']);
			$s = explode(' ',$rows['school']);
			$ss='';
			foreach($s as $kk => $vv){
				$ss .=ucfirst($vv).' ';
			}
			$new_user[$rows['Userid']]['school'] =$ss;
			$new_user[$rows['Userid']]['date'] = $rows['date'];
		}while($rows = mysql_fetch_assoc($result));
	}
	mysql_free_result($result);

        /*
	$query = "SELECT a.`id`, a.`word`, a.`py`, a.`en`, a.`cn`, a.`level`, a.`time`,
					b.`Firstname`, b.`Lastname`, 
					c.`name` AS school,
					d.`name` AS role 
					FROM `newword` AS a
					LEFT JOIN `user` AS b ON (a.`user` = b.`Userid`)
					LEFT JOIN `base_school` AS c ON (b.`University` = c.`id`)
					LEFT JOIN `base_membership` AS d ON (b.`Membership` = d.`id`)
					WHERE a.`validated`=0
					ORDER BY  b.`Firstname` ASC";         
         */
        $query = "Select * from get_inactive_words";
	$result = mysql_query($query, $cavoconnection) or die(mysql_error());
	$rows = mysql_fetch_assoc($result);
	$num_act = mysql_num_rows($result);	
	if($num_act >0){
		do{
			$new_words[$rows['id']]['word'] = $rows['word'];
			$new_words[$rows['id']]['py'] = $rows['py'];
			$new_words[$rows['id']]['en'] = $rows['en'];
			$new_words[$rows['id']]['cn'] = $rows['cn'];
			$new_words[$rows['id']]['level'] = is_null($rows['level'])?'n/a':$rows['level'];
			$new_words[$rows['id']]['date'] = $rows['time'];
			$new_words[$rows['id']]['role'] = $rows['role'];
			$new_words[$rows['id']]['school'] = $rows['school'];
			$new_words[$rows['id']]['user'] = ucfirst($rows['Firstname']).' '.ucfirst($rows['Lastname']);
		}while($rows = mysql_fetch_assoc($result));
	}
	mysql_free_result($result);
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
<!--<link rel="stylesheet" type="text/css" href="../../../Assets/js/jquery.ui/css/south-street/jquery-ui-1.8.custom.css"/>-->
<script type="text/javascript" src="../../../Assets/js/jquery-1.4.2.min.js"></script>
<script type="text/javascript" src="../../../Assets/js/jquery.form.min.js"></script>
<script type="text/javascript" src="../../../Assets/js/jquery.blockui.min.js"></script>
<script type="text/javascript" src="../logic/cdock.main.pack.js"></script>
<script type="text/javascript" src="../logic/cdock.validate.pack.js"></script>
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
        <h1>Your Control Panel</h1>
        
		<?php  if($_SESSION['MM_UserGroup']==$role['admin'] || $_SESSION['MM_UserGroup']==$role['editor']){ ?>			
		<p><a href="../tiku/index.php">Tiku Editting</a></p>
        <?php } ?>
        
        <?php  if($_SESSION['MM_UserGroup']==$role['admin']){ ?>			
		<?php if(isset($new_user) && count($new_user)>0){ ?>
        <h2>New user waiting to be activated</h2>
        <div id='tbl_actuser_wrapper'>
            <table id='tbl_actuser' border="0" cellpadding="0" cellspacing="0" class="ui-widget">
            <thead class="ui-widget-header">
                <tr>
                    <th class="firstcell">Activate</th>
                    <th>Name</th>
                    <th>Role</th>
                    <th>School</th>
                    <th class="lastcell">Date</th>
                </tr>
            </thead>
            <tbody class="ui-widget-content">
        	<?php foreach($new_user as $id => $arr){ ?>
                <tr>
                    <td class="firstcell"><input type="checkbox" id='new_user<?php echo $id;?>' name="user[]" value="<?php echo $id;?>" /></td>
                    <td ><label for="new_user<?php echo $id;?>"><?php echo $arr['name']; ?></label></td>
                    <td ><label for="new_user<?php echo $id;?>"><?php echo $arr['role']; ?></label></td>
                    <td><label for="new_user<?php echo $id;?>"><?php echo $arr['school']; ?></label></td>
                    <td class="lastcell"><label for="new_user<?php echo $id;?>"><?php echo $arr['date']; ?></label></td>
                </tr>
        	<?php } ?>      
            </tbody>
            <tfoot class="ui-widget-footer">
                <tr>
                	<td class="firstcell">&nbsp;</td>
                    <td><input type="button" id="btn_actuser" value="Activate" /></td>
                    <td>&nbsp;</td>
                    <td class="lastcell" colspan="2"><input type="button" id="btn_deluser" value="Delete" /></td>                    
                </tr>
            </tfoot>
            </table>
        </div>
        <?php } else { ?>
        <h2>No new user found.</h2>
        <?php } ?>
        
        <?php  if(isset($new_words) && count($new_words)>0){ ?>
        <h2>New words waiting to be activated</h2>
        <div id='tbl_actword_wrapper'>
            <table id='tbl_actword' border="0" cellpadding="0" cellspacing="0" class="ui-widget">
            <thead class="ui-widget-header">
                <tr>
                    <th class="firstcell">Select</th>
                    <th>Word</th>
                    <th>PY</th>
                    <th>EN</th>
                    <th>CN</th>
                    <th>Level</th>
                    <th>User</th>
                    <th>Role</th>
                    <th>School</th>
                    <th class="lastcell">Date</th>
                </tr>
            </thead>
            <tbody class="ui-widget-content">
        	<?php foreach($new_words as $id => $arr){ ?>
                <tr>
                    <td class="firstcell"><input type="checkbox" id='new_word<?php echo $id;?>' name="word[]" value="<?php echo $id;?>" /></td>
                    <td ><label for="new_word<?php echo $id;?>"><?php echo $arr['word']; ?></label></td>
                    <td ><label for="new_word<?php echo $id;?>"><?php echo $arr['py']; ?></label></td>
                    <td ><label for="new_word<?php echo $id;?>"><?php echo $arr['en']; ?></label></td>
                    <td ><label for="new_word<?php echo $id;?>"><?php echo $arr['cn']; ?></label></td>
                    <td ><label for="new_word<?php echo $id;?>"><?php echo $arr['level']; ?></label></td>
                    <td ><label for="new_word<?php echo $id;?>"><?php echo $arr['user']; ?></label></td>
                    <td ><label for="new_word<?php echo $id;?>"><?php echo $arr['role']; ?></label></td>
                    <td ><label for="new_word<?php echo $id;?>"><?php echo $arr['school']; ?></label></td>
                    <td class="lastcell"><label for="new_user<?php echo $id;?>"><?php echo $arr['date']; ?></label></td>
                </tr>
        	<?php } ?>      
            </tbody>
            <tfoot class="ui-widget-footer">
                <tr>
                	<td class="firstcell">&nbsp;</td>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                    <td><input type="button" id="btn_actword" value="Activate" /></td>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                    <td class="lastcell"><input type="button" id="btn_delword" value="Delete" /></td>                    
                </tr>            
            </tfoot>
            </table>
        </div>
        <?php } else { ?>
        <h2>No new word found.</h2>
        <?php } ?>
        <?php } else { ?>
        <blockquote>
          <h2>Welcome to CAVO! </h2>
		  <h2>To take a test, go to "Vocabulary Test" in the above main menu.<br>
		  To view reports, etc, use menu on the left.</h2>
        </blockquote>
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
