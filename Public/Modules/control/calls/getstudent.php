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

				$afield = 'a.`'.$field.'`';
				
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
					}
				}
            }
        }
    }
    return $qwery;
}
function ToSql ($field, $oper, $val) {
	switch ($field) {
		case 'Userid': 			return intval($val); break;
		case 'Membership':		return intval($val); break;
		case 'University':		return intval($val); break;
		case 'EnrollmentYear':	return intval($val); break;
		case 'age':				return intval($val); break;
		case 'native':			return intval($val); break;
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
$page = $_REQUEST['page'];
$limit = $_REQUEST['rows'];
$sord = $_REQUEST['sord'];

if(isset($_REQUEST['sidx'])){
	$sidx = "a.`".$_REQUEST['sidx']."`";
}else{
	$sidx = 1;
}


$wh = "";
$searchOn = Strip($_REQUEST['_search']);
if($searchOn=='true') {
	$searchstr = Strip($_REQUEST['filters']);
	$wh= constructWhere($searchstr);
	//echo $wh;
}

mysql_select_db($database_cavoconnection, $cavoconnection);
mysql_query("SET NAMES UTF8");

$user_id = $_SESSION['MM_Userid'];
$user_member = array_search($_SESSION['MM_UserGroup'], $role);

$whh='';
if($user_member != 'admin'){
	$whh = "INNER JOIN `user` AS d ON (d.`University` = a.`University`) WHERE d.`Userid` = $user_id AND b.`name` = 'student'";
}

// record count
$query_counts = "SELECT a.`Userid`, a.`Firstname`, a.`Lastname`, a.`Email`, a.`Password`, a.`Membership`, a.`University`, a.`Active`, a.`EnrollmentYear`, a.`native`, 
				b.`name` AS role, c.`name` AS school, e.`range` AS age
				FROM `user` AS a
				LEFT JOIN `base_membership` AS b ON (a.`Membership` = b.`id`)
				LEFT JOIN `base_school` AS c ON (a.`University` = c.`id`)
				LEFT JOIN `base_age` AS e ON (a.`age` = e.`id`)";
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

if($count <= 0){
	print 100;
}else{
	// set parameter
	$total_pages = ceil($count/$limit);
	$page = ($page > $total_pages) ? $total_pages : $page;
	$start = ($limit*$page - $limit > 0)?($limit*$page - $limit):0;

	// record count
	$query_records = "SELECT a.`Userid`, a.`Firstname`, a.`Lastname`, a.`Email`, a.`Password`, a.`Membership`, a.`University`, a.`Active`, a.`EnrollmentYear`, a.`native`,
					b.`name` AS role, c.`name` AS school, e.`range` AS age
					FROM `user` AS a
					LEFT JOIN `base_membership` AS b ON (a.`Membership` = b.`id`)
					LEFT JOIN `base_school` AS c ON (a.`University` = c.`id`)
					LEFT JOIN `base_age` AS e ON (a.`age` = e.`id`)";
	if(strlen($whh)>0){$query_records .= "  ".$whh; }
	if(strlen($wh)>0) {
		if(strlen($whh)>0){
			$query_records .= " AND ".$wh;
		}else{
			$query_records .=" WHERE ".$wh;
		}
	}
	$query_records .= " ORDER BY $sidx $sord LIMIT $start, $limit";
	// print $query_records;
	$records = mysql_query($query_records, $cavoconnection) or die(mysql_error());
	$rows_records = mysql_fetch_assoc($records);
	$totalRows_records = mysql_num_rows($records);

	if($totalRows_records > 0){		
		$responce->page = $page; 
		$responce->total = $total_pages; 
		$responce->records = $count; 
		$ij=0;
		do{
			$aj = $rows_records['Active']== 1?'Yes':'No'; 	
			$aq = $rows_records['native'] == 1? 'Yes' : 'No';
			$responce->rows[$ij]['id'] = $rows_records['Userid']; 
			$responce->rows[$ij]['cell']=array(
				$rows_records['Userid'],
				$rows_records['Firstname'],
				$rows_records['Lastname'],
				$rows_records['Email'],
				$rows_records['Password'],
				'',		//password2
				$rows_records['role'],
				$rows_records['school'], 
				$rows_records['EnrollmentYear'],
				$rows_records['age'],
				$aq,
				$aj
			); 
			$ij++; 
		}while ($rows_records = mysql_fetch_assoc($records));
	}
	mysql_free_result($records);

	print json_encode($responce);
}
?>