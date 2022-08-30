<?php

include_once("settings.php");
include_once("library.php");
include_once("lib_users.php");
global $denial_message;
global $navi_1_system;
global $navi_2_sys_users;
global $role_man;
global $USER; # set by admin_verify()

log_open("vilcol.log");

sql_connect();

admin_verify(); # writes to $USER

if ($USER['IS_ENABLED'])
{
	if (role_check('*', $role_man))
	{
		$navi_1_system = true; # settings.php; used by navi_1_heading()
		$navi_2_sys_users = true; # settings.php; used by navi_2_heading()
		$onload = "onload=\"set_scroll();\"";
		$page_title_2 = 'Users - Vilcol';
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
	global $sc_jobs;
	global $sc_text;
	global $task;
	global $tr_colour_1;

	print "<h3>System Administration</h3>";
	navi_2_heading(); # secondary navigation buttons
	print "<h3>Users Screen</h3>";
	dprint(post_values());

	if (post_val('search_clicked'))
		$task = "search";
	else
		$task = post_val('task');
	$sc_text = post_val('sc_text', false, false, false, 1);
	$sort_time = false;
	if ($sc_text == '---')
	{
		$sort_time = true;
		$sc_text = '';
	}
	$sc_dis = post_val('sc_dis', true);
	$sc_sales = post_val('sc_sales', true);
	$sc_jobs = post_val('sc_jobs', true);
	$sc_man = post_val('sc_man', true);
	$edit = post_val('edit', true);
	$one_user_id = post_val('user_id', true);
	
	javascript();
	
	print "
	<div id=\"div_form_main\" style=\"background-color:{$tr_colour_1};\">
	<hr>
	<form name=\"form_main\" action=\"" . server_php_self() . "\" method=\"post\">
	" . input_hidden('task', '') . "
	" . input_hidden('user_id', '') . "
	" . input_hidden('edit', '') . "
	Search for user name: 
	" . input_textbox('sc_text', $sc_text, 20, 50, "onkeypress=\"search_prepare()\"") . # onkeydown causes a backspace to delete two characters the first time it is used!
	"
	" . input_button('Search', 'search_js(1)') . "
	&nbsp;&nbsp;&nbsp;...or: " . input_button('Show all users', 'search_js(0)') . "
	&nbsp;&nbsp;&nbsp;" . input_tickbox('Include disabled users', 'sc_dis', 1, $sc_dis) . "
	&nbsp;&nbsp;&nbsp;" . input_tickbox('Just salespersons', 'sc_sales', 1, $sc_sales) . "
	&nbsp;&nbsp;&nbsp;" . input_tickbox('Just managers', 'sc_man', 1, $sc_man) . "
	&nbsp;&nbsp;&nbsp;" . input_tickbox('Job Statistics', 'sc_jobs', 1, $sc_jobs) . "
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

	if (($task == 'details') && 
		(($one_user_id > 0) || (($one_user_id == 0) && ($edit == 1))))
		print_one_user();
	else 
	{
		sql_get_users(true, false, $sc_dis, false, false, $sc_text, $sc_sales, '*', '', '', $sc_man, true, $sort_time); # lib_vilcol.php
		print_users();
		if (!$sc_jobs)
			print "
			<p>" . input_button('Create New User', 'edit_user(0,1)') . "</p>
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
	
	function search_js(useSC,sort_time=false)
	{
		if (useSC == 0)
		{
			document.form_main.sc_text.value = '';
			document.form_main.sc_dis.checked = true;
			document.form_main.sc_sales.checked = false;
			document.form_main.sc_man.checked = false;
			document.form_main.sc_jobs.checked = false;
		}
		if (sort_time)
			document.form_main.sc_text.value = '---';
		document.form_main.task.value = 'search';
		please_wait_on_submit();
		document.form_main.submit();
	}
	
	function save_new_user()
	{
		var el = document.form_edit.u_firstname;
		var val = trim(el.value);
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
		el = document.form_edit.user_role_id_t;
		val = 1 * el.value;
		if (!(val > 0))
		{
			alert('Please select a Role (Traces)');
			return;
		}
		el = document.form_edit.user_role_id_c;
		val = 1 * el.value;
		if (!(val > 0))
		{
			alert('Please select a Role (Collections)');
			return;
		}
		el = document.form_edit.user_role_id_a;
		val = 1 * el.value;
		if (!(val > 0))
		{
			alert('Please select a Role (Accounts)');
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
		var url = 'users_ajax.php?op=u&i={$one_user_id}&n=' + field_name + '&v=' + encodeURIComponent(field_value);
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
	$user_role_id_c = post_val('user_role_id_c', true);
	if (!$user_role_id_c)
	{
		log_write("$error_txt_1 Bad user_role_id_c \"" . post_val('user_role_id_c') . "\" $error_txt_2");
		return false;
	}
	$user_role_id_t = post_val('user_role_id_t', true);
	if (!$user_role_id_t)
	{
		log_write("$error_txt_1 Bad user_role_id_t \"" . post_val('user_role_id_t') . "\" $error_txt_2");
		return false;
	}
	$user_role_id_a = post_val('user_role_id_a', true);
	if (!$user_role_id_a)
	{
		log_write("$error_txt_1 Bad user_role_id_a \"" . post_val('user_role_id_a') . "\" $error_txt_2");
		return false;
	}
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
	
	$fields = "USERNAME,  ORIG_USERNAME_C, ORIG_USERNAME_T, PASSWORD,  U_EMAIL,  IS_ENABLED,  IS_LOCKED_OUT,  FAILED_LOGINS, ";
	$values = "$username, $username,       $username,       $password, $u_email, $is_enabled, $is_locked_out, 0,             ";
	
	$fields .= "CREATED_DT, U_FIRST_DT, U_LAST_DT, U_NOTES,  USER_ROLE_ID_C,  USER_ROLE_ID_T,  USER_ROLE_ID_A,  U_FIRSTNAME,  ";
	$values .= "$sqlNow,    NULL,       NULL,      $u_notes, $user_role_id_c, $user_role_id_t, $user_role_id_a, $u_firstname, ";
	
	$fields .= "U_LASTNAME,  U_INITIALS";
	$values .= "$u_lastname, $u_initials";
	
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
	global $id_USER_ROLE_developer;
	global $role_man;
	global $sc_jobs;
	global $tr_colour_1;
	global $tr_colour_2;
	global $USER;
	global $users;
	
	if (!$users)
	{
		print "<p>No users matched your search criteria.</p>";
		return;
	}
	
	$e_disabled = (role_check('*', $role_man) ? '' : 'disabled');
	
	$grey = "style=\"color:gray;\"";
	print "
	<table name=\"table_users\" class=\"spaced_table\" border=\"0\">
	<tr>
		";
		if ($sc_jobs)
		{
			$job_stats = sql_agent_job_stats(0);
			print "
			<th>Login</th><th>Name</th><th>Init.</th>
			<th>Open Trace-type Jobs<br>older than {$job_stats['OPEN_TRACE_DAYS']} days</th>
			<th>Open Trace-type Jobs<br>older than {$job_stats['OPEN_TRACE_DAYS']} days<br>(Complete=No/Blank)</th>
			<th>Open Retrace-type Jobs<br>older than {$job_stats['OPEN_RETRACE_DAYS']} days</th>
			<th>Open Retrace-type Jobs<br>older than {$job_stats['OPEN_RETRACE_DAYS']} days<br>(Complete=No/Blank)</th>
			<th>Open Trace Jobs<br>of any job type</th>
			<th>Open Trace Jobs<br>of any job type<br>(Complete=No/Blank)</th>
			<th>Open Collect Jobs</th>
			<th $grey>DB ID</th>
			";
		}
		else
			print "
			<th></th><th></th><th>Login</th><th>Name</th><th>Init.</th>
			<th>Role (Trace)</th><th>Role (Coll.)</th><th>Role (Acc.)</th><th>Sales person</th><th>Email</th><th>Enabled</th><th>Locked Out</th>
			" . (user_debug(2) ? "<th $grey onclick=\"search_js(1,true)\">Last Logged-in</th>" : '') . "
			<th $grey>DB ID</th>";
		print "
	</tr>
	";
	$trcol = $tr_colour_1;
	foreach ($users as $one_user)
	{
		if ($sc_jobs)
		{
			$job_stats = sql_agent_job_stats($one_user['USER_ID']);
			
			print "
			<tr bgcolor=\"$trcol\" onmouseover=\"this.className='Highlight'\" onmouseout=\"this.className='Normal'\">
				<td>{$one_user['USERNAME']}</td>
				<td>{$one_user['U_FIRSTNAME']} {$one_user['U_LASTNAME']}</td>
				<td>{$one_user['U_INITIALS']}</td>
				<td $ar>{$job_stats['OPEN_TRACE_COUNT']}</td>
				<td $ar>{$job_stats['OPEN_TRACE_COUNT_COMP_NO']}</td>
				<td $ar>{$job_stats['OPEN_RETRACE_COUNT']}</td>
				<td $ar>{$job_stats['OPEN_RETRACE_COUNT_COMP_NO']}</td>
				<td $ar>{$job_stats['OPEN_T_COUNT']}</td>
				<td $ar>{$job_stats['OPEN_T_COUNT_COMP_NO']}</td>
				<td $ar>{$job_stats['OPEN_C_COUNT']}</td>
				<td $grey $ar>{$one_user['USER_ID']}</td>
			</tr>
			";
			$trcol = (($trcol == $tr_colour_1) ? $tr_colour_2 : $tr_colour_1);
		}
		else
		{
			$enabled = ($one_user['IS_ENABLED'] ? 'Yes' : 'No');
			$locked = ($one_user['IS_LOCKED_OUT'] ? 'Yes' : 'No');
			$blocked = (((!$one_user['IS_ENABLED']) || $one_user['IS_LOCKED_OUT']) ? true : false);

			if ($one_user['U_SALES'])
				$sales = 'Yes';
			elseif ($one_user['U_SALES_ISH'])
				$sales = 'Has sold';
			else
				$sales = 'No';
	//		$sales = ($one_user['U_SALES'] ? 'Yes' : 'No');
	//		if ((!$one_user['U_SALES']) && $one_user['U_SALES_ISH'])
	//			$sales .= " / Yes";

			if ($e_disabled && ($USER['USER_ID'] == $one_user['USER_ID']))
				$e_disabled_2 = ''; # user can edit themselves
			elseif (($USER['USER_ROLE_ID_C'] != $id_USER_ROLE_developer) && ($USER['USER_ROLE_ID_T'] != $id_USER_ROLE_developer) &&
					($USER['USER_ROLE_ID_A'] != $id_USER_ROLE_developer) &&
					(($one_user['USER_ROLE_ID_C'] == $id_USER_ROLE_developer) || 
						($one_user['USER_ROLE_ID_T'] == $id_USER_ROLE_developer) || 
						($one_user['USER_ROLE_ID_A'] == $id_USER_ROLE_developer))
					)
				$e_disabled_2 = 'disabled'; # can't edit developer unless you are one
			elseif ($one_user['U_HISTORIC'] == 1)
				$e_disabled_2 = 'disabled'; # can't edit historic accounts
			elseif (($one_user['USERNAME'] == 'SYSTEM') || ($one_user['USERNAME'] == 'HOUSE'))
				$e_disabled_2 = 'disabled'; # can't edit system accounts
			else
				$e_disabled_2 = $e_disabled; # the default

			$u_role_t = ((strtolower($one_user['UR_ROLE_T']) == 'none') ? '' : $one_user['UR_ROLE_T']);
			$u_role_c = ((strtolower($one_user['UR_ROLE_C']) == 'none') ? '' : $one_user['UR_ROLE_C']);
			$u_role_a = ((strtolower($one_user['UR_ROLE_A']) == 'none') ? '' : $one_user['UR_ROLE_A']);
			
			print "
			<tr bgcolor=\"$trcol\" onmouseover=\"this.className='Highlight'\" onmouseout=\"this.className='Normal'\"" .
					($blocked ? "style=\"color:red;\"" : '') . ">
				<td>" . input_button('View', "edit_user({$one_user['USER_ID']},0);") . "</td>
				<td>" . input_button('Edit', "edit_user({$one_user['USER_ID']},1);", $e_disabled_2) . "</td>
				<td>{$one_user['USERNAME']}</td>
				<td>{$one_user['U_FIRSTNAME']} {$one_user['U_LASTNAME']}</td>
				<td>{$one_user['U_INITIALS']}</td>
				<td>$u_role_t</td><td>$u_role_c</td><td>$u_role_a</td><td>$sales</td>
				<td>{$one_user['U_EMAIL']}</td><td>$enabled</td><td>$locked</td>
				" . (user_debug(2) ? "<td $grey>" . date_for_sql($one_user['U_LAST_DT'], true) . "</td>" : '') . "
				<td $grey $ar>{$one_user['USER_ID']}</td>
			</tr>
			";
			$trcol = (($trcol == $tr_colour_1) ? $tr_colour_2 : $tr_colour_1);
		}
	}
	print "
	</table><!--table_users-->
	</form><!--form_main-->
	";
	
}

function print_one_user()
{
	#global $ar;
	global $at;
	global $col2;
	global $col3;
	#global $col4;
	global $col6;
	global $id_USER_ROLE_developer;
	global $NEW_USERS; # lib_users.php
	#global $perm_users;
	#global $perms;
	global $role_man;
	global $roles;
	global $roles_a;
	global $USER; # the user who is currently logged in
	global $one_user_id; # 
	
	$one_user = sql_get_one_user($one_user_id);
	sql_get_user_roles_plus(); # writes to $roles and $roles_a
	sql_get_perms(); # writes to $perms
	
	$new_user = (($one_user_id > 0) ? false : true);
	$can_edit = (post_val('edit', true) ? true : false);
	if ($can_edit && 
		( (!role_check('*', $role_man)) || ($one_user['U_HISTORIC'] == 1) )
	   )
	{
		$can_edit = false;
		$restricted = true;
	}
	else 
		$restricted = false;
		
	# $dev_protect stops non-developers from editing developer info
	if ($can_edit && 
		($USER['USER_ROLE_ID_C'] != $id_USER_ROLE_developer) && ($USER['USER_ROLE_ID_T'] != $id_USER_ROLE_developer) && 
		($USER['USER_ROLE_ID_A'] != $id_USER_ROLE_developer) && 
		(	($one_user['USER_ROLE_ID_C'] == $id_USER_ROLE_developer) || 
			($one_user['USER_ROLE_ID_T'] == $id_USER_ROLE_developer) ||
			($one_user['USER_ROLE_ID_A'] == $id_USER_ROLE_developer))
		)
		$dev_protect = true;
	else 
		$dev_protect = false;
		
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
	elseif ($can_edit && (!$dev_protect))
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
		";
//		if ($one_user['U_IMPORTED'])
//		{
//			print "
//			<tr>
//				<td $at>Original Username<br>(Old Traces)</td>
//				<td $at>" . input_textbox('orig_username_t', $one_user['ORIG_USERNAME_T'], $szstd, 0, "$x_text disabled") . "</td>
//			</tr>
//			<tr>
//				<td $at>Original Username<br>(Old Collections<br>or New System)</td>
//				<td $at>" . input_textbox('orig_username_c', $one_user['ORIG_USERNAME_C'], $szstd, 0, "$x_text disabled") . "</td>
//			</tr>
//			";
//		}
//		elseif (global_debug() && ($one_user_id > 0))
//		{
//			print "
//			<tr>
//				<td $at>Orig T/C</td>
//				<td $at>" . input_textbox('x', $one_user['ORIG_USERNAME_T'] . '/' . $one_user['ORIG_USERNAME_C'], 
//								$szstd, 0, "disabled") . "</td>
//			</tr>
//			";
//		}
		print "
		<tr>
			<td>" . input_tickbox($enabled_txt, 'is_enabled', 1, $one_user['IS_ENABLED'], $clk_ena, $x_tick) . "</td>
			<td>" . input_tickbox($locked_txt, 'is_locked_out', 1, $one_user['IS_LOCKED_OUT'], $clk_loc, $x_tick) . "</td>
			<td></td>
			<td>" . input_tickbox('Salesperson', 'u_sales', 1, $one_user['U_SALES'], $clk_tick, $x_tick) . "&nbsp;&nbsp;&nbsp;
				" . input_tickbox('Has Sold', 'u_sales_ish', 1, $one_user['U_SALES_ISH'], $clk_tick, $x_tick) . "</td>
			<td>" . input_tickbox('From old system', 'u_imported', 1, $one_user['U_IMPORTED'], '', 'disabled') . "</td>
			<td>" . input_tickbox('Historic user', 'u_historic', 1, $one_user['U_HISTORIC'], '', 'disabled') . "</td>
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
			<td>Role<br>(Traces)</td>
			<td>
				";
				if ($one_user['USER_ROLE_ID_T'] == $id_USER_ROLE_developer)
					print "Developer";
				else 
					print input_select('user_role_id_t', $roles, $one_user['USER_ROLE_ID_T'], $x_num, true);
				print "
			</td>
		</tr>
		<tr>
			<td>Role<br>(Collections)</td>
			<td>
				";
				if ($one_user['USER_ROLE_ID_C'] == $id_USER_ROLE_developer)
					print "Developer";
				else 
					print input_select('user_role_id_c', $roles, $one_user['USER_ROLE_ID_C'], $x_num, true);
				print "
			</td>
		</tr>
		<tr>
			<td>Role<br>(Accounts)</td>
			<td>
				";
				if ($one_user['USER_ROLE_ID_A'] == $id_USER_ROLE_developer)
					print "Developer";
				else 
					print input_select('user_role_id_a', $roles_a, $one_user['USER_ROLE_ID_A'], $x_num, true);
				print "
			</td>
		</tr>
		<tr>
			<td>&nbsp;</td>
		</tr>
		";
//		print "
//		<tr>
//			<td>Permissions</td>
//			<td $col3>
//				<table name=\"table_perms\" border=\"1\">
//				";
////				print "
////				<tr>
////					<td $col3>" . print_r($one_user['PERMS'],1) . "</td>
////				</tr>
////				";
//				print "
//				<tr>
//					<th>Permission Name</th><th $grey>DB ID</th><th $grey>Code</th>
//				</tr>
//				<tr>
//					<td $col3>Trace system:</td>
//				</tr>
//				";
//				$dis = ($can_edit ? '' : 'disabled');
//				foreach ($perms as $one_perm)
//				{
//					if ($one_perm['UP_SYS'] == 'T')
//					{
//						$pid = $one_perm['USER_PERMISSION_ID'];
//						$ticked = (in_array($pid, $one_user['PERMS']) ? true : false);
//						print "
//						<tr><td>" . input_tickbox($one_perm['UP_PERM'], "perm_{$pid}", 1, $ticked, $clk_tick, $dis) . "</td>
//							<td $grey $ar>{$one_perm['USER_PERMISSION_ID']}</td><td $grey>{$one_perm['UP_CODE']}</td>
//						</tr>";
//					}
//				}
//				print "
//				<tr>
//					<td $col3>Collection system:</td>
//				</tr>
//				";
//				foreach ($perms as $one_perm)
//				{
//					if ($one_perm['UP_SYS'] == 'C')
//					{
//						$pid = $one_perm['USER_PERMISSION_ID'];
//						$ticked = (in_array($pid, $one_user['PERMS']) ? true : false);
//						print "
//						<tr><td>" . input_tickbox($one_perm['UP_PERM'], "perm_{$pid}", 1, $ticked, $clk_tick, $dis) . "</td>
//							<td $grey $ar>{$one_perm['USER_PERMISSION_ID']}</td><td $grey>{$one_perm['UP_CODE']}</td>
//						</tr>";
//					}
//				}
//				print "
//				</table><!--table_perms-->
//			</td>
//		</tr>
//		<tr>
//			<td>&nbsp;</td>
//		</tr>
//		";
		print "
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
			";
		}
		
		$old_logins = ($one_user['U_IMPORTED'] ? $NEW_USERS[$one_user_id][1] : '');
		if ($old_logins)
		{
			print "
			<tr>
				<td $at>Old System Logins</td>
				<td $col6>
					<table class=\"spaced_table\">
						<tr><th>System</th><th>Login name</th><th>Filename</th></tr>
						";
						foreach ($old_logins as $olog)
							print "<tr><td>" . (($olog[0] == 'T') ? 'Trace' : (($olog[0] == 'C') ? 'Collect' : '?')) . "</td>
										<td>{$olog[1]}</td><td>{$olog[2]}</td></tr>";
						print "
					</table>
				</td>
			</tr>
			<tr>
				<td>Initial password:</td>
				<td>" . $NEW_USERS[$one_user_id][0][1] . "</td>
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

?>
