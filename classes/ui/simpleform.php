<?php
/*
 * Created on Sep 19, 2011
 *
 * (c) Ilja Tihhanovski, Intellisoft
 *
 */

	define("DGRID_COL_SELECT", "select");
	define("DGRID_COL_TEXTBOX", "textbox");
	define("DGRID_COL_DATEPICKER", "datepicker");
	define("DGRID_COL_CHECKBOX", "checkbox");
	define("DGRID_COL_STATIC", "static");
	define("DGRID_COL_DOUBLE", "double");
	define("DGRID_COL_KEYSEL", "keysel");
	define("DGRID_COL_REORDER", "reorder");
	define("DGRID_COL_TEXTBOXAUTOCOMPLETE", "textboxautocomplete");

	define("MENUPART_DOCUMENT", 3);
	define("AVAILABLECOLUMN_FIELD_PREFIX", "showCol");

 	class DetailGridColumn
 	{
 		public $name, $caption, $control, $width, $selectOptions, $align;

 		/**
 		 * control: select, textbox, datepicker, checkbox, static, double, keysel, reorder, textboxautocomplete,
 		 *
 		 */
 		function __construct($name, $caption, $control, $width, $selectOptions = null, $align = null)
 		{
 			$this->name = $name;
 			if($caption == null)
 				$this->caption = t($name);
 			else
 				$this->caption = t($caption);
 			$this->control = $control;
 			$this->width = $width;
 			if($selectOptions != null)
 				$this->selectOptions = $selectOptions;
 			if($align != null)
 				$this->align = $align;
 		}
 	}

 	class DetailGridMoveDownColumn
 	{
 		public $name, $caption, $control, $width, $setup;
 		function __construct($name = "reorder", $caption = "&nbsp;", $width = 2)
 		{
 			$this->name = $name;
 			$this->control = "reorder";
 			$this->caption = $caption;
 			$this->width = $width;
 		}
 	}

 	class KeySelColumn
 	{
 		public $name, $caption, $control, $width, $setup;
 		function __construct($name, $caption, $width, $cls)
 		{
 			$this->name = $name;
 			if($caption == null)
 				$this->caption = t($name);
 			else
 				$this->caption = t($caption);
 			$this->control = "keysel";
 			$this->width = $width;
 			$obj = app()->dbo($cls);
 			$s = app()->dbo($obj->getForeignKeyTable($name))->getKeySelSetup($obj, $name);
 			$o = app()->dbo($s->cls);
 			$this->setup = array(
				"cols" => $s->columns,
				"id" => $o->getPrimaryKeyField(),
				"cls" => $s->cls,
				"canUpdate" => app()->canUpdate($s->cls),
				"canSelect" => app()->canSelect($s->cls)
			);
 		}
 	}

 	class DetailGrid
 	{
 		public $name, $columns, $additional, $newRowCapable;

 		function __construct($name, $columns, $additional = null)
 		{
 			$this->name = $name;
 			$this->columns = $columns;
 			$this->additional = $additional;
 		}

 		function toHtml()
 		{
 			$model = array("name" => $this->name, "cols" => $this->columns);
 			if(is_array($this->additional))
	 			foreach ($this->additional as $key => $value)
 					$model[$key] = $value;

 			return "<div class=\"formRow\" id=\"{$this->name}Grid\"></div>" .
 					"<script type=\"text/javascript\">\n" .
 					"var gm{$this->name} = " . json_encode($model) . ";\n" .
 					"\$(function(){detailGrid.build(\"#{$this->name}Grid\", gm{$this->name});});\n" .
 					"\n</script>";
 		}
 	}

 	class Layout
 	{
 		protected $elements;

 		function __construct($a)
 		{
 			$this->elements = $a;
 		}

 		function getMinimalRepresentation()
 		{
 			$ret = array();
 			if(is_array($this->elements))
 				foreach ($this->elements as $c)
 					$ret[] = $c->getMinimalRepresentation();
 			return $ret;
 		}
 	}

 	class ColumnLayout extends Layout
 	{
 		function toHtml()
 		{
 			$ret = "";
 			if(is_array($this->elements))
 				foreach ($this->elements as $c)
 				{
 					$ret .= "<div class=\"columnLayoutItem\"><div>" . $c->getCaption() . "</div><div>" . $c->getInputPart() . "</div></div>";
 				}
 			return "<div class=\"columnLayout\">$ret</div>";
 		}
 	}

 	function detailGrid($name, $columns, $add = null)
 	{
 		$x = new DetailGrid($name, $columns, $add);
 		return $x->toHtml();
 	}

	function buttonSetDefaultValue($obj, $field, $caption = "set default")
	{
		if($obj->isEditable())
			return "<div class=\"formRowAdditional\">" .
					"<div class=\"formLabel\">&nbsp;</div>" .
					"<a href=\"JavaScript:setFieldToDefault('" . $obj->fullpath . CHILD_DELIMITER . $field . "');\">" . t($caption) . "</a></div>";
		else
			return "";
	}

	function startReportButton($c = null)
	{
		$html = "";
		if(is_object($c))
		{
			$ret = "";
			foreach(array("pdf", "html", "xls", "xml") as $fmt)
			{
				$f = $fmt . "Enabled";
				if(isset($c->$f) && $c->$f)
				{
					$url = app()->url("?registry=" . app()->request("registry") . "&action=previewReport&output=$fmt");
					//$ret .= "<div class=\"startReportButton\"><a href=\"$url\" target=\"_blank\" class=\"startReportButton\"><img src=\"" . app()->url("ui/img/16/export-" . strtoupper($fmt) . ".png") . "\" border=\"0\" class=\"topmenuIcon\"/></a>" .
					//		"<span class=\"topMenuCaption\"><a href=\"$url\" target=\"_blank\">" . t(strtoupper($fmt)) . "</a></span></div>";
					$ret .= "<a href=\"$url\" target=\"_blank\" class=\"startReportButton\"><span>" .
							"<img src=\"" . app()->url("ui/img/16/export-" . strtoupper($fmt) . ".png") . "\" border=\"0\"/>" .
							t(strtoupper($fmt)) . "</span></a>";
				}
			}
			$html = lockedMemo($ret, "Start report");
		}
		else
			$html = "<div class=\"formRow\" style=\"padding-top: 20px;\">" .
				"<div class=\"formLabel\">&nbsp;</div>" .
				"<a href=\"" . app()->url("?registry=" . app()->request("registry") . "&action=previewReport") . "\" " .
				"target=\"_blank\" class=\"startReportButton\">" . t("Start report") . "</a></div>";

		return $html . "<script language=\"JavaScript\"> \$(function(){\$(\".startReportButton\").button();}); </script>";

	}

	function contextData($context = null)
	{
		if(is_null($context))
			$context = app()->getCurrentContext();

		if(!(isset($context->obj) && is_object($context->obj)))
			return "";

		app()->uiHelper()->contextDataWritten = true;
		return "<script type=\"text/javascript\"> " .
				"var obj = " . json_encode($context->obj->get_data_for_json()) . "; " .
				"</script>";
	}

	function modificationData($obj)
	{
		if(!is_object($obj))
			return "";
		$df = getFormatter(FORMAT_DATETIME);
		app()->uiHelper()->modificationDataWritten = true;
		$cl = trim(
				(is_object($c = $obj->getCreatorUser()) ? app()->getLinkedCaption($c) : "") .
				(isset($obj->mdCreated) && ($cd = $obj->mdCreated) ? " " . $df->encodeHuman($cd) : "")
			);
		$ul = trim(
				(is_object($u = $obj->getUpdaterUser()) ? app()->getLinkedCaption($u) : "") .
				(isset($obj->mdUpdated) && ($ud = $obj->mdUpdated) ? " " . $df->encodeHuman($ud) : "")
			);

		return ($cl ? lockedMemo($cl, t("Created")) : "") .
			($ul ? lockedMemo($ul, t("Updated")) : "");
	}

	function closeDocumentToolbar($obj)
	{
		if(!is_object($obj))
			return "";
		app()->uiHelper()->closeDocumentToolbarWritten = true;

		if(!$obj->isClosable())
			return "";
		if($obj->isNew())
			return "";

		$img = "z.png";

		if($obj->canCloseDocument())
		{
			$ret = t("Document is opened for usage") . ". <a href=\"JavaScript:app.closeDocument();\">" . t("Close document") . "</a>";
			$img = "doc-opened.png";
		}

		if($obj->canOpenDocument())
		{

			$ret = t("Document is closed for usage") . ". " .
				"<a href=\"JavaScript:app.reopenClosedDocument();\">" . t("Reopen document") . "</a>";
			$img = "doc-closed.png";
		}

		return "<div class=\"formRowLocked\">" .
				"<div class=\"formLabel\">&nbsp;</div><div class=\"formInputContainerLocked\">" .
				"<img src=\"" . app()->url("ui/img/16/" . $img) . "\" border=\"0\" class=\"topmenuIcon\"/>" .
				"<span class=\"topMenuCaption\">" . $ret  . "</span></div></div>";

	}

	function lockButton($context)
	{
		$o = $context->obj;
		$action = "";
		if($o->canLock())
			$action = "lock";
		if($o->canUnlock())
			$action = "unlock";
		if($action)
			return "<a href=\"JavaScript:{$action}Document();\">" .
				"<img src=\"" . app()->url("ui/img/16/print.png") . "\" border=\"0\"/>" .
				t($action) . "</a>";
		else
			return "";
	}

	function lockedMemo($value, $caption, $id = "", $addAttr = null)
	{
		$aa = "";
		if(is_array($addAttr))
			foreach ( $addAttr as $k => $v )
				$aa .= " $k=\"$v\"";
		return "<div class=\"formRowLocked\">" .
				"<div class=\"formLabel\">" . t($caption) .
				"</div><div " . ($id ? "id=\"$id\" " : "") . "class=\"formInputContainerLocked\" $aa>" . str_replace("\n", "<br/>", $value)  . "</div></div>";
	}

	function simpleLabel($caption, $bold = false)
	{
		return "<div class=\"formRowLocked\">" .
				"<div class=\"formLabel\">" . ($bold ? "<b>" : "") . sanitize(t($caption)) . ($bold ? "</b>" : "") .
				"</div></div>";
	}

	function linkedEmailsBox($obj)
	{
		$id = $obj->fullpath . "_linkedEmails";
		if($obj->isInDatabase())
			return "<div class=\"formRowLocked\">" .
					"<div class=\"formLabel\">" . t("Sent emails") ."</div>" . 
					"<div class=\"formInputContainerLocked boxedMemoUI\">" . 
					"<div id=\"$id\"></div>" .
					"<div><a href=\"JavaScript:app.loadLinkedEmails();\">" . t("Reload") . "</a></div>" .
					"</div>" .
					"</div>" .
					"<script language=\"JavaScript\"> \$(function(){app.loadLinkedEmails();}); </script>";
	}

	function toolbar($buttons)
	{
		$ret = "";
		if(is_array($buttons))
			foreach($buttons as $caption => $link)
			{
				$tcap = t($caption);
				$id = str_replace(" ", "_", strtolower($caption));
				$ctrlId = "toolbar_" . $id;
				$icon = "ui/img/16/" . $id . ".png";
				if(app()->getAbsoluteFile($icon))
					$icon = "<img src=\"" . app()->url($icon) . "\" border=\"0\" alt=\"$tcap\" class=\"topmenuIcon\"/>";
				else
					$icon = "";//"[$icon] ";
				$ret .= "<div id=\"$ctrlId\" class=\"topMenuItem\"><a href=\"JavaScript:$link;\" title=\"$tcap\" tabindex=\"-1\">" .
						$icon .
						(SETUP_TOOLBAR_CAPTIONS_VISIBLE || (!$icon) ? "<span class=\"topMenuCaption\">" . $tcap . "</span>" : "") .
						"</a></div>";
			}
		return $ret;
	}

	function debugBox($obj)
	{
		if(DEBUG)
			return "<div class=\"debugBox\"><div id=\"log\"></div><pre>" . print_r($obj, true) . "</pre></div>";
		else
			return "";
	}

	function addGridRowButton($collection, $funcsuffix="")
	{
		$ret = "<a href=\"JavaScript:addChild('$collection', addRow$funcsuffix);\" class=\"addGridRowButton\">" .
				"<img src=\"" . app()->url("ui/img/16/add.png") . "\" border=\"0\"/>" .
				"" . t("Add row") . "</a>";

		return $ret;
	}

	function linkItem($href, $caption)
	{
		return "<div class=\"mainMenuItem\" onclick=\"JavaScript:$href\">" . t($caption) . "</div>";
	}

	function hr()
	{
		return "<div style=\"clear: both;\"></div><hr/>";
	}

	function filterActiveCheckbox($fo)
	{
		return new CheckBox($fo, "active", "Filter active");
	}

	function filterShowOnStartupCheckbox($fo)
	{
		return new CheckBox($fo, "filterOnStartup", "Show filter on startup");
	}

	function tipSystemCommand($page = null)
	{
		if($page == null)
		{
			$page = app()->request(REQUEST_REGISTRY);
			if($a = app()->request(REQUEST_ACTION))
				$page .= "." . $a;
			else
			{
				if($page)	//registry set, so it is grid
					$page .= ".grid";
			}
		}

		$tip = app()->dbo("tipsystem");

		$pgs = explode(".", $page);
		if($pgs[0] != $page)
			$pgs[] = $page;

		$w = array();
		foreach ( $pgs as $p )
			$w[] = "(concat(' ', page, ' ') like '" . $tip->escape("% $p %") . "')";

		//TODO better SQL needed
		$whr = "id not in (select tipId from tipshown " .
				"where userId = " . app()->user()->getIdValue() . ") " .
				"and (" . implode(" or ", $w) . ")";

		$tips = array();
		$tip->whereAdd($whr);

		if($tip->find())
			while($tip->fetch())
				if($tip->active)
					$tips[] = array(
						"id" => $tip->getIdValue(),
						"message" => $tip->getMessage(),
						"control" => $tip->control
					);

		$tr = array(
			"cancel" => t("Cancel"),
			"cancelAll" => t("Cancel all"),
		);

		return "tipSystem.start(" . json_encode($tips) . ", " . json_encode($tr) . ");";
	}

 	function mi($mod)
 	{
 		return "<div class=\"mmi\">" .
 				"<a href=\"" . app()->url("?registry=" . $mod->name) . "\">" . t($mod->getCaption()) . "</a></div>";
 	}

 	function prevNextMonthyear($d1, $d2)
 	{
 		return lockedMemo("<a href=\"JavaScript:prevMonth('$d1', '$d2');\">" . t("Previous month") . "</a>&nbsp;&nbsp;&nbsp;" .
 			"<a href=\"JavaScript:thisMonth('$d1', '$d2');\">" . t("This month") . "</a>&nbsp;&nbsp;&nbsp;" .
 			"<a href=\"JavaScript:prevYear('$d1', '$d2');\">" . t("Previous year") . "</a>&nbsp;&nbsp;&nbsp;" .
 			"<a href=\"JavaScript:thisYear('$d1', '$d2');\">" . t("This year") . "</a>",
 			"&nbsp;");
 	}

 	function prevNextMonth($d1, $d2)
 	{
 		return lockedMemo("<a href=\"JavaScript:prevMonth('$d1', '$d2');\">" . t("Previous month") . "</a>&nbsp;&nbsp;&nbsp;" .
 			"<a href=\"JavaScript:thisMonth('$d1', '$d2');\">" . t("This month") . "</a>", "&nbsp;");
 	}

 	function prevNextYear($d1, $d2)
 	{
 		return lockedMemo("<a href=\"JavaScript:prevYear('$d1', '$d2');\">" . t("Previous year") . "</a>&nbsp;&nbsp;&nbsp;" .
 			"<a href=\"JavaScript:thisYear('$d1', '$d2');\">" . t("This year") . "</a>", "&nbsp;");
 	}

	function mainMenuNew()
	{
		//deprecated
		echo app()->uiHelper()->getMainMenu()->toHtml();
	}

	function simpleform($arr)
	{
		$ret = "";
		if(is_array($arr))
			foreach ($arr as $c)
			{
				if(is_object($c))
					$ret .= $c->toHtml();
				else
					$ret .= $c;
			}
		return $ret;
	}

	/**
	 * multiselector styled group of checkboxes
	 * @param array<Checkbox> $chks checkboxes HTML to embed
	 * @param string $caption
	 * @return string
	 */
	function applyMultiselStyle($chks, $caption)
	{
		$ret = "<div class=\"mselect mselect-list\">";
		foreach($chks as $chk)
		{
			$ret .= "<div class=\"clearBoth mselect-list-item acsItem\">" . $chk->getInputPart() . "</div>";
		}
		$ret .= "<div class=\"clearBoth\"></div></div>";
		return lockedMemo($ret, $caption);
	}

	/**
	 * available columns selector html for RegistryDescriptor
	 * @param ReportDescriptor $rd
	 * @return string
	 */
	function getAvailableColumnsSelector($rd)
	{
		$obj = $rd->getContext()->obj;
		$arr = array();
		foreach($rd->getAvailableColumns() as $c)
			$arr[] = new CheckBox($obj, AVAILABLECOLUMN_FIELD_PREFIX . $c->name, $c->caption);
		return applyMultiselStyle($arr, "Columns");
	}

	/**
	 * available columns selector html for RegistryDescriptor
	 * @param ReportDescriptor $rd
	 * @return string
	 */
	function getAvailableColumnsOrderSelector($rd)
	{
		$obj = $rd->getContext()->obj;
		$items = array();
		foreach ($rd->getAvailableColumns() as $c)
			$items[$c->name] = $c->caption;
		$x = new Select();
		return $x->prepareInput($obj, "orderBy", "Order by", $items);
	}


	function htmlReportHeader($caption)
	{
		?><!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html><head><title><?=$caption?></title><meta http-equiv="Content-Type" content="text/html; charset=utf-8" /><?php

	$scripts = array(SETUP_JQUERY);		//, "http://code.jquery.com/ui/1.10.2/jquery-ui.js"

	foreach ( $scripts as $src)
		echo "<script type=\"text/javascript\" src=\"$src\"></script>";

?></head>
<body><style type="text/css">

	@media print
	{
		.printToolbar{display: none;}
		.printToolbarPch{display: none;}
	}

	@media screen
	{
		.printToolbar
		{
			width: 100%;
			border-bottom: 1px solid #000000;
			background-color: #ffffc9;
			padding-top: 5px;
			height: 30px;
			position: fixed;
			top: 0px;
			left: 0px;
		}

		.printToolbarPch
		{
			height: 35px;
		}
	}


</style><div class="printToolbar">
	<div style="float: left; padding-left: 20px;"><b><?=$caption?></b></div>
	<div style="float: right; padding-right: 20px;"><a href="JavaScript:window.close();"><?=t("Close")?></a></div>
	<div style="float: right; padding-right: 20px;"><a href="Javascript:window.print();"><?=t("Print")?></a></div>
</div><div class="printToolbarPch"></div><?php
	}


	//deprecated
	function mainMenuItem($val)
	{
 		return "<div class=\"mainMenuItem\" onclick=\"JavaScript:openRegistry('" . $val->name . "');\">" .
 			"<img src=\"" . app()->url("ui/img/16/mi-" . $val->typeID . ".png") . "\" border=\"0\" width=\"16\" height=\"16\" class=\"mainMenuIcon\" />" .
 			t($val->getCaption()) . "</div>";
	}

