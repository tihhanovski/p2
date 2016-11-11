<?php
/**
 * Grid filter UI part
 * @author Ilja Tihhanovski <ilja.tihhanovski@gmail.com>
 * @copyright (c) 2011 - 2015 Intellisoft OÜ
 */

?><div class="filterHeader"><?=t("filter")?></div><div id="filterContents" class="filterContents"><?php

	//remove "active" checkbox as it is not needed anymore
	$fs = array();
	foreach ($fields as $f)
		if(!(property_exists($f, "field")) || $f->field != "active")
			$fs[] = $f;
	echo simpleform($fs);

	//buttons list: JS function => caption
	$buttons = array(
		"applyFilter" => "Apply",
		"cancelFilter" => "Cancel",
		"emptyFilter" => "Clear filter",
		);

?></div><div class="filterClose"><?php

	foreach ($buttons as $k => $v)
	{
		?><button class="filterButton" onclick="JavaScript:grid.<?=$k?>();"><?=t($v)?></button><?php
	}

?></div><script type="text/javascript"> app.setupFieldFormats(); $(".filterButton").button(); </script>