<?php
require_once("segmentation.php");

// implement custom test design engine
class CustomTest{
	private $keywords;
	private $subject;
	private $article;
	

	function __construct($article, $subject){
		// parse article
		$this->keywords = parse
		// set subject
		$this->subject = $subject;
	}
	
	public function parser(){
		// do parsing
		
		return $this->keywords;	
	}
}
?>