<?php
/**
 * Warehouse module index
 * @author Ilja Tihhanovski <ilja.tihhanovski@gmail.com>
 * @copyright (c) 2015 Intellisoft OÜ
 *
 */

	const WHMVTYPE_INITIAL = 1;
	const WHMVTYPE_INCOME = 2;
	const WHMVTYPE_OUTCOME = 3;
	const WHMVTYPE_INTRA = 4;
	const WHMVTYPE_WRITEOFF = 5;
	const WHMVTYPE_PRODUCTION = 6;
	const WHMVTYPE_INVENTORY = 7;

	const DEFAULT_WHMVTYPE_ID = WHMVTYPE_INITIAL;

	const WHMV_TOTALCOST_ROUNDING = 2;
	const WHMV_TOTALPRICE_ROUNDING = 2;
	const DEFAULT_WHMV_MODIFIER = 1;
	const DEFAULT_WAREHOUSE = 1;
	const DEFAULT_COMPANY = 1;

	require_once WFW_CLASSPATH . "warehouse/WarehouseModule.php";
	require_once WFW_CLASSPATH . "warehouse/WHMVRegistryDescriptor.php";
	require_once WFW_CLASSPATH . "warehouse/queries-wh.php";
	require_once WFW_CLASSPATH . "warehouse/WhmvbatchPrintout.php";

	require_once WFW_CLASSPATH . "warehouse/WarehouseState.php";
	require_once WFW_CLASSPATH . "warehouse/DeferredBatchTotalCostCalculator.php";
	require_once WFW_CLASSPATH . "warehouse/DeferredWhstateRecalculator.php";

	require_once WFW_CLASSPATH . "warehouse/dbo/WhmvParent.php";
	require_once WFW_CLASSPATH . "warehouse/dbo/WhmvbatchParent.php";
	require_once WFW_CLASSPATH . "warehouse/dbo/WhinventoryParent.php";
	require_once WFW_CLASSPATH . "warehouse/dbo/WhinventoryrowParent.php";
