
	function toggleAddressBoxes(field)
	{
		//log('#' + req.registry + req.id + '_addressBox' + field + 'REPR');
		$('#' + req.registry + req.id + '_addressBox' + field + 'REPR').toggle('fade');
		$('#addressBox_' + field).toggle('slow');
	}

	function img(src, alt)
	{
		return '<img src="' + setup.WFW_WEB + '/ui/img/16/' + src + '" alt="' + alt + '" border="0"/>';
	}

	function prevYear(f1, f2)
	{
		ajaxCommand("?registry=" + req.registry + "&action=prevYear&f1=" + f1 + "&f2=" + f2);
	}

	function thisYear(f1, f2)
	{
		ajaxCommand("?registry=" + req.registry + "&action=thisYear&f1=" + f1 + "&f2=" + f2);
	}

	function prevMonth(f1, f2)
	{
		ajaxCommand("?registry=" + req.registry + "&action=prevMonth&f1=" + f1 + "&f2=" + f2);
	}

	function thisMonth(f1, f2)
	{
		ajaxCommand("?registry=" + req.registry + "&action=thisMonth&f1=" + f1 + "&f2=" + f2);
	}

	function moveChildUp(path)
	{
		app.ajaxCommand("?action=moveChildUp&path=" + path);
	}

	function moveChildDn(path)
	{
		app.ajaxCommand("?action=moveChildDn&path=" + path);
	}

	function processError(message, field)
	{
		processWarning({'message': message, 'field': field, 'severity': 'error'});
	}

	function processWarnings(data)
	{
		app.removeWarnings();
		var x;
		if(data.warnings)
			for(x = 0; x < data.warnings.length; x++)
				processWarning(data.warnings[x]);
	}

	function getWarningsContainer(field)
	{
		var wcId;
		if(field != "")
			wcId = field + "_warnings";
		else
			wcId = "doc_warnings";

		var exists = false;
		$("#" + wcId).each(function(){exists = true;});
		if(!exists)
		{
			if($("#" + field).parent().hasClass("gridCell"))
				$("#" + field).after('<div id="' + wcId + '" class="fieldWarningContainer"></div>');
			else
				$("#" + field).parent().after('<div id="' + wcId + '" class="fieldWarningContainer"></div>');
		}

		return $("#" + wcId);
	}

	function stackPush(val)
	{
		var currentStack = ajaxReadFromSession("CLIENT_SIDE_REGISTRY_STACK");
		if (currentStack)
			 currentStack += "|" + val
		else
			currentStack = val;
		return ajaxWriteToSession("CLIENT_SIDE_REGISTRY_STACK", currentStack);
	}

	function clearStack()
	{
		ajaxWriteToSession("CLIENT_SIDE_REGISTRY_STACK", "");
	}

	function stackPop(onlyPeek)
	{
		var currentStack = ajaxReadFromSession("CLIENT_SIDE_REGISTRY_STACK");
		var result = "";
		if (currentStack && currentStack != null){
			var splitStrings = currentStack.split("|");
			result = splitStrings.pop();
			if (!onlyPeek)
				ajaxWriteToSession("CLIENT_SIDE_REGISTRY_STACK", splitStrings.join("|"));

		}
		return result;
	}

	function processWarning(w)
	{
		processWarning(w, 10)
	}

	function processWarning(w, timeout)
	{
		//log("warning(field: " + w.field + "; severity: " + w.severity + "; message: " + w.message + ")");
		var wc = getWarningsContainer(w.field);
		var wcls = "warningSeverity" + w.severity;
		wc.html('<div class="' + wcls + '">' + w.message + '</div>');
		if(w.field == "")
			wc.css("left", (($(window).width() - wc.width()) / 2 - 10) + "px").idle(timeout * 1000).fadeOut("slow");
		wc.show();
	}

	function setTextAreaHeight(ta)
	{
 		if(ta.scrollHeight > ta.offsetHeight)
 		{
 			var h = ta.scrollHeight + 4;
 			if(h > 200)
 				h = 200;
 			if(h < 30)
 				h = 30;
 			$("#" + ta.id).css("height", h);
 		}
	}

	function setComboAdv(id, foreignTable, foreignTableField, columns, value, width, callbackF, allowNewButton, registryName)
	{
		// NB! only first column in columns is searched
		var representationField = columns[0]['columnName'];

		var representationFields = "";
		var colModel = [{'columnName': foreignTableField, 'label': foreignTableField, 'hide':'true'}];

		$.each(columns, function(index, value){
			representationFields += value['columnName'];
			if (index < columns.length - 1){
				representationFields += ",";
			}
			colModel.push(value)});


		myUrl = baseUrl() + "?action=comboData" +
			"&combot=" + foreignTable +
			"&idfield=" + foreignTableField +
			"&rfield=" + representationFields;

		if (registryName === undefined)
			registryName = foreignTable; // todo sasha: map later

		var newRegCall = null;
		if (allowNewButton)
			newRegCall = "createNewAndSelectInCombo('" + registryName+ "', '" + id +"');";

		$newInput = $('#' + id).clone();
		$newInput.attr('id', id + 'REPR');
		$('#' + id).parent().append($newInput);

		$('#' + id).hide();

		$('#' + id + 'REPR').combogrid({
			createNewCallback: newRegCall,
			createNewCaption: t("New"),
			//debug: true,
			url: myUrl,
			//searchButton:true,
			//okIcon: true,
			//showOn: true,
			width: width + "px",
			colModel: colModel,
			select: function( event, ui ) {
				saveField(id, id, ui.item[foreignTableField]);
				return false;
			}
		});

		function z(){
			ajaxTableRow($('#' + id).val(), foreignTable, foreignTableField,
				function(data)
				{
						$('#' + id + 'REPR').val(data[representationField]);
						if(callbackF){
							callbackF(id, data);
						}
				});
		}
		$('#' + id).val(value);
		$('#' + id).bind("updateCombo", z);
		$('#' + id).trigger("updateCombo");
	}

	function setKeySel(id, foreignTable, foreignTableField, columns, value, width, callbackF, allowNewButton, registryName, canSelect, af)
	{

		/*
		log("id: " + id);
		log("foreignTable: " + foreignTable);
		log("foreignTableField: " + foreignTableField);
		log("columns: ");
		log(columns);
		log("value: " + value);
		log("width: " + width)
		log("callbackF: ");
		log("callbackF");
		log("allowNewButton: " + allowNewButton);
		log("registryName: " + registryName);
		log("canSelect: " + canSelect);
		/* */

		// NB! only first column in columns is searched
		var representationField;
		var additionalDisplayField;
		try
		{
			representationField = columns[0]['columnName'];
			additionalDisplayField = columns[1]['columnName'];	//TODO?
		}
		catch(e)
		{
			log("---------------");
			log(e.message);
			log("id: " + id);
			log("foreignTable: " + foreignTable);
			log("foreignTableField: " + foreignTableField);
			log("columns: ");
			log(columns);
			log("value: " + value);
			log("width: " + width)
			log("callbackF: ");
			log("callbackF");
			log("allowNewButton: " + allowNewButton);
			log("registryName: " + registryName);
			log("canSelect: " + canSelect);
			log("---------------");
			return;
		}

		var representationFields = "";
		var colModel = [{'columnName': foreignTableField, 'label': foreignTableField, 'hide':'true'}];

		$.each(columns, function(index, value){
			representationFields += value['columnName'];
			if (index < columns.length - 1){
				representationFields += ",";
			}
			colModel.push(value)});


		myUrl = baseUrl() + "?action=comboData" +
			"&combot=" + foreignTable +
			"&idfield=" + foreignTableField +
			"&rfield=" + representationFields +
			(af != "" ? "&af=" + af : "");

		if (registryName === undefined)
			registryName = foreignTable; // todo sasha: map later

		var newRegCall = null;
		if (allowNewButton)
			newRegCall = "createNewAndSelectInCombo('" + registryName+ "', '" + id +"');";

		//log("oldInput: " + $('#' + id).length);

		var newInput = $('#' + id).clone();
		newInput.attr('id', id + 'REPR').blur(function(){
			if("" == $(this).val())
			{
				$("#" + id).val("").trigger("updateCombo");
				saveField(id, id, "");
			}
		});

		//log("newInput: '" + id + "'");
		//log(newInput);

		$('#' + id).parent().prepend(newInput);
		$('#' + id).hide().data("canSelect", canSelect);

		$('#' + id + 'REPR').combogrid({
			createNewCallback: newRegCall,
			createNewCaption: t("New"),
			//debug: true,
			url: myUrl,
			//searchButton:true,
			//okIcon: true,
			//showOn: true,
			width: width + "px",
			colModel: colModel,
			select: function( event, ui ) {
				var newId = ui.item[foreignTableField]
				//log("select " + newId);
				saveField(id, id, newId);
				//$('#' + id).val(newId);
				//$('#' + id).trigger("updateCombo");
				return false;
			}
		});

		function z()
		{
			ajaxTableRow($('#' + id).val(), foreignTable, foreignTableField,
				function(data)
				{
					$('#' + id + 'REPR').val(data[representationField]);
					var html = "";
					var v = $('#' + id).val();
					if((v != "0")&&(v != "")&&(v != null))
					{
						var cs = $('#' + id).data("canSelect");
						if(cs)
							html += '<a href="' + baseUrl() +
								'?action=open&registry=' + registryName +
								'&id=' + v + '" target="_blank" tabIndex="-1">';
						html += data[additionalDisplayField];
						if(cs)
							html += "</a>";
					}
					$('#' + id + 'ADDD').html(html);
					if(callbackF)
						callbackF(id, data);
				}
			);
		}

		$('#' + id).bind("updateCombo", z);
		$('#' + id).val(value);
		$('#' + id).trigger("updateCombo");
	}

	function retrieveID(IdToRetrieve)
	{
		if (!checkIfPreparedForRetrieval())
			return;

		var whereToComboIdPeek = stackPop(true);

		var whereToComboId = stackPop(false);
		var mycontextName = stackPop(false);
		var mycontextId = stackPop(false);

		ajaxWriteToSession('retrcombo', whereToComboId);
		ajaxWriteToSession('retrcomboval', IdToRetrieve);

		var opener = window.opener;

		window.close();

		if (!opener || opener.closed)
		{
			// openSpecificDocument(mycontextName, mycontextId);
			// todo sasha: put values into it
		}
		else
		{
			opener.focus();
			opener.updateComboWithRetrievedValue();
		}
	}

	function checkIfPreparedForRetrieval()
	{
		return ("" != stackPop(true));
	}

	function updateComboWithRetrievedValue()
	{
		var whereToComboId = ajaxReadFromSession('retrcombo');
		var IdToRetrieve = ajaxReadFromSession('retrcomboval');

		if (whereToComboId != "" && whereToComboId != "null"){
			ajaxWriteToSession('retrcombo', '');
			ajaxWriteToSession('retrcomboval', '');

			$("#" + whereToComboId).val(IdToRetrieve);
			$("#" + whereToComboId).trigger("updateCombo");

			saveField(whereToComboId, whereToComboId, IdToRetrieve);
			$('#' + whereToComboId).focus();
			$(".combogrid").attr("style", $(".combogrid").attr("style")+ "; display: none;");
		}
	}

	function ajaxReadFromSession(variableName)
	{
		var url = baseUrl() +
			"?action=readFromSession" +
			"&svname=" + variableName;
		var result = "";
	    jQuery.ajax({
			url:url,
			success: function(data) {
					if(data['state'] == "ok")
					{
						result = data.message;
					}
				},
				dataType: "json",
				async: false
	    	});
		return result;
	}

	function ajaxWriteToSession(variableName, variableValue)
	{
		var url = baseUrl() +
			"?action=writeToSession" +
			"&svname=" + variableName +
			"&svval=" + variableValue;

		var result = false;
	    jQuery.ajax({
			url:url,
			success:
				function(data)
				{
					if(data.state == "ok")
					{
						result = true;
					}
				},
				dataType: "json",
				async: false
	    	});
		return result;
	}



	function ajaxTableRow(value, foreignTable, foreignTableField, callbackF)
	{
		var url = baseUrl() +
			"?action=ajaxTableRow" +
			"&cvalue=" + value +
			"&ctable=" + foreignTable +
			"&ctablekey=" + foreignTableField;

		$.get(url,
			function(data)
			{
				if(data.state == "ok")
				{
					callbackF(data.row);
				}
			}, "json");
	}

	function ajaxTableValue(value, foreignTable, foreignTableField, representationField, callbackF)
	{
		var url = baseUrl() +
			"?action=ajaxTableValue" +
			"&cvalue=" + value +
			"&ctable=" + foreignTable +
			"&ctablekey=" + foreignTableField +
			"&ctablefield=" + representationField;

		$.get(url,
			function(data)
			{
				if(data.state == "ok")
				{
					callbackF(data.message);

				}
			}, "json");
	}

 	function openRegistry(r)
 	{
 		document.location = baseUrl() + "?registry=" + r;
 	}

	function checkboxclick(b){
		saveField(b.id, b.id, b.checked ? "1":"0");
	}

	function fieldOnBlur(b)
	{
		log("fieldonblur " + b.id);
		saveField(b.id, b.id, b.value, null);
	}

	function saveField(htmlId, fullpath, value)
	{
		saveField(htmlId, fullpath, value, null);
	}

	function saveField(htmlId, fullpath, value, callAfterSave)
	{
		log("saving field " + htmlId);
		app.removeWarnings(fullpath);
		var url = baseUrl() + "?action=saveField&path=" + fullpath + "&v=" + encodeURIComponent(value);
		rq = new Object();
		rq.action = "saveField";
		rq.path = fullpath;
		rq.v = value;
		app.markFieldAsSaving(htmlId);
		app.nowHasUnsavedChanges(true);
		$.post(baseUrl(), rq,
			function(data)
			{
				if(data.state == "ok")
					app.unmarkFieldAsSaving(htmlId);
				handleAjaxResponce(data, htmlId);
				if(callAfterSave)
					callAfterSave();
				if(app.afterSaveField)
					app.afterSaveField(data);
			}, "json");
	}

	function updateOtherFields(arr)
	{
		try
		{
			var x;
			for(x = 0; x < arr.length; x++)
			{
				var o = arr[x];
				var c = $("#" + o.id + ":checkbox");
				if(c.length == 1)
				{
					if(o.value == 1)
						c[0].checked = true;
					else
						c[0].checked = false;
				}
				else
				{
					var ct = $("#" + o.id);
					var v1 = ct.val();
					ct.val(o.value);
					if(o.id == document.activeElement.id)
						ct[0].select();
				}

				$("div#" + o.id).html(o.value);
				$("#" + o.id).trigger("updateCombo");

				try
				{
					if(obj.onFieldUpdated)
						obj.onFieldUpdated(o.id, o.value);
				}catch(e1){}
			}
		}catch(e){}
	}

	function selectActiveField()
	{
		return;

		try
		{
			var url = "" + document.location;
			var a = url.split("#");
			if(a.length > 1)
			{
				document.getElementById(a[1]).focus();
			}
		}catch(e){log(e.message)}


		var v = $("input#" + document.activeElement.id);
			if(v.size() == 1)
				if(!$(v[0]).hasClass("datepicker"))
					v[0].select();
		v = $("textarea#" + document.activeElement.id);
			if(v.size() == 1)
				v[0].select();

	}

	function handleAjaxResponce(data, ctrlId)
	{
		processWarnings(data);

		if(data.state == "error")
		{
			log(data);
			processError(data.message, ctrlId);
			if(data.message != "")
				app.alert(data.message);
		}

		if(data.state == "ok")
		{
			if(data.update)
				updateOtherFields(data.update);
			if(data.reloadPage)
				reloadPage();
			if(data.reload)
				reopenDocument();
			if(data.goback)
				openDocumentList();
		}
	}

	function logout()
 	{
 		document.location = baseUrl() + "?auth=logout";
 	}

 	function openUserProfile()
 	{
 		document.location = baseUrl() + "?registry=profile";
 	}

	function clientSideCheck()
	{
		boxedBoolean = new Object();
		boxedBoolean.value= true;
		$("body").trigger("savingClientSideCheck", boxedBoolean);

		return boxedBoolean.value;
	}

	function onNonEmptyChange()
	{
		if ($(this).val()!="" && $(this).val()!=null){
			$(this).unbind('keyup', onNonEmptyChange);
			$(".validationfail", $(this).parent()).remove();
			$(this).removeClass("notfilled");
		}
	}

	function invalidate(o)
	{
		$(o).addClass("notfilled");
		$(".validationfail", $(o).parent()).remove();
		$(o).parent().append('<div class="validationfail">' + $(o).attr("err") + "</div>");
		$(o).bind('keyup',onNonEmptyChange);
		$(o).focus();
	}

	function savingClientSideCheckShouldBeNotEmpty(e, b)
	{
		$(".shouldbenotempty").removeClass("notfilled");
		$(".shouldbenotempty").each(function(){
			if ($(this).val()=="" || $(this).val()==null){
				b.value = false;
				invalidate(this);
				return false; //each() should not continue
			} else {
				return true; //each() should  continue
			}
		});
		return b.value; // no further call to savingClientSideCheck handlers if b.value is false
	}

	function deleteDocument()
	{
		if(obj.__isNotSaved)
			return app.alert(t("cant delete new document"));
		app.confirm(msg["Delete document?"], function(){
			ajaxCommand(baseUrl() + "?action=delete&path=" + contextName());
		})
	}

	function newVersion()
	{
		ajaxCommand(baseUrl() +
			"?action=newVersion" +
			"&path=" + contextName());
	}

	function lockDocument()
	{
		$("#rightPanelLockButtonLink").attr("href", "JavaScript:void(0);");
		$("#rightPanelLockButtonText").html(t("locking"));
		if(app.waitForSavingFields(lockDocument))
			return;
		app.progressMsg(t("locking"));
		ajaxCommand(baseUrl() +
				"?action=lock" +
				"&path=" + contextName());
	}

	function unlockDocument()
	{
		app.confirm(t("unlock document"), function(){
			$("#rightPanelLockButtonLink").attr("href", "JavaScript:void(0);");
			$("#rightPanelLockButtonText").html(t("unlocking"));
			app.progressMsg(t("unlocking"));
			ajaxCommand(baseUrl() +
					"?action=unlock" +
					"&path=" + contextName());
		})
	}

	function saveDocument()
	{
		app.saveDocument();
	}

	function focusFirstWarning(data)
	{
		if(data.warnings)
		{
			for(x = 0; x < data.warnings.length; x++)
				if(data.warnings[x].field != "")
				{
					$("#" + data.warnings[x].field).focus();
					return;
				}
		}
	}

	function setFieldToDefault(ctrlid)
	{
		saveField(ctrlid, ctrlid, setup.SPECIALVALUE_DEFAULT);
		$("#" + ctrlid).focus();
	}

	function t(s)
	{
		if(undefined == msg[s])
			return s;
		else
			return msg[s];
	}

	//--------- controls ---------


	function checkbox(obj, field)
	{
		var id = obj.fullpath + "_" + field;
		return '<input id="' + id + '" ' +
			'onclick="JavaScript:checkboxclick(this);" ' +
			(obj[field] == 1 ? "checked " : "") +
			'type="checkbox"/>';
	}

	function combo(obj, field, options, cls, onblur)
	{
		if(onblur == undefined)
			onblur = "JavaScript:fieldOnBlur(this);";
		if(cls == undefined)
			cls = "gridText";
		var id = obj.fullpath + "_" + field;

		return "<select id=\"" + id + "\" class=\"" + cls + "\" " +
			"onblur=\"" + onblur + "\" onchange=\"" + onblur + "\" >" + options + "</select>";		//chosen test onchange=\"" + onblur + "\"
	}

	function textbox(obj, field, value, cls, onblur)
	{
		if(onblur == undefined)
			onblur = "JavaScript:fieldOnBlur(this);";
		if(cls == undefined)
			cls = "gridText";
		var id = obj.fullpath + "_" + field;

		return "<input type=\"text\" id=\"" + id + "\" value=\"" + unnull(value) + "\" " +
			"class=\"" + cls + "\" onblur=\"" + onblur + "\"/>";
	}

	function datepicker(obj, field, value, cls, onblur)
	{
		if(onblur == undefined)
			onblur = "JavaScript:fieldOnBlur(this);";
		if(cls == undefined)
			cls = "gridText";
		var id = obj.fullpath + "_" + field;

		return "<input type=\"text\" id=\"" + id + "\" value=\"" + unnull(value) + "\" " +
			"class=\"" + cls + " datepicker\" onblur=\"" + onblur + "\"/>";
	}

	function disabledtextbox(obj, field, value, cls)
	{
		if(cls == undefined)
			cls = "gridText";
		var id = obj.fullpath + "_" + field;

		return "<input type=\"text\" id=\"" + id + "\" value=\"" + unnull(value) + "\" " +
			"class=\"" + cls + "\" disabled/>";
	}

	function comboAdv(fullpath){
		return "<input id=\"" + fullpath + "\"/>";
	}

	function readonlytext(htmlID){
		return "<div id=\"" + htmlID + "\"></div>";
	}

	function addChild(collection, func)
	{
		var url = baseUrl() +
			"?action=addChild" +
			"&path=" + collection;

		$.get(url,
			function(data)
			{
				if(data.state == "ok")
				{
					app.nowHasUnsavedChanges(true);
					func(data.obj, true, data.path);
					updateOtherFields(data.update);
				}
			}, "json");
	}

	function delRow(path)
	{
		app.confirm(t("Delete row?"), function(){
			var url = baseUrl() +
				"?action=deleteChild" +
				"&path=" + path;

			log("delRow: " + path);
			$("#" + path).addClass("waitingToDelete");
			$.get(url,
				function(data)
				{
					if(data.state == "ok")
					{
						$("#" + path).remove();

						try
						{
							onRowDeleteSuccess(path);
						}catch(e){}
					}
					else
					{
						app.alert(data.message);
						$("#" + path).removeclass("waitingToDelete");
					}
					updateOtherFields(data.update);

				}, "json");
		});
	}

	function createNewAndSelectInCombo(registryName, returnToId)
	{
		stackPush(req.id);
		stackPush(req.registry);
		stackPush(returnToId);

		openSpecificDocumentInNew(registryName, "");
	}

	function newDocument(registryName)
	{
		if(setup.DocOpenInTab)
			openDocumentInNewTab("");
		else
			openSpecificDocument(registryName, "");
	}

	function openSpecificDocument(registryName, id)
	{
		var myUrl = prepareUrl(registryName, id);
		document.location = myUrl;
	}

	function openSpecificDocumentInNew(registryName, id)
	{
		var options =
			'width=400,height=200,toolbar=yes,' +
			'location=yes,directories=yes,status=yes,menubar=yes,scrollbars=yes,copyhistory=yes,'+
			'resizable=yes';
		var myUrl = prepareUrl(registryName, id);

		options = 'width=800,height=600';
		window.open(myUrl,'mywindow', options);
	}

	function prepareUrl(registryName, id)
	{
		return baseUrl() +
			"?action=open" +
			"&registry=" + registryName +
			"&id=" + id;
	}

	function openDocumentInNewTab(id)
	{
		log("openDocumentInNewTab(" + id + ")");
		if($("#openerForm").length == 0)
			$("body").append('<form action="' + setup.INSTANCE_WEB + '" id="openerForm" method="GET" target="_blank"></form>');
		try
		{
			$("#openerForm").html('<input type="hidden" name="action" value="open"/>' +
					'<input type="hidden" name="registry" value="' + req.registry + '"/>' +
					'<input type="hidden" name="id" value="' + id + '"/>')[0].submit();
			return;
		}catch(e){log(e.message);}
	}

	function openDocumentList()
	{
		if(setup.DocOpenInTab)
		{
			try{
				window.opener.focus();
			}catch(e){log(e.message);}
			window.close();
		}
		else
			document.location = "index.php?registry=" + req.registry;
	}

	function openDocument(id)
	{
		if(setup.DocOpenInTab)
			openDocumentInNewTab(id);
		else
			openSpecificDocument(req.registry, id);
	}

	function reopenDocument()
	{
		app.reopenDocument();
	}

	function showLog()
	{
		document.location = "?action=showlog&registry=" + req.registry + "&id=" + req.id;
	}

	function reloadPage()
	{
		app.reloadPage();
		/*app.reloadCurrentDocumentGridRow();
		//window.onbeforeunload = null;
		var url = "" + document.location;
		var a = url.split("#");
		try{url = a[0] + "#" + document.activeElement.id}catch(e){}
		document.location.assign(url);
		document.location.reload();*/
	}

	function baseUrl()
	{
		return setup.INSTANCE_WEB;
	}

	function openLink(l)
	{
		var f = $("#linkOpenerForm");
		if(f.length == 0)
		{
			$("body").append('<form action="" target="_blank" id="linkOpenerForm" method="get"></form>');
			f = $("#linkOpenerForm");
		}
		var ff = f[0];
		ff.action = l;
		ff.submit();
	}

	function getCtrlRow(id)
	{
		return $(".ctrlRow_" + obj.fullpath + "_" + id);
	}

	//=======================================================================================================

	function contextName()
	{
		return app.contextName();
	}

	function ajaxCommand(s)
	{
		return app.ajaxCommand(s);
	}

	function removeWarnings(field)
	{
		app.removeWarnings(field);
	}

	function mainMenu()
	{
		app.mainMenu();
	}
