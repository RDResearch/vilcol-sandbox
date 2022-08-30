<?php

include_once("settings.php");
include_once("library.php");

global $denial_message;
global $navi_1_clients;
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
		$navi_1_clients = true; # settings.php; used by navi_1_heading()
		$onload = "onload=\"set_scroll();\"";
		$page_title_2 = 'Clients - Vilcol';
		screen_layout();
	}
	else 
		print "<p>$denial_message</p>";
}
else 
	print "<p>" . server_php_self() . ": login is not enabled</p>";
	
sql_disconnect();
log_close();

function screen_content()
{
	global $agent_c;
	global $agent_t;
	global $anchor;
	global $ar;
	global $can_edit;
	global $client2_id;
	global $client_groups; # from sql_get_client_groups()
	global $col2;
	global $col3;
	global $col4;
	global $export;
	global $manager_a;
	global $manager_x;
	global $role_agt;
	global $role_man;
	global $sc_addr;
	global $sc_alpha;
	global $sc_archived;
	global $sc_bacs;
	global $sc_bank;
	global $sc_contact;
	global $sc_fstmt;
	global $sc_group;
	global $sc_open;
	global $sc_stmtinv;
	global $sc_system;
	global $sc_text;
	global $sc_uninvbill;
	global $sc_uninvpay;
	global $sc_vat;
	global $steve_user_id;
	global $task;
	global $ticked_clients;
	#global $time_tests;
	global $tr_colour_1;
	global $USER;
	
	#$time_tests = true;#
	
	dprint(post_values());
	
	$task = post_val('task');
	$client2_id = post_val('client2_id', true);
	if (($task == 'edit') && ($client2_id == 0))
		$client2_id = create_new_client();
	
	$manager_t = role_check('t', $role_man);
	$manager_c = role_check('c', $role_man);
	$manager_a = role_check('a', $role_man);
	$manager_x = ($manager_t || $manager_c || $manager_a);
	$can_edit = $manager_x;
	$agent_c = role_check('c', $role_agt);
	$agent_t = role_check('t', $role_agt);
	#dprint("can_edit=$can_edit",true);#
	$new_job_id = 0;
	$ticked_clients = explode(',', post_val('ticked_clients_main'));
	$export = post_val('export');
	$anchor = '';
	
	print "<h3>Clients Screen</h3>";
	
	print "
	<div id=\"div_wait\">
		<p style=\"color:blue\">Please wait...</p>
	</div><!--div_wait-->
	<div id=\"div_form\" style=\"display:none;\">
	";

	if (post_val('search_clicked'))
		$task = "search";
	else
	{
		if ($task == 'create_new_group')
		{
			create_new_group();
			$task = 'edit';
		}
		elseif ($task == 'archive_client')
		{
			sql_archive_client($client2_id);
			$task = 'edit';
		}
		elseif ($task == 'unarchive_client')
		{
			sql_unarchive_client($client2_id);
			$task = 'edit';
		}
		elseif ($task == 'add_contact')
		{
			contact_add();
			$task = 'edit';
		}
		elseif ($task == 'add_note')
		{
			note_add();
			$task = 'edit';
			$anchor = 'notes_top';
		}
		elseif ($task == 'add_phone')
		{
			phone_add();
			$task = 'edit';
		}
		elseif ($task == 'res_phone')
		{
			phone_restore();
			$task = 'edit';
		}
		elseif ($task == 'del_phone')
		{
			phone_delete();
			$task = 'edit';
		}
		elseif ($task == 'add_job')
		{
			$new_job_id = sql_add_job($client2_id, post_val('temp_text_1'));
		}
		elseif ($task == 'add_report')
		{
			$new_job_id = sql_add_client_report($client2_id, post_val('temp_text_1'), post_val('temp_text_2'));
			$anchor = 'reports_top';
			$task = 'edit';
		}
		elseif ($task == 'pre_statements_t')
		{
			$ticked_clients = explode(',', str_replace('*','',post_val('sc_text')));
			$task = 'search';
			dprint("Please use the \"Create Trace Statements\" button to create statement inovoices for the clients", true);
		}
		elseif ($task == 'pre_statements_c')
		{
			$ticked_clients = explode(',', str_replace('*','',post_val('sc_text')));
			$task = 'search';
			dprint("Please use the \"Create Collect Statements\" button to create statement invoices for the clients", true);
		}
		elseif ($task == 'create_statements')
		{
			$cs_sys = post_val('temp_text_1');
			if (($cs_sys == 't') || ($cs_sys == 'c'))
				create_statements($cs_sys);
			$task = 'search';
		}
		elseif ($task == 'bulk_save_comm')
		{
			$error = sql_bulk_save_commission(post_val('temp_text_1'), '', post_val('ticked_clients_main'));
			if ($error)
				dprint("Set Commission failed: \"$error\"", true);
			else
				dprint("The commission rate has been set for the ticked clients", true, 'blue');
			$task = 'search';
		}
	}
	
	$sc_addr = post_val('sc_addr', false, false, false, 1);
	$sc_bank = post_val('sc_bank');
	$sc_bacs = post_val('sc_bacs', true);
	$sc_vat = post_val('sc_vat', true);
	$sc_alpha = post_val('sc_alpha');
	$sc_contact = post_val('sc_contact', false, false, false, 1);
	$sc_text = post_val('sc_text', false, false, false, 1);

	$sc_system = post_val('sc_system');
	if (($sc_system != '') && ($sc_system != 'c') && ($sc_system != 't'))
		$sc_system = '';
	$sys_list = array();
	if ($agent_t)
		$sys_list['t'] = 'Trace';
	if ($agent_c)
		$sys_list['c'] = 'Collect';
	
	$sc_stmtinv = post_val('sc_stmtinv', true);
	sql_get_client_groups(true); # writes to $client_groups
	$sc_group = post_val('sc_group', true);
	$sc_open = post_val('sc_open', true);
	$sc_archived = post_val('sc_archived', true);
	$sc_fstmt = post_val('sc_fstmt', true);
	if (!$sc_fstmt)
		$sc_fstmt = '';
	$sc_uninvbill = post_val('sc_uninvbill', true);
	$sc_uninvpay = post_val('sc_uninvpay', true);

	javascript();
	
	if ($task == 'add_job')
	{
		if (0 < $new_job_id)
		{
			print "
				<form name=\"form_new_job\" action=\"jobs.php\" method=\"post\">
					" . input_hidden('task', '') . "
					" . input_hidden('job_id', '') . "
				</form><!--form_new_job-->
				<script type=\"text/javascript\">
				redirect_to_job($new_job_id);
				</script>
				";
		}
	}
	
	$gap = "<td width=15\">&nbsp;</td>";
	$btnw = "style=\"width:140px;\"";
	
	print "
	<div id=\"div_form_main\" style=\"background-color:{$tr_colour_1};\">
	<hr>
	<form name=\"form_main\" action=\"" . server_php_self() . "\" method=\"post\">
	" . input_hidden('task', '') . "
	" . input_hidden('client2_id', '') . "
	" . input_hidden('temp_text_1', '') . "
	" . input_hidden('temp_text_2', '') . "
	" . input_hidden('show_obs_contact', '') . "
	" . input_hidden('show_obs_phone', '') . "
	" . input_hidden('ticked_clients_main', '') . "
	" . input_hidden('export', '') . "
	";
	# The following line causes form submission when ENTER key is pressed.
	print "
	<input type=\"submit\" style=\"display:none\">
	";
	print "
	<table class=\"basic_table\" border=\"0\"><!---->
	<tr>
		<td $col4 $ar>Search for numeric client code or whole/partial client name:</td>
		<td>" . input_textbox('sc_text', $sc_text, 20, 8000, "onkeypress=\"search_prepare()\"") . # onkeydown causes a backspace to delete two characters the first time it is used!
			"</td>
		$gap
		<td>System:</td><td>" . input_select('sc_system', $sys_list, $sc_system, "onchange=\"search_prepare()\"", false, false) . "</td>
		$gap
		<td>" . input_button('Search', 'search_js(1)') . "</td>
		$gap
		<td>...or: " . input_button('Show all clients', 'search_js(0)') . "</td>
		$gap
		<td>" . input_button('Clear', 'clear_form()');
			if (global_debug() || ($USER['USER_ID'] == $steve_user_id))
				print "&nbsp;&nbsp;&nbsp;&nbsp;" . input_button('Client Export', 'client_export()');
			print "
			</td>
	</tr>
	<tr>
		<td $ar>Client Group:</td><td>" . input_select('sc_group', $client_groups, $sc_group, '', false, false) . "</td>
		$gap
		<td $ar>Address:</td><td>" . input_textbox('sc_addr', $sc_addr, 20, 50, "onkeypress=\"search_prepare()\"") . # onkeydown causes a backspace to delete two characters the first time it is used!
			"</td>
		$gap
		<td $ar>Contact:</td><td>" . input_textbox('sc_contact', $sc_contact, 20, 50, "onkeypress=\"search_prepare()\"") . # onkeydown causes a backspace to delete two characters the first time it is used!
			"</td>
		$gap
		<td $col2 $ar>Alpha Code:</td>
		<td>" . input_textbox('sc_alpha', $sc_alpha, 20, 50, "onkeypress=\"search_prepare()\"") . # onkeydown causes a backspace to delete two characters the first time it is used!
			"</td>
		$gap
		<td>" . input_button('Create New Client', "view_js(0,1)", $btnw . ($manager_x ? '' : ' disabled')) . "</td>
	</tr>
	<tr>
		<td $ar>Bank Acc No:</td>
		<td>" . input_textbox('sc_bank', $sc_bank, 20, 50, "onkeypress=\"search_prepare()\"") . # onkeydown causes a backspace to delete two characters the first time it is used!
			"</td>
		$gap
		<td $ar>BACS:</td>
		<td>" . input_select('sc_bacs', array(1 => 'Paid BACS', -1 => 'Not BACS'), $sc_bacs, "onchange=\"search_prepare()\"", false, false) . "</td>
		$gap
		<td $ar>VAT:</td>
		<td>" . input_select('sc_vat', array(1 => 'Pays VAT', -1 => 'No VAT'), $sc_vat, "onchange=\"search_prepare()\"", false, false) . "</td>
		$gap
		<td $col3>" . ($agent_t ? input_tickbox('Un-invoiced trace statement billing', 'sc_uninvbill', 1, $sc_uninvbill) : '') . "</td>
		$gap
		<td $col3>" . input_tickbox('Open Jobs', 'sc_open', 1, $sc_open) . "</td>
	</tr>
	<tr>
		<td $ar>Statement Invoices:</td>
		<td>" . input_select('sc_stmtinv', array(1 => 'Statements', -1 => 'Not Stmts'), 
								$sc_stmtinv, "onchange=\"search_prepare()\"", false, false) . " (Trace)</td>
		$gap
		<td $ar $col4>Statement due within this many days:</td>
		<td>" . input_textbox('sc_fstmt', $sc_fstmt, 4, 10, "onkeypress=\"search_prepare()\"") . "</td>
		$gap
		<td $col3>" . ($agent_c ? input_tickbox('Un-invoiced collection payments', 'sc_uninvpay', 1, $sc_uninvpay) : '') . "</td>
		$gap
		<td $col3>" . input_tickbox('Archived Clients', 'sc_archived', 1, $sc_archived) . "</td>
	</tr>
	</table>
	<hr>
	</form><!--form_main-->
	</div><!--div_form_main-->

	<form name=\"form_csv_download\" action=\"csv_dl.php\" method=\"post\" target=\"_blank\" rel=\"noopener\">
		<input type=\"hidden\" name=\"short_fname\" value=\"\" />
		<input type=\"hidden\" name=\"full_fname\" value=\"\" />
	</form><!--form_csv_download-->
	";
	
	if (($task == 'search') || ($task == 'client_export'))
	{
		ini_set('max_execution_time', 60 * 10); # 10 mins
		print_clients();
	}
	elseif (($task == 'view') && (0 < $client2_id))
		print_one_client(false); # View Client
	elseif ($task == 'edit')
	{
		if (0 < $client2_id)
			print_one_client(true); # Edit Client
		else
			dprint("Cannot Edit Client with ID of \"$client2_id\"", true);
	}
	
	print "
	</div><!--div_form-->
	";

} # screen_content()

function screen_content_2()
{
	# This is required by screen_layout()
	# Do things that depend on the main_screen_div being displayed
	
	global $anchor;
	global $page_title_2;
	global $sc_addr;
	global $sc_alpha;
	global $sc_bank;
	global $sc_contact;
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
		elseif ($sc_addr)
			print "
			<script type=\"text/javascript\">
			document.getElementById('sc_addr').focus();
			document.getElementById('sc_addr').setSelectionRange(100, 100); // any number larger than length of content
			</script>
			";
		elseif ($sc_contact)
			print "
			<script type=\"text/javascript\">
			document.getElementById('sc_contact').focus();
			document.getElementById('sc_contact').setSelectionRange(100, 100); // any number larger than length of content
			</script>
			";
		elseif ($sc_bank)
			print "
			<script type=\"text/javascript\">
			document.getElementById('sc_bank').focus();
			document.getElementById('sc_bank').setSelectionRange(100, 100); // any number larger than length of content
			</script>
			";
		elseif ($sc_alpha)
			print "
			<script type=\"text/javascript\">
			document.getElementById('sc_alpha').focus();
			document.getElementById('sc_alpha').setSelectionRange(100, 100); // any number larger than length of content
			</script>
			";
	}
	print "
	<script type=\"text/javascript\">
	document.getElementById('div_wait').style.display = 'none';
	document.getElementById('div_form').style.display = 'block';
	document.getElementById('page_title').innerHTML = '$page_title_2';
	</script>
	";

	if ($anchor != '')
	{
		print "
		<script type=\"text/javascript\">
		window.location.href = '#{$anchor}';
		</script>
		";
	}
	
} # screen_content_2()

function javascript()
{
	global $client2_id;
	global $safe_amp;
	global $safe_slash;
	global $uni_pound;
	
	print "
	<script type=\"text/javascript\">
	
	function client_export()
	{
		clear_form();
		document.form_main.export.value = 'xl';
		search_js(1,'client_export');
	}
	
	function report_new_save()
	{
		var abort = false;
		var new_name = trim(document.getElementById('report_new_name').value);
		if (new_name)
		{
			var new_dt = trim(document.getElementById('report_new_dt').value);
			if (new_dt)
			{
				if (checkDate(new_dt, 'of report', ''))
				{
					document.form_main.client2_id.value = $client2_id;
					document.form_main.task.value = 'add_report';
					document.form_main.temp_text_1.value = new_name;
					document.form_main.temp_text_2.value = new_dt;
					please_wait_on_submit();
					document.form_main.submit();
				}
				else
					abort = true;
			}
			else
			{
				alert('Please enter a report date');
				abort = true;
			}
		}
		else
		{
			alert('Please enter a report name');
			abort = true;
		}
		if (abort)
			return false;
	}
	
	function bulk_set_commission()
	{
		var el = document.getElementById('comm_span');
		if (el)
			el.innerHTML = '<input type=\"text\" id=\"comm_box\" name=\"comm_box\"></input>%<br><input type=\"button\" value=\"Save Commission\" onclick=\"bulk_save_comm()\"><input type=\"button\" value=\"Cancel\" onclick=\"bulk_cancel_comm()\">';
		else
			alert('Cannot find element comm_span');
	}
	
	function bulk_save_comm()
	{
		var txt = trim(document.getElementById('comm_box').value.replace('%',''));
		if (txt != '')
		{
			if (isNumeric(txt,true,true,false,false))
			{
				var inputs = document.getElementsByTagName('input');
				var cid = '';
				var ticked = [];
				var tix = 0;
				for (var i = 0; i < inputs.length; i++)
				{
					if (inputs[i].type == 'checkbox')
					{
						if (inputs[i].name.substring(0,4) == 'tck_')
						{
							cid = inputs[i].name.replace('tck_','');
							if (isNumeric(cid,false,false,false,false))
							{
								if (inputs[i].checked)
									ticked[tix++] = cid;
							}
						}
					}
				}
				if (0 < ticked.length)
				{
					document.form_main.task.value = 'bulk_save_comm';
					document.form_main.client2_id.value = $client2_id;
					document.form_main.ticked_clients_main.value = ticked.toString();
					document.form_main.temp_text_1.value = txt;
					please_wait_on_submit();
					document.form_main.submit();
				}
				else
					alert('No clients are ticked, so cannot set commission for ticked clients');
			}
			else
				alert('The commission percentage should be numeric');
		}
		else
			alert('Please enter a commission percentage');
	}
	
	function bulk_cancel_comm()
	{
		var el = document.getElementById('comm_span');
		el.innerHTML = '';
	}
	
	function drop_xl_button()
	{
		var but_xl = document.getElementById('but_export_xl');
		if (but_xl)
			but_xl.style.visibility = 'hidden';
		search_prepare(0);
	}
	
	function export_xl()
	{
		document.form_main.export.value = 'xl';
		search_js(1);
	}
	
	function create_stmts(sys)
	{
		var inputs = document.getElementsByTagName('input');
		var cid = '';
		var ticked = [];
		var tix = 0;
		for (var i = 0; i < inputs.length; i++)
		{
			if (inputs[i].type == 'checkbox')
			{
				if (inputs[i].name.substring(0,4) == 'tck_')
				{
					cid = inputs[i].name.replace('tck_','');
					if (isNumeric(cid,false,false,false,false))
					{
						if (inputs[i].checked)
							ticked[tix++] = cid;
					}
				}
			}
		}
		if (0 < ticked.length)
		{
			if (confirm('Do you really want to create Statement Invoices for the ticked clients?'))
			{
				document.form_main.task.value = 'create_statements';
				document.form_main.temp_text_1.value = sys; // not form_main.sc_system
				document.form_main.ticked_clients_main.value = ticked.toString();
				please_wait_on_submit();
				document.form_main.submit();
			}
		}
		else
			alert('No clients are ticked, so cannot create statements for ticked clients');
	}

	function tick_all()
	{
		var inputs = document.getElementsByTagName('input');
		var cid = '';
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
						cid = inputs[i].name.replace('tck_','');
						if (isNumeric(cid,false,false,false,false))
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
	
	function csv_bulk_upload_jobs(c2id)
	{
		document.form_bulk.client2_id.value = c2id;
		document.form_bulk.submit();
	}
	
	function redirect_to_job(new_job_id)
	{
		document.form_new_job.task.value = 'edit';
		document.form_new_job.job_id.value = new_job_id;
		document.form_new_job.submit();
	}
	
	function create_job(tc)
	{
		var tctxt = ((tc == 't') ? 'Trace' : 'Collection');
		if (confirm('Do you really want to create a new ' + tctxt + ' Job for this client?'))
		{
			document.form_main.client2_id.value = $client2_id;
			document.form_main.task.value = 'add_job';
			document.form_main.temp_text_1.value = tc;
			please_wait_on_submit();
			document.form_main.submit();
		}
	}

	function update_letter_type(control, ltid, yn)
	{
		update_client(control,'t');
		var name = 'ltrtype_' + ltid + '_y';
		var elTckSel = document.getElementById(name);
		name = 'ltrtype_' + ltid + '_n';
		elTckNot = document.getElementById(name);
		name = 'div_lt_' + ltid + '_y';
		var elSpanSel = document.getElementById(name);
		name = 'div_lt_' + ltid + '_n';
		var elSpanNot = document.getElementById(name);
		if (yn == 'y')
		{
			elTckNot.checked = false;
			elSpanSel.style.display = 'none';
			elSpanNot.style.display = 'block';
		}
		else
		{
			elTckSel.checked = true;
			elSpanSel.style.display = 'block';
			elSpanNot.style.display = 'none';
		}
	}
		
	function add_note()
	{
		document.form_main.client2_id.value = $client2_id;
		document.form_main.task.value = 'add_note';
		please_wait_on_submit();
		document.form_main.submit();
	}
	
	function add_contact()
	{
		document.form_main.client2_id.value = $client2_id;
		document.form_main.task.value = 'add_contact';
		please_wait_on_submit();
		document.form_main.submit();
	}
	
	function show_obsolete_contacts()
	{
		document.form_main.client2_id.value = $client2_id;
		document.form_main.task.value = 'edit';
		document.form_main.show_obs_contact.value = 1;
		please_wait_on_submit();
		document.form_main.submit();
	}
	
	function show_obsolete_phones()
	{
		document.form_main.client2_id.value = $client2_id;
		document.form_main.task.value = 'edit';
		document.form_main.show_obs_phone.value = 1;
		please_wait_on_submit();
		document.form_main.submit();
	}
	
	function add_phone(ccid)
	{
		document.form_main.client2_id.value = $client2_id;
		document.form_main.task.value = 'add_phone';
		document.form_main.temp_text_1.value = ccid;
		please_wait_on_submit();
		document.form_main.submit();
	}
	
	function res_phone(phid)
	{
		document.form_main.client2_id.value = $client2_id;
		document.form_main.task.value = 'res_phone';
		document.form_main.temp_text_1.value = phid;
		please_wait_on_submit();
		document.form_main.submit();
	}
	
	function del_phone(phid)
	{
		if (confirm('Do you really want to mark this phone number as Obsolete?'))
		{
			document.form_main.client2_id.value = $client2_id;
			document.form_main.task.value = 'del_phone';
			document.form_main.temp_text_1.value = phid;
			please_wait_on_submit();
			document.form_main.submit();
		}
	}
	
	function create_new_group()
	{
		var group_name = prompt('Please enter name of new Client Group to be created now', 'MyGroup');
		//alert(group_name);
		if (group_name && (0 < group_name.length))
		{
			document.form_main.client2_id.value = $client2_id;
			document.form_main.task.value = 'create_new_group';
			document.form_main.temp_text_1.value = group_name;
			please_wait_on_submit();
			document.form_main.submit();
		}
	}
	
	function clear_form()
	{
		document.form_main.sc_addr.value = '';
		document.form_main.sc_alpha.value = '';
		document.form_main.sc_bank.value = '';
		document.form_main.sc_bacs.value = '';
		document.form_main.sc_vat.value = '';
		document.form_main.sc_contact.value = '';
		document.form_main.sc_text.value = '';
		document.form_main.sc_system.value = '';
		document.form_main.sc_stmtinv.value = '';
		document.form_main.sc_group.value = '';
		document.form_main.sc_open.checked = false;
		document.form_main.sc_archived.checked = false;
		document.form_main.sc_fstmt.value = '';
		if (document.form_main.sc_uninvbill)
			document.form_main.sc_uninvbill.checked = false;
		if (document.form_main.sc_uninvpay)
			document.form_main.sc_uninvpay.checked = false;
	}
	
	function goto_client(c2id,ccode)
	{
		document.form_client.client2_id.value = c2id;
		document.form_client.sc_text.value = ccode;
		document.form_client.task.value = 'view';
		document.form_client.submit();
	}
	
	function show_audit(c2id)
	{
		document.form_audit.client2_id.value = c2id;
		document.form_audit.submit();
	}
	
	function archive_client()
	{
		if (confirm('Do you really want to archive this client and all of its jobs?'))
		{
			document.form_main.client2_id.value = $client2_id;
			document.form_main.task.value = 'archive_client';
			please_wait_on_submit();
			document.form_main.submit();
		}
	}
	
	function unarchive_client()
	{
		if (confirm('Do you really want to un-archive this client (but NOT its jobs)?'))
		{
			document.form_main.client2_id.value = $client2_id;
			document.form_main.task.value = 'unarchive_client';
			please_wait_on_submit();
			document.form_main.submit();
		}
	}
	
	function show_jobs(ccode)
	{
		document.form_jobs.sc_client.value = ccode;
		document.form_jobs.task.value = 'search';
		document.form_jobs.submit();
	}
	
	function show_letter_seq()
	{
		document.form_seq.submit();
	}
	
	function show_invoices(ccode)
	{
		document.form_invoices.sc_client.value = ccode;
		document.form_invoices.task.value = 'search';
		document.form_invoices.submit();
	}
	
	function view_js(cid, ed)
	{
		";
		# Arg cid is CLIENT2_ID. If zero then we are creating a new client (and ed==1).
		# Arg ed is edit mode: 0==view, 1=edit.
		print "
		if ((0 < cid) || confirm('Do you really want to create a New client?'))
		{
			document.form_main.task.value = (ed ? 'edit' : 'view');
			document.form_main.client2_id.value = cid;
			please_wait_on_submit();
			document.form_main.submit();
		}
	}
	
	function search_prepare()
	{
		document.form_main.task.value = 'search';
	}
	
	function search_js(useSC,task2)
	{
		if (useSC == 0)
			clear_form();
		//document.getElementById('div_form').style.display = 'none';
		if (task2)
			document.form_main.task.value = task2;
		else
			document.form_main.task.value = 'search';
		//document.getElementById('div_wait').style.display = 'block';
		please_wait_on_submit();
		document.form_main.submit();
	}
	
	function update_target(control, tar_id, field_type)
	{
		var htId = 'ct_fee_ht_' + tar_id; // hidden tick element ID
		var htEl = document.getElementById(htId);
		var warn = '';
		if (htEl)
		{
			if (control.name == 'ct_fee_tck')
			{
				// This is the tickbox being ticked or unticked
				if (control.checked)
					htEl.value = 1;
				else
					htEl.value = 0;
			}
			else if (control.name == 'ct_fee')
			{
				// This is the fee box
				if (htEl.value == 0)
					warn = 'Warning: this fee is not yet selected (ticked)';
			}
		}
		else
			alert('Element \"' + htId + '\" not found');
		update_client(control, field_type, '', 0, 0, 0, tar_id);
		if (warn)
			alert(warn);
	}
	
	function update_note(control, note_id, field_type)
	{
		update_client(control, field_type, '', 0, 0, note_id, 0);
	}
	
	function update_phone(control, phone_id, field_type)
	{
		update_client(control, field_type, '', 0, phone_id, 0, 0);
	}
	
	function update_contact(control, contact_id, field_type)
	{
		update_client(control, field_type, '', contact_id, 0, 0, 0);
	}
	
	function client_lookup()
	{
		xmlHttp3 = GetXmlHttpObject();
		if (xmlHttp3 == null)
			return;
		var c_code = (document.getElementById('c_code').value);
		
		var c_name = (document.getElementById('c_co_name').value);
		c_name = c_name.replace(/'/g, \"\\'\");
		c_name = c_name.replace(/&/g, \"\\u0026\");
		c_name = c_name.replace(/&/g, \"$safe_amp\");
		c_name = c_name.replace(/\//g, \"$safe_slash\");
		c_name = c_name.replace(/\\n/g, \"\\%0A\");
		
		var url = 'clients_ajax.php?op=cli_lkp&i=' + $client2_id + '&c=' + c_code + '&n=' + c_name;
		url = url + '&ran=' + Math.random();
		xmlHttp3.onreadystatechange = stateChanged_client_lookup;
		xmlHttp3.open('GET', url, true);
		xmlHttp3.send(null);
	}

	function stateChanged_client_lookup()
	{
		if (xmlHttp3.readyState == 4)
		{
			var mat = document.getElementById('matches');
			if (mat)
			{
				var resptxt = xprint_noscript(xmlHttp3.responseText);
				if (resptxt)
					mat.innerHTML = 'Matches:&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<input type=\"button\" value=\"x\" onclick=\"clear_matches()\"><br>' + resptxt;
				else
					mat.innerHTML = '';
			}
			else
				alert('Cannot find element \"matches\"');
		}
	}
	
	function clear_matches()
	{
		var mat = document.getElementById('matches');
		if (mat)
			mat.innerHTML = '';
	}
	
	function update_client(control, field_type, date_check, contact_id, phone_id, note_id, tar_id)
	{
		" .
		// field_type (data type):
		//				blank or x = default (text); no extra processing
		//				d = Date, optionally send date_check e.g. '<=' for on or before today
		//				m = money (optional '£')
		//				p = percentage (optional '%')
		//				n = Number (allows negatives and decimals)
		//				t = Tickbox
		//				e = Email address
		// date_check:
		//				see 'd' above
		// contact_id:
		//				blank => updating CLIENT2 record (or CLIENT_CONTACT_PHONE or CLIENT_NOTE or CLIENT_TARGET_LINK record)
		//				number => updating CLIENT_CONTACT record with that ID
		// phone_id:
		//				blank => updating CLIENT2 record (or CLIENT_CONTACT or CLIENT_NOTE or CLIENT_TARGET_LINK record)
		//				number => updating CLIENT_CONTACT_PHONE record with that ID
		// note_id:
		//				blank => updating CLIENT2 record (or CLIENT_CONTACT or CLIENT_CONTACT_PHONE or CLIENT_TARGET_LINK record)
		//				number => updating CLIENT_NOTE record with that ID
		// tar_id:
		//				blank => updating CLIENT2 record (or CLIENT_CONTACT or CLIENT_CONTACT_PHONE or CLIENT_NOTE record)
		//				number => adding/deleting CLIENT_TARGET_LINK record, or updating fee, with the JOB_TARGET_SD.JOB_TARGET_ID
		"
		var field_name = control.name;
		var field_value = trim(control.value);
		var field_value_raw = field_value;
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
			field_type = 'x'; // text
			
		var op = 'u';
		var obj_id = {$client2_id};
		if (0 < contact_id)
		{
			op = 'ucc';
			obj_id = contact_id;
		}
		else if (0 < phone_id)
		{
			op = 'uph';
			obj_id = phone_id;
		}
		else if (0 < note_id)
		{
			op = 'unt';
			obj_id = note_id;
		}
		else if (0 < tar_id)
		{
			op = 'uta';
			obj_id = (10000 * {$client2_id}) + tar_id;
		}
		
		xmlHttp2 = GetXmlHttpObject();
		if (xmlHttp2 == null)
			return;
		var url = 'clients_ajax.php?op=' + op + '&i=' + obj_id + '&n=' + field_name + '&v=' + field_value + '&ty=' + field_type;
		url = url + '&ran=' + Math.random();
		//alert(url);
		xmlHttp2.onreadystatechange = stateChanged_update_client;
		xmlHttp2.open('GET', url, true);
		xmlHttp2.send(null);
	}
	
	function stateChanged_update_client()
	{
		if (xmlHttp2.readyState == 4)
		{
			var resptxt = xprint_noscript(xmlHttp2.responseText);
			if (resptxt)
			{
				bits = resptxt.split('|');
				if (bits[0] == 1)
				{
					if (bits[1] == 'fees_t')
					{
						var elset, ii, tfat;
						
						if (bits[2] != '-')
							document.getElementById('tr_fee').value = bits[2];
						if (bits[3] != '-')
							document.getElementById('nt_fee').value = bits[3];
						if (bits[4] != '-')
							document.getElementById('tc_fee').value = bits[4];
						if (bits[5] != '-')
							document.getElementById('rp_fee').value = bits[5];
						if (bits[6] != '-')
							document.getElementById('sv_fee').value = bits[6];
						if (bits[7] != '-')
							document.getElementById('mn_fee').value = bits[7];
						if (bits[8] != '-')
							document.getElementById('tm_fee').value = bits[8];
						if (bits[9] != '-')
							document.getElementById('at_fee').value = bits[9];
							
						elset = document.getElementsByClassName('tarfee');
						if (bits[10] != '-')
						{
							for (ii = 0; ii < elset.length; ii++)
							{
								tfat = elset[ii].getAttribute('tarfee');
								if (tfat == 'tck_1')
									elset[ii].checked = true;
								else if (tfat == 'fee_1')
									elset[ii].value = bits[10];
							}
						}
						if (bits[11] != '-')
						{
							for (ii = 0; ii < elset.length; ii++)
							{
								tfat = elset[ii].getAttribute('tarfee');
								if (tfat == 'tck_2')
									elset[ii].checked = true;
								else if (tfat == 'fee_2')
									elset[ii].value = bits[11];
							}
						}
						if (bits[12] != '-')
						{
							for (ii = 0; ii < elset.length; ii++)
							{
								tfat = elset[ii].getAttribute('tarfee');
								if (tfat == 'tck_3')
									elset[ii].checked = true;
								else if (tfat == 'fee_3')
									elset[ii].value = bits[12];
							}
						}
						if (bits[13] != '-')
						{
							for (ii = 0; ii < elset.length; ii++)
							{
								tfat = elset[ii].getAttribute('tarfee');
								if (tfat == 'tck_4')
									elset[ii].checked = true;
								else if (tfat == 'fee_4')
									elset[ii].value = bits[13];
							}
						}
						if (bits[14] != '-')
						{
							var ltset = bits[14].split('~');
							for (ii = 0; ii < ltset.length; ii++)
							{
								lt_id = ltset[ii];
								document.getElementById('div_lt_' + lt_id + '_n').style.display = 'none';
								document.getElementById('ltrtype_' + lt_id + '_y').checked = true;
								document.getElementById('div_lt_' + lt_id + '_y').style.display = 'block';
							}
						}
					}
					else if (bits[1] == 'fees_c')
					{
						if (bits[2] != '-')
						{
							var ltset = bits[2].split('~');
							for (ii = 0; ii < ltset.length; ii++)
							{
								lt_id = ltset[ii];
								document.getElementById('div_lt_' + lt_id + '_n').style.display = 'none';
								document.getElementById('ltrtype_' + lt_id + '_y').checked = true;
								document.getElementById('div_lt_' + lt_id + '_y').style.display = 'block';
							}
						}
					}
					else if (bits[1] != 'ok')
						alert('stateChanged_update_client: Ajax returned one and: ' + bits[1]);
				}
				else if (bits[0] == -1)
					alert('Error: ' + bits[1]);
				else if (bits[0] == -2)
				{
					var targetID = bits[1];
					alert('Error: ' + bits[2]);
				}
				else
					alert('stateChanged_update_client: Ajax returned: ' + resptxt);
			}
			//else
			//	alert('stateChanged_update_client: No response from ajax call!');
		}
	}
	
	</script>
	";
}

function print_clients()
{
	global $ac;
	global $ar;
#	global $can_edit;
	global $col2;
	global $col3;
	global $col6;
	global $csv_path;
	global $export;
	global $grey;
	global $manager_a;
	global $manager_x;
	global $page_title_2;
	global $phpExcel_ext;
	global $sc_addr;
	global $sc_alpha;
	global $sc_archived;
	global $sc_bacs;
	global $sc_bank;
	global $sc_contact;
	global $sc_fstmt;
	global $sc_group;
	global $sc_open;
	global $sc_stmtinv;
	global $sc_system;
	global $sc_text;
	global $sc_uninvbill;
	global $sc_uninvpay;
	global $sc_vat;
	global $subdir;
	global $task;
	global $ticked_clients;
	global $tr_colour_1;
	global $tr_colour_2;
	global $USER;
	
//	#
//	global $pm_match;
//	is_postcode($sc_text);
//	print "Match = $pm_match";
//	return;
	$page_title_2 = 'Clients - Vilcol';
	
	if ($sc_uninvbill)
		print "<p>Note that \"Un-invoiced trace statement billing\" only considers billing that has occurred within the last two years, on jobs that are closed.</p>";
	if ($sc_uninvpay)
		print "<p>Note that \"Un-invoiced collection payments\" only considers payments that have occurred within the last two years, on jobs that are still open.</p>";

	$clients = sql_get_clients($sc_system, $sc_text, $sc_alpha, $sc_addr, $sc_contact, $sc_group, $sc_bank, $sc_open, false, $sc_fstmt, $sc_uninvpay, $sc_uninvbill, $sc_stmtinv, $sc_bacs, $sc_vat, $sc_archived, false, $task == 'client_export' ? 'C_CO_NAME' : '');
	#dprint("Got Clients: " . print_r($clients,1));#
	if ($clients)
	{
		$export_xl = (($export == 'xl') ? true : false);
		
		if ($export_xl)
		{
			$filters = array();
			if ($sc_system == 't')
				$filters[] = "Trace system";
			elseif ($sc_system == 'c')
				$filters[] = "Collect system";
			$title = "Clients Search" . ($filters ? (" (" . implode(', ', $filters) . ")") : '');
			$title_short = "Clients Search";
			$xfile = "clients_search_{$USER['USER_ID']}";
		}
		else
		{
			$title = '';
			$title_short = '';
			$xfile = '';
		}
		
		if ($task == 'client_export')
			$headings = array('Client Name', 'Client Code', 'Contact', 'Client Address', 'Email Address');
		else
		{
			$headings = array('Code', 'Client Name');
			if ($sc_fstmt || $sc_uninvpay)
				$headings[] = 'Statement Due';
			$headings = array_merge($headings, array('Client Address', 'Contact', 'Phone', 'Open Jobs', 'System', 'Imported'));
		}
		
		$summary = "Showing " . count($clients) . " client" . ((count($clients) == 1) ? '' : 's') . ".";
		
		print "
		<table name=\"table_main\" class=\"spaced_table\" border=\"0\"><!---->
		<tr>
			<td $col3>$summary</td>
			";
			if ($task != 'client_export')
			{
				print "
				<td>&nbsp;&nbsp;&nbsp;&nbsp;" . input_button('Export to Excel', "export_xl()", $manager_x ? '' : 'disabled', 'but_export_xl') . "</td>
				<td $col2 $ar>" . input_button('Create Trace Statements', "create_stmts('t')", $manager_a ? '' : 'disabled') . "
								" . input_button('Create Collect Statements', "create_stmts('c')", $manager_a ? '' : 'disabled') . "</td>
				<td $col3>" . input_button('Set Commission', "bulk_set_commission()", $manager_x ? '' : 'disabled') . "</td>
				";
			}
			print "
		</tr>
		<tr><td $col6></td><td $col2><span id=\"comm_span\"></span></td></tr>
		<tr>
			";
			if ($task != 'client_export')
				print "
				<th>" . input_button('All', 'tick_all()', "style=\"width:30px;\"") . "</th>
				<th></th>
				";
			foreach ($headings as $hd)
				print "<th>$hd</th>";
			if ($task != 'client_export')
				print "<th $grey>DB ID</th>
		</tr>
		";
		
		$datalines = array(); # for export file
		$but_style = "style=\"width:44px;\"";
		$trcol = $tr_colour_1;
		foreach ($clients as $one)
		{
			$address = $one['C_ADDR_1'];
			$address .= (($address && $one['C_ADDR_2']) ? ", " : '') . $one['C_ADDR_2'];
			$address .= (($address && $one['C_ADDR_3']) ? ", " : '') . $one['C_ADDR_3'];
			$address .= (($address && $one['C_ADDR_4']) ? ", " : '') . $one['C_ADDR_4'];
			$address .= (($address && $one['C_ADDR_5']) ? ", " : '') . $one['C_ADDR_5'];
			$address .= (($address && $one['C_ADDR_PC']) ? ", " : '') . $one['C_ADDR_PC'];
			
			$contact = trim((string)$one['CC_FIRSTNAME']);
			$temp = trim((string)$one['CC_LASTNAME']);
			if ($temp)
			{
				if ($contact)
					$contact .= ' ';
				$contact .= $temp;
			}
			
			$phone = trim((string)$one['CP_PHONE']);
			if ($phone && (strpos($phone, ' ') === false))
				$phone = " $phone"; # prevent Excel from removing leading zero
			
			if ($one['C_TRACE'] && $one['C_COLLECT'])
				$sys = 'Tr+Co';
			elseif ($one['C_TRACE'])
				$sys = 'Trc';
			elseif ($one['C_COLLECT'])
				$sys = 'Coll';
			else
				$sys = '';
			$imported = (($one['IMPORTED'] == 1) ? 'Yes' : 'No');
			
			$open_jobs = array();
			if ($one['OPEN_JOBS_T'])
				$open_jobs[] = "Trc: " . $one['OPEN_JOBS_T'];
			if ($one['OPEN_JOBS_C'])
				$open_jobs[] = "Coll: " . $one['OPEN_JOBS_C'];
			$open_jobs = implode(', ', $open_jobs);
			
			$show_line = true;
			# $line_s is line for screen, $line_x is line for export
			if ($task == 'client_export')
			{
				$contact = str_replace('(Blank in old database)', '', $contact);
				$email = $one['CC_EMAIL_1'];
				if ($one['CC_EMAIL_2'])
				{
					if ($email)
						$email .= ", ";
					$email .= $one['CC_EMAIL_2'];
				}
				if ($one['C_CO_NAME'] || $contact)
				{
					$line_s = array($one['C_CO_NAME'], $one['C_CODE'], $contact, $address, $email);
					$line_x = array($one['C_CO_NAME'], $one['C_CODE'], $contact, $address, $email);
				}
				else
					$show_line = false;
			}
			else
			{
				$line_s = array($one['C_CODE'], $one['C_CO_NAME']);
				if ($sc_fstmt || $sc_uninvpay)
					$line_s[] = date_for_sql($one['INV_NEXT_STMT_DT'], true, false);
				$line_s = array_merge($line_s, array($address, $contact, $phone, $open_jobs ? $open_jobs : '', $sys, $imported, $one['CLIENT2_ID']));

				$line_x = array($one['C_CODE'], $one['C_CO_NAME']);
				if ($sc_fstmt || $sc_uninvpay)
					$line_x[] = date_for_sql($one['INV_NEXT_STMT_DT'], true, false);
				$line_x = array_merge($line_x, array($address, $contact, $phone, $open_jobs ? $open_jobs : '', $sys, $imported));
			}
			
			if ($show_line)
			{
				$datalines[] = $line_x;

				$cell = 0;
				print "
				<tr bgcolor=\"$trcol\" onmouseover=\"this.className='Highlight'\" onmouseout=\"this.className='Normal'\"><!---->
					";
					if ($task == 'client_export')
					{
						for ($ii = 0; $ii < 5; $ii++)
						{
							$temp = trim($line_s[$cell++]);
							if ($temp == '')
								$temp = '-';
							$align = (($ii == 1) ? $ar : '');
							print "
							<td $align>$temp</td>
							";
						}
					}
					else
					{
						print "
						<td $ac>"
						;
						if ($one['C_ARCHIVED'])
							print "ARCHIVED";
						else
							print input_tickbox('', "tck_{$one['CLIENT2_ID']}", 1, in_array($one['CLIENT2_ID'], $ticked_clients) ? true : false);
						print "</td>
						<td>" . input_button('View', "view_js({$one['CLIENT2_ID']},0)", $but_style) . "
							" . #($can_edit ? input_button('Edit', "view_js({$one['CLIENT2_ID']},1)", $but_style) : '') . 
							"</td>
						<td $ar>{$line_s[$cell++]}</td>
						<td>{$line_s[$cell++]}</td>
						" . (($sc_fstmt || $sc_uninvpay) ? "<td $ar>{$line_s[$cell++]}</td>" : '') . "
						<td>{$line_s[$cell++]}</td>
						<td>{$line_s[$cell++]}</td>
						<td $ar>{$line_s[$cell++]}</td>
						<td style=\"white-space:nowrap;\">{$line_s[$cell++]}</td>
						<td $ac>{$line_s[$cell++]}</td>
						<td $ac>{$line_s[$cell++]}</td>
						<td $ar $grey>{$line_s[$cell++]}</td>
						";
					}
					print "
				</tr>
				";
			}
			$trcol = (($trcol == $tr_colour_1) ? $tr_colour_2 : $tr_colour_1);
		}
		print "
		</table><!--table_main-->
		";
		
		if ($export == 'xl')
		{
			$top_lines = array(array($title), array($summary), array());
			$formats = array();#'G' => $excel_date_format);
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
	else#if ($sc_text || $sc_addr || $sc_contact || $sc_bank || $sc_group)
		dprint("No clients matched your search.", true);
	#else 
	#	dprint("There are no Clients in the database.", true);
		
}

function print_one_client($editing)
{
	global $ac;
	global $agent_c;
	global $agent_t;
	global $ar;
	global $at;
	global $can_edit;
	global $client; # set by print_one_client()
	global $client_groups;
	global $client2_id;
	global $col2;
	global $col3;
	global $grey;
	global $manager_a;
	global $manager_x;
	global $page_title_2;
	#global $style_r;
	global $sz_med_1;
	global $sz_med_2;
	global $sz_long;
	global $sz_small;

	$user_debug = user_debug();
	
	if ($editing && (!$can_edit))
		$editing = false;
	$client = sql_get_one_client($client2_id);
	
	$editing2 = $editing;
	if ($client['C_ARCHIVED'])
		$editing2 = false;

	$page_title_2 = "Client {$client['C_CODE']} - Vilcol";
	sql_get_client_groups(false); # writes to $client_groups
	
	list($balance, $invoices, $credits, $focs, $receipts, $adjusts) = sql_get_client_balance_info($client2_id);
	$balance_info = "Account Balance: " . money_format_kdb($balance, true, true, true) . "<br>" .
					"Invoices&nbsp;" . money_format_kdb($invoices, true, true, true) . ". " .
					"Credits&nbsp;" . money_format_kdb($credits, true, true, true) . ". " .
					"FOCs&nbsp;" . money_format_kdb($focs, true, true, true) . ". " .
					"Receipts&nbsp;" . money_format_kdb($receipts, true, true, true) . ". " .
					"Adjustments&nbsp;" . money_format_kdb($adjusts, true, true, true) . ".";
	
	$salesperson_id = 0;
	$salesperson_txt = '(none)';
	foreach ($client['SALESPERSONS'] as $spinfo)
	{
		$salesperson_id = $spinfo['SP_USER_ID'];
		if ($salesperson_id != $client['SALESPERSON_ID'])
			dprint("***DATA ERROR*** print_one_client() salesperson_id=$salesperson_id, SALESPERSON_ID={$client['SALESPERSON_ID']}", true);
		$salesperson_txt = ($spinfo['USERNAME'] ? $spinfo['USERNAME'] : ($spinfo['SP_TXT'] ? $spinfo['SP_TXT'] : '(No Name)'));
		break;
	}
	$salespeople = ($editing2 ? sql_get_salespersons() : array());
	#dprint("\$salesperson_id=$salesperson_id, \$salesperson_txt=\"$salesperson_txt\", salesp=" . print_r($salespeople,1));#
	
	$onchange_txt = ($editing2 ? "onchange=\"update_client(this);\"" : 'readonly');
	$onkey_client = ($editing2 ? "onkeyup=\"client_lookup();\"" : '');
	$onchange_num = ($editing2 ? "onchange=\"update_client(this,'n');\"" : 'readonly');
	$onchange_tck = ($editing2 ? "update_client(this,'t')" : '');
	$extra_tck = ($editing2 ? '' : 'disabled');
	
	$sz_small = 3;
	$sz_med_1 = 16;
	$sz_med_2 = 24;
	$sz_long = 65;
	
//	print "
//	<script type=\"text/javascript\">
//	document.getElementById('sc_text').value = '{$client['C_CODE']}';
//	</script>
//	";

	if ($client['NUM_JOBS'] <= 0)
		$txt_client_jobs = "Client has no jobs.";
	elseif ($client['NUM_JOBS'] == 1)
	{
		$txt_client_jobs = "Client has one job, ";
		if ($client['NUM_JOBS_OPEN'] <= 0)
			$txt_client_jobs .= "which is not open.";
		else
			$txt_client_jobs .= "which is open.";
	}
	else
	{
		$txt_client_jobs = "Client has {$client['NUM_JOBS']} jobs, ";
		if ($client['NUM_JOBS_OPEN'] == 0)
			$txt_client_jobs .= "none of which are open.";
		elseif ($client['NUM_JOBS_OPEN'] == 1)
			$txt_client_jobs .= "one of which is open.";
		else
			$txt_client_jobs .= "{$client['NUM_JOBS_OPEN']} of which are open.";
	}
	
	print "
	<span style=\"font-weight:bold;\">" . ($editing ? "Edit Client" : "View Client") . "</span>&nbsp;&nbsp;
	" . input_button('View', "view_js($client2_id,0)") . "
	";
	if ($manager_x)
		print "
		" . input_button('Edit', "view_js($client2_id,1)") . "
		";
	
	$button_w = "style=\"width:186px;\"";
	if ($client['C_ARCHIVED'])
	{
		$div_main_colour = "style=\"background-color:gray\"";
		print "<p>THIS CLIENT IS ARCHIVED</p>
			";
	}
	else
		$div_main_colour = "";
	$tabindex = 1;
	$tabindex_rightside = 100;
	print "
	<div id=\"div_main\" $div_main_colour>
	<br>
	<table class=\"basic_table\" id=\"table_client_main\" border=\"0\"><!---->
	<tr>
		<td>Client Name</td>
		";
		$taborder = "tabindex=\"" . ($tabindex++) . "\"";
		print "
		<td>" . input_textbox('c_co_name', $client['C_CO_NAME'], $sz_long, 0, "$onchange_txt $onkey_client $taborder") . "</td>
		<td width=\"20\">&nbsp;</td>
		<td $col2>$txt_client_jobs</td>
		<td width=\"50\" $ar $grey>ID {$client['CLIENT2_ID']}</td>
	</tr>
	<tr>
		";
		$taborder = "tabindex=\"" . ($tabindex++) . "\"";
		print "
		<td>Client Code</td><td>" . input_textbox('c_code', $client['C_CODE'], $sz_med_1, 0, "$onchange_txt $onkey_client $taborder") . "&nbsp;&nbsp;&nbsp;
			";
			$taborder = "tabindex=\"" . ($tabindex++) . "\"";
			print "
			Alpha code: " . input_textbox('alpha_code', $client['ALPHA_CODE'], $sz_small, 10, "$onchange_txt $taborder") . "</td>
		<td>&nbsp;</td>
		<td>" . ((0 < $client['C_CODE']) ? input_button('Show Jobs (in new Tab)', "show_jobs({$client['C_CODE']})", $button_w) : '') . "</td>
		<td>";
			if (1)#$editing2)
				print input_button('Load new jobs from CSV', "csv_bulk_upload_jobs({$client['CLIENT2_ID']})", $button_w);
			#else
			#	print input_button('Load new jobs from CSV', '', "$button_w disabled");
			print "</td>
	</tr>
	<tr>
		<td></td><td><span id=\"matches\" style=\"color:blue;\"></span></td>
	</tr>
	<tr>
		<td>Client Group</td>
		<td>
			";
			if ($client['GROUP_NAME'])
			{
				$group_name = $client['GROUP_NAME'];
				$group_members = "Other clients in this group: ";
				if (count($client['GROUP_CLIENTS']) > 0)
				{
					foreach ($client['GROUP_CLIENTS'] as $m_id => $member)
						$group_members .= "&nbsp;" . input_button($member, "goto_client($m_id, $member)");
				}
				else
					$group_members .= '(none)';
			}
			else
			{
				$group_name = '(none)';
				$group_members = '';
			}
			if ($editing2)
			{
				print input_select('client_group_id', $client_groups, $client['CLIENT_GROUP_ID'], $onchange_num);
				if (!(0 < $client['CLIENT_GROUP_ID']))
					print "&nbsp;" . input_button('Create New Group', 'create_new_group()');
			}
			else
				print input_textbox('group_name', $group_name, $sz_long, 0, 'readonly');
			print "
			&nbsp;&nbsp;&nbsp;$group_members
		</td>
		<td>&nbsp;</td>
		<td $col2 rowspan=\"2\" $at>" . ((0 < $client['C_CODE']) ? 
					(input_button('Show Invoices (in new Tab)', "show_invoices({$client['C_CODE']})", 
										$button_w . ($manager_x ? '' : ' disabled')) . "&nbsp;&nbsp;&nbsp;" . 
						($manager_x ? $balance_info : ''))
					: '') . "</td>
	</tr>
	<tr>
		";
		$taborder = "tabindex=\"" . ($tabindex++) . "\"";
		print "
		<td>Address 1</td><td>" . input_textbox('c_addr_1', $client['C_ADDR_1'], $sz_long, 0, "$onchange_txt $taborder") . "</td>
		<td>&nbsp;</td>
	</tr>
	<tr>
		";
		$taborder = "tabindex=\"" . ($tabindex++) . "\"";
		print "
		<td>Address 2</td><td>" . input_textbox('c_addr_2', $client['C_ADDR_2'], $sz_long, 0, "$onchange_txt $taborder") . "</td>
		<td>&nbsp;</td>
		<td>" . input_tickbox('Collections Client', 'c_collect', 1, $client['C_COLLECT'], $onchange_tck, $extra_tck) . "</td>
		<td>" . (($agent_c || $manager_a) ? input_button('Create New Collection Job', "create_job('c')", $button_w) : '') . "</td>
	</tr>
	<tr>
		";
		$taborder = "tabindex=\"" . ($tabindex++) . "\"";
		print "
		<td>Address 3</td><td>" . input_textbox('c_addr_3', $client['C_ADDR_3'], $sz_long, 0, "$onchange_txt $taborder") . "</td>
		<td>&nbsp;</td>
		<td>" . input_tickbox('Traces Client', 'c_trace', 1, $client['C_TRACE'], $onchange_tck, $extra_tck) . "</td>
		<td>" . (($agent_t || $manager_a) ? input_button('Create New Trace Job', "create_job('t')", $button_w) : '') . "</td>
	</tr>
	<tr>
		";
		$taborder = "tabindex=\"" . ($tabindex++) . "\"";
		print "
		<td>Address 4</td><td>" . input_textbox('c_addr_4', $client['C_ADDR_4'], $sz_long, 0, "$onchange_txt $taborder") . "</td>
		<td>&nbsp;</td>
		<td>" . input_tickbox('Client is an Individual', 'c_individual', 1, $client['C_INDIVIDUAL'], $onchange_tck, $extra_tck) . "</td>
	</tr>
	<tr>
		";
		$taborder = "tabindex=\"" . ($tabindex++) . "\"";
		print "
		<td>Address 5</td><td>" . input_textbox('c_addr_5', $client['C_ADDR_5'], $sz_long, 0, "$onchange_txt $taborder") . "</td>
		<td>&nbsp;</td>
		<td>" . input_tickbox('Client is an Agency', 'c_agency', 1, $client['C_AGENCY'], $onchange_tck, $extra_tck) . "</td>
	</tr>
	<tr>
		";
		$taborder = "tabindex=\"" . ($tabindex++) . "\"";
		print "
		<td>Postcode</td><td>" . input_textbox('c_addr_pc', $client['C_ADDR_PC'], $sz_med_1, 0, "$onchange_txt $taborder") . "
			&nbsp;&nbsp;
			" . ($client['C_ADDR_PC'] ? input_button('Look-up', "postcode_lookup('c_addr_pc','{$client['C_ADDR_PC']}')") : '') . "</td>
		<td>&nbsp;</td>
		<td $col2>" . input_tickbox('Group Head Office (if client is in a Client Group)', 
									'c_group_ho', 1, $client['C_GROUP_HO'], $onchange_tck, $extra_tck) . "</td>
	</tr>
	<tr>
		<td>Contacts</td><td></td>
		<td>&nbsp;</td>
		<td></td>
	</tr>
	<tr>
		<td>&nbsp;</td>
		<td $at>
			<div id=\"div_contacts\" style=\"height:200px; overflow-y:scroll; border:dotted gray;\">
			";
			$tabindex = print_one_client_contacts($editing2, $tabindex);
			print "
			</div><!--div_contacts-->
			";
			if ($editing2)
				print "
				<br>
				";
				$taborder = "tabindex=\"" . ($tabindex++) . "\"";
				print input_button('Add new Contact', "add_contact()", $taborder) . "&nbsp;&nbsp;
				" . input_button('Show Obsolete Contacts', "show_obsolete_contacts()") . "&nbsp;&nbsp;
				" . input_button('Show Obsolete Phones', "show_obsolete_phones()") . "
				";
			print "
		</td>
		<td>&nbsp;</td>
		<td $col3 $at>
			";
			if ($agent_t || $manager_a)
				$tabindex_rightside = print_one_client_fees($editing2, 1, $tabindex_rightside);
			print "
		</td>
	</tr>
	<tr><td id=\"notes_top\"></td></tr>
	<tr>
		<td>Notes</td><td></td>
		<td>&nbsp;</td>
		<td $col3 $at rowspan=\"2\">
			";
			if ($agent_t || $manager_a)
				$tabindex_rightside = print_one_client_fees($editing2, 2, $tabindex_rightside);
			print "
		</td>
	</tr>
	<tr>
		<td>&nbsp;</td>
		<td $at>
			<div id=\"div_notes\" style=\"height:250px; overflow-y:scroll;\">
			";
			$tabindex = print_one_client_notes($editing2, $tabindex);
			print "
			</div><!--div_notes-->
			";
			if ($editing2)
			{
				$taborder = "tabindex=\"" . ($tabindex++) . "\"";
				print "
				<br>" . input_button('Add new Note', "add_note()", $taborder) . "
				";
			}
			print "
		</td>
		<td>&nbsp;</td>
	</tr>
	<tr>
		<td>&nbsp;</td>
	</tr>
	<tr>
		<td>Salesperson</td>
		";
		$taborder = "tabindex=\"" . ($tabindex++) . "\"";
		print "
		<td>" . ($editing2 ? 
				("$salesperson_txt " . input_select('salesperson_id', $salespeople, $salesperson_id, "$onchange_num $taborder")) :
				input_textbox('salesperson_txt', $salesperson_txt, $sz_med_1, 0, "readonly $taborder") ) . "</td>
		<td>&nbsp;</td>
		<td>Accounts</td>
	</tr>
	<tr>
		<td $at>Salesperson<br>History</td>
		<td $at>
			<table class=\"basic_table\">
				<tr><th>Username</th><th>&nbsp;</th><th>From</th>
					" . ($user_debug ? "<th $grey>ID</th>" : '') . "
				</tr>
				";
				foreach ($client['SALESPERSONS'] as $spid => $spinfo)
				{
					if ($spinfo['SP_DT'])
						$from = date_for_sql($spinfo['SP_DT'], true, true, true, false, false, true, false, true);
					else 
						$from = "Old database";
					print "
					<tr><td>{$spinfo['USERNAME']}</td><td>{$spinfo['SP_TXT']}</td><td $ac>$from</td>
						" . ($user_debug ? "<td $grey $ar>$spid/{$spinfo['SP_USER_ID']}</td>" : '') . "
					</tr>
					";
				}
				print "
			</table>
		</td>
		<td>&nbsp;</td>
		<td $col3 $at rowspan=\"6\">
			";
			$tabindex_rightside = print_one_client_accounts($editing2, $tabindex_rightside);
			print "
		</td>
	</tr>
	<tr>
		<td $at>Letters<br>Selected</td>
		<td $at>
			<div id=\"div_letters\" style=\"height:300px; overflow-y:scroll;\">
			";
			$ltsz = "width=\"150\"";
			print "
			<table name=\"table_letter_types\" class=\"basic_table\" border=\"0\"><!---->
			" . ($editing2 ? "<tr><th $ltsz>Selected</th><th $ltsz>Not selected</th></tr>" : '') . "
			<tr>
				<td $at>
				";
				$count_sel = 0;
				foreach ($client['LETTER_TYPES'] as $ltid => $ltinfo)
				{
					$onchange_lt = ($editing2 ? "update_letter_type(this, $ltid, 'y')" : '');
					$extra_lt = ($editing2 ? '' : 'disabled');
					print "<span id=\"div_lt_{$ltid}_y\" style=\"display:" . ($ltinfo['SEL'] ? 'block' : 'none') . ";\">" .
								input_tickbox($ltinfo['NAME'], "ltrtype_{$ltid}_y", 1, $ltinfo['SEL'], $onchange_lt, $extra_lt) . 
							"</span>
							";
					if ($ltinfo['SEL'])
						$count_sel++;
				}
				if ((!$editing2) && ($count_sel == 0))
					print "No letter types are selected for this client.";
				print "
				</td>
				";
				if ($editing2)
				{
					print "
					<td $at>
					";
					foreach ($client['LETTER_TYPES'] as $ltid => $ltinfo)
					{
						if ($ltinfo['SYSTEM'] == 'T')
						{
							# Trace letter
							if (!($agent_t || $manager_a))
								continue;
						}
						elseif ($ltinfo['SYSTEM'] == 'C')
						{
							# Collection letter
							if (!($agent_c || $manager_a))
								continue;
						}
						$onchange_lt = ($editing2 ? "update_letter_type(this, $ltid, 'n')" : '');
						$extra_lt = ($editing2 ? '' : 'disabled');
						print "<span id=\"div_lt_{$ltid}_n\" style=\"display:" . ($ltinfo['SEL'] ? 'none' : 'block') . ";\">" .
									input_tickbox($ltinfo['NAME'], "ltrtype_{$ltid}_n", 1, $ltinfo['SEL'], $onchange_lt, $extra_lt) . 
								"</span>
								";
					}
					print "
					</td>
					";
				}
				print "
			</tr>
			";
//			print "
//			<tr><td $col2>" . print_r($client['LETTER_TYPES'],1) . "</td></tr>
//			";
			print "
			</table><!--table_letter_types-->
			</div><!--div_letters-->
			<br>
			";
			
			print "
			<b>Collection Letter Sequence (" . ($client['LETTER_SEQ'][-1] ? "Client-specific" : "Default") . ")</b>
				&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
				" . ($manager_x ? input_button('Edit Sequences', "show_letter_seq()", $button_w) : '') . "
			<table name=\"table_letter_seq\" class=\"basic_table\" border=\"0\"><!---->
			<tr><th width=\"10\"></th><th>Letter</th><th>Days After</th></tr>
			";
			foreach ($client['LETTER_SEQ'] as $lsid => $lsinfo)
			{
				if (0 < $lsid)
				{
					print "
					<tr id=\"LETTER_SEQ_{$lsid}\">
						<td></td>
						<td>{$lsinfo['LETTER_NAME']}</td>
						<td $ac>{$lsinfo['SEQ_DAYS']}</td>
					</tr>
					";
				}
			}
			print "
			</table><!--table_letter_seq-->
		</td>
	</tr>
	<tr><td id=\"reports_top\"></td></tr>
	<tr>
		<td $at>Reports Sent</td>
		<td $at>
			<table name=\"table_reports\" class=\"spaced_table\">
			";
			if ($client['CLIENT_REPORTS'])
			{
				print "
				<tr>
					<th>Report</th><th>Date</th><th $grey>ID</th>
				</tr>
				";
				foreach ($client['CLIENT_REPORTS'] as $report)
				{
					print "
					<tr>
						<td>{$report['REPORT_NAME']}</td>
						<td>" . date_for_sql($report['REPORT_DT'], true, false) . "</td>
						<td $grey>{$report['CLIENT_REPORT_ID']}</td>
					</tr>
					";
				}
			}
			else
				print "<tr><td $col3>No reports yet</td></tr>
					";
			if ($editing2)
			{
				print "
				<tr>
					<td><br>Add new report:</td>
				</tr>
				<tr>
					<td>Report name</td>
					<td>Report date</td>
				</tr>
				<tr>
					<td>" . input_textbox('report_new_name', '', 30, 100) . "</td>
					<td>" . input_textbox('report_new_dt', '', 4, 10) . "</td>
					<td>" . input_button('Save', 'report_new_save()') . "</td>
				</tr>
				";
			}
			print "
			</table><!--table_reports-->
			";
		print "
		</td>
	</tr>
	<tr>
		<td>" . input_tickbox('Portal Push', 'portal_push', 1, $client['PORTAL_PUSH'], $onchange_tck, $extra_tck) . "</td>
	</tr>
	<tr>
		<td>Client Created:</td><td>" . ($client['IMPORTED'] ? 'Imported from old database' : 
										date_for_sql($client['CREATED_DT'], true, true, false, false, false, true, false, true)) . "</td>
	</tr>
	<tr>
		<td>Client Updated:</td><td>" . date_for_sql($client['UPDATED_DT'], true, true, false, false, false, true, false, true) . "</td>
	</tr>
	";
//	print "
//	<tr>
//		<td $grey>Client DB ID</td>
//		<td>" . input_textbox('client2_id', $client['CLIENT2_ID'], $sz_small, 0, "$style_r readonly") . "</td>
//	</tr>
//	";
	if (1)#$editing)
	{
		print "
		<tr>
			<td></td>
			<td>" . input_button('Show Audit', "show_audit({$client['CLIENT2_ID']})");
			if ($editing && $manager_x)
				print "&nbsp;&nbsp;" . ($client['C_ARCHIVED'] ? input_button('Un-archive Client', "unarchive_client()")
																: input_button('Archive Client and Jobs', "archive_client()"));
				print "
				</td>
		</tr>
		";
	}
	print "
	<tr>
		<td></td>
	</tr>
	</table><!--table_client_main-->
	</div><!--div_main-->
	";

	if (user_debug() && $client['IMPORTED'])
	{
		print "
		<p><b>This client was imported from the Old Database.</b>
			<br>Other data imported from the old database &ndash; this can be ignored:</p>
		<table class=\"basic_table\" id=\"table_client_z\" border=\"1\">
			<tr><th>Field</th><th>Value</th></tr>
			";
			foreach ($client['CLIENT_Z'] as $zf => $zv)
				# Don't display first two characters "Z_" of field name
				print "<tr><td>" . substr($zf, 2) . "</td><td>$zv</td></tr>
						";
			print "
		</table><!--table_client_z-->
		";
	}
	
	print "
	<form name=\"form_jobs\" action=\"jobs.php\" method=\"post\" target=\"_blank\" rel=\"noopener\">
		" . input_hidden('sc_client', '') . "
		" . input_hidden('task', '') . "
	</form><!--form_jobs-->
	
	<form name=\"form_invoices\" action=\"ledger.php\" method=\"post\" target=\"_blank\" rel=\"noopener\">
		" . input_hidden('sc_client', '') . "
		" . input_hidden('task', '') . "
	</form><!--form_invoices-->

	<form name=\"form_client\" action=\"clients.php\" method=\"post\" target=\"_blank\" rel=\"noopener\">
		" . input_hidden('client2_id', '') . "
		" . input_hidden('sc_text', '') . "
		" . input_hidden('task', '') . "
	</form><!--form_client-->

	<form name=\"form_audit\" action=\"audit.php\" method=\"post\" target=\"_blank\" rel=\"noopener\">
		" . input_hidden('client2_id', '') . "
	</form><!--form_client-->

	<form name=\"form_bulk\" action=\"bulkjobs.php\" method=\"post\" target=\"_blank\" rel=\"noopener\">
		" . input_hidden('client2_id', '') . "
	</form><!--form_bulk-->

	<form name=\"form_seq\" action=\"standing.php\" method=\"post\" target=\"_blank\" rel=\"noopener\">
		" . input_hidden('task', 'edit') . "
		" . input_hidden('item', 'letter_sequence') . "
	</form><!--form_seq-->
	";
	
} # print_one_client()

function print_one_client_contacts($editing, $tabindex)
{
	# Called from print_one_client()
	global $ar;
	#global $at;
	global $client; # set by print_one_client()
	#global $col2;
	global $col3;
	global $col4;
	global $col5;
	global $grey;
	global $sz_med_1;
	global $sz_med_2;
	global $sz_long;
	global $sz_small;

	$user_debug = user_debug();

	# The first entry in $client['CONTACTS'] should have CC_MAIN=1
	
	#dprint(print_r($client['CONTACTS'],1));#
	
	$show_obs_contact = post_val('show_obs_contact', true);
	$contact_count = 0;
	foreach ($client['CONTACTS'] as $contact)
	{
		if ($show_obs_contact || (!$contact['OBSOLETE']))
			$contact_count++;
	}

	print "
	<table class=\"basic_table\" id=\"table_client_contacts\" border=\"0\"><!---->
	";
	if (0 < $contact_count)
	{
		$contact_ix = 0;
		foreach ($client['CONTACTS'] as $client_contact_id => $contact)
		{
			if ($contact['OBSOLETE'] && (!$show_obs_contact))
				continue;

			$col_c = ($contact['OBSOLETE'] ? "style=\"color:red\"" : '');
			$editing2 = (($editing && (!$contact['OBSOLETE'])) ? true : false);
			$onchange_txt = ($editing2 ? "onchange=\"update_contact(this,$client_contact_id);\"" : 'readonly');
			$onchange_tck = ($editing2 ? "update_contact(this,$client_contact_id,'t')" : '');
			$extra_tck = ($editing2 ? '' : 'disabled');
			$onchange_obs = ($editing ? "update_contact(this,$client_contact_id,'t')" : '');
			$extra_obs = ($editing ? '' : 'disabled');
			print "
			<tr $col_c>
				<td $col_c></td>
				<td $col_c>Title</td>
				<td $col_c>First name</td>
				<td $col_c>Last name</td>
				<td>&nbsp;</td>
			</tr>
			<tr $col_c>
				<td $ar $col_c>Name:</td>
				";
				$taborder = "tabindex=\"" . ($tabindex++) . "\"";
				print "
				<td $col_c>" . input_textbox('cc_title', $contact['CC_TITLE'], $sz_small, 10, "$onchange_txt $taborder") . "</td>
				";
				$taborder = "tabindex=\"" . ($tabindex++) . "\"";
				print "
				<td $col_c>" . input_textbox('cc_firstname', $contact['CC_FIRSTNAME'], $sz_med_1, 100, "$onchange_txt $taborder") . "</td>
				";
				$taborder = "tabindex=\"" . ($tabindex++) . "\"";
				print "
				<td $col_c>" . input_textbox('cc_lastname', $contact['CC_LASTNAME'], $sz_med_2, 50, "$onchange_txt $taborder") . "</td>
				<td>&nbsp;</td>
				" . ($user_debug ? "<td $grey $ar>$client_contact_id</td>" : '') . "
			</tr>
			<tr $col_c>
				<td>&nbsp;</td>
				<td $col3>" . input_tickbox('Main Contact', 'cc_main', 1, $contact['CC_MAIN'], $onchange_tck, "$extra_tck $col_c") . '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;' .
							 input_tickbox('Invoices Contact', 'cc_inv', 1, $contact['CC_INV'], $onchange_tck, "$extra_tck $col_c") . '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;' . 
							 input_tickbox('Reports Contact', 'cc_rep', 1, $contact['CC_REP'], $onchange_tck, "$extra_tck $col_c") .
					"</td>
			</tr>
			";

			$show_obs_phone = post_val('show_obs_phone', true);
			$phone_count = 0;
			foreach ($contact['PHONES'] as $phoneinfo)
			{
				if ($show_obs_phone || (!$phoneinfo['OBSOLETE']))
					$phone_count++;
			}
			if (0 < $phone_count)
			{
				$first_phone = true;
				foreach ($contact['PHONES'] as $phid => $phoneinfo)
				{
					if ($phoneinfo['OBSOLETE'] && (!$show_obs_phone))
						continue;
					$onchange_ph_txt = ($editing2 ? "onchange=\"update_phone(this,$phid);\"" : 'readonly');
					$onchange_ph_tck = ($editing2 ? "update_phone(this,$phid,'t')" : '');
					$extra_ph_tck = ($editing2 ? '' : 'disabled');
					$col_ph = ($col_c ? $col_c : ($phoneinfo['OBSOLETE'] ? "style=\"color:red\"" : ''));
					print "
					<tr $col_ph>
					";
					if ($first_phone)
						print "
						<td $ar $col_ph>Phone(s):</td>
							";
					else
						print "
						<td>&nbsp;</td>
							";
					#dprint("descr=|{$phoneinfo['CP_DESCR']}|");#
					print "
						<td $col_ph>" . ($phoneinfo['OBSOLETE'] ? 
										'(OBS.)' :
										input_tickbox('Main', 'cp_main', 1, $phoneinfo['CP_MAIN'], $onchange_ph_tck, $extra_ph_tck)) . "</td>
						";
						$taborder = "tabindex=\"" . ($tabindex++) . "\"";
						print "
						<td $col_ph>" . ($phoneinfo['OBSOLETE'] ? 
										$phoneinfo['CP_PHONE'] :
										input_textbox('cp_phone', $phoneinfo['CP_PHONE'], $sz_med_1, 50, "$onchange_ph_txt $taborder")) . "</td>
						";
						$taborder = "tabindex=\"" . ($tabindex++) . "\"";
						print "
						<td $col_ph>" . ($phoneinfo['OBSOLETE'] ? 
										$phoneinfo['CP_DESCR'] :
										input_textbox('cp_descr', $phoneinfo['CP_DESCR'], $sz_med_2, 1000, "$onchange_ph_txt $taborder")) . "</td>
						<td>" . ($editing2 ?
									($phoneinfo['OBSOLETE'] ?
										input_button('r', "res_phone($phid)", "title=\"Restore Phone\"") :
										input_button('x', "del_phone($phid)", "title=\"Delete Phone\""))
									: '') . "</td>
						" . ($user_debug ? "<td $grey $ar>$phid</td>" : '') . "
					</tr>
					";
					$first_phone = false;
				}
			}
			else
				print "
				<tr $col_c><td $ar $col_c>Phone(s):</td><td $col4 $col_c>No phone numbers</td></tr>
				";
			if ($editing2)
				print "
				<tr $col_c>
					<td>&nbsp;</td>
					<td $col5 $col_c>" . input_button('Add Phone', "add_phone($client_contact_id)") . "
						</td>
				</tr>
				";
			print "
			<tr $col_c>
				<td $ar>Email 1:</td>
				";
				$taborder = "tabindex=\"" . ($tabindex++) . "\"";
				print "
				<td $col3 $col_c>" . input_textbox('cc_email_1', $contact['CC_EMAIL_1'], $sz_long, 100, "$onchange_txt $taborder") . "</td>
			</tr>
			<tr $col_c>
				<td $ar>Email 2:</td>
				";
				$taborder = "tabindex=\"" . ($tabindex++) . "\"";
				print "
				<td $col3 $col_c>" . input_textbox('cc_email_2', $contact['CC_EMAIL_2'], $sz_long, 100, "$onchange_txt $taborder") . "</td>
			</tr>
			<tr $col_c>
				<td $ar>Position:</td>
				";
				$taborder = "tabindex=\"" . ($tabindex++) . "\"";
				print "
				<td $col3 $col_c>" . input_textbox('cc_position', $contact['CC_POSITION'], $sz_long, 100, "$onchange_txt $taborder") . "</td>
			</tr>
			";
			if ($editing)
				print "
				<tr $col_c>
					<td>&nbsp;</td>
					";
					$taborder = "tabindex=\"" . ($tabindex++) . "\"";
					print "
					<td $col4 $col_c>" . input_tickbox('Obsolete Contact', 'obsolete', 1, $contact['OBSOLETE'], $onchange_obs, "$extra_obs $taborder") . "</td>
				</tr>
				";
			if ($editing || ($contact_ix < ($contact_count-1)))
				print "<tr><td $col5><hr></td></tr>";
			$contact_ix++;
		} # foreach (contacts)
	} # if (0 < $contact_count)
	else
		print "<tr><td $col5>No Contacts</td></tr>";
	
	print "
	</table><!--table_client_contacts-->
	";
	
//	print "
//	<table class=\"basic_table\">
//	<tr>
//		<td $col3>
//			";
//			if (count($client['CONTACTS']) > 0)
//			{
//				print "
//				<table class=\"spaced_table\">
//					<tr><th>Title</th><th>First name</th><th>Last name</th><th>Main?</th><th>Invoices?</th><th>Reports?</th>
//						<th>Email(s)</th><th>Phone(s)</th>
//						<th $grey>ID</th>
//					</tr>
//					";
//					foreach ($client['CONTACTS'] as $ccid => $ccinfo)
//					{
//						$emails = $ccinfo['CC_EMAIL_1'] . (($ccinfo['CC_EMAIL_1'] && $ccinfo['CC_EMAIL_2']) ? '<br>' : '') .
//									$ccinfo['CC_EMAIL_2'];
//						print "
//						<tr><td>{$ccinfo['CC_TITLE']}</td><td>{$ccinfo['CC_FIRSTNAME']}</td>
//							<td>{$ccinfo['CC_LASTNAME']}</td>
//							<td>" . ($ccinfo['CC_MAIN'] ? 'Yes' : 'No') . "</td>
//							<td>" . ($ccinfo['CC_INV'] ? 'Yes' : 'No') . "</td>
//							<td>" . ($ccinfo['CC_REP'] ? 'Yes' : 'No') . "</td>
//							<td>$emails</td>
//							<td>
//							";
//							if (count($ccinfo['PHONES']) > 0)
//							{
//								print "
//								<table class=\"spaced_table\">
//								";
//								foreach ($ccinfo['PHONES'] as $phoneinfo)
//									print "<tr><td>{$phoneinfo['CP_PHONE']}</td><td>{$phoneinfo['CP_DESCR']}</td></tr>
//											";
//								print "
//								</table>
//								";
//							}
//							else 
//								print "None yet";
//							print "
//							</td>
//							<td $grey>$ccid</td>
//						</tr>
//						";
//					}
//					print "
//				</table>
//				";
//			}
//			else 
//				print "(None yet)";
//			print "
//		</td>
//	</tr>
//	</table>
//	";
	return $tabindex;
} # print_one_client_contacts()

function print_one_client_fees($editing, $half, $tabindex)
{
	# Called from print_one_client()
	global $ac;
	global $ar;
	global $at;
	global $client; # set by print_one_client()
#	global $col2;
	global $col3;
	global $grey;
	global $style_r;
	global $sz_med_1;
#	global $sz_med_2;
	#global $sz_long;
	global $sz_small;

	$user_debug = user_debug();

	$sz_med_1 = 50;
	$sz_small = 10;
	
	#$onchange_txt = ($editing ? "onchange=\"update_client(this);\"" : 'readonly');
	#$onchange_num = ($editing ? "onchange=\"update_client(this,'n');\"" : 'readonly') . ' ' . $style_r;
	$onchange_mon = ($editing ? "onchange=\"update_client(this,'m');\"" : 'readonly') . ' ' . $style_r;
	$onchange_mon_tar = ''; # redefined for each target fee
	$onchange_tck_tar = ''; # redefined for each target fee
	$extra_tck_tar = ($editing ? '' : 'disabled');
	
	if ($half == 1)
	{
		print "
		<table id=\"table_client_fees\" class=\"basic_table\" border=\"0\"><!---->
		<tr>
			<td>Fees</td>
		</tr>
		<tr>
			";
			$taborder = "tabindex=\"" . ($tabindex++) . "\"";
			print "
			<td></td><td>E-Trace</td><td $ar style=\"width:100px\">" . 
				input_textbox('et_fee', money_or_blank($client['ET_FEE']), $sz_small, 10, "$onchange_mon $taborder") . "</td><td></td>
		</tr>
		<tr>
			";
			$taborder = "tabindex=\"" . ($tabindex++) . "\"";
			print "
			<td></td><td style=\"width:100px\">Trace Success</td><td $ar style=\"width:100px\">" . 
				input_textbox('tr_fee', money_or_blank($client['TR_FEE']), $sz_small, 10, "$onchange_mon $taborder") . "</td><td></td>
		</tr>
		<tr>
			";
			$taborder = "tabindex=\"" . ($tabindex++) . "\"";
			print "
			<td></td><td>No-Trace</td><td $ar style=\"width:100px\">" . 
				input_textbox('nt_fee', money_or_blank($client['NT_FEE']), $sz_small, 10, "$onchange_mon $taborder") . "</td><td></td>
		</tr>
		<tr>
			";
			$taborder = "tabindex=\"" . ($tabindex++) . "\"";
			print "
			<td></td><td>Trace & Coll.</td><td $ar style=\"width:100px\">" . 
				input_textbox('tc_fee', money_or_blank($client['TC_FEE']), $sz_small, 10, "$onchange_mon $taborder") . "</td><td></td>
		</tr>
		<tr>
			";
			$taborder = "tabindex=\"" . ($tabindex++) . "\"";
			print "
			<td></td><td>Repossession</td><td $ar style=\"width:100px\">" . 
				input_textbox('rp_fee', money_or_blank($client['RP_FEE']), $sz_small, 10, "$onchange_mon $taborder") . "</td><td></td>
		</tr>
		<tr>
			";
			$taborder = "tabindex=\"" . ($tabindex++) . "\"";
			print "
			<td></td><td>Service</td><td $ar style=\"width:100px\">" . 
				input_textbox('sv_fee', money_or_blank($client['SV_FEE']), $sz_small, 10, "$onchange_mon $taborder") . "</td><td></td>
		</tr>
		<tr>
			";
			$taborder = "tabindex=\"" . ($tabindex++) . "\"";
			print "
			<td></td><td>Means</td><td $ar style=\"width:100px\">" . 
				input_textbox('mn_fee', money_or_blank($client['MN_FEE']), $sz_small, 10, "$onchange_mon $taborder") . "</td><td></td>
		</tr>
		<tr>
			";
			$taborder = "tabindex=\"" . ($tabindex++) . "\"";
			print "
			<td></td><td>Trace & Means</td><td $ar style=\"width:100px\">" . 
				input_textbox('tm_fee', money_or_blank($client['TM_FEE']), $sz_small, 10, "$onchange_mon $taborder") . "</td><td></td>
		</tr>
		<tr>
			";
			$taborder = "tabindex=\"" . ($tabindex++) . "\"";
			print "
			<td></td><td>Attend</td><td $ar style=\"width:100px\">" . 
				input_textbox('at_fee', money_or_blank($client['AT_FEE']), $sz_small, 10, "$onchange_mon $taborder") . "</td><td></td>
		</tr>
		</table><!--table_client_fees-->
		";
	}
	
	if ($half == 2)
	{
		print "
		<table id=\"table_target_fees\" class=\"basic_table\" border=\"0\"><!---->
		<tr>
			<td $at>Target Fees</td>
		</tr>
		<tr>
			<td></td>
			<td $col3>
				<table class=\"basic_table\">
					<tr><th>Selected</th><th>Job Type</th><th>Target</th><th>Hours</th><th>Fee</th>
						" . ($user_debug ? "<th $grey>DB ID</th>" : '') . "
					</tr>
					";
					foreach ($client['TARGETS'] as $target)
					{
						$id = $target['JOB_TARGET_ID'];
						$onchange_tck_tar = ($editing ? "update_target(this,$id,'t')" : '');
						$onchange_mon_tar = ($editing ? "onchange=\"update_target(this,$id,'m');\"" : 'readonly') . ' ' . $style_r;
						$tarfee_tck = '';
						$tarfee_fee = '';
						if ((1 <= $id) && ($id <= 4))
						{
							$tarfee_tck = "class=\"tarfee\" tarfee=\"tck_{$id}\"";
							$tarfee_fee = "class=\"tarfee\" tarfee=\"fee_{$id}\"";
						}
						print "
						<tr>
							";
							$taborder = "tabindex=\"" . ($tabindex++) . "\"";
							print "
							<td $ac>" . input_tickbox('', "ct_fee_tck", 1, $target['SELECTED'], $onchange_tck_tar, "$extra_tck_tar $tarfee_tck $taborder") . "
									" . input_hidden("ct_fee_ht_{$id}", $target['SELECTED'] ? 1 : 0) . "</td>
							<td $ac>{$target['JT_TYPE']}</td>
							<td $ac>{$target['JTA_NAME']}</td>
							<td $ar>{$target['JTA_TIME']}</td>
							";
							$taborder = "tabindex=\"" . ($tabindex++) . "\"";
							print "
							<td $ar>" . input_textbox('ct_fee', 
											money_format_kdb($target['JTA_FEE'], true, true, true), 5, 10, "$onchange_mon_tar $tarfee_fee $taborder") . "</td>
							" . ($user_debug ? "<td $ar $grey>$id</td>" : '') . "
						</tr>
						";
					}
					print "
				</table>
			</td>
		</tr>
		</table><!--table_target_fees-->
		";
	}
	return $tabindex;
} # print_one_client_fees()

function phone_add()
{
	# Add a blank phone number to one client contact
	
	global $client2_id;
	global $sqlFalse;
	
	if (0 < $client2_id)
	{
		$client_contact_id = post_val('temp_text_1', true);
		if (0 < $client_contact_id)
		{
			sql_encryption_preparation('CLIENT_CONTACT_PHONE');
			$phone = sql_encrypt('NUMBER', false, 'CLIENT_CONTACT_PHONE');
			$descr = sql_encrypt('DESCRIPTION', false, 'CLIENT_CONTACT_PHONE');
			$fields = "CLIENT_CONTACT_ID,  CP_PHONE, CP_DESCR, CP_MAIN,   IMPORTED,  OBSOLETE";
			$values = "$client_contact_id, $phone,   $descr,   $sqlFalse, $sqlFalse, $sqlFalse";
			$sql = "INSERT INTO CLIENT_CONTACT_PHONE ($fields) VALUES ($values)";
			dprint($sql);#
			audit_setup_client($client2_id, 'CLIENT_CONTACT_PHONE', 'CLIENT_CONTACT_PHONE_ID', 0, '', '');
			sql_execute($sql, true); # audited
			sql_update_client($client2_id);
		}
		else
			dprint("phone_add(): bad contact ID \"" . post_val('temp_text_1') . "\"", true);
	}
	else
		dprint("phone_add(): bad client ID \"$client2_id\"", true);
} # phone_add()

function phone_restore()
{
	# Make one client contact phone record un-obsolete
	
	global $client2_id;
	global $sqlFalse;
	
	if (0 < $client2_id)
	{
		$client_contact_phone_id = post_val('temp_text_1', true);
		if (0 < $client_contact_phone_id)
		{
			$sql = "UPDATE CLIENT_CONTACT_PHONE SET OBSOLETE=$sqlFalse WHERE CLIENT_CONTACT_PHONE_ID=$client_contact_phone_id";
			dprint($sql);#
			audit_setup_client($client2_id, 'CLIENT_CONTACT_PHONE', 'CLIENT_CONTACT_PHONE_ID', $client_contact_phone_id, 'OBSOLETE', $sqlFalse);
			sql_execute($sql, true); # audited
			sql_update_client($client2_id);
		}
		else
			dprint("phone_restore(): bad phone ID \"" . post_val('temp_text_1') . "\"", true);
	}
	else
		dprint("phone_restore(): bad client ID \"$client2_id\"", true);
} # phone_restore()

function phone_delete()
{
	# Make one client contact phone record obsolete
	
	global $client2_id;
	global $sqlFalse;
	global $sqlTrue;
	
	if (0 < $client2_id)
	{
		$client_contact_phone_id = post_val('temp_text_1', true);
		if (0 < $client_contact_phone_id)
		{
			$sql = "UPDATE CLIENT_CONTACT_PHONE SET CP_MAIN=$sqlFalse WHERE CLIENT_CONTACT_PHONE_ID=$client_contact_phone_id";
			dprint($sql);#
			audit_setup_client($client2_id, 'CLIENT_CONTACT_PHONE', 'CLIENT_CONTACT_PHONE_ID', $client_contact_phone_id, 'CP_MAIN', $sqlFalse);
			sql_execute($sql, true); # audited

			$sql = "UPDATE CLIENT_CONTACT_PHONE SET OBSOLETE=$sqlTrue WHERE CLIENT_CONTACT_PHONE_ID=$client_contact_phone_id";
			dprint($sql);#
			audit_setup_client($client2_id, 'CLIENT_CONTACT_PHONE', 'CLIENT_CONTACT_PHONE_ID', $client_contact_phone_id, 'OBSOLETE', $sqlTrue);
			sql_execute($sql, true); # audited
			
			sql_update_client($client2_id);
		}
		else
			dprint("phone_delete(): bad phone ID \"" . post_val('temp_text_1') . "\"", true);
	}
	else
		dprint("phone_delete(): bad client ID \"$client2_id\"", true);
} # phone_delete()

function create_new_group()
{
	# Create a new Client Group and add the current client to it.
	
	global $client2_id;

	if (0 < $client2_id)
	{
		$group_name = post_val('temp_text_1');
		if ($group_name)
		{
			$group_name = quote_smart($group_name, true);
			$sql = "SELECT COUNT(*) FROM CLIENT_GROUP WHERE GROUP_NAME=$group_name";
			sql_execute($sql);
			$count = 0;
			while (($newArray = sql_fetch()) != false)
				$count = $newArray[0];
			if ($count == 0)
			{
				$group_name_enc = sql_encrypt($group_name, true, 'CLIENT_GROUP');
				sql_encryption_preparation('CLIENT_GROUP');
				$sql = "INSERT INTO CLIENT_GROUP (GROUP_NAME) VALUES ($group_name_enc)";
				#dprint($sql);#
				audit_setup_client($client2_id, 'CLIENT_GROUP', 'CLIENT_GROUP_ID', 0, '', '');
				$client_group_id = sql_execute($sql, true); # audited
				if (0 < $client_group_id)
				{
					$sql = "UPDATE CLIENT2 SET CLIENT_GROUP_ID=$client_group_id WHERE CLIENT2_ID=$client2_id";
					#dprint($sql);#
					audit_setup_client($client2_id, 'CLIENT2', 'CLIENT2_ID', $client2_id, 'CLIENT_GROUP_ID', $client_group_id);
					sql_execute($sql, true); # audited
					sql_update_client($client2_id);
				}
				else
					dprint("Task create_new_group: no ID after inserting new record with name $group_name.", true);
			}
			else
				dprint("Cannot create new group with name $group_name: that name is already in use.", true);
		}
		else
			dprint("Task create_new_group: bad group name", true);
	}
	else
		dprint("Task create_new_group: bad client_id \"$client2_id\"", true);
	
} # create_new_group()

function contact_add()
{
	# Add a new client contact
	
	global $client2_id;
	global $sqlFalse;
	
	if (0 < $client2_id)
	{
		sql_encryption_preparation('CLIENT_CONTACT');
		$title = "'Mr'";
		$firstname = sql_encrypt('FIRST_NAME', false, 'CLIENT_CONTACT');
		$lastname = sql_encrypt('LAST_NAME', false, 'CLIENT_CONTACT');
		$fields = "CLIENT2_ID,  CC_TITLE, CC_FIRSTNAME, CC_LASTNAME, IMPORTED";
		$values = "$client2_id, $title,   $firstname,   $lastname,   $sqlFalse";
		$sql = "INSERT INTO CLIENT_CONTACT ($fields) VALUES ($values)";
		dprint($sql);#
		audit_setup_client($client2_id, 'CLIENT_CONTACT', 'CLIENT_CONTACT_ID', 0, '', '');
		sql_execute($sql, true); # audited
		sql_update_client($client2_id);
	}
	else
		dprint("contact_add(): bad client id \"$client2_id\"", true);
} # contact_add()

function print_one_client_notes($editing, $tabindex)
{
	# Called from print_one_client()
	#global $ar;
	global $client; # set by print_one_client()
#	global $col5;
#	global $sz_med_1;
	#global $sz_med_2;
	#global $sz_long;
	#global $sz_small;

	$user_debug = user_debug();

	print "
	<table class=\"basic_table\" id=\"table_client_notes\" border=\"0\"><!---->
	<tr>
		<td>
			";
			if (count($client['NOTES']) > 0)
			{
				print "
				<table class=\"spaced_table\">
					";
					foreach ($client['NOTES'] as $note_id => $noteinfo)
					{
						$onchange_txt = ($editing ? "onchange=\"update_note(this,$note_id);\"" : 'readonly');
						$created_dt = date_for_sql($noteinfo['CN_ADDED_DT'], true, true, true, false, false, true, false, true, false);
						$updated_dt = date_for_sql($noteinfo['CN_UPDATED_DT'], true, true, true, false, false, true, false, true, false);
						$note = $noteinfo['CN_NOTE'];
						$taborder = "tabindex=\"" . ($tabindex++) . "\"";
						print "<tr>
									<td>" . input_textarea("cn_note", 5, 60, $note, "$onchange_txt $taborder") . "</td>
									<td>Created:
										<br>&nbsp;&nbsp;{$noteinfo['USERNAME_C']}
										<br>&nbsp;&nbsp;$created_dt
										<br>Updated:
										<br>&nbsp;&nbsp;{$noteinfo['USERNAME_U']}
										<br>&nbsp;&nbsp;$updated_dt
									</td>
									" . ($user_debug ? "<td>$note_id</td>" : '') . "
								</tr>
								";
					}
					print "
				</table>
				";
			}
			elseif (!$client['IMPORTED'])
				print "No notes yet.";
			print "
		</td>
	</tr>
	";
	if ($client['IMPORTED'])
	{
		print "
		<tr>
			<td>Imported Notes</td>
		</tr>
		<tr>
			<td>
			";
			$imp_notes = pound_clean($client['NOTES_IMP'] . $client['NOTES_IMP_2'] . $client['NOTES_IMP_3'] . $client['NOTES_IMP_4'], 1);
			$taborder = "tabindex=\"" . ($tabindex++) . "\"";
			print input_textarea('imported_notes', 8, 80, $imp_notes, "readonly $taborder");
			print "
			</td>
		</tr>
		";
	}
	print "
	</table><!--table_client_notes-->
	";
	return $tabindex;
	
} # print_one_client_notes()

function note_add()
{
	# Add a new client note
	
	global $client2_id;
	global $sqlFalse;
	global $sqlNow;
	global $USER;
	
	if (0 < $client2_id)
	{
		sql_encryption_preparation('CLIENT_NOTE');
		$note = sql_encrypt('NEW NOTE', false, 'CLIENT_NOTE');
		$fields = "CLIENT2_ID,  CN_NOTE, IMPORTED,  CN_ADDED_ID,        CN_ADDED_DT";
		$values = "$client2_id, $note,   $sqlFalse, {$USER['USER_ID']}, $sqlNow";
		$sql = "INSERT INTO CLIENT_NOTE ($fields) VALUES ($values)";
		dprint($sql);#
		audit_setup_client($client2_id, 'CLIENT_NOTE', 'CLIENT_NOTE_ID', 0, '', '');
		sql_execute($sql, true); # audited
		sql_update_client($client2_id);
	}
	else
		dprint("note_add(): bad client id \"$client2_id\"", true);
} # note_add()

function print_one_client_accounts($editing, $tabindex)
{
	# Called from print_one_client()
	
	global $ac;
	global $ar;
	global $client; # set by print_one_client()
	global $grey;
	global $ro_colour;
	global $style_r;
	global $sz_med_1;
	#global $sz_med_2;
	#global $sz_long;
	global $sz_small;

	$payments = client_uninvoiced_payments($client['CLIENT2_ID']);
	if ($payments)
	{
		$uninvoiced_payments_txt = "This client has had [collection] payments by subjects which<br>" .
									"do not appear on any invoice (see below).<br>" .
									"Therefore the client is due a statement invoice in the future.";
		$tab_h = 30 + (25 * count($payments));
		if (100 < $tab_h)
			$tab_h = 100;
		$table = "
		<div style=\"height:{$tab_h}px; overflow-y:scroll;\">
		<table class=\"basic_table\">
		<tr><th>Job</th><th></th><th>Paid</th><th></th><th>Amount</th><th></th><th>Route</th><th></th><th $grey>ID</th></tr>
		";
		$tab_gap = "<td width=\"10\"></td>";
		$total = 0.0;
		foreach ($payments as $one_pay)
		{
			$total += floatval($one_pay['COL_AMT_RX']);
			$table .= "
			<tr><td $ar>{$one_pay['J_VILNO']}</td>$tab_gap
				<td $ar>" . date_for_sql($one_pay['COL_DT_RX'], true, false, true) . "</td>$tab_gap
				<td $ar>" . money_format_kdb($one_pay['COL_AMT_RX'], true, true, true) . "</td>$tab_gap
				<td $ac>{$one_pay['PR_CODE']}</td>$tab_gap
				<td $grey $ar>{$one_pay['JOB_PAYMENT_ID']}</td>
				</tr>";
		}
		$table .= "
		</table>
		</div>
		";
		$uninvoiced_payments_txt .= ("<br>There are " . count($payments) . " payments totalling " . money_format_kdb($total, true, true, true) . $table);
	}
	else
		$uninvoiced_payments_txt = "&nbsp;None";
	
	$billings = client_uninvoiced_billings($client['CLIENT2_ID']);
	if ($billings)
	{
		$uninvoiced_billings_txt = "This client has [trace] billings which<br>" .
									"do not appear on any invoice (see below).<br>" .
									"Therefore the client is due a statement invoice in the future.";
		$tab_h = 30 + (25 * count($billings));
		if (100 < $tab_h)
			$tab_h = 100;
		$table = "
		<div style=\"height:{$tab_h}px; overflow-y:scroll;\">
		<table class=\"basic_table\">
		<tr><th>Job</th><th></th><th>Type</th><th></th><th>Closed</th><th></th><th>Cost</th><th></th><th $grey>ID</th></tr>
		";
		$tab_gap = "<td width=\"10\"></td>";
		$total = 0.0;
		foreach ($billings as $one_bill)
		{
			$total += floatval($one_bill['BL_COST']);
			$table .= "
			<tr><td $ar>{$one_bill['J_VILNO']}</td>$tab_gap
				<td>{$one_bill['JT_CODE']}</td>$tab_gap
				<td $ar>" . date_for_sql($one_bill['J_CLOSED_DT'], true, false, true) . "</td>$tab_gap
				<td $ar>" . money_format_kdb($one_bill['BL_COST'], true, true, true) . "</td>$tab_gap
				<td $grey $ar>{$one_bill['INV_BILLING_ID']}</td>
				</tr>";
		}
		$table .= "
		</table>
		</div>
		";
		$uninvoiced_billings_txt .= ("<br>There are " . count($billings) . " billings totalling " . money_format_kdb($total, true, true, true) . $table);
	}
	else
		$uninvoiced_billings_txt = "&nbsp;None";
	
	$last_stmt_inv = client_last_stmt_invoice($client['CLIENT2_ID']);
	if ($last_stmt_inv)
		$last_stmt_inv = "Invoice {$last_stmt_inv['INV_NUM']} on " . date_for_sql($last_stmt_inv['INV_DT'], true, false, true) . "
							<span style=\"color:grey\">(ID {$last_stmt_inv['INVOICE_ID']})</span>";
	else
		$last_stmt_inv = "None";
	
	$onchange_txt = ($editing ? "onchange=\"update_client(this);\"" : 'readonly');
	$onchange_dt = ($editing ? "onchange=\"update_client(this,'d');\"" : 'readonly');
	#$onchange_num = ($editing ? "onchange=\"update_client(this,'n');\"" : 'readonly');
	$onchange_sel_txt = ($editing ? "onchange=\"update_client(this);\"" : 'disabled');
	#$onchange_mon = ($editing ? "onchange=\"update_client(this,'m');\"" : 'readonly');
	$onchange_per = ($editing ? "onchange=\"update_client(this,'p');\"" : 'readonly');
	$onchange_tck = ($editing ? "update_client(this,'t')" : '');
	$extra_tck = ($editing ? '' : 'disabled');
	print "
	<table class=\"basic_table\" id=\"table_client_accounts\" border=\"0\"><!---->
	<tr>
		<td width=\"50\">&nbsp;</td>
		<td>Statement<br>Invoices</td><td>
			";
			$taborder = "tabindex=\"" . ($tabindex++) . "\"";
			print "
			" . input_tickbox('', "s_invs_trace", 1, $client['S_INVS_TRACE'], $onchange_tck, "$extra_tck $taborder") . "
			&nbsp;(for trace jobs)
		</td>
	</tr>
	<tr>
		<td></td>
		<td>Uninvoiced<br>Payments</td><td>
			<div style=\"border:1px solid grey; color:$ro_colour;\">$uninvoiced_payments_txt</div>
		</td>
	</tr>
	<tr>
		<td></td>
		<td>Uninvoiced<br>Billings</td><td>
			<div style=\"border:1px solid grey; color:$ro_colour;\">$uninvoiced_billings_txt</div>
		</td>
	</tr>
	<tr>
		<td></td>
		<td>Statement<br>Frequency</td>
			";
			$taborder = "tabindex=\"" . ($tabindex++) . "\"";
			print "
		<td>
			" . input_select('inv_stmt_freq', stmt_freq_list(), $client['INV_STMT_FREQ'], "$onchange_sel_txt $taborder"). "
		</td>
	</tr>
	<tr>
		<td></td>
		<td>Next State-<br>ment Date</td>
			";
			$taborder = "tabindex=\"" . ($tabindex++) . "\"";
			print "
		<td>
			" . input_textbox('inv_next_stmt_dt', date_for_sql($client['INV_NEXT_STMT_DT'], true, false, true), $sz_small, 10, "$onchange_dt $taborder") . "
		</td>
	</tr>
	<tr>
		<td></td>
		<td>Previous<br>Statement</td>
		<td>
			<div style=\"inline; border:1px solid grey; color:$ro_colour;\">&nbsp;$last_stmt_inv</div>
		</td>
	</tr>
	<tr>
		<td></td>
		<td>Deduct at<br>Source</td>
			";
			$taborder = "tabindex=\"" . ($tabindex++) . "\"";
			print "
		<td>
			" . input_tickbox('', 'deduct_as', 1, $client['DEDUCT_AS'], $onchange_tck, "$extra_tck $taborder") . "
		</td>
	</tr>
	<tr>
		<td></td>
			";
			$taborder = "tabindex=\"" . ($tabindex++) . "\"";
			print "
		<td>Pay by BACS</td><td>
			" . input_tickbox('', 'c_bacs', 1, $client['C_BACS'], $onchange_tck, "$extra_tck $taborder") . "
		</td>
	</tr>
	<tr>
		<td></td>
		<td>Charged VAT</td>
			";
			$taborder = "tabindex=\"" . ($tabindex++) . "\"";
			print "
		<td>
			" . input_tickbox('', 'c_vat', 1, $client['C_VAT'], $onchange_tck, "$extra_tck $taborder") . "
		</td>
	</tr>
	<tr>
		<td width=\"50\">&nbsp;</td>
		<td>Invoices<br>Emailed</td>
			";
			$taborder = "tabindex=\"" . ($tabindex++) . "\"";
			print "
		<td>
			" . input_tickbox('', "inv_emailed", 1, $client['INV_EMAILED'], $onchange_tck, "$extra_tck $taborder") . "
		</td>
	</tr>
	<tr>
		<td width=\"50\">&nbsp;</td>
		<td>Invoice<br>Attachments<br>Combined</td>
			";
			$taborder = "tabindex=\"" . ($tabindex++) . "\"";
			print "
		<td>
			" . input_tickbox('', "inv_combine", 1, $client['INV_COMBINE'], $onchange_tck, "$extra_tck $taborder") . "
		</td>
	</tr>
	<tr>
		<td width=\"50\">&nbsp;</td>
		<td>Branch<br>Invoices<br>Combined</td>
			";
			$taborder = "tabindex=\"" . ($tabindex++) . "\"";
			print "
		<td>
			" . input_tickbox('', "inv_branch_comb", 1, $client['INV_BRANCH_COMB'], $onchange_tck, "$extra_tck $taborder") . "
		</td>
	</tr>
	<tr>
		<td></td>
		<td>Invoice Email<br>Address</td>
			";
			$taborder = "tabindex=\"" . ($tabindex++) . "\"";
			print "
		<td>
			" . input_textbox('inv_email_addr', $client['INV_EMAIL_ADDR'], $sz_med_1, 100, "$onchange_txt $taborder") . "
		</td>
	</tr>
	<tr>
		<td></td>
		<td>Invoice Email<br>Name</td>
			";
			$taborder = "tabindex=\"" . ($tabindex++) . "\"";
			print "
		<td>
			" . input_textbox('inv_email_name', $client['INV_EMAIL_NAME'], $sz_med_1, 100, "$onchange_txt $taborder") . "
		</td>
	</tr>
	<tr>
		<td></td>
		<td>Bank Name</td>
			";
			$taborder = "tabindex=\"" . ($tabindex++) . "\"";
			print "
		<td>
			" . input_textbox('c_bank_name', $client['C_BANK_NAME'], $sz_med_1, 100, "$onchange_txt $taborder") . "
		</td>
	</tr>
	<tr>
		<td></td>
		<td>Bank Sortcode</td>
			";
			$taborder = "tabindex=\"" . ($tabindex++) . "\"";
			print "
		<td>
			" . input_textbox('c_bank_sortcode', $client['C_BANK_SORTCODE'], $sz_med_1, 20, "$onchange_txt $taborder") . "
		</td>
	</tr>
	<tr>
		<td></td>
		<td>Bank Acc. No.</td>
			";
			$taborder = "tabindex=\"" . ($tabindex++) . "\"";
			print "
		<td>
			" . input_textbox('c_bank_acc_num', $client['C_BANK_ACC_NUM'], $sz_med_1, 20, "$onchange_txt $taborder") . "
		</td>
	</tr>
	<tr>
		<td></td>
		<td>Bank Acc. Name</td>
			";
			$taborder = "tabindex=\"" . ($tabindex++) . "\"";
			print "
		<td>
			" . input_textbox('c_bank_acc_name', $client['C_BANK_ACC_NAME'], $sz_med_1, 100, "$onchange_txt $taborder") . "
		</td>
	</tr>
	<tr>
		<td></td>
		<td>Bank Country<br>(if not UK)</td>
			";
			$taborder = "tabindex=\"" . ($tabindex++) . "\"";
			print "
		<td>
			" . input_textbox('c_bank_country', $client['C_BANK_COUNTRY'], $sz_med_1, 100, "$onchange_txt $taborder") . "
		</td>
	</tr>
	<tr>
		<td></td>
		<td>SWIFT Code.</td>
			";
			$taborder = "tabindex=\"" . ($tabindex++) . "\"";
			print "
		<td>
			" . input_textbox('c_bank_swift', $client['C_BANK_SWIFT'], $sz_med_1, 50, "$onchange_txt $taborder") . "
		</td>
	</tr>
	<tr>
		<td></td>
		<td>IBAN No.</td>
			";
			$taborder = "tabindex=\"" . ($tabindex++) . "\"";
			print "
		<td>
			" . input_textbox('c_bank_iban', $client['C_BANK_IBAN'], $sz_med_1, 100, "$onchange_txt $taborder") . "
		</td>
	</tr>
	<tr>
		<td></td>
		<td>Commission %</td>
			";
			$taborder = "tabindex=\"" . ($tabindex++) . "\"";
			print "
		<td>
			" . input_textbox('comm_percent', "{$client['COMM_PERCENT']}%", $sz_small, 10, "$onchange_per $style_r $taborder") . "
		</td>
	</tr>
	<tr>
		<td width=\"50\">&nbsp;</td>
		<td>Closeout<br>Report<br>Reminder</td>
			";
			$taborder = "tabindex=\"" . ($tabindex++) . "\"";
			print "
		<td>
			" . input_tickbox('', "c_closeout", 1, $client['C_CLOSEOUT'], $onchange_tck, "$extra_tck $taborder") . "
		</td>
	</tr>
	</table><!--table_client_accounts-->
	";
	return $tabindex;
	
} # print_one_client_accounts()

function create_new_client()
{
	# Allow user to create a new client, typing in just the essential information.
	# After clicking SAVE the user will be presented with the normal Edit screen.
	
	global $sqlFalse;
	global $sqlNow;
	global $sqlTrue;

	$c_code = 0;
	$sql = "SELECT MAX(C_CODE) FROM CLIENT2 WHERE C_CODE < 9000";
	sql_execute($sql);
	while (($newArray = sql_fetch()) != false)
		$c_code = intval($newArray[0]) + 1;
	if ((9000 <= $c_code) && ($c_code <= 9999))
	{
		$c_code = 0;
		$sql = "SELECT MAX(C_CODE) FROM CLIENT2 WHERE 9999 < C_CODE";
		sql_execute($sql);
		while (($newArray = sql_fetch()) != false)
		{
			$temp = intval($newArray[0]); # may be NULL turning to zero
			if (0 < $temp)
				$c_code = $temp + 1;
			else
				$c_code = 10000;
		}
		if ($c_code == 0)
			$c_code = 10000;
	}
	
	$c_co_name = sql_encrypt('NEW CLIENT', '', 'CLIENT2');
	$com_percent = 0.0;
	
	$tr_fee = 0.0;
	$sql = "SELECT JT_FEE FROM JOB_TYPE_SD WHERE JT_CODE='TRC'";
	sql_execute($sql);
	while (($newArray = sql_fetch()) != false)
		$tr_fee = floatval($newArray[0]);
	
	$nt_fee = 0.0;
	
	$tc_fee = 0.0;
	$sql = "SELECT JT_FEE FROM JOB_TYPE_SD WHERE JT_CODE='T/C'";
	sql_execute($sql);
	while (($newArray = sql_fetch()) != false)
		$tc_fee = floatval($newArray[0]);
	
	$rp_fee = 0.0;
	$sql = "SELECT JT_FEE FROM JOB_TYPE_SD WHERE JT_CODE='RPO'";
	sql_execute($sql);
	while (($newArray = sql_fetch()) != false)
		$rp_fee = floatval($newArray[0]);
	
	$sv_fee = 0.0;
	$sql = "SELECT JT_FEE FROM JOB_TYPE_SD WHERE JT_CODE='SVC'";
	sql_execute($sql);
	while (($newArray = sql_fetch()) != false)
		$sv_fee = floatval($newArray[0]);
	
	$mn_fee = 0.0;
	$sql = "SELECT JT_FEE FROM JOB_TYPE_SD WHERE JT_CODE='MNS'";
	sql_execute($sql);
	while (($newArray = sql_fetch()) != false)
		$mn_fee = floatval($newArray[0]);
	
	$tm_fee = 0.0;
	$sql = "SELECT JT_FEE FROM JOB_TYPE_SD WHERE JT_CODE='T/M'";
	sql_execute($sql);
	while (($newArray = sql_fetch()) != false)
		$tm_fee = floatval($newArray[0]);
	
	$at_fee = 0.0;
	$sql = "SELECT JT_FEE FROM JOB_TYPE_SD WHERE JT_CODE='ATT'";
	sql_execute($sql);
	while (($newArray = sql_fetch()) != false)
		$at_fee = floatval($newArray[0]);
	
	$et_fee = 0.0;
	$sql = "SELECT JT_FEE FROM JOB_TYPE_SD WHERE JT_CODE='ETR'";
	sql_execute($sql);
	while (($newArray = sql_fetch()) != false)
		$et_fee = floatval($newArray[0]);
	
	$fields = "C_CODE,  C_CO_NAME,  COMM_PERCENT, TR_FEE,  NT_FEE,  TC_FEE,  RP_FEE,  SV_FEE,  MN_FEE,  TM_FEE,  AT_FEE,  ET_FEE,  ";
	$values = "$c_code, $c_co_name, $com_percent, $tr_fee, $nt_fee, $tc_fee, $rp_fee, $sv_fee, $mn_fee, $tm_fee, $at_fee, $et_fee, ";
	
	$fields .= "CREATED_DT, IMPORTED,  PORTAL_PUSH";
	$values .= "$sqlNow,    $sqlFalse, $sqlTrue   ";
	
	sql_encryption_preparation('CLIENT2');
	$sql = "INSERT INTO CLIENT2 ($fields) VALUES ($values)";
	audit_setup_gen('CLIENT2', 'CLIENT2_ID', 0, '', '');
	$new_id = sql_execute($sql, true); # audited
	dprint("$sql -> ID $new_id");#
	if (!(0 < $new_id))
		dprint("***FAILED TO CREATE NEW CLIENT FROM: $sql", true);
	
	if (0 < $new_id)
	{
		# Create new Portal User
		sql_create_portal_user($new_id, $c_code);
	}
	
	return $new_id;
	
} # create_new_client()

function create_statements($sys)
{
	global $manager_a;
	global $ticked_clients;

	#dprint("Creating statements for: " . print_r($ticked_clients,1) . " - not yet implemented");#

	if (!$manager_a)
	{
		dprint("You do not have access to this function", true);#
		return;
	}
	
	foreach ($ticked_clients as $client2_id)
	{
		$client_name = client_name_from_id($client2_id);
//		if ($agent_c)
		if ($sys == 't')
		{
			# Create statement invoice for any Trace jobs for this client
			$inv_num = statement_trace($client2_id);
			if (0 < $inv_num)
			{
				dprint("Trace Invoice #{$inv_num} has been created for client {$client_name['C_CODE']} ({$client_name['C_CO_NAME']})", true);
				sql_update_client($client2_id);
			}	
		}

//		if ($agent_t)
		if ($sys == 'c')
		{
			# Create statement invoice for any Collect jobs for this client
			$inv_num = statement_collect($client2_id);
			if (0 < $inv_num)
			{
				dprint("Collection Invoice #{$inv_num} has been created for client {$client_name['C_CODE']} ({$client_name['C_CO_NAME']})", true);
				sql_update_client($client2_id);
			}
		}
	} # foreach ($ticked_clients)
	
} # create_statements()

function statement_trace($client2_id)
{
	# Create a Statement Invoice for trace jobs for the given client.

	global $sqlFalse;
	global $sqlTrue;

	#dprint("statement_trace($client2_id) - enter");#

	$c_vat = (intval(sql_select_single("SELECT C_VAT FROM CLIENT2 WHERE CLIENT2_ID=$client2_id")) ? true : false);
	if ($c_vat)
		$vat_rate = 0.01 * floatval(misc_info_read('*', 'VAT_RATE'));
	else
		$vat_rate = 0.0;
	
	$billings = client_uninvoiced_billings($client2_id);
	$inv_net = 0.0;
	$inv_vat = 0.0;
	$inv_start_dt = '';
	$inv_end_dt = '';
	foreach ($billings as $one_bill)
	{
		$amount = round(floatval($one_bill['BL_COST']), 2);
		$inv_net += $amount;
		if (($inv_start_dt == '') || ($one_bill['J_CLOSED_DT'] < $inv_start_dt))
			$inv_start_dt = $one_bill['J_CLOSED_DT'];
		if (($inv_end_dt == '') || ($inv_end_dt < $one_bill['J_CLOSED_DT']))
			$inv_end_dt = $one_bill['J_CLOSED_DT'];
	}
	
	if ($billings)
	{
		$now = date_now_sql();
		$inv_dt = "'$now'";
		#$inv_dt = "'" . date_now_sql(true) . "'";

		$due_ep = date_add_months_kdb(time(), 1); # add on one month
		$due_dt = date_from_epoch(true, $due_ep, false, false, true);
		$inv_due_dt = "'$due_dt'";
		#$inv_due_dt = "'" . date_from_epoch(true, time() + (30 * 24* 60 * 60), false, false, true) . "'";
		
		$inv_net = round($inv_net, 2);
		$inv_vat = round($vat_rate * $inv_net, 2);
		$inv_start_dt = "'{$inv_start_dt}'";
		$inv_end_dt = "'{$inv_end_dt}'";
		$inv_notes = sql_encrypt("Invoice created automatically from Create Statements button on Clients screen, " . date_now(), false, 'INVOICE');

		$inv_num = inv_num_next();

		$fields = "INV_NUM,  INV_SYS, INV_SYS_IMP, INV_TYPE, INV_DT,  INV_DUE_DT,  CLIENT2_ID,  INV_NET,  INV_VAT,  INV_STMT, ";
		$values = "$inv_num, 'T',     NULL,        'I',      $inv_dt, $inv_due_dt, $client2_id, $inv_net, $inv_vat, $sqlTrue, ";

		$fields .= "INV_START_DT,  INV_END_DT,  INV_COMPLETE, INV_NOTES,  IMPORTED";
		$values .= "$inv_start_dt, $inv_end_dt, $sqlFalse,    $inv_notes, $sqlFalse";

		sql_encryption_preparation('INVOICE');
		$sql = "INSERT INTO INVOICE ($fields) VALUES ($values)";
		dprint($sql);#
		audit_setup_client($client2_id, 'INVOICE', 'INVOICE_ID', 0, '', '');
		$invoice_id = sql_execute($sql, true); # audited
		dprint("statement_trace($client2_id) - new invoice_id=$invoice_id");

		# Update all the billing records to say that they are linked to this invoice
		foreach ($billings as $one_bill)
		{
			$inv_billing_id = $one_bill['INV_BILLING_ID'];
			$sql = "UPDATE INV_BILLING SET INVOICE_ID=$invoice_id WHERE INV_BILLING_ID=$inv_billing_id";
			#dprint($sql);#
			audit_setup_gen('INV_BILLING', 'INV_BILLING_ID', $inv_billing_id, 'INVOICE_ID', $invoice_id);
			sql_execute($sql, true); # audited
		}
	}
	else
	{
		dprint("statement_trace($client2_id) - no billings found so no invoice created");
		$inv_num = 0;
	}
	return $inv_num;
	
} # statement_trace()

function statement_collect($client2_id)
{
	# Create a Statement Invoice for collect jobs for the given client.
	
	global $sqlFalse;
	global $sqlTrue;
	
	$c_vat = (intval(sql_select_single("SELECT C_VAT FROM CLIENT2 WHERE CLIENT2_ID=$client2_id")) ? true : false);
	if ($c_vat)
		$vat_rate = 0.01 * floatval(misc_info_read('*', 'VAT_RATE'));
	else
		$vat_rate = 0.0;
	
	$payments = client_uninvoiced_payments($client2_id);
	$inv_net = 0.0;
	$inv_vat = 0.0;
	$inv_start_dt = '';
	$inv_end_dt = '';
	foreach ($payments as $one_pay)
	{
		$amount = round(floatval($one_pay['COL_AMT_RX']), 2);
		$percent = floatval($one_pay['PAY_PERCENT']);
		$commission = round(($percent / 100.0) * $amount, 2);
		$inv_net += $commission;
		if (($inv_start_dt == '') || ($one_pay['COL_DT_RX'] < $inv_start_dt))
			$inv_start_dt = $one_pay['COL_DT_RX'];
		if (($inv_end_dt == '') || ($inv_end_dt < $one_pay['COL_DT_RX']))
			$inv_end_dt = $one_pay['COL_DT_RX'];
	}
	
	#dprint("statement_collect($client2_id) - " . count($payments) . " payments");# = " . print_r($payments,1));#

	if ($payments)
	{
		$now = date_now_sql();
		$inv_dt = "'$now'";
		#$inv_dt = "'" . date_now_sql(true) . "'";

		$due_ep = date_add_months_kdb(time(), 1); # add on one month
		$due_dt = date_from_epoch(true, $due_ep, false, false, true);
		$inv_due_dt = "'$due_dt'";
		#$inv_due_dt = "'" . date_from_epoch(true, time() + (30 * 24* 60 * 60), false, false, true) . "'";
		
		$inv_net = round($inv_net, 2);
		$inv_vat = round($vat_rate * $inv_net, 2);
		$inv_start_dt = "'{$inv_start_dt}'";
		$inv_end_dt = "'{$inv_end_dt}'";
		$inv_notes = sql_encrypt("Invoice created automatically from Create Statements button on Clients screen, " . date_now(), false, 'INVOICE');

		$inv_num = inv_num_next();

		$fields = "INV_NUM,  INV_SYS, INV_SYS_IMP, INV_TYPE, INV_DT,  INV_DUE_DT,  CLIENT2_ID,  INV_NET,  INV_VAT,  INV_STMT, ";
		$values = "$inv_num, 'C',     NULL,        'I',      $inv_dt, $inv_due_dt, $client2_id, $inv_net, $inv_vat, $sqlTrue, ";

		$fields .= "INV_START_DT,  INV_END_DT,  INV_COMPLETE, INV_NOTES,  IMPORTED";
		$values .= "$inv_start_dt, $inv_end_dt, $sqlFalse,    $inv_notes, $sqlFalse";

		sql_encryption_preparation('INVOICE');
		$sql = "INSERT INTO INVOICE ($fields) VALUES ($values)";
		dprint($sql);#
		audit_setup_client($client2_id, 'INVOICE', 'INVOICE_ID', 0, '', '');
		$invoice_id = sql_execute($sql, true); # audited
		dprint("statement_collect($client2_id) - new invoice_id=$invoice_id");

		# Update all the payment records to say that they are linked to this invoice
		foreach ($payments as $one_pay)
		{
			$job_payment_id = $one_pay['JOB_PAYMENT_ID'];
			$sql = "UPDATE JOB_PAYMENT SET INVOICE_ID=$invoice_id WHERE JOB_PAYMENT_ID=$job_payment_id";
			#dprint($sql);#
			audit_setup_gen('JOB_PAYMENT', 'JOB_PAYMENT_ID', $job_payment_id, 'INVOICE_ID', $invoice_id);
			sql_execute($sql, true); # audited
		}
	}
	else
	{
		dprint("statement_collect($client2_id) - no payments found so no invoice created");
		$inv_num = 0;
	}
	return $inv_num;
	
} # statement_collect()

?>
