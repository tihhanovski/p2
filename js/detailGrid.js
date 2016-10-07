	var detailGrid =
	{
		"resize": function(model)
		{
			var w = 0;
			var h = 0;
			var cont = "#" + model.name + "GridContainer";
			$(cont).children(".gridHead").find(".gridHeadCell:visible").each(function(){
				w += $(this).width() + 10;
				h = Math.max($(this).height(), h);
			});
			$(cont).width(w).find(".gridHead").css("height", h + "px").find(".gridHeadCell:visible").css("height", h + "px");
		},

		"build": function (container, model)
		{
			model.fullpath = obj.fullpath + "_" + model.name;
			model.containerId = container;
			if(model.rowsChangeable === undefined)
				model.rowsChangeable = true;
			if(model.rowsAppendable === undefined)
				model.rowsAppendable = true;

			var x;
			var html = '';

			if(model.leftCaption)
			{
				html += '<div class="formLabel">' + model.caption + '</div>' +
					'<div class="formInputContainer">';
			}
			else
				if(model.caption)
					html += '<b>' + model.caption + '</b>';

			html += '<div id="' + model.name + 'GridContainer" class="ui-corner-all gridContainer" style="overflow: none;">';
			html += '<div class="gridHead">';

			html += this.colHeader({"name": "", "caption": "&nbsp;", "width": "1"});
			for(x = 0; x < model.cols.length; x++)
				html += this.colHeader(model.cols[x]);
			if(model.rowsChangeable)
				html += this.colHeader({"name": "", "caption": "&nbsp;", "width": "1"});

			html += '</div>' +
				'<div id="' + model.fullpath + '"></div><div style="clear: both;">';


			if(model.rowsChangeable && model.rowsAppendable)
				html += '<a href="JavaScript:void(0);" ' +
					'class="addGridRowButton"><img src="' + setup.WFW_WEB + '/ui/img/16/add.png" border="0"/>' + t("Add row") + '</a>';

			html += '</div></div>';

			if(model.leftCaption)
				html += '</div>';

			$(container).html(html).find(".addGridRowButton").click(function(){
				//addChild(model.fullpath, model.addRow);
				model.startAddRow();
			});

			model.startAddRow = function()
			{
				addChild(this.fullpath, this.addRow);
			}

			model.addRow = function(obj, visible, path)
			{
				if(!('detailGridCanChange' in obj))
					obj.detailGridCanChange = true;
				if(obj.detailGridCanChange == null)
					obj.detailGridCanChange = true;

				var canChange = model.rowsChangeable && obj.detailGridCanChange;

				if(visible)
					stl = '';
				else
					stl = 'style="display: none;"';
				if(!obj.todelete)
				{
					var x;
					var html = '<div id="' + obj.fullpath + '" ' + stl + ' class="gridRow">' +
						'<div class="gridCell gridCellW1"></div></div>';
					$("#" + path).append(html);

					this.focusedControl = null;
					for(x = 0; x < model.cols.length; x++)
						model.cell(model.cols[x], obj);

					try
					{
						if(!this.loading)
							$("#" + obj.fullpath).find("select:visible,input:visible")[0].focus();//input:text:visible:first
					}catch(e){}
					if(canChange)
						$("#" + obj.fullpath).append(getGridDelRowFieldDiv(obj.fullpath));
				}
			}

			model.cell = function(col, data)
			{
				var html = '<div id="' + data.fullpath + '_' + col.name + 'CellContainer" ' +
					'class="gridCell gridCellW' + col.width + ' ' + (col.align ? col.align : "") + '">' +
					'</div>';
				$("#" + data.fullpath).append(html);
				col.dataHandler(data, col);
			}

			this.addRows(obj, model);
			detailGrid.resize(model);
		},

		"refill": function(obj, model)
		{
			this.clear(obj, model);
			this.addRows(obj, model);
		},

		"clear": function(obj, model)
		{
			model.loading = true;
			var rowsC = obj.fullpath + "_" + model.name;
			$("#" + rowsC).html('');
			model.loading = false;
		},

		"addRows": function(obj, model)
		{
			model.loading = true;
			try
			{
				var x;
				var rows = getDataObject(obj, model.name);
				var rowsC = obj.fullpath + "_" + model.name;
				if($.isArray(rows))
					for(x = 0; x < rows.length; x++)
						model.addRow(rows[x], true, rowsC);
			}catch(e){log(e.message);}
			model.loading = false;
		},

		"colHeader": function(col)
		{
			if(col.control && !col.dataHandler)
			{
				if(col.control == "select")
					col.dataHandler = detailGrid.select;
				if(col.control == "textbox")
					col.dataHandler = detailGrid.textBox;
				if(col.control == "datepicker")
					col.dataHandler = detailGrid.datePicker;
				if(col.control == "checkbox")
					col.dataHandler = detailGrid.checkBox;
				if(col.control == "static")
					col.dataHandler = detailGrid.staticBox;
				if(col.control == "double")
					col.dataHandler = detailGrid.doubleBox;
				if(col.control == "keysel")
					col.dataHandler = detailGrid.keysel;
				if(col.control == "reorder")
					col.dataHandler = detailGrid.reorder;
				if(col.control == "textboxautocomplete")
					col.dataHandler = detailGrid.textBoxAutocomplete;

				if(!col.dataHandler)
					col.dataHandler = detailGrid.staticBox;
			}
			return '<div class="gridHeadCell gridCellW' + col.width + ' ' + (col.align ? col.align : "") + '">' + col.caption + '</div>';
		},

		"reorder": function(data, col)
		{
			var html = '<div style="padding-top: 4px;"><a href="JavaScript:moveChildUp(\'' + data.fullpath + '\');">' + img('up.png', '') + '</a>' +
				'<a href="JavaScript:moveChildDn(\'' + data.fullpath + '\');">' + img('dn.png', '') + '</a></div>';
			$('#' + data.fullpath + '_' + col.name + 'CellContainer').html(html);
		},

		"keysel": function(data, col)
		{
			var html = '<input  class="keySelInput" id="' + data.fullpath + '_' + col.name + '" type="text"></input>' +
				'<span id="' + data.fullpath + '_' + col.name + 'ADDD" class="keySelNameField"></span>';
			$('#' + data.fullpath + '_' + col.name + 'CellContainer').html(html);
			setKeySel(data.fullpath + '_' + col.name,
					col.setup.cls, col.setup.id, col.setup.cols,
					data[col.name], $(window).width - 300, null,
					col.setup.canUpdate, col.setup.cls, col.setup.canSelect);
			if(!data.detailGridCanChange)
				$('#' + data.fullpath + '_' + col.name + 'REPR').attr("disabled", true);
		},

		"datePicker": function(data, col)
		{
			if(!data.detailGridCanChange)
				return detailGrid.staticBox(data, col);
			$('#' + data.fullpath + '_' + col.name + 'CellContainer').html(datepicker(data, col.name, data[col.name]));
			setDatePicker('#' + data.fullpath + '_' + col.name);
		},

		"textBox": function(data, col)
		{
			if(!data.detailGridCanChange)
				return detailGrid.staticBox(data, col);
			$('#' + data.fullpath + '_' + col.name + 'CellContainer').html(textbox(data, col.name, data[col.name]));
		},

		"textBoxAutocomplete": function(data, col)
		{
			if(!data.detailGridCanChange)
				return detailGrid.staticBox(data, col);
			$('#' + data.fullpath + '_' + col.name + 'CellContainer').html(textbox(data, col.name, data[col.name]));
			$('#' + data.fullpath + '_' + col.name).autocomplete({lookup: col.selectOptions});
		},

		"doubleBox": function(data, col)
		{
			if(!data.detailGridCanChange)
				return detailGrid.staticBox(data, col);
			$('#' + data.fullpath + '_' + col.name + 'CellContainer').html(textbox(data, col.name, data[col.name], 'gridFloat'));
			setNumericDbl('#' + data.fullpath + '_' + col.name);
		},

		"staticBox": function(data, col)
		{
			var html = '<div id="' + data.fullpath + "_" + col.name + '" class="gridStaticValue">' + unnull(data[col.name]) + '</div>';

			$('#' + data.fullpath + '_' + col.name + 'CellContainer').html(html);
		},

		"select": function(data, col)
		{
			var id = '#' + data.fullpath + '_' + col.name;
			$(id + 'CellContainer').html(combo(data, col.name, col.selectOptions));
			$(id).val(data[col.name]);
			//$(id).chosen();
		},

		"checkBox": function(data, col)
		{
			var c = $('#' + data.fullpath + '_' + col.name + 'CellContainer').html(checkbox(data, col.name));
			if(!data.detailGridCanChange)
				c.attr("disabled", true);
		}
	}