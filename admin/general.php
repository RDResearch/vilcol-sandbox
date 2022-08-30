<?php

include_once("settings.php");
include_once("library.php");
global $denial_message;
global $navi_1_finance;
global $navi_2_fin_general;
global $role_man;
global $USER; # set by admin_verify()

log_open("vilcol.log");

sql_connect();

admin_verify(); # writes to $USER

if ($USER['IS_ENABLED'])
{
	if (role_check('*', $role_man))
	{
		$navi_1_finance = true; # settings.php; used by navi_1_heading()
		$navi_2_fin_general= true; # settings.php; used by navi_2_heading()
		$onload = "onload=\"set_scroll();\"";
		$page_title_2 = 'Post General - Vilcol';
		screen_layout();
	}
	else 
		print "<p>Sorry, you do not have access to this screen.";
}
else 
	print "<p>" . server_php_self() . ": login is not enabled</p>";
	
sql_disconnect();
log_close();

function screen_content()
{
	global $client2_id;
	global $inv_dt;
	global $inv_net;
	global $inv_num;
	global $inv_type;
	global $invoice_id;
	global $page_title_2;
	global $posting;
	global $task;
	global $tr_colour_1;
	
	print "<h3>Finance</h3>";
	navi_2_heading(); # secondary navigation buttons
	print "<h3>Post General Invoices &amp; Credits</h3>";
	
	dprint(post_values());

	$task = post_val('task');
	$client2_id = post_val('client2_id', true);
	$invoice_id = post_val('invoice_id', true);

	$posting = ( ((0 < $client2_id) && (($invoice_id == 0) || ($task == 'list_invoices')# || ($task == 'delete_invoice')
																						)) ? true : false);
	
//	if ($task == 'delete_invoice')
//	{
//		sql_delete_invoice($invoice_id);
//		$task = 'list_invoices';
//	}
	
	if ($task == 'list_invoices')
	{
		$inv_type = '';
		$inv_num = '';
		$inv_dt = '';
		$inv_net = '';
	}
	else
	{
		$inv_type = post_val('inv_type');
		$inv_num = post_val('inv_num');
		if ($inv_num != '')
			$inv_num *= 1;
		$inv_dt = post_val('inv_dt');
		$inv_net = post_val('inv_net');
		if ($inv_net != '')
			$inv_net *= 1.0;
	}
	
	javascript();
	print_goto_form();
	
	if ($task == 'post')
	{
		$invoice_id = do_post_invoice();
		$inv_type = '';
		$inv_num = '';
		$inv_dt = '';
		$inv_net = '';
		if (0 < $invoice_id)
		{
			print "
			<script type=\"text/javascript\">
			edit_invoice($invoice_id);
			</script>
			";
			$task = 'list_invoices';
		}
	}
	
//	if ($task == 'edit_invoice')
//		print_edit_invoice();
//	else
//	{
		print "
		<div id=\"div_form_main\" style=\"background-color:{$tr_colour_1};\">
		<hr>
		";
		print_client_form();
		if (0 < $client2_id)
			print_post_form();
		print "
		<hr>
		</div><!--div_form_main-->
		";
		if (0 < $client2_id)
			print_invoices();
//	}
	
	print "
	<script type=\"text/javascript\">
	document.getElementById('page_title').innerHTML = '$page_title_2';
	</script>
	";
	
} # screen_content()

function screen_content_2()
{
	# This is required by screen_layout()
} # screen_content_2()

function print_client_form()
{
	global $client_name;
	global $client2_id;
	
	$client_name = '';
	if (0 < $client2_id)
	{
		sql_encryption_preparation('CLIENT2');
		$sql = "SELECT C_CODE, " . sql_decrypt('C_CO_NAME', '', true) . " FROM CLIENT2 WHERE CLIENT2_ID=$client2_id";
		sql_execute($sql);
		while (($newArray = sql_fetch_assoc()) != false)
			$client_name = "{$newArray['C_CODE']} - {$newArray['C_CO_NAME']}"; # format: "<code> - <name>"
	}
	
	print "
	<form name=\"form_client\" action=\"" . server_php_self() . "\" method=\"post\" onsubmit=\"return false;\">
	<table name=\"table_client\">
	<tr>
		<td>Please select a client: </td>
		<td>" . input_textbox('client_txt', $client_name, 50, 100, "autocomplete=\"off\" onkeyup=\"find_clients(this, event);\"") . "
			" . input_hidden('client2_id', $client2_id) . "</td>
	</tr>
	<tr>
		<td></td>
		<td><span id=\"client_list\"></span></td>
	</tr>
	</table><!--table_main-->
	</form><!--form_client-->
	";
} # print_client_form()

function javascript()
{
	global $safe_amp;
	global $safe_slash;
	global $uni_pound;
	
	print "
	<script type=\"text/javascript\">
	
	var ajax_clients = '';
	var first_client2_id = 0;
	
	function show_invoices(cid)
	{
		document.form_goto_invoice.client2_id.value = cid;
		document.form_goto_invoice.sc_type.value = '';
		document.form_goto_invoice.task.value = 'search_client2_id';
		document.form_goto_invoice.submit();
	}
	
//	function del_invoice(iid)
//	{
//		if (confirm('WARNING: Deleting this invoice cannot be undone. Do you really want to DELETE this invoice?'))
//		{
//			document.form_edit.task.value = 'delete_invoice';
//			document.form_edit.submit();
//		}
//	}
	
//	function refresh_edit()
//	{
//		document.form_edit.task.value = 'edit_invoice';
//		document.form_edit.submit();
//	}
	
	function refresh_list()
	{
		document.form_post.task.value = 'list_invoices';
		please_wait_on_submit();
		document.form_post.submit();
	}
	
//	function show_client()
//	{
//		document.form_edit.task.value = 'list_invoices';
//		document.form_edit.submit();
//	}
	
//	function update_invoice(control, i_id, i_type, field_type, date_check)
//	{
		" .
//		// i_id:
//		//				INVOICE.INVOICE_ID
//		// i_type:
//		//				INVOICE.INV_TYPE
//		// field_type (data type):
//		//				blank or x = default (text); no extra processing
//		//				d = Date, optionally send date_check e.g. '<=' for on or before today
//		//				i = Time, 24 hour clock, with/without colon
//		//				m = money (optional '£')
//		//				p = percentage (optional '%')
//		//				n = Number (allows negatives and decimals)
//		//				t = Tickbox
//		//				e = Email address
//		// date_check:
//		//				see 'd' above
		"
//		var field_name = control.name;
//		var field_value_raw = trim(control.value);
//		var field_value = field_value_raw;
//		field_value = field_value.replace(/'/g, \"\\'\");
//		field_value = field_value.replace(/&/g, \"\\u0026\");
//		field_value = field_value.replace(/&/g, \"$safe_amp\");
//		field_value = field_value.replace(/\//g, \"$safe_slash\");
//		field_value = field_value.replace(/\\n/g, \"\\%0A\");
//		
//		if (field_type == 'm') // money
//		{
//			field_value = trim(field_value.replace('£','').replace(/,/g,'').replace(/\\{$uni_pound}/g, ''));
//			field_type = 'n';
//		}
//		else if (field_type == 'p') // percentage
//		{
//			field_value = trim(field_value.replace('%',''));
//			field_type = 'n';
//		}
//		// don't do any more 'else'
//		
//		if (field_type == 'd') // date
//		{
//			if (field_value == '')
//				field_value = '__NULL__';
//			else if (checkDate(field_value_raw, 'entry', date_check))
//				field_value = dateToSql(field_value_raw);
//			else
//				return false;
//		}
//		else if (field_type == 'i') // time
//		{
//			if (field_value == '')
//				field_value = '__NULL__';
//			else
//			{
//				var hour = -1;
//				var min = -1;
//				var sec = -1;
//
//				var fv = field_value.replace(/\\./g,':').replace(/ /g,':');
//				if (fv.indexOf(':') == -1)
//				{
//					if (fv.length == 3)
//						fv = fv.substring(0,0) + ':' + fv.substring(1,2);
//					else if (fv.length == 4)
//						fv = fv.substring(0,1) + ':' + fv.substring(2,3);
//				}
//				//alert('FV=' + field_value + ', fv=' + fv);
//				
//				var bits = fv.split(':');
//				if (1 < bits.length)
//				{
//					if (isNumeric(bits[0], false, false, false, false) && isNumeric(bits[1], false, false, false, false))
//					{
//						if ((0 <= bits[0]) && (bits[0] <= 23) && (0 <= bits[1]) && (bits[1] <= 59))
//						{
//							if (bits.length == 2)
//								sec = 0;
//							else if (2 < bits.length)
//							{
//								if (isNumeric(bits[2], false, false, false, false) && (0 <= bits[2]) && (bits[2] <= 59))
//									sec = bits[2];
//							}
//							if (0 <= sec)
//							{
//								hour = bits[0];
//								min = bits[1];
//							}
//						}
//					}
//				}
//				//alert('H=' + hour + ', M=' + min);
//				
//				if (0 <= hour)
//				{
//					field_value = hour + ':' + min + ':' + sec;
//				}
//				else
//				{
//					alert('Please enter a time e.g. 15:45');
//					return false;
//				}
//			}
//		}
//		else if (field_type == 'n') // number
//		{
//			if (field_value == '')
//				field_value = '__NULL__';
//			else if (!isNumeric(field_value, true, true, false, false)) // allow neg and decimal
//			{
//				alert('Please enter a number');
//				return false;
//			}
//		}
//		else if (field_type == 't') // tickbox
//			field_value = (control.checked ? '1' : '0');
//		else if (field_type == 'e') // email
//		{
//			if (field_value == '')
//			{
//				//alert('Please enter an email address');
//				//return false;
//			}
//			else if (!email_valid(field_value))
//			{
//				alert('The email address is syntactically invalid');
//				return false;
//			}
//		}
//		else
//			field_type = 'x'; // text
//			
//		xmlHttp2 = GetXmlHttpObject();
//		if (xmlHttp2 == null)
//			return;
//		var url = 'ledger_ajax.php?op=ui&i=' + i_id + '&n=' + field_name + '&v=' + field_value + '&ty=' + field_type;
//		url = url + '&ran=' + Math.random();
//		//alert(url);
//		xmlHttp2.onreadystatechange = stateChanged_update_invoice;
//		xmlHttp2.open('GET', url, true);
//		xmlHttp2.send(null);
//	}
//	
//	function stateChanged_update_invoice()
//	{
//		if (xmlHttp2.readyState == 4)
//		{
//			var resptxt = xprint_noscript(xmlHttp2.responseText);
//			if (resptxt)
//			{
//				bits = resptxt.split('|');
//				if (bits[0] == 1)
//				{
//					if (bits[1] == 'warning')
//						alert('WARNING: ' + bits[2]);
//					else if (bits[1] != 'ok')
//						alert('stateChanged_update_invoice: Ajax returned one and: ' + bits[1]);
//				}
//				else
//					alert('stateChanged_update_invoice: Ajax returned: ' + resptxt);
//			}
//			//else
//			//	alert('stateChanged_update_invoice: No response from ajax call!');
//		}
//	}
	
//	function update_alloc(control, i_id, inv_id, alloc_id, field_type, date_check)
//	{
		" .
//		// i_id:
//		//				INV_ALLOC.INVOICE_ID
//		// inv_id:
//		//				INV_ALLOC.INVOICE_ID
//		// alloc_id:
//		//				INV_ALLOC.INV_ALLOC_ID (if zero then create new INV_ALLOC record)
//		// field_type (data type):
//		//				blank or x = default (text); no extra processing
//		//				d = Date, optionally send date_check e.g. '<=' for on or before today
//		//				i = Time, 24 hour clock, with/without colon
//		//				m = money (optional '£')
//		//				p = percentage (optional '%')
//		//				n = Number (allows negatives and decimals)
//		//				t = Tickbox
//		//				e = Email address
//		// date_check:
//		//				see 'd' above
		"
//		//var field_name = control.name; // not needed
//		var field_value_raw = trim(control.value);
//		var field_value = field_value_raw;
//		field_value = field_value.replace(/'/g, \"\\'\");
//		field_value = field_value.replace(/&/g, \"\\u0026\");
//		field_value = field_value.replace(/&/g, \"$safe_amp\");
//		field_value = field_value.replace(/\//g, \"$safe_slash\");
//		field_value = field_value.replace(/\\n/g, \"\\%0A\");
//		
//		if (field_type == 'm') // money
//		{
//			field_value = trim(field_value.replace('£','').replace(/,/g,'').replace(/\\{$uni_pound}/g, ''));
//			field_type = 'n';
//		}
//		else if (field_type == 'p') // percentage
//		{
//			field_value = trim(field_value.replace('%',''));
//			field_type = 'n';
//		}
//		// don't do any more 'else'
//		
//		if (field_type == 'd') // date
//		{
//			if (field_value == '')
//				field_value = '__NULL__';
//			else if (checkDate(field_value_raw, 'entry', date_check))
//				field_value = dateToSql(field_value_raw);
//			else
//				return false;
//		}
//		else if (field_type == 'i') // time
//		{
//			if (field_value == '')
//				field_value = '__NULL__';
//			else
//			{
//				var hour = -1;
//				var min = -1;
//				var sec = -1;
//
//				var fv = field_value.replace(/\\./g,':').replace(/ /g,':');
//				if (fv.indexOf(':') == -1)
//				{
//					if (fv.length == 3)
//						fv = fv.substring(0,0) + ':' + fv.substring(1,2);
//					else if (fv.length == 4)
//						fv = fv.substring(0,1) + ':' + fv.substring(2,3);
//				}
//				//alert('FV=' + field_value + ', fv=' + fv);
//				
//				var bits = fv.split(':');
//				if (1 < bits.length)
//				{
//					if (isNumeric(bits[0], false, false, false, false) && isNumeric(bits[1], false, false, false, false))
//					{
//						if ((0 <= bits[0]) && (bits[0] <= 23) && (0 <= bits[1]) && (bits[1] <= 59))
//						{
//							if (bits.length == 2)
//								sec = 0;
//							else if (2 < bits.length)
//							{
//								if (isNumeric(bits[2], false, false, false, false) && (0 <= bits[2]) && (bits[2] <= 59))
//									sec = bits[2];
//							}
//							if (0 <= sec)
//							{
//								hour = bits[0];
//								min = bits[1];
//							}
//						}
//					}
//				}
//				//alert('H=' + hour + ', M=' + min);
//				
//				if (0 <= hour)
//				{
//					field_value = hour + ':' + min + ':' + sec;
//				}
//				else
//				{
//					alert('Please enter a time e.g. 15:45');
//					return false;
//				}
//			}
//		}
//		else if (field_type == 'n') // number
//		{
//			if (field_value == '')
//				field_value = '__NULL__';
//			else if (!isNumeric(field_value, true, true, false, false)) // allow neg and decimal
//			{
//				alert('Please enter a number');
//				return false;
//			}
//		}
//		else if (field_type == 't') // tickbox
//			field_value = (control.checked ? '1' : '0');
//		else if (field_type == 'e') // email
//		{
//			if (field_value == '')
//			{
//				//alert('Please enter an email address');
//				//return false;
//			}
//			else if (!email_valid(field_value))
//			{
//				alert('The email address is syntactically invalid');
//				return false;
//			}
//		}
//		else
//			field_type = 'x'; // text
//		
//		update_alloc_direct(i_id, inv_id, alloc_id, field_value, field_type, 0);
//	}
//	
//	function update_alloc_direct(i_id, inv_id, alloc_id, field_value, field_type, reload)
//	{
//		xmlHttp2 = GetXmlHttpObject();
//		if (xmlHttp2 == null)
//			return;
//		var url = 'ledger_ajax.php?op=ua&ri=' + i_id + '&ii=' + inv_id + '&ai=' + alloc_id + '&v=' + field_value + '&ty=' + field_type;
//		url = url + '&ran=' + Math.random();
//		//alert(url);
//		xmlHttp2.onreadystatechange = stateChanged_update_alloc;
//		xmlHttp2.open('GET', url, true);
//		xmlHttp2.send(null);
//		
//		if (reload == 1)
//			setTimeout(refresh_edit, 500);
//	}
//	
//	function stateChanged_update_alloc()
//	{
//		if (xmlHttp2.readyState == 4)
//		{
//			var resptxt = xprint_noscript(xmlHttp2.responseText);
//			if (resptxt)
//			{
//				bits = resptxt.split('|');
//				if (bits[0] == 1)
//				{
//					if (bits[1] == 'warning')
//						alert('WARNING: ' + bits[2]);
//					else if (bits[1] != 'ok')
//						alert('stateChanged_update_alloc: Ajax returned one and: ' + bits[1]);
//				}
//				else
//					alert('stateChanged_update_alloc: Ajax returned: ' + resptxt);
//			}
//			//else
//			//	alert('stateChanged_update_alloc: No response from ajax call!');
//		}
//	}
	
	function edit_invoice(iid)
	{
		document.form_goto_invoice.task.value = 'edit_by_invoice_id';
		document.form_goto_invoice.sc_type.value = 'ic';
		document.form_goto_invoice.invoice_id.value = iid;
		document.form_goto_invoice.submit();
	}

	function find_clients(control, event)
	{
		if (event.keyCode == 13)
		{
			//alert('Up: RETURN (' + event.keyCode + ') - ' + first_client2_id);
			choose_client(first_client2_id);
			return;
		}
		var el = document.getElementById('client_list');
		if (el)
		{
			el.innerHTML = '';
			first_client2_id = 0;
		}
		el = document.getElementById('div_post');
		if (el)
			el.style.display = 'none';
		el = document.getElementById('div_invoices');
		if (el)
			el.style.display = 'none';
		
		var ctxt = control.value;
		if (3 <= ctxt.length)
		{
			xmlHttp2 = GetXmlHttpObject();
			if (xmlHttp2 == null)
				return;
			var url = 'clients_ajax.php?op=find&t=' + ctxt;
			url = url + '&ran=' + Math.random();
			//alert(url);
			xmlHttp2.onreadystatechange = stateChanged_find_clients;
			xmlHttp2.open('GET', url, true);
			xmlHttp2.send(null);
		}
		//alert(ctxt);
	}
	
	function stateChanged_find_clients()
	{
		if (xmlHttp2.readyState == 4)
		{
			var resptxt = xprint_noscript(xmlHttp2.responseText);
			if (resptxt)
			{
				ajax_clients = resptxt;
				bits = ajax_clients.split('|');
				var el = document.getElementById('client_list');
				var htmltxt = '';
				
				for (ii = 0; ii < bits.length; ii++)
				{
					cfields = bits[ii].split('^');
					if (first_client2_id == 0)
						first_client2_id = cfields[0];
					";
					# format of displayed client name: "<code> - <name>"
					print "
					htmltxt += '<span style=\"cursor:pointer;\" onclick=\"choose_client(' + cfields[0] + ');\">' + cfields[1] + ' - ' + cfields[2] + '</span><br>';
				}
				el.innerHTML = htmltxt;
			}
		}
	}
	
	function choose_client(cid)
	{
		bits = ajax_clients.split('|');
		for (ii = 0; ii < bits.length; ii++)
		{
			cfields = bits[ii].split('^');
			if (cfields[0] == cid)
			{
				document.form_client.client2_id.value = cid;
				";
				# format of displayed client name: "<code> - <name>"
				print "
				document.form_client.client_txt.value = cfields[1] + ' - ' + cfields[2];
				var el = document.getElementById('client_list');
				el.innerHTML = '';
				first_client2_id = 0;
				please_wait_on_submit();
				document.form_client.submit();
			}
		}
	}
	
	function post_invoice()
	{
		var itype = get_radio_value(document.form_post.inv_type_txt);
		if (itype == '')
		{
			alert('Please select either \"Invoice\" or \"Credit\"');
			return false;
		}
		var num = trim(document.form_post.inv_num_txt.value);
		if ((num != '') && (!isNumeric(num, false, false, false, false))) // disallow neg and decimal
		{
			alert('Please enter a number into \"Invoice/Credit number (or leave blank)\"');
			return false;
		}
		var dt = trim(document.form_post.inv_dt_txt.value);
		if (dt != '')
		{
			if (!checkDate(dt, '', '<='))
				return false;
			dt = dateToSql(dt);
		}
		var net = trim(document.form_post.inv_net_txt.value.replace('£','').replace(/,/g,'').replace(/\\{$uni_pound}/g, ''));
		if (!isNumeric(net, true, true, false, false)) // allow neg and decimal
		{
			alert('Please enter a number into \"Amount\"');
			return false;
		}
		document.form_post.inv_type.value = itype;
		document.form_post.inv_num.value = num;
		document.form_post.inv_net.value = net;
		document.form_post.inv_dt.value = dt;
		document.form_post.task.value = 'post';
		please_wait_on_submit();
		document.form_post.submit();
	}
	
	</script>
	";
} # javascript()

function print_post_form()
{
	global $client_archived; # set here, also used by print_invoices()
	global $client2_id;
	global $posting;
	global $inv_num;
	global $inv_net;
	global $inv_dt;
	global $inv_type;

	list($balance, $invoices, $credits, $focs, $receipts, $adjusts) = sql_get_client_balance_info($client2_id);
	$balance_info = "Client Account Balance: " . money_format_kdb($balance, true, true, true) . "<br>" .
					"Invoices&nbsp;" . money_format_kdb($invoices, true, true, true) . ". " .
					"Credits&nbsp;" . money_format_kdb($credits, true, true, true) . ". " .
					"FOCs&nbsp;" . money_format_kdb($focs, true, true, true) . ". " .
					"Receipts&nbsp;" . money_format_kdb($receipts, true, true, true) . ". " .
					"Credits&nbsp;" . money_format_kdb($adjusts, true, true, true) . ".";
	
	$onchange = ($posting ? '' : 'readonly');
	
	print "
	<div id=\"div_post\" style=\"display:" . ((0 < $client2_id) ? 'block' : 'none') . "\">
	<p>{$balance_info}&nbsp;&nbsp;&nbsp;" . input_button('View all invoices &amp; receipts', "show_invoices($client2_id)") . "</p>
	";
	
	$client_archived = client_archived($client2_id);
	if ($client_archived)
		print "<p>This client is Archived";
	else
	{
		print "
		<form name=\"form_post\" action=\"" . server_php_self() . "\" method=\"post\" onsubmit=\"post_invoice()\">
			" . input_hidden('client2_id', $client2_id) . "
			" . input_hidden('task', '') . "
			" . input_hidden('inv_type', '') . "
			" . input_hidden('inv_num', '') . "
			" . input_hidden('inv_net', '') . "
			" . input_hidden('inv_dt', '') . "
		" . ($posting ? "<input type=\"submit\" style=\"display:none\">" : '') . "
		";

		$button = ($posting ? input_button('Post Invoice/Credit', 'post_invoice()') : '');
		print_invoice_line(0, $posting ? '' : $inv_type, $onchange, $inv_num, $onchange, $inv_dt, $onchange, $inv_net, $onchange, '', $button);

		print "
		</form><!--form_post-->
		";
	}
	
	print "
	</div><!--div_post-->
	";
} # print_post_form()

function print_invoice_line($invoice_id, $inv_type, $ex_type, $inv_num, $ex_num, $inv_dt, $ex_dt, $inv_net, $ex_net, $alloc_rem, $button)
{
	global $grey;
	global $style_r;
	
	if (0 < $invoice_id)
	{
		$name_type = 'inv_type';
		$name_num = 'inv_num';
		$name_dt = 'inv_dt';
		$name_net = 'inv_net';
	}
	else
	{
		$name_type = 'inv_type_txt';
		$name_num = 'inv_num_txt';
		$name_dt = 'inv_dt_txt';
		$name_net = 'inv_net_txt';
	}
	
	$gap = "<td width=\"10\"></td>";
	$am = "valign=\"middle\"";
	
	print "
	<table name=\"table_invoice_line\" class=\"basic_table\">
	<tr>
		<td $am>
			" . input_radio($name_type, array('Invoice' => 'I', 'Credit' => 'C'), $inv_type, '<br>', $ex_type) . "</td>
		$gap
		<td $am>Invoice/Credit number: " . input_textbox($name_num, $inv_num, 10, 10, "$style_r $ex_num") . "<br>(leave blank for auto)</td>
		$gap
		<td $am>Date issued: " . input_textbox($name_dt, date_for_sql($inv_dt, true, false), 10, 10, "$style_r $ex_dt") . "<br>(leave blank for auto)</td>
		$gap
		<td $am>Net amount: " . input_textbox($name_net, 
										($inv_net == '') ? '' : money_format_kdb($inv_net, true, true, true), 
										10, 10, "$style_r $ex_net") . "
		" . (($alloc_rem === '') ? '' :
				("$gap
				Unallocated: " . input_textbox('unallocated', money_format_kdb($alloc_rem, true, true, true), 10, 10, "$style_r readonly")
				)). "<br>&nbsp;</td>
		";
		if ($button)
			print "
				$gap
				<td $am>$button<br>&nbsp;</td>
				";
		if (user_debug())
			print "
				$gap
				<td $am><span $grey>DB ID: $invoice_id</span><br>&nbsp;</td>
				";
		print "
	</tr>
	</table><!--table_invoice_line-->
	";
} # print_invoice_line()

function do_post_invoice()
{
	global $client2_id;
	global $invoice_id;
	global $inv_type;
	global $inv_num;
	global $inv_net;
	global $inv_dt;
	global $sqlFalse;
	#global $sqlTrue;
	
	if (!(0 < $client2_id))
	{
		dprint("Error: Client ID is not specified", true);
		return 0;
	}
	
	if ($inv_type == 'I')
		$inv_type_txt = 'Invoice';
	elseif ($inv_type == 'C')
		$inv_type_txt = 'Credit';
	else
	{
		dprint("Error: \"Invoice\"/\"Credit\" is not specified", true);
		return 0;
	}
	
	if ($inv_num == '')
	{
		#$sql = "SELECT MAX(INV_NUM) FROM INVOICE";
		#sql_execute($sql);
		#while (($newArray = sql_fetch()) != false)
		#	$inv_num = $newArray[0] + 1;
		$inv_num = inv_num_next();
	}
	else
	{
		if (!(0 < $inv_num))
		{
			dprint("Error: \"$inv_type_txt number\" should be a number greater than zero", true);
			return 0;
		}
		$sql = "SELECT COUNT(*) FROM INVOICE WHERE (INV_NUM=$inv_num) AND (OBSOLETE=$sqlFalse)";#(INV_TYPE='$inv_type') AND 
		sql_execute($sql);
		$count = 0;
		while (($newArray = sql_fetch()) != false)
			$count = $newArray[0];
		if (0 < $count)
		{
			dprint("Error: There is already a invoice/credit in the database with an \"Invoice number\" of $inv_num", true);#$inv_type_txt
			return 0;
		}
	}
	
	if (!(0.0 < $inv_net))
	{
		dprint("Error: \"Amount received\" should be a number greater than zero", true);
		return 0;
	}

	if ($inv_dt == '')
		$inv_dt = date_now_sql();
	elseif (!date_sql_valid($inv_dt))
	{
		dprint("Error: \"Date received\" should be a valid date e.g. 31/12/2000", true);
		return 0;
	}
	
	$fields = "CLIENT2_ID,  INV_SYS, INV_NUM,  INV_NET,  INV_DT,    INV_TYPE,    IMPORTED";
	$values = "$client2_id, 'G',     $inv_num, $inv_net, '$inv_dt', '$inv_type', $sqlFalse";
	$sql = "INSERT INTO INVOICE ($fields) VALUES ($values)";
	audit_setup_gen('INVOICE', 'INVOICE_ID', 0, '', '');
	$invoice_id = sql_execute($sql, true); # audited

	$fields = "INVOICE_ID,  INV_NUM,  BL_SYS, BL_LPOS, BL_COST,  IMPORTED";
	$values = "$invoice_id, $inv_num, 'G',    1,       $inv_net, $sqlFalse";
	$sql = "INSERT INTO INV_BILLING ($fields) VALUES ($values)";
	audit_setup_gen('INV_BILLING', 'INV_BILLING_ID', 0, '', '');
	$inv_billing_id = sql_execute($sql, true); # audited

	$inv_billing_id=$inv_billing_id; # keep code-checker quiet
	
	return $invoice_id;
	
} # do_post_invoice()

function print_invoices()
{
	#global $ac;
	global $ar;
	global $client_archived; # from print_post_form()
	global $client_name;
	global $client2_id;
	global $grey;
	global $tr_colour_1;
	global $tr_colour_2;

	list($count,$invoices) = ((0 < $client2_id) ? sql_get_invoices('g', 'ic', "*{$client2_id}", '') : array(0,array()));
	#dprint(substr(print_r($invoices,1),0,800));#
	$count=$count; # keep code-checker quiet
	
	print "
	<div id=\"div_invoices\" style=\"display:" . ((0 < $client2_id) ? 'block' : 'none') . "\">

	<form name=\"form_invoices\" action=\"" . server_php_self() . "\" method=\"post\">
		" . input_hidden('client2_id', $client2_id) . "
		" . input_hidden('task', '') . "
		" . input_hidden('invoice_id', '') . "
	<h4>General Invoices &amp; Credits for client $client_name</h4>
	" . input_button('Refresh', 'refresh_list()') . "
	<table name=\"table_invoices\" type=\"spaced_table\">
	<tr>
		<th>Type</th><th>Number</th><th>Date</th><th>Net</th><th>VAT</th><th>Gross</th><th>Paid</th><th>&nbsp;</th>
		" . (user_debug() ? "<th $grey>DB ID</th>" : '') . "
	<tr>
	";
	$trcol = $tr_colour_1;
	foreach ($invoices as $one)
	{
		$id = $one['INVOICE_ID'];
		if ($one['INV_TYPE'] == 'I')
			$type = 'Invoice';
		elseif ($one['INV_TYPE'] == 'C')
			$type = 'Credit';
		else
			$type = '*Error*';
		
		$net = floatval($one['INV_NET']);
		$vat = floatval($one['INV_VAT']);
		$gross = $net + $vat;
		$paid = (($one['INV_TYPE'] == 'I') ? floatval($one['INV_PAID']) : '');
		$col = ( (($one['INV_TYPE'] == 'I') && ($paid < $gross)) ? "style=\"color:blue;\"" : '');
		print "
		<tr bgcolor=\"$trcol\" onmouseover=\"this.className='Highlight'\" onmouseout=\"this.className='Normal'\">
			<td $col>$type</td>
			<td $col $ar>{$one['INV_NUM']}</td>
			<td $col $ar>" . date_for_sql($one['INV_DT'], true, false, true) . "</td>
			<td $col $ar>" . money_format_kdb($net, true, true, true) . "</td>
			<td $col $ar>" . money_format_kdb($vat, true, true, true) . "</td>
			<td $col $ar>" . money_format_kdb($gross, true, true, true) . "</td>
			<td $col $ar>" . (($paid === '') ? '' : money_format_kdb($paid, true, true, true)) . "</td>
			" . ($client_archived ? '' : ("<td>" . input_button("Edit", "edit_invoice($id)") . "</td>")) . "
			" . (user_debug() ? "<td $grey $ar>$id</td>" : '') . "
		</tr>
		";
		$trcol = (($trcol == $tr_colour_1) ? $tr_colour_2 : $tr_colour_1);
		#dprint(print_r($one,1));
	}
	print "
	</table><!--table_invoices-->
	</form><!--form_invoices-->
	
	</div><!--div_invoices-->
	";
} # print_post_form()

//function print_edit_invoice()
//{
//	global $ac;
//	global $ar;
//	global $client2_id;
//	global $grey;
//	global $invoice_id;
//	global $style_r;
//	
//	if (!(0 < $client2_id))
//	{
//		dprint("Error: print_edit_invoice(): no client specified!", true);
//		return;
//	}
//	if (!(0 < $invoice_id))
//	{
//		dprint("Error: print_edit_invoice(): no invoice specified!", true);
//		return;
//	}
//	
//	# --- Get underlying data ----------------
//	
//	sql_encryption_preparation('CLIENT2');
//	$sql = "SELECT C_CODE, " . sql_decrypt('C_CO_NAME', '', true) . " FROM CLIENT2 WHERE CLIENT2_ID=$client2_id";
//	sql_execute($sql);
//	while (($newArray = sql_fetch_assoc()) != false)
//		$client_name = "{$newArray['C_CODE']} - {$newArray['C_CO_NAME']}"; # format: "<code> - <name>"
//		
//	list($balance, $invoices, $credits, $focs, $receipts, $adjusts) = sql_get_client_balance_info($client2_id);
//	$balance_info = "Client Account Balance: " . money_format_kdb($balance, true, true, true) . "<br>" .
//					"Invoices&nbsp;" . money_format_kdb($invoices, true, true, true) . ". " .
//					"Credits&nbsp;" . money_format_kdb($credits, true, true, true) . ". " .
//					"FOCs&nbsp;" . money_format_kdb($focs, true, true, true) . ". " .
//					"Receipts&nbsp;" . money_format_kdb($receipts, true, true, true) . ". " .
//					"Credits&nbsp;" . money_format_kdb($adjusts, true, true, true) . ".";
//	
//	$invoice = sql_get_one_invoice($invoice_id);
//	$alloc_rem = 1.0 * $invoice['ALLOC_REM'];
//	$invoices = sql_get_invoices_and_allocs($client2_id, $invoice_id);
//	$allocs = sql_get_invoice_allocs($invoice_id);
//	$doc_type = ($invoice['INV_TYPE'] ? 'Credit' : 'Receipt');
//	
//	$onchange_num = "onchange=\"update_invoice(this,$invoice_id,'n');\"";
//	$onchange_mon = "onchange=\"update_invoice(this,$invoice_id,'m');\"";
//	$onchange_dt = "onchange=\"update_invoice(this,$invoice_id,'d');\"";
//	
//	if ($invoice['ALLOC_REM'] == 0.0)
//		$auto_active = false;
//	else
//		$auto_active = true;
//	$auto_note_allocs = ($auto_active ? 
//		"Note: \"Auto\" will add the \"Outstanding\" amount to the \"Allocated\" amount, and will then automatically refresh the screen"
//		: '');
//	$auto_note_invoices = ($auto_active ? 
//		"Note: \"Auto\" will add the \"Outstanding\" amount to the \"Allocate\" box, and will then automatically refresh the screen"
//		: '');
//	
//	$div_h = 150;
//	$div_w = 700;
//	
//	# --- Display Client Info ---------------------
//	
//	print "
//	" . input_button('Back', 'show_client()') . "&nbsp;&nbsp;&nbsp;" . input_button('Refresh', 'refresh_edit()') . "
//	<h3>{$doc_type} {$invoice['INV_NUM']} for client $client_name</h3>
//	<p>$balance_info</p>
//	<form name=\"form_edit\" action=\"" . server_php_self() . "\" method=\"post\">
//	" . input_hidden('task', '') . "
//	" . input_hidden('client2_id', $client2_id) . "
//	" . input_hidden('invoice_id', $invoice_id) . "
//	";
//	
//	print_invoice_line($invoice_id, $invoice['INV_TYPE'], $onchange_num, $invoice['INV_NUM'], $onchange_num, 
//						$invoice['INV_DT'], $onchange_dt, $invoice['INV_NET'], $onchange_mon, $alloc_rem, '');
//	
//	# --- Display Receipt Allocations done before ---------------------
//	
//	print "
//	<br><br><b>Current Allocations for this $doc_type</b><br>
//	<div id=\"div_allocs\" style=\"height:{$div_h}px; width:{$div_w}px; overflow-y:scroll; border:solid gray 1px;\">
//	";
//	if (count($allocs) == 0)
//		print "<p>There are no allocations yet for this $doc_type</p>";
//	else
//	{
//		print "
//		<table name=\"table_allocs\" class=\"spaced_table\">
//		<tr>
//			<th>Inv. No.</th><th>Inv. Date</th><th>Net &pound;</th><th>VAT &pound;</th><th>Gross &pound;</th>
//			<th>Outstanding</th><th>Allocated</th>" . ($auto_active ? "<th>&nbsp;</th>" : '') . "
//			" . (user_debug() ? "<th $grey>Invoice ID</th><th $grey>Alloc ID</th>" : '') . "
//		</tr>
//		";
//		foreach ($allocs as $one)
//		{
//			$onchange = "onchange=\"update_alloc(this, $invoice_id, {$one['INVOICE_ID']}, {$one['INV_ALLOC_ID']}, 'm');\"";
//			$auto_sum = (1.0 * $one['OUTSTANDING']) + (1.0 * $one['AL_AMOUNT']);
//			if ($alloc_rem < $auto_sum)
//				$auto_sum = $alloc_rem;
//			$auto = "update_alloc_direct($invoice_id, {$one['INVOICE_ID']}, {$one['INV_ALLOC_ID']}, $auto_sum, 'm', 1);";
//			print "
//			<tr>
//				<td $ar>{$one['INV_NUM']}</td>
//				<td $ar>" . date_for_sql($one['INV_DT'], true, false, true) . "</td>
//				<td $ar>" . money_format_kdb($one['INV_NET'], true, true, true) . "</td>
//				<td $ar>" . money_format_kdb($one['INV_VAT'], true, true, true) . "</td>
//				<td $ar>" . money_format_kdb($one['GROSS'], true, true, true) . "</td>
//				<td $ar>" . money_format_kdb($one['OUTSTANDING'], true, true, true) . "</td>
//				<td $ar>" . input_textbox('al_amount', money_format_kdb($one['AL_AMOUNT'], true, true, true), 10, 10, "$onchange $style_r") . "</td>
//				" . ($auto_active ? ("<td>" . input_button('Auto', $auto) . "</td>") : '') . "
//				" . (user_debug() ? "<td $grey $ar>{$one['INVOICE_ID']}</td><td $grey $ar>{$one['INV_ALLOC_ID']}</td>" : '') . "
//			</tr>
//			";
//		}
//		print "
//		</table><!--table_allocs-->
//		";
//	}
//	print "
//	</div><!--div_allocs-->
//	" . ((count($allocs) == 0) ? '' : "$auto_note_allocs<br>") . "
//	";
//	
//	# --- Display Unpaid Invoices with implicit option to create new Allocation records ---------------------
//	
//	print "
//	<br><b>Unpaid Invoices (without allocations) for this client</b><br>
//	<div id=\"div_invoices\" style=\"height:{$div_h}px; width:{$div_w}px; overflow-y:scroll; border:solid gray 1px;\">
//	";
//	if (count($invoices) == 0)
//		print "<p>There are no unpaid invoices (without allocations) for this client</p>";
//	else
//	{
//		print "
//		<table name=\"table_invoices\" class=\"spaced_table\">
//		<tr>
//			<th>Inv. No.</th><th>Inv. Date</th><th>Net &pound;</th><th>VAT &pound;</th><th>Gross &pound;</th>
//			<th>Outstanding</th><th>Allocate</th>" . ($auto_active ? "<th>&nbsp;</th>" : '') . "
//			" . (user_debug() ? "<th $grey>Invoice ID</th><th $grey>Alloc ID</th>" : '') . "
//		</tr>
//		";
//		foreach ($invoices as $one)
//		{
//			$onchange = "onchange=\"update_alloc(this, $invoice_id, {$one['INVOICE_ID']}, 0, 'm');\"";
//			$auto_sum = 1.0 * $one['OUTSTANDING'];
//			if ($alloc_rem < $auto_sum)
//				$auto_sum = $alloc_rem;
//			$auto = "update_alloc_direct($invoice_id, {$one['INVOICE_ID']}, 0, $auto_sum, 'm', 1);";
//			print "
//			<tr>
//				<td $ar>{$one['INV_NUM']}</td>
//				<td $ar>" . date_for_sql($one['INV_DT'], true, false, true) . "</td>
//				<td $ar>" . money_format_kdb($one['INV_NET'], true, true, true) . "</td>
//				<td $ar>" . money_format_kdb($one['INV_VAT'], true, true, true) . "</td>
//				<td $ar>" . money_format_kdb($one['GROSS'], true, true, true) . "</td>
//				<td $ar>" . money_format_kdb($one['OUTSTANDING'], true, true, true) . "</td>
//				<td>" . input_textbox('al_amount', '', 10, 10, "$onchange $style_r") . "</td>
//				" . ($auto_active ? ("<td>" . input_button('Auto', $auto) . "</td>") : '') . "
//				" . (user_debug() ? "<td $grey $ar>{$one['INVOICE_ID']}</td><td $grey $ac>-</td>" : '') . "
//			</tr>
//			";
//		}
//		print "
//		</table><!--table_invoices-->
//		";
//	}
//	print "
//	</div><!--div_invoices-->
//	" . ((count($invoices) == 0) ? '' : $auto_note_invoices) . "
//	";
//
//	print "
//		<br><br>" . input_button("Delete this $doc_type", "del_invoice($invoice_id)") . "
//	</form><!--form_edit-->
//	";
//	
//} # print_edit_invoice()

function print_goto_form()
{
	print "
	<form name=\"form_goto_invoice\" action=\"ledger.php\" method=\"post\" target=\"_blank\" rel=\"noopener\">
		" . input_hidden('client2_id', '') . "
		" . input_hidden('invoice_id', '') . "
		" . input_hidden('sc_type', '') . "
		" . input_hidden('task', '') . "
	</form><!--form_goto_invoice-->
	";
	
} # print_goto_form()

?>
