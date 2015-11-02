<?php
/*
 * Created on Jun 28, 2012
 *
 * (c) Ilja Tihhanovski, Intellisoft
 *
 */
 
	
	$arr = app()->queryAsArray(
		"select l.id, l.dt, concat('logaction-', l.acn) as acn, u.uid " .
		"from objlog l inner join webuser u on u.id = l.userId " .
		"and l.robject = '" . app()->request("registry") . app()->request("id") . "' " .
		"order by id desc", 
		DB_FETCHMODE_OBJECT,
		array("dt" => FORMAT_DATETIME, "acn" => FORMAT_TRANSLATED) 
		);
				
?><div id="logList" style="position: fixed; top: 32px; left: 4px; width: 300px; height: 400px; border-right: 1px solid #c9c9c9; margin-right: 10px; overflow: auto;"></div>
<div id="logBody" style="position: fixed; top: 32px; left: 310px;"><iframe id="lb" style="border: 0px;"></iframe></div>
<script type="text/javascript">

	var log = <?=json_encode($arr)?>;
	
	$(window).resize(function()
	{
		var w = $(window);
		$("#logList").height(w.height() - 40);
		$("#logBody").width(w.width() - 320).height(w.height() - 40);
		$("#lb").width(w.width() - 310).height(w.height() - 40);
	});
	
	$(window).resize();
	
	function showLog(id)
	{
		$("#lb")[0].src = "?action=showrev&registry=" + req.registry + "&id=" + req.id + "&rev=" + id;
		$(".logItem").removeClass("selectedLogItem");
		$(".li" + id).addClass("selectedLogItem");
	}
	
	$(function(){
		try
		{
			$("#logList").html('<div></div>');
			for(x = 0; x < log.length; x++)
			{
				var i = log[x];
				var html = '<div class="logItem li' + i.id + '" onclick="JavaScript:showLog(' + i.id + ');">' + i.acn + ' ' + i.dt + ' // ' + i.uid + '</div>';
				$("#logList").append(html);
			}
		}catch(e){}
		
		try
		{
			showLog(log[0].id);
		}catch(e){}
		
	});

</script>