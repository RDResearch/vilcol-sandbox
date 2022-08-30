<?php

include_once("settings.php");
include_once("library.php");
include_once("lib_users.php");
global $denial_message;
global $navi_1_system;
global $navi_2_sys_portal;
global $role_man;
global $USER; # set by admin_verify()

log_open("vilcol.log");

sql_connect();

admin_verify(); # writes to $USER

if ($USER['IS_ENABLED'])
{
	if (user_is_super_user() && role_check('*', $role_man))
	{
		$navi_1_system = true; # settings.php; used by navi_1_heading()
		$navi_2_sys_portal = true; # settings.php; used by navi_2_heading()
		$onload = "onload=\"set_scroll();\"";
		$page_title_2 = 'Portal Users - Vilcol';
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
	global $one_user_id;
	global $page_title_2;
	global $sc_c_code;
	global $sc_text;
	global $task;
	global $tr_colour_1;

	print "<h3>System Administration</h3>";
	navi_2_heading(); # secondary navigation buttons
	print "<h3>Portal Users Screen</h3>";
	dprint(post_values());

	if (post_val('search_clicked'))
		$task = "search";
	else
		$task = post_val('task');
	$sc_c_code = post_val('sc_c_code');
	$sc_text = post_val('sc_text', false, false, false, 1);
	$sort_time = false;
	if ($sc_text == '---')
	{
		$sort_time = true;
		$sc_c_code = '';
		$sc_text = '';
	}
	$sc_dis = post_val('sc_dis', true);
	$edit = post_val('edit', true);
	$one_user_id = post_val('user_id', true);
	
	javascript();
	
	print "
	<span id=\"ajax_output\"></span>
	<div id=\"div_form_main\" style=\"background-color:{$tr_colour_1};\">
	<hr>
	<form name=\"form_main\" action=\"" . server_php_self() . "\" method=\"post\">
	" . input_hidden('task', '') . "
	" . input_hidden('user_id', '') . "
	" . input_hidden('edit', '') . "
	Search for user name: 
	" . input_textbox('sc_text', $sc_text, 20, 50, "onkeypress=\"search_prepare()\"") . # onkeydown causes a backspace to delete two characters the first time it is used!
	"
	&nbsp;&nbsp;&nbsp;
	Client(s): 
	" . input_textbox('sc_c_code', $sc_c_code, 10, 100, "onkeypress=\"search_prepare()\"") . # onkeydown causes a backspace to delete two characters the first time it is used!
	"
	" . input_button('Search', 'search_js(1)') . "
	&nbsp;&nbsp;&nbsp;...or: " . input_button('Show all users', 'search_js(0)') . "
	&nbsp;&nbsp;&nbsp;" . input_tickbox('Include disabled users', 'sc_dis', 1, $sc_dis) . "
	<hr>
	</form><!--form_main-->
	<hr>
	</div><!--div_form_main-->
	";
	
	if ($task == 'save')
	{
		if (!save_new_user())
			print "
			<h1 style=\"color:red;\">New user failed to be created - check log file for details</h1>
			";
	}
	elseif ($task == 'auto_create')
	{
		auto_create();
	}

	if (($task == 'details') && 
		(($one_user_id > 0) || (($one_user_id == 0) && ($edit == 1))))
		print_one_user();
	else 
	{
		sql_get_portal_users($sc_c_code, $sc_dis, $sc_text, $sort_time); # lib_vilcol.php
		print_users();
		$gap = "&nbsp;&nbsp;&nbsp;";
		print "
			<p>" . input_button('Create New User', 'edit_user(0,1)') . "$gap
				" . input_button('Auto-create Missing Portal Users', 'auto_create()') . "$gap
				" . #input_button('Copy Users to Portal', 'copy_users()') . 
				"
			</p>
			";
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

	global $sc_text;
	global $task;

	if (($task != 'details') && $sc_text)
	{
		print "
		<script type=\"text/javascript\">
		document.getElementById('sc_text').focus();
		document.getElementById('sc_text').setSelectionRange(100, 100); // any number larger than length of content
		</script>
		";
	}
	
} # screen_content_2()

function javascript()
{
	global $password_policy; # settings.php
	global $safe_amp;
	global $uni_pound;
	global $one_user_id;
	
	print "
	<script type=\"text/javascript\">

//	function copy_users()
//	{
//		xmlHttp2 = GetXmlHttpObject();
//		if (xmlHttp2 == null)
//			return;
//		var url = 'portal_ajax.php?op=copy_users';
//		url = url + '&ran=' + Math.random();
//		xmlHttp2.onreadystatechange = stateChanged_copy_users;
//		xmlHttp2.open('GET', url, true);
//		xmlHttp2.send(null);
//	}
//		
//	function stateChanged_copy_users()
//	{
//		if (xmlHttp2.readyState == 4)
//		{
//			var resptxt = xprint_noscript(xmlHttp2.responseText);
//			var bits = resptxt.split('|');
//			document.getElementById('ajax_output').innerHTML = '<span style=\"color:green\"><hr><h2>Copy Users</h2>' + bits[1] + '<br><br><hr><br><br></span>';
//			if (bits[0] != 'ok')
//				alert('Error: ' + bits[1]);
//		}
//	}
	
	function auto_create()
	{
		document.form_main.sc_c_code.value = '';
		document.form_main.sc_text.value = '';
		document.form_main.sc_dis.checked = true;
		document.form_main.task.value = 'auto_create';
		please_wait_on_submit();
		document.form_main.submit();
	}

	function search_js(useSC,sort_time=false)
	{
		if (useSC == 0)
		{
			document.form_main.sc_c_code.value = '';
			document.form_main.sc_text.value = '';
			document.form_main.sc_dis.checked = true;
		}
		if (sort_time)
			document.form_main.sc_text.value = '---';
		document.form_main.task.value = 'search';
		please_wait_on_submit();
		document.form_main.submit();
	}
	
	function save_new_user()
	{
		var el = document.form_edit.client2_id;
		var val = trim(el.value);
		if ((val == '') || (val == '0'))
		{
			alert('Please specify a Client');
			return;
		}
		el = document.form_edit.u_firstname;
		val = trim(el.value);
		if (val == '')
		{
			alert('Please enter a First Name');
			return;
		}
		el = document.form_edit.u_lastname;
		val = trim(el.value);
		if (val == '')
		{
			alert('Please enter a Last Name');
			return;
		}
		el = document.form_edit.username;
		val = trim(el.value);
		if (val == '')
		{
			alert('Please enter a Username');
			return;
		}
		el = document.form_edit.username;
		val = trim(el.value);
		if (val == '')
		{
			alert('Please enter a Username');
			return;
		}
		var test_result = test_password();
		if (test_result == -1)
		{
			alert('Please enter new password into Box 1');
			return;
		}
		if (test_result == -2)
		{
			alert('$password_policy');
			return;
		}
		if (test_result == -3)
		{
			alert('Please enter new password into Box 2');
			return;
		}
		if (test_result == -4)
		{
			alert('The passwords do not match');
			return;
		}
		document.form_edit.task.value = 'save';
		please_wait_on_submit();
		document.form_edit.submit();
	}
	
	function enabled_clicked(control)
	{
		if (control.checked)
			document.getElementById('enabled_id').innerHTML = 'Enabled';
		else
			document.getElementById('enabled_id').innerHTML = '<span style=\"color:red;\">Enabled</span>';
		update_user(control, 't');
	}
	
	function locked_clicked(control)
	{
		if (control.checked)
			document.getElementById('locked_id').innerHTML = '<span style=\"color:red;\">Locked out</span>';
		else
		{
			document.getElementById('locked_id').innerHTML = 'Locked out';
			var el = document.getElementById('failed_logins');
			el.value = 0;
			update_user(el);
		}
		update_user(control, 't');
	}
	
	function test_password()
	{
		document.getElementById('password').value = trim(document.getElementById('password').value);
		if (document.getElementById('password').value == '')
			return -1;
		document.getElementById('password_2').value = trim(document.getElementById('password_2').value);
		if (document.getElementById('password_2').value == '')
			return -3;
		var cp = password_test(document.getElementById('password').value);
		if (cp == '')
		{
			if (document.getElementById('password').value == document.getElementById('password_2').value)
				return 0; // all OK
			else
				return -4; // passwords differ
		}
		else
		{
			//alert(cp);
			return -2; // failed policy
		}
	}
	
	function save_password()
	{
		var test_result = test_password();
		if (test_result == -1)
			alert('Please enter new password into Box 1');
		else if (test_result == -2)
			alert('$password_policy');
		else if (test_result == -3)
			alert('Please enter new password into Box 2');
		else if (test_result == -4)
			alert('The passwords do not match');
		else
		{
			update_user(document.getElementById('password'));
			alert('The new password has been saved');
		}
	}
	
	function update_user(control, field_type, date_check)
	{
		" .
		// field_type:
		//				(blank) - default; no extra processing
		//				d = Date, optionally send date_check e.g. '<=' for on or before today
		//				m = money (optional '£')
		//				p = percentage (optional '%')
		//				n = Number (allows negatives and decimals)
		//				t = Tickbox
		//				e = Email address
		"
		var field_name = control.name;
		var field_value = trim(control.value);
		field_value = field_value.replace(/'/g, \"\\'\");
		field_value = field_value.replace(/&/g, \"\\u0026\");
		// Might need to add this: field_value = field_value.replace(/&/g, \"$safe_amp\");
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
			else if (checkDate(field_value, 'entry', date_check))
				field_value = dateToSql(field_value);
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
					
		xmlHttp2 = GetXmlHttpObject();
		if (xmlHttp2 == null)
			return;
		var url = 'users_ajax.php?op=upu&i={$one_user_id}&n=' + field_name + '&v=' + encodeURIComponent(field_value);
		url = url + '&ran=' + Math.random();
		//alert(url);
		xmlHttp2.onreadystatechange = stateChanged_update_user;
		xmlHttp2.open('GET', url, true);
		xmlHttp2.send(null);
	}
		
	function stateChanged_update_user()
	{
		if (xmlHttp2.readyState == 4)
		{
			var resptxt = xprint_noscript(xmlHttp2.responseText);
			if (resptxt != 'ok')
			{
				//if (!resptxt)
				//	resptxt = 'No response from ajax call!';
				if (resptxt)
					alert(resptxt);
			}
		}
	}
	
	function edit_user(id,edt)
	{
		document.form_main.user_id.value = id;
		document.form_main.task.value = 'details';
		document.form_main.edit.value = (edt ? '1' : '0');
		please_wait_on_submit();
		document.form_main.submit();
	}
	
	function search_prepare()
	{
		document.form_main.task.value = 'search';
	}
	
	</script>
	";
}

function save_new_user()
{
	global $sqlNow; # settings.php

	$error_txt_1 = "save_new_user(): Aborting.";
	$error_txt_2 = "from POST: " . post_values();
	
	# Get data from $_POST
	
	$client2_id = post_val('client2_id', true);
	if (!$client2_id) # may be negative
	{
		log_write("$error_txt_1 Bad CLIENT2_ID \"" . post_val('client2_id') . "\" $error_txt_2");
		return false;
	}
	$username = trim(post_val('username'));
	if (!$username)
	{
		log_write("$error_txt_1 Bad username \"" . post_val('username') . "\" $error_txt_2");
		return false;
	}
	$password = trim(post_val('password'));
	$password_test = password_test($password);
	if ($password_test)
	{
		dprint($password_test, true);
		log_write("$error_txt_1 Bad password \"" . post_val('password') . "\", $password_test $error_txt_2");
		return false;
	}
	$u_email = trim(post_val('u_email'));
	#if (!$u_email)
	#	$u_email = 'x@x.com';
	$is_enabled = post_val('is_enabled', true);
	$is_locked_out = post_val('is_locked_out', true);
	$u_notes = trim(post_val('u_notes'));
	$u_firstname = trim(post_val('u_firstname'));
	if (!$u_firstname)
	{
		log_write("$error_txt_1 Bad u_firstname \"" . post_val('u_firstname') . "\" $error_txt_2");
		return false;
	}
	$u_lastname = trim(post_val('u_lastname'));
	if (!$u_lastname)
	{
		log_write("$error_txt_1 Bad u_lastname \"" . post_val('u_lastname') . "\" $error_txt_2");
		return false;
	}
	$u_initials = trim(post_val('u_initials'));
	if (!$u_initials)
		$u_initials = $u_firstname[0] . $u_lastname[0];

	# Prepare data for SQL insertion
	
	sql_encryption_preparation('USERV');
	
	$username = sql_encrypt($username, false, 'USERV');
	$password_raw = $password;
	$password = sql_encrypt($password, false, 'USERV');
	$u_email = sql_encrypt($u_email, false, 'USERV');
	$is_enabled = ($is_enabled ? 1 : 0);
	$is_locked_out = ($is_locked_out ? 1 : 0);
	$u_notes = sql_encrypt($u_notes, false, 'USERV');
	$u_firstname = quote_smart($u_firstname);
	$u_lastname = sql_encrypt(quote_smart($u_lastname, true), true, 'USERV');
	
	$u_initials = sql_get_initials_unique($u_initials);
	$u_initials = quote_smart($u_initials);

	# SQL insertion
	
	$fields = "CLIENT2_ID,  USERNAME,  PASSWORD,  U_EMAIL,  IS_ENABLED,  IS_LOCKED_OUT,  FAILED_LOGINS, ";
	$values = "$client2_id, $username, $password, $u_email, $is_enabled, $is_locked_out, 0,             ";
	
	$fields .= "CREATED_DT, U_FIRST_DT, U_LAST_DT, U_NOTES,  U_FIRSTNAME,  U_LASTNAME,  U_INITIALS,  PORTAL_PUSH";
	$values .= "$sqlNow,    NULL,       NULL,      $u_notes, $u_firstname, $u_lastname, $u_initials, 1          ";
	
	$sql = "INSERT INTO USERV ($fields) VALUES ($values)";
	$temp = "save_new_user(): " . str_replace($password_raw, "***", $sql);
	dprint($temp);
	log_write($temp);
	# Set up audit for new record insertion
	audit_setup_gen('USERV', 'USER_ID', 0, '', '');
	$insert_id = sql_execute($sql, true); # audited
	log_write("save_new_user(): New USER_ID=$insert_id");
	return true;
}

function print_users()
{
	global $ar;
	global $col2;
	global $role_man;
	global $tr_colour_1;
	global $tr_colour_2;
	global $USER;
	global $portal_users;
	
	if (!$portal_users)
	{
		print "<p>No users matched your search criteria.</p>";
		return;
	}
	
	$e_disabled = (role_check('*', $role_man) ? '' : 'disabled');
	
	$grey = "style=\"color:gray;\"";
	print "
	<table name=\"table_users\" class=\"spaced_table\" border=\"0\">
	<tr>
		<th></th><th></th><th $col2>Client</th><th>Login</th><th>Name</th><th>Init.</th><th>Email</th><th>Enabled</th><th>Locked Out</th>
		" . (user_debug(2) ? "<th $grey onclick=\"search_js(1,true)\">Last Logged-in</th>" : '') . "
		<th $grey>DB ID</th>
	</tr>
	";
	$trcol = $tr_colour_1;
	foreach ($portal_users as $one_user)
	{
		$enabled = ($one_user['IS_ENABLED'] ? 'Yes' : 'No');
		$locked = ($one_user['IS_LOCKED_OUT'] ? 'Yes' : 'No');
		$blocked = (((!$one_user['IS_ENABLED']) || $one_user['IS_LOCKED_OUT']) ? true : false);

		if ($e_disabled && ($USER['USER_ID'] == $one_user['USER_ID']))
			$e_disabled_2 = ''; # user can edit themselves
		else
			$e_disabled_2 = $e_disabled; # the default

		print "
			<tr bgcolor=\"$trcol\" onmouseover=\"this.className='Highlight'\" onmouseout=\"this.className='Normal'\"" .
					($blocked ? "style=\"color:red;\"" : '') . ">
				<td>" . input_button('View', "edit_user({$one_user['USER_ID']},0);") . "</td>
				<td>" . input_button('Edit', "edit_user({$one_user['USER_ID']},1);", $e_disabled_2) . "</td>
				<td $ar>{$one_user['C_CODE']}</td>
				<td>{$one_user['C_CO_NAME']}</td>
				<td>{$one_user['USERNAME']}</td>
				<td>{$one_user['U_FIRSTNAME']} {$one_user['U_LASTNAME']}</td>
				<td>{$one_user['U_INITIALS']}</td>
				<td>{$one_user['U_EMAIL']}</td><td>$enabled</td><td>$locked</td>
				" . (user_debug(2) ? "<td $grey>" . date_for_sql($one_user['U_LAST_DT'], true) . "</td>" : '') . "
				<td $grey $ar>{$one_user['USER_ID']}</td>
			</tr>
			";
			$trcol = (($trcol == $tr_colour_1) ? $tr_colour_2 : $tr_colour_1);
	}
	print "
	</table><!--table_users-->
	</form><!--form_main-->
	";
}

function print_one_user()
{
	global $at;
	global $col2;
	global $col3;
	global $col6;
	global $role_man;
	global $one_user_id; # 
	
	$one_user = sql_get_one_portal_user($one_user_id);
	$sgc_cid = intval($one_user['CLIENT2_ID']);
	$clients = sql_get_clients_for_select('*', $sgc_cid, true, true);
	
	$new_user = (($one_user_id > 0) ? false : true);
	$can_edit = (post_val('edit', true) ? true : false);
	if ($can_edit && (!role_check('*', $role_man)))
	{
		$can_edit = false;
		$restricted = true;
	}
	else 
		$restricted = false;
		
	$szstd = 15;
	$szlg = 40;
	$gap = "<td width=\"50\">&nbsp;</td>";
	$grey = "style=\"color:gray;\"";
	
	if ($new_user)
	{
		$x_text = '';
		$x_email = '';
		$x_num = '';
		$x_pass = '';
		$clk_ena = '';
		$clk_loc = '';
		$clk_tick = '';
		$x_tick = '';
	}
	elseif ($can_edit)
	{
		$x_text = "onchange=\"update_user(this);\"";
		$x_email = "onchange=\"update_user(this, 'e');\"";
		$x_num = "onchange=\"update_user(this, 'n');\"";
		$x_pass = "";
		$clk_ena = "enabled_clicked(this);";
		$clk_loc = "locked_clicked(this);";
		$clk_tick = "update_user(this, 't');";
		$x_tick = "";
	}
	else 
	{
		$x_text = "disabled";
		$x_email = "disabled";
		$x_num = 'disabled';
		$x_pass = ($restricted ? '' : "disabled");
		$clk_ena = "";
		$clk_loc = "";
		$clk_tick = "";
		$x_tick = "disabled";
	}
	
	$enabled_txt = "Enabled";
	if (!$one_user['IS_ENABLED'])
		$enabled_txt = "<span style=\"color:red;\">$enabled_txt</span>";
	$enabled_txt = "<span id=\"enabled_id\">$enabled_txt</span>";
	
	$locked_txt = "Locked out";
	if ($one_user['IS_LOCKED_OUT'])
		$locked_txt = "<span style=\"color:red;\">$locked_txt</span>";
	$locked_txt = "<span id=\"locked_id\">$locked_txt</span>";
		
	print "
	<form name=\"form_edit\" action=\"" . server_php_self() . "\" method=\"post\">
	<table name=\"table_edit\" class=\"spaced_table\" border=\"0\"><!---->
		<tr>
			<td>Client</td>
			<td>" . input_select('client2_id', $clients, $one_user['CLIENT2_ID'], "$x_num style=\"width:350px;\"") . "</td>
		</tr>
		<tr>
			<td>First name</td>
			<td>" . input_textbox('u_firstname', $one_user['U_FIRSTNAME'], $szstd, 0, $x_text) . "</td>
			$gap
			<td $col3>First Logged in: " . ($one_user['U_FIRST_DT'] ? $one_user['U_FIRST_DT'] : 'never') . "</td>
			<td>Created on: " . 
				(($one_user['CREATED_DT'] == '01/01/1980') ? '(imported from Rialto-1)' : $one_user['CREATED_DT']) . "</td>
		</tr>
		<tr>
			<td>Last name</td>
			<td>" . input_textbox('u_lastname', $one_user['U_LASTNAME'], $szstd, 0, $x_text) . "</td>
			$gap
			<td $col3>Last Logged in: " . ($one_user['U_LAST_DT'] ? $one_user['U_LAST_DT'] : 'never') . "</td>
		</tr>
		<tr>
			<td>Initials</td>
			<td>" . input_textbox('u_initials', $one_user['U_INITIALS'], $szstd, 0, $x_text) . "</td>
		</tr>
		<tr>
			<td>Username</td>
			<td>" . input_textbox('username', $one_user['USERNAME'], $szstd, 0, $x_text) . "</td>
			";
		if ($can_edit || $restricted)
		{
			print "
			$gap
			<td $col2>Password &nbsp;
				" . input_password('password', '', $szstd, 0, $x_pass) . " &nbsp;
				" . input_password('password_2', '', $szstd, 0, $x_pass) . " &nbsp;
				" . ($new_user ? '' : input_button('Save Password', 'save_password()', $x_pass)) . "
				<br>
				To change password, please enter new password twice then click Save
			</td>
			";
		}
		print "
		</tr>
		<tr>
			<td>" . input_tickbox($enabled_txt, 'is_enabled', 1, $one_user['IS_ENABLED'], $clk_ena, $x_tick) . "</td>
			<td>" . input_tickbox($locked_txt, 'is_locked_out', 1, $one_user['IS_LOCKED_OUT'], $clk_loc, $x_tick) . "</td>
			<td></td>
			<td>" . input_hidden('failed_logins', '') . "</td>
		</tr>
		<tr>
			<td>&nbsp;</td>
		</tr>
		<tr>
			<td>Email</td>
			<td $col3>" . input_textbox('u_email', $one_user['U_EMAIL'], $szlg, 0, $x_email) . "</td>
		</tr>
		<tr>
			<td>&nbsp;</td>
		</tr>
		<tr>
			<td $at>Notes</td>
			<td $col6>" . input_textarea('u_notes', 5, 80, $one_user['U_NOTES'], $x_text) . "</td>
		</tr>
		";
		if (global_debug())
		{
			print "
			<tr>
				<td>" . input_tickbox('Debug Flag', 'u_debug', 1, $one_user['U_DEBUG'], $clk_ena, $x_tick) . "</td>
			</tr>
			<tr>
				<td>" . input_tickbox('Portal Push', 'portal_push', 1, $one_user['PORTAL_PUSH'], $clk_ena, $x_tick) . "</td>
			</tr>
			";
		}
		
		print "
		<tr>
			<td $grey>DB ID: $one_user_id</td>
		</tr>
	</table><!--table_edit-->
	";
	if ($new_user)
		print input_button('Save', 'save_new_user()') . input_hidden('task', '');
	print "
	</form><!--form_edit-->
	";
}

function auto_create()
{
	# Create one Portal User for Kevin, Gina and Steve, and every non-archived client that doesn't already have one.
	# Set it to disabled by default.
	
	global $sqlFalse;
	global $sqlNow;
	
	$client_kevin = -1;
	$count_kevin = 0;
	$client_gina = -2;
	$count_gina = 0;
	$client_steve = -3;
	$count_steve = 0;
	
	$count = sql_select_single("SELECT COUNT(*) FROM USERV WHERE CLIENT2_ID=$client_kevin");
	if ($count == 0)
	{
		$client2_id = $client_kevin;
		
		$username = "C0.Kevin";
		$username = sql_encrypt($username, false, 'USERV');

		$password_raw = "~kevin~red~234~";
		$password = sql_encrypt($password_raw, false, 'USERV');

		$u_email = "kevin@rdresearch.co.uk";
		$u_email = sql_encrypt($u_email, false, 'USERV');

		$is_enabled = 1;
		$is_locked_out = 0;

		$u_notes = "User auto-created";
		$u_notes = sql_encrypt($u_notes, false, 'USERV');

		$u_firstname = "Kevin";
		$u_firstname = quote_smart($u_firstname);

		$u_lastname = "Beckett";
		$u_lastname = sql_encrypt(quote_smart($u_lastname, true), true, 'USERV');

		$u_initials = "PU0KB";
		$u_initials = quote_smart($u_initials);

		$fields = "CLIENT2_ID,  USERNAME,  PASSWORD,  U_EMAIL,  IS_ENABLED,  IS_LOCKED_OUT,  FAILED_LOGINS, ";
		$values = "$client2_id, $username, $password, $u_email, $is_enabled, $is_locked_out, 0,             ";

		$fields .= "CREATED_DT, U_FIRST_DT, U_LAST_DT, U_NOTES,  U_FIRSTNAME,  U_LASTNAME,  U_INITIALS,  PORTAL_PUSH";
		$values .= "$sqlNow,    NULL,       NULL,      $u_notes, $u_firstname, $u_lastname, $u_initials, 1          ";

		$sql = "INSERT INTO USERV ($fields) VALUES ($values)";
		$temp = "save_new_user(): " . str_replace($password_raw, "***", $sql);
		dprint($temp);
		log_write($temp);
		# Set up audit for new record insertion
		audit_setup_gen('USERV', 'USER_ID', 0, '', '');
		$insert_id = sql_execute($sql, true); # audited
		$temp = "auto_create(): New USER_ID=$insert_id";
		dprint($temp);
		log_write($temp);
		$count_kevin++;
	} # kevin
	
	$count = sql_select_single("SELECT COUNT(*) FROM USERV WHERE CLIENT2_ID=$client_gina");
	if ($count == 0)
	{
		$client2_id = $client_gina;
		
		$username = "C0.Gina";
		$username = sql_encrypt($username, false, 'USERV');

		$password_raw = "~gina~green~456~";
		$password = sql_encrypt($password_raw, false, 'USERV');

		$u_email = "gina@rdresearch.co.uk";
		$u_email = sql_encrypt($u_email, false, 'USERV');

		$is_enabled = 1;
		$is_locked_out = 0;

		$u_notes = "User auto-created";
		$u_notes = sql_encrypt($u_notes, false, 'USERV');

		$u_firstname = "Gina";
		$u_firstname = quote_smart($u_firstname);

		$u_lastname = "Williamson";
		$u_lastname = sql_encrypt(quote_smart($u_lastname, true), true, 'USERV');

		$u_initials = "PU0GW";
		$u_initials = quote_smart($u_initials);

		$fields = "CLIENT2_ID,  USERNAME,  PASSWORD,  U_EMAIL,  IS_ENABLED,  IS_LOCKED_OUT,  FAILED_LOGINS, ";
		$values = "$client2_id, $username, $password, $u_email, $is_enabled, $is_locked_out, 0,             ";

		$fields .= "CREATED_DT, U_FIRST_DT, U_LAST_DT, U_NOTES,  U_FIRSTNAME,  U_LASTNAME,  U_INITIALS,  PORTAL_PUSH";
		$values .= "$sqlNow,    NULL,       NULL,      $u_notes, $u_firstname, $u_lastname, $u_initials, 1          ";

		$sql = "INSERT INTO USERV ($fields) VALUES ($values)";
		$temp = "save_new_user(): " . str_replace($password_raw, "***", $sql);
		dprint($temp);
		log_write($temp);
		# Set up audit for new record insertion
		audit_setup_gen('USERV', 'USER_ID', 0, '', '');
		$insert_id = sql_execute($sql, true); # audited
		$temp = "auto_create(): New USER_ID=$insert_id";
		dprint($temp);
		log_write($temp);
		$count_gina++;
	} # gina
	
	$count = sql_select_single("SELECT COUNT(*) FROM USERV WHERE CLIENT2_ID=$client_steve");
	if ($count == 0)
	{
		$client2_id = $client_steve;
		
		$username = "C0.Steve";
		$username = sql_encrypt($username, false, 'USERV');

		$password_raw = "~steve~blue~678~";
		$password = sql_encrypt($password_raw, false, 'USERV');

		$u_email = "steve@vilcol.com";
		$u_email = sql_encrypt($u_email, false, 'USERV');

		$is_enabled = 1;
		$is_locked_out = 0;

		$u_notes = "User auto-created";
		$u_notes = sql_encrypt($u_notes, false, 'USERV');

		$u_firstname = "Steve";
		$u_firstname = quote_smart($u_firstname);

		$u_lastname = "Rowlands";
		$u_lastname = sql_encrypt(quote_smart($u_lastname, true), true, 'USERV');

		$u_initials = "PU0SR";
		$u_initials = quote_smart($u_initials);

		$fields = "CLIENT2_ID,  USERNAME,  PASSWORD,  U_EMAIL,  IS_ENABLED,  IS_LOCKED_OUT,  FAILED_LOGINS, ";
		$values = "$client2_id, $username, $password, $u_email, $is_enabled, $is_locked_out, 0,             ";

		$fields .= "CREATED_DT, U_FIRST_DT, U_LAST_DT, U_NOTES,  U_FIRSTNAME,  U_LASTNAME,  U_INITIALS,  PORTAL_PUSH";
		$values .= "$sqlNow,    NULL,       NULL,      $u_notes, $u_firstname, $u_lastname, $u_initials, 1          ";

		$sql = "INSERT INTO USERV ($fields) VALUES ($values)";
		$temp = "save_new_user(): " . str_replace($password_raw, "***", $sql);
		dprint($temp);
		log_write($temp);
		# Set up audit for new record insertion
		audit_setup_gen('USERV', 'USER_ID', 0, '', '');
		$insert_id = sql_execute($sql, true); # audited
		$temp = "auto_create(): New USER_ID=$insert_id";
		dprint($temp);
		log_write($temp);
		$count_steve++;
	} # steve
	
	$sql = "SELECT C_CODE, COUNT(*)
			FROM CLIENT2
			GROUP BY C_CODE
			HAVING 1 < COUNT(*)";
	dprint($sql);
	sql_execute($sql);
	$dups = array();
	while (($newArray = sql_fetch()) != false)
		$dups[] = $newArray[0];
	if ($dups)
		$dups = " AND (C_CODE NOT IN(" . implode(',', $dups) . ")) ";
	else
		$dups = " ";
	
	$sql = "SELECT CLIENT2_ID, C_CODE
			FROM CLIENT2
			WHERE C_ARCHIVED=$sqlFalse $dups
			ORDER BY C_CODE";
	dprint($sql);
	sql_execute($sql);
	$codes = array();
	while (($newArray = sql_fetch()) != false)
		$codes[$newArray[0]] = $newArray[1];
	if ($codes)
		dprint("Codes: " . count($codes) . " found"); #print_r($codes,1));
	else
		dprint("No codes found", true, 'orange');
	
	$new_user_count = 0;
	foreach ($codes as $client2_id => $c_code)
	{
		$old_user_count = sql_select_single("SELECT COUNT(*) FROM USERV WHERE CLIENT2_ID=$client2_id");
		if ($old_user_count == 0)
		{
			sql_create_portal_user($client2_id, $c_code);
			$new_user_count++;
		}
		if (8 <= $new_user_count)# [REVIEW]
			break;
	} # foreach ($codes)
	
	if ($count_kevin)
		dprint("Created Kevin account");
	if ($count_gina)
		dprint("Created Gina account");
	if ($count_steve)
		dprint("Created Steve account");
	if ($new_user_count)
		dprint("Created $new_user_count new client account(s)");
	else
		dprint("Created no new client accounts");
	#dprint("Created)
} # auto_create()

?>
