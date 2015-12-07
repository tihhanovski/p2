<?php
/**
 * Cron log registry descriptor
 * @author Ilja Tihhanovski <ilja.tihhanovski@gmail.com>
 * @copyright (c) 2011 Intellisoft OÜ
 */

	/**
	 * Cron log registry descriptor
	 */
	class CronlogRegistryDescriptor extends RegistryDescriptor
	{
		public $gridSql = "select id, memo, mdCreated, mdUpdated from cronlog";

		function getGrid()
		{
			$ret = new RegFlexiGrid();

			$ret->sortname = "mdCreated";
			$ret->sortorder = "desc";

			$ret->addColumn(new SimpleFlexiGridColumn("memo", null, "800"));
			$ret->addColumn(new SimpleFlexiGridColumn("mdCreated", null, "150", MGRID_ALIGN_LEFT, FORMAT_DATETIME));
			$ret->addColumn(new SimpleFlexiGridColumn("mdUpdated", null, "150", MGRID_ALIGN_LEFT, FORMAT_DATETIME));

			return $ret;
		}

	}
