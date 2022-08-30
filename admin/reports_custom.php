<?php

include_once("settings.php");
include_once("library.php");
global $navi_1_reports;
global $role_man;
global $sqlTrue;
global $USER; # set by admin_verify()

log_open("vilcol.log");

sql_connect();

admin_verify(); # writes to $USER

if ($USER['IS_ENABLED'])
{
	if (role_check('*', $role_man))
	{
		$navi_1_reports = true; # settings.php; used by navi_1_heading()
		$onload = "onload=\"set_scroll();\"";
		u_rep_custom_set($sqlTrue); # set DB flag to "on custom reports page"
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
	global $anchor;
	global $ci_ticked; # "clients in" that are ticked and should stay ticked
//	global $client_list_in; # string version of $clients_in
//	global $client_list_out; # string version of $clients_out
	global $edit_id; # the report being edited
	global $fi_ticked; # "fields in" that are ticked and should stay ticked
	global $from_edit_screen;
	global $rc_analysis;
	global $rc_collect;
	global $rc_global;
	global $rc_report; # the report selected in the drop-down list

	dprint(post_values());
	set_time_limit(1200); # 20 mins

	custom_delete_temp(); # delete old temp reports

	if (count($_POST) == 0)
	{
		$rc_global = u_rep_global_get(); # get whether we are on local or global reports
		$rc_analysis = u_rep_analysis_get();
		$rc_collect = u_rep_collect_get();
	}
	else
	{
		$rc_global = post_val('rc_global', true);
		u_rep_global_set($rc_global); # set whether we are on local or global reports
		$rc_analysis = post_val('rc_analysis', true);
		u_rep_analysis_set($rc_analysis); # set whether we are on analysis or job detail reports
		$rc_collect = post_val('rc_collect', true);
		u_rep_collect_set($rc_collect); # set whether we are on collect or trace reports
	}

	$rc_report = post_val('rc_report', true);
	if ($rc_report == 0)
		$rc_report = u_report_id_get(); # last viewed custom report id

	$rc_task = post_val('rc_task');
	$edit_id = post_val('edit_id', true);
	$ci_ticked = array();
	$fi_ticked = array();
//	$client_list_out = post_val('client_list_out');
//	#dprint("Client List Out=<pre>$client_list_out</pre>");#
//	$client_list_in = post_val('client_list_in');
//	#dprint("Client List In=<pre>$client_list_in</pre>");#

	if ($rc_task == 'sql_save')
	{
		sql_save_report();
		$rc_task = 'edit';
	}
	elseif ($rc_task == 'cli_add')
	{
		sql_add_clients();
		$rc_task = 'edit';
		$anchor = 'a_clients';
	}
	elseif ($rc_task == 'cli_rem')
	{
		sql_remove_clients();
		$rc_task = 'edit';
		$anchor = 'a_clients';
	}
	elseif ($rc_task == 'cli_up')
	{
		sql_move_client_up();
		$rc_task = 'edit';
		$anchor = 'a_clients';
	}
	elseif ($rc_task == 'cli_down')
	{
		sql_move_client_down();
		$rc_task = 'edit';
		$anchor = 'a_clients';
	}
	elseif ($rc_task == 'fld_add')
	{
		sql_add_fields();
		$rc_task = 'edit';
		$anchor = 'a_fields';
	}
	elseif ($rc_task == 'fld_rem')
	{
		sql_remove_fields();
		$rc_task = 'edit';
		$anchor = 'a_fields';
	}
	elseif ($rc_task == 'fld_up')
	{
		sql_move_field_up();
		$rc_task = 'edit';
		$anchor = 'a_fields';
	}
	elseif ($rc_task == 'fld_down')
	{
		sql_move_field_down();
		$rc_task = 'edit';
		$anchor = 'a_fields';
	}
	elseif ($rc_task == 'set_obsolete')
	{
		sql_set_obsolete();
		$rc_task = 'edit';
	}
	# Don't use "else" here

	$from_edit_screen = false;
	if ($rc_task == 'edit')
	{
		if ($edit_id == 0)
			$edit_id = sql_create_custom_report(); # create temp report
		$from_edit_screen = true;
		print_download_form();
		print_edit_screen();
	}
	elseif ($rc_task == 'print_run')
	{
//		# Put in a hack for Feedback #208 25/04/17
//		print "
//		<script>
//		document.getElementById('please_wait_div').style.display = 'none';
//		document.getElementById('main_screen_div').style.display = 'block';
//		</script>
//		";

		$anchor = 'a_run';
		print_download_form();
		print_run_outer();
	}
	else
		print_form();

	javascript();

//	if (global_debug())
//		dlog("Finished screen_content()");
} # screen_content()

function screen_content_2()
{
	# This is required by screen_layout()
} # screen_content_2()

function print_download_form()
{
	print "
	<form name=\"form_csv_download\" action=\"csv_dl.php\" method=\"post\" target=\"_blank\" rel=\"noopener\">
		<input type=\"hidden\" name=\"short_fname\" value=\"\" />
		<input type=\"hidden\" name=\"full_fname\" value=\"\" />
	</form><!--form_csv_download-->
	";
}

function print_form()
{
	global $col2;
#	global $col4;
	global $csv_dir;
	global $rc_analysis; # from screen_content()
	global $rc_collect; # from screen_content()
	global $rc_global; # from screen_content()
	global $rc_report; # the report selected in the drop-down list
	global $reports_title_width; # settings.php
	global $reps_subdir;
	global $role_man;
	global $tr_colour_1;
	global $USER;

	$manager_t = role_check('t', $role_man);
	$manager_c = role_check('c', $role_man);
	$manager_a = role_check('a', $role_man);
	$access_collect = ($manager_c || $manager_a);
	$access_trace = ($manager_t || $manager_a);
	if ( ! ($access_collect || $access_trace) )
	{
		print "<p>Sorry you do not have access to reports</p>";
		return;
	}
	if (!$access_collect)
	{
		if ($rc_collect)
			$rc_collect = false;
	}
	if (!$access_trace)
	{
		if (!$rc_collect)
			$rc_collect = true;
	}

	$global_txt = ($rc_global ? 'Global' : 'Local');
	$analysis_txt = ($rc_analysis ? 'Analysis' : 'Job Detail');
	$collect_txt = ($rc_collect ? 'Collection' : 'Trace');

	$reports_custom = sql_get_reports_custom($rc_global ? 0 : $USER['USER_ID'], $rc_analysis, $rc_collect, true);
//	$reports_custom_obs = sql_get_reports_custom($rc_global ? 0 : $USER['USER_ID'], $rc_analysis, $rc_collect, true, true);
//	$reports_custom = array();
//	foreach ($reports_custom_obs as $id => $name_obs)
//	{
//		list($name, $obs) = $name_obs;
//		$reports_custom[$id] = $name;
//	}
	if ((count($reports_custom) == 1) && (!array_key_exists($rc_report, $reports_custom)))
	{
		foreach ($reports_custom as $id => $name)
			$rc_report = $id;
		$name=$name; # keep code-checker quiet
	}
	#dprint(print_r($reports_custom,1));#

	#$gap = "&nbsp;&nbsp;&nbsp;&nbsp;";
	$radio_width = 110;
	$radio_box = "border:1px solid black;";
	$radio_gap_1 = "<br>"; # "</td><td width=\"$radio_width\" style=\"$radio_box\">";
	$radio_gap_2 = "<br>"; # "</td><td width=\"$radio_width\"";

	print "
	<table>
	<tr>
		<td width=\"$reports_title_width\" style=\"font-weight:bold; font-size:20px;\">Custom Reports</td>
		<td width=\"$reports_title_width\">" . input_button('Fixed Reports', 'fixed_reports()') . "</td>
	</tr>
	</table>

	<div id=\"div_form_main\" style=\"background-color:{$tr_colour_1};\">
	<hr>
	<form name=\"rep_custom_form\" action=\"" . server_php_self() . "\" method=\"post\">
		" . input_hidden('rc_task', '') . "
		" . input_hidden('edit_id', '') . # edit_id is NOT the same as rc_report
		"
	<table border=\"0\"><!---->
	<tr>
		<td width=\"$radio_width\" style=\"$radio_box\">
		" . input_radio('rc_global', array('Local' => 0, 'Global' => 1), $rc_global, $radio_gap_1,
							"onclick=\"document.rep_custom_form.submit();\"") . "
		</td>
		<td>
			&nbsp;
		</td>
		<td rowspan=\"3\">
			";
			if ($reports_custom)
			{
				print input_select('rc_report', $reports_custom, $rc_report) . "<br>" .
						input_button("Run selected report", "print_run()") . "<br>" .
						input_button("Edit selected report", "edit_report(1)");
			}
			else
				print "There are no $global_txt $analysis_txt $collect_txt reports yet";
			print "
		</td>
	</tr>
	<tr>
		<td width=\"$radio_width\" style=\"$radio_box\">
		" . input_radio('rc_analysis', array('Analysis' => 1, 'Job Detail' => 0), $rc_analysis, $radio_gap_2,
							"onclick=\"document.rep_custom_form.submit();\"") . "
		</td>
	</tr>
	<tr>
		<td width=\"$radio_width\" style=\"$radio_box\">
		";
			$rad_trace_txt = ($access_trace ? 'Trace' : '<span style="color:red">Trace</span>');
			$rad_collect_txt = ($access_collect ? 'Collection' : '<span style="color:red">Collection</span>');
			print input_radio('rc_collect',
				array($rad_trace_txt => 0, $rad_collect_txt => 1),
				$rc_collect, $radio_gap_2, "onclick=\"document.rep_custom_form.submit();\"",
				array($access_trace ? '' : 'disabled', $access_collect ? '' : 'disabled'));
			print "
		</td>
	</tr>
	<tr>
		<td $col2>" . input_button("Create new $global_txt $analysis_txt $collect_txt report", "create_report()") . "
		</td>
	</tr>
	</table>
	</form><!--rep_custom_form-->
	<hr>
	</div><!--div_form_main-->
	";

	# --- Feedback #208 - Show list of Spreadsheets for this user. Reports named "r<reportID>_u<userID>_<type>.xls" ------------------
	$dirname = "$csv_dir/$reps_subdir";
	$dir = opendir($dirname);
	if (!$dir)
	{
		print "<p>** Could not open '$dirname' directory **</p>";
		return;
	}
	$filelist = array();
	while (($file = readdir($dir)) != false)
	{
		if (($file[0] == 'r') && (strpos($file, ".xls") !== false))
		{
			$bits = explode('_', $file);
			#dprint("bits=" . print_r($bits,1));
			$temp = substr($bits[0], 1); # 123 from r123
			if (0 < intval($temp))
			{
				$temp = substr($bits[1], 1); # 123 from u123
				if ($USER['USER_ID'] == intval($temp))
				{
					$timestamp = filemtime("$dirname/$file");
					$filelist[] = array('TS' => $timestamp, 'FN' => $file, 'DT' => date_now(false, $timestamp));
				}
			}
		}
	}
	arsort($filelist);
	closedir($dir);
	print "
	<br>
	<h3>Spreadsheets</h3>
	";
	foreach ($filelist as $fileinfo)
		print "<a href=\"$dirname/{$fileinfo['FN']}\">{$fileinfo['FN']} ... {$fileinfo['DT']}</a><br>";
	# --------------------------------------------------------------------------------------------------------------------------------

} # print_form()

function anchor_jump()
{
	global $anchor;

	print "
	<script type=\"text/javascript\">
	//alert('#' + '$anchor');
	location.hash = '#' + '$anchor';
	</script>
	";
}

function javascript()
{
//	global $client_list_in; # string version of $clients_in
//	global $client_list_out; # string version of $clients_out
	global $csv_path;
	global $edit_id; # the report being edited
	global $from_edit_screen;
	global $safe_amp;
	global $uni_pound;

	print "
	<script type=\"text/javascript\">

	var ajax_running = false;

	function please_wait()
	{
		var df = document.getElementById('div_form');
		df.style.display = 'none';
		var dw = document.getElementById('div_wait');
		dw.style.display = 'block';
	}

	function obsolete_ticked()
	{
		please_wait();
		document.edit_form.rc_task.value = 'set_obsolete';
		please_wait_on_submit();
		document.edit_form.submit();
	}

	function main_screen()
	{
		document.edit_form.rc_task.value = '';
		please_wait_on_submit();
		document.edit_form.submit();
	}

	function export_xl()
	{
		run_report(1);
	}

	function run_report(to_xl)
	{
		";
		if ($from_edit_screen)
			print "
			document.edit_form.rc_task.value = 'edit';
			document.edit_form.run_task.value = 'run_edit';
			document.edit_form.run_xl.value = to_xl;
			refresh_edit();
			";
		else
			print "
			document.edit_form.rc_task.value = 'print_run';
			document.edit_form.run_task.value = 'run_run';
			document.edit_form.run_xl.value = to_xl;
			please_wait_on_submit();
			document.edit_form.submit();
			";
		print "
	}

	function update_report(control, field_type, date_check)
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

		ajax_running = true;
		xmlHttp2 = GetXmlHttpObject();
		if (xmlHttp2 == null)
			return;
		var url = 'reports_ajax.php?op=ur&i={$edit_id}&n=' + field_name + '&v=' + field_value;
		url = url + '&ran=' + Math.random();
		//alert(url);
		xmlHttp2.onreadystatechange = stateChanged_update_report;
		xmlHttp2.open('GET', url, true);
		xmlHttp2.send(null);
	}

	function stateChanged_update_report()
	{
		if (xmlHttp2.readyState == 4)
		{
			var resptxt = xprint_noscript(xmlHttp2.responseText);
			ajax_running = false;
			if (resptxt == 'do_refresh')
				refresh_edit();
			else if (resptxt != 'ok')
			{
				//if (!resptxt)
				//	resptxt = 'No response from ajax call!';
				if (resptxt)
					alert(resptxt);
			}
		}
	}

	function move_client(dir)
	{
		var inputs = document.getElementsByTagName('input');
		var count = 0;
		for (var i = 0; i < inputs.length; i++)
		{
			if (inputs[i].type == 'checkbox')
			{
				if (inputs[i].name.substring(0,3) == 'ci_')
				{
					if (inputs[i].checked)
						count++;
				}
			}
		}
		if (count == 0)
			alert('Please tick a client to move');
		else if (count == 1)
		{
			please_wait();
			document.edit_form.rc_task.value = ((dir == 1) ? 'cli_up' : 'cli_down');
			please_wait_on_submit();
			document.edit_form.submit();
		}
		else
			alert('Only one client can be moved at a time');
	}

	function move_field(dir)
	{
		var inputs = document.getElementsByTagName('input');
		var count = 0;
		for (var i = 0; i < inputs.length; i++)
		{
			if (inputs[i].type == 'checkbox')
			{
				if (inputs[i].name.substring(0,3) == 'fi_')
				{
					if (inputs[i].checked)
						count++;
				}
			}
		}
		if (count == 0)
			alert('Please tick a field to move');
		else if (count == 1)
		{
			please_wait();
			document.edit_form.rc_task.value = ((dir == 1) ? 'fld_up' : 'fld_down');
			please_wait_on_submit();
			document.edit_form.submit();
		}
		else
			alert('Only one field can be moved at a time');
	}

	function refresh_edit()
	{
		please_wait();
		document.edit_form.rc_task.value = 'edit';
		please_wait_on_submit();
		document.edit_form.submit();
	}

	function add_clients()
	{
		please_wait();
		document.edit_form.rc_task.value = 'cli_add';
		" .
//		document.edit_form.client_list_out.value = '$client_list_out';
//		document.edit_form.client_list_in.value = '$client_list_in';
		"
		please_wait_on_submit();
		document.edit_form.submit();
	}

	function remove_clients()
	{
		please_wait();
		var inputs = document.getElementsByTagName('input');
		var count = 0;
		for (var i = 0; i < inputs.length; i++)
		{
			if (inputs[i].type == 'checkbox')
			{
				if (inputs[i].name.substring(0,3) == 'ci_')
				{
					if (inputs[i].checked)
						count++;
				}
			}
		}
		if (count == 0)
			alert('Please tick a client to remove');
		else
		{
			document.edit_form.rc_task.value = 'cli_rem';
			please_wait_on_submit();
			document.edit_form.submit();
		}
	}

	function add_fields()
	{
		please_wait();
		document.edit_form.rc_task.value = 'fld_add';
		please_wait_on_submit();
		document.edit_form.submit();
	}

	function remove_fields()
	{
		please_wait();
		var inputs = document.getElementsByTagName('input');
		var count = 0;
		for (var i = 0; i < inputs.length; i++)
		{
			if (inputs[i].type == 'checkbox')
			{
				if (inputs[i].name.substring(0,3) == 'fi_')
				{
					if (inputs[i].checked)
						count++;
				}
			}
		}
		if (count == 0)
			alert('Please tick a field to remove');
		else
		{
			document.edit_form.rc_task.value = 'fld_rem';
			please_wait_on_submit();
			document.edit_form.submit();
		}
	}

	function save_report_1()
	{
		if (ajax_running)
		{
			setTimeout('save_report_1', 100);
			//alert('pausing');//
			return false;
		}

		var name = trim(document.edit_form.rep_name.value);
		if (name == '')
		{
			alert('Please enter a report name');
			return false;
		}

		//window.setTimeout('save_report_2', 1000);
		save_report_2();
	}

	function save_report_2()
	{
		xmlHttp3 = GetXmlHttpObject();
		if (xmlHttp3 == null)
			return;
		var url = 'reports_ajax.php?op=fn&n=' + name + '&i=' + document.edit_form.edit_id.value;
		url = url + '&ran=' + Math.random();
		//alert(url);//
		xmlHttp3.onreadystatechange = stateChanged_save_report_2;
		xmlHttp3.open('GET', url, true);
		xmlHttp3.send(null);
	}

	function stateChanged_save_report_2()
	{
		if (xmlHttp3.readyState == 4)
		{
			//alert(xmlHttp3.responseText);//
			var resptxt = xprint_noscript(xmlHttp3.responseText);
			if (resptxt)
			{
				var bits = resptxt.split('|');
				if (bits[0] != -1)
				{
					if (bits[1] == 0)
					{
						// Report name is OK
						document.edit_form.rc_task.value = 'sql_save';
						please_wait_on_submit();
						document.edit_form.submit();
					}
					else
						alert('That report name already exists - please try another');
				}
				else
					alert(bits[1]);
			}
		}
	}

	function create_report()
	{
		document.rep_custom_form.rc_task.value = 'edit';
		document.rep_custom_form.edit_id.value = '';
		document.rep_custom_form.submit();
	}

	function edit_report(from_main)
	{
		if (from_main == 1)
		{
			if (0 < document.rep_custom_form.rc_report.value)
			{
				document.rep_custom_form.rc_task.value = 'edit';
				document.rep_custom_form.edit_id.value = document.rep_custom_form.rc_report.value; // NOT the same as each other
				please_wait_on_submit();
				document.rep_custom_form.submit();
			}
			else
				alert('Please select a report to edit');
		}
		else
		{
			if ((0 < document.edit_form.rc_report.value) && (0 < document.edit_form.edit_id.value))
			{
				if (document.edit_form.rc_report.value == document.edit_form.edit_id.value)
				{
					document.edit_form.rc_task.value = 'edit';
					please_wait_on_submit();
					document.edit_form.submit();
				}
				else
					alert('Error: rc_report and edit_id not equal');
			}
			else
				alert('Error: rc_report and edit_id not both > 0');
		}
	}

	function print_run()
	{
		if (0 < document.rep_custom_form.rc_report.value)
		{
			document.rep_custom_form.rc_task.value = 'print_run';
			document.rep_custom_form.edit_id.value = document.rep_custom_form.rc_report.value; // NOT the same as each other
			please_wait_on_submit();
			document.rep_custom_form.submit();
		}
		else
			alert('Please select a report to run');
	}

	function fixed_reports()
	{
		document.location.href = 'reports.php';
	}

	function csv_download(fname)
	{
		//alert(fname);
		document.form_csv_download.short_fname.value = fname;
		document.form_csv_download.full_fname.value = '$csv_path' + fname;
		document.form_csv_download.submit();
	}

	</script>
	";
} # javascript()

function print_edit_screen()
{
	# This is the equivlalent of print_run_outer(), but we are editing the report as well as running it.

	global $ar;
	global $at;
	global $ci_ticked; # "clients in" that are ticked and should stay ticked
//	global $client_list_in; # string version of $clients_in
//	global $client_list_out; # string version of $clients_out
	global $col2;
	global $col3;
	global $edit_id; # the report being edited
	global $fi_ticked; # "fields in" that are ticked and should stay ticked
	global $grey;
	global $rc_analysis;
	global $rc_collect;
	global $rc_global;
	global $rc_report; # the report selected in the drop-down list
	global $USER;

	$report = sql_get_report($edit_id);
	#dprint("report=" . print_r($report,1));#
	if ($report)
		u_report_id_set($edit_id);

	$temp = ($report['REP_TEMP'] ? true : false);
	if ($temp && substr($report['REP_NAME'], 0, 3) == '_*_')
		$rep_name = '';
	else
		$rep_name = $report['REP_NAME'];
	$global_txt = ($report['REP_USER_ID'] ? 'Local' : 'Global');
	$analysis_txt = ($report['REP_ANALYSIS'] ? 'Analysis' : 'Job Detail');
	$collect_txt = ($report['REP_COLLECT'] ? 'Collection' : 'Trace');

	if ($report['OBSOLETE'])
	{
		$obsolete = true;
		$disabled = 'disabled';
		$obs_col = "style=\"color:red;\"";
	}
	else
	{
		$obsolete = false;
		$disabled = '';
		$obs_col = "";
	}

	if (!$temp)
	{
		$all_clients = post_val('all_clients', true);
		$sort_client_name = post_val('sort_client_name', true);
//		if ($client_list_out || $client_list_in)
//			list($clients_in, $clients_out) = get_clients_from_post();
//		else
//		{
			list($clients_in, $clients_out) = sql_get_clients_for_report($edit_id, false, $all_clients, $sort_client_name ? 'name' : 'code');
//			$client_list_out = client_list_set($clients_out);
//			$client_list_in = client_list_set($clients_in);
//		}
		$disable_cli = ((count($clients_in) == 0) ? 'disabled' : '');

		list($fields_in, $fields_out) = sql_get_fields_for_report($edit_id, false, $report['REP_ANALYSIS'], $report['REP_COLLECT']);
		#dprint("FieldsIn=" . print_r($fields_in,1));#
		#dprint("FieldsOut=" . print_r($fields_out,1));#
		$disable_fld = ((count($fields_in) == 0) ? 'disabled' : '');

		$fontsize = "13px";
	}
	$gap = "</td><td>";
	$gap2 = "&nbsp;&nbsp;&nbsp;&nbsp;";
	$rad_left = 110;

	print "
	<h3>" . ($temp ? "Create" : "Edit") . " $global_txt $analysis_txt $collect_txt Report</h3>
		" . input_button('Back', 'main_screen()') . "

	<form name=\"edit_form\" action=\"" . server_php_self() . "\" method=\"post\">
		" . input_hidden('rc_global', $rc_global) . "
		" . input_hidden('rc_analysis', $rc_analysis) . "
		" . input_hidden('rc_collect', $rc_collect) . "
		" . input_hidden('rc_report', $rc_report) . "
		" . input_hidden('edit_id', $edit_id) . "
		" . input_hidden('rc_task', '') . "
		" . input_hidden('client_list_out', '') . "
		" . input_hidden('client_list_in', '') . "

	<div id=\"div_wait\">
		<p style=\"color:blue\">Please wait...</p>
	</div><!--div_wait-->
	<div id=\"div_form\" style=\"display:none;\">
	<table name=\"t_top\" border=\"0\"><!---->
		<tr>
			<td>Report Name</td><td width=\"10\"></td>
			<td $col2>" . input_textbox('rep_name', $rep_name, 30, 100, "onchange=\"update_report(this)\" $disabled");
			if (user_debug())
				print "<span $grey> ID {$report['REPORT_ID']}</span>";
			print "</td>
			<td width=\"70\"></td>
		</tr>
		<tr>
			<td>Local/Global</td><td></td>
			<td width=\"$rad_left\">
				" . input_radio('rep_user_id', array('Local' => $USER['USER_ID'], 'Global' => 0), # 0 will become NULL in DB if selected by user
									$report['REP_USER_ID'], $gap, "onchange=\"update_report(this,'n')\" $disabled") . "
			</td>
		</tr>
		<tr>
			<td>Analysis/Job</td><td></td>
			<td width=\"$rad_left\">
				" . input_radio('rep_analysis', array('Analysis' => 1, 'Job Detail' => 0),
									$report['REP_ANALYSIS'], $gap, "onchange=\"update_report(this,'n');\" $disabled") . "
			</td>
		</tr>
		<tr>
			<td>Trace/Collect</td><td></td>
			<td width=\"$rad_left\">
				" . input_radio('rep_collect', array('Trace' => 0, 'Collect' => 1),
									$report['REP_COLLECT'], $gap, "onchange=\"update_report(this,'n');\" $disabled") . "
			</td>
		</tr>
		";
		if ($report['REP_ANALYSIS'])
		{
			print "
			<tr>
				<td>Report by</td><td></td>
				<td width=\"$rad_left\">
				" . input_radio('month_rcvd', array('Date received' => 0, 'Month received' => 1),
										$report['MONTH_RCVD'], $gap, "onchange=\"update_report(this,'n')\" $disabled") . "
				</td>
			</tr>
			<tr>
				<td>Client Division</td><td></td>
				<td $col3>
				" . input_radio('client_per_sheet', array('One client per sheet' => 1, 'All clients on same sheet' => 0),
										$report['CLIENT_PER_SHEET'], $gap2, "onchange=\"update_report(this,'n')\" $disabled") . "
				</td>
			</tr>
			<tr>
				<td>Subtotals by year</td><td></td>
				<td>" . input_tickbox('', 'subt_by_year', 1, $report['SUBT_BY_YEAR'], '', "onchange=\"update_report(this,'t')\" $disabled") . "</td>
			</tr>
			";
		}
		else
		{
			print "
			<tr>
				<td>Multi-sheet</td><td></td>
				<td width=\"$rad_left\">
				" . input_radio('client_per_sheet', array('Multi-sheet' => 1, 'Single sheet' => 0),
										$report['CLIENT_PER_SHEET'], $gap, "onchange=\"update_report(this,'n')\" $disabled") . "
				</td>
			</tr>
			";
			if ($report['REP_COLLECT'])
			{
				print "
				<tr>
					<td>Job Status</td><td></td>
					<td $col3>" . input_radio('rep_job_status', array('All' => 0, 'Open Jobs' => 1, 'Closed Jobs' => -1),
											$report['REP_JOB_STATUS'], $gap2, "onchange=\"update_report(this,'n')\" $disabled") . "
					</td>
				</tr>
				<tr>
					<td>Job Payments</td><td></td>
					<td $col3>" . input_radio('rep_payments',
						array('All' => 0, 'Jobs with Payments (in payment period)' => 1, 'Jobs without Payments (in payment period)' => -1),
						$report['REP_PAYMENTS'], $gap2, "onchange=\"update_report(this,'n')\" $disabled") . "
					</td>
				</tr>
				";
			}
		}
		if ($temp)
		{
			print "
			<tr>
				<td>" . input_button('Save Report', "save_report_1()") . " (please note: you might need to click this twice)</td>
			</tr>
				";
		}
		else
		{
			print "
			<tr>
				<td $obs_col>Report Obsolete</td><td></td>
				<td $obs_col $col2>" . input_tickbox('', 'obsolete', 1, $obsolete, "obsolete_ticked()") .
					($obsolete ? '&nbsp;&nbsp;** This report is obsolete **' : '') . "</td>
				<td></td>
			</tr>
			";
		}
		print "
	</table><!--t_top-->
	";
	if (!$temp)
	{
		$div_style = "width:400px; height:150px; border:1px solid; overflow:scroll; font-size:{$fontsize}; resize:both;";
		#$sep = "&ndash;";
		$sep = "&nbsp;";
		print "
		<p style=\"font-weight:bold;\">Clients to report on:</p>
		<table name=\"t_clients\">
			<tr>
				<td>
				" . input_tickbox("Show all clients (otherwise just Collect clients)", 'all_clients', 1, $all_clients, "refresh_edit()") . "
				</td>
				<td $ar>
					Client Code(s):
				</tr>
			</tr>
			<tr>
				<td>
				" . input_tickbox("Sort on client name (otherwise client code)", 'sort_client_name', 1, $sort_client_name, "refresh_edit()") . "
				</td>
				<td $ar>
					" . input_textbox('add_code', '', 10, 100, $disabled) . "
				</tr>
			</tr>
			<tr>
				<td $col2>
					<div style=\"$div_style\">
					<table id=\"clients_out\" style=\"font-size:{$fontsize}\">
					";
					foreach ($clients_out as $cli)
					{
						if (($cli['C_CODE'] != '') && ($cli['C_CODE'] != 0))
							print "<tr>
									<td>" . input_tickbox('', "co_{$cli['CLIENT2_ID']}", 1, false, '', $disabled) . "</td>
									<td $ar>{$cli['C_CODE']}</td><td>$sep</td><td>{$cli['C_CO_NAME']}</td>
								   </tr>";
					}
					print "
					</table>
					</div>
				</td>
				<td $at $ar>
					" . input_button("Add >>", "add_clients()", "style=\"width:94px;\" $disabled") . "<br>
					" . input_button("<< Remove", "remove_clients()", "style=\"width:94px;\" $disable_cli $disabled") . "<br>
					<br>
					" . input_button("Move\rUp", "move_client(1)", "style=\"width:50px;\" $disable_cli $disabled") . "<br>
					" . input_button("Move\rDown", "move_client(-1)", "style=\"width:50px;\" $disable_cli $disabled") . "
				</td>
				<td>
					<div style=\"$div_style\">
					<table id=\"clients_in\" style=\"font-size:{$fontsize}\">
					";
					foreach ($clients_in as $cli)
					{
						$code = $cli['C_CODE'];
						if (!$code)
							$code = "[ID={$cli['CLIENT2_ID']}]";
						$ticked = (in_array($cli['CLIENT2_ID'], $ci_ticked) ? true : false);
						print "<tr><td>" . input_tickbox('', "ci_{$cli['CLIENT2_ID']}", 1, $ticked, '', $disabled) . "</td>
									<td $ar>$code</td><td>$sep</td><td>{$cli['C_CO_NAME']}</td></tr>";
					}
					print "
					</table>
					</div>
				</td>
			</tr>
		</table><!--t_clients-->
		<a name=\"a_clients\"></a>
		";

		$div_style = "width:400px; height:150px; border:1px solid; overflow:scroll; font-size:{$fontsize}; resize:both;";
		print "
		<h4>Fields to report on:</h4>
		";

		if ($fields_in || $fields_out)
		{
			print "
			<table name=\"t_fields\">
				<tr>
					<td>
						<div style=\"$div_style\">
						";
						foreach ($fields_out as $fld)
							print input_tickbox($fld['RF_DESCR'], "fo_{$fld['REPORT_FIELD_ID']}", 1, false, '', $disabled) . "<br>";
						print "
						</div>
					</td>
					<td $at $ar>
						" . input_button("Add >>", "add_fields()", "style=\"width:94px;\" $disabled") . "<br>
						" . input_button("<< Remove", "remove_fields()", "style=\"width:94px;\" $disable_fld $disabled") . "<br>
						<br>
						" . input_button("Move\rUp", "move_field(1)", "style=\"width:50px;\" $disable_fld $disabled") . "<br>
						" . input_button("Move\rDown", "move_field(-1)", "style=\"width:50px;\" $disable_fld $disabled") . "
					</td>
					<td>
						<div style=\"$div_style\">
						";
						foreach ($fields_in as $fld)
						{
							$ticked = (in_array($fld['REPORT_FIELD_ID'], $fi_ticked) ? true : false);
							print input_tickbox($fld['RF_DESCR'], "fi_{$fld['REPORT_FIELD_ID']}", 1, $ticked, '', $disabled) . "<br>";
						}
						print "
						</div>
					</td>
				</tr>
			</table><!--t_fields-->
			";
		}
		else
			dprint("There are no fields set up to choose from", true);

		print "
		<a name=\"a_fields\"></a>
		";

		if ($clients_in && $fields_in && (!$disabled))
		{
			print "<br><hr>";
			print_run($report); # this must be called before printing </form>
		}
	}

	print "
	</div><!--div_form-->
	</form><!--edit_form-->
	";

//	if (global_debug())
//		dlog("Showing DIV/edit");
	print "
	<script type=\"text/javascript\">
	document.getElementById('div_wait').style.display = 'none';
	document.getElementById('div_form').style.display = 'block';
	</script>
	";

	if (!$temp)
		anchor_jump();

//	if (global_debug())
//		dlog("Finished print_edit_screen()");
} # print_edit_screen()

function sql_save_report()
{
	global $edit_id; # the report being edited
	global $sqlFalse;

	if (0 < $edit_id)
	{
		$updates = array();
		$rep_name = post_val('rep_name');
		if ($rep_name)
		{
			$sql = "SELECT COUNT(*) FROM REPORT
					WHERE REP_NAME=" . quote_smart($rep_name, true) . " AND REPORT_ID<>$edit_id";
			$count = 0;
			sql_execute($sql);
			while (($newArray = sql_fetch()) != false)
				$count = $newArray[0];
			if ($count == 0)
			{
				$rep_user_id = post_val('rep_user_id', true);
				if ( ! (0 < $rep_user_id) )
					$rep_user_id = 'NULL';

				$updates[] = array("REP_NAME", $rep_name, true);
				$updates[] = array("REP_USER_ID", $rep_user_id, false);
				$updates[] = array("CLIENT_PER_SHEET", post_val('client_per_sheet', true), false);
				$updates[] = array("SUBT_BY_YEAR", post_val('subt_by_year', true), false);
				$updates[] = array("MONTH_RCVD", post_val('month_rcvd', true), false);
				if (array_key_exists('rep_job_status', $_POST))
					$updates[] = array("REP_JOB_STATUS", post_val('rep_job_status', true), false); # 0, 1 or -1
				if (array_key_exists('rep_payments', $_POST))
					$updates[] = array("REP_PAYMENTS", post_val('rep_payments', true), false); # 0, 1 or -1
				$updates[] = array("REP_TEMP", $sqlFalse, false);
				foreach ($updates as $upd)
				{
					$name = $upd[0];
					$value = $upd[1];
					$value_sql = ($upd[2] ? quote_smart($value, true) : $value);
					audit_setup_gen('REPORT', 'REPORT_ID', $edit_id, $name, $value);
					$sql = "UPDATE REPORT SET $name=$value_sql WHERE REPORT_ID=$edit_id";
					#dprint($sql);#
					sql_execute($sql, true); # audited
				}
			}
			else
				dprint("Cannot save report - Report Name \"$rep_name\" already exists", true);
		}
		else
			dprint("Cannot save report - there is no Report Name", true);
	}
	else
		dprint("Cannot save report - there is no Report ID!", true);
} # sql_save_report()

function sql_add_clients()
{
	global $edit_id; # the report being edited
//	global $client_ids_added;

//	$client_ids_added = array();
	if (0 < $edit_id)
	{
		$sort_order = 0;
		$edited = false;

		# ---- Look for any client codes in 'add_code' and if found add client_id(s) to $_POST ----
		$add_codes = explode(',', str_replace(' ', ',', post_val('add_code')));
		foreach ($add_codes as $code)
		{
			$code = intval($code);
			if (0 < $code)
			{
				$client2_id = client_id_from_code(intval($code));
				if (0 < $client2_id)
				{
					$key = "co_{$client2_id}";
					$value = 1;
					if ((!array_key_exists($key, $_POST)) || (post_val($key) != $value))
						$_POST[$key] = $value;
				}
			}
		}
		# ---- ----

		foreach ($_POST as $key => $value)
		{
			if ((substr($key, 0, 3) == "co_") && ($value == 1))
			{
				if ($sort_order == 0)
				{
					$sql = "SELECT MAX(SORT_ORDER) FROM REPORT_CLIENT_LINK WHERE REPORT_ID=$edit_id";
					sql_execute($sql);
					while (($newArray = sql_fetch()) != false)
						$sort_order = $newArray[0];
				}
				$client2_id = intval(substr($key, 3));
				$sort_order++;
				$sql = "INSERT INTO REPORT_CLIENT_LINK (REPORT_ID, CLIENT2_ID, SORT_ORDER)
						VALUES ($edit_id, $client2_id, $sort_order)";
				#dprint($sql);#
				audit_setup_gen('REPORT_CLIENT_LINK', 'REPORT_CLIENT_LINK_ID', 0, '', '');
				sql_execute($sql, true); # audited

				$edited = true;
//				$client_ids_added[] = $client2_id;
			}
		}
		if ($edited)
		{
			$now = date_now_sql();
			$sql = "UPDATE REPORT SET EDITED_DT='$now' WHERE REPORT_ID=$edit_id";
			audit_setup_gen('REPORT', 'REPORT_ID', $edit_id, 'EDITED_DT', $now);
			sql_execute($sql, true); # audited
		}
	}
	else
		dprint("Cannot add clients to report - there is no Report ID!", true);
} # sql_add_clients()

function sql_remove_clients()
{
	global $edit_id; # the report being edited

	if (0 < $edit_id)
	{
		$removed = false;
		foreach ($_POST as $key => $value)
		{
			if ((substr($key, 0, 3) == "ci_") && ($value == 1))
			{
				$client2_id = intval(substr($key, 3));
				$sql = "SELECT REPORT_CLIENT_LINK_ID FROM REPORT_CLIENT_LINK
						WHERE (REPORT_ID=$edit_id) AND (CLIENT2_ID=$client2_id)";
				sql_execute($sql);
				$link_id = 0;
				while (($newArray = sql_fetch()) != false)
					$link_id = $newArray[0];
				$sql = "DELETE FROM REPORT_CLIENT_LINK WHERE REPORT_CLIENT_LINK_ID=$link_id";
				#dprint($sql);#
				audit_setup_gen('REPORT_CLIENT_LINK', 'REPORT_CLIENT_LINK_ID', $link_id, '', '');
				sql_execute($sql, true); # audited
				$removed = true;
			}
		}
		if ($removed)
		{
			# Adjust SORT_ORDER
			$sql = "SELECT REPORT_CLIENT_LINK_ID FROM REPORT_CLIENT_LINK
						WHERE (REPORT_ID=$edit_id) ORDER BY SORT_ORDER";
			sql_execute($sql);
			$ids = array();
			while (($newArray = sql_fetch()) != false)
				$ids[] = $newArray[0];
			$sort_order = 1;
			foreach ($ids as $id)
			{
				$sql = "UPDATE REPORT_CLIENT_LINK SET SORT_ORDER=$sort_order
						WHERE REPORT_CLIENT_LINK_ID=$id";
				sql_execute($sql); # no need to audit
				$sort_order++;
			}

			$now = date_now_sql();
			$sql = "UPDATE REPORT SET EDITED_DT='$now' WHERE REPORT_ID=$edit_id";
			audit_setup_gen('REPORT', 'REPORT_ID', $edit_id, 'EDITED_DT', $now);
			sql_execute($sql, true); # audited
		}
	}
	else
		dprint("Cannot remove clients from report - there is no Report ID!", true);
} # sql_remove_clients()

function sql_move_client_up()
{
	global $ci_ticked; # "clients in" that are ticked and should stay ticked
	global $edit_id; # the report being edited

	if (0 < $edit_id)
	{
		foreach ($_POST as $key => $value)
		{
			# Only one $key should be a "ci_..."
			if ((substr($key, 0, 3) == "ci_") && ($value == 1))
			{
				$client2_id = intval(substr($key, 3));
				$ci_ticked[] = $client2_id;
				$sql = "SELECT REPORT_CLIENT_LINK_ID, SORT_ORDER FROM REPORT_CLIENT_LINK
						WHERE (REPORT_ID=$edit_id) AND (CLIENT2_ID=$client2_id)";
				sql_execute($sql);
				$link_id_1 = 0;
				$sort_order_1 = 0;
				while (($newArray = sql_fetch()) != false)
				{
					$link_id_1 = $newArray[0];
					$sort_order_1 = $newArray[1];
				}
				if ($sort_order_1 <= 1)
					javascript_alert("Cannot move that client up any more", true);
				else
				{
					$link_id_2 = 0;
					$sort_order_2 = $sort_order_1 - 1;
					$sql = "SELECT REPORT_CLIENT_LINK_ID FROM REPORT_CLIENT_LINK
							WHERE (REPORT_ID=$edit_id) AND (SORT_ORDER=$sort_order_2)";
					sql_execute($sql);
					while (($newArray = sql_fetch()) != false)
						$link_id_2 = $newArray[0];

					$sql = "UPDATE REPORT_CLIENT_LINK SET SORT_ORDER=$sort_order_2
							WHERE REPORT_CLIENT_LINK_ID=$link_id_1";
					audit_setup_gen('REPORT_CLIENT_LINK', 'REPORT_CLIENT_LINK_ID', $link_id_1, 'SORT_ORDER', $sort_order_2);
					sql_execute($sql, true); # audited

					$sql = "UPDATE REPORT_CLIENT_LINK SET SORT_ORDER=$sort_order_1
							WHERE REPORT_CLIENT_LINK_ID=$link_id_2";
					audit_setup_gen('REPORT_CLIENT_LINK', 'REPORT_CLIENT_LINK_ID', $link_id_2, 'SORT_ORDER', $sort_order_1);
					sql_execute($sql, true); # audited

					$now = date_now_sql();
					$sql = "UPDATE REPORT SET EDITED_DT='$now' WHERE REPORT_ID=$edit_id";
					audit_setup_gen('REPORT', 'REPORT_ID', $edit_id, 'EDITED_DT', $now);
					sql_execute($sql, true); # audited
				}
			}
		}
	}
	else
		dprint("Cannot move client - there is no Report ID!", true);
} # sql_move_client_up()

function sql_move_client_down()
{
	global $ci_ticked; # "clients in" that are ticked and should stay ticked
	global $edit_id; # the report being edited

	if (0 < $edit_id)
	{
		foreach ($_POST as $key => $value)
		{
			# Only one $key should be a "ci_..."
			if ((substr($key, 0, 3) == "ci_") && ($value == 1))
			{
				$client2_id = intval(substr($key, 3));
				$ci_ticked[] = $client2_id;
				$sql = "SELECT REPORT_CLIENT_LINK_ID, SORT_ORDER FROM REPORT_CLIENT_LINK
						WHERE (REPORT_ID=$edit_id) AND (CLIENT2_ID=$client2_id)";
				sql_execute($sql);
				$link_id_1 = 0;
				$sort_order_1 = 0;
				while (($newArray = sql_fetch()) != false)
				{
					$link_id_1 = $newArray[0];
					$sort_order_1 = $newArray[1];
				}

				$link_id_2 = 0;
				$sort_order_2 = $sort_order_1 + 1;
				$sql = "SELECT REPORT_CLIENT_LINK_ID FROM REPORT_CLIENT_LINK
						WHERE (REPORT_ID=$edit_id) AND (SORT_ORDER=$sort_order_2)";
				sql_execute($sql);
				while (($newArray = sql_fetch()) != false)
					$link_id_2 = $newArray[0];
				if ($link_id_2 == 0)
					javascript_alert("Cannot move that client down any more", true);
				else
				{
					$sql = "UPDATE REPORT_CLIENT_LINK SET SORT_ORDER=$sort_order_2
							WHERE REPORT_CLIENT_LINK_ID=$link_id_1";
					audit_setup_gen('REPORT_CLIENT_LINK', 'REPORT_CLIENT_LINK_ID', $link_id_1, 'SORT_ORDER', $sort_order_2);
					sql_execute($sql, true); # audited

					$sql = "UPDATE REPORT_CLIENT_LINK SET SORT_ORDER=$sort_order_1
							WHERE REPORT_CLIENT_LINK_ID=$link_id_2";
					audit_setup_gen('REPORT_CLIENT_LINK', 'REPORT_CLIENT_LINK_ID', $link_id_2, 'SORT_ORDER', $sort_order_1);
					sql_execute($sql, true); # audited

					$now = date_now_sql();
					$sql = "UPDATE REPORT SET EDITED_DT='$now' WHERE REPORT_ID=$edit_id";
					audit_setup_gen('REPORT', 'REPORT_ID', $edit_id, 'EDITED_DT', $now);
					sql_execute($sql, true); # audited
				}
			}
		}
	}
	else
		dprint("Cannot move client - there is no Report ID!", true);
} # sql_move_client_down()

function sql_add_fields()
{
	global $edit_id; # the report being edited

	if (0 < $edit_id)
	{
		$sort_order = 0;
		$edited = false;
		foreach ($_POST as $key => $value)
		{
			if ((substr($key, 0, 3) == "fo_") && ($value == 1))
			{
				if ($sort_order == 0)
				{
					$sql = "SELECT MAX(SORT_ORDER) FROM REPORT_FIELD_LINK WHERE REPORT_ID=$edit_id";
					sql_execute($sql);
					while (($newArray = sql_fetch()) != false)
						$sort_order = $newArray[0];
				}
				$field_id = intval(substr($key, 3));
				$sort_order++;
				$sql = "INSERT INTO REPORT_FIELD_LINK (REPORT_ID, REPORT_FIELD_ID, SORT_ORDER)
						VALUES ($edit_id, $field_id, $sort_order)";
				#dprint($sql);#
				audit_setup_gen('REPORT_FIELD_LINK', 'REPORT_FIELD_LINK_ID', 0, '', '');
				sql_execute($sql, true); # audited

				$edited = true;
			}
		}
		if ($edited)
		{
			$now = date_now_sql();
			$sql = "UPDATE REPORT SET EDITED_DT='$now' WHERE REPORT_ID=$edit_id";
			audit_setup_gen('REPORT', 'REPORT_ID', $edit_id, 'EDITED_DT', $now);
			sql_execute($sql, true); # audited
		}
	}
	else
		dprint("Cannot add fields to report - there is no Report ID!", true);
} # sql_add_fields()

function sql_remove_fields()
{
	global $edit_id; # the report being edited

	if (0 < $edit_id)
	{
		$removed = false;
		foreach ($_POST as $key => $value)
		{
			if ((substr($key, 0, 3) == "fi_") && ($value == 1))
			{
				$field_id = intval(substr($key, 3));
				$sql = "SELECT REPORT_FIELD_LINK_ID FROM REPORT_FIELD_LINK
						WHERE (REPORT_ID=$edit_id) AND (REPORT_FIELD_ID=$field_id)";
				sql_execute($sql);
				$link_id = 0;
				while (($newArray = sql_fetch()) != false)
					$link_id = $newArray[0];
				$sql = "DELETE FROM REPORT_FIELD_LINK WHERE REPORT_FIELD_LINK_ID=$link_id";
				#dprint($sql);#
				audit_setup_gen('REPORT_FIELD_LINK', 'REPORT_FIELD_LINK_ID', $link_id, '', '');
				sql_execute($sql, true); # audited
				$removed = true;
			}
		}
		if ($removed)
		{
			# Adjust SORT_ORDER
			$sql = "SELECT REPORT_FIELD_LINK_ID FROM REPORT_FIELD_LINK
						WHERE (REPORT_ID=$edit_id) ORDER BY SORT_ORDER";
			sql_execute($sql);
			$ids = array();
			while (($newArray = sql_fetch()) != false)
				$ids[] = $newArray[0];
			$sort_order = 1;
			foreach ($ids as $id)
			{
				$sql = "UPDATE REPORT_FIELD_LINK SET SORT_ORDER=$sort_order
						WHERE REPORT_FIELD_LINK_ID=$id";
				sql_execute($sql); # no need to audit
				$sort_order++;
			}

			$now = date_now_sql();
			$sql = "UPDATE REPORT SET EDITED_DT='$now' WHERE REPORT_ID=$edit_id";
			audit_setup_gen('REPORT', 'REPORT_ID', $edit_id, 'EDITED_DT', $now);
			sql_execute($sql, true); # audited
		}
	}
	else
		dprint("Cannot remove fields from report - there is no Report ID!", true);
} # sql_remove_fields()

function sql_move_field_up()
{
	global $fi_ticked; # "fields in" that are ticked and should stay ticked
	global $edit_id; # the report being edited

	if (0 < $edit_id)
	{
		foreach ($_POST as $key => $value)
		{
			# Only one $key should be a "fi_..."
			if ((substr($key, 0, 3) == "fi_") && ($value == 1))
			{
				$field_id = intval(substr($key, 3));
				$fi_ticked[] = $field_id;
				$sql = "SELECT REPORT_FIELD_LINK_ID, SORT_ORDER FROM REPORT_FIELD_LINK
						WHERE (REPORT_ID=$edit_id) AND (REPORT_FIELD_ID=$field_id)";
				sql_execute($sql);
				$link_id_1 = 0;
				$sort_order_1 = 0;
				while (($newArray = sql_fetch()) != false)
				{
					$link_id_1 = $newArray[0];
					$sort_order_1 = $newArray[1];
				}
				if ($sort_order_1 <= 1)
					javascript_alert("Cannot move that field up any more", true);
				else
				{
					$link_id_2 = 0;
					$sort_order_2 = $sort_order_1 - 1;
					$sql = "SELECT REPORT_FIELD_LINK_ID FROM REPORT_FIELD_LINK
							WHERE (REPORT_ID=$edit_id) AND (SORT_ORDER=$sort_order_2)";
					sql_execute($sql);
					while (($newArray = sql_fetch()) != false)
						$link_id_2 = $newArray[0];

					$sql = "UPDATE REPORT_FIELD_LINK SET SORT_ORDER=$sort_order_2
							WHERE REPORT_FIELD_LINK_ID=$link_id_1";
					audit_setup_gen('REPORT_FIELD_LINK', 'REPORT_FIELD_LINK_ID', $link_id_1, 'SORT_ORDER', $sort_order_2);
					sql_execute($sql, true); # audited

					$sql = "UPDATE REPORT_FIELD_LINK SET SORT_ORDER=$sort_order_1
							WHERE REPORT_FIELD_LINK_ID=$link_id_2";
					audit_setup_gen('REPORT_FIELD_LINK', 'REPORT_FIELD_LINK_ID', $link_id_2, 'SORT_ORDER', $sort_order_1);
					sql_execute($sql, true); # audited

					$now = date_now_sql();
					$sql = "UPDATE REPORT SET EDITED_DT='$now' WHERE REPORT_ID=$edit_id";
					audit_setup_gen('REPORT', 'REPORT_ID', $edit_id, 'EDITED_DT', $now);
					sql_execute($sql, true); # audited
				}
			}
		}
	}
	else
		dprint("Cannot move field - there is no Report ID!", true);
} # sql_move_field_up()

function sql_move_field_down()
{
	global $fi_ticked; # "fields in" that are ticked and should stay ticked
	global $edit_id; # the report being edited

	if (0 < $edit_id)
	{
		foreach ($_POST as $key => $value)
		{
			# Only one $key should be a "fi_..."
			if ((substr($key, 0, 3) == "fi_") && ($value == 1))
			{
				$field_id = intval(substr($key, 3));
				$fi_ticked[] = $field_id;
				$sql = "SELECT REPORT_FIELD_LINK_ID, SORT_ORDER FROM REPORT_FIELD_LINK
						WHERE (REPORT_ID=$edit_id) AND (REPORT_FIELD_ID=$field_id)";
				sql_execute($sql);
				$link_id_1 = 0;
				$sort_order_1 = 0;
				while (($newArray = sql_fetch()) != false)
				{
					$link_id_1 = $newArray[0];
					$sort_order_1 = $newArray[1];
				}

				$link_id_2 = 0;
				$sort_order_2 = $sort_order_1 + 1;
				$sql = "SELECT REPORT_FIELD_LINK_ID FROM REPORT_FIELD_LINK
						WHERE (REPORT_ID=$edit_id) AND (SORT_ORDER=$sort_order_2)";
				sql_execute($sql);
				while (($newArray = sql_fetch()) != false)
					$link_id_2 = $newArray[0];
				if ($link_id_2 == 0)
					javascript_alert("Cannot move that field down any more", true);
				else
				{
					$sql = "UPDATE REPORT_FIELD_LINK SET SORT_ORDER=$sort_order_2
							WHERE REPORT_FIELD_LINK_ID=$link_id_1";
					audit_setup_gen('REPORT_FIELD_LINK', 'REPORT_FIELD_LINK_ID', $link_id_1, 'SORT_ORDER', $sort_order_2);
					sql_execute($sql, true); # audited

					$sql = "UPDATE REPORT_FIELD_LINK SET SORT_ORDER=$sort_order_1
							WHERE REPORT_FIELD_LINK_ID=$link_id_2";
					audit_setup_gen('REPORT_FIELD_LINK', 'REPORT_FIELD_LINK_ID', $link_id_2, 'SORT_ORDER', $sort_order_1);
					sql_execute($sql, true); # audited

					$now = date_now_sql();
					$sql = "UPDATE REPORT SET EDITED_DT='$now' WHERE REPORT_ID=$edit_id";
					audit_setup_gen('REPORT', 'REPORT_ID', $edit_id, 'EDITED_DT', $now);
					sql_execute($sql, true); # audited
				}
			}
		}
	}
	else
		dprint("Cannot move field - there is no Report ID!", true);
} # sql_move_field_down()

//function client_list_set($clients)
//{
//	$list = '';
//	foreach ($clients as $cli)
//		$list .= ($list ? '||' : '') . implode('~~', $cli);
//	return str_replace("'", "\\'", $list);
//}
//
//function client_list_get($list)
//{
//	$clients = array();
//	$temp_1 = explode('||', $list);
//	foreach ($temp_1 as $t1)
//	{
//		$temp_2 = explode('~~', $t1);
//		$clients[] = array('CLIENT2_ID' => $temp_2[0], 'C_CODE' => $temp_2[1], 'C_CO_NAME' => $temp_2[2]);
//	}
//	return $clients;
//}
//
//function get_clients_from_post()
//{
//	global $client_ids_added;
//	global $client_list_in; # string version of $clients_in
//	global $client_list_out; # string version of $clients_out
//
//	$clients_in = client_list_get($client_list_in);
//	$clients_out = client_list_get($client_list_out);
//	if ($client_ids_added)
//	{
//		foreach ($clients_out as $out)
//		{
//			if (in_array($out['CLIENT2_ID'], $client_ids_added))
//			{
//				# Move this client from $clients_out to $clients_in
//				$clients_in
//			}
//		}
//	}
//
//}

function custom_delete_temp()
{
	# The database table REPORT may contain temporary reports which didn't become permanent and never will.

	global $sqlNow;

	$prints = array();
	$deletes = 0;

	$date_add = sql_date_add("CAST($sqlNow AS DATE)", 'DAY', -1);
	$sql = "SELECT REPORT_ID FROM REPORT WHERE (REP_TEMP=1) AND (CREATED_DT < $date_add)";
	$prints[] = $sql;
	sql_execute($sql);
	$ids = array();
	while (($newArray = sql_fetch()) != false)
		$ids[] = $newArray[0];
	$prints[] = "...found: " . print_r($ids,1);

	foreach ($ids as $id)
	{
		$sql = "DELETE FROM REPORT WHERE REPORT_ID=$id";
		$prints[] = $sql;
		# The currently logged in user will be marked as the person who deleted these temporary records but it doesn't matter
		audit_setup_gen('REPORT', 'REPORT_ID', $id, '', '');
		sql_execute($sql, true); # audited
		$deletes++;
	}

	if (0 < $deletes)
	{
		foreach ($prints as $line)
			dprint($line);
	}

} # custom_delete_temp()

function print_run($report='')
{
	# NOTE: the caller must put <form> and </form> around the call to this function.

	global $ar;
	global $col3;
	global $edit_id;
	global $from_edit_screen;
	global $run_p1_dt_fr;
	global $run_p1_dt_to;
	global $run_rx_dt_fr;
	global $run_rx_dt_to;
	global $sz_date;

	if ($report == '')
	{
		$report = sql_get_report($edit_id);
		#dprint("report=" . print_r($report,1));#
		if ($report)
			u_report_id_set($edit_id);
	}

	$abandon_run = false;
	if ($report['OBSOLETE'])
	{
		dprint("This report is obsolete and so cannot be run", true);
		$abandon_run = true;
	}
	elseif (!$from_edit_screen)
	{
		$clients = sql_get_clients_for_report($report['REPORT_ID']); # list of CLIENT2.CLIENT2_ID
		if (count($clients) == 0)
		{
			dprint("This report has no clients and so cannot be run!", true);
			$abandon_run = true;
		}
		$fields = sql_get_fields_for_report($report['REPORT_ID']); # list of REPORT_FIELD_SD.RF_CODE
		if (count($fields) == 0)
		{
			dprint("This report has no fields and so cannot be run!", true);
			$abandon_run = true;
		}
	}

	$periods = (($report['REP_ANALYSIS'] || $report['REP_COLLECT']) ? true : false);

////	if (count($_POST) == 0)
////	{
////		$run_rx_dt_fr = date_now(true, date_last_month(1), false); # last month plus one day
////		$run_rx_dt_to = date_now(true, '', false);
////	}
////	else
////	{
//		$run_rx_dt_fr = post_val('run_rx_dt_fr', false, true);
//		$run_rx_dt_to = post_val('run_rx_dt_to', false, true);
////	}
	if (array_key_exists('run_rx_dt_fr', $_POST))
	{
		$run_rx_dt_fr = post_val('run_rx_dt_fr', false, true, false, 1);
		$run_rx_dt_to = post_val('run_rx_dt_to', false, true, false, 1);
		if ($periods)
		{
			$run_p1_dt_fr = post_val('run_p1_dt_fr', false, true, false, 1);
			$run_p1_dt_to = post_val('run_p1_dt_to', false, true, false, 1);
		}
	}
	else
	{
		$run_rx_dt_fr = ($report['RUN_RX_DT_FR'] ? date_for_sql($report['RUN_RX_DT_FR'], true, false) : '');
		$run_rx_dt_to = ($report['RUN_RX_DT_TO'] ? date_for_sql($report['RUN_RX_DT_TO'], true, false) : '');
		if ($periods)
		{
			$run_p1_dt_fr = ($report['RUN_P1_DT_FR'] ? date_for_sql($report['RUN_P1_DT_FR'], true, false) : '');
			$run_p1_dt_to = ($report['RUN_P1_DT_TO'] ? date_for_sql($report['RUN_P1_DT_TO'], true, false) : '');
		}
	}
	if (!$periods)
	{
		$run_p1_dt_fr = '';
		$run_p1_dt_to = '';
	}
	#dprint("periods=$periods, run_rx_dt_fr=$run_rx_dt_fr, run_rx_dt_to=$run_rx_dt_to, run_p1_dt_fr=$run_p1_dt_fr, run_p1_dt_to=$run_p1_dt_to");#

	$calendar_names = array();
	$onkeydown = '';
	$onchange = '';

	if ($from_edit_screen)
		print "
		<h3>Run Report</h3>
		";
	print "
		" . ($from_edit_screen ? '' : input_button('Back', 'main_screen()')) . "
		" . input_hidden('run_task', '') . "
		" . input_hidden('run_xl', '') . "
	";
	if (!$abandon_run)
	{
		print "
		<table>
		<tr>
			<td>Job Received Dates:</td>
			<td width=\"10\"></td>
			<td $ar>From:</td>
			<td>" . input_textbox('run_rx_dt_fr', $run_rx_dt_fr, $sz_date, 0, $onkeydown . $onchange) .
											calendar_icon('run_rx_dt_fr') . "</td>
			<td width=\"10\"></td>
			<td $ar>To:</td>
			<td>" . input_textbox('run_rx_dt_to', $run_rx_dt_to, $sz_date, 0, $onkeydown . $onchange) .
											calendar_icon('run_rx_dt_to') . "</td>
		</tr>
		";
		$calendar_names[] = "run_rx_dt_fr";
		$calendar_names[] = "run_rx_dt_to";

		if ($periods)
		{
			$label = ($report['REP_ANALYSIS'] ? "Period 1 Dates" : "Payment Period");
			print "
			<tr>
				<td>$label:</td>
				<td width=\"10\"></td>
				<td $ar>From:</td>
				<td>" . input_textbox('run_p1_dt_fr', $run_p1_dt_fr, $sz_date, 0, $onkeydown . $onchange) .
												calendar_icon('run_p1_dt_fr') . "</td>
				<td width=\"10\"></td>
				<td $ar>To:</td>
				<td>" . input_textbox('run_p1_dt_to', $run_p1_dt_to, $sz_date, 0, $onkeydown . $onchange) .
												calendar_icon('run_p1_dt_to') . "</td>
			</tr>
			";
			$calendar_names[] = "run_p1_dt_fr";
			$calendar_names[] = "run_p1_dt_to";
		}
		print "
		<tr>
			<td>" . input_button("Run Report", "run_report(0)") . "</td>
			";
			if (!$from_edit_screen)
				print "
				<td $col3>" . input_button("Edit Report", "edit_report(0)") . "</td>
				";
			print "
		</tr>
		</table>
		<a name=\"a_run\"></a>
		";

		$run_task = post_val('run_task');
		if ( //($run_task == 'run') ||
			($run_task == 'run_edit') || ($run_task == 'run_run'))
		{
			if ($run_rx_dt_fr)
				$run_rx_dt_fr_sql = date_for_sql($run_rx_dt_fr);
			else
				$run_rx_dt_fr_sql = '';
			if ($run_rx_dt_to)
			{
				$run_rx_dt_to_sql = date_for_sql($run_rx_dt_to);
				$run_rx_dt_to_plus1_sql = date_for_sql($run_rx_dt_to, false, false, false, false, true); # add one day
			}
			else
			{
				$run_rx_dt_to_sql = '';
				$run_rx_dt_to_plus1_sql = '';
			}
			if ($run_p1_dt_fr)
				$run_p1_dt_fr_sql = date_for_sql($run_p1_dt_fr);
			else
				$run_p1_dt_fr_sql = '';
			if ($run_p1_dt_to)
			{
				$run_p1_dt_to_sql = date_for_sql($run_p1_dt_to);
				$run_p1_dt_to_plus1_sql = date_for_sql($run_p1_dt_to, false, false, false, false, true); # add one day
			}
			else
			{
				$run_p1_dt_to_sql = '';
				$run_p1_dt_to_plus1_sql = '';
			}
			#dprint("\$run_rx_dt_fr_sql=$run_rx_dt_fr_sql, \$run_rx_dt_to_sql=$run_rx_dt_to_sql,
			#			\$run_p1_dt_fr_sql=$run_p1_dt_fr_sql, \$run_p1_dt_to_sql=$run_p1_dt_to_sql");#
			sql_update_run_dates($report['REPORT_ID'], $run_rx_dt_fr_sql, $run_rx_dt_to_sql, $run_p1_dt_fr_sql, $run_p1_dt_to_sql);
			run_report($report, $run_rx_dt_fr_sql, $run_rx_dt_to_plus1_sql, $run_p1_dt_fr_sql, $run_p1_dt_to_plus1_sql);
		}
	}
	else
		print input_button("Edit Report", "edit_report(0)") . "<br>";
//	if (global_debug())
//		dlog("Finished print_run()");

	javascript_calendars($calendar_names);

} # print_run()

function sql_update_run_dates($report_id, $run_rx_dt_fr_sql, $run_rx_dt_to_sql, $run_p1_dt_fr_sql, $run_p1_dt_to_sql)
{
	$sql = "UPDATE REPORT SET
				RUN_RX_DT_FR=" . ($run_rx_dt_fr_sql ? $run_rx_dt_fr_sql : 'NULL') . ",
				RUN_RX_DT_TO=" . ($run_rx_dt_to_sql ? $run_rx_dt_to_sql : 'NULL') . ",
				RUN_P1_DT_FR=" . ($run_p1_dt_fr_sql ? $run_p1_dt_fr_sql : 'NULL') . ",
				RUN_P1_DT_TO=" . ($run_p1_dt_to_sql ? $run_p1_dt_to_sql : 'NULL') . "
			WHERE REPORT_ID=$report_id";
	sql_execute($sql); # no need to audit
}

function run_report($report, $run_rx_dt_fr_sql, $run_rx_dt_to_plus1_sql, $run_p1_dt_fr_sql, $run_p1_dt_to_plus1_sql)
{
	# Date parameters are SQL format with quotes around them

	global $anchor;
	global $USER;

	$report_id = $report['REPORT_ID'];
	$this_function = "run_report([$report_id], $run_rx_dt_fr_sql, $run_rx_dt_to_plus1_sql, $run_p1_dt_fr_sql, $run_p1_dt_to_plus1_sql)";
	$anchor = 'a_run';
	$run_xl = post_val('run_xl', true);
	$dest = ($run_xl ? 'x' : 's');

	if ($report['REP_ANALYSIS'])
	{
		if ($report['REP_COLLECT'])
		{
			$xfile = ($run_xl ? "r{$report_id}_u{$USER['USER_ID']}_ana_col" : '');
			dprint("$this_function: Analysis/Collect: dest=$dest, xfile=$xfile");
			run_ana_col($report, $run_rx_dt_fr_sql, $run_rx_dt_to_plus1_sql, $run_p1_dt_fr_sql, $run_p1_dt_to_plus1_sql, $dest, $xfile);
		}
		else
		{
			dprint("$this_function: Analysis/Trace: not currently supported");
		}
	}
	else
	{
		if ($report['REP_COLLECT'])
		{
			$xfile = ($run_xl ? "r{$report_id}_u{$USER['USER_ID']}_jobdet_col" : '');
			#dprint("$this_function: JobDetail/Collect: dest=$dest, xfile=$xfile");
			run_jobdet_col($report, $run_rx_dt_fr_sql, $run_rx_dt_to_plus1_sql, $run_p1_dt_fr_sql, $run_p1_dt_to_plus1_sql, $dest, $xfile);
		}
		else
		{
			$xfile = ($run_xl ? "r{$report_id}_u{$USER['USER_ID']}_jobdet_trc" : '');
			#dprint("$this_function: JobDetail/Trace: dest=$dest, xfile=$xfile");
			run_jobdet_trc($report, $run_rx_dt_fr_sql, $run_rx_dt_to_plus1_sql, $dest, $xfile);
		}
	}
//	if (global_debug())
//		dlog("Finished run_report()");

} # run_report()

function print_run_outer()
{
	# This is the equivalent of print_edit_screen(), but we are just running a report not editing it.

	global $edit_id; # the report being edited
	global $rc_analysis;
	global $rc_collect;
	global $rc_global;
	global $rc_report; # the report selected in the drop-down list

	$report = sql_get_report($edit_id);
	#dprint("report=" . print_r($report,1));#
	if ($report)
		u_report_id_set($edit_id);

	print "
	<h3>Runing report \"{$report['REP_NAME']}\"</h3>
	<form name=\"edit_form\" action=\"" . server_php_self() . "\" method=\"post\">
		" . input_hidden('rc_global', $rc_global) . "
		" . input_hidden('rc_analysis', $rc_analysis) . "
		" . input_hidden('rc_collect', $rc_collect) . "
		" . input_hidden('rc_report', $rc_report) . "
		" . input_hidden('edit_id', $edit_id) . "
		" . input_hidden('rc_task', '') . "
		" . input_hidden('client_list_out', '') . "
		" . input_hidden('client_list_in', '') . "
	";

	print_run($report); # this must be called before printing </form>

	print "
	</form><!--edit_form-->
	";
	anchor_jump();

//	if (global_debug())
//		dlog("Finished print_run_outer()");
} # print_run_outer()

function sql_set_obsolete()
{
	global $edit_id;
	global $sqlFalse;
	global $sqlTrue;

	$obsolete = (post_val('obsolete', true) ? $sqlTrue : $sqlFalse);
	$sql = "UPDATE REPORT SET OBSOLETE=$obsolete WHERE REPORT_ID=$edit_id";
	audit_setup_gen('REPORT', 'REPORT_ID', $edit_id, 'OBSOLETE', $obsolete);
	sql_execute($sql, true); # audited
}

#==================================================================================================
function run_ana_col($report, $run_rx_dt_fr_sql, $run_rx_dt_to_plus1_sql, $run_p1_dt_fr_sql, $run_p1_dt_to_plus1_sql, $dest, $xfile='')
{
	# Run report: Analysis Collection,
	# both Multi-sheet (each client on a separate sheet),
	# and Single-sheet (all clients on the same sheet).
	# Output to screen and maybe to Excel too ($dest = 's' or 'x').

	global $ac; # settings.php
	global $ar; # settings.php
	global $csv_path; # settings.php
	global $id_JOB_STATUS_rcr; # JOB_STATUS_SD.JOB_STATUS_ID for "RCR"
	global $id_ROUTE_cspent; # aka "returned"
	global $id_ROUTE_direct; # settings.php
	global $id_ROUTE_fwd;
	global $id_ROUTE_tous; # aka "Vilcol"
	global $phpExcel_ext; # settings.php: "xls"
	global $sqlFalse; # settings.php
	global $sqlTrue; # settings.php
	global $reps_subdir;

	$this_function = "run_ana_col([{$report['REPORT_ID']}], $run_rx_dt_fr_sql, $run_rx_dt_to_plus1_sql, $run_p1_dt_fr_sql, $run_p1_dt_to_plus1_sql, $dest, $xfile)";
	#dprint($this_function);#

	# ---- Sanity checks --------------------------------------------------------------------------

	$clients = sql_get_clients_for_report($report['REPORT_ID']); # list of CLIENT2.CLIENT2_ID
	if (!$clients)
	{
		dprint("$this_function: No Clients!!", true);
		return;
	}

	$fields = sql_get_fields_for_report($report['REPORT_ID']); # list of REPORT_FIELD_SD.RF_CODE
	if (!$fields)
	{
		dprint("$this_function: No Fields!!", true);
		return;
	}

	if (($dest == 'x') && (!$xfile))
	{
		dprint("$this_function: No Excel filename!!", true);
		return;
	}

	$year_subtotals = ($report['SUBT_BY_YEAR'] ? true : false);
	$multi_sheet = ($report['CLIENT_PER_SHEET'] ? true : false);
	if ((!$multi_sheet) && $year_subtotals)
	{
		dprint("Cannot have All-clients-on-same-sheet at same time as Subtotals-by-year.<br>Subtotals-by-year will be ignored.", true);
		$year_subtotals = false;
	}
	# ---------------------------------------------------------------------------------------------

	# ---- Establish what input data we need ------------------------------------------------------

	$select = array('C.CLIENT2_ID', 'J.JOB_ID', 'J.J_OPENED_DT');
	$v_j_count = false;
	$v_tot_owed = false;
	$v_avg_debt = false;
	$v_coll_tot_1 = false;
	$v_coll_pcent_1 = false;
	$v_coll_dir_1 = false;
	$v_coll_vil_1 = false;
	$v_coll_fwd_1 = false;
	$v_ret_tot_1 = false;
	$v_out_op = false;
	$v_coll_tot_2 = false;
	$v_coll_pcent_2 = false;
	$v_coll_dir_2 = false;
	$v_coll_vil_2 = false;
	$v_coll_fwd_2 = false;
	$v_ret_tot_2 = false;
	$v_co_pf_n = false; # closed-out, paid in full, number of jobs
	$v_co_pf_v = false; # closed-out, paid in full, value (amounts owed) of jobs
	$v_co_pp_n = false; # closed-out, paid in part, number of jobs
	$v_co_pp_v = false; # closed-out, paid in part, value (amounts owed) of jobs
	$v_co_np_n = false; # closed-out, paid zero (nothing), number of jobs
	$v_co_np_v = false; # closed-out, paid zero (nothing), value (amounts owed) of jobs
	$v_op_pp_n = false; # open, paid in part, number of jobs
	$v_op_pp_v = false; # open, paid in part, value (amounts owed) of jobs
	$v_op_np_n = false; # open, paid zero (nothing), number of jobs
	$v_op_np_v = false; # open, paid zero (nothing), value (amounts owed) of jobs
	$v_tc_ntrc = false; # T/C job with no trace
	$v_rcr_n = false;
	$v_rcr_v = false;

	foreach ($fields as $fld)
	{
		switch ($fld)
		{
			# The following fields are in the same order as the table in the spec (section 9.11 "Analysis Reports")
			case 'V_J_RX_DT':
				# J.J_OPENED_DT is always included
				break;
			case 'V_J_COUNT':
				$v_j_count = true;
				break;
			case 'V_TOT_OWED':
				if (!$v_tot_owed)
				{
					if (!in_array('J.JC_TOTAL_AMT', $select))
						$select[] = 'J.JC_TOTAL_AMT';
					$v_tot_owed = true;
				}
				break;
			case 'V_AVG_DEBT':
				$v_avg_debt = true;
				# We need job count and amount owed
				$v_j_count = true;
				if (!$v_tot_owed)
				{
					if (!in_array('J.JC_TOTAL_AMT', $select))
						$select[] = 'J.JC_TOTAL_AMT';
					$v_tot_owed = true;
				}
				break;
			case 'V_COLL_TOT_1':
				$v_coll_tot_1 = true;
				break;
			case 'V_COLL_PCENT_1':
				$v_coll_pcent_1 = true;
				# We need amount owed and amount collected
				if (!$v_tot_owed)
				{
					if (!in_array('J.JC_TOTAL_AMT', $select))
						$select[] = 'J.JC_TOTAL_AMT';
					$v_tot_owed = true;
				}
				$v_coll_tot_1 = true;
				break;
			case 'V_COLL_DIR_1':
				$v_coll_dir_1 = true;
				break;
			case 'V_COLL_VIL_1':
				$v_coll_vil_1 = true;
				break;
			case 'V_COLL_FWD_1':
				$v_coll_fwd_1 = true;
				break;
			case 'V_RET_TOT_1':
				$v_ret_tot_1 = true;
				break;
			case 'V_OUT_OP':
				$v_out_op = true; # but only for open jobs
				# We need amount owed and amount collected
				if (!$v_tot_owed)
				{
					if (!in_array('J.JC_TOTAL_AMT', $select))
						$select[] = 'J.JC_TOTAL_AMT';
					$v_tot_owed = true;
				}
				$v_coll_tot_1 = true;
				break;
			case 'V_COLL_TOT_2':
				$v_coll_tot_2 = true;
				break;
			case 'V_COLL_PCENT_2':
				$v_coll_pcent_2 = true;
				# We need amount owed and amount collected
				if (!$v_tot_owed)
				{
					if (!in_array('J.JC_TOTAL_AMT', $select))
						$select[] = 'J.JC_TOTAL_AMT';
					$v_tot_owed = true;
				}
				$v_coll_tot_2 = true;
				break;
			case 'V_COLL_DIR_2':
				$v_coll_dir_2 = true;
				break;
			case 'V_COLL_VIL_2':
				$v_coll_vil_2 = true;
				break;
			case 'V_COLL_FWD_2':
				$v_coll_fwd_2 = true;
				break;
			case 'V_RET_TOT_2':
				$v_ret_tot_2 = true;
				break;
			case 'V_CO_PF_N':
			case 'V_CO_PF_V':
			case 'V_CO_PP_N':
			case 'V_CO_PP_V':
			case 'V_CO_NP_N':
			case 'V_CO_NP_V':
			case 'V_OP_PP_N':
			case 'V_OP_PP_V':
			case 'V_OP_NP_N':
			case 'V_OP_NP_V':
				switch ($fld)
				{
					case 'V_CO_PF_N': $v_co_pf_n = true; break;
					case 'V_CO_PF_V': $v_co_pf_v = true; break;
					case 'V_CO_PP_N': $v_co_pp_n = true; break;
					case 'V_CO_PP_V': $v_co_pp_v = true; break;
					case 'V_CO_NP_N': $v_co_np_n = true; break;
					case 'V_CO_NP_V': $v_co_np_v = true; break;
					case 'V_OP_PP_N': $v_op_pp_n = true; break;
					case 'V_OP_PP_V': $v_op_pp_v = true; break;
					case 'V_OP_NP_N': $v_op_np_n = true; break;
					case 'V_OP_NP_V': $v_op_np_v = true; break;
				}
				# We need amount owed and amount collected
				if (!$v_tot_owed)
				{
					if (!in_array('J.JC_TOTAL_AMT', $select))
						$select[] = 'J.JC_TOTAL_AMT';
					$v_tot_owed = true;
				}
				$v_coll_tot_1 = true;
				break;
			case 'V_TC_NTRC':
				$v_tc_ntrc = true;
				break;
			case 'V_RCR_N':
				$v_rcr_n = true;
				break;
			case 'V_RCR_V':
				$v_rcr_v = true;
				break;
			default:
				dprint("Error $this_function: field \"$fld\" not recognised", true);
				break;
		}
	}
	# ---------------------------------------------------------------------------------------------

	# ---- Get the raw data from the database -----------------------------------------------------

	$jobs = array(); # indexed by JOB_ID
	$in_sheets = array(); # indexed by CLIENT2_ID, one sheet per client
	$client_names = array();

	foreach ($clients as $client2_id)
	{
		$in_sheets[$client2_id] = array(); # each sheet will have a list of dates; each date will be indexed by J_OPENED_DT (either day or month)
		$client_names[$client2_id] = client_name_from_id($client2_id);
	}

	$where = array("J.OBSOLETE=$sqlFalse", "J.JC_JOB=$sqlTrue", "C.CLIENT2_ID IN (" . implode(',', $clients) . ")");
	if ($run_rx_dt_fr_sql)
		$where[] = "$run_rx_dt_fr_sql <= J.J_OPENED_DT";
	if ($run_rx_dt_to_plus1_sql)
		$where[] = "J.J_OPENED_DT < $run_rx_dt_to_plus1_sql";

	$where_base = "(" . implode(') AND (', $where) . ")";
	$where_base = str_replace('C.CLIENT2_ID', 'J.CLIENT2_ID', $where_base);

	# Set of open jobs
	$jobs_open = array(); # list of JOB_ID of jobs that are open
	$balances_open = array(); # list of JOB_ID => array('OWED' => x, 'PAID' => y) for each job in $jobs_open
	if ($v_out_op || $v_op_pp_n || $v_op_pp_v || $v_op_np_n || $v_op_np_v)
	{
		# Get list of Job IDs for all open jobs in the received-date range
		$sql = "SELECT J.JOB_ID
				FROM JOB AS J
				WHERE $where_base AND (J.JOB_CLOSED=$sqlFalse)";
		#dprint($sql);#
		sql_execute($sql);
		while (($newArray = sql_fetch_assoc()) != false)
			$jobs_open[] = $newArray['JOB_ID'];
	} # Set of open jobs

	# Set of closed jobs
	$jobs_closed = array();
	$balances_closed = array(); # list of JOB_ID => array('OWED' => x, 'PAID' => y) for each job in $jobs_closed
	if ($v_co_pf_n || $v_co_pf_v || $v_co_pp_n || $v_co_pp_v || $v_co_np_n || $v_co_np_v)
	{
		# Get list of Job IDs for all closed jobs in the received-date range
		$sql = "SELECT J.JOB_ID
				FROM JOB AS J
				WHERE $where_base AND (J.JOB_CLOSED=$sqlTrue)";
		#dprint($sql);#
		sql_execute($sql);
		while (($newArray = sql_fetch_assoc()) != false)
			$jobs_closed[] = $newArray['JOB_ID'];
	} # Set of closed jobs

	# Set of T/C jobs
	$jobs_tc = array();
	if ($v_tc_ntrc)
	{
		# Get list of Job IDs for all T/C jobs in the received-date range
		$sql = "SELECT J.JOB_ID, J.JOB_GROUP_ID
				FROM JOB AS J
				WHERE $where_base AND (J.JC_TC_JOB=$sqlTrue) AND (0 < J.JOB_GROUP_ID)";
		#dprint($sql);#
		sql_execute($sql);
		while (($newArray = sql_fetch_assoc()) != false)
			$jobs_tc[$newArray['JOB_ID']] = array('JOB_GROUP_ID' => intval($newArray['JOB_GROUP_ID']), 'LINKED_COUNT' => 0);

		# For each T/C Collect job, find count of linked Trace jobs
		foreach ($jobs_tc as $job_id => $tc_info)
		{
			$group_id = $tc_info['JOB_GROUP_ID'];
			$linked_count = $tc_info['LINKED_COUNT'];
			if (0 < $group_id)
			{
				$sql = "SELECT COUNT(*) FROM JOB
						WHERE (JC_TC_JOB=$sqlTrue) AND (JOB_ID <> $job_id) AND (JOB_GROUP_ID=$group_id)";
				#dprint($sql);#
				sql_execute($sql);
				while (($newArray = sql_fetch()) != false)
					$linked_count = $newArray[0];
			}
			if (0 < $linked_count)
				$jobs_tc[$job_id]['LINKED_COUNT'] = $linked_count;
		}
	} # Set of T/C jobs

	# Set of RCR jobs
	$jobs_rcr = array();
	if ($v_rcr_n || $v_rcr_v)
	{
		# Get list of Job IDs for all RCR jobs in the received-date range
		$sql = "SELECT J.JOB_ID, J.JC_TOTAL_AMT
				FROM JOB AS J
				WHERE $where_base AND J.JC_JOB_STATUS_ID=$id_JOB_STATUS_rcr
				";
		#dprint($sql);#
		sql_execute($sql);
		while (($newArray = sql_fetch_assoc()) != false)
			$jobs_rcr[$newArray['JOB_ID']] = $newArray['JC_TOTAL_AMT'];
	} # Set of RCR jobs
	#dprint("jobs_rcr=" . print_r($jobs_rcr,1));#

	# Built $in_sheets: list of date-based lines, zero or more jobs per date/line:
	$sql = "SELECT " . implode(', ', $select) . "
			FROM JOB AS J
			INNER JOIN CLIENT2 AS C ON C.CLIENT2_ID=J.CLIENT2_ID
			WHERE (" . implode(') AND (', $where) . ")
			ORDER BY J_OPENED_DT";
	#dprint(str_replace(chr(10),'<br>',$sql));#
	sql_execute($sql);
	while (($newArray = sql_fetch_assoc()) != false)
	{
		$job_id = $newArray['JOB_ID'];
		$client2_id = $newArray['CLIENT2_ID'];

		# Add to $jobs list
		$jobs[$job_id] = $newArray;

		# Add to this sheet's dates list
		if ($report['MONTH_RCVD'])
			$date = substr($newArray['J_OPENED_DT'], 0, strlen('yyyy-mm')) . '-01';
		else
			$date = substr($newArray['J_OPENED_DT'], 0, strlen('yyyy-mm-dd'));
		if (!array_key_exists($date, $in_sheets[$client2_id]))
		{
			# Create new empty date-info element for all sheets
			foreach ($clients as $cid)
			{
				$in_sheets[$cid][$date] = array('JOBS' => array());
				if ($v_j_count)
					$in_sheets[$cid][$date]['V_J_COUNT'] = 0;
				if ($v_tot_owed)
					$in_sheets[$cid][$date]['V_TOT_OWED'] = 0.0;
				if ($v_out_op)
					$in_sheets[$cid][$date]['V_OUT_OP'] = 0.0;
				if ($v_coll_tot_1)
					$in_sheets[$cid][$date]['V_COLL_TOT_1'] = 0.0;
				if ($v_coll_dir_1)
					$in_sheets[$cid][$date]['V_COLL_DIR_1'] = 0.0;
				if ($v_coll_vil_1)
					$in_sheets[$cid][$date]['V_COLL_VIL_1'] = 0.0;
				if ($v_coll_fwd_1)
					$in_sheets[$cid][$date]['V_COLL_FWD_1'] = 0.0;
				if ($v_ret_tot_1)
					$in_sheets[$cid][$date]['V_RET_TOT_1'] = 0.0;
				if ($v_co_pf_n)
					$in_sheets[$cid][$date]['V_CO_PF_N'] = 0;
				if ($v_co_pf_v)
					$in_sheets[$cid][$date]['V_CO_PF_V'] = 0.0;
				if ($v_co_pp_n)
					$in_sheets[$cid][$date]['V_CO_PP_N'] = 0;
				if ($v_co_pp_v)
					$in_sheets[$cid][$date]['V_CO_PP_V'] = 0.0;
				if ($v_co_np_n)
					$in_sheets[$cid][$date]['V_CO_NP_N'] = 0;
				if ($v_co_np_v)
					$in_sheets[$cid][$date]['V_CO_NP_V'] = 0.0;
				if ($v_op_pp_n)
					$in_sheets[$cid][$date]['V_OP_PP_N'] = 0;
				if ($v_op_pp_v)
					$in_sheets[$cid][$date]['V_OP_PP_V'] = 0.0;
				if ($v_op_np_n)
					$in_sheets[$cid][$date]['V_OP_NP_N'] = 0;
				if ($v_op_np_v)
					$in_sheets[$cid][$date]['V_OP_NP_V'] = 0.0;
				if ($v_tc_ntrc)
					$in_sheets[$cid][$date]['V_TC_NTRC'] = 0;
				if ($v_rcr_n)
					$in_sheets[$cid][$date]['V_RCR_N'] = 0;
				if ($v_rcr_v)
					$in_sheets[$cid][$date]['V_RCR_V'] = 0.0;
			}
		}

		# Fill in $dates element
		$in_sheets[$client2_id][$date]['JOBS'][] = $job_id;
		if ($v_j_count)
			$in_sheets[$client2_id][$date]['V_J_COUNT']++;
		if ($v_tot_owed)
			$in_sheets[$client2_id][$date]['V_TOT_OWED'] += $newArray['JC_TOTAL_AMT'];
		if ($v_out_op && in_array($job_id, $jobs_open))
			# In this while() loops, element V_OUT_OP is just the amount owed, for open jobs.
			$in_sheets[$client2_id][$date]['V_OUT_OP'] += $newArray['JC_TOTAL_AMT'];

		# Balances for open and closed jobs
		if ($v_co_pf_n || $v_co_pf_v || $v_co_pp_n || $v_co_pp_v || $v_co_np_n || $v_co_np_v ||
			$v_op_pp_n || $v_op_pp_v || $v_op_np_n || $v_op_np_v)
		{
			if (in_array($job_id, $jobs_open))
				$balances_open[$job_id] = array('OWED' => $newArray['JC_TOTAL_AMT'], 'PAID' => 0.0);
			elseif (in_array($job_id, $jobs_closed))
				$balances_closed[$job_id] = array('OWED' => $newArray['JC_TOTAL_AMT'], 'PAID' => 0.0);
		}
	}
	#dprint("Found " . count($jobs) . " jobs and " . count($in_sheets) . " sheets/clients."); #
	#dprint("jobs=" . print_r($jobs,1)); #

	# Amounts collected regardless of Period
	if ($v_coll_tot_1 || $v_coll_dir_1 || $v_coll_vil_1 || $v_coll_fwd_1 || $v_ret_tot_1 || $v_out_op)
	{
		# We need to run separate SQL to get the collection payments data.
		# For this section, we get collection payments regardless of period dates.
		#$print_col_sql = true;
		#$print_col_sql_out_op = true;
		foreach ($in_sheets as $client2_id => $dates)
		{
			foreach ($dates as $date => $date_info)
			{
				# We now have a client and a date (either day or whole month).
				# This is associated with zero or more jobs.
				if (0 < count($date_info['JOBS']))
				{
					if ($v_coll_tot_1 || $v_coll_dir_1 || $v_coll_vil_1 || $v_coll_fwd_1 || $v_ret_tot_1)
					{
						$jobs_ids = implode(',', $date_info['JOBS']);
						$sql = "SELECT COL_AMT_RX, COL_PAYMENT_ROUTE_ID FROM JOB_PAYMENT WHERE (OBSOLETE=$sqlFalse) AND (JOB_ID IN ($jobs_ids))";
						#if ($print_col_sql)
						#	dprint($sql);#
						#$print_col_sql = false;
						sql_execute($sql);
						while (($newArray = sql_fetch_assoc()) != false)
						{
							$amt = floatval($newArray['COL_AMT_RX']);
							if ($v_coll_tot_1)
								$in_sheets[$client2_id][$date]['V_COLL_TOT_1'] += $amt;
							if ($v_coll_dir_1 && ($newArray['COL_PAYMENT_ROUTE_ID'] == $id_ROUTE_direct))
								$in_sheets[$client2_id][$date]['V_COLL_DIR_1'] += $amt;
							if ($v_coll_vil_1 && ($newArray['COL_PAYMENT_ROUTE_ID'] == $id_ROUTE_tous))
								$in_sheets[$client2_id][$date]['V_COLL_VIL_1'] += $amt;
							if ($v_coll_fwd_1 && ($newArray['COL_PAYMENT_ROUTE_ID'] == $id_ROUTE_fwd))
								$in_sheets[$client2_id][$date]['V_COLL_FWD_1'] += $amt;
							if ($v_ret_tot_1 && ($newArray['COL_PAYMENT_ROUTE_ID'] == $id_ROUTE_cspent))
								$in_sheets[$client2_id][$date]['V_RET_TOT_1'] += $amt;
						}
					}
					if ($v_out_op)
					{
						# Outstanding amounts for open jobs
						$open_ids = array();
						foreach ($date_info['JOBS'] as $job_id)
						{
							if (in_array($job_id, $jobs_open))
								$open_ids[] = $job_id;
						}
						if (0 < count($open_ids))
						{
							# Get amounts paid, for all the open jobs with a job-received date on this specific $date,
							# regardless of when the payment was made.
							$jobs_ids = implode(',', $open_ids);
							$sql = "SELECT COL_AMT_RX FROM JOB_PAYMENT WHERE (OBSOLETE=$sqlFalse) AND (JOB_ID IN ($jobs_ids))";
							#if ($print_col_sql_out_op)
							#	dprint($sql);#
							#$print_col_sql_out_op = false;
							sql_execute($sql);
							while (($newArray = sql_fetch_assoc()) != false)
								# Reduce the outstanding amount by the amount paid
								$in_sheets[$client2_id][$date]['V_OUT_OP'] -= floatval($newArray['COL_AMT_RX']);
						}
					}
				}
			}
		}
	} # Amounts collected regardless of Period

	# Amounts collected in Period
	if ($v_coll_tot_2 || $v_coll_dir_2 || $v_coll_vil_2 || $v_coll_fwd_2 || $v_ret_tot_2)
	{
		# We need to run separate SQL to get the collection payments data.
		# For this section, we get collection payments within the given period dates.
		#$print_col_sql = true;
		foreach ($in_sheets as $client2_id => $dates)
		{
			foreach ($dates as $date => $date_info)
			{
				# We now have a client and a date (either day or whole month).
				# This is associated with zero or more jobs.
				if ($v_coll_tot_2)
					$in_sheets[$client2_id][$date]['V_COLL_TOT_2'] = 0.0;
				if ($v_coll_dir_2)
					$in_sheets[$client2_id][$date]['V_COLL_DIR_2'] = 0.0;
				if ($v_coll_vil_2)
					$in_sheets[$client2_id][$date]['V_COLL_VIL_2'] = 0.0;
				if ($v_coll_fwd_2)
					$in_sheets[$client2_id][$date]['V_COLL_FWD_2'] = 0.0;
				if ($v_ret_tot_2)
					$in_sheets[$client2_id][$date]['V_RET_TOT_2'] = 0.0;
				if (0 < count($date_info['JOBS']))
				{
					$jobs_ids = implode(',', $date_info['JOBS']);
					$where = array("OBSOLETE=$sqlFalse", "JOB_ID IN ($jobs_ids)");
					if ($run_p1_dt_fr_sql)
						$where[] = "$run_p1_dt_fr_sql <= COL_DT_RX";
					if ($run_p1_dt_to_plus1_sql)
						$where[] = "COL_DT_RX < $run_p1_dt_to_plus1_sql";
					$sql = "SELECT COL_AMT_RX, COL_PAYMENT_ROUTE_ID FROM JOB_PAYMENT
							WHERE (" . implode(') AND (', $where) . ")";
					#if ($print_col_sql)
					#	dprint($sql);#
					#$print_col_sql = false;
					sql_execute($sql);
					while (($newArray = sql_fetch_assoc()) != false)
					{
						$amt = floatval($newArray['COL_AMT_RX']);
						if ($v_coll_tot_2)
							$in_sheets[$client2_id][$date]['V_COLL_TOT_2'] += $amt;
						if ($v_coll_dir_2 && ($newArray['COL_PAYMENT_ROUTE_ID'] == $id_ROUTE_direct))
							$in_sheets[$client2_id][$date]['V_COLL_DIR_2'] += $amt;
						if ($v_coll_vil_2 && ($newArray['COL_PAYMENT_ROUTE_ID'] == $id_ROUTE_tous))
							$in_sheets[$client2_id][$date]['V_COLL_VIL_2'] += $amt;
						if ($v_coll_fwd_2 && ($newArray['COL_PAYMENT_ROUTE_ID'] == $id_ROUTE_fwd))
							$in_sheets[$client2_id][$date]['V_COLL_FWD_2'] += $amt;
						if ($v_ret_tot_2 && ($newArray['COL_PAYMENT_ROUTE_ID'] == $id_ROUTE_cspent))
							$in_sheets[$client2_id][$date]['V_RET_TOT_2'] += $amt;
					}
				}
			}
		}
	}  # Amounts collected in Period

	# Balances for all jobs found in received-date range
	if ($v_co_pf_n || $v_co_pf_v || $v_co_pp_n || $v_co_pp_v || $v_co_np_n || $v_co_np_v ||
		$v_op_pp_n || $v_op_pp_v || $v_op_np_n || $v_op_np_v)
	{
		foreach ($jobs_open as $job_id)
		{
			$sql = "SELECT COL_AMT_RX FROM JOB_PAYMENT WHERE JOB_ID=$job_id AND (OBSOLETE=$sqlFalse)";
			sql_execute($sql);
			while (($newArray = sql_fetch_assoc()) != false)
				$balances_open[$job_id]['PAID'] += floatval($newArray['COL_AMT_RX']);
		}
		foreach ($jobs_closed as $job_id)
		{
			$sql = "SELECT COL_AMT_RX FROM JOB_PAYMENT WHERE JOB_ID=$job_id AND (OBSOLETE=$sqlFalse)";
			sql_execute($sql);
			while (($newArray = sql_fetch_assoc()) != false)
				$balances_closed[$job_id]['PAID'] += floatval($newArray['COL_AMT_RX']);
		}
	} # Balances for all jobs found in received-date range

	#dprint("balances_open: " . print_r($balances_open,1));#
	#dprint("balances_closed: " . print_r($balances_closed,1));#

	#dprint("in_sheets=" . print_r($in_sheets,1)); #
	# ---------------------------------------------------------------------------------------------

	# ---- Create the printable report data -------------------------------------------------------

	# Create a new set of sheets: $out_sheets
	# Each column of $out_sheets in an array where 'V' is the value and 'D' is the data type;
	#    data types: T=text(left-aligned), TR=text(right-aligned), I=int, F=float. Date and VilNo and SequenceNo will be TR.
	$out_sheets = array(); # indexed by CLIENT2_ID, one sheet per client
	foreach ($clients as $client2_id)
		$out_sheets[$client2_id] = array(); # for $multi_sheets: each sheet will have a list of dates; each date will be indexed by J_OPENED_DT (either day or month)
	$out_sheets['SINGLE'] = array(); # for !$multi_sheets: list of lines

	$headings = array(); # each sheet will have the same headings
	$init_headings = true;
	if (!$multi_sheet)
	{
		$headings[] = 'Client'; # client code
		$headings[] = 'Client Name';
	}

	# For each client:
	foreach ($in_sheets as $client2_id => $dates) # go through input sheets
	{
		# Working on one client

		$totals = array(); # each client has its own $totals
		$init_totals = true;
		# $hidden_totals: totals that are needed as inputs to other calculations but are not output on the screen or Excel
		$hidden_totals = array('V_J_COUNT' => 0, 'V_TOT_OWED' => 0.0, 'V_COLL_TOT_1' => 0.0, 'V_COLL_TOT_2' => 0.0);

		# The $year_xxx variables are only used if $year_subtotals is true (not the case if !$multi_sheets).
		#dprint("\$year_subtotals=$year_subtotals");#
		$year_prev = 0; # previous year from previous $date
		$year_curr = 0; # current year from $date
		$year_totals = array(); # various totals for current year
		$year_init = $year_subtotals; # whether to initialise $year_totals
		# $year_ht: hidden totals that are needed as inputs to other calculations but are not output on the screen or Excel
		$year_ht = array('V_J_COUNT' => 0, 'V_TOT_OWED' => 0.0, 'V_COLL_TOT_1' => 0.0, 'V_COLL_TOT_2' => 0.0);

		if (!$multi_sheet)
			$out_sheets['SINGLE'][$client2_id] = array(); # one line per client: list of fields

		foreach ($dates as $date => $date_info)
		{
			# --- NOTE: This chunk of code has be to roughly replicated after the end of the foreach($dates) loop! ----
			if ($year_subtotals)
			{
				$year_curr = substr($date, 0, strlen('yyyy')); # year on current line
				if (($year_prev != 0) && ($year_prev != $year_curr))
				{
					# Change of year - output subtotals for previous year
					if ($v_avg_debt)
						$year_totals['V_AVG_DEBT']['V'] =
							($year_ht['V_J_COUNT'] ? (floatval($year_ht['V_TOT_OWED']) / $year_ht['V_J_COUNT']) : 0.0);
					if ($v_coll_pcent_1)
						$year_totals['V_COLL_PCENT_1']['V'] =
							($year_ht['V_TOT_OWED'] ? (100.0 * floatval($year_ht['V_COLL_TOT_1']) / $year_ht['V_TOT_OWED']) : 0.0);
					if ($v_coll_pcent_2)
						$year_totals['V_COLL_PCENT_2']['V'] =
							($year_ht['V_TOT_OWED'] ? (100.0 * floatval($year_ht['V_COLL_TOT_2']) / $year_ht['V_TOT_OWED']) : 0.0);
					$out_sheets[$client2_id]["SUB_TOTAL_{$year_prev}_1"] =
						array(array('V' => "Sub totals for $year_prev:", 'D' => 'T'));
					$out_sheets[$client2_id]["SUB_TOTAL_{$year_prev}_2"] = $year_totals;
					$year_totals = array();
					$year_init = true;
					$year_ht = array('V_J_COUNT' => 0, 'V_TOT_OWED' => 0.0, 'V_COLL_TOT_1' => 0.0, 'V_COLL_TOT_2' => 0.0);
				}
			}
			# ----------------------------------------------------------------------------------------------------------

			$out_sheets[$client2_id][$date] = array(); # one line of output: date-info

			foreach ($fields as $fld)
			{
				switch ($fld)
				{
					case 'V_J_RX_DT':
						if ($multi_sheet)
						{
							if ($init_headings)
								$headings[] = 'Date Jobs Received';
							if ($init_totals)
								$totals['V_J_RX_DT'] = array('V' => 0, 'D' => 'X');
							if ($year_init)
								$year_totals['V_J_RX_DT'] = array('V' => '', 'D' => 'X');
							$out_sheets[$client2_id][$date][] = array('V' => date_for_sql($date, true, false), 'D' => 'TR');
							$totals['V_J_RX_DT']['V']++;
							if ($year_subtotals)
								$year_totals['V_J_RX_DT']['V']++;
						}
						break;
					case 'V_J_COUNT':
						if ($init_headings)
							$headings[] = 'Number of Jobs';
						if ($init_totals)
							$totals['V_J_COUNT'] = array('V' => 0, 'D' => 'I');
						if ($year_init)
							$year_totals['V_J_COUNT'] = array('V' => 0, 'D' => 'I');
						$temp = intval($date_info['V_J_COUNT']);
						$out_sheets[$client2_id][$date][] = array('V' => $temp, 'D' => 'I');
						$totals['V_J_COUNT']['V'] += $temp;
						if ($year_subtotals)
							$year_totals['V_J_COUNT']['V'] += $temp;
						break;
					case 'V_TOT_OWED':
						if ($init_headings)
							$headings[] = 'Total Amount Owed';
						if ($init_totals)
							$totals['V_TOT_OWED'] = array('V' => 0.0, 'D' => 'CU');
						if ($year_init)
							$year_totals['V_TOT_OWED'] = array('V' => 0.0, 'D' => 'CU');
						$temp = floatval($date_info['V_TOT_OWED']);
						$out_sheets[$client2_id][$date][] = array('V' => $temp, 'D' => 'CU');
						$totals['V_TOT_OWED']['V'] += $temp;
						if ($year_subtotals)
							$year_totals['V_TOT_OWED']['V'] += $temp;
						break;
					case 'V_AVG_DEBT':
						if ($init_headings)
							$headings[] = 'Average Debt Amount';
						if ($init_totals)
							$totals['V_AVG_DEBT'] = array('V' => 0.0, 'D' => 'CU');
						if ($year_init)
							$year_totals['V_AVG_DEBT'] = array('V' => 0.0, 'D' => 'CU');
						$out_sheets[$client2_id][$date][] =
							array('V' => ($date_info['V_J_COUNT'] ? (floatval($date_info['V_TOT_OWED']) / $date_info['V_J_COUNT']) : 0.0), 'D' => 'CU');
						break;
					case 'V_COLL_TOT_1':
						if ($init_headings)
							$headings[] = 'Total Amount Collected';
						if ($init_totals)
							$totals['V_COLL_TOT_1'] = array('V' => 0.0, 'D' => 'CU');
						if ($year_init)
							$year_totals['V_COLL_TOT_1'] = array('V' => 0.0, 'D' => 'CU');
						$temp = floatval($date_info['V_COLL_TOT_1']);
						$out_sheets[$client2_id][$date][] = array('V' => $temp, 'D' => 'CU');
						$totals['V_COLL_TOT_1']['V'] += $temp;
						if ($year_subtotals)
							$year_totals['V_COLL_TOT_1']['V'] += $temp;
						break;
					case 'V_COLL_PCENT_1':
						if ($init_headings)
							$headings[] = 'Perc. of Total Amt. Collected';
						if ($init_totals)
							$totals['V_COLL_PCENT_1'] = array('V' => 0.0, 'D' => 'PC');
						if ($year_init)
							$year_totals['V_COLL_PCENT_1'] = array('V' => 0.0, 'D' => 'PC');
						$out_sheets[$client2_id][$date][] =
							array('V' => ($date_info['V_TOT_OWED'] ? (100.0 * floatval($date_info['V_COLL_TOT_1']) / $date_info['V_TOT_OWED']) : 0.0), 'D' => 'PC');
						break;
					case 'V_COLL_DIR_1':
						if ($init_headings)
							$headings[] = 'Amount Collected (Direct)';
						if ($init_totals)
							$totals['V_COLL_DIR_1'] = array('V' => 0.0, 'D' => 'CU');
						if ($year_init)
							$year_totals['V_COLL_DIR_1'] = array('V' => 0.0, 'D' => 'CU');
						$temp = floatval($date_info['V_COLL_DIR_1']);
						$out_sheets[$client2_id][$date][] = array('V' => $temp, 'D' => 'CU');
						$totals['V_COLL_DIR_1']['V'] += $temp;
						if ($year_subtotals)
							$year_totals['V_COLL_DIR_1']['V'] += $temp;
						break;
					case 'V_COLL_VIL_1':
						if ($init_headings)
							$headings[] = 'Amount Collected (Vilcol)';
						if ($init_totals)
							$totals['V_COLL_VIL_1'] = array('V' => 0.0, 'D' => 'CU');
						if ($year_init)
							$year_totals['V_COLL_VIL_1'] = array('V' => 0.0, 'D' => 'CU');
						$temp = floatval($date_info['V_COLL_VIL_1']);
						$out_sheets[$client2_id][$date][] = array('V' => $temp, 'D' => 'CU');
						$totals['V_COLL_VIL_1']['V'] += $temp;
						if ($year_subtotals)
							$year_totals['V_COLL_VIL_1']['V'] += $temp;
						break;
					case 'V_COLL_FWD_1':
						if ($init_headings)
							$headings[] = 'Amount Collected (Forwd)';
						if ($init_totals)
							$totals['V_COLL_FWD_1'] = array('V' => 0.0, 'D' => 'CU');
						if ($year_init)
							$year_totals['V_COLL_FWD_1'] = array('V' => 0.0, 'D' => 'CU');
						$temp = floatval($date_info['V_COLL_FWD_1']);
						$out_sheets[$client2_id][$date][] = array('V' => $temp, 'D' => 'CU');
						$totals['V_COLL_FWD_1']['V'] += $temp;
						if ($year_subtotals)
							$year_totals['V_COLL_FWD_1']['V'] += $temp;
						break;
					case 'V_RET_TOT_1':
						if ($init_headings)
							$headings[] = 'Amount Returned';
						if ($init_totals)
							$totals['V_RET_TOT_1'] = array('V' => 0.0, 'D' => 'CU');
						if ($year_init)
							$year_totals['V_RET_TOT_1'] = array('V' => 0.0, 'D' => 'CU');
						$temp = floatval($date_info['V_RET_TOT_1']);
						$out_sheets[$client2_id][$date][] = array('V' => $temp, 'D' => 'CU');
						$totals['V_RET_TOT_1']['V'] += $temp;
						if ($year_subtotals)
							$year_totals['V_RET_TOT_1']['V'] += $temp;
						break;
					case 'V_OUT_OP':
						if ($init_headings)
							$headings[] = 'Amount still Outst. (Open jobs)';
						if ($init_totals)
							$totals['V_OUT_OP'] = array('V' => 0.0, 'D' => 'CU');
						if ($year_init)
							$year_totals['V_OUT_OP'] = array('V' => 0.0, 'D' => 'CU');
						$temp = floatval($date_info['V_OUT_OP']);
						$out_sheets[$client2_id][$date][] = array('V' => $temp, 'D' => 'CU');
						$totals['V_OUT_OP']['V'] += $temp;
						if ($year_subtotals)
							$year_totals['V_OUT_OP']['V'] += $temp;
						break;
					case 'V_COLL_TOT_2':
						if ($init_headings)
							$headings[] = 'Amt. Col. in Rep. Per. (Total)';
						if ($init_totals)
							$totals['V_COLL_TOT_2'] = array('V' => 0.0, 'D' => 'CU');
						if ($year_init)
							$year_totals['V_COLL_TOT_2'] = array('V' => 0.0, 'D' => 'CU');
						$temp = floatval($date_info['V_COLL_TOT_2']);
						$out_sheets[$client2_id][$date][] = array('V' => $temp, 'D' => 'CU');
						$totals['V_COLL_TOT_2']['V'] += $temp;
						if ($year_subtotals)
							$year_totals['V_COLL_TOT_2']['V'] += $temp;
						break;
					case 'V_COLL_PCENT_2':
						if ($init_headings)
							$headings[] = 'Perc. col. in Rep. Per.';
						if ($init_totals)
							$totals['V_COLL_PCENT_2'] = array('V' => 0.0, 'D' => 'PC');
						if ($year_init)
							$year_totals['V_COLL_PCENT_2'] = array('V' => 0.0, 'D' => 'PC');
						$out_sheets[$client2_id][$date][] =
							array('V' => ($date_info['V_TOT_OWED'] ? (100.0 * floatval($date_info['V_COLL_TOT_2']) / $date_info['V_TOT_OWED']) : 0.0), 'D' => 'PC');
						break;
					case 'V_COLL_DIR_2':
						if ($init_headings)
							$headings[] = 'Amt. Col. in Rep. Per. (Dir.)';
						if ($init_totals)
							$totals['V_COLL_DIR_2'] = array('V' => 0.0, 'D' => 'CU');
						if ($year_init)
							$year_totals['V_COLL_DIR_2'] = array('V' => 0.0, 'D' => 'CU');
						$temp = floatval($date_info['V_COLL_DIR_2']);
						$out_sheets[$client2_id][$date][] = array('V' => $temp, 'D' => 'CU');
						$totals['V_COLL_DIR_2']['V'] += $temp;
						if ($year_subtotals)
							$year_totals['V_COLL_DIR_2']['V'] += $temp;
						break;
					case 'V_COLL_VIL_2':
						if ($init_headings)
							$headings[] = 'Amt. Col. in Rep. Per. (Vil.)';
						if ($init_totals)
							$totals['V_COLL_VIL_2'] = array('V' => 0.0, 'D' => 'CU');
						if ($year_init)
							$year_totals['V_COLL_VIL_2'] = array('V' => 0.0, 'D' => 'CU');
						$temp = floatval($date_info['V_COLL_VIL_2']);
						$out_sheets[$client2_id][$date][] = array('V' => $temp, 'D' => 'CU');
						$totals['V_COLL_VIL_2']['V'] += $temp;
						if ($year_subtotals)
							$year_totals['V_COLL_VIL_2']['V'] += $temp;
						break;
					case 'V_COLL_FWD_2':
						if ($init_headings)
							$headings[] = 'Amt. Col. in Rep. Per. (For.)';
						if ($init_totals)
							$totals['V_COLL_FWD_2'] = array('V' => 0.0, 'D' => 'CU');
						if ($year_init)
							$year_totals['V_COLL_FWD_2'] = array('V' => 0.0, 'D' => 'CU');
						$temp = floatval($date_info['V_COLL_FWD_2']);
						$out_sheets[$client2_id][$date][] = array('V' => $temp, 'D' => 'CU');
						$totals['V_COLL_FWD_2']['V'] += $temp;
						if ($year_subtotals)
							$year_totals['V_COLL_FWD_2']['V'] += $temp;
						break;
					case 'V_RET_TOT_2':
						if ($init_headings)
							$headings[] = 'Amt. Ret. in each rep. per.';
						if ($init_totals)
							$totals['V_RET_TOT_2'] = array('V' => 0.0, 'D' => 'CU');
						if ($year_init)
							$year_totals['V_RET_TOT_2'] = array('V' => 0.0, 'D' => 'CU');
						$temp = floatval($date_info['V_RET_TOT_2']);
						$out_sheets[$client2_id][$date][] = array('V' => $temp, 'D' => 'CU');
						$totals['V_RET_TOT_2']['V'] += $temp;
						if ($year_subtotals)
							$year_totals['V_RET_TOT_2']['V'] += $temp;
						break;
					case 'V_CO_PF_N':
						if ($init_headings)
							$headings[] = 'Jobs C/O – paid in full (no.)';
						if ($init_totals)
							$totals['V_CO_PF_N'] = array('V' => 0, 'D' => 'I');
						if ($year_init)
							$year_totals['V_CO_PF_N'] = array('V' => 0, 'D' => 'I');
						$count = 0;
						foreach ($date_info['JOBS'] as $job_id)
						{
							if (array_key_exists($job_id, $balances_closed))
							{
								$balance = $balances_closed[$job_id]['PAID'] - $balances_closed[$job_id]['OWED'];
								if (0 <= $balance)
									$count++;
							}
						}
						$out_sheets[$client2_id][$date][] = array('V' => $count, 'D' => 'I');
						$totals['V_CO_PF_N']['V'] += $count;
						if ($year_subtotals)
							$year_totals['V_CO_PF_N']['V'] += $count;
						break;
					case 'V_CO_PF_V':
						if ($init_headings)
							$headings[] = 'Jobs C/O – paid in full (val.)';
						if ($init_totals)
							$totals['V_CO_PF_V'] = array('V' => 0.0, 'D' => 'CU');
						if ($year_init)
							$year_totals['V_CO_PF_V'] = array('V' => 0.0, 'D' => 'CU');
						$value = 0.0;
						foreach ($date_info['JOBS'] as $job_id)
						{
							if (array_key_exists($job_id, $balances_closed))
							{
								$balance = $balances_closed[$job_id]['PAID'] - $balances_closed[$job_id]['OWED'];
								if (0 <= $balance)
									$value += $balances_closed[$job_id]['OWED'];
							}
						}
						$out_sheets[$client2_id][$date][] = array('V' => $value, 'D' => 'CU');
						$totals['V_CO_PF_V']['V'] += $value;
						if ($year_subtotals)
							$year_totals['V_CO_PF_V']['V'] += $value;
						break;
					case 'V_CO_PP_N':
						if ($init_headings)
							$headings[] = 'Jobs C/O – some pay (no.)';
						if ($init_totals)
							$totals['V_CO_PP_N'] = array('V' => 0, 'D' => 'I');
						if ($year_init)
							$year_totals['V_CO_PP_N'] = array('V' => 0, 'D' => 'I');
						$count = 0;
						foreach ($date_info['JOBS'] as $job_id)
						{
							if (array_key_exists($job_id, $balances_closed))
							{
								if (0 < $balances_closed[$job_id]['PAID'])
								{
									$balance = $balances_closed[$job_id]['PAID'] - $balances_closed[$job_id]['OWED'];
									if ($balance < 0)
										$count++;
								}
							}
						}
						$out_sheets[$client2_id][$date][] = array('V' => $count, 'D' => 'I');
						$totals['V_CO_PP_N']['V'] += $count;
						if ($year_subtotals)
							$year_totals['V_CO_PP_N']['V'] += $count;
						break;
					case 'V_CO_PP_V':
						if ($init_headings)
							$headings[] = 'Jobs C/O – some pay (val.)';
						if ($init_totals)
							$totals['V_CO_PP_V'] = array('V' => 0.0, 'D' => 'CU');
						if ($year_init)
							$year_totals['V_CO_PP_V'] = array('V' => 0.0, 'D' => 'CU');
						$value = 0.0;
						foreach ($date_info['JOBS'] as $job_id)
						{
							if (array_key_exists($job_id, $balances_closed))
							{
								if (0 < $balances_closed[$job_id]['PAID'])
								{
									$balance = $balances_closed[$job_id]['PAID'] - $balances_closed[$job_id]['OWED'];
									if ($balance < 0)
										$value += $balances_closed[$job_id]['OWED'];
								}
							}
						}
						$out_sheets[$client2_id][$date][] = array('V' => $value, 'D' => 'CU');
						$totals['V_CO_PP_V']['V'] += $value;
						if ($year_subtotals)
							$year_totals['V_CO_PP_V']['V'] += $value;
						break;
					case 'V_CO_NP_N':
						if ($init_headings)
							$headings[] = 'Jobs C/O – no pay (no.)';
						if ($init_totals)
							$totals['V_CO_NP_N'] = array('V' => 0, 'D' => 'I');
						if ($year_init)
							$year_totals['V_CO_NP_N'] = array('V' => 0, 'D' => 'I');
						$count = 0;
						foreach ($date_info['JOBS'] as $job_id)
						{
							if (array_key_exists($job_id, $balances_closed))
							{
								if ($balances_closed[$job_id]['PAID'] <= 0)
									$count++;
							}
						}
						$out_sheets[$client2_id][$date][] = array('V' => $count, 'D' => 'I');
						$totals['V_CO_NP_N']['V'] += $count;
						if ($year_subtotals)
							$year_totals['V_CO_NP_N']['V'] += $count;
						break;
					case 'V_CO_NP_V':
						if ($init_headings)
							$headings[] = 'Jobs C/O – no pay (val.)';
						if ($init_totals)
							$totals['V_CO_NP_V'] = array('V' => 0.0, 'D' => 'CU');
						if ($year_init)
							$year_totals['V_CO_NP_V'] = array('V' => 0.0, 'D' => 'CU');
						$value = 0.0;
						foreach ($date_info['JOBS'] as $job_id)
						{
							if (array_key_exists($job_id, $balances_closed))
							{
								if ($balances_closed[$job_id]['PAID'] <= 0)
									$value += $balances_closed[$job_id]['OWED'];
							}
						}
						$out_sheets[$client2_id][$date][] = array('V' => $value, 'D' => 'CU');
						$totals['V_CO_NP_V']['V'] += $value;
						if ($year_subtotals)
							$year_totals['V_CO_NP_V']['V'] += $value;
						break;
					case 'V_OP_PP_N':
						if ($init_headings)
							$headings[] = 'Open jobs some col. (no.)';
						if ($init_totals)
							$totals['V_OP_PP_N'] = array('V' => 0, 'D' => 'I');
						if ($year_init)
							$year_totals['V_OP_PP_N'] = array('V' => 0, 'D' => 'I');
						$count = 0;
						foreach ($date_info['JOBS'] as $job_id)
						{
							if (array_key_exists($job_id, $balances_open))
							{
								if (0 < $balances_open[$job_id]['PAID'])
									$count++;
							}
						}
						$out_sheets[$client2_id][$date][] = array('V' => $count, 'D' => 'I');
						$totals['V_OP_PP_N']['V'] += $count;
						if ($year_subtotals)
							$year_totals['V_OP_PP_N']['V'] += $count;
						break;
					case 'V_OP_PP_V':
						if ($init_headings)
							$headings[] = 'Open jobs some col. (val.)';
						if ($init_totals)
							$totals['V_OP_PP_V'] = array('V' => 0.0, 'D' => 'CU');
						if ($year_init)
							$year_totals['V_OP_PP_V'] = array('V' => 0.0, 'D' => 'CU');
						$value = 0.0;
						foreach ($date_info['JOBS'] as $job_id)
						{
							if (array_key_exists($job_id, $balances_open))
							{
								if (0 < $balances_open[$job_id]['PAID'])
									$value += $balances_open[$job_id]['OWED'];
							}
						}
						$out_sheets[$client2_id][$date][] = array('V' => $value, 'D' => 'CU');
						$totals['V_OP_PP_V']['V'] += $value;
						if ($year_subtotals)
							$year_totals['V_OP_PP_V']['V'] += $value;
						break;
					case 'V_OP_NP_N':
						if ($init_headings)
							$headings[] = 'Open jobs with no col. (no.)';
						if ($init_totals)
							$totals['V_OP_NP_N'] = array('V' => 0, 'D' => 'I');
						if ($year_init)
							$year_totals['V_OP_NP_N'] = array('V' => 0, 'D' => 'I');
						$count = 0;
						foreach ($date_info['JOBS'] as $job_id)
						{
							if (array_key_exists($job_id, $balances_open))
							{
								if ($balances_open[$job_id]['PAID'] <= 0)
									$count++;
							}
						}
						$out_sheets[$client2_id][$date][] = array('V' => $count, 'D' => 'I');
						$totals['V_OP_NP_N']['V'] += $count;
						if ($year_subtotals)
							$year_totals['V_OP_NP_N']['V'] += $count;
						break;
					case 'V_OP_NP_V':
						if ($init_headings)
							$headings[] = 'Open jobs with no col. (val.)';
						if ($init_totals)
							$totals['V_OP_NP_V'] = array('V' => 0.0, 'D' => 'CU');
						if ($year_init)
							$year_totals['V_OP_NP_V'] = array('V' => 0.0, 'D' => 'CU');
						$value = 0.0;
						foreach ($date_info['JOBS'] as $job_id)
						{
							if (array_key_exists($job_id, $balances_open))
							{
								if ($balances_open[$job_id]['PAID'] <= 0)
									$value += $balances_open[$job_id]['OWED'];
							}
						}
						$out_sheets[$client2_id][$date][] = array('V' => $value, 'D' => 'CU');
						$totals['V_OP_NP_V']['V'] += $value;
						if ($year_subtotals)
							$year_totals['V_OP_NP_V']['V'] += $value;
						break;
					case 'V_TC_NTRC':
						if ($init_headings)
							$headings[] = 'T/C jobs with no trace (no.)';
						if ($init_totals)
							$totals['V_TC_NTRC'] = array('V' => 0, 'D' => 'I');
						if ($year_init)
							$year_totals['V_TC_NTRC'] = array('V' => 0, 'D' => 'I');
						$count = 0;
						foreach ($date_info['JOBS'] as $job_id)
						{
							if (array_key_exists($job_id, $jobs_tc))
							{
								if ($jobs_tc[$job_id]['LINKED_COUNT'] == 0)
									$count++;
							}
						}
						$out_sheets[$client2_id][$date][] = array('V' => $count, 'D' => 'I');
						$totals['V_TC_NTRC']['V'] += $count;
						if ($year_subtotals)
							$year_totals['V_TC_NTRC']['V'] += $count;
						break;
					case 'V_RCR_N':
						if ($init_headings)
							$headings[] = 'RCR jobs (number)';
						if ($init_totals)
							$totals['V_RCR_N'] = array('V' => 0, 'D' => 'I');
						if ($year_init)
							$year_totals['V_RCR_N'] = array('V' => 0, 'D' => 'I');
						$count = 0;
						foreach ($date_info['JOBS'] as $job_id)
						{
							if (array_key_exists($job_id, $jobs_rcr))
								$count++;
						}
						$out_sheets[$client2_id][$date][] = array('V' => $count, 'D' => 'I');
						$totals['V_RCR_N']['V'] += $count;
						if ($year_subtotals)
							$year_totals['V_RCR_N']['V'] += $count;
						break;
					case 'V_RCR_V':
						if ($init_headings)
							$headings[] = 'RCR jobs (value)';
						if ($init_totals)
							$totals['V_RCR_V'] = array('V' => 0.0, 'D' => 'CU');
						if ($year_init)
							$year_totals['V_RCR_V'] = array('V' => 0.0, 'D' => 'CU');
						$value = 0.0;
						foreach ($date_info['JOBS'] as $job_id)
						{
							if (array_key_exists($job_id, $jobs_rcr))
								$value += $jobs_rcr[$job_id];
						}
						$out_sheets[$client2_id][$date][] = array('V' => $value, 'D' => 'CU');
						$totals['V_RCR_V']['V'] += $value;
						if ($year_subtotals)
							$year_totals['V_RCR_V']['V'] += $value;
						break;
					default:
						break;
				}
			} # foreach ($fields)
			$init_headings = false;
			$init_totals = false;
			$year_init = false;

			if ($v_j_count)
				$hidden_totals['V_J_COUNT'] += intval($date_info['V_J_COUNT']);
			if ($v_tot_owed)
				$hidden_totals['V_TOT_OWED'] += floatval($date_info['V_TOT_OWED']);
			if ($v_coll_tot_1)
				$hidden_totals['V_COLL_TOT_1'] += floatval($date_info['V_COLL_TOT_1']);
			if ($v_coll_tot_2)
				$hidden_totals['V_COLL_TOT_2'] += floatval($date_info['V_COLL_TOT_2']);

			if ($year_subtotals)
			{
				if ($v_j_count)
					$year_ht['V_J_COUNT'] += intval($date_info['V_J_COUNT']);
				if ($v_tot_owed)
					$year_ht['V_TOT_OWED'] += floatval($date_info['V_TOT_OWED']);
				if ($v_coll_tot_1)
					$year_ht['V_COLL_TOT_1'] += floatval($date_info['V_COLL_TOT_1']);
				if ($v_coll_tot_2)
					$year_ht['V_COLL_TOT_2'] += floatval($date_info['V_COLL_TOT_2']);

				$year_prev = $year_curr; # when we look at next line, this is the year on the previous line
			}

		} # foreach ($dates)

		# --- NOTE: This chunk of code is a rough replica from the top of the foreach($dates) loop! ----
		if ($year_subtotals)
		{
			# End of year - output subtotals for that year ($year_prev is the same as $year_curr now)
			if ($v_avg_debt)
				$year_totals['V_AVG_DEBT']['V'] =
					($year_ht['V_J_COUNT'] ? (floatval($year_ht['V_TOT_OWED']) / $year_ht['V_J_COUNT']) : 0.0);
			if ($v_coll_pcent_1)
				$year_totals['V_COLL_PCENT_1']['V'] =
				($year_ht['V_TOT_OWED'] ? (100.0 * floatval($year_ht['V_COLL_TOT_1']) / $year_ht['V_TOT_OWED']) : 0.0);
			if ($v_coll_pcent_2)
				$year_totals['V_COLL_PCENT_2']['V'] =
				($year_ht['V_TOT_OWED'] ? (100.0 * floatval($year_ht['V_COLL_TOT_2']) / $year_ht['V_TOT_OWED']) : 0.0);
			$out_sheets[$client2_id]["SUB_TOTAL_{$year_prev}_1"] =
				array(array('V' => "Sub totals for $year_prev:", 'D' => 'T'));
			$out_sheets[$client2_id]["SUB_TOTAL_{$year_prev}_2"] = $year_totals;
		}
		# -----------------------------------------------------------------------------------------------

		if ($v_avg_debt)
			$totals['V_AVG_DEBT']['V'] = ($hidden_totals['V_J_COUNT'] ?
											(floatval($hidden_totals['V_TOT_OWED']) / $hidden_totals['V_J_COUNT']) : 0.0);
		if ($v_coll_pcent_1)
			$totals['V_COLL_PCENT_1']['V'] = ($hidden_totals['V_TOT_OWED'] ?
											(100.0 * floatval($hidden_totals['V_COLL_TOT_1']) / $hidden_totals['V_TOT_OWED']) : 0.0);
		if ($v_coll_pcent_2)
			$totals['V_COLL_PCENT_2']['V'] = ($hidden_totals['V_TOT_OWED'] ?
											(100.0 * floatval($hidden_totals['V_COLL_TOT_2']) / $hidden_totals['V_TOT_OWED']) : 0.0);
		$out_sheets[$client2_id]['TOTALS_1'] = array(array('V' => 'TOTALS:', 'D' => 'T'));
		if (count($totals) == 0)
			$totals[] = array('V' => 0, 'D' => 'X');
		$out_sheets[$client2_id]['TOTALS_2'] = $totals;

		if (!$multi_sheet)
		{
			# All fields for this client's line
			$out_sheets['SINGLE'][$client2_id][] = array('V' => $client_names[$client2_id]['C_CODE'], 'D' => 'TR');
			$out_sheets['SINGLE'][$client2_id][] = array('V' => $client_names[$client2_id]['C_CO_NAME'], 'D' => 'T');
			foreach ($totals as $fld)
				$out_sheets['SINGLE'][$client2_id][] = $fld;
		}

	} # for each client ($in_sheets)

	# ---------------------------------------------------------------------------------------------

	# ---- Output the data to the screen and/or Excel ---------------------------------------------

	if (($dest == 's') || ($dest == 'x'))
	{

		$screen = "
		<p style=\"font-size:18px; font-weight:bold;\">
			Report {$report['REP_NAME']}
			&nbsp;&nbsp;&nbsp;&nbsp;
			" . input_button('Export to Excel', "export_xl()", '', 'but_export_xl') . "
		</p>
		";

		foreach ($out_sheets as $client2_id => $datalines)
		{
			if ($multi_sheet)
			{
				if ($client2_id == 'SINGLE')
					continue; # this sheet is the single-sheet which we don't want to output
			}
			else
			{
				if ($client2_id != 'SINGLE')
					continue; # this sheet is one of the multi-sheets which we don't want to output
			}

			if ($multi_sheet)
			{
				$screen .= "
				<p style=\"font-size:15px; font-weight:bold;\">
					Client: {$client_names[$client2_id]['C_CODE']}, {$client_names[$client2_id]['C_CO_NAME']}
				</p>
				";
			}
			$screen .= "
			<table class=\"spaced_table\">
			<tr>
				";
				foreach ($headings as $head)
					$screen .= "<th>$head</th>";
				$screen .= "
			</tr>
			";
			foreach ($datalines as $dline)
			{
				$screen .= "
				<tr>
				";
				foreach ($dline as $fld)
				{
					$align = '';
					if (array_key_exists('D', $fld))
					{
						if ($fld['D'] == 'T')
							$fld = $fld['V'];
						elseif ($fld['D'] == 'TR')
						{
							$align = $ar;
							$fld = $fld['V'];
						}
						elseif ($fld['D'] == 'I')
						{
							$align = $ar;
							$fld = number_with_commas($fld['V'], true, false);
						}
						elseif ($fld['D'] == 'F')
						{
							$align = $ar;
							$fld = number_with_commas($fld['V'], true, true);
						}
						elseif ($fld['D'] == 'CU')
						{
							$align = $ar;
							$fld = money_format_kdb($fld['V'], true, true, true);
						}
						elseif ($fld['D'] == 'PC')
						{
							$align = $ar;
							$fld = round($fld['V'], 2) . "%";
						}
						elseif ($fld['D'] == 'X')
						{
							$align = $ac;
							$fld = '-';
						}
						else
							dprint("***ERROR*** fld['D'] is not one of T/TR/I/F/CU/PC: \"{$fld['D']}\"");
					}
					else
						dprint("***ERROR*** fld has no 'D' element: \"$fld\" or " . print_r($fld,1));
					$screen .= "
					<td $align>$fld</td>
					";
				}
				$screen .= "
				</tr>
				";
			}
			$screen .= "
			</table>
			";
		} # foreach ($out_sheets)

		print $screen;

		if ($dest == 'x')
		{
			$xl_sheets = array();
			foreach ($out_sheets as $client2_id => $datalines_vd)
			{
				if ($multi_sheet)
				{
					if ($client2_id == 'SINGLE')
						continue; # this sheet is the single-sheet which we don't want to output

					$title_short = $client_names[$client2_id]['C_CO_NAME'];
					$title_long = "{$report['REP_NAME']} for client: " .
							"{$client_names[$client2_id]['C_CODE']}, {$client_names[$client2_id]['C_CO_NAME']}";
				}
				else
				{
					if ($client2_id != 'SINGLE')
						continue; # this sheet is one of the multi-sheets which we don't want to output

					$title_short = "All Clients";
					$title_long = $report['REP_NAME'];
				}

				$top_lines = array_merge(array(array($title_long)), array(array()));
				$formats = array();

				$datalines = array();
				foreach ($datalines_vd as $old_line)
				{
					$new_line = array();
					foreach ($old_line as $fld)
						$new_line[] = (($fld['D'] == 'X') ? '' : $fld['V']);
					$datalines[] = $new_line;
				}

				$xl_sheets[] = array($title_short, $top_lines, $headings, $datalines, $formats, '');
			}

			phpExcel_output($reps_subdir, $xfile, $xl_sheets); # library.php

			# Auto-download file to PC
			print "
			<script type=\"text/javascript\">
				document.form_csv_download.short_fname.value = '$xfile.$phpExcel_ext';
				document.form_csv_download.full_fname.value = '$csv_path' + '{$reps_subdir}/$xfile.$phpExcel_ext';
				document.form_csv_download.submit();
			</script>
			";
		}
	} # $dest 's' or 'x'
	else
		dlog("$this_function: *=* bad dest \"$dest\"");
	# ---------------------------------------------------------------------------------------------

} # run_ana_col()

function run_jobdet_trc($report, $run_rx_dt_fr_sql, $run_rx_dt_to_plus1_sql, $dest, $xfile='')
{
	# Run report: Job-Detail Trace,
	# both Multi-sheet (each client on a separate sheet),
	# and Single-sheet (all clients on the same sheet).
	# Output to screen and maybe to Excel too ($dest = 's' or 'x').

	# This report is virtually the same as the Job-Detail Collect report, apart from the choice of fields available to the user.
	run_jobdet_gen($report, $run_rx_dt_fr_sql, $run_rx_dt_to_plus1_sql, '', '', $dest, $xfile, 'T');

} # run_jobdet_trc()

function run_jobdet_col($report, $run_rx_dt_fr_sql, $run_rx_dt_to_plus1_sql, $run_p1_dt_fr_sql, $run_p1_dt_to_plus1_sql, $dest, $xfile='')
{
	# Run report: Job-Detail Collection,
	# both Multi-sheet (each client on a separate sheet),
	# and Single-sheet (all clients on the same sheet).
	# Output to screen and maybe to Excel too ($dest = 's' or 'x').

	# This report is virtually the same as the Job-Detail Trace report, apart from the choice of fields available to the user.
	run_jobdet_gen($report, $run_rx_dt_fr_sql, $run_rx_dt_to_plus1_sql, $run_p1_dt_fr_sql, $run_p1_dt_to_plus1_sql, $dest, $xfile, 'C');
}

function run_jobdet_gen($report, $run_rx_dt_fr_sql, $run_rx_dt_to_plus1_sql, $run_p1_dt_fr_sql, $run_p1_dt_to_plus1_sql, $dest, $xfile, $sys)
{
	# Called from run_jobdet_trc() or run_jobdet_col()

	global $ac; # settings.php
	global $all_emails; # created here; used by get_any_email()
	#global $add_phones; # created here; used by get_add_phone()
	global $all_phones; # created here; used by get_any_phone()
	global $ar; # settings.php
	global $csv_path; # settings.php
	global $excel_debug;
	global $id_LETTER_TYPE_trace_no; # settings.php
	global $id_LETTER_TYPE_trace_yes; # settings.php
	global $id_LETTER_TYPE_means_no; # settings.php
	global $id_LETTER_TYPE_means_yes; # settings.php
	global $id_LETTER_TYPE_repo_no; # settings.php
	global $id_LETTER_TYPE_repo_yes; # settings.php
	global $id_LETTER_TYPE_other_no; # settings.php
	global $id_LETTER_TYPE_other_yes; # settings.php
	global $id_LETTER_TYPE_serv_no; # settings.php
	global $id_LETTER_TYPE_serv_yes; # settings.php
	global $id_LETTER_TYPE_retrc1_no; # settings.php
	global $id_LETTER_TYPE_retrc1_yes; # settings.php
	global $id_LETTER_TYPE_retrc1_foc; # settings.php
	global $id_LETTER_TYPE_retrc2_no; # settings.php
	global $id_LETTER_TYPE_retrc2_yes; # settings.php
	global $id_LETTER_TYPE_retrc2_foc; # settings.php
	global $id_LETTER_TYPE_retrc3_no; # settings.php
	global $id_LETTER_TYPE_retrc3_yes; # settings.php
	global $id_LETTER_TYPE_retrc3_foc; # settings.php
	global $id_LETTER_TYPE_tc_no; # settings.php
	global $id_LETTER_TYPE_tc_yes; # settings.php
	global $id_LETTER_TYPE_tm_no; # settings.php
	global $id_LETTER_TYPE_tm_yes; # settings.php
	global $id_LETTER_TYPE_attend_no; # settings.php
	global $id_LETTER_TYPE_attend_yes; # settings.php
	global $id_LETTER_TYPE_letter_1; # settings.php
	global $id_LETTER_TYPE_letter_2; # settings.php
	global $id_LETTER_TYPE_letter_3; # settings.php
	global $id_LETTER_TYPE_contact; # settings.php
	global $id_LETTER_TYPE_demand; # settings.php
	global $id_ROUTE_cspent; # settings.php
	global $id_ROUTE_direct; # settings.php
	global $id_ROUTE_fwd; # settings.php
	global $id_ROUTE_tous; # settings.php
	#global $job_id; # created here; used by get_add_phone()
	global $job_id; # created here; used by get_any_phone()
	global $phpExcel_ext; # settings.php: "xls"
	global $sqlFalse; # settings.php
	global $sqlTrue; # settings.php
	global $reps_subdir;

	$debug = false;#
	#$debug = global_debug();#
	$excel_debug = $debug; # used by library.php / phpExcel_output()

	$collect = ($report['REP_COLLECT'] ? true : false);
	$report_system = ($collect ? 'Collect' : 'Trace');
	$this_function = "run_jobdet_gen([{$report['REPORT_ID']}], " .
		"$run_rx_dt_fr_sql, $run_rx_dt_to_plus1_sql, $run_p1_dt_fr_sql, $run_p1_dt_to_plus1_sql, $dest, $xfile, $sys) [$report_system system]";
	if ($debug) dlog($this_function);

	# ---- Sanity checks --------------------------------------------------------------------------

	$clients = sql_get_clients_for_report($report['REPORT_ID']); # list of CLIENT2.CLIENT2_ID
	if (!$clients)
	{
		dprint("$this_function: No Clients!!", true);
		return;
	}

	$fields = sql_get_fields_for_report($report['REPORT_ID']); # list of REPORT_FIELD_SD.RF_CODE
	if (!$fields)
	{
		dprint("$this_function: No Fields!!", true);
		return;
	}
	if ($debug) dlog("run_jobdet_gen(): fields=" . print_r($fields,1));

	if (($dest == 'x') && (!$xfile))
	{
		dprint("$this_function: No Excel filename!!", true);
		return;
	}

	$multi_sheet = ($report['CLIENT_PER_SHEET'] ? true : false);
	# ---------------------------------------------------------------------------------------------

	# ---- Establish what input data we need ------------------------------------------------------

	$select = array('J.CLIENT2_ID', 'J.JOB_ID', 'J.J_OPENED_DT');
	$encrypted_f = array(); # those elements (fields) of $select that are encypted in the database
	$encrypted_t = array(); # database table names for fields in $encrypted_f

	$v_vilno = false;
	$v_cliref = false;
	$v_dt_rx = false;
	$v_lname = false;
	$v_fname = false;
	$v_title = false;
	$v_comp = false;
	$v_jtype = false;
	$v_success = false;
	$v_status = false; # JOB.JC_JOB_STATUS_ID -> JOB_STATUS_SD.J_STATUS
	$v_owed = false;
	$v_collected = false;
	$v_outst = false;
	$v_cspent = false;
	$v_num_pay = false;
	$v_ret_total = false;
	$v_col_total = false; # in period
	$v_col_dir = false; # in period
	$v_col_tov = false; # in period
	$v_col_for = false; # in period
	$v_ret_per = false; # in period
	$v_paid_full = false;
	$v_pay_last = false;
	$v_closed = false;
	$v_home_1 = false;
	$v_home_2 = false;
	$v_home_3 = false;
	$v_home_4 = false;
	$v_home_5 = false;
	$v_home_pc = false;
	$v_home_t = false;
	$v_prop = false;
	$v_l1_s = false;
	$v_l1_dt = false;
	$v_l2_s = false;
	$v_l2_dt = false;
	$v_l3_s = false;
	$v_l3_dt = false;
	$v_lc_s = false;
	$v_lc_dt = false;
	$v_ld_s = false;
	$v_ld_dt = false;
	$v_rep_last = false;
	$v_upd_last = false;
	$v_reg_am = false;
	$v_reg_int = false;
	$v_reg_start = false;
	$v_reg_meth = false;
	$v_last_amt = false;
	$v_next_dt = false;
	$v_l_next = false;
	$v_diary = false;
	$v_email = false;
	$v_new_1 = false;
	$v_new_2 = false;
	$v_new_3 = false;
	$v_new_4 = false;
	$v_new_5 = false;
	$v_ls_t = false;
	$v_ld_t = false;
	$v_ls_m = false;
	$v_ld_m = false;
	$v_ls_rp = false;
	$v_ld_rp = false;
	$v_ls_ot = false;
	$v_ld_ot = false;
	$v_ls_sv = false;
	$v_ld_sv = false;
	$v_ls_r1 = false;
	$v_ld_r1 = false;
	$v_ls_r2 = false;
	$v_ld_r2 = false;
	$v_ls_r3 = false;
	$v_ld_r3 = false;
	$v_ls_tc = false;
	$v_ld_tc = false;
	$v_ls_tm = false;
	$v_ld_tm = false;
	$v_ls_at = false;
	$v_ld_at = false;
	$v_cli_id = false;
	$v_seq = false;
	$v_user = false;
	$v_dt_start = false;
	$v_dt_end = false;
	$v_ap_n1 = false;
	$v_ap_d1 = false;
	$v_ap_n2 = false;
	$v_ap_d2 = false;
	$v_ap_n3 = false;
	$v_ap_d3 = false;
	$v_ap_n4 = false;
	$v_ap_d4 = false;
	$v_ap_n5 = false;
	$v_ap_d5 = false;
	$v_tdx_id = false;
	$v_tdx_as = false;
	$v_tdx_pp = false;
	$v_tdx_client = false;
	$reason_debt = false;

	foreach ($fields as $fld)
	{
		switch ($fld)
		{
			# The following fields are in the same order as the table in the spec (section 9.11 "Analysis Reports")
			case 'V_DT_RX':
				# J.J_OPENED_DT is always included in the SELECT but only in the output if the user selects it
				$v_dt_rx = true;
				break;
			case 'V_VILNO':
				$v_vilno = true;
				if (!in_array('J.J_VILNO', $select))
					$select[] = 'J.J_VILNO';
				break;
			case 'V_CLIREF':
				$v_cliref = true;
				if (!in_array('J.CLIENT_REF', $select))
				{
					$select[] = 'J.CLIENT_REF';
					$encrypted_f[] = 'J.CLIENT_REF';
					if (!in_array('JOB', $encrypted_t))
						$encrypted_t[] = 'JOB';
				}
				break;
			case 'V_LNAME':
				$v_lname = true;
				if (!in_array('S.JS_LASTNAME', $select))
				{
					$select[] = 'S.JS_LASTNAME';
					$encrypted_f[] = 'S.JS_LASTNAME';
					if (!in_array('JOB_SUBJECT', $encrypted_t))
						$encrypted_t[] = 'JOB_SUBJECT';
				}
				break;
			case 'V_FNAME':
				$v_fname = true;
				if (!in_array('S.JS_FIRSTNAME', $select))
				{
					$select[] = 'S.JS_FIRSTNAME';
					$encrypted_f[] = 'S.JS_FIRSTNAME';
					if (!in_array('JOB_SUBJECT', $encrypted_t))
						$encrypted_t[] = 'JOB_SUBJECT';
				}
				break;
			case 'V_TITLE':
				$v_title = true;
				if (!in_array('S.JS_TITLE', $select))
					$select[] = 'S.JS_TITLE';
				break;
			case 'V_COMP':
				$v_comp = true;
				if (!in_array('S.JS_COMPANY', $select))
				{
					$select[] = 'S.JS_COMPANY';
					$encrypted_f[] = 'S.JS_COMPANY';
					if (!in_array('JOB_SUBJECT', $encrypted_t))
						$encrypted_t[] = 'JOB_SUBJECT';
				}
				break;
			case 'V_JTYPE':
				$v_jtype = true;
				if (!in_array('JT.JT_TYPE', $select))
					$select[] = "JT.JT_TYPE";
				break;
			case 'V_SUCCESS':
				$v_success = true;
				if (!in_array('J.JT_SUCCESS', $select))
					$select[] = "J.JT_SUCCESS";
				break;
			case 'V_STATUS':
				$v_status = true;
				if (!in_array('ST.J_STATUS', $select))
					$select[] = 'ST.J_STATUS';
				break;
			case 'V_OWED':
				$v_owed = true;
				if (!in_array('J.JC_TOTAL_AMT', $select))
					$select[] = 'J.JC_TOTAL_AMT';
				break;
			case 'V_COLLECTED':
				$v_collected = true;
				break;
			case 'V_OUTST':
				$v_outst = true;
				if (!in_array('J.JC_TOTAL_AMT', $select))
					$select[] = 'J.JC_TOTAL_AMT';
				break;
			case 'V_CSPENT':
				$v_cspent = true;
				break;
			case 'V_NUM_PAY':
				$v_num_pay = true;
				break;
			case 'V_RET_TOTAL':
				$v_ret_total = true;
				break;
			case 'V_COL_TOTAL':
				$v_col_total = true;
				break;
			case 'V_COL_DIR':
				$v_col_dir = true;
				break;
			case 'V_COL_TOV':
				$v_col_tov = true;
				break;
			case 'V_COL_FOR':
				$v_col_for = true;
				break;
			case 'V_RET_PER':
				$v_ret_per = true;
				break;
			case 'V_PAID_FULL':
				$v_paid_full = true;
				if (!in_array('J.JC_TOTAL_AMT', $select))
					$select[] = 'J.JC_TOTAL_AMT';
				break;
			case 'V_PAY_LAST':
				$v_pay_last = true;
				break;
			case 'V_CLOSED':
				$v_closed = true;
				if (!in_array('J.J_CLOSED_DT', $select))
					$select[] = 'J.J_CLOSED_DT';
				break;
			case 'V_HOME_1':
				$v_home_1 = true;
				if (!in_array('S.JS_ADDR_1', $select))
				{
					$select[] = 'S.JS_ADDR_1';
					$encrypted_f[] = 'S.JS_ADDR_1';
					if (!in_array('JOB_SUBJECT', $encrypted_t))
						$encrypted_t[] = 'JOB_SUBJECT';
				}
				break;
			case 'V_HOME_2':
				$v_home_2 = true;
				if (!in_array('S.JS_ADDR_2', $select))
				{
					$select[] = 'S.JS_ADDR_2';
					$encrypted_f[] = 'S.JS_ADDR_2';
					if (!in_array('JOB_SUBJECT', $encrypted_t))
						$encrypted_t[] = 'JOB_SUBJECT';
				}
				break;
			case 'V_HOME_3':
				$v_home_3 = true;
				if (!in_array('S.JS_ADDR_3', $select))
				{
					$select[] = 'S.JS_ADDR_3';
					$encrypted_f[] = 'S.JS_ADDR_3';
					if (!in_array('JOB_SUBJECT', $encrypted_t))
						$encrypted_t[] = 'JOB_SUBJECT';
				}
				break;
			case 'V_HOME_4':
				$v_home_4 = true;
				if (!in_array('S.JS_ADDR_4', $select))
				{
					$select[] = 'S.JS_ADDR_4';
					$encrypted_f[] = 'S.JS_ADDR_4';
					if (!in_array('JOB_SUBJECT', $encrypted_t))
						$encrypted_t[] = 'JOB_SUBJECT';
				}
				break;
			case 'V_HOME_5':
				$v_home_5 = true;
				if (!in_array('S.JS_ADDR_5', $select))
				{
					$select[] = 'S.JS_ADDR_5';
					$encrypted_f[] = 'S.JS_ADDR_5';
					if (!in_array('JOB_SUBJECT', $encrypted_t))
						$encrypted_t[] = 'JOB_SUBJECT';
					break;
				}
			case 'V_HOME_PC':
				$v_home_pc = true;
				if (!in_array('S.JS_ADDR_PC', $select))
				{
					$select[] = 'S.JS_ADDR_PC';
					$encrypted_f[] = 'S.JS_ADDR_PC';
					if (!in_array('JOB_SUBJECT', $encrypted_t))
						$encrypted_t[] = 'JOB_SUBJECT';
				}
				break;
			case 'V_HOME_T':
				$v_home_t = true;
//				if (!in_array('JPP.JP_PHONE', $select))
//				{
//					$select[] = 'JPP.JP_PHONE';
//					$encrypted_f[] = 'JPP.JP_PHONE';
//					if (!in_array('JOB_PHONE', $encrypted_t))
//						$encrypted_t[] = 'JOB_PHONE';
//				}
				break;
			case 'V_PROP':
				$v_prop = true;
				if (!in_array('J.JT_PROPERTY', $select))
					$select[] = 'J.JT_PROPERTY';
				break;
			case 'V_L1_S':
				$v_l1_s = true;
				break;
			case 'V_L1_DT':
				$v_l1_dt = true;
				break;
			case 'V_L2_S':
				$v_l2_s = true;
				break;
			case 'V_L2_DT':
				$v_l2_dt = true;
				break;
			case 'V_L3_S':
				$v_l3_s = true;
				break;
			case 'V_L3_DT':
				$v_l3_dt = true;
				break;
			case 'V_LC_S':
				$v_lc_s = true;
				break;
			case 'V_LC_DT':
				$v_lc_dt = true;
				break;
			case 'V_LD_S':
				$v_ld_s = true;
				break;
			case 'V_LD_DT':
				$v_ld_dt = true;
				break;
			case 'V_REP_LAST':
				$v_rep_last = true;
				break;
			case 'V_UPD_LAST':
				$v_upd_last = true;
				if (!in_array('J.J_UPDATED_DT', $select))
					$select[] = 'J.J_UPDATED_DT';
				break;
			case 'V_REG_AM':
				$v_reg_am = true;
				if (!in_array('J.JC_INSTAL_AMT', $select))
					$select[] = 'J.JC_INSTAL_AMT';
				break;
			case 'V_REG_INT':
				$v_reg_int = true;
				if (!in_array('J.JC_INSTAL_FREQ', $select))
					$select[] = 'J.JC_INSTAL_FREQ';
				break;
			case 'V_REG_START':
				$v_reg_start = true;
				if (!in_array('J.JC_INSTAL_DT_1', $select))
					$select[] = 'J.JC_INSTAL_DT_1';
				break;
			case 'V_REG_METH':
				$v_reg_meth = true;
				if (!in_array('PM.PAYMENT_METHOD', $select))
					$select[] = 'PM.PAYMENT_METHOD';
				break;
			case 'V_LAST_AMT':
				$v_last_amt = true;
				break;
			case 'V_NEXT_DT':
				$v_next_dt = true;
				if (!in_array('J.JC_INSTAL_FREQ', $select))
					$select[] = 'J.JC_INSTAL_FREQ';
				if (!in_array('J.JC_INSTAL_DT_1', $select))
					$select[] = 'J.JC_INSTAL_DT_1';
				if (!in_array('J.JC_TOTAL_AMT', $select))
					$select[] = 'J.JC_TOTAL_AMT';
				if (!in_array('J.J_CLOSED_DT', $select))
					$select[] = 'J.J_CLOSED_DT';
				break;
			case 'V_L_NEXT':
				$v_l_next = true;
				if (!in_array('J.JC_LETTER_MORE', $select))
					$select[] = 'J.JC_LETTER_MORE';
				if (!in_array('LT.LETTER_NAME', $select))
					$select[] = 'LT.LETTER_NAME';
				break;
			case 'V_DIARY':
				$v_diary = true;
				if (!in_array('J.J_DIARY_DT', $select))
					$select[] = 'J.J_DIARY_DT';
				break;
			case 'V_EMAIL':
				$v_email = true;
//				if (!in_array('JPE.JP_EMAIL', $select))
//				{
//					$select[] = 'JPE.JP_EMAIL';
//					$encrypted_f[] = 'JPE.JP_EMAIL';
//					if (!in_array('JOB_PHONE', $encrypted_t))
//						$encrypted_t[] = 'JOB_PHONE';
//				}
				break;
			case 'V_NEW_1':
				$v_new_1 = true;
				if (!in_array('S.NEW_ADDR_1', $select))
				{
					$select[] = 'S.NEW_ADDR_1';
					$encrypted_f[] = 'S.NEW_ADDR_1';
					if (!in_array('JOB_SUBJECT', $encrypted_t))
						$encrypted_t[] = 'JOB_SUBJECT';
				}
				break;
			case 'V_NEW_2':
				$v_new_2 = true;
				if (!in_array('S.NEW_ADDR_2', $select))
				{
					$select[] = 'S.NEW_ADDR_2';
					$encrypted_f[] = 'S.NEW_ADDR_2';
					if (!in_array('JOB_SUBJECT', $encrypted_t))
						$encrypted_t[] = 'JOB_SUBJECT';
				}
				break;
			case 'V_NEW_3':
				$v_new_3 = true;
				if (!in_array('S.NEW_ADDR_3', $select))
				{
					$select[] = 'S.NEW_ADDR_3';
					$encrypted_f[] = 'S.NEW_ADDR_3';
					if (!in_array('JOB_SUBJECT', $encrypted_t))
						$encrypted_t[] = 'JOB_SUBJECT';
				}
				break;
			case 'V_NEW_4':
				$v_new_4 = true;
				if (!in_array('S.NEW_ADDR_4', $select))
				{
					$select[] = 'S.NEW_ADDR_4';
					$encrypted_f[] = 'S.NEW_ADDR_4';
					if (!in_array('JOB_SUBJECT', $encrypted_t))
						$encrypted_t[] = 'JOB_SUBJECT';
				}
				break;
			case 'V_NEW_5':
				$v_new_5 = true;
				if (!in_array('S.NEW_ADDR_5', $select))
				{
					$select[] = 'S.NEW_ADDR_5';
					$encrypted_f[] = 'S.NEW_ADDR_5';
					if (!in_array('JOB_SUBJECT', $encrypted_t))
						$encrypted_t[] = 'JOB_SUBJECT';
					break;
				}
			case 'V_LS_T':
				$v_ls_t = true;
				break;
			case 'V_LD_T':
				$v_ld_t = true;
				break;
			case 'V_LS_M':
				$v_ls_m = true;
				break;
			case 'V_LD_M':
				$v_ld_m = true;
				break;
			case 'V_LS_RP':
				$v_ls_rp = true;
				break;
			case 'V_LD_RP':
				$v_ld_rp = true;
				break;
			case 'V_LS_OT':
				$v_ls_ot = true;
				break;
			case 'V_LD_OT':
				$v_ld_ot = true;
				break;
			case 'V_LS_SV':
				$v_ls_sv = true;
				break;
			case 'V_LD_SV':
				$v_ld_sv = true;
				break;
			case 'V_LS_R1':
				$v_ls_r1 = true;
				break;
			case 'V_LD_R1':
				$v_ld_r1 = true;
				break;
			case 'V_LS_R2':
				$v_ls_r2 = true;
				break;
			case 'V_LD_R2':
				$v_ld_r2 = true;
				break;
			case 'V_LS_R3':
				$v_ls_r3 = true;
				break;
			case 'V_LD_R3':
				$v_ld_r3 = true;
				break;
			case 'V_LS_TC':
				$v_ls_tc = true;
				break;
			case 'V_LD_TC':
				$v_ld_tc = true;
				break;
			case 'V_LS_TM':
				$v_ls_tm = true;
				break;
			case 'V_LD_TM':
				$v_ld_tm = true;
				break;
			case 'V_LS_AT':
				$v_ls_at = true;
				break;
			case 'V_LD_AT':
				$v_ld_at = true;
				break;
			case 'V_CLI_ID':
				$v_cli_id = true;
				break;
			case 'V_SEQ':
				$v_seq = true;
				if (!in_array('J.J_SEQUENCE', $select))
					$select[] = 'J.J_SEQUENCE';
				break;
			case 'V_USER':
				$v_user = true;
				if (!in_array('J.J_USER_ID', $select))
					$select[] = 'J.J_USER_ID';
				if (!in_array('US.U_INITIALS', $select))
					$select[] = 'US.U_INITIALS';
				break;
			case 'V_DT_START':
				$v_dt_start = true;
				if (!in_array('J.J_OPENED_DT', $select))
					$select[] = 'J.J_OPENED_DT';
				break;
			case 'V_DT_END':
				$v_dt_end = true;
				if (!in_array('J.J_CLOSED_DT', $select))
					$select[] = 'J.J_CLOSED_DT';
				break;
			case 'V_AP_N1':
				$v_ap_n1 = true;
				break;
			case 'V_AP_D1':
				$v_ap_d1 = true;
				break;
			case 'V_AP_N2':
				$v_ap_n2 = true;
				break;
			case 'V_AP_D2':
				$v_ap_d2 = true;
				break;
			case 'V_AP_N3':
				$v_ap_n3 = true;
				break;
			case 'V_AP_D3':
				$v_ap_d3 = true;
				break;
			case 'V_AP_N4':
				$v_ap_n4 = true;
				break;
			case 'V_AP_D4':
				$v_ap_d4 = true;
				break;
			case 'V_AP_N5':
				$v_ap_n5 = true;
				break;
			case 'V_AP_D5':
				$v_ap_d5 = true;
				break;
			case 'V_TDX_ID':
				$v_tdx_id = true;
				if (!in_array('J.JC_TRANS_ID', $select))
					$select[] = 'J.JC_TRANS_ID';
				break;
			case 'V_TDX_AS':
				$v_tdx_as = true;
				break;
			case 'V_TDX_PP':
				$v_tdx_pp = true;
				break;
			case 'V_TDX_CLIENT':
				$v_tdx_client = true;
				break;
			case 'REASON_DEBT':
				$reason_debt = true;
				if (!in_array('J.JC_REASON_2', $select))
				{
					$select[] = 'J.JC_REASON_2';
					$encrypted_f[] = 'J.JC_REASON_2';
					if (!in_array('JOB', $encrypted_t))
						$encrypted_t[] = 'JOB';
				}
				break;
			default:
				dprint("Error $this_function: field \"$fld\" not recognised", true);
				break;
		}
	}
	# ---------------------------------------------------------------------------------------------

	# ---- Get the raw data from the database -----------------------------------------------------

	$in_sheets = array(); # indexed by CLIENT2_ID, one sheet per client
	$client_names = array();
	$client_reports = array();

	foreach ($clients as $client2_id)
	{
		$in_sheets[$client2_id] = array(); # each sheet will have a list of jobs; each job will be indexed by JOB_ID
		$client_names[$client2_id] = client_name_from_id($client2_id);
		if ($v_rep_last)
			$client_reports[$client2_id] = '';
	}

	$where = array("J.OBSOLETE=$sqlFalse", "J.CLIENT2_ID IN (" . implode(',', $clients) . ")");
	$where[] = ($collect ? "J.JC_JOB=$sqlTrue" : "J.JT_JOB=$sqlTrue");
	if ($run_rx_dt_fr_sql)
		$where[] = "$run_rx_dt_fr_sql <= J.J_OPENED_DT";
	if ($run_rx_dt_to_plus1_sql)
		$where[] = "J.J_OPENED_DT < $run_rx_dt_to_plus1_sql";

	$job_status_check = false;
	if ($collect)
	{
		if ($report['REP_JOB_STATUS'] == 1)
		{
			$job_status_check = true;
			$where[] = "J.JOB_CLOSED = $sqlFalse";
		}
		elseif ($report['REP_JOB_STATUS'] == -1)
		{
			$job_status_check = true;
			$where[] = "J.JOB_CLOSED=$sqlTrue";
		}
		if (($report['REP_PAYMENTS'] == 1) || ($report['REP_PAYMENTS'] == -1))
		{
			$sub_sql = "
				(SELECT COUNT(*) FROM JOB_PAYMENT AS PA WHERE PA.JOB_ID=J.JOB_ID AND (PA.OBSOLETE=$sqlFalse) ";
			if ($run_p1_dt_fr_sql)
				$sub_sql .= " AND ($run_p1_dt_fr_sql <= COL_DT_RX) ";
			if ($run_p1_dt_to_plus1_sql)
				$sub_sql .= " AND (COL_DT_RX < $run_p1_dt_to_plus1_sql)";
			$sub_sql .= ") ";
			if ($report['REP_PAYMENTS'] == 1)
				$sub_sql .= ">";
			else
				$sub_sql .= "=";
			$sub_sql .= "0
				";
			$where[] = $sub_sql;
		}
	}

	# Set of closed jobs
	$jobs_closed = array();
	if ($v_closed || $v_next_dt)
	{
		# Get list of Job IDs for all closed jobs in the received-date range (and maybe within the collect-status criteria)
		$sql = "SELECT J.JOB_ID
				FROM JOB AS J
				WHERE (" . implode(') AND (', $where) . ") AND (J.JOB_CLOSED=$sqlTrue)";
		#dprint($sql);#
		if ($debug) dlog("sql/1: $sql");
		sql_execute($sql);
		while (($newArray = sql_fetch_assoc()) != false)
			$jobs_closed[] = $newArray['JOB_ID'];
		if ($debug) dlog("found " . count($jobs_closed) . " closed jobs");
	} # Set of closed jobs

//	# Set of "additional" phone numbers
//	$add_phones = array();
//	if ($v_ap_n1 || $v_ap_d1 || $v_ap_n2 || $v_ap_d2 || $v_ap_n3 || $v_ap_d3 || $v_ap_n4 || $v_ap_d4 || $v_ap_n5 || $v_ap_d5)
//	{
//		# Get list of non-primary phone numbers for all jobs in the received-date range
//		sql_encryption_preparation('JOB_PHONE');
//		$sql = "SELECT J.JOB_ID, " . sql_decrypt('JP.JP_PHONE', '', true) . " ," . sql_decrypt('JP.JP_DESCR', '', true) . "
//				FROM JOB AS J
//				INNER JOIN JOB_PHONE AS JP ON JP.JOB_ID=J.JOB_ID AND JP.JP_PHONE IS NOT NULL AND JP.JP_PRIMARY_P=$sqlFalse
//				WHERE (" . implode(') AND (', $where) . ")
//				ORDER BY J.JOB_ID, JP.JOB_PHONE_ID
//				";
//		#dprint($sql);#
//		sql_execute($sql);
//		while (($newArray = sql_fetch_assoc()) != false)
//		{
//			$jp_phone = trim($newArray['JP_PHONE']);
//			if (($jp_phone != '') && ($jp_phone != 'NL'))
//			{
//				if (!array_key_exists($newArray['JOB_ID'], $add_phones))
//					$add_phones[$newArray['JOB_ID']] = array();
//				$jp_descr = trim(str_replace('Imported from old system job file', '', $newArray['JP_DESCR']));
//				$add_phones[$newArray['JOB_ID']][] = array($jp_phone, $jp_descr);
//			}
//		}
//	} # Set of "additional" phone numbers
//	#dprint("add_phones=" . print_r($add_phones,1));#

	# Set of all phone numbers for all jobs
	$all_phones = array();
	if ($v_home_t || $v_ap_n1 || $v_ap_d1 || $v_ap_n2 || $v_ap_d2 || $v_ap_n3 || $v_ap_d3 || $v_ap_n4 || $v_ap_d4 || $v_ap_n5 || $v_ap_d5)
	{
		# Get list of all phone numbers for all jobs in the received-date range
		sql_encryption_preparation('JOB_PHONE');
		$sql = "SELECT J.JOB_ID, JP.JP_PRIMARY_P, " . sql_decrypt('JP.JP_PHONE', '', true) . " ," . sql_decrypt('JP.JP_DESCR', '', true) . "
				FROM JOB AS J
				INNER JOIN JOB_PHONE AS JP ON JP.JOB_ID=J.JOB_ID AND JP.JP_PHONE IS NOT NULL
				WHERE (" . implode(') AND (', $where) . ")
				ORDER BY J.JOB_ID, JP.JP_PRIMARY_P DESC, JP.JOB_PHONE_ID
				";
		#dprint($sql);#
		if ($debug) dlog("sql/2: $sql");
		sql_execute($sql);
		while (($newArray = sql_fetch_assoc()) != false)
		{
			$jp_phone = trim($newArray['JP_PHONE']);
			if (($jp_phone != '') && ($jp_phone != 'NL'))
			{
				if (!array_key_exists($newArray['JOB_ID'], $all_phones))
					$all_phones[$newArray['JOB_ID']] = array();
				$jp_descr = trim(str_replace('Imported from old system job file', '', $newArray['JP_DESCR']));
				$all_phones[$newArray['JOB_ID']][] = array($jp_phone, $jp_descr);
			}
		}
		if ($debug) dlog("found " . count($all_phones) . " phones");
	} # Set of all phone numbers
	#dprint("all_phones=" . print_r($all_phones,1));#

	# Main email address for all jobs
	$all_emails = array();
	if ($v_email)
	{
		sql_encryption_preparation('JOB_PHONE');
		$sql = "SELECT J.JOB_ID, JP.JP_PRIMARY_E, " . sql_decrypt('JP.JP_EMAIL', '', true) . " ," . sql_decrypt('JP.JP_DESCR', '', true) . "
				FROM JOB AS J
				INNER JOIN JOB_PHONE AS JP ON JP.JOB_ID=J.JOB_ID AND JP.JP_EMAIL IS NOT NULL
				WHERE (" . implode(') AND (', $where) . ")
				ORDER BY J.JOB_ID, JP.JP_PRIMARY_E DESC, JP.JOB_PHONE_ID
				";
		#dprint($sql);#
		if ($debug) dlog("sql/3: $sql");
		sql_execute($sql);
		while (($newArray = sql_fetch_assoc()) != false)
		{
			$jp_email = trim($newArray['JP_EMAIL']);
			if ($jp_email != '')
			{
				if (!array_key_exists($newArray['JOB_ID'], $all_emails))
					$all_emails[$newArray['JOB_ID']] = array();
				$jp_descr = trim(str_replace('Imported from old system job file', '', $newArray['JP_DESCR']));
				$all_emails[$newArray['JOB_ID']][] = array($jp_email, $jp_descr);
			}
		}
		if ($debug) dlog("found " . count($all_emails) . " emails");
	}
	#dprint("\$all_emails=" . print_r($all_emails,1));#

	# Get Jobs with info for selected report fields
	$select_sql = array();
	foreach ($select as $fld)
	{
		if (in_array($fld, $encrypted_f))
			$select_sql[] = sql_decrypt($fld, '', true);
		else
			$select_sql[] = $fld;
	}
	if (0 < count($encrypted_t))
	{
		foreach ($encrypted_t as $table)
			sql_encryption_preparation($table);
	}

	$sql = "SELECT " . implode(', ', $select_sql) . "
			FROM JOB AS J ";
	if ($v_lname || $v_fname || $v_title || $v_home_1 || $v_home_2 || $v_home_3 || $v_home_4 || $v_home_5 || $v_home_pc ||
		$v_new_1 || $v_new_2 || $v_new_3 || $v_new_4 || $v_new_5) #$v_home_t || $v_email ||
	{
		$sql .= "
			LEFT JOIN JOB_SUBJECT AS S
				ON (S.JOB_ID=J.JOB_ID) AND (S.JS_PRIMARY=$sqlTrue) ";
		# Only select primary subject to ensure no more than one record per job in resultant record-set
		#$where[] = "S.JS_PRIMARY=1";

//		if ($v_home_t)
//		{
//			$sql .= "
//				LEFT JOIN JOB_PHONE AS JPP
//					ON (JPP.JOB_SUBJECT_ID=S.JOB_SUBJECT_ID) AND (JPP.JP_PHONE IS NOT NULL) AND (JPP.JP_PRIMARY_P=$sqlTrue)";
//		}
//		if ($v_email)
//		{
//			$sql .= "
//				LEFT JOIN JOB_PHONE AS JPE
//					ON (JPE.JOB_SUBJECT_ID=S.JOB_SUBJECT_ID) AND (JPE.JP_EMAIL IS NOT NULL) AND (JPE.JP_PRIMARY_E=$sqlTrue)";
//		}
	}
	if ($job_status_check || $v_status) # || $v_success) # we need "ST." for WHERE for $v_success
		$sql .= "
			LEFT JOIN JOB_STATUS_SD AS ST
				ON ST.JOB_STATUS_ID=J.JC_JOB_STATUS_ID";
	if ($v_jtype)
		$sql .= "
			LEFT JOIN JOB_TYPE_SD AS JT
				ON JT.JOB_TYPE_ID=J.JT_JOB_TYPE_ID";
	if ($v_l_next)
		$sql .= "
			LEFT JOIN LETTER_TYPE_SD AS LT
				ON LT.LETTER_TYPE_ID=J.JC_LETTER_TYPE_ID";
	if ($v_user)
		$sql .= "
			LEFT JOIN USERV AS US
				ON US.USER_ID=J.J_USER_ID";
	if ($v_reg_meth)
		$sql .= "
			LEFT JOIN PAYMENT_METHOD_SD AS PM
				ON PM.PAYMENT_METHOD_ID=J.JC_PAYMENT_METHOD_ID";
	$sql .= "
			WHERE (" . implode(') AND (', $where) . ")
			ORDER BY J_OPENED_DT";
	#if ($debug) dprint(str_replace(chr(10), '<br>', $sql));#
	if ($debug) dlog("sql/4: $sql");
	sql_execute($sql);
	while (($newArray = sql_fetch_assoc()) != false)
	{
		$job_id = $newArray['JOB_ID'];
		$client2_id = $newArray['CLIENT2_ID'];

		# Add to this sheet's jobs list
		if (!array_key_exists($job_id, $in_sheets[$client2_id]))
			$in_sheets[$client2_id][$job_id] = array();

		# Fill in Job element
		if ($v_vilno)
			$in_sheets[$client2_id][$job_id]['V_VILNO'] = $newArray['J_VILNO'];
		if ($v_cliref)
			$in_sheets[$client2_id][$job_id]['V_CLIREF'] = $newArray['CLIENT_REF'];
		if ($v_dt_rx)
			$in_sheets[$client2_id][$job_id]['V_DT_RX'] = $newArray['J_OPENED_DT'];
		if ($v_lname)
			$in_sheets[$client2_id][$job_id]['V_LNAME'] = $newArray['JS_LASTNAME'];
		if ($v_fname)
			$in_sheets[$client2_id][$job_id]['V_FNAME'] = $newArray['JS_FIRSTNAME'];
		if ($v_title)
			$in_sheets[$client2_id][$job_id]['V_TITLE'] = $newArray['JS_TITLE'];
		if ($v_comp)
			$in_sheets[$client2_id][$job_id]['V_COMP'] = $newArray['JS_COMPANY'];
		if ($v_jtype)
			$in_sheets[$client2_id][$job_id]['V_JTYPE'] = $newArray['JT_TYPE'];
		if ($v_success)
			$in_sheets[$client2_id][$job_id]['V_SUCCESS'] = $newArray['JT_SUCCESS']; # 1, 0, -1 or blank
		if ($v_status)
			$in_sheets[$client2_id][$job_id]['V_STATUS'] = $newArray['J_STATUS'];
		if ($v_owed)
			$in_sheets[$client2_id][$job_id]['V_OWED'] = $newArray['JC_TOTAL_AMT'];
		if ($v_collected || $v_next_dt)
			$in_sheets[$client2_id][$job_id]['V_COLLECTED'] = 0.0; # This is filled in with another SQL call below
		if ($v_outst)
			$in_sheets[$client2_id][$job_id]['V_OUTST'] = $newArray['JC_TOTAL_AMT']; # This is modified with another SQL call below
		if ($v_cspent)
			$in_sheets[$client2_id][$job_id]['V_CSPENT'] = 0.0; # This is filled in with another SQL call below
		if ($v_num_pay)
			$in_sheets[$client2_id][$job_id]['V_NUM_PAY'] = 0; # This is filled in with another SQL call below
		if ($v_ret_total)
			$in_sheets[$client2_id][$job_id]['V_RET_TOTAL'] = 0.0; # This is filled in with another SQL call below
		if ($v_col_total)
			$in_sheets[$client2_id][$job_id]['V_COL_TOTAL'] = 0.0; # This is filled in with another SQL call below
		if ($v_col_dir)
			$in_sheets[$client2_id][$job_id]['V_COL_DIR'] = 0.0; # This is filled in with another SQL call below
		if ($v_col_tov)
			$in_sheets[$client2_id][$job_id]['V_COL_TOV'] = 0.0; # This is filled in with another SQL call below
		if ($v_col_for)
			$in_sheets[$client2_id][$job_id]['V_COL_FOR'] = 0.0; # This is filled in with another SQL call below
		if ($v_ret_per)
			$in_sheets[$client2_id][$job_id]['V_RET_PER'] = 0.0; # This is filled in with another SQL call below
		if ($v_paid_full)
			$in_sheets[$client2_id][$job_id]['V_PAID_FULL'] = $newArray['JC_TOTAL_AMT']; # This is modified with another SQL call below
		if ($v_pay_last || $v_last_amt || $v_next_dt)
			$in_sheets[$client2_id][$job_id]['V_PAY_LAST'] = '';
		if ($v_closed || $v_next_dt)
		{
			if (in_array($job_id, $jobs_closed))
			{
				if ($newArray['J_CLOSED_DT'] != '')
					$in_sheets[$client2_id][$job_id]['V_CLOSED'] = $newArray['J_CLOSED_DT'];
				else
					$in_sheets[$client2_id][$job_id]['V_CLOSED'] = 'XX';
			}
			else
				$in_sheets[$client2_id][$job_id]['V_CLOSED'] = '';
		}
		if ($v_home_1)
			$in_sheets[$client2_id][$job_id]['V_HOME_1'] = $newArray['JS_ADDR_1'];
		if ($v_home_2)
			$in_sheets[$client2_id][$job_id]['V_HOME_2'] = $newArray['JS_ADDR_2'];
		if ($v_home_3)
			$in_sheets[$client2_id][$job_id]['V_HOME_3'] = $newArray['JS_ADDR_3'];
		if ($v_home_4)
			$in_sheets[$client2_id][$job_id]['V_HOME_4'] = $newArray['JS_ADDR_4'];
		if ($v_home_5)
			$in_sheets[$client2_id][$job_id]['V_HOME_5'] = $newArray['JS_ADDR_5'];
		if ($v_home_pc)
			$in_sheets[$client2_id][$job_id]['V_HOME_PC'] = $newArray['JS_ADDR_PC'];
//		if ($v_home_t)
//			$in_sheets[$client2_id][$job_id]['V_HOME_T'] = $newArray['JP_PHONE'];
		if ($v_prop)
			$in_sheets[$client2_id][$job_id]['V_PROP'] = $newArray['JT_PROPERTY'];
		if ($v_l1_s)
			$in_sheets[$client2_id][$job_id]['V_L1_S'] = 0; # count
		if ($v_l1_dt)
			$in_sheets[$client2_id][$job_id]['V_L1_DT'] = '';
		if ($v_l2_s)
			$in_sheets[$client2_id][$job_id]['V_L2_S'] = 0; # count
		if ($v_l2_dt)
			$in_sheets[$client2_id][$job_id]['V_L2_DT'] = '';
		if ($v_l3_s)
			$in_sheets[$client2_id][$job_id]['V_L3_S'] = 0; # count
		if ($v_l3_dt)
			$in_sheets[$client2_id][$job_id]['V_L3_DT'] = '';
		if ($v_lc_s)
			$in_sheets[$client2_id][$job_id]['V_LC_S'] = 0; # count
		if ($v_lc_dt)
			$in_sheets[$client2_id][$job_id]['V_LC_DT'] = '';
		if ($v_ld_s)
			$in_sheets[$client2_id][$job_id]['V_LD_S'] = 0; # count
		if ($v_ld_dt)
			$in_sheets[$client2_id][$job_id]['V_LD_DT'] = '';
		if ($v_upd_last)
			$in_sheets[$client2_id][$job_id]['V_UPD_LAST'] = $newArray['J_UPDATED_DT'];
		if ($v_reg_am)
			$in_sheets[$client2_id][$job_id]['V_REG_AM'] = $newArray['JC_INSTAL_AMT'];
		if ($v_reg_int)
			$in_sheets[$client2_id][$job_id]['V_REG_INT'] = $newArray['JC_INSTAL_FREQ'];
		if ($v_reg_start)
			$in_sheets[$client2_id][$job_id]['V_REG_START'] = $newArray['JC_INSTAL_DT_1'];
		if ($v_reg_meth)
			$in_sheets[$client2_id][$job_id]['V_REG_METH'] = $newArray['PAYMENT_METHOD'];
		if ($v_last_amt)
			$in_sheets[$client2_id][$job_id]['V_LAST_AMT'] = '';
		if ($v_next_dt)
			$in_sheets[$client2_id][$job_id]['V_NEXT_DT'] = array(
					$newArray['JC_INSTAL_FREQ'], $newArray['JC_INSTAL_DT_1'], $newArray['JC_TOTAL_AMT']);

		if ($v_l_next)
		{
			if ($newArray['JC_LETTER_MORE'])
			{
				if ($newArray['LETTER_NAME'])
					$next_letter = $newArray['LETTER_NAME'];
				else
					$next_letter = "(missing letter name)";
			}
			else
			{
				if ($newArray['LETTER_NAME'])
					$next_letter = "None [{$newArray['LETTER_NAME']}]";
				else
					$next_letter = "None.";
			}
			$in_sheets[$client2_id][$job_id]['V_L_NEXT'] = $next_letter;
		}

		if ($v_diary)
			$in_sheets[$client2_id][$job_id]['V_DIARY'] = $newArray['J_DIARY_DT'];
//		if ($v_email)
//			$in_sheets[$client2_id][$job_id]['V_EMAIL'] = $newArray['JP_EMAIL'];
		if ($v_new_1)
			$in_sheets[$client2_id][$job_id]['V_NEW_1'] = $newArray['NEW_ADDR_1'];
		if ($v_new_2)
			$in_sheets[$client2_id][$job_id]['V_NEW_2'] = $newArray['NEW_ADDR_2'];
		if ($v_new_3)
			$in_sheets[$client2_id][$job_id]['V_NEW_3'] = $newArray['NEW_ADDR_3'];
		if ($v_new_4)
			$in_sheets[$client2_id][$job_id]['V_NEW_4'] = $newArray['NEW_ADDR_4'];
		if ($v_new_5)
			$in_sheets[$client2_id][$job_id]['V_NEW_5'] = $newArray['NEW_ADDR_5'];
		if ($v_ls_t)
			$in_sheets[$client2_id][$job_id]['V_LS_T'] = 0; # count
		if ($v_ld_t)
			$in_sheets[$client2_id][$job_id]['V_LD_T'] = '';
		if ($v_ls_m)
			$in_sheets[$client2_id][$job_id]['V_LS_M'] = 0; # count
		if ($v_ld_m)
			$in_sheets[$client2_id][$job_id]['V_LD_M'] = '';
		if ($v_ls_rp)
			$in_sheets[$client2_id][$job_id]['V_LS_RP'] = 0; # count
		if ($v_ld_rp)
			$in_sheets[$client2_id][$job_id]['V_LD_RP'] = '';
		if ($v_ls_ot)
			$in_sheets[$client2_id][$job_id]['V_LS_OT'] = 0; # count
		if ($v_ld_ot)
			$in_sheets[$client2_id][$job_id]['V_LD_OT'] = '';
		if ($v_ls_sv)
			$in_sheets[$client2_id][$job_id]['V_LS_SV'] = 0; # count
		if ($v_ld_sv)
			$in_sheets[$client2_id][$job_id]['V_LD_SV'] = '';
		if ($v_ls_r1)
			$in_sheets[$client2_id][$job_id]['V_LS_R1'] = 0; # count
		if ($v_ld_r1)
			$in_sheets[$client2_id][$job_id]['V_LD_R1'] = '';
		if ($v_ls_r2)
			$in_sheets[$client2_id][$job_id]['V_LS_R2'] = 0; # count
		if ($v_ld_r2)
			$in_sheets[$client2_id][$job_id]['V_LD_R2'] = '';
		if ($v_ls_r3)
			$in_sheets[$client2_id][$job_id]['V_LS_R3'] = 0; # count
		if ($v_ld_r3)
			$in_sheets[$client2_id][$job_id]['V_LD_R3'] = '';
		if ($v_ls_tc)
			$in_sheets[$client2_id][$job_id]['V_LS_TC'] = 0; # count
		if ($v_ld_tc)
			$in_sheets[$client2_id][$job_id]['V_LD_TC'] = '';
		if ($v_ls_tm)
			$in_sheets[$client2_id][$job_id]['V_LS_TM'] = 0; # count
		if ($v_ld_tm)
			$in_sheets[$client2_id][$job_id]['V_LD_TM'] = '';
		if ($v_ls_at)
			$in_sheets[$client2_id][$job_id]['V_LS_AT'] = 0; # count
		if ($v_ld_at)
			$in_sheets[$client2_id][$job_id]['V_LD_AT'] = '';
		if ($v_seq)
			$in_sheets[$client2_id][$job_id]['V_SEQ'] = $newArray['J_SEQUENCE'];
		if ($v_user)
			$in_sheets[$client2_id][$job_id]['V_USER'] = "{$newArray['U_INITIALS']} ({$newArray['J_USER_ID']})";
		if ($v_dt_start)
			$in_sheets[$client2_id][$job_id]['V_DT_START'] = $newArray['J_OPENED_DT'];
		if ($v_dt_end)
			$in_sheets[$client2_id][$job_id]['V_DT_END'] = $newArray['J_CLOSED_DT'];
//		if ($v_ap_n1)
//			$in_sheets[$client2_id][$job_id]['V_AP_N1'] = get_add_phone(1, 'n'); # uses $add_phones and $job_id; lib_vilcol.php
//		if ($v_ap_d1)
//			$in_sheets[$client2_id][$job_id]['V_AP_D1'] = get_add_phone(1, 'd'); # uses $add_phones and $job_id; lib_vilcol.php
//		if ($v_ap_n2)
//			$in_sheets[$client2_id][$job_id]['V_AP_N2'] = get_add_phone(2, 'n'); # uses $add_phones and $job_id; lib_vilcol.php
//		if ($v_ap_d2)
//			$in_sheets[$client2_id][$job_id]['V_AP_D2'] = get_add_phone(2, 'd'); # uses $add_phones and $job_id; lib_vilcol.php
//		if ($v_ap_n3)
//			$in_sheets[$client2_id][$job_id]['V_AP_N3'] = get_add_phone(3, 'n'); # uses $add_phones and $job_id; lib_vilcol.php
//		if ($v_ap_d3)
//			$in_sheets[$client2_id][$job_id]['V_AP_D3'] = get_add_phone(3, 'd'); # uses $add_phones and $job_id; lib_vilcol.php
//		if ($v_ap_n4)
//			$in_sheets[$client2_id][$job_id]['V_AP_N4'] = get_add_phone(4, 'n'); # uses $add_phones and $job_id; lib_vilcol.php
//		if ($v_ap_d4)
//			$in_sheets[$client2_id][$job_id]['V_AP_D4'] = get_add_phone(4, 'd'); # uses $add_phones and $job_id; lib_vilcol.php
//		if ($v_ap_n5)
//			$in_sheets[$client2_id][$job_id]['V_AP_N5'] = get_add_phone(5, 'n'); # uses $add_phones and $job_id; lib_vilcol.php
//		if ($v_ap_d5)
//			$in_sheets[$client2_id][$job_id]['V_AP_D5'] = get_add_phone(5, 'd'); # uses $add_phones and $job_id; lib_vilcol.php
		if ($v_home_t)
			$in_sheets[$client2_id][$job_id]['V_HOME_T'] = get_any_phone(0, 'n'); # uses $all_phones and $job_id; lib_vilcol.php
		if ($v_ap_n1)
			$in_sheets[$client2_id][$job_id]['V_AP_N1'] = get_any_phone(1, 'n'); # uses $all_phones and $job_id; lib_vilcol.php
		if ($v_ap_d1)
			$in_sheets[$client2_id][$job_id]['V_AP_D1'] = get_any_phone(1, 'd'); # uses $all_phones and $job_id; lib_vilcol.php
		if ($v_ap_n2)
			$in_sheets[$client2_id][$job_id]['V_AP_N2'] = get_any_phone(2, 'n'); # uses $all_phones and $job_id; lib_vilcol.php
		if ($v_ap_d2)
			$in_sheets[$client2_id][$job_id]['V_AP_D2'] = get_any_phone(2, 'd'); # uses $all_phones and $job_id; lib_vilcol.php
		if ($v_ap_n3)
			$in_sheets[$client2_id][$job_id]['V_AP_N3'] = get_any_phone(3, 'n'); # uses $all_phones and $job_id; lib_vilcol.php
		if ($v_ap_d3)
			$in_sheets[$client2_id][$job_id]['V_AP_D3'] = get_any_phone(3, 'd'); # uses $all_phones and $job_id; lib_vilcol.php
		if ($v_ap_n4)
			$in_sheets[$client2_id][$job_id]['V_AP_N4'] = get_any_phone(4, 'n'); # uses $all_phones and $job_id; lib_vilcol.php
		if ($v_ap_d4)
			$in_sheets[$client2_id][$job_id]['V_AP_D4'] = get_any_phone(4, 'd'); # uses $all_phones and $job_id; lib_vilcol.php
		if ($v_ap_n5)
			$in_sheets[$client2_id][$job_id]['V_AP_N5'] = get_any_phone(5, 'n'); # uses $all_phones and $job_id; lib_vilcol.php
		if ($v_ap_d5)
			$in_sheets[$client2_id][$job_id]['V_AP_D5'] = get_any_phone(5, 'd'); # uses $all_phones and $job_id; lib_vilcol.php
		if ($v_email)
			$in_sheets[$client2_id][$job_id]['V_EMAIL'] = get_any_email(-1, 'n'); # uses $all_emails and $job_id; lib_vilcol.php
		if ($v_tdx_id)
			$in_sheets[$client2_id][$job_id]['V_TDX_ID'] = $newArray['JC_TRANS_ID'];
		if ($v_tdx_as)
			$in_sheets[$client2_id][$job_id]['V_TDX_AS'] = (($v_tdx_id && $newArray['JC_TRANS_ID']) ? '?' : '');
		if ($v_tdx_pp)
			$in_sheets[$client2_id][$job_id]['V_TDX_PP'] = (($v_tdx_id && $newArray['JC_TRANS_ID'])  ? '?' : '');
		if ($reason_debt)
			$in_sheets[$client2_id][$job_id]['REASON_DEBT'] = $newArray['JC_REASON_2'];

	} # while (($newArray = sql_fetch_assoc()) != false)
	if ($debug) dlog("After SQL-4, count(in_sheets)=" . count($in_sheets));

	if ($v_l1_s || $v_l1_dt || $v_l2_s || $v_l2_dt || $v_l3_s || $v_l3_dt || $v_lc_dt || $v_lc_s || $v_ld_s || $v_ld_dt ||
		$v_ls_t || $v_ld_t || $v_ls_m || $v_ld_m || $v_ls_rp || $v_ld_rp || $v_ls_ot || $v_ld_ot || $v_ls_sv || $v_ld_sv ||
		$v_ls_r1 || $v_ld_r1 || $v_ls_r2 || $v_ld_r2 || $v_ls_r3 || $v_ld_r3 || $v_ls_tc || $v_ld_tc || $v_ls_tm || $v_ld_tm ||
		$v_ls_at || $v_ld_at)
	{
		# Check for Letters attached to jobs
		foreach ($in_sheets as $client2_id => $jobs) # go through input sheets
		{
			foreach ($jobs as $job_id => $job_info)
			{
				$sql = "SELECT L.JL_POSTED_DT, E.EM_DT, L.LETTER_TYPE_ID " .
						"FROM JOB_LETTER AS L LEFT JOIN EMAIL AS E ON E.EMAIL_ID=L.JL_EMAIL_ID AND E.OBSOLETE=$sqlFalse
						WHERE L.JOB_ID=$job_id";
				#dprint(str_replace(chr(10), '<br>', $sql));#
				#if ($debug) dlog("sql/5: $sql");
				sql_execute($sql);
				$letter_count = 0;
				$letter_types = '';
				while (($newArray = sql_fetch_assoc()) != false)
				{
					$letter_count++;
					$letter_types .= "{$newArray['LETTER_TYPE_ID']},";
					if ($v_l1_s)
					{
						if ($newArray['LETTER_TYPE_ID'] == $id_LETTER_TYPE_letter_1)
							$in_sheets[$client2_id][$job_id]['V_L1_S']++;
					}
					if ($v_l1_dt)
					{
						# Return latest date
						if (($newArray['LETTER_TYPE_ID'] == $id_LETTER_TYPE_letter_1) &&
							1)#($in_sheets[$client2_id][$job_id]['V_L1_DT'] == ''))
						{
							$dt = date_first($newArray['JL_POSTED_DT'], $newArray['EM_DT']);
							if ($dt)
							{
								if (	($in_sheets[$client2_id][$job_id]['V_L1_DT'] == '') ||
										($in_sheets[$client2_id][$job_id]['V_L1_DT'] < $dt)
									)
									$in_sheets[$client2_id][$job_id]['V_L1_DT'] = $dt;
							}
						}
					}
					if ($v_l2_s)
					{
						if ($newArray['LETTER_TYPE_ID'] == $id_LETTER_TYPE_letter_2)
							$in_sheets[$client2_id][$job_id]['V_L2_S']++;
					}
					if ($v_l2_dt)
					{
						# Return latest date
						if (($newArray['LETTER_TYPE_ID'] == $id_LETTER_TYPE_letter_2) &&
							1)#($in_sheets[$client2_id][$job_id]['V_L2_DT'] == ''))
						{
							$dt = date_first($newArray['JL_POSTED_DT'], $newArray['EM_DT']);
							if ($dt)
							{
								if (	($in_sheets[$client2_id][$job_id]['V_L2_DT'] == '') ||
										($in_sheets[$client2_id][$job_id]['V_L2_DT'] < $dt)
									)
									$in_sheets[$client2_id][$job_id]['V_L2_DT'] = $dt;
							}
						}
					}
					if ($v_l3_s)
					{
						if ($newArray['LETTER_TYPE_ID'] == $id_LETTER_TYPE_letter_3)
							$in_sheets[$client2_id][$job_id]['V_L3_S']++;
					}
					if ($v_l3_dt)
					{
						# Return latest date
						if (($newArray['LETTER_TYPE_ID'] == $id_LETTER_TYPE_letter_3) &&
							1)#($in_sheets[$client2_id][$job_id]['V_L3_DT'] == ''))
						{
							$dt = date_first($newArray['JL_POSTED_DT'], $newArray['EM_DT']);
							if ($dt)
							{
								if (	($in_sheets[$client2_id][$job_id]['V_L3_DT'] == '') ||
										($in_sheets[$client2_id][$job_id]['V_L3_DT'] < $dt)
									)
									$in_sheets[$client2_id][$job_id]['V_L3_DT'] = $dt;
							}
						}
					}
					if ($v_lc_s)
					{
						if ($newArray['LETTER_TYPE_ID'] == $id_LETTER_TYPE_contact)
							$in_sheets[$client2_id][$job_id]['V_LC_S']++;
					}
					if ($v_lc_dt)
					{
						# Return latest date
						if (($newArray['LETTER_TYPE_ID'] == $id_LETTER_TYPE_contact) &&
							1)#($in_sheets[$client2_id][$job_id]['V_LC_DT'] == ''))
						{
							$dt = date_first($newArray['JL_POSTED_DT'], $newArray['EM_DT']);
							if ($dt)
							{
								if (	($in_sheets[$client2_id][$job_id]['V_LC_DT'] == '') ||
										($in_sheets[$client2_id][$job_id]['V_LC_DT'] < $dt)
									)
									$in_sheets[$client2_id][$job_id]['V_LC_DT'] = $dt;
							}
						}
					}
					if ($v_ld_s)
					{
						if ($newArray['LETTER_TYPE_ID'] == $id_LETTER_TYPE_demand)
							$in_sheets[$client2_id][$job_id]['V_LD_S']++;
					}
					if ($v_ld_dt)
					{
						# Return latest date
						if (($newArray['LETTER_TYPE_ID'] == $id_LETTER_TYPE_demand) &&
							1)#($in_sheets[$client2_id][$job_id]['V_LD_DT'] == ''))
						{
							$dt = date_first($newArray['JL_POSTED_DT'], $newArray['EM_DT']);
							if ($dt)
							{
								if (	($in_sheets[$client2_id][$job_id]['V_LD_DT'] == '') ||
										($in_sheets[$client2_id][$job_id]['V_LD_DT'] < $dt)
									)
									$in_sheets[$client2_id][$job_id]['V_LD_DT'] = $dt;
							}
						}
					}
					if ($v_ls_t)
					{
						if (($newArray['LETTER_TYPE_ID'] == $id_LETTER_TYPE_trace_no) ||
							($newArray['LETTER_TYPE_ID'] == $id_LETTER_TYPE_trace_yes))
							$in_sheets[$client2_id][$job_id]['V_LS_T']++;
					}
					if ($v_ld_t)
					{
						# Return latest date
						if (  (($newArray['LETTER_TYPE_ID'] == $id_LETTER_TYPE_trace_no) ||
							   ($newArray['LETTER_TYPE_ID'] == $id_LETTER_TYPE_trace_yes)) &&
							1)#($in_sheets[$client2_id][$job_id]['V_LD_T'] == ''))
						{
							$dt = date_first($newArray['JL_POSTED_DT'], $newArray['EM_DT']);
							if ($dt)
							{
								if (	($in_sheets[$client2_id][$job_id]['V_LD_T'] == '') ||
										($in_sheets[$client2_id][$job_id]['V_LD_T'] < $dt)
									)
									$in_sheets[$client2_id][$job_id]['V_LD_T'] = $dt;
							}
						}
					}
					if ($v_ls_m)
					{
						if (($newArray['LETTER_TYPE_ID'] == $id_LETTER_TYPE_means_no) ||
							($newArray['LETTER_TYPE_ID'] == $id_LETTER_TYPE_means_yes))
							$in_sheets[$client2_id][$job_id]['V_LS_M']++;
					}
					if ($v_ld_m)
					{
						# Return latest date
						if (  (($newArray['LETTER_TYPE_ID'] == $id_LETTER_TYPE_means_no) ||
							   ($newArray['LETTER_TYPE_ID'] == $id_LETTER_TYPE_means_yes)) &&
							1)#($in_sheets[$client2_id][$job_id]['V_LD_M'] == ''))
						{
							$dt = date_first($newArray['JL_POSTED_DT'], $newArray['EM_DT']);
							if ($dt)
							{
								if (	($in_sheets[$client2_id][$job_id]['V_LD_M'] == '') ||
										($in_sheets[$client2_id][$job_id]['V_LD_M'] < $dt)
									)
									$in_sheets[$client2_id][$job_id]['V_LD_M'] = $dt;
							}
						}
					}
					if ($v_ls_rp)
					{
						if (($newArray['LETTER_TYPE_ID'] == $id_LETTER_TYPE_repo_no) ||
							($newArray['LETTER_TYPE_ID'] == $id_LETTER_TYPE_repo_yes))
							$in_sheets[$client2_id][$job_id]['V_LS_RP']++;
					}
					if ($v_ld_rp)
					{
						# Return latest date
						if (  (($newArray['LETTER_TYPE_ID'] == $id_LETTER_TYPE_repo_no) ||
							   ($newArray['LETTER_TYPE_ID'] == $id_LETTER_TYPE_repo_yes)) &&
							1)#($in_sheets[$client2_id][$job_id]['V_LD_RP'] == ''))
						{
							$dt = date_first($newArray['JL_POSTED_DT'], $newArray['EM_DT']);
							if ($dt)
							{
								if (	($in_sheets[$client2_id][$job_id]['V_LD_RP'] == '') ||
										($in_sheets[$client2_id][$job_id]['V_LD_RP'] < $dt)
									)
									$in_sheets[$client2_id][$job_id]['V_LD_RP'] = $dt;
							}
						}
					}
					if ($v_ls_ot)
					{
						if (($newArray['LETTER_TYPE_ID'] == $id_LETTER_TYPE_other_no) ||
							($newArray['LETTER_TYPE_ID'] == $id_LETTER_TYPE_other_yes))
							$in_sheets[$client2_id][$job_id]['V_LS_OT']++;
					}
					if ($v_ld_ot)
					{
						# Return latest date
						if (  (($newArray['LETTER_TYPE_ID'] == $id_LETTER_TYPE_other_no) ||
							   ($newArray['LETTER_TYPE_ID'] == $id_LETTER_TYPE_other_yes)) &&
							1)#($in_sheets[$client2_id][$job_id]['V_LD_OT'] == ''))
						{
							$dt = date_first($newArray['JL_POSTED_DT'], $newArray['EM_DT']);
							if ($dt)
							{
								if (	($in_sheets[$client2_id][$job_id]['V_LD_OT'] == '') ||
										($in_sheets[$client2_id][$job_id]['V_LD_OT'] < $dt)
									)
									$in_sheets[$client2_id][$job_id]['V_LD_OT'] = $dt;
							}
						}
					}
					if ($v_ls_sv)
					{
						if (($newArray['LETTER_TYPE_ID'] == $id_LETTER_TYPE_serv_no) ||
							($newArray['LETTER_TYPE_ID'] == $id_LETTER_TYPE_serv_yes))
							$in_sheets[$client2_id][$job_id]['V_LS_SV']++;
					}
					if ($v_ld_sv)
					{
						# Return latest date
						if (  (($newArray['LETTER_TYPE_ID'] == $id_LETTER_TYPE_serv_no) ||
							   ($newArray['LETTER_TYPE_ID'] == $id_LETTER_TYPE_serv_yes)) &&
							1)#($in_sheets[$client2_id][$job_id]['V_LD_SV'] == ''))
						{
							$dt = date_first($newArray['JL_POSTED_DT'], $newArray['EM_DT']);
							if ($dt)
							{
								if (	($in_sheets[$client2_id][$job_id]['V_LD_SV'] == '') ||
										($in_sheets[$client2_id][$job_id]['V_LD_SV'] < $dt)
									)
									$in_sheets[$client2_id][$job_id]['V_LD_SV'] = $dt;
							}
						}
					}
					if ($v_ls_r1)
					{
						if (($newArray['LETTER_TYPE_ID'] == $id_LETTER_TYPE_retrc1_no) ||
							($newArray['LETTER_TYPE_ID'] == $id_LETTER_TYPE_retrc1_yes) ||
							($newArray['LETTER_TYPE_ID'] == $id_LETTER_TYPE_retrc1_foc))
							$in_sheets[$client2_id][$job_id]['V_LS_R1']++;
					}
					if ($v_ld_r1)
					{
						# Return latest date
						if (  (($newArray['LETTER_TYPE_ID'] == $id_LETTER_TYPE_retrc1_no) ||
							   ($newArray['LETTER_TYPE_ID'] == $id_LETTER_TYPE_retrc1_yes) ||
							   ($newArray['LETTER_TYPE_ID'] == $id_LETTER_TYPE_retrc1_foc)) &&
							1)#($in_sheets[$client2_id][$job_id]['V_LD_R1'] == ''))
						{
							$dt = date_first($newArray['JL_POSTED_DT'], $newArray['EM_DT']);
							if ($dt)
							{
								if (	($in_sheets[$client2_id][$job_id]['V_LD_R1'] == '') ||
										($in_sheets[$client2_id][$job_id]['V_LD_R1'] < $dt)
									)
									$in_sheets[$client2_id][$job_id]['V_LD_R1'] = $dt;
							}
						}
					}
					if ($v_ls_r2)
					{
						if (($newArray['LETTER_TYPE_ID'] == $id_LETTER_TYPE_retrc2_no) ||
							($newArray['LETTER_TYPE_ID'] == $id_LETTER_TYPE_retrc2_yes) ||
							($newArray['LETTER_TYPE_ID'] == $id_LETTER_TYPE_retrc2_foc))
							$in_sheets[$client2_id][$job_id]['V_LS_R2']++;
					}
					if ($v_ld_r2)
					{
						# Return latest date
						if (  (($newArray['LETTER_TYPE_ID'] == $id_LETTER_TYPE_retrc2_no) ||
							   ($newArray['LETTER_TYPE_ID'] == $id_LETTER_TYPE_retrc2_yes) ||
							   ($newArray['LETTER_TYPE_ID'] == $id_LETTER_TYPE_retrc2_foc)) &&
							1)#($in_sheets[$client2_id][$job_id]['V_LD_R2'] == ''))
						{
							$dt = date_first($newArray['JL_POSTED_DT'], $newArray['EM_DT']);
							if ($dt)
							{
								if (	($in_sheets[$client2_id][$job_id]['V_LD_R2'] == '') ||
										($in_sheets[$client2_id][$job_id]['V_LD_R2'] < $dt)
									)
									$in_sheets[$client2_id][$job_id]['V_LD_R2'] = $dt;
							}
						}
					}
					if ($v_ls_r3)
					{
						if (($newArray['LETTER_TYPE_ID'] == $id_LETTER_TYPE_retrc3_no) ||
							($newArray['LETTER_TYPE_ID'] == $id_LETTER_TYPE_retrc3_yes) ||
							($newArray['LETTER_TYPE_ID'] == $id_LETTER_TYPE_retrc3_foc))
							$in_sheets[$client2_id][$job_id]['V_LS_R3']++;
					}
					if ($v_ld_r3)
					{
						# Return latest date
						if (  (($newArray['LETTER_TYPE_ID'] == $id_LETTER_TYPE_retrc3_no) ||
							   ($newArray['LETTER_TYPE_ID'] == $id_LETTER_TYPE_retrc3_yes) ||
							   ($newArray['LETTER_TYPE_ID'] == $id_LETTER_TYPE_retrc3_foc)) &&
							1)#($in_sheets[$client2_id][$job_id]['V_LD_R3'] == ''))
						{
							$dt = date_first($newArray['JL_POSTED_DT'], $newArray['EM_DT']);
							if ($dt)
							{
								if (	($in_sheets[$client2_id][$job_id]['V_LD_R3'] == '') ||
										($in_sheets[$client2_id][$job_id]['V_LD_R3'] < $dt)
									)
									$in_sheets[$client2_id][$job_id]['V_LD_R3'] = $dt;
							}
						}
					}
					if ($v_ls_tc)
					{
						if (($newArray['LETTER_TYPE_ID'] == $id_LETTER_TYPE_tc_no) ||
							($newArray['LETTER_TYPE_ID'] == $id_LETTER_TYPE_tc_yes))
							$in_sheets[$client2_id][$job_id]['V_LS_TC']++;
					}
					if ($v_ld_tc)
					{
						# Return latest date
						if (  (($newArray['LETTER_TYPE_ID'] == $id_LETTER_TYPE_tc_no) ||
							   ($newArray['LETTER_TYPE_ID'] == $id_LETTER_TYPE_tc_yes)) &&
							1)#($in_sheets[$client2_id][$job_id]['V_LD_TC'] == ''))
						{
							$dt = date_first($newArray['JL_POSTED_DT'], $newArray['EM_DT']);
							if ($dt)
							{
								if (	($in_sheets[$client2_id][$job_id]['V_LD_TC'] == '') ||
										($in_sheets[$client2_id][$job_id]['V_LD_TC'] < $dt)
									)
									$in_sheets[$client2_id][$job_id]['V_LD_TC'] = $dt;
							}
						}
					}
					if ($v_ls_tm)
					{
						if (($newArray['LETTER_TYPE_ID'] == $id_LETTER_TYPE_tm_no) ||
							($newArray['LETTER_TYPE_ID'] == $id_LETTER_TYPE_tm_yes))
							$in_sheets[$client2_id][$job_id]['V_LS_TM']++;
					}
					if ($v_ld_tm)
					{
						# Return latest date
						if (  (($newArray['LETTER_TYPE_ID'] == $id_LETTER_TYPE_tm_no) ||
							   ($newArray['LETTER_TYPE_ID'] == $id_LETTER_TYPE_tm_yes)) &&
							1)#($in_sheets[$client2_id][$job_id]['V_LD_TM'] == ''))
						{
							$dt = date_first($newArray['JL_POSTED_DT'], $newArray['EM_DT']);
							if ($dt)
							{
								if (	($in_sheets[$client2_id][$job_id]['V_LD_TM'] == '') ||
										($in_sheets[$client2_id][$job_id]['V_LD_TM'] < $dt)
									)
									$in_sheets[$client2_id][$job_id]['V_LD_TM'] = $dt;
							}
						}
					}
					if ($v_ls_at)
					{
						if (($newArray['LETTER_TYPE_ID'] == $id_LETTER_TYPE_attend_no) ||
							($newArray['LETTER_TYPE_ID'] == $id_LETTER_TYPE_attend_yes))
							$in_sheets[$client2_id][$job_id]['V_LS_AT']++;
					}
					if ($v_ld_at)
					{
						# Return latest date
						if (  (($newArray['LETTER_TYPE_ID'] == $id_LETTER_TYPE_attend_no) ||
							   ($newArray['LETTER_TYPE_ID'] == $id_LETTER_TYPE_attend_yes)) &&
							1)#($in_sheets[$client2_id][$job_id]['V_LD_AT'] == ''))
						{
							$dt = date_first($newArray['JL_POSTED_DT'], $newArray['EM_DT']);
							if ($dt)
							{
								if (	($in_sheets[$client2_id][$job_id]['V_LD_AT'] == '') ||
										($in_sheets[$client2_id][$job_id]['V_LD_AT'] < $dt)
									)
									$in_sheets[$client2_id][$job_id]['V_LD_AT'] = $dt;
							}
						}
					}
				}
				#dprint("JOB_ID $job_id: found $letter_count letters of types $letter_types");#
			}
		}
	} # Check for Letters attached to jobs

	if ($debug) dlog("Done Check for Letters attached to jobs");

	#$debug_dates=true;
	if ($v_collected || $v_outst || $v_cspent || $v_num_pay || $v_ret_total ||
		$v_col_total || $v_col_dir || $v_col_tov || $v_col_for || $v_ret_per ||
		$v_paid_full || $v_pay_last || $v_last_amt || $v_next_dt)
	{
		# Add Collections to jobs
		foreach ($in_sheets as $client2_id => $jobs) # go through input sheets
		{
			foreach ($jobs as $job_id => $job_info)
			{
				if ($v_collected || $v_outst || $v_cspent || $v_num_pay || $v_ret_total || $v_paid_full ||
					$v_pay_last || $v_last_amt || $v_next_dt)
				{
					# Need payments info on job

					$sql = "SELECT COL_AMT_RX, COL_PAYMENT_ROUTE_ID" .
							(($v_pay_last || $v_last_amt || $v_next_dt) ? ", COL_DT_RX" : '') .
							($v_last_amt ? ", COL_AMT_RX" : '') .
							" FROM JOB_PAYMENT WHERE JOB_ID=$job_id AND (OBSOLETE=$sqlFalse)";
					#dprint(str_replace(chr(10), '<br>', $sql));#
					#if ($debug) dlog("sql/6: $sql");
					sql_execute($sql);
					while (($newArray = sql_fetch_assoc()) != false)
					{
						$temp = floatval($newArray['COL_AMT_RX']);
						if ($v_collected || $v_next_dt)
							$in_sheets[$client2_id][$job_id]['V_COLLECTED'] += $temp;
						if ($v_outst)
							$in_sheets[$client2_id][$job_id]['V_OUTST'] -= $temp;
						if ($v_cspent && ($newArray['COL_PAYMENT_ROUTE_ID'] == $id_ROUTE_cspent))
							$in_sheets[$client2_id][$job_id]['V_CSPENT'] += $temp;
						if ($v_num_pay)
							$in_sheets[$client2_id][$job_id]['V_NUM_PAY']++;
						if ($v_ret_total && ($newArray['COL_PAYMENT_ROUTE_ID'] == $id_ROUTE_cspent))
							$in_sheets[$client2_id][$job_id]['V_RET_TOTAL'] += $temp;
						if ($v_paid_full)
							$in_sheets[$client2_id][$job_id]['V_PAID_FULL'] -= $temp;
						if ($v_pay_last || $v_last_amt || $v_next_dt)
						{
							if ( ($in_sheets[$client2_id][$job_id]['V_PAY_LAST'] == '') ||
								 ($in_sheets[$client2_id][$job_id]['V_PAY_LAST'] < $newArray['COL_DT_RX'])
							   )
							{
								$in_sheets[$client2_id][$job_id]['V_PAY_LAST'] = $newArray['COL_DT_RX'];
								if ($v_last_amt)
									$in_sheets[$client2_id][$job_id]['V_LAST_AMT'] = $newArray['COL_AMT_RX'];
							}
						}
					}
					if ($v_paid_full)
					{
						if ($in_sheets[$client2_id][$job_id]['V_PAID_FULL'] <= 0)
							$in_sheets[$client2_id][$job_id]['V_PAID_FULL'] = 'Yes';
						else
							$in_sheets[$client2_id][$job_id]['V_PAID_FULL'] = 'No';
					}
					if ($v_next_dt)
					{
						$freq = $in_sheets[$client2_id][$job_id]['V_NEXT_DT'][0];
						$start = $in_sheets[$client2_id][$job_id]['V_NEXT_DT'][1];
						$owed = round(floatval($in_sheets[$client2_id][$job_id]['V_NEXT_DT'][2]), 2);
						$paid = round(floatval($in_sheets[$client2_id][$job_id]['V_COLLECTED']), 2);
						$closed = $in_sheets[$client2_id][$job_id]['V_CLOSED'];

						#if ($job_id == 479770)
						#	dprint("freq=$freq, start=$start, owed=$owed, paid=$paid");#
						if ((!$closed) && ($paid < $owed))
						{
							$days = 0;
							switch ($freq)
							{
								case 'I': $days = 1; break;
								case 'D': $days = 1; break;
								case 'W': $days = 7; break;
								case 'F': $days = 14; break;
								case 'M': $days = 30; break;
								case 'T': $days = 60; break;
								case 'Q': $days = 90; break;
								default : $days = 1; break;
							}
							# Get the date of the next due payment
							if ($in_sheets[$client2_id][$job_id]['V_PAY_LAST'])
							{
								$next_dt_ep = date_to_epoch($in_sheets[$client2_id][$job_id]['V_PAY_LAST'], true, $days);
								$in_sheets[$client2_id][$job_id]['V_NEXT_DT'] = $next_dt_ep;
								#$in_sheets[$client2_id][$job_id]['V_NEXT_DT'] = date_from_epoch(false, $next_dt_ep, false, false, true);
								#if ($job_id == 479770)
								#if ($debug_dates)
								#	dprint("LAST=\"{$in_sheets[$client2_id][$job_id]['V_PAY_LAST']}\", days=$days, next_ep=$next_dt_ep, NEXT_DT=\"{$in_sheets[$client2_id][$job_id]['V_NEXT_DT']}\"");#
							}
							else
								$in_sheets[$client2_id][$job_id]['V_NEXT_DT'] = date_to_epoch($start);
						}
						else
							$in_sheets[$client2_id][$job_id]['V_NEXT_DT'] = '';
					}
				} # if need payments info on job

				if ($v_col_total || $v_col_dir || $v_col_tov || $v_col_for || $v_ret_per)
				{
					$where = array("OBSOLETE=$sqlFalse", "JOB_ID=$job_id");
					if ($run_p1_dt_fr_sql)
						$where[] = "$run_p1_dt_fr_sql <= COL_DT_RX";
					if ($run_p1_dt_to_plus1_sql)
						$where[] = "COL_DT_RX < $run_p1_dt_to_plus1_sql";
					$sql = "SELECT COL_AMT_RX, COL_PAYMENT_ROUTE_ID FROM JOB_PAYMENT
							WHERE (" . implode(') AND (', $where) . ")";
					#dprint(str_replace(chr(10), '<br>', $sql));#
					#if ($debug) dlog("sql/7: $sql");
					sql_execute($sql);
					while (($newArray = sql_fetch_assoc()) != false)
					{
						$temp = floatval($newArray['COL_AMT_RX']);
						if ($v_col_total)
							$in_sheets[$client2_id][$job_id]['V_COL_TOTAL'] += $temp;
						if ($v_col_dir && ($newArray['COL_PAYMENT_ROUTE_ID'] == $id_ROUTE_direct))
							$in_sheets[$client2_id][$job_id]['V_COL_DIR'] += $temp;
						if ($v_col_tov && ($newArray['COL_PAYMENT_ROUTE_ID'] == $id_ROUTE_tous))
							$in_sheets[$client2_id][$job_id]['V_COL_TOV'] += $temp;
						if ($v_col_for && ($newArray['COL_PAYMENT_ROUTE_ID'] == $id_ROUTE_fwd))
							$in_sheets[$client2_id][$job_id]['V_COL_FOR'] += $temp;
						if ($v_ret_per && ($newArray['COL_PAYMENT_ROUTE_ID'] == $id_ROUTE_cspent))
							$in_sheets[$client2_id][$job_id]['V_RET_PER'] += $temp;
					}
				}
			} # foreach $jobs
		} # foreach $in_sheets
	} # add collections to jobs

	if ($debug) dlog("count(client_reports)=" . count($client_reports));

	if ($v_rep_last)
	{
		# Last Client Report date
		foreach ($client_reports as $client2_id => $temp)
		{
			$sql = "SELECT MAX(REPORT_SENT_DT) FROM CLIENT_REPORT WHERE CLIENT2_ID=$client2_id";
			#if ($debug) dlog("sql/8: $sql");
			sql_execute($sql);
			while (($newArray = sql_fetch()) != false)
				$client_reports[$client2_id] = $newArray[0];
		}
	} # $v_rep_last

	#dprint("Found " . count($in_sheets) . " sheets/clients."); #
	#dprint("in_sheets=" . print_r($in_sheets,1)); #
	# ---------------------------------------------------------------------------------------------

	# ---- Create the printable report data -------------------------------------------------------

	if ($debug) dlog("Create the printable report data");

	# Create a new set of sheets: $out_sheets
	# Each column of $out_sheets in an array where 'V' is the value and 'D' is the data type;
	#    data types: T=text(left-aligned), TR=text(right-aligned), I=int, F=float. Date and VilNo and SequenceNo will be TR.
	$out_sheets = array(); # indexed by CLIENT2_ID, one sheet per client
	foreach ($clients as $client2_id)
		$out_sheets[$client2_id] = array(); # for $multi_sheets: each sheet will have a list of jobs; each job will be indexed by JOB_ID
	$out_sheets['SINGLE'] = array(); # for !$multi_sheets: list of lines

	$headings = array(); # each sheet will have the same headings
	$init_headings = true;
	if (!$multi_sheet)
	{
		$headings[] = 'Client'; # client code
		$headings[] = 'Client Name';
	}

	if ($debug) dlog("Working on in_sheets...");
	# For each client:
	foreach ($in_sheets as $client2_id => $jobs) # go through input sheets
	{
		# Working on one client

		$totals = array(); # each client has its own $totals
		$init_totals = true;

		foreach ($jobs as $job_id => $job_info)
		{
			$out_sheets[$client2_id][$job_id] = array(); # one line of output: date-info

			foreach ($fields as $fld)
			{
				switch ($fld)
				{
					case 'V_DT_RX':
						if ($init_headings)
							$headings[] = 'Date Job Received';
						if ($init_totals)
							$totals['V_DT_RX'] = array('V' => 0, 'D' => 'X');
						$out_sheets[$client2_id][$job_id][] = array('V' => date_for_sql($job_info['V_DT_RX'], true, false), 'D' => 'TR');
						$totals['V_DT_RX']['V']++;
						break;
					case 'V_VILNO':
						if ($init_headings)
							$headings[] = 'Vilcol Reference';
						if ($init_totals)
							$totals['V_VILNO'] = array('V' => 0, 'D' => 'X');
						$out_sheets[$client2_id][$job_id][] = array('V' => $job_info['V_VILNO'], 'D' => 'TR');
						$totals['V_VILNO']['V']++;
						break;
					case 'V_CLIREF':
						if ($init_headings)
							$headings[] = 'Client Reference';
						if ($init_totals)
							$totals['V_CLIREF'] = array('V' => 0, 'D' => 'X');
						$out_sheets[$client2_id][$job_id][] = array('V' => $job_info['V_CLIREF'], 'D' => 'T');
						$totals['V_CLIREF']['V']++;
						break;
					case 'V_LNAME':
						if ($init_headings)
							$headings[] = 'Last Name';
						if ($init_totals)
							$totals['V_LNAME'] = array('V' => 0, 'D' => 'X');
						$out_sheets[$client2_id][$job_id][] = array('V' => $job_info['V_LNAME'], 'D' => 'T');
						$totals['V_LNAME']['V']++;
						break;
					case 'V_FNAME':
						if ($init_headings)
							$headings[] = 'First Name';
						if ($init_totals)
							$totals['V_FNAME'] = array('V' => 0, 'D' => 'X');
						$out_sheets[$client2_id][$job_id][] = array('V' => $job_info['V_FNAME'], 'D' => 'T');
						$totals['V_FNAME']['V']++;
						break;
					case 'V_TITLE':
						if ($init_headings)
							$headings[] = 'Title';
						if ($init_totals)
							$totals['V_TITLE'] = array('V' => 0, 'D' => 'X');
						$out_sheets[$client2_id][$job_id][] = array('V' => $job_info['V_TITLE'], 'D' => 'T');
						$totals['V_TITLE']['V']++;
						break;
					case 'V_COMP':
						if ($init_headings)
							$headings[] = 'Company Name';
						if ($init_totals)
							$totals['V_COMP'] = array('V' => 0, 'D' => 'X');
						$out_sheets[$client2_id][$job_id][] = array('V' => $job_info['V_COMP'], 'D' => 'T');
						$totals['V_COMP']['V']++;
						break;
					case 'V_JTYPE':
						if ($init_headings)
							$headings[] = 'Job Type';
						if ($init_totals)
							$totals['V_JTYPE'] = array('V' => 0, 'D' => 'X');
						$out_sheets[$client2_id][$job_id][] = array('V' => $job_info['V_JTYPE'], 'D' => 'T');
						$totals['V_JTYPE']['V']++;
						break;
					case 'V_SUCCESS':
						if ($init_headings)
							$headings[] = 'Success?';
						if ($init_totals)
							$totals['V_SUCCESS'] = array('V' => 0, 'D' => 'X');
						if ($job_info['V_SUCCESS'] == 1)
							$success_txt = 'Success';
						elseif ($job_info['V_SUCCESS'] == 0)
							$success_txt = 'No';
						elseif ($job_info['V_SUCCESS'] == -1)
							$success_txt = 'FOC';
						else
							$success_txt = '-';
						$out_sheets[$client2_id][$job_id][] = array('V' => $success_txt, 'D' => 'T');
						$totals['V_SUCCESS']['V']++;
						break;
					case 'V_STATUS':
						if ($init_headings)
							$headings[] = 'Status of job';
						if ($init_totals)
							$totals['V_STATUS'] = array('V' => 0, 'D' => 'X');
						$out_sheets[$client2_id][$job_id][] = array('V' => $job_info['V_STATUS'], 'D' => 'T');
						$totals['V_STATUS']['V']++;
						break;
					case 'V_OWED':
						if ($init_headings)
							$headings[] = 'Amount Owed';
						if ($init_totals)
							$totals['V_OWED'] = array('V' => 0.0, 'D' => 'CU');
						$temp = floatval($job_info['V_OWED']);
						$out_sheets[$client2_id][$job_id][] = array('V' => $temp, 'D' => 'CU');
						$totals['V_OWED']['V'] += $temp;
						break;
					case 'V_COLLECTED':
						if ($init_headings)
							$headings[] = 'Amount Collected';
						if ($init_totals)
							$totals['V_COLLECTED'] = array('V' => 0.0, 'D' => 'CU');
						$temp = floatval($job_info['V_COLLECTED']);
						$out_sheets[$client2_id][$job_id][] = array('V' => $temp, 'D' => 'CU');
						$totals['V_COLLECTED']['V'] += $temp;
						break;
					case 'V_OUTST':
						if ($init_headings)
							$headings[] = 'Amount Outstanding';
						if ($init_totals)
							$totals['V_OUTST'] = array('V' => 0.0, 'D' => 'CU');
						$temp = floatval($job_info['V_OUTST']);
						$out_sheets[$client2_id][$job_id][] = array('V' => $temp, 'D' => 'CU');
						$totals['V_OUTST']['V'] += $temp;
						break;
					case 'V_CSPENT':
						if ($init_headings)
							$headings[] = 'C/Spent';
						if ($init_totals)
							$totals['V_CSPENT'] = array('V' => 0.0, 'D' => 'CU');
						$temp = floatval($job_info['V_CSPENT']);
						$out_sheets[$client2_id][$job_id][] = array('V' => $temp, 'D' => 'CU');
						$totals['V_CSPENT']['V'] += $temp;
						break;
					case 'V_NUM_PAY':
						if ($init_headings)
							$headings[] = 'Number of Payments';
						if ($init_totals)
							$totals['V_NUM_PAY'] = array('V' => 0, 'D' => 'I');
						$temp = intval($job_info['V_NUM_PAY']);
						$out_sheets[$client2_id][$job_id][] = array('V' => $temp, 'D' => 'I');
						$totals['V_NUM_PAY']['V'] += $temp;
						break;
					case 'V_RET_TOTAL':
						if ($init_headings)
							$headings[] = 'Total Amt. of Ret. Payments';
						if ($init_totals)
							$totals['V_RET_TOTAL'] = array('V' => 0.0, 'D' => 'CU');
						$temp = floatval($job_info['V_RET_TOTAL']);
						$out_sheets[$client2_id][$job_id][] = array('V' => $temp, 'D' => 'CU');
						$totals['V_RET_TOTAL']['V'] += $temp;
						break;
					case 'V_COL_TOTAL':
						if ($init_headings)
							$headings[] = 'Amt. Col. in Period (Total)';
						if ($init_totals)
							$totals['V_COL_TOTAL'] = array('V' => 0.0, 'D' => 'CU');
						$temp = floatval($job_info['V_COL_TOTAL']);
						$out_sheets[$client2_id][$job_id][] = array('V' => $temp, 'D' => 'CU');
						$totals['V_COL_TOTAL']['V'] += $temp;
						break;
					case 'V_COL_DIR':
						if ($init_headings)
							$headings[] = 'Amt. Col. in Period (Direct)';
						if ($init_totals)
							$totals['V_COL_DIR'] = array('V' => 0.0, 'D' => 'CU');
						$temp = floatval($job_info['V_COL_DIR']);
						$out_sheets[$client2_id][$job_id][] = array('V' => $temp, 'D' => 'CU');
						$totals['V_COL_DIR']['V'] += $temp;
						break;
					case 'V_COL_TOV':
						if ($init_headings)
							$headings[] = 'Amt. Col. in Period (To Vilcol)';
						if ($init_totals)
							$totals['V_COL_TOV'] = array('V' => 0.0, 'D' => 'CU');
						$temp = floatval($job_info['V_COL_TOV']);
						$out_sheets[$client2_id][$job_id][] = array('V' => $temp, 'D' => 'CU');
						$totals['V_COL_TOV']['V'] += $temp;
						break;
					case 'V_COL_FOR':
						if ($init_headings)
							$headings[] = 'Amt. Col. in Period (For.)';
						if ($init_totals)
							$totals['V_COL_FOR'] = array('V' => 0.0, 'D' => 'CU');
						$temp = floatval($job_info['V_COL_FOR']);
						$out_sheets[$client2_id][$job_id][] = array('V' => $temp, 'D' => 'CU');
						$totals['V_COL_FOR']['V'] += $temp;
						break;
					case 'V_RET_PER':
						if ($init_headings)
							$headings[] = 'Amt. Returned in Period';
						if ($init_totals)
							$totals['V_RET_PER'] = array('V' => 0.0, 'D' => 'CU');
						$temp = floatval($job_info['V_RET_PER']);
						$out_sheets[$client2_id][$job_id][] = array('V' => $temp, 'D' => 'CU');
						$totals['V_RET_PER']['V'] += $temp;
						break;
					case 'V_PAID_FULL':
						if ($init_headings)
							$headings[] = 'Paid in Full?';
						if ($init_totals)
							$totals['V_PAID_FULL'] = array('V' => 0, 'D' => 'X');
						$out_sheets[$client2_id][$job_id][] = array('V' => $job_info['V_PAID_FULL'], # Yes or No
																	'D' => 'T');
						$totals['V_PAID_FULL']['V']++;
						break;
					case 'V_PAY_LAST':
						if ($init_headings)
							$headings[] = 'Last Payment Date';
						if ($init_totals)
							$totals['V_PAY_LAST'] = array('V' => 0, 'D' => 'X');
						$out_sheets[$client2_id][$job_id][] = array('V' => date_for_sql($job_info['V_PAY_LAST'], true, false), 'D' => 'TR');
						$totals['V_PAY_LAST']['V']++;
						break;
					case 'V_CLOSED':
						if ($init_headings)
							$headings[] = 'Date Job Closed';
						if ($init_totals)
							$totals['V_CLOSED'] = array('V' => 0, 'D' => 'X');
						if ($job_info['V_CLOSED'] == '')
							$date_closed = '';
						elseif ($job_info['V_CLOSED'] == 'XX')
							$date_closed = '(unknown)';
						else
							$date_closed = date_for_sql($job_info['V_CLOSED'], true, false);
						$out_sheets[$client2_id][$job_id][] = array('V' => $date_closed, 'D' => 'TR');
						$totals['V_CLOSED']['V']++;
						break;
					case 'V_HOME_1':
						if ($init_headings)
							$headings[] = 'Home Address [1]';
						if ($init_totals)
							$totals['V_HOME_1'] = array('V' => 0, 'D' => 'X');
						$out_sheets[$client2_id][$job_id][] = array('V' => $job_info['V_HOME_1'], 'D' => 'T');
						$totals['V_HOME_1']['V']++;
						break;
					case 'V_HOME_2':
						if ($init_headings)
							$headings[] = 'Home Address [2]';
						if ($init_totals)
							$totals['V_HOME_2'] = array('V' => 0, 'D' => 'X');
						$out_sheets[$client2_id][$job_id][] = array('V' => $job_info['V_HOME_2'], 'D' => 'T');
						$totals['V_HOME_2']['V']++;
						break;
					case 'V_HOME_3':
						if ($init_headings)
							$headings[] = 'Home Address [3]';
						if ($init_totals)
							$totals['V_HOME_3'] = array('V' => 0, 'D' => 'X');
						$out_sheets[$client2_id][$job_id][] = array('V' => $job_info['V_HOME_3'], 'D' => 'T');
						$totals['V_HOME_3']['V']++;
						break;
					case 'V_HOME_4':
						if ($init_headings)
							$headings[] = 'Home Address [4]';
						if ($init_totals)
							$totals['V_HOME_4'] = array('V' => 0, 'D' => 'X');
						$out_sheets[$client2_id][$job_id][] = array('V' => $job_info['V_HOME_4'], 'D' => 'T');
						$totals['V_HOME_4']['V']++;
						break;
					case 'V_HOME_5':
						if ($init_headings)
							$headings[] = 'Home Address [5]';
						if ($init_totals)
							$totals['V_HOME_5'] = array('V' => 0, 'D' => 'X');
						$out_sheets[$client2_id][$job_id][] = array('V' => $job_info['V_HOME_5'], 'D' => 'T');
						$totals['V_HOME_5']['V']++;
						break;
					case 'V_HOME_PC':
						if ($init_headings)
							$headings[] = 'Post Code';
						if ($init_totals)
							$totals['V_HOME_PC'] = array('V' => 0, 'D' => 'X');
						$out_sheets[$client2_id][$job_id][] = array('V' => $job_info['V_HOME_PC'], 'D' => 'T');
						$totals['V_HOME_PC']['V']++;
						break;
					case 'V_HOME_T':
						if ($init_headings)
							$headings[] = 'Home Phone No';
						if ($init_totals)
							$totals['V_HOME_T'] = array('V' => 0, 'D' => 'X');
						$out_sheets[$client2_id][$job_id][] = array('V' => $job_info['V_HOME_T'], 'D' => 'T');
						$totals['V_HOME_T']['V']++;
						break;
					case 'V_PROP':
						if ($init_headings)
							$headings[] = 'Property Type';
						if ($init_totals)
							$totals['V_PROP'] = array('V' => 0, 'D' => 'X');
						$prop_type = (($job_info['V_PROP'] == '') ? '-' : property_type_txt($job_info['V_PROP']));
						$out_sheets[$client2_id][$job_id][] = array('V' => $prop_type, 'D' => 'T');
						$totals['V_PROP']['V']++;
						break;
					case 'V_L1_S':
						if ($init_headings)
							$headings[] = 'Letter 1 Sent ?';
						if ($init_totals)
							$totals['V_L1_S'] = array('V' => 0, 'D' => 'X');
						$sent = ((0 < $job_info['V_L1_S']) ? 'Yes' : 'No');
						$out_sheets[$client2_id][$job_id][] = array('V' => $sent, 'D' => 'T');
						$totals['V_L1_S']['V']++;
						break;
					case 'V_L1_DT':
						if ($init_headings)
							$headings[] = 'Letter 1 Date';
						if ($init_totals)
							$totals['V_L1_DT'] = array('V' => 0, 'D' => 'X');
						$date = ($job_info['V_L1_DT'] ? date_for_sql($job_info['V_L1_DT'], true, false) : '-');
						$out_sheets[$client2_id][$job_id][] = array('V' => $date, 'D' => 'TR');
						$totals['V_L1_DT']['V']++;
						break;
					case 'V_L2_S':
						if ($init_headings)
							$headings[] = 'Letter 2 Sent ?';
						if ($init_totals)
							$totals['V_L2_S'] = array('V' => 0, 'D' => 'X');
						$sent = ((0 < $job_info['V_L2_S']) ? 'Yes' : 'No');
						$out_sheets[$client2_id][$job_id][] = array('V' => $sent, 'D' => 'T');
						$totals['V_L2_S']['V']++;
						break;
					case 'V_L2_DT':
						if ($init_headings)
							$headings[] = 'Letter 2 Date';
						if ($init_totals)
							$totals['V_L2_DT'] = array('V' => 0, 'D' => 'X');
						$date = ($job_info['V_L2_DT'] ? date_for_sql($job_info['V_L2_DT'], true, false) : '-');
						$out_sheets[$client2_id][$job_id][] = array('V' => $date, 'D' => 'TR');
						$totals['V_L2_DT']['V']++;
						break;
					case 'V_L3_S':
						if ($init_headings)
							$headings[] = 'Letter 3 Sent ?';
						if ($init_totals)
							$totals['V_L3_S'] = array('V' => 0, 'D' => 'X');
						$sent = ((0 < $job_info['V_L3_S']) ? 'Yes' : 'No');
						$out_sheets[$client2_id][$job_id][] = array('V' => $sent, 'D' => 'T');
						$totals['V_L3_S']['V']++;
						break;
					case 'V_L3_DT':
						if ($init_headings)
							$headings[] = 'Letter 3 Date';
						if ($init_totals)
							$totals['V_L3_DT'] = array('V' => 0, 'D' => 'X');
						$date = ($job_info['V_L3_DT'] ? date_for_sql($job_info['V_L3_DT'], true, false) : '-');
						$out_sheets[$client2_id][$job_id][] = array('V' => $date, 'D' => 'TR');
						$totals['V_L3_DT']['V']++;
						break;
					case 'V_LC_S':
						if ($init_headings)
							$headings[] = 'Contact Letter Sent ?';
						if ($init_totals)
							$totals['V_LC_S'] = array('V' => 0, 'D' => 'X');
						$sent = ((0 < $job_info['V_LC_S']) ? 'Yes' : 'No');
						$out_sheets[$client2_id][$job_id][] = array('V' => $sent, 'D' => 'T');
						$totals['V_LC_S']['V']++;
						break;
					case 'V_LC_DT':
						if ($init_headings)
							$headings[] = 'Contact Letter Date';
						if ($init_totals)
							$totals['V_LC_DT'] = array('V' => 0, 'D' => 'X');
						$date = ($job_info['V_LC_DT'] ? date_for_sql($job_info['V_LC_DT'], true, false) : '-');
						$out_sheets[$client2_id][$job_id][] = array('V' => $date, 'D' => 'TR');
						$totals['V_LC_DT']['V']++;
						break;
					case 'V_LD_S':
						if ($init_headings)
							$headings[] = 'Demand Letter Sent ?';
						if ($init_totals)
							$totals['V_LD_S'] = array('V' => 0, 'D' => 'X');
						$sent = ((0 < $job_info['V_LD_S']) ? 'Yes' : 'No');
						$out_sheets[$client2_id][$job_id][] = array('V' => $sent, 'D' => 'T');
						$totals['V_LD_S']['V']++;
						break;
					case 'V_LD_DT':
						if ($init_headings)
							$headings[] = 'Demand Letter Date';
						if ($init_totals)
							$totals['V_LD_DT'] = array('V' => 0, 'D' => 'X');
						$date = ($job_info['V_LD_DT'] ? date_for_sql($job_info['V_LD_DT'], true, false) : '-');
						$out_sheets[$client2_id][$job_id][] = array('V' => $date, 'D' => 'TR');
						$totals['V_LD_DT']['V']++;
						break;
					case 'V_REP_LAST':
						if ($init_headings)
							$headings[] = 'Last Client Rep.';
						if ($init_totals)
							$totals['V_REP_LAST'] = array('V' => 0, 'D' => 'X');
						$date = ($client_reports[$client2_id] ? date_for_sql($client_reports[$client2_id], true, false) : '-');
						$out_sheets[$client2_id][$job_id][] = array('V' => $date, 'D' => 'TR');
						$totals['V_REP_LAST']['V']++;
						break;
					case 'V_UPD_LAST':
						if ($init_headings)
							$headings[] = 'Last Record Update';
						if ($init_totals)
							$totals['V_UPD_LAST'] = array('V' => 0, 'D' => 'X');
						$out_sheets[$client2_id][$job_id][] = array('V' => date_for_sql($job_info['V_UPD_LAST'], true, false), 'D' => 'TR');
						$totals['V_UPD_LAST']['V']++;
						break;
					case 'V_REG_AM':
						if ($init_headings)
							$headings[] = 'Regular Payment Amount';
						if ($init_totals)
							$totals['V_REG_AM'] = array('V' => 0.0, 'D' => 'CU');
						$temp = floatval($job_info['V_REG_AM']);
						if ($temp != 0)
							$out_sheets[$client2_id][$job_id][] = array('V' => $temp, 'D' => 'CU');
						else
							$out_sheets[$client2_id][$job_id][] = array('V' => '-', 'D' => 'TR');
						$totals['V_REG_AM']['V'] += $temp;
						break;
					case 'V_REG_INT':
						if ($init_headings)
							$headings[] = 'Regular Payment Interval';
						if ($init_totals)
							$totals['V_REG_INT'] = array('V' => 0, 'D' => 'X');
						$out_sheets[$client2_id][$job_id][] = array('V' => instal_freq_from_code($job_info['V_REG_INT']), 'D' => 'T');
						$totals['V_REG_INT']['V']++;
						break;
					case 'V_REG_START':
						if ($init_headings)
							$headings[] = 'Regular Payment Start Date';
						if ($init_totals)
							$totals['V_REG_START'] = array('V' => 0, 'D' => 'X');
						$out_sheets[$client2_id][$job_id][] = array('V' => date_for_sql($job_info['V_REG_START'], true, false), 'D' => 'TR');
						$totals['V_REG_START']['V']++;
						break;
					case 'V_REG_METH':
						if ($init_headings)
							$headings[] = 'Regular Payment Method';
						if ($init_totals)
							$totals['V_REG_METH'] = array('V' => 0, 'D' => 'X');
						$out_sheets[$client2_id][$job_id][] = array('V' => $job_info['V_REG_METH'], 'D' => 'T');
						$totals['V_REG_METH']['V']++;
						break;
					case 'V_LAST_AMT':
						if ($init_headings)
							$headings[] = 'Last Payment Amount';
						if ($init_totals)
							$totals['V_LAST_AMT'] = array('V' => 0.0, 'D' => 'CU');
						$temp = floatval($job_info['V_LAST_AMT']);
						if ($temp != 0)
							$out_sheets[$client2_id][$job_id][] = array('V' => $temp, 'D' => 'CU');
						else
							$out_sheets[$client2_id][$job_id][] = array('V' => '-', 'D' => 'TR');
						$totals['V_LAST_AMT']['V'] += $temp;
						break;
					case 'V_NEXT_DT':
						if ($init_headings)
							$headings[] = 'Next Payment Date (**Overdue)';
						if ($init_totals)
							$totals['V_NEXT_DT'] = array('V' => 0, 'D' => 'X');

						$next_ep = intval($in_sheets[$client2_id][$job_id]['V_NEXT_DT']);
						if (0 < $next_ep)
						{
							$next_dt = date_from_epoch(false, $next_ep, false, false, true);
							$date = date_for_sql($next_dt, true, false);
							if ($next_ep < time())
								$date = "**{$date}";
						}
						else
							$date = '-';

						$out_sheets[$client2_id][$job_id][] = array('V' => $date, 'D' => 'TR');
						$totals['V_NEXT_DT']['V']++;
						break;
					case 'V_L_NEXT':
						if ($init_headings)
							$headings[] = 'Next Letter';
						if ($init_totals)
							$totals['V_L_NEXT'] = array('V' => 0, 'D' => 'X');
						$out_sheets[$client2_id][$job_id][] = array('V' => $job_info['V_L_NEXT'], 'D' => 'T');
						$totals['V_L_NEXT']['V']++;
						break;
					case 'V_DIARY':
						if ($init_headings)
							$headings[] = 'Diary Date';
						if ($init_totals)
							$totals['V_DIARY'] = array('V' => 0, 'D' => 'X');
						$date = ($job_info['V_DIARY'] ? date_for_sql($job_info['V_DIARY'], true, false) : '-');
						$out_sheets[$client2_id][$job_id][] = array('V' => $date, 'D' => 'TR');
						$totals['V_DIARY']['V']++;
						break;
					case 'V_EMAIL':
						if ($init_headings)
							$headings[] = 'Email Address';
						if ($init_totals)
							$totals['V_EMAIL'] = array('V' => 0, 'D' => 'X');
						$out_sheets[$client2_id][$job_id][] = array('V' => $job_info['V_EMAIL'], 'D' => 'T');
						$totals['V_EMAIL']['V']++;
						break;
					case 'V_NEW_1':
						if ($init_headings)
							$headings[] = 'New Address [1]';
						if ($init_totals)
							$totals['V_NEW_1'] = array('V' => 0, 'D' => 'X');
						$out_sheets[$client2_id][$job_id][] = array('V' => $job_info['V_NEW_1'], 'D' => 'T');
						$totals['V_NEW_1']['V']++;
						break;
					case 'V_NEW_2':
						if ($init_headings)
							$headings[] = 'New Address [2]';
						if ($init_totals)
							$totals['V_NEW_2'] = array('V' => 0, 'D' => 'X');
						$out_sheets[$client2_id][$job_id][] = array('V' => $job_info['V_NEW_2'], 'D' => 'T');
						$totals['V_NEW_2']['V']++;
						break;
					case 'V_NEW_3':
						if ($init_headings)
							$headings[] = 'New Address [3]';
						if ($init_totals)
							$totals['V_NEW_3'] = array('V' => 0, 'D' => 'X');
						$out_sheets[$client2_id][$job_id][] = array('V' => $job_info['V_NEW_3'], 'D' => 'T');
						$totals['V_NEW_3']['V']++;
						break;
					case 'V_NEW_4':
						if ($init_headings)
							$headings[] = 'New Address [4]';
						if ($init_totals)
							$totals['V_NEW_4'] = array('V' => 0, 'D' => 'X');
						$out_sheets[$client2_id][$job_id][] = array('V' => $job_info['V_NEW_4'], 'D' => 'T');
						$totals['V_NEW_4']['V']++;
						break;
					case 'V_NEW_5':
						if ($init_headings)
							$headings[] = 'New Address [5]';
						if ($init_totals)
							$totals['V_NEW_5'] = array('V' => 0, 'D' => 'X');
						$out_sheets[$client2_id][$job_id][] = array('V' => $job_info['V_NEW_5'], 'D' => 'T');
						$totals['V_NEW_5']['V']++;
						break;
					case 'V_LS_T':
						if ($init_headings)
							$headings[] = 'Trace Letter Sent?';
						if ($init_totals)
							$totals['V_LS_T'] = array('V' => 0, 'D' => 'X');
						$sent = ((0 < $job_info['V_LS_T']) ? 'Yes' : 'No');
						$out_sheets[$client2_id][$job_id][] = array('V' => $sent, 'D' => 'T');
						$totals['V_LS_T']['V']++;
						break;
					case 'V_LD_T':
						if ($init_headings)
							$headings[] = 'Trace Letter Date';
						if ($init_totals)
							$totals['V_LD_T'] = array('V' => 0, 'D' => 'X');
						$date = ($job_info['V_LD_T'] ? date_for_sql($job_info['V_LD_T'], true, false) : '-');
						$out_sheets[$client2_id][$job_id][] = array('V' => $date, 'D' => 'TR');
						$totals['V_LD_T']['V']++;
						break;
					case 'V_LS_M':
						if ($init_headings)
							$headings[] = 'Means Letter Sent?';
						if ($init_totals)
							$totals['V_LS_M'] = array('V' => 0, 'D' => 'X');
						$sent = ((0 < $job_info['V_LS_M']) ? 'Yes' : 'No');
						$out_sheets[$client2_id][$job_id][] = array('V' => $sent, 'D' => 'T');
						$totals['V_LS_M']['V']++;
						break;
					case 'V_LD_M':
						if ($init_headings)
							$headings[] = 'Means Letter Date';
						if ($init_totals)
							$totals['V_LD_M'] = array('V' => 0, 'D' => 'X');
						$date = ($job_info['V_LD_M'] ? date_for_sql($job_info['V_LD_M'], true, false) : '-');
						$out_sheets[$client2_id][$job_id][] = array('V' => $date, 'D' => 'TR');
						$totals['V_LD_M']['V']++;
						break;
					case 'V_LS_RP':
						if ($init_headings)
							$headings[] = 'Repossession Letter Sent?';
						if ($init_totals)
							$totals['V_LS_RP'] = array('V' => 0, 'D' => 'X');
						$sent = ((0 < $job_info['V_LS_RP']) ? 'Yes' : 'No');
						$out_sheets[$client2_id][$job_id][] = array('V' => $sent, 'D' => 'T');
						$totals['V_LS_RP']['V']++;
						break;
					case 'V_LD_RP':
						if ($init_headings)
							$headings[] = 'Repossession Letter Date';
						if ($init_totals)
							$totals['V_LD_RP'] = array('V' => 0, 'D' => 'X');
						$date = ($job_info['V_LD_RP'] ? date_for_sql($job_info['V_LD_RP'], true, false) : '-');
						$out_sheets[$client2_id][$job_id][] = array('V' => $date, 'D' => 'TR');
						$totals['V_LD_RP']['V']++;
						break;
					case 'V_LS_OT':
						if ($init_headings)
							$headings[] = 'Other Letter Sent?';
						if ($init_totals)
							$totals['V_LS_OT'] = array('V' => 0, 'D' => 'X');
						$sent = ((0 < $job_info['V_LS_OT']) ? 'Yes' : 'No');
						$out_sheets[$client2_id][$job_id][] = array('V' => $sent, 'D' => 'T');
						$totals['V_LS_OT']['V']++;
						break;
					case 'V_LD_OT':
						if ($init_headings)
							$headings[] = 'Other Letter Date';
						if ($init_totals)
							$totals['V_LD_OT'] = array('V' => 0, 'D' => 'X');
						$date = ($job_info['V_LD_OT'] ? date_for_sql($job_info['V_LD_OT'], true, false) : '-');
						$out_sheets[$client2_id][$job_id][] = array('V' => $date, 'D' => 'TR');
						$totals['V_LD_OT']['V']++;
						break;
					case 'V_LS_SV':
						if ($init_headings)
							$headings[] = 'Service Letter Sent?';
						if ($init_totals)
							$totals['V_LS_SV'] = array('V' => 0, 'D' => 'X');
						$sent = ((0 < $job_info['V_LS_SV']) ? 'Yes' : 'No');
						$out_sheets[$client2_id][$job_id][] = array('V' => $sent, 'D' => 'T');
						$totals['V_LS_SV']['V']++;
						break;
					case 'V_LD_SV':
						if ($init_headings)
							$headings[] = 'Service Letter Date';
						if ($init_totals)
							$totals['V_LD_SV'] = array('V' => 0, 'D' => 'X');
						$date = ($job_info['V_LD_SV'] ? date_for_sql($job_info['V_LD_SV'], true, false) : '-');
						$out_sheets[$client2_id][$job_id][] = array('V' => $date, 'D' => 'TR');
						$totals['V_LD_SV']['V']++;
						break;
					case 'V_LS_R1':
						if ($init_headings)
							$headings[] = 'Retrace (1) Letter Sent?';
						if ($init_totals)
							$totals['V_LS_R1'] = array('V' => 0, 'D' => 'X');
						$sent = ((0 < $job_info['V_LS_R1']) ? 'Yes' : 'No');
						$out_sheets[$client2_id][$job_id][] = array('V' => $sent, 'D' => 'T');
						$totals['V_LS_R1']['V']++;
						break;
					case 'V_LD_R1':
						if ($init_headings)
							$headings[] = 'Retrace (1) Letter Date';
						if ($init_totals)
							$totals['V_LD_R1'] = array('V' => 0, 'D' => 'X');
						$date = ($job_info['V_LD_R1'] ? date_for_sql($job_info['V_LD_R1'], true, false) : '-');
						$out_sheets[$client2_id][$job_id][] = array('V' => $date, 'D' => 'TR');
						$totals['V_LD_R1']['V']++;
						break;
					case 'V_LS_R2':
						if ($init_headings)
							$headings[] = 'Retrace (2) Letter Sent?';
						if ($init_totals)
							$totals['V_LS_R2'] = array('V' => 0, 'D' => 'X');
						$sent = ((0 < $job_info['V_LS_R2']) ? 'Yes' : 'No');
						$out_sheets[$client2_id][$job_id][] = array('V' => $sent, 'D' => 'T');
						$totals['V_LS_R2']['V']++;
						break;
					case 'V_LD_R2':
						if ($init_headings)
							$headings[] = 'Retrace (2) Letter Date';
						if ($init_totals)
							$totals['V_LD_R2'] = array('V' => 0, 'D' => 'X');
						$date = ($job_info['V_LD_R2'] ? date_for_sql($job_info['V_LD_R2'], true, false) : '-');
						$out_sheets[$client2_id][$job_id][] = array('V' => $date, 'D' => 'TR');
						$totals['V_LD_R2']['V']++;
						break;
					case 'V_LS_R3':
						if ($init_headings)
							$headings[] = 'Retrace (3) Letter Sent?';
						if ($init_totals)
							$totals['V_LS_R3'] = array('V' => 0, 'D' => 'X');
						$sent = ((0 < $job_info['V_LS_R3']) ? 'Yes' : 'No');
						$out_sheets[$client2_id][$job_id][] = array('V' => $sent, 'D' => 'T');
						$totals['V_LS_R3']['V']++;
						break;
					case 'V_LD_R3':
						if ($init_headings)
							$headings[] = 'Retrace (3) Letter Date';
						if ($init_totals)
							$totals['V_LD_R3'] = array('V' => 0, 'D' => 'X');
						$date = ($job_info['V_LD_R3'] ? date_for_sql($job_info['V_LD_R3'], true, false) : '-');
						$out_sheets[$client2_id][$job_id][] = array('V' => $date, 'D' => 'TR');
						$totals['V_LD_R3']['V']++;
						break;
					case 'V_LS_TC':
						if ($init_headings)
							$headings[] = 'T/C Letter Sent?';
						if ($init_totals)
							$totals['V_LS_TC'] = array('V' => 0, 'D' => 'X');
						$sent = ((0 < $job_info['V_LS_TC']) ? 'Yes' : 'No');
						$out_sheets[$client2_id][$job_id][] = array('V' => $sent, 'D' => 'T');
						$totals['V_LS_TC']['V']++;
						break;
					case 'V_LD_TC':
						if ($init_headings)
							$headings[] = 'T/C Letter Date';
						if ($init_totals)
							$totals['V_LD_TC'] = array('V' => 0, 'D' => 'X');
						$date = ($job_info['V_LD_TC'] ? date_for_sql($job_info['V_LD_TC'], true, false) : '-');
						$out_sheets[$client2_id][$job_id][] = array('V' => $date, 'D' => 'TR');
						$totals['V_LD_TC']['V']++;
						break;
					case 'V_LS_TM':
						if ($init_headings)
							$headings[] = 'T/M Letter Sent?';
						if ($init_totals)
							$totals['V_LS_TM'] = array('V' => 0, 'D' => 'X');
						$sent = ((0 < $job_info['V_LS_TM']) ? 'Yes' : 'No');
						$out_sheets[$client2_id][$job_id][] = array('V' => $sent, 'D' => 'T');
						$totals['V_LS_TM']['V']++;
						break;
					case 'V_LD_TM':
						if ($init_headings)
							$headings[] = 'T/M Letter Date';
						if ($init_totals)
							$totals['V_LD_TM'] = array('V' => 0, 'D' => 'X');
						$date = ($job_info['V_LD_TM'] ? date_for_sql($job_info['V_LD_TM'], true, false) : '-');
						$out_sheets[$client2_id][$job_id][] = array('V' => $date, 'D' => 'TR');
						$totals['V_LD_TM']['V']++;
						break;
					case 'V_LS_AT':
						if ($init_headings)
							$headings[] = 'Attendance Letter Sent?';
						if ($init_totals)
							$totals['V_LS_AT'] = array('V' => 0, 'D' => 'X');
						$sent = ((0 < $job_info['V_LS_AT']) ? 'Yes' : 'No');
						$out_sheets[$client2_id][$job_id][] = array('V' => $sent, 'D' => 'T');
						$totals['V_LS_AT']['V']++;
						break;
					case 'V_LD_AT':
						if ($init_headings)
							$headings[] = 'Attendance Letter Date';
						if ($init_totals)
							$totals['V_LD_AT'] = array('V' => 0, 'D' => 'X');
						$date = ($job_info['V_LD_AT'] ? date_for_sql($job_info['V_LD_AT'], true, false) : '-');
						$out_sheets[$client2_id][$job_id][] = array('V' => $date, 'D' => 'TR');
						$totals['V_LD_AT']['V']++;
						break;
					case 'V_CLI_ID':
						if ($init_headings)
							$headings[] = 'Vilcol Client ID';
						if ($init_totals)
							$totals['V_CLI_ID'] = array('V' => 0, 'D' => 'X');
						$out_sheets[$client2_id][$job_id][] = array('V' => $client_names[$client2_id]['C_CODE'], 'D' => 'T');
						$totals['V_CLI_ID']['V']++;
						break;
					case 'V_SEQ':
						if ($init_headings)
							$headings[] = 'Vilcol Sequence No';
						if ($init_totals)
							$totals['V_SEQ'] = array('V' => 0, 'D' => 'X');
						$out_sheets[$client2_id][$job_id][] = array('V' => $job_info['V_SEQ'], 'D' => 'TR');
						$totals['V_SEQ']['V']++;
						break;
					case 'V_USER':
						if ($init_headings)
							$headings[] = 'Vilcol User ID';
						if ($init_totals)
							$totals['V_USER'] = array('V' => 0, 'D' => 'X');
						$out_sheets[$client2_id][$job_id][] = array('V' => $job_info['V_USER'], 'D' => 'T');
						$totals['V_USER']['V']++;
						break;
					case 'V_DT_START':
						if ($init_headings)
							$headings[] = 'Date Job Started';
						if ($init_totals)
							$totals['V_DT_START'] = array('V' => 0, 'D' => 'X');
						$date = ($job_info['V_DT_START'] ? date_for_sql($job_info['V_DT_START'], true, false) : '-');
						$out_sheets[$client2_id][$job_id][] = array('V' => $date, 'D' => 'TR');
						$totals['V_DT_START']['V']++;
						break;
					case 'V_DT_END':
						if ($init_headings)
							$headings[] = 'Date Job Completed';
						if ($init_totals)
							$totals['V_DT_END'] = array('V' => 0, 'D' => 'X');
						$date = ($job_info['V_DT_END'] ? date_for_sql($job_info['V_DT_END'], true, false) : '-');
						$out_sheets[$client2_id][$job_id][] = array('V' => $date, 'D' => 'TR');
						$totals['V_DT_END']['V']++;
						break;
					case 'V_AP_N1':
						if ($init_headings)
							$headings[] = 'Additional Phone No. (1)';
						if ($init_totals)
							$totals['V_AP_N1'] = array('V' => 0, 'D' => 'X');
						$out_sheets[$client2_id][$job_id][] = array('V' => $job_info['V_AP_N1'], 'D' => 'T');
						$totals['V_AP_N1']['V']++;
						break;
					case 'V_AP_D1':
						if ($init_headings)
							$headings[] = 'Additional Phone Desc. (1)';
						if ($init_totals)
							$totals['V_AP_D1'] = array('V' => 0, 'D' => 'X');
						$out_sheets[$client2_id][$job_id][] = array('V' => $job_info['V_AP_D1'], 'D' => 'T');
						$totals['V_AP_D1']['V']++;
						break;
					case 'V_AP_N2':
						if ($init_headings)
							$headings[] = 'Additional Phone No. (2)';
						if ($init_totals)
							$totals['V_AP_N2'] = array('V' => 0, 'D' => 'X');
						$out_sheets[$client2_id][$job_id][] = array('V' => $job_info['V_AP_N2'], 'D' => 'T');
						$totals['V_AP_N2']['V']++;
						break;
					case 'V_AP_D2':
						if ($init_headings)
							$headings[] = 'Additional Phone Desc. (2)';
						if ($init_totals)
							$totals['V_AP_D2'] = array('V' => 0, 'D' => 'X');
						$out_sheets[$client2_id][$job_id][] = array('V' => $job_info['V_AP_D2'], 'D' => 'T');
						$totals['V_AP_D2']['V']++;
						break;
					case 'V_AP_N3':
						if ($init_headings)
							$headings[] = 'Additional Phone No. (3)';
						if ($init_totals)
							$totals['V_AP_N3'] = array('V' => 0, 'D' => 'X');
						$out_sheets[$client2_id][$job_id][] = array('V' => $job_info['V_AP_N3'], 'D' => 'T');
						$totals['V_AP_N3']['V']++;
						break;
					case 'V_AP_D3':
						if ($init_headings)
							$headings[] = 'Additional Phone Desc. (3)';
						if ($init_totals)
							$totals['V_AP_D3'] = array('V' => 0, 'D' => 'X');
						$out_sheets[$client2_id][$job_id][] = array('V' => $job_info['V_AP_D3'], 'D' => 'T');
						$totals['V_AP_D3']['V']++;
						break;
					case 'V_AP_N4':
						if ($init_headings)
							$headings[] = 'Additional Phone No. (4)';
						if ($init_totals)
							$totals['V_AP_N4'] = array('V' => 0, 'D' => 'X');
						$out_sheets[$client2_id][$job_id][] = array('V' => $job_info['V_AP_N4'], 'D' => 'T');
						$totals['V_AP_N4']['V']++;
						break;
					case 'V_AP_D4':
						if ($init_headings)
							$headings[] = 'Additional Phone Desc. (4)';
						if ($init_totals)
							$totals['V_AP_D4'] = array('V' => 0, 'D' => 'X');
						$out_sheets[$client2_id][$job_id][] = array('V' => $job_info['V_AP_D4'], 'D' => 'T');
						$totals['V_AP_D4']['V']++;
						break;
					case 'V_AP_N5':
						if ($init_headings)
							$headings[] = 'Additional Phone No. (5)';
						if ($init_totals)
							$totals['V_AP_N5'] = array('V' => 0, 'D' => 'X');
						$out_sheets[$client2_id][$job_id][] = array('V' => $job_info['V_AP_N5'], 'D' => 'T');
						$totals['V_AP_N5']['V']++;
						break;
					case 'V_AP_D5':
						if ($init_headings)
							$headings[] = 'Additional Phone Desc. (5)';
						if ($init_totals)
							$totals['V_AP_D5'] = array('V' => 0, 'D' => 'X');
						$out_sheets[$client2_id][$job_id][] = array('V' => $job_info['V_AP_D5'], 'D' => 'T');
						$totals['V_AP_D5']['V']++;
						break;
					case 'V_TDX_ID':
						if ($init_headings)
							$headings[] = 'TDX Account ID';
						if ($init_totals)
							$totals['V_TDX_ID'] = array('V' => 0, 'D' => 'X');
						$out_sheets[$client2_id][$job_id][] = array('V' => $job_info['V_TDX_ID'], 'D' => 'T');
						$totals['V_TDX_ID']['V']++;
						break;
					case 'V_TDX_AS':
						if ($init_headings)
							$headings[] = 'TDX Assignment ID';
						if ($init_totals)
							$totals['V_TDX_AS'] = array('V' => 0, 'D' => 'X');
						$out_sheets[$client2_id][$job_id][] = array('V' => $job_info['V_TDX_AS'], 'D' => 'T');
						$totals['V_TDX_AS']['V']++;
						break;
					case 'V_TDX_PP':
						if ($init_headings)
							$headings[] = 'TDX Primary Person ID';
						if ($init_totals)
							$totals['V_TDX_PP'] = array('V' => 0, 'D' => 'X');
						$out_sheets[$client2_id][$job_id][] = array('V' => $job_info['V_TDX_PP'], 'D' => 'T');
						$totals['V_TDX_PP']['V']++;
						break;
					case 'V_TDX_CLIENT':
						if ($init_headings)
							$headings[] = 'TDX Client Name';
						if ($init_totals)
							$totals['V_TDX_CLIENT'] = array('V' => 0, 'D' => 'X');
						$out_sheets[$client2_id][$job_id][] = array('V' => $client_names[$client2_id]['C_CO_NAME'], 'D' => 'T');
						$totals['V_TDX_CLIENT']['V']++;
						break;
					case 'REASON_DEBT':
						if ($init_headings)
							$headings[] = 'Reason for Debt';
						if ($init_totals)
							$totals['REASON_DEBT'] = array('V' => 0, 'D' => 'X');
						$out_sheets[$client2_id][$job_id][] = array('V' => $job_info['REASON_DEBT'], 'D' => 'T');
						$totals['REASON_DEBT']['V']++;
						break;
					default:
						break;
				}
			} # foreach $fields
			$init_headings = false;
			$init_totals = false;

		} # foreach ($jobs)

		# -----------------------------------------------------------------------------------------------

		$out_sheets[$client2_id]['TOTALS_1'] = array(array('V' => 'TOTALS:', 'D' => 'T'));
		if (count($totals) == 0)
			$totals[] = array('V' => 0, 'D' => 'X');
		$out_sheets[$client2_id]['TOTALS_2'] = $totals;

		if (!$multi_sheet)
		{
			# Copy all lines onto the 'SINGLE' sheet
			foreach ($out_sheets[$client2_id] as $unused => $job_info) # for each line on the sheet
			{
				$line = array(	array('V' => $client_names[$client2_id]['C_CODE'], 'D' => 'TR'),
								array('V' => $client_names[$client2_id]['C_CO_NAME'], 'D' => 'T')
							 );
				foreach ($job_info as $fld)
					$line[] = $fld;
				#dprint("job_info=\"$job_info\"=" . print_r($job_info,1) . "<br>line=\"$line\"=" . print_r($line,1));#
				$out_sheets['SINGLE'][] = $line;
				$unused=$unused; # keep code-checker quiet
			}
			$out_sheets['SINGLE'][] = array(array('V' => '', 'D' => 'X')); # blank line
		}

	} # for each client ($in_sheets)
	if ($debug) dlog("...done in_sheets");

	#dprint("Outsheets=" . print_r($out_sheets,1));#
	#dprint("Outsheets/SINGLE=" . print_r($out_sheets['SINGLE'],1));#

	# ---------------------------------------------------------------------------------------------

	# ---- Output the data to the screen and/or Excel ---------------------------------------------

	#$dline_max = 100;#
	$dline_max = 0;

	if (($dest == 's') || ($dest == 'x'))
	{

		$screen = "
		<p style=\"font-size:18px; font-weight:bold;\">
			Report {$report['REP_NAME']}
			&nbsp;&nbsp;&nbsp;&nbsp;
			" . input_button('Export to Excel', "export_xl()", '', 'but_export_xl') . "
		</p>
		";
		if (0 < $dline_max)
			$screen .= "<p style=\"color:red;\">*** Screen display is limited to $dline_max lines ***</p>";

		if ($debug) dlog("Working on out_sheets...");
		foreach ($out_sheets as $client2_id => $datalines)
		{
			if ($multi_sheet)
			{
				if ($client2_id == 'SINGLE')
					continue; # this sheet is the single-sheet which we don't want to output
			}
			else
			{
				if ($client2_id != 'SINGLE')
					continue; # this sheet is one of the multi-sheets which we don't want to output
			}

			if ($multi_sheet)
			{
				$screen .= "
				<p style=\"font-size:15px; font-weight:bold;\">
					Client: {$client_names[$client2_id]['C_CODE']}, {$client_names[$client2_id]['C_CO_NAME']}
				</p>
				";
			}
			$screen .= "
			<table class=\"spaced_table\">
			<tr>
				";
				foreach ($headings as $head)
					$screen .= "<th>$head</th>";
				$screen .= "
			</tr>
			";
			$dline_ix = 0;
			foreach ($datalines as $dline)
			{
				if ($dline_max && ($dline_max < $dline_ix))
					break;

				$screen .= "
				<tr>
				";
				foreach ($dline as $fld)
				{
					$align = '';
					#dprint("fld=\"$fld\"=" . print_r($fld,1));#
					if (array_key_exists('D', $fld))
					{
						if ($fld['D'] == 'T')
							$fld = $fld['V'];
						elseif ($fld['D'] == 'TR')
						{
							$align = $ar;
							$fld = $fld['V'];
						}
						elseif ($fld['D'] == 'I')
						{
							$align = $ar;
							$fld = number_with_commas($fld['V'], true, false);
						}
						elseif ($fld['D'] == 'F')
						{
							$align = $ar;
							$fld = number_with_commas($fld['V'], true, true);
						}
						elseif ($fld['D'] == 'CU')
						{
							$align = $ar;
							$fld = money_format_kdb($fld['V'], true, true, true);
						}
						elseif ($fld['D'] == 'X')
						{
							$align = $ac;
							$fld = '-';
						}
						else
							dprint("***ERROR*** fld['D'] is not one of T/TR/I/F/CU: \"{$fld['D']}\"");
					}
					else
						dprint("***ERROR*** fld has no 'D' element: \"$fld\" or " . print_r($fld,1));
					$screen .= "
					<td $align>$fld</td>
					";
				}
				$screen .= "
				</tr>
				";
				$dline_ix++;
			}
			$screen .= "
			</table>
			";
		} # foreach ($out_sheets)
		if ($debug) dlog("...done out_sheets");

		print $screen;

		if ($dest == 'x')
		{
			if ($debug) dlog("Exporting " . count($out_sheets) . " out_sheets (multi_sheet=$multi_sheet)...");
			$xl_sheets = array();
			foreach ($out_sheets as $client2_id => $datalines_vd)
			{
				if ($debug) dlog("... out_sheet[" . count($xl_sheets) . "], count(datalines_vd)=" . count($datalines_vd));
				if ($multi_sheet)
				{
					if ($client2_id == 'SINGLE')
					{
						if ($debug) dlog("...multi/single - continuing round");
						continue; # this sheet is the single-sheet which we don't want to output
					}
					$title_short = $client_names[$client2_id]['C_CO_NAME'];
					$title_long = "{$report['REP_NAME']} for client: " .
							"{$client_names[$client2_id]['C_CODE']}, {$client_names[$client2_id]['C_CO_NAME']}";
				}
				else
				{
					if ($client2_id != 'SINGLE')
					{
						if ($debug) dlog("...!multi/!single - continuing round");
						continue; # this sheet is one of the multi-sheets which we don't want to output
					}
					$title_short = "All Clients";
					$title_long = $report['REP_NAME'];
				}

				$top_lines = array_merge(array(array($title_long)), array(array()));
				$formats = array();

				$datalines = array();
				foreach ($datalines_vd as $old_line)
				{
					$new_line = array();
					foreach ($old_line as $fld)
						$new_line[] = (($fld['D'] == 'X') ? '' : $fld['V']);
					$datalines[] = $new_line;
				}

				$xl_sheets[] = array($title_short, $top_lines, $headings, $datalines, $formats, '');
			}
			if ($debug) dlog("...count(xl_sheets)= " . count($xl_sheets) . ", reps_subdir=\"$reps_subdir\", xfile=\"$xfile\"");

			phpExcel_output($reps_subdir, $xfile, $xl_sheets); # library.php

			# Auto-download file to PC
			print "
			<script type=\"text/javascript\">
				document.form_csv_download.short_fname.value = '$xfile.$phpExcel_ext';
				document.form_csv_download.full_fname.value = '$csv_path' + '{$reps_subdir}/$xfile.$phpExcel_ext';
				document.form_csv_download.submit();
			</script>
			";
			if ($debug) dlog("...done Export");
		}
	} # $dest 's' or 'x'
	else
		dlog("$this_function: *=* bad dest \"$dest\"");

	if ($debug) dlog("Finished run_jobdet_gen()");
	# ---------------------------------------------------------------------------------------------
} # run_jobdet_gen()

?>
