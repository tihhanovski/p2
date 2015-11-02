<?php
/**
 * Warehouse inventory print form
 * @author Ilja Tihhanovski <ilja.tihhanovski@gmail.com>
 * @copyright (c) 18.10.2015 Ilja Tihhanovski
 */

	app()->initReporting();

	$obj = $context->obj;

	$model = new ReportModel(t("ru_whinventory") . " " . $obj->fullNr);
	$model->addFilter(t("Warehouse"), $obj->getLink("whId")->getCaption());
	$model->addFilter(t("Date"), $obj->getValue("dt"));
	if($obj->articlegroupId)
	$model->addFilter(t("Article group"), $obj->getLink("articlegroupId")->getCaption());

	$model->columns = array(
		new PdfReportColumn("code", "Code", 10),
		new PdfReportColumn("name", "Article name", 40),
		new PdfReportColumn("unit", "Unit", 8),
		new PdfReportColumn("quantity", "Quantity", 15, "R", FORMAT_FLOAT2),
		new PdfReportColumn("realQuantity", "Real quantity", 20),
	);

	foreach ($obj->rows as $row)
	{
		$a = $row->getLink("articleId");
		$model->addRow(array(
			"code" => $a->code,
			"name" => $a->name,
			"unit" => $a->getLink("unitId")->name,
			"quantity" => $row->quantity,
			"realQuantity" => "",
			));
	}

	$model->output();