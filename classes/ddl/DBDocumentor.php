<?php
/**
 * Context
 * @author Ilja Tihhanovski <ilja.tihhanovski@gmail.com>
 * @copyright (c) 2014 Intellisoft OÜ
 *
 */

	define("KEYWORD_DEPRECATED", "@deprecated");
	define("KEYWORD_SYSTEM", "@system");
	define("DBDOC_SHOW_COLLATION", true);
	define("DBDOC_SHOW_KEYWORDS", false);

	define("DBDOC_SHOW_SYSTEM_TABLES", false);
	define("DBDOC_SHOW_DEPRECATED_TABLES", false);
	define("DBDOC_SHOW_BACKLINKS", true);

	define("DBDOC_SHOW_CHARSET_CHANGES", false);

	define("DBDOC_CHARSET", STRUCTURE_CHARSET);
	define("DBDOC_COLLATION", STRUCTURE_COLLATION);

	/**
	 * DB Documentor class
	 */
	class DBDocTableData
	{
		public $db;
		public $table;
		public $rows;
		public $keywords;
		public $comment;
		public $collation;
		public $ld;


		function __construct($db, $t)
		{
			$this->init($db, $t);
		}

		function init($db, $t)
		{
			$this->db = $db;
			$this->table = $t;
			$tq = app()->query("select table_collation as collation, table_comment as comment " .
				"from information_schema.tables " .
				"where TABLE_SCHEMA = '{$this->db}' and TABLE_NAME = '$t'");
			$tq->fetchInto($tableData, DB_FETCHMODE_OBJECT);
			$this->comment = $tableData->comment;
			$this->collation = $tableData->collation;

			$q = app()->query("SHOW FULL COLUMNS FROM $t");
			while($q->fetchInto($o, DB_FETCHMODE_OBJECT))
				$this->rows[] = $o;

			$this->extractKeywords();

			$this->extractLinks();

			$this->fillAlterCharsetList();

		}

		function extractLinks()
		{
			$dbo = app()->dbo($this->table);
			if(app()->isDBError($dbo))
				return;
			$this->ld = array();
			foreach ($dbo->links() as $f => $lx)
			{
				$ltx = explode(":", $lx);
				$this->ld[$f] = $ltx[0];
				app()->dbDocumentor()->addBacklink($this->table, $ltx[0]);
			}
		}

		function backLinksToHtml()
		{
			$ret = "";
			if(DBDOC_SHOW_BACKLINKS && isset(app()->dbDocumentor()->backLinks[$this->table]))
				if(is_array($a = app()->dbDocumentor()->backLinks[$this->table]))
				{
					$r = array();
					foreach ($a as $t)
					{
						$r[] = "<a href=\"#$t\">$t</a>";
					}
					$ret = "<div class=\"tableBacklinks\">" . t("Relations: ") . implode("; ", $r) . "</div>";
				}

			return $ret;
		}

		function toHtml()
		{
			$ret = "";
			$ret .=  "<a name=\"{$this->table}\"/>\n<div id=\"{$this->table}\"><h2>{$this->table}</h2><table>";
			foreach ($this->rows as $o)
				$ret .= $this->rowHtml($o);
			$ret .= "</table>" .
				(DBDOC_SHOW_COLLATION ? "<div class=\"tableCollation\">Collation: {$this->collation}</div>" : "") . 
				"<div class=\"tableComments\">" . $this->comment . "</div>" . 
				(DBDOC_SHOW_KEYWORDS && isset($this->keywords) && is_array($this->keywords) ? "<div class=\"tableKeywords\">" . implode("<br/>", $this->keywords) . "</div>" : "") . 
				$this->backLinksToHtml() .
				"</div>";

			//$ret .= print_r($this->ld, 1);

			return $ret;
		}

		function isCharsetted($t)
		{
			return strtolower(substr($t, 0, 7)) == "varchar" ||
				strtolower($t) == "text" ||
				strtolower($t) == "longtext"
				;
		}

		function addAlterCharsetLine($s)
		{
			app()->dbDocumentor()->listCharset[] = $s;
		}

		function fillAlterCharsetList()
		{
			if($this->table == "waybillGrid")
				return;

			$a = false;
			foreach ($this->rows as $o)
				if($this->isCharsetted($o->Type))
					if($o->Collation != DBDOC_COLLATION)
					{
						$this->addAlterCharsetLine("alter table {$this->table} change {$o->Field} {$o->Field} {$o->Type} " . 
							"character set " . DBDOC_CHARSET . " collate " . DBDOC_COLLATION . 
							";");
						$a = true;
					}
			if($a)
			{
				$this->addAlterCharsetLine("alter table {$this->table} character set = " . DBDOC_CHARSET . ";");
				$this->addAlterCharsetLine("alter table {$this->table} collate = " . DBDOC_COLLATION . ";");
			}
		}

		function rowHtml($o)
		{
			/*
				Array
				(
				    [Field] => ArveID
				    [Type] => int(11)
				    [Collation] => 
				    [Null] => NO
				    [Key] => PRI
				    [Default] => 0
				    [Extra] => 
				    [Privileges] => select,insert,update,references
				    [Comment] => 
				)
			*/

			if(is_array($this->ld) && isset($this->ld[$o->Field]))
				$lt = $this->ld[$o->Field];
			else
				$lt = "";


			return "<tr><td>" . ($o->Key == "PRI" ? "<span class=\"pk\">#</span>" : "") . 
						"</td><td>" . 
						$o->Field . 
						"</td><td>" . $o->Type .
						"</td><td>" . 
						($lt ? "→<a href=\"#$lt\">$lt</a>" : "") .
						"</td><td>" . ($o->Comment ? $o->Comment : getDefaultMemo($o->Field)) .
						"</td></tr>";
		}

		function extractKeywords()
		{
			$kws = $this->comment;
			foreach (array("\n" => " ", "\r" => " ") as $k => $v)	//TODO do it better
				$kws = str_replace($k, $v, $kws);

			//echo $kws . "<hr/>";

			foreach(explode(" ", $kws) as $kw)
				if(substr($kw, 0, 1) == "@")
					$this->keywords[strtolower($kw)] = $kw;
		}

		function hasKeyword($kw)
		{
			return isset($this->keywords) && is_array($this->keywords) && isset($this->keywords[$kw]);
		}

	}

	class DBDocumentor
	{
		public $backLinks;
		public $listCharset;


		function header()
		{
			?><!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html><head><title>DB Documentation</title><meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<style>

	.tableGrid{
		border: 1px solid #c9c9c9;
		border-collapse: collapse;
	}

	.pk{ color: red;}

	h2{margin-bottom: 0px;}

	.tableCollation{margin: 0px; margin-left: 0px; padding-left: 20px; color: #505050; font-size: 8pt;}
	.tableComments{margin: 0px; margin-left: 0px; padding-left: 20px; font-size: 9pt;}
	.tableKeywords{margin: 0px; margin-left: 0px; padding-left: 20px; font-size: 8pt;}
	.tableBacklinks{margin: 0px; margin-left: 0px; padding-left: 20px; font-size: 8pt;}




</style><?php

			$scripts = array(
					SETUP_JQUERY,
				);

			foreach ( $scripts as $src)
				echo "<script type=\"text/javascript\" src=\"$src?v=" . JS_VERSION . "\"></script>";

			?></head><body><?php
		}

		function footer()
		{
			?><script language="javascript">

	$(function(){
		$("table").attr("border", "1").attr("cellspacing", "0").attr("cellpadding", "2").addClass("tableGrid");
	});
		

</script></body></html><?php
		}


		function run()
		{
			//if($t = app()->request("t"))
			//	return $this->table($t);

			return $this->index();
		}

		function getTables()
		{
			$ts = array();
			$q = app()->query("show tables");
			$this->db = $q->dbh->dsn["database"];
			while($q->fetchInto($o, DB_FETCHMODE_ARRAY))
				$ts[strtolower($o[0])] = new DBDocTableData($this->db, $o[0]);

			ksort($ts); //TODO PHP 5.4 | SORT_FLAG_CASE

			return $ts;
		}

		function canShow($t)
		{
			if(!DBDOC_SHOW_SYSTEM_TABLES)
				if($t->hasKeyword(KEYWORD_SYSTEM))
					return false;
			if(!DBDOC_SHOW_DEPRECATED_TABLES)
				if($t->hasKeyword(KEYWORD_DEPRECATED))
					return false;

			return true;

		}

		function index()
		{
			$this->header();
			$ts = $this->getTables();

			echo "<h1>Database {$this->db}</h1><ul>";
			foreach ($ts as $t)
				if($this->canShow($t))
					echo "<li><a href=\"#{$t->table}\">{$t->table}</a></li>";
			foreach ($ts as $t)
				if($this->canShow($t))
					echo $t->toHtml();

			if(DBDOC_SHOW_CHARSET_CHANGES)
				$this->showCharsetChanges();

			$this->footer();
		}

		function showCharsetChanges()
		{
			echo "<hr/>";
			if(is_array($this->listCharset))
				foreach ($this->listCharset as $s)
					echo $s . "<br/>";
		}

		function error($e)
		{
			echo "<h1>ERROR</h1><pre>";
			print_r($e);
		}

		function addBacklink($from, $to)
		{
			//echo "addBacklink($from, $to)<br/>";
			if(!isset($this->backLinks))
				$this->backLinks = array();
			if(!isset($this->backLinks[$to]))
				$this->backLinks[$to] = array();
			$this->backLinks[$to][] = $from;

			//print_r($this->backLinks);
			//echo "<hr/>";
		}

	}

	function getDefaultMemo($f)
	{
		$t = "dbdoc_" . strtolower($f);
		$r = t($t);
		if($r == $t)
			return "";
		else
			return $r;
	}