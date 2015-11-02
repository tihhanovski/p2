<?php
/*
 * Created on Nov 03, 2011
 *
 * (c) Ilja Tihhanovski, Intellisoft
 *
 */


	class _RegistryDescriptor extends RegistryDescriptor
	{
		public $gridSql = "select id, name, state, typeId from robject";

		function getGrid()
		{
			$ret = new RegFlexiGrid();
			$ret->sortname = "name";
			$ret->sortorder = MGRID_ORDER_ASC;
			$ret->addColumn(new SimpleFlexiGridColumn("name", "name", "300"));
			$ret->addColumn(stateGridColumn());
			return $ret;
		}
	}