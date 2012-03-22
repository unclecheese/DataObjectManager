<?php

class DataObjectManager extends FormField
{
	
	protected $grid;

	protected $dataClass;
	
	function __construct($controller, $name = null, $sourceClass = null, $fieldList = null, $detailFormFields = null, $sourceFilter = "", $sourceSort = null, $sourceJoin = "") 
	{
		$title = FormField::name_to_label($name);
		parent::__construct($name, $title, null);
		$this->grid = new GridField($name, $title, $controller->$name(), GridFieldConfig_RecordEditor::create());
		$this->dataClass = $sourceClass;
		if($fieldList) {
			$this->grid->setDisplayFields($fieldList);
		}
		elseif(!singleton($this->dataClass)->stat('summary_fields')) {
			if($db = singleton($this->dataClass)->db()) {
				$list = array ();
				foreach($db as $name => $type) {
					$list[$name] = FormField::name_to_label($name);
				}
				$this->grid->setDisplayFields($list);
			}
		}
	}


	public function setForm($form) {
		$this->grid->setForm($form);
	}

	public function setClickToToggle($bool) {	}
	
	
	public function setSourceFilter($filter) {
	   $this->grid->getList()->where($filter);
	}
	
	
	public function setUseViewAll($bool) {	}
	
	
	public function setPerPageMap($values)  { }	


	public function setPluralTitle($title) {
		$this->grid->setTitle($title);
	}
	
	public function setWideMode($bool) { }


	
	public function setFilter($field, $label, $map, $default = null) {	}

	public function setSingleTitle($title) {  }
	
	public function getColumnWidths() { }


	public function setColumnWidths($widths) {	}
	
	public function setFilterEmptyString($str) {	}
	
	public function addPermission($perm) {	}
	
  	public function removePermission($perm)	{ 	}
 		
	public function setPopupWidth($val)	{	}
	
	public function setConfirmDelete($bool) {	}
	
	public function Field() {
		return $this->FieldHolder();
	}


	public function FieldHolder() {
		return $this->grid->FieldHolder();
	}	



	public function handleAction($actionName, $args, $data) {
		return $this->grid->handleAction($actionName, $args, $data);
	}
	
	function handleRequest(SS_HTTPRequest $request, DataModel $model) {	
		return $this->grid->handleRequest($request, $model);
	}

}

