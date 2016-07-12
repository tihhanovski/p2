<div class="<?=app()->ui()->getTopMenuClass()?>"><div class="appIcon"><img src="<?=app()->url("resources/webicon.png")?>" width="16" height="16" border="0"/></div><div class="topMenuItem" id="toolbar_MainMenu"><a href="<?=app()->url()?>" tabindex="-1"><b><?=app()->getPageTitle()?></b></a></div><div id="toolbar_UserMenu" class="topMenuRightItem" style="margin-right: 20px;"><?=app()->isI18n() ? "<span onclick=\"JavaScript:app.userMenu();\">" . app()->getLocale() . "</span> " : ""?><a href="JavaScript:app.userMenu();" tabindex="-1"><?=app()->user()->uid?></a></div><div id="toolbar_Help" class="topMenuRightItem"><?php

	if($h = app()->helpLink())
		echo "<a href=\"$h\" target=\"_blank\" tabindex=\"-1\"><img src=\"" . app()->url("ui/img/16/help.png") . "\" alt=\"\" border=\"0\"/></a>";

?></div><?=$this->getTopToolbar()?></div><div id="mainMenu" class="mainMenu" style="display: none;"></div>