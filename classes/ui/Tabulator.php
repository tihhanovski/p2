<?php
	/*
	* Created on Sep 30, 2013
	* (c) ilja@intellisoft.ee
	*/

	class TabulatorItem
	{
		public $id, $caption, $contents;

		function __construct($id, $caption, $contents)
		{
			$this->id = $id;
			$this->caption = $caption;
			$this->contents = $contents;
		}

		public function getContentsHtml()
		{
			if(is_array($this->contents))
			{
				$ret = "";
				foreach ($this->contents as $i)
					if(is_object($i))
						$ret .= $i->toHtml();
					else
						$ret .= $i;
			}
			else
				$ret = $this->contents;
			return $ret;
		}
	}

	class Tabulator
	{
		protected $_items, $id;

		function __construct($id, $items = null)
		{
			$this->id = $id;
			if(!is_null($items))
				$this->_items = $items;
		}

		function items()
		{
			if(!isset($this->_items) || !is_array($this->_items))
				$this->_items = array();
			return $this->_items;
		}

		function addItem($i)
		{
			$this->items();
			$this->items[$i->id] = $i;
		}

		function toHtml()
		{
			$ret = "<div class=\"formRow\"><div id=\"{$this->id}\"><ul>";

			foreach($this->_items as $k => $i)
				$ret .= "<li><a href=\"#{$k}\">" . t($i->caption) . "</a></li>";
			$ret .= "</ul>";

			foreach($this->_items as $k => $i)
				$ret .= "<div id=\"{$k}\">" . $i->getContentsHtml() . "<div style=\"clear: both;\"></div></div>";

			$ret .= "</div><script type=\"text/javascript\"> $(function() {\$( \"#{$this->id}\" ).tabs();}); </script>";

			return $ret;
		}
	}