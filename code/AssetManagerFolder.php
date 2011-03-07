<?php

class AssetManagerFolder extends DataObjectDecorator
{
  static $permissions = array('add','edit','upload','delete','import');
  
  public function updateCMSFields(Fieldset $fields)
  {
    $fields->removeFieldFromTab("Root.Files","Files");
    $fields->removeFieldFromTab("Root.Files","deletemarked");
    $fields->removeByName("Upload");
    $fields->addFieldToTab("Root.Files", $a = new AssetManager($this->owner,"Files"));
    $a->setUploadFolder($this->owner->Filename);
    $a->setColumnWidths(array(
      'Title' => 30,
      'Filename' => 70
    ));
    $folder_filter = "(Classname != 'Folder' AND ParentID = ".$this->owner->ID.")"; 
    $filter = $a->sourceFilter ? "({$a->sourceFilter}) AND $folder_filter" : $folder_filter;
    $a->setSourceFilter($filter);
    $a->setParentClass("Folder");
    $a->setPermissions(self::$permissions);
    if($this->owner->Title)
      $a->setAddTitle(sprintf(_t('AssetManager.ADDFILESTO','files to "%s"'),$this->owner->Title));
    else 
      $a->setAddTitle(_t('AssetManager.FILES','files'));
    return $fields;
  }
}

?>