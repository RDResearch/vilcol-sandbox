<?php

include_once("settings.php");
include_once("library.php");
include_once("lib_pdf.php");
include_once("lib_mail.php");

include_once(__DIR__.'/vendor/autoload.php');

use iio\libmergepdf\Merger;
use iio\libmergepdf\Pages;
use Spatie\Async\Pool;

global $denial_message;
global $navi_1_jobs;
global $role_agt;
global $time_tests; # settings.php
global $USER; # set by admin_verify()
global $ticked_jobs;
global $site_domain;

global $mass_print_amount;
$mass_print_amount = 0;

$subdir = 'search';

log_open("vilcol.log");

sql_connect();

admin_verify(); # writes to $USER

if ($USER['IS_ENABLED'])
{
	if (role_check('*', $role_agt))
	{
		$navi_1_jobs = true; # settings.php; used by navi_1_heading()
		$onload = "onload=\"set_scroll();\"";
		$page_title_2 = 'Jobs - Vilcol';
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
    global $site_domain;
    global $mass_print_amount;
	global $added_activity_id;
	global $added_arrange_id;
	global $added_billing_id;
	global $added_email_id;
	global $added_note_id;
	global $added_payment_id;
	global $added_phone_id;
	global $added_subject_id;
	global $agent_c;
	global $agent_t;
	global $ar;
	global $at;
	global $assign_id;
	global $calendar_names;
	global $can_edit;
	global $can_edit_client;
	global $client_groups;
	global $col2;
	global $col3;
	global $col4;
	#global $col5;
	global $export;
	global $job_id;
	global $last_job;
	global $last_job_id;
	global $letter_rebuild;
	global $letter_top;
	global $manager_a;
	global $manager_c;
	global $manager_t;
	global $manager_tc;
	global $manager_x;
	global $nearly_all_agents;
	global $pdf_message;
	global $resend_207;
	global $role_agt;
	global $role_man;
	global $sc_act_fr;
	global $sc_act_to;
	global $sc_addr;
	global $sc_agent;
	global $sc_archived;
	global $sc_bill;
	global $sc_client;
	global $sc_clref;
	global $sc_closed_fr;
	global $sc_closed_to;
	global $sc_complete;
	global $sc_credit;
	global $sc_date_fr;
	global $sc_date_to;
	global $sc_diary;
	global $sc_group;
	global $sc_inst;
	global $sc_inv;
	global $sc_jopenclosed;
	global $sc_jobstatus;
	global $sc_jobtype;
	global $sc_letters;
	global $sc_ltype;
	global $sc_llist;
	global $sc_obsolete;
	#global $sc_pending;
	global $sc_pmt_fr;
	global $sc_pmt_to;
	global $sc_success;
	global $sc_sys;
	global $sc_target_fr;
	global $sc_target_to;
	global $sc_text;
	global $sc_upd_fr;
	global $sc_upd_to;
	global $sz_date;
	global $task;
	global $ticked_jobs;
	global $time_tests;
	global $tr_colour_1;
	global $USER;
	global $ynfoc_list;
	global $ynfrxns_list;
	global $ynpend_list;

	dprint(post_values());

	#if (post_val('sc_complete') == -1) # "Review"
	#	$time_tests = true;#

	if ($time_tests) log_write("jobs.php/screen_conent(): calling role_check() 5 times...");
	$manager_t = role_check('t', $role_man);
	$manager_c = role_check('c', $role_man);
	$manager_tc = ($manager_t || $manager_c);
	$manager_a = role_check('a', $role_man);
	$manager_x = ($manager_tc || $manager_a);
	$agent_t = role_check('t', $role_agt);
	$agent_c = role_check('c', $role_agt);
	$agent_tc = ($agent_t || $agent_c);

	if ((!$manager_a) && (!$agent_tc))
	{
		print "<p>Sorry you cannot access this screen</p>";
		return;
	}


	$mass_print_path = "/massprint";
	function scan_mass_prints()
	{
		global $mass_print_path;
		global $unix_path;
		$dir = "/home/forge/vilcoldbl.com/admin/massprint/";

		$files = scandir($dir);

		// remove . and .. from the array
		try {

			array_shift($files);
			array_shift($files);
		}
		catch (Exception $e){
		}

		return $files;
	}


	$files = scan_mass_prints();

	?>
    <a href="<?php echo('massPrint.php')?>" target="_blank">View mass prints</a>
	<?php

	print "<h3>Jobs Screen</h3>";

	print "
	<div id=\"div_wait\">
		<p id=\"para_wait\" style=\"color:blue\">Please wait...</p>
		<p id=\"para_wait_extra\" style=\"color:blue\">" . post_val2('wait_longer') . "</p>
	</div><!--div_wait-->
	<div id=\"div_form\" style=\"display:none;\">
	";
//	print "<span id=\"span_message\" style=\"color:blue; font-weight:bold;\">Please wait...</span>";
//	print "<div id=\"screen_content\" style=\"display:none;\">";

	set_time_limit(60 * 5); # 5 minutes to allow for searching-by-subject-name

	$can_edit = true; #role_check('*', $role_man);
	$can_edit_client = false;

	if ($time_tests) log_write("jobs.php/screen_conent(): collecting POST data...");

	if (count($_POST) == 0)
	{
		$sc_date_fr = '';# date_now(true, date_last_month(1), false); # last month plus one day
		$sc_date_to = '';# date_now(true, '', false);
		#$sc_date_fr = '01/01/2012';#
		#$sc_date_to = '31/03/2012';#
	}
	else
	{
		$sc_date_fr = post_val('sc_date_fr', false, true, false, 1);
		$sc_date_to = post_val('sc_date_to', false, true, false, 1);
	}
	#dprint("Got FR=\"$sc_date_fr\", TO=\"$sc_date_to\"");#
	$sc_closed_fr = post_val('sc_closed_fr', false, true, false, 1);
	$sc_closed_to = post_val('sc_closed_to', false, true, false, 1);
	$sc_act_fr = post_val('sc_act_fr', false, true, false, 1);
	$sc_act_to = post_val('sc_act_to', false, true, false, 1);
	$sc_target_fr = post_val('sc_target_fr', false, true, false, 1);
	$sc_target_to = post_val('sc_target_to', false, true, false, 1);
	$sc_pmt_fr = post_val('sc_pmt_fr', false, true, false, 1);
	$sc_pmt_to = post_val('sc_pmt_to', false, true, false, 1);
	$sc_upd_fr = post_val('sc_upd_fr', false, true, false, 1);
	$sc_upd_to = post_val('sc_upd_to', false, true, false, 1);

	if (post_val('search_clicked'))
		$task = "search";
	else
		$task = post_val('task');
	$sc_text = post_val('sc_text', false, false, false, 1);
	$sc_addr = post_val('sc_addr', false, false, false, 1);
	#dprint("\$sc_text=\"$sc_text\"");#

	$sc_client = post_val('sc_client', false, false, false, 1);
	$sc_clref = post_val('sc_clref', false, false, false, 1);
	sql_get_client_groups(true); # writes to $client_groups
	$sc_group = post_val('sc_group', true);

	$sc_complete = post_val('sc_complete'); # blank, 0, 1 or -1
	$sc_credit = post_val('sc_credit'); # blank, 0, 1 or -1
	$sc_success = post_val('sc_success'); # blank, 0, 1 or -1
	$sc_inv = post_val('sc_inv', true); # 0/blank, 1, -1 or -2
	$sc_bill = post_val('sc_bill', true); # 0/blank, 1 or -1

	$sc_inst = post_val('sc_inst', true);
	$sc_jopenclosed = post_val('sc_jopenclosed', true);
	if ((!$manager_x) && (!$agent_c))
		$sc_jopenclosed = 1; # trace agents can only search for open jobs

	$sc_sys = strtolower(post_val('sc_sys'));
	if (strlen($sc_sys) > 1)
		$sc_sys = substr($sc_sys, 0, 1);
	if ((!$manager_a) && $agent_t && (!$agent_c))
		$sc_sys = 't';
	if ((!$manager_a) && $agent_c && (!$agent_t))
		$sc_sys = 'c';

	$sc_jobtype = post_val('sc_jobtype', true);
	$sc_jobstatus = post_val('sc_jobstatus', true);
	if ($manager_x || (!$agent_t)) # Note: if $manager_x is true then $agent_t might be true too!
		$sc_agent = post_val('sc_agent', true);
	else
		$sc_agent = $USER['USER_ID'];
	$job_id = post_val('job_id', true);
	$export = post_val('export');
	$sc_letters = post_val('sc_letters', true);
	$sc_ltype = post_val('sc_ltype', true);
	$sc_llist = post_val('sc_llist', true);
	#$sc_pending = post_val('sc_pending', true);
	$sc_obsolete = post_val('sc_obsolete', true);
	$sc_archived = post_val('sc_archived', true);
	$sc_diary = post_val('sc_diary', true);

	$assign_id = post_val('assign_agent_main', true);
	$ticked_jobs = explode(',', post_val('ticked_jobs_main'));
	$resend_207 = 0;

	$added_subject_id = 0;
	$added_phone_id = 0;
	$added_email_id = 0;
	$added_note_id = 0;
	$added_arrange_id = 0;
	$added_payment_id = 0;
	$added_activity_id = 0;
	$added_billing_id = 0;

	$pdf_message = array();

	if ($time_tests) log_write("jobs.php/screen_conent(): checking for actions to perform before displaying screen...");

	$letter_top = false;
	$letter_rebuild = false;
	$letter_task = post_val('letter_task');
	if ($letter_task == 'letter_preview')
	{
		$letter_top = true;
		$letter_rebuild = true;
	}
	elseif ($letter_task == 'letter_add')
	{
		add_collect_letter(); # lib_vilcol.php
		$letter_top = true;
	}
	elseif ($letter_task == 'letter_del')
	{
		delete_letter();
		$letter_top = true;
	}
	elseif ($letter_task == 'letter_app')
	{
		approve_letter();
		$letter_top = true;
	}
	elseif ($letter_task == 'reload')
	{
		$letter_top = true;
	}

	if ($task == 'save_letter_preview')
	{
		sql_save_letter($job_id, post_val('letter_id',true), post_val('note_text_main', false, false, false, 0), $letter_task);
		$letter_top = true;
		$task = 'edit';
	}
	elseif ($task == 'letter_unapprove')
	{
		$letter_top = true;
		sql_letter_trace_unapprove($job_id, post_val('letter_id',true));
		$task = 'edit';
	}
	elseif ($task == 'assign_to_agent')
	{
		assign_jobs_to_agent($assign_id, $ticked_jobs);
		$task = 'search';
	}
	elseif ($task == 'remove_from_group')
	{
		sql_remove_job_from_group($job_id);
		$task = 'edit';
	}
	elseif ($task == 'add_subject')
	{
		$added_subject_id = sql_add_subject($job_id);
		$task = 'edit';
	}
	elseif ($task == 'add_phone')
	{
		$added_phone_id = sql_add_phone($job_id);
		$task = 'edit';
	}
	elseif ($task == 'add_email')
	{
		$added_email_id = sql_add_email($job_id);
		$task = 'edit';
	}
	elseif ($task == 'add_note')
	{
		$added_note_id = sql_add_note($job_id, post_val('note_text_main'));
		$task = 'edit';
	}
	elseif ($task == 'add_arrange')
	{
		$added_arrange_id = sql_add_arrange($job_id);
		$task = 'edit';
	}
	elseif ($task == 'add_payment')
	{
		#$added_payment_id = sql_add_payment($job_id);
		sql_add_payment($job_id);
		$added_payment_id = -1;
		$task = 'edit';
	}
	elseif ($task == 'del_payment')
	{
		sql_delete_payment($job_id, post_val('letter_id',true));
		$added_payment_id = -1;
		$task = 'edit';
	}
	elseif ($task == 'payments_refresh')
	{
		$added_payment_id = -1;
		$task = 'edit';
	}
	elseif ($task == 'add_activity')
	{
		$act_new_count = post_val('doctype', true);
		if ($act_new_count <= 0)
			$act_new_count = 1;
		sql_add_activity($job_id, $act_new_count);
		$added_activity_id = -1;
		$task = 'edit';
	}
	elseif ($task == 'activity_refresh')
	{
		$added_activity_id = -1;
		$task = 'edit';
	}
	elseif ($task == 'add_billing')
	{
		sql_add_billing('t', $job_id);
		$added_billing_id = -1;
		$task = 'edit';
	}
	elseif ($task == 'del_billing')
	{
		#dprint("Found del_billing");#
		sql_delete_billing('t', $job_id, post_val('letter_id',true));
		$added_billing_id = -1;
		$task = 'edit';
	}
	elseif ($task == 'billing_refresh')
	{
		$added_billing_id = -1;
		$task = 'edit';
	}
	elseif ($task == 'add_invoice_t')
	{
		$invoice_id = sql_add_trace_invoice($job_id); # add invoice or credit
		if (0 < $invoice_id)
			$added_billing_id = -1; # makes screen jump to anchor
		$task = 'edit';
	}
	elseif ($task == 'add_collect_job')
	{
		$new_job_id = sql_add_collect_job_to_trace_job($job_id);
		dprint("A new collection job has been created (ID $new_job_id) and added to the Job Group", true, 'blue');
		#dprint("$task: new Job ID = $new_job_id");
		$task = 'edit';
	}
	elseif ($task == 'close_job')
	{
		sql_close_job($job_id);
		$task = 'edit';
	}
	elseif ($task == 'reopen_job')
	{
		sql_reopen_job($job_id);
		$task = 'edit';
	}
	elseif ($task == 'clone_job')
	{
		sql_clone_trace_job($job_id);
		$task = 'edit';
	}
	elseif ($task == 'archive_job')
	{
		sql_archive_job($job_id);
		$task = 'edit';
	}
	elseif ($task == 'unarchive_job')
	{
		sql_unarchive_job($job_id);
		$task = 'edit';
	}
	elseif ($task == 'delete_job')
	{
		sql_delete_job($job_id);
		$task = 'view';
	}
	elseif ($task == 'request_job')
	{
		$job_id = request_next_trace_job();
		if (0 < $job_id)
			$task = 'view';
		else
			$task = '';
	}
	elseif ($task == 'bulk_save_note')
	{
		$error = sql_bulk_save_note(post_val('note_text_main'), post_val('ticked_jobs_main'));
		if ($error)
			dprint("Add Note failed: \"$error\"", true);
		else
			dprint("The note has been added to the ticked jobs", true, 'blue');
		$task = 'search';
	}
	elseif ($task == 'bulk_save_comm')
	{
		$error = sql_bulk_save_commission(post_val('note_text_main'), post_val('ticked_jobs_main'), '');
		if ($error)
			dprint("Set Commission failed: \"$error\"", true);
		else
			dprint("The commission rate has been set for the ticked jobs", true, 'blue');
		$task = 'search';
	}
	elseif ($task == 'new_address')
	{
		$subject_id = post_val('letter_id', true);
		add_new_address($subject_id, array(), '');
		$task = 'edit';
	}
	elseif ($task == 'approval_reject')
	{
		$added_note_id = approval_reject();
		$task = 'edit';
	}
	elseif ($task == 'letter_email')
	{
		email_job_letter();
		$task = 'edit';
	}
	elseif ($task == 'letter_post')
	{
		post_job_letter();
		$task = 'edit';
	}
//	elseif ($task == 'email_resend')
//	{
//		$letter_id = post_val('letter_id',true);
//		$resend_to = post_val('email_main_addr');
//		$rc = email_job_letter_resend($letter_id, $resend_to);
//		if ($rc)
//			print("<h4 style=\"color:red\">The email resend failed: \"$rc\"</h4>");
//		else
//			dprint("The email has been resent to $resend_to", true, 'blue');
//		$task = 'edit';
//	}
	elseif ($task == 'resend_207')
	{
		$resend_207 = post_val('letter_id',true);
		$task = 'edit';
		$letter_top = true;
	}
	elseif ($task == 'cancel_207')
	{
		$task = 'edit';
		$letter_top = true;
	}
	elseif ($task == 'send_now_207')
	{
		$letter_id = post_val('letter_id',true);
		$resend_to = post_val('email_main_addr');
		$resend_subject = post_val('email_main_subject');
		$resend_message = post_val('email_main_message');
		$resend_letter = post_val2('email_main_letter'); # e.g. v1556557/letter_1556557_90912614_1250506_20181216_113835.pdf
		$resend_invoice = post_val2('email_main_invoice'); # e.g. c1602/invoice_205784_20181216_113923.pdf
		$bits = explode('_', $resend_invoice);
		$resend_invoice_num = intval($bits[1]);
		$resend_invoice_id = sql_select_single("SELECT INVOICE_ID FROM INVOICE WHERE INV_NUM=$resend_invoice_num");
		$rc = email_job_letter_resend_207($letter_id, $resend_to, $resend_subject, $resend_message, $resend_letter, $resend_invoice, $resend_invoice_id);
		if ($rc)
			print("<h4 style=\"color:red\">The email resend failed: \"$rc\"</h4>");
		else
			dprint("The email has been resent to $resend_to", true, 'blue');
		$task = 'edit';
		$letter_top = true;
	}
//	elseif ($export == 'pdf')
//	{
//		pdf_create_doc_letter(post_val('doctype'), post_val('letter_id', true), '');
//		$letter_top = true;
//	}
	elseif ($task == 'search')
	{
		if ($export == 'mark_as_sent')
			sql_mark_as_sent($ticked_jobs); # these are actually ticked letter IDs
		elseif ($export == 'reset_jobs')
			reset_jobs($ticked_jobs);
		elseif ($export == 'mass_print')
			mass_print_letters($ticked_jobs);
//		elseif ($export == 'hack_dates')
//			hack_dates($ticked_jobs);
		elseif ($export == 'upload_app')
			mass_print_letters(array(0), true);
	}
	elseif ($task == 'reload_with_client')
	{
		$can_edit_client = true;
		$task = 'edit';
	}

	if ($time_tests) log_write("jobs.php/screen_conent(): checking for the last job...");

	$u_subject = '';
	$last_job_id = $USER['U_JOB_ID'];
	$last_job = array();
	if (0 < $last_job_id)
	{
		if ($time_tests) log_write("jobs.php/screen_conent(): getting last job...");
		$last_job = sql_get_one_job($USER['U_JOB_ID'], ($last_job_id == $job_id) ? true : false);
		if ($time_tests) log_write("jobs.php/screen_conent(): got last job...");
		if ($last_job)
		{
			$u_subject = "(no subject)";
			if (0 < count($last_job['SUBJECTS']))
			{
				$last_fn = trim(substr($last_job['SUBJECTS'][0]['JS_FIRSTNAME'],0,1));
				$last_ln = trim((string)$last_job['SUBJECTS'][0]['JS_LASTNAME']);
				$last_co = trim((string)$last_job['SUBJECTS'][0]['JS_COMPANY']);
				#dprint("fn=$last_fn, ln=$last_ln, co=$last_co, job=" . print_r($last_job,1));#
				if ($last_fn)
				{
					if ($last_ln)
						$u_subject = "{$last_fn}. $last_ln";
					else
						$u_subject = trim($last_job['SUBJECTS'][0]['JS_FIRSTNAME']);
				}
				elseif ($last_ln)
					$u_subject = $last_ln;
				elseif ($last_co)
					$u_subject = $last_co;
			}
			if (15 < strlen($u_subject))
				$u_subject = substr($u_subject, 0, 15) . "...";
		}
		else
		{
			# Job has been deleted from the database - this can happen during development but shouldn't happen in the live system
			$USER['U_JOB_ID'] = 0;
			$u_subject = '';
		}
	}
	#dprint("U_JOB_ID={$USER['U_JOB_ID']}, u_subject=\"$u_subject\"");#

	if ($time_tests) log_write("jobs.php/screen_conent(): creating javascript...");

	javascript();

	$calendar_names = array();

	if ($time_tests) log_write("jobs.php/screen_conent(): getting agents...");

	$c_agents = sql_get_agents('c', true, false); # get list of collection agents
	#dprint("c_agents=" . print_r($c_agents,1));#
	$t_agents = sql_get_agents('t', true, false); # get list of collection agents
	#dprint("t_agents=" . print_r($t_agents,1));#
	$all_agents = array();
	if ($manager_x)
	{
		if ($agent_t || $manager_a)
		{
			foreach ($t_agents as $id => $name)
				$all_agents[$id] = $name . ($agent_c ? " (T)" : '');
		}
		if ($agent_c || $manager_a)
		{
			foreach ($c_agents as $id => $name)
			{
				if (array_key_exists($id, $all_agents))
					$all_agents[$id] = str_replace('(T)', '(T&C)', $all_agents[$id]);
				else
					$all_agents[$id] = $name . ($agent_t ? " (C)" : '');
			}
		}
	}
	else
		$all_agents[$USER['USER_ID']] = ($agent_t ? $t_agents[$USER['USER_ID']] : $c_agents[$USER['USER_ID']]);

	asort($all_agents);
	$nearly_all_agents = $all_agents;
	if ($manager_x)
		$all_agents[-1] = '(not assigned)';
	#dprint("all_agents=" . print_r($all_agents,1));#

//	if ($agent_t && $agent_c)
//		$c_agents = sql_get_agents('*', true, false); # get list of all agents
//	elseif ($agent_t)
//		$c_agents = sql_get_agents('t', true, false); # get list of trace agents
//	elseif ($agent_c)
//		$c_agents = sql_get_agents('c', true, false); # get list of collection agents
//	else
//		$c_agents = array();

	$sys_list = array();
	#$sys_list = array('' => 'All');
	if ($manager_a || $agent_t)
		$sys_list['t'] = 'Trace';
	if ($manager_a || $agent_c)
		$sys_list['c'] = 'Collect';

	if ($time_tests) log_write("jobs.php/screen_conent(): getting job types etc...");

	$jobtypes = sql_get_job_types(true);
	$jobtypes[-1] = '(not assigned)';

	$jobstatuses = sql_get_job_statuses_for_select();
	$jobstatuses[-1] = '(not assigned)';

	$letter_types_col = sql_get_letter_types_for_client(0, 'c');

	$onchange = "onchange=\"drop_xl_button()\"";
	$onchange_letters = "onchange=\"onchange_letters()\"";
	$onkeydown = "onkeypress=\"drop_xl_button()\"";# onkeydown causes a backspace to delete two characters the first time it is used!

	if ($time_tests) log_write("jobs.php/screen_conent(): displaying form_main (search criteria)...");

	print "
	<div id=\"div_form_main\" style=\"background-color:{$tr_colour_1};\">
	<hr>
	<form name=\"form_main\" action=\"" . server_php_self() . "\" method=\"post\" onsubmit=\"formMainOnSubmit();\">
	" . input_hidden('task', '') . "
	" . input_hidden('job_id', '') . "
	" . input_hidden('export', '') . "
	" . input_hidden('wait_longer', '') . "
	" . input_hidden('assign_agent_main', '') . "
	" . input_hidden('ticked_jobs_main', '') . "
	" . input_hidden('letter_task', '') . "
	" . input_hidden('letter_id', '') . "
	" . input_hidden('doctype', '') . "
	" . input_hidden('note_text_main', '') . "
	" . input_hidden('email_main_addr', '') . "
	" . input_hidden('email_main_subject', '') . "
	" . input_hidden('email_main_message', '') . "
	" . input_hidden('email_main_letter', '') . "
	" . input_hidden('email_main_invoice', '') . "
	" . input_hidden('mp_age_main', '') . "
	";
	# The following line causes form submission when ENTER key is pressed.
	print "
	<input type=\"submit\" style=\"display:none\">
	";

	$last_job_html = '';
	if (0 < $USER['U_JOB_ID'])
		$last_job_html = "
		<td $ar $col4>Last Job: " . input_button($u_subject, "view_js({$USER['U_JOB_ID']},0)") . "</td>
		";

	print "
	<table name=\"table_buttons\" class=\"basic_table\" border=\"0\"><!---->
	";
	$manager_style = ($manager_x ? '' : 'display:none;');
	if ($agent_t || $manager_a)
	{
		print "
		<tr>
			<td>" . input_button("My open jobs", 'my_jobs()', ($agent_t ? '' : 'disabled') . " style=\"width:100px;\"") . "</td>
			<td>" . input_button("Request a new Trace Job", 'request_job()', ($agent_t ? '' : 'disabled') . " style=\"width:177px;\"") . "</td>
			<td>" . input_button("Unassigned Trace Jobs", "search_unassigned('t')", ($manager_tc ? '' : 'disabled') . " style=\"$manager_style\"") . "</td>
			<td>" . input_button("Overdue Trace Jobs", 'search_trace_overdue()', ($manager_tc ? '' : 'disabled') . " style=\"$manager_style\"") . "</td>
			<td>" . input_button("Trace Jobs under review", 'search_trace_review()', ($manager_tc ? '' : 'disabled') . " style=\"$manager_style\"") . "</td>
			<td>" . input_button("Trace Jobs pending invoice", 'search_trace_uninvoiced()', ($manager_x ? '' : 'disabled') . " style=\"$manager_style\"") . "</td>
		</tr>
		";
	}
	if ($agent_c || $manager_a)
	{
		print "
		<tr>
			<td>" . input_button("My Diary", 'my_diary()', ($agent_c ? '' : 'disabled') . " style=\"width:100px;\"") . "</td>
			<td>" . input_button("Unassigned Collection Jobs", "search_unassigned('c')", ($manager_tc ? '' : 'disabled') . " style=\"width:177px; $manager_style\"") . "</td>
			<td $col2>" . input_button("Job Letters not yet approved", 'search_collect_pending(2)', ($manager_tc ? '' : 'disabled') . " style=\"width:295px; $manager_style\"") . "</td>
			<td>" . input_button("Pending letters (approved)", 'search_collect_pending(1)', ($manager_tc ? '' : 'disabled') . " " . " style=\"$manager_style\"") . "</td>
			<td>" . input_button("Jobs with Uninvoiced Payments", 'search_collect_uninvoiced()', ($manager_x ? '' : 'disabled') . " style=\"$manager_style\"") . "</td>
		</tr>
		";
	}
	print "
	</table><!--table_buttons-->

	<table name=\"table_main\" class=\"basic_table\" border=\"0\"><!---->
	<tr>
		<td " . ($manager_x ? $col4 : '') . " $ar>Filter on VILNo, Sequence or Subject/Co name:</td>
		<td>" . input_textbox('sc_text', $sc_text, $manager_x ? 15 : 45, 500, $onkeydown) . # onkeydown causes a backspace to delete two characters the first time it is used!
		"</td>
		<td width=\"10\">&nbsp;</td>
		<td>Client Code/Name:</td>
		<td>" . input_textbox('sc_client', $sc_client, 15, 50, $onkeydown) . # onkeydown causes a backspace to delete two characters the first time it is used!
		"</td>
		<td width=\"10\">&nbsp;</td>
		";
	if ($manager_x)
	{
		print "
			<td $ar>Client Group:</td>
			<td>" . input_select('sc_group', $client_groups, $sc_group, $onchange, false, false) . "</td>
			";
	}
	else
		print input_hidden('sc_group','');
	print "
		<td $col2>" . input_button('Search', 'search_js(1)') . "</td>
		<td width=\"10\">&nbsp;</td>
		<td $ar>...or: " . input_button('Show all jobs', 'search_js(0)') . "</td>
	</tr>
	<tr>
		<td $ar>Subject Addr/Tel:</td>
		<td " . ($manager_x ? $col4 : '') . ">" . input_textbox('sc_addr', $sc_addr, 45, 50, $onkeydown) . # onkeydown causes a backspace to delete two characters the first time it is used!
		"</td>
		<td></td>
		<td $ar>Client Ref:</td>
		<td>" . input_textbox('sc_clref', $sc_clref, 15, 100, $onkeydown) . "</td>
		<td></td>
		";
	if ($manager_x)
	{
		print "
			<td $ar>System:</td>
			<td>" . input_select('sc_sys', $sys_list, $sc_sys, $onchange, false, false) . "</td>
			";
	}
	else
		print input_hidden('sc_sys','');
	print "
		<td $col2>" . input_button('Clear', 'clear_filters()') . "</td>
		" . ($manager_x ? '' : $last_job_html) . "
	</tr>
	";

	$html_jopenclosed_0 = "
		<td $ar>Job Open/Closed:</td>
		<td>" . input_select('sc_jopenclosed', array(1 => 'Open', 2 => 'Closed'), $sc_jopenclosed, $onchange, false, false) . "</td>
		";
	$html_agent_0 = "
		<td $ar $at>Agent:</td>
		<td $at>" . input_select('sc_agent', $all_agents, $sc_agent, $onchange, false, false) . "</td>
		";
	if ($manager_x || (!$agent_c))
	{
		# Managers and trace agents
		$html_jopenclosed_1 = $html_jopenclosed_0;
		$html_jopenclosed_2 = '';
		$html_agent_1 = $html_agent_0;
	}
	else
	{
		# Collection agents
		$html_jopenclosed_1 = '<td></td><td></td>';
		$html_jopenclosed_2 = "
		<tr>$html_jopenclosed_0<td></td>$html_agent_0</tr>
		";
		$html_agent_1 = '';
	}

	print "
	<tr " . ($manager_x ? '' : "style=\"display:none;\"") . ">
		";
	$calendar_names[] = "sc_date_fr";
	$calendar_names[] = "sc_date_to";
	print "
		<td $ar>Received from:</td>
		<td>" . input_textbox('sc_date_fr', $sc_date_fr, $sz_date, 10, $onkeydown) . calendar_icon('sc_date_fr') . "</td>
		<td></td>
		<td $ar>Received to:</td>
		<td>" . input_textbox('sc_date_to', $sc_date_to, $sz_date, 10, $onkeydown) . calendar_icon('sc_date_to') . "</td>
		<td></td>
		$html_jopenclosed_1
		<td></td>
		<td $ar>Invoices:</td>
		<td>" . input_select('sc_inv', array(1 => 'With invoice(s)', -1 => 'Without invoice(s)', -2 => 'Uninvoiced Payments'),
			$sc_inv, $onchange, false, false) . "</td>
		$last_job_html
	</tr>
	$html_jopenclosed_2
	<tr " . ($manager_x ? '' : "style=\"display:none;\"") . ">
		";
	$calendar_names[] = "sc_closed_fr";
	$calendar_names[] = "sc_closed_to";
	print "
		<td $ar $at>Closed from:</td>
		<td $at>" . input_textbox('sc_closed_fr', $sc_closed_fr, $sz_date, 0, $onkeydown) . calendar_icon('sc_closed_fr') . "</td>
		<td></td>
		<td $ar $at>Closed to:</td>
		<td $at>" . input_textbox('sc_closed_to', $sc_closed_to, $sz_date, 0, $onkeydown) . calendar_icon('sc_closed_to') . "</td>
		<td></td>
		$html_agent_1
		<td width=\"10\"></td>
		<td $ar>Letter(s):</td>
		<td>" . input_select('sc_letters', array(2 => 'Unapproved', 1 => 'Approved', 3 => 'Sent'), $sc_letters, $onchange_letters, false, false) .
		#input_tickbox('Letters pending approval', 'sc_pending', 1, $sc_pending) .
		"</td>
		<td $col3>" . input_tickbox('Archived Jobs', 'sc_archived', 1, $sc_archived) . "</td>
		<td $ar>" . input_tickbox('Deleted Jobs', 'sc_obsolete', 1, $sc_obsolete) . "</td>
	</tr>
	<tr " . ($manager_x ? '' : "style=\"display:none;\"") . ">
		";
	$calendar_names[] = "sc_upd_fr";
	$calendar_names[] = "sc_upd_to";
	print "
		<td $ar $at>Updated from:</td>
		<td $at>" . input_textbox('sc_upd_fr', $sc_upd_fr, $sz_date, 0, $onkeydown) . calendar_icon('sc_upd_fr') . "</td>
		<td></td>
		<td $ar $at>Updated to:</td>
		<td $at>" . input_textbox('sc_upd_to', $sc_upd_to, $sz_date, 0, $onkeydown) . calendar_icon('sc_upd_to') . "</td>
		<td></td>
		<td></td>
		<td $at>" . input_tickbox('Diary List', 'sc_diary', 1, $sc_diary, "diary_clicked()") . "</td>
		<td></td>
		<td $ar>Ltr Type</td>
		<td $at>" . input_select('sc_ltype', $letter_types_col, $sc_ltype, $onchange_letters, false, false) . "</td>
		<td $col4>" . input_tickbox('Letter List', 'sc_llist', 1, $sc_llist, "llist_clicked()") . "</td>
	</tr>
	";
	if ($agent_t)
	{
		print "
		<tr " . ($manager_x ? '' : "style=\"display:none;\"") . ">
			";
		$calendar_names[] = "sc_target_fr";
		$calendar_names[] = "sc_target_to";
		print "
			<td $ar>Target from:</td>
			<td>" . input_textbox('sc_target_fr', $sc_target_fr, $sz_date, 0, $onkeydown) .
			calendar_icon('sc_target_fr') . "</td>
			<td></td>
			<td $ar>Target to:</td>
			<td>" . input_textbox('sc_target_to', $sc_target_to, $sz_date, 0, $onkeydown) .
			calendar_icon('sc_target_to') . "</td>
			<td></td>
			<td $ar>Trace Job Type:</td>
			<td>" . input_select('sc_jobtype', $jobtypes, $sc_jobtype, $onchange, false, false) . "</td>
			<td></td>
			<td $ar $at>Complete:</td>
			<td $at>" . input_select('sc_complete', $ynpend_list, $sc_complete, $onchange, false, false) . "</td>
			<td></td>
			<td $ar>Billing:</td>
			<td $col2>" . input_select('sc_bill', array(1 => 'With billing', -1 => 'Without billing'),
				$sc_bill, $onchange, false, false) . "</td>
		</tr>
		<tr " . ($manager_x ? '' : "style=\"display:none;\"") . ">
			<td $ar>Trace Success:</td>
			<td>" . input_select('sc_success', $ynfrxns_list, $sc_success, $onchange, false, false) . "</td>
			<td></td>
			<td $ar>Trace Credit:</td>
			<td>" . input_select('sc_credit', $ynfoc_list, $sc_credit, $onchange, false, false) . "</td>
		</tr>
		";
	}
	else
		print input_hidden('sc_target_fr','') . input_hidden('sc_target_to','') . input_hidden('sc_jobtype','') .
			input_hidden('sc_complete','') . input_hidden('sc_bill','') . input_hidden('sc_success','') . input_hidden('sc_credit','');
	if ($agent_c)
	{
		print "
		<tr " . ($manager_x ? '' : "style=\"display:none;\"") . ">
			";
		$calendar_names[] = "sc_act_fr";
		$calendar_names[] = "sc_act_to";
		print "
			<td $ar>Activity from:</td>
			<td>" . input_textbox('sc_act_fr', $sc_act_fr, $sz_date, 0, $onkeydown) . calendar_icon('sc_act_fr') . "</td>
			<td></td>
			<td $ar>Activity to:</td>
			<td>" . input_textbox('sc_act_to', $sc_act_to, $sz_date, 0, $onkeydown) . calendar_icon('sc_act_to') . "</td>
			<td></td>
			<td $ar>Collection Status:</td>
			<td>" . input_select('sc_jobstatus', $jobstatuses, $sc_jobstatus, $onchange, false, false) . "</td>
			<td></td>
			<td $col2>" . input_tickbox('Instalments', 'sc_inst', 1, $sc_inst, '', (true || user_debug(2)) ? '' : 'style="display:none;"') . "</td>
		</tr>
		<tr " . ($manager_x ? '' : "style=\"display:none;\"") . ">
			";
		$calendar_names[] = "sc_pmt_fr";
		$calendar_names[] = "sc_pmt_to";
		print "
			<td $ar>Payments from:</td>
			<td>" . input_textbox('sc_pmt_fr', $sc_pmt_fr, $sz_date, 0, $onkeydown) . calendar_icon('sc_pmt_fr') . "</td>
			<td></td>
			<td $ar>Payments to:</td>
			<td>" . input_textbox('sc_pmt_to', $sc_pmt_to, $sz_date, 0, $onkeydown) . calendar_icon('sc_pmt_to') . "</td>

		</tr>
		";
	}
	else
		print input_hidden('sc_act_fr','') . input_hidden('sc_act_to','') . input_hidden('sc_jobstatus','') . input_hidden('sc_inst','') .
			input_hidden('sc_pmt_fr','') . input_hidden('sc_pmt_to','');
	print "
	</table>
	<hr>
	";
	if ($agent_tc)
		print "
	" . input_button('Bulk Notes Import', "location.href='bulknotes.php';", ($manager_x ? '' : 'disabled') . " style=\"$manager_style\"") . "&nbsp;&nbsp;
	" . input_button('Bulk Address Import', "location.href='bulkaddr.php';", ($manager_x ? '' : 'disabled') . " style=\"$manager_style\"") . "&nbsp;&nbsp;
		";
	if ($agent_c)
		print "
	" . input_button('Bulk Reset Import', "location.href='bulkreset.php';", ($manager_x ? '' : 'disabled') . " style=\"$manager_style\"") . "&nbsp;&nbsp;
	" . input_button('Bulk Code Import', "location.href='bulkstatus.php';", ($manager_x ? '' : 'disabled') . " style=\"$manager_style\"") . "&nbsp;&nbsp;
	" . input_button('Bulk Client Change', "location.href='bulkclient.php';", ($manager_x ? '' : 'disabled') . " style=\"$manager_style\"") . "&nbsp;&nbsp;
	" . input_button('Bulk User Change', "location.href='bulkuser.php';", ($manager_x ? '' : 'disabled') . " style=\"$manager_style\"") . "&nbsp;&nbsp;
	" . input_button('Bulk Activity', "location.href='bulkact.php';", ($manager_x ? '' : 'disabled') . " style=\"$manager_style\"") . "&nbsp;&nbsp;
	" . input_button('Data 8 Import', "location.href='bulkdata8.php';", ($manager_x ? '' : 'disabled') . " style=\"$manager_style\"") . "&nbsp;&nbsp;
		";
	if ($agent_tc)
		print "
	" . input_button('Bulk Dated Notes', "location.href='bulknotesdated.php';", ($manager_x ? '' : 'disabled') . " style=\"$manager_style\"") . "&nbsp;&nbsp;
		";
	print "
	<hr>
		";
	print "
	</form><!--form_main-->
	</div><!--div_form_main-->

	<form name=\"form_csv_download\" action=\"csv_dl.php\" method=\"post\" target=\"_blank\" rel=\"noopener\">
		<input type=\"hidden\" name=\"short_fname\" value=\"\" />
		<input type=\"hidden\" name=\"full_fname\" value=\"\" />
	</form><!--form_csv_download-->
	";

	if ($time_tests) log_write("jobs.php/screen_conent(): done form_main...");

	if ($task == 'search')
	{
		if ($time_tests) log_write("jobs.php/screen_conent(): calling print_jobs()...");
		print_jobs();
	}
	elseif (($task == 'view') && (0 < $job_id))
	{
		if ($time_tests) log_write("jobs.php/screen_conent(): calling print_one_job()/view...");
		print_one_job(false); # View Job
	}
	elseif ($task == 'edit')
	{
		if ($job_id == 0)
		{
			#$job_id = create_new_job();
			dprint("Cannot Create Job from jobs.php!", true);
		}
		if (0 < $job_id)
		{
			if ($time_tests) log_write("jobs.php/screen_conent(): calling print_one_job()/edit...");
			print_one_job(true); # Edit Job
		}
		else
			dprint("Cannot Edit Job with ID of \"$job_id\"", true);
	}

	print "</div><!--div_form-->";
//	print "</div><!--screen_content-->";

	if ($time_tests) log_write("jobs.php/screen_conent(): doing calendars...");

	javascript_calendars($calendar_names);

	if ($time_tests) log_write("jobs.php/screen_conent(): Exit.");
} # screen_content()

function screen_content_2()
{
	# This is required by screen_layout()
	# Do things that depend on the main_screen_div being displayed

	global $added_activity_id;
	global $added_arrange_id;
	global $added_billing_id;
	global $added_email_id;
	global $added_note_id;
	global $added_payment_id;
	global $added_phone_id;
	global $added_subject_id;
	global $letter_top;
	global $page_title_2;
	global $sc_addr;
	global $sc_client;
	global $sc_clref;
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
		elseif ($sc_clref)
			print "
			<script type=\"text/javascript\">
			document.getElementById('sc_clref').focus();
			document.getElementById('sc_clref').setSelectionRange(100, 100); // any number larger than length of content
			</script>
			";
		elseif ($sc_addr)
			print "
			<script type=\"text/javascript\">
			document.getElementById('sc_addr').focus();
			document.getElementById('sc_addr').setSelectionRange(100, 100); // any number larger than length of content
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

	# If the View Collection Job's Schedule and/or Combined-Schedule-and-Payments are present, scroll them both to "today"
	print "
	<script type=\"text/javascript\">
	var el = document.getElementById('today_line_s');
	if (el)
		el.scrollIntoView(true);
	el = document.getElementById('today_line_c');
	if (el)
		el.scrollIntoView(true);
	</script>
	";

	if ($letter_top)
		$anchor = 'letter_top';
	elseif (0 < $added_subject_id)
		$anchor = "js_{$added_subject_id}";
	elseif (0 < $added_phone_id)
		$anchor = "jp_{$added_phone_id}";
	elseif (0 < $added_email_id)
		$anchor = "jp_{$added_email_id}";
	elseif (0 < $added_note_id)
		$anchor = "jn_{$added_note_id}";
	elseif (0 < $added_arrange_id)
		$anchor = "j_{$added_arrange_id}";
	#elseif (0 < $added_payment_id)
	#	$anchor = "j_{$added_payment_id}";
	elseif ($added_payment_id == -1)
		$anchor = "j_pf";
	elseif ($added_activity_id == -1)
		$anchor = "j_act";
	elseif ($added_billing_id == -1)
		$anchor = "j_bi";
	else#if (!global_debug())
		$anchor = 'page_top';
	#else
	#	$anchor = '';
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
	global $id_ROUTE_cspent;
	global $job_id;
	global $safe_amp;
	global $safe_slash;
	global $success_return;
	global $task;
	global $uni_pound;
	global $USER;

	print "
	<script type=\"text/javascript\">

	var ajaxNext = '';
	var ajaxId = '';
	var jt_success_prev = '';
	var tick_max_figure = -1;

	function reload_with_client()
	{
		document.form_main.job_id.value = $job_id;
		document.form_main.task.value = 'reload_with_client';
		please_wait_on_submit();
		document.form_main.submit();
	}

//	function email_resend(letter_id)
//	{
//		if (confirm('Do you really want to re-send this email and attachments to the client?'))
//		{
//			document.form_main.job_id.value = $job_id;
//			document.form_main.task.value = 'email_resend';
//			document.form_main.email_main_addr.value = document.getElementById('resend_to').value;
//			document.form_main.letter_id.value = letter_id;
//			please_wait_on_submit();
//			document.form_main.submit();
//		}
//	}

	function resend_207(letter_id)
	{
		// Feedback #207 16/12/18
		document.form_main.job_id.value = $job_id;
		document.form_main.task.value = 'resend_207';
		document.form_main.letter_id.value = letter_id;
		please_wait_on_submit();
		document.form_main.submit();
	}

	function send_now_207(letter_id)
	{
		// Feedback #207 16/12/18
		if (confirm('Do you really want to re-send this email and attachments to the client?'))
		{
			document.form_main.job_id.value = $job_id;
			document.form_main.task.value = 'send_now_207';
			document.form_main.letter_id.value = letter_id;
			document.form_main.email_main_addr.value = document.getElementById('resend_to').value;
			document.form_main.email_main_subject.value = document.getElementById('resend_subject').value;
			document.form_main.email_main_message.value = document.getElementById('resend_message').value;
			var el = document.getElementById('resend_letter');
			if (el && el.checked)
				document.form_main.email_main_letter.value = el.value;
			el = document.getElementById('resend_invoice');
			if (el && el.checked)
				document.form_main.email_main_invoice.value = el.value;
			please_wait_on_submit();
			document.form_main.submit();
		}
	}

	function cancel_207()
	{
		// Feedback #207 16/12/18
		document.form_main.job_id.value = $job_id;
		document.form_main.task.value = 'resend_207';
		please_wait_on_submit();
		document.form_main.submit();
	}

	function tick_max_changed(control)
	{
		var max = control.value;
		if (isNumeric(max, false, false, false, false))
		{
			if (max < 0)
				max = 0;
			else if (100 < max)
				max = 100;
		}
		else
			max = 0;
		tick_max_figure = max;
		document.getElementById('but_tick_some').value = 'Tick ' + max;
	}

	function tick_some(default_max)
	{
		if (tick_max_figure == -1)
			tick_max_figure = default_max;
		if (0 < tick_max_figure)
			tick_all(tick_max_figure);
	}

	function mass_print()
	{
		if (" . post_val2('mp_age_main', true) . " != 1)
		{
			alert('Mass Print can only be used for PDFs with NEW letterheads');
			return false;
		}

		var inputs = document.getElementsByTagName('input');
		var ticked = [];
		for (var i = 0; i < inputs.length; i++)
		{
			if (inputs[i].type == 'checkbox')
			{
				if (inputs[i].name.substring(0,4) == 'tck_')
				{
					jid = inputs[i].name.replace('tck_',''); // letter ID
					if (isNumeric(jid,false,false,false,false))
					{
						if (inputs[i].checked)
							ticked.push(jid); // letter ID
					}
				}
			}
		}
		if (0 < ticked.length)
		{
			if (confirm('Do you really want to mass-print all the ticked letters?'))
			{
				relay_filters();
				document.form_main.task.value = 'search';
				document.form_main.export.value = 'mass_print';
				document.form_main.ticked_jobs_main.value = ticked.toString(); // ticked letter IDs
				please_wait_on_submit();
				document.form_main.submit();
			}
		}
		else
			alert('There are no ticked letters to mass-print');
	}

//	function hack_dates()
//	{
//		var inputs = document.getElementsByTagName('input');
//		var ticked = [];
//		for (var i = 0; i < inputs.length; i++)
//		{
//			if (inputs[i].type == 'checkbox')
//			{
//				if (inputs[i].name.substring(0,4) == 'tck_')
//				{
//					jid = inputs[i].name.replace('tck_',''); // letter ID
//					if (isNumeric(jid,false,false,false,false))
//					{
//						if (inputs[i].checked)
//							ticked.push(jid); // letter ID
//					}
//				}
//			}
//		}
//		if (0 < ticked.length)
//		{
//			if (confirm('Do you really want to hack the dates of all the ticked letters as having been sent on the approved date?'))
//			{
//				relay_filters();
//				document.form_main.task.value = 'search';
//				document.form_main.export.value = 'hack_dates';
//				document.form_main.ticked_jobs_main.value = ticked.toString(); // ticked letter IDs
//				please_wait_on_submit();
//				document.form_main.submit();
//			}
//		}
//		else
//			alert('There are no ticked letters to hack');
//	}


	function submit_mass_print(){
	
		if (" . post_val2('mp_age_main', true) . " != 1)
			{
				alert('Mass Print can only be used for PDFs with NEW letterheads');
				return false;
			}
	
			var inputs = document.getElementsByTagName('input');
			var ticked = [];
			for (var i = 0; i < inputs.length; i++)
			{
				if (inputs[i].type == 'checkbox')
				{
					if (inputs[i].name.substring(0,4) == 'tck_')
					{
						jid = inputs[i].name.replace('tck_',''); // letter ID
						if (isNumeric(jid,false,false,false,false))
						{
							if (inputs[i].checked)
								ticked.push(jid); // letter ID
						}
					}
				}
			}
			if (0 < ticked.length)
			{
				if (confirm('Do you really want to mass-print all the ticked letters?'))
				{
					relay_filters();
					
					console.log(ticked.toString());
					   $.ajax({
							type: 'POST',
							url: 'syncMassPrint.php',
							data:{data: ticked.toString()}, 
							cache: false,
							async: false,
							success: function(){
								alert('Mass Merge triggered 😊');
							}
					});
				}
			}
			else{
				alert('There are no ticked letters to mass-print');
				}
	
		
	}

	function upload_app()
	{
		relay_filters();
		document.form_main.task.value = 'search';
		document.form_main.export.value = 'upload_app';
		document.form_main.ticked_jobs_main.value = '';
		please_wait_on_submit();
		document.form_main.submit();
	}

	function mark_as_sent()
	{
		var inputs = document.getElementsByTagName('input');
		var ticked = [];
		for (var i = 0; i < inputs.length; i++)
		{
			if (inputs[i].type == 'checkbox')
			{
				if (inputs[i].name.substring(0,4) == 'tck_')
				{
					jid = inputs[i].name.replace('tck_',''); // letter ID
					if (isNumeric(jid,false,false,false,false))
					{
						if (inputs[i].checked)
							ticked.push(jid); // letter ID
					}
				}
			}
		}
		if (0 < ticked.length)
		{
			if (confirm('Do you really want to mark *AND DELETE* all the ticked letters as having been sent (printed and posted)?'))
			{
				relay_filters();
				document.form_main.task.value = 'search';
				document.form_main.export.value = 'mark_as_sent';
				document.form_main.ticked_jobs_main.value = ticked.toString(); // ticked letter IDs
				please_wait_on_submit();
				document.form_main.submit();
			}
		}
		else
			alert('There are no ticked letters to mark');
	}

	function reset_jobs()
	{
		var inputs = document.getElementsByTagName('input');
		var ticked = [];
		for (var i = 0; i < inputs.length; i++)
		{
			if (inputs[i].type == 'checkbox')
			{
				if (inputs[i].name.substring(0,4) == 'tck_')
				{
					jid = inputs[i].name.replace('tck_','');
					if (isNumeric(jid,false,false,false,false))
					{
						if (inputs[i].checked)
							ticked.push(jid);
					}
				}
			}
		}
		if (0 < ticked.length)
		{
			if (confirm('Do you really want to RESET all the ticked jobs?'))
			{
				relay_filters();
				document.form_main.task.value = 'search';
				document.form_main.export.value = 'reset_jobs';
				document.form_main.ticked_jobs_main.value = ticked.toString();
				please_wait_on_submit();
				document.form_main.submit();
			}
		}
		else
			alert('There are no ticked jobs to reset');
	}

	function mail_merge_excel()
	{
		// This is a short-term alternative to print_letters()
		var inputs = document.getElementsByTagName('input');
		var ticked = [];
		for (var i = 0; i < inputs.length; i++)
		{
			if (inputs[i].type == 'checkbox')
			{
				if (inputs[i].name.substring(0,4) == 'tck_')
				{
					jid = inputs[i].name.replace('tck_',''); // letter ID
					if (isNumeric(jid,false,false,false,false))
					{
						if (inputs[i].checked)
							ticked.push(jid); // letter ID
					}
				}
			}
		}
		//alert(ticked.toString());

		if (0 < ticked.length)
		{
			relay_filters();
			document.form_main.task.value = 'search';
			document.form_main.export.value = 'mail_merge';
			document.form_main.ticked_jobs_main.value = ticked.toString(); // ticked letter IDs
			please_wait_on_submit();
			document.form_main.submit();
		}
		else
			alert('There are no ticked letters to print');
	}

	function print_letters()
	{
		var inputs = document.getElementsByTagName('input');
		var ticked = [];
		for (var i = 0; i < inputs.length; i++)
		{
			if (inputs[i].type == 'checkbox')
			{
				if (inputs[i].name.substring(0,4) == 'tck_')
				{
					jid = inputs[i].name.replace('tck_',''); // letter ID
					if (isNumeric(jid,false,false,false,false))
					{
						if (inputs[i].checked)
							ticked.push(jid); // letter ID
					}
				}
			}
		}
		//alert(ticked.toString());

		if (0 < ticked.length)
		{
			xmlHttp2 = GetXmlHttpObject();
			if (xmlHttp2 == null)
				return;
			var url = 'jobs_ajax.php?op=print&i=' + ticked.toString(); // ticked letter IDs
			url = url + '&ran=' + Math.random();
			//alert(url);
			xmlHttp2.onreadystatechange = stateChanged_print_letters;
			xmlHttp2.open('GET', url, true);
			xmlHttp2.send(null);
		}
		else
			alert('There are no ticked letters to print');
	}

	function stateChanged_print_letters()
	{
		if (xmlHttp2.readyState == 4)
		{
			var resptxt = xprint_noscript(xmlHttp2.responseText);
			//REVIEW once pdftk has finished, show button to download output file; test with 500 files for timing issues.
			if (resptxt)
				alert(resptxt);
		}
	}

	function diary_clicked()
	{
		if (document.getElementById('sc_diary').checked && document.getElementById('sc_llist').checked)
			document.getElementById('sc_llist').checked = false;
		if (document.getElementById('sc_letters').value)
			document.getElementById('sc_letters').value = '';
		if (document.getElementById('sc_ltype').value)
			document.getElementById('sc_ltype').value = '';
	}

	function llist_clicked()
	{
		if (document.getElementById('sc_llist').checked && document.getElementById('sc_diary').checked)
			document.getElementById('sc_diary').checked = false;
	}

	function onchange_letters()
	{
		document.getElementById('sc_diary').checked = false;
		drop_xl_button();
	}

	function jt_success_clicked(control)
	{
		jt_success_prev = control.value;
	}

	function jt_success_changed(control,manager)
	{
		if ((control.value == $success_return) && (!manager))
		{
			control.value = jt_success_prev;
			alert('Only a manager can set Success to Returned');
		}
		else
			update_job(control,'n');
	}

	function juser_set(control)
	{
		update_job(control,'n');
		document.getElementById('j_available').checked = true;
	}

	function j_available_ticked(control)
	{
		var prefix = 'Cannot make job available to agents because ';
		if (control.checked)
		{
			var abort = false;
			var el = document.getElementById('j_vilno');
			if ((!el) || (trim(el.value) == '') || (el.value == 0))
			{
				alert(prefix + 'there is no VILNo');
				abort = true;
			}
			el = document.getElementById('j_sequence');
			if ((!el) || (trim(el.value) == '') || (el.value == 0))
			{
				alert(prefix + 'there is no Sequence No.');
				abort = true;
			}
			el = document.getElementById('client2_id');
			if ((!el) || (el.value == 0))
			{
				alert(prefix + 'there is no Client');
				abort = true;
			}
			el = document.getElementById('jt_job_type_id');
			if ((!el) || (el.value == 0))
			{
				alert(prefix + 'there is no Job Type');
				abort = true;
			}
			if (abort)
			{
				control.checked = false;
				return false;
			}
		}
		update_job(control,'t');
	}

	function jump(place)
	{
		var anchor;
		if (place == 'page_top')
			anchor = place;
		else
			anchor = 'section_' + place;
		//alert(anchor);
		window.location.href = '#' + anchor;
	}

	function update_more_letters(control)
	{
		update_job(control, 't');
		var lt = document.getElementById('jc_letter_type_id');
		var bt = document.getElementById('add_cltr_button');
		if (control.checked)
		{
			if (lt)
				lt.disabled = false;
			if (bt)
				bt.disabled = false;
		}
		else
		{
			if (lt)
			{
				lt.value = '';
				lt.disabled = true;
			}
			if (bt)
				bt.disabled = true;
		}
	}

	function email_letter(sys,lid,hasinv,invapp)
	{
		var el = document.getElementById('email_addr');
		if (!el)
		{
			alert('Cannot find element email_addr');
			return false;
		}
		var e_addr = trim(el.value);
		if (!e_addr)
		{
			alert('Please select an email address to send to');
			return false;
		}

		var el = document.getElementById('email_subject');
		if (!el)
		{
			alert('Cannot find element email_subject');
			return false;
		}
		var e_subject = trim(el.value);
		if (!e_subject)
		{
			alert('Please enter a subject line for the outgoing email');
			return false;
		}

		var el = document.getElementById('email_message');
		if (!el)
		{
			alert('Cannot find element email_message');
			return false;
		}
		var e_message = trim(el.value);
		if (!e_message)
		{
			alert('Please enter a message body for the outgoing email');
			return false;
		}

		var proceed = false;
		if (sys == 't')
		{
			if (hasinv)
			{
				if (invapp)
					proceed = confirm('Do you really want to email this letter and invoice to the client?');
				else
					proceed = confirm('WARNING: The invoice is not APPROVED. Do you really want to email the letter and invoice to the client?');
			}
			else
				proceed = confirm('WARNING: There is no invoice for this job. Do you really want to email just the letter to the client?');
		}
		else
			proceed = confirm('Do you really want to email this letter to the client?')
		if (proceed)
		{
			document.form_main.email_main_addr.value = e_addr;
			document.form_main.email_main_subject.value = e_subject;
			document.form_main.email_main_message.value = e_message;
			document.form_main.job_id.value = $job_id;
			document.form_main.task.value = 'letter_email';
			document.form_main.letter_id.value = lid;
			please_wait_on_submit();
			document.form_main.submit();
		}
	}

	function post_letter(lid)
	{
		if (confirm('Do you really want to mark this letter as posted?'))
		{
			document.form_main.job_id.value = $job_id;
			document.form_main.task.value = 'letter_post';
			document.form_main.letter_id.value = lid;
			please_wait_on_submit();
			document.form_main.submit();
		}
	}

	function save_trace_report()
	{
		var el = document.getElementById('jt_let_report');
		if (el)
			update_job(el);
	}

	function spell_letter()
	{
		alert('Please save letter using the Save button if spelling changes are made');
	}

	function spell_trace_report()
	{
		var el = document.getElementById('save_report_button');
		if (el)
			el.style.display = 'inline';
		alert('Please save report using the Save button if spelling changes are made');
	}

	function report_appr(control)
	{
		if (control.checked)
		{
			var abort = false;
			var el = document.getElementById('jt_job_type_id');
			if ((!el) || (el.value == 0))
			{
				alert('The report cannot be approved because there is no Job Type');
				abort = true;
			}
			if (document.getElementById('j_complete').value != -1)
			{
				alert('The report cannot be approved unless \"Complete\" is set to \"Review\"');
				abort = true;
			}
			el = document.getElementById('jt_let_report');
			if ((!el) || (trim(el.value) == ''))
			{
				alert('The report cannot be approved because it is empty');
				abort = true;
			}
			if (abort)
			{
				control.checked = false;
				return false;
			}
		}
		update_job(control,'t');
	}

	function letter_warn()
	{
		var el = document.getElementById('letter_warning');
		if (el)
			el.innerHTML = '&nbsp;&nbsp;* To save changes to the letter text, please click the Save button below *<br>';
		else
			alert('Element letter_warning not found');
		el = document.getElementById('jl_approved_dt');
		if (el)
			el.disabled = true;
	}

	function letter_approve(control)
	{
		if (control.checked)
			save_letter_preview('approve');
		else
		{
			document.form_main.job_id.value = $job_id;
			document.form_main.task.value = 'letter_unapprove';
			document.form_main.letter_id.value = document.getElementById('letter_preview_id').value;
			please_wait_on_submit();
			document.form_main.submit();
		}
	}

	function save_letter_preview(ltask)
	{
		relay_filters();
		document.form_main.job_id.value = $job_id;
		document.form_main.task.value = 'save_letter_preview';
		document.form_main.letter_task.value = ltask;
		document.form_main.letter_id.value = document.getElementById('letter_preview_id').value;
		document.form_main.note_text_main.value = document.getElementById('letter_preview').value;
		please_wait_on_submit();
		document.form_main.submit();
	}

	function approval_reject()
	{
		var el_cl = document.getElementById('job_closed');
		if (el_cl)
		{
			if (el_cl.value == 1)
				alert('This job is closed so cannot be returned to agent');
			else
			{
				var el_co = document.getElementById('j_complete');
				if (el_co)
				{
					if (el_co.value == 1)
						alert('This job is marked as complete - change this if you want to return it to agent');
					else if (el_co.value != -1)
						alert('This job has not yet been sent for approval, so cannot be returned to agent');
					else
					{
						var el_s = document.getElementById('jt_success');
						if (el_s)
						{
							if (confirm('Do you really want to return this job to the agent?'))
							{
								relay_filters();
								document.form_main.job_id.value = $job_id;
								document.form_main.task.value = 'approval_reject';
								please_wait_on_submit();
								document.form_main.submit();
							}
						}
						else
							alert('Element jt_success not found');
					}
				}
				else
					alert('Element j_complete not found');
			}
		}
		else
			alert('Element job_closed not found');
	}

	function approval_submit()
	{
		var el_cl = document.getElementById('job_closed');
		if (el_cl)
		{
			if (el_cl.value == 1)
				alert('This job is already closed');
			else
			{
				var el_co = document.getElementById('j_complete');
				if (el_co)
				{
					if (el_co.value == 1)
						alert('This job is already complete');
					else if (el_co.value == -1)
						alert('This job has already been sent for approval');
					else
					{
						var el_s = document.getElementById('jt_success');
						if (el_s)
						{
							if ((el_s.value != '0') && (el_s.value != '1') && (el_s.value != '-1'))
								alert('Job cannot be submitted unless SUCCESS is set to Yes, No, or FOC');
							else
							{
								var el_jt = document.getElementById('jt_job_type_id');
								if ((!el_jt) || (el_jt.value == 0))
									alert('Job cannot be submitted because there is no Job Type');
								else if (confirm('Do you really want to submit this job for manager approval?'))
								{
									el_co.value = -1; // set complete to review
									update_job(el_co,'n');
									alert('The job has been submitted for approval');

									relay_filters();
									document.form_main.job_id.value = $job_id;
									document.form_main.task.value = 'view';
									please_wait_on_submit();
									document.form_main.submit();
								}
							}
						}
						else
							alert('Element jt_success not found');
					}
				}
				else
					alert('Element j_complete not found');
			}
		}
		else
			alert('Element job_closed not found');
	}

	function new_address(subject_id)
	{
		relay_filters();
		document.form_main.job_id.value = $job_id;
		document.form_main.letter_id.value = subject_id;
		document.form_main.task.value = 'new_address';
		please_wait_on_submit();
		document.form_main.submit();
	}

	function bulk_add_note()
	{
		bulk_cancel_comm();
		var el = document.getElementById('note_span');
		if (el)
			el.innerHTML = '<textarea id=\"note_box\" rows=\"5\" cols=\"50\"></textarea><br><input type=\"button\" value=\"Save Note\" onclick=\"bulk_save_note()\"><input type=\"button\" value=\"Cancel\" onclick=\"bulk_cancel_note()\">';
		else
			alert('Cannot find element note_span');
	}

	function bulk_save_note()
	{
		var txt = trim(document.getElementById('note_box').value);
		if (txt != '')
		{
			var inputs = document.getElementsByTagName('input');
			var jid = '';
			var ticked = [];
			var tix = 0;
			for (var i = 0; i < inputs.length; i++)
			{
				if (inputs[i].type == 'checkbox')
				{
					if (inputs[i].name.substring(0,4) == 'tck_')
					{
						jid = inputs[i].name.replace('tck_','');
						if (isNumeric(jid,false,false,false,false))
						{
							if (inputs[i].checked)
								ticked[tix++] = jid;
						}
					}
				}
			}
			if (0 < ticked.length)
			{
				document.form_main.task.value = 'bulk_save_note';
				document.form_main.job_id.value = $job_id;
				document.form_main.ticked_jobs_main.value = ticked.toString();
				document.form_main.note_text_main.value = txt;
				relay_filters();
				please_wait_on_submit();
				document.form_main.submit();
			}
			else
				alert('No jobs are ticked, so cannot add note to ticked jobs');
		}
		else
			alert('Please enter some text into the Note box above');
	}

	function bulk_cancel_note()
	{
		var el = document.getElementById('note_span');
		el.innerHTML = '';
	}

	function bulk_set_commission()
	{
		bulk_cancel_note();
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
				var jid = '';
				var ticked = [];
				var tix = 0;
				for (var i = 0; i < inputs.length; i++)
				{
					if (inputs[i].type == 'checkbox')
					{
						if (inputs[i].name.substring(0,4) == 'tck_')
						{
							jid = inputs[i].name.replace('tck_','');
							if (isNumeric(jid,false,false,false,false))
							{
								if (inputs[i].checked)
									ticked[tix++] = jid;
							}
						}
					}
				}
				if (0 < ticked.length)
				{
					document.form_main.task.value = 'bulk_save_comm';
					document.form_main.job_id.value = $job_id;
					document.form_main.ticked_jobs_main.value = ticked.toString();
					document.form_main.note_text_main.value = txt;
					relay_filters();
					please_wait_on_submit();
					document.form_main.submit();
				}
				else
					alert('No jobs are ticked, so cannot set commission for ticked jobs');
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

//	function create_pdf(doctype, docid)
//	{
//		relay_filters();
//		document.form_main.job_id.value = $job_id;
//		document.form_main.letter_id.value = docid;
//		document.form_main.export.value = 'pdf';
//		document.form_main.doctype.value = doctype;
//		document.form_main.task.value = '$task';
//		document.form_main.submit();
//	}

	function trace_letter_is_approved()
	{
		var el = document.getElementById('jl_approved_dt');
		if (el && el.checked)
			return true;
		return false;
	}

	function trace_letter_is_sent()
	{
		var el = document.getElementById('sent_letters_count');
		if (el && (0 < el.value))
			return true;
		return false;
	}

	function add_trace_invoice(stmt, itype)
	{
		if (stmt == 1)
			alert('This job is invoiced for using Statement Invoicing and so ' + itype + ' cannot be created here.');
		else
		{
			if (trace_letter_is_approved())
			{
				if (confirm('Are you sure you want to add ' + itype + ' to this Trace job?'))
				{
					relay_filters();
					document.form_main.job_id.value = $job_id;
					document.form_main.task.value = 'add_invoice_t';
					please_wait_on_submit();
					document.form_main.submit();
				}
			}
			else
				alert('' + itype + ' can only be created one the Letter is approved');
		}
	}

	function delete_billing(bid,hasinv)
	{
		if (hasinv)
			alert('This billing item cannot be deleted because it is linked to an invoice');
		else
		{
			if (confirm('Are you sure you want to DELETE this billing item?'))
			{
				relay_filters();
				document.form_main.job_id.value = $job_id;
				document.form_main.letter_id.value = bid; // billing ID not letter ID
				document.form_main.task.value = 'del_billing';
				please_wait_on_submit();
				document.form_main.submit();
			}
		}
	}

	function delete_payment(pid)
	{
		if (confirm('Are you sure you want to DELETE this payment?'))
		{
			relay_filters();
			document.form_main.job_id.value = $job_id;
			document.form_main.letter_id.value = pid; // payment ID not letter ID
			document.form_main.task.value = 'del_payment';
			please_wait_on_submit();
			document.form_main.submit();
		}
	}

	function add_collect_letter()
	{
		var el = document.getElementById('jc_letter_type_id');
		if (0 < el.value)
		{
			el = document.getElementById('pending_letters_count');
			if (el)
			{
				if (el.value == 0)
				{
					relay_filters();
					document.form_main.job_id.value = $job_id;
					document.form_main.task.value = '$task';
					document.form_main.letter_task.value = 'letter_add';
					document.form_main.letter_id.value = document.getElementById('jc_letter_type_id').value; // ID of letter type/template
					please_wait_on_submit();
					document.form_main.submit();
				}
				else
					alert('Cannot add another letter because one is already pending');
			}
			else
				alert('Element pending_letters_count not found');
		}
		else
			alert('Please select a letter type first');
	}

	function delete_letter(lid)
	{
		if (confirm('Are you sure you want to DELETE this letter?'))
		{
			relay_filters();
			document.form_main.job_id.value = $job_id;
			document.form_main.task.value = '$task';
			document.form_main.letter_task.value = 'letter_del';
			document.form_main.letter_id.value = lid; // ID of job letter
			please_wait_on_submit();
			document.form_main.submit();
		}
	}

	function approve_letter(lid)
	{
		if (confirm('Are you sure you want to Approve this letter (so that it can be sent)?'))
		{
			relay_filters();
			document.form_main.job_id.value = $job_id;
			document.form_main.task.value = '$task';
			document.form_main.letter_task.value = 'letter_app';
			document.form_main.letter_id.value = lid; // ID of job letter
			please_wait_on_submit();
			document.form_main.submit();
		}
	}

	function search_unassigned(sys)
	{
		clear_filters();
		document.form_main.sc_sys.value = sys;
		document.form_main.sc_agent.value = '-1';
		search_js(1);
	}

	function delete_job()
	{
		if (confirm('Do you really want to DELETE this job? This cannot be undone.'))
		{
			relay_filters();
			document.form_main.job_id.value = $job_id;
			document.form_main.task.value = 'delete_job';
			please_wait_on_submit();
			document.form_main.submit();
		}
	}

	function clone_job()
	{
		if (confirm('Do you really want to create a CLONE of this job?'))
		{
			relay_filters();
			document.form_main.job_id.value = $job_id;
			document.form_main.task.value = 'clone_job';
			please_wait_on_submit();
			document.form_main.submit();
		}
	}

	function reopen_job()
	{
		if (confirm('Do you really want to re-open this job?'))
		{
			relay_filters();
			document.form_main.job_id.value = $job_id;
			document.form_main.task.value = 'reopen_job';
			please_wait_on_submit();
			document.form_main.submit();
		}
	}

	function archive_job()
	{
		if (confirm('Do you really want to archive this job?'))
		{
			relay_filters();
			document.form_main.job_id.value = $job_id;
			document.form_main.task.value = 'archive_job';
			please_wait_on_submit();
			document.form_main.submit();
		}
	}

	function unarchive_job()
	{
		if (confirm('Do you really want to un-archive this job?'))
		{
			relay_filters();
			document.form_main.job_id.value = $job_id;
			document.form_main.task.value = 'unarchive_job';
			please_wait_on_submit();
			document.form_main.submit();
		}
	}

	function close_job_inner()
	{
		relay_filters();
		document.form_main.job_id.value = $job_id;
		document.form_main.task.value = 'close_job';
		please_wait_on_submit();
		document.form_main.submit();
	}

	function close_job()
	{
		if (confirm('Do you really want to CLOSE this job?'))
			close_job_inner();
	}

	function create_collect_job()
	{
		relay_filters();
		document.form_main.job_id.value = $job_id;
		document.form_main.task.value = 'add_collect_job';
		please_wait_on_submit();
		document.form_main.submit();
	}

	function request_job()
	{
		if (confirm('Do you really want to request a new Trace Job to be assigned to yourself?'))
		{
			relay_filters();
			document.form_main.task.value = 'request_job';
			please_wait_on_submit();
			document.form_main.submit();
		}
	}

	function my_jobs()
	{
		clear_filters();
		document.form_main.sc_jopenclosed.value = '1';
		document.form_main.sc_complete.value = '0';
		for (ii = 0; ii < document.form_main.sc_agent.length; ii++)
		{
			if (document.form_main.sc_agent[ii].value == {$USER['USER_ID']})
				document.form_main.sc_agent.value = {$USER['USER_ID']};
		}
		search_js(1);
	}

	function my_diary()
	{
		clear_filters();
		document.form_main.sc_diary.checked = true;
		for (ii = 0; ii < document.form_main.sc_agent.length; ii++)
		{
			if (document.form_main.sc_agent[ii].value == {$USER['USER_ID']})
				document.form_main.sc_agent.value = {$USER['USER_ID']};
		}
		search_js(1);
	}

	function search_collect_uninvoiced()
	{
		clear_filters();
		document.form_main.sc_sys.value = 'c';
		document.form_main.sc_jopenclosed.value = '1';
		document.form_main.sc_inv.value = -2;
		search_js(1);
	}

	function search_collect_pending(scltr)
	{
		clear_filters();
		document.form_main.sc_sys.value = 'c';
		document.form_main.sc_jopenclosed.value = '1';
		document.form_main.sc_letters.value = scltr;
		document.form_main.sc_llist.checked = true;
		//document.form_main.sc_pending.checked = true;
		search_js(1);
	}

	function search_trace_overdue()
	{
		clear_filters();
		document.form_main.sc_sys.value = 't';
		document.form_main.sc_jopenclosed.value = '1';

		var dt = new Date();
		var day = dt.getDate();
		var mon = dt.getMonth() + 1;
		var yr = dt.getFullYear();
		//alert(dt + ' = ' + day + '/' + mon + '/' + yr);
		document.form_main.sc_target_to.value = day + '/' + mon + '/' + yr;
		search_js(1);
	}

	function search_trace_uninvoiced()
	{
		clear_filters();
		document.form_main.sc_sys.value = 't';
		document.form_main.sc_jopenclosed.value = '1';
		document.form_main.sc_letters.value = '1';
		document.form_main.sc_inv.value = '-1';
		search_js(1);
	}

	function search_trace_review()
	{
		clear_filters();
		document.form_main.sc_sys.value = 't';
		document.form_main.sc_jopenclosed.value = '1';
		document.form_main.sc_complete.value = '-1';
		//document.form_main.sc_inv.value = '-1';
		//document.form_main.sc_success.value = '-2';
		search_js(1);
	}

	function letter_preview_1()
	{
		setTimeout(letter_preview_2, 100); // avoid timing issues
	}

	function letter_preview_2()
	{
		relay_filters();
		document.form_main.job_id.value = $job_id;
		document.form_main.task.value = '$task';
		document.form_main.letter_task.value = 'letter_preview';
		please_wait_on_submit();
		document.form_main.submit();
	}

	function bulk_assign_agent()
	{
		var agent_id = document.getElementById('assign_id').value;
		if (0 < agent_id)
		{
			var inputs = document.getElementsByTagName('input');
			var jid = '';
			var ticked = [];
			var tix = 0;
			for (var i = 0; i < inputs.length; i++)
			{
				if (inputs[i].type == 'checkbox')
				{
					if (inputs[i].name.substring(0,4) == 'tck_')
					{
						jid = inputs[i].name.replace('tck_','');
						if (isNumeric(jid,false,false,false,false))
						{
							if (inputs[i].checked)
								ticked[tix++] = jid;
						}
					}
				}
			}
			if (0 < ticked.length)
			{
				if (confirm('Do you really want to assign all of the ticked jobs to the selected agent?'))
				{
					document.form_main.task.value = 'assign_to_agent';
					document.form_main.job_id.value = $job_id;
					document.form_main.assign_agent_main.value = agent_id;
					document.form_main.ticked_jobs_main.value = ticked.toString();
					relay_filters();
					please_wait_on_submit();
					document.form_main.submit();
				}
			}
			else
				alert('No jobs are ticked, so cannot assign jobs to agent');
		}
		else
			alert('No agent is selected, so nobody to assign jobs to');
	}

	function tick_all(max)
	{
	
	   
	
		if (!max)
			max = 0;

        max = 100;

		var inputs = document.getElementsByTagName('input');
		var jid = '';
		var count_y = 0;
		var count_n = 0;
		var chkd = false;

		// Pass 0: tick-counting.
		// Pass 1: tick-setting/clearing.

		for (pass = 0; pass < 2; pass++)
		{
			if (pass == 1)
			{
				// We always set ticks if max is specified
				if (0 < max)
					count_y = 0;
				else if (0 < count_n)
					chkd = true;
				else
					chkd = false;
			}
			// We don't need to do the tick-counting if max is specified
			if ((max == 0) || (pass == 1))
			{
				for (var i = 0; i < inputs.length; i++)
				{
					if (inputs[i].type == 'checkbox')
					{
						if (inputs[i].name.substring(0,4) == 'tck_')
						{
							jid = inputs[i].name.replace('tck_','');
							if (isNumeric(jid,false,false,false,false))
							{
								if (pass == 0)
								{
									if (inputs[i].checked)
										count_y++;
									else
										count_n++;
								}
								else if (0 < max)
								{
									if (count_y < max)
										inputs[i].checked = true;
									else
										inputs[i].checked = false;
									count_y++;
								}
								else
									inputs[i].checked = chkd;
							}
						}
					}
				}
			}
		}
	}

	function remove_from_group()
	{
		document.form_main.task.value = 'remove_from_group';
		document.form_main.job_id.value = $job_id;
		relay_filters();
		please_wait_on_submit();
		document.form_main.submit();
	}

	function goto_job(jid, vno)
	{
		document.form_job.task.value = 'view';
		document.form_job.sc_text.value = vno;
		document.form_job.job_id.value = jid;
		document.form_job.submit();
	}

	function show_audit(jid)
	{
		document.form_audit.job_id.value = jid;
		document.form_audit.submit();
	}

	function payments_add()
	{
		document.form_main.task.value = 'add_payment';
		document.form_main.job_id.value = $job_id;
		relay_filters();
		please_wait_on_submit();
		document.form_main.submit();
	}

	function billing_add()
	{
		document.form_main.task.value = 'add_billing';
		document.form_main.job_id.value = $job_id;
		relay_filters();
		please_wait_on_submit();
		document.form_main.submit();
	}

	function activity_add()
	{
		document.form_main.task.value = 'add_activity';
		document.form_main.job_id.value = $job_id;
		document.form_main.doctype.value = document.getElementById('act_new_count').value;
		relay_filters();
		please_wait_on_submit();
		document.form_main.submit();
	}

	function payments_refresh()
	{
		document.form_main.task.value = 'payments_refresh';
		document.form_main.job_id.value = $job_id;
		relay_filters();
		please_wait_on_submit();
		document.form_main.submit();
	}

	function billing_refresh()
	{
		document.form_main.task.value = 'billing_refresh';
		document.form_main.job_id.value = $job_id;
		relay_filters();
		please_wait_on_submit();
		document.form_main.submit();
	}

	function activity_refresh()
	{
		document.form_main.task.value = 'activity_refresh';
		document.form_main.job_id.value = $job_id;
		relay_filters();
		please_wait_on_submit();
		document.form_main.submit();
	}

	function add_arrange()
	{
		document.form_main.task.value = 'add_arrange';
		document.form_main.job_id.value = $job_id;
		relay_filters();
		please_wait_on_submit();
		document.form_main.submit();
	}

	function add_subject()
	{
		document.form_main.task.value = 'add_subject';
		document.form_main.job_id.value = $job_id;
		relay_filters();
		please_wait_on_submit();
		document.form_main.submit();
	}

	function add_phone()
	{
		document.form_main.task.value = 'add_phone';
		document.form_main.job_id.value = $job_id;
		relay_filters();
		please_wait_on_submit();
		document.form_main.submit();
	}

	function add_email()
	{
		document.form_main.task.value = 'add_email';
		document.form_main.job_id.value = $job_id;
		relay_filters();
		please_wait_on_submit();
		document.form_main.submit();
	}

	function add_note(txt)
	{
		document.form_main.task.value = 'add_note';
		document.form_main.note_text_main.value = txt;
		document.form_main.job_id.value = $job_id;
		relay_filters();
		please_wait_on_submit();
		document.form_main.submit();
	}

	function clear_filters()
	{
		document.form_main.sc_text.value = '';
		document.form_main.sc_addr.value = '';
		document.form_main.sc_client.value = '';
		document.form_main.sc_clref.value = '';
		document.form_main.sc_sys.value = '';
		document.form_main.sc_jobtype.value = '';
		document.form_main.sc_jobstatus.value = '';
		document.form_main.sc_date_fr.value = '';
		document.form_main.sc_date_to.value = '';
		document.form_main.sc_closed_fr.value = '';
		document.form_main.sc_closed_to.value = '';
		document.form_main.sc_act_fr.value = '';
		document.form_main.sc_act_to.value = '';
		document.form_main.sc_target_fr.value = '';
		document.form_main.sc_target_to.value = '';
		document.form_main.sc_upd_fr.value = '';
		document.form_main.sc_upd_to.value = '';
		document.form_main.sc_pmt_fr.value = '';
		document.form_main.sc_pmt_to.value = '';
		document.form_main.sc_agent.value = '';
		document.form_main.sc_inst.checked = false;
		document.form_main.sc_jopenclosed.value = '';
		document.form_main.sc_success.value = '';
		document.form_main.sc_credit.value = '';
		document.form_main.sc_complete.value = '';
		document.form_main.sc_inv.value = '';
		document.form_main.sc_bill.value = '';
		document.form_main.sc_obsolete.checked = false;
		document.form_main.sc_archived.checked = false;
		document.form_main.sc_diary.checked = false;
		document.form_main.sc_letters.value = '';
		document.form_main.sc_ltype.value = '';
		document.form_main.sc_llist.checked = false;
		//document.form_main.sc_pending.checked = false;
	}

	function relay_filters()
	{
		document.form_main.sc_text.value = '" . post_val('sc_text') . "';
		document.form_main.sc_addr.value = '" . post_val('sc_addr') . "';
		document.form_main.sc_client.value = '" . post_val('sc_client') . "';
		document.form_main.sc_clref.value = '" . post_val('sc_clref') . "';
		document.form_main.sc_sys.value = '" . post_val('sc_sys') . "';
		document.form_main.sc_jobtype.value = '" . post_val('sc_jobtype') . "';
		document.form_main.sc_jobstatus.value = '" . post_val('sc_jobstatus') . "';
		document.form_main.sc_date_fr.value = '" . post_val2('sc_date_fr') . "';
		document.form_main.sc_date_to.value = '" . post_val2('sc_date_to') . "';
		document.form_main.sc_closed_fr.value = '" . post_val2('sc_closed_fr') . "';
		document.form_main.sc_closed_to.value = '" . post_val2('sc_closed_to') . "';
		document.form_main.sc_act_fr.value = '" . post_val2('sc_act_fr') . "';
		document.form_main.sc_act_to.value = '" . post_val2('sc_act_to') . "';
		document.form_main.sc_target_fr.value = '" . post_val2('sc_target_fr') . "';
		document.form_main.sc_target_to.value = '" . post_val2('sc_target_to') . "';
		document.form_main.sc_upd_fr.value = '" . post_val2('sc_upd_fr') . "';
		document.form_main.sc_upd_to.value = '" . post_val2('sc_upd_to') . "';
		document.form_main.sc_pmt_fr.value = '" . post_val2('sc_pmt_fr') . "';
		document.form_main.sc_pmt_to.value = '" . post_val2('sc_pmt_to') . "';
		document.form_main.sc_agent.value = '" . post_val('sc_agent') . "';
		document.form_main.sc_inst.checked = " . (post_val('sc_inst',true) ? 'true' : 'false') . ";
		document.form_main.sc_jopenclosed.value = '" . post_val('sc_jopenclosed') . "';
		document.form_main.sc_success.value = '" . post_val('sc_success') . "';
		document.form_main.sc_credit.value = '" . post_val('sc_credit') . "';
		document.form_main.sc_complete.value = '" . post_val('sc_complete') . "';
		document.form_main.sc_inv.value = '" . post_val('sc_inv') . "';
		document.form_main.sc_bill.value = '" . post_val('sc_bill') . "';
		document.form_main.sc_obsolete.checked = " . (post_val('sc_obsolete',true) ? 'true' : 'false') . ";
		document.form_main.sc_archived.checked = " . (post_val('sc_archived',true) ? 'true' : 'false') . ";
		document.form_main.sc_diary.checked = " . (post_val('sc_diary',true) ? 'true' : 'false') . ";
		document.form_main.sc_letters.value = '" . post_val('sc_letters') . "';
		document.form_main.sc_ltype.value = '" . post_val('sc_ltype') . "';
		document.form_main.sc_llist.checked = " . (post_val('sc_llist',true) ? 'true' : 'false') . ";
		//document.form_main.sc_pending.checked = " . (post_val('sc_pending',true) ? 'true' : 'false') . ";
	}

	function drop_xl_button()
	{
		var but_xl = document.getElementById('but_export_xl');
		if (but_xl)
			but_xl.style.visibility = 'hidden';
		but_xl = document.getElementById('but_print_letters');
		if (but_xl)
			but_xl.style.visibility = 'hidden';
		but_xl = document.getElementById('but_sent_letters');
		if (but_xl)
			but_xl.style.visibility = 'hidden';
		but_xl = document.getElementById('reset_jobs');
		if (but_xl)
			but_xl.style.visibility = 'hidden';
		but_xl = document.getElementById('but_mass_print');
		if (but_xl)
			but_xl.style.visibility = 'hidden';
//		but_xl = document.getElementById('mp_age_dd'); // drop-down list rather than button
//		if (but_xl)
//			but_xl.style.visibility = 'hidden';
		but_xl = document.getElementById('but_upload_app');
		if (but_xl)
			but_xl.style.visibility = 'hidden';
		search_prepare(0);
	}

	function view_invoice(doctype, iid, ve)
	{
		document.form_invoices.task.value = ve; // view or edit
		document.form_invoices.doctype.value = doctype;
		document.form_invoices.invoice_id.value = iid;
		document.form_invoices.submit();
	}

	function goto_client(c2id,ccode)
	{
		document.form_client.client2_id.value = c2id;
		document.form_client.sc_text.value = ccode;
		document.form_client.task.value = 'view';
		document.form_client.submit();
	}

	function view_js(jid,ed)
	{
		";
	# Arg jid is JOB_ID. If zero then we are creating a new job (and ed==1).
	# Arg ed is edit mode: 0==view, 1=edit.
	print "
		if ((0 < jid) || confirm('Do you really want to create a New job?'))
		{
			document.form_main.task.value = (ed ? 'edit' : 'view');
			document.form_main.job_id.value = jid;
			please_wait_on_submit();
			document.form_main.submit();
		}
	}

	function search_prepare(dxl)
	{
		if (dxl == 1)
			drop_xl_button();
		document.form_main.task.value = 'search';
	}

	function formMainOnSubmit()
	{
		var el = document.getElementById('para_wait');
		var el_text = '';
		if (el)
		{
			el_text = '<br><hr>Searching...<hr><br>';
			if ( ((document.form_main.sc_text.value != '') && (isNumeric(trim(document.form_main.sc_text.value)) == false)) || (document.form_main.sc_addr.value != '') )
			{
				var wait_txt = 'Please note that searching by subject name/address will take longer than normal...';
				el_text = el_text + wait_txt + '<br><br><hr><br><br>';
				document.form_main.wait_longer.value = wait_txt;
			}
			el.innerHTML = el_text;
		}
		else
			alert('Cannot find element para_wait');
		el = document.getElementById('para_wait_extra');
		if (el)
			el.innerHTML = '';
		document.getElementById('div_form').style.display = 'none';
		document.getElementById('div_wait').style.display = 'block';

		return true;
	}

	function search_js(useSC)
	{
		if (useSC == 0)
			clear_filters();
		document.form_main.task.value = 'search';
		// Bizarrely, calling form_main.submit() doesn't automatically call formMainOnSubmit()
		formMainOnSubmit();
		document.getElementById('div_form').style.display = 'none';
		document.form_main.task.value = 'search';
		document.getElementById('div_wait').style.display = 'block';
		please_wait_on_submit();
		document.form_main.submit();
	}

	function export_xl()
	{
		document.form_main.export.value = 'xl';
		search_js(1);
	}

	function update_subject(control, subject_id, field_type, date_check)
	{
		update_job(control, field_type, date_check, subject_id, 0, 0, 0, 0, 0, 0);
	}

	function update_phone(control, phone_id, field_type, date_check)
	{
		update_job(control, field_type, date_check, 0, phone_id, 0, 0, 0, 0, 0);
	}

	function update_note(control, note_id, field_type, date_check)
	{
		update_job(control, field_type, date_check, 0, 0, note_id, 0, 0, 0, 0);
	}

//	function update_letter(control, letter_id, field_type, date_check)
//	{
//		update_job(control, field_type, date_check, 0, 0, 0, letter_id, 0, 0, 0);
//	}

	function update_payment(control, payment_id, field_type, date_check)
	{
		if (control.name == 'col_percent')
		{
			ajaxNext = 'col_percent';
			ajaxId = 'com_gbp_' + payment_id;
		}
		else if ((control.name == 'col_payment_route_id') && (control.value == $id_ROUTE_cspent))
		{
			document.getElementById('col_percent').value = 0;
		}
		update_job(control, field_type, date_check, 0, 0, 0, 0, payment_id, 0, 0);
	}

	function update_act(control, act_id, field_type, date_check)
	{
		update_job(control, field_type, date_check, 0, 0, 0, 0, 0, act_id, 0);
	}

	function update_billing(control, bill_id, field_type, date_check)
	{
		update_job(control, field_type, date_check, 0, 0, 0, 0, 0, 0, bill_id);
	}

	function subject_lookup(sid)
	{
		xmlHttp3 = GetXmlHttpObject();
		if (xmlHttp3 == null)
			return;

		var firstname = (document.getElementById('js_firstname' + sid).value);
		firstname = firstname.replace(/'/g, \"\\'\");
		firstname = firstname.replace(/&/g, \"\\u0026\");
		firstname = firstname.replace(/&/g, \"$safe_amp\");
		firstname = firstname.replace(/\//g, \"$safe_slash\");
		firstname = firstname.replace(/\\n/g, \"\\%0A\");

		var lastname = (document.getElementById('js_lastname' + sid).value);
		lastname = lastname.replace(/'/g, \"\\'\");
		lastname = lastname.replace(/&/g, \"\\u0026\");
		lastname = lastname.replace(/&/g, \"$safe_amp\");
		lastname = lastname.replace(/\//g, \"$safe_slash\");
		lastname = lastname.replace(/\\n/g, \"\\%0A\");

		var url = 'jobs_ajax.php?op=sub_lkp&i=' + sid + '&fn=' + firstname + '&ln=' + lastname;
		url = url + '&ran=' + Math.random();
		xmlHttp3.onreadystatechange = stateChanged_subject_lookup;
		xmlHttp3.open('GET', url, true);
		xmlHttp3.send(null);
	}

	function stateChanged_subject_lookup()
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

	function update_job(control, field_type, date_check, subject_id, phone_id, note_id, letter_id, payment_id, act_id, bill_id)
	{
		var abort = false;
		if (control.name == 'j_complete')
		{
			var appr_but = document.getElementById('approval_button');
			if (control.value == 0) // we are setting 'complete' to 'no'
			{
				appr_but.disabled = false;
			}
			else if ((control.value == -1) || (control.value == 1)) // we are setting 'complete' to 'review' or 'yes'
			{
				var succ = document.getElementById('jt_success');
				if (succ)
				{
					if ((succ.value == -1) || (succ.value == 0) || (succ.value == 1))
						appr_but.disabled = true;
					else
					{
						alert('COMPLETE cannot be set to ' + ((control.value == -1) ? 'REVlEW' : 'YES') + ' unless SUCCESS is first set to YES, NO or FOC');
						control.value = 0;
						abort = true;
					}
				}
				else
				{
					alert('Cannot find element jt_success');
					control.value = 0;
					abort = true;
				}
			}
			if ( (!abort) && (control.value == 1)) // we are setting 'complete' to 'yes'
			{
				if (trace_letter_is_approved() || trace_letter_is_sent())
				{
					var bcount = document.getElementById('billing_count');
					if (bcount)
					{
						if (0 < bcount.value)
						{
							var icount = document.getElementById('approved_invoice_count');
							if (icount)
							{
								if ((icount.value != -1) && (!(0 < icount.value)))
								{
									if (!confirm('There is no approved invoice. Do you really want to set COMPLETE to YES?'))
									{
										control.value = -1;
										abort = true;
									}
								}
							}
							else
							{
								alert('Cannot find element approved_invoice_count');
								control.value = -1;
								abort = true;
							}
						}
						else
						{
							if (!confirm('There is no billing. Do you really want to set COMPLETE to YES?'))
							{
								control.value = -1;
								abort = true;
							}
						}
					}
					else
					{
						alert('Cannot find element billing_count');
						control.value = -1;
						abort = true;
					}
				}
				else
				{
					alert('Cannot set COMPLETE to YES unless the Letter is approved');
					control.value = -1;
					abort = true;
				}
			}
			if ( (!abort) && (control.value == 1)) // we are setting 'complete' to 'yes'
			{
				if (confirm('Do you really want to close this job?'))
				{
					if (document.getElementById('c_closeout').value == 1)
						alert('Reminder: You may need to create and send a Closeout Report for this client');
				}
				else
				{
					control.value = -1;
					abort = true;
				}
			}
		}
		if (abort)
			return false;

		" .
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
		// subject_id:
		//				If we are updating a job subject, this is its ID
		// phone_id:
		//				If we are updating a job phone or email, this is its ID
		// note_id:
		//				If we are updating a job note, this is its ID
		// letter_id:
		//				If we are updating a job letter, this is its ID
		// payment_id:
		//				If we are updating a job payment, this is its ID
		// act_id:
		//				If we are updating a job activity, this is its ID
		// bill_id:
		//				If we are updating an invoice billing for this job, this is its ID
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
			if ((field_value == '') || (field_value == 'NULL'))
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

		xmlHttp2 = GetXmlHttpObject();
		if (xmlHttp2 == null)
			return;
		var op = 'u';
		var obj_id = {$job_id};
		if (0 < subject_id)
		{
			op = 'usu';
			obj_id = subject_id;
		}
		else if (0 < phone_id)
		{
			op = 'uph';
			obj_id = phone_id;
		}
		else if (0 < note_id)
		{
			op = 'uno';
			obj_id = note_id;
		}
		else if (0 < letter_id)
		{
			op = 'ule';
			obj_id = letter_id;
		}
		else if (0 < payment_id)
		{
			op = 'upa';
			obj_id = payment_id;
		}
		else if (0 < act_id)
		{
			op = 'uact';
			obj_id = act_id;
		}
		else if (0 < bill_id)
		{
			op = 'ubi';
			obj_id = bill_id;
		}
		var url = 'jobs_ajax.php?op=' + op + '&i=' + obj_id + '&job_id={$job_id}&n=' + field_name + '&v=' + field_value + '&ty=' + field_type;
		url = url + '&ran=' + Math.random();
		//alert(url);
		xmlHttp2.onreadystatechange = stateChanged_update_job;
		xmlHttp2.open('GET', url, true);
		xmlHttp2.send(null);
	}

	function stateChanged_update_job()
	{
		if (xmlHttp2.readyState == 4)
		{
			var resptxt = xprint_noscript(xmlHttp2.responseText);
			if (resptxt)
			{
				bits = resptxt.split('|');
				if (bits[0] == 1)
				{
					if (bits[1] == 'fees')
					{
						var fee_y = bits[2];
						var fee_n = bits[3];
						//alert('Fees ' + fee_y + ' and ' + fee_n);
						document.getElementById('jt_fee_y').value = fee_y;
						document.getElementById('jt_fee_n').value = fee_n;
						alert('Fees have been updated');
					}
					else if (bits[1] == 'target')
					{
						document.getElementById('j_target_dt').value = bits[2];
						alert('Target Date has been updated');
					}
					else if (bits[1] == 'feesandtarget')
					{
						document.getElementById('jt_fee_y').value = bits[2];
						document.getElementById('jt_fee_n').value = bits[3];
						document.getElementById('j_target_dt').value = bits[4];
						alert('Fees and Target Date have been updated');
					}
					else if (bits[1] == 'udt')
					{
						//alert(bits[2]);
						var yyyy = bits[2].substring(0,4);
						var mm = bits[2].substring(5,7);
						var dd = bits[2].substring(8,10);
						document.getElementById('j_user_dt').value = dd + '/' + mm + '/' + yyyy;
					}
					else if (bits[1] != 'ok')
					{
						if (ajaxNext != 'col_percent')
							alert('stateChanged_update_job: Ajax returned one and: ' + bits[1]);
					}
				}
				else if (bits[0] == 2)
				{
					// Reload job
					relay_filters();
					document.form_main.job_id.value = $job_id;
					document.form_main.task.value = 'edit';
					document.form_main.letter_task.value = 'reload';
					document.getElementById('div_form').style.display = 'none';
					document.getElementById('div_wait').style.display = 'block';
					please_wait_on_submit();
					document.form_main.submit();

				}
				else if (bits[0] == 3)
				{
					// Close job
					close_job_inner();
				}
				else if (bits[0] == -1)
					alert(bits[1]);
				else
					alert('stateChanged_update_job: Ajax returned: ' + resptxt);
			}
			//else
			//	alert('stateChanged_update_job: No response from ajax call!');

			if (ajaxNext == 'col_percent')
			{
				var el = document.getElementById(ajaxId);
				if (el)
					el.innerHTML = bits[1];
				else
					alert('element ' + idValue + ' not found');
			}
		}
	}

	</script>
	";
}

function print_jobs()
{
	global $ac;
	global $ar;
	global $assign_id;
	global $col2;
	global $col3;
	global $col4;
	global $col6;
	global $csv_path;
	global $excel_date_format;
	global $export;
	global $grey;
	global $JOB_STATUSES; # from lib_vilcol.php / init_data()
	global $JOB_TYPES; # from lib_vilcol.php / init_data()
	global $manager_x;
	global $nearly_all_agents;
	global $page_title_2;
	global $phpExcel_ext; # settings.php: "xls"
	#global $role_agt;
	global $sc_act_fr;
	global $sc_act_to;
	global $sc_addr;
	global $sc_agent;
	global $sc_archived;
	global $sc_bill;
	global $sc_client;
	global $sc_clref;
	global $sc_closed_fr;
	global $sc_closed_to;
	global $sc_complete;
	global $sc_credit;
	global $sc_date_fr;
	global $sc_date_to;
	global $sc_diary;
	global $sc_group;
	global $sc_inst;
	global $sc_inv;
	global $sc_jopenclosed;
	global $sc_jobstatus;
	global $sc_jobtype;
	global $sc_letters;
	global $sc_ltype;
	global $sc_llist;
	global $sc_obsolete;
	#global $sc_pending;
	global $sc_pmt_fr;
	global $sc_pmt_to;
	global $sc_sys;
	global $sc_success;
	global $sc_target_fr;
	global $sc_target_to;
	global $sc_text;
	global $sc_upd_fr;
	global $sc_upd_to;
	global $subdir;
	global $ticked_jobs;
	global $time_tests;
	global $tr_colour_1;
	global $tr_colour_2;
	global $USER;
	global $ynfoc_list;
	global $ynfrxns_list;
	global $ynpend_list;

	#if (role_check('c', $role_agt) && ($sc_sys != 't'))
	#	$c_jobs_possible = true;
	#else
	#	$c_jobs_possible = false;

	$page_title_2 = 'Jobs - Vilcol';

	$limit = 1000;
	if ($time_tests) log_write("jobs.php/print_jobs(): Enter. Calling sql_get_jobs()...");
	list($count, $jobs, $count_t, $count_c) = sql_get_jobs($sc_sys, $sc_text, $sc_addr, $sc_date_fr, $sc_date_to, $sc_client, $sc_clref, $sc_group, $sc_agent,
		$sc_inst, $limit, $sc_closed_fr, $sc_closed_to, $sc_jobtype, $sc_jobstatus, $sc_jopenclosed,
		$sc_success, $sc_credit, $sc_complete, $sc_inv, $sc_bill, $sc_act_fr, $sc_act_to, $sc_target_fr, $sc_target_to,
		$sc_obsolete, $sc_letters, $sc_ltype, $sc_pmt_fr, $sc_pmt_to, $sc_upd_fr, $sc_upd_to, $sc_diary, $sc_archived, $sc_llist);#$sc_pending
	#print("jobs=" . print_r($jobs,1));#
	#dprint("Found $count jobs/letters and count(\$jobs)=" . count($jobs));
	$count_c=$count_c; # keep code_checker quiet
	if ($time_tests) log_write("jobs.php/print_jobs(): ...done sql_get_jobs()");

	if ($time_tests) log_write("jobs.php/print_jobs(): creating table to hold jobs...()");
	if ($jobs)
	{
		$export_xl = (($export == 'xl') ? true : false);
		$mail_merge = ( ($sc_llist && (($export == 'mail_merge') || ($export == 'mass_print'))) ? true : false );

		if ($export_xl)
		{
			$filters = array();
			if ($sc_sys == 't')
				$filters[] = "Trace system";
			elseif ($sc_sys == 'c')
				$filters[] = "Collect system";
			if ($sc_jobtype)
				$filters[] = "Job Type $sc_jobtype";
			if ($sc_jobstatus)
				$filters[] = "Job Status $sc_jobstatus";
			if ($sc_complete != '')
				$filters[] = "Complete {$ynpend_list[$sc_complete]}";
			if ($sc_success != '')
				$filters[] = "Trace Success {$ynfrxns_list[$sc_success]}";
			if ($sc_credit != '')
				$filters[] = "Trace Credit {$ynfoc_list[$sc_credit]}";
			if ($sc_text)
				$filters[] = "Job $sc_text";
			if ($sc_addr)
				$filters[] = "Address $sc_addr";
			if ($sc_date_fr)
				$filters[] = "Placed $sc_date_fr";
			if ($sc_date_to)
				$filters[] = "To $sc_date_to";
			if ($sc_closed_fr)
				$filters[] = "Closed $sc_closed_fr";
			if ($sc_closed_to)
				$filters[] = "To $sc_closed_to";
			if ($sc_act_fr)
				$filters[] = "Activity from $sc_act_fr";
			if ($sc_act_to)
				$filters[] = "To $sc_act_to";
			if ($sc_target_fr)
				$filters[] = "Target date from $sc_target_fr";
			if ($sc_target_to)
				$filters[] = "To $sc_target_to";
			if ($sc_upd_fr)
				$filters[] = "Updated from $sc_upd_fr";
			if ($sc_upd_to)
				$filters[] = "To $sc_upd_to";
			if ($sc_pmt_fr)
				$filters[] = "Payment date from $sc_pmt_fr";
			if ($sc_pmt_to)
				$filters[] = "To $sc_pmt_to";
			if ($sc_client)
				$filters[] = "Client $sc_client";
			if ($sc_clref)
				$filters[] = "Cl.Ref $sc_clref";
			if ($sc_agent)
				$filters[] = "Agent " . user_name_from_id($sc_agent, true);
			if ($sc_inst)
				$filters[] = "Only jobs that have instalments set up";
			if ($sc_jopenclosed == 1)
				$filters[] = "Only jobs that are marked as Open";
			elseif ($sc_jopenclosed == 2)
				$filters[] = "Only jobs that are marked as Closed";
			if ($sc_letters == 1)
				$filters[] = "Jobs with Approved Letters";
			elseif ($sc_letters == 2)
				$filters[] = "Jobs with To-be-approved Letters";
			elseif ($sc_letters == 2)
				$filters[] = "Jobs with Sent Letters";
			#if ($sc_pending == 1)
			#	$filters[] = "Jobs with Pending Letters";
			if ($sc_archived == 1)
				$filters[] = "Archived Jobs";
			if ($sc_obsolete == 1)
				$filters[] = "Deleted Jobs";
			if ($limit)
				$filters[] = "Limited to $limit jobs";
			$title = "Jobs Search (" . implode(', ', $filters) . ")";
			$title_short = "Jobs Search";
			$xfile = "jobs_search_{$USER['USER_ID']}";
		}
		elseif ($mail_merge)
		{
			$title = '';
			$title_short = "Letters";
			$xfile = "letter_mail_merge_{$USER['USER_ID']}";
		}
		else
		{
			$title = '';
			$title_short = '';
			$xfile = '';
		}

		$headings_mail_merge = '';
		if ($sc_diary)
			$headings = array('Agent', 'Diary Date', 'Diary Text', 'VILNo', 'Sequence', 'Subject Name', 'Client', 'Client Name');
		elseif ($sc_llist)
		{
			$headings = array('System', 'VILNo', 'Subject Name', 'Client', 'Client Name', 'Agent', 'Letter', 'PDF', 'Approved', 'Sent');
			if ($mail_merge)
				$headings_mail_merge = array('Letter Type', 'VILNO', 'Title', 'Firstname', 'Surname',
					'Address1', 'Address2', 'Address3', 'Address4', 'Address5', 'Postcode',
					'Client', 'Balance');
		}
		else
		{
			$headings = array('System', 'VILNo', 'Sequence', 'Subject Name', 'Client', 'Client Name', 'Agent', 'Received');
			if (0 < $count_t)
			{
				$headings[] = 'Report Approved';
				$headings[] = 'Letter Approved';
			}
		}

		$subcount = count($jobs);
		$from = (($subcount < $count) ? (" from a set of " . number_with_commas($count)) : '');
		$object_type = 'job';
		if ($sc_diary)
			$object_type = 'date';
		elseif ($sc_llist)
			$object_type = 'letter';
		$summary = "Showing " . number_with_commas($subcount) . " $object_type" . (($subcount == 1) ? '' : 's') . "{$from}.";
		#print "$summary &nbsp;&nbsp;&nbsp;&nbsp;
		#			" . input_button('Export to Excel', "export_xl()", '', 'but_export_xl') . "
		if ($sc_diary)
			print "<h3>Diary List</h3>";
		elseif ($sc_llist)
			print "<h3>Letter List</h3>";
		else
		{
			if ($sc_inv == -2)
				print "<p>Note that \"Uninvoiced Payments\" only considers payments that have occurred within the last two years, on jobs that are still open.</p>";
			else
				print "<br>";
		}
		print "
		<table name=\"table_main\" class=\"spaced_table\" border=\"0\"><!---->
		<tr>
			<td $col4>$summary</td>
		";
		if ($sc_diary)
		{
			print "
			<td $col2>" . input_button('Export list to Excel', "export_xl()", $manager_x ? '' : 'disabled', 'but_export_xl') . "</td>
				";
		}
		elseif ($sc_llist)
		{
			$tick_max = (count($_POST) ? 100 : post_val('tick_max',true));
			$mp_age = post_val2('mp_age_main', true);
			print "
			<td $col2>" . input_button('Export list to Excel', "export_xl()", $manager_x ? '' : 'disabled', 'but_export_xl') . "</td>
			<td $col3 $ar>" . input_button("Tick $tick_max", "tick_some($tick_max)", '', 'but_tick_some') .
				input_textbox('tick_max', $tick_max, 2, 5, "style=\"text-align:right;\" onkeyup=\"tick_max_changed(this)\"") . "</td>
			<td $ac $col3>" .
				#REVIEW input_button('Print ticked approved letters', "print_letters()", $manager_x ? '' : 'disabled', 'but_print_letters') .
				input_button('Mail Merge to Excel', "mail_merge_excel()", $manager_x ? '' : 'disabled', 'but_print_letters') .
				'<br>' .
				input_button('Mass Print', "mass_print()", $manager_x ? '' : 'disabled', 'but_mass_print') .
				input_select('mp_age_dd', array('0' => 'All', -1 => 'Old', 1 => 'New'), $mp_age, "title=\"All letters, or just letters with Old letterheads, or just letters with New letterheads\" onchange=\"document.form_main.mp_age_main.value=this.value;search_js(1)\"", true) .
				"</td>
			<td $col2>" .
				input_button('Mark as Sent', "mark_as_sent()", $manager_x ? '' : 'disabled', 'but_sent_letters') .
				"</td>

			";
			if (global_debug())
			{
				print "
				<td>" .
					input_button('Upl. App', "upload_app()", $manager_x ? '' : 'disabled', 'but_upload_app') .
					//input_button('Hack Dates', "hack_dates()") .
					"</td>
				";
			}
		}
		elseif ($manager_x)
		{
			print "
			<td>&nbsp;&nbsp;" .
				input_button('Export to Excel', "export_xl()", $manager_x ? '' : 'disabled', 'but_export_xl') .
				((0 < $count_c) ? input_button('Reset Jobs', "reset_jobs()", $manager_x ? '' : 'disabled', 'but_reset_jobs') : '') .
				"</td>
			<td $col2>Assign ticked jobs to: " . input_select('assign_id', $nearly_all_agents, $assign_id, $manager_x ? '' : 'disabled') . "</td>
			<td>" . input_button('Assign', "bulk_assign_agent()", $manager_x ? '' : 'disabled') . "</td>
			<td>" . input_button('Add Note', "bulk_add_note()", $manager_x ? '' : 'disabled') . "</td>
			<td $col3>" . input_button('Set Commission', "bulk_set_commission()", $manager_x ? '' : 'disabled') . "</td>
				";
		}
		print "
		</tr>
		<tr><td $col6></td><td $col3 $ar><span id=\"note_span\"></span></td><td $col3><span id=\"comm_span\"></span></td></tr>
		<tr>
			<th></th>
			";
		foreach ($headings as $hd)
		{
			if ($sc_llist && ($hd == 'Letter'))
				print "<th>" . input_button('100', 'tick_all()', "style=\"width:30px;\"") . "</th>";
			print "<th>$hd</th>";
		}
		if ((!$sc_diary) && (!$sc_llist))
		{
			print "<th>" . ($manager_x ? input_button('100', 'tick_all()', "style=\"width:30px;\"") : '') . "</th>";
			if ($sc_inst)
				#if ($c_jobs_possible)
				print "<th>Instalments</th>";
			#if (0 < $sc_jopenclosed)
			print "<th>Open</th>";
			print "<th $grey>Imported?</th>";
		}
		print "<th $grey>Job ID</th>";
		if ($sc_llist)
			print "<th $grey>Letter ID</th>";
		print "
		</tr>
		";

		$datalines = array(); # for export file
		$ticked_letters = (($sc_llist && $mail_merge)? explode(',', post_val('ticked_jobs_main')) : array());
		#dprint("ticked_letters=" . print_r($ticked_letters,1));#
		$trcol = $tr_colour_1;
		if ($time_tests) log_write("jobs.php/print_jobs(): creating each job one by one...");
		foreach ($jobs as $one)
		{
			if ($one['JT_JOB'] == 1)
			{
				$jtid = intval($one['JT_JOB_TYPE_ID']);
				if (array_key_exists($jtid, $JOB_TYPES))
				{
					$jtc = $JOB_TYPES[$jtid]['JT_CODE'];
					if ($jtc == 'TRC')
						$jtc = '';
				}
				else
					#$jtc = '-';
					$jtc = '';
				$sys = "Trace" . ($jtc ? " ($jtc)" : '');
				$instal = '';
			}
			else
			{
				$jsid = intval($one['JC_JOB_STATUS_ID']);
				if (array_key_exists($jsid, $JOB_STATUSES))
					$jst = $JOB_STATUSES[$jsid]['J_STATUS'];
				else
					#$jst = '-';
					$jst = '';
				$sys = "Collect" . ($jst ? " ($jst)" : '');
				$instal = floatval($one['JC_INSTAL_AMT']);
				if ($instal > 0)
					$instal = money_format_kdb($instal);
				else
					$instal = '-';
			}
			#$client = $one['C_CODE'] . ': ' . $one['C_CO_NAME'];
			if ($one['JS_COMPANY'])
				$subject = $one['JS_COMPANY'];
			else
				$subject = title_first_last($one['JS_TITLE'], $one['JS_FIRSTNAME'], $one['JS_LASTNAME'], '(none)');
			$placement_s = date_for_sql($one['J_OPENED_DT'], true, false, true); # for screen
			$placement_x = date_for_sql($one['J_OPENED_DT'], true, false, false); # for export
			$agent = trim("{$one['U_FIRSTNAME']} {$one['U_LASTNAME']}");
			$imported = (($one['IMPORTED'] == 1) ? 'Yes' : 'No');
			$job_open = ($one['J_ARCHIVED'] ? 'Archived' : (($one['JOB_CLOSED'] == 1) ? 'No' : 'Yes'));

			if ($sc_diary)
			{
				$diary_dt_s = date_for_sql($one['J_DIARY_DT'], true, false, true); # for screen
				$diary_dt_x = date_for_sql($one['J_DIARY_DT'], true, false, false); # for export
				$diary_txt = str_replace('Imported from old database', '', $one['J_DIARY_TXT']);
			}
			else
			{
				$diary_dt_s = '';
				$diary_dt_x = '';
				$diary_txt = '';
			}

			# $line_s is line for screen, $line_x is line for export
			if ($sc_diary)
			{
				$line_s = array($one['U_INITIALS'], $diary_dt_s, $diary_txt, $one['J_VILNO'], $one['J_SEQUENCE'], $subject, $one['C_CODE'], $one['C_CO_NAME']);
				$line_x = array($one['U_INITIALS'], $diary_dt_x, $diary_txt, $one['J_VILNO'], $one['J_SEQUENCE'], $subject, $one['C_CODE'], $one['C_CO_NAME']);
			}
			elseif ($sc_llist)
			{
				if ($one['JL_IMPORTED'])
					$approved = 'Yes';
				elseif ($one['JL_APPROVED_DT'])
					$approved = date_for_sql($one['JL_APPROVED_DT'], true, false, true);
				else
					$approved = '-';
				if ($one['JL_IMPORTED'])
					$sent = 'Yes';
				elseif ($one['EM_DT'])
					$sent = date_for_sql($one['EM_DT'], true, false, true);
				elseif ($one['JL_POSTED_DT'])
					$sent = date_for_sql($one['JL_POSTED_DT'], true, false, true);
				else
					$sent = '-';
				$line_s = array($sys, $one['J_VILNO'], $subject, $one['C_CODE'], $one['C_CO_NAME'], $one['U_INITIALS'],
					$one['LETTER_NAME'],
					($one['PDF_LINK'] ?
						"<a href=\"{$one['PDF_LINK']}\" target=\"_blank\" rel=\"noopener\"><img src=\"images/pdf.png\" height=\"23\" width=\"23\"></a>"
						: ''),
					$approved, $sent);
				if ($export_xl)
					$line_x = array($sys, $one['J_VILNO'], $subject, $one['C_CODE'], $one['C_CO_NAME'], "{$one['U_INITIALS']} ($agent)",
						$one['LETTER_NAME'],
						($one['PDF_LINK'] ? 'PDF' : ''),
						$one['JL_APPROVED_DT'], $sent, $one['JOB_LETTER_ID']);
				elseif ($mail_merge && in_array($one['JOB_LETTER_ID'], $ticked_letters))
				{
					if ($one['JS_COMPANY'])
					{
						$su_ti = $one['JS_COMPANY'];
						$su_fi = '';
						$su_la = '';
					}
					else
					{
						$su_ti = $one['JS_TITLE'];
						$su_fi = $one['JS_FIRSTNAME'];
						$su_la = $one['JS_LASTNAME'];
					}
					$line_x = array($one['LETTER_NAME'], $one['J_VILNO'], $su_ti, $su_fi, $su_la,
						$one['JS_ADDR_1'], $one['JS_ADDR_2'], $one['JS_ADDR_3'], $one['JS_ADDR_4'], $one['JS_ADDR_5'], $one['JS_ADDR_PC'],
						"{$one['C_CO_NAME']} ({$one['C_CODE']})", round($one['JC_TOTAL_AMT'],2));
				}
				else
					$line_x = '';
			}
			else
			{
				$report_approved = '';
				$letter_approved = '';
				if ($one['JT_JOB'] == 1)
				{
					if ($one['IMPORTED'])
					{
						$report_approved = 'Yes';
						$letter_approved = 'Yes';
					}
					else
					{
						if ($one['JT_REPORT_APPR'])
							$report_approved = date_for_sql($one['JT_REPORT_APPR'], true, false, true);
						else
							$report_approved = '-';
						if (array_key_exists('JL_APPROVED_DT', $one) && $one['JL_APPROVED_DT'])
							$letter_approved = date_for_sql($one['JL_APPROVED_DT'], true, false, true);
						else
							$letter_approved = '-';
					}
				}

				$line_s = array($sys, $one['J_VILNO'], $one['J_SEQUENCE'], $subject, $one['C_CODE'], $one['C_CO_NAME'], $one['U_INITIALS'], $placement_s);
				$line_x = array($sys, $one['J_VILNO'], $one['J_SEQUENCE'], $subject, $one['C_CODE'], $one['C_CO_NAME'],
					"{$one['U_INITIALS']} ($agent)", $placement_x);
				if (0 < $count_t)
				{
					$line_s[] = $report_approved;
					$line_s[] = $letter_approved;
					$line_x[] = $report_approved;
					$line_x[] = $letter_approved;
				}
			}
			if ($line_x)
				$datalines[] = $line_x;

			$tdcol = ($one['J_ARCHIVED'] ? "style=\"color:#505050;\"" : ($one['OBSOLETE'] ? "style=\"color:red;\"" : ''));

			$allow_edit = false;
			if ($one['JT_JOB'])
			{
				if ((!$one['JOB_CLOSED']) || $manager_x)
					$allow_edit = true;
			}
			else
			{
				if ((!$one['JOB_CLOSED']) || $manager_x)
					$allow_edit = true;
			}
			print "
			<tr bgcolor=\"$trcol\" fgcolor=\"red\" onmouseover=\"this.className='Highlight'\" onmouseout=\"this.className='Normal'\">
				<td style=\"white-space: nowrap;\">" . input_button('View', "view_js({$one['JOB_ID']},0)") .
				input_button('Edit', "view_js({$one['JOB_ID']},1)", $allow_edit ? '' : 'disabled') . "</td>
				";
			if ($sc_diary)
			{
				print "
					<td $tdcol title=\"$agent\" $ac>{$line_s[0]}</td>
					<td $tdcol $ar>{$line_s[1]}</td>
					<td $tdcol>{$line_s[2]}</td>
					<td $tdcol $ar>{$line_s[3]}</td>
					<td $tdcol $ar>{$line_s[4]}</td>
					<td $tdcol>{$line_s[5]}</td>
					<td $tdcol $ar>{$line_s[6]}</td>
					<td $tdcol>{$line_s[7]}</td>
					<td $ar $grey>{$one['JOB_ID']}</td>";
			}
			elseif ($sc_llist)
			{
				$pdf_link = $line_s[7];
				$can_tick = $pdf_link; # || global_debug();#
				print "
					<td  style=\"white-space: nowrap;\" $tdcol>{$line_s[0]}</td>
					<td $tdcol $ar>{$line_s[1]}</td>
					<td $tdcol>{$line_s[2]}</td>
					<td $tdcol $ar>{$line_s[3]}</td>
					<td $tdcol>{$line_s[4]}</td>
					<td $tdcol title=\"$agent\" $ac>{$line_s[5]}</td>
					<td $tdcol $ac>" .
					($can_tick ? input_tickbox('', "tck_{$one['JOB_LETTER_ID']}", 1, in_array($one['JOB_LETTER_ID'], $ticked_letters) ? true : false) : '') .
					"</td>
					<td $tdcol>{$line_s[6]}</td>
					<td $ar>$pdf_link</td>
					<td $tdcol $ac>{$line_s[8]}</td>
					<td $tdcol $ac>{$line_s[9]}</td>
					<td $ar $grey>{$one['JOB_ID']}</td>
					<td $ar $grey>{$one['JOB_LETTER_ID']}</td>
					";
//					<td $tdcol $ac>" . ($line_s[7] ? 'Yes' : 'No') . "</td>
//					<td $tdcol $ac>" . ($line_s[8] ? 'Yes' : 'No') . "</td>
			}
			else
			{
				print "
					<td  style=\"white-space: nowrap;\" $tdcol>{$line_s[0]}</td>
					<td $tdcol $ar>{$line_s[1]}</td>
					<td $tdcol $ar>{$line_s[2]}</td>
					<td $tdcol>{$line_s[3]}</td>
					<td $tdcol $ar>{$line_s[4]}</td>
					<td $tdcol>{$line_s[5]}</td>
					<td $tdcol title=\"$agent\" $ac>{$line_s[6]}</td>
					<td $tdcol $ar>{$line_s[7]}</td>
					";
				if (0 < $count_t)
					print "
					<td $tdcol $ar>{$line_s[8]}</td>
					<td $tdcol $ar>{$line_s[9]}</td>
						";
				print "
					<td $tdcol $ac>" . (((!$manager_x) || $one['OBSOLETE'] || $one['J_ARCHIVED']) ? '' :
						input_tickbox('', "tck_{$one['JOB_ID']}", 1, in_array($one['JOB_ID'], $ticked_jobs) ? true : false)) . "</td>
					";
				if ($sc_inst)
					#if ($c_jobs_possible)
					print "<td $tdcol $ar>$instal</td>";
				#if (0 < $sc_jopenclosed)
				print "<td $tdcol $ac>$job_open</td>";
				print "<td $grey $ac>$imported</td><td $ar $grey>{$one['JOB_ID']}</td>";
			}
			print "
			</tr>
			";
			$trcol = (($trcol == $tr_colour_1) ? $tr_colour_2 : $tr_colour_1);
		} # foreach job
		print "
		</table><!--table_main-->
		";
		if ($time_tests) log_write("jobs.php/print_jobs(): ... done table of jobs");

		if ($export_xl)
		{
			if ($time_tests) log_write("jobs.php/print_jobs(): exporting to Excel...");
			$top_lines = array(array($title), array($summary), array());
			if ($sc_diary)
				$formats = array('B' => $excel_date_format);
			elseif (!$sc_llist)
				$formats = array('H' => $excel_date_format);
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
			if ($time_tests) log_write("jobs.php/print_jobs(): ...done export to Excel");
		}
		elseif ($mail_merge)
		{
			if ($time_tests) log_write("jobs.php/print_jobs(): mail-merging to Excel...");
			$top_lines = array();
			$formats = array();
			$sheet = array('Letters', $top_lines, $headings_mail_merge, $datalines, $formats, '');
			phpExcel_output($subdir, $xfile, array($sheet)); # library.php

			# Auto-download file to PC
			print "
			<script type=\"text/javascript\">
				document.form_csv_download.short_fname.value = '$xfile.$phpExcel_ext';
				document.form_csv_download.full_fname.value = '$csv_path' + '{$subdir}/$xfile.$phpExcel_ext';
				document.form_csv_download.submit();
			</script>
			";
			if ($time_tests) log_write("jobs.php/print_jobs(): ...done mail-merging to Excel");
		}
	}
	else#if ($sc_sys || $sc_text || $sc_client)
		dprint("No jobs matched your search.", true);
	#else
	#	dprint("There are no Jobs in the database.", true);

	if ($time_tests) log_write("jobs.php/print_jobs(): Exit print_jobs().");
} # print_jobs()

function print_one_job($editing)
{
	global $ab;
	global $ac;
	#global $added_arrange_id; # from screen_content()
	global $added_activity_id; # from screen_content()
	global $added_billing_id; # from screen_content()
	global $added_email_id; # from screen_content()
	global $added_note_id; # from screen_content()
	global $added_payment_id; # from screen_content()
	global $added_phone_id; # from screen_content()
	global $added_subject_id; # from screen_content()
	global $agent_c;
	global $ar;
	global $at;
	global $blank_line;
	global $calendar_names;
	global $can_edit;
	global $can_edit_client;
	global $col2;
	global $col3;
	global $col4;
	global $col5;
	global $col6;
	global $col8;
	global $col10;
	global $col11;
	global $crlf;
	global $div_h_max;
	global $extra_tck;
	global $extra_tck_m;
	global $job_id;
	global $gap;
	global $grey;
	global $grey_colour;
	global $invoice_trace_ap; # whether the trace job has an invoice that is approved
	global $invoice_trace_ex; # whether the trace job has an invoice that exists
	global $job_statuses_sel; # init_data()
	global $last_job;
	global $last_job_id;
	global $manager_a;
	global $manager_c;
	global $manager_t;
	global $manager_x;
	global $onchange_dt;
	global $onchange_dt_m;
	global $onchange_mon;
	global $onchange_mon_m;
	global $onchange_num;
	global $onchange_num_m;
	global $onchange_pc;
	global $onchange_pc_m;
	global $onchange_sel;
	global $onchange_sel_m;
	global $onchange_sel_suc;
	global $onchange_sel_txt;
	global $onchange_sel_txt_m;
	global $onchange_tck;
	global $onchange_tck_m;
	global $onchange_txt;
	global $onchange_txt_m;
	global $page_title_2;
	#global $pdf_message;
	global $span_warn;
	global $style_r;
	global $szlong;
	global $szmid;
	global $szsmall;
	global $szsmall2;
	global $ta_cols;
	global $time_tests;
	global $USER;
	global $user_debug;

	if ($time_tests) log_write("jobs.php/print_one_job(): Enter");

	$user_debug = user_debug();

	#dprint("\$editing=$editing");#

	if ($time_tests) log_write ("print_one_job($job_id) - calling sql_get_one_job()");
	$span_warn = '';
	if ($job_id == $last_job_id)
		$job = $last_job;
	else
		$job = sql_get_one_job($job_id, true);
	$page_title_2 = "Job {$job['J_VILNO']} - Vilcol";
	#dprint("Job User=" . $job['J_USER_ID']);
	#dprint("Job Success = \"{$job['TRACE_DETAILS']['JT_SUCCESS']}\"");#
	if ($time_tests) log_write ("print_one_job($job_id) - back from sql_get_one_job()");

	if ($editing && $job['C_ARCHIVED'])
	{
		$can_edit = false;
		$editing = false;
		dprint("\$editing=$editing because: archived");#
	}

	if ($editing && (!$can_edit))
	{
		$can_edit = false;
		$editing = false;
		dprint("\$editing=$editing because: can't edit");#
	}

	$deleted_job = (($job['OBSOLETE'] == 1) ? true : false);
	if ($deleted_job)
	{
		$can_edit = false;
		$editing = false;
		dprint("\$editing=$editing because: deleted");#
	}

	$trace_job = (($job['JT_JOB'] == 1) ? true : false);
	if (!$manager_x)
	{
		if (	($job['JOB_CLOSED'] == 1) || # Don't allow an agent to edit a closed job
			($trace_job && ($job['J_AVAILABLE'] != 1)) # Don't allow an agent to edit a trace job that's not available to agents
			#|| ($job['J_USER_ID'] != $USER['USER_ID']) # Don't allow an agent to edit a job that's not theirs
			# || ( ($job['JT_JOB'] == 1) && ($job['TRACE_DETAILS']['J_COMPLETE'] == -1) ) # Don't allow an agent to edit a review trace job
		)
		{
			$can_edit = false;
			$editing = false;
			dprint("\$editing=$editing because: agent and (closed or unavailable-trace)");#
		}
	}

	# If user is an agent and this is a trace job under review, then only allow user to edit notes.
	if ((!$manager_x) && (($job['JT_JOB'] == 1) && ($job['TRACE_DETAILS']['J_COMPLETE'] == -1)))
		$eon = true; # edit only notes
	# If user is a manager and the job is closed, then only allow user to edit notes.
	elseif ($manager_x && ($job['JOB_CLOSED'] == 1))
		$eon = true; # edit only notes
	else
		$eon = false;
	#dprint("\$eon=$eon");#

	if ($time_tests) log_write ("print_one_job($job_id) - updating U_JOB_ID");
	if ((0 < $job_id) && (!$deleted_job))
	{
		$sql = "UPDATE USERV SET U_JOB_ID=$job_id WHERE USER_ID={$USER['USER_ID']}";
		sql_execute($sql); # no need to audit
	}
	if ($time_tests) log_write ("print_one_job($job_id) - back from updating U_JOB_ID");

	#dprint(print_r($job['SUBJECTS'],1));#

	$system = ($trace_job ? 't' : 'c');
	$job_is = ($job['J_ARCHIVED'] ? 'Archived' : ($job['JOB_CLOSED'] ? 'Closed' : 'Open'));
	$open = $job['JOB_CLOSED'] ? false : true;

	$invoice_trace_ex = false; # whether the trace job has an invoice that exists
	$invoice_trace_ap = false; # whether the trace job has an invoice that is approved
	if ($trace_job)
	{
		foreach ($job['BILLING'] as $details)
		{
			if (0 < $details['INVOICE_ID'])
			{
				$invoice_trace_ex = true;
				if ($details['INV_APPROVED_DT'])
					$invoice_trace_ap = true;
				break;
			}
		}
	}


	$div_h_max = 300;
	$blank_line = "<tr><td>&nbsp;</td></tr>";

	#$subject = $job['SUBJECTS'][0]; # the primary subject

	$client_can_be_changed = $can_edit_client;
	if ($client_can_be_changed)
	{
		if ($time_tests) log_write ("print_one_job($job_id) - calling sql_get_clients_for_select()");
		$clients = sql_get_clients_for_select('*', $job['CLIENT2_ID']);# Takes 10 seconds 19/01/17! #$system); # 28/07/16 - passing $system can mean client is not displayed on screen.
		if ($time_tests) log_write ("print_one_job($job_id) - done");
		#dprint("Job client = {$job['CLIENT2_ID']}, system=\"$system\",<br>list = " . print_r($clients,1));#
	}
	else
		$clients = array($job['CLIENT2_ID'] => "{$job['C_CODE']} - {$job['C_CO_NAME']}");

	if ($time_tests) log_write ("print_one_job($job_id) - calling sql_get_agents()/1");
	$agents = sql_get_agents($system, true, false, (0 < $job['J_USER_ID']) ? array($job['J_USER_ID']) : '');
	if ($time_tests) log_write ("print_one_job($job_id) - done");

	if ($time_tests) log_write ("print_one_job($job_id) - calling sql_get_agents()/2");
	$agents_all = sql_get_agents($system, true, true, (0 < $job['J_CLOSED_ID']) ? array($job['J_CLOSED_ID']) : '');
	if ($time_tests) log_write ("print_one_job($job_id) - done");
	#dprint("agents_all=" . print_r($agents_all,1));#

	$onchange_txt = (($editing && $open && (!$eon)) ? "onchange=\"update_job(this);\"" : 'readonly');
	$onchange_num = (($editing && $open && (!$eon)) ? "onchange=\"update_job(this,'n');\"" : 'readonly');
	$onchange_mon = (($editing && $open && (!$eon)) ? "onchange=\"update_job(this,'m');\"" : 'readonly');
	$onchange_pc = (($editing && $open && (!$eon)) ? "onchange=\"update_job(this,'p');\"" : 'readonly');
	$onchange_sel = (($editing && $open && (!$eon)) ? "onchange=\"update_job(this,'n');\"" : 'disabled');
	$onchange_sel_txt = (($editing && $open && (!$eon)) ? "onchange=\"update_job(this);\"" : 'disabled'); # when 'value' of each option is text not number
	$onchange_sel_suc = (($editing && $open && (!$eon)) ? "onclick=\"jt_success_clicked(this)\" onchange=\"jt_success_changed(this,".($manager_x?1:0).");\"" : 'disabled'); # when 'value' of each option is text not number
	$onchange_dt = (($editing && $open && (!$eon)) ? "onchange=\"update_job(this,'d');\"" : 'readonly');
	$onchange_tck = (($editing && $open && (!$eon)) ? "update_job(this,'t')" : '');
	$extra_tck = (($editing && $open && (!$eon)) ? '' : 'disabled');

	$onchange_txt_m = (($editing && $open && $manager_x) ? "onchange=\"update_job(this);\"" : 'readonly');
	$onchange_num_m = (($editing && $open && $manager_x) ? "onchange=\"update_job(this,'n');\"" : 'readonly');
	$onchange_mon_m = (($editing && $open && $manager_x) ? "onchange=\"update_job(this,'m');\"" : 'readonly');
	$onchange_pc_m = (($editing && $open && $manager_x) ? "onchange=\"update_job(this,'p');\"" : 'readonly');
	$onchange_sel_m = (($editing && $open && $manager_x) ? "onchange=\"update_job(this,'n');\"" : 'disabled');
	$client_dblclick = (($editing && $open && $manager_x) ? "ondblclick=\"reload_with_client();\"" : '');
	$onchange_sel_jusr_m = (($editing && $open && $manager_x) ? "onchange=\"juser_set(this);\"" : 'disabled');
	$onchange_sel_txt_m = (($editing && $open && $manager_x) ? "onchange=\"update_job(this);\"" : 'disabled'); # when 'value' of each option is text not number
	$onchange_dt_m = (($editing && $open && $manager_x) ? "onchange=\"update_job(this,'d');\"" : 'readonly');
	$onchange_tck_m = (($editing && $open && $manager_x) ? "update_job(this,'t')" : '');
	$extra_tck_m = (($editing && $open && $manager_x) ? '' : 'disabled');
	$onchange_tck_ja = (($editing && $open && $manager_x) ? "j_available_ticked(this)" : '');
	$extra_tck_ja = (($editing && $open && $manager_x) ? '' : 'disabled');

	$onchange_grpadd = (($editing && $manager_x) ? "onchange=\"update_job(this,'n');\"" : 'readonly');

	$szsmall = 10;
	$szsmall2 = 15;
	#$szsm2 = 20;
	$szmid = 30;
	$szlong = 50;
	$szxlong = 60;
	$div_h = 180;
	$div_h_x2 = ((2 * $div_h) + 23);
	$gap = "<td width=\"15\">&nbsp;</td>";
	$pink = "style=\"background-color:#ffd0d0\"";
	$gry = ($trace_job ? "style=\"background-color:#d0d0d0\"" : '');
	$numcols = 13; # Number of columns in table_main
	$ta_cols = 60;

	$jump_to_top = "
	<tr>
		<td $col10>" . input_button("Jump to top", "jump('page_top')") . "</td>
	</tr>
	";

//	#$client = $job['C_CODE'] . ': ' . $job['C_CO_NAME'];
//	#if ($job['JS_COMPANY'])
//		$subject_comp = $subject['JS_COMPANY'];
//	#else
//		$subject_per = title_first_last($subject['JS_TITLE'], $subject['JS_FIRSTNAME'], $subject['JS_LASTNAME'], '');
//	if (($subject_per == '') && ($subject_comp == ''))
//		$subject_per = '(none)';

	if ($time_tests) log_write ("print_one_job($job_id) - printing table");

	if ($trace_job)
	{
		$onchange_tck_jsi = $onchange_tck_m;
		$extra_tck_jsi = $extra_tck_m;
	}
	else
	{
		$onchange_tck_jsi = '';
		$extra_tck_jsi = 'disabled';
	}
	$job_title = ($deleted_job ? "*DELETED* " : '') . ($trace_job ? "Trace" : "Collection") . " Job";
	print "<span style=\"font-size:14px; font-weight:bold;" . ($deleted_job ? " font-color:red;" : '') . "\">$job_title</span>&nbsp;&nbsp;
	" . input_button('View', "view_js($job_id,0)") . "
	";
	if ($can_edit)
		print "
		" . input_button('Edit', "view_js($job_id,1)") . "
		";

	for ($ii = 0; $ii < 20; $ii++)
		print "&nbsp;";
	print "Jump to: " . input_button('Notes', "jump('notes')") . ' ' . input_button('Letter', "jump('letter')") . ' ';
	if ($trace_job)
		print input_button('Billing', "jump('billing')");
	else
		print input_button('Arrangement', "jump('arrangements')") . ' ' . input_button('Payments', "jump('payments')") . ' ' .
			input_button('Activity', "jump('activity')");

	if ($job['J_ARCHIVED'] || $job['C_ARCHIVED'])
	{
		$div_main_colour = "style=\"background-color:gray\"";
		if ($job['C_ARCHIVED'])
			print "<p>THIS JOB (AND ITS CLIENT) ARE ARCHIVED</p>
				";
		else
			print "<p>THIS JOB IS ARCHIVED</p>
				";
	}
	else
		$div_main_colour = "";
	print "
	<div id=\"div_main\" $div_main_colour>
	<span id=\"span_warn\" style=\"color:red\"></span>
	<table id=\"table_main\" class=\"basic_table\" border=\"0\"><!---->
	<tr>
		<td $ar>VILNO</td>
		<td>" . input_textbox('j_vilno', $job['J_VILNO'], $szsmall2, 20, $onchange_num_m) . "</td>
		";
	if ($manager_x)
	{
		$group_add_button = "Group with VILNO " . input_textbox('group_addition', '', 6, 20, "$style_r $onchange_grpadd") . "";
		$group_del_button = input_button('Remove from Group', "remove_from_group()");
		if ($job['GROUP_MEMBERS'] || $user_debug)
		{
			print "
				$gap
				<td $ar>Grouping</td>
				";
			if ($job['GROUP_MEMBERS'])
				print "<td $ar $at>Job Group Members:</td>";
			else
				print "<td>" . input_textbox('x', 'This job is not in a group.', $szmid, '', 'readonly') . "</td><td $col2></td>";
			print "
				<td $col8>";
			if ($job['GROUP_MEMBERS'])
			{
				print "<table>";
				$gix = 0;
				foreach ($job['GROUP_MEMBERS'] as $member)
				{
					if (($gix % 5) == 0)
					{
						if (0 < $gix)
							print "</tr>";
						print "<tr>";
					}
					print "<td>" . input_button("{$member['J_VILNO']}/{$member['J_SEQUENCE']}", "goto_job({$member['JOB_ID']},{$member['J_VILNO']});") . "</td>";
					$gix++;
				}
				print "</tr></table>";
				#print print_r($job['GROUP_MEMBERS'],1);
			}
			else
				print "&nbsp;";
			if ($editing && (!$eon))
				print "&nbsp;&nbsp;&nbsp;$group_add_button" . ($job['GROUP_MEMBERS'] ? "&nbsp;&nbsp;&nbsp;$group_del_button" : '');
		}
		elseif ($editing && (!$eon))
			print "<td $col10>$group_add_button</td>";
	}
	else
		print "<td $col10></td>";
	print "
	</tr>
	<tr>
		<td $ar>Sequence</td>
		<td>" . input_textbox('j_sequence', $job['J_SEQUENCE'], $szsmall2, 20, $onchange_num_m) . "</td>
		$gap
		<td $ar $client_dblclick>Client</td>
		<td $col4>" . input_select('client2_id', $clients, $job['CLIENT2_ID'], "$onchange_sel_m style=\"width:350px;\"") . "</td>
		<td $col2>" . input_button("Go to client (in new tab)", "goto_client('{$job['CLIENT2_ID']}','{$job['C_CODE']}');",
			$manager_x ? '' : 'disabled') . "
			" . input_hidden('c_closeout', $job['C_CLOSEOUT']) . "
			</td>
		$gap
		<td $col2 $ar $grey>JOB ID: $job_id" . ($job['IMPORTED'] ? "&nbsp;&nbsp;&nbsp;(Imported)" : '') . "</td>
	</tr>
	<tr>
		";
	#if ($trace_job)
	#{
	$j_opened_dt = ($trace_job ? date_for_sql($job['J_OPENED_DT'], true, true, true, false, false, false, false, true)
		: date_for_sql($job['J_OPENED_DT'], true, false, true));
	print "
			<td $ar>Received</td>
			<td>" . input_textbox('j_opened_dt', $j_opened_dt, $szsmall2, 20, $onchange_dt_m) . "</td>
			$gap
			";
	#}
	#else
	#	print "
	#			$gap";
	print "
		<td $ar>Client Ref</td>
		<td $col3>" . input_textbox('client_ref', $job['CLIENT_REF'], $szmid, 100, $onchange_txt_m) . "</td>
		$gap
		<td $ar>" . input_tickbox('', 'j_s_invs', 1, $job['J_S_INVS'], $onchange_tck_jsi, $extra_tck_jsi) . "</td>
		<td>Statement Invoicing</td>
		";
//		<td $ar>...Referrer</td>
//		<td>" . input_textbox('j_referrer', $job['J_REFERRER'], $szmid, 1000, $onchange_txt) . "</td>
	print "
	</tr>
	<tr>
		<td $ar>Agent</td>
		<td>" . input_select('j_user_id', $agents, $job['J_USER_ID'], $onchange_sel_jusr_m) . "</td>
		$gap
		<td $ar>...since</td>
		<td>" . input_textbox('j_user_dt', date_for_sql($job['J_USER_DT'], true, false, true), $szmid, 10, $onchange_dt_m) . "</td>
		$gap
		$gap
		$gap
		";
	if ($trace_job)
	{
		print "
			<td $ar>" . input_tickbox('', 'j_available', 1, $job['J_AVAILABLE'], $onchange_tck_ja, $extra_tck_ja) . "</td>
			<td>Available to Agents</td>
			";
	}
	else
		print "<td $col2>" . input_hidden('j_available', $job['J_AVAILABLE']) . "</td>";
	print "
		$gap
		<td $ar $ab>" . str_replace(' ', '&nbsp;', 'Job last updated') . "</td>
		<td $ab>" . input_textbox('j_updated_dt', $job['J_UPDATED_DT'] ?
			date_for_sql($job['J_UPDATED_DT'], true, true, true, false, false, false, false, true)
			: 'n/a', $szsmall2, 10, 'readonly') . "</td>
	</tr>
	<tr>
		<td $ar $at>Job is </td>
		<td>" . input_textbox('job_closed_txt', $job_is, $szsmall2, 0, 'readonly') . input_hidden('job_closed', $job['JOB_CLOSED']) .  "</td>
		";
	if ((!$editing) || $job['JOB_CLOSED']) #(0 < $job['J_CLOSED_ID']))
		print "
			$gap
			<td $ar $at>Closed&nbsp;by</td>
			<td $at>" . input_select('j_closed_id', $agents_all, $job['J_CLOSED_ID'], 'disabled') . "</td>
			<td $ar $at>...on</td>
			<td $at>" . input_textbox('j_closed_dt', date_for_sql($job['J_CLOSED_DT'], true, true, true, false, false, false, false, true),
				$szsmall2, 10, 'readonly') . "</td>
			";
	elseif ($manager_x)
		print "
			<td $col2 $at>" . ($trace_job ? '' : input_button('Close Job', 'close_job()')) . "</td>
			<td $col3 $at></td>
			";
	print "
		$gap
			";
	if ($trace_job)
	{
		print "
			<td $ar $at>Target End</td>
			<td $at>" . input_textbox('j_target_dt', date_for_sql($job['J_TARGET_DT'], true, true, true, false, false, false, false, true),
				$szsmall2, 20, $onchange_dt_m) .
			(($editing && (!$eon)) ? calendar_icon('j_target_dt') : '') . "</td>
			$gap
			";
		if ($editing && (!$eon))
			$calendar_names[] = "j_target_dt";
	}
	else
		print "<td $col2></td>";
	print "
	</tr>
	";
	if (!$trace_job)
	{
		print "
		<tr>
			<td $ar>Status</td>
			<td>" . input_select('jc_job_status_id', $job_statuses_sel, $job['COLLECT_DETAILS']['JC_JOB_STATUS_ID'], $onchange_sel_m) . "</td>
		</tr>
		";
	}
	print "
	<tr>
		<td></td>
		<td $col3>
		";
//		if ($editing && (!$eon))
//		{
//			if ($job['J_ARCHIVED'] && $manager_x)
//				print input_button('Un-archive this job', 'unarchive_job()');
//			elseif ($job['JOB_CLOSED'])
//				print input_button('Reopen', 'reopen_job()', "style=\"width:88px\"") . " " .
//						($manager_x ? input_button('Archive', 'archive_job()', "style=\"width:88px\"") : '');
//		}
	if ($editing && $manager_x)
	{
		if ($job['J_ARCHIVED'])
			print input_button('Un-archive this job', 'unarchive_job()');
		else
		{
			if ($job['JOB_CLOSED'])
				print input_button('Reopen', 'reopen_job()', "style=\"width:88px\"") . " " .
					input_button('Archive', 'archive_job()', "style=\"width:88px\"");
			print " " . ($trace_job ? input_button('Clone Job', 'clone_job()', "style=\"width:88px\"") : '');
		}
	}
	print "
		</td>
	</tr>
	<tr>
		<td $col11>&nbsp;</td>
		";
	if ($trace_job)
	{
//			print "
//			<td $ar>Turn-around in hours</td>
//			<td>" . input_textbox('j_turn_h', $job['TRACE_DETAILS']['J_TURN_H'], 2, 4, $onchange_num . $style_r) . "</td>
//			";
	}
	print "
	</tr>
	";

	if ($trace_job)
	{
		print "
		<tr>
			<td colspan=\"$numcols\"><hr style=\"border:1px solid $grey_colour;\"></td>
		</tr>
		<tr>
			<td $at></td>
			<td colspan=\"" . ($numcols-1) . "\">
				";
		print_one_job_trace($job, $editing && (!$eon), $open, $editing);
		print "
			</td>
		</tr>
		";
	}

	print "
	<tr>
		<td colspan=\"$numcols\"><hr style=\"border:1px solid $grey_colour;\"></td>
	</tr>
	";

	$subject_count = 0;
	foreach ($job['SUBJECTS'] as $one_sub)
	{
		if ($one_sub['OBSOLETE'] == 0)
			$subject_count++;
	}
	if (1 < $subject_count)
		$subject_heading = "Subjects ($subject_count)";
	else
		$subject_heading = "Subject";

	if (0 < $added_subject_id)
		$aid = "id=\"js_{$added_subject_id}\"";
	elseif (0 < $added_phone_id)
		$aid = "id=\"jp_{$added_phone_id}\"";
	elseif (0 < $added_email_id)
		$aid = "id=\"jp_{$added_email_id}\"";
	else
		$aid = '';
	if ($aid)
		print "
		<tr><td $aid></td></tr>
		";
	$aid = '';

	print "
	<tr>
		<td $at $ar rowspan=\"2\">$subject_heading" .
		(($editing && $open && (!$eon) && $manager_x) ? ("<br>" . input_button('Add' . chr(13)	 . 'New', 'add_subject()')) : '') . "</td>
		<td $at $col6 rowspan=\"2\">
			<div id=\"div_subjects\" style=\"height:{$div_h_x2}px; overflow-y:scroll; border:solid gray 1px;\">
			<table class=\"basic_table\" id=\"table_subjects\" width=\"100%\" border=\"0\"><!---->
			";
	foreach ($job['SUBJECTS'] as $sub)
	{
		$editing2 = $editing && $open && (!$eon);
		$tcol = '';
		if ($sub['OBSOLETE'])
		{
			if (!($editing && $open && (!$eon)))
				continue;
			$editing2 = false;
			$tcol = "style=\"color:red;\"";
		}

		$id = $sub['JOB_SUBJECT_ID'];
		#$aid = '';
		#if ($id == $added_subject_id)
		#	$aid = "id=\"js_{$added_subject_id}\"";

		$onchange_sub_txt = ($editing2 ? "onchange=\"update_subject(this,$id);\"" : 'readonly');
		$onchange_sub_txt_m = (($manager_x && $editing2) ? "onchange=\"update_subject(this,$id);\"" : 'readonly');
		$onchange_sub_txt_adr = ((($manager_x || $agent_c) && $editing2) ? "onchange=\"update_subject(this,$id);\"" : 'readonly');
		$onkey_subject = (($manager_x && $editing2) ? "onkeyup=\"subject_lookup($id);\"" : '');
		#$onchange_sub_num = ($editing2 ? "onchange=\"update_subject(this,$id,'n');\"" : 'readonly');
		#$onchange_sub_sel = ($editing2 ? "onchange=\"update_subject(this,$id,'n');\"" : 'disabled');
		$onchange_sub_dt = ($editing2 ? "onchange=\"update_subject(this,$id,'d');\"" : 'readonly');
		#$onchange_sub_tck = ($editing2 ? "update_subject(this,$id,'t')" : '');
		#$extra_sub_tck = ($editing2 ? '' : 'disabled');
		$onchange_sub_tck_m = (($manager_x && $editing2) ? "update_subject(this,$id,'t')" : '');
		$extra_sub_tck_m = (($manager_x && $editing2) ? '' : 'disabled');
		$onchange_sub_obs = (($editing && $open && (!$eon)) ? "update_subject(this,$id,'t')" : '');
		$extra_obs = (($editing && $open && (!$eon)) ? '' : 'disabled');
		print "
				<tr><td $tcol $aid>Title</td>	<td>" . input_textbox('js_title', $sub['JS_TITLE'], $szsmall, 10, $onchange_sub_txt_m) . "</td>
					<td $tcol $ar>" . input_tickbox('Primary', 'js_primary', 1, $sub['JS_PRIMARY'], $onchange_sub_tck_m, $extra_sub_tck_m) . "</td>
					" . ($user_debug ? "<td $grey>&nbsp;&nbsp;DB ID: $id</td>" : '') . "
					</tr>
				<tr><td $tcol>First name</td>	<td $col2>" . input_textbox('js_firstname', $sub['JS_FIRSTNAME'], $szlong, 100, "$onchange_sub_txt_m $onkey_subject", $id) . "</td></tr>
				<tr><td $tcol $at>Last name</td>
					<td $col2 $at>" . input_textbox('js_lastname', $sub['JS_LASTNAME'], $szlong, 50, "$onchange_sub_txt_m $onkey_subject", $id) . "</td>
					<td><span id=\"matches\" style=\"color:blue;\"></span></td>
					</tr>
				<tr><td $tcol>Company</td>		<td $col2>" . input_textbox('js_company', $sub['JS_COMPANY'], $szlong, 100, $onchange_sub_txt_m) . "</td></tr>
				";
		if ($trace_job)
			print "
						<tr><td $tcol $gry $col3>Address supplied by client:</td></tr>
						";
		print "
				<tr><td $tcol $gry>Addr 1</td>		<td $col2>" . input_textbox('js_addr_1', $sub['JS_ADDR_1'], $szlong, 100, $onchange_sub_txt_adr) . "</td></tr>
				<tr><td $tcol $gry>Addr 2</td>		<td $col2>" . input_textbox('js_addr_2', $sub['JS_ADDR_2'], $szlong, 100, $onchange_sub_txt_adr) . "</td></tr>
				<tr><td $tcol $gry>Addr 3</td>		<td $col2>" . input_textbox('js_addr_3', $sub['JS_ADDR_3'], $szlong, 100, $onchange_sub_txt_adr) . "</td></tr>
				<tr><td $tcol $gry>Addr 4</td>		<td $col2>" . input_textbox('js_addr_4', $sub['JS_ADDR_4'], $szlong, 100, $onchange_sub_txt_adr) . "</td></tr>
				<tr><td $tcol $gry>Addr 5</td>		<td $col2>" . input_textbox('js_addr_5', $sub['JS_ADDR_5'], $szlong, 100, $onchange_sub_txt_adr) . "</td></tr>
				<tr>
					<td $tcol $gry>Postcode</td>
					<td>" . input_textbox('js_addr_pc', $sub['JS_ADDR_PC'] . (global_debug() ? "({$sub['JS_OUTCODE']})" : ''), $szsmall, 10, $onchange_sub_txt_adr) . "&nbsp;&nbsp;
						" . input_button('Look-up', "postcode_lookup('js_addr_pc','{$sub['JS_ADDR_PC']}')") . "</td>
					";
		if ((!$trace_job) && $editing2)
			print "
						<td $ar>" . input_button('Add New Address', "new_address($id)") . "</td>
						";
		print "
				</tr>
				";
		if ($trace_job)
		{
			print "
					<tr><td $col3 $tcol $pink>New Address:</td></tr>
					<tr><td $tcol $pink>Addr 1</td>		<td $col2>" . input_textbox('new_addr_1', $sub['NEW_ADDR_1'], $szlong, 100, $onchange_sub_txt) . "</td></tr>
					<tr><td $tcol $pink>Addr 2</td>		<td $col2>" . input_textbox('new_addr_2', $sub['NEW_ADDR_2'], $szlong, 100, $onchange_sub_txt) . "</td></tr>
					<tr><td $tcol $pink>Addr 3</td>		<td $col2>" . input_textbox('new_addr_3', $sub['NEW_ADDR_3'], $szlong, 100, $onchange_sub_txt) . "</td></tr>
					<tr><td $tcol $pink>Addr 4</td>		<td $col2>" . input_textbox('new_addr_4', $sub['NEW_ADDR_4'], $szlong, 100, $onchange_sub_txt) . "</td></tr>
					<tr><td $tcol $pink>Addr 5</td>		<td $col2>" . input_textbox('new_addr_5', $sub['NEW_ADDR_5'], $szlong, 100, $onchange_sub_txt) . "</td></tr>
					<tr>
						<td $tcol $pink>Postcode</td>
						<td>" . input_textbox('new_addr_pc', $sub['NEW_ADDR_PC'] . (global_debug() ? "({$sub['NEW_OUTCODE']})" : ''), $szsmall, 10, $onchange_sub_txt) . "&nbsp;
							" . input_button('Look-up', "postcode_lookup('new_addr_pc','{$sub['NEW_ADDR_PC']}')") . "</td>
					</tr>
					";
		}
		print "
				<tr><td $tcol>D.O.B.</td>		<td>" . input_textbox('js_dob', date_for_sql($sub['JS_DOB'], true, false), $szsmall, 0, $onchange_sub_dt) . "</td></tr>
				";
		if ($editing && (!$eon) && $sub['OBSOLETE'])
			print "
					<tr><td>Obsolete</td>	<td>" . input_tickbox('', 'obsolete', 1, $sub['OBSOLETE'], $onchange_sub_obs, $extra_obs) . "</td></tr>
					";
		if ((!$trace_job) && $sub['ADDRESS_HISTORY'])
		{
			$tcol = "style=\"color:grey;\"";
			foreach ($sub['ADDRESS_HISTORY'] as $hist)
			{
				$ad_from = ($hist['AD_FROM_DT'] ? date_for_sql($hist['AD_FROM_DT'], true, false) : '');
				if ($ad_from == '01/01/1900')
					$ad_from = '';
				$ad_to = date_for_sql($hist['AD_TO_DT'], true, false);
				$date_range = ($ad_from ? "between $ad_from and $ad_to" : "up to $ad_to");
				print "
						<tr><td $col3>Old Address ($date_range):</td></tr>
						<tr><td $tcol>Addr 1</td> <td $col2>" . input_textbox('addr_1', $hist['ADDR_1'], $szlong, 100, "$tcol readonly") . "</td></tr>
						<tr><td $tcol>Addr 2</td> <td $col2>" . input_textbox('addr_2', $hist['ADDR_2'], $szlong, 100, "$tcol readonly") . "</td></tr>
						<tr><td $tcol>Addr 3</td> <td $col2>" . input_textbox('addr_3', $hist['ADDR_3'], $szlong, 100, "$tcol readonly") . "</td></tr>
						<tr><td $tcol>Addr 4</td> <td $col2>" . input_textbox('addr_4', $hist['ADDR_4'], $szlong, 100, "$tcol readonly") . "</td></tr>
						<tr><td $tcol>Addr 5</td> <td $col2>" . input_textbox('addr_5', $hist['ADDR_5'], $szlong, 100, "$tcol readonly") . "</td></tr>
						<tr>
							<td $tcol>Postcode</td>
							<td>" . input_textbox('addr_pc', $hist['ADDR_PC'], $szsmall, 10, "$tcol readonly") . "&nbsp;
								" . input_button('Look-up', "postcode_lookup('','{$hist['ADDR_PC']}')") . "</td>
						</tr>
						";
			}
		}
		if (!$trace_job)
		{
			print "
					<tr>
						<td>Bank name</td> <td $col2>" . input_textbox('js_bank_name', $sub['JS_BANK_NAME'], $szlong, 100, "$tcol $onchange_sub_txt") . "</td>
					</tr>
					<tr>
						<td>Acc. name</td> <td $col2>" . input_textbox('js_bank_acc_name', $sub['JS_BANK_ACC_NAME'], $szlong, 100, "$tcol $onchange_sub_txt") . "</td>
					</tr>
					<tr>
						<td>Sort code</td> <td $col2>" . input_textbox('js_bank_sortcode', $sub['JS_BANK_SORTCODE'], $szlong, 100, "$tcol $onchange_sub_txt") . "</td>
					</tr>
					<tr>
						<td>Account No.</td> <td $col2>" . input_textbox('js_bank_acc_num', $sub['JS_BANK_ACC_NUM'], $szlong, 100, "$tcol $onchange_sub_txt") . "</td>
					</tr>
					<tr>
						<td>Country<br>(if not UK)</td>
							<td $col2 $at>" . input_textbox('js_bank_country', $sub['JS_BANK_COUNTRY'], $szlong, 100, "$tcol $onchange_sub_txt") . "</td>
					</tr>
					";
		}
		print "
				<tr><td $col4><hr></td></tr>
				";
	} # foreach subject
	print "
			</table><!--table_subjects-->
		</div><!--div_subjects-->
		</td>
		$gap
		<td $at $ar>Phone(s)" . (($editing && $open && (!$eon)) ? ("<br>" . input_button('Add' . chr(13)	 . 'New', 'add_phone()')) : '') . "</td>
		<td $at $col5\">
			<div id=\"div_phones\" style=\"height:{$div_h}px; overflow-y:scroll; border:solid gray 1px;\">
			<table class=\"basic_table\" id=\"table_phones\" width=\"100%\" border=\"0\"><!---->
			<tr><td $ac>Phone</td><td $ac>Primary</td><td $ac>Obsolete</td><td $ac $grey>Imported</td>" .
		($user_debug ? "<td $ac $grey>From<br>PHONES.DBF</td><td $ac $grey>DB ID</td>" : '') . "
			";
	foreach ($job['PHONES'] as $phone)
	{
		$editing2 = $editing && $open && (!$eon);
		$tcol = '';
		if ($phone['OBSOLETE'])
		{
			if (!($editing && $open && (!$eon)))
				continue;
			$editing2 = false;
			$tcol = "style=\"color:red;\"";
		}

		$id = $phone['JOB_PHONE_ID'];
		#$aid = '';
		#if ($id == $added_phone_id)
		#	$aid = "id=\"jp_{$added_phone_id}\"";

		$onchange_ph_txt = ($editing2 ? "onchange=\"update_phone(this,$id);\"" : 'readonly');
		#$onchange_ph_num = ($editing2 ? "onchange=\"update_phone(this,$id,'n');\"" : 'readonly');
		#$onchange_ph_sel = ($editing2 ? "onchange=\"update_phone(this,$id,'n');\"" : 'disabled');
		#$onchange_ph_dt = ($editing2 ? "onchange=\"update_phone(this,$id,'d');\"" : 'readonly');
		$onchange_ph_tck = ($editing2 ? "update_phone(this,$id,'t')" : '');
		$extra_ph_tck = ($editing2 ? '' : 'disabled');
		$onchange_ph_obs = (($editing && $open && (!$eon)) ? "update_phone(this,$id,'t')" : '');
		$extra_obs = (($editing && $open && (!$eon)) ? '' : 'disabled');

		$p_imported = (($phone['IMPORTED'] == 1) ? 'Yes' : 'No');
		$imp_ph = (($phone['IMP_PH'] == 1) ? 'Yes' : 'No');

		print "
				<tr>
					<td $aid>" . input_textbox('jp_phone', $phone['JP_PHONE'], $szmid, 0, "$onchange_ph_txt $tcol") . "</td>
					<td $ac>" . input_tickbox('Pri.', 'jp_primary_p', 1, $phone['JP_PRIMARY_P'], $onchange_ph_tck, $extra_ph_tck, false) . "</td>
					<td $ac>" . input_tickbox('Obs.', 'obsolete', 1, $phone['OBSOLETE'], $onchange_ph_obs, $extra_obs, false) . "</td>
					<td $grey $ac>$p_imported</td>
					" . ($user_debug ? "
						<td $grey $ac>$imp_ph</td>
						<td $grey $ar>$id</td>
						" : '') . "
				</tr>
				<tr>
					<td $col6>Description: " . input_textbox('jp_descr', $phone['JP_DESCR'], $szxlong, 0, "$onchange_ph_txt $tcol") . "</td>
				</tr>
				<tr>
					<td $col6><hr></td>
				</tr>
				<tr>
				</tr>
				";
	}
	print "
			</table><!--table_phones-->
		</div><!--div_phones-->
		<br>
		</td>
	</tr>
	<tr>
		<!-- The first two td's in row above this one have rowspan=2 -->
		$gap
		<td $at $ar>Email(s)" . (($editing && $open && (!$eon)) ? ("<br>" . input_button('Add' . chr(13)	 . 'New', 'add_email()')) : '') . "</td>
		<td $at $col5\">
			<div id=\"div_emails\" style=\"height:{$div_h}px; overflow-y:scroll; border:solid gray 1px;\">
			<table class=\"basic_table\" id=\"table_emails\" width=\"100%\" border=\"0\"><!---->
			<tr><td $ac>Email</td><td $ac>Primary</td><td $ac>Obsolete</td><td $ac $grey>Imported</td>" .
		($user_debug ? "<td $ac $grey>From<br>PHONES.DBF</td><td $ac $grey>DB ID</td>" : '') . "
			";
	foreach ($job['EMAILS'] as $email)
	{
		$editing2 = $editing && $open && (!$eon);
		$tcol = '';
		if ($email['OBSOLETE'])
		{
			if (!($editing && $open && (!$eon)))
				continue;
			$editing2 = false;
			$tcol = "style=\"color:red;\"";
		}

		$id = $email['JOB_PHONE_ID'];
		#$aid = '';
		#if ($id == $added_email_id)
		#	$aid = "id=\"jp_{$added_email_id}\"";

		$onchange_ph_txt = ($editing2 ? "onchange=\"update_phone(this,$id);\"" : 'readonly');
		#$onchange_ph_num = ($editing2 ? "onchange=\"update_phone(this,$id,'n');\"" : 'readonly');
		#$onchange_ph_sel = ($editing2 ? "onchange=\"update_phone(this,$id,'n');\"" : 'disabled');
		#$onchange_ph_dt = ($editing2 ? "onchange=\"update_phone(this,$id,'d');\"" : 'readonly');
		$onchange_ph_tck = ($editing2 ? "update_phone(this,$id,'t')" : '');
		$extra_ph_tck = ($editing2 ? '' : 'disabled');
		$onchange_ph_obs = (($editing && $open && (!$eon)) ? "update_phone(this,$id,'t')" : '');
		$extra_obs = (($editing && $open && (!$eon)) ? '' : 'disabled');

		$p_imported = (($email['IMPORTED'] == 1) ? 'Yes' : 'No');
		$imp_ph = (($email['IMP_PH'] == 1) ? 'Yes' : 'No');

		print "
				<tr>
					<td $aid>" . input_textbox('jp_email', $email['JP_EMAIL'], $szmid, 0, "$onchange_ph_txt $tcol") . "</td>
					<td $ac>" . input_tickbox('Pri.', 'jp_primary_e', 1, $email['JP_PRIMARY_E'], $onchange_ph_tck, $extra_ph_tck, false) . "</td>
					<td $ac>" . input_tickbox('Obs.', 'obsolete', 1, $email['OBSOLETE'], $onchange_ph_obs, $extra_obs, false) . "</td>
					<td $grey $ac>$p_imported</td>
					" . ($user_debug ? "
						<td $grey $ac>$imp_ph</td>
						<td $grey $ar>$id</td>
						" : '') . "
				</tr>
				<tr>
					<td $col6>Description: " . input_textbox('jp_descr', $email['JP_DESCR'], $szxlong, 0, "$onchange_ph_txt $tcol") . "</td>
				</tr>
				<tr>
					<td $col6><hr></td>
				</tr>
				<tr>
				</tr>
				";
	}
	print "
			</table><!--table_emails-->
		</div><!--div_emails-->
		</td>
	</tr>
	$jump_to_top
	<tr>
		<td id=\"jn_{$added_note_id}\"></td>
	</tr>
	$blank_line
	<tr id=\"section_notes\">
		<td $at $ar>Notes" . (($editing && ($open || $eon)) ? ("<br>" . input_button('Add' . chr(13)	. 'New', 'add_note(\'NEW NOTE\')')) : '') . "</td>
		<td $at colspan=\"" . ($numcols-1) . "\">
			<div id=\"div_notes\" style=\"height:{$div_h_x2}px; overflow-y:scroll; border:solid gray 1px;\">
			<table class=\"basic_table\" id=\"table_notes\" border=\"0\"><!---->
			<tr>
				<td $ac>Note</td>$gap<td $ac>Added</td>$gap<td $ac>Last Updated</td>
				" . ($user_debug ? "$gap<td $ac $grey>DB ID</td>" : '') . "
			</tr>
			";
	foreach ($job['NOTES'] as $note)
	{
		$id = $note['JOB_NOTE_ID'];
		$aid = '';
		#if ($id == $added_note_id)
		#	$aid = "id=\"jn_{$added_note_id}\"";
		$added_dt = str_replace(' ', '<br>', date_for_sql($note['JN_ADDED_DT'], true, true, true, false, false, false, false, true));
		$updated_dt = str_replace(' ', '<br>', date_for_sql($note['JN_UPDATED_DT'], true, true, true, false, false, false, false, true));
		$onchange_note_txt = (($editing && ($open || $eon) && (!$note['IMPORTED'])) ? "onchange=\"update_note(this,$id);\"" : 'readonly');

		$ta_rows_note = 5;
		if ($note['IMPORTED'])
		{
			if (10 < strlen($note['J_NOTE']))
				$ta_rows_note = 15;
		}
		print "
				<tr>
					<td $aid>" . input_textarea('j_note', $ta_rows_note, $ta_cols, $note['J_NOTE'], $onchange_note_txt) . "</td>
					$gap
					<td $ac $at><br>" . ($note['IMPORTED'] ? 'Imported<br>from old system' : $note['U_ADDED']) . "<br>$added_dt</td>
					$gap
					<td $ac $at><br>{$note['U_UPDATED']}<br>$updated_dt</td>
					" . ($user_debug ? "$gap<td $ac $at $grey><br>$id</td>" : '') . "
				</tr>
				";
	}
	print "
			</table><!--table_notes-->
		</div><!--div_notes-->
		</td>
	</tr>
	$jump_to_top
	<tr id=\"section_letter\">
		<td id=\"letter_top\"></td>
	</tr>
	$blank_line
	";
	if ($trace_job)
	{
		# Report and Letter
		print_one_job_trace_letter($job, $editing && (!$eon), $open, $numcols, $div_h_x2);
		if ($manager_t || $manager_a)
		{
			print "<tr id=\"section_billing\"></tr>";
			if ($added_billing_id == -1)
				print "<tr><td><span id=\"j_bi\"></span></td></tr>
					";
			print "
			<tr>
				<td $at>Billing";
			if ($editing && (!$eon))
			{
				if (0 < count($job['BILLING']))
					print "<br>" . input_button('Refresh', 'billing_refresh()');
				if ($editing && $open && (!$eon))
					print "<br>" . input_button('Add new', 'billing_add()');
				if ($editing && $open && (!$eon) && (0 < count($job['BILLING'])))# && (!$job['J_S_INVS']))# && $job['JOB_CLOSED'])
				{
					if ($job['BILLING_COST_TOTAL'] < 0.0)
					{
						$inv_type_text_1 = 'Credit';
						$inv_type_text_2 = 'a credit';
					}
					else
					{
						$inv_type_text_1 = 'Invoice';
						$inv_type_text_2 = 'an invoice';
					}
					print "<br><br>" . input_button(str_replace(' ', $crlf, "Create $inv_type_text_1"),
							"add_trace_invoice({$job['J_S_INVS']},'$inv_type_text_2')");
				}
			}
			print "
				</td>
				<td $at colspan=\"" . ($numcols-1) . "\">
					";
			print_one_job_trace_bill($job, $editing && (!$eon), $open);
			print "
				</td>
			</tr>
			";
		}
	}
	else
	{
		# Letter(s)
		print_one_job_collect_letter($job, $editing && (!$eon), $open, $numcols, $div_h_x2);
	}
	print $jump_to_top;

	if (!$trace_job)
	{
		$aid = (($added_payment_id == -1) ? "<span id=\"j_pf\"></span>" : '');
		print "
		<tr id=\"section_arrangements\"><td>$aid</td></tr>
		$blank_line
		<tr>
			<td $at $ar>Collection<br>Details</td>
			<td colspan=\"" . ($numcols-1) . "\">
				";
		print_one_job_collect($job, $editing && (!$eon), $open);
		print "
			</td>
		</tr>
		$jump_to_top
		<tr>
			<td colspan=\"$numcols\"><hr style=\"border:1px solid $grey_colour;\"></td>
		</tr>
		<tr id=\"section_payments\">
			<td $at $ac>Payments<br><span style=\"color:blue;\">(adjustments<br>in blue)</span>" .
			((($manager_c || $manager_a) && $editing && $open && (!$eon))
				? ("<br>" . input_button('Refresh', 'payments_refresh()') . "<br>" .
					input_button('Add new', 'payments_add()', $manager_x ? '' : 'disabled'))
				: '') . "
				</td>
			<td colspan=\"" . ($numcols-1) . "\">
				";
		print_one_job_collect_pay($job, $editing && (!$eon), $open);
		print "
			</td>
		</tr>
		$jump_to_top
		<tr>
			<td colspan=\"$numcols\"><hr style=\"border:1px solid $grey_colour;\"></td>
		</tr>
		<tr>
			<td $at $ac>Schedule<br>calculated<br>from<br>arrange-<br>ment(s)<br><span style=\"color:blue\">(future<br>payments<br>in blue)</span></td>
			<td colspan=\"" . ($numcols-1) . "\">
				";
		print_one_job_collect_schedule($job);
		print "
			</td>
		</tr>
		$jump_to_top
		<tr>
			<td colspan=\"$numcols\"><hr style=\"border:1px solid $grey_colour;\"></td>
		</tr>
		<tr>
			<td $at $ac>Combined<br>Schedule<br>and<br>Payments</td>
			<td colspan=\"" . ($numcols-1) . "\">
				";
		print_one_job_collect_schpay($job); #($editing && $open) ? true : false);
		print "
			</td>
		</tr>
		$jump_to_top
		";
		$aid = (($added_activity_id == -1) ? "<span id=\"j_act\"></span>" : '');
		print "
		<tr id=\"section_activity\">
			<td colspan=\"$numcols\">$aid<hr style=\"border:1px solid $grey_colour;\"></td>
		</tr>
		<tr>
			<td $at>Activity" .
			(($editing && $open && (!$eon)) ? ("<br>" . input_button('Refresh', 'activity_refresh()') .
				"<br>" . input_textbox('act_new_count', 1, 2, 3, $style_r) .
				"<br>" . input_button('Add new', 'activity_add()'))
				: '') . "</td>
			<td colspan=\"" . ($numcols-1) . "\">
				";
		print_one_job_collect_act($job, $editing && (!$eon), $open);
		print "
			</td>
		</tr>
		$jump_to_top
		<tr>
			<td colspan=\"$numcols\"><hr style=\"border:1px solid $grey_colour;\"></td>
		</tr>
		<tr>
			<td $at>Misc.</td>
			<td colspan=\"" . ($numcols-1) . "\">
				<table name=\"table_misc\" class=\"basic_table\">
				<tr>
					<td $ar>TDX Account ID</td>
					<td>" . input_textbox('jc_trans_id', $job['COLLECT_DETAILS']['JC_TRANS_ID'], $szlong, 100, $onchange_txt) . "</td>
				</tr>
				<tr>
					<td $ar>TDX Assignment ID</td>
					<td>" . input_textbox('jc_trans_cnum', $job['COLLECT_DETAILS']['JC_TRANS_CNUM'], $szlong, 100, $onchange_txt) . "</td>
				</tr>

				</table>
			</td>
		</tr>
		";
		if ($job['COLLECT_1_10'])
		{
			# This isn't fully imported, so no point displaying it!
//			print "
//			<tr>
//				<td colspan=\"$numcols\"><hr style=\"border:1px solid $grey_colour;\"></td>
//			</tr>
//			<tr>
//				<td $at>Collection Notes<br>1 to 10</td>
//				<td colspan=\"" . ($numcols-1) . "\">
//					";
//					print_collect_1_10($job);
//					print "
//				</td>
//			</tr>
//			";
		}
	}
	print "
	<tr>
		<td colspan=\"$numcols\"><hr style=\"border:1px solid $grey_colour;\"></td>
	</tr>
	<tr>
		";
	if ($manager_x)
		print "
			<td $col2>" . input_button('Show Audit', "show_audit({$job['JOB_ID']})") . "</td>
			";
	if ($manager_x && $editing && $open && (!$eon))
		print "
			<td></td>
			<td $col2>" . input_button('Delete Job', "delete_job({$job['JOB_ID']})") . "</td>";
	print "
	</tr>
	<tr>
		<td colspan=\"$numcols\"><hr style=\"border:1px solid $grey_colour;\"></td>
	</tr>
	$jump_to_top
	</table><!--table_main-->
	</div><!--div_main-->
	";

	if (user_debug() && $job['IMPORTED'])
	{
		print "
		<p><b>This job was imported from the Old Database.</b>
			<br>Other data imported from the old database &ndash; this can be ignored:</p>
		<table class=\"basic_table\" border=\"1\">
			<tr><th>Field</th><th>Value</th></tr>
			";
		foreach ($job['JOB_Z'] as $zf => $zv)
			# Don't display first four characters "Z_" of field name (e.g. "Z_T_" or "Z_X_")
			print "<tr><td>" . substr($zf, 4) . "</td><td>$zv</td></tr>
						";
		print "
		$jump_to_top
		</table>
		";
	}
	if ($time_tests) log_write ("print_one_job($job_id) - done printing table");

	print "
	<form name=\"form_client\" action=\"clients.php\" method=\"post\" target=\"_blank\" rel=\"noopener\">
		" . input_hidden('client2_id', '') . "
		" . input_hidden('sc_text', '') . "
		" . input_hidden('task', '') . "
	</form><!--form_client-->

	<form name=\"form_invoices\" action=\"ledger.php\" method=\"post\" target=\"_blank\" rel=\"noopener\">
		" . input_hidden('sc_code', '') . "
		" . input_hidden('task', '') . "
		" . input_hidden('invoice_id', '') . "
		" . input_hidden('doctype', '') . "
	</form><!--form_invoices-->

	<form name=\"form_audit\" action=\"audit.php\" method=\"post\" target=\"_blank\" rel=\"noopener\">
		" . input_hidden('job_id', '') . "
	</form><!--form_audit-->

	<form name=\"form_job\" action=\"jobs.php\" method=\"post\" target=\"_blank\" rel=\"noopener\">
		" . input_hidden('task', '') . "
		" . input_hidden('job_id', '') . "
		" . input_hidden('sc_text', '') . "
	</form><!--form_job-->
	";

	if ($span_warn)
	{
		print "
		<script type=\"text/javascript\">
		document.getElementById('span_warn').innerHTML = '$span_warn';
		</script>
		";
	}
	if ($time_tests) log_write("jobs.php/print_one_job(): Exit");
} # print_one_job()

function print_one_job_trace($job, $editing, $open, $trying_to_edit)
{
	# Called from print_one_job().
	# This function's output should be a <table>.

#	global $ab;
	global $ar;
#	global $at;
	global $col2;
	#global $col3;
	global $col4;
	global $col7;
	#global $extra_tck;
	global $extra_tck_m;
	global $gap;
	global $id_JOB_TYPE_tc; # JOB_TYPE_SD.JOB_TYPE_ID for "T/C"
	global $id_JOB_TYPE_tm; # JOB_TYPE_SD.JOB_TYPE_ID for "T/M"
	global $job_types_sel; # init_data()
	global $manager_t;
	global $onchange_dt;
	global $onchange_dt_m;
	global $onchange_mon;
	#global $onchange_num;
	#global $onchange_num_m;
	global $onchange_sel;
	global $onchange_sel_m;
	global $onchange_sel_suc;
	#global $onchange_tck;
	global $onchange_tck_m;
	global $onchange_txt_m;
	global $style_r;
	#global $szlong;
	global $szmid;
	global $szsmall;
	global $szsmall2;
	global $ta_cols;
	global $ynfoc_list;
	global $ynfrns_list;
	global $ynpend_list;

	$details = $job['TRACE_DETAILS'];
	$jt_back_dt = date_for_sql($details['JT_BACK_DT'], true, false);
	$job_review = ($job['TRACE_DETAILS']['J_COMPLETE'] == -1) ? true : false;
	$targets = sql_get_client_targets($job['CLIENT2_ID'], $job['TRACE_DETAILS']['JT_JOB_TYPE_ID']);

	if (!$job['TRACE_DETAILS']['JT_FEE_Y'])
		print "<h2 style=\"color:red\">Warning: the \"Fee on Success\" is zero</h2>";

	print "
	<table id=\"table_trace\" class=\"basic_table\" border=\"0\"><!---->
	<tr>
		<td $ar>Job Type</td>
		<td>" . input_select('jt_job_type_id', $job_types_sel, $job['TRACE_DETAILS']['JT_JOB_TYPE_ID'], $onchange_sel_m) . "</td>
		$gap
		<td $ar>Target Time</td>
		<td>" . input_select('jt_job_target_id', $targets, $job['TRACE_DETAILS']['JT_JOB_TARGET_ID'], $onchange_sel_m) . "</td>
		$gap
		";
	if ($manager_t)
	{
		print "
			<td $ar>Fee on success</td>
			<td>" . input_textbox('jt_fee_y', money_format_kdb($details['JT_FEE_Y'], true, true, true), 4, 10, "$onchange_mon $style_r") . "</td>
			$gap
			<td $ar>Fee otherwise</td>
			<td>" . input_textbox('jt_fee_n', money_format_kdb($details['JT_FEE_N'], true, true, true), 4, 10, "$onchange_mon $style_r") . "</td>
			";
	}
	print "
	</tr>
	<tr>
		<td $ar>Complete</td>
		<td>" . input_select('j_complete', $ynpend_list, $job['TRACE_DETAILS']['J_COMPLETE'], $onchange_sel_m, true) . "</td>
		$gap
		<td $ar>Success</td>
		<td>" . input_select('jt_success', $ynfrns_list, $details['JT_SUCCESS'], $onchange_sel_suc, true) . "</td>
		$gap
		<td $ar>Credit</td><td>" . input_select('jt_credit', $ynfoc_list, $details['JT_CREDIT'], $onchange_sel, true) . "</td>
		$gap
		<td $ar>Date Back</td>
		<td>" . input_textbox('jt_back_dt', $jt_back_dt, $szsmall2, 10, "$onchange_dt_m $style_r") . "</td>
	</tr>
	";
	if ($details['JT_JOB_TYPE_ID'] == $id_JOB_TYPE_tm)
	{
		print "
		<tr>
			<td $ar>Trace complete</td>
			<td>" . input_tickbox('', 'jt_tm_t_comp', 1, $details['JT_TM_T_COMP'], $onchange_tck_m, $extra_tck_m) . "</td>
			$gap
			<td $ar>Means complete</td>
			<td>" . input_tickbox('', 'jt_tm_m_comp', 1, $details['JT_TM_M_COMP'], $onchange_tck_m, $extra_tck_m) . "</td>
			$gap
			<td $ar>Trace-only Fee</td>
			<td>" . input_textbox('jt_tm_t_fee', money_format_kdb($details['JT_TM_T_FEE'], true, true, true), 4, 10, "$onchange_mon $style_r") . "</td>
		</tr>
		";
	}
	elseif ($details['JT_JOB_TYPE_ID'] == $id_JOB_TYPE_tc)
	{
		print "
		<tr>
			<td $ar>Reason<br>for Debt</td>
			<td $col7>" . input_textarea('jc_reason_2', 1, $ta_cols, $details['JC_REASON_2'], $onchange_txt_m) . "</td>
			$gap
			<td $ar>Amount<br>Outstanding</td>
			<td>" . input_textbox('jt_amount', money_format_kdb($details['JT_AMOUNT'], true, true, true), $szsmall, 10, "$onchange_mon $style_r") . "</td>
			$gap
			<td $ar>Diary Date</td>
			<td>" . input_textbox('j_diary_dt', date_for_sql($details['J_DIARY_DT'], true, false), $szsmall, 10, "$style_r $onchange_dt") . "</td>
		</tr>
		";
	}
	if ($details['JT_PROPERTY_TXT'])
		print "
		<tr>
			<td $ar>Property Type</td>
			<td $col4>" . input_textbox('jt_property_txt', $details['JT_PROPERTY_TXT'], $szmid, 100, "readonly") . "
		</tr>
		";

	#dprint("editing=$editing, open=$open, manager_t=$manager_t, job_type={$details['JT_JOB_TYPE_ID']}, id_JOB_TYPE_tc=$id_JOB_TYPE_tc");#

	if ($editing && $open)
	{
		print "
		<tr>
			<td $col2>" . input_button('Submit for Approval', $job_review ? '' : "approval_submit()", $job_review ? 'disabled' : '',
				'approval_button') . "</td>
			";
		if ($manager_t)
			print "
				$gap
				<td $col2>" . input_button('Return to Agent', "approval_reject()") . "</td>
				";
		print "
			";
		if ($manager_t)
		{
			if ($editing && ($details['JT_JOB_TYPE_ID'] == $id_JOB_TYPE_tc))
			{
				print "
					$gap
					<td>" . input_button('Create Collection Job', 'create_collect_job()') . "</td>
					";
			}
		}
		print "
		</tr>
		";
	}
	elseif ($trying_to_edit && $manager_t && ($details['JT_JOB_TYPE_ID'] == $id_JOB_TYPE_tc))
		print "
		<tr>
			<td $col2>" . input_button('Create Collection Job', 'create_collect_job()') . "</td>
		</tr>
		";

	print "
	</table><!--table_trace-->
	";

} # print_one_job_trace()

function print_one_job_collect($job, $editing, $open)
{
	# Called from print_one_job().
	# This function's output should be a <table>.

	global $ar;
	global $at;
	global $blank_line;
	global $col2;
	global $col3;
	global $col7;
	global $col8;
	global $col11;
	global $extra_tck;
	global $gap;
	global $manager_x;
	global $onchange_dt;
	global $onchange_mon;
	#global $onchange_num;
	global $onchange_pc;
	global $onchange_sel;
	global $onchange_sel_txt;
	global $onchange_tck;
	global $onchange_txt;
	global $onchange_txt_m;
	global $PAYMENT_METHODS; # init_data()
	global $span_warn;
	global $style_r;
	#global $szlong;
	#global $szmid;
	global $szsmall;
	global $ta_cols;

	$details = $job['COLLECT_DETAILS'];
	$outstanding = floatval($details['JC_TOTAL_AMT']) - floatval($details['JC_PAID_SO_FAR']) - floatval($details['JC_ADJUSTMENT']);

	if ($details['JC_PAID_SO_FAR'] == $details['SUM_PAYMENTS'])
	{
		$sum_payments = '';
		$sp_outst = '';
		$psf_edit = 'readonly';
	}
	else
	{
		$sp_style = "color:red; font-size:12;";
		$sum_payments = money_format_kdb($details['SUM_PAYMENTS'], true, true, true);
		$sum_payments = "<span style=\"$sp_style\"><br>BUT: Sum of<br>Payments = $sum_payments</span>";
		$temp = floatval($details['JC_TOTAL_AMT']) - floatval($details['SUM_PAYMENTS']);
		$sp_outst = "<span style=\"$sp_style\"><br>= &pound;$temp based on<br>Sum of Payments</span>";
		$psf_edit = $onchange_mon;
	}
	if ($details['JC_MIN_SETT'] === '')
		$jc_min_sett = '';
	else
		$jc_min_sett = floatval($details['JC_MIN_SETT']) . '%';

	$warn_percent = '';
	$percent = floatval($details['JC_PERCENT']);
	if (!$percent)
	{
		$span_warn = 'WARNING: COMMISSION IS ZERO';
		$warn_percent = "<h3 style=\"color:red;\">$span_warn</h3>";
	}
	$percent = "{$percent}%";

	print "
	$warn_percent
	<table id=\"table_collect\" class=\"basic_table\" border=\"0\"><!---->
	<tr>
		<td $col2>&nbsp;&nbsp;" . input_tickbox('Is a T/C Job', 'jc_tc_job', 1, $details['JC_TC_JOB'], $onchange_tck, $extra_tck) . "</td>
		<td $ar>Commission</td>
		<td>" . input_textbox('jc_percent', $percent, $szsmall, 10, "$style_r $onchange_pc") . "</td>
		$gap
		<td $ar>Min. Settlement</td>
		<td>" . input_textbox('jc_min_sett', $jc_min_sett, $szsmall, 10, "$style_r $onchange_pc") . "</td>
		$gap
		<td $ar>Diary Date</td>
		<td>" . input_textbox('j_diary_dt', date_for_sql($details['J_DIARY_DT'], true, false), $szsmall, 10, "$style_r $onchange_dt") . "</td>
		$gap
		<td $ar>Diary Notes</td>
		<td>" . input_textarea('j_diary_txt', 1, 30, $details['J_DIARY_TXT'], "$onchange_txt style=\"height:25px;\"") . "</td>
	</tr>
	<tr>
		<td $col3 $ar $at>Total Amount to be Paid</td>
		<td $at>" . input_textbox('jc_total_amt', money_format_kdb($details['JC_TOTAL_AMT'], true, true, true), $szsmall, 10,
			"$style_r " . ($manager_x ? $onchange_mon : 'readonly')) . "</td>
		$gap
		<td $ar $at>Paid so far</td>
		<td $at>" . input_textbox('jc_paid_so_far', money_format_kdb($details['JC_PAID_SO_FAR'], true, true, true), $szsmall, 0, "$style_r $psf_edit") . "
			{$sum_payments}</td>
		$gap
		<td $ar $at>Outstanding</td>
		<td $at>" . input_textbox('outstanding', money_format_kdb($outstanding, true, true, true), $szsmall, 0, "$style_r readonly") . "
			{$sp_outst}</td>
		$gap
		<td $ar $at>Adjustments</td>
		<td $at>" . input_textbox('jc_adjustment', money_format_kdb($details['JC_ADJUSTMENT'], true, true, true), $szsmall, 0, "$style_r readonly") . "</td>
	</tr>
	<tr>
		<td $col8></td>
		<td $ar>Arrears</td>
		<td>" . input_textbox('auto_arrears', '', $szsmall, 0, "style=\"text-align:right; color:red;\" readonly") . "</td>
	</tr>
	<tr>
		<td $col3>Collection Arrangement:</td>
		";
	if (($editing && $open))
		print "
			<td $col8>&nbsp;</td>
			<td $col2 $ar>" . input_button('Create New Arrangement', 'add_arrange()') . "</td>
			";
	print "
	</tr>
	<tr>
		<td $col3 $ar>Instalments</td>
		<td>" . input_textbox('jc_instal_amt', money_format_kdb($details['JC_INSTAL_AMT'], true, true, true), $szsmall, 10, "$style_r $onchange_mon") . "</td>
		$gap
		<td $ar>Starting</td>
		<td>" . input_textbox('jc_instal_dt_1', date_for_sql($details['JC_INSTAL_DT_1'], true, false), $szsmall, 10, "$style_r $onchange_dt") . "</td>
		$gap
		<td $ar>Inst. Frequency</td>
		<td>" . input_select('jc_instal_freq', instal_freq_list(), $details['JC_INSTAL_FREQ'], $onchange_sel_txt) . "</td>
		$gap
		<td $ar>Method</td>
		<td>" . input_select('jc_payment_method_id', $PAYMENT_METHODS, $details['JC_PAYMENT_METHOD_ID'], $onchange_sel) . "</td>
	</tr>
	<tr>
		<td $col3 $ar>Next Review Date</td>
		<td>" . input_textbox('jc_review_dt', date_for_sql($details['JC_REVIEW_DT'], true, false), $szsmall, 10, "$style_r $onchange_dt") . "</td>
	</tr>
	";

	# Previous Arrangements:
	$prev_count = count($job['PREV_ARRANGE']);
	print "
	$blank_line
	<tr>
		<td $col3>Previous Arrangement" . (($prev_count == 1) ? '' : 's') . ":</td>
	</tr>
	";
	if (0 < $prev_count)
	{
		print "
			<tr>
				<td $col2>&nbsp;</td>
				<td $col11><hr></td>
			</tr>
			";
		$pvi = 0;
		foreach ($job['PREV_ARRANGE'] as $prevar)
		{
			print "
			<tr>
				<td $col3 $ar>Instalments</td>
				<td>" . input_textbox('ja_instal_amt', money_format_kdb($prevar['JA_INSTAL_AMT'], true, true, true), $szsmall, 10, "$style_r readonly") . "</td>
				$gap
				<td $ar>Starting</td>
				<td>" . input_textbox('ja_instal_dt_1', date_for_sql($prevar['JA_INSTAL_DT_1'], true, false), $szsmall, 10, "$style_r readonly") . "</td>
				$gap
				<td $ar>Inst. Frequency</td>
				<td>" . input_select('ja_instal_freq', instal_freq_list(), $prevar['JA_INSTAL_FREQ'], 'disabled') . "</td>
				$gap
				<td $ar>Method</td>
				<td>" . input_select('ja_payment_method_id', $PAYMENT_METHODS, $prevar['JA_PAYMENT_METHOD_ID'], 'disabled') . "</td>
			</tr>
			<tr>
				<td $col3 $ar>Total Due</td>
				<td>" . input_textbox('ja_total', money_format_kdb($prevar['JA_TOTAL'], true, true, true), $szsmall, 10, "$style_r readonly") . "</td>
				$gap
				<td $ar>Deposit</td>
				<td>" . input_textbox('z_depamount', $prevar['Z_DEPAMOUNT'] ? money_format_kdb($prevar['Z_DEPAMOUNT'], true, true, true) : '',
					$szsmall, 10, "$style_r readonly") . "</td>
				$gap
				<td $ar>Dep.Date</td>
				<td>" . input_textbox('z_depdate', date_for_sql($prevar['Z_DEPDATE'], true, false), $szsmall, 10, "$style_r readonly") . "</td>
			</tr>
			";
			$pvi++;
			if ($pvi < $prev_count)
				print "
				<tr>
					<td $col2>&nbsp;</td>
					<td $col11><hr></td>
				</tr>
				";
		}
	}
	else
		print "
			<tr>
				<td $col2>&nbsp;</td>
				<td>None.</td>
			</tr>
			";

	print "
	$blank_line
	<tr>
		<td $ar $at>Reason for debt</td>
		<td $col7>" . input_textarea('jc_reason_2', 4, $ta_cols, $details['JC_REASON_2'], $onchange_txt_m) . "</td>
	</tr>
	";
	print "
	</table><!--table_collect-->
	";

//	print "
//	<table>
//	";
//
//	print "
//	<tr>
//		<td $col2><br><b>Payments:</b></td>
//	</tr>
//	";
//	if (count($job['PAYMENTS']) > 0)
//	{
//		print "
//		<tr>
//			<td>&nbsp;</td>
//			<td>
//			";
//			print_one_job_collect_pay($job);
//			print "
//			</td>
//		</tr>
//		";
//	}
//	else
//		print "
//			<tr>
//				<td $col2>-none</td>
//			</tr>
//			";
//
//	print "
//	<tr>
//		<td $col2><br><b>Activity (TDX):</b></td>
//	</tr>
//	";
//	if (count($job['ACTIVITY']) > 0)
//	{
//		print "
//		<tr>
//			<td>&nbsp;</td>
//			<td>
//			";
//			print_one_job_collect_act($job);
//			print "
//			</td>
//		</tr>
//		";
//	}
//	else
//		print "
//			<tr>
//				<td $col2>-none</td>
//			</tr>
//			";
//	print "
//	</table>
//	";

} # print_one_job_collect()

function print_one_job_collect_pay($job, $editing, $open)
{
	global $ac;
	global $at;
	#global $added_payment_id; # from screen_content()
	global $ADJUSTMENTS; # init_data()
	global $ar;
	global $col6;
	global $div_h_max;
	global $grey;
	global $id_ROUTE_cspent;
	global $manager_a;
	global $payment_methods_sel; # init_data()
	global $payment_routes_sel; # init_data()
	global $style_r;
	global $ta_cols;
	global $yn_list;

	#$aid = (($added_payment_id == -1) ? "id=\"j_pf\"" : '');
	print "
	&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span id=\"pay_summary\"></span>
	";
	#<span $aid>&nbsp;</span>

	$div_h = 50 + (30 * count($job['PAYMENTS']));
	if ($div_h_max < $div_h)
		$div_h = $div_h_max;
	$html_table = "
	<div id=\"div_payments\" style=\"height:{$div_h}px; overflow-y:scroll; border:solid gray 1px;\">
	<table name=\"table_payments\" class=\"spaced_table\">
	<tr>
		<!--<th>Pmt/Adj</th>--><th>Route</th><th>Date</th><th>Amount</th><th>Invoice</th><th>Method</th><th>Adjustment Reason</th>
		<th>Commission %</th><th>Commission &pound;</th><th>Bounced</th><th>Imported</th><th>Delete</th><th $grey>DB ID</th>
	</tr>
	";
	$count_pay = 0;
	$count_adj = 0;
	$sum = 0.0;
	$btnw = 75;
	#dprint("payments/2=".print_r($job['PAYMENTS'],1));#
	foreach ($job['PAYMENTS'] as $details)
	{
		$id = $details['JOB_PAYMENT_ID'];
		#if ($id == $added_payment_id)
		#	$aid = "id=\"j_{$added_payment_id}\"";
		#else
		$aid = '';

		$onchange_pmt_txt = (($editing && $open) ? "onchange=\"update_payment(this,$id);\"" : 'readonly');
		#$onchange_pmt_num = (($editing && $open) ? "onchange=\"update_payment(this,$id,'n');\"" : 'readonly');
		$onchange_pmt_mon = (($editing && $open) ? "onchange=\"update_payment(this,$id,'m');\"" : 'readonly');
		$onchange_pmt_sel = (($editing && $open) ? "onchange=\"update_payment(this,$id,'n');\"" : 'disabled');
		$onchange_pmt_dt = (($editing && $open) ? "onchange=\"update_payment(this,$id,'d');\"" : 'disabled');
		$onchange_pmt_pc = (($editing && $open) ? "onchange=\"update_payment(this,$id,'p');\"" : 'disabled');

		$is_adj = (($details['COL_PAYMENT_ROUTE_ID'] == $id_ROUTE_cspent) ? true : false);
		$has_route = ((0 < $details['COL_PAYMENT_ROUTE_ID']) ? true : false);
		#$is_pmt = (((!$is_adj) && $details['COL_AMT_RX']) ? true : false);
		$clr = ($is_adj ? "style=\"color:blue;\"" : '');

		$col_percent = floatval($details['COL_PERCENT']);
		$com_gbp = 0.01 * floatval($details['COL_AMT_RX']) * floatval($details['COL_PERCENT']);
		$col_percent = round($col_percent,2);
		$col_percent = input_textbox('col_percent', "{$col_percent}%", 8, 10, "$style_r $onchange_pmt_pc");

		if (0 < $details['INV_NUM'])
		{
			$ve = ($manager_a ? "'edit'" : "'view'");
			$invoice_button = input_button($details['INV_NUM'], "view_invoice('i', {$details['INVOICE_ID']}, $ve);", "style=\"width:{$btnw}px;\"");
		}
		else#if ($is_adj || $is_pmt)
			#$invoice_button = '(no invoice)';
			$invoice_button = input_button('no invoice', '', " disabled style=\"width:{$btnw}px;\"");
		#else
		#	$invoice_button = '';

		$route = (($editing && $open) ?
			input_select('col_payment_route_id', $payment_routes_sel, $details['COL_PAYMENT_ROUTE_ID'], $onchange_pmt_sel, false, false)
			: $details['PAYMENT_ROUTE']);
		$method = input_select('col_payment_method_id', $payment_methods_sel, $details['COL_PAYMENT_METHOD_ID'], $onchange_pmt_sel, false, false);
		$adjust_reason = input_select('adjustment_id', $ADJUSTMENTS, $details['ADJUSTMENT_ID'], $onchange_pmt_sel, false, false);
		$date = input_textbox('col_dt_rx', date_for_sql($details['COL_DT_RX'], true,false, true), 8, 10, "$style_r $onchange_pmt_dt");
		$amount = input_textbox('col_amt_rx', money_format_kdb($details['COL_AMT_RX'], true, true, true), 8, 10, "$style_r $onchange_pmt_mon");
		$bounced = input_select('col_bounced', $yn_list, $details['COL_BOUNCED'], $onchange_pmt_sel, true);
		$imported = ($details['IMPORTED'] ? "Imp." : "-");
		$delete = (($details['IMPORTED'] || $details['INVOICE_ID']) ? "" : input_button('X', "delete_payment($id)", "style=\"color:red\""));

		$html_table .= "
		<tr $aid $clr>
			<td $ac>$route</td>
			<td>$date</td>
			<td $ar>$amount</td>
			<td $ac>$invoice_button</td>
			<td $ac>$method</td>
			<td $ac>$adjust_reason</td>
			<td $ar>$col_percent</td>
			<td $ar id=\"com_gbp_{$id}\">" . ($com_gbp ? money_format_kdb($com_gbp, true, true, true) : '-') . "</td>
			<td $ac>$bounced</td>
			<td $ac>$imported</td>
			<td $ac>$delete</td>
			<td $ar $grey>$id</td>
		</tr>
		";
		if ($is_adj || (!$has_route))
		{
			$html_table .= "
			<tr $clr>
				<td $ac></td>
				<td></td>
				<td $ar></td>
				<td $ac></td>
				<td $ar $at>Adjustment Notes:</td>
				<td $col6>" . input_textarea('col_notes', 1, $ta_cols, $details['COL_NOTES'], "$onchange_pmt_txt style=\"height:25px\"") . "</td>
			</tr>
			";
		}
		if ($is_adj)
			$count_adj++;
		elseif (!$details['COL_BOUNCED'])
		{
			$count_pay++;
			$sum += floatval($details['COL_AMT_RX']);
		}
	} # foreach()

	$html_table .= "
	</table><!--table_payments-->
	</div><!--div_payments-->
	";

	if (($count_pay + $count_adj) == 0)
	{
		$pay_summary = '<p>There are no payments or adjustments</p>';
		$html_table = '';
	}
	else
		$pay_summary = "Number of payments: $count_pay, totalling " . money_format_kdb($sum, true, true, true) .
			" (not including adjustments or bounced payments)";

	print "
	$html_table
	<script type=\"text/javascript\">
	document.getElementById('pay_summary').innerHTML = '$pay_summary';
	</script>
	";

} # print_one_job_collect_pay()

function print_one_job_collect_schedule($job)
{
	#global $ac;
	global $ar;
	#global $col11;
	global $div_h_max;
	global $mktime_year_limit;
	#global $szsmall;
	#global $tr_colour_2;

	$arrangements = array(); # oldest arrangements come first
	$prev_start_dt = '';
	$jj = 0;
	for ($ii = count($job['PREV_ARRANGE'])-1; 0 <= $ii; $ii--)
	{
		$arrange = $job['PREV_ARRANGE'][$ii]; # the arrangement we are now examining
		if (0.0 < $arrange['JA_INSTAL_AMT'])
		{
			if ($prev_start_dt && ($arrange['JA_INSTAL_DT_1'] == $prev_start_dt))
				$jj--;
			$arrangements[$jj] = array('START_DT' => $arrange['JA_INSTAL_DT_1'], 'AMOUNT' => $arrange['JA_INSTAL_AMT'],
				'FREQ' => $arrange['JA_INSTAL_FREQ']);
			$jj++;
			$prev_start_dt = $arrange['JA_INSTAL_DT_1'];
		}
	}
	if (0.0 < $job['COLLECT_DETAILS']['JC_INSTAL_AMT'])
	{
		if ($prev_start_dt && ($job['COLLECT_DETAILS']['JC_INSTAL_DT_1'] == $prev_start_dt))
			$jj--;
		$arrangements[$jj] = array('START_DT' => $job['COLLECT_DETAILS']['JC_INSTAL_DT_1'], 'AMOUNT' => $job['COLLECT_DETAILS']['JC_INSTAL_AMT'],
			'FREQ' => $job['COLLECT_DETAILS']['JC_INSTAL_FREQ']);
	}

	$count_arr = count($arrangements);
	if ($count_arr == 0)
	{
		print "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;There is no schedule because there is no arrangement";
		return;
	}
	#dprint("arrangements:" . print_r($arrangements,1));#

	$schedule = array();
	$pmt_ix = count($job['PAYMENTS']) - 1; # index into $job['PAYMENTS']
	$total_due = 0.0;
	$total_paid = 0.0;
	$end_ep = date_to_epoch("{$mktime_year_limit}-01-01"); # 01/01/2037
	#dprint("end_ep=$end_ep");#

	for ($ii = 0; $ii < $count_arr; $ii++)
	{
		$arrange = $arrangements[$ii]; # the arrangement we are now examining
		$due_amt = $arrange['AMOUNT'];
		$next_arr = ((($ii+1) < $count_arr) ? $arrangements[$ii+1] : ''); # the next (later) arrangement
		$start_dt = $arrange['START_DT'];
		$start_ep = date_to_epoch($start_dt);
		if ($next_arr)
		{
			$next_start_dt = $next_arr['START_DT'];
			$next_start_ep = ($next_start_dt ? date_to_epoch($next_start_dt) : 0);
		}
		else
		{
			$next_start_dt = '';
			$next_start_ep = $end_ep;
		}

		$period = 0; # period between payments, in days, unless monthly or quarterly
		$day = 0; # day of the month, if monthly or quarterly
		if ($arrange['FREQ'] == 'I')
			$period = 1;
		elseif ($arrange['FREQ'] == 'D')
			$period = 1;
		elseif ($arrange['FREQ'] == 'W')
			$period = 7;
		elseif ($arrange['FREQ'] == 'F')
			$period = 14;
		elseif ($arrange['FREQ'] == 'M')
			$day = substr($start_dt, 8, 2);
		elseif ($arrange['FREQ'] == 'Q')
			$day = substr($start_dt, 8, 2);
		else
			$period = 1;
		$period *= (24 * 60 * 60); # convert days to seconds

		$months = 0;
		for ($ep = $start_ep; $ep < $next_start_ep; )
		{
			$paid = 0.0;
			for ( ; 0 <= $pmt_ix; $pmt_ix--)
			{
				$payment = $job['PAYMENTS'][$pmt_ix];
				if ($payment['COL_EP_RX'] <= $ep)
					$paid += floatval($payment['COL_AMT_RX']);
				else
					break; # leave $pmt_ix ready for next line of $schedule
			}

			$last_line = false;
			if (($job['COLLECT_DETAILS']['JC_TOTAL_AMT'] - $total_due) <= $due_amt)
			{
				$due_amt = $job['COLLECT_DETAILS']['JC_TOTAL_AMT'] - $total_due;
				$last_line = true;
			}
			$total_due += $due_amt;
			$total_paid += $paid;
			if (($job['COLLECT_DETAILS']['JC_TOTAL_AMT'] <= $total_paid) && ($job['COLLECT_DETAILS']['JC_PAID_SO_FAR'] <= $total_paid ))
				$last_line = true;
			$schedule[] = array('DUE_EP' => $ep, 'DUE_AMT' => $due_amt, 'PAID' => $paid, 'TOTAL_DUE' => $total_due, 'TOTAL_PAID' => $total_paid);
			if ($last_line)
				break;

			# Prepare for next iteration of loop:
			if ($day)
			{
				if ($arrange['FREQ'] == 'M')
					$months++;
				else
					$months += 3;
				$ep = date_add_months_kdb($start_ep, $months);
			}
			else
				$ep += $period;
		} # for ($ep)
	} # for ($ii thru arrangements)

	$gap = "<th>&nbsp;&nbsp;</th>";
	$headings = "
	<tr>
		<th>Due Date</th>$gap<th>Due Amount</th>$gap<th>Paid Amount</th>$gap<th>Total Due</th>$gap<th>Total Paid</th>$gap<th>Arrears</th>
	</tr>
	";
	$div_h = 30 + (25 * count($schedule));
	if ($div_h_max < $div_h)
		$div_h = $div_h_max;
	$html_table = "
	<div id=\"div_schedule\" style=\"height:{$div_h}px; overflow-y:scroll; border:solid gray 1px;\">
		" . count($schedule) . " scheduled payments
	<table name=\"table_schedule\" class=\"spaced_table\">
	$headings
	";
//	$html_table = "
//	<tr>
//		<td $col5>" . print_r($schedule,1) . "</td>
//	</tr>
//	";

	#$headings = str_replace('<th>', "<td $ac style=\"background-color:{$tr_colour_2}\"><b>", str_replace('</th>', '</b></td>', $headings));
	$gap = "<td></td>";

	$time = time();
	$tix = 0; # index into table rows
	$prev_colour = '';
	$final_arrears = 0.0;
	for ($ii = count($schedule)-1; 0 <= $ii; $ii--)
	{
		$future = (($time < $schedule[$ii]['DUE_EP']) ? true : false);
		if ($future)
		{
			$paid = '';
			$total_paid = '';
			$arrears = 0.0;
			$arrears_txt = '';
		}
		else
		{
			if ($schedule[$ii]['PAID'] == 0.0)
				$paid = '-';
			else
				$paid = money_format_kdb($schedule[$ii]['PAID'], true, true, true);
			$total_paid = money_format_kdb($schedule[$ii]['TOTAL_PAID'], true, true, true);
			$arrears = $schedule[$ii]['TOTAL_DUE'] - $schedule[$ii]['TOTAL_PAID'];
			if (0.0 < $arrears)
				$arrears_txt = "<span style=\"color:red;\">" . money_format_kdb($arrears, true, true, true) . "</span>";
			else
				$arrears_txt = '';
		}
		$colour = ($future ? "style=\"color:blue\"" : '');
		if ($tix == 0)
			$final_arrears = $arrears; # default - needed if there are no future payments
		elseif ((0 < $tix) && ($colour != $prev_colour))
		{
			# We have changed from future payments to current/past payments
			$final_arrears = $arrears;
			#$html_table .= "<tr id=\"today_line_s\"><td $col11><hr style=\"color:grey;\"></td></tr>
			#				$headings";
			$html_table .= str_replace("<tr", "<tr id=\"today_line_s\"", $headings);

			$tix++;
		}
		$html_table .= "
		<tr $colour>
			<td $ar>" . date_from_epoch(true, $schedule[$ii]['DUE_EP'], true) . "</td>$gap
			<td $ar>" . money_format_kdb($schedule[$ii]['DUE_AMT'], true, true, true) . "</td>$gap
			<td $ar>$paid</td>$gap
			<td $ar>" . money_format_kdb($schedule[$ii]['TOTAL_DUE'], true, true, true) . "</td>$gap
			<td $ar>$total_paid</td>$gap
			<td $ar>$arrears_txt</td>
		</tr>
		";
		$prev_colour = $colour;
		$tix++;
		if (($tix % 20) == 0)
			$html_table .= $headings;
	}
	if (0 < $final_arrears)
		$final_arrears = money_format_kdb($final_arrears, true, false, true);
	else
		$final_arrears = '';

	$html_table .= "
	</table><!--table_schedule-->
	</div><!--div_schedule-->

	<script type=\"text/javascript\">
	var span_a = document.getElementById('auto_arrears');
	if (span_a)
		span_a.value = '$final_arrears';
	else
		alert('Cannot find element auto_arrears');
	</script>
	";

	print "
	$html_table
	";

} # print_one_job_collect_schedule()

function print_one_job_collect_act($job, $editing, $open)
{
	global $ac;
	global $activities_sel; # lib_vilcol.php / init_data()
	global $activities_sel_nm; # lib_vilcol.php / init_data()
	#global $added_activity_id; # from screen_content()
	global $ar;
	global $div_h_max;
	global $grey;
	global $manager_x;
	global $style_r;

	$feedback_38 = global_debug();

	#$aid = (($added_activity_id == -1) ? "id=\"j_act\"" : '');
	#print "
	#<span $aid>&nbsp;</span>
	#";

	$div_h = 30 + (40 * count($job['ACTIVITY']));
	if ($div_h_max < $div_h)
		$div_h = $div_h_max;
	$html_table = "
	<div id=\"div_activity\" style=\"height:{$div_h}px; overflow-y:scroll; border:solid gray 1px;\">
	<table name=\"table_activity\" class=\"spaced_table\">
	<tr>
		<th>Code</th><th>Description</th><th>Date</th><th>Time</th><th>Notes</th><th>Imported</th><th $grey>DB ID</th>
		<td width=\"30\">&nbsp;</td>
		<th>Amount</th><th>Method</th><th>Route</th><th $grey>DB ID</th>
	</tr>
	";

	$count = 0;
	foreach ($job['ACTIVITY'] as $details)
	{
		$editing2 = $editing;
		$all_acts = true;
		if ($feedback_38){
			if (!$manager_x)
			{
				if ((!$details['ACTIVITY_ID']) || array_key_exists($details['ACTIVITY_ID'], $activities_sel_nm))
					$all_acts = false;
				else
					$editing2 = false;
			}
		}

		$id = $details['JOB_ACT_ID'];
		$onchange_act_sel = (($editing2 && $open) ? "onchange=\"update_act(this,$id,'n');\"" : 'disabled');
		$onchange_act_dt = (($editing && $open) ? "onchange=\"update_act(this,$id,'d');\"" : 'readonly');
		$onchange_act_ti = (($editing && $open) ? "onchange=\"update_act(this,$id,'i');\"" : 'readonly');
		$onchange_act_txt = (($editing && $open) ? "onchange=\"update_act(this,$id);\"" : 'readonly');

		$payment = array('amount' => '', 'date' => '', 'method' => '', 'route' => '', 'id' => '');
		$link = '';
		if ($details['ACT_TDX'] == 'PAR')
		{
			# Find linked payment record
			$act_dt = substr($details['JA_DT'], 0, strlen('yyyy-mm-dd'));
			foreach ($job['PAYMENTS'] as $paymt)
			{
				$pay_dt = substr($paymt['COL_DT_RX'], 0, strlen('yyyy-mm-dd'));
				if ($pay_dt == $act_dt)
				{
					#$link = '.....';
					$payment['amount'] = money_format_kdb($paymt['COL_AMT_RX'], true, true, true);
					#$payment['date'] = date_for_sql($paymt['COL_DT_RX'], true,false, true);
					$payment['method'] = $paymt['PAYMENT_METHOD'];
					$payment['route'] = $paymt['PAYMENT_ROUTE'];
					$payment['id'] = $paymt['JOB_PAYMENT_ID'];
					break;
				}
			}
		}

		$code = input_select('activity_id', $all_acts ? $activities_sel : $activities_sel_nm, $details['ACTIVITY_ID'], $onchange_act_sel);

		$bits = explode(' ', date_for_sql($details['JA_DT'], true, true, true, false, false, false, false, true));
		$date = input_textbox('ja_dt_d', $bits[0], 7, 10, "$style_r $onchange_act_dt");
		$time = input_textbox('ja_dt_t', $bits[1], 3, 10, "$style_r $onchange_act_ti");
		$note = input_textarea('ja_note', 1, 20, $details['JA_NOTE'], "style=\"height:23px;\" $onchange_act_txt");

		if ($details['IMPORTED'])
			$imported = "Yes";
		else
			$imported = "No";
		$html_table .= "
		<tr>
			<td>$code</td>
			<td>{$details['ACT_DSHORT']}</td>
			<td>$date</td>
			<td>$time</td>
			<td>$note</td>
			<td $ac>$imported</td>
			<td $ar $grey>$id</td>
			<td $ac>$link</td>
			<td $ar>{$payment['amount']}</td>
			" . #<td>{$payment['date']}</td>
			"
			<td $ac>{$payment['method']}</td>
			<td $ac>{$payment['route']}</td>
			<td $ar $grey>{$payment['id']}</td>
		</tr>
		";
		$count++;
	}
	$html_table .= "
	</table><!--table_activity-->
	</div><!--div_activity-->
	";
	if ($count == 0)
		print "<p>There is no Activity</p>";
	else
		print $html_table;

} # print_one_job_collect_act()

function print_one_job_trace_bill($job, $editing, $open)
{
	global $ac;
	#global $added_billing_id; # from screen_content()
	global $ar;
	global $grey;
	global $manager_a;
	global $manager_t;
	global $style_r;

	if ((!$manager_t) && (!$manager_a))
		return;

	#dprint(print_r($job['BILLING'],1) . '<br>');

//	$aid = (($added_billing_id == -1) ? "id=\"j_bi\"" : '');
//	print "
//	<span $aid>&nbsp;</span>
//	";

	$approved_invoice_count = 0;
	if (0 < count($job['BILLING']))
	{
		print "
		<table name=\"table_billing\" class=\"spaced_table\">
		<tr>
			<th>Description</th><th>Cost</th><th>Letter Date</th><th>Invoice</th><th>Approved</th>
			<th>Imported</th><th $grey>Sys</th>" . ($editing ? "<th></th>" : '') . "<th $grey>DB ID</th>
		</tr>
		";
		$btnw = 75;
		foreach ($job['BILLING'] as $details)
		{
			$id = $details['INV_BILLING_ID'];

			$onchange_bill_txt = (($editing && $open) ? "onchange=\"update_billing(this,$id);\"" : 'readonly');
			$onchange_bill_mon = (($editing && $open) ? "onchange=\"update_billing(this,$id,'m');\"" : 'readonly');

			$descr = input_textarea('bl_descr', 3, 40, $details['BL_DESCR'], $onchange_bill_txt);
			$cost = input_textbox('bl_cost', money_format_kdb($details['BL_COST'], true, true, true), 4, 10, "$style_r $onchange_bill_mon");
			$date = input_textbox('bl_letter_dt', date_for_sql($details['BL_LETTER_DT'], true,false, true), 8, 10, "$style_r readonly");
			$imported = ($details['IMPORTED'] ? "Imp." : "-");
			$sys_txt = $details['BL_SYS'] . ($details['BL_SYS_IMP'] ? "/{$details['BL_SYS_IMP']}" : '');

			$has_invoice = ((0 < $details['INV_NUM']) ? 1 : 0);
			if ($has_invoice)
			{
				$ve = ($manager_a ? "'edit'" : "'view'");
				$invoice_button = input_button($details['INV_NUM'], "view_invoice('i', {$details['INVOICE_ID']}, $ve);", "style=\"width:{$btnw}px;\"");
				if ($details['INV_APPROVED_DT'])
				{
					$inv_appr = "Approved";
					$approved_invoice_count++;
				}
				else
					$inv_appr = "No";
			}
			else
			{
				$invoice_button = input_button('no invoice', '', " disabled style=\"width:{$btnw}px;\"");
				$inv_appr = '-';
			}

			print "
			<tr>
				<td>$descr</td>
				<td>$cost</td>
				<td>$date</td>
				<td>$invoice_button</td>
				<td $ac>$inv_appr</td>
				<td $ac>$imported</td>
				<td $ac $grey>$sys_txt</td>
				" . (($editing && $open) ? ("<td>" . input_button('Delete Billing', "delete_billing($id,$has_invoice)") . "</td>") : '') . "
				<td $ar $grey>$id</td>
			</tr>
			";
		} # foreach()

		print "
		</table><!--table_billing-->
		";
	}
	else
		print "<p>There is no billing for this job</p>";

	if ($job['J_S_INVS'] == 1)
		$approved_invoice_count = -1;

	print input_hidden('billing_count', count($job['BILLING'])) . input_hidden('approved_invoice_count', $approved_invoice_count);

} # print_one_job_trace_bill()

function assign_jobs_to_agent($assign_agent_id, $assign_job_ids)
{
	global $sqlTrue;

	$this_txt = "assign_jobs_to_agent($assign_agent_id, Array(" . $assign_job_ids[0] . ",...))";
	if (!(0 < $assign_agent_id))
	{
		dprint("$this_txt: bad agent id $assign_agent_id");
		return;
	}
	if (!(0 < count($assign_job_ids)))
	{
		dprint("$this_txt: bad jobs list");
		return;
	}

	#dprint("Assigning the following JOB_IDs to Agent $assign_agent_id: " . print_r($assign_job_ids,1), true);

	$now = date_now_sql();
	$now_sql = "'$now'";
	foreach ($assign_job_ids as $job_id)
	{
		$sql = "UPDATE JOB SET J_AVAILABLE=$sqlTrue WHERE JOB_ID=$job_id";
		audit_setup_job($job_id, 'JOB', 'JOB_ID', $job_id, 'J_AVAILABLE', $sqlTrue);
		sql_execute($sql, true); # audited

		$sql = "UPDATE JOB SET J_USER_ID=$assign_agent_id WHERE JOB_ID=$job_id";
		audit_setup_job($job_id, 'JOB', 'JOB_ID', $job_id, 'J_USER_ID', $assign_agent_id);
		sql_execute($sql, true); # audited

		$sql = "UPDATE JOB SET J_USER_DT=$now_sql WHERE JOB_ID=$job_id";
		audit_setup_job($job_id, 'JOB', 'JOB_ID', $job_id, 'J_USER_DT', $now);
		sql_execute($sql, true); # audited

		#$sql = "UPDATE JOB SET J_AVAILABLE=$sqlFalse WHERE JOB_ID=$job_id";
		#audit_setup_job($job_id, 'JOB', 'JOB_ID', $job_id, 'J_AVAILABLE', $sqlFalse);
		#sql_execute($sql, true); # audited

		sql_update_job($job_id);
	}

	$sql = "SELECT J_VILNO FROM JOB WHERE JOB_ID IN (" . implode(',', $assign_job_ids) . ")";
	sql_execute($sql);
	$vilnos = array();
	while (($newArray = sql_fetch()) != false)
		$vilnos[] = $newArray[0];

	$agent_fullname = user_name_from_id($assign_agent_id, true);

	dprint("The jobs with VILNo's " . implode(', ', $vilnos) . " have been assigned to agent $agent_fullname", true);

} # assign_jobs_to_agent()

function delete_letter()
{
	global $job_id;
	global $sqlTrue;

	$job_letter_id = post_val('letter_id', true);
	if (!(0 < $job_letter_id))
	{
		dprint("delete_letter(): invalid job letter id \"" . post_val('letter_id') . "\"", true);
		return;
	}

	$sql = "UPDATE JOB_LETTER SET OBSOLETE=$sqlTrue WHERE JOB_LETTER_ID=$job_letter_id";
	audit_setup_job($job_id, 'JOB_LETTER', 'JOB_LETTER_ID', $job_letter_id, 'OBSOLETE', $sqlTrue);
	sql_execute($sql, true); # audited

	sql_update_letter($job_id, $job_letter_id);

} # delete_letter()

function approve_letter()
{
	global $job_id;

	$job_letter_id = post_val('letter_id', true);
	if (!(0 < $job_letter_id))
	{
		dprint("approve_letter(): invalid job letter id \"" . post_val('letter_id') . "\"", true);
		return;
	}

	$now = date_now_sql();
	$now_sql = "'$now'";
	$sql = "UPDATE JOB_LETTER SET JL_APPROVED_DT=$now_sql WHERE JOB_LETTER_ID=$job_letter_id";
	audit_setup_job($job_id, 'JOB_LETTER', 'JOB_LETTER_ID', $job_letter_id, 'JL_APPROVED_DT', $now);
	sql_execute($sql, true); # audited

	sql_update_letter($job_id, $job_letter_id);

} # approve_letter()

function request_next_trace_job()
{
	# Find an available trace job and assign it to the agent. Return the JOB_ID, or zero if a job can't be got.
	# See also spec section 15.11.

	global $sqlFalse;
	global $sqlTrue;
	global $USER;

	$debug = false; #
	if ($debug)
		log_write("request_next_trace_job() for user {$USER['USER_ID']}");

	$job_stats = sql_agent_job_stats($USER['USER_ID']);

	if (0 < $job_stats['OPEN_TRACE_COUNT_COMP_NO'])
	{
		$msg = "Sorry, you already have {$job_stats['OPEN_TRACE_COUNT_COMP_NO']} open trace jobs that are older than {$job_stats['OPEN_TRACE_DAYS']} days";
		if ($job_stats['OPEN_TRACE_COUNT_COMP_NO'] == 1)
			$msg = str_replace('jobs that are', 'job that is', $msg);
		if ($debug) dlog($msg); else
			dprint($msg, true);
		return 0;
	}

	if (0 < $job_stats['OPEN_RETRACE_COUNT_COMP_NO'])
	{
		$msg = "Sorry, you already have {$job_stats['OPEN_RETRACE_COUNT_COMP_NO']} open retrace jobs that are older than {$job_stats['OPEN_RETRACE_DAYS']} days";
		if ($job_stats['OPEN_RETRACE_COUNT_COMP_NO'] == 1)
			$msg = str_replace('jobs that are', 'job that is', $msg);
		if ($debug) dlog($msg); else
			dprint($msg, true);
		return 0;
	}

	if ($job_stats['OPEN_T_LIMIT'] <= $job_stats['OPEN_T_COUNT_COMP_NO'])
	{
		$msg = "Sorry, you already have {$job_stats['OPEN_T_COUNT_COMP_NO']} open jobs";
		if ($job_stats['OPEN_T_COUNT_COMP_NO'] == 1)
			$msg = str_replace('open jobs', 'open job', $msg);
		if ($debug) dlog($msg); else
			dprint($msg, true);
		return 0;
	}

	list($ms_top, $my_limit) = sql_top_limit(1);
	$sql = "SELECT $ms_top JOB_ID, J_OPENED_DT
			FROM JOB
			WHERE JT_JOB=$sqlTrue AND JOB_CLOSED=$sqlFalse AND J_AVAILABLE=$sqlTrue AND J_USER_ID IS NULL AND OBSOLETE=$sqlFalse AND J_ARCHIVED=$sqlFalse
			ORDER BY J_OPENED_DT $my_limit
			";
	#dprint($sql);#
	sql_execute($sql);
	$job_id = 0;
	while (($newArray = sql_fetch()) != false)
		$job_id = $newArray[0];

	if ($job_id == 0)
	{
		$msg = "Sorry, no available jobs were found";
		if ($debug) dlog($msg); else
			dprint($msg, true);
		return 0;
	}

	if ($debug)
		log_write("request_next_trace_job(): assigning JOB_ID $job_id to user \"{$USER['USER_ID']}\"");

	# Assign this job to agent
	assign_jobs_to_agent($USER['USER_ID'], array($job_id));

	return $job_id;

} # request_next_trace_job()

function approval_reject()
{
	# A manager has clicked the "Return to Agent" button.
	# Set "Completed" to "No", "Success" to "Not Set" and add a note, returning the note ID

	global $job_id;
	global $sqlFalse;
	global $sqlTrue;
	global $success_return;

	$sql = "SELECT JOB_LETTER_ID FROM JOB_LETTER WHERE JOB_ID=$job_id AND OBSOLETE=$sqlFalse";
	sql_execute($sql);
	$letters = array();
	while (($newArray = sql_fetch()) != false)
		$letters[] = $newArray[0];
	foreach ($letters as $letter_id)
	{
		$sql = "UPDATE JOB_LETTER SET OBSOLETE=$sqlTrue WHERE JOB_LETTER_ID=$letter_id";
		audit_setup_job($job_id, 'JOB', 'JOB_ID', $job_id, 'OBSOLETE', $sqlTrue);
		sql_execute($sql, true); # audited

		sql_update_letter($job_id, $letter_id);
	}

	$sql = "UPDATE JOB SET JT_REPORT_APPR=NULL WHERE JOB_ID=$job_id";
	audit_setup_job($job_id, 'JOB', 'JOB_ID', $job_id, 'JT_REPORT_APPR', 'NULL');
	sql_execute($sql, true); # audited

	$sql = "UPDATE JOB SET J_COMPLETE=$sqlFalse WHERE JOB_ID=$job_id";
	audit_setup_job($job_id, 'JOB', 'JOB_ID', $job_id, 'J_COMPLETE', $sqlFalse);
	sql_execute($sql, true); # audited

	$sql = "UPDATE JOB SET JT_SUCCESS=$success_return WHERE JOB_ID=$job_id";
	audit_setup_job($job_id, 'JOB', 'JOB_ID', $job_id, 'JT_SUCCESS', $success_return);
	sql_execute($sql, true); # audited

	$new_note_id = sql_add_note($job_id, 'Job returned to agent by Manager');
	return $new_note_id;

} # approval_reject()

function print_one_job_trace_letter($job, $editing, $open, $numcols, $div_h_x2)
{
	global $ac;
	global $agent_t;
	global $ar;
	global $at;
	global $col2;
	global $col3;
	#global $col4;
	global $col6;
	global $col7;
	global $crlf;
	global $csv_dir;
	global $gap;
	global $grey;
	global $invoice_trace_ap; # whether the trace job has an invoice that is approved
	global $invoice_trace_ex; # whether the trace job has an invoice that exists
	global $letter_rebuild;
	global $manager_a;
	global $manager_t;
//	global $pdf_message;
	global $resend_207;
	global $ta_cols;
	global $user_debug;

	if ($job['LETTERS_COUNT'] == 0)
	{
		$do_report = true;
		$do_letter = false;
		$letter_id = 0;
		$letter_appr = false;
		$pdf_url_pend = '';
		#$pdf_button = '';
	}
	elseif ($job['LETTERS_PENDING'])
	{
		$do_report = false;
		$do_letter = true;
		$letter_id = $job['LETTERS_PENDING'][0]['JOB_LETTER_ID'];
		if ($job['LETTERS_PENDING'][0]['JL_APPROVED_DT'] == '')
			$letter_appr = false;
		else
			$letter_appr = true;
		$pdf_url_pend = pdf_link('jl', "v{$job['J_VILNO']}", "{$job['J_VILNO']}_{$job['J_SEQUENCE']}_{$letter_id}");
		#if ($pdf_url_pend)
		#	$pdf_button = 'Re-create PDF';
		#else
		#	$pdf_button = 'Create PDF';
	}
	else
	{
		$do_report = false;
		$do_letter = false;
		$letter_id = 0;
		$letter_appr = false;
		$pdf_url_pend = '';
		#$pdf_button = '';
	}
	#dprint("\$do_report=$do_report, \$do_letter=$do_letter, \$letter_appr=$letter_appr, \$do_letter=$do_letter");#

	$sub_name = '';
	$sub_addr = array();
	$new_addr = array();
	if (0 < count($job['SUBJECTS']))
	{
		$sub = $job['SUBJECTS'][0]; # assume first subject is primary one
		$sub_name = '';
		$sub_count = count($job['SUBJECTS']);
		for ($sub_ix = 0; $sub_ix < $sub_count; $sub_ix++)
		{
			$one_subject = $job['SUBJECTS'][$sub_ix];
			$temp_name = trim("{$one_subject['JS_TITLE']} {$one_subject['JS_FIRSTNAME']} {$one_subject['JS_LASTNAME']} {$one_subject['JS_COMPANY']}");
			if ($sub_name == '')
				$sub_name = $temp_name;
			else if ($sub_ix == ($sub_count - 1))
				$sub_name .= " & $temp_name";
			else
				$sub_name .= ", $temp_name";
		}
		$temp = trim((string)$sub['JS_ADDR_1']);
		if ($temp)
			$sub_addr[] = $temp;
		$temp = trim((string)$sub['JS_ADDR_2']);
		if ($temp)
			$sub_addr[] = $temp;
		$temp = trim((string)$sub['JS_ADDR_3']);
		if ($temp)
			$sub_addr[] = $temp;
		$temp = trim((string)$sub['JS_ADDR_4']);
		if ($temp)
			$sub_addr[] = $temp;
		$temp = trim((string)$sub['JS_ADDR_5']);
		if ($temp)
			$sub_addr[] = $temp;
		$temp = trim((string)$sub['JS_ADDR_PC']);
		if ($temp)
			$sub_addr[] = $temp;
		$temp = trim((string)$sub['NEW_ADDR_1']);
		if ($temp)
			$new_addr[] = $temp;
		$temp = trim((string)$sub['NEW_ADDR_2']);
		if ($temp)
			$new_addr[] = $temp;
		$temp = trim((string)$sub['NEW_ADDR_3']);
		if ($temp)
			$new_addr[] = $temp;
		$temp = trim((string)$sub['NEW_ADDR_4']);
		if ($temp)
			$new_addr[] = $temp;
		$temp = trim((string)$sub['NEW_ADDR_5']);
		if ($temp)
			$new_addr[] = $temp;
		$temp = trim((string)$sub['NEW_ADDR_PC']);
		if ($temp)
			$new_addr[] = $temp;
	}
	#dprint("PHONES/1=" . print_r($job['PHONES'],1));#
	if ((!$letter_rebuild) && $do_letter && $job['LETTERS_PENDING'][0]['JL_TEXT'])
		$letter_preview = $job['LETTERS_PENDING'][0]['JL_TEXT'];
	elseif ($do_report || $do_letter)
		$letter_preview = letter_for_trace_job($job['TRACE_DETAILS']['JT_JOB_TYPE_ID'], $job['TRACE_DETAILS']['JT_SUCCESS'],
			$job['J_VILNO'], $job['CLIENT2_ID'], $job['CLIENT_REF'], $job['TRACE_DETAILS']['JT_LET_REPORT'],
			$sub_name, $sub_addr, $new_addr, $job['PHONES']);
	else
		$letter_preview = '';

	$onchange_report_txt = (($editing && $open && $do_report && $agent_t) ? "onchange=\"update_job(this);\"" : 'readonly');
	$onchange_report_tck = (($editing && $open && $manager_t &&
		($do_report || ($do_letter && (!$letter_appr)))) ? "report_appr(this);" : '');
	$extra_report_tck = (($editing && $open && $manager_t &&
		($do_report || ($do_letter && (!$letter_appr)))) ? '' : 'disabled');

	$onchange_letter_txt = (($editing && $open && $do_letter && (!$letter_appr)) ? "onkeydown=\"letter_warn();\"" : 'readonly');
	$onchange_letter_tck = (($manager_t && $editing && $open && $do_letter) ? "letter_approve(this);" : '');
	$extra_letter_tck = (($manager_t && $editing && $open && $do_letter) ? '' : 'disabled');

	$cols_report = floor($numcols / 2);
	$cols_preview = ($numcols-1) - $cols_report;
	$biggap2 = "
				&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
				&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
				";
	$biggap4 = "
				&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
				&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
				&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
				&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
				";
	print "
	<tr>
		<td $at $ar>Letter and<br>Report</td>
		<td $at colspan=\"$cols_report\">
			<div id=\"div_report\" style=\"height:{$div_h_x2}px; overflow-y:scroll; border:solid gray 1px;\">
				&nbsp;Report Body<br>
				&nbsp;" . input_textarea('jt_let_report', 16, $ta_cols, $job['TRACE_DETAILS']['JT_LET_REPORT'], $onchange_report_txt) . "<br>
				&nbsp;" . ($do_report ? input_button('Check Spelling', 'spell_trace_report()', '', 'spellcheck_button') : '') . "&nbsp;" .
		input_button('Save Report', "save_trace_report()", "style=\"display:none;\"", "save_report_button") . "
				$biggap4" . input_tickbox('Report Approved', 'jt_report_appr', 1,
			$job['TRACE_DETAILS']['JT_REPORT_APPR'] ? 1 : 0, $onchange_report_tck, $extra_report_tck) . "
			</div><!--div_report-->
		</td>
		$gap
		<td $at colspan=\"$cols_preview\">
			";
	if (($manager_t || $manager_a) && ($do_report || $do_letter))
	{
		$div_h_ltr = (($editing && $letter_appr) ? 1.7 : 1.0) * $div_h_x2;
		print "
				<div id=\"div_preview\" style=\"height:{$div_h_ltr}px; overflow-y:scroll; border:solid gray 1px;\">
					&nbsp;" . (($do_report || (!$letter_appr)) ?
				(input_button('Letter Preview', 'letter_preview_1()') . " &ndash; what the letter will look like when it is created")
				: '') . "<br>
					&nbsp;" . input_textarea('letter_preview', 15, $ta_cols, $letter_preview, $onchange_letter_txt) . "
								" . input_hidden('letter_preview_id', $letter_id) . "<br>
					<span style=\"color:red\" id=\"letter_warning\"></span>
					&nbsp;";
		if ($do_letter)
		{
			if ($editing && $open && (!$letter_appr))
				print input_button('Check Spelling', 'spell_letter()', '', 'spellcheck_button') . "&nbsp;" .
					input_button('Save Letter', "save_letter_preview('')");
			print $biggap2;
			if ($do_letter)
				print input_tickbox('Letter Approved', 'jl_approved_dt', 1, $job['LETTERS_PENDING'][0]['JL_APPROVED_DT'] ? 1 : 0,
						$onchange_letter_tck, $extra_letter_tck) . "&nbsp;&nbsp;";
			if ($editing && $open && $letter_appr)
			{
				if ($pdf_url_pend)
					print "
									<a href=\"$pdf_url_pend\" target=\"_blank\" rel=\"noopener\"><img src=\"images/pdf.png\" height=\"23\" width=\"23\"></a>&nbsp;&nbsp;&nbsp;";
				#print input_button($pdf_button, "create_pdf('jl',$letter_id)"); # zero for not-yet-created letter ID
			}
			print "&nbsp;&nbsp;&nbsp;" . ($letter_id ? "<span $grey>ID $letter_id</span>" : '');
			if ($editing && $open && $letter_appr)
			{
				$emails = sql_client_emails($job['CLIENT2_ID'], true);
				$def_email = '';
				foreach ($emails as $one_em)
				{
					$def_email = $one_em;
					break; # just take the first one
				}
				print "
								<table name=\"email_table\" class=\"basic_table\" border=\"0\"><!---->
								<tr>
									<td>To:</td>
									<td>" . input_select('email_addr', $emails, $def_email) . "</td>
								</tr>
								<tr>
									<td>Subject:</td>
									<td>" . input_textbox('email_subject', 'Vilcol Trace Report' . ($invoice_trace_ex ? ' and Invoice' : ''), 60, 1000) . "</td>
								</tr>
								<tr>
									<td $at>Message:</td>
									<td>" . input_textarea('email_message', 5, 57,
						"Please find attached our report" . ($invoice_trace_ex ? " and invoice" : "") .
						" in response to your recent Instruction.{$crlf}" .
						$crlf .
						$crlf .
						"Best regards,{$crlf}" .
						$crlf .
						"Vilcol") . "</td>
								</tr>
								<tr>
									<td></td>
									<td>The PDF of the Letter and/or Invoice will be<br>automatically attached to the outgoing email.</td>
								</tr>
								<tr>
									<td $col2>" . input_button('Email Letter', "email_letter('t', $letter_id, " . ($invoice_trace_ex ? '1' : '0') . ", " .
						($invoice_trace_ap ? '1' : '0') . ")");
				if (!$job['J_S_INVS'])
				{
					if (!$invoice_trace_ex)
						print "&nbsp;&nbsp;&nbsp;<span style=\"color:red;\">Warning: there is no invoice yet.</span>";
					elseif (!$invoice_trace_ap)
						print "&nbsp;&nbsp;&nbsp;<span style=\"color:red;\">Warning: the invoice is not APPROVED yet.</span>";
				}
				print "</td>
								</tr>
								<tr>
									<td $col7>" . input_button('Or, Letter has been printed and posted', "post_letter($letter_id)") . "</td>
								</tr>
								</table><!--email_table-->
								";
			}
		}
		else
			print "The Letter can be created (and approved) once the Report is approved.";
		print "
				</div><!--div_preview-->
				";
	}
	print "
		</td>
	</tr>
	";

	if ($do_report)
		$spell_box = 'jt_let_report';
	elseif ($do_letter && $editing && $open && (!$letter_appr))
		$spell_box = 'letter_preview';
	else
		$spell_box = '';
	if ($spell_box)
		print "
		<script>
		var checker = new sc.SpellChecker({
			button: 'spellcheck_button', // HTML element that will open the spell checker when clicked
			textInput: '$spell_box', // HTML field containing the text to spell check
			action: 'spell/spellcheck.php' // URL of the server side script
			});
		</script>
		";

	elseif ($job['LETTERS_SENT'])
	{
		# Letter has been sent

		print "
		<tr>
			<td $at $ar>Sent<br>Letters</td>
			<td $at colspan=\"" . ($numcols-1) . "\">
				<div id=\"div_letters_sent\" style=\"height:{$div_h_x2}px; overflow-y:scroll; border:solid gray 1px;\">
				";
		if ($manager_t || $manager_a)
		{
			print "
					<table class=\"basic_table\" id=\"table_letters_sent\" border=\"0\"><!---->
					<tr>
						<td $ac>The Letter that was Sent</td>$gap<td $ac>Added</td>$gap<td $ac>Last Updated</td>$gap<td $ac>Posted</td>
						" . ($user_debug ? "$gap<td $ac $grey>DB ID</td>" : '') . "<td></td>
					</tr>
					" . input_hidden('sent_letters_count', count($job['LETTERS_SENT'])) . "
					";
			foreach ($job['LETTERS_SENT'] as $letter)
			{
				$letter_id = $letter['JOB_LETTER_ID'];
				$resend_now = (($resend_207 == $letter_id) ? true : false);
				$added_dt = str_replace(' ', '<br>', date_for_sql($letter['JL_ADDED_DT'], true, true, true, false, false, false, false, true));
				if ($letter['EM_DT'])
				{
					$posted_dt = '(emailed)';
					$emailed_dt = date_for_sql($letter['EM_DT'], true, true, true, false, false, false, false, true);
				}
				else
				{
					$posted_pdf = ($letter['JL_POSTED_PDF'] ?
						("<br><br><br>" .
							"<a href=\"{$csv_dir}/{$letter['JL_POSTED_PDF']}\" target=\"_blank\" rel=\"noopener\">" .
							"<img src=\"images/pdf.png\" height=\"23\" width=\"23\"></a>")
						: '');
					$posted_dt = str_replace(' ', '<br>', date_for_sql($letter['JL_POSTED_DT'], true, true, true, false, false, false, false, true)) .
						$posted_pdf;
					$emailed_dt = '';
				}
				if ($letter['JL_UPDATED_DT'])
				{
					$updated_dt = date_for_sql($letter['JL_UPDATED_DT'], true, true, true, false, false, false, false, true);
					$updated_dt = $letter['UPDATED_U'] . '<br>' . str_replace(' ', '<br>', $updated_dt);
				}
				else
					$updated_dt = '';

				$attach = '';
				if ($letter['EM_ATTACH'])
				{
					#dprint($letter['EM_ATTACH']);#
					# Example EM_ATTACH: "v1512257/letter_1512257_90868311_373223_20161123_103127.pdf|c1234/invoice_204145_20161123_102417.pdf"
					# Also: "v1546871/letter_1546871_90902925_1247469_20170103_145712.pdf|c/"
					$afiles = explode('|', $letter['EM_ATTACH']);
					foreach ($afiles as $one_af)
					{
						if (($one_af != '') && ($one_af != "c/"))
						{
							$pdf_url_sent = "{$csv_dir}/{$one_af}";
							$bits = explode('_', $one_af);
							$bits2 = explode('/', $bits[0]); # $bits[0]: subdir, stroke and first word of filename e.g. "v123456/letter"
							$pdf_label = $bits2[count($bits2)-1]; # e.g. "letter"
							$attach .= "&nbsp;&nbsp;$pdf_label:<a href=\"$pdf_url_sent\" target=\"_blank\" rel=\"noopener\">" .
								"<img src=\"images/pdf.png\" height=\"23\" width=\"23\"></a>";
							if ($resend_now)
								$attach .= input_tickbox('', "resend_{$pdf_label}", $one_af, false);
							$attach .= "&nbsp;&nbsp;";
						}
					}
				}

				print "
						<tr>
							<td $at rowspan=\"8\">" . input_textarea('jl_letter_sent', 15, $ta_cols, $letter['JL_TEXT'] . $letter['JL_TEXT_2'], 'readonly') . "</td>
							$gap
							<td $ac $at>" . ($letter['IMPORTED'] ? 'Imported<br>from old system<br>' : '') . "$added_dt</td>
							$gap
							<td $ac $at>$updated_dt</td>
							$gap
							<td $ac $at>$posted_dt</td>
							" . ($user_debug ? "$gap<td $ac $at $grey><br>$letter_id</td>" : '') . "
						</tr>
						";
				if ($letter['EM_DT'])
				{
					$resends = '';
					if ($letter['JL_EMAIL_RESENDS'])
						$resends .= "Simple re-sends:<br>" . str_replace('|', '<br>', str_replace('()','',$letter['JL_EMAIL_RESENDS'])) . "<hr><br>";
					if ($letter['JL_EMAILS_OLD'])
					{
						foreach ($letter['JL_EMAILS_OLD'] as $old_email_id => $old_email_info)
						{
							$resend_attach = '';
							$afiles = explode('|', $old_email_info['EM_ATTACH']);
							foreach ($afiles as $one_af)
							{
								if (($one_af != '') && ($one_af != "c/"))
								{
									$pdf_url_sent = "{$csv_dir}/{$one_af}";
									$bits = explode('_', $one_af);
									$bits2 = explode('/', $bits[0]); # $bits[0]: subdir, stroke and first word of filename e.g. "v123456/letter"
									$pdf_label = $bits2[count($bits2)-1]; # e.g. "letter"
									$resend_attach .= "&nbsp;&nbsp;$pdf_label:<a href=\"$pdf_url_sent\" target=\"_blank\" rel=\"noopener\">" .
										"<img src=\"images/pdf.png\" height=\"23\" width=\"23\"></a>&nbsp;&nbsp;";
								}
							}
							$sent = date_for_sql($old_email_info['EM_DT'], true, true, true, false, false, false, false, true);
							$resends .= "Sent: $sent to: " . $old_email_info['EM_TO'] .
								"&nbsp;&nbsp;<span $grey>ID:$old_email_id</span><br>" .
								"Subject: " . $old_email_info['EM_SUBJECT'] . "<br>" .
								"Message: " . $old_email_info['EM_MESSAGE'] . "<br>" .
								"Attached: " . $resend_attach . "<br>" .
								"<hr><br>";
						}
					}
					print "
							<tr>
								$gap
								<td $col7><br><u>Email Info</u></td>
							</tr>
							<tr>
								$gap
								<td $ar>Date Sent:</td>
								<td $col2>$emailed_dt</td>
								<td></td>
								<td align=\"right\">
									";
					if ($resend_207 == 0)
						print input_button('Re-send', "resend_207($letter_id)", "");
					elseif ($resend_now)
						print input_button('Send now', "send_now_207($letter_id)", "");
					print "
									</td>
								";
					$emails = sql_client_emails($job['CLIENT2_ID'], true);
					$def_email = '';
					foreach ($emails as $one_em)
					{
						if ($one_em == $letter['EM_TO'])
						{
							$def_email = $one_em;
							break; # just take the first one
						}
					}
					if (!$def_email)
					{
						if ($emails)
							$def_email = $emails[0];
					}
					print "
								<td $col3>
									";
					if ($resend_now)
						print input_button('Cancel', "cancel_207($letter_id)", "");
					#input_button('Re-send to', "email_resend($letter_id)", ($manager_a || $manager_t) ? '' : 'disabled') . "
					#input_select('resend_to', $emails, $def_email) .
					print "
									</td>
							</tr>
							<tr>
								$gap
								";
					if ($resend_now)
					{
						print "
									<td $ar>Send To:</td>
									<td $col6>" . input_select('resend_to', $emails, $def_email) . "</td>
									";
					}
					else
					{
						print "
									<td $ar>Sent To:</td>
									<td $col6>{$letter['EM_TO']}</td>
									";
					}
					print "
							</tr>
							<tr>
								$gap
								<td $ar $at>Subject:</td>
								<td $col6>
									";
					if ($resend_now)
						print input_textarea('resend_subject', 1, 60, $letter['EM_SUBJECT'], "style=\"height:25px;\"");
					else
						print "<textarea rows=\"1\" cols=\"60\" style=\"height:25px;\" readonly>{$letter['EM_SUBJECT']}</textarea>";
					print "
									</td>
							</tr>
							<tr>
								$gap
								<td $ar $at>Message:</td>
								<td $col6>
									";
					if ($resend_now)
						print input_textarea('resend_message', 3, 60, br2nl_kdb($letter['EM_MESSAGE']));
					else
						print "<textarea rows=\"3\" cols=\"60\" readonly>" . br2nl_kdb($letter['EM_MESSAGE']) . "</textarea>";
					print "
									</td>
							</tr>
							<tr>
								$gap
								<td $ar>Attached:</td>
								<td $col6>$attach</td>
							</tr>
							<tr>
								$gap
								<td $ar $at>History:</td>
								<td $col6>
									<div style=\"border:1px solid black;\">
									$resends
									</div></td>
							</tr>
							";
				}
				else
				{
					print "
							<tr>$gap</tr>
							<tr>$gap</tr>
							<tr>$gap</tr>
							<tr>$gap</tr>
							<tr>$gap</tr>
							<tr>$gap</tr>
							";
				}
			} # foreach LETTERS_SENT
			print "
					</table><!--table_letters_sent-->
					";
		}
		else
			print "A letter has been sent.";
		print "
				</div><!--div_letters_sent-->
			</td>
		</tr>
		";

	}  #elseif ($job['LETTERS_SENT'])

} # print_one_job_trace_letter()

function post_job_letter()
{
	global $id_ACTIVITY_lse;
	global $job_id;
	global $sqlFalse;

	$letter_id = post_val('letter_id', true);

	$sql = "SELECT JT_JOB
			FROM JOB
			WHERE JOB_ID=$job_id
			";
	#dprint($sql);#
	sql_execute($sql);
	$jt_job = 0;
	while (($newArray = sql_fetch_assoc()) != false)
		$jt_job = intval($newArray['JT_JOB']);

	list($ms_top, $my_limit) = sql_top_limit(1);
	$sql = "SELECT $ms_top I.INVOICE_ID
			FROM INVOICE AS I INNER JOIN INV_BILLING AS B ON B.INVOICE_ID=I.INVOICE_ID
			INNER JOIN JOB AS J ON J.JOB_ID=B.JOB_ID
			WHERE B.JOB_ID=$job_id AND B.OBSOLETE=$sqlFalse AND I.INV_APPROVED_DT IS NOT NULL
			$my_limit ";
	#dprint($sql);#
	sql_execute($sql);
	$invoice_id = 0;
	while (($newArray = sql_fetch_assoc()) != false)
		$invoice_id = $newArray['INVOICE_ID'];

	#dprint("\$invoice_id=$invoice_id, \$jt_job=$jt_job");#

	$now = date_now_sql();

	$sql = "UPDATE JOB_LETTER SET JL_POSTED_DT='$now' WHERE JOB_LETTER_ID=$letter_id";
	dprint($sql);
	audit_setup_job($job_id, 'JOB_LETTER', 'JOB_LETTER_ID', $letter_id, 'JL_POSTED_DT', $now);
	sql_execute($sql, true); # audited

	sql_add_activity($job_id, 1, $id_ACTIVITY_lse, false); # don't call sql_update_job()

	sql_update_letter($job_id, $letter_id);

	if ($jt_job)
	{
		if (0 < $invoice_id)
		{
			$sql = "UPDATE INVOICE SET INV_POSTED_DT='$now' WHERE INVOICE_ID=$invoice_id";
			dprint($sql);
			audit_setup_job($job_id, 'INVOICE', 'INVOICE_ID', $invoice_id, 'INV_POSTED_DT', $now);
			sql_execute($sql, true); # audited
		}

		$sql = "UPDATE JOB SET J_COMPLETE=1 WHERE JOB_ID=$job_id";
		dprint($sql);
		audit_setup_job($job_id, 'JOB', 'JOB_ID', $job_id, 'J_COMPLETE', 1);
		sql_execute($sql, true); # audited

		sql_close_job($job_id);
	}

	sql_update_job($job_id);

} # post_job_letter()

function print_one_job_collect_letter($job, $editing, $open, $numcols, $div_h_x2)
{
	global $ac;
	global $ar;
	global $at;
	global $col2;
	global $col4;
	global $col5;
	global $col6;
	global $col7;
	global $csv_dir;
	global $gap;
	global $grey;
	global $job_id;
	global $manager_c;
	global $manager_x;
	global $onchange_sel;
	global $onchange_txt;
	global $style_r;
	#global $szsmall;
	global $ta_cols;
	global $user_debug;

	$feedback_39 = global_debug();

	$letter_types = sql_get_letter_types_for_client($job['CLIENT2_ID'], 'c');
	$letter_types_nm = sql_get_letter_types_for_client($job['CLIENT2_ID'], 'c', true); # LT_NON_MAN

	$all_letters = true;
	$editing2 = $editing;
	if ($feedback_39) {
		if (!$manager_x)
		{
			if (array_key_exists($job['COLLECT_DETAILS']['JC_LETTER_TYPE_ID'], $letter_types_nm))
				$all_letters = false;
			else
				$editing2 = false;
		}
	}

	$biggap2 = "	&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
					&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
					";
	$onchange_ml_tck = (($editing2 && $open) ? "update_more_letters(this)" : '');
	$extra_ml_tck = (($editing2 && $open) ? '' : 'disabled');

	$more_letters = ($job['COLLECT_DETAILS']['JC_LETTER_MORE'] ? true : false);
	print "
	<tr>
		<td $ar $at>Letters</td>
		<td $ar $at>More letters</td>
		<td $at>" . input_tickbox('', 'jc_letter_more', 1, $more_letters, $onchange_ml_tck, $extra_ml_tck) . "</td>
		<td $ac $col2>Next Letter: " . input_select('jc_letter_type_id', $all_letters ? $letter_types : $letter_types_nm, $job['COLLECT_DETAILS']['JC_LETTER_TYPE_ID'],
			($editing2 && $more_letters) ? $onchange_sel : 'disabled') . "</td>
		<td></td>
		<td $col4>Delay: " . input_textbox('jc_letter_delay', $job['COLLECT_DETAILS']['JC_LETTER_DELAY'], 3, 10, "$onchange_txt $style_r") . "days</td>
		<td>" . (($editing && $open) ? input_button('Add Next Letter', 'add_collect_letter()',
			$more_letters ? '' : 'disabled', 'add_cltr_button') : '') . "</td>
	</tr>
	";

	# Pending Letters section
	print "
	<tr>
		<td $at $ar>Pending<br>Letters</td>
		<td $at colspan=\"" . ($numcols-1) . "\">
		" . input_hidden('pending_letters_count', count($job['LETTERS_PENDING'])) . "
		";
	if ($job['LETTERS_PENDING'])
	{
		#dprint("LETTERS_PENDING=" . print_r($job['LETTERS_PENDING'],1));#
		$div_h_ltr = (($editing && $job['LETTERS_PENDING'][0]['JL_APPROVED_DT']) ? 1.7 : 1.0) * $div_h_x2;
		print "
			<div id=\"div_letters_pending\" style=\"height:{$div_h_ltr}px; overflow-y:scroll; border:solid gray 1px;\">
			<table class=\"basic_table\" id=\"table_letters_pending\" border=\"0\"><!---->
			<tr>
				<td>" .#&nbsp;The Letter that is Pending (\"{$job['LETTERS_PENDING'][0]['LETTER_NAME']}\")
			"</td>
				$gap<td $ac>Added</td>$gap<td $ac>Last Updated</td>
				" . (user_debug() ? "$gap<td $ac $grey>DB ID</td>" : '') . "
			</tr>
			";
		foreach ($job['LETTERS_PENDING'] as $letter)
		{
			$letter_id = $letter['JOB_LETTER_ID'];
			$letter_appr = (($letter['JL_APPROVED_DT'] == '') ? false : true);
			$pdf_url_pend = pdf_link('jl', "v{$job['J_VILNO']}", "{$job['J_VILNO']}_{$job['J_SEQUENCE']}_{$letter_id}");

			$added_dt = str_replace(' ', '<br>', date_for_sql($letter['JL_ADDED_DT'], true, true, true, false, false, false, false, true));
			if ($letter['JL_UPDATED_DT'])
			{
				$updated_dt = date_for_sql($letter['JL_UPDATED_DT'], true, true, true, false, false, false, false, true);
				$updated_dt = $letter['UPDATED_U'] . '<br>' . str_replace(' ', '<br>', $updated_dt);
			}
			else
				$updated_dt = '';

			$onchange_letter_txt = (($editing && $open && (!$letter_appr)) ? "onkeydown=\"letter_warn();\"" : 'readonly');
			$onchange_letter_tck = (($editing && $open && $manager_c) ? "letter_approve(this);" : '');
			$extra_letter_tck = (($editing && $open && $manager_c) ? '' : 'disabled');

			print "
				<tr>
					<td rowspan=\"2\">
						\"{$job['LETTERS_PENDING'][0]['LETTER_NAME']}\"<br>
						" . input_textarea('letter_preview', 15, $ta_cols, $letter['JL_TEXT'] . $letter['JL_TEXT_2'], $onchange_letter_txt) . "<br>
						" . input_hidden('letter_preview_id', $letter_id) . "
						<span style=\"color:red\" id=\"letter_warning\"></span>
						";
			if ($editing && $open && (!$letter_appr))
				print "&nbsp;" . input_button('Check Spelling', 'spell_letter()', '', 'spellcheck_button') . "&nbsp;" .
					input_button('Save Letter', "save_letter_preview('')");
			print $biggap2;
			print input_tickbox('Letter Approved', 'jl_approved_dt', 1, $letter_appr ? 1 : 0,
					$onchange_letter_tck, $extra_letter_tck) . "&nbsp;&nbsp;";
			if ($open && $letter_appr)#$editing &&
			{
				if ($pdf_url_pend)
					print "
								<a href=\"$pdf_url_pend\" target=\"_blank\" rel=\"noopener\"><img src=\"images/pdf.png\" height=\"23\" width=\"23\"></a>&nbsp;&nbsp;&nbsp;";
			}
			if ($editing && $manager_c && $open && (global_debug() || (!$letter_appr)))
				print $biggap2 . input_button('Delete letter', "delete_letter($letter_id)");
			print "&nbsp;&nbsp;&nbsp;" . ($letter_id ? "<span $grey>ID $letter_id</span>" : '');
			print "
					</td>
					$gap
					<td $ac $at><br>" . ($letter['IMPORTED'] ? 'Imported<br>from old system<br>' : '') . "$added_dt</td>
					$gap
					<td $ac $at><br>$updated_dt</td>
					" . ($user_debug ? "$gap<td $ac $at $grey><br>$letter_id</td>" : '') . "
				</tr>
				";
			if ($editing && $open && $letter_appr && $manager_c)
			{
				#$emails = sql_client_emails($job['CLIENT2_ID'], true);
				$emails = sql_subject_emails($job_id, true);
				$def_email = '';
				foreach ($emails as $one_em)
				{
					$def_email = $one_em;
					break; # just take the first one
				}
				print "
				  <tr>
					$gap
					<td $col5>
						<table name=\"email_table\" class=\"basic_table\" border=\"0\"><!---->
						<tr>
							<td>To:</td>
							<td>" . input_select('email_addr', $emails, $def_email) . "</td>
						</tr>
						<tr>
							<td>Subject:</td>
							<td>" . input_textbox('email_subject', 'Our Letter', 60, 1000) . "</td>
						</tr>
						<tr>
							<td $at>Message:</td>
							<td>" . input_textarea('email_message', 5, 57, 'Please find attached our letter for the collection job.') . "</td>
						</tr>
						<tr>
							<td></td>
							<td>The PDF of the Letter will be automatically attached to the outgoing email.</td>
						</tr>
						<tr>
							<td $col2>" . input_button('Email Letter', "email_letter('c',$letter_id)") . "</td>
						</tr>
						<tr>
							<td $col7>" . input_button('Or, Letter has been printed and posted', "post_letter($letter_id)") . "</td>
						</tr>
						</table><!--email_table-->
					</td>
				  </tr>
					";
			}
			print "
				<tr><td>&nbsp;</td></tr>
				";
		} # foreach LETTERS_PENDING
		print "
			</table><!--table_letters_pending-->
			</div><!--div_letters_pending-->
			";

		if ($editing && $open && (!$letter_appr))
			print "
				<script>
				var checker = new sc.SpellChecker({
					button: 'spellcheck_button', // HTML element that will open the spell checker when clicked
					textInput: 'letter_preview', // HTML field containing the text to spell check
					action: 'spell/spellcheck.php' // URL of the server side script
					});
				</script>
				";
	} # if pending letters
	else
		print "
			<div id=\"div_letters_pending\" style=\"height:80px; overflow-y:scroll; border:solid gray 1px;\">
			There are no letters that are pending.
			</div><!--div_letters_pending-->
			";
	print "
		</td>
	</tr>
	";
	# end of pendng letters section

	# Sent Letters section
	print "
	<tr>
		<td $at $ar>Sent<br>Letters</td>
		<td $at colspan=\"" . ($numcols-1) . "\">
		";
	if (#$manager_c &&
	$job['LETTERS_SENT'])
	{
		$headers = "
				<tr>
					<td>&nbsp;The Letter that was Sent (\"unknown type\")</td>$gap<td $ac>Added</td>$gap<td $ac>Last Updated</td>$gap<td $ac>Posted</td>
					" . ($user_debug ? "$gap<td $ac $grey>DB ID</td>" : '') . "
				</tr>
				";
		print "
			<div id=\"div_letters_sent\" style=\"height:{$div_h_x2}px; overflow-y:scroll; border:solid gray 1px;\">
			&nbsp;Contact Letters: ";
		if (0 < $job['LETTERS_SENT_COUNT'])
			print $job['LETTERS_SENT_COUNT'] . ", last one: " . $job['LETTERS_SENT_LAST'] . '.';
		else
			print "none.";
		print "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
				Demand Letters: ";
		if (0 < $job['LETTERS_DEMD_COUNT'])
			print $job['LETTERS_DEMD_COUNT'] . ", last one: " . $job['LETTERS_DEMD_LAST'] . '.';
		else
			print "none.";
		print "
			<br><br>
			<table class=\"basic_table\" id=\"table_letters_sent\" border=\"0\"><!---->
			";
		foreach ($job['LETTERS_SENT'] as $letter)
		{
			$letter_id = $letter['JOB_LETTER_ID'];
			$added_dt = str_replace(' ', '<br>', date_for_sql($letter['JL_ADDED_DT'], true, true, true, false, false, false, false, true));
			$posted_dt = str_replace(' ', '<br>', date_for_sql($letter['JL_POSTED_DT'], true, true, true, false, false, false, false, true));
			$emailed_dt = date_for_sql($letter['EM_DT'], true, true, true, false, false, false, false, true);

			if ($letter['JL_UPDATED_DT'])
			{
				$updated_dt = date_for_sql($letter['JL_UPDATED_DT'], true, true, true, false, false, false, false, true);
				$updated_dt = $letter['UPDATED_U'] . '<br>' . str_replace(' ', '<br>', $updated_dt);
			}
			else
				$updated_dt = '';

			$attach = '';
			if ($letter['EM_ATTACH'])
			{
				# Example EM_ATTACH: "v1512257/letter_1512257_90868311_373223_20161123_103127.pdf|i/invoice_204145_20161123_102417.pdf"
				$afiles = explode('|', $letter['EM_ATTACH']);
				foreach ($afiles as $one_af)
				{
					$pdf_url_sent = "{$csv_dir}/{$one_af}";
					$bits = explode('_', $one_af);
					$bits2 = explode('/', $bits[0]); # $bits[0]: subdir, stroke and first word of filename e.g. "v123456/letter"
					$pdf_label = $bits2[count($bits2)-1]; # e.g. "letter"
					$attach .= "&nbsp;&nbsp;$pdf_label:" .
						"<a href=\"$pdf_url_sent\" target=\"_blank\" rel=\"noopener\"><img src=\"images/pdf.png\" height=\"23\" width=\"23\"></a>&nbsp;&nbsp;";
				}
			}

			print "
				" . str_replace('unknown type', $letter['LETTER_NAME'], $headers) . "
				<tr>
					<td " . ($letter['EM_DT'] ? "rowspan=\"7\"" : '') . ">
						" . input_textarea('jl_letter_sent', 4, $ta_cols, ($manager_c ? ($letter['JL_TEXT'] . $letter['JL_TEXT_2']) : '...'), 'readonly') . "</td>
					$gap
					<td $ac $at><br>" . ($letter['IMPORTED'] ? 'Imported<br>from old system<br>' : '') . "$added_dt</td>
					$gap
					<td $ac $at><br>$updated_dt</td>
					$gap
					<td $ac $at><br>$posted_dt</td>
					" . ($user_debug ? "$gap<td $ac $at $grey><br>$letter_id</td>" : '') . "
				</tr>
				";
			if ($letter['EM_DT'])
			{
				print "
					<tr>
						$gap
						<td $col7><br><u>Email Info</u></td>
					</tr>
					<tr>
						$gap
						<td $ar>Date Sent:</td>
						<td $col6>$emailed_dt</td>
					</tr>
					<tr>
						$gap
						<td $ar>Sent To:</td>
						<td $col6>{$letter['EM_TO']}</td>
					</tr>
					<tr>
						$gap
						<td $ar $at>Subject:</td>
						<td $col6><textarea rows=\"1\" cols=\"60\" style=\"height:25px;\" readonly>{$letter['EM_SUBJECT']}</textarea></td>
					</tr>
					<tr>
						$gap
						<td $ar $at>Message:</td>
						<td $col6><textarea rows=\"3\" cols=\"60\" readonly>" . ($manager_c ? $letter['EM_MESSAGE'] : '...') . "</textarea></td>
					</tr>
					<tr>
						$gap
						<td $ar valign=\"bottom\">Attached:</td>
						<td $col6>" . ($manager_c ? $attach : '...') . "</td>
					</tr>
					";
			}
//				else
//				{
//					print "
//					<tr>$gap</tr>
//					<tr>$gap</tr>
//					<tr>$gap</tr>
//					<tr>$gap</tr>
//					<tr>$gap</tr>
//					<tr>$gap</tr>
//					";
//				}
			print "
				<tr><td colspan=\"7\"><hr style=\"color:#f0f0f0\"></td></tr>
				";
		} # foreach LETTERS_SENT

		print "
			</table><!--table_letters_sent-->
			</div><!--div_letters_sent-->
			";
	}
	else
	{
		print "
			<div id=\"div_letters_sent\" style=\"height:80px; overflow-y:scroll; border:solid gray 1px;\">
			";
		if ($job['LETTERS_SENT'])
			print "Letters have been sent.";
		else
			print "There are no letters that have been sent.";
		print "
			</div><!--div_letters_sent-->
			";
	}
	print "
		</td>
	</tr>
	";

} # print_one_job_collect_letter()

//function item_1_10($x)
//{
//	if (($x == '') || ($x == '.00'))
//		$x = '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
//	$x = "[<u>$x</u>]";
//	return $x;
//}
//
//function print_collect_1_10($job)
//{
//	global $col3;
//
//	$details = $job['COLLECT_1_10'];
//	$gap = "<td>&nbsp;</td>";
//	print "
//	<table name=\"collect_1_10\" class=\"basic_table\" border=\"1\">
//	<tr>
//		<td>" . ($details['JC_IN_PROGRESS_CU1'] ? 'yes' : 'no') . "</td>
//		$gap
//		<td>1.</td>
//		$gap
//		<td>Collection activity still in progress</td>
//	</tr>
//	<tr>
//		<td>" . ($details['JC_SUBJ_PAID_CU2'] ? 'yes' : 'no') . "</td>
//		$gap
//		<td>2.</td>
//		$gap
//		<td>Your customer paid " . item_1_10($details['JC_PAID_AMT_CU2']) . " on " . item_1_10($details['JC_PAID_DT_CU2']) . " to us/direct</td>
//	</tr>
//	<tr>
//		<td>" . ($details['JC_PDCHEQUES_CU3'] ? 'yes' : 'no') . "</td>
//		$gap
//		<td>3.</td>
//		$gap
//		<td>We are holding postdated cheques as follows:</td>
//	</tr>
//	<tr>
//		<td></td>
//		$gap
//		<td $col3>" . item_1_10($details['JC_PDC_TXT_CU3']) . "</td>
//	</tr>
//	<tr>
//		<td>" . ($details['JC_SUBJ_CONT_CU4'] ? 'yes' : 'no') . "</td>
//		$gap
//		<td>4.</td>
//		$gap
//		<td>We have made contact with your customer</td>
//	</tr>
//	<tr>
//		<td>" . ($details['JC_AGREED_CU5'] ? 'yes' : 'no') . "</td>
//		$gap
//		<td>5.</td>
//		$gap
//		<td>They have agreed to pay (or TDX Client Name)</td>
//	</tr>
//	<tr>
//		<td></td>
//		$gap
//		<td $col3>" . item_1_10($details['JC_AGR_TXT_CU5']) . "</td>
//	</tr>
//	<tr>
//		<td>" . ($details['JC_NEW_ADR_CU6'] ? 'yes' : 'no') . "</td>
//		$gap
//		<td>6.</td>
//		$gap
//		<td>Customer's new address is:</td>
//	</tr>
//	<tr>
//		<td></td>
//		$gap
//		<td $col3>" . item_1_10($details['JC_ADDR_1_CU6']) . "</td>
//	</tr>
//	<tr>
//		<td></td>
//		$gap
//		<td $col3>" . item_1_10($details['JC_ADDR_2_CU6']) . "</td>
//	</tr>
//	<tr>
//		<td></td>
//		$gap
//		<td $col3>" . item_1_10($details['JC_ADDR_3_CU6']) . "</td>
//	</tr>
//	<tr>
//		<td></td>
//		$gap
//		<td $col3>" . item_1_10($details['JC_ADDR_4_CU6']) . "</td>
//	</tr>
//	<tr>
//		<td></td>
//		$gap
//		<td $col3>" . item_1_10($details['JC_ADDR_5_CU6']) . "</td>
//	</tr>
//	<tr>
//		<td></td>
//		$gap
//		<td $col3>" . item_1_10($details['JC_ADDR_PC_CU6']) . "</td>
//	</tr>
//	<tr>
//		<td>" . ($details['JC_NO_ADDR_CU7'] ? 'yes' : 'no') . "</td>
//		$gap
//		<td>7.</td>
//		$gap
//		<td>Can't find address. Closing file.</td>
//	</tr>
//	<tr>
//		<td>" . ($details['JC_FAIL_PROM_CU8'] ? 'yes' : 'no') . "</td>
//		$gap
//		<td>8.</td>
//		$gap
//		<td>They have failed to adhere to promises of payments</td>
//	</tr>
//	<tr>
//		<td>" . ($details['JC_NOT_RESP_CU9'] ? 'yes' : 'no') . "</td>
//		$gap
//		<td>9.</td>
//		$gap
//		<td>They have not responded</td>
//	</tr>
//	<tr>
//		<td>" . ($details['JC_ADD_NOTES_CU10'] ? 'yes' : 'no') . "</td>
//		$gap
//		<td>10.</td>
//		$gap
//		<td>Additional notes, or TDX Account/Assignment/Primary Person IDs</td>
//	</tr>
//	<tr>
//		<td></td>
//		$gap
//		<td $col3>" . item_1_10($details['JC_AN1_CU10']) . "</td>
//	</tr>
//	<tr>
//		<td></td>
//		$gap
//		<td $col3>" . item_1_10($details['JC_AN2_CU10']) . "</td>
//	</tr>
//	<tr>
//		<td></td>
//		$gap
//		<td $col3>" . item_1_10($details['JC_AN3_CU10']) . "</td>
//	</tr>
//	</table><!--collect_1_10-->
//	";
//} # print_collect_1_10()

function print_one_job_collect_schpay($job)#, $editable)
{
	#global $ADJUSTMENTS; # init_data()
	global $ac;
	global $ar;
	#global $at;
	#global $col6;
	#global $col11;
	global $div_h_max;
	global $grey;
	global $id_ROUTE_cspent;
	global $manager_a;
	global $mktime_year_limit;
	global $payment_methods_sel; # init_data()
	global $payment_routes_sel; # init_data()
	global $style_r;
	#global $szsmall;
	#global $ta_cols;
	#global $tr_colour_2;
	global $yn_list;

	$editable = false;

	# ---- Step 1 - Get Schedule --------------------------------------------------------------------------------------------------------------------

	$arrangements = array(); # oldest arrangements come first
	$prev_start_dt = '';
	$jj = 0;
	for ($ii = count($job['PREV_ARRANGE'])-1; 0 <= $ii; $ii--)
	{
		$arrange = $job['PREV_ARRANGE'][$ii]; # the arrangement we are now examining
		if (0.0 < $arrange['JA_INSTAL_AMT'])
		{
			if ($prev_start_dt && ($arrange['JA_INSTAL_DT_1'] == $prev_start_dt))
				$jj--;
			$arrangements[$jj] = array('START_DT' => $arrange['JA_INSTAL_DT_1'], 'AMOUNT' => $arrange['JA_INSTAL_AMT'],
				'FREQ' => $arrange['JA_INSTAL_FREQ']);
			$jj++;
			$prev_start_dt = $arrange['JA_INSTAL_DT_1'];
		}
	}
	if (0.0 < $job['COLLECT_DETAILS']['JC_INSTAL_AMT'])
	{
		if ($prev_start_dt && ($job['COLLECT_DETAILS']['JC_INSTAL_DT_1'] == $prev_start_dt))
			$jj--;
		$arrangements[$jj] = array('START_DT' => $job['COLLECT_DETAILS']['JC_INSTAL_DT_1'], 'AMOUNT' => $job['COLLECT_DETAILS']['JC_INSTAL_AMT'],
			'FREQ' => $job['COLLECT_DETAILS']['JC_INSTAL_FREQ']);
	}

	$count_arr = count($arrangements);
	if ($count_arr == 0)
	{
		print "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;There is no schedule because there is no arrangement";
		return;
	}
	#dprint("arrangements:" . print_r($arrangements,1));#

	$schedule = array();
	$pmt_ix = count($job['PAYMENTS']) - 1; # index into $job['PAYMENTS']
	$total_due = 0.0;
	$total_paid = 0.0;
	$end_ep = date_to_epoch("{$mktime_year_limit}-01-01"); # 01/01/2037
	#dprint("end_ep=$end_ep");#

	for ($ii = 0; $ii < $count_arr; $ii++)
	{
		$arrange = $arrangements[$ii]; # the arrangement we are now examining
		$due_amt = $arrange['AMOUNT'];
		$next_arr = ((($ii+1) < $count_arr) ? $arrangements[$ii+1] : ''); # the next (later) arrangement
		$start_dt = $arrange['START_DT'];
		$start_ep = date_to_epoch($start_dt);
		if ($next_arr)
		{
			$next_start_dt = $next_arr['START_DT'];
			$next_start_ep = ($next_start_dt ? date_to_epoch($next_start_dt) : 0);
		}
		else
		{
			$next_start_dt = '';
			$next_start_ep = $end_ep;
		}

		$period = 0; # period between payments, in days, unless monthly or quarterly
		$day = 0; # day of the month, if monthly or quarterly
		if ($arrange['FREQ'] == 'I')
			$period = 1;
		elseif ($arrange['FREQ'] == 'D')
			$period = 1;
		elseif ($arrange['FREQ'] == 'W')
			$period = 7;
		elseif ($arrange['FREQ'] == 'F')
			$period = 14;
		elseif ($arrange['FREQ'] == 'M')
			$day = substr($start_dt, 8, 2);
		elseif ($arrange['FREQ'] == 'Q')
			$day = substr($start_dt, 8, 2);
		else
			$period = 1;
		$period *= (24 * 60 * 60); # convert days to seconds

		$months = 0;
		for ($ep = $start_ep; $ep < $next_start_ep; )
		{
			$paid = 0.0;
			for ( ; 0 <= $pmt_ix; $pmt_ix--)
			{
				$payment = $job['PAYMENTS'][$pmt_ix];
				if ($payment['COL_EP_RX'] <= $ep)
					$paid += floatval($payment['COL_AMT_RX']);
				else
					break; # leave $pmt_ix ready for next line of $schedule
			}

			$last_line = false;
			if (($job['COLLECT_DETAILS']['JC_TOTAL_AMT'] - $total_due) <= $due_amt)
			{
				$due_amt = $job['COLLECT_DETAILS']['JC_TOTAL_AMT'] - $total_due;
				$last_line = true;
			}
			$total_due += $due_amt;
			$total_paid += $paid;
			if (($job['COLLECT_DETAILS']['JC_TOTAL_AMT'] <= $total_paid) && ($job['COLLECT_DETAILS']['JC_PAID_SO_FAR'] <= $total_paid ))
				$last_line = true;
			$schedule[] = array('DUE_EP' => $ep, 'DUE_DT' => date_from_epoch(true, $ep, false, false, true),
				'DUE_AMT' => $due_amt, 'PAID' => $paid, 'TOTAL_DUE' => $total_due, 'TOTAL_PAID' => $total_paid);
			if ($last_line)
				break;

			# Prepare for next iteration of loop:
			if ($day)
			{
				if ($arrange['FREQ'] == 'M')
					$months++;
				else
					$months += 3;
				$ep = date_add_months_kdb($start_ep, $months);
			}
			else
				$ep += $period;
		} # for ($ep)
	} # for ($ii thru arrangements)
	#dprint("schedule(" . count($schedule) . "): " . print_r($schedule,1));#

	# ---- Step 2 - Get Payments --------------------------------------------------------------------------------------------------------------------

	$payments = array();
	for ($ii = count($job['PAYMENTS'])-1; 0 <= $ii; $ii--)
	{
		$paymt = $job['PAYMENTS'][$ii];
		$bits = explode(' ', $paymt['COL_DT_RX']);
		$col_dt_rx = $bits[0];
		$payments[] = array('JOB_PAYMENT_ID' => $paymt['JOB_PAYMENT_ID'],
			'IS_ADJ' => (($paymt['COL_PAYMENT_ROUTE_ID'] == $id_ROUTE_cspent) ? 1 : 0),
			'COL_DT_RX' => $col_dt_rx, 'COL_AMT_RX' => $paymt['COL_AMT_RX'],
			'COL_PAYMENT_ROUTE_ID' => $paymt['COL_PAYMENT_ROUTE_ID'], 'PAYMENT_ROUTE' => $paymt['PAYMENT_ROUTE'],
			'COL_PAYMENT_METHOD_ID' => $paymt['COL_PAYMENT_METHOD_ID'],
			'INVOICE_ID' => $paymt['INVOICE_ID'], 'INV_NUM' => $paymt['INV_NUM'],
			'ADJUSTMENT_ID' => $paymt['ADJUSTMENT_ID'], 'COL_PERCENT' => $paymt['COL_PERCENT'],
			'COL_BOUNCED' => $paymt['COL_BOUNCED'], 'COL_NOTES' => $paymt['COL_NOTES'],
			'IMPORTED' => $paymt['IMPORTED']
		);
	}
	#dprint("payments(" . count($payments) . "): " . print_r($payments,1));#

	# ---- Step 3 - Combine Schedule and Payments ---------------------------------------------------------------------------------------------------

	$ix_s = 0; # index into $schedule
	$ix_p = 0; # index into $payments
	$max_s = count($schedule);
	$max_p = count($payments);
	$fin_s = ($max_s ? false : true); # whether finished $schedule
	$fin_p = ($max_p ? false : true); # whether finished $payments
	$combined = array(); # combination of $schedule and $payments

	$prev_total_due = 0.0;
	$prev_total_paid = 0.0;
	for ($safety = 0; $safety < 10000; $safety++)
	{
		$show_s = ''; # show schedule line in combined line
		$show_p = ''; # show payment line in combined line
		$dt_s = ($fin_s ? '' : $schedule[$ix_s]['DUE_DT']);
		$dt_p = ($fin_p ? '' : $payments[$ix_p]['COL_DT_RX']);
		#dprint("\$safety=$safety, \$ix_s=$ix_s, \$max_s=$max_s, \$ix_p=$ix_p, \$max_p=$max_p, \$fin_s=$fin_s, \$fin_p=$fin_p, \$dt_s=$dt_s, \$dt_p=$dt_p");#
		if ((!$fin_s) && (!$fin_p) && ($dt_s == $dt_p))
		{
			$show_s = $schedule[$ix_s++];
			$show_p = $payments[$ix_p++];
			$which = 'C'; # combined line
		}
		elseif ((!$fin_s) && ($fin_p || ($dt_s < $dt_p)))
		{
			$show_s = $schedule[$ix_s++];
			$which = 'S'; # schedule-only line
		}
		elseif ((!$fin_p) && ($fin_s || ($dt_p < $dt_s)))
		{
			$show_p = $payments[$ix_p++];
			$which = 'P'; # payment-only line
		}
		# no need for final 'else'

		if ($show_s)
			$total_paid = $show_s['TOTAL_PAID'];
		else
			$total_paid = $prev_total_paid + $show_p['COL_AMT_RX'];
		$combined[] = array('WHICH' => $which,
			'DUE_DT' => ($show_s ? $show_s['DUE_DT'] : ''),
			'DUE_EP' => ($show_s ? $show_s['DUE_EP'] : 0),
			'DUE_AMT' => ($show_s ? $show_s['DUE_AMT'] : 0.0),
			'COL_DT_RX' => ($show_p ? $show_p['COL_DT_RX'] : ''),
			'COL_AMT_RX' => ($show_p ? $show_p['COL_AMT_RX'] : 0.0),
			'JOB_PAYMENT_ID' => ($show_p ? $show_p['JOB_PAYMENT_ID'] : 0),
			'IS_ADJ' => (($show_p && ($show_p['COL_PAYMENT_ROUTE_ID'] == $id_ROUTE_cspent)) ? 1 : 0),
			'COL_PAYMENT_ROUTE_ID' => ($show_p ? $show_p['COL_PAYMENT_ROUTE_ID'] : 0),
			'PAYMENT_ROUTE' => ($show_p ? $show_p['PAYMENT_ROUTE'] : ''),
			'COL_PAYMENT_METHOD_ID' => ($show_p ? $show_p['COL_PAYMENT_METHOD_ID'] : 0),
			'INVOICE_ID' => ($show_p ? $show_p['INVOICE_ID'] : 0),
			'INV_NUM' => ($show_p ? $show_p['INV_NUM'] : 0),
			'ADJUSTMENT_ID' => ($show_p ? $show_p['ADJUSTMENT_ID'] : 0),
			'COL_PERCENT' => ($show_p ? $show_p['COL_PERCENT'] : 0.0),
			'COL_BOUNCED' => ($show_p ? $show_p['COL_BOUNCED'] : 0),
			'COL_NOTES' =>  ($show_p ? $show_p['COL_NOTES'] : ''),
			'IMPORTED' => ($show_p ? $show_p['IMPORTED'] : 0),
			'TOTAL_DUE' => ($show_s ? $show_s['TOTAL_DUE'] : $prev_total_due),
			'TOTAL_PAID' => $total_paid
		);

		if ($show_s)
			$prev_total_due = $show_s['TOTAL_DUE'];
		$prev_total_paid = $total_paid;

		if ($max_s <= $ix_s)
			$fin_s = true;
		if ($max_p <= $ix_p)
			$fin_p = true;
		if ($fin_s && $fin_p)
			break;

	} # for ($safety)
	#dprint("combined(" . count($combined) . "): " . print_r($combined,1));#

	# ---- Step 4 - Display Combination -------------------------------------------------------------------------------------------------------------

	$div_h = 50 + (30 * count($combined));
	if ($div_h_max < $div_h)
		$div_h = $div_h_max;

	$headings = "
	<tr>
		<th>Due<br>Date</th><th>Due<br>Amount</th><th>Paid<br>Date</th><th>Paid<br>Amount</th>
		<th>Route</th><th>Method</th><th>Invoice</th>" .#<th>Adjustment Reason</th>
		"
		<th>Comm-<br>is'n %</th><th>Comm-<br>is'n &pound;</th><th>Bounced</th>
		<th>Total<br>Due</th><th>Total<br>Paid</th><th>Arrears</th>
		<th>Imp-<br>orted</th><th><span $grey>DB ID</span></th>
	</tr>
	";

	$table_lines = array($headings);

	#$headings = str_replace('<th>', "<td $ac style=\"background-color:{$tr_colour_2}\"><b>", str_replace('</th>', '</b></td>', $headings));

	$count = 0;
	$sum = 0.0;
	$final_arrears = 0.0;
	$time = time();
	$tix = 0; # index into table rows
	$btnw = 75;
	$prev_future = -1; # value of $future on previous line
	#$prev_arrears = '';

	for ($cix = count($combined)-1; 0 <= $cix; $cix--)
	{
		$details = $combined[$cix];
		$id = $details['JOB_PAYMENT_ID'];
		$future = (($time < $details['DUE_EP']) ? 1 : 0);

		#$onchange_pmt_txt = ($editable ? "onchange=\"update_payment(this,$id);\"" : 'readonly');
		$onchange_pmt_mon = ($editable ? "onchange=\"update_payment(this,$id,'m');\"" : 'readonly');
		$onchange_pmt_sel = ($editable ? "onchange=\"update_payment(this,$id,'n');\"" : 'disabled');
		$onchange_pmt_dt = ($editable ? "onchange=\"update_payment(this,$id,'d');\"" : 'disabled');
		$onchange_pmt_pc = ($editable ? "onchange=\"update_payment(this,$id,'p');\"" : 'disabled');

		$is_adj = (($details['COL_PAYMENT_ROUTE_ID'] == $id_ROUTE_cspent) ? true : false);
		#$has_route = ((0 < $details['COL_PAYMENT_ROUTE_ID']) ? true : false);
		$clr = (($is_adj || $future) ? "style=\"color:blue;\"" : '');

		$col_percent = floatval($details['COL_PERCENT']);
		$com_gbp = 0.01 * floatval($details['COL_AMT_RX']) * floatval($details['COL_PERCENT']);
		$col_percent = round($col_percent,2);
		$col_percent = (($details['WHICH'] == 'S') ? '' :
			input_textbox('col_percent', "{$col_percent}%", 2, 10, "$style_r $onchange_pmt_pc"));
		$comm_amt = (($details['WHICH'] == 'S') ? '' :
			($com_gbp ? money_format_kdb($com_gbp, true, true, true) : '-')
		);

		if ($details['WHICH'] == 'S')
			$invoice_button = '';
		else
		{
			if (0 < $details['INV_NUM'])
			{
				$ve = ($manager_a ? "'edit'" : "'view'");
				$invoice_button = input_button($details['INV_NUM'], "view_invoice('i', {$details['INVOICE_ID']}, $ve);", "style=\"width:{$btnw}px;\"");
			}
			else
				$invoice_button = input_button('no invoice', '', " disabled style=\"width:{$btnw}px;\"");
		}

		$route = (($details['WHICH'] == 'S') ? '' :
			($editable ?
				input_select('col_payment_route_id', $payment_routes_sel, $details['COL_PAYMENT_ROUTE_ID'], $onchange_pmt_sel, false, false)
				: $details['PAYMENT_ROUTE'])
		);
		$method = (($details['WHICH'] == 'S') ? '' :
			input_select('col_payment_method_id', $payment_methods_sel, $details['COL_PAYMENT_METHOD_ID'], $onchange_pmt_sel, false, false));
		#$adjust_reason = (($details['WHICH'] == 'S') ? '' :
		#					input_select('adjustment_id', $ADJUSTMENTS, $details['ADJUSTMENT_ID'], $onchange_pmt_sel, false, false));
		$date_rx = (($details['WHICH'] == 'S') ? '' :
			input_textbox('col_dt_rx', date_for_sql($details['COL_DT_RX'], true,false, true), 6, 10, "$style_r $onchange_pmt_dt"));
		$amt_rx = (($details['WHICH'] == 'S') ? '' :
			input_textbox('col_amt_rx', money_format_kdb($details['COL_AMT_RX'], true, true, true), 4, 10, "$style_r $onchange_pmt_mon"));
		$bounced = (($details['WHICH'] == 'S') ? '' :
			input_select('col_bounced', $yn_list, $details['COL_BOUNCED'], $onchange_pmt_sel, true));
		$imported = (($details['WHICH'] == 'S') ? '' : ($details['IMPORTED'] ? "Imp." : "-"));

		if ($future)
		{
			$total_paid = '';
			$arrears = 0.0;
			$arrears_txt = '';
		}
		else
		{
			$total_paid = money_format_kdb($details['TOTAL_PAID'], true, true, true);
			$arrears = $details['TOTAL_DUE'] - $details['TOTAL_PAID'];
			if (0.0 < $arrears)
				$arrears_txt = "<span style=\"color:red;\">" . money_format_kdb($arrears, true, true, true) . "</span>";
			else
				$arrears_txt = '';
		}
		if ($tix == 0)
			$final_arrears = $arrears; # default - needed if there are no future payments
		elseif ((0 < $tix) && ($future != $prev_future))
		{
			# We have changed from future payments to current/past payments
			$final_arrears = $arrears;
			#$html_table .= "<tr id=\"today_line_c\"><td $col11><hr style=\"color:grey;\"></td></tr>
			#				$headings";
			$table_lines[] = str_replace("<tr", "<tr id=\"today_line_c\"", $headings);
			$tix++;
		}

		$due_dt = (($details['WHICH'] == 'P') ? '' : date_from_epoch(true, $details['DUE_EP'], true));
		$due_amt = (($details['WHICH'] == 'P') ? '' : money_format_kdb($details['DUE_AMT'], true, true, true));
		$id = ((0 < $id) ? $id : '');

		$table_lines[] = "
		<tr $clr>
			<td $ar>$due_dt</td>
			<td $ar>$due_amt</td>
			<td>$date_rx</td>
			<td $ar>$amt_rx</td>
			<td $ac>$route</td>
			<td $ac>$method</td>
			<td $ac>$invoice_button</td>
			" .#<td $ac>$adjust_reason</td>
			"
			<td $ar>$col_percent</td>
			<td $ar id=\"com_gbp_{$id}\">$comm_amt</td>
			<td $ac>$bounced</td>
			<td $ar>" . money_format_kdb($details['TOTAL_DUE'], true, true, true) . "</td>
			<td $ar>$total_paid</td>
			<td $ar>$arrears_txt</td>
			<td $ac>$imported</td>
			<td $ar $grey>$id</td>
		</tr>
		";
		$tix++;

//		if ($is_adj || (($details['WHICH'] != 'S') && (!$has_route)))
//		{
//			$html_table .= "
//			<tr $clr>
//				<td $col6></td>
//				<td $ar $at>Adjustment Notes:</td>
//				<td $col6>" . input_textarea('col_notes', 1, $ta_cols, $details['COL_NOTES'], "$onchange_pmt_txt style=\"height:25px\"") . "</td>
//			</tr>
//			";
//			$tix++;
//		}
		if (($details['WHICH'] != 'S') && (!$is_adj) && (!$details['COL_BOUNCED']))
		{
			$count++;
			$sum += floatval($details['COL_AMT_RX']);
		}

		$prev_future = $future;
		#$prev_arrears = $arrears_txt;
		if (($tix % 20) == 0)
			$table_lines[] = $headings;

	}# for ($cix)

	$html_table = "
	&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
	<span id=\"combined_summary\"></span>
	<div id=\"div_combined\" style=\"height:{$div_h}px; overflow-y:scroll; border:solid gray 1px;\">
	<table name=\"table_combined\" class=\"spaced_table\">
	";
	foreach ($table_lines as $one_line)
		$html_table .= $one_line;
	$html_table .= "
	</table><!--table_combined-->
	</div><!--div_combined-->

	$html_table
	";

} # print_one_job_collect_schpay()

//function hack_dates($ticked_jobs)
//{
//	# $ticked_jobs is an array of JOB_LETTER.JOB_LETTER_ID
//
//	$letter_id_list = implode(', ', $ticked_jobs);
//	#dprint("hack_dates($letter_id_list)");
//
//	$sql = "UPDATE JOB_LETTER SET JL_POSTED_DT = DATEADD(DAY,1,JL_APPROVED_DT) FROM JOB_LETTER WHERE JOB_LETTER_ID IN ($letter_id_list)";
//	dlog($sql);#
//	#sql_execute($sql); # not audited
//
//} # hack_dates()

function mass_print_letters($ticked_jobs, $upload_app=false)
{
    global $mass_print_amount;
    $mass_print_amount = 0;

	# $ticked_jobs is an array of JOB_LETTER.JOB_LETTER_ID

	global $csv_dir;
	global $job_id; # set here, used by add_collect_letter()
	global $tunnel_ftp_ip;

	$letter_id_list = implode(', ', $ticked_jobs);

	dprint("mass_print_letters($letter_id_list)");


	# --- Delete and Recreate
	# PDF Recreate 28/09/17:
	# From 28/09/17, we need to delete and recreate all the PDFs. Do this in the same way that auto_letter.php does it using add_collect_letter().
	$sql = "SELECT J.J_VILNO, J.J_SEQUENCE, L.JOB_LETTER_ID, L.JL_APPROVED_DT, L.JOB_ID, L.LETTER_TYPE_ID
				FROM JOB_LETTER AS L INNER JOIN JOB AS J ON J.JOB_ID=L.JOB_ID
				WHERE L.JOB_LETTER_ID IN ($letter_id_list)
				";
	sql_execute($sql);
	$letters = array();
	while (($newArray = sql_fetch_assoc()) != false)
	{
		$dt0 = str_replace(' ', '_', str_replace(':', '', str_replace('-', '', str_replace('.000', '', $newArray['JL_APPROVED_DT']))));
		$newArray['FILENAME'] = "$csv_dir/v{$newArray['J_VILNO']}/" .
			"letter_{$newArray['J_VILNO']}_{$newArray['J_SEQUENCE']}_{$newArray['JOB_LETTER_ID']}_{$dt0}.pdf";
		$letters[] = $newArray;
	}
	$letter_count = count($letters);
	for ($lix = 0; $lix < $letter_count; $lix++)
	{
		$job_letter_id = $letters[$lix]['JOB_LETTER_ID']; # used by add_collect_letter()
		$job_id = $letters[$lix]['JOB_ID']; # used by add_collect_letter()
		$letter_type_id = $letters[$lix]['LETTER_TYPE_ID']; # used by add_collect_letter()

		# Delete existing PDF
		if (file_exists($letters[$lix]['FILENAME']))
		{
			#dprint("Deleting \"{$letters[$lix]['FILENAME']}\"...");#
			unlink($letters[$lix]['FILENAME']);
		}

		# Recreate PDF
		add_collect_letter($letter_type_id, $job_letter_id); # also uses global $job_id

	} # for ($lix)
	# --- Delete and Recreate


	# Now start again

	$sql = "SELECT J.J_VILNO, J.J_SEQUENCE, L.JOB_LETTER_ID, L.JL_APPROVED_DT, L.JL_CREATED_DT
			FROM JOB_LETTER AS L INNER JOIN JOB AS J ON J.JOB_ID=L.JOB_ID
			WHERE L.JOB_LETTER_ID IN ($letter_id_list)
			";
	sql_execute($sql);
	$letters = array();
	$pdfs = array();
	while (($newArray = sql_fetch_assoc()) != false)
	{
		$dt1 = ($newArray['JL_CREATED_DT'] ? $newArray['JL_CREATED_DT'] : $newArray['JL_APPROVED_DT']);
		$dt2 = str_replace(' ', '_', str_replace(':', '', str_replace('-', '', str_replace('.000', '', $dt1))));
		$newArray['FILENAME'] = "$csv_dir/v{$newArray['J_VILNO']}/" .
			"letter_{$newArray['J_VILNO']}_{$newArray['J_SEQUENCE']}_{$newArray['JOB_LETTER_ID']}_{$dt2}.pdf";
		$letters[] = $newArray;
		$pdfs[] = $newArray['FILENAME'];
	}

	//FIXME - MERGE HERE

	log_open("vilcol.log");
	log_write("Begin merge");

	error_log("Starting merge");

	$date = new DateTime();
	$current_time = $date->format('Y-m-dTH-i-s');

	$file_name = "csvex/massprint/massPrint-".(string)$current_time;

	// asynchronus merging
	$pool = Pool::create();

	$count = 0;
	foreach (array_chunk($pdfs, 100) as $key=>$pdf_chunk){
		log_write("Merging ".$count);
        $mass_print_amount++;
		$pool->add(function () use ($pdf_chunk, $key, $file_name, $count){
			$merger = new Merger;

			foreach($pdf_chunk as $pdf){
				$merger->addFile($pdf);
			}

			$createdPdf = $merger->merge();

			// append count amount
			$file_name .= "-".$count."-".($count+100).".pdf";

			$myfile = fopen($file_name, "wb");
			$txt = $createdPdf;
			fwrite($myfile, $txt);
			fclose($myfile);

		})->then(function($output){
			echo ('Merge completed');
		});
		$count = $count + 100;
	}

	//	$pool->wait();

	return;

	log_write('Created merges');

	// $dprint = "Letters:<br>";
	// $letter_ix = 0;
	// foreach ($letters as $one_ltr)
	// {
	// 	$dprint .= str_replace("[FILENAME]", "<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;[FILENAME]", print_r($one_ltr,1) . "<br>");
	// 	$dprint .= "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;=={$pdfs[$letter_ix]}<br>";
	// 	$letter_ix++;
	// }
	// dprint($dprint);

//	log_write('Creating button');
//
	// Link works - was a typo in the url
//	print("
//	<span>Mass printer - merged $count letters into 1 PDF</span>
//	<form target=\"_blank\" action=\"https://vilcoldbl.com/admin/$file_name\">
//		<input type=\"submit\" value=\"Click to open\" />
//	</form>
//	");
//
//	log_write('Button made');


// //	$server_names = array('ftp.village.com', '169.0.0.5', '81.5.144.205');
// //	$ftp_server = $server_names[1];
// 	$ftp_server = $tunnel_ftp_ip; #'169.0.0.5';
// 	// $ftp_user_name = "kevin"; FIX ME - uncommented to stop ftp
// 	// $ftp_user_pass = "D0omBar#14"; - uncommented to stop ftp
// //	$ssl_ftp = false;
// 	$ftp_log_debug = false;#

// //	dlog("Connecting to \"$ftp_server\" (SSL:" . ($ssl_ftp ? 'yes' : 'no') . ") ...");
// 	dlog("Connecting to \"$ftp_server\" (SSL:yes) ...");
// //	if ($ssl_ftp)
// //		$conn_id = ftp_ssl_connect($ftp_server);
// //	else
// 		$conn_id = ftp_connect($ftp_server);
// 	if ($conn_id)
// 	{
// 		dlog("...connected, conn_id=$conn_id");

// 		dlog("Logging in as user \"$ftp_user_name\" (with password too)...");
// 		$login_result = ftp_login($conn_id, $ftp_user_name, $ftp_user_pass);
// 		if ($login_result)
// 		{
// 			dlog("...logged in, login_result=$login_result");
// //			if ($ssl_ftp)
// //			{
// //				dlog("Setting passive mode ON...");
// //				ftp_pasv($conn_id, true); # needed for SSL FTP
// //			}
// //			else
// //			{
// 				dlog("Setting passive mode OFF...");
// 				ftp_pasv($conn_id, 0);
// //			}
// 			$debug_max = 0;#
// 			$upload_count = 0;
// 			foreach ($pdfs as $one_pdf)
// 			{
// 				$uploaded = false;
// 				if ((!$debug_max) || ($upload_count < $debug_max))
// 				{
// 					$source_file = $one_pdf;
// 					$pos = strrpos($one_pdf, "/");
// 					if (($pos !== false) && ($pos < strlen($one_pdf)))
// 						$destination_file = substr($one_pdf, $pos+1);
// 					else
// 						$destination_file = $one_pdf;
// 					if ($ftp_log_debug)
// 					{
// 						$msg = "Uploading \"$source_file\" to \"$destination_file\"...";
// 						if ($upload_count < 10)
// 							dlog($msg);
// 						else
// 							dprint($msg);
// 					}
// 					$upload = ftp_put($conn_id, $destination_file, $source_file, FTP_BINARY);
// 					if ($upload)
// 					{
// 						$msg = "...uploaded $source_file OK (count=" . ($upload_count+1) . ")";
// 						$uploaded = true;
// 					}
// 					else
// 						$msg = "...upload failed";
// 					if ($ftp_log_debug)
// 					{
// 						if ($upload_count < 10)
// 							dlog($msg);
// 						else
// 							dprint($msg);
// 					}
// 				}
// 				if ($uploaded)
// 					$upload_count++;
// 			}
// 			$msg = "$upload_count PDF files have been uploaded to the Vilcol print server";
// 			log_write($msg);
// 			dprint($msg, true, 'blue');

// 			if (global_debug() && $upload_app)
// 			{
// 				log_write("Uploading new app...");
// 				$upload = ftp_put($conn_id, "app.zip", "csvex/app.zip", FTP_BINARY);
// 				if ($upload)
// 				{
// 					$msg = "...uploaded new app OK";
// 					$uploaded = true;
// 				}
// 				else
// 					$msg = "...upload of new app failed";
// 				log_write($msg);
// 			}
// 		}
// 		else
// 			dlog("...login failed");

// 		dlog("...closing connection");
// 		ftp_close($conn_id);
// 	}
// 	else
// 	{
// 		dprint("*** CONNECTION TO VILCOL FTP SERVER ($ftp_server) HAS FAILED ***", true);
// 		dlog("...connection failed");
// 	}

} # mass_print_letters())

?>
