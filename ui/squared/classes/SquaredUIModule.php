<?php

	class SquaredUIModule extends AbstractUIModule
	{
		public function getAppMenuHtml()
		{
			$menu = $this->getMainMenu();
			$menu->addItem(new SquaredMainMenuItem("fa-level-up", app()->url(), t("Frontpage")));
			$menu->addItem(new SquaredMainMenuItem("fa-arrow-left", "JavaScript:app.mainMenu();", t("close")));
			return $menu->toHtml();
		}
	}
