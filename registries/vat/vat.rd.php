<?php
/**
 * VAT registry descriptor
 * @author Ilja Tihhanovski <ilja.tihhanovski@gmail.com>
 * @copyright (c) 06.09.2014 Intellisoft OÜ
 */


	/**
	 * VAT registry descriptor
	 */
	class VatRegistryDescriptor extends RegistryDescriptor
	{
		public $gridSql = "select t.id, if(t.closed = 1, 'gclosed', '') as style, t.closed,
				t.name, t.pct, t.memo,
				t.mdCreated, c.uid as creator,
				t.mdUpdated, u.uid as updater
				from vat t
				left join webuser c on c.id = t.mdCreatorId
				left join webuser u on u.id = t.mdUpdaterId";

		/**
		 * {@inheritdoc}
		 */
		function getGrid()
		{
			$ret = new RegFlexiGrid();
			$ret->sortname = "t.name";
			$ret->sortorder = "asc";
			$ret->addColumn(new StyleColumn());
			$ret->addClosedIconColumn();
			$ret->addColumn(new MGridColumn("Name", "name", "t.name", 200));
			$ret->addColumn(new MGridColumn("Percent", "pct", "t.pct", 100, MGRID_ALIGN_RIGHT, FORMAT_FLOAT2));
			$ret->addColumn(new MGridColumn("memo", "memo", "t.memo", 200));
			$ret->addColumn(new MGridColumn("mdCreated", "mdCreated", "t.mdCreated", 80, MGRID_ALIGN_LEFT, FORMAT_DATE));
			$ret->addColumn(new MGridColumn("creator", "creator", "c.uid", 80));
			$ret->addColumn(new MGridColumn("mdUpdated", "mdUpdated", "t.mdUpdated", 80, MGRID_ALIGN_LEFT, FORMAT_DATE));
			$ret->addColumn(new MGridColumn("updater", "updater", "u.uid", 80));
			return $ret;
		}

	}
