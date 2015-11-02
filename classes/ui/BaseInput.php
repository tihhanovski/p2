<?php
/*
 * Created on Mar 22, 2012
 *
 * (c) Ilja Tihhanovski, Intellisoft
 *
 */


	abstract class BaseInput
	{
		protected $objfield = "";
		protected $obj;
		protected $caption;
		protected $attr;
		protected $addAttr;

		public $field;

		function __construct($obj = null, $field = null, $caption = null, $attr = null, $addAttr = null)
		{
			 $this->obj = $obj;
			 $this->field = $field;
			 $this->caption = $caption;
			 $this->attr = $attr;
			 $this->addAttr = $addAttr;
		}

		function getMinimalRepresentation()
		{
			$v = $this->obj->getValue($this->field);
			return $v ? $this->getCaption() . ": " . $v : "";
		}

		protected static abstract function getType();

		protected function additionalAttributes(&$attr){}

		protected function htmlType()
		{
			return "input";
		}

		protected function inputContent()
		{
			return "";
		}

		protected function uniteArrays(&$attr, $addAttr)
		{
			if (is_array($addAttr))
			{
				foreach($addAttr as $name => $value)
				{
					if(!isset($attr["$name"]))
						$attr["$name"] = "";
					$attr["$name"] .= ($attr["$name"] ? " " : "") . $addAttr["$name"];
				}
			}
		}

		public function getLabelPart()
		{
			return "<div class=\"formLabel\">" .
					"<label for=\"" . $this->getControlId() . "\">".
					$this->getCaption() . "</label>" . "</div>";
		}

		function getControlId()
		{
			return $this->obj->fullpath . CHILD_DELIMITER . $this->field;
		}

		protected function getValueFromObj()
		{
			return sanitize($this->obj->getValue($this->field));
		}

		public function getInputPart()
		{
			$this->objValue = $this->getValueFromObj();

			if(!is_array($this->attr))
			{
				$this->attr = getDefaultAttr($this->getType());
				if(!is_array($this->attr))
					$this->attr = array();
			}

			$this->attr["id"] = $this->getControlId();
			$this->additionalAttributes($this->attr);

			$this->uniteArrays($this->attr, $this->addAttr);

			$a = "";
			if(is_array($this->attr))
				foreach($this->attr as $name => $value)
					$a .= " " . $name . "=\"" . $value . "\"";
			$htmlT = $this->htmlType();
			$inputContent = $this->inputContent();
			return "<$htmlT $a>$inputContent</$htmlT>";
		}

		function getCaption()
		{
			if(null == $this->caption)
				return t($this->field);
			else
				return t($this->caption);
		}

		protected function noDivInputPart($obj, $field, $caption = null, $attr = null, $addAttr = null)
		{
			global $context;

			$this->objfield = $obj->getValue($field);

			if(!is_array($attr))
				$attr = getDefaultAttr($this->getType());
			if(!is_array($attr))
				$attr = array();

			$attr["id"] = $this->getControlId();
			$this->additionalAttributes($attr);

			$this->uniteArrays($attr, $addAttr);

			$a = "";
			if(is_array($attr))
				foreach($attr as $name => $value)
					$a .= " " . $name . "=\"" . $value . "\"";
			$htmlT = $this->htmlType();
			$inputContent = $this->inputContent();
			return "<$htmlT $a>$inputContent</$htmlT>";
		}

		protected function prepareLabelRow($rowContent, $caption, $id)
		{
			return "<div class=\"formRow\"><div class=\"formLabel\">" .
					"<label for=\"$id\">". t($caption) . "</label>" . "</div>" .
					"<div class=\"formInputContainer\">" .
					$rowContent .
					"</div></div>";
		}

		public function toHtml()
		{
			return $this->inputWrapper($this->getInputPart(), $this->getLabelPart(), $this->getControlRowFullPathId());
		}

		function getControlRowFullPathId()
		{
			return "ctrlRow_{$this->obj->fullpath}_{$this->field}";
		}

		public function toVerticalHtml()
		{
			return "<div><div>" . $this->getCaption() . "</div>" . $this->getInputPart() . "</div>";
		}

		//TODO deprecated
		public function prepareInput($obj, $field, $caption = null, $attr = null, $addAttr = null)
		{
			 $this->obj = $obj;
			 $this->field = $field;
			 $this->caption = $caption;
			 $this->attr = $attr;
			 $this->addAttr = $addAttr;

			 return $this->toHtml();

			//$id = $obj->fullpath . CHILD_DELIMITER . $field;
			//$input = $this->getInputPart();
			//return $this->prepareLabelRow($input, $caption, $id);
		}

		public function inputWrapper($content, $label, $fullpath, $rowAddClass = "", $inputContainerAddClass = "")
		{
			return "<div class=\"formRow $fullpath" . ($rowAddClass ? " " . rowAddClass : "") . "\">" . 
						$label .
						"<div class=\"formInputContainer" . ($inputContainerAddClass ? " " . $inputContainerAddClass : "") . "\">" .$content . "</div>" . 
					"</div>";
		}
	}

	class HtmlWrapper extends BaseInput
	{
		function __construct($content, $caption)
		{
			$this->content = $content;
			$this->caption = $caption;
		}

		function getInputPart()
		{
			return $this->content;
		}

		protected static function getType()
		{
			return "";
		}

		function getControlRowFullPathId()
		{
			return "";
		}
	}

	function htmlWrapper($content, $caption)
	{
		$x = new HtmlWrapper($content, $caption);
		return $x->toHtml();
	}


	$_defaultAttrs;

	function initAttrs()
	{
		setDefaultAttr("text", array("class" => "textBox"));
		setDefaultAttr("textarea", array("class" => "textArea"));
		setDefaultAttr("datepicker", array("class" => "datepicker"));
		setDefaultAttr("select", array("class" => "select"));
		setDefaultAttr("double", array("class" => "numericDbl"));
		setDefaultAttr("checkbox", array("class" => "checkbox"));
		setDefaultAttr("comboadv", array("class" => "textBox"));
		setDefaultAttr("keysel", array("class" => "keySelInput"));
	}

	function setDefaultAttr($type, $arr)
	{
		global $_defaultAttrs;
		if(!is_array($_defaultAttrs))
			$_defaultAttrs = array();

		$_defaultAttrs[$type] = $arr;
	}

	function getDefaultAttr($type)
	{
		global $_defaultAttrs;
		if(!is_array($_defaultAttrs))
		{
			$_defaultAttrs = array();
			initAttrs();
		}

		if(isset($_defaultAttrs[$type]))
			return $_defaultAttrs[$type];
		else
			return null;
	}

	/**
	 * sanitize string for use in html controls
	 */
	function sanitize($s)
	{
		if(is_numeric($s))
			return $s;
		if(is_string($s))
			return htmlspecialchars($s);
	}

