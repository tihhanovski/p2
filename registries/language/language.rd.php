<?php
/*
 * Created on Nov 29, 2011
 *
 * (c) Intellisoft
 */


	class _RegistryDescriptor extends SimpleCodedAndNamedRegistryDescriptor
	{
		/**
		 * {@inheritdoc}
		 */
		public function init($table)
		{
			$this->gridSql = "select t.id, if(t.closed = 1, 'gclosed', '') as style,
				t.closed,
				t.code, t.name,
				t.mdCreated, c.uid as creator,
				t.mdUpdated, u.uid as updater
				from $table t
				left join webuser c on c.id = t.mdCreatorId
				left join webuser u on u.id = t.mdUpdaterId";
		}

		/**
		 * {@inheritdoc}
		 */
		public function getGrid()
		{
			$ret = new RegFlexiGrid();
			$ret->sortname = "t.name";
			$ret->sortorder = "asc";
			$ret->addColumn(new StyleColumn());
			$ret->addClosedIconColumn();
			$ret->addColumn(new MGridColumn("code", "code", "t.code", 80));
			$ret->addColumn(new MGridColumn("name", "name", "t.name", 200));
			$ret->addColumn(new MGridColumn("mdCreated", "mdCreated", "t.mdCreated", 80, MGRID_ALIGN_LEFT, FORMAT_DATE));
			$ret->addColumn(new MGridColumn("creator", "creator", "c.uid", 80));
			$ret->addColumn(new MGridColumn("mdUpdated", "mdUpdated", "t.mdUpdated", 80, MGRID_ALIGN_LEFT, FORMAT_DATE));
			$ret->addColumn(new MGridColumn("updater", "updater", "u.uid", 80));

			return $ret;
		}
	}
