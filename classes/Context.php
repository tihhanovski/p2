<?php
/**
 * Context
 * @author Ilja Tihhanovski <ilja.tihhanovski@gmail.com>
 * @copyright (c) 2011 Intellisoft OÜ
 *
 */


 	require_once 'address_syntax.php';

 	/**
 	 * Context is used to hold data of object being edited on server side.
 	 * Context is serialized and saved between requests.
 	 * Context is able to communicate with database (CRUD) and privileges aware.
 	 */
	class Context
	{
		/** @var String context main object class name, equals to db table name. In version 2.0 it could change */
		public $className;

		/** @var String context name */
		public $namePrefix;

		/** @var array class hierarchy for current context */
		public $treeStructure;

		/** @var DB_DataObject DBO */
		public $obj;

		/** @var int ID of loaded object */
		public $id;

		/** @var String class name for privileges */
		public $privilegesObjectName;

		/**
		 * constructor
		 * @param String @namePrefix
		 * @param array $tree
		 * @param int $id
		 */
		function __construct($namePrefix, $tree, $id)
		{
			$this->initContextProperties($namePrefix, $tree, $id);
			$this->obj = DB_DataObject::factory($this->className);
			$this->obj->fullpath = $this->name();
		}

		/**
		 * Initializes context properties
		 * @param String $namePrefix
		 * @param array $tree
		 * @param int $id
		 */
		private function initContextProperties($namePrefix, $tree, $id)
		{
			$this->namePrefix = $namePrefix;
			$this->id = $id;
			$this->treeStructure = $tree;
			if(isset($this->treeStructure))
				if(is_array($this->treeStructure))
				{
					$keys = array_keys($this->treeStructure);
					if(is_array($keys))
						$this->className = $keys[0];
				}
			if(!isset($this->className))
				$this->className = $namePrefix;
			$this->privilegesObjectName = $this->className;
		}

		/**
		 * Adds and initializes new child according to path
		 * @param String $path
		 * @return array
		 */
		function addChild($path)
		{
			app()->requirePrivilegeJson(PRIVILEGE_UPDATE, $this->getPrivilegesObjectName());
			return $this->obj->addChild($path, $this->treeStructure);
		}

		/**
		 * Moves child up in collection
		 * @param String $path
		 */
		function moveChildUp($path)
		{
			app()->requirePrivilegeJson(PRIVILEGE_UPDATE, $this->getPrivilegesObjectName());
			list($var, $path2) = explode(CHILD_DELIMITER, $path, 2);
			$this->obj->moveChildUp($path2, $this->treeStructure);
		}

		/**
		 * Moves child down in collection
		 * @param String $path
		 */
		function reorderChild($path, $delta)
		{
			app()->requirePrivilegeJson(PRIVILEGE_UPDATE, $this->getPrivilegesObjectName());
			list($var, $path2) = explode(CHILD_DELIMITER, $path, 2);
			$this->obj->reorderChild($path2, $delta, $this->treeStructure);
		}

		/**
		 * Deletes child according to path
		 * @param String $path
		 */
		function deleteChild($path)
		{
			app()->requirePrivilegeJson(PRIVILEGE_UPDATE, $this->getPrivilegesObjectName());
			list($var, $path2) = explode(CHILD_DELIMITER, $path, 2);
			$this->obj->deleteChild($path2, $this->treeStructure);
		}

		/**
		 * Privileges class name
		 * @return String
		 */
		function getPrivilegesObjectName()
		{
			return $this->privilegesObjectName != "" ? $this->privilegesObjectName : $this->name();
		}

		/**
		 * Set objects or its children field value
		 * @param String $path
		 * @param String $value
		 */
		function setValueByPath($path, $value)
		{
			app()->requirePrivilegeJson(PRIVILEGE_UPDATE, $this->getPrivilegesObjectName());
			$this->obj->setValueByPath($path, $value, $this->treeStructure);
		}

		/**
		 * Load context data from database
		 * @return bool
		 */
		function load()
		{
			if($this->id)
			{
				if($this->obj->get($this->id))
					$this->obj->loadChildren($this->treeStructure);
				else
					return false;
			}
			else
			{
				$this->obj->setDefaultValues();
				if(CONTEXT_AUTOSAVE)
				{
					if($this->obj->hasSingleKeyField() && ($pk = $this->obj->getPrimaryKeyField()))
					{
						if($this->obj->insert())
						{
							$id = $this->obj->getIdValue();
							$this->initContextProperties($this->namePrefix . $id, $this->treeStructure, $id);
							$this->obj->fullpath = $this->name();
							$_REQUEST["id"] = $this->id;
						}
					}
				}
			}
			return true;
		}

		/**
		 * Load context data to database
		 * @return bool
		 */
		function save()
		{
			app()->requirePrivilegeJson(PRIVILEGE_UPDATE, $this->getPrivilegesObjectName());

			if($this->obj->validateDocument())
			{
				$this->obj->persist();
				$this->obj->saveChildren($this->treeStructure);
				$this->obj->finalizeSave();
				return true;
			}
			else
				return false;
		}

		/**
		 * Copy context data
		 * @return mixed copied object ID
		 */
		function copyObject()
		{
			dbglog("<h1>copy object</h1>");
			$t = microtime(true);
			app()->requirePrivilegeJson(PRIVILEGE_UPDATE, $this->getPrivilegesObjectName()); 

			$o2 = clone($this->obj);

			$o2->prepareCopy($this->treeStructure);

			$t2 = microtime(true);
			dbglog("<h4>copy prepared in " . mt2ms($t, $t2) . " sec</h4>");

			$o2->insert();
			$t3 = microtime(true);
			dbglog("<h4>inserted in " . mt2ms($t2, $t3) . " sec</h4>");

			$o2->saveChildren($this->treeStructure);
			$t4 = microtime(true);
			dbglog("<h4>children saved in " . mt2ms($t3, $t4) . " sec</h4>");

			$o2->saveDynamicProperties();
			$t5 = microtime(true);
			dbglog("<h4>properties saved in " . mt2ms($t4, $t5) . " sec</h4>");

			dbglog("<h3>copied in " . mt2ms($t, microtime(true)) . " sec</h3>");

			return $o2->getIdValue();
		}

		/**
		 * context name
		 * @return String
		 */
		function name()
		{
			return $this->namePrefix;
		}

		/**
		 * Deletes context
		 * @return mixed
		 */
		function delete()
		{
			app()->requirePrivilegeJson(PRIVILEGE_DELETE, $this->getPrivilegesObjectName());
			return $this->obj->delete();
		}

	}

	/**
	 * Context for object loaded from log entry
	 */
	class LogContext extends Context
	{
		function __construct($log)
		{
			$obj = unserialize($log->val);
			$obj->logEntry = true;
			$this->obj = $obj;
		}
	}