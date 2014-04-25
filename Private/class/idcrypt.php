<?php
class IdCrypt{
	private $source;
	private $result;
	public $voca =array('a','b','c','d','e','f','g','h','i','j','k','l','m','n','o','p','q','r','s','t','u','v','w','x','y','z');
	
	function __construct($id,$action){				
		if($id<=0){
			throw new Exception("Only positive integer id will be accepted!");
		}
		
		if(!in_array($action, array('encrypt', 'decrypt'))){
			throw new Exception("Only action 'encrypt' or 'decrypt' is allowed for class: idcrypt($id, $action) !");
		}
		
		$this->source = $id;
		switch($action){
			case 'encrypt': $this->result = $this->encrypt(); break;
			case 'decrypt': $this->result = $this->decrypt(); break;			
		}
	}
	public function encrypt(){
		$b = pow(($this->source/2),2)+2941;
		$f = $b - floor($b);
		$e='';
		if($f>0){
			$c = sprintf('%f',$f);
			$ct = explode('.',$c);
			$d = $ct[1];
			for($i=0;$i<strlen($d);$i++){
				$e .= $this->voca[$d{$i}];
			}
		}
		return strval(floor($b)).$e;		
	}
	
	public function decrypt(){
		$b=''; 
		$c='';
		$a = $this->source;
		
		for($i=0;$i<strlen($a);$i++){
			if(!is_numeric($a{$i})){
				$b .= array_search($a{$i},$this->voca);
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
	
	public function getresult(){
		return $this->result;
	}
}
?>