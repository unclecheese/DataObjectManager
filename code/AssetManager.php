<?php

class AssetManager extends FileDataObjectManager
{
  
  public $default_view = "list";
  
  public function __construct($controller, $name, $sourceClass = "File", $headings = null)
  {
    if($headings === null) {
      $headings = array(
        'Title' => 'Title',
        'Filename' => 'Filename'
      );
    }
    
    $fields = singleton($sourceClass)->getCMSFields();
    $fields->removeByName("OwnerID");
    $fields->removeByName("Parent");
    $fields->removeByName("Filename");
    $fields->removeByName("SortOrder");
    $fields->removeByName("Sort");
    $fields->push(new ReadonlyField('Filename'));
    $fields->push(new SimpleTreeDropdownField('ParentID','Folder',"Folder"));
    $fields->push(new HiddenField('ID','',$controller->ID));
    
    parent::__construct($controller, $name, $sourceClass, null, $headings, $fields, "\"ClassName\" != 'Folder'");
  }
  
	function FieldHolder()
	{
		if(!$this->controller->ID)
			return "";
		return parent::FieldHolder();
	}
  

}

?>