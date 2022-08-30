<?php

include_once("settings.php");
include_once("library.php");
include_once("lib_pdf.php");
include_once("lib_pm.php");

global $denial_message;
global $navi_1_finance;
global $navi_2_fin_ledger;
global $role_man;
global $USER; # set by admin_verify()

$subdir = 'search';

log_open("vilcol.log");

sql_connect();

admin_verify(); # writes to $USER

if ($USER['IS_ENABLED'])
{
	if (role_check('*', $role_man))
	{
		$navi_1_finance = true; # settings.php; used by navi_1_heading()
		$navi_2_fin_ledger = true; # settings.php; used by navi_2_heading()
		$onload = "onload=\"set_scroll();\"";
		$page_title_2 = 'Invoices - Vilcol';
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
	global $ar;
	global $can_edit;
	global $col2;
	global $col3;
	global $col4;
	global $export;
	global $inv_billing_id;
	global $invoice_id;
	global $manager_a;
	global $page_title_2;
	global $receipt_id;
	global $role_agt;
	global $role_man;
	global $sc_amt;
	global $sc_appr;
	global $sc_client;
	global $sc_cover_fr; # covering date
	global $sc_cover_to; # covering date
	global $sc_date_fr; # issue date
	global $sc_date_to; # issue date
	global $sc_emailed;
	global $sc_exact;
	global $sc_late;
	global $sc_out;
	global $sc_posted;
	global $sc_stmt;
	global $sc_sys;
	global $sc_text;
	global $sc_type;
	#global $systems; # settings.php
	global $sz_date;
	global $task;
	global $tr_colour_1;
	
	print "<h3>Finance</h3>";
	navi_2_heading(); # secondary navigation buttons
	print "<h3>Invoices, Credits, Receipts &amp; Adjustments</h3>";
	
	dprint(post_values());

	$manager_a = role_check('a', $role_man);
	$can_edit = $manager_a;
	$agent_c = role_check('c', $role_agt);
	$agent_t = role_check('t', $role_agt);

	if (post_val('search_clicked'))
		$task = "search";
	else
		$task = post_val('task');
	$sc_client = post_val('sc_client');
	$sc_text = post_val('sc_text', false, false, false, 1);
	$doctype = post_val('doctype');
	$invoice_id = post_val('invoice_id', true);
	$receipt_id = 0;
	$inv_billing_id = 0;
	$export = post_val('export');
	
	if ($task == 'search_client2_id')
	{
		$invoice_id = 0;
		$client2_id = post_val('client2_id', true);
		if (0 < $client2_id)
		{
			$client_name = client_name_from_id($client2_id);
			$sc_client = $client_name['C_CODE'];
		}
		$task = 'search';
	}
	elseif ($task == 'view_by_invoice_id')
	{
		if (0 < $invoice_id)
			$sc_text = "*{$invoice_id}";
		$task = 'view';
	}
	elseif ($task == 'edit_by_invoice_id')
	{
		if (0 < $invoice_id)
			$sc_text = "*{$invoice_id}";
		$task = 'edit';
	}
	elseif ($task == 'add_gen_billing_line')
	{
		$inv_billing_id = sql_add_gen_billing_line($invoice_id);
		$task = 'edit';
	}
//	elseif ($task == 'edit_gen_billing_line')
//	{
//		$inv_billing_id = post_val('inv_billing_id', true);
//		$task = 'edit';
//	}
	elseif ($task == 'set_net_to_billing')
	{
		sql_set_invoice_net_to_billing($invoice_id);
		$task = 'edit';
	}
	elseif ($task == 'delete_invoice')
	{
		sql_delete_invoice($invoice_id);
		$task = 'edit';
	}
	elseif ($task == 'link_ic')
	{
		link_ic();
		$task = 'search';
	}
	elseif ($task == 'send_invoices_1')
	{
		$invoice_id = 0;
		send_invoices_1();
	}
	elseif ($task == 'send_invoices_2')
	{
		$invoice_id = 0;
		send_invoices_2();
	}
	elseif ($export == 'pdf')
	{
		pdf_create_doc_financial($doctype, $invoice_id);
	}
	else
		$invoice_id = 0;
	
	$systems2 = array('g' => 'General');
	if ($agent_t)
		$systems2['t'] = 'Trace';
	if ($agent_c)
		$systems2['c'] = 'Collect';
	$sc_sys = strtolower(post_val('sc_sys'));
	if (strlen($sc_sys) > 1)
		$sc_sys = substr($sc_sys, 0, 1);
	if (!array_key_exists($sc_sys, $systems2))
		$sc_sys = '';
	
	$sc_type = strtolower(post_val('sc_type'));
	$sc_stmt = post_val('sc_stmt', true);
	$sc_appr = post_val('sc_appr', true);
	$sc_out = post_val('sc_out', true);
	$sc_emailed = post_val('sc_emailed', true);
	$sc_posted = post_val('sc_posted', true);
	if ((!$sc_type) && (($sc_stmt == 1) || $sc_appr || $sc_out || $sc_emailed || $sc_posted))
		$sc_type = 'i';
	else
	{
		if (3 < strlen($sc_type))
			$sc_type = substr($sc_type, 0, 3);
	}
	if (($sc_type == 'r') || ($sc_type == 'a') || ($sc_type == 'ra')) # Receipt/
		$sc_sys = ''; # Receipt/Adjustments are global, not system-specific

	if (count($_POST) == 0)
	{
		$sc_date_fr = date_now(true, date_last_month(1), false); # last month plus one day
		$sc_date_to = '';# date_now(true, '', false);
		$sc_cover_fr = '';
		$sc_cover_to = '';
		$task = 'search';
	}
	else
	{
		$sc_date_fr = post_val('sc_date_fr', false, true, false, 1);
		$sc_date_to = post_val('sc_date_to', false, true, false, 1);
		$sc_cover_fr = post_val('sc_cover_fr', false, true, false, 1);
		$sc_cover_to = post_val('sc_cover_to', false, true, false, 1);
	}
	$calendar_names = array();

	if (($doctype == 'i') && (!(0 < $invoice_id)))
		$invoice_id = post_val('invoice_id', true);
	elseif (($doctype == 'r') && (!(0 < $invoice_id)))
		$receipt_id = post_val('invoice_id', true);
	
	$sc_amt = post_val('sc_amt', true);
	$sc_exact = trim(str_replace('£', '', post_val('sc_exact')));
	if ($sc_exact != '')
		$sc_exact = floatval($sc_exact);
	$sc_late = post_val('sc_late');
	if ($sc_late != '')
		$sc_late = floatval($sc_late);
	
	if ((count($_POST) <= 4) && (post_val('task') == 'view') && (0 < post_val('invoice_id',true)) && (post_val('doctype') == 'i'))
	{
		$task = 'view';
		$sc_text = "*" . post_val('invoice_id',true);
		$sc_type = post_val('doctype');
	}
	
	$onchange = "onchange=\"drop_xl_button()\"";
	$onkeydown = "onkeypress=\"drop_xl_button()\"";# onkeydown causes a backspace to delete two characters the first time it is used!
	$gap = "<td width=\"10\">&nbsp;</td>";
	javascript();
	
	#if (($task != "send_invoices_1") && ($task != "send_invoices_2"))
	if ($task != "send_invoices_2")
	{
		print "
		<div id=\"div_form_main\" style=\"background-color:{$tr_colour_1};" . (($task == "send_invoices_1") ? ' display:none;' : '') . "\">
		<hr>
		<form name=\"form_main\" action=\"" . server_php_self() . "\" method=\"post\">
		" . input_hidden('task', '') . "
		" . input_hidden('doctype', '') . "
		" . input_hidden('invoice_id', '') . "
		" . input_hidden('link_credit_id', '') . "
		" . input_hidden('inv_billing_id', '') . "
		" . input_hidden('export', '') . "
		";
		# The following line causes form submission when ENTER key is pressed.
		print "
		<input type=\"submit\" style=\"display:none\">
		";
		print "
		<table class=\"basic_table\" border=\"0\"><!---->
		<tr>
			<td $ar>Search for doc number:</td>
			<td>" . input_textbox('sc_text', $sc_text, $sz_date, 10, $onkeydown) . "</td>
			<td $col2 $ar>Client Code/Name:</td>
			<td $col2>" . input_textbox('sc_client', $sc_client, 20, 100, $onkeydown) . "</td>
			<td $ar>Doc. Type:</td>
			<td>" . input_select('sc_type', 
									array('i' => 'Invoice', 'c' => 'Credit', 'ic' => 'Inv. & Cre.', 'f' => 'FOC',
											'r' => 'Receipt', 'a' => 'Adjustment', 'ra' => 'Rec. & Adj.'), 
									$sc_type, $onchange, false, false) . "</td>
			$gap
			<td $ar>System:</td>
			<td>" . input_select('sc_sys', $systems2, $sc_sys, $onchange, false, false) . "</td>
			$gap
			<td>" . input_button('Search', 'search_js(1)') . "</td>
			$gap
			<td $col3>...or: " . input_button('Show all documents', 'search_js(0)') . "</td>
		</tr>
		<tr>
			";
			$calendar_names[] = "sc_date_fr";
			$calendar_names[] = "sc_date_to";
			print "
			<td $ar>Issued from:</td>
			<td>" . input_textbox('sc_date_fr', $sc_date_fr, $sz_date, 10, $onkeydown) . calendar_icon('sc_date_fr') . "</td>
			$gap
			<td $ar>Issued to:</td>
			<td>" . input_textbox('sc_date_to', $sc_date_to, $sz_date, 10, $onkeydown) . calendar_icon('sc_date_to') . "</td>
			$gap
			<td $ar>Amount:</td>
			<td>" . input_select('sc_amt', array('1' => 'Zero', '-1' => 'Non-zero'), $sc_amt, $onchange, false, false) . "</td>
			$gap
			<td $ar>Exact Amt:</td>
			<td>" . input_textbox('sc_exact', ($sc_exact == '') ? '' : money_format_kdb($sc_exact, true, true, true), $sz_date, 10, $onkeydown) . "</td>
			<td>&nbsp;</td>
			<td>" . input_button('Clear', 'clear_filters()') . "</td>
			$gap
			<td $col4>" . input_button('Overdue invoices', 'overdue_invoices()') . "&nbsp;&nbsp;&nbsp;&nbsp;
						" . input_button('Invoices not sent', 'invoices_not_sent()') . "</td>
		</tr>
		<tr>
			";
			$calendar_names[] = "sc_cover_fr";
			$calendar_names[] = "sc_cover_to";
			print "
			<td $ar>Covering from:</td>
			<td>" . input_textbox('sc_cover_fr', $sc_cover_fr, $sz_date, 10, $onkeydown) . calendar_icon('sc_cover_fr') . "</td>
			$gap
			<td $ar>Covering to:</td>
			<td>" . input_textbox('sc_cover_to', $sc_cover_to, $sz_date, 10, $onkeydown) . calendar_icon('sc_cover_to') . "</td>
			$gap
			<td $ar>Outstanding:</td>
			<td>" . input_select('sc_out', array('1' => 'Zero', '-1' => 'Non-zero'), $sc_out, $onchange, false, false) . "</td>
			$gap
			<td $ar>Days late:</td>
			<td>" . input_textbox('sc_late', $sc_late, $sz_date, 10, $onkeydown) . "</td>
			$gap
			<td $col2 $ar>Approved:</td>
			<td>" . input_select('sc_appr', array('1' => 'Approved', '-1' => 'Not Aprvd'), $sc_appr, $onchange, false, false) . "</td>
			$gap
			<td $ar>Statements:</td>
			<td>" . input_select('sc_stmt', array('1' => 'Statement', '-1' => 'Not Stmt'), $sc_stmt, $onchange, false, false) . "</td>
		</tr>
		<tr>
			<td $ar>Emailed:</td>
			<td>" . input_select('sc_emailed', array('1' => 'Emailed out', '-1' => 'Not emailed'), $sc_emailed, $onchange, false, false) . "</td>
			$gap
			<td $ar>Posted:</td>
			<td>" . input_select('sc_posted', array('1' => 'Posted out', '-1' => 'Not Posted'), $sc_posted, $onchange, false, false) . "</td>
		</tr>
		</table>
		<hr>
		</form><!--form_main-->
		</div><!--div_form_main-->
		";
	}
	
	print "
	<form name=\"form_csv_download\" action=\"csv_dl.php\" method=\"post\" target=\"_blank\" rel=\"noopener\">
		<input type=\"hidden\" name=\"short_fname\" value=\"\" />
		<input type=\"hidden\" name=\"full_fname\" value=\"\" />
	</form><!--form_csv_download-->
	";
	
	if ($task == 'search')
		print_invoices();
	elseif (($task == 'view') || ($task == 'edit'))
	{
		if ($invoice_id > 0)
			print_one_invoice(($task == 'edit') ? true : false);
		elseif ($receipt_id > 0)
			print_one_receipt(($task == 'edit') ? true : false);
		print_minor_forms();
	}
	
	print "
	<script type=\"text/javascript\">
	document.getElementById('page_title').innerHTML = '$page_title_2';
	</script>
	";
}

function screen_content_2()
{
	# This is required by screen_layout()
	# Do things that depend on the main_screen_div being displayed

	global $sc_client;
	global $sc_text;
	global $task;
	
	if ($task == 'search')
	{
		if ($sc_text)
			print "
			<script type=\"text/javascript\">
			document.getElementById('sc_text').focus();
			document.getElementById('sc_text').setSelectionRange(100, 100); // any number larger than length of content
			</script>
			";
		elseif ($sc_client)
			print "
			<script type=\"text/javascript\">
			document.getElementById('sc_client').focus();
			document.getElementById('sc_client').setSelectionRange(100, 100); // any number larger than length of content
			</script>
			";
	}
	
} # screen_content_2()

function javascript()
{
	global $invoice_id;
	global $receipt_id;
	global $safe_amp;
	global $safe_pound;
	global $safe_slash;
	global $task;
	global $uni_pound;
	
	print "
	<script type=\"text/javascript\">
	
	function could_tick(control,inv_id)
	{
		var el_name = 'send_' + inv_id;
		var el = document.getElementById(el_name);
		if (el)
			el.value = (control.checked ? 1 : 0);
		else
			alert('Could not find element ' + el_name);
	}
	
	function email_back()
	{
		document.form_send.task.value = 'search';
		please_wait_on_submit();
		document.form_send.submit();
	}
	
	function refresh_email_list(invoice_list)
	{
		document.form_send.task.value = 'send_invoices_1';
		document.form_send.invoice_id.value = invoice_list;
		please_wait_on_submit();
		document.form_send.submit();
	}
	
	function invoices_not_sent()
	{
		document.form_main.sc_type.value = 'i';
		document.form_main.sc_emailed.value = -1;
		document.form_main.sc_posted.value = -1;
		document.form_main.task.value = 'search';
		please_wait_on_submit();
		document.form_main.submit();
	}
	
	function overdue_invoices()
	{
		document.form_main.sc_type.value = 'i';
		document.form_main.sc_out.value = -1;
		document.form_main.sc_late.value = 1;
		document.form_main.task.value = 'search';
		please_wait_on_submit();
		document.form_main.submit();
	}
	
	function tick_all()
	{
		var inputs = document.getElementsByTagName('input');
		var did = '';
		var count_y = 0;
		var count_n = 0;
		var chkd = false;
		for (pass = 0; pass < 2; pass++)
		{
			if (pass == 1)
			{
				if (0 < count_n)
					chkd = true;
				else
					chkd = false;
			}
			for (var i = 0; i < inputs.length; i++)
			{
				if (inputs[i].type == 'checkbox')
				{
					if (inputs[i].name.substring(0,4) == 'tck_')
					{
						did = inputs[i].name.replace('tck_','');
						if (isNumeric(did,false,false,false,false))
						{
							if (pass == 0)
							{
								if (inputs[i].checked)
									count_y++;
								else
									count_n++;
							}
							else
								inputs[i].checked = chkd;
						}	
					}
				}
			}
		}
	}

	function tick_valid_emails()
	{
		var inputs = document.getElementsByTagName('input');
		var did = '';
		var count_y = 0;
		for (var i = 0; i < inputs.length; i++)
		{
			if (inputs[i].type == 'checkbox')
			{
				if (inputs[i].name.substring(0,4) == 'tck_')
				{
					did = inputs[i].name.replace('tck_','');
					if (isNumeric(did,false,false,false,false))
					{
						var ev = inputs[i].getAttribute('valid_email');
						if (ev == 1)
						{
							inputs[i].checked = true;
							count_y++;
						}
						else
							inputs[i].checked = false;
					}	
				}
			}
		}
		if (count_y)
			alert('Ticked ' + count_y + ' invoices');
		else
			alert('No invoices were ticked');
	}

	function send_invoices_1()
	{
		var inputs = document.getElementsByTagName('input');
		var did = '';
		var ticked = [];
		var tix = 0;
		var illegal = 0;
		for (var i = 0; i < inputs.length; i++)
		{
			if (inputs[i].type == 'checkbox')
			{
				if (inputs[i].name.substring(0,4) == 'tck_')
				{
					did = inputs[i].name.replace('tck_','');
					if (isNumeric(did,false,false,false,false))
					{
						if (inputs[i].checked)
						{
							if (inputs[i].className == 'ticker inv_type_I')
								ticked[tix++] = did;
							else
								illegal++;
						}
					}
				}
			}
		}
		if (illegal == 0)
		{
			if (0 < ticked.length)
			{
				document.form_main.task.value = 'send_invoices_1';
				document.form_main.invoice_id.value = ticked.toString();
				please_wait_on_submit();
				document.form_main.submit();
			}
			else
				alert('No invoices are ticked, so there is nothing to send');
		}
		else
			alert('Please untick documents that are not invoices.');
	}
	
	function send_invoices_2()
	{
		document.form_send.task.value = 'send_invoices_2';
		please_wait_on_submit();
		document.form_send.submit();
	}
	
	function link_ic()
	{
		var inputs = document.getElementsByTagName('input');
		var doc_id;
		var inv_id = 0;
		var crd_id = 0;
		var illegal = 0;
		var abort = '';
		for (var i = 0; i < inputs.length; i++)
		{
			if (inputs[i].type == 'checkbox')
			{
				doc_id = inputs[i].name.replace('tck_','');
				if (isNumeric(doc_id,false,false,false,false))
				{
					if (inputs[i].checked)
					{
						if (inputs[i].className == 'ticker inv_type_I')
						{
							if (inv_id == 0)
								inv_id = 1 * doc_id;
							else
								abort = 'Cannot link more than one invoice to a credit';
						}
						else if (inputs[i].className == 'ticker inv_type_C')
						{
							if (crd_id == 0)
								crd_id = 1 * doc_id;
							else
								abort = 'Cannot link more than one credit to an invoice';
						}
						else
							illegal++;
					}
				}
			}
			if (abort)
				break;
		}
		if (illegal == 0)
		{
			if ((!abort) && ((inv_id == 0) || (crd_id == 0)))
				abort = 'One invoice and one credit must be ticked';
			if (abort)
			{
				alert(abort);
				return false;
			}
			document.form_main.task.value = 'link_ic';
			document.form_main.invoice_id.value = inv_id;
			document.form_main.link_credit_id.value = crd_id;
			please_wait_on_submit();
			document.form_main.submit();
		}
		else
			alert('Please untick documents that are not invoices or credits.');
	}
	
//	function OLD_link_ic()
//	{
//		var inputs = document.getElementsByClassName('tk_link');
//		var doc_id;
//		var inv_id = 0;
//		var crd_id = 0;
//		var abort = '';
//		for (var i = 0; i < inputs.length; i++)
//		{
//			doc_id = inputs[i].name.replace('lk_','');
//			if (isNumeric(doc_id,false,false,false,false))
//			{
//				if (inputs[i].checked)
//				{
//					if (inputs[i].className == 'tk_link inv_type_I')
//					{
//						if (inv_id == 0)
//							inv_id = 1 * doc_id;
//						else
//							abort = 'Cannot link more than one invoice to a credit';
//					}
//					else if (inputs[i].className == 'tk_link inv_type_C')
//					{
//						if (crd_id == 0)
//							crd_id = 1 * doc_id;
//						else
//							abort = 'Cannot link more than one credit to an invoice';
//					}
//				}
//			}
//			if (abort)
//				break;
//		}
//		if ((!abort) && ((inv_id == 0) || (crd_id == 0)))
//			abort = 'One invoice and one credit must be ticked';
//		if (abort)
//		{
//			alert(abort);
//			return false;
//		}
//		document.form_main.task.value = 'link_ic';
//		document.form_main.invoice_id.value = inv_id;
//		document.form_main.link_credit_id.value = crd_id;
//		document.form_main.submit();
//	}
	
	function approve_invoice(control)
	{
		if (control.checked)
		{
			if (confirm('Do you really want to Approve this invoice/credit?'))
			{
				update_invoice(control,'i','I','t'); // ticked
				setTimeout(create_pdf_invoice, 100); // avoid timing issues
			}
			else
				control.checked = false;
		}
		else
		{
			if (confirm('Do you really want to Un-approve this invoice/credit?'))
			{
				update_invoice(control,'i','I','t'); // unticked
				setTimeout(reload_invoice, 1000); // avoid timing issues
			}
			else
				control.checked = true;
		}
//		if (!control.checked)
//		{
//			var el = document.getElementById('pdf_span_1');
//			if (el)
//				el.innerHTML = '';
//			el = document.getElementById('pdf_span_2');
//			if (el)
//				el.innerHTML = '';
//		}
	}
	
	function create_pdf_invoice()
	{
		create_pdf('i', $invoice_id);
	}
	
	function reload_invoice()
	{
		document.form_main.task.value = 'edit';
		document.form_main.doctype.value = 'i';
		document.form_main.invoice_id.value = $invoice_id;
		please_wait_on_submit();
		document.form_main.submit();
	}
	
	function create_pdf(doctype, docid)
	{
		xmlHttp2 = GetXmlHttpObject();
		if (xmlHttp2 == null)
			return;
		var url = 'ledger_ajax.php?op=appchk&i=' + docid + '&t=' + doctype;
		url = url + '&ran=' + Math.random();
		//alert(url);
		xmlHttp2.onreadystatechange = stateChanged_create_pdf;
		xmlHttp2.open('GET', url, true);
		xmlHttp2.send(null);
	}
	
	function stateChanged_create_pdf()
	{
		if (xmlHttp2.readyState == 4)
		{
			var bits = xprint_noscript(xmlHttp2.responseText).split('|');
			if (bits[0] == '1')
			{
				refresh_filters();
				document.form_main.task.value = '$task';
				document.form_main.export.value = 'pdf';
				document.form_main.invoice_id.value = bits[1];
				document.form_main.doctype.value = bits[2];
				please_wait_on_submit();
				document.form_main.submit();
			}
			else
				alert('The invoice/credit is not yet approved so a PDF cannot be created');
		}
	}
	
	function delete_invoice(iid)
	{
		if (confirm('Do you really want to DELETE this invoice?'))
		{
			refresh_filters();
			document.form_main.task.value = 'delete_invoice';
			document.form_main.invoice_id.value = iid;
			please_wait_on_submit();
			document.form_main.submit();
		}
	}
	
//	function edit_gen_billing_line(bid)
//	{
//		refresh_filters();
//		document.form_main.task.value = 'edit_gen_billing_line';
//		document.form_main.doctype.value = '" . post_val('doctype') . "';
//		document.form_main.invoice_id.value = '" . post_val('invoice_id') . "';
//		document.form_main.inv_billing_id.value = bid;
//		document.form_main.submit();
//	}
	
	function add_gen_billing_line()
	{
		refresh_filters();
		document.form_main.task.value = 'add_gen_billing_line';
		document.form_main.doctype.value = '" . post_val('doctype') . "';
		document.form_main.invoice_id.value = '" . post_val('invoice_id') . "';
		please_wait_on_submit();
		document.form_main.submit();
	}
	
	function set_net_to_billing()
	{
		if (confirm('Do you really want to set the Invoice NET value to the sum of the Billing Line cost values?'))
		{
			refresh_filters();
			document.form_main.task.value = 'set_net_to_billing';
			document.form_main.doctype.value = '" . post_val('doctype') . "';
			document.form_main.invoice_id.value = '" . post_val('invoice_id') . "';
			please_wait_on_submit();
			document.form_main.submit();
		}
	}
	
	function export_xl()
	{
		document.form_main.export.value = 'xl';
		search_js(1);
	}
	
	function drop_xl_button()
	{
		var but_xl = document.getElementById('but_export_xl');
		if (but_xl)
			but_xl.style.visibility = 'hidden';
		search_prepare(0);
	}
	
	function view_job(jid, vilno)
	{
		document.form_job.task.value = 'view';
		document.form_job.sc_text.value = vilno;
		document.form_job.job_id.value = jid;
		document.form_job.submit();
	}
	
	function view_invoice(doctype, iid)
	{
		document.form_invoices.task.value = 'view';
		document.form_invoices.doctype.value = doctype;
		document.form_invoices.invoice_id.value = iid;
		document.form_invoices.submit();
	}
	
	function show_invoices(ccode)
	{
		document.form_invoices.sc_client.value = ccode;
		document.form_invoices.task.value = 'search';
		document.form_invoices.submit();
	}
	
	function goto_client(c2id,ccode)
	{
		document.form_client.client2_id.value = c2id;
		document.form_client.sc_text.value = ccode;
		document.form_client.task.value = 'view';
		document.form_client.submit();
	}
	
	function view_js(type,iid,ed)
	{
		//alert('view_js('+type+','+iid+')');
		if (iid > 0)
		{
			document.form_main.task.value = (ed ? 'edit' : 'view');
			document.form_main.doctype.value = type;
			document.form_main.invoice_id.value = iid;
			//alert('Submitting form_main, id='+iid);
			document.form_main.export.value = '';
			please_wait_on_submit();
			document.form_main.submit();
		}
		//else
		//	alert('Bad ID ' + iid);
	}
	
	function search_prepare(dxl)
	{
		if (dxl == 1)
			drop_xl_button();
		document.form_main.task.value = 'search';
	}
	
	function search_js(useSC)
	{
		if (useSC == 0)
			clear_filters();
		document.form_main.task.value = 'search';
		please_wait_on_submit();
		document.form_main.submit();
	}
	
	function clear_filters()
	{
		document.form_main.sc_text.value = '';
		document.form_main.sc_client.value = '';
		document.form_main.sc_type.value = '';
		document.form_main.sc_sys.value = '';
		document.form_main.sc_date_fr.value = '';
		document.form_main.sc_date_to.value = '';
		document.form_main.sc_cover_fr.value = '';
		document.form_main.sc_cover_to.value = '';
		document.form_main.sc_amt.value = '';
		document.form_main.sc_appr.value = '';
		document.form_main.sc_exact.value = '';
		document.form_main.sc_out.value = '';
		document.form_main.sc_emailed.value = '';
		document.form_main.sc_posted.value = '';
		document.form_main.sc_stmt.value = '';
		document.form_main.sc_late.value = '';
	}
	
	function refresh_filters()
	{
		document.form_main.sc_text.value = '" . post_val('sc_text') . "';
		document.form_main.sc_client.value = '" . post_val('sc_client') . "';
		document.form_main.sc_type.value = '" . post_val('sc_type') . "';
		document.form_main.sc_sys.value = '" . post_val('sc_sys') . "';
		document.form_main.sc_date_fr.value = '" . post_val2('sc_date_fr') . "';
		document.form_main.sc_date_to.value = '" . post_val2('sc_date_to') . "';
		document.form_main.sc_cover_fr.value = '" . post_val2('sc_cover_fr') . "';
		document.form_main.sc_cover_to.value = '" . post_val2('sc_cover_to') . "';
		document.form_main.sc_amt.value = '" . post_val('sc_amt') . "';
		document.form_main.sc_appr.value = '" . post_val('sc_appr') . "';
		document.form_main.sc_exact.value = '" . post_val('sc_exact') . "';
		document.form_main.sc_out.value = '" . post_val('sc_out') . "';
		document.form_main.sc_emailed.value = '" . post_val('sc_emailed') . "';
		document.form_main.sc_posted.value = '" . post_val('sc_posted') . "';
		document.form_main.sc_stmt.value = '" . post_val('sc_stmt') . "';
		document.form_main.sc_late.value = '" . post_val('sc_late') . "';
	}
	
	function update_billing(control, b_id, field_type, date_check)
	{
		" .
		// b_id:
		//				INV_BILLING.INV_BILLING_ID
		// field_type (data type):
		//				blank or x = default (text); no extra processing
		//				d = Date, optionally send date_check e.g. '<=' for on or before today
		//				i = Time, 24 hour clock, with/without colon
		//				m = money (optional '£')
		//				p = percentage (optional '%')
		//				n = Number (allows negatives and decimals)
		//				t = Tickbox
		//				e = Email address
		// date_check:
		//				see 'd' above
		"
		var field_name = control.name;
		var field_value_raw = trim(control.value);
		var field_value = field_value_raw;
		field_value = field_value.replace(/'/g, \"\\'\");
		field_value = field_value.replace(/&/g, \"\\u0026\");
		field_value = field_value.replace(/&/g, \"$safe_amp\");
		field_value = field_value.replace(/\//g, \"$safe_slash\");
		field_value = field_value.replace(/\\n/g, \"\\%0A\");
		
		if (field_type == 'm') // money
		{
			field_value = trim(field_value.replace('£','').replace(/,/g,'').replace(/\\{$uni_pound}/g, ''));
			field_type = 'n';
		}
		else if (field_type == 'p') // percentage
		{
			field_value = trim(field_value.replace('%',''));
			field_type = 'n';
		}
		// don't do any more 'else'
		
		if (field_type == 'd') // date
		{
			if (field_value == '')
				field_value = '__NULL__';
			else if (checkDate(field_value_raw, 'entry', date_check))
				field_value = dateToSql(field_value_raw);
			else
				return false;
		}
		else if (field_type == 'i') // time
		{
			if (field_value == '')
				field_value = '__NULL__';
			else
			{
				var hour = -1;
				var min = -1;
				var sec = -1;

				var fv = field_value.replace(/\\./g,':').replace(/ /g,':');
				if (fv.indexOf(':') == -1)
				{
					if (fv.length == 3)
						fv = fv.substring(0,0) + ':' + fv.substring(1,2);
					else if (fv.length == 4)
						fv = fv.substring(0,1) + ':' + fv.substring(2,3);
				}
				//alert('FV=' + field_value + ', fv=' + fv);
				
				var bits = fv.split(':');
				if (1 < bits.length)
				{
					if (isNumeric(bits[0], false, false, false, false) && isNumeric(bits[1], false, false, false, false))
					{
						if ((0 <= bits[0]) && (bits[0] <= 23) && (0 <= bits[1]) && (bits[1] <= 59))
						{
							if (bits.length == 2)
								sec = 0;
							else if (2 < bits.length)
							{
								if (isNumeric(bits[2], false, false, false, false) && (0 <= bits[2]) && (bits[2] <= 59))
									sec = bits[2];
							}
							if (0 <= sec)
							{
								hour = bits[0];
								min = bits[1];
							}
						}
					}
				}
				//alert('H=' + hour + ', M=' + min);
				
				if (0 <= hour)
				{
					field_value = hour + ':' + min + ':' + sec;
				}
				else
				{
					alert('Please enter a time e.g. 15:45');
					return false;
				}
			}
		}
		else if (field_type == 'n') // number
		{
			if (field_value == '')
				field_value = '__NULL__';
			else if (!isNumeric(field_value, true, true, false, false)) // allow neg and decimal
			{
				alert('Please enter a number');
				return false;
			}
		}
		else if (field_type == 't') // tickbox
			field_value = (control.checked ? '1' : '0');
		else if (field_type == 'e') // email
		{
			if (field_value == '')
			{
				//alert('Please enter an email address');
				//return false;
			}
			else if (!email_valid(field_value))
			{
				alert('The email address is syntactically invalid');
				return false;
			}
		}
		else
		{
			field_type = 'x'; // text
			field_value = field_value.replace(/£/g, \"$safe_pound\");
		}
			
		xmlHttp2 = GetXmlHttpObject();
		if (xmlHttp2 == null)
			return;
		var url = 'ledger_ajax.php?op=ub&ii={$invoice_id}&bi=' + b_id + '&n=' + field_name + '&v=' + field_value + '&ty=' + field_type;
		url = url + '&ran=' + Math.random();
		//alert(url);
		xmlHttp2.onreadystatechange = stateChanged_update_billing;
		xmlHttp2.open('GET', url, true);
		xmlHttp2.send(null);
	}
	
	function stateChanged_update_billing()
	{
		if (xmlHttp2.readyState == 4)
		{
			var resptxt = xprint_noscript(xmlHttp2.responseText);
			if (resptxt)
			{
				bits = resptxt.split('|');
				if (bits[0] == 1)
				{
					if (bits[1] != 'ok')
						alert('stateChanged_update_billing: Ajax returned 1 and: ' + bits[1]);
				}
				else if (bits[0] == -1)
					alert(bits[1]);
				else
					alert('stateChanged_update_billing: Ajax returned: ' + resptxt);
			}
			//else
			//	alert('stateChanged_update_billing: No response from ajax call!');
		}
	}
	
	function update_invoice(control, doc_type, inv_type, field_type, date_check)
	{
		" .
		// doc_type:
		//				i = invoice/credit/FOC
		//				r = receipt/adjustment
		// inv_type:
		//				for doc_type i:
		//					I = invoice
		//					C = credit
		//					F = FOC
		//				for doc_type r:
		//					blank, not used
		// field_type (data type):
		//				blank or x = default (text); no extra processing
		//				d = Date, optionally send date_check e.g. '<=' for on or before today
		//				i = Time, 24 hour clock, with/without colon
		//				m = money (optional '£')
		//				p = percentage (optional '%')
		//				n = Number (allows negatives and decimals)
		//				t = Tickbox
		//				e = Email address
		// date_check:
		//				see 'd' above
		"
		var field_name = control.name;
		var field_value_raw = trim(control.value);
		var field_value = field_value_raw;
		field_value = field_value.replace(/'/g, \"\\'\");
		field_value = field_value.replace(/&/g, \"\\u0026\");
		field_value = field_value.replace(/&/g, \"$safe_amp\");
		field_value = field_value.replace(/\//g, \"$safe_slash\");
		field_value = field_value.replace(/\\n/g, \"\\%0A\");
		
		if (field_type == 'm') // money
		{
			field_value = trim(field_value.replace('£','').replace(/,/g,'').replace(/\\{$uni_pound}/g, ''));
			field_type = 'n';
		}
		else if (field_type == 'p') // percentage
		{
			field_value = trim(field_value.replace('%',''));
			field_type = 'n';
		}
		// don't do any more 'else'
		
		if (field_type == 'd') // date
		{
			if (field_value == '')
				field_value = '__NULL__';
			else if (checkDate(field_value_raw, 'entry', date_check))
				field_value = dateToSql(field_value_raw);
			else
				return false;
		}
		else if (field_type == 'i') // time
		{
			if (field_value == '')
				field_value = '__NULL__';
			else
			{
				var hour = -1;
				var min = -1;
				var sec = -1;

				var fv = field_value.replace(/\\./g,':').replace(/ /g,':');
				if (fv.indexOf(':') == -1)
				{
					if (fv.length == 3)
						fv = fv.substring(0,0) + ':' + fv.substring(1,2);
					else if (fv.length == 4)
						fv = fv.substring(0,1) + ':' + fv.substring(2,3);
				}
				//alert('FV=' + field_value + ', fv=' + fv);
				
				var bits = fv.split(':');
				if (1 < bits.length)
				{
					if (isNumeric(bits[0], false, false, false, false) && isNumeric(bits[1], false, false, false, false))
					{
						if ((0 <= bits[0]) && (bits[0] <= 23) && (0 <= bits[1]) && (bits[1] <= 59))
						{
							if (bits.length == 2)
								sec = 0;
							else if (2 < bits.length)
							{
								if (isNumeric(bits[2], false, false, false, false) && (0 <= bits[2]) && (bits[2] <= 59))
									sec = bits[2];
							}
							if (0 <= sec)
							{
								hour = bits[0];
								min = bits[1];
							}
						}
					}
				}
				//alert('H=' + hour + ', M=' + min);
				
				if (0 <= hour)
				{
					field_value = hour + ':' + min + ':' + sec;
				}
				else
				{
					alert('Please enter a time e.g. 15:45');
					return false;
				}
			}
		}
		else if (field_type == 'n') // number
		{
			if (field_value == '')
				field_value = '__NULL__';
			else if (!isNumeric(field_value, true, true, false, false)) // allow neg and decimal
			{
				alert('Please enter a number');
				return false;
			}
		}
		else if (field_type == 't') // tickbox
			field_value = (control.checked ? '1' : '0');
		else if (field_type == 'e') // email
		{
			if (field_value == '')
			{
				//alert('Please enter an email address');
				//return false;
			}
			else if (!email_valid(field_value))
			{
				alert('The email address is syntactically invalid');
				return false;
			}
		}
		else
		{
			field_type = 'x'; // text
			field_value = field_value.replace(/£/g, \"$safe_pound\");
		}
			
		xmlHttp2 = GetXmlHttpObject();
		if (xmlHttp2 == null)
			return;
		var op = '';
		var obj_id = 0;
		if (doc_type == 'i')
		{
			op = 'ui';
			obj_id = {$invoice_id};
		}
		else if (doc_type == 'r')
		{
			op = 'ur';
			obj_id = {$receipt_id};
		}
		else
		{
			op = 'x';
			obj_id = -1;
		}
		var url = 'ledger_ajax.php?op=' + op + '&i=' + obj_id + '&t=' + inv_type + '&n=' + field_name + '&v=' + field_value + '&ty=' + field_type;
		url = url + '&ran=' + Math.random();
		//alert(url);
		xmlHttp2.onreadystatechange = stateChanged_update_invoice;
		xmlHttp2.open('GET', url, true);
		xmlHttp2.send(null);
	}
	
	function stateChanged_update_invoice()
	{
		if (xmlHttp2.readyState == 4)
		{
			var resptxt = xprint_noscript(xmlHttp2.responseText);
			if (resptxt)
			{
				bits = resptxt.split('|');
				if (bits[0] == 1)
				{
					if (bits[1] == 'amounts')
					{
						document.getElementById('inv_net').value = '£' + bits[2];
						document.getElementById('x_vat_rate').value = bits[3] + '%';
						document.getElementById('inv_vat').value = '£' + bits[4];
						document.getElementById('x_gross').value = '£' + bits[5];
						document.getElementById('x_outst').value = '£' + bits[6];
					}
					else if (bits[1] == 'warning')
						alert('WARNING: ' + bits[2]);
					else if (bits[1] != 'ok')
						alert('stateChanged_update_invoice: Ajax returned 1 and: ' + bits[1]);
				}
				else if (bits[0] == -1)
					alert(bits[1]);
				else
					alert('stateChanged_update_invoice: Ajax returned: ' + resptxt);
			}
			//else
			//	alert('stateChanged_update_invoice: No response from ajax call!');
		}
	}
	
	</script>
	";
}

function print_invoices()
{
	global $ac;
	global $ar;
	global $csv_path;
	global $excel_currency_format;
	global $excel_date_format;
	global $export;
	global $grey;
	global $phpExcel_ext; # settings.php: "xls"
	global $sc_amt;
	global $sc_appr;
	global $sc_client;
	global $sc_cover_fr;
	global $sc_cover_to;
	global $sc_date_fr;
	global $sc_date_to;
	global $sc_emailed;
	global $sc_exact;
	global $sc_late;
	global $sc_out;
	global $sc_posted;
	global $sc_stmt;
	global $sc_sys;
	global $sc_text;
	global $sc_type;
	global $subdir;
	global $tr_colour_1;
	global $tr_colour_2;
	global $USER;
	
	$limit = 1000;
	# Get invoices first, then receipts
	if (($sc_type == 'r') || ($sc_type == 'a') || ($sc_type == 'ra')) # user only wants receipts
	{
		$count_inv = 0;
		$invoices = array();
	}
	else
		list($count_inv, $invoices) = sql_get_invoices($sc_sys, $sc_type, $sc_client, $sc_text, $sc_amt, $sc_exact, $sc_out, $sc_late,
														$sc_date_fr, $sc_date_to, $sc_cover_fr, $sc_cover_to, $sc_stmt, $sc_appr, 
														$sc_emailed, $sc_posted, $limit);
	
	if (
			($sc_type && (!(($sc_type == 'r') || ($sc_type == 'a') || ($sc_type == 'ra')))) || # user doesn't want receipts
			$sc_sys || # user has specified a system, but receipts don't have a system
			(false)#$limit <= $count_inv) # no room left for any receipts
		)
	{
		$count_recp = 0;
		$receipts = array();
	}
	else
		list($count_recp, $receipts) = sql_get_receipts($sc_type, $sc_client, $sc_text, $sc_amt, $sc_exact, $sc_date_fr, $sc_date_to, $limit);#$limit - $count_inv);
	
	if ($invoices || $receipts)
	{
		$export_xl = (($export == 'xl') ? true : false);
		
		if ($export_xl)
		{
			$filters = array();
			if ($sc_sys == 't')
				$filters[] = "Trace documents";
			elseif ($sc_sys == 'c')
				$filters[] = "Collect documents";
			elseif ($sc_sys == 'g')
				$filters[] = "General documents";
			if ($sc_type == 'i')
				$filters[] = "Invoices";
			elseif ($sc_type == 'c')
				$filters[] = "Credits";
			elseif ($sc_type == 'ic')
				$filters[] = "Invoices & Credits";
			elseif ($sc_type == 'f')
				$filters[] = "FOC docs";
			elseif ($sc_type == 'r')
				$filters[] = "Receipts";
			elseif ($sc_type == 'a')
				$filters[] = "Adjustments";
			elseif ($sc_type == 'ra')
				$filters[] = "Receipts & Adjustments";
			if ($sc_date_fr)
				$filters[] = "Issued $sc_date_fr";
			if ($sc_date_to)
				$filters[] = "To $sc_date_to";
			if ($limit)
				$filters[] = "Limited to $limit documents";
			$title = "Finance Search (" . implode(', ', $filters) . ")";
			$title_short = "Finance Search";
			$xfile = "finance_search_{$USER['USER_ID']}";
		}
		else
		{
			$title = '';
			$title_short = '';
			$xfile = '';
		}
		$headings = array('Doc.Type', 'Doc.No.', 'System', 'Date', 'Client', 'Client Name', 'Net', 'VAT', 'Gross', 'Sent', 'Paid', 'Due', 'Statement?');#, 'Alloc');
		
		if ($sc_type == 'i')
			$inv_type = "Invoice";
		elseif ($sc_type == 'c')
			$inv_type = "Credit";
		elseif ($sc_type == 'ic')
			$inv_type = "Invoice/Credit";
		elseif ($sc_type == 'f')
			$inv_type = "FOC";
		elseif ($sc_type == 'r')
			$inv_type = "Receipt";
		elseif ($sc_type == 'a')
			$inv_type = "Adjustment";
		elseif ($sc_type == 'ra')
			$inv_type = "Receipt/Adjustment";
		else
			$inv_type = "Document";
		
		$sub_inv = count($invoices);
		$sub_recp = count($receipts);
		$subcount = $sub_inv + $sub_recp;
		if ($limit < $subcount)
			$subcount = $limit;
		
		$from = (($subcount < ($count_inv + $count_recp)) ? (" from a set of " . number_with_commas($count_inv + $count_recp)) : '');
		$summary = "Showing " . number_with_commas($subcount) . " $inv_type" . (($subcount == 1) ? '' : 's') . "{$from}.";
		$button_gap = "&nbsp;&nbsp;&nbsp;&nbsp;";
		print "
		{$summary}" . (($sc_emailed == -1) ? ($button_gap . input_button('Tick valid email addresses', "tick_valid_emails()")) : '') . # "Not emailed"
					"{$button_gap}" . input_button('Export to Excel', "export_xl()", '', 'but_export_xl') . "
					{$button_gap}" . input_button('Link ticked Invoice &amp; Credit', "link_ic()") . "
					{$button_gap}" . input_button('Email out ticked invoices', "send_invoices_1()") . "
		<br>
		";
		if ($sc_emailed || $sc_posted)
			print "Note: the \"Emailed\" and \"Posted\" filters only return invoices that were NOT imported from the old system.<br>
				";
		print "
		<table name=\"table_main\" class=\"spaced_table\" border=\"0\">
		<tr>
			<th></th>
			<th>" . input_button('All', 'tick_all()') . "</th>
			";
			for ($hix = 0; $hix < count($headings); $hix++)
			{
				if ($headings[$hix] == 'System')
					print "<th>Linked</th>";
				print "<th>{$headings[$hix]}</th>";
			}
			print "
			<th>Imported?</th>
			<th $grey>DB ID</th>
		</tr>
		";
		$trcol = $tr_colour_1;
		$ix_inv = 0;
		$ix_recp = 0;
		
		$datalines = array(); # for export file
		$dl_count = 0;
		
		while (true)
		{
			# Show invoices and receipts in the same list, in descending order of DOCNO.
			
			$empty_inv = (($sub_inv <= $ix_inv) ? true : false);
			$empty_recp = (($sub_recp <= $ix_recp) ? true : false);
			
			if ($empty_inv && $empty_recp)
				break; # from while(true)

			$dt_inv = ($empty_inv ? '' : $invoices[$ix_inv]['INV_DT']);
			$dt_recp = ($empty_recp ? '' : $receipts[$ix_recp]['RC_DT']);
			
			$tck_class = '';
			$linked_num = '';
			#$link_class = '';
			$valid_email = '';
			
			#dprint("dl_count=$dl_count, empty_inv=$empty_inv, empty_recp=$empty_recp, dt_inv=\"$dt_inv\", dt_recp=\"$dt_recp\"");#
			
			if ((!$empty_inv) && ($empty_recp || ($dt_recp <= $dt_inv)))
			{
				# Show an invoice/credit/FOC
				
				$one = $invoices[$ix_inv];
				$col = '';
				
				$net = floatval($one['INV_NET']);
				$vat = floatval($one['INV_VAT']);
				$gross = $net + $vat;
				#$alloc = 1.0 * $one['SUM_AL_AMOUNT'];
				$paid = floatval($one['INV_PAID']);
				$outst = round($gross,2) - round($paid,2);
				
				if ($one['EM_DT'])
					$sent = date_for_sql($one['EM_DT'], true, false, true);
				elseif ($one['INV_POSTED_DT'])
					$sent = date_for_sql($one['INV_POSTED_DT'], true, false, true);
				else
					$sent = '-';
				
				$pal = $ar;
				if ($one['INV_TYPE'] == 'I')
				{
					$inv_type = "Invoice";
					if ((0 < $net) && (round($paid,2) == 0))
					{
						$paid_x = 0;
						#$paid = "Nil";
						#$pal = $ac;
						$paid = money_format_kdb(0, true, true, true);
					}
					else
					{
						$paid_x = $paid;
						$paid = round($paid,2);
						#if ($outst == 0)
						#{
						#	$paid = "In full";
						#	$pal = $ac;
						#}
						#else
							$paid = money_format_kdb($paid, true, true, true);
					}
					if (0 < $outst)
						$col = "style=\"color:blue;\"";
				}
				elseif ($one['INV_TYPE'] == 'C')
				{
					$inv_type = "Credit";
					$paid = '';#"n/a";
					$pal = $ac;
					$paid_x = '';
				}
				elseif ($one['INV_TYPE'] == 'F')
				{
					$inv_type = "FOC";
					$paid = '';#"n/a";
					$pal = $ac;
					$paid_x = '';
				}
				else
				{
					$inv_type = "*Error*";
					$paid = "**";
					$pal = $ac;
					$paid_x = '';
				}
				
				$net_x = $net;
				$net = money_format_kdb($net, true, true, true);
				$vat_x = $vat;
				$vat = money_format_kdb($vat, true, true, true);
				$gross_x = $gross;
				$gross = money_format_kdb($gross, true, true, true);
				#$alloc_x = $alloc;
				#$alloc = money_format_kdb($alloc_x, true, true, true);
	
				if ($one['INV_SYS'] == 'G')
					$system = 'General';
				elseif ($one['INV_SYS'] == 'T')
					$system = 'Trace';
				elseif ($one['INV_SYS'] == 'C')
					$system = 'Collect';
				else
					$system = '*Error*';
				if ($one['INV_STMT'] == 1)
					$statement = 'Stmt';
				else
					$statement = '';
				if ($one['IMPORTED'] == 1)
				{
					if ($one['INV_SYS_IMP'] == 'T')
						$imported = 'from Trace';
					elseif ($one['INV_SYS_IMP'] == 'C')
						$imported = 'from Collect';
					else
						$imported = '*Error*';
				}
				else
					$imported = '';#'No';

				$date = date_for_sql($one['INV_DT'], true, false, true);
				$date_x = date_for_sql($one['INV_DT'], true, false, false);
				
				$due = date_for_sql($one['INV_DUE_DT'], true, false, true);
				$due_x = date_for_sql($one['INV_DUE_DT'], true, false, false);
				
				$doc_id = intval($one['INVOICE_ID']);
				$doc_num = intval($one['INV_NUM']);
				$c_code = intval($one['C_CODE']);
				$c_co_name = $one['C_CO_NAME'];
				if ($sc_emailed == -1) # "Not emailed"
				{
					if ($one['INV_EMAILED'])
					{
						$inv_email = trim($one['INV_EMAIL_ADDR']);
						if ($inv_email)
						{
							if (email_valid($inv_email))
							{
								$email_line = "<span style=\"color:green;\">$inv_email</span>";
								$valid_email = "valid_email=\"1\"";
							}
							else
								$email_line = "<span style=\"color:red;\">Invalid Email: $inv_email</span>";
						}
						else
							$email_line = "<span style=\"color:brown;\">(no email address)</span>";
					}
					else
						$email_line = "<span style=\"color:brown;\">(client does not want invoices emailed)</span>";
					$c_co_name .= "<br>$email_line";
				}
				
				$tck_class = "class=\"ticker inv_type_{$one['INV_TYPE']}\"";
				if (0 < $one['LINKED_NUM'])
					$linked_num = $one['LINKED_NUM'];
				#else
				#	$link_class = "class=\"tk_link inv_type_{$one['INV_TYPE']}\"";
					
				$doctype = 'i';
				$ix_inv++;
			} # Show an invoice/credit/FOC
			
			elseif ((!$empty_recp) && ($empty_inv || ($dt_inv <= $dt_recp)))
			{
				# Show a receipt
				
				$one = $receipts[$ix_recp];
				$col = '';
				
				$doc_id = intval($one['INV_RECP_ID']);
				$inv_type = (($one['RC_ADJUST'] == 1) ? "Adjustment" : "Receipt");
				$doc_num = intval($one['RC_NUM']);
				$system = '-';
				$date = date_for_sql($one['RC_DT'], true, false, true);
				$date_x = date_for_sql($one['RC_DT'], true, false, false);
				$due = '';
				$due_x = '';
				$c_code = intval($one['C_CODE']);
				$c_co_name = $one['C_CO_NAME'];
				$net = '';
				$net_x = '';
				$vat = '';
				$vat_x = '';
				$gross = '';
				$gross_x = '';
				$paid_x = floatval($one['RC_AMOUNT']);
				$paid = money_format_kdb($paid_x, true, true, true);
				$pal = $ar;
				#$alloc = '';
				#$alloc_x = '';
				$statement = '';
				$sent = '';
				
				if ($one['IMPORTED'] == 1)
				{
					if ($one['RC_SYS_IMP'] == 'T')
						$imported = 'from Trace';
					elseif ($one['RC_SYS_IMP'] == 'C')
						$imported = 'from Collect';
					else
						$imported = '*Error*';
				}
				else
					$imported = '';#'No';
				
				$doctype = 'r';
				$ix_recp++;
			} # # Show a receipt

			# $line_s is line for screen, $line_x is line for export
			$line_s = array($inv_type, $doc_num, $system, $date, $c_code, $c_co_name, $net, $vat, $gross, $sent, $paid, $due, $statement);#, $alloc);
			$line_x = array($inv_type, $doc_num, $system, $date_x, $c_code, $c_co_name, $net_x, $vat_x, $gross_x, $sent, $paid_x, $due_x, $statement);#, $alloc_x);
			$datalines[] = $line_x;
			$dl_count++;

			$ii = 0;
			print "
			<tr bgcolor=\"$trcol\" onmouseover=\"this.className='Highlight'\" onmouseout=\"this.className='Normal'\">
				<td>" . input_button('View', "view_js('$doctype', $doc_id, 0)") . "</td>
				<td $ac>" . input_tickbox('', "tck_{$doc_id}", 1, false, '', "$tck_class $valid_email", false) . "</td>
				<td $col>{$line_s[$ii++]}</td>
				<td $col $ar>{$line_s[$ii++]}</td>
				<td $ac>";
					if ($linked_num)
						print "<span style=\"font-size:12px; color:gray;\">$linked_num</span>";
//					if ($link_class)
//						print input_tickbox('', "lk_{$doc_id}", 1, false, '', $link_class, false);
//					else
//						print "<span style=\"font-size:12px; color:gray;\">$linked_num</span>";
					print "</td>
				<td $col>{$line_s[$ii++]}</td>
				<td $col $ar>{$line_s[$ii++]}</td>
				<td $col $ar>{$line_s[$ii++]}</td>
				<td $col>{$line_s[$ii++]}</td>
				<td $col $ar>{$line_s[$ii++]}</td>
				<td $col $ar>{$line_s[$ii++]}</td>
				<td $col $ar>{$line_s[$ii++]}</td>
				<td $col $ar>{$line_s[$ii++]}</td>
				<td $col $pal>{$line_s[$ii++]}</td>
				<td $col $ar>{$line_s[$ii++]}</td>
				<td $col $ac>{$line_s[$ii++]}</td>
				" .
//				"
//				<td $ar>{$line_s[$ii++]}</td>
//				" .
				"
				<td $col>$imported</td>
				<td $ar $grey>$doc_id</td>
			</tr>
			";
			$trcol = (($trcol == $tr_colour_1) ? $tr_colour_2 : $tr_colour_1);
			
			if ((0 < $limit) && ($limit <= $dl_count))
				break;
		} # while(true)
		
		print "
		</table><!--table_main-->
		";
		
		if ($export == 'xl')
		{
			$top_lines = array(array($title), array($summary), array());
			$formats = array('D' => $excel_date_format, 'G' => $excel_currency_format, 'H' => $excel_currency_format, 
								'I' => $excel_currency_format, 'J' => $excel_currency_format);
			$sheet = array($title_short, $top_lines, $headings, $datalines, $formats, '');
			phpExcel_output($subdir, $xfile, array($sheet)); # library.php

			# Auto-download file to PC
			print "
			<script type=\"text/javascript\">
				document.form_csv_download.short_fname.value = '$xfile.$phpExcel_ext';
				document.form_csv_download.full_fname.value = '$csv_path' + '{$subdir}/$xfile.$phpExcel_ext';
				document.form_csv_download.submit();
			</script>
			";
		}
	}
	elseif ($sc_sys || $sc_type || $sc_client || $sc_text || $sc_amt || $sc_appr || $sc_exact || $sc_out || $sc_emailed || $sc_posted || $sc_late || 
			$sc_date_fr || $sc_date_to || $sc_cover_fr || $sc_cover_to || $sc_stmt)
		dprint("No documents matched your search.", true);
	else 
		dprint("There are no documents in the database.", true);
}

function print_one_invoice($editing)
{
	# Invoice or Credit
	
	global $ac;
	global $ar;
//	global $ascii_pound;
	global $at;
	global $can_edit;
	global $col2;
	global $col3;
	global $col4;
	global $col5;
	global $col8;
	global $grey;
	global $id_ROUTE_direct;
	global $id_ROUTE_fwd;
	global $id_ROUTE_tous;
	#global $inv_billing_id;
	global $invoice_id;
	global $manager_a;
	global $page_title_2;
//	global $pound_194;
	global $style_r;
	global $SYSTEMS; # settings.php
	#global $sz_date; # settings.php
//	global $uni_pound;
	
	$global_debug = global_debug();
	#dprint("editing=$editing, can_edit=$can_edit, manager_a=$manager_a");#
	$logging = false;#
	if ($logging) log_write("print_one_invoice($editing): enter");
	
	if ($editing && (!$can_edit))
		$editing = false;
	if (!$manager_a)
	{
		$can_edit = false;
		$editing = false;
	}

	$invoice = sql_get_one_invoice($invoice_id);
	if ($logging) log_write("...done sql_get_one_invoice($invoice_id)");
	
	#dprint("PAID={$invoice['INV_PAID']}");#
	$deleted = $invoice['OBSOLETE'] ? true : false;
	if ($deleted)
		$editing = false;
	
	$unapproved = $invoice['INV_APPROVED_DT'] ? false : true;
	
	if ($unapproved)
	{
		$pdf_url = '';
		#$pdf_button = '';
	}
	else
	{
		$pdf_url = pdf_link('i', "c{$invoice['C_CODE']}", $invoice['INV_NUM']);
		if ($logging) log_write("...done pdf_link()");
		#if ($pdf_url)
		#	$pdf_button = 'Re-create PDF';
		#else
		#	$pdf_button = 'Create PDF';
	}
	#dprint("pdf_url=\"$pdf_url\"");#
	
	$show_del_button = (($editing && $unapproved && (!$deleted)) ? true : false);
	if ($invoice['C_ARCHIVED'])
		$read_only = "its client is archived";
//	elseif ($invoice['INV_EMAIL_ID'])
//		$read_only = "it has already been emailed to the client";
//	elseif ($invoice['INV_POSTED_DT'])
//		$read_only = "it has already been posted to the client";
//	elseif ($invoice['INV_PAID'])
//		$read_only = "it has been fully or partly paid";
//	elseif ($invoice['IMPORTED'])
//		$read_only = "it is imported from the old database";
	elseif ($deleted)
		$read_only = "it is deleted";
	else
		$read_only = '';
	if ($read_only)
		$editing = false; # but don't unset $can_edit or $show_del_button
	
	$inv_type = $invoice['INV_TYPE']; # I, C or F
	
	$client_can_be_changed = false;
	if ($client_can_be_changed)
	{
		$clients = sql_get_clients_for_select('*', $invoice['CLIENT2_ID']); #$client_sys);
	}
	else
		$clients = array($invoice['CLIENT2_ID'] => "{$invoice['C_CODE']} - {$invoice['C_CO_NAME']}");
	if ($logging) log_write("...done sql_get_clients_for_select()");
	
	list($balance, $invoices, $credits, $focs, $receipts, $adjusts) = sql_get_client_balance_info($invoice['CLIENT2_ID']);
	$balance_info = "Account Balance: " . money_format_kdb($balance, true, true, true) . ". " .
					"Invoices " . money_format_kdb($invoices, true, true, true) . ". " .
					"Credits " . money_format_kdb($credits, true, true, true) . ". " .
					"FOCs " . money_format_kdb($focs, true, true, true) . ". " .
					"Receipts " . money_format_kdb($receipts, true, true, true) . ". " .
					"Adjustments " . money_format_kdb($adjusts, true, true, true) . ".";
	if ($logging) log_write("...done sql_get_client_balance_info()");
	
	if ($inv_type == 'I')
	{
		$inv_type_txt = "Invoice";
		$inv_dt_txt = "Invoice Issued";
	}
	elseif ($inv_type == 'C')
	{
		$inv_type_txt = "Credit";
		$inv_dt_txt = "Credit Issued";
	}
	elseif ($inv_type == 'F')
	{
		$inv_type_txt = "FOC";
		$inv_dt_txt = "FOC Issued";
	}
	else
	{
		$inv_type_txt = "*Error*";
		$inv_dt_txt = "*Error*";
	}
	$page_title_2 = "{$inv_type_txt} {$invoice['INV_NUM']} - Vilcol";
	if ($deleted)
		$inv_type_txt = "<span style=\"color:red;\">*DELETED* $inv_type_txt</span>";
	
	if ($invoice['INV_SYS'] == 'G')
	{
		$inv_sys = 'General';
		#$client_sys = '*';
	}
	elseif ($invoice['INV_SYS'] == 'T')
	{
		$inv_sys = 'Trace';
		#$client_sys = 't';
	}
	elseif ($invoice['INV_SYS'] == 'C')
	{
		$inv_sys = 'Collect';
		#$client_sys = 'c';
	}
	else
	{
		$inv_sys = '*Error*';
		#$client_sys = 'x';
	}

	$net = floatval($invoice['INV_NET']);
	$vat_amount = floatval($invoice['INV_VAT']);
	$vat_rate = ($net ? round(100.0 * floatval($vat_amount) / $net, 1) : 0.0);
	$gross = round($net,2) + round($vat_amount, 2);
	$paid = round(floatval($invoice['INV_PAID']), 2);
	$outst = round($gross - $paid, 2);
	
	#$onchange_txt = (($editing && $unapproved) ? "onchange=\"update_invoice(this,'i','$inv_type');\"" : 'readonly');
	$onchange_txt_x = ($editing ? "onchange=\"update_invoice(this,'i','$inv_type');\"" : 'readonly');
	$onchange_num = (($editing && $unapproved) ? "onchange=\"update_invoice(this,'i','$inv_type','n');\"" : 'readonly');
	$onchange_num_x = ($editing ? "onchange=\"update_invoice(this,'i','$inv_type','n');\"" : 'readonly');
	$onchange_dt = (($editing && $unapproved) ? "onchange=\"update_invoice(this,'i','$inv_type','d');\"" : 'readonly');
	$onchange_dt_x = ($editing ? "onchange=\"update_invoice(this,'i','$inv_type','d');\"" : 'readonly');
	$onchange_sel = (($editing && $unapproved) ? "onchange=\"update_invoice(this,'i','$inv_type','n');\"" : 'disabled');
	$onchange_mon = (($editing && $unapproved) ? "onchange=\"update_invoice(this,'i','$inv_type','m');\"" : 'readonly');
	$onchange_pc = (($editing && $unapproved) ? "onchange=\"update_invoice(this,'i','$inv_type','p');\"" : 'readonly');
	$onchange_tck = (($editing && $unapproved) ? "update_invoice(this,'i','$inv_type','t')" : '');
	$extra_tck = (($editing && $unapproved) ? '' : 'disabled');
	$onchange_iapr = ($editing ? "approve_invoice(this)" : '');
	$extra_iapr = ($editing ? '' : 'disabled');

	#$sz = 50;
	$szsmall = 10;
	$gap = "<td width=\"15\">&nbsp;</td>";
	
	if ($logging) log_write("...about to print screen...");
	print "
	" . input_button('View', "view_js('i',$invoice_id,0)") . "
	";
	if ($can_edit)
	{
		if ($read_only)
		{
			$edit_click = "alert('This document cannot be edited because $read_only')";
			$del_click = "alert('This document cannot be deleted because $read_only')";
		}
		else
		{
			$edit_click = "view_js('i',$invoice_id,1)";
			$del_click = "delete_invoice($invoice_id)";
		}
		print "
		" . input_button('Edit', $edit_click) . "
		";
	}
	
	print "
	<table id=\"table_main\" class=\"basic_table\" border=\"0\"><!---->
	<tr>
		<td style=\"font-size:15px; font-weight:bold;\">$inv_type_txt</td>
	</tr>
	<tr>
		<td $ar>Document No.</td>
		<td>" . input_textbox('inv_num', $invoice['INV_NUM'], $szsmall, 10, "$style_r $onchange_num") . "</td>
		$gap
		<td $ar>System</td>
		<td>" . input_select('inv_sys', $SYSTEMS, $invoice['INV_SYS'], 'disabled') . "</td>
		$gap
		<td $ar>
			";
			if ($invoice['INV_APPROVED_DT'] && $pdf_url)#($inv_type == 'I') && 
				print "
				<a href=\"$pdf_url\" target=\"_blank\" rel=\"noopener\"><img src=\"images/pdf.png\" height=\"23\" width=\"23\"></a>
				&nbsp;&nbsp;&nbsp;";
			print "
		</td>
		<td>
			";
			#if ($inv_type == 'I')
				print input_tickbox("$inv_type_txt Approved", 'inv_approved_dt', 1, $invoice['INV_APPROVED_DT'], $onchange_iapr, $extra_iapr);
			print "
		</td>
		";
//		<td $ar>
//			<span id=\"pdf_span_1\">
//			";
//			if ($invoice['INV_APPROVED_DT'])
//				print "PDF";
//			print "
//			</span><!--pdf_span_1-->
//		</td>
//		<td>
//			<span id=\"pdf_span_2\">
//			";
//			if ($invoice['INV_APPROVED_DT'])
//			{
//				if ($pdf_url)
//					print "
//					<a href=\"$pdf_url\" target=\"_blank\" rel=\"noopener\"><img src=\"images/pdf.png\" height=\"23\" width=\"23\"></a>
//					&nbsp;&nbsp;&nbsp;";
//				#print input_button($pdf_button, "create_pdf('i',$invoice_id)");
//			}
//			print "
//			</span><!--pdf_span_2-->
//		</td>
		print "
		<td $col2>&nbsp;</td>
		";
		if ($invoice['INV_SYS_IMP'] != '')
			print "
			<td $ar>Imported from 
				" . input_select('inv_sys_imp', $SYSTEMS, $invoice['INV_SYS_IMP'], 'disabled') . "</td>
			";
		else
			print "
			<td>&nbsp;</td>
			";
		print "
		<td $col2 $ar $grey>DB ID: {$invoice['INVOICE_ID']}</td>
	</tr>
	<tr>
		<td $ar>Issued</td>
		<td>" . input_textbox('inv_dt', date_for_sql($invoice['INV_DT'], true, false, true), $szsmall, 10, "$style_r $onchange_dt") . "</td>
		$gap
		";
		if ($inv_type == 'I')
		{
			print "
				<td $ar>Due</td>
				<td>" . input_textbox('inv_due_dt', date_for_sql($invoice['INV_DUE_DT'], true, false, true), $szsmall, 10, "$style_r $onchange_dt") . "</td>
				";
		}
		else
			print "
				<td $col2>&nbsp;</td>
				";
		print "
		$gap
		<td $ar>Client</td>
		<td $col3>" . input_select('client2_id', $clients, $invoice['CLIENT2_ID'], $onchange_sel) . "</td>
		<td>" . input_button("Go to client (in new tab)", "goto_client('{$invoice['CLIENT2_ID']}','{$invoice['C_CODE']}');") . "</td>
		<td>" . input_button('View all invoices &amp; receipts', "show_invoices({$invoice['C_CODE']})") . "</td>
	</tr>
	<tr>
		<td $col2 $ar>" . (($inv_type == 'I') ?
						input_tickbox('Statement Invoice', 'inv_stmt', 1, $invoice['INV_STMT'], $onchange_tck, $extra_tck)
						: ''). "
			</td>
		$gap
		<td $col2 $ar>
			</td>
		<td $col2>&nbsp;</td>
		<td $col5>$balance_info</td>
	</tr>
	<tr>
		<td $ar>Net</td>
		<td>" . input_textbox('inv_net', money_format_kdb($net, true, true, true), $szsmall, 0, "$style_r $onchange_mon") . "</td>
		$gap
		<td $ar>VAT %</td>
		<td>" . input_textbox('x_vat_rate', "{$vat_rate}%", $szsmall, 0, "$style_r $onchange_pc") . "</td>
		$gap
		<td $ar>VAT &pound;</td>
		<td>" . input_textbox('inv_vat', money_format_kdb($vat_amount, true, true, true), $szsmall, 0, "$style_r readonly") . "</td>
		<td $ar>Notes</td>
		<td $col3 rowspan=\"3\">" . input_textarea('inv_notes', 3, 50, $invoice['INV_NOTES'], $onchange_txt_x) . "</td>
	</tr>
	<tr>
		<td $ar>Gross</td>
		<td>" . input_textbox('x_gross', money_format_kdb($gross, true, true, true), $szsmall, 0, "$style_r readonly") . "</td>
		";
		if ($inv_type == 'I')
		{
			print "
			$gap
			<td $ar>Received</td>
			<td>" . input_textbox('inv_paid', money_format_kdb($paid, true, true, true), $szsmall, 0, "$style_r readonly") . "</td>
			$gap
			<td $ar>Outstanding</td>
			<td>" . input_textbox('x_outst', money_format_kdb($outst, true, true, true), $szsmall, 0, 
									"$style_r readonly" . ((0 < $outst) ? " kolourred" : '')) . "</td>
			";
		}
		print "
	</tr>
	<tr>
		<td $ar>Emailed</td>
		<td>";
			if ($invoice['IMPORTED'])
				print input_textbox('z_em_dt', '(imported)', $szsmall, 0, "readonly");
			else
				print input_textbox('em_dt', date_for_sql($invoice['EM_DT'], true, false, true), $szsmall, 0, "$style_r readonly");
			print "</td>
		$gap
		<td $ar>Posted</td>
		<td>";
			if ($invoice['IMPORTED'])
				print input_textbox('z_inv_posted_dt', '(imported)', $szsmall, 0, "readonly");
			else
				print input_textbox('inv_posted_dt', date_for_sql($invoice['INV_POSTED_DT'], true, false, true), $szsmall, 0, "$style_r $onchange_dt_x");
			print "</td>
	</tr>
	";
	if ($invoice['EM_DT'])
	{
		print "
		<tr>
			<td $ar>Emailed to</td>
			<td $col8>\"{$invoice['EM_TO']}\" with subject line \"{$invoice['EM_SUBJECT']}\"</td>
		</tr>
		";
	}
	if ($invoice['INV_STMT'] == 1)
	{
		print "
		<tr>
			<td $col3 $ar>Covering Dates:</td>
			<td $ar>From</td>
			<td>" . input_textbox('inv_start_dt', date_for_sql($invoice['INV_START_DT'], true, false, true), 
									$szsmall, 0, "$style_r readonly") . "</td>
			$gap
			<td $ar>...to</td>
			<td>" . input_textbox('inv_end_dt', date_for_sql($invoice['INV_END_DT'], true, false, true), 
									$szsmall, 0, "$style_r readonly") . "</td>
		</tr>
		";
	}
	#else
	#	print "<td>&nbsp;</td>";
	#print "
	#";
	if (($inv_type == 'I') || ($inv_type == 'C'))
	{
		print "
		<tr>
			<td $col4 $ar>Linked to " . (($inv_type == 'I') ? 'credit' : 'invoice') . "</td>
			<td>" . input_textbox('linked_inv_num', $invoice['LINKED_INV_NUM'], $szsmall, 0, "$style_r $onchange_num_x") . "</td>
		</tr>
		";
	}
	print "
	</table><!--table_main-->
	";
	
	if (($invoice['INV_SYS'] == 'G') || ($invoice['INV_SYS_IMP'] == 'G') || ($invoice['INV_SYS'] == 'T') || ($invoice['INV_SYS_IMP'] == 'T'))
	{
		# Billing Information is only for General and Trace invoices.
		$gen_billing = ((($invoice['INV_SYS'] == 'G') || ($invoice['INV_SYS_IMP'] == 'G')) ? true : false);
		$trace_billing = !$gen_billing;
		
		print "
		<h3>Billing Information</h3>
		";
		if ($editing && $unapproved && $gen_billing)
			print input_button('Add Billing Line', 'add_gen_billing_line()');
		if ($invoice['BILLING'])
		{
			if ($editing && $unapproved && $gen_billing)
				print '&nbsp;&nbsp;&nbsp;' . input_button('Set Invoice Net to Billings Costs', 'set_net_to_billing()');
			if ($editing && $unapproved && $trace_billing)
				print "(To edit the billing, go to the Job screen)<br>";
			print "
			<table class=\"spaced_table\">
			<tr>
				<th>Line</th><th>Description</th><th>Cost</th>
				" . (($invoice['INV_SYS'] == 'G') ? '' : "<th>Job VilNo &amp; Subject</th>") . "
				" . ($trace_billing ? "<th>Letter Date</th>" : '') . "
				<th>Imported</th>" . #(($editing && $gen_billing) ? "<th>&nbsp;</th>" : '') . "
				"
				" . (user_debug() ? "<th $grey>CLID</th><th $grey>DOCTYPE</th><th $grey>S_INVS</th><th $grey>NSECS</th><th $grey>DB ID</th>" : '') . "
			</tr>
			";
			#$next_line = 1;
			$cost_total = 0.0;
			$jobs = array();
			foreach ($invoice['BILLING'] as $bill)
			{
				$id = $bill['INV_BILLING_ID'];
				#$edit_bill = (($editing && ($id == $inv_billing_id)) ? true : false);
				$edit_bill = (($editing && $unapproved && $gen_billing) ? true : false);
				
				$onchange_bill_txt = ($edit_bill ? "onchange=\"update_billing(this,$id);\"" : 'readonly');
				$onchange_bill_mon = ($edit_bill ? "onchange=\"update_billing(this,$id,'m');\"" : 'readonly');
				$onchange_bill_dt = ($edit_bill ? "onchange=\"update_billing(this,$id,'d');\"" : 'readonly');
				
				$lpos = intval($bill['BL_LPOS']);
				if (!$lpos)
					$lpos = '';
				#	$lpos = $next_line;
				#$next_line = $lpos + 1;
				
				$cost = round(floatval($bill['BL_COST']), 2);
				if ($lpos < 2)
					$cost_total += $cost; # don't add cost again for line 2, 3, etc.
				$cost = ($cost ? money_format_kdb($cost, true, true, true) : "");

				$date = date_for_sql($bill['BL_LETTER_DT'], true, false, true);
				
				$bl_descr = pound_clean($bill['BL_DESCR'], 1);
				$descr_rows = 1;
				$descr_cols = 40;
				if ($editing && $unapproved && $gen_billing)
				{
					$descr_rows = count_lines($bl_descr) - 1;
					if ($descr_rows < 1)
						$descr_rows = 1;
				}
				else
					$bl_descr = nl2br($bl_descr);
				
				if (0 < $bill['JOB_ID'])
				{
					if ($bill['J_VILNO'] != '')
						$vilno = $bill['J_VILNO'] . ' ' . trim("{$bill['JS_FIRSTNAME']} {$bill['JS_LASTNAME']} {$bill['JS_COMPANY']}");
					else
						$vilno = '(VilNo)';
					$job_button = input_button($vilno, "view_job({$bill['JOB_ID']}, {$bill['J_VILNO']});", ''); #"style=\"width:150px;\""
					if (!in_array($bill['JOB_ID'], $jobs))
						$jobs[] = $bill['JOB_ID'];
				}
				else
					$job_button = input_button('(none)', '', 'disabled');

				if ($bill['IMPORTED'])
				{
					if ($bill['BL_SYS_IMP'] == 'C')
						$imported = "From Collect";
					elseif ($bill['BL_SYS_IMP'] == 'T')
						$imported = "From Trace";
					else
						$imported = "From ???";
				}
				else
					$imported = "No";
				
				print "
				<tr>
					<td $ar $at>" . (($editing && $unapproved && $gen_billing) ? input_textbox('bl_lpos', $lpos, 4, 4, "$style_r $onchange_bill_txt") : $lpos) . "</td>
					<td $at>" . (($editing && $unapproved && $gen_billing) ? input_textarea('bl_descr', $descr_rows, $descr_cols, $bl_descr, $onchange_bill_txt) : $bl_descr) . "</td>
					<td $ar $at>" . (($editing && $unapproved && $gen_billing) ? input_textbox('bl_cost', $cost, 6, 10, "$style_r $onchange_bill_mon") : $cost) . "</td>
					" . (($invoice['INV_SYS'] == 'G') ? '' : "<td $at>$job_button</td>") . "
					" . ($trace_billing ? 
							("<td $ar $at>" . (($editing && $unapproved && $gen_billing) ? 
											input_textbox('bl_letter_dt', $date, 6, 10, "$style_r $onchange_bill_dt") : $date) . "
								</td>")
							: '') . "
					<td $at>$imported</td>
					" . #(($editing && $gen_billing) ? ("<td $at>" . ($edit_bill ? '' : input_button('Edit', "edit_gen_billing_line($id)")) . "</td>") : '') . "
					"
					" . (user_debug() ? "
									<td $at $grey>{$bill['Z_CLID']}</td>
									<td $at $grey>{$bill['Z_DOCTYPE']}</td>
									<td $at $grey>{$bill['Z_S_INVS']}</td>
									<td $at $grey>{$bill['Z_NSECS']}</td>
									<td $at $ar $grey>$id</td>
										" : '') . "
				</tr>
				";
			}
			$style = "style=\"font-weight:bold; border-top:1px solid black;\"";
			print "
			<tr>
				<td $col3 $ar $style>Total costs: " . money_format_kdb($cost_total, true, true, true) . "</td>
				";
				if ($invoice['INV_SYS'] == 'T')
					print "
					<td $style>&nbsp;&nbsp;&nbsp;Number of jobs: " . count($jobs) . "</td>
					";
				print "
			</tr>
			";
			print "
			</table>
			";
		}
		else
			print "<p>None</p>";
	}
	if ($logging) log_write("...done start of screen...");
	
	print "
	<h3>Allocations" . (($inv_type == 'I') ? " - what receipts have paid this {$inv_type_txt}" : '') . "</h3>
	";
	if ($invoice['ALLOC'])
	{
		print "
		<table class=\"spaced_table\">
		<tr>
			<th>Amount</th><th>Document No.</th><th>Date</th><th>Recp.Amount</th>
			<th>Imported</th><th $grey>DOCTYPE</th><th $grey>DOCAMOUNT</th><th $grey>DB ID</th>
		</tr>
		";
		foreach ($invoice['ALLOC'] as $alloc)
		{
			$amount = money_format_kdb(floatval($alloc['AL_AMOUNT']), true, true, true);
			
			if ($alloc['RC_ADJUST'] == 0)
				$type = "Receipt";
			elseif ($alloc['RC_ADJUST'] == 1)
				$type = "Adjustment";
			else
				$type = "*Error*";
			
			$date = date_for_sql($alloc['RC_DT'], true, false, true);
			
			$rc_amount = money_format_kdb(floatval($alloc['RC_AMOUNT']), true, true, true);
			
			if ($alloc['IMPORTED'])
			{
				if ($alloc['AL_SYS_IMP'] == 'C')
					$imported = "From Collect";
				elseif ($alloc['AL_SYS_IMP'] == 'T')
					$imported = "From Trace";
				else
					$imported = "From ???";
			}
			else
				$imported = "No";
			
			print "
			<tr>
				<td $ar>$amount</td>
				<td>" . input_button("{$type} {$alloc['RC_NUM']}", "view_invoice('r', {$alloc['INV_RECP_ID']});", '') . #"style=\"width:70px;\""
					"</td>
				<td $ar>$date</td>
				<td $ar>$rc_amount</td>
				<td>$imported</td>
				<td $grey>{$alloc['Z_DOCTYPE']}</td>
				<td $grey>{$alloc['Z_DOCAMOUNT']}</td>
				<td $ar $grey>{$alloc['INV_ALLOC_ID']}</td>
			</tr>
			";
		}
		print "
		</table>
		<p>
		";
		$unalloc = round($invoice['ALLOC_REM'],2);
		if ($unalloc)
			print "Amount of this {$inv_type_txt} that is not included in the above allocations: " . money_format_kdb($unalloc, true, true, true);
		else
			print "This {$inv_type_txt} is fully paid and the client payment(s) are included in the above allocations";
		print "
		</p>
		";
	}
	else
		print "<p>There are no allocations for this {$inv_type_txt} yet.</p>";
	if ($logging) log_write("...done allocations...");
	
	if ((($invoice['INV_SYS'] == 'C') || ($invoice['INV_SYS_IMP'] == 'C')) && ($invoice['INV_SYS'] != 'G'))
	{
		# Subject Payments are only for Collection invoices.
		$payment_count = 0;
		$payment_sum = 0.0;
		$commission_sum = 0.0;
		$client_sum = 0.0;
		$pay_to_us = 0.0;
		$pay_dir = 0.0;
		$pay_fwd = 0.0; 
		
		list($all_jobs_count, $all_jobs_value, $all_jobs_collected, $closed_jobs_count, $closed_jobs_value, 
				$paid_full_count, $paid_full_collected) = 
					invoice_info_collect($invoice['CLIENT2_ID'], $invoice['INV_DT']);
		$pcent_collected = ($all_jobs_value ? round(100.0 * floatval($all_jobs_collected) / $all_jobs_value, 2) : 0.0);
		$pcent_closed_value = ($all_jobs_value ? round(100.0 * floatval($closed_jobs_value) / $all_jobs_value, 2) : 0.0);
		$pcent_full_value = ($all_jobs_value ? round(100.0 * floatval($paid_full_collected) / $all_jobs_value, 2) : 0.0);
		$open_jobs_value = $all_jobs_value - $closed_jobs_value;
		$open_jobs_count = $all_jobs_count - $closed_jobs_count;
		
		print "
		<h3>Subject Payments - what payments by the subject/debtor are included in this {$inv_type_txt}</h3>
		";
		if ($invoice['PAYMENTS'])
		{
			print "
			<table name=\"table_outer\" cellspacing=\"0\" cellpadding=\"0\"><tr><td $at>
				
			<table name=\"table_in_left\" class=\"spaced_table\" border=\"0\"><!---->
			<tr>
				<th>Amount</th><th>Date</th><th>Job</th><th>Client Ref</th><th>Route</th><th $col2>Commission</th><th>Client Cut</th>
				<th>Imported</th><th $grey>DB ID</th>
			</tr>
			";
			foreach ($invoice['PAYMENTS'] as $payment)
			{
				$payment_count++;
				
				$amount = round(floatval($payment['COL_AMT_RX']), 2);
				$compc = floatval($payment['COL_PERCENT']);
				$commission = round($amount * $payment['COL_PERCENT'] / 100.0, 2);
				$client_cut = $amount - $commission;

				$payment_sum += $amount;
				$commission_sum += $commission;
				$client_sum += $client_cut;

				if ($payment['PAYMENT_ROUTE_ID'] == $id_ROUTE_tous)
					$pay_to_us += $amount;
				elseif ($payment['PAYMENT_ROUTE_ID'] == $id_ROUTE_direct)
					$pay_dir += $amount;
				elseif ($payment['PAYMENT_ROUTE_ID'] == $id_ROUTE_fwd)
					$pay_fwd += $amount;
				
				$amount = money_format_kdb($amount, true, true, true);
				$commission = money_format_kdb($commission, true, true, true);
				$client_cut = money_format_kdb($client_cut, true, true, true);

				$date = date_for_sql($payment['COL_DT_RX'], true, false, true);
				$vilno = $payment['J_VILNO'] . ' ' . trim("{$payment['JS_FIRSTNAME']} {$payment['JS_LASTNAME']} {$payment['JS_COMPANY']}");
				
				if ($payment['IMPORTED'])
					$imported = "Yes";
				else
					$imported = "No";

				print "
				<tr>
					<td $ar>$amount</td>
					<td $ar>$date</td>
					<td>" . input_button($vilno, "view_job({$payment['JOB_ID']},{$payment['J_VILNO']})", '') . #"style=\"width:70px;\""
						"</td>
					<td>{$payment['CLIENT_REF']}</td>
					<td>{$payment['PAYMENT_ROUTE']}</td>
					<td $ar>{$compc}%</td>
					<td $ar>$commission</td>
					<td $ar>$client_cut</td>
					<td $ac>$imported</td>
					<td $ar $grey>{$payment['JOB_PAYMENT_ID']}</td>
				</tr>
				";
			}
			$style_total = "style=\"font-weight:bold; border-top:1px black solid;\"";
			$prefix = "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;...";
			print "
			<tr>
				<td $ar $style_total>" . money_format_kdb($payment_sum, true, true, true) . "</td>
				<td $col4></td>
				<td $ar $col2 $style_total>" . money_format_kdb($commission_sum, true, true, true) . "</td>
				<td $ar $style_total>" . money_format_kdb($client_sum, true, true, true) . "</td>
			</tr>
			</table><!--table_in_left-->

			</td><td width=\"10\" style=\"border-right:1px solid black;\">&nbsp;</td><td width=\"10\">&nbsp;</td><td $at>
			
			<table name=\"table_in_right\" cellspacing=\"0\" cellpadding=\"0\" border=\"0\"><!---->
			<tr>
				<td>Orginal number of accounts</td><td $ar>" . number_with_commas($all_jobs_count, false) . "</td>
			</tr>
			<tr>
				<td>Total value</td><td $ar>" . money_format_kdb($all_jobs_value, true, true, true) . "</td>
			</tr>
			<tr>
				<td>Number of payments</td><td $ar>" . number_with_commas($payment_count, false) . "</td>
			</tr>
			<tr>
				<td>To us</td><td $ar>" . money_format_kdb($pay_to_us, true, true, true) . "</td>
			</tr>
			<tr>
				<td>Direct to client</td><td $ar>" . money_format_kdb($pay_dir, true, true, true) . "</td>
			</tr>
			<tr>
				<td>Forwarded</td><td $ar>" . money_format_kdb($pay_fwd, true, true, true) . "</td>
			</tr>
			<tr>
				<td>Total collected to date</td><td $ar>" . money_format_kdb($all_jobs_collected, true, true, true) . "</td>
			</tr>
			<tr>
				<td>Percentage collected</td><td $ar>" . number_format($pcent_collected, 2) . "%</td>
			</tr>
			<tr>
				<td>Closed accounts to date</td><td $ar>" . number_with_commas($closed_jobs_count, false) . "</td>
			</tr>
			<tr>
				<td>{$prefix}value</td><td $ar>" . money_format_kdb($closed_jobs_value, true, true, true) . "</td>
			</tr>
			<tr>
				<td>{$prefix}% total value</td><td $ar>" . number_format($pcent_closed_value, 2) . "%</td>
			</tr>
			<tr>
				<td>Paid in full</td><td $ar>" . number_with_commas($paid_full_count, false) . "</td>
			</tr>
			<tr>
				<td>{$prefix}collected</td><td $ar>" . money_format_kdb($paid_full_collected, true, true, true) . "</td>
			</tr>
			<tr>
				<td>{$prefix}% total value</td><td $ar>" . number_format($pcent_full_value, 2) . "%</td>
			</tr>
			<tr>
				<td>Current batch value</td><td $ar>" . money_format_kdb($open_jobs_value, true, true, true) . "</td>
			</tr>
			<tr>
				<td>Current number of accounts</td><td $ar>" . number_with_commas($open_jobs_count, false) . "</td>
			</tr>
			</table><!--table_in_right-->
			
			</td></tr></table><!--table_outer-->
			";
		}
		else
			print "<p>None</p>";
	}
	if ($logging) log_write("...done payments...");
	
	print "
		<h3>Email History</h3>
		";
	if ($invoice['EMAIL_HISTORY'])
	{
		print "
		<table name=\"table_email_history\" class=\"spaced_table\" cellspacing=\"0\" cellpadding=\"0\" border=\"0\"><!---->
		<tr>
			<th>Sent</th><th>To</th><th>Subject</th><th>Attached</th>" . ($global_debug ? "<th $grey>EMAIL_ID/SOURCE</th>" : '') . "
		</tr>
		";
		foreach ($invoice['EMAIL_HISTORY'] as $one_email)
		{
			# EM_ATTACH: e.g. v1556557/letter_1556557_90912614_1250506_20181216_113835.pdf|c1602/invoice_205784_20181216_113923.pdf
			$inv_num = '';
			$files = explode('|', $one_email['EM_ATTACH']);
			foreach ($files as $one_file)
			{
				$dirs = explode('/', $one_file); # e.g. c1602/invoice_205784_20181216_113923.pdf
				foreach ($dirs as $one_dir)
				{
					$bits = explode('_', $one_dir); # e.g. invoice_205784_20181216_113923.pdf
					if ($bits[0] == 'invoice')
						$inv_num = $bits[1];
				}
			}
			print "
			<tr>
				<td>" . date_for_sql($one_email['EM_DT'], true, true, true) . "</td>
				<td>{$one_email['EM_TO']}</td><td>{$one_email['EM_SUBJECT']}</td><td $ac>$inv_num</td>
				" . ($global_debug ? "<td $ar $grey>{$one_email['EMAIL_ID']}/{$one_email['SOURCE']}</td>" : '') . "
			</tr>
			";
		}
		print "
		</table><!--table_email_history-->
		";
		#print_r($invoice['EMAIL_HISTORY']);#
	}
	else
		print "None<br>";
	
	if ($show_del_button)
		print "<br>" . input_button("Delete {$inv_type_txt}", $del_click);
	
	if (user_debug() && $invoice['IMPORTED'])
	{
		if ($invoice['INV_SYS_IMP'] == 'T')
			$from = "Trace";
		elseif ($invoice['INV_SYS_IMP'] == 'C')
			$from = "Collect";
		else
			$from = "*Error*";
		if ($invoice['INV_S_INVS'] == 'C')
			$s_invs = "col";
		elseif ($invoice['INV_S_INVS'] == 'G')
			$s_invs = "gen";
		elseif ($invoice['INV_S_INVS'] == 'Y')
			$s_invs = "yes";
		elseif ($invoice['INV_S_INVS'] == 'N')
			$s_invs = "no";
		elseif ($invoice['INV_S_INVS'] == '')
			$s_invs = "";
		else
			$s_invs = "*Error*";
		
		print "
		<h3>This {$inv_type_txt} was imported from the old $from database</h3>
		<p>Other data imported from old database &ndash; this can be ignored:</p>
		<table class=\"basic_table\" border=\"1\">
			<tr><th>Field</th><th>Value</th></tr>
			<tr><td>S_INVS</td><td>$s_invs</td></tr>
			<tr><td>COMPLETE</td><td>" . (($invoice['INV_COMPLETE'] == 1) ? 'Y' : 'N') . "</td></tr>
			<tr><td>EOM</td><td>{$invoice['Z_EOM']}</td></tr>
		</table>
		";
	}
	if ($logging) log_write("...exit");
	
} # print_one_invoice()

function print_one_receipt($editing)
{
	global $ac;
	global $ar;
	global $at;
//	global $ascii_pound;
//	global $at;
	global $can_edit;
	global $col2;
	global $col4;
	global $col5;
#	global $col7;
	global $grey;
	global $page_title_2;
//	global $pound_194;
	global $receipt_id;
	global $style_r;
	global $SYSTEMS;
//	global $uni_pound;
	
	if ($editing && (!$can_edit))
		$editing = false;

	$receipt = sql_get_one_receipt($receipt_id);
	
	$clients = sql_get_clients_for_select('*', $receipt['CLIENT2_ID']);
	
	list($balance, $invoices, $credits, $focs, $receipts, $adjusts) = sql_get_client_balance_info($receipt['CLIENT2_ID']);
	$balance_info = "Account Balance: " . money_format_kdb($balance, true, true, true) . ". " .
					"Invoices&nbsp;" . money_format_kdb($invoices, true, true, true) . ". " .
					"Credits&nbsp;" . money_format_kdb($credits, true, true, true) . ". " .
					"FOCs&nbsp;" . money_format_kdb($focs, true, true, true) . ". " .
					"Receipts&nbsp;" . money_format_kdb($receipts, true, true, true) . ". " .
					"Adjustments&nbsp;" . money_format_kdb($adjusts, true, true, true) . ".";
	
	if ($receipt['RC_ADJUST'] == 0)
	{
		$rc_type = "Receipt";
		$rc_dt_txt = "Receipt Received";
	}
	elseif ($receipt['RC_ADJUST'] == 1)
	{
		$rc_type = "Adjustment";
		$rc_dt_txt = "Adjustment Made";
	}
	else
	{
		$rc_type = "*Error*";
		$rc_dt_txt = "*Error*";
	}
	$page_title_2 = "{$rc_type} {$receipt['RC_NUM']} - Vilcol";

	if ($receipt['C_ARCHIVED'])
		$read_only = "its client is archived";
	elseif ($receipt['IMPORTED'])
		$read_only = "it is imported from the old database";
	else
		$read_only = '';
	if ($read_only)
		$editing = false; # but don't unset $can_edit or $show_del_button
	
	$onchange_txt = ($editing ? "onchange=\"update_invoice(this,'r','');\"" : 'readonly');
	$onchange_num = ($editing ? "onchange=\"update_invoice(this,'r','','n');\"" : 'readonly');
	$onchange_dt = ($editing ? "onchange=\"update_invoice(this,'r','','d');\"" : 'readonly');
	$onchange_sel = ($editing ? "onchange=\"update_invoice(this,'r','','n');\"" : 'disabled');
	$onchange_mon = ($editing ? "onchange=\"update_invoice(this,'r','','m');\"" : 'readonly');
	#$onchange_tck = ($editing ? "update_invoice(this,'r','','t')" : '');
	#$extra_tck = ($editing ? '' : 'disabled');

	#$sz = 50;
	$szsmall = 10;
	$gap = "<td width=\"15\">&nbsp;</td>";

	print "
	" . input_button('View', "view_js('r',$receipt_id,0)") . "
	";
	if ($can_edit)
	{
		if ($read_only)
			$edit_click = "alert('This document cannot be edited because $read_only')";
		else
			$edit_click = "view_js('r',$receipt_id,1)";
		print "
		" . input_button('Edit', $edit_click) . "
		";
	}
	
	print "
	<table id=\"table_main\" class=\"basic_table\" border=\"0\"><!---->
	<tr>
		<td style=\"font-size:15px; font-weight:bold;\">$rc_type</td>
	</tr>
	<tr>
		<td $ar>Document No.</td>
		<td>" . input_textbox('rc_num', $receipt['RC_NUM'], $szsmall, 10, "$style_r $onchange_num") . "</td>
		$gap
		<td $col2>" . #input_tickbox('Is Adjustment', 'rc_adjust', 1, $receipt['RC_ADJUST'], $onchange_tck, $extra_tck) . 
			"</td>
		";
		if ($receipt['RC_SYS_IMP'] != '')
			print "
			<td $ar>Imported from</td>
			<td>" . input_select('rc_sys_imp', $SYSTEMS, $receipt['RC_SYS_IMP'], 'disabled') . "</td>
			";
		else
			print "
			<td $col2>&nbsp;</td>
			";
		print "
		<td $ar $grey>DB ID: {$receipt['INV_RECP_ID']}</td>
	</tr>
	<tr>
		<td $ar>Received</td>
		<td>" . input_textbox('rc_dt', date_for_sql($receipt['RC_DT'], true, false, true), $szsmall, 10, "$style_r $onchange_dt") . "</td>
		$gap
		<td $ar>Client</td>
		<td>" . input_select('client2_id', $clients, $receipt['CLIENT2_ID'], $onchange_sel) . "</td>
		<td $col2>" . input_button("Go to client (in new tab)", "goto_client('{$receipt['CLIENT2_ID']}','{$receipt['C_CODE']}');") . "</td>
		<td>" . input_button('View all invoices &amp; receipts', "show_invoices({$receipt['C_CODE']})") . "</td>
	</tr>
	<tr>
		<td $col4>&nbsp;</td>
		<td $col5>$balance_info</td>
	</tr>
	<tr>
		<td $ar $at>Amount</td>
		<td $at>" . input_textbox('rc_amount', money_format_kdb($receipt['RC_AMOUNT'], true, true, true), $szsmall, 0, "$style_r $onchange_mon") . "</td>
		$gap
		<td $ar $at>Notes</td>
		<td $col4>" . input_textarea('rc_notes', 3, 50, $receipt['RC_NOTES'], $onchange_txt) . "</td>
	</tr>
	</table><!--table_main-->
	";
		
	print "
	<h3>Allocations - what this receipt is paying for</h3>
	";
	if ($receipt['ALLOC'])
	{
		print "
		<table class=\"spaced_table\">
		<tr>
			<th>Amount</th><th>Doc.Type</th><th>Doc.Num</th><th>Date</th><th>Net</th><th>VAT</th><th>Gross</th>
			<th>Imported</th><th $grey>DOCTYPE</th><th $grey>DOCAMOUNT</th><th $grey>DB ID</th>
		</tr>
		";
		$sum_alloc = 0.0;
		foreach ($receipt['ALLOC'] as $alloc)
		{
			$amount = floatval($alloc['AL_AMOUNT']);
			$sum_alloc += $amount;
			$amount = money_format_kdb($amount, true, true, true);
			
			if ($alloc['INV_TYPE'] == 'I')
				$type = "Invoice";
			elseif ($alloc['INV_TYPE'] == 'C')
				$type = "Credit";
			elseif ($alloc['INV_TYPE'] == 'F')
				$type = "FOC";
			else
				$type = "*Error*";
			
			$date = date_for_sql($alloc['INV_DT'], true, false, true);
			
			$net = floatval($alloc['INV_NET']);
			$vat = floatval($alloc['INV_VAT']);
			$gross = $net + $vat;
			$net = money_format_kdb($net, true, true, true);
			$vat = money_format_kdb($vat, true, true, true);
			$gross = money_format_kdb($gross, true, true, true);
			
			if ($alloc['IMPORTED'])
			{
				if ($alloc['AL_SYS_IMP'] == 'C')
					$imported = "From Collect";
				elseif ($alloc['AL_SYS_IMP'] == 'T')
					$imported = "From Trace";
				else
					$imported = "From ???";
			}
			else
				$imported = "No";

			print "
			<tr>
				<td $ar>$amount</td>
				<td $ac>$type</td>
				<td $ar>" . input_button($alloc['INV_NUM'], "view_invoice('i', {$alloc['INVOICE_ID']});", "style=\"width:70px;\"") . "</td>
				<td $ar>$date</td>
				<td $ar>$net</td>
				<td $ar>$vat</td>
				<td $ar>$gross</td>
				<td>$imported</td>
				<td $grey>{$alloc['Z_DOCTYPE']}</td>
				<td $grey>{$alloc['Z_DOCAMOUNT']}</td>
				<td $ar $grey>{$alloc['INV_ALLOC_ID']}</td>
			</tr>
			";
		}
		print "
		</table>
		<p>
		";
		$sum_alloc = round($sum_alloc,2);
		print "Sum of Allocations: " . money_format_kdb($sum_alloc, true, true, true) . "<br>";
		$unalloc = round($receipt['ALLOC_REM'],2);
		if ($unalloc)
			print "Amount of this receipt that is unallocated: " . money_format_kdb($unalloc, true, true, true);
		else
			print "This receipt is fully allocated to the above invoices (or credits)";
		print "
		</p>
		";
	}
	else
		print "<p>There are no allocations for this receipt yet.</p>";
	
	if (user_debug() && $receipt['IMPORTED'])
	{
		if ($receipt['RC_SYS_IMP'] == 'T')
			$from = "Trace";
		elseif ($receipt['RC_SYS_IMP'] == 'C')
			$from = "Collect";
		else
			$from = "*Error*";
		print "
		<h3>This {$rc_type} was imported from the old $from database</h3>
		<p>Other data imported from old database &ndash; this can be ignored:</p>
		<table class=\"spaced_table\">
			<tr><th>Field</th><th>Value</th></tr>
			<tr><td>COMPLETE</td><td>{$receipt['Z_COMPLETE']}</td></tr>
		</table>
		";
	}
	
} # print_one_receipt()

function print_minor_forms()
{
	print "
	<form name=\"form_client\" action=\"clients.php\" method=\"post\" target=\"_blank\" rel=\"noopener\">
		" . input_hidden('client2_id', '') . "
		" . input_hidden('sc_text', '') . "
		" . input_hidden('task', '') . "
	</form><!--form_client-->
	
	<form name=\"form_invoices\" action=\"ledger.php\" method=\"post\" target=\"_blank\" rel=\"noopener\">
		" . input_hidden('sc_client', '') . "
		" . input_hidden('task', '') . "
		" . input_hidden('invoice_id', '') . "
		" . input_hidden('doctype', '') . "
	</form><!--form_invoices-->

	<form name=\"form_job\" action=\"jobs.php\" method=\"post\" target=\"_blank\" rel=\"noopener\">
		" . input_hidden('task', '') . "
		" . input_hidden('sc_text', '') . "
		" . input_hidden('job_id', '') . "
	</form><!--form_job-->

	";
}

function pdf_create_doc_financial($doctype, $doc_id)
{
	# Create a PDF of the document (invoice, credit or receipt). Store in /csvex directory.
	# E.g. $doctype = 'i' and $doc_id is the INVOICE_ID
	
	global $csv_dir; # csvex in settings.php
	global $pdf_error;
	
	sql_encryption_preparation('CLIENT2');
	sql_encryption_preparation('JOB');
	sql_encryption_preparation('JOB_SUBJECT');

	$pdf_error = '';
	$pdf_filename = '';
	$html_body = '';
	
	#dprint("pdf_create_doc_financial($doctype, $doc_id)");#
	if ($doctype == 'i')
	{
		# Invoice or Credit
		list($pdf_filename, $c_code, $html_body) = pdf_create_inv($doc_id); # this may set $pdf_error
	}
	else
	{
		$pdf_error = "pdf_create_doc_financial($doctype, $doc_id): doctype \"$doctype\" not recognised";
	}
	
	if (!$pdf_error)
	{
		$pdf_dir = "{$csv_dir}/c{$c_code}";
		if (check_dir($pdf_dir))
			$pdf_error = pdf_create($pdf_dir, $pdf_filename, $html_body);
		else
			$pdf_error = "pdf_create_doc_financial() failed to create dir \"$pdf_dir\"";
	}
	
	if ($pdf_error)
		dprint("PDF Creation failed. Error: $pdf_error", true);
	else
		dprint("The PDF has been created successfully.", true);
	
} # pdf_create_doc_financial()

function pdf_create_inv($doc_id)
{
	global $pdf_error;
	global $sqlFalse;
	global $sqlTrue;
	
	$invoice_id = $doc_id;
	$sql = "SELECT BT.INV_BILLING_ID, PC.JOB_PAYMENT_ID, JT.JOB_ID, JC.JOB_ID, I.INV_APPROVED_DT, I.INV_TYPE,
				I.INV_NUM, I.INV_SYS, I.INV_STMT, C.CLIENT2_ID, C.C_CODE, I.INV_DT, I.INV_NET, I.INV_VAT, I.INV_START_DT, I.INV_END_DT,
				
				" . # Trace job info:
					sql_decrypt('JT.CLIENT_REF') . " AS JT_CLIENT_REF, " . sqla('JT.J_VILNO') . " AS JT_VILNO, BT.BL_DESCR, BT.BL_COST,
					" . sql_decrypt('ST.JS_FIRSTNAME') . " AS JT_JS_FIRSTNAME, " . sql_decrypt('ST.JS_LASTNAME') . " AS JT_JS_LASTNAME, 
					" . sql_decrypt('ST.JS_COMPANY') . " AS JT_JS_COMPANY,
					
				" . # Collect job info:
					sql_decrypt('JC.CLIENT_REF') . " AS JC_CLIENT_REF, " . sqla('JC.J_VILNO') . " AS JC_VILNO, 
					PC.COL_DT_RX, PC.COL_AMT_RX, PC.COL_PAYMENT_ROUTE_ID, 
					" . sql_decrypt('SC.JS_FIRSTNAME') . " AS JC_JS_FIRSTNAME, " . sql_decrypt('SC.JS_LASTNAME') . " AS JC_JS_LASTNAME, 
					" . sql_decrypt('SC.JS_COMPANY') . " AS JC_JS_COMPANY,
					CASE WHEN PC.COL_PERCENT IS NOT NULL
					THEN PC.COL_PERCENT
					ELSE
						CASE WHEN JC.JC_PERCENT IS NOT NULL
						THEN JC.JC_PERCENT
						ELSE
							CASE WHEN C.COMM_PERCENT IS NOT NULL
							THEN C.COMM_PERCENT
							ELSE 0.0
							END
						END
					END AS PAY_PERCENT
					
			FROM INVOICE AS I 
			LEFT JOIN CLIENT2 AS C ON C.CLIENT2_ID=I.CLIENT2_ID
			
			" . # Trace job tables:
			"LEFT JOIN INV_BILLING AS BT ON BT.INVOICE_ID=I.INVOICE_ID AND BT.OBSOLETE=$sqlFalse
			LEFT JOIN JOB AS JT ON JT.JOB_ID=BT.JOB_ID
			LEFT JOIN JOB_SUBJECT AS ST ON ST.JOB_ID=JT.JOB_ID AND ST.JS_PRIMARY=$sqlTrue AND ST.OBSOLETE=$sqlFalse
			
			" . # Collect job tables:
			"LEFT JOIN JOB_PAYMENT AS PC ON PC.INVOICE_ID=I.INVOICE_ID
			LEFT JOIN JOB AS JC ON JC.JOB_ID=PC.JOB_ID
			LEFT JOIN JOB_SUBJECT AS SC ON SC.JOB_ID=JC.JOB_ID AND SC.JS_PRIMARY=$sqlTrue AND SC.OBSOLETE=$sqlFalse
			
			WHERE I.INVOICE_ID=$invoice_id AND (PC.JOB_PAYMENT_ID IS NULL OR PC.OBSOLETE=$sqlFalse)
			ORDER BY BT.BL_LPOS, BT.INV_BILLING_ID
			";
	#dprint($sql);#
	sql_execute($sql);
	$client2_id = 0;
	$c_code = '';
	$inv_num = '';
	$inv_type = ''; # I, C or F
	$inv_sys_t = false; # invoice for Trace job
	$inv_sys_c = false; # invoice for Collect job
	$inv_sys_g = false; # General invoice
	$inv_stmt = false;
	$inv_dt = '';
	$inv_start_dt = '';
	$inv_end_dt = '';
	$inv_net = 0.0;
	$inv_vat = 0.0;
	$inv_approved = '';
	$client_ref = '';
	$vilno = '';
	$bills = array();
	$payments = array();
	while (($newArray = sql_fetch_assoc()) != false)
	{
		#dprint(print_r($newArray,1));#
		if ($client2_id == 0)
		{
			# Process the field values that will be the same for all records in the result set.
			$client2_id = $newArray['CLIENT2_ID'];
			$c_code = $newArray['C_CODE'];
			$inv_num = $newArray['INV_NUM']; # this will be at least 190000 so no need to pad with zeroes on the front.
			$inv_type = $newArray['INV_TYPE'];
			$inv_dt = $newArray['INV_DT'];
			$inv_approved = $newArray['INV_APPROVED_DT'];
			$inv_net = round(floatval($newArray['INV_NET']), 2); # avoid ".00"
			$inv_vat = round(floatval($newArray['INV_VAT']), 2); # avoid ".00"
			$inv_gross = $inv_net + $inv_vat;

			if ($newArray['INV_SYS'] == 'T')
				$inv_sys_t = true;
			elseif ($newArray['INV_SYS'] == 'C')
				$inv_sys_c = true;
			elseif ($newArray['INV_SYS'] == 'G')
				$inv_sys_g = true;
			if ($inv_sys_t)
			{
				$client_ref = $newArray['JT_CLIENT_REF'];
				$vilno = $newArray['JT_VILNO'];
			}

			if ($newArray['INV_STMT'] == 1)
			{
				$inv_stmt = true;
				$inv_start_dt = $newArray['INV_START_DT'];
				$inv_end_dt = $newArray['INV_END_DT'];
			}
		}
		# Process the field values that may be different from other records in the result set.
		if ($inv_sys_t || $inv_sys_g)
		{
			$newArray['BL_COST'] = floatval($newArray['BL_COST']); # avoid ".00"
			if ($newArray['BL_DESCR'] || $newArray['BL_COST'])
				$bills[] = array('JT_CLIENT_REF' => $newArray['JT_CLIENT_REF'],
								'JT_SUBJECT' => trim("{$newArray['JT_JS_FIRSTNAME']} {$newArray['JT_JS_LASTNAME']} {$newArray['JT_JS_COMPANY']}"),
								'JT_VILNO' => $newArray['JT_VILNO'],
								'BL_DESCR' => $newArray['BL_DESCR'], 
								'BL_COST' => $newArray['BL_COST']
								);
		}
		elseif ($inv_sys_c)
		{
			$payments[] = array('JC_CLIENT_REF' => $newArray['JC_CLIENT_REF'],
								'JC_SUBJECT' => trim("{$newArray['JC_JS_FIRSTNAME']} {$newArray['JC_JS_LASTNAME']} {$newArray['JC_JS_COMPANY']}"),
								'JC_VILNO' => $newArray['JC_VILNO'],
								'COL_DT_RX' => $newArray['COL_DT_RX'], 
								'COL_AMT_RX' => round(floatval($newArray['COL_AMT_RX']), 2),
								'PAYMENT_ROUTE_ID' => $newArray['COL_PAYMENT_ROUTE_ID'],
								'PAY_PERCENT' => $newArray['PAY_PERCENT']
								);
		}
	} # while sql_fetch
	#dprint("payments=" . print_r($payments,1));#
	#dprint("bills=" . print_r($bills,1));#
	#dprint("pdf_create_inv(): invoice has " . count($bills) . " bills");#
	
	$timestamp = str_replace(' ', '_', str_replace('-', '', str_replace(':', '', str_replace('.000', '', trim($inv_approved)))));		
	$pdf_filename = "invoice_{$inv_num}_{$timestamp}.pdf"; # OK for credits too

	$client_address = array();
	#if ($inv_sys_c && $inv_stmt)
	#{
		$sql = "SELECT " . sql_decrypt('C_CO_NAME', '', 'CLIENT2') . ", " . sql_decrypt('C_ADDR_1', '', 'CLIENT2') . ", 
					" . sql_decrypt('C_ADDR_2', '', 'CLIENT2') . ", " . sql_decrypt('C_ADDR_3', '', 'CLIENT2') . ", 
					" . sql_decrypt('C_ADDR_4', '', 'CLIENT2') . ", " . sql_decrypt('C_ADDR_5', '', 'CLIENT2') . ", 
					" . sql_decrypt('C_ADDR_PC', '', 'CLIENT2') . " 
				FROM CLIENT2 WHERE CLIENT2_ID=$client2_id";
		sql_execute($sql);
		while (($newArray = sql_fetch()) != false)
		{
			$client_address[] = trim($newArray[0]); # company name
			$temp = trim($newArray[1]);
			if ($temp)
				$client_address[] = $temp; # address line
			$temp = trim($newArray[2]);
			if ($temp)
				$client_address[] = $temp; # address line
			$temp = trim($newArray[3]);
			if ($temp)
				$client_address[] = $temp; # address line
			$temp = trim($newArray[4]);
			if ($temp)
				$client_address[] = $temp; # address line
			$temp = trim($newArray[5]);
			if ($temp)
				$client_address[] = $temp; # address line
			$temp = trim($newArray[6]);
			if ($temp)
				$client_address[] = $temp; # address line
		}
	#}
	
	if ($inv_sys_t && (!$inv_stmt))
	{
		# Trace One-off Invoice
		$html_body = pdf_create_inv_trc_oneoff($c_code, $client_address, $inv_num, $inv_dt, $client_ref, $vilno, $inv_net, $inv_vat, $inv_gross, $bills);
	}
	elseif ($inv_sys_t && $inv_stmt)
	{
		# Trace Statement Invoice
		$html_body = pdf_create_inv_trc_stmt($c_code, $client_address, $inv_num, $inv_dt, $inv_net, $inv_vat, $inv_gross, $bills, 
													$inv_start_dt, $inv_end_dt);
	}
	elseif ($inv_sys_c && (!$inv_stmt))
	{
		# Collection One-off Invoice
		$html_body = '';
		$pdf_error = "pdf_create_inv($doc_id): Collection One-off Invoice not supported";
	}
	elseif ($inv_sys_c && $inv_stmt)
	{
		# Collection Statement Invoice
		$html_body = pdf_create_inv_coll_stmt($client2_id, $c_code, $inv_num, $inv_dt, $inv_net, $inv_vat, $inv_gross, $payments, 
																	$client_address, $inv_start_dt, $inv_end_dt);
	}
	elseif ($inv_sys_g)
	{
		# General [One-off] Invoice or Credit
		$html_body = pdf_create_inv_gen($c_code, $client_address, $inv_num, $inv_type, $inv_dt, $inv_net, $inv_vat, $inv_gross, $bills);
	}
	else
	{
		$pdf_error = "pdf_create_inv($doc_id): INV_SYS/INV_STMT not recognised";
		$html_body = '';
	}
	#$html_body = "<div style=\"border:solid 1px red;\">$html_body</div>";

	$html_body = pound_clean($html_body, 1);
	return array($pdf_filename, $c_code, $html_body);
} # pdf_create_inv()

function pdf_create_inv_trc_oneoff($c_code, $client_address, $inv_num, $inv_dt, $client_ref, $vilno, $inv_net, $inv_vat, $inv_gross, $bills)
{
	global $ar;
	global $at;
	global $bank_line_1; # settings.php
	global $bank_line_2; # settings.php
	global $bank_line_2b; # settings.php
	global $bank_line_3; # settings.php
	global $bank_line_4; # settings.php
	global $col2;
	global $vat_number; # settings.php
	
	$sp = "&nbsp;";
	$gap = $sp . $sp . $sp;
	#$col_count = 6;
	
	$ca_count = count($client_address);
	$client_address_0 = $sp; # company name
	$client_address_1 = $sp; # address line 1
	$client_address_2 = $sp; # address line 2
	$client_address_3 = $sp; # address line 3
	$client_address_4 = $sp; # address line 4
	$client_address_5 = $sp; # address line 5
	$client_address_6 = $sp; # address postcode
	if ((0 < $ca_count) && $client_address[0])
		$client_address_0 = $client_address[0];
	if ((1 < $ca_count) && $client_address[1])
	{
		$client_address_1 = $client_address[1];
		if ((2 < $ca_count) && $client_address[2])
		{
			$client_address_2 = $client_address[2];
			if ((3 < $ca_count) && $client_address[3])
			{
				$client_address_3 = $client_address[3];
				if ((4 < $ca_count) && $client_address[4])
				{
					$client_address_4 = $client_address[4];
					if ((5 < $ca_count) && $client_address[5])
					{
						$client_address_5 = $client_address[5];
						if ((6 < $ca_count) && $client_address[6])
							$client_address_6 = $client_address[6];
					}
				}
			}
		}
	}

	$blank_line = "
	<tr>
		<td>$sp</td>
	</tr>
	";

	$lines_in_body = 25; # Support ticket #1072 (24/08/20): 25 lines (was 26) ###; #Feedback #223		28;
	$line_count = 0;

	$html_body = "
	<table width=\"100%\" border=\"0\"><!---->
	";
			
	$html_body .= "
	<tr>
		<td $col2>INVOICE NUMBER{$gap}{$c_code}/{$inv_num}</td>
	</tr>
	";
	$line_count++;

	$html_body .= "
	<tr>
		<!--Date label-->	<td style=\"width:100px;\">Date:</td>
		<!--Date value-->	<td>" . date_for_sql($inv_dt, true, false, true) . "</td>
		<!--Net label-->	<td style=\"width:100px;\">$sp</td>
		<!--Net value-->	<td style=\"width:80px;\">$sp</td>
		<!--Margin-->		<td style=\"width:40px;\">$sp</td>
	</tr>
	";
	#style=\"width:250px;\"
	$line_count++;

	$html_body .= $blank_line;
	$line_count++;

//	$ca_count = count($client_address);
//	$ca_ix = 0;
//	if ($ca_ix < $ca_count)
//		$ca_line = $client_address[$ca_ix++];
//	else
//		$ca_line = '';
	$html_body .= "
	<tr>
		<td $col2>Attn:&nbsp;&nbsp;$client_address_0</td>
	</tr>
	";
	$line_count++;

//	if ($ca_ix < $ca_count)
//		$ca_line = $client_address[$ca_ix++];
//	else
//		$ca_line = '';
	$html_body .= "
	<tr>
		<td $col2>$client_address_1</td>
	</tr>
	";
	$line_count++;

//	if ($ca_ix < $ca_count)
//		$ca_line = $client_address[$ca_ix++];
//	else
//		$ca_line = '';
	$html_body .= "
	<tr>
		<td $col2>$client_address_2</td>
	</tr>
	";
	$line_count++;

//	if ($ca_ix < $ca_count)
//	{
//		$ca_line = $client_address[$ca_ix++];
		$html_body .= "
		<tr>
			<td $col2>$client_address_3</td>
		</tr>
		";
		$line_count++;

//		if ($ca_ix < $ca_count)
//		{
//			$ca_line = $client_address[$ca_ix++];
			$html_body .= "
			<tr>
				<td $col2>$client_address_4</td>
			</tr>
			";
			$line_count++;
	
//			if ($ca_ix < $ca_count)
//			{
//				$ca_line = $client_address[$ca_ix++];
				$html_body .= "
				<tr>
					<td $col2>$client_address_5</td>
					<td $col2>VAT No.{$gap}{$vat_number}</td>
				</tr>
				";
				$line_count++;
	
//				if ($ca_ix < $ca_count)
//				{
//					$ca_line = $client_address[$ca_ix++];
					$html_body .= "
					<tr>
						<td $col2>$client_address_6</td>
					</tr>
					";
					$line_count++;
//				}
//			}
//		}
//	}
	
	$html_body .= $blank_line;
	$line_count++;

	$cell_style = "style=\"border-bottom:double 1px black;\"";
	$html_body .= "
	<tr>
		<td $col2><b><span $cell_style>DESCRIPTION</span></b></td>
	</tr>
	";
	$line_count++;

	$html_body .= "
	<tr>
		<td>YOUR REF:</td>
		<td>$client_ref</td>
	</tr>
	";
	$line_count++;

	$html_body .= "
	<tr>
		<td>OUR REF:</td>
		<td>$vilno</td>
	</tr>
	";
	$line_count++;

	$html_body .= "
	<tr>
		<td>RE: </td>
		<td>{$bills[0]['JT_SUBJECT']}</td>
	</tr>
	";
	$line_count++;

	$html_body .= $blank_line;
	$line_count++;

	$cell_style = "style=\"border-bottom:dashed 1px black;\"";
	$html_body .= "
	<tr>
		<td $col2><span $cell_style>Work Performed</span></td>
	</tr>
	";
	$line_count++;

	$html_body .= $blank_line;
	$line_count++;

	if ($bills)
	{
		foreach ($bills as $bill_line)
		{
			$html_body .= "
			<tr>
				<td $at $col2>{$bill_line['BL_DESCR']}</td>
				<td>$sp</td>
				<td $at $ar>" . money_format_kdb($bill_line['BL_COST'], true, true, true) . "</td>
			</tr>
			";
			$line_count++;
		}
	}
	else
	{
		$html_body .= "
		<tr>
			<td $col2>(no billing lines)</td>
		</tr>
		";
		$line_count++;
	}

	while ($line_count < $lines_in_body)
	{
		$html_body .= $blank_line;
		$line_count++;
	}

	$cell_style = "style=\"border-top:dashed 1px black;\"";
	$html_body .= "
	<tr>
		<td>$sp</td>
		<td>$sp</td>
		<td $cell_style>NET TOTAL</td>
		<td $cell_style $ar>" . money_format_kdb($inv_net, true, true, true) . "</td>
	</tr>
	";
	$line_count++;

	$html_body .= "
	<tr>
		<td>$sp</td>
		<td>$sp</td>
		<td $cell_style>V.A.T.</td>
		<td $cell_style $ar>" . money_format_kdb($inv_vat, true, true, true) . "</td>
	</tr>
	";
	$line_count++;

	$cell_style = "style=\"border-top:dashed 1px black; border-bottom:dashed 1px black;\"";
	$html_body .= "
	<tr>
		<td>$sp</td>
		<td>$sp</td>
		<td $cell_style>TOTAL</td>
		<td $cell_style $ar>" . money_format_kdb($inv_gross, true, true, true) . "</td>
	</tr>
	";
	$line_count++;

	$cell_style = "style=\"border-bottom:dashed 1px black;\"";
	$html_body .= "
	<tr>
		<td $col2><span $cell_style>BACS PAYMENT</span></td>
	</tr>
	";
	$line_count++;

	$html_body .= "
	<tr>
		<td $col2>$bank_line_1</td>
	</tr>
	";
	$line_count++;

	$html_body .= "
	<tr>
		<td $col2>$bank_line_2</td>
	</tr>
	";
	$line_count++;

	$html_body .= "
	<tr>
		<td $col2>$bank_line_2b</td>
	</tr>
	";
	$line_count++;

	$html_body .= "
	<tr>
		<td $col2>$bank_line_3</td>
	</tr>
	";
	$line_count++;

	$html_body .= "
	<tr>
		<td $col2>$bank_line_4</td>
	</tr>
	";
	$line_count++;

	$html_body .= "
	</table>
	";
	return $html_body;
} # pdf_create_inv_trc_oneoff()

function pdf_create_inv_trc_stmt($c_code, $client_address, $inv_num, $inv_dt, $inv_net, $inv_vat, $inv_gross, $bills, 
										$inv_start_dt, $inv_end_dt)
{
	global $ar;
	global $bank_line_1; # settings.php
	global $bank_line_2; # settings.php
	global $bank_line_2b; # settings.php
	global $bank_line_3; # settings.php
	global $bank_line_4; # settings.php
	global $col2;
	global $col3;
	global $col5;
	global $col6;
	global $vat_number; # settings.php
	
	$sp = "&nbsp;";
	$gap = $sp . $sp . $sp;
	#$col_count = 6;
	$blank_line = "
	<tr>
		<td>$sp</td>
	</tr>
	";

	$thgap = "<th style=\"width:5%\">$sp</th>";
	$tdgap = "<td>$sp</td>";
	$bill_lines_hdr = "<tr><th style=\"width:15%\">OUR REF</th>$thgap
							 <th style=\"width:20%\">YOUR REF</th>$thgap
							 <th style=\"width:35%\">NAME</th>$thgap
							 <th style=\"width:15%\">COST</th>
						</tr>";
	$bill_lines = array();
	$cost_total = 0.0;
	foreach ($bills as $bill)
	{
		$cost = round(floatval($bill['BL_COST']), 2);
		$cost_total += $cost;
		$bill_lines[] = "
		<tr>
			<td>{$bill['JT_VILNO']}</td>$tdgap
			<td>{$bill['JT_CLIENT_REF']}</td>$tdgap
			<td>" . str_replace(' ', '&nbsp;', $bill['JT_SUBJECT']) . "</td>$tdgap
			<td $ar>" . money_format_kdb($cost, true, true, true) . "</td>
		</tr>
		";
	}
	#dprint("pdf_create_inv_trc_stmt(): created " . count($bill_lines) . " HTML bill_lines");#
	$bill_lines[] = "<tr>$tdgap</tr>";
	$style = "style=\"font-weight:bold;\"";
	$bill_lines[] = "<tr><td $col6 $ar $style>TOTAL COST</td>
							<td $style>" . money_format_kdb($cost_total, true, true, true) . "</td></tr>";
	
	# --- PAGE 1 ------------------

	$page = 1;
	$lines_in_body = 23; # Support ticket #1072 (24/08/20): 25 lines (was 24) ###; #Feedback #223		26;
	$line_count = 0;

	$html_body = "
	<table width=\"100%\" border=\"0\"><!---->
	";
			
	$html_body .= "
	<tr>
		<td $col2>INVOICE NUMBER{$gap}{$c_code}/{$inv_num}</td>
	</tr>
	";
	$line_count++;

	$html_body .= "
	<tr>
		<!--Date label-->	<td style=\"width:100px;\">Date:</td>
		<!--Date value-->	<td>" . date_for_sql($inv_dt, true, false, true) . "</td>
		<!--Net label-->	<td style=\"width:100px;\">$sp</td>
		<!--Net value-->	<td style=\"width:80px;\">$sp</td>
		<!--Margin-->		<td style=\"width:40px;\">$sp</td>
	</tr>
	";
	$line_count++;

	$html_body .= $blank_line;
	$line_count++;

	$ca_count = count($client_address);
	$ca_ix = 0;
	if ($ca_ix < $ca_count)
		$ca_line = $client_address[$ca_ix++];
	else
		$ca_line = '';
	$html_body .= "
	<tr>
		<td>Attn:</td>
		<td>$ca_line</td>
	</tr>
	";
	$line_count++;

	if ($ca_ix < $ca_count)
		$ca_line = $client_address[$ca_ix++];
	else
		$ca_line = '';
	$html_body .= "
	<tr>
		<td></td>
		<td>$ca_line</td>
	</tr>
	";
	$line_count++;

	if ($ca_ix < $ca_count)
		$ca_line = $client_address[$ca_ix++];
	else
		$ca_line = '';
	$html_body .= "
	<tr>
		<td>$sp</td>
		<td>$ca_line</td>
		<td $col2>VAT No.{$gap}{$vat_number}</td>
	</tr>
	";
	$line_count++;

	if ($ca_ix < $ca_count)
	{
		$ca_line = $client_address[$ca_ix++];
		$html_body .= "
		<tr>
			<td></td>
			<td>$ca_line</td>
		</tr>
		";
		$line_count++;

		if ($ca_ix < $ca_count)
		{
			$ca_line = $client_address[$ca_ix++];
			$html_body .= "
			<tr>
				<td></td>
				<td>$ca_line</td>
			</tr>
			";
			$line_count++;
	
			if ($ca_ix < $ca_count)
			{
				$ca_line = $client_address[$ca_ix++];
				$html_body .= "
				<tr>
					<td></td>
					<td>$ca_line</td>
				</tr>
				";
				$line_count++;
	
				if ($ca_ix < $ca_count)
				{
					$ca_line = $client_address[$ca_ix++];
					$html_body .= "
					<tr>
						<td></td>
						<td>$ca_line</td>
					</tr>
					";
					$line_count++;
				}
			}
		}
	}
	
	$html_body .= $blank_line;
	$line_count++;

	$cell_style = "style=\"border-bottom:double 1px black;\"";
	$html_body .= "
	<tr>
		<td $col2><b><span $cell_style>DESCRIPTION</span></b></td>
	</tr>
	";
	$line_count++;

	$html_body .= $blank_line;
	$line_count++;

	$html_body .= "
	<tr>
		<td $col3>SERVICES BETWEEN " . date_for_sql($inv_start_dt, true, false, true) . " AND " . date_for_sql($inv_end_dt, true, false, true) . "</td>
		<td $col2>" . money_format_kdb($inv_net, true, true, true) . "</td>
	</tr>
	";
	$line_count++;

	$html_body .= "
	<tr>
		<td $col3>(See Attached Schedule)</td>
	</tr>
	";
	$line_count++;

	while ($line_count < $lines_in_body)
	{
		$html_body .= $blank_line;
		$line_count++;
	}

	$cell_style = "style=\"border-top:dashed 1px black;\"";
	$html_body .= "
	<tr>
		<td>$sp</td>
		<td>$sp</td>
		<td $cell_style>NET TOTAL</td>
		<td $cell_style $ar>" . money_format_kdb($inv_net, true, true, true) . "</td>
	</tr>
	";
	$line_count++;

	$html_body .= "
	<tr>
		<td>$sp</td>
		<td>$sp</td>
		<td $cell_style>V.A.T.</td>
		<td $cell_style $ar>" . money_format_kdb($inv_vat, true, true, true) . "</td>
	</tr>
	";
	$line_count++;

	$cell_style = "style=\"border-top:dashed 1px black; border-bottom:dashed 1px black;\"";
	$html_body .= "
	<tr>
		<td>$sp</td>
		<td>$sp</td>
		<td $cell_style>TOTAL</td>
		<td $cell_style $ar>" . money_format_kdb($inv_gross, true, true, true) . "</td>
	</tr>
	";
	$line_count++;

	$cell_style = "style=\"border-bottom:dashed 1px black;\"";
	$html_body .= "
	<tr>
		<td $col2><span $cell_style>BACS PAYMENT</span></td>
	</tr>
	";
	$line_count++;

	$html_body .= "
	<tr>
		<td $col2>$bank_line_1</td>
	</tr>
	";
	$line_count++;

	$html_body .= "
	<tr>
		<td $col2>$bank_line_2</td>
	</tr>
	";
	$line_count++;

	$html_body .= "
	<tr>
		<td $col2>$bank_line_2b</td>
	</tr>
	";
	$line_count++;

	$html_body .= "
	<tr>
		<td $col2>$bank_line_3</td>
	</tr>
	";
	$line_count++;

	$html_body .= "
	<tr>
		<td $col2>$bank_line_4</td>
	</tr>
	";
	$line_count++;

	$html_body .= "
	</table>
	
	<p style=\"page-break-after:always;\"</p>
	";
	
	# --- PAGE 2 plus ------------------
	
	$loop_safety = 1000; # to avoid infinite looping
	$line_ix = 0; # first line in $bill_lines
	$lines_in_table = 26; # $bill_lines on one page
	
	while ($page < $loop_safety)
	{
		$page++; # first page in this loop will be 2
		$line_count = 0;

		$html_body .= "
		<table border=\"0\">
		";

		$html_body .= "
		<tr>
			<td $col2>$sp</td>
			<td $col5>SCHEDULE FOR INVOICE No. $inv_num</td>
		</tr>
		";
		$line_count++;

		$html_body .= "
		<tr>
			<td $col2>$sp</td>
			<td $col5>" . date_for_sql($inv_dt, true, false, true) . "</td>
		</tr>
		";
		$line_count++;

		$html_body .= "
		<tr>
			<td>Attn:</td>
		</tr>
		";
		$line_count++;

		$html_body .= $blank_line;
		$line_count++;

		$html_body .= $bill_lines_hdr;
		$line_count++;
		
		$create_new_page = false;
		for ( ; $line_ix < count($bill_lines); $line_ix++)
		{
			$html_body .= $bill_lines[$line_ix];
			$line_count++;
			
			if ($lines_in_table <= $line_count)
			{
				$create_new_page = true;
				$line_ix++;
				break; # from for($line_ix)
			}
		} # for ($line_ix)

		$html_body .= "
		</table>
		";
		
		if ($create_new_page)
		{
			$html_body .= "
			<p style=\"page-break-after:always;\"</p>
			";
		}
		else
			break; # from while($page)
	} # while($page)
	
	$html_body .= "
	</table>
	";
	return $html_body;
	
} # pdf_create_inv_trc_stmt()

function pdf_create_inv_coll_stmt($client2_id, $c_code, $inv_num, $inv_dt, $inv_net, $inv_vat, $inv_gross, $payments, 
														$client_address, $inv_start_dt, $inv_end_dt)
{
	global $al;
	global $ar;
	global $bank_line_1; # settings.php
	global $bank_line_2; # settings.php
	global $bank_line_2b; # settings.php
	global $bank_line_3; # settings.php
	global $bank_line_4; # settings.php
	global $col2;
	global $col3;
	global $col4;
	global $col8;
	global $col9;
	global $id_ROUTE_cspent;
	global $id_ROUTE_direct;
	global $id_ROUTE_fwd;
	global $id_ROUTE_tous;
	global $vat_number; # settings.php
	
	$local_routes = array($id_ROUTE_cspent => 'CSP', $id_ROUTE_direct => 'DIR', $id_ROUTE_fwd => 'FWD', $id_ROUTE_tous => 'VIL');

	$sp = "&nbsp;";
	$thgap = "<th width=\"6\">$sp</th>";
	$tdgap = "<td>$sp</td>";
	
	$payment_sum = 0.0;
	$comm_pc = 0.0;
	$pay_to_us = 0.0;
	$pay_dir = 0.0;
	$pay_fwd = 0.0; 
	$pay_client = 0.0;
	
	list($all_jobs_count, $all_jobs_value, $all_jobs_collected, $closed_jobs_count, $closed_jobs_value, 
			$paid_full_count, $paid_full_collected, $client_deduct_as) = 
				invoice_info_collect($client2_id, $inv_dt);
	$pcent_collected = round(100.0 * floatval($all_jobs_collected) / $all_jobs_value, 2);
	$pcent_closed_value = round(100.0 * floatval($closed_jobs_value) / $all_jobs_value, 2);
	$pcent_full_value = round(100.0 * floatval($paid_full_collected) / $all_jobs_value, 2);
	$open_jobs_value = $all_jobs_value - $closed_jobs_value;
	$open_jobs_count = $all_jobs_count - $closed_jobs_count;
	
	$payment_lines = array("<tr><th $al>DATE</th>$thgap<th $al>NAME</th>$thgap<th $al>OUR REF</th>$thgap<th $al>YOUR REF</th>$thgap" .
								"<th $ar>AMOUNT</th>$thgap<th $al>ROUTE</th></tr>");
	
	foreach ($payments as $payment)
	{
		$amount = round(floatval($payment['COL_AMT_RX']), 2);
		$cpc = round(floatval($payment['PAY_PERCENT']), 3);
		
		$payment_sum += $amount;
		if ($comm_pc < $cpc)
			$comm_pc = $cpc;
		
		if ($payment['PAYMENT_ROUTE_ID'] == $id_ROUTE_tous)
		{
			$pay_to_us += $amount;
			$pay_client += $amount;
		}
		elseif ($payment['PAYMENT_ROUTE_ID'] == $id_ROUTE_direct)
			$pay_dir += $amount;
		elseif ($payment['PAYMENT_ROUTE_ID'] == $id_ROUTE_fwd)
			$pay_fwd += $amount;
		
		if (array_key_exists($payment['PAYMENT_ROUTE_ID'], $local_routes))
			$route = $local_routes[$payment['PAYMENT_ROUTE_ID']];
		else
			$route = "*{$payment['PAYMENT_ROUTE_ID']}*";
			
		$payment_lines[] = "
		<tr>
			<td>" . date_for_sql($payment['COL_DT_RX'], true, false, true) . "</td>$tdgap
			<td>{$payment['JC_SUBJECT']}</td>$tdgap
			<td>{$payment['JC_VILNO']}</td>$tdgap
			<td>{$payment['JC_CLIENT_REF']}</td>$tdgap
			<td $ar>" . money_format_kdb($amount, true, true, true). "</td>$tdgap
			<td>$route</td>
		</tr>
		";
	}
	if ($client_deduct_as)
		$pay_client -= $inv_gross;
	
	$blank_line = "<tr><td>$sp</td></tr>";
	
	# --- PAGE 1 ------------------
	
	$page = 1;
	$lines_in_body = 21; # Support ticket #1072 (24/08/20): 25 lines (was 22) ###; #Feedback #223		24;
	$line_count = 0;

	$html_body = "
	<table width=\"100%\" border=\"0\"><!---->
	";

	$ca_count = count($client_address);
	$client_address_0 = $sp; # company name
	$client_address_1 = $sp; # address line 1
	$client_address_2 = $sp; # address line 2
	$client_address_3 = $sp; # address line 3
	$client_address_4 = $sp; # address line 4
	$client_address_5 = $sp; # address line 5
	$client_address_6 = $sp; # address postcode
	if ((0 < $ca_count) && $client_address[0])
		$client_address_0 = $client_address[0];
	if ((1 < $ca_count) && $client_address[1])
	{
		$client_address_1 = $client_address[1];
		if ((2 < $ca_count) && $client_address[2])
		{
			$client_address_2 = $client_address[2];
			if ((3 < $ca_count) && $client_address[3])
			{
				$client_address_3 = $client_address[3];
				if ((4 < $ca_count) && $client_address[4])
				{
					$client_address_4 = $client_address[4];
					if ((5 < $ca_count) && $client_address[5])
					{
						$client_address_5 = $client_address[5];
						if ((6 < $ca_count) && $client_address[6])
							$client_address_6 = $client_address[6];
					}
				}
			}
		}
	}
	$html_body .= "
	<tr>
		<td>$client_address_0</td>
	</tr>
	";
	$line_count++;
	
	$html_body .= "
	<tr>
		<td>$client_address_1</td>
	</tr>
	";
	$line_count++;
	
	$html_body .= "
	<tr>
		<td>$client_address_2</td>
		<td>$sp</td>
		<td $col3>INV. No. {$c_code}/{$inv_num}</td>
	</tr>
	";
	$line_count++;
	
	$html_body .= "
	<tr>
		<td>$client_address_3</td>
		<td>$sp</td>
		<td $col3>" . date_for_sql($inv_dt, true, false, true) . "</td>
	</tr>
	";
	$line_count++;

	$html_body .= "
	<tr>
		<td>$client_address_4</td>
	</tr>
	";
	$line_count++;

	$html_body .= "
	<tr>
		<td>$client_address_5</td>
	</tr>
	";
	$line_count++;
	
	$html_body .= "
	<tr>
		<td>$client_address_6</td>
	</tr>
	";
	$line_count++;
	
	$html_body .= "
	<tr>
		<td $col2>$sp</td>
		<td $col3>VAT No. $vat_number</td>
	</tr>
	";
	$line_count++;
	
	$html_body .= $blank_line;
	$line_count++;
	
	$cell_style = "style=\"border-bottom:double 1px black;\"";
	$html_body .= "
	<tr>
		<td><b><span $cell_style>DESCRIPTION</span></b></td>
	</tr>
	";
	$line_count++;
	
	$html_body .= "
	<tr>
		<td $col2>COLLECTIONS BETWEEN " . date_for_sql($inv_start_dt, true, false, true) . " AND " . date_for_sql($inv_end_dt, true, false, true) . "</td>
		<td $ar>" . money_format_kdb($inv_net, true, true, true) ."</td>
	</tr>
	";
	$line_count++;
	
	$html_body .= "
	<tr>
		<td $col2>" . money_format_kdb($payment_sum, true, true, true) ." collected @ $comm_pc percent commission</td>
	</tr>
	";

	# Fill up the middle gap
	while ($line_count < $lines_in_body)
	{
		$html_body .= $blank_line;
		$line_count++;
	}

	$cell_style = "style=\"border-top:dashed 1px black;\"";
	$html_body .= "
	<tr>
		<td>$sp</td>
		<td $cell_style>NET TOTAL</td>
		<td $cell_style $ar>" . money_format_kdb($inv_net, true, true, true) . "</td>
	</tr>
	";
	$line_count++;
	
	$html_body .= "
	<tr>
		<td>$sp</td>
		<td $cell_style>V.A.T.</td>
		<td $cell_style $ar>" . money_format_kdb($inv_vat, true, true, true) . "</td>
	</tr>
	";
	$line_count++;
	
	$cell_style = "style=\"border-top:dashed 1px black; border-bottom:dashed 1px black;\"";
	$html_body .= "
	<tr>
		<td>$sp</td>
		<td $cell_style>TOTAL</td>
		<td $cell_style $ar>" . money_format_kdb($inv_gross, true, true, true) . "</td>
	</tr>
	";
	$line_count++;
	
	$cell_style = "style=\"border-bottom:dashed 1px black;\"";
	$html_body .= "
	<tr>
		<td $col2><span $cell_style>BACS PAYMENT</span></td>
	</tr>
	";
	$line_count++;
	
	$html_body .= "
	<tr>
		<td $col2>$bank_line_1</td>
	</tr>
	";
	$line_count++;
	
	$html_body .= "
	<tr>
		<td $col2>$bank_line_2</td>
	</tr>
	";
	$line_count++;
	
	$html_body .= "
	<tr>
		<td $col2>$bank_line_2b</td>
	</tr>
	";
	$line_count++;

	$html_body .= "
	<tr>
		<td $col2>$bank_line_3</td>
	</tr>
	";
	$line_count++;
	
	$html_body .= "
	<tr>
		<td $col2>$bank_line_4</td>
	</tr>
	";
	$line_count++;
	
	$html_body .= "
	</table>
	
	<p style=\"page-break-after:always;\"</p>
	";
	
	# --- PAGE 2 ------------------
	
	$page++;
	$lines_in_body = 1;
	$line_count = 0;
	
	$html_body .= "
	<table>
	<tr>
		<td $col3>$client_address_0</td>
	</tr>
	";
	$line_count++;
	
	$html_body .= "
	<tr>
		<td $col3>$client_address_1</td>
	</tr>
	";
	$line_count++;
	
	$html_body .= "
	<tr>
		<td $col3>$client_address_2</td>
		<td>$inv_num</td>
	</tr>
	";
	$line_count++;
	
	$html_body .= "
	<tr>
		<td $col3>$client_address_3</td>
		<td>" . date_for_sql($inv_dt, true, false, true) . "</td>
	</tr>
	";
	$line_count++;
	
	$html_body .= "
	<tr>
		<td $col3>$client_address_4</td>
	</tr>
	";
	$line_count++;
	
	$html_body .= "
	<tr>
		<td $col3>$client_address_5</td>
	</tr>
	";
	$line_count++;
	
	$html_body .= "
	<tr>
		<td $col3>$client_address_6</td>
	</tr>
	";
	$line_count++;
	
	$html_body .= "
	$blank_line
	";
	$line_count++;
	
	$html_body .= "
	<tr>
		<td $col3>Dear Sir,</td>
	</tr>
	";
	$line_count++;
	
	$html_body .= "
	$blank_line
	";
	$line_count++;
	
	$html_body .= "
	<tr>
		<td $col3>Please find enclosed our print outs cnfirming payments on your account.
			<br>Original numberof Accounts &ndash; $all_jobs_count, Value " . money_format_kdb($all_jobs_value, true, true, true) . "
			</td>
	</tr>
	";
	$line_count++;
	
	$html_body .= "
	<tr>
		<td>To us</td>
		<td $ar width=\"10\">&ndash;</td><td $ar>{$sp}" . money_format_kdb($pay_to_us, true, true, true) . "</td>
	</tr>
	";
	$line_count++;
	
	$html_body .= "
	<tr>
		<td>Direct to Yourselves</td>
		<td $ar>&ndash;</td><td $ar>{$sp}" . money_format_kdb($pay_dir, true, true, true) . "</td>
	</tr>
	";
	$line_count++;
	
	$html_body .= "
	<tr>
		<td>Forwarded</td>
		<td $ar>&ndash;</td><td $ar>{$sp}" . money_format_kdb($pay_fwd, true, true, true) . "</td>
	</tr>
	";
	$line_count++;
	
	$html_body .= "
	<tr>
		<td>Total collected to date</td>
		<td $ar>&ndash;</td><td $ar>{$sp}" . money_format_kdb($all_jobs_collected, true, true, true) . "</td>
	</tr>
	";
	$line_count++;
	
	$html_body .= "
	<tr>
		<td>Percentage collected</td>
		<td $ar>&ndash;</td><td $ar>{$sp}" . number_format($pcent_collected, 2) . "%</td>
	</tr>
	";
	$line_count++;
	
	$html_body .= "
	$blank_line
	";
	$line_count++;
	
	$html_body .= "
	<tr>
		<td>Number of accounts closed out to date</td>
		<td $ar>&ndash;</td><td $ar>{$sp}$closed_jobs_count</td>
	</tr>
	";
	$line_count++;
	
	$html_body .= "
	<tr>
		<td $ar>Batch Value</td>
		<td $ar>&ndash;</td><td $ar>{$sp}" . money_format_kdb($closed_jobs_value, true, true, true) . "</td>
	</tr>
	";
	$line_count++;
	
	$html_body .= "
	<tr>
		<td $ar>Percentage of Batch Value</td>
		<td $ar>&ndash;</td><td $ar>{$sp}" . number_format($pcent_closed_value, 2) . "%</td>
	</tr>
	";
	$line_count++;
	
	$html_body .= "
	<tr>
		<td $col4>Paid in Full &ndash; $paid_full_count &ndash; " . money_format_kdb($paid_full_collected, true, true, true) . " &ndash; 
					" . number_format($pcent_full_value, 2) . "%</td>
	</tr>
	";
	$line_count++;
	
	$html_body .= "
	<tr>
		<td>Current Batch Value</td>
		<td $ar>&ndash;</td><td $ar>{$sp}" . money_format_kdb($open_jobs_value, true, true, true) . "</td>
	</tr>
	";
	$line_count++;
	
	$html_body .= "
	<tr>
		<td>Current number of accounts</td>
		<td $ar>&ndash;</td><td $ar>{$sp}$open_jobs_count</td>
	</tr>
	";
	$line_count++;
	
	$html_body .= "
	$blank_line
	";
	$line_count++;
	
	$html_body .= "
	<tr>
		<td $col4>Please find attached invoice for collecting " . money_format_kdb($payment_sum, true, true, true) . " @ $comm_pc%</td>
	</tr>
	";
	$line_count++;
	
	$html_body .= "
	<tr>
		<td $col4>Please find enclosed cheque for " . money_format_kdb($pay_client, true, true, true) . "</td>
	</tr>
	";
	$line_count++;
	
	$html_body .= "
	<tr>
		<td $col4>Assuring you ofour best attention at all times</td>
	</tr>
	";
	$line_count++;
	
	$html_body .= "
	<tr>
		<td $col4>Yours sincerely</td>
	</tr>
	";
	$line_count++;
	
	$html_body .= "
	$blank_line
	";
	$line_count++;
	
	$html_body .= "
	$blank_line
	";
	$line_count++;
	
	$html_body .= "
	<tr>
		<td $col4>VIL Collections</td>
	</tr>
	";
	$line_count++;
	
	$html_body .= "
	</table>
	
	<p style=\"page-break-after:always;\"</p>
	";
	
	# --- PAGE 3 plus ------------------
	
	$loop_safety = 1000; # to avoid infinite looping
	$line_ix = 0; # first line in $payment_lines
	$lines_in_table = 22; # $payment_lines on one page
	
	while ($page < $loop_safety)
	{
		$page++; # first page in this loop will be 3
		$line_count = 0;

		$html_body .= "
		<table>
		";

		$html_body .= "
		<tr>
			<td $col8>$sp</td>
			<td $col3>RE. INVOICE $inv_num</td>
		</tr>
		";
		$line_count++;

		$html_body .= "
		<tr>
			<td $col8>$sp</td>
			<td $col3>" . date_for_sql($inv_dt, true, false, true) . "</td>
		</tr>
		";
		$line_count++;

		$html_body .= "
		<tr>
			<td $col9>COLLECTIONS MADE FOR... {$client_address_0}</td>
			$tdgap
			<td $ar>Page " . ($page - 2) . "</td>
		</tr>
		";
		$line_count++;

		$html_body .= $blank_line;
		$line_count++;

		$create_new_page = false;
		for ( ; $line_ix < count($payment_lines); $line_ix++)
		{
			$html_body .= $payment_lines[$line_ix];
			$line_count++;
			
			if ($lines_in_table < $line_count)
			{
				$create_new_page = true;
				$line_ix++;
				break; # from for($line_ix)
			}
		} # for ($line_ix)

		$html_body .= "
		</table>
		";
		
		if ($create_new_page)
		{
			$html_body .= "
			<p style=\"page-break-after:always;\"</p>
			";
		}
		else
			break; # from while($page)
	} # while($page)
	
	return $html_body;
	
} # pdf_create_inv_coll_stmt()

function pdf_create_inv_gen($c_code, $client_address, $inv_num, $inv_type, $inv_dt, $inv_net, $inv_vat, $inv_gross, $bills)
{
	# Invoice or Credit
	global $ar;
	global $at;
	global $bank_line_1; # settings.php
	global $bank_line_2; # settings.php
	global $bank_line_2b; # settings.php
	global $bank_line_3; # settings.php
	global $bank_line_4; # settings.php
	global $col2;
	global $vat_number; # settings.php
	
	$sp = "&nbsp;";
	$gap = $sp . $sp . $sp;
	#$col_count = 6;

	$ca_count = count($client_address);
	$client_address_0 = $sp; # company name
	$client_address_1 = $sp; # address line 1
	$client_address_2 = $sp; # address line 2
	$client_address_3 = $sp; # address line 3
	$client_address_4 = $sp; # address line 4
	$client_address_5 = $sp; # address line 5
	$client_address_6 = $sp; # address postcode
	if ((0 < $ca_count) && $client_address[0])
		$client_address_0 = $client_address[0];
	if ((1 < $ca_count) && $client_address[1])
	{
		$client_address_1 = $client_address[1];
		if ((2 < $ca_count) && $client_address[2])
		{
			$client_address_2 = $client_address[2];
			if ((3 < $ca_count) && $client_address[3])
			{
				$client_address_3 = $client_address[3];
				if ((4 < $ca_count) && $client_address[4])
				{
					$client_address_4 = $client_address[4];
					if ((5 < $ca_count) && $client_address[5])
					{
						$client_address_5 = $client_address[5];
						if ((6 < $ca_count) && $client_address[6])
							$client_address_6 = $client_address[6];
					}
				}
			}
		}
	}

	$blank_line = "
	<tr>
		<td>$sp</td>
	</tr>
	";

	$lines_in_body = 25; # Support ticket #1072 (24/08/20): 25 lines (was 26) ##; #Feedback #223		28;
	$line_count = 0;

	if ($inv_type == 'C')
	{
		$type_text_u = 'CREDIT';
		$type_text_p = 'Credit';
	}
	else
	{
		$type_text_u = 'INVOICE';
		$type_text_p = 'Invoice';
	}
	
	$html_body = "
	<table width=\"100%\" border=\"0\"><!---->
	";
			
	$html_body .= "
	<tr>
		<td $col2>$type_text_u NUMBER{$gap}{$c_code}/{$inv_num}</td>
	</tr>
	";
	$line_count++;

	$html_body .= "
	<tr>
		<!--Date label-->	<td style=\"width:50px;\">Date:</td>
		<!--Date value-->	<td>" . date_for_sql($inv_dt, true, false, true) . "</td>
		<!--Net label-->	<td style=\"width:100px;\">$sp</td>
		<!--Net value-->	<td style=\"width:80px;\">$sp</td>
		<!--Margin-->		<td style=\"width:40px;\">$sp</td>
	</tr>
	";
	#style=\"width:250px;\"
	$line_count++;

	$html_body .= $blank_line;
	$line_count++;

//	$ca_count = count($client_address);
//	$ca_ix = 0;
//	if ($ca_ix < $ca_count)
//		$ca_line = $client_address[$ca_ix++];
//	else
//		$ca_line = '';
	$html_body .= "
	<tr>
		<td $col2>Attn:&nbsp;&nbsp;$client_address_0</td>
	</tr>
	";
	$line_count++;

//	if ($ca_ix < $ca_count)
//		$ca_line = $client_address[$ca_ix++];
//	else
//		$ca_line = '';
	$html_body .= "
	<tr>
		<td $col2>$client_address_1</td>
	</tr>
	";
	$line_count++;

//	if ($ca_ix < $ca_count)
//		$ca_line = $client_address[$ca_ix++];
//	else
//		$ca_line = '';
	$html_body .= "
	<tr>
		<td $col2>$client_address_2</td>
	</tr>
	";
	$line_count++;

//	if ($ca_ix < $ca_count)
//	{
//		$ca_line = $client_address[$ca_ix++];
		$html_body .= "
		<tr>
			<td $col2>$client_address_3</td>
		</tr>
		";
		$line_count++;

//		if ($ca_ix < $ca_count)
//		{
//			$ca_line = $client_address[$ca_ix++];
			$html_body .= "
			<tr>
				<td $col2>$client_address_4</td>
			</tr>
			";
			$line_count++;
	
//			if ($ca_ix < $ca_count)
//			{
//				$ca_line = $client_address[$ca_ix++];
				$html_body .= "
				<tr>
					<td $col2>$client_address_5</td>
					<td $col2>VAT No.{$gap}{$vat_number}</td>
				</tr>
				";
				$line_count++;
	
//				if ($ca_ix < $ca_count)
//				{
//					$ca_line = $client_address[$ca_ix++];
					$html_body .= "
					<tr>
						<td $col2>$client_address_6</td>
					</tr>
					";
					$line_count++;
//				}
//			}
//		}
//	}
	
	$html_body .= $blank_line;
	$line_count++;

	$cell_style = "style=\"border-bottom:double 1px black;\"";
	$html_body .= "
	<tr>
		<td $col2><b><span $cell_style>DESCRIPTION</span></b></td>
	</tr>
	";
	$line_count++;

	$html_body .= $blank_line;
	$line_count++;

	$cell_style = "style=\"border-bottom:dashed 1px black;\"";
	$html_body .= "
	<tr>
		<td $col2><span $cell_style>$type_text_p Details</span></td>
	</tr>
	";
	$line_count++;

	$html_body .= $blank_line;
	$line_count++;

	if ($bills)
	{
		foreach ($bills as $bill_line)
		{
			$html_body .= "
			<tr>
				<td $at $col2>{$bill_line['BL_DESCR']}</td>
				<td>$sp</td>
				<td $at $ar>" . money_format_kdb($bill_line['BL_COST'], true, true, true) . "</td>
			</tr>
			";
			$line_count++;
		}
	}
	else
	{
		$html_body .= "
		<tr>
			<td $col2>(no billing lines)</td>
		</tr>
		";
		$line_count++;
	}

	while ($line_count < $lines_in_body)
	{
		$html_body .= $blank_line;
		$line_count++;
	}

	$cell_style = "style=\"border-top:dashed 1px black;\"";
	$html_body .= "
	<tr>
		<td>$sp</td>
		<td>$sp</td>
		<td $cell_style>NET TOTAL</td>
		<td $cell_style $ar>" . money_format_kdb($inv_net, true, true, true) . "</td>
	</tr>
	";
	$line_count++;

	$html_body .= "
	<tr>
		<td>$sp</td>
		<td>$sp</td>
		<td $cell_style>V.A.T.</td>
		<td $cell_style $ar>" . money_format_kdb($inv_vat, true, true, true) . "</td>
	</tr>
	";
	$line_count++;

	$cell_style = "style=\"border-top:dashed 1px black; border-bottom:dashed 1px black;\"";
	$html_body .= "
	<tr>
		<td>$sp</td>
		<td>$sp</td>
		<td $cell_style>TOTAL</td>
		<td $cell_style $ar>" . money_format_kdb($inv_gross, true, true, true) . "</td>
	</tr>
	";
	$line_count++;

	$cell_style = "style=\"border-bottom:dashed 1px black;\"";
	$html_body .= "
	<tr>
		<td $col2><span $cell_style>BACS PAYMENT</span></td>
	</tr>
	";
	$line_count++;

	$html_body .= "
	<tr>
		<td $col2>$bank_line_1</td>
	</tr>
	";
	$line_count++;

	$html_body .= "
	<tr>
		<td $col2>$bank_line_2</td>
	</tr>
	";
	$line_count++;

	$html_body .= "
	<tr>
		<td $col2>$bank_line_2b</td>
	</tr>
	";
	$line_count++;

	$html_body .= "
	<tr>
		<td $col2>$bank_line_3</td>
	</tr>
	";
	$line_count++;

	$html_body .= "
	<tr>
		<td $col2>$bank_line_4</td>
	</tr>
	";
	$line_count++;

	$html_body .= "
	</table>
	";
	return $html_body;
} # pdf_create_inv_gen()

function link_ic()
{
	$invoice_id = post_val('invoice_id', true);
	$credit_id = post_val('link_credit_id', true);
	if (!((0 < $invoice_id) && (0 < $credit_id)))
	{
		dprint("link_ic() bad invoice (" . post_val('invoice_id') . ") or credit (" . post_val('link_credit_id') . ")");
		return;
	}
	
	$sql = "UPDATE INVOICE SET LINKED_ID=$credit_id WHERE INVOICE_ID=$invoice_id";
	audit_setup_gen('INVOICE', 'INVOICE_ID', $invoice_id, 'LINKED_ID', $credit_id);
	sql_execute($sql, true); # audited
	
	$sql = "UPDATE INVOICE SET LINKED_ID=$invoice_id WHERE INVOICE_ID=$credit_id";
	audit_setup_gen('INVOICE', 'INVOICE_ID', $credit_id, 'LINKED_ID', $invoice_id);
	sql_execute($sql, true); # audited
	
} # link_ic()

function send_invoices_1()
{
	global $ac;
	global $ar;
	global $col8;
	global $grey;

	# List all invoices with email addresses and list all without, ask user to confirm send.
	$invoice_list = post_val('invoice_id');
	#$invoice_ids = explode(',', $invoice_list);

	$clients = array();
	sql_encryption_preparation('CLIENT2');
	$sql = "SELECT DISTINCT C.CLIENT2_ID, C.C_CODE, " . sql_decrypt('C.C_CO_NAME', '', true) . ", C.INV_EMAILED, 
				" . sql_decrypt('C.INV_EMAIL_NAME', '', true) . ", " . sql_decrypt('C.INV_EMAIL_ADDR', '', true) . "
			FROM INVOICE AS I INNER JOIN CLIENT2 AS C ON C.CLIENT2_ID=I.CLIENT2_ID
			WHERE I.INVOICE_ID IN ($invoice_list)";
	sql_execute($sql);
	while (($newArray = sql_fetch_assoc()) != false)
	{
		if (!$newArray['INV_EMAIL_NAME'])
			$newArray['INV_EMAIL_NAME'] = "Sir/Madam";
		if (!array_key_exists($newArray['CLIENT2_ID'], $clients))
			$clients[$newArray['CLIENT2_ID']] = $newArray;
	}

	$invoices_send = array();
	$invoices_dont = array();
	$invoices_could = array(); # Invoices that are part of $invoices_dont but user may opt to send anyway
	$sql = "SELECT I.INVOICE_ID, I.CLIENT2_ID, I.INV_NUM, I.INV_SYS, I.INV_TYPE, I.INV_DT, I.INV_NET, I.INV_VAT, I.INV_DUE_DT, I.INV_STMT,
				I.INV_APPROVED_DT, I.INV_PAID, I.INV_EMAIL_ID, I.INV_POSTED_DT, I.IMPORTED, I.OBSOLETE
			FROM INVOICE AS I
			WHERE I.INVOICE_ID IN ($invoice_list)
			ORDER BY INV_NUM DESC";
	#dprint($sql);#
	sql_execute($sql);
	while (($newArray = sql_fetch_assoc()) != false)
	{
		$dont = '';
		$could = 0;
		$client2_id = $newArray['CLIENT2_ID'];
		if ($newArray['INV_TYPE'] == 'I')
		{
			if ($clients[$client2_id]['INV_EMAILED'])
			{
				if ($clients[$client2_id]['INV_EMAIL_ADDR'])
				{
					if (0.0 < $newArray['INV_NET'])
					{
						if (floatval($newArray['INV_PAID']) < (floatval($newArray['INV_NET']) + floatval($newArray['INV_VAT'])))
						{
							if (!$newArray['INV_EMAIL_ID'])
							{
								if (!$newArray['IMPORTED'])
								{
									if ($newArray['INV_APPROVED_DT'])
									{
										if (!pdf_link('i', "c{$clients[$client2_id]['C_CODE']}", $newArray['INV_NUM']))
											$dont = "This invoice has no PDF of itself";
									}
									else
										$dont = "Invoice has not yet been approved";
								}
								else
									$dont = "Invoice is one imported from the old system";
							}
							else
							{
								$dont = "Invoice has already been emailed out";
								$could = $newArray['INVOICE_ID'];
							}
						}
						else
						{
							$dont = "Invoice is already paid";
							$could = $newArray['INVOICE_ID'];
						}
					}
					else
					{
						$dont = "Invoice is for a zero amount";
						$could = $newArray['INVOICE_ID'];
					}
				}
				else
					$dont = "Client does not have an \"Invoice Email Address\"";
			}
			else
				$dont = "Client does not want invoices emailed";
		}
		else
			$dont = "Document is not an invoice";
		if (!$dont)
			$invoices_send[] = $newArray;
		else
		{
			$newArray['DONT'] = $dont;
			$invoices_dont[] = $newArray;
			if ($could)
				$invoices_could[] = $could; # INVOICE_ID
		}
	}
	#dprint("Invoices to Send: " . print_r($invoices_send,1) . "<br>
	#		Invoices Don't Send: " . print_r($invoices_dont,1) . "<br>
	#		" . "Clients: " . print_r($clients,1));#

	print "
	<form name=\"form_send\" action=\"" . server_php_self() . "\" method=\"post\">
		" . input_hidden('task', '') . "
		" . input_hidden('invoice_id', '') . "
		";
		foreach ($_POST as $name => $val)
		{
			if (substr($name, 0, 3) == "sc_")
				print input_hidden(xprint($name,false), xprint($val,false)) . "
					";
		}
		
	if ($invoices_send || $invoices_could)
	{
		$hidden_invoices = "";
		print "
		<h4>Invoices to Email Out &ndash; Please check then click \"Send\" to send</h4>
		<table name=\"table_send\" class=\"spaced_table\" border=\"0\"><!---->
		<tr>
			<th>Doc.No.</th>
			<th>System</th>
			<th>Date</th>
			<th>Client</th>
			<th>Client Name</th>
			<th>Net</th>
			<th>VAT</th>
			<th>Gross</th>
			<th>Recipient</th>
			<th>Email</th>
			" . (global_debug() ? "<th $grey>DB ID</th>" : '') . "
		</tr>
		";
		foreach ($invoices_send as $one)
		{
			$invoice_id = $one['INVOICE_ID'];
			#$inv_type = "Invoice";
			$net = floatval($one['INV_NET']);
			$vat = floatval($one['INV_VAT']);
			$gross = $net + $vat;
			$net = money_format_kdb($net, true, true, true);
			$vat = money_format_kdb($vat, true, true, true);
			$gross = money_format_kdb($gross, true, true, true);

			if ($one['INV_SYS'] == 'G')
				$system = 'General';
			elseif ($one['INV_SYS'] == 'T')
				$system = 'Trace';
			elseif ($one['INV_SYS'] == 'C')
				$system = 'Collect';
			else
				$system = '*Error*';
			#if ($one['INV_STMT'] == 1)
			#	$statement = 'Stmt';
			#else
			#	$statement = '';

			$date = date_for_sql($one['INV_DT'], true, false, true);
			#$due = date_for_sql($one['INV_DUE_DT'], true, false, true);
			$doc_num = intval($one['INV_NUM']);
			$c_code = intval($clients[$one['CLIENT2_ID']]['C_CODE']);
			$c_co_name = $clients[$one['CLIENT2_ID']]['C_CO_NAME'];
			$rec_name = $clients[$one['CLIENT2_ID']]['INV_EMAIL_NAME'];
			$rec_addr = $clients[$one['CLIENT2_ID']]['INV_EMAIL_ADDR'];

			print "
			<tr>
				<td $ar>$doc_num</td>
				<td>$system</td>
				<td $ar>$date</td>
				<td $ar>$c_code</td>
				<td>$c_co_name</td>
				<td $ar>$net</td>
				<td $ar>$vat</td>
				<td $ar>$gross</td>
				<td>$rec_name</td>
				<td>$rec_addr</td>
				" . (global_debug() ? "<td $grey>$invoice_id</td>" : '') . "
			</tr>
			";
			$hidden_invoices .= input_hidden("send_{$invoice_id}", 1) . "
			";
		} # foreach ($invoices_send)
		foreach ($invoices_could as $invoice_id)
		{
			$hidden_invoices .= input_hidden("send_{$invoice_id}", 0);
		} # foreach ($invoices_could)
		print "
			<tr>
				<td>" . input_button("Send", "send_invoices_2()") . "</td>
				<td $col8>&nbsp;</td>
				<td $ar>" . input_button("Refresh List", "refresh_email_list('$invoice_list')") . "
							" . input_button("Back", "email_back('$invoice_list')") . "</td>
			</tr>
		</table><!--table_send-->
		$hidden_invoices
		";
	}
	else
		print "<h4 style=\"color:red\">There are no invoices that can be emailed out</h4>
				" . input_button("Refresh List", "refresh_email_list('$invoice_list')") . "
				" . input_button("Back", "email_back('$invoice_list')") . "
				";
	print "
		</form><!--form_send-->
		";
	
	if ($invoices_dont)
	{
		$user_debug = user_debug();
		print "
		<h4>Invoices that can't be emailed out</h4>
		<table name=\"table_dont\" class=\"spaced_table\" border=\"0\"><!---->
		<tr>
			<th>Doc.Type</th>
			<th>Doc.No.</th>
			<th>System</th>
			<th>Date</th>
			<th>Client</th>
			<th>Client Name</th>
			<th>Net</th>
			<th>VAT</th>
			<th>Gross</th>
			<th>Recipient</th>
			<th>Email</th>
			<th>Reason why can't be emailed</th>
			<th>Send anyway</th>
			" . ($user_debug ? "<th $grey>DB ID</th>" : '') . "
		</tr>
		";
		foreach ($invoices_dont as $one)
		{
			$invoice_id = $one['INVOICE_ID'];
			if ($one['INV_TYPE'] == 'I')
				$inv_type = "Invoice";
			elseif ($one['INV_TYPE'] == 'C')
				$inv_type = "Credit";
			elseif ($one['INV_TYPE'] == 'F')
				$inv_type = "FOC";
			else
				$inv_type = "Unknown";
			$net = floatval($one['INV_NET']);
			$vat = floatval($one['INV_VAT']);
			$gross = $net + $vat;
			$net = money_format_kdb($net, true, true, true);
			$vat = money_format_kdb($vat, true, true, true);
			$gross = money_format_kdb($gross, true, true, true);

			if ($one['INV_SYS'] == 'G')
				$system = 'General';
			elseif ($one['INV_SYS'] == 'T')
				$system = 'Trace';
			elseif ($one['INV_SYS'] == 'C')
				$system = 'Collect';
			else
				$system = '*Error*';
			#if ($one['INV_STMT'] == 1)
			#	$statement = 'Stmt';
			#else
			#	$statement = '';

			$date = date_for_sql($one['INV_DT'], true, false, true);
			#$due = date_for_sql($one['INV_DUE_DT'], true, false, true);
			$doc_num = intval($one['INV_NUM']);
			$c_code = intval($clients[$one['CLIENT2_ID']]['C_CODE']);
			$c_co_name = $clients[$one['CLIENT2_ID']]['C_CO_NAME'];
			$rec_name = $clients[$one['CLIENT2_ID']]['INV_EMAIL_NAME'];
			$rec_addr = $clients[$one['CLIENT2_ID']]['INV_EMAIL_ADDR'];

			print "
			<tr>
				<td $ar>$inv_type</td>
				<td $ar>$doc_num</td>
				<td>$system</td>
				<td $ar>$date</td>
				<td $ar>$c_code</td>
				<td>$c_co_name</td>
				<td $ar>$net</td>
				<td $ar>$vat</td>
				<td $ar>$gross</td>
				<td>$rec_name</td>
				<td>$rec_addr</td>
				<td>{$one['DONT']}</td>
				<td $ac>
					";
					if (in_array($invoice_id, $invoices_could))
						print input_tickbox('', "could_{$invoice_id}", 1, false, "could_tick(this,$invoice_id)");
					print "
					</td>
				" . ($user_debug ? "<td $grey>$invoice_id</td>" : '') . "
			</tr>
			";
		} # foreach (dont)
		print "
		</table><!--table_dont-->
		";
	}	
	
} # send_invoices_1()

function send_invoices_2()
{
	global $crlf;
	global $csv_dir;
	global $email_accounts;
	global $emailName_accounts;
	global $sqlNow;

	$invoice_list = array();
	foreach ($_POST as $name => $val)
	{
		if ((substr($name, 0, 5) == "send_") && ($val == 1))
		{
			$iid = substr($name, 5);
			$iid = intval($iid);
			if (0 < $iid)
				$invoice_list[] = $iid;
		}
	}
	$invoice_list = implode(',', $invoice_list);
	#dprint("invoice_list=$invoice_list");#
	
	if (!$invoice_list)
	{
		dprint("send_invoices_2(): no invoices found!!", true, 'red');
		return;
	}
	
	$clients = array();
	sql_encryption_preparation('CLIENT2');
	$sql = "SELECT DISTINCT C.CLIENT2_ID, C.C_CODE, " . sql_decrypt('C.C_CO_NAME', '', true) . ", 
				" . sql_decrypt('C.INV_EMAIL_NAME', '', true) . ", " . sql_decrypt('C.INV_EMAIL_ADDR', '', true) . "
			FROM INVOICE AS I INNER JOIN CLIENT2 AS C ON C.CLIENT2_ID=I.CLIENT2_ID
			WHERE I.INVOICE_ID IN ($invoice_list)";
	sql_execute($sql);
	while (($newArray = sql_fetch_assoc()) != false)
	{
		if (!$newArray['INV_EMAIL_NAME'])
			$newArray['INV_EMAIL_NAME'] = "Sir/Madam";
		if (!array_key_exists($newArray['CLIENT2_ID'], $clients))
			$clients[$newArray['CLIENT2_ID']] = $newArray;
	}

	$invoices_send = array();
	$sql = "SELECT I.INVOICE_ID, I.CLIENT2_ID, I.INV_NUM, I.INV_SYS, I.INV_TYPE, I.INV_DT, I.INV_NET, I.INV_VAT, I.INV_DUE_DT, I.INV_STMT,
				I.INV_APPROVED_DT, I.INV_PAID, I.INV_EMAIL_ID, I.INV_POSTED_DT, I.IMPORTED, I.OBSOLETE
			FROM INVOICE AS I
			WHERE I.INVOICE_ID IN ($invoice_list)
			ORDER BY INV_NUM DESC";
	#dprint($sql);#
	sql_execute($sql);
	while (($newArray = sql_fetch_assoc()) != false)
	{
		$c_code = $clients[$newArray['CLIENT2_ID']]['C_CODE'];
		$pdf_link = pdf_link('i', "c{$c_code}", $newArray['INV_NUM']);
		# E.g. "http://localhost:8080/vilcol/web/csvex/c4142/invoice_204146_20161123_163437.pdf"
		$bits = explode('/', $pdf_link);
		$fname = $bits[count($bits)-1];
		if (!$fname)
			$fname = "Invoice_Not_Found.pdf";
		check_dir("{$csv_dir}/c{$c_code}"); # e.g. "csvex/c4142"
		$newArray['PDF_LINK'] = "c{$c_code}/{$fname}"; # e.g. "c4142/invoice_204146_20161123_163437.pdf"
		$invoices_send[] = $newArray;
	}
	#dprint("Invoices to Send: " . print_r($invoices_send,1) . "<br>
	#		" . "Clients: " . print_r($clients,1));#

	print "
	<form name=\"form_send\" action=\"" . server_php_self() . "\" method=\"post\">
		" . input_hidden('task', '') . "
		" . input_hidden('invoice_id', '') . "
		";
		foreach ($_POST as $name => $val)
		{
			if (substr($name, 0, 3) == "sc_")
				print input_hidden(xprint($name,false), xprint($val,false)) . "
					";
		}
	print "
	</form>
	";
	
	if ($invoices_send)
	{
		$sent = array();
		sql_encryption_preparation('EMAIL');
		foreach ($invoices_send as $one)
		{
			$client2_id = $one['CLIENT2_ID'];
			$mailto = $clients[$client2_id]['INV_EMAIL_ADDR'];
			if (email_valid($mailto))
			{
				$subject = "Vilcol Invoice No. {$one['INV_NUM']}";
				$message = "Dear {$clients[$client2_id]['INV_EMAIL_NAME']}," . "<br>$crlf" .
					"Please find attached our invoice, number {$one['INV_NUM']}." . "<br>$crlf" .
					"Yours," . "<br>$crlf" .
					"Vilcol Ltd.";
				$attachment_file = "{$csv_dir}/{$one['PDF_LINK']}"; # e.g. "csvex/c4142/invoice_204146_20161123_163437.pdf"
				$attachment_name = "Vilcol Invoice No. {$one['INV_NUM']}.pdf";
				$sending = array('INV_NUM' => $one['INV_NUM'], 'C_CODE' => $c_code, 'EMAIL_NAME' => $clients[$client2_id]['INV_EMAIL_NAME'], 
									'EMAIL_ADDR' => $mailto, 'EM_DT' => '', 'ERROR' => '');
				#$mailto = 'kevinbeckett1@gmail.com'; #
				if (mail_pm($mailto, $subject, $message, $email_accounts, $emailName_accounts, $attachment_file, $attachment_name,
								'', '', '', false))
				{
					$mailto = sql_encrypt($mailto, false, 'EMAIL');
					$subject = sql_encrypt($subject, false, 'EMAIL');
					$message = sql_encrypt(addslashes_kdb($message), false, 'EMAIL');
					$sql = "INSERT INTO EMAIL ( CLIENT2_ID,  EM_DT,   EM_TO,   EM_SUBJECT, EM_MESSAGE, EM_ATTACH,            INVOICE_ID)
										VALUES ($client2_id, $sqlNow, $mailto, $subject,   $message,   '{$one['PDF_LINK']}', {$one['INVOICE_ID']})";
					dprint($sql);
					audit_setup_client($client2_id, 'EMAIL', 'EMAIL_ID', 0, '', '');
					$new_email_id = sql_execute($sql, true); # audited
					$sql = "UPDATE INVOICE SET INV_EMAIL_ID=$new_email_id WHERE INVOICE_ID={$one['INVOICE_ID']}";
					dprint($sql);
					audit_setup_gen('INVOICE', 'INVOICE_ID', $one['INVOICE_ID'], 'INV_EMAIL_ID', $new_email_id);
					sql_execute($sql, true); # audited
					$sql = "SELECT E.EM_DT FROM EMAIL AS E INNER JOIN INVOICE AS I ON I.INV_EMAIL_ID=E.EMAIL_ID
								WHERE I.INVOICE_ID={$one['INVOICE_ID']}";
					sql_execute($sql);
					while (($newArray = sql_fetch()) != false)
						$sending['EM_DT'] = date_for_sql($newArray[0], true, true, true);
				}
				else
					$sending['ERROR'] = "Failed to email PDF to client";
			}
			else
				$sending['ERROR'] = "No (or bad) email address for client (ID $client2_id): \"$mailto\"";
			$sent[] = $sending;
		}
		print "
		<h4>The following invoices have been emailed out:</h4>
		<table>
		<tr>
			<th>Invoice No.</th>
			<th>Client</th>
			<th>Recipient</th>
			<th>Email Address</th>
			<th>Sent</th>
		</tr>
		";
		foreach ($sent as $one)
		{
			print "
			<tr>
				<td>{$one['INV_NUM']}</td>
				<td>{$one['C_CODE']}</td>
				<td>{$one['EMAIL_NAME']}</td>
				<td>{$one['EMAIL_ADDR']}</td>
				<td>" . ($one['ERROR'] ? $one['ERROR'] : $one['EM_DT']) . "</td>
			</tr>
			";
		}
		print "
		</table>
		";
	}
	else
		print "<p>***Nothing to send***</p>";

	print input_button("Back", "email_back('')");
	
} # send_invoices_2()

?>


