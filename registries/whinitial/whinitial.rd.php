<?php
/*
 * Created on Nov 03, 2011
 *
 * (c) Ilja Tihhanovski, Intellisoft
 *
 */


	class _RegistryDescriptor extends WHMVRegistryDescriptor
	{
		public $gridSql = "select
			b.id, b.locked,
			b.fullNr, b.dt,
			dw.name as whDst,
			b.totalCost
			from whmvbatch b
			left join whmvtype t on t.id = b.typeId
			left join warehouse dw on dw.id = b.whDstId
			where b.typeId = 1";

		function getGrid()
		{
			$ret = new RegFlexiGrid();
			$ret->sortname = "b.fullNr";
			$ret->sortorder = MGRID_ORDER_ASC;
			$this->ui_addGridCol_locked($ret);
			$this->ui_addGridCol_number($ret);
			$this->ui_addGridCol_dt($ret);
			$this->ui_addGridCol_whDstId($ret);
			$this->ui_addGridCol_totalCost($ret);
			return $ret;
		}

		protected function getSimpleformComponents($obj)
		{
			$cols = array();
			$cols[] = $this->ui_rows_articleId();
			if(app()->warehouse()->isArticleModifiersEnabled())
				$cols[] = $this->ui_rows_modifier();
			$cols[] = $this->ui_rows_unitName();
			$cols[] = $this->ui_rows_quantity();
			$cols[] = $this->ui_rows_cost();
			$cols[] = $this->ui_rows_memo();

			return array(
					$this->ui_number($obj),
					$this->ui_dt($obj),
					$this->ui_whDstId($obj),
					$this->ui_rowsGrid($cols),
					$this->ui_totalCost($obj),
					$this->ui_memo($obj),
				);
		}
	}