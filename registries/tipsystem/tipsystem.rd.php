<?php
/**
 * Tip system registry
 * @author Ilja Tihhanovski <ilja.tihhanovski@gmail.com>
 * @copyright (c) 2011 Intellisoft OÜ
 */

	/**
	 * Tip system unit registry
	 */
	class TipsystemRegistryDescriptor extends RegistryDescriptor
	{
		public $gridSql = "select id, page, control, active, mdCreated, mdUpdated from tipsystem";

		function getGrid()
		{
			$ret = new RegFlexiGrid();

			$ret->sortname = "id";
			$ret->sortorder = "desc";

			$ret->addColumn(new SimpleFlexiGridColumn("page", null, "100"));
			$ret->addColumn(new SimpleFlexiGridColumn("control", null, "100"));
			$ret->addCheckboxColumn("active");
			$ret->addColumn(new SimpleFlexiGridColumn("mdCreated", null, "150", MGRID_ALIGN_LEFT, FORMAT_DATETIME));
			$ret->addColumn(new SimpleFlexiGridColumn("mdUpdated", null, "150", MGRID_ALIGN_LEFT, FORMAT_DATETIME));

			return $ret;
		}

	}
