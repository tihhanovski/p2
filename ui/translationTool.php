<?php
/*
 * Created on Mar 1, 2012
 *
 * (c) Ilja Tihhanovski, Intellisoft
 *
 */

	include app()->getAbsoluteFile("ui/pagestart.php");

	if(!(($l1 = app()->request("l1")) && ($l2 = app()->request("l2"))))
		die("usage: " . app()->url("?action=translationTool&l1=[locale1]&l2=[locale2]"));

	$locale = app()->getLocale;
	app()->setLocale($l1);
	$l1t = app()->getTranslations();

	$tx = app()->dbo("translated");
	if($tx->find())
		while($tx->fetch())
			if(!isset($l1t[$tx->name]))
				$l1t[$tx->name] = "";

	app()->setLocale($l2);
	$l2t = app()->getTranslations();

	app()->setLocale($locale);


?><style>

	.translation{width: 400px;}

</style><div id="toolbar" style="position: fixed; float: right; border: 1px solid #c9c9c9; width: 200px; height: 200px; margin: 10px; padding: 10px; background-color: #ffffff">
	<div>translation tool: <?=$l1?> -> <?=$l2?></div><hr/>
	<a href="JavaScript:buildScript()">Build</a><br/>
	<a href="JavaScript:focusBack();">Back to translate</a><br/>
	<a href="JavaScript:showTranslated();">Show translated</a><br/>
	<a href="JavaScript:hideTranslated();">Hide translated</a><br/>
	</div>
	<div style="margin: 10px;"><table><?php

	foreach ( $l1t as $key => $value )
	{
		$tr = $l2t[$key];

		?><tr>
			<td align="right" class="ttc"><?=$key?></td>
			<td><input type="text" name="<?=$key?>" value="<?=$tr?>" class="translation"/></td>
			<td class="ttc"><?=$value?></td>
		</tr><?php

	}

?></table><textarea id="script" style="width: 100%; height: 400px;"></textarea><br/></div><script type="text/javascript">

	var lastFocused = null;

	$(function(){

		$(window).resize(function(){
			$("#toolbar").css("left", ($(window).width() - 240) + "px").css("top", "0px");
			$(".translation").focus(function(){
				lastFocused = this;
			});
		})

		$(window).resize();
		hideTranslated();

		$(".ttc").click(function(){
			var t = $(this).html();
			var tr = $(this).parent();
			console.log("translate " + t);
			console.log(tr);



		})

	});

	function focusBack()
	{
		$(lastFocused).focus();
	}

	function showTranslated()
	{
		$(".translation").each(function(){
			if(this.value != "")
				$(this).parent().parent().show();
		});
	}

	function hideTranslated()
	{
		$(".translation").each(function(){
			if(this.value != "")
				$(this).parent().parent().hide();
		});
	}

	function buildScript()
	{
		var script = '<' + '?php\n' +
			'/*\n' +
			'* Translation for locale <?=$l2?>\n' +
			'* By translationTool\n' +
			'*/\n\n' +
			'\tapp()->setTranslations(\n' +
			'\t\tarray(\n';
		$(".translation").each(function(){
			if(this.value != "")
				script += '\t\t\t"' + this.name + '" => "' + this.value + '",\n';
		});

		script += '\t\t)\n\t);\n';

		$("#script").val(script).focus();


	}

</script><?php

	include app()->getAbsoluteFile("ui/pagefinish.php");