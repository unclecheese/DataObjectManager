<?php

/**
 * A {@link DropdownField} that allows HTML in its option tags
 * Overloads {@link DropdownField::Field()} to omit Convert::raw2xml
 * @package DataObjectManager
 */
 
class HTMLDropdownField extends DropdownField
{
	/**
	 * Returns a <select> tag containing all the appropriate <option> tags.
	 * Makes use of {@link FormField->createTag()} to generate the <select>
	 * tag and option elements inside is as the content of the <select>.
	 * 
	 * @return string HTML tag for this dropdown field
	 */
	function Field() {
		$options = '';

		$source = $this->getSource();
		if($source) {
			// For SQLMap sources, the empty string needs to be added specially
			if(is_object($source) && $this->emptyString) {
				$options .= $this->createTag('option', array('value' => ''), $this->emptyString);
			}
			
			foreach($source as $value => $title) {
				
				// Blank value of field and source (e.g. "" => "(Any)")
				if($value === '' && ($this->value === '' || $this->value === null)) {
					$selected = 'selected';
				} else {
					// Normal value from the source
					if($value) {
						$selected = ($value == $this->value) ? 'selected' : null;
					} else {
						// Do a type check comparison, we might have an array key of 0
						$selected = ($value === $this->value) ? 'selected' : null;
					}
					
					$this->isSelected = ($selected) ? true : false;
				}
				
				$options .= $this->createTag(
					'option',
					array(
						'selected' => $selected,
						'value' => $value
					),
					$title
				);
			}
		}
		
		$attributes = array(
			'class' => ($this->extraClass() ? $this->extraClass() : ''),
			'id' => $this->id(),
			'name' => $this->name,
			'tabindex' => $this->getTabIndex()
		);
		
		if($this->disabled) $attributes['disabled'] = 'disabled';

		return $this->createTag('select', $attributes, $options);
	}

}