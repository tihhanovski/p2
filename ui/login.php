<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
	<head><title><?=APP_TITLE?></title><meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
	<link rel="stylesheet" type="text/css" href="<?=app()->url("ui/login.css")?>"/>
</head>
<body><script type="text/javascript" src="<?=SETUP_JQUERY?>"></script>
<script type="text/javascript" src="<?=SETUP_JQUERY_UI?>"></script>
<?php

	if(defined("LOGINBGPIC"))
	{
		$bgUrl = LOGINBGPIC;
		if(app()->getAbsoluteFile($bgUrl))
		{
			$bgUrl = app()->url($bgUrl);
			?><style>

				body{
					background: url('<?=$bgUrl?>');
					background-position:bottom center;
					background-repeat:no-repeat;
					background-size:cover;
					background-attachment:fixed;
				}

			</style><?php
		}
	}

?>
<div class="loginHeader"><?=APP_TITLE?></div>
<form id="lf" method="POST" action="<?=app()->url()?>"><input type="hidden" name="auth" value="login"/><div class="loginBox">
	<div class="loginSignInBoxHeader"><?=t("Sign in")?></div>
	<div class="loginLabel"><?=t("Login")?></div>
	<div><input type="text" name="uid" value="" id="uid" class="loginInput"/></div>
	<div class="loginLabel"><?=t("Password")?></div>
	<div><input type="password" name="pwd" id="pwd" value="" class="loginInput"/></div>
	<div class="submitPanel"><input type="submit" class="submit" name="name" value="<?=t("Log in")?>"/></div>
</div><?php

	if(defined("APP_COPYRIGHT") && $copyright = APP_COPYRIGHT)
	{
		?><div class="loginFooter"><?=$copyright?></div><?php
	}

?></form><script type="text/javascript">

	try{document.getElementById("uid").focus();}catch(e){}

	$(function(){
		$("#lf").submit(function(){
			var url = this.action + "?action=loginJson&" + $(this).serialize();
			$("input").each(function(){$(this).attr("disabled", "true");});
			$("#pwd").val("");
			var jqxr = $.get(url, function(data)
			{
				$("input").each(function(){$(this).removeAttr("disabled");});
				if(data.state == "ok")
				{
					var x;
					if(data.warnings)
						for(x = 0; x < data.warnings.length; x++)
							if(data.warnings[x].message == "requirePasswd")
							{
								document.location = "<?=app()->url("?action=requirePasswd")?>";
								return;
							}
					$("#lf").html("");
					document.location = "<?=app()->url()?>";
				}
				else
				{
					$(".loginBox").addClass("loginError").effect("bounce", { times:3, direction:"left" }, 300);
					//wrong login, do something;
				}
			}, "json").fail(function(){$("input").each(function(){$(this).removeAttr("disabled");});});
			return false;
		});

		$(window).resize(function(){
			var w = $(window);
			$(".loginFooter").css("top", w.height() - 50).css("width", w.width());
		});

		$(window).resize();
		$(".loginFooter").show();
	});

	function log(s)
	{
		console.log(s);
	}

</script></body></html>