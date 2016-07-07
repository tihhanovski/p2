<?php

	class AbstractUIModule
	{
		private $componentPrefix;

		public function __construct($s)
		{
			$this->componentPrefix = $s;
			$this->initComponents($s);
		}

		public function initComponents($s)
		{
			$uiModuleDir = app()->getAbsoluteFile(UI_MODULE);
			$compClasses = array(
					"MainMenu",
					"Toolbar",
					"UserMenu",
				);
			foreach ($compClasses as $cls)
				require_once $uiModuleDir . "classes/" . $s . "UIModule.php";
		}

		public function getMainMenu()
		{
			return $this->getUIComponentObject("MainMenu");
		}

		public function getToolbar()
		{
			return $this->getUIComponentObject("Toolbar");
		}

		public function getUserMenu()
		{
			return $this->getUIComponentObject("UserMenu");
		}

		public function getUIComponentObject($component)
		{
			if(!isset($this->$component))
			{
				$className = $this->componentPrefix . $component;
				if(!class_exists($className))
					app()->panic("Component class does not exist: $component");
				$this->component = new $className();
			}
			return $this->component;
		}

		private $modulePath;
		public function getFilePath($path)
		{
			if(!isset($this->modulePath))
				$modulePath = app()->getAbsoluteFile(UI_MODULE);
			return $modulePath . $path;
		}

		public function url($path)
		{
			return app()->url(UI_MODULE . $path);
		}


		public function outputFrontpage()
		{
			include $this->getFilePath("html/frontpage.php");
		}
	}