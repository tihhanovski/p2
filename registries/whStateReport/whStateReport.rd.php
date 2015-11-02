<?php
/*
 * Created on 17.03.2015
 *
 * (c) Ilja Tihhanovski, Intellisoft OÜ
 *
 */

	app()->initReporting();

	class _RegistryDescriptor extends ReportDescriptor
	{
		public $pdfEnabled = true;
		public $htmlEnabled = false;
		public $xlsEnabled = true;

		/**
		 * {@inheritdoc}
		 */
		function initColumns()
		{
			$ret = array();
			$ret[] = new PdfReportColumn("code", "Code", 20);
			$ret[] = new PdfReportColumn("name", "Name", 40);
			if(app()->warehouse()->isArticleModifiersEnabled())
				$ret[] = new PdfReportColumn("md", "Modifier", 20);
			$ret[] = new PdfReportColumn("unit", "Unit", 7);
			$ret[] = new PdfReportColumn("qty", "Quantity", 20, "R", FORMAT_QUANTITY_WAREHOUSE, true, AGG_SUM);
			$ret[] = new PdfReportColumn("cost", "Cost", 20, "R", FORMAT_COST_WAREHOUSE);
			$ret[] = new PdfReportColumn("tcost", "Total", 20, "R", FORMAT_FLOAT2, true, AGG_SUM);

			return $ret;
		}

		/**
		 * {@inheritdoc}
		 */
		public function setDefaults($context)
		{
			$b = $this->setDefaultColumns($context);
			return $b;
		}
	}