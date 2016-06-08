<?php
/**
 * Warehouse inventory print form
 * @author Ilja Tihhanovski <ilja.tihhanovski@gmail.com>
 * @copyright (c) 18.10.2015 Ilja Tihhanovski
 */

	app()->initReporting();

	$obj = $context->obj;

	$model = new ReportModel(t("Inventory report") . " " . $obj->fullNr);
	$model->addFilter(t("Warehouse"), $obj->getLink("whId")->getCaption());
	$model->addFilter(t("Date"), $obj->getValue("dt"));
	if($obj->articlegroupId)
	$model->addFilter(t("Article group"), $obj->getLink("articlegroupId")->getCaption());

	$model->columns = array();

	if(ARTICLECODE_ENABLED)
		$model->columns[] = new PdfReportColumn("code", "Code", 10);
	$model->columns[] = new PdfReportColumn("name", "Article name", 40);
	$model->columns[] = new PdfReportColumn("unit", "Unit", 8);
	$model->columns[] = new PdfReportColumn("quantity", "Quantity", 15, "R", FORMAT_FLOAT2);
	$model->columns[] = new PdfReportColumn("realQuantity", "Real quantity", 15, "R", FORMAT_FLOAT2);
	$model->columns[] = new PdfReportColumn("delta", "Delta", 15, "R", FORMAT_FLOAT2);

	foreach ($obj->rows as $row)
	{
		$a = $row->getLink("articleId");
		$model->addRow(array(
			"code" => $a->code,
			"name" => $a->name,
			"unit" => $a->getLink("unitId")->name,
			"quantity" => $row->quantity,
			"realQuantity" => $row->realQuantity,
			"delta" => $row->delta,
			));
	}

	$model->output();