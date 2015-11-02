	//--------files control---------
	var filesControl = new Object();

	filesControl.create = function(ctrl)
	{
		try
		{
			cid = ctrl[0].id;
				
			var html = 
					'<div id="' + cid + 'ShowLink">' + 
					'<a href="JavaScript:filesControl.showList(\'' + cid + '\');" tabindex="-1">' + t("files") + '</a></div>' +
					'<div id="' + cid + 'HideLink">' + 
					'<a href="JavaScript:filesControl.hideList(\'' + cid + '\');" tabindex="-1">' + t("files") + '</a></div>' +
					'<div class="rightPanelItem" id="' + cid + 'FileList"></div>';
			ctrl.html(html);
			$("#" + cid + "HideLink").hide();
		}catch(e){}
	};
	
	filesControl.hideList = function(ctrlid)
	{
		$("#" + ctrlid + "FileList").html("").hide();
		$("#" + ctrlid + "ShowLink").show();
		$("#" + ctrlid + "HideLink").hide();
		$("#" + ctrlid).parent().width(200);
		$(window).resize();
	}
		
	filesControl.showList = function(ctrlid)
	{
		$.get(
		baseUrl() + "?action=files&path=" + contextName(),
		function(data)
		{
			if(data.state == "ok")
			{
				var html = '<div class="fileListContents">';
				var x;
				for(x = 0; x < data.files.length; x++)
				{
					var f = data.files[x];
					html += '<div>' + 
						'<a href="' + f.url + '" target="_blank" class="fileListItem">' +
						f.name + '</a></div>';
				}
				html += '</div>';
				$("#" + ctrlid + "FileList").html(html).show();
				$("#" + ctrlid + "ShowLink").hide();
				$("#" + ctrlid + "HideLink").show();
				
				$("#" + ctrlid).parent().width(300);
				$(window).resize();
			}
		}, "json");
	};
