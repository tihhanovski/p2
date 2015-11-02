<?php
/*
 * Created on 15.03.2015
 *
 * (c) Ilja Tihhanovski, Intellisoft OÜ
 *
 */

	echo simpleform(array(
			textboxAutocompleteSql($obj, "artx", "Article", SQL_AUTOCOMPLETE_ARTICLE_ALL),
			app()->warehouse()->isArticleModifiersEnabled() ?
				selectSqlNotNullable($obj, "modId", "Modifier", SQL_COMBO_WHMV_MODIFIER) : 
				"",
			selectSqlNotNullable($obj, "whId", "Warehouse", SQL_COMBO_WAREHOUSE),
			datepicker($obj, "dt1", "Period start"),
			datepicker($obj, "dt2", "Period finish"),
			prevNextMonthYear("dt1", "dt2"),
			getAvailableColumnsSelector($this),
			startReportButton($this),
		));
