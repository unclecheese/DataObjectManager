<?php

class DataObjectManager extends ComplexTableField
{
	
	protected static $allow_assets_override = true;
	protected static $allow_css_override = false;
	public static $popup_width = 640;
	protected static $confirm_delete = true;
	
	protected $template = "DataObjectManager";
	protected $start = "0";
	protected $per_page = "10";
	protected $showAll = "0";
	protected $search = "";
	protected $filter = "";
	protected $sort_dir = null;
	protected $sort = null;
	protected $filter_map = array();
	protected $filtered_field;
	protected $filter_label = "Filter results";
	protected $filter_empty_string = "";
	protected $column_widths = array();
	protected $per_page_map = array('10','20','30','40','50');
	protected $use_view_all = true;
	protected $popupWidth;
	protected $confirmDelete;
	protected $hasCustomSourceID;
	public $itemClass = "DataObjectManager_Item";
	public $addTitle;
	public $singleTitle;
	public $hasNested = false;
	public $isNested = false;
	
	


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

	static $url_handlers = array(
		'duplicate/$ID' => 'handleDuplicate'
	);
	
	
	public $popupClass = "DataObjectManager_Popup";
	public $templatePopup = "DataObjectManager_popup";
	
	public static function allow_assets_override($bool)
	{
    if($bool) {
      DataObject::add_extension("Folder","AssetManagerFolder");
      SortableDataObject::add_sortable_class("File");
    }
    else
      DataObject::remove_extension("Folder","AssetManagerFolder");
	}
	
	public static function allow_css_override($bool)
	{
	   self::$allow_css_override = $bool;
	}
	
	public static function set_popup_width($width)
	{
	   self::$popup_width = $width;
	}
	
	public static function set_confirm_delete($bool)
	{
	   self::$confirm_delete = $bool;
	}
	
	function __construct($controller, $name = null, $sourceClass = null, $fieldList = null, $detailFormFields = null, $sourceFilter = "", $sourceSort = null, $sourceJoin = "") 
	{
		if(!class_exists("ComplexTableField_ItemRequest"))
			die("<strong>"._t('DataObjectManager.ERROR','Error')."</strong>: "._t('DataObjectManager.SILVERSTRIPEVERSION','DataObjectManager requires Silverstripe version 2.3 or higher.'));
    
    // If no name is given, search the has_many for the first relation.
    if($name === null && $sourceClass === null) {
      if($has_manys = $controller->stat('has_many')) {
        foreach($has_manys as $relation => $value) {
          $name = $relation;
          $sourceClass = $value;
          break;
        }
      }
    }
    $SNG = singleton($sourceClass);
    

    if($fieldList === null) {
      $diff = array_diff($SNG->summaryFields(),singleton('DataObject')->summaryFields());
      if(!empty($diff)) {
        $fieldList = $SNG->summaryFields();
      }
      else if($db = $SNG->db()) {
        $fieldList = array();
        foreach($db as $field => $type) {
          if($field != "SortOrder")
            $fieldList[$field] = DOMUtil::readable_class($field);
        }
      }
    }
		parent::__construct($controller, $name, $sourceClass, $fieldList, $detailFormFields, $sourceFilter, $sourceSort, $sourceJoin);
		Requirements::css('dataobject_manager/css/dataobject_manager.css');
		Requirements::css('dataobject_manager/css/facebox.css');
		if(self::$allow_css_override)
  			Requirements::css('dataobject_manager/css/dataobjectmanager_override.css');
		Requirements::javascript(THIRDPARTY_DIR.'/jquery-livequery/jquery.livequery.js');
		Requirements::javascript('dataobject_manager/javascript/facebox.js');	
		Requirements::javascript('dataobject_manager/javascript/dom_jquery_ui.js');
		Requirements::javascript('dataobject_manager/javascript/tooltip.js');
		Requirements::javascript('dataobject_manager/javascript/dataobject_manager.js');
		
		$this->filter_empty_string = '-- '._t('DataObjectManager.NOFILTER','No filter').' --';

		if($this->sourceSort) {
  		$parts = explode(" " , $this->sourceSort);
		  if(is_array($parts) && sizeof($parts) == 2) {
  		  list($field,$dir) = $parts;
  		  $this->sort = trim($field);
  		  $this->sort_dir = trim($dir);
		  }
		  else {
		    $this->sort = $this->sourceSort;
		    $this->sort_dir = "ASC";
		  }
		}

		if(isset($_REQUEST['ctf'][$this->Name()])) {
			$this->start = $_REQUEST['ctf'][$this->Name()]['start'];
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

		$fields = $this->getRawDetailFields(singleton($this->sourceClass()));
		foreach($fields as $field) {
		  if($field instanceof DataObjectManager && !($field->controller instanceof SiteTree)) {
		    $this->hasNested = true;
		    $this->setPopupWidth(850);
		  }
		}

	}
	
	public function setSourceFilter($filter)
	{
	   $this->sourceFilter = $filter;
	}
	
	public function setUseViewAll($bool)
	{
	   $this->use_view_all = $bool;
	}
	
	public function setPerPageMap($values)
	{
	   $this->per_page_map = $values;
	}
	
	public function setPluralTitle($title)
	{
		$this->pluralTitle = $title;
	}
	
	public function setWideMode($bool)
	{
	  $this->hasNested = $bool;
	}

	public function PluralTitle()
	{
		return $this->pluralTitle ? $this->pluralTitle : $this->AddTitle()."s";
	}
	
		
	protected function loadSort()
	{
		if($this->ShowAll()) 
			$this->setPageSize(999);
		
		if($this->Sortable() && (!isset($_REQUEST['ctf'][$this->Name()]['sort']) || $_REQUEST['ctf'][$this->Name()]['sort'] == "SortOrder")) {
			$this->sort = "SortOrder";
			$this->sourceSort = "\"SortOrder\" ASC";
		}
		elseif(isset($_REQUEST['ctf'][$this->Name()]['sort']) && !empty($_REQUEST['ctf'][$this->Name()]['sort'])) {
			$this->sourceSort = "\"" . $_REQUEST['ctf'][$this->Name()]['sort'] . "\" " . $this->sort_dir;
		}
		elseif($sort = singleton($this->sourceClass())->stat('default_sort')) {
			$this->sourceSort = $sort;
		}
		else {
			$this->sourceSort = "Created DESC";
		}

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
				if($SNG->hasDatabaseField($field->Name))	
					$search[] = "UPPER($field->Name) LIKE '%".Convert::raw2sql(strtoupper($this->search))."%'";
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

	public function getQueryString($params = array())
	{ 
		$start    = isset($params['start'])? $params['start']       : 	$this->start;
		$per_page = isset($params['per_page'])? $params['per_page'] : 	$this->per_page;
		$show_all = isset($params['show_all'])? $params['show_all'] : 	$this->showAll;
		$sort 	  = isset($params['sort'])? $params['sort'] 		: 	$this->sort;
		$sort_dir = isset($params['sort_dir'])? $params['sort_dir'] :	$this->sort_dir;
		$filter   = isset($params['filter'])? $params['filter'] 	: 	$this->filter;
		$search   = isset($params['search'])? $params['search'] 	: 	$this->search;
		return "ctf[{$this->Name()}][start]={$start}&ctf[{$this->Name()}][per_page]={$per_page}&ctf[{$this->Name()}][showall]={$show_all}&ctf[{$this->Name()}][sort]={$sort}&ctf[{$this->Name()}][sort_dir]={$sort_dir}&ctf[{$this->Name()}][search]={$search}&ctf[{$this->Name()}][filter]={$filter}";
	}
	
	public function getSetting($setting)
	{
	   if($this->$setting) {
	     return $this->$setting;
	   }
	   return Object::get_static($this->class,DOMUtil::to_underscore($setting));
	}
	
	function FieldHolder()
	{
		if(!$this->controller->ID && $this->isNested)
			return $this->renderWith('DataObjectManager_holder');
		return parent::FieldHolder();
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
	      "IsSortable" => singleton($this->sourceClass)->hasDatabaseField($fieldName),
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
		try {
			$childData->write();
		} 
		catch(ValidationException $e) {
			$form->sessionMessage($e->getResult()->message(), 'bad');
			return Director::redirectBack();
		}		
		if($childData->many_many()) {
		  $form->saveInto($childData);
		  $childData->write();
		}		
		$form->sessionMessage(sprintf(_t('DataObjectManager.ADDEDNEW','Added new %s successfully'),$this->SingleTitle()), 'good');

		if($form->getFileFields() || $form->getNestedDOMs()) {
			$form->clearMessage();
      	Director::redirect(Controller::join_links($this->BaseLink(),'item', $childData->ID, 'edit'));		

    }
		else Director::redirectBack();

	}
	
	function setSourceID($val) { 
		if (is_numeric($val)) { 
			$this->sourceID = $val; 
			$this->hasCustomSourceID = true;
		} 
	}	
	
	function sourceID() {
		if ($this->hasCustomSourceID) {
			return $this->sourceID;
		}
	
		if($this->isNested)
			return $this->controller->ID;				
		$idField = $this->form->dataFieldByName('ID'); 
		return ($idField && is_numeric($idField->Value())) ? $idField->Value() : (isset($_REQUEST['ctf']['ID']) ? $_REQUEST['ctf']['ID'] : null); 
 	} 
	
	
  protected function getRawDetailFields($childData)
  {
		if(is_a($this->detailFormFields,"Fieldset")) 
			$fields = $this->detailFormFields;
		else {
			if(!is_string($this->detailFormFields)) $this->detailFormFields = "getCMSFields";
			$functioncall = $this->detailFormFields;
			if(!$childData->hasMethod($functioncall)) $functioncall = "getCMSFields";
			
			$fields = $childData->$functioncall();
		}
    return $fields;  
  }
	
	public function getCustomFieldsFor($childData) {
		$fields = $this->getRawDetailFields($childData);
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
		$titles = array();
		if($files = $form->getFileFields()) {
			foreach($files as $field)	$titles[] = DOMUtil::readable_class($field->Title());
		}
		if($doms = $form->getNestedDOMs())
			foreach($doms as $field) $titles[] = $field->PluralTitle(); 
    if(empty($titles))
      $text = _t('DataObjectManager.SAVE','Save');
    elseif(sizeof($titles) > 3) {
      $first_three = array_slice($titles,0,3);
      $remaining = sizeof(array_slice($titles, 4));
      $text = sprintf(_t('DataObjectManager.SAVEANDADD','Save and add %s'), implode(', ',$first_three));
      $text .= ", " . sprintf(_t('DataObjectManager.ANDOTHERCOMPONENTS','and %d other components'),$remaining);
    }
    else
      $text = sprintf(_t('DataObjectManager.SAVEANDADD','Save and add %s'), DOMUtil::readable_list($titles));

		$actions->push(
			$saveAction = new FormAction("saveComplexTableField", $text)
		);	
		$saveAction->addExtraClass('save');
		$form->setActions($actions);
		$form->Fields()->insertFirst(new LiteralField('open','<div id="field-holder"><div id="fade"></div>'));
		$o = $form->Fields()->Last();
		$form->Fields()->insertAfter(new LiteralField('close','</div>'),$o->Name());

		return $form;
	}	
	
	public function Link($action = null)
	{
    return Controller::join_links(parent::Link($action),'?'.$this->getQueryString());
	}
	
	public function BaseLink()
	{
 		return parent::Link();
	}
	
	public function CurrentLink()
	{
		return $this->Link();
	}	
	
	public function RelativeLink($params = array())
	{
    return Controller::join_links(parent::Link(),'?'.$this->getQueryString($params));
	}	
	public function FirstLink()
	{
		return parent::FirstLink() ? $this->RelativeLink(array('start' => '0')) : false;
	}
	
	public function PrevLink()
	{
		$start = ($this->start - $this->pageSize < 0)  ? 0 : $this->start - $this->pageSize;
		return parent::PrevLink() ? $this->RelativeLink(array('start' => $start)) : false;
	}
	
	public function NextLink()
	{
		$currentStart = isset($_REQUEST['ctf'][$this->Name()]['start']) ? $_REQUEST['ctf'][$this->Name()]['start'] : 0;
		$start = ($currentStart + $this->pageSize < $this->TotalCount()) ? $currentStart + $this->pageSize : $this->TotalCount() % $this->pageSize > 0;
		return parent::NextLink() ? $this->RelativeLink(array('start' => $start)) : false;
	}
	
	public function LastLink()
	{
		$pageSize = ($this->TotalCount() % $this->pageSize > 0) ? $this->TotalCount() % $this->pageSize : $this->pageSize;
		$start = $this->TotalCount() - $pageSize;
		return parent::LastLink() ? $this->RelativeLink(array('start' => $start)) : false;
	}
	
	public function ShowAllLink()
	{
		return $this->RelativeLink(array('show_all' => '1'));
	}
	
	public function PaginatedLink()
	{
		return $this->RelativeLink(array('show_all' => '0'));
	}

	public function AddLink() {
	    return Controller::join_links($this->BaseLink(), 'add');
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
	
	public function setFilter($field, $label, $map, $default = null)
	{
		if(is_array($map)) {
			$this->filter_map = $map;
			$this->filtered_field = $field;
			$this->filter_label = $label;
		}
		if($default) {
		  $this->filter = $this->filtered_field.'_'.$default;
		  $this->loadSourceFilter();
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
		foreach($this->per_page_map as $num) $map[$this->RelativeLink(array('per_page' => $num))] = $num;
		if($this->use_view_all)
		  $map[$this->RelativeLink(array('per_page' => '9999'))] = _t('DataObjectManager.ALL','All');
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
		return $this->addTitle ? $this->addTitle : DOMUtil::readable_class($this->Title());
	}
	
	public function SingleTitle()
	{
		return $this->singleTitle ? $this->singleTitle : DOMUtil::readable_class($this->AddTitle());
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
	
	public function addPermission($perm)
	{
		if(!in_array($perm,$this->permissions))
			$this->permissions[] = $perm;
	}
	
  public function removePermission($perm)
 	{
		$key = array_search($perm,$this->permissions);
		if($key !== false)
 			unset($this->permissions[$key]);
 	}
 	
	public function NestedType()
	{
	   if($this->hasNested)
	     return "hasNested";
	   else if($this->isNested)
	     return "isNested";
	   else
	     return "";
	}
	
	public function handleDuplicate($request)
	{
		return new DataObjectManager_ItemRequest($this,$request->param('ID'));
	}
	
	public function setPopupWidth($val)
	{
	   $this->popupWidth = $val;
	}
	
	public function setConfirmDelete($bool)
	{
	   $this->confirmDelete = $bool;
	}
	
	public function PopupWidth()
	{
	   return $this->popupWidth ? $this->popupWidth : self::$popup_width;
	}
	
	public function ConfirmDelete()
	{
	   return $this->getSetting('confirmDelete');
	}
	
	

}

class DataObjectManager_Item extends ComplexTableField_Item {
	function __construct(DataObject $item, DataObjectManager $parent) 
	{
		parent::__construct($item, $parent);
	}
	
	function Link() {
    return Controller::join_links($this->parent->BaseLink(), 'item', $this->item->ID);
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
	
	public function CanViewOrEdit()
	{
		return $this->parent->Can('view') || $this->parent->Can('edit');
	}
	
	public function ViewOrEdit()
	{
		if($this->CanViewOrEdit())
			return $this->parent->Can('edit') ? "edit" : "view";
		return false;
	}
	
	public function ViewOrEdit_i18n()
	{
	  if($res = $this->ViewOrEdit()) {
	     return ($res == "edit") ? _t('DataObjectManager.EDIT','Edit') : _t('DataObjectManager.VIEW','View');
	  }
	  return null;
	}
	
	public function EditLink()
	{
	 	return Controller::join_links($this->Link(), "edit","?".$this->parent->getQueryString());
	}

	public function DuplicateLink()
	{
    return Controller::join_links($this->Link(), "duplicate");
	}

	public function CustomActions()
	{
		if($this->item->hasMethod('customDOMActions'))
			return $this->item->customDOMActions();
		return false;
	}
	
	public function PopupWidth()
	{
	   return $this->parent->PopupWidth();
	}
	
	public function Actions()
	{
	   $actions = new DataObjectSet();
	   foreach($this->parent->permissions as $perm) {
	     $action = false;
	     switch($perm) {
	       case "edit":
	       case "view":
	         $actions->push(new DataObjectManagerAction(
	           $this->ViewOrEdit_i18n(),
	           $this->EditLink(),
	           "popup",
	           "dataobject_manager/images/page_white_{$this->ViewOrEdit()}.png",
	           "editlink"	,
	           $this->parent->PopupWidth()           
	         ));
	       break;
	       	       	       
	       case "delete":
	         $actions->push(new DataObjectManagerAction(
	           _t('DataObjectManager.DELETE','Delete'),
	           $this->DeleteLink(),
	           "delete",
	           "dataobject_manager/images/trash.gif",
	           null,
	           $this->parent->getSetting('confirmDelete') ? "confirm" : null
	         ));
	       break;
	       
	       case "duplicate":
	         $actions->push(new DataObjectManagerAction(
	           _t('DataObjectManager.DUPLICATE','Duplicate'),
	           $this->DuplicateLink(),
	           "popup",
	           "dataobject_manager/images/page_copy.png",
	           null,
	           400
	         ));
	       break;
	     }
	   }
	   if($custom = $this->CustomActions()) {
	     if($custom instanceof DataObjectSet)
	       $actions->merge($custom);
	     else
	       $actions->push($custom);
	   }
	   return $actions;
	}
}

class DataObjectManager_Controller extends Controller
{
	 public function dosort()
	 {
	    if(!empty($_POST) && is_array($_POST) && isset($this->urlParams['ID'])) {
	      $className = $this->urlParams['ID'];
	      if(stristr($className,"-") !== false) {
	       list($ownerClass, $className) = explode("-",$className);
	      }
	      $many_many = ((is_numeric($this->urlParams['OtherID'])) && SortableDataObject::is_sortable_many_many($className));
	      foreach($_POST as $group => $map) {
	        if(substr($group, 0, 7) == "record-") {
	          if($many_many) {
	            $controllerID = $this->urlParams['OtherID'];          
	            $candidates = singleton($ownerClass)->many_many();
	            if(is_array($candidates)) {
	              foreach($candidates as $name => $class)
	                if($class == $className) {
	                  $relationName = $name;
	                  break;
	                }
	            }
	            if(!isset($relationName)) return false;
	            list($parentClass, $componentClass, $parentField, $componentField, $table) = singleton($ownerClass)->many_many($relationName);            
	            foreach($map as $sort => $id)
	              DB::query("UPDATE \"$table\" SET \"SortOrder\" = $sort WHERE \"{$className}ID\" = $id AND \"{$ownerClass}ID\" = $controllerID");
	          }
	          else {
	            foreach($map as $sort => $id) {
	              $obj = DataObject::get_by_id($className, $id);
	              $obj->SortOrder = $sort;
	              $obj->write();
	            }           
	          }
	          break;
	        }
	      }
	    }
	}
	public function i18n_js()
	{
	   return Convert::array2json(array(
	     'delete_confirm' => _t('DataObjectManager.CONFIRMDELETE','Delete?')
	   ));
	}
}



class DataObjectManager_Popup extends Form {
	protected $sourceClass;
	protected $dataObject;
	public $NestedController = false;

	function __construct($controller, $name, $fields, $validator, $readonly, $dataObject) {
		$this->dataObject = $dataObject;
		Requirements::clear();
		Requirements::clear_combined_files();
	    Requirements::javascript(THIRDPARTY_DIR.'/jquery/jquery.js');
		Requirements::javascript(THIRDPARTY_DIR.'/jquery-livequery/jquery.livequery.js');    
		Requirements::block(THIRDPARTY_DIR.'/behaviour.js');
		Requirements::block(SAPPHIRE_DIR.'/javascript/Validator.js');
		Requirements::block(THIRDPARTY_DIR.'/prototype.js');
		Requirements::clear(THIRDPARTY_DIR.'/behavior.js');
		Requirements::block(THIRDPARTY_DIR.'/behavior.js');
		Requirements::block(SAPPHIRE_DIR.'/javascript/ComplexTableField.js');
		Requirements::block(SAPPHIRE_DIR.'/javascript/TableListField.js');
		Requirements::block(THIRDPARTY_DIR.'/greybox/greybox.js');
		Requirements::block(THIRDPARTY_DIR.'/greybox/AmiJS.js');
		Requirements::block(THIRDPARTY_DIR.'/greybox/greybox.css');
		Requirements::block(SAPPHIRE_DIR.'/css/TableListField.css');
		Requirements::block(SAPPHIRE_DIR.'/css/ComplexTableField.css');
		Requirements::block(ASSETS_DIR.'/leftandmain.js');
		Requirements::block(ASSETS_DIR.'/base.js');
		Requirements::block(SAPPHIRE_DIR.'/javascript/lang/en_US.js');
		Requirements::css(SAPPHIRE_DIR . '/css/Form.css');
		Requirements::css(CMS_DIR . '/css/typography.css');
		Requirements::css(CMS_DIR . '/css/cms_right.css');
	    Requirements::css('dataobject_manager/css/dataobject_manager.css');

 		if($this->dataObject->hasMethod('getRequirementsForPopup')) {
			$this->dataObject->getRequirementsForPopup();
		}
		Requirements::javascript('dataobject_manager/javascript/dataobjectmanager_popup.js');
		
		
		$actions = new FieldSet();	
		if(!$readonly) {
			$actions->push(
				$saveAction = new FormAction("saveComplexTableField", _t('DataObjectManager.SAVE','Save'))

			);	
			$saveAction->addExtraClass('save');
		}
		
		parent::__construct($controller, $name, $fields, $actions, $validator);
	    if ($this->validator instanceof Validator) {
        	$this->validator->setJavascriptValidationHandler('none');
		} 
		else {
        	$this->unsetValidator();
      	}

		
	  if($this->getNestedDOMs()) {
			Requirements::javascript(THIRDPARTY_DIR.'/jquery-livequery/jquery.livequery.js');
			Requirements::javascript('dataobject_manager/javascript/dom_jquery_ui.js');
	  		Requirements::javascript('dataobject_manager/javascript/tooltip.js');    
			Requirements::javascript('dataobject_manager/javascript/dataobject_manager.js');
  	}
    $this->NestedController = $this->controller->isNested;
	}

	function FieldHolder() {
		return $this->renderWith('ComplexTableField_Form');
	}
	
	public function getFileFields()
	{
		$file_fields = array();
		foreach($this->Fields() as $field) {
			if($field instanceof FileIFrameField || $field instanceof ImageField)
				$file_fields[] = $field;
		}
		return !empty($file_fields)? $file_fields : false;	
	}
	
	public function getNestedDOMs()
	{
		$dom_fields = array();
		foreach($this->Fields()->dataFields() as $field) {
			if($field instanceof DataObjectManager) {
			  $field->isNested = true;
				$dom_fields[] = $field;
		  }
		  elseif($field instanceof CompositeField) {
		  	if($children = $field->children) {
		  		foreach($children as $child) {
		  			if($child instanceof DataObjectManager) {
		  				$child->isNested = true;
		  				$dom_fields[] = $child;
		  			}
		  		}
		  	}
		  }
		}
		return !empty($dom_fields)? $dom_fields : false;		
	}
	
	
}



class DataObjectManager_ItemRequest extends ComplexTableField_ItemRequest 
{
	public $isNested = false;
	protected $itemList;
	protected $currentIndex;
	
	function __construct($ctf, $itemID) 
	{
		parent::__construct($ctf, $itemID);
		$this->isNested = $this->ctf->isNested;
		if($this->ctf->Items()) {
  	  $this->itemList = $this->ctf->Items()->column();
  	  $this->currentIndex = array_search($this->itemID,$this->itemList);
    }
	}

	function Link() 
	{
    return Controller::join_links($this->ctf->BaseLink() , 'item', $this->itemID);	
  }

	function saveComplexTableField($data, $form, $request) {
		$dataObject = $this->dataObj();
		$form->saveInto($dataObject);
		try {
			$dataObject->write();
		} 
		catch(ValidationException $e) {
			$form->sessionMessage($e->getResult()->message(), 'bad');
			return Director::redirectBack();
		}

		// Save the many many relationship if it's available
		if(isset($data['ctf']['manyManyRelation'])) {
			$parentRecord = DataObject::get_by_id($data['ctf']['parentClass'], (int) $data['ctf']['sourceID']);
			$relationName = $data['ctf']['manyManyRelation'];
			$componentSet = $parentRecord->getManyManyComponents($relationName);
			$componentSet->add($dataObject);
		}
		
		$form->sessionMessage(sprintf(_t('DataObjectManager.SAVED','Saved %s successfully'),$this->ctf->SingleTitle()), 'good');

		Director::redirectBack();
	}
	
	function DetailForm($childID = null)
	{
		$form = parent::DetailForm($childID);
		$form->Fields()->insertFirst(new LiteralField('open','<div id="field-holder"><div id="fade"></div>'));
		$o = $form->Fields()->Last();
		$form->Fields()->insertAfter(new LiteralField('close','</div>'),$o->Name());
		if(!$this->ctf->Can('edit')) {
			$form->makeReadonly();
			$form->setActions(null);
		}
		return $form;
	}
	
	function edit() {
		if(!$this->ctf->Can('view') && !$this->ctf->Can('edit'))
			return false;

		$this->methodName = "edit";

		echo $this->renderWith($this->ctf->templatePopup);
	}
	
	public function duplicate()
	{
		if(!$this->ctf->Can('duplicate'))
			return false;
		$this->methodName = "duplicate";
		
		echo $this->renderWith(array('DataObjectManager_duplicate'));
	}
	
	public function DuplicateForm()
	{
		return new Form(
			$this,
			"DuplicateForm",
			new FieldSet(
				new FieldGroup(
					new LabelField('copy',_t('DataObjectManager.CREATE','Create ')),
					new NumericField('Count','','1'),
					new LabelField('times',sprintf(_t('DataObjectManager.COPIESOFOBJECT',' copies of this %s'),$this->ctf->SingleTitle()))
				),
				new CheckboxField('Relations',_t('DataObjectManager.INCLUDERELATIONS','Include related objects'))
			),
			new FieldSet(
				new FormAction('doDuplicate',_t('DataObjectManager.DUPLICATE','Duplicate'))
			)
		);
	}
	
	public function doDuplicate($data,$form)
	{
		if($obj = $this->dataObj()) {
			for($i = 0;$i < $data['Count'];$i++) {
				$new = $obj->duplicate();
				if(isset($data['Relations']) && $data['Relations'] == "1") {
					if($has_manys = $obj->has_many()) {
						foreach($has_manys as $name => $class) {
							// get the owner relation name
							if($has_ones = singleton($class)->has_one()) {
								if($ownerRelation = array_search($this->ctf->SourceClass(),$has_ones)) {
									$ownerID = $ownerRelation."ID";
									if($related_objects = $obj->$name()) {
										foreach($related_objects as $related_obj) {
											$o = $related_obj->duplicate(false);
											$o->$ownerID = $new->ID;	
											$o->write();
										}
									}
								}
								else
									die(sprintf(_t('DataObjectManager.COULDNOTFINDRELATION','Could not find owner relation for class %s'),$this->ctf->SourceClass()));
							}
							else
									die(sprintf(_t('DataObjectManager.COULDNOTFINDRELATION','Could not find owner relation for class %s'),$this->ctf->SourceClass()));
						}
					}
					if($many_manys = $obj->many_many()) {
						foreach($many_manys as $name => $class) {
							if($obj->$name()) {
								$new->$name()->setByIdList($obj->$name()->column());
							}
						}
						$new->write();
					}
				}				
			}
			$ret = "$i " . _t('DataObjectManager.DUPLICATESCREATED','duplicate(s) created');
			if(isset($data['Relations']) && $data['Relations'] == "1") $ret .= ", " . _t('DataObjectManager.WITHRELATIONS','with relations included');
			$form->sessionMessage($ret,'good');
		}
		else
			$form->sessionMessage(_t('DataObjectManager.ERRORDUPLICATING','There was an error duplicating the object.'),'bad');
		Director::redirectBack();
	}
	
		
	protected function getPrevID()
	{
	  return $this->itemList[$this->currentIndex - 1];
	}
	
	protected function getNextID()
	{
	  return $this->itemList[$this->currentIndex + 1];
	}


	function NextRecordLink()
	{
		if(!$this->itemList || $this->currentIndex == sizeof($this->itemList)-1) return false;
		return Controller::join_links($this->ctf->BaseLink() , 'item/' . $this->getNextID().'/edit',"?".$this->ctf->getQueryString());
	}
	
	function PrevRecordLink()
	{
		if(!$this->itemList || $this->currentIndex == 0) return false;
		return Controller::join_links($this->ctf->BaseLink() , 'item/' . $this->getPrevID().'/edit',"?".$this->ctf->getQueryString());
	}
			
	
	function HasPagination()
	{
	 return $this->NextRecordLink() || $this->PrevRecordLink();
	}
	
	function HasDuplicate()
	{
		return $this->ctf->Can('duplicate');
	}
	
	function SingleTitle()
	{
		return $this->ctf->SingleTitle();
	}
	
	function DuplicateLink()
	{
		return Controller::join_links($this->ctf->BaseLink(),'duplicate'.$this->itemID);
	}
	
	function HasRelated()
	{
		$has_many = singleton($this->ctf->SourceClass())->has_many();
		return is_array($has_many) && !empty($has_many);
	}
	
}

class DataObjectManagerAction extends ViewableData
{
	static $behaviour_to_js = array (
		'popup' => 'popup-button',
		'delete' => 'delete-link',
		'refresh' => 'refresh-button'
	);
	
	public $Title;
	public $Behaviour;
	public $ActionClass;
	public $Link;
	public $IconURL;
	
	public function __construct($title, $link, $behaviour = "popup", $icon = null, $class = null, $rel = null) {
		parent::__construct();
		$this->Title = $title;
		$this->Link = $link;
		$this->Behaviour = self::$behaviour_to_js[$behaviour];
		$this->IconURL = $icon;
		$this->ActionClass = $class;
		$this->Rel = $rel;
	}
}

class DOMUtil
{
	public static function readable_list($array)
	{
    if(!is_array($array))
        return '';
    $and = _t('DataObjectManager.AND','and');
    switch(count($array))
    {
    case 0:
        return '';
    case 1:
        // This may not be a normal numerically-indexed array.
        return reset($array);
    case 2:
        return reset($array)." $and ".end($array);
    default:
        $last = array_pop($array);
        return implode(', ', $array).", $and $last";
    }
	}
	
	public static function readable_class($string)
	{
    return ucwords(trim(strtolower(ereg_replace('([A-Z])',' \\1',$string))));	
	}
	
  /**
   * Translates a camel case string into a string with underscores (e.g. firstName -&gt; first_name)
   * @param    string   $str    String in camel case format
   * @return    string            $str Translated into underscore format
   */
  public static function to_underscore($str) {
    $str[0] = strtolower($str[0]);
    $func = create_function('$c', 'return "_" . strtolower($c[1]);');
    return preg_replace_callback('/([A-Z])/', $func, $str);
  }
 
  /**
   * Translates a string with underscores into camel case (e.g. first_name -&gt; firstName)
   * @param    string   $str                     String in underscore format
   * @param    bool     $capitalise_first_char   If true, capitalise the first char in $str
   * @return   string                              $str translated into camel caps
   */
  public static function to_camel_case($str, $capitalise_first_char = false) {
    if($capitalise_first_char) {
      $str[0] = strtoupper($str[0]);
    }
    $func = create_function('$c', 'return strtoupper($c[1]);');
    return preg_replace_callback('/_([a-z])/', $func, $str);
  }
	
}


