<?php
/*
 * Created on Sep 29, 2011
 *
 * (c) Ilja Tihhanovski, Intellisoft
 *
 */


	if($obj->getIdValue() == SYSTEM_USER_ID)	//system user does not need any UI
	{
		echo lockedMemo($obj->uid, "uid");
	}
	else
	{
		if($obj->isNew())
			echo simpleform(array(
				textbox($obj, "name", "name(real)"),
				textbox($obj, "uid"),
				textbox($obj, "pwd"),
				textbox($obj, "email"),
				));
		else
		{
			$cbs = "";
			$r = app()->dbo("role");
			if($r->find())
				while($r->fetch())
					if($r->getIdValue() > SYSTEM_ROLE_ID)
						$cbs .= checkbox($obj, "role" . $r->getIdValue(), $r->name);

			echo simpleform(array(
				lockedMemo($obj->uid, "uid"),
				textbox($obj, "name", "name(real)"),
				textbox($obj, "email"),
				$cbs,
				lockedMemo("<a href=\"JavaScript:passwdStart();\">" . t("Change password") . "</a>", "&nbsp;"),
				));
		}
	}

?><script type="text/javascript">

	function passwdStart()
	{
		bubble.show(
			'<div class="formRow">' +
			'<div class="formLabel">&nbsp;</div>' +
			'<div class="formInputContainer"><?php

				foreach (app()->getPasswordValidators() as $validator)
					echo "<li>" . t($validator->getErrorMessage()) . "</li>";

			?></div>' +
			'</div>' +
			'<div class="formRow">' +
			'<div class="formLabel"><label for="pwdx"><?=t("New password")?></label></div>' +
			'<div class="formInputContainer"><input class="textBox" id="passwdNew" type="text" value=""/></div>' +
			'</div>' +
			'<div class="formRow">' +
			'<div class="formLabel">&nbsp;</div>' +
			'<div class="formInputContainer"><button id="passwdClick" onclick="JavaScript:doPasswd();"><?=t("Change password")?></button></div>' +
			'</div>'

			);
		$("#passwdNew").focus().keypress(function(evt){
			if ( event.which == 13 )
			{
				doPasswd();
			}
		});
	}

	function doPasswd()
	{
		var p = $("#passwdNew").val();
		if(p != "")
		{
			$("#passwdNew").val("");
			ajaxCommand(baseUrl() + "?registry=webuser&id=" + req.id + "&action=passwd" +
					"&new=" + encodeURIComponent(p));
			bubble.hide();
		}
	}

</script>