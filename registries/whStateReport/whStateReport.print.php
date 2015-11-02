<?php
/**
 * Article Warehouse state report print form
 * @author Ilja Tihhanovski <ilja.tihhanovski@gmail.com>
 * @copyright (c) 17.03.2015 Ilja Tihhanovski
 */

	$obj = $context->obj;
	$obj->whId = isset($obj->whId) && $obj->whId ? (int)$obj->whId : DEFAULT_WAREHOUSE;
	$obj->showNulls = isset($obj->showNulls) ? (int)$obj->showNulls : 0;
	$obj->showMods = app()->warehouse()->isArticleModifiersEnabled() && isset($obj->showMods) ? 
		(int)$obj->showMods :
		0;
	$obj->showClosed = isset($obj->showClosed) ? (int)$obj->showClosed : 0;


	$model = new ReportModel();

	$model->setupVisibleColumnsAndOrder($this, $obj);

	$df = getFormatter(FORMAT_DATE);

	$d = $df->decodeHuman($obj->dt);
	$wh = app()->get("warehouse", $obj->whId);
	$model->addFilter(t("Warehouse"), $wh->getCaption());

	$model->addFilter(t("Date"), $df->encodeHuman($d));

	$sd = quote($d);

	$qmodSql = $obj->whId == DEFAULT_WAREHOUSE ?
		"if(whSrcId = 1, 1, -1)" :
		"if(whDstId = {$obj->whId}, 1, -1)";
	$innerFilter =
		($obj->whId != DEFAULT_WAREHOUSE ? " and (whSrcId = {$obj->whId} or whDstId = {$obj->whId})" : "");

	$outerFilter = "";
	if(!$obj->showNulls)
		$outerFilter = $this->addWhere($outerFilter, "m.qty <> 0");
	if(!$obj->showClosed)
		$outerFilter = $this->addWhere($outerFilter, "a.closed = 0");


	$sql = "select a.id, a.code, a.name, " . ($obj->showMods ? "x.name as md, " : "") . "u.name as unit,
		m.qty, if(m.qty = 0, 0, m.tcost / m.qty) as cost, m.tcost
		from article a
		left join (
			select articleId, " . ($obj->showMods ? "modifierId, " : "") . "sum(qty * qmod) as qty, sum(qty * cost * qmod) as tcost
			from (
				select articleId, " . ($obj->showMods ? "modifierId, " : "") . "quantity as qty, cost, if(whSrcId = 1, 1, -1) as qmod
				from whmv
				where dt <= $sd $innerFilter
			) m group by articleId" . ($obj->showMods ? ", modifierId" : "") . "
		) m on m.articleId = a.id
		left join unit u on u.id = a.unitId
		" . ($obj->showMods ? "left join whmvmodifier x on x.id = m.modifierId" : "") . "
		$outerFilter
		order by a.code";

	$model->fillBySql($sql);
	$model->output();
