<?php

class LiveDropdownField extends HiddenField {

	
	public $labelField;
	
	public $sourceClass;
	
	public function __construct($name, $title, $className = "SiteTree", $label = "Title", $val = null, $form = null) {
		parent::__construct($name, $title, $val, $form);
		$this->labelField = $label;
		$this->sourceClass = $className;		
	}

	public function getresults(SS_HTTPRequest $r) {

			$q = Convert::raw2sql($r->requestVar('q'));
			$results = DataObject::get($this->sourceClass,$this->labelField . " LIKE '%{$q}%'");
			if($results) {
				$set = new DataObjectSet();
				foreach($results->toDropdownMap('ID', $this->labelField) as $key => $val) {
					$set->push(new ArrayData(array(
						'Key' => $key,
						'Val' => $val
					)));
				}
			}
			else {
				$set = false;
			}
			
			return $this->customise(array('Results' => $set))->renderWith('LiveDropdownField_results');			

	}
	
	public function Field() {
		$text = "";
		if($this->Value()) {
			if($record = DataObject::get_by_id($this->sourceClass,(int) $this->Value())) {
				$text = $record->{$this->labelField};
			}
		}
		Requirements::javascript(THIRDPARTY_DIR.'/jquery/jquery.js');
		Requirements::javascript(THIRDPARTY_DIR.'/jquery-metadata/jquery.metadata.js');
		Requirements::javascript(THIRDPARTY_DIR.'/jquery-livequery/jquery.livequery.js');

		Requirements::javascript('dataobject_manager/code/autocomplete_field/javascript/autocomplete_field.js');
		Requirements::css('dataobject_manager/code/autocomplete_field/css/autocomplete_field.css');
		return '<div class="field text autocomplete_holder livedropdownfield">
					<label for="'.$this->id().'">'.$this->Title().'</label>
					<div class="middleColumn">
						<input type="text" class="field text autocomplete_input {\'url\' : \''.$this->Link('getresults').'\'}" name="'.$this->Name().'_search" value="'.$text.'" />'.
						parent::Field().
						'<button class="livedropdown_browse">'._t('LiveDropdownField.BROWSE','Browse...').'</button>'.
						'<div class="autocomplete_results"></div>
					</div>
				</div>';
	}	
	

}