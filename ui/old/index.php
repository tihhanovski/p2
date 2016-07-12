<?php

	$uiModuleDir = app()->getAbsoluteFile(UI_MODULE);
	//require_once $uiModuleDir . "OldUIModule.php";

	$uiModuleDir = app()->getAbsoluteFile(UI_MODULE);
	require_once $uiModuleDir . "classes/OldUIModule.php";
	require_once $uiModuleDir . "classes/OldMainMenu.php";
	require_once $uiModuleDir . "classes/OldToolbar.php";
	require_once $uiModuleDir . "classes/OldUserMenu.php";

	app()->_uiModule = new OldUIModule("Old");
