
	//--------messages control---------
	var msgControl = new Object();

	msgControl.create = function(ctrl)
	{
		try
		{
			cid = ctrl[0].id;
				
			var html = 
					'<div id="' + cid + 'ShowLink">' + 
					'<a href="JavaScript:msgControl.showList(\'' + cid + '\');" tabindex="-1">' + t("messages") + '</a></div>' +
					'<div id="' + cid + 'HideLink">' + 
					'<a href="JavaScript:msgControl.hideList(\'' + cid + '\');" tabindex="-1">' + t("messages") + '</a></div>' +
					'<div class="rightPanelItem" id="' + cid + 'MsgList"></div>';
			ctrl.html(html);
			$("#" + cid + "HideLink").hide();
		}catch(e){}
	};
	
	msgControl.hideList = function(ctrlid)
	{
		$("#" + ctrlid + "MsgList").html("").hide();
		$("#" + ctrlid + "ShowLink").show();
		$("#" + ctrlid + "HideLink").hide();
		$("#" + ctrlid).parent().width(200);
		$(window).resize();
	}
		
	msgControl.showList = function(ctrlid)
	{
		$.get(
		baseUrl() + "?action=messages&path=" + contextName(),
		function(data)
		{
			if(data.state == "ok")
			{
				var html = '';
				var x;
				for(x = 0; x < data.msg.length; x++)
				{
					var i = data.msg[x];
					html += '<div class="msgHeader" id="msgHeader_' + i.id + '" onclick="JavaScript:msgControl.showMsgBody(\'' + i.id + '\');">' +
						'<b>' + i.sender + '</b> ' + i.caption + '</div>' + 
						'<div class="msgBody" id="msgBody_' + i.id + '">' + i.body + 
						'<br/>' + i.sent + 
						'</div>';
				}
				html = '<div class="msgListContents">' + html + '</div>';
				html += '<div class="smallText"><a href="JavaScript:msgControl.newMessage(\'#' + ctrlid + 'MsgList\');">' + t("send message") + '</a></div>';
				$("#" + ctrlid + "MsgList").html(html).show();
				$("#" + ctrlid + "ShowLink").hide();
				$("#" + ctrlid + "HideLink").show();
				$("#" + ctrlid).parent().width(300);
				$(window).resize();
			}
		}, "json");
	};
	
	msgControl.showMsgBody = function(id)
	{
		$("#msgBody_" + id).toggle();
	}
	
	msgControl.newMessage = function(ctrl)
	{
		var html = '<div>' + t("reciever") + '</div>' + 
			'<div style="padding-left: 2px;">' +
			'<input type="text" id="newmsgReciever" value="" style="width: 295px;"/></div>' +
			'<div>' + t("subject") + '</div>' +
			'<div style="padding-left: 2px;"">' +
			'<input type="text" id="newmsgSubject" value="" style="width: 295px;"/></div>' +
			'<div style="padding-left: 2px; padding-top: 8px;">' +
			'<textarea id="newmsgBody" style="height: 185px; width: 295px;"></textarea></div>' +
			'<div style="padding-top: 5px; text-align: right;">' +
			'<a href="JavaScript:sendMessage();">' + t("send") + '</a>&nbsp;' + 
			'<a href="JavaScript:bubble.hide()">' + t("cancel") + '</a></div>';
			
		bubble.pos($(ctrl).parent().parent().position().left - 290, $(ctrl).position().top + 65)
			.dimensions(300, 300)
			.show(html);
		$("#newmsgReciever").focus();
	}
	
	function sendMessage()
	{
		$.get(
		baseUrl() + "?action=sendMessage&path=" + contextName + 
				"&reciever=" + encodeURIComponent($("#newmsgReciever").val()) +
				"&subject=" + encodeURIComponent($("#newmsgSubject").val()) +
				"&body=" + encodeURIComponent($("#newmsgBody").val()),
		function(data)
		{
			processWarnings(data);
			if(data.state == "ok")
				bubble.hide();
			else
			{
				alert(data.message);
				$("#newmsg" + data.focus).focus();
			}
		}, "json");		
	}