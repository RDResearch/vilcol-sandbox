<?php

include_once("settings.php");
include_once("library.php");
global $navi_1_reports;
global $role_man;
global $screen_delay; # settings.php - see also $screen_html
global $screen_html; # settings.php - see also $screen_delay
global $sqlFalse;
global $USER; # set by admin_verify()

$sigma_code_trans = 1; # code for Sigma transaction report
$sigma_code_dial = 2; # code for Sigma non-diallers event report
$sigma_code_arr = 3; # code for Sigma arrangements report
$sigma_code_close = 4; # code for Sigma closures report

log_open("vilcol.log");

sql_connect();

admin_verify(); # writes to $USER

if ($USER['IS_ENABLED'])
{
	if (role_check('*', $role_man))
	{
		$navi_1_reports = true; # settings.php; used by navi_1_heading()
		$onload = "onload=\"set_scroll();\"";
		u_rep_custom_set($sqlFalse); # set DB flag to "not on custom reports page"
		$page_title_2 = 'Reports - Vilcol';

		$screen_delay = true; # This means that we mustn't print anything to the screen in this script!!
		$screen_html = '';
		screen_layout();
		print $screen_html;
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
	global $access_collect;
	global $access_trace;
	global $collect_fixed;
	global $collect_statistics;
	global $page_title_2;
	global $role_man;
	#global $manager_a;
	#global $manager_c;
	#global $manager_t;
	global $sc_agent;
	global $sc_client;
	global $sc_date_fr;
	global $sc_date_to;
	global $sc_dtcol_fr;
	global $sc_dtcol_to;
	global $sc_group;
	global $sc_job_type;
	global $sc_sales;
	global $sc_rt_di;
	global $sc_rt_fw;
	global $sc_rt_tv;
	#global $trace_fixed;
	global $trace_statistics;
	global $USER;

	dprint(post_values());

	$manager_t = role_check('t', $role_man);
	$manager_c = role_check('c', $role_man);
	$manager_a = role_check('a', $role_man);
	$access_collect = ($manager_c || $manager_a);
	$access_trace = ($manager_t || $manager_a);
	if ( ! ($access_collect || $access_trace) )
	{
		printD("<p>Sorry you do not have access to reports</p>");
		return;
	}

	if (count($_POST) == 0)
	{
		$sc_date_fr = date_now(true, date_last_month(1), false); # last month plus one day
		$sc_date_to = date_now(true, '', false);
		#$sc_date_fr = '01/01/2015';#
		#$sc_date_to = '31/03/2015';#
		$sc_rt_tv = 1;
		$sc_rt_fw = 1;
		$sc_rt_di = 1;
	}
	else
	{
		$sc_date_fr = post_val('sc_date_fr', false, true, false, 1);
		$sc_date_to = post_val('sc_date_to', false, true, false, 1);
		$sc_dtcol_fr = post_val('sc_dtcol_fr', false, true, false, 1);
		$sc_dtcol_to = post_val('sc_dtcol_to', false, true, false, 1);
		$sc_rt_tv = post_val('sc_rt_tv', true);
		$sc_rt_fw = post_val('sc_rt_fw', true);
		$sc_rt_di = post_val('sc_rt_di', true);
	}
	$sc_client = post_val('sc_client'); # might be more than one client code
	$sc_group = post_val('sc_group', true);
	$sc_sales = post_val('sc_sales', true);
	$sc_job_type = post_val('sc_job_type', true);
	$task = post_val('task');
	$collect_statistics = post_val('collect_statistics');
	$collect_fixed = post_val('collect_fixed');
	$trace_statistics = post_val('trace_statistics');
	#$trace_fixed = post_val('trace_fixed');

	if (($collect_statistics == '') && ($collect_fixed == '') && ($trace_statistics == '')) # && ($trace_fixed == ''))
	{
		if ($access_collect)
			$sc_agent = post_val('sc_agent_c', true);
		else
			$sc_agent = post_val('sc_agent_t', true);
	}
	else
	{
		if ($collect_statistics || $collect_fixed)
			$sc_agent = post_val('sc_agent_c', true);
		else
			$sc_agent = post_val('sc_agent_t', true);
	}

	#$time_limit = 5 * 60; # 5 minutes

	printD("
	<div id=\"div_wait\">
		<p style=\"color:blue\">Please wait...</p>
	</div><!--div_wait-->
	<div id=\"div_form\" style=\"display:none;\">
	");

	print_form();

	javascript();

	if ($collect_statistics)
	{
		if ($collect_statistics == 'cs_col_rate_client')
		{
			#set_time_limit($time_limit);
			rep_cs_col_rate_client('s', $sc_date_fr, $sc_date_to, $sc_client, $sc_group);
			if ($task == 'x') # Export to Excel
			{
				$xfile = "cs_col_rate_client_{$USER['USER_ID']}";
				rep_cs_col_rate_client($task, $sc_date_fr, $sc_date_to, $sc_client, $sc_group, $xfile);
			}
		}
		elseif ($collect_statistics == 'cs_jobs_client')
		{
			#set_time_limit($time_limit);
			rep_cs_jobs_client('s', $sc_date_fr, $sc_date_to, $sc_sales, $sc_client, $sc_group);
			if ($task == 'x') # Export to Excel
			{
				$xfile = "cs_jobs_client_{$USER['USER_ID']}";
				rep_cs_jobs_client($task, $sc_date_fr, $sc_date_to, $sc_sales, $sc_client, $sc_group, $xfile);
			}
		}
		elseif ($collect_statistics == 'cs_col_rate_agent')
		{
			#set_time_limit($time_limit);
			rep_cs_col_rate_agent('s', $sc_date_fr, $sc_date_to);#, $sc_client, $sc_group);
			if ($task == 'x') # Export to Excel
			{
				$xfile = "cs_col_rate_agent_{$USER['USER_ID']}";
				rep_cs_col_rate_agent($task, $sc_date_fr, $sc_date_to, $xfile); #$sc_client, $sc_group, $xfile);
			}
		}
		elseif ($collect_statistics == 'cs_col_sing_agent')
		{
			#set_time_limit($time_limit);
			rep_cs_col_sing_agent('s', $sc_date_fr, $sc_date_to, $sc_agent, $sc_client, $sc_group);
			if ($task == 'x') # Export to Excel
			{
				$xfile = "cs_col_sing_agent_{$USER['USER_ID']}";
				rep_cs_col_sing_agent($task, $sc_date_fr, $sc_date_to, $sc_agent, $sc_client, $sc_group, $xfile);
			}
		}
		elseif ($collect_statistics == 'cs_stat_client')
		{
			#set_time_limit($time_limit);
			rep_cs_stat_client('s', $sc_date_fr, $sc_date_to, $sc_client, $sc_group);
			if ($task == 'x') # Export to Excel
			{
				$xfile = "cs_stat_client_{$USER['USER_ID']}";
				rep_cs_stat_client($task, $sc_date_fr, $sc_date_to, $sc_client, $sc_group, $xfile);
			}
		}
		elseif ($collect_statistics == 'cs_vs_col_summ')
		{
			printD("
				<div style=\"color:blue;\">
				<h3>View/Search collection summaries</h3>
				<p>Please use the \"Jobs\" screen for this.</p>
				</div>
				");
		}
		elseif ($collect_statistics == 'cs_stmt_inv')
		{
			#set_time_limit($time_limit);
			rep_cs_stmt_inv('s', $sc_date_fr);
			if ($task == 'x') # Export to Excel
			{
				$xfile = "cs_stmt_inv_{$USER['USER_ID']}";
				rep_cs_stmt_inv('x', $sc_date_fr, $xfile);
			}
		}
		else
			dprint("Report \"$collect_statistics\" not recognised", true);
	}
	elseif ($collect_fixed)
	{
		if ($collect_fixed == 'cf_single_client')
		{
			#set_time_limit($time_limit);
			rep_cf_single_client('s', $sc_date_fr, $sc_date_to, $sc_dtcol_fr, $sc_dtcol_to, $sc_client, $sc_group, $sc_rt_tv, $sc_rt_fw, $sc_rt_di);
			if ($task == 'x') # Export to Excel
			{
				$xfile = "cf_single_client_{$USER['USER_ID']}";
				rep_cf_single_client($task, $sc_date_fr, $sc_date_to, $sc_dtcol_fr, $sc_dtcol_to, $sc_client, $sc_group, $sc_rt_tv, $sc_rt_fw, $sc_rt_di, $xfile);
			}
		}
		elseif ($collect_fixed == 'cf_stair_step')
		{
			#set_time_limit($time_limit);
			rep_cf_stair_step('s', $sc_date_fr, $sc_date_to, $sc_dtcol_fr, $sc_dtcol_to, $sc_client, $sc_group, $sc_rt_tv, $sc_rt_fw, $sc_rt_di);
			if ($task == 'x') # Export to Excel
			{
				$xfile = "cf_stair_step_{$USER['USER_ID']}";
				rep_cf_stair_step($task, $sc_date_fr, $sc_date_to, $sc_dtcol_fr, $sc_dtcol_to, $sc_client, $sc_group, $sc_rt_tv, $sc_rt_fw, $sc_rt_di, $xfile);
			}
		}
		elseif ($collect_fixed == 'cf_tdx_trans')
		{
			#set_time_limit($time_limit);
			rep_cf_tdx_trans('s', $sc_date_fr, $sc_date_to, $sc_client, $sc_group);
			if ($task == 'x') # Export to Excel
			{
				$xfile = "cf_tdx_trans_{$USER['USER_ID']}";
				rep_cf_tdx_trans($task, $sc_date_fr, $sc_date_to, $sc_client, $sc_group, $xfile);
			}
		}
		elseif ($collect_fixed == 'cf_tdx_close')
		{
			#set_time_limit($time_limit);
			rep_cf_tdx_close('s', $sc_date_fr, $sc_date_to, $sc_client, $sc_group);
			if ($task == 'x') # Export to Excel
			{
				$xfile = "cf_tdx_close_{$USER['USER_ID']}";
				rep_cf_tdx_close($task, $sc_date_fr, $sc_date_to, $sc_client, $sc_group, $xfile);
			}
		}
		elseif ($collect_fixed == 'cf_tdx_recon')
		{
			#set_time_limit($time_limit);
			rep_cf_tdx_recon('s', $sc_date_fr, $sc_date_to, $sc_client, $sc_group);
			if ($task == 'x') # Export to Excel
			{
				$xfile = "cf_tdx_recon_{$USER['USER_ID']}";
				rep_cf_tdx_recon($task, $sc_date_fr, $sc_date_to, $sc_client, $sc_group, $xfile);
			}
		}
		elseif ($collect_fixed == 'cf_sigma_trans')
		{
			#set_time_limit($time_limit);
			rep_cf_sigma_trans('s', $sc_date_fr, $sc_date_to, $sc_client, $sc_group);
			if ($task == 'x') # Export to Excel
			{
				$xfile = "cf_sigma_trans_{$USER['USER_ID']}";
				rep_cf_sigma_trans($task, $sc_date_fr, $sc_date_to, $sc_client, $sc_group, $xfile);
			}
		}
		elseif ($collect_fixed == 'cf_sigma_dial')
		{
			#set_time_limit($time_limit);
			rep_cf_sigma_dial('s', $sc_date_fr, $sc_date_to, $sc_client, $sc_group);
			if ($task == 'x') # Export to Excel
			{
				$xfile = "cf_sigma_dial_{$USER['USER_ID']}";
				rep_cf_sigma_dial($task, $sc_date_fr, $sc_date_to, $sc_client, $sc_group, $xfile);
			}
		}
		elseif ($collect_fixed == 'cf_sigma_arr')
		{
			#set_time_limit($time_limit);
			rep_cf_sigma_arr('s', $sc_date_fr, $sc_date_to, $sc_client, $sc_group);
			if ($task == 'x') # Export to Excel
			{
				$xfile = "cf_sigma_arr_{$USER['USER_ID']}";
				rep_cf_sigma_arr($task, $sc_date_fr, $sc_date_to, $sc_client, $sc_group, $xfile);
			}
		}
		elseif ($collect_fixed == 'cf_sigma_close')
		{
			#set_time_limit($time_limit);
			rep_cf_sigma_close('s', $sc_date_fr, $sc_date_to, $sc_client, $sc_group);
			if ($task == 'x') # Export to Excel
			{
				$xfile = "cf_sigma_close_{$USER['USER_ID']}";
				rep_cf_sigma_close($task, $sc_date_fr, $sc_date_to, $sc_client, $sc_group, $xfile);
			}
		}
		elseif ($collect_fixed == 'cf_sigma_remit')
		{
			#set_time_limit($time_limit);
			rep_cf_sigma_remit('s', $sc_date_fr, $sc_date_to, $sc_client, $sc_group);
			if ($task == 'x') # Export to Excel
			{
				$xfile = "cf_sigma_remit_{$USER['USER_ID']}";
				rep_cf_sigma_remit($task, $sc_date_fr, $sc_date_to, $sc_client, $sc_group, $xfile);
			}
		}
		elseif ($collect_fixed == 'cf_noncol')
		{
			#set_time_limit($time_limit);
			rep_cf_noncol('s', $sc_date_fr, $sc_date_to);
			if ($task == 'x') # Export to Excel
			{
				$xfile = "cf_noncol_{$USER['USER_ID']}";
				rep_cf_noncol($task, $sc_date_fr, $sc_date_to, $xfile);
			}
		}
		elseif ($collect_fixed == 'cf_data_8')
		{
			#set_time_limit($time_limit);
			rep_cf_data_8('s', $sc_date_fr, $sc_date_to);
			if (($task == 'x') || ($task == 'c')) # Export to Excel or UTF-8 CSV
			{
				$xfile = "cf_data_8_{$USER['USER_ID']}";
				rep_cf_data_8($task, $sc_date_fr, $sc_date_to, $xfile);
			}
		}
		else
			dprint("Report \"$collect_fixed\" not recognised", true);
	}
	elseif ($trace_statistics)
	{
		if ($trace_statistics == 'ts_num_jobs')
		{
			#set_time_limit($time_limit);
			rep_ts_num_jobs('s', $sc_date_fr, $sc_date_to, $sc_client, $sc_group);
			if ($task == 'x') # Export to Excel
			{
				$xfile = "ts_num_jobs_{$USER['USER_ID']}";
				rep_ts_num_jobs($task, $sc_date_fr, $sc_date_to, $sc_client, $sc_group, $xfile);
			}
		}
		elseif ($trace_statistics == 'ts_days_behind')
		{
			#set_time_limit($time_limit);
			rep_ts_days_behind('s', $sc_date_fr, $sc_date_to, $sc_client, $sc_group);
			if ($task == 'x') # Export to Excel
			{
				$xfile = "ts_days_behind_{$USER['USER_ID']}";
				rep_ts_days_behind($task, $sc_date_fr, $sc_date_to, $sc_client, $sc_group, $xfile);
			}
		}
		elseif ($trace_statistics == 'ts_success_client')
		{
			#set_time_limit($time_limit);
			rep_ts_success_client('s', $sc_date_fr, $sc_date_to, $sc_client, $sc_group);
			if ($task == 'x') # Export to Excel
			{
				$xfile = "ts_success_client_{$USER['USER_ID']}";
				rep_ts_success_client($task, $sc_date_fr, $sc_date_to, $sc_client, $sc_group, $xfile);
			}
		}
		elseif ($trace_statistics == 'ts_breakdown')
		{
			#set_time_limit($time_limit);
			rep_ts_breakdown('s', $sc_date_fr, $sc_date_to, $sc_client, $sc_group);
			if ($task == 'x') # Export to Excel
			{
				$xfile = "ts_breakdown_{$USER['USER_ID']}";
				rep_ts_breakdown($task, $sc_date_fr, $sc_date_to, $sc_client, $sc_group, $xfile);
			}
		}
		elseif ($trace_statistics == 'ts_client_month')
		{
			#set_time_limit($time_limit);
			rep_ts_client_month('s', $sc_date_fr, $sc_date_to, $sc_sales, $sc_client, $sc_group);
			if ($task == 'x') # Export to Excel
			{
				$xfile = "ts_client_month_{$USER['USER_ID']}";
				rep_ts_client_month($task, $sc_date_fr, $sc_date_to, $sc_sales, $sc_client, $sc_group, $xfile);
			}
		}
		elseif ($trace_statistics == 'ts_user_rate')
		{
			#set_time_limit($time_limit);
			rep_ts_user_rate('s', $sc_date_fr, $sc_date_to, $sc_agent, $sc_client, $sc_group);
			if ($task == 'x') # Export to Excel
			{
				$xfile = "ts_user_rate_{$USER['USER_ID']}";
				rep_ts_user_rate($task, $sc_date_fr, $sc_date_to, $sc_agent, $sc_client, $sc_group, $xfile);
			}
		}
		elseif ($trace_statistics == 'ts_user_invoices')
		{
			rep_ts_user_invoices('s', $sc_date_fr, $sc_date_to, $sc_agent, $sc_client, $sc_group);
			if ($task == 'x') # Export to Excel
			{
				$xfile = "ts_user_invoices_{$USER['USER_ID']}";
				rep_ts_user_invoices($task, $sc_date_fr, $sc_date_to, $sc_agent, $sc_client, $sc_group, $xfile);
			}
		}
		elseif ($trace_statistics == 'ts_success_user')
		{
			#set_time_limit($time_limit);
			rep_ts_success_user('s', $sc_date_fr, $sc_date_to, $sc_agent, $sc_client, $sc_group);
			if ($task == 'x') # Export to Excel
			{
				$xfile = "ts_success_user_{$USER['USER_ID']}";
				rep_ts_success_user($task, $sc_date_fr, $sc_date_to, $sc_agent, $sc_client, $sc_group, $xfile);
			}
		}
		elseif ($trace_statistics == 'ts_vs_col_summ')
		{
			printD("
				<div style=\"color:blue;\">
				<h3>View/Search job summaries</h3>
				<p>Please use the \"Jobs\" screen for this.</p>
				</div>
				");
		}
		elseif ($trace_statistics == 'ts_stmt_inv')
		{
			#set_time_limit($time_limit);
			rep_ts_stmt_inv('s', $sc_date_fr);
			if ($task == 'x') # Export to Excel
			{
				$xfile = "ts_stmt_inv_{$USER['USER_ID']}";
				rep_ts_stmt_inv('x', $sc_date_fr, $xfile);
			}
		}
		else
			dprint("Report \"$trace_statistics\" not recognised", true);
	}
	#elseif ($trace_fixed)
	#{
	#	dprint("Report not yet implemented", true);
	#}

	printD("
	</div><!--div_form-->
	");

	printD("
	<script type=\"text/javascript\">
	document.getElementById('div_wait').style.display = 'none';
	document.getElementById('div_form').style.display = 'block';
	document.getElementById('page_title').innerHTML = '$page_title_2';
	</script>
	");

} # screen_content()

function screen_content_2()
{
	# This is required by screen_layout()
} # screen_content_2()

function print_form()
{
	global $access_collect;
	global $access_trace;
	global $ar;
	global $at;
	global $bold_colour;
	global $client_groups; # from sql_get_client_groups()
	global $col2;
	global $col3;
	global $collect_fixed;
	global $collect_statistics;
	global $csv_dir;
	global $reports_title_width; # settings.php
	global $reps_subdir;
	global $salespersons;
	global $sc_agent;
	global $sc_client;
	global $sc_date_fr;
	global $sc_date_to;
	global $sc_dtcol_fr;
	global $sc_dtcol_to;
	global $sc_group;
	global $sc_job_type;
	global $sc_rt_di;
	global $sc_rt_fw;
	global $sc_rt_tv;
	global $sc_sales;
	global $sz_date;
	#global $trace_fixed;
	global $trace_statistics;
	global $tr_colour_1;
	global $USER;

	$calendar_names = array();

	$show_agent_c = 'none';
	$show_agent_t = 'none';
	if (($collect_statistics == '') && ($collect_fixed == '') && ($trace_statistics == '')) # && ($trace_fixed == ''))
	{
		$nothing_selected = true;
		if ($access_collect)
			$show_agent_c = 'inline';
		else
			$show_agent_t = 'inline';
	}
	else
	{
		$nothing_selected = false;
		if ($collect_statistics || $collect_fixed)
			$show_agent_c = 'inline';
		else
			$show_agent_t = 'inline';
	}
	$show_agent_c = " style=\"display:{$show_agent_c};\" ";
	$show_agent_t = " style=\"display:{$show_agent_t};\" ";

	if (($collect_fixed == 'cf_single_client') || ($collect_fixed == 'cf_stair_step'))
	{
		$dtcol_disp = 'inline';
		#$blank_disp = 'none';
	}
	else
	{
		$dtcol_disp = 'none';
		#$blank_disp = 'inline';
	}
	$dtcol_disp = " style=\"display:$dtcol_disp\" ";
	#$blank_disp = " style=\"display:$blank_disp\" ";

	$client_code = intval($sc_client);
	$client_name = '';
	if ($client_code > 0)
		$client_name = client_name_from_code($client_code);

	# $data_8_dt for cf_data_8 report
	$data_8_dt = misc_info_read('C', 'DATA_8_DT', true);
	if ((!$data_8_dt) || ($data_8_dt < "2017-01-01"))
		$data_8_dt = "2017-01-01";
	$data_8_dt = date_for_sql($data_8_dt, true, false);

	$gap_1 = " style=\"width:10px\" ";
	#$gap_2 = "&nbsp;&nbsp;";

	$sel_w = " style=\"width:230px;\" ";

	$blank_line = "
		<tr>
			<td>&nbsp;</td>
		</tr>
		";
//	$blank_line_2 = "
//		<tr>
//			<td><span id=\"span_blank\" $blank_disp>&nbsp;</span></td>
//		</tr>
//		";

	printD("
	<table>
	<tr>
		<td width=\"$reports_title_width\" style=\"font-weight:bold; font-size:20px;\">Fixed Reports</td>
		<td width=\"$reports_title_width\">" . input_button('Custom Reports', 'custom_reports()') . "</td>
	</tr>
	</table>

	<div id=\"div_form_main\" style=\"background-color:{$tr_colour_1};\">
	<hr>
	<p>Please select a report and optionally select one or more filters.</p>
	<form name=\"form_main\" action=\"" . server_php_self() . "\" method=\"post\">
		" . input_hidden('task', '') . "
	<table border=\"0\"><!---->
	");
	if ($access_collect)
	{
		printD("
		<tr>
			<td><b>Collect system:</b></td>
			<td $gap_1></td>
			<td>View Statistics</td>
			<td>" . input_select('collect_statistics',
						array(	'cs_col_rate_client' => 'Collection rate per client',
								'cs_jobs_client' => 'Jobs per client in month',
								'cs_col_rate_agent' => 'Summary of user collection rates',
								'cs_col_sing_agent' => 'Collections by single user',
								'cs_stat_client' => 'Job statistics per client',
								'cs_vs_col_summ' => 'View/Search collection summaries',
								'cs_stmt_inv' => 'Statement Invoices'
							 ),
						$collect_statistics,
						"onchange=\"col_stat_change()\" $sel_w") . "
			</td>
		</tr>
		<tr>
			<td></td>
			<td></td>
			<td>Fixed Reports</td>
			<td>" . # Update 13/04/16: Steve wanted the "TDX" prefix to be taken off the TDX reports
				input_select('collect_fixed',
						array(	'cf_single_client' => 'Single Client Payment Listing',
								'cf_stair_step' => 'Stair Step Payment Listing',
								'cf_tdx_trans' => 'Transaction Report',
								'cf_tdx_close' => 'Write-off Report',
								'cf_tdx_recon' => 'Reconciliation Report',
								'cf_sigma_trans' => 'Sigma Transaction Report',
								'cf_sigma_dial' => 'Sigma Non-Diallers Event Report',
								'cf_sigma_arr' => 'Sigma Arrangement Report',
								'cf_sigma_close' => 'Sigma Closures Report',
								'cf_sigma_remit' => 'Sigma Weekly Remittance Summary',
								'cf_noncol' => 'Non-Collections',
								'cf_data_8' => 'Data 8 Phone Numbers'
							 ),
						$collect_fixed,
						"onchange=\"col_fix_change()\" $sel_w") . "</td>
			" . input_hidden('data_8_dt', $data_8_dt) . "
		</tr>
		");
	}
	printD("
	$blank_line
	");
	if ($access_trace)
	{
		printD("
		<tr>
			<td><b>Trace system:</b></td>
			<td></td>
			<td>View Statistics</td>
			<td>" . input_select('trace_statistics',
						array(	'ts_num_jobs' => 'Number of jobs to be processed',
								'ts_days_behind' => 'Number of days behind',
								'ts_success_client' => 'Success rate per client',
								'ts_breakdown' => 'Breakdown of job types',
								'ts_client_month' => 'Jobs per client in month',
								'ts_user_rate' => 'Summary of user success rates',
								'ts_user_invoices' => 'Monthly user invoice amounts',
								'ts_success_user' => 'Successful jobs for single user',
								'ts_vs_col_summ' => 'View/search job summaries',
								'ts_stmt_inv' => 'Statement Invoices'
							 ),
						$trace_statistics,
						"onchange=\"tra_stat_change()\" $sel_w") . "</td>
		</tr>
		");
//		printD("
//		<tr>
//			<td></td>
//			<td></td>
//			<td>Fixed Reports</td>
//			<td>" . input_select('trace_fixed',
//						array(	'tf_f1' => 'Fixed Report 1',
//								'tf_f2' => 'Fixed Report 2'
//							 ),
//						$trace_fixed,
//						"onchange=\"tra_fix_change()\" $sel_w") . "</td>
//		</tr>
//		";
	}
	printD("
	$blank_line
	</table>
	");

	sql_get_client_groups(false); # writes to $client_groups
	$salespersons = sql_get_salespersons(true, true);
	$c_agents = sql_get_agents('c', true, false); # get list of collection agents
	$t_agents = sql_get_agents('t', true, false); # get list of trace agents
	$job_types = sql_get_job_types(true, false);

	if ($nothing_selected || ($collect_statistics == 'cs_col_rate_client') ||
		($collect_statistics == 'cs_jobs_client') || ($collect_statistics == 'cs_col_sing_agent') || ($collect_statistics == 'cs_stat_client') ||
		($collect_fixed == 'cf_single_client') || ($collect_fixed == 'cf_stair_step') ||
		($collect_fixed == 'cf_tdx_trans') || ($collect_fixed == 'cf_tdx_close') || ($collect_fixed == 'cf_tdx_recon') ||
		($collect_fixed == 'cf_sigma_trans') || ($collect_fixed == 'cf_sigma_dial') || ($collect_fixed == 'cf_sigma_arr') ||
		($collect_fixed == 'cf_sigma_close') || ($collect_fixed == 'cf_sigma_remit') ||
		($trace_statistics == 'ts_num_jobs') || ($trace_statistics == 'ts_days_behind') || ($trace_statistics == 'ts_success_client') ||
		($trace_statistics == 'ts_breakdown') || ($trace_statistics == 'ts_client_month') || ($trace_statistics == 'ts_user_rate') ||
		($trace_statistics == 'ts_user_invoices') || ($trace_statistics == 'ts_success_user'))
	{
		$sc_client_dis = '';
		$sc_group_dis = '';
	}
	else
	{
		$sc_client_dis = 'disabled';
		$sc_group_dis = 'disabled';
	}

	if ($nothing_selected || ($collect_statistics == 'cs_jobs_client') || ($trace_statistics == 'ts_client_month'))
		$sc_sales_dis = '';
	else
		$sc_sales_dis = 'disabled';

	if ($nothing_selected || ($collect_statistics == 'cs_col_sing_agent') || ($trace_statistics == 'ts_user_rate') || ($trace_statistics == 'ts_user_invoices') ||
		($trace_statistics == 'ts_success_user'))
		$sc_agent_dis = '';
	else
		$sc_agent_dis = 'disabled';

	if ($nothing_selected && ($collect_fixed == ''))
		$sc_job_type_dis = '';
	else
		$sc_job_type_dis = 'disabled';

	if ($nothing_selected)
		$date_desc = '';
	elseif (($collect_statistics == 'cs_col_rate_client') || ($collect_statistics == 'cs_col_rate_agent') ||
			($collect_statistics == 'cs_col_sing_agent') || ($collect_fixed == 'cf_tdx_trans') ||
			($collect_fixed == 'cf_sigma_trans') || ($collect_fixed == 'cf_sigma_remit'))
		$date_desc = 'Collection payments:';
	elseif ($collect_fixed == 'cf_noncol')
		$date_desc = 'Invoice Sent:';
	elseif (($collect_statistics == 'cs_jobs_client') || ($collect_statistics == 'cs_stat_client') ||
			($trace_statistics == 'ts_num_jobs') || ($trace_statistics == 'ts_days_behind') || ($trace_statistics == 'ts_success_client') ||
			($trace_statistics == 'ts_breakdown') || ($trace_statistics == 'ts_client_month') ||
			($collect_fixed == 'cf_single_client') || ($collect_fixed == 'cf_stair_step') || ($collect_fixed == 'cf_data_8')
		   )
		$date_desc = 'Job placements:';
	elseif (($trace_statistics == 'ts_user_rate') || ($trace_statistics == 'ts_user_invoices') || ($trace_statistics == 'ts_success_user'))
		$date_desc = 'Job completions:';
	elseif (($collect_statistics == 'cs_stmt_inv') || ($trace_statistics == 'ts_stmt_inv'))
		$date_desc = 'Statement due:';
	elseif (($collect_fixed == 'cf_tdx_close') || ($collect_fixed == 'cf_sigma_close'))
		$date_desc = 'Job Closeout:';
	elseif (($collect_fixed == 'cf_tdx_recon') || ($collect_fixed == 'cf_sigma_dial'))
		$date_desc = 'Job Activity:';
	elseif ($collect_fixed == 'cf_sigma_arr')
		$date_desc = 'Arrangement start:';
	elseif (($collect_statistics == 'cs_vs_col_summ') || ($trace_statistics == 'ts_vs_col_summ'))
		$date_desc = '';
	else
		$date_desc = '???';

	$add_month_disabled = '';
	if ($collect_fixed == 'cf_data_8')
		$add_month_disabled = 'disabled';

	$onchange = " onchange=\"drop_xl_button()\" ";
	#$onkeydown = " onkeydown=\"drop_xl_button()\" "; # onkeydown causes a backspace to delete two characters the first time it is used!
	$onkeydown = " onkeypress=\"drop_xl_button()\" ";
	$onclientkeyup = " onkeyup=\"find_client()\" ";

	printD("
	<table border=\"0\"><!---->
	<tr>
		<td $col3><b>Filters</b></td><!-- sc - search criteria -->
		<td colspan=\"13\"><span id=\"span_client_name\"
			style=\"color:$bold_colour; font-size:14px; display:" . ($client_name ? 'inline' : 'hidden') . ";\">$client_name</span></td>
	</tr>
	<tr>
		<td></td>
			<td $gap_1></td>
		<td $ar>Client:</td>
			<td>" . input_textbox('sc_client', $sc_client, 0, 0, $onkeydown . $onclientkeyup . $onchange . $sc_client_dis) . "</td>
			<td $gap_1></td>
		<td $ar>Client Group:</td>
			<td>" . input_select('sc_group', $client_groups, $sc_group, $onchange . $sc_group_dis) . "</td>
			<td $gap_1></td>
		<td $ar>Agent:</td>
			<td>
			<div id=\"agents_c\">" . input_select('sc_agent_c', $c_agents, $sc_agent, $show_agent_c . $onchange . $sc_agent_dis) . "</div>
			<div id=\"agents_t\">" . input_select('sc_agent_t', $t_agents, $sc_agent, $show_agent_t . $onchange . $sc_agent_dis) . "</div>
			</td>
			<td $gap_1></td>
		<td $ar>Salesperson:</td>
			<td>" . input_select('sc_sales', $salespersons, $sc_sales, $onchange . $sc_sales_dis) . "</td>
			<td $gap_1></td>
		<td>" . input_button('Clear Filters', "clear_filters()") . "</td>
	</tr>
	<tr>
		<td></td>
		<td></td>
		<td $col2 $ar><span id=\"span_date\">$date_desc</span></td><td></td>
		");
		$calendar_names[] = "sc_date_fr";
		$calendar_names[] = "sc_date_to";
		printD("
		<td $ar>Date from:</td><td>" . input_textbox('sc_date_fr', $sc_date_fr, $sz_date, 0, $onkeydown . $onchange) .
									calendar_icon('sc_date_fr') . "</td><td></td>
		<td $ar>Date to:</td><td>" . input_textbox('sc_date_to', $sc_date_to, $sz_date, 0, $onkeydown . $onchange) .
									calendar_icon('sc_date_to') . "&nbsp;" . input_button('+1m', "add_month(false)", $add_month_disabled, 'id_add_month') . "</td>
		<td></td>
		<td $ar>Job Type:</td><td>" . input_select('sc_job_type', $job_types, $sc_job_type, $onchange . $sc_job_type_dis) . "</td>
		<td></td>
		<td></td>
	</tr>
	<tr>
		<td></td>
		<td></td>
		<td $at $col2 $ar><span id=\"span_dtcol_1\" $dtcol_disp>Collection payments:</span></td><td></td>
		");
		$calendar_names[] = "sc_dtcol_fr";
		$calendar_names[] = "sc_dtcol_to";
		printD("
		<td $at $ar><span id=\"span_dtcol_2\" $dtcol_disp>Date from:</span></td>
		<td $at><span id=\"span_dtcol_3\" $dtcol_disp>" . input_textbox('sc_dtcol_fr', $sc_dtcol_fr, $sz_date, 0, $onkeydown . $onchange) .
										calendar_icon('sc_dtcol_fr') . "</span></td>
		<td></td>
		<td $at $ar><span id=\"span_dtcol_4\" $dtcol_disp>Date to:</span></td>
		<td $at><span id=\"span_dtcol_5\" $dtcol_disp>" . input_textbox('sc_dtcol_to', $sc_dtcol_to, $sz_date, 0, $onkeydown . $onchange) .
										calendar_icon('sc_dtcol_to') . "&nbsp;" . input_button('+1m', "add_month(true)") . "</span></td>
		<td></td>
		<td $at><span id=\"span_dtcol_6\" $dtcol_disp>Include Routes:</span></td>
		<td $at><span id=\"span_dtcol_7\" $dtcol_disp>
			" . input_tickbox('To Vilcol', 'sc_rt_tv', 1, $sc_rt_tv ? 'checked' : '') . "<br>
			" . input_tickbox('Forwarded', 'sc_rt_fw', 1, $sc_rt_fw ? 'checked' : '') . "<br>
			" . input_tickbox('Direct', 'sc_rt_di', 1, $sc_rt_di ? 'checked' : '') . "<br>
			</span></td>
		<td></td>
		<td></td>
	</tr>
	<tr>
		<td $col3>" . input_button('Generate Report', 'gen_report()') . "</td>
	</tr>
	</table>
	</form><!--form_main-->
	<hr>
	</div><!--div_form_main-->

	<form name=\"form_csv_download\" action=\"csv_dl.php\" method=\"post\" target=\"_blank\" rel=\"noopener\">
		<input type=\"hidden\" name=\"short_fname\" value=\"\" />
		<input type=\"hidden\" name=\"full_fname\" value=\"\" />
	</form><!--form_csv_download-->

	<form name=\"form_statements\" action=\"clients.php\" method=\"post\" target=\"_blank\" rel=\"noopener\">
		<input type=\"hidden\" name=\"task\" value=\"\" />
		<input type=\"hidden\" name=\"sc_text\" value=\"\" />
	</form><!--form_statements-->
	");

	javascript_calendars($calendar_names);

	# --- Feedback #208 - Show list of Spreadsheets for this user. Reports named "r<reportID>_u<userID>_<type>.xls" ------------------
	$dirname = "$csv_dir/$reps_subdir";
	$dir = opendir($dirname);
	if (!$dir)
	{
		printD("<p>** Could not open '$dirname' directory **</p>");
		return;
	}
	$filelist = array();
	while (($file = readdir($dir)) != false)
	{
		if (	(	(($file[0] == 'c') && ($file[1] == 'f') && ($file[2] == '_')) ||
					(($file[0] == 'c') && ($file[1] == 's') && ($file[2] == '_')) ||
					(($file[0] == 't') && ($file[1] == 's') && ($file[2] == '_'))
				) &&
				(strpos($file, ".xls") !== false)
			)
		{
			$bits = explode('_', $file);
			#dprint("bits=" . print_r($bits,1));
			$temp = $bits[count($bits)-1]; # user ID
			if (0 < intval($temp))
			{
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
	printD("
	<br>
	<h3>Spreadsheets</h3>
	");
	foreach ($filelist as $fileinfo)
		printD("<a href=\"$dirname/{$fileinfo['FN']}\">{$fileinfo['FN']} ... {$fileinfo['DT']}</a><br>");
	# --------------------------------------------------------------------------------------------------------------------------------

} # print_form()

function javascript()
{
	global $csv_path;
	global $reps_subdir;

	printD("
	<script type=\"text/javascript\">

	var tdx_clients = '4239,4240,4241,4242,4347,6603,6604,6605,6606';
	var sigma_clients = '4461,4826';

	function create_statements(sys,cids)
	{
		document.form_statements.task.value = 'pre_statements_' + sys;
		document.form_statements.sc_text.value = cids;
		document.form_statements.submit();
	}

	function custom_reports()
	{
		document.location.href = 'reports_custom.php';
	}

	function find_client()
	{
		var ccode = trim(document.getElementById('sc_client').value.replace(' ',''));
		var catcode = ccode.replace(/,/g,'');
		//alert(catcode);//
		if (isNumeric(catcode, false, false, false, false))
		{
			xmlHttp2 = GetXmlHttpObject();
			if (xmlHttp2 == null)
				return;
			var url = 'clients_ajax.php?op=fc&c=' + ccode + '&ran=' + Math.random();
			//alert(url);
			xmlHttp2.onreadystatechange = stateChanged_find_client;
			xmlHttp2.open('GET', url, true);
			xmlHttp2.send(null);
		}
		else
		{
			var el = document.getElementById('span_client_name');
			if (el)
			{
				el.innerHTML = '';
				el.style.display = 'none';
			}
			else
				alert('Element \"span_client_name\" not found');
		}
	}

	function stateChanged_find_client()
	{
		if (xmlHttp2.readyState == 4)
		{
			var resptxt = xprint_noscript(xmlHttp2.responseText);
			if (resptxt)
			{
				var el = document.getElementById('span_client_name');
				if (el)
				{
					var bits = resptxt.split('|');
					if (bits[0] == '1')
					{
						el.innerHTML = bits[1];
						el.style.display = 'inline';
					}
					else
					{
						el.innerHTML = '';
						el.style.display = 'none';
						if (bits[0] == -1)
							alert(bits[1]);
					}
				}
				else
					alert('Element \"span_client_name\" not found');
			}
		}
	}

	function add_month(second)
	{
		var fr = (second ? document.form_main.sc_dtcol_fr.value : document.form_main.sc_date_fr.value);
		if (fr && checkDate(fr, 'From', ''))
		{
			fr = dateToSql(fr);
			var delim1 = fr.indexOf('-');
			var delim2 = fr.lastIndexOf('-');
			var yr = parseInt(fr.substring(0, 4), 10);
			var mo = parseInt(fr.substring(5, 7), 10);
			var day = parseInt(fr.substring(8, 10), 10);

			// Initially, assume all months have 31 days

			// Add a month
			if (mo < 12)
				mo = mo + 1;
			else
			{
				mo = 1;
				yr = yr + 1;
			}

			// Subtract a day
			if (1 < day)
				day = day - 1;
			else
			{
				day = 31;
				if (1 < mo)
					mo = mo - 1;
				else
				{
					mo = 12;
					yr = yr - 1;
				}
			}

			// Check day is not too big
			if (mo == 2)
			{
				if ((yr % 4) == 0)
				{
					if (29 < day)
						day = 29;
				}
				else
				{
					if (28 < day)
						day = 28;
				}
			}
			else if ((mo == 4) || (mo == 6) || (mo == 9) || (mo == 11))
			{
				if (30 < day)
					day = 30;
			}
			if (day < 10)
				day = '0' + day;
			if (mo < 10)
				mo = '0' + mo;

			var to = '' + day + '/' + mo + '/' + yr;
			if (second)
				document.form_main.sc_dtcol_to.value = to;
			else
				document.form_main.sc_date_to.value = to;
		}
	}

	function clear_filters()
	{
		document.form_main.sc_client.value = '';
		document.form_main.sc_group.value = '';
		document.form_main.sc_sales.value = '';
		document.form_main.sc_agent_c.value = '';
		document.form_main.sc_agent_t.value = '';
		document.form_main.sc_date_fr.value = '';
		document.form_main.sc_date_to.value = '';
		document.form_main.sc_dtcol_fr.value = '';
		document.form_main.sc_dtcol_to.value = '';
		document.form_main.sc_job_type.value = '';
	}

	function csv_download(fname)
	{
		//alert(fname);
		document.form_csv_download.short_fname.value = fname;
		document.form_csv_download.full_fname.value = '$csv_path' + '{$reps_subdir}/' + fname;
		document.form_csv_download.submit();
	}

	function col_stat_change()
	{
		var col_stat = document.getElementById('collect_statistics');
		var col_fix = document.getElementById('collect_fixed');
		var tra_stat = document.getElementById('trace_statistics');
		//var tra_fix = document.getElementById('trace_fixed');
		var d_span = document.getElementById('span_date');
		var dt_fr = document.getElementById('sc_date_fr');
		var dt_to = document.getElementById('sc_date_to');
		var cl_txt = document.getElementById('sc_client');
		var gr_sel = document.getElementById('sc_group');
		var sa_sel = document.getElementById('sc_sales');
		var ag_sel_c = document.getElementById('sc_agent_c');
		var ag_sel_t = document.getElementById('sc_agent_t');
		var jt_sel = document.getElementById('sc_job_type');
		var dtc_1 = document.getElementById('span_dtcol_1');
		var dtc_2 = document.getElementById('span_dtcol_2');
		var dtc_3 = document.getElementById('span_dtcol_3');
		var dtc_4 = document.getElementById('span_dtcol_4');
		var dtc_5 = document.getElementById('span_dtcol_5');
		var dtc_6 = document.getElementById('span_dtcol_6');
		var dtc_7 = document.getElementById('span_dtcol_7');
		//var sblank = document.getElementById('span_blank');

		if (col_stat)
		{
			if (col_stat.value != '')
			{
				if (col_fix)
					col_fix.value = '';
				if (tra_stat)
					tra_stat.value = '';
				//if (tra_fix)
				//	tra_fix.value = '';
				if (d_span)
				{
					var disab = false;
					if ((col_stat.value == 'cs_col_rate_client') || (col_stat.value == 'cs_col_rate_agent') || (col_stat.value == 'cs_col_sing_agent'))
						d_span.innerHTML = 'Collection payments:';
					else if ((col_stat.value == 'cs_jobs_client') || (col_stat.value == 'cs_stat_client'))
						d_span.innerHTML = 'Job placements:';
					else if (col_stat.value == 'cs_stmt_inv')
						d_span.innerHTML = 'Statement due:';
					else
					{
						disab = true;
						if (col_stat.value == 'cs_vs_col_summ')
							d_span.innerHTML = '';
						else
							d_span.innerHTML = '???:';
					}
					if (dt_fr)
					{
						if (disab)
							dt_fr.value = '';
						dt_fr.disabled = disab;
					}
					if (dt_to)
					{
						if (col_stat.value == 'cs_stmt_inv')
							disab_to = true;
						else
							disab_to = disab;
						if (disab_to)
						{
							dt_to.value = '';
							document.getElementById('sc_date_to_trigger').disabled = true;
							document.getElementById('sc_date_to_trigger').style.visibility = 'hidden';
							document.getElementById('id_add_month').disabled = true;
						}
						else
						{
							document.getElementById('sc_date_to_trigger').disabled = false;
							document.getElementById('sc_date_to_trigger').style.visibility = 'visible';
							document.getElementById('id_add_month').disabled = false;
						}
						dt_to.disabled = disab_to;
					}
				}
				if (cl_txt)
				{
					if ((col_stat.value == 'cs_col_rate_client') || (col_stat.value == 'cs_jobs_client') ||
						(col_stat.value == 'cs_col_sing_agent') || (col_stat.value == 'cs_stat_client') ||
						(col_stat.value == 'cs_col_rate_agent'))
						cl_txt.disabled = false;
					else
					{
						cl_txt.value = '';
						cl_txt.disabled = true;
					}
				}
				if (gr_sel)
				{
					if ((col_stat.value == 'cs_col_rate_client') || (col_stat.value == 'cs_jobs_client') ||
						(col_stat.value == 'cs_col_sing_agent') || (col_stat.value == 'cs_stat_client') ||
						(col_stat.value == 'cs_col_rate_agent'))
						gr_sel.disabled = false;
					else
					{
						gr_sel.value = '';
						gr_sel.disabled = true;
					}
				}
				if (sa_sel)
				{
					if (col_stat.value == 'cs_jobs_client')
						sa_sel.disabled = false;
					else
					{
						sa_sel.value = '';
						sa_sel.disabled = true;
					}
				}
				if (ag_sel_c)
				{
					if (col_stat.value == 'cs_col_sing_agent')
						ag_sel_c.disabled = false;
					else
					{
						ag_sel_c.value = '';
						ag_sel_c.disabled = true;
					}
					if (ag_sel_t)
					{
						ag_sel_t.value = '';
						ag_sel_t.style.display = 'none';
					}
					ag_sel_c.style.display = 'inline';
				}
				if (jt_sel)
				{
					if (col_stat.value == 'xxx')
						jt_sel.disabled = false;
					else
					{
						jt_sel.value = '';
						jt_sel.disabled = true;
					}
				}
				if (dtc_1)
					dtc_1.style.display = 'none';
				if (dtc_2)
					dtc_2.style.display = 'none';
				if (dtc_3)
					dtc_3.style.display = 'none';
				if (dtc_4)
					dtc_4.style.display = 'none';
				if (dtc_5)
					dtc_5.style.display = 'none';
				if (dtc_6)
					dtc_6.style.display = 'none';
				if (dtc_7)
					dtc_7.style.display = 'none';
				//if (sblank)
				//	sblank.style.display = 'inline';
			}
		}
		drop_xl_button();
	}

	function col_fix_change()
	{
		var col_stat = document.getElementById('collect_statistics');
		var col_fix = document.getElementById('collect_fixed');
		var tra_stat = document.getElementById('trace_statistics');
		//var tra_fix = document.getElementById('trace_fixed');
		var d_span = document.getElementById('span_date');
		var cl_txt = document.getElementById('sc_client');
		var gr_sel = document.getElementById('sc_group');
		var sa_sel = document.getElementById('sc_sales');
		var ag_sel_c = document.getElementById('sc_agent_c');
		var ag_sel_t = document.getElementById('sc_agent_t');
		var jt_sel = document.getElementById('sc_job_type');
		var dtc_1 = document.getElementById('span_dtcol_1');
		var dtc_2 = document.getElementById('span_dtcol_2');
		var dtc_3 = document.getElementById('span_dtcol_3');
		var dtc_4 = document.getElementById('span_dtcol_4');
		var dtc_5 = document.getElementById('span_dtcol_5');
		var dtc_6 = document.getElementById('span_dtcol_6');
		var dtc_7 = document.getElementById('span_dtcol_7');
		//var sblank = document.getElementById('span_blank');
		var dt_fr = document.getElementById('sc_date_fr');
		var dt_to = document.getElementById('sc_date_to');

		if (col_fix)
		{
			if (col_fix.value != '')
			{
				if (col_stat)
					col_stat.value = '';
				if (tra_stat)
					tra_stat.value = '';
				//if (tra_fix)
				//	tra_fix.value = '';
				if (d_span)
				{
					if ((col_fix.value == 'cf_single_client') || (col_fix.value == 'cf_stair_step'))
					{
						d_span.innerHTML = 'Job placements:';
						if (dtc_1)
							dtc_1.style.display = 'inline';
						if (dtc_2)
							dtc_2.style.display = 'inline';
						if (dtc_3)
							dtc_3.style.display = 'inline';
						if (dtc_4)
							dtc_4.style.display = 'inline';
						if (dtc_5)
							dtc_5.style.display = 'inline';
						if (dtc_6)
							dtc_6.style.display = 'inline';
						if (dtc_7)
							dtc_7.style.display = 'inline';
						//if (sblank)
						//	sblank.style.display = 'none';
					}
					else
					{
						if ((col_fix.value == 'cf_tdx_trans') || (col_fix.value == 'cf_sigma_trans') || (col_fix.value == 'cf_sigma_remit'))
							d_span.innerHTML = 'Collection Payments:';
						else if (col_fix.value == 'cf_noncol')
							d_span.innerHTML = 'Invoice Sent:';
						else if ((col_fix.value == 'cf_tdx_close') || (col_fix.value == 'cf_sigma_close'))
							d_span.innerHTML = 'Job Closeout:';
						else if ((col_fix.value == 'cf_tdx_recon') || (col_fix.value == 'cf_sigma_dial'))
							d_span.innerHTML = 'Job Activity:';
						else if (col_fix.value == 'cf_sigma_arr')
							d_span.innerHTML = 'Arrangement start:';
						else if (col_fix.value == 'cf_data_8')
						{
							d_span.innerHTML = 'Job placements:';
							if (dt_fr)
								dt_fr.value = document.getElementById('data_8_dt').value;
							if (dt_to)
							{
								dt_to.value = '';
								dt_to.disabled = true;
								document.getElementById('id_add_month').disabled = true;
							}

						}
						else
							d_span.innerHTML = '???:';
						if (dtc_1)
							dtc_1.style.display = 'none';
						if (dtc_2)
							dtc_2.style.display = 'none';
						if (dtc_3)
							dtc_3.style.display = 'none';
						if (dtc_4)
							dtc_4.style.display = 'none';
						if (dtc_5)
							dtc_5.style.display = 'none';
						if (dtc_6)
							dtc_6.style.display = 'none';
						if (dtc_7)
							dtc_7.style.display = 'none';
						//if (sblank)
						//	sblank.style.display = 'inline';
					}
				}
				if (cl_txt)
				{
					if ((col_fix.value == 'cf_single_client') || (col_fix.value == 'cf_stair_step') ||
						(col_fix.value == 'cf_tdx_trans') || (col_fix.value == 'cf_tdx_close') || (col_fix.value == 'cf_tdx_recon') ||
						(col_fix.value == 'cf_sigma_trans') || (col_fix.value == 'cf_sigma_dial') || (col_fix.value == 'cf_sigma_arr') ||
						(col_fix.value == 'cf_sigma_close') || (col_fix.value == 'cf_sigma_remit'))
						cl_txt.disabled = false;
					else
					{
						cl_txt.value = '';
						cl_txt.disabled = true;
					}
				}
				if (gr_sel)
				{
					if ((col_fix.value == 'cf_single_client') || (col_fix.value == 'cf_stair_step') ||
						(col_fix.value == 'cf_tdx_trans') || (col_fix.value == 'cf_tdx_close') || (col_fix.value == 'cf_tdx_recon') ||
						(col_fix.value == 'cf_sigma_trans') || (col_fix.value == 'cf_sigma_dial') || (col_fix.value == 'cf_sigma_arr') ||
						(col_fix.value == 'cf_sigma_close') || (col_fix.value == 'cf_sigma_remit'))
						gr_sel.disabled = false;
					else
					{
						gr_sel.value = '';
						gr_sel.disabled = true;
					}
				}
				if (sa_sel)
				{
					if (0)
						sa_sel.disabled = false;
					else
					{
						sa_sel.value = '';
						sa_sel.disabled = true;
					}
				}
				if (ag_sel_c)
				{
					if (0)
						ag_sel_c.disabled = false;
					else
					{
						ag_sel_c.value = '';
						ag_sel_c.disabled = true;
					}
					if (ag_sel_t)
					{
						ag_sel_t.value = '';
						ag_sel_t.style.display = 'none';
					}
					ag_sel_c.style.display = 'inline';
				}
				if (jt_sel)
				{
					if (0)
						jt_sel.disabled = false;
					else
					{
						jt_sel.value = '';
						jt_sel.disabled = true;
					}
				}
				if ((col_fix.value == 'cf_tdx_trans') || (col_fix.value == 'cf_tdx_close') || (col_fix.value == 'cf_tdx_recon'))
				{
					var proceed = true;
					if ((cl_txt.value && (cl_txt.value != tdx_clients)) || gr_sel.value)
					{
						if (!confirm('Do you want to do this for clients ' + tdx_clients + '?'))
							proceed = false;
					}
					if (proceed)
					{
						cl_txt.value = tdx_clients;
						gr_sel.value = '';
						find_client(); // for cl_txt
					}
				}
				else if ((col_fix.value == 'cf_sigma_trans') || (col_fix.value == 'cf_sigma_dial') || (col_fix.value == 'cf_sigma_arr') ||
							(col_fix.value == 'cf_sigma_close') || (col_fix.value == 'cf_sigma_remit'))
				{
					var proceed = true;
					if ((cl_txt.value && (cl_txt.value != sigma_clients)) || gr_sel.value)
					{
						if (!confirm('Do you want to do this for clients ' + sigma_clients + '?'))
							proceed = false;
					}
					if (proceed)
					{
						cl_txt.value = sigma_clients;
						gr_sel.value = '';
						find_client(); // for cl_txt
					}
				}

			} // if (col_fix.value != '')
		} // if (col_fix)
		drop_xl_button();
	}

	function tra_stat_change()
	{
		var col_stat = document.getElementById('collect_statistics');
		var col_fix = document.getElementById('collect_fixed');
		var tra_stat = document.getElementById('trace_statistics');
		//var tra_fix = document.getElementById('trace_fixed');
		var d_span = document.getElementById('span_date');
		var dt_fr = document.getElementById('sc_date_fr');
		var dt_to = document.getElementById('sc_date_to');
		var cl_txt = document.getElementById('sc_client');
		var gr_sel = document.getElementById('sc_group');
		var sa_sel = document.getElementById('sc_sales');
		var ag_sel_c = document.getElementById('sc_agent_c');
		var ag_sel_t = document.getElementById('sc_agent_t');
		var jt_sel = document.getElementById('sc_job_type');
		var dtc_1 = document.getElementById('span_dtcol_1');
		var dtc_2 = document.getElementById('span_dtcol_2');
		var dtc_3 = document.getElementById('span_dtcol_3');
		var dtc_4 = document.getElementById('span_dtcol_4');
		var dtc_5 = document.getElementById('span_dtcol_5');
		var dtc_6 = document.getElementById('span_dtcol_6');
		var dtc_7 = document.getElementById('span_dtcol_7');
		//var sblank = document.getElementById('span_blank');

		if (tra_stat)
		{
			if (tra_stat.value != '')
			{
				if (col_stat)
					col_stat.value = '';
				if (col_fix)
					col_fix.value = '';
				//if (tra_fix)
				//	tra_fix.value = '';
				if (d_span)
				{
					var disab = false;
					if ((tra_stat.value == 'ts_num_jobs') || (tra_stat.value == 'ts_days_behind') || (tra_stat.value == 'ts_success_client') || (tra_stat.value == 'ts_breakdown') || (tra_stat.value == 'ts_client_month'))
						d_span.innerHTML = 'Job placements:';
					else if ((tra_stat.value == 'ts_user_rate') || (tra_stat.value == 'ts_user_invoices') || (tra_stat.value == 'ts_success_user'))
						d_span.innerHTML = 'Job completions:';
					else if (tra_stat.value == 'ts_stmt_inv')
						d_span.innerHTML = 'Statement due:';
					else
					{
						disab = true;
						if (tra_stat.value == 'ts_vs_col_summ')
							d_span.innerHTML = '';
						else
							d_span.innerHTML = '???:';
					}
					if (dt_fr)
					{
						if (disab)
							dt_fr.value = '';
						dt_fr.disabled = disab;
					}
					if (dt_to)
					{
						if (tra_stat.value == 'ts_stmt_inv')
							disab_to = true;
						else
							disab_to = disab;
						if (disab_to)
						{
							dt_to.value = '';
							document.getElementById('sc_date_to_trigger').disabled = true;
							document.getElementById('sc_date_to_trigger').style.visibility = 'hidden';
							document.getElementById('id_add_month').disabled = true;
						}
						else
						{
							document.getElementById('sc_date_to_trigger').disabled = false;
							document.getElementById('sc_date_to_trigger').style.visibility = 'visible';
							document.getElementById('id_add_month').disabled = false;
						}
						dt_to.disabled = disab_to;
					}
				}
				if (cl_txt)
				{
					if ((tra_stat.value == 'ts_num_jobs') || (tra_stat.value == 'ts_days_behind') ||
						(tra_stat.value == 'ts_success_client') || (tra_stat.value == 'ts_breakdown') ||
						(tra_stat.value == 'ts_client_month') || (tra_stat.value == 'ts_user_rate') || (tra_stat.value == 'ts_user_invoices') ||
						(tra_stat.value == 'ts_success_user'))
						cl_txt.disabled = false;
					else
					{
						cl_txt.value = '';
						cl_txt.disabled = true;
					}
				}
				if (gr_sel)
				{
					if ((tra_stat.value == 'ts_num_jobs') || (tra_stat.value == 'ts_days_behind') ||
						(tra_stat.value == 'ts_success_client') || (tra_stat.value == 'ts_breakdown') ||
						(tra_stat.value == 'ts_client_month') || (tra_stat.value == 'ts_user_rate') || (tra_stat.value == 'ts_user_invoices') ||
						(tra_stat.value == 'ts_success_user'))
						gr_sel.disabled = false;
					else
					{
						gr_sel.value = '';
						gr_sel.disabled = true;
					}
				}
				if (sa_sel)
				{
					if (tra_stat.value == 'ts_client_month')
						sa_sel.disabled = false;
					else
					{
						sa_sel.value = '';
						sa_sel.disabled = true;
					}
				}
				if (ag_sel_t)
				{
					if ((tra_stat.value == 'ts_user_rate') || (tra_stat.value == 'ts_user_invoices') || (tra_stat.value == 'ts_success_user'))
						ag_sel_t.disabled = false;
					else
					{
						ag_sel_t.value = '';
						ag_sel_t.disabled = true;
					}
					if (ag_sel_c)
					{
						ag_sel_c.value = '';
						ag_sel_c.style.display = 'none';
					}
					ag_sel_t.style.display = 'inline';
				}
				if (jt_sel)
				{
					if (0)
						jt_sel.disabled = false;
					else
					{
						jt_sel.value = '';
						jt_sel.disabled = true;
					}
				}
				if (dtc_1)
					dtc_1.style.display = 'none';
				if (dtc_2)
					dtc_2.style.display = 'none';
				if (dtc_3)
					dtc_3.style.display = 'none';
				if (dtc_4)
					dtc_4.style.display = 'none';
				if (dtc_5)
					dtc_5.style.display = 'none';
				if (dtc_6)
					dtc_6.style.display = 'none';
				if (dtc_7)
					dtc_7.style.display = 'none';
				//if (sblank)
				//	sblank.style.display = 'inline';
			}
		}
		drop_xl_button();
	}

	function pre_submit_check()
	{
		var col_stat = document.getElementById('collect_statistics');
		var col_fix = document.getElementById('collect_fixed');
		var tra_stat = document.getElementById('trace_statistics');
		//var tra_fix = document.getElementById('trace_fixed');
		var ag_sel_c = document.getElementById('sc_agent_c');
		var ag_sel_t = document.getElementById('sc_agent_t');
		var cl_txt = document.getElementById('sc_client');
		var gr_sel = document.getElementById('sc_group');
		var date_fr = document.getElementById('sc_date_fr');
		var proceed = false;

		if (	(col_stat && (col_stat.value != '')) ||
				(col_fix && (col_fix.value != '')) ||
				(tra_stat && (tra_stat.value != '')) //||
				//(tra_fix && (tra_fix.value != ''))
			)
		{
			if (col_stat && (col_stat.value == 'cs_col_sing_agent'))
			{
				if (ag_sel_c && (ag_sel_c.value > 0))
					proceed = true;
				else
					alert('Please select an Agent');
			}
			else if (tra_stat && (tra_stat.value == 'ts_success_user'))
			{
				if (ag_sel_t && (ag_sel_t.value > 0))
					proceed = true;
				else
					alert('Please select an Agent');
			}
			else if (  (col_stat && (col_stat.value == 'cs_stat_client')) ||
					   (tra_stat && (tra_stat.value == 'ts_success_client')) ||
					   (col_fix && (	(col_fix.value == 'cf_single_client') || (col_fix.value == 'cf_stair_step') ||
										(col_fix.value == 'cf_tdx_trans') || (col_fix.value == 'cf_tdx_close') ||
										(col_fix.value == 'cf_tdx_recon') || (col_fix.value == 'cf_sigma_trans') ||
										(col_fix.value == 'cf_sigma_dial') || (col_fix.value == 'cf_sigma_arr') ||
										(col_fix.value == 'cf_sigma_close') || (col_fix.value == 'cf_sigma_remit')
									))
					)
			{
				if (cl_txt && (cl_txt.value != ''))
					proceed = true;
				else if (gr_sel && (gr_sel.value != ''))
					proceed = true;
				else
					alert('Please either specify a client or select a client group');
			}
			else if (tra_stat && tra_stat.value == 'ts_breakdown')
			{
				if (date_fr && (date_fr.value != ''))
					proceed = true;
				else
					alert('Please specify a start date');
			}
			else
				proceed = true;
		}
		else
			alert('Please select a report');
		return proceed;
	}

	function drop_xl_button()
	{
		var but_xl = document.getElementById('but_export_xl');
		if (but_xl)
			but_xl.style.visibility = 'hidden';
	}

	function gen_report()
	{
		if (pre_submit_check())
		{
			document.form_main.task.value = 's';
			please_wait_on_submit();
			document.form_main.submit();
		}
	}

//	function export_csv()
//	{
//		if (pre_submit_check())
//		{
//			document.form_main.task.value = 'c';
//			document.form_main.submit();
//		}
//	}

	function export_xl()
	{
		if (pre_submit_check())
		{
			document.form_main.task.value = 'x';
			please_wait_on_submit();
			document.form_main.submit();
		}
	}

	function export_csv()
	{
		if (pre_submit_check())
		{
			document.form_main.task.value = 'c';
			please_wait_on_submit();
			document.form_main.submit();
		}
	}

	</script>
	");
} # javascript()

function rep_cs_col_rate_client($dest, $sc_date_fr, $sc_date_to, $sc_client='', $sc_group=0, $xfile='')
{
	# Collections Report: Collection rate per client.
	# $dest is 's' for screen or 'x' for export to Excel file.
	# $sc_date_fr and $sc_date_to are search criteria that form a date range which
	#		collection payment dates must fall within; either may be blank.
	# $sc_client is search criteria: a client code or part of a client name (or an alpha code)
	# $sc_group is search criteria: a client group ID
	# $xfile is the name of the file that the search results should be written to;
	#		it is used when $dest=='x'

	global $ar;
	global $csv_path;
	global $excel_currency_format;
	global $grey;
	global $id_ROUTE_direct;
	global $id_ROUTE_fwd;
	global $id_ROUTE_tous;
	global $phpExcel_ext; # settings.php: "xls"
	global $sqlFalse;
	global $sqlTrue;
	global $reps_subdir;

	$this_function = "rep_cs_col_rate_client(\"$dest\", \"$sc_date_fr\", \"$sc_date_to\", \"$sc_client\", \"$sc_group\", \"$xfile\")";

	#dprint("Entered: $this_function");#

	if ($sc_date_fr)
	{
		if ($sc_date_to)
			$date_string = "between $sc_date_fr and $sc_date_to";
		else
			$date_string = "from $sc_date_fr";
	}
	elseif ($sc_date_to)
		$date_string = "up to $sc_date_to";
	else
		$date_string = "(all dates)";

	$sc_c_code = intval($sc_client);
	$client_string = '';
	if ($sc_client)
	{
		$client_name = '';
		if ($sc_c_code > 0)
		{
			$client_name = client_name_from_code($sc_c_code);
			if ($client_name)
				$client_name = "$sc_c_code - $client_name";
		}
		if (!$client_name)
			$client_name = $sc_client;
		$client_string .= ", client $client_name";
	}

	$group_id = intval($sc_group);
	if ($group_id > 0)
		$group_string = ", client group \"" . sql_client_group_name_from_id($group_id) . "\"";
	else
		$group_string = '';

	$title_short = "Collection Rate per Client";
	$title = "{$title_short} {$date_string}{$client_string}{$group_string}";

	$where = array("J.OBSOLETE=$sqlFalse", "J.JC_JOB=$sqlTrue");
	$date_field = 'P.COL_DT_RX';
	if ($sc_date_fr)
		$where[] = "'" . date_for_sql_nqnt($sc_date_fr) . "' <= $date_field";
	if ($sc_date_to)
		$where[] = "$date_field < '" . date_for_sql_nqnt($sc_date_to, true) . "'"; # NOT "<="
	if ($sc_client)
	{
		$where[] = "C.CLIENT2_ID > 0";
		if ($sc_c_code > 0)
			$where[] = "C.C_CODE = $sc_c_code";
		else
		{
			$sc_client2_id = (($sc_client[0] == '*') ? intval(trim(str_replace('*', '', $sc_client))) : 0);
			if ($sc_client2_id > 0)
				$where[] = "C.CLIENT2_ID = $sc_client2_id";
			else
			{
				sql_encryption_preparation('CLIENT2');
				$sc_client = addslashes_kdb($sc_client);
				$sql = "SELECT CLIENT2_ID FROM CLIENT2 WHERE " . sql_decrypt('C_CO_NAME') . " LIKE '%{$sc_client}%' OR " .
							"(ALPHA_CODE='" . addslashes_kdb($sc_client) . "')";
				#dprint($sql);#
				sql_execute($sql);
				$cids = array();
				while (($newArray = sql_fetch()) != false)
					$cids[] = $newArray[0];
				if ($cids)
					$where[] = "C.CLIENT2_ID IN (" . implode(',', $cids) . ")";
				else
					dprint("sc_client \"$sc_client\" yielded no client_id(s)");
			}
		}
	}
	if ($group_id > 0)
	{
		$where[] = "G.CLIENT_GROUP_ID > 0";
		$where[] = "G.CLIENT_GROUP_ID=$group_id";
		$group_select = ", G.CLIENT_GROUP_ID";
		$group_join = " LEFT JOIN CLIENT_GROUP G ON G.CLIENT_GROUP_ID=C.CLIENT_GROUP_ID ";
	}
	else
	{
		$group_select = '';
		$group_join = '';
	}
	$where[] = "P.OBSOLETE=$sqlFalse";
	if ($where)
		$where = "WHERE (" . implode(') AND (', $where) . ")";
	else
		$where = '';

	$lines = array();
	$total_amount = 0.0;
	$to_us_amount = 0.0;
	$forward_amount = 0.0;
	$direct_amount = 0.0;
	sql_encryption_preparation('CLIENT2');
	$sql = "
		SELECT C.CLIENT2_ID, C.C_CODE, " . sql_decrypt('C.C_CO_NAME', '', true) . ",
			P.COL_PAYMENT_ROUTE_ID $group_select, COUNT(*) AS NUM_PAYMENTS, SUM(COALESCE(P.COL_AMT_RX,0.0)) AS SUM_PAYMENTS
		FROM JOB_PAYMENT AS P
		LEFT JOIN JOB AS J ON J.JOB_ID=P.JOB_ID
		LEFT JOIN CLIENT2 AS C ON C.CLIENT2_ID=J.CLIENT2_ID
		$group_join
		$where
		GROUP BY C.CLIENT2_ID, C.C_CODE, " . sql_decrypt('C.C_CO_NAME') . ", P.COL_PAYMENT_ROUTE_ID $group_select
		ORDER BY C.C_CODE, P.COL_PAYMENT_ROUTE_ID
		";
	if ($dest == 's') dprint($sql);#
	sql_execute($sql);
	while (($newArray = sql_fetch_assoc()) != false)
	{
		$amount = floatval($newArray['SUM_PAYMENTS']);
		$total_amount += $amount;
		if ($newArray['COL_PAYMENT_ROUTE_ID'] == $id_ROUTE_tous)
			$to_us_amount += $amount;
		elseif ($newArray['COL_PAYMENT_ROUTE_ID'] == $id_ROUTE_fwd)
			$forward_amount += $amount;
		elseif ($newArray['COL_PAYMENT_ROUTE_ID'] == $id_ROUTE_direct)
			$direct_amount += $amount;

		$lines[] = $newArray;
	}
	#dprint("lines=" . print_r($lines,1));#

	$datalines = array();
	foreach ($lines as $line)
	{
		if (!array_key_exists($line['CLIENT2_ID'], $datalines))
			$datalines[$line['CLIENT2_ID']] =
				array('C_CODE' => $line['C_CODE'], 'C_CO_NAME' => $line['C_CO_NAME'], 'AMOUNT' => 0.0);
		$datalines[$line['CLIENT2_ID']]['AMOUNT'] = $datalines[$line['CLIENT2_ID']]['AMOUNT'] + floatval($line['SUM_PAYMENTS']);
	}
	#dprint("datalines=" . print_r($datalines,1));#

	$headings = array('Client Code', 'Client Name', 'Total Amount Collected');

	$summary_s = "Total amount collected: " . money_format_kdb($total_amount, true, true, true) . ". " .
					"To us: " . money_format_kdb($to_us_amount, true, true, true) . ". " .
					"Forwarded: " . money_format_kdb($forward_amount, true, true, true) . ". " .
					"Direct: " . money_format_kdb($direct_amount, true, true, true) . ".";
	$summary_x = "Total amount collected: " . money_format_kdb($total_amount, true, false, true) . ". " .
					"To us: " . money_format_kdb($to_us_amount, true, false, true) . ". " .
					"Forwarded: " . money_format_kdb($forward_amount, true, false, true) . ". " .
					"Direct: " . money_format_kdb($direct_amount, true, false, true) . ".";

	# Line 1: $title
	# Line 2: $summary
	# Line 3: $headings
	# Lines 4+: $datalines

	if ($dest == 's')
	{
		$screen = "
		<p style=\"font-size:18px; font-weight:bold;\">
			$title
			" .
//			"
//			&nbsp;&nbsp;&nbsp;&nbsp;
//			" . input_button('Export to CSV', "export_csv()") . "
//			" .
			"
			&nbsp;&nbsp;&nbsp;&nbsp;
			" . input_button('Export to Excel', "export_xl()", '', 'but_export_xl') . "
		</p>
		<p>$summary_s</p>

		<table class=\"spaced_table\">
		<tr>
			";
			foreach ($headings as $head)
				$screen .= "<th>$head</th>";
			if (user_debug())
				$screen .= "<th $grey>Group ID</th>";
			$screen .= "
		</tr>
		";
		foreach ($datalines as $dline)
		{
			$amount = money_format_kdb($dline['AMOUNT'], true, true, true);
			$group = (array_key_exists('CLIENT_GROUP_ID', $dline) ? $dline['CLIENT_GROUP_ID'] : '');
			$screen .= "
			<tr>
			<td $ar>{$dline['C_CODE']}</td><td>{$dline['C_CO_NAME']}</td><td $ar>$amount</td>
			" . (user_debug() ? "<td $grey>$group</td>" : '') . "
			</tr>
			";
		}
		$screen .= "
		</table>
		";
		printD($screen);
	}
	elseif ($dest == 'x')
	{
		$top_lines = array(array($title), array($summary_x), array());
		$formats = array('C' => $excel_currency_format);
		$sheet = array($title_short, $top_lines, $headings, $datalines, $formats, '');
		phpExcel_output($reps_subdir, $xfile, array($sheet)); # library.php

		# Auto-download file to PC
		printD("
		<script type=\"text/javascript\">
			document.form_csv_download.short_fname.value = '$xfile.$phpExcel_ext';
			document.form_csv_download.full_fname.value = '$csv_path' + '{$reps_subdir}/$xfile.$phpExcel_ext';
			document.form_csv_download.submit();
		</script>
		");
	}
	else
		dlog("$this_function: *=* bad dest \"$dest\"");

} # rep_cs_col_rate_client()

function rep_cs_jobs_client($dest, $sc_date_fr, $sc_date_to, $sc_sales, $sc_client, $sc_group, $xfile='')
{
	# Collections Report: Jobs per client in month.
	# $dest is 's' for screen or 'x' for export to Excel file.
	# $sc_date_fr and $sc_date_to are search criteria that form a date range which
	#		job placement dates must fall within; either may be blank.
	# $sc_sales is search criteria: salesperson ID
	# $sc_client is search criteria: a client code or part of a client name (or an alpha code)
	# $sc_group is search criteria: a client group ID
	# $xfile is the name of the file that the search results should be written to;
	#		it is used when $dest=='x'

	global $ar;
	global $csv_path;
//	global $excel_currency_format;
	global $grey;
	global $phpExcel_ext; # settings.php: "xls"
	global $salespersons;
	global $sqlFalse;
	global $sqlTrue;
	global $reps_subdir;

	$this_function = "rep_cs_jobs_client(\"$dest\", \"$sc_date_fr\", \"$sc_date_to\", \"$sc_sales\", \"$sc_client\", \"$sc_group\", \"$xfile\")";

	#dprint("Entered: $this_function");#

	if ($sc_date_fr)
	{
		if ($sc_date_to)
			$date_string = "between $sc_date_fr and $sc_date_to";
		else
			$date_string = "from $sc_date_fr";
	}
	elseif ($sc_date_to)
		$date_string = "up to $sc_date_to";
	else
		$date_string = "(all dates)";

	if ($sc_sales > 0)
		$sales_string = " for salesperson " . $salespersons[$sc_sales];
	else
		$sales_string = " for all salespersons";

	$sc_c_code = intval($sc_client);
	$client_string = '';
	if ($sc_client)
	{
		$client_name = '';
		if ($sc_c_code > 0)
		{
			$client_name = client_name_from_code($sc_c_code);
			if ($client_name)
				$client_name = "$sc_c_code - $client_name";
		}
		if (!$client_name)
			$client_name = $sc_client;
		$client_string .= ", client $client_name";
	}

	$group_id = intval($sc_group);
	if ($group_id > 0)
		$group_string = ", client group \"" . sql_client_group_name_from_id($group_id) . "\"";
	else
		$group_string = '';

	$title_short = "Jobs per client in month";
	$title = "{$title_short} {$date_string}{$sales_string}{$client_string}{$group_string}";

	$where = array("J.OBSOLETE=$sqlFalse", "J.JC_JOB=$sqlTrue");
	$date_field_raw = "J_OPENED_DT";
	$date_field = "J.$date_field_raw";
	if ($sc_date_fr)
		$where[] = "'" . date_for_sql_nqnt($sc_date_fr) . "' <= $date_field";
	if ($sc_date_to)
		$where[] = "$date_field < '" . date_for_sql_nqnt($sc_date_to, true) . "'"; # NOT "<="
	if ($sc_client)
	{
		$where[] = "C.CLIENT2_ID > 0";
		if ($sc_c_code > 0)
			$where[] = "C.C_CODE = $sc_c_code";
		else
		{
			$sc_client2_id = (($sc_client[0] == '*') ? intval(trim(str_replace('*', '', $sc_client))) : 0);
			if ($sc_client2_id > 0)
				$where[] = "C.CLIENT2_ID = $sc_client2_id";
			else
			{
				sql_encryption_preparation('CLIENT2');
				$sc_client = addslashes_kdb($sc_client);
				$sql = "SELECT CLIENT2_ID FROM CLIENT2 WHERE " . sql_decrypt('C_CO_NAME') . " LIKE '%{$sc_client}%' OR " .
							"(ALPHA_CODE='" . addslashes_kdb($sc_client) . "')";
				#dprint($sql);#
				sql_execute($sql);
				$cids = array();
				while (($newArray = sql_fetch()) != false)
					$cids[] = $newArray[0];
				if ($cids)
					$where[] = "C.CLIENT2_ID IN (" . implode(',', $cids) . ")";
				else
					dprint("sc_client \"$sc_client\" yielded no client_id(s)");
			}
		}
	}
	if ($group_id > 0)
	{
		$where[] = "G.CLIENT_GROUP_ID > 0";
		$where[] = "G.CLIENT_GROUP_ID=$group_id";
		$group_select = ", G.CLIENT_GROUP_ID";
		$group_join = " LEFT JOIN CLIENT_GROUP G ON G.CLIENT_GROUP_ID=C.CLIENT_GROUP_ID ";
	}
	else
	{
		$group_select = '';
		$group_join = '';
	}
	if ($sc_sales > 0)
		$where[] = "C.SALESPERSON_ID = $sc_sales";
	if ($where)
		$where = "WHERE (" . implode(') AND (', $where) . ")";
	else
		$where = '';

	if ($sc_date_fr)
		$month_start = substr(date_for_sql_nqnt($sc_date_fr), 0, 7) . "-01"; # YYYY-MM-01
	else
		$month_start = "1980-01-01";
	if ($sc_date_to)
		$month_end = substr(date_for_sql_nqnt($sc_date_to), 0, 7) . "-01"; # YYYY-MM-01
	else
		$month_end = substr(date_now_sql(true), 0, 7) . "-01"; # YYYY-MM-01
	$months = months_range($month_start, $month_end, false); # library.php; all months have day="01"
	#dprint("Months range = \"$month_start\" .. \"$month_end\" = " . print_r($months,1));#
	# E.g.	Array ( [0] => 2010-01-01 [1] => 2010-02-01 [2] => 2010-03-01 )

//	$month_amounts = array();
//	foreach ($months as $month)
//		$month_amounts[$month] = 0;
//	#dprint("Month amounts = " . print_r($month_amounts,1));#
//	# E.g.	Array ( [2010-01-01] => 0 [2010-02-01] => 0 [2010-03-01] => 0 )

	$lines = array();
	sql_encryption_preparation('CLIENT2');
	$sql = "
		SELECT C.CLIENT2_ID, C.C_CODE, " . sql_decrypt('C.C_CO_NAME', '', true) . ",
			J.JOB_ID, $date_field, J.CLIENT2_ID $group_select
		FROM JOB AS J
		LEFT JOIN CLIENT2 AS C ON C.CLIENT2_ID=J.CLIENT2_ID
		$group_join
		$where
		ORDER BY C.C_CODE
		";
	if ($dest == 's') dprint($sql);#
	sql_execute($sql);
	while (($newArray = sql_fetch_assoc()) != false)
	{
		$lines[] = $newArray;
	}
	#dprint("lines=" . print_r($lines,1));#

	$datalines = array();
	$lastline = array('C_CODE' => '', 'C_CO_NAME' => 'TOTALS:'); # will get added to end of $datalines
	foreach ($months as $month)
		$lastline[$month] = 0;
	$lastline['TOTAL_C'] = 0;

	foreach ($lines as $line)
	{
		if (!array_key_exists($line['CLIENT2_ID'], $datalines))
		{
			$datalines[$line['CLIENT2_ID']] = array('C_CODE' => $line['C_CODE'], 'C_CO_NAME' => $line['C_CO_NAME']);
			foreach ($months as $month)
				$datalines[$line['CLIENT2_ID']][$month] = 0;
			$datalines[$line['CLIENT2_ID']]['TOTAL_C'] = 0;
		}
		$month = substr($line[$date_field_raw], 0, 7) . "-01"; # YYYY-MM-01
//		if (!array_key_exists($month, $datalines[$line['CLIENT2_ID']]['MONTHS']))
//		{
//			#$datalines[$line['CLIENT2_ID']]['MONTHS'][$month] = 0;
//			#$lastline['TOTAL_M'][$month] = 0;
//			dlog("*=* Month \"$month\" not found in month_amounts " . print_r($month_amounts,1));
//		}
		$datalines[$line['CLIENT2_ID']][$month] = $datalines[$line['CLIENT2_ID']][$month] + 1;
		$datalines[$line['CLIENT2_ID']]['TOTAL_C'] = $datalines[$line['CLIENT2_ID']]['TOTAL_C'] + 1;
		$lastline[$month] = $lastline[$month] + 1;
		$lastline['TOTAL_C'] = $lastline['TOTAL_C'] + 1;
	}
	$datalines[0] = $lastline;
	#dprint("datalines=" . print_r($datalines,1));#
	# E.g.   [1498] => Array (	[C_CODE] => 1602 [C_CO_NAME] => London Borough of Hackney
	#							[2010-01-01] => 19 [2010-02-01] => 9 [2010-03-01] => 13
	#							[TOTAL_C] => 41 )

	$headings = array('Client Code', 'Client Name');
	foreach ($months as $month)
		$headings[] = date_for_sql($month, true, false, true, false, false, true, true, true, false, false);
	$headings[] = "Total";
	#dprint("Headings=" . print_r($headings,1));#

	if ($dest == 's')
	{
		$screen = "
		<p style=\"font-size:18px; font-weight:bold;\">
			$title
			&nbsp;&nbsp;&nbsp;&nbsp;
			" . input_button('Export to Excel', "export_xl()", '', 'but_export_xl') . "
		</p>

		<table class=\"spaced_table\">
		<tr>
			";
			foreach ($headings as $head)
				$screen .= "<th>$head</th>";
			if (user_debug())
				$screen .= "<th $grey>Group ID</th>";
			$screen .= "
		</tr>
		";
		foreach ($datalines as $dline)
		{
			$group = (array_key_exists('CLIENT_GROUP_ID', $dline) ? $dline['CLIENT_GROUP_ID'] : '');
			$screen .= "
			<tr>
			<td $ar>{$dline['C_CODE']}</td><td>{$dline['C_CO_NAME']}</td>";
			foreach ($months as $month)
			{
				$count = intval($dline[$month]);
				if (!$count)
					$count = "&nbsp;";
				$screen .= "<td $ar>$count</td>";
			}
			$screen .= "<td $ar>" . intval($dline['TOTAL_C']) . "</td>";
			if (user_debug())
				$screen .= "<td $grey>$group</td>";
			$screen .= "
			</tr>
			";
		}
		$screen .= "
		</table>
		";
		printD($screen);
	}
	elseif ($dest == 'x')
	{
		$top_lines = array(array($title), array());
		$formats = array();
//		$colm = 'C'; # first Excel column that needs a currency format
//		foreach ($months as $month)
//		{
//			$formats[$colm] = $excel_currency_format;
//			$colm = chr(ord($colm)+1); # Won't work past 'Z'
//		}
		$sheet = array($title_short, $top_lines, $headings, $datalines, $formats, '');
		phpExcel_output($reps_subdir, $xfile, array($sheet)); # library.php

		# Auto-download file to PC
		printD("
		<script type=\"text/javascript\">
			document.form_csv_download.short_fname.value = '$xfile.$phpExcel_ext';
			document.form_csv_download.full_fname.value = '$csv_path' + '{$reps_subdir}/$xfile.$phpExcel_ext';
			document.form_csv_download.submit();
		</script>
		");
	}
	else
		dlog("$this_function: *=* bad dest \"$dest\"");

} # rep_cs_jobs_client()

function rep_cs_col_rate_agent($dest, $sc_date_fr, $sc_date_to, $xfile='') #$sc_client, $sc_group, $xfile='')
{
	# Collections Report: Summary of user collection rates.
	# $dest is 's' for screen or 'x' for export to Excel file.
	# $sc_date_fr and $sc_date_to are search criteria that form a date range which
	#		collection payment dates must fall within; either may be blank.
	# $sc_client is search criteria: a client code or part of a client name (or an alpha code)
	# $sc_group is search criteria: a client group ID
	# $xfile is the name of the file that the search results should be written to;
	#		it is used when $dest=='x'

	global $ar;
	global $csv_path;
	global $excel_currency_format;
	global $id_ROUTE_cspent;
	global $id_ROUTE_direct;
	global $id_ROUTE_fwd;
	global $id_ROUTE_tous;
	global $phpExcel_ext; # settings.php: "xls"
	global $sqlFalse;
	global $sqlTrue;
	global $reps_subdir;

	$this_function = "rep_cs_col_rate_agent(\"$dest\", \"$sc_date_fr\", \"$sc_date_to\", \"$xfile\")"; # \"$sc_client\", \"$sc_group\", \"$xfile\")";

	#dprint("Entered: $this_function");#

	if ($sc_date_fr)
	{
		if ($sc_date_to)
			$date_string = "between $sc_date_fr and $sc_date_to";
		else
			$date_string = "from $sc_date_fr";
	}
	elseif ($sc_date_to)
		$date_string = "up to $sc_date_to";
	else
		$date_string = "(all dates)";

	$title_short = "Summary of user collection rates";
	$title = "{$title_short} {$date_string}"; #{$client_string}{$group_string}";

	$where = array("J.OBSOLETE=$sqlFalse", "J.JC_JOB=$sqlTrue");
	$date_field_raw = "COL_DT_RX";
	$date_field = "P.$date_field_raw";
	if ($sc_date_fr)
		$where[] = "'" . date_for_sql_nqnt($sc_date_fr) . "' <= $date_field";
	if ($sc_date_to)
		$where[] = "$date_field < '" . date_for_sql_nqnt($sc_date_to, true) . "'"; # NOT "<="
	$where[] = "P.OBSOLETE=$sqlFalse";
	if ($where)
		$where = "WHERE (" . implode(') AND (', $where) . ")";
	else
		$where = '';

	$lines = array();
	$total_amount = 0.0;
	$to_us_amount = 0.0;
	$forward_amount = 0.0;
	$direct_amount = 0.0;
	$adjust_amount = 0.0;
	#$other_amount = 0.0;
	sql_encryption_preparation('USERV');
	$sql = "
		SELECT U.USER_ID, U.U_FIRSTNAME, " . sql_decrypt('U.U_LASTNAME', '', true) . ", " . sql_decrypt('U.USERNAME', '', true) . ",
			P.COL_PAYMENT_ROUTE_ID, COUNT(*) AS NUM_PAYMENTS, SUM(COALESCE(P.COL_AMT_RX,0.0)) AS SUM_PAYMENTS
		FROM JOB_PAYMENT AS P
		LEFT JOIN JOB AS J ON J.JOB_ID=P.JOB_ID
		LEFT JOIN USERV AS U ON U.USER_ID=J.J_USER_ID
		LEFT JOIN CLIENT2 AS C ON C.CLIENT2_ID=J.CLIENT2_ID
		$where
		GROUP BY U.USER_ID, U.U_FIRSTNAME, " . sql_decrypt('U.U_LASTNAME') . ", " . sql_decrypt('U.USERNAME') . ", P.COL_PAYMENT_ROUTE_ID
		ORDER BY U.U_FIRSTNAME, " . sql_decrypt('U.U_LASTNAME') . ", P.COL_PAYMENT_ROUTE_ID
		";
	if ($dest == 's') dprint($sql);#
	sql_execute($sql);
	while (($newArray = sql_fetch_assoc()) != false)
	{
		$newArray['TO_US'] = 0.0;
		$newArray['FWD'] = 0.0;
		$newArray['DIRECT'] = 0.0;
		$newArray['ADJUST'] = 0.0;
		#$newArray['OTHER'] = 0.0;
		#$newArray['O_ROUTES'] = array();

		$amount = floatval($newArray['SUM_PAYMENTS']);
		$total_amount += $amount;
		if ($newArray['COL_PAYMENT_ROUTE_ID'] == $id_ROUTE_tous)
		{
			$to_us_amount += $amount;
			$newArray['TO_US'] = $amount;
		}
		elseif ($newArray['COL_PAYMENT_ROUTE_ID'] == $id_ROUTE_fwd)
		{
			$forward_amount += $amount;
			$newArray['FWD'] = $amount;
		}
		elseif ($newArray['COL_PAYMENT_ROUTE_ID'] == $id_ROUTE_direct)
		{
			$direct_amount += $amount;
			$newArray['DIRECT'] = $amount;
		}
		elseif ($newArray['COL_PAYMENT_ROUTE_ID'] == $id_ROUTE_cspent)
		{
			$adjust_amount += $amount;
			$newArray['ADJUST'] = $amount;
		}
		else
		{
			#$other_amount += $amount;
			#$newArray['OTHER'] = $amount;
			#if (!in_array($newArray['COL_PAYMENT_ROUTE_ID'], $newArray['O_ROUTES']))
			#	$newArray['O_ROUTES'][] = $newArray['COL_PAYMENT_ROUTE_ID'];
		}

		$lines[] = $newArray;
	}
	#dprint("lines=" . print_r($lines,1));#

	$datalines = array();
	foreach ($lines as $line)
	{
		if (!array_key_exists($line['USER_ID'], $datalines))
		{
			$name = trim("{$line['U_FIRSTNAME']} {$line['U_LASTNAME']}");
			if (trim(str_replace(' ', '', str_replace('-', '', $name))) == '')
				$name = "(" . str_replace('-T', '', str_replace('-C', '', $line['USERNAME'])) . ")";
			$datalines[$line['USER_ID']] = array('AGENT_NAME' => $name, 'TOTAL' => 0.0,
										'TO_US' => 0.0, 'FWD' => 0.0, 'DIRECT' => 0.0, 'ADJUST' => 0.0);#, 'OTHER' => 0.0, 'O_ROUTES' => array());
		}
		$datalines[$line['USER_ID']]['TOTAL'] = $datalines[$line['USER_ID']]['TOTAL'] + floatval($line['SUM_PAYMENTS']);
		$datalines[$line['USER_ID']]['TO_US'] = $datalines[$line['USER_ID']]['TO_US'] + floatval($line['TO_US']);
		$datalines[$line['USER_ID']]['FWD'] = $datalines[$line['USER_ID']]['FWD'] + floatval($line['FWD']);
		$datalines[$line['USER_ID']]['DIRECT'] = $datalines[$line['USER_ID']]['DIRECT'] + floatval($line['DIRECT']);
		$datalines[$line['USER_ID']]['ADJUST'] = $datalines[$line['USER_ID']]['ADJUST'] + floatval($line['ADJUST']);
		#$datalines[$line['USER_ID']]['OTHER'] = $datalines[$line['USER_ID']]['OTHER'] + (1.0 * $line['OTHER']);
		#$datalines[$line['USER_ID']]['O_ROUTES'] = array_merge($datalines[$line['USER_ID']]['O_ROUTES'], $line['O_ROUTES']);
	}
	#dprint("datalines=" . print_r($datalines,1));#

	$headings = array('Agent Name', 'Total Amount Collected', 'To us', 'Forwarded', 'Direct', 'Adjustments');#, 'Other', 'O.Routes');

	$summary_s = "Total amount collected: " . money_format_kdb($total_amount, true, true, true) . ". " .
					"To us: " . money_format_kdb($to_us_amount, true, true, true) . ". " .
					"Forwarded: " . money_format_kdb($forward_amount, true, true, true) . ". " .
					"Direct: " . money_format_kdb($direct_amount, true, true, true) . ". " .
					"Adjustments: " . money_format_kdb($adjust_amount, true, true, true) . ". ";# .
					#"Other: " . money_format_kdb($other_amount, true, true, true) . ".";
	$summary_x = "Total amount collected: " . money_format_kdb($total_amount, true, false, true) . ". " .
					"To us: " . money_format_kdb($to_us_amount, true, false, true) . ". " .
					"Forwarded: " . money_format_kdb($forward_amount, true, false, true) . ". " .
					"Direct: " . money_format_kdb($direct_amount, true, false, true) . ". " .
					"Adjustments: " . money_format_kdb($adjust_amount, true, false, true) . ". ";# .
					#"Other: " . money_format_kdb($other_amount, true, false, true) . ".";

	if ($dest == 's')
	{
		$screen = "
		<p style=\"font-size:18px; font-weight:bold;\">
			$title
			&nbsp;&nbsp;&nbsp;&nbsp;
			" . input_button('Export to Excel', "export_xl()", '', 'but_export_xl') . "
		</p>

		<p>$summary_s</p>

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
				<td $ar>{$dline['AGENT_NAME']}</td>
				<td $ar>" . money_format_kdb($dline['TOTAL'], true, true, true) . "</td>
				<td $ar>" . money_format_kdb($dline['TO_US'], true, true, true) . "</td>
				<td $ar>" . money_format_kdb($dline['FWD'], true, true, true) . "</td>
				<td $ar>" . money_format_kdb($dline['DIRECT'], true, true, true) . "</td>
				<td $ar>" . money_format_kdb($dline['ADJUST'], true, true, true) . "</td>
				" .
				#<td $ar>" . money_format_kdb($dline['OTHER'], true, true, true) . "</td>
				#<td>" . implode(',', $dline['O_ROUTES']) . "</td>
				"
			</tr>
			";
		}
		$screen .= "
		</table>
		";
		printD($screen);
	}
	elseif ($dest == 'x')
	{
		$top_lines = array(array($title), array(), array($summary_x), array());
		$formats = array('B' => $excel_currency_format, 'C' => $excel_currency_format,
							'D' => $excel_currency_format, 'E' => $excel_currency_format, 'F' => $excel_currency_format);
		$sheet = array($title_short, $top_lines, $headings, $datalines, $formats, '');
		phpExcel_output($reps_subdir, $xfile, array($sheet)); # library.php

		# Auto-download file to PC
		printD("
		<script type=\"text/javascript\">
			document.form_csv_download.short_fname.value = '$xfile.$phpExcel_ext';
			document.form_csv_download.full_fname.value = '$csv_path' + '{$reps_subdir}/$xfile.$phpExcel_ext';
			document.form_csv_download.submit();
		</script>
		");
	}
	else
		dlog("$this_function: *=* bad dest \"$dest\"");

} # rep_cs_col_rate_agent()

function rep_cs_col_sing_agent($dest, $sc_date_fr, $sc_date_to, $sc_agent, $sc_client, $sc_group, $xfile='')
{
	# Collections Report: Collections by single user.
	# $dest is 's' for screen or 'x' for export to Excel file.
	# $sc_date_fr and $sc_date_to are search criteria that form a date range which
	#		collection payment dates must fall within; either may be blank.
	# $sc_agent is ID of agent working on job(s).
	# $sc_client is search criteria: a client code or part of a client name (or an alpha code)
	# $sc_group is search criteria: a client group ID
	# $xfile is the name of the file that the search results should be written to;
	#		it is used when $dest=='x'

	global $ar;
	global $csv_path;
	global $excel_currency_format;
	global $grey;
	global $id_ROUTE_direct;
	global $id_ROUTE_fwd;
	global $id_ROUTE_tous;
	global $phpExcel_ext; # settings.php: "xls"
	global $sqlFalse;
	global $sqlTrue;
	global $reps_subdir;

	$this_function = "rep_cs_col_sing_agent(\"$dest\", \"$sc_date_fr\", \"$sc_date_to\", \"$sc_agent\", \"$sc_client\", \"$sc_group\", \"$xfile\")";
	$user_id = intval($sc_agent);
	if ( ! (0 < $user_id) )
	{
		dprint("Agent not specified", true);
		return;
	}

	#dprint("Entered: $this_function");#

	if ($sc_date_fr)
	{
		if ($sc_date_to)
			$date_string = "between $sc_date_fr and $sc_date_to";
		else
			$date_string = "from $sc_date_fr";
	}
	elseif ($sc_date_to)
		$date_string = "up to $sc_date_to";
	else
		$date_string = "(all dates)";

	$sc_c_code = intval($sc_client);
	$client_string = '';
	if ($sc_client)
	{
		$client_name = '';
		if ($sc_c_code > 0)
		{
			$client_name = client_name_from_code($sc_c_code);
			if ($client_name)
				$client_name = "$sc_c_code - $client_name";
		}
		if (!$client_name)
			$client_name = $sc_client;
		$client_string .= ", client $client_name";
	}

	$group_id = intval($sc_group);
	if ($group_id > 0)
		$group_string = ", client group \"" . sql_client_group_name_from_id($group_id) . "\"";
	else
		$group_string = '';

	if ($user_id > 0)
		$user_string = ", User " . user_name_from_id($user_id, true);
	else
		$user_string = '';

	$title_short = "Collections by single user";
	$title = "{$title_short} {$date_string}{$user_string}{$client_string}{$group_string}";

	$where = array("J.OBSOLETE=$sqlFalse", "J.JC_JOB=$sqlTrue");
	$date_field = 'P.COL_DT_RX';
	if ($sc_date_fr)
		$where[] = "'" . date_for_sql_nqnt($sc_date_fr) . "' <= $date_field";
	if ($sc_date_to)
		$where[] = "$date_field < '" . date_for_sql_nqnt($sc_date_to, true) . "'"; # NOT "<="
	if ($sc_client)
	{
		$where[] = "C.CLIENT2_ID > 0";
		if ($sc_c_code > 0)
			$where[] = "C.C_CODE = $sc_c_code";
		else
		{
			$sc_client2_id = (($sc_client[0] == '*') ? intval(trim(str_replace('*', '', $sc_client))) : 0);
			if ($sc_client2_id > 0)
				$where[] = "C.CLIENT2_ID = $sc_client2_id";
			else
			{
				sql_encryption_preparation('CLIENT2');
				$sc_client = addslashes_kdb($sc_client);
				$sql = "SELECT CLIENT2_ID FROM CLIENT2 WHERE " . sql_decrypt('C_CO_NAME') . " LIKE '%{$sc_client}%' OR " .
							"(ALPHA_CODE='" . addslashes_kdb($sc_client) . "')";
				#dprint($sql);#
				sql_execute($sql);
				$cids = array();
				while (($newArray = sql_fetch()) != false)
					$cids[] = $newArray[0];
				if ($cids)
					$where[] = "C.CLIENT2_ID IN (" . implode(',', $cids) . ")";
				else
					dprint("sc_client \"$sc_client\" yielded no client_id(s)");
			}
		}
	}
	if ($group_id > 0)
	{
		$where[] = "G.CLIENT_GROUP_ID > 0";
		$where[] = "G.CLIENT_GROUP_ID=$group_id";
		$group_select = ", G.CLIENT_GROUP_ID";
		$group_join = " LEFT JOIN CLIENT_GROUP G ON G.CLIENT_GROUP_ID=C.CLIENT_GROUP_ID ";
	}
	else
	{
		$group_select = '';
		$group_join = '';
	}
	if ($user_id > 0)
		$where[] = "J.J_USER_ID=$user_id";
	$where[] = "P.OBSOLETE=$sqlFalse";
	if ($where)
		$where = "WHERE (" . implode(') AND (', $where) . ")";
	else
		$where = '';

	$lines = array();
	$total_amount = 0.0;
	$to_us_amount = 0.0;
	$forward_amount = 0.0;
	$direct_amount = 0.0;
	$total_commission = 0.0;
	sql_encryption_preparation('CLIENT2');
	$sql = "
		SELECT C.CLIENT2_ID, C.C_CODE, " . sql_decrypt('C.C_CO_NAME', '', true) . ",
			P.COL_PAYMENT_ROUTE_ID $group_select, COUNT(*) AS NUM_PAYMENTS, SUM(COALESCE(P.COL_AMT_RX,0.0)) AS SUM_PAYMENTS,
			SUM(0.01 * COALESCE(P.COL_PERCENT,0.0) * COALESCE(P.COL_AMT_RX,0.0)) AS SUM_COMMISSION
		FROM JOB_PAYMENT AS P
		LEFT JOIN JOB AS J ON J.JOB_ID=P.JOB_ID
		LEFT JOIN CLIENT2 AS C ON C.CLIENT2_ID=J.CLIENT2_ID
		$group_join
		$where
		GROUP BY C.CLIENT2_ID, C.C_CODE, " . sql_decrypt('C.C_CO_NAME', '', false) . ", P.COL_PAYMENT_ROUTE_ID $group_select
		ORDER BY C.C_CODE, P.COL_PAYMENT_ROUTE_ID
		";
	if ($dest == 's') dprint($sql);#
	#dprint("Calling SQL");#
	sql_execute($sql);
	while (($newArray = sql_fetch_assoc()) != false)
	{
		#dprint("Found one line: " . print_r($newArray,1));#
		$amount = floatval($newArray['SUM_PAYMENTS']);
		$total_amount += $amount;
		if ($newArray['COL_PAYMENT_ROUTE_ID'] == $id_ROUTE_tous)
			$to_us_amount += $amount;
		elseif ($newArray['COL_PAYMENT_ROUTE_ID'] == $id_ROUTE_fwd)
			$forward_amount += $amount;
		elseif ($newArray['COL_PAYMENT_ROUTE_ID'] == $id_ROUTE_direct)
			$direct_amount += $amount;
		$total_commission += floatval($newArray['SUM_COMMISSION']);

		$lines[] = $newArray;
	}
	dprint("Found " . count($lines) . " lines");#
	#dprint("lines=" . print_r($lines,1));#

	$datalines = array();
	foreach ($lines as $line)
	{
		if (!array_key_exists($line['CLIENT2_ID'], $datalines))
			$datalines[$line['CLIENT2_ID']] =
				array('C_CODE' => $line['C_CODE'], 'C_CO_NAME' => $line['C_CO_NAME'], 'AMOUNT' => 0.0, 'COMMISSION' => 0.0);
		$datalines[$line['CLIENT2_ID']]['AMOUNT'] = $datalines[$line['CLIENT2_ID']]['AMOUNT'] + floatval($line['SUM_PAYMENTS']);
		$datalines[$line['CLIENT2_ID']]['COMMISSION'] = $datalines[$line['CLIENT2_ID']]['COMMISSION'] + floatval($line['SUM_COMMISSION']);
	}
	#dprint("datalines=" . print_r($datalines,1));#

	$headings = array('Client Code', 'Client Name', 'Total Amount Collected', 'Total Commission');

	$summary_s = "Total amount collected: " . money_format_kdb($total_amount, true, true, true) . ". " .
					"To us: " . money_format_kdb($to_us_amount, true, true, true) . ". " .
					"Forwarded: " . money_format_kdb($forward_amount, true, true, true) . ". " .
					"Direct: " . money_format_kdb($direct_amount, true, true, true) . ".";
	$summary_x = "Total amount collected: " . money_format_kdb($total_amount, true, false, true) . ". " .
					"To us: " . money_format_kdb($to_us_amount, true, false, true) . ". " .
					"Forwarded: " . money_format_kdb($forward_amount, true, false, true) . ". " .
					"Direct: " . money_format_kdb($direct_amount, true, false, true) . ".";

	$summary_s2 = "Total commission: " . money_format_kdb($total_commission, true, true, true) . ".";
	$summary_x2 = "Total commission: " . money_format_kdb($total_commission, true, false, true) . ".";

	# Line 1: $title
	# Line 2: $summary
	# Line 3: $headings
	# Lines 4+: $datalines

	if ($dest == 's')
	{
		$screen = "
		<p style=\"font-size:18px; font-weight:bold;\">
			$title
			&nbsp;&nbsp;&nbsp;&nbsp;
			" . input_button('Export to Excel', "export_xl()", '', 'but_export_xl') . "
		</p>
		<p>$summary_s</p>
		<p>$summary_s2</p>

		<table class=\"spaced_table\">
		<tr>
			";
			foreach ($headings as $head)
				$screen .= "<th>$head</th>";
			if (user_debug())
				$screen .= "<th $grey>Group ID</th>";
			$screen .= "
		</tr>
		";
		foreach ($datalines as $dline)
		{
			$amount = money_format_kdb($dline['AMOUNT'], true, true, true);
			$commission = money_format_kdb($dline['COMMISSION'], true, true, true);
			$group = (array_key_exists('CLIENT_GROUP_ID', $dline) ? $dline['CLIENT_GROUP_ID'] : '');
			$screen .= "
			<tr>
			<td $ar>{$dline['C_CODE']}</td><td>{$dline['C_CO_NAME']}</td><td $ar>$amount</td><td $ar>$commission</td>
			" . (user_debug() ? "<td $grey>$group</td>" : '') . "
			</tr>
			";
		}
		$screen .= "
		</table>
		";
		printD($screen);
	}
	elseif ($dest == 'x')
	{
		$top_lines = array(array($title), array($summary_x), array($summary_x2), array());
		$formats = array('C' => $excel_currency_format, 'D' => $excel_currency_format);
		$sheet = array($title_short, $top_lines, $headings, $datalines, $formats, '');
		phpExcel_output($reps_subdir, $xfile, array($sheet)); # library.php

		# Auto-download file to PC
		printD("
		<script type=\"text/javascript\">
			document.form_csv_download.short_fname.value = '$xfile.$phpExcel_ext';
			document.form_csv_download.full_fname.value = '$csv_path' + '{$reps_subdir}/$xfile.$phpExcel_ext';
			document.form_csv_download.submit();
		</script>
		");
	}
	else
		dlog("$this_function: *=* bad dest \"$dest\"");

} # rep_cs_col_sing_agent()

function rep_cs_stat_client($dest, $sc_date_fr, $sc_date_to, $sc_client='', $sc_group=0, $xfile='')
{
	# Collections Report: Job Statistics per client.
	# $dest is 's' for screen or 'x' for export to Excel file.
	# $sc_date_fr and $sc_date_to are search criteria that form a date range which
	#		collection payment dates must fall within; either may be blank.
	# $sc_client is search criteria: a client code or part of a client name (or an alpha code)
	# $sc_group is search criteria: a client group ID
	# $xfile is the name of the file that the search results should be written to;
	#		it is used when $dest=='x'

	global $ar;
	global $csv_path;
	global $excel_currency_format; # settings.php
	global $excel_integer_format; # settings.php
	#global $grey;
	global $id_JOB_STATUS_rcr; # JOB_STATUS_SD.JOB_STATUS_ID for "RCR"
	global $phpExcel_ext; # settings.php: "xls"
	global $sqlFalse;
	global $sqlTrue;
	global $reps_subdir;

	$this_function = "rep_cs_stat_client(\"$dest\", \"$sc_date_fr\", \"$sc_date_to\", \"$sc_client\", \"$sc_group\", \"$xfile\")";
	$debug = false;#
	if ($debug) dprint("Entered: $this_function");#

	if ($sc_date_fr)
	{
		if ($sc_date_to)
			$date_string = "between $sc_date_fr and $sc_date_to";
		else
			$date_string = "from $sc_date_fr";
	}
	elseif ($sc_date_to)
		$date_string = "up to $sc_date_to";
	else
		$date_string = "(all dates)";

	$sc_c_code = intval($sc_client);
	$client_string = '';
	if ($sc_client)
	{
		$client_name = '';
		if ($sc_c_code > 0)
		{
			$client_name = client_name_from_code($sc_c_code);
			if ($client_name)
				$client_name = "$sc_c_code - $client_name";
		}
		if (!$client_name)
			$client_name = $sc_client;
		$client_string .= ", client $client_name";
	}

	$group_id = intval($sc_group);
	if ($group_id > 0)
		$group_string = ", client group \"" . sql_client_group_name_from_id($group_id) . "\"";
	else
		$group_string = '';

	$title_short = "Job Statistics per client";
	$title = "{$title_short} {$date_string}{$client_string}{$group_string}";

	$where = array("J.OBSOLETE=$sqlFalse", "J.JC_JOB=$sqlTrue");
	$date_field_raw = "J_OPENED_DT";
	$date_field = "J.$date_field_raw";
	if ($sc_date_fr)
		$where[] = "'" . date_for_sql_nqnt($sc_date_fr) . "' <= $date_field";
	if ($sc_date_to)
		$where[] = "$date_field < '" . date_for_sql_nqnt($sc_date_to, true) . "'"; # NOT "<="
	if ($sc_client)
	{
		$where[] = "C.CLIENT2_ID > 0";
		if ($sc_c_code > 0)
			$where[] = "C.C_CODE = $sc_c_code";
		else
		{
			$sc_client2_id = (($sc_client[0] == '*') ? intval(trim(str_replace('*', '', $sc_client))) : 0);
			if ($sc_client2_id > 0)
				$where[] = "C.CLIENT2_ID = $sc_client2_id";
			else
			{
				sql_encryption_preparation('CLIENT2');
				$sc_client = addslashes_kdb($sc_client);
				$sql = "SELECT CLIENT2_ID FROM CLIENT2 WHERE " . sql_decrypt('C_CO_NAME') . " LIKE '%{$sc_client}%' OR " .
							"(ALPHA_CODE='" . addslashes_kdb($sc_client) . "')";
				#dprint($sql);#
				sql_execute($sql);
				$cids = array();
				while (($newArray = sql_fetch()) != false)
					$cids[] = $newArray[0];
				if ($cids)
					$where[] = "C.CLIENT2_ID IN (" . implode(',', $cids) . ")";
				else
					dprint("sc_client \"$sc_client\" yielded no client_id(s)");
			}
		}
	}
	if ($group_id > 0)
	{
		$where[] = "G.CLIENT_GROUP_ID > 0";
		$where[] = "G.CLIENT_GROUP_ID=$group_id";
		$group_select = ", G.CLIENT_GROUP_ID";
		$group_join = " LEFT JOIN CLIENT_GROUP G ON G.CLIENT_GROUP_ID=C.CLIENT_GROUP_ID ";
	}
	else
	{
		$group_select = '';
		$group_join = '';
	}
	if ($where)
		$where = "WHERE (" . implode(') AND (', $where) . ")";
	else
		$where = '';

	#$lines = array();
	$clients = array();

	# The variables below are for all matching clients.
	# The per-client values are in $clients() - see initialisation of $clients[$client2_id] below.
	$total_jobs = 0; # JT: number of jobs for selected client(s) with placement date in date range
	$total_value = 0.0; # VT: total value of jobs: JC_TOTAL_AMT
	$jobs_closed_paid = 0; # JCP: number of jobs closed and paid in full
	$value_closed_paid = 0.0; # VCP: value of jobs closed and paid in full
	$jobs_closed_unpaid = 0; # JCU: number of jobs closed but not paid in full
	$value_closed_unpaid = 0.0; # VCU: value of jobs closed but not paid in full
	$jobs_closed_nopay = 0; # JCN: number of jobs closed with no payments
	$value_closed_nopay = 0.0; # VCN: value of jobs closed with no payments
	$jobs_open_unpaid = 0; # JOU: number of jobs open with some payments
	$value_open_unpaid = 0.0; # VOU: value of jobs open with some payments
	$jobs_open_nopay = 0; # JON: number of jobs open with no payments
	$value_open_nopay = 0.0; # VON: value of jobs open with no payments
	$jobs_rcr = 0; # JRCR: number of jobs with status RCR
	$value_rcr = 0.0; # VRCR: value of jobs with status RCR

	sql_encryption_preparation('CLIENT2');
	$sql = "
		SELECT C.CLIENT2_ID, J.JOB_ID, J.JC_TOTAL_AMT, J.J_COMPLETE, J.J_CLOSED_DT, J.JC_PAID_SO_FAR, J.JC_JOB_STATUS_ID
		FROM JOB AS J
		LEFT JOIN CLIENT2 AS C ON C.CLIENT2_ID=J.CLIENT2_ID
		$group_join
		$where
		ORDER BY C.C_CODE, J.JOB_ID
		";
	if ($dest == 's') dprint($sql);#
	sql_execute($sql);
	while (($newArray = sql_fetch_assoc()) != false)
	{
		$job_value = floatval($newArray['JC_TOTAL_AMT']);
		$paid = floatval($newArray['JC_PAID_SO_FAR']);

		$client2_id = intval($newArray['CLIENT2_ID']);
		if (!array_key_exists($client2_id, $clients))
			$clients[$client2_id] = array('C_CODE' => '', 'C_CO_NAME' => '', 'JT' => 0, 'VT' => 0.0,
											'JCP' => 0, 'VCP' => 0.0, 'JCU' => 0, 'VCU' => 0.0, 'JCN' => 0, 'VCN' => 0.0,
											'JOU' => 0, 'VOU' => 0.0, 'JON' => 0, 'VON' => 0.00, 'JRCR' => 0, 'VRCR' => 0.0);
		$total_jobs++;
		$clients[$client2_id]['JT']++;
		$total_value += $job_value;
		$clients[$client2_id]['VT'] += $job_value;

		if (($newArray['J_COMPLETE'] == 1) || (strlen($newArray['J_CLOSED_DT']) > 0))
		{
			# Job is closed
			if ($job_value <= $paid)
			{
				# Job is fully paid
				$jobs_closed_paid++;
				$clients[$client2_id]['JCP']++;
				$value_closed_paid += $job_value;
				$clients[$client2_id]['VCP'] += $job_value;
			}
			else
			{
				if ($paid <= 0.0)
				{
					# Job has no payments
					$jobs_closed_nopay++;
					$clients[$client2_id]['JCN']++;
					$value_closed_nopay += $job_value;
					$clients[$client2_id]['VCN'] += $job_value;
				}
				else
				{
					# Job not fully paid
					$jobs_closed_unpaid++;
					$clients[$client2_id]['JCU']++;
					$value_closed_unpaid += $job_value;
					$clients[$client2_id]['VCU'] += $job_value;
				}
			}
		}
		else
		{
			# Job is open
			if (0 <= $paid)
			{
				# Some payments
				$jobs_open_unpaid++;
				$clients[$client2_id]['JOU']++;
				$value_open_unpaid += $job_value;
				$clients[$client2_id]['VOU'] += $job_value;
			}
			else
			{
				# No payments
				$jobs_open_nopay++;
				$clients[$client2_id]['JON']++;
				$value_open_nopay += $job_value;
				$clients[$client2_id]['VON'] += $job_value;
			}
		}
		if ($newArray['JC_JOB_STATUS_ID'] == $id_JOB_STATUS_rcr)
		{
			$jobs_rcr++;
			$clients[$client2_id]['JRCR']++;
			$value_rcr += $job_value;
			$clients[$client2_id]['VRCR'] += $job_value;
		}
		#$lines[] = $newArray;
	}
	#dprint("\$total_jobs=$total_jobs, \$total_value=$total_value, \$jobs_closed_paid=$jobs_closed_paid, \$value_closed_paid=$value_closed_paid,<br>" .
	#		"\$jobs_closed_unpaid=$jobs_closed_unpaid, \$value_closed_unpaid=$value_closed_unpaid, \$jobs_closed_nopay=$jobs_closed_nopay, " .
	#		"\$value_closed_nopay=$value_closed_nopay");#
	#dprint("lines=" . print_r($lines,1));#

	$jobs_closed_paid_perc = $total_jobs ? (floatval($jobs_closed_paid) / $total_jobs) : 0;
	$value_closed_paid_perc = $total_value ? (floatval($value_closed_paid) / $total_value) : 0;
	$jobs_closed_unpaid_perc = $total_jobs ? (floatval($jobs_closed_unpaid) / $total_jobs) : 0;
	$value_closed_unpaid_perc = $total_value ? (floatval($value_closed_unpaid) / $total_value) : 0;
	$jobs_closed_nopay_perc = $total_jobs ? (floatval($jobs_closed_nopay) / $total_jobs) : 0;
	$value_closed_nopay_perc = $total_value ? (floatval($value_closed_nopay) / $total_value) : 0;
	$jobs_open_unpaid_perc = $total_jobs ? (floatval($jobs_open_unpaid) / $total_jobs) : 0;
	$value_open_unpaid_perc = $total_value ? (floatval($value_open_unpaid) / $total_value) : 0;
	$jobs_open_nopay_perc = $total_jobs ? (floatval($jobs_open_nopay) / $total_jobs) : 0;
	$value_open_nopay_perc = $total_value ? (floatval($value_open_nopay) / $total_value) : 0;
	$jobs_rcr_perc = $total_jobs ? (floatval($jobs_rcr) / $total_jobs) : 0;
	$value_rcr_perc = $total_value ? (floatval($value_rcr) / $total_value) : 0;

	foreach ($clients as $client2_id => $info)
	{
		$clients[$client2_id]['JCP_PC'] = floatval($clients[$client2_id]['JCP']) / $clients[$client2_id]['JT'];
		$clients[$client2_id]['VCP_PC'] = floatval($clients[$client2_id]['VCP']) / $clients[$client2_id]['VT'];
		$clients[$client2_id]['JCU_PC'] = floatval($clients[$client2_id]['JCU']) / $clients[$client2_id]['JT'];
		$clients[$client2_id]['VCU_PC'] = floatval($clients[$client2_id]['VCU']) / $clients[$client2_id]['VT'];
		$clients[$client2_id]['JCN_PC'] = floatval($clients[$client2_id]['JCN']) / $clients[$client2_id]['JT'];
		$clients[$client2_id]['VCN_PC'] = floatval($clients[$client2_id]['VCN']) / $clients[$client2_id]['VT'];
		$clients[$client2_id]['JOU_PC'] = floatval($clients[$client2_id]['JOU']) / $clients[$client2_id]['JT'];
		$clients[$client2_id]['VOU_PC'] = floatval($clients[$client2_id]['VOU']) / $clients[$client2_id]['VT'];
		$clients[$client2_id]['JON_PC'] = floatval($clients[$client2_id]['JON']) / $clients[$client2_id]['JT'];
		$clients[$client2_id]['VON_PC'] = floatval($clients[$client2_id]['VON']) / $clients[$client2_id]['VT'];
		$clients[$client2_id]['JRCR_PC'] = floatval($clients[$client2_id]['JRCR']) / $clients[$client2_id]['JT'];
		$clients[$client2_id]['VRCR_PC'] = floatval($clients[$client2_id]['VRCR']) / $clients[$client2_id]['VT'];

		$cname = client_name_from_id($client2_id);
		$clients[$client2_id]['C_CODE'] = $cname['C_CODE'];
		$clients[$client2_id]['C_CO_NAME'] = $cname['C_CO_NAME'];

		$info=$info; # suppress warnings
	}
	#dprint("clients=" . print_r($clients,1));#

//	if ($debug)
//	{
//		$clients = array();
//		foreach ($lines as $line)
//		{
//			if (!array_key_exists($line['CLIENT2_ID'], $clients))
//				$clients[$line['CLIENT2_ID']] = client_name_from_id($line['CLIENT2_ID']);
//		}
//		dprint("clients=" . print_r($clients,1));#
//	}


	$headings = array('Client', 'Client Name', '', 'Job Count', '%', 'Value', '%');

	if (count($clients) == 1)
	{
		foreach ($clients as $client)
		{
			$first_client_code = $client['C_CODE'];
			$first_client_name = $client['C_CO_NAME'];
		}
	}
	else
	{
		$first_client_code = '';
		$first_client_name = 'All matching clients';
	}
	$datalines = array(
		array($first_client_code, $first_client_name, "Collection jobs",
				$total_jobs, '', $total_value, ''),
		array('', '', "Jobs closed and paid in full",
				$jobs_closed_paid, $jobs_closed_paid_perc, $value_closed_paid, $value_closed_paid_perc),
		array('', '', "Jobs closed but not paid in full",
				$jobs_closed_unpaid, $jobs_closed_unpaid_perc, $value_closed_unpaid, $value_closed_unpaid_perc),
		array('', '', "Jobs closed with no payments",
				$jobs_closed_nopay, $jobs_closed_nopay_perc, $value_closed_nopay, $value_closed_nopay_perc),
		array('', '', "Jobs open with some payments",
				$jobs_open_unpaid, $jobs_open_unpaid_perc, $value_open_unpaid, $value_open_unpaid_perc),
		array('', '', "Jobs open with no payments",
				$jobs_open_nopay, $jobs_open_nopay_perc, $value_open_nopay, $value_open_nopay_perc),
		array('', '', "Number of T/C jobs with no trace",
				'n/a', '', '', ''),
		array('', '', "Number of RCR jobs",
				$jobs_rcr, $jobs_rcr_perc, $value_rcr, $value_rcr_perc),
		);

	if (1 < count($clients))
	{
		foreach ($clients as $client)
		{
			$datalines[] = array('', '', '', '', '', '', '');
			$datalines[] = array($client['C_CODE'], $client['C_CO_NAME'], "Collection jobs",
									$client['JT'], '', $client['VT'], '');
			$datalines[] = array('', '', "Jobs closed and paid in full",
									$client['JCP'], $client['JCP_PC'], $client['VCP'], $client['VCP_PC']);
			$datalines[] = array('', '', "Jobs closed but not paid in full",
									$client['JCU'], $client['JCU_PC'], $client['VCU'], $client['VCU_PC']);
			$datalines[] = array('', '', "Jobs closed with no payments",
									$client['JCN'], $client['JCN_PC'], $client['VCN'], $client['VCN_PC']);
			$datalines[] = array('', '', "Jobs open with some payments",
									$client['JOU'], $client['JOU_PC'], $client['VOU'], $client['VOU_PC']);
			$datalines[] = array('', '', "Jobs open with no payments",
									$client['JON'], $client['JON_PC'], $client['VON'], $client['VON_PC']);
			$datalines[] = array('', '', "Number of T/C jobs with no trace",
									'n/a', '', '', '');
			$datalines[] = array('', '', "Number of RCR jobs",
									$client['JRCR'], $client['JRCR_PC'], $client['VRCR'], $client['VRCR_PC']);
		}
	}

	#dprint("datalines=" . print_r($datalines,1));#
	#
	# Line 1: $title
	# Line 2: $summary
	# Line 3: $headings
	# Lines 4+: $datalines

	if ($dest == 's')
	{
		$screen = "
		<p style=\"font-size:18px; font-weight:bold;\">
			$title
			" .
//			"
//			&nbsp;&nbsp;&nbsp;&nbsp;
//			" . input_button('Export to CSV', "export_csv()") . "
//			" .
			"
			&nbsp;&nbsp;&nbsp;&nbsp;
			" . input_button('Export to Excel', "export_xl()", '', 'but_export_xl') . "
		</p>

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
				<td $ar>{$dline[0]}</td>
				<td>{$dline[1]}</td>
				<td>{$dline[2]}</td>
				<td $ar>{$dline[3]}</td>
				<td $ar>" . (($dline[4] == '') ? '&nbsp;' : (round(100.0 * floatval($dline[4]),1)) . "%") . "</td>
				<td $ar>" . (($dline[5] == '') ? '&nbsp;' : money_format_kdb($dline[5], true, true, true)) . "</td>
				<td $ar>" . (($dline[6] == '') ? '&nbsp;' : (round(100.0 * floatval($dline[6]),1)) . "%") . "</td>
			</tr>
			";
		}
		$screen .= "
		</table>
		";
		printD($screen);
	}
	elseif ($dest == 'x')
	{
		$top_lines = array(array($title), array());
		$formats = array('D' => $excel_integer_format, 'E' => "0.00%", 'F' => $excel_currency_format, 'G' => "0.00%");
		$sheet = array($title_short, $top_lines, $headings, $datalines, $formats, '');
		phpExcel_output($reps_subdir, $xfile, array($sheet)); # library.php

		# Auto-download file to PC
		printD("
		<script type=\"text/javascript\">
			document.form_csv_download.short_fname.value = '$xfile.$phpExcel_ext';
			document.form_csv_download.full_fname.value = '$csv_path' + '{$reps_subdir}/$xfile.$phpExcel_ext';
			document.form_csv_download.submit();
		</script>
		");
	}
	else
		dlog("$this_function: *=* bad dest \"$dest\"");

} # rep_cs_stat_client()

function rep_ts_num_jobs($dest, $sc_date_fr, $sc_date_to, $sc_client, $sc_group, $xfile='')
{
	# Traces/Statistics: Number of jobs to be processed
	# $dest is 's' for screen or 'x' for export to Excel file.
	# $sc_date_fr and $sc_date_to are search criteria that form a date range which
	#		job placement dates must fall within; either may be blank.
	# $sc_client is search criteria: a client code or part of a client name (or an alpha code)
	# $sc_group is search criteria: a client group ID
	# $xfile is the name of the file that the search results should be written to;
	#		it is used when $dest=='x'

	global $ar;
	global $csv_path;
	global $grey;
	global $ids_JOB_TYPE_retraces;
	global $phpExcel_ext; # settings.php: "xls"
	global $sqlFalse;
	global $sqlTrue;
	global $reps_subdir;

	$this_function = "rep_ts_num_jobs(\"$dest\", \"$sc_date_fr\", \"$sc_date_to\", \"$sc_client\", \"$sc_group\", \"$xfile\")";

	#dprint("Entered: $this_function");#

	$t_agents = sql_get_agents('t', true, false); # get list of trace agents, e.g. Array ( [13] => Alex Salamone [10] => Del Hudson ... )
	$ag_ids = array();
	$ag_counts = array();
	foreach ($t_agents as $tid => $tname)
	{
		$ag_ids[] = $tid;
		$ag_counts[$tid] = array('ALL' => 0, 'RETRACES' => 0);
	}
	$tname=$tname; # suppress warnings
	#dprint("\$t_agents=" . print_r($t_agents,1) . "<br>\$ag_ids=" . print_r($ag_ids,1));#

	if ($sc_date_fr)
	{
		if ($sc_date_to)
			$date_string = "between $sc_date_fr and $sc_date_to";
		else
			$date_string = "from $sc_date_fr";
	}
	elseif ($sc_date_to)
		$date_string = "up to $sc_date_to";
	else
		$date_string = "(all dates)";

	$sc_c_code = intval($sc_client);
	$client_string = '';
	if ($sc_client)
	{
		$client_name = '';
		if ($sc_c_code > 0)
		{
			$client_name = client_name_from_code($sc_c_code);
			if ($client_name)
				$client_name = "$sc_c_code - $client_name";
		}
		if (!$client_name)
			$client_name = $sc_client;
		$client_string .= ", client $client_name";
	}

	$group_id = intval($sc_group);
	if ($group_id > 0)
		$group_string = ", client group \"" . sql_client_group_name_from_id($group_id) . "\"";
	else
		$group_string = '';

	$title_short = "Number of jobs to be processed";
	$title = "{$title_short} {$date_string}{$client_string}{$group_string}";

	$where = array("J.OBSOLETE=$sqlFalse", "J.JT_JOB=$sqlTrue", "J.J_CLOSED_DT IS NULL");
	$where[] = ("J.J_USER_ID IN (" . implode(',', $ag_ids) . ")");
	#$w_client = false;

	$date_field = 'J.J_OPENED_DT'; # job placement date
	if ($sc_date_fr)
		$where[] = "'" . date_for_sql_nqnt($sc_date_fr) . "' <= $date_field";
	if ($sc_date_to)
		$where[] = "$date_field < '" . date_for_sql_nqnt($sc_date_to, true) . "'"; # NOT "<="
	if ($sc_client)
	{
		$where[] = "C.CLIENT2_ID > 0";
		if ($sc_c_code > 0)
		{
			$where[] = "C.C_CODE = $sc_c_code";
			#$w_client = true;
		}
		else
		{
			$sc_client2_id = (($sc_client[0] == '*') ? intval(trim(str_replace('*', '', $sc_client))) : 0);
			if ($sc_client2_id > 0)
			{
				$where[] = "C.CLIENT2_ID = $sc_client2_id";
				#$w_client = true;
			}
			else
			{
				sql_encryption_preparation('CLIENT2');
				$sc_client = addslashes_kdb($sc_client);
				$sql = "SELECT CLIENT2_ID FROM CLIENT2 WHERE " . sql_decrypt('C_CO_NAME') . " LIKE '%{$sc_client}%' OR " .
							"(ALPHA_CODE='" . addslashes_kdb($sc_client) . "')";
				#dprint($sql);#
				sql_execute($sql);
				$cids = array();
				while (($newArray = sql_fetch()) != false)
					$cids[] = $newArray[0];
				if ($cids)
				{
					$where[] = "C.CLIENT2_ID IN (" . implode(',', $cids) . ")";
					#$w_client = true;
				}
				else
					dprint("sc_client \"$sc_client\" yielded no client_id(s)");
			}
		}
	}
	if ($group_id > 0)
	{
		$where[] = "G.CLIENT_GROUP_ID > 0";
		$where[] = "G.CLIENT_GROUP_ID=$group_id";
		$group_select = ", G.CLIENT_GROUP_ID";
		$group_join = " LEFT JOIN CLIENT_GROUP G ON G.CLIENT_GROUP_ID=C.CLIENT_GROUP_ID ";
		#$w_client = true;
	}
	else
	{
		$group_select = '';
		$group_join = '';
	}
	#if ($w_client)
		$client_join = "LEFT JOIN CLIENT2 AS C ON C.CLIENT2_ID=J.CLIENT2_ID";
	#else
	#	$client_join = '';

	if ($where)
		$where = "WHERE (" . implode(') AND (', $where) . ")";
	else
		$where = '';

	sql_encryption_preparation('CLIENT2');
	$sql = "
		SELECT J.JT_JOB_TYPE_ID, J.J_USER_ID, C.C_CODE
		FROM JOB AS J
		$client_join
		$group_join
		$where
		";
	if ($dest == 's') dprint($sql);#
	sql_execute($sql);
	$count_all = 0;
	$count_retraces = 0;
	$clients = array();
	while (($newArray = sql_fetch_assoc()) != false)
	{
		$ag_counts[$newArray['J_USER_ID']]['ALL'] = $ag_counts[$newArray['J_USER_ID']]['ALL'] + 1;
		$count_all++;
		if (in_array($newArray['JT_JOB_TYPE_ID'], $ids_JOB_TYPE_retraces))
		{
			$ag_counts[$newArray['J_USER_ID']]['RETRACES'] = $ag_counts[$newArray['J_USER_ID']]['RETRACES'] + 1;
			$count_retraces++;
		}
		if (!in_array($newArray['C_CODE'], $clients))
			$clients[] = $newArray['C_CODE'];
	}
	#dprint("ag_counts=" . print_r($ag_counts,1) . "<br>All=$count_all, Retraces=$count_retraces<br>clients=" . print_r($clients,1));#

	$headings = array('', 'New Jobs', 'Retraces');

	$datalines = array();
	$datalines[] = array('NAME' => 'Total jobs waiting to be processed', 'ALL' => $count_all, 'RETRACES' => $count_retraces);
	foreach ($t_agents as $agid => $name)
	{
		if (array_key_exists($agid, $ag_counts))
		{
			$c_all = $ag_counts[$agid]['ALL'];
			$c_ret = $ag_counts[$agid]['RETRACES'];
		}
		else
		{
			$c_all = 0;
			$c_ret = 0;
		}
		$datalines[$agid] = array('NAME' => "Jobs with $name", 'ALL' => $c_all, 'RETRACES' => $c_ret);
	}
	#dprint("datalines=" . print_r($datalines,1));#

	if ($dest == 's')
	{
		$screen = "
		<p style=\"font-size:18px; font-weight:bold;\">
			$title
			&nbsp;&nbsp;&nbsp;&nbsp;
			" . input_button('Export to Excel', "export_xl()", '', 'but_export_xl') . "
		</p>

		<table class=\"spaced_table\">
		<tr>
			";
			foreach ($headings as $head)
				$screen .= "<th>$head</th>";
			if (user_debug())
				$screen .= "<th $grey>Group ID</th>";
			$screen .= "
		</tr>
		";
		foreach ($datalines as $dline)
		{
			$group = (array_key_exists('CLIENT_GROUP_ID', $dline) ? $dline['CLIENT_GROUP_ID'] : '');
			$screen .= "
			<tr>
			<td>{$dline['NAME']}</td><td $ar>{$dline['ALL']}</td><td $ar>{$dline['RETRACES']}</td>
			" . (user_debug() ? "<td $grey>$group</td>" : '') . "
			</tr>
			";
		}
		$screen .= "
		</table>
		";
		printD($screen);
	}
	elseif ($dest == 'x')
	{
		$top_lines = array(array($title), array());
		$formats = array();
		$sheet = array($title_short, $top_lines, $headings, $datalines, $formats, '');
		phpExcel_output($reps_subdir, $xfile, array($sheet)); # library.php

		# Auto-download file to PC
		printD("
		<script type=\"text/javascript\">
			document.form_csv_download.short_fname.value = '$xfile.$phpExcel_ext';
			document.form_csv_download.full_fname.value = '$csv_path' + '{$reps_subdir}/$xfile.$phpExcel_ext';
			document.form_csv_download.submit();
		</script>
		");
	}
	else
		dlog("$this_function: *=* bad dest \"$dest\"");

} # rep_ts_num_jobs()

function rep_ts_days_behind($dest, $sc_date_fr, $sc_date_to, $sc_client, $sc_group, $xfile='')
{
	# Traces/Statistics: Number of Days Behind.
	# $dest is 's' for screen or 'x' for export to Excel file.
	# $sc_date_fr and $sc_date_to are search criteria that form a date range which
	#		job placement dates must fall within; either may be blank.
	# $sc_client is search criteria: a client code or part of a client name (or an alpha code)
	# $sc_group is search criteria: a client group ID
	# $xfile is the name of the file that the search results should be written to;
	#		it is used when $dest=='x'

	global $ar;
	global $csv_path;
	global $grey;
	global $phpExcel_ext; # settings.php: "xls"
	global $sqlFalse;
	global $sqlTrue;
	global $reps_subdir;

	$this_function = "rep_ts_days_behind(\"$dest\", \"$sc_date_fr\", \"$sc_date_to\", \"$sc_client\", \"$sc_group\", \"$xfile\")";

	#dprint("Entered: $this_function");#

	$t_agents = sql_get_agents('t', true, false); # get list of trace agents, e.g. Array ( [13] => Alex Salamone [10] => Del Hudson ... )
	$ag_ids = array();
	foreach ($t_agents as $tid => $tname)
	{
		$ag_ids[] = $tid;
		$t_agents[$tid] = array('NAME' => $tname,
								'MOST_DAYS' => array('JOB_ID' => 0, 'VILNO' => '', 'DAYS_WITH_USER' => ''),
								'OLDEST' => array('JOB_ID' => 0, 'VILNO' => '', 'DAYS_OLD' => '')
								);
	}
	$tname=$tname; # suppress warnings
	#dprint("\$t_agents=" . print_r($t_agents,1) . "<br>\$ag_ids=" . print_r($ag_ids,1));#

	if ($sc_date_fr)
	{
		if ($sc_date_to)
			$date_string = "between $sc_date_fr and $sc_date_to";
		else
			$date_string = "from $sc_date_fr";
	}
	elseif ($sc_date_to)
		$date_string = "up to $sc_date_to";
	else
		$date_string = "(all dates)";

	$sc_c_code = intval($sc_client);
	$client_string = '';
	if ($sc_client)
	{
		$client_name = '';
		if ($sc_c_code > 0)
		{
			$client_name = client_name_from_code($sc_c_code);
			if ($client_name)
				$client_name = "$sc_c_code - $client_name";
		}
		if (!$client_name)
			$client_name = $sc_client;
		$client_string .= ", client $client_name";
	}

	$group_id = intval($sc_group);
	if ($group_id > 0)
		$group_string = ", client group \"" . sql_client_group_name_from_id($group_id) . "\"";
	else
		$group_string = '';

	$title_short = "Number of days behind";
	$title = "{$title_short} {$date_string}{$client_string}{$group_string}";

	$where = array("J.OBSOLETE=$sqlFalse", "J.JT_JOB=$sqlTrue", "J.J_CLOSED_DT IS NULL", "J.J_COMPLETE IS NULL OR J.J_COMPLETE<>1");

	$where_user = "J.J_USER_ID IN (" . implode(',', $ag_ids) . ")";
	$where[] = $where_user;

	$date_field = 'J.J_OPENED_DT'; # job placement date
	if ($sc_date_fr)
		$where[] = "'" . date_for_sql_nqnt($sc_date_fr) . "' <= $date_field";
	if ($sc_date_to)
		$where[] = "$date_field < '" . date_for_sql_nqnt($sc_date_to, true) . "'"; # NOT "<="
	if ($sc_client)
	{
		$where[] = "C.CLIENT2_ID > 0";
		if ($sc_c_code > 0)
			$where[] = "C.C_CODE = $sc_c_code";
		else
		{
			$sc_client2_id = (($sc_client[0] == '*') ? intval(trim(str_replace('*', '', $sc_client))) : 0);
			if ($sc_client2_id > 0)
				$where[] = "C.CLIENT2_ID = $sc_client2_id";
			else
			{
				sql_encryption_preparation('CLIENT2');
				$sc_client = addslashes_kdb($sc_client);
				$sql = "SELECT CLIENT2_ID FROM CLIENT2 WHERE " . sql_decrypt('C_CO_NAME') . " LIKE '%{$sc_client}%' OR " .
							"(ALPHA_CODE='" . addslashes_kdb($sc_client) . "')";
				#dprint($sql);#
				sql_execute($sql);
				$cids = array();
				while (($newArray = sql_fetch()) != false)
					$cids[] = $newArray[0];
				if ($cids)
					$where[] = "C.CLIENT2_ID IN (" . implode(',', $cids) . ")";
				else
					dprint("sc_client \"$sc_client\" yielded no client_id(s)");
			}
		}
	}
	if ($group_id > 0)
	{
		$where[] = "G.CLIENT_GROUP_ID > 0";
		$where[] = "G.CLIENT_GROUP_ID=$group_id";
		$group_select = ", G.CLIENT_GROUP_ID";
		$group_join = " LEFT JOIN CLIENT_GROUP G ON G.CLIENT_GROUP_ID=C.CLIENT_GROUP_ID ";
	}
	else
	{
		$group_select = '';
		$group_join = '';
	}
	$client_join = "LEFT JOIN CLIENT2 AS C ON C.CLIENT2_ID=J.CLIENT2_ID";

	if ($where)
		$where = "WHERE (" . implode(') AND (', $where) . ")";
	else
		$where = '';

	sql_encryption_preparation('CLIENT2');
	$sql = "
		SELECT J.JOB_ID, J.J_VILNO, J.J_OPENED_DT, J.J_USER_ID, J.J_USER_DT, J.J_TARGET_DT, C.C_CODE
		FROM JOB AS J
		$client_join
		$group_join
		$where
		";
	if ($dest == 's') dprint($sql);#
	sql_execute($sql);
	$clients = array();
	while (($newArray = sql_fetch_assoc()) != false)
	{
		# Test whether target date is before today i.e. target <= yesterday
		if ($newArray['J_TARGET_DT'] <= date_for_sql_nqnt(date_plus_days(-1)))
		{
			$agid = $newArray['J_USER_ID'];
			$days_with_user = round(
				(floatval(date_to_epoch(date_now_sql(true), false)) - floatval(date_to_epoch($newArray['J_USER_DT'], false)))
							/ (60.0 * 60.0 * 24.0));
			$days_old = round(
				(floatval(date_to_epoch(date_now_sql(true), false)) - floatval(date_to_epoch($newArray['J_OPENED_DT'], false)))
							/ (60.0 * 60.0 * 24.0));
			if ($t_agents[$agid]['MOST_DAYS']['DAYS_WITH_USER'] < $days_with_user)
			{
				$t_agents[$agid]['MOST_DAYS']['JOB_ID'] = $newArray['JOB_ID'];
				$t_agents[$agid]['MOST_DAYS']['VILNO'] = $newArray['J_VILNO'];
				$t_agents[$agid]['MOST_DAYS']['DAYS_WITH_USER'] = $days_with_user;
			}
			if ($t_agents[$agid]['OLDEST']['DAYS_OLD'] < $days_old)
			{
				$t_agents[$agid]['OLDEST']['JOB_ID'] = $newArray['JOB_ID'];
				$t_agents[$agid]['OLDEST']['VILNO'] = $newArray['J_VILNO'];
				$t_agents[$agid]['OLDEST']['DAYS_OLD'] = $days_old;
			}
		}
		if (!in_array($newArray['C_CODE'], $clients))
			$clients[] = $newArray['C_CODE'];
	}
	#dprint("\$t_agents=" . print_r($t_agents,1) . "<br>clients=" . print_r($clients,1));#

	$headings = array('', 'Longest Possession', '', 'Oldest Job', '');
	$second_h_s = array('', 'Job No.', 'Days with User', 'Job No.', 'Days Old'); # for screen
	$second_h_x = array(0 => array('NAME' => '', 'JOB_NO_M' => 'Job No.', 'DAYS_WITH_USER' => 'Days with User',
										'JOB_NO_O' => 'Job No.', 'OLDEST' => 'Days Old')); # for export

	$datalines = array();
	foreach ($t_agents as $agid => $info)
	{
		$datalines[$agid] = array('NAME' => $info['NAME'],
								'JOB_NO_M' => $info['MOST_DAYS']['VILNO'],
								'DAYS_WITH_USER' => $info['MOST_DAYS']['DAYS_WITH_USER'],
								'JOB_NO_O' => $info['OLDEST']['VILNO'],
								'OLDEST' => $info['OLDEST']['DAYS_OLD']
								);
	}

	$sql2 = str_replace($where_user, "J.J_USER_ID IS NULL", $sql);
	if ($dest == 's') dprint($sql2);#
	sql_execute($sql2);
	$oldest_vilno = '';
	$oldest_days = 0;
	while (($newArray = sql_fetch_assoc()) != false)
	{
		# Test whether target date is before today i.e. target <= yesterday
		if ($newArray['J_TARGET_DT'] <= date_for_sql_nqnt(date_plus_days(-1)))
		{
			$days_old = round(
				(floatval(date_to_epoch(date_now_sql(true), false)) - floatval(date_to_epoch($newArray['J_OPENED_DT'], false)))
							/ (60.0 * 60.0 * 24.0));
			if ($oldest_days < $days_old)
			{
				$oldest_vilno = $newArray['J_VILNO'];
				$oldest_days = $days_old;
			}
		}
	}

	$sql2 = str_replace($client_join, "$client_join LEFT JOIN JOB_LETTER AS JL ON J.JOB_ID=JL.JOB_ID AND JL.OBSOLETE=$sqlFalse",
				str_replace($where_user, "J.JT_REPORT_APPR IS NOT NULL AND J.JT_REPORT_APPR <> '' AND JL.JL_APPROVED_DT IS NULL",
					$sql));
	if ($dest == 's') dprint($sql2);#
	sql_execute($sql2);
	$wp_vilno = '';
	$wp_days = 0;
	while (($newArray = sql_fetch_assoc()) != false)
	{
		# Test whether target date is before today i.e. target <= yesterday
		if ($newArray['J_TARGET_DT'] <= date_for_sql_nqnt(date_plus_days(-1)))
		{
			$days_old = round(
				(floatval(date_to_epoch(date_now_sql(true), false)) - floatval(date_to_epoch($newArray['J_OPENED_DT'], false)))
							/ (60.0 * 60.0 * 24.0));
			if ($wp_days < $days_old)
			{
				$wp_vilno = $newArray['J_VILNO'];
				$wp_days = $days_old;
			}
		}
	}

	$sql2 = str_replace($client_join, "$client_join LEFT JOIN JOB_LETTER AS JL ON J.JOB_ID=JL.JOB_ID AND JL.OBSOLETE=$sqlFalse",
				str_replace($where_user, "J.JT_REPORT_APPR IS NOT NULL AND J.JT_REPORT_APPR <> '' AND " .
										 "JL.JL_APPROVED_DT IS NOT NULL AND JL.JL_APPROVED_DT <> '' AND " .
										 "JL.JL_EMAIL_ID IS NULL AND JL.JL_POSTED_DT IS NULL",
					$sql));
	if ($dest == 's') dprint($sql2);#
	sql_execute($sql2);
	$prt_vilno = '';
	$prt_days = 0;
	while (($newArray = sql_fetch_assoc()) != false)
	{
		# Test whether target date is before today i.e. target <= yesterday
		if ($newArray['J_TARGET_DT'] <= date_for_sql_nqnt(date_plus_days(-1)))
		{
			$days_old = round(
				(floatval(date_to_epoch(date_now_sql(true), false)) - floatval(date_to_epoch($newArray['J_OPENED_DT'], false)))
							/ (60.0 * 60.0 * 24.0));
			if ($prt_days < $days_old)
			{
				$prt_vilno = $newArray['J_VILNO'];
				$prt_days = $days_old;
			}
		}
	}

	$datalines[-1] = array('NAME' => '', # blank line
								'JOB_NO_M' => '',
								'DAYS_WITH_USER' => '',
								'JOB_NO_O' => '',
								'OLDEST' => ''
								);
	$datalines[-2] = array('NAME' => 'Oldest job in Queue',
								'JOB_NO_M' => '',
								'DAYS_WITH_USER' => '',
								'JOB_NO_O' => ($oldest_vilno ? $oldest_vilno : '-'),
								'OLDEST' => ($oldest_days ? $oldest_days : '-')
								);
	$datalines[-3] = array('NAME' => 'Oldest job Waiting for W.P.',
								'JOB_NO_M' => '',
								'DAYS_WITH_USER' => '',
								'JOB_NO_O' => ($wp_vilno ? $wp_vilno : '-'),
								'OLDEST' => ($wp_days ? $wp_days : '-')
								);
	$datalines[-4] = array('NAME' => 'Oldest job Waiting for Printing',
								'JOB_NO_M' => '',
								'DAYS_WITH_USER' => '',
								'JOB_NO_O' => ($prt_vilno ? $prt_vilno : '-'),
								'OLDEST' => ($prt_days ? $prt_days : '-')
								);
	#dprint("datalines=" . print_r($datalines,1));#

	if ($dest == 's')
	{
		$screen = "
		<p style=\"font-size:18px; font-weight:bold;\">
			$title
			&nbsp;&nbsp;&nbsp;&nbsp;
			" . input_button('Export to Excel', "export_xl()", '', 'but_export_xl') . "
		</p>

		<table class=\"spaced_table\">
		<tr>
			";
			foreach ($headings as $head)
				$screen .= "<th>$head</th>";
			if (user_debug())
				$screen .= "<th $grey>Group ID</th>";
			$screen .= "
		</tr>
		<tr>
			";
			foreach ($second_h_s as $head)
				$screen .= "<th>$head</th>";
			if (user_debug())
				$screen .= "<th></th>";
			$screen .= "
		</tr>
		";
		foreach ($datalines as $dline)
		{
			$group = (array_key_exists('CLIENT_GROUP_ID', $dline) ? $dline['CLIENT_GROUP_ID'] : '');
			$screen .= "
			<tr>
			<td>{$dline['NAME']}</td><td $ar>{$dline['JOB_NO_M']}</td><td $ar>{$dline['DAYS_WITH_USER']}</td>
									<td $ar>{$dline['JOB_NO_O']}</td><td $ar>{$dline['OLDEST']}</td>
			" . (user_debug() ? "<td $grey>$group</td>" : '') . "
			</tr>
			";
		}
		$screen .= "
		</table>
		";
		printD($screen);
	}
	elseif ($dest == 'x')
	{
		$top_lines = array(array($title), array());
		$formats = array();
		$datalines = array_merge($second_h_x, $datalines);
		#dprint("datalines/x=" . print_r($datalines,1));#
		$sheet = array($title_short, $top_lines, $headings, $datalines, $formats, '');
		phpExcel_output($reps_subdir, $xfile, array($sheet)); # library.php

		# Auto-download file to PC
		printD("
		<script type=\"text/javascript\">
			document.form_csv_download.short_fname.value = '$xfile.$phpExcel_ext';
			document.form_csv_download.full_fname.value = '$csv_path' + '{$reps_subdir}/$xfile.$phpExcel_ext';
			document.form_csv_download.submit();
		</script>
		");
	}
	else
		dlog("$this_function: *=* bad dest \"$dest\"");

} # rep_ts_days_behind()

function rep_ts_success_client($dest, $sc_date_fr, $sc_date_to, $sc_client, $sc_group, $xfile='')
{
	# Traces/Statistics: Success rate per client.
	# $dest is 's' for screen or 'x' for export to Excel file.
	# $sc_date_fr and $sc_date_to are search criteria that form a date range which
	#		job placement dates must fall within; either may be blank.
	# $sc_client is search criteria: a client code or part of a client name (or an alpha code)
	# $sc_group is search criteria: a client group ID
	# $xfile is the name of the file that the search results should be written to;
	#		it is used when $dest=='x'

	global $ar;
	global $csv_path;
	global $excel_int_or_dec_format;
	global $excel_integer_format;
	global $grey;
	global $phpExcel_ext; # settings.php: "xls"
	global $sqlFalse;
	global $sqlTrue;
	global $reps_subdir;

	$this_function = "rep_ts_success_client(\"$dest\", \"$sc_date_fr\", \"$sc_date_to\", \"$sc_client\", \"$sc_group\", \"$xfile\")";

	#dprint("Entered: $this_function");#

	if ($sc_date_fr)
	{
		if ($sc_date_to)
			$date_string = "between $sc_date_fr and $sc_date_to";
		else
			$date_string = "from $sc_date_fr";
	}
	elseif ($sc_date_to)
		$date_string = "up to $sc_date_to";
	else
		$date_string = "(all dates)";

	$sc_c_code = intval($sc_client);
	$client_string = '';
	if ($sc_client)
	{
		$client_name = '';
		if ($sc_c_code > 0)
		{
			$client_name = client_name_from_code($sc_c_code);
			if ($client_name)
				$client_name = "$sc_c_code - $client_name";
		}
		if (!$client_name)
			$client_name = $sc_client;
		$client_string .= ", client $client_name";
	}

	$group_id = intval($sc_group);
	if ($group_id > 0)
		$group_string = ", client group \"" . sql_client_group_name_from_id($group_id) . "\"";
	else
		$group_string = '';

	$title_short = "Success rate per client";
	$title = "{$title_short} {$date_string}{$client_string}{$group_string}";

	$where = array("J.OBSOLETE=$sqlFalse", "J.JT_JOB=$sqlTrue");
	$date_field_raw = "J_OPENED_DT";
	$date_field = "J.$date_field_raw";
	if ($sc_date_fr)
		$where[] = "'" . date_for_sql_nqnt($sc_date_fr) . "' <= $date_field";
	if ($sc_date_to)
		$where[] = "$date_field < '" . date_for_sql_nqnt($sc_date_to, true) . "'"; # NOT "<="
	if ($sc_client)
	{
		$where[] = "C.CLIENT2_ID > 0";
		if ($sc_c_code > 0)
			$where[] = "C.C_CODE = $sc_c_code";
		else
		{
			$sc_client2_id = (($sc_client[0] == '*') ? intval(trim(str_replace('*', '', $sc_client))) : 0);
			if ($sc_client2_id > 0)
				$where[] = "C.CLIENT2_ID = $sc_client2_id";
			else
			{
				sql_encryption_preparation('CLIENT2');
				$sc_client = addslashes_kdb($sc_client);
				$sql = "SELECT CLIENT2_ID FROM CLIENT2 WHERE " . sql_decrypt('C_CO_NAME') . " LIKE '%{$sc_client}%' OR " .
							"(ALPHA_CODE='" . addslashes_kdb($sc_client) . "')";
				#dprint($sql);#
				sql_execute($sql);
				$cids = array();
				while (($newArray = sql_fetch()) != false)
					$cids[] = $newArray[0];
				if ($cids)
					$where[] = "C.CLIENT2_ID IN (" . implode(',', $cids) . ")";
				else
					dprint("sc_client \"$sc_client\" yielded no client_id(s)");
			}
		}
	}
	if ($group_id > 0)
	{
		$where[] = "G.CLIENT_GROUP_ID > 0";
		$where[] = "G.CLIENT_GROUP_ID=$group_id";
		$group_select = ", G.CLIENT_GROUP_ID";
		$group_join = " LEFT JOIN CLIENT_GROUP G ON G.CLIENT_GROUP_ID=C.CLIENT_GROUP_ID ";
	}
	else
	{
		$group_select = '';
		$group_join = '';
	}
	if ($where)
		$where = "WHERE (" . implode(') AND (', $where) . ")";
	else
		$where = '';

	$clients = array();
	sql_encryption_preparation('CLIENT2');
	$sql = "
		SELECT C.CLIENT2_ID, C.C_CODE, " . sql_decrypt('C.C_CO_NAME', '', true) . ",
				J.JOB_ID, $date_field, J.J_COMPLETE, J.J_CLOSED_DT, J.JT_SUCCESS, J.JT_CREDIT,
				JT.JT_CODE $group_select
		FROM JOB AS J
		LEFT JOIN CLIENT2 AS C ON C.CLIENT2_ID=J.CLIENT2_ID
		LEFT JOIN JOB_TYPE_SD AS JT ON JT.JOB_TYPE_ID=J.JT_JOB_TYPE_ID
		$group_join
		$where
		ORDER BY C.C_CODE
		";
	if ($dest == 's') dprint($sql);#
	sql_execute($sql);
	while (($newArray = sql_fetch_assoc()) != false)
	{
		$cid = $newArray['CLIENT2_ID'];
		if (!array_key_exists($cid, $clients))
			$clients[$cid] = array('C_CODE' => $newArray['C_CODE'], 'C_CO_NAME' => $newArray['C_CO_NAME'],
									'JOB_COUNT' => 0, 'COMPLETED' => 0, 'PENDING' => 0, 'SUCCESS' => 0,
									'RT1_SUCCESS' => 0, 'RT1_FOC' => 0, 'RT1_CREDIT' => 0, 'RT1_PEND' => 0,
									'RT2_SUCCESS' => 0, 'RT2_FOC' => 0, 'RT2_CREDIT' => 0, 'RT2_PEND' => 0,
									'RT3_SUCCESS' => 0, 'RT3_FOC' => 0, 'RT3_CREDIT' => 0, 'RT3_PEND' => 0,
									);
		$clients[$cid]['JOB_COUNT'] = $clients[$cid]['JOB_COUNT'] + 1;
		if (($newArray['J_COMPLETE'] == 1) || (strlen($newArray['J_CLOSED_DT']) > 0))
		{
			$clients[$cid]['COMPLETED'] = $clients[$cid]['COMPLETED'] + 1;
			if ($newArray['JT_SUCCESS'] == 1)
				$clients[$cid]['SUCCESS'] = $clients[$cid]['SUCCESS'] + 1;
		}
		elseif ($newArray['J_COMPLETE'] == -1)
			$clients[$cid]['PENDING'] = $clients[$cid]['PENDING'] + 1;

		if ($newArray['JT_CODE'] == 'RT1')
		{
			if ($newArray['JT_SUCCESS'] == 1)
				$clients[$cid]['RT1_SUCCESS'] = $clients[$cid]['RT1_SUCCESS'] + 1;
			elseif ($newArray['JT_CREDIT'] == 1)
				$clients[$cid]['RT1_CREDIT'] = $clients[$cid]['RT1_CREDIT'] + 1;
			elseif (($newArray['JT_SUCCESS'] == -1) || ($newArray['JT_CREDIT'] == -1))
				$clients[$cid]['RT1_FOC'] = $clients[$cid]['RT1_FOC'] + 1;
			else
				$clients[$cid]['RT1_PEND'] = $clients[$cid]['RT1_PEND'] + 1;
		}
		elseif ($newArray['JT_CODE'] == 'RT2')
		{
			if ($newArray['JT_SUCCESS'] == 1)
				$clients[$cid]['RT2_SUCCESS'] = $clients[$cid]['RT2_SUCCESS'] + 1;
			elseif ($newArray['JT_CREDIT'] == 1)
				$clients[$cid]['RT2_CREDIT'] = $clients[$cid]['RT2_CREDIT'] + 1;
			elseif (($newArray['JT_SUCCESS'] == -1) || ($newArray['JT_CREDIT'] == -1))
				$clients[$cid]['RT2_FOC'] = $clients[$cid]['RT2_FOC'] + 1;
			else
				$clients[$cid]['RT2_PEND'] = $clients[$cid]['RT2_PEND'] + 1;
		}
		elseif ($newArray['JT_CODE'] == 'RT3')
		{
			if ($newArray['JT_SUCCESS'] == 1)
				$clients[$cid]['RT3_SUCCESS'] = $clients[$cid]['RT3_SUCCESS'] + 1;
			elseif ($newArray['JT_CREDIT'] == 1)
				$clients[$cid]['RT3_CREDIT'] = $clients[$cid]['RT3_CREDIT'] + 1;
			elseif (($newArray['JT_SUCCESS'] == -1) || ($newArray['JT_CREDIT'] == -1))
				$clients[$cid]['RT3_FOC'] = $clients[$cid]['RT3_FOC'] + 1;
			else
				$clients[$cid]['RT3_PEND'] = $clients[$cid]['RT3_PEND'] + 1;
		}
	}

	#dprint("\$clients=" . print_r($clients,1));#

	$datalines = array();

	foreach ($clients as $client)
	{
		$datalines[] = array($client['C_CODE'], $client['C_CO_NAME'], "New Jobs:", '', '', '', '', '');
		$datalines[] = array('', '', "Number of Jobs received in Period:", '', '', '', '', $client['JOB_COUNT']);
		$datalines[] = array('', '', "Number Completed:", '', '', '', '', $client['COMPLETED']);
		$datalines[] = array('', '', "Number Pending:", '', '', '', '', $client['PENDING']);
		$datalines[] = array('', '', '', '', '', '', '', '');
		$datalines[] = array('', '', "Number of Successful Completions:", '', '', '', '', $client['SUCCESS']);
		$temp = ($client['JOB_COUNT'] ?
					round(100.0 * floatval($client['SUCCESS']) / floatval($client['JOB_COUNT']), 2) : '-');
		$datalines[] = array('', '', "Percentage Success Rate:", '', '', '', '', $temp);
		$datalines[] = array('', '', '', '', '', '', '', '');
		$datalines[] = array('', '', "Retraces:", '', '', '', '', '');
		$datalines[] = array('', '', '', "Total", "Success", "F.O.C.", "Credit", "Pending");
		$temp = $client['RT1_SUCCESS'] + $client['RT1_FOC'] + $client['RT1_CREDIT'] + $client['RT1_PEND'];
		$datalines[] = array('', '', "Retrace 1:", $temp, $client['RT1_SUCCESS'], $client['RT1_FOC'], $client['RT1_CREDIT'], $client['RT1_PEND']);
		$temp = $client['RT2_SUCCESS'] + $client['RT2_FOC'] + $client['RT2_CREDIT'] + $client['RT2_PEND'];
		$datalines[] = array('', '', "Retrace 2:", $temp, $client['RT2_SUCCESS'], $client['RT2_FOC'], $client['RT2_CREDIT'], $client['RT2_PEND']);
		$temp = $client['RT3_SUCCESS'] + $client['RT3_FOC'] + $client['RT3_CREDIT'] + $client['RT3_PEND'];
		$datalines[] = array('', '', "Retrace 3:", $temp, $client['RT3_SUCCESS'], $client['RT3_FOC'], $client['RT3_CREDIT'], $client['RT3_PEND']);
	}
	#dprint("datalines=" . print_r($datalines,1));#

	$headings = array('Client', 'Client Name', 'Statistics', '', '', '', '', '');

	if ($dest == 's')
	{
		$screen = "
		<p style=\"font-size:18px; font-weight:bold;\">
			$title
			&nbsp;&nbsp;&nbsp;&nbsp;
			" . input_button('Export to Excel', "export_xl()", '', 'but_export_xl') . "
		</p>

		<table class=\"spaced_table\">
		<tr>
			";
			foreach ($headings as $head)
				$screen .= "<th>$head</th>";
			if (user_debug())
				$screen .= "<th $grey>Group ID</th>";
			$screen .= "
		</tr>
		";
		foreach ($datalines as $dline)
		{
			$group = (array_key_exists('CLIENT_GROUP_ID', $dline) ? $dline['CLIENT_GROUP_ID'] : '');
			$screen .= "
			<tr>
				<td>{$dline[0]}</td><td>{$dline[1]}</td><td>{$dline[2]}</td>
				<td $ar>{$dline[3]}</td><td $ar>{$dline[4]}</td><td $ar>{$dline[5]}</td>
				<td $ar>{$dline[6]}</td><td $ar>{$dline[7]}</td>";
			if (user_debug())
				$screen .= "<td $grey>$group</td>";
			$screen .= "
			</tr>
			";
		}
		$screen .= "
		</table>
		";
		printD($screen);
	}
	elseif ($dest == 'x')
	{
		$top_lines = array(array($title), array());
		$formats = array('D' => $excel_integer_format, 'E' => $excel_integer_format, 'F' => $excel_integer_format, 'G' => $excel_integer_format,
							'H' => $excel_int_or_dec_format);
		$sheet = array($title_short, $top_lines, $headings, $datalines, $formats, '');
		phpExcel_output($reps_subdir, $xfile, array($sheet)); # library.php

		# Auto-download file to PC
		printD("
		<script type=\"text/javascript\">
			document.form_csv_download.short_fname.value = '$xfile.$phpExcel_ext';
			document.form_csv_download.full_fname.value = '$csv_path' + '{$reps_subdir}/$xfile.$phpExcel_ext';
			document.form_csv_download.submit();
		</script>
		");
	}
	else
		dlog("$this_function: *=* bad dest \"$dest\"");
} # rep_ts_success_client()

function rep_ts_breakdown($dest, $sc_date_fr, $sc_date_to, $sc_client, $sc_group, $xfile='')
{
	# Traces/Statistics: Breakdown of job types.
	# $dest is 's' for screen or 'x' for export to Excel file.
	# $sc_date_fr and $sc_date_to are search criteria that form a date range which
	#		job placement dates must fall within; either may be blank.
	# $sc_client is search criteria: a client code or part of a client name (or an alpha code)
	# $sc_group is search criteria: a client group ID
	# $xfile is the name of the file that the search results should be written to;
	#		it is used when $dest=='x'

	global $ar;
	global $csv_path;
	global $excel_decimal_format;
	global $excel_integer_format;
	global $grey;
	global $JOB_TYPES;
	global $phpExcel_ext; # settings.php: "xls"
	global $sqlFalse;
	global $sqlTrue;
	global $reps_subdir;

	$this_function = "rep_ts_breakdown(\"$dest\", \"$sc_date_fr\", \"$sc_date_to\", \"$sc_client\", \"$sc_group\", \"$xfile\")";

	#dprint("Entered: $this_function");#

	if ($sc_date_fr)
	{
		if ($sc_date_to)
			$date_string = "between $sc_date_fr and $sc_date_to";
		else
			$date_string = "from $sc_date_fr";
	}
	elseif ($sc_date_to)
		$date_string = "up to $sc_date_to";
	else
		$date_string = "(all dates)";

	$sc_c_code = intval($sc_client);
	$client_string = '';
	if ($sc_client)
	{
		$client_name = '';
		if ($sc_c_code > 0)
		{
			$client_name = client_name_from_code($sc_c_code);
			if ($client_name)
				$client_name = "$sc_c_code - $client_name";
		}
		if (!$client_name)
			$client_name = $sc_client;
		$client_string .= ", client $client_name";
	}

	$group_id = intval($sc_group);
	if ($group_id > 0)
	{
		$group_name = sql_client_group_name_from_id($group_id);
		$group_string = ", client group \"$group_name\"";
	}
	else
	{
		$group_name = '';
		$group_string = '';
	}

	$title_short = "Breakdown of job types";
	$title = "{$title_short} {$date_string}{$client_string}{$group_string}";

	$where = array("J.OBSOLETE=$sqlFalse", "J.JT_JOB=$sqlTrue");
	$date_field_raw = "J_OPENED_DT";
	$date_field = "J.$date_field_raw";
	if ($sc_date_fr)
		$where[] = "'" . date_for_sql_nqnt($sc_date_fr) . "' <= $date_field";
	if ($sc_date_to)
		$where[] = "$date_field < '" . date_for_sql_nqnt($sc_date_to, true) . "'"; # NOT "<="
	if ($sc_client)
	{
		$where[] = "C.CLIENT2_ID > 0";
		if ($sc_c_code > 0)
			$where[] = "C.C_CODE = $sc_c_code";
		else
		{
			$sc_client2_id = (($sc_client[0] == '*') ? intval(trim(str_replace('*', '', $sc_client))) : 0);
			if ($sc_client2_id > 0)
				$where[] = "C.CLIENT2_ID = $sc_client2_id";
			else
			{
				sql_encryption_preparation('CLIENT2');
				$sc_client = addslashes_kdb($sc_client);
				$sql = "SELECT CLIENT2_ID FROM CLIENT2 WHERE " . sql_decrypt('C_CO_NAME') . " LIKE '%{$sc_client}%' OR " .
							"(ALPHA_CODE='" . addslashes_kdb($sc_client) . "')";
				#dprint($sql);#
				sql_execute($sql);
				$cids = array();
				while (($newArray = sql_fetch()) != false)
					$cids[] = $newArray[0];
				if ($cids)
					$where[] = "C.CLIENT2_ID IN (" . implode(',', $cids) . ")";
				else
					dprint("sc_client \"$sc_client\" yielded no client_id(s)");
			}
		}
	}
	if ($group_id > 0)
	{
		$where[] = "G.CLIENT_GROUP_ID > 0";
		$where[] = "G.CLIENT_GROUP_ID=$group_id";
		$group_select = ", G.CLIENT_GROUP_ID";
		$group_join = " LEFT JOIN CLIENT_GROUP G ON G.CLIENT_GROUP_ID=C.CLIENT_GROUP_ID ";
	}
	else
	{
		$group_select = '';
		$group_join = '';
	}
	if ($where)
		$where = "WHERE (" . implode(') AND (', $where) . ")";
	else
		$where = '';

	$totals = array();
	foreach ($JOB_TYPES as $jtype)
		$totals["JT_{$jtype['JT_CODE']}"] = 0;
	$grand_total = 0;

	$clients = array();
	sql_encryption_preparation('CLIENT2');
	$sql = "
		SELECT C.CLIENT2_ID, C.C_CODE, " . sql_decrypt('C.C_CO_NAME', '', true) . ",
				J.JOB_ID, JT.JT_CODE $group_select
		FROM JOB AS J
		LEFT JOIN CLIENT2 AS C ON C.CLIENT2_ID=J.CLIENT2_ID
		LEFT JOIN JOB_TYPE_SD AS JT ON JT.JOB_TYPE_ID=J.JT_JOB_TYPE_ID
		$group_join
		$where
		ORDER BY C.C_CODE
		";
	if ($dest == 's') dprint($sql);#
	sql_execute($sql);
	while (($newArray = sql_fetch_assoc()) != false)
	{
		$jtcode = $newArray['JT_CODE'];
		$totals["JT_{$jtcode}"] = $totals["JT_{$jtcode}"] + 1;
		if ($sc_client || $sc_group)
		{
			$cid = $newArray['CLIENT2_ID'];
			if (!array_key_exists($cid, $clients))
			{
				$clients[$cid] = array('C_CODE' => $newArray['C_CODE'], 'C_CO_NAME' => $newArray['C_CO_NAME']);
				foreach ($JOB_TYPES as $jtype)
					$clients[$cid]["JT_{$jtype['JT_CODE']}"] = 0;
			}
			$clients[$cid]["JT_{$jtcode}"] = $clients[$cid]["JT_{$jtcode}"] + 1;
		}
	}
	foreach ($totals as $tot)
		$grand_total += $tot;
	#dprint("\$grand_total=$grand_total, \$totals = " . print_r($totals,1) . "<br>\$clients=" . print_r($clients,1));#

	$datalines = array();
	if ($sc_group || ($sc_client && (1 < count($clients))))
	{
		if ($sc_client)
		{
			if ($sc_group)
				$datalines[] = array('', "\"$sc_client\" Clients in Group \"$group_name\"", '', '', '', '');
			else
				$datalines[] = array('', "All \"$sc_client\" Clients", '', '', '', '');
		}
		elseif ($sc_group)
			$datalines[] = array('', "Client group \"$group_name\"", '', '', '', '');

		foreach ($JOB_TYPES as $jtype)
		{
			$temp1 = intval($totals["JT_{$jtype['JT_CODE']}"]);
			$temp2 = ($grand_total ? round(100.0 * floatval($temp1) / $grand_total, 2) : 0.0);
			if ($sc_client || $sc_group)
				$datalines[] = array('', '', $jtype['JT_TYPE'], $temp1, $temp2);
			else
				$datalines[] = array(        $jtype['JT_TYPE'], $temp1, $temp2);
		}
		if ($sc_client || $sc_group)
			$datalines[] = array('', '', 'TOTALS', $grand_total, 100.0);
		else
			$datalines[] = array(        'TOTALS', $grand_total, 100.0);
		#dprint("datalines/1=" . print_r($datalines,1));#
	}

	if ($sc_client || $sc_group)
	{
		foreach ($clients as $cid => $client)
		{
			$c_total = 0;
			foreach ($JOB_TYPES as $jtype)
				$c_total += $client["JT_{$jtype['JT_CODE']}"];
			$datalines[] = array($client['C_CODE'], $client['C_CO_NAME'], '', '', '');
			foreach ($JOB_TYPES as $jtype)
			{
				$temp1 = intval($client["JT_{$jtype['JT_CODE']}"]);
				$temp2 = ($c_total ? round(100.0 * floatval($temp1) / $c_total, 2) : 0.0);
				$datalines[] = array('', '', $jtype['JT_TYPE'], $temp1, $temp2);
			}
			$datalines[] = array('', '', 'TOTALS', $c_total, 100.0);
		}
	}
	#dprint("datalines/2=" . print_r($datalines,1));#

	if ($sc_client || $sc_group)
		$headings = array('Client', 'Client Name', 'Job Type', 'Quantity', 'Percentage');
	else
		$headings = array(                         'Job Type', 'Quantity', 'Percentage');

	if ($dest == 's')
	{
		$screen = "
		<p style=\"font-size:18px; font-weight:bold;\">
			$title
			&nbsp;&nbsp;&nbsp;&nbsp;
			" . input_button('Export to Excel', "export_xl()", '', 'but_export_xl') . "
		</p>

		<table class=\"spaced_table\">
		<tr>
			";
			foreach ($headings as $head)
				$screen .= "<th>$head</th>";
			if (user_debug())
				$screen .= "<th $grey>Group ID</th>";
			$screen .= "
		</tr>
		";
		foreach ($datalines as $dline)
		{
			$group = (array_key_exists('CLIENT_GROUP_ID', $dline) ? $dline['CLIENT_GROUP_ID'] : '');
			$screen .= "
			<tr>
				";
				if ($sc_client || $sc_group)
					$screen .= "<td>{$dline[0]}</td><td>{$dline[1]}</td><td>{$dline[2]}</td><td $ar>{$dline[3]}</td><td $ar>{$dline[4]}</td>";
				else
					$screen .= "                                        <td>{$dline[0]}</td><td $ar>{$dline[1]}</td><td $ar>{$dline[2]}</td>";
				if (user_debug())
					$screen .= "<td $grey>$group</td>";
			$screen .= "
			</tr>
			";
		}
		$screen .= "
		</table>
		";
		printD($screen);
	}
	elseif ($dest == 'x')
	{
		$top_lines = array(array($title), array());
		if ($sc_client || $sc_group)
			$formats = array('D' => $excel_integer_format, 'E' => $excel_decimal_format);
		else
			$formats = array('B' => $excel_integer_format, 'C' => $excel_decimal_format);
		$sheet = array($title_short, $top_lines, $headings, $datalines, $formats, '');
		phpExcel_output($reps_subdir, $xfile, array($sheet)); # library.php

		# Auto-download file to PC
		printD("
		<script type=\"text/javascript\">
			document.form_csv_download.short_fname.value = '$xfile.$phpExcel_ext';
			document.form_csv_download.full_fname.value = '$csv_path' + '{$reps_subdir}/$xfile.$phpExcel_ext';
			document.form_csv_download.submit();
		</script>
		");
	}
	else
		dlog("$this_function: *=* bad dest \"$dest\"");
} # rep_ts_breakdown()

function rep_ts_client_month($dest, $sc_date_fr, $sc_date_to, $sc_sales, $sc_client, $sc_group, $xfile='')
{
	# Traces/Statistics: Jobs per client in month.
	# $dest is 's' for screen or 'x' for export to Excel file.
	# $sc_date_fr and $sc_date_to are search criteria that form a date range which
	#		job placement dates must fall within; either may be blank.
	# $sc_sales is ID of salesperson.
	# $sc_client is search criteria: a client code or part of a client name (or an alpha code)
	# $sc_group is search criteria: a client group ID
	# $xfile is the name of the file that the search results should be written to;
	#		it is used when $dest=='x'

	global $ar;
	global $csv_path;
	global $excel_integer_format;
	global $grey;
	global $phpExcel_ext; # settings.php: "xls"
	global $salespersons;
	global $sqlFalse;
	global $sqlTrue;
	global $reps_subdir;

	$this_function = "rep_ts_client_month(\"$dest\", \"$sc_date_fr\", \"$sc_date_to\", \"$sc_sales\", \"$sc_client\", \"$sc_group\", \"$xfile\")";

	#dprint("Entered: $this_function");#

	if ($sc_date_fr)
	{
		if ($sc_date_to)
			$date_string = "between $sc_date_fr and $sc_date_to";
		else
			$date_string = "from $sc_date_fr";
	}
	elseif ($sc_date_to)
		$date_string = "up to $sc_date_to";
	else
		$date_string = "(all dates)";

	if ($sc_sales > 0)
		$sales_string = " for salesperson " . $salespersons[$sc_sales];
	else
		$sales_string = " for all salespersons";

	$sc_c_code = intval($sc_client);
	$client_string = '';
	if ($sc_client)
	{
		$client_name = '';
		if ($sc_c_code > 0)
		{
			$client_name = client_name_from_code($sc_c_code);
			if ($client_name)
				$client_name = "$sc_c_code - $client_name";
		}
		if (!$client_name)
			$client_name = $sc_client;
		$client_string .= ", client $client_name";
	}

	$group_id = intval($sc_group);
	if ($group_id > 0)
		$group_string = ", client group \"" . sql_client_group_name_from_id($group_id) . "\"";
	else
		$group_string = '';

	$title_short = "Jobs per client in month";
	$title = "{$title_short} {$date_string}{$sales_string}{$client_string}{$group_string}";

	#dprint($title);#

	$where = array("J.OBSOLETE=$sqlFalse", "J.JT_JOB=$sqlTrue");
	$date_field_raw = "J_OPENED_DT";
	$date_field = "J.$date_field_raw";
	if ($sc_date_fr)
		$where[] = "'" . date_for_sql_nqnt($sc_date_fr) . "' <= $date_field";
	if ($sc_date_to)
		$where[] = "$date_field < '" . date_for_sql_nqnt($sc_date_to, true) . "'"; # NOT "<="
	if ($sc_sales > 0)
		$where[] = "C.SALESPERSON_ID = $sc_sales";
	if ($sc_client)
	{
		$where[] = "C.CLIENT2_ID > 0";
		if ($sc_c_code > 0)
			$where[] = "C.C_CODE = $sc_c_code";
		else
		{
			$sc_client2_id = (($sc_client[0] == '*') ? intval(trim(str_replace('*', '', $sc_client))) : 0);
			if ($sc_client2_id > 0)
				$where[] = "C.CLIENT2_ID = $sc_client2_id";
			else
			{
				sql_encryption_preparation('CLIENT2');
				$sc_client = addslashes_kdb($sc_client);
				$sql = "SELECT CLIENT2_ID FROM CLIENT2 WHERE " . sql_decrypt('C_CO_NAME') . " LIKE '%{$sc_client}%' OR " .
							"(ALPHA_CODE='" . addslashes_kdb($sc_client) . "')";
				#dprint($sql);#
				sql_execute($sql);
				$cids = array();
				while (($newArray = sql_fetch()) != false)
					$cids[] = $newArray[0];
				if ($cids)
					$where[] = "C.CLIENT2_ID IN (" . implode(',', $cids) . ")";
				else
					dprint("sc_client \"$sc_client\" yielded no client_id(s)");
			}
		}
	}
	if ($group_id > 0)
	{
		$where[] = "G.CLIENT_GROUP_ID > 0";
		$where[] = "G.CLIENT_GROUP_ID=$group_id";
		$group_select = ", G.CLIENT_GROUP_ID";
		$group_join = " LEFT JOIN CLIENT_GROUP G ON G.CLIENT_GROUP_ID=C.CLIENT_GROUP_ID ";
	}
	else
	{
		$group_select = '';
		$group_join = '';
	}
	if ($where)
		$where = "WHERE (" . implode(') AND (', $where) . ")";
	else
		$where = '';

	if ($sc_date_fr)
		$month_start = substr(date_for_sql_nqnt($sc_date_fr), 0, 7) . "-01"; # YYYY-MM-01
	else
		$month_start = "1980-01-01";
	if ($sc_date_to)
		$month_end = substr(date_for_sql_nqnt($sc_date_to), 0, 7) . "-01"; # YYYY-MM-01
	else
		$month_end = substr(date_now_sql(true), 0, 7) . "-01"; # YYYY-MM-01
	$months = months_range($month_start, $month_end, false); # library.php; all months have day="01"
	#dprint("Months range = \"$month_start\" .. \"$month_end\" = " . print_r($months,1));#
	# E.g.	Array ( [0] => 2010-01-01 [1] => 2010-02-01 [2] => 2010-03-01 )

	$clients = array();
	sql_encryption_preparation('CLIENT2');
	$sql = "
		SELECT C.CLIENT2_ID, C.C_CODE, " . sql_decrypt('C.C_CO_NAME', '', true) . ",
				J.JOB_ID, $date_field $group_select
		FROM JOB AS J
		LEFT JOIN CLIENT2 AS C ON C.CLIENT2_ID=J.CLIENT2_ID
		$group_join
		$where
		ORDER BY C.C_CODE
		";
	if ($dest == 's') dprint($sql);#
	sql_execute($sql);
	while (($newArray = sql_fetch_assoc()) != false)
	{
		$cid = $newArray['CLIENT2_ID'];
		if (!array_key_exists($cid, $clients))
		{
			$clients[$cid] = array('C_CODE' => $newArray['C_CODE'], 'C_CO_NAME' => $newArray['C_CO_NAME'],
									'JOB_COUNT' => 0);
			foreach ($months as $month)
				$clients[$cid][$month] = 0;
		}
		$clients[$cid]['JOB_COUNT'] = $clients[$cid]['JOB_COUNT'] + 1;

		$month = substr($newArray[$date_field_raw], 0, 7) . "-01"; # YYYY-MM-01
		$clients[$cid][$month] = $clients[$cid][$month] + 1;
	}
	#dprint("\$clients=" . print_r($clients,1));#

	$datalines = array();
	$totals = array();
	foreach ($months as $month)
		$totals[$month] = 0;
	$grand_total = 0;

	foreach ($clients as $client)
	{
		$temp = array($client['C_CODE'], $client['C_CO_NAME'], $client['JOB_COUNT']);
		$grand_total += $client['JOB_COUNT'];
		foreach ($months as $month)
		{
			$temp[] = $client[$month];
			$totals[$month] += $client[$month];
		}
		$datalines[] = $temp;
	}
	$temp = array('', 'TOTALS', $grand_total);
	foreach ($months as $month)
		$temp[] = $totals[$month];
	$datalines[] = $temp;
	#dprint("datalines=" . print_r($datalines,1));#

	$headings = array('Client', 'Client Name', 'TOTAL');
	foreach ($months as $month)
		$headings[] = substr(date_for_sql($month, true, false), 3); # Drop of "dd/" from front

	if ($dest == 's')
	{
		$screen = "
		<p style=\"font-size:18px; font-weight:bold;\">
			$title
			&nbsp;&nbsp;&nbsp;&nbsp;
			" . input_button('Export to Excel', "export_xl()", '', 'but_export_xl') . "
		</p>

		<table class=\"spaced_table\">
		<tr>
			";
			foreach ($headings as $head)
				$screen .= "<th>$head</th>";
			if (user_debug())
				$screen .= "<th $grey>Group ID</th>";
			$screen .= "
		</tr>
		";
		foreach ($datalines as $dline)
		{
			$group = (array_key_exists('CLIENT_GROUP_ID', $dline) ? $dline['CLIENT_GROUP_ID'] : '');
			$screen .= "
			<tr>
				<td>{$dline[0]}</td><td>{$dline[1]}</td><td $ar>{$dline[2]}</td>
				";
				$ii = 3;
				foreach ($months as $month)
				{
					$screen .= "<td $ar>{$dline[$ii]}</td>";
					$ii++;
				}
				if (user_debug())
					$screen .= "<td $grey>$group</td>";
			$screen .= "
			</tr>
			";
		}
		$screen .= "
		</table>
		";
		printD($screen);
	}
	elseif ($dest == 'x')
	{
		$top_lines = array(array($title), array());
		$colm = 'C';
		$formats = array($colm => $excel_integer_format);
		foreach ($months as $month)
		{
			$colm = excel_next_column($colm); #chr(ord($colm)+1);
			$formats[$colm] = $excel_integer_format;
		}
		$sheet = array($title_short, $top_lines, $headings, $datalines, $formats, '');
		phpExcel_output($reps_subdir, $xfile, array($sheet)); # library.php

		# Auto-download file to PC
		printD("
		<script type=\"text/javascript\">
			document.form_csv_download.short_fname.value = '$xfile.$phpExcel_ext';
			document.form_csv_download.full_fname.value = '$csv_path' + '{$reps_subdir}/$xfile.$phpExcel_ext';
			document.form_csv_download.submit();
		</script>
		");
	}
	else
		dlog("$this_function: *=* bad dest \"$dest\"");

} # rep_ts_client_month()

function rep_ts_user_rate($dest, $sc_date_fr, $sc_date_to, $sc_agent, $sc_client, $sc_group, $xfile='')
{
	# Traces/Statistics: User Success Rates
	# $dest is 's' for screen or 'x' for export to Excel file.
	# $sc_date_fr and $sc_date_to are search criteria that form a date range which
	#		job completion dates must fall within; either may be blank.
	# $sc_agent is agent user ID.
	# $sc_client is search criteria: a client code or part of a client name (or an alpha code)
	# $sc_group is search criteria: a client group ID
	# $xfile is the name of the file that the search results should be written to;
	#		it is used when $dest=='x'

	global $ar;
	global $csv_path;
	global $excel_decimal_format;
	global $excel_integer_format;
	global $grey;
	global $ids_JOB_TYPE_retraces;
	global $phpExcel_ext; # settings.php: "xls"
	global $sqlFalse;
	global $sqlTrue;
	global $reps_subdir;

	$this_function = "rep_ts_user_rate(\"$dest\", \"$sc_date_fr\", \"$sc_date_to\", \"$sc_agent\", \"$sc_client\", \"$sc_group\", \"$xfile\")";

	#dprint("Entered: $this_function");#

	$user_id = intval($sc_agent);
	$t_agents = sql_get_agents('t', true, false); # get list of trace agents, e.g. Array ( [13] => Alex Salamone [10] => Del Hudson ... )
	$ag_ids = array();
	foreach ($t_agents as $tid => $tname)
	{
		$ag_ids[] = $tid;
		$t_agents[$tid] = array('NAME' => $tname, 'NEW_COMP' => 0, 'NEW_SUCC' => 0, 'NEW_SPC' => 0.0,
								'RT_COMP' => 0, 'RT_SUCC' => 0, 'RT_FOC' => 0, 'RT_CRED' => 0
								);
	}
	$tname=$tname; # suppress warnings
	#dprint("\$t_agents=" . print_r($t_agents,1) . "<br>\$ag_ids=" . print_r($ag_ids,1));#

	$totals = array('NAME' => 'TOTALS', 'NEW_COMP' => 0, 'NEW_SUCC' => 0, 'NEW_SPC' => 0.0,
					'RT_COMP' => 0, 'RT_SUCC' => 0, 'RT_FOC' => 0, 'RT_CRED' => 0);

	if ($sc_date_fr)
	{
		if ($sc_date_to)
			$date_string = "between $sc_date_fr and $sc_date_to";
		else
			$date_string = "from $sc_date_fr";
	}
	elseif ($sc_date_to)
		$date_string = "up to $sc_date_to";
	else
		$date_string = "(all dates)";

	if ($user_id > 0)
		$user_string = ", User " . user_name_from_id($user_id, true);
	else
		$user_string = '';

	$sc_c_code = intval($sc_client);
	$client_string = '';
	if ($sc_client)
	{
		$client_name = '';
		if ($sc_c_code > 0)
		{
			$client_name = client_name_from_code($sc_c_code);
			if ($client_name)
				$client_name = "$sc_c_code - $client_name";
		}
		if (!$client_name)
			$client_name = $sc_client;
		$client_string .= ", client $client_name";
	}

	$group_id = intval($sc_group);
	if ($group_id > 0)
		$group_string = ", client group \"" . sql_client_group_name_from_id($group_id) . "\"";
	else
		$group_string = '';

	$title_short = "User Success Rates";
	$title = "{$title_short} {$date_string}{$user_string}{$client_string}{$group_string}";
	#dprint($title);#

	$where = array("J.OBSOLETE=$sqlFalse", "J.JT_JOB=$sqlTrue", "J.J_CLOSED_DT IS NOT NULL"); # all trace jobs with a completion date set

	if ($user_id > 0)
		$where[] = "J.J_USER_ID=$user_id";
	else
		$where[] = ("J.J_USER_ID IN (" . implode(',', $ag_ids) . ")");

	$date_field = 'J.J_CLOSED_DT'; # job completion date
	if ($sc_date_fr)
		$where[] = "'" . date_for_sql_nqnt($sc_date_fr) . "' <= $date_field";
	if ($sc_date_to)
		$where[] = "$date_field < '" . date_for_sql_nqnt($sc_date_to, true) . "'"; # NOT "<="

	if ($sc_client)
	{
		$where[] = "C.CLIENT2_ID > 0";
		if ($sc_c_code > 0)
			$where[] = "C.C_CODE = $sc_c_code";
		else
		{
			$sc_client2_id = (($sc_client[0] == '*') ? intval(trim(str_replace('*', '', $sc_client))) : 0);
			if ($sc_client2_id > 0)
				$where[] = "C.CLIENT2_ID = $sc_client2_id";
			else
			{
				sql_encryption_preparation('CLIENT2');
				$sc_client = addslashes_kdb($sc_client);
				$sql = "SELECT CLIENT2_ID FROM CLIENT2 WHERE " . sql_decrypt('C_CO_NAME') . " LIKE '%{$sc_client}%' OR " .
							"(ALPHA_CODE='" . addslashes_kdb($sc_client) . "')";
				#dprint($sql);#
				sql_execute($sql);
				$cids = array();
				while (($newArray = sql_fetch()) != false)
					$cids[] = $newArray[0];
				if ($cids)
					$where[] = "C.CLIENT2_ID IN (" . implode(',', $cids) . ")";
				else
					dprint("sc_client \"$sc_client\" yielded no client_id(s)");
			}
		}
	}
	if ($group_id > 0)
	{
		$where[] = "G.CLIENT_GROUP_ID > 0";
		$where[] = "G.CLIENT_GROUP_ID=$group_id";
		$group_select = ", G.CLIENT_GROUP_ID";
		$group_join = " LEFT JOIN CLIENT_GROUP G ON G.CLIENT_GROUP_ID=C.CLIENT_GROUP_ID ";
	}
	else
	{
		$group_select = '';
		$group_join = '';
	}
	$client_join = "LEFT JOIN CLIENT2 AS C ON C.CLIENT2_ID=J.CLIENT2_ID";

	if ($where)
		$where = "WHERE (" . implode(') AND (', $where) . ")";
	else
		$where = '';

	sql_encryption_preparation('CLIENT2');
	$sql = "
		SELECT J.JOB_ID, J.J_USER_ID, $date_field, J.J_COMPLETE, J.JT_SUCCESS, J.JT_CREDIT,
				J.JT_JOB_TYPE_ID, JT.JT_CODE, C.C_CODE
		FROM JOB AS J
		LEFT JOIN JOB_TYPE_SD AS JT ON JT.JOB_TYPE_ID=J.JT_JOB_TYPE_ID
		$client_join
		$group_join
		$where
		";
	if ($dest == 's') dprint($sql);#
	sql_execute($sql);
	$clients = array();
	while (($newArray = sql_fetch_assoc()) != false)
	{
		$uid = $newArray['J_USER_ID'];
		if (in_array($newArray['JT_JOB_TYPE_ID'], $ids_JOB_TYPE_retraces))
		{
			$t_agents[$uid]['RT_COMP']++;
			$totals['RT_COMP']++;
			if ($newArray['JT_SUCCESS'] == 1)
			{
				$t_agents[$uid]['RT_SUCC']++;
				$totals['RT_SUCC']++;
			}
			elseif ($newArray['JT_CREDIT'] == 1)
			{
				$t_agents[$uid]['RT_CRED']++;
				$totals['RT_CRED']++;
			}
			elseif (($newArray['JT_SUCCESS'] == -1) || ($newArray['JT_CREDIT'] == -1))
			{
				$t_agents[$uid]['RT_FOC']++;
				$totals['RT_FOC']++;
			}
		}
		else
		{
			$t_agents[$uid]['NEW_COMP']++;
			$totals['NEW_COMP']++;
			if ($newArray['JT_SUCCESS'] == 1)
			{
				$t_agents[$uid]['NEW_SUCC']++;
				$totals['NEW_SUCC']++;
			}
		}
		if (!in_array($newArray['C_CODE'], $clients))
			$clients[] = $newArray['C_CODE'];
	}
	#dprint("\$t_agents=" . print_r($t_agents,1) . "<br>clients=" . print_r($clients,1));#

	$headings = array(  '', 'New Jobs',  '',           '',          'Retraces',  '',           '',       ''      );
	$second_h_s = array('', 'Completed', 'Successful', 'Success %', 'Completed', 'Successful', 'F.O.C.', 'Credit'); # for screen
	$second_h_x = array(0 => array('NAME' => '', 'NEW_COMP' => 'Completed', 'NEW_SUCC' => 'Successful', 'NEW_SPC' => 'Success %',
								'RT_COMP' => 'Completed', 'RT_SUCC' => 'Successful', 'RT_FOC' => 'F.O.C.', 'RT_CRED' => 'Credit')); # for export

	$datalines = array();
	foreach ($t_agents as $agid => $info)
	{
		$info['NEW_SPC'] = ($info['NEW_COMP'] ? round(100.0 * floatval($info['NEW_SUCC']) / $info['NEW_COMP'], 2) : 0.0);
		$datalines[$agid] = $info;
	}
	$totals['NEW_SPC'] = ($totals['NEW_COMP'] ? round(100.0 * floatval($totals['NEW_SUCC']) / $totals['NEW_COMP'], 2) : 0.0);
	$datalines[-1] = $totals;
	#dprint("datalines=" . print_r($datalines,1));#

	if ($dest == 's')
	{
		$screen = "
		<p style=\"font-size:18px; font-weight:bold;\">
			$title
			&nbsp;&nbsp;&nbsp;&nbsp;
			" . input_button('Export to Excel', "export_xl()", '', 'but_export_xl') . "
		</p>

		<table class=\"spaced_table\">
		<tr>
			";
			foreach ($headings as $head)
				$screen .= "<th>$head</th>";
			if (user_debug())
				$screen .= "<th $grey>Group ID</th>";
			$screen .= "
		</tr>
		<tr>
			";
			foreach ($second_h_s as $head)
				$screen .= "<th>$head</th>";
			if (user_debug())
				$screen .= "<th></th>";
			$screen .= "
		</tr>
		";
		foreach ($datalines as $dline)
		{
			$group = (array_key_exists('CLIENT_GROUP_ID', $dline) ? $dline['CLIENT_GROUP_ID'] : '');

			$screen .= "
			<tr>
			<td>{$dline['NAME']}</td><td $ar>{$dline['NEW_COMP']}</td><td $ar>{$dline['NEW_SUCC']}</td><td $ar>{$dline['NEW_SPC']}</td>
				<td $ar>{$dline['RT_COMP']}</td><td $ar>{$dline['RT_SUCC']}</td><td $ar>{$dline['RT_FOC']}</td><td $ar>{$dline['RT_CRED']}</td>
			" . (user_debug() ? "<td $grey>$group</td>" : '') . "
			</tr>
			";
		}
		$screen .= "
		</table>
		";
		printD($screen);
	}
	elseif ($dest == 'x')
	{
		$top_lines = array(array($title), array());
		$formats = array('B' => $excel_integer_format, 'C' => $excel_integer_format, 'D' => $excel_decimal_format,
				'E' => $excel_integer_format, 'F' => $excel_integer_format, 'G' => $excel_integer_format, 'H' => $excel_integer_format);
		$datalines = array_merge($second_h_x, $datalines);
		#dprint("datalines/x=" . print_r($datalines,1));#
		$sheet = array($title_short, $top_lines, $headings, $datalines, $formats, '');
		phpExcel_output($reps_subdir, $xfile, array($sheet)); # library.php

		# Auto-download file to PC
		printD("
		<script type=\"text/javascript\">
			document.form_csv_download.short_fname.value = '$xfile.$phpExcel_ext';
			document.form_csv_download.full_fname.value = '$csv_path' + '{$reps_subdir}/$xfile.$phpExcel_ext';
			document.form_csv_download.submit();
		</script>
		");
	}
	else
		dlog("$this_function: *=* bad dest \"$dest\"");
} # rep_ts_user_rate()

function rep_ts_user_invoices($dest, $sc_date_fr, $sc_date_to, $sc_agent, $sc_client, $sc_group, $xfile='')
{
	# Traces/Statistics: Monthly user invoice amounts (support ticket #1080 21/05/21).
	# $dest is 's' for screen or 'x' for export to Excel file.
	# $sc_date_fr and $sc_date_to are search criteria that form a date range which
	#		job completion dates must fall within; either may be blank.
	# $sc_agent is agent user ID.
	# $sc_client is search criteria: a client code or part of a client name (or an alpha code)
	# $sc_group is search criteria: a client group ID
	# $xfile is the name of the file that the search results should be written to;
	#		it is used when $dest=='x'

	global $ar;
	global $csv_path;
	global $excel_currency_format;
	global $grey;
	global $phpExcel_ext; # settings.php: "xls"
	global $sqlFalse;
	global $sqlTrue;
	global $reps_subdir;

	$this_function = "rep_ts_user_invoices(\"$dest\", \"$sc_date_fr\", \"$sc_date_to\", \"$sc_agent\", \"$sc_client\", \"$sc_group\", \"$xfile\")";

	#dprint("Entered: $this_function");#

	# Set up monthly array using $sc_date_fr and $sc_date_to
	if (!$sc_date_fr)
		$sc_date_fr = '01/01/2015';
	if (!$sc_date_to)
		$sc_date_to = date_now();
	$sc_date_fr_sql = date_for_sql($sc_date_fr); # 'YYYY-MM-DD 00:00:00' including quotes!
	$date_fr_01 = substr($sc_date_fr_sql, 0, 9) # e.g. "'2021-05-" from "'2021-05-21 00:00:00'"
					. "01" # first day of month
					. " 00:00:00'";
	$sc_date_to_sql = date_for_sql($sc_date_to);
	$date_to_01 = substr($sc_date_to_sql, 0, 9) # e.g. "'2021-05-" from "'2021-05-21 00:00:00'"
					. "01" # first day of month
					. " 00:00:00'";
	$sc_date_to_p1_sql = date_for_sql($sc_date_to, false, true, false, false, true);
	$months = months_range($date_fr_01, $date_to_01, false);
	#dprint("[$sc_date_fr_sql][$sc_date_to_sql][$sc_date_to_p1_sql] ... " . print_r($months,1));#
	
	$user_id = intval($sc_agent);
	$t_agents = sql_get_agents('t', true, false); # get list of trace agents, e.g. Array ( [13] => Alex Salamone [10] => Del Hudson ... )
	$ag_ids = array();
	foreach ($t_agents as $tid => $tname)
	{
		$ag_ids[] = $tid;
		$t_agents[$tid] = array('NAME' => $tname);
		foreach ($months as $mnth)
			$t_agents[$tid][$mnth] = 0.0;
	}
	$tname=$tname; # suppress warnings
	#dprint("\$t_agents=" . print_r($t_agents,1) . "<br>\$ag_ids=" . print_r($ag_ids,1));#

	$totals = array('NAME' => 'TOTALS');
	foreach ($months as $mnth)
		$totals[$mnth] = 0.0;

	#if ($sc_date_fr)
	#{
	#	if ($sc_date_to)
			$date_string = "between $sc_date_fr and $sc_date_to";
	#	else
	#		$date_string = "from $sc_date_fr";
	#}
	#elseif ($sc_date_to)
	#	$date_string = "up to $sc_date_to";
	#else
	#	$date_string = "(all dates)";

	if ($user_id > 0)
		$user_string = ", User " . user_name_from_id($user_id, true);
	else
		$user_string = '';

	$sc_c_code = intval($sc_client);
	$client_string = '';
	if ($sc_client)
	{
		$client_name = '';
		if ($sc_c_code > 0)
		{
			$client_name = client_name_from_code($sc_c_code);
			if ($client_name)
				$client_name = "$sc_c_code - $client_name";
		}
		if (!$client_name)
			$client_name = $sc_client;
		$client_string .= ", client $client_name";
	}

	$group_id = intval($sc_group);
	if ($group_id > 0)
		$group_string = ", client group \"" . sql_client_group_name_from_id($group_id) . "\"";
	else
		$group_string = '';

	$title_short = "Monthly User Invoice Amounts (ex-VAT)";
	$title = "{$title_short} {$date_string}{$user_string}{$client_string}{$group_string}";
	#dprint($title);#

	$where = array("J.OBSOLETE=$sqlFalse", "J.JT_JOB=$sqlTrue", "J.J_CLOSED_DT IS NOT NULL", # all trace jobs with a completion date set
					"J.J_COMPLETE=1");

	if ($user_id > 0)
		$where[] = "J.J_USER_ID=$user_id";
	else
		$where[] = ("J.J_USER_ID IN (" . implode(',', $ag_ids) . ")");

	$date_field = 'J.J_CLOSED_DT'; # job completion date
	#if ($sc_date_fr)
		$where[] = "$sc_date_fr_sql <= $date_field";
	#if ($sc_date_to)
		$where[] = "$date_field < $sc_date_to_p1_sql"; # NOT "<="

	if ($sc_client)
	{
		$where[] = "C.CLIENT2_ID > 0";
		if ($sc_c_code > 0)
			$where[] = "C.C_CODE = $sc_c_code";
		else
		{
			$sc_client2_id = (($sc_client[0] == '*') ? intval(trim(str_replace('*', '', $sc_client))) : 0);
			if ($sc_client2_id > 0)
				$where[] = "C.CLIENT2_ID = $sc_client2_id";
			else
			{
				sql_encryption_preparation('CLIENT2');
				$sc_client = addslashes_kdb($sc_client);
				$sql = "SELECT CLIENT2_ID FROM CLIENT2 WHERE " . sql_decrypt('C_CO_NAME') . " LIKE '%{$sc_client}%' OR " .
							"(ALPHA_CODE='" . addslashes_kdb($sc_client) . "')";
				#dprint($sql);#
				sql_execute($sql);
				$cids = array();
				while (($newArray = sql_fetch()) != false)
					$cids[] = $newArray[0];
				if ($cids)
					$where[] = "C.CLIENT2_ID IN (" . implode(',', $cids) . ")";
				else
					dprint("sc_client \"$sc_client\" yielded no client_id(s)");
			}
		}
	}
	if ($group_id > 0)
	{
		$where[] = "G.CLIENT_GROUP_ID > 0";
		$where[] = "G.CLIENT_GROUP_ID=$group_id";
		$group_select = ", G.CLIENT_GROUP_ID";
		$group_join = " LEFT JOIN CLIENT_GROUP G ON G.CLIENT_GROUP_ID=C.CLIENT_GROUP_ID ";
	}
	else
	{
		$group_select = '';
		$group_join = '';
	}
	$client_join = "LEFT JOIN CLIENT2 AS C ON C.CLIENT2_ID=J.CLIENT2_ID";

	if ($where)
		$where = "WHERE (" . implode(') AND (', $where) . ")";
	else
		$where = '';

	sql_encryption_preparation('CLIENT2');
	$sql = "
		SELECT J.JOB_ID, J.J_USER_ID, $date_field, J.JT_CREDIT, J.J_CLOSED_DT, IB.BL_COST
		FROM JOB AS J
		LEFT JOIN JOB_TYPE_SD AS JT ON JT.JOB_TYPE_ID=J.JT_JOB_TYPE_ID
		LEFT JOIN INV_BILLING AS IB ON IB.JOB_ID=J.JOB_ID
		$client_join
		$group_join
		$where
		";
	if ($dest == 's') dprint($sql);#
	sql_execute($sql);
	while (($newArray = sql_fetch_assoc()) != false)
	{
		$uid = $newArray['J_USER_ID'];
		# Get the month of the job closure, but use 1st day of month
		$mnth = substr($newArray['J_CLOSED_DT'], 0, 8) # e.g. '2021-05-' from '2021-05-21 10:41:59'
					. '01'; # first day of month
		$t_agents[$uid][$mnth] += floatval($newArray['BL_COST']);
		$totals[$mnth] += floatval($newArray['BL_COST']);
	}
	#dprint("\$t_agents=" . print_r($t_agents,1));#

	$headings_s = array(''); # for screen
	$headings_x = array(0 => array('NAME' => '')); # for export
	foreach ($months as $mnth)
	{
		$headings_s[$mnth] = date_pretty_month_and_year($mnth);
		$headings_x[0][$mnth] = date_pretty_month_and_year($mnth);
	}

	$datalines = array();
	foreach ($t_agents as $agid => $info)
	{
		$datalines[$agid] = $info;
	}
	$datalines[-1] = $totals;
	#dprint("datalines=" . print_r($datalines,1));#

	if ($dest == 's')
	{
		$screen = "
		<p style=\"font-size:18px; font-weight:bold;\">
			$title
			&nbsp;&nbsp;&nbsp;&nbsp;
			" . input_button('Export to Excel', "export_xl()", '', 'but_export_xl') . "
		</p>

		<table class=\"spaced_table\">
		<tr>
			";
			foreach ($headings_s as $head)
				$screen .= "<th>$head</th>";
			if (user_debug())
				$screen .= "<th $grey>Group ID</th>";
			$screen .= "
		</tr>
		";
		foreach ($datalines as $dline)
		{
			$group = (array_key_exists('CLIENT_GROUP_ID', $dline) ? $dline['CLIENT_GROUP_ID'] : '');

			$screen .= "
			<tr>
				<td>{$dline['NAME']}</td>
				";
				foreach ($months as $mnth)
					$screen .= ("<td $ar>" . money_format_kdb($dline[$mnth], true, true, true) . "</td>");
				$screen .= "
				" . (user_debug() ? "<td $grey>$group</td>" : '') . "
			</tr>
			";
		}
		$screen .= "
		</table>
		";
		printD($screen);
	}
	elseif ($dest == 'x')
	{
		$top_lines = array(array($title), array());
		$formats = array('B' => $excel_currency_format);
		#dprint("datalines/x=" . print_r($datalines,1));#
		$datalines = array_merge($headings_x, $datalines);
		$sheet = array($title_short, $top_lines, array(), $datalines, $formats, '');
		phpExcel_output($reps_subdir, $xfile, array($sheet)); # library.php

		# Auto-download file to PC
		printD("
		<script type=\"text/javascript\">
			document.form_csv_download.short_fname.value = '$xfile.$phpExcel_ext';
			document.form_csv_download.full_fname.value = '$csv_path' + '{$reps_subdir}/$xfile.$phpExcel_ext';
			document.form_csv_download.submit();
		</script>
		");
	}
	else
		dlog("$this_function: *=* bad dest \"$dest\"");
} # rep_ts_user_invoices()

function rep_ts_success_user($dest, $sc_date_fr, $sc_date_to, $sc_agent, $sc_client, $sc_group, $xfile='')
{
	# Traces/Statistics: Successful Jobs for Single User
	# $dest is 's' for screen or 'x' for export to Excel file.
	# $sc_date_fr and $sc_date_to are search criteria that form a date range which
	#		job completion dates must fall within; either may be blank.
	# $sc_agent is mandatory agent user ID.
	# $sc_client is search criteria: a client code or part of a client name (or an alpha code)
	# $sc_group is search criteria: a client group ID
	# $xfile is the name of the file that the search results should be written to;
	#		it is used when $dest=='x'

	global $ar;
	global $csv_path;
	global $excel_integer_format;
	global $grey;
	global $ids_JOB_TYPE_retraces;
	global $phpExcel_ext; # settings.php: "xls"
	global $sqlFalse;
	global $sqlTrue;
	global $reps_subdir;

	$this_function = "rep_ts_success_user(\"$dest\", \"$sc_date_fr\", \"$sc_date_to\", \"$sc_agent\", \"$sc_client\", \"$sc_group\", \"$xfile\")";

	#dprint("Entered: $this_function");#

	$user_id = intval($sc_agent);
	if ( ! (0 < $user_id) )
	{
		dprint("Agent not specified", true);
		return;
	}
	$user_name = user_name_from_id($user_id, true);

	if ($sc_date_fr)
	{
		if ($sc_date_to)
			$date_string = "between $sc_date_fr and $sc_date_to";
		else
			$date_string = "from $sc_date_fr";
	}
	elseif ($sc_date_to)
		$date_string = "up to $sc_date_to";
	else
		$date_string = "(all dates)";

	if ($user_id > 0)
		$user_string = ", User $user_name";
	else
		$user_string = '';

	$sc_c_code = intval($sc_client);
	$client_string = '';
	if ($sc_client)
	{
		$client_name = '';
		if ($sc_c_code > 0)
		{
			$client_name = client_name_from_code($sc_c_code);
			if ($client_name)
				$client_name = "$sc_c_code - $client_name";
		}
		if (!$client_name)
			$client_name = $sc_client;
		$client_string .= ", client $client_name";
	}

	$group_id = intval($sc_group);
	if ($group_id > 0)
		$group_string = ", client group \"" . sql_client_group_name_from_id($group_id) . "\"";
	else
		$group_string = '';

	$title_short = "Successful Jobs for Single User";
	$title = "{$title_short} {$date_string}{$user_string}{$client_string}{$group_string}";
	#dprint($title);#

	# We want all trace jobs that are closed as successful or FOC or Credit, last worked on by selected agent
	$where = array("J.OBSOLETE=$sqlFalse", "J.JT_JOB=$sqlTrue", "J.J_CLOSED_DT IS NOT NULL", "J.JT_SUCCESS=1 OR J.JT_SUCCESS=-1 OR J.JT_CREDIT=1 OR J.JT_CREDIT=-1",
				"J.J_USER_ID=$user_id");

	$date_field = 'J.J_CLOSED_DT'; # job completion date
	if ($sc_date_fr)
		$where[] = "'" . date_for_sql_nqnt($sc_date_fr) . "' <= $date_field";
	if ($sc_date_to)
		$where[] = "$date_field < '" . date_for_sql_nqnt($sc_date_to, true) . "'"; # NOT "<="

	if ($sc_client)
	{
		$where[] = "C.CLIENT2_ID > 0";
		if ($sc_c_code > 0)
			$where[] = "C.C_CODE = $sc_c_code";
		else
		{
			$sc_client2_id = (($sc_client[0] == '*') ? intval(trim(str_replace('*', '', $sc_client))) : 0);
			if ($sc_client2_id > 0)
				$where[] = "C.CLIENT2_ID = $sc_client2_id";
			else
			{
				sql_encryption_preparation('CLIENT2');
				$sc_client = addslashes_kdb($sc_client);
				$sql = "SELECT CLIENT2_ID FROM CLIENT2 WHERE " . sql_decrypt('C_CO_NAME') . " LIKE '%{$sc_client}%' OR " .
							"(ALPHA_CODE='" . addslashes_kdb($sc_client) . "')";
				#dprint($sql);#
				sql_execute($sql);
				$cids = array();
				while (($newArray = sql_fetch()) != false)
					$cids[] = $newArray[0];
				if ($cids)
					$where[] = "C.CLIENT2_ID IN (" . implode(',', $cids) . ")";
				else
					dprint("sc_client \"$sc_client\" yielded no client_id(s)");
			}
		}
	}
	if ($group_id > 0)
	{
		$where[] = "G.CLIENT_GROUP_ID > 0";
		$where[] = "G.CLIENT_GROUP_ID=$group_id";
		$group_select = ", G.CLIENT_GROUP_ID";
		$group_join = " LEFT JOIN CLIENT_GROUP G ON G.CLIENT_GROUP_ID=C.CLIENT_GROUP_ID ";
	}
	else
	{
		$group_select = '';
		$group_join = '';
	}
	$client_join = "LEFT JOIN CLIENT2 AS C ON C.CLIENT2_ID=J.CLIENT2_ID";

	if ($where)
		$where = "WHERE (" . implode(') AND (', $where) . ")";
	else
		$where = '';

	sql_encryption_preparation('CLIENT2');
	$sql = "
		SELECT J.JOB_ID, $date_field, J.JT_SUCCESS, J.J_COMPLETE, J.JT_CREDIT, J.JT_JOB_TYPE_ID,
				JT.JT_CODE, C.CLIENT2_ID
		FROM JOB AS J
		LEFT JOIN JOB_TYPE_SD AS JT ON JT.JOB_TYPE_ID=J.JT_JOB_TYPE_ID
		$client_join
		$group_join
		$where
		ORDER BY CLIENT2_ID
		";
	if ($dest == 's') dprint($sql);#
	sql_execute($sql);

	$datalines = array();
	$cids = array();
	$totals = array('C_CODE' => '', 'C_CO_NAME' => 'TOTALS',
				'NEW' => 0, 'RT' => 0, 'FOC' => 0, 'CREDIT' => 0);
	while (($newArray = sql_fetch_assoc()) != false)
	{
		$cid = $newArray['CLIENT2_ID'];
		if (!array_key_exists($cid, $datalines))
		{
			$datalines[$cid] = array('C_CODE' => '', 'C_CO_NAME' => '',
				'NEW' => 0, 'RT' => 0, 'FOC' => 0, 'CREDIT' => 0);
			$cids[] = $cid;
		}
		if (in_array($newArray['JT_JOB_TYPE_ID'], $ids_JOB_TYPE_retraces))
		{
			$datalines[$cid]['RT']++;
			$totals['RT']++;
		}
		else
		{
			$datalines[$cid]['NEW']++;
			$totals['NEW']++;
		}
		if (($newArray['JT_SUCCESS'] == -1) || ($newArray['JT_CREDIT'] == -1))
		{
			$datalines[$cid]['FOC']++;
			$totals['FOC']++;
		}
		if ($newArray['JT_CREDIT'] == 1)
		{
			$datalines[$cid]['CREDIT']++;
			$totals['CREDIT']++;
		}
	}
	if ($cids)
	{
		$sql = "SELECT CLIENT2_ID, C_CODE, " . sql_decrypt('C_CO_NAME') . " FROM CLIENT2
				WHERE CLIENT2_ID IN (" . implode(',', $cids) . ")";
		if ($dest == 's') dprint($sql);#
		sql_encryption_preparation('CLIENT2');
		sql_execute($sql);
		while (($newArray = sql_fetch()) != false)
		{
			$datalines[$newArray[0]]['C_CODE'] = $newArray[1];
			$datalines[$newArray[0]]['C_CO_NAME'] = $newArray[2];
		}
	}
	$datalines[-1] = $totals;
	#dprint("\$datalines=" . print_r($datalines,1));#

	$headings = array('CLIENT', 'CLIENT NAME', 'NEW JOBS', 'RETRACES', 'F.O.C.', 'CREDITS');

	if ($dest == 's')
	{
		$screen = "
		<p style=\"font-size:18px; font-weight:bold;\">
			$title
			&nbsp;&nbsp;&nbsp;&nbsp;
			" . input_button('Export to Excel', "export_xl()", '', 'but_export_xl') . "
		</p>

		<table class=\"spaced_table\">
		<tr>
			";
			foreach ($headings as $head)
				$screen .= "<th>$head</th>";
			if (user_debug())
				$screen .= "<th $grey>Group ID</th>";
			$screen .= "
		</tr>
		";
		foreach ($datalines as $dline)
		{
			$group = (array_key_exists('CLIENT_GROUP_ID', $dline) ? $dline['CLIENT_GROUP_ID'] : '');

			$screen .= "
			<tr>
			<td>{$dline['C_CODE']}</td><td>{$dline['C_CO_NAME']}</td>
				<td $ar>{$dline['NEW']}</td><td $ar>{$dline['RT']}</td><td $ar>{$dline['FOC']}</td><td $ar>{$dline['CREDIT']}</td>
			" . (user_debug() ? "<td $grey>$group</td>" : '') . "
			</tr>
			";
		}
		$screen .= "
		</table>
		";
		printD($screen);
	}
	elseif ($dest == 'x')
	{
		$top_lines = array(array($title), array());
		$formats = array('C' => $excel_integer_format, 'E' => $excel_integer_format,
							'E' => $excel_integer_format, 'F' => $excel_integer_format);
		$sheet = array($title_short, $top_lines, $headings, $datalines, $formats, '');
		phpExcel_output($reps_subdir, $xfile, array($sheet)); # library.php

		# Auto-download file to PC
		printD("
		<script type=\"text/javascript\">
			document.form_csv_download.short_fname.value = '$xfile.$phpExcel_ext';
			document.form_csv_download.full_fname.value = '$csv_path' + '{$reps_subdir}/$xfile.$phpExcel_ext';
			document.form_csv_download.submit();
		</script>
		");
	}
	else
		dlog("$this_function: *=* bad dest \"$dest\"");
} # rep_ts_success_user()

function rep_cf_single_client($dest, $sc_date_fr, $sc_date_to, $sc_dtcol_fr, $sc_dtcol_to, $sc_client, $sc_group, $sc_rt_tv, $sc_rt_fw, $sc_rt_di, $xfile='')
{
	# Collections Report: Single Client Payment Listing
	# $dest is 's' for screen or 'x' for export to Excel file.
	# $sc_date_fr and $sc_date_to are search criteria that form a date range which
	#		job placement dates must fall within; either may be blank.
	# $sc_dtcol_fr and $sc_dtcol_to are search criteria that form a date range which
	#		collection payment dates must fall within; either may be blank.
	# $sc_client is search criteria: a client code or part of a client name (or an alpha code)
	# $sc_group is search criteria: a client group ID
	# $sc_rt_tv, $sc_rt_fw and $sc_rt_di are payment route types, each is boolean (1 or 0)
	# $xfile is the name of the file that the search results should be written to;
	#		it is used when $dest=='x'

	global $ar;
	global $csv_path;
	global $excel_currency_format;
	global $excel_date_format;
	global $grey;
	global $id_ROUTE_direct;
	global $id_ROUTE_fwd;
	global $id_ROUTE_tous;
	global $PAYMENT_ROUTES; # init_data()
	global $phpExcel_ext; # settings.php: "xls"
	global $sqlFalse;
	global $sqlTrue;
	global $reps_subdir;

	$this_function = "rep_cf_single_client(\"$dest\", \"$sc_date_fr\", \"$sc_date_to\", \"$sc_dtcol_fr\", \"$sc_dtcol_to\", \"$sc_client\", \"$sc_group\", \"$sc_rt_tv\", \"$sc_rt_fw\", \"$sc_rt_di\", \"$xfile\")";
	#dprint("Entered: $this_function");#

	if ($sc_date_fr)
	{
		if ($sc_date_to)
			$date_string = ", jobs placed between $sc_date_fr and $sc_date_to";
		else
			$date_string = ", jobs placed from $sc_date_fr";
	}
	elseif ($sc_date_to)
		$date_string = ", jobs placed up to $sc_date_to";
	else
		$date_string = "(all placement dates)";

	if ($sc_dtcol_fr)
	{
		if ($sc_dtcol_to)
			$date2_string = ", paid between $sc_dtcol_fr and $sc_dtcol_to";
		else
			$date2_string = ", paid from $sc_dtcol_fr";
	}
	elseif ($sc_dtcol_to)
		$date2_string = ", paid up to $sc_dtcol_to";
	else
		$date2_string = "(all payment dates)";

	$sc_c_code = intval($sc_client);
	$c_code_list = array();
	$temp = str_replace(',', '', str_replace(' ', '', $sc_client));
	if (is_numeric_kdb($temp, false, false, false))
		$c_code_list = explode(',', str_replace(' ', '', $sc_client));
	$client_string = '';
	if ($sc_client)
	{
		$client_name = '';
		if ($sc_c_code > 0)
		{
			$client_name = client_name_from_code($sc_c_code);
			if ($client_name)
				$client_name = "$sc_c_code - $client_name";
		}
		if (!$client_name)
			$client_name = $sc_client;
		$client_string .= ", client $client_name";
	}

	$group_id = intval($sc_group);
	if ($group_id > 0)
		$group_string = ", client group \"" . sql_client_group_name_from_id($group_id) . "\"";
	else
		$group_string = '';

	$route_string = array();
	if ($sc_rt_tv)
		$route_string[] = "To Vilcol";
	if ($sc_rt_fw)
		$route_string[] = "Forwarded";
	if ($sc_rt_di)
		$route_string[] = "Direct";
	if (count($route_string) == 1)
		$route_string = ", route: {$route_string[0]}";
	elseif (1 < count($route_string))
		$route_string = ", routes: " . implode(', ', $route_string);
	else
		$route_string = ", (no routes)";

	$title_short = "Single Client Payment Listing";
	$title = "{$title_short}{$date_string}{$date2_string}{$client_string}{$group_string}{$route_string}";
	#dprint($title);#

	$where = array("J.OBSOLETE=$sqlFalse", "J.JC_JOB=$sqlTrue");

	$date_j_field = 'J.J_OPENED_DT'; # job placement date
	$date_c_field = 'P.COL_DT_RX'; # collection (payment) date
	if ($sc_date_fr) # placement
		$where[] = "'" . date_for_sql_nqnt($sc_date_fr) . "' <= $date_j_field";
	if ($sc_date_to) # placement
		$where[] = "$date_j_field < '" . date_for_sql_nqnt($sc_date_to, true) . "'"; # NOT "<="
	if ($sc_dtcol_fr) # collection
		$where[] = "'" . date_for_sql_nqnt($sc_dtcol_fr) . "' <= $date_c_field";
	if ($sc_dtcol_to) # collection
		$where[] = "$date_c_field < '" . date_for_sql_nqnt($sc_dtcol_to, true) . "'"; # NOT "<="

	if ($sc_client)
	{
		$where[] = "C.CLIENT2_ID > 0";
		if (count($c_code_list) > 1)
			$where[] = "C.C_CODE IN (" . implode(',', $c_code_list) . ")";
		elseif ($sc_c_code > 0)
			$where[] = "C.C_CODE = $sc_c_code";
		else
		{
			$sc_client2_id = (($sc_client[0] == '*') ? intval(trim(str_replace('*', '', $sc_client))) : 0);
			if ($sc_client2_id > 0)
				$where[] = "C.CLIENT2_ID = $sc_client2_id";
			else
			{
				sql_encryption_preparation('CLIENT2');
				$sc_client = addslashes_kdb($sc_client);
				$sql = "SELECT CLIENT2_ID FROM CLIENT2 WHERE " . sql_decrypt('C_CO_NAME') . " LIKE '%{$sc_client}%' OR " .
							"(ALPHA_CODE='" . addslashes_kdb($sc_client) . "')";
				#dprint($sql);#
				sql_execute($sql);
				$cids = array();
				while (($newArray = sql_fetch()) != false)
					$cids[] = $newArray[0];
				if ($cids)
					$where[] = "C.CLIENT2_ID IN (" . implode(',', $cids) . ")";
				else
					dprint("sc_client \"$sc_client\" yielded no client_id(s)");
			}
		}
	}
	if ($group_id > 0)
	{
		$where[] = "G.CLIENT_GROUP_ID > 0";
		$where[] = "G.CLIENT_GROUP_ID=$group_id";
		$group_select = ", G.CLIENT_GROUP_ID";
		$group_join = " LEFT JOIN CLIENT_GROUP G ON G.CLIENT_GROUP_ID=C.CLIENT_GROUP_ID ";
	}
	else
	{
		$group_select = '';
		$group_join = '';
	}

	$sub_where = array();
	if ($sc_rt_tv)
		$sub_where[] = "P.COL_PAYMENT_ROUTE_ID=$id_ROUTE_tous";
	if ($sc_rt_fw)
		$sub_where[] = "P.COL_PAYMENT_ROUTE_ID=$id_ROUTE_fwd";
	if ($sc_rt_di)
		$sub_where[] = "P.COL_PAYMENT_ROUTE_ID=$id_ROUTE_direct";
	if ($sub_where)
		$where[] = "(" . implode(') OR (', $sub_where) . ")";
	else
	{
		dprint("No Payment Routes are ticked. Search aborted.", true);
		return;
	}
	$where[] = "P.OBSOLETE=$sqlFalse";

	if ($where)
		$where = "WHERE (" . implode(') AND (', $where) . ")";
	else
		$where = '';

	$sql = "
		SELECT P.JOB_PAYMENT_ID, C.CLIENT2_ID, C.C_CODE, J.JOB_ID,
			P.COL_PAYMENT_ROUTE_ID, P.COL_AMT_RX, P.COL_DT_RX, P.COL_PAYMENT_METHOD_ID $group_select
		FROM JOB_PAYMENT AS P
		LEFT JOIN JOB AS J ON J.JOB_ID=P.JOB_ID
		LEFT JOIN CLIENT2 AS C ON C.CLIENT2_ID=J.CLIENT2_ID
		$group_join
		$where
		ORDER BY C.C_CODE, J.JOB_ID, P.COL_DT_RX
		";
	if ($dest == 's') dprint($sql);#
	sql_execute($sql);
	$lines = array(); # payments
	$clients = array();
	$cids = array();
	$jobs = array();
	while (($newArray = sql_fetch_assoc()) != false)
	{
		$lines[] = $newArray;
		if (!in_array($newArray['CLIENT2_ID'], $cids))
		{
			$cids[] = $newArray['CLIENT2_ID'];
			$clients[$newArray['CLIENT2_ID']] = '';
		}
		if (!array_key_exists($newArray['JOB_ID'], $jobs))
			$jobs[$newArray['JOB_ID']] = '';
	}
	#dprint("lines=" . print_r($lines,1) . "<br>clients/1=" . print_r($clients,1) . "<br>jobs/1=" . print_r($jobs,1));#

	if (count($lines) == 0)
	{
		dprint("No Payments were found with the given criteria.", true);
		return;
	}

	sql_encryption_preparation('CLIENT2');
	$sql = "SELECT CLIENT2_ID, C_CODE, " . sql_decrypt('C_CO_NAME', '', true) . " FROM CLIENT2
			WHERE CLIENT2_ID IN (" . implode(',', $cids) . ")";
	#if ($dest == 's') dprint($sql);#
	sql_execute($sql);
	$clients = array();
	$the_client = '';
	$the_code = '';
	while (($newArray = sql_fetch_assoc()) != false)
	{
		if (!$the_client)
		{
			$the_client = $newArray['C_CO_NAME'];
			$the_code = $newArray['C_CODE'];
		}
		$clients[$newArray['CLIENT2_ID']] = array('C_CODE' => $newArray['C_CODE'], 'C_CO_NAME' => $newArray['C_CO_NAME']);
	}
	if (!$the_client)
	{
		$the_client = '(none)';
		$the_code = '-';
	}
	#dprint("theClient=\"$the_client\", the_code=\"$the_code\", clients/2=" . print_r($clients,1));#

	sql_encryption_preparation('JOB');
	sql_encryption_preparation('JOB_SUBJECT');
	foreach ($jobs as $job_id => $blank)
	{
		list($ms_top, $my_limit) = sql_top_limit(1);
		$sql = "SELECT $ms_top S.JS_TITLE, " . sql_decrypt('S.JS_FIRSTNAME') . ", " . sql_decrypt('S.JS_LASTNAME') . ",
				" . sql_decrypt('J.CLIENT_REF', '', true) . "
				FROM JOB_SUBJECT AS S
				INNER JOIN JOB AS J ON J.JOB_ID=S.JOB_ID
				WHERE J.JOB_ID=$job_id ORDER BY S.JOB_SUBJECT_ID $my_limit";
		#if ($dest == 's') dprint($sql);#
		sql_execute($sql);
		while (($newArray = sql_fetch()) != false)
		{
			$temp = $newArray[0];
			if ($newArray[1])
				$temp .= ($temp ? ' ' : '') . $newArray[1];
			if ($newArray[2])
				$temp .= ($temp ? ' ' : '') . $newArray[2];
			$jobs[$job_id] = array('SUBJECT' => $temp, 'CLIENT_REF' => $newArray[3]);
		}
		$blank=$blank; # keep code-checker quiet
	}
	#dprint("jobs/2=" . print_r($jobs,1));#

	$payment_methods = sql_get_payment_methods(true);
	$datalines = array();
	foreach ($lines as $line)
	{
		$pid = intval($line['JOB_PAYMENT_ID']);
		$cid = intval($line['CLIENT2_ID']);
		$jid = intval($line['JOB_ID']);
		$mid = intval($line['COL_PAYMENT_METHOD_ID']);
		$rid = intval($line['COL_PAYMENT_ROUTE_ID']);
		$datalines[$pid] = array();
		if (1 < count($cids))
		{
			$datalines[$pid]['C_CODE'] = $clients[$cid]['C_CODE'];
			$datalines[$pid]['C_CO_NAME'] = $clients[$cid]['C_CO_NAME'];
		}
		$datalines[$pid]['CLIENT_REF'] = $jobs[$jid]['CLIENT_REF'];
		$datalines[$pid]['SUBJECT'] = $jobs[$jid]['SUBJECT'];
		$datalines[$pid]['DATE'] = date_for_sql($line['COL_DT_RX'], true, false);
		#dprint("mid=$mid, pm/mid={$payment_methods[$mid]}");#
		$datalines[$pid]['PAY_METH'] = ($mid ? $payment_methods[$mid]['PAYMENT_METHOD'] : '');
		$datalines[$pid]['ROUTE'] = ($rid ? $PAYMENT_ROUTES[$rid] : '');
		$datalines[$pid]['AMOUNT'] = round($line['COL_AMT_RX'],2);
	}
	#dprint("datalines=" . print_r($datalines,1));#

	$top_lines = array(
		array('Vilcol Payments Report'),
		array("Client: $the_client", "Client Code: $the_code"),
		array("Payment Date Range:", "$sc_dtcol_fr - $sc_dtcol_to"),
		array("Report Date: " . date_now(true, '', false))
		);

	$headings = array();
	if (1 < count($cids))
	{
		$headings[] = 'Client Code';
		$headings[] = 'Client Name';
	}
	$headings[] = 'Client Ref';
	$headings[] = 'Subject Name';
	$headings[] = 'Pay Date';
	$headings[] = 'Payment Method';
	$headings[] = 'Route';
	$headings[] = 'Amount';

	if ($dest == 's')
	{
		$screen = "
		<p style=\"font-size:18px; font-weight:bold;\">
			$title
			&nbsp;&nbsp;&nbsp;&nbsp;
			" . input_button('Export to Excel', "export_xl()", '', 'but_export_xl') . "
		</p>
		<table class=\"spaced_table\">
		";
		foreach ($top_lines as $line)
		{
			$screen .= "
				<tr>";
			foreach ($line as $field)
				$screen .= "<td>$field</td>";
			$screen .= "</tr>
				";
		}
		$screen .= "
		<tr>
			";
			foreach ($headings as $head)
				$screen .= "<th>$head</th>";
			if (user_debug())
				$screen .= "<th $grey>Group ID</th>";
			$screen .= "
		</tr>
		";
		foreach ($datalines as $dline)
		{
			$group = (array_key_exists('CLIENT_GROUP_ID', $dline) ? $dline['CLIENT_GROUP_ID'] : '');
			$screen .= "
			<tr>
			";
				if (1 < count($cids))
					$screen .= "<td $ar>{$dline['C_CODE']}</td><td>{$dline['C_CO_NAME']}</td>";
				$screen .= "
				<td>{$dline['CLIENT_REF']}</td>
				<td>{$dline['SUBJECT']}</td><td>{$dline['DATE']}</td><td>{$dline['PAY_METH']}</td>
				<td>{$dline['ROUTE']}</td><td $ar>" . money_format_kdb($dline['AMOUNT'], true, true, true) . "</td>
				" . (user_debug() ? "<td $grey>$group</td>" : '') . "
			</tr>
			";
		}
		$screen .= "
		</table>
		";
		printD($screen);
	}
	elseif ($dest == 'x')
	{
		$top_lines = array_merge(array(array($title)), array(array()), $top_lines, array(array()));
		#dprint("TL=" . print_r($top_lines,1));#
		#dprint("DL=" . print_r($datalines,1));#
		if (1 < count($cids))
			$formats = array('E' => $excel_date_format, 'H' => $excel_currency_format);
		else
			$formats = array('C' => $excel_date_format, 'F' => $excel_currency_format);
		$sheet = array($title_short, $top_lines, $headings, $datalines, $formats, '');
		phpExcel_output($reps_subdir, $xfile, array($sheet)); # library.php

		# Auto-download file to PC
		printD("
		<script type=\"text/javascript\">
			document.form_csv_download.short_fname.value = '$xfile.$phpExcel_ext';
			document.form_csv_download.full_fname.value = '$csv_path' + '{$reps_subdir}/$xfile.$phpExcel_ext';
			document.form_csv_download.submit();
		</script>
		");
	}
	else
		dlog("$this_function: *=* bad dest \"$dest\"");

} # rep_cf_single_client()

function rep_cf_stair_step($dest, $sc_date_fr, $sc_date_to, $sc_dtcol_fr, $sc_dtcol_to, $sc_client, $sc_group, $sc_rt_tv, $sc_rt_fw, $sc_rt_di, $xfile='')
{
	# Collections Report: Stair Step Payment Listing
	# $dest is 's' for screen or 'x' for export to Excel file.
	# $sc_date_fr and $sc_date_to are search criteria that form a date range which
	#		job placement dates must fall within; either may be blank.
	# $sc_dtcol_fr and $sc_dtcol_to are search criteria that form a date range which
	#		collection payment dates must fall within; either may be blank.
	# $sc_client is search criteria: a client code or part of a client name (or an alpha code)
	# $sc_group is search criteria: a client group ID
	# $sc_rt_tv, $sc_rt_fw and $sc_rt_di are payment route types, each is boolean (1 or 0)
	# $xfile is the name of the file that the search results should be written to;
	#		it is used when $dest=='x'

	global $ar;
	global $csv_path;
	#global $excel_currency_format;
	#global $excel_date_format;
	global $grey;
	global $id_ROUTE_direct;
	global $id_ROUTE_fwd;
	global $id_ROUTE_tous;
	#global $PAYMENT_ROUTES; # init_data()
	global $phpExcel_ext; # settings.php: "xls"
	global $sqlFalse;
	global $sqlTrue;
	global $reps_subdir;

	$this_function = "rep_cf_stair_step(\"$dest\", \"$sc_date_fr\", \"$sc_date_to\", \"$sc_dtcol_fr\", \"$sc_dtcol_to\", \"$sc_client\", \"$sc_group\", \"$sc_rt_tv\", \"$sc_rt_fw\", \"$sc_rt_di\", \"$xfile\")";
	#dprint("Entered: $this_function");#

	if ($sc_date_fr)
	{
		if ($sc_date_to)
			$date_string = ", jobs placed between $sc_date_fr and $sc_date_to";
		else
			$date_string = ", jobs placed from $sc_date_fr";
	}
	elseif ($sc_date_to)
		$date_string = ", jobs placed up to $sc_date_to";
	else
		$date_string = "(all placement dates)";

	if ($sc_dtcol_fr)
	{
		if ($sc_dtcol_to)
			$date2_string = ", paid between $sc_dtcol_fr and $sc_dtcol_to";
		else
			$date2_string = ", paid from $sc_dtcol_fr";
	}
	elseif ($sc_dtcol_to)
		$date2_string = ", paid up to $sc_dtcol_to";
	else
		$date2_string = "(all payment dates)";

	$sc_c_code = intval($sc_client);
	$client_name = '';
	$client_string = '';
	if ($sc_client)
	{
		if ($sc_c_code > 0)
		{
			$client_name = client_name_from_code($sc_c_code);
			if ($client_name)
				$client_name = "$sc_c_code - $client_name";
		}
		if (!$client_name)
			$client_name = $sc_client;
		$client_string .= ", client $client_name";
	}

	$group_id = intval($sc_group);
	if ($group_id > 0)
		$group_string = ", client group \"" . sql_client_group_name_from_id($group_id) . "\"";
	else
		$group_string = '';

	$route_string = array();
	if ($sc_rt_tv)
		$route_string[] = "To Vilcol";
	if ($sc_rt_fw)
		$route_string[] = "Forwarded";
	if ($sc_rt_di)
		$route_string[] = "Direct";
	if (count($route_string) == 1)
		$route_string = ", route: {$route_string[0]}";
	elseif (1 < count($route_string))
		$route_string = ", routes: " . implode(', ', $route_string);
	else
		$route_string = ", (no routes)";

	$title_short = "Stair Step Payment Listing";
	$title = "{$title_short}{$date_string}{$date2_string}{$client_string}{$group_string}{$route_string}";
	#dprint($title);#

	$temp = array("J.OBSOLETE=$sqlFalse", "J.JC_JOB=$sqlTrue");
	$where_p_and_c = $temp; # client and placement dates, but also collection dates and routes
	$where_placement_only = $temp; # client and placement dates but not collection dates or routes

	$date_j_field = 'J.J_OPENED_DT'; # job placement date
	$date_c_field = 'P.COL_DT_RX'; # collection (payment) date
	$where_coll_start = ''; # WHERE sub-clause for start date of collection date range, if given
	$where_coll_end = ''; # WHERE sub-clause for end date of collection date range, if given
	$where_coll_prior = ''; # WHERE sub-clause for all dats prior to start date of collection date range
	if ($sc_date_fr) # placements
	{
		$temp = "'" . date_for_sql_nqnt($sc_date_fr) . "' <= $date_j_field";
		$where_p_and_c[] = $temp;
		$where_placement_only[] = $temp;
	}
	if ($sc_date_to) # placements
	{
		$temp = "$date_j_field < '" . date_for_sql_nqnt($sc_date_to, true) . "'"; # NOT "<="
		$where_p_and_c[] = $temp;
		$where_placement_only[] = $temp;
	}
	if ($sc_dtcol_fr) # payments
	{
		$where_coll_start = "'" . date_for_sql_nqnt($sc_dtcol_fr) . "' <= $date_c_field";
		$where_coll_prior = "$date_c_field < '" . date_for_sql_nqnt($sc_dtcol_fr) . "'"; # NOT "<="
		$where_p_and_c[] = $where_coll_start;
	}
	if ($sc_dtcol_to) # payments
	{
		$where_coll_end = "$date_c_field < '" . date_for_sql_nqnt($sc_dtcol_to, true) . "'"; # NOT "<="
		$where_p_and_c[] = $where_coll_end;
	}

	if ($sc_client)
	{
		$temp = "C.CLIENT2_ID > 0";
		$where_p_and_c[] = $temp;
		$where_placement_only[] = $temp;
		if ($sc_c_code > 0)
		{
			$temp = "C.C_CODE = $sc_c_code";
			$where_p_and_c[] = $temp;
			$where_placement_only[] = $temp;
		}
		else
		{
			$sc_client2_id = (($sc_client[0] == '*') ? intval(trim(str_replace('*', '', $sc_client))) : 0);
			if ($sc_client2_id > 0)
			{
				$temp = "C.CLIENT2_ID = $sc_client2_id";
				$where_p_and_c[] = $temp;
				$where_placement_only[] = $temp;
			}
			else
			{
				sql_encryption_preparation('CLIENT2');
				$sc_client = addslashes_kdb($sc_client);
				$sql = "SELECT CLIENT2_ID FROM CLIENT2 WHERE " . sql_decrypt('C_CO_NAME') . " LIKE '%{$sc_client}%' OR " .
							"(ALPHA_CODE='" . addslashes_kdb($sc_client) . "')";
				#dprint($sql);#
				sql_execute($sql);
				$cids = array();
				while (($newArray = sql_fetch()) != false)
					$cids[] = $newArray[0];
				if ($cids)
				{
					$temp = "C.CLIENT2_ID IN (" . implode(',', $cids) . ")";
					$where_p_and_c[] = $temp;
					$where_placement_only[] = $temp;
				}
				else
					dprint("sc_client \"$sc_client\" yielded no client_id(s)");
			}
		}
	}
	if ($group_id > 0)
	{
		$temp = "G.CLIENT_GROUP_ID > 0";
		$where_p_and_c[] = $temp;
		$where_placement_only[] = $temp;
		$temp = "G.CLIENT_GROUP_ID=$group_id";
		$where_p_and_c[] = $temp;
		$where_placement_only[] = $temp;
		$group_select = ", G.CLIENT_GROUP_ID";
		$group_join = " LEFT JOIN CLIENT_GROUP G ON G.CLIENT_GROUP_ID=C.CLIENT_GROUP_ID ";
	}
	else
	{
		$group_select = '';
		$group_join = '';
	}

	$sub_where = array();
	if ($sc_rt_tv)
		$sub_where[] = "P.COL_PAYMENT_ROUTE_ID=$id_ROUTE_tous";
	if ($sc_rt_fw)
		$sub_where[] = "P.COL_PAYMENT_ROUTE_ID=$id_ROUTE_fwd";
	if ($sc_rt_di)
		$sub_where[] = "P.COL_PAYMENT_ROUTE_ID=$id_ROUTE_direct";
	if ($sub_where)
		$where_p_and_c[] = "(" . implode(') OR (', $sub_where) . ")";
	else
	{
		dprint("No Payment Routes are ticked. Search aborted.", true);
		return;
	}
	$where_p_and_c[] = "P.OBSOLETE=$sqlFalse";

	#dprint("where = " . print_r($where_p_and_c,1));#
	#dprint("where_placement_only = " . print_r($where_placement_only,1));#

	if ($where_p_and_c)
		$where_p_and_c = "WHERE (" . implode(') AND (', $where_p_and_c) . ")";
	else
		$where_p_and_c = '';

	if ($where_placement_only)
		$where_placement_only = "WHERE (" . implode(') AND (', $where_placement_only) . ")";
	else
		$where_placement_only = '';

	# Get Dataset A - jobs within client and placement-date criteria regardless of collections
	$pa_months = array(); # placement-amount months
	$placed_count = 0;
	$placed_amount = 0.0;
	$sql= "SELECT J.J_OPENED_DT, J.JC_TOTAL_AMT $group_select
			FROM JOB AS J INNER JOIN CLIENT2 AS C ON C.CLIENT2_ID=J.CLIENT2_ID
			$group_join
			$where_placement_only";
	#if ($dest == 's') dprint($sql);#
	sql_execute($sql);
	while (($newArray = sql_fetch_assoc()) != false)
	{
		$placed_count++;
		$month = ($newArray['J_OPENED_DT'] ? substr($newArray['J_OPENED_DT'], 0, strlen('yyyy-mm')) : '');
		if ($month && !array_key_exists($month, $pa_months))
			$pa_months[$month] = 0.0;

		$amount = floatval($newArray['JC_TOTAL_AMT']);
		$pa_months[$month] += $amount;
		$placed_amount += $amount;
	}
	krsort($pa_months);
	#dprint("pa_months = " . print_r($pa_months,1));#

	# Get Dataset B - jobs within client and placement-date criteria and within collection-date criteria and payment route criteria
	# We run the SQL twice - once for collection dates in the given date range,
	# then again for collection dates prior to the given date range
	$c_min = ''; # first month of collections
	$c_max = ''; # last month of collections
	$sql = "
		SELECT J.J_OPENED_DT, J.JC_TOTAL_AMT, P.COL_AMT_RX, P.COL_DT_RX $group_select
		FROM JOB AS J INNER JOIN CLIENT2 AS C ON C.CLIENT2_ID=J.CLIENT2_ID
		INNER JOIN JOB_PAYMENT AS P ON J.JOB_ID=P.JOB_ID
		$group_join
		$where_p_and_c
		";
	#if ($dest == 's') dprint($sql);#
	sql_execute($sql);
	$collections = array(); # $collections[placement_month][collection_month] = amount collected
	while (($newArray = sql_fetch_assoc()) != false)
	{
		$plc_month = ($newArray['J_OPENED_DT'] ? substr($newArray['J_OPENED_DT'], 0, strlen('yyyy-mm')) : '');
		if ($plc_month && !array_key_exists($plc_month, $collections))
			$collections[$plc_month] = array();

		$col_month = ($newArray['COL_DT_RX'] ? substr($newArray['COL_DT_RX'], 0, strlen('yyyy-mm')) : '');
		if ($col_month && !array_key_exists($col_month, $collections[$plc_month]))
			$collections[$plc_month][$col_month] = 0.0;
		$collections[$plc_month][$col_month] += floatval($newArray['COL_AMT_RX']);

		if ((!$c_min) || ($col_month < $c_min))
			$c_min = $col_month;
		if ((!$c_max) || ($c_max < $col_month))
			$c_max = $col_month;
	}
	if ($where_coll_prior)
	{
		# Second run for "prior" date column
		$sql = str_replace($where_coll_start, $where_coll_prior, $sql);
		$sql = str_replace($where_coll_end, '0=0', $sql);
		#if ($dest == 's') dprint($sql);#
		sql_execute($sql);
		while (($newArray = sql_fetch_assoc()) != false)
		{
			$plc_month = ($newArray['J_OPENED_DT'] ? substr($newArray['J_OPENED_DT'], 0, strlen('yyyy-mm')) : '');
			if ($plc_month && !array_key_exists($plc_month, $collections))
				$collections[$plc_month] = array();

			$col_month = 'PRIOR';
			if ($col_month && !array_key_exists($col_month, $collections[$plc_month]))
				$collections[$plc_month][$col_month] = 0.0;
			$collections[$plc_month][$col_month] += floatval($newArray['COL_AMT_RX']);
		}
	}
	else
	{
		foreach ($collections as $plc_month => $plc_data)
			$collections[$plc_month]['PRIOR'] = 0.0;
	}
	krsort($collections);
	foreach ($collections as $plc_month => $plc_data)
		krsort($plc_data);
	#dprint("\$collections = " . print_r($collections,1));#

	if ( ! ($c_min && $c_max) )
	{
		dprint("No Collection dates could be established. Search aborted.", true);
		return;
	}
	$c_months = months_range($c_min, $c_max, true, true); # collection months
	$c_months[] = 'PRIOR';
	$c_totals = array();
	$c_grand_total = 0.0;
	foreach ($c_months as $c_mon)
	{
		$c_totals[$c_mon] = 0.0;
		foreach ($collections as $plc_month => $plc_data)
		{
			if (array_key_exists($c_mon, $plc_data))
				$c_totals[$c_mon] += $plc_data[$c_mon];
		}
		$c_grand_total += $c_totals[$c_mon];
	}

	$datalines = array();
	foreach ($pa_months as $p_mon => $p_amount)
	{
		$line_1 = array();
		$line_2 = array();
//		if (1 < count($cids))
//		{
//			$line_1['C_CODE'] = ''; # client code
//			$line_2['C_CODE'] = ''; # client code
//			$line_1['C_CO_NAME'] = ''; # client name
//			$line_2['C_CO_NAME'] = ''; # client name
//		}
		$line_1['ROW_HEADING'] = $p_mon;
		$line_2['ROW_HEADING'] = '';
		$line_1['P_AMOUNT'] = $p_amount;
		$line_2['P_AMOUNT'] = '';
		foreach ($c_months as $c_mon)
		{
			if (array_key_exists($c_mon, $collections[$p_mon]))
			{
				$line_1[$c_mon] = $collections[$p_mon][$c_mon];
				$line_2[$c_mon] = 100.0 * floatval($collections[$p_mon][$c_mon]) / $p_amount;
			}
			else
			{
				$line_1[$c_mon] = 0.0;
				$line_2[$c_mon] = 0.0;
			}
		}
		$datalines[] = $line_1;
		$datalines[] = $line_2;
	}

	$line_1 = array();
//	if (1 < count($cids))
//	{
//		$line_1['C_CODE'] = ''; # client code
//		$line_1['C_CO_NAME'] = ''; # client name
//	}
	$line_1['ROW_HEADING'] = 'Totals';
	$line_1['P_AMOUNT'] = $placed_amount;
	foreach ($c_months as $c_mon)
		$line_1[$c_mon] = $c_totals[$c_mon];
	$datalines[] = $line_1;

	$blank_line = array();
//	if (1 < count($cids))
//	{
//		$blank_line['C_CODE'] = ''; # client code
//		$blank_line['C_CO_NAME'] = ''; # client name
//	}
	$blank_line['ROW_HEADING'] = '';
	$blank_line['P_AMOUNT'] = '';
	foreach ($c_months as $c_mon)
		$blank_line[$c_mon] = '';
	$datalines[] = $blank_line;

	#dprint("datalines=" . print_r($datalines,1));#

	$bottom_lines = array();

	$line_1 = array();
//	if (1 < count($cids))
//	{
//		$line_1['C_CODE'] = ''; # client code
//		$line_1['C_CO_NAME'] = ''; # client name
//	}
	$line_1['ROW_HEADING'] = 'Accounts Placed:';
	#$line_1['P_AMOUNT'] = count($jobs);
	$line_1['P_AMOUNT'] = $placed_count;
	foreach ($c_months as $c_mon)
		$line_1[$c_mon] = '';
	$bottom_lines[] = $line_1;

	$bottom_lines[] = $blank_line;

	$line_1 = array();
//	if (1 < count($cids))
//	{
//		$line_1['C_CODE'] = ''; # client code
//		$line_1['C_CO_NAME'] = ''; # client name
//	}
	$line_1['ROW_HEADING'] = 'Amount Placed:';
	$line_1['P_AMOUNT'] = $placed_amount;
	foreach ($c_months as $c_mon)
		$line_1[$c_mon] = '';
	$bottom_lines[] = $line_1;

	$bottom_lines[] = $blank_line;

	$line_1 = array();
//	if (1 < count($cids))
//	{
//		$line_1['C_CODE'] = ''; # client code
//		$line_1['C_CO_NAME'] = ''; # client name
//	}
	$line_1['ROW_HEADING'] = 'Amount Collected:';
	$line_1['P_AMOUNT'] = $c_grand_total;
	foreach ($c_months as $c_mon)
		$line_1[$c_mon] = '';
	$bottom_lines[] = $line_1;

	$bottom_lines[] = $blank_line;

	$line_1 = array();
//	if (1 < count($cids))
//	{
//		$line_1['C_CODE'] = ''; # client code
//		$line_1['C_CO_NAME'] = ''; # client name
//	}
	$line_1['ROW_HEADING'] = '% Collected:';
	$line_1['P_AMOUNT'] = 100.0 * floatval($c_grand_total) / $placed_amount;
	foreach ($c_months as $c_mon)
		$line_1[$c_mon] = '';
	$bottom_lines[] = $line_1;

	#dprint("\$bottom_lines=" . print_r($bottom_lines,1));#

	$top_lines = array(
		array('Vilcol Stair Step Report'),
		array("Client Code: $sc_c_code", "Client: $client_name"),
		array("Report Date: " . date_now(true, '', false))
		);

	$headings = array();
//	if (1 < count($cids))
//	{
//		$headings[] = 'Client Code';
//		$headings[] = 'Client Name';
//	}
	$headings[] = 'Date Placed';
	$headings[] = 'Placement Amount';
	$prev_month = 'x';
	foreach ($c_months as $c_mon)
	{
		if ($c_mon == 'PRIOR')
			$headings[] = "Prior to $prev_month";
		else
		{
			$headings[] = $c_mon;
			$prev_month = $c_mon;
		}
	}

	if ($dest == 's')
	{
		$screen = "
		<p style=\"font-size:18px; font-weight:bold;\">
			$title
			&nbsp;&nbsp;&nbsp;&nbsp;
			" . input_button('Export to Excel', "export_xl()", '', 'but_export_xl') . "
		</p>
		<table class=\"spaced_table\">
		";
		foreach ($top_lines as $line)
		{
			$screen .= "
				<tr>";
			foreach ($line as $field)
				$screen .= "<td>$field</td>";
			$screen .= "</tr>
				";
		}
		$screen .= "
		<tr>
			";
			foreach ($headings as $head)
				$screen .= "<th>$head</th>";
			if (user_debug())
				$screen .= "<th $grey>Group ID</th>";
			$screen .= "
		</tr>
		";
		$odd_line = true;
		foreach ($datalines as $dline)
		{
			$group = (array_key_exists('CLIENT_GROUP_ID', $dline) ? $dline['CLIENT_GROUP_ID'] : '');
			$screen .= "
			<tr>
			";
//				if (1 < count($cids))
//					$screen .= "<td $ar>{$dline['C_CODE']}</td><td>{$dline['C_CO_NAME']}</td>";
				$screen .= "
				<td>{$dline['ROW_HEADING']}</td>
				<td $ar>" . ($odd_line ? money_format_kdb($dline['P_AMOUNT'], true, true, true) : '') . "</td>
					";
				foreach ($c_months as $c_mon)
				{
					$screen .= "<td $ar>";
					if ($dline[$c_mon] != '')
					{
						if ($odd_line)
							$screen .= money_format_kdb($dline[$c_mon], true, true, true);
						else
							$screen .= round($dline[$c_mon], 4) . "%";
					}
					$screen .= "</td>";
				}
				$screen .= (user_debug() ? "<td $grey>$group</td>" : '');
				$screen .= "
			</tr>
			";
			$odd_line = !$odd_line;
		}

		$bline = 1;
		foreach ($bottom_lines as $line)
		{
			$screen .= "
				<tr>";
//			if (1 < count($cids))
//				$screen .= "<td $ar></td><td></td>";
			if ($line['ROW_HEADING'] == '') # must be a blank line
				$screen .= "<td></td><td></td>";
			else
			{
				$screen .= "<td>{$line['ROW_HEADING']}</td><td $ar>";
				if ($bline == 1)
					$screen .= $line['P_AMOUNT'];
				elseif ($bline == 2 || $bline == 3)
					$screen .= money_format_kdb($line['P_AMOUNT'], true, true, true);
				else
					$screen .= round($line['P_AMOUNT'], 4) . "%";
				$screen .= "</td>";
				$bline++;
			}
			$screen .= "</tr>
					";
		}

		$screen .= "
		</table>
		";
		printD($screen);
	}
	elseif ($dest == 'x')
	{
		$top_lines = array_merge(array(array($title)), array(array()), $top_lines, array(array()));
		#dprint("TL=" . print_r($top_lines,1));#
		#dprint("DL=" . print_r($datalines,1));#
//		if (1 < count($cids))
//			$formats = array();
//		else
			$formats = array();
		$sheet = array($title_short, $top_lines, $headings, $datalines, $formats, $bottom_lines);
		phpExcel_output($reps_subdir, $xfile, array($sheet)); # library.php

		# Auto-download file to PC
		printD("
		<script type=\"text/javascript\">
			document.form_csv_download.short_fname.value = '$xfile.$phpExcel_ext';
			document.form_csv_download.full_fname.value = '$csv_path' + '{$reps_subdir}/$xfile.$phpExcel_ext';
			document.form_csv_download.submit();
		</script>
		");
	}
	else
		dlog("$this_function: *=* bad dest \"$dest\"");

} # rep_cf_stair_step()

function rep_cf_tdx_trans($dest, $sc_date_fr, $sc_date_to, $sc_client, $sc_group, $xfile='')
{
	# Collections Report: TDX Transaction Report. Lots of questions emailed to Jim on this one.
	# $dest is 's' for screen or 'x' for export to Excel file.
	# $sc_date_fr and $sc_date_to are search criteria that form a date range which
	#	collection payment must fall within; either may be blank.
	# $sc_client is search criteria: a client code or part of a client name (or an alpha code)
	# $sc_group is search criteria: a client group ID
	# $xfile is the name of the file that the search results should be written to;
	#		it is used when $dest=='x'

	global $ar;
	global $csv_path;
	global $excel_currency_format;
	global $excel_date_format;
	global $excel_decimal_format;
	global $excel_integer_format;
	global $grey;
	global $phpExcel_ext; # settings.php: "xls"
	global $sqlFalse;
	global $sqlTrue;
	global $reps_subdir;

	$this_function = "rep_cf_tdx_trans(\"$dest\", \"$sc_date_fr\", \"$sc_date_to\", \"$sc_client\", \"$sc_group\", \"$xfile\")";
	#dprint("Entered: $this_function");#

	if ($sc_date_fr)
	{
		if ($sc_date_to)
			$date_string = ", payments between $sc_date_fr and $sc_date_to";
		else
			$date_string = ", payments from $sc_date_fr";
	}
	elseif ($sc_date_to)
		$date_string = ", payments up to $sc_date_to";
	else
		$date_string = "(all payment dates)";

	$sc_c_code = intval($sc_client);
	$c_code_list = array();
	$temp = str_replace(',', '', str_replace(' ', '', $sc_client));
	if (is_numeric_kdb($temp, false, false, false))
		$c_code_list = explode(',', str_replace(' ', '', $sc_client));
	$client_string = '';
	if ($sc_client)
	{
		$client_name = '';
		if ($sc_c_code > 0)
		{
			$client_name = client_name_from_code($sc_c_code);
			if ($client_name)
				$client_name = "$sc_c_code - $client_name";
		}
		if (!$client_name)
			$client_name = $sc_client;
		$client_string .= ", client $client_name";
	}

	$group_id = intval($sc_group);
	if ($group_id > 0)
		$group_string = ", client group \"" . sql_client_group_name_from_id($group_id) . "\"";
	else
		$group_string = '';

	$title_short = "Transaction Report";
	$title = "{$title_short}{$date_string}{$client_string}{$group_string}";
	#dprint($title);#

	$where = array("J.OBSOLETE=$sqlFalse", "J.JC_JOB=$sqlTrue");

	$date_c_field = 'P.COL_DT_RX'; # collection (payment) date
	if ($sc_date_fr)
		$where[] = "'" . date_for_sql_nqnt($sc_date_fr) . "' <= $date_c_field";
	if ($sc_date_to)
		$where[] = "$date_c_field < '" . date_for_sql_nqnt($sc_date_to, true) . "'"; # NOT "<="

	if ($sc_client)
	{
		$where[] = "C.CLIENT2_ID > 0";
		if (count($c_code_list) > 1)
			$where[] = "C.C_CODE IN (" . implode(',', $c_code_list) . ")";
		elseif ($sc_c_code > 0)
			$where[] = "C.C_CODE = $sc_c_code";
		else
		{
			$sc_client2_id = (($sc_client[0] == '*') ? intval(trim(str_replace('*', '', $sc_client))) : 0);
			if ($sc_client2_id > 0)
				$where[] = "C.CLIENT2_ID = $sc_client2_id";
			else
			{
				sql_encryption_preparation('CLIENT2');
				$sc_client = addslashes_kdb($sc_client);
				$sql = "SELECT CLIENT2_ID FROM CLIENT2 WHERE " . sql_decrypt('C_CO_NAME') . " LIKE '%{$sc_client}%' OR " .
							"(ALPHA_CODE='" . addslashes_kdb($sc_client) . "')";
				#dprint($sql);#
				sql_execute($sql);
				$cids = array();
				while (($newArray = sql_fetch()) != false)
					$cids[] = $newArray[0];
				if ($cids)
					$where[] = "C.CLIENT2_ID IN (" . implode(',', $cids) . ")";
				else
					dprint("sc_client \"$sc_client\" yielded no client_id(s)");
			}
		}
	}
	if ($group_id > 0)
	{
		$where[] = "G.CLIENT_GROUP_ID > 0";
		$where[] = "G.CLIENT_GROUP_ID=$group_id";
		$group_select = ", G.CLIENT_GROUP_ID";
		$group_join = " LEFT JOIN CLIENT_GROUP G ON G.CLIENT_GROUP_ID=C.CLIENT_GROUP_ID ";
	}
	else
	{
		$group_select = '';
		$group_join = '';
	}
	$where[] = "P.OBSOLETE=$sqlFalse";

	if ($where)
		$where = "WHERE (" . implode(') AND (', $where) . ")";
	else
		$where = '';

	sql_encryption_preparation('CLIENT2');
	sql_encryption_preparation('JOB');
	$sql = "
		SELECT P.JOB_PAYMENT_ID, P.COL_AMT_RX, P.COL_DT_RX, P.COL_PERCENT, P.COL_BOUNCED,
			C.CLIENT2_ID, PM.TDX_CODE, J.JC_TRANS_ID, J.JC_TRANS_CNUM,
			J.JC_TOTAL_AMT - J.JC_PAID_SO_FAR AS BALANCE, " . sql_decrypt('J.CLIENT_REF', '', true) . "
			$group_select
		FROM JOB_PAYMENT AS P
		LEFT JOIN JOB AS J ON J.JOB_ID=P.JOB_ID
		LEFT JOIN CLIENT2 AS C ON C.CLIENT2_ID=J.CLIENT2_ID
		LEFT JOIN PAYMENT_METHOD_SD AS PM ON PM.PAYMENT_METHOD_ID=P.COL_PAYMENT_METHOD_ID
		$group_join
		$where
		ORDER BY " . sql_decrypt('J.CLIENT_REF') . ", P.COL_DT_RX
		";
	if ($dest == 's') dprint($sql);#
	sql_execute($sql);
	$lines = array(); # payments
	$clients = array();
	$cids = array();
	while (($newArray = sql_fetch_assoc()) != false)
	{
		$bits = explode(' ', $newArray['COL_DT_RX']);
		$newArray['COL_DT_RX'] = $bits[0]; # drop the time portion
		$lines[] = $newArray;
		if (!in_array($newArray['CLIENT2_ID'], $cids))
		{
			$cids[] = $newArray['CLIENT2_ID'];
			$clients[$newArray['CLIENT2_ID']] = '';
		}
	}
	#dprint("lines=" . print_r($lines,1) . "<br>clients/1=" . print_r($clients,1));#

	if (count($lines) == 0)
	{
		dprint("No Payments were found with the given criteria.", true);
		return;
	}

	sql_encryption_preparation('CLIENT2');
	$sql = "SELECT CLIENT2_ID, C_CODE, " . sql_decrypt('C_CO_NAME', '', true) . " FROM CLIENT2
			WHERE CLIENT2_ID IN (" . implode(',', $cids) . ")";
	#if ($dest == 's') dprint($sql);#
	sql_execute($sql);
	$clients = array();
	$the_client = '';
	$the_code = '';
	while (($newArray = sql_fetch_assoc()) != false)
	{
		if (!$the_client)
		{
			$the_client = $newArray['C_CO_NAME'];
			$the_code = $newArray['C_CODE'];
		}
		$clients[$newArray['CLIENT2_ID']] = array('C_CODE' => $newArray['C_CODE'],
				'C_CO_NAME' => $newArray['C_CO_NAME'], 'ALT_NAME' => 'TXT Loan');
	}
	if (!$the_client)
	{
		$the_client = '(none)';
		$the_code = '-';
	}
	#dprint("theClient=\"$the_client\", the_code=\"$the_code\", clients/2=" . print_r($clients,1));#

	$datalines = array();
	$nlines = count($lines);
	$sum_pay = 0.0;
	$count_pay = 0;
	$blank_tid = 0;
	for ($ii=0 ; $ii < $nlines; $ii++)
	{
		$line = $lines[$ii];
		$pid = intval($line['JOB_PAYMENT_ID']);
		$datalines[$pid] = array();
		$datalines[$pid]['JC_TRANS_ID'] = $line['JC_TRANS_ID'];
		if ($datalines[$pid]['JC_TRANS_ID'] == '')
			$blank_tid++;
		$datalines[$pid]['CLIENT_REF'] = $line['CLIENT_REF'];
		$datalines[$pid]['JC_TRANS_CNUM'] = $line['JC_TRANS_CNUM'];
		$datalines[$pid]['C_CO_NAME'] = $clients[$line['CLIENT2_ID']]['C_CO_NAME'];
		$datalines[$pid]['ALT_NAME'] = $clients[$line['CLIENT2_ID']]['C_CO_NAME'];
		$datalines[$pid]['BALANCE'] = $line['BALANCE'];

		$last_dt = $line['COL_DT_RX'];
		for ($jj = $ii+1; $jj < $nlines; $jj++)
		{
			if ($lines[$jj]['CLIENT_REF'] == $line['CLIENT_REF'])
				$last_dt = $lines[$jj]['COL_DT_RX'];
		}
		$datalines[$pid]['LAST_DT'] = date_for_sql($last_dt, true, false);
		$datalines[$pid]['COL_AMT_RX'] = $line['COL_AMT_RX'];
		$datalines[$pid]['COL_DT_RX'] = date_for_sql($line['COL_DT_RX'], true, false);
		$sum_pay += floatval($line['COL_AMT_RX']);
		$count_pay++;

		$datalines[$pid]['PAY_REV'] = ($line['COL_BOUNCED'] ? 'REV' : 'PAY');
		$datalines[$pid]['PAYMENT_METHOD'] = $line['TDX_CODE'];
		$datalines[$pid]['CURRENCY'] = 'GBP';
		$datalines[$pid]['COMM_AMOUNT'] = 0.01 * floatval($line['COL_PERCENT']) * $line['COL_AMT_RX'];
		$datalines[$pid]['COMM_FRACTION'] = 0.01 * floatval($line['COL_PERCENT']);
		$datalines[$pid]['ZERO'] = 0;
	}
	#dprint("datalines=" . print_r($datalines,1));#

	$top_lines = array(
		array('Transaction', date_now(true, '', false), 'VIL_TDX_transaction.csv', $sum_pay, $count_pay)
		);

	$headings = array();

	if ($dest == 's')
	{
		$screen = "
		<p style=\"font-size:18px; font-weight:bold;\">
			$title
			&nbsp;&nbsp;&nbsp;&nbsp;
			" . input_button('Export to Excel', "export_xl()", '', 'but_export_xl') . "
		</p>
		<table class=\"spaced_table\">
		";
		foreach ($top_lines as $line)
		{
			$screen .= "
				<tr>";
			foreach ($line as $field)
				$screen .= "<td>$field</td>";
			$screen .= "</tr>
				";
		}
		if ($headings)
		{
			$screen .= "
			<tr>
				";
				foreach ($headings as $head)
					$screen .= "<th>$head</th>";
				if (user_debug())
					$screen .= "<th $grey>Group ID</th>";
				$screen .= "
			</tr>
			";
		}
		foreach ($datalines as $dline)
		{
			$group = (array_key_exists('CLIENT_GROUP_ID', $dline) ? $dline['CLIENT_GROUP_ID'] : '');
			$screen .= "
			<tr>
				<td>{$dline['JC_TRANS_ID']}</td>
				<td>{$dline['CLIENT_REF']}</td>
				<td>{$dline['JC_TRANS_CNUM']}</td>
				<td>{$dline['C_CO_NAME']}</td>
				<td>{$dline['ALT_NAME']}</td>
				<td $ar>" . money_format_kdb($dline['BALANCE'], true, true, true) . "</td>
				<td $ar>{$dline['LAST_DT']}</td>
				<td $ar>" . money_format_kdb($dline['COL_AMT_RX'], true, true, true) . "</td>
				<td $ar>{$dline['COL_DT_RX']}</td>
				<td>{$dline['PAY_REV']}</td>
				<td>{$dline['PAYMENT_METHOD']}</td>
				<td>{$dline['CURRENCY']}</td>
				<td $ar>" . money_format_kdb($dline['COMM_AMOUNT'], true, true, true) . "</td>
				<td $ar>{$dline['COMM_FRACTION']}</td>
				<td $ar>{$dline['ZERO']}</td>
				" . (user_debug() ? "<td $grey>$group</td>" : '') . "
			</tr>
			";
		}
		$screen .= "
		</table>
		";
		printD($screen);
		dprint("There were $blank_tid records with a Blank Transaction ID (from a total of $nlines records)", true);
	}
	elseif ($dest == 'x')
	{
		$top_lines = array_merge(array(array($title)), array(array()), $top_lines);
		#dprint("TL=" . print_r($top_lines,1));#
		#dprint("DL=" . print_r($datalines,1));#
		$formats = array('F' => $excel_currency_format, 'G' => $excel_date_format, 'H' => $excel_currency_format, 'I' => $excel_date_format,
						'M' => $excel_currency_format, 'N' => $excel_decimal_format, 'O' => $excel_integer_format);
		$sheet = array($title_short, $top_lines, $headings, $datalines, $formats, '');
		phpExcel_output($reps_subdir, $xfile, array($sheet)); # library.php

		# Auto-download file to PC
		printD("
		<script type=\"text/javascript\">
			document.form_csv_download.short_fname.value = '$xfile.$phpExcel_ext';
			document.form_csv_download.full_fname.value = '$csv_path' + '{$reps_subdir}/$xfile.$phpExcel_ext';
			document.form_csv_download.submit();
		</script>
		");
	}
	else
		dlog("$this_function: *=* bad dest \"$dest\"");

} # rep_cf_tdx_trans()

function rep_cf_tdx_close($dest, $sc_date_fr, $sc_date_to, $sc_client, $sc_group, $xfile='')
{
	# Collections Report: TDX Closure Report aka TDX Write-off report.
	# $dest is 's' for screen or 'x' for export to Excel file.
	# $sc_date_fr and $sc_date_to are search criteria that form a date range which
	#	job closure dates must fall within; either may be blank.
	# $sc_client is search criteria: a client code or part of a client name (or an alpha code)
	# $sc_group is search criteria: a client group ID
	# $xfile is the name of the file that the search results should be written to;
	#		it is used when $dest=='x'

	global $ar;
	global $csv_path;
	global $excel_currency_format;
	global $excel_date_format;
	global $grey;
	global $phpExcel_ext; # settings.php: "xls"
	global $sqlFalse;
	global $sqlTrue;
	global $reps_subdir;

	$this_function = "rep_cf_tdx_close(\"$dest\", \"$sc_date_fr\", \"$sc_date_to\", \"$sc_client\", \"$sc_group\", \"$xfile\")";
	#dprint("Entered: $this_function");#

	if ($sc_date_fr)
	{
		if ($sc_date_to)
			$date_string = ", closures between $sc_date_fr and $sc_date_to";
		else
			$date_string = ", closures from $sc_date_fr";
	}
	elseif ($sc_date_to)
		$date_string = ", closures up to $sc_date_to";
	else
		$date_string = "(all closure dates)";

	$sc_c_code = intval($sc_client);
	$c_code_list = array();
	$temp = str_replace(',', '', str_replace(' ', '', $sc_client));
	if (is_numeric_kdb($temp, false, false, false))
		$c_code_list = explode(',', str_replace(' ', '', $sc_client));
	$client_string = '';
	if ($sc_client)
	{
		$client_name = '';
		if ($sc_c_code > 0)
		{
			$client_name = client_name_from_code($sc_c_code);
			if ($client_name)
				$client_name = "$sc_c_code - $client_name";
		}
		if (!$client_name)
			$client_name = $sc_client;
		$client_string .= ", client $client_name";
	}

	$group_id = intval($sc_group);
	if ($group_id > 0)
		$group_string = ", client group \"" . sql_client_group_name_from_id($group_id) . "\"";
	else
		$group_string = '';

	$title_short = "Closure Report";
	$title = "{$title_short}{$date_string}{$client_string}{$group_string}";
	#dprint($title);#

	$where = array("J.OBSOLETE=$sqlFalse", "J.JC_JOB=$sqlTrue");

	$date_c_field = 'J.J_CLOSED_DT'; # job closure date
	if ($sc_date_fr)
		$where[] = "'" . date_for_sql_nqnt($sc_date_fr) . "' <= $date_c_field";
	if ($sc_date_to)
		$where[] = "$date_c_field < '" . date_for_sql_nqnt($sc_date_to, true) . "'"; # NOT "<="

	if ($sc_client)
	{
		$where[] = "C.CLIENT2_ID > 0";
		if (count($c_code_list) > 1)
			$where[] = "C.C_CODE IN (" . implode(',', $c_code_list) . ")";
		elseif ($sc_c_code > 0)
			$where[] = "C.C_CODE = $sc_c_code";
		else
		{
			$sc_client2_id = (($sc_client[0] == '*') ? intval(trim(str_replace('*', '', $sc_client))) : 0);
			if ($sc_client2_id > 0)
				$where[] = "C.CLIENT2_ID = $sc_client2_id";
			else
			{
				sql_encryption_preparation('CLIENT2');
				$sc_client = addslashes_kdb($sc_client);
				$sql = "SELECT CLIENT2_ID FROM CLIENT2 WHERE " . sql_decrypt('C_CO_NAME') . " LIKE '%{$sc_client}%' OR " .
							"(ALPHA_CODE='" . addslashes_kdb($sc_client) . "')";
				#dprint($sql);#
				sql_execute($sql);
				$cids = array();
				while (($newArray = sql_fetch()) != false)
					$cids[] = $newArray[0];
				if ($cids)
					$where[] = "C.CLIENT2_ID IN (" . implode(',', $cids) . ")";
				else
					dprint("sc_client \"$sc_client\" yielded no client_id(s)");
			}
		}
	}
	if ($group_id > 0)
	{
		$where[] = "G.CLIENT_GROUP_ID > 0";
		$where[] = "G.CLIENT_GROUP_ID=$group_id";
		$group_select = ", G.CLIENT_GROUP_ID";
		$group_join = " LEFT JOIN CLIENT_GROUP G ON G.CLIENT_GROUP_ID=C.CLIENT_GROUP_ID ";
	}
	else
	{
		$group_select = '';
		$group_join = '';
	}

	if ($where)
		$where = "WHERE (" . implode(') AND (', $where) . ")";
	else
		$where = '';

	sql_encryption_preparation('JOB');
	$sql = "
		SELECT C.CLIENT2_ID, J.JOB_ID, J.JC_TRANS_ID, J.JC_TRANS_CNUM, J.J_CLOSED_DT,
			J.JC_TOTAL_AMT - J.JC_PAID_SO_FAR AS BALANCE, " . sql_decrypt('J.CLIENT_REF', '', true) . " ,
			'ABC-123' AS PREV_TRANS, 'PQR' AS TRI_CODE
			$group_select
		FROM JOB AS J
		LEFT JOIN CLIENT2 AS C ON C.CLIENT2_ID=J.CLIENT2_ID
		$group_join
		$where
		ORDER BY " . sql_decrypt('J.CLIENT_REF') . ", J.J_CLOSED_DT
		";
	if ($dest == 's') dprint($sql);#
	sql_execute($sql);
	$lines = array(); # payments
	$clients = array();
	$cids = array();
	$sum_bal = 0.0;
	$count_bal = 0;
	while (($newArray = sql_fetch_assoc()) != false)
	{
		$sum_bal += floatval($newArray['BALANCE']);
		$count_bal++;

		$bits = explode(' ', $newArray['J_CLOSED_DT']);
		$newArray['J_CLOSED_DT'] = $bits[0]; # drop the time portion
		$lines[] = $newArray;
		if (!in_array($newArray['CLIENT2_ID'], $cids))
		{
			$cids[] = $newArray['CLIENT2_ID'];
			$clients[$newArray['CLIENT2_ID']] = '';
		}
	}
	#dprint("lines=" . print_r($lines,1) . "<br>clients/1=" . print_r($clients,1));#

	if (count($lines) == 0)
	{
		dprint("No Closures were found with the given criteria.", true);
		return;
	}

	sql_encryption_preparation('CLIENT2');
	$sql = "SELECT CLIENT2_ID, C_CODE, " . sql_decrypt('C_CO_NAME', '', true) . " FROM CLIENT2
			WHERE CLIENT2_ID IN (" . implode(',', $cids) . ")";
	#if ($dest == 's') dprint($sql);#
	sql_execute($sql);
	$clients = array();
	$the_client = '';
	$the_code = '';
	while (($newArray = sql_fetch_assoc()) != false)
	{
		if (!$the_client)
		{
			$the_client = $newArray['C_CO_NAME'];
			$the_code = $newArray['C_CODE'];
		}
		$clients[$newArray['CLIENT2_ID']] = array('C_CODE' => $newArray['C_CODE'],
				'C_CO_NAME' => $newArray['C_CO_NAME'], 'ALT_NAME' => 'TXT Loan');
	}
	if (!$the_client)
	{
		$the_client = '(none)';
		$the_code = '-';
	}
	#dprint("theClient=\"$the_client\", the_code=\"$the_code\", clients/2=" . print_r($clients,1));#

	$datalines = array();
	$nlines = count($lines);
	$blank_tid = 0;
	for ($ii=0 ; $ii < $nlines; $ii++)
	{
		$line = $lines[$ii];
		$jid = intval($line['JOB_ID']);
		$datalines[$jid] = array();
		$datalines[$jid]['JC_TRANS_ID'] = $line['JC_TRANS_ID'];
		if ($datalines[$jid]['JC_TRANS_ID'] == '')
			$blank_tid++;
		$datalines[$jid]['CLIENT_REF'] = $line['CLIENT_REF'];
		$datalines[$jid]['JC_TRANS_CNUM'] = $line['JC_TRANS_CNUM'];
		$datalines[$jid]['C_CO_NAME'] = $clients[$line['CLIENT2_ID']]['C_CO_NAME'];
		$datalines[$jid]['ALT_NAME'] = $clients[$line['CLIENT2_ID']]['C_CO_NAME'];
		$datalines[$jid]['BALANCE'] = $line['BALANCE'];
		$datalines[$jid]['J_CLOSED_DT'] = date_for_sql($line['J_CLOSED_DT'], true, false);
		$datalines[$jid]['PREV_TRANS'] = $line['PREV_TRANS'];
		$datalines[$jid]['TRI_CODE'] = $line['TRI_CODE'];
	}
	#dprint("datalines=" . print_r($datalines,1));#

	$top_lines = array(
		array('WriteOff', date_now(true, '', false), 'VIL_TDX_transaction.csv', $sum_bal, $count_bal)
		);

	$headings = array();

	if ($dest == 's')
	{
		$screen = "
		<p style=\"font-size:18px; font-weight:bold;\">
			$title
			&nbsp;&nbsp;&nbsp;&nbsp;
			" . input_button('Export to Excel', "export_xl()", '', 'but_export_xl') . "
		</p>
		<table class=\"spaced_table\">
		";
		foreach ($top_lines as $line)
		{
			$screen .= "
				<tr>";
			foreach ($line as $field)
				$screen .= "<td>$field</td>";
			$screen .= "</tr>
				";
		}
		if ($headings)
		{
			$screen .= "
			<tr>
				";
				foreach ($headings as $head)
					$screen .= "<th>$head</th>";
				if (user_debug())
					$screen .= "<th $grey>Group ID</th>";
				$screen .= "
			</tr>
			";
		}
		foreach ($datalines as $dline)
		{
			$group = (array_key_exists('CLIENT_GROUP_ID', $dline) ? $dline['CLIENT_GROUP_ID'] : '');
			$screen .= "
			<tr>
				<td>{$dline['JC_TRANS_ID']}</td>
				<td>{$dline['CLIENT_REF']}</td>
				<td>{$dline['JC_TRANS_CNUM']}</td>
				<td>{$dline['C_CO_NAME']}</td>
				<td>{$dline['ALT_NAME']}</td>
				<td $ar>" . money_format_kdb($dline['BALANCE'], true, true, true) . "</td>
				<td $ar>{$dline['J_CLOSED_DT']}</td>
				<td>{$dline['PREV_TRANS']}</td>
				<td>{$dline['TRI_CODE']}</td>
				" . (user_debug() ? "<td $grey>$group</td>" : '') . "
			</tr>
			";
		}
		$screen .= "
		</table>
		";
		printD($screen);
		dprint("There were $blank_tid records with a Blank Transaction ID (from a total of $nlines records)", true);
	}
	elseif ($dest == 'x')
	{
		$top_lines = array_merge(array(array($title)), array(array()), $top_lines);
		#dprint("TL=" . print_r($top_lines,1));#
		#dprint("DL=" . print_r($datalines,1));#
		$formats = array('F' => $excel_currency_format, 'G' => $excel_date_format);
		$sheet = array($title_short, $top_lines, $headings, $datalines, $formats, '');
		phpExcel_output($reps_subdir, $xfile, array($sheet)); # library.php

		# Auto-download file to PC
		printD("
		<script type=\"text/javascript\">
			document.form_csv_download.short_fname.value = '$xfile.$phpExcel_ext';
			document.form_csv_download.full_fname.value = '$csv_path' + '{$reps_subdir}/$xfile.$phpExcel_ext';
			document.form_csv_download.submit();
		</script>
		");
	}
	else
		dlog("$this_function: *=* bad dest \"$dest\"");

} # rep_cf_tdx_close()

function rep_cf_tdx_recon($dest, $sc_date_fr, $sc_date_to, $sc_client, $sc_group, $xfile='')
{
	# Collections Report: TDX Reconciliation (Activity) Report.
	# $dest is 's' for screen or 'x' for export to Excel file.
	# $sc_date_fr and $sc_date_to are search criteria that form a date range which
	#	activity dates must fall within; either may be blank.
	# $sc_client is search criteria: a client code or part of a client name (or an alpha code)
	# $sc_group is search criteria: a client group ID
	# $xfile is the name of the file that the search results should be written to;
	#		it is used when $dest=='x'

	global $ar;
	global $csv_path;
	global $excel_currency_format;
	global $excel_date_format;
	global $grey;
	global $phpExcel_ext; # settings.php: "xls"
	global $sqlFalse;
	global $sqlTrue;
	global $reps_subdir;

	$this_function = "rep_cf_tdx_recon(\"$dest\", \"$sc_date_fr\", \"$sc_date_to\", \"$sc_client\", \"$sc_group\", \"$xfile\")";
	#dprint("Entered: $this_function");#

	if ($sc_date_fr)
	{
		if ($sc_date_to)
			$date_string = ", activity between $sc_date_fr and $sc_date_to";
		else
			$date_string = ", activity from $sc_date_fr";
	}
	elseif ($sc_date_to)
		$date_string = ", activity up to $sc_date_to";
	else
		$date_string = "(all activity dates)";

	$sc_c_code = intval($sc_client);
	$c_code_list = array();
	$temp = str_replace(',', '', str_replace(' ', '', $sc_client));
	if (is_numeric_kdb($temp, false, false, false))
		$c_code_list = explode(',', str_replace(' ', '', $sc_client));
	$client_string = '';
	if ($sc_client)
	{
		$client_name = '';
		if ($sc_c_code > 0)
		{
			$client_name = client_name_from_code($sc_c_code);
			if ($client_name)
				$client_name = "$sc_c_code - $client_name";
		}
		if (!$client_name)
			$client_name = $sc_client;
		$client_string .= ", client $client_name";
	}

	$group_id = intval($sc_group);
	if ($group_id > 0)
		$group_string = ", client group \"" . sql_client_group_name_from_id($group_id) . "\"";
	else
		$group_string = '';

	$title_short = "Reconciliation Report";
	$title = "{$title_short}{$date_string}{$client_string}{$group_string}";
	#dprint($title);#

	$where = array("J.OBSOLETE=$sqlFalse", "J.JC_JOB=$sqlTrue");

	$date_c_field = 'JA.JA_DT'; # activity date
	if ($sc_date_fr)
		$where[] = "'" . date_for_sql_nqnt($sc_date_fr) . "' <= $date_c_field";
	if ($sc_date_to)
		$where[] = "$date_c_field < '" . date_for_sql_nqnt($sc_date_to, true) . "'"; # NOT "<="

	if ($sc_client)
	{
		$where[] = "C.CLIENT2_ID > 0";
		if (count($c_code_list) > 1)
			$where[] = "C.C_CODE IN (" . implode(',', $c_code_list) . ")";
		elseif ($sc_c_code > 0)
			$where[] = "C.C_CODE = $sc_c_code";
		else
		{
			$sc_client2_id = (($sc_client[0] == '*') ? intval(trim(str_replace('*', '', $sc_client))) : 0);
			if ($sc_client2_id > 0)
				$where[] = "C.CLIENT2_ID = $sc_client2_id";
			else
			{
				sql_encryption_preparation('CLIENT2');
				$sc_client = addslashes_kdb($sc_client);
				$sql = "SELECT CLIENT2_ID FROM CLIENT2 WHERE " . sql_decrypt('C_CO_NAME') . " LIKE '%{$sc_client}%' OR " .
							"(ALPHA_CODE='" . addslashes_kdb($sc_client) . "')";
				#dprint($sql);#
				sql_execute($sql);
				$cids = array();
				while (($newArray = sql_fetch()) != false)
					$cids[] = $newArray[0];
				if ($cids)
					$where[] = "C.CLIENT2_ID IN (" . implode(',', $cids) . ")";
				else
					dprint("sc_client \"$sc_client\" yielded no client_id(s)");
			}
		}
	}
	if ($group_id > 0)
	{
		$where[] = "G.CLIENT_GROUP_ID > 0";
		$where[] = "G.CLIENT_GROUP_ID=$group_id";
		$group_select = ", G.CLIENT_GROUP_ID";
		$group_join = " LEFT JOIN CLIENT_GROUP G ON G.CLIENT_GROUP_ID=C.CLIENT_GROUP_ID ";
	}
	else
	{
		$group_select = '';
		$group_join = '';
	}

	if ($where)
		$where = "WHERE (" . implode(') AND (', $where) . ")";
	else
		$where = '';

	sql_encryption_preparation('CLIENT2');
	sql_encryption_preparation('JOB');
	$sql = "
		SELECT JA.JOB_ACT_ID, JA.JA_DT, A.ACT_TDX,
			C.CLIENT2_ID, J.JC_TOTAL_AMT - J.JC_PAID_SO_FAR AS BALANCE, " . sql_decrypt('J.CLIENT_REF', '', true) . "
			$group_select
		FROM JOB_ACT AS JA
		LEFT JOIN JOB AS J ON J.JOB_ID=JA.JOB_ID
		LEFT JOIN CLIENT2 AS C ON C.CLIENT2_ID=J.CLIENT2_ID
		LEFT JOIN ACTIVITY_SD AS A ON A.ACTIVITY_ID=JA.ACTIVITY_ID
		$group_join
		$where
		ORDER BY " . sql_decrypt('J.CLIENT_REF') . ", JA.JA_DT
		";
	if ($dest == 's') dprint($sql);#
	sql_execute($sql);
	$lines = array(); # payments
	$clients = array();
	$cids = array();
	while (($newArray = sql_fetch_assoc()) != false)
	{
		#$bits = explode(' ', $newArray['JA_DT']);
		#$newArray['JA_DT'] = $bits[0]; # drop the time portion
		$lines[] = $newArray;
		if (!in_array($newArray['CLIENT2_ID'], $cids))
		{
			$cids[] = $newArray['CLIENT2_ID'];
			$clients[$newArray['CLIENT2_ID']] = '';
		}
	}
	#dprint("lines=" . print_r($lines,1) . "<br>clients/1=" . print_r($clients,1));#

	if (count($lines) == 0)
	{
		dprint("No Activities were found with the given criteria.", true);
		return;
	}

	sql_encryption_preparation('CLIENT2');
	$sql = "SELECT CLIENT2_ID, C_CODE, " . sql_decrypt('C_CO_NAME', '', true) . " FROM CLIENT2
			WHERE CLIENT2_ID IN (" . implode(',', $cids) . ")";
	#if ($dest == 's') dprint($sql);#
	sql_execute($sql);
	$clients = array();
	$the_client = '';
	$the_code = '';
	while (($newArray = sql_fetch_assoc()) != false)
	{
		if (!$the_client)
		{
			$the_client = $newArray['C_CO_NAME'];
			$the_code = $newArray['C_CODE'];
		}
		$clients[$newArray['CLIENT2_ID']] = array('C_CODE' => $newArray['C_CODE'],
				'C_CO_NAME' => $newArray['C_CO_NAME'], 'ALT_NAME' => 'TXT Loan');
	}
	if (!$the_client)
	{
		$the_client = '(none)';
		$the_code = '-';
	}
	#dprint("theClient=\"$the_client\", the_code=\"$the_code\", clients/2=" . print_r($clients,1));#

	$datalines = array();
	$nlines = count($lines);
	$sum_bal = 0.0;
	$count_bal = 0;
	for ($ii=0 ; $ii < $nlines; $ii++)
	{
		$line = $lines[$ii];
		$aid = intval($line['JOB_ACT_ID']);
		$datalines[$aid] = array();
		$datalines[$aid]['CLIENT_REF'] = $line['CLIENT_REF'];
		$datalines[$aid]['C_CO_NAME'] = $clients[$line['CLIENT2_ID']]['C_CO_NAME'];
		$datalines[$aid]['HDO'] = 'HDO';
		$datalines[$aid]['ACT_TDX'] = $line['ACT_TDX'];
		$datalines[$aid]['JA_DT'] = date_for_sql($line['JA_DT'], true, true, false, false, false, false, false, true);
		$datalines[$aid]['BLANK_1'] = '';
		$datalines[$aid]['BLANK_2'] = '';

		$bal = floatval($line['BALANCE']);
		$datalines[$aid]['BALANCE'] = $bal;
		$sum_bal += $bal;
		$count_bal++;
	}
	#dprint("datalines=" . print_r($datalines,1));#

	$top_lines = array(
		array('DCAReconciliation', 'VILCOL', date_now(true, '', false), 'VIL_TDX_recon.csv', sprintf("%.2f",$sum_bal), $count_bal)
		);

	$headings = array();

	if ($dest == 's')
	{
		$screen = "
		<p style=\"font-size:18px; font-weight:bold;\">
			$title
			&nbsp;&nbsp;&nbsp;&nbsp;
			" . input_button('Export to Excel', "export_xl()", '', 'but_export_xl') . "
		</p>
		<table class=\"spaced_table\">
		";
		foreach ($top_lines as $line)
		{
			$screen .= "
				<tr>";
			foreach ($line as $field)
				$screen .= "<td>$field</td>";
			$screen .= "</tr>
				";
		}
		if ($headings)
		{
			$screen .= "
			<tr>
				";
				foreach ($headings as $head)
					$screen .= "<th>$head</th>";
				if (user_debug())
					$screen .= "<th $grey>Group ID</th>";
				$screen .= "
			</tr>
			";
		}
		foreach ($datalines as $dline)
		{
			$group = (array_key_exists('CLIENT_GROUP_ID', $dline) ? $dline['CLIENT_GROUP_ID'] : '');
			$screen .= "
			<tr>
				<td>{$dline['CLIENT_REF']}</td>
				<td>{$dline['C_CO_NAME']}</td>
				<td>{$dline['HDO']}</td>
				<td>{$dline['ACT_TDX']}</td>
				<td $ar>{$dline['JA_DT']}</td>
				<td>{$dline['BLANK_1']}</td>
				<td>{$dline['BLANK_2']}</td>
				<td $ar>" . money_format_kdb($dline['BALANCE'], true, true, true) . "</td>
				" . (user_debug() ? "<td $grey>$group</td>" : '') . "
			</tr>
			";
		}
		$screen .= "
		</table>
		";
		printD($screen);
	}
	elseif ($dest == 'x')
	{
		$top_lines = array_merge(array(array($title)), array(array()), $top_lines);
		#dprint("TL=" . print_r($top_lines,1));#
		#dprint("DL=" . print_r($datalines,1));#
		$formats = array('E' => $excel_date_format, 'H' => $excel_currency_format);
		$sheet = array($title_short, $top_lines, $headings, $datalines, $formats, '');
		phpExcel_output($reps_subdir, $xfile, array($sheet)); # library.php

		# Auto-download file to PC
		printD("
		<script type=\"text/javascript\">
			document.form_csv_download.short_fname.value = '$xfile.$phpExcel_ext';
			document.form_csv_download.full_fname.value = '$csv_path' + '{$reps_subdir}/$xfile.$phpExcel_ext';
			document.form_csv_download.submit();
		</script>
		");
	}
	else
		dlog("$this_function: *=* bad dest \"$dest\"");

} # rep_cf_tdx_recon()

function rep_cf_sigma_trans($dest, $sc_date_fr, $sc_date_to, $sc_client, $sc_group, $xfile='')
{
	# Collections Report: Sigma Transaction Report.
	# $dest is 's' for screen or 'x' for export to Excel file.
	# $sc_date_fr and $sc_date_to are search criteria that form a date range which
	#	collection payment must fall within; either may be blank.
	# $sc_client is search criteria: a client code or part of a client name (or an alpha code)
	# $sc_group is search criteria: a client group ID
	# $xfile is the name of the file that the search results should be written to;
	#		it is used when $dest=='x'

	global $ar;
	global $csv_path;
	global $excel_currency_format;
	global $excel_date_format;
	global $excel_decimal_format;
	global $excel_integer_format;
	global $grey;
	global $phpExcel_ext; # settings.php: "xls"
	global $sigma_code_trans;
	global $sqlFalse;
	global $sqlTrue;
	global $reps_subdir;

	$this_function = "rep_cf_sigma_trans(\"$dest\", \"$sc_date_fr\", \"$sc_date_to\", \"$sc_client\", \"$sc_group\", \"$xfile\")";
	#dprint("Entered: $this_function");#

	if ($sc_date_fr)
	{
		if ($sc_date_to)
			$date_string = ", payments between $sc_date_fr and $sc_date_to";
		else
			$date_string = ", payments from $sc_date_fr";
	}
	elseif ($sc_date_to)
		$date_string = ", payments up to $sc_date_to";
	else
		$date_string = "(all payment dates)";

	$sc_c_code = intval($sc_client);
	$c_code_list = array();
	$temp = str_replace(',', '', str_replace(' ', '', $sc_client));
	if (is_numeric_kdb($temp, false, false, false))
		$c_code_list = explode(',', str_replace(' ', '', $sc_client));
	$client_string = '';
	if ($sc_client)
	{
		$client_name = '';
		if ($sc_c_code > 0)
		{
			$client_name = client_name_from_code($sc_c_code);
			if ($client_name)
				$client_name = "$sc_c_code - $client_name";
		}
		if (!$client_name)
			$client_name = $sc_client;
		$client_string .= ", client $client_name";
	}

	$group_id = intval($sc_group);
	if ($group_id > 0)
		$group_string = ", client group \"" . sql_client_group_name_from_id($group_id) . "\"";
	else
		$group_string = '';

	$title_short = "Transaction Report";
	$title = "{$title_short}{$date_string}{$client_string}{$group_string}";
	#dprint($title);#

	$where = array("J.OBSOLETE=$sqlFalse", "J.JC_JOB=$sqlTrue");

	$date_c_field = 'P.COL_DT_RX'; # collection (payment) date
	if ($sc_date_fr)
		$where[] = "'" . date_for_sql_nqnt($sc_date_fr) . "' <= $date_c_field";
	if ($sc_date_to)
		$where[] = "$date_c_field < '" . date_for_sql_nqnt($sc_date_to, true) . "'"; # NOT "<="

	if ($sc_client)
	{
		$where[] = "C.CLIENT2_ID > 0";
		if (count($c_code_list) > 1)
			$where[] = "C.C_CODE IN (" . implode(',', $c_code_list) . ")";
		elseif ($sc_c_code > 0)
			$where[] = "C.C_CODE = $sc_c_code";
		else
		{
			$sc_client2_id = (($sc_client[0] == '*') ? intval(trim(str_replace('*', '', $sc_client))) : 0);
			if ($sc_client2_id > 0)
				$where[] = "C.CLIENT2_ID = $sc_client2_id";
			else
			{
				sql_encryption_preparation('CLIENT2');
				$sc_client = addslashes_kdb($sc_client);
				$sql = "SELECT CLIENT2_ID FROM CLIENT2 WHERE " . sql_decrypt('C_CO_NAME') . " LIKE '%{$sc_client}%' OR " .
							"(ALPHA_CODE='" . addslashes_kdb($sc_client) . "')";
				#dprint($sql);#
				sql_execute($sql);
				$cids = array();
				while (($newArray = sql_fetch()) != false)
					$cids[] = $newArray[0];
				if ($cids)
					$where[] = "C.CLIENT2_ID IN (" . implode(',', $cids) . ")";
				else
					dprint("sc_client \"$sc_client\" yielded no client_id(s)");
			}
		}
	}
	if ($group_id > 0)
	{
		$where[] = "G.CLIENT_GROUP_ID > 0";
		$where[] = "G.CLIENT_GROUP_ID=$group_id";
		$group_select = ", G.CLIENT_GROUP_ID";
		$group_join = " LEFT JOIN CLIENT_GROUP G ON G.CLIENT_GROUP_ID=C.CLIENT_GROUP_ID ";
	}
	else
	{
		$group_select = '';
		$group_join = '';
	}
	$where[] = "P.OBSOLETE=$sqlFalse";

	if ($where)
		$where = "WHERE (" . implode(') AND (', $where) . ")";
	else
		$where = '';

	sql_encryption_preparation('CLIENT2');
	sql_encryption_preparation('JOB');
	$sql = "
		SELECT P.JOB_PAYMENT_ID, P.COL_AMT_RX, P.COL_DT_RX, P.COL_PERCENT, P.COL_BOUNCED,
			C.CLIENT2_ID, PM.TDX_CODE, J.JC_TRANS_ID, J.JC_TRANS_CNUM,
			J.JC_TOTAL_AMT - J.JC_PAID_SO_FAR AS BALANCE, " . sql_decrypt('J.CLIENT_REF', '', true) . "
			$group_select
		FROM JOB_PAYMENT AS P
		LEFT JOIN JOB AS J ON J.JOB_ID=P.JOB_ID
		LEFT JOIN CLIENT2 AS C ON C.CLIENT2_ID=J.CLIENT2_ID
		LEFT JOIN PAYMENT_METHOD_SD AS PM ON PM.PAYMENT_METHOD_ID=P.COL_PAYMENT_METHOD_ID
		$group_join
		$where
		ORDER BY " . sql_decrypt('J.CLIENT_REF') . ", P.COL_DT_RX
		";
	if ($dest == 's') dprint($sql);#
	sql_execute($sql);
	$lines = array(); # payments
	$clients = array();
	$cids = array();
	while (($newArray = sql_fetch_assoc()) != false)
	{
		$bits = explode(' ', $newArray['COL_DT_RX']);
		$newArray['COL_DT_RX'] = $bits[0]; # drop the time portion
		$lines[] = $newArray;
		if (!in_array($newArray['CLIENT2_ID'], $cids))
		{
			$cids[] = $newArray['CLIENT2_ID'];
			$clients[$newArray['CLIENT2_ID']] = '';
		}
	}
	#dprint("lines=" . print_r($lines,1) . "<br>clients/1=" . print_r($clients,1));#

	if (count($lines) == 0)
	{
		dprint("No Payments were found with the given criteria.", true);
		return;
	}

	sql_encryption_preparation('CLIENT2');
	$sql = "SELECT CLIENT2_ID, C_CODE, " . sql_decrypt('C_CO_NAME', '', true) . " FROM CLIENT2
			WHERE CLIENT2_ID IN (" . implode(',', $cids) . ")";
	#if ($dest == 's') dprint($sql);#
	sql_execute($sql);
	$clients = array();
	$the_client = '';
	$the_code = '';
	while (($newArray = sql_fetch_assoc()) != false)
	{
		if (!$the_client)
		{
			$the_client = $newArray['C_CO_NAME'];
			$the_code = $newArray['C_CODE'];
		}
		$clients[$newArray['CLIENT2_ID']] = array('C_CODE' => $newArray['C_CODE'],
				'C_CO_NAME' => $newArray['C_CO_NAME'], 'ALT_NAME' => 'TXT Loan');
	}
	if (!$the_client)
	{
		$the_client = '(none)';
		$the_code = '-';
	}
	#dprint("theClient=\"$the_client\", the_code=\"$the_code\", clients/2=" . print_r($clients,1));#

	$datalines = array();
	$nlines = count($lines);
	$sum_pay = 0.0;
	$count_pay = 0;
	$blank_tid = 0;
	for ($ii=0 ; $ii < $nlines; $ii++)
	{
		$line = $lines[$ii];
		$pid = intval($line['JOB_PAYMENT_ID']);
		$datalines[$pid] = array();
		$datalines[$pid]['JC_TRANS_ID'] = $line['JC_TRANS_ID'];
		if ($datalines[$pid]['JC_TRANS_ID'] == '')
			$blank_tid++;
		#$datalines[$pid]['CLIENT_REF'] = $line['CLIENT_REF'];
		#$datalines[$pid]['JC_TRANS_CNUM'] = $line['JC_TRANS_CNUM'];
		#$datalines[$pid]['C_CO_NAME'] = $clients[$line['CLIENT2_ID']]['C_CO_NAME'];
		#$datalines[$pid]['ALT_NAME'] = $clients[$line['CLIENT2_ID']]['C_CO_NAME'];
		#$datalines[$pid]['BALANCE'] = $line['BALANCE'];

		#$last_dt = $line['COL_DT_RX'];
		#for ($jj = $ii+1; $jj < $nlines; $jj++)
		#{
		#	if ($lines[$jj]['CLIENT_REF'] == $line['CLIENT_REF'])
		#		$last_dt = $lines[$jj]['COL_DT_RX'];
		#}
		#$datalines[$pid]['LAST_DT'] = date_for_sql($last_dt, true, false);
		$datalines[$pid]['COL_AMT_RX'] = $line['COL_AMT_RX'];
		$datalines[$pid]['COL_DT_RX'] = date_for_sql($line['COL_DT_RX'], true, false);
		$sum_pay += floatval($line['COL_AMT_RX']);
		$count_pay++;

		#$datalines[$pid]['PAY_REV'] = ($line['COL_BOUNCED'] ? 'REV' : 'PAY');
		$datalines[$pid]['PAYMENT_METHOD'] = $line['TDX_CODE'];
		#$datalines[$pid]['CURRENCY'] = 'GBP';
		#$datalines[$pid]['COMM_AMOUNT'] = 0.01 * $line['COL_PERCENT'] * $line['COL_AMT_RX'];
		#$datalines[$pid]['COMM_FRACTION'] = 0.01 * $line['COL_PERCENT'];
		#$datalines[$pid]['ZERO'] = 0;
	}
	#dprint("datalines=" . print_r($datalines,1));#

	$top_lines = array(
		array(strftime("%Y%m%d") . "_Vilcol_Transactions.csv|{$sigma_code_trans}|" . date_now(true, '', false) . "|" . $count_pay)
		);

	$headings = array();

	if ($dest == 's')
	{
		$screen = "
		<p style=\"font-size:18px; font-weight:bold;\">
			$title
			&nbsp;&nbsp;&nbsp;&nbsp;
			" . input_button('Export to Excel', "export_xl()", '', 'but_export_xl') . "
		</p>
		<table class=\"spaced_table\">
		";
		foreach ($top_lines as $line)
		{
			$screen .= "
				<tr>";
			foreach ($line as $field)
				$screen .= "<td>$field</td>";
			$screen .= "</tr>
				";
		}
		if ($headings)
		{
			$screen .= "
			<tr>
				";
				foreach ($headings as $head)
					$screen .= "<th>$head</th>";
				if (user_debug())
					$screen .= "<th $grey>Group ID</th>";
				$screen .= "
			</tr>
			";
		}
		foreach ($datalines as $dline)
		{
			$group = (array_key_exists('CLIENT_GROUP_ID', $dline) ? $dline['CLIENT_GROUP_ID'] : '');
			$screen .= "
			<tr>
				<td>{$dline['JC_TRANS_ID']}</td>
				<td $ar>{$dline['COL_DT_RX']}</td>
				<td>{$dline['PAYMENT_METHOD']}</td>
				<td $ar>-" . number_format($dline['COL_AMT_RX'], 2) . "</td>
				" . (user_debug() ? "<td $grey>$group</td>" : '') . "
			</tr>
			";
		}
		$screen .= "
		</table>
		";
		printD($screen);
		dprint("There were $blank_tid records with a Blank Transaction ID (from a total of $nlines records)", true);
	}
	elseif ($dest == 'x')
	{
		$top_lines = array_merge(array(array($title)), array(array()), $top_lines);
		#dprint("TL=" . print_r($top_lines,1));#
		#dprint("DL=" . print_r($datalines,1));#
		$formats = array('F' => $excel_currency_format, 'G' => $excel_date_format, 'H' => $excel_currency_format, 'I' => $excel_date_format,
						'M' => $excel_currency_format, 'N' => $excel_decimal_format, 'O' => $excel_integer_format);
		$sheet = array($title_short, $top_lines, $headings, $datalines, $formats, '');
		phpExcel_output($reps_subdir, $xfile, array($sheet)); # library.php

		# Auto-download file to PC
		printD("
		<script type=\"text/javascript\">
			document.form_csv_download.short_fname.value = '$xfile.$phpExcel_ext';
			document.form_csv_download.full_fname.value = '$csv_path' + '{$reps_subdir}/$xfile.$phpExcel_ext';
			document.form_csv_download.submit();
		</script>
		");
	}
	else
		dlog("$this_function: *=* bad dest \"$dest\"");

} # rep_cf_sigma_trans()

function rep_cf_sigma_dial($dest, $sc_date_fr, $sc_date_to, $sc_client, $sc_group, $xfile='')
{
	# Collections Report: Sigma Non-Dialler Events Report.
	# $dest is 's' for screen or 'x' for export to Excel file.
	# $sc_date_fr and $sc_date_to are search criteria that form a date range which
	#	activity events must fall within; either may be blank.
	# $sc_client is search criteria: a client code or part of a client name (or an alpha code)
	# $sc_group is search criteria: a client group ID
	# $xfile is the name of the file that the search results should be written to;
	#		it is used when $dest=='x'

	global $ar;
	global $csv_path;
	global $excel_date_format;
	global $grey;
	global $phpExcel_ext; # settings.php: "xls"
	global $sigma_code_dial;
	global $sqlFalse;
	global $sqlTrue;
	global $reps_subdir;

	$this_function = "rep_cf_sigma_dial(\"$dest\", \"$sc_date_fr\", \"$sc_date_to\", \"$sc_client\", \"$sc_group\", \"$xfile\")";
	#dprint("Entered: $this_function");#

	if ($sc_date_fr)
	{
		if ($sc_date_to)
			$date_string = ", activity between $sc_date_fr and $sc_date_to";
		else
			$date_string = ", activity from $sc_date_fr";
	}
	elseif ($sc_date_to)
		$date_string = ", activity up to $sc_date_to";
	else
		$date_string = "(all activity dates)";

	$sc_c_code = intval($sc_client);
	$c_code_list = array();
	$temp = str_replace(',', '', str_replace(' ', '', $sc_client));
	if (is_numeric_kdb($temp, false, false, false))
		$c_code_list = explode(',', str_replace(' ', '', $sc_client));
	$client_string = '';
	if ($sc_client)
	{
		$client_name = '';
		if ($sc_c_code > 0)
		{
			$client_name = client_name_from_code($sc_c_code);
			if ($client_name)
				$client_name = "$sc_c_code - $client_name";
		}
		if (!$client_name)
			$client_name = $sc_client;
		$client_string .= ", client $client_name";
	}

	$group_id = intval($sc_group);
	if ($group_id > 0)
		$group_string = ", client group \"" . sql_client_group_name_from_id($group_id) . "\"";
	else
		$group_string = '';

	$title_short = "Non-Dialler Events Report";
	$title = "{$title_short}{$date_string}{$client_string}{$group_string}";
	#dprint($title);#

	$where = array("J.OBSOLETE=$sqlFalse", "J.JC_JOB=$sqlTrue");

	$date_c_field = 'JA.JA_DT'; # activity date
	if ($sc_date_fr)
		$where[] = "'" . date_for_sql_nqnt($sc_date_fr) . "' <= $date_c_field";
	if ($sc_date_to)
		$where[] = "$date_c_field < '" . date_for_sql_nqnt($sc_date_to, true) . "'"; # NOT "<="

	if ($sc_client)
	{
		$where[] = "C.CLIENT2_ID > 0";
		if (count($c_code_list) > 1)
			$where[] = "C.C_CODE IN (" . implode(',', $c_code_list) . ")";
		elseif ($sc_c_code > 0)
			$where[] = "C.C_CODE = $sc_c_code";
		else
		{
			$sc_client2_id = (($sc_client[0] == '*') ? intval(trim(str_replace('*', '', $sc_client))) : 0);
			if ($sc_client2_id > 0)
				$where[] = "C.CLIENT2_ID = $sc_client2_id";
			else
			{
				sql_encryption_preparation('CLIENT2');
				$sc_client = addslashes_kdb($sc_client);
				$sql = "SELECT CLIENT2_ID FROM CLIENT2 WHERE " . sql_decrypt('C_CO_NAME') . " LIKE '%{$sc_client}%' OR " .
							"(ALPHA_CODE='" . addslashes_kdb($sc_client) . "')";
				#dprint($sql);#
				sql_execute($sql);
				$cids = array();
				while (($newArray = sql_fetch()) != false)
					$cids[] = $newArray[0];
				if ($cids)
					$where[] = "C.CLIENT2_ID IN (" . implode(',', $cids) . ")";
				else
					dprint("sc_client \"$sc_client\" yielded no client_id(s)");
			}
		}
	}
	if ($group_id > 0)
	{
		$where[] = "G.CLIENT_GROUP_ID > 0";
		$where[] = "G.CLIENT_GROUP_ID=$group_id";
		$group_select = ", G.CLIENT_GROUP_ID";
		$group_join = " LEFT JOIN CLIENT_GROUP G ON G.CLIENT_GROUP_ID=C.CLIENT_GROUP_ID ";
	}
	else
	{
		$group_select = '';
		$group_join = '';
	}
	$where[] = "A.DIALLER_EV=$sqlFalse";

	if ($where)
		$where = "WHERE (" . implode(') AND (', $where) . ")";
	else
		$where = '';

	sql_encryption_preparation('CLIENT2');
	sql_encryption_preparation('JOB');
	$sql = "
		SELECT JA.JOB_ACT_ID, JA.JA_DT, A.ACT_DSHORT, J.JC_TRANS_ID,
			C.CLIENT2_ID, J.JC_TOTAL_AMT - J.JC_PAID_SO_FAR AS BALANCE, " . sql_decrypt('J.CLIENT_REF', '', true) . "
			$group_select
		FROM JOB_ACT AS JA
		LEFT JOIN JOB AS J ON J.JOB_ID=JA.JOB_ID
		LEFT JOIN CLIENT2 AS C ON C.CLIENT2_ID=J.CLIENT2_ID
		LEFT JOIN ACTIVITY_SD AS A ON A.ACTIVITY_ID=JA.ACTIVITY_ID
		$group_join
		$where
		ORDER BY " . sql_decrypt('J.CLIENT_REF') . ", JA.JA_DT
		";
	if ($dest == 's') dprint($sql);#
	sql_execute($sql);
	$lines = array(); # payments
	$clients = array();
	$cids = array();
	while (($newArray = sql_fetch_assoc()) != false)
	{
		$bits = explode(' ', $newArray['JA_DT']);
		$newArray['JA_DT'] = $bits[0]; # drop the time portion
		$lines[] = $newArray;
		if (!in_array($newArray['CLIENT2_ID'], $cids))
		{
			$cids[] = $newArray['CLIENT2_ID'];
			$clients[$newArray['CLIENT2_ID']] = '';
		}
	}
	#dprint("lines=" . print_r($lines,1) . "<br>clients/1=" . print_r($clients,1));#

	if (count($lines) == 0)
	{
		dprint("No Activities were found with the given criteria.", true);
		return;
	}

	sql_encryption_preparation('CLIENT2');
	$sql = "SELECT CLIENT2_ID, C_CODE, " . sql_decrypt('C_CO_NAME', '', true) . " FROM CLIENT2
			WHERE CLIENT2_ID IN (" . implode(',', $cids) . ")";
	#if ($dest == 's') dprint($sql);#
	sql_execute($sql);
	$clients = array();
	$the_client = '';
	$the_code = '';
	while (($newArray = sql_fetch_assoc()) != false)
	{
		if (!$the_client)
		{
			$the_client = $newArray['C_CO_NAME'];
			$the_code = $newArray['C_CODE'];
		}
		$clients[$newArray['CLIENT2_ID']] = array('C_CODE' => $newArray['C_CODE'],
				'C_CO_NAME' => $newArray['C_CO_NAME'], 'ALT_NAME' => 'TXT Loan');
	}
	if (!$the_client)
	{
		$the_client = '(none)';
		$the_code = '-';
	}
	#dprint("theClient=\"$the_client\", the_code=\"$the_code\", clients/2=" . print_r($clients,1));#

	$datalines = array();
	$nlines = count($lines);
	$blank_tid = 0;
	$count_bal = 0;
	for ($ii=0 ; $ii < $nlines; $ii++)
	{
		$line = $lines[$ii];
		$aid = intval($line['JOB_ACT_ID']);
		$datalines[$aid] = array();
		$datalines[$aid]['JC_TRANS_ID'] = $line['JC_TRANS_ID'];
		if ($datalines[$aid]['JC_TRANS_ID'] == '')
			$blank_tid++;
		$datalines[$aid]['JA_DT'] = date_for_sql($line['JA_DT'], true, true, false, false, false, false, false, true);
		$datalines[$aid]['ACT_DSHORT'] = $line['ACT_DSHORT'];
		$count_bal++;
	}
	#dprint("datalines=" . print_r($datalines,1));#

	$top_lines = array(
		array(strftime("%Y%m%d") . "_Vilcol_Transactions.csv|{$sigma_code_dial}|" . date_now(true, '', false) . "|" . $count_bal)
		);

	$headings = array();

	if ($dest == 's')
	{
		$screen = "
		<p style=\"font-size:18px; font-weight:bold;\">
			$title
			&nbsp;&nbsp;&nbsp;&nbsp;
			" . input_button('Export to Excel', "export_xl()", '', 'but_export_xl') . "
		</p>
		<table class=\"spaced_table\">
		";
		foreach ($top_lines as $line)
		{
			$screen .= "
				<tr>";
			foreach ($line as $field)
				$screen .= "<td>$field</td>";
			$screen .= "</tr>
				";
		}
		if ($headings)
		{
			$screen .= "
			<tr>
				";
				foreach ($headings as $head)
					$screen .= "<th>$head</th>";
				if (user_debug())
					$screen .= "<th $grey>Group ID</th>";
				$screen .= "
			</tr>
			";
		}
		foreach ($datalines as $dline)
		{
			$group = (array_key_exists('CLIENT_GROUP_ID', $dline) ? $dline['CLIENT_GROUP_ID'] : '');
			$screen .= "
			<tr>
				<td>{$dline['JC_TRANS_ID']}</td>
				<td $ar>{$dline['JA_DT']}</td>
				<td>{$dline['ACT_DSHORT']}</td>
				" . (user_debug() ? "<td $grey>$group</td>" : '') . "
			</tr>
			";
		}
		$screen .= "
		</table>
		";
		printD($screen);
		dprint("There were $blank_tid records with a Blank Transaction ID (from a total of $nlines records)", true);
	}
	elseif ($dest == 'x')
	{
		$top_lines = array_merge(array(array($title)), array(array()), $top_lines);
		#dprint("TL=" . print_r($top_lines,1));#
		#dprint("DL=" . print_r($datalines,1));#
		$formats = array('B' => $excel_date_format);
		$sheet = array($title_short, $top_lines, $headings, $datalines, $formats, '');
		phpExcel_output($reps_subdir, $xfile, array($sheet)); # library.php

		# Auto-download file to PC
		printD("
		<script type=\"text/javascript\">
			document.form_csv_download.short_fname.value = '$xfile.$phpExcel_ext';
			document.form_csv_download.full_fname.value = '$csv_path' + '{$reps_subdir}/$xfile.$phpExcel_ext';
			document.form_csv_download.submit();
		</script>
		");
	}
	else
		dlog("$this_function: *=* bad dest \"$dest\"");

} # rep_cf_sigma_dial()

function rep_cf_sigma_arr($dest, $sc_date_fr, $sc_date_to, $sc_client, $sc_group, $xfile='')
{
	# Collections Report: Sigma Arrangement Report.
	# $dest is 's' for screen or 'x' for export to Excel file.
	# $sc_date_fr and $sc_date_to are search criteria that form a date range which
	#	arrangement start date must fall within; either may be blank.
	# $sc_client is search criteria: a client code or part of a client name (or an alpha code)
	# $sc_group is search criteria: a client group ID
	# $xfile is the name of the file that the search results should be written to;
	#		it is used when $dest=='x'

	global $ar;
	global $csv_path;
	global $excel_currency_format;
	global $excel_date_format;
	global $grey;
	global $phpExcel_ext; # settings.php: "xls"
	global $sigma_code_arr;
	global $sqlFalse;
	global $sqlTrue;
	global $reps_subdir;

	$this_function = "rep_cf_sigma_arr(\"$dest\", \"$sc_date_fr\", \"$sc_date_to\", \"$sc_client\", \"$sc_group\", \"$xfile\")";
	#dprint("Entered: $this_function");#

	if ($sc_date_fr)
	{
		if ($sc_date_to)
			$date_string = ", arrangement starts between $sc_date_fr and $sc_date_to";
		else
			$date_string = ", arrangement starts from $sc_date_fr";
	}
	elseif ($sc_date_to)
		$date_string = ", arrangement starts up to $sc_date_to";
	else
		$date_string = "(all arrangement start dates)";

	$sc_c_code = intval($sc_client);
	$c_code_list = array();
	$temp = str_replace(',', '', str_replace(' ', '', $sc_client));
	if (is_numeric_kdb($temp, false, false, false))
		$c_code_list = explode(',', str_replace(' ', '', $sc_client));
	$client_string = '';
	if ($sc_client)
	{
		$client_name = '';
		if ($sc_c_code > 0)
		{
			$client_name = client_name_from_code($sc_c_code);
			if ($client_name)
				$client_name = "$sc_c_code - $client_name";
		}
		if (!$client_name)
			$client_name = $sc_client;
		$client_string .= ", client $client_name";
	}

	$group_id = intval($sc_group);
	if ($group_id > 0)
		$group_string = ", client group \"" . sql_client_group_name_from_id($group_id) . "\"";
	else
		$group_string = '';

	$title_short = "Arrangement Report";
	$title = "{$title_short}{$date_string}{$client_string}{$group_string}";
	#dprint($title);#

	$where = array("J.OBSOLETE=$sqlFalse", "J.JC_JOB=$sqlTrue");

	$date_c_field = 'J.JC_INSTAL_DT_1'; # arrangement start date
	if ($sc_date_fr)
		$where[] = "'" . date_for_sql_nqnt($sc_date_fr) . "' <= $date_c_field";
	if ($sc_date_to)
		$where[] = "$date_c_field < '" . date_for_sql_nqnt($sc_date_to, true) . "'"; # NOT "<="

	if ($sc_client)
	{
		$where[] = "C.CLIENT2_ID > 0";
		if (count($c_code_list) > 1)
			$where[] = "C.C_CODE IN (" . implode(',', $c_code_list) . ")";
		elseif ($sc_c_code > 0)
			$where[] = "C.C_CODE = $sc_c_code";
		else
		{
			$sc_client2_id = (($sc_client[0] == '*') ? intval(trim(str_replace('*', '', $sc_client))) : 0);
			if ($sc_client2_id > 0)
				$where[] = "C.CLIENT2_ID = $sc_client2_id";
			else
			{
				sql_encryption_preparation('CLIENT2');
				$sc_client = addslashes_kdb($sc_client);
				$sql = "SELECT CLIENT2_ID FROM CLIENT2 WHERE " . sql_decrypt('C_CO_NAME') . " LIKE '%{$sc_client}%' OR " .
							"(ALPHA_CODE='" . addslashes_kdb($sc_client) . "')";
				#dprint($sql);#
				sql_execute($sql);
				$cids = array();
				while (($newArray = sql_fetch()) != false)
					$cids[] = $newArray[0];
				if ($cids)
					$where[] = "C.CLIENT2_ID IN (" . implode(',', $cids) . ")";
				else
					dprint("sc_client \"$sc_client\" yielded no client_id(s)");
			}
		}
	}
	if ($group_id > 0)
	{
		$where[] = "G.CLIENT_GROUP_ID > 0";
		$where[] = "G.CLIENT_GROUP_ID=$group_id";
		$group_select = ", G.CLIENT_GROUP_ID";
		$group_join = " LEFT JOIN CLIENT_GROUP G ON G.CLIENT_GROUP_ID=C.CLIENT_GROUP_ID ";
	}
	else
	{
		$group_select = '';
		$group_join = '';
	}

	if ($where)
		$where = "WHERE (" . implode(') AND (', $where) . ")";
	else
		$where = '';

	sql_encryption_preparation('CLIENT2');
	sql_encryption_preparation('JOB');
	$sql = "
		SELECT C.CLIENT2_ID, J.JOB_ID, " . sql_decrypt('J.CLIENT_REF', '', true) . ", PM.PAYMENT_METHOD, J.JC_TOTAL_AMT, $date_c_field,
			'' AS COLM_5, 0 AS COLM_6, J.JC_INSTAL_FREQ, J.JC_INSTAL_AMT, 0 AS COLM_9
			$group_select
		FROM JOB AS J
		LEFT JOIN CLIENT2 AS C ON C.CLIENT2_ID=J.CLIENT2_ID
		LEFT JOIN PAYMENT_METHOD_SD AS PM ON PM.PAYMENT_METHOD_ID=J.JC_PAYMENT_METHOD_ID
		$group_join
		$where
		ORDER BY " . sql_decrypt('J.CLIENT_REF') . ", $date_c_field
		";
	if ($dest == 's') dprint($sql);#
	sql_execute($sql);
	$lines = array(); # payments
	$clients = array();
	$cids = array();
	while (($newArray = sql_fetch_assoc()) != false)
	{
		$bits = explode(' ', $newArray['JC_INSTAL_DT_1']);
		$newArray['JC_INSTAL_DT_1'] = $bits[0]; # drop the time portion
		$lines[] = $newArray;
		if (!in_array($newArray['CLIENT2_ID'], $cids))
		{
			$cids[] = $newArray['CLIENT2_ID'];
			$clients[$newArray['CLIENT2_ID']] = '';
		}
	}
	#dprint("lines=" . print_r($lines,1) . "<br>clients/1=" . print_r($clients,1));#

	if (count($lines) == 0)
	{
		dprint("No arrangements were found with the given criteria.", true);
		return;
	}

	$datalines = array();
	$nlines = count($lines);
	$count_pay = 0;
	for ($ii=0 ; $ii < $nlines; $ii++)
	{
		$line = $lines[$ii];
		$jid = intval($line['JOB_ID']);
		$datalines[$jid] = array();
		$datalines[$jid]['CLIENT_REF'] = $line['CLIENT_REF'];
		$datalines[$jid]['PAYMENT_METHOD'] = $line['PAYMENT_METHOD'];
		$datalines[$jid]['JC_TOTAL_AMT'] = $line['JC_TOTAL_AMT'];
		$datalines[$jid]['JC_INSTAL_DT_1'] = date_for_sql($line['JC_INSTAL_DT_1'], true, false);
		$datalines[$jid]['COLM_5'] = $line['COLM_5'];
		$datalines[$jid]['COLM_6'] = $line['COLM_6'];
		$datalines[$jid]['JC_INSTAL_FREQ'] = instal_freq_from_code($line['JC_INSTAL_FREQ']);
		$datalines[$jid]['JC_INSTAL_AMT'] = $line['JC_INSTAL_AMT'];
		$datalines[$jid]['COLM_9'] = $line['COLM_9'];
		$count_pay++;
	}
	#dprint("datalines=" . print_r($datalines,1));#

	$top_lines = array(
		array(strftime("%Y%m%d") . "_Vilcol_Arrangements.csv|{$sigma_code_arr}|" . date_now(true, '', false) . "|" . $count_pay)
		);

	$headings = array();

	if ($dest == 's')
	{
		$screen = "
		<p style=\"font-size:18px; font-weight:bold;\">
			$title
			&nbsp;&nbsp;&nbsp;&nbsp;
			" . input_button('Export to Excel', "export_xl()", '', 'but_export_xl') . "
		</p>
		<table class=\"spaced_table\">
		";
		foreach ($top_lines as $line)
		{
			$screen .= "
				<tr>";
			foreach ($line as $field)
				$screen .= "<td>$field</td>";
			$screen .= "</tr>
				";
		}
		if ($headings)
		{
			$screen .= "
			<tr>
				";
				foreach ($headings as $head)
					$screen .= "<th>$head</th>";
				if (user_debug())
					$screen .= "<th $grey>Group ID</th>";
				$screen .= "
			</tr>
			";
		}
		foreach ($datalines as $dline)
		{
			$group = (array_key_exists('CLIENT_GROUP_ID', $dline) ? $dline['CLIENT_GROUP_ID'] : '');
			$screen .= "
			<tr>
				<td>{$dline['CLIENT_REF']}</td>
				<td>{$dline['PAYMENT_METHOD']}</td>
				<td $ar>" . number_format($dline['JC_TOTAL_AMT'], 2) . "</td>
				<td $ar>{$dline['JC_INSTAL_DT_1']}</td>
				<td>{$dline['COLM_5']}</td>
				<td>{$dline['COLM_6']}</td>
				<td>{$dline['JC_INSTAL_FREQ']}</td>
				<td $ar>" . number_format($dline['JC_INSTAL_AMT'], 2) . "</td>
				<td>{$dline['COLM_9']}</td>
				" . (user_debug() ? "<td $grey>$group</td>" : '') . "
			</tr>
			";
		}
		$screen .= "
		</table>
		";
		printD($screen);
	}
	elseif ($dest == 'x')
	{
		$top_lines = array_merge(array(array($title)), array(array()), $top_lines);
		#dprint("TL=" . print_r($top_lines,1));#
		#dprint("DL=" . print_r($datalines,1));#
		$formats = array('C' => $excel_currency_format, 'D' => $excel_date_format, 'H' => $excel_currency_format, 'I' => $excel_currency_format);
		$sheet = array($title_short, $top_lines, $headings, $datalines, $formats, '');
		phpExcel_output($reps_subdir, $xfile, array($sheet)); # library.php

		# Auto-download file to PC
		printD("
		<script type=\"text/javascript\">
			document.form_csv_download.short_fname.value = '$xfile.$phpExcel_ext';
			document.form_csv_download.full_fname.value = '$csv_path' + '{$reps_subdir}/$xfile.$phpExcel_ext';
			document.form_csv_download.submit();
		</script>
		");
	}
	else
		dlog("$this_function: *=* bad dest \"$dest\"");

} # rep_cf_sigma_arr()

function rep_cf_sigma_close($dest, $sc_date_fr, $sc_date_to, $sc_client, $sc_group, $xfile='')
{
	# Collections Report: Sigma Closures Report.
	# $dest is 's' for screen or 'x' for export to Excel file.
	# $sc_date_fr and $sc_date_to are search criteria that form a date range which
	#	arrangement start date must fall within; either may be blank.
	# $sc_client is search criteria: a client code or part of a client name (or an alpha code)
	# $sc_group is search criteria: a client group ID
	# $xfile is the name of the file that the search results should be written to;
	#		it is used when $dest=='x'

	global $ar;
	global $csv_path;
	global $excel_currency_format;
	global $excel_date_format;
	global $grey;
	global $phpExcel_ext; # settings.php: "xls"
	global $sigma_code_arr;
	global $sqlFalse;
	global $sqlTrue;
	global $reps_subdir;

	$this_function = "rep_cf_sigma_close(\"$dest\", \"$sc_date_fr\", \"$sc_date_to\", \"$sc_client\", \"$sc_group\", \"$xfile\")";
	#dprint("Entered: $this_function");#

	if ($sc_date_fr)
	{
		if ($sc_date_to)
			$date_string = ", closures between $sc_date_fr and $sc_date_to";
		else
			$date_string = ", closures from $sc_date_fr";
	}
	elseif ($sc_date_to)
		$date_string = ", closures up to $sc_date_to";
	else
		$date_string = "(all closure dates)";

	$sc_c_code = intval($sc_client);
	$c_code_list = array();
	$temp = str_replace(',', '', str_replace(' ', '', $sc_client));
	if (is_numeric_kdb($temp, false, false, false))
		$c_code_list = explode(',', str_replace(' ', '', $sc_client));
	$client_string = '';
	if ($sc_client)
	{
		$client_name = '';
		if ($sc_c_code > 0)
		{
			$client_name = client_name_from_code($sc_c_code);
			if ($client_name)
				$client_name = "$sc_c_code - $client_name";
		}
		if (!$client_name)
			$client_name = $sc_client;
		$client_string .= ", client $client_name";
	}

	$group_id = intval($sc_group);
	if ($group_id > 0)
		$group_string = ", client group \"" . sql_client_group_name_from_id($group_id) . "\"";
	else
		$group_string = '';

	$title_short = "Closures Report";
	$title = "{$title_short}{$date_string}{$client_string}{$group_string}";
	#dprint($title);#

	$where = array("J.OBSOLETE=$sqlFalse", "J.JC_JOB=$sqlTrue");

	$date_c_field = 'J.J_CLOSED_DT'; # closure date
	if ($sc_date_fr)
		$where[] = "'" . date_for_sql_nqnt($sc_date_fr) . "' <= $date_c_field";
	if ($sc_date_to)
		$where[] = "$date_c_field < '" . date_for_sql_nqnt($sc_date_to, true) . "'"; # NOT "<="

	if ($sc_client)
	{
		$where[] = "C.CLIENT2_ID > 0";
		if (count($c_code_list) > 1)
			$where[] = "C.C_CODE IN (" . implode(',', $c_code_list) . ")";
		elseif ($sc_c_code > 0)
			$where[] = "C.C_CODE = $sc_c_code";
		else
		{
			$sc_client2_id = (($sc_client[0] == '*') ? intval(trim(str_replace('*', '', $sc_client))) : 0);
			if ($sc_client2_id > 0)
				$where[] = "C.CLIENT2_ID = $sc_client2_id";
			else
			{
				sql_encryption_preparation('CLIENT2');
				$sc_client = addslashes_kdb($sc_client);
				$sql = "SELECT CLIENT2_ID FROM CLIENT2 WHERE " . sql_decrypt('C_CO_NAME') . " LIKE '%{$sc_client}%' OR " .
							"(ALPHA_CODE='" . addslashes_kdb($sc_client) . "')";
				#dprint($sql);#
				sql_execute($sql);
				$cids = array();
				while (($newArray = sql_fetch()) != false)
					$cids[] = $newArray[0];
				if ($cids)
					$where[] = "C.CLIENT2_ID IN (" . implode(',', $cids) . ")";
				else
					dprint("sc_client \"$sc_client\" yielded no client_id(s)");
			}
		}
	}
	if ($group_id > 0)
	{
		$where[] = "G.CLIENT_GROUP_ID > 0";
		$where[] = "G.CLIENT_GROUP_ID=$group_id";
		$group_select = ", G.CLIENT_GROUP_ID";
		$group_join = " LEFT JOIN CLIENT_GROUP G ON G.CLIENT_GROUP_ID=C.CLIENT_GROUP_ID ";
	}
	else
	{
		$group_select = '';
		$group_join = '';
	}

	if ($where)
		$where = "WHERE (" . implode(') AND (', $where) . ")";
	else
		$where = '';

	sql_encryption_preparation('CLIENT2');
	sql_encryption_preparation('JOB');
	sql_encryption_preparation('JOB_SUBJECT');
	$sql = "
		SELECT C.CLIENT2_ID, J.JOB_ID, " . sql_decrypt('J.CLIENT_REF', '', true) . ", $date_c_field, S.J_STATUS,
			J.J_VILNO, J.JC_TOTAL_AMT, J.JC_PAID_SO_FAR,
		" . sql_decrypt('JS.JS_FIRSTNAME', '', true) . ", " . sql_decrypt('JS.JS_LASTNAME', '', true) . ", " . sql_decrypt('JS.JS_COMPANY', '', true) . "
			$group_select
		FROM JOB AS J
		LEFT JOIN CLIENT2 AS C ON C.CLIENT2_ID=J.CLIENT2_ID
		LEFT JOIN JOB_STATUS_SD AS S ON S.JOB_STATUS_ID=J.JC_JOB_STATUS_ID
		LEFT JOIN JOB_SUBJECT AS JS ON JS.JOB_ID=J.JOB_ID AND JS.JS_PRIMARY=$sqlTrue AND JS.OBSOLETE=$sqlFalse
		$group_join
		$where
		ORDER BY " . sql_decrypt('J.CLIENT_REF') . ", $date_c_field
		";
	if ($dest == 's') dprint($sql);#
	sql_execute($sql);
	$lines = array(); # payments
	$clients = array();
	$cids = array();
	while (($newArray = sql_fetch_assoc()) != false)
	{
		$bits = explode(' ', $newArray['J_CLOSED_DT']);
		$newArray['J_CLOSED_DT'] = $bits[0]; # drop the time portion
		$lines[] = $newArray;
		if (!in_array($newArray['CLIENT2_ID'], $cids))
		{
			$cids[] = $newArray['CLIENT2_ID'];
			$clients[$newArray['CLIENT2_ID']] = '';
		}
	}
	#dprint("lines=" . print_r($lines,1) . "<br>clients/1=" . print_r($clients,1));#

	if (count($lines) == 0)
	{
		dprint("No closures were found with the given criteria.", true);
		return;
	}

	$datalines = array();
	$nlines = count($lines);
	$count_pay = 0;
	for ($ii=0 ; $ii < $nlines; $ii++)
	{
		$line = $lines[$ii];
		$jid = intval($line['JOB_ID']);
		$datalines[$jid] = array();
		$datalines[$jid]['CLIENT_REF'] = $line['CLIENT_REF'];
		$datalines[$jid]['J_CLOSED_DT'] = date_for_sql($line['J_CLOSED_DT'], true, false);
		$datalines[$jid]['J_STATUS'] = $line['J_STATUS'];
		$datalines[$jid]['J_VILNO'] = $line['J_VILNO'];
		$subject_name = trim("{$line['JS_FIRSTNAME']} {$line['JS_LASTNAME']}");
		if (!$subject_name)
			$subject_name = $line['JS_COMPANY'];
		$datalines[$jid]['SUBJECT_NAME'] = $subject_name;
		$outstanding = floatval($line['JC_TOTAL_AMT']) - floatval($line['JC_PAID_SO_FAR']);
		$datalines[$jid]['OUTSTANDING'] = $outstanding;
		$count_pay++;
	}
	#dprint("datalines=" . print_r($datalines,1));#

	$top_lines = array(
		array(strftime("%Y%m%d") . "_Vilcol_Closures.csv|{$sigma_code_arr}|" . date_now(true, '', false) . "|" . $count_pay)
		);

	$headings = array();

	if ($dest == 's')
	{
		$screen = "
		<p style=\"font-size:18px; font-weight:bold;\">
			$title
			&nbsp;&nbsp;&nbsp;&nbsp;
			" . input_button('Export to Excel', "export_xl()", '', 'but_export_xl') . "
		</p>
		<table class=\"spaced_table\">
		";
		foreach ($top_lines as $line)
		{
			$screen .= "
				<tr>";
			foreach ($line as $field)
				$screen .= "<td>$field</td>";
			$screen .= "</tr>
				";
		}
		if ($headings)
		{
			$screen .= "
			<tr>
				";
				foreach ($headings as $head)
					$screen .= "<th>$head</th>";
				if (user_debug())
					$screen .= "<th $grey>Group ID</th>";
				$screen .= "
			</tr>
			";
		}
		foreach ($datalines as $dline)
		{
			$group = (array_key_exists('CLIENT_GROUP_ID', $dline) ? $dline['CLIENT_GROUP_ID'] : '');
			$screen .= "
			<tr>
				<td>{$dline['CLIENT_REF']}</td>
				<td $ar>{$dline['J_CLOSED_DT']}</td>
				<td>{$dline['J_STATUS']}</td>
				<td $ar>{$dline['J_VILNO']}</td>
				<td>{$dline['SUBJECT_NAME']}</td>
				<td $ar>&pound;{$dline['OUTSTANDING']}</td>
				" . (user_debug() ? "<td $grey>$group</td>" : '') . "
			</tr>
			";
		}
		$screen .= "
		</table>
		";
		printD($screen);
	}
	elseif ($dest == 'x')
	{
		$top_lines = array_merge(array(array($title)), array(array()), $top_lines);
		#dprint("TL=" . print_r($top_lines,1));#
		#dprint("DL=" . print_r($datalines,1));#
		$formats = array('B' => $excel_date_format, 'F' => $excel_currency_format);
		$sheet = array($title_short, $top_lines, $headings, $datalines, $formats, '');
		phpExcel_output($reps_subdir, $xfile, array($sheet)); # library.php

		# Auto-download file to PC
		printD("
		<script type=\"text/javascript\">
			document.form_csv_download.short_fname.value = '$xfile.$phpExcel_ext';
			document.form_csv_download.full_fname.value = '$csv_path' + '{$reps_subdir}/$xfile.$phpExcel_ext';
			document.form_csv_download.submit();
		</script>
		");
	}
	else
		dlog("$this_function: *=* bad dest \"$dest\"");

} # rep_cf_sigma_close()

function rep_cf_sigma_remit($dest, $sc_date_fr, $sc_date_to, $sc_client, $sc_group, $xfile='')
{
	# Collections Report: Sigma Weeky Remittance Report.
	# $dest is 's' for screen or 'x' for export to Excel file.
	# $sc_date_fr and $sc_date_to are search criteria that form a date range which
	#	collection payment must fall within; either may be blank.
	# $sc_client is search criteria: a client code or part of a client name (or an alpha code)
	# $sc_group is search criteria: a client group ID
	# $xfile is the name of the file that the search results should be written to;
	#		it is used when $dest=='x'

	global $ar;
	global $csv_path;
	global $excel_currency_format;
	global $excel_date_format;
	#global $excel_decimal_format;
	global $excel_integer_format;
	global $grey;
	global $id_ROUTE_cspent;
	global $phpExcel_ext; # settings.php: "xls"
	global $sqlFalse;
	global $sqlTrue;
	global $reps_subdir;

	$this_function = "rep_cf_sigma_remit(\"$dest\", \"$sc_date_fr\", \"$sc_date_to\", \"$sc_client\", \"$sc_group\", \"$xfile\")";
	#dprint("Entered: $this_function");#

	if ($sc_date_fr)
	{
		if ($sc_date_to)
			$date_string = ", payments between $sc_date_fr and $sc_date_to";
		else
			$date_string = ", payments from $sc_date_fr";
	}
	elseif ($sc_date_to)
		$date_string = ", payments up to $sc_date_to";
	else
		$date_string = "(all payment dates)";
	$sql_date_fr = ($sc_date_fr ? date_for_sql_nqnt($sc_date_fr) : '');
	$sql_date_to = ($sc_date_to ? date_for_sql_nqnt($sc_date_to, true) : '');

	$sc_c_code = intval($sc_client);
	$c_code_list = array();
	$temp = str_replace(',', '', str_replace(' ', '', $sc_client));
	if (is_numeric_kdb($temp, false, false, false))
		$c_code_list = explode(',', str_replace(' ', '', $sc_client));
	$client_string = '';
	if ($sc_client)
	{
		$client_name = '';
		if ($sc_c_code > 0)
		{
			$client_name = client_name_from_code($sc_c_code);
			if ($client_name)
				$client_name = "$sc_c_code - $client_name";
		}
		if (!$client_name)
			$client_name = $sc_client;
		$client_string .= ", client $client_name";
	}

	$group_id = intval($sc_group);
	if ($group_id > 0)
		$group_string = ", client group \"" . sql_client_group_name_from_id($group_id) . "\"";
	else
		$group_string = '';

	$title_short = "Remittance Report";
	$title = "{$title_short}{$date_string}{$client_string}{$group_string}";
	#dprint($title);#

	# Create an array where there is one day per element in the given date range
	$days = array();
	for ($dt = $sql_date_fr; $dt < $sql_date_to; ) # NOT <=
	{
		$bits = explode(' ', $dt);
		$dt2 = $bits[0]; # drop the time element
		if (!array_key_exists($dt2, $days)) # cope with equinox dalight saving changes
			$days[$dt2] = array('PAY' => 0.0, 'NPAY' => 0, 'REV' => 0.0, 'NREV' => 0);

		$ep = date_to_epoch($dt) + (24 * 60 * 60);
		$dt = date_from_epoch(false, $ep, false, false, true);
	} # for ($dt)
	#dprint("days/1=" . print_r($days,1));#

	# Get the data

	$where = array("J.OBSOLETE=$sqlFalse", "J.JC_JOB=$sqlTrue");

	$date_c_field = 'P.COL_DT_RX'; # collection (payment) date
	if ($sc_date_fr)
		$where[] = "'" . $sql_date_fr . "' <= $date_c_field";
	if ($sc_date_to)
		$where[] = "$date_c_field < '" . $sql_date_to . "'"; # NOT "<="

	if ($sc_client)
	{
		$where[] = "C.CLIENT2_ID > 0";
		if (count($c_code_list) > 1)
			$where[] = "C.C_CODE IN (" . implode(',', $c_code_list) . ")";
		elseif ($sc_c_code > 0)
			$where[] = "C.C_CODE = $sc_c_code";
		else
		{
			$sc_client2_id = (($sc_client[0] == '*') ? intval(trim(str_replace('*', '', $sc_client))) : 0);
			if ($sc_client2_id > 0)
				$where[] = "C.CLIENT2_ID = $sc_client2_id";
			else
			{
				sql_encryption_preparation('CLIENT2');
				$sc_client = addslashes_kdb($sc_client);
				$sql = "SELECT CLIENT2_ID FROM CLIENT2 WHERE " . sql_decrypt('C_CO_NAME') . " LIKE '%{$sc_client}%' OR " .
							"(ALPHA_CODE='" . addslashes_kdb($sc_client) . "')";
				#dprint($sql);#
				sql_execute($sql);
				$cids = array();
				while (($newArray = sql_fetch()) != false)
					$cids[] = $newArray[0];
				if ($cids)
					$where[] = "C.CLIENT2_ID IN (" . implode(',', $cids) . ")";
				else
					dprint("sc_client \"$sc_client\" yielded no client_id(s)");
			}
		}
	}
	if ($group_id > 0)
	{
		$where[] = "G.CLIENT_GROUP_ID > 0";
		$where[] = "G.CLIENT_GROUP_ID=$group_id";
		$group_select = ", G.CLIENT_GROUP_ID";
		$group_join = " LEFT JOIN CLIENT_GROUP G ON G.CLIENT_GROUP_ID=C.CLIENT_GROUP_ID ";
	}
	else
	{
		$group_select = '';
		$group_join = '';
	}
	$where[] = "P.OBSOLETE=$sqlFalse";

	if ($where)
		$where = "WHERE (" . implode(') AND (', $where) . ")";
	else
		$where = '';

	sql_encryption_preparation('CLIENT2');
	sql_encryption_preparation('JOB');
	$sql = "
		SELECT P.COL_AMT_RX, P.COL_DT_RX, P.COL_PAYMENT_ROUTE_ID
			$group_select
		FROM JOB_PAYMENT AS P
		LEFT JOIN JOB AS J ON J.JOB_ID=P.JOB_ID
		LEFT JOIN CLIENT2 AS C ON C.CLIENT2_ID=J.CLIENT2_ID
		$group_join
		$where
		";
	if ($dest == 's') dprint($sql);#
	sql_execute($sql);
//	$lines = array(); # payments
//	$clients = array();
//	$cids = array();
	while (($newArray = sql_fetch_assoc()) != false)
	{
		$bits = explode(' ', $newArray['COL_DT_RX']);
		$dt = $bits[0]; # drop the time portion

		if (array_key_exists($dt, $days))
		{
			if ($newArray['COL_PAYMENT_ROUTE_ID'] == $id_ROUTE_cspent)
			{
				$days[$dt]['REV'] += (-1.0 * floatval($newArray['COL_AMT_RX']));
				$days[$dt]['NREV']++;
			}
			else
			{
				$days[$dt]['PAY'] += (-1.0 * floatval($newArray['COL_AMT_RX']));
				$days[$dt]['NPAY']++;
			}
		}
		else
			dprint("Array key \"$dt\" not found in \$days array!!", true, 'red');

//		$lines[] = $newArray;
//		if (!in_array($newArray['CLIENT2_ID'], $cids))
//		{
//			$cids[] = $newArray['CLIENT2_ID'];
//			$clients[$newArray['CLIENT2_ID']] = '';
//		}
	}
	#dprint("lines=" . print_r($lines,1) . "<br>clients/1=" . print_r($clients,1));#
	#dprint("days/2=" . print_r($days,1));#

//	if (count($lines) == 0)
//	{
//		dprint("No Payments were found with the given criteria.", true);
//		return;
//	}

//	sql_encryption_preparation('CLIENT2');
//	$sql = "SELECT CLIENT2_ID, C_CODE, " . sql_decrypt('C_CO_NAME', '', true) . " FROM CLIENT2
//			WHERE CLIENT2_ID IN (" . implode(',', $cids) . ")";
//	#if ($dest == 's') dprint($sql);#
//	sql_execute($sql);
//	$clients = array();
//	$the_client = '';
//	$the_code = '';
//	while (($newArray = sql_fetch_assoc()) != false)
//	{
//		if (!$the_client)
//		{
//			$the_client = $newArray['C_CO_NAME'];
//			$the_code = $newArray['C_CODE'];
//		}
//		$clients[$newArray['CLIENT2_ID']] = array('C_CODE' => $newArray['C_CODE'],
//				'C_CO_NAME' => $newArray['C_CO_NAME'], 'ALT_NAME' => 'TXT Loan');
//	}
//	if (!$the_client)
//	{
//		$the_client = '(none)';
//		$the_code = '-';
//	}
//	#dprint("theClient=\"$the_client\", the_code=\"$the_code\", clients/2=" . print_r($clients,1));#

	$datalines = array();
	$totals = array('PAY' => 0.0, 'NPAY' => 0, 'REV' => 0.0, 'NREV' => 0, 'BALANCE' => 0.0);
	$dlcount = 0;
	foreach ($days as $dt => $paydata)
	{
		$datalines[$dlcount] = array();
		$datalines[$dlcount]['DATE'] = date_for_sql($dt, true, false);
		$datalines[$dlcount]['PAY'] = number_format($paydata['PAY'], 2);
		$datalines[$dlcount]['NPAY'] = $paydata['NPAY'];
		$datalines[$dlcount]['REV'] = number_format($paydata['REV'], 2);
		$datalines[$dlcount]['NREV'] = $paydata['NREV'];
		$datalines[$dlcount]['BALANCE'] = number_format($paydata['PAY'] + $paydata['REV'], 2);

		$totals['PAY'] += $paydata['PAY'];
		$totals['NPAY'] += $paydata['NPAY'];
		$totals['REV'] += $paydata['REV'];
		$totals['NREV'] += $paydata['NREV'];
		$totals['BALANCE'] += ($paydata['PAY'] + $paydata['REV']);

		$dlcount++;
	}
	$datalines[$dlcount] = array('DATE' => 'Totals',
			'PAY' => $totals['PAY'], 'NPAY' => $totals['NPAY'], 'REV' => $totals['REV'], 'NREV' => $totals['NREV'], 'BALANCE' => $totals['BALANCE']);
	#dprint("datalines=" . print_r($datalines,1));#

	$top_lines = array(
		array('Weekly Remittance Summary')
		);

	$headings = array('Date', 'Payments Value', 'Number of Payments', 'Reversals Value', 'Number of Reversals', 'Total Balance');

	if ($dest == 's')
	{
		$screen = "
		<p style=\"font-size:18px; font-weight:bold;\">
			$title
			&nbsp;&nbsp;&nbsp;&nbsp;
			" . input_button('Export to Excel', "export_xl()", '', 'but_export_xl') . "
		</p>
		<table class=\"spaced_table\">
		";
		foreach ($top_lines as $line)
		{
			$screen .= "
				<tr>";
			foreach ($line as $field)
				$screen .= "<td>$field</td>";
			$screen .= "</tr>
				";
		}
		if ($headings)
		{
			$screen .= "
			<tr>
				";
				foreach ($headings as $head)
					$screen .= "<th>$head</th>";
				if (user_debug())
					$screen .= "<th $grey>Group ID</th>";
				$screen .= "
			</tr>
			";
		}
		foreach ($datalines as $dline)
		{
			$group = (array_key_exists('CLIENT_GROUP_ID', $dline) ? $dline['CLIENT_GROUP_ID'] : '');
			$screen .= "
			<tr>
				<td $ar>{$dline['DATE']}</td>
				<td $ar>{$dline['PAY']}</td>
				<td $ar>{$dline['NPAY']}</td>
				<td $ar>{$dline['REV']}</td>
				<td $ar>{$dline['NREV']}</td>
				<td $ar>{$dline['BALANCE']}</td>
				" . (user_debug() ? "<td $grey>$group</td>" : '') . "
			</tr>
			";
		}
		$screen .= "
		</table>
		";
		printD($screen);
	}
	elseif ($dest == 'x')
	{
		$top_lines = array_merge(array(array($title)), array(array()), $top_lines);
		#dprint("TL=" . print_r($top_lines,1));#
		#dprint("DL=" . print_r($datalines,1));#
		$formats = array('A' => $excel_date_format, 'B' => $excel_currency_format, 'C' => $excel_integer_format,
							'D' => $excel_currency_format, 'E' => $excel_integer_format, 'F' => $excel_currency_format);
		$sheet = array($title_short, $top_lines, $headings, $datalines, $formats, '');
		phpExcel_output($reps_subdir, $xfile, array($sheet)); # library.php

		# Auto-download file to PC
		printD("
		<script type=\"text/javascript\">
			document.form_csv_download.short_fname.value = '$xfile.$phpExcel_ext';
			document.form_csv_download.full_fname.value = '$csv_path' + '{$reps_subdir}/$xfile.$phpExcel_ext';
			document.form_csv_download.submit();
		</script>
		");
	}
	else
		dlog("$this_function: *=* bad dest \"$dest\"");

} # rep_cf_sigma_remit()

function rep_cf_noncol($dest, $sc_date_fr, $sc_date_to, $xfile='')
{
	# Collections Report: Non-Collections report.
	# See specification for details (section 15.17).
	# $dest is 's' for screen or 'x' for export to Excel file.
	# $sc_date_fr and $sc_date_to are search criteria that form a date range which
	#		collection dates must fall within; either may be blank.
	# $xfile is the name of the file that the search results should be written to;
	#		it is used when $dest=='x'

	global $add_phones; # created here; used by get_add_phone()
	global $ar;
	global $csv_path;
	#global $excel_currency_format;
	#global $excel_date_format;
	#global $grey;
	global $id_LETTER_TYPE_contact; # settings.php
	global $id_LETTER_TYPE_demand; # settings.php
	global $id_LETTER_TYPE_letter_1; # settings.php
	global $id_LETTER_TYPE_letter_2; # settings.php
	global $id_LETTER_TYPE_letter_3; # settings.php
	global $id_ROUTE_cspent;
	global $id_ROUTE_direct;
	global $id_ROUTE_fwd;
	global $id_ROUTE_tous;
	global $job_id; # created here; used by get_add_phone()
	global $phpExcel_ext; # settings.php: "xls"
	global $sqlFalse;
	global $sqlTrue;
	global $reps_subdir;

	$this_function = "rep_cf_noncol(\"$dest\", \"$sc_date_fr\", \"$sc_date_to\", \"$xfile\")";
	#dprint("Entered: $this_function");#

	#set_time_limit(1 * 60 * 60); # 1 hour

	if ($sc_date_fr)
	{
		if ($sc_date_to)
			$date_string = ", between $sc_date_fr and $sc_date_to";
		else
			$date_string = ", from $sc_date_fr";
	}
	elseif ($sc_date_to)
		$date_string = ", up to $sc_date_to";
	else
		$date_string = " (all payment dates)";

	$title_short = "Non-Collections Report";
	$title = "{$title_short} for Open jobs{$date_string}";
	#dprint($title);#

	# --- 1. Find all clients who haven't been invoiced in the period

	# 1a. Find clients who have been invoiced in the period
	$date_fr_sql = ($sc_date_fr ? date_for_sql_nqnt($sc_date_fr) : '');
	$date_to_sql = ($sc_date_to ? date_for_sql_nqnt($sc_date_to, true) : '');
	$sql = "SELECT DISTINCT CLIENT2_ID FROM INVOICE WHERE INV_SYS='C' AND INV_TYPE='I' AND 0.0<INV_NET AND OBSOLETE=$sqlFalse";
	if ($date_fr_sql)
		$sql .= " AND '$date_fr_sql' <= INV_DT";
	if ($date_to_sql)
		$sql .= " AND INV_DT < '$date_to_sql'";
	#dprint($sql);#
	sql_execute($sql);
	$clients_inv = array();
	while (($newArray = sql_fetch_assoc()) != false)
		$clients_inv[] = $newArray['CLIENT2_ID'];
	#dprint("Found " . count($clients_inv) . " clients who have been collection-invoiced{$date_string}");#

	# 1b. Invert the set of clients who have been invoiced to get the set who haven't
	$sql = "SELECT CLIENT2_ID FROM CLIENT2 WHERE C_ARCHIVED=$sqlFalse";
	if ($clients_inv)
		$sql .= " AND CLIENT2_ID NOT IN (" . implode(',', $clients_inv) . ")";
	#dprint($sql);#
	sql_execute($sql);
	$clients_not = array();
	while (($newArray = sql_fetch_assoc()) != false)
		$clients_not[] = $newArray['CLIENT2_ID'];
	dprint("Found " . count($clients_not) . " clients who have NOT been collection-invoiced{$date_string}", true);

	# 1c. Find all open jobs for those clients
	$sql = "
		SELECT CLIENT2_ID, JOB_ID FROM JOB WHERE (JC_JOB=$sqlTrue) AND (JOB_CLOSED=$sqlFalse) AND (J_ARCHIVED=$sqlFalse)
			AND (CLIENT2_ID IN (LIST_OF_CLIENTS))
		ORDER BY JOB_ID
		";
	#dprint($sql);#
	$sql = str_replace('LIST_OF_CLIENTS', implode(',', $clients_not), $sql);
	sql_execute($sql);
	$jobs = array();
	$clients_j = array();
	while (($newArray = sql_fetch_assoc()) != false)
	{
		$cid = $newArray['CLIENT2_ID'];
		if (!in_array($cid, $clients_j))
			$clients_j[] = $cid;
		$jobs[] = $newArray['JOB_ID'];
	}
	dprint("Of those clients: found " . count($jobs) . " open collection jobs, belonging to " . count($clients_j) . " clients", true);

	# 1d. Find all open jobs that were either received within the last two years or have been updated within the last two years
	#		or have had activity within the last two years.
	$two_years_ago = "'" . (intval(strftime('%Y')) - 2) . strftime("-%m-%d") . "'";
	$sql = "
		SELECT J.CLIENT2_ID, J.JOB_ID, COUNT(*)
		FROM JOB AS J LEFT JOIN JOB_ACT AS A ON A.JOB_ID=J.JOB_ID
		WHERE (J.JC_JOB=$sqlTrue) AND (J.JOB_CLOSED=$sqlFalse) AND (J.J_ARCHIVED=$sqlFalse) AND (J.CLIENT2_ID IN (LIST_OF_CLIENTS))
			AND ( ($two_years_ago <= J.J_OPENED_DT) OR ($two_years_ago <= J.J_UPDATED_DT) OR ($two_years_ago <= A.JA_DT) )
		GROUP BY J.CLIENT2_ID, J.JOB_ID
		ORDER BY J.JOB_ID
		";
	#dprint($sql);#
	$sql = str_replace('LIST_OF_CLIENTS', implode(',', $clients_not), $sql);
	sql_execute($sql);
	$jobs = array();
	$clients_j = array();
	while (($newArray = sql_fetch_assoc()) != false)
	{
		$cid = $newArray['CLIENT2_ID'];
		if (!in_array($cid, $clients_j))
			$clients_j[] = $cid;
		$jobs[] = $newArray['JOB_ID'];
	}
	dprint("Reducing this to jobs that: (i) were received within last two years, " .
			"or (ii) were updated within the last two years, or (iii) have activity recorded within the last two years:<br>" .
			"&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;" .
			"found " . count($jobs) . " jobs, belonging to " . count($clients_j) . " clients", true);

	# --- 4. Get client codes and names for the un-invoiced clients

	sql_encryption_preparation('CLIENT2');
	$sql = "SELECT CLIENT2_ID, C_CODE, " . sql_decrypt('C_CO_NAME', '', true) . "
			FROM CLIENT2
			WHERE CLIENT2_ID IN (" . implode(',', $clients_j) . ")
			ORDER BY CLIENT2_ID
			";
	#dprint($sql);#
	sql_execute($sql);
	$clients = array();
	$client_reports = array();
	while (($newArray = sql_fetch_assoc()) != false)
	{
		$clients[$newArray['CLIENT2_ID']] = array($newArray['C_CODE'], $newArray['C_CO_NAME']);
		$client_reports[$newArray['CLIENT2_ID']] = '';
	}
	#dprint("Clients (" . count($clients) . ") = " . print_r($clients,1)); #

	foreach ($client_reports as $client2_id => $temp)
	{
		$sql = "SELECT MAX(REPORT_SENT_DT) FROM CLIENT_REPORT WHERE CLIENT2_ID=$client2_id";
		sql_execute($sql);
		while (($newArray = sql_fetch()) != false)
			$client_reports[$client2_id] = $newArray[0];
		$temp=$temp; # keep code-checker quiet
	}

	$max_jobs = 5000;
	if ($max_jobs < count($jobs))
	{
		dprint("TRUNCATING LIST OF JOBS from " . count($jobs) . " to $max_jobs", true);
		$temp = array();
		for ($ii = 0; $ii < $max_jobs; $ii++)
			$temp[$ii] = $jobs[$ii];
		$jobs = $temp;
		$temp = '';
	}

	$cl_jobs_list = implode(',', $jobs); # the open jobs for the uninvoiced clients

	# --- 5. Do report on the jobs

	sql_encryption_preparation('JOB');
	sql_encryption_preparation('JOB_SUBJECT');
	sql_encryption_preparation('JOB_PHONE');
	$sql = "SELECT J.JOB_ID, J.CLIENT2_ID, J.J_VILNO, " . sql_decrypt('J.CLIENT_REF', '', true) . ", J.J_OPENED_DT, J.J_UPDATED_DT,
				" . sql_decrypt('JS.JS_LASTNAME', '', true) . ", " . sql_decrypt('JS.JS_FIRSTNAME', '', true) . ", JS.JS_TITLE,
				" . sql_decrypt('JS.JS_COMPANY', '', true) . ", ST.J_STATUS, J.JC_TOTAL_AMT,
				" . sql_decrypt('JS.JS_ADDR_1', '', true) . ", " . sql_decrypt('JS.JS_ADDR_2', '', true) . ",
				" . sql_decrypt('JS.JS_ADDR_3', '', true) . ", " . sql_decrypt('JS.JS_ADDR_4', '', true) . ",
				" . sql_decrypt('JS.JS_ADDR_5', '', true) . ", " . sql_decrypt('JS.JS_ADDR_PC', '', true) . ",
				" . sql_decrypt('PH.JP_PHONE', '', true) . ", J.JC_INSTAL_AMT, J.JC_INSTAL_FREQ, J.JC_INSTAL_DT_1,
				J.JC_LETTER_MORE, LT.LETTER_NAME, J.J_DIARY_DT, " . sql_decrypt('EM.JP_EMAIL', '', true) . ",
				J.J_SEQUENCE, J.J_USER_ID, US.U_INITIALS, J.JC_TRANS_ID
			FROM JOB AS J
				INNER JOIN CLIENT2			AS C	ON C.CLIENT2_ID=J.CLIENT2_ID
				LEFT JOIN JOB_SUBJECT		AS JS	ON JS.JOB_ID=J.JOB_ID AND JS.JS_PRIMARY=1
				LEFT JOIN JOB_STATUS_SD		AS ST	ON ST.JOB_STATUS_ID=J.JC_JOB_STATUS_ID
				LEFT JOIN JOB_PHONE			AS PH	ON PH.JOB_ID=J.JOB_ID AND PH.JP_PRIMARY_P=$sqlTrue
				LEFT JOIN LETTER_TYPE_SD	AS LT	ON LT.LETTER_TYPE_ID=J.JC_LETTER_TYPE_ID
				LEFT JOIN JOB_PHONE			AS EM	ON EM.JOB_ID=J.JOB_ID AND EM.JP_PRIMARY_E=$sqlTrue
				LEFT JOIN USERV				AS US	ON US.USER_ID=J.J_USER_ID
			WHERE J.JOB_ID IN (LIST_OF_JOBS)
			ORDER BY C.C_CODE, J.J_VILNO
			";
	#dprint($sql);#
	$sql = str_replace('LIST_OF_JOBS', $cl_jobs_list, $sql);
	sql_execute($sql);
	$datalines = array();
	while (($newArray = sql_fetch_assoc()) != false)
	{
		$job_id = $newArray['JOB_ID'];
		$client2_id = $newArray['CLIENT2_ID'];
		$datalines[$job_id] = array(
			'C_CODE' => $clients[$client2_id][0],
			'C_CO_NAME' => $clients[$client2_id][1],
			'J_VILNO' => $newArray['J_VILNO'],
			'CLIENT_REF' => $newArray['CLIENT_REF'],
			'J_OPENED_DT' => $newArray['J_OPENED_DT'],
			'JS_LASTNAME' => $newArray['JS_LASTNAME'],
			'JS_FIRSTNAME' => $newArray['JS_FIRSTNAME'],
			'JS_TITLE' => $newArray['JS_TITLE'],
			'JS_COMPANY' => $newArray['JS_COMPANY'],
			'J_STATUS' => $newArray['J_STATUS'],
			'JC_TOTAL_AMT' => $newArray['JC_TOTAL_AMT'],
			'COL_AMT_RX' => 0.0,
			'OUTSTANDING' => $newArray['JC_TOTAL_AMT'],
			'CSPENT' => 0.0,
			'TOT_AMT_RET_PAY' => 0.0,
			'NUM_PAYMENTS' => 0,
			'AMT_COL_PER_TOT' => 0.0,
			'AMT_COL_PER_DIR' => 0.0,
			'AMT_COL_PER_TOV' => 0.0,
			'AMT_COL_PER_FOR' => 0.0,
			'AMT_COL_PER_RET' => 0.0,
			'LAST_PAY_DT' => '',
			'JS_ADDR_1' => $newArray['JS_ADDR_1'],
			'JS_ADDR_2' => $newArray['JS_ADDR_2'],
			'JS_ADDR_3' => $newArray['JS_ADDR_3'],
			'JS_ADDR_4' => $newArray['JS_ADDR_4'],
			'JS_ADDR_5' => $newArray['JS_ADDR_5'],
			'JS_ADDR_PC' => $newArray['JS_ADDR_PC'],
			'JP_PHONE' => phone_to_text($newArray['JP_PHONE']),
			'LT_1_SENT' => 0,
			'LT_1_DT' => '',
			'LT_2_SENT' => 0,
			'LT_2_DT' => '',
			'LT_3_SENT' => 0,
			'LT_3_DT' => '',
			'LT_CON_SENT' => 0,
			'LT_CON_DT' => '',
			'LT_DEM_SENT' => 0,
			'LT_DEM_DT' => '',
			'LAST_CLIENT_REP' => $client_reports[$client2_id],
			'J_UPDATED_DT' => $newArray['J_UPDATED_DT'],
			'JC_INSTAL_AMT' => $newArray['JC_INSTAL_AMT'],
			'JC_INSTAL_FREQ' => $newArray['JC_INSTAL_FREQ'],
			'JC_INSTAL_DT_1' => $newArray['JC_INSTAL_DT_1'],
			'LAST_PAY_AMT' => 0.0,
			'NEXT_PAY_DT' => '',
			'NEXT_LETTER' => array('JC_LETTER_MORE' => $newArray['JC_LETTER_MORE'], 'LETTER_NAME' => $newArray['LETTER_NAME']),
			'J_DIARY_DT' => $newArray['J_DIARY_DT'],
			'JP_EMAIL' => $newArray['JP_EMAIL'],
			'J_SEQUENCE' => $newArray['J_SEQUENCE'],
			'VILCOL_USER' => "{$newArray['U_INITIALS']} ({$newArray['J_USER_ID']})",
			'ADD_PH_NO_1' => '',
			'ADD_PH_DE_1' => '',
			'ADD_PH_NO_2' => '',
			'ADD_PH_DE_2' => '',
			'ADD_PH_NO_3' => '',
			'ADD_PH_DE_3' => '',
			'ADD_PH_NO_4' => '',
			'ADD_PH_DE_4' => '',
			'ADD_PH_NO_5' => '',
			'ADD_PH_DE_5' => '',
			'JC_TRANS_ID' => $newArray['JC_TRANS_ID'],
			'NOTES' => ''
			);
	}

	# Payments regardless of when collected:
	$sql = "SELECT JOB_ID, COL_AMT_RX, COL_PAYMENT_ROUTE_ID, COL_DT_RX
			FROM JOB_PAYMENT
			WHERE (COL_BOUNCED=$sqlFalse) AND (OBSOLETE=$sqlFalse) AND (JOB_ID IN ($cl_jobs_list))
			";
	$payments_sql = $sql;
	#dprint($sql);#
	sql_execute($sql);
	while (($newArray = sql_fetch()) != false)
	{
		$job_id = intval($newArray[0]);
		$col_amt_rx = floatval($newArray[1]);
		$payment_route_id = intval($newArray[2]);
		$col_dt_rx = $newArray[3];
		$datalines[$job_id]['COL_AMT_RX'] += $col_amt_rx;
		$datalines[$job_id]['OUTSTANDING'] -= $col_amt_rx;
		$datalines[$job_id]['NUM_PAYMENTS']++;
		if ($payment_route_id == $id_ROUTE_cspent)
		{
			$datalines[$job_id]['CSPENT'] += $col_amt_rx;
			$datalines[$job_id]['TOT_AMT_RET_PAY'] += $col_amt_rx;
		}
		if ( ($datalines[$job_id]['LAST_PAY_DT'] == '') || ($datalines[$job_id]['LAST_PAY_DT'] < $col_dt_rx) )
		{
			$datalines[$job_id]['LAST_PAY_DT'] = $col_dt_rx;
			$datalines[$job_id]['LAST_PAY_AMT'] = $col_amt_rx;
		}
	}
	#dprint("Got Payments regardless of when collected");#

	# Payments collected within the given date period:
	$where = array();
	if ($sc_date_fr)
		$where[] = "$sc_date_fr <= COL_DT_RX";
	if ($sc_date_to)
		$where[] = "COL_DT_RX < $sc_date_to";
	$sql = $payments_sql . ($where ? (" AND (" . implode(') AND (', $where) . ")") : '');
	#dprint($sql);#
	sql_execute($sql);
	while (($newArray = sql_fetch()) != false)
	{
		$job_id = $newArray[0];
		$col_amt_rx = floatval($newArray[1]);
		$payment_route_id = intval($newArray[2]);
		$datalines[$job_id]['AMT_COL_PER_TOT'] += $col_amt_rx;
		if ($payment_route_id == $id_ROUTE_direct)
			$datalines[$job_id]['AMT_COL_PER_DIR'] += $col_amt_rx;
		elseif ($payment_route_id == $id_ROUTE_tous)
			$datalines[$job_id]['AMT_COL_PER_TOV'] += $col_amt_rx;
		elseif ($payment_route_id == $id_ROUTE_fwd)
			$datalines[$job_id]['AMT_COL_PER_FOR'] += $col_amt_rx;
		elseif ($payment_route_id == $id_ROUTE_cspent)
			$datalines[$job_id]['AMT_COL_PER_RET'] += $col_amt_rx;
	}
	#dprint("Got Payments collected within the given date period");#

	# Letters
	$sql = "SELECT L.JOB_ID, L.JL_POSTED_DT, E.EM_DT, L.LETTER_TYPE_ID
			FROM JOB_LETTER AS L LEFT JOIN EMAIL AS E ON E.EMAIL_ID=L.JL_EMAIL_ID AND E.OBSOLETE=$sqlFalse
			WHERE (L.JOB_ID IN ($cl_jobs_list))
			";
	#dprint($sql);#
	sql_execute($sql);
	while (($newArray = sql_fetch()) != false)
	{
		$job_id = intval($newArray[0]);
		$jl_posted_dt = $newArray[1]; # SQL date
		$jl_emailed_dt = $newArray[2]; # SQL date
		$letter_type_id = $newArray[3];

		if ($letter_type_id == $id_LETTER_TYPE_letter_1)
		{
			$datalines[$job_id]['LT_1_SENT']++;
			$dt = date_first($jl_posted_dt, $jl_emailed_dt);
			if ($dt &&
				(	($datalines[$job_id]['LT_1_DT'] == '') || ($datalines[$job_id]['LT_1_DT'] < $dt)	))
				$datalines[$job_id]['LT_1_DT'] = $dt;
		}
		if ($letter_type_id == $id_LETTER_TYPE_letter_2)
		{
			$datalines[$job_id]['LT_2_SENT']++;
			$dt = date_first($jl_posted_dt, $jl_emailed_dt);
			if ($dt &&
				(	($datalines[$job_id]['LT_2_DT'] == '') || ($datalines[$job_id]['LT_2_DT'] < $dt)	))
				$datalines[$job_id]['LT_2_DT'] = $dt;
		}
		if ($letter_type_id == $id_LETTER_TYPE_letter_3)
		{
			$datalines[$job_id]['LT_3_SENT']++;
			$dt = date_first($jl_posted_dt, $jl_emailed_dt);
			if ($dt &&
				(	($datalines[$job_id]['LT_3_DT'] == '') || ($datalines[$job_id]['LT_3_DT'] < $dt)	))
				$datalines[$job_id]['LT_3_DT'] = $dt;
		}
		if ($letter_type_id == $id_LETTER_TYPE_contact)
		{
			$datalines[$job_id]['LT_CON_SENT']++;
			$dt = date_first($jl_posted_dt, $jl_emailed_dt);
			if ($dt &&
				(	($datalines[$job_id]['LT_CON_DT'] == '') || ($datalines[$job_id]['LT_CON_DT'] < $dt)	))
				$datalines[$job_id]['LT_CON_DT'] = $dt;
		}
		if ($letter_type_id == $id_LETTER_TYPE_demand)
		{
			$datalines[$job_id]['LT_DEM_SENT']++;
			$dt = date_first($jl_posted_dt, $jl_emailed_dt);
			if ($dt &&
				(	($datalines[$job_id]['LT_DEM_DT'] == '') || ($datalines[$job_id]['LT_DEM_DT'] < $dt)	))
				$datalines[$job_id]['LT_DEM_DT'] = $dt;
		}
	} # Letters
	#dprint("Got Letters");#

	# Additional [non-primary] phone numbers
	$add_phones = array();
	sql_encryption_preparation('JOB_PHONE');
	$sql = "SELECT JOB_ID, " . sql_decrypt('JP_PHONE', '', true) . " ," . sql_decrypt('JP_DESCR', '', true) . "
			FROM JOB_PHONE
			WHERE (JP_PHONE IS NOT NULL) AND (JP_PRIMARY_P=$sqlFalse) AND (JOB_ID IN ($cl_jobs_list))
			ORDER BY JOB_ID, JOB_PHONE_ID
			";
	#dprint($sql);#
	sql_execute($sql);
	while (($newArray = sql_fetch_assoc()) != false)
	{
		$jp_phone = trim($newArray['JP_PHONE']);
		if (($jp_phone != '') && ($jp_phone != 'NL'))
		{
			if (!array_key_exists($newArray['JOB_ID'], $add_phones))
				$add_phones[$newArray['JOB_ID']] = array();
			$jp_descr = trim(str_replace('Imported from old system job file', '', $newArray['JP_DESCR']));
			$add_phones[$newArray['JOB_ID']][] = array($jp_phone, $jp_descr);
		}
	}
	# Additional [non-primary] phone numbers
	#dprint("Got Additional [non-primary] phone numbers");#

	# Notes
	$notes = array();
	sql_encryption_preparation('JOB_NOTE');
	$sql = "SELECT N.JOB_ID, N.JN_ADDED_ID, U.U_INITIALS, N.JN_ADDED_DT, " . sql_decrypt('N.J_NOTE', '', true) . "
			FROM JOB_NOTE AS N
			LEFT JOIN USERV AS U ON U.USER_ID=N.JN_ADDED_ID
			WHERE (JOB_ID IN ($cl_jobs_list))
			ORDER BY JOB_ID, JN_ADDED_DT
			";
	#dprint($sql);#
	sql_execute($sql);
	while (($newArray = sql_fetch_assoc()) != false)
	{
		$user = "{$newArray['U_INITIALS']} ({$newArray['JN_ADDED_ID']})";
		$dt = date_for_sql($newArray['JN_ADDED_DT'], true, true, true, false, false, true, false, true);
		$note = trim($newArray['J_NOTE']);
		if ($note != '')
		{
			if (!array_key_exists($newArray['JOB_ID'], $notes))
				$notes[$newArray['JOB_ID']] = array();
			$notes[$newArray['JOB_ID']][] = array($user, $dt, $note);
		}
	}
	# Notes
	#dprint("Got Notes");#

	# Convert SQL dates into readable dates, and other misc transformations.
	$dl_ix = 0;
	foreach ($datalines as $job_id => $dline)
	{
		# NEXT_PAY_DT - do this now, before transforming other dates etc.
		if (0.0 < $dline['OUTSTANDING'])
		{
			if ($dline['LAST_PAY_DT'])
			{
				$days = 0;
				switch ($dline['JC_INSTAL_FREQ'])
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
				$next_ep = date_to_epoch($dline['LAST_PAY_DT'], true, $days);
			}
			else
				$next_ep = date_to_epoch($dline['JC_INSTAL_DT_1']);
			if (0 < $next_ep)
			{
				$next_dt = date_from_epoch(false, $next_ep, false, false, true);
				$next_dt = date_for_sql($next_dt, true, false);
				if ($next_ep < time())
					$next_dt = "**{$next_dt}";
			}
			else
				$next_dt = '-';
		}
		else
			$next_dt = '';
		$datalines[$job_id]['NEXT_PAY_DT'] = $next_dt;

		$datalines[$job_id]['J_OPENED_DT'] = ($dline['J_OPENED_DT'] ? date_for_sql($dline['J_OPENED_DT'], true, false) : '');
		$datalines[$job_id]['LAST_PAY_DT'] = ($dline['LAST_PAY_DT'] ? date_for_sql($dline['LAST_PAY_DT'], true, false) : '');
		$datalines[$job_id]['LT_1_SENT'] = ($dline['LT_1_SENT'] ? 'Yes' : '');
		$datalines[$job_id]['LT_1_DT'] = ($dline['LT_1_DT'] ? date_for_sql($dline['LT_1_DT'], true, false) : '');
		$datalines[$job_id]['LT_2_SENT'] = ($dline['LT_2_SENT'] ? 'Yes' : '');
		$datalines[$job_id]['LT_2_DT'] = ($dline['LT_3_DT'] ? date_for_sql($dline['LT_1_DT'], true, false) : '');
		$datalines[$job_id]['LT_3_SENT'] = ($dline['LT_3_SENT'] ? 'Yes' : '');
		$datalines[$job_id]['LT_3_DT'] = ($dline['LT_3_DT'] ? date_for_sql($dline['LT_1_DT'], true, false) : '');
		$datalines[$job_id]['LT_CON_SENT'] = ($dline['LT_CON_SENT'] ? 'Yes' : '');
		$datalines[$job_id]['LT_CON_DT'] = ($dline['LT_CON_DT'] ? date_for_sql($dline['LT_1_DT'], true, false) : '');
		$datalines[$job_id]['LT_DEM_SENT'] = ($dline['LT_DEM_SENT'] ? 'Yes' : '');
		$datalines[$job_id]['LT_DEM_DT'] = ($dline['LT_DEM_DT'] ? date_for_sql($dline['LT_1_DT'], true, false) : '');
		$datalines[$job_id]['LAST_CLIENT_REP'] = ($dline['LAST_CLIENT_REP'] ? date_for_sql($dline['LAST_CLIENT_REP'], true, false) : '');
		$datalines[$job_id]['J_UPDATED_DT'] = ($dline['J_UPDATED_DT'] ? date_for_sql($dline['J_UPDATED_DT'], true, false) : '');
		$datalines[$job_id]['JC_INSTAL_AMT'] = floatval($dline['JC_INSTAL_AMT']);
		$datalines[$job_id]['JC_INSTAL_FREQ'] = instal_freq_from_code($dline['JC_INSTAL_FREQ']);
		$datalines[$job_id]['JC_INSTAL_DT_1'] = ($dline['JC_INSTAL_DT_1'] ? date_for_sql($dline['JC_INSTAL_DT_1'], true, false) : '');

		if ($dline['NEXT_LETTER']['JC_LETTER_MORE'])
		{
			if ($dline['NEXT_LETTER']['LETTER_NAME'])
				$next_letter = $dline['NEXT_LETTER']['LETTER_NAME'];
			else
				$next_letter = "(missing letter name)";
		}
		else
		{
			if ($dline['NEXT_LETTER']['LETTER_NAME'])
				$next_letter = "None [{$dline['NEXT_LETTER']['LETTER_NAME']}]";
			else
				$next_letter = "None.";
		}
		$datalines[$job_id]['NEXT_LETTER'] = $next_letter;

		$datalines[$job_id]['J_DIARY_DT'] = ($dline['J_DIARY_DT'] ? date_for_sql($dline['J_DIARY_DT'], true, false) : '');
		$datalines[$job_id]['ADD_PH_NO_1'] = phone_to_text(get_add_phone(1, 'n')); # uses $add_phones and $job_id; lib_vilcol.php
		$datalines[$job_id]['ADD_PH_DE_1'] = phone_to_text(get_add_phone(1, 'd')); # uses $add_phones and $job_id; lib_vilcol.php
		$datalines[$job_id]['ADD_PH_NO_2'] = phone_to_text(get_add_phone(2, 'n')); # uses $add_phones and $job_id; lib_vilcol.php
		$datalines[$job_id]['ADD_PH_DE_2'] = phone_to_text(get_add_phone(2, 'd')); # uses $add_phones and $job_id; lib_vilcol.php
		$datalines[$job_id]['ADD_PH_NO_3'] = phone_to_text(get_add_phone(3, 'n')); # uses $add_phones and $job_id; lib_vilcol.php
		$datalines[$job_id]['ADD_PH_DE_3'] = phone_to_text(get_add_phone(3, 'd')); # uses $add_phones and $job_id; lib_vilcol.php
		$datalines[$job_id]['ADD_PH_NO_4'] = phone_to_text(get_add_phone(4, 'n')); # uses $add_phones and $job_id; lib_vilcol.php
		$datalines[$job_id]['ADD_PH_DE_4'] = phone_to_text(get_add_phone(4, 'd')); # uses $add_phones and $job_id; lib_vilcol.php
		$datalines[$job_id]['ADD_PH_NO_5'] = phone_to_text(get_add_phone(5, 'n')); # uses $add_phones and $job_id; lib_vilcol.php
		$datalines[$job_id]['ADD_PH_DE_5'] = phone_to_text(get_add_phone(5, 'd')); # uses $add_phones and $job_id; lib_vilcol.php

		if (array_key_exists($job_id, $notes))
		{
			$dline_notes = '';
			foreach ($notes[$job_id] as $one_note)
			{
				$dline_notes .= "[By {$one_note[0]} on {$one_note[1]}:] " . str_replace(chr(13), '  ', $one_note[2]) . "   ";
			}
			$datalines[$job_id]['NOTES'] = $dline_notes;
		}

		$dl_ix++;
		if ($max_jobs < $dl_ix)
		{
			dprint("*** LIST TRUNCATED TO $max_jobs LINES (from " . count($datalines) . " lines) ***", true);
			break;
		}
	} # foreach ($datalines)
	# Convert SQL dates into readable dates, and other misc transformations.
	#dprint("Done date and misc transformations");#

	#dprint("datalines (" . count($datalines) . ") = " . print_r($datalines,1));#

	$top_lines = array(
		array('Vilcol Non-Collections Report'),
		array("Payment Date Range:", "$sc_date_fr - $sc_date_to"),
		array("Report Date:", date_now(true, '', false))
		);

	$headings = array();
	$headings[] = 'Client Code';
	$headings[] = 'Client Name';
	$headings[] = 'Vilcol Ref';
	$headings[] = 'Client Ref';
	$headings[] = 'Date Job Received';
	$headings[] = 'Last Name';
	$headings[] = 'First Name';
	$headings[] = 'Title';
	$headings[] = 'Company Name';
	$headings[] = 'Status of job';
	$headings[] = 'Amount Owed';
	$headings[] = 'Amount Collected';
	$headings[] = 'Amount Outstanding';
	$headings[] = 'C/Spent';
	$headings[] = 'Number of Payments';
	$headings[] = 'Total Amt. of Ret. Payments';
	$headings[] = 'Amt. Col. in Period (Total)';
	$headings[] = 'Amt. Col. in Period (Direct)';
	$headings[] = 'Amt. Col. in Period (To Vilcol)';
	$headings[] = 'Amt. Col. in Period (For.)';
	$headings[] = 'Amt. Returned in Period';
	$headings[] = 'Last Payment Date';
	$headings[] = 'Home Address [1]';
	$headings[] = 'Home Address [2]';
	$headings[] = 'Home Address [3]';
	$headings[] = 'Home Address [4]';
	$headings[] = 'Home Address [5]';
	$headings[] = 'Post Code';
	$headings[] = 'Home Phone No';
	$headings[] = 'Letter 1 Sent ?';
	$headings[] = 'Letter 1 Date';
	$headings[] = 'Letter 2 Sent ?';
	$headings[] = 'Letter 2 Date';
	$headings[] = 'Letter 3 Sent ?';
	$headings[] = 'Letter 3 Date';
	$headings[] = 'Contact Letter Sent ?';
	$headings[] = 'Contact Letter Date';
	$headings[] = 'Demand Letter Sent ?';
	$headings[] = 'Demand Letter Date';
	$headings[] = 'Last Client Rep.';
	$headings[] = 'Last Record Update';
	$headings[] = 'Regular Payment Amount';
	$headings[] = 'Regular Payment Interval';
	$headings[] = 'Regular Payment Start Date';
	$headings[] = 'Last Payment Amount';
	$headings[] = 'Next Payment Date (**Overdue)';
	$headings[] = 'Next Letter';
	$headings[] = 'Diary Date';
	$headings[] = 'Email Address';
	$headings[] = 'Vilcol Sequence No';
	$headings[] = 'Vilcol User ID';
	$headings[] = 'Additional Phone No. (1)';
	$headings[] = 'Additional Phone Desc. (1)';
	$headings[] = 'Additional Phone No. (2)';
	$headings[] = 'Additional Phone Desc. (2)';
	$headings[] = 'Additional Phone No. (3)';
	$headings[] = 'Additional Phone Desc. (3)';
	$headings[] = 'Additional Phone No. (4)';
	$headings[] = 'Additional Phone Desc. (4)';
	$headings[] = 'Additional Phone No. (5)';
	$headings[] = 'Additional Phone Desc. (5)';
	$headings[] = 'TDX Account ID';
	$headings[] = 'Notes';

	$headings_html = "
		<tr>
			";
			foreach ($headings as $head)
				$headings_html .= "<th>$head</th>";
			$headings_html .= "
		</tr>
		";

	if ($dest == 's')
	{
		$screen = "
		<p style=\"font-size:18px; font-weight:bold;\">
			$title
			&nbsp;&nbsp;&nbsp;&nbsp;
			" . input_button('Export to Excel', "export_xl()", '', 'but_export_xl') . "
		</p>
		<table class=\"spaced_table\">
		";
		foreach ($top_lines as $line)
		{
			$screen .= "
				<tr>";
			foreach ($line as $field)
				$screen .= "<td>$field</td>";
			$screen .= "</tr>
				";
		}

		$prev_code = '';
		$screen_row = 0;
		foreach ($datalines as $job_id => $dline)
		{
			if (($prev_code == '') || ($prev_code != $dline['C_CODE']))
			{
				$screen .= $headings_html;
				$prev_code = $dline['C_CODE'];
			}

			$screen .= "
			<tr>
				<td $ar>{$dline['C_CODE']}</td>
				<td>{$dline['C_CO_NAME']}</td>
				<td title=\"{$job_id}\">{$dline['J_VILNO']}</td>
				<td>{$dline['CLIENT_REF']}</td>
				<td>{$dline['J_OPENED_DT']}</td>
				<td>{$dline['JS_LASTNAME']}</td>
				<td>{$dline['JS_FIRSTNAME']}</td>
				<td>{$dline['JS_TITLE']}</td>
				<td>{$dline['JS_COMPANY']}</td>
				<td>{$dline['J_STATUS']}</td>
				<td $ar>" . money_format_kdb($dline['JC_TOTAL_AMT'], true, true, true) . "</td>
				<td $ar>" . money_format_kdb($dline['COL_AMT_RX'], true, true, true) . "</td>
				<td $ar>" . money_format_kdb($dline['OUTSTANDING'], true, true, true) . "</td>
				<td $ar>" . money_format_kdb($dline['CSPENT'], true, true, true) . "</td>
				<td $ar>{$dline['NUM_PAYMENTS']}</td>
				<td $ar>" . money_format_kdb($dline['TOT_AMT_RET_PAY'], true, true, true) . "</td>
				<td $ar>" . money_format_kdb($dline['AMT_COL_PER_TOT'], true, true, true) . "</td>
				<td $ar>" . money_format_kdb($dline['AMT_COL_PER_DIR'], true, true, true) . "</td>
				<td $ar>" . money_format_kdb($dline['AMT_COL_PER_TOV'], true, true, true) . "</td>
				<td $ar>" . money_format_kdb($dline['AMT_COL_PER_FOR'], true, true, true) . "</td>
				<td $ar>" . money_format_kdb($dline['AMT_COL_PER_RET'], true, true, true) . "</td>
				<td>{$dline['LAST_PAY_DT']}</td>
				<td>{$dline['JS_ADDR_1']}</td>
				<td>{$dline['JS_ADDR_2']}</td>
				<td>{$dline['JS_ADDR_3']}</td>
				<td>{$dline['JS_ADDR_4']}</td>
				<td>{$dline['JS_ADDR_5']}</td>
				<td>{$dline['JS_ADDR_PC']}</td>
				<td>{$dline['JP_PHONE']}</td>
				<td>{$dline['LT_1_SENT']}</td>
				<td>{$dline['LT_1_DT']}</td>
				<td>{$dline['LT_2_SENT']}</td>
				<td>{$dline['LT_2_DT']}</td>
				<td>{$dline['LT_3_SENT']}</td>
				<td>{$dline['LT_3_DT']}</td>
				<td>{$dline['LT_CON_SENT']}</td>
				<td>{$dline['LT_CON_DT']}</td>
				<td>{$dline['LT_DEM_SENT']}</td>
				<td>{$dline['LT_DEM_DT']}</td>
				<td>{$dline['LAST_CLIENT_REP']}</td>
				<td>{$dline['J_UPDATED_DT']}</td>
				<td $ar>" . money_format_kdb($dline['JC_INSTAL_AMT'], true, true, true) . "</td>
				<td $ar>{$dline['JC_INSTAL_FREQ']}</td>
				<td>{$dline['JC_INSTAL_DT_1']}</td>
				<td $ar>" . money_format_kdb($dline['LAST_PAY_AMT'], true, true, true) . "</td>
				<td>{$dline['NEXT_PAY_DT']}</td>
				<td>{$dline['NEXT_LETTER']}</td>
				<td>{$dline['J_DIARY_DT']}</td>
				<td>{$dline['JP_EMAIL']}</td>
				<td>{$dline['J_SEQUENCE']}</td>
				<td>{$dline['VILCOL_USER']}</td>
				<td>{$dline['ADD_PH_NO_1']}</td>
				<td>{$dline['ADD_PH_DE_1']}</td>
				<td>{$dline['ADD_PH_NO_2']}</td>
				<td>{$dline['ADD_PH_DE_2']}</td>
				<td>{$dline['ADD_PH_NO_3']}</td>
				<td>{$dline['ADD_PH_DE_3']}</td>
				<td>{$dline['ADD_PH_NO_4']}</td>
				<td>{$dline['ADD_PH_DE_4']}</td>
				<td>{$dline['ADD_PH_NO_5']}</td>
				<td>{$dline['ADD_PH_DE_5']}</td>
				<td>{$dline['JC_TRANS_ID']}</td>
				<td><textarea rows=\"1\" cols=\"20\">{$dline['NOTES']}</textarea></td>
				" . # Add blank <td> so can stretch the above textarea
				"
				<td width=\"100\">&nbsp;</td>
			</tr>
			";
			$screen_row++;
			if ($max_jobs < $screen_row)
			{
				$screen .= "
				<tr>
					<td colspan=\"99\">*** LIST TRUNCATED AFTER $max_jobs ROWS ***</td>
				</tr>
				";
				break;
			}
		}
		$screen .= "
		</table>
		";
		printD($screen);
	}
	elseif ($dest == 'x')
	{
		$top_lines = array_merge(array(array($title)), array(array()), $top_lines, array(array()));
		#dprint("TL=" . print_r($top_lines,1));#
		#dprint("DL=" . print_r($datalines,1));#
		$formats = array();
		$sheet = array($title_short, $top_lines, $headings, $datalines, $formats, '');
		phpExcel_output($reps_subdir, $xfile, array($sheet)); # library.php

		# Auto-download file to PC
		printD("
		<script type=\"text/javascript\">
			document.form_csv_download.short_fname.value = '$xfile.$phpExcel_ext';
			document.form_csv_download.full_fname.value = '$csv_path' + '{$reps_subdir}/$xfile.$phpExcel_ext';
			document.form_csv_download.submit();
		</script>
		");
	}
	else
		dlog("$this_function: *=* bad dest \"$dest\"");

} # rep_cf_noncol()

//function rep_cf_noncol_old($dest, $sc_date_fr, $sc_date_to, $xfile='')
//{
//	# Collections Report: Non-Collections report.
//	# See specification for details (section 15.17).
//	# $dest is 's' for screen or 'x' for export to Excel file.
//	# $sc_date_fr and $sc_date_to are search criteria that form a date range which
//	#		collection dates must fall within; either may be blank.
//	# $xfile is the name of the file that the search results should be written to;
//	#		it is used when $dest=='x'
//
//	global $add_phones; # created here; used by get_add_phone()
//	global $ar;
//	global $csv_path;
//	#global $excel_currency_format;
//	#global $excel_date_format;
//	#global $grey;
//	global $id_LETTER_TYPE_contact; # settings.php
//	global $id_LETTER_TYPE_demand; # settings.php
//	global $id_LETTER_TYPE_letter_1; # settings.php
//	global $id_LETTER_TYPE_letter_2; # settings.php
//	global $id_LETTER_TYPE_letter_3; # settings.php
//	global $id_ROUTE_cspent;
//	global $id_ROUTE_direct;
//	global $id_ROUTE_fwd;
//	global $id_ROUTE_tous;
//	global $job_id; # created here; used by get_add_phone()
//	global $phpExcel_ext; # settings.php: "xls"
//	global $sqlFalse;
//	global $sqlTrue;
//
//	$this_function = "rep_cf_noncol(\"$dest\", \"$sc_date_fr\", \"$sc_date_to\", \"$xfile\")";
//	#dprint("Entered: $this_function");#
//
//	if ($sc_date_fr)
//	{
//		if ($sc_date_to)
//			$date_string = ", between $sc_date_fr and $sc_date_to";
//		else
//			$date_string = ", from $sc_date_fr";
//	}
//	elseif ($sc_date_to)
//		$date_string = ", up to $sc_date_to";
//	else
//		$date_string = " (all payment dates)";
//
//	$title_short = "Non-Collections Report";
//	$title = "{$title_short} for Open jobs{$date_string}";
//	#dprint($title);#
//
//	# --- 1. Find all clients with open collection jobs, regardless of payments
//
//	$sql = "
//		SELECT DISTINCT CLIENT2_ID, JOB_ID FROM JOB AS J
//		WHERE (0 < CLIENT2_ID) AND (JC_JOB=$sqlTrue) AND (JOB_CLOSED=$sqlFalse) AND J_ARCHIVED=$sqlFalse
//		ORDER BY JOB_ID
//		";
//	#dprint($sql);#
//	sql_execute($sql);
//	$clients_j = array(); # clients with open jobs
//	$jobs_all = array();
//	$j_count = 0;
//	while (($newArray = sql_fetch_assoc()) != false)
//	{
//		if (!array_key_exists($newArray['CLIENT2_ID'], $jobs_all))
//		{
//			$jobs_all[$newArray['CLIENT2_ID']] = array();
//			$clients_j[] = $newArray['CLIENT2_ID'];
//		}
//		$jobs_all[$newArray['CLIENT2_ID']][] = $newArray['JOB_ID'];
//		$j_count++;
//	}
//	dprint("Open Jobs: found " . count($jobs_all) . " (" . count($clients_j) . ") clients with $j_count jobs", true);#
//
//	# --- 2. Find all clients that have had no invoices sent to them in the given period.
//
//	$where_e = array();
//	$where_p = array();
//	if ($sc_date_fr)
//	{
//		$date_sql = date_for_sql_nqnt($sc_date_fr);
//		$where_e[] = "'{$date_sql}' <= E.EM_DT";
//		$where_p[] = "'{$date_sql}' <= I.INV_POSTED_DT";
//	}
//	if ($sc_date_to)
//	{
//		$date_sql = date_for_sql_nqnt($sc_date_to, true);
//		$where_e[] = "E.EM_DT < '{$date_sql}'";
//		$where_p[] = "I.INV_POSTED_DT < '{$date_sql}'"; # NOT "<="
//	}
//	if ($where_e)
//		$where_e = "(" . implode(') AND (', $where_e) . ")";
//	if ($where_p)
//		$where_p = "(" . implode(') AND (', $where_p) . ")";
//	$where = array();
//	if ($where_e && $where_p)
//		$where[] = "(($where_e) OR ($where_p))";
//	elseif ($where_e)
//		$where[] = $where_e;
//	elseif ($where_p)
//		$where[] = $where_p;
//	$where[] = "I.OBSOLETE=$sqlFalse";
//	$where[] = "E.OBSOLETE=$sqlFalse";
//	if ($where)
//		$where = " AND (" . implode(') AND (', $where) . ")";
//	else
//		$where = "";
//	$sql = "SELECT DISTINCT I.CLIENT2_ID FROM INVOICE AS I LEFT JOIN EMAIL AS E ON E.EMAIL_ID=I.INV_EMAIL_ID
//			WHERE (I.CLIENT2_ID IN (" . implode(',', $clients_j) . ")) $where";
//	#dprint($sql);#
//	sql_execute($sql);
//	$clients_i = array(); # clients invoiced in the date range
//	while (($newArray = sql_fetch_assoc()) != false)
//	{
//		$clients_i[] = $newArray['CLIENT2_ID'];
//	}
//	#dprint("Invoiced Clients: found " . count($clients_i) . " clients");#
//
//	$clients_u = array_diff($clients_j, $clients_i); # clients with open jobs but uninvoiced in the date range
//	dprint("Un-invoiced Clients: found " . count($clients_u) . " clients", true);#
//
//	# --- 3. Get the set of open jobs for un-invoiced clients.
//
//	$cl_jobs = array(); # jobs for $clients
//	foreach ($clients_u as $client2_id)
//	{
//		$cl_jobs = array_merge($cl_jobs, $jobs_all[$client2_id]);
//	}
//	$j_count = count($cl_jobs);
//	dprint("Un-invoiced Client Jobs: found $j_count jobs", true);#
//
//	if (1000 < $j_count)
//	{
//		# REVIEW: Cut down the number of jobs (Steve says (16/05/16) that this won't be a problem when we next import old data)
//		$temp = array();
//		$cl_count = count($cl_jobs);
//		for ($ii = 0; $ii < $cl_count; $ii++)
//		{
//			if (($cl_count - $ii) <= 1000)
//				$temp[] = $cl_jobs[$ii];
//		}
//		$cl_jobs = $temp;
//		dprint("*** NOTICE *** The number of Non-collection Jobs has been artificially and automatically reduced from $j_count to " . count($cl_jobs), true);
//	}
//
//	# --- 4. Get client codes and names for the un-invoiced clients
//
//	sql_encryption_preparation('CLIENT2');
//	$sql = "SELECT CLIENT2_ID, C_CODE, " . sql_decrypt('C_CO_NAME', '', true) . "
//			FROM CLIENT2
//			WHERE CLIENT2_ID IN (" . implode(',', $clients_u) . ")
//			ORDER BY CLIENT2_ID
//			";
//	#dprint($sql);#
//	sql_execute($sql);
//	$clients = array();
//	$client_reports = array();
//	while (($newArray = sql_fetch_assoc()) != false)
//	{
//		$clients[$newArray['CLIENT2_ID']] = array($newArray['C_CODE'], $newArray['C_CO_NAME']);
//		$client_reports[$newArray['CLIENT2_ID']] = '';
//	}
//	#dprint("Clients (" . count($clients) . ") = " . print_r($clients,1)); #
//
//	foreach ($client_reports as $client2_id => $temp)
//	{
//		$sql = "SELECT MAX(REPORT_SENT_DT) FROM CLIENT_REPORT WHERE CLIENT2_ID=$client2_id";
//		sql_execute($sql);
//		while (($newArray = sql_fetch()) != false)
//			$client_reports[$client2_id] = $newArray[0];
//	}
//
//	$cl_jobs_list = implode(',', $cl_jobs); # the open jobs for the uninvoiced clients
//
//	# --- 5. Do report on the jobs
//
//	sql_encryption_preparation('JOB');
//	sql_encryption_preparation('JOB_SUBJECT');
//	sql_encryption_preparation('JOB_PHONE');
//	$sql = "SELECT J.JOB_ID, J.CLIENT2_ID, J.J_VILNO, " . sql_decrypt('J.CLIENT_REF', '', true) . ", J.J_OPENED_DT, J.J_UPDATED_DT,
//				" . sql_decrypt('JS.JS_LASTNAME', '', true) . ", " . sql_decrypt('JS.JS_FIRSTNAME', '', true) . ", JS.JS_TITLE,
//				" . sql_decrypt('JS.JS_COMPANY', '', true) . ", ST.J_STATUS, J.JC_TOTAL_AMT,
//				" . sql_decrypt('JS.JS_ADDR_1', '', true) . ", " . sql_decrypt('JS.JS_ADDR_2', '', true) . ",
//				" . sql_decrypt('JS.JS_ADDR_3', '', true) . ", " . sql_decrypt('JS.JS_ADDR_4', '', true) . ",
//				" . sql_decrypt('JS.JS_ADDR_5', '', true) . ", " . sql_decrypt('JS.JS_ADDR_PC', '', true) . ",
//				" . sql_decrypt('PH.JP_PHONE', '', true) . ", J.JC_INSTAL_AMT, J.JC_INSTAL_FREQ, J.JC_INSTAL_DT_1,
//				J.JC_LETTER_MORE, LT.LETTER_NAME, J.J_DIARY_DT, " . sql_decrypt('EM.JP_EMAIL', '', true) . ",
//				J.J_SEQUENCE, J.J_USER_ID, US.U_INITIALS, J.JC_TRANS_ID
//			FROM JOB AS J
//				INNER JOIN CLIENT2			AS C	ON C.CLIENT2_ID=J.CLIENT2_ID
//				LEFT JOIN JOB_SUBJECT		AS JS	ON JS.JOB_ID=J.JOB_ID AND JS.JS_PRIMARY=1
//				LEFT JOIN JOB_STATUS_SD		AS ST	ON ST.JOB_STATUS_ID=J.JC_JOB_STATUS_ID
//				LEFT JOIN JOB_PHONE			AS PH	ON PH.JOB_ID=J.JOB_ID AND PH.JP_PRIMARY_P=$sqlTrue
//				LEFT JOIN LETTER_TYPE_SD	AS LT	ON LT.LETTER_TYPE_ID=J.JC_LETTER_TYPE_ID
//				LEFT JOIN JOB_PHONE			AS EM	ON EM.JOB_ID=J.JOB_ID AND EM.JP_PRIMARY_E=$sqlTrue
//				LEFT JOIN USERV				AS US	ON US.USER_ID=J.J_USER_ID
//			WHERE J.JOB_ID IN ($cl_jobs_list)
//			ORDER BY C.C_CODE, J.J_VILNO
//			";
//	#dprint($sql);#
//	sql_execute($sql);
//	$datalines = array();
//	while (($newArray = sql_fetch_assoc()) != false)
//	{
//		$job_id = $newArray['JOB_ID'];
//		$client2_id = $newArray['CLIENT2_ID'];
//		$datalines[$job_id] = array(
//			'C_CODE' => $clients[$client2_id][0],
//			'C_CO_NAME' => $clients[$client2_id][1],
//			'J_VILNO' => $newArray['J_VILNO'],
//			'CLIENT_REF' => $newArray['CLIENT_REF'],
//			'J_OPENED_DT' => $newArray['J_OPENED_DT'],
//			'JS_LASTNAME' => $newArray['JS_LASTNAME'],
//			'JS_FIRSTNAME' => $newArray['JS_FIRSTNAME'],
//			'JS_TITLE' => $newArray['JS_TITLE'],
//			'JS_COMPANY' => $newArray['JS_COMPANY'],
//			'J_STATUS' => $newArray['J_STATUS'],
//			'JC_TOTAL_AMT' => $newArray['JC_TOTAL_AMT'],
//			'COL_AMT_RX' => 0.0,
//			'OUTSTANDING' => $newArray['JC_TOTAL_AMT'],
//			'CSPENT' => 0.0,
//			'TOT_AMT_RET_PAY' => 0.0,
//			'NUM_PAYMENTS' => 0,
//			'AMT_COL_PER_TOT' => 0.0,
//			'AMT_COL_PER_DIR' => 0.0,
//			'AMT_COL_PER_TOV' => 0.0,
//			'AMT_COL_PER_FOR' => 0.0,
//			'AMT_COL_PER_RET' => 0.0,
//			'LAST_PAY_DT' => '',
//			'JS_ADDR_1' => $newArray['JS_ADDR_1'],
//			'JS_ADDR_2' => $newArray['JS_ADDR_2'],
//			'JS_ADDR_3' => $newArray['JS_ADDR_3'],
//			'JS_ADDR_4' => $newArray['JS_ADDR_4'],
//			'JS_ADDR_5' => $newArray['JS_ADDR_5'],
//			'JS_ADDR_PC' => $newArray['JS_ADDR_PC'],
//			'JP_PHONE' => phone_to_text($newArray['JP_PHONE']),
//			'LT_1_SENT' => 0,
//			'LT_1_DT' => '',
//			'LT_2_SENT' => 0,
//			'LT_2_DT' => '',
//			'LT_3_SENT' => 0,
//			'LT_3_DT' => '',
//			'LT_CON_SENT' => 0,
//			'LT_CON_DT' => '',
//			'LT_DEM_SENT' => 0,
//			'LT_DEM_DT' => '',
//			'LAST_CLIENT_REP' => $client_reports[$client2_id],
//			'J_UPDATED_DT' => $newArray['J_UPDATED_DT'],
//			'JC_INSTAL_AMT' => $newArray['JC_INSTAL_AMT'],
//			'JC_INSTAL_FREQ' => $newArray['JC_INSTAL_FREQ'],
//			'JC_INSTAL_DT_1' => $newArray['JC_INSTAL_DT_1'],
//			'LAST_PAY_AMT' => 0.0,
//			'NEXT_PAY_DT' => '',
//			'NEXT_LETTER' => array('JC_LETTER_MORE' => $newArray['JC_LETTER_MORE'], 'LETTER_NAME' => $newArray['LETTER_NAME']),
//			'J_DIARY_DT' => $newArray['J_DIARY_DT'],
//			'JP_EMAIL' => $newArray['JP_EMAIL'],
//			'J_SEQUENCE' => $newArray['J_SEQUENCE'],
//			'VILCOL_USER' => "{$newArray['U_INITIALS']} ({$newArray['J_USER_ID']})",
//			'ADD_PH_NO_1' => '',
//			'ADD_PH_DE_1' => '',
//			'ADD_PH_NO_2' => '',
//			'ADD_PH_DE_2' => '',
//			'ADD_PH_NO_3' => '',
//			'ADD_PH_DE_3' => '',
//			'ADD_PH_NO_4' => '',
//			'ADD_PH_DE_4' => '',
//			'ADD_PH_NO_5' => '',
//			'ADD_PH_DE_5' => '',
//			'JC_TRANS_ID' => $newArray['JC_TRANS_ID'],
//			'NOTES' => ''
//			);
//	}
//
//	# Payments regardless of when collected:
//	$sql = "SELECT JOB_ID, COL_AMT_RX, COL_PAYMENT_ROUTE_ID, COL_DT_RX
//			FROM JOB_PAYMENT
//			WHERE (COL_BOUNCED=$sqlFalse) AND (OBSOLETE=$sqlFalse) AND (JOB_ID IN ($cl_jobs_list))
//			";
//	$payments_sql = $sql;
//	#dprint($sql);#
//	sql_execute($sql);
//	while (($newArray = sql_fetch()) != false)
//	{
//		$job_id = 1 * $newArray[0];
//		$col_amt_rx = 1.0 * $newArray[1];
//		$payment_route_id = 1 * $newArray[2];
//		$col_dt_rx = $newArray[3];
//		$datalines[$job_id]['COL_AMT_RX'] += $col_amt_rx;
//		$datalines[$job_id]['OUTSTANDING'] -= $col_amt_rx;
//		$datalines[$job_id]['NUM_PAYMENTS']++;
//		if ($payment_route_id == $id_ROUTE_cspent)
//		{
//			$datalines[$job_id]['CSPENT'] += $col_amt_rx;
//			$datalines[$job_id]['TOT_AMT_RET_PAY'] += $col_amt_rx;
//		}
//		if ( ($datalines[$job_id]['LAST_PAY_DT'] == '') || ($datalines[$job_id]['LAST_PAY_DT'] < $col_dt_rx) )
//		{
//			$datalines[$job_id]['LAST_PAY_DT'] = $col_dt_rx;
//			$datalines[$job_id]['LAST_PAY_AMT'] = $col_amt_rx;
//		}
//	}
//
//	# Payments collected within the given date period:
//	$where = array();
//	if ($sc_date_fr)
//		$where[] = "$sc_date_fr <= COL_DT_RX";
//	if ($sc_date_to)
//		$where[] = "COL_DT_RX < $sc_date_to";
//	$sql = $payments_sql . ($where ? (" AND (" . implode(') AND (', $where) . ")") : '');
//	#dprint($sql);#
//	sql_execute($sql);
//	while (($newArray = sql_fetch()) != false)
//	{
//		$job_id = $newArray[0];
//		$col_amt_rx = 1.0 * $newArray[1];
//		$payment_route_id = 1 * $newArray[2];
//		$datalines[$job_id]['AMT_COL_PER_TOT'] += $col_amt_rx;
//		if ($payment_route_id == $id_ROUTE_direct)
//			$datalines[$job_id]['AMT_COL_PER_DIR'] += $col_amt_rx;
//		elseif ($payment_route_id == $id_ROUTE_tous)
//			$datalines[$job_id]['AMT_COL_PER_TOV'] += $col_amt_rx;
//		elseif ($payment_route_id == $id_ROUTE_fwd)
//			$datalines[$job_id]['AMT_COL_PER_FOR'] += $col_amt_rx;
//		elseif ($payment_route_id == $id_ROUTE_cspent)
//			$datalines[$job_id]['AMT_COL_PER_RET'] += $col_amt_rx;
//	}
//
//	# Letters
//	$sql = "SELECT L.JOB_ID, L.JL_POSTED_DT, E.EM_DT, L.LETTER_TYPE_ID
//			FROM JOB_LETTER AS L LEFT JOIN EMAIL AS E ON E.EMAIL_ID=L.JL_EMAIL_ID AND E.OBSOLETE=$sqlFalse
//			WHERE (L.JOB_ID IN ($cl_jobs_list))
//			";
//	#dprint($sql);#
//	sql_execute($sql);
//	while (($newArray = sql_fetch()) != false)
//	{
//		$job_id = 1 * $newArray[0];
//		$jl_posted_dt = $newArray[1]; # SQL date
//		$jl_emailed_dt = $newArray[2]; # SQL date
//		$letter_type_id = $newArray[3];
//
//		if ($letter_type_id == $id_LETTER_TYPE_letter_1)
//		{
//			$datalines[$job_id]['LT_1_SENT']++;
//			$dt = date_first($jl_posted_dt, $jl_emailed_dt);
//			if ($dt &&
//				(	($datalines[$job_id]['LT_1_DT'] == '') || ($datalines[$job_id]['LT_1_DT'] < $dt)	))
//				$datalines[$job_id]['LT_1_DT'] = $dt;
//		}
//		if ($letter_type_id == $id_LETTER_TYPE_letter_2)
//		{
//			$datalines[$job_id]['LT_2_SENT']++;
//			$dt = date_first($jl_posted_dt, $jl_emailed_dt);
//			if ($dt &&
//				(	($datalines[$job_id]['LT_2_DT'] == '') || ($datalines[$job_id]['LT_2_DT'] < $dt)	))
//				$datalines[$job_id]['LT_2_DT'] = $dt;
//		}
//		if ($letter_type_id == $id_LETTER_TYPE_letter_3)
//		{
//			$datalines[$job_id]['LT_3_SENT']++;
//			$dt = date_first($jl_posted_dt, $jl_emailed_dt);
//			if ($dt &&
//				(	($datalines[$job_id]['LT_3_DT'] == '') || ($datalines[$job_id]['LT_3_DT'] < $dt)	))
//				$datalines[$job_id]['LT_3_DT'] = $dt;
//		}
//		if ($letter_type_id == $id_LETTER_TYPE_contact)
//		{
//			$datalines[$job_id]['LT_CON_SENT']++;
//			$dt = date_first($jl_posted_dt, $jl_emailed_dt);
//			if ($dt &&
//				(	($datalines[$job_id]['LT_CON_DT'] == '') || ($datalines[$job_id]['LT_CON_DT'] < $dt)	))
//				$datalines[$job_id]['LT_CON_DT'] = $dt;
//		}
//		if ($letter_type_id == $id_LETTER_TYPE_demand)
//		{
//			$datalines[$job_id]['LT_DEM_SENT']++;
//			$dt = date_first($jl_posted_dt, $jl_emailed_dt);
//			if ($dt &&
//				(	($datalines[$job_id]['LT_DEM_DT'] == '') || ($datalines[$job_id]['LT_DEM_DT'] < $dt)	))
//				$datalines[$job_id]['LT_DEM_DT'] = $dt;
//		}
//	} # Letters
//
//	# Additional [non-primary] phone numbers
//	$add_phones = array();
//	sql_encryption_preparation('JOB_PHONE');
//	$sql = "SELECT JOB_ID, " . sql_decrypt('JP_PHONE', '', true) . " ," . sql_decrypt('JP_DESCR', '', true) . "
//			FROM JOB_PHONE
//			WHERE (JP_PHONE IS NOT NULL) AND (JP_PRIMARY_P=$sqlFalse) AND (JOB_ID IN ($cl_jobs_list))
//			ORDER BY JOB_ID, JOB_PHONE_ID
//			";
//	#dprint($sql);#
//	sql_execute($sql);
//	while (($newArray = sql_fetch_assoc()) != false)
//	{
//		$jp_phone = trim($newArray['JP_PHONE']);
//		if (($jp_phone != '') && ($jp_phone != 'NL'))
//		{
//			if (!array_key_exists($newArray['JOB_ID'], $add_phones))
//				$add_phones[$newArray['JOB_ID']] = array();
//			$jp_descr = trim(str_replace('Imported from old system job file', '', $newArray['JP_DESCR']));
//			$add_phones[$newArray['JOB_ID']][] = array($jp_phone, $jp_descr);
//		}
//	}
//	# Additional [non-primary] phone numbers
//
//	# Notes
//	$notes = array();
//	sql_encryption_preparation('JOB_NOTE');
//	$sql = "SELECT N.JOB_ID, N.JN_ADDED_ID, U.U_INITIALS, N.JN_ADDED_DT, " . sql_decrypt('N.J_NOTE', '', true) . "
//			FROM JOB_NOTE AS N
//			LEFT JOIN USERV AS U ON U.USER_ID=N.JN_ADDED_ID
//			WHERE (JOB_ID IN ($cl_jobs_list))
//			ORDER BY JOB_ID, JN_ADDED_DT
//			";
//	#dprint($sql);#
//	sql_execute($sql);
//	while (($newArray = sql_fetch_assoc()) != false)
//	{
//		$user = "{$newArray['U_INITIALS']} ({$newArray['JN_ADDED_ID']})";
//		$dt = date_for_sql($newArray['JN_ADDED_DT'], true, true, true, false, false, true, false, true);
//		$note = trim($newArray['J_NOTE']);
//		if ($note != '')
//		{
//			if (!array_key_exists($newArray['JOB_ID'], $notes))
//				$notes[$newArray['JOB_ID']] = array();
//			$notes[$newArray['JOB_ID']][] = array($user, $dt, $note);
//		}
//	}
//	# Notes
//
//	# Convert SQL dates into readable dates, and other misc transformations.
//	foreach ($datalines as $job_id => $dline)
//	{
//		# NEXT_PAY_DT - do this now, before transforming other dates etc.
//		if (0.0 < $dline['OUTSTANDING'])
//		{
//			if ($dline['LAST_PAY_DT'])
//			{
//				$days = 0;
//				switch ($dline['JC_INSTAL_FREQ'])
//				{
//					case 'I': $days = 1; break;
//					case 'D': $days = 1; break;
//					case 'W': $days = 7; break;
//					case 'F': $days = 14; break;
//					case 'M': $days = 30; break;
//					case 'T': $days = 60; break;
//					case 'Q': $days = 90; break;
//					default : $days = 1; break;
//				}
//				$next_ep = date_to_epoch($dline['LAST_PAY_DT'], true, $days);
//			}
//			else
//				$next_ep = date_to_epoch($dline['JC_INSTAL_DT_1']);
//			if (0 < $next_ep)
//			{
//				$next_dt = date_from_epoch(false, $next_ep, false, false, true);
//				$next_dt = date_for_sql($next_dt, true, false);
//				if ($next_ep < time())
//					$next_dt = "**{$next_dt}";
//			}
//			else
//				$next_dt = '-';
//		}
//		else
//			$next_dt = '';
//		$datalines[$job_id]['NEXT_PAY_DT'] = $next_dt;
//
//		$datalines[$job_id]['J_OPENED_DT'] = ($dline['J_OPENED_DT'] ? date_for_sql($dline['J_OPENED_DT'], true, false) : '');
//		$datalines[$job_id]['LAST_PAY_DT'] = ($dline['LAST_PAY_DT'] ? date_for_sql($dline['LAST_PAY_DT'], true, false) : '');
//		$datalines[$job_id]['LT_1_SENT'] = ($dline['LT_1_SENT'] ? 'Yes' : '');
//		$datalines[$job_id]['LT_1_DT'] = ($dline['LT_1_DT'] ? date_for_sql($dline['LT_1_DT'], true, false) : '');
//		$datalines[$job_id]['LT_2_SENT'] = ($dline['LT_2_SENT'] ? 'Yes' : '');
//		$datalines[$job_id]['LT_2_DT'] = ($dline['LT_3_DT'] ? date_for_sql($dline['LT_1_DT'], true, false) : '');
//		$datalines[$job_id]['LT_3_SENT'] = ($dline['LT_3_SENT'] ? 'Yes' : '');
//		$datalines[$job_id]['LT_3_DT'] = ($dline['LT_3_DT'] ? date_for_sql($dline['LT_1_DT'], true, false) : '');
//		$datalines[$job_id]['LT_CON_SENT'] = ($dline['LT_CON_SENT'] ? 'Yes' : '');
//		$datalines[$job_id]['LT_CON_DT'] = ($dline['LT_CON_DT'] ? date_for_sql($dline['LT_1_DT'], true, false) : '');
//		$datalines[$job_id]['LT_DEM_SENT'] = ($dline['LT_DEM_SENT'] ? 'Yes' : '');
//		$datalines[$job_id]['LT_DEM_DT'] = ($dline['LT_DEM_DT'] ? date_for_sql($dline['LT_1_DT'], true, false) : '');
//		$datalines[$job_id]['LAST_CLIENT_REP'] = ($dline['LAST_CLIENT_REP'] ? date_for_sql($dline['LAST_CLIENT_REP'], true, false) : '');
//		$datalines[$job_id]['J_UPDATED_DT'] = ($dline['J_UPDATED_DT'] ? date_for_sql($dline['J_UPDATED_DT'], true, false) : '');
//		$datalines[$job_id]['JC_INSTAL_AMT'] = 1.0 * $dline['JC_INSTAL_AMT'];
//		$datalines[$job_id]['JC_INSTAL_FREQ'] = instal_freq_from_code($dline['JC_INSTAL_FREQ']);
//		$datalines[$job_id]['JC_INSTAL_DT_1'] = ($dline['JC_INSTAL_DT_1'] ? date_for_sql($dline['JC_INSTAL_DT_1'], true, false) : '');
//
//		if ($dline['NEXT_LETTER']['JC_LETTER_MORE'])
//		{
//			if ($dline['NEXT_LETTER']['LETTER_NAME'])
//				$next_letter = $dline['NEXT_LETTER']['LETTER_NAME'];
//			else
//				$next_letter = "(missing letter name)";
//		}
//		else
//		{
//			if ($dline['NEXT_LETTER']['LETTER_NAME'])
//				$next_letter = "None [{$dline['NEXT_LETTER']['LETTER_NAME']}]";
//			else
//				$next_letter = "None.";
//		}
//		$datalines[$job_id]['NEXT_LETTER'] = $next_letter;
//
//		$datalines[$job_id]['J_DIARY_DT'] = ($dline['J_DIARY_DT'] ? date_for_sql($dline['J_DIARY_DT'], true, false) : '');
//		$datalines[$job_id]['ADD_PH_NO_1'] = phone_to_text(get_add_phone(1, 'n')); # uses $add_phones and $job_id; lib_vilcol.php
//		$datalines[$job_id]['ADD_PH_DE_1'] = phone_to_text(get_add_phone(1, 'd')); # uses $add_phones and $job_id; lib_vilcol.php
//		$datalines[$job_id]['ADD_PH_NO_2'] = phone_to_text(get_add_phone(2, 'n')); # uses $add_phones and $job_id; lib_vilcol.php
//		$datalines[$job_id]['ADD_PH_DE_2'] = phone_to_text(get_add_phone(2, 'd')); # uses $add_phones and $job_id; lib_vilcol.php
//		$datalines[$job_id]['ADD_PH_NO_3'] = phone_to_text(get_add_phone(3, 'n')); # uses $add_phones and $job_id; lib_vilcol.php
//		$datalines[$job_id]['ADD_PH_DE_3'] = phone_to_text(get_add_phone(3, 'd')); # uses $add_phones and $job_id; lib_vilcol.php
//		$datalines[$job_id]['ADD_PH_NO_4'] = phone_to_text(get_add_phone(4, 'n')); # uses $add_phones and $job_id; lib_vilcol.php
//		$datalines[$job_id]['ADD_PH_DE_4'] = phone_to_text(get_add_phone(4, 'd')); # uses $add_phones and $job_id; lib_vilcol.php
//		$datalines[$job_id]['ADD_PH_NO_5'] = phone_to_text(get_add_phone(5, 'n')); # uses $add_phones and $job_id; lib_vilcol.php
//		$datalines[$job_id]['ADD_PH_DE_5'] = phone_to_text(get_add_phone(5, 'd')); # uses $add_phones and $job_id; lib_vilcol.php
//
//		if (array_key_exists($job_id, $notes))
//		{
//			$dline_notes = '';
//			foreach ($notes[$job_id] as $one_note)
//			{
//				$dline_notes .= "[By {$one_note[0]} on {$one_note[1]}:] " . str_replace(chr(13), '  ', $one_note[2]) . "   ";
//			}
//			$datalines[$job_id]['NOTES'] = $dline_notes;
//		}
//
//	} # Convert SQL dates into readable dates, and other misc transformations.
//
//	#dprint("datalines (" . count($datalines) . ") = " . print_r($datalines,1));#
//
//	$top_lines = array(
//		array('Vilcol Non-Collections Report'),
//		array("Payment Date Range:", "$sc_date_fr - $sc_date_to"),
//		array("Report Date:", date_now(true, '', false))
//		);
//
//	$headings = array();
//	$headings[] = 'Client Code';
//	$headings[] = 'Client Name';
//	$headings[] = 'Vilcol Ref';
//	$headings[] = 'Client Ref';
//	$headings[] = 'Date Job Received';
//	$headings[] = 'Last Name';
//	$headings[] = 'First Name';
//	$headings[] = 'Title';
//	$headings[] = 'Company Name';
//	$headings[] = 'Status of job';
//	$headings[] = 'Amount Owed';
//	$headings[] = 'Amount Collected';
//	$headings[] = 'Amount Outstanding';
//	$headings[] = 'C/Spent';
//	$headings[] = 'Number of Payments';
//	$headings[] = 'Total Amt. of Ret. Payments';
//	$headings[] = 'Amt. Col. in Period (Total)';
//	$headings[] = 'Amt. Col. in Period (Direct)';
//	$headings[] = 'Amt. Col. in Period (To Vilcol)';
//	$headings[] = 'Amt. Col. in Period (For.)';
//	$headings[] = 'Amt. Returned in Period';
//	$headings[] = 'Last Payment Date';
//	$headings[] = 'Home Address [1]';
//	$headings[] = 'Home Address [2]';
//	$headings[] = 'Home Address [3]';
//	$headings[] = 'Home Address [4]';
//	$headings[] = 'Home Address [5]';
//	$headings[] = 'Post Code';
//	$headings[] = 'Home Phone No';
//	$headings[] = 'Letter 1 Sent ?';
//	$headings[] = 'Letter 1 Date';
//	$headings[] = 'Letter 2 Sent ?';
//	$headings[] = 'Letter 2 Date';
//	$headings[] = 'Letter 3 Sent ?';
//	$headings[] = 'Letter 3 Date';
//	$headings[] = 'Contact Letter Sent ?';
//	$headings[] = 'Contact Letter Date';
//	$headings[] = 'Demand Letter Sent ?';
//	$headings[] = 'Demand Letter Date';
//	$headings[] = 'Last Client Rep.';
//	$headings[] = 'Last Record Update';
//	$headings[] = 'Regular Payment Amount';
//	$headings[] = 'Regular Payment Interval';
//	$headings[] = 'Regular Payment Start Date';
//	$headings[] = 'Last Payment Amount';
//	$headings[] = 'Next Payment Date (**Overdue)';
//	$headings[] = 'Next Letter';
//	$headings[] = 'Diary Date';
//	$headings[] = 'Email Address';
//	$headings[] = 'Vilcol Sequence No';
//	$headings[] = 'Vilcol User ID';
//	$headings[] = 'Additional Phone No. (1)';
//	$headings[] = 'Additional Phone Desc. (1)';
//	$headings[] = 'Additional Phone No. (2)';
//	$headings[] = 'Additional Phone Desc. (2)';
//	$headings[] = 'Additional Phone No. (3)';
//	$headings[] = 'Additional Phone Desc. (3)';
//	$headings[] = 'Additional Phone No. (4)';
//	$headings[] = 'Additional Phone Desc. (4)';
//	$headings[] = 'Additional Phone No. (5)';
//	$headings[] = 'Additional Phone Desc. (5)';
//	$headings[] = 'TDX Account ID';
//	$headings[] = 'Notes';
//
//	$headings_html = "
//		<tr>
//			";
//			foreach ($headings as $head)
//				$headings_html .= "<th>$head</th>";
//			$headings_html .= "
//		</tr>
//		";
//
//	if ($dest == 's')
//	{
//		$screen = "
//		<p style=\"font-size:18px; font-weight:bold;\">
//			$title
//			&nbsp;&nbsp;&nbsp;&nbsp;
//			" . input_button('Export to Excel', "export_xl()", '', 'but_export_xl') . "
//		</p>
//		<table class=\"spaced_table\">
//		";
//		foreach ($top_lines as $line)
//		{
//			$screen .= "
//				<tr>";
//			foreach ($line as $field)
//				$screen .= "<td>$field</td>";
//			$screen .= "</tr>
//				";
//		}
//
//		$prev_code = '';
//		foreach ($datalines as $job_id => $dline)
//		{
//			if (($prev_code == '') || ($prev_code != $dline['C_CODE']))
//			{
//				$screen .= $headings_html;
//				$prev_code = $dline['C_CODE'];
//			}
//
//			$screen .= "
//			<tr>
//				<td $ar>{$dline['C_CODE']}</td>
//				<td>{$dline['C_CO_NAME']}</td>
//				<td title=\"{$job_id}\">{$dline['J_VILNO']}</td>
//				<td>{$dline['CLIENT_REF']}</td>
//				<td>{$dline['J_OPENED_DT']}</td>
//				<td>{$dline['JS_LASTNAME']}</td>
//				<td>{$dline['JS_FIRSTNAME']}</td>
//				<td>{$dline['JS_TITLE']}</td>
//				<td>{$dline['JS_COMPANY']}</td>
//				<td>{$dline['J_STATUS']}</td>
//				<td $ar>" . money_format_kdb($dline['JC_TOTAL_AMT'], true, true, true) . "</td>
//				<td $ar>" . money_format_kdb($dline['COL_AMT_RX'], true, true, true) . "</td>
//				<td $ar>" . money_format_kdb($dline['OUTSTANDING'], true, true, true) . "</td>
//				<td $ar>" . money_format_kdb($dline['CSPENT'], true, true, true) . "</td>
//				<td $ar>{$dline['NUM_PAYMENTS']}</td>
//				<td $ar>" . money_format_kdb($dline['TOT_AMT_RET_PAY'], true, true, true) . "</td>
//				<td $ar>" . money_format_kdb($dline['AMT_COL_PER_TOT'], true, true, true) . "</td>
//				<td $ar>" . money_format_kdb($dline['AMT_COL_PER_DIR'], true, true, true) . "</td>
//				<td $ar>" . money_format_kdb($dline['AMT_COL_PER_TOV'], true, true, true) . "</td>
//				<td $ar>" . money_format_kdb($dline['AMT_COL_PER_FOR'], true, true, true) . "</td>
//				<td $ar>" . money_format_kdb($dline['AMT_COL_PER_RET'], true, true, true) . "</td>
//				<td>{$dline['LAST_PAY_DT']}</td>
//				<td>{$dline['JS_ADDR_1']}</td>
//				<td>{$dline['JS_ADDR_2']}</td>
//				<td>{$dline['JS_ADDR_3']}</td>
//				<td>{$dline['JS_ADDR_4']}</td>
//				<td>{$dline['JS_ADDR_5']}</td>
//				<td>{$dline['JS_ADDR_PC']}</td>
//				<td>{$dline['JP_PHONE']}</td>
//				<td>{$dline['LT_1_SENT']}</td>
//				<td>{$dline['LT_1_DT']}</td>
//				<td>{$dline['LT_2_SENT']}</td>
//				<td>{$dline['LT_2_DT']}</td>
//				<td>{$dline['LT_3_SENT']}</td>
//				<td>{$dline['LT_3_DT']}</td>
//				<td>{$dline['LT_CON_SENT']}</td>
//				<td>{$dline['LT_CON_DT']}</td>
//				<td>{$dline['LT_DEM_SENT']}</td>
//				<td>{$dline['LT_DEM_DT']}</td>
//				<td>{$dline['LAST_CLIENT_REP']}</td>
//				<td>{$dline['J_UPDATED_DT']}</td>
//				<td $ar>" . money_format_kdb($dline['JC_INSTAL_AMT'], true, true, true) . "</td>
//				<td $ar>{$dline['JC_INSTAL_FREQ']}</td>
//				<td>{$dline['JC_INSTAL_DT_1']}</td>
//				<td $ar>" . money_format_kdb($dline['LAST_PAY_AMT'], true, true, true) . "</td>
//				<td>{$dline['NEXT_PAY_DT']}</td>
//				<td>{$dline['NEXT_LETTER']}</td>
//				<td>{$dline['J_DIARY_DT']}</td>
//				<td>{$dline['JP_EMAIL']}</td>
//				<td>{$dline['J_SEQUENCE']}</td>
//				<td>{$dline['VILCOL_USER']}</td>
//				<td>{$dline['ADD_PH_NO_1']}</td>
//				<td>{$dline['ADD_PH_DE_1']}</td>
//				<td>{$dline['ADD_PH_NO_2']}</td>
//				<td>{$dline['ADD_PH_DE_2']}</td>
//				<td>{$dline['ADD_PH_NO_3']}</td>
//				<td>{$dline['ADD_PH_DE_3']}</td>
//				<td>{$dline['ADD_PH_NO_4']}</td>
//				<td>{$dline['ADD_PH_DE_4']}</td>
//				<td>{$dline['ADD_PH_NO_5']}</td>
//				<td>{$dline['ADD_PH_DE_5']}</td>
//				<td>{$dline['JC_TRANS_ID']}</td>
//				<td><textarea rows=\"1\" cols=\"20\">{$dline['NOTES']}</textarea></td>
//				" . # Add blank <td> so can stretch the above textarea
//				"
//				<td width=\"100\">&nbsp;</td>
//			</tr>
//			";
//		}
//		$screen .= "
//		</table>
//		";
//		printD($screen);
//	}
//	elseif ($dest == 'x')
//	{
//		$top_lines = array_merge(array(array($title)), array(array()), $top_lines, array(array()));
//		#dprint("TL=" . print_r($top_lines,1));#
//		#dprint("DL=" . print_r($datalines,1));#
//		$formats = array();
//		$sheet = array($title_short, $top_lines, $headings, $datalines, $formats, '');
//		phpExcel_output($reps_subdir, $xfile, array($sheet)); # library.php
//
//		# Auto-download file to PC
//		printD("
//		<script type=\"text/javascript\">
//			document.form_csv_download.short_fname.value = '$xfile.$phpExcel_ext';
//			document.form_csv_download.full_fname.value = '$csv_path' + '{$reps_subdir}/$xfile.$phpExcel_ext';
//			document.form_csv_download.submit();
//		</script>
//		";
//	}
//	else
//		dlog("$this_function: *=* bad dest \"$dest\"");
//
//} # rep_cf_noncol_old()

function rep_ts_stmt_inv($dest, $sc_date_fr, $xfile='')
{
	# Traces/Statistics: Statement Invoices.
	# $dest is 's' for screen or 'x' for export to Excel file.
	# $sc_date_fr is search criteria that forms a date range (up to "today") within which statement period dates must overlap; may be blank.
	# Works on all clients.
	# $xfile is the name of the file that the search results should be written to;
	#		it is used when $dest=='x'

	global $ar;
	global $csv_path;
	global $excel_currency_format;
	global $grey;
	global $phpExcel_ext; # settings.php: "xls"
	global $sqlFalse;
	global $sqlNow;
	global $sqlTrue;
	global $reps_subdir;

	$this_function = "rep_ts_stmt_inv(\"$dest\", \"$sc_date_fr\", \"$xfile\")";

	if ($sc_date_fr)
		$date_string = "from $sc_date_fr";
	else
		$date_string = "(all dates)";

	$title_short = "Statement Invoices";
	$title = "{$title_short} {$date_string}";

	$dt2y = date_n_years_ago(2); # only jobs that have been completed within the last two years
	if ($sc_date_fr)
		$where_next_dt = "($sc_date_fr <= C.INV_NEXT_STMT_DT AND C.INV_NEXT_STMT_DT <= $sqlNow)";
	else
		$where_next_dt = "(C.INV_NEXT_STMT_DT <= $sqlNow)";
	sql_encryption_preparation('CLIENT2');
	$sql = "SELECT C.CLIENT2_ID, C.C_CODE, " . sql_decrypt('C_CO_NAME', '', true) . ", J.JOB_ID, B.BL_COST
			FROM INV_BILLING AS B
			INNER JOIN JOB AS J ON J.JOB_ID=B.JOB_ID
			INNER JOIN CLIENT2 AS C ON C.CLIENT2_ID=J.CLIENT2_ID
			WHERE (B.OBSOLETE=$sqlFalse) AND (0 < B.BL_COST) AND (B.INVOICE_ID IS NULL) AND (COALESCE(B.BL_LPOS,1) < 2) AND
					(J.OBSOLETE=$sqlFalse) AND (J.JT_JOB=$sqlTrue) AND (J.J_S_INVS=$sqlTrue) AND ('$dt2y' < J.J_CLOSED_DT) AND
					( (C.INV_NEXT_STMT_DT IS NULL) OR (C.INV_NEXT_STMT_DT='') OR $where_next_dt )
			ORDER BY C.CLIENT2_ID, J.JOB_ID";
	if ($dest == 's') dprint($sql);#
	sql_execute($sql);
	$clients = array();
	$client_ids = array();
	$jobs = array();
	while (($newArray = sql_fetch_assoc()) != false)
	{
		if (!array_key_exists($newArray['CLIENT2_ID'], $clients))
		{
			$clients[$newArray['CLIENT2_ID']] = array('C_CODE' => $newArray['C_CODE'], 'C_CO_NAME' => $newArray['C_CO_NAME'],
														'NUM_JOBS' => 0, 'TOTAL_COST' => 0.0);
			$client_ids[] = $newArray['CLIENT2_ID'];
		}

		if (!in_array($newArray['JOB_ID'], $jobs))
		{
			$jobs[] = $newArray['JOB_ID'];
			$clients[$newArray['CLIENT2_ID']]['NUM_JOBS']++;
		}
		$clients[$newArray['CLIENT2_ID']]['TOTAL_COST'] += floatval($newArray['BL_COST']);
	}

	$headings = array('Client Code', 'Client Name', 'No. Jobs', 'Amount');
	if (user_debug())
		$headings[] = 'CLIENT2_ID';

	$datalines = array();
	foreach ($clients as $client2_id => $client_info)
	{
		$datalines[] = array('C_CODE' => $client_info['C_CODE'], 'C_CO_NAME' => $client_info['C_CO_NAME'],
							'NUM_JOBS' => $client_info['NUM_JOBS'], 'TOTAL_COST' => $client_info['TOTAL_COST'],
							'CLIENT2_ID' => (user_debug() ? $client2_id : ''));
	}

	if ($dest == 's')
	{
		$screen = "
		<p style=\"font-size:18px; font-weight:bold;\">
			$title
			&nbsp;&nbsp;&nbsp;&nbsp;
			" . input_button('Export to Excel', "export_xl()", '', 'but_export_xl') . "
		</p>
		";
		if ($client_ids)
			$screen .= input_button('Create Statements', "create_statements('t', '*" . implode(',*', $client_ids) . "')") .
				" (this will take you to the \"Clients\" screen with these clients pre-selected, so that you can create the statements)";
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
				<td>{$dline['C_CODE']}</td><td>{$dline['C_CO_NAME']}</td><td $ar>{$dline['NUM_JOBS']}</td>
				<td $ar>" . money_format_kdb($dline['TOTAL_COST'], true, true, true) . "</td>
				" . (user_debug() ? "<td $grey $ar>{$dline['CLIENT2_ID']}</td>" : '') . "
			</tr>
			";
		}
		$screen .= "
		</table>
		";
		printD($screen);
		#dprint(print_r($clients,1));#
		#dprint(print_r($client_ids,1));#
	}
	elseif ($dest == 'x')
	{
		$top_lines = array(array($title), array());
		$formats = array('D' => $excel_currency_format);
		$sheet = array($title_short, $top_lines, $headings, $datalines, $formats, '');
		phpExcel_output($reps_subdir, $xfile, array($sheet)); # library.php

		# Auto-download file to PC
		printD("
		<script type=\"text/javascript\">
			document.form_csv_download.short_fname.value = '$xfile.$phpExcel_ext';
			document.form_csv_download.full_fname.value = '$csv_path' + '{$reps_subdir}/$xfile.$phpExcel_ext';
			document.form_csv_download.submit();
		</script>
		");
	}
	else
		dlog("$this_function: *=* bad dest \"$dest\"");

} # rep_ts_stmt_inv()

function rep_cs_stmt_inv($dest, $sc_date_fr, $xfile='')
{
	# Collect/Statistics: Statement Invoices.
	# $dest is 's' for screen or 'x' for export to Excel file.
	# $sc_date_fr is search criteria that forms a date range (up to "today") within which statement period dates must overlap; may be blank.
	# Works on all clients.
	# $xfile is the name of the file that the search results should be written to;
	#		it is used when $dest=='x'

	global $ar;
	global $csv_path;
	global $excel_currency_format;
	global $grey;
	global $phpExcel_ext; # settings.php: "xls"
	global $sqlFalse;
	global $sqlNow;
	global $sqlTrue;
	global $reps_subdir;

	$this_function = "rep_cs_stmt_inv(\"$dest\", \"$sc_date_fr\", \"$xfile\")";

	if ($sc_date_fr)
		$date_string = "from $sc_date_fr";
	else
		$date_string = "(all dates)";

	$title_short = "Statement Invoices";
	$title = "{$title_short} {$date_string}";

	$dt2y = date_n_years_ago(2);
	if ($sc_date_fr)
		$where_next_dt = "($sc_date_fr <= C.INV_NEXT_STMT_DT AND C.INV_NEXT_STMT_DT <= $sqlNow)";
	else
		$where_next_dt = "(C.INV_NEXT_STMT_DT <= $sqlNow)";
	$sql = "SELECT C.CLIENT2_ID, C.C_CODE, " . sql_decrypt('C_CO_NAME', '', true) . ", J.JOB_ID, P.COL_AMT_RX
			FROM JOB AS J
			INNER JOIN JOB_PAYMENT AS P ON P.JOB_ID=J.JOB_ID
			INNER JOIN CLIENT2 AS C ON C.CLIENT2_ID=J.CLIENT2_ID
			WHERE (J.OBSOLETE=$sqlFalse) AND (J.JC_JOB=$sqlTrue) AND (J.J_S_INVS=$sqlTrue) AND (J.J_ARCHIVED=$sqlFalse)
				AND P.INVOICE_ID IS NULL AND P.COL_DT_RX IS NOT NULL AND '$dt2y' <= P.COL_DT_RX AND P.OBSOLETE=$sqlFalse
				AND ( (C.INV_NEXT_STMT_DT IS NULL) OR (C.INV_NEXT_STMT_DT='') OR $where_next_dt )
			ORDER BY C.CLIENT2_ID, J.JOB_ID";
	if ($dest == 's') dprint($sql);#
	sql_execute($sql);
	$clients = array();
	$client_ids = array();
	$jobs = array();
	while (($newArray = sql_fetch_assoc()) != false)
	{
		if (!array_key_exists($newArray['CLIENT2_ID'], $clients))
		{
			$clients[$newArray['CLIENT2_ID']] = array('C_CODE' => $newArray['C_CODE'], 'C_CO_NAME' => $newArray['C_CO_NAME'],
														'NUM_JOBS' => 0, 'TOTAL_PAY' => 0.0);
			$client_ids[] = $newArray['CLIENT2_ID'];
		}

		if (!in_array($newArray['JOB_ID'], $jobs))
		{
			$jobs[] = $newArray['JOB_ID'];
			$clients[$newArray['CLIENT2_ID']]['NUM_JOBS']++;
		}
		$clients[$newArray['CLIENT2_ID']]['TOTAL_PAY'] += floatval($newArray['COL_AMT_RX']);
	}

	$headings = array('Client Code', 'Client Name', 'No. Jobs', 'Payments');
	if (user_debug())
		$headings[] = 'CLIENT2_ID';

	$datalines = array();
	foreach ($clients as $client2_id => $client_info)
	{
		$datalines[] = array('C_CODE' => $client_info['C_CODE'], 'C_CO_NAME' => $client_info['C_CO_NAME'],
							'NUM_JOBS' => $client_info['NUM_JOBS'], 'TOTAL_PAY' => $client_info['TOTAL_PAY'],
							'CLIENT2_ID' => (user_debug() ? $client2_id : ''));
	}

	if ($dest == 's')
	{
		$screen = "
		<p style=\"font-size:18px; font-weight:bold;\">
			$title
			&nbsp;&nbsp;&nbsp;&nbsp;
			" . input_button('Export to Excel', "export_xl()", '', 'but_export_xl') . "
		</p>
		";
		if ($client_ids)
			$screen .= input_button('Create Statements', "create_statements('c', '*" . implode(',*', $client_ids) . "')") .
				" (this will take you to the \"Clients\" screen with these clients pre-selected, so that you can create the statements)";
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
				<td>{$dline['C_CODE']}</td><td>{$dline['C_CO_NAME']}</td><td $ar>{$dline['NUM_JOBS']}</td>
				<td $ar>" . money_format_kdb($dline['TOTAL_PAY'], true, true, true) . "</td>
				" . (user_debug() ? "<td $grey $ar>{$dline['CLIENT2_ID']}</td>" : '') . "
			</tr>
			";
		}
		$screen .= "
		</table>
		";
		printD($screen);
		#dprint(print_r($clients,1));#
		#dprint(print_r($client_ids,1));#
	}
	elseif ($dest == 'x')
	{
		$top_lines = array(array($title), array());
		$formats = array('D' => $excel_currency_format);
		$sheet = array($title_short, $top_lines, $headings, $datalines, $formats, '');
		phpExcel_output($reps_subdir, $xfile, array($sheet)); # library.php

		# Auto-download file to PC
		printD("
		<script type=\"text/javascript\">
			document.form_csv_download.short_fname.value = '$xfile.$phpExcel_ext';
			document.form_csv_download.full_fname.value = '$csv_path' + '{$reps_subdir}/$xfile.$phpExcel_ext';
			document.form_csv_download.submit();
		</script>
		");
	}
	else
		dlog("$this_function: *=* bad dest \"$dest\"");

} # rep_cs_stmt_inv()

function rep_cf_data_8($dest, $sc_date_fr, $sc_date_to, $xfile='')
{
	# Collections Report: Data 8 Phone numbers report.
	# See document "Feedback #2 Data 8 Phone Numbers" for details.
	# $dest is 's' for screen or 'x' for export to Excel file or 'c' for export to UTF-8 CSV file.
	# $sc_date_fr is the job placement date of the earliest job that should be considered.
	# There is no end date.
	# $xfile is the name of the file that the search results should be written to;
	#		it is used when $dest=='x'

	#global $ar;
	global $csv_path;
	#global $excel_currency_format;
	#global $excel_date_format;
	#global $grey;
	global $job_id; # created here; used by get_add_phone()
	global $phpExcel_ext; # settings.php: "xls"
	global $sqlFalse;
	global $sqlTrue;
	global $reps_subdir;

	$debug = false; #global_debug();#
	$this_function = "rep_cf_data_8(\"$dest\", \"$sc_date_fr\", \"$sc_date_to\", \"$xfile\")";
	#dprint("Entered: $this_function");#
	if ($debug) log_write("[data8] Entered: $this_function");

	#set_time_limit(1 * 60 * 60); # 1 hour

	if ($sc_date_fr)
		$date_string = ", from $sc_date_fr";
	else
		$date_string = ", ERROR - NO FROM DATE";

	$title_short = "Data 8 Report";
	$title = "{$title_short} for Open jobs{$date_string}";
	#dprint($title);#

	# Find all collection open jobs,
	#		that were either received within the last two years or have been updated within the last two years
	#		or have had activity within the last two years.
	$date_from = date_for_sql($sc_date_fr);
	$two_years_ago = "'" . (intval(strftime('%Y')) - 2) . strftime("-%m-%d") . "'";
	#dprint("date_from=\"$date_from\", twoyo=\"$two_years_ago\", sc=\"$sc_date_fr\"");#
	if ($date_from < $two_years_ago)
		$date_from = $two_years_ago;

	$sql = "
		SELECT J.CLIENT2_ID, J.JOB_ID, COUNT(*)
		FROM JOB AS J LEFT JOIN JOB_ACT AS A ON A.JOB_ID=J.JOB_ID
		WHERE (J.JC_JOB=$sqlTrue) AND (J.JOB_CLOSED=$sqlFalse) AND (J.J_ARCHIVED=$sqlFalse)
			AND ( ($date_from <= J.J_OPENED_DT) OR ($date_from <= J.J_UPDATED_DT) OR ($date_from <= A.JA_DT) )
		GROUP BY J.CLIENT2_ID, J.JOB_ID
		ORDER BY J.JOB_ID
		";
	#dprint($sql);#

	sql_execute($sql);
	$jobs = array();
	while (($newArray = sql_fetch_assoc()) != false)
		$jobs[] = $newArray['JOB_ID'];
	dprint("Found " . count($jobs) . " jobs");#, belonging to " . count($clients_j) . " clients", true);

	if (!$jobs)
	{
		$temp = "*** No jobs found! ***";
		dprint($temp, true);
		if ($debug) log_write("[data8] Exit function ($temp)");
		return;
	}

	$max_jobs = 0;#
	if ($max_jobs && ($max_jobs < count($jobs)))
	{
		dprint("TRUNCATING LIST OF JOBS from " . count($jobs) . " to $max_jobs", true);
		$temp = array();
		for ($ii = 0; $ii < $max_jobs; $ii++)
			$temp[$ii] = $jobs[$ii];
		$jobs = $temp;
		$temp = '';
	}

	$cl_jobs_list = implode(',', $jobs); # the open jobs

	# Do report on the jobs

	sql_encryption_preparation('JOB');
	sql_encryption_preparation('JOB_SUBJECT');
	sql_encryption_preparation('JOB_PHONE');
	$sql = "SELECT J.JOB_ID, J.J_VILNO,
				" . sql_decrypt('JS.JS_LASTNAME', '', true) . ", " . sql_decrypt('JS.JS_FIRSTNAME', '', true) . ", JS.JS_TITLE,
				" . sql_decrypt('JS.JS_COMPANY', '', true) . ",
				" . sql_decrypt('JS.JS_ADDR_1', '', true) . ", " . sql_decrypt('JS.JS_ADDR_2', '', true) . ",
				" . sql_decrypt('JS.JS_ADDR_3', '', true) . ", " . sql_decrypt('JS.JS_ADDR_4', '', true) . ",
				" . sql_decrypt('JS.JS_ADDR_5', '', true) . ", " . sql_decrypt('JS.JS_ADDR_PC', '', true) . "
			FROM JOB AS J
				LEFT JOIN JOB_SUBJECT		AS JS	ON JS.JOB_ID=J.JOB_ID AND JS.JS_PRIMARY=1
			WHERE J.JOB_ID IN (LIST_OF_JOBS)
			ORDER BY " . sql_decrypt('JS.JS_LASTNAME') . ", " . sql_decrypt('JS.JS_COMPANY') . "
			";
	#dprint($sql);#
	$sql = str_replace('LIST_OF_JOBS', $cl_jobs_list, $sql);
	sql_execute($sql);
	$datalines = array();
	while (($newArray = sql_fetch_assoc()) != false)
	{
		$job_id = $newArray['JOB_ID'];
		$datalines[$job_id] = array(
			'BLANK' => $newArray['J_VILNO'],
			'JS_FULLNAME' => trim("{$newArray['JS_TITLE']} {$newArray['JS_FIRSTNAME']} {$newArray['JS_LASTNAME']} {$newArray['JS_COMPANY']}"),
			'JS_ADDR_1' => $newArray['JS_ADDR_1'],
			'JS_ADDR_2' => $newArray['JS_ADDR_2'],
			'JS_ADDR_3' => $newArray['JS_ADDR_3'],
			'JS_ADDR_4' => $newArray['JS_ADDR_4'],
			'JS_ADDR_5' => $newArray['JS_ADDR_5'],
			'JS_ADDR_PC' => $newArray['JS_ADDR_PC'],
			'JP_PHONE' => ''
			);
	}

	if ($debug) log_write("[data8] Processing datalines (" . count($datalines) . ")");
	foreach ($datalines as $job_id => $info)
	{
		$sql = "SELECT " . sql_decrypt('JP_PHONE', '', true) . " FROM JOB_PHONE WHERE JOB_ID=$job_id ORDER BY " . sql_decrypt('JP_PHONE') . "";
		sql_execute($sql);
		$phones = array();
		while (($newArray = sql_fetch()) != false)
		{
			$temp = trim($newArray[0]);
			if ($temp && ($temp != 'NL') && (strtolower($temp) != 'no search'))
			{
				#if ($temp[0] != '0')
				#	$temp = "0{$temp}";
				$phones[] = $temp;
			}
		}
		$datalines[$job_id]['JP_PHONE'] = implode(', ', $phones);
		$info=$info; # keep code-checker quiet
	}
	if ($debug) log_write("[data8] ...done datalines");
	#dprint("datalines (" . count($datalines) . ") = " . print_r($datalines,1));#

	$top_lines = array(
		array('Vilcol Data 8 Report'),
		array("Job Placement Date Start:", $sc_date_fr),
		array("Report Date:", date_now(true, '', false))
		);

	$headings = array();
	$headings[] = 'Blank';
	$headings[] = 'Full Name';
	$headings[] = 'Home Address [1]';
	$headings[] = 'Home Address [2]';
	$headings[] = 'Home Address [3]';
	$headings[] = 'Home Address [4]';
	$headings[] = 'Home Address [5]';
	$headings[] = 'Post Code';
	$headings[] = 'Phone No(s)';

	$headings_html = "
		<tr>
			";
			foreach ($headings as $head)
				$headings_html .= "<th>$head</th>";
			$headings_html .= "
		</tr>
		";

	if ($dest == 's')
	{
		if ($debug) log_write("[data8] Printing Data 8 to screen...");
		$screen = "
		<p style=\"font-size:18px; font-weight:bold;\">
			$title
			&nbsp;&nbsp;&nbsp;&nbsp;
			" . input_button('Export to CSV', "export_csv()", '', 'but_export_xl') .
			  #. input_button('Export to Excel', "export_xl()", '', 'but_export_xl') .
			"
		</p>
		<table class=\"spaced_table\">
		";
		foreach ($top_lines as $line)
		{
			$screen .= "
				<tr>";
			foreach ($line as $field)
				$screen .= "<td>$field</td>";
			$screen .= "</tr>
				";
		}
		$screen .= $headings_html;

		$screen_row = 0;
		foreach ($datalines as $job_id => $dline)
		{
			$screen .= "
			<tr>
				<td>{$dline['BLANK']}</td>
				<td>{$dline['JS_FULLNAME']}</td>
				<td>{$dline['JS_ADDR_1']}</td>
				<td>{$dline['JS_ADDR_2']}</td>
				<td>{$dline['JS_ADDR_3']}</td>
				<td>{$dline['JS_ADDR_4']}</td>
				<td>{$dline['JS_ADDR_5']}</td>
				<td>{$dline['JS_ADDR_PC']}</td>
				<td>{$dline['JP_PHONE']}</td>
				" . # Add blank <td> so can stretch the above textarea
				"
				<td width=\"100\">&nbsp;</td>
			</tr>
			";
			$screen_row++;
		}
		$screen .= "
		</table>
		";
		printD($screen);
		if ($debug) log_write("[data8] ...done printing");
	}
	elseif ($dest == 'c')
	{
		if ($debug) log_write("[data8] Exporting Data 8 to CSV \"$csv_path/$reps_subdir/$xfile.csv...");
		$fp = csv_open("$csv_path/$reps_subdir", "$xfile.csv", "w");
		if ($fp)
		{
			csv_write($fp, $headings);
			foreach ($datalines as $dline)
				csv_write($fp, $dline);

			# Auto-download file to PC
			printD("
			<script type=\"text/javascript\">
				document.form_csv_download.short_fname.value = '$xfile.csv';
				document.form_csv_download.full_fname.value = '$csv_path' + '{$reps_subdir}/$xfile.csv';
				document.form_csv_download.submit();
			</script>
			");
		}
		csv_close($fp);
		if ($debug) log_write("[data8] ...done export");
		#global $audit_debug;
		#$audit_debug = true;
		misc_info_write('C', 'DATA_8_DT', 'DT', date_now_sql());
	}
	elseif ($dest == 'x')
	{
		if ($debug) log_write("[data8] Exporting Data 8 to Excel \"$csv_path/$reps_subdir/$xfile.xls...");
		$top_lines = array_merge(array(array($title)), array(array()), $top_lines, array(array()));
		#dprint("TL=" . print_r($top_lines,1));#
		#dprint("DL=" . print_r($datalines,1));#
		$formats = array();
		$sheet = array($title_short, $top_lines, $headings, $datalines, $formats, '');
		phpExcel_output($reps_subdir, $xfile, array($sheet)); # library.php

		# Auto-download file to PC
		printD("
		<script type=\"text/javascript\">
			document.form_csv_download.short_fname.value = '$xfile.$phpExcel_ext';
			document.form_csv_download.full_fname.value = '$csv_path' + '{$reps_subdir}/$xfile.$phpExcel_ext';
			document.form_csv_download.submit();
		</script>
		");
		if ($debug) log_write("[data8] ...done export");
	}
	else
		dlog("$this_function: *=* bad dest \"$dest\"");
	if ($debug) log_write("[data8] Exiting rep_cf_data_8()");

} # rep_cf_data_8()

?>
