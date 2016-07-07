<?php

	const MAIN_MENU_CLASS = "SquaredMainMenu";
	const TOOLBAR_CLASS = "SquaredToolbar";
	const USERMENU_CLASS = "SquaredUserMenu";

	$uiModuleDir = app()->getAbsoluteFile(UI_MODULE);
	require_once $uiModuleDir . "classes/SquaredUIModule.php";
	require_once $uiModuleDir . "classes/SquaredMainMenu.php";
	require_once $uiModuleDir . "classes/SquaredToolbar.php";
	require_once $uiModuleDir . "classes/SquaredUserMenu.php";

	app()->_uiModule = new SquaredUIModule("Squared");