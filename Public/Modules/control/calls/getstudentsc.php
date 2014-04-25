<?php 
require_once('../../../../Private/config/config.php');
require_once('../../../../Private/class/function.php');
?>
<?php require_once('authm.php'); ?>
<?php
function Strip($value){
	if(get_magic_quotes_gpc() != 0)  	{
    	if(is_array($value))  
			if ( array_is_associative($value) )
			{
				foreach( $value as $k=>$v)
					$tmp_val[$k] = stripslashes($v);
				$value = $tmp_val; 
			}				
			else  
				for($j = 0; $j < sizeof($value); $j++)
        			$value[$j] = stripslashes($value[$j]);
		else
			$value = stripslashes($value);
	}
	return $value;
}
function array_is_associative ($array){
    if ( is_array($array) && ! empty($array) )
    {
        for ( $iterator = count($array) - 1; $iterator; $iterator-- )
        {
            if ( ! array_key_exists($iterator, $array) ) { return true; }
        }
        return ! array_key_exists(0, $array);
    }
    return false;
}
function constructWhere($s){
    $qwery = "";
	//['eq','ne','lt','le','gt','ge','bw','bn','in','ni','ew','en','cn','nc']
    $qopers = array(
				  'eq'=>" = ",
				  'ne'=>" <> ",
				  'lt'=>" < ",
				  'le'=>" <= ",
				  'gt'=>" > ",
				  'ge'=>" >= ",
				  'bw'=>" LIKE ",
				  'bn'=>" NOT LIKE ",
				  'in'=>" IN ",
				  'ni'=>" NOT IN ",
				  'ew'=>" LIKE ",
				  'en'=>" NOT LIKE ",
				  'cn'=>" LIKE " ,
				  'nc'=>" NOT LIKE " );
    if ($s) {
        $jsona = json_decode($s,true);
        if(is_array($jsona)){
			$gopr = $jsona['groupOp'];
			$rules = $jsona['rules'];
            $i =0;
            foreach($rules as $key=>$val) {
                $field = $val['field'];
                $op = $val['op'];
                $v = $val['data'];
				
				switch($field){
					case 'Firstname':		$afield = 'c.`'.$field.'`'; break;
					case 'Lastname':		$afield = 'c.`'.$field.'`'; break;
					case 'EnrollmentYear':	$afield = 'c.`'.$field.'`'; break;					
					case 'University':		$afield = 'c.`'.$field.'`'; break;
					default: 				$afield = 'a.`'.$field.'`'; break;
				}
				
				if(!is_null($v) && $op) {
	                $i++;
					$v = ToSql($field,$op,$v);
					if($i>1){
						$qwery .= " " .$gopr." ";
					}
					switch ($op) {
						// in need other thing
					    case 'in' :
					    case 'ni' :
					        $qwery .= $afield.$qopers[$op]." (".$v.")";
					        break;
						default:
					        $qwery .= $afield.$qopers[$op].$v;
							break;
					}
				}
            }
        }
    }
    return $qwery;
}
function ToSql ($field, $oper, $val) {
	switch ($field) {
		case 'id': 			return intval($val); break;		
		case 'EnrollmentYear':	return intval($val); break;
		case 'test':			return intval($val); break;
		case 'duration':		return floatval($val); break;
		case 'score':			return floatval($val); break;
		default:
			//mysql_real_escape_string is better
			if($oper=='bw' || $oper=='bn') return "'" . addslashes($val) . "%'";
			else if ($oper=='ew' || $oper=='en') return "'%" . addcslashes($val) . "'";
			else if ($oper=='cn' || $oper=='nc') return "'%" . addslashes($val) . "%'";
			else return "'" . addslashes($val) . "'";		
	}
}
?>
<?php
/*
'Index'
'Firstname'
'Lastname'
'EnrollmentYear'
'Testdate'
'Testid'
'Duration'
'Score'
'University'
*/

$page = $_REQUEST['page'];
$limit = $_REQUEST['rows'];
$sord = $_REQUEST['sord'];

if(isset($_REQUEST['sidx'])){
	switch($_REQUEST['sidx']){
		case 'Firstname':		$sidx = 'c.`'.$_REQUEST['sidx'].'`'; break;
		case 'Lastname':		$sidx = 'c.`'.$_REQUEST['sidx'].'`'; break;
		case 'EnrollmentYear':	$sidx = 'c.`'.$_REQUEST['sidx'].'`'; break;
		case 'University':		$sidx = 'c.`'.$_REQUEST['sidx'].'`'; break;		
		default: 				$sidx = 'a.`'.$_REQUEST['sidx'].'`'; break;		
	}
}else{
	$sidx = 1;
}

$wh = "";
$searchOn = Strip($_REQUEST['_search']);
if($searchOn=='true') {
	$searchstr = Strip($_REQUEST['filters']);
	$wh= constructWhere($searchstr);
}

mysql_select_db($database_cavoconnection, $cavoconnection);
mysql_query("SET NAMES UTF8");

$user_id = $_SESSION['MM_Userid'];
$user_member = array_search($_SESSION['MM_UserGroup'], $role);

$whh='';
if($user_member != 'admin'){
	$whh = "INNER JOIN `user` AS d ON (d.`University` = c.`University`) WHERE d.`Userid` = $user_id AND g.`name` = 'student'";
}
// record count
$query_counts = "SELECT a.`id`, a.`test`, a.`date`, DATE(a.`date`) AS 'date', a.`score`, a.`duration`, 
					b.`name` AS testname,
					c.`Firstname`, c.`Lastname`, c.`EnrollmentYear`, c.`University`,
					f.`name` AS school
				FROM `test_records` AS a 
				INNER JOIN `user` AS c ON (c.`Userid` = a.`user`)
				LEFT JOIN `base_test` AS b ON (a.`test` = b.`id`) 				
				LEFT JOIN `base_school` AS f ON (f.`id` = c.`University`)
				LEFT JOIN `base_membership` AS g ON (g.`id` = c.`Membership`)";
if(strlen($whh)>0){$query_counts .= "  ".$whh; }
if(strlen($wh)>0) {
	if(strlen($whh)>0){
		$query_counts .= " AND ".$wh;
	}else{
		$query_counts .=" WHERE ".$wh;
	}
}
//echo $query_counts;

$result_counts = mysql_query($query_counts, $cavoconnection) or die(mysql_error());
$count = mysql_num_rows($result_counts);
mysql_free_result($result_counts);

//print $query_counts;

if($count <= 0){
	print 100;
}else{
	// set parameter
	$total_pages = ceil($count/$limit);
	$page = ($page > $total_pages) ? $total_pages : $page;
	$start = ($limit*$page - $limit > 0)?($limit*$page - $limit):0;
	
	// record count
	$query_testrecord = "SELECT a.`id`, a.`test`, a.`date`, DATE(a.`date`) AS 'date', a.`score`, a.`duration`, 
						b.`name` AS testname, 
						c.`Firstname`, c.`Lastname`, c.`EnrollmentYear`, c.`University`,
						f.`name` AS school FROM `test_records` AS a 
					LEFT JOIN `base_test` AS b ON (a.`test` = b.`id`) 
					INNER JOIN `user` AS c ON (c.`Userid` = a.`user`)
					LEFT JOIN `base_school` AS f ON (f.`id` = c.`University`)
					LEFT JOIN `base_membership` AS g ON (g.`id` = c.`Membership`)";
	if(strlen($whh)>0){$query_testrecord .= "  ".$whh; }
	if(strlen($wh)>0) {
		if(strlen($whh)>0){
			$query_testrecord .= " AND ".$wh;
		}else{
			$query_testrecord .=" WHERE ".$wh;
		}
	}
	
	$query_testrecord .= " ORDER BY $sidx $sord LIMIT $start, $limit";
//	print $query_testrecord;
	
	$testrecord = mysql_query($query_testrecord, $cavoconnection) or die(mysql_error());
	$row_testrecord = mysql_fetch_assoc($testrecord);
	$totalRows_testrecord = mysql_num_rows($testrecord);

	if($totalRows_testrecord > 0){		
		$responce->page = $page; 
		$responce->total = $total_pages; 
		$responce->records = $count; 
		
		$ii=0;
		do{
			$responce->rows[$ii]['id'] = $row_testrecord['id'];
			$responce->rows[$ii]['cell'] = array(
				//rid,'First name','Last name','Enrollment','Test Date', 'Test', 'Duration', 'Score', 'School'] 
				$row_testrecord['id'],
				$row_testrecord['Firstname'],
				$row_testrecord['Lastname'],
				$row_testrecord['EnrollmentYear'],
				$row_testrecord['date'],
				$row_testrecord['testname'],
				$row_testrecord['duration'],
				$row_testrecord['score'],
				$row_testrecord['school'],
			);
			
			$ii++; 
			
		}while ($row_testrecord = mysql_fetch_assoc($testrecord));
	}
	mysql_free_result($testrecord);
	
	print json_encode($responce);	
}
?>