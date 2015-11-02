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

	require_once "WarehouseModule.php";
	require_once "WHMVRegistryDescriptor.php";
	require_once "queries-wh.php";

	require_once "dbo/WhmvParent.php";
	require_once "dbo/WhmvbatchParent.php";
	require_once "dbo/WhinventoryParent.php";
	require_once "dbo/WhinventoryrowParent.php";
