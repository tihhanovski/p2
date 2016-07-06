<?php
/**
 * MainMenu
 * @author Ilja Tihhanovski <ilja.tihhanovski@gmail.com>
 * @copyright (c) 2015 Intellisoft OÜ
 *
 */

	class SquaredMainMenu extends HtmlComponent
	{
		public function toHtml()
		{
		 	$p = app()->dbo("menupart");
		 	$p->orderBy("id");
		 	$parts = array();
		 	if($p->find())
		 		while($p->fetch())
		 			$parts[] = clone $p;

		 	$p = app()->dbo("rmodule");
		 	$p->orderBy("pos");
		 	$modules = array();
		 	if($p->find())
		 		while($p->fetch())
		 			$modules[] = clone $p;


		 	$ret = "";

		 	foreach ($parts as $p)
		 	{
		 		$pc = "";

				foreach ($modules as $mod)
					$pc .= "<div class=\"menuItemBoxContainer part{$p->id}\">
							<div class=\"menuItemBox\"><span class=\"menuItemName\">" . t($mod->name) . "</span></div>
						</div>";

				foreach (app()->registries() as $val)
					//if(($val->module == $m->getIdValue()) && ($val->menupartId == $p->getIdValue()) && ($val->typeId < 10))	//TODO
					if(($val->menupartId == $p->getIdValue()) && ($val->typeId < 10))	//TODO
					{
						$pc .= "<div class=\"menuItemBoxContainer module{$val->module}\"  style=\"display: none;\"><div class=\"menuItemBox\"><span class=\"menuItemName\">" . t($val->getCaption()) . "</span></div></div>";
					}


		 		$ret .= "<div class=\"partContainer\"><div class=\"partHeader\" onclick=\"JavaScript:togglePart({$p->id});\"><span class=\"menuItemName\">" . t($p->name) . "</span></div><div id=\"partItemsContainer{$p->id}\" class=\"partItemsContainer\">" . $pc . "</div></div>";
		 	}

		 	$ret .= "<script src=\"" . app()->ui()->url("js/mainMenu.js") . "\"></script>";


		 	return $ret;
		}
	}