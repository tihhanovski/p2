<?php
/*
 * Created on 15.03.2015
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
			app()->warehouse();
			return array(
				new PdfReportColumn("d", "D", 5),
				new PdfReportColumn("doc", "Document", 16),
				new PdfReportColumn("dt", "Date", 18, "L", FORMAT_DATE),
				new PdfReportColumn("ep", "Endpoint", 30, "L"),
				new PdfReportColumn("qty", "Quantity", 20, "R", FORMAT_QUANTITY_WAREHOUSE, true, AGG_SUM),
				new PdfReportColumn("cost", "Cost", 20, "R", FORMAT_COST_WAREHOUSE),
				new PdfReportColumn("tcost", "Total cost", 20, "R", FORMAT_FLOAT2, true, AGG_SUM),
				new PdfReportColumn("price", "Price", 20, "R", FORMAT_PRICE_WAREHOUSE),
				new PdfReportColumn("tprice", "Total price", 20, "R", FORMAT_FLOAT2, true, AGG_SUM),
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

		protected function getArtxId($s)
		{
			$a = explode(":", $s);
			$c = trim($a[0]);

			$o = app()->dbo("article");
			$o->code = $c;
			$o->find(true);
			return (int)$o->id;
		}
	}