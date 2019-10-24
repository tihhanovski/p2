
var	commentsControl = {
		"create": function(ctrl)
		{
			try
			{
				var cid = ctrl[0].id;
				var html = '<i class="fa fa-comments-o rightPanelFAIcon" aria-hidden="true"></i><div id="commentsContainer" class="commentsContainer"></div>' +
					'<div id="addComment">' +
					'<textarea id="newComment" class="newCommentText" onkeypress="Javascript:commentsControl.newCommentTextKP(event);"></textarea>' +
					'</div>';

				ctrl.html(html);

				this.fill();
			}
			catch(e){}
		},

		"fill": function()
		{
			$.get(baseUrl() + "?action=getComments&registry=" + req.registry + "&id=" + req.id,
				function(data)
				{
					var html = '';
					if(data.state == "ok")
					{
						var x;
						for(x = 0; x < data.items.length; x++)
						{
							html += commentsControl.getItemHtml(data.items[x]);
						}
					}
					var c = $("#commentsContainer");
					c.html(html);
					c.scrollTop($(c)[0].scrollHeight);
					$("#newComment").val("");
				},
				"json");
		},

		"getItemHtml": function(i)
		{
			return '<div class="commentsItem">' +
				'<span class="commentsItemUser">' + i.uid + '</span>' +
				'<span class="commentItemBody">' + i.comment + '</span>' +
				'<div class="commentItemDate">' + i.dt + '</div>' +
				'</div>';
		},

		"newCommentTextKP": function(e)
		{
			if(e.keyCode == 13)
			{
				rq = new Object();
				rq.action = "comment";
				rq.registry = req.registry;
				rq.id = req.id;
				rq.comment = $("#newComment").val();
				$("#newComment").val("");
				$.post(baseUrl(), rq,
					function(data)
					{
						commentsControl.fill();
					},
					"json");
				e.bubbles = false;
			}
		}
	};