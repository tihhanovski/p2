<?php
/*
 * Created on Mar 22, 2012
 *
 * (c) Ilja Tihhanovski, Intellisoft
 *
 */

	class BaseSelect extends BaseInput
	{
		public $nullable = true;
		public $nullableValue = "";
		public $nullableCaption = "";

		function getMinimalRepresentation()
		{

			if($v = $this->obj->getValue($this->field) && ($tr = $this->getTextualValue()))
				return $this->getCaption() . ": " . $tr;
			else
				return "";
		}

		function getTextualValue()
		{
			return "";
		}

		function getSqlSelectOptions()
		{
			$row = array();
	 		$ahtml = "";

	 		$value = $this->obj->getValue($this->field);

	 		if($this->sql)
	 		{
				$c = app()->getDBConnection();
		 		$q =& $c->query($this->sql);
		 		if(!app()->isDBError($q))
		 		{
		 			if($this->nullable)
		 				$ahtml .= "<option value=\"{$this->nullableValue}\">{$this->nullableCaption}</option>";
			 		while($q->fetchInto($row, DB_FETCHMODE_ORDERED))
			 		{
			 			$key = $row[0];
			 			$val = $row[1];
						$ahtml .= "<option value=\"" . sanitize($key) . "\"" .
							($key == $value ? " selected" : "") .
							">" . sanitize($val) . "</option>";
			 		}
		 		}
	 		}
	 		return $ahtml;
		}

		protected static function getType(){
			return "staticValue";
		}

		protected function additionalAttributes(&$attr){
			$attr["onblur"] = "JavaScript:fieldOnBlur(this);";
			//$attr["onchange"] = "JavaScript:fieldOnBlur(this);";	//chosen test
			$attr["type"] = "text";
 		}
 		protected function htmlType(){
 			return "select";
 		}
	}

	class BaseMultiselect extends BaseSelect
	{
		public $nullable = false;

		protected function additionalAttributes(&$attr)
		{
			$attr["onblur"] = "JavaScript:fieldOnBlur(this);";
			$attr["multiple"] = "on";
			$attr["size"] = $this->size;
 		}
	}

	class SelectSql extends BaseSelect
	{
		protected $sql = "";

		function __construct($obj = null, $field = null, $caption = null, $sql = "", $attr = null, $addAttr = null)
		{
			 $this->obj = $obj;
			 $this->field = $field;
			 $this->caption = $caption;
			 $this->attr = $attr;
			 $this->addAttr = $addAttr;
			 $this->sql = $sql;
		}

		function getTextualValue()
		{
	 		$value = $this->obj->getValue($this->field);
	 		if($this->sql)
	 		{
				$c = app()->getDBConnection();
		 		$q =& $c->query($this->sql);
		 		if(!app()->isDBError($q))
		 		{
			 		while($q->fetchInto($row, DB_FETCHMODE_ORDERED))
			 		{
			 			$key = $row[0];
			 			$val = $row[1];
			 			if($key == $value)
			 				return sanitize($val);
			 		}
		 		}
	 		}
	 		return "";
		}

 		protected function inputContent()
 		{
 			return $this->getSqlSelectOptions();
 		}

 		 public function prepareInput($obj, $field, $caption = null, $sql = null, $attr = null, $addAttr = null)
 		 {
 		 	if(!is_null($sql))
				$this->sql = $sql;
			return parent::prepareInput($obj, $field, $caption, $attr, $addAttr);
 		 }
	}

	class VerticalMultiselectSql extends MultiselectSql
	{
	}

	class MultiselectSql extends BaseMultiselect
	{
		protected $sql = "";
		protected $size = 10;

		function __construct($obj = null, $field = null, $caption = null, $sql = "", $attr = null, $addAttr = null)
		{
			 $this->obj = $obj;
			 $this->field = $field;
			 $this->caption = $caption;
			 $this->attr = $attr;
			 $this->addAttr = $addAttr;
			 $this->sql = $sql;
		}

		function getTextualValue()
		{
			$ret = array();
	 		$values = explode(",", $this->obj->getValue($this->field));
	 		if($this->sql)
	 		{
				$c = app()->getDBConnection();
		 		$q =& $c->query($this->sql);
		 		if(!app()->isDBError($q))
		 		{
			 		while($q->fetchInto($row, DB_FETCHMODE_ORDERED))
			 		{
			 			$key = $row[0];
			 			$val = $row[1];
			 			if(in_array($key, $values))
			 				$ret[] = $this->processValue($val);
			 		}
		 		}
	 		}
	 		return implode(";", $ret);
		}

		public function processValue($v)
		{
			return sanitize($v);
		}

		function getSqlSelectOptions()
		{
			$row = array();
	 		$ahtml = "";

	 		$values = explode(",", $this->obj->getValue($this->field));

	 		if($this->sql)
	 		{
		 		$q = app()->query($this->sql);
		 		if(!app()->isDBError($q))
		 		{
			 		while($q->fetchInto($row, DB_FETCHMODE_ORDERED))
			 		{
			 			$key = sanitize($row[0]);
			 			$val = $this->processValue($row[1]);
						$ahtml .= "<option value=\"$key\"";
						if(in_array($key, $values))
							$ahtml .= " selected";
						$ahtml .= ">$val</option>";
			 		}
		 		}
	 		}
	 		return $ahtml;
		}

 		protected function inputContent()
 		{
 			return $this->getSqlSelectOptions();
 		}

 		 public function prepareInput($obj, $field, $caption = null, $sql = null, $attr = null, $addAttr = null)
 		 {
 		 	if(!is_null($sql))
				$this->sql = $sql;
			return parent::prepareInput($obj, $field, $caption, $attr, $addAttr);
 		 }
	}

	class MultiselectSqlTranslated extends MultiselectSql
	{
		public function processValue($v)
		{
			return t(sanitize($v));
		}
	}

	class Select extends BaseSelect
	{
 		protected function inputContent()
 		{
 			$f = $this->field;
 			$v = isset($this->obj->$f) ? $this->obj->$f : null;
 			return getSelectOptionsTranslatedArray($this->arr, $v);
 		}

 		 public function prepareInput($obj, $field, $caption = null, $arr, $attr = null, $addAttr = null)
 		 {
			$this->arr = $arr;
			return parent::prepareInput($obj, $field, $caption, $attr, $addAttr);
 		 }
	}

	class SelectSqlTranslated extends SelectSql
	{
 		protected function inputContent()
 		{
 			return getSelectOptionsTranslated($this->sql, $this->obj->getValue($this->field), $this->nullable);
 		}
	}

	function selectSql($obj, $field, $caption = null, $sql = null, $attr = null, $addAttr = null)
	{
		if(!$obj->isEditable())
			return lockedMemo(app()->getLinkedCaption($obj->getLink($field)), $caption == null ? t($field) : $caption);
		$x = new SelectSql($obj, $field, $caption, $sql, $attr, $addAttr);
		return $x->toHtml();
	}

	function selectSqlNotNullable($obj, $field, $caption = null, $sql = null, $attr = null, $addAttr = null)
	{
		if(!$obj->isEditable())
			return lockedMemo(app()->getLinkedCaption($obj->getLink($field)), $caption == null ? t($field) : $caption);
		$x = new SelectSql($obj, $field, $caption, $sql, $attr, $addAttr);
		$x->nullable = false;
		return $x->toHtml();
	}

	function selectSqlTranslatedNotNullable($obj, $field, $caption = null, $sql = null, $attr = null, $addAttr = null)
	{
		if(!$obj->isEditable())
			return lockedMemo(app()->getLinkedCaption($obj->getLink($field)), $caption == null ? t($field) : $caption);
		$x = new SelectSqlTranslated($obj, $field, $caption, $sql, $attr, $addAttr);
		$x->nullable = false;
		return $x->toHtml();
	}

	function selectSqlNotLockable($obj, $field, $caption = null, $sql = null, $attr = null, $addAttr = null)
	{
		$x = new SelectSql($obj, $field, $caption, $sql, $attr, $addAttr);
		return $x->toHtml();
	}

	function multiselectSql($obj, $field, $caption = null, $sql = null, $attr = null, $addAttr = null)
	{
		$x = new MultiselectSql();
		return $x->prepareInput($obj, $field, $caption, $sql, $attr, $addAttr);
	}

	function multiselectSqlTranslated($obj, $field, $caption = null, $sql = null, $attr = null, $addAttr = null)
	{
		$x = new MultiselectSqlTranslated();
		return $x->prepareInput($obj, $field, $caption, $sql, $attr, $addAttr);
	}

	function selectPrintForm($obj, $caption = null, $attr = null, $addAttr = null)
	{
		if(is_array($items = app()->getRegistryDescriptor()->getAvailableForms()))
		{
			$x = new Select();
			return $x->prepareInput($obj, "form", $caption, $items, $attr, $addAttr);
		}
		return "";
	}

	function select($obj, $field, $caption = null, $options = null, $attr = null, $addAttr = null)
	{
		$x = new Select();
		return $x->prepareInput($obj, $field, $caption, $options, $attr, $addAttr);
	}

	function selectSqlTranslated($obj, $field, $caption = null, $sql = null, $attr = null, $addAttr = null, $nullable = false)
	{
		if(!$obj->isEditable())
			return lockedMemo(app()->getLinkedCaptionTranslated($obj->getLink($field)), $caption == null ? t($field) : $caption);
		$x = new SelectSqlTranslated();
		$x->nullable = $nullable;
		return $x->prepareInput($obj, $field, $caption, $sql, $attr, $addAttr);
	}

	function dbToArray($sql)
	{
		$ret = array();
		$row = array();
		$c = app()->getDBConnection();
 		$q =& $c->query($sql);
		if(!app()->isDBError($q))
	 		while($q->fetchInto($row, DB_FETCHMODE_ORDERED))
	 			$ret[] = $row[0];
 		return $ret;
	}

	function getSelectOptionsArray($arr, $value = null, $nullable = true)
	{
		$ahtml = "";
 		if($nullable)
 			$ahtml .= "<option value=\"\"></option>";
		foreach ( $arr as $v)
		{
			$ahtml .= "<option value=\"" . $v[0] . "\"" . ($v[0] == $value ? " selected" : "") . ">" . $v[1] . "</option>";
		}
		return $ahtml;
	}

	function getSelectOptions($sql, $value = null, $nullable = true, $nullId = "", $nullCaption = "")
	{
		$row = array();
 		$ahtml = "";

		$c = app()->getDBConnection();
 		$q =& $c->query($sql);
 		if(!app()->isDBError($q))
 		{
 			if($nullable)
 				$ahtml .= "<option value=\"$nullId\">$nullCaption</option>";
	 		while($q->fetchInto($row, DB_FETCHMODE_ORDERED))
	 		{
	 			$key = sanitize($row[0]);
	 			$val = sanitize($row[1]);
				$ahtml .= "<option value=\"$key\"";
				if($key == $value)
					$ahtml .= " selected";
				$ahtml .= ">$val</option>";
	 		}
 		}
 		return $ahtml;
	}

	function getSelectOptionsTranslated($sql, $value = null, $nullable = false, $nullableValue = "", $nullableCaption = "")
	{
		$row = array();
 		$ahtml = "";

		$c = app()->getDBConnection();
 		$q =& $c->query($sql);
 		if(!app()->isDBError($q))
 		{
		 	if($nullable)
		 		$ahtml .= "<option value=\"" . $nullableValue . "\">" . t($nullableCaption) . "</option>";
	 		while($q->fetchInto($row, DB_FETCHMODE_ORDERED))
	 		{
	 			$key = sanitize($row[0]);
	 			$val = sanitize($row[1]);
				$ahtml .= "<option value=\"$key\"";
				if($key == $value)
					$ahtml .= " selected";
				$ahtml .= ">" . t($val) . "</option>";
	 		}
 		}
 		return $ahtml;
	}

	function getSelectOptionsTranslatedArray($arr, $value = null)
	{
		$html = "";
		foreach ( $arr as $key => $val )
			$html .= "<option value=\"" . sanitize($key) . "\"" . ($value == $key ? "selected" : "") . ">" . t(sanitize($val)) . "</option>";
		return $html;
	}