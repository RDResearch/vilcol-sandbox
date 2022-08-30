<?php

include_once("settings.php");
include_once("library.php");
global $navi_1_system;
global $navi_2_sys_standing;
global $USER; # set by admin_verify()

log_open("vilcol.log");

sql_connect();

admin_verify(); # writes to $USER

$feedback_38 = global_debug();
$feedback_39 = global_debug();
$feedback_42 = global_debug();

if ($USER['IS_ENABLED'])
{
	$navi_1_system = true; # settings.php; used by navi_1_heading()
	$navi_2_sys_standing = true; # settings.php; used by navi_2_heading()
	$onload = "onload=\"set_scroll();\"";
	$page_title_2 = 'Standing Data - Vilcol';
	screen_layout();
}
else 
	print "<p>" . server_php_self() . ": login is not enabled</p>";
	
sql_disconnect();
log_close();

function screen_content()
{
	global $at;
	global $item; # e.g. 'activity' for Activity Codes
	global $item_id;
	global $page_title_2;
	global $role_man;
	
	print "<h3>System Administration</h3>";
	navi_2_heading(); # secondary navigation buttons
	print "<h3>Standing Data</h3>";
	
	dprint(post_values());
	$task = post_val('task');
	$item = post_val('item');
	$item_id = post_val('item_id', true); # will be -1 if user clicked 'Add New'
	#dprint("task=\"$task\", item=\"$item\", item_id=\"$item_id\"");
	
	javascript(); # must come after setting of $item etc
	
	if ($item == 'activity')
	{
		if ($task == 'save')
		{
			$new_id = save_activity();
			if (0 < $new_id)
			{
				dprint("The new Activity Code has been saved", true);
				$task = ''; # show the list
				#$task = 'edit'; # show the item
			}
			else
				dprint("*** Error saving item ***", true);
		}
		# Don't put "else" here
		if ($task == 'edit')
		{
			sql_get_activity(false, true, $item_id);
			print_activity();
		}
		else 
		{
			sql_get_activity();
			print_activity();
		}
	}
	elseif ($item == 'adjust')
	{
		if ($task == 'save')
		{
			$new_id = save_adjust();
			if (0 < $new_id)
			{
				dprint("The new Adjustment Reason has been saved", true);
				$task = ''; # show the list
				#$task = 'edit'; # show the item
			}
			else
				dprint("*** Error saving item ***", true);
		}
		# Don't put "else" here
		if ($task == 'edit')
		{
			sql_get_adjust(false, true, $item_id);
			print_adjust();
		}
		else 
		{
			sql_get_adjust();
			print_adjust();
		}
	}
	elseif ($item == 'misc_info')
	{
		if ($task == 'edit')
		{
			sql_get_misc_info($item_id);
			print_misc_info();
		}
		else 
		{
			sql_get_misc_info();
			print_misc_info();
		}
	}
	elseif ($item == 'role_perm')
	{
		if ($task == 'save')
		{
			#gl_code_sql_save();
			#gl_code_sql_get();
			#gl_code_list();
		}
		elseif ($task == 'edit')
		{
			#gl_code_sql_get();
			#gl_code_edit();
		}
		else 
		{
			sql_get_roles();
			sql_get_perms();
			sql_get_role_perms();
			print_role_perms();
		}
	}
	elseif ($item == 'user_perm')
	{
		if ($task == 'save')
		{
			#gl_code_sql_save();
			#gl_code_sql_get();
			#gl_code_list();
		}
		elseif ($task == 'edit')
		{
			#gl_code_sql_get();
			#gl_code_edit();
		}
		else 
		{
			sql_get_perms();
			print_perms();
		}
	}
	elseif ($item == 'user_role')
	{
		# User cannot create new roles.
		if ($task == 'edit')
		{
			sql_get_roles($item_id);
			print_roles();
		}
		else 
		{
			sql_get_roles();
			print_roles();
		}
	}
	elseif ($item == 'pay_meth')
	{
		if ($task == 'save')
		{
			$new_id = save_pay_meth();
			if (0 < $new_id)
			{
				dprint("The new Payment Method has been saved", true);
				$task = ''; # show the list
				#$task = 'edit'; # show the item
			}
			else
				dprint("*** Error saving item ***", true);
		}
		# Don't put "else" here
		if ($task == 'edit')
		{
			sql_get_payment_methods(false, $item_id);
			print_payment_methods();
		}
		else
		{
			sql_get_payment_methods(false);
			print_payment_methods();
		}
	}
	elseif ($item == 'pay_route')
	{
		if ($task == 'save')
		{
			$new_id = save_pay_route();
			if (0 < $new_id)
			{
				dprint("The new Payment Route has been saved", true);
				$task = ''; # show the list
				#$task = 'edit'; # show the item
			}
			else
				dprint("*** Error saving item ***", true);
		}
		# Don't put "else" here
		if ($task == 'edit')
		{
			sql_get_payment_routes($item_id);
			print_payment_routes();
		}
		else 
		{
			sql_get_payment_routes();
			print_payment_routes();
		}
	}
	elseif ($item == 'job_target')
	{
		if ($task == 'save')
		{
			$new_id = save_job_target();
			if (0 < $new_id)
			{
				dprint("The new Job Target has been saved", true);
				$task = ''; # show the list
				#$task = 'edit'; # show the item
			}
			else
				dprint("*** Error saving item ***", true);
		}
		# Don't put "else" here
		if ($task == 'edit')
		{
			sql_get_job_targets($item_id);
			print_job_targets();
		}
		else 
		{
			sql_get_job_targets();
			print_job_targets();
		}
	}
	elseif ($item == 'job_type')
	{
		if ($task == 'save')
		{
			$new_id = save_job_type();
			if (0 < $new_id)
			{
				dprint("The new Job Type has been saved", true);
				$task = ''; # show the list
				#$task = 'edit'; # show the item
			}
			else
				dprint("*** Error saving item ***", true);
		}
		# Don't put "else" here
		if ($task == 'edit')
		{
			sql_get_job_types(false, true, $item_id);
			print_job_types();
		}
		else 
		{
			sql_get_job_types();
			print_job_types();
		}
	}
	elseif ($item == 'letter_sequence')
	{
		if ($task == 'save')
		{
			$new_id = save_letter_sequence();
			if (0 < $new_id)
			{
				dprint("The new Letter Sequence has been saved", true);
				$task = ''; # show the list
				#$task = 'edit'; # show the item
			}
			else
				dprint("*** Error saving item ***", true);
		}
		# Don't put "else" here
		if ($task == 'edit')
		{
			sql_get_letter_sequences(false, true, $item_id);
			print_letter_sequences();
		}
		else 
		{
			sql_get_letter_sequences();
			print_letter_sequences();
		}
	}
	elseif ($item == 'job_status')
	{
		if ($task == 'save')
		{
			$new_id = save_job_status();
			if (0 < $new_id)
			{
				dprint("The new Job Status has been saved", true);
				$task = ''; # show the list
				#$task = 'edit'; # show the item
			}
			else
				dprint("*** Error saving item ***", true);
		}
		# Don't put "else" here
		if ($task == 'edit')
		{
			sql_get_job_statuses($item_id);
			print_job_statuses();
		}
		else 
		{
			sql_get_job_statuses();
			print_job_statuses();
		}
	}
	elseif ($item == 'letter')
	{
		if ($task == 'save')
		{
			#gl_code_sql_save();
			#gl_code_sql_get();
			#gl_code_list();
		}
		elseif ($task == 'edit')
		{
			sql_get_letter_templates($item_id);
			print_letters();
		}
		else 
		{
			sql_get_letter_templates();
			print_letters();
		}
	}
	elseif ($item == 'client_group')
	{
		if ($task == 'save')
		{
			$new_id = save_client_group();
			if (0 < $new_id)
			{
				dprint("The new Client Group has been saved", true);
				$task = ''; # show the list
				#$task = 'edit'; # show the item
			}
			else
				dprint("*** Error saving item ***", true);
		}
		# Don't put "else" here
		if ($task == 'edit')
		{
			sql_get_client_groups_with_clients($item_id);
			print_client_groups();
		}
		else 
		{
			sql_get_client_groups_with_clients();
			print_client_groups();
		}
	}
	elseif ($item == 'job_group')
	{
		set_time_limit(60 * 5);
		sql_get_job_groups_with_jobs();
		print_job_groups();
	}
	elseif ($item == 'rep_field')
	{
		# The user cannot create new report fields
		if ($task == 'edit')
		{
			sql_get_report_fields(false, $item_id);
			print_report_fields();
		}
		else
		{
			sql_get_report_fields(false);
			print_report_fields();
		}
	}
	else 
	{
		$shape = 'style="width:100px; height:60px;"';
		$gap = '&nbsp;&nbsp;';
		
		print_form_start();
		print "
		<h2>Standing Data</h2>
		<p>Each set of standing data can be viewed and/or edited by clicking on the appropriate button below</p>
		<table>
		<tr>
			<td $at>" . input_button("&nbsp;Activity \rCodes", "view_standing('activity');", $shape) . "</td>
			$gap
			<td $at>" . input_button("&nbsp;Adjustment \rReasons", "view_standing('adjust');", $shape) . "</td>
			$gap
			<td $at>" . input_button("&nbsp;Job \rStatuses", "view_standing('job_status');", $shape) . "</td>
			$gap
			<td $at>" . input_button("&nbsp;Job \rTargets", "view_standing('job_target');", $shape) . "</td>
			$gap
			<td $at>" . input_button("&nbsp;Job \rTypes", "view_standing('job_type');", $shape) . "</td>
			$gap
			<td $at>" . input_button("&nbsp;Letter \rSequences", "view_standing('letter_sequence');", $shape) . "</td>
			$gap
			<td $at>" . input_button("&nbsp;Letter \rTemplates", "view_standing('letter');", $shape) . "</td>
			$gap
		</tr>
		<tr>
			<td $at>" . input_button("&nbsp;Payment \rMethods", "view_standing('pay_meth');", $shape) . "</td>
			$gap
			<td $at>" . input_button("&nbsp;Payment \rRoutes", "view_standing('pay_route');", $shape) . "</td>
			$gap
			<td $at>" . input_button("&nbsp;Report \rFields", "view_standing('rep_field');", $shape) . "</td>
			$gap
			<td $at>
				";
				if (role_check('*', $role_man))
					print input_button("&nbsp;System \rInformation", "view_standing('misc_info');", $shape);
				else 
					print input_button("&nbsp;System \rInformation", '', "$shape disabled");
				print "
			</td>
			<td $at>" . input_button("&nbsp;User \rPermissions", "view_standing('user_perm');", $shape) . "</td>
			$gap
			<td $at>" . input_button("&nbsp;User \rRoles", "view_standing('user_role');", $shape) . "</td>
			$gap
			<td $at>" . input_button("&nbsp;User \rRole \rPermissions", "view_standing('role_perm');", $shape) . "</td>
			$gap
		</tr>
		<tr height=\"50\">
			<td colspan=\"11\"><hr></td>
		</tr>
		<tr>
			<td colspan=\"11\"><h3>Miscellaneous (non-standing) Data</h3></td>
		</tr>
		<tr>
			<td $at>
				";
				if (role_check('*', $role_man))
					print input_button("&nbsp;Client \rGroups", "view_standing('client_group');", $shape);
				else 
					print input_button("&nbsp;Client \rGroups", '', "$shape disabled");
				print "
			</td>
			$gap
			<td $at>
				";
				if (role_check('*', $role_man))
					print input_button("&nbsp;Job \rGroups", "view_standing('job_group');", $shape);
				else 
					print input_button("&nbsp;Job \rGroups", '', "$shape disabled");
				print "
			</td>
			$gap
		</tr>
		</table>
		";
		print_form_end();
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
} # screen_content_2()

function javascript()
{
	global $item; # screen_content()
	global $item_id; # screen_content()
	global $safe_amp; # settings.php
	global $safe_slash;
	global $uni_pound; # settings.php
	
	print "
	<script type=\"text/javascript\">
	
	function edit_letter(lid)
	{
		document.form_letters.task.value = 'edit';
		document.form_letters.item_id.value = lid;
		please_wait_on_submit();
		document.form_letters.submit();
	}
	
	function goto_job(jid, vno)
	{
		document.form_job.task.value = 'view';
		document.form_job.sc_text.value = vno;
		document.form_job.job_id.value = jid;
		document.form_job.submit();
	}

	function letters_show(shw)
	{
		document.form_letters.ltr_show.value = shw;
		please_wait_on_submit();
		document.form_letters.submit();
	}
	
	function update_item(control, field_type, date_check)
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
		// date_check:
		//				see 'd' above
		"
		var field_name = control.name;
		var field_value = trim(control.value);
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
		var url = 'standing_ajax.php?op=u&t={$item}&i={$item_id}&n=' + field_name + '&v=' + field_value;
		url = url + '&ran=' + Math.random();
		//alert(url);
		xmlHttp2.onreadystatechange = stateChanged_update_item;
		xmlHttp2.open('GET', url, true);
		xmlHttp2.send(null);
	}
	
	function stateChanged_update_item()
	{
		if (xmlHttp2.readyState == 4)
		{
			var resptxt = xprint_noscript(xmlHttp2.responseText);
			if (resptxt && (resptxt != 'ok'))
			{
				//if (!resptxt)
				//	resptxt = 'No response from ajax call!';
				if (resptxt)
					alert(resptxt);
			}
		}
	}
	
	function edit_standing(item, id)
	{
		document.form_standing.task.value = 'edit';
		document.form_standing.item.value = item;
		document.form_standing.item_id.value = id;
		please_wait_on_submit();
		document.form_standing.submit();
	}
	
	function view_standing(item)
	{
		document.form_standing.task.value = 'view';
		document.form_standing.item.value = item;
		document.form_standing.item_id.value = '';
		please_wait_on_submit();
		document.form_standing.submit();
	}
	
	function save_standing(item)
	{
		if (item == 'activity')
		{
			if (trim(document.form_standing.act_tdx.value) == '')
			{
				alert('Please enter an Activity Code');
				return false;
			}
		}
		else if (item == 'job_status')
		{
			if (trim(document.form_standing.j_status.value) == '')
			{
				alert('Please enter a Status Code');
				return false;
			}
		}
		else if (item == 'job_target')
		{
			if (trim(document.form_standing.jta_name.value) == '')
			{
				alert('Please enter a Target Name');
				return false;
			}
			if ( (trim(document.form_standing.jta_time.value) == '') || (!isNumeric(trim(document.form_standing.jta_time.value),0,0,0,0)) || (!(0 < trim(document.form_standing.jta_time.value))) )
			{
				alert('Please enter a Time');
				return false;
			}
			if (!(0 < document.form_standing.job_type_id.value))
			{
				alert('Please enter a Job Type');
				return false;
			}
			if ( (trim(document.form_standing.jta_fee.value) == '') || (!isNumeric(trim(document.form_standing.jta_fee.value),0,1,0,0)) || (!(0 <= trim(document.form_standing.jta_fee.value))) )
			{
				alert('Please enter a Fee');
				return false;
			}
		}
		else if (item == 'letter_sequence')
		{
			//REVIEW: need to add tests for user entry for letter sequences
		}
		else if (item == 'job_type')
		{
			if (trim(document.form_standing.jt_type.value) == '')
			{
				alert('Please enter a Job Type');
				return false;
			}
			if (trim(document.form_standing.jt_code.value) == '')
			{
				alert('Please enter a Job Code');
				return false;
			}
			if ( (trim(document.form_standing.jt_fee.value) == '') || (!isNumeric(trim(document.form_standing.jt_fee.value),0,1,0,0)) || (!(0 <= trim(document.form_standing.jt_fee.value))) )
			{
				alert('Please enter a Fee');
				return false;
			}
			if ( (trim(document.form_standing.jt_days.value) == '') || (!isNumeric(trim(document.form_standing.jt_days.value),0,0,0,0)) || (!(0 <= trim(document.form_standing.jt_days.value))) )
			{
				alert('Please enter a number of days within which a job of this type should be completed');
				return false;
			}
		}
		else if (item == 'pay_meth')
		{
			if (trim(document.form_standing.payment_method.value) == '')
			{
				alert('Please enter a Payment Method');
				return false;
			}
		}
		else if (item == 'pay_route')
		{
			if (trim(document.form_standing.payment_route.value) == '')
			{
				alert('Please enter a Payment Route');
				return false;
			}
		}
		else if (item == 'client_group')
		{
			if (trim(document.form_standing.group_name.value) == '')
			{
				alert('Please enter a Group Name');
				return false;
			}
		}
		document.form_standing.task.value = 'save';
		document.form_standing.item.value = item;
		document.form_standing.item_id.value = '';
		please_wait_on_submit();
		document.form_standing.submit();
	}
	
	</script>
	";
}

function print_roles()
{
	global $ac;
	global $ar;
	global $grey;
	global $item; # screen_content()
	global $item_id; # screen_content()
	global $role_man; # settings.php
	global $roles;
	global $tr_colour_1;
	global $tr_colour_2;
	
	$can_edit = role_check('*', $role_man);
	if ($can_edit && ((0 < $item_id) || ($item_id == -1)))
	{
		edit_role();
		return;
	}
	
	print "<h3>User Roles</h3>";
	
	if ($can_edit)
	{
		print_form_start();
		# User cannot create new roles.  #print input_button('Create New', "edit_standing('$item', -1);");
		print_form_end();
	}
	
	if (!$roles)
	{
		print "<p>There are no user roles in the database</p>";
		return;
	}
	print "
	<form name=\"form_roles\" action=\"" . server_php_self() . "\" method=\"post\">
	<table name=\"table_roles\" class=\"spaced_table\" border=\"0\">
	<tr>
		<th>Role Name</th><th>Role Code</th><th>Description</th><th>Sort Order</th>
		<th>Edit</th><th>Obsolete</th><th $grey>DB ID</th>
	</tr>
	";
	$trcol = $tr_colour_1;
	foreach ($roles as $one_role)
	{
		$id = $one_role['USER_ROLE_ID'];
		$col = ($one_role['OBSOLETE'] ? "style=\"color:red;\"" : "");
		$obsolete = ($one_role['OBSOLETE'] ? "Yes" : "No");
		$can_edit_2 = ((($one_role['UR_CODE'] == 'DEV') && (!global_debug())) ? false : $can_edit);
		print "
		<tr bgcolor=\"$trcol\" onmouseover=\"this.className='Highlight'\" onmouseout=\"this.className='Normal'\">
			<td $col>{$one_role['UR_ROLE']}</td>
			<td $col>{$one_role['UR_CODE']}</td>
			<td $col>{$one_role['UR_COMMENT']}</td>
			<td $col>{$one_role['SORT_ORDER']}</td>
			<td>" . input_button('Edit', "edit_standing('$item', $id)", $can_edit_2 ? '' : 'disabled') . "</td>
			<td $col $ac>$obsolete</td>
			<td $grey $ar>$id</td>
		</tr>";
		$trcol = (($trcol == $tr_colour_1) ? $tr_colour_2 : $tr_colour_1);
	}
	print "
	</table><!--table_roles-->
	</form><!--form_roles-->
	";
}

function print_payment_methods()
{
	global $ac;
	global $ar;
	global $grey;
	global $item; # screen_content()
	global $item_id; # screen_content()
	global $pay_meths;
	global $role_man; # settings.php
	global $tr_colour_1;
	global $tr_colour_2;
	
	$can_edit = role_check('*', $role_man);
	if ($can_edit && ((0 < $item_id) || ($item_id == -1)))
	{
		edit_pay_meth();
		return;
	}
	
	print "<h3>Payment Methods</h3>";
	
	if ($can_edit)
	{
		print_form_start();
		print input_button('Create New', "edit_standing('$item', -1);");
		print_form_end();
	}
	
	if (!$pay_meths)
	{
		print "<p>There are no payment methods in the database</p>";
		return;
	}
	
	print "
	<form name=\"form_pay_meths\" action=\"" . server_php_self() . "\" method=\"post\">
	<table name=\"table_pay_meths\" class=\"spaced_table\" border=\"0\">
	<tr>
		<th>Payment Method</th><th>TDX Code</th><th>Sort Order</th>
		<th>Edit</th><th>Obsolete</th><th $grey>DB ID</th>
	</tr>
	";
	$trcol = $tr_colour_1;
	foreach ($pay_meths as $one_meth)
	{
		$id = $one_meth['PAYMENT_METHOD_ID'];
		$col = ($one_meth['OBSOLETE'] ? "style=\"color:red;\"" : "");
		$obsolete = ($one_meth['OBSOLETE'] ? "Yes" : "No");
		print "
		<tr bgcolor=\"$trcol\" onmouseover=\"this.className='Highlight'\" onmouseout=\"this.className='Normal'\">
			<td $col>{$one_meth['PAYMENT_METHOD']}</td>
			<td $col>{$one_meth['TDX_CODE']}</td>
			<td $col $ar>{$one_meth['SORT_ORDER']}</td>
			<td>" . input_button('Edit', "edit_standing('$item', $id)", $can_edit ? '' : 'disabled') . "</td>
			<td $col $ac>$obsolete</td>
			<td $grey $ar>$id</td>
		</tr>";
		$trcol = (($trcol == $tr_colour_1) ? $tr_colour_2 : $tr_colour_1);
	}
	print "
	</table><!--table_pay_meths-->
	</form><!--form_pay_meths-->
	";
} # print_payment_methods()

function print_payment_routes()
{
	global $ac;
	global $ar;
	global $grey;
	global $item; # screen_content()
	global $item_id; # screen_content()
	global $pay_routes;
	global $role_man; # settings.php
	global $tr_colour_1;
	global $tr_colour_2;

	$can_edit = role_check('*', $role_man);
	if ($can_edit && ((0 < $item_id) || ($item_id == -1)))
	{
		edit_pay_route();
		return;
	}
	
	print "<h3>Payment Routes</h3>";
	
	if ($can_edit)
	{
		print_form_start();
		print input_button('Create New', "edit_standing('$item', -1);");
		print_form_end();
	}
	
	if (!$pay_routes)
	{
		print "<p>There are no payment routes in the database</p>";
		return;
	}
	print "
	<form name=\"form_pay_routes\" action=\"" . server_php_self() . "\" method=\"post\">
	<table name=\"table_pay_routes\" class=\"spaced_table\" border=\"0\">
	<tr>
		<th>Payment Route</th><th>Code</th><th>Sort Order</th><th>Edit</th><th>Obsolete</th><th $grey>DB ID</th>
	</tr>
	";
	$trcol = $tr_colour_1;
	foreach ($pay_routes as $one_route)
	{
		$id = $one_route['PAYMENT_ROUTE_ID'];
		$col = ($one_route['OBSOLETE'] ? "style=\"color:red;\"" : "");
		$obsolete = ($one_route['OBSOLETE'] ? "Yes" : "No");
		print "
		<tr bgcolor=\"$trcol\" onmouseover=\"this.className='Highlight'\" onmouseout=\"this.className='Normal'\">
			<td $col>{$one_route['PAYMENT_ROUTE']}</td>
			<td $col>{$one_route['PR_CODE']}</td>
			<td $col>{$one_route['SORT_ORDER']}</td>
			<td $col>" . input_button('Edit', "edit_standing('$item', $id)", $can_edit ? '' : 'disabled') . "</td>
			<td $col $ac>$obsolete</td>
			<td $grey $ar>$id</td>
		</tr>";
		$trcol = (($trcol == $tr_colour_1) ? $tr_colour_2 : $tr_colour_1);
	}
	print "
	</table><!--table_pay_routes-->
	</form><!--form_pay_routes-->
	";
}

function print_job_targets()
{
	global $ac;
	global $ar;
	global $grey;
	global $item; # screen_content()
	global $item_id; # screen_content()
	global $job_targets;
	global $role_man; # settings.php
	global $tr_colour_1;
	global $tr_colour_2;
	
	$can_edit = role_check('*', $role_man);
	if ($can_edit && ((0 < $item_id) || ($item_id == -1)))
	{
		edit_job_target();
		return;
	}
	
	print "<h3>Job Targets</h3>";
	
	if ($can_edit)
	{
		print_form_start();
		print input_button('Create New', "edit_standing('$item', -1);");
		print_form_end();
	}
	
	if (!$job_targets)
	{
		print "<p>There are no job targets in the database</p>";
		return;
	}
	
	print "
	<form name=\"form_job_targets\" action=\"" . server_php_self() . "\" method=\"post\">
	<table name=\"table_job_targets\" class=\"spaced_table\"border=\"0\">
	<tr>
		<th>Job Type</th><th>Target Name</th><th>Time (hours)</th><th>Fee</th>
		<th>Edit</th><th>Obsolste</th><th $grey>DB ID</th>
	</tr>
	";
	$trcol = $tr_colour_1;
	foreach ($job_targets as $one_job_target)
	{
		$id = $one_job_target['JOB_TARGET_ID'];
		$col = ($one_job_target['OBSOLETE'] ? "style=\"color:red;\"" : "");
		$obsolete = ($one_job_target['OBSOLETE'] ? "Yes" : "No");
		print "
		<tr bgcolor=\"$trcol\" onmouseover=\"this.className='Highlight'\" onmouseout=\"this.className='Normal'\">
			<td $col>{$one_job_target['JT_TYPE']}</td>
			<td $col>{$one_job_target['JTA_NAME']}</td>
			<td $col $ar>{$one_job_target['JTA_TIME']}</td>
			<td $col $ar>" . money_format_kdb($one_job_target['JTA_FEE']) . "</td>
			<td>" . input_button('Edit', "edit_standing('$item', $id)", $can_edit ? '' : 'disabled') . "</td>
			<td $col $ac>$obsolete</td>
			<td $grey $ar>$id</td>
		</tr>";
		$trcol = (($trcol == $tr_colour_1) ? $tr_colour_2 : $tr_colour_1);
	}
	print "
	</table><!--table_job_targets-->
	</form><!--form_job_targets-->
	";
} # print_job_targets()

function print_job_types()
{
	global $ac;
	global $ar;
	global $grey;
	global $item; # screen_content()
	global $item_id; # screen_content()
	global $job_types;
	global $role_man; # settings.php
	global $tr_colour_1;
	global $tr_colour_2;
	
	$can_edit = role_check('*', $role_man);
	if ($can_edit && ((0 < $item_id) || ($item_id == -1)))
	{
		edit_job_type();
		return;
	}
	
	print "<h3>Job Types</h3>";
	
	if ($can_edit)
	{
		print_form_start();
		print input_button('Create New', "edit_standing('$item', -1);");
		print_form_end();
	}
	
	if (!$job_types)
	{
		print "<p>There are no job types in the database</p>";
		return;
	}
	
	print "
	<form name=\"form_job_types\" action=\"" . server_php_self() . "\" method=\"post\">
	<table name=\"table_job_types\" class=\"spaced_table\" border=\"0\">
	<tr>
		<th>Job Type</th><th>Code</th><th>Fee</th><th>Days</th>
		<th>Edit</th><th>Obsolete</th><th $grey>DB ID</th>
	</tr>
	";
	$trcol = $tr_colour_1;
	foreach ($job_types as $one_job_type)
	{
		$id = $one_job_type['JOB_TYPE_ID'];
		$col = ($one_job_type['OBSOLETE'] ? "style=\"color:red;\"" : "");
		$obsolete = ($one_job_type['OBSOLETE'] ? "Yes" : "No");
		print "
		<tr bgcolor=\"$trcol\" onmouseover=\"this.className='Highlight'\" onmouseout=\"this.className='Normal'\">
			<td $col>{$one_job_type['JT_TYPE']}</td>
			<td $col>{$one_job_type['JT_CODE']}</td>
			<td $col $ar>" . money_format_kdb($one_job_type['JT_FEE']) . "</td>
			<td $col $ar>{$one_job_type['JT_DAYS']}</td>
			<td>" . input_button('Edit', "edit_standing('$item', $id)", $can_edit ? '' : 'disabled') . "</td>
			<td $col $ac>$obsolete</td>
			<td $grey $ar>{$one_job_type['JOB_TYPE_ID']}</td>
		</tr>";
		$trcol = (($trcol == $tr_colour_1) ? $tr_colour_2 : $tr_colour_1);
	}
	print "
	</table><!--table_job_types-->
	</form><!--form_job_types-->
	";
}

function print_letter_sequences()
{
	global $ac;
	global $ar;
	global $col7;
	global $grey;
	global $item; # screen_content()
	global $item_id; # screen_content()
	global $letter_sequences;
	global $role_man; # settings.php
	global $tr_colour_1;
	global $tr_colour_2;
	
	$can_edit = role_check('*', $role_man);
	if ($can_edit && ((0 < $item_id) || ($item_id == -1)))
	{
		edit_letter_sequence();
		return;
	}
	
	print "<h3>Letter Sequences</h3>";
	
	if ($can_edit)
	{
		print_form_start();
		print input_button('Create New', "edit_standing('$item', -1);");
		print_form_end();
	}
	
	if (!$letter_sequences)
	{
		print "<p>There are no letter sequences in the database</p>";
		return;
	}
	
	print "
	<form name=\"form_letter_sequences\" action=\"" . server_php_self() . "\" method=\"post\">
	<table name=\"table_letter_sequences\" class=\"spaced_table\" border=\"0\">
	<tr>
		<th>Client</th><th>Client Name</th><th>Sequence</th><th>Letter</th><th>Days After</th><th>Obsolete</th><th>Edit</th><th $grey>DB ID</th>
	</tr>
	";
	$trcol = $tr_colour_1;
	$prev_client = -1;
	foreach ($letter_sequences as $one_letter_sequence)
	{
		if ((0 <= $prev_client) && ($prev_client != $one_letter_sequence['C_CODE']))
		{
			print "
			<tr bgcolor=\"gray\">
				<td $col7></td>
			</tr>";
		}
		$prev_client = $one_letter_sequence['C_CODE'];
		
		$id = $one_letter_sequence['LETTER_SEQ_ID'];
		$c_code = ($one_letter_sequence['C_CODE'] ? $one_letter_sequence['C_CODE'] : '-');
		$c_co_name = ($one_letter_sequence['C_CO_NAME'] ? $one_letter_sequence['C_CO_NAME'] : '*DEFAULT*');
		$col = ($one_letter_sequence['OBSOLETE'] ? "style=\"color:red;\"" : "");
		$obsolete = ($one_letter_sequence['OBSOLETE'] ? "Yes" : "No");
		
		print "
		<tr bgcolor=\"$trcol\" onmouseover=\"this.className='Highlight'\" onmouseout=\"this.className='Normal'\">
			<td $col $ar>$c_code</td>
			<td $col>$c_co_name</td>
			<td $col $ar>{$one_letter_sequence['SEQ_NUM']}</td>
			<td $col>{$one_letter_sequence['LETTER_NAME']}</td>
			<td $col $ar>{$one_letter_sequence['SEQ_DAYS']}</td>
			<td $col $ac>$obsolete</td>
			<td>" . input_button('Edit', "edit_standing('$item', $id)", $can_edit ? '' : 'disabled') . "</td>
			<td $grey $ar>$id</td>
		</tr>";
		$trcol = (($trcol == $tr_colour_1) ? $tr_colour_2 : $tr_colour_1);
	}
	print "
	</table><!--table_letter_sequences-->
	</form><!--form_letter_sequences-->
	";
}

function print_job_statuses()
{
	global $ac;
	global $ar;
	global $grey;
	global $item; # screen_content()
	global $item_id; # screen_content()
	global $job_statuses;
	global $role_man; # settings.php
	global $tr_colour_1;
	global $tr_colour_2;
	
	$can_edit = role_check('*', $role_man);
	if ($can_edit && ((0 < $item_id) || ($item_id == -1)))
	{
		edit_job_status();
		return;
	}
	
	print "<h3>Job Statuses (Collection Jobs only)</h3>";
	
	if ($can_edit)
	{
		print_form_start();
		print input_button('Create New', "edit_standing('$item', -1);");
		print_form_end();
	}
	
	if (!$job_statuses)
	{
		print "<p>There are no job statuses in the database</p>";
		return;
	}
	print "
	<form name=\"form_job_statuses\" action=\"" . server_php_self() . "\" method=\"post\">
	<table name=\"table_job_statuses\" class=\"spaced_table\" border=\"0\">
	<tr>
		<th>Job Status</th><th>Description</th><th>Job Closed</th><th>Edit</th><th>Obsolete</th><th $grey>DB ID</th>
	</tr>
	";
	$trcol = $tr_colour_1;
	foreach ($job_statuses as $one_job_status)
	{
		$id = $one_job_status['JOB_STATUS_ID'];
		$col = ($one_job_status['OBSOLETE'] ? "style=\"color:red;\"" : "");
		$obsolete = ($one_job_status['OBSOLETE'] ? "Yes" : "No");
		print "
		<tr bgcolor=\"$trcol\" onmouseover=\"this.className='Highlight'\" onmouseout=\"this.className='Normal'\">
			<td $col>{$one_job_status['J_STATUS']}</td>
			<td $col>{$one_job_status['J_STTS_DESCR']}</td>
			<td $col $ac>" .($one_job_status['J_STTS_CLOSED'] ? 'Yes' : '') . "</td>
			<td>" . input_button('Edit', "edit_standing('$item', $id)", $can_edit ? '' : 'disabled') . "</td>
			<td $col $ac>$obsolete</td>
			<td $grey $ar>$id</td>
		</tr>";
		$trcol = (($trcol == $tr_colour_1) ? $tr_colour_2 : $tr_colour_1);
	}
	print "
	</table><!--table_job_statuses-->
	</form><!--form_job_statuses-->
	";
}

function print_perms()
{
	global $ar;
	global $grey;
	global $perms;
	
	print "<h3>User Permissions (not currently in use)</h3>";
	
	if (!$perms)
	{
		print "<p>There are no user permissions in the database</p>";
		return;
	}
	print "
	<form name=\"form_perms\" action=\"" . server_php_self() . "\" method=\"post\">
	<table name=\"table_perms\" border=\"1\">
	<tr>
		<th>System</th><th>Permission Name</th><th>Permission Code</th><th>Sort Order</th><th>Deleted</th><th $grey>DB ID</th>
	</tr>
	";
	foreach ($perms as $one_perm)
	{
		print "
		<tr><td>" . (($one_perm['UP_SYS'] == 'C') ? 'Collect' : (($one_perm['UP_SYS'] == 'T') ? 'Trace' : 'ERROR')). "</td>
			<td>{$one_perm['UP_PERM']}</td><td>{$one_perm['UP_CODE']}</td>
			<td>{$one_perm['SORT_ORDER']}</td><td $ar>{$one_perm['OBSOLETE']}</td>
			<td $grey $ar>{$one_perm['USER_PERMISSION_ID']}</td>
		</tr>";
	}
	print "
	</table><!--table_perms-->
	</form><!--form_perms-->
	";
}

function print_role_perms()
{
	global $ar;
	global $col4;
	global $grey;
	global $perms;
	global $role_perms;
	global $roles;
	
	print "<h3>Role Permissions (not currently in use)</h3>
		<p>These are the default permissions for each User Role.</p>
		";
	
	if (!$role_perms)
	{
		print "<p>There are no role permissions in the database</p>";
		return;
	}
	
	#dprint(print_r($role_perms,1));#
	
	print "
	<form name=\"form_role_perms\" action=\"" . server_php_self() . "\" method=\"post\">
	<table name=\"table_roles\" border=\"1\">
	<tr>
		<th>Role Name</th><th>Role Code</th><th>Permissions</th><th $grey>DB ID</th>
	</tr>
	";
	foreach ($roles as $one_role)
	{
		print "
		<tr><td>{$one_role['UR_ROLE']}</td><td>{$one_role['UR_CODE']}</td>
			<td>
			";
			if (array_key_exists($one_role['USER_ROLE_ID'], $role_perms))
			{
				print "
				<table name=\"table_perms\" border=\"1\">
				<tr>
					<th>Permission Name</th><th>Permission Code</th><th>Granted</th><th $grey>DB ID</th>
				</tr>
				<tr>
					<td $col4>Trace system:</td>
				</tr>
				";
				foreach ($perms as $one_perm)
				{
					if ($one_perm['UP_SYS'] == 'T')
					{
						$ticked = (in_array($one_perm['USER_PERMISSION_ID'],
											$role_perms[$one_role['USER_ROLE_ID']]['T']) ? true : false);
						print "
						<tr><td>{$one_perm['UP_PERM']}</td><td>{$one_perm['UP_CODE']}</td>
							<td>" . input_tickbox('', 'x', 1, $ticked, '', 'disabled') . "</td>
							<td $grey $ar>{$one_perm['USER_PERMISSION_ID']}</td>
						</tr>";
					}
				}
				print "
				<tr>
					<td $col4>Collection system:</td>
				</tr>
				";
				foreach ($perms as $one_perm)
				{
					if ($one_perm['UP_SYS'] == 'C')
					{
						$ticked = (in_array($one_perm['USER_PERMISSION_ID'],
											$role_perms[$one_role['USER_ROLE_ID']]['C']) ? true : false);
						print "
						<tr><td>{$one_perm['UP_PERM']}</td><td>{$one_perm['UP_CODE']}</td>
							<td>" . input_tickbox('', 'x', 1, $ticked, '', 'disabled') . "</td>
							<td $grey $ar>{$one_perm['USER_PERMISSION_ID']}</td>
						</tr>";
					}
				}
				print "
				</table><!--table_perms-->
				";
			}
			else 
				print "None";
			print "
			</td>
			<td $grey $ar>{$one_role['USER_ROLE_ID']}</td>
		</tr>";
	}
	print "
	</table><!--table_roles-->
	</form><!--form_role_perms-->
	";
}

function sql_get_misc_info($item_id=0)
{
	# This function is only needed for this screen.
	
	global $misc_info;
	
	$where = array();
	if (0 < $item_id)
		$where[] = "MISC_INFO_ID=$item_id";
	$where = ($where ? ("WHERE (" . implode(') AND (', $where) . ")") : '');
	
	sql_encryption_preparation('MISC_INFO');
	$sql = "SELECT MISC_INFO_ID, MISC_SYS, MISC_KEY, MISC_DESCR, MISC_TYPE, VALUE_INT, VALUE_DEC, VALUE_TXT, VALUE_DT,
				" . sql_decrypt('VALUE_ENC', '', true) . "
			FROM MISC_INFO 
			$where ORDER BY MISC_INFO_ID";
	sql_execute($sql);
	$misc_info = array();
	while (($newArray = sql_fetch_assoc()) != false)
	{
		$misc_info[$newArray['MISC_INFO_ID']] = $newArray;
		$misc_info[$newArray['MISC_INFO_ID']]['VALUE_ALL'] = $newArray['VALUE_INT'] . $newArray['VALUE_DEC'] . 
										$newArray['VALUE_TXT'] . $newArray['VALUE_DT'] . $newArray['VALUE_ENC'];
	}
}

function print_misc_info()
{
	global $ar;
	global $grey;
	global $item; # screen_content()
	global $item_id; # screen_content()
	global $misc_info;
	global $role_man; # settings.php
	global $tr_colour_1;
	global $tr_colour_2;
	
	$can_edit = role_check('*', $role_man);
	if ($can_edit && ((0 < $item_id) || ($item_id == -1)))
	{
		edit_misc_info();
		return;
	}
	
	print "<h3>System Information</h3>";
	
	if ($can_edit)
	{
		print_form_start();
		# User cannot create new Misc Info items.  #print input_button('Create New', "edit_standing('$item', -1);");
		print_form_end();
	}
	
	print "
	<form name=\"form_misc\" action=\"" . server_php_self() . "\" method=\"post\">
	<table name=\"table_misc\" class=\"spaced_table\" border=\"0\">
	<tr>
		<th>System</th><th>Item Name</th><th>Description</th><th>Type</th><th>Value</th><th>Edit</th><th $grey>DB ID</th>
	</tr>
	";
	$trcol = $tr_colour_1;
	foreach ($misc_info as $one_mi)
	{
		$id = $one_mi['MISC_INFO_ID'];
		if (($one_mi['MISC_KEY'] == 'OUT_PW_T') || ($one_mi['MISC_KEY'] == 'PWORD_T') || ($one_mi['MISC_KEY'] == 'PWORD2_T') || 
			($one_mi['MISC_KEY'] == 'OUT_PW_C') || ($one_mi['MISC_KEY'] == 'PWORD_C') || ($one_mi['MISC_KEY'] == 'PWORD2_C') || 
			($one_mi['MISC_KEY'] == 'APP_PW'))
			$value = '(undisclosed)';
		else 
			$value = $one_mi['VALUE_ALL'];
		if (($one_mi['MISC_KEY'] == 'VAT_RATE') || ($one_mi['MISC_KEY'] == 'VAT_NUM') || ($one_mi['MISC_KEY'] == 'APP_PW') || 
			($one_mi['MISC_KEY'] == 'EMAIL_FROM_T') || ($one_mi['MISC_KEY'] == 'NAME_FROM_T') || ($one_mi['MISC_KEY'] == 'EMAIL_FROM_C') || 
			($one_mi['MISC_KEY'] == 'NAME_FROM_C'))
			$can_edit_2 = $can_edit;
		else 
			$can_edit_2 = false;
		if ($one_mi['MISC_TYPE'] == 'I')
		{
			$type = 'Integer';
			$aln = $ar;
		}
		elseif ($one_mi['MISC_TYPE'] == 'F')
		{
			$type = 'Decimal';
			$aln = $ar;
		}
		elseif ($one_mi['MISC_TYPE'] == 'T')
		{
			$type = 'Text';
			$aln = '';
		}
		elseif ($one_mi['MISC_TYPE'] == 'D')
		{
			$type = 'Date/time';
			$aln = '';
		}
		elseif ($one_mi['MISC_TYPE'] == 'E')
		{
			$type = 'Encrypted';
			$aln = '';
		}
		$aln = (is_numeric_kdb($value) ? $ar : '');
		print "
		<tr bgcolor=\"$trcol\" onmouseover=\"this.className='Highlight'\" onmouseout=\"this.className='Normal'\">
			<td>{$one_mi['MISC_SYS']}</td>
			<td>{$one_mi['MISC_KEY']}</td>
			<td>{$one_mi['MISC_DESCR']}</td>
			<td>$type</td>
			<td $aln>$value</td>
			<td>" . input_button('Edit', "edit_standing('$item', $id)", $can_edit_2 ? '' : 'disabled') . "</td>
			<td $grey $ar>$id</td>
		</tr>";
		$trcol = (($trcol == $tr_colour_1) ? $tr_colour_2 : $tr_colour_1);
	}
	print "
	</table><!--table_misc-->
	</form><!--form_misc-->
	";
} # print_misc_info()

function print_letters()
{
	global $at;
	global $col2;
	global $feedback_39;
	global $feedback_42;
	global $grey;
	global $item_id;
	global $letter_templates;
	global $role_man;
	
	#dprint(print_r($letter_templates,1));

	$can_edit = role_check('*', $role_man);
	if ($can_edit && ((0 < $item_id) || ($item_id == -1)))
	{
		edit_letter();
		return;
	}
	
	$ltr_show = post_val('ltr_show', true);
	if ($ltr_show == 1)
		$ltr_show = true;
	elseif ($ltr_show == -1)
		$ltr_show = false;
	else
		$ltr_show = false;
		
	print "
	<h4>Letter Templates</h4>
	
	<form name=\"form_letters\" action=\"" . server_php_self() . "\" method=\"post\">
		" . input_button('Hide content', "letters_show(-1)", "style=\"display:" . ($ltr_show ? 'block' : 'none') . ";\"", "btn_ltr_hide") . "
		" . input_button('Show content', "letters_show(1)", "style=\"display:" . ($ltr_show ? 'none' : 'block') . ";\"", "btn_ltr_show") . "
		" . input_hidden('task', 'view') . "
		" . input_hidden('item', 'letter') . "
		" . input_hidden('item_id', '') . "
		" . input_hidden('ltr_show', '') . "
		
	<table class=\"spaced_table\" border=\"1\">
	<tr><th>System</th><th>Letter Name</th>" . ($ltr_show ? "<th $col2>Letter</th>" : "") . 
		"" . ($feedback_39 ? "<th>Non-Managers</th>" : '') . "" . ($feedback_42 ? "<th>Auto-Approve</th>" : '') . "<th></th><th $grey>DB ID</th>
	</tr>
	";
	foreach ($letter_templates as $tem)
	{
		$letter = '';
		if ($tem['JT_T_JOB_TYPE_ID'] == '')
		{
			$sys = "Collect";
			$lt_non_man = ($tem['LT_NON_MAN'] ? 'Yes' : '-');
			$lt_auto_app = ($tem['LT_AUTO_APP'] ? 'Yes' : '-');
			if ($ltr_show)
			{
				$letter_1 = $tem['LETTER_TEMPLATE'];
				$letter = "<td $col2>" . input_textarea('letter_template', 50, 70, $letter_1, 'readonly') . "</td>";
			}
		}
		else
		{
			$sys = "Trace";
			$lt_non_man = '-';
			$lt_auto_app = '-';
			if ($ltr_show)
			{
				$letter_1 = $tem['JT_T_OPEN'];
				$letter_2 = $tem['JT_T_CLOSE'];
				$letter = "<td>" . input_textarea('jt_t_open', 10, 30, $letter_1, 'readonly') . "</td>
							<td>" . input_textarea('jt_t_close', 10, 30, $letter_2, 'readonly') . "</td>";
			}
		}
		$letter_name = $tem['LETTER_NAME'];
		print "
		<tr><td $at>$sys</td><td $at>$letter_name</td>{$letter}
			" . ($feedback_39 ? "<td $at>$lt_non_man</td>" : '') . "
			" . ($feedback_42 ? "<td $at>$lt_auto_app</td>" : '') . "
			<td $at>" . input_button('Edit', "edit_letter({$tem['LETTER_TYPE_ID']})")  . "</td>
			<td $grey $at>{$tem['LETTER_TYPE_ID']}</td>
		</tr>
		";
	}
	print "
	</table>
	</form><!--form_letters-->
	";
	
}

function print_client_groups()
{
	global $ac;
	global $ar;
	global $at;
	global $client_groups_with_clients;
	global $col3;
	global $grey;
	global $item; # screen_content()
	global $item_id; # screen_content()
	global $role_man;
	global $tr_colour_1;
	global $tr_colour_2;
	
	$can_edit = role_check('*', $role_man);
	if ($can_edit && ((0 < $item_id) || ($item_id == -1)))
	{
		edit_client_group();
		return;
	}
	
	print "<h3>Client Groups</h3>";
	
	if ($can_edit)
	{
		print_form_start();
		print input_button('Create New', "edit_standing('$item', -1);");
		print_form_end();
	}
	
	if (!$client_groups_with_clients)
	{
		print "<p>There are no Client Groups in the database</p>";
		return;
	}
	
	$screen = "
	<form name=\"form_groups\" action=\"" . server_php_self() . "\" method=\"post\">
	<table class=\"spaced_table\" border=\"0\">
	<tr>
		<th>Client Group</th><th>Edit</th><th $col3>Clients (Code, Name, ID)</th><th>Obsolete</th><th $grey>Group DB ID</th>
	</tr>
	";
	$trcol = $tr_colour_1;
	foreach ($client_groups_with_clients as $cg_id => $cg_data)
	{
		$col = ($cg_data['OBSOLETE'] ? "style=\"color:red;\"" : "");
		$obsolete = ($cg_data['OBSOLETE'] ? "Obs." : "-");
		$rspan = count($cg_data['CLIENTS']);
		if (0 < count($cg_data['CLIENTS']))
		{
			foreach ($cg_data['CLIENTS'] as $client)
			{
				$screen .= "
				<tr bgcolor=\"$trcol\" onmouseover=\"this.className='Highlight'\" onmouseout=\"this.className='Normal'\">
					";
					if ($rspan)
						$screen .= "
						<td rowspan=\"$rspan\" $at $col>{$cg_data['GROUP_NAME']}</td>
						<td>" . input_button('Edit', "edit_standing('$item', $cg_id)", $can_edit ? '' : 'disabled') . "</td>
						";
					else
						$screen .= "
						<td></td>
						";
					$screen .= "
						<td $ar $col>{$client['C_CODE']}</td>
						<td $col>{$client['C_CO_NAME']}</td>
						<td $ar $grey>{$client['CLIENT2_ID']}</td>
						";
					if ($rspan)
						$screen .= "
						<td $col $ac>$obsolete</td>
						";
					else
						$screen .= "
						<td></td>
						";
					$screen .= "<td $ar $grey>$cg_id</td>
				</tr>
				";
				$trcol = (($trcol == $tr_colour_1) ? $tr_colour_2 : $tr_colour_1);
				$rspan = '';
			}
		}
		else
		{
			$screen .= "
			<tr bgcolor=\"$trcol\" onmouseover=\"this.className='Highlight'\" onmouseout=\"this.className='Normal'\">
				<td $col>{$cg_data['GROUP_NAME']}</td>
				<td>" . input_button('Edit', "edit_standing('$item', $cg_id)", $can_edit ? '' : 'disabled') . "</td>
				<td $col3>&nbsp;</td>
				<td $col $ac>$obsolete</td>
				<td $ar $grey>$cg_id</td>
			</tr>
			";
			$trcol = (($trcol == $tr_colour_1) ? $tr_colour_2 : $tr_colour_1);
			$rspan = '';
		}
		$screen .= "<tr><td>&nbsp;</td></tr>";
	}
	$screen .= "
		</table>
		</form><!--form_groups-->
		";
	print $screen;
	
} # print_client_groups()

function print_activity()
{
	global $activities;
	global $ac;
	global $ar;
	global $feedback_38;
	global $grey;
	global $item; # screen_content()
	global $item_id; # screen_content()
	global $role_man; # settings.php
	global $tr_colour_1;
	global $tr_colour_2;
	
	$can_edit = role_check('*', $role_man);
	if ($can_edit && ((0 < $item_id) || ($item_id == -1)))
	{
		edit_activity();
		return;
	}
	
	print "<h3>Activity Codes</h3>";
	
	if ($can_edit)
	{
		print_form_start();
		print input_button('Create New', "edit_standing('$item', -1);");
		print_form_end();
	}
	
	if (!$activities)
	{
		print "<p>There are no activity codes in the database</p>";
		return;
	}
	
	print "
	<form name=\"form_activity\" action=\"" . server_php_self() . "\" method=\"post\">
	<table name=\"table_activity\" class=\"spaced_table\" border=\"0\">
	<tr>
		<th>Activity Code</th><th>Short Description</th><th>Long Description</th><th>Dialler Event</th><th>Short-List</th>" . ($feedback_38 ? "<th>Non-managers</th>" : '') . "
		<th>Edit</th><th>Obsolete</th><th $grey>DB ID</th>
	</tr>
	";
	$trcol = $tr_colour_1;
	foreach ($activities as $one_act)
	{
		$id = $one_act['ACTIVITY_ID'];
		$col = ($one_act['OBSOLETE'] ? "style=\"color:red;\"" : "");
		$dialler_ev = ($one_act['DIALLER_EV'] ? "Yes" : "-");
		$act_non_man = ($one_act['ACT_NON_MAN'] ? "Yes" : "-");
		$obsolete = ($one_act['OBSOLETE'] ? "Yes" : "-");
		print "
		<tr bgcolor=\"$trcol\" onmouseover=\"this.className='Highlight'\" onmouseout=\"this.className='Normal'\">
			<td $col>{$one_act['ACT_TDX']}</td>
			<td $col>{$one_act['ACT_DSHORT']}</td>
			<td $col>{$one_act['ACT_DLONG']}</td>
			<td $col $ac>$dialler_ev</td>
			<td $col $ac>{$one_act['SHORT_LIST']}</td>
			" . ($feedback_38 ? "<td $col $ac>$act_non_man</td>" : '') . "
			<td>" . input_button('Edit', "edit_standing('$item', $id)", $can_edit ? '' : 'disabled') . "</td>
			<td $col $ac>$obsolete</td>
			<td $grey $ar>$id</td>
		</tr>";
		$trcol = (($trcol == $tr_colour_1) ? $tr_colour_2 : $tr_colour_1);
	}
	print "
	</table><!--table_activity-->
	</form><!--form_activity-->
	";
} # print_activity()

function print_adjust()
{
	global $adjusts;
	global $ac;
	global $ar;
	global $grey;
	global $item; # screen_content()
	global $item_id; # screen_content()
	global $role_man; # settings.php
	global $tr_colour_1;
	global $tr_colour_2;
	
	$can_edit = role_check('*', $role_man);
	if ($can_edit && ((0 < $item_id) || ($item_id == -1)))
	{
		edit_adjust();
		return;
	}
	
	print "<h3>Adjustment Reasons</h3>";
	
	if ($can_edit)
	{
		print_form_start();
		print input_button('Create New', "edit_standing('$item', -1);");
		print_form_end();
	}
	
	if (!$adjusts)
	{
		print "<p>There are no adjustment reasons in the database</p>";
		return;
	}
	
	print "
	<form name=\"form_adjust\" action=\"" . server_php_self() . "\" method=\"post\">
	<table name=\"table_adjust\" class=\"spaced_table\" border=\"0\">
	<tr>
		<th>Adjustment Reason</th><th>Sort Order</th>
		<th>Edit</th><th>Obsolete</th><th $grey>DB ID</th>
	</tr>
	";
	$trcol = $tr_colour_1;
	foreach ($adjusts as $one_adj)
	{
		$id = $one_adj['ADJUSTMENT_ID'];
		$col = ($one_adj['OBSOLETE'] ? "style=\"color:red;\"" : "");
		$obsolete = ($one_adj['OBSOLETE'] ? "Yes" : "No");
		print "
		<tr bgcolor=\"$trcol\" onmouseover=\"this.className='Highlight'\" onmouseout=\"this.className='Normal'\">
			<td $col>{$one_adj['ADJUSTMENT']}</td>
			<td $col>{$one_adj['SORT_ORDER']}</td>
			<td>" . input_button('Edit', "edit_standing('$item', $id)", $can_edit ? '' : 'disabled') . "</td>
			<td $col $ac>$obsolete</td>
			<td $grey $ar>$id</td>
		</tr>";
		$trcol = (($trcol == $tr_colour_1) ? $tr_colour_2 : $tr_colour_1);
	}
	print "
	</table><!--table_adjust-->
	</form><!--form_adjust-->
	";
} # print_adjust()

function print_report_fields()
{
	global $ac;
	global $ar;
	global $grey;
	global $item; # screen_content()
	global $item_id; # screen_content()
	global $report_fields;
	global $role_man; # settings.php
	global $tr_colour_1;
	global $tr_colour_2;
	
	$can_edit = role_check('*', $role_man);
	if ($can_edit && ((0 < $item_id) || ($item_id == -1)))
	{
		edit_report_field();
		return;
	}
	
	print "<h3>Report Fields</h3>";
	
	if ($can_edit)
	{
		print_form_start();
		# The user cannot create new report fields
		#print input_button('Create New', "edit_standing('$item', -1);");
		print_form_end();
	}
	
	if (!$report_fields)
	{
		print "<p>There are no Report Fields in the database</p>";
		return;
	}
	
	print "
	<form name=\"form_report_fields\" action=\"" . server_php_self() . "\" method=\"post\">
	<table name=\"table_report_fields\" class=\"spaced_table\" border=\"0\">
	<tr>
		<th>Report<br>Field</th><th>Available<br>for<br>reporting</th>
		<th>For<br>Ana-<br>lysis</th><th>For<br>Job<br>Detail</th>
		<th>Trace</th><th>Collect</th><th>Code</th>
		<th>Long Description</th><th>Sort<br>Order</th>
		<th>Edit</th><th $grey>DB ID</th>
	</tr>
	";
	$trcol = $tr_colour_1;
	foreach ($report_fields as $one_field)
	{
		$id = $one_field['REPORT_FIELD_ID'];
		$col = (((!$one_field['RF_SEL']) || ((!$one_field['RF_TRACE']) && (!$one_field['RF_COLL']))) ? "style=\"color:red;\"" : "");
		if ($one_field['RF_SEL'])
		{
			$sel = "Avail";
			$col = '';
		}
		else
		{
			$sel = "";
			$col = "style=\"color:red;\"";
		}
		$analysis = ($one_field['RF_ANALYSIS'] ? 'Ana' : '');
		if ($analysis && (!$one_field['RF_SEL']))
			$analysis = "($analysis)";
		$job_detail = ($one_field['RF_JOB_DETAIL'] ? 'J.D.' : '');
		if ($job_detail && (!$one_field['RF_SEL']))
			$job_detail = "($job_detail)";
		$trace = ($one_field['RF_TRACE'] ? 'Tra' : '');
		if ($trace && (!$one_field['RF_SEL']))
			$trace = "($trace)";
		$collect = ($one_field['RF_COLL'] ? 'Col' : '');
		if ($collect && (!$one_field['RF_SEL']))
			$collect = "($collect)";
		print "
		<tr bgcolor=\"$trcol\" onmouseover=\"this.className='Highlight'\" onmouseout=\"this.className='Normal'\">
			<td $col>{$one_field['RF_DESCR']}</td><td $ac>$sel</td>
			<td $col $ac>$analysis</td><td $ac>$job_detail</td>
			<td $col $ac>$trace</td><td $ac>$collect</td><td>{$one_field['RF_CODE']}</td>
			<td $col>{$one_field['RF_LONG']}</td><td $ar>{$one_field['SORT_ORDER']}</td>
			<td>" . input_button('Edit', "edit_standing('$item', $id)", $can_edit ? '' : 'disabled') . "</td>
			<td $grey $ar>$id</td>
		</tr>";
		$trcol = (($trcol == $tr_colour_1) ? $tr_colour_2 : $tr_colour_1);
	}
	print "
	</table><!--table_report_fields-->
	</form><!--form_report_fields-->
	";
} # print_report_fields()

function print_job_groups()
{
	global $ac;
	global $ar;
	global $at;
	global $col3;
	global $col4;
	global $grey;
	global $job_groups;
	
	if (count($job_groups) == 0)
	{
		print "<p>There are no Job Groups in the database</p>";
		return;
	}
	
	$screen = "
		<table border=\"1\">
		<tr>
			<th rowspan=\"2\" $at>Job Group<br>ID</th><th $col3>Jobs</th>
		</tr>
		<tr>
			<th>VilNo</th><th>Sequence</th><th>JOB_ID</th>
		</tr>
		";
	$screen .= "<tr><td $col4>&nbsp;</td></tr>";
	foreach ($job_groups as $group_id => $group_info)
	{
		#$group_name = $group_info['NAME'];
		$jobs_list = $group_info['JOBS'];
		
		$rspan = count($jobs_list);
		if (0 < $rspan)
		{
			foreach ($jobs_list as $job_info)
			{
				$screen .= "
				<tr>
					";
					if ($rspan)
						$screen .= "<td rowspan=\"$rspan\" $ar>$group_id</td>";
					else
						$screen .= "";
					if (0 < $job_info['JOB_ID'])
						$screen .= "
							<td $ar>" . input_button($job_info['J_VILNO'], "goto_job({$job_info['JOB_ID']},{$job_info['J_VILNO']});",
													"style=\"width:80px\"") . "</td>
							<td $ar>{$job_info['J_SEQUENCE']}</td>
							<td $ar $grey>{$job_info['JOB_ID']}</td>
							";
					else
						$screen .= "<td $col3 $ac>(Empty Job)</td>";
					$screen .= "
					</tr>
					";
				$rspan = 0;
			}
		}
		else
		{
			$screen .= "
			<tr>
				<td $ar>$group_id</td>
				<td $col3 $ac>(No Jobs)</td>
			</tr>
			";
		}
		$screen .= "<tr><td $col4>&nbsp;</td></tr>";
	}
	$screen .= "
		</table>
		";
	print $screen;
	
	print "
	<form name=\"form_job\" action=\"jobs.php\" method=\"post\" target=\"_blank\" rel=\"noopener\">
		" . input_hidden('task', '') . "
		" . input_hidden('job_id', '') . "
		" . input_hidden('sc_text', '') . "
	</form><!--form_job-->
	";
	
} # print_job_groups()

function print_form_start()
{
	print "
	<form name=\"form_standing\" action=\"" . server_php_self() . "\" method=\"post\">
	" .
	input_hidden('task', '') .
	input_hidden('item', '') .
	input_hidden('item_id', '') .
	"
	";
}

function print_form_end()
{
	print "
	</form><!--form_standing-->
	";
}

function edit_activity()
{
	global $activities;
	global $feedback_38;
	global $item; # screen_content()
	global $item_id; # screen_content()
	global $yn_list;
	
	$the_item = $activities[0];
	$editing = ((0 < $item_id) ? true : false);
	$onchange_txt = ($editing ? "onchange=\"update_item(this);\"" : '');
	$onchange_num = ($editing ? "onchange=\"update_item(this,'n');\"" : '');
	
	print_form_start();
	print input_button("Back", "view_standing('$item')");
	
	print "	
	<p style=\"color:blue;\">Note: " .
		($editing ? "changes are saved automatically" : "please click SAVE after entering values") . ".</p>
	<h2>" . ($editing ? "Edit Activity" : "Create New Activity") . "</h2>
	<table>
		<tr>
			<td>Activity Code</td>
			<td>" . input_textbox('act_tdx', $the_item['ACT_TDX'], 10, 10, $onchange_txt) . "</td>
		</tr>
		<tr>
			<td>Short Description</td>
			<td>" . input_textbox('act_dshort', $the_item['ACT_DSHORT'], 63, 200, $onchange_txt) . "</td>
		</tr>
		<tr>
			<td>Long Description</td>
			<td>" . input_textarea('act_dlong', 5, 60, $the_item['ACT_DLONG'], $onchange_txt) . "</td>
		</tr>
		<tr>
			<td>Dialler Event</td>
			<td>" . input_select('dialler_ev', $yn_list, $the_item['DIALLER_EV'], $onchange_num, true) . "</td>
		</tr>
		" . ($feedback_38 ? "
		<tr>
			<td>Available to<br>Non-managers</td>
			<td>" . input_select('act_non_man', $yn_list, $the_item['ACT_NON_MAN'], $onchange_num, true) . "</td>
		</tr>
		" : '') . "
		";
		if ($editing)
		{
			print "
			<tr>
				<td>Obsolete</td>
				<td>" . input_select('obsolete', $yn_list, $the_item['OBSOLETE'], $onchange_num, true) . "</td>
			</tr>
			<tr>
				<td>Database ID</td>
				<td>" . input_textbox('db_id', $item_id, 4, 10, "readonly") . "</td>
			</tr>
			";
		}
		else
		{
			print "
			<tr>
				<td>" .input_button("SAVE", "save_standing('$item')") . "</td>
			</tr>
			";
		}
		print "
	</table>
	";

	print_form_end();
} # edit_activity()

function edit_adjust()
{
	global $adjusts;
	global $item; # screen_content()
	global $item_id; # screen_content()
	global $yn_list;
	
	$the_item = $adjusts[0];
	$editing = ((0 < $item_id) ? true : false);
	$onchange_txt = ($editing ? "onchange=\"update_item(this);\"" : '');
	$onchange_num = ($editing ? "onchange=\"update_item(this,'n');\"" : '');
	
	print_form_start();
	print input_button("Back", "view_standing('$item')");
	
	print "	
	<p style=\"color:blue;\">Note: " .
		($editing ? "changes are saved automatically" : "please click SAVE after entering values") . ".</p>
	<h2>" . ($editing ? "Edit Adjustment Reason" : "Create New Adjustment Reason") . "</h2>
	<table>
		<tr>
			<td>Adjustment Reason</td>
			<td>" . input_textbox('adjustment', $the_item['ADJUSTMENT'], 30, 100, $onchange_txt) . "</td>
		</tr>
		<tr>
			<td>Sort Order</td>
			<td>" . input_textbox('sort_order', $the_item['SORT_ORDER'], 4, 10, $onchange_num) . "</td>
		</tr>
		";
		if ($editing)
		{
			print "
			<tr>
				<td>Obsolete</td>
				<td>" . input_select('obsolete', $yn_list, $the_item['OBSOLETE'], $onchange_num, true) . "</td>
			</tr>
			<tr>
				<td>Database ID</td>
				<td>" . input_textbox('db_id', $item_id, 4, 10, "readonly") . "</td>
			</tr>
			";
		}
		else
		{
			print "
			<tr>
				<td>" .input_button("SAVE", "save_standing('$item')") . "</td>
			</tr>
			";
		}
		print "
	</table>
	";

	print_form_end();
} # edit_adjust()

function save_activity()
{
	$act_tdx = post_val('act_tdx');
	$act_dshort = post_val('act_dshort');
	$act_dlong = post_val('act_dlong');
	$dialler_ev = (post_val('dialler_ev') ? 1 : 0);
	$act_non_man = (post_val('act_non_man') ? 1 : 0);

	audit_setup_gen('ACTIVITY_SD', 'ACTIVITY_ID', 0, '', '');
	
	$act_tdx = quote_smart($act_tdx, true, true);
	$act_dshort = quote_smart($act_dshort, true, true);
	$act_dlong = quote_smart($act_dlong, true, true);
	
	$fields = "ACT_TDX,  ACT_DSHORT,  ACT_DLONG,  DIALLER_EV,  ACT_NON_MAN";
	$values = "$act_tdx, $act_dshort, $act_dlong, $dialler_ev, $act_non_man";
	
	$sql = "INSERT INTO ACTIVITY_SD ($fields) VALUES ($values)";
	$new_id = sql_execute($sql, true); # audited
	return $new_id;
	
} # save_activity()

function save_adjust()
{
	$adjustment = post_val('adjustment');
	$sort_order = post_val('sort_order',true);

	audit_setup_gen('ADJUSTMENT_SD', 'ADJUSTMENT_ID', 0, '', '');
	
	$adjustment = quote_smart($adjustment, true, true);
	
	$fields = "ADJUSTMENT,  SORT_ORDER";
	$values = "$adjustment, $sort_order";
	
	$sql = "INSERT INTO ADJUSTMENT_SD ($fields) VALUES ($values)";
	$new_id = sql_execute($sql, true); # audited
	return $new_id;
	
} # save_adjust()

function edit_job_status()
{
	global $job_statuses;
	global $item; # screen_content()
	global $item_id; # screen_content()
	global $yn_list;
	
	$the_item = $job_statuses[0];
	$editing = ((0 < $item_id) ? true : false);
	$onchange_txt = ($editing ? "onchange=\"update_item(this);\"" : '');
	$onchange_num = ($editing ? "onchange=\"update_item(this,'n');\"" : '');
	
	print_form_start();
	print input_button("Back", "view_standing('$item')");
	
	print "	
	<p style=\"color:blue;\">Note: " .
		($editing ? "changes are saved automatically" : "please click SAVE after entering values") . ".</p>
	<h2>" . ($editing ? "Edit Job Status" : "Create New Job Status") . "</h2>
	<table>
		<tr>
			<td>Status Code</td>
			<td>" . input_textbox('j_status', $the_item['J_STATUS'], 100, 100, $onchange_txt) . "</td>
		</tr>
		<tr>
			<td>Description</td>
			<td>" . input_textbox('j_stts_descr', $the_item['J_STTS_DESCR'], 100, 200, $onchange_txt) . "</td>
		</tr>
		<tr>
			<td>Indicates that<br>job is closed</td>
			<td>" . input_select('j_stts_closed', $yn_list, $the_item['J_STTS_CLOSED'], $onchange_num, true) . "</td>
		</tr>
		";
		if ($editing)
		{
			print "
			<tr>
				<td>Obsolete</td>
				<td>" . input_select('obsolete', $yn_list, $the_item['OBSOLETE'], $onchange_num, true) . "</td>
			</tr>
			<tr>
				<td>Database ID</td>
				<td>" . input_textbox('db_id', $item_id, 4, 10, "readonly") . "</td>
			</tr>
			";
		}
		else
		{
			print "
			<tr>
				<td>" .input_button("SAVE", "save_standing('$item')") . "</td>
			</tr>
			";
		}
		print "
	</table>
	";

	print_form_end();
} # edit_job_status()

function save_job_status()
{
	$j_status = post_val('j_status');
	$j_stts_descr = post_val('j_stts_descr');
	$j_stts_closed = (post_val('j_stts_closed', true) ? 1 : 0);

	audit_setup_gen('JOB_STATUS_SD', 'JOB_STATUS_ID', 0, '', '');
	
	$j_status = quote_smart($j_status, true, true);
	$j_stts_descr = quote_smart($j_stts_descr, true, true);
	
	$fields = "J_STATUS,  J_STTS_DESCR,  J_STTS_CLOSED";
	$values = "$j_status, $j_stts_descr, $j_stts_closed";
	
	$sql = "INSERT INTO JOB_STATUS_SD ($fields) VALUES ($values)";
	$new_id = sql_execute($sql, true); # audited
	return $new_id;
	
} # save_job_status()

function edit_job_target()
{
	global $job_targets;
	global $item; # screen_content()
	global $item_id; # screen_content()
	global $yn_list;
	
	$the_item = $job_targets[0];
	$editing = ((0 < $item_id) ? true : false);
	$onchange_txt = ($editing ? "onchange=\"update_item(this);\"" : '');
	$onchange_num = ($editing ? "onchange=\"update_item(this,'n');\"" : '');
	
	$job_types = sql_get_job_types(true, false);
	
	print_form_start();
	print input_button("Back", "view_standing('$item')");
	
	print "	
	<p style=\"color:blue;\">Note: " .
		($editing ? "changes are saved automatically" : "please click SAVE after entering values") . ".</p>
	<h2>" . ($editing ? "Edit Job Target" : "Create New Job Target") . "</h2>
	<table>
		<tr>
			<td>Target Name</td>
			<td>" . input_textbox('jta_name', $the_item['JTA_NAME'], 50, 100, $onchange_txt) . "</td>
		</tr>
		<tr>
			<td>Time (Hours)</td>
			<td>" . input_textbox('jta_time', $the_item['JTA_TIME'], 4, 10, $onchange_num) . "</td>
		</tr>
		<tr>
			<td>Job Type</td>
			<td>" . input_select('job_type_id', $job_types, $the_item['JOB_TYPE_ID'], $onchange_num, true) . "</td>
		</tr>
		<tr>
			<td>Fee (&pound;)</td>
			<td>" . input_textbox('jta_fee', $the_item['JTA_FEE'], 4, 10, $onchange_num) . "</td>
		</tr>
		";
		if ($editing)
		{
			print "
			<tr>
				<td>Obsolete</td>
				<td>" . input_select('obsolete', $yn_list, $the_item['OBSOLETE'], $onchange_num, true) . "</td>
			</tr>
			<tr>
				<td>Database ID</td>
				<td>" . input_textbox('db_id', $item_id, 4, 10, "readonly") . "</td>
			</tr>
			";
		}
		else
		{
			print "
			<tr>
				<td>" .input_button("SAVE", "save_standing('$item')") . "</td>
			</tr>
			";
		}
		print "
	</table>
	";

	print_form_end();
} # edit_job_target()

function save_job_target()
{
	$job_type_id = post_val('job_type_id', true);
	$jta_name = post_val('jta_name');
	$jta_time = post_val('jta_time', true);
	$jta_fee = floatval(post_val('jta_fee'));

	audit_setup_gen('JOB_TARGET_SD', 'JOB_TARGET_ID', 0, '', '');
	
	$jta_name = quote_smart($jta_name, true, true);
	
	$fields = "JOB_TYPE_ID,  JTA_NAME,  JTA_TIME,  JTA_FEE";
	$values = "$job_type_id, $jta_name, $jta_time, $jta_fee";
	
	$sql = "INSERT INTO JOB_TARGET_SD ($fields) VALUES ($values)";
	$new_id = sql_execute($sql, true); # audited
	return $new_id;
	
} # save_job_target()

function edit_job_type()
{
	global $job_types;
	global $id_JOB_TYPE_maximum_built_in;
	global $item; # screen_content()
	global $item_id; # screen_content()
	global $yn_list;
	
	$the_item = $job_types[0];
	$editing = ((0 < $item_id) ? true : false);
	$onchange_txt = ($editing ? "onchange=\"update_item(this);\"" : '');
	$onchange_num = ($editing ? "onchange=\"update_item(this,'n');\"" : '');
	
	print_form_start();
	print input_button("Back", "view_standing('$item')");
	
	print "	
	<p style=\"color:blue;\">Note: " .
		($editing ? "changes are saved automatically" : "please click SAVE after entering values") . ".</p>
	<h2>" . ($editing ? "Edit Job Type" : "Create New Job Type") . "</h2>
	<table>
		<tr>
			<td>Job Type</td>
			<td>" . input_textbox('jt_type', $the_item['JT_TYPE'], 50, 100, $onchange_txt) . "</td>
		</tr>
		<tr>
			<td>Code</td>
			<td>" . input_textbox('jt_code', $the_item['JT_CODE'], 10, 10, 
						(($item_id == -1) || ($id_JOB_TYPE_maximum_built_in < $item_id)) ? $onchange_txt : 'readonly') . "</td>
		</tr>
		<tr>
			<td>Fee</td>
			<td>" . input_textbox('jt_fee', $the_item['JT_FEE'], 10, 10, $onchange_num) . "</td>
		</tr>
		<tr>
			<td>Days</td>
			<td>" . input_textbox('jt_days', $the_item['JT_DAYS'], 10, 10, $onchange_num) . "</td>
		</tr>
		";
		if ($editing)
		{
			print "
			<tr>
				<td>Obsolete</td>
				<td>" . input_select('obsolete', $yn_list, $the_item['OBSOLETE'], $onchange_num, true) . "</td>
			</tr>
			<tr>
				<td>Database ID</td>
				<td>" . input_textbox('db_id', $item_id, 4, 10, "readonly") . "</td>
			</tr>
			";
		}
		else
		{
			print "
			<tr>
				<td>" .input_button("SAVE", "save_standing('$item')") . "</td>
			</tr>
			";
		}
		print "
	</table>
	";

	print_form_end();
} # edit_job_type()

function edit_letter_sequence()
{
	global $letter_sequences;
	global $item; # screen_content()
	global $item_id; # screen_content()
	global $yn_list;
	
	$the_item = $letter_sequences[0];
	$editing = ((0 < $item_id) ? true : false);
	$onchange_num = ($editing ? "onchange=\"update_item(this,'n');\"" : '');
	
	$clients = sql_get_clients_for_select('*', 0, true);
	$letters = sql_get_letter_types_for_client($the_item['CLIENT2_ID'], 'c');
	
	print_form_start();
	print input_button("Back", "view_standing('$item')");
	
	print "	
	<p style=\"color:blue;\">Note: " .
		($editing ? "changes are saved automatically" : "please click SAVE after entering values") . ".</p>
	<h2>" . ($editing ? "Edit Letter Sequence" : "Create New Letter Sequence") . "</h2>
	<table>
		<tr>
			<td>Client</td>
			<td>" . input_select('client2_id', $clients, $the_item['CLIENT2_ID'], $editing ? 'disabled' : '') . "</td>
		</tr>
		<tr>
			<td>Sequence</td>
			<td>" . input_textbox('seq_num', $the_item['SEQ_NUM'], 10, 10, $onchange_num) . "</td>
		</tr>
		<tr>
			<td>Letter</td>
			<td>" . input_select('letter_type_id', $letters, $the_item['LETTER_TYPE_ID'], $onchange_num) . "</td>
		</tr>
		<tr>
			<td>Days After</td>
			<td>" . input_textbox('seq_days', $the_item['SEQ_DAYS'], 10, 10, $onchange_num) . "</td>
		</tr>
		";
		if ($editing)
		{
			print "
			<tr>
				<td>Obsolete</td>
				<td>" . input_select('obsolete', $yn_list, $the_item['OBSOLETE'], $onchange_num, true) . "</td>
			</tr>
			<tr>
				<td>Database ID</td>
				<td>" . input_textbox('db_id', $item_id, 4, 10, "readonly") . "</td>
			</tr>
			";
		}
		else
		{
			print "
			<tr>
				<td>" .input_button("SAVE", "save_standing('$item')") . "</td>
			</tr>
			";
		}
		print "
	</table>
	";

	print_form_end();
} # edit_letter_sequence()

function save_job_type()
{
	$jt_type = post_val('jt_type');
	$jt_code = post_val('jt_code');
	$jt_fee = floatval(post_val('jt_fee'));
	$jt_days = post_val('jt_days', true);

	audit_setup_gen('JOB_TYPE_SD', 'JOB_TYPE_ID', 0, '', '');
	
	$jt_type = quote_smart($jt_type, true, true);
	$jt_code = quote_smart($jt_code, true, true);
	
	$fields = "JT_TYPE,  JT_CODE,  JT_FEE,  JT_DAYS";
	$values = "$jt_type, $jt_code, $jt_fee, $jt_days";
	
	$sql = "INSERT INTO JOB_TYPE_SD ($fields) VALUES ($values)";
	$new_id = sql_execute($sql, true); # audited
	return $new_id;
	
} # save_job_type()

function save_letter_sequence()
{
	$client2_id = post_val('client2_id', true);
	$seq_num = post_val('seq_num', true);
	$letter_type_id = floatval(post_val('letter_type_id'));
	$seq_days = post_val('seq_days', true);

	audit_setup_gen('LETTER_SEQ', 'LETTER_SEQ_ID', 0, '', '');
	
	$fields = "CLIENT2_ID,  SEQ_NUM,  LETTER_TYPE_ID,  SEQ_DAYS";
	$values = "$client2_id, $seq_num, $letter_type_id, $seq_days";
	
	$sql = "INSERT INTO LETTER_SEQ ($fields) VALUES ($values)";
	dprint($sql);
	$new_id = sql_execute($sql, true); # audited
	return $new_id;
	
} # save_letter_sequence()

function edit_pay_meth()
{
	global $item; # screen_content()
	global $item_id; # screen_content()
	global $pay_meths;
	global $yn_list;
	
	$the_item = $pay_meths[0];
	$editing = ((0 < $item_id) ? true : false);
	$onchange_txt = ($editing ? "onchange=\"update_item(this);\"" : '');
	$onchange_num = ($editing ? "onchange=\"update_item(this,'n');\"" : '');
	
	print_form_start();
	print input_button("Back", "view_standing('$item')");
	
	print "	
	<p style=\"color:blue;\">Note: " .
		($editing ? "changes are saved automatically" : "please click SAVE after entering values") . ".</p>
	<h2>" . ($editing ? "Edit Payment Method" : "Create New Payment Method") . "</h2>
	<table>
		<tr>
			<td>Payment Method</td>
			<td>" . input_textbox('payment_method', $the_item['PAYMENT_METHOD'], 50, 100, $onchange_txt) . "</td>
		</tr>
		<tr>
			<td>TDX Code</td>
			<td>" . input_textbox('tdx_code', $the_item['TDX_CODE'], 10, 10, $onchange_txt) . "</td>
		</tr>
		<tr>
			<td>Sort Order</td>
			<td>" . input_textbox('sort_order', $the_item['SORT_ORDER'], 4, 10, $onchange_num) . "</td>
		</tr>
		";
		if ($editing)
		{
			print "
			<tr>
				<td>Obsolete</td>
				<td>" . input_select('obsolete', $yn_list, $the_item['OBSOLETE'], $onchange_num, true) . "</td>
			</tr>
			<tr>
				<td>Database ID</td>
				<td>" . input_textbox('db_id', $item_id, 4, 10, "readonly") . "</td>
			</tr>
			";
		}
		else
		{
			print "
			<tr>
				<td>" .input_button("SAVE", "save_standing('$item')") . "</td>
			</tr>
			";
		}
		print "
	</table>
	";

	print_form_end();
} # edit_pay_meth()

function save_pay_meth()
{
	$payment_method = post_val('payment_method');
	$tdx_code = post_val('tdx_code');
	$sort_order = post_val('sort_order', true);

	audit_setup_gen('PAYMENT_METHOD_SD', 'PAYMENT_METHOD_ID', 0, '', '');
	
	$payment_method = quote_smart($payment_method, true, true);
	$tdx_code = quote_smart($tdx_code, true, true);
	
	$fields = "PAYMENT_METHOD,  TDX_CODE,  SORT_ORDER";
	$values = "$payment_method, $tdx_code, $sort_order";
	
	$sql = "INSERT INTO PAYMENT_METHOD_SD ($fields) VALUES ($values)";
	$new_id = sql_execute($sql, true); # audited
	return $new_id;
	
} # save_pay_meth()

function edit_pay_route()
{
	global $item; # screen_content()
	global $item_id; # screen_content()
	global $pay_routes;
	global $yn_list;
	
	$the_item = $pay_routes[0];
	$editing = ((0 < $item_id) ? true : false);
	$onchange_txt = ($editing ? "onchange=\"update_item(this);\"" : '');
	$onchange_num = ($editing ? "onchange=\"update_item(this,'n');\"" : '');
	
	print_form_start();
	print input_button("Back", "view_standing('$item')");
	
	print "	
	<p style=\"color:blue;\">Note: " .
		($editing ? "changes are saved automatically" : "please click SAVE after entering values") . ".</p>
	<h2>" . ($editing ? "Edit Payment Route" : "Create New Payment Route") . "</h2>
	<table>
		<tr>
			<td>Payment Route</td>
			<td>" . input_textbox('payment_route', $the_item['PAYMENT_ROUTE'], 50, 100, $onchange_txt) . "</td>
		</tr>
		<tr>
			<td>Code</td>
			<td>" . input_textbox('pr_code', $the_item['PR_CODE'], 4, 10, $onchange_txt) . "</td>
		</tr>
		<tr>
			<td>Sort Order</td>
			<td>" . input_textbox('sort_order', $the_item['SORT_ORDER'], 4, 10, $onchange_num) . "</td>
		</tr>
		";
		if ($editing)
		{
			print "
			<tr>
				<td>Obsolete</td>
				<td>" . input_select('obsolete', $yn_list, $the_item['OBSOLETE'], $onchange_num, true) . "</td>
			</tr>
			<tr>
				<td>Database ID</td>
				<td>" . input_textbox('db_id', $item_id, 4, 10, "readonly") . "</td>
			</tr>
			";
		}
		else
		{
			print "
			<tr>
				<td>" .input_button("SAVE", "save_standing('$item')") . "</td>
			</tr>
			";
		}
		print "
	</table>
	";

	print_form_end();
} # edit_pay_route()

function save_pay_route()
{
	$payment_route = post_val('payment_route');
	$pr_code = post_val('pr_code');
	$sort_order = post_val('sort_order', true);

	audit_setup_gen('PAYMENT_ROUTE_SD', 'PAYMENT_ROUTE_ID', 0, '', '');
	
	$payment_route = quote_smart($payment_route, true, true);
	$pr_code = quote_smart($pr_code, true, true);
	
	$sql = "SELECT COUNT(*) FROM PAYMENT_ROUTE_SD WHERE PR_CODE=$pr_code";
	sql_execute($sql);
	$count = 0;
	while (($newArray = sql_fetch()) != false)
		$count = $newArray[0];
	
	if ($count == 0)
	{
		$fields = "PAYMENT_ROUTE,  PR_CODE,  SORT_ORDER";
		$values = "$payment_route, $pr_code, $sort_order";

		$sql = "INSERT INTO PAYMENT_ROUTE_SD ($fields) VALUES ($values)";
		$new_id = sql_execute($sql, true); # audited
	}
	else
	{
		dprint("The code \"" . post_val('pr_code') . "\" already exists so the new Payment Route cannot be added to the database.", true);
		$new_id = 0;
	}
	return $new_id;
	
} # save_pay_route()

function edit_report_field()
{
	global $item; # screen_content()
	global $item_id; # screen_content()
	global $report_fields;
	global $yn_list;
	
	$the_item = $report_fields[0];
	$editing = true; # User cannot create new report fields. #((0 < $item_id) ? true : false);
	$onchange_txt = ($editing ? "onchange=\"update_item(this);\"" : '');
	$onchange_num = ($editing ? "onchange=\"update_item(this,'n');\"" : '');
	
	print_form_start();
	print input_button("Back", "view_standing('$item')");
	
	print "	
	<p style=\"color:blue;\">Note: " .
		($editing ? "changes are saved automatically" : "ERROR") . ".</p>
	<h2>" . ($editing ? "Edit Report Field" : "ERROR") . "</h2>
	<table>
		<tr>
			<td>Field Code</td>
			<td>" . input_textbox('rf_code', $the_item['RF_CODE'], 10, 20, "readonly") . "</td>
		</tr>
		<tr>
			<td>Description</td>
			<td>" . input_textbox('rf_descr', $the_item['RF_DESCR'], 20, 200, $onchange_txt) . "</td>
		</tr>
		<tr>
			<td>Long Description</td>
			<td>" . input_textarea('rf_long', 5, 60, $the_item['RF_LONG'], $onchange_txt) . "</td>
		</tr>
		";
		if ($editing)
		{
			print "
			<tr>
				<td>Available to<br>report on</td>
				<td>" . input_select('rf_sel', $yn_list, $the_item['RF_SEL'], $onchange_num, true) . "</td>
			</tr>
			";
		}
		print "
		<tr>
			<td>For Analysis<br>reports</td>
			<td>" . input_select('rf_analysis', $yn_list, $the_item['RF_ANALYSIS'], "disabled", true) . "</td>
		</tr>
		<tr>
			<td>For Job Detail<br>reports</td>
			<td>" . input_select('rf_job_detail', $yn_list, $the_item['RF_JOB_DETAIL'], "disabled", true) . "</td>
		</tr>
		<tr>
			<td>For Collection<br>reports</td>
			<td>" . input_select('rf_coll', $yn_list, $the_item['RF_COLL'], "disabled", true) . "</td>
		</tr>
		<tr>
			<td>For Trace<br>reports</td>
			<td>" . input_select('rf_trace', $yn_list, $the_item['RF_TRACE'], "disabled", true) . "</td>
		</tr>
		<tr>
			<td>Sort Order</td>
			<td>" . input_textbox('sort_order', $the_item['SORT_ORDER'], 4, 10, $onchange_num) . "</td>
		</tr>
		";
		if ($editing)
		{
			print "
			<tr>
				<td>Database ID</td>
				<td>" . input_textbox('db_id', $item_id, 4, 10, "readonly") . "</td>
			</tr>
			";
		}
		print "
	</table>
	";

	print_form_end();
} # edit_report_field()

function edit_misc_info()
{
	global $item; # screen_content()
	global $item_id; # screen_content()
	global $misc_info;
	
	$the_item = $misc_info[$item_id];
	$editing = true; # User cannot create new MISC_INFO items  #((0 < $item_id) ? true : false);
	$onchange_txt = ($editing ? "onchange=\"update_item(this);\"" : '');
	$onchange_num = ($editing ? "onchange=\"update_item(this,'n');\"" : '');
	$onchange_dt = ($editing ? "onchange=\"update_item(this,'d');\"" : '');
	
	if ($the_item['MISC_SYS'] == 'C')
		$system = 'Collection';
	elseif ($the_item['MISC_SYS'] == 'T')
		$system = 'Trace';
	else
		$system = 'All';
	
	if ($the_item['MISC_TYPE'] == 'I')
		$type = 'Integer';
	elseif ($the_item['MISC_TYPE'] == 'F')
		$type = 'Decimal';
	elseif ($the_item['MISC_TYPE'] == 'T')
		$type = 'Text';
	elseif ($the_item['MISC_TYPE'] == 'D')
		$type = 'Date/time';
	elseif ($the_item['MISC_TYPE'] == 'E')
		$type = 'Encrypted';
	else
	{
		dprint("edit_misc_info(): unrecognised MISC_TYPE \"{$the_item['MISC_TYPE']}\", ID $item_id", true);
		return;
	}
	
	print_form_start();
	print input_button("Back", "view_standing('$item')");
	
	print "	
	<p style=\"color:blue;\">Note: " .
		($editing ? "changes are saved automatically" : "ERROR") . ".</p>
	<h2>" . ($editing ? "Edit System Information" : "ERROR") . "</h2>
	<table>
		<tr>
			<td>System</td>
			<td>" . input_textbox('misc_sys', $system, 10, 10, "readonly") . "</td>
		</tr>
		<tr>
			<td>S.I. Name</td>
			<td>" . input_textbox('misc_key', $the_item['MISC_KEY'], 10, 10, "readonly") . "</td>
		</tr>
		<tr>
			<td>Description</td>
			<td>" . input_textbox('misc_descr', $the_item['MISC_DESCR'], 63, 1000, $onchange_txt) . "</td>
		</tr>
		<tr>
			<td>Type</td>
			<td>" . input_textbox('misc_type', $type, 10, 10, "readonly") . "</td>
		</tr>
		<tr>
			<td>Value</td>
			<td>
			";
			if ($the_item['MISC_TYPE'] == 'I')
				print input_textbox('value_int', $the_item['VALUE_INT'], 10, 10, $onchange_num);
			elseif ($the_item['MISC_TYPE'] == 'F')
				print input_textbox('value_dec', $the_item['VALUE_DEC'], 10, 10, $onchange_num);
			elseif ($the_item['MISC_TYPE'] == 'T')
				print input_textbox('value_txt', $the_item['VALUE_TXT'], 63, 1000, $onchange_txt);
			elseif ($the_item['MISC_TYPE'] == 'D')
				print input_textbox('value_dt', $the_item['VALUE_DT'], 10, 20, $onchange_dt);
			elseif ($the_item['MISC_TYPE'] == 'E')
				print input_textbox('value_enc', $the_item['VALUE_ENC'], 63, 1000, $onchange_txt);
			print "
			</td>
		</tr>
		";
		if ($editing)
		{
			print "
			<tr>
				<td>Database ID</td>
				<td>" . input_textbox('db_id', $item_id, 4, 10, "readonly") . "</td>
			</tr>
			";
		}
		print "
	</table>
	";

	print_form_end();
} # edit_misc_info()

function edit_role()
{
	global $item; # screen_content()
	global $item_id; # screen_content()
	global $roles;
	global $yn_list;
	
	$the_item = $roles[0];
	$editing = ((0 < $item_id) ? true : false);
	$onchange_txt = ($editing ? "onchange=\"update_item(this);\"" : '');
	$onchange_num = ($editing ? "onchange=\"update_item(this,'n');\"" : '');
	
	print_form_start();
	print input_button("Back", "view_standing('$item')");
	
	print "	
	<p style=\"color:blue;\">Note: " .
		($editing ? "changes are saved automatically" : "ERROR") . ".</p>
	<h2>" . ($editing ? "Edit Activity" : "ERROR") . "</h2>
	<table>
		<tr>
			<td>Role Name</td>
			<td>" . input_textbox('ur_role', $the_item['UR_ROLE'], 63, 100, $onchange_txt) . "</td>
		</tr>
		<tr>
			<td>Role Code</td>
			<td>" . input_textbox('ur_code', $the_item['UR_CODE'], 63, 100, "readonly") . "</td>
		</tr>
		<tr>
			<td>Notes</td>
			<td>" . input_textarea('ur_comment', 5, 60, $the_item['UR_COMMENT'], $onchange_txt) . "</td>
		</tr>
		<tr>
			<td>Sort Order</td>
			<td>" . input_textbox('sort_order', $the_item['SORT_ORDER'], 4, 10, $onchange_num) . "</td>
		</tr>
		";
		if ($editing)
		{
			print "
			<tr>
				<td>Obsolete</td>
				<td>" . input_select('obsolete', $yn_list, $the_item['OBSOLETE'], $onchange_num, true) . "</td>
			</tr>
			<tr>
				<td>Database ID</td>
				<td>" . input_textbox('db_id', $item_id, 4, 10, "readonly") . "</td>
			</tr>
			";
		}
		print "
	</table>
	";

	print_form_end();
} # edit_role()

function edit_client_group()
{
	global $client_groups_with_clients;
	global $item; # screen_content()
	global $item_id; # screen_content()
	global $yn_list;
	
	$the_item = $client_groups_with_clients[$item_id];
	$editing = ((0 < $item_id) ? true : false);
	$onchange_txt = ($editing ? "onchange=\"update_item(this);\"" : '');
	$onchange_num = ($editing ? "onchange=\"update_item(this,'n');\"" : '');
	
	print_form_start();
	print input_button("Back", "view_standing('$item')");
	
	print "	
	<p style=\"color:blue;\">Note: " .
		($editing ? "changes are saved automatically" : "please click SAVE after entering values") . ".</p>
	<h2>" . ($editing ? "Edit Client Group" : "Create New Client Group") . "</h2>
	<table>
		<tr>
			<td>Group Name</td>
			<td>" . input_textbox('group_name', $the_item['GROUP_NAME'], 50, 100, $onchange_txt) . "</td>
		</tr>
		";
		if ($editing)
		{
			print "
			<tr>
				<td>Obsolete</td>
				<td>" . input_select('obsolete', $yn_list, $the_item['OBSOLETE'], $onchange_num, true) . "</td>
			</tr>
			<tr>
				<td>Database ID</td>
				<td>" . input_textbox('db_id', $item_id, 4, 10, "readonly") . "</td>
			</tr>
			";
		}
		else
		{
			print "
			<tr>
				<td>" .input_button("SAVE", "save_standing('$item')") . "</td>
			</tr>
			";
		}
		print "
	</table>
	";

	print_form_end();
} # edit_client_group()

function save_client_group()
{
	$group_name = post_val('group_name');

	audit_setup_gen('CLIENT_GROUP', 'CLIENT_GROUP_ID', 0, '', '');
	
	sql_encryption_preparation('CLIENT_GROUP');
	$group_name = sql_encrypt(quote_smart($group_name, true, true), true, 'CLIENT_GROUP');
	
	$fields = "GROUP_NAME";
	$values = "$group_name";
	
	$sql = "INSERT INTO CLIENT_GROUP ($fields) VALUES ($values)";
	$new_id = sql_execute($sql, true); # audited
	return $new_id;
	
} # save_client_group()

function edit_letter()
{
	global $at;
	global $feedback_39;
	global $feedback_42;
	global $item; # screen_content()
	global $item_id; # screen_content()
	global $letter_templates;
	global $yn_list;
	
	$the_item = $letter_templates[0];
	$editing = ((0 < $item_id) ? true : false);
	$onchange_txt = ($editing ? "onchange=\"update_item(this);\"" : '');
	$onchange_num = ($editing ? "onchange=\"update_item(this,'n');\"" : '');
	
	print_form_start();
	print input_button("Back", "view_standing('$item')");
	
	$rows = 10;
	$cols = 80;
	
	print "	
	<p style=\"color:blue;\">Note: " .
		($editing ? "changes are saved automatically" : "please click SAVE after entering values") . ".</p>
	<h2>" . ($editing ? "Edit Letter Template" : "Create New Letter Template") . "</h2>
	<table>
		";
		if (0 < $the_item['JT_T_JOB_TYPE_ID'])
		{
			print "
			<tr>
				<td $at>{$the_item['LETTER_NAME']}<br>Part 1</td>
				<td>" . input_textarea('jt_t_open', $rows, $cols, $the_item['JT_T_OPEN'], $onchange_txt) . "</td>
			</tr>
			<tr>
				<td $at>{$the_item['LETTER_NAME']}<br>Part 2</td>
				<td>" . input_textarea('jt_t_close', $rows, $cols, $the_item['JT_T_CLOSE'], $onchange_txt) . "</td>
			</tr>
			";
		}
		else
		{
			print "
			<tr>
				<td $at>" . input_textbox('letter_name', $the_item['LETTER_NAME'], 10, 100, $onchange_txt ) . "</td>
				<td>" . input_textarea('letter_template', 2 * $rows, $cols, $the_item['LETTER_TEMPLATE'], $onchange_txt) . "</td>
			</tr>
			";
			if ($feedback_39)
				print "
				<tr>
					<td>Available to<br>Non-managers</td>
					<td>" . input_select('lt_non_man', $yn_list, $the_item['LT_NON_MAN'], $onchange_num, true) . "</td>
				</tr>
				";
			if ($feedback_42)
				print "
				<tr>
					<td>Auto-Approve</td>
					<td>" . input_select('lt_auto_app', $yn_list, $the_item['LT_AUTO_APP'], $onchange_num, true) . "</td>
				</tr>
				";
		}
		if ($editing)
		{
			print "
			<tr>
				<td>Database ID</td>
				<td>" . input_textbox('db_id', $item_id, 4, 10, "readonly") . "</td>
			</tr>
			";
		}
		else
		{
			print "
			<tr>
				<td>" .input_button("SAVE", "save_standing('$item')") . "</td>
			</tr>
			";
		}
		print "
	</table>
	";

	print_form_end();
} # edit_letter()

?>
