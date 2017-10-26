<?php
/*
 * Created on Sep 11, 2011
 *
 * (c) Ilja Tihhanovski, Intellisoft
 *
 */

 	define("MGRID_ALIGN_LEFT", "left");
 	define("MGRID_ALIGN_RIGHT", "right");
 	define("MGRID_ALIGN_CENTER", "center");

 	define("MGRID_ORDER_ASC", "asc");
 	define("MGRID_ORDER_DESC", "desc");

 	function stateGridColumn($col = "state")
 	{
 		return new SimpleFlexiGridColumn("state", $col, "40", MGRID_ALIGN_CENTER, FORMAT_DEFAULT, "state");
 	}

 	class StyleColumn extends FlexiGridColumn
 	{
 		function __construct()
 		{
 			$this->visible = false;
 			$this->name = "##style";
 		}
 	}

	class SimpleFlexiGridColumn extends FlexiGridColumn
	{
		function __construct($display, $name = null, $width, $align = MGRID_ALIGN_LEFT, $format = "", $handler = "", $sortable = true)
		{
			$this->init($display, $name, null, $width, $align, $format, $handler, $sortable);
		}
	}

 	class MGridColumn extends SimpleFlexiGridColumn
 	{
		function __construct($display, $name = null, $findSql = null, $width, $align = MGRID_ALIGN_LEFT, $format = "", $handler = "", $sortable = true)
		{
			$this->init($display, $name, $findSql, $width, $align, $format, $handler, $sortable);
		}
 	}

	class FlexiGridColumn
	{
		public $display, $name, $width, $sortable, $align, $handler, $findSql;
		public $visible = true;
		public $printable = true;
		protected $format = FORMAT_DEFAULT;

		function init($display, $name = null, $findSql = null, $width, $align = MGRID_ALIGN_LEFT, $format = "", $handler = "", $sortable = true)
		{
			$this->display = t($display);
			if($name == null)
				$this->name = $display;
			else
				$this->name = $name;

			
			$this->findSql = is_null($findSql) ? $name : $findSql;

			$this->width = $width;
			$this->align = $align;
			$this->sortable = $sortable;

			if($format)
				$this->format = $format;
			$this->handler = $handler;
		}

		function isPrintable()
		{
			return $this->visible && $this->printable;
		}

		function format($s)
		{
			$ret = getFormatter($this->format)->encodeHuman($s);
			//echo "format " . $this->format . ": " . $s . " -> " . $ret . "<br/>";
			return $ret;
		}

		function getFormat()
		{
			return $this->format;
		}
	}

	class FlexiGridButton
	{
		public $name, $bclass, $onpress, $separator;
	}

	class FlexiGridSearchItem
	{
		public $display, $name;
		public $isdefault = false;
	}

	class RegFlexiGrid extends FlexiGrid
	{
		private $reqPart;
		public $modifier;

		function __construct($modifier = "")
		{
			$this->modifier = $modifier;
			$this->reqPart = REQUEST_REGISTRY . "=" . app()->request(REQUEST_REGISTRY);
			$this->url = app()->url("?action=gridData&" . $this->reqPart . ($modifier ? "&mod=" . $modifier : ""));
			//$this->url = "griddata.php?" . $this->reqPart;
		}



		function toHtml()
		{
			$id = "grid";
			$ret = "<table id=\"$id\"></table>" .
					wrapScript(
					"function procMe( celDiv, id ) {\$( celDiv ).click( function() {document.location = \"" . app()->url() . "?action=open&" . $this->reqPart . "&id=\" + id;});}\n\n" .
					"\$(document).ready(function(){" .
					"var gridModel = " . json_encode($this) . ";" .
					"gridModel.height = \$(window).height() - \$(\".pageHeader\").height() - 62;\n" .
					"for(x = 0; x < gridModel.colModel.length; x++) gridModel.colModel[x].process = procMe; " .
					"\$(\"#$id\").flexigrid(gridModel);\n\n" .
					"});");

			return $ret;
		}
	}


	class FlexiGrid
	{
		public $url;
		public $dataType = "json";
		public $keyboardNavigation = GRID_KEYBOARD_NAVIGATION;

		public $colModel;

		function getSortSqlColumn($name)
		{
			if(is_array($this->colModel))
				foreach ($this->colModel as $col)
					if($col->name == $name)
						return $col->findSql;
			return $name;
		}

		function addColumn($col)
		{
			if(!is_array($this->colModel))
				$this->colModel = array();
			$this->colModel[] = $col;
		}

		function addCheckboxColumn($caption, $column = "", $width = 60)
		{
			$c = new SimpleFlexiGridColumn($caption, $column == "" ? $caption : $column, $width, MGRID_ALIGN_CENTER, FORMAT_DEFAULT, "checkbox");
			$c->printable = false;
			$this->addColumn($c);
		}

		function addIconColumn($column = "icon")
		{
			$c = new SimpleFlexiGridColumn("", $column, 16, MGRID_ALIGN_CENTER, FORMAT_ICON, "icon");
			$c->printable = false;
			$this->addColumn($c);
		}

		function addLockboxColumn($column = "locked")
		{
			$c = new SimpleFlexiGridColumn("", $column, 16, MGRID_ALIGN_CENTER, FORMAT_DEFAULT, "lockbox");
			$c->printable = false;
			$this->addColumn($c);
		}

		function addSelectionColumn($column = "sel")
		{
			$c = new SimpleFlexiGridColumn("", $column, 16, MGRID_ALIGN_CENTER, FORMAT_DEFAULT, "selectionbox");
			$c->printable = false;
			$this->addColumn($c);
		}

		function addCommentsColumn($column = "mdCommentsCount")
		{
			$c = new SimpleFlexiGridColumn("", $column, 16, MGRID_ALIGN_CENTER, FORMAT_DEFAULT, "comments");
			$c->printable = false;
			$this->addColumn($c);
		}

		function addClosedIconColumn($column = "closed")
		{
			$c = new SimpleFlexiGridColumn("", $column, 16, MGRID_ALIGN_CENTER, FORMAT_DEFAULT, "closedicon");
			$c->printable = false;
			$this->addColumn($c);
		}

		function addStyleColumn()
		{
			$this->addColumn(new StyleColumn());
		}

		public $sortname;
		public $sortorder;
		public $usepager = true;
		public $title = "";
		public $useRp = true;
		public $rp = 50;
		public $showTableToggleBtn = false;
		public $width = "100%";
		public $height = "300";
		public $resizable = false;
		public $fixedHeight = 0;
		public $filterWidth = 400;
		public $selectionEnabled = false;

		function toHtml($id)
		{
			$ret = "<table id=\"$id\"></table>" .
					wrapScript("\$(\"#grid\").flexigrid(" . json_encode($this) . ");");

			return $ret;
		}
	}

	function addUpdatedAndChangedColumns($grid, $tableAlias = "t", $creatorAlias = "c", $updaterAlias = "u")
	{
		$grid->addColumn(new MGridColumn("mdCreated", "mdCreated", $tableAlias . ".mdCreated", 80, MGRID_ALIGN_LEFT, FORMAT_DATE));
		$grid->addColumn(new MGridColumn("creator", "creator", $creatorAlias . ".uid", 100));
		$grid->addColumn(new MGridColumn("mdUpdated", "mdUpdated", $tableAlias . ".mdUpdated", 80, MGRID_ALIGN_LEFT, FORMAT_DATE));
		$grid->addColumn(new MGridColumn("updater", "updater", $updaterAlias . ".uid", 100));
	}