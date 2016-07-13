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
				var dh = $(".frontpageTopMenu").outerHeight();
				var dw = 30;

				$("#dh").width(w - dw);

				$(".frontpageMainMenu").height(h - dh);
				$(".frontpageDashboard").height(h - dh - 40).width(w - 340);
			});

			$(window).resize();
		});

</script>
<div id="dh" class="frontpageTopMenu">
	<div class="frontpageCaption">
		<img src="<?=app()->url("resources/p2logo.png")?>" border="0" height="16"/>
		<?=APP_TITLE?>
	</div><?=$ui->getUserMenu()->toHtml()?>
</div>
<div class="frontpageBody">
	<div class="frontpageMainMenu"><?=$ui->getMainMenu()->toHtml()?></div>
	<div class="frontpageDashboard">
		<div class="frontpageDashboardContents">
			<div><?=$cn?></div>
			<div class="frontpageDasboardItems">
				<div class="dashboardWidget dww1 dwh1">widget 1</div>
				<div class="dashboardWidget dww2 dwh2">widget 2</div>
				<div class="dashboardWidget dww2 dwh1">widget 3</div>
				<div class="dashboardWidget dww1 dwh1">widget 4</div>
				<div class="dashboardWidget">widget 5</div>
				<div class="dashboardWidget">widget 6</div>
				<div class="dashboardWidget">widget 7</div>
				<div class="dashboardWidget">widget 8</div>
				<div class="dashboardWidget">widget 8</div>
			</div>
		</div>
	</div>
</div>
<?php

	include $ui->getFilePath("html/pagefinish.php");
