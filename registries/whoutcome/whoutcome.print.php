<?php
/**
 * Warehouse outcome printform
 * @author Ilja Tihhanovski <ilja.tihhanovski@gmail.com>
 * @copyright (c) 2015 Intellisoft OÜ
 *
 */

	app()->warehouse();

	class WhoutcomePrintout extends WhmvbatchPrintout
	{
		public function getTopFilters($obj)
		{
			return array(
					"dt" => $obj->getValue("dt"),
					"Source warehouse" => $obj->getLink("whSrcId")->name,
					"Destination company" => $obj->getLink("companyDstId")->name,
				);
		}

		public function getBottomFilters($obj)
		{
			return array(
					"Total cost" => $obj->getValue("totalCost"),
					"Total price" => $obj->getValue("totalPrice"),
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

			if($obj->whSrcId === DEFAULT_WAREHOUSE)
				new PdfReportColumn("whSrcName", "Source warehouse", 4);

			$ret[] = new PdfReportColumn("qty", "Quantity", 6, "R", FORMAT_QUANTITY_WAREHOUSE);
			$ret[] = new PdfReportColumn("cost", "Cost", 6, "R", FORMAT_COST_WAREHOUSE);
			$ret[] = new PdfReportColumn("price", "Price", 6, "R", FORMAT_PRICE_WAREHOUSE);
			$ret[] = new PdfReportColumn("memo", "Memo", 6);

			return $ret;
		}

		public function getCaption($obj)
		{
			return t("ru_whoutcome") . " " . $obj->fullNr;
		}
	}

	$c = new WhoutcomePrintout();
	$c->run($context->obj);
