<?php
/**
 * Article registry descriptor
 * @author Ilja Tihhanovski <ilja.tihhanovski@gmail.com>
 * @copyright (c) 2015 Intellisoft OÜ
 */


	/**
	 * Article registry descriptor
	 */
	class ArticleRegistryDescriptor extends RegistryDescriptor
	{
		public $gridSql = "select m.id, if(m.closed = 1, 'gclosed', '') as style,
				m.closed,
				m.code, m.name,
				y.name as un, m.memo

				from article m
				left join unit y on y.id = m.unitId
				";

		/**
		 * {@inheritdoc}
		 */
		public function getGrid()
		{
			$ret = new RegFlexiGrid();
			$ret->sortname = "m.name";
			$ret->sortorder = "asc";
			$ret->addColumn(new StyleColumn());
			$ret->addClosedIconColumn();
			$ret->addColumn(new MGridColumn("Code", "code", "m.code", 100));
			$ret->addColumn(new MGridColumn("Name", "name", "m.name", 300));
			$ret->addColumn(new MGridColumn("Unit", "un", "y.name", 60));
			$ret->addColumn(new MGridColumn("Memo", "memo", "m.memo", 200));
			return $ret;
		}

		/**
		 * {@inheritdoc}
		 */
		function getChildrenTree()
		{
			return array("article" => array());
		}

		/**
		 * {@inheritdoc}
		 */
		function appendFilter($sql, $filter)
		{
			//if(isset($filter->group) && ($filter->group))
			//	$sql = $this->addWhere($sql, "m.groupId = {$filter->group}");
			if(isset($filter->unit) && ($filter->unit))
				$sql = $this->addWhere($sql, "m.unitId = {$filter->unit}");
			if($filter->showClosed + $filter->showActive == 1)
			{
				if($filter->showClosed)
					$sql = $this->addWhere($sql, "m.closed = 1");
				if($filter->showActive)
					$sql = $this->addWhere($sql, "m.closed = 0");
			}

			return $sql;
		}

		/**
		 * {@inheritdoc}
		 */
		function getFilterFields()
		{
			$fo = $this->getFilter();
			return array(
			 	new SelectSql($fo, "unit", "Unit", SQL_COMBO_UNIT),
			 	new CheckBox($fo, "showClosed", "Show closed"),
			 	new CheckBox($fo, "showActive", "Show active"),
			 	filterActiveCheckbox($fo)
			);
		}
	}