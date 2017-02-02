function getDataObject(obj, path)
{
	var p = path.replace("-", "_");
	if(p.indexOf("_") < 0)
		return obj[p];
	else
	{
		var l1 = p.split("_");
		var pref = l1[0];
		var obj = obj[pref];
		var np = "";
		var x;
		for(x = 1; x < l1.length; x++)
			np += (np != "" ? "_" : "") + l1[x];
		return getDataObject(obj, np);
	}
}


function quote(s)
{
	return s.replace(new RegExp("\"", 'g'), "&quot;");
}

function enterAsTab()
{
	$('input, select, textarea').live('keydown', function(e) 
			{
				if(e.keyCode==13)
				{
			  		var focusable = $('input,a,select,button,textarea').filter(':visible');
			  		var i = focusable.index(this)+1;
			  		for(x = i; x < focusable.length; x++)
			  		{
			  			var f = focusable[x];
			  			if(f.tabIndex >= 0)
			  			{
			  				f.focus();
			  				return false;
			  			}
			  		}
			  		return false;
			 	}
			});
}

function cursorKeysOnGrid()
{
	$(".gridText,.gridFloat").live("keydown", handleGridFieldCursorKeys);
}

function moveCursorOnGridLR(c, direction)
{
		var focusable = $(".gridText,.gridFloat").filter(':visible');
		var i = focusable.index(c) + direction;
		if(i >= 0)
			for(x = i; x < focusable.length; x++)
			{
				var f = focusable[x];
				if(f.tabIndex >= 0)
				{
					f.focus();
					if(f.select)
						f.select();
					return false;
				}
			}
		return false;
}

function moveCursorOnGridUD(c, direction)
{
	if(c.tagName == "SELECT")
		return true;
	
	var li = c.id.lastIndexOf("-");
	if(li >= 0)
	{
		var s1 = c.id.substring(0, li + 1);
		var s2 = c.id.substring(li + 1);
		var io = s2.indexOf("_");
		var i = s2.substring(0, io);
		var s3 = s2.substring(io);
		var newId = s1 + (direction + Number(i)) + s3;
		try{$("#" + newId).focus().select();}catch(e){}
	}
	return false;
}

function handleGridFieldCursorKeys(event)
{
	if(event.keyCode == 37 && selectedFromTheBegin(this))	//move left
		return moveCursorOnGridLR(this, -1);
	if(event.keyCode == 38 && selectedFromTheBegin(this))	//move up
		return moveCursorOnGridUD(this, -1);
	if(event.keyCode == 39 && selectedToTheEnd(this))		//move right
		return moveCursorOnGridLR(this, 1);
	if(event.keyCode == 40 && selectedToTheEnd(this))		//move dn
		return moveCursorOnGridUD(this, 1);
}

function selectedToTheEnd(c)	//TODO move to framework
{
	if(c.tagName == "INPUT")
		return c.selectionEnd == c.value.length;
	return true;
}

function selectedFromTheBegin(c)	//TODO move to framework
{
	if(c.tagName == "INPUT")
		return c.selectionStart == 0;
	return true;
}

function resizeEditGrid(id)
{
	var w = 0;
	var h = 0;
	$("#" + id).children(".gridHead").find(".gridHeadCell:visible").each(function(){
		w += $(this).width() + 10;
		h = Math.max($(this).height(), h);
	});
	$("#" + id).width(w);
	$("#" + id).find(".gridHead").css("height", h + "px").find(".gridHeadCell:visible").css("height", h + "px");
}

function unnull(val)
{
	if(val == null)
		return '';
	else
		return val;
}

function log(l)
{
	try{console.log(l);}catch(e){}
}

function setDatePicker(s)
{
	$(s).datepicker({
		onClose: function(dateText, inst)
		{
			log("datepicker.onClose: " + this.id);
			this.focus();
			this.onblur = this.savedOnBlur;
			saveField(this.id, this.id, this.value, null);
			app.lastFocusedDatePicker = "";
		},
		beforeShow: function(input, inst)
		{
			if(app.lastFocusedDatePicker && app.lastFocusedDatePicker != "")
			{
				saveField(app.lastFocusedDatePicker, app.lastFocusedDatePicker, $("#" + app.lastFocusedDatePicker).val(), null);
			}
			app.lastFocusedDatePicker = this.id;
			log("datepicker.beforeShow: " + this.id);
			this.savedOnBlur = this.onblur;
			this.onblur = null;
		},
		showWeek: true,
		dateFormat: setup.datepickerFormat,
		});
}

function setNumericInt(s)
{
	$(s).keydown(function(event)
	{
		var kk = event.keyCode;
        // Allow only backspace and delete
        if ( kk == 46 || kk == 8 || kk == 9 || kk == 221 || (kk >= 37 && kk <= 40))
        {
            // let it happen, don't do anything
        }
        else 
        {
            // Ensure that it is a number and stop the keypress
            if ((kk < 48 || kk > 57) && (kk < 96 || kk > 107 ) && kk != 109 && kk != 111)
            {
                event.preventDefault(); 
            }
        }
    });
}

function setNumericDbl(s)
{
	$(s).keydown(function(event) 
	{
		var kk = event.keyCode;
        // Allow only backspace and delete
        if ( kk == 46 || kk == 8 || kk == 9 || (kk >= 37 && kk <= 40) 
        	|| kk == 188 || kk == 190 || kk == 189 || kk == 110 || kk == 221 
        	|| kk == 187 || kk == 220 || kk == 82)
        {
            // let it happen, don't do anything
        }
        else
        {
            // Ensure that it is a number and stop the keypress
            if ((kk < 48 || kk > 57) && (kk < 96 || kk > 107 ) && kk != 109 && kk != 111)
            {
                event.preventDefault(); 
            }
        }
    });
}

function getGridDelRowField(delPath)
{
	return '<td class="gridDeleteField" onclick="JavaScript:delRow(\'' + delPath + '\');"><img src="ui/img/16/del.png" border="0"/></td>';
}

function getGridDelRowFieldDiv(delPath)
{
	return '<div class="gridCell gridCellW1 gridDeleteField" onclick="JavaScript:delRow(\'' + delPath + '\');">' + 
		'<img src="' + setup.WFW_WEB + 'ui/img/16/del.png" style="margin-top: 4px;" border="0"/></div>';
}

function todo(ticket)
{
	var url = 'http://84.50.246.180/projects/code/ticket/' + ticket;
	if(confirm('Not ready yet.\nView trac?'))
		document.location = url;
}

