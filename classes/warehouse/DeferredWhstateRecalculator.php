<?php

	class DeferredWhstateRecalculator extends ApplicationDeferredTask
	{
		public $warehouseId;
		public $articleId;

		public function __construct($warehouseId, $articleId)
		{
			$this->warehouseId = (int)$warehouseId;
			$this->articleId = (int)$articleId;
		}

		public function run()
		{
			if($this->warehouseId > DEFAULT_WAREHOUSE && $this->articleId)
				app()->warehouse()->saveWarehouseState($this->warehouseId, $this->articleId);
		}
	}