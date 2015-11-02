<?php
/*
 * Created on Mar 22, 2012
 *
 * (c) Ilja Tihhanovski, Intellisoft
 *
 */


 	function keySel($obj, $field, $caption = null, $setup = null, $attr = null, $addAttr = null)
 	{
 		//TODO take fields to show in locked mode from setup.
		if(!is_object($setup))
		{
			if($setup == null)
				$setup = $obj->getForeignKeyTable($field);
			if($setup)
				$setup = app()->dbo($setup)->getKeySelSetup($obj, $field);
		}

		if(!$obj->isEditable())
		{
			$c = array();
			foreach ($setup->columns as $col)
				$c[] = $col["columnName"];
			return lockedMemo(app()->getLinkedCaption($obj->getLink($field), $c), $caption == null ? t($field) : $caption);
		}

		$x = new KeySelector($obj, $field, $caption, $attr, $addAttr);
		return $x->toHtml() .
			(is_object($setup) ? $setup->toHtml() : "");
	}

	class KeySelector extends BaseInput
	{
		protected static function getType()
		{
			return "keysel";
		}

		protected function additionalAttributes(&$attr)
		{
			$attr["type"] = "text";
 		}

		function getInputPart()
		{
			return parent::getInputPart() . "<span id=\"" . $this->getControlId() . "ADDD\" class=\"keySelNameField\"></span>";
		}
	}

 	class KeySelSetup
	{
		public $columns = array();
		public $af = "";

		function __construct($obj, $field, $cls, $columns = null, $af = "")
		{
	    	$o = $obj->__table;
			if($columns != null)
				$this->columns = $columns;
			$this->obj = $obj;
			$this->field = $field;
			$this->cls = $cls;
			$this->af = $af;
		}

		function toHtml()
		{
			$o = app()->dbo($this->cls);
			$f = $this->field;
			$value = $this->obj->$f;
			$cols = json_encode($this->columns);
			return "<script language=\"JavaScript\">" .
					"\$(function() {" .
					"setKeySel(\"{$this->obj->fullpath}_{$this->field}\", " .
						"\"{$this->cls}\", \"" . $o->getPrimaryKeyField() . "\", $cols, " .
						"\"$value\", \$(window).width - 300, null, " . 
						(app()->canUpdate($this->cls) ? "true" : "false") . ", \"{$this->cls}\", " . 
						(app()->canSelect($this->cls) ? "true" : "false") . ", \"" . $this->af . "\");" .
					"});</script>";
		}
	}
