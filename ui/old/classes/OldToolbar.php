<?php
/**
 * MainMenu
 * @author Ilja Tihhanovski <ilja.tihhanovski@gmail.com>
 * @copyright (c) 2015 Intellisoft OÜ
 *
 */

	class OldToolbar extends HtmlComponent
	{
		public function toHtml()
		{
			$ret = '<div id="dh" class="frontpageTopMenu">
				<div class="frontpageCaption">
				<img src="<?=app()->url("resources/p2logo.png")?>" border="0" height="24"/>
				<?=APP_TITLE . ($cn ? " / " . $cn : "")?>
				</div>' . app()->ui()->getUserMenu()->toHtml() . '</div>';
		}
	}