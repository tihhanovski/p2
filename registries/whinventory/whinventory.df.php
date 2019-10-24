<?php
/**
 * Warehouse inventory detail form
 * @author Ilja Tihhanovski <ilja.tihhanovski@gmail.com>
 * @copyright (c) 16.10.2015 Intellisoft OÜ
 */

	$inDB = $obj->isInDatabase();
	$gdbl = $obj->isEditable() ? "double" : "static";
	$gtxt = $obj->isEditable() ? "textbox" : "static";

	echo simpleform(array(
			rightPanel($context,
				array(
					new PrintButton($context, "", "Print"),
					$obj->isLocked() ? new PrintButton($context, "rpt", "Inventory report") : "",
					new LockButton($context),
					!$obj->isLocked() ? "<hr/>" : "",
					!$obj->isLocked() ? new AppFuncButton($context, "fillQuantitiesNotFilledYet", "Fill quantities not filled yet") : "",
					!$obj->isLocked() ? new AppFuncButton($context, "updateWhStates", "Update warehouse states with current values") : "",
				),
				false, false
			),
			staticValue($obj, "fullNr", "Number"),
			$inDB ? staticValue($obj, "dt", "Date") : datepicker($obj, "dt", "Date"),
			$inDB ?
				lockedMemo(app()->getLinkedCaption($obj->getLink("whId")), "Warehouse") :
				selectSql($obj, "whId", "Warehouse", SQL_COMBO_WAREHOUSE_NOVIRTUAL),
			$inDB ?
				lockedMemo(app()->getLinkedCaption($obj->getLink("articlegroupId")), "Article group") :
				selectSql($obj, "articlegroupId", "Article group", SQL_COMBO_ARTICLEGROUP),
			$inDB ?
				detailGrid("rows", array(
					new DetailGridColumn("articleCaption", "Article", "static", 16),
					new DetailGridColumn("quantity", "Quantity", "static", 6, null, "gridCellRight"),
					new DetailGridColumn("realQuantity", "Real quantity", $gdbl, 6, null, "gridCellRight"),
					new DetailGridColumn("delta", "Delta", "static", 6, null, "gridCellRight"),
					new DetailGridColumn("cost", "Cost", $gdbl, 4, null, "gridCellRight"),
					new DetailGridColumn("memo", "Memo", $gtxt, 8),
				), array(
					"caption" => t("Articles"),
					"leftCaption" => true,
					"rowsChangeable" => false,
					"rowsAppendable" => false,
				)) :
				lockedMemo("<a href=\"JavaScript:app.saveDocument();\">" . t("Save to fill articles grid") . "</a>", "&nbsp;"),

			textarea($obj, "memo"),
		));
