<?php
/*
 * Created on Mar 22, 2012
 *
 * (c) Ilja Tihhanovski, Intellisoft
 *
 */

	class TextArea extends BaseInput
	{
		protected static function getType()
		{
			return "textarea";
		}

		protected function additionalAttributes(&$attr)
		{
			$attr["onblur"] = "JavaScript:fieldOnBlur(this);";
		}
		protected function htmlType()
		{
 			return "textarea";
		}

		protected function inputContent()
		{
			return $this->getValueFromObj();
		}
	}

 	function textareaNotLockable($obj, $field, $caption = null, $attr = null, $addAttr = null)
 	{
		$x = new TextArea();
		return $x->prepareInput($obj, $field, $caption, $attr, $addAttr);
	}

 	function textarea($obj, $field, $caption = null, $attr = null, $addAttr = null)
 	{
		if(!$obj->isEditable())
			return lockedMemo($obj->getValue($field), $caption == null ? t($field) : $caption, "", $addAttr);

		$x = new TextArea();
		return $x->prepareInput($obj, $field, $caption, $attr, $addAttr);
	}

	function dynMultiTextArea($obj, $field, $caption = null, $attr = null, $addAttr = null)
	{
		if($caption == null)
			$caption = $field;
		$ret = "<div style=\"clear: both;\"><b>" . t($caption) . "</b></div>";
		$l = app()->dbo("language");
		if($l->find())
			while($l->fetch())
				$ret .= textarea($obj, PROPERTY_PREFIX . $field . "" . $l->code, $l->name, $attr, $addAttr);

		return $ret;
	}
