<?php
/**
 * MainMenu
 * @author Ilja Tihhanovski <ilja.tihhanovski@gmail.com>
 * @copyright (c) 2015 Intellisoft OÜ
 *
 */

	class OldMainMenu extends HtmlComponent
	{
		public function toHtml()
		{
			$ret = "";

			$widgets = array();
			foreach (app()->registries() as $widget)
				if($widget->typeId == ROBJECT_TYPE_SIMPLEWIDGET)
					$widgets[] = $widget;
			if(count($widgets))
			{
				$ret .= "<div class=\"mainMenuWidgets\">";

				foreach ($widgets as $widget)
					if(file_exists($fn = app()->getAbsoluteFile("registries/" . $widget->name . "/" . $widget->name . ".wt.php")))
						include $fn;

				$ret .= "</div><div class=\"clearBoth\"></div>";
			}


		 	$p = app()->dbo("menupart");
		 	$p->orderBy("id");
		 	$parts = array();
		 	if($p->find())
		 		while($p->fetch())
		 			$parts[] = clone $p;

		 	$ret .= "<table border=\"0\" cellspacing=\"20\">";
			$ret .= "<tr><td></td>";
		 	foreach ( $parts as $p )
				$ret .= "<td>" . t($p->name) . "</td>";
			$ret .= "</tr>";

			$m = app()->dbo("rmodule");
			$m->orderBy("pos");
			if($m->find())
				while($m->fetch())
				{
					$tcnt = 0;
					$mh = "<tr><td valign=\"top\" align=\"right\">" . t($m->name) . "</td>";
					foreach ( $parts as $p )
					{
						$l = array();
						foreach (app()->registries() as $val)
							if(($val->module == $m->getIdValue()) && ($val->menupartId == $p->getIdValue()) && ($val->typeId < 10))	//TODO
								$l[] = $val;

						$mh .= "<td valign=\"top\">";

						$cnt = 0;
						foreach ($l as $val)
						{
							$mh .= mi($val);
							$cnt++;
							$tcnt++;
						}
						$mh .= "</td>";
					}
					$mh .= "</tr>";

					if($tcnt) $ret .= $mh;
				}
		 	$ret .= "</table>";
		 	return $ret;
		}
	}