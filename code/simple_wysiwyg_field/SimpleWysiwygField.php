<?php

class SimpleWysiwygField extends TextareaField
{

	private static $default_configuration = array();
	private $configuration = array();
	
	public $initFile;
	public static $default_init_js = "dataobject_manager/code/simple_wysiwyg_field/javascript/simple_wysiwyg_init.js";
	
	function __construct($name, $title = null, $config = array(), $rows = 15, $cols = 55, $value = "", $form = null) 
	{
		parent::__construct($name, $title, $rows, $cols, $value, $form);
		
  }
  
  public static function set_default_configuration($conf)
  {
    self::$default_configuration = $conf;
  }
  
  public function configure($conf)
  {
    $this->configuration = $conf;
  }
  
  private function getConfiguration()
  {
    return !empty($this->configuration) ? $this->configuration : self::$default_configuration;
  }
  
  private function buildJS()
  {
    $js = "
      $(function() {
				$('#{$this->id()}').htmlbox().idir('dataobject_manager/code/simple_wysiwyg_field/images/')";
    foreach($this->getConfiguration() as $row => $buttons) {
      $r = $row+1;
      $r = $r == 1 ? "" : ",$r";
      foreach($buttons as $button)
        $js .= $button == "|" ? ".separator('dots'$r)" : ".button('$button'$r)";
    }
    $js .= "
      .init();
    });";
    return $js;
  }
  
  public function getInitFile()
  {
    if($this->initFile)
      return $this->initFile;
    elseif(Director::fileExists($file = project()."/javascript/simple_wysiwyg_init.js"))
      return $file;
    elseif(Director::fileExists($file = ViewableData::ThemeDir()."/javascript/simple_wysiwyg_init.js"))
      return $file;
    else
      return self::$default_init_js;
  }
  
  
  
  function Field()
  {
    Requirements::javascript(THIRDPARTY_DIR."/jquery/jquery.js");
    Requirements::javascript("dataobject_manager/code/simple_wysiwyg_field/javascript/htmlbox.min.js");
    Requirements::javascript("dataobject_manager/code/simple_wysiwyg_field/javascript/xhtml.js");
    Requirements::customScript($this->buildJS());
    return parent::Field();
  }

}



