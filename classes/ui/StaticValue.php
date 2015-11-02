<?php
/*
 * Created on Mar 22, 2012
 *
 * (c) Ilja Tihhanovski, Intellisoft
 *
 */
 
 
	class StaticValue extends BaseInput
	{
		protected static function getType()
		{
			return "staticValue";
		}
		
		protected function additionalAttributes(&$attr)
		{
			$attr["disabled"] = "true";
			//$attr["style"] = "border: none;";
			$attr["type"] = "text";
			$attr["value"] = $this->objValue;
			$attr["class"] = "staticValue";
 		}
	}
	
	function staticValue($obj, $field, $caption = null)
	{
		$x = new StaticValue($obj, $field, $caption);
		return $x->toHtml();
	}

	class StaticProvidedValue extends BaseInput
	{
		public $providedValue = "";

		protected static function getType()
		{
			return "staticProvidedValue";
		}
		protected function htmlType(){
 			return "div";
 		}
 		protected function inputContent()
 		{
 			return $this->providedValue;
 		}

	}
	
	function staticProvidedValue($obj, $field, $caption, $value)
	{
		$x = new StaticProvidedValue();
		$x->providedValue = $value;
		return $x->prepareInput($obj, $field, $caption);
	}
