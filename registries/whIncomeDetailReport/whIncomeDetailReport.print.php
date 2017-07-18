<?php
/**
 * Warehouse income detail report print form
 * @author Ilja Tihhanovski <ilja.tihhanovski@gmail.com>
 * @copyright (c) 2017 Ilja Tihhanovski
 */

	//format input variables
	$obj = $context->obj;
	$obj->articleId = isset($obj->articleId) ? (int)$obj->articleId : 0;
	$obj->whId = isset($obj->whId) ? (int)$obj->whId : DEFAULT_WAREHOUSE;
	$obj->modId = isset($obj->modId) ? (int)$obj->modId : DEFAULT_WHMV_MODIFIER;
	$obj->companySrcId = isset($obj->companySrcId) ? (int)$obj->companySrcId : 0;
	$df = getFormatter(FORMAT_DATE);
	$d1 = $df->decodeHuman($obj->dt1);
	$d2 = $df->decodeHuman($obj->dt2);
	$sd1 = quote($d1);
	$sd2 = quote($d2);

	//create report model
	$model = new ReportModel();
	$model->setupVisibleColumnsAndOrder($this, $obj);

	//output filters
	$model->addFilter(t("Period"), $df->encodeHuman($d1) . " - " . $df->encodeHuman($d2));

	$wh = app()->get("warehouse", $obj->whId);
	$model->addFilter(t("Warehouse"), $wh->getCaption());

	if($obj->articleId)
	{
		$art = app()->get("article", $obj->articleId);
		$model->addFilter(t("Article"), $art->getCaption());
	}

	if($obj->companySrcId)
	{
		$sup = app()->get("company", $obj->companySrcId);
		$model->addFilter(t("Supplier"), $sup->getCaption());
	}

	if($modFiltered = app()->warehouse()->isArticleModifiersEnabled() && $obj->modId != DEFAULT_WHMV_MODIFIER)
	{
		$mod = app()->get("whmvmodifier", $obj->modId);
		$model->addFilter(t("Modifier"), $mod->getCaption());
	}

	//create SQL
	$filters = array();
	if($obj->dt1)
		$filters[] = "m.dt >= $sd1";
	if($obj->dt2)
		$filters[] = "m.dt <= $sd2";
	if($obj->whId == DEFAULT_WAREHOUSE)
	{
		$filters[] = "m.whSrcId = " . $obj->whId;
		$filters[] = "m.whDstId > " . $obj->whId;
	}
	else
		$filters[] = "m.whDstId = " . $obj->whId;
	if($obj->articleId)
		$filters[] = "m.articleId = " . $obj->articleId;
	if($obj->companySrcId)
		$filters[] = "m.companySrcId = " . $obj->companySrcId;
	if($modFiltered)
		$filters[] = "m.modifierId = " . $obj->modId;
	$filterSql = trim(implode(" and ", $filters));
	if($filterSql != "")
		$filterSql = "where " . $filterSql;


	//$qmodSql = $obj->whId == DEFAULT_WAREHOUSE ? "if(m.whSrcId = 1, 1, -1)" : "if(m.whDstId = {$obj->whId}, 1, -1)";
	//$filterSql = ($modFiltered ? " and m.modifierId = {$obj->modId}" : "") .
	//	($obj->whId != DEFAULT_WAREHOUSE ? " and (m.whSrcId = {$obj->whId} or m.whDstId = {$obj->whId})" : " and m.typeId <> " . WHMVTYPE_INTRA);


	$sql = "select 
			b.fullNr as doc, 
			m.dt, 
			a.code, 
			a.name, 
			m.quantity as qty, 
			m.cost, 
			m.quantity * m.cost as tcost,
			concat(
				if(m.typeId = 1, '" . t("Initial state") . "', ''),
				if(m.typeId = 2, sc.name, ''),
				if(m.typeId = 4, sw.name, ''),
				if(m.typeId = 6, '" . t("ru_whproduction") . "', ''),
				if(m.typeId = 7, '" . t("ru_whinventory") . "', '')
			) as ep

			from whmv m
			left join article a on a.id = m.articleId
			left join whmvbatch b on b.id = m.batchId
			left join company sc on sc.id = m.companySrcId
			left join warehouse sw on sw.id = m.whSrcId
			$filterSql
			order by m.dt, b.nrprefix, b.nr";

	//die("<pre>$sql");

	$model->fillBySql($sql);
	$model->output();
