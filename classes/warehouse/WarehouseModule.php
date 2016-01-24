<?php
/**
 * Warehouse data module
 * @author Ilja Tihhanovski <ilja.tihhanovski@gmail.com>
 * @copyright (c) 2015 Intellisoft OÜ
 *
 */


/**
 * Warehouse global functions such as cost calculation etc
*/
class WarehouseModule
{
	/**
	 * gets default whmvtype id using registry request var as source of information
	 * @return int
	 */
	public function getWhmvType()
	{
        $t = app()->dbo("whmvtype");
        $t->name = app()->request(REQUEST_REGISTRY);
        if($t->find(true))
            return $t->id;
        return DEFAULT_WHMVTYPE_ID;
	}

	public function getDefaultSrcWarehouse()
	{
		$t = $this->getWhmvType();
		if($t == WHMVTYPE_OUTCOME || $t == WHMVTYPE_WRITEOFF)
			return $this->getDefaultWarehouseId();
		return DEFAULT_WAREHOUSE;
	}

	public function getDefaultDstWarehouse()
	{
		$t = $this->getWhmvType();
		if($t == WHMVTYPE_INITIAL || $t == WHMVTYPE_INCOME)
			return $this->getDefaultWarehouseId();
		return DEFAULT_WAREHOUSE;
	}

	public function getDefaultWarehouseId()
	{
		return app()->system()->dynDefaultWarehouseId;
	}

	public function isArticleModifiersEnabled()
	{
		return ARTICLEMODIFIERS_ENABLED;
	}

	public function init()
	{
		$defaults = array(
			"FORMAT_COST_WAREHOUSE" => FORMAT_FLOAT6,
			"FORMAT_QUANTITY_WAREHOUSE" => FORMAT_FLOAT6,
			"FORMAT_PRICE_WAREHOUSE" => FORMAT_FLOAT6,
			"ARTICLEMODIFIERS_ENABLED" => false,
			);

		foreach ($defaults as $f => $ft)
			if(!defined($f))
				define($f, $ft);
	}

	public function __construct()
	{
		$this->init();
	}

	private function qryAsArray($sql, $fm = DB_FETCHMODE_ASSOC)
	{
		$ret = array();
		$q = app()->query($sql);
		while($q->fetchInto($o, $fm))
			$ret[] = $o;
		return $ret;
	}

	/**
	 *
	 */
	public function getWarehouseState($warehouseId, $articleId, $modifierId = DEFAULT_WHMV_MODIFIER, $date = "")
	{
		$s = new WarehouseState($warehouseId, $articleId, $modifierId, $date);
		$s->calculate();
		return $s;
	}

	private function dbg($s)
	{
		if(!isset($this->_dbg))
			$this->_dbg = app()->request("dbg") == "1";
		if($this->_dbg)
			echo $s;
	}

	/**
	 * recalculates costs for given params.
	 * @param int $aId article id
	 * @param int $wId warehouse id
	 * @param int $mId modifier id
	 * @param float $qp
	 */
	public function resetCosts($aId, $wId, $mId, $qp = 0)
	{
		$this->dbg("<b>resetCosts($aId, $wId, $mId, $qp)</b><br/>");
		$inSql = "select id, quantity, cost, iqp, typeId, batchId
			from whmv
			where articleId = $aId
			and whDstId = $wId
			and modifierId = $mId
			and iqp >= $qp
			order by iqp";
		$outSql = "select id, quantity, cost, oqp, typeId, batchId
			from whmv
			where articleId = $aId
			and whSrcId = $wId
			and modifierId = $mId
			and oqp >= $qp
			order by oqp";

		$in = $this->qryAsArray($inSql, DB_FETCHMODE_OBJECT);
		$out = $this->qryAsArray($outSql, DB_FETCHMODE_OBJECT);

		foreach ($out as $outr)
		{
			//$this->dbg("<small><small><pre>" . print_r($in, 1) . "</pre></small></small><br/>");
			$this->dbg("<br/><br/>MVOUT #{$outr->id}: t: \"{$outr->typeId}\" " . (0 + $outr->quantity) . " * " . (0 + $outr->cost) . " //oqp=" . (0 + $outr->oqp) . "<br/>");
			$oqp = $outr->oqp;
			$qty = $outr->quantity;
			$sum = 0;
			$this->dbg("<small>");
			foreach ($in as $inr)
			{
				$this->dbg("&nbsp;&nbsp;&nbsp;found MVIN #{$inr->id} //iqp=" . (0 + $inr->iqp) . "<br/>");
				$inrfn = $inr->iqp + $inr->quantity;
				$this->dbg("* {$inrfn} >= " . (0 + $oqp) . "?<br/>");
				if($inrfn >= $oqp)	// && $inr->iqp <= $oqp + $qty
				{
					$this->dbg("&nbsp;&nbsp;&nbsp;FIT<br/>");
					$this->dbg("&nbsp;&nbsp;&nbsp;inr: " . (0 + $inr->quantity) . " * " . (0 + $inr->cost) . "<br/>");
					$qtyUsable = $inr->quantity; // - ($outr->oqp - $inr->iqp);
					$qtyDeducted = ($qty < $qtyUsable ? $qty : $qtyUsable);
					$this->dbg("&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;qty usable: " . (0 + $qtyUsable) . "<br/>"); //" . (0 + $inr->quantity) . " - (" . ( + $outr->oqp) . " - " . (0 + $inr->iqp) . 
					$this->dbg("&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;qty deducted: " . (0 + $qtyDeducted) . "<br/>");
					$sum += $qtyDeducted * $inr->cost;
					$this->dbg("&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;sum: $sum<br/>");
					$qty -= $qtyDeducted;
					$inr->quantity -= $qtyDeducted;
					$this->dbg("&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;qty leftover: $qty<br/>");
					if($qty == 0)
						break;
				}
				else
				{
					$this->dbg("NO FIT");
				}
			}
			$cost = $outr->quantity == 0 ? 0 : $sum / $outr->quantity;
			$this->dbg("total: $sum<br/>new cost: $cost<br/>");
			if(round(0 + $outr->cost, 6) != round(0 + $cost, 6))
			{
				$updSql = "update whmv set cost = $cost where id = {$outr->id}";
				$this->dbg("[!]$updSql<br/>");
				app()->query($updSql);

				/*if($outr->typeId == WHMVTYPE_PRODUCTION) //update produced article cost
				{
					$this->dbg("production");
					$incomeCost = $this->getProducedCost($outr->batchId);
					$rprodIn = app()->dbo("whmv");
					$rprodIn->batchId = $outr->batchId;
					if($rprodIn->find())
						while($rprodIn->fetch())
							if($rprodIn->cost != $incomeCost)
							{
								$this->dbg("update cost for {$rprodIn->id} to $incomeCost");
								$rprodIn->cost = $incomeCost;
								$rprodIn->update();
							}
				}*/
			}
			$this->dbg("</small>");
		}
		$this->dbg("<hr/>");
	}

	public function calculateProducedCost($batchId)
	{
		$this->dbg("calculateProducedCost($batchId) START<br/><small>");
		$cost = $this->getProducedCost($batchId);
		$r = app()->dbo("whmv");
		$r->batchId = $batchId;
		$r->whSrcId = DEFAULT_WAREHOUSE;
		if($r->find())
			while($r->fetch())
			{
				$this->dbg("found row #{$r->id}. old cost = {$r->cost}; new cost: $cost<br/>");
				if(round(0 + $r->cost, 6) != round(0 + $cost, 6))
				{
					//TODO optimize? DB_Dataobject -> sql + resetQPForWhmv($r) ?
					$sql = "update whmv set cost = $cost where id = {$r->id}";
					$this->dbg("$sql<br/>");
					app()->query($sql);
					$r->cost = $cost;
					$r->update();
				}
			}
		$this->dbg("</small>calculateProducedCost($batchId) END<br/>");
	}

	public function getProducedCost($batchId)
	{
		//get total outcome cost
		$this->dbg("getProducedCost($batchId)<br/>");
		$sql = "select sum(cost * quantity) from whmv where batchId = $batchId and whDstId = " . DEFAULT_WAREHOUSE;
		$this->dbg($sql . "<br/>");
		$totalOutCost = 0 + app()->valFromDB($sql);
		$this->dbg("total out cost: " . $totalOutCost . "<br/>");

		//get total income amount
		$sql = "select sum(quantity) from whmv where batchId = $batchId and whSrcId = " . DEFAULT_WAREHOUSE;
		$this->dbg($sql . "<br/>");
		$totalInAmount = 0 + app()->valFromDB($sql);
		$this->dbg("total in amount: " . $totalInAmount . "<br/>");

		$incomeCost = $totalInAmount == 0 ? 0 : $totalOutCost / $totalInAmount;
		$this->dbg("new cost: $incomeCost<hr/>");
		return $incomeCost;
	}

	/**
	 * resets certain q part for given params
	 * @param int $aId article id
	 * @param int $wId warehouse id
	 * @param int $mId modifier id
	 * @param string $qpx qp field, either "iqp" or "oqp"
	 * @param string whx warehouse source or destination field, either "whDstId" or "whSrcId"
	 */
	private function resetQ($aId, $wId, $mId, $qpx, $whx)
	{
		$this->dbg("<b>resetQ($aId, $wId, $mId, $qpx, $whx)</b><br/>");
		$sql = "select id, quantity, $qpx
			from whmv
			where articleId = $aId and $whx = $wId and modifierId = $mId
			order by dt, batchId, id";
		$this->dbg($sql . "<br/>");
		$q = app()->query($sql);
		$qt = 0;
		while($q->fetchInto($o, DB_FETCHMODE_ASSOC))
		{
			if($qt != $o[$qpx])
			{
				$sqlu = "update whmv set $qpx = $qt where id = " . $o["id"];
				$this->dbg("$sqlu<br/>");
				app()->query($sqlu);
			}
			$qt += $o["quantity"];
		}
	}

	/**
	 * Recalculate iqp and oqp in whmv table for given warehouse and article id
	 * @param int $whId warehouse id
	 * @param int $articleId article id
	 * @param int $modifierId article additional data id (such as party or serial nr)
	 */
	public function resetQP($whId, $articleId, $modifierId)
	{
		$this->dbg("resetQP($whId, $articleId, $modifierId)<br/>");
		$aId = (int)$articleId;
		$wId = (int)$whId;
		$mId = (int)$modifierId;

		$this->resetQ($aId, $wId, $mId, "iqp", "whDstId");
		$this->resetQ($aId, $wId, $mId, "oqp", "whSrcId");

		$this->resetCosts($aId, $wId, $mId);	//TODO starter value for optimisation
	}

	/**
	 * Recalculate iqp and oqp in whmv table for given warehouse movement document
	 * @param DBO_Whmv $whmv
	 */
	public function resetQPForWhmv($whmv)
	{
		if($whmv->whSrcId != DEFAULT_WAREHOUSE)
			$this->resetQP($whmv->whSrcId, $whmv->articleId, $whmv->modifierId);
		if($whmv->whDstId != DEFAULT_WAREHOUSE && $whmv->whSrcId != $whmv->whDstId)
			$this->resetQP($whmv->whDstId, $whmv->articleId, $whmv->modifierId);
		if($whmv->whSrcId != DEFAULT_WAREHOUSE)
			$whmv->setValue("cost", app()->valFromDB("select cost from whmv where id = " . ((int)$whmv->id)));
	}



	private $cachedWarehouseLinkedCaptionList;

	public function getWarehouseLinkedCaption($id)
	{
		if(!isset($this->cachedWarehouseLinkedCaptionList))
			$this->cachedWarehouseLinkedCaptionList = array();
		if(!isset($this->cachedWarehouseLinkedCaptionList[$id]))
			$this->cachedWarehouseLinkedCaptionList[$id] = app()->getLinkedCaption(app()->get("warehouse", $id));
		return $this->cachedWarehouseLinkedCaptionList[$id];
	}
}