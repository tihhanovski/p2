<?php
/**
 * General printform helper
 * @author Ilja Tihhanovski <ilja.tihhanovski@gmail.com>
 * @copyright (c) 2016 Intellisoft OÜ
 *
 */

	/**
	 * General printform helper
	 */
	class SimpleDocumentReport
	{

		public function getModel()
		{
			if(!isset($this->model))
				$this->model = new ReportModel($this->getCaption($obj));
			return $this->model;
		}

		/**
		 * @param DB_Dataobject $obj
		 */
		public function run($obj)
		{
			app()->initReporting();

			$model = $this->getModel();

			if(is_array($arr = $this->getTopFilters($obj)))
				foreach ($arr as $c => $f)
					$model->addFilter(t($c), $f);
			if(is_array($arr = $this->getBottomFilters($obj)))
				foreach ($arr as $c => $f)
					$model->addBottomFilter(t($c), $f);

			if(is_array($arr = $this->getColumns($obj)))
				foreach ($arr as $c)
					$model->addColumn($c);

			if(is_array($ds = $this->getDataset($obj)))
				foreach ($ds as $row)
					$model->addRow($this->getRow($row));

			$model->output();
		}

		/**
		 * @param DB_Dataobject $obj
		 * @return array report rows
		 */
		public function getDataset($obj)
		{
			return $obj->rows;
		}

		/**
		 * @param DB_Dataobject $obj
		 */
		public function getTopFilters($obj)
		{
		}

		/**
		 * @param DB_Dataobject $obj
		 */
		public function getBottomFilters($obj)
		{
		}

		/**
		 * @param DB_Dataobject $obj
		 */
		public function getColumns($obj)
		{
		}

		/**
		 * @param DB_Dataobject $obj
		 */
		public function getCaption($obj)
		{
			return "";
		}

		/**
		 * @param DB_Dataobject $row document row
		 * @return array report row data
		 */
		public function getRow($row)
		{
		}
	}