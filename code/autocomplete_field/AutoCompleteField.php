<?php

/**
 * Renders a textfield that will return a set of results from a callback URL
 * as the user types. Useful for search forms.
 *
 * By default, the field will look on the current controller for a function named
 * "autocomplte", but it can be passed a custom function or a custom URL. For globally
 * accessible fields, it's a good idea to use a custom URL, so it doesn't attempt a
 * request for something like /Security/autocomplete.
 *
 * Any template can be returned in the callback function, but to make things easier,
 * you can use AutoCompleteField::render($resultSet) to fall back on the default
 * template.
 */
class AutoCompleteField extends TextField {
	
	/**
	 * Render a default template of results.
	 * Note: You must have functions "AutoCompleteTitle()" and "AutoCompleteSummary"
	 * defined on the returned objects.
	 * @param DataObjectSet $results The results to render in the autocomplete box
	 * @return SSViewer
	 */
	public static function render($results) {
		if(!$results) return false;	
		
		$template = new SSViewer('AutoComplete_default');
		return $template->process(new ArrayData(array (
			'Results' => $results
		)));
	}
	
	/**
	 * Constructor for AutoCompleteField
	 * @param string $name The name of the field
	 * @param string $title The label for the field
	 * @param string $url A function or a URL for the results callback
	 * @param string $val A default value (will be knocked out on focus)
	 */
	public function __construct($name, $title, $url = "autocomplete", $val = null) {
		parent::__construct($name, $title, $val);
		if(!stristr($url,'/')) {
			// url was passed as a function name.. use the current controller
			$url = Controller::curr()->Link($url);
		}
		$this->addExtraClass("autocomplete_input");
		$this->addExtraClass("{'url' : '$url'}");
	}
	
	/**
	 * Require the dependencies and render the field
	 * Note: The wrapper div is a hack. Position:relative would not work against
	 * an input field in most browsers. :-(
	 * @return string
	 */
	public function Field() {
		Requirements::javascript(THIRDPARTY_DIR.'/jquery/jquery.js');
		Requirements::javascript(THIRDPARTY_DIR.'/jquery-metadata/jquery.metadata.js');
		Requirements::javascript(THIRDPARTY_DIR.'/jquery-livequery/jquery.livequery.js');

		Requirements::javascript('dataobject_manager/code/autocomplete_field/javascript/autocomplete_field.js');
		Requirements::css('dataobject_manager/code/autocomplete_field/css/autocomplete_field.css');
		return '<div class="autocomplete_holder">'.parent::Field().'<div class="autocomplete_results"></div></div>';
	}	
}