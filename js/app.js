var app = {

		"alert": function(message)
		{
			var html = '<h1>' + message + '</h1>' +
				'<div class="confirmButtons" style="text-align: center;">' +	//TODO
					'<button id="btnOK">' + t('OK') + '</button>' +
				'</div>';
			bubble.reset();
			bubble.dimensions(400, 150).show(html);
			$("#btnOK").button().click(function( event ) {
        		event.preventDefault();
        		bubble.hide();
      		}).focus();

			var h1 = $("#bubbleContents").children().height();
				$("#bubbleContents").height(h1 + 60);
		},

		"confirm": function(message, func, funcNo)
		{
			var html = '<h1>' + message + '</h1>' +
				'<div class="confirmButtons">' +
					'<button id="btnYes" class="floatLeft">' + t('Yes') + '</button>' +
					'<button id="btnNo" class="floatRight">' + t('No') + '</button>' +
				'</div>';
			bubble.reset();
			bubble.dimensions(400, 150).show(html);
			$("#btnYes").button({icons: {primary: "ui-icon-check"}}).click(function( event ) {
        		event.preventDefault();
        		bubble.hide();
        		func();
      		});
			$("#btnNo").button({icons: {primary: "ui-icon-close"}}).click(function( event ) {
        		event.preventDefault();
        		bubble.hide();
        		if(funcNo)
        			funcNo();
      		});

			var h1 = $("#bubbleContents").children().height();
				$("#bubbleContents").height(h1 + 60);
			$("#btnYes").focus();
		},

		"confirmedFunc": function(message, func)
		{
			log(func);
			app.confirm(message, function(){app.func(func)});
		},

		"saveDocument": function()
		{
			app.removeWarnings();
			if(app.waitForSavingFields(app.saveDocument))
				return;
			app.progressMsg(t("saving"));

			if (clientSideCheck())
			{
				var savingNew = obj.__isNotSaved;

				var url = baseUrl() +
					"?action=saveDocument" +
					"&registry=" + req.registry +
					"&id=" + req.id;

				var rq = new Object();
				rq.action = "saveDocument";
				rq.registry = req.registry;
				rq.id = req.id;
				if(req.saveDocumentWithFields)
				{
					rq.fieldsData = 1;
					//fill rq with form data
					$("input").each(function(){
							rq[this.id] = this.type == "checkbox" ? this.checked ? 1 : 0 : $(this).val();

					})
				}

				log("saveDocument " + baseUrl());
				$.post(baseUrl(),
					rq,
					function(data)
					{
						if(data.state == "ok")
						{
							app.nowHasUnsavedChanges(false);
							if(savingNew)
							{
								req.id = data.message;
								app.reloadCurrentDocumentGridNewRow(req.id);
								app.reopenDocument();
							}
							else
							{
								handleAjaxResponce(data);
								app.reloadCurrentDocumentGridRow();
							}
							retrieveID(data.message);
							if(app.afterSaveDocument)
								app.afterSaveDocument();
						}
						else
						{
							handleAjaxResponce(data);
							focusFirstWarning(data);
						}
					}, "json");
			}
		},

		"reloadPage": function()
		{
			app.reloadCurrentDocumentGridRow();
			window.onbeforeunload = null;
			var url = "" + document.location;
			var a = url.split("#");
			try{url = a[0] + "#" + document.activeElement.id}catch(e){}
			document.location.assign(url);
			document.location.reload();
		},

		"reopenDocument": function()
		{
			app.reloadCurrentDocumentGridRow();
			window.onbeforeunload = null;
			openSpecificDocument(req.registry, req.id);
		},

		"setupToolbar": function()
		{
		},

		"markFieldAsSaving": function(id)
		{
			$("#" + id).addClass("waitingToSave");
		},

		"unmarkFieldAsSaving": function(id)
		{
			$("#" + id).removeClass("waitingToSave");
		},

		"waitForSavingFields": function(func)
		{
			if(app.waitingBeforeSave)
			{
				app.waitingBeforeSave = false;
				removeWarnings();
				return false;
			}

			if($(".waitingToSave").length > 0)
			{
				app.waitingBeforeSave = true;
				app.progressMsg(t("Preparing to save..."));
				setTimeout(func, app.waitForSavingFieldsTimeoutSecs * 1000);
				log("waiting for 1 secs before save");
				return true;
			}
		},

		"loadLinkedEmails": function()
		{
			var url = baseUrl() + "?registry=" + req.registry + "&id=" + req.id + "&action=getLinkedEmailsAsHtml";
			log("loadLinkedEmails");
			var xid = "#" + obj.fullpath + "_linkedEmails";
			$(xid).html(t("Loading..."));
			$.get(url, function(html){
				$(xid).html(html);
			});
		},

		"onSelectAutocomplete": function(v, d, f)
		{
			$("#" + f).focus();
			saveField(f, f, v);
		},

		"setupFieldFormats": function()
		{
			setDatePicker(".datepicker");
			setNumericInt(".numericInt");
			setNumericDbl(".numericDbl");
		},

		"rightPanelThreshold": 30,

		"resizeMainMenu": function()
		{
			var w = $(window).width();
			$("#mainMenu").offset({ top: 30, left: 0 })
				.width(w - 100)
				.height($(window).height() - 130);
		},

		"start": function()
		{
			try
			{
				if($.datepicker.regional[setup.LOCALE])
					$.datepicker.setDefaults($.datepicker.regional[setup.LOCALE]);
			}
			catch(e){}

			app.setupFieldFormats();

			app.initMSelectList();

			$("textarea")
				.keydown(function(e){setTextAreaHeight(this);})
				.each(function(){setTextAreaHeight(this);});

			$(window).resize(function()
			{
				var w = $(window).width();
				$(".rightPanel").css("left", w - $(".rightPanel").width() - app.rightPanelThreshold);

				app.resizeMainMenu();

				//$(".formRowLocked").width(w - 40);
				//$(".formInputContainerLocked").width(w - 270);	//TODO test 05.02.2016
			});
			$(window).resize();
			$(".rightPanel").show();

			jQuery.fn.idle = function(time){
				return this.each(function(){
					var i = $(this);
					i.queue(function(){setTimeout(function(){i.dequeue();}, time);});
				});
			};

			try{$("#toolbar_MainMenu").find("a")[0].href = "JavaScript:app.mainMenu();";}catch(e){}
		},

		"finish": function()
		{
			$(window).resize();
			$("input").focus(function(){selectActiveField();});
			try{
				if(!obj.__isNotSaved)
					if(obj.__caption)
						document.title = document.title + " " + obj.__caption;
			}catch(e){}

			app.user = setup.user;

			try{
				if(req.startupMsg)
					app.infoBox(req.startupMsg);
			}catch(e){}
		},

		"changeLocale": function (loc)
		{
				$.get('?locale=' + loc, function(data)
					{
						try{document.location.reload();}catch(e){}
						var dl = document.location;
						document.location = dl;
					});
		},

		"multiselClicker": function()
		{
				var ctrl = $(this).parent().parent().parent().prev();
				var id = ctrl[0].id;
				var i1 = this.value;
				var fld = id + ":multisel:" + i1;
				var chk = this.checked ? 1 : 0;
				saveField(id, id, setup.SPECIALVALUE_MSELECT + this.value + ':' + chk);
		},

		"initMSelectList": function()
		{
			$(document).on("click", ".mselect-list-item-checkbox", app.multiselClicker);

			$(".multiSelect").multiselect();
		},

		"mmacc": function()
		{
			$.get("?action=mainMenuItems", function(data){
				if(data.state == "ok")
					app.buildAccordionMainMenu($("#mainMenu"), data.items);
			}, "json");

			$("#mainMenu");
		},

		"buildAccordionMainMenu": function(div, items)
		{
			var html = '<div id="mmacc" style="width: 300px;">';
			var lm = "";
			var ld = "";
			for(x = 0; x < items.length; x++)
			{
				var i = items[x];
				if(lm != i.module)
				{
					html += (lm == '' ? '' : '</div>') +
						'<h3>' + i.module + '</h3><div>';
					lm = i.module;
				}
				html += '<div>';

				if(ld != i.rtype)
				{
					html += '<div>' + i.rtype + '</div>';
					ld = i.rtype;
				}

				html += '<a href="?registry=' + i.reg + '">' + i.caption + '</a>';
				html += '</div>';
			}
			html += '</div></div>';

			div.html(html);
			$("#mmacc").accordion({icons:null});
		},

		"mainMenu": function()
		{
			if($("#mainMenu")[0].style.display == "none")
			{
				$.get(baseUrl() + "?action=mainMenu",
				function(data)
				{
					$("#mainMenu")
						.html(data)
						.css("width", ($(window).width() - 100) + "px")
						.css("top", ($(".topMenu").height() + 5) + "px")
						.css("left", "0px")
						.slideDown(200);
				});
			}
			else
			{
				$("#mainMenu").slideUp(200);
			}
		},

		"closeDocument": function()
		{
			this.progressMsg(t("Closing document"));
			this.func("closeDocument");
		},

		"reopenClosedDocument": function()
		{
			this.progressMsg(t("Reopening document"));
			this.func("reopenDocument");
		},

		"removeWarnings": function(field)
		{
			if(field)
				$("#" + field + "_warnings").html("").hide();
			else
			{
				$(".fieldWarningContainer").html("").hide();
				$("#doc_warnings").html("").hide();
			}
		},

		"progressMsg": function(s)
		{
			var msg = '<img src="' + setup.WFW_WEB + 'ui/img/16/progress.gif" border="0" class="mGridBottomIcon" />' + s;
			this.infoBox(msg);
		},

		"infoBox": function(s)
		{
			processWarning({"field": "", "severity": "info", "message": s}, 60);
		},

		"nowHasUnsavedChanges": function(b)
		{
			try{
				obj.__hasUnsavedChanges = b;
			}catch(e){}
		},

		"hasUnsavedChanges": function()
		{
			try{
				return obj.__hasUnsavedChanges;
			}catch(e){
				return false;
			}
		},

		"contextName": function()
		{
			return req.registry + req.id;
		},

		"copyDocument": function()
		{
			if(app.hasUnsavedChanges())
			{
				if(!confirm(t("Copy unsaved document?")))
					return;
				app.nowHasUnsavedChanges(false);
			}
			if (clientSideCheck())
			{
				app.progressMsg(t("making copy"));
				$.get(baseUrl() +
					"?action=copyObject" +
					"&path=" + app.contextName(),
					function(data)
					{
						if(data.state == "ok")
							if(data.copyID)
							{
								var url = baseUrl() +
									"?action=open" +
									"&registry=" + req.registry +
									"&id=" + data.copyID +
									"&startupMsg=" + encodeURI("copy opened");
								document.location = url;
							}
					}, "json");
			}
		},

		"askBeforeLeave": function()
		{
			return app.hasUnsavedChanges() ? t("Leave unsaved document?") : null;
		},

		"userMenu": function()
		{
			var html = '<div class="userDropdownMenuItem right"><b>' + this.user.name + '</b></div>' +
				'<div class="userDropdownMenuItem right"><a href="JavaScript:openUserProfile();">' + t('User profile') + '</a></div>';
			if(app.i18n)
			{
				html += '<div class="userDropdownMenuItem">' + t('Change locale') + ': ';
				try{
					$.each(locales, function(i, v)
							{
								if(v == setup.LOCALE)
									html += '<b>' + v + '</b>';
								else
									html += '<a href="JavaScript:app.changeLocale(\'' + v + '\');">' + v + '</a>';
								html += '&nbsp;';
							});
				}catch(e){}
				html += '</div>';
			}
			html += '<hr/>';
			html += '<div class="userDropdownMenuItem right"><a href="JavaScript:logout();">' + t("logout") + '</a></div>';
			html += '</div>';

			bubble.userMenu(html);
		},

		"func": function(name, params, progressMsg, callAfter)
		{
			var url = "?registry=" + req.registry + "&action=" + name;
			if(req.id)
				url += "&id=" + req.id;
			if(params)
				url += "&" + $.param(params);
			this.ajaxCommand(url, progressMsg, callAfter);
		},

		"ajaxCommand": function(s, progressMsg, callAfter)
		{
			if(progressMsg)
				app.progressMsg(t(progressMsg));
			$.get(s , function (data)
					{
						try{
							app.handleAjaxResponce(data);
						}catch(e){}
						try{
							if(callAfter)
								callAfter(data);
						}catch(e){log("app.ajaxCommand: callAfter> " + e.message)}
					}, "json");
		},

		"handleAjaxResponce": function (data, ctrlId)
		{
			processWarnings(data);

			if(data.state == "error")
			{
				//log(data);
				processError(data.message, ctrlId);
				if(data.message != "")
					app.alert(data.message);
			}

			if(data.state == "ok")
			{
				if(data.update)
					updateOtherFields(data.update);
				if(data.reloadPage)
					app.reloadPage();
				if(data.reload)
					app.reopenDocument();
				if(data.goback)
					openDocumentList();
			}
		},

		"reloadCurrentDocumentGridRow": function()
		{
			try{window.opener.grid.reloadRow(req.id);}catch(e){}
		},

		"reloadCurrentDocumentGridNewRow": function(id)
		{
			try{window.opener.grid.reloadNewRow(id);}catch(e){}
		},

		"waitingBeforeSave": false,
		"waitForSavingFieldsTimeoutSecs": 1
};