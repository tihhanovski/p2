<?php
/*
 * Created on Oct 28, 2011
 *
 * (c) Intellisoft
 */



	echo simpleform(array(
			textbox($obj, "name"),
			textbox($obj, "code"),
			addressBoxes($obj, "addr", "Address"),
			textarea($obj, "memo"),
			checkbox($obj, "customer"),
			checkbox($obj, "supplier"),

			textbox($obj, "regCode", "Reg no"),
			textbox($obj, "vatCode", "VAT no"),
			//textbox($obj, "accCode", "Accounting code"),

			textbox($obj, "deliveryTerms", "Shipping terms"),
			textbox($obj, "paymentTerms", "Payment terms"),

			//selectSql($obj, "managerId", "Manager", SQL_COMBO_WEBUSER),
			//selectSql($obj, "languageId", "Language", SQL_COMBO_LANGUAGE),
			//selectSql($obj, "currencyId", "Currency", SQL_COMBO_CURRENCY),

			/*detailGrid("contacts", array(
				new DetailGridColumn("contact", "Contact", "textbox", 8),
				new DetailGridColumn("email", "Email", "textbox", 8),
				new DetailGridColumn("phone", "Phone", "textbox", 8),
				new DetailGridColumn("fax", "Fax", "textbox", 8),
			), array("caption" => t("Contacts"), "leftCaption" => true)),*/
		));

