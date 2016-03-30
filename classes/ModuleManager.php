<?php
/**
 * Module Manager
 * @author Ilja Tihhanovski <ilja.tihhanovski@gmail.com>
 * @copyright (c) 2016 Ilja Tihhanovski
 *
 */

	class ModuleManager
	{
		private $modules = array();
		private $instances = array();

		private $currentModuleRoot;
		public function getCurrentModuleRoot()
		{
			return $this->currentModuleRoot;
		}

		public function add($web, $root = "")
		{
			if($root === "")
				$root = DOC_ROOT . $web;
			$this->modules[$web] = $root;

			$this->currentModuleRoot = $root;
			require_once($root . "index.php");
		}

		public function getList()
		{
			return $this->modules;
		}

		public function includeAll($s)
		{
			foreach ($this->getList() as $root)
			{
				$fn = $root . $s;
				if(file_exists($fn))
					include_once $fn;
			}
		}

		public function getLastUrl($s)
		{
			$ret = "";
			foreach ($this->getList() as $web => $root)
				if(file_exists($root . $s))
				$ret = $web . $s;
			return $ret;
		}

		public function getLastAbs($s)
		{
			$ret = "";
			foreach ($this->getList() as $root)
				if(file_exists($root . $s))
				$ret = $root . $s;
			return $ret;
		}

		public function getModule($s)
		{
			if(isset($this->modules[$s]))
			{
				if(!isset($this->instances[$s]))
				{
					$a = explode("/", $s);
					$clsName = ucfirst($a[count($a) - 1]) . "Module";
					if(class_exists($clsName))
						$this->instances[$s] = new $clsName();
				}
				if(isset($this->instances[$s]))
					return $this->instances[$s];
			}
			return null;
		}
	}

	$_moduleManager = new ModuleManager();

	function moduleManager()
	{
		global $_moduleManager;
		return $_moduleManager;
	}