<?php
/*
 * Created on Mar 22, 2012
 *
 * (c) Ilja Tihhanovski, Intellisoft
 *
 */
 
 
	class CheckBox extends BaseInput
	{
		protected static function getType()
		{
			return "checkbox";
		}
		
		function getLabelPart()
		{
			return "<div class=\"formLabel\">&nbsp;</div>";
		}
		
		function getInputPart()
		{
			return "<label>" .
				parent::getInputPart() .
				$this->getCaption() . "</label>";
		}
		
		function getInputPart_old()
		{
			return "<div class=\"floatLeft\">" . parent::getInputPart() . "</div>" .
				"<div class=\"floatLeft\"><label for=\"" . $this->getControlId() . "\">" . 
				$this->getCaption() . "</label></div>";
		}

		protected function additionalAttributes(&$attr)
		{
			$attr["onclick"] = "JavaScript:checkboxclick(this);";
			$attr["type"] = "checkbox";
			
			if ($this->obj->getValue($this->field) == "1")
				$attr["checked"] = "yes";			
 		}

		function getMinimalRepresentation()
		{
			return $this->obj->getValue($this->field) ? $this->getCaption() : "";
		}
	}
	
	function checkbox($obj, $field, $caption = null, $attr = null, $addAttr = null)
	{
		if(!$obj->isEditable())
		{
			if($obj->$field)
				$c = "<img src=\"" . app()->url("ui/img/16/check-1.png") . "\" border=\"0\" />";
			else
				$c = "<div style=\"float: left; margin: 2px; margin-right: 5px; width: 9px; height: 9px; border: 1px solid #505050;\"></div>";
				
			
			
			return lockedMemo($c . t($caption == null ? t($field) : $caption), "&nbsp;");			
		}

		$x = new CheckBox($obj, $field, $caption, $attr, $addAttr);
		return $x->toHtml();
	}
