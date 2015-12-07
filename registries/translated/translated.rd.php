<?php
/**
 * Translation queue registry
 * @author Ilja Tihhanovski <ilja.tihhanovski@gmail.com>
 * @copyright (c) 2011 Intellisoft OÜ
 */

	/**
	 * Translation queue registry
	 */
	class TranslatedRegistryDescriptor extends RegistryDescriptor
	{
		public $gridSql = "select id, name, mdCreated
				from translated


				";

		function getGrid()
		{
			$ret = new RegFlexiGrid();

			$ret->sortname = "name";
			$ret->sortorder = "desc";

			$ret->addColumn(new SimpleFlexiGridColumn("name", "", "300"));
			$ret->addColumn(new SimpleFlexiGridColumn("mdCreated", "p.mdCreated", "150", MGRID_ALIGN_LEFT, FORMAT_DATETIME));

			return $ret;
		}

	}
