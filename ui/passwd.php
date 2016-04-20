<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
	<head><title><?=APP_TITLE . " " . t("Change password")?></title>
	<meta http-equiv="Content-Type" content="text/html; charset=<?=SETUP_CHARSET?>" /><link rel="icon" href="<?=app()->url("resources/webicon.ico")?>" type="image/x-con" /><link rel="shortcut icon" href="<?=app()->url("resources/webicon.ico")?>" type="image/x-con" /><?php

	app()->uiHelper()->includeStyles();
	app()->uiHelper()->includeScripts();

	?><link rel="stylesheet" type="text/css" href="<?=app()->url("ui/login.css")?>"/>
</head>
<body><script type="text/javascript" src="<?=SETUP_JQUERY?>"></script>
<script type="text/javascript" src="<?=SETUP_JQUERY_UI?>"></script>
<div style="padding: 20px; font-size: 50pt; border-bottom: 1px solid #dddddd; margin-bottom: 40px; font-weight: bold; color: #c9c9c9;"><?=APP_TITLE . " " . t("Change password")?></div>
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
			<div class="formInputContainer"><button onclick="JavaScript:changePwd();"><b><?=t("Change password")?></b></button></div>
		</div>
		<div class="formRow">
			<div class="formLabel">&nbsp;</div>
			<div class="formInputContainer"><?php

				foreach (app()->getPasswordValidators() as $validator)
					echo "<li>" . t($validator->getErrorMessage()) . "</li>";

			?></div>
		</div>
	<div class="formRow"></div></div>
<script type="text/javascript">

	try{document.getElementById("uid").focus();}catch(e){}

	function changePwd()
	{
		var o = $("#profile_oldpwd").val();
		var n = $("#profile_newpwd").val();
		var n2 = $("#profile_newpwd2").val();

		if(n != n2)
			return app.alert("<?=t("new passwords does not match")?>");

		$("#profile_oldpwd").val("");
		$("#profile_newpwd").val("");
		$("#profile_newpwd2").val("");

		app.ajaxCommand(baseUrl() + "?action=passwd" +
			"&old=" + encodeURIComponent(o) +
			"&new=" + encodeURIComponent(n), "", function(data){
				if(data.state == "ok")
					document.location = "<?=app()->url()?>";
			});
	}

</script><?php

	app()->uiHelper()->includePageSetup();

?></body></html>