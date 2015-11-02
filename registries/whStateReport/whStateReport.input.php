<?php
/*
 * Created on 17.03.2015
 *
 * (c) Ilja Tihhanovski, Intellisoft OÜ
 *
 */

	echo simpleform(array(
			datepicker($obj, "dt", "Date"),
			selectSqlNotNullable($obj, "whId", "Warehouse", SQL_COMBO_WAREHOUSE),
			checkbox($obj, "showNulls", "Show null quantities"),
			app()->warehouse()->isArticleModifiersEnabled() ?
				checkbox($obj, "showMods", "Show modifiers") :
				"",
			checkbox($obj, "showClosed", "Show closed articles"),
			getAvailableColumnsSelector($this),
			startReportButton($this),
		));
