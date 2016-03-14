<?php
/*
 * Created on Aug 14, 2012
 *
 * (c) Ilja Tihhanovski, Intellisoft
 *
 */


	class XLSExporter
	{
		public $fileName = "export.xlsx";
		public $sheetName = "Sheet1";

		public $xls;
		public $workbook;
		public $worksheet;
		public $fname;

		private $bClosed = false;

		//public $formats;

		function __construct()
		{
			$this->startXLSExporter();
			/*$this->formats = array(
				FORMAT_FLOAT1 => "#,##0.00",
				FORMAT_FLOAT2 => "#,##0.00",
				FORMAT_FLOAT2_NULLABLE => "#,##0.00",
				FORMAT_FLOAT3 => "#,##0.000",
				FORMAT_FLOAT4 => "#,##0.0000",
				FORMAT_FLOAT6 => "#,##0.000000",
			);*/
		}

		function getWorkbook()
		{
			if(!isset($this->fname))
				$this->fname = tempnam("/tmp", $this->fileName);
 
			$u = app()->user();
			$this->xls = new PHPExcel();
			$this->xls->getProperties()->setCreator($u->name)
							 ->setLastModifiedBy($u->name)
							 ->setTitle($this->sheetName)
							 ->setSubject($this->sheetName)
							 ->setDescription($this->sheetName);
							 //->setKeywords("office PHPExcel php")
							 //->setCategory("Test result file");

			$this->xls->setActiveSheetIndex(0);
			//echo $this->sheetName . "<br/>";
			if(!$this->sheetName)
				$this->sheetName = "output";
			$this->xls->getActiveSheet()->setTitle($this->sheetName);
			return $this->xls;
		}

		function getWorksheet()
		{
			return $this->xls;
		}

		function startXLSExporter()
		{
			require_once SETUP_3RD_XLS;
		}

		function write($addr, $content, $format = null)
		{
			if(!is_object($this->xls))
				$this->getWorkbook();

			return $this->xls->getActiveSheet()->setCellValue($addr, $content);
		}

		function close()
		{
			if(!isset($this->fname))
				$this->fname = tempnam("/tmp", $this->fileName);
			if(!$this->fname)
				$fname = tempnam("/tmp", "output.xlsx");

			$objWriter = PHPExcel_IOFactory::createWriter($this->xls, 'Excel2007');
			$objWriter->save($this->fname);
		}

		function getCoord($x, $y)
		{
			$nr = $x - 1;
			$i1 = (int)($nr / 26);
			$i2 = $nr - $i1 * 26;
			$p1 = ($i1 ? chr(64 + $i1) : "") . chr(65 + $i2);
			$this->addRange($p1);
			return $p1 . $y;
		}

		function addRange($r)
		{
			if(!isset($this->xlsRange))
				$this->xlsRange = array();
			if(!isset($this->xlsRange[$r]))
				$this->xlsRange[$r] = $r;
		}

		function autosize()
		{
		 	if(isset($this->xlsRange) && is_array($this->xlsRange))
		 		//die(print_r($this->xlsRange, 1));
		 		foreach ($this->xlsRange as $r)
		 			$this->xls->getActiveSheet()->getColumnDimension($r)->setAutoSize(true);
		}

		function mergeCells($range)
		{
			$this->xls->getActiveSheet()->mergeCells($range);
		}

		function download()
		{
			if(!$this->fileName || $this->fileName == ".xlsx")
				$this->fileName = "output.xlsx";
			$this->close();
			header("Content-Type: application/x-msexcel; name=\"{$this->fileName}\"");
			header("Content-Disposition: inline; filename=\"{$this->fileName}\"");
			$fh=fopen($this->fname, "rb");
			fpassthru($fh);
			unlink($this->fname);
		}
	}