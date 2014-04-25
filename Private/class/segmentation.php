<?php
require_once("idcrypt.php");

class Segmentation{
	private $dbhandle;
	private $input;
	private $cleanedstr;
	
	private $mingram = 2;
	private $maxgram = 4;
	private $limit = 5000;
	private $dbtiku = 'tiku_cavo_test';	
	
	private $keywords;
	
	public  $encoding = 'utf-8';
		
	function __construct($article, $maxgram, $mingram, $idb, $limit=NULL, $dict=NULL){
		if($maxgram >= $mingram && $mingram > 0){
			$this->mingram = $mingram;
			$this->maxgram = $maxgram;
		}else{
			throw new Exception("Invalid min/max gram number entered for class: Segmentation($article, $maxgram, $mingram, $limit=NULL, $dict=NULL)!");	
		}
		
		if( is_null($limit) ){
			if(mb_strlen($article, $this->encoding) < $this->limit){
				$this->limit = mb_strlen($article, $this->encoding);
			}
		}else{
			if(mb_strlen($article, $this->encoding) < $limit){
				$this->limit = mb_strlen($article, $this->encoding);
			}else{
				$this->limit = $limit;
			}
		}
		$this->input= $article;
		
		//$this->input = mb_substr($article,0,$this->limit, $this->encoding);
				
		if(isset($dict)) $this->dbtiku = $dict;
		
		if(is_null($idb)|| !isset($idb) ){
			throw new Exception("You must enter a valid mysqli database handle!");	
		}else{
			$this->dbhandle = $idb;
		}
	}
	
	// clean the input string
	public function preprocessing(){
		$str = mb_substr($this->input,0,$this->limit, $this->encoding);
		$str = preg_replace("/[0-9a-zA-Z]/",'',$str);
		$str = mb_strtolower(trim(preg_replace('#[^\p{L}\p{N}]+#u',' ',$str)), $this->encoding);
		
		return $str;
	}
	
	// keyword identification
	public function parse(){
		$lib = array();
		$encoding = $this->encoding;


		$icavoconnection = $this->dbhandle;

		if (mysqli_connect_errno()) {
			throw new Exception("Connect failed: %s\n", mysqli_connect_error());
		}else{
		}
		
		$icavoconnection->query("SET NAMES UTF8");
		
		// build lexicon
		$query = "SELECT DISTINCT TRIM(`word`) AS voc FROM `".$this->dbtiku."` WHERE char_length(`word`)>=".$this->mingram." AND char_length(`word`)<=".$this->maxgram;

		if($result = $icavoconnection->query($query)) {
			$nlex = $result->num_rows;
			$c=0;
			while ($rows = $result->fetch_assoc()) {
				$lib[$rows['voc']]=$c;
				$c++;
			}
			$result->close();
		}
		
		// matching
		$fstr=explode(" ", $this->getcleanedstr());
		
		foreach($fstr as $kk => $string){
			if(mb_strlen(trim($string),$encoding)>0){
				$len = mb_strlen($string, $encoding);
				if($len > 1){
					$pos = 0;
					while($pos < $len){
						$find = false;
						$gram = ($len-$pos)>=$this->maxgram?$this->maxgram:($len-$pos);
						while($gram >= $this->mingram){
							$seg = mb_substr($string,$pos,$gram,$encoding);						
							if(isset($lib[$seg]) ){
								$find = true;
								$matchs[] = $seg;
								break;
							}
							$gram--;
						}
						$pos = $find?($pos+$gram):($pos+1);
					}
				}
			}
		}
		
		// fetching	
		if(count($matchs)>0){		
			$freq = array_count_values($matchs);
			$checks = array_keys($freq);
			array_walk($checks, 'gensqlstring');
			$vocs = implode($checks,',');
			
			$sql = "SELECT `id`, `word`, TRIM(`py`) AS 'PY', TRIM(`cn`) AS 'CN', TRIM(`en`) AS 'EN' FROM `".$this->dbtiku."` WHERE `word` IN ($vocs)";
			if($result = $icavoconnection->query($sql)) {
				while ($rows = $result->fetch_assoc()) {
					$len = mb_strlen($rows['word'],$encoding);
					$f = $freq[$rows['word']];
					
					if(!isset($data[$rows['word']])){
						$r = 0;
					}else{
						$r = count($data[$rows['word']]['v']);
					}
					$data[$rows['word']]['len']= mb_strlen($rows['word'],$encoding);
					$data[$rows['word']]['freq']= $freq[$rows['word']];
					
					$cryptid = new IdCrypt($rows['id'], 'encrypt');
					$data[$rows['word']]['ids'][] = $cryptid->getresult();
				}			
				$result->close();
			}
		}
		
		// parsing
		if(isset($data) && count($data)>0){		
			foreach($data as $v => $row){
				$vfreq[$v] = $row['freq'];
				$vlen[$v] = $row['len'];
			}
			array_multisort($vfreq,SORT_DESC,$vlen,SORT_DESC,$data);
			
			$cc=0;
			foreach($data as $voc => $iinfo){
				$dd[$cc]['voc'] = $voc;
				$dd[$cc]['freq'] = $iinfo['freq'];
				$dd[$cc]['len'] = $iinfo['len'];
				$dd[$cc]['ids'] = implode('_', $iinfo['ids']);
				$cc++;
			}
			
			$this->keywords = $dd;
		}else{
			$this->keywords = NULL;	
		}
		
		
	}
	
	public function getcleanedstr(){
		return $this->preprocessing();
	}
	
	public function getkeywords(){
		$this->parse();
		return $this->keywords;	
	}
	public function getlimit(){
		return $this->limit;	
	}
	public function getsrclen(){
		return mb_strlen($this->input, $this->encoding);
	}
}
function gensqlstring(&$val){
	$val = "'".$val."'";
}
?>