<?php

include_once("settings.php");
include_once("library.php");
global $denial_message;
global $navi_1_system;
global $navi_2_sys_audit;
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
		$navi_2_sys_audit = true; # settings.php; used by navi_2_heading()
		$onload = "onload=\"set_scroll();\"";
		$page_title_2 = 'Audit - Vilcol';
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
	global $page_title_2;
	global $qualifiers;

	print "<h3>System Administration</h3>";
	navi_2_heading(); # secondary navigation buttons
	print "<h3>Audit Screen</h3>";

	dprint(post_values());

	javascript();

	$qualifiers = array();

	$qualifiers['date_from'] = post_val('date_from', false, true, false, 1);
	$qualifiers['date_to'] = post_val('date_to', false, true, false, 1);
	$temp = post_val('change_id', true);
	if (($temp == -1) || (0 < $temp))
		$qualifiers['change_id'] = $temp;
	else
		$qualifiers['change_id'] = '';

	$qualifiers['logins'] = post_val('logins', true);

	$qualifiers['table'] = post_val('table');
	if ($qualifiers['logins'] == 1)
		$qualifiers['table_id'] = post_val('table_id'); # IP address
	else
	{
		if (($temp = post_val('table_id', true)) > 0)
			$qualifiers['table_id'] = $temp;
		else
			$qualifiers['table_id'] = '';
	}

	if (($temp = post_val('job_id', true)) > 0)
		$qualifiers['job_id'] = $temp;
	else
		$qualifiers['job_id'] = '';

	if (($temp = post_val('client2_id', true)) > 0)
		$qualifiers['client2_id'] = $temp;
	else
		$qualifiers['client2_id'] = '';

//	$qualifiers['filename'] = post_val('filename');

	if (($temp = post_val('limit', true)) > 0)
		$qualifiers['limit'] = $temp;
	else
		$qualifiers['limit'] = 1000;
	$audit = sql_get_audit_all($qualifiers);

	print_search();
	print_audit($audit);

	print "
	<script type=\"text/javascript\">
	document.getElementById('page_title').innerHTML = '$page_title_2';
	</script>
	";
}

function screen_content_2()
{
	# This is required by screen_layout()
} # screen_content_2()

function print_search()
{
	global $ar;
	global $col2;
	global $col4;
	global $qualifiers;
	global $tr_colour_1;

	$gap = "<td width=\"20\"></td>";
	$users = sql_get_users(false, true, true, false, false, '', false, '*', '', '', false, false);
	$users[-1] = '(new system)'; # e.g. auto_letter.php

	$tables = audit_get_tables(); # lib_audit.php

	print "
	<div id=\"div_form_main\" style=\"background-color:{$tr_colour_1};\">
	<hr>
	<form name=\"form_search\" action=\"" . server_php_self() . "\" method=\"post\">
	<p>Search Criteria</p>
	<table name=\"table_search\" class=\"spaced_table\" border=\"0\"><!---->
		<tr>
			<td>Date from:</td><td>" . input_textbox('date_from', $qualifiers['date_from']) . "</td>$gap
			<td>Date to:</td><td>" . input_textbox('date_to', $qualifiers['date_to']) . "</td>$gap
			<td>User:</td><td>" . input_select('change_id', $users, $qualifiers['change_id']) . "</td>$gap
			<td>Table:</td><td>" . input_select('table', $tables, $qualifiers['table']) . "</td>$gap
			<td>Table ID:</td><td>" . input_textbox('table_id', $qualifiers['table_id']) . "</td>$gap
		</tr>
		<tr>
		";
//		print "
//			<td>Filename</td><td>" . input_textbox('filename', $qualifiers['filename']) . "</td>$gap
//		";
		print "
			<td $col2>" . input_tickbox('Only Login events', 'logins', 1, $qualifiers['logins']) . "</td>$gap
			<td $col4 $ar>Related to this Job ID:</td><td>" . input_textbox('job_id', $qualifiers['job_id']) . "</td>$gap
			<td $col4 $ar>Related to this Client ID:</td><td>" . input_textbox('client2_id', $qualifiers['client2_id']) . "</td>$gap
		</tr>
		<tr>
			<td $col2>" . input_button('Search on Criteria', 'do_search();') .
								input_button('Clear', 'do_clear();') . "</td>$gap
			<td>Limit to:</td><td>" . input_textbox('limit', $qualifiers['limit'], 4) . " results</td>$gap
		</tr>
	</table><!--table_search-->
	" . # The below is to allow form submission by pressing the ENTER key on keyboard
	"
	<input type=\"submit\" style=\"display:none\">
	</form><!--form_search-->
	<hr>
	</div><!--div_form_main-->
	";
}

function print_audit($audit)
{
	global $customer_ip;
	global $customer_ip_2;
	global $file_event_upload;
	global $file_event_create;
	global $file_event_delete;
	global $ip_local;
	global $ip_rdr_kdb;
	global $tr_colour_1;
	global $tr_colour_2;

	if ($audit)
	{
		$val_width = '';#"style=\"width:200px\"";
		print "
		<table name=\"table_audit\" class=\"spaced_table\" border=\"0\">
		<tr>
			<th>Date</th><th>User</th><th>Linked to</th>
			<th>Table</th><th>ID</th><th>Field</th><th $val_width>Old Value</th><th $val_width>New Value</th><th>MySQL</th>
		</tr>
		";
		$trcol = $tr_colour_1;

		foreach ($audit as $one)
		{
			$linked_to = array();
			if ($one['JOB_ID'] && ($one['TABLE_NAME'] != 'JOB'))
				$linked_to[] = "Job ID " . $one['JOB_ID'];
			if ($one['CLIENT2_ID'] && ($one['TABLE_NAME'] != 'CLIENT2'))
				$linked_to[] = "Client ID " . $one['CLIENT2_ID'];
//			if ($one['CONTACT_ID'])
//				$linked_to[] = "Contact " . $one['CONTACT_ID'];
			if ($one['USER_ID'])
				$linked_to[] = "USER ID " . $one['USER_ID'];
			#if ($one['LOGIN_EVENT'])
			#	$linked_to[] = 'LOGIN';
			$linked_to = implode('<br>', $linked_to);

			if ($one['CHANGED_BY'])
				$changed_by = $one['CHANGED_BY'];
			else
				$changed_by = '(new system)';
			if ($one['FROM_MYSQL'])
			{
				$from_mysql = 'MySQL';
				$date_colour = " style=\"color:blue;\"";
			}
			else
			{
				$from_mysql = '';
				$date_colour = '';
			}
			print "
				<tr bgcolor=\"$trcol\" onmouseover=\"this.className='Highlight'\" onmouseout=\"this.className='Normal'\">
					<td $date_colour>{$one['CHANGE_DT']}</td><td $date_colour>$changed_by</td><td $date_colour>$linked_to</td>
					";
				if ($one['FILE_EVENT'])
				{
					if ($one['FILE_EVENT'] == $file_event_upload)
						$event = 'uploaded';
					elseif ($one['FILE_EVENT'] == $file_event_create)
						$event = 'created';
					elseif ($one['FILE_EVENT'] == $file_event_delete)
						$event = 'deleted';
					else
						$event = 'eaten by the file monster';
					print "
					<td $date_colour colspan=\"5\">File \"{$one['TABLE_NAME']}\" was $event</td>
					";
				}
				elseif ($one['LOGIN_EVENT'])
				{
					$ip_addr = $one['TABLE_NAME'];
					if ($ip_addr == $ip_rdr_kdb)
						$ip_addr .= " (RDR/KDB)";
					elseif (($ip_addr == $customer_ip) || ($ip_addr == $customer_ip_2))
						$ip_addr .= " (Vilcol)";
					elseif ($ip_addr == $ip_local)
						$ip_addr .= " (Local)";
					print "
					<td $date_colour colspan=\"5\">Logged in from $ip_addr via {$one['ID_NAME']}</td>
					";
				}
				else
				{
					if ($one['FIELD_NAME'] == 'PASSWORD')
					{
						if ($one['OLD_VAL'] == '(new record)')
							$old_val = $one['OLD_VAL'];
						else
							$old_val = '(undisclosed)';
						$new_val = '(undisclosed)';
					}
					else
					{
						$old_val = $one['OLD_VAL'];
						$new_val = $one['NEW_VAL'];
						if (($one['FIELD_NAME'] === 'LETTER_TEMPLATE') || ($one['FIELD_NAME'] === 'JL_TEXT'))
						{
							$temp = '';
							for ($ii = 0, $iiMax = strlen($old_val); $ii < $iiMax; $ii += 50) {
								$temp .= (substr($old_val, $ii, 50) . "<br>");
							}
							$old_val = $temp;
							$temp = '';
							for ($ii = 0, $iiMax = strlen($new_val); $ii < $iiMax; $ii += 50) {
								$temp .= (substr($new_val, $ii, 50) . "<br>");
							}
							$new_val = $temp;
						}
					}
					print "
					<td $date_colour>{$one['TABLE_NAME']}</td>
					<td $date_colour align=\"right\">{$one['ID_VALUE']}</td>
					<td $date_colour>{$one['FIELD_NAME']}</td><td $date_colour $val_width>$old_val</td><td $date_colour $val_width>$new_val</td>
					";
				}
				print "
					<td $date_colour>$from_mysql</td>
				</tr>
				";
				$trcol = (($trcol == $tr_colour_1) ? $tr_colour_2 : $tr_colour_1);
		}
		print "
		</table><!--table_audit-->
		";
	}
	else
		print "<p>No matching audit records</p>";
}

function javascript()
{
	print "
	<script type=\"text/javascript\">

	function do_search()
	{
		please_wait_on_submit();
		document.form_search.submit();
	}

	function do_clear()
	{
		document.form_search.date_from.value = '';
		document.form_search.date_to.value = '';
		document.form_search.change_id.value = '';
		document.form_search.table.value = '';
		document.form_search.table_id.value = '';
		//document.form_search.filename.value = '';
		document.form_search.logins.checked = false;
		document.form_search.limit.value = '1000';
	}

	</script>
	";
}

?>
