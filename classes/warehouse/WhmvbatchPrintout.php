<?php
/**
 * General WH movement printform helper
 * @author Ilja Tihhanovski <ilja.tihhanovski@gmail.com>
 * @copyright (c) 2015 Intellisoft OÜ
 *
 */

/**
 * General WH movement printform helper
 */
class WhmvbatchPrintout extends SimpleDocumentReport
{
	public function run($obj)
	{
		app()->warehouse();
		parent::run($obj);
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