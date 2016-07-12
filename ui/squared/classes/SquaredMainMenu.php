<?php
/**
 * MainMenu
 * @author Ilja Tihhanovski <ilja.tihhanovski@gmail.com>
 * @copyright (c) 2015 Intellisoft OÜ
 *
 */

	class SquaredMainMenuItem extends HtmlComponent
	{
		public $icon, $href, $caption;
		public $items = array();

		public function __construct($icon, $href, $caption)
		{
			$this->icon = $icon;
			$this->href = $href;
			$this->caption = $caption;
		}

		public function addItem($m)
		{
			$this->items[] = $m;
		}

		public function toHtml()
		{
			$hasChildren = count($this->items) > 0;
			$ae = $hasChildren ? "aria-expanded=\"false\"" : "";
			$ret = "<a href=\"{$this->href}\" $ae >" .
				"<span class=\"sidebar-nav-item-icon fa {$this->icon}\" aria-hidden=\"true\"></span>" .
				$this->caption . "</a>";
			if($hasChildren)
			{
				$ret .= "<ul $ae>";
				foreach ($this->items as $i)
					$ret .= $i->toHtml();
				$ret .= "</ul>";
			}

		 	return "<li>" . $ret . "</li>";
		}
	}


	class SquaredMainMenu extends SquaredMainMenuItem
	{
		public function __construct()
		{
			$this->fillAppRegistries();
		}

		public function fillAppRegistries()
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

		 	foreach ($modules as $m)
		 	{
		 		$mnuModule = new SquaredMainMenuItem("fa-cube", "#", t($m->name));
		 		$sub = "";

		 		foreach ($parts as $icon => $mp)
		 			foreach ($registries as $reg)
		 				if($reg->module == $m->getIdValue() && $reg->menupartId == $mp->getIdValue() && $reg->typeId < 10)
		 					$mnuModule->addItem(new SquaredMainMenuItem($icon, app()->url("?registry=" . $reg->name), t($reg->getCaption())));

		 		$this->addItem($mnuModule);

		 	}
		}

		public function toHtml()
		{
		 	$ret = "";
		 	foreach ($this->items as $i)
		 		$ret .= $i->toHtml();
		 	return "<aside class=\"sidebar\"><nav class=\"sidebar-nav\"><ul class=\"metismenu\" id=\"menu\">$ret</ul></nav></aside>";
		}

		public function toHtml_v1()
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

		 	$ret .= "</ul></nav></aside>";


		 	return $ret;
		}
	}