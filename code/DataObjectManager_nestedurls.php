<?php

/**
 * A work in progress in preparation for the nestedurls release.
 * Replace DataObjectManager.php with this code to enable nestedurls compliance.
 * Right now, the nestedurls changes are breaking sites on the trunk.
 *

class DataObjectManager extends ComplexTableField
{
	
	protected $template = "DataObjectManager";
	protected $per_page = "10";
	protected $showAll = "0";
	protected $search = "";
	protected $filter = "";
	protected $sort_dir = "DESC";
	protected $sort = "Created";
	protected $filter_map = array();
	protected $filtered_field;
	protected $filter_label = "Filter results";
	protected $filter_empty_string = "";
	protected $column_widths = array();
	public $itemClass = "DataObjectManager_Item";
	public $addTitle;
	public $singleTitle;
	

	public $actions = array(
		'edit' => array(
			'label' => 'Edit',
			'icon' => null,
			'class' => 'popuplink editlink',
		),
		'delete' => array(
			'label' => 'Delete',
			'icon' => null,
			'class' => 'deletelink',
		)
	);
	
	public $popupClass = "DataObjectManager_Popup";
	public $templatePopup = "DataObjectManager_popup";
	
	
	function __construct($controller, $name, $sourceClass, $fieldList = null, $detailFormFields = null, $sourceFilter = "", $sourceSort = "", $sourceJoin = "") 
	{
		if(!class_exists("ComplexTableField_ItemRequest"))
			die("<strong>"._t('DataObjectManager.ERROR','Error')."</strong>: "._t('DataObjectManager.SILVERSTRIPEVERSION','DataObjectManager requires Silverstripe version 2.3 or higher.'));

		parent::__construct($controller, $name, $sourceClass, $fieldList, $detailFormFields, $sourceFilter, $sourceSort, $sourceJoin);
		Requirements::block(THIRDPARTY_DIR . "/greybox/AmiJS.js");
		Requirements::block(THIRDPARTY_DIR . "prototype.js");
		Requirements::block(THIRDPARTY_DIR . "/greybox/greybox.js");
		Requirements::block(SAPPHIRE_DIR . "/javascript/ComplexTableField.js");
		Requirements::block(SAPPHIRE_DIR . "/javascript/TableListField.js");

		Requirements::block(THIRDPARTY_DIR . "/greybox/greybox.css");
		Requirements::block(SAPPHIRE_DIR . "/css/ComplexTableField.css");
		Requirements::css('dataobject_manager/css/dataobject_manager.css');
		Requirements::css('dataobject_manager/css/facebox.css');
		Requirements::javascript('dataobject_manager/javascript/facebox.js');	
		Requirements::javascript('dataobject_manager/javascript/jquery-ui.1.5.3.js');
		Requirements::javascript('dataobject_manager/javascript/dataobject_manager.js');
		Requirements::javascript('dataobject_manager/javascript/tooltip.js');
		
		$this->filter_empty_string = '-- '._t('DataObjectManager.NOFILTER','No filter').' --';

		if(isset($_REQUEST['ctf'][$this->Name()])) {
			$this->per_page = $_REQUEST['ctf'][$this->Name()]['per_page'];
			$this->showAll = $_REQUEST['ctf'][$this->Name()]['showall'];
			$this->search = $_REQUEST['ctf'][$this->Name()]['search'];
			$this->filter = $_REQUEST['ctf'][$this->Name()]['filter'];			
			$this->sort = $_REQUEST['ctf'][$this->Name()]['sort'];
			$this->sort_dir = $_REQUEST['ctf'][$this->Name()]['sort_dir'];
		}
		$this->setPageSize($this->per_page);
		$this->loadSort();
		$this->loadSourceFilter();
	}

	protected function loadSort()
	{
		if($this->ShowAll()) 
			$this->setPageSize(999);
		
		if($this->Sortable() && (!isset($_REQUEST['ctf'][$this->Name()]['sort']) || $_REQUEST['ctf'][$this->Name()]['sort'] == "SortOrder")) {
			$this->sort = "SortOrder";
			$this->sourceSort = "SortOrder ASC";
		}
		elseif(isset($_REQUEST['ctf'][$this->Name()]['sort']))
			$this->sourceSort = $_REQUEST['ctf'][$this->Name()]['sort'] . " " . $this->sort_dir;
	
	}
	
	protected function loadSourceFilter()
	{
		$filter_string = "";
		if(!empty($this->filter)) {
			$break = strpos($this->filter, "_");
			$field = substr($this->filter, 0, $break);
			$value = substr($this->filter, $break+1, strlen($this->filter) - strlen($field));
			$filter_string = $field . "='$value'";
		}	

		$search_string = "";
		if(!empty($this->search)) {
			$search = array();
	        $SNG = singleton($this->sourceClass); 			
			foreach(parent::Headings() as $field) {
				if($SNG->hasField($field->Name))	
					$search[] = "UPPER($field->Name) LIKE '%".strtoupper($this->search)."%'";
			}
			$search_string = "(".implode(" OR ", $search).")";
		}
		$and = (!empty($this->filter) && !empty($this->search)) ? " AND " : "";
		$source_filter = $filter_string.$and.$search_string;
		if(!$this->sourceFilter) $this->sourceFilter = $source_filter;
		else if($this->sourceFilter && !empty($source_filter)) $this->sourceFilter .= " AND " . $source_filter;		
	}
	
	public function handleItem($request) {
		return new DataObjectManager_ItemRequest($this, $request->param('ID'));
	}

	protected function getQueryString($params = array())
	{ 
		$per_page = isset($params['per_page'])? $params['per_page'] : 	$this->per_page;
		$show_all = isset($params['show_all'])? $params['show_all'] : 	$this->showAll;
		$sort 	  = isset($params['sort'])? $params['sort'] 		: 	$this->sort;
		$sort_dir = isset($params['sort_dir'])? $params['sort_dir'] : 	$this->sort_dir;
		$filter   = isset($params['filter'])? $params['filter'] 	: 	$this->filter;
		$search   = isset($params['search'])? $params['search'] 	: 	$this->search;
		return "ctf[{$this->Name()}][per_page]={$per_page}&ctf[{$this->Name()}][showall]={$show_all}&ctf[{$this->Name()}][sort]={$sort}&ctf[{$this->Name()}][sort_dir]={$sort_dir}&ctf[{$this->Name()}][search]={$search}&ctf[{$this->Name()}][filter]={$filter}";
	}

	
	public function Headings()
	{
		$headings = array();
		foreach($this->fieldList as $fieldName => $fieldTitle) {
			if(isset($_REQUEST['ctf'][$this->Name()]['sort_dir'])) 
				$dir = $_REQUEST['ctf'][$this->Name()]['sort_dir'] == "ASC" ? "DESC" : "ASC";
			else 
				$dir = "ASC"; 
			$headings[] = new ArrayData(array(
				"Name" => $fieldName, 
				"Title" => ($this->sourceClass) ? singleton($this->sourceClass)->fieldLabel($fieldTitle) : $fieldTitle,
	      "IsSortable" => singleton($this->sourceClass)->hasField($fieldName),
				"SortLink" => $this->RelativeLink(array(
					'sort_dir' => $dir,
					'sort' => $fieldName
				)),
				"SortDirection" => $dir,
			  "IsSorted" => (isset($_REQUEST['ctf'][$this->Name()]['sort'])) && ($_REQUEST['ctf'][$this->Name()]['sort'] == $fieldName),
				"ColumnWidthCSS" => !empty($this->column_widths) ? sprintf("style='width:%f%%;'",($this->column_widths[$fieldName] - 0.1)) : ""
			));
		}
		return new DataObjectSet($headings);
	}
	
	function saveComplexTableField($data, $form, $params) {
		$className = $this->sourceClass();
		$childData = new $className();
		$form->saveInto($childData);
		$childData->write();
		$form->sessionMessage(sprintf(_t('DataObjectManager.ADDEDNEW','Added new %s successfully'),$this->SingleTitle()), 'good');

		if($form->getFileField()) {
			$form->clearMessage();
			Director::redirect($this->BaseLink().'/item/'.$childData->ID.'/edit');
		}
		else Director::redirectBack();

	}
	
	
	function getCustomFieldsFor($childData) {
		if(is_a($this->detailFormFields,"Fieldset")) 
			$fields = $this->detailFormFields;
		else {
			if(!is_string($this->detailFormFields)) $this->detailFormFields = "getCMSFields";
			$functioncall = $this->detailFormFields;
			if(!$childData->hasMethod($functioncall)) $functioncall = "getCMSFields";
			
			$fields = $childData->$functioncall();
		}
		
		foreach($fields as $field) {
			if($field->class == "CalendarDateField")
				$fields->replaceField($field->Name(), new DatePickerField($field->Name(), $field->Title(), $field->attrValue()));
		}
		return $fields;
	}
	
	function AddForm($childID = null)
	{
		$form = parent::AddForm($childID);
		$actions = new FieldSet();	
		$text = ($field = $form->getFileField()) ? sprintf(_t('DataObjectManager.SAVEANDADD','Save and add %s'), $field->Title()) : _t('DataObjectManager.SAVE','Save');

		$actions->push(
			$saveAction = new FormAction("saveComplexTableField", $text)
		);	
		$saveAction->addExtraClass('save');
		$form->setActions($actions);
		return $form;

		
	}
	
	public function Link($action = null)
	{
		return parent::Link($action)."?".$this->getQueryString();
	}
	
	public function BaseLink($action = null)
	{
		return parent::Link($action);
	}
	
	public function CurrentLink($action = null)
	{
		return $this->Link($action);
	}	
	
	public function RelativeLink($params = array(), $action = null)
	{
		return parent::Link($action)."?".$this->getQueryString($params);
	}	
	public function FirstLink($action = null)
	{
		return parent::FirstLink($action) ? parent::FirstLink($action)."&".$this->getQueryString() : false;
	}
	
	public function PrevLink($action = null)
	{
		return parent::PrevLink($action) ? parent::PrevLink($action)."&".$this->getQueryString() : false;
	}
	
	public function NextLink($action = null)
	{
		return parent::NextLink($action) ? parent::NextLink($action)."&".$this->getQueryString() : false;
	}
	
	public function LastLink($action = null)
	{
		return parent::LastLink($action) ? parent::LastLink($action)."&".$this->getQueryString() : false;
	}
	
	public function ShowAllLink($action = null)
	{
		return $this->RelativeLink(array('show_all' => '1'),$action);
	}
	
	public function PaginatedLink($action = null)
	{
		return $this->RelativeLink(array('show_all' => '0'),$action);
	}

	public function AddLink($action = null) {
		return $this->BaseLink($action) . '/add';
	}
	
	
		
	public function ShowAll()
	{
		return $this->showAll == "1";
	}
	
	public function Paginated()
	{
		return $this->showAll == "0";
	}
		
	public function Sortable()
	{
		return SortableDataObject::is_sortable_class($this->sourceClass());
	}
	
	public function setFilter($field, $label, $map)
	{
		if(is_array($map)) {
			$this->filter_map = $map;
			$this->filtered_field = $field;
			$this->filter_label = $label;
		}
	}

	public function HasFilter()
	{
		return !empty($this->filter_map);
	}
	
	public function FilterDropdown()
	{
		$map = $this->filter_empty_string ? array($this->RelativeLink(array('filter' => '')) => $this->filter_empty_string) : array();
		foreach($this->filter_map as $k => $v) {
			$map[$this->RelativeLink(array('filter' => $this->filtered_field.'_'.$k))] = $v;
		}
		$value = !empty($this->filter) ? $this->RelativeLink(array('filter' => $this->filter)) : null;
		$dropdown = new DropdownField('Filter',$this->filter_label . " (<a href='#' class='refresh'>"._t('DataObjectManager.REFRESH','refresh')."</a>)", $map, $value);
		return $dropdown->FieldHolder();
	}
	
	public function PerPageDropdown()
	{
		$map = array();
		for($i=10;$i<=50;$i+=10) $map[$this->RelativeLink(array('per_page' => $i))] = $i;
		$value = !empty($this->per_page) ? $this->RelativeLink(array('per_page' => $this->per_page)) : null;
		return new FieldGroup(
			new LabelField('show', _t('DataObjectManager.PERPAGESHOW','Show').' '),
			new DropdownField('PerPage','',$map, $value),
			new LabelField('results', ' '._t('DataObjectManager.PERPAGERESULTS','results per page'))

		);
	}
	public function SearchValue()
	{
		return !empty($this->search) ? $this->search : false;
	}
	
	public function AddTitle()
	{
		return $this->addTitle ? $this->addTitle : $this->Title();
	}
	
	public function SingleTitle()
	{
		return $this->singleTitle ? $this->singleTitle : $this->AddTitle();
	}
	
	public function setAddTitle($title)
	{
		$this->addTitle = $title;
	}
	
	public function setSingleTitle($title)
	{
		$this->singleTitle = $title;
	}
	
	public function getColumnWidths()
	{
		return $this->column_widths;
	}
	
	public function setColumnWidths($widths)
	{
		if(is_array($widths)) {
			$total = 0;
			foreach($widths as $name => $value)	$total += $value;
			if($total != 100) 
				die('<strong>DataObjectManager::setColumnWidths()</strong>:' . sprintf(_t('DataObjectManager.TOTALNOT100','Column widths must total 100 and not %s'), $total));
			else
				$this->column_widths = $widths;
		}
	}
	
	public function setFilterEmptyString($str)
	{
		$this->filter_empty_string = $str;
	}

}

class DataObjectManager_Item extends ComplexTableField_Item {
	function __construct(DataObject $item, DataObjectManager $parent, $start) 
	{
		parent::__construct($item, $parent, $start);
	}
	
	function Link($action = null) {
		return $this->parent->BaseLink(Controller::join_links('item', $this->item->ID, $action));
	}
	
	function Fields() {
		$fields = parent::Fields();
		$widths = $this->parent->getColumnWidths();
		if(!empty($widths)) {
			foreach($fields as $field) {
				$field->ColumnWidthCSS = sprintf("style='width:%f%%;'",($widths[$field->Name] - 0.1));
			}
		}
		return $fields;		
	}
	
}

class DataObjectManager_Controller extends Controller
{
	function dosort()
	{
		if(!empty($_POST) && is_array($_POST) && isset($this->urlParams['ID'])) {
			$className = $this->urlParams['ID'];
			foreach($_POST as $group => $map) {
				if(substr($group, 0, 7) == "record-") {
 					foreach($map as $sort => $id) {
 						$obj = DataObject::get_by_id($className, $id);
 						$obj->SortOrder = $sort;
 						$obj->write();
 					}
				}
			}
		}
	}

}


class DataObjectManager_Popup extends Form {
	protected $sourceClass;
	protected $dataObject;

	function __construct($controller, $name, $fields, $validator, $readonly, $dataObject) {
		$this->dataObject = $dataObject;
		Requirements::clear();
		Requirements::block('/jsparty/behaviour.js');
		Requirements::block('sapphire/javascript/Validator.js');
		Requirements::block('jsparty/prototype.js');
		Requirements::block('jsparty/behavior.js');
		Requirements::block('jsparty/jquery/jquery.js');
		Requirements::clear('jsparty/behavior.js');

		Requirements::block('sapphire/javascript/i18n.js');
		Requirements::block('assets/base.js');
		Requirements::block('sapphire/javascript/lang/en_US.js');
		Requirements::css(SAPPHIRE_DIR . '/css/Form.css');
		Requirements::css(CMS_DIR . '/css/typography.css');
		Requirements::css(CMS_DIR . '/css/cms_right.css');
		Requirements::css('dataobject_manager/css/dataobject_manager.css');
 		if($this->dataObject->hasMethod('getRequirementsForPopup')) {
			$this->dataObject->getRequirementsForPopup();
		}
		
		Requirements::javascript('dataobject_manager/javascript/jquery.1.3.js');
		
		// File iframe fields force horizontal scrollbars in the popup. Not cool.
		// Override the close popup method.
		Requirements::customScript("
			jQuery(function() {
				jQuery('iframe').css({'width':'433px'});				
			});
		");
		
		$actions = new FieldSet();	
		if(!$readonly) {
			$actions->push(
				$saveAction = new FormAction("saveComplexTableField", _t('DataObjectManager.SAVE','Save'))

			);	
			$saveAction->addExtraClass('save');
		}
		
		parent::__construct($controller, $name, $fields, $actions, $validator);
		
		$this->unsetValidator();
	}

	function FieldHolder() {
		return $this->renderWith('ComplexTableField_Form');
	}
	
	public function getFileField()
	{
		foreach($this->Fields() as $field) {
			if($field instanceof FileIFrameField || $field instanceof ImageField)
				return $field;
		}
		
		return false;
	}
	
}



class DataObjectManager_ItemRequest extends ComplexTableField_ItemRequest 
{
	function __construct($ctf, $itemID) 
	{
		parent::__construct($ctf, $itemID);
	}
	
	function Link($action = null) 
	{
		return $this->ctf->BaseLink(Controller::join_links('item', $this->itemID, $action));
	}

	function saveComplexTableField($data, $form, $request) {
		$form->saveInto($this->dataObj());
		$this->dataObj()->write();
		$form->sessionMessage(sprintf(_t('DataObjectManager.SAVED','Saved %s successfully'),$this->ctf->SingleTitle()), 'good');

		Director::redirectBack();
	}

}




*/
?>