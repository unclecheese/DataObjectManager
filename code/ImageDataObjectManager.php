<?php

class ImageDataObjectManager extends FileDataObjectManager
{
	protected static $sliderWidth = 150;
	protected static $minImageSize = 25;
	protected static $maxImageSize = 300;


	public $view = "grid";
	protected $limitFileTypes = array ('jpg','jpeg','gif','png');
	public $template = "ImageDataObjectManager";
	public $itemClass = "ImageDataObjectManager_Item";
	public $popupClass = "ImageDataObjectManager_Popup";
	public $importClass = "Image";
	
	public $imageSize = 100;
	
	public $uploadifyField = "MultipleImageUploadField";

	public function __construct($controller, $name = null, $sourceClass = null, $fileFieldName = null, $fieldList = null, $detailFormFields = null, $sourceFilter = "", $sourceSort = "", $sourceJoin = "") 
	{
		parent::__construct($controller, $name, $sourceClass, $fileFieldName, $fieldList, $detailFormFields, $sourceFilter, $sourceSort, $sourceJoin); 
		Requirements::css('dataobject_manager/css/ui/dom_jquery_ui.css');
		Requirements::javascript('dataobject_manager/javascript/imagedataobject_manager.js');

		if(isset($_REQUEST['ctf'][$this->Name()])) {		
				$this->imageSize = $_REQUEST['ctf'][$this->Name()]['imagesize'];
		}
		$this->setAllowedFileTypes($this->limitFileTypes);
	}

	function handleItem($request) {
		return new ImageDataObjectManager_ItemRequest($this, $request->param('ID'));
	}

	public function getQueryString($params = array())
	{ 
		$imagesize = isset($params['imagesize'])? $params['imagesize'] : $this->imageSize;
		return parent::getQueryString($params)."&ctf[{$this->Name()}][imagesize]={$imagesize}";
	}
	
	public function SliderPercentage()
	{
		return ($this->imageSize - self::$minImageSize) / ((self::$maxImageSize - self::$minImageSize) / 100);
	}
	
	public function SliderPosition()
	{
		return floor(($this->SliderPercentage()/100) * self::$sliderWidth); // handle is 16px wide
	}
		

}

class ImageDataObjectManager_Item extends FileDataObjectManager_Item 
{

	function __construct(DataObject $item, ComplexTableField $parent)
	{
		parent::__construct($item, $parent);
	}

	public function FileIcon()
	{
		$file = ($this->parent->hasDataObject) ? $this->obj($this->parent->fileFieldName) : $this->item;
		if($file) {
			if($this->parent->imageSize <= 50) $size = 50;
			elseif($this->parent->imageSize <= 100) $size = 100;
			elseif($this->parent->imageSize <= 200) $size = 200;
			else $size = 300;
			return ($file instanceof Image && $cropped = $file->CroppedImage($size, $size)) ? $cropped->URL : $file->Icon();
		}
		return false;
	}
	
	public function ImageSize()
	{
		return $this->parent->imageSize;
	}

}

class ImageDataObjectManager_Popup extends FileDataObjectManager_Popup
{
	function __construct($controller, $name, $fields, $validator, $readonly, $dataObject) 
	{
			parent::__construct($controller, $name, $fields, $validator, $readonly, $dataObject);
			Requirements::css('dataobject_manager/css/imagedataobject_manager.css');
	}

}

class ImageDataObjectManager_ItemRequest extends DataObjectManager_ItemRequest 
{
	function __construct($ctf, $itemID) 
	{
		parent::__construct($ctf, $itemID);
	}
	
	function DetailForm($childID = null)
	{	
		if($this->ctf->hasDataObject) {
			$fileField = $this->ctf->fileFieldName;
			$imgObj = $this->dataObj()->$fileField();
		}
		else
			$imgObj = $this->dataObj();
		$form = parent::DetailForm($childID);
		$form->Fields()->insertAfter($this->ctf->getPreviewFieldFor($imgObj, 200), 'open');
		return $form;
	}

}

?>