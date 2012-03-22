<?php

class FileDataObjectManager extends DataObjectManager
{
	public function __construct($controller, $name = null, $sourceClass = null, $fileFieldName = null, $fieldList = null, $detailFormFields = null, $sourceFilter = "", $sourceSort = "", $sourceJoin = "") 
	{
		parent::__construct($controller, $name, $sourceClass, $fieldList, $detailFormFields, $souceFilter, $sourceSort, $sourceJoin);
	}

	
	public function setGridLabelField($fieldName) {	}
		

	
	public function setAllowedFileTypes($types = array())
	{
		foreach($types as $type) {
			if(is_array($this->limitFileTypes) && !in_array(strtolower(str_replace(".","",$type)), $this->limitFileTypes))
				// To-do: get user_error working.
				die("<strong>".$this->class . "::setAllowedFileTypes() -- Only files of type " . implode(", ", $this->limitFileTypes) . " are allowed.</strong>");
		}
		$this->allowedFileTypes = $types;
	}
	
	public function getAllowedFileTypes() {	}
	
	public function setUploadLimit($num) { }
	
	public function getUploadLimit() {	}
	
	public function setBrowseButtonText($text){ }
	
	public function getBrowseButtonText() { }
		
	public function allowUploadFolderSelection() { }
	
	public function enableUploadDebugging() { }
	
	public function setDefaultView($type) {	}
	
	public function uploadOnSubmit() {	}
	
}