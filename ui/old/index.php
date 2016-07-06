<?php

	$uiModuleDir = app()->getAbsoluteFile(UI_MODULE);
	require_once $uiModuleDir . "OldUIModule.php";

	app()->_uiModule = new OldUIModule();