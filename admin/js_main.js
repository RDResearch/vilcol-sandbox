
var pwd_min = 8; // min password length, same as $pwd_min in settings.php
var pwd_max = 20; // max password length, same as $pwd_max in settings.php
var password_policy = 'Passwords must be between ' + pwd_min + ' and ' + pwd_max + ' characters long with mixed-case letters and numbers'; // same as $password_policy in settings.php
var js_debug = '';

// ------------------------------------------------------------
// Unsaved changes warnings - see settings.php for details
var unsaved_changes_made = 0;
var allow_unload = false;
var page_loaded = false; // may be used to avoid calling ajax when page is being loaded

function js_onunload()
{
	if (unsaved_changes_made)
	{
		if (allow_unload)
		{
			//alert('allow');
		}
		else
		{
			// The following alert appears before the standard one (${unsaved_warn_text})
			//alert('**Note**. There are unsaved changes on this page.\nThe next prompt will allow you to stay on the page.');
			allow_unload = true;
			return false;
		}
	}
	else
	{
		//alert('unchanged');
	}
}

function getElementFromEvent(e)
{
	if (!e)
	{
		var e = window.event;
	}
	var el;
	if (e.target)
	{
		el = e.target;
	}
	else if (e.srcElement)
	{
		el = e.srcElement;
	}
	if (el)
	{
		if (el.nodeType===3) // defeat Safari bug
		{
			el = el.parentNode;
		}
	}
	return el;
}

function disableBackspace(ev)
{
	// <body onkeydown="return disableBackspace(event);">
	// Note: in <body> tag, if onkeydown is used then IE is happy but if onkeypress is used, IE doesn't work with backspace!
	if (!ev)
		ev = window.event;
	if (ev)
	{
		if ((ev.keyCode == 8) || (ev.keyCode == 13))
		{
			var el = getElementFromEvent(ev);
			if (el)
			{
				var ty = el.type;
				if ( ! ((ty=='text') || (ty=='textarea') || (ty=='file') || (ty=='password')) )
					return false;
			}
		}
	}
	return true;		
}

function password_test(pwd) // Should be same as password_test() in library.php
{
	// Check that the password pwd satisfies the Password Policy using globals pwd_min, pwd_max and password_policy.
	// If so, return an empty string.
	// Else return the policy.
	
	var count_n = 0; // count of numbers
	var count_u = 0; // count of upper case
	var count_l = 0; // count of lower case
	if (pwd && (pwd_min <= pwd.length) && (pwd.length <= pwd_max))
	{
		for (var ii=0; ii < pwd.length; ++ii)
		{
			if (('0' <= pwd.charAt(ii)) && (pwd.charAt(ii) <= '9'))
				count_n++;
			if (('A' <= pwd.charAt(ii)) && (pwd.charAt(ii) <= 'Z'))
				count_u++;
			if (('a' <= pwd.charAt(ii)) && (pwd.charAt(ii) <= 'z'))
				count_l++;
		}
	}
	if ((count_n > 0) && (count_u > 0) && (count_l > 0))
		return '';
	return password_policy;
}

function email_valid(em)
{
	if (em.search(/^([A-Za-z0-9_\-\.\+\=])+@([A-Za-z0-9_\-\.])+\.([A-Za-z]{2,4})$/) <= -1)
		return false;
	else
		return true;
}

function isNumeric(strString, allowNeg, allowDec, allowHex, allowAlphaNumeric) //, debug)
{
	var strValidChars = '0123456789';
	var strChar;
	var blnResult = true;
	var foundDec = false;
	
	//if (debug)
	//	alert('isNumeric(' + strString + ')');
		
	if (strString.length == 0)
		return false;
	
	if (allowHex)
	{
		strString = strString.toLowerCase();
		strValidChars += 'abcdef';
	}	
	else if (allowAlphaNumeric)
	{
		strString = strString.toLowerCase();
		strValidChars += 'abcdefghijklmnopqrstuvwxyz';
	}
	for (i = 0; (i < strString.length) && blnResult; i++)
	{
		strChar = strString.charAt(i);
		if (strValidChars.indexOf(strChar) == -1)
		{
			if (strChar == '-')
			{
				if ((!allowNeg) || (i > 0))
					blnResult = false;
			}
			else if (strChar == '.')
			{
				if ((!allowDec) || foundDec)
					blnResult = false;
				else
					foundDec = true;
			}
			else
				blnResult = false;
		}
	}
	return blnResult;
}

function number_with_commas_js(num, add_pence)
{
	var num_t = '' + num;
	var bits = num_t.split('.');
	num_t = bits[0];
	var num_f = 1.0 * num_t;
	var fraction = ((bits.length > 1) ? ('.' + bits[1]) : (add_pence ? '.00' : ''));
	var num2 = '';
	while (true)
	{
		if (num_f < 1000)
		{
			num2 = num_t + num2;
			break;
		}
		else
		{
			num2 = ',' + num_t.substring(num_t.length-3, num_t.length) + num2;
			num_t = num_t.substring(0, num_t.length-3);
			num_f = 1.0 * num_t;
		}
	}
	return num2 + fraction;
}

function GetXmlHttpObject()
{
	var xmlHttp1=null;
	try
	{
		// Firefox, Opera 8.0+, Safari
		xmlHttp1=new XMLHttpRequest();
	}
	catch (e)
	{
		// Internet Explorer
		try
		{
			xmlHttp1=new ActiveXObject("Msxml2.XMLHTTP");
		}
		catch (e)
		{
			xmlHttp1=new ActiveXObject("Microsoft.XMLHTTP");
		}
	}
	return xmlHttp1;
}

// The following function is superseded by password_test()
//function password_policy_test(pwd)
//{
//	var msg = '';
//	var len = pwd.length;
//	var code = 0;
//	var uppers = 0;
//	var lowers = 0;
//	var digits = 0;
//	
//	if ((len < pwd_min) || (len > pwd_max))
//		msg = 'Password must be between ' + pwd_min + ' and ' + pwd_max + ' characters long';
//	else if (!isNumeric(pwd, 0, 0, 0, 1)) // allow alpha-numerics
//		msg = 'Password must only contain A-Z, a-z and/or 0-9';
//	else
//	{
//		for (var ii=0; ii<len; ii++)
//		{
//			code = pwd[ii].charCodeAt(0);
//			if ((code >= 48) && (code <= 57))
//				digits++;
//			else if ((code >= 65) && (code <= 90))
//				uppers++;
//			else if ((code >= 97) && (code <= 122))
//				lowers++;
//		}
//		if ((!digits) || (!uppers) || (!lowers))
//		{
//			msg = 'Password must contain a mixture of A-Z, a-z and 0-9. Please add ';
//			sub_msg = '';
//			if (!digits)
//				sub_msg += (sub_msg ? ', ' : '') + 'digits';
//			if (!uppers)
//				sub_msg += (sub_msg ? ', ' : '') + 'upper-case letters';
//			if (!lowers)
//				sub_msg += (sub_msg ? ', ' : '') + 'lower-case letters';
//			msg += sub_msg + '.';
//		}
//	}
//	return msg;
//}

function dateToSql(entry)
{
	// Must validate entry with checkDate() first
    var mo, day, yr;
    var bits = entry.split(' ');
    var e_date = bits[0];
    var e_time = '';
    if (bits.length > 1)
    	e_time = bits[1];
    var delimChar = (e_date.indexOf("/") != -1) ? "/" : ((e_date.indexOf("-") != -1) ? "-" : ".");
    var delim1 = e_date.indexOf(delimChar);
    var delim2 = e_date.lastIndexOf(delimChar);
    day = parseInt(e_date.substring(0, delim1), 10);
    mo = parseInt(e_date.substring(delim1+1, delim2), 10);
    yr = parseInt(e_date.substring(delim2+1), 10);
    if (yr <= 30)
    	yr += 2000;
    else if (yr < 100)
    	yr += 1900;
    if (mo < 10)
        mo = '0' + mo;
    if (day < 10)
        day = '0' + day;
    var rc = yr + '-' + mo + '-' + day;
    if (e_time != '')
    	rc = rc + ' ' + e_time;
    return rc;
}

function checkDate(entry, nn, today)
{
    //alert('checkDate(' + entry + ', ' + nn + ', ' + today + ')');
    var debug=0;
    var mo, day, yr;
    var re = /\b\d{1,2}[\/-]\d{1,2}[\/-]\d{1,4}\b/;
    //if (debug)
    //	alert(entry);//
    if (re.test(entry)) 
   	{
        //alert('test() OK');//
        var delimChar = (entry.indexOf("/") != -1) ? "/" : ((entry.indexOf("-") != -1) ? "-" : ".");
        var delim1 = entry.indexOf(delimChar);
        var delim2 = entry.lastIndexOf(delimChar);
        day = parseInt(entry.substring(0, delim1), 10);
        mo = parseInt(entry.substring(delim1+1, delim2), 10);
        yr = parseInt(entry.substring(delim2+1), 10);
        if (yr <= 30)
        	yr += 2000;
        else if (yr < 100)
        	yr += 1900;
	if (debug)
    	    alert('d='+day+', m='+mo+', yr='+yr);
        var testDate = new Date(yr, mo-1, day);
        //alert(testDate)
        
        // KDB 27/06/13: rather than compare testDate to "new Date()", create a today date without time element
        var temp = new Date();
        var dateToday = new Date(temp.getFullYear(), temp.getMonth(), temp.getDate());
        
        if (testDate.getDate() == day) 
       	{
            if (testDate.getMonth() + 1 == mo) 
           	{
                if (testDate.getFullYear() == yr) 
                {
                	if (today == '<')
                	{
                		if (testDate < dateToday) //new Date())
                			return true;
                		else
                			alert("Date " + nn + " should be before today");
                	}
                	else if (today == '<=')
                	{
                		if (testDate <= dateToday) //new Date())
                			return true;
                		else
                			alert("Date " + nn + " should be before or equal to today");
                	}
                	else if (today == '>=')
                	{
                		if (testDate >= dateToday) //new Date())
                			return true;
                		else
                			alert("Date " + nn + " should be after or equal to today");
                	}
                	else if (today == '>')
                	{
                		if (testDate > dateToday) //new Date())
                			return true;
                		else
                			alert("Date " + nn + " should be after today");
                	}
                	else
                    	return true;
                }
                else
                    alert("There is a problem with the year entry with Date " + nn + ".");
           	}
            else
                alert("There is a problem with the month entry with Date " + nn + ".");
       	} 
        else 
            alert("There is a problem with the day entry with Date " + nn + ".");
   	}
    else 
        alert("Incorrect date format with Date " + nn + ". Enter as dd/mm/yyyy.");
    return false;
}
	
function checkDateRange(entry_from, entry_to)
{
	var debug=0;
    var mo, day, yr;
    var re = /\b\d{1,2}[\/-]\d{1,2}[\/-]\d{1,4}\b/;
    var fromDate = 0;
    var toDate = 0;
    if (debug)
    	alert(entry_from + ', ' + entry_to);
    if (re.test(entry_from))
    {
        var delimChar = (entry_from.indexOf("/") != -1) ? "/" : ((entry_from.indexOf("-") != -1) ? "-" : ".");
        var delim1 = entry_from.indexOf(delimChar);
        var delim2 = entry_from.lastIndexOf(delimChar);
        day = parseInt(entry_from.substring(0, delim1), 10);
        mo = parseInt(entry_from.substring(delim1+1, delim2), 10);
        yr = parseInt(entry_from.substring(delim2+1), 10);
        if (yr <= 20)
        	yr += 2000;
        else if (yr < 100)
        	yr += 1900;
	    if (debug)
    	    alert('from: d='+day+', m='+mo+', yr='+yr);
        fromDate = new Date(yr, mo-1, day);
	
	    if (re.test(entry_to))
	    {
	        var delimChar = (entry_to.indexOf("/") != -1) ? "/" : ((entry_to.indexOf("-") != -1) ? "-" : ".");
	        var delim1 = entry_to.indexOf(delimChar);
	        var delim2 = entry_to.lastIndexOf(delimChar);
	        day = parseInt(entry_to.substring(0, delim1), 10);
	        mo = parseInt(entry_to.substring(delim1+1, delim2), 10);
	        yr = parseInt(entry_to.substring(delim2+1), 10);
	        if (yr <= 20)
	        	yr += 2000;
	        else if (yr < 100)
	        	yr += 1900;
		    if (debug)
	    	    alert('to: d='+day+', m='+mo+', yr='+yr);
	        toDate = new Date(yr, mo-1, day);
	        
	        if (toDate < fromDate)
	        {
	        	alert('\"To\" date is before \"from\" date - search aborted');
	        	return false;
	        }
		}
    }
	return true;
}
	
function trim(s)
{
	var l = 0; 
	var r = s.length - 1;
	while ((l < s.length) && ((s[l] == ' ') || (s[l] == '	')))
		l++;
	while ((r > l) && ((s[r] == ' ') || (s[r] == '	')))
		r -= 1;
	return s.substring(l, r+1);
}
	
function get_radio_value(el)
{
	var rad_val = '';
	for (var i=0; i < el.length; i++)
	{
		if (el[i].checked)
		{
			rad_val = el[i].value;
			break;
		}
	}
	return rad_val;
}

var auto_logout_timer_1 = 0;
var auto_logout_timeout_1 = 0;
var auto_logout_timer_2 = 0;
var auto_logout_timeout_2 = 10 * 1000;

function auto_logout_start(tout)
{
	auto_logout_timeout_1 = tout * 1000;
	auto_logout_timer_1 = setTimeout('auto_logout_trigger();', auto_logout_timeout_1);
}

function auto_logout_trigger()
{
	document.getElementById('auto_logout_prompt').style.display = 'block';
	auto_logout_timer_2 = setTimeout('auto_logout_logmeout();', auto_logout_timeout_2);
}

function auto_logout_cancel()
{
	clearTimeout(auto_logout_timer_2);
	document.getElementById('auto_logout_prompt').style.display = 'none';
	auto_logout_timer_1 = setTimeout('auto_logout_trigger();', auto_logout_timeout_1);
}

function auto_logout_logmeout()
{
	window.location = 'login.php?page_task=logout';
}

function js_trace_add(txt)
{
	var el = document.getElementById('js_trace');
	if (el)
		el.value += txt + '\r';
}

function insertAtCursor(myField, myValue)
{
	//myField accepts an object reference, myValue accepts the text strint to add
	// Example call: 
	//		insertAtCursor(document.getElementById('notes_textarea'), 'STUFF');
	// Based on code from http://www.webmasterworld.com/forum91/4686.htm
	
	//IE support
	if (document.selection)
	{
		myField.focus();
		
		//in effect we are creating a text range with zero
		//length at the cursor location and replacing it
		//with myValue
		sel = document.selection.createRange();
		sel.text = myValue;
	}

	//Mozilla/Firefox/Netscape 7+ support
	else if (myField.selectionStart || myField.selectionStart == '0')
	{
		//Here we get the start and end points of the
		//selection. Then we create substrings up to the
		//start of the selection and from the end point
		//of the selection to the end of the field value.
		//Then we concatenate the first substring, myValue,
		//and the second substring to get the new value.
		var startPos = myField.selectionStart;
		var endPos = myField.selectionEnd;
		myField.value = myField.value.substring(0, startPos)+ myValue+ myField.value.substring(endPos, myField.value.length);
	}
	
	else
	{
		myField.value += myValue;
	}
} 

function modalWin(url, uname, dw, dh)
{
	if (!dw)
		dw = 400;
	if (!dh)
		dh = 600;
	if (window.showModalDialog)
		window.showModalDialog(url, uname, 'dialogWidth:' + dw + 'px;dialogHeight:' + dh + 'px');
	else
		window.open(url, name, 'height=' + dh + ',width=' + dw + ',toolbar=no,directories=no,status=no,menubar=no,scrollbars=no,resizable=no,modal=yes');
} 

function save_scroll()
{
	var scroll = get_scroll(); 
	var el = document.getElementById('scroll_x');
	if (el)
		el.value = scroll.x;
	el = document.getElementById('scroll_y');
	if (el)
		el.value = scroll.y;
}

function get_scroll()
{
	var x = 0, y = 0;
	if ( typeof( window.pageYOffset ) == 'number' ) 
	{
		//Netscape compliant
		y = window.pageYOffset;
		x = window.pageXOffset;
	}
	else if( document.body && ( document.body.scrollLeft || document.body.scrollTop ) ) 
	{
		//DOM compliant
		y = document.body.scrollTop;
		x = document.body.scrollLeft;
	}
	else if( document.documentElement && 
			( document.documentElement.scrollLeft || document.documentElement.scrollTop ) ) 
	{
		//IE6 standards compliant mode
		y = document.documentElement.scrollTop;
		x = document.documentElement.scrollLeft;
	}
	var obj = new Object();
	obj.x = x;
	obj.y = y;
	return obj;
}
	
function postcode_lookup(eid,pcode)
{
    var pc = '';
    var el = document.getElementById(eid);
    if (el)
        pc = el.value;
    if (pc == '')
        pc = pcode;
    window.open('http://maps.google.co.uk/maps?f=q&source=s_q&hl=en&geocode=&q=' + pc.replace(' ','%20'));
}

function xprint(a)
{
	// Deny all suspicious characters
	var b = a.replace(/&/g, '&amp;');
	b = b.replace(/</g, '&lt;');
	b = b.replace(/>/g, '&gt;');
	b = b.replace(/"/g, '&quot;');
	b = b.replace(/'/g, '&#x27;');
	b = b.replace(/\//g, '&#x2F;');
	return b;
}

function xprint_noscript(a)
{
	// Deny "<script" but allow other characters
	var b = a.replace(/<script/g, 'xxx');
	b = b.replace(/alert\(/g, 'xxx');
	return b;
}
