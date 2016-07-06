<?php

	class AbstractUIModule
	{
		//TODO
		public function getMainMenu()
		{
			if(!isset($this->mainMenu))
			{
				if(!defined(MAIN_MENU_CLASS))
					define("MAIN_MENU_CLASS", "MainMenu");
				if(class_exists($cls = MAIN_MENU_CLASS))
					$this->mainMenu = new $cls;
			}
			return $this->mainMenu;
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