<?php
/**
 * HtmlComponent
 * @author Ilja Tihhanovski <ilja.tihhanovski@gmail.com>
 * @copyright (c) 2015 Intellisoft OÜ
 *
 */

	class HtmlComponent
	{
		public $controls;
		public $tag;
		public $attrs;

		public function __construct($controls = null, $tag = "div", $attrs = null)
		{
			$this->controls = is_array($controls) ? $controls : array();
			$this->attrs = is_array($attrs) ? $attrs : array();
			$this->tag = $tag;
		}

		public function getAttr($n)
		{
			if(isset($this->attrs[$n]))
				return $this->attrs[$n];
			else
				return "";
		}

		public function setAttr($n, $v)
		{
			if($v)
				$this->attrs[$n] = $v;
			else
				if(isset($this->attrs[$n]))
					unset($this->attrs[$n]);
		}

		public function getAttrsHtml()
		{
			$r = array();
			foreach ($this->attrs as $k => $v)
				$r[] = $k . "=\"" . htmlspecialchars($v) . "\"";
			return implode(" ", $r);
		}

		public function getStartTagHtml()
		{
			$a = $this->getAttrsHtml();
			if($this->tag)
				return "<" . $this->tag . ($a ? " " . $a : "") . ">";
			else
				return "";
		}

		public function getEndTagHtml()
		{
			if($this->tag)
				return "</" . $this->tag . ">";
			else
				return "";
		}

		/**
		 * @return string generated HTML
		 */
		public function toHtml()
		{
			$ret = "";
			foreach ($this->controls as $c)
			{
				if(is_object($c))
					$ret .= $c->toHtml();
				else
					$ret .= $c;
			}
			return $this->getStartTagHtml() . $ret . $this->getEndTagHtml();
		}

	}