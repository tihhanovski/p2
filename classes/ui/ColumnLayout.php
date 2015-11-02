<?php
/**
 * ColumnLayout pane
 * @author Ilja Tihhanovski <ilja.tihhanovski@gmail.com>
 * @copyright (c) 2015 Intellisoft OÜ
 *
 */


	class HtmlColumnLayout extends HtmlComponent
	{
		public function __construct($controls = null)
		{
			parent::__construct($controls, "div", array("class" => "columnLayout"));
		}
	}