<?php
/*
 * Created on Sep 19, 2011
 *
 * (c) Ilja Tihhanovski, Intellisoft
 *
 */


	class _RegistryDescriptor extends RegistryDescriptor
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
