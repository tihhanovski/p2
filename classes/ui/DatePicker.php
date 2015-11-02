<?php
/*
 * Created on Mar 22, 2012
 *
 * (c) Ilja Tihhanovski, Intellisoft
 *
 */


	class DatePicker extends BaseInput
	{
		protected static function getType()
		{
			return "datepicker";
		}

		protected function additionalAttributes(&$attr)
		{
			$attr["onblur"] = "JavaScript:fieldOnBlur(this);";
			$attr["type"] = "text";
			$attr["value"] = $this->obj->getValue($this->field);
 		}
	}

	function datepicker($obj, $field, $caption = null, $attr = null, $addAttr = null)
	{
		if(!$obj->isEditable())
			return lockedMemo($obj->getValue($field), $caption == null ? t($field) : $caption);
		$x = new DatePicker($obj, $field, $caption, $attr, $addAttr);
		return $x->toHtml();
	}
