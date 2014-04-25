<?php require_once("../../../../Private/config/config.php"); ?>
<?php require_once('authm.php'); ?>
<?php require_once("../../../../Private/class/segmentation.php");?>
<?php require_once("../../../../Private/class/idcrypt.php");?>
<?php require_once("../../../../Private/class/function.php");?>
<?php
$error = '';

$testname = isset($_POST['testname'])&&!empty($_POST['testname'])?$_POST['testname']:NULL;
$testtype = isset($_POST['testtype'])&&!empty($_POST['testtype'])?$_POST['testtype']:NULL;
$testcat = isset($_POST['testcat'])&&!empty($_POST['testcat'])?$_POST['testcat']:NULL;
$article = isset($_POST['article'])&&!empty($_POST['article'])?$_POST['article']:NULL;
if(is_null($testname)){$error.="You must give your test a name!<br />";}
if(is_null($testtype)){$error.="You must specify your test type!<br />";}
if(is_null($testcat)){$error.="You must specify your test category!<br />";}
if(is_null($article)){$error.="You must paste your test article!<br />";}

$now = date("Y-m-d");

$testdescription = isset($_POST['testdescription'])&&!empty($_POST['testdescription'])?$_POST['testdescription']:NULL;

$time_limit = isset($_POST['time_limit'])?$_POST['time_limit']:NULL;
if(!is_null($time_limit)){
	if($time_limit==1){
		$start_time = isset($_POST['start_time'])?$_POST['start_time']:$now;
		$end_time = isset($_POST['end_time'])?$_POST['end_time']:NULL;
		if(is_null($end_time) || empty($end_time)){$error.="Test end time is empty!<br />";}
	}
}else{
	$start_time=$now;
	$end_time=NULL;
}
$start_time = (is_null($start_time)||empty($start_time))?$now:$start_time;

$mingram = !empty($_POST['mingram'])?$_POST['mingram']:2;
$maxgram = !empty($_POST['maxgram'])?$_POST['maxgram']:4;

if(count($_POST['keywords'])==0){
	$keywords = NULL;
	$limit = 3000;
	if($error == ''){
		// $article, $maxgram, $mingram, $limit=NULL, $dict=NULL, $idb
		$seg = new Segmentation($article,$maxgram,$mingram,$icavoconnection,$limit,NULL);
		$keywords = $seg->getkeywords();
		
		if(count($keywords)>0 ){
			$cm = 0;		
			$cs = 0;
			$mul_data=array();
			$single_data=array();
			foreach($keywords as $d => $arr){
				if(strpos($arr['ids'], '_')!==false){
					$tmp = explode('_', $arr['ids']);				
					$mids = array();
					foreach($tmp as $k => $v){
						$t = new IdCrypt($v, 'decrypt');
						$mids[] = $t->getresult();	
					}				
					$ab = ($testtype==1)?'py':(($testtype==2)?'en':'cn');
					$query = "SELECT `id`, `word`, `$ab` from `tiku_cavo_test` WHERE `id` IN (".implode(',',$mids).")";				
					$result = mysql_query($query,$cavoconnection) or die(mysql_error());
					$rows = mysql_fetch_assoc($result);
					$nrows = mysql_num_rows($result);
					if($nrows >0){
						$aba = array();
						do{
							$aba['voc'] = $rows['word'];
							$tt = new IdCrypt($rows['id'], 'encrypt');
							$aba['ids'] = $tt->getresult();
							$aba[$ab] = $rows[$ab];
							
							if($testtype==1){
								$single_data[$cs]=$aba;
								$cs++;
								break;							
							}else{
								$mul_data[$cm] = $aba;
								$cm++;
							}
						}while($rows = mysql_fetch_assoc($result));
					}
					mysql_free_result($result);				
				}else{
					$single_data[$cs]=$arr;
					$cs++;
				}			
				$tot= count($single_data) + count($mul_data);
			}
		}else{
			$error .= "No keywords found";	
		}		
	}	
	$error.='No keyword selected!<br />';
}

mysql_select_db($database_cavoconnection, $cavoconnection);
mysql_query("SET NAMES UTF8");

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

if($error == ''){
	$ids = array();
	foreach($_POST['keywords'] as $k => $ev){
		$unlock = new IdCrypt($ev, 'decrypt');
		$idd = $unlock->getresult();
		$query = "SELECT `cavo` from `cavo_level_init` WHERE `tiku_id` = $idd";
		$result = mysql_query($query) or die(mysql_error());
		$row = mysql_fetch_assoc($result);
		$ids[$idd] =$row['cavo'];
		mysql_free_result($result);		
	}
	
	//print_r($ids);
	
	$query = "SELECT `University` FROM `user` WHERE `Userid` = ".$_SESSION['MM_Userid'];
	$result = mysql_query($query) or die(mysql_error());
	$row = mysql_fetch_assoc($result);
	$school =$row['University'];	
	mysql_free_result($result);
	
	//create test
	// base_test,	test_type 	test_category 	name 	description
	$query= sprintf("INSERT INTO `base_test` (`test_type`,`test_category`,`name`,`description`) VALUES(%s,%s,%s,%s)",
					GetSQLValueString($testtype, 'int'),
					GetSQLValueString($testcat, 'int'),
					GetSQLValueString($testname, 'text'),
					GetSQLValueString($testdescription, 'text'));					
	mysql_query($query) or die(mysql_error());
	
	$test_id = mysql_insert_id();
	
	// school_test, test, user, article, date_start, date_end, date_create
	$query = sprintf("INSERT INTO `school_test` (`test`,`school`,`user`,`article`,`date_start`,`date_end`,`date_create`, `active`) VALUES (%s,%s,%s,%s,%s,%s,NOW(),1)",
					GetSQLValueString($test_id, 'int'),
					GetSQLValueString($school, 'int'),
					GetSQLValueString($_SESSION['MM_Userid'], 'int'),
					GetSQLValueString($article, 'text'),
					GetSQLValueString($start_time, 'date'),
					GetSQLValueString($end_time, 'date'));	
        //print_r($query);
	mysql_query($query) or die(mysql_error());
		
	// cavo_level,  tiku_id, test, level
	foreach($ids as $k => $v){
		$query = "INSERT INTO `cavo_level` (`tiku_id`, `test`, `level`) VALUES ($k, $test_id, $v)";
		mysql_query($query) or die(mysql_error());
	}
}
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
<script type="text/javascript">
$(document).ready(function () {
    $("#sidebar-left").find("li").each(function () {
        $(this).click(function () {
            var a;
            switch ($(this).attr("id")) {
            case "report":
                a = "../reports.php";
                break;
            case "profile":
                a = "../profile.php";
                break;
            case "user":
                a = "../user.php";
                break;
            case "settings":
                a = "../settings.php";
                break;
            case "stats":
                a = "../stats.php"
            }
            window.location = a
        });
    });
	
	var dates = $("#start_time, #end_time").datepicker({
		defaultDate: "+1w",
		changeMonth: true,
		numberOfMonths: 2,
		dateFormat: 'yy-mm-dd',
		onSelect: function( selectedDate ) {
			var option = this.id == "start_time" ? "minDate" : "maxDate",
				instance = $( this ).data( "datepicker" ),
				date = $.datepicker.parseDate(
					instance.settings.dateFormat ||
					$.datepicker._defaults.dateFormat,
					selectedDate, instance.settings );
			dates.not( this ).datepicker( "option", option, date);
		}
	});
	
	$('#addall').click( function(){
		var checked_status = this.checked;
		$('.kylist').each( function(){
			this.checked = checked_status;
		});
	});
	
	countChecked();    
	$("form#selectkeywords :checkbox").click(countChecked);
});
function countChecked() {
  var n = $("form#selectkeywords input:checked").length;
  $(".cbcounter").text(n + (n <= 1 ? " keyword is" : " keywords are") + " selected");
}
</script>
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
        <h1>Status</h1>
		
        <div id='result'>
			<?php if($error ==''){ ?>
			<h2>Your test is generated successfully. Please go to <a href="../../test/index.php">Vocabulary Test</a> to see the test.</h2>		
			<?php }else{ ?>
            <div class="error"><h2>Error!</h2><?php echo $error;?></div>
            
        	<?php if(is_null($keywords)){ ?>
            	<!--<div class="error"><h2>Error!</h2>No keywords found in your article. please try again!</div>-->
            <?php }else{ ?>
			
            	<h2><?php echo $tot; ?> keywords found.</h2>
                <p>Please select keywords for your test</p>
                <div class='cbcounter thighlight'></div>
                
                <form name='selectkeywords' id='selectkeywords' action='gentest.php' method="post">
                <div style="width:100%;margin:10px 0;float:left;">
                <?php
					if(count($single_data)>0){ 
						foreach($single_data as $k => $arr){
				?>
                	<div class='kywdwrapper'>
                    <label><input type='checkbox' class='kylist' name='keywords[]' value="<?php echo $arr['ids'];?>" />
					<?php echo $arr['voc']; ?></label>
                    </div>
                <?php }} ?>
                
                <?php if(count($mul_data)>0){ ?>
                <hr class='hrline' />
                <?php foreach($mul_data as $k => $arr){?>
                	<div class='kywdlwrapper'>
                    <label><input type='checkbox' class='kylist' name='keywords[]' value="<?php echo $arr['ids'];?>" />
					<?php printf("%s: %s", $arr['voc'], $arr[$ab]); ?></label>
                    </div>
                <?php }} ?>                 
                </div>
                
                <div class='alignleft'>
                	<label class="thighlight">
                	<input type='checkbox' name='addall' id='addall' value='addall' /><b>Select all words</b></label>
                </div>                
                <div class="alignright">&nbsp;</div>
                
				<input type='hidden' name='testtype' value='<?php echo $testtype; ?>' />
				<input type='hidden' name='testname' value='<?php echo $testname; ?>' />
				<input type='hidden' name='testcat' value='<?php echo $testcat; ?>' />
				<input type='hidden' name='article' value='<?php echo $article; ?>' />
				<input type='hidden' name='testdescription' value='<?php echo $testdescription; ?>' />
				<input type='hidden' name='time_limit' value='<?php echo $time_limit; ?>' />
				<input type='hidden' name='start_time' value='<?php echo $start_time; ?>' />
				<input type='hidden' name='end_time' value='<?php echo $end_time; ?>' />
				
				
                <div class='alignleft'>&nbsp;</div>
                <div class="alignright">
                    <input type="submit" class="ui-button" value="Create test" /></div>                
                </form>

                <?php if( $seg->getsrclen() > $seg->getlimit() ){ ?>
                	<div style="width:100%;margin:10px 0;float:left;">
                    	Your article length exceeds the limit of <?php echo $seg->getlimit(); ?>
                    </div>
                <?php } ?>                                                
            <?php }} ?>            
        </div>
        
		<?php if($error !=''){ ?>
		
		<div style="width:100%; float:left; margin:20px 0; border-bottom:2px solid #ccc;">&nbsp;</div>
		
        <div id='inputform'>
        <form action="ctestimport.php" method="post" id='ctest'>
        	<h2>Your custom test information</h2>
            <div class='alignleft'>Name of your test<span class="thighlight">*</span></div>            
			<div class='alignright'>
            	<input name='testname' type='text' id='testname' value="<?php echo !is_null($testname)?$testname:''; ?>" />
            </div>

            <div class='alignleft'>Test description</div>
            <div class='alignright'>
            	<input name='testdescription' type='text' id='testdescription' value="<?php echo !is_null($testdescription)?$testdescription:''; ?>" />
            </div>

            <div class='alignleft'>What kind of test?<span class="thighlight">*</span></div>
            <div class='alignright'>
                <select name='testtype'>
                <?php foreach($test as $id => $name){ ?>
                  <option value=<?php echo $id;?><?php echo !is_null($testtype)&&$testtype==$id?" selected":''; ?>>
				  	<?php echo $name;?></option>
                <?php } ?>                
                </select></div>

            <div class='alignleft'>What is this test for?<span class="thighlight">*</span></div>
            <div class='alignright'>
                <select name='testcat'>
                <option value=''>-Select-</option>
                <?php foreach($test_cat as $id => $name){ ?>
                  <option value=<?php echo $id;?><?php echo !is_null($testcat)&&$testcat==$id?" selected":''; ?>>
				  	<?php echo $name;?></option>
                <?php } ?>
                </select></div>

            <h2>Is this a time limited test?</h2>
            <div class='alignleft'>
            	<label for='tl_y'><input type="radio" name="time_limit" id='tl_y' value=1 <?php echo !is_null($time_limit)&&$time_limit==1?" checked":''; ?> />Yes</label>
            	<label for='tl_n'><input type="radio" name="time_limit" id='tl_n' value=0 <?php echo !is_null($time_limit)&&$time_limit==0?" checked":''; ?>/>No</label>
            </div>

            <h2>Test time frame</h2>
            <div class='formtextarea'>
                <label>Start:</label><input type='text' name='start_time' id='start_time' value="<?php echo !is_null($start_time)?$start_time:''; ?>" />
                <label>End:</label><input type='text' name='end_time' id='end_time' value="<?php echo !is_null($end_time)?$end_time:''; ?>" />
            </div>

            <h2>Vocabulary Criteria</h2>
            <div class='alignleft'>Minimum number of characters in a vocabulary</div>
            <div class='alignright'>
            	<input name='mingram' type='text' size="5" maxlength="3" value="<?php echo !is_null($mingram)?$mingram:''; ?>" /></div>

            <div class='alignleft'>Maximum number of characters in a vocabulary</div>
            <div class='alignright'>
            	<input name='maxgram' type='text' size="5" maxlength="3" value="<?php echo !is_null($maxgram)?$maxgram:''; ?>" /></div>

            <div class='alignleft notes'>Default minimum = 2, maximum = 4</div>
            <div class="alignright">&nbsp;</div>
            
            <h2>Article</h2>
            <div class='alignleft notes'>paste your article below<span class="thighlight">*</span></div>
            <div class="alignright">&nbsp;</div>
            
            <div class='formtextarea'>
            	<textarea name="article" rows="14" cols="60"><?php echo !is_null($article)?$article:''; ?></textarea>
            </div>
            
            <div class='alignleft notes'>
            	Article length is limited to 3000 words.</div>
            <div class="alignright">&nbsp;</div>
            
            <div class='alignleft'>&nbsp;</div>
            <div class="alignright">
            	<input type="submit" class="ui-button" value="Search Vocabulary Again" /></div>
        </form>
        </div>
		<?php } ?>

		
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
