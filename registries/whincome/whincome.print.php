<?php
/**
 * Warehouse income printform
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
					"Source company" => $obj->getLink("companySrcId")->name,
					"Destination warehouse" => $obj->getLink("whDstId")->name,
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
			$ret = array(
					new PdfReportColumn("artCode", "Code", 8),
					new PdfReportColumn("artName", "Name", 12),
					new PdfReportColumn("unitName", "Unit", 4),
					);

			if($obj->whDstId === DEFAULT_WAREHOUSE)
				new PdfReportColumn("whDstName", "Destination warehouse", 4);

			$ret[] = new PdfReportColumn("qty", "Quantity", 6, "R", FORMAT_QUANTITY_WAREHOUSE);
			$ret[] = new PdfReportColumn("cost", "Cost", 6, "R", FORMAT_COST_WAREHOUSE);
			$ret[] = new PdfReportColumn("memo", "Memo", 6);

			return $ret;
		}

		public function getCaption($obj)
		{
			return t("ru_whincome") . " " . $obj->fullNr;
		}
	}

	$c = new WhwriteoffPrintout();
	$c->run($context->obj);
