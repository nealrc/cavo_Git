<?php
if (!function_exists("GetSQLValueString")) {
function GetSQLValueString($theValue, $theType, $theDefinedValue = "", $theNotDefinedValue = "") {
  $theValue = get_magic_quotes_gpc() ? stripslashes($theValue) : $theValue;
  $theValue = function_exists("mysql_real_escape_string") ? mysql_real_escape_string($theValue) : mysql_escape_string($theValue);

  switch ($theType) {
    case "text":
      $theValue = ($theValue != "") ? "'" . $theValue . "'" : "NULL";
      break;    
    case "long":
    case "int":
      $theValue = ($theValue != "") ? intval($theValue) : "NULL";
      break;
    case "double":
      $theValue = ($theValue != "") ? "'" . doubleval($theValue) . "'" : "NULL";
      break;
    case "date":
      $theValue = ($theValue != "") ? "'" . $theValue . "'" : "NULL";
      break;
    case "defined":
      $theValue = ($theValue != "") ? $theDefinedValue : $theNotDefinedValue;
      break;
  }
  return $theValue;
}
}
function checkEmail($address) {
    // Check against a massive regex for validity ...
    $email = preg_match("/^[a-z0-9_-][a-z0-9._-]+@([a-z0-9][a-z0-9-]*\.)+[a-z]{2,6}$/i", $address);
	if($email){
		return $email;
	}else{
		return false;
	}
}
function checkYear($yr) {
	if(strlen($yr)!=4) return false;

	if(!is_numeric($yr)) return false;

// 	$currentyr = date('Y');	
//	if($yr > $currentyr || $yr <1900) return false;
			
	return true;	
}

function isEmpty($str){
	if($str == '' ){
		return true;
	}else{
		return false;
	}
}
function sortAssoc($arr, $order_by, $descendent=true, $flags=0){
    $named_hash = array();
     foreach($arr as $key => $fields){
             $named_hash["$key"] = $fields[$order_by];
	} 
    if($descendent){
		arsort($named_hash,$flags=0);
    }else{ 
		asort($named_hash, $flags=0);
	}
    $sorted_records = array();
    foreach($named_hash as $key => $val){
           $sorted_records["$key"]= $arr[$key];
 	}	
	return $sorted_records;
}

function cavo_encrypt($a){
	$dict=array('a','b','c','d','e','f','g','h','i','j','k','l','m','n','o','p','q','r','s','t','u','v','w','x','y','z');
	$b = pow(($a/2),2)+2941;
	$f = $b - floor($b);
	$e='';
	if($f>0){
		$c = sprintf('%f',$f);
		$ct = explode('.',$c);
		$d = $ct[1];
		for($i=0;$i<strlen($d);$i++){
			$e .= $dict[$d{$i}];
		}
	}
	return strval(floor($b)).$e;
}
function cavo_decrypt($a){
	$dict=array('a','b','c','d','e','f','g','h','i','j','k','l','m','n','o','p','q','r','s','t','u','v','w','x','y','z');
	$b=''; $c='';
	for($i=0;$i<strlen($a);$i++){
		if(!is_numeric($a{$i})){
			$b .= array_search($a{$i},$dict);
		}else{
			$c .= $a{$i};
		}
	}
	$e = $c;
	if(strlen($b)>0){
		$e .= '.'.$b;
	}
	$e = floatval($e);
	return intval(2*pow(($e-2941),0.5));
}
 function duration($seconds_count){
 		$delimiter  = ':';
 		$seconds = $seconds_count % 60;
  		$minutes = floor($seconds_count/60);
  		$hours   = floor($seconds_count/3600);
  
  		$seconds = str_pad($seconds, 2, "0", STR_PAD_LEFT);
  		$minutes = str_pad($minutes, 2, "0", STR_PAD_LEFT).$delimiter;
  
  		if($hours > 0)
 		{
 			$hours = str_pad($hours, 2, "0", STR_PAD_LEFT).$delimiter;
  		}
 		else
 		{
 			$hours = str_pad($hours, 2, "0", STR_PAD_LEFT).$delimiter;
 		}
 
 		return "$hours$minutes$seconds";
}
if (!function_exists("gensqlstring")) {
function gensqlstring(&$val){
	$val = "'".$val."'";
}
}
?>