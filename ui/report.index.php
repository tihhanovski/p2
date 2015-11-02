<?php
/*
 * Created on Nov 10, 2011
 *
 * (c) Ilja Tihhanovski, Intellisoft
 *
 */

	include app()->getAbsoluteFile("ui/pagestart.php");

?><body><?php

	include "topmenu.php";

?><div class="editorContent"><div class="reportInputContents"><?php

	if($r = app()->getRegistryDescriptor()->getReportInputPath())
		include $r;
	else
		echo t("no input form");	//TODO replace with "detailNotFound.php"?

?></div></div><script language="JavaScript">

	$(function()
	{
		$(".multiSelect").multiselect();
		$(".mselect").css("width", "405px");
		$(".checkbox").css("margin-left", "5px");
		$(".acsItem").hover(function(){$(this).addClass("ui-state-hover")}, function(){$(this).removeClass("ui-state-hover")})
		var fcnt = 0;
		$(".checkbox").each(function(){
			fcnt += this.checked ? 1 : 0;
		});
	});

</script><?php

	include app()->getAbsoluteFile("ui/edit.finish.php");
	include app()->getAbsoluteFile("ui/pagefinish.php");