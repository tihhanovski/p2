<?php
/*
 * Created on Sep 14, 2011
 *
 * (c) Ilja Tihhanovski, Intellisoft
 *
 */

	require_once 'DB/DataObject.php';

	define("SECS_DAY", 86400);

	define("ENUMERATOR_NR_FIELD", "nr");
	define("ENUMERATOR_GRP_FIELD", "grp");
	define("PROPERTY_PREFIX", "dyn");
	define("SPECIALVALUE_DEFAULT", "##{default}");
	define("SPECIALVALUE_MSELECT", "##{mselect}");
	define("COPY_STRING_MODIFIER", "CopyStringModifier");
	define("CAPTION_SEPARATOR", " : ");

	define("LOG_ACTION_INS", 0);
	define("LOG_ACTION_UPD", 1);
	define("LOG_ACTION_DEL", 2);
	define("LOG_TYPE_NORMAL", 0);
	define("LOG_TYPE_DOC", 1);

	define("LIST_DELIMITER", ";");

	const NULL_VALUE = "NULL";

	function is_field_ok_for_json($name){
		return (strpos($name, "_") !== 0
			&& $name !== "N"
			&& $name !=="parentID"
			&& $name !== "primaryKey"
			&& $name !== "rights");
	}

	class WFWObject extends DB_DataObject
	{

		public $fullpath;
		public $logEnabled = true;

	    protected $changed = array();
	    protected $captionFields;

	    public function comment($comment)
	    {
	    	if($this->isInDatabase())
	    	{
	    		$c = app()->dbo("objcomment");
	    		$c->objreg = $this->__table;
	    		$c->objId = $this->getIdValue();
	    		$c->comment = $comment;
	    		$c->userId = app()->user()->id;
	    		$c->dt = app()->now();
	    		$r = $c->insert();
	    		if($r && property_exists($this, "mdCommentsCount"))
	    		{
	    			$sql = "update " . $this->__table .
	    				" set mdCommentsCount = mdCommentsCount + 1 " .
	    				"where " . $this->getPrimaryKeyField() . " = " . $this->getIdValue();
	    			app()->query($sql);
	    		}
	    		return $r;
	    	}
	    	return false;
	    }

	    public function getComments()
	    {
	    	$sql = "select c.id, c.comment, c.dt, u.uid
				from objcomment c
				left join webuser u on u.id = c.userId where objreg = " . quote($this->__table) . "
				and objid = " . (int)$this->getIdValue();
			return app()->queryAsArray($sql, DB_FETCHMODE_ASSOC, array("dt" => FORMAT_DATETIME));
	    }

	    /**
	     * called after all saved
	     */
	    function finalizeSave()
	    {
	    }

	    function keySelAdditionalSql($m)
	    {
	    	return "";
	    }

	    function checkLastSaved()
	    {
	    	if($this->__table)
		    	if($this->isInDatabase())
		    		if(property_exists($this, "mdUpdated"))
		    		{
		    			$updField = "";
		    			foreach (array("mdUpdaterId", "mdUpdaterID") as $f)
		    				if(property_exists($this, $f))
		    				{
		    					$updField = $f;
		    					break;
		    				}

		    			$sql = "select mdUpdated as d" .
		    				($updField ? ", $updField as u" : "") .
		    				" from " . $this->__table .
		    				" where " . $this->getPrimaryKeyField() . " = " . filter_var($this->getIdValue(), FILTER_SANITIZE_NUMBER_INT);

		    			$a = app()->queryAsArray($sql, DB_FETCHMODE_OBJECT);
		    			if(is_array($a))
		    				if(isset($a[0]))
		    				{
		    					$d = $a[0]->d;
		    					if($d > $this->mdUpdated)
		    						$this->addWarning(new Warning("msgDocUpdatedByOther"));
		    				}
		    		}
	    }

	    function canCopy()
	    {
	    	return false;
	    }

	    function validateDocument()
	    {
	    	$ret = true;
	    	if(isset($this->validators) && is_array($this->validators))
		    	foreach ( $this->validators as $field => $val )
		    	{
		    		$ret = ($this->validateValue($field, $this->$field, $this->$field)) & $ret;
		    	}
	    	return $ret;
	    }

		function validateFieldIsEmpty($field)
		{
			if(!$this->$field)	//TODO
			{
				$this->addWarning(new Warning("Field empty", $field, WARNING_ERROR));
				return true;
			}

			return false;
		}

	    function addWarning($w)
	    {
	    	if($w->field)
	    		$w->field = $this->fullpath . CHILD_DELIMITER . $w->field;
	    	app()->addWarning($w);
	    }

	    /*
	     * if all is fine, returns ""
	     */
	    function getLockErrors()
	    {
	    	return "";
	    }

	    function getDocOpenUrl()
	    {
	    	return app()->url("?action=open&registry=" . $this->__table . "&id=" . $this->getIdValue());
	    }

	    function getLinkedCaption($fields = null, $sep = " : ", $target = "_blank")
	    {
	    	return app()->getLinkedCaption($this, $fields, $sep, $target);
	    }

	    protected function advancedComboColumns()
	    {
	    	return array();
	    }

	    function getCopySource()
	    {
	    	if(isset($this->dynCopyOfTable) && isset($this->dynCopyOfId) && $this->dynCopyOfTable && $this->dynCopyOfId)
	    	{
	    		$r = app()->get($this->dynCopyOfTable, $this->dynCopyOfId);
	    		if(app()->isDBError($r))
	    			$r = null;
	    		return $r;
	    	}
	    	else
	    		return null;
	    }

	    public function keySelColumns()
	    {
	    	return array();
	    }

	    function getAdvancedComboSetup($obj, $field)
	    {
	    	return new AdvancedComboSetup($obj, $field, $this->__table, $this->advancedComboColumns());
	    }

	    function getKeySelSetup($obj, $field)
	    {
	    	//$o = $obj->__table;
	    	//die("return new KeySelSetup($o, $field, $this->__table, $this->keySelColumns());");
	    	return new KeySelSetup($obj, $field, $this->__table, $this->keySelColumns());
	    }

	    function getForeignKeyTable($field)
	    {
	    	$links = $this->links();
            if (is_array($links))
                if ($links[$field])
                {
                    list($table,$link) = explode(':', $links[$field]);
                    return $table;
                }

            return "";
	    }

	    function getCaption($fields = null, $sep = null)
	    {
	    	if(is_null($sep))
	    		$sep = isset($this->captionSeparator) ? $this->captionSeparator : CAPTION_SEPARATOR;
	    	if(is_null($fields))
	    		if(isset($this->captionFields))
	    			$fields = $this->captionFields;

	    	if(is_null($fields))
	    		return "";

	    	if(!is_array($fields))
	    		$fields = array($fields);

	    	$ret = array();

	    	foreach ( $fields as $fld )
	    		$ret[] = sanitize($this->getValue($fld));

	    	return implode($sep, $ret);
	    }

	    function getCaptionTranslated($fields = null, $sep = " : ")
	    {
	    	if($fields == null)
	    		if(isset($this->captionFields))
	    			$fields = $this->captionFields;

	    	if($fields == null)
	    		return "";

	    	if(!is_array($fields))
	    		$fields = array($fields);

	    	$ret = array();

	    	foreach ( $fields as $fld )
	    		$ret[] = t($this->getValue($fld));

	    	return implode($sep, $ret);
	    }

		function setupFormat($field, $format)
		{
			$this->formats[$field] = $format;
		}

		public function getFormat($field)
		{
			if(isset($this->formats))
				if(is_array($this->formats))
					if(isset($this->formats[$field]))
						return $this->formats[$field];
			return FORMAT_DEFAULT;

		}

		function getFormatter($field)
		{
			if(isset($this->formats))
				if(is_array($this->formats))
					if(isset($this->formats[$field]))
						return getFormatter($this->formats[$field]);

			return getFormatter(FORMAT_DEFAULT);
		}

	    function setChanged($field)
	    {
	    	$this->changed[$field] = true;
	    }

	    function setUnchanged($field)
	    {
	    	unset($this->changed[$field]);
	    }

	    /**
	     * Links other object with this by adding record to table objectlink
	     * @param WFWObject $obj object to link
	     */
	    public function addLinkedObject($obj)
	    {
	    	if(($id1 = $this->getIdValue()) && ($id2 = $obj->getIdValue()))
	    	{
	    		$l = app()->dbo("objectlink");
	    		$l->robject1 = $this->__table;
	    		$l->robject2 = $obj->__table;
	    		$l->id1 = $id1;
	    		$l->id2 = $id2;
	    		return $l->insert();
	    	}
	    	return false;
	    }

	    /**
	     * Return linked objects sorted by link id (in most cases it is creation order)
	     * @param string $sortOrder
	     * @return array<WFWObject>
	     */
	    public function getLinkedObjects($sortOrder = "asc")
	    {
	    	$ret = array();
	    	$l = app()->dbo("objectlink");
	    	$robj = $this->escape($this->__table . $this->getIdValue());
	    	$l->whereAdd("(concat(robject1, id1) = '$robj') or (concat(robject2, id2) = '$robj')");
	    	$l->orderBy("id $sortOrder");
	    	$l->mainObject = $this->__table;
	    	if($l->find())
	    		while($l->fetch())
	    			$ret[] = $l->getObject();

	    	return $ret;
	    }

	    /**
	     * Return linked emails
	     * @return array<WFWEmail>
	     */
	    public function getLinkedEmails()
	    {
	    	$ret = array();
	    	foreach($this->getLinkedObjects("desc") as $link)
	    		if($link->__table == "email")
	    			$ret[] = $link;
	    	return $ret;
	    }

	    /**
	     * check if given field is changed
	     * @param string $field Field name
	     * @return boolean
	     */
		public function isChanged($field)
		{
			return isset($this->changed[$field]);
		}

		/**
		 * Returns object fields prepared for export in JSON format
		 * @return array
		 */
		public function get_array_data_for_json($arr){
			$result = array();
			foreach ($this->$arr as $a)
			{
				if(is_object($a) && method_exists($a, "get_data_for_json"))
					$result[] = $a->get_data_for_json();
				else
					$result[] = $a;
			}
			return $result;
		}

		function isNotSanitizedField($f)
		{
			return true;//$f == "LinkedCaption" || $f == "link";
		}

		function get_data_for_json()
		{
			$ref = new ReflectionObject($this);
    		$pros = $ref->getProperties(ReflectionProperty::IS_PUBLIC);
    		$result = array();

    		reset($pros);
    		foreach ($pros as $pro)
    		{
        		$name = $pro->getName();
        		if(isset($this->$name))
	        		if (is_field_ok_for_json($name))
	        		{
	        			if (is_array($this->$name))
	        				$result[$name] = $this->get_array_data_for_json($name);
	        			else
	        				$result[$name] = $this->getValue($name);
	        		}
	    	}
    		$result["__isNotSaved"] = $this->isNew();

			if(isset($this->captionFields) && is_array($this->captionFields))
				$result["__caption"] = $this->getCaption();

			if($this->isClosable())
			{
				$result["__closable"] = true;
				$result["__closed"] = $this->isClosed();
			}

    		return $result;
		}

		protected $dynamicProperties = true;

		function hasDynamicProperties()
		{
			return isset($this->dynamicProperties) && $this->dynamicProperties;
		}

		private function getDynProperty()
		{
			$p = app()->dbo("objectproperty");
			$p->robject = $this->getRObjectID();
			return $p;
		}

		function getRObjectID()
		{
			return $this->__table . $this->getIdValue();
		}

		public $dynamicPropertiesLoaded = false;

		function loadDynamicPropertiesIfNotLoaded()
		{
			if(!$this->dynamicPropertiesLoaded)
				$this->loadDynamicProperties();
		}

		function loadDynamicProperties()
		{
			if($this->isDBError($p = $this->getDynProperty()))
				return;
			if($p->find())
				while($p->fetch())
				{
					$field = PROPERTY_PREFIX . $p->name;
					$this->$field = $p->value;
				}
			$this->dynamicPropertiesLoaded = true;
		}

		function isDBError($obj)
		{
			return app()->isDBError($obj);
		}

		function saveDynamicProperties()
		{
			$ref = new ReflectionObject($this);
			$pros = $ref->getProperties(ReflectionProperty::IS_PUBLIC);
			foreach($pros as $pro)
			{
				$pl = strlen(PROPERTY_PREFIX);
				$name = $pro->getName();
				if ((strpos($name, PROPERTY_PREFIX) !== false) && ($name != "dynamicPropertiesLoaded"))
				{
					$p = $this->getDynProperty();
					$p->name = substr($name, $pl);
					$p->find(true);
					$p->value = $this->$name;
					$p->persist();
				}
			}
		}

		function copyDynamicProperties($destination)
		{
			$ref = new ReflectionObject($this);
			$pros = $ref->getProperties(ReflectionProperty::IS_PUBLIC);
			foreach($pros as $pro)
			{
				$pl = strlen(PROPERTY_PREFIX);
				$name = $pro->getName();
				if (strpos($name, PROPERTY_PREFIX) !== false)
					$destination->$name = $this->$name;
			}
		}

		function persist()
		{
			if($this->isInDatabase())
				return $this->update();
			else
				return $this->insert();
		}

		function setValueByPath($path, $value, $tree)
		{
			$this->checkLastSaved();	//TODO check speed, ilja 10.01.2014
			if(isIndexed($path))
			{
				$children = $tree[$this->__table];

				list($var, $path2) = explode(CHILD_DELIMITER, $path, 2);
				if(isIndexed($var))
				{
					list($v, $index) = explode(INDEX_DELIMITER, $var);
					$arr = $this->$v;
					if(is_array($arr))
						$obj = $arr[$index];
				}
				else
					$obj = $this->$var;

				if(is_object($obj))
				{
					$obj->setValueByPath($path2, $value, $tree);
					$this->childValueChanged($path2, $value, $tree);
				}
			}
			else
				$this->setValue($path, $value);
		}

		/**
		 * Here we can respond to children changing
		 * for example when document row total changes we could recalculate document total.
		 */
		public function childValueChanged($path, $value, $tree)
		{
		}

		/**
		 * Here we can respond to children delete
		 * for example when document row total changes we could recalculate document total.
		 */
		public function childDeleted($path, $tree)
		{
		}

		function decodeValue($field, $value)
		{
			return $this->getFormatter($field)->decodeHuman($value);
		}

		function encodeValue($field, $value)
		{
			return $this->getFormatter($field)->encodeHuman($value);
		}

		function setDefaultValues()
		{
			$ref = new ReflectionObject($this);
			$pros = $ref->getProperties(ReflectionProperty::IS_PUBLIC);
			foreach ($pros as $pro)
			{
        		$name = $pro->getName();
        		if(($s = $this->getDefaultValue($name)) !== "")
        			$this->$name = $s;
			}
		}

		function getDefaultValue($field)
		{
			$m = "getDefaultFor_$field";
			if(method_exists($this, $m))
				return $this->$m();
			if(isset($this->defaults) && is_array($this->defaults) && isset($this->defaults[$field]))
				return $this->defaults[$field];
			return "";
		}

		function setValue($field, $value, $setChanged = true)
		{
			$updateIfValidated = true;
			$sv = $value;

			if($value === SPECIALVALUE_DEFAULT)
				$value = $this->getDefaultValue($field);
			else
			{
				if(strpos($value, SPECIALVALUE_MSELECT) !== false)
				{
					$updateIfValidated = false;
					list($x, $s1) = explode("}", $value);
					list($idx, $sel) = explode(":", $s1);

					if($this->$field)
						$va = explode(",", $this->$field);
					else
						$va = array();

					if($sel)
					{
						if(array_search($idx, $va) === false)
							$va[] = $idx;
					}
					else
					{
						if(false !== $key = array_search($idx, $va))
							unset($va[$key]);
					}

					//delete all non-numerics from list
					foreach ($va as $k => $v)
						if(!is_numeric($v))
							unset($va[$k]);

					$value = implode(",", $va);
				}

				$value = $this->decodeValue($field, $value);
			}

			$oldValue = isset($this->$field) ? $this->$field : "";	//TODO is "" ok?
			$this->$field = $value;


			if($oldValue != $value)
				if($setChanged)
					$this->setChanged($field);

			if($this->validateValue($field, $oldValue, $value))
				if($updateIfValidated)
					$this->addUpdated($field, $value);
		}

		function addUpdated($field, $value = null)	//TODO value not needed
		{
			$o = new EmptyObject();
			$o->id = $this->fullpath . "_" . $field;
			$o->value = $this->getValue($field);
			app()->addUpdated($o);
		}

		function getValue($field)
		{
			if($field)
				if(isset($this->$field))
					return $this->encodeValue($field, $this->$field);
			return "";	//TODO ?
		}

		function validateValue($field, $oldValue, $newValue)
		{
			if(isset($this->validators[$field]))
			{
				if(is_array($validators = $this->validators[$field]))
				{

					foreach ( $validators as $validator )
					{
						$ret = getValidator($validator)->validate($this, $field);
						if(!$ret)
							return false;
					}
				}
				else
					return getValidator($this->validators[$field])->validate($this, $field);
			}
			return true;
		}

		function addChild($path, $tree)
		{
			$children = $tree[$this->__table];
			if(isIndexed($path))
			{
				list($var, $subpath) = explode(CHILD_DELIMITER, $path, 2);
				if(isIndexed($var))
				{
					list($v, $index) = explode(INDEX_DELIMITER, $var);
					if(is_array($arr = $this->$v))
					{
						if(isset($arr[$index]))
							if(is_object($child = $arr[$index]))
								return $child->addChild($subpath, $tree);
					}
				}
			}
			else
			{
				$class = $children[$path];
				$c = DB_DataObject::factory($class);
				if(!is_a($c, "WFWObject"))
					$c = new $class;
				if(!isset($this->$path))
					$this->$path = array();
				$a = $this->$path;
				$a[] = $c;
				$key = array_search($c, $a, true);
				$this->$path = $a;
				$c->fullpath = $this->fullpath . CHILD_DELIMITER . $path . INDEX_DELIMITER . $key;
				$c->setDefaultValues();
				$this->initializeChild($c);
				return array($c, $key);
			}
		}

		/**
		 * initialize child
		 * @param DB_Dataobject $obj
		 */
		public function initializeChild($obj)
		{

		}

		function copyProperties($from, $to, $list)
		{
			foreach ( $list as $p )
				if(isset($from->$p))
					$to->$p = $from->$p;
		}

		function getLastIndex()
		{
			$a = explode(INDEX_DELIMITER, $this->fullpath);
			return $a[count($a) - 1];
		}

		function addChild2($alias, $c)
		{
			//$alias should be array in this
			$a = $this->$alias;
			$a[] = $c;
			$key = array_search($c, $a, true);
			$this->$alias = $a;
			$c->fullpath = $this->fullpath . CHILD_DELIMITER . $alias . INDEX_DELIMITER . $key;
		}

	    function getParentIdField($table)
	    {
	    	if(is_array($links = $this->links()))
	    	{
	    		reset($links);
	    		while(list($k, $v) = each($links))
	    		{
	    			$a = explode(":", $v);
	    			if($a[0] == $table)
	    				return $k;
	    		}
	    	}
	    }

	    function hasSingleKeyField()
	    {
	    	if(is_array($keys = $this->keys()))
	    		return (count($keys) == 1);
	    	return false;
	    }

	    function getPrimaryKeyField()
	    {
	    	$keys = $this->keys();
	    	if(is_array($keys))
	    	{
	    		if(count($keys) != 1)
	    			die("wrong number of keys for " . $this->__table . "<hr/><pre>" . print_r($this, true) . "</pre><hr/>");
	    		return $keys[0];
	    	}
	    	else
	    	{
	    		die("no keys array for " . $this->__table);
	    	}
	    }

	    function saveChildren($tree)
	    {
	    	if(is_array($tree))
	    		if(isset($tree[$this->__table]))
	    			if(is_array($cl = $tree[$this->__table]))
	    			{
						reset($cl);
						while(list($k, $v) = each($cl))
							$this->saveChildrenByClass($k, $v, $tree);
	    			}
	    	if($this->hasDynamicProperties())
	    		$this->saveDynamicProperties();

	    }

	    function stripPrimaryKeys($tree)
	    {
	    	$kf = $this->getPrimaryKeyField();
	    	unset($this->$kf);
	    	if(is_array($tree))
	    		if(isset($tree[$this->__table]))
	    			if(is_array($cl = $tree[$this->__table]))
	    				foreach ( $cl as $k => $v )
							$this->prepareCopyChildrenByClass($k, $v, $tree);
	    }

		/**
		 * Modifies copied document fields, adds "Copy" eg to field "Name" etc
		 */
	    function modifyCopyName()
	    {
	    	if(isset($this->captionFields) && is_array($this->captionFields) && isset($this->captionFields[0]))
	    	{
	    		$f = $this->captionFields[0];
	    		$t = t(COPY_STRING_MODIFIER);
	    		if(COPY_STRING_MODIFIER != $t)
	    			$this->setValue($f, sprintf($t, $this->$f));
	    	}
	    }

	    function appendCopyString($f)
	    {
			$c = t("copy");
			$s = $this->$f;
			if($pos = stripos($s, $c))
				$s = substr($s, 0, $pos + strlen($c));
			else
				$s .= " " . $c;

			list($cnt) = app()->rowFromDB("select count(*) from " . $this->__table . " where " . $f . " like '" . $this->escape("%" . $s . "%") . "'");
			if($cnt)
				$s .= " " . (1 + $cnt);
			return $s;
	    }

	    function prepareCopy($tree)
	    {
	    	$this->dynCopyOfTable = $this->__table;
	    	$this->dynCopyOfId = $this->getIdValue();

	    	$this->modifyCopyName();
	    	$this->stripPrimaryKeys($tree);
	    	if($this->isLocked())
	    		$this->unlock();
	    }

	    function prepareCopyChildrenByClass($var, $cls, $tree)
	    {
	    	$array = $this->$var;
	    	$thisID = $this->getPrimaryKeyField();

	    	if(is_array($array))
				foreach ( $array as $k => $c )
		    	{
		    		$cf = $c->getPrimaryKeyField();
		    		$parentID = $c->getParentIdField($this->__table);
		    		unset($c->$parentID);
		    		$c->prepareCopy($tree);
		    	}
	    }

		function loadChildren($tree)
		{
	    	if(is_array($tree))
	    		if(isset($tree[$this->__table]))
	    			if(is_array($cl = $tree[$this->__table]))
	    			{
						reset($cl);
						while(list($k, $v) = each($cl))
							$this->loadChildrenByClass($k, $v, $tree);
	    			}

	    	if($this->hasDynamicProperties())
	    		$this->loadDynamicProperties();
	    	$this->loadAdditionalData();
		}

		/**
		 * @return bool
		 * @param string $f field name
		 */
		protected function isLinkedField($f)
	    {
			return $this->hasLinkedFields() && isset($this->linkedFields[$f]);
		}

		/**
		 * @return void
		 * @param string $k
		 * @param string $c
		 */
		protected function loadLinkedField($k, $c)
		{
			if(is_object($o = $this->getLink($k)))
				if(!app()->isDBError($o))
					$this->setValue($c, $o->getFirstCaption());
		}

		/**
		 * @return bool
		 * Returns true if object has linked fields.
		 */
		protected function hasLinkedFields()
		{
			return isset($this->linkedFields) && is_array($this->linkedFields);
		}

		/**
		 * loads linked fields data
		 */
		protected function loadLinkedFields()
		{
			if($this->hasLinkedFields())
				foreach ($this->linkedFields as $k => $c)
					$this->loadLinkedField($k, $c);
		}

		/**
		 * saves linked field's data
		 * @param string $k
		 * @param string $c
		 */
		protected function saveLinkedField($k, $c)
		{
			if($this->$c)
				if($t = $this->getForeignKeyTable($k))
				{
					$o = app()->dbo($t);
					$o->setFirstCaption($this->$c);
					$o->find(true);
					if($o->isInDatabase())
						$this->$k = $o->getIdValue();
					else
					{
						$o = app()->dbo($t);
						$o->setFirstCaption($this->$c);
						$o->insert();
						$this->$k = $o->getIdValue();
					}
				}
		}

		/**
		 * saves linked fields data
		 */
		protected function saveLinkedFields()
		{
			if($this->hasLinkedFields())
				foreach ($this->linkedFields as $k => $c)
					$this->saveLinkedField($k, $c);
		}

		function loadAdditionalData()
		{
			$this->loadLinkedFields();
		}

		/**
		 * alias for isNew
		 * @return bool
		 */
	    function isInDatabase()
	    {
	    	return !$this->isNew();
	    }

	    /**
		 * @return bool
	     */
		function isNew()
		{
			$f = $this->getPrimaryKeyField();
			return !(isset($this->$f) && $this->$f);
		}

	    function loadChildrenByClass($var, $cls, $tree)
	    {
	    	//obj files loading
	    	if($cls == "robjfile")
	    	{
	    		$this->objFilesVariable = $var;
	    		return $this->getObjFiles($var);
	    	}

	    	$c = DB_DataObject::factory($cls);

	    	$parentID = $c->getParentIdField($this->__table);
	    	$thisID = $this->getPrimaryKeyField();
	    	if(!$parentID)
	    		throw new Exception("ParentId not found for $cls >- {$this->__table}");
	    	if(!$thisID)
	    		throw new Exception("Primary key not found for {$this->__table}");

	    	$array = array();
	    	$c->$parentID = $this->$thisID;
	    	$this->initChildOrder($c);	//was $c->initOrder();
	    	if($c->find())
	    		while($c->fetch())
	    			$this->appendChild($array, clone $c, $var, $tree);
	    	$this->$var = $array;
	    }

	    public function initChildOrder($c)
	    {
	    	$c->initOrder();
	    }

	    /**
	     * Adds new child document
	     */
	    protected function appendChild(&$array, $c2, $var, $tree)
	    {
	    	$array[] = $c2;
	   		$key = array_search($c2, $array);
	   		$c2->fullpath = $this->fullpath . CHILD_DELIMITER . $var . INDEX_DELIMITER . $key;
	   		$c2->loadChildren($tree);
	   		$c2->loadAdditionalData();
	    }

	    /**
	     * Saves dbo document children data to database
	     * @param string $var
	     * @param string $cls child class name
	     * @param array $tree
	     * @return void
	     */
	    public function saveChildrenByClass($var, $cls, $tree)
	    {
	    	if($cls == "robjfile")
	    		return;
	    	//echo "saveChildrenByClass($var, $cls, $tree)\n";
	    	if(isset($this->$var))
	    	{
		    	$array = $this->$var;
		    	//print_r($array);
		    	if(!$thisID = $this->getPrimaryKeyField())
		    		throw new WFWException("No primary key field");
		    	if(is_array($array))
		    		foreach ($array as $k => $c)
			    	{
			    		$parentID = $c->getParentIdField($this->__table);
			    		if(!$parentID)
			    			throw new WFWException("No parent Id field found");
			    		$cID = $c->getPrimaryKeyField();
			    		$c->$parentID = $this->$thisID;

			    		//echo "parentID: $parentID\n thisID: $thisID\n id: $c->id";

			    		if($c->isNew())
			    		{
			    			$le = $c->logEnabled;
			    			$c->setLogEnabled(false);
			    			$c->insert();
			    			$c->saveChildren($tree);
			    			$c->setLogEnabled($le);
			    		}
			    		else
			    		{
			    			if($c->willBeDeleted())
			    			{
			    				$c->delete();
			    				unset($array[$k]);
			    			}
			    			else
			    			{
				    			$le = $c->logEnabled;
				    			$c->setLogEnabled(FALSE);
			    				$c->update();
				    			$c->saveChildren($tree);
				    			$c->setLogEnabled($le);
			    			}
			    		}
			    	}
			    $this->$var = $array;
	    	}
		}

	    function willBeDeleted()
	    {
	    	if(isset($this->todelete))
	    		return $this->todelete;
	    	return false;
	    }

	    function switchChildren(&$c1, &$c2, $collectionName)
	    {
	    }

	    function reorderChild($path, $delta, $tree)
	    {
	    	list($var, $path2) = explode(CHILD_DELIMITER, $path, 2);
	    	if(isIndexed($var))
	    	{
				list($v, $index) = explode(INDEX_DELIMITER, $var);
				$arr = &$this->$v;
				if(is_array($arr))
				{
					$obj = $arr[$index];

					if ($path2 == "")
					{
						if(isset($arr[$index]) && is_object($child = $arr[$index]))
							if(isset($arr[$index + $delta]) && is_object($prev = $arr[$index + $delta]))
							{
								$xpath = $prev->fullpath;
								$prev->fullpath = $child->fullpath;
								$child->fullpath = $xpath;

								$arr[$index] = $prev;
								$arr[$index + $delta] = $child;
								$this->switchChildren($child, $prev, $v);
							}
					}
					else
						$obj->reorderChild($path2, $delta, $tree);
				}
	    	}
	    }

	    function removedFromParentDocument()
	    {

	    }

	    /**
	     * Called when child document is removed from parent document
	     */
	    public function beingRemovedFromParentDocument()
	    {
	    }

	    /**
	     *
	     */
	    public function deleteChild($path, $tree)
	    {
	    	list($var, $path2) = app()->explodePath($path);

			if(isIndexed($var))
			{
				list($v, $index) = explode(INDEX_DELIMITER, $var);
				$arr = $this->$v;
				if(is_array($arr))
				{
					$obj = $arr[$index];

					if ($path2 == "")
					{
						$child = $arr[$index];
						$child->beingRemovedFromParentDocument();

						if($obj->__table == "robjfile")
						{
							$obj->delete();
							unset($arr[$index]);
						}
						else
						{
							if($child->isInDatabase())
								$child->todelete = true;
							else
								unset($arr[$index]);
						}
						//TODO why I need to do that? Maybe need to use pointers or something?
						$this->$v = $arr;
						// TODO cascade deletion

						$this->childDeleted($path, $tree);
					}
					else
					{
						$obj->deleteChild($path2, $tree);
					}
				}
			}
	    }

		/**
		 * row number autocalculation for numbered rows of documents, like MaterjaliOstuhinnad
		 * called before insert and update
		 * @param string $nrField
		 * numbered field
		 *
		 * @param string $fkField
		 * foreign key field for linking with document
		 */
	    function calcNr($nrField, $fkField)
	    {
	    	if(!$this->$nrField)
	    	{
	    		$sql = "select max($nrField) from " . $this->__table . " where $fkField = " . $this->$fkField;
	    		$data =& $this->getDatabaseConnection()->getCol($sql);
	    		$this->$nrField = 1 + $data[0];
	    	}
	    }

	    function enumerate()
	    {
	    	if(isset($this->enumerator))
	    		if(is_array($this->enumerator))
	    			$this->calcNr($this->enumerator[ENUMERATOR_NR_FIELD], $this->enumerator[ENUMERATOR_GRP_FIELD]);
	    }

	    function getMetadataUserFor($f)
	    {
	    	$ff = "md" . $f . "Id";
	    	if(isset($this->$ff))
	    		return app()->get("webuser", $this->$ff);
	    	$ff = "md" . $f . "ID";
	    	if(isset($this->$ff))
	    		return app()->get("webuser", $this->$ff);
	    	return null;
	    }

	    function getUpdaterUser()
	    {
	    	return $this->getMetadataUserFor("Updater");
	    }

	    function getCreatorUser()
	    {
	    	return $this->getMetadataUserFor("Creator");
	    }

	    function setUpdateMetadata()
	    {
	    	if(property_exists($this, "mdUpdaterID"))
	    		$this->mdUpdaterID = app()->user()->getIdValue();
	    	if(property_exists($this, "mdUpdaterId"))
	    		$this->mdUpdaterId = app()->user()->getIdValue();
	    	if(property_exists($this, "mdUpdated"))
	    		$this->mdUpdated = date(FORMATSTRING_DATETIME_MACHINE);
	    }

	    function setInsertMetadata()
	    {
	    	if(property_exists($this, "mdCreatorID"))
	    		$this->mdCreatorID = app()->user()->getIdValue();
	    	if(property_exists($this, "mdCreatorId"))
	    		$this->mdCreatorId = app()->user()->getIdValue();
	    	if(property_exists($this, "mdCreated"))
	    		$this->mdCreated = date(FORMATSTRING_DATETIME_MACHINE);
	    }

	    function insert()
	    {
	    	$this->enumerate();
	    	$this->setInsertMetadata();
	    	$this->setUpdateMetadata();
	    	$ret = parent::insert();
	    	$this->log(LOG_ACTION_INS);
	    	$this->changed = array();
	    	return $ret;
	    }

	    function update()
	    {
	    	$this->enumerate();
	    	$this->setUpdateMetadata();
	    	$ret = parent::update();
	    	$this->log(LOG_ACTION_UPD);
	    	$this->changed = array();
	    	return $ret;
	    }

	    function delete()
	    {
	    	$this->log(LOG_ACTION_DEL);
	    	$this->changed = array();
	    	return parent::delete();
	    }

	    function setLogEnabled(/**boolean*/ $b)
	    {
	    	$this->logEnabled = $b;
	    }

	    function canLog()
	    {
	    	if(OBJLOG_ENABLED && isset($this->logEnabled) && $this->logEnabled)
	    	{
	    		$logBlackList = array(
	    			"objlog",
	    			"SqlUpdateLog",
	    			"objectproperty",
	    			"userproperty",
	    			"tipsystem",
	    			"userstats",
	    		);

	    		foreach ($logBlackList as $b)
	    			if($this->__table == $b)
	    				return false;

		    	if(!$this->hasSingleKeyField())
		    		return false;

		    	return true;
		    }
		    return false;
	    }

	    function log($acn, $acntype = LOG_TYPE_NORMAL)
	    {
	    	if($this->canLog())
			    	{
			    		$log = app()->dbo("objlog");
			    		$log->dt = date(FORMATSTRING_DATETIME_MACHINE);
			    		$log->robject = $this->getRObjectID();
			    		$log->val = $this->getSerializedLogData();
			    		$log->acn = $acn;
			    		$log->acntype = $acntype;
			    		$log->userId = app()->user()->getIdValue();
			    		if($this->isDBError($log))
			    			return false;
			    		else
			    			return $log->insert();
			    	}
	    }

	    function getSerializedLogData()
	    {
	    	$r = clone $this;

	    	//unset DB_DataObject stuff and security critical data
	    	$fieldsToUnset = array(
	    		"_DB_DataObject_version",
	    		"_database_dsn",
	    		"_database_dsn_md5",
	    		"_database",
	    		"_DB_resultid",
	    		"_resultFields",
	    		"_link_loaded",
	    		"_join",
	    		"_lastError",

	    		"dynamicPropertiesLoaded",
	    		"closedField",
	    		"closedValue",
	    		"notClosedValue",

	    	);

	    	if($r->__table == "webuser")
	    		$fieldsToUnset[] = "_query";

	    	foreach ($fieldsToUnset as $f)
	    		if(isset($r->$f))
	    			unset($r->$f);

	    	return serialize($r);
	    }

    	function initOrder()
    	{
	    	if(isset($this->enumerator))
	    		if(is_array($this->enumerator))
    				$this->orderBy($this->enumerator[ENUMERATOR_NR_FIELD]);
    	}

		/**
		 * returns primary key value
		 */
		function getIdValue()
		{
			$f = $this->getPrimaryKeyField();
			if(isset($f))
				return $this->$f;
			return null;
		}

		function setIdValue($i)
		{
			$f = $this->getPrimaryKeyField();
			$this->$f = $i;
		}

		/**
		 * closable infrastructure
		 */

		protected $closedField;
		protected $closedValue = 1;
		protected $notClosedValue = 0;

		function getNotClosedSQLClause()
		{
			if($this->isClosable())
				return $this->closedField . " = " . $this->notClosedValue;
			else
				return "";
		}

		function isClosable()
		{
			return isset($this->closedField);
		}

		public function afterClose()
		{
		}

		public function afterReopen()
		{
		}

		private function setClosed($b)
		{
			if($this->isClosable())
			{
				$cf = $this->closedField;
				if($b)
					if($this->canCloseDocument())
						$this->$cf = $this->closedValue;

				if(!$b)
					if($this->canOpenDocument())
						$this->$cf = $this->notClosedValue;

				return true;
			}
			else
				return false;
		}

		function isClosed()
		{
			if(!$this->isClosable())
				return false;
			$cf = $this->closedField;
			return ($this->$cf == $this->closedValue);
		}

		function closeDocument()
		{
			if($ret = $this->setClosed(true))
			{
				$this->setTimestamp("LastClosed");
				$this->afterClose();
			}
			return $ret;
		}

		function reopenDocument()
		{
			if($ret = $this->setClosed(false))
			{
				$this->setTimestamp("LastReopened");
				$this->afterReopen();
			}
			return $ret;
		}

		function canCloseDocument()
		{
			return $this->isClosable() && !$this->isClosed();
		}

		function canOpenDocument()
		{
			return $this->isClosable() && $this->isClosed();
		}

		function isLockable()
		{
			return false;
		}

		function isEditable()
		{
			if(isset($this->logEntry) && $this->logEntry)
				return false;
			return !($this->isLocked() || $this->isClosed());
		}

		function isDeletable()
		{
			return $this->isEditable();
		}

		function isLocked()
		{
			return $this->isLockable() &&
				property_exists($this, "locked") &&
				isset($this->locked) && $this->locked;	//TODO what if field name is not "locked"?
		}

		function lock()
		{
	 	  	return false;
		}

		function unlock()
		{
			return false;
		}

		function canLock()
		{
			return false;
		}

		function canUnlock()
		{
			return false;
		}

		function getFolder()
		{
			return $this->getIdValue() . "/";
		}

		public function getTitleForFile($np = "", $title = "")
		{
			if($rc = app()->request(REQUEST_REGISTRY))
			{
				if($title == "")
				{
					$t = "ru_" . $rc;
					$t1 = t($t);
					if($t1 == $t)
						$t1 = t("ro_" . $rc);
					$title = $t1;
				}
				$fn = $title . ($np ? " " . $np : "") . "-" . app()->user()->uid . "-" . date(FORMATSTRING_DATETIME_MACHINE);
				$fn = strtolower(str_replace(array(" ", ":", ".", "-"), array("_", "_", "_", "_"), $fn));
				return $fn;
			}
			return "";
		}

		function appendObjFile($tempFileName, $fileName)
		{
			if($this->isInDatabase())
			{
				$rf = app()->dbo("robjfile");
				$rf->name = $fileName;
				$rf->robj = $this->__table;
				$rf->rid = $this->getIdValue();
				$rf->insert();
				if($rf->getIdValue())
				{
					if(!file_exists($f = INSTANCE_ROOT . USERFILES . $rf->__table))
						mkdir($f);
					rename($tempFileName, $f . "/" . $rf->getIdValue());
					return $rf->getIdValue();
				}
			}
			return false;
		}

		function getObjFilesCount()
		{
			return 0 + app()->valFromDB("select count(id) from robjfile
				where robj = " . quote($this->__table) . "
				and rid = " . ((int)$this->getIdValue()));
		}

		function getObjFiles($alias = "")
		{
			if($alias == "")
				$alias = isset($this->objFilesVariable) ? $this->objFilesVariable : "files";
			$this->$alias = array();
			if($this->isInDatabase())
			{
				$rf = app()->dbo("robjfile");
				$rf->robj = $this->__table;
				$rf->rid = $this->getIdValue();
				if($rf->find())
					while($rf->fetch())
					{
						$c = clone $rf;
						$c->loadAdditionalData();
						$this->addChild2($alias, $c);
					}
			}
			return $this->$alias;
		}

		function getFilesPath()
		{
			if($this->isInDatabase())
				if(file_exists($p = INSTANCE_ROOT . USERFILES . $this->__table . "/" . $this->getFolder()))
					return $p;
			return "";
		}

		function ensureFolderExists()
		{
			if(!file_exists($p1 = INSTANCE_ROOT . USERFILES . $this->__table))
				mkdir($p1);
			if(!file_exists($p = INSTANCE_ROOT . USERFILES . $this->__table . "/" . $this->getFolder()))
				mkdir($p);
			if(!file_exists($p1))
				app()->panic("cant create userfiles folder");
		}

		function getFiles()
		{
			$fs = array();
			if($dir = $this->getFilesPath())
			{
				$d = dir($dir);
				while (false !== ($file = $d->read()))
					if(substr($file, 0, 1) != ".")
					{
						$fd = new EmptyObject();
						$fd->name = $file;
						$fd->url = INSTANCE_WEB . USERFILES . $this->__table . "/" . $this->getFolder() . $file;
						$fs[] = $fd;
					}

				$d->close();
			}
			sort($fs);
			return $fs;
		}

		function getMessages()
		{
			$ret = array();
			$msg = app()->dbo("message");
			$msg->robject = $this->getRObjectID();
			$msg->recieverId = app()->user()->getIdValue();
			$msg->orderBy("sent desc");
			if($msg->find())
				while($msg->fetch())
				{
					$o = new EmptyObject();
					$o->id = $msg->getIdValue();	//TODO
					$o->caption = htmlspecialchars($msg->caption);
					$o->sent = $msg->getValue("sent");
					$o->sender = $msg->getLink("senderId")->uid;
					$o->body = htmlspecialchars($msg->body);
					$ret[] = $o;
				}

			return $ret;
		}

		function setTimestamp($field)
		{
			$f = PROPERTY_PREFIX . $field;
			$this->$f = date(FORMATSTRING_DATETIME_MACHINE);
			$this->saveDynamicProperties();
		}

		function newVersion()
		{
		}

		/* Address stuff */

		private $_addressFields;

		public function getAddressFields()
		{
			if(!isset($this->_addressFields))
				$this->_addressFields = array(
				"Street1" => " ",
				"Street2" => " ",
				"City" => " ",
				"Index" => " ",
				"Country" => " ",
				);
			return $this->_addressFields;
		}

		public function copyAddress($from, $to, $obj, $bUpd = true, $onlyIfDestinationIsEmpty = false)
		{
			if($onlyIfDestinationIsEmpty)
				foreach($this->getAddressFields() as $fld => $sep)
				{
					$ft = $to . $fld;
					if($obj->$ft)
						return;
				}

			foreach($this->getAddressFields() as $fld => $sep)
			{
				$ff = $from . $fld;
				$ft = $to . $fld;
					$obj->setValue($ft, $this->$ff, $bUpd);
			}
		}

		public function updateAddrRepresentations()
		{
			if(is_array($this->addrRepresentations))
				foreach ( $this->addrRepresentations as $ae )
					$this->updateAddrRepr($ae);
		}

	    protected function copyAddrFieldValueIfNotEmpty($f1, $f2)
	    {
	    	if($this->$f1 != "")
		    	if($this->$f2 == "")
		    		$this->setValue($f2, $this->$f1);
		    $this->updateAddrRepresentations();
		    return true;
	    }

	    public function fillAddrRepr($f)
	    {
	    	$this->updateAddrRepr($f);
	    	return $this->$rf;
	    }

	    public function getAddrRepr($f)
	    {
	    	$ret = "";

	    	foreach($this->getAddressFields() as $fld => $sep)
	    	{
	    		$f2 = $f . $fld;
	   			if($this->$f2 != "")
	   				$ret .= $this->$f2 . $sep;
	   		}
	    	return trim($ret);
	    }

	    function updateAddrRepr($f)
	    {
	    	$rf = "addressBox" . $f . "REPR";
	    	$r = $this->getAddrRepr($f);
	    	if((!isset($this->$rf)) || ($this->$rf != $r))
	    		$this->setValue($rf, $r);
	    }
	}

	class WFWNamed extends WFWObject
	{
	    protected $captionFields = array("name");

	    protected $closedField = "closed";  //closable

	    protected $validators = array(
	        "name" => array(VALIDATION_NOT_EMPTY, VALIDATION_UNIQUE),
	    );

	    protected $formats = array(
	        "mdCreated" =>      FORMAT_DATETIME,
	        "mdUpdated" =>      FORMAT_DATETIME,
	    );

	    public function canCopy(){return true;}
	}

	class WFWCodedAndNamed extends WFWObject
	{
	    protected $captionFields = array("code", "name");

	    protected $closedField = "closed";  //closable

	    protected $validators = array(
        	"code" => array(VALIDATION_NOT_EMPTY, VALIDATION_UNIQUE),
	        "name" => array(VALIDATION_NOT_EMPTY),
	    );

	    protected $formats = array(
	        "mdCreated" =>      FORMAT_DATETIME,
	        "mdUpdated" =>      FORMAT_DATETIME,
	    );

	    public function canCopy(){return true;}

	    public function advancedComboColumns()
	    {
	    	return array(
				array(
					"columnName" => "code",
					"label" => t("code"),
					"width" => "30",
					"align" => "left"
				),
				array(
					"columnName" => "name",
					"label" => t("name"),
					"width" => "70",
					"align" => "left"
				)
			);
	    }

	    public function keySelColumns()
	    {
	    	return array(
				array(
					"columnName" => "code",
					"label" => t("code"),
					"width" => "30",
					"align" => "left"
				),
				array(
					"columnName" => "name",
					"label" => t("name"),
					"width" => "70",
					"align" => "left"
				)
			);
	    }	}
