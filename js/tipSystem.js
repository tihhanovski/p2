
	var tipSystem = {

		tips: [],
		pointer: 0,

		i18n: {"cancel": "Cancel"},

		start: function(tips, i18n)
		{
			this.tips = new Array();

			//log(tips.length);

			var x;
			var c = 0;
			for(x = 0; x < tips.length; x++)
				if(tips[x].control == "" || $(tips[x].control).length > 0)
				{
					this.tips[c++] = tips[x];
				}

			this.pointer = 0;

			if(i18n)
				this.i18n = i18n;

			this.next();
		},

		cancel: function()
		{
			tipSystem.container().hide().html("");
			$.get("?action=tipSystemDismiss");
		},

		next: function()
		{
			if(this.pointer < this.tips.length)
			{
				this.showTip(this.tips[this.pointer]);
				this.pointer++;
			}
			else
				tipSystem.container().hide();
		},

		showTip: function(data)
		{
			try
			{
				var html = '<div class="tipContents" id="tipContents">' +
								data.message + '<br/>' +
								'<div style="text-align: right; margin-top: 20px;">' +
								'<a href="JavaScript:tipSystem.cancel();">' + tipSystem.i18n.cancel + '</a>' +
								'<br>[' + data.control + ']' +
								'</div>' +
								'</div>';
				tipSystem.container().
					html(html).
					show();

				var tip = $("#tipContents");

				var left = ($(window).width() - tip.width()) / 2;
				var top = ($(window).height() - tip.height()) / 2;
				var co = "t";

				if(data.control != "")
				{
					var tgt = $(data.control);
					if(tgt.length > 0)
					{
						var tleft = tgt.position().left;
						var twidth = tgt.width();
						var ttop = tgt.position().top;
						var theight = tgt.height();
					}
				}

				log("left: " + left);
				log("top: " + top);

				/**/
				if(data.control != "")
				{
					var tgt = $(data.control);
					if(tgt.length > 0)
					{
						left = tgt.position().left + tgt.width() / 2;
						top = tgt.position().top + tgt.height() + 10;
						if(left + tip.width() > $(window).width() - 30)
						{
							left = tgt.position().left - tip.width() - 15;
							top = tgt.position().top;
							co = "r";
							if(top < 5)
								top = 5;
						}

						if(top + tip.height() > $(window).height() - 30)
						{
							top = tgt.position().top - tip.height() - 30;
							co = "b";
						}
					}
				}/**/

				tip
				.css("left", left + "px")
				.css("top", top + "px")
				.click(function(event){event.stopPropagation();});

				/**/
				if(data.control != "")
				{
					var coX = left + 5;
					var coY = top - 9;
					var coW = 19;
					var coH = 10;

					switch(co)
					{
						case "r":
							coX = left + tip.width() + 21;
							coY = top + 2;
							coW = 10;
							coH = 19;
							break;
						case "b":
							coY = top + tip.height() + 21;
							break;
					}

					var tch = '<img id="tipCallout" src="' + setup.WFW_WEB + 'ui/img/tipsystem/' + co + '.png" width="' + coW + '" height="' + coH + '" border="0" />';

					tipSystem.container().append(tch);

					var tc = $("#tipCallout")
						.css("z-index", "55")
						.css("position", "fixed")
						.css("left", coX + "px")
						.css("top", coY + "px");
				}/**/

				$.get("?action=tipSystemDisplayed&id=" + data.id);
			}
			catch(e)
			{
				tipSystem.container().hide();
			}
			this.container().show();
		},

		container: function()
		{
			if($("#tipSystemContainer").length == 0)
			{
				$("body").append('<div id="tipSystemContainer" style="display: none; left: 0px; top: 0px; position: absolute; cursor: pointer; z-index: 30;"></div>');
				$(window).resize(function(){$("#tipSystemContainer").css("width", $(window).width() + "px").css("height", $(window).height() + "px")});
				$(window).resize();
				$("#tipSystemContainer").click(function(){tipSystem.next();});
			}
			return $("#tipSystemContainer");
		}
	}