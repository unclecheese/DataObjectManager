<?php

class StateProvinceDropdownField extends DropdownField
{
	protected static $state_province_map = array('AL' => 'Alabama','AK' => 'Alaska','AZ' => 'Arizona','AR' => 'Arkansas','CA' => 'California','CO' => 'Colorado','CT' => 'Connecticut','DE' => 'Delaware', 'DC' => 'Disctrict of Columbia', 'FL' => 'Florida','GA' => 'Georgia','HI' => 'Hawaii','ID' => 'Idaho','IL' => 'Illinois','IN' => 'Indiana','IA' => 'Iowa','KS' => 'Kansas','KY' => 'Kentucky','LA' => 'Louisiana','ME' => 'Maine','MD' => 'Maryland','MA' => 'Massachusetts','MI' => 'Michigan','MN' => 'Minnesota','MS' => 'Mississippi','MO' => 'Missouri','MT' => 'Montana','NE' => 'Nebraska','NV' => 'Nevada','NH' => 'New Hampshire','NJ' => 'New Jersey','NM' => 'New Mexico','NY' => 'New York','NC' => 'North Carolina','ND' => 'North Dakota','OH' => 'Ohio','OK' => 'Oklahoma','OR' => 'Oregon','PA' => 'Pennsylvania','RI' => 'Rhode Island','SC' => 'South Carolina','SD' => 'South Dakota','TN' => 'Tennessee','TX' => 'Texas','UT' => 'Utah','VT' => 'Vermont','VA' => 'Virginia','WA' => 'Washington', 'WV' => 'West Virginia','WI' => 'Wisconsin','WY' => 'Wyoming','AB' => 'Alberta ','BC' => 'British Columbia ','MB' => 'Manitoba ','NB' => 'New Brunswick ','NL' => 'Newfoundland and Labrador ','NT' => 'Northwest Territories ','NS' => 'Nova Scotia ','NU' => 'Nunavut ','ON' => 'Ontario ','PE' => 'Prince Edward Island ','QC' => 'Quebec ','SK' => 'Saskatchewan ','YT' => 'Yukon');
	
	function __construct($name, $title = null, $value = "", $form = null, $emptyString = null) {
	 parent::__construct($name, $title, self::$state_province_map, $value, $form, $emptyString);
	}

}