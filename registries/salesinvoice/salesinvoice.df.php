<?php
/**
 * Article detailform
 * @author Ilja Tihhanovski <ilja.tihhanovski@gmail.com>
 * @copyright (c) Intellisoft OÜ
 *
 */

	echo simpleform(array(
			staticValue($obj, "fullNr", "Number"),
			datepicker($obj, "dt", "Date"),
			keySel($obj, "customerId", "Customer"),
			keySel($obj, "payerId", "Payer"),

			detailGrid("rows", array(
				new DetailGridColumn("articleId", "Article", "select", 12, getSelectOptions(SQL_COMBO_ARTICLE)),
				new DetailGridColumn("priceNoVat", "Price", "double", 4, null, "gridCellRight"),
				new DetailGridColumn("quantity", "Quantity", "double", 4, null, "gridCellRight"),
				new DetailGridColumn("vatId", "VAT tariff", "select", 4, getSelectOptions(SQL_COMBO_VAT)),
				//new DetailGridColumn("vat", "VAT", "static", 4, null, "gridCellRight"),
				new DetailGridColumn("priceWithVat", "Price with VAT", "static", 6, null, "gridCellRight"),
				new DetailGridColumn("totalNoVat", "Total", "static", 6, null, "gridCellRight"),
				new DetailGridColumn("totalVat", "VAT", "static", 6, null, "gridCellRight"),
				new DetailGridColumn("totalWithVat", "Total with VAT", "static", 6, null, "gridCellRight"),
				//new DetailGridMoveDownColumn(),
			), array(
				"caption" => t("Articles"),
				"leftCaption" => true,
				"rowsChangeable" => $obj->isEditable()
			)),
			staticValue($obj, "totalPrice", "Total sum"),
			textarea($obj, "memo", "Memo"),

		));
