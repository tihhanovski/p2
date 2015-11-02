<?php
/*
 * Created on Sep 29, 2011
 *
 * (c) Ilja Tihhanovski, Intellisoft
 *
 */


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
	if($r->find())
		while($r->fetch())
		{
			?><div class="gridRow">
				<div class="gridCell gridCellW12"><?=t("ro_" . $r->name)?></div><?php
			foreach($a as $v => $capt)
			{
				$c = new CheckBox($obj, "obj" . $r->getIdValue() . $v, " ");
				?><div class="gridCell gridCellW6 center"><?=$c->getInputPart()?></div><?php
			}
			?><div class="gridCell gridCellW12"><a href="JavaScript:allowRow(<?=$r->getIdValue()?>);"><?=$cAllow?></a> <a href="JavaScript:declineRow(<?=$r->getIdValue()?>);"><?=$cDecline?></a></div></div><?php
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

</script>