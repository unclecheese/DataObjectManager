<?php

class SimpleTinyMCEField extends TextareaField
{

  private static $default_plugins = "safari,paste";
  private static $default_theme = "advanced";
  private static $default_buttons = array(
    'bold,italic,underline,|,justifyleft,justifycenter,justifyright,|,styleselect,formatselect',
    'cut,copy,paste,pastetext,pasteword,|,bullist,numlist,|,link,unlink,anchor,image,|,code'
  );
  private static $default_toolbar_location = "top";
  private static $default_toolbar_align = "left";
  private static $default_statusbar_location = "bottom";
  private static $default_advanced_resizing = false;
  private static $default_content_css;
  private static $default_extra_options;
  
  private $plugins;
  private $theme;
  private $buttons;
  private $toolbarLocation;
  private $toolbarAlign;
  private $statusbarLocation;
  private $advancedResizing;
  private $contentCSS;
  private $extraOptions;
  
  
	
	function __construct($name, $title = null, $config = array(), $rows = 15, $cols = 55, $value = "", $form = null) 
	{
		parent::__construct($name, $title, $rows, $cols, $value, $form);
  }

  public static function set_default_plugins($value)
  {
    self::$default_plugins = $value;
  }

  public static function set_default_theme($value)
  {
    self::$default_theme = $value;
  }
  
  public static function set_default_buttons($buttons)
  {
    if(!is_array($buttons))
      die("<strong>".__CLASS__."::set_default_buttons()</strong> Value must be passed as an array of rows.");
    self::$default_buttons = $buttons;
  }
  
  public static function set_default_toolbar_align($value)
  {
    self::$default_toolbar_align = $value;
  }

  public static function set_default_statusbar_location($value)
  {
    self::$default_statusbar_location = $value;
  }

  public static function set_default_advanced_resizing($bool)
  {
    self::$default_advanced_resizing = $bool;
  }

  public static function set_default_content_css($value)
  {
    self::$default_content_css = $value;
  }

  public static function set_default_extra_options($value)
  {
    self::$default_extra_options = $value;
  }
  
  private function getPlugins()
  {
    return $this->plugins ? $this->plugins : self::$default_plugins;
  }
  
  private function getTheme()
  {
    return $this->theme ? $this->theme : self::$default_theme;  
  }
  
  private function getButtons()
  {
    $buttons = $this->buttons ? $this->buttons : self::$default_buttons;
    if(sizeof($buttons < 4)) {
      for($i=0;$i<4;$i++)
        if(!isset($buttons[$i])) $buttons[$i] = "";
    }
    $ret = "";
    $first = true;
    foreach($buttons as $index => $line) {
      $ret .= sprintf("%stheme_%s_buttons%d : '%s'",
        $first ? "" : ",",
        $this->getTheme(),
        $index+1,
        $line
      );
      $first = false;
    }
    return $ret;
  }
  
  public function setPlugins($plugins)
  {
    $this->plugins = $plugins;
  }
  
  public function setTheme($theme)
  {
    $this->theme = $theme;
  }
  
  public function setButtons($buttons)
  {
    if(!is_array($buttons))
      die("<strong>{$this->class}::setButtons()</strong> Value must be passed as an array of rows.");
    $this->buttons = $buttons;
  }
  
  private function getToolbarLocation()
  {
    return $this->toolbarLocation ? $this->toolbarLocation : self::$default_toolbar_location;
  }

  private function getToolbarAlign()
  {
    return $this->toolbarAlign ? $this->toolbarAlign : self::$default_toolbar_align;
  }
  
  private function getStatusbarLocation()
  {
    return $this->statusbarLocation ? $this->statusbarLocation : self::$default_statusbar_location;
  }
  
  private function getAdvancedResizing()
  {
    $bool = $this->advancedResizing !== null ? $this->advancedResizing : self::$default_advanced_resizing;
    return $bool ? "true" : "false";
  }
  
  private function getContentCSS()
  {
    return $this->contentCSS ? $this->contentCSS : self::$default_content_css;    
  }
  
  private function getExtraOptions()
  {
    $value = $this->extraOptions ? $this->extraOptions : self::$default_extra_options; 
    return $value ? ",".$value : "";
  }
  
  public function setToolbarLocation($loc)
  {
    $this->toolbarLocation = $loc;
  }
  
  public function setStatusbarLocation($loc)
  {
    $this->statusbarLocation = $loc;
  }
  
  public function setAdvancedResizing($bool)
  {
    $this->advancedResizing = $bool;
  }
  
  public function setContentCSS($css)
  {
    $this->contentCSS = $css;
  }
  
  public function setExtraOptions($opts)
  {
    $this->extraOptions = $opts;
  }
  
  private function buildJS()
  {
    $js = sprintf("
      $(function() {
				$('#%s').tinymce({
  				  plugins : '%s',
  				  theme : '%s',
  			    %s,	  
  			   theme_advanced_toolbar_location : '%s',
      		 theme_advanced_toolbar_align : '%s',
      		 theme_advanced_statusbar_location : '%s',
      		 theme_advanced_resizing : %s,
           paste_auto_cleanup_on_paste : true, 
           paste_remove_spans: true, 
           paste_remove_styles: true,      		 
  			   content_css : '%s'
  			   %s
		    });
		  });",
		  $this->Id(),
		  $this->getPlugins(),
		  $this->getTheme(),
		  $this->getButtons(),
		  $this->getToolbarLocation(),
		  $this->getToolbarAlign(),
		  $this->getStatusbarLocation(),
		  $this->getAdvancedResizing(),
		  $this->getContentCSS(),
		  $this->getExtraOptions()
		  
		);
    return $js;
  }

  function Field()
  {
    Requirements::javascript(THIRDPARTY_DIR."/jquery/jquery.js");
    Requirements::javascript("dataobject_manager/code/simple_tinymce_field/javascript/tiny_mce/jquery.tinymce.js");
    Requirements::javascript("dataobject_manager/code/simple_tinymce_field/javascript/tiny_mce/tiny_mce.js");
    Requirements::customScript($this->buildJS());
    return parent::Field();
  }

}
