<?php
/**
 * Article history report print form
 * @author Ilja Tihhanovski <ilja.tihhanovski@gmail.com>
 * @copyright (c) 2015 Intellisoft OÜ
 */


	$obj = $context->obj;
	$obj->articleId = (int)$obj->articleId;
	$model = new ReportModel();

	$model->setupVisibleColumnsAndOrder($this, $obj);

	$df = getFormatter(FORMAT_DATE);

	$d1 = $df->decodeHuman($obj->dt1);
	$d2 = $df->decodeHuman($obj->dt2);
	if($obj->articleId)
	{
		$art = app()->get("article", $obj->articleId);
		$model->addFilter(t("Article"), $art->getCaption());
	}
	if($modFiltered = app()->warehouse()->isArticleModifiersEnabled() && $obj->modId != DEFAULT_WHMV_MODIFIER)
	{
		$mod = app()->get("whmvmodifier", $obj->modId);
		$model->addFilter(t("Modifier"), $mod->getCaption());
	}
	$wh = app()->get("warehouse", $obj->whId);
	$model->addFilter(t("Warehouse"), $wh->getCaption());

	$model->addFilter(t("Period"), $df->encodeHuman($d1) . " - " . $df->encodeHuman($d2));

	$sd1 = quote($d1);
	$sd2 = quote($d2);

	$qmodSql = $obj->whId == DEFAULT_WAREHOUSE ? "if(m.whSrcId = 1, 1, -1)" : "if(m.whDstId = {$obj->whId}, 1, -1)";
	$filterSql = ($modFiltered ? " and m.modifierId = {$obj->modId}" : "") .
		($obj->whId != DEFAULT_WAREHOUSE ? " and (m.whSrcId = {$obj->whId} or m.whDstId = {$obj->whId})" : " and m.typeId <> " . WHMVTYPE_INTRA);


	$sql = "select 0 as pri, 'A' as d, '' as doc, $sd1 as dt, 'Perioodi algseis' as ep,
		coalesce(sum(quantity * qmod), 0) as qty,
		coalesce(if(sum(quantity * qmod) = 0, 0, sum(quantity * qmod * cost) / sum(quantity * qmod)), 0) as cost, 
		coalesce(sum(quantity * qmod * cost), 0) as tcost,
		NULL as price, NULL as tprice, 0 as oqp, 0 as iqp
		from (
			select m.quantity, $qmodSql as qmod, m.cost from
			whmv m
			where m.articleId = {$obj->articleId} and m.dt < $sd1
			$filterSql
		) x

	union all

	select 1 as pri, if(qmod = 1, 'S', 'V') as d, doc, dt, ep,
		qty * qmod as qty, cost, qty * qmod * cost as tcost,
		price, -1 * qty * qmod * price as tprice, oqp, iqp from (
			select b.fullNr as doc, m.dt,
			concat(
				if(m.typeId = 1, 'Algseis', ''),
				if(m.typeId = 2, sc.name, ''),
				if(m.typeId = 3, dc.name, ''),
				if(m.typeId = 4, if(m.whSrcId = {$obj->whId}, dw.name, sw.name), ''),
				if(m.typeId = 5, '" . t("ru_whwriteoff") . "', ''),
				if(m.typeId = 6, '" . t("ru_whproduction") . "', ''),
				if(m.typeId = 7, '" . t("ru_whinventory") . "', '')
			) as ep,
			$qmodSql as qmod,
			m.quantity as qty,
			m.cost,
			m.price,
			m.oqp, m.iqp
			from whmv m
			left join whmvbatch b on b.id = m.batchId
			left join company sc on sc.id = m.companySrcId
			left join company dc on dc.id = m.companyDstId
			left join warehouse sw on sw.id = m.whSrcId
			left join warehouse dw on dw.id = m.whDstId
			where m.articleId = {$obj->articleId}" .
			($obj->dt1 ? " and m.dt >= $sd1" : "") .
			($obj->dt2 ? " and m.dt <= $sd2" : "") .
			"$filterSql
		) x
		order by pri, dt, oqp, iqp, doc";

	$model->fillBySql($sql);
	$model->output();
