<?php 
require_once('../../../../Private/config/config.php');
require_once('../../../../Private/class/function.php');
?>
<?php require_once('auth.php'); ?>
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
					case 'id':
						$afield = "a.`Index`";
						break;
					case 'date':
						$afield = "DATE(a.`Testdate`)";
						break;
					case 'test':
						$afield = "a.`Testid`";
						break;
					case 'time':
						$afield = "a.`Duration`";
						break;
					case 'score':
						$afield = "a.`Score`";
						break;						
				}
				
				if($v && $op) {
	                $i++;
					// ToSql in this case is absolutley needed
					$v = ToSql($field,$op,$v);
					if ($i == 1){ 
						$qwery = " AND ";
					}else{ 
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
	// we need here more advanced checking using the type of the field - i.e. integer, string, float
	switch ($field) {
		case 'id':
			return intval($val);
			break;
		case 'date':
		case 'test':
		case 'time':
			return floatval($val);
			break;
		case 'score':
			return floatval($val);
			break;
	}
}
?>
<?php
//
//use $_SESSION['MM_Userid'] for user's own preview
//use $_GET['id'] for instructor review student
//
$uid = isset($_GET['userid'])?$_GET['userid']:$_SESSION['MM_Userid'];

$page = $_REQUEST['page'];
$limit = $_REQUEST['rows'];
$sidx = isset($_REQUEST['sidx'])?$_REQUEST['sidx']:1;
$sord = $_REQUEST['sord'];


$wh = "";
$searchOn = Strip($_REQUEST['_search']);
if($searchOn=='true') {
	$searchstr = Strip($_REQUEST['filters']);
	$wh= constructWhere($searchstr);
}


mysql_select_db($database_cavoconnection, $cavoconnection);
mysql_query("SET NAMES UTF8");


//count records
$query_counts = "SELECT  a.`id`, DATE(a.`date`) AS date, a.`score`, a.`duration` AS time, a.`test`, b.`name` AS testname, a.`identifier` FROM `test_records` AS a LEFT JOIN `base_test` AS b ON (a.`test` = b.`id`) WHERE a.`user` = $uid".$wh;

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
	
	//get user test record
	$query_testrecord = "SELECT  a.`id`, DATE(a.`date`) AS date, a.`score`, a.`duration` AS time, a.`test`, b.`name` AS testname, a.`identifier` FROM `test_records` AS a LEFT JOIN `base_test` AS b ON (a.`test` = b.`id`) WHERE a.`user` = $uid".$wh." ORDER BY $sidx $sord LIMIT $start , $limit";
	
	$testrecord = mysql_query($query_testrecord, $cavoconnection) or die(mysql_error());
	$row_testrecord = mysql_fetch_assoc($testrecord);
	$totalRows_testrecord = mysql_num_rows($testrecord);

	//print $query_testrecord;

	if($totalRows_testrecord > 0){
		
		$responce->page = $page; 
		$responce->total = $total_pages; 
		$responce->records = $count; 
		
		$ii=0;
		do{
			$responce->rows[$ii]['id'] = $row_testrecord['id'];
			$responce->rows[$ii]['cell'] = array(
				$row_testrecord['id'],
				//substr($row_testrecord['date'], 0, 10),
				$row_testrecord['date'],
				$row_testrecord['testname'],
				duration($row_testrecord['time']),
				$row_testrecord['score']
			);
			
			$ii++; 
			
		}while ($row_testrecord = mysql_fetch_assoc($testrecord));
	}
	mysql_free_result($testrecord);
	
	print json_encode($responce);	
}
?>