<?php
/**
 * Warehouse data module registry descriptor
 * @author Ilja Tihhanovski <ilja.tihhanovski@gmail.com>
 * @copyright (c) 2015 Intellisoft OÜ
 *
 */


/**
 * Warehouse document Registry descriptor
*/
class WHMVRegistryDescriptor extends RegistryDescriptor
{
	/**
	 * {@inheritdoc}
	 */
	public function getChildrenTree()
	{
		return array(
			"whmvbatch" => array(
				"rows" => "whmv",
				)
			);
	}

	/**
	 * Returns array of components to build detail form and printform.
	 * @return array
	 * @param DB_Dataobject $obj
	 */
	protected function getSimpleformComponents($obj)
	{
	}

	protected function getRightPanelItems($context)
	{
		return array(
					new PrintButtonAsRequested($context),
					new LockButton($context),
				);
	}

	protected function getRightPanel($context)
	{
		return rightPanel($context,
				$this->getRightPanelItems($context),
				false, false
			);
	}

	protected function ui_number($obj)
	{
		return new StaticValue($obj, "fullNr", "Number");
	}

	protected function ui_dt($obj)
	{
		if(!$obj->isEditable())
			return new StaticValue($obj, "dt", "Date");
		return new DatePicker($obj, "dt", "Date");
	}

	protected function getWarehouseComboSqlByType($typeId)
	{
		//if($typeId == WHMVTYPE_INCOME || $typeId == WHMVTYPE_OUTCOME || $typeId == WHMVTYPE_PRODUCTION)
			return SQL_COMBO_WAREHOUSE;
		//else
		//	return SQL_COMBO_WAREHOUSE_NOVIRTUAL;
	}

	protected function ui_typeId($obj)
	{
		return selectSqlTranslatedNotNullable($obj, "typeId", "Type", SQL_COMBO_WHMVTYPE);
	}

	protected function ui_whSrcId($obj)
	{
		return selectSqlNotNullable($obj, "whSrcId", "Source warehouse", $this->getWarehouseComboSqlByType($obj->typeId));
	}

	protected function ui_whDstId($obj)
	{
		return selectSqlNotNullable($obj, "whDstId", "Destination warehouse", $this->getWarehouseComboSqlByType($obj->typeId));
	}

	protected function ui_companySrcId($obj)
	{
		return keySel($obj, "companySrcId", "Source company");
	}

	protected function ui_companyDstId($obj)
	{
		return keySel($obj, "companyDstId", "Destination company");
	}

	protected function ui_totalCost($obj)
	{
		return staticValue($obj, "totalCost", "Total cost");
	}

	protected function ui_totalPrice($obj)
	{
		return staticValue($obj, "totalPrice", "Total price");
	}

	protected function ui_memo($obj)
	{
		return textarea($obj, "memo");
	}

	protected function isObjEditable($obj = null)
	{
		return is_null($obj) ? $this->getContext()->obj->isEditable() : $obj->isEditable();
	}

	protected function ui_rowsGrid($cols)
	{
		return new DetailGrid
				(
					"rows",
					$cols,
					array(
						"caption" => "",
						"leftCaption" => false,
						"rowsChangeable" => $this->isObjEditable(),
						"rowsAppendable" => $this->isObjEditable(),
					)
				);
	}

	protected function ui_rows_articleId()
	{
		app()->warehouse();
		$w = 20;

		if(!$this->isObjEditable())
			return new DetailGridColumn("articleLink", "component", "static", $w);

		if(defined("WAREHOUSE_ARTICLE_SELECTOR") && WAREHOUSE_ARTICLE_SELECTOR == "select")
			return new DetailGridColumn("articleId", "component", "select", $w, getSelectOptions(SQL_COMBO_WAREHOUSE_ARTICLE, null, false));

		return new KeySelColumn("articleId", "component", $w, "whmv");	//default keysel
	}

	protected function ui_rows_modifier()
	{
		return new DetailGridColumn("modifier", "Modifier", "textbox", 8);
	}

	protected function ui_rows_unitName()
	{
		return new DetailGridColumn("unitName", "Unit", "static", 2);
	}

	protected function ui_rows_whSrcId()
	{
		return new DetailGridColumn("whSrcId", "Source warehouse", "select", 5, getSelectOptions(SQL_COMBO_WAREHOUSE_NOVIRTUAL, null, false));
	}

	protected function ui_rows_whDstId()
	{
		$w = 5;
		return $this->isObjEditable() ?
			new DetailGridColumn("whDstId", "Destination warehouse", "select", 5, getSelectOptions(SQL_COMBO_WAREHOUSE_NOVIRTUAL, null, false)) :
			new DetailGridColumn("whDstLink", "Destination warehouse", "static", $w);
	}

	protected function ui_rows_quantity()
	{
		$t = $this->isObjEditable() ? "double" : "static";
		return new DetailGridColumn("quantity", "Quantity", $t, 4, null, "gridCellRight");
	}

	protected function ui_rows_cost()
	{
		$t = $this->isObjEditable() ? "double" : "static";
		return new DetailGridColumn("cost", "Cost", $t, 4, null, "gridCellRight");
	}

	protected function ui_rows_cost_locked()
	{
		return new DetailGridColumn("cost", "Cost", "static", 4, null, "gridCellRight");
	}

	protected function ui_rows_price()
	{
		return new DetailGridColumn("price", "Price", "double", 4, null, "gridCellRight");
	}

	protected function ui_rows_memo()
	{
		$t = $this->isObjEditable() ? "textbox" : "static";
		return new DetailGridColumn("memo", "Memo", $t, 8);
	}

	protected function ui_addGridCol_typeId($grid)
	{
		$grid->addColumn(new MGridColumn("Type", "type", "t.name", 100, MGRID_ALIGN_LEFT, FORMAT_TRANSLATED));
	}

	protected function ui_addGridCol_locked($grid)
	{
		$grid->addLockboxColumn("b.locked");
	}

	protected function ui_addGridCol_number($grid)
	{
		$grid->addColumn(new MGridColumn("Number", "fullNr", "b.fullNr", 100));
	}

	protected function ui_addGridCol_dt($grid)
	{
		$grid->addColumn(new MGridColumn("Date", "dt", "b.dt", 65, MGRID_ALIGN_LEFT, FORMAT_DATE));
	}

	protected function ui_addGridCol_whSrcId($grid)
	{
		$grid->addColumn(new MGridColumn("Source warehouse", "whSrc", "sw.name", 120));
	}

	protected function ui_addGridCol_whDstId($grid)
	{
		$grid->addColumn(new MGridColumn("Destination warehouse", "whDst", "dw.name", 120));
	}

	protected function ui_addGridCol_companySrcId($grid)
	{
		$grid->addColumn(new MGridColumn("Source company", "coSrc", "sc.name", 120));
	}

	protected function ui_addGridCol_companyDstId($grid)
	{
		$grid->addColumn(new MGridColumn("Destination company", "coDst", "dc.name", 120));
	}

	protected function ui_addGridCol_totalCost($grid)
	{
		$grid->addColumn(new MGridColumn("Total cost", "totalCost", "b.totalCost", 120, MGRID_ALIGN_RIGHT, FORMAT_FLOAT2));
	}

	protected function ui_addGridCol_totalPrice($grid)
	{
		$grid->addColumn(new MGridColumn("Total price", "totalPrice", "b.totalPrice", 120, MGRID_ALIGN_RIGHT, FORMAT_FLOAT2));
	}

}
