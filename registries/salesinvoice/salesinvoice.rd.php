<?php
/**
 * Sales invoice registry descriptor
 * @author Ilja Tihhanovski <ilja.tihhanovski@gmail.com>
 * @copyright (c) 2015 Intellisoft OÜ
 */


	/**
	 * Sales invoice registry descriptor
	 */
	class SalesinvoiceRegistryDescriptor extends RegistryDescriptor
	{
		public $gridSql = "select
			i.id, i.locked,
			i.fullNr,
			i.dt,
			cc.name as customer,
			pc.name as payer,
			i.totalWithVat
			from salesinvoice i
			left join company cc on cc.id = i.customerId
			left join company pc on pc.id = i.payerId";

		/**
		 * {@inheritdoc}
		 */
		public function getGrid()
		{
			$ret = new RegFlexiGrid();
			$ret->sortname = "i.fullNr";
			$ret->sortorder = MGRID_ORDER_ASC;
			$ret->addLockboxColumn("i.locked");
			$ret->addColumn(new MGridColumn("Number", "fullNr", "b.fullNr", 100));
			$ret->addColumn(new MGridColumn("Date", "dt", "b.dt", 65, MGRID_ALIGN_LEFT, FORMAT_DATE));
			$ret->addColumn(new MGridColumn("Customer", "customer", "cc.name", 120));
			$ret->addColumn(new MGridColumn("Payer", "payer", "pc.name", 120));
			$ret->addColumn(new MGridColumn("Total sum", "totalWithVat", "b.totalWithVat", 120, MGRID_ALIGN_RIGHT, FORMAT_FLOAT2));
			return $ret;
		}

		/**
		 * {@inheritdoc}
		 */
		function getChildrenTree()
		{
			return array("salesinvoice" => array("rows" => "salesinvoicerow"));
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
			/*$fo = $this->getFilter();
			return array(
			 	new SelectSql($fo, "unit", "Unit", SQL_COMBO_UNIT),
			 	new CheckBox($fo, "showClosed", "Show closed"),
			 	new CheckBox($fo, "showActive", "Show active"),
			 	filterActiveCheckbox($fo)
			);*/
		}
	}