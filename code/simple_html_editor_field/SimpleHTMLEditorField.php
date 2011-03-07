<?php

class SimpleHTMLEditorField extends TextareaField
{
	protected $css;
	protected $controls = array (
		'insertOrderedList' 	=>	true,
		'insertUnorderedList'	=> 	true,
		'insertImage'			=>  true,
		'justifyLeft' 			=>	true,
		'justifyRight' 			=> 	true,
		'justifyCenter'			=> 	true,
		'justifyFull' 			=> 	false,
		'cut' 					=> 	false,
		'copy' 					=>	false,
		'paste' 				=> 	false,
		'increaseFontSize' 		=> 	true,
		'decreaseFontSize' 		=> 	true,
		'h1'					=>  false,
		'h2'					=>  false,
		'h3'					=>  true,
		'h4'					=>  true,
		'h5'					=>  true,
		'h6'					=>  false,
		'html'					=>  true
		
		
	);

	function __construct($name, $title = null, $config = array(), $rows = 5, $cols = 55, $value = "", $form = null) {
		parent::__construct($name, $title, $rows, $cols, $value, $form);
		$this->extraClasses = array('hidden');
		if(!empty($config)) {
			foreach($config as $k => $v) {
				if($k == "css") $this->css = $v;
				else if(array_key_exists($k, $this->controls))
					$this->controls[$k] = $v;
			}
		}
	}
	
	private function getCss()
	{
		return $this->css ? "css : '{$this->css}'" : "css : ''";
	}
	
	private function getControls()
	{
		$controls = "controls : {\n";
		$first = true;
		foreach($this->controls as $var => $value) {
			$controls .= $first ? "" : ",";
			if((strlen($var) == 2 && $var[0] == "h") 
				&& stristr($_SERVER['HTTP_USER_AGENT'], "Mozilla")
				&& stristr($_SERVER['HTTP_USER_AGENT'], "Safari") === false
			) {
				$var.="mozilla";
			}
			$controls .= $var . " : ";
			$controls .= $value ? "{visible : true}" : "{visible : false}";
			$controls .=  "\n";
			$first = false;
		}
		$controls .= "},\n";
		return $controls;
	}
	
	private function getConfig()
	{
		return $this->getControls().$this->getCss();
	}
	
	public function FieldHolder()
	{
		Requirements::javascript('dataobject_manager/javascript/jquery.wysiwyg.js');
		Requirements::css('dataobject_manager/css/jquery.wysiwyg.css');
		Requirements::customScript("
			$(function() {
				$('#{$this->id()}').wysiwyg({
					{$this->getConfig()}
				}).parents('.simplehtmleditor').removeClass('hidden');
				
			});
		");
		return parent::FieldHolder();		
	}	
	
}




?>