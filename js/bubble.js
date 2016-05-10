var bubble = {

	"left":-1,
	"top":-1,
	"width":-1,
	"height":-1,

	"containerId": "#bubbleContainer",

	"reset": function()
	{
		this.left = -1;
		this.top = -1;
		this.width = -1;
		this.height = -1;
		return this;
	},

	"pos": function(x, y)
	{
		this.left = x;
		this.top = y;
		return this;
	},

	"dimensions": function(w, h)
	{
		this.width = w;
		this.height = h;
		return this;
	},

	"userMenu": function(contents)
	{
		this.showBubble(contents, "userMenu").stopEventPropagation();;
	},

	"show": function(contents)
	{
		this.showBubble(contents, "bubbleContents")
			.positionBubble()
			.stopEventPropagation();
		return this;
	},

	"showBubble": function(contents, className)
	{
		var html = '<div class="' + className + '" id="bubbleContents">' + contents + '</div>';

		this.container()
			.width($(window).width())
			.height($(window).height())
			.html(html)
			.fadeIn(200);
		return this;
	},

	"positionBubble": function()
	{
		if(this.width < 0)
			this.width = $(window).width() / 2;
		if(this.height < 0)
			this.height = $(window).height() / 2;
		if(this.left < 0)
			this.left = ($(window).width() - this.width - 20) / 2;
		if(this.top < 0)
			this.top = ($(window).height() - this.height - 20) / 2;

		$("#bubbleContents")
				.css("left", this.left + "px")
				.css("top", this.top + "px")
				.width(this.width)
				.height(this.height);
		return this;
	},

	"packHeight": function()
	{
		var h1 = $("#bubbleContents").children().height();
		if(h1 < this.height)
			$("#bubbleContents").height(h1 + 38);

	},

	"stopEventPropagation": function()
	{
		$("#bubbleContents").click(function(event){event.stopPropagation();});
		return this;
	},

	"hide": function()
	{
		$(this.containerId).fadeOut(200).html("");
		this.pos(-1, -1).dimensions(-1, -1);
		if(this.onHide)
			this.onHide();
	},

	"resize": function()
	{
		$(this.containerId)
			.width($(window).width())
			.height($(window).height());

	},

	"container": function()
	{
		if($(this.containerId).length == 0)
		{
			var html = '<div id="bubbleContainer" class="bubbleContainer" onclick="JavaScript:bubble.hide();"></div>';
			$("body").append(html);
		}
		return $(this.containerId);
	}
};

$(function(){$(window).resize(function(){bubble.resize();})})