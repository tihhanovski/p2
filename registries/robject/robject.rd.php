<?php
/**
 * Registries list registry descriptor
 * @author Ilja Tihhanovski <ilja.tihhanovski@gmail.com>
 * @copyright (c) 2015 Intellisoft OÜ
 */

	/**
	 * Registries list registry descriptor
	 */
	class RobjectRegistryDescriptor extends RegistryDescriptor
	{
		public $gridSql = "select id, name, typeId from robject";

		public function getGrid()
		{
			$ret = new RegFlexiGrid();
			$ret->sortname = "name";
			$ret->sortorder = MGRID_ORDER_ASC;
			$ret->addColumn(new SimpleFlexiGridColumn("name", "name", "300"));
			//$ret->addColumn(stateGridColumn());
			return $ret;
		}
	}