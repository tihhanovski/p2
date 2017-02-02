<?php
/*
 * Created on Sep 8, 2011
 *
 * (c) Ilja Tihhanovski, Intellisoft
 *
 */

 	const JS_VERSION = 34;

 	define("WFW_CLASSPATH", WFW_ROOT . "classes/");

 	require_once WFW_CLASSPATH . "ModuleManager.php";
 	require_once WFW_CLASSPATH . "WFWException.php";
 	require_once WFW_CLASSPATH . "WFWObject.php";
	require_once WFW_CLASSPATH . "SetupObject.php";
	require_once WFW_CLASSPATH . "ReportObject.php";
	require_once WFW_CLASSPATH . "FilterObject.php";

	require_once WFW_CLASSPATH . "RequestHandler.php";
	require_once WFW_CLASSPATH . "Warning.php";
	require_once WFW_CLASSPATH . "Context.php";
	require_once WFW_CLASSPATH . "ReportContext.php";
	require_once WFW_CLASSPATH . "RegistryDescriptor.php";
	require_once WFW_CLASSPATH . "SubdocumentRegistryDescriptor.php";	//TODO remove it!
	require_once WFW_CLASSPATH . "ReportDescriptor.php";
	require_once WFW_CLASSPATH . "SetupFormDescriptor.php";
 	require_once WFW_CLASSPATH . "address_syntax.php";
 	require_once WFW_CLASSPATH . "Formatter.php";
 	require_once WFW_CLASSPATH . "Validation.php";
 	require_once WFW_CLASSPATH . "CronTask.php";
 	require_once WFW_CLASSPATH . "CLITask.php";

 	require_once WFW_CLASSPATH . "ddl/index.php";

 	require_once WFW_CLASSPATH . "ContextProvider.php";
 	require_once WFW_CLASSPATH . "UIHelper.php";
 	require_once WFW_CLASSPATH . "PasswordValidator.php";
	require_once WFW_CLASSPATH . "Application.php";

	require_once WFW_CLASSPATH . "XLSExporter.php";

	require_once WFW_CLASSPATH . "ui/index.php";

	require_once WFW_CLASSPATH . "dbo/index.php";

	require_once WFW_CLASSPATH . "queries.php";

	//reporting
	require_once WFW_CLASSPATH . "reporting/index.php";

	//modules
 	require_once WFW_CLASSPATH . "warehouse/index.php";
