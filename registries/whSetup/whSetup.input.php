<?php

/**
 * Warehouse setup form
 * @author Ilja Tihhanovski <ilja.tihhanovski@gmail.com>
 * @copyright (c) 2015 Intellisoft OÜ
 *
 */

	echo simpleform(array(
		selectSqlNotNullable($obj, "dynDefaultWarehouseId", "Default warehouse", SQL_COMBO_WAREHOUSE),
		));