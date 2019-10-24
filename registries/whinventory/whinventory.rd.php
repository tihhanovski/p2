<?php
/**
 * Warehouse inventory registry descriptor
 * @author Ilja Tihhanovski <ilja.tihhanovski@gmail.com>
 * @copyright (c) 16.10.2015 Intellisoft OÜ
 */


	/**
	 * Warehouse inventory registry descriptor
	 */
	class WhinventoryRegistryDescriptor extends RegistryDescriptor
	{
		public $gridSql = "select i.id, i.locked, i.fullNr, i.dt, w.code as
				whCode, w.name as whName, g.name as gName, i.memo,
				i.mdCreated, c.uid as creator,
				i.mdUpdated, u.uid as updater
				from whinventory i
				left join warehouse w on w.id = i.whId
				left join articlegroup g on g.id = i.articlegroupId
				left join webuser c on c.id = i.mdCreatorId
				left join webuser u on u.id = i.mdUpdaterId";

		/**
		 * {@inheritdoc}
		 */
		function getGrid()
		{
			$ret = new RegFlexiGrid();
			$ret->sortname = "i.dt";
			$ret->sortorder = "desc";
			$ret->addLockboxColumn("i.locked");
			$ret->addColumn(new MGridColumn("Nr", "fullNr", "i.fullNr", 60));
			$ret->addColumn(new MGridColumn("Date", "dt", "i.dt", 100, MGRID_ALIGN_LEFT, FORMAT_DATE));
			$ret->addColumn(new MGridColumn("Warehouse code", "whCode", "w.code", 60));
			$ret->addColumn(new MGridColumn("Warehouse name", "whName", "w.name", 200));
			$ret->addColumn(new MGridColumn("Article group", "gName", "g.name", 200));
			$ret->addColumn(new MGridColumn("memo", "memo", "t.memo", 200));
			$ret->addColumn(new MGridColumn("mdCreated", "mdCreated", "i.mdCreated", 80, MGRID_ALIGN_LEFT, FORMAT_DATE));
			$ret->addColumn(new MGridColumn("creator", "creator", "c.uid", 80));
			$ret->addColumn(new MGridColumn("mdUpdated", "mdUpdated", "i.mdUpdated", 80, MGRID_ALIGN_LEFT, FORMAT_DATE));
			$ret->addColumn(new MGridColumn("updater", "updater", "u.uid", 80));
			return $ret;
		}

		/**
		 * {@inheritdoc}
		 */
		public function getChildrenTree()
		{
			return array(
				"whinventory" => array("rows" => "whinventoryrow"),
			);
		}

		public function fillQuantitiesNotFilledYet()
		{
			app()->requirePrivilegeJson(PRIVILEGE_UPDATE);
			if(is_object($context = app()->getContext($this->getContextName())))
				if(is_object($obj = $context->obj))
				{
					$obj->fillQuantitiesNotFilledYet();
					app()->putContext($context);
				}
			echo app()->jsonMessage();
		}

		public function updateWhStates()
		{
			app()->requirePrivilegeJson(PRIVILEGE_UPDATE);
			if(is_object($context = app()->getContext($this->getContextName())))
				if(is_object($obj = $context->obj))
				{
					$obj->updateWhStates();
					app()->putContext($context);
				}
			echo app()->jsonMessage();			
		}

	}
