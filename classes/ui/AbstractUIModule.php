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

		public function includeFile($path)
		{
			include $this->getFilePath($path);
		}

		public function url($path)
		{
			return app()->url(UI_MODULE . $path);
		}


		public function outputFrontpage()
		{
			include $this->getFilePath("html/frontpage.php");
		}

		//misc functions
		/**
		* app top menu class to distinguish test version from production
		* @return String
		*/
		function getTopMenuClass()
		{
			return "topMenu" . (defined("TOPMENU_COLOR") ? " " . TOPMENU_COLOR : "");
		}

		public function getAppMenuHtml()
		{
			return
				$this->getMainMenu()->toHtml() .
				hr() .
				linkItem("document.location = '" . app()->url() . "';", "Frontpage") .
				linkItem("app.mainMenu();", "close");
		}

		public function getFaIcon($icon, $def = "")
		{
			$in = app()->ui()->getFaIconName($icon, $def);
			return $in ? "<i class=\"fa $in\" aria-hidden=\"true\"></i>" : "";
		}

		public function getFaIconName($icon, $def = "fa-question-circle-o")
		{
			if(!isset($this->_faIcons))
				$this->_faIcons = array(
						"pdf" => "fa-file-pdf-o",
						"html" => "fa-file-code-o",
						"xls" => "fa-file-excel-o",
						"xlsx" => "fa-file-excel-o",
						"excel" => "fa-file-excel-o",
						"xml" => "fa-file-code-o",
						"new" => "fa-plus",
						"docs_list" => "fa-folder-open-o",
						"save" => "fa-floppy-o",
						"copy" => "fa-clipboard",
						"delete" => "fa-times",
						"undo" => "fa-undo",
						"log" => "fa-clock-o", //fa-history
					);

			return (isset($this->_faIcons[$icon])) ?
				$this->_faIcons[$icon]:
				$def;

		}


	}