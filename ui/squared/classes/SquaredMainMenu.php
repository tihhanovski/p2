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
		 		{
		 			switch ($p->id)
		 			{
		 				case 1:
		 					$icon = "fa-file";
		 					break;
		 				case 2:
		 					$icon = "fa-print";
		 					break;
		 				case 3:
		 					$icon = "fa-list-alt";
		 					break;
		 				case 4:
		 					$icon = "fa-gear";
		 					break;
		 				default:
		 					$icon = "fa-cube";
		 					break;
		 			}
		 			$parts[$icon] = clone $p;
		 		}

		 	$p = app()->dbo("rmodule");
		 	$p->orderBy("pos");
		 	$modules = array();
		 	if($p->find())
		 		while($p->fetch())
		 			$modules[] = clone $p;

		 	$registries = app()->registries();

		 	$ret = "<aside class=\"sidebar\"><nav class=\"sidebar-nav\"><ul class=\"metismenu\" id=\"menu\">";

		 	foreach ($modules as $m)
		 	{
		 		$sub = "";

		 		foreach ($parts as $icon => $mp)
		 			foreach ($registries as $reg)
		 				if($reg->module == $m->getIdValue() && $reg->menupartId == $mp->getIdValue() && $reg->typeId < 10)
					 		$sub .= "<li><a href=\"" . app()->url("?registry=" . $reg->name) . "\"><span class=\"sidebar-nav-item-icon fa $icon\" aria-hidden=\"true\"></span>" .
					 			t($reg->getCaption()) .
					 			"</a></li>\n\n";


		 		$ret .= "<li>" .
		 			"<a href=\"#\" aria-expanded=\"false\">" .
		 			"<span class=\"sidebar-nav-item-icon fa fa-cube\" aria-hidden=\"true\"></span>" . t($m->name) . "</a>" .	//fa-lg
		 			"<ul aria-expanded=\"false\">" .
		 			$sub .
		 			"</ul>" .
		 			"</li>";
		 	}

		 	$ret .= "</ul></nav></aside>" .
		 		"<script src=\"" . app()->ui()->url("js/mainMenu.js") . "\"></script>";


		 	return $ret;
		}
	}