<?php
/*
 * Created on Sep 8, 2011
 *
 * (c) Ilja Tihhanovski, Intellisoft
 *
 */

 	const JS_VERSION = 25;

 	require_once "WFWException.php";
 	require_once "WFWObject.php";
	require_once "SetupObject.php";
	require_once "ReportObject.php";
	require_once "FilterObject.php";

	require_once "RequestHandler.php";
	require_once "Warning.php";
	require_once "Context.php";
	require_once "ReportContext.php";
	require_once "RegistryDescriptor.php";
	require_once "SubdocumentRegistryDescriptor.php";	//TODO remove it!
	require_once "ReportDescriptor.php";
	require_once "SetupFormDescriptor.php";
 	require_once "address_syntax.php";
 	require_once "Formatter.php";
 	require_once "Validation.php";
 	require_once "CronTask.php";
 	require_once "CLITask.php";

 	require_once "ddl/index.php";

 	require_once "ContextProvider.php";
 	require_once "UIHelper.php";
 	require_once "PasswordValidator.php";
	require_once "Application.php";

	require_once "XLSExporter.php";

	require_once "ui/index.php";

	require_once "dbo/index.php";

	require_once "queries.php";

	//reporting
	require_once "reporting/index.php";

	//modules
 	require_once "warehouse/index.php";
