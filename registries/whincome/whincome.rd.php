<?php
/**
 * Warehouse incomes registry descriptor
 * @author Ilja Tihhanovski <ilja.tihhanovski@gmail.com>
 * @copyright (c) 2011 Intellisoft OÜ
 *
 */


	/**
	 * Warehouse incomes registry descriptor
	 */
	class WhincomeRegistryDescriptor extends WHMVRegistryDescriptor
	{
		public $gridSql = "select
			b.id, b.locked,
			b.fullNr, b.dt,
			sc.name as coSrc,
			dw.name as whDst,
			b.totalCost
			from whmvbatch b
			left join warehouse dw on dw.id = b.whDstId
			left join company sc on sc.id = b.companySrcId
			where b.typeId = 2";

		/**
		 * {@inheritdoc}
		 */
		function getGrid()
		{
			$ret = new RegFlexiGrid();
			$ret->sortname = "b.fullNr";
			$ret->sortorder = MGRID_ORDER_ASC;
			$this->ui_addGridCol_locked($ret);
			$this->ui_addGridCol_number($ret);
			$this->ui_addGridCol_dt($ret);
			$this->ui_addGridCol_companySrcId($ret);
			$this->ui_addGridCol_whDstId($ret);
			$this->ui_addGridCol_totalCost($ret);
			return $ret;
		}

		protected function getSimpleformComponents($obj)
		{
			$t = $this->isObjEditable() ? "double" : "static";
			$cols = array();
			$cols[] = $this->ui_rows_articleId();
			if(app()->warehouse()->isArticleModifiersEnabled())
				$cols[] = $this->ui_rows_modifier();
			$cols[] = $this->ui_rows_unitName();
			$cols[] = $this->ui_rows_whDstId();
			$cols[] = $this->ui_rows_quantity();
			$cols[] = $this->ui_rows_cost();
			$cols[] = $this->ui_rows_memo();

			return array(
				$this->getRightPanel($this->getContext()),
				$this->ui_number($obj),
				$this->ui_dt($obj),
				$this->ui_companySrcId($obj),
				$this->ui_whDstId($obj),
				$this->ui_rowsGrid($cols),
				$this->ui_totalCost($obj),
				$this->ui_memo($obj),
			);
		}
	}