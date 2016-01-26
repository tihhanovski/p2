<?php

	class DeferredBatchTotalCostCalculator extends ApplicationDeferredTask
	{
		public $batchId;

		public function __construct($batchId)
		{
			$this->batchId = $batchId;
		}

		public function run()
		{
			app()->debug("DeferredBatchTotalCostCalculator.run: {$this->batchId}");
			if($id = (int)$this->batchId)
			{
				$t = 0 + app()->valFromDB("select sum(round(cost * quantity, " . WHMV_TOTALCOST_ROUNDING . ")) from whmv where batchId = $id");
				app()->query("update whmvbatch set totalCost = $t where id = $id");
				app()->debug("new total: $t");
			}
		}
	}