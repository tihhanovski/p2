<?php
/*
 * Created on Nov 5, 2011
 *
 * (c) Ilja Tihhanovski, Intellisoft
 *
 */

	include app()->getAbsoluteFile("ui/pagestart.php");

	$sys = app()->system();
	$cn = $sys->getValue("dynCompanyName");



	?><script type="text/javascript">

		$(function(){
			$(window).resize(function(){
				$("#dh").width($(window).width() - 60);
			});

			$(window).scroll(function(){
				if($(window).scrollTop() > 5)
					$("#dh").css("border-bottom", "1px solid #c9c9c9");
				else
					$("#dh").css("border-bottom", "0px");
			});
		});

	</script><div id="dh" class="mainMenuTopDiv <?=app()->getTopMenuClass()?>">
		<div style="float: left; font-size: 32px; font-weight: bold; color: #505050;"><?=APP_TITLE . ($cn ? " / " . $cn : "")?></div>
		<div style="float: right;">
			<div><a href="JavaScript:logout();"><?=t("Logout")?></a></div>
			<div style="margin-top: 10px;" id="toolbar_Locales"></div><?php

				if($v = app()->getVersion())
				{
					?><div><a href="JavaScript:showNews();"><?=t("ver") . " " . $v?></a></div><?php
				}

		?></div>
	</div>

	<div style="margin: 50px; padding-top: 50px;"><?php

	include app()->getAbsoluteFile("ui/mainmenu.php");

	echo "</div>";

	include app()->getAbsoluteFile("ui/pagefinish.php");

?><script type="text/javascript">

	function showNews()
	{
		var url = "?action=versionLog";
		$.get(url, function(data){
			var html = '<div style="width: 600px; height: 400px; overflow: auto; border: 1px solid #eeeeee;">' + 
				'<div style="padding: 4px;"><b><?=t("Version log")?></b><ul>';
			var x;
			var c = "";
			for(x = 0; x < data.log.length; x++)
			{
				var i = data.log[x];
				html += '<li><b>' + i.v + '</b>: ' + i.m + '</li>';
			}
			html += '</ul></div></div>';

			bubble.dimensions(600, 400).show(html);

		}, "json");
	}

</script>