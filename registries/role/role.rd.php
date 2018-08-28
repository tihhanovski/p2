<?php
/**
 * User role registry
 * @author Ilja Tihhanovski <ilja.tihhanovski@gmail.com>
 * @copyright (c) 2015 Intellisoft OÜ
 */

/**
 * User role unit registry
 */
	class RoleRegistryDescriptor extends RegistryDescriptor
	{
		public $gridSql = "select t.id, t.name, t.memo from role t where t.id > 1";

		function getGrid()
		{
			$ret = new RegFlexiGrid();
			$ret->sortname = "t.name";
			$ret->sortorder = MGRID_ORDER_ASC;
			$ret->addColumn(new MGridColumn("name", "name", "t.name", 150));
			$ret->addColumn(new MGridColumn("memo", "memo", "t.memo", 300));
			return $ret;
		}

		function getChildrenTree()
		{
			return array("role" => [
				"rights" => "objectright", 
				"specialrights" => "rolespecialright"
			]);
		}

		function grantRobject()
		{
			if(is_object($context = $this->getExistingContext()))
				if(is_object($obj = $context->obj))
					if($rid = filter_var(app()->request("rid"), FILTER_SANITIZE_NUMBER_INT))
					{
						$obj->grantRobject($rid, app()->request("v") ? true : false);
						app()->putContext($context);
					}
			echo app()->jsonMessage();
		}

		function grantGlobalPrivilege()
		{
			if(is_object($context = $this->getExistingContext()))
				if(is_object($obj = $context->obj))
					if($cid = app()->request("cid"))
					{
						$obj->grantGlobalPrivilege($cid, app()->request("v") ? true : false);
						app()->putContext($context);
					}
			echo app()->jsonMessage();
		}

		function grantSpecialPrivilege()
		{
			if (is_object($context = $this->getExistingContext())) {
				if (is_object($obj = $context->obj)) {
					if ($cid = app()->request("cid")) {
						$obj->grantSpecialPrivilege($cid, app()->request("v") ? true : false);
						app()->putContext($context);
					}
				}
			}

			echo app()->jsonMessage();
		}
	}
