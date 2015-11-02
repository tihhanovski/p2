<?php
/*
 * Created on Nov 03, 2011
 *
 * (c) Ilja Tihhanovski, Intellisoft
 *
 */

	echo
		lockedMemo($obj->uid, "uid"),
		textbox($obj, "name", "name(real)"),
		textbox($obj, "email");

?><div class="formRow">
		<div class="formLabel"><?=t("ro_role")?></div>
		<div class="formInputContainer boxedMemoUI"><?php

			$arr = array();

			$u = app()->dbo("role");
			$u->whereAdd("id in (select roleID from userrole where userID = " . $obj->getIdValue() . ")");
			if($u->find())
				while($u->fetch())
					$arr[] = app()->getLinkedCaption($u);

			echo implode("; ", $arr);

		?></div>
</div>
<div class="formRow"><div id="tabs">
	<ul>
		<li><a href="#tabGeneral"><?=t("General")?></a></li>
		<li><a href="#tabUI"><?=t("User interface")?></a></li>
		<li><a href="#tabPasswd"><?=t("Password")?></a></li>
	</ul>
	<div id="tabGeneral"><?php

		echo textbox($obj, PROPERTY_PREFIX . "EmailBcc", "BCC");

		$l = app()->dbo("language");
		if($l->find())
			while($l->fetch())
				echo textarea($obj, PROPERTY_PREFIX . "Signature_" . $l->code, t("signature") . " (" . $l->name . ")");

		echo textbox($obj, PROPERTY_PREFIX . "Phone", "Phone"),
		textbox($obj, PROPERTY_PREFIX . "Fax", "Fax"),
		textbox($obj, PROPERTY_PREFIX . "Mobile", "Mobile phone"),
		textbox($obj, PROPERTY_PREFIX . "Occupation", "Occupation");

	?><div class="formRow"></div>
	</div>
	<div id="tabUI"><?php

		echo checkbox($obj, PROPERTY_PREFIX . "DocOpenInTab", "Open documents in tabs");


	?><div class="formRow"></div>
	</div>
	<div id="tabPasswd">
		<div class="formRow">
			<div class="formLabel"><label for="profile_oldpwd"><?=t("Old password")?></label></div>
			<div class="formInputContainer"><input  class="textBox" id="profile_oldpwd" type="password" value=""></input></div>
		</div>
		<div class="formRow">
			<div class="formLabel"><label for="profile_newpwd"><?=t("New password")?></label></div>
			<div class="formInputContainer"><input  class="textBox" id="profile_newpwd" type="password" value=""></input></div>
		</div>
		<div class="formRow">
			<div class="formLabel"><label for="profile_newpwd2"><?=t("New password")?></label></div>
			<div class="formInputContainer"><input  class="textBox" id="profile_newpwd2" type="password" value=""></input></div>
		</div>
		<div class="formRow">
			<div class="formLabel">&nbsp;</div>
			<div class="formInputContainer"><a href="JavaScript:changePwd();"><?=t("Change password")?></a></div>
		</div>
	<div class="formRow"></div></div>
</div><script type="text/javascript">

	$(function() {
		$( "#tabs" ).tabs();
	});

	function changePwd()
	{
		var o = $("#profile_oldpwd").val();
		var n = $("#profile_newpwd").val();
		var n2 = $("#profile_newpwd2").val();

		$("#profile_oldpwd").val("");
		$("#profile_newpwd").val("");
		$("#profile_newpwd2").val("");

		if(n != n2)
			return alert("<?=t("new passwords does not match")?>");
		if(confirm("<?=t("Change password?")?>"))
			ajaxCommand(baseUrl() + "?action=passwd" +
				"&old=" + encodeURIComponent(o) +
				"&new=" + encodeURIComponent(n));
	}

</script>