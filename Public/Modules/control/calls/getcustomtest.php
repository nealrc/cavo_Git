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
					case 'test_active': $field = 'a.`active`'; break;	
					case 'test_type': $field = 'c.`id`'; break;	
					case 'test_category': $field = 'e.`id`'; break;	
					case 'school': $field = 'f.`id`'; break;	
					default: $field = $field;
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
					        $qwery .= $field.$qopers[$op]." (".$v.")";
					        break;
						default:
					        $qwery .= $field.$qopers[$op].$v;
					}
				}
            }
        }
    }
    return $qwery;
}
function ToSql ($field, $oper, $val) {
	switch ($field) {
		case 'c.`id`': 				return intval($val); break;
		case 'e.`id`': 				return intval($val); break;
		case 'f.`id`': 				return intval($val); break;
		case 'a.`active`':			return intval($val); break;
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
	$sidx = $_REQUEST['sidx'];
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
	$whh = "LEFT JOIN `user` AS g ON (g.`University` = a.`school`) WHERE g.`Userid` = $user_id";
}

// record count
$query_counts = "SELECT a.`id` AS id, a.`date_start` AS date_start, a.`date_end` AS date_end, a.`date_create` AS date_create, a.`active` AS test_active,
					b.`name` AS test_name, b.`description` AS test_description, 
					c.`description` AS test_type, 
					e.`name` AS test_category,
					CONCAT(CONCAT(UCASE(SUBSTRING(d.`Firstname`, 1,1)),LOWER(SUBSTRING(d.`Firstname`, 2))),' ',CONCAT(UCASE(SUBSTRING(d.`Lastname`, 1,1)),LOWER(SUBSTRING(d.`Lastname`, 2)))) AS creator,
					f.`name` AS school
				FROM `school_test` AS a
				LEFT JOIN `base_test` AS b ON (a.`test` = b.`id`)
				LEFT JOIN `base_test_type` AS c ON (b.`test_type` = c.`id`)
				LEFT JOIN `user` AS d ON (a.`user` = d.`Userid`)
				LEFT JOIN `base_test_category` AS e ON (b.`test_category` = e.`id`)
				LEFT JOIN `base_school` AS f ON (d.`University` = f.`id`)";
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
	$query_records = "SELECT a.`id` AS id, a.`date_start` AS date_start, a.`date_end` AS date_end, a.`date_create` AS date_create, a.`active` AS test_active,
					b.`name` AS test_name, b.`description` AS test_description, 
					c.`description` AS test_type, c.`id` AS test_type_id,
					e.`name` AS test_category, e.`id` AS test_category_id,
					CONCAT(CONCAT(UCASE(SUBSTRING(d.`Firstname`, 1,1)),LOWER(SUBSTRING(d.`Firstname`, 2))),' ',CONCAT(UCASE(SUBSTRING(d.`Lastname`, 1,1)),LOWER(SUBSTRING(d.`Lastname`, 2)))) AS creator,
					f.`name` AS school, f.`id` AS school_id
				FROM `school_test` AS a
				LEFT JOIN `base_test` AS b ON (a.`test` = b.`id`)
				LEFT JOIN `base_test_type` AS c ON (b.`test_type` = c.`id`)
				LEFT JOIN `user` AS d ON (a.`user` = d.`Userid`)
				LEFT JOIN `base_test_category` AS e ON (b.`test_category` = e.`id`)
				LEFT JOIN `base_school` AS f ON (d.`University` = f.`id`)";
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
                $responce = new stdClass();
		$responce->page = $page; 
		$responce->total = $total_pages; 
		$responce->records = $count; 
		$ij=0;
		do{
			//"Index", "Name", "Type", "Category", "Description", "Creator", "Date Created", "Date Start", "Date End", "Active", "School"
			$aj = $rows_records['test_active']== 1?'Yes':'No';
			
			$responce->rows[$ij]['id'] = $rows_records['id']; 
			$responce->rows[$ij]['cell']=array(
				$rows_records['id'],
				$rows_records['test_name'],
				$rows_records['test_type'],
				$rows_records['test_category'],
				$rows_records['test_description'],
				$rows_records['creator'],
				$rows_records['date_create'],
				$rows_records['date_start'],
				$rows_records['date_end'],
				$aj,
				$rows_records['school']
			); 
			$ij++; 
		}while ($rows_records = mysql_fetch_assoc($records));
	}
	mysql_free_result($records);

	print json_encode($responce);
}
?>