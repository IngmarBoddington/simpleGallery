<?php

/** 
 * Page class. 
 * 
 * Used to create html output for each page
 * @author BJP
 * 
 */
class page {
	// TODO - Insert your code here

	public $html						= '';
	
	public $header						= '';
	public $headTitle 					= 'default';
	public $headKeywords				= 'default';
	public $headDescription				= 'default';
	public $headExtraTagArray			= array();
	
	public $body						= '';
	
	private $installDirectory 			= '';
	
	/**
	 */
	function __construct() {		
		$this->installDirectory		= str_ireplace('index.php', '', $_SERVER['SCRIPT_NAME']);
		
		//insert some default head tags
		$this->headExtraTagArray[] 	= '<meta name="robots" content="index,follow" />';
		$this->headExtraTagArray[] 	= '<link rel="stylesheet" type="text/css" href="'.$this->installDirectory.'styles.css" />';
		$this->headExtraTagArray[] 	= '<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.7.1/jquery.min.js"></script>';
		$this->html 				= file_get_contents('views/page_template.inc.php');
	}
	
	/**
	 * Outputs the html
	 */
	function __destruct() {
		//build header
		$this->header 	= '<title>' . $this->headTitle . '</title>' . PHP_EOL;
		$this->header 	.= '	<meta charset="utf-8" />' . PHP_EOL;
		$this->header 	.= '	<meta name="keywords" content="' . $this->headKeywords . '" />' . PHP_EOL;
		$this->header 	.= '	<meta name="description" content="' . $this->headDescription	 . '" />' . PHP_EOL;
		foreach($this->headExtraTagArray as $tag) {
			$this->header 	.= '	' . $tag	 . PHP_EOL;
		}
		
		//output html for the page
		echo sprintf($this->html,
					$this->header,
					$this->body
					);
	}
}

?>