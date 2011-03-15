<?php

class HasOneDataObjectManager extends HasManyDataObjectManager
{

	/**
	 * Most of the code below was copied from HasOneComplexTableField.
	 * Painful, but necessary, until PHP supports multiple inheritance.
	 */

	public $itemClass = 'HasOneDataObjectManager_Item';
	public $isOneToOne = false;
	
	function getParentIdName($parentClass, $childClass) {
		return $this->getParentIdNameRelation($parentClass, $childClass, 'has_one');
	}
			
	function getControllerJoinID() {
		return $this->controller->{$this->joinField};
	}
	
	function saveInto(DataObject $record) {
		$fieldName = $this->name;
		$fieldNameID = $fieldName . 'ID';
		
		$record->$fieldNameID = 0;
		if($val = $this->value[ $this->htmlListField ]) {
			if($val != 'undefined')
				$record->$fieldNameID = trim($val,",");
		}
		
		$record->write();
	}
	
	function setOneToOne() {
		$this->isOneToOne = true;
	}
	
	function isChildSet($childID) {
		return DataObject::get($this->controllerClass(), '"' . $this->joinField . "\" = '$childID'");
	}
	
	function ExtraData() {
		$val = $this->getControllerJoinID() ? ','.$this->getControllerJoinID().',' : '';
		$inputId = $this->id() . '_' . $this->htmlListEndName;
		return <<<HTML
		<input id="$inputId" name="{$this->name}[{$this->htmlListField}]" type="hidden" value="$val"/>
HTML;
	}


}

class HasOneDataObjectManager_Item extends DataObjectManager_Item {
	
	function MarkingCheckbox() {
		$name = $this->parent->Name() . '[]';
		
		$isOneToOne = $this->parent->isOneToOne;
		$joinVal = $this->parent->getControllerJoinID();
		$childID = $this->item->ID;
		$disabled = $this->parent->hasMarkingPermission() ? "" : "disabled='disabled'";
						
		if($this->parent->IsReadOnly || ($isOneToOne && $joinVal != $childID && $this->parent->isChildSet($childID)))
			return "<input class=\"radio\" type=\"radio\" name=\"$name\" value=\"{$this->item->ID}\" disabled=\"disabled\"/>";
		else if($joinVal == $childID)
			return "<input class=\"radio\" type=\"radio\" name=\"$name\" value=\"{$this->item->ID}\" checked=\"checked\" $disabled />";
		else
			return "<input class=\"radio\" type=\"radio\" name=\"$name\" value=\"{$this->item->ID}\" $disabled />";
	}
}


?>