<?php

/**
 * System profile registry descriptor
 * @author Ilja Tihhanovski <ilja.tihhanovski@gmail.com>
 * @copyright (c) 08.06.2015 Ilja Tihhanovski
 */

echo simpleform(array(
	new Tabulator("t2",
		array(
			new TabulatorItem(
				"tabGeneral",
				"General",
				array(
					textbox($obj, PROPERTY_PREFIX . "CompanyName", "Company name"),
					textbox($obj, PROPERTY_PREFIX . "CompanyAddress", "Address"),
					//selectSql($obj, PROPERTY_PREFIX . "Country", "Country", SQL_COMBO_COUNTRY),
					textbox($obj, PROPERTY_PREFIX . "CompanyPhone", "Phone"),
					textbox($obj, PROPERTY_PREFIX . "CompanyFax", "Fax"),
					textbox($obj, PROPERTY_PREFIX . "CompanyEmail", "E-mail"),
					textbox($obj, PROPERTY_PREFIX . "CompanyWeb", "Web address"),
					textbox($obj, PROPERTY_PREFIX . "CompanyRegCode", "Reg. code"),
					textbox($obj, PROPERTY_PREFIX . "CompanyVatNo", "VAT no"),
					textbox($obj, PROPERTY_PREFIX . "CompanyIBAN", "IBAN"),
					textbox($obj, PROPERTY_PREFIX . "CompanySWIFT", "SWIFT"),
					textbox($obj, PROPERTY_PREFIX . "CompanyBank", "Bank"),
					//selectSql($obj, PROPERTY_PREFIX . "Currency", "Currency", SQL_COMBO_CURRENCY),
					//selectSql($obj, PROPERTY_PREFIX . "Currency2", "Second currency", SQL_COMBO_CURRENCY),
					//checkbox($obj, PROPERTY_PREFIX . "PrintCurrency2Sum", "Print totals in second currency"),
					//textbox($obj, PROPERTY_PREFIX . "CompanyLogo", "Logo url"),
					//dynMultiTextArea($obj, "CompanyAddress", "Address for documents"),
					new DetailGrid(
						"banks",
						array(
								new DetailGridColumn("name", "Name", 'textbox', 12),
								new DetailGridColumn("iban", "IBAN", 'textbox', 12),
								new DetailGridColumn("swift", "SWIFT", 'textbox', 6),
							),
						array(
							"caption" => t("Banks"),
							"leftCaption" => true,
							"rowsChangeable" => true,
							"rowsAppendable" => true,
						)
					)
				)
			),
			new TabulatorItem(
				"tabEmail",
				"E-mailing",
				array(
					textbox($obj, PROPERTY_PREFIX . "SmtpServer", "SMTP Server"),
					textbox($obj, PROPERTY_PREFIX . "SmtpLogin", "Login"),
					textbox($obj, PROPERTY_PREFIX . "SmtpPassword", "Password"),
					textboxdouble($obj, PROPERTY_PREFIX . "SmtpPort", "Port"),
					textbox($obj, "email", "System e-mail"),
					dynMultiTextArea($obj, "EmailHeader", "E-mail header"),
					dynMultiTextArea($obj, "EmailFooter", "E-mail footer"),
				)
			),
			new TabulatorItem(
				"tabSoftwareIssue",
				"ro_softwareissue",
				array(
					selectSql($obj, "dyndefsoftwareissueownerId", "Default owner", "select ID, uid from webuser order by uid"),
				)
			),
		)
	)));

