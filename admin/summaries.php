<?php

include_once("settings.php");
include_once("library.php");
include_once("lib_pdf.php");
include_once("lib_mail.php");

global $denial_message;
global $navi_1_finance;
global $navi_2_fin_summaries;
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
		$navi_2_fin_summaries = true; # settings.php; used by navi_2_heading()
		$onload = "onload=\"set_scroll();\"";
		$page_title_2 = 'Summaries - Vilcol';
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
	global $export;
	global $page_title_2;
	#global $salesperson_table; # if true then get salespersons from database table SALESPERSON
	global $sc_clients;
	global $sc_clients_ok;
	global $sc_date_cut;
	global $sc_date_fr;
	global $sc_date_to;
	global $sc_salesperson;
	global $sc_sort;
	global $task;

	print "<h3>Finance</h3>";
	navi_2_heading(); # secondary navigation buttons
	print "<h3>View Summaries</h3>";

	dprint(post_values());

	#$salesperson_table = false;
	#$salesperson_table = true;#

	if (count($_POST) == 0)
	{
		$task = '';
		$sc_date_fr = date_from_epoch(true, date_last_month(0, true, false, 1), false);
		$sc_date_to = date_from_epoch(true, date_last_month(0, false, true, 1), false);
		$client2_id = '';
		$sc_salesperson = 0;
		$sc_sort = 1; # Client Code
		$sc_date_cut = date_from_epoch(true, date_last_month(0, false, true, 2), false);
		$sc_clients = '';
		check_client_range();
	}
	else
	{
		$task = post_val('task');
		#if (($task == '') || ($task == 'amounts_billed') || ($task == 'bills_by_customer') || ($task == 'bills_by_salesperson'))
		#{
			$sc_date_fr = post_val('sc_date_fr', false, true, false, 1);
			$sc_date_to = post_val('sc_date_to', false, true, false, 1);
		#}
		#else
		#{
		#	$sc_date_fr = '';
		#	$sc_date_to = '';
		#}
		if (($task == '') || ($task == 'bills_by_customer'))
			$client2_id = post_val('client2_id', true);
		else
			$client2_id = '';
		if (($task == '') || ($task == 'bills_by_salesperson'))
		{
			$sc_salesperson = post_val('sc_salesperson', true);
			$sc_sort = post_val('sc_sort', true);
		}
		else
		{
			$sc_salesperson = 0;
			$sc_sort = 1; # Client Code
		}

		$sc_date_cut = post_val('sc_date_cut', false, true, false, 1);
		if (($task == '') || ($task == 'view_statements'))
		{
			$sc_clients = post_val('sc_clients');
			check_client_range();
		}
		else
		{
			#$sc_date_cut = '';
			$sc_clients = '';
		}

	}
	$export = post_val('export');

	javascript();

	print_form();

	print "
	<div id=\"div_results\">
	";
	if ($task == 'amounts_billed')
		amounts_billed();
	elseif ($task == 'bills_by_customer')
		bills_by_customer();
	elseif ($task == 'bills_by_salesperson')
		bills_by_salesperson();
	elseif ($task == 'view_aged_debtors')
		view_aged_debtors();
	elseif (($task == 'view_statements') && $sc_clients_ok)
		view_statements();
	print "
	</div><!--div_results-->
	";

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

function javascript()
{
//	global $safe_amp;
//	global $safe_slash;
	global $task;
//	global $uni_pound;

	print "
	<script type=\"text/javascript\">

	var ajax_clients = '';
	var first_client2_id = 0;

	function export_xl()
	{
		document.form_main.export.value = 'xl';
		document.form_main.task.value = '" . $task . "';
		please_wait_on_submit();
		document.form_main.submit();
	}

	function export_email()
	{
		document.form_main.export.value = 'email';
		document.form_main.task.value = '" . $task . "';
		please_wait_on_submit();
		document.form_main.submit();
	}

	function drop_xl_button()
	{
		var but_xl = document.getElementById('but_export_xl');
		if (but_xl)
			but_xl.style.visibility = 'hidden';
		var but_email = document.getElementById('but_export_email');
		if (but_email)
			but_email.style.visibility = 'hidden';
		search_prepare(0);
	}

	function goto_client(c2id,ccode)
	{
		document.form_client.client2_id.value = c2id;
		document.form_client.sc_text.value = ccode;
		document.form_client.task.value = 'view';
		document.form_client.submit();
	}

	function goto_invoice(iid)
	{
		document.form_invoices.invoice_id.value = iid;
		document.form_invoices.task.value = 'view_by_invoice_id';
		document.form_invoices.submit();
	}

	function show_invoices(cid)
	{
		document.form_invoices.client2_id.value = cid;
		document.form_invoices.task.value = 'search_client2_id';
		document.form_invoices.submit();
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
		el = document.getElementById('div_hr');
		if (el)
			el.style.display = 'block';
		el = document.getElementById('div_results');
		if (el)
			el.style.display = 'block';
		el = document.getElementById('tr_bill_sales');
		if (el)
			el.style.visibility = 'visible';
//		el = document.getElementById('div_post');
//		if (el)
//			el.style.display = 'none';
//		el = document.getElementById('div_invoices');
//		if (el)
//			el.style.display = 'none';

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
				var elhr = document.getElementById('div_hr');
				var elres = document.getElementById('div_results');
				var elbs = document.getElementById('tr_bill_sales');
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
				if (htmltxt != '')
				{
					if (elhr)
						elhr.style.display = 'none';
					if (elres)
						elres.style.display = 'none';
					if (elbs)
						elbs.style.visibility = 'hidden';
					htmltxt = '<div style=\"display:block; border:solid gray 1px;\">' + htmltxt + '</div>';
				}
				else
				{
					if (elhr)
						elhr.style.display = 'block';
					if (elres)
						elres.style.display = 'block';
					if (elbs)
						elbs.style.visibility = 'visible';
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
				document.form_main.client2_id.value = cid;
				";
				# format of displayed client name: "<code> - <name>"
				print "
				document.form_main.client_txt.value = cfields[1] + ' - ' + cfields[2];
				var el = document.getElementById('client_list');
				el.innerHTML = '';
				el = document.getElementById('div_hr');
				if (el)
					el.style.display = 'block';
				el = document.getElementById('div_results');
				if (el)
					el.style.display = 'block';
				el = document.getElementById('tr_bill_sales');
				if (el)
					el.style.visibility = 'visible';
				first_client2_id = 0;
				please_wait_on_submit();
				document.form_main.submit();
			}
		}
	}

	function set_month()
	{
		var today = new Date();
		var dd = 0;
		var mm = 0;
		var yyyy = 0;

		//var today = new Date(2016, 6, 11); // January is 0!
		//var dd = today.getDate();
		//var mm = today.getMonth() + 1; // January is 0!
		//var yyyy = today.getFullYear();
		//alert('Today/1: ' + today + ' = ' + dd + '/' + mm + '/' + yyyy);

		var d_fr = new Date(today.getFullYear(), today.getMonth() - 1, 1);
		dd = d_fr.getDate();
		mm = d_fr.getMonth() + 1; // January is 0!
		yyyy = d_fr.getFullYear();
		//alert('From: ' + dd + '/' + mm + '/' + yyyy);
		document.form_main.sc_date_fr.value = dd + '/' + mm + '/' + yyyy;

		var d_to = new Date(today.getFullYear(), today.getMonth(), 0);
		dd = d_to.getDate();
		mm = d_to.getMonth() + 1; // January is 0!
		yyyy = d_to.getFullYear();
		//alert('To: ' + dd + '/' + mm + '/' + yyyy);
		document.form_main.sc_date_to.value = dd + '/' + mm + '/' + yyyy;
	}

	function set_year()
	{
		var today = new Date();
		var dd = 0;
		var mm = 0;
		var yyyy = 0;

		var d_fr = new Date(today.getFullYear() - 1, today.getMonth(), 1);
		dd = d_fr.getDate();
		mm = d_fr.getMonth() + 1; // January is 0!
		yyyy = d_fr.getFullYear();
		document.form_main.sc_date_fr.value = dd + '/' + mm + '/' + yyyy;

		var d_to = new Date(today.getFullYear(), today.getMonth(), 0);
		dd = d_to.getDate();
		mm = d_to.getMonth() + 1; // January is 0!
		yyyy = d_to.getFullYear();
		document.form_main.sc_date_to.value = dd + '/' + mm + '/' + yyyy;
	}

	function amounts_billed()
	{
		document.form_main.export.value = '';
		document.form_main.task.value = 'amounts_billed';
		please_wait_on_submit();
		document.form_main.submit();
	}

	function bills_by_customer()
	{
		document.form_main.export.value = '';
		document.form_main.task.value = 'bills_by_customer';
		please_wait_on_submit();
		document.form_main.submit();
	}

	function bills_by_salesperson()
	{
		document.form_main.export.value = '';
		document.form_main.task.value = 'bills_by_salesperson';
		please_wait_on_submit();
		document.form_main.submit();
	}

	function view_statements()
	{
		document.form_main.export.value = '';
		document.form_main.task.value = 'view_statements';
		please_wait_on_submit();
		document.form_main.submit();
	}

	function view_aged_debtors()
	{
		document.form_main.export.value = '';
		document.form_main.task.value = 'view_aged_debtors';
		please_wait_on_submit();
		document.form_main.submit();
	}

	</script>
	";
}

function print_form()
{
	global $ac;
	global $ar;
	global $at;
	global $client2_id;
	global $col2;
	global $col3;
	global $col4;
	#global $salesperson_table; # if true then get salespersons from database table SALESPERSON
	global $sc_clients;
	global $sc_date_cut;
	global $sc_date_fr;
	global $sc_date_to;
	global $sc_salesperson;
	global $sc_sort;
	global $style_r;
	global $tr_colour_1;

	$client_name = '';
	if (0 < $client2_id)
	{
		sql_encryption_preparation('CLIENT2');
		$sql = "SELECT C_CODE, " . sql_decrypt('C_CO_NAME', '', true) . " FROM CLIENT2 WHERE CLIENT2_ID=$client2_id";
		sql_execute($sql);
		while (($newArray = sql_fetch_assoc()) != false)
			$client_name = "{$newArray['C_CODE']} - {$newArray['C_CO_NAME']}"; # format: "<code> - <name>"
	}

	#if ($salesperson_table)
	#	$salespersons = sql_get_salespersons_from_salespersons_table();
	#else
		$salespersons = #array(0 => 'Salesperson') +
							sql_get_salespersons(true);
	#dprint("Salespersons: " . print_r($salespersons,1));#

	$div_h = 120;
	$szdate = 8;
	$calendar_names = array();

	print "
	<div id=\"div_form_main\" style=\"background-color:{$tr_colour_1};\">
	<hr>
	<form name=\"form_main\" action=\"" . server_php_self() . "\" method=\"post\" onsubmit=\"return false;\">
	" . input_hidden('task', '') . "
	" . input_hidden('export', '') . "
	";
	# The following line causes form submission when ENTER key is pressed.
	print "
	<input type=\"submit\" style=\"display:none\">
	";
	print "
	<table name=\"table_outer\" class=\"basic_table\" border=\"0\"><!---->
	<tr>
		<td $at>
			<div id=\"div_inner_left\" style=\"height:{$div_h}px; border:solid gray 1px;\">

			<table name=\"table_inner_left\" class=\"basic_table\" border=\"0\"><!---->
			<tr>
				<td $ar>From:</td>
				<td>" . input_textbox('sc_date_fr', $sc_date_fr, $szdate, 10, "$style_r tabindex=\"1\"") . calendar_icon('sc_date_fr') . "</td>
				<td width=\"10\"></td>
				<td $col3>" . input_button('Summary of Amounts Billed and Received', 'amounts_billed()') . "</td>
			</tr>
			<tr>
				<td $ar>To:</td>
				<td>" . input_textbox('sc_date_to', $sc_date_to, $szdate, 10, "$style_r tabindex=\"2\"") . calendar_icon('sc_date_to') . "</td>
				<td></td>
				<td $col3>&nbsp;</td>
			</tr>
			";
			$calendar_names[] = "sc_date_fr";
			$calendar_names[] = "sc_date_to";
			print "
			<tr>
				<td $col2 $ar $at>" . input_button('Month', 'set_month()') . "&nbsp;&nbsp;
								" . input_button('Year', 'set_year()') . "</td>
				<td></td>
				<td $at>" . input_button('Billing Records by Customer', 'bills_by_customer()') . "</td>
				<td $col2>" . input_textbox('client_txt', $client_name, 50, 100, "autocomplete=\"off\" onkeyup=\"find_clients(this, event);\"") . "
							<span id=\"client_list\"></span>
							" . input_hidden('client2_id', $client2_id) . "</td>
			</tr>
			<tr id=\"tr_bill_sales\">
				<td $col2></td>
				<td></td>
				<td>" . input_button('Billing Records by Salesperson', 'bills_by_salesperson()') . "</td>
				<td>" . input_select('sc_salesperson', $salespersons, $sc_salesperson, '', false, false) . "</td>
				<td>...sorted by " . input_select('sc_sort',
									array(1 => 'Client Code', 2 => 'Client Name', 3 => 'Since Date'), $sc_sort, '', true) . "</td>
			</tr>
			</table><!--table_inner_left-->

			</div><!--div_inner_left-->
		</td>
		<td width=\"20\">
		</td>
		<td $at>
			<div id=\"div_inner_middle\" style=\"height:{$div_h}px; border:solid gray 1px;\">
			<table name=\"table_inner_middle\" class=\"basic_table\" border=\"0\"><!---->
			<tr>
				<td width=\"10\"></td>
				<td>" . input_button('View Aged Debtors', 'view_aged_debtors()') . "</td>
				<td width=\"10\"></td>
			</tr>
			</table><!--table_inner_middle-->
			</div><!--div_inner_middle-->
		</td>
		<td width=\"20\">
		</td>
		<td $at>
			<div id=\"div_inner_right\" style=\"height:{$div_h}px; border:solid gray 1px;\">
			<table name=\"table_inner_right\" class=\"basic_table\" border=\"0\"><!---->
			<tr>
				<td $col4 $ac>" . input_button('View Statements', 'view_statements()') . "</td>
			</tr>
			<tr>
				<td>Cut-off</td>
				<td $col2>" . input_textbox('sc_date_cut', $sc_date_cut, $szdate, 10, $style_r) . calendar_icon('sc_date_cut') . "</td>
			</tr>
			";
			$calendar_names[] = "sc_date_cut";
			print "
			<tr rowspan=\"2\">
				<td $at>Client<br>Code(s)</td>
				<td $col3>" . input_textbox('sc_clients', $sc_clients ? $sc_clients : '', 15, 100, $style_r) . "
							<br>&nbsp;&nbsp;(e.g. 3 or 5-8 or 2,4,6)</td>
			</tr>
			</table><!--table_inner_right-->
			</div><!--div_inner_right-->
		</td>
	</tr>
	</table><!--table_outer-->
	<div id=\"div_hr\"><hr></div>
	</form><!--form_main-->
	</div><!--div_form_main-->

	<form name=\"form_csv_download\" action=\"csv_dl.php\" method=\"post\" target=\"_blank\" rel=\"noopener\">
		<input type=\"hidden\" name=\"short_fname\" value=\"\" />
		<input type=\"hidden\" name=\"full_fname\" value=\"\" />
	</form><!--form_csv_download-->

	<form name=\"form_invoices\" action=\"ledger.php\" method=\"post\" target=\"_blank\" rel=\"noopener\">
		" . input_hidden('client2_id', '') . "
		" . input_hidden('invoice_id', '') . "
		" . input_hidden('task', '') . "
	</form><!--form_invoices-->

	<form name=\"form_client\" action=\"clients.php\" method=\"post\" target=\"_blank\" rel=\"noopener\">
		" . input_hidden('client2_id', '') . "
		" . input_hidden('sc_text', '') . "
		" . input_hidden('task', '') . "
	</form><!--form_client-->
	";

	javascript_calendars($calendar_names);

} # print_form()

function amounts_billed()
{
	global $ac;
	global $ar;
	global $col2;
	global $col3;
	global $col7;
	global $csv_path;
	global $excel_currency_format;
	global $export;
	global $phpExcel_ext;
	global $sc_date_fr;
	global $sc_date_to;
	global $sqlFalse;
	global $reps_subdir;
	global $USER;

	# Trace figures:
	$t_inv_net = 0.0;
	$t_inv_vat = 0.0;
	$t_inv_grs = 0.0;
	$t_cre_net = 0.0;
	$t_cre_vat = 0.0;
	$t_cre_grs = 0.0;

	# Collect figures:
	$c_inv_net = 0.0;
	$c_inv_vat = 0.0;
	$c_inv_grs = 0.0;
	$c_cre_net = 0.0;
	$c_cre_vat = 0.0;
	$c_cre_grs = 0.0;

	# General figures:
	$g_inv_net = 0.0;
	$g_inv_vat = 0.0;
	$g_inv_grs = 0.0;
	$g_cre_net = 0.0;
	$g_cre_vat = 0.0;
	$g_cre_grs = 0.0;

	# Total figures:
	$x_inv_net = 0.0;
	$x_inv_vat = 0.0;
	$x_inv_grs = 0.0;
	$x_cre_net = 0.0;
	$x_cre_vat = 0.0;
	$x_cre_grs = 0.0;

	# Received figures:
	$x_rx_recp = 0.0;
	$x_rx_adj = 0.0;

	$sc_date_fr_sql = ($sc_date_fr ? date_for_sql($sc_date_fr) : '');
	$sc_date_to_p1_sql = date_for_sql($sc_date_to, false, true, false, false, true);
	$sc_date_txt = '';

	$where = array("I.OBSOLETE=$sqlFalse", "(I.INV_TYPE='I') OR (I.INV_TYPE='C')");
	if ($sc_date_fr)
	{
		$where[] = "$sc_date_fr_sql <= I.INV_DT";
		$sc_date_txt = "from $sc_date_fr";
	}
	if ($sc_date_to)
	{
		$where[] = "I.INV_DT < $sc_date_to_p1_sql";
		if ($sc_date_txt != '')
			$sc_date_txt .= ' ';
		$sc_date_txt .= "to $sc_date_to";
	}
	elseif ($sc_date_txt == '')
		$sc_date_txt = "all dates";
	if ($where)
		$where = "WHERE (" . implode(') AND (', $where) . ")";

	$sql = "SELECT I.INVOICE_ID, I.INV_SYS, I.INV_TYPE, COALESCE(I.INV_NET,0) AS INV_NET, COALESCE(I.INV_VAT,0) AS INV_VAT
			FROM INVOICE AS I
			$where
			";
	#dprint($sql);#
	sql_execute($sql);
	while (($newArray = sql_fetch_assoc()) != false)
	{
		$net = floatval($newArray['INV_NET']);
		$vat = floatval($newArray['INV_VAT']);
		$grs = $net + $vat;

		if ($newArray['INV_SYS'] == 'T')
		{
			if ($newArray['INV_TYPE'] == 'I')
			{
				$t_inv_net += $net;
				$t_inv_vat += $vat;
				$t_inv_grs += $grs;
				$x_inv_net += $net;
				$x_inv_vat += $vat;
				$x_inv_grs += $grs;
			}
			elseif ($newArray['INV_TYPE'] == 'C')
			{
				$t_cre_net += $net;
				$t_cre_vat += $vat;
				$t_cre_grs += $grs;
				$x_cre_net += $net;
				$x_cre_vat += $vat;
				$x_cre_grs += $grs;
			}
		}
		elseif ($newArray['INV_SYS'] == 'C')
		{
			if ($newArray['INV_TYPE'] == 'I')
			{
				$c_inv_net += $net;
				$c_inv_vat += $vat;
				$c_inv_grs += $grs;
				$x_inv_net += $net;
				$x_inv_vat += $vat;
				$x_inv_grs += $grs;
			}
			elseif ($newArray['INV_TYPE'] == 'C')
			{
				$c_cre_net += $net;
				$c_cre_vat += $vat;
				$c_cre_grs += $grs;
				$x_cre_net += $net;
				$x_cre_vat += $vat;
				$x_cre_grs += $grs;
			}
		}
		elseif ($newArray['INV_SYS'] == 'G')
		{
			if ($newArray['INV_TYPE'] == 'I')
			{
				$g_inv_net += $net;
				$g_inv_vat += $vat;
				$g_inv_grs += $grs;
				$x_inv_net += $net;
				$x_inv_vat += $vat;
				$x_inv_grs += $grs;
			}
			elseif ($newArray['INV_TYPE'] == 'C')
			{
				$g_cre_net += $net;
				$g_cre_vat += $vat;
				$g_cre_grs += $grs;
				$x_cre_net += $net;
				$x_cre_vat += $vat;
				$x_cre_grs += $grs;
			}
		}
	}

	$where = array("R.OBSOLETE=$sqlFalse");
	if ($sc_date_fr)
		$where[] = "$sc_date_fr_sql <= R.RC_DT";
	if ($sc_date_to)
		$where[] = "R.RC_DT < $sc_date_to_p1_sql";
	if ($where)
		$where = "WHERE (" . implode(') AND (', $where) . ")";

	$sql = "SELECT R.RC_AMOUNT, R.RC_ADJUST FROM INV_RECP AS R $where";
	#dprint($sql);#
	sql_execute($sql);
	while (($newArray = sql_fetch_assoc()) != false)
	{
		$amt = floatval($newArray['RC_AMOUNT']);
		if ($newArray['RC_ADJUST'] == 0)
			$x_rx_recp += $amt;
		else
			$x_rx_adj += $amt;
	}

	$export_xl = (($export == 'xl') ? true : false);
	if ($export_xl)
	{
		$title = '';
		$title_short = "Amounts Billed";
		$xfile = "SUM" . strftime_rdr("%Y%m%d_%H%M%S") . "_{$USER['USER_ID']}_AmountsBilled";
	}
	else
	{
		$title = '';
		$title_short = '';
		$xfile = '';
	}
	$headings = array();
	$datalines = array();

	print "
	<table name=\"table_results\" type=\"basic_table\">
	";

	$line = array("Date Range: $sc_date_txt");
	print "
	<tr>
		<td $col7>{$line[0]}</td>
		<td $col3>&nbsp;</td>
		<td width=\"20\">&nbsp;</td>
		<td>" . input_button('Export to Excel', "export_xl()", '', 'but_export_xl') . "</td>
	</tr>
	";
	if ($export_xl)
		$datalines[] = $line;

	print "
	<tr>
		<td $col7>&nbsp;</td>
	<tr>
	";
	if ($export_xl)
		$datalines[] = array('');

	$line = array("Summary of Amounts Billed", "Gross", "Net", "VAT", "", "Summary of Amounts Received");
	print "
	<tr>
		<td>{$line[0]}</td>
		<td width=\"10\"></td>
		<td $ac>{$line[1]}</td>
		<td width=\"10\"></td>
		<td $ac>{$line[2]}</td>
		<td width=\"10\"></td>
		<td $ac>{$line[3]}</td>
		<td width=\"50\"></td>
		<td>{$line[5]}</td>
	</tr>
	";
	if ($export_xl)
		$datalines[] = $line;

	print "
	<tr>
		<td $col7><hr></td>
		<td></td>
		<td $col2><hr></td>
	<tr>
	";
	if ($export_xl)
		$datalines[] = array('');

	$line_x = array("Total Invoices", $x_inv_grs, $x_inv_net, $x_inv_vat, "", "Receipts", $x_rx_recp);
	$line_s = array($line_x[0], money_format_kdb($line_x[1], true, true, true), money_format_kdb($line_x[2], true, true, true),
					money_format_kdb($line_x[3], true, true, true), $line_x[4], $line_x[5], money_format_kdb($line_x[6], true, true, true));
	print "
	<tr>
		<td>{$line_s[0]}</td>
		<td></td>
		<td $ar>{$line_s[1]}</td>
		<td></td>
		<td $ar>{$line_s[2]}</td>
		<td></td>
		<td $ar>{$line_s[3]}</td>
		<td></td>
		<td>{$line_s[5]}</td>
		<td $ar>{$line_s[6]}</td>
	</tr>
	";
	if ($export_xl)
		$datalines[] = $line_x;

	$line_x = array("Total Credits", $x_cre_grs, $x_cre_net, $x_cre_vat, "", "Adjustments", $x_rx_adj);
	$line_s = array($line_x[0], money_format_kdb($line_x[1], true, true, true), money_format_kdb($line_x[2], true, true, true),
					money_format_kdb($line_x[3], true, true, true), $line_x[4], $line_x[5], money_format_kdb($line_x[6], true, true, true));
	print "
	<tr>
		<td>{$line_s[0]}</td>
		<td></td>
		<td $ar>{$line_s[1]}</td>
		<td></td>
		<td $ar>{$line_s[2]}</td>
		<td></td>
		<td $ar>{$line_s[3]}</td>
		<td></td>
		<td>{$line_s[5]}</td>
		<td $ar>{$line_s[6]}</td>
	</tr>
	";
	if ($export_xl)
		$datalines[] = $line_x;

	$line_x = array("Net Total", $x_inv_grs + $x_cre_grs, $x_inv_net + $x_cre_net, $x_inv_vat + $x_cre_vat, "", "Total", $x_rx_recp + $x_rx_adj);
	$line_s = array($line_x[0], money_format_kdb($line_x[1], true, true, true), money_format_kdb($line_x[2], true, true, true),
					money_format_kdb($line_x[3], true, true, true), $line_x[4], $line_x[5], money_format_kdb($line_x[6], true, true, true));
	print "
	<tr>
		<td>{$line_s[0]}</td>
		<td></td>
		<td $ar>{$line_s[1]}</td>
		<td></td>
		<td $ar>{$line_s[2]}</td>
		<td></td>
		<td $ar>{$line_s[3]}</td>
		<td></td>
		<td>{$line_s[5]}</td>
		<td $ar>{$line_s[6]}</td>
	</tr>
	";
	if ($export_xl)
		$datalines[] = $line_x;

	print "
	<tr>
		<td $col7>&nbsp;</td>
	<tr>
	";
	if ($export_xl)
		$datalines[] = array('');

	$line_x = array("Trace Invoices", $t_inv_grs, $t_inv_net, $t_inv_vat);
	$line_s = array($line_x[0], money_format_kdb($line_x[1], true, true, true), money_format_kdb($line_x[2], true, true, true),
					money_format_kdb($line_x[3], true, true, true));
	print "
	<tr>
		<td>{$line_s[0]}</td>
		<td></td>
		<td $ar>{$line_s[1]}</td>
		<td></td>
		<td $ar>{$line_s[2]}</td>
		<td></td>
		<td $ar>{$line_s[3]}</td>
	</tr>
	";
	if ($export_xl)
		$datalines[] = $line_x;

	$line_x = array("Trace Credits", $t_cre_grs, $t_cre_net, $t_cre_vat);
	$line_s = array($line_x[0], money_format_kdb($line_x[1], true, true, true), money_format_kdb($line_x[2], true, true, true),
					money_format_kdb($line_x[3], true, true, true));
	print "
	<tr>
		<td>{$line_s[0]}</td>
		<td></td>
		<td $ar>{$line_s[1]}</td>
		<td></td>
		<td $ar>{$line_s[2]}</td>
		<td></td>
		<td $ar>{$line_s[3]}</td>
	</tr>
	";
	if ($export_xl)
		$datalines[] = $line_x;

	$line_x = array("Trace Net", $t_inv_grs + $t_cre_grs, $t_inv_net + $t_cre_net, $t_inv_vat + $t_cre_vat);
	$line_s = array($line_x[0], money_format_kdb($line_x[1], true, true, true), money_format_kdb($line_x[2], true, true, true),
					money_format_kdb($line_x[3], true, true, true));
	print "
	<tr>
		<td>{$line_s[0]}</td>
		<td></td>
		<td $ar>{$line_s[1]}</td>
		<td></td>
		<td $ar>{$line_s[2]}</td>
		<td></td>
		<td $ar>{$line_s[3]}</td>
	</tr>
	";
	if ($export_xl)
		$datalines[] = $line_x;

	print "
	<tr>
		<td $col7>&nbsp;</td>
	<tr>
	";
	if ($export_xl)
		$datalines[] = array('');

	$line_x = array("Collect Invoices", $c_inv_grs, $c_inv_net, $c_inv_vat);
	$line_s = array($line_x[0], money_format_kdb($line_x[1], true, true, true), money_format_kdb($line_x[2], true, true, true),
					money_format_kdb($line_x[3], true, true, true));
	print "
	<tr>
		<td>{$line_s[0]}</td>
		<td></td>
		<td $ar>{$line_s[1]}</td>
		<td></td>
		<td $ar>{$line_s[2]}</td>
		<td></td>
		<td $ar>{$line_s[3]}</td>
	</tr>
	";
	if ($export_xl)
		$datalines[] = $line_x;

	$line_x = array("Collect Credits", $c_cre_grs, $c_cre_net, $c_cre_vat);
	$line_s = array($line_x[0], money_format_kdb($line_x[1], true, true, true), money_format_kdb($line_x[2], true, true, true),
					money_format_kdb($line_x[3], true, true, true));
	print "
	<tr>
		<td>{$line_s[0]}</td>
		<td></td>
		<td $ar>{$line_s[1]}</td>
		<td></td>
		<td $ar>{$line_s[2]}</td>
		<td></td>
		<td $ar>{$line_s[3]}</td>
	</tr>
	";
	if ($export_xl)
		$datalines[] = $line_x;

	$line_x = array("Collect Net", $c_inv_grs + $c_cre_grs, $c_inv_net + $c_cre_net, $c_inv_vat + $c_cre_vat);
	$line_s = array($line_x[0], money_format_kdb($line_x[1], true, true, true), money_format_kdb($line_x[2], true, true, true),
					money_format_kdb($line_x[3], true, true, true));
	print "
	<tr>
		<td>{$line_s[0]}</td>
		<td></td>
		<td $ar>{$line_s[1]}</td>
		<td></td>
		<td $ar>{$line_s[2]}</td>
		<td></td>
		<td $ar>{$line_s[3]}</td>
	</tr>
	";
	if ($export_xl)
		$datalines[] = $line_x;

	print "
	<tr>
		<td $col7>&nbsp;</td>
	<tr>
	";
	if ($export_xl)
		$datalines[] = array('');

	$line_x = array("General Invoices", $g_inv_grs, $g_inv_net, $g_inv_vat);
	$line_s = array($line_x[0], money_format_kdb($line_x[1], true, true, true), money_format_kdb($line_x[2], true, true, true),
					money_format_kdb($line_x[3], true, true, true));
	print "
	<tr>
		<td>{$line_s[0]}</td>
		<td></td>
		<td $ar>{$line_s[1]}</td>
		<td></td>
		<td $ar>{$line_s[2]}</td>
		<td></td>
		<td $ar>{$line_s[3]}</td>
	</tr>
	";
	if ($export_xl)
		$datalines[] = $line_x;

	$line_x = array("General Credits", $g_cre_grs, $g_cre_net, $g_cre_vat);
	$line_s = array($line_x[0], money_format_kdb($line_x[1], true, true, true), money_format_kdb($line_x[2], true, true, true),
					money_format_kdb($line_x[3], true, true, true));
	print "
	<tr>
		<td>{$line_s[0]}</td>
		<td></td>
		<td $ar>{$line_s[1]}</td>
		<td></td>
		<td $ar>{$line_s[2]}</td>
		<td></td>
		<td $ar>{$line_s[3]}</td>
	</tr>
	";
	if ($export_xl)
		$datalines[] = $line_x;

	$line_x = array("General Net", $g_inv_grs + $g_cre_grs, $g_inv_net + $g_cre_net, $g_inv_vat + $g_cre_vat);
	$line_s = array($line_x[0], money_format_kdb($line_x[1], true, true, true), money_format_kdb($line_x[2], true, true, true),
					money_format_kdb($line_x[3], true, true, true));
	print "
	<tr>
		<td>{$line_s[0]}</td>
		<td></td>
		<td $ar>{$line_s[1]}</td>
		<td></td>
		<td $ar>{$line_s[2]}</td>
		<td></td>
		<td $ar>{$line_s[3]}</td>
	</tr>
	";
	if ($export_xl)
		$datalines[] = $line_x;

	print "
	</table><!--table_results-->
	";

	if ($export_xl)
	{
		$top_lines = array();
		$formats = array('B' => $excel_currency_format, 'C' => $excel_currency_format, 'D' => $excel_currency_format, 'G' => $excel_currency_format);
		$sheet = array($title_short, $top_lines, $headings, $datalines, $formats, '');
		phpExcel_output($reps_subdir, $xfile, array($sheet)); # library.php

		# Auto-download file to PC
		print "
		<script type=\"text/javascript\">
			document.form_csv_download.short_fname.value = '$xfile.$phpExcel_ext';
			document.form_csv_download.full_fname.value = '$csv_path' + '{$reps_subdir}/$xfile.$phpExcel_ext';
			document.form_csv_download.submit();
		</script>
		";
	}

} # amounts_billed()

function bills_by_customer()
{
	global $ar;
	global $at;
	global $client2_id;
	global $col6;
	global $col11;
	global $col12;
	global $crlf;
	global $csv_path;
	global $excel_currency_format;
	global $export;
	global $phpExcel_ext;
	global $sc_date_fr;
	global $sc_date_to;
	global $sqlFalse;
	global $sqlTrue;
	global $reps_subdir;
	global $USER;

	if (!(0 < $client2_id))
	{
		dprint("Please type/select a client before clicking the button", true);
		return;
	}

	#dprint("Bills by Customer: work in progress", true);

	$c_individual = false;
	$client_code = '';
	$client_name = '';
	$client_contact = '';
	$client_addr = array();
	if (0 < $client2_id)
	{
		sql_encryption_preparation('CLIENT2');
		$sql = "SELECT C_INDIVIDUAL, C_CODE, " . sql_decrypt('C_CO_NAME', '', true) . ",
				" . sql_decrypt('C_ADDR_1', '', true) . ", " . sql_decrypt('C_ADDR_2', '', true) . ", " . sql_decrypt('C_ADDR_3', '', true) . ",
				" . sql_decrypt('C_ADDR_4', '', true) . ", " . sql_decrypt('C_ADDR_5', '', true) . ", " . sql_decrypt('C_ADDR_PC', '', true) . "
				FROM CLIENT2 WHERE CLIENT2_ID=$client2_id";
		sql_execute($sql);
		while (($newArray = sql_fetch_assoc()) != false)
		{
			$c_individual = (($newArray['C_INDIVIDUAL'] == 1) ? true : false);
			$client_code = $newArray['C_CODE'];
			$client_name = $newArray['C_CO_NAME'];
			if ($newArray['C_ADDR_1'])
				$client_addr[] = $newArray['C_ADDR_1'];
			if ($newArray['C_ADDR_2'])
				$client_addr[] = $newArray['C_ADDR_2'];
			if ($newArray['C_ADDR_3'])
				$client_addr[] = $newArray['C_ADDR_3'];
			if ($newArray['C_ADDR_4'])
				$client_addr[] = $newArray['C_ADDR_4'];
			if ($newArray['C_ADDR_5'])
				$client_addr[] = $newArray['C_ADDR_5'];
			if ($newArray['C_ADDR_PC'])
				$client_addr[] = $newArray['C_ADDR_PC'];
		}
		if ($c_individual)
		{
			$client_contact = $client_name;
			$client_name = '';
		}
		else
		{
			sql_encryption_preparation('CLIENT_CONTACT');
			$sql = "SELECT " . sql_decrypt('CC_FIRSTNAME', '', true) . ", " . sql_decrypt('CC_LASTNAME', '', true) . "
					FROM CLIENT_CONTACT WHERE CLIENT2_ID=$client2_id AND CC_MAIN=$sqlTrue";
			sql_execute($sql);
			while (($newArray = sql_fetch_assoc()) != false)
				$client_contact = $newArray['CC_FIRSTNAME'] . ' ' . $newArray['CC_LASTNAME'];
		}
		$client_lines = array();
		if ($client_name)
			$client_lines[] = $client_name;
		$ii_max = count($client_addr);
		for ($ii = 0; $ii < $ii_max; $ii++)
		{
			if ($client_addr[$ii])
				$client_lines[] = $client_addr[$ii];
		}
	}

	$sc_date_fr_sql = ($sc_date_fr ? date_for_sql($sc_date_fr) : '');
	$sc_date_to_p1_sql = date_for_sql($sc_date_to, false, true, false, false, true);
	$sc_date_txt = '';
	$today_txt = date_now(true);

	$where = array("I.OBSOLETE=$sqlFalse", "(I.INV_TYPE='I') OR (I.INV_TYPE='C')", "I.CLIENT2_ID=$client2_id");
	if ($sc_date_fr)
	{
		$where[] = "$sc_date_fr_sql <= I.INV_DT";
		$sc_date_txt = "from $sc_date_fr";
	}
	if ($sc_date_to)
	{
		$where[] = "I.INV_DT < $sc_date_to_p1_sql";
		if ($sc_date_txt != '')
			$sc_date_txt .= ' ';
		$sc_date_txt .= "to $sc_date_to";
	}
	elseif ($sc_date_txt == '')
		$sc_date_txt = "all dates";
	if ($where)
		$where = "WHERE (" . implode(') AND (', $where) . ")";

	$sql = "SELECT I.INVOICE_ID, I.INV_NUM, I.INV_SYS, I.INV_TYPE, COALESCE(I.INV_NET,0) AS INV_NET, COALESCE(I.INV_VAT,0) AS INV_VAT
			FROM INVOICE AS I
			$where
			";
	#dprint($sql);#
	sql_execute($sql);
	$invoices = array();
	$total_cost = 0.0;
	while (($newArray = sql_fetch_assoc()) != false)
	{
		$invoices[$newArray['INVOICE_ID']] = $newArray;
		$total_cost += (floatval($newArray['INV_NET']) + floatval($newArray['INV_VAT']));
	}

	sql_encryption_preparation('JOB');
	sql_encryption_preparation('JOB_SUBJECT');
	foreach ($invoices as $invoice_id => $one)
	{
		if ($one['INV_SYS'] == 'T')
		{
			# Trace Invoice
			$sql = "SELECT J.JOB_ID, " . sql_decrypt('J.CLIENT_REF', '', true) . ", " . sql_decrypt('S.JS_FIRSTNAME', '', true) . ",
						" . sql_decrypt('S.JS_LASTNAME', '', true) . ", " . sql_decrypt('S.JS_COMPANY', '', true) . ",
						T.JT_TYPE
					FROM INV_BILLING AS B
					LEFT JOIN JOB AS J ON J.JOB_ID=B.JOB_ID
					LEFT JOIN JOB_SUBJECT AS S ON S.JOB_ID=J.JOB_ID AND S.JS_PRIMARY=$sqlTrue
					LEFT JOIN JOB_TYPE_SD AS T ON T.JOB_TYPE_ID=J.JT_JOB_TYPE_ID
					WHERE B.INVOICE_ID=$invoice_id";
			sql_execute($sql);
			$job_id = 0;
			$client_ref = '(job not found)';
			$subject_name = '';
			while (($newArray = sql_fetch_assoc()) != false)
			{
				if ($job_id == 0)
				{
					if (0 < $newArray['JOB_ID'])
					{
						$job_id = $newArray['JOB_ID'];
						$client_ref = $newArray['CLIENT_REF'] . '<br>' . $newArray['JT_TYPE'];
						$subject_name = $newArray['JS_COMPANY'];
						if (!$subject_name)
							$subject_name = $newArray['JS_FIRSTNAME'] . ' ' . $newArray['JS_LASTNAME'];
					}
				}
			}
			$invoices[$invoice_id]['CLIENT_REF'] = $client_ref;
			$invoices[$invoice_id]['SUBJECT_NAME'] = $subject_name;
		} # T

		elseif ($one['INV_SYS'] == 'C')
		{
			# Collect Invoice
			$sql = "SELECT COUNT(*) FROM JOB_PAYMENT WHERE INVOICE_ID=$invoice_id AND (OBSOLETE=$sqlFalse)";
			sql_execute($sql);
			$count = 0;
			while (($newArray = sql_fetch()) != false)
				$count = $newArray[0];
			$invoices[$invoice_id]['CLIENT_REF'] = 'Collection Invoice';
			$invoices[$invoice_id]['SUBJECT_NAME'] = "{$count} payment(s)";
		} # C

		elseif ($one['INV_SYS'] == 'G')
		{
			# General Invoice
			$sql = "SELECT BL_DESCR FROM INV_BILLING WHERE INVOICE_ID=$invoice_id";
			sql_execute($sql);
			$descr = '';
			while (($newArray = sql_fetch()) != false)
			{
				if ($newArray[0])
				{
					if ($descr)
						$descr .= ' ';
					$descr .= $newArray[0];
				}
			}
			$invoices[$invoice_id]['CLIENT_REF'] = 'General Invoice';
			$invoices[$invoice_id]['SUBJECT_NAME'] = $descr;
		} # G
	} # foreach ($invoices)

	$total_cost_txt_x = $total_cost;
	$total_cost_txt = money_format_kdb($total_cost, true, true, true);

	$border_all = "style=\"border: solid black 1px;\"";
	$border_b = "style=\"border-bottom: solid black 1px;\"";
	$border_br = "style=\"border-bottom: solid black 1px; border-right: solid black 1px;\"";
	$border_br_dot = "style=\"border-bottom: dotted black 1px; border-right: solid black 1px;\"";
	$border_b_dot = "style=\"border-bottom: dotted black 1px;\"";

	$export_xl = (($export == 'xl') ? true : false);
	if ($export_xl)
	{
		$title = '';
		$title_short = "Billing by Customer";
		$xfile = "SUM" . strftime_rdr("%Y%m%d_%H%M%S") . "_{$USER['USER_ID']}_BillByCust";
	}
	else
	{
		$title = '';
		$title_short = '';
		$xfile = '';
	}
	$headings = array();
	$datalines = array();

	print "
	<div id=\"div_outer\" " .#style=\"width:800px;\"
		">
	<table name=\"table_outer\" " .#width=\"100%\"
		">
	<tr>
		<td $at>
			<div id=\"div_bills\" " . #style=\"border:solid black 1px;\"
				">
			<table name=\"table_bills\" class=\"basic_table\" cellspacing=\"0\" cellpadding=\"0\" $border_all>
			";

			$txt = "Billing Records by Customer";
			print "
			<tr>
				<td $border_b>&nbsp;</td>
				<td $border_b $col11>$txt</td>
			</tr>
			";
			if ($export_xl)
				$datalines[] = array($txt);

			print "
			<tr>
				<td>&nbsp;</td>
			</tr>
			";
			if ($export_xl)
				$datalines[] = array('');

			$txt = "Schedule $sc_date_txt";
			print "
			<tr>
				<td>&nbsp;</td>
				<td $col11>$txt</td>
			</tr>
			";
			if ($export_xl)
				$datalines[] = array($txt);

			$txt = "Created $today_txt";
			print "
			<tr>
				<td>&nbsp;</td>
				<td $col11></td>
			</tr>
			";
			if ($export_xl)
				$datalines[] = array($txt);

			print "
			<tr>
				<td>&nbsp;</td>
			</tr>
			";
			if ($export_xl)
				$datalines[] = array('');

			$txt = "Attn: $client_contact";
			print "
			<tr>
				<td>&nbsp;</td>
				<td $col11>$txt</td>
			</tr>
			";
			if ($export_xl)
				$datalines[] = array($txt);

			$ii_max = count($client_lines);
			for ($ii = 0; $ii < $ii_max; $ii++)
			{
				$txt = $client_lines[$ii];
				print "
				<tr>
					<td>&nbsp;</td>
					<td $col11>$txt</td>
				</tr>
				";
				if ($export_xl)
					$datalines[] = array($txt);
			}
			print "
			<tr>
				<td $col12 $border_b>&nbsp;</td>
			</tr>
			";
			if ($export_xl)
				$datalines[] = array('');

			$heads = array('Our Ref', 'Your Ref', 'Name', 'Cost');
			print "
			<tr>
				<td $border_b>&nbsp;</td>	<td $border_b>{$heads[0]}</td>	<td $border_br>&nbsp;</td>
				<td $border_b>&nbsp;</td>	<td $border_b>{$heads[1]}</td>	<td $border_br>&nbsp;</td>
				<td $border_b>&nbsp;</td>	<td $border_b>{$heads[2]}</td>	<td $border_br>&nbsp;</td>
				<td $border_b>&nbsp;</td>	<td $border_b>{$heads[3]}</td>	<td $border_b>&nbsp;</td>
			</tr>
			";
			if ($export_xl)
				$datalines[] = $heads;

			foreach ($invoices as $id => $one)
			{
				$our_ref = $one['INV_NUM'];
				if ($one['INV_TYPE'] == 'C')
					$our_ref = "(Credit) $our_ref";
				$our_ref_x = $our_ref;
				$our_ref = "<span style=\"cursor:pointer;\" onclick=\"goto_invoice($id)\">$our_ref</span>";

				$your_ref = $one['CLIENT_REF'];
				$your_ref_x = $your_ref;

				$name = $one['SUBJECT_NAME'];
				$name_x = printable_chars($name);
				$name = pound_clean($name, 1);

				$cost = floatval($one['INV_NET']) + floatval($one['INV_VAT']);
				$cost_x = $cost;
				$cost = money_format_kdb($cost, true, true, true);

				print "
				<tr>
					<td $border_b>&nbsp;</td>	<td $border_b $ar>$our_ref</td>	<td $border_br_dot>&nbsp;</td>
					<td $border_b>&nbsp;</td>	<td $border_b>$your_ref</td>	<td $border_br_dot>&nbsp;</td>
					<td $border_b>&nbsp;</td>	<td $border_b>$name</td>		<td $border_br_dot>&nbsp;</td>
					<td $border_b>&nbsp;</td>	<td $border_b $ar>$cost</td>	<td $border_b_dot>&nbsp;</td>
				</tr>
				";
				if ($export_xl)
					$datalines[] = array($our_ref_x, $your_ref_x, $name_x, $cost_x);
			}

			print "
			<tr>
				<td $col6>&nbsp;</td>
				<td>&nbsp;</td>	<td>TOTAL COST</td>			<td>&nbsp;</td>
				<td>&nbsp;</td>	<td>$total_cost_txt</td>	<td>&nbsp;</td>
			</tr>
			";
			if ($export_xl)
				$datalines[] = array('', '', 'TOTAL COST', $total_cost_txt_x);

			print "
			</table><!--table_bills-->
			</div><!--div_bills-->
		</td>
		<td width=\"50\"></td>
		<td $at>
			" . input_button('Export to Excel', "export_xl()", '', 'but_export_xl') . "
			<br><br><br><br>
			" . input_button("View client (in new tab)", "goto_client('$client2_id','$client_code');", "style=\"width:180px;\"") . "
			<br>
			" . input_button('View all invoices &amp; receipts' . $crlf . 'for this client (in new tab)', "show_invoices($client2_id)", "style=\"width:180px;\"") . "
			<br><br><br><br><br>
			Note: clicking on the \"Our Ref\" cell<br>for a given invoice will open up<br>that invoice in a new tab.
		</tr>
	</tr>
	</table><!--table_outer-->
	</div><!--div_outer-->
	";

	if ($export_xl)
	{
		$top_lines = array();
		$formats = array('D' => $excel_currency_format);
		$sheet = array($title_short, $top_lines, $headings, $datalines, $formats, '');
		phpExcel_output($reps_subdir, $xfile, array($sheet)); # library.php

		# Auto-download file to PC
		print "
		<script type=\"text/javascript\">
			document.form_csv_download.short_fname.value = '$xfile.$phpExcel_ext';
			document.form_csv_download.full_fname.value = '$csv_path' + '{$reps_subdir}/$xfile.$phpExcel_ext';
			document.form_csv_download.submit();
		</script>
		";
	}
} # bills_by_customer()

function bills_by_salesperson()
{
	global $ac;
	global $ar;
	global $col3;
	global $csv_path;
	global $excel_currency_format;
	global $excel_date_format;
	global $excel_integer_format_no_comma;
	global $export;
	global $phpExcel_ext;
	global $sc_date_fr;
	global $sc_date_to;
	global $sc_salesperson;
	global $sc_sort;
	global $reps_subdir;
	global $USER;

	# Do debug for user 11 (Denise) and client 8027 (8100 Rizwan Ali)
	$debug_sp = 11;
	#$debug_cid = 8027; # 8100 Rizwan Ali
	#$debug_cid = 8051; # 8124 Fraser Hart
	$debug_cid = 8178; # 8250 Parvinder Singh
	$debug_sp_cid = false; #(($sc_salesperson == $debug_sp) ? true : false);#
	if ($debug_sp_cid)
		dprint("bills_by_salesperson(): Debugging $debug_sp and $debug_cid");
	$debug = false;


	if (!(0 < $sc_salesperson))
	{
		dprint("Please select a salesperson before clicking the button", true);
		return;
	}

	set_time_limit(60 * 10); # 10 minutes

	$sc_date_fr_sql = ($sc_date_fr ? date_for_sql($sc_date_fr) : '1980-01-01');
	$sc_date_to_sql = ($sc_date_to ? date_for_sql($sc_date_to) : date_now_sql());
	$sc_date_to_p1_sql = ($sc_date_to ? date_for_sql($sc_date_to, false, true, false, false, true) : date_now_sql());

	$months = months_range($sc_date_fr_sql, $sc_date_to_sql, false);
	$months_total_12 = array();
	$months_total_24 = array();
	$months_total_36 = array();
	foreach ($months as $one_m)
	{
		$months_total_12[$one_m] = 0.0;
		$months_total_24[$one_m] = 0.0;
		$months_total_36[$one_m] = 0.0;
	}
	#dprint("Months ($sc_date_fr_sql, $sc_date_to_sql) = " . print_r($months,1));#

	$inv_where = array();
	if ($sc_date_fr)
		$inv_where[] = "$sc_date_fr_sql <= INV_DT";
	if ($sc_date_to)
		$inv_where[] = "INV_DT < $sc_date_to_p1_sql";
	if ($inv_where)
		$inv_where = " AND (" . implode (') AND (', $inv_where) . ")";
	else
		$inv_where = '';

	# Get a list of clients for the selected salesperson
	$sql = "SELECT CLIENT2_ID, C_CODE FROM CLIENT2 WHERE SALESPERSON_ID=$sc_salesperson ORDER BY C_CODE";
	#dprint($sql);#
	sql_execute($sql);
	$clients_1 = array();
	$found_client = false;
	while (($newArray = sql_fetch()) != false)
	{
		if ($debug_sp_cid)
		{
			if ($newArray[0] == $debug_cid)
				$found_client = true;
		}
		$clients_1[] = $newArray[0];
	}
	if ($debug_sp_cid)
	{
		dprint("Client $debug_cid: \$found_client=$found_client.  Months=" . print_r($months,1));
	}
	#dprint("Clients/1 = " . print_r($clients_1,1));#

	# Get the start date of this salesperson for each client
	$clients_2 = array();
	foreach ($clients_1 as $client2_id)
	{
		if ($debug_sp_cid && ($client2_id == $debug_cid))
			$debug = true;
		else
			$debug = false;

		# Get the date that the salesperson started being the salesperson for this client.
		list($ms_top, $my_limit) = sql_top_limit(1);
		$sql = "SELECT $ms_top COALESCE(SP_DT,'1901-01-01') FROM SALESPERSON
				WHERE CLIENT2_ID=$client2_id AND SP_USER_ID=$sc_salesperson ORDER BY SALESPERSON_ID DESC $my_limit";
		if ($debug) dprint($sql);
		sql_execute($sql);
		$start_date = '1902-02-02';
		while (($newArray = sql_fetch()) != false)
		{
			$bits = explode(' ', $newArray[0]); # strip off the time element
			$start_date = $bits[0];
		}
		if ($debug) dprint("start_date/1 = $start_date");

		if ($start_date < '1910')
		{
			# Start date wasn't found in SALESPERSON table, so check jobs instead.
			$start_date_2 = '';
			$sql = "SELECT MIN(J_OPENED_DT) FROM JOB WHERE CLIENT2_ID=$client2_id";
			#dprint($sql);#
			sql_execute($sql);
			while (($newArray = sql_fetch()) != false)
				$start_date_2 = $newArray[0];
			if ($start_date_2)
			{
				$bits = explode(' ', $start_date_2); # strip off the time element
				$start_date = $bits[0]; # the date element
			}
		}
		if ($debug) dprint("start_date/2 = $start_date");
		$bits = explode('-', $start_date);
		$start_date = "{$bits[0]}-{$bits[1]}-01"; # set to the 1st day of the month, like with $months
		if ($debug) dprint("start_date/3 = $start_date");

		$client_name = client_name_from_id($client2_id);

		$month_gross = array();
		foreach ($months as $one_m)
		{
			$gross = 0.0;
			if ($start_date <= $one_m)
			{
				$next_month = date_add_months_kdb(date_to_epoch($one_m), 1);
				$next_month = date_from_epoch(true, $next_month, false, true, true);
				$sql = "SELECT SUM(INV_NET) FROM INVOICE
						WHERE INV_TYPE='I' AND CLIENT2_ID=$client2_id AND '$one_m' <= INV_DT AND INV_DT <= '$next_month' $inv_where";
						# + SUM(INV_VAT)
				if ($debug) dprint($sql);
				sql_execute($sql);
				while (($newArray = sql_fetch()) != false)
					$gross = floatval($newArray[0]);
			}
			elseif ($debug)
				dprint("Month \"$one_m\" is before start date \"$start_date\"");
			$month_gross[$one_m] = $gross;
		}

		if ($sc_sort == 1)
			$clients_2[$client2_id] = array('C_CODE' => $client_name['C_CODE'], 'C_CO_NAME' => $client_name['C_CO_NAME'], 'SINCE' => $start_date);
		elseif ($sc_sort == 2)
			$clients_2[$client2_id] = array('C_CO_NAME' => $client_name['C_CO_NAME'], 'C_CODE' => $client_name['C_CODE'], 'SINCE' => $start_date);
		else
			$clients_2[$client2_id] = array('SINCE' => $start_date, 'C_CODE' => $client_name['C_CODE'], 'C_CO_NAME' => $client_name['C_CO_NAME']);
		foreach ($month_gross as $one_m => $gross)
			$clients_2[$client2_id][$one_m] = $gross;

		if ($debug)
			dprint("client array = " . print_r($clients_2[$client2_id],1));
	}
	asort($clients_2);
	#dprint("Clients/2 = " . print_r($clients_2,1));#

	# Partition the clients according to how long they have been with the salesperson
	$this_month = date_to_epoch(strftime_rdr('%Y-%m-01'));
	#dprint("This month = " . print_r($this_month,1));#

	$clients_12 = array();
	$clients_24 = array();
	$clients_36 = array();

	foreach ($clients_2 as $client2_id => $c_info)
	{
		if ($debug_sp_cid && ($client2_id == $debug_cid))
			$debug = true;
		else
			$debug = false;

		$bits = explode('-', $c_info['SINCE']);
		$start_date = "{$bits[0]}-{$bits[1]}-01"; # set to the 1st day of the month, like with $this_month
		$ep = date_to_epoch($start_date);
		$diff = $this_month - $ep; # in seconds
		$diff = floor($diff / (60.0 * 60.0 * 24.0 * 30.5)); # convert from seconds to months
		if ($debug) dprint("SINCE={$c_info['SINCE']}, ep=$ep, thismonth=$this_month, diff = $diff");

		if ($diff <= 12)
		{
			$clients_12[$client2_id] = $c_info;
			foreach ($months as $one_m)
				$months_total_12[$one_m] += $c_info[$one_m];
		}
		elseif ($diff <= 24)
		{
			$clients_24[$client2_id] = $c_info;
			foreach ($months as $one_m)
				$months_total_24[$one_m] += $c_info[$one_m];
		}
		else
		{
			$clients_36[$client2_id] = $c_info;
			foreach ($months as $one_m)
				$months_total_36[$one_m] += $c_info[$one_m];
		}
	}

	$salesperson_txt = user_name_from_id($sc_salesperson, true);
	$title = "Billing Records by Salesperson: $salesperson_txt";

	$export_xl = (($export == 'xl') ? true : false);
	if ($export_xl)
	{
		$title_short_12 = "12 Months";
		$title_short_24 = "24 Months";
		$title_short_36 = "36 Months";
		$xfile = "SUM" . strftime_rdr("%Y%m%d_%H%M%S") . "_{$USER['USER_ID']}_BillBySalesP";
	}
	else
	{
		$title_short_12 = '';
		$title_short_24 = '';
		$title_short_36 = '';
		$xfile = '';
	}
	$headings = array('Client No.', 'Client Name', 'Since');
	foreach ($months as $one_m)
		$headings[] = date_for_sql($one_m, true, false, true);
	$datalines_12 = array();
	$datalines_24 = array();
	$datalines_36 = array();

	print "
	<table>
	<tr>
		<td><h3>$title</h3></td>
		<td width=\"50\">&nbsp;</td>
		<td>" . input_button('Export to Excel', "export_xl()", '', 'but_export_xl') . "</td>
	</tr>
	</table>
	";

	$colspan = "colspan=\"" . (3 + count($months)) . "\"";

	# === 12-Month sheet ==================

	print "
	<table class=\"spaced_table\">
	<tr>
		<th $colspan>12 Months (up to 12 months)</th>
	</tr>
	<tr>
		";
		foreach ($headings as $one)
			print "<th>$one</th>";
	print "
	</tr>
	";

	if ($clients_12)
	{
		foreach ($clients_12 as $client2_id => $client_info)
		{
			$line_s = array($client_info['C_CODE'], $client_info['C_CO_NAME'], date_for_sql($client_info['SINCE'], true, false));
			$line_x = array($client_info['C_CODE'], $client_info['C_CO_NAME'], date_for_sql($client_info['SINCE'], true, false));
			foreach ($months as $one_m)
			{
				$bits = explode('-', $client_info['SINCE']);
				$start_date = "{$bits[0]}-{$bits[1]}-01"; # set to the 1st day of the month, like with $one_m
				if ($start_date <= $one_m)
				{
					$gross_x = floatval($client_info[$one_m]);
					if ($gross_x)
						$gross_s = money_format_kdb($gross_x, true, true, true);
					else
						$gross_s = '-';
					$line_s[] = $gross_s;
					$line_x[] = $gross_x;
				}
				else
				{
					$line_s[] = '&nbsp;';
					$line_x[] = '';
				}
			}

			$ii = 0;
			print "
			<tr>
				<td $ar>{$line_s[$ii++]}</td>
				<td>{$line_s[$ii++]}</td>
				<td $ar>{$line_s[$ii++]}</td>
				";
				foreach ($months as $one_m)
				{
					$gross_s = $line_s[$ii++];
					if ($gross_s != '&nbsp;')
						print "<td " . (($gross_s == '-') ? $ac : $ar) . ">$gross_s</td>";
					else
						print "<td style=\"background-color:grey\">&nbsp;</td>";
				}
			print "
			</tr>
			";
			if ($export_xl)
				$datalines_12[] = $line_x;
		} # foreach client

		print "
		<tr>
			<td $col3 $ar><b>TOTALS</b></td>
			";
			if ($export_xl)
				$line_x = array('', '', 'TOTALS');
			foreach ($months as $one_m)
			{
				$total_x = $months_total_12[$one_m];
				if ($total_x)
				{
					$total_s = money_format_kdb($total_x, true, true, true);
					$aln = $ar;
				}
				else
				{
					$total_s = '-';
					$aln = $ac;
				}
				print "<td $aln><b>$total_s</b></td>";
				if ($export_xl)
					$line_x[] = $total_x;
			}
		print "
		</tr>
		";
		if ($export_xl)
			$datalines_12[] = $line_x;
	}
	else
		print "<tr><td $colspan>(no clients)<br><br></td></tr>";
	print "
	</table>
	";

	# === 24-Month sheet ==================

	print "
	<table class=\"spaced_table\">
	<tr>
		<th $colspan>24 Months (over 12 months and up to 24 months)</th>
	</tr>
	<tr>
		";
		foreach ($headings as $one)
			print "<th>$one</th>";
	print "
	</tr>
	";

	if ($clients_24)
	{
		foreach ($clients_24 as $client2_id => $client_info)
		{
			$line_s = array($client_info['C_CODE'], $client_info['C_CO_NAME'], date_for_sql($client_info['SINCE'], true, false));
			$line_x = array($client_info['C_CODE'], $client_info['C_CO_NAME'], date_for_sql($client_info['SINCE'], true, false));
			foreach ($months as $one_m)
			{
				$bits = explode('-', $client_info['SINCE']);
				$start_date = "{$bits[0]}-{$bits[1]}-01"; # set to the 1st day of the month, like with $one_m
				if ($start_date <= $one_m)
				{
					$gross_x = floatval($client_info[$one_m]);
					if ($gross_x)
						$gross_s = money_format_kdb($gross_x, true, true, true);
					else
						$gross_s = '-';
					$line_s[] = $gross_s;
					$line_x[] = $gross_x;
				}
				else
				{
					$line_s[] = '&nbsp;';
					$line_x[] = '';
				}
			}

			$ii = 0;
			print "
			<tr>
				<td $ar>{$line_s[$ii++]}</td>
				<td>{$line_s[$ii++]}</td>
				<td $ar>{$line_s[$ii++]}</td>
				";
				foreach ($months as $one_m)
				{
					$gross_s = $line_s[$ii++];
					if ($gross_s != '&nbsp;')
						print "<td " . (($gross_s == '-') ? $ac : $ar) . ">$gross_s</td>";
					else
						print "<td style=\"background-color:grey\">&nbsp;</td>";
				}
			print "
			</tr>
			";
			if ($export_xl)
				$datalines_24[] = $line_x;
		} # foreach client

		print "
		<tr>
			<td $col3 $ar><b>TOTALS</b></td>
			";
			if ($export_xl)
				$line_x = array('', '', 'TOTALS');
			foreach ($months as $one_m)
			{
				$total_x = $months_total_24[$one_m];
				if ($total_x)
				{
					$total_s = money_format_kdb($total_x, true, true, true);
					$aln = $ar;
				}
				else
				{
					$total_s = '-';
					$aln = $ac;
				}
				print "<td $aln><b>$total_s</b></td>";
				if ($export_xl)
					$line_x[] = $total_x;
			}
		print "
		</tr>
		";
		if ($export_xl)
			$datalines_24[] = $line_x;
	}
	else
		print "<tr><td $colspan>(no clients)<br><br></td></tr>";
	print "
	</table>
	";

	# === 36-Month sheet ==================

	print "
	<table class=\"spaced_table\">
	<tr>
		<th $colspan>36 Months (over 24 months)</th>
	</tr>
	<tr>
		";
		foreach ($headings as $one)
			print "<th>$one</th>";
	print "
	</tr>
	";

	if ($clients_36)
	{
		foreach ($clients_36 as $client2_id => $client_info)
		{
			$line_s = array($client_info['C_CODE'], $client_info['C_CO_NAME'], date_for_sql($client_info['SINCE'], true, false));
			$line_x = array($client_info['C_CODE'], $client_info['C_CO_NAME'], date_for_sql($client_info['SINCE'], true, false));
			foreach ($months as $one_m)
			{
				$bits = explode('-', $client_info['SINCE']);
				$start_date = "{$bits[0]}-{$bits[1]}-01"; # set to the 1st day of the month, like with $one_m
				if ($start_date <= $one_m)
				{
					$gross_x = floatval($client_info[$one_m]);
					if ($gross_x)
						$gross_s = money_format_kdb($gross_x, true, true, true);
					else
						$gross_s = '-';
					$line_s[] = $gross_s;
					$line_x[] = $gross_x;
				}
				else
				{
					$line_s[] = '&nbsp;';
					$line_x[] = '';
				}
			}

			$ii = 0;
			print "
			<tr>
				<td $ar>{$line_s[$ii++]}</td>
				<td>{$line_s[$ii++]}</td>
				<td $ar>{$line_s[$ii++]}</td>
				";
				foreach ($months as $one_m)
				{
					$gross_s = $line_s[$ii++];
					if ($gross_s != '&nbsp;')
						print "<td " . (($gross_s == '-') ? $ac : $ar) . ">$gross_s</td>";
					else
						print "<td style=\"background-color:grey\">&nbsp;</td>";
				}
			print "
			</tr>
			";
			if ($export_xl)
				$datalines_36[] = $line_x;
		} # foreach client

		print "
		<tr>
			<td $col3 $ar><b>TOTALS</b></td>
			";
			if ($export_xl)
				$line_x = array('', '', 'TOTALS');
			foreach ($months as $one_m)
			{
				$total_x = $months_total_36[$one_m];
				if ($total_x)
				{
					$total_s = money_format_kdb($total_x, true, true, true);
					$aln = $ar;
				}
				else
				{
					$total_s = '-';
					$aln = $ac;
				}
				print "<td $aln><b>$total_s</b></td>";
				if ($export_xl)
					$line_x[] = $total_x;
			}
		print "
		</tr>
		";
		if ($export_xl)
			$datalines_36[] = $line_x;
	}
	else
		print "<tr><td $colspan>(no clients)<br><br></td></tr>";
	print "
	</table>
	";

	# === Excel export ==================

	if ($export_xl)
	{
		$top_lines = array();
		$formats = array('A' => $excel_integer_format_no_comma, 'C' => $excel_date_format, 'D' => $excel_currency_format);
		$colm = 'D';
		foreach ($months as $one_m)
		{
			$colm = excel_next_column($colm);
			$formats[$colm] = $excel_currency_format;
		}
		$sheet_12 = array($title_short_12, $top_lines, $headings, $datalines_12, $formats, '');
		$sheet_24 = array($title_short_24, $top_lines, $headings, $datalines_24, $formats, '');
		$sheet_36 = array($title_short_36, $top_lines, $headings, $datalines_36, $formats, '');
		phpExcel_output($reps_subdir, $xfile, array($sheet_12, $sheet_24, $sheet_36)); # library.php

		# Auto-download file to PC
		print "
		<script type=\"text/javascript\">
			document.form_csv_download.short_fname.value = '$xfile.$phpExcel_ext';
			document.form_csv_download.full_fname.value = '$csv_path' + '{$reps_subdir}/$xfile.$phpExcel_ext';
			document.form_csv_download.submit();
		</script>
		";
	}

} # bills_by_salesperson()

function view_aged_debtors()
{
	global $ar;
	global $col3;
	global $csv_path;
	global $excel_currency_format;
	global $excel_integer_format_no_comma;
	global $export;
	global $phpExcel_ext;
	global $sqlFalse;
	global $reps_subdir;
	global $USER;

	set_time_limit(60 * 10); # 10 minutes

//	$feedback_192 = true;

	# Get all unpaid invoices
	# Feedback #17 - only invoices that are not imported from the old system.
	sql_encryption_preparation('CLIENT2');
	$sql = "SELECT I.INVOICE_ID, I.CLIENT2_ID, C.C_CODE, " . sql_decrypt('C.C_CO_NAME', '', true) . ",
					I.INV_DT, I.INV_DUE_DT, I.INV_NET + I.INV_VAT AS INV_GROSS, I.INV_PAID
			FROM INVOICE AS I
			INNER JOIN CLIENT2 AS C ON C.CLIENT2_ID=I.CLIENT2_ID
			WHERE (I.IMPORTED=$sqlFalse) AND (I.OBSOLETE=$sqlFalse) AND (I.INV_TYPE='I') AND (I.INV_PAID <> (I.INV_NET + I.INV_VAT))
			";
			#$sql .= " AND C.CLIENT2_ID=880 ";#
			#$sql .= " AND C.CLIENT2_ID=5002 ";#
			#$sql .= " AND C.CLIENT2_ID>5000 ";#
			$sql .= "
			ORDER BY C.C_CODE";
	sql_execute($sql);
	$invoices = array();
	while (($newArray = sql_fetch_assoc()) != false)
	{
		$invoices[] = $newArray;
		#if ($newArray['CLIENT2_ID'] == 1498)#
		#	dprint(print_r($newArray,1));
	}
	#dprint("Under-paid/over-paid invoice count: " . count($invoices));#
	#dprint("Under-paid/over-paid invoices: " . print_r($invoices,1));#

	$today = date_to_epoch(strftime_rdr("%Y-%m-%d"), false); # today without the time
	#$today = date_to_epoch('2015-01-10', false);#
	$now_0 = date_to_epoch(strftime_rdr("%Y-%m-01"), false); # first day of today's month
	#$now_0 = date_to_epoch('2015-01-01', false);#
	$now_1 = date_add_months_kdb($now_0, -1); # first day of last month
	$now_2 = date_add_months_kdb($now_0, -2); # first day of two months ago
	$now_3 = date_add_months_kdb($now_0, -3); # first day of three months ago

	$txt_0 = "Current";
	$txt_1 = strftime_rdr("%B", $now_1);
	$txt_2 = strftime_rdr("%B", $now_2);
	$txt_3 = strftime_rdr("%B", $now_3);
	$txt_4 = "Prior";

	# Get amount owed for each client, in due-date bands.
	$clients = array();
	$grand_totals = array('C_CODE' => '', 'C_CO_NAME' => '',
							'DUE_0' => 0.0, 'DUE_1' => 0.0, 'DUE_2' => 0.0, 'DUE_3' => 0.0, 'DUE_4' => 0.0,
							'ACCOUNT' => 0.0, 'TOTAL' => 0.0);
	foreach ($invoices as $one_i)
	{
		if (!array_key_exists($one_i['CLIENT2_ID'], $clients))
			$clients[$one_i['CLIENT2_ID']] = array('C_CODE' => $one_i['C_CODE'], 'C_CO_NAME' => $one_i['C_CO_NAME'],
													'DUE_0' => 0.0, 'DUE_1' => 0.0, 'DUE_2' => 0.0, 'DUE_3' => 0.0, 'DUE_4' => 0.0,
													'ACCOUNT' => 0.0, 'TOTAL' => 0.0);

		$unpaid = floatval($one_i['INV_GROSS']) - floatval($one_i['INV_PAID']);
		if ($one_i['INV_DUE_DT'])
			$due_ep = date_to_epoch($one_i['INV_DUE_DT'], false);
		else
			$due_ep = date_to_epoch($one_i['INV_DT'], false, 30);
		if ($now_0 <= $due_ep)
		{
			if ($due_ep < $today)
				$clients[$one_i['CLIENT2_ID']]['DUE_0'] += $unpaid;
		}
		elseif ($now_1 <= $due_ep)
			$clients[$one_i['CLIENT2_ID']]['DUE_1'] += $unpaid;
		elseif ($now_2 <= $due_ep)
			$clients[$one_i['CLIENT2_ID']]['DUE_2'] += $unpaid;
		elseif ($now_3 <= $due_ep)
			$clients[$one_i['CLIENT2_ID']]['DUE_3'] += $unpaid;
		else
			$clients[$one_i['CLIENT2_ID']]['DUE_4'] += $unpaid;
	}
	#dprint("Got DUE amounts");#
	#dprint("Clients after DUE amounts established: " . print_r($clients,1));#

	# Get monies on account for each client.
	#$count = 0;
	foreach ($clients as $client2_id => $one_c)
	{
		#if ($client2_id == 1498)#
		#	dprint("one_c = " . print_r($one_c,1));

		# The following is dead code - its yield is not used
//		# The problem with the following method, which is correct logically, is that some
//		# reciepts are not allocated to invoices, even though they should be, so we need to find
//		# a different method.
//		# Get receipts and adjustments for this client
//		# Feedback #17 - only receipts and adjustments that are not imported from the old system.
//		$sql = "SELECT R.INV_RECP_ID, R.RC_AMOUNT, SUM(COALESCE(A.AL_AMOUNT,0.0)) AS SUM_AL_AMOUNT
//				FROM INV_RECP AS R LEFT JOIN INV_ALLOC AS A ON A.INV_RECP_ID=R.INV_RECP_ID
//				WHERE (R.IMPORTED=$sqlFalse) AND (A.IMPORTED=$sqlFalse) AND (R.OBSOLETE=$sqlFalse) AND (R.CLIENT2_ID=$client2_id)
//				GROUP BY R.INV_RECP_ID, R.RC_AMOUNT
//				HAVING SUM(COALESCE(A.AL_AMOUNT,0.0)) < R.RC_AMOUNT";
//		sql_execute($sql);
//		#$receipts = array();
//		$account = 0.0;
//		while (($newArray = sql_fetch_assoc()) != false)
//		{
//			#$receipts[] = $newArray;
//			$unalloc = (1.0 * $newArray['RC_AMOUNT']) - (1.0 * $newArray['SUM_AL_AMOUNT']);
//			if (0 < $unalloc)
//				$account += $unalloc;
//		}
//		#dprint("account from allocations=$account");#

//		if (!$feedback_192)
//		{
//		# Alternative method:
//		# Feedback #17 - only invoices that are not imported from the old system.
//		$sql = "SELECT SUM(INV_NET + INV_VAT) FROM INVOICE WHERE IMPORTED=$sqlFalse AND OBSOLETE=$sqlFalse AND CLIENT2_ID=$client2_id AND INV_TYPE='I'";
//		sql_execute($sql);
//		$amt_inv = 0.0;
//		while (($newArray = sql_fetch()) != false)
//			$amt_inv = 1.0 * $newArray[0];
//		#dprint("amt_inv=$amt_inv");#
//		$sql = "SELECT SUM(INV_NET + INV_VAT) FROM INVOICE WHERE IMPORTED=$sqlFalse AND OBSOLETE=$sqlFalse AND CLIENT2_ID=$client2_id AND INV_TYPE='C'";
//		sql_execute($sql);
//		$amt_cr = 0.0;
//		while (($newArray = sql_fetch()) != false)
//			$amt_cr = -1.0 * $newArray[0];
//		#dprint("amt_cr=$amt_cr");#
//		$sql = "SELECT SUM(RC_AMOUNT) FROM INV_RECP WHERE IMPORTED=$sqlFalse AND OBSOLETE=$sqlFalse AND CLIENT2_ID=$client2_id";
//		sql_execute($sql);
//		$amt_rx = 0.0;
//		while (($newArray = sql_fetch()) != false)
//			$amt_rx = 1.0 * $newArray[0];
//		#dprint("amt_rx=$amt_rx");#
//		$sql = "SELECT SUM(INV_PAID) FROM INVOICE WHERE IMPORTED=$sqlFalse AND OBSOLETE=$sqlFalse AND CLIENT2_ID=$client2_id AND INV_TYPE='I'";
//		sql_execute($sql);
//		$amt_pd = 0.0;
//		while (($newArray = sql_fetch()) != false)
//			$amt_pd = 1.0 * $newArray[0];
//		#dprint("amt_pd=$amt_pd");#
//		$amt_plus = max($amt_rx, $amt_pd) + $amt_cr;
//		$account = $amt_plus - $amt_inv;
//		#dprint("account=$account");#
//		if ($account < 0.0)
//			$account = 0.0;
//		}
//		else
//		{
			# Fix for Feedback #192.
			# ID8061=CC8134, ID1498=CC1602

//			# Find all allocations that are for non-imported invoices.
//			$sql = "SELECT A.AL_AMOUNT
//					FROM INV_RECP AS R
//					INNER JOIN INV_ALLOC AS A ON A.INV_RECP_ID=R.INV_RECP_ID
//					INNER JOIN INVOICE AS I ON I.INVOICE_ID=A.INVOICE_ID
//					WHERE R.IMPORTED=$sqlFalse AND A.IMPORTED=$sqlFalse AND I.IMPORTED=$sqlFalse
//							AND R.OBSOLETE=$sqlFalse AND R.CLIENT2_ID=$client2_id";
//			sql_execute($sql);
//			$amt_rx = 0.0;
//			while (($newArray = sql_fetch()) != false)
//				$amt_rx += (1.0 * $newArray[0]);
//
			# Find all receipt amounts that are unallocated, but only non-imported receipts.

			# First, get sum amount of all non-imported receipts.
			$sql = "SELECT R.INV_RECP_ID, R.RC_AMOUNT
					FROM INV_RECP AS R
					WHERE R.IMPORTED=$sqlFalse AND R.OBSOLETE=$sqlFalse AND R.CLIENT2_ID=$client2_id";
			sql_execute($sql);
			$receipts = array();
			$account = 0.0;
			while (($newArray = sql_fetch()) != false)
			{
				$receipts[] = $newArray[0];
				$account += floatval($newArray[1]);
			}
			#if (($client2_id == 1199) || ($client2_id == 1498))#
			#	dprint("account/1 = $account");

			# Second, for those receipts, get sum amount of allocations (regardless of whether allocated to imported invoices or not).
			foreach ($receipts as $one_recp)
			{
				$sql = "SELECT A.AL_AMOUNT
						FROM INV_ALLOC AS A
						WHERE A.INV_RECP_ID=$one_recp";
				sql_execute($sql);
				while (($newArray = sql_fetch()) != false)
					$account -= floatval($newArray[0]);
			}
			if (((-0.01 < $account) && ($account < 0.0)) || ((0.0 < $account) && ($account < 0.01)))
				$account = 0.0;
			#if (($client2_id == 1199) || ($client2_id == 1498))#
			#	dprint("account/2 = $account");
//		}

		$clients[$client2_id]['ACCOUNT'] = -1.0 * floatval($account);
		$clients[$client2_id]['TOTAL'] = $clients[$client2_id]['DUE_0'] + $clients[$client2_id]['DUE_1'] + $clients[$client2_id]['DUE_2'] +
											$clients[$client2_id]['DUE_3'] + $clients[$client2_id]['DUE_4'] + $clients[$client2_id]['ACCOUNT'];

		if (round($clients[$client2_id]['TOTAL'], 2) != 0.0)
		{
			$grand_totals['DUE_0'] += $clients[$client2_id]['DUE_0'];
			$grand_totals['DUE_1'] += $clients[$client2_id]['DUE_1'];
			$grand_totals['DUE_2'] += $clients[$client2_id]['DUE_2'];
			$grand_totals['DUE_3'] += $clients[$client2_id]['DUE_3'];
			$grand_totals['DUE_4'] += $clients[$client2_id]['DUE_4'];
			$grand_totals['ACCOUNT'] += $clients[$client2_id]['ACCOUNT'];
			$grand_totals['TOTAL'] += $clients[$client2_id]['TOTAL'];
		}
		#if ($count < 100)
		#	dprint("Receipts for " . print_r($one_c,1) . ":<br>$sql<br>" . print_r($receipts,1));#
		#$count++;
	}
	#dprint("Clients: " . print_r($clients,1));#

	$title = "Aged Debtors List &mdash; " . date_now(true);
	$export_xl = (($export == 'xl') ? true : false);
	if ($export_xl)
	{
		$title = str_replace('&mdash;', '-', $title);
		$title_short = "Aged Debtors";
		$xfile = "SUM" . strftime_rdr("%Y%m%d_%H%M%S") . "_{$USER['USER_ID']}_AgedDebt";
	}
	else
	{
		$title_short = '';
		$xfile = '';
	}
	$headings = array('Account', $txt_4, $txt_3, $txt_2, $txt_1, $txt_0, 'Account', 'Total Owed', 'Client Name');
	$datalines = array();

	print "
	<table>
	<tr>
		<td><h3>$title</h3></td><td width=\"50\">&nbsp;</td>
		<td>" . input_button('Export to Excel', "export_xl()", '', 'but_export_xl') . "</td>
	</tr>
	</table>
	";

	print "
	<table>
	<tr>
		<th $col3 align=\"left\">Summary of Aged Debtors</td><td width=\"20\">&nbsp;</td><td>&nbsp;</td>
	</tr>
	<tr>
		<td>$txt_4</td><td></td><td $ar>" . money_format_kdb($grand_totals['DUE_4'], true, true, true) . "</td>
	</tr>
	<tr>
		<td>$txt_3</td><td></td><td $ar>" . money_format_kdb($grand_totals['DUE_3'], true, true, true) . "</td>
	</tr>
	<tr>
		<td>$txt_2</td><td></td><td $ar>" . money_format_kdb($grand_totals['DUE_2'], true, true, true) . "</td>
	</tr>
	<tr>
		<td>$txt_1</td><td></td><td $ar>" . money_format_kdb($grand_totals['DUE_1'], true, true, true) . "</td>
	</tr>
	<tr>
		<td>$txt_0</td><td></td><td $ar>" . money_format_kdb($grand_totals['DUE_0'], true, true, true) . "</td>
	</tr>
	<tr>
		<td>On Account</td><td></td><td $ar>" . money_format_kdb($grand_totals['ACCOUNT'], true, true, true) . "</td>
	</tr>
	<tr>
		<td>Total Owed</td><td></td><td $ar>" . money_format_kdb($grand_totals['TOTAL'], true, true, true) . "</td>
	</tr>
	</table>
	<br><br>
	";
	if ($export_xl)
		$summary = array(
			array('##BOLD##Summary of Aged Debtors'),
			array($txt_4, '##CURR##' . $grand_totals['DUE_4']),
			array($txt_3, '##CURR##' . $grand_totals['DUE_3']),
			array($txt_2, '##CURR##' . $grand_totals['DUE_2']),
			array($txt_1, '##CURR##' . $grand_totals['DUE_1']),
			array($txt_0, '##CURR##' . $grand_totals['DUE_0']),
			array('On Account', '##CURR##' . $grand_totals['ACCOUNT']),
			array('Total Owed', '##CURR##' . $grand_totals['TOTAL']),
			array(),
			array('##BOLD##Details of Aged Debtors')
			);
	else
		$summary = array();

	print "
	<table class=\"basic_table\" border=\"0\"><!---->
	<tr>
		<th $col3 align=\"left\">Details of Aged Debtors</td>
	</tr>
	<tr>
		<td></td>
	";
		for ($ii = 0; $ii <= 8; $ii++)
			print "<th>{$headings[$ii]}</th>";
	print "
	</tr>
	";

	$pad = "&nbsp;&nbsp;";
	foreach ($clients as $one_c)
	{
		$total_owed = round(floatval($one_c['TOTAL']), 2);
		if ($total_owed == 0)
			continue;
		elseif ($total_owed < 0)
			$rcol = "green";
		else
			$rcol = "white";
		print "
		<tr>
			<td style=\"width:50px; background-color:$rcol;\">
			<td $ar>{$one_c['C_CODE']}</td>
			<td $ar>" . money_format_kdb($one_c['DUE_4'], true, true, true) . "</td>
			<td $ar>" . money_format_kdb($one_c['DUE_3'], true, true, true) . "</td>
			<td $ar>" . money_format_kdb($one_c['DUE_2'], true, true, true) . "</td>
			<td $ar>" . money_format_kdb($one_c['DUE_1'], true, true, true) . "</td>
			<td $ar>" . money_format_kdb($one_c['DUE_0'], true, true, true) . "</td>
			<td $ar>" . money_format_kdb($one_c['ACCOUNT'], true, true, true) . "</td>
			<td $ar>" . money_format_kdb($one_c['TOTAL'], true, true, true) . "</td>
			<td>$pad{$one_c['C_CO_NAME']}</td>
		</tr>
		";
		if ($export_xl)
			$datalines[] = array($one_c['C_CODE'], $one_c['DUE_4'], $one_c['DUE_3'], $one_c['DUE_2'], $one_c['DUE_1'], $one_c['DUE_0'],
									$one_c['ACCOUNT'], $one_c['TOTAL'], $one_c['C_CO_NAME']);
	}
	$client_count = count($clients);
	print "
	<tr>
		<td $ar>Totals</td>
		<td $ar>" . money_format_kdb($grand_totals['DUE_4'], true, true, true) . "</td>
		<td $ar>" . money_format_kdb($grand_totals['DUE_3'], true, true, true) . "</td>
		<td $ar>" . money_format_kdb($grand_totals['DUE_2'], true, true, true) . "</td>
		<td $ar>" . money_format_kdb($grand_totals['DUE_1'], true, true, true) . "</td>
		<td $ar>" . money_format_kdb($grand_totals['DUE_0'], true, true, true) . "</td>
		<td $ar>" . money_format_kdb($grand_totals['ACCOUNT'], true, true, true) . "</td>
		<td $ar>" . money_format_kdb($grand_totals['TOTAL'], true, true, true) . "</td>
		<td>$pad($client_count clients)</td>
	</tr>
	";
	if ($export_xl)
		$datalines[] = array('Totals', $grand_totals['DUE_4'], $grand_totals['DUE_3'], $grand_totals['DUE_2'], $grand_totals['DUE_1'],
								$grand_totals['DUE_0'], $grand_totals['ACCOUNT'], $grand_totals['TOTAL'], "($client_count clients)");
	print "
	</table>
	";

	if ($export_xl)
	{
		$top_lines = array_merge(array(array($title), array()), $summary);
		$formats = array('A' => $excel_integer_format_no_comma, 'B' => $excel_currency_format, 'C' => $excel_currency_format,
							'D' => $excel_currency_format, 'E' => $excel_currency_format, 'F' => $excel_currency_format,
							'G' => $excel_currency_format, 'H' => $excel_currency_format);
		$sheet = array($title_short, $top_lines, $headings, $datalines, $formats, '');
		phpExcel_output($reps_subdir, $xfile, array($sheet)); # library.php

		# Auto-download file to PC
		print "
		<script type=\"text/javascript\">
			document.form_csv_download.short_fname.value = '$xfile.$phpExcel_ext';
			document.form_csv_download.full_fname.value = '$csv_path' + '{$reps_subdir}/$xfile.$phpExcel_ext';
			document.form_csv_download.submit();
		</script>
		";
	}
} # view_aged_debtors()

function check_client_range()
{
	global $sc_client_list;
	global $sc_client_range;
	global $sc_clients;
	global $sc_clients_ok;

	$sc_clients_ok = false;
	$sc_client_range = array();
	$sc_client_list = array();
	$allowed_txt = "Client codes can be one code, or two codes separated by a hyphen, or two or more codes separated by commas";

	$sc_clients = trim(str_replace(' ', '', $sc_clients));
	if ($sc_clients === '')
		$sc_clients_ok = true; # all clients
	elseif (strpos($sc_clients, '-') !== false)
	{
		# Range of clients
		$bits = explode('-', $sc_clients);
		if (count($bits) == 2)
		{
			$c1 = $bits[0];
			$c2 = $bits[1];
			if (is_numeric_kdb($c1, false, false, false) && is_numeric_kdb($c2, false, false, false))
			{
				if ($c1 == $c2)
				{
					$sc_client_list = array(intval($c1));
					$sc_clients = $sc_client_list[0];
					$sc_clients_ok = true;
				}
				else
				{
					#if ($c2 < $c1)
					#{
					#	$temp = $c1;
					#	$c1 = $c2;
					#	$c2 = $temp;
					#}
					$sc_client_range = array(intval($c1), intval($c2));
					$sc_clients = $sc_client_range[0] . '-' . $sc_client_range[1];
					$sc_clients_ok = true;
				}
			}
		}
	}
	elseif (strpos($sc_clients, ',') !== false)
	{
		# List of clients
		$bits = explode(',', $sc_clients);
		foreach ($bits as $one)
		{
			if (is_numeric_kdb($one, false, false, false))
				$sc_client_list[] = intval($one);
			else
			{
				$sc_client_list = array();
				break;
			}
		}
		if (0 < count($sc_client_list))
		{
			$sc_clients = implode(',', $sc_client_list);
			$sc_clients_ok = true;
		}
	}
	elseif (is_numeric_kdb($sc_clients, false, false, false))
	{
		$sc_client_list = array(intval($sc_clients));
		$sc_clients = $sc_client_list[0];
		$sc_clients_ok = true;
	}

	if (!$sc_clients_ok)
		dprint($allowed_txt, true);

	#dprint("Check: ok=($sc_clients_ok), box=($sc_clients), range=" . print_r($sc_client_range,1) . ", list=" . print_r($sc_client_list,1));#
}

function view_statements()
{
	global $csv_path;
	global $excel_currency_format;
	global $excel_date_format;
	global $excel_integer_format_no_comma;
	global $excel_sheets;
	global $export;
	global $pdf_messages;
	global $phpExcel_ext;
	global $sc_client_list;
	global $sc_client_range;
	global $sc_clients;
	global $sc_date_cut;
	global $sqlFalse;
	global $reps_subdir;
	global $USER;

	if (!$sc_date_cut)
	{
		dprint("Please specify a cut-off date", true);
		return;
	}

	if (count($sc_client_list) == 1)
		$where = "C_CODE = {$sc_client_list[0]}";
	elseif (0 < count($sc_client_list))
		$where = "C_CODE IN (" . implode(',', $sc_client_list) . ")";
	elseif (0 < count($sc_client_range))
	{
		$c1 = $sc_client_range[0];
		$c2 = $sc_client_range[1];
		if ($c2 < $c1)
		{
			$temp = $c1;
			$c1 = $c2;
			$c2 = $temp;
		}
		$where = "$c1 <= C_CODE AND C_CODE <= $c2";
	}
	else
		$where = "0 < CLIENT2_ID";

	sql_encryption_preparation('CLIENT2');
	sql_encryption_preparation('CLIENT_CONTACT');

	$sql = "SELECT CLIENT2_ID, C_CODE, INV_EMAILED, " . sql_decrypt('INV_EMAIL_NAME', '', true) . ", " . sql_decrypt('INV_EMAIL_ADDR', '', true) . "
			FROM CLIENT2
			WHERE $where AND C_ARCHIVED=$sqlFalse
			ORDER BY C_CODE";
	#dprint($sql);#
	sql_execute($sql);
	$clients = array();
	while (($newArray = sql_fetch_assoc()) != false)
		$clients[$newArray['CLIENT2_ID']] = $newArray;
	#dprint("Found " . count($clients) . " clients");#

	foreach ($clients as $client2_id => $one)
	{
		$e_name = '';
		$e_address = '';
		if ($one['INV_EMAILED'] == 1)
		{
			if ($one['INV_EMAIL_ADDR'])
			{
				$e_address= $one['INV_EMAIL_ADDR'];
				$e_name = ($one['INV_EMAIL_NAME'] ? $one['INV_EMAIL_NAME'] : 'Sir/Madam');
			}
			else
			{
				$sql = "SELECT " . sql_decrypt('CC_FIRSTNAME', '', true) . ", " . sql_decrypt('CC_LASTNAME', '', true) . ",
								" . sql_decrypt('CC_EMAIL_1', '', true) . ", " . sql_decrypt('CC_EMAIL_2', '', true) . "
						FROM CLIENT_CONTACT
						WHERE CLIENT2_ID=$client2_id AND OBSOLETE=$sqlFalse
						ORDER BY CC_INV DESC, CC_MAIN DESC
						";
				sql_execute($sql);
				while (($newArray = sql_fetch_assoc()) != false)
				{
					if (!$e_address)
					{
						$e_address = $newArray['CC_EMAIL_1'];
						if (!$e_address)
							$e_address = $newArray['CC_EMAIL_2'];
						if ($e_address)
							$e_name = trim("{$newArray['CC_FIRSTNAME']} {$newArray['CC_LASTNAME']}");
					}
				}
			}
		}
		$clients[$client2_id] = array('C_CODE' => $one['C_CODE'],
										'INV_EMAILED' => $one['INV_EMAILED'], 'E_NAME' => $e_name, 'E_ADDRESS' => $e_address);
	}
	#dprint("View Statements: clients=" . print_r($clients,1), true);#

	$plural = (1 < count($clients)) ? true : false;
	$title = "View Statements &nbsp;&nbsp;&nbsp; Cut-off date $sc_date_cut &nbsp;&nbsp;&nbsp; ";
	if ($sc_clients)
		$title .= "Client" . ($plural ? 's' : '') . " $sc_clients";
	else
		$title .= "All clients";

	$headings = array('Date', 'Type', 'Reference', 'Debit', 'Credit', 'Balance');

	$export_xl = (($export == 'xl') ? true : false);
	if ($export_xl)
	{
		$formats = array('A' => $excel_date_format, 'C' => $excel_integer_format_no_comma,
						'D' => $excel_currency_format, 'E' => $excel_currency_format, 'F' => $excel_currency_format);
		$xfile = "SUM" . strftime_rdr("%Y%m%d_%H%M%S") . "_{$USER['USER_ID']}_ViewStmts";
	}
	else
	{
		$formats = array();
		$xfile = '';
	}

	$send_emails = (($export == 'email') ? true : false);

	$pdf_messages = array();

	print "
	<table>
	<tr>
		<td><h3>$title</h3></td>
		<td width=\"50\">&nbsp;</td>
		<td>" . input_button('Export to Excel', "export_xl()", '', 'but_export_xl') . "</td>
		<td width=\"50\">&nbsp;</td>
		<td>" . input_button('Send out by Email', "export_email()", '', 'but_export_email') . "</td>
	</tr>
	</table>
	";

	foreach ($clients as $client2_id => $one)
	{
		view_statements_client($client2_id, $one['C_CODE'], $headings, $formats, $export_xl, $send_emails,
								$one['INV_EMAILED'], $one['E_NAME'], $one['E_ADDRESS']);#, $title);
	}

	# === Excel export ==================

	if ($export_xl)
	{
		phpExcel_output($reps_subdir, $xfile, $excel_sheets); # library.php

		# Auto-download file to PC
		print "
		<script type=\"text/javascript\">
			document.form_csv_download.short_fname.value = '$xfile.$phpExcel_ext';
			document.form_csv_download.full_fname.value = '$csv_path' + '{$reps_subdir}/$xfile.$phpExcel_ext';
			document.form_csv_download.submit();
		</script>
		";
	}

	if ($send_emails)
	{
		$errors = array();
		foreach ($pdf_messages as $cid => $one)
		{
			if ($one)
				$errors[] = "The PDF for client with ID $cid failed: $one";
		}
		if ($errors)
		{
			$output = "The following errors occured whilst creating PDFs:<br>";
			foreach ($errors as $one)
				$output .= "$one<br>";
		}
		else
			$output = "PDFs of all statements (to be emailed) were created successfully";
		dprint($output, true, 'blue');
	}

} # view_statements()

function view_statements_client($client2_id, $c_code, $headings, $formats, $export_xl, $send_emails, $inv_emailed, $e_name, $e_address)#, $title)
{
	global $ac;
	global $ar;
	#global $col4;
	#global $col7;
	global $col13;
	global $col16;
	global $col22;
	global $crlf;
	global $csv_dir;
	global $email_accounts;
	global $emailName_accounts;
	global $excel_sheets;
	global $pdf_messages;
	global $sc_date_cut;
	global $sqlFalse;
	global $sqlTrue;

	$debug = false; #((global_debug() && $client2_id == 3807) ? true : false);
	#$debug = global_debug();#

	# Find invoices issued before or on the cut-off date
	$sc_date_cut_sql = date_for_sql($sc_date_cut);
	$sc_date_cut_sql_p1 = date_for_sql($sc_date_cut, false, false, false, false, true); # plus one day
	$sql = "SELECT I.INVOICE_ID, I.INV_SYS, I.INV_DT, I.INV_DUE_DT, I.INV_TYPE, I.INV_NUM, I.INV_NET + I.INV_VAT AS INV_GROSS, I.INV_PAID
			FROM INVOICE AS I
			INNER JOIN CLIENT2 AS C ON C.CLIENT2_ID=I.CLIENT2_ID
			WHERE (I.CLIENT2_ID=$client2_id) AND (I.OBSOLETE=$sqlFalse) AND (I.INV_DT < $sc_date_cut_sql_p1) AND
				" .
				#	(I.INV_TYPE IN ('I','C')) AND (I.INV_PAID < (I.INV_NET + I.INV_VAT))
				"	(	((I.INV_TYPE = 'I') AND (COALESCE(I.INV_PAID,0) < (I.INV_NET + I.INV_VAT)))
						OR
						((I.INV_TYPE = 'C') AND (I.LINKED_ID IS NULL) AND (I.IMPORTED=0))
					)
			ORDER BY I.INV_DT";
	if ($debug) dprint($sql);#
	sql_execute($sql);
	$invoices = array();
	$invoice_id_list = array();
	$i_gross = 0.0;
	$i_paid = 0.0;
	while (($newArray = sql_fetch_assoc()) != false)
	{
		$invoices[$newArray['INVOICE_ID']] = $newArray;
		$invoice_id_list[] = $newArray['INVOICE_ID'];
		#$i_gross += ( (($newArray['INV_TYPE'] == 'I') ? 1.0 : -1.0) * $newArray['INV_GROSS'] );
		$i_gross += floatval($newArray['INV_GROSS']);
		#$i_paid += ( (($newArray['INV_TYPE'] == 'I') ? 1.0 : -1.0) * $newArray['INV_PAID'] );
		$i_paid += floatval($newArray['INV_PAID']);
	}
	if ($debug) dprint("Invoices=" . print_r($invoices,1));

	# Feedback #219 - include all invoices and un-linked credits
//	if ($i_gross == $i_paid)
//	{
//		if ($debug) dprint("...fully paid, exiting");
//		return; # we don't do statements for clients who are fully paid (up to the cut-off date)
//	}

	# Find receipts (that are not or only partially allocated to invoices) dated from one month before the cut-off date
	$recp_cut_ep = date_to_epoch($sc_date_cut_sql, false, -31);
	$recp_cut_sql = "'" . date_from_epoch(true, $recp_cut_ep, false, false, true) . "'";
	if ($debug) dprint("sc_dat_cut_sql=$sc_date_cut_sql, recp_cut_ep=$recp_cut_ep, recp_cut_sql=$recp_cut_sql");
	$sql = "SELECT R.INV_RECP_ID, R.RC_NUM, R.RC_DT, R.RC_AMOUNT, SUM(COALESCE(A.AL_AMOUNT,0.0)) AS SUM_AL_AMOUNT
			FROM INV_RECP AS R LEFT JOIN INV_ALLOC AS A ON A.INV_RECP_ID=R.INV_RECP_ID
			WHERE (R.OBSOLETE=$sqlFalse) AND (R.CLIENT2_ID=$client2_id) AND ($recp_cut_sql <= R.RC_DT)
			GROUP BY R.INV_RECP_ID, R.RC_NUM, R.RC_DT, R.RC_AMOUNT
			HAVING SUM(COALESCE(A.AL_AMOUNT,0.0)) < R.RC_AMOUNT";
	if ($debug) dprint($sql);#
	sql_execute($sql);
	$receipts = array();
	$receipt_id_list = array();
	$account = 0.0;
	$unalloc = 0.0;
	while (($newArray = sql_fetch_assoc()) != false)
	{
		$unalloc = floatval($newArray['RC_AMOUNT']) - floatval($newArray['SUM_AL_AMOUNT']);
		if (0 < $unalloc)
		{
			$receipts[$newArray['INV_RECP_ID']] = array('RC_NUM' => $newArray['RC_NUM'], 'RC_DT' => $newArray['RC_DT'],
								'RC_AMOUNT' => $newArray['RC_AMOUNT'], 'SUM_AL_AMOUNT' => $newArray['SUM_AL_AMOUNT'], 'UNALLOC' => $unalloc);
			$receipt_id_list[] = $newArray['INV_RECP_ID'];
			$account += $unalloc;
		}
	}

	if ($debug) dprint("...i_gross=$i_gross, i_paid=$i_paid, unalloc=$unalloc, invoice count=" . count($invoices) . ", receipt count=" . count($receipts));

	#$today = date_to_epoch($sc_date_cut_sql, false); # cut-off date
	$now_0 = date_to_epoch($sc_date_cut_sql, false); # first day of cut-off date's month
	$now_1 = date_add_months_kdb($now_0, -1); # first day of last month (relative to cut-off date)
	$now_2 = date_add_months_kdb($now_0, -2); # first day of two months ago (relative to cut-off date)
	$now_3 = date_add_months_kdb($now_0, -3); # first day of three months ago (relative to cut-off date)

	$txt_0 = "During Current Month";
	$txt_1 = "During " . strftime_rdr("%B %Y", $now_1);
	$txt_2 = "During " . strftime_rdr("%B %Y", $now_2);
	$txt_3 = "During " . strftime_rdr("%B %Y", $now_3);
	$txt_4 = "Before " . strftime_rdr("%B %Y", $now_3);

	$summary = array('DUE_0' => 0.0, 'DUE_1' => 0.0, 'DUE_2' => 0.0, 'DUE_3' => 0.0, 'DUE_4' => 0.0, 'TOTAL' => 0.0);

	$datalines = array();

	sql_encryption_preparation('JOB');
	sql_encryption_preparation('JOB_SUBJECT');
	foreach ($invoices as $invoice_id => $one)
	{
		$unpaid = floatval($one['INV_GROSS']) - floatval($one['INV_PAID']);
		#if ($one['INV_TYPE'] == 'C')
		#	$unpaid = 0 - $unpaid;

		if ($one['INV_DUE_DT'])
			$due_ep = date_to_epoch($one['INV_DUE_DT'], false);
		else
			$due_ep = date_to_epoch($one['INV_DT'], false, 30);
		if ($now_0 <= $due_ep)
		{
			#if ($due_ep < $today)
				$summary['DUE_0'] += $unpaid;
		}
		elseif ($now_1 <= $due_ep)
			$summary['DUE_1'] += $unpaid;
		elseif ($now_2 <= $due_ep)
			$summary['DUE_2'] += $unpaid;
		elseif ($now_3 <= $due_ep)
			$summary['DUE_3'] += $unpaid;
		else
			$summary['DUE_4'] += $unpaid;
		$summary['TOTAL'] += $unpaid;

		if ($debug) dprint("Invoice {$one['INV_NUM']}/{$one['INV_SYS']}: unpaid=$unpaid");

		if ($one['INV_SYS'] == 'T')
		{
			# Trace Invoice
			$sql = "SELECT J.JOB_ID, " . sql_decrypt('J.CLIENT_REF', '', true) . ", " . sql_decrypt('S.JS_FIRSTNAME', '', true) . ",
						" . sql_decrypt('S.JS_LASTNAME', '', true) . ", " . sql_decrypt('S.JS_COMPANY', '', true) . ", J.J_VILNO
					FROM INV_BILLING AS B
					LEFT JOIN JOB AS J ON J.JOB_ID=B.JOB_ID
					LEFT JOIN JOB_SUBJECT AS S ON S.JOB_ID=J.JOB_ID AND S.JS_PRIMARY=$sqlTrue
					WHERE B.INVOICE_ID=$invoice_id";
			sql_execute($sql);
			$job_id = 0;
			$client_ref = '(job not found)';
			$subject_name = '';
			$vilno = 0;
			while (($newArray = sql_fetch_assoc()) != false)
			{
				if ($job_id == 0)
				{
					if (0 < $newArray['JOB_ID'])
					{
						$job_id = $newArray['JOB_ID'];
						$vilno = $newArray['J_VILNO'];
						$client_ref = $newArray['CLIENT_REF'];
						$subject_name = $newArray['JS_COMPANY'];
						if (!$subject_name)
							$subject_name = $newArray['JS_FIRSTNAME'] . ' ' . $newArray['JS_LASTNAME'];
					}
				}
			}
			$invoices[$invoice_id]['CLIENT_REF'] = $client_ref;
			$invoices[$invoice_id]['VILNO'] = $vilno;
			$invoices[$invoice_id]['SUBJECT_NAME'] = $subject_name;
		} # T

		elseif ($one['INV_SYS'] == 'C')
		{
			# Collect Invoice
			$sql = "SELECT COUNT(*) FROM JOB_PAYMENT WHERE INVOICE_ID=$invoice_id AND (OBSOLETE=$sqlFalse)";
			sql_execute($sql);
			$count = 0;
			while (($newArray = sql_fetch()) != false)
				$count = $newArray[0];
			$invoices[$invoice_id]['CLIENT_REF'] = 'Collection Invoice';
			$invoices[$invoice_id]['VILNO'] = '';
			$invoices[$invoice_id]['SUBJECT_NAME'] = "{$count} payment(s)";
		} # C

		elseif ($one['INV_SYS'] == 'G')
		{
			# General Invoice
			$sql = "SELECT BL_DESCR FROM INV_BILLING WHERE INVOICE_ID=$invoice_id";
			sql_execute($sql);
			$descr = '';
			while (($newArray = sql_fetch()) != false)
			{
				if ($newArray[0])
				{
					if ($descr)
						$descr .= ' ';
					$descr .= $newArray[0];
				}
			}
			$invoices[$invoice_id]['CLIENT_REF'] = 'General Invoice';
			$invoices[$invoice_id]['VILNO'] = '';
			$invoices[$invoice_id]['SUBJECT_NAME'] = $descr;
		} # G
		else
		{
			$invoices[$invoice_id]['CLIENT_REF'] = '*ERROR*';
			$invoices[$invoice_id]['VILNO'] = '*ERROR*';
			$invoices[$invoice_id]['SUBJECT_NAME'] = '*ERROR*';
		}
	}

	foreach ($receipts as $inv_recp_id => $one)
	{
		$surplus = floatval($one['UNALLOC']);
		$issued_ep = date_to_epoch($one['RC_DT'], false);
		if ($now_0 <= $issued_ep)
		{
			#if ($issued_ep < $today)
				$summary['DUE_0'] -= $surplus;
		}
		elseif ($now_1 <= $issued_ep)
			$summary['DUE_1'] -= $surplus;
		elseif ($now_2 <= $issued_ep)
			$summary['DUE_2'] -= $surplus;
		elseif ($now_3 <= $issued_ep)
			$summary['DUE_3'] -= $surplus;
		else
			$summary['DUE_4'] -= $surplus;
		$summary['TOTAL'] -= $surplus;

		if ($debug) dprint("Receipt ID $inv_recp_id: surplus=$surplus");
	}

	#dprint("Invoices (" . count($invoices) . ") for client ID $client2_id: " . print_r($invoices,1));#

	# We need to merge $invoices and $receipts into $inv_recps
	$inv_ii = 0;
	$inv_max = count($invoices) - 1;
	$recp_ii = 0;
	$recp_max = count($receipts) - 1;
	$inv_recps = array();
	for ($ii = 0; $ii < 10000; $ii++) # to prevent infinite looping
	{
		$do_type = '';
		if ($recp_ii <= $recp_max)
		{
			if ($inv_ii <= $inv_max)
			{
				if ($invoices[$invoice_id_list[$inv_ii]]['INV_DT'] <= $receipts[$receipt_id_list[$recp_ii]]['RC_DT'])
					$do_type = 'i';
				else
					$do_type = 'r';
			}
			else
				$do_type = 'r';
		}
		else
		{
			if ($inv_ii <= $inv_max)
				$do_type = 'i';
		}
		#if (($recp_max < $recp_ii) ||
		#	($invoices[$invoice_id_list[$inv_ii]]['INV_DT'] <= $receipts[$receipt_id_list[$recp_ii]]['RC_DT']))
		if ($do_type == 'i')
		{
			$inv_recps[] = $invoices[$invoice_id_list[$inv_ii]];
			$inv_ii++;
		}
		elseif ($do_type == 'r')
		{
			$inv_recps[] = array('INV_SYS' => '',
									'INV_DT' => $receipts[$receipt_id_list[$recp_ii]]['RC_DT'],
									'INV_DUE_DT' => '',
									'INV_TYPE' => 'R',
									'INV_NUM' => $receipts[$receipt_id_list[$recp_ii]]['RC_NUM'],
									'INV_GROSS' => $receipts[$receipt_id_list[$recp_ii]]['SUM_AL_AMOUNT'],
									'INV_PAID' => $receipts[$receipt_id_list[$recp_ii]]['RC_AMOUNT']
								);
			$recp_ii++;
		}
		if (($inv_max < $inv_ii) && ($recp_max < $recp_ii))
			break;
	} # for ($ii)
	if ($debug) dprint("Combined: " . print_r($inv_recps,1));



	sql_encryption_preparation('CLIENT2');
	$sql = "SELECT C_INDIVIDUAL, C_CODE, " . sql_decrypt('C_CO_NAME', '', true) . ",
			" . sql_decrypt('C_ADDR_1', '', true) . ", " . sql_decrypt('C_ADDR_2', '', true) . ", " . sql_decrypt('C_ADDR_3', '', true) . ",
			" . sql_decrypt('C_ADDR_4', '', true) . ", " . sql_decrypt('C_ADDR_5', '', true) . ", " . sql_decrypt('C_ADDR_PC', '', true) . "
			FROM CLIENT2 WHERE CLIENT2_ID=$client2_id";
	sql_execute($sql);
	while (($newArray = sql_fetch_assoc()) != false)
	{
		$c_individual = (($newArray['C_INDIVIDUAL'] == 1) ? true : false);
		$client_code = $newArray['C_CODE'];
		$client_name = $newArray['C_CO_NAME'];
		$client_addr = array();
		if ($newArray['C_ADDR_1'])
			$client_addr[] = $newArray['C_ADDR_1'];
		if ($newArray['C_ADDR_2'])
			$client_addr[] = $newArray['C_ADDR_2'];
		if ($newArray['C_ADDR_3'])
			$client_addr[] = $newArray['C_ADDR_3'];
		if ($newArray['C_ADDR_4'])
			$client_addr[] = $newArray['C_ADDR_4'];
		if ($newArray['C_ADDR_5'])
			$client_addr[] = $newArray['C_ADDR_5'];
		if ($newArray['C_ADDR_PC'])
			$client_addr[] = $newArray['C_ADDR_PC'];
	}
	if ($c_individual)
	{
		$client_contact = $client_name;
		$client_name = '';
	}
	else
	{
		sql_encryption_preparation('CLIENT_CONTACT');
		$sql = "SELECT " . sql_decrypt('CC_FIRSTNAME', '', true) . ", " . sql_decrypt('CC_LASTNAME', '', true) . "
				FROM CLIENT_CONTACT WHERE CLIENT2_ID=$client2_id AND CC_MAIN=$sqlTrue";
		sql_execute($sql);
		$client_contact = '(No name)';
		while (($newArray = sql_fetch_assoc()) != false)
			$client_contact = $newArray['CC_FIRSTNAME'] . ' ' . $newArray['CC_LASTNAME'];
	}
	$client_lines = array(	'Statement of Account',
							$sc_date_cut . "&nbsp;&nbsp;&nbsp;Client No. $client_code",
							'&nbsp;',
							"Attn: $client_contact"
						 );
	if ($client_name)
		$client_lines[] = $client_name;
	$ii_max = count($client_addr);
	for ($ii = 0; $ii < $ii_max; $ii++)
	{
		if ($client_addr[$ii])
			$client_lines[] = $client_addr[$ii];
	}


	$border_all = "style=\"border: solid black 1px;\"";
	#$border_b = "style=\"border-bottom: solid black 1px;\"";
	$border_r = "style=\"border-right: solid black 1px;\"";
	$border_t = "style=\"border-top: solid black 1px;\"";
	$border_tr = "style=\"border-top: solid black 1px; border-right: solid black 1px;\"";
	#$border_br = "style=\"border-bottom: solid black 1px; border-right: solid black 1px;\"";
	$border_bt = "style=\"border-bottom: solid black 1px; border-top: solid black 1px;\"";
	$border_brt = "style=\"border-bottom: solid black 1px; border-right: solid black 1px; border-top: solid black 1px;\"";
	#$border_br_dot = "style=\"border-bottom: dotted black 1px; border-right: solid black 1px;\"";
	#$border_b_dot = "style=\"border-bottom: dotted black 1px;\"";
	#$border_t_dot = "style=\"border-top: dotted black 1px;\"";
	#$border_tr_dot = "style=\"border-top: dotted black 1px; border-right: solid black 1px;\"";

	$html = "
	<table name=\"table_statement\" class=\"basic_table\" cellspacing=\"0\" cellpadding=\"0\" $border_all border=\"0\"><!---->
	";
	foreach ($client_lines as $txt)
	{
		$html .= "
		<tr>
			<td>&nbsp;</td>
			<td $col22>$txt</td>
			<td>&nbsp;</td>
		</tr>
		";
	}
	$html .= "
	<tr>
		<td>&nbsp;</td>
		<td $col22>&nbsp;</td>
		<td>&nbsp;</td>
	</tr>
	";

	$html .= "
	<tr>
		<td>&nbsp;</td>				<td width=\"6\">&nbsp;</td>			<td $border_r>&nbsp;</td>
		<td $border_bt>&nbsp;</td>	<td $border_bt>{$headings[0]}</td>	<td $border_brt>&nbsp;</td>
		<td $border_bt>&nbsp;</td>	<td $border_bt>{$headings[1]}</td>	<td $border_brt>&nbsp;</td>
		<td $border_bt>&nbsp;</td>	<td $border_bt>{$headings[2]}</td>	<td $border_brt>&nbsp;</td>
		<td $border_bt>&nbsp;</td>	<td $border_bt>{$headings[3]}</td>	<td $border_brt>&nbsp;</td>
		<td $border_bt>&nbsp;</td>	<td $border_bt>{$headings[4]}</td>	<td $border_brt>&nbsp;</td>
		<td $border_bt>&nbsp;</td>	<td $border_bt>{$headings[5]}</td>	<td $border_brt>&nbsp;</td>
		<td>&nbsp;</td>				<td width=\"6\">&nbsp;</td>			<td >&nbsp;</td>
	</tr>
	";

	$balance_total = 0.0;
	$ix = 0;
	foreach ($inv_recps as $one)
	{
		$date = date_for_sql($one['INV_DT'], true, false);
		$type = (($one['INV_TYPE'] == 'I') ? 'Invoice' : (($one['INV_TYPE'] == 'C') ? 'Credit' : (($one['INV_TYPE'] == 'R') ? 'Receipt' : '*ERROR*')));
		$ref = $one['INV_NUM'];

		$gross = floatval($one['INV_GROSS']);
		$paid = floatval($one['INV_PAID']);
		$debit = ((($one['INV_TYPE'] == 'I') || ($one['INV_TYPE'] == 'R')) ? $gross : (($one['INV_TYPE'] == 'C') ? '' : '*ERROR*'));
		$credit = (($one['INV_TYPE'] == 'I') ? '' : (($one['INV_TYPE'] == 'C') ? $gross : (($one['INV_TYPE'] == 'R') ? $paid : '*ERROR*')));
		#$balance = ((($one['INV_TYPE'] == 'I') || ($one['INV_TYPE'] == 'R')) ? ($gross - $paid) : (($one['INV_TYPE'] == 'C') ? (0 - $gross) : '*ERROR*'));
		$balance = ((($one['INV_TYPE'] == 'I') || ($one['INV_TYPE'] == 'R')) ? ($gross - $paid) : (($one['INV_TYPE'] == 'C') ? ($gross) : '*ERROR*'));
		$balance_total += $balance;

		$debit_txt = money_format_kdb($debit, true, true, true);
		#$credit_txt = money_format_kdb($credit, true, true, true);
		if ($credit < 0)
			$credit_txt = "(" . money_format_kdb(0 - $credit, true, true, true) . ")";
		else
			$credit_txt = money_format_kdb($credit, true, true, true);
		if ($balance < 0)
			$balance_txt = "(" . money_format_kdb(0 - $balance, true, true, true) . ")";
		else
			$balance_txt = money_format_kdb($balance, true, true, true);

		if ($one['INV_TYPE'] == 'I')
		{
			if ($one['INV_SYS'] == 'T')
				$your_ref = "(Your Ref: {$one['CLIENT_REF']}&nbsp;&nbsp;Our Ref: {$one['VILNO']}&nbsp;&nbsp;Name: {$one['SUBJECT_NAME']})";
			else
				$your_ref = "({$one['CLIENT_REF']}, {$one['SUBJECT_NAME']})";
		}
		else
			$your_ref = '';

		$html .= "
		<tr>
			<td>&nbsp;</td>	<td>&nbsp;</td>				<td $border_r>&nbsp;</td>
			<td>&nbsp;</td>	<td $ac>$date</td>			<td $border_r>&nbsp;</td>
			<td>&nbsp;</td>	<td $ac>$type</td>			<td $border_r>&nbsp;</td>
			<td>&nbsp;</td>	<td $ac>$ref</td>			<td $border_r>&nbsp;</td>
			<td>&nbsp;</td>	<td $ar>$debit_txt</td>		<td $border_r>&nbsp;</td>
			<td>&nbsp;</td>	<td $ar>$credit_txt</td>	<td $border_r>&nbsp;</td>
			<td>&nbsp;</td>	<td $ar>$balance_txt</td>	<td $border_r>&nbsp;</td>
			<td>&nbsp;</td>	<td>&nbsp;</td>				<td>&nbsp;</td>
		</tr>
		";
		if ($your_ref)
		{
			$html .= "
			<tr>
				<td>&nbsp;</td>	<td>&nbsp;</td>						<td $border_r>&nbsp;</td>
				<td>&nbsp;</td>	<td colspan=\"16\">$your_ref</td>	<td $border_r>&nbsp;</td>
				<td>&nbsp;</td>	<td>&nbsp;</td>						<td>&nbsp;</td>
			</tr>
				";
		}
		if ($export_xl)
		{
			$datalines[] = array($date, $type, $ref, $debit, $credit, $balance);
			$datalines[] = array("##TXT##" . str_replace('&nbsp;', ' ', $your_ref));
		}

		if (++$ix < count($inv_recps))
		{
			$html .= "
			<tr>
				<td>&nbsp;</td>	<td>&nbsp;</td>	<td $border_r>&nbsp;</td>
				<td>&nbsp;</td>	<td $ar></td>	<td $border_r>&nbsp;</td>
				<td>&nbsp;</td>	<td $ac></td>	<td $border_r>&nbsp;</td>
				<td>&nbsp;</td>	<td $ac></td>	<td $border_r>&nbsp;</td>
				<td>&nbsp;</td>	<td $ar></td>	<td $border_r>&nbsp;</td>
				<td>&nbsp;</td>	<td $ar></td>	<td $border_r>&nbsp;</td>
				<td>&nbsp;</td>	<td $ar></td>	<td $border_r>&nbsp;</td>
				<td>&nbsp;</td>	<td>&nbsp;</td>	<td>&nbsp;</td>
			</tr>
			";
//			$html .= "
//			<tr>
//				<td $border_t_dot>&nbsp;</td>	<td $border_t_dot $ar></td>	<td $border_tr_dot>&nbsp;</td>
//				<td $border_t_dot>&nbsp;</td>	<td $border_t_dot $ac></td>	<td $border_tr_dot>&nbsp;</td>
//				<td $border_t_dot>&nbsp;</td>	<td $border_t_dot $ac></td>	<td $border_tr_dot>&nbsp;</td>
//				<td $border_t_dot>&nbsp;</td>	<td $border_t_dot $ar></td>	<td $border_tr_dot>&nbsp;</td>
//				<td $border_t_dot>&nbsp;</td>	<td $border_t_dot $ar></td>	<td $border_tr_dot>&nbsp;</td>
//				<td $border_t_dot>&nbsp;</td>	<td $border_t_dot $ar></td>	<td $border_t_dot>&nbsp;</td>
//			</tr>
//			";
		}
		if ($export_xl)
			$datalines[] = array();

	} # foreach ($inv_recps)

	if ($balance_total < 0)
		$balance_total_txt = "(" . money_format_kdb(0 - $balance_total, true, true, true) . ")";
	else
		$balance_total_txt = money_format_kdb($balance_total, true, true, true);
	$html .= "
	<tr>
		<td>&nbsp;</td>				<td>&nbsp;</td>										<td $border_r>&nbsp;</td>
		<td $border_t>&nbsp;</td>	<td $border_t $ar colspan=\"13\">Total Balance</td>	<td $border_tr>&nbsp;</td>
		<td $border_t>&nbsp;</td>	<td $border_t $ar>$balance_total_txt</td>			<td $border_tr>&nbsp;</td>
		<td>&nbsp;</td>				<td>&nbsp;</td>										<td>&nbsp;</td>
	</tr>
	<tr>
		<td>&nbsp;</td>				<td>&nbsp;</td>							<td>&nbsp;</td>
		<td $border_t>&nbsp;</td>	<td $border_t $ar colspan=\"13\"></td>	<td $border_t>&nbsp;</td>
		<td $border_t>&nbsp;</td>	<td $border_t $ar></td>					<td $border_t>&nbsp;</td>
		<td>&nbsp;</td>				<td>&nbsp;</td>							<td>&nbsp;</td>
	</tr>
	";
	if ($export_xl)
	{
		$datalines[] = array('', '', '', 'Total Balance', '', $balance_total);
		$datalines[] = array('', '', '', '');
	}

	$html .= "
	<tr>
		<td>&nbsp;</td>	<td $col22>Summary</td>	<td>&nbsp;</td>
	</tr>
	";
	if ($export_xl)
		$datalines[] = array('##BOLD##Summary', '', '', '', '', '');

	$due_4 = money_format_kdb($summary['DUE_4'], true, true, true);
	$html .= "
	<tr>
		<td>&nbsp;</td>				<td>&nbsp;</td>						<td $border_r>&nbsp;</td>
		<td $border_t>&nbsp;</td>	<td $col13 $border_t>$txt_4</td>		<td $border_t>&nbsp;</td>
		<td $border_t>&nbsp;</td>	<td $ar $border_t>{$due_4}</td>		<td $border_tr>&nbsp;</td>
		<td>&nbsp;</td>				<td>&nbsp;</td>							<td>&nbsp;</td>
	</tr>
	";
	if ($export_xl)
		$datalines[] = array($txt_4, '', '', '', '', $summary['DUE_4']);

	$due_3 = money_format_kdb($summary['DUE_3'], true, true, true);
	$html .= "
	<tr>
		<td>&nbsp;</td>	<td>&nbsp;</td>			<td $border_r>&nbsp;</td>
		<td>&nbsp;</td>	<td $col13>$txt_3</td>	<td>&nbsp;</td>
		<td>&nbsp;</td>	<td $ar>{$due_3}</td>	<td $border_r>&nbsp;</td>
		<td>&nbsp;</td>				<td>&nbsp;</td>							<td>&nbsp;</td>
	</tr>
	";
	if ($export_xl)
		$datalines[] = array($txt_3, '', '', '', '', $summary['DUE_3']);

	$due_2 = money_format_kdb($summary['DUE_2'], true, true, true);
	$html .= "
	<tr>
		<td>&nbsp;</td>	<td>&nbsp;</td>			<td $border_r>&nbsp;</td>
		<td>&nbsp;</td>	<td $col13>$txt_2</td>	<td>&nbsp;</td>
		<td>&nbsp;</td>	<td $ar>{$due_2}</td>	<td $border_r>&nbsp;</td>
		<td>&nbsp;</td>				<td>&nbsp;</td>							<td>&nbsp;</td>
	</tr>
	";
	if ($export_xl)
		$datalines[] = array($txt_2, '', '', '', '', $summary['DUE_2']);

	$due_1 = money_format_kdb($summary['DUE_1'], true, true, true);
	$html .= "
	<tr>
		<td>&nbsp;</td>	<td>&nbsp;</td>			<td $border_r>&nbsp;</td>
		<td>&nbsp;</td>	<td $col13>$txt_1</td>	<td>&nbsp;</td>
		<td>&nbsp;</td>	<td $ar>{$due_1}</td>	<td $border_r>&nbsp;</td>
		<td>&nbsp;</td>				<td>&nbsp;</td>							<td>&nbsp;</td>
	</tr>
	";
	if ($export_xl)
		$datalines[] = array($txt_1, '', '', '', '', $summary['DUE_1']);

	$due_0 = money_format_kdb($summary['DUE_0'], true, true, true);
	$html .= "
	<tr>
		<td>&nbsp;</td>	<td>&nbsp;</td>			<td $border_r>&nbsp;</td>
		<td>&nbsp;</td>	<td $col13>$txt_0</td>	<td>&nbsp;</td>
		<td>&nbsp;</td>	<td $ar>{$due_0}</td>	<td $border_r>&nbsp;</td>
		<td>&nbsp;</td>				<td>&nbsp;</td>							<td>&nbsp;</td>
	</tr>
	<tr>
		<td>&nbsp;</td>				<td>&nbsp;</td>					<td>&nbsp;</td>
		<td $border_t>&nbsp;</td>	<td $border_t $ar $col16></td>	<td $border_t>&nbsp;</td>
		<td>&nbsp;</td>				<td>&nbsp;</td>					<td>&nbsp;</td>
	</tr>
	";
	if ($export_xl)
	{
		$datalines[] = array($txt_0, '', '', '', '', $summary['DUE_0']);
		$datalines[] = array('', '', '', '', '', '');
	}

	$html .= "
	<tr>
		<td>&nbsp;</td>	<td $col22>Total Due</td>	<td>&nbsp;</td>
	</tr>
	";
	if ($export_xl)
		$datalines[] = array('##BOLD##Total Due', '', '', '');

	$total_due = money_format_kdb($summary['TOTAL'], true, true, true);
	$html .= "
	<tr>
		<td>&nbsp;</td>				<td>&nbsp;</td>						<td $border_r>&nbsp;</td>
		<td $border_t>&nbsp;</td>	<td $col13 $border_t></td>			<td $border_t>&nbsp;</td>
		<td $border_t>&nbsp;</td>	<td $ar $border_t>$total_due</td>	<td $border_tr>&nbsp;</td>
		<td>&nbsp;</td>				<td>&nbsp;</td>							<td>&nbsp;</td>
	</tr>
	<tr>
		<td>&nbsp;</td>				<td>&nbsp;</td>					<td>&nbsp;</td>
		<td $border_t>&nbsp;</td>	<td $border_t $ar $col16></td>	<td $border_t>&nbsp;</td>
		<td>&nbsp;</td>				<td>&nbsp;</td>					<td>&nbsp;</td>
	</tr>
	";
	if ($export_xl)
	{
		$datalines[] = array('', '', '', '', '', $summary['TOTAL']);
		$datalines[] = array('', '', '', '', '', '');
	}

	$html .= "
	</table><!--table_statement-->
	";
	print $html;

	print "<span style=\"color:blue;\">";
	if ($inv_emailed)
	{
		if ($e_address)
			print "Client is to be emailed: $e_address ($e_name)";
		else
			print "<span style=\"color:red;\">*** Client is to be emailed but there is no email address!! ***</span>";
	}
	else
		print "Client is NOT to be emailed";
	print "</span><br>
		";

	if ($send_emails && $inv_emailed && $e_address)
	{
		# Create PDF of the statement and save to server disk.
		$pdf_filename = sprintf("STATEMENT_%04d_%.5s.pdf", $c_code, filename_from_freetext($client_name));
		$pdf_dir = check_dir("{$csv_dir}/c{$c_code}");
		$pdf_error = pdf_create($pdf_dir, $pdf_filename, $html);
		if (!$pdf_error)
		{
			$mailto = $e_address;
			if (email_valid($mailto))
			{
				$subject = "Vilcol Statement";
				$message = "Dear $e_name," . "<br>$crlf" .
					"Please find attached your new statement from Vilcol." . "<br>$crlf" .
					"Yours," . "<br>$crlf" .
					"Vilcol Ltd.";
				$attachment_file = "{$pdf_dir}/{$pdf_filename}";
				$attachment_name = 'Client Statement.pdf';
				if (mail_pm($mailto, $subject, $message, $email_accounts, $emailName_accounts, $attachment_file, $attachment_name,
								'', '', '', false))
				{
					$error = '';
					print "<span style=\"color:blue;\">Statement has been emailed to client ($mailto)</span>";
				}
				else
				{
					$error = "Failed to email PDF to client ($mailto)";
					print "<span style=\"color:red;\">*** $error ***</span>";
				}
			}
			else
			{
				$error = "No (or bad) email address for client (ID $client2_id): \"$mailto\"";
				print "<span style=\"color:red;\">*** $error ***</span>";
			}
			$pdf_messages[$client2_id] = $error;
		}
		else
			$pdf_messages[$client2_id] = "PDF Creation failed. Error: $pdf_error";
	} # if ($send_emails)

	print "<br><br><br>
		";

	#$title_x = str_replace('&nbsp;', '', $title);

	#$top_lines = array(array($title_x), array());
	$top_lines = array();
	$prefix = '##BOLD##';
	foreach ($client_lines as $one)
	{
		$tt = array();
		$bits = explode('&nbsp;', $one);
		foreach ($bits as $bb)
			$tt[] = $prefix . trim($bb);
		$top_lines[] = $tt;
		$prefix = '';
	}
	$top_lines[] = array('');

	#dprint("top=" . print_r($top_lines,1));#
	$excel_sheets[] = array("Client $c_code", $top_lines, $headings, $datalines, $formats, '');

} # view_statements_client()

?>
