<?php

class SimpleTreeDropdownField extends HTMLDropdownField
{
	protected $sourceClass, $labelField, $parentID, $useCache, $filter;
	private static $cache = array();

	function __construct($name, $title = "", $sourceClass = "SiteTree", $value = "", $labelField = "Title", $form = null, $emptyString = null, $parentID = 0, $cache = false)
	{
		$this->labelField = $labelField;
		$this->parentID = $parentID;
		$this->useCache = $cache;
		parent::__construct($name, $title, null, $value, $form, $emptyString);
		//so that we mimic the behaviour of TreeDropDownField,
		// if you pass an array, we will treat it as the source.
		if (is_array($sourceClass)) {
			$this->source = $sourceClass;
		}
		else {
			$this->sourceClass = $sourceClass;
		}
	}

	public function setLabelField($field)
	{
		$this->labelField = $field;
	}
	
	public function setFilter($filter) {
		$this->filter = $filter;
	}

	function getSource() {
		if (!$this->source) {
			if ($this->useCache) {
				$this->source = $this->getCachedHierarchy((int)$this->parentID);
			}
			else {
				$this->source = $this->getHierarchy((int)$this->parentID);
			}
		}
		return parent::getSource();
	}

	private function getCachedHierarchy($parentID) {
		$class = ($this->sourceClass == "SiteTree" || is_subclass_of($this->sourceClass, "SiteTree")) ? "SiteTree" : $this->sourceClass;
		if (!isset(self::$cache[$class][$parentID])) {
			if (!isset(self::$cache[$class])) {
				self::$cache[$class] = array();
			}
			self::$cache[$class][$parentID] = $this->getHierarchy($parentID);
		}
		return self::$cache[$class][$parentID];
	}

	private function getHierarchy($parentID, $level = 0)
	{
		$options = array();
		$class = ($this->sourceClass == "SiteTree" || is_subclass_of($this->sourceClass, "SiteTree")) ? "SiteTree" : $this->sourceClass;
		$filter = ($this->filter) ? "ParentID = $parentID AND $this->filter" : "ParentID = $parentID";
		if($children = DataObject::get($class, $filter)) {
			foreach($children as $child) {
				$indent="";
				for($i=0;$i<$level;$i++) $indent .= "&nbsp;&nbsp;";
				if($child->ClassName == $this->sourceClass || is_subclass_of($child, $this->sourceClass)) {
					$text = $child->__get($this->labelField);
					$options[$child->ID] = empty($text) ? "<em>$indent Untitled</em>" : $indent.$text;
				}
				$options += $this->getHierarchy($child->ID, $level+1);
			}
		}
		return $options;
	}
}
