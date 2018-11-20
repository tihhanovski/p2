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
			// TODO: find better way to fix IE focus issue
			//this.focus();
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


function isMobile()
{
	a = navigator.userAgent||navigator.vendor||window.opera;
	if(/(android|bb\d+|meego).+mobile|avantgo|bada\/|blackberry|ipad|blazer|compal|elaine|fennec|hiptop|iemobile|ip(hone|od)|iris|kindle|lge |maemo|midp|mmp|mobile.+firefox|netfront|opera m(ob|in)i|palm( os)?|phone|p(ixi|re)\/|plucker|pocket|psp|series(4|6)0|symbian|treo|up\.(browser|link)|vodafone|wap|windows ce|xda|xiino/i.test(a)||/1207|6310|6590|3gso|4thp|50[1-6]i|770s|802s|a wa|abac|ac(er|oo|s\-)|ai(ko|rn)|al(av|ca|co)|amoi|an(ex|ny|yw)|aptu|ar(ch|go)|as(te|us)|attw|au(di|\-m|r |s )|avan|be(ck|ll|nq)|bi(lb|rd)|bl(ac|az)|br(e|v)w|bumb|bw\-(n|u)|c55\/|capi|ccwa|cdm\-|cell|chtm|cldc|cmd\-|co(mp|nd)|craw|da(it|ll|ng)|dbte|dc\-s|devi|dica|dmob|do(c|p)o|ds(12|\-d)|el(49|ai)|em(l2|ul)|er(ic|k0)|esl8|ez([4-7]0|os|wa|ze)|fetc|fly(\-|_)|g1 u|g560|gene|gf\-5|g\-mo|go(\.w|od)|gr(ad|un)|haie|hcit|hd\-(m|p|t)|hei\-|hi(pt|ta)|hp( i|ip)|hs\-c|ht(c(\-| |_|a|g|p|s|t)|tp)|hu(aw|tc)|i\-(20|go|ma)|i230|iac( |\-|\/)|ibro|idea|ig01|ikom|im1k|inno|ipaq|iris|ja(t|v)a|jbro|jemu|jigs|kddi|keji|kgt( |\/)|klon|kpt |kwc\-|kyo(c|k)|le(no|xi)|lg( g|\/(k|l|u)|50|54|\-[a-w])|libw|lynx|m1\-w|m3ga|m50\/|ma(te|ui|xo)|mc(01|21|ca)|m\-cr|me(rc|ri)|mi(o8|oa|ts)|mmef|mo(01|02|bi|de|do|t(\-| |o|v)|zz)|mt(50|p1|v )|mwbp|mywa|n10[0-2]|n20[2-3]|n30(0|2)|n50(0|2|5)|n7(0(0|1)|10)|ne((c|m)\-|on|tf|wf|wg|wt)|nok(6|i)|nzph|o2im|op(ti|wv)|oran|owg1|p800|pan(a|d|t)|pdxg|pg(13|\-([1-8]|c))|phil|pire|pl(ay|uc)|pn\-2|po(ck|rt|se)|prox|psio|pt\-g|qa\-a|qc(07|12|21|32|60|\-[2-7]|i\-)|qtek|r380|r600|raks|rim9|ro(ve|zo)|s55\/|sa(ge|ma|mm|ms|ny|va)|sc(01|h\-|oo|p\-)|sdk\/|se(c(\-|0|1)|47|mc|nd|ri)|sgh\-|shar|sie(\-|m)|sk\-0|sl(45|id)|sm(al|ar|b3|it|t5)|so(ft|ny)|sp(01|h\-|v\-|v )|sy(01|mb)|t2(18|50)|t6(00|10|18)|ta(gt|lk)|tcl\-|tdg\-|tel(i|m)|tim\-|t\-mo|to(pl|sh)|ts(70|m\-|m3|m5)|tx\-9|up(\.b|g1|si)|utst|v400|v750|veri|vi(rg|te)|vk(40|5[0-3]|\-v)|vm40|voda|vulc|vx(52|53|60|61|70|80|81|83|85|98)|w3c(\-| )|webc|whit|wi(g |nc|nw)|wmlb|wonu|x700|yas\-|your|zeto|zte\-/i.test(a.substr(0,4)))
		return true;
	else
		return false;
}