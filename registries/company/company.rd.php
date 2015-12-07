<?php
/**
 * Company registry descriptor
 * @author Ilja Tihhanovski <ilja.tihhanovski@gmail.com>
 * @copyright (c) 2015 Intellisoft OÜ
 */

	/**
	 * Company registry descriptor
	 */
 	class CompanyRegistryDescriptor extends RegistryDescriptor
	{
		public $gridSql = "select t.id, if(t.closed = 1, 'gclosed', '') as style,
				t.closed,
				t.code, t.name,
				t.regCode, t.vatCode, t.addr, t.contact, t.phone, t.email, t.memo,
				t.mdCreated, c.uid as creator,
				t.mdUpdated, u.uid as updater
				from company t
				left join webuser c on c.id = t.mdCreatorId
				left join webuser u on u.id = t.mdUpdaterId";

		/**
		 * {@inheritdoc}
		 */
		function getGrid()
		{
			$ret = new RegFlexiGrid();

			$ret->sortname = "code";
			$ret->sortorder = "asc";
			$ret->addColumn(new StyleColumn());
			$ret->addClosedIconColumn();
			$ret->addColumn(new MGridColumn("code", "code", "t.code", 200));
			$ret->addColumn(new MGridColumn("name", "name", "t.name", 300));
			$ret->addColumn(new MGridColumn("Reg no", "regCode", "t.regCode", 80));
			$ret->addColumn(new MGridColumn("VAT no", "vatCode", "t.vatCode", 80));
			$ret->addColumn(new MGridColumn("Address", "addr", "t.addr", 200));
			$ret->addColumn(new MGridColumn("Contact", "contact", "t.contact", 80));
			$ret->addColumn(new MGridColumn("Phone", "phone", "t.phone", 80));
			$ret->addColumn(new MGridColumn("Email", "email", "t.email", 80));
			$ret->addColumn(new MGridColumn("Memo", "memo", "t.memo", 120));
			$ret->addColumn(new MGridColumn("mdCreated", "mdCreated", "t.mdCreated", 80, MGRID_ALIGN_LEFT, FORMAT_DATE));
			$ret->addColumn(new MGridColumn("creator", "creator", "c.uid", 80));
			$ret->addColumn(new MGridColumn("mdUpdated", "mdUpdated", "t.mdUpdated", 80, MGRID_ALIGN_LEFT, FORMAT_DATE));
			$ret->addColumn(new MGridColumn("updater", "updater", "u.uid", 80));
			return $ret;
		}
	}