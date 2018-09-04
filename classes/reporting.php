<?php
/*
 * Created on Nov 15, 2011
 *
 * (c) Ilja Tihhanovski, Intellisoft
 *
 */

	const DEFAULT_ARRAYREPORTCLASS = "WFWArrayReport";

 	define("AGG_SUM", "sum");
 	define("AGG_TEXT", "text");
 	define("AGG_COUNT", "count");

 	define("RPT_GRIDSETUP_LEFTMARGIN", "margin-left");
 	define("RPT_GRIDSETUP_RIGHTMARGIN", "margin-right");
 	define("RPT_GRIDSETUP_TOPMARGIN", "margin-top");
 	define("RPT_GRIDSETUP_BOTTOMMARGIN", "margin-bottom");

 	define("RPT_GRIDSETUP_LEFTPADDING", "padding-left");
 	define("RPT_GRIDSETUP_RIGHTPADDING", "padding-right");
 	define("RPT_GRIDSETUP_TOPPADDING", "padding-top");
 	define("RPT_GRIDSETUP_BOTTOMPADDING", "padding-bottom");

	define("PDFOPTS_ORIENTATION", "orientation");
	define("PDFOPTS_LEFTMARGIN", "margin-left");
	define("PDFOPTS_TOPMARGIN", "margin-top");
	define("PDFOPTS_RIGHTMARGIN", "margin-right");
	define("PDFOPTS_AUTOPAGEBREAK", "autopagebreak");
	define("PDFOPTS_LOWERMARGIN", "lowermargin");
	define("PDFOPTS_GRIDLOWERMARGIN", "gridlowermargin");
	define("PDFOPTS_DEFAULTFONTSIZE", "defaultfontsize");
	define("PDFOPTS_DEFAULTFONTFAMILY", "defaultfontfamily");

	class PdfReportColumn
	{
		public $caption, $name, $width, $align, $format, $agg;

		public $strechWidth = true;

		public function __construct($name, $caption = null, $width, $align = "L", $format = FORMAT_DEFAULT, $strechWidth = true, $agg = "")
		{
			$this->name = $name;
			if($caption == null)
				$this->caption = $name;
			else
				$this->caption = $caption;
			$this->width = $width;
			$this->align = $align;
			$this->format = $format;

			if($this->align == MGRID_ALIGN_LEFT)
				$this->align = "L";
			if($this->align == MGRID_ALIGN_RIGHT)
				$this->align = "R";
			if($this->align == MGRID_ALIGN_CENTER)
				$this->align = "C";


			$this->strechWidth = $strechWidth;
			$this->agg = $agg;
		}

		public function format($v)
		{
			return getFormatter($this->format)->encodeHuman($v);
		}
	}

	class PdfTableReport extends TCPDF
	{
		public $columns;
		public $reportTitle;
		public $maxY, $maxX, $minX, $minY;
		public $gridLowerMargin = 10;
		public $gridUpperMargin = 30;
		public $lowerMargin = 20;
		public $htmlCells = false;
		public $drawBoxes = true;

		public $gridSetup = array();

		public function finish()
		{
			$this->Output("test.pdf", "I");
		}

		public function xywhCell($x, $y, $w, $h, $s, $a = "L")
		{
			if($y != 0)
				$this->SetY($y);
			if($x != 0)
				$this->SetX($x);
			$this->MultiCell($w, $h, $s, 0, $a);
		}

		function addColumn($col)
		{
			$this->columns[$col->name] = $col;
		}

		function getGridSetup($field, $default)
		{
			if(is_array($this->gridSetup))
				if(isset($this->gridSetup[$field]))
					return $this->gridSetup[$field];
			return $default;
		}

		public $lineStyle = array(
			'width' => 0.05,
			'cap' => 'butt',
			'join' => 'miter',
			'dash' => 0,
			'color' => array(200, 200, 200),
		);

		function setDefaultFont()
		{
			$this->SetFont($this->defaultFontFamily, "", $this->defaultFontSize);
		}

		public $defaultFontFamily = REPORT_FONTFAMILY; //"helvetica";
		public $defaultFontSize = 9;

		/**
		 * @return boolean new page formed
		 */
		function checkFreePageSpace($height)
		{
			if($this->getMaxY() - $height < $this->GetY())
			{
				$this->AddPage();
				return true;
			}
			else
				return false;
		}

		function getMaxY()
		{
			if(!$this->maxY)
				$this->initFormatting();
			return $this->maxY;
		}

		function getMaxX()
		{
			if(!$this->maxX)
				$this->initFormatting();
			return $this->maxX;
		}

		function getMinX()
		{
			if(!$this->minX)
				$this->initFormatting();
			return $this->minX;
		}

		function initFormatting()
		{
			$y = $this->GetY();
			$this->SetY(-1);
			$this->maxY = $this->GetY() - $this->lowerMargin;
			$this->SetY($y);

			$x = $this->GetX();
			$this->minX = $x;
			$this->SetX(-1);
			$this->maxX = $this->GetX() - 9;
			$this->SetX($x);
		}

		function strechColumns($totalWidth = null)
		{
			if($totalWidth == null)
				$totalWidth = $this->getMaxX() - $this->getMinX();
			if(is_array($this->columns))
			{
				$ct = 0;
				$tw = $totalWidth;
				$lastStreched = null;
				foreach ( $this->columns as $col )
				{
					if($col->strechWidth)
						$ct = $ct + $col->width;
					else
						$tw = $tw - $col->width;
				}
				if($ct > 0)
				{
					$coef = $tw / $ct;
					if($coef > 0)
					{
						$nt = 0;
						foreach ( $this->columns as $col )
						{
							if($col->strechWidth)
							{
								$col->width = round($col->width * $coef);
								$lastStreched = $col;
							}
							$nt = $nt + $col->width;
						}
						if($lastStreched != null)
							$lastStreched->width = $lastStreched->width - $nt + $totalWidth;
					}
				}
			}
		}

		public $agg;

		function initAggregate()
		{
			$this->agg = array();
		}

		function processAggregate($row)
		{
			if(!is_array($this->agg))
				$this->initAggregate();

			foreach ( $this->columns as $col )
			{
				if($col->agg)
				{
					$d = 0 + $row[$col->name];
					if($col->agg == AGG_SUM)
					{
						if(!isset($this->agg[$col->name]))
							$this->agg[$col->name] = 0;
						$this->agg[$col->name] += $d;
					}
					if($col->agg == AGG_COUNT)
					{
						if(!isset($this->agg[$col->name]))
							$this->agg[$col->name] = 0;
						$this->agg[$col->name] += 1;
					}
				}
       		}
		}

		function setMetadata($name)
		{
			$this->SetCreator(APP_TITLE);
			$this->SetAuthor(app()->user()->uid);
			$this->SetTitle($name);
			$this->SetSubject($name);
			$this->SetKeywords($name);
			$this->reportTitle = $name;
		}

		public function setMasterHeaderParams()
		{
			$this->SetFont($this->defaultFontFamily, 'B', $this->defaultFontSize);
		}

		public function setMasterDataParams()
		{
			$this->SetFont($this->defaultFontFamily, '', $this->defaultFontSize);
		}

		public function setMasterTotalsParams()
		{
			$this->SetFont($this->defaultFontFamily, 'B', $this->defaultFontSize);
		}

		public function MasterHeader()
		{
			if(is_array($this->columns))
			{
				$this->rememberRowPositions();
				$this->setMasterHeaderParams();
				foreach ( $this->columns as $col)
					$this->GridCell($col->caption, $col->width, $col->align);
				$this->finishRow();
				//$this->initAggregate();
			}
		}

		public function MasterData($row)
		{
			if(is_array($this->columns))
			{
				$this->rememberRowPositions();


				if($this->checkFreePageSpace($this->gridLowerMargin))
				{
					$this->maxRowY = $this->minRowY;
					$this->MasterHeader();
				}

				$this->setMasterDataParams();
				foreach ( $this->columns as $col)
					$this->GridCell($col->format(isset($row[$col->name]) ? $row[$col->name] : ""), $col->width, $col->align);	//TODO PHP Notice:  Undefined index
				$this->finishRow();
				$this->processAggregate($row);
			}
		}

		public function MasterTotals($agg = null)
		{
			if($agg == null)
				$agg = $this->agg;

			if(is_array($this->columns))
			{
				$this->rememberRowPositions();
				$this->setMasterTotalsParams();
				foreach ( $this->columns as $col)
				{
					$d = "";
					if($col->agg)
					{
						if($col->agg == AGG_TEXT)
							$d = $agg[$col->name];
						else
							$d = $col->format($agg[$col->name]);
					}
					$this->GridCell($d, $col->width, $col->align);
				}
				$this->SetX($this->minRowX);
			}
		}

		protected function drawGridBoxes()
		{
			if(!$this->drawBoxes)
				return;
			$this->SetLineStyle($this->lineStyle);
				$this->maxRowX = $this->GetX();
				$this->Rect($this->minRowX, $this->minRowY, $this->maxRowX - $this->minRowX, $this->maxRowY - $this->minRowY);	//, "", $this->lineStyle
				$x = $this->minRowX;
				foreach ( $this->columns as $col)
				{
					if($x > $this->minRowX)
						$this->Line($x, $this->minRowY, $x, $this->maxRowY, $this->lineStyle);
					$x = $x + $col->width;
				}
		}

		function finishRow()
		{
			$this->drawGridBoxes();
			$this->SetX($this->minRowX);

			if($this->maxRowY > $this->getMaxY())
			{
				$this->AddPage();
				$this->MasterHeader();
			}
			else
			{
				$this->SetY($this->maxRowY);
			}
		}

		function rememberRowPositions()
		{
			$this->maxRowY = $this->GetY();
			$this->minRowY = $this->GetY();
			$this->minRowX = $this->GetX();
		}

		protected function GridCell_old($caption, $width, $align = "L")	//TODO remove?
		{
			return $this->GridCell_v1($caption, $width, $align);

			$x = $this->GetX();
			$y = $this->GetY();

			$this->MultiCell($width, 5, t($caption), 0, $align, false, 1, "", "", true, 0, true);

			$this->maxRowY = max($this->GetY(), $this->maxRowY);
			$this->SetY($y);
			$this->SetX($x + $width);
		}

		protected function GridCell($caption, $width, $align = "L")
		{
			$lm = 0 + $this->getGridSetup(RPT_GRIDSETUP_LEFTMARGIN, 0);
			$rm = 0 + $this->getGridSetup(RPT_GRIDSETUP_RIGHTMARGIN, 0);
			$tm = 0 + $this->getGridSetup(RPT_GRIDSETUP_TOPMARGIN, 0);
			$bm = 0 + $this->getGridSetup(RPT_GRIDSETUP_BOTTOMMARGIN, 0);

			$lp = 0 + $this->getGridSetup(RPT_GRIDSETUP_LEFTPADDING, 0);//TODO
			$rp = 0 + $this->getGridSetup(RPT_GRIDSETUP_RIGHTPADDING, 0);
			$tp = 0 + $this->getGridSetup(RPT_GRIDSETUP_TOPPADDING, 0);
			$bp = 0 + $this->getGridSetup(RPT_GRIDSETUP_BOTTOMPADDING, 0);

			$x = $this->GetX();
			$y = $this->GetY();

			$this->SetY($y + $tp);
			$this->SetX($x - $lm + $lp);
			$this->MultiCell($width + $lm + $rm - $lp - $rp, 5, t($caption), 0, $align, false, 1, "", "", true, 0, $this->htmlCells);

			$this->maxRowY = max($this->GetY() + $bp, $this->maxRowY);
			$this->SetY($y);
			$this->SetX($x + $width);
		}

		function run()
		{
			//override it in actual reports
		}
	}

	class ReportModel
	{
		public $rows;
		public $filters;
		public $bottomFilters;
		public $header;
		public $autoGroupCaption = false;
		public $groupStartNewPage = false;
		public $groupedPrintTotals = true;
		public $htmlCells = false;
		public $footerEnabled = true;

		public $grouper;

		public $pdfOptions = array(
				PDFOPTS_ORIENTATION => "P",
				PDFOPTS_LEFTMARGIN => 10,
				PDFOPTS_TOPMARGIN => 15,
				PDFOPTS_RIGHTMARGIN => 10,
				PDFOPTS_AUTOPAGEBREAK => false,
			);

		/**
		 * sets report orientation to landscape mode
		 */
		public function landscape()
		{
			$this->pdfOptions = array(
				PDFOPTS_ORIENTATION => "L",
				PDFOPTS_LEFTMARGIN => 10,
				PDFOPTS_TOPMARGIN => 15,
				PDFOPTS_RIGHTMARGIN => 10,
				PDFOPTS_AUTOPAGEBREAK => false,
				PDFOPTS_GRIDLOWERMARGIN => 5,
			);
		}

		/**
		 * Builds report from simpleform definition, commonly returned by getSimpleformComponents method of RegistryDescriptor.
		 * @param array $form
		 */
		public function buildFromSimpleForm($form)
		{
			//TODO
			foreach ($variable as $key => $value) {
				# code...
			}
		}

		/**
		 * Set up columns and sort order for ReportModel using ReportDescriptor and report setup data
		 * @param ReportDescriptor $rd
		 * @param Object $obj
		 */
		public function setupVisibleColumnsAndOrder($rd, $obj)
		{
			$this->orderBy = "";
			foreach($rd->getAvailableColumns() as $c)
			{
				$cn = AVAILABLECOLUMN_FIELD_PREFIX . $c->name;
				if(isset($obj->$cn) && $obj->$cn)
					$this->addColumn($c);
				if(isset($obj->orderBy) and $obj->orderBy == $c->name)
					$this->orderBy = $c->name;
			}
		}

		function groupBy($v = null)
		{
			if($v == null)
				$this->grouper = $v;
			else
				if(is_array($v))
					$this->grouper = $v;
				else
					$this->grouper = array($v);
		}

		function getGroupCaption($row)
		{
			$ret = "";
			if($this->autoGroupCaption)
				foreach ( $this->grouper as $g )
					$ret .= $row[$g] . " ";
			return $ret;
		}

		function dontGroup()
		{
			$this->groupBy();
		}

		function getGroupIdentifier($row)
		{
			$ret = "";
			foreach ( $this->grouper as $g )
				$ret .= $row[$g] . "|";
			return $ret;
		}

		function pdfFooter($pdf)
		{
		}

		function pdfHeader($pdf)
		{
		}

		function setupPdf($pdf)
		{
			$pdf->setFontSubsetting(PDF_FONT_SUBSETTING);
			$pdf->SetMargins($this->pdfOptions[PDFOPTS_LEFTMARGIN], $this->pdfOptions[PDFOPTS_TOPMARGIN], $this->pdfOptions[PDFOPTS_RIGHTMARGIN]);

			if(isset($this->pdfOptions[PDFOPTS_AUTOPAGEBREAK]))
				$pdf->SetAutoPageBreak($this->pdfOptions[PDFOPTS_AUTOPAGEBREAK]);
			if(isset($this->pdfOptions[PDFOPTS_LOWERMARGIN]))
				$pdf->lowerMargin = $this->pdfOptions[PDFOPTS_LOWERMARGIN];
			if(isset($this->pdfOptions[PDFOPTS_DEFAULTFONTSIZE]))
				$pdf->defaultFontSize = $this->pdfOptions[PDFOPTS_DEFAULTFONTSIZE];
			if(isset($this->pdfOptions[PDFOPTS_DEFAULTFONTFAMILY]))
				$pdf->defaultFontFamily = $this->pdfOptions[PDFOPTS_DEFAULTFONTFAMILY];
			if(isset($this->pdfOptions[PDFOPTS_GRIDLOWERMARGIN]))
				$pdf->gridLowerMargin = $this->pdfOptions[PDFOPTS_GRIDLOWERMARGIN];
			$pdf->model = $this;

			$this->footerLeftSide = t("Report exported") . " " . getFormatter(FORMAT_DATETIME_SHORT)->encodeHuman(app()->now()) . " " . app()->user()->name;
			$this->footerRightSide = "#pagedata";

			$pdf->footerEnabled = $this->footerEnabled && ((isset($this->footerLeftSide) && $this->footerLeftSide) || (isset($this->footerRightSide) && $this->footerRightSide));
			$pdf->footerLeftSide = $this->footerLeftSide;
			$pdf->footerRightSide = $this->footerRightSide;

			$pdf->htmlCells = $this->htmlCells;
		}

		function getPdf()
		{
			$arrayReportClass = defined("ARRAYREPORTCLASS") ? ARRAYREPORTCLASS : DEFAULT_ARRAYREPORTCLASS;
			$pdf = new $arrayReportClass($this->pdfOptions[PDFOPTS_ORIENTATION], PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
			$this->setupPdf($pdf);
			return $pdf;
		}

		function output($fileName = "", $objectToLink = null)
		{
			//TODO link to object for XLS output
			$output = app()->request("output", "pdf");
			if($output == "pdf")
			{
				$this->pdf = $this->getPdf();
				$this->pdfHeader($this->pdf);
				$this->pdf->run();
				$this->pdfFooter($this->pdf);
				if($fileName === "")
					$fileName = $this->getExportFileName();
				if(!is_null($objectToLink))
				{
					$tmp = app()->tempFile($fileName);
					$this->pdf->Output($tmp, "F");
					$objectToLink->appendObjFile($tmp, $fileName);
				}
				$this->pdf->Output($fileName . ".pdf", 'I');
			}

			if($output == "xls")
				$this->toXLS();

			if($output == "xml")
				$this->toXML();

			if($output == "html")
			{
				echo "html not implemented yet";	//TODO
			}
		}

		function tableRowsToXLS()
		{
			if(is_array($this->rows))
			 	foreach($this->rows as $row)
			 	{
			 		$this->y++;
		 			$this->tableRowToXLS($row);
		 		}
		}

		function tableRowToXLS($row)
		{
		 	$x = 0;
		 	foreach ( $this->columns as $col )
		 	{
		 		$x++;
		 		$this->w->write($this->w->getCoord($x, $this->y), encodeXLS($col->format(isset($row[$col->name]) ? $row[$col->name] : "")));
		 	}
		}

		function tableHeaderToXLS()
		{
			if(is_array($this->rows))
			{
			 	$this->y += 2;
			 	$x = 0;
			 	foreach ( $this->columns as $col )
			 	{
			 		$x++;
			 		$this->w->write($this->w->getCoord($x, $this->y), encodeXLS(t($col->caption)));
			 	}
			}
		}

		protected function outputPairsToXLS($a)
		{
			$bLength = count($this->columns);
		 	if(is_array($a))
			 	foreach ( $a as $k => $v )
			 	{
			 		$this->y++;
			 		$this->w->write("A" . $this->y, encodeXLS($k));
			 		$this->w->write("B" . $this->y, encodeXLS($v));
			 		$this->w->mergeCells("B" . $this->y . ":" . $this->w->getCoord($bLength, $this->y));
			 	}
		}

		function filtersToXLS()
		{
			$this->outputPairsToXLS($this->filters);
		}

		function bottomFiltersToXLS()
		{
			$this->outputPairsToXLS($this->bottomFilters);
		}

		private function getExportFileName()
		{
			$rr = "ro_" . app()->getCurrentRegistry();
			$sr = t($rr);
			if($rr == $sr)
				$sr = t("report");
			return str_replace(" ", "_", $sr) . "_" . date("Y_m_d_H_i_s");
		}

		function getXLSExporter()
		{
			$this->w = new XLSExporter();
			$this->w->sheetName = t("Report");	//substr($this->header ? $this->header : t("report"), 0, 10);
			$this->w->fileName = $this->getExportFileName() . ".xlsx";
			$this->w->getWorkbook();
			return $this->w;
		}

		function getXMLExporter($t)
		{
			$this->xml = new XMLExporter($t);
			return $this->xml;
		}

		public function toXML()
		{
		 	app()->requirePrivilegeJson(PRIVILEGE_SELECT);
			$xml = $this->getXMLExporter($this->header);

			$xml->addPairs($this->filters, "filters", "filter");
			$xml->addRows($this->rows);

			$xml->addPairs($this->bottomFilters, "footers", "footer");

			$xml->output($this->getExportFileName());
		}

		/**
		 * export report as xlsx
		 */
		public function toXLS()
		{
		 	app()->requirePrivilegeJson(PRIVILEGE_SELECT);

			$this->y = 0;
			$this->getXLSExporter();

		 	//caption
		 	$this->w->write("A1", encodeXLS($this->header));
		 	$this->w->mergeCells("A1:" . $this->w->getCoord(count($this->columns), 1));
		 	$this->y += 2;

		 	$this->filtersToXLS();
		 	$this->tableHeaderToXLS();
		 	$this->tableRowsToXLS();

		 	$this->bottomFiltersToXLS();

		 	$this->w->autosize();

			$this->w->download();
		}

		function addFilter($k, $v)
		{
			if(!is_array($this->filters))
				$this->filters = array();
			$this->filters[$k] = $v;
		}

		function addBottomFilter($k, $v)
		{
			if(!is_array($this->bottomFilters))
				$this->bottomFilters = array();
			$this->bottomFilters[$k] = $v;
		}

		function addColumn($col)
		{
			if(!isset($this->columns))
				$this->columns = array();
			if(!is_array($this->columns))
				$this->columns = array();
			$this->columns[] = $col;

			return $this;
		}

		function addRow($row)
		{
			if(!is_array($this->rows))
				$this->rows = array();
			$this->rows[] = $row;
		}

		function fillBySql($sql)
		{
			if(app()->request("dbg") === "1")
				echo $sql . "<br/>\n";
			$c = app()->getDBConnection();
			$q =& $c->query($sql);

			if(app()->isDBError($q))
				die("SQL error<hr/>$sql<hr/>");

			while($row =& $q->fetchRow(DB_FETCHMODE_ASSOC))
				$this->addRow($row);
		}

		function __construct($h = "")
		{
			if($h)
				$this->header = $h;
			else
				$this->header = t("ro_" . app()->request(REQUEST_REGISTRY));
		}
	}

	class WFWArrayReport extends PdfTableReport
	{
		public $model;

		public function run()
		{
			$this->prepareModel();
			$this->outputFilters();
			$this->Ln();

			$this->outputModel();

			$this->outputBottomFilters();
		}

		public function prepareModel()
		{
			$this->setMetadata($this->model->header);
			$this->AddPage();
			$this->SetFont($this->defaultFontFamily, "", $this->defaultFontSize);
			$this->columns = $this->model->columns;
			$this->strechColumns();
		}

		public function outputFilters()
		{
			$this->SetFont($this->defaultFontFamily, "", $this->defaultFontSize);
			if(is_array($this->model->filters))
				foreach ( $this->model->filters as $k => $v)
					$this->printFilter($k, $v);
		}

		public function outputBottomFilters()
		{
			$this->SetFont($this->defaultFontFamily, "", $this->defaultFontSize);
			if(is_array($this->model->bottomFilters))
			{
				$this->Ln();
				foreach ( $this->model->bottomFilters as $k => $v)
					$this->printFilter($k, $v);
			}
		}

		public function printFilter($k, $v)
		{
			$this->MultiCell($this->getMaxX() - $this->getMinX(), 4, $k . ": " . $v, 0, "L");
		}

		public function outputModel()
		{
			if(is_array($this->model->grouper))
				return $this->outputModelGrouped();
			if(is_array($this->model->rows))
			{
				$this->MasterHeader();
				foreach ($this->model->rows as $row)
					$this->MasterData($row);
				$this->MasterTotals();
			}
		}

		private function appendAgg(&$agg)
		{
			foreach ( $this->columns as $col )
								if($col->agg)
								{
									$d = 0 + $this->agg[$col->name];
									if($col->agg == AGG_SUM)
									{
										if(!isset($agg[$col->name]))
											$agg[$col->name] = 0;
										$agg[$col->name] += $d;
									}
									if($col->agg == AGG_COUNT)
									{
										if(!isset($agg[$col->name]))
											$agg[$col->name] = 0;
										$agg[$col->name] += $d;
									}
								}

		}

		public function GroupHeader($row)
		{
			$this->checkFreePageSpace(30);	//TODO why 30?
			$cap = $this->model->getGroupCaption($row);
			$fs = $this->FontSize;
			$this->SetFont(REPORT_FONTFAMILY, "B", 12);
			$this->MultiCell($this->maxX - $this->minX, 1, $cap, 0, "L");	//TODO page width instead of 180
			$this->SetFont(REPORT_FONTFAMILY, "", $fs);
		}

		public function outputModelGrouped()
		{
			if(is_array($this->model->rows))
			{
				$gi = "";
				$agg = array();
				foreach ($this->model->rows as $row)
				{
					$this->currentRow = $row;
					$ngi = $this->model->getGroupIdentifier($row);
					if($ngi != $gi)
					{
						if($gi != "")
						{
							$this->MasterTotals();

							if($this->model->groupStartNewPage)
								$this->AddPage();
							else
							{
								$this->Ln();
								$this->Ln();
							}

							$this->appendAgg($agg);
							$this->initAggregate();
						}
						$this->GroupHeader($row);
						$this->MasterHeader();
					}
					$this->MasterData($row);
					$gi = $ngi;
				}
				$this->MasterTotals();
				$this->appendAgg($agg);
				if($this->model->groupedPrintTotals)
				{
					$this->Ln();
					$this->Ln();
					$this->MasterTotals($agg);
				}
			}
		}


		public function Header()
		{
			parent::Header();

			$this->SetY(5);
			$this->SetFont($this->defaultFontFamily, "B", 14);
			$this->Cell(0, 6, $this->reportTitle);
			$this->SetFont($this->defaultFontFamily, "", $this->defaultFontSize);
		}

		public function Footer()
		{
			if(isset($this->footerEnabled) && $this->footerEnabled)
			{
				if(!$this->maxX)
					$this->initFormatting();
				$this->SetY(-15);
				$this->Line(10, $this->GetY(), $this->maxX, $this->GetY(), $this->lineStyle);

				$this->SetFont($this->defaultFontFamily, "", 8);
				$this->SetTextColor(150);
				$this->xywhCell(10, -13, $this->maxX - 50, 5, $this->footerLeftSide);
				if($this->footerRightSide == "#pagedata")
					$this->xywhCell($this->maxX - 35, -13, 50, 5, $this->getAliasNumPage() . '/' . $this->getAliasNbPages(), "R");

				$this->SetFont($this->defaultFontFamily, "", 8);
				$this->SetTextColor(0);

			}
			else
				parent::Footer();
		}
	}

	function encodeXLS($s)	//TODO remove
	{
		return $s;
	}

	function doSimplePrint($context, $pdf, $f)
	{
		$fn = app()->getAbsoluteFile($f);
		require $fn;
	}

	function printAndLink($pdf, $obj, $fileName)
	{
		$tmp = app()->tempFile($fileName);
		$pdf->Output($tmp, "F");
		$obj->appendObjFile($tmp, $fileName);
		$pdf->Output($fileName, 'I');	//TODO output from file?
	}
