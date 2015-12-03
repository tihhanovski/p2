<?php
/**
 * Article history report input form
 * @author Ilja Tihhanovski <ilja.tihhanovski@gmail.com>
 * @copyright (c) 2015 Intellisoft OÜ
 */

	$a = app()->dbo("article");
	$ts = new KeySelSetup($obj, "articleId", "article", $a->keySelColumns());

	echo simpleform(array(
			keySel($obj, "articleId", "Article", $ts),
			//textboxAutocompleteSql($obj, "artx", "Article", SQL_AUTOCOMPLETE_ARTICLE_ALL),
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
