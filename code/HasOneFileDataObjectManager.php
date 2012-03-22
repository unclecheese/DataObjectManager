<?php

class HasOneFileDataObjectManager extends FileDataObjectManager
{

	public function FieldHolder() {
		$list = Object::create(
			"DropdownField", 
			$this->grid->getName()."ID", 
			sprintf(_t('DOM.Selected','Selected %s'),$this->grid->Title()), 
			DataList::create($this->dataClass)
				->map('ID', 'Title')
				->toArray()
		);
		$this->grid->setList(DataList::create($this->dataClass));		
		return "<div>{$list->FieldHolder()}</div>{$this->grid->FieldHolder()}";
	}


}