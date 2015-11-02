<?php
/**
 * Users registry descriptor
 * @author Ilja Tihhanovski <ilja.tihhanovski@gmail.com>
 * @copyright (c) 2011 Intellisoft OÜ
 *
 */


	/**
	 * Users registry descriptor
	 */
	class _RegistryDescriptor extends RegistryDescriptor
	{
		public $gridSql = "select t.id, if(t.closed = 1, 'gclosed', '') as style,
				t.closed,
				t.uid, t.name, t.email, t.roles,
				t.mdCreated, c.uid as creator,
				t.mdUpdated, u.uid as updater
				from webuser t
				left join webuser c on c.id = t.mdCreatorId
				left join webuser u on u.id = t.mdUpdaterId
				where t.id > 1";

		/**
		 * {@inheritdoc}
		 */
		public function getGrid()
		{
			$ret = new RegFlexiGrid();

			$ret->sortname = "t.uid";
			$ret->sortorder = MGRID_ORDER_ASC;

			$ret->addColumn(new StyleColumn());
			$ret->addClosedIconColumn();
			$ret->addColumn(new MGridColumn("uid", "uid", "t.uid", 100));
			$ret->addColumn(new MGridColumn("name", "name", "t.name", 200));
			$ret->addColumn(new MGridColumn("email", "email", "t.email", 200));
			$ret->addColumn(new MGridColumn("roles", "roles", "t.roles", 500));

			addUpdatedAndChangedColumns($ret);

			return $ret;
		}

		/**
		 * Initializes sorting for grid query.
		 * Called from initGridVariables, sets $sqlSort.
		 * One can override it to change default sort behaviour
		 * @return void
		 */
		protected function initSort()
		{
		 	$this->sqlSort = " order by t.closed asc";
		 	if($this->sortname)
		 	{
		 		//$this->modifier:
				app()->setUserProperty($this->registry . ".sortname", $this->sortname);
				app()->setUserProperty($this->registry . ".sortorder", $this->sortorder);

		 		$this->sqlSort .= ", " . $this->sortname;
		 		if($this->sortorder)
		 			$this->sqlSort .= " " . $this->sortorder;

		 		if(isset($this->secondSortName) && $this->secondSortName)
		 			$this->sqlSort .= ", " . $this->secondSortName .
		 			($this->secondSortOrder ? " " . $this->secondSortOrder : ($this->sortorder ? " " . $this->sortorder : ""));
		 	}
		}

		/**
		 * {@inheritdoc }
		 */
		public function getChildrenTree()
		{
			return array("webuser" => array("userroles" => "userrole"));
		}

		/**
		 * change password from user registry (for admin)
		 * @return void
		 */
		public function passwd()
		{
			app()->requirePrivilegeJson(PRIVILEGE_UPDATE);

			if(is_object($context = app()->getContext($this->getContextName())))
			{
				if(is_object($obj = $context->obj) && $obj->getIdValue())
				{
					$new = app()->request("new");
					$u = app()->get("webuser", $obj->getIdValue());
					$u->pwd = $new;
					if($err = $u->getPasswordError())
						die(app()->jsonMessage(RESULT_ERROR, t($err)));
					$sql = "update " . $obj->__table .
						" set pwd = password('" . $obj->escape($new) . "')" .
						" where id = " . $obj->getIdValue();
					$obj->getDatabaseConnection()->query($sql);
					app()->addWarning(new Warning("Password changed"));
					app()->addUserStat($obj, "password changed", "", "ok", USERSTATTYPE_MANIPULATION);
					echo app()->jsonMessage(RESULT_OK, "ok");
				}
			}
		}
	}
