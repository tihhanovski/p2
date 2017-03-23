$(function()
{
	$("#menu").metisMenu();
	$( ".frontpageDasboardItems" ).sortable();
	//$("#mainMenu").click(function(){app.mainMenu()});

	app.mainMenu = function()
	{
		var mm = $("#mainMenu");

		if(app.isMainMenuVisible())
		{
			$("#popupMainMenu").animate({"left": (-$("#popupMainMenu").width() - 10) + "px"}, "fast");
			$("#mainMenu").hide("fast");
		}
		else
		{
			var url = baseUrl() + "?action=mainMenu" + (req.registry ? "&registry=" + req.registry : "");
			$.get(
				url,
				function(data)
				{
					$("#mainMenu")
						.html('<div class="popupMainMenu" id="popupMainMenu" style="display: none;">' + data +
								'</div><div class="mainMenuExitPlaceholder" onclick="JavaScript:app.mainMenu();"></div>')
						.show();
					$("#menu").metisMenu();

					app.resizeMainMenu();

					$("#popupMainMenu")
						.css("left", (-$("#popupMainMenu").width()) + "px")
						.show()
						.animate({"left": "0px"}, "fast");
			});
		}
	};

	app.isMainMenuVisible = function()
	{
		var mm = $("#mainMenu");
		return mm[0].style.display != "none" && mm.position().left > -10;
	};

	app.resizeMainMenu = function()
	{
		var w = $(window);
		var tmh = $(".topMenu").outerHeight();
		var popup = $("#popupMainMenu");
		var mh = w.height() - tmh;
		var mw = 350;
		$("#mainMenu").offset({ top: tmh, left: 0 })
			.width(w.width())
			.height(mh);
		popup
			.offset({top: tmh, left: 0})
			.height(mh)
			.width(mw);

		$(".mainMenuExitPlaceholder")
			.offset({top: tmh, left: popup.outerWidth()})
			.height(mh)
			.width(w.width() - mw);
	};
});