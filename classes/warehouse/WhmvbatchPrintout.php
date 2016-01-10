<?php

class WhmvbatchPrintout
{
	public function run($obj)
	{
		app()->warehouse();
		app()->initReporting();

		$model = new ReportModel($this->getCaption($obj));

		foreach ($this->getTopFilters($obj) as $c => $f)
			$model->addFilter(t($c), $f);
		foreach ($this->getBottomFilters($obj) as $c => $f)
			$model->addBottomFilter(t($c), $f);

		foreach ($this->getColumns($obj) as $c)
			$model->addColumn($c);

		foreach ($obj->rows as $row)
			$model->addRow($this->getRow($row));

		$model->output();
	}

	public function getTopFilters($obj)
	{
	}

	public function getBottomFilters($obj)
	{
	}

	public function getColumns($obj)
	{
	}

	public function getCaption($obj)
	{
		return "";
	}

	public function getRow($row)
	{
		$a = $row->getLink("articleId");

		return array(
				"artCode" => $a->code,
				"artName" => $a->name,
				"unitName" => $row->unitName,
				"qty" => $row->quantity,
				"cost" => $row->cost,
				"price" => $row->price,
				"memo" => $row->memo,
				"whDstName" => $row->getLink("whDstId")->name,
				"whSrcName" => $row->getLink("whSrcId")->name,
			);

	}
}