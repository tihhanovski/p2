<?php
/**
 * Application front page
 * @author Ilja Tihhanovski <ilja.tihhanovski@gmail.com>
 * @copyright (c) 2016 Ilja Tihhanovski
 *
 */

	$ui = app()->ui();
	$sys = app()->system();
	$cn = $sys->getValue("dynCompanyName");

	include $ui->getFilePath("html/pagestart.php");

?><script type="text/javascript">

		$(function(){
			$(window).resize(function(){

				var w = $(window).width();
				var h = $(window).height();
				var dh = 200;
				var dw = 80;

				$("#dh").width(w - dw);

				$(".frontpageMainMenu").height(h - dh);
				$(".frontpageMainMenuContents").height(h - dh);
			});

			$(window).resize();
		});

</script>
<link rel="stylesheet" type="text/css" href="<?=$ui->url("styles.css")?>" />
<div id="dh" class="frontpageTopMenu">
	<div style="float: left; font-size: 32px; font-weight: bold; color: #505050;">
		<img src="<?=app()->url("resources/p2logo.png")?>" border="0" height="24"/>
		<?=APP_TITLE . ($cn ? " / " . $cn : "")?> sq
	</div>
	<div style="float: right;">
		<div>
			<!--a href="JavaScript:logout();"><?=t("Logout")?></a-->
			<i class="userMenuItem fa fa-share-alt" aria-hidden="true"></i>
			<i class="userMenuItem fa fa-user" aria-hidden="true"></i>
			<i class="userMenuItem fa fa-cog" aria-hidden="true"></i>
		</div>
	</div>
</div>
<div class="frontpageBody">
	<div class="frontpageMainMenu">
		<div class="frontpageMainMenuContents"><?=$ui->getMainMenu()->toHtml()?></div>
	</div>
	<div class="frontpageDashboard">
		<div class="frontpageDashboardContents">
			dashboard contents
		</div>
	</div>
</div>
<?php

	include $ui->getFilePath("html/pagefinish.php");
