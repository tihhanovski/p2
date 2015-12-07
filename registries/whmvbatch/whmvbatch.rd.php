<?php
/**
 * Warehouse movement descriptor
 * @author Ilja Tihhanovski <ilja.tihhanovski@gmail.com>
 * @copyright (c) 2015 Intellisoft OÜ
 *
 */


	/**
	 * Warehouse movement registry descriptor
	 */
	class WhmvbatchRegistryDescriptor extends WHMVRegistryDescriptor
	{
		public $gridSql = "select
			b.id, b.locked,
			t.name as type, b.fullNr, b.dt,
			sw.name as whSrc, dw.name as whDst,
			sc.name as coSrc, dc.name as coDst, b.totalCost, b.totalPrice
			from whmvbatch b
			left join whmvtype t on t.id = b.typeId
			left join warehouse sw on sw.id = b.whSrcId
			left join warehouse dw on dw.id = b.whDstId
			left join company sc on sc.id = b.companySrcId
			left join company dc on dc.id = b.companyDstId";

		/**
		 * {@inheritdoc}
		 */
		public function open()
		{
			app()->requireLogin();
			$id = (int)app()->request("id");
			$batch = app()->get("whmvbatch", $id);
			if($tName = $batch->getLink("typeId")->name)
			{
				if($tName == "whinventory")
					$id = (int)$batch->whinventoryId;
				return app()->location("?action=open&registry=$tName&id=$id");
			}
			return parent::open();
		}

		/**
		 * {@inheritdoc}
		 */
		function getIndexTopToolbar()
		{
		 	$buttons = array();
		 	//no new button for this registry
			return toolbar($buttons) . $this->getTopFilterToolbarItem();
		}

		/**
		 * {@inheritdoc}
		 */
		public function getGrid()
		{
			$ret = new RegFlexiGrid();
			$ret->sortname = "b.fullNr";
			$ret->sortorder = MGRID_ORDER_ASC;
			$this->ui_addGridCol_locked($ret);
			$this->ui_addGridCol_typeId($ret);
			$this->ui_addGridCol_number($ret);
			$this->ui_addGridCol_dt($ret);
			$this->ui_addGridCol_whSrcId($ret);
			$this->ui_addGridCol_whDstId($ret);
			$this->ui_addGridCol_companySrcId($ret);
			$this->ui_addGridCol_companyDstId($ret);
			$this->ui_addGridCol_totalCost($ret);
			$this->ui_addGridCol_totalPrice($ret);
			return $ret;
		}

		protected function getSimpleformComponents($obj)
		{
			$cols = array();
			$cols[] = $this->ui_rows_articleId();
			if(app()->warehouse()->isArticleModifiersEnabled())
				$cols[] = $this->ui_rows_modifier();
			$cols[] = $this->ui_rows_unitName();
			$cols[] = $this->ui_rows_whSrcId();
			$cols[] = $this->ui_rows_whDstId();
			$cols[] = $this->ui_rows_quantity();
			$cols[] = $this->ui_rows_cost();
			$cols[] = $this->ui_rows_price();
			$cols[] = $this->ui_rows_memo();

			return array(
				$this->ui_number($obj),
				$this->ui_dt($obj),
				$this->ui_typeId($obj),
				$this->ui_whSrcId($obj),
				$this->ui_whDstId($obj),
				$this->ui_companySrcId($obj),
				$this->ui_companyDstId($obj),
				$this->ui_rowsGrid($cols),
				$this->ui_totalCost($obj),
				$this->ui_totalPrice($obj),
				$this->ui_memo($obj),
			);
		}
	}