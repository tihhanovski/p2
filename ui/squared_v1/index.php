<?php

	const MAIN_MENU_CLASS = "SquaredMainMenu";

	$uiModuleDir = app()->getAbsoluteFile(UI_MODULE);
	require_once $uiModuleDir . "SquaredUIModule.php";
	require_once $uiModuleDir . "SquaredMainMenu.php";

	app()->_uiModule = new SquaredUIModule();