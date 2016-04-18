<?php
/**
 * WarehouseState object used to calculate articles warehouse state
 * @author Ilja Tihhanovski <ilja.tihhanovski@gmail.com>
 * @copyright (c) 2015 Intellisoft OÜ
 *
 */

	/**
	* WarehouseState object used to calculate articles warehouse state
	*/
	class WarehouseState
	{
		public $warehouseId;
		public $articleId;
		public $modifierId;
		public $date;
		public $quantity;
		public $totalCost;
		public $cost;

		function __construct($warehouseId, $articleId, $modifierId = DEFAULT_WHMV_MODIFIER, $date = "")
		{
			$this->warehouseId = (int)$warehouseId;
			$this->articleId = (int)$articleId;
			$this->modifierId = (int)$modifierId;
			$this->date = $date;
		}

		public function calculate()
		{
			$sql = "select sum(q) as q, sum(c * q) as tc from (
				select cost as c,
				quantity *
				if(" . ($this->warehouseId > DEFAULT_WAREHOUSE ? "whDstId = " . $this->warehouseId : "whSrcId = " . DEFAULT_WAREHOUSE) . ", 1, -1) as q
				from whmv
				where articleId = {$this->articleId} and (whSrcId = {$this->warehouseId} or whDstId = {$this->warehouseId})
				" . ($this->date ? "and dt <= " . quote($this->date) : "") . "
				" . (ARTICLEMODIFIERS_ENABLED && $this->modifierId ? "and modifierId = " . $this->modifierId : "") . "
				) x";

			//echo "<hr/>$sql<hr/>\n";

			$r = app()->rowFromDB($sql);
			//print_r($r);
			$this->quantity = $r[0];
			$this->totalCost = $r[1];

			$this->cost = $this->quantity == 0 ? 0 : $this->totalCost / $this->quantity;	//TODO rounding
		}
	}