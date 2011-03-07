<?php

class DatePickerField extends DateField 
{
	
	static $dateFormat = "dd/mm/yy";
	
	public function getProjectConfiguration(BedrockSetting $settings) {
		if($format = $settings->getDateFormat())
			self::set_date_format($format);
	}
	
	static function HTMLField( $id, $name, $val ) {
		return <<<HTML
			<input type="text" readonly="readonly" id="$id" name="$name" value="$val" /> (<a href="javascript:void(0)" rel="clear-btn">clear</a>)
HTML;
	}
	
	public static function set_date_format($format)
	{
		switch($format) {
			case "mdy":
			self::$dateFormat = "mm/dd/yy";
			break;
			
			case "dmy":
			self::$dateFormat = "dd/mm/yy";
			break;
			
			default:
			self::$dateFormat = "dd/mm/yy";
			break;
		}
	}
	
	public static function dmy()
	{
		return self::$dateFormat == "dd/mm/yy";
	}
	
	public static function mdy()
	{
		return self::$dateFormat == "mm/dd/yy";
	}
	
	
	function setValue($val) {
		if(is_string($val) && preg_match('/^([\d]{2,4})-([\d]{1,2})-([\d]{1,2})/', $val)) {
			$this->value = self::mdy() ? 
				preg_replace('/^([\d]{2,4})-([\d]{1,2})-([\d]{1,2})/','\\2/\\3/\\1', $val) :
				preg_replace('/^([\d]{2,4})-([\d]{1,2})-([\d]{1,2})/','\\3/\\2/\\1', $val);		
		} else {
			$this->value = $val;
		}
	}
	
	function dataValue() {
		if(is_array($this->value)) {
			if(isset($this->value['Year']) && isset($this->value['Month']) && isset($this->value['Day'])) {
				return $this->value['Year'] . '-' . $this->value['Month'] . '-' . $this->value['Day'];
			} else {
				user_error("Bad DateField value " . var_export($this->value,true), E_USER_WARNING);
			}
		} elseif(preg_match('/^([\d]{1,2})\/([\d]{1,2})\/([\d]{2,4})/', $this->value, $parts)) {
			return self::mdy() ? "$parts[3]-$parts[1]-$parts[2]" : "$parts[3]-$parts[2]-$parts[1]";
		} elseif(!empty($this->value)) {
			return date('Y-m-d', strtotime($this->value));
		} else {
			return null;
		}
	}
	
	public function validate() {return true;}
	
	function Field() {
		Requirements::javascript(THIRDPARTY_DIR."/jquery-livequery/jquery.livequery.js");
		Requirements::javascript(THIRDPARTY_DIR."/jquery-metadata/jquery.metadata.js");
    	Requirements::javascript("dataobject_manager/javascript/dom_jquery_ui.js");
  		Requirements::javascript("dataobject_manager/code/date_picker_field/datepicker_init.js");
		Requirements::css("dataobject_manager/css/ui/dom_jquery_ui.css");	
		$id = $this->id();
		$val = $this->attrValue();
		$field = parent::Field();
				
		$innerHTML = self::HTMLField( $id, $this->name, $val );
		
		return "
					<div class=\"datepicker field {dateFormat : '".self::$dateFormat."'}\">
						$innerHTML
					</div>
		";	
		}
		
}

class DatePickerField_Controller extends Controller
{
	function dateformat()
	{
	  echo DatePickerField::$dateFormat;
	}
}

?>