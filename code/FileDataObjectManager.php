<?php

class FileDataObjectManager extends DataObjectManager
{
	static $url_handlers = array(
		'import/$ID' => 'handleImport'
	);
	
	public static $upgrade_audio = true;
	public static $upgrade_video = true;
	public static $upgrade_image = true;
	public static $upload_limit  = "40";
	
	public $view;
	public $default_view = "grid";
	protected $allowedFileTypes;
	protected $limitFileTypes;
	protected $uploadLimit;
	protected $allowUploadFolderSelection = true;
	protected $enableUploadDebugging = false;
	public $hasDataObject = true;
	public $importClass = "File";

	protected $permissions = array(
		"add",
		"edit",
		"show",
		"delete",
		"upload",
		"import"
	);
	public $popupClass = "FileDataObjectManager_Popup";
	public $itemClass = "FileDataObjectManager_Item";
	public $template = "FileDataObjectManager";
	public $templatePopup = "DataObjectManager_popup";
	
	public $gridLabelField;
	public $pluralTitle;
	public $browseButtonText = "Upload files";
	
	public $uploadFolder = "Uploads";
	
	public $uploadifyField = "MultipleFileUploadField";
	
	public function __construct($controller, $name = null, $sourceClass = null, $fileFieldName = null, $fieldList = null, $detailFormFields = null, $sourceFilter = "", $sourceSort = "", $sourceJoin = "") 
	{
		if(!class_exists("SWFUploadField") && !class_exists("UploadifyField"))
			die("<strong>"._t('DataObjectManager.ERROR','Error')."</strong>: "._t('FileDataObjectManager.SWFUPLOADUPLOADIFY','DataObjectManager requires the Uploadify or SWFUpload modules.'));
		parent::__construct($controller, $name, $sourceClass, $fieldList, $detailFormFields, $sourceFilter, $sourceSort, $sourceJoin);
	  // Intelligent constructor for fileFieldName
		$SNG = singleton($this->sourceClass());
	  if($fileFieldName === null) {
        if($has_ones = $SNG->has_one()) {
          foreach($has_ones as $relation => $value) {
            if($value == "File" || is_subclass_of($value,"File")) {
              $fileFieldName = $relation;
              $fileClassName = $value;
              break;
            }
          }
        }
    }
			
		if(isset($_REQUEST['ctf'][$this->Name()])) {		
				$this->view = $_REQUEST['ctf'][$this->Name()]['view'];
		}
		if($this->sourceClass() == "File" || is_subclass_of($this->sourceClass(), "File")) {
			$this->hasDataObject = false;
			$this->fileFieldName = $name;
			$this->fileClassName = $this->sourceClass();
			$this->dataObjectFieldName = null;
		}
		else {
			$this->dataObjectFieldName = $name;
			$this->fileFieldName = $fileFieldName;
			$this->fileClassName = $SNG->has_one($this->fileFieldName);
			if(!$this->fileClassName)
				die("<strong>FileDataObjectManager::__construct():</strong>"._t('FileDataObjectManager.FILERELATION','Could not determine file relationship'));
		}
		
		$this->controllerClassName = $controller->class;
		if($key = array_search($this->controllerClassName, $SNG->stat('has_one')))
			$this->controllerFieldName = $key;
		else
			$this->controllerFieldName = $this->controllerClassName;
		$this->controllerID = $controller->ID;
		// Check for allowed file types
		if($types = Object::get_static($this->fileClassName,'allowed_file_types'))
			$this->setAllowedFileTypes($types);
	}

	public function getQueryString($params = array())
	{ 
		$view = isset($params['view'])? $params['view'] : $this->view;
		return parent::getQueryString($params)."&ctf[{$this->Name()}][view]={$view}";
	}
	
	public function setGridLabelField($fieldName)
	{
		$this->gridLabelField = $fieldName;
	}
		
	public function GridLink()
	{
		return $this->RelativeLink(array('view' => 'grid'));
	}
	
	public function ListLink()
	{
		return $this->RelativeLink(array('view' => 'list'));
	}

	public function GridView()
	{
		return $this->ListStyle() == "grid";
	}
	
	public function ListView()
	{
		return $this->ListStyle() == "list";
	}

	public function ListStyle()
	{
		return $this->view ? $this->view : $this->default_view;
	}
	
	
	public function ImportDropdown()
	{
		return new HTMLDropdownField('ImportFolder','',$this->getImportFolderHierarchy(0),null, null, "-- "._t('DataObjectManager.SELECTFOLDER', 'Select a folder')." --");
	}
	
	protected function importLinkFor($file)
	{
		return Controller::join_links($this->BaseLink(), "import", $file->ID);
	}
	
	protected function getImportFolderHierarchy($parentID, $level = 0)
	{
		$options = array();		
		if($children = DataObject::get("Folder", "ParentID = $parentID")) {
			foreach($children as $child) {
				$indent="";
				for($i=0;$i<$level;$i++) $indent .= "&nbsp;&nbsp;";
				$files = DataObject::get($this->importClass, "ClassName != 'Folder' AND ParentID = $child->ID");
				$count = $files ? $files->Count() : "0";
				$options[$this->importLinkFor($child)] = $indent.$child->Title . " <span>($count files)</span>";
				$options += $this->getImportFolderHierarchy($child->ID, $level+1);
			}
		}
		return $options;
	}

	protected function getUploadFolderHierarchy($parentID, $level = 0)
	{
		$options = array();		
		if($children = DataObject::get("Folder", "ParentID = $parentID")) {
			foreach($children as $child) {
				$indent="";
				for($i=0;$i<$level;$i++) $indent .= "&nbsp;&nbsp;";
				$options[$child->ID] = empty($child->Title) ? "<em>$indent Untitled</em>" : $indent.$child->Title;
				$options += $this->getUploadFolderHierarchy($child->ID, $level+1);
			}
		}
		return $options;
	}

	
	public function setAllowedFileTypes($types = array())
	{
		foreach($types as $type) {
			if(is_array($this->limitFileTypes) && !in_array(strtolower(str_replace(".","",$type)), $this->limitFileTypes))
				// To-do: get user_error working.
				die("<strong>".$this->class . "::setAllowedFileTypes() -- Only files of type " . implode(", ", $this->limitFileTypes) . " are allowed.</strong>");
		}
		$this->allowedFileTypes = $types;
	}
	
	public function getAllowedFileTypes()
	{
		return $this->allowedFileTypes;
	}
	
	public function setUploadLimit($num)
	{
		$this->uploadLimit = $num;
	}
	
	public function getUploadLimit()
	{
		return $this->getSetting('uploadLimit');
	}
	
	public function setBrowseButtonText($text)
	{
		$this->browseButtonText = $text;
	}
	
	public function getBrowseButtonText()
	{
		return $this->browseButtonText;
	}
	
	public function ButtonAddTitle()
	{
		return $this->addTitle ? $this->addTitle : $this->PluralTitle();
	}
	
	public function allowUploadFolderSelection()
	{
		$this->allowUploadFolderSelection = true;
	}
	
	public function enableUploadDebugging()
	{
		$this->enableUploadDebugging = true;
	}
	
	public function setDefaultView($type)
	{
		$this->default_view = $type;
	}
	
	public function upload()
	{
		if(!$this->can('add')) return;
		$form = class_exists('UploadifyField') ? $this->UploadifyForm() : $this->UploadForm();
		if(is_string($form))
			return $this->customise(array(
				'String' => true,
				'NestedController' => $this->isNested,
				'DetailForm' => $this->UploadForm(),
			))->renderWith($this->templatePopup);
		else {
			$form = class_exists('UploadifyField') ? $this->UploadifyForm() : $this->UploadForm();
			return $this->customise(array(
			  'String' => is_string($form),
				'DetailForm' => $form
			))->renderWith($this->templatePopup);		
		}
	}
	
	public function UploadLink()
	{
		return Controller::join_links($this->BaseLink(),'upload');
	}
	
	protected function getUploadFields()
	{
		
		$fields = new FieldSet(
			new HeaderField($title = sprintf(_t('DataObjectManager.ADDITEM', 'Add %s'),$this->PluralTitle()), $headingLevel = 2),
			new HeaderField($title = _t('DataObjectManager.UPLOADFROMPC', 'Upload from my computer'), $headingLevel = 3),
			new SWFUploadField(
				"UploadForm",
				"Upload",
				"",
				array(
					'file_upload_limit' => $this->getUploadLimit(), // how many files can be uploaded
					'file_queue_limit' => $this->getUploadLimit(), // how many files can be in the queue at once
					'browse_button_text' => $this->getBrowseButtonText(),
					'upload_url' => Director::absoluteURL('FileDataObjectManager_Controller/handleswfupload'),
					'required' => 'true'			
				)
			)
		);

		if($this->allowUploadFolderSelection) 
			$fields->insertBefore(new HTMLDropdownField('UploadFolder','',$this->getUploadFolderHierarchy(0),null, null, "-- Select a folder --"),"Upload");
		return $fields;
	}

	protected function getUploadifyFields()
	{
		
		$class = $this->uploadifyField;
		$fields = new FieldSet(
			new HeaderField($title = sprintf(_t('DataObjectManager.ADDITEM', 'Add %s'),$this->PluralTitle()), $headingLevel = 2),
			$uploader = new $class('UploadedFiles')
		);

		if(!$this->allowUploadFolderSelection) { 
			$uploader->removeFolderSelection();
		}
		if($this->uploadFolder) {
			$uploader->setUploadFolder($this->uploadFolder);
		}
		$uploader->setVar('buttonText', $this->getBrowseButtonText());
		$uploader->setVar('queueSizeLimit', $this->getUploadLimit());
		if(is_subclass_of($this->fileClassName, "File")) {
			if(is_subclass_of($this->fileClassName, "Image")) {
				$uploader->setVar('image_class', $this->fileClassName);
			}
			else {
				$uploader->setVar('file_class', $this->fileClassName);
			}
		}
		
		if(!empty($this->allowedFileTypes)) { 
			$uploader->setFileTypes($this->getAllowedFileTypes(), $this->PluralTitle() . '(' . implode(',',$this->allowedFileTypes) . ')'); 
		}  
		$uploader->uploadFolder = $this->uploadFolder; 
		return $fields;
	}
	
	public function UploadifyForm() {

		Validator::set_javascript_validation_handler('none');
		
		$fields = $this->Can('upload') ? $this->getUploadifyFields() : new FieldSet(			
			new HeaderField($title = sprintf(_t('DataObjectManager.ADD', 'Add %s'),$this->PluralTitle()), $headingLevel = 2)
		);

		$className = $this->sourceClass();
		$childData = new $className();

		$form = Object::create(
			$this->popupClass,
			$this,
			'UploadifyForm',
			$fields,
			new RequiredFields('UploadedFiles'),
			false,
			$childData
		);
		
		$uploader = $form->Fields()->fieldByName('UploadedFiles');
		$action = $this->Can('upload') ? new FieldSet(new FormAction('saveUploadifyForm', 'Continue')) : new FieldSet();
		$form->setActions($action);
		return $form;
	}
	
	public function UploadForm()
	{
		// Sync up the DB
//		singleton('Folder')->syncChildren();
		$className = $this->sourceClass();
		$childData = new $className();
		$validator = $this->getValidatorFor($childData);
		if($this->Can('upload')) {
			SWFUploadConfig::addPostParams(array(
				'dataObjectClassName' => $this->sourceClass(),
				'dataObjectFieldName' => $this->dataObjectFieldName,
				'fileFieldName' => $this->fileFieldName,
				'fileClassName' => $this->fileClassName,
				'parentIDName' => $this->getParentIdName( $this->getParentClass(), $this->sourceClass() ),
				'controllerID' => $this->controllerID,
				'OverrideUploadFolder' => $this->getUploadFolder(),
				'hasDataObject' => $this->hasDataObject ? 1 : 0
			));
			
			if($this->allowUploadFolderSelection)
				SWFUploadConfig::addDynamicPostParam('UploadFolder',$this->popupClass.'_UploadForm_UploadFolder');
	
			if($this->getAllowedFileTypes()) 
				SWFUploadConfig::addFileTypes($this->getAllowedFileTypes());
			
			if($this->enableUploadDebugging)
				SWFUploadConfig::set_var('debug','true');
		}
						
		$fields = $this->Can('upload') ? $this->getUploadFields() : new FieldSet(			
			new HeaderField($title = sprintf(_t('DataObjectManager.ADD', 'Add %s'),$this->PluralTitle()), $headingLevel = 2)
		);
		
		$form = Object::create(
			$this->popupClass,
			$this,
			'UploadForm',
			$fields,
			$validator,
			false,
			$childData
		);
		$action = $this->Can('upload') ? new FieldSet(new FormAction('saveUploadForm', 'Upload')) : new FieldSet();
		$form->setActions($action);
		if($this->Can('import')) {
			$header = new HeaderField($title = _t('DataObjectManager.IMPORTFROMFOLDER', 'Import from an existing folder'), $headingLevel = 3);
			$holder = 	new LiteralField("holder","<div class='ajax-loader'></div><div id='import-holder'></div>");
			if(!isset($_POST['uploaded_files']))
				return $form->forTemplate() . $header->Field() . $this->ImportDropdown()->Field() . $holder->Field();
			else
				return $form;
		}
		return $form;
		
	}
	
	public function saveUploadForm()
	{
		if(isset($_POST['uploaded_files']) && is_array($_POST['uploaded_files'])) {
      $form = $this->EditUploadedForm();
			return $this->customise(array(
			  'String' => is_string($form),
				'DetailForm' => $form
			))->renderWith($this->templatePopup);
		}
	}
	
	public function updateDataObject(&$object) { }
	
	public function saveUploadifyForm($data, $form)
	{
		if(!isset($data['UploadedFiles']) || !is_array($data['UploadedFiles'])) {
			return Director::redirectBack();
		}
		
		$file_class = $this->fileClassName;
		$do_class = $this->sourceClass();
		$idxfield = $this->fileFieldName."ID";
		$fff = $this->fileFieldName;
		$dataobject_ids = array();
		if($this->hasDataObject) {
			foreach($data['UploadedFiles'] as $id) {
				if($file = DataObject::get_by_id("File", (int) $id)) {
					$upload_folder = $form->Fields()->fieldByName('UploadedFiles')->uploadFolder;
					$folder_id = Folder::findOrMake($upload_folder)->ID;
					if($file->ParentID != $folder_id) {
						$new_file_path = $this->uploadFolder.'/'.$file->Name;
						copy($file->getFullPath(), BASE_PATH.'/'.ASSETS_DIR.'/'.$new_file_path);
						$clone = new $file_class();
						$clone->Filename = $new_file_path;
						$clone->ParentID = $folder_id;						
						$clone->write();
						$id = $clone->ID;
					}
					
					$obj = new $do_class();			
					$obj->$idxfield = $id;
					$ownerID = $this->getParentIdName($this->getParentClass(), $this->sourceClass());
					$obj->$ownerID = $this->controllerID;
					$this->updateDataObject($obj);
					$obj->write();
					$obj->$fff()->write();
					$dataobject_ids[] = $obj->ID;
				}
			}
			$_POST['uploaded_files'] = $dataobject_ids;
			foreach($_POST['uploaded_files'] as $id) {
			}
		}	
		else {
			foreach($data['UploadedFiles'] as $id) {
				if($file = DataObject::get_by_id("File", (int) $id)) {
					$ownerID = $this->getParentIdName($this->getParentClass(), $this->sourceClass());
					$file->$ownerID = $this->controllerID;
					$file->write();
				}
			}
		}		

      $form = $this->EditUploadedForm();
		return $this->customise(array(
		  'String' => is_string($form),
			'DetailForm' => $form
		))->renderWith($this->templatePopup);
	}
	
	protected function getChildDataObj()
	{
		$class = $this->sourceClass();
		return new $class();
	}
	
	public function getPreviewFieldFor($fileObject, $size = 150)
	{
		if($fileObject instanceof Image) {
			$URL = $fileObject->getHeight() > $size ? $fileObject->SetHeight($size)->URL : $fileObject->URL
			;
			return new LiteralField("icon",
				"<div class='current-image'><img src='$URL' alt='' /><h3>$fileObject->Filename</h3></div>"
			);
		}
		else {
			$URL = $fileObject->Icon();			
			return new LiteralField("icon",
				"<h3><img src='$URL' alt='' /><span>$fileObject->Filename</span></h3>"
			);			
		}	
	}
	
	protected function closePopup()
	{
			Requirements::clear();
			if($this->isNested)
				Requirements::customScript("parent.jQuery('#iframe_".$this->id()." a').click();");
			else {
			Requirements::customScript("
					var container = parent.jQuery('#".$this->id()."');
					parent.jQuery('#facebox').fadeOut(function() {
						parent.jQuery('#facebox .content').removeClass().addClass('content');
						parent.jQuery('#facebox_overlay').remove();
						parent.jQuery('#facebox .loading').remove();
						parent.refresh(container, container.attr('href'));
					});");
			}
			return $this->customise(array(
				'String' => true,
				'DetailForm' => 'Closing...'
			))->renderWith($this->templatePopup);	
	}
	
	public function EditUploadedForm()
	{
		if(!$this->hasDataObject)
			return $this->closePopup();

		$childData = $this->getChildDataObj();
		$validator = $this->getValidatorFor($childData);
		$fields = $this->getFieldsFor($childData);
		$fields->removeByName($this->fileFieldName);
		$total = isset($_POST['totalsize']) ? $_POST['totalsize'] : sizeof($_POST['uploaded_files']);
		$index = isset($_POST['index']) ? $_POST['index'] + 1 : 1;
		$fields->push(new HiddenField('totalsize','',$total));
		$fields->push(new HiddenField('index','',$index));
		if(isset($_POST['uploaded_files']) && is_array($_POST['uploaded_files'])) {
			$remaining_files = $_POST['uploaded_files'];
			$current = $remaining_files[0];
			$dataObject = DataObject::get_by_id($this->sourceClass(), $current);
			$fileObject = $dataObject->obj($this->fileFieldName);
			$fields->push(new HiddenField('current','',$current));
			unset($remaining_files[0]);
			if(!$fields->loaded) {
				foreach($remaining_files as $id)
						$fields->push(new LiteralField("u-$id","<input type='hidden' name='uploaded_files[]' value='$id' />"));
				$first = $fields->First()->Name();
				$fields->insertBefore(new HeaderField("Header","Editing file $index of $total",2), $first);				
				$fields->insertBefore($this->getPreviewFieldFor($fileObject), $first);
			}
		}

		$form = Object::create(
			$this->popupClass,
			$this,
			'EditUploadedForm',
			$fields,
			$validator,
			false,
			$childData
		);
		$form->setActions(new FieldSet(new FormAction("saveEditUploadedForm", $index == $total ? "Finish" : "Next")));
		if(isset($dataObject) && $dataObject) 
			$form->loadDataFrom($dataObject);
		$fields->loaded = true;		
		return $form;
	}
	
	function saveEditUploadedForm($data, $form)
	{
		$obj = DataObject::get_by_id($this->sourceClass(), $data['current']);
		$form->saveInto($obj);
		$obj->write();
		if(isset($data['uploaded_files']) && is_array($data['uploaded_files'])) {
      $form = $this->EditUploadedForm();
			return $this->customise(array(
        'String' => is_string($form),
				'DetailForm' => $form
			))->renderWith($this->templatePopup);
		}
		else {
			return $this->closePopup();
		}
	}
	
	public function handleImport($request)
	{
		$this->importFolderID = $request->param('ID');
		die($this->ImportForm($this->importFolderID)->forTemplate());
	}
	
	protected function getImportFields()
	{
		return new FieldSet(
				new HiddenField('dataObjectClassName','',$this->sourceClass()),
				new HiddenField('fileFieldName','', $this->fileFieldName),
				new HiddenField('parentIDName','', $this->getParentIdName( $this->getParentClass(), $this->sourceClass() )),
				new HiddenField('controllerID','',$this->controllerID)
			);
	}
	
	protected function ImportForm($folder_id = null)
	{
		$folder_id = isset($_POST['folder_id']) ? $_POST['folder_id'] : $this->importFolderID;;
		if($files = DataObject::get($this->importClass, "ClassName != 'Folder' AND ParentID = $folder_id"))
			$fields = $this->getImportFields();
			$fields->push(new HiddenField('folder_id','',$folder_id));
			$fields->push(new LiteralField('select','<div class="select"><span>Select</span>: <a href="javascript:void(0)" rel="all">all</a> | <a href="javascript:void(0)" rel="none">none</a></div>'));
			$fields->push(new LiteralField("ul","<ul>"));
			foreach($files as $file) {
				if($file instanceof Image) {
				  if($img = $file->CroppedImage(35,35))
				    $icon = $img->URL;
				  else
				    $icon = "";
				}
				elseif($file instanceof File)
				  $icon = $file->Icon();
				else
				  $icon = "";
				
				$title = strlen($file->Title) > 30 ? substr($file->Title, 0, 30)."..." : $file->Title;
				$types = $this->getAllowedFileTypes();
				if(is_array($types) && !empty($types))
				  $allowed = in_array(strtolower($file->Extension), $types);
				else
				  $allowed = true;
				
				$class = !$allowed ? "class='disabled'" : "";
				$disabled = !$allowed ? "disabled='disabled'" : "";
				
				$fields->push(new LiteralField("li-$file->ID",
					"<li $class>
						<span class='import-checkbox'><input $disabled type='checkbox' name='imported_files[]' value='$file->ID' /></span>
						<span class='import-icon'><img src='$icon' alt='' /></span>
						<span class='import-title'>".$title."</span>
					</li>"
				));
			}
			$fields->push(new LiteralField("_ul","</ul>"));			
			return new Form(
				$this,
				"ImportForm",
				$fields,
				new FieldSet(new FormAction('saveImportForm','Import'))
			);
	}
	
	public function saveImportForm($data, $form)
	{
		if(isset($data['imported_files']) && is_array($data['imported_files'])) {
			$_POST['uploaded_files'] = array();
				// If the user has set a custom upload folder, cut a new copy of the file when importing
			$custom_folder = $this->getUploadFolder() != "Uploads" ? Folder::findOrMake($this->getCleanUploadFolder()) : false;
			foreach($data['imported_files'] as $file_id) {
				$file = DataObject::get_by_id("File",$file_id);
				if($custom_folder && $file->ParentID != $custom_folder->ID) {
					$new_path = Director::baseFolder().'/'.$custom_folder->Filename.$file->Name;	
					copy($file->getFullPath(),$new_path);
					$file_class = $file->ClassName;
					$new_file = new $file_class();
					$new_file->setFilename($custom_folder->Filename.$file->Name);
					$new_file->setName($file->Name);
					$new_file->setParentID($custom_folder->ID);
					$new_file->write();
					$file = $new_file;
					$file_id = $new_file->ID;
				}

				// If something other than File has been specified as the linked file class,
				// we need to "upgrade" the imported file to the correct class.
				if($this->fileClassName != "File" && $file->ClassName != $this->fileClassName) {
					$file = $file->newClassInstance($this->fileClassName);
					$file->write();
				}
				$owner_id = $data['parentIDName'];
				if($this->hasDataObject) {
					$do_class = $data['dataObjectClassName'];
					$idxfield = $data['fileFieldName']."ID";
					$obj = new $do_class();
					$obj->$idxfield = $file_id;
					$obj->$owner_id = $data['controllerID'];
					$obj->write();
					$_POST['uploaded_files'][] = $obj->ID;
				}
				else {
					if($file = DataObject::get_by_id($this->fileClassName, $file_id)) {
						$id_field = $this->controllerFieldName."ID";

						if($file->hasField($owner_id)) {
							$file->$owner_id = $this->controllerID;
							$file->write();
						}
					}
				}
			}
			$form = $this->EditUploadedForm();
			return $this->customise(array(
				'String' => is_string($form),
				'DetailForm' => $form
			))->renderWith($this->templatePopup);		

		}
	}
	public function setUploadFolder($override)
	{
		$this->uploadFolder = $override;
	}
	public function getUploadFolder()
	{
		return $this->uploadFolder;
	}
	
	public function getCleanUploadFolder()
	{
		$path = str_replace(ASSETS_DIR."/","",$this->getUploadFolder());
		if(substr($path,-1)=="/") $path = substr($path,0, -1);
		return $path;
	}
	
}

class FileDataObjectManager_Controller extends Controller
{
	public function handleswfupload()
	{
		if(!Permission::check("CMS_ACCESS_CMSMain"))
			return;
		
		if(isset($_FILES['swfupload_file']) && !empty($_FILES['swfupload_file'])) {
			$do_class = $_POST['dataObjectClassName'];
			$hasDataObject = $_POST['hasDataObject'];
			$idxfield = $_POST['fileFieldName']."ID";
			$file_class = $_POST['fileClassName'];
			$file = new $file_class();

			if(isset($_POST['UploadFolder'])) {
				$folder = DataObject::get_by_id("Folder",$_POST['UploadFolder']);
				$path = str_replace(ASSETS_DIR."/","",$folder->Filename);
				if(substr($path,-1)=="/") $path = substr($path,0, -1);
			}
			else {
				$path = str_replace(ASSETS_DIR."/","",$_POST['OverrideUploadFolder']);
				if(substr($path,-1)=="/") $path = substr($path,0, -1);
			}
			if(class_exists("Upload")) {
				$u = new Upload();
				$u->loadIntoFile($_FILES['swfupload_file'], $file, $path);
			}
			else
				$file->loadUploaded($_FILES['swfupload_file'],$path);
			
			if(isset($_POST['UploadFolder']))
				$file->setField("ParentID",$folder->ID);

			// Provide an "upgrade" to File subclasses
			if($file->class == "File") {
				$ext = strtolower($file->Extension);
				if(in_array($ext, MP3::$allowed_file_types) && FileDataObjectManager::$upgrade_audio)
					$file = $file->newClassInstance("MP3");
				else if(in_array($ext, array('jpg','jpeg','gif','png')) && FileDataObjectManager::$upgrade_image)
					$file = $file->newClassInstance("Image");
				else if(in_array($ext, FLV::$allowed_file_types) && FileDataObjectManager::$upgrade_video)
					$file = $file->newClassInstance("FLV");
			}
      $file->OwnerID = Member::currentUserID();
			if($hasDataObject) {
				$file->write();
				$obj = new $do_class();			
				$obj->$idxfield = $file->ID;
				$ownerID = $_POST['parentIDName'];
				$obj->$ownerID = $_POST['controllerID'];
				$obj->write();
				echo $obj->ID;
			}
			else {
				$ownerID = $_POST['parentIDName'];
				$file->$ownerID = $_POST['controllerID'];
				$file->write();
				echo $file->ID;
			}
		}
		else {
			echo ' ';
		}
	
	
	}
}

class FileDataObjectManager_Item extends DataObjectManager_Item {
	function __construct(DataObject $item, ComplexTableField $parent) 
	{
		parent::__construct($item, $parent);
	}
	
	public function IsFile()
	{
		return $this instanceof File;
	}
	
	public function FileIcon()
	{
    if($this->parent->hasDataObject) {
	    $field = $this->parent->fileFieldName."ID";
	    $file = DataObject::get_by_id($this->parent->fileClassName, $this->item->$field);
    }
    else 
	    $file = $this->item;

    if($file && $file->ID) {
       if($file instanceof Image)
          $img = $file;
       else {
          $ext = $file->Extension;
          $imgExts = array('jpg','jpeg','gif');
          if(in_array($ext, $imgExts)) {
             $img = new Image_Cached($file->Filename);
             $img->ID = $file->ID; //image resize functions require an id
          }
       }         
       if(isset($img)) {
       	if($crop = $img->CroppedImage(50,50)) {
       		return $crop->URL;
       	}
       }
       return $file->Icon();

    }
    else return "{$this->item->$field}"; 
 	}
	
	public function FileLabel()
	{
		$idField = $this->parent->fileFieldName."ID";
		if($this->parent->gridLabelField) {
			$field = $this->parent->gridLabelField;
			return $this->$field;
		}
		else if(!$this->parent->hasDataObject)
			$label = $this->item->Title;
		else if($file = DataObject::get_by_id($this->parent->fileClassName, $this->item->$idField))
			$label = $file->Title;
		else
			$label = "";
		return strlen($label) > 30 ? substr($label, 0, 30)."..." : $label;
	}
	
}


class FileDataObjectManager_Popup extends DataObjectManager_Popup
{
	function __construct($controller, $name, $fields, $validator, $readonly, $dataObject) {
			parent::__construct($controller, $name, $fields, $validator, $readonly, $dataObject);
			
			// Hack!
			Requirements::block(THIRDPARTY_DIR.'/prototype.js');
			if($name == "UploadForm" && !isset($_POST['uploaded_files']) && $controller->Can('upload')) SWFUploadConfig::bootstrap();
			
			Requirements::javascript('dataobject_manager/javascript/filedataobjectmanager_popup.js');			
	}
	
}

?>
