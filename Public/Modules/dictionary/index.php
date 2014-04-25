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
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml"><!-- InstanceBegin template="/Templates/functions.dwt" codeOutsideHTMLIsLocked="false" -->
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<!-- InstanceBeginEditable name="doctitle" -->
<title>Chinese Dictionary - CAVO</title>
<!-- InstanceEndEditable -->
<link rel="stylesheet" type="text/css" href="../../../Assets/css/style.css" />
<!-- InstanceBeginEditable name="head" -->
<meta name="description" content="Chinese vocabulary frequency analsysis" /> 
<meta name="keywords" content="Chinese segmentation, dictionary based maximum matcing algorithm, nature language processing, NLP, Chinese" /> 
<link rel="stylesheet" type="text/css" href="../../../Assets/js/jquery.ui/css/south-street/jquery-ui-1.8.custom.css"/>
<link rel="stylesheet" type="text/css" href="../../../Assets/css/vfa.css" />
<script type="text/javascript" src="../../../Assets/js/jquery-1.4.2.min.js"></script>
<script type="text/javascript" src="../../../Assets/js/jquery.form.min.js"></script>
<script type="text/javascript" src="../../../Assets/js/jquery.blockui.min.js"></script>
<script type="text/javascript" src="../../../Assets/js/jquery.masonry.min.js"></script>
<script type="text/javascript" src="../logic/dict.min.js"></script>
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
        <div id='dictwrapper'>
            <h1>Chinese Dictionary</h1><br />
            <h2>Select a dictionary:</h2>
          <ul id='dictdesp'>
              <li>
               	<h2>CAVO Test Dictionary</h2>
                  <p>CAVO test dictionary contains more than <b>20,000</b> Chinese vocabulary.</p>
</li>
                
            <li>
   	      <h2>CAVO Comprehensive Dictionary</h2>                    
                  <p>CAVO comprehensive dictionary contains more than <strong>155,000</strong> Chinese vocabulary.</p>
            </li>

          </ul>

          <h2>Set search property:</h2>
            
            <ul id='dictdesp'>
                <li>
                	<h2>Phrase length</h2>
                    <p>You can set the desired minimum and/or maximum number of characters in a phrase to help you narrow down the number of returned entries.</p>
                    <p>For example, if you want to find out the meaning of a single Chinese word '马', you can  specify both the min. and max. number of characters to 1, so that the function will only fetch the '马' from the dictionary for you.</p>               
                </li>
                
                <li>
                	<h2>Fuzzy search</h2>                    
                    <p>Entering search term '马' will return all Chinese character or phrases containing the search term in any position of the phrase. </p>
                    <p><b>Be aware of the large number of returned entries.</b> We recommend you use the min. and max. number characters in the phrase to make search more efficient.</p>
                </li>
                
                <li>
                    <h2>Wildcard search</h2>
                    <p>Wildcard (*) can be used in your search term to help you further narrow down the search results. </p>
                    <p>For example:</p>
                    <ul> 
                    <li>A search term of '万马*' will return Chinese phrases like '万马奔腾', '万马齐喑', etc. </li>
                    <li>A search term of '斗*性' will return Chinese phrase like '斗争性', etc. </li>
                    <li>A search term of '*千*万' will return Chinese phrases like '千千万万', '成千上万', etc.</li>
                    </ul>                
                </li>
            </ul>


            <div id='inputwrapper'>
                <form action="search.php" method="post" name="dict" id="dict">                
                <fieldset>
                	<h2>Dictionary:</h2>
                    <input type='radio' name='dict' id='dict_1' value='test' checked="checked" />
                    	<label for="dict_1">CAVO Test Dictionary (default)</label><br />
                    <input type='radio' name='dict' id='dict_2' value='comp' />
                    	<label for="dict_2">CAVO Comprehensive Dictionary</label><br /><br />

                    
                    <h2>Search property:</h2>
                      <label>Minimum phrase length (default = 2): </label>
                      	<input name='ming' type='text' value='2' size="5" maxlength="2" /><br />

                      <label>Maximum phrase length (default = 4): </label>
                      	<input name='maxg' type='text' value='4' size="5" maxlength="2" /><br />
                        
                  <p>                  
                  <input name="search" type="text" size="30"  style="float:left;line-height:20px;font-size:14px; margin:10px 5px; padding:5px 0;" />
                  <input class="btn_red" name="submit" type="submit" value="Search" />
                  </p>
                  
                </fieldset>
                </form>
            </div>            
			
            <div id='resultwrapper'>
                <h1>Dictionary results</h1>
                <div id='results'></div>
            </div>

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
