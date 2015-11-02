<?php
/*
 * Created on Sep 29, 2011
 *
 * (c) Ilja Tihhanovski, Intellisoft
 *
 */

	define("USERSTATTYPE_CAPTION_PREFIX", "userstattype_");

	class _RegistryDescriptor extends RegistryDescriptor
	{
		public $gridSql = "select s.id, u.uid, s.dt, concat('userstattype_', t.name) as type, s.name, s.memo, a.uid as actor
			from userstats s
			left join webuser u on u.id = s.userId
			left join webuser a on a.id = s.actorId
			left join userstattype t on t.id = s.typeId";

		/**
		 * returns all set DBO for userstattype filtering
		 * @return DBO_Userstattype
		 */
		private function getTypeForFilter()
		{
			$t = app()->dbo("userstattype");
			$t->orderBy("id");
			return $t;
		}

		function appendFilter($sql, $filter)
		{
			if(isset($filter->user) && $filter->user)
				$sql = $this->addWhere($sql, "s.userId = " . ((int)$filter->user));
			if(isset($filter->actor) && $filter->actor)
				$sql = $this->addWhere($sql, "s.actorId = " . ((int)$filter->actor));
			if(isset($filter->dt1))
				$sql = $this->addWhere($sql, "s.dt >= '" . $filter->dt1 . "'");
			if(isset($filter->dt2))
				$sql = $this->addWhere($sql, "s.dt <= '" . $filter->dt2 . "'");

			$tfs = array();
			$t = $this->getTypeForFilter();
			if($t->find())
				while($t->fetch())
				{
					$f = "type" . $t->id;
					if(isset($filter->$f) && $filter->$f)
						$tfs[] = $t->id;
				}

			if($stfs = implode(",", $tfs))
				$sql = $this->addWhere($sql, "s.typeId in ($stfs)");

			return $sql;
		}

		function getFilterFields()
		{
			$fo = $this->getFilter();
			$a = array(
			 	new SelectSql($fo, "user", "user", SQL_COMBO_WEBUSER),
			 	new DatePicker($fo, "dt1", "Start"),
			 	new DatePicker($fo, "dt2", "Finish"),
			 	new SelectSql($fo, "actor", "Actor", SQL_COMBO_WEBUSER),
				);

			$t = $this->getTypeForFilter();
			if($t->find())
				while($t->fetch())
					$a[] = new CheckBox($fo, "type" . $t->id, USERSTATTYPE_CAPTION_PREFIX . $t->name);

			$a[] = filterActiveCheckbox($fo);
			return $a;
		}

		function setupFilterFormats($fo)
		{
			$fo->formats = array("kpv1" => FORMAT_DATE, "kpv2" => FORMAT_DATE);
		}

		function getGrid()
		{
			$ret = new RegFlexiGrid();
			$ret->sortname = "s.dt";
			$ret->sortorder = MGRID_ORDER_DESC;
			$ret->addColumn(new MGridColumn("user", "uid", "u.uid", 80));
			$ret->addColumn(new MGridColumn("date", "dt", "s.dt", 130, MGRID_ALIGN_LEFT, FORMAT_DATETIME));
			$ret->addColumn(new MGridColumn("Type", "type", "t.name", 180, MGRID_ALIGN_LEFT, FORMAT_TRANSLATED));
			$ret->addColumn(new MGridColumn("Name", "name", "s.name", 200, MGRID_ALIGN_LEFT, FORMAT_TRANSLATED));
			$ret->addColumn(new MGridColumn("Memo", "memo", "s.memo", 400));
			$ret->addColumn(new MGridColumn("Actor", "actor", "a.uid", 80));
			return $ret;
		}
	}
