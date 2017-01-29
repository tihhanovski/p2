
	function setupGrid(grid)
	{
		if(!grid.containerId)
			grid.containerId = "#gridContainer";
		grid.query = "";

		grid.filterHeightDelta = 60;

		grid.run = function()
		{
			this.makeGrid();
			this.loadPage();
		}

		grid.getUrl = function()
		{
			return this.url +
				"&page=" + this.currentPage +
				"&rp=" + this.rp +
				"&sortname=" + this.sortname +
				"&sortorder=" + this.sortorder +
				this.appendToUrl();
		}

		grid.getRowContents = function(grid, row)
		{
			var cm = grid.colModel;
			var html = '';

			var fh = "";
			if(grid.fixedHeight != 0)
				fh = "height: " + grid.fixedHeight + "px;";

			for(x = 0; x < cm.length; x++)
			{
				var c = cm[x];
				if(c.visible)
				{
					html += '<div class="mGridCell" ' +
						'style="width: ' + c.width + 'px; text-align: ' + c.align + '; ' + fh + '">' +
					grid.handleCell(c.handler, row, x) + '</div>';
				}
			}
			return html;
		}

		grid.refillRow = function(grid, row)
		{
			var gr = $("#mgr_" + row.id);
			var rc = grid.getRowContents(grid, row);
			gr.html(rc);
		}

		grid.updateRowsStatus = function()
		{
			$("#gridStats3").html("" + this.addedRows + " / " + this.totalRows);
		}

		grid.clearOtherStats = function()
		{
			$(".gridStats").each(function(){$(this).html("");});
		}

		grid.updateOtherStats = function()
		{
			this.clearOtherStats();
			if(this.stats)
			{
				gg = this;
				$(".gridStats").each(function(){
					try{
						var f = this.id.split("_")[1];
						if(gg.stats[f])
							$(this).html(gg.stats[f]);
					}catch(e){}
				})
			}
		}

		grid.prependRow = function(grid, row)
		{
			//log("prependRow");
			//log(row);
			var cnt = $("#mgr_" + row.id).length;
			if(cnt > 0)
			{
				//log("row exists, refilling");
				this.refillRow(grid, row);
				return;
			}
			try{
				//log("row does not exist");
				this.addedRows++;
				this.updateRowsStatus();

				var fh = "";
				if(grid.fixedHeight != 0)
					fh = "height: " + grid.fixedHeight + "px;";

				var html = '<div class="mGridRow ' + grid.getStyle(row) + '" id="mgr_' + row.id + '"' + (fh != '' ? ' style="' + fh + '"' : '') + '>' +
					grid.getRowContents(grid, row) +
					'</div>';
				$("#gridContents").prepend(html);

				var tw = 0;

				var h = 0;
				$("#mgr_" + row.id).children().each(function(){
							if(h < $(this).height()) 
								h = $(this).height();
							tw += $(this).width;
						});

				if(this.fixedHeight == 0)
				{
					$("#mgr_" + row.id).height(10 + h);
					$("#mgr_" + row.id).children().height(h);
				}

				$("#mgr_" + row.id).css("width", tw + "px");
				this.setRowEventHandlers(row);

			}catch(e)
			{
				log(e.message);
			}
		}

		grid.setActiveRow = function(ctrlId)
		{
			var a = ctrlId.split("_");
			var id = a[1];
			var ctrl = $("#" + ctrlId);
			//log("set active row " + ctrlId + "(id = " + id + ")");

			$(".activeGridRow").removeClass("activeGridRow");
			ctrl.addClass("activeGridRow");

			var delta = $(".activeGridRow").offset().top + $(".activeGridRow").height() - $("#gridScroller").offset().top - $("#gridScroller").height();
			//log("d: " + delta);
			if(delta > 0)
				$("#gridScroller").scrollTop($("#gridScroller").scrollTop() + delta);

			var delta = $(".activeGridRow").offset().top - $("#gridScroller").offset().top;
			//log("d: " + delta);
			if(delta < 0)
				$("#gridScroller").scrollTop($("#gridScroller").scrollTop() + delta);
		}

		grid.setRowEventHandlers = function(row)
		{
			var gg = this;
			$("#mgr_" + row.id)
				.click(function(){gg.setActiveRow(this.id);})
				.dblclick(function(){gg.onRowClick(this.id);})
		}

		grid.reloadNewRow = function(id)
		{
			var url = this.getUrl() + "&reloadRow=" + id;
			var gg = this;
			$.get(url,
					function(data)
					{
						var x;
						if(data.rows)
							for(x = 0; x < data.rows.length; x++)
								gg.prependRow(gg, data.rows[x]);
					},
					"json"
			);
		}

		grid.reloadRow = function(id)
		{
			$("#mgr_" + id).addClass("mgridReloadingRow");
			var url = this.getUrl() + "&reloadRow=" + id;
			var gg = this;
			$.get(url,
					function(data)
					{
						var x;
						if(data.rows)
						{
							for(x = 0; x < data.rows.length; x++)
								gg.refillRow(gg, data.rows[x]);
							$(".mgridReloadingRow").removeClass("mgridReloadingRow");
						}
					},
					"json"
			);
		}

		grid.loadStatsOnly = function()
		{
			this.loadingPage = true;
			var url = this.getUrl();
			if((this.query != "")&&(this.sortname != ""))
				url += "&qtype=" + this.sortname +
				"&query=" + this.query;
			this.setStatusLoading();
			var gg = this;
			$.get(url,
					function(data)
					{
						$("#gridStats3").html("");
						gg.totalRows = data.total;
						gg.addedRows = $(".mGridRow").length;
						gg.stats = data.stats;
						gg.updateOtherStats();
						gg.updateRowsStatus();
						gg.loadingPage = false;
					},
					"json"
			);
		}

		grid.setStatusLoading = function()
		{
			$("#gridStats3").html('<img src="' + setup.WFW_WEB + 'ui/img/16/progress.gif" border="0" class="mGridBottomIcon" />' + t("Loading..."));
		}

		grid.loadPage = function()
		{
			this.loadingPage = true;
			if(this.currentPage * this.rp > this.totalRows)
				return;
			this.currentPage++;

			var url = this.getUrl();

			if((this.query != "")&&(this.sortname != ""))
				url += "&qtype=" + this.sortname +
				"&query=" + this.query;
			this.setStatusLoading();
			var gg = this;
			$.get(url,
					function(data)
					{
						$("#gridStats3").html("");
						gg.totalRows = data.total;
						gg.stats = data.stats;
						var x;
						if(data.rows)
							for(x = 0; x < data.rows.length; x++)
								gg.addRow(gg, data.rows[x]);
						gg.updateOtherStats();
						gg.loadingPage = false;
						if(data.filterOnStartup)
							gg.showFilterOnStartup(data.filterOnStartup);
						if(data.filterCaption)
							gg.setFilterCaption(data.filterCaption);
					},
					"json"
			);
		}

		grid.showFilterOnStartup = function(v)
		{
			if(v == 1)
			{
				var url = document.location.toString().split("#")[0];
				var ref = document.referrer.split("#")[0];
				if(ref != url)
					this.toggleFilter();
			}
		}

		grid.cancelFilter = function()
		{
			$.get(setup.INSTANCE_WEB + "?action=cancelFilter&registry=" + req.registry,
				function(data)
				{
					if(data.state == "ok")
						grid.toggleFilter();
				},
				"json");
		}

		grid.applyFilter = function()
		{
			if(app.waitForSavingFields(this.applyFilter))
				return;
			$.get(setup.INSTANCE_WEB + "?action=applyFilter&registry=" + req.registry,
				function(data)
				{
					if(data.state == "ok")
						reloadPage();
				},
				"json");
		}

		grid.emptyFilter = function()
		{
			$.get(setup.INSTANCE_WEB + "?action=emptyFilter&registry=" + req.registry,
				function(data)
				{
					if(data.state == "ok")
						reloadPage();
				},
				"json");
		}

		grid.appendToUrl = function()
		{
			return "";
		}

		grid.handleCell = function(h, row, col)
		{
			try
			{
				var v = row.cell[col];
				switch(h)
				{
					case "":
						return unnull(v);
					case "selectionbox":
						return '<img src="' + grid.getSelectedIcon(v) + '" border="0" width="16" height="16" ' +
							'onclick="' + grid.getSelectedIconFunction(v, row.id) + '" ' +
							'id="rowselected' + row.id + '"/>';
					case "icon":
						return '<img src="' + v + '" border="0"/>';
					case "closedicon":
						return '<img src="' + setup.WFW_WEB + 'ui/img/16/closed-' + v + '.png" border="0"/>';
					case "lockbox":
						return '<img src="' + setup.WFW_WEB + 'ui/img/16/lock-' + v + '.png" border="0"/>';
					case "checkbox":
						return '<img src="' + setup.WFW_WEB + 'ui/img/16/check-' + v + '.png" border="0"/>';
					case "state":
						return '<img src="' + setup.WFW_WEB + 'ui/img/16/state-' + v + '.png" border="0"/>';
					case"comments":
					{
						if(v == 0)
							return '<img src="' + setup.WFW_WEB + 'ui/img/16/z.png" border="0"/>';
						else
							return '<img src="' + setup.WFW_WEB + 'ui/img/16/comments.png" border="0"/>';
					}
				}
				return v;
			}catch(e)
			{
				return "";
			}
		}

		grid.getSelectedIcon = function(m)
		{
			return setup.WFW_WEB + 'ui/img/16/selected-' + m + '.png';
		}

		grid.getSelectedIconFunction = function(m, id)
		{
			return 'JavaScript:grid.' + (m && m > 0 ? 'un' : '') + 'setSelected(event, ' + id + ');'
		}

		grid.selectionMethod = function(m, id)
		{
			var tgtIcon = $("#rowselected" + id);
			tgtIcon.hide("clip");
			log(m + 'Selected: ' + id);
			$.get(setup.INSTANCE_WEB + "?action=" + m + "Selected&registry=" + req.registry + "&id=" + id, function(data)
			{
				log("id: " + id);
				log(data);
				if(data.state == "ok")
					tgtIcon
						.attr("src", grid.getSelectedIcon(data.message))
						.attr("onclick", grid.getSelectedIconFunction(data.message, id));
				else
					grid.reloadRow(id);

				tgtIcon.show("clip");
			}, "json")
		}

		grid.setSelected = function(event, id)
		{
			event.stopPropagation();
			grid.selectionMethod("set", id);
		}

		grid.unsetSelected = function(event, id)
		{
			event.stopPropagation();
			grid.selectionMethod("unset", id);
		}

		grid.onRowClick = function(ctrlId)
		{
			var a = ctrlId.split("_");
			this.clickRow(a[1]);
		}

		grid.clickRow = function(id)
		{
			openDocument(id);
		}

		grid.styleColumn = -1;

		grid.getStyle = function(row)
		{
			if(this.styleColumn >= 0)
				return row.cell[this.styleColumn];
			return "";
		}

		grid.addRow = function(grid, row)
		{
			var cnt = $("#mgr_" + row.id).length;
			if(cnt > 0)
				return;
			try{
				this.addedRows++;
				this.updateRowsStatus();
				var cm = this.colModel;
				var x;
				var tw = 0;
				var fh = "";
				if(this.fixedHeight != 0)
					fh = "height: " + this.fixedHeight + "px;";

				var html = '<div class="mGridRow ' + grid.getStyle(row) + '" id="mgr_' + row.id + '"' + (fh != '' ? ' style="' + fh + '"' : '') + '>';
				for(x = 0; x < cm.length; x++)
				{
					var c = cm[x];
					if(c.visible)
					{
						html += '<div class="mGridCell" ' +
							'style="width: ' + c.width + 'px; text-align: ' + c.align + '; ' + fh + '">' +
						this.handleCell(c.handler, row, x) + '</div>';
						tw += Math.round(c.width) + 12;
					}
				}
				html += '</div>';
				$("#gridContents").append(html);
				if(this.fixedHeight == 0)
				{
					var h = 0;
					$("#mgr_" + row.id).children().each(function(){if(h < $(this).height()) h = $(this).height()});
					$("#mgr_" + row.id).height(10 + h);
					$("#mgr_" + row.id).children().height(h);
				}

				$("#mgr_" + row.id).css("width", tw + "px");
				this.setRowEventHandlers(row);
			}catch(e)
			{
				log(e.message);
			}
		}

		grid.doFind = function(s)
		{
			this.query = s;
			this.init();
			this.loadPage();
		}

		grid.sortBy = function(s)
		{
			if(this.sortname == s)
			{
				if(this.sortorder == "asc")
					this.sortorder = "desc";
				else
					this.sortorder = "asc";
			}
			else
			{
				this.sortname = s;
				this.sortorder = "asc";
			}
			this.init();
			this.loadPage();
		}

		grid.reload = function()
		{
			this.initVars();
			$("#gridContents").html("");
			this.loadPage();
		}

		grid.getHeader = function()
		{
			var html = '';
			var cm = this.colModel;
			var w = 100;
			for(x = 0; x < cm.length; x++)
			{
				var c = cm[x];
				if(c.name == "##style")
					this.styleColumn = x;
				else
				{
					if(this.setup != undefined)
						c.visible = (this.setup[c.name] != 0);
					if(c.visible)
					{
						w += Math.round(c.width) + 11;
						var cls = "mGridHeadCell";
						var sortHtml = "";
						if(c.sortable)
						{
							cls += " mGridCellSortable";
							sortHtml = ' onclick="JavaScript:grid.sortBy(\'' + c.name + '\');"';
						}
						var caption = c.display;
						if(c.name == this.sortname)
						{
							cls += " mGridHeaderCellSorted";
							if(this.sortorder == "desc")
								caption += '<div class="mGridSortIndicator">&uarr;</div>';	//&uarr;
							else
								caption += '<div class="mGridSortIndicator">&darr;</div>';	//&darr;
						}
						html += '<div id="gh_' + c.name + '" class="' + cls + '" ' +
							'style="width: ' + c.width + 'px; text-align: ' + c.align + '"' +
							sortHtml + '>' + caption + '</div>';
					}
				}
			}
			html = '<div class="mGridHead" id="gridHeader">' +
				'<div id="gridHeaderContents" style="width: ' + w + 'px">' +
				html +
				'<div style="width: 200px; float: left; height: 10px;"></div>' +
				'</div></div><div class="mGridHeadPlaceholder"></div>';
			return html;
		}
		grid.loadingPage = false;

		grid.doScroll = function()
		{
			$("#gridHeader").scrollLeft($("#gridScroller").scrollLeft());
			var h = $("#gridScroller").height() + 100;
			var t = $("#loadMore").position().top + $("#loadMore").height();
			if(h > t / 3)
				if(!this.loadingPage)
				{
					this.loadPage();
				}
		}

		grid.setFilterCaption = function(fc)
		{
			if(!this.hasFilteredFields)
				return;
			var html = '';
			for (var i = 0; i < fc.length; i++)
				if(fc[i] != "")
					html += '<span class="gridFilterCaptionItem">' + fc[i] + '</span>';
			$(".gridFilterCaption").html(html == '' ? t("Filter") : html);
		}

		grid.getFooterContents = function()
		{
			return (this.hasFilteredFields ?
					'<div id="gridFilterButton" class="mGridFooterPanel pointerCursor">' +
					'<img src="' + setup.WFW_WEB + 'ui/img/16/funnel.png" border="0" alt="' + t('Filter') + '" class="mGridBottomIcon" />' +
					'<div class=\"gridFilterCaption gridFilterCaptionShort\">' + t("Filter") + '</div>' +
				'</div>': '') +
				'<div class="mGridFooterPanel gridSetupColumns" style="cursor: pointer;">' +
				'<img src="' + setup.WFW_WEB + 'ui/img/16/eye.png" border="0" alt="' + t('Visible columns') + '" class="mGridBottomIcon"/>' + t('Visible columns') +
				'</div>' +
				'<div id="gridFind" class="mGridFooterPanel"><input type="text" id="gridFindBox" value="' + quote(this.query) + '"/></div>' +
				'<div id="gridStats" class="mGridFooterPanel"></div>' +
				'<div id="gridStats3" class="mGridFooterPanel"></div>' +
				'<div id="gridExport" class="mGridFooterPanel"></div>' +
				//(this.selectionEnabled ? '<div id="gridSelection" class="mGridFooterPanel">' + t('Selection') + '</div>' : '')
				'';
		}

		grid.getFooter = function()
		{
			return '<div class="mGridFooter">' + this.getFooterContents() + '</div>';
		}

		grid.initVars = function()
		{
			this.currentPage = 0;
			this.totalRows = 1e12;
			this.stats = null;
			this.addedRows = 0;
		}

		grid.fixHeaderHeight = function()
		{
			var h = 0;
			var header = $(this.containerId).find(".mGridHead");
			header.find(".mGridHeadCell").each(function(){h = Math.max(h, $(this).height());});
			header.height(h + 10);
			$(this.containerId).find(".mGridHeadPlaceholder").height(h+10);
			header.find(".mGridHeadCell").height(h);
		}

		grid.initExporters = function()
		{
			this.addExporter("XLS");
			this.addExporter("PDF");
			//this.addExporter("CSV");
			//this.addExporter("JSON");
		}

		grid.addExporter = function(type)
		{
			var grid = this;
			var id = 'gridExporter' + type;
			var html = '<div style="cursor: pointer; float: left; margin-right: 3px;" id="' + id + '">' +
				'<img src="' + setup.WFW_WEB + 'ui/img/16/export-' + type + '.png" border="0" alt="' + type + '"/></div>'
			$("#gridExport").append(html);
			$("#" + id).click(function(event){
				var url = setup.INSTANCE_WEB +
					"?registry=" + req.registry +
					"&action=exportGridAs" + type +
					"&sortname=" + grid.sortname +
					"&sortorder=" + grid.sortorder +
					"&qtype=" + grid.sortname +
					"&query=" + grid.query +
					grid.appendToUrl();

				document.location = url;
			});
		}

		grid.init = function()
		{
			this.filterShown = false;
			this.initVars();
			var grid = this;
			var html = this.getHeader() +
				'<div id="gridScroller" style="clear: both; width: 100%; overflow: auto;">' +
				'<div id="gridContents"></div>' +
				'<div id="loadMore"></div></div>' +
				this.getFooter();

			$(this.containerId).html(html);
			this.fixHeaderHeight();

			$("#gridFindBox").keydown(function(event)
			{
				var kk = event.keyCode;
				if(kk == 13)
				{
					grid.doFind($("#gridFindBox").val());
					$("#gridFindBox").select();
				}
			});

			$("#gridFilterButton").click(function(event)
			{
				grid.toggleFilter();
			});

			$(this.containerId).find(".gridSetupColumns").click(function(event)
					{
						var h = Math.min(400, $(window).height() - 100);
						var html = '<div><div class="mGridVisibleColumnsHeader">' + t("Visible columns") + '</div>' +
							'<div style="height: ' + (h - 65) + 'px; overflow: auto; border: 1px solid #c9c9c9; margin-bottom: 10px;">';
						var x;
						for(x = 0; x < grid.colModel.length; x++)
						{
							var col = grid.colModel[x];
							if(col.name != "##style")
							{
								if(grid.setup[col.name] != 0)
									grid.setup[col.name] = 1;
								html += '<div class="mGridVisibleColumnsItem"><label>' + checkbox(grid.setup, col.name) + col.display + '</label></div>'; //col.display + ' &gt;&gt; ' + col.name + '</div>';								
							}
						}
						html += '</div><div style="text-align: right;"><a href="JavaScript:reloadPage();">' + t('OK') + '</a></div></div>';
						bubble.dimensions(300, h).show(html);
					});

			//$(this.containerId).find(".gridSelection")

			$("#gridScroller").scroll(function()
			{
				grid.doScroll();
			});
			this.initExporters();
			$(window).resize();
			if(this.postInitTasks)
				this.postInitTasks();
		}

		grid.showMore = function()
		{
			bubble
				.pos($("#gridMoreButton").position().left, $("#gridMoreButton").position().top + $("#gridMoreButton").height())
				.dimensions(200, 200)
				.show(t("Loading..."));
			$.get(setup.INSTANCE_WEB + "?action=moreMenuItemsHtml&registry=" + req.registry,
				function(data)
				{
					$("#bubbleContents").html(data);
					bubble.packHeight();
				});
		}

		grid.selectAll = function()
		{
			var url = setup.INSTANCE_WEB + "?action=gridSelectAll&registry=" + req.registry;
			if((this.query != "")&&(this.sortname != ""))
				url += "&qtype=" + this.sortname +
				"&query=" + this.query;

			$.get(url,
				function(data)
				{
					bubble.hide();
					grid.reload();
				},"json");
		}

		grid.unselectAll = function()
		{
			$.get(setup.INSTANCE_WEB + "?action=gridUnselectAll&registry=" + req.registry,
				function(data)
				{
					bubble.hide();
					grid.reload();
				},"json");
		}

		grid.invertSelection = function()
		{
			$.get(setup.INSTANCE_WEB + "?action=gridInvertSelection&registry=" + req.registry,
				function(data)
				{
					bubble.hide();
					grid.reload();
				},"json");
		}

		grid.getFilterWidth = function()
		{
			if(this.filterWidth == "100%")
				return $(window).width() - 60;
			return this.filterWidth;
		}

		grid.toggleFilter = function()
		{
			this.filterShown = !this.filterShown;
			if(this.filterShown)
			{
				bubble
					.pos(5, 5)
					.dimensions(this.getFilterWidth(), $(window).height() - this.filterHeightDelta)
					.show(t("Loading..."))
					.onHide = reloadPage;
				var url = setup.INSTANCE_WEB + "?action=filterUi&registry=" + req.registry;
				$.get(url,
						function(data)
						{
							$("#bubbleContents").html(data);
							grid.resizeFilterControls();
							grid.resizeGrid();
							if(grid.afterFilterShown)
								grid.afterFilterShown();
						});
			}
			else
				reloadPage();
		}

		grid.resizeFilterControls = function()
		{
			app.initMSelectList();
			$(".mselect-list-item-checkbox").click(app.multiselClicker);
			$(".mselect-list").css("width", "550px");
			$(".columnLayout").each(function()
				{
					var cc = $(this).children().length;
					var cih = bubble.height - 180;
					if(cc > 0)
					{
						var ciw = (bubble.width - 10) / cc - 10;
						var t = $(this);
						t.children().css("margin-right", "10px");
						t.find(".mselect-list").css("width", ciw).css("height", cih);
						t.find(".multiFilterControl").css("width", ciw).css("height", cih);
					}
				});
		}

		grid.resizeGrid = function()
		{
			var wh = $(window).height();
			$("#gridHeader").width($(this.containerId).width());
			$("#gridScroller")
				.height(wh - ($("#gridScroller").position().top + $(this.containerId).find(".mGridFooter").height() + 10));
			if(grid.filterShown)
			{
				$("#bubbleContents").height(wh - this.filterHeightDelta);
				$("#filterContents").height(wh - this.filterHeightDelta - $(".filterHeader").height() - 50);
			}
		}

		grid.addKeyboardNavigation = function()
		{
			if(!grid.keyboardNavigation)
				return;
			$("body").keydown(function(event)
			{
				var activeRow = $(".activeGridRow");
				if(activeRow.length < 1)
				{
					gg.setActiveRow($(".mGridRow")[0].id);
					activeRow = $(".activeGridRow");
				}
				//log(event.which);
				if(event.which == 40)
					if(activeRow.length > 0)
					{
						//down
						event.preventDefault();
						//log("down from " + activeRow[0].id);
						var next = activeRow.next();
						if(next.length > 0)
							gg.setActiveRow(next[0].id);
					}
				if(event.which == 38)
					if(activeRow.length > 0)
					{
						//up
						event.preventDefault();
						//log("up from " + activeRow[0].id);
						var prev = activeRow.prev();
						if(prev.length > 0)
							gg.setActiveRow(prev[0].id);
					}
				if(event.which == 13)
				{
					event.preventDefault();
					if(activeRow.length > 0)
						activeRow.dblclick();
				}
			});
		}

		grid.makeGrid = function()
		{
			var scrollerId = "#gridScroller";
			var s = $().length;
			var gg = this;

			if(s == 0)
			{
				$(window).resize(function()
				{
					gg.resizeGrid();
				});
			}
			this.init();
			this.addKeyboardNavigation();

		}
		if(grid.afterSetup)
				grid.afterSetup();
	}
