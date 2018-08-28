<?php
/*
 * Created on Sep 29, 2011
 *
 * (c) Ilja Tihhanovski, Intellisoft
 *
 */

	if (!defined('SHOW_HIDDEN_MODULES')) {
		define('SHOW_HIDDEN_MODULES', true);
	}

	echo textbox($obj, "name"),
		textarea($obj, "memo");

	?><div class="formRow">
		<div class="formLabel"><?=t("ro_webuser")?></div>
		<div class="formInputContainer boxedMemoUI"><?php

			$arr = array();

			$u = app()->dbo("webuser");
			$u->whereAdd("id in (select userId from userrole where roleid = " . $obj->getIdValue() . ")");
			if($u->find())
				while($u->fetch())
					$arr[] = app()->getLinkedCaption($u);

			echo implode("; ", $arr);

		?></div>
	</div><?php

		$a = array(
			"s" => "select",
			"u" => "update",
			"d" => "delete",
			"l" => "Lock privilege");

		$cAllow = t("Allow all");
		$cDecline = t("Decline all");

	?><div class="ui-corner-all gridContainer roleRightsBox"><div class="gridHead">
		<div class="gridHeadCell gridCellW12"><?=t("registry")?></div><?php

		foreach($a as $v => $capt)
		{
			?><div class="gridHeadCell gridCellW6 center"><?=t($capt)?></div><?php
		}

		?><div class="gridHeadCell gridCellW12">&nbsp;</div></div>
		<div class="gridHead">
		<div class="gridHeadCell gridCellW12">&nbsp;</div><?php

		foreach($a as $v => $capt)
		{
			?><div class="gridHeadCell gridCellW6 center"><a href="JavaScript:allowCol('<?=$v?>');"><?=$cAllow?></a></div><?php
		}

		?><div class="gridHeadCell gridCellW12">&nbsp;</div></div>
		<div class="gridHead"><div class="gridHeadCell gridCellW12">&nbsp;</div><?php

		foreach($a as $v => $capt)
		{
			?><div class="gridHeadCell gridCellW6 center"><a href="JavaScript:declineCol('<?=$v?>');"><?=$cDecline?></a></div><?php
		}

		?><div class="gridHeadCell gridCellW12">&nbsp;</div></div>

	<?php

	$r = app()->dbo("robject");
	$r->orderBy('module DESC');
	$modules = [];
	$prevModule = null;
	if($r->find())
		while($r->fetch())
		{
			// Cache module data
			if (!isset($modules[$r->module])) {
				$rmodule = app()->dbo("rmodule");
				$rmodule->id = $r->module;
				$rmodule->find(true);
				$modules[$r->module] = $rmodule;
			} else {
				$rmodule = $modules[$r->module];
			}

			if (SHOW_HIDDEN_MODULES || strlen($rmodule->name) > 0) {
				if (!$prevModule || $prevModule->getIdValue() !== $rmodule->getIdValue()) {
				?>
				<div class="gridRow moduleRow">
					<div class="gridCell">
						<strong><?=(strlen($rmodule->name) > 0) ? t($rmodule->name) : 'ID: '.$rmodule->getIdValue()?></strong>
					</div>
					<div class="clr"></div>
				</div>
				<?php 
				} 
				?>
				<div class="gridRow">
					<div class="gridCell gridCellW12"><?=t("ro_" . $r->name)?></div><?php
				foreach($a as $v => $capt)
				{
					$c = new CheckBox($obj, "obj" . $r->getIdValue() . $v, " ");
					?><div class="gridCell gridCellW6 center"><?=$c->getInputPart()?></div><?php
				}
				?><div class="gridCell gridCellW12"><a href="JavaScript:allowRow(<?=$r->getIdValue()?>);"><?=$cAllow?></a> <a href="JavaScript:declineRow(<?=$r->getIdValue()?>);"><?=$cDecline?></a></div></div><?php
				$prevModule = $rmodule;

				$specialrights = $r->specialrights();
				if (is_array($specialrights) && count($specialrights) > 0) {
				?>
					<div class="gridHead">
						<div class="gridHeadCell gridCellW12"><?=t("specialrights")?></div>
						<div class="gridHeadCell gridCellW6 center"><?=t('specialright_allowed')?></div>
						<div class="gridHeadCell gridCellW30">&nbsp;</div>
					</div>
					<div class="gridHead">
						<div class="gridHeadCell gridCellW12">&nbsp;</div>
						<div class="gridHeadCell gridCellW6 center"><a href="JavaScript:allowSpecialCol('<?=$r->id?>');"><?=$cAllow?></a></div>
						<div class="gridHeadCell gridCellW30">&nbsp;</div>
					</div>
					<div class="gridHead">
						<div class="gridHeadCell gridCellW12">&nbsp;</div>
						<div class="gridHeadCell gridCellW6 center"><a href="JavaScript:declineSpecialCol('<?=$r->id?>');"><?=$cDecline?></a></div>
						<div class="gridHeadCell gridCellW30">&nbsp;</div>
					</div>
				<?php

					foreach ($specialrights as $right) {
						$c = new CheckBox($obj, "specialright". $r->id . $right->id, " ");
					?>
					<div class="gridRow">
						<div class="gridCell gridCellW12"><?=$right->getCaption()?></div>
						<div class="gridCell gridCellW6 center"><?=$c->getInputPart()?></div>
						<div class="gridCell gridCellW30">&nbsp;</div>
					</div>
					<?php
					}
				}
			}
		}

	?><div class="clearBoth"></div></div></div><script type="text/javascript">

		function allowRow(id)
		{
			app.func("grantRobject", {"rid": id, "v": 1});
		}

		function declineRow(id)
		{
			app.func("grantRobject", {"rid": id, "v": 0});
		}

		function allowCol(id)
		{
			app.func("grantGlobalPrivilege", {"cid": id, "v": 1});
		}

		function declineCol(id)
		{
			app.func("grantGlobalPrivilege", {"cid": id, "v": 0});
		}

		function allowSpecialCol(id)
		{
			app.func("grantSpecialPrivilege", {"cid": id, "v": 1});
		}

		function declineSpecialCol(id)
		{
			app.func("grantSpecialPrivilege", {"cid": id, "v": 0});
		}
		
</script>