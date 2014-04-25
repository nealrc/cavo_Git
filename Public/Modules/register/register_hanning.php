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

$profileLink = '../control/profile.php';
?>
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

$query_university = "SELECT * FROM `base_school`";
$university = mysql_query($query_university, $cavoconnection) or die(mysql_error());
$row_university = mysql_fetch_assoc($university);
$totalRows_university = mysql_num_rows($university);
do {
	$s = explode(' ', $row_university['name']);
	$ss = '';
	foreach($s as $k => $v){
		$ss.=ucfirst($v).' ';
	}	
	$uv[$row_university['id']]= $ss;
} while ($row_university = mysql_fetch_assoc($university));
mysql_free_result($university);

$query = "SELECT * FROM `base_membership` WHERE `name`!='administrator' || 'editor' || 'temporary'";
$result = mysql_query($query, $cavoconnection) or die(mysql_error());
$rows = mysql_fetch_assoc($result);
do {
	if($rows['name']!='admin'){
		$allroles[$rows['id']]= ucfirst($rows['name']);
	}
} while ($rows = mysql_fetch_assoc($result));
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
<link rel="stylesheet" type="text/css" href="../../../Assets/css/register.css" />
<script type="text/javascript" src="../../../Assets/js/jquery-1.4.2.min.js"></script>
<script type="text/javascript" src="../../../Assets/js/jquery.form.min.js"></script>
<script type="text/javascript" src="../../../Assets/js/jquery.blockui.min.js"></script>
<script type="text/javascript">
$(document).ready( function(){	
	var p = $("#school option:selected").val();
	if(p == "Other"){$('#h1').show();}else{	$('#h1').hide();}
	/*
	var q = $("#membership option:selected").val();
	if(q == "Student"){$('#h2').show();}else{$('#h2').hide();}
	*/
	$('#h2').show();
	
	$("#school").change(function () {											
		p = $("#school option:selected").text();
		if(p == "Other"){ $('#h1').show(); }else{ $('#h1').hide();}
	});
    $("#membership").change(function () {											
		q = $("#membership option:selected").text();			
		if(q == "Student"){ $('#h2').show();
		}else{ $('#h2').hide(); }
	});

	$('#rform').submit( function() {
		$(this).ajaxSubmit({
			beforeSubmit: function(){
				$("#regresult").empty();
				$('#registration').block({
                	message: "<h1>Processing your registration. please wait...<img src='images/loader-bigcircle.gif' /></h1>",
                	css: { border: '3px solid #a00', width:'70%', padding:'15px'}
				});
			},
			success: function(r){
				if(r == 1){
					
/*					$("#regresult").removeClass('error').addClass('success')
						.append('<h1>Registration success!</h1><ul><li>If you have registered as a <strong><i>"Student"</i></strong>, you can go ahead <a href="../../../signin.php">login</a> and start using CAVO. Otherwise, please contact us to activate your account.</li></ul>');
//used for when instructor needs approval
*/					
					$("#regresult").removeClass('error').addClass('success')
						.append('<h1>Registration success!</h1><ul><li>You can now <a href="../../../signin.php">login</a> and start using CAVO. (Unless if you registered as an <strong><i>"Editor"</i></strong>, then please contact us to activate your account.)</li></ul>');
					$('#registration').unblock();
					$("#registration").hide();
					$('#rform').resetForm();
				}else{
					$('#regresult').removeClass('success').addClass('error').append('<h1>Warning!</h1>'+r);
					$('#registration').unblock();
				}
			}
		});
		return false;
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
   			<div class="fancyboxw-wrapper">            
            	<div class="fancyboxw-top">
                	<h1>REGISTER WITH CAVO</h1>
                </div>                
                
                <div class="fancyboxw-content">                
                	<div id='regresult'></div>                
                	<div id="registration">                		
         				<form name='rform' id='rform' action="regprocess.php" method="post">
                        <div id='register_wrapper_left'>
                            <h1>Contact Information</h1>
                        	<div class="item-wrap">
                                <h2>Your name</h2>
                                <div class="inputfirst">
                                    <input type="text" name="firstname" /><br /><label for="firstname">First Name <span>*</span></label>
                                </div>
                                <div class="inputlast">
                                    <input type="text" name="lastname"/><br />
                                    <label for="lastname">Last Name <span>*</span></label>
                                </div>
                                <div style="clear: both"></div>
                            </div>
                            
                            <div class="item-wrap">
                            	<h2>User name (your email address)</h2>
                                <div class="inputfirst">
                                    <input type="text" name="email" /><br />
                                    <label for="email">Email Address <span>*</span></label>
                                </div>
                                <div style="clear: both"></div>
                            </div>
                            
                            <div class="item-wrap">
                            	<h2>Password</h2>
                            	<div class="inputfirst">
                                    <input type="password" name="password" /><br />
                                    <label for="password">Password <span>*</span></label>
                                </div>
                                
                                <div class="inputlast">
                                    <input type="password" name="repassword" /><br />
                                    <label for="repassword">Re-enter Password <span>*</span></label>
                                </div>
                                <div style="clear: both"></div>
                             </div>                             
						</div>

					  <div id='register_wrapper_right'>
                    		<h1>Tell Us About Yourself</h1>
                            
              				<div class="item-wrap">                              
                                <div class="inputfirst">
                                <h2>What is your age range?</h2>
                                    <select name="age" id="age">
                                    <option value=''>-Select Age Range-</option>
									<?php
                                        foreach($agerange as $k => $v){
											echo "<option value=\"$k\">$v</option>";
                                        }
                                    ?>
                                    </select><br />
                                    <label for="age">Select your age range from the list <span>*</span></label>
                              	</div>                                
                                <div class="inputlast">
                                	<h2>Are you a native speaker?</h2>
                                    <label for='native_yes'>
                                        <input type="radio" name="native" id='native_yes' value="y" />Yes
                                    </label>
                                    <label for='native_no'>
                                        <input type="radio" name="native" id='native_no' value="n" />No
                                    </label><br />
                                    <label for="native">&nbsp;Required <span>*</span></label>
                                </div>                                
                                <div style="clear: both"></div>
                          	</div>
                            
                             <div class="item-wrap">
                                <h2>Which school are you from?</h2>
                                <div class="inputfirst">
                                    <select id="school" name="school">
									<option value=''>-Select School-</option>
									<?php
                                        foreach($uv as $k => $v){
                                            echo "<option value=\"$k\">$v</option>";
                                        }										
                                    ?>
                                    <option>Other</option>
                                    </select><br/>
                                    <label for="school">Select your Univeristy from the list <span>*</span></label>
                                </div>
                                <div class="inputlast" id='h1'>
                                	<input type="text" name="from" /><br />
                                    <label for="from">Your school's name?<span class="star">*</span></label>
                                </div>                                
                                <div style="clear: both"></div>
                          </div>
                            
              				<div class="item-wrap">
                                <h2>What is your role?</h2>
                                <div class="inputfirst">
                                    <select name="membership" id="membership">
									<?php
                                        foreach($allroles as $k => $v){
											if($v=='Student'){
                                            	echo "<option value=\"$k\" selected='selected'>$v</option>";												
											}else{
												echo "<option value=\"$k\">$v</option>";
											}
                                        }
                                    ?>
                                    </select><br />
                                    <label for="membership">Select membership from the list <span>*</span></label>
                                </div>                                
                                <div class="inputlast" id='h2'>
                                	<input type="text" name="enroll" /><br />
                                    <label for="enroll">Your enrollment year<span class="star">*</span> i.e. 2008</label>
                                </div>                                
                                <div style="clear: both"></div>
                          </div>                                                  
                        </div>                        
                        <div style="clear: both"></div>
                        <div id="btn_submit_register">
                          <input type="submit" value="Register now" class="btn_red" />
                        </div>
       				  </form>                        
                    </div><!--end REGISTRATION-->
               	</div><!--end REGISTRATION Content-->                
                <div class="fancyboxw-bottom"></div>
            </div><!--end REGISTER-WRAPPER-->

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
