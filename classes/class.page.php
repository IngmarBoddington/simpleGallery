<?php

/** 
 * Page class. 
 * 
 * Used to store values for use in html output for each page
 * @author BJP
 * 
 */
class page {
	//base default values...
	public $template 				= 'template/index.php';
	public $title 					= 'Simple Photo Gallery';
	public $description				= 'A really simply photo gallery site by http://ba.rrypark.in';
	public $header					= '<h1>Simple Photo Gallery</h1>';
	public $navigation				= '';
	public $content					= '';
	public $footer					= 'Functionality by: <a href="http://ba.rrypark.in">Barry Parkin</a>';
	
	//html output
	public $html					= '';
	
	//folder that the site exists in...
	private $installdirectory 		= '';
	
	/**
	 */
	function __construct() {		
		//load template
		$this->html = file_get_contents($this->template);
		
		//determine installation directory
		$this->installdirectory = str_ireplace('index.php', '', $_SERVER['SCRIPT_NAME']);
	}
	
	public function __set($name, $value) {
		$this->$name = $value;
		if($name == 'template') {
			//template has changed, so load the new one
			$this->html = file_get_contents($this->template);
		}
	}
	
	/**
	 * Outputs the html
	 */
	function __destruct() {		
		//begin find & replaces...
		$this->html = str_replace('<% $page->title %>', $this->title, $this->html);
		$this->html = str_replace('<% $page->description %>', $this->description, $this->html);
		$this->html = str_replace('<% $page->header %>', $this->header, $this->html);
		$this->html = str_replace('<% $page->navigation %>', $this->navigation, $this->html);
		$this->html = str_replace('<% $page->content %>', $this->content, $this->html);
		$this->html = str_replace('<% $page->footer %>', $this->footer, $this->html);
		$this->html = str_replace('<% $page->installdirectory %>', $this->installdirectory, $this->html);
		
		echo $this->html;
	}
}

?>