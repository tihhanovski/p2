<?php
/*
 * Created on Feb 28, 2012
 *
 * (c) Ilja Tihhanovski, Intellisoft
 *
 */
 
 
	class ComboAdv extends BaseInput{
		
		protected static function getType(){
			return "comboadv";
		}
	}
	

 	function comboadv($obj, $field, $caption = null, $attr = null, $addAttr = null)
 	{
		$x = new ComboAdv($obj, $field, $caption, $attr, $addAttr);
		return $x->toHtml();
	}
	
 	function comboadv2($obj, $field, $caption = null, $setup = null, $attr = null, $addAttr = null)
 	{
		if(!$obj->isEditable())
			return lockedMemo(app()->getLinkedCaption($obj->getLink($field)), $caption == null ? t($field) : $caption);

		$x = new ComboAdv($obj, $field, $caption, $attr, $addAttr);
		if(!is_object($setup))
		{
			if($setup == null)
				$setup = $obj->getForeignKeyTable($field);
				
			if($setup)
				$setup = app()->dbo($setup)->getAdvancedComboSetup($obj, $field);
		}
		
		return $x->toHtml() .
			(is_object($setup) ? $setup->toHtml() : "");
	}

 	class AdvancedComboSetup
	{
		public $columns = array();
		
		function __construct($obj, $field, $cls, $columns = null)
		{
			if($columns != null)
				$this->columns = $columns;
			$this->obj = $obj;
			$this->field = $field;
			$this->cls = $cls;
		}
		
		function toHtml()
		{
			$o = app()->dbo($this->cls);
			$f = $this->field;
			$value = $this->obj->$f;
			$cols = json_encode($this->columns);
			return "<script language=\"JavaScript\">" .
					"\$(function() {" .
					"setComboAdv(\"{$this->obj->fullpath}_{$this->field}\", " .
						"\"{$this->cls}\", \"" . $o->getPrimaryKeyField() . "\", $cols, " .
						"\"$value\", \$(window).width - 300, null, " . 
						(app()->canUpdate($this->cls) ? "true" : "false") . ");" .
					//"alert('$cols');" .
					"});</script>";
		}
	}
