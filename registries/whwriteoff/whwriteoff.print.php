<?php
/**
 * Warehouse writeoff printform
 * @author Ilja Tihhanovski <ilja.tihhanovski@gmail.com>
 * @copyright (c) 2015 Intellisoft OÜ
 *
 */

	app()->warehouse();

	class WhwriteoffPrintout extends WhmvbatchPrintout
	{
		public function getTopFilters($obj)
		{
			return array(
					"dt" => $obj->getValue("dt"),
					"Source warehouse" => $obj->getLink("whSrcId")->name,
				);
		}

		public function getBottomFilters($obj)
		{
			return array(
					"Total cost" => $obj->getValue("totalCost"),
					"memo" => $obj->memo,
				);
		}

		public function getColumns($obj)
		{
			return array(
					new PdfReportColumn("artCode", "Code", 8),
					new PdfReportColumn("artName", "Name", 12),
					new PdfReportColumn("unitName", "Unit", 4),
					new PdfReportColumn("qty", "Quantity", 6, "R", FORMAT_QUANTITY_WAREHOUSE),
					new PdfReportColumn("cost", "Cost", 6, "R", FORMAT_COST_WAREHOUSE),
					new PdfReportColumn("memo", "Memo", 6)
					);
		}

		public function getCaption($obj)
		{
			return t("ru_whwriteoff") . " " . $obj->fullNr;
		}
	}

	$c = new WhwriteoffPrintout();
	$c->run($context->obj);
