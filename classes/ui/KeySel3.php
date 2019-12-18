<?php

 	function keySel3($obj, $field, $caption = null, $attr = null, $addAttr = null)
 	{
		$x = new KeySel3($obj, $field, $caption, $attr, $addAttr);
		return $x->toHtml();
	}

	class KeySel3 extends BaseInput
	{
    private function getSource()
    {
      if(!isset($this->source))
      {
        $this->source = $this->obj->getForeignKeyTable($this->field);
        if(!$this->source)
          throw new WFWException("No foreign key for " . $this->obj->__table . "->" . $this->field);
      }
      return $this->source;
    }

    public function toHtml()
    {
      if(!$this->obj->isEditable())
  			return lockedMemo(app()->getLinkedCaption($this->obj->getLink($this->field)), $this->caption == null ? t($this->field) : $this->caption);
      else
        return parent::toHtml();
    }

		protected static function getType()
		{
			return "keysel3";
		}

		protected function additionalAttributes(&$attr)
		{
			$attr["type"] = "text";
 		}

    public function getLabelPart()
		{
			return "<div class=\"formLabel\">" .
					"<label for=\"" . $this->getControlId() . "Entry\">".
					$this->getCaption() . "</label>" . "</div>";
		}

    /*
    id:<input type="text" id="projectId" value="" disabled/>
    entry:<input type="text" id="projectId_entry" value=""/>
    label:<input type="text" id="projectId_memo" value="" disabled/>
    */

		function getInputPart()
		{
        $this->objValue = $this->getValueFromObj();

        $ctrlId = $this->getControlId();
        $source = $this->getSource();
        $val = $this->objValue;

  			if(!is_array($this->attr))
  			{
    				$this->attr = getDefaultAttr($this->getType());
    				if(!is_array($this->attr))
      					$this->attr = array();
  			}

  			$this->attr["id"] = $ctrlId . "Entry";
  			$this->additionalAttributes($this->attr);

  			$this->uniteArrays($this->attr, $this->addAttr);

  			$a = "";
  			if(is_array($this->attr))
  				foreach($this->attr as $name => $value)
  					$a .= " " . $name . "=\"" . $value . "\"";
  			$htmlT = $this->htmlType();
  			$inputContent = $this->inputContent();
  			$ret = "<$htmlT $a>$inputContent</$htmlT>";


  			return $ret .
          "<input type=\"hidden\" id=\"" . $ctrlId . "\" value=\"" . $this->objValue . "\" />" .
          "<span id=\"" . $ctrlId . "Label\" class=\"keySelNameField\"></span>" .
          wrapScript("\$(function(){ setKeySel3('$ctrlId', '$source'); setKeySel3Value('$ctrlId', '$source', '$val'); });");
  		}
	}

 	class KeySelSetup3
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
			return wrapScript(
					"\$(function() {" .
					"setKeySel(\"{$this->obj->fullpath}_{$this->field}\", " .
						"\"{$this->cls}\", \"" . $o->getPrimaryKeyField() . "\", $cols, " .
						"\"$value\", \$(window).width - 300, null, " .
						(app()->canUpdate($this->cls) ? "true" : "false") . ", \"{$this->cls}\", " .
						(app()->canSelect($this->cls) ? "true" : "false") . ", \"" . $this->af . "\");" .
					"});");
		}
	}
