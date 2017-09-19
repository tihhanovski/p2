<?php
/**
 * Warehouse income detail report input form
 * @author Ilja Tihhanovski <ilja.tihhanovski@gmail.com>
 * @copyright (c) 2017 Ilja Tihhanovski
 */

	echo simpleform(array(
			datepicker($obj, "dt1", "Period start"),
			datepicker($obj, "dt2", "Period finish"),
			prevNextMonthYear("dt1", "dt2"),

			selectSqlNotNullable($obj, "whId", "Warehouse", SQL_COMBO_WAREHOUSE),
			selectSql($obj, "articleId", "Article", ARTICLECODE_ENABLED ? SQL_COMBO_ARTICLE : SQL_COMBO_ARTICLE_NAMEONLY),
			app()->warehouse()->isArticleModifiersEnabled() ?
				selectSqlNotNullable($obj, "modId", "Modifier", SQL_COMBO_WHMV_MODIFIER) : 
				"",

			selectSql($obj, "companySrcId", "Supplier", "select id, name from company where closed = 0 and supplier = 1 order by name"),

			checkbox($obj, "groupByGroup", "Group by article groups"),

			getAvailableColumnsSelector($this),
			startReportButton($this),
			clearReportFieldsButton(),
		));
