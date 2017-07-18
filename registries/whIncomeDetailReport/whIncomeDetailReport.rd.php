<?php
/**
 * Warehouse income detail report registry descriptor
 * @author Ilja Tihhanovski <ilja.tihhanovski@gmail.com>
 * @copyright (c) 2017 Ilja Tihhanovski
 */

	app()->initReporting();

	/**
	 * Article history report registry descriptor
	 */
	class WhincomedetailreportRegistryDescriptor extends ReportDescriptor
	{
		public $pdfEnabled = true;
		public $htmlEnabled = false;
		public $xlsEnabled = true;

		/**
		 * {@inheritdoc}
		 */
		function initColumns()
		{
			app()->warehouse();
			return array(
				new PdfReportColumn("doc", "Document", 16),
				new PdfReportColumn("dt", "Date", 18, "L", FORMAT_DATE),
				new PdfReportColumn("ep", "Endpoint", 30, "L"),
				new PdfReportColumn("code", "Article", 20, "L"),
				new PdfReportColumn("name", "Name", 40, "L"),
				new PdfReportColumn("qty", "Quantity", 20, "R", FORMAT_QUANTITY_WAREHOUSE, true, AGG_SUM),
				new PdfReportColumn("cost", "Cost", 20, "R", FORMAT_COST_WAREHOUSE),
				new PdfReportColumn("tcost", "Total cost", 20, "R", FORMAT_FLOAT2, true, AGG_SUM),
				);
		}

		/**
		 * {@inheritdoc}
		 */
		public function setDefaults($context)
		{
			$b = $this->setDefaultColumns($context);
			//if($this->setDefaultOrderBy($context))
			//	$b = true;
			return $b;
		}

	}