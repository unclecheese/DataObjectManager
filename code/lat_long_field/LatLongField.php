<?php

class LatLongButton extends FormField {
	
	protected static $instance_count = 0;
		
	protected $addressFields = array();
	
	protected $latField;
	
	protected $longField;
	
	protected $buttonText;
	
	public function __construct($latField = "Lat", $longField = "Long", $addressFields = array(), $buttonText = null) {
		self::$instance_count++;
		$this->addressFields = $addressFields;
		$this->latField = $latField;
		$this->longField = $longField;
		$this->buttonText = $buttonText;
		parent::__construct($latField.$longField.self::$instance_count, "");	
	}
	
	public function Field() {
		Requirements::javascript(THIRDPARTY_DIR.'/jquery/jquery.js');
		Requirements::javascript(THIRDPARTY_DIR.'/jquery-metadata/jquery.metadata.js');
		Requirements::javascript('dataobject_manager/code/lat_long_field/javascript/lat_long_field.js');
		Requirements::css('dataobject_manager/code/lat_long_field/css/lat_long_field.css');
		return sprintf('<a class="geocode_button {\'aFields\': \'%s\',\'lat\': \'%s\', \'long\': \'%s\'}" href="'.$this->Link('geocode').'">'.
							_t('LatLongField.GEOCODE','Look up').
						'</a>', implode(',',$this->addressFields), $this->latField, $this->longField);

	}
	
	public function geocode(SS_HTTPRequest $r) {
		if($address = $r->requestVar('address')) {
			if($json = @file_get_contents("http://maps.googleapis.com/maps/api/geocode/json?sensor=false&address=".urlencode($address))) {
				$response = Convert::json2array($json);
				$location = $response['results'][0]->geometry->location;
				return new SS_HTTPResponse($location->lat.",".$location->lng);
			}
		}
	}
		
}