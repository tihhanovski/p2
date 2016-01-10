<?php
/**
 * Warehouse writeoff documents registry descriptor
 * @author Ilja Tihhanovski <ilja.tihhanovski@gmail.com>
 * @copyright (c) 2015 Intellisoft OÜ
 *
 */


	/**
	 * Warehouse writeoff documents registry descriptor
	 */
	class WhwriteoffRegistryDescriptor extends WHMVRegistryDescriptor
	{
		public $gridSql = "select
			b.id,
			b.locked,
			b.fullNr,
			b.dt,
			sw.name as whSrc,
			b.totalCost
			from whmvbatch b
			left join warehouse sw on sw.id = b.whSrcId
			where b.typeId = 5";

		/**
		 * {@inheritdoc}
		 */
		public function getGrid()
		{
			$ret = new RegFlexiGrid();
			$ret->sortname = "b.fullNr";
			$ret->sortorder = MGRID_ORDER_ASC;
			$this->ui_addGridCol_locked($ret);
			$this->ui_addGridCol_number($ret);
			$this->ui_addGridCol_dt($ret);
			$this->ui_addGridCol_whSrcId($ret);
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
			$cols[] = $this->ui_rows_cost_locked();
			$cols[] = $this->ui_rows_memo();

			return array(
				$this->getRightPanel($this->getContext()),
				$this->ui_number($obj),
				$this->ui_dt($obj),
				$this->ui_whSrcId($obj),
				$this->ui_rowsGrid($cols),
				$this->ui_totalCost($obj),
				$this->ui_memo($obj),
			);
		}
	}