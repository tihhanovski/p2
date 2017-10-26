<?php
/*
 * Created on Mar 22, 2012
 *
 * (c) Ilja Tihhanovski, Intellisoft
 *
 */

	class TextBoxDouble extends TextBox
	{
		protected static function getType()
		{
			return "double";
		}
	}

	function textboxdouble($obj, $field, $caption = null, $attr = null, $addAttr = null)
	{
		if(!$obj->isEditable())
			return lockedMemo($obj->getValue($field), $caption == null ? t($field) : $caption);

		{
			$x = new TextBoxDouble();
			return $x->prepareInput($obj, $field, $caption, $attr, $addAttr);
		}
	}

	function textboxdoubleNotLockable($obj, $field, $caption = null, $attr = null, $addAttr = null)
	{
		$x = new TextBoxDouble();
		return $x->prepareInput($obj, $field, $caption, $attr, $addAttr);
	}

	function dynMultiTextBox($obj, $field, $caption = null, $attr = null, $addAttr = null)
	{
		if($caption == null)
			$caption = $field;
		$ret = "<div style=\"clear: both;\"><b>" . t($caption) . "</b></div>";
		$l = app()->dbo("language");
		if($l->find())
			while($l->fetch())
				$ret .= textbox($obj, PROPERTY_PREFIX . $field . "_" . $l->code, $l->name, $attr, $addAttr);

		return $ret;
	}


	class TextBox extends BaseInput
	{
		protected static function getType()
		{
			return "text";
		}

		protected function additionalAttributes(&$attr)
		{
 			$attr["onblur"] = "JavaScript:fieldOnBlur(this);";
			$attr["type"] = "text";
			$attr["value"] = $this->getValueFromObj();
 		}
	}

	class TextBoxPwd extends BaseInput
	{
		protected static function getType()
		{
			return "password";
		}

		protected function additionalAttributes(&$attr)
		{
 			$attr["onblur"] = "JavaScript:fieldOnBlur(this);";
			$attr["type"] = "password";
			$attr["value"] = $this->getValueFromObj();
 		}
	}

	function addressBoxesEt($obj, $field, $caption = null, $shown = true, $attr = null, $addAttr = null, $togglable = true)
	{
		$flds = array(
			"Country" => "autocomplete",
			"City" => "autocomplete", 
			"Street1" => "text", 
			"Street2" => "text", 
			"Index" => "text", 
			);
		$ret = "";
		
		$disp1 = $shown ? "none" : "block";
		$disp2 = $shown ? "block" : "none";

		$oid = $obj->getIdValue();
		
		if(!is_null($caption))
			$ret .= lockedMemo("<div id=\"{$obj->__table}{$oid}_addressBox{$field}REPR\" style=\"display: $disp1;\">" . $obj->getValue("addressBox" . $field .  "REPR") . "</div>", ($togglable ? "<a href=\"JavaScript:toggleAddressBoxes('$field');\">" : "") . t($caption) . ($togglable ? "</a>" : ""));
		
		$ret .= "<div id=\"addressBox_$field\" style=\"display: $disp2;\"><div style=\"width: 800px;\">";
		foreach($flds as $f => $fmt)
		{
			if($fmt === "text")
				$ret .= textbox($obj, $field . $f, $f, $attr, $addAttr);
			if($fmt === "autocomplete")
			{
				if(strpos($field, PROPERTY_PREFIX) === 0)
					$ret .= textbox($obj, $field . $f, $f, $attr, $addAttr);
				else
					$ret .= textboxAutocomplete($obj, $field . $f, $f, $attr, $addAttr);
			}
		}
		$ret .= "</div></div>";
		return $ret;
	}

	function addressBoxes($obj, $field, $caption = null, $shown = true, $attr = null, $addAttr = null, $togglable = true)
	{
		$flds = array(
			"Street1" => "text", 
			"Street2" => "text", 
			"City" => "autocomplete", 
			"Index" => "text", 
			"Country" => "autocomplete",
			);
		$ret = "";
		
		$disp1 = $shown ? "none" : "block";
		$disp2 = $shown ? "block" : "none";

		$oid = $obj->getIdValue();
		
		if(!is_null($caption))
			$ret .= lockedMemo("<div id=\"{$obj->__table}{$oid}_addressBox{$field}REPR\" style=\"display: $disp1;\">" . $obj->getValue("addressBox" . $field .  "REPR") . "</div>", ($togglable ? "<a href=\"JavaScript:toggleAddressBoxes('$field');\">" : "") . t($caption) . ($togglable ? "</a>" : ""));
		
		$ret .= "<div id=\"addressBox_$field\" style=\"display: $disp2;\"><div style=\"width: 800px;\">";
		foreach($flds as $f => $fmt)
		{
			if($fmt === "text")
				$ret .= textbox($obj, $field . $f, $f, $attr, $addAttr);
			if($fmt === "autocomplete")
			{
				if(strpos($field, PROPERTY_PREFIX) === 0)
					$ret .= textbox($obj, $field . $f, $f, $attr, $addAttr);
				else
					$ret .= textboxAutocomplete($obj, $field . $f, $f, $attr, $addAttr);
			}
		}
		$ret .= "</div></div>";
		return $ret;
	}

	function textbox($obj, $field, $caption = null, $attr = null, $addAttr = null)
	{
		if(!$obj->isEditable())
			return lockedMemo($obj->getValue($field), $caption == null ? t($field) : $caption);
		$x = new TextBox($obj, $field, $caption, $attr, $addAttr);
		return $x->toHtml();
	}

	function textboxPwd($obj, $field, $caption = null, $attr = null, $addAttr = null)
	{
		if(!$obj->isEditable())
			return lockedMemo("***", $caption == null ? t($field) : $caption);
		$x = new TextBoxPwd($obj, $field, $caption, $attr, $addAttr);
		return $x->toHtml();
	}

	function textboxNotLockable($obj, $field, $caption = null, $attr = null, $addAttr = null)
	{
		$x = new TextBox($obj, $field, $caption, $attr, $addAttr);
		return $x->toHtml();
	}

	function selectAutocomplete($obj, $field, $caption = null, $optionsSql = null, $attr = null, $addAttr = null)
	{

	}

	function textboxAutocomplete($obj, $field, $caption = null, $attr = null, $addAttr = null)
	{
		$sql = "select distinct coalesce($field, '') from {$obj->__table}" .
				(($ncs = $obj->getNotClosedSQLClause()) != "" ? " where " . $obj->getNotClosedSQLClause() : "") .
				" order by $field asc";
		return textboxAutocompleteSql($obj, $field, $caption, $sql, $attr, $addAttr);
	}

	function textboxAutocompleteSql($obj, $field, $caption = null, $optionsSql = null, $attr = null, $addAttr = null)
	{
		if(!$obj->isEditable())
			return lockedMemo($obj->getValue($field), $caption == null ? t($field) : $caption);
		$x = new TextBox($obj, $field, $caption, $attr, $addAttr);
		$opn = json_encode(dbToArray($optionsSql));
		return $x->toHtml() . wrapScript("\$(function(){app.setupAutocomplete(\"{$obj->fullpath}_{$field}\", $opn);});");
	}
