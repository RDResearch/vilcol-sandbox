<?php

/*
 * See section 17 (17.11) of new system spec ("Vilcol New System DB Schema v1.04" Word doc)
 * If re-importing over an existing database, back up the existing database first!
 */

include_once("settings.php");
include_once("library.php");
#include_once("lib_create_tables.php");
include_once("lib_users.php");
global $denial_message;
global $navi_1_system;
global $navi_2_sys_import;
global $USER; # set by admin_verify()

$fix_list = array(90149542, 90503059, 90694238, 90700518, 90707120, 90714469, 90717737, 90723905, 90738552);
$fix_aux = array( # for records with sequence of -1
			array(-1, '54B MORAY ROAD') # input record where (($one_record[0] == -1) && (trim($one_record[8]) == '54B MORAY ROAD'))
			);

# ==================================
$laravel_do_client_2 = false;
$laravel_do_job = false;
$laravel_do_letter = false;
$laravel_do_subject = false;
# . . . . . . . . . . . . . . . . . 
#$laravel_do_subject = true;
# ==================================

log_open("import-vilcol/import_" . strftime_rdr('%Y_%m_%d_%H%M') . ".log");
log_write(post_values());#

sql_connect();

admin_verify(); # writes to $USER

if ($USER['IS_ENABLED'])
{
	if (global_debug())
	{
		$navi_1_system = true; # settings.php; used by navi_1_heading()
		$navi_2_sys_import = true; # settings.php; used by navi_2_heading()
		$onload = "onload=\"set_scroll();\"";
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
	global $fix_col_seq;
	global $letter_id;
	global $tc;
	
	print "<h3>System Administration</h3>";
	navi_2_heading(); # secondary navigation buttons
	javascript();
	print "
	<h3>Import Data from Old System</h3>
	<p>For most tables there are two old data files, one for TRA (traces) and one for COL (collections)</p>
	";
	dprint(post_values());
	
	$task = post_val('task');
	$letter_id = post_val('letter_id', true);
	$fix_col_seq = post_val('fix_col_seq', true);
	
	$tc = strtolower(post_val('tc'));
	if (($tc == 't') || ($tc == 'c') || ($tc == '*') || ($tc == ''))
	{
		if ($task == 'i_alloc')
			import_alloc();
		elseif ($task == 'i_arrange')
			import_arrange();
		elseif ($task == 'i_arrange_2')
			import_arrange_2();
		elseif ($task == 'i_billing')
			import_billing();
		elseif ($task == 'i_clients')
			import_clients();
		elseif ($task == 'i_col_letters')
			import_col_letters();
		elseif ($task == 'i_col_2015')
			import_col_2015();
		elseif ($task == 'i_feedback')
			create_feedback();
		elseif ($task == 'i_genbill')
			import_genbill();
		elseif ($task == 'i_jobs')
			import_jobs();
		elseif ($task == 'i_jobs_amt')
			import_jobs_amt();
		elseif ($task == 'i_job_reports')
			import_job_reports();
		elseif ($task == 'i_job_groups')
			set_job_groups();
		elseif ($task == 'i_job_target_dt')
			set_target_dt();
		elseif ($task == 'i_jobs_test')
			test_collect_jobs();
		elseif ($task == 'i_ledger')
			import_ledger();
		elseif ($task == 'i_letter_template')
			import_letter_templates();
		elseif ($task == 'i_payments')
			import_payments();
		elseif ($task == 'i_phones')
			import_phones();
		elseif ($task == 'i_primary')
			primary_phones();
		elseif ($task == 'i_recp')
			import_recp();
		elseif ($task == 'i_sys')
			import_sys();
		elseif ($task == 'i_tcflags')
			import_tcflags();
		elseif ($task == 'i_tdx')
			import_tdx();
		elseif ($task == 'i_trans')
			import_trans();
		elseif ($task == 'i_trans_assignment_ids')
			import_trans_assignment_ids();
		elseif ($task == 'i_users')
			import_users();
		elseif ($task == 'i_jobs_ltr_more')
			import_jobs_ltr_more();
		elseif ($task == 'i_trace_letters')
			import_trace_letters();
		elseif ($task == 'i_job_closed')
			set_job_closed();
		elseif ($task == 'i_review_dt')
			set_review_dt();
		elseif ($task == 'i_inv_paid')
			set_inv_paid();
		elseif ($task == 'i_inv_paid_2')
			set_inv_paid_method_2();
		elseif ($task == 'i_pay_percent')
			set_pay_percent();
		elseif ($task == 'i_pay_percent_adj')
			set_pay_percent_adj();
		elseif ($task == 'j_available')
			set_j_available();
		elseif ($task == 'i_inv_due_dt')
			set_inv_due_dt();
		elseif ($task == 'i_client_letter_link')
			set_client_letter_link();
		elseif ($task == 'i_letter_seq')
			import_letter_sequences();
		elseif ($task == 'fix_col_notes')
			fix_col_notes();
		elseif ($task == 'set_target_fees')
			set_target_fees();
		elseif ($task == 'dump_col_notes')
			dump_col_notes();
		elseif ($task == 'import_front_details')
			import_front_details();
		elseif ($task == 'import_next_letter')
			import_next_letter();
		elseif ($task == 'mend_letter_sequences')
			import_letter_sequences_fix();
		elseif ($task == 'i_job_percent')
			set_job_percent();
		elseif ($task == 'ftp_test')
			ftp_test();
		elseif ($task == 'postcode_outcodes')
			postcode_outcodes();
		elseif ($task == 'laravel_preprocess')
			laravel_preprocess();
		elseif ($task == 'laravel_reset')
			laravel_reset();
		elseif ($task != '')
			dprint("*=* Error Invalid task \"$task\" *=*", true);
	}
	else 
		dprint("*=* Error Invalid tc \"$tc\" *=*", true);
	
	$style = "style=\"width:300px;\"";
	print "
	<form name=\"form_main\" action=\"" . server_php_self() . "\" method=\"post\">
	" . input_hidden('task', '') . "
	" . input_hidden('tc', '') . "
	" . input_hidden('other', '') . "
	" . input_button('LARAVEL PRE-PROCESS', "laravel_preprocess()", $style) . "<br>
	" . input_button('LARAVEL RESET', "laravel_reset()", $style) . "<br>
	<br>
	" . input_button('FTP Test', "ftp_test()", $style) . "<br>
	" . input_button('SYS and SYS2 (TRA and COL)', "imp_sys()", $style) . "<br>
	" . input_button('LETTERS (TRA) and COLET (COL)', "imp_letter_templates()", $style) . "<br>
	" . input_button('USERS (TRA and COL)', "imp_users()", $style) . "<br>
	" . input_button('CLIENTS (COL only)', "imp_clients()", $style) . "<br>
	" . input_button('COLLECT 2015', "imp_col_2015()", $style) . "<br>
	" . input_button('Test Collect Jobs', "imp_test_jobs()", $style) . "<br>
	" . input_button('*DELETE* ALL JOBS', "imp_jobs('*',0)", $style) . "<br>
	" . input_button('JOBS (TRA)', "imp_jobs('t',0)", $style) . "<br>
	" . input_button('JOBS (COL)', "imp_jobs('c',0)", $style) . "<br>
	" . input_button('JOBS-INCREMENTAL (COL)', "imp_jobs('c',1)", $style) . "<br>
	" . input_button('JOBS TARGET DATE', "imp_job_target_dt('*')", $style) . "<br>
	" . input_button('JOBS -FIX FOR JC_TOTAL_AMT (COL)', "imp_jobs_amt()", $style) . "<br>
	" . input_button('JOB GROUPS', "imp_job_groups('*')", $style) . "<br>
	" . input_button('TRACE JOB REPORTS', "imp_job_reports('t')", $style) . "<br>
	" . input_button('AUTO_LS (COL)', "imp_col_letters()", $style) . "<br>
	" . input_button('PHONES (COL)', "imp_phones()", $style) . "<br>
	" . input_button('ARRANGEMENTS (COL)', "imp_arrange()", $style) . "<br>
	" . input_button('ARRANGE-2: FIX ARRNGMTS', "imp_arrange_2()", $style) . "<br>
	" . input_button('*DELETE* ALL INVOICES', "imp_ledger('*')", $style) . "<br>
	" . input_button('INVOICES/LEDGER (TRA)', "imp_ledger('t')", $style) . "<br>
	" . input_button('INVOICES/LEDGER (COL)', "imp_ledger('c')", $style) . "<br>
	" . input_button('INVOICES/GENBILL (TRA)', "imp_genbill('t')", $style) . "<br>
	" . input_button('INVOICES/GENBILL (COL)', "imp_genbill('c')", $style) . "<br>
	" . input_button('INVOICES/BILLING (TRA)', "imp_billing()", $style) . "<br>
	" . input_button('INVOICES/RECEIPTS (TRA)', "imp_recp('t')", $style) . "<br>
	" . input_button('INVOICES/RECEIPTS (COL)', "imp_recp('c')", $style) . "<br>
	" . input_button('INVOICES/R_XREF (TRA)', "imp_alloc('t')", $style) . "<br>
	" . input_button('INVOICES/R_XREF (COL)', "imp_alloc('c')", $style) . "<br>
	" . input_button('PAYMENTS (COL)', "imp_payments()", $style) . "<br>
	" . input_button('TDX CODES (COL)', "imp_tdx()", $style) . "<br>
	" . input_button('TRANSACTION IDs (COL)', "imp_trans()", $style) . "<br>
	" . input_button('CLIENT TRC/COL FLAGS', "imp_tcflags()", $style) . "<br>
	" . input_button('PRIMARY PHONE/EMAIL', "imp_primary()", $style) . "<br>
	" . input_button('JOBS -FIX FOR JC_LETTER_MORE (COL)', "imp_jobs_ltr_more()", $style) . "<br>
	" . input_button('JOBS -FIX FOR TRACE LETTER TYPES (TRA)', "imp_trace_letters()", $style) . "<br>
	" . input_button('SET JOB.JOB_CLOSED FOR ALL JOBS', "imp_job_closed()", $style) . "<br>
	" . input_button('SET JOB.JC_REVIEW_DT FOR ALL JOBS', "imp_review_dt()", $style) . "<br>
	" . input_button('SET INVOICE.INV_PAID FOR ALL INVOICES (1)', "imp_inv_paid()", $style) . "<br>
	" . input_button('SET INVOICE.INV_PAID FOR ALL INVOICES (2)', "imp_inv_paid_2()", $style) . "<br>
	" . input_button('SET PAYMENT COMM PERCENT (COL)', "imp_pay_percent()", $style) . "<br>
	" . input_button('SET J_AVAILABLE', "imp_j_available()", $style) . "<br>
	" . input_button('FEEDBACK MODULE', "imp_feedback()", $style) . "<br>
	" . input_button('Set INV_DUE_DT', "imp_inv_due_dt()", $style) . "<br>
	" . input_button('Set CLIENT_LETTER_LINK', "imp_client_letter_link()", $style) . "&nbsp;&nbsp;&nbsp;" . input_textbox('letter_id', $letter_id) . "<br>
	" . input_button('LETTER SEQUENCES (COL)', "imp_letter_seq()", $style) . "<br>
	" . input_button('Fix Collection Notes', "fix_col_notes()", $style) . "&nbsp;&nbsp;&nbsp;" . input_textbox('fix_col_seq', $fix_col_seq) . "<br>
	" . input_button('Set Adjustment COMM PERCENT (COL)', "imp_pay_percent_adj()", $style) . "<br>
	" . input_button('Set Target Fees', "set_target_fees()", $style) . "<br>
	" . input_button('Dump Collection Notes', "dump_col_notes()", $style) . "<br>
	" . input_button('Import Front-Details (TRA)', "import_front_details('t')", $style) . "<br>
	" . input_button('Import Front-Details (COL)', "import_front_details('c')", $style) . "<br>
	" . input_button('Mend Next Letter (COL)', "import_next_letter()", $style) . "<br>
	" . input_button('Mend Letter Seq (COL)', "mend_letter_sequences()", $style) . "<br>
	" . input_button('Set Job COMM PERCENT (COL)', "imp_job_percent()", $style) . "<br>
	" . input_button('TDX ASSIGNMENT IDs (COL)', "imp_trans_assignment_ids()", $style) . "<br>
	" . input_button('POSTCODE OUTCODES', "set_postcode_outcodes()", $style) . "<br>
	</form><!--form_main-->
	";
} # screen_content()

function screen_content_2()
{
	# This is required by screen_layout()
} # screen_content_2()

function javascript()
{
	print "
	<script type=\"text/javascript\">

	function laravel_preprocess()
	{
		if (confirm('Do you want to pre-process the database ready for Laravel?'))
		{
			document.form_main.task.value = 'laravel_preprocess';
			document.form_main.submit();
		}
	}

	function laravel_reset()
	{
		if (confirm('Do you want to reset the decrypted fields that were encrypted for Laravel?'))
		{
			document.form_main.task.value = 'laravel_reset';
			document.form_main.submit();
		}
	}

	function set_postcode_outcodes()
	{
		if (confirm('Do you want to set Postcode Outcodes for all subjects?'))
		{
			document.form_main.task.value = 'postcode_outcodes';
			document.form_main.submit();
		}
	}
	
	function ftp_test()
	{
		document.form_main.task.value = 'ftp_test';
		document.form_main.submit();
	}

	function mend_letter_sequences()
	{
		if (confirm('Do you want to mend \"Letter Sequences\"?'))
		{
			document.form_main.task.value = 'mend_letter_sequences';
			document.form_main.submit();
		}
	}
	
	function import_next_letter()
	{
		if (confirm('Do you want to mend \"Next Letter\" on all collection jobs?'))
		{
			document.form_main.task.value = 'import_next_letter';
			document.form_main.submit();
		}
	}
	
	function import_front_details(sys)
	{
		if (confirm('Do you want to import the \"Front Details\" of jobs (\"' + sys + '\" system)?'))
		{
			document.form_main.task.value = 'import_front_details';
			document.form_main.tc.value = sys;
			document.form_main.submit();
		}
	}
	
	function dump_col_notes()
	{
		if (confirm('Do you want to Dump Collection Notes into JOB.JC_IMP_NOTES_VMAX?'))
		{
			document.form_main.task.value = 'dump_col_notes';
			document.form_main.tc.value = '*';
			document.form_main.submit();
		}
	}
	
	function fix_col_notes()
	{
		if (confirm('Do you want to Fix Collection Notes?'))
		{
			document.form_main.task.value = 'fix_col_notes';
			document.form_main.tc.value = '*';
			document.form_main.submit();
		}
	}
	
	function set_target_fees()
	{
		if (confirm('Do you want to set the client Target Fees?'))
		{
			document.form_main.task.value = 'set_target_fees';
			document.form_main.tc.value = '*';
			document.form_main.submit();
		}
	}
	
	function imp_client_letter_link()
	{
		if (confirm('Do you want to fill the CLIENT_LETTER_LINK table?'))
		{
			document.form_main.task.value = 'i_client_letter_link';
			document.form_main.tc.value = '*';
			document.form_main.submit();
		}
	}
	
	function imp_feedback()
	{
		if (confirm('Do you want to create the FEEDBACK table (unless already created)?'))
		{
			document.form_main.task.value = 'i_feedback';
			document.form_main.tc.value = '*';
			document.form_main.submit();
		}
	}
		
	function imp_inv_due_dt()
	{
		if (confirm('Do you want to set the INV_DUE_DT for all invoices where it is 2020-01-01?'))
		{
			document.form_main.task.value = 'i_inv_due_dt';
			document.form_main.tc.value = '*';
			document.form_main.submit();
		}
	}
		
	function imp_col_2015()
	{
		if (confirm('Do you really want to import the Jan 2015 COLLECT.DBF into COLLECT_DBF_2015?'))
		{
			document.form_main.task.value = 'i_col_2015';
			document.form_main.tc.value = 'c';
			document.form_main.submit();
		}
	}
	
	function imp_j_available()
	{
		if (confirm('Do you really want to set JOB.J_AVAILABLE for all open assigned jobs?'))
		{
			document.form_main.task.value = 'j_available';
			document.form_main.tc.value = '*';
			document.form_main.submit();
		}
	}

	function imp_pay_percent()
	{
		if (confirm('Do you really want to set the zero-value collection payment commission percentages to the client percentage for system \\'' + 'C' + '\\'?'))
		{
			document.form_main.task.value = 'i_pay_percent';
			document.form_main.tc.value = 'c';
			document.form_main.submit();
		}
	}

	function imp_pay_percent_adj()
	{
		if (confirm('Do you really want to set the collection adjustment commission percentages to zero for system \\'' + 'C' + '\\'?'))
		{
			document.form_main.task.value = 'i_pay_percent_adj';
			document.form_main.tc.value = 'c';
			document.form_main.submit();
		}
	}

	function imp_job_percent()
	{
		if (confirm('Do you really want to fix the collection job commission percentages for system \\'' + 'C' + '\\'?'))
		{
			document.form_main.task.value = 'i_job_percent';
			document.form_main.tc.value = 'c';
			document.form_main.submit();
		}
	}

	function imp_letter_seq()
	{
		if (confirm('Do you really want to import Letter Sequences for system \\'' + 'C' + '\\'?'))
		{
			document.form_main.task.value = 'i_letter_seq';
			document.form_main.tc.value = 'c';
			document.form_main.submit();
		}
	}

	function imp_job_reports(tc)
	{
		if (confirm('Do you really want to re-import the Trace Job Reports from system \\'' + tc + '\\'?'))
		{
			document.form_main.task.value = 'i_job_reports';
			document.form_main.tc.value = tc;
			document.form_main.submit();
		}
	}
	
	function imp_job_groups()
	{
		if (confirm('Do you really want to create Job Groups from Job VILNos?'))
		{
			document.form_main.task.value = 'i_job_groups';
			document.form_main.tc.value = '*';
			document.form_main.submit();
		}
	}
	
	function imp_inv_paid()
	{
		if (confirm('Do you really want to set INVOICE.INV_PAID (Method #1) for all invoices, overwriting the old data?'))
		{
			document.form_main.task.value = 'i_inv_paid';
			document.form_main.tc.value = '*';
			document.form_main.submit();
		}
	}
	
	function imp_inv_paid_2()
	{
		if (confirm('Do you really want to set INVOICE.INV_PAID (Method #2) for all invoices, overwriting the old data?'))
		{
			document.form_main.task.value = 'i_inv_paid_2';
			document.form_main.tc.value = '*';
			document.form_main.submit();
		}
	}
	
	function imp_review_dt()
	{
		if (confirm('Do you really want to set JOB.JC_REVIEW_DT for all jobs, overwriting the old data?'))
		{
			document.form_main.task.value = 'i_review_dt';
			document.form_main.tc.value = '*';
			document.form_main.submit();
		}
	}
	
	function imp_job_closed()
	{
		if (confirm('Do you really want to set JOB.JOB_CLOSED for all jobs, overwriting the old data?'))
		{
			document.form_main.task.value = 'i_job_closed';
			document.form_main.tc.value = '*';
			document.form_main.submit();
		}
	}
	
	function imp_primary()
	{
		if (confirm('Do you really want to set the Primary flag for Phones and Emails, overwriting the old data?'))
		{
			document.form_main.task.value = 'i_primary';
			document.form_main.tc.value = '*';
			document.form_main.submit();
		}
	}
	
	function imp_trans()
	{
		if (confirm('Do you really want to import the Transaction ID (Additional Notes) data from system \\'C\\', overwriting the old data?'))
		{
			document.form_main.task.value = 'i_trans';
			document.form_main.tc.value = 'c';
			document.form_main.submit();
		}
	}
	
	function imp_trans_assignment_ids()
	{
		if (confirm('Do you really want to import the TDX Assignment IDs data from system \\'C\\', overwriting the old data?'))
		{
			document.form_main.task.value = 'i_trans_assignment_ids';
			document.form_main.tc.value = 'c';
			document.form_main.submit();
		}
	}
	
	function imp_tcflags()
	{
		if (confirm('Do you really want to set the Trace & Collect flags in all the Client records?'))
		{
			document.form_main.task.value = 'i_tcflags';
			document.form_main.tc.value = '*';
			document.form_main.submit();
		}
	}
	
	function imp_tdx()
	{
		if (confirm('Do you really want to import the TDX_ACT data from system \\'C\\', overwriting the old data?'))
		{
			document.form_main.task.value = 'i_tdx';
			document.form_main.tc.value = 'c';
			document.form_main.submit();
		}
	}
	
	function imp_alloc(tc)
	{
		if (confirm('Do you really want to import the INVOICE/R_XREF (Allocations) data from system \\'' + tc + '\\', overwriting the old data?'))
		{
			document.form_main.task.value = 'i_alloc';
			document.form_main.tc.value = tc;
			document.form_main.submit();
		}
	}
	
	function imp_recp(tc)
	{
		if (confirm('Do you really want to import the INVOICE/RECEIPTS data from system \\'' + tc + '\\', overwriting the old data?'))
		{
			document.form_main.task.value = 'i_recp';
			document.form_main.tc.value = tc;
			document.form_main.submit();
		}
	}
	
	function imp_test_jobs()
	{
		if (confirm('Do you really want to test the COLLECT.csv file for lines with wrong number of fields?'))
		{
			document.form_main.task.value = 'i_jobs_test';
			document.form_main.submit();
		}
	}
	
	function imp_payments()
	{
		if (confirm('Do you really want to import the PAYMENTS data from system \\'C\\', overwriting the old data?'))
		{
			document.form_main.task.value = 'i_payments';
			document.form_main.submit();
		}
	}
	
	function imp_billing()
	{
		if (confirm('Do you really want to import the INVOICE/BILLING data from system \\'T\\', overwriting the old data?'))
		{
			document.form_main.task.value = 'i_billing';
			document.form_main.submit();
		}
	}
	
	function imp_genbill(tc)
	{
		if (confirm('Do you really want to import the INVOICE/GENBILL data from system \\'' + tc + '\\', overwriting the old data?'))
		{
			document.form_main.task.value = 'i_genbill';
			document.form_main.tc.value = tc;
			document.form_main.submit();
		}
	}
	
	function imp_ledger(tc)
	{
		var msg;
		if (tc == '*')
			msg = 'Do you really want to *DELETE* all invoices (T and C)?';
		else
			msg = 'Do you really want to import the INVOICE/LEDGER data from system \\'' + tc + '\\', overwriting the old data?';
		if (confirm(msg))
		{
			document.form_main.task.value = 'i_ledger';
			document.form_main.tc.value = tc;
			document.form_main.submit();
		}
	}
	
	function imp_sys()
	{
		if (confirm('Do you really want to import the SYS/SYS2 data, overwriting the old data?'))
		{
			document.form_main.task.value = 'i_sys';
			document.form_main.submit();
		}
	}
	
	function imp_letter_templates()
	{
		if (confirm('Do you really want to import the LETTERS/COLET templates, overwriting the old data?'))
		{
			document.form_main.task.value = 'i_letter_template';
			document.form_main.submit();
		}
	}
	
	function imp_col_letters()
	{
		if (confirm('Do you really want to import the AUTO_LS Collection Letters, overwriting the old data?'))
		{
			document.form_main.task.value = 'i_col_letters';
			document.form_main.submit();
		}
	}
	
	function imp_phones()
	{
		if (confirm('Do you really want to import the PHONES Collection numbers, overwriting the old data?'))
		{
			document.form_main.task.value = 'i_phones';
			document.form_main.submit();
		}
	}
	
	function imp_arrange()
	{
		if (confirm('Do you really want to import the ARRANGE Collection arrangements, overwriting the old data?'))
		{
			document.form_main.task.value = 'i_arrange';
			document.form_main.submit();
		}
	}
	
	function imp_arrange_2()
	{
		if (confirm('Do you really want to fix the Collection arrangements, (\"Arrange-2\")?'))
		{
			document.form_main.task.value = 'i_arrange_2';
			document.form_main.submit();
		}
	}
	
	function imp_users()
	{
		if (confirm('Do you really want to recreate the USERS data, overwriting the old data?'))
		{
			document.form_main.task.value = 'i_users';
			document.form_main.submit();
		}
	}
	
	function imp_clients()
	{
		if (confirm('Do you really want to import the CLIENTS data, overwriting the old data?'))
		{
			document.form_main.task.value = 'i_clients';
			document.form_main.submit();
		}
	}
	
	function imp_job_target_dt()
	{
		var msg;
		msg = 'Do you really want to set JOB.J_TARGET_DT to 30 days from the received date?';
		if (confirm(msg))
		{
			document.form_main.task.value = 'i_job_target_dt';
			document.form_main.tc.value = '*';
			document.form_main.submit();
		}
	}
	
	function imp_jobs_amt()
	{
		var msg;
		msg = 'Do you really want to import the JC_TOTAL_AMT into JOBS data from system \\'' + 'C' + '\\', overwriting the old data?';
		if (confirm(msg))
		{
			document.form_main.task.value = 'i_jobs_amt';
			document.form_main.tc.value = 'c';
			document.form_main.submit();
		}
	}
	
	function imp_jobs_ltr_more()
	{
		var msg;
		msg = 'Do you really want to import the JC_LETTER_MORE into JOBS data from system \\'' + 'C' + '\\', overwriting the old data?';
		if (confirm(msg))
		{
			document.form_main.task.value = 'i_jobs_ltr_more';
			document.form_main.tc.value = 'c';
			document.form_main.submit();
		}
	}
	
	function imp_trace_letters()
	{
		var msg;
		msg = 'Do you really want to update the LETTER_TYPE_ID for Trace JOBS data from system \\'' + 'T' + '\\', overwriting the old data?';
		if (confirm(msg))
		{
			document.form_main.task.value = 'i_trace_letters';
			document.form_main.tc.value = 't';
			document.form_main.submit();
		}
	}
	
	function imp_jobs(tc,incr)
	{
		var msg;
		if ((incr != 0) && (tc != 'c'))
		{
			alert('imp_jobs('+tc+','+incr+') - illegal args');
			return false;
		}
		if (tc == '*')
			msg = 'Do you really want to *DELETE* all jobs (T and C)?';
		else
			msg = 'Do you really want to import the JOBS data from system \\'' + tc + '\\', overwriting the old data?';
		if (confirm(msg))
		{
			document.form_main.task.value = 'i_jobs';
			document.form_main.tc.value = tc;
			document.form_main.other.value = incr;
			document.form_main.submit();
		}
	}
	
	</script>
	";
}

function csvfield($fieldname, $is_date='', $return_if_error=false, $other_marker='')
{
	# Used by import_clients() and import_jobs() etc.
	global $c_code; # from_import_clients()
	global $col_nums;
	global $csvfield_error;
	global $input_count; # from caller e.g. import_jobs()
	global $j_sequence; # from import_jobs()
	global $one_record;

	$value = '';
	$csvfield_error = '';
	if ($fieldname)
	{
		if (array_key_exists($fieldname, $col_nums))
		{
			$or_ix = $col_nums[$fieldname];
			if (array_key_exists($or_ix, $one_record))
				$value = trim($one_record[$or_ix]);
			else
				$csvfield_error = "*=* ERROR csvfield($fieldname, $is_date): \$or_ix ($or_ix) not a key in \$one_record " .
									"(\$return_if_error=\"$return_if_error\", \$other_marker=\"$other_marker\"), " .
									"\$col_nums=" . print_r($col_nums,1) . ", \$one_record=" . print_r($one_record,1) . ", \$j_sequence=$j_sequence";
		}
		else
			$csvfield_error = "*=* ERROR csvfield($fieldname, $is_date): \"$fieldname\" not a key in col_nums " .
									"(\$return_if_error=\"$return_if_error\", \$other_marker=\"$other_marker\"), " .
									"\$col_nums=" . print_r($col_nums,1) . ", \$j_sequence=$j_sequence";
	}
	else
		$csvfield_error = "*=* ERROR csvfield($fieldname, $is_date): no fieldname (\$return_if_error=\"$return_if_error\", \$other_marker=\"$other_marker\"), " .
							"\$j_sequence=$j_sequence";
	
	if ((!$csvfield_error) && $value && $is_date)
	{
		# If USA date, $value is m/d/yyyy. Convert to yyyy-mm-dd.
		# Or could be UK date already (ARRANGE.DBF).
		$bits = explode('/', $value);
		if (count($bits) >= 3)
		{
			if ($is_date == 'us')
				$value = $bits[2] . '-' . substr("0{$bits[0]}", -2) . '-' . substr("0{$bits[1]}", -2);
			elseif ($is_date == 'uk')
				$value = $bits[2] . '-' . substr("0{$bits[1]}", -2) . '-' . substr("0{$bits[0]}", -2);
			else
				$csvfield_error = "*=* ERROR is_date=\"$is_date\" (other_marker=\"$other_marker\")";
		}
		else
		{
			if ($return_if_error)
				$csvfield_error = "*=* ERROR csvfield($fieldname, $is_date): \"$value\" is not a date (other_marker=\"$other_marker\")";
			$value = '';
		}
	}
	if ($csvfield_error)
	{
		$csvfield_error .= " [input_count=$input_count] (other_marker=\"$other_marker\")";
		if ($c_code)
			$csvfield_error .= " [c_code=$c_code] (other_marker=\"$other_marker\")";
		if ($j_sequence)
			$csvfield_error .= " [j_sequence=$j_sequence] (other_marker=\"$other_marker\")";
		dprint($csvfield_error, true);
		log_write($csvfield_error);
		if ($return_if_error)
			$value = '';
		else
			exit();
	}
	return $value;
}

function import_sys()
{
	# Required:
	#	t/SYS.DBF
	#	t/SYS2.DBF
	#	c/SYS.DBF
	#	c/SYS2.DBF

	# Import 31/12/16:
	# import_2016_12_31_1007.log
	# 2016-12-31 10:07:29/   POST=Array
	#	(
	#		[task] => i_sys
	#		[tc] => 
	#		[other] => 
	#	)
	
	$enabled = false; #
	if (!$enabled)
	{
		dprint("This import function is disabled", true);
		return;
	}
	
	print "<h3>Importing SYS and SYS2 (both systems)</h3>";
	
	init_sys();
	
	# Lines for 't' and 'c' (traces and collect). Each has '1' and '2' (SYS.DBF and SYS2.DBF).
	$lines = array('t' => array('1' => array(), '2' => array()), 'c' => array('1' => array(), '2' => array()));
	
	# Import Traces 't' SYS.DBF then SYS2.DBF; then import Collect 'c' SYS.DBF then SYS2.DBF.
	# Warning: the 't' files have different fields and different size fields to the 'c' files!!
	
	$sys = 't'; # traces
	$while_safety = 0; # to avoid infinite-looping
	while (true)
	{
		$dirname = "import-vilcol/{$sys}";
		
		$dbf = "SYS.DBF";
		$fx = 1; # SYS.DBF
		$fp_dbf = fopen("$dirname/$dbf", 'r');
		if ($fp_dbf)
		{
			$readlen = 50000;
			$buffer = fread($fp_dbf, $readlen); # read whole file in one go
			
			# Get column names
			$off = 32; # first column name is 32 bytes into the file
			$reclen = 32; # each column name is 32 bytes after the previous one
			$pos = $off; # position in buffer
			while ($pos <= $readlen)
			{
				if (substr($buffer, $pos, 1) == chr(13)) # Hex 0D - this is end of column names
				{
					#dprint("End of column names");
					break;
				}
				$col = substr($buffer, $pos, $reclen);
				$endpos = strpos($col, chr(0));
				if ($endpos !== false)
					$col = substr($col, 0, $endpos);
				else 
				{
					dprint("*=* No zero found in \"$col\" (pos=$pos)");
					return;
				}
				#dprint("COL=\"$col\"");
				if ($col)
					$lines[$sys][$fx][$col] = '';
				$pos += $reclen;
			}
			#dprint("Columns = " . print_r($lines[$sys][$fx],1));
			
			# Get field values
			$pos += 3; # first field value is 3 bytes after end of last column name (2 bytes after hex 0D)
			if ($pos <= $readlen)
			{
				$fname = 'VILNO';
				$reclen = (($sys == 't') ? 6 : 8); # VILNO
				$fvalue = trim(substr($buffer, $pos, $reclen));
				$pos += $reclen;
				$lines[$sys][$fx][$fname] = $fvalue;
			}
			if ($pos <= $readlen)
			{
				$fname = 'SEQUENCE';
				$reclen = 8;
				$fvalue = trim(substr($buffer, $pos, $reclen));
				$pos += $reclen;
				$lines[$sys][$fx][$fname] = $fvalue;
			}
			if ($pos <= $readlen)
			{
				$fname = 'INVNO';
				$reclen = 6;
				$fvalue = trim(substr($buffer, $pos, $reclen));
				$pos += $reclen;
				$lines[$sys][$fx][$fname] = $fvalue;
			}
			if ($pos <= $readlen)
			{
				$fname = 'CREDNO';
				$reclen = 6;
				$fvalue = trim(substr($buffer, $pos, $reclen));
				$pos += $reclen;
				$lines[$sys][$fx][$fname] = $fvalue;
			}
			if ($pos <= $readlen)
			{
				$fname = 'VATRATE';
				$reclen = 10;
				$fvalue = trim(substr($buffer, $pos, $reclen));
				$pos += $reclen;
				$lines[$sys][$fx][$fname] = $fvalue;
			}
			if ($pos <= $readlen)
			{
				$fname = 'VATNO';
				$reclen = 12;
				$fvalue = trim(substr($buffer, $pos, $reclen));
				$pos += $reclen;
				$lines[$sys][$fx][$fname] = $fvalue;
			}
			if ($pos <= $readlen)
			{
				$fname = 'BOLD_ON';
				$reclen = 10;
				$fvalue = trim(substr($buffer, $pos, $reclen));
				$pos += $reclen;
				$lines[$sys][$fx][$fname] = $fvalue;
			}
			if ($pos <= $readlen)
			{
				$fname = 'BOLD_OFF';
				$reclen = 10;
				$fvalue = trim(substr($buffer, $pos, $reclen));
				$pos += $reclen;
				$lines[$sys][$fx][$fname] = $fvalue;
			}
			if ($pos <= $readlen)
			{
				$fname = 'UPPER_TRAY';
				$reclen = 10;
				$fvalue = trim(substr($buffer, $pos, $reclen));
				$pos += $reclen;
				$lines[$sys][$fx][$fname] = $fvalue;
			}
			if ($pos <= $readlen)
			{
				$fname = 'LOWER_TRAY';
				$reclen = 10;
				$fvalue = trim(substr($buffer, $pos, $reclen));
				$pos += $reclen;
				$lines[$sys][$fx][$fname] = $fvalue;
			}
			if ($pos <= $readlen)
			{
				$fname = 'LO_MIN';
				$reclen = 6;
				$fvalue = trim(substr($buffer, $pos, $reclen));
				$pos += $reclen;
				$lines[$sys][$fx][$fname] = $fvalue;
			}
			if ($pos <= $readlen)
			{
				$fname = 'COM_PORT';
				$reclen = 2;
				$fvalue = trim(substr($buffer, $pos, $reclen));
				$pos += $reclen;
				$lines[$sys][$fx][$fname] = $fvalue;
			}
			if ($pos <= $readlen)
			{
				$fname = 'BAUD_RATE';
				$reclen = 8;
				$fvalue = trim(substr($buffer, $pos, $reclen));
				$pos += $reclen;
				$lines[$sys][$fx][$fname] = $fvalue;
			}
			if ($pos <= $readlen)
			{
				$fname = 'PDF_LEFT';
				$reclen = 8;
				$fvalue = trim(substr($buffer, $pos, $reclen));
				$pos += $reclen;
				$lines[$sys][$fx][$fname] = $fvalue;
			}
			if ($pos <= $readlen)
			{
				$fname = 'PDF_TOP';
				$reclen = 8;
				$fvalue = trim(substr($buffer, $pos, $reclen));
				$pos += $reclen;
				$lines[$sys][$fx][$fname] = $fvalue;
			}
			if ($pos <= $readlen)
			{
				$fname = 'COMP_ON';
				$reclen = 10;
				$fvalue = trim(substr($buffer, $pos, $reclen));
				$pos += $reclen;
				$lines[$sys][$fx][$fname] = $fvalue;
			}
			if ($pos <= $readlen)
			{
				$fname = 'COMP_OFF';
				$reclen = 10;
				$fvalue = trim(substr($buffer, $pos, $reclen));
				$pos += $reclen;
				$lines[$sys][$fx][$fname] = $fvalue;
			}
			if ($pos <= $readlen)
			{
				$fname = 'AP_DEDUCT';
				$reclen = 10;
				$fvalue = trim(substr($buffer, $pos, $reclen));
				$pos += $reclen;
				$lines[$sys][$fx][$fname] = $fvalue;
			}
			if ($pos <= $readlen)
			{
				$fname = 'NO_TRACE';
				$reclen = 3;
				$fvalue = trim(substr($buffer, $pos, $reclen));
				$pos += $reclen;
				$lines[$sys][$fx][$fname] = $fvalue;
			}
			if ($pos <= $readlen)
			{
				$fname = 'EMAIL_FROM';
				$reclen = 50;
				$fvalue = trim(substr($buffer, $pos, $reclen));
				$pos += $reclen;
				$lines[$sys][$fx][$fname] = $fvalue;
			}
			if ($pos <= $readlen)
			{
				$fname = 'OUT_USER';
				$reclen = 50;
				$fvalue = trim(substr($buffer, $pos, $reclen));
				$pos += $reclen;
				$lines[$sys][$fx][$fname] = $fvalue;
			}
			if ($pos <= $readlen)
			{
				$fname = 'OUT_PW';
				$reclen = 50;
				$fvalue = trim(substr($buffer, $pos, $reclen));
				$pos += $reclen;
				$lines[$sys][$fx][$fname] = $fvalue;
			}
			if ($pos <= $readlen)
			{
				$fname = 'NAME_FROM';
				$reclen = 50;
				$fvalue = trim(substr($buffer, $pos, $reclen));
				$pos += $reclen;
				$lines[$sys][$fx][$fname] = $fvalue;
			}
			if ($pos <= $readlen)
			{
				$fname = 'OUT_SERV';
				$reclen = 50;
				$fvalue = trim(substr($buffer, $pos, $reclen));
				$pos += $reclen;
				$lines[$sys][$fx][$fname] = $fvalue;
			}
			if ($pos <= $readlen)
			{
				$fname = 'OUT_PORT';
				$reclen = 10;
				$fvalue = trim(substr($buffer, $pos, $reclen));
				$pos += $reclen;
				$lines[$sys][$fx][$fname] = $fvalue;
			}
			if ($pos <= $readlen)
			{
				$fname = 'PWORD';
				$reclen = 12;
				$fvalue = trim(substr($buffer, $pos, $reclen));
				$pos += $reclen;
				$lines[$sys][$fx][$fname] = $fvalue;
			}
			if ($pos <= $readlen)
			{
				$fname = 'PWORD2';
				$reclen = 12;
				$fvalue = trim(substr($buffer, $pos, $reclen));
				$pos += $reclen;
				$lines[$sys][$fx][$fname] = $fvalue;
			}
			if ($pos <= $readlen)
			{
				$fname = 'CLFDATE';
				$reclen = 8;
				$fvalue = trim(substr($buffer, $pos, $reclen));
				$pos += $reclen;
				$lines[$sys][$fx][$fname] = $fvalue;
			}
			if ($pos <= $readlen)
			{
				$fname = 'CLTDATE';
				$reclen = 8;
				$fvalue = trim(substr($buffer, $pos, $reclen));
				$pos += $reclen;
				$lines[$sys][$fx][$fname] = $fvalue;
			}
			if ($pos <= $readlen)
			{
				$fname = 'CLETDATE';
				$reclen = 8;
				$fvalue = trim(substr($buffer, $pos, $reclen));
				$pos += $reclen;
				$lines[$sys][$fx][$fname] = $fvalue;
			}
			if ($pos <= $readlen)
			{
				$fname = 'CLLT';
				$reclen = 8;
				$fvalue = trim(substr($buffer, $pos, $reclen));
				$pos += $reclen;
				$lines[$sys][$fx][$fname] = $fvalue;
			}
			if ($pos <= $readlen)
			{
				$fname = 'NEXT_REC';
				$reclen = 8;
				$fvalue = trim(substr($buffer, $pos, $reclen));
				$pos += $reclen;
				$lines[$sys][$fx][$fname] = $fvalue;
			}
			if ($sys == 'c')
			{
				if ($pos <= $readlen)
				{
					$fname = 'NEXT_EMI';
					$reclen = 8;
					$fvalue = trim(substr($buffer, $pos, $reclen));
					$pos += $reclen;
					$lines[$sys][$fx][$fname] = $fvalue;
				}
			}
			dprint("Lines from $sys/$dbf = " . print_r($lines[$sys][$fx],1));
			fclose($fp_dbf);
		}
		else
			dlog("*=* Failed to fopen(\"$dirname/$dbf\",'r')");

		$dbf = "SYS2.DBF";
		$fx = 2; # SYS2.DBF
		$fp_dbf = fopen("$dirname/$dbf", 'r');
		if ($fp_dbf)
		{
			$readlen = 50000;
			$buffer = fread($fp_dbf, $readlen); # read whole file in one go
			
			# Get column names
			$off = 32; # first column name is 32 bytes into the file
			$reclen = 32; # each column name is 32 bytes after the previous one
			$pos = $off; # position in buffer
			while ($pos <= $readlen)
			{
				if (substr($buffer, $pos, 1) == chr(13)) # Hex 0D - this is end of column names
				{
					#dprint("End of column names");
					break;
				}
				$col = substr($buffer, $pos, $reclen);
				$endpos = strpos($col, chr(0));
				if ($endpos !== false)
					$col = substr($col, 0, $endpos);
				else 
				{
					dprint("*=* No zero found in \"$col\" (pos=$pos)");
					return;
				}
				#dprint("COL=\"$col\"");
				if ($col)
					$lines[$sys][$fx][$col] = '';
				$pos += $reclen;
			}
			#dprint("Columns = " . print_r($lines[$sys][$fx],1));
			
			# Get field values
			$pos += 2; # first field value is 2 bytes after end of last column name (2 bytes after hex 0D)
			if ($pos <= $readlen)
			{
				$fname = 'NEXTUSER';
				$reclen = 3;
				$fvalue = trim(substr($buffer, $pos, $reclen));
				$pos += $reclen;
				$lines[$sys][$fx][$fname] = $fvalue;
			}
			dprint("Lines from $sys/$dbf = " . print_r($lines[$sys][$fx],1));
			fclose($fp_dbf);
		}
		else
			dlog("*=* Failed to fopen(\"$dirname/$dbf\",'r')");

		if ($sys == 't')
			$sys = 'c';
		else 
			break; # from while (true)
			
		$while_safety++;
		if ($while_safety > 100000) # 100,000 clients
			break; # from while (true)
			
	} # while (true)
	
	# Now update MISC_INFO
	
	$value = $lines['c'][1]['VILNO'];
	misc_info_write('c', 'VILNO_C', 'int', $value, '', false); # don't audit
	
	$value = $lines['t'][1]['VILNO'];
	misc_info_write('t', 'VILNO_T', 'int', $value, '', false); # don't audit
	
	$value = $lines['c'][1]['SEQUENCE'];
	misc_info_write('c', 'SEQ_C', 'int', $value, '', false); # don't audit
	
	$value = $lines['t'][1]['SEQUENCE'];
	misc_info_write('t', 'SEQ_T', 'int', $value, '', false); # don't audit
	
	$value = $lines['c'][1]['INVNO'];
	misc_info_write('c', 'INVNO_C', 'int', $value, '', false); # don't audit
	
	$value = $lines['t'][1]['INVNO'];
	misc_info_write('t', 'INVNO_T', 'int', $value, '', false); # don't audit
	
	$value = $lines['c'][1]['CREDNO'];
	misc_info_write('c', 'CREDNO_C', 'int', $value, '', false); # don't audit
	
	$value = $lines['t'][1]['CREDNO'];
	misc_info_write('t', 'CREDNO_T', 'int', $value, '', false); # don't audit
	
	$value = $lines['c'][1]['VATRATE'];
	$value_x = $lines['t'][1]['VATRATE'];
	if ($value == $value_x)
		misc_info_write('*', 'VAT_RATE', 'dec', $value, '', false); # don't audit
	else 
	{
		dprint("*=*Different values for VATRATE \"$value\" and \"$value_x\"");
		return;
	}
	
	$value = $lines['c'][1]['VATNO'];
	$value_x = $lines['t'][1]['VATNO'];
	if ($value == $value_x)
		misc_info_write('*', 'VAT_NUM', 'txt', $value, '', false); # don't audit
	else 
	{
		dprint("*=* Different values for VATNO \"$value\" and \"$value_x\"");
		return;
	}
	
	foreach ($lines['t'][1] as $key => $value)
	{
		if (($key != 'VILNO') && ($key != 'SEQUENCE') && ($key != 'INVNO') && ($key != 'CREDNO') && 
			($key != 'VATRATE') && ($key != 'VATNO'))
			misc_info_write('t', "{$key}_T", 'txt', $value, 'Imported from Trace/SYS.DBF', false);
	}
	
	foreach ($lines['c'][1] as $key => $value)
	{
		if (($key != 'VILNO') && ($key != 'SEQUENCE') && ($key != 'INVNO') && ($key != 'CREDNO') && 
			($key != 'VATRATE') && ($key != 'VATNO'))
			misc_info_write('c', "{$key}_C", 'txt', $value, 'Imported from Collect/SYS.DBF', false);
	}
	
	foreach ($lines['t'][2] as $key => $value)
	{
		if (($key != 'VILNO') && ($key != 'SEQUENCE') && ($key != 'INVNO') && ($key != 'CREDNO') && 
			($key != 'VATRATE') && ($key != 'VATNO'))
			misc_info_write('t', "{$key}_T", 'txt', $value, 'Imported from Trace/SYS2.DBF', false);
	}
	
	foreach ($lines['c'][2] as $key => $value)
	{
		if (($key != 'VILNO') && ($key != 'SEQUENCE') && ($key != 'INVNO') && ($key != 'CREDNO') && 
			($key != 'VATRATE') && ($key != 'VATNO'))
			misc_info_write('c', "{$key}_C", 'txt', $value, 'Imported from Collect/SYS2.DBF', false);
	}
	
} # import_sys()

function init_ledger($tc)
{
	# If $tc == '*', delete and recreate invoice tables.
	# But, it is not possible to just delete the records for the system t or c.
	
	# --------------------------------------------------------------------------------------

	if ($tc == '*')
	{
		# --- JOB_PAYMENT -----------------------------------------------------------------------------------

		init_payments(true);

		
		# --- INV_ALLOC -----------------------------------------------------------------------------------

		if (sql_table_exists('INV_ALLOC'))
		{
			$sql = "DROP TABLE INV_ALLOC";
			dprint($sql);
			sql_execute($sql); # no need to audit
		}

		$sql = "CREATE TABLE [dbo].[INV_ALLOC](
					[INV_ALLOC_ID] [int] IDENTITY(1,1) NOT NULL,
					[INVOICE_ID] [int] NOT NULL,
					[INV_RECP_ID] [int] NOT NULL,
					[AL_AMOUNT] [decimal](10, 2) NOT NULL,
					[IMPORTED] [bit] NOT NULL,
					[AL_SYS_IMP] [varchar](1) NULL,
					[Z_DOCTYPE] [varchar](1) NULL,
					[Z_DOCAMOUNT] [decimal](10, 2) NULL,
				 CONSTRAINT [PK_INV_ALLOC] PRIMARY KEY CLUSTERED 
				(
					[INV_ALLOC_ID] ASC
				)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, 
				ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON) ON [PRIMARY]
				) ON [PRIMARY]
			";
		dprint((strlen($sql) > 100) ? (substr($sql, 0, 100) . "...") : $sql);
		sql_execute($sql); # no need to audit

		$sql = "ALTER TABLE [dbo].[INV_ALLOC] ADD  CONSTRAINT [DF_INV_ALLOC_IMPORTED]  DEFAULT ((0)) FOR [IMPORTED]
			";
		dprint((strlen($sql) > 100) ? (substr($sql, 0, 100) . "...") : $sql);
		sql_execute($sql); # no need to audit

		$sql = "CREATE NONCLUSTERED INDEX [IX_INVOICE_ID] ON [dbo].[INV_ALLOC]
				(
					[INVOICE_ID] ASC
				)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, SORT_IN_TEMPDB = OFF, 
				DROP_EXISTING = OFF, ONLINE = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON)
			";
		dprint((strlen($sql) > 100) ? (substr($sql, 0, 100) . "...") : $sql);
		sql_execute($sql); # no need to audit

		$sql = "CREATE NONCLUSTERED INDEX [IX_INV_RECP_ID] ON [dbo].[INV_ALLOC]
				(
					[INV_RECP_ID] ASC
				)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, SORT_IN_TEMPDB = OFF, 
				DROP_EXISTING = OFF, ONLINE = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON)
			";
		dprint((strlen($sql) > 100) ? (substr($sql, 0, 100) . "...") : $sql);
		sql_execute($sql); # no need to audit

		
		# --- INV_BILLING -----------------------------------------------------------------------------------

		if (sql_table_exists('INV_BILLING'))
		{
			$sql = "DROP TABLE INV_BILLING";
			dprint($sql);
			sql_execute($sql); # no need to audit
		}

		$sql = "CREATE TABLE [dbo].[INV_BILLING](
					[INV_BILLING_ID] [int] IDENTITY(1,1) NOT NULL,
					[INVOICE_ID] [int] NULL,
					[INV_NUM] [int] NULL,
					[JOB_ID] [int] NULL,
					[BL_SYS] [varchar](1) NULL,
					[BL_SYS_IMP] [varchar](1) NULL,
					[BL_DESCR] [varchar](1000) NULL,
					[BL_COST] [decimal](10, 2) NULL,
					[BL_LPOS] [int] NULL,
					[BL_LETTER_DT] [datetime] NULL,
					[IMPORTED] [bit] NOT NULL,
					[OBSOLETE] [bit] NOT NULL CONSTRAINT [DF_INV_BILLING_OBSOLETE]  DEFAULT ((0)),
					[Z_CLID] [int] NULL,
					[Z_DOCTYPE] [varchar](1) NULL,
					[Z_S_INVS] [varchar](3) NULL,
					[Z_NSECS] [int] NULL,
				 CONSTRAINT [PK_INV_BILLING] PRIMARY KEY CLUSTERED 
				(
					[INV_BILLING_ID] ASC
				)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, 
				ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON) ON [PRIMARY]
				) ON [PRIMARY]
			";
		dprint((strlen($sql) > 100) ? (substr($sql, 0, 100) . "...") : $sql);
		sql_execute($sql); # no need to audit

		$sql = "ALTER TABLE [dbo].[INV_BILLING] ADD  CONSTRAINT [DF_INV_BILLING_IMPORTED]  DEFAULT ((0)) FOR [IMPORTED]
			";
		dprint((strlen($sql) > 100) ? (substr($sql, 0, 100) . "...") : $sql);
		sql_execute($sql); # no need to audit

		$sql = "CREATE NONCLUSTERED INDEX [IX_IB_INVOICE_ID] ON [dbo].[INV_BILLING]
				(
					[INVOICE_ID] ASC
				)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, SORT_IN_TEMPDB = OFF, 
				DROP_EXISTING = OFF, ONLINE = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON)
			";
		dprint((strlen($sql) > 100) ? (substr($sql, 0, 100) . "...") : $sql);
		sql_execute($sql); # no need to audit

		$sql = "CREATE NONCLUSTERED INDEX [IX_IB_JOB_ID] ON [dbo].[INV_BILLING]
				(
					[JOB_ID] ASC
				)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, SORT_IN_TEMPDB = OFF, 
				DROP_EXISTING = OFF, ONLINE = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON)
			";
		dprint((strlen($sql) > 100) ? (substr($sql, 0, 100) . "...") : $sql);
		sql_execute($sql); # no need to audit

		$sql = "CREATE NONCLUSTERED INDEX [IX_INV_NUM] ON [dbo].[INV_BILLING]
				(
					[INV_NUM] ASC
				)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, SORT_IN_TEMPDB = OFF, 
				DROP_EXISTING = OFF, ONLINE = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON)
			";
		dprint((strlen($sql) > 100) ? (substr($sql, 0, 100) . "...") : $sql);
		sql_execute($sql); # no need to audit

		 
		# --- INV_RECP -----------------------------------------------------------------------------------

		if (sql_table_exists('INV_RECP'))
		{
			$sql = "DROP TABLE INV_RECP";
			dprint($sql);
			sql_execute($sql); # no need to audit
		}

		$sql = "CREATE TABLE [dbo].[INV_RECP](
					[INV_RECP_ID] [int] IDENTITY(1,1) NOT NULL,
					[RC_NUM] [int] NOT NULL,
					[CLIENT2_ID] [int] NOT NULL,
					[RC_DT] [datetime] NULL,
					[RC_AMOUNT] [decimal](10, 2) NOT NULL,
					[RC_ADJUST] [bit] NOT NULL,
					[RC_NOTES] [varbinary](2000) NULL,
					[IMPORTED] [bit] NOT NULL,
					[RC_SYS_IMP] [varchar](1) NULL,
					[OBSOLETE] [bit] NOT NULL,
					[Z_COMPLETE] [varchar](1) NULL,
				 CONSTRAINT [PK_INV_RECP] PRIMARY KEY CLUSTERED 
				(
					[INV_RECP_ID] ASC
				)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, 
				ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON) ON [PRIMARY]
				) ON [PRIMARY]
			";
		dprint((strlen($sql) > 100) ? (substr($sql, 0, 100) . "...") : $sql);
		sql_execute($sql); # no need to audit

		$sql = "ALTER TABLE [dbo].[INV_RECP] ADD  CONSTRAINT [DF_INV_RECP_RC_ADJUST]  DEFAULT ((0)) FOR [RC_ADJUST]
			";
		dprint((strlen($sql) > 100) ? (substr($sql, 0, 100) . "...") : $sql);
		sql_execute($sql); # no need to audit

		$sql = "ALTER TABLE [dbo].[INV_RECP] ADD  CONSTRAINT [DF_INV_RECP_IMPORTED]  DEFAULT ((0)) FOR [IMPORTED]
			";
		dprint((strlen($sql) > 100) ? (substr($sql, 0, 100) . "...") : $sql);
		sql_execute($sql); # no need to audit

		$sql = "ALTER TABLE [dbo].[INV_RECP] ADD  CONSTRAINT [DF_INV_RECP_OBSOLETE]  DEFAULT ((0)) FOR [OBSOLETE]
			";
		dprint((strlen($sql) > 100) ? (substr($sql, 0, 100) . "...") : $sql);
		sql_execute($sql); # no need to audit

		$sql = "CREATE NONCLUSTERED INDEX [IX_CLIENT2_ID] ON [dbo].[INV_RECP]
				(
					[CLIENT2_ID] ASC
				)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, SORT_IN_TEMPDB = OFF, 
				DROP_EXISTING = OFF, ONLINE = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON)
			";
		dprint((strlen($sql) > 100) ? (substr($sql, 0, 100) . "...") : $sql);
		sql_execute($sql); # no need to audit

		$sql = "CREATE NONCLUSTERED INDEX [IX_RC_NUM] ON [dbo].[INV_RECP]
				(
					[RC_NUM] ASC
				)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, SORT_IN_TEMPDB = OFF, 
				DROP_EXISTING = OFF, ONLINE = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON)
			";
		dprint((strlen($sql) > 100) ? (substr($sql, 0, 100) . "...") : $sql);
		sql_execute($sql); # no need to audit

		
		# --- INVOICE -----------------------------------------------------------------------------------
	
		if (sql_table_exists('INVOICE'))
		{
			$sql = "DROP TABLE INVOICE";
			dprint($sql);
			sql_execute($sql); # no need to audit
		}
		
		$sql = "CREATE TABLE [dbo].[INVOICE](
					[INVOICE_ID] [int] IDENTITY(1,1) NOT NULL,
					[INV_NUM] [int] NOT NULL,
					[INV_SYS] [varchar](1) NOT NULL,
					[INV_SYS_IMP] [varchar](1) NULL,
					[INV_TYPE] [varchar](1) NOT NULL,
					[INV_DT] [datetime] NOT NULL,
					[INV_DUE_DT] [datetime] NULL,
					[CLIENT2_ID] [int] NOT NULL,
					[INV_NET] [decimal](10, 2) NOT NULL,
					[INV_VAT] [decimal](10, 2) NOT NULL,
					[INV_RX] [decimal](10, 2) NULL,
					[INV_PAID] [decimal](10, 2) NOT NULL,
					[INV_STMT] [bit] NOT NULL,
					[INV_S_INVS] [varchar](1) NULL,
					[INV_START_DT] [datetime] NULL,
					[INV_END_DT] [datetime] NULL,
					[INV_EMAIL_ID] [int] NULL,
					[INV_POSTED_DT] [datetime] NULL,
					[INV_COMPLETE] [bit] NOT NULL,
					[INV_APPROVED_DT] [datetime] NULL,
					[INV_NOTES] [varbinary](2000) NULL,
					[LINKED_ID] [int] NULL,
					[OBSOLETE] [bit] NOT NULL,
					[IMPORTED] [bit] NOT NULL,
					[Z_EOM] [varchar](5) NULL,
					CONSTRAINT [PK_INVOICE] PRIMARY KEY CLUSTERED 
						(
							[INVOICE_ID] ASC
						)WITH (PAD_INDEX  = OFF, STATISTICS_NORECOMPUTE  = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS  = ON, ALLOW_PAGE_LOCKS  = ON) ON [PRIMARY]
				) ON [PRIMARY]
			";
		dprint((strlen($sql) > 100) ? (substr($sql, 0, 100) . "...") : $sql);
		sql_execute($sql); # no need to audit

		$sql = "ALTER TABLE [dbo].[INVOICE] ADD  CONSTRAINT [DF_INVOICE_INV_NET]  DEFAULT ((0)) FOR [INV_NET]
			";
		dprint((strlen($sql) > 100) ? (substr($sql, 0, 100) . "...") : $sql);
		sql_execute($sql); # no need to audit

		$sql = "ALTER TABLE [dbo].[INVOICE] ADD  CONSTRAINT [DF_INVOICE_INV_VAT]  DEFAULT ((0)) FOR [INV_VAT]
			";
		dprint((strlen($sql) > 100) ? (substr($sql, 0, 100) . "...") : $sql);
		sql_execute($sql); # no need to audit

		$sql = "ALTER TABLE [dbo].[INVOICE] ADD  CONSTRAINT [DF_INVOICE_INV_PAID]  DEFAULT ((0)) FOR [INV_PAID]
			";
		dprint((strlen($sql) > 100) ? (substr($sql, 0, 100) . "...") : $sql);
		sql_execute($sql); # no need to audit

		$sql = "ALTER TABLE [dbo].[INVOICE] ADD  CONSTRAINT [DF_INVOICE_INV_STMT]  DEFAULT ((0)) FOR [INV_STMT]
			";
		dprint((strlen($sql) > 100) ? (substr($sql, 0, 100) . "...") : $sql);
		sql_execute($sql); # no need to audit

		$sql = "ALTER TABLE [dbo].[INVOICE] ADD  CONSTRAINT [DF_INVOICE_INV_COMPLETE]  DEFAULT ((0)) FOR [INV_COMPLETE]
			";
		dprint((strlen($sql) > 100) ? (substr($sql, 0, 100) . "...") : $sql);
		sql_execute($sql); # no need to audit

		$sql = "ALTER TABLE [dbo].[INVOICE] ADD  CONSTRAINT [DF_INVOICE_OBSOLETE]  DEFAULT ((0)) FOR [OBSOLETE]
			";
		dprint((strlen($sql) > 100) ? (substr($sql, 0, 100) . "...") : $sql);
		sql_execute($sql); # no need to audit

		$sql = "ALTER TABLE [dbo].[INVOICE] ADD  CONSTRAINT [DF_INVOICE_IMPORTED]  DEFAULT ((0)) FOR [IMPORTED]
			";
		dprint((strlen($sql) > 100) ? (substr($sql, 0, 100) . "...") : $sql);
		sql_execute($sql); # no need to audit

		$sql = "CREATE NONCLUSTERED INDEX [IX_INV_NUM] ON [dbo].[INVOICE] 
				(
					[INV_NUM] ASC
				)WITH (PAD_INDEX  = OFF, STATISTICS_NORECOMPUTE  = OFF, SORT_IN_TEMPDB = OFF, IGNORE_DUP_KEY = OFF, DROP_EXISTING = OFF, ONLINE = OFF, ALLOW_ROW_LOCKS  = ON, ALLOW_PAGE_LOCKS  = ON) ON [PRIMARY]
			";
		dprint((strlen($sql) > 100) ? (substr($sql, 0, 100) . "...") : $sql);
		sql_execute($sql); # no need to audit

		$sql = "CREATE NONCLUSTERED INDEX [IX_INV_DT] ON [dbo].[INVOICE] 
				(
					[INV_DT] ASC
				)WITH (PAD_INDEX  = OFF, STATISTICS_NORECOMPUTE  = OFF, SORT_IN_TEMPDB = OFF, IGNORE_DUP_KEY = OFF, DROP_EXISTING = OFF, ONLINE = OFF, ALLOW_ROW_LOCKS  = ON, ALLOW_PAGE_LOCKS  = ON) ON [PRIMARY]
			";
		dprint((strlen($sql) > 100) ? (substr($sql, 0, 100) . "...") : $sql);
		sql_execute($sql); # no need to audit

		$sql = "CREATE NONCLUSTERED INDEX [IX_CLIENT2_ID] ON [dbo].[INVOICE]
				(
					[CLIENT2_ID] ASC
				)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, SORT_IN_TEMPDB = OFF, DROP_EXISTING = OFF, ONLINE = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON)
			";
		dprint((strlen($sql) > 100) ? (substr($sql, 0, 100) . "...") : $sql);
		sql_execute($sql); # no need to audit

		$sql = "CREATE NONCLUSTERED INDEX [IX_LINKED_ID] ON [dbo].[INVOICE]
				(
					[LINKED_ID] ASC
				)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, SORT_IN_TEMPDB = OFF, DROP_EXISTING = OFF, ONLINE = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON)
			";
		dprint((strlen($sql) > 100) ? (substr($sql, 0, 100) . "...") : $sql);
		sql_execute($sql); # no need to audit
	}
	else
		dprint("Error: init_ledger() called without star: \"$tc\"");
	
} # init_ledger()

function init_genbill($tc, $bl_sys)
{
	$sql = "DELETE FROM INV_BILLING WHERE (BL_SYS='$bl_sys') AND (BL_SYS_IMP='" . strtoupper($tc) . "')";
	dprint((strlen($sql) > 100) ? (substr($sql, 0, 100) . "...") : $sql);
	sql_execute($sql); # no need to audit
	
} # init_genbill()

function init_recp($tc)
{
	$sql = "DELETE FROM INV_RECP WHERE (RC_SYS_IMP='" . strtoupper($tc) . "')";
	dprint((strlen($sql) > 100) ? (substr($sql, 0, 100) . "...") : $sql);
	sql_execute($sql); # no need to audit
	
} # init_recp()

function init_billing()
{
	$sql = "SELECT COUNT(*) FROM INV_BILLING WHERE (BL_SYS='T') AND (BL_SYS_IMP='T')";
	sql_execute($sql);
	$count = 0;
	while (($newArray = sql_fetch()) != false)
		$count = $newArray[0];
	
	$sql = "DELETE FROM INV_BILLING WHERE (BL_SYS='T') AND (BL_SYS_IMP='T')";
	dprint(((strlen($sql) > 100) ? (substr($sql, 0, 100) . "...") : $sql) . " (probably $count records)");
	sql_execute($sql); # no need to audit
	
} # init_billing()

function init_jobs($tc)
{
	# If $tc == '*', delete and recreate job tables.
	# Otherwise, just delete the records for the system t or c.

	global $sqlTrue;
	global $V_Text_Size; # settings.php
	
	# --------------------------------------------------------------------------------------

	if ($tc == '*')
	{
		# --- JOB_ARRANGE -----------------------------------------------------------------------------------
	
		if (sql_table_exists('JOB_ARRANGE'))
		{
			$sql = "DROP TABLE JOB_ARRANGE";
			dprint($sql);
			sql_execute($sql); # no need to audit
		}
		
		$sql = "
			CREATE TABLE [dbo].[JOB_ARRANGE](
				[JOB_ARRANGE_ID] [int] IDENTITY(1,1) NOT NULL,
				[JOB_ID] [int] NOT NULL,
				[JA_DT] [datetime] NULL,
				[JA_INSTAL_FREQ] [varchar](1) NULL,
				[JA_INSTAL_AMT] [decimal](19, 4) NULL,
				[JA_INSTAL_DT_1] [datetime] NULL,
				[JA_PAYMENT_METHOD_ID] [int] NULL,
				[JA_METHOD_NUMBER] [varbinary](256) NULL,
				[JA_TOTAL] [decimal](19, 4) NULL,
				[IMPORTED] [bit] NOT NULL,
				[Z_DEPDATE] [datetime] NULL,
				[Z_DEPAMOUNT] [decimal](12, 2) NULL,
				[Z_REMAMOUNT] [decimal](12, 2) NULL,
			 CONSTRAINT [PK_JOB_ARRANGE] PRIMARY KEY CLUSTERED 
			(
				[JOB_ARRANGE_ID] ASC
			)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, 
			ALLOW_PAGE_LOCKS = ON) ON [PRIMARY]
			) ON [PRIMARY]
			";
		dprint((strlen($sql) > 100) ? (substr($sql, 0, 100) . "...") : $sql);
		sql_execute($sql); # no need to audit
		
		$sql = "CREATE NONCLUSTERED INDEX [IX_JOB_ID] ON [dbo].[JOB_ARRANGE]
				(
					[JOB_ID] ASC
				)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, SORT_IN_TEMPDB = OFF, DROP_EXISTING = OFF, 
				ONLINE = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON)
			";
		dprint((strlen($sql) > 100) ? (substr($sql, 0, 100) . "...") : $sql);
		sql_execute($sql); # no need to audit
		

		# --- ADDRESS_HISTORY -----------------------------------------------------------------------------------
	
		if (sql_table_exists('ADDRESS_HISTORY'))
		{
			$sql = "DROP TABLE ADDRESS_HISTORY";
			dprint($sql);
			sql_execute($sql); # no need to audit
		}
		
		$sql = "
			CREATE TABLE [dbo].[ADDRESS_HISTORY](
				[ADDRESS_HISTORY_ID] [int] IDENTITY(1,1) NOT NULL,
				[JOB_SUBJECT_ID] [int] NOT NULL,
				[AD_FROM_DT] [datetime] NOT NULL,
				[AD_TO_DT] [datetime] NOT NULL,
				[ADDR_1] [varbinary](100) NULL,
				[ADDR_2] [varbinary](100) NULL,
				[ADDR_3] [varbinary](100) NULL,
				[ADDR_4] [varbinary](100) NULL,
				[ADDR_5] [varbinary](100) NULL,
				[ADDR_PC] [varbinary](100) NULL,
				[AD_NOTES] [varbinary](1000) NULL,
			 CONSTRAINT [PK_ADDRESS_HISTORY] PRIMARY KEY CLUSTERED 
				(
					[ADDRESS_HISTORY_ID] ASC
				)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON) ON [PRIMARY]
			) ON [PRIMARY]
			";
		dprint((strlen($sql) > 100) ? (substr($sql, 0, 100) . "...") : $sql);
		sql_execute($sql); # no need to audit
		
		$sql = "CREATE NONCLUSTERED INDEX [IX_SUBJECT] ON [dbo].[ADDRESS_HISTORY]
					(
						[JOB_SUBJECT_ID] ASC
					)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, SORT_IN_TEMPDB = OFF, DROP_EXISTING = OFF, ONLINE = OFF, 
					ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON)
			";
		dprint((strlen($sql) > 100) ? (substr($sql, 0, 100) . "...") : $sql);
		sql_execute($sql); # no need to audit
		
		# --- JOB_PHONE -----------------------------------------------------------------------------------
	
		if (sql_table_exists('JOB_PHONE'))
		{
			$sql = "DROP TABLE JOB_PHONE";
			dprint($sql);
			sql_execute($sql); # no need to audit
		}
		
		$sql = "
			CREATE TABLE [dbo].[JOB_PHONE](
				[JOB_PHONE_ID] [int] IDENTITY(1,1) NOT NULL,
				[JOB_ID] [int] NOT NULL,
				[JOB_SUBJECT_ID] [int] NULL,
				[JP_PHONE] [varbinary](256) NULL,
				[JP_PRIMARY_P] [bit] NOT NULL CONSTRAINT [DF_JOB_PHONE_JP_PRIMARY_P]  DEFAULT ((0)),
				[JP_EMAIL] [varbinary](256) NULL,
				[JP_PRIMARY_E] [bit] NOT NULL CONSTRAINT [DF_JOB_PHONE_JP_PRIMARY_E]  DEFAULT ((0)),
				[JP_DESCR] [varbinary](256) NULL,
				[IMPORTED] [bit] NOT NULL,
				[IMP_PH] [bit] NOT NULL,
				[OBSOLETE] [bit] NOT NULL,
			 CONSTRAINT [PK_JOB_PHONE] PRIMARY KEY CLUSTERED 
			(
				[JOB_PHONE_ID] ASC
			)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON) ON [PRIMARY]
			) ON [PRIMARY]
			";
		dprint((strlen($sql) > 100) ? (substr($sql, 0, 100) . "...") : $sql);
		sql_execute($sql); # no need to audit
		
		$sql = "ALTER TABLE [dbo].[JOB_PHONE] ADD  CONSTRAINT [DF_JOB_PHONE_OBSOLETE]  DEFAULT ((0)) FOR [OBSOLETE]
			";
		dprint((strlen($sql) > 100) ? (substr($sql, 0, 100) . "...") : $sql);
		sql_execute($sql); # no need to audit
		
		$sql = "CREATE NONCLUSTERED INDEX [IX_JOB_ID] ON [dbo].[JOB_PHONE]
			(
				[JOB_ID] ASC
			)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, SORT_IN_TEMPDB = OFF, DROP_EXISTING = OFF, ONLINE = OFF, 
			ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON)
			";
		dprint((strlen($sql) > 100) ? (substr($sql, 0, 100) . "...") : $sql);
		sql_execute($sql); # no need to audit
		
		$sql = "CREATE NONCLUSTERED INDEX [IX_SUBJECT_ID] ON [dbo].[JOB_PHONE]
			(
				[JOB_SUBJECT_ID] ASC
			)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, SORT_IN_TEMPDB = OFF, DROP_EXISTING = OFF, ONLINE = OFF, 
			ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON)
			";
		dprint((strlen($sql) > 100) ? (substr($sql, 0, 100) . "...") : $sql);
		sql_execute($sql); # no need to audit

		
		# --- JOB_ACT -----------------------------------------------------------------------------------

		init_job_act_table();

		
		# --- JOB_NOTE -----------------------------------------------------------------------------------
	
		if (sql_table_exists('JOB_NOTE'))
		{
			$sql = "DROP TABLE JOB_NOTE";
			dprint($sql);
			sql_execute($sql); # no need to audit
		}
				
		$sql = "
			CREATE TABLE [dbo].[JOB_NOTE](
				[JOB_NOTE_ID] [int] IDENTITY(1,1) NOT NULL,
				[JOB_ID] [int] NOT NULL,
				[J_NOTE] [varbinary](6000) NOT NULL,
				[IMPORTED] [bit] NOT NULL,
				[IMP_2] [bit] NULL,
				[JN_ADDED_ID] [int] NOT NULL,
				[JN_ADDED_DT] [datetime] NOT NULL,
				[JN_UPDATED_ID] [int] NULL,
				[JN_UPDATED_DT] [datetime] NULL,
			 CONSTRAINT [PK_JOB_NOTE] PRIMARY KEY CLUSTERED 
			(
				[JOB_NOTE_ID] ASC
			)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, 
			ALLOW_PAGE_LOCKS = ON) ON [PRIMARY]
			) ON [PRIMARY]
			";
		dprint((strlen($sql) > 100) ? (substr($sql, 0, 100) . "...") : $sql);
		sql_execute($sql); # no need to audit
		
		$sql = "CREATE NONCLUSTERED INDEX [IX_JOB_ID] ON [dbo].[JOB_NOTE]
			(
				[JOB_ID] ASC
			)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, SORT_IN_TEMPDB = OFF, DROP_EXISTING = OFF, ONLINE = OFF, 
			ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON)
			";
		dprint((strlen($sql) > 100) ? (substr($sql, 0, 100) . "...") : $sql);
		sql_execute($sql); # no need to audit
		

		# --- JOB_LETTER -----------------------------------------------------------------------------------
	
		if (sql_table_exists('JOB_LETTER'))
		{
			$sql = "DROP TABLE JOB_LETTER";
			dprint($sql);
			sql_execute($sql); # no need to audit
		}
				
		$sql = "
			CREATE TABLE [dbo].[JOB_LETTER](
				[JOB_LETTER_ID] [int] IDENTITY(1,1) NOT NULL,
				[JOB_ID] [int] NOT NULL,
				[LETTER_TYPE_ID] [int] NULL,
				[JL_ADDED_DT] [datetime] NULL,
				[JL_APPROVED_DT] [datetime] NULL,
				[JL_CREATED_DT] [datetime] NULL,
				[JL_EMAIL_ID] [int] NULL,
				[JL_EMAIL_RESENDS] [varchar](2000) NULL,
				[JL_POSTED_DT] [datetime] NULL,
				[JL_TEXT] [varbinary]($V_Text_Size) NULL,
				[JL_TEXT_2] [varbinary]($V_Text_Size) NULL,
				[JL_UPDATED_ID] [int] NULL,
				[JL_UPDATED_DT] [datetime] NULL,
				[JL_AL] [bit] NOT NULL,
				[IMPORTED] [bit] NOT NULL,
				[OBSOLETE] [bit] NOT NULL CONSTRAINT [DF_JOB_LETTER_OBSOLETE]  DEFAULT ((0)),
			 CONSTRAINT [PK_JOB_LETTER] PRIMARY KEY CLUSTERED 
			(
				[JOB_LETTER_ID] ASC
			)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, 
			ALLOW_PAGE_LOCKS = ON) ON [PRIMARY]
			) ON [PRIMARY]
			";
		dprint((strlen($sql) > 100) ? (substr($sql, 0, 100) . "...") : $sql);
		sql_execute($sql); # no need to audit
		
		$sql = "
			CREATE NONCLUSTERED INDEX [IX_LETTER_JOB_ID] ON [dbo].[JOB_LETTER]
			(
				[JOB_ID] ASC
			)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, SORT_IN_TEMPDB = OFF, DROP_EXISTING = OFF, ONLINE = OFF, 
			ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON)
			";
		dprint((strlen($sql) > 100) ? (substr($sql, 0, 100) . "...") : $sql);
		sql_execute($sql); # no need to audit
		
		$sql = "
			ALTER TABLE [dbo].[JOB_LETTER] ADD  CONSTRAINT [DF_JOB_LETTER_JL_AL]  DEFAULT ((0)) FOR [JL_AL]
			";
		dprint((strlen($sql) > 100) ? (substr($sql, 0, 100) . "...") : $sql);
		sql_execute($sql); # no need to audit
		
		
		# --- JOB_SUBJECT -----------------------------------------------------------------------------------
		
		if (sql_table_exists('JOB_SUBJECT'))
		{
			$sql = "DROP TABLE JOB_SUBJECT";
			dprint($sql);
			sql_execute($sql); # no need to audit
		}
				
		$sql = "
			CREATE TABLE [dbo].[JOB_SUBJECT](
				[JOB_SUBJECT_ID] [int] IDENTITY(1,1) NOT NULL,
				[JOB_ID] [int] NOT NULL,
				[JS_PRIMARY] [bit] NOT NULL,
				[JS_TITLE] [varchar](10) NULL,
				[JS_FIRSTNAME] [varbinary](256) NULL,
				[JS_LASTNAME] [varbinary](256) NOT NULL,
				[JS_COMPANY] [varbinary](256) NULL,
				[JS_DOB] [datetime] NULL,
				[JS_ADDR_1] [varbinary](256) NULL,
				[JS_ADDR_2] [varbinary](256) NULL,
				[JS_ADDR_3] [varbinary](256) NULL,
				[JS_ADDR_4] [varbinary](256) NULL,
				[JS_ADDR_5] [varbinary](256) NULL,
				[JS_ADDR_PC] [varbinary](256) NULL,
				[JS_OUTCODE] [varchar](10) NULL,
				[JS_BANK_NAME] [varbinary](256) NULL,
				[JS_BANK_SORTCODE] [varbinary](256) NULL,
				[JS_BANK_ACC_NUM] [varbinary](256) NULL,
				[JS_BANK_ACC_NAME] [varbinary](256) NULL,
				[JS_BANK_COUNTRY] [varchar](100) NULL,
				[NEW_ADDR_1] [varbinary](256) NULL,
				[NEW_ADDR_2] [varbinary](256) NULL,
				[NEW_ADDR_3] [varbinary](256) NULL,
				[NEW_ADDR_4] [varbinary](256) NULL,
				[NEW_ADDR_5] [varbinary](256) NULL,
				[NEW_ADDR_PC] [varbinary](256) NULL,
				[NEW_OUTCODE] [varchar](10) NULL,
				[IMPORTED] [bit] NOT NULL,
				[OBSOLETE] [bit] NOT NULL,
			 CONSTRAINT [PK_JOB_SUBJECT] PRIMARY KEY CLUSTERED 
			(
				[JOB_SUBJECT_ID] ASC
			)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, 
			ALLOW_PAGE_LOCKS = ON) ON [PRIMARY]
			) ON [PRIMARY]
			";
		dprint((strlen($sql) > 100) ? (substr($sql, 0, 100) . "...") : $sql);
		sql_execute($sql); # no need to audit
	
		$sql = "ALTER TABLE [dbo].[JOB_SUBJECT] ADD  CONSTRAINT [DF_JOB_SUBJECT_OBSOLETE]  DEFAULT ((0)) FOR [OBSOLETE]
			";
		dprint((strlen($sql) > 100) ? (substr($sql, 0, 100) . "...") : $sql);
		sql_execute($sql); # no need to audit
		
		$sql = "
			CREATE NONCLUSTERED INDEX [IX_JOB_ID] ON [dbo].[JOB_SUBJECT]
			(
				[JOB_ID] ASC
			)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, SORT_IN_TEMPDB = OFF, DROP_EXISTING = OFF, ONLINE = OFF, 
			ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON)
			";
		dprint((strlen($sql) > 100) ? (substr($sql, 0, 100) . "...") : $sql);
		sql_execute($sql); # no need to audit
	
		$sql = "
			CREATE NONCLUSTERED INDEX [IX_LASTNAME] ON [dbo].[JOB_SUBJECT]
			(
				[JS_LASTNAME] ASC
			)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, SORT_IN_TEMPDB = OFF, DROP_EXISTING = OFF, ONLINE = OFF, 
			ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON)
			";
		dprint((strlen($sql) > 100) ? (substr($sql, 0, 100) . "...") : $sql);
		sql_execute($sql); # no need to audit
		
		
		# --- JOB_Z -----------------------------------------------------------------------------------
		
		if (sql_table_exists('JOB_Z'))
		{
			$sql = "DROP TABLE JOB_Z";
			dprint($sql);
			sql_execute($sql); # no need to audit
		}
				
		$sql = "
			CREATE TABLE [dbo].[JOB_Z](
				[JOB_ID] [int] NOT NULL,
				[Z_C_AMOUNTOUT] [decimal](12, 2) NULL,
				[Z_X_AUTO] [varchar](3) NULL,
				[Z_C_COLDATE] [datetime] NULL,
				[Z_C_CONTLET] [int] NULL,
				[Z_C_DEMLET] [int] NULL,
				[Z_C_L_CONTLET] [datetime] NULL,
				[Z_C_L_DEMLET] [datetime] NULL,
				[Z_C_LCOLDATE] [datetime] NULL,
				[Z_C_LCRDATE] [datetime] NULL,
				[Z_C_LET1] [varchar](3) NULL,
				[Z_C_LET1DATE] [datetime] NULL,
				[Z_C_LET2] [varchar](3) NULL,
				[Z_C_LET2DATE] [datetime] NULL,
				[Z_C_LET3] [varchar](3) NULL,
				[Z_C_LET3DATE] [datetime] NULL,
				[Z_C_LETPEND] [varchar](8) NULL,
				[Z_C_NUMCOL] [int] NULL,
				[Z_C_PASTPAYCD] [datetime] NULL,
				[Z_T_LET1] [varchar](3) NULL,
				[Z_T_DATE1] [datetime] NULL,
				[Z_T_LET2] [varchar](3) NULL,
				[Z_T_DATE2] [datetime] NULL,
				[Z_T_LET3] [varchar](3) NULL,
				[Z_T_DATE3] [datetime] NULL,
				[Z_T_LET4] [varchar](3) NULL,
				[Z_T_DATE4] [datetime] NULL,
				[Z_T_LET5] [varchar](3) NULL,
				[Z_T_DATE5] [datetime] NULL,
				[Z_T_LET6] [varchar](3) NULL,
				[Z_T_DATE6] [datetime] NULL,
				[Z_T_LET7] [varchar](3) NULL,
				[Z_T_DATE7] [datetime] NULL,
				[Z_T_LET8] [varchar](3) NULL,
				[Z_T_DATE8] [datetime] NULL,
				[Z_T_LET9] [varchar](3) NULL,
				[Z_T_DATE9] [datetime] NULL,
				[Z_T_LET10] [varchar](3) NULL,
				[Z_T_DATE10] [datetime] NULL,
				[Z_T_LET11] [varchar](3) NULL,
				[Z_T_DATE11] [datetime] NULL,
				[Z_T_LET12] [varchar](3) NULL,
				[Z_T_DATE12] [datetime] NULL,
				[Z_T_LETDATE] [datetime] NULL,
				[Z_T_REP1] [varchar](66) NULL,
				[Z_T_REP2] [varchar](66) NULL,
				[Z_T_REP3] [varchar](66) NULL,
				[Z_T_REP4] [varchar](66) NULL,
				[Z_T_REP5] [varchar](66) NULL,
				[Z_T_REP6] [varchar](66) NULL,
				[Z_T_REP7] [varchar](66) NULL,
				[Z_T_REP8] [varchar](66) NULL,
				[Z_T_REP9] [varchar](66) NULL,
				[Z_T_REP10] [varchar](66) NULL,
				[Z_T_REP11] [varchar](66) NULL,
			 CONSTRAINT [IX_JOB_ID] UNIQUE NONCLUSTERED 
			(
				[JOB_ID] ASC
			)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, 
			ALLOW_PAGE_LOCKS = ON) ON [PRIMARY]
			) ON [PRIMARY]
			";
		dprint((strlen($sql) > 100) ? (substr($sql, 0, 100) . "...") : $sql);
		sql_execute($sql); # no need to audit

		
		# --- JOB ------------------------------
		
		if (sql_table_exists('JOB'))
		{
			$sql = "DROP TABLE JOB";
			dprint($sql);
			sql_execute($sql); # no need to audit
		}
				
		$sql = "
			CREATE TABLE [dbo].[JOB](
				[JOB_ID] [int] IDENTITY(1,1) NOT NULL,
				[CLIENT2_ID] [int] NOT NULL,
				[CLIENT_REF] [varbinary](256) NULL,
				[JOB_CLOSED] [bit] NOT NULL CONSTRAINT [DF_JOB_JOB_CLOSED]  DEFAULT ((0)),
				[J_COMPLETE] [int] NULL,
				[J_VILNO] [int] NOT NULL,
				[J_SEQUENCE] [int] NOT NULL,
				[J_INHOUSE] [bit] NOT NULL,
				[J_USER_ID] [int] NULL,
				[J_USER_DT] [datetime] NULL,
				[J_OPENED_DT] [datetime] NULL,
				[J_UPDATED_DT] [datetime] NULL,
				[J_CLOSED_DT] [datetime] NULL,
				[J_CLOSED_ID] [int] NULL,
				[J_DIARY_DT] [datetime] NULL,
				[J_DIARY_TXT] [varbinary](1000) NULL,
				[J_TURN_H] [int] NULL,
				[J_TURN_D] [int] NULL,
				[J_TARGET_DT] [datetime] NULL,
				[J_REFERRER] [varbinary](1000) NULL,
				[J_S_INVS] [bit] NOT NULL,
				[JOB_GROUP_ID] [int] NULL,
				[J_AVAILABLE] [bit] NOT NULL CONSTRAINT [DF_JOB_J_AVAILABLE]  DEFAULT ((0)),
				[J_FRONT_DETAILS] [varchar](200) NULL,
				[J_BULK] [bit] NOT NULL CONSTRAINT [DF_JOB_J_BULK]  DEFAULT ((0)),
				[IMPORTED] [bit] NOT NULL,
				[VS_2015] [bit] NOT NULL CONSTRAINT [DF_JOB_VS_2015]  DEFAULT ((0)),
				[J_ARCHIVED] [bit] NOT NULL,
				[OBSOLETE] [bit] NOT NULL CONSTRAINT [DF_JOB_OBSOLETE]  DEFAULT ((0)),
				[JT_JOB] [bit] NOT NULL,
				[JT_SUCCESS] [int] NULL,
				[JT_CREDIT] [int] NULL,
				[JT_JOB_TYPE_ID] [int] NULL,
				[JT_JOB_TARGET_ID] [int] NULL,
				[JT_FEE_Y] [decimal](12, 2) NULL,
				[JT_FEE_N] [decimal](12, 2) NULL,
				[JT_TM_T_FEE] [decimal](12, 2) NULL,
				[JT_TM_T_COMP] [bit] NOT NULL CONSTRAINT [DF_JOB_JT_TM_T_COMP]  DEFAULT ((0)),
				[JT_TM_M_COMP] [bit] NOT NULL CONSTRAINT [DF_JOB_JT_TM_M_COMP]  DEFAULT ((0)),
				[JT_BACK_DT] [datetime] NULL,
				[JT_PROPERTY] [int] NULL,
				[JT_AMOUNT] [decimal](12, 2) NULL,
				[JT_LET_REPORT] [varbinary](6000) NULL,
				[JT_REPORT_APPR] [datetime] NULL,
				[JT_LET_PEND] [bit] NOT NULL,
				[JT_LET_PRINT] [bit] NOT NULL,
				[JC_JOB] [bit] NOT NULL,
				[JC_TC_JOB] [bit] NOT NULL,
				[JC_JOB_STATUS_ID] [int] NULL,
				[JC_PERCENT] [decimal](12, 3) NULL,
				[JC_TOTAL_AMT] [decimal](19, 4) NULL,
				[JC_PAYMENT_METHOD_ID] [int] NULL,
				[JC_INSTAL_FREQ] [varchar](1) NULL,
				[JC_INSTAL_AMT] [decimal](19, 4) NULL,
				[JC_INSTAL_DT_1] [datetime] NULL,
				[JC_PAID_SO_FAR] [decimal](12, 2) NULL,
				[JC_PAID_IN_FULL] [bit] NOT NULL,
				[JC_ADJUSTMENT] [decimal](12, 2) NULL,
				[JC_ADJ_OPTION] [varchar](1) NULL,
				[JC_ADJ_TEXT] [varchar](1000) NULL,
				[JC_REVIEW_DT] [datetime] NULL,
				[JC_REVIEW_D_OLD] [int] NULL,
				[JC_LETTER_MORE] [bit] NOT NULL,
				[JC_LETTER_TYPE_ID] [int] NULL,
				[JC_LETTER_DELAY] [int] NULL,
				[JC_IN_PROGRESS_CU1] [bit] NOT NULL,
				[JC_SUBJ_PAID_CU2] [bit] NOT NULL,
				[JC_PAID_DT_CU2] [datetime] NULL,
				[JC_PAID_AMT_CU2] [decimal](12, 2) NULL,
				[JC_PDCHEQUES_CU3] [bit] NOT NULL,
				[JC_PDC_TXT_CU3] [varchar](500) NULL,
				[JC_SUBJ_CONT_CU4] [bit] NOT NULL,
				[JC_AGREED_CU5] [bit] NOT NULL,
				[JC_AGR_TXT_CU5] [varchar](500) NULL,
				[JC_NEW_ADR_CU6] [bit] NOT NULL,
				[JC_ADDR_1_CU6] [varbinary](256) NULL,
				[JC_ADDR_2_CU6] [varbinary](256) NULL,
				[JC_ADDR_3_CU6] [varbinary](256) NULL,
				[JC_ADDR_4_CU6] [varbinary](256) NULL,
				[JC_ADDR_5_CU6] [varbinary](256) NULL,
				[JC_ADDR_PC_CU6] [varbinary](256) NULL,
				[JC_NO_ADDR_CU7] [bit] NOT NULL,
				[JC_FAIL_PROM_CU8] [bit] NOT NULL,
				[JC_NOT_RESP_CU9] [bit] NOT NULL,
				[JC_ADD_NOTES_CU10] [bit] NOT NULL,
				[JC_AN1_CU10] [varbinary](256) NULL,
				[JC_AN2_CU10] [varbinary](256) NULL,
				[JC_AN3_CU10] [varbinary](256) NULL,
				[JC_MIN_SETT] [decimal](12, 2) NULL,
				[JC_IR_ADDR_1] [varbinary](256) NULL,
				[JC_IR_ADDR_2] [varbinary](256) NULL,
				[JC_IR_ADDR_3] [varbinary](256) NULL,
				[JC_IR_ADDR_4] [varbinary](256) NULL,
				[JC_IR_ADDR_5] [varbinary](256) NULL,
				[JC_IR_ADDR_PC] [varbinary](256) NULL,
				[JC_REASON_2] [varbinary](6000) NULL,
				[JC_TRANS_ID] [varchar](100) NULL,
				[JC_TRANS_CNUM] [varchar](100) NULL,
				[JC_IMP_NOTES_VMAX] [nvarchar](max) NULL,
			 CONSTRAINT [PK_JOB] PRIMARY KEY CLUSTERED 
			(
				[JOB_ID] ASC
			)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, 
			ALLOW_PAGE_LOCKS = ON) ON [PRIMARY]
			) ON [PRIMARY]
			";
		dprint((strlen($sql) > 100) ? (substr($sql, 0, 100) . "...") : $sql);
		sql_execute($sql); # no need to audit
		
		$sql = "ALTER TABLE [dbo].[JOB] ADD  CONSTRAINT [DF_JOB_J_INHOUSE]  DEFAULT ((0)) FOR [J_INHOUSE]
			";
		dprint((strlen($sql) > 100) ? (substr($sql, 0, 100) . "...") : $sql);
		sql_execute($sql); # no need to audit
		
		$sql = "ALTER TABLE [dbo].[JOB] ADD  CONSTRAINT [DF_JOB_J_ARCHIVED]  DEFAULT ((0)) FOR [J_ARCHIVED]
			";
		dprint((strlen($sql) > 100) ? (substr($sql, 0, 100) . "...") : $sql);
		sql_execute($sql); # no need to audit
		
		$sql = "ALTER TABLE [dbo].[JOB] ADD  CONSTRAINT [DF_JOB_JT_LET_PEND]  DEFAULT ((0)) FOR [JT_LET_PEND]
			";
		dprint((strlen($sql) > 100) ? (substr($sql, 0, 100) . "...") : $sql);
		sql_execute($sql); # no need to audit
		
		$sql = "ALTER TABLE [dbo].[JOB] ADD  CONSTRAINT [DF_JOB_JT_LET_PRINT]  DEFAULT ((0)) FOR [JT_LET_PRINT]
			";
		dprint((strlen($sql) > 100) ? (substr($sql, 0, 100) . "...") : $sql);
		sql_execute($sql); # no need to audit
		
		$sql = "ALTER TABLE [dbo].[JOB] ADD  CONSTRAINT [DF_JOB_JC_PAID_IN_FULL]  DEFAULT ((0)) FOR [JC_PAID_IN_FULL]
			";
		dprint((strlen($sql) > 100) ? (substr($sql, 0, 100) . "...") : $sql);
		sql_execute($sql); # no need to audit
		
		$sql = "ALTER TABLE dbo.JOB ADD CONSTRAINT DF_JOB_JC_TC_JOB DEFAULT 0 FOR JC_TC_JOB
			";
		dprint((strlen($sql) > 100) ? (substr($sql, 0, 100) . "...") : $sql);
		sql_execute($sql); # no need to audit
		
		$sql = "ALTER TABLE [dbo].[JOB] ADD  CONSTRAINT [DF_JOB_JC_LETTER_MORE]  DEFAULT ((0)) FOR [JC_LETTER_MORE]
			";
		dprint((strlen($sql) > 100) ? (substr($sql, 0, 100) . "...") : $sql);
		sql_execute($sql); # no need to audit
		
		$sql = "ALTER TABLE [dbo].[JOB] ADD  CONSTRAINT [DF_JOB_JC_IN_PROGRESS_CU1]  DEFAULT ((0)) FOR [JC_IN_PROGRESS_CU1]
			";
		dprint((strlen($sql) > 100) ? (substr($sql, 0, 100) . "...") : $sql);
		sql_execute($sql); # no need to audit
		
		$sql = "ALTER TABLE [dbo].[JOB] ADD  CONSTRAINT [DF_JOB_JC_SUBJ_PAID_CU2]  DEFAULT ((0)) FOR [JC_SUBJ_PAID_CU2]
			";
		dprint((strlen($sql) > 100) ? (substr($sql, 0, 100) . "...") : $sql);
		sql_execute($sql); # no need to audit
		
		$sql = "ALTER TABLE [dbo].[JOB] ADD  CONSTRAINT [DF_JOB_JC_PDCHEQUES_CU3]  DEFAULT ((0)) FOR [JC_PDCHEQUES_CU3]
			";
		dprint((strlen($sql) > 100) ? (substr($sql, 0, 100) . "...") : $sql);
		sql_execute($sql); # no need to audit
		
		$sql = "ALTER TABLE [dbo].[JOB] ADD  CONSTRAINT [DF_JOB_JC_SUBJ_CONT_CU4]  DEFAULT ((0)) FOR [JC_SUBJ_CONT_CU4]
			";
		dprint((strlen($sql) > 100) ? (substr($sql, 0, 100) . "...") : $sql);
		sql_execute($sql); # no need to audit
		
		$sql = "ALTER TABLE [dbo].[JOB] ADD  CONSTRAINT [DF_JOB_JC_AGREED_CU5]  DEFAULT ((0)) FOR [JC_AGREED_CU5]
			";
		dprint((strlen($sql) > 100) ? (substr($sql, 0, 100) . "...") : $sql);
		sql_execute($sql); # no need to audit
		
		$sql = "ALTER TABLE [dbo].[JOB] ADD  CONSTRAINT [DF_JOB_JC_NEW_ADR_CU6]  DEFAULT ((0)) FOR [JC_NEW_ADR_CU6]
			";
		dprint((strlen($sql) > 100) ? (substr($sql, 0, 100) . "...") : $sql);
		sql_execute($sql); # no need to audit
		
		$sql = "ALTER TABLE [dbo].[JOB] ADD  CONSTRAINT [DF_JOB_JC_NO_ADDR_CU7]  DEFAULT ((0)) FOR [JC_NO_ADDR_CU7]
			";
		dprint((strlen($sql) > 100) ? (substr($sql, 0, 100) . "...") : $sql);
		sql_execute($sql); # no need to audit
		
		$sql = "ALTER TABLE [dbo].[JOB] ADD  CONSTRAINT [DF_JOB_JC_FAIL_PROM_CU8]  DEFAULT ((0)) FOR [JC_FAIL_PROM_CU8]
			";
		dprint((strlen($sql) > 100) ? (substr($sql, 0, 100) . "...") : $sql);
		sql_execute($sql); # no need to audit
		
		$sql = "ALTER TABLE [dbo].[JOB] ADD  CONSTRAINT [DF_JOB_JC_NOT_RESP_CU9]  DEFAULT ((0)) FOR [JC_NOT_RESP_CU9]
			";
		dprint((strlen($sql) > 100) ? (substr($sql, 0, 100) . "...") : $sql);
		sql_execute($sql); # no need to audit
		
		$sql = "ALTER TABLE [dbo].[JOB] ADD  CONSTRAINT [DF_JOB_JC_ADD_NOTES_CU10]  DEFAULT ((0)) FOR [JC_ADD_NOTES_CU10]
			";
		dprint((strlen($sql) > 100) ? (substr($sql, 0, 100) . "...") : $sql);
		sql_execute($sql); # no need to audit
		
		$sql = "CREATE NONCLUSTERED INDEX [IX_CLIENT2_ID] ON [dbo].[JOB]
			(
				[CLIENT2_ID] ASC
			)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, SORT_IN_TEMPDB = OFF, DROP_EXISTING = OFF, ONLINE = OFF, 
			ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON)
			";
		dprint((strlen($sql) > 100) ? (substr($sql, 0, 100) . "...") : $sql);
		sql_execute($sql); # no need to audit
		
		$sql = "CREATE NONCLUSTERED INDEX [IX_JOB_STATUS] ON [dbo].[JOB]
			(
				[JC_JOB_STATUS_ID] ASC
			)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, SORT_IN_TEMPDB = OFF, DROP_EXISTING = OFF, ONLINE = OFF, 
			ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON)

			";
		dprint((strlen($sql) > 100) ? (substr($sql, 0, 100) . "...") : $sql);
		sql_execute($sql); # no need to audit
		
		$sql = "CREATE NONCLUSTERED INDEX [IX_JOB_TYPE] ON [dbo].[JOB]
			(
				[JT_JOB_TYPE_ID] ASC
			)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, SORT_IN_TEMPDB = OFF, DROP_EXISTING = OFF, ONLINE = OFF, 
			ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON)
			";
		dprint((strlen($sql) > 100) ? (substr($sql, 0, 100) . "...") : $sql);
		sql_execute($sql); # no need to audit
		
		$sql = "CREATE NONCLUSTERED INDEX [IX_SEQUENCE] ON [dbo].[JOB]
				(
					[J_SEQUENCE] ASC
				)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, SORT_IN_TEMPDB = OFF, 
				DROP_EXISTING = OFF, ONLINE = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON)
			";
		dprint((strlen($sql) > 100) ? (substr($sql, 0, 100) . "...") : $sql);
		sql_execute($sql); # no need to audit
		
		$sql = "CREATE NONCLUSTERED INDEX [IX_J_USER_ID] ON [dbo].[JOB]
			(
				[J_USER_ID] ASC
			)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, SORT_IN_TEMPDB = OFF, DROP_EXISTING = OFF, ONLINE = OFF, 
			ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON)
			";
		dprint((strlen($sql) > 100) ? (substr($sql, 0, 100) . "...") : $sql);
		sql_execute($sql); # no need to audit
		
		$sql = "CREATE NONCLUSTERED INDEX [IX_VILNO] ON [dbo].[JOB]
			(
				[J_VILNO] ASC
			)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, SORT_IN_TEMPDB = OFF, DROP_EXISTING = OFF, ONLINE = OFF, 
			ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON)
			";
		dprint((strlen($sql) > 100) ? (substr($sql, 0, 100) . "...") : $sql);
		sql_execute($sql); # no need to audit
		
		$sql = "CREATE NONCLUSTERED INDEX [IX_J_COMPLETE] ON [dbo].[JOB]
				(
					[J_COMPLETE] ASC
				)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, SORT_IN_TEMPDB = OFF, DROP_EXISTING = OFF, ONLINE = OFF, 
				ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON)
			";
		dprint((strlen($sql) > 100) ? (substr($sql, 0, 100) . "...") : $sql);
		sql_execute($sql); # no need to audit
		
		$sql = "CREATE NONCLUSTERED INDEX [IX_JT_JOB] ON [dbo].[JOB]
				(
					[JT_JOB] ASC
				)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, SORT_IN_TEMPDB = OFF, DROP_EXISTING = OFF, ONLINE = OFF, 
				ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON)
			";
		dprint((strlen($sql) > 100) ? (substr($sql, 0, 100) . "...") : $sql);
		sql_execute($sql); # no need to audit
		
		$sql = "CREATE NONCLUSTERED INDEX [IX_JC_JOB] ON [dbo].[JOB]
				(
					[JC_JOB] ASC
				)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, SORT_IN_TEMPDB = OFF, DROP_EXISTING = OFF, ONLINE = OFF, 
				ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON)
			";
		dprint((strlen($sql) > 100) ? (substr($sql, 0, 100) . "...") : $sql);
		sql_execute($sql); # no need to audit
		
		$sql = "CREATE NONCLUSTERED INDEX [IX_JOB_CLOSED] ON [dbo].[JOB]
				(
					[JOB_CLOSED] ASC
				)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, SORT_IN_TEMPDB = OFF, DROP_EXISTING = OFF, ONLINE = OFF, 
				ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON)
			";
		dprint((strlen($sql) > 100) ? (substr($sql, 0, 100) . "...") : $sql);
		sql_execute($sql); # no need to audit
		
		$sql = "CREATE NONCLUSTERED INDEX [IX_JOB_OBSOLETE] ON [dbo].[JOB]
				(
					[OBSOLETE] ASC
				)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, SORT_IN_TEMPDB = OFF, DROP_EXISTING = OFF, ONLINE = OFF, 
				ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON)
			";
		dprint((strlen($sql) > 100) ? (substr($sql, 0, 100) . "...") : $sql);
		sql_execute($sql); # no need to audit
		
		$sql = "CREATE NONCLUSTERED INDEX [IX_J_ARCHIVED] ON [dbo].[JOB]
				(
					[J_ARCHIVED] ASC
				)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, SORT_IN_TEMPDB = OFF, DROP_EXISTING = OFF, ONLINE = OFF, 
				ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON)
			";
		dprint((strlen($sql) > 100) ? (substr($sql, 0, 100) . "...") : $sql);
		sql_execute($sql); # no need to audit

		# Recommended by Rackspace 01/03/17:
		# https://my.rackspace.com/portal/ticket/detail/170228-03539 ([missing_index_12388_12387_JOB])
		$sql = "CREATE INDEX [JOB_GRP_OBS_ID] ON [VILCOLDB].[dbo].[JOB] 
				([JOB_GROUP_ID], [OBSOLETE],[JOB_ID]) INCLUDE ([J_VILNO], [J_SEQUENCE])
			";
		dprint((strlen($sql) > 100) ? (substr($sql, 0, 100) . "...") : $sql);
		sql_execute($sql); # no need to audit
		
		# Recommended by Rackspace 01/03/17:
		# https://my.rackspace.com/portal/ticket/detail/170228-03539 ([missing_index_8_7_CLIENT_LETTER_LINK])
		$sql = "CREATE INDEX [CL_LTR_K_ID] ON [VILCOLDB].[dbo].[CLIENT_LETTER_LINK] 
				([CLIENT2_ID]) INCLUDE ([LETTER_TYPE_ID])
			";
		dprint((strlen($sql) > 100) ? (substr($sql, 0, 100) . "...") : $sql);
		sql_execute($sql); # no need to audit
		
		# Recommended by Rackspace 01/03/17:
		# https://my.rackspace.com/portal/ticket/detail/170228-03539 ([missing_index_12267_12266_JOB])
		$sql = "CREATE INDEX [JOB_ARCH_OBS] ON [VILCOLDB].[dbo].[JOB] 
				([J_ARCHIVED], [OBSOLETE]) INCLUDE ([JOB_ID], [CLIENT_REF], [JC_TRANS_ID])
			";
		dprint((strlen($sql) > 100) ? (substr($sql, 0, 100) . "...") : $sql);
		sql_execute($sql); # no need to audit
		
		# Recommended by Rackspace 01/03/17:
		# https://my.rackspace.com/portal/ticket/detail/170228-03539 ([missing_index_12270_12269_JOB])
		$sql = "CREATE INDEX [JOB_ARCH_OBS_LONG] ON [VILCOLDB].[dbo].[JOB] 
				([J_ARCHIVED], [OBSOLETE]) 
				INCLUDE ([JOB_ID], [CLIENT2_ID], [CLIENT_REF], [JOB_CLOSED], [J_VILNO], [J_SEQUENCE], [J_USER_ID], [J_OPENED_DT], [IMPORTED], [JT_JOB], 
																[JT_JOB_TYPE_ID], [JT_REPORT_APPR], [JC_JOB_STATUS_ID], [JC_INSTAL_AMT], [JC_TRANS_ID])
			";
		dprint((strlen($sql) > 100) ? (substr($sql, 0, 100) . "...") : $sql);
		sql_execute($sql); # no need to audit

	}
	else
	{
		if ($tc == 't')
			$field = 'JT_JOB';
		elseif ($tc == 'c')
			$field = 'JC_JOB';
		else
			$field = '*=* ERROR';
		
		$aux_tables = array('JOB_ARRANGE', 'JOB_LETTER', 'JOB_NOTE', 'JOB_PHONE', 'JOB_ACT', 'JOB_SUBJECT', 'JOB_Z');
		foreach ($aux_tables as $one_t)
		{
			$sql = "DELETE FROM $one_t WHERE JOB_ID IN (SELECT JOB_ID FROM JOB WHERE $field=$sqlTrue)";
			dprint((strlen($sql) > 100) ? (substr($sql, 0, 100) . "...") : $sql);
			sql_execute($sql); # no need to audit
		}
					
		$sql = "DELETE FROM JOB WHERE $field=$sqlTrue";
		dprint((strlen($sql) > 100) ? (substr($sql, 0, 100) . "...") : $sql);
		sql_execute($sql); # no need to audit
	}
} # init_jobs()

function init_letter_templates()
{
	# Delete all letter templates:

	global $V_Text_Size;

	# --------------------------------------------------------------------------------------

	if (sql_table_exists('LETTER_TYPE_SD'))
	{
		$sql = "DROP TABLE LETTER_TYPE_SD";
		dprint($sql);
		sql_execute($sql); # no need to audit
	}
		
	$sql = "
		CREATE TABLE [dbo].[LETTER_TYPE_SD](
			[LETTER_TYPE_ID] [int] IDENTITY(1,1) NOT NULL,
			[LETTER_NAME] [varchar](100) NOT NULL,
			[JT_T_JOB_TYPE_ID] [int] NULL,
			[LETTER_DESCR] [varchar](500) NULL,
			[LETTER_TEMPLATE] [varchar]($V_Text_Size) NULL,
			[LETTER_EM_SUBJECT] [varchar](200) NULL,
			[LETTER_EM_BODY] [varchar](1000) NULL,
			[JT_T_SUCC] [int] NULL,
			[JT_T_OPEN] [varchar](1000) NULL,
			[JT_T_CLOSE] [varchar](1000) NULL,
			[LT_NON_MAN] [bit] NOT NULL,
			[OBSOLETE] [bit] NOT NULL,
		 CONSTRAINT [PK_LETTER_TYPE_SD] PRIMARY KEY CLUSTERED 
		(
			[LETTER_TYPE_ID] ASC
		)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, 
		ALLOW_PAGE_LOCKS = ON) ON [PRIMARY]
		) ON [PRIMARY]
		";
	dprint((strlen($sql) > 100) ? (substr($sql, 0, 100) . "...") : $sql);
	sql_execute($sql); # no need to audit
	
	$sql = "ALTER TABLE [dbo].[LETTER_TYPE_SD] ADD  CONSTRAINT [DF_LETTER_TYPE_SD_LT_NON_MAN]  DEFAULT ((0)) FOR [LT_NON_MAN]
		";
	dprint((strlen($sql) > 100) ? (substr($sql, 0, 100) . "...") : $sql);
	sql_execute($sql); # no need to audit
	
	$sql = "ALTER TABLE [dbo].[LETTER_TYPE_SD] ADD  CONSTRAINT [DF_LETTER_TYPE_SD_OBSOLETE]  DEFAULT ((0)) FOR [OBSOLETE]
		";
	dprint((strlen($sql) > 100) ? (substr($sql, 0, 100) . "...") : $sql);
	sql_execute($sql); # no need to audit
	
	$sql = "
		ALTER TABLE [dbo].[LETTER_TYPE_SD] ADD  CONSTRAINT [IX_LETTER_NAME] UNIQUE NONCLUSTERED 
		(
			[LETTER_NAME] ASC
		)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, SORT_IN_TEMPDB = OFF, IGNORE_DUP_KEY = OFF, ONLINE = OFF, 
		ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON)
		";
	dprint((strlen($sql) > 100) ? (substr($sql, 0, 100) . "...") : $sql);
	sql_execute($sql); # no need to audit
	
} # init_letter_templates()

function init_letter_sequences()
{
	if (sql_table_exists('LETTER_SEQ'))
	{
		$sql = "DROP TABLE LETTER_SEQ";
		dprint($sql);
		sql_execute($sql); # no need to audit
	}
		
	$sql = "
			CREATE TABLE [dbo].[LETTER_SEQ](
				[LETTER_SEQ_ID] [int] IDENTITY(1,1) NOT NULL,
				[CLIENT2_ID] [int] NOT NULL,
				[SEQ_NUM] [int] NOT NULL,
				[LETTER_TYPE_ID] [int] NOT NULL,
				[SEQ_DAYS] [int] NOT NULL,
				[OBSOLETE] [bit] NOT NULL,
			 CONSTRAINT [PK_LETTER_SEQ] PRIMARY KEY CLUSTERED 
			(
				[LETTER_SEQ_ID] ASC
			)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON) ON [PRIMARY]
			) ON [PRIMARY]
		";
	dprint((strlen($sql) > 100) ? (substr($sql, 0, 100) . "...") : $sql);
	sql_execute($sql); # no need to audit
	
	$sql = "
		ALTER TABLE [dbo].[LETTER_SEQ] ADD  CONSTRAINT [DF_LETTER_SEQ_OBSOLETE]  DEFAULT ((0)) FOR [OBSOLETE]
		";
	dprint((strlen($sql) > 100) ? (substr($sql, 0, 100) . "...") : $sql);
	sql_execute($sql); # no need to audit
	
	$sql = "
		CREATE NONCLUSTERED INDEX [IX_CLIENT2_ID] ON [dbo].[LETTER_SEQ]
		(
			[CLIENT2_ID] ASC
		)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, SORT_IN_TEMPDB = OFF, DROP_EXISTING = OFF, ONLINE = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON)
		";
	dprint((strlen($sql) > 100) ? (substr($sql, 0, 100) . "...") : $sql);
	sql_execute($sql); # no need to audit
	
	$inserts = array(	"INSERT INTO LETTER_SEQ (CLIENT2_ID, SEQ_NUM, LETTER_TYPE_ID, SEQ_DAYS) VALUES (0, 1, 26, 14)",
						"INSERT INTO LETTER_SEQ (CLIENT2_ID, SEQ_NUM, LETTER_TYPE_ID, SEQ_DAYS) VALUES (0, 2, 27, 14)",
						"INSERT INTO LETTER_SEQ (CLIENT2_ID, SEQ_NUM, LETTER_TYPE_ID, SEQ_DAYS) VALUES (0, 3, 28,  0)"
						);
	foreach ($inserts as $sql)
	{
		dprint((strlen($sql) > 100) ? (substr($sql, 0, 100) . "...") : $sql);
		sql_execute($sql); # no need to audit
	}
	
} # init_letter_sequences()

function init_clients()
{
	# Delete all client data:

	global $V_Text_Size; # settings.php
	
	# --- SALESPERSON -----------------------------------------------------------------------------------
	if (sql_table_exists('SALESPERSON'))
	{
		$sql = "DROP TABLE SALESPERSON";
		dprint($sql);
		sql_execute($sql); # no need to audit
	}
		
	$sql = "
		CREATE TABLE [dbo].[SALESPERSON](
			[SALESPERSON_ID] [int] IDENTITY(1,1) NOT NULL,
			[CLIENT2_ID] [int] NOT NULL,
			[SP_USER_ID] [int] NULL,
			[SP_TXT] [varchar](200) NULL,
			[SP_DT] [datetime] NULL,
		 CONSTRAINT [PK_SALESPERSON] PRIMARY KEY CLUSTERED 
		(
			[SALESPERSON_ID] ASC
		)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, 
		ALLOW_PAGE_LOCKS = ON) ON [PRIMARY]
		) ON [PRIMARY]
		";
	dprint((strlen($sql) > 100) ? (substr($sql, 0, 100) . "...") : $sql);
	sql_execute($sql); # no need to audit
	
	$sql = "
		CREATE NONCLUSTERED INDEX [IX_CLIENT2_ID] ON [dbo].[SALESPERSON]
		(
			[CLIENT2_ID] ASC
		)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, SORT_IN_TEMPDB = OFF, DROP_EXISTING = OFF, ONLINE = OFF, 
		ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON)
		";
	dprint((strlen($sql) > 100) ? (substr($sql, 0, 100) . "...") : $sql);
	sql_execute($sql); # no need to audit
	
	
	# --- CLIENT_TARGET_LINK -----------------------------------------------------------------------------------

	if (sql_table_exists('CLIENT_TARGET_LINK'))
	{
		$sql = "DROP TABLE CLIENT_TARGET_LINK";
		dprint($sql);
		sql_execute($sql); # no need to audit
	}
		
	$sql = "
		CREATE TABLE [dbo].[CLIENT_TARGET_LINK](
			[CLIENT_TARGET_LINK_ID] [int] IDENTITY(1,1) NOT NULL,
			[CLIENT2_ID] [int] NOT NULL,
			[JOB_TARGET_ID] [int] NOT NULL,
			[CT_FEE] [decimal](10, 2) NULL,
		 CONSTRAINT [PK_CLIENT_TARGET_LINK] PRIMARY KEY CLUSTERED 
		(
			[CLIENT_TARGET_LINK_ID] ASC
		)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, 
		ALLOW_PAGE_LOCKS = ON) ON [PRIMARY]
		) ON [PRIMARY]
		";
	dprint((strlen($sql) > 100) ? (substr($sql, 0, 100) . "...") : $sql);
	sql_execute($sql); # no need to audit
	
	$sql = "
		CREATE NONCLUSTERED INDEX [IX_CLIENT2_ID] ON [dbo].[CLIENT_TARGET_LINK]
		(
			[CLIENT2_ID] ASC
		)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, SORT_IN_TEMPDB = OFF, DROP_EXISTING = OFF, ONLINE = OFF, 
		ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON)
		";
	dprint((strlen($sql) > 100) ? (substr($sql, 0, 100) . "...") : $sql);
	sql_execute($sql); # no need to audit
	
	$sql = "
		CREATE NONCLUSTERED INDEX [IX_JOB_TARGET_ID] ON [dbo].[CLIENT_TARGET_LINK]
		(
			[JOB_TARGET_ID] ASC
		)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, SORT_IN_TEMPDB = OFF, DROP_EXISTING = OFF, ONLINE = OFF, 
		ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON)
		";
	dprint((strlen($sql) > 100) ? (substr($sql, 0, 100) . "...") : $sql);
	sql_execute($sql); # no need to audit
	
	
	# --- CLIENT_REPORT -----------------------------------------------------------------------------------

	if (sql_table_exists('CLIENT_REPORT'))
	{
		$sql = "DROP TABLE CLIENT_REPORT";
		dprint($sql);
		sql_execute($sql); # no need to audit
	}
		
	$sql = "
		CREATE TABLE [dbo].[CLIENT_REPORT](
			[CLIENT_REPORT_ID] [int] IDENTITY(1,1) NOT NULL,
			[CLIENT2_ID] [int] NOT NULL,
			[REPORT_NAME] [varchar](100) NOT NULL,
			[REPORT_DT] [datetime] NULL,
			[REPORT_FILENAME] [varchar](100) NULL,
			[REPORT_GEN_ID] [int] NULL,
			[REPORT_GEN_DT] [datetime] NULL,
			[REPORT_SENT_ID] [int] NULL,
			[REPORT_SENT_DT] [datetime] NULL,
			CONSTRAINT [PK_CLIENT_REPORT] PRIMARY KEY CLUSTERED 
			(
			[CLIENT_REPORT_ID] ASC
			)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON) ON [PRIMARY]
		) ON [PRIMARY]
		";
	dprint((strlen($sql) > 100) ? (substr($sql, 0, 100) . "...") : $sql);
	sql_execute($sql); # no need to audit
	
	$sql = "
		CREATE NONCLUSTERED INDEX [IX_CLIENT_ID] ON [dbo].[CLIENT_REPORT]
		(
			[CLIENT2_ID] ASC
		)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, SORT_IN_TEMPDB = OFF, DROP_EXISTING = OFF, ONLINE = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON)
		";
	dprint((strlen($sql) > 100) ? (substr($sql, 0, 100) . "...") : $sql);
	sql_execute($sql); # no need to audit
	
	
	# --- CLIENT_CONTACT -----------------------------------------------------------------------------------
	
	if (sql_table_exists('CLIENT_CONTACT'))
	{
		$sql = "DROP TABLE CLIENT_CONTACT";
		dprint($sql);
		sql_execute($sql); # no need to audit
	}	
	
	$sql = "
		CREATE TABLE [dbo].[CLIENT_CONTACT](
			[CLIENT_CONTACT_ID] [int] IDENTITY(1,1) NOT NULL,
			[CLIENT2_ID] [int] NOT NULL,
			[CC_TITLE] [varchar](10) NULL,
			[CC_FIRSTNAME] [varbinary](256) NULL,
			[CC_LASTNAME] [varbinary](256) NOT NULL,
			[CC_MAIN] [bit] NOT NULL,
			[CC_INV] [bit] NOT NULL,
			[CC_REP] [bit] NOT NULL,
			[CC_POSITION] [varbinary](256) NULL,
			[CC_EMAIL_1] [varbinary](256) NULL,
			[CC_EMAIL_2] [varbinary](256) NULL,
			[CC_AD_AS_CL] [bit] NOT NULL,
			[CC_ADDR_1] [varbinary](256) NULL,
			[CC_ADDR_2] [varbinary](256) NULL,
			[CC_ADDR_3] [varbinary](256) NULL,
			[CC_ADDR_4] [varbinary](256) NULL,
			[CC_ADDR_5] [varbinary](256) NULL,
			[CC_ADDR_PC] [varbinary](256) NULL,
			[IMPORTED] [bit] NOT NULL,
			[OBSOLETE] [bit] NOT NULL,
		 CONSTRAINT [PK_CLIENT_CONTACT] PRIMARY KEY CLUSTERED 
		(
			[CLIENT_CONTACT_ID] ASC
		)WITH (PAD_INDEX  = OFF, STATISTICS_NORECOMPUTE  = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS  = ON, 
		ALLOW_PAGE_LOCKS  = ON) ON [PRIMARY]
		) ON [PRIMARY]
		";
	dprint((strlen($sql) > 100) ? (substr($sql, 0, 100) . "...") : $sql);
	sql_execute($sql); # no need to audit

	$sql = "ALTER TABLE [dbo].[CLIENT_CONTACT] ADD  CONSTRAINT [DF_CLIENT_CONTACT_CC_MAIN]  DEFAULT ((0)) FOR [CC_MAIN]
		";
	dprint((strlen($sql) > 100) ? (substr($sql, 0, 100) . "...") : $sql);
	sql_execute($sql); # no need to audit
	
	$sql = "ALTER TABLE [dbo].[CLIENT_CONTACT] ADD  CONSTRAINT [DF_CLIENT_CONTACT_CC_INV]  DEFAULT ((0)) FOR [CC_INV]
		";
	dprint((strlen($sql) > 100) ? (substr($sql, 0, 100) . "...") : $sql);
	sql_execute($sql); # no need to audit
	
	$sql = "ALTER TABLE [dbo].[CLIENT_CONTACT] ADD  CONSTRAINT [DF_CLIENT_CONTACT_CC_REP]  DEFAULT ((0)) FOR [CC_REP]
		";
	dprint((strlen($sql) > 100) ? (substr($sql, 0, 100) . "...") : $sql);
	sql_execute($sql); # no need to audit
	
	$sql = "ALTER TABLE [dbo].[CLIENT_CONTACT] ADD  CONSTRAINT [DF_CLIENT_CONTACT_CC_AD_AS_CL]  DEFAULT ((1)) FOR [CC_AD_AS_CL]
		";
	dprint((strlen($sql) > 100) ? (substr($sql, 0, 100) . "...") : $sql);
	sql_execute($sql); # no need to audit
	
	$sql = "ALTER TABLE [dbo].[CLIENT_CONTACT] ADD  CONSTRAINT [DF_CLIENT_CONTACT_OBSOLETE]  DEFAULT ((0)) FOR [OBSOLETE]
		";
	dprint((strlen($sql) > 100) ? (substr($sql, 0, 100) . "...") : $sql);
	sql_execute($sql); # no need to audit
	
	$sql = "CREATE NONCLUSTERED INDEX [IX_CLIENT2_ID] ON [dbo].[CLIENT_CONTACT] 
		(
			[CLIENT2_ID] ASC
		)WITH (PAD_INDEX  = OFF, STATISTICS_NORECOMPUTE  = OFF, SORT_IN_TEMPDB = OFF, IGNORE_DUP_KEY = OFF, DROP_EXISTING = OFF,
		 ONLINE = OFF, ALLOW_ROW_LOCKS  = ON, ALLOW_PAGE_LOCKS  = ON) ON [PRIMARY]
		";
	dprint((strlen($sql) > 100) ? (substr($sql, 0, 100) . "...") : $sql);
	sql_execute($sql); # no need to audit

	$sql = "CREATE NONCLUSTERED INDEX [IX_CC_MAIN] ON [dbo].[CLIENT_CONTACT]
			(
				[CC_MAIN] ASC
			)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, SORT_IN_TEMPDB = OFF, DROP_EXISTING = OFF, 
			ONLINE = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON)
		";
	dprint((strlen($sql) > 100) ? (substr($sql, 0, 100) . "...") : $sql);
	sql_execute($sql); # no need to audit

	
	# --- CLIENT_NOTE -----------------------------------------------------------------------------------
	
	if (sql_table_exists('CLIENT_NOTE'))
	{
		$sql = "DROP TABLE CLIENT_NOTE";
		dprint($sql);
		sql_execute($sql); # no need to audit
	}	
	
	$sql = "
		CREATE TABLE [dbo].[CLIENT_NOTE](
			[CLIENT_NOTE_ID] [int] IDENTITY(1,1) NOT NULL,
			[CLIENT2_ID] [int] NOT NULL,
			[CN_NOTE] [varbinary]($V_Text_Size) NOT NULL,
			[IMPORTED] [bit] NOT NULL,
			[CN_ADDED_ID] [int] NOT NULL,
			[CN_ADDED_DT] [datetime] NOT NULL,
			[CN_UPDATED_ID] [int] NULL,
			[CN_UPDATED_DT] [datetime] NULL,
		 CONSTRAINT [PK_CLIENT_NOTE] PRIMARY KEY CLUSTERED 
		(
			[CLIENT_NOTE_ID] ASC
		)WITH (PAD_INDEX  = OFF, STATISTICS_NORECOMPUTE  = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS  = ON, 
		ALLOW_PAGE_LOCKS  = ON) ON [PRIMARY]
		) ON [PRIMARY]
		";
	dprint((strlen($sql) > 100) ? (substr($sql, 0, 100) . "...") : $sql);
	sql_execute($sql); # no need to audit
	
	$sql = "CREATE NONCLUSTERED INDEX [IX_CLIENT2_ID_CN] ON [dbo].[CLIENT_NOTE] 
		(
			[CLIENT2_ID] ASC
		)WITH (PAD_INDEX  = OFF, STATISTICS_NORECOMPUTE  = OFF, SORT_IN_TEMPDB = OFF, IGNORE_DUP_KEY = OFF, DROP_EXISTING = OFF,
		 ONLINE = OFF, ALLOW_ROW_LOCKS  = ON, ALLOW_PAGE_LOCKS  = ON) ON [PRIMARY]
		";
	dprint((strlen($sql) > 100) ? (substr($sql, 0, 100) . "...") : $sql);
	sql_execute($sql); # no need to audit

	
	# --- CLIENT_CONTACT_PHONE -----------------------------------------------------------------------------------

	if (sql_table_exists('CLIENT_CONTACT_PHONE'))
	{
		$sql = "DROP TABLE CLIENT_CONTACT_PHONE";
		dprint($sql);
		sql_execute($sql); # no need to audit
	}	
	$sql = "
		CREATE TABLE [dbo].[CLIENT_CONTACT_PHONE](
			[CLIENT_CONTACT_PHONE_ID] [int] IDENTITY(1,1) NOT NULL,
			[CLIENT_CONTACT_ID] [int] NOT NULL,
			[CP_PHONE] [varbinary](256) NULL,
			[CP_DESCR] [varbinary](256) NULL,
			[CP_MAIN] [bit] NOT NULL,
			[IMPORTED] [bit] NOT NULL,
			[OBSOLETE] [bit] NOT NULL,
		 CONSTRAINT [PK_CLIENT_CONTACT_PHONE] PRIMARY KEY CLUSTERED 
		(
			[CLIENT_CONTACT_PHONE_ID] ASC
		)WITH (PAD_INDEX  = OFF, STATISTICS_NORECOMPUTE  = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS  = ON, 
		ALLOW_PAGE_LOCKS  = ON) ON [PRIMARY]
		) ON [PRIMARY]
		";
	dprint((strlen($sql) > 100) ? (substr($sql, 0, 100) . "...") : $sql);
	sql_execute($sql); # no need to audit
	
	$sql = "ALTER TABLE [dbo].[CLIENT_CONTACT_PHONE] ADD  CONSTRAINT [DF_CLIENT_CONTACT_PHONE_OBSOLETE]  DEFAULT ((0)) FOR [OBSOLETE]
		";
	dprint((strlen($sql) > 100) ? (substr($sql, 0, 100) . "...") : $sql);
	sql_execute($sql); # no need to audit
	
	$sql = "CREATE NONCLUSTERED INDEX [IX_CLIENT_CONTACT_ID] ON [dbo].[CLIENT_CONTACT_PHONE] 
		(
			[CLIENT_CONTACT_ID] ASC
		)WITH (PAD_INDEX  = OFF, STATISTICS_NORECOMPUTE  = OFF, SORT_IN_TEMPDB = OFF, IGNORE_DUP_KEY = OFF, DROP_EXISTING = OFF,
		 ONLINE = OFF, ALLOW_ROW_LOCKS  = ON, ALLOW_PAGE_LOCKS  = ON) ON [PRIMARY]
		";
	dprint((strlen($sql) > 100) ? (substr($sql, 0, 100) . "...") : $sql);
	sql_execute($sql); # no need to audit
	
	$sql = "CREATE NONCLUSTERED INDEX [IX_CP_MAIN] ON [dbo].[CLIENT_CONTACT_PHONE]
			(
				[CP_MAIN] ASC
			)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, SORT_IN_TEMPDB = OFF, DROP_EXISTING = OFF, 
			ONLINE = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON)
		";
	dprint((strlen($sql) > 100) ? (substr($sql, 0, 100) . "...") : $sql);
	sql_execute($sql); # no need to audit
	
	
	# --- CLIENT_LETTER_LINK -----------------------------------------------------------------------------------

	if (sql_table_exists('CLIENT_LETTER_LINK'))
	{
		$sql = "DROP TABLE CLIENT_LETTER_LINK";
		dprint($sql);
		sql_execute($sql); # no need to audit
	}	

	$sql = "CREATE TABLE [dbo].[CLIENT_LETTER_LINK](
			[CLIENT_LETTER_LINK_ID] [int] IDENTITY(1,1) NOT NULL,
			[CLIENT2_ID] [int] NOT NULL,
			[LETTER_TYPE_ID] [int] NOT NULL,
		 CONSTRAINT [PK_CLIENT_LETTER_LINK] PRIMARY KEY CLUSTERED 
		(
			[CLIENT_LETTER_LINK_ID] ASC
		)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON) ON [PRIMARY]
		) ON [PRIMARY]
		";
	dprint((strlen($sql) > 100) ? (substr($sql, 0, 100) . "...") : $sql);
	sql_execute($sql); # no need to audit

	
	# --- CLIENT_Z -----------------------------------------------------------------------------------

	if (sql_object_exists('IX_CLIENT_Z'))
	{
		$sql = "ALTER TABLE CLIENT_Z DROP CONSTRAINT IX_CLIENT_Z";
		dprint($sql);
		sql_execute($sql); # no need to audit
	}
	if (sql_object_exists('IX_CLIENT_Z'))
	{
		$sql = "DROP INDEX CLIENT_Z.IX_CLIENT_Z";
		dprint($sql);
		sql_execute($sql); # no need to audit
	}	
	if (sql_table_exists('CLIENT_Z'))
	{
		$sql = "DROP TABLE CLIENT_Z";
		dprint($sql);
		sql_execute($sql); # no need to audit
	}	
	$sql = "
		CREATE TABLE [dbo].[CLIENT_Z](
			[CLIENT2_ID] [int] NOT NULL,
			[Z_ACC] [int] NULL,
			[Z_AP_AMOUNT] [decimal](10, 2) NULL,
			[Z_AP_CODE] [varchar](3) NULL,
			[Z_AP_DEF] [varchar](3) NULL,
			[Z_CS_DEL] [int] NULL,
			[Z_CSPENT] [datetime] NULL,
			[Z_DI_DEL] [int] NULL,
			[Z_DIRECT] [datetime] NULL,
			[Z_FO_DEL] [int] NULL,
			[Z_FORWARDED] [datetime] NULL,
			[Z_JOBS] [int] NULL,
			[Z_LASTREP] [datetime] NULL,
			[Z_MARK] [int] NULL,
			[Z_NOTRACES] [varchar](3) NULL,
			[Z_REP_ORD] [varchar](1) NULL,
			[Z_RETR] [int] NULL,
			[Z_TO_US] [datetime] NULL,
			[Z_TU_DEL] [int] NULL,
			[Z_VPS] [varchar](8) NULL,
		 CONSTRAINT [IX_CLIENT_Z] UNIQUE NONCLUSTERED 
		(
			[CLIENT2_ID] ASC
		)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON)
		 ON [PRIMARY]
		) ON [PRIMARY]
		";
	dprint((strlen($sql) > 100) ? (substr($sql, 0, 100) . "...") : $sql);
	sql_execute($sql); # no need to audit
	
	
	# --- CLIENT2 -----------------------------------------------------------------------------------
	
	if (sql_table_exists('CLIENT2'))
	{
		$sql = "DROP TABLE CLIENT2";
		dprint($sql);
		sql_execute($sql); # no need to audit
	}	
	$sql = "
		CREATE TABLE [dbo].[CLIENT2](
			[CLIENT2_ID] [int] IDENTITY(1,1) NOT NULL,
			[C_CODE] [int] NOT NULL,
			[CLIENT_GROUP_ID] [int] NULL,
			[C_INDIVIDUAL] [bit] NOT NULL,
			[C_AGENCY] [bit] NOT NULL,
			[C_CO_NAME] [varbinary](256) NULL,
			[C_ADDR_1] [varbinary](256) NULL,
			[C_ADDR_2] [varbinary](256) NULL,
			[C_ADDR_3] [varbinary](256) NULL,
			[C_ADDR_4] [varbinary](256) NULL,
			[C_ADDR_5] [varbinary](256) NULL,
			[C_ADDR_PC] [varbinary](256) NULL,
			[ALPHA_CODE] [varchar](10) NULL,
			[C_TRACE] [bit] NOT NULL,
			[C_COLLECT] [bit] NOT NULL,
			[COMM_PERCENT] [decimal](10, 3) NULL,
			[TR_FEE] [decimal](10, 2) NULL,
			[NT_FEE] [decimal](10, 2) NULL,
			[TC_FEE] [decimal](10, 2) NULL,
			[RP_FEE] [decimal](10, 2) NULL,
			[SV_FEE] [decimal](10, 2) NULL,
			[MN_FEE] [decimal](10, 2) NULL,
			[TM_FEE] [decimal](10, 2) NULL,
			[AT_FEE] [decimal](10, 2) NULL,
			[DEDUCT_AS] [bit] NOT NULL,
			[S_INVS_TRACE] [bit] NOT NULL,
			[SALESPERSON_ID] [int] NULL,
			[SALESPERSON_TXT] [varchar](200) NULL,
			[CREATED_DT] [datetime] NOT NULL,
			[UPDATED_DT] [datetime] NULL,
			[IMPORTED] [bit] NOT NULL,
			[C_BANK_NAME] [varbinary](256) NULL,
			[C_BANK_SORTCODE] [varbinary](256) NULL,
			[C_BANK_ACC_NUM] [varbinary](256) NULL,
			[C_BANK_ACC_NAME] [varbinary](256) NULL,
			[C_BANK_COUNTRY] [varchar](100) NULL,
			[C_BANK_SWIFT] [varbinary](256) NULL,
			[C_BANK_IBAN] [varbinary](256) NULL,
			[C_BACS] [bit] NOT NULL,
			[INV_EMAILED] [bit] NOT NULL,
			[INV_EMAIL_NAME] [varbinary](256) NULL,
			[INV_EMAIL_ADDR] [varbinary](256) NULL,
			[INV_COMBINE] [bit] NOT NULL,
			[INV_BRANCH_COMB] [bit] NOT NULL,
			[INV_STMT_FREQ] [varchar](1) NULL,
			[INV_NEXT_STMT_DT] [datetime] NULL,
			[C_ARCHIVED] [bit] NOT NULL,
			[C_VAT] [bit] NOT NULL,
			[C_GROUP_HO] [bit] NOT NULL,
			[C_CLOSEOUT] [bit] NOT NULL,
		 CONSTRAINT [PK_CLIENT2] PRIMARY KEY CLUSTERED 
		(
			[CLIENT2_ID] ASC
		)WITH (PAD_INDEX  = OFF, STATISTICS_NORECOMPUTE  = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS  = ON, ALLOW_PAGE_LOCKS  = ON) ON [PRIMARY]
		) ON [PRIMARY]
		";
	dprint((strlen($sql) > 100) ? (substr($sql, 0, 100) . "...") : $sql);
	sql_execute($sql); # no need to audit

	$sql = "ALTER TABLE [dbo].[CLIENT2] ADD  CONSTRAINT [DF_CLIENT2_C_INDIVIDUAL]  DEFAULT ((0)) FOR [C_INDIVIDUAL]";
	dprint((strlen($sql) > 100) ? (substr($sql, 0, 100) . "...") : $sql);
	sql_execute($sql); # no need to audit
	
	$sql = "ALTER TABLE [dbo].[CLIENT2] ADD  CONSTRAINT [DF_CLIENT2_C_AGENCY]  DEFAULT ((0)) FOR [C_AGENCY]";
	dprint((strlen($sql) > 100) ? (substr($sql, 0, 100) . "...") : $sql);
	sql_execute($sql); # no need to audit
	
	$sql = "ALTER TABLE [dbo].[CLIENT2] ADD  CONSTRAINT [DF_CLIENT2_C_TRACE]  DEFAULT ((0)) FOR [C_TRACE]";
	dprint((strlen($sql) > 100) ? (substr($sql, 0, 100) . "...") : $sql);
	sql_execute($sql); # no need to audit
	
	$sql = "ALTER TABLE [dbo].[CLIENT2] ADD  CONSTRAINT [DF_CLIENT2_C_COLLECT]  DEFAULT ((0)) FOR [C_COLLECT]";
	dprint((strlen($sql) > 100) ? (substr($sql, 0, 100) . "...") : $sql);
	sql_execute($sql); # no need to audit
	
	$sql = "ALTER TABLE [dbo].[CLIENT2] ADD  CONSTRAINT [DF_CLIENT2_DEDUCT_AS]  DEFAULT ((0)) FOR [DEDUCT_AS]";
	dprint((strlen($sql) > 100) ? (substr($sql, 0, 100) . "...") : $sql);
	sql_execute($sql); # no need to audit
	
	$sql = "ALTER TABLE [dbo].[CLIENT2] ADD  CONSTRAINT [DF_CLIENT2_S_INVS]  DEFAULT ((0)) FOR [S_INVS_TRACE]";
	dprint((strlen($sql) > 100) ? (substr($sql, 0, 100) . "...") : $sql);
	sql_execute($sql); # no need to audit
	
	$sql = "ALTER TABLE [dbo].[CLIENT2] ADD  CONSTRAINT [DF_CLIENT2_C_BACS]  DEFAULT ((0)) FOR [C_BACS]";
	dprint((strlen($sql) > 100) ? (substr($sql, 0, 100) . "...") : $sql);
	sql_execute($sql); # no need to audit
	
	$sql = "ALTER TABLE [dbo].[CLIENT2] ADD  CONSTRAINT [DF_CLIENT2_INV_EMAILED]  DEFAULT ((0)) FOR [INV_EMAILED]";
	dprint((strlen($sql) > 100) ? (substr($sql, 0, 100) . "...") : $sql);
	sql_execute($sql); # no need to audit
	
	$sql = "ALTER TABLE [dbo].[CLIENT2] ADD  CONSTRAINT [DF_CLIENT2_INV_COMBINE]  DEFAULT ((0)) FOR [INV_COMBINE]";
	dprint((strlen($sql) > 100) ? (substr($sql, 0, 100) . "...") : $sql);
	sql_execute($sql); # no need to audit
	
	$sql = "ALTER TABLE [dbo].[CLIENT2] ADD  CONSTRAINT [DF_CLIENT2_INV_BRANCH_COMB]  DEFAULT ((0)) FOR [INV_BRANCH_COMB]";
	dprint((strlen($sql) > 100) ? (substr($sql, 0, 100) . "...") : $sql);
	sql_execute($sql); # no need to audit
	
	$sql = "ALTER TABLE [dbo].[CLIENT2] ADD  CONSTRAINT [DF_CLIENT2_C_ARCHIVED]  DEFAULT ((0)) FOR [C_ARCHIVED]";
	dprint((strlen($sql) > 100) ? (substr($sql, 0, 100) . "...") : $sql);
	sql_execute($sql); # no need to audit
	
	$sql = "ALTER TABLE [dbo].[CLIENT2] ADD  CONSTRAINT [DF_CLIENT2_C_VAT]  DEFAULT ((0)) FOR [C_VAT]";
	dprint((strlen($sql) > 100) ? (substr($sql, 0, 100) . "...") : $sql);
	sql_execute($sql); # no need to audit
	
	$sql = "ALTER TABLE [dbo].[CLIENT2] ADD  CONSTRAINT [DF_CLIENT2_C_GROUP_HO]  DEFAULT ((0)) FOR [C_GROUP_HO]";
	dprint((strlen($sql) > 100) ? (substr($sql, 0, 100) . "...") : $sql);
	sql_execute($sql); # no need to audit
	
	$sql = "ALTER TABLE [dbo].[CLIENT2] ADD  CONSTRAINT [DF_CLIENT2_C_CLOSEOUT]  DEFAULT ((0)) FOR [C_CLOSEOUT]";
	dprint((strlen($sql) > 100) ? (substr($sql, 0, 100) . "...") : $sql);
	sql_execute($sql); # no need to audit
	
	$sql = "CREATE NONCLUSTERED INDEX [IX_C_CO_NAME] ON [dbo].[CLIENT2] 
		(
			[C_CO_NAME] ASC
		)WITH (PAD_INDEX  = OFF, STATISTICS_NORECOMPUTE  = OFF, SORT_IN_TEMPDB = OFF, IGNORE_DUP_KEY = OFF, DROP_EXISTING = OFF,
		ONLINE = OFF, ALLOW_ROW_LOCKS  = ON, ALLOW_PAGE_LOCKS  = ON) ON [PRIMARY]
		";
	dprint((strlen($sql) > 100) ? (substr($sql, 0, 100) . "...") : $sql);
	sql_execute($sql); # no need to audit
	
	$sql = "CREATE NONCLUSTERED INDEX [IX_C_CODE] ON [dbo].[CLIENT2] 
		(
			[C_CODE] ASC
		)WITH (PAD_INDEX  = OFF, STATISTICS_NORECOMPUTE  = OFF, SORT_IN_TEMPDB = OFF, IGNORE_DUP_KEY = OFF, DROP_EXISTING = OFF,
		ONLINE = OFF, ALLOW_ROW_LOCKS  = ON, ALLOW_PAGE_LOCKS  = ON) ON [PRIMARY]
		";
	dprint((strlen($sql) > 100) ? (substr($sql, 0, 100) . "...") : $sql);
	sql_execute($sql); # no need to audit

	$sql = "CREATE NONCLUSTERED INDEX [IX_C_ARCHIVED] ON [dbo].[CLIENT2]
			(
				[C_ARCHIVED] ASC
			)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, SORT_IN_TEMPDB = OFF, DROP_EXISTING = OFF, 
			ONLINE = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON)
		";
	dprint((strlen($sql) > 100) ? (substr($sql, 0, 100) . "...") : $sql);
	sql_execute($sql); # no need to audit
	

	# --- CLIENT_GROUP -----------------------------------------------------------------------------------
	
	if (sql_table_exists('CLIENT_GROUP'))
	{
		$sql = "DROP TABLE CLIENT_GROUP";
		dprint($sql);
		sql_execute($sql); # no need to audit
	}	
	$sql = "
		CREATE TABLE [dbo].[CLIENT_GROUP](
			[CLIENT_GROUP_ID] [int] IDENTITY(1,1) NOT NULL,
			[GROUP_NAME] [varbinary](256) NOT NULL,
			[OBSOLETE] [bit] NOT NULL,
		 CONSTRAINT [PK_CLIENT_GROUP] PRIMARY KEY CLUSTERED 
		(
			[CLIENT_GROUP_ID] ASC
		)WITH (PAD_INDEX  = OFF, STATISTICS_NORECOMPUTE  = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS  = ON, 
		ALLOW_PAGE_LOCKS  = ON) ON [PRIMARY]
		) ON [PRIMARY]
		";
	dprint((strlen($sql) > 100) ? (substr($sql, 0, 100) . "...") : $sql);
	sql_execute($sql); # no need to audit
	
	$sql = "ALTER TABLE [dbo].[CLIENT_GROUP] ADD  CONSTRAINT [DF_CLIENT_GROUP_OBSOLETE]  DEFAULT ((0)) FOR [OBSOLETE]
		";
	dprint((strlen($sql) > 100) ? (substr($sql, 0, 100) . "...") : $sql);
	sql_execute($sql); # no need to audit
	
} # init_clients()

function import_clients()
{
	# Before calling this function:
	#	- Export COLLECT/CLIENTS.DBF as CLIENTS.csv using DBF Manager with default settings.
	#	- Export COLLECT/EQUIVCLI.DBF as EQUIVCLI.csv using DBF Manager with default settings.
	# Required:
	#	c/CLIENTS.DBF
	#	c/CLIENTS.DBV
	#	c/CLIENTS.csv
	#	c/EQUIVCLI.csv
	
	# Import 31/12/16:
	# import_2016_12_31_1027.log
	# 2016-12-31 10:27:05/   POST=Array
	# (
	#     [task] => i_clients
	#     [tc] => 
	#     [other] => 
	# )
	# We imported 8175 clients: 8174 from CSV and one dummy client with code 2 and name “(Unknown client 2)” (ID 8175).
	# Imported 25 Equiv Client records from 25 records.
	
	global $c_code; # for csvfield()
	global $col_nums; # for csvfield()
	global $input_count; # for csvfield()
	global $one_record; # for csvfield()
	global $pm_match; # postcode found by is_postcode()
	global $sqlNow;
	global $sqlTrue;
	global $super_user_id;
	global $v_text_clip; # settings.php
	
	$enabled = false; #
	if (!$enabled)
	{
		dprint("This import function is disabled", true);
		return;
	}
	
	print "<h3>Importing Clients (Collect system only)</h3>";
	
	set_time_limit(10 * 60); # 10 mins
	
	init_clients();

	$sys = 'c'; # collect

	$stop_after = 0;#
	$stop_margin = 0;
	$input_count = 0;
	
	$dirname = "import-vilcol/{$sys}";
	$dbf = "CLIENTS.DBF";
	$dbv = "CLIENTS.DBV";
	$csv = "CLIENTS.csv";
	$fp_dbf = fopen("$dirname/$dbf", 'r');
	$fp_dbv = fopen("$dirname/$dbv", 'r');
	$fp_csv = fopen("$dirname/$csv", 'r');
	if ($fp_dbf && $fp_dbv && $fp_csv)
	{
		sql_encryption_preparation('CLIENT2');
		sql_encryption_preparation('CLIENT_NOTE');
		sql_encryption_preparation('CLIENT_CONTACT');
		sql_encryption_preparation('CLIENT_CONTACT_PHONE');
		$col_nums = array();
		
		# File offsets into DBF
		$dbf_firstoff = 1730; # offset into DBF of first data record (after the header record)
		$dbf_reclen = 710; # length of each data record in DBF

		$while_safety = 0; # to avoid infinite-looping
		while (true)
		{
			$verbose = false;
			if ((0 < $stop_after) && ($input_count <= $stop_after) && (($stop_after - $input_count) < $stop_margin))
				$verbose = true;
			#if (5103 <= $input_count)#
			#	$verbose = true;
			
			$while_safety++;
			if ($while_safety > 100000) # 100,000 clients
				break; # while(true)
				
			$one_record = fgetcsv($fp_csv);
			if ($verbose)
				dprint((($input_count == 0) ? "Headers: " : "Client: ") . print_r($one_record,1));#
			if ($input_count == 0)
			{
				foreach ($one_record as $num => $field)
					$col_nums[$field] = $num;
				if ($verbose)
					dprint("col_nums: " . print_r($col_nums,1));#
			}
			elseif ($one_record && (count($one_record) > 0))
			{
				# --- Client Code and DBF integrity check
				
				$c_code = csvfield('CLID');
				
				# Check we can access the same client in DBF
				$dbf_offset = $dbf_firstoff + (($input_count - 1) * $dbf_reclen);
				#dprint("DBF OFF = $dbf_offset (record $input_count)");#
				fseek($fp_dbf, $dbf_offset);
				$dbf_c_code = fread($fp_dbf, 5);
				#dprint("DBF C_CODE: \"$dbf_c_code\"");#
				if (trim($dbf_c_code) != $c_code)
				{
					dprint("*=* CSV/DBF mismatch: CSV client code=\"$c_code\", DBF=\"$dbf_c_code\", " .
							"DBF OFF = $dbf_offset (record $input_count)");
					return ;
				}
				
				
				# --- Name and Address ------------
				
				$c_co_name = sql_encrypt(csvfield('NAME'), false, 'CLIENT2');

				if (is_postcode(csvfield('ADD5')))
				{
					$c_addr_pc = $pm_match;
					$one_record[$col_nums['ADD5']] = trim(str_replace($pm_match, '', csvfield('ADD5')));
				}
				elseif (is_postcode(csvfield('ADD4')))
				{
					$c_addr_pc = $pm_match;
					$one_record[$col_nums['ADD4']] = trim(str_replace($pm_match, '', csvfield('ADD4')));
				}
				elseif (is_postcode(csvfield('ADD3')))
				{
					$c_addr_pc = $pm_match;
					$one_record[$col_nums['ADD3']] = trim(str_replace($pm_match, '', csvfield('ADD3')));
				}
				elseif (is_postcode(csvfield('ADD2')))
				{
					$c_addr_pc = $pm_match;
					$one_record[$col_nums['ADD2']] = trim(str_replace($pm_match, '', csvfield('ADD2')));
				}
				elseif (is_postcode(csvfield('ADD1')))
				{
					$c_addr_pc = $pm_match;
					$one_record[$col_nums['ADD1']] = trim(str_replace($pm_match, '', csvfield('ADD1')));
				}
				else 
					$c_addr_pc = '';
				$c_addr_1 = sql_encrypt(csvfield('ADD1'), false, 'CLIENT2');
				$c_addr_2 = sql_encrypt(csvfield('ADD2'), false, 'CLIENT2');
				$c_addr_3 = sql_encrypt(csvfield('ADD3'), false, 'CLIENT2');
				$c_addr_4 = sql_encrypt(csvfield('ADD4'), false, 'CLIENT2');
				$c_addr_5 = sql_encrypt(csvfield('ADD5'), false, 'CLIENT2');
				$c_addr_pc = sql_encrypt($c_addr_pc, false, 'CLIENT2');
				
				$alpha_code = quote_smart(csvfield('ALPHA'), true);
				
				
				# --- Misc ---------------
				
				$temp = yesno2bool(csvfield('S_INVS'));
				if (($temp === true) || ($temp === false))
					$s_invs_trace = ($temp ? 1 : 0);
				else 
				{
					dprint("*=* S_INVS unexpected value \"" . csvfield('S_INVS') . "\", CLID=$c_code");
					$s_invs_trace = 0;
				}

				$temp = yesno2bool(csvfield('DASS'));
				if (($temp === true) || ($temp === false))
					$deduct_as = ($temp ? 1 : 0);
				else 
				{
					dprint("*=* DASS unexpected value \"" . csvfield('DASS') . "\", CLID=$c_code");
					$deduct_as = 0;
				}

				$comm_percent = 1.0 * csvfield('PERCENT');
				$tr_fee = 1.0 * csvfield('T_FEE');
				$nt_fee = 1.0 * csvfield('NT_FEE');
				$tc_fee = 1.0 * csvfield('TC_FEE');
				$rp_fee = 1.0 * csvfield('C_FEE');
				$sv_fee = 1.0 * csvfield('S_FEE');
				$mn_fee = 1.0 * csvfield('M_FEE');
				$tm_fee = 1.0 * csvfield('TM_FEE');
				$at_fee = 1.0 * csvfield('ATT_FEE');
				
				$temp = yesno2bool(csvfield('PAYBANK'));
				if (($temp === true) || ($temp === false))
					$c_bacs = ($temp ? 1 : 0);
				else 
				{
					dprint("*=* PAYBANK unexpected value \"" . csvfield('PAYBANK') . "\", CLID=$c_code");
					$c_bacs = 0;
				}
				$c_bank_sortcode = sql_encrypt(csvfield('BANKSORT'), false, 'CLIENT2');
				$c_bank_acc_num = sql_encrypt(csvfield('BANKACC'), false, 'CLIENT2');
				$c_bank_acc_name = sql_encrypt(csvfield('BANKNAME'), false, 'CLIENT2');

				$temp = yesno2bool(csvfield('EMAILINV'));
				if (($temp === true) || ($temp === false))
					$inv_emailed = ($temp ? 1 : 0);
				else 
				{
					dprint("*=* EMAILINV unexpected value \"" . csvfield('EMAILINV') . "\", CLID=$c_code");
					$inv_emailed = 0;
				}
				$inv_email_name = sql_encrypt(csvfield('EMAILNAME'), false, 'CLIENT2');
				$inv_email_addr = sql_encrypt(csvfield('EMAILADD'), false, 'CLIENT2');
				$temp = yesno2bool(csvfield('EMAILCOMB'));
				if (($temp === true) || ($temp === false))
					$inv_combine = ($temp ? 1 : 0);
				else 
				{
					dprint("*=* EMAILCOMB unexpected value \"" . csvfield('EMAILCOMB') . "\", CLID=$c_code");
					$inv_combine = 0;
				}

				
				# --- Salesperson --------------
				
				$salesperson_id = 0;
				$salesperson_txt = csvfield('USER');
				if ($salesperson_txt)
				{
					$salesperson_id = user_id_from_old_username($salesperson_txt, '');
					#dprint($sql . " -> $salesperson_id");#
				}
				if ($salesperson_id)
					$salesperson_txt = 'NULL';
				else 
				{
					$salesperson_id = 'NULL';
					$salesperson_txt = quote_smart($salesperson_txt, true);
				}
				
				
				# --- Client Contact --------------
				
				$cc_title = '';
				$cc_firstname = '';
				$cc_lastname = '';
				$cc_main = 1;
				$cc_inv = 1;
				$cc_rep = 1;
				$cc_email_1 = csvfield('EMAIL');
				$cp_phone = csvfield('TEL1') . csvfield('TEL2') . csvfield('TEL3');
				if ((strlen($cp_phone) > 0) && is_numeric_kdb($cp_phone[0], false, false, true) && ($cp_phone[0] != '0'))
					$cp_phone = "0{$cp_phone}";
				$cp_descr = 'Imported from old database, 2016';
				$cc_firstname=$cc_firstname; # keep stupid code-checker quiet.
				$cc_lastname=$cc_lastname; # keep stupid code-checker quiet.
				
				$contact = csvfield('CONTACT');
				if ($contact == '')
				{
					$cc_lastname = '(Blank in old database)';
				}
				else 
				{
					$contact = explode(' ', $contact);
					if (in_array(strtolower($contact[0]), array('mr','mrs','miss','ms','dr','prof','sir','lord')))
					{
						$cc_title = $contact[0];
						$contact[0] = '';
						if ((count($contact) > 1) &&
							in_array(strtolower($contact[1]), array('mr','mrs','miss','ms','dr','prof','sir','lord')))
						{
							$cc_title .= ' & ' . $contact[1];
							$contact[1] = '';
						}
						elseif ((count($contact) > 2) &&
								(($contact[1] == '&') || ($contact[1] == '+')) &&
								in_array(strtolower($contact[2]), array('mr','mrs','miss','ms','dr','prof','sir','lord')))
						{
							$cc_title .= ' & ' . $contact[2];
							$contact[1] = '';
							$contact[2] = '';
						}
					}
					$cc_lastname = $contact[count($contact)-1];
					$contact[count($contact)-1] = '';
					$cc_firstname = trim(str_replace('  ', ' ', implode(' ', $contact)));
				}
				
				$cc_title = quote_smart($cc_title, true);
				$cc_firstname = sql_encrypt($cc_firstname, false, 'CLIENT_CONTACT');
				$cc_lastname = sql_encrypt($cc_lastname, false, 'CLIENT_CONTACT');
				$cc_email_1 = sql_encrypt($cc_email_1, false, 'CLIENT_CONTACT');
				$cp_phone = sql_encrypt($cp_phone, false, 'CLIENT_CONTACT_PHONE');
				$cp_descr = sql_encrypt($cp_descr, false, 'CLIENT_CONTACT_PHONE');
				
				
				# --- VLF Notes ---------------
					
				$cn_note = '';
				$cn_note2 = ''; # if old notes are too big for one record
				$cn_note3 = ''; # if old notes are too big for two records
				$cn_note4 = ''; # if old notes are too big for three record2
				
				#$vlf_from_csv = false;
				#if ($vlf_from_csv)
				#{
				#	$vlf = csvfield('VLF');
				#}
				#else 
				#{
					$dbf_offset += ($dbf_reclen - 1 - 6); # This should now point to the VLF field.
					#dprint("DBF OFF for VLF = $dbf_offset (record $input_count)");#
					fseek($fp_dbf, $dbf_offset);
					$vlf = fread($fp_dbf, 6);
				#}
				$vlf_ascii = "" . ord($vlf[0]) . "," . ord($vlf[1]) . "," . ord($vlf[2]) . "," . 
									ord($vlf[3]) . "," . ord($vlf[4]) . "," . ord($vlf[5]);
				#dprint("VLF ASCII = \"$vlf_ascii\"");#
				
				$dbv_offset = vlf_convert_raw($vlf);
				if ($verbose)
					dprint("VLF/{$input_count}: \"$vlf\" = \"$vlf_ascii\" -> offset=\"$dbv_offset\"");#
				if ($dbv_offset > 0)
				{
					fseek($fp_dbv, $dbv_offset);
					$dbv_readlen = 50000;
					$dbv_buffer = fread($fp_dbv, $dbv_readlen);
					$gotlen = strlen($dbv_buffer);
					if ($gotlen < 10)
					{
						dprint("*=* DBV Read problem: VLF: \"$vlf\" = \"$vlf_ascii\" -> offset=\"$dbv_offset\". " .
							(($gotlen > 0) ? "Only $gotlen" : "No") . " bytes read. " .
							"\$input_count = $input_count", true);
						return;
					}
					# First 6 bytes are size of Pascal TDBVStru structure.
					if ($verbose)
					{
						$six = "" . ord($dbv_buffer[0]) . "," . ord($dbv_buffer[1]) . "," . ord($dbv_buffer[2]) . "," . 
									ord($dbv_buffer[3]) . "," . ord($dbv_buffer[4]) . "," . ord($dbv_buffer[5]);
						dprint("DBV 6-pack = \"$six\"");
					}
					$dbv_len = 0;
					$dbv_len += ord($dbv_buffer[0]);
					$dbv_len += (ord($dbv_buffer[1]) << 8);
					$dbv_len += (ord($dbv_buffer[2]) << 16);
					$dbv_len += (ord($dbv_buffer[3]) << 32);
					if ($verbose)
						dprint("DBV Note length = $dbv_len");#
					$cn_note = substr($dbv_buffer, 6, $dbv_len);
					if ($verbose)
						dprint("Note/A(" . strlen($cn_note) . ")=\"$cn_note\"");
					$cn_note = trim($cn_note);
					#dprint("Note/B(" . strlen($cn_note) . ")=\"$cn_note\"");#
				}
//				else 
//				{
//					#dprint("*=* No DBV offset: VLF: \"$vlf\" = \"$vlf_ascii\" -> offset=\"$dbv_offset\". " .
//					#		"\$input_count = $input_count", true);
//					dprint("Client has no notes: " . csvfield('CLID') . " / " . csvfield('NAME') . 
//							" \$input_count = $input_count");
//				}
				if (strlen($cn_note) >= $v_text_clip)
				{
					$cn_note2 = substr($cn_note, $v_text_clip);
					$cn_note = substr($cn_note, 0, $v_text_clip);
					if (strlen($cn_note2) >= $v_text_clip)
					{
						$cn_note3 = substr($cn_note2, $v_text_clip);
						$cn_note2 = substr($cn_note2, 0, $v_text_clip);
						if (strlen($cn_note3) >= $v_text_clip)
						{
							$cn_note4 = substr($cn_note3, $v_text_clip);
							$cn_note3 = substr($cn_note3, 0, $v_text_clip);
						}
					}
				}
				#dprint("Note/C1(" . strlen($cn_note) . ")=\"$cn_note\"");
				#dprint("Note/C2(" . strlen($cn_note2) . ")=\"$cn_note2\"");
				#dprint("Note/C3(" . strlen($cn_note3) . ")=\"$cn_note3\"");
				#dprint("Note/C4(" . strlen($cn_note4) . ")=\"$cn_note4\"");
				$cn_note = sql_encrypt($cn_note, false, 'CLIENT_NOTE');
				if ($cn_note2)
					$cn_note2 = sql_encrypt($cn_note2, false, 'CLIENT_NOTE');
				if ($cn_note3)
					$cn_note3 = sql_encrypt($cn_note3, false, 'CLIENT_NOTE');
				if ($cn_note4)
					$cn_note4 = sql_encrypt($cn_note4, false, 'CLIENT_NOTE');
				if ($verbose)
					dprint("SQL Note=\"$cn_note\"" . ($cn_note2 ? " and \"$cn_note2\"" : '') . 
						($cn_note3 ? " and \"$cn_note3\"" : '') . ($cn_note4 ? " and \"$cn_note4\"" : ''));

					
				# --- Old stuff for CLIENT_Z table ---------------
				
				$z_rep_ord = csvfield('REP_ORD');
				$z_retr = csvfield('RETR');
				$z_jobs = csvfield('JOBS');
				$z_lastrep = (csvfield('LASTREP', 'uk') ? ("'" . csvfield('LASTREP', 'uk') . "'") : 'NULL');
				$z_vps = csvfield('VPS');
				$z_acc = csvfield('ACC');
				$z_to_us = (csvfield('TO_US', 'uk') ? ("'" . csvfield('TO_US', 'uk') . "'") : 'NULL');
				$z_forwarded = (csvfield('FORWARDED', 'uk') ? ("'" . csvfield('FORWARDED', 'uk') . "'") : 'NULL');
				$z_direct = (csvfield('DIRECT', 'uk') ? ("'" . csvfield('DIRECT', 'uk') . "'") : 'NULL');
				$z_cspent = (csvfield('CSPENT', 'uk') ? ("'" . csvfield('CSPENT', 'uk') . "'") : 'NULL');
				$z_tu_del = csvfield('TU_DEL');
				$z_fo_del = csvfield('FO_DEL');
				$z_di_del = csvfield('DI_DEL');
				$z_cs_del = csvfield('CS_DEL');
				$z_ap_def = csvfield('AP_DEF');
				$z_ap_amount = csvfield('AP_AMOUNT');
				$z_ap_code = csvfield('AP_CODE');
				$z_notraces = csvfield('NOTRACES');
				$z_mark = csvfield('MARK');
				
				
				# --- SQL Insertions --------------------------------------------
				
				# --- CLIENT2 ---------------------------------------------------
				
				$fields = "C_CODE,  C_CO_NAME,  C_ADDR_1,  C_ADDR_2,  C_ADDR_3,  C_ADDR_4,  C_ADDR_5,  C_ADDR_PC,  ";
				$values = "$c_code, $c_co_name, $c_addr_1, $c_addr_2, $c_addr_3, $c_addr_4, $c_addr_5, $c_addr_pc, ";
				
				$fields .= "ALPHA_CODE,  SALESPERSON_ID,  SALESPERSON_TXT,  CREATED_DT, IMPORTED, ";
				$values .= "$alpha_code, $salesperson_id, $salesperson_txt, $sqlNow,    $sqlTrue, ";
				
				$fields .= "S_INVS_TRACE,  DEDUCT_AS,  COMM_PERCENT,  ";
				$values .= "$s_invs_trace, $deduct_as, $comm_percent, ";
				
				$fields .= "TR_FEE,  NT_FEE,  TC_FEE,  RP_FEE,  SV_FEE,  MN_FEE,  TM_FEE,  AT_FEE,  ";
				$values .= "$tr_fee, $nt_fee, $tc_fee, $rp_fee, $sv_fee, $mn_fee, $tm_fee, $at_fee, ";

				$fields .= "C_BACS,  C_BANK_SORTCODE,  C_BANK_ACC_NUM,  C_BANK_ACC_NAME,  C_VAT,    ";
				$values .= "$c_bacs, $c_bank_sortcode, $c_bank_acc_num, $c_bank_acc_name, $sqlTrue, ";

				$fields .= "INV_EMAILED,  INV_EMAIL_NAME,  INV_EMAIL_ADDR,  INV_COMBINE ";
				$values .= "$inv_emailed, $inv_email_name, $inv_email_addr, $inv_combine";

				$sql = "INSERT INTO CLIENT2 ($fields) VALUES ($values)";
				if ($verbose)
					dprint($sql);
				$client2_id = sql_execute($sql); # no need to audit
				if ($verbose)
					dprint("-> ID $client2_id");
				
				# --- CLIENT_NOTE ---------------------------------------------------
				
				$fields = "CLIENT2_ID,  CN_NOTE,  IMPORTED, CN_ADDED_ID,    CN_ADDED_DT";
				$values = "$client2_id, $cn_note, $sqlTrue, $super_user_id, $sqlNow";
				
				$sql = "INSERT INTO CLIENT_NOTE ($fields) VALUES ($values)";
				if ($verbose)
					dprint("[" . strlen($cn_note) . "] " . $sql);
				$client_note_id = sql_execute($sql);
				if ($verbose)
					dprint("-> ID $client_note_id");
					
				if ($cn_note2)
				{
					$fields = "CLIENT2_ID,  CN_NOTE,   IMPORTED, CN_ADDED_ID,    CN_ADDED_DT";
					$values = "$client2_id, $cn_note2, $sqlTrue, $super_user_id, $sqlNow";
					
					$sql = "INSERT INTO CLIENT_NOTE ($fields) VALUES ($values)";
					if ($verbose)
						dprint("[" . strlen($cn_note2) . "] " . $sql);
					$client_note_id = sql_execute($sql);
					if ($verbose)
						dprint("-> ID $client_note_id");
				}
				if ($cn_note3)
				{
					$fields = "CLIENT2_ID,  CN_NOTE,   IMPORTED, CN_ADDED_ID,    CN_ADDED_DT";
					$values = "$client2_id, $cn_note3, $sqlTrue, $super_user_id, $sqlNow";
					
					$sql = "INSERT INTO CLIENT_NOTE ($fields) VALUES ($values)";
					if ($verbose)
						dprint("[" . strlen($cn_note3) . "] " . $sql);
					$client_note_id = sql_execute($sql);
					if ($verbose)
						dprint("-> ID $client_note_id");
				}
				if ($cn_note4)
				{
					$fields = "CLIENT2_ID,  CN_NOTE,   IMPORTED, CN_ADDED_ID,    CN_ADDED_DT";
					$values = "$client2_id, $cn_note4, $sqlTrue, $super_user_id, $sqlNow";
					
					$sql = "INSERT INTO CLIENT_NOTE ($fields) VALUES ($values)";
					if ($verbose)
						dprint("[" . strlen($cn_note4) . "] " . $sql);
					$client_note_id = sql_execute($sql);
					if ($verbose)
						dprint("-> ID $client_note_id");
				}
				
				# --- CLIENT_Z ----------------------------------
				
				$fields = "CLIENT2_ID,  Z_JOBS,    Z_REP_ORD,    Z_RETR,    Z_LASTREP,  Z_VPS,    Z_ACC,    Z_TO_US,  ";
				$values = "$client2_id, '$z_jobs', '$z_rep_ord', '$z_retr', $z_lastrep, '$z_vps', '$z_acc', $z_to_us, ";
				
				$fields .= "Z_FORWARDED,  Z_DIRECT,  Z_CSPENT,  Z_TU_DEL,    Z_FO_DEL,    Z_DI_DEL,    Z_CS_DEL,    ";
				$values .= "$z_forwarded, $z_direct, $z_cspent, '$z_tu_del', '$z_fo_del', '$z_di_del', '$z_cs_del', ";
				
				$fields .= "Z_AP_DEF,    Z_AP_AMOUNT,    Z_AP_CODE,    Z_NOTRACES,    Z_MARK   ";
				$values .= "'$z_ap_def', '$z_ap_amount', '$z_ap_code', '$z_notraces', '$z_mark'";
				
				$sql = "INSERT INTO CLIENT_Z ($fields) VALUES ($values)";
				if ($verbose)
					dprint($sql);
				sql_execute($sql);
				
				# --- CLIENT_CONTACT ----------------------------------
				
				$fields = "CLIENT2_ID,  CC_TITLE,  CC_FIRSTNAME,  CC_LASTNAME,  CC_MAIN,  CC_INV,  CC_REP,  CC_EMAIL_1,  ";
				$values = "$client2_id, $cc_title, $cc_firstname, $cc_lastname, $cc_main, $cc_inv, $cc_rep, $cc_email_1, ";
				
				$fields .= "IMPORTED";
				$values .= "$sqlTrue";
				
				$sql = "INSERT INTO CLIENT_CONTACT ($fields) VALUES ($values)";
				if ($verbose)
					dprint($sql);
				$client_contact_id = sql_execute($sql);
				if ($verbose)
					dprint("-> ID $client_contact_id");
				
				# --- CLIENT_CONTACT_PHONE ----------------------------------
				
				$fields = "CLIENT_CONTACT_ID,  CP_PHONE,  CP_DESCR,  CP_MAIN,  IMPORTED";
				$values = "$client_contact_id, $cp_phone, $cp_descr, $sqlTrue, $sqlTrue";
				
				$sql = "INSERT INTO CLIENT_CONTACT_PHONE ($fields) VALUES ($values)";
				if ($verbose)
					dprint($sql);
				sql_execute($sql);
				
				if (  (($salesperson_id != 'NULL') && (0 < $salesperson_id)) || (($salesperson_txt != 'NULL') && (0 < strlen($salesperson_txt)))  )
				{
					# --- SALESPERSON ----------------------------------

					$fields = "CLIENT2_ID,  SP_USER_ID,      SP_TXT,           SP_DT";
					$values = "$client2_id, $salesperson_id, $salesperson_txt, NULL";

					$sql = "INSERT INTO SALESPERSON ($fields) VALUES ($values)";
					if ($verbose)
						dprint($sql);
					$new_salesperson_id = sql_execute($sql);
					if ($verbose)
						dprint("-> ID $new_salesperson_id");
					
					if (($salesperson_id != 'NULL') && (0 < $salesperson_id))
					{
						$sql = "UPDATE USERV SET U_SALES_ISH=$sqlTrue WHERE USER_ID=$salesperson_id";
						if ($verbose)
							dprint($sql);
						sql_execute($sql);
					}
				}
			}
			else 
			{
				dprint("End of Clients");
				break; # while(true)
			}
			
			$input_count++;
			if ((0 < $stop_after) && ($stop_after < $input_count))
				break; # while(true)
				
		} # while(true)

		$client_count = $input_count - 1;
		dprint("Imported $client_count clients");
		
		fclose($fp_dbf);
		fclose($fp_dbv);
		fclose($fp_csv);
	}
	else
	{
		if (!$fp_dbf)
			dlog("*=* Failed to fopen(\"$dirname/$dbf\",'r')");
		if (!$fp_dbv)
			dlog("*=* Failed to fopen(\"$dirname/$dbv\",'r')");
		if (!$fp_csv)
			dlog("*=* Failed to fopen(\"$dirname/$csv\",'r')");
	}

	# Create clients that are referenced from old jobs but don't exist in CLIENTS.DBF.
	
	$c_co_name = sql_encrypt('(Unknown client 2)', '', 'CLIENT2');
	
	$fields = "C_CODE, C_CO_NAME,  CREATED_DT, IMPORTED ";
	$values = "2,      $c_co_name, $sqlNow,    $sqlTrue ";
	
	$sql = "INSERT INTO CLIENT2 ($fields) VALUES ($values)";
	#if ($verbose)
		dprint($sql);
	$client2_id = sql_execute($sql); # no need to audit
	#if ($verbose)
		dprint("-> ID $client2_id");

	# --------------------------------------------------------------------------
	# Now import Client Groups from EQUIVCLI.csv
	# --------------------------------------------------------------------------

	$sys = 'c'; # collect

	$stop_after = 0;#
	$stop_margin = 1000;
	$input_count = 0;
	$output_count = 0;
	
	$dirname = "import-vilcol/{$sys}";
	$csv = "EQUIVCLI.csv";
	$fp_csv = fopen("$dirname/$csv", 'r');
	if ($fp_csv)
	{
		sql_encryption_preparation('CLIENT_GROUP');
		
		$col_nums = array();
		
		$while_safety = 0; # to avoid infinite-looping
		while (true)
		{
			$verbose = false;
			if ((0 < $stop_after) && ($input_count <= $stop_after) && (($stop_after - $input_count) < $stop_margin))
				$verbose = true;
			$print_buffer = array();
			if ($verbose)
				$print_buffer[] = "Starting record, input_count=$input_count";
				
			$while_safety++;
			if ($while_safety > 100000) # 100,000 clients
				break; # while(true)
				
			$one_record = fgetcsv($fp_csv);
			if ($verbose)
				$print_buffer[] = ((($input_count == 0) ? "Headers: " : "Phone: ") . print_r($one_record,1));#
			if ($input_count == 0)
			{
				foreach ($one_record as $num => $field)
					$col_nums[$field] = $num;
				if ($verbose)
					$print_buffer[] = ("col_nums: " . print_r($col_nums,1));#
			}
			elseif ($one_record && (count($one_record) > 0))
			{
				$trc_code = 1 * csvfield('TRACECLI');
				if ($trc_code > 0)
				{
					$col_code = 1 * csvfield('COLLECTCLI');
					if ($col_code > 0)
					{
						$trc_id = client_id_from_code($trc_code);
						if ($trc_id > 0)
						{
							$col_id = client_id_from_code($col_code);
							if ($col_id > 0)
							{
								$sql = "SELECT CLIENT_GROUP_ID FROM CLIENT2 WHERE CLIENT2_ID=$trc_id";
								sql_execute($sql);
								$group_id = 0;
								while (($newArray = sql_fetch()) != false)
									$group_id = $newArray[0];
								if ($group_id > 0)
								{
									# Update Collect client to be in same group as Trace client
									$sql = "UPDATE CLIENT2 SET CLIENT_GROUP_ID=$group_id WHERE CLIENT2_ID=$col_id";
									dprint($sql);
									sql_execute($sql);
								}
								else
								{
									$sql = "SELECT CLIENT_GROUP_ID FROM CLIENT2 WHERE CLIENT2_ID=$col_id";
									sql_execute($sql);
									$group_id = 0;
									while (($newArray = sql_fetch()) != false)
										$group_id = $newArray[0];
									if ($group_id > 0)
									{
										# Update Trace client to be in same group as Collect client
										$sql = "UPDATE CLIENT2 SET CLIENT_GROUP_ID=$group_id WHERE CLIENT2_ID=$trc_id";
										dprint($sql);
										sql_execute($sql);
									}
									else
									{
										# Neither client is yet in a group, so create one.
										$gname = "GROUP_{$trc_code}_{$col_code}";
										$sql = "INSERT INTO CLIENT_GROUP (GROUP_NAME) VALUES (" . sql_encrypt($gname, false, 'CLIENT_GROUP') . ")";
										dprint($sql);
										$group_id = sql_execute($sql);
										
										$sql = "UPDATE CLIENT2 SET CLIENT_GROUP_ID=$group_id WHERE CLIENT2_ID=$trc_id";
										dprint($sql);
										sql_execute($sql);

										$sql = "UPDATE CLIENT2 SET CLIENT_GROUP_ID=$group_id WHERE CLIENT2_ID=$col_id";
										dprint($sql);
										sql_execute($sql);
									}
								}
								$output_count++;
							}
							else
								$print_buffer[] = "No Client ID found from COLLECTCLI \"" . csvfield('COLLECTCLI') . "\", input_count=$input_count";
						}
						else
							$print_buffer[] = "No Client ID found from TRACECLI \"" . csvfield('TRACECLI') . "\", input_count=$input_count";
					}
					else
						$print_buffer[] = "COLLECTCLI not a number \"" . csvfield('COLLECTCLI') . "\", input_count=$input_count";
				}
				else
					$print_buffer[] = "TRACECLI not a number \"" . csvfield('TRACECLI') . "\", input_count=$input_count";

				foreach ($print_buffer as $print_line)
					dprint($print_line);
			}
			else 
			{
				dprint("End of Equiv");
				break; # while(true)
			}
			
			$input_count++;
			if ((0 < $stop_after) && ($stop_after < $input_count))
				break; # while(true)
				
		} # while(true)

		$equiv_count = $input_count - 1;
		dprint("Imported $output_count Equiv Client records from $equiv_count records");
		
		fclose($fp_csv);
	}
	else
	{
		if (!$fp_csv)
			dlog("*=* Failed to fopen(\"$dirname/$csv\",'r')");
	}
	
} # import_clients()

function init_sys()
{
	# See spec for initial population, ID values 1 to 10.
	# No need to audit these.

	global $vat_number; # settings.php

	# Insert/Update base entries
	$id = 0;
	misc_info_write("c", "VILNO_C", "int", 0, '', false); # 1
	$id++;
	misc_info_write("t", "VILNO_T", "int", 0, '', false); # 2 
	$id++;
	misc_info_write("c", "SEQ_C", "int", 0, '', false); # 3
	$id++;
	misc_info_write("t", "SEQ_T", "int", 0, '', false); # 4
	$id++;
	misc_info_write("c", "INVNO_C", "int", 0, '', false); # 5
	$id++;
	misc_info_write("t", "INVNO_T", "int", 0, '', false); # 6
	$id++;
	misc_info_write("c", "CREDNO_C", "int", 0, '', false); # 7
	$id++;
	misc_info_write("t", "CREDNO_T", "int", 0, '', false); # 8
	$id++;
	misc_info_write("*", "VAT_RATE", "dec", 20.0, '', false); # 9
	$id++;
	misc_info_write("*", "VAT_NUM", "txt", $vat_number, '', false); # 10
	$id++;
	misc_info_write("*", "APP_PW", "enc", 'boo', '', false); # 11
	$id++;

	$sql = "DELETE FROM MISC_INFO WHERE MISC_INFO_ID>{$id}";
	dprint($sql, true);
	sql_execute($sql); # no need to audit
	
	
	if (!sql_table_exists('STUFF'))
	{
		$sql = "CREATE TABLE [dbo].[STUFF](
					[STUFF] [varchar](4000) NOT NULL
				) ON [PRIMARY]";
		dprint($sql, true);
		sql_execute($sql); # no need to audit

		# We always need one record in STUFF
		$sql = "INSERT INTO STUFF (STUFF) VALUES ('Hello')";
		dprint($sql, true);
		sql_execute($sql); # no need to audit
	}	
}

function init_users()
{
	# *************************
	#
	# *** WARNING ***
	#
	# This will result in all users (except me) having new ID values
	#
	# *************************
	
	global $NEW_USERS; # lib_users.php
	global $sqlNow;
	
	# Delete all users except me. Create all other users from Steve's crib sheet USERS_by_Steve.xlsx (08/12/15).
	
	$sql = "DELETE FROM USER_PERM_LINK WHERE USER_ID <> 1";
	dprint($sql, true);
	sql_execute($sql); # no need to audit
	
	$sql = "DELETE FROM USERV WHERE USER_ID <> 1";
	dprint($sql, true);
	sql_execute($sql); # no need to audit

//	$sql = "SET IDENTITY_INSERT USERV ON";
//	dprint($sql, true);
//	sql_execute($sql);
	
	$user_id = 2; # corresponds to the first non-null element in $NEW_USERS
	foreach ($NEW_USERS as $uu)
	{
		if ($uu)
		{
			$ii = 0;
			$username_raw = $uu[0][$ii];
			$username = sql_encrypt($uu[0][$ii++], false, 'USERV');
			$password = sql_encrypt($uu[0][$ii++], false, 'USERV');
			$firstname = quote_smart($uu[0][$ii++], true);
			$lastname = sql_encrypt($uu[0][$ii++], false, 'USERV');
			$initials = quote_smart($uu[0][$ii++], true);
			$role_c = $uu[0][$ii++];
			$role_t = $uu[0][$ii++];
			$role_a = $uu[0][$ii++];
			$sales = $uu[0][$ii++];
			$historic = $uu[0][$ii++];
			$is_enabled = ($historic ? 0 : 1);
			
			$system = 0;
			$house = 0;
			$imported = 0;
			if ($username_raw == 'SYSTEM')
				$system = 1;
			elseif ($username_raw == 'HOUSE')
				$house = 1;
			else
				$imported = 1;
			
			# Feedback #21.
			if (($is_enabled && (!$historic)) || $house)
				$sales = 1;
			#UPDATE USERV SET U_SALES=1 WHERE (U_SYSTEM=0) AND ((IS_ENABLED=1 AND U_HISTORIC=0) OR (U_HOUSE=1))
			
			$fields = "USERNAME,  PASSWORD, U_FIRSTNAME, U_LASTNAME, U_INITIALS, USER_ROLE_ID_C, USER_ROLE_ID_T, ";
			$values = "$username, $password, $firstname, $lastname,  $initials,  $role_c,        $role_t,        ";
			
			$fields .= "USER_ROLE_ID_A, U_SALES, U_HISTORIC, IS_ENABLED,  U_SYSTEM, U_HOUSE, U_IMPORTED, CREATED_DT";
			$values .= "$role_a,        $sales,  $historic,  $is_enabled, $system,  $house,  $imported,  $sqlNow";
			
			$sql = "SET IDENTITY_INSERT USERV ON;" .
					"INSERT INTO USERV (USER_ID, $fields) VALUES ($user_id, $values);" .
					"SET IDENTITY_INSERT USERV OFF;";
			dprint($sql, true);
			$new_id = sql_execute($sql); # no need to audit
			dprint(" -> $new_id");
			$user_id++;
		}
	}
	
//	$sql = "SET IDENTITY_INSERT USERV OFF";
//	dprint($sql, true);
//	sql_execute($sql);
}

function find_shadow($list, $username)
{
	$ix = -1;
	$ii = 0;
	foreach ($list as $one_user)
	{
		if ($one_user['USER'] == $username)
		{
			$ix = $ii;
			break;
		}
		$ii++;
	}
	return $ix;
}

function import_users()
{
	# Required:
	#	No files
	
	$enabled = false; #
	if (!$enabled)
	{
		dprint("This import function is disabled", true);
		return;
	}
	
	print "<h3>Creating USERS from Steve's crib sheet</h3>";
	
	init_users();

} # import_users()

function test_collect_jobs()
{
	global $col_nums;
	global $fix_list;
	global $input_count; # from caller e.g. import_jobs()
	global $one_record;
	
	# Import 31/12/16:
	# import_2016_12_31_1158.log
	# 2016-12-31 11:58:05/   POST=Array
	# (
	#     [task] => i_jobs_test
	#     [tc] => 
	#     [other] => 
	# )

	print "<h3>Testing Collect Jobs</h3>";
	
	$start_time = time();
	
	$sys = 'c';
	$dirname = "import-vilcol/{$sys}";
	$csv = "COLLECT.csv";
	$fp_csv = fopen("$dirname/$csv", 'r');

	if ($fp_csv)
	{
		$while_safety = 0; # to avoid infinite-looping
		$input_count = 0;
		
		while (true)
		{
			$verbose = false;
			
			$while_safety++;
			if ($while_safety > 100000) # 1,000,000 jobs
			{
				dprint("*=* (Sys=$sys) Safety Ejection!");
				break; # while(true)
			}

			if ($verbose)
				dprint("=== Record $input_count ===");
				
			$one_record = fgetcsv($fp_csv);

			if ($input_count == 0)
			{
				# Header record
				foreach ($one_record as $num => $field)
					$col_nums[$field] = $num;
				if ($verbose)
					dprint("col_nums: " . print_r($col_nums,1));#
			}
			else
			{
				# Data record
				if ($one_record && (count($one_record) > 0)) # test for end-of-records
				{
					if (count($one_record) != 89)
					{
						dprint("Record only has " . count($one_record) . " fields, input_count=$input_count, " .
								"sequence=" . csvfield('SEQUENCE'));
						dprint("...col_nums: " . print_r($col_nums,1));
						dprint("...record: " . print_r($one_record,1));
						if (in_array(csvfield('SEQUENCE'), $fix_list))
							dprint("This is in the Fix List");
						elseif (in_fix_aux_list($one_record))
							dprint("This is in the Fix/Aux List");
						else
							dprint("This IS NOT in the Fix List");
						dprint("<hr>");
					}
				}
				else
				{
					dprint("End of Jobs");
					break; # while(true)
				}
			}
			$input_count++;
			if (($input_count % 10000) == 0)
				dlog("test_collect_jobs(): done $input_count input records so far");
		} # while(true)

		$time_taken = 1.0 * (time() - $start_time) / 60.0;
		dprint("Time taken: $time_taken mins");
		
		fclose($fp_csv);
	}
	else
		dlog("*=* (Sys=$sys) Failed to fopen(\"$dirname/$csv\",'r')");
	
} # test_collect_jobs()

function import_jobs()
{
	# Before calling this function:
	#	- Export TRACE/TRACES.DBF as TRACES.csv using DBF Manager with default settings.
	#	- Export COLLECT/COLLECT.DBF as COLLECT.csv using DBF Manager with default settings.
	# Required:
	#	t/TRACES.DBF
	#	t/TRACES.DBV
	#	t/TRACES.csv
	#	c/COLLECT.DBF
	#	c/COLLECT.DBV
	#	c/COLLECT.csv
	
	# Archived jobs: if a job has a sequence number of -1 and a VILNo of 0 this probably means that the job has
	# been archived and so we need to look up some other data items in the COLLECT_DBF_2015	table to find the
	# proper sequence number and VILNo.
	
	# Import 31/12/16:
	# Trace:
	#	import_2016_12_31_1200.log
	#	2016-12-31 12:00:35/   POST=Array
	#	(
	#	    [task] => i_jobs
	#	    [tc] => t
	#	    [other] => 0
	#	)
	#	2016-12-31 12:00:38/1 *=* (Sys=t) Non-numeric or illegal VILNO "0" (record 87, VILNO="0", SEQUENCE="452765")
	#	2016-12-31 12:00:38/1 *=* (Sys=t) Non-numeric or illegal VILNO "0" (record 106, VILNO="0", SEQUENCE="627104")
	#	2016-12-31 12:21:25/1 *=* (Sys=t) Non-numeric or illegal VILNO "0" (record 46042, VILNO="0", SEQUENCE="796840")
	#	2016-12-31 12:22:01/1 Imported 47548 jobs from a Traces file of 47549 jobs (and 1 header record).
	#	Input file had min sequence of 473, max of 798347, 
	#	had 1 blank records and had 0 zero sequences.
	#	Trace letters imported: 0 (from 0). Collection letters imported: 0 (from 0).
	#	Trace letters found in DBV: 47493. Found then lost: 0. Not found in DBV: 55.
	#	Timek taken: 21.433333333333 mins (27.046353158913 secs per 1000 jobs)
	# Collect:
	#	First Pass:
	#	import_2016_12_31_1251.log
	#	2016-12-31 12:51:57/   POST=Array
	#	(
	#	    [task] => i_jobs
	#	    [tc] => c
	#	    [other] => 0
	#	)
	#	2016-12-31 12:51:58/1 *=* (Sys=c) Non-numeric or illegal VILNO "0" (record 1, VILNO="0", SEQUENCE="90000002")
	#	2016-12-31 12:57:02/1 *=* (Sys=c) Non-numeric or illegal SEQUENCE "0" changed to 101 (record 12117, VILNO="0", $seq_zero_count=1)
	#	2016-12-31 12:57:02/1 *=* (Sys=c) Non-numeric or illegal VILNO "0" (record 12117, VILNO="0", SEQUENCE="0")
	#	2016-12-31 12:57:04/1 *=* (Sys=c) Non-numeric or illegal SEQUENCE "0" changed to 102 (record 12181, VILNO="0", $seq_zero_count=2)
	#	2016-12-31 12:57:04/1 *=* (Sys=c) Non-numeric or illegal VILNO "0" (record 12181, VILNO="0", SEQUENCE="0")
	#	Collection jobs done so far: 84,251.
	#	Second Pass:
	#	import_2016_12_31_1338.log
	#	2016-12-31 13:44:04/1 *=* (Sys=c) Non-numeric or illegal SEQUENCE "-1" changed to 103 (record 84252, VILNO="0", $seq_zero_count=3)
	#	2016-12-31 13:44:04/1 *=* (Sys=c) Non-numeric or illegal VILNO "0" (record 84252, VILNO="0", SEQUENCE="-1")
	#	2016-12-31 13:50:44/1 *=* (Sys=c) Non-numeric or illegal VILNO "0" (record 98633, VILNO="0", SEQUENCE="90708469")
	#	2016-12-31 14:13:46/1 *=* (Sys=c) Non-numeric or illegal VILNO "0" (record 159995, VILNO="0", SEQUENCE="90586694")
	#	2016-12-31 15:05:06/1 *=* (Sys=c) Non-numeric or illegal VILNO "-1350250" (record 278668, VILNO="-1350250", SEQUENCE="90706287")
//		2016-12-31 15:19:49/1 *=* (Sys=c) Unrecognised CLIENT CODE="8" (record 311862; VilNo=1383632 Seq=90739677) $one_record=Array
//			(
//				[0] => 90739677
//				[1] => yes
//				[2] => 1383632
//				[3] => VILCOL
//				[4] => Xu
//				[5] => Awei
//				[6] => Miss
//				[7] => 
//				[8] => Flat 13 Choudhury Mansions
//				[9] => 70Pembroke Street
//				[10] => London
//				[11] => 
//				[12] => 
//				[13] => **UNPAID T MOBILE BILL
//				[14] => **Sigma Ltd Bought debt
//				[15] => **BAN58455517 AGREEMENT
//				[16] => **
//				[17] => **
//				[18] => 
//				[19] => 
//				[20] => 
//				[21] => 
//				[22] => 
//				[23] => 
//				[24] => 
//				[25] => 
//				[26] => 
//				[27] => NL
//				[28] => 
//				[29] => 
//				[30] => 
//				[31] => 
//				[32] => 
//				[33] => 01/02/2013
//				[34] => 8
//				[35] => DEN
//				[36] => SIG/1271016
//				[37] => no
//				[38] => 1148.75
//				[39] => 0
//				[40] => 28/01/2013
//				[41] => 
//				[42] => no
//				[43] => yes
//				[44] => no
//				[45] => no
//				[46] => 28/01/2013
//				[47] => 
//				[48] => 
//				[49] => 0
//				[50] => 
//				[51] => 0
//				[52] => 
//				[53] => 0
//				[54] => 
//				[55] => 
//				[56] => 0
//				[57] => 
//				[58] => 
//				[59] => 1148.75
//				[60] => 
//				[61] => 
//				[62] => Letter 2
//				[63] => 01/03/2013
//				[64] => 0
//				[65] => 
//				[66] => no
//				[67] => no
//				[68] => no
//				[69] => no
//				[70] => no
//				[71] => no
//				[72] => no
//				[73] => no
//				[74] => no
//				[75] => no
//				[76] => 
//				[77] => 0
//				[78] => 
//				[79] => 
//				[80] => 
//				[81] => 
//				[82] => 
//				[83] => 
//				[84] => 
//				[85] => 
//				[86] => 
//				[87] => 
//				[88] => +¾-öîõ
//			)
	#	Collection jobs done so far: 358,580.
	#	Third Pass:
	#	import_2016_12_31_1556.log
	#	Imported 114611 jobs from a Collections file of 473202 jobs (and 1 header record).
	#		NOTE: there are now 473,191 collection jobs in the database.
	#	Input file had min sequence of 90000002, max of 90902920, 
	#	had 11 blank records and had 9 zero sequences.
	#	Trace letters imported: 0 (from 0). Collection letters imported: 0 (from 0).
	#	Trace letters found in DBV: 0. Found then lost: 0. Not found in DBV: 0.
	#	Time taken: 64.583333333333 mins (33.810018235597 secs per 1000 jobs)
	#	Total collection jobs: 473,191.
	
	# 26/09/16 on www.vilcoldb.com:
	#		Imported 45159 jobs from a Traces file of 45159 jobs (and 1 header record).
	#		Input file had min sequence of 473, max of 795958, and had 0 zero sequences.
	#		Time taken: 15.55 mins (20.660333488341 secs per 1000 jobs)
	# 27/09/16:
	#		Collect Jobs import: input file had 438936 jobs, wrote 438925 Collect jobs to JOB table (incrementally).
	
	global $col_nums; # for csvfield()
	global $crlf;
	global $incr_sequence;
	global $incr_vilno;
	global $input_count; # for csvfield()
	global $j_sequence; # for csvfield()
	global $letters_open_close; # from sql_get_letter_open_close()
	global $one_record; # for csvfield()
	global $pm_match; # postcode found by is_postcode()
	global $sqlFalse;
	global $sqlNow;
	global $sqlTrue;
	global $tc;
	global $USER; # the user who is logged in
	global $v_text_clip; # settings.php
		
	$enabled = false; #
	if (!$enabled)
	{
		dprint("This import function is disabled", true);
		return;
	}
	if (($tc != 'c') && ($tc != 't') && ($tc != '*'))
	{
		dlog("*=* import_jobs() - illegal tc \"$tc\"");
		return;
	}
	
	$incr = post_val('other', true);
	if (($incr != 0) && ($tc != 'c'))
	{
		dlog("*=* import_jobs() - illegal args tc=\"$tc\" incr=\"" . post_val('other') . "\"");
		return;
	}
	
	print "<h3>Importing Jobs (" . (($tc == 'c') ? "Collect" : "Trace") . " system)" . ($incr ? "-INCREMETALLY" : '') . "</h3>";
	
	$start_time = time();
	set_time_limit(60 * 60 * 8); # 8 hours
	
	$incr_vilno = 0;
	$incr_sequence = 0;
	$incr_found = false;
	if ($incr)
	{
		$rc = init_jobs_incr_c(); # this sets $incr_vilno and $incr_sequence
		if ($rc != 0)
		{
			dlog("import_jobs(): (tc=$tc) error from init_jobs_incr_c(): $rc");
			return;
		}
	}
	else
	{
		init_jobs($tc);
		if ($tc == '*')
		{
			dprint("Old Jobs all deleted");
			return;
		}
	}
	
	$sql = "SELECT COUNT(*) FROM JOB_LETTER WHERE LETTER_TYPE_ID <= 25";
	sql_execute($sql);
	$letters_t_start = 0;
	while (($newArray = sql_fetch()) != false)
		$letters_t_start = $newArray[0];

	$sql = "SELECT COUNT(*) FROM JOB_LETTER WHERE 26 <= LETTER_TYPE_ID";
	sql_execute($sql);
	$letters_c_start = 0;
	while (($newArray = sql_fetch()) != false)
		$letters_c_start = $newArray[0];

	$letters_dbv_yes = 0;
	$letters_dbv_no = 0;
	$letters_dbv_yes_then_no = 0;
	
	$stop_after = 0;#
	$stop_margin = 0;
	$input_count = 0;
	
	$sys = $tc; # Either 't' or 'c' from this point
	$dirname = "import-vilcol/{$sys}";
	if ($sys == 't')
	{
		$sys_txt = "Traces";

		$dbf = "TRACES.DBF"; # In files dated Jan 2015, there are 57,808 jobs. Takes 24 mins to import on RDR server.
		$dbv = "TRACES.DBV";
		$csv = "TRACES.csv";
		
		# File offsets into DBF
		$dbf_firstoff = 3683; # offset into DBF of first data record (after the header record)
		$dbf_reclen = 3780; # length of each data record in DBF
	}
	else
	{
		$sys_txt = "Collections";

		$dbf = "COLLECT.DBF"; # In files dated Jan 2015, there are 427,058 jobs. Takes 161 mins to import on RDR server.
		$dbv = "COLLECT.DBV";
		$csv = "COLLECT.csv";
		
		# File offsets into DBF
		$dbf_firstoff = 2883; # offset into DBF of first data record (after the header record)
		$dbf_reclen = 1460; # length of each data record in DBF
	}
	
	$fp_dbf = fopen("$dirname/$dbf", 'r');
	$fp_dbv = fopen("$dirname/$dbv", 'r');
	$fp_csv = fopen("$dirname/$csv", 'r');
	if ($fp_dbf && $fp_dbv && $fp_csv)
	{
		sql_encryption_preparation('JOB');
		sql_encryption_preparation('JOB_SUBJECT');
		sql_encryption_preparation('JOB_LETTER');
		sql_encryption_preparation('JOB_PHONE');
		sql_encryption_preparation('JOB_NOTE');
		sql_get_letter_open_close(); # into $letters_open_close
		
		$col_nums = array();
		
		$seq_min = 0;
		$seq_max = 0;
		$seq_zero_count = 0;
		$seq_zero_replacement = 101;
		$job_count = 0;
		$blank_count = 0;
		
		$while_safety = 0; # to avoid infinite-looping
		while (true)
		{
			$verbose = false;
			if ((0 < $stop_after) && ($input_count <= $stop_after) && (($stop_after - $input_count) < $stop_margin))
				$verbose = true;
			
			$while_safety++;
			if ($while_safety > 800000) # 800,000 jobs
			{
				dlog("*=* (Sys=$sys) Safety Ejection!");
				break; # while(true)
			}

			if ($verbose)
				dprint("=== Record $input_count ===");
				
			$one_record = fgetcsv($fp_csv);
			
			# Check for a blank data record
			if (($input_count > 0) && $one_record && (count($one_record) > 0))
			{
				$blank_rec = true;
				foreach ($one_record as $or)
				{
					if (($or != 0) && (trim($or) != ''))
					{
						$blank_rec = false;
						break; # from foreach()
					}
				}
				if ($blank_rec)
				{
					$input_count++;
					$blank_count++;
					continue; # around while(true)
				}
			}
			
			if ($input_count > 0) # skip header record
			{
				$vilno_2015 = 0;
				$sequence_2015 = 0;
				$vs_2015 = 0;
				
				if ($one_record && (count($one_record) > 0)) # test for end-of-records
				{
					$seq_raw = csvfield('SEQUENCE');
					$j_sequence = 1 * $seq_raw;
					if ($j_sequence == -1)
					{
						# This might be a job that's been archived leaving it with sequence = -1 and vilno = 0.
						# Look it up in the COLLECT_DBF_2015 table to try and find it's correct sequence number and vilno.
						$where = array();
						if (csvfield('DATEREC', 'uk') != '')
							$where[] = "DATEREC=" . quote_smart(csvfield('DATEREC', 'uk'), true);
						else 
							$where[] = "DATEREC IS NULL";
						$where[] = "LASTNAME=" . quote_smart(csvfield('LASTNAME'), true);
						$where[] = "CLIREF=" . quote_smart(csvfield('CLIREF'), true);
						$where[] = "CLID=" . csvfield('CLID');
						$sql = "SELECT TOP 1 VILNO, SEQUENCE FROM COLLECT_DBF_2015 WHERE (" . implode(') AND (', $where) . ")";
						if ($verbose)
							dprint($sql);
						sql_execute($sql);
						while (($newArray = sql_fetch()) != false)
						{
							$vilno_2015 = 1 * $newArray[0];
							$sequence_2015 = 1 * $newArray[1];
						}
						if ((0 < $vilno_2015) && (0 < $sequence_2015))
						{
							$j_sequence = $sequence_2015;
							$vs_2015 = 1;
							if ($verbose)
								dprint("Found V=$vilno_2015 & S=$sequence_2015 from: $sql");
						}
						else
						{
							$vilno_2015 = 0;
							$sequence_2015 = 0;
						}
					}
					if ($j_sequence > 0)
					{
						if (($seq_min == 0) || ($j_sequence < $seq_min))
							$seq_min = $j_sequence;
						if (($seq_max == 0) || ($seq_max < $j_sequence))
							$seq_max = $j_sequence;
					}
					else
					{
						$j_sequence = $seq_zero_replacement++;
						$seq_zero_count++;
						if ($seq_zero_count <= 1000)
							dlog("*=* (Sys=$sys) Non-numeric or illegal SEQUENCE \"$seq_raw\" changed to $j_sequence " .
									"(record $input_count, VILNO=\"" . csvfield('VILNO') . "\", \$seq_zero_count=$seq_zero_count)");
					}
				}
				else 
				{
					dlog("End of Jobs");
					break; # while(true)
				}
				
				$old_verbose = $verbose;
				
				if (($j_sequence == 90149542) || ($j_sequence == 90493801)# || ($j_sequence == 70940)) 70940 only for import of 20/09/16
					#|| (($one_record[0] == -1) && (trim($one_record[8]) == '54B MORAY ROAD'))
					|| in_fix_aux_list($one_record)
					)
				{
					# See also $fix_list
					#$verbose = true; #
					if ($verbose)
						dlog("*=* Found sequence=$j_sequence. col_nums:<br>" . print_r($col_nums,1) . "<br>" . 
								"Record:<br>" . print_r($one_record,1));
					for ($ii = 88; 36 <= $ii; $ii--)
						$one_record[$ii] = $one_record[$ii-4];
					$bits = explode(',', str_replace('"', '', $one_record[31]));
					$one_record[31] = str_replace('\\', '', $bits[0]);
					$one_record[32] = $bits[1];
					$one_record[33] = $bits[2];
					$one_record[34] = $bits[3];
					$one_record[35] = $bits[4];
					if ($verbose)
						dlog("Cleaned Record:<br>" . print_r($one_record,1));
				}
				elseif ($j_sequence == 730508)
				{
					# See also $fix_list
					#$verbose = true; #
					if ($verbose)
					{
						dlog("*=* Found sequence=$j_sequence. col_nums:<br>" . print_r($col_nums,1) . "<br>" . 
								"Record:<br>" . print_r($one_record,1));
					}
					$one_record[70] = str_replace('foren\",m', 'forenm', $one_record[70]);
					$one_record[71] = str_replace('"', '', $one_record[71]);
					if ($verbose)
						dlog("Cleaned:<br>" . print_r($one_record,1));
				}
				elseif ($j_sequence == 90503059)
				{
					# See also $fix_list
					#$verbose = true; #
					if ($verbose)
						dlog("*=* Found sequence=$j_sequence. col_nums:<br>" . print_r($col_nums,1) . "<br>" . 
								"Record:<br>" . print_r($one_record,1));
					for ($ii = 88; 15 <= $ii; $ii--)
						$one_record[$ii] = $one_record[$ii-1];
					$bits = explode(',', str_replace('"', '', $one_record[13]));
					$one_record[13] = str_replace('\\', '', $bits[0]);
					$one_record[14] = $bits[1];
					if ($verbose)
						dlog("Cleaned Record:<br>" . print_r($one_record,1));
				}
				elseif (($j_sequence == 90694238) || ($j_sequence == 90700518))
				{
					# See also $fix_list
					#$verbose = true; #
					if ($verbose)
						dlog("*=* Found sequence=$j_sequence. col_nums:<br>" . print_r($col_nums,1) . "<br>" . 
								"Record:<br>" . print_r($one_record,1));
					for ($ii = 88; 28 <= $ii; $ii--)
						$one_record[$ii] = $one_record[$ii-7];
					$bits = explode(',', str_replace('"', '', $one_record[20]));
					$one_record[20] = str_replace('\\', '', $bits[0]);
					$one_record[21] = $bits[1];
					$one_record[22] = $bits[2];
					$one_record[23] = $bits[3];
					$one_record[24] = $bits[4];
					$one_record[25] = $bits[5];
					$one_record[26] = $bits[6];
					$one_record[27] = $bits[7];
					if ($verbose)
						dlog("Cleaned Record:<br>" . print_r($one_record,1));
				}
				elseif (($j_sequence == 90707120) || ($j_sequence == 90738552))
				{
					# See also $fix_list
					#$verbose = true; #
					if ($verbose)
						dlog("*=* Found sequence=$j_sequence. col_nums:<br>" . print_r($col_nums,1) . "<br>" . 
								"Record:<br>" . print_r($one_record,1));
					$vlf = $one_record[81];
					if ($verbose)
						dlog("VLF=\"$vlf\"");
					for ($ii = 87; 28 <= $ii; $ii--)
						$one_record[$ii] = $one_record[$ii-7];
					$bits = explode(',', str_replace('"', '', $one_record[20]));
					$one_record[20] = str_replace('\\', '', $bits[0]);
					$one_record[21] = $bits[1];
					$one_record[22] = $bits[2];
					$one_record[23] = $bits[3];
					$one_record[24] = $bits[4];
					$one_record[25] = $bits[5];
					$one_record[26] = $bits[6];
					$one_record[27] = $bits[7];
					$one_record[88] = $vlf;
					if ($verbose)
						dlog("Cleaned Record:<br>" . print_r($one_record,1));
				}
				elseif (($j_sequence == 90714469) || ($j_sequence == 90717737))
				{
					# See also $fix_list
					#$verbose = true; #
					if ($verbose)
						dlog("*=* Found sequence=$j_sequence. col_nums:<br>" . print_r($col_nums,1) . "<br>" . 
								"Record:<br>" . print_r($one_record,1));
					$vlf = $one_record[87];
					if ($verbose)
						dlog("VLF=\"$vlf\"");
					for ($ii = 87; 28 <= $ii; $ii--)
						$one_record[$ii] = $one_record[$ii-1];
					$bits = explode(',', str_replace('"', '', $one_record[26]));
					$one_record[26] = str_replace('\\', '', $bits[0]);
					$one_record[27] = $bits[1];
					$one_record[88] = $vlf;
					if ($verbose)
						dlog("Cleaned Record:<br>" . print_r($one_record,1));
				}
				elseif ($j_sequence == 90723905)
				{
					# See also $fix_list
					#$verbose = true; #
					if ($verbose)
						dlog("*=* Found sequence=$j_sequence. col_nums:<br>" . print_r($col_nums,1) . "<br>" . 
								"Record:<br>" . print_r($one_record,1));
					$vlf = $one_record[83];
					if ($verbose)
						dlog("VLF=\"$vlf\"");
					for ($ii = 87; 26 <= $ii; $ii--)
						$one_record[$ii] = $one_record[$ii-5];
					$bits = explode(',', str_replace('"', '', $one_record[20]));
					$one_record[20] = str_replace('\\', '', $bits[0]);
					$one_record[21] = $bits[1];
					$one_record[22] = $bits[2];
					$one_record[23] = $bits[3];
					$one_record[24] = $bits[4];
					$one_record[25] = $bits[5];
					$one_record[88] = $vlf;
					if ($verbose)
						dlog("Cleaned Record:<br>" . print_r($one_record,1));
				}
				#if ($j_sequence != 90707120)#
				#	continue;
				$verbose = $old_verbose;
			} # if ($input_count > 0) # skip header record
				
			if ((0 < $stop_after) && ($stop_after < $input_count))
			{
				$input_count++;
				continue;
			}
						
			if ($verbose)
				dprint((($input_count == 0) ? "Headers: " : "Job: ") . print_r($one_record,1));#
			if ($input_count == 0)
			{
				# Header record
				foreach ($one_record as $num => $field)
					$col_nums[$field] = $num;
				if ($verbose)
					dprint("col_nums: " . print_r($col_nums,1));#
			}

			if ($incr && (!$incr_found))
			{
				if ($incr_sequence != $j_sequence)
				{
					$input_count++;
					if (($input_count % 10000) == 0)
						dlog("import_jobs($sys): skipped over $input_count input records so far");
					continue;
				}
				$count = 1 * sql_select_single("SELECT COUNT(*) FROM JOB WHERE JC_JOB=$sqlTrue");
				dlog("Setting incr_found to True for Sequence $j_sequence (there are currently $count Collect jobs in table JOB)");
				$incr_found = true;
			}
						
			if (0 < $input_count)
			{
				# Data record
				
				# --- CSV and DBF integrity check
				
				if ($sys == 't')
				{
					$user_dt_raw = csvfield('DATEOUT');
					$j_user_dt = (csvfield('DATEOUT', 'uk') ? csvfield('DATEOUT', 'uk') : '');
	
					# Check we can access the same job in DBF
					$dbf_offset = $dbf_firstoff + (($input_count - 1) * $dbf_reclen);
					#dprint("DBF OFF = $dbf_offset (record $input_count)");#
					fseek($fp_dbf, $dbf_offset);
					$dbf_user_dt = fread($fp_dbf, 8);
					#dprint("CSV DATEOUT: \"$user_dt_raw\"=\"$j_user_dt\". DBF DATEOUT: \"$dbf_user_dt\".");#
					if (trim($dbf_user_dt) != str_replace('-', '', $j_user_dt))
					{
						dlog("*=* ABORTING! (Sys=$sys) CSV/DBF mismatch: CSV DATEOUT=\"$user_dt_raw\"=\"$j_user_dt\", " .
								"DBF=\"$dbf_user_dt\", DBF OFF = $dbf_offset (record $input_count)");
						dlog("...one_record=" . print_r($one_record,1));
						return;
					}
					elseif ($verbose)
						dprint(":-) CSV/DBF matched OK");
					$j_user_dt = ($j_user_dt ? "'{$j_user_dt}'" : 'NULL');
				}
				else 
				{
					# Check we can access the same sequence number in DBF 	
					$dbf_offset = $dbf_firstoff + (($input_count - 1) * $dbf_reclen);
					#dprint("DBF OFF = $dbf_offset (record $input_count)");#
					fseek($fp_dbf, $dbf_offset);
					$dbf_seq = fread($fp_dbf, 8);
					#dprint("CSV SEQ: \"$seq_raw\"=\"$j_sequence\". DBF SEQ: \"$dbf_seq\".");#
					if ($dbf_seq != $seq_raw)
					{
						dlog("*=* ABORTING! (Sys=$sys) CSV/DBF mismatch: CSV SEQ=\"$seq_raw\", DBF=\"$dbf_seq\", " .
								"DBF OFF = $dbf_offset (record $input_count, j_sequence=$j_sequence)");
						dlog("...one_record=" . print_r($one_record,1));
						return;
					}
					elseif ($verbose)
						dprint(":-) CSV/DBF matched OK");
						
					$j_user_dt = ''; # this will get set later
				}
				
				# --- Other data ----------------------
				
				$j_note = '';
				
				if ($sys == 't')
				{
					if (csvfield('DATEBACK', 'uk') != '')
						$jt_back_dt = "'" . csvfield('DATEBACK', 'uk') . "'";
					else 
						$jt_back_dt = 'NULL';
					$j_closed_dt = $jt_back_dt;
					$j_updated_dt = 'NULL';
				}
				else
				{
					if (csvfield('CLOSEDATE', 'uk') != '')
						$j_closed_dt = "'" . csvfield('CLOSEDATE', 'uk') . "'";
					else 
						$j_closed_dt = 'NULL';
					if (csvfield('LASTUPD', 'uk') != '')
						$j_updated_dt = "'" . csvfield('LASTUPD', 'uk') . "'";
					else 
						$j_updated_dt = 'NULL';
				}

				$user_raw = csvfield('USER');
				if ($user_raw)
				{
					$j_user_id = user_id_from_old_username($user_raw, $sys);
					if (!($j_user_id > 0))
					{
						dlog("*=* (Sys=$sys) User ID not found from old username \"$user_raw\" (record $input_count)");
						$j_user_id = 0;
					}
				}
				else 
					$j_user_id = 'NULL';
					
				if ($sys == 't')
				{
					#$seq_checks = array(8157,165617,287744,729763,729769);#
					$success_raw = csvfield('SUCCESS');
					$jt_success = yesno2bool($success_raw, false, array('foc' => 2, '' => 3));
					#if (in_array($j_sequence,$seq_checks))#
					#	$jt_success_1 = $jt_success;
					if ($jt_success === true)
						$jt_success = 1;
					elseif ($jt_success === false)
						$jt_success = 0;
					elseif ($jt_success == 2) # FOC
						$jt_success = -1; # FOC
					elseif ($jt_success == 3) # Blank
						$jt_success = -2; # Blank
					else
					{
						dlog("*=* (Sys=$sys) Unrecognised SUCCESS=\"" . csvfield('SUCCESS') . " (record $input_count, " .
									"VILNO=\"" . csvfield('VILNO') . "\", SEQUENCE=\"" . csvfield('SEQUENCE') . "\")");
						$jt_success = -2; # Blank
					}
					#if (in_array($j_sequence,$seq_checks))#
					#	dprint("seq=$j_sequence, SUCCESS=\"$success_raw\", \$jt_success_1=$jt_success_1, " .
					#				"\$jt_success=$jt_success");
				}
				else 
				{
					$jc_job_status_id = 0;
					if (csvfield('STATUS'))
					{
						$jc_job_status_id = job_status_id_from_code(csvfield('STATUS'));
						if (!($jc_job_status_id > 0))
						{
							dlog("*=* (Sys=$sys) Unrecognised STATUS=\"" . csvfield('STATUS') . "\" (record $input_count, " .
									"VILNO=\"" . csvfield('VILNO') . "\", SEQUENCE=\"" . csvfield('SEQUENCE') . "\")");
							$jc_job_status_id = 0;
						}
					}
					if (!($jc_job_status_id > 0))
						$jc_job_status_id = 'NULL';
				}
				
				$z_x_auto = quote_smart(csvfield('AUTO'), true);
				
				if ($vilno_2015 <= 0)
					$j_vilno = 1 * csvfield('VILNO');
				else
					$j_vilno = $vilno_2015;
				if (!($j_vilno > 0))
				{
					if ($seq_zero_count <= 1000)
						dlog("*=* (Sys=$sys) Non-numeric or illegal VILNO \"" . csvfield('VILNO') . "\" (record $input_count, " .
								"VILNO=\"" . csvfield('VILNO') . "\", SEQUENCE=\"" . csvfield('SEQUENCE') . "\")");
					$j_vilno = 0;
				}
				
				$js_lastname = sql_encrypt(csvfield('LASTNAME'), false, 'JOB_SUBJECT');
				$js_firstname = sql_encrypt(csvfield('FIRSTNAME'), false, 'JOB_SUBJECT');
				$js_title = quote_smart(csvfield('TITLE'), true);
				$js_company = sql_encrypt(csvfield('COMPNAME'), false, 'JOB_SUBJECT');

				if ($sys == 't')
				{
					$jt_job_type_id = job_type_id_from_old_description(csvfield('JOBTYPE'));
				
					$jt_credit = yesno2bool(csvfield('CREDIT'), false, array('foc' => 2, '' => 3));
					if ($jt_credit === true)
						$jt_credit = 1;
					elseif ($jt_credit === false)
						$jt_credit = 0;
					elseif ($jt_credit == 2) # FOC
						$jt_credit = -1; # FOC
					elseif ($jt_credit == 3) # Blank
						$jt_credit = 'NULL'; # Blank
					else
					{
						dlog("*=* (Sys=$sys) Unrecognised CREDIT=\"" . csvfield('CREDIT') . 
								" (record $input_count; VilNo=$j_vilno Seq=$j_sequence)");
						$jt_credit = 'NULL'; # Blank
					}
					
					$jt_property = strtolower(csvfield('PROPERTY'));
					if ($jt_property == 'owner')
						$jt_property = 1;
					elseif ($jt_property == 'tenant')
						$jt_property = 2;
					elseif ($jt_property == 'parents')
						$jt_property = 3;
					elseif ($jt_property == 'forces')
						$jt_property = 4;
					elseif ($jt_property == 'other')
						$jt_property = 5;
					else
						$jt_property = 'NULL';
				}
								
				$js_addr_pc = '';
				$js_outcode = '';
				$js_addr_6 = ''; # if POSTCODE is not a postcode
				if ($sys == 'c')
				{
					if (is_postcode(csvfield('POSTCODE')))
					{
						$js_addr_pc = $pm_match;
						$js_addr_6 = trim(str_replace($pm_match, '', csvfield('POSTCODE')));
					}
				}
				if ($js_addr_pc)
				{
					# Do nothing
				}
				elseif (is_postcode(csvfield('HOMEADD5')))
				{
					$js_addr_pc = $pm_match;
					$one_record[$col_nums['HOMEADD5']] = trim(str_replace($pm_match, '', csvfield('HOMEADD5')));
				}
				elseif (is_postcode(csvfield('HOMEADD4')))
				{
					$js_addr_pc = $pm_match;
					$one_record[$col_nums['HOMEADD4']] = trim(str_replace($pm_match, '', csvfield('HOMEADD4')));
				}
				elseif (is_postcode(csvfield('HOMEADD3')))
				{
					$js_addr_pc = $pm_match;
					$one_record[$col_nums['HOMEADD3']] = trim(str_replace($pm_match, '', csvfield('HOMEADD3')));
				}
				elseif (is_postcode(csvfield('HOMEADD2')))
				{
					$js_addr_pc = $pm_match;
					$one_record[$col_nums['HOMEADD2']] = trim(str_replace($pm_match, '', csvfield('HOMEADD2')));
				}
				elseif (is_postcode(csvfield('HOMEADD1')))
				{
					$js_addr_pc = $pm_match;
					$one_record[$col_nums['HOMEADD1']] = trim(str_replace($pm_match, '', csvfield('HOMEADD1')));
				}
				$js_outcode = postcode_outcode($js_addr_pc);
				
				$js_addr_1 = sql_encrypt(csvfield('HOMEADD1'), false, 'JOB_SUBJECT');
				$js_addr_2 = sql_encrypt(csvfield('HOMEADD2'), false, 'JOB_SUBJECT');
				$js_addr_3 = sql_encrypt(csvfield('HOMEADD3'), false, 'JOB_SUBJECT');
				$js_addr_4 = sql_encrypt(csvfield('HOMEADD4'), false, 'JOB_SUBJECT');
				$js_addr_5 = sql_encrypt(csvfield('HOMEADD5') . ($js_addr_6 ? " $js_addr_6" : ''), false, 'JOB_SUBJECT');
				$js_addr_pc = sql_encrypt($js_addr_pc, false, 'JOB_SUBJECT');
				$js_outcode = ($js_outcode ? "'$js_outcode'" : 'NULL');
				
				
				$temp = csvfield('WORKADD1');
				if (csvfield('WORKADD2'))
					$temp .= (($temp ? $crlf : '') . csvfield('WORKADD2'));
				if (csvfield('WORKADD3'))
					$temp .= (($temp ? $crlf : '') . csvfield('WORKADD3'));
				if (csvfield('WORKADD4'))
					$temp .= (($temp ? $crlf : '') . csvfield('WORKADD4'));
				if (csvfield('WORKADD5'))
					$temp .= (($temp ? $crlf : '') . csvfield('WORKADD5'));
				if ($temp)
				{
					if ($sys == 't')
						$wa_field = "Word Address";
					else 
						$wa_field = "Details";
					$j_note .= "{$crlf}Imported from old \"$wa_field\":{$crlf}{$temp}{$crlf}";
				}
				
				if ($sys == 't')
				{
					$temp = csvfield('OCCUP');
					if ($temp)
						$j_note .= "{$crlf}Imported from old \"Occup\":{$crlf}{$temp}{$crlf}";
				}
								
				$jp_phone = csvfield('HOMETEL1') . csvfield('HOMETEL2') . csvfield('HOMETEL3');
				if ((strlen($jp_phone) > 0) && is_numeric_kdb($jp_phone[0], false, false, true) && ($jp_phone[0] != '0'))
					$jp_phone = "0{$jp_phone}";
				if ($jp_phone)
					$jp_phone = sql_encrypt($jp_phone, '', 'JOB_PHONE');
				
				$jp_phone_2 = csvfield('WORKTEL1') . csvfield('WORKTEL2') . csvfield('WORKTEL3');
				if (csvfield('EXT'))
					$jp_phone_2 .= ' Ext. ' . csvfield('EXT');
				if ((strlen($jp_phone_2) > 0) && is_numeric_kdb($jp_phone_2[0], false, false, true) && ($jp_phone_2[0] != '0'))
					$jp_phone_2 = "0{$jp_phone_2}";
				if ($jp_phone_2)
					$jp_phone_2 = sql_encrypt($jp_phone_2, '', 'JOB_PHONE');

				if ($sys == 'c')
				{
					$jp_email = csvfield('EMAIL');
					$jp_email = ($jp_email ? sql_encrypt($jp_email, '', 'JOB_PHONE') : '');
					
					$jp_mobile = csvfield('MOBILE');
					$jp_mobile = ($jp_mobile ? sql_encrypt($jp_mobile, '', 'JOB_PHONE') : '');
					if ((strlen($jp_mobile) > 0) && is_numeric_kdb($jp_mobile[0], false, false, true) && ($jp_mobile[0] != '0'))
						$jp_mobile = "0{$jp_mobile}";
					
					$jp_base = csvfield('PHONEBASE');
					$jp_base = ($jp_base ? sql_encrypt($jp_base, '', 'JOB_PHONE') : '');
					if ((strlen($jp_base) > 0) && is_numeric_kdb($jp_base[0], false, false, true) && ($jp_base[0] != '0'))
						$jp_base = "0{$jp_base}";
				}
				else 
				{
					$jp_email = '';
					$jp_mobile = '';
					$jp_base = '';
				}
									
				if ($sys == 't')
				{
					$temp = csvfield('BANK1');
					if (csvfield('BANK2'))
						$temp .= (($temp ? $crlf : '') . csvfield('BANK2'));
					if (csvfield('BANK3'))
						$temp .= (($temp ? $crlf : '') . csvfield('BANK3'));
					if (csvfield('BANK4'))
						$temp .= (($temp ? $crlf : '') . csvfield('BANK4'));
					if (csvfield('BANK5'))
						$temp .= (($temp ? $crlf : '') . csvfield('BANK5'));
					if ($temp)
						$j_note .= "{$crlf}Imported from old \"Bank Details\":{$crlf}{$temp}{$crlf}";
						
					$js_bank_name = '';
					$js_bank_sortcode = '';
					$js_bank_acc_num = '';
				}
				else 
				{
					$temp = csvfield('BANKNAME');
					if (csvfield('SORTCODE'))
						$temp .= (($temp ? $crlf : '') . csvfield('SORTCODE'));
					if (csvfield('ACCOUNTNO'))
						$temp .= (($temp ? $crlf : '') . csvfield('ACCOUNTNO'));
					if ($temp)
						$j_note .= "{$crlf}Imported from old \"Bank Details\":{$crlf}{$temp}{$crlf}";
						
					$js_bank_name = csvfield('BANKNAME');
					$js_bank_name = ($js_bank_name ? sql_encrypt($js_bank_name, '', 'JOB_SUBJECT') : '');
					$js_bank_sortcode = csvfield('SORTCODE');
					$js_bank_sortcode = ($js_bank_sortcode ? sql_encrypt($js_bank_sortcode, '', 'JOB_SUBJECT') : '');
					$js_bank_acc_num = csvfield('ACCOUNTNO');
					$js_bank_acc_num = ($js_bank_acc_num ? sql_encrypt($js_bank_acc_num, '', 'JOB_SUBJECT') : '');
				}
				
				if ($sys == 't')
				{
					$temp = csvfield('INFO1');
					if (csvfield('INFO2'))
						$temp .= (($temp ? $crlf : '') . csvfield('INFO2'));
					if (csvfield('INFO3'))
						$temp .= (($temp ? $crlf : '') . csvfield('INFO3'));
					if (csvfield('INFO4'))
						$temp .= (($temp ? $crlf : '') . csvfield('INFO4'));
					if (csvfield('INFO5'))
						$temp .= (($temp ? $crlf : '') . csvfield('INFO5'));
					if (csvfield('INFO6'))
						$temp .= (($temp ? $crlf : '') . csvfield('INFO6'));
					if (csvfield('INFO7'))
						$temp .= (($temp ? $crlf : '') . csvfield('INFO7'));
					if (csvfield('INFO8'))
						$temp .= (($temp ? $crlf : '') . csvfield('INFO8'));
					if (csvfield('INFO9'))
						$temp .= (($temp ? $crlf : '') . csvfield('INFO9'));
					if ($temp)
						$j_note .= "{$crlf}Imported from old \"Additional Information\":{$crlf}{$temp}{$crlf}";
				
					$temp = csvfield('SCR1');
					if (csvfield('SCR2'))
						$temp .= (($temp ? $crlf : '') . csvfield('SCR2'));
					if (csvfield('SCR3'))
						$temp .= (($temp ? $crlf : '') . csvfield('SCR3'));
					if (csvfield('SCR4'))
						$temp .= (($temp ? $crlf : '') . csvfield('SCR4'));
					if (csvfield('SCR5'))
						$temp .= (($temp ? $crlf : '') . csvfield('SCR5'));
					if (csvfield('SCR6'))
						$temp .= (($temp ? $crlf : '') . csvfield('SCR6'));
					if (csvfield('SCR7'))
						$temp .= (($temp ? $crlf : '') . csvfield('SCR7'));
					if (csvfield('SCR8'))
						$temp .= (($temp ? $crlf : '') . csvfield('SCR8'));
					if (csvfield('SCR9'))
						$temp .= (($temp ? $crlf : '') . csvfield('SCR9'));
					if (csvfield('SCR10'))
						$temp .= (($temp ? $crlf : '') . csvfield('SCR10'));
					if (csvfield('SCR11'))
						$temp .= (($temp ? $crlf : '') . csvfield('SCR11'));
					if (csvfield('SCR12'))
						$temp .= (($temp ? $crlf : '') . csvfield('SCR12'));
					if (csvfield('SCR13'))
						$temp .= (($temp ? $crlf : '') . csvfield('SCR13'));
					if (csvfield('SCR14'))
						$temp .= (($temp ? $crlf : '') . csvfield('SCR14'));
					if (csvfield('SCR15'))
						$temp .= (($temp ? $crlf : '') . csvfield('SCR15'));
					if (csvfield('SCR16'))
						$temp .= (($temp ? $crlf : '') . csvfield('SCR16'));
					if (csvfield('SCR17'))
						$temp .= (($temp ? $crlf : '') . csvfield('SCR17'));
					if (csvfield('SCR18'))
						$temp .= (($temp ? $crlf : '') . csvfield('SCR18'));
					if ($temp)
						$j_note .= "{$crlf}Imported from old \"User Notes\":{$crlf}{$temp}{$crlf}";
				}
								
				$new_outcode = '';
				if (is_postcode(csvfield('NEWADD5')))
				{
					$new_addr_pc = $pm_match;
					$one_record[$col_nums['NEWADD5']] = trim(str_replace($pm_match, '', csvfield('NEWADD5')));
				}
				elseif (is_postcode(csvfield('NEWADD4')))
				{
					$new_addr_pc = $pm_match;
					$one_record[$col_nums['NEWADD4']] = trim(str_replace($pm_match, '', csvfield('NEWADD4')));
				}
				elseif (is_postcode(csvfield('NEWADD3')))
				{
					$new_addr_pc = $pm_match;
					$one_record[$col_nums['NEWADD3']] = trim(str_replace($pm_match, '', csvfield('NEWADD3')));
				}
				elseif (is_postcode(csvfield('NEWADD2')))
				{
					$new_addr_pc = $pm_match;
					$one_record[$col_nums['NEWADD2']] = trim(str_replace($pm_match, '', csvfield('NEWADD2')));
				}
				elseif (is_postcode(csvfield('NEWADD1')))
				{
					$new_addr_pc = $pm_match;
					$one_record[$col_nums['NEWADD1']] = trim(str_replace($pm_match, '', csvfield('NEWADD1')));
				}
				else 
					$new_addr_pc = '';
				$new_outcode = postcode_outcode($new_addr_pc);
				
				$new_addr_1 = sql_encrypt(csvfield('NEWADD1'), false, 'JOB_SUBJECT');
				$new_addr_2 = sql_encrypt(csvfield('NEWADD2'), false, 'JOB_SUBJECT');
				$new_addr_3 = sql_encrypt(csvfield('NEWADD3'), false, 'JOB_SUBJECT');
				$new_addr_4 = sql_encrypt(csvfield('NEWADD4'), false, 'JOB_SUBJECT');
				$new_addr_5 = sql_encrypt(csvfield('NEWADD5'), false, 'JOB_SUBJECT');
				$new_addr_pc = sql_encrypt($new_addr_pc, false, 'JOB_SUBJECT');
				$new_outcode = ($new_outcode ? "'$new_outcode'" : '');
				
				$jt_let_report = 'NULL';
				if ($sys == 't')
				{
					$report = csvfield('REP1'); # agent's report from REP1 to REP9; see also $jl_text
					if (csvfield('REP2'))
						$report .= (($report ? $crlf : '') . csvfield('REP2'));
					if (csvfield('REP3'))
						$report .= (($report ? $crlf : '') . csvfield('REP3'));
					if (csvfield('REP4'))
						$report .= (($report ? $crlf : '') . csvfield('REP4'));
					if (csvfield('REP5'))
						$report .= (($report ? $crlf : '') . csvfield('REP5'));
					if (csvfield('REP6'))
						$report .= (($report ? $crlf : '') . csvfield('REP6'));
					if (csvfield('REP7'))
						$report .= (($report ? $crlf : '') . csvfield('REP7'));
					if (csvfield('REP8'))
						$report .= (($report ? $crlf : '') . csvfield('REP8'));
					if (csvfield('REP9'))
						$report .= (($report ? $crlf : '') . csvfield('REP9'));
					if (csvfield('REP10'))
						$report .= (($report ? $crlf : '') . csvfield('REP10'));
					if (csvfield('REP11'))
						$report .= (($report ? $crlf : '') . csvfield('REP11'));
					if ($report)
					{
						if ($verbose)
							dprint("Report (REP1 to REP11):<br>$report");
						$report_field_value = str_replace('  ', ' ', str_replace($crlf, ' ', $report));
						$jt_let_report = sql_encrypt($report_field_value, false, 'JOB');
					}
					else
					{
						if ($verbose)
							dprint("Job (VilNo=$j_vilno Seq=$j_sequence) has no Report");
					}
					$z_t_rep1 = (csvfield('REP1') ? quote_smart(csvfield('REP1'), true) : 'NULL');
					$z_t_rep2 = (csvfield('REP2') ? quote_smart(csvfield('REP2'), true) : 'NULL');
					$z_t_rep3 = (csvfield('REP3') ? quote_smart(csvfield('REP3'), true) : 'NULL');
					$z_t_rep4 = (csvfield('REP4') ? quote_smart(csvfield('REP4'), true) : 'NULL');
					$z_t_rep5 = (csvfield('REP5') ? quote_smart(csvfield('REP5'), true) : 'NULL');
					$z_t_rep6 = (csvfield('REP6') ? quote_smart(csvfield('REP6'), true) : 'NULL');
					$z_t_rep7 = (csvfield('REP7') ? quote_smart(csvfield('REP7'), true) : 'NULL');
					$z_t_rep8 = (csvfield('REP8') ? quote_smart(csvfield('REP8'), true) : 'NULL');
					$z_t_rep9 = (csvfield('REP9') ? quote_smart(csvfield('REP9'), true) : 'NULL');
					$z_t_rep10 = (csvfield('REP10') ? quote_smart(csvfield('REP10'), true) : 'NULL');
					$z_t_rep11 = (csvfield('REP11') ? quote_smart(csvfield('REP11'), true) : 'NULL');
					
					$temp = csvfield('GB');
					if ($temp)
						$j_note .= "{$crlf}Imported from old \"GB\":{$crlf}{$temp}{$crlf}";
				}
								
				$client2_id = client_id_from_code(csvfield('CLID'));
				if (!($client2_id > 0))
				{
					dlog("*=* (Sys=$sys) Unrecognised CLIENT CODE=\"" . csvfield('CLID') . "\"" .
							" (record $input_count; VilNo=$j_vilno Seq=$j_sequence) \$one_record=" . print_r($one_record,1));
					$client2_id = 0;
				}
					
				$client_ref = sql_encrypt(csvfield('CLIREF'), '', 'JOB');
				
				if (csvfield('DATEREC', 'uk') != '')
					$j_opened_dt = "'" . csvfield('DATEREC', 'uk') . "'";
				else 
					$j_opened_dt = 'NULL';
				if (($sys == 'c') && ($j_user_id != 'NULL') && (0 < $j_user_id))
					$j_user_dt = $j_opened_dt;
				if ($j_user_dt == '')
					$j_user_dt = 'NULL';
				
				if ($sys == 't')
					$jt_amount = 1.0 * csvfield('AMOUNT');
				else 
				{
					if (yesno2bool(csvfield('PAYINFULL')) === true)
						$jc_paid_in_full = 1;
					else 
						$jc_paid_in_full = 0;
					$jc_total_amt = 1.0 * csvfield('AMOUNTOWED');
					$jc_paid_so_far = 1.0 * csvfield('AMOUNTC');
					if (csvfield('STARTCOL', 'uk') != '')
						$jc_instal_dt_1 = "'" . csvfield('STARTCOL', 'uk') . "'";
					else 
						$jc_instal_dt_1 = 'NULL';
					$jc_instal_amt = 1.0 * csvfield('AMOUNTCYC');
					
					$temp = strtolower(csvfield('COLCYC'));
					if ($temp == "immediate")
						$jc_instal_freq = "'I'";
					elseif ($temp == "week")
						$jc_instal_freq = "'W'";
					elseif ($temp == "two weeks")
						$jc_instal_freq = "'F'";
					elseif ($temp == "month")
						$jc_instal_freq = "'M'";
					elseif ($temp == "two months")
						$jc_instal_freq = "'T'";
					elseif ($temp == "quarterly")
						$jc_instal_freq = "'Q'";
					else
					{
						if ($temp != "")
							dlog("*=* (Sys=$sys) Unrecognised COLCYC \"" . csvfield('COLCYC') . "\" " .
									"(record $input_count; VilNo=$j_vilno Seq=$j_sequence)");
						$jc_instal_freq = 'NULL';
					}

					$jc_adjustment = 1.0 * csvfield('CSPENT');
					
					if (csvfield('COLDATE', 'uk') != '')
						$z_c_coldate = "'" . csvfield('COLDATE', 'uk') . "'";
					else 
						$z_c_coldate = 'NULL';
					$z_c_numcol = 1 * csvfield('NUMCOL');
					if (csvfield('LCOLDATE', 'uk') != '')
						$z_c_lcoldate = "'" . csvfield('LCOLDATE', 'uk') . "'";
					else 
						$z_c_lcoldate = 'NULL';
					if (csvfield('LCRDATE', 'uk') != '')
						$z_c_lcrdate = "'" . csvfield('LCRDATE', 'uk') . "'";
					else 
						$z_c_lcrdate = 'NULL';
					if (csvfield('PASTPAYCD', 'uk') != '')
						$z_c_pastpaycd = "'" . csvfield('PASTPAYCD', 'uk') . "'";
					else 
						$z_c_pastpaycd = 'NULL';
					$z_c_amountout = 1.0 * csvfield('AMOUNTOUT');
					$z_c_letpend = quote_smart(csvfield('LETPEND'));
				}
				
				if ($sys == 't')
				{
					# Either none or exactly one LET1, LET2, etc should be set to "yes".
					$jl_posted_dt = ''; # date letter was sent
					for ($lix = 1; $lix <= 12; $lix++)
					{
						$temp = yesno2bool(csvfield("LET{$lix}"));
						if ($temp === true)
						{
							if (csvfield("DATE{$lix}", 'uk') != '')
								$jl_posted_dt = "'" . csvfield("DATE{$lix}", 'uk') . "'";
						}
					}
					$z_t_let1 = quote_smart(csvfield('LET1'), true);
					$z_t_let2 = quote_smart(csvfield('LET2'), true);
					$z_t_let3 = quote_smart(csvfield('LET3'), true);
					$z_t_let4 = quote_smart(csvfield('LET4'), true);
					$z_t_let5 = quote_smart(csvfield('LET5'), true);
					$z_t_let6 = quote_smart(csvfield('LET6'), true);
					$z_t_let7 = quote_smart(csvfield('LET7'), true);
					$z_t_let8 = quote_smart(csvfield('LET8'), true);
					$z_t_let9 = quote_smart(csvfield('LET9'), true);
					$z_t_let10 = quote_smart(csvfield('LET10'), true);
					$z_t_let11 = quote_smart(csvfield('LET11'), true);
					$z_t_let12 = quote_smart(csvfield('LET12'), true);
					if (csvfield('DATE1', 'uk') != '')
						$z_t_date1 = "'" . csvfield('DATE1', 'uk') . "'";
					else 
						$z_t_date1 = 'NULL';
					if (csvfield('DATE2', 'uk') != '')
						$z_t_date2 = "'" . csvfield('DATE2', 'uk') . "'";
					else 
						$z_t_date2 = 'NULL';
					if (csvfield('DATE3', 'uk') != '')
						$z_t_date3 = "'" . csvfield('DATE3', 'uk') . "'";
					else 
						$z_t_date3 = 'NULL';
					if (csvfield('DATE4', 'uk') != '')
						$z_t_date4 = "'" . csvfield('DATE4', 'uk') . "'";
					else 
						$z_t_date4 = 'NULL';
					if (csvfield('DATE5', 'uk') != '')
						$z_t_date5 = "'" . csvfield('DATE5', 'uk') . "'";
					else 
						$z_t_date5 = 'NULL';
					if (csvfield('DATE6', 'uk') != '')
						$z_t_date6 = "'" . csvfield('DATE6', 'uk') . "'";
					else 
						$z_t_date6 = 'NULL';
					if (csvfield('DATE7', 'uk') != '')
						$z_t_date7 = "'" . csvfield('DATE7', 'uk') . "'";
					else 
						$z_t_date7 = 'NULL';
					if (csvfield('DATE8', 'uk') != '')
						$z_t_date8 = "'" . csvfield('DATE8', 'uk') . "'";
					else 
						$z_t_date8 = 'NULL';
					if (csvfield('DATE9', 'uk') != '')
						$z_t_date9 = "'" . csvfield('DATE9', 'uk') . "'";
					else 
						$z_t_date9 = 'NULL';
					if (csvfield('DATE10', 'uk') != '')
						$z_t_date10 = "'" . csvfield('DATE10', 'uk') . "'";
					else 
						$z_t_date10 = 'NULL';
					if (csvfield('DATE11', 'uk') != '')
						$z_t_date11 = "'" . csvfield('DATE11', 'uk') . "'";
					else 
						$z_t_date11 = 'NULL';
					if (csvfield('DATE12', 'uk') != '')
						$z_t_date12 = "'" . csvfield('DATE12', 'uk') . "'";
					else 
						$z_t_date12 = 'NULL';
				}
				else 
				{
					# Collection letters (just 3). Note that all letters sent are in AUTO_LS.DBF.
					# So these 3 are not really needed.
//					$jc_letters = array();
//					for ($lix = 1; $lix <= 3; $lix++)
//					{
//						$temp = yesno2bool(csvfield("LET{$lix}"));
//						$temp_dt = '';
//						if ($temp === true)
//						{
//							if (csvfield("DATE{$lix}") != '')
//								$temp_dt = "'" . csvfield("DATE{$lix}", 'uk') . "'";
//						}
//						$jc_letters[$lix] = array($temp, $temp_dt);
//					}
					$z_c_let1 = quote_smart(csvfield('LET1'), true);
					$z_c_let2 = quote_smart(csvfield('LET2'), true);
					$z_c_let3 = quote_smart(csvfield('LET3'), true);
					if (csvfield('LET1DATE', 'uk') != '')
						$z_c_let1date = "'" . csvfield('LET1DATE', 'uk') . "'";
					else 
						$z_c_let1date = 'NULL';
					if (csvfield('LET2DATE', 'uk') != '')
						$z_c_let2date = "'" . csvfield('LET2DATE', 'uk') . "'";
					else 
						$z_c_let2date = 'NULL';
					if (csvfield('LET3DATE', 'uk') != '')
						$z_c_let3date = "'" . csvfield('LET3DATE', 'uk') . "'";
					else 
						$z_c_let3date = 'NULL';
						
					# Notes on JC_LETTER_MORE 12/04/16:
					#	- After the import, all records had JC_LETTER_MORE==0.
					#	- In the COLLECT.CSV (a conversion of the DBF), some MORELET==Yes.
					#	- So we have a bug in the import, although I can't see how it went wrong.
					#	- Fixed using function import_jobs_ltr_more().
					#	- Cause found: call to csvfield() wasn't being made!!
					if (yesno2bool(csvfield('MORELET')) === true)
					#if (yesno2bool('MORELET') === true)
						$jc_letter_more = 1;
					else 
						$jc_letter_more = 0;
						
					$jc_letter_type_id = letter_type_id_from_name(csvfield('QLETTER'));
					if (!($jc_letter_type_id > 0))
						$jc_letter_type_id = 'NULL';
						
					$z_c_contlet = 1 * csvfield('CONTLET');
					if (csvfield('L_CONTLET', 'uk') != '')
						$z_c_l_contlet = "'" . csvfield('L_CONTLET', 'uk') . "'";
					else 
						$z_c_l_contlet = 'NULL';

					$z_c_demlet = 1 * csvfield('DEMLET');
					if (csvfield('L_DEMLET', 'uk') != '')
						$z_c_l_demlet = "'" . csvfield('L_DEMLET', 'uk') . "'";
					else 
						$z_c_l_demlet = 'NULL';
				}
				
				$j_diary_dt = 'NULL';
				$j_diary_txt = 'NULL';
				if (($sys == 'c') && (csvfield('DIARYDATE', 'uk') != ''))
				{
					$temp = csvfield('DIARYDATE', 'uk');
					if (substr($temp, 0, 3) != '*=*')
					{
						$j_diary_dt = "'$temp'";
						$j_diary_txt = sql_encrypt('Imported from old database', false, 'JOB');
					}
					else
						dlog("*=* (Sys=$sys) Unrecognised DIARYDATE=\"" . csvfield('DIARYDATE') . "\"" .
								" (record $input_count; VilNo=$j_vilno Seq=$j_sequence)");
				}
				
				if ($sys == 'c')
				{
					$jc_review_d_old = 1 * csvfield('SDATE'); # month and year represented by number e.g. 1299
					$jc_review_dt = date_from_mmyy($jc_review_d_old); # in SQL format
					$jc_review_dt = ($jc_review_dt ? "'$jc_review_dt'" : "NULL");
					
					$temp = yesno2bool(csvfield('CU1'));
					if ($temp === true)
						$jc_in_progress_cu1 = 1;
					else
						$jc_in_progress_cu1 = 0;
					
					$temp = yesno2bool(csvfield('CU2'));
					if ($temp === true)
						$jc_subj_paid_cu2 = 1;
					else
						$jc_subj_paid_cu2 = 0;
						
					$temp = yesno2bool(csvfield('CU3'));
					if ($temp === true)
						$jc_pdcheques_cu3 = 1;
					else
						$jc_pdcheques_cu3 = 0;

					$temp = yesno2bool(csvfield('CU4'));
					if ($temp === true)
						$jc_subj_cont_cu4 = 1;
					else
						$jc_subj_cont_cu4 = 0;

					$temp = yesno2bool(csvfield('CU5'));
					if ($temp === true)
						$jc_agreed_cu5 = 1;
					else
						$jc_agreed_cu5 = 0;

					$temp = yesno2bool(csvfield('CU6'));
					if ($temp === true)
						$jc_new_adr_cu6 = 1;
					else
						$jc_new_adr_cu6 = 0;
						
					$temp = yesno2bool(csvfield('CU7'));
					if ($temp === true)
						$jc_no_addr_cu7 = 1;
					else
						$jc_no_addr_cu7 = 0;

					$temp = yesno2bool(csvfield('CU8'));
					if ($temp === true)
						$jc_fail_prom_cu8 = 1;
					else
						$jc_fail_prom_cu8 = 0;

					$temp = yesno2bool(csvfield('CU9'));
					if ($temp === true)
						$jc_not_resp_cu9 = 1;
					else
						$jc_not_resp_cu9 = 0;

					$temp = yesno2bool(csvfield('CU10'));
					if ($temp === true)
						$jc_add_notes_cu10 = 1;
					else
						$jc_add_notes_cu10 = 0;
						
					if (csvfield('PDATE', 'uk') != '')
						$jc_paid_dt_cu2 = "'" . csvfield('L_DEMLET', 'uk') . "'";
					else 
						$jc_paid_dt_cu2 = 'NULL';
					
					$jc_paid_amt_cu2 = 1.0 * csvfield('PAMOUNT');
					
					$jc_pdc_txt_cu3 = quote_smart(csvfield('PDC'), true);
					
					$jc_agr_txt_cu5 = quote_smart(csvfield('ATP'), true);
					
					$temp = csvfield('AN1');
					if (csvfield('AN2'))
						$temp .= (($temp ? $crlf : '') . csvfield('AN2'));
					if (csvfield('AN3'))
						$temp .= (($temp ? $crlf : '') . csvfield('AN3'));
					if ($temp)
						$j_note .= "{$crlf}Imported from old \"Additional Notes\":{$crlf}{$temp}{$crlf}";
				}
					
				if ($sys == 't')
				{
					$j_complete = yesno2bool(csvfield('COMPLETE'), false, array('pen' => 2));
					if ($j_complete === true)
						$j_complete = 1; # Complete=Yes
					elseif ($j_complete === false)
						$j_complete = 0; # Complete=No
					elseif ($j_complete == 2) # Pending i.e, not assigned to an agent
						$j_complete = 0; # Complete=No
					else
					{
						dlog("*=* (Sys=$sys) Unrecognised COMPLETE=\"" . csvfield('COMPLETE') . "\"" .
								" (record $input_count; VilNo=$j_vilno Seq=$j_sequence)");
						$j_complete = 0;
					}
				}
				else 
				{
					#if (csvfield('CLOSEDATE') != '')
					#	$j_complete = 1;
					#else 
						$j_complete = 0;
				}
				
				if ($sys == 't')
				{
					$jt_let_pend = yesno2bool(csvfield('PENDING'));
					if (($jt_let_pend === true) || ($jt_let_pend === false))
						$jt_let_pend = ($jt_let_pend ? 1 : 0);
					else
					{
						dlog("*=* (Sys=$sys) Unrecognised PENDING=\"" . csvfield('PENDING') . "\"" .
								" (record $input_count; VilNo=$j_vilno Seq=$j_sequence)");
						$jt_let_pend = 0;
					}
					
					$jt_let_print = yesno2bool(csvfield('PRINTED'));
					if (($jt_let_print === true) || ($jt_let_print === false))
						$jt_let_print = ($jt_let_print ? 1 : 0);
					else
					{
						dlog("*=* (Sys=$sys) Unrecognised PRINTED=\"" . csvfield('PRINTED') . "\"" .
								" (record $input_count; VilNo=$j_vilno Seq=$j_sequence)");
						$jt_let_print = 0;
					}
					
					if (csvfield('LETDATE', 'uk') != '')
						$z_t_letdate = "'" . csvfield('LETDATE', 'uk') . "'";
					else 
						$z_t_letdate = 'NULL';
									
						
					# --- VLF Report ---------------
					
					# Letter = OpeningPara + Report + ClosingPara
					
					$jl_text = '';
					$jl_text2 = ''; # if old letter is too big for one record
					$jl_text_found = false;
					
					$dbf_offset += ($dbf_reclen - 6 - 1); # This should now point to the VLF field.
					#dprint("DBF OFF for VLF = $dbf_offset = HEX " . dechex($dbf_offset) . " (record $input_count)");#
					fseek($fp_dbf, $dbf_offset);
					$vlf = fread($fp_dbf, 6);
	
					$vlf_ascii = "" . ord($vlf[0]) . "," . ord($vlf[1]) . "," . ord($vlf[2]) . "," . 
										ord($vlf[3]) . "," . ord($vlf[4]) . "," . ord($vlf[5]);
					#$vlf_hex = "" . dechex(ord($vlf[0])) . "," . dechex(ord($vlf[1])) . "," . dechex(ord($vlf[2])) . "," . 
					#					dechex(ord($vlf[3])) . "," . dechex(ord($vlf[4])) . "," . dechex(ord($vlf[5]));
					#dprint("VLF ASCII = \"$vlf_ascii\", HEX = \"$vlf_hex\"");#
					
					$dbv_offset = vlf_convert_raw($vlf);
					if ($verbose)
						dprint("VLF/{$input_count}: \"$vlf\" = \"$vlf_ascii\" -> offset=\"$dbv_offset\"");#
					if ($dbv_offset > 0)
					{
						$letters_dbv_yes++;
						$jl_text_found = true;
						
						fseek($fp_dbv, $dbv_offset);
						$dbv_readlen = 50000;
						$dbv_buffer = fread($fp_dbv, $dbv_readlen);
						$gotlen = strlen($dbv_buffer);
						if ($gotlen < 10)
						{
							dlog("*=* (Sys=$sys) DBV Read problem: VLF: \"$vlf\" = \"$vlf_ascii\" -> " .
								"offset=\"$dbv_offset\". " . (($gotlen > 0) ? "Only $gotlen" : "No") . " bytes read. " .
								"\$input_count = $input_count. No Letter extracted.");
						}
						else 
						{
							# First 6 bytes are size of Pascal TDBVStru structure.
							if ($verbose)
							{
								$six = "" . ord($dbv_buffer[0]) . "," . ord($dbv_buffer[1]) . "," . ord($dbv_buffer[2]) . "," . 
											ord($dbv_buffer[3]) . "," . ord($dbv_buffer[4]) . "," . ord($dbv_buffer[5]);
								dprint("DBV 6-pack = \"$six\"");
							}
							$dbv_len = 0;
							$dbv_len += ord($dbv_buffer[0]);
							$dbv_len += (ord($dbv_buffer[1]) << 8);
							$dbv_len += (ord($dbv_buffer[2]) << 16);
							$dbv_len += (ord($dbv_buffer[3]) << 32);
							if ((0 < $stop_after) && ($input_count <= $stop_after) && 
																		(($stop_after - $input_count) < $stop_margin))
								dprint("DBV Report length = $dbv_len");#
							$jl_text = substr($dbv_buffer, 6, $dbv_len);
							if ((0 < $stop_after) && ($input_count <= $stop_after) && 
																		(($stop_after - $input_count) < $stop_margin))
								dprint("DBV Report/A(" . strlen($jl_text) . ")=\"$jl_text\"");
							$jl_text = trim($jl_text);
							#dprint("DBV Report/B(" . strlen($jl_text) . ")=\"$jl_text\"");#
						}
					}
					else 
					{
						$letters_dbv_no++;
						#dlog("*=* (Sys=$sys) No DBV offset: VLF: \"$vlf\" = \"$vlf_ascii\" -> offset=\"$dbv_offset\". " .
						#		"\$input_count = $input_count", true);
						if ($verbose)
							dprint("Job (VilNo=$j_vilno Seq=$j_sequence) has no letter"); # Not an error
						if ($report)
						{
							$jl_text = $report;
							if ((0 < $stop_after) && ($input_count <= $stop_after) && 
																(($stop_after - $input_count) < $stop_margin))
								dprint("...so using Report text instead");
						}
						elseif ((0 < $stop_after) && ($input_count <= $stop_after) && 
															(($stop_after - $input_count) < $stop_margin))
							dprint("...and no Report text either");
					}
					
	//				if ($report)
	//				{
	//					if ($jl_text)
	//						$jl_text = "{$report}{$crlf}{$jl_text}";
	//					else 
	//						$jl_text = $report;
	//				}
					if ($jl_text)
					{
						# Add opening and closing paragraphs from in-memory array
						$opening_para = '';
						$closing_para = '';
						if (($jt_job_type_id > 0) && ($jt_success != -2)) # -2 means blank
						{
							$opening_para = $letters_open_close[$jt_job_type_id][$jt_success]['OPEN'];
							$closing_para = $letters_open_close[$jt_job_type_id][$jt_success]['CLOSE'];
							$jl_text = $opening_para . $crlf . $jl_text . $crlf . $closing_para;
						}
						else
						{
							$msg = "No opening or closing para's for job (jobType=$jt_job_type_id, succ=$success_raw) " .
									"(input_count=$input_count, vilno=$j_vilno, sequence=$j_sequence)";
							if ($success_raw != '')
								dlog("*=* (Sys=$sys) $msg");
							#else
							#	dprint($msg);
						}
						
						if (strlen($jl_text) >= $v_text_clip)
						{
							$jl_text2 = substr($jl_text, $v_text_clip);
							$jl_text = substr($jl_text, 0, $v_text_clip);
							if (strlen($jl_text2) >= $v_text_clip)
							{
								dlog("*=* (Sys=$sys) Job Letter too big (" . (strlen($jl_text) + strlen($jl_text2)) . ") " .
										"for two fields each of size $v_text_clip !! \$input_count=$input_count, " .
										"vilno=$j_vilno, sequence=$j_sequence");#
								$jl_text2 = substr($jl_text2, 0, $v_text_clip);
							}
						}
						#dprint("Letter/C1(" . strlen($jl_text) . ")=\"$jl_text\"");#
						#dprint("Letter/C2(" . strlen($jl_text2) . ")=\"$jl_text2\"");#
						$jl_text = sql_encrypt($jl_text, false, 'JOB_LETTER');
						if ($jl_text2)
							$jl_text2 = sql_encrypt($jl_text2, false, 'JOB_LETTER');
						if ($verbose)
							dprint("SQL Letter=\"$jl_text\"" . ($jl_text2 ? " and \"$jl_text2\"" : ''));
					}		
							
					if ($jt_success == -2) # -2 means blank
						$jt_success = 'NULL';
						
				} # if 't'
				
				if ($j_note)
					$j_note = "{$crlf}{$j_note}";
				$j_note = "Job imported from old $sys_txt system on " . date_now() . $j_note;
				$j_note = sql_encrypt($j_note, '', 'JOB_NOTE');
				
				
				if ($sys == 't')
				{
					$j_s_invs = 0;
					if ($client2_id > 0)
					{
						$sql = "SELECT S_INVS_TRACE FROM CLIENT2 WHERE CLIENT2_ID=$client2_id";
						#dprint($sql);#
						sql_execute($sql);
						while (($newArray = sql_fetch()) != false)
							$j_s_invs = 1 * $newArray[0];
					}
				}
				else 
					$j_s_invs = 1;
									
				# --- SQL Insertions - JOB --------------------------------------------

				# All-job fields (but J_COMPLETE is only valid for Trace jobs):
				$fields = "J_SEQUENCE,  J_VILNO,  CLIENT2_ID,  CLIENT_REF,  J_USER_ID,  J_USER_DT,  J_DIARY_DT,  VS_2015,  ";
				$values = "$j_sequence, $j_vilno, $client2_id, $client_ref, $j_user_id, $j_user_dt, $j_diary_dt, $vs_2015, ";
				
				$fields .= "J_DIARY_TXT,  J_OPENED_DT,  J_CLOSED_DT,  JOB_CLOSED, J_UPDATED_DT,  IMPORTED, J_COMPLETE,  J_S_INVS,  ";
				$values .= "$j_diary_txt, $j_opened_dt, $j_closed_dt, $sqlFalse,  $j_updated_dt, $sqlTrue, $j_complete, $j_s_invs, ";

				if ($sys == 't')
				{
					# Trace-job fields:
					$fields .= "JT_JOB,   JC_JOB,    JT_BACK_DT,  JT_JOB_TYPE_ID,  JT_SUCCESS,  JT_CREDIT,  JT_AMOUNT,  ";
					$values .= "$sqlTrue, $sqlFalse, $jt_back_dt, $jt_job_type_id, $jt_success, $jt_credit, $jt_amount, ";
	
					$fields .= "JT_LET_REPORT,  JT_LET_PEND,  JT_LET_PRINT";
					$values .= "$jt_let_report, $jt_let_pend, $jt_let_print";
				}
				else 
				{
					# Collect-job fields:
					$fields .= "JT_JOB,    JC_JOB,   JC_JOB_STATUS_ID,  JC_PAID_IN_FULL,  JC_TOTAL_AMT,  JC_PAID_SO_FAR,  ";
					$values .= "$sqlFalse, $sqlTrue, $jc_job_status_id, $jc_paid_in_full, $jc_total_amt, $jc_paid_so_far, ";
					
					$fields .= "JC_LETTER_MORE,  JC_LETTER_TYPE_ID,  JC_INSTAL_DT_1,  JC_INSTAL_AMT,  JC_INSTAL_FREQ,  ";
					$values .= "$jc_letter_more, $jc_letter_type_id, $jc_instal_dt_1, $jc_instal_amt, $jc_instal_freq, ";
					
					$fields .= "JC_ADJUSTMENT,  JC_REVIEW_D_OLD,  JC_REVIEW_DT,  JC_PAID_DT_CU2,  JC_PAID_AMT_CU2,  JC_PDC_TXT_CU3,  ";
					$values .= "$jc_adjustment, $jc_review_d_old, $jc_review_dt, $jc_paid_dt_cu2, $jc_paid_amt_cu2, $jc_pdc_txt_cu3, ";
					
					$fields .= "JC_AGR_TXT_CU5,  JC_IN_PROGRESS_CU1,  JC_SUBJ_PAID_CU2,  JC_PDCHEQUES_CU3,  ";
					$values .= "$jc_agr_txt_cu5, $jc_in_progress_cu1, $jc_subj_paid_cu2, $jc_pdcheques_cu3, ";
					
					$fields .= "JC_SUBJ_CONT_CU4,  JC_AGREED_CU5,  JC_NEW_ADR_CU6,  JC_NO_ADDR_CU7,  JC_FAIL_PROM_CU8,  ";
					$values .= "$jc_subj_cont_cu4, $jc_agreed_cu5, $jc_new_adr_cu6, $jc_no_addr_cu7, $jc_fail_prom_cu8, ";
					
					$fields .= "JC_NOT_RESP_CU9,  JC_ADD_NOTES_CU10  ";
					$values .= "$jc_not_resp_cu9, $jc_add_notes_cu10 ";
				}
				
				$sql = "INSERT INTO JOB ($fields) VALUES ($values)";
				if ($verbose)
					dprint($sql);
				$job_id = sql_execute($sql); # no need to audit
				if ($verbose)
					dprint("-> ID $job_id");
				$job_count++;
					
				# --- SQL Insertions - JOB_SUBJECT --------------------------------------------
				
				$fields = "JOB_ID,  JS_PRIMARY, JS_LASTNAME,  JS_FIRSTNAME,  JS_TITLE,  JS_COMPANY,  IMPORTED, ";
				$values = "$job_id, $sqlTrue,   $js_lastname, $js_firstname, $js_title, $js_company, $sqlTrue, ";
				
				$fields .= "JS_ADDR_1,  JS_ADDR_2,  JS_ADDR_3,  JS_ADDR_4,  JS_ADDR_5,  JS_ADDR_PC,  JS_OUTCODE,  ";
				$values .= "$js_addr_1, $js_addr_2, $js_addr_3, $js_addr_4, $js_addr_5, $js_addr_pc, $js_outcode, ";
				
				$fields .= "NEW_ADDR_1,  NEW_ADDR_2,  NEW_ADDR_3,  NEW_ADDR_4,  NEW_ADDR_5,  NEW_ADDR_PC,  NEW_OUTCODE ";
				$values .= "$new_addr_1, $new_addr_2, $new_addr_3, $new_addr_4, $new_addr_5, $new_addr_pc, $new_outcode";
				
				if ($js_bank_name)
				{
					$fields .= ", JS_BANK_NAME ";
					$values .= ", $js_bank_name";
				}
				if ($js_bank_sortcode)
				{
					$fields .= ", JS_BANK_SORTCODE ";
					$values .= ", $js_bank_sortcode";
				}
				if ($js_bank_acc_num)
				{
					$fields .= ", JS_BANK_ACC_NUM ";
					$values .= ", $js_bank_acc_num";
				}
				
				$sql = "INSERT INTO JOB_SUBJECT ($fields) VALUES ($values)";
				if ($verbose)
					dprint($sql);
				$job_subject_id = sql_execute($sql);
				if ($verbose)
					dprint("-> ID $job_subject_id");
				
					
				# --- SQL Insertions - JOB_NOTE --------------------------------------------
				
				$fields = "JOB_ID,  J_NOTE,  IMPORTED, JN_ADDED_ID,        JN_ADDED_DT";
				$values = "$job_id, $j_note, $sqlTrue, {$USER['USER_ID']}, $sqlNow";
				
				$sql = "INSERT INTO JOB_NOTE ($fields) VALUES ($values)";
				if ($verbose)
					dprint($sql);
				$job_note_id = sql_execute($sql);
				if ($verbose)
					dprint("-> ID $job_note_id");
				
					
				# --- SQL Insertions - JOB_PHONE --------------------------------------------
				
				$jp_descr = sql_encrypt('Imported from old system job file', '', 'JOB_PHONE');
				
				if ($jp_phone)
				{
					$fields = "JOB_ID,  JOB_SUBJECT_ID,  JP_PHONE,  IMPORTED, IMP_PH,    JP_DESCR";
					$values = "$job_id, $job_subject_id, $jp_phone, $sqlTrue, $sqlFalse, $jp_descr";
					
					$sql = "INSERT INTO JOB_PHONE ($fields) VALUES ($values)";
					if ($verbose)
						dprint("(P1) " . $sql);
					$job_phone_id = sql_execute($sql);
					if ($verbose)
						dprint("-> ID(P1) $job_phone_id");
				}
								
				if ($jp_phone_2)
				{
					$fields = "JOB_ID,  JOB_SUBJECT_ID,  JP_PHONE,    IMPORTED, IMP_PH,    JP_DESCR";
					$values = "$job_id, $job_subject_id, $jp_phone_2, $sqlTrue, $sqlFalse, $jp_descr";
					
					$sql = "INSERT INTO JOB_PHONE ($fields) VALUES ($values)";
					if ($verbose)
						dprint("(P2) " . $sql);
					$job_phone_id = sql_execute($sql);
					if ($verbose)
						dprint("-> ID(P2) $job_phone_id");
				}
							
				if ($jp_email)
				{
					$fields = "JOB_ID,  JOB_SUBJECT_ID,  JP_EMAIL,  IMPORTED, IMP_PH,    JP_DESCR";
					$values = "$job_id, $job_subject_id, $jp_email, $sqlTrue, $sqlFalse, $jp_descr";
					
					$sql = "INSERT INTO JOB_PHONE ($fields) VALUES ($values)";
					if ($verbose)
						dprint("(email) " . $sql);
					$job_phone_id = sql_execute($sql);
					if ($verbose)
						dprint("-> ID(email) $job_phone_id");
				}
							
				if ($jp_mobile)
				{
					$fields = "JOB_ID,  JOB_SUBJECT_ID,  JP_PHONE,   IMPORTED, IMP_PH,    JP_DESCR";
					$values = "$job_id, $job_subject_id, $jp_mobile, $sqlTrue, $sqlFalse, $jp_descr";
					
					$sql = "INSERT INTO JOB_PHONE ($fields) VALUES ($values)";
					if ($verbose)
						dprint("(mobile) " . $sql);
					$job_phone_id = sql_execute($sql);
					if ($verbose)
						dprint("-> ID(mobile) $job_phone_id");
				}
							
				if ($jp_base)
				{
					$fields = "JOB_ID,  JOB_SUBJECT_ID,  JP_PHONE, IMPORTED, IMP_PH,    JP_DESCR";
					$values = "$job_id, $job_subject_id, $jp_base, $sqlTrue, $sqlFalse, $jp_descr";
					
					$sql = "INSERT INTO JOB_PHONE ($fields) VALUES ($values)";
					if ($verbose)
						dprint("(base) " . $sql);
					$job_phone_id = sql_execute($sql);
					if ($verbose)
						dprint("-> ID(base) $job_phone_id");
				}
							
				if ($sys == 't')
				{
					# --- SQL Insertions - JOB_LETTER --------------------------------------------
					
					if ($jl_text)
					{
						# Note: LETTER_TYPE_ID should not be NULL; this is corrected in a later operation
						$fields = "JOB_ID,  LETTER_TYPE_ID,  IMPORTED, JL_ADDED_DT, JL_APPROVED_DT, JL_TEXT  ";
						$values = "$job_id, NULL,            $sqlTrue, $sqlNow,     '1977-01-01',   $jl_text ";
						
						if ($jl_text2)
						{
							$fields .= ", JL_TEXT_2";
							$values .= ", $jl_text2";
						}
							
						if ($jl_posted_dt)
						{
							$fields .= ", JL_POSTED_DT";
							$values .= ", $jl_posted_dt";
						}
							
						$sql = "INSERT INTO JOB_LETTER ($fields) VALUES ($values)";
						if ($verbose)
							dprint($sql);
						$job_letter_id = sql_execute($sql);
						if ($verbose)
							dprint("-> ID $job_letter_id");
					}
					elseif ($jl_text_found)
						$letters_dbv_yes_then_no++;
				}
												
				# --- SQL Insertions - JOB_Z --------------------------------------------

				$fields = "JOB_ID,  Z_X_AUTO,  ";
				$values = "$job_id, $z_x_auto, ";
				
				if ($sys == 't')
				{
					$fields .= "Z_T_LETDATE,  Z_T_LET1,  Z_T_LET2,  Z_T_LET3,  Z_T_LET4,  Z_T_LET5,  Z_T_LET6,  Z_T_LET7,  ";
					$values .= "$z_t_letdate, $z_t_let1, $z_t_let2, $z_t_let3, $z_t_let4, $z_t_let5, $z_t_let6, $z_t_let7, ";
					
					$fields .= "Z_T_LET8,  Z_T_LET9,  Z_T_LET10,  Z_T_LET11,  Z_T_LET12,  Z_T_DATE1,  Z_T_DATE2,  ";
					$values .= "$z_t_let8, $z_t_let9, $z_t_let10, $z_t_let11, $z_t_let12, $z_t_date1, $z_t_date2, ";
					
					$fields .= "Z_T_DATE3,  Z_T_DATE4,  Z_T_DATE5,  Z_T_DATE6,  Z_T_DATE7,  Z_T_DATE8,  Z_T_DATE9,  ";
					$values .= "$z_t_date3, $z_t_date4, $z_t_date5, $z_t_date6, $z_t_date7, $z_t_date8, $z_t_date9, ";
					
					$fields .= "Z_T_DATE10,  Z_T_DATE11,  Z_T_DATE12,  Z_T_REP1,  Z_T_REP2,  Z_T_REP3,  Z_T_REP4,  ";
					$values .= "$z_t_date10, $z_t_date11, $z_t_date12, $z_t_rep1, $z_t_rep2, $z_t_rep3, $z_t_rep4, ";
					
					$fields .= "Z_T_REP5,  Z_T_REP6,  Z_T_REP7,  Z_T_REP8,  Z_T_REP9,  Z_T_REP10,  Z_T_REP11";
					$values .= "$z_t_rep5, $z_t_rep6, $z_t_rep7, $z_t_rep8, $z_t_rep9, $z_t_rep10, $z_t_rep11";
				}
				else 
				{
					$fields .= "Z_C_LET1,  Z_C_LET2,  Z_C_LET3,  Z_C_LET1DATE,  Z_C_LET2DATE,  Z_C_LET3DATE,  Z_C_COLDATE,  ";
					$values .= "$z_c_let1, $z_c_let2, $z_c_let3, $z_c_let1date, $z_c_let2date, $z_c_let3date, $z_c_coldate, ";
					
					$fields .= "Z_C_CONTLET,  Z_C_L_CONTLET,  Z_C_DEMLET,  Z_C_L_DEMLET,  Z_C_NUMCOL,  Z_C_LCOLDATE,  ";
					$values .= "$z_c_contlet, $z_c_l_contlet, $z_c_demlet, $z_c_l_demlet, $z_c_numcol, $z_c_lcoldate, ";
					
					$fields .= "Z_C_AMOUNTOUT,  Z_C_LCRDATE,  Z_C_PASTPAYCD,  Z_C_LETPEND";
					$values .= "$z_c_amountout, $z_c_lcrdate, $z_c_pastpaycd, $z_c_letpend";
				}
				
				$sql = "INSERT INTO JOB_Z ($fields) VALUES ($values)";
				if ($verbose)
					dprint($sql);
				sql_execute($sql);
			}
			
			$input_count++;
			if (($input_count % 10000) == 0)
				dlog("import_jobs($sys): done $input_count input records so far");
			if ((0 < $stop_after) && ($stop_after < $input_count))
			{
				dlog("Stopping after $stop_after jobs");
				#break; # while(true)
			}
			
		} # while(true)

		$sql = "SELECT COUNT(*) FROM JOB_LETTER WHERE LETTER_TYPE_ID <= 25";
		sql_execute($sql);
		$letters_t = 0;
		while (($newArray = sql_fetch()) != false)
			$letters_t = $newArray[0];
		
		$sql = "SELECT COUNT(*) FROM JOB_LETTER WHERE 26 <= LETTER_TYPE_ID";
		sql_execute($sql);
		$letters_c = 0;
		while (($newArray = sql_fetch()) != false)
			$letters_c = $newArray[0];
		
		$time_taken = 1.0 * (time() - $start_time) / 60.0;
		$per_rec = 60.0 * $time_taken / (1.0 * $job_count / 1000.0);
		dlog("Imported $job_count jobs from a $sys_txt file of " . ($input_count-1) . " jobs (and 1 header record).<br>" .
				"Input file had min sequence of $seq_min, max of $seq_max, <br>" .
				"had $blank_count blank records and had $seq_zero_count zero sequences.<br>" .
				"Trace letters imported: $letters_t (from $letters_t_start). Collection letters imported: $letters_c (from $letters_c_start).<br>" .
				"Trace letters found in DBV: $letters_dbv_yes. Found then lost: $letters_dbv_yes_then_no. Not found in DBV: $letters_dbv_no.<br>" .
				"Time taken: $time_taken mins ($per_rec secs per 1000 jobs)");
		
		fclose($fp_dbf);
		fclose($fp_dbv);
		fclose($fp_csv);
	}
	else
	{
		if (!$fp_dbf)
			dlog("*=* (Sys=$sys) Failed to fopen(\"$dirname/$dbf\",'r')");
		if (!$fp_dbv)
			dlog("*=* (Sys=$sys) Failed to fopen(\"$dirname/$dbv\",'r')");
		if (!$fp_csv)
			dlog("*=* (Sys=$sys) Failed to fopen(\"$dirname/$csv\",'r')");
	}

} # import_jobs()

function import_letter_templates()
{
	# Before calling this function:
	#	- Export TRACE/LETTERS.DBF as LETTERS.csv using DBF Manager with default settings.
	# Required:
	#	t/LETTERS.csv
	#	c/COLET.DBF
	#	c/COLET.DBV
	
	# Import 31/12/16:
	# import_2016_12_31_1014.log
	# 2016-12-31 10:14:25/   POST=Array
	# (
	#     [task] => i_letter_template
	#     [tc] => 
	#     [other] => 
	# )
	
	global $col_nums; # for csvfield()
	global $crlf;
	global $input_count; # for csvfield()
	global $one_record; # for csvfield()
	
	$enabled = false; #
	if (!$enabled)
	{
		dprint("This import function is disabled", true);
		return;
	}

	print "<h3>Importing Letter Templates</h3>";
	
	set_time_limit(10 * 60); # 10 mins
	
	init_letter_templates();
	
	$jtid_trc = job_type_id_from_code('TRC');
	if (!$jtid_trc)
	{
		dprint("*=* Job type TRC not found", true);
		return;
	}
	$jtid_mns = job_type_id_from_code('MNS');
	if (!$jtid_mns)
	{
		dprint("*=* Job type MNS not found", true);
		return;
	}
	$jtid_rpo = job_type_id_from_code('RPO');
	if (!$jtid_rpo)
	{
		dprint("*=* Job type RPO not found", true);
		return;
	}
	$jtid_oth = job_type_id_from_code('OTH');
	if (!$jtid_oth)
	{
		dprint("*=* Job type OTH not found", true);
		return;
	}
	$jtid_svc = job_type_id_from_code('SVC');
	if (!$jtid_svc)
	{
		dprint("*=* Job type SVC not found", true);
		return;
	}
	$jtid_rt1 = job_type_id_from_code('RT1');
	if (!$jtid_rt1)
	{
		dprint("*=* Job type RT1 not found", true);
		return;
	}
	$jtid_rt2 = job_type_id_from_code('RT2');
	if (!$jtid_rt2)
	{
		dprint("*=* Job type RT2 not found", true);
		return;
	}
	$jtid_rt3 = job_type_id_from_code('RT3');
	if (!$jtid_rt3)
	{
		dprint("*=* Job type RT3 not found", true);
		return;
	}
	$jtid_tco = job_type_id_from_code('T/C');
	if (!$jtid_tco)
	{
		dprint("*=* Job type T/C not found", true);
		return;
	}
	$jtid_tmn = job_type_id_from_code('T/M');
	if (!$jtid_tmn)
	{
		dprint("*=* Job type T/M not found", true);
		return;
	}
	$jtid_att = job_type_id_from_code('ATT');
	if (!$jtid_att)
	{
		dprint("*=* Job type ATT not found", true);
		return;
	}

	# --- Import LETTERS.csv ------------------------
	
	$stop_after = 0;#
	$stop_margin = 0;
	$input_count = 0;
	
	$sys = 't';
	$dirname = "import-vilcol/{$sys}";
	$csv = "LETTERS.csv";
	$fp_csv = fopen("$dirname/$csv", 'r');
	if ($fp_csv)
	{
		$col_nums = array();
		
		$while_safety = 0; # to avoid infinite-looping
		while (true)
		{
			$verbose = false;
			if ((0 < $stop_after) && ($input_count <= $stop_after) && (($stop_after - $input_count) < $stop_margin))
				$verbose = true;
			
			$one_record = fgetcsv($fp_csv);
			if ($verbose)
				dprint((($input_count == 0) ? "Headers: " : "Letter: ") . print_r($one_record,1));#
			if ($input_count == 0)
			{
				foreach ($one_record as $num => $field)
					$col_nums[$field] = $num;
				if ($verbose)
					dprint("col_nums: " . print_r($col_nums,1));#
			}
			elseif ($one_record && (count($one_record) > 0))
			{
				$jobtype = csvfield('JOBTYPE');
				$jt_t_job_type_id = 0;
				if ($jobtype == 'Trace')
					$jt_t_job_type_id = $jtid_trc;
				elseif ($jobtype == 'Means')
					$jt_t_job_type_id = $jtid_mns;
				elseif ($jobtype == 'Repo')
					$jt_t_job_type_id = $jtid_rpo;
				elseif ($jobtype == 'Other')
					$jt_t_job_type_id = $jtid_oth;
				elseif ($jobtype == 'Service')
					$jt_t_job_type_id = $jtid_svc;
				elseif ($jobtype == 'Retrace 1')
					$jt_t_job_type_id = $jtid_rt1;
				elseif ($jobtype == 'Retrace 2')
					$jt_t_job_type_id = $jtid_rt2;
				elseif ($jobtype == 'Retrace 3')
					$jt_t_job_type_id = $jtid_rt3;
				elseif ($jobtype == 'T/C')
					$jt_t_job_type_id = $jtid_tco;
				elseif ($jobtype == '.T/M')
					$jt_t_job_type_id = $jtid_tmn;
				elseif ($jobtype == 'Attend.')
					$jt_t_job_type_id = $jtid_att;
				else
				{
					dprint("*=* LETTERS.DBF/JOBTYPE \"$jobtype\" not recognised (record $input_count)");
					return;
				}
				
				$letter_name = csvfield('SUCCESS');
				$temp = yesno2bool($letter_name, false);
				if ($temp === true)
					$jt_t_succ = 1;
				elseif ($temp === false)
					$jt_t_succ = 0;
				elseif (strtolower($letter_name) == 'foc')
					$jt_t_succ = -1;
				else
				{
					dprint("*=* LETTERS.DBF/SUCCESS \"$letter_name\" not recognised (record $input_count)");
					return;
				}
				$letter_name = "{$jobtype}/{$letter_name}";
				$letter_name = quote_smart($letter_name, true);

				$jt_t_open = '';
				$temp = csvfield('OPEN1');
				if ($temp)
					$jt_t_open .= $temp . $crlf;
				$temp = csvfield('OPEN2');
				if ($temp)
					$jt_t_open .= $temp . $crlf;
				$temp = csvfield('OPEN3');
				if ($temp)
					$jt_t_open .= $temp . $crlf;
				$temp = csvfield('OPEN4');
				if ($temp)
					$jt_t_open .= $temp . $crlf;
				$temp = csvfield('OPEN5');
				if ($temp)
					$jt_t_open .= $temp . $crlf;
				$temp = csvfield('OPEN6');
				if ($temp)
					$jt_t_open .= $temp . $crlf;
				$temp = csvfield('OPEN7');
				if ($temp)
					$jt_t_open .= $temp . $crlf;
				$temp = csvfield('OPEN8');
				if ($temp)
					$jt_t_open .= $temp . $crlf;
				$jt_t_open = quote_smart($jt_t_open, true);
				
				$jt_t_close = '';
				$temp = csvfield('CLOSE1');
				if ($temp)
					$jt_t_close .= $temp . $crlf;
				$temp = csvfield('CLOSE2');
				if ($temp)
					$jt_t_close .= $temp . $crlf;
				$temp = csvfield('CLOSE3');
				if ($temp)
					$jt_t_close .= $temp . $crlf;
				$temp = csvfield('CLOSE4');
				if ($temp)
					$jt_t_close .= $temp . $crlf;
				$temp = csvfield('CLOSE5');
				if ($temp)
					$jt_t_close .= $temp . $crlf;
				$temp = csvfield('CLOSE6');
				if ($temp)
					$jt_t_close .= $temp . $crlf;
				$temp = csvfield('CLOSE7');
				if ($temp)
					$jt_t_close .= $temp . $crlf;
				$temp = csvfield('CLOSE8');
				if ($temp)
					$jt_t_close .= $temp . $crlf;
				$jt_t_close = quote_smart($jt_t_close, true);
				
				# --- SQL Insertions --------------------------------------------
				
				$fields = "LETTER_NAME,  JT_T_JOB_TYPE_ID,  JT_T_SUCC,  LETTER_DESCR, JT_T_OPEN,  JT_T_CLOSE ";
				$values = "$letter_name, $jt_t_job_type_id, $jt_t_succ, $letter_name, $jt_t_open, $jt_t_close";
				
				$sql = "INSERT INTO LETTER_TYPE_SD ($fields) VALUES ($values)";
				if ($verbose)
					dprint($sql);
				sql_execute($sql); # no need to audit
			}
			else 
			{
				dprint("End of Trace Letter templates");
				break; # while(true)
			}
			
			$input_count++;
			if ((0 < $stop_after) && ($stop_after < $input_count))
				break; # while(true)
			
			$while_safety++;
			if ($while_safety > 100000) # 100,000 letters
				break; # while(true)
				
		} # while(true)

		$letter_count = $input_count - 1;
		dprint("Imported $letter_count Trace letter templates");
		
		fclose($fp_csv);
	}
	else
		dlog("*=* Failed to fopen(\"$dirname/$csv\",'r')");

	# --- Import COLET.DBF -------------
	
	$sys = 'c'; # collect

	$stop_after = 0;#
	$input_count = 0;
	
	$dirname = "import-vilcol/{$sys}";
	$dbf = "COLET.DBF";
	$dbv = "COLET.DBV";
	$fp_dbf = fopen("$dirname/$dbf", 'r');
	$fp_dbv = fopen("$dirname/$dbv", 'r');
	if ($fp_dbf && $fp_dbv)
	{
		$readlen = 50000;
		$buffer = fread($fp_dbf, $readlen); # read whole file in one go
		$gotlen = strlen($buffer);
		#dprint("gotlen=$gotlen");#
		
		# Get column names
		$off = 32; # first column name is 32 bytes into the file
		$reclen = 32; # each column name is 32 bytes after the previous one
		$pos = $off; # position in buffer
		$headings = array();
		while ($pos <= $gotlen)
		{
			if (substr($buffer, $pos, 1) == chr(13)) # Hex 0D - this is end of column names
			{
				#dprint("End of column names");
				break;
			}
			$col = substr($buffer, $pos, $reclen);
			$endpos = strpos($col, chr(0));
			if ($endpos !== false)
				$col = substr($col, 0, $endpos);
			else 
			{
				dprint("*=* No zero found in \"$col\" (pos=$pos)");
				return;
			}
			#dprint("COL=\"$col\"");
			if ($col)
				$headings[] = $col;
			$pos += $reclen;
		}
		#dprint("Columns = " . print_r($headings,1));#
		
		# Get field values
		$pos += 2; # first field value is 2 bytes after hex 0D
		$records = array();
		$safety = $gotlen; # don't loop more times than there are bytes in the buffer!
		while ($pos <= $gotlen) # get all data records
		{
			$one_rec = array();
			$reclen = 8;
			$one_rec['LETTYPE'] = trim(substr($buffer, $pos, $reclen));
			if ($one_rec['LETTYPE'] == '')
			{
				dprint("Blank LETTYPE - probably end of file");
				break;
			}
			$pos += $reclen;
			$reclen = 6;
			$one_rec['VLF'] = trim(substr($buffer, $pos, $reclen));
			$pos += $reclen;
			$records[] = $one_rec;
			$pos++; # skip over end-of-record character
			
			if (($gotlen <= $pos) || ($buffer[$pos] == chr(26))) # Hex 1A seems to be end of file marker
			{
				dprint("End of File");
				break;
			}
			
			$safety--;
			if ($safety <= 0)
			{
				dprint("*=* Safety ejection!");
				break; # prevent infinite looping
			}
		}
		#dprint("Records = " . print_r($records,1));#
		
		$input_count = 1; # pretend we have skipped over an imaginary header record
		foreach ($records as $one_rec)
		{
			# Read DBV and insert into LETTER_TYPE_SD
		
			$letter_name = quote_smart($one_rec['LETTYPE'], true);
			$vlf = $one_rec['VLF'];
			$vlf_ascii = "" . ord($vlf[0]) . "," . ord($vlf[1]) . "," . ord($vlf[2]) . "," . 
								ord($vlf[3]) . "," . ord($vlf[4]) . "," . ord($vlf[5]);
			#dprint("VLF ASCII = \"$vlf_ascii\"");#
			
			$dbv_offset = vlf_convert_raw($vlf);
			if ($verbose)
				dprint("VLF/{$input_count}: \"$vlf\" = \"$vlf_ascii\" -> offset=\"$dbv_offset\"");#
			if ($dbv_offset > 0)
			{
				fseek($fp_dbv, $dbv_offset);
				$dbv_readlen = 50000;
				$dbv_buffer = fread($fp_dbv, $dbv_readlen);
				$gotlen = strlen($dbv_buffer);
				if ($gotlen < 10)
				{
					dprint("*=* DBV Read problem: VLF: \"$vlf\" = \"$vlf_ascii\" -> offset=\"$dbv_offset\". " .
						(($gotlen > 0) ? "Only $gotlen" : "No") . " bytes read. " .
						"\$input_count = $input_count", true);
					return;
				}
				# First 6 bytes are size of Pascal TDBVStru structure.
				if ($verbose)
				{
					$six = "" . ord($dbv_buffer[0]) . "," . ord($dbv_buffer[1]) . "," . ord($dbv_buffer[2]) . "," . 
								ord($dbv_buffer[3]) . "," . ord($dbv_buffer[4]) . "," . ord($dbv_buffer[5]);
					dprint("DBV 6-pack = \"$six\"");
				}
				$dbv_len = 0;
				$dbv_len += ord($dbv_buffer[0]);
				$dbv_len += (ord($dbv_buffer[1]) << 8);
				$dbv_len += (ord($dbv_buffer[2]) << 16);
				$dbv_len += (ord($dbv_buffer[3]) << 32);
				if ($verbose)
					dprint("DBV Letter length = $dbv_len");#
				$letter_template = substr($dbv_buffer, 6, $dbv_len);
				if ($verbose)
					dprint("Letter/A(" . strlen($letter_template) . ")=\"$letter_template\"");
				$letter_template = quote_smart(trim($letter_template), true);
				#dprint("Letter/B(" . strlen($letter_template) . ")=\"$letter_template\"");#
			}
			else 
			{
				dprint("*=* No DBV offset: VLF: \"$vlf\" = \"$vlf_ascii\" -> offset=\"$dbv_offset\". " .
						"\$input_count = $input_count", true);
				return;
			}
			
			
			# --- SQL Insertions --------------------------------------------
			
			$fields = "LETTER_NAME,  JT_T_JOB_TYPE_ID, LETTER_DESCR, LETTER_TEMPLATE ";
			$values = "$letter_name, NULL,             $letter_name, $letter_template";
			
			$sql = "INSERT INTO LETTER_TYPE_SD ($fields) VALUES ($values)";
			if ($verbose)
				dprint($sql);
			sql_execute($sql); # no need to audit

			$input_count++;
		} # foreach ($records)
			
		$letter_count = $input_count - 1;
		dprint("Imported $letter_count CoLet letter templates");
		
		fclose($fp_dbf);
		fclose($fp_dbv);
	}
	else
	{
		if (!$fp_dbf)
			dlog("*=* Failed to fopen(\"$dirname/$dbf\",'r')");
		if (!$fp_dbv)
			dlog("*=* Failed to fopen(\"$dirname/$dbv\",'r')");
	}
	
	
	# --- Create Collection Templates that are not in template file but are referenced by old jobs.
	
	$extra = array(
			"'Allpay'",
			"'Debit Ca'",
			"'Discount'",
			"'High Dis'",
			"'Rcpt'",
			"'Refusal'",
			"'Reve'",
			"'Reveiw l'",
			"'Standing'",
			"'Swalp'",
			);
	foreach ($extra as $letter_name)
	{
		$letter_template = "'(Template not found in old system.)'";
	
		$fields = "LETTER_NAME,  JT_T_JOB_TYPE_ID, LETTER_DESCR, LETTER_TEMPLATE ";
		$values = "$letter_name, NULL,             $letter_name, $letter_template";
		
		$sql = "INSERT INTO LETTER_TYPE_SD ($fields) VALUES ($values)";
		#if ($verbose)
			dprint($sql);
		sql_execute($sql); # no need to audit
	}
	
} # import_letter_templates()

function init_col_letters()
{
	global $sqlTrue;
	
	$sql = "DELETE FROM JOB_LETTER WHERE (26 <= LETTER_TYPE_ID) AND (IMPORTED = $sqlTrue)";
	dprint($sql);
	sql_execute($sql); # no need to audit
}

function init_arrange()
{
	global $sqlTrue;
	
	$sql = "DELETE FROM JOB_ARRANGE WHERE (IMPORTED = $sqlTrue)";
	dprint($sql);
	sql_execute($sql); # no need to audit
}

function init_phones()
{
	global $sqlTrue;
	
	$sql = "DELETE FROM JOB_PHONE WHERE (IMP_PH = $sqlTrue) AND (IMPORTED = $sqlTrue)";
	dprint($sql);
	sql_execute($sql); # no need to audit
}

function init_col_2015()
{
	if (sql_table_exists('COLLECT_DBF_2015'))
	{
		$sql = "DROP TABLE COLLECT_DBF_2015";
		dprint($sql);
		sql_execute($sql); # no need to audit
	}

	$sql = "
		CREATE TABLE [dbo].[COLLECT_DBF_2015](
			[COLLECT_DBF_2015_ID] [int] IDENTITY(1,1) NOT NULL,
			[SEQUENCE] [int] NOT NULL,
			[VILNO] [int] NOT NULL,
			[LASTNAME] [varchar](100) NULL,
			[FIRSTNAME] [varchar](100) NULL,
			[COMPNAME] [varchar](100) NULL,
			[HOMEADD1] [varchar](100) NULL,
			[EMAIL] [varchar](100) NULL,
			[MOBILE] [varchar](100) NULL,
			[CLOSEDATE] [date] NULL,
			[CLID] [int] NULL,
			[CLIREF] [varchar](100) NULL,
			[DATEREC] [date] NULL,
		 CONSTRAINT [PK_COLLECT_DBF_2015] PRIMARY KEY CLUSTERED 
		(
			[COLLECT_DBF_2015_ID] ASC
		)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON) ON [PRIMARY]
		) ON [PRIMARY]
		";
	dprint((strlen($sql) > 100) ? (substr($sql, 0, 100) . "...") : $sql);
	sql_execute($sql); # no need to audit

	$sql = "
		CREATE NONCLUSTERED INDEX [IX_NAME_ETC] ON [dbo].[COLLECT_DBF_2015]
		(
			[DATEREC] ASC,
			[LASTNAME] ASC,
			[CLIREF] ASC,
			[CLID] ASC
		)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, SORT_IN_TEMPDB = OFF, DROP_EXISTING = OFF, ONLINE = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON)
		";
	dprint((strlen($sql) > 100) ? (substr($sql, 0, 100) . "...") : $sql);
	sql_execute($sql); # no need to audit
	
} # init_col_2015()

function init_job_act_table()
{
	if (sql_table_exists('JOB_ACT'))
	{
		$sql = "DROP TABLE JOB_ACT";
		dprint($sql);
		sql_execute($sql); # no need to audit
	}

	$sql = "
		CREATE TABLE [dbo].[JOB_ACT](
			[JOB_ACT_ID] [int] IDENTITY(1,1) NOT NULL,
			[JOB_ID] [int] NOT NULL,
			[ACTIVITY_ID] [int] NOT NULL,
			[JA_DT] [datetime] NULL,
			[JA_NOTE] [varchar](2000) NULL,
			[QC_EXPORT_ID] [int] NULL,
			[IMPORTED] [bit] NULL,
		 CONSTRAINT [PK_JOB_ACT] PRIMARY KEY CLUSTERED 
			(
				[JOB_ACT_ID] ASC
			)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, 
			ALLOW_PAGE_LOCKS = ON) ON [PRIMARY]
		) ON [PRIMARY]
		";
	dprint((strlen($sql) > 100) ? (substr($sql, 0, 100) . "...") : $sql);
	sql_execute($sql); # no need to audit

	$sql = "
		ALTER TABLE [dbo].[JOB_ACT] ADD  CONSTRAINT [DF_JOB_ACT_IMPORTED]  DEFAULT ((0)) FOR [IMPORTED]
		";
	dprint((strlen($sql) > 100) ? (substr($sql, 0, 100) . "...") : $sql);
	sql_execute($sql); # no need to audit

	$sql = "
		CREATE NONCLUSTERED INDEX [IX_JOB_ID] ON [dbo].[JOB_ACT]
		(
			[JOB_ID] ASC
		)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, SORT_IN_TEMPDB = OFF, DROP_EXISTING = OFF, ONLINE = OFF, 
		ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON)
		";
	dprint((strlen($sql) > 100) ? (substr($sql, 0, 100) . "...") : $sql);
	sql_execute($sql); # no need to audit

	$sql = "
		CREATE NONCLUSTERED INDEX [IX_ACTIVITY_ID] ON [dbo].[JOB_ACT]
		(
			[ACTIVITY_ID] ASC
		)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, SORT_IN_TEMPDB = OFF, DROP_EXISTING = OFF, 
		ONLINE = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON)
		";
	dprint((strlen($sql) > 100) ? (substr($sql, 0, 100) . "...") : $sql);
	sql_execute($sql); # no need to audit
	
} # init_job_act_table()

function init_tdx()
{
//	global $sqlTrue;
//	
//	$new_ID_values = true;
//	
//	if ($new_ID_values)
		init_job_act_table();
//	else
//	{
//		$sql = "DELETE FROM JOB_ACT WHERE (IMPORTED = $sqlTrue)";
//		dprint($sql);
//		sql_execute($sql); # no need to audit
//	}
}

function init_trans()
{
	$sql = "UPDATE JOB SET JC_TRANS_ID=NULL, JC_TRANS_CNUM=NULL";
	dprint($sql);
	sql_execute($sql); # no need to audit
}

function init_trans_assignment_ids()
{
	$sql = "UPDATE JOB SET JC_TRANS_CNUM=NULL";
	dprint($sql);
	sql_execute($sql); # no need to audit
}

function init_tcflags()
{
	global $sqlFalse;
	
	$sql = "UPDATE CLIENT2 SET C_TRACE=$sqlFalse, C_COLLECT=$sqlFalse";
	dprint($sql);
	sql_execute($sql); # no need to audit
}

function init_payments($rebuild_table)
{
	global $sqlTrue;
	
	if ($rebuild_table)
	{
		if (sql_table_exists('JOB_PAYMENT'))
		{
			$sql = "DROP TABLE JOB_PAYMENT";
			dprint($sql);
			sql_execute($sql); # no need to audit
		}

		$sql = "CREATE TABLE [dbo].[JOB_PAYMENT](
					[JOB_PAYMENT_ID] [int] IDENTITY(1,1) NOT NULL,
					[JOB_ID] [int] NOT NULL,
					[COL_PERCENT] [decimal](12, 3) NULL,
					[COL_AMT_DUE] [decimal](19, 4) NULL,
					[COL_AMT_RX] [decimal](19, 4) NULL,
					[COL_PAYMENT_METHOD_ID] [int] NULL,
					[COL_METHOD_NUMBER] [varbinary](256) NULL,
					[COL_DT_DUE] [datetime] NULL,
					[COL_DT_RX] [datetime] NULL,
					[COL_PAYMENT_ROUTE_ID] [int] NULL,
					[COL_BOUNCED] [bit] NOT NULL,
					[COL_NOTES] [varbinary](2000) NULL,
					[ADJUSTMENT_ID] [int] NULL,
					[INVOICE_ID] [int] NULL,
					[IMPORTED] [bit] NOT NULL,
					[OBSOLETE] [bit] NOT NULL CONSTRAINT [DF_JOB_PAYMENT_OBSOLETE]  DEFAULT ((0)),
					[Z_CLID] [int] NULL,
					[Z_DATE] [varchar](10) NULL,
				 CONSTRAINT [PK_JOB_PAYMENT] PRIMARY KEY CLUSTERED 
				(
					[JOB_PAYMENT_ID] ASC
				)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, 
				ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON) ON [PRIMARY]
				) ON [PRIMARY]
			";
		dprint((strlen($sql) > 100) ? (substr($sql, 0, 100) . "...") : $sql);
		sql_execute($sql); # no need to audit

		$sql = "ALTER TABLE [dbo].[JOB_PAYMENT] ADD  CONSTRAINT [DF_JOB_PAYMENT_COL_BOUNCED]  DEFAULT ((0)) FOR [COL_BOUNCED]
			";
		dprint((strlen($sql) > 100) ? (substr($sql, 0, 100) . "...") : $sql);
		sql_execute($sql); # no need to audit

		$sql = "ALTER TABLE [dbo].[JOB_PAYMENT] ADD  CONSTRAINT [DF_JOB_PAYMENT_IMPORTED]  DEFAULT ((0)) FOR [IMPORTED]
			";
		dprint((strlen($sql) > 100) ? (substr($sql, 0, 100) . "...") : $sql);
		sql_execute($sql); # no need to audit

		$sql = "CREATE NONCLUSTERED INDEX [IX_JOB_ID] ON [dbo].[JOB_PAYMENT]
				(
					[JOB_ID] ASC
				)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, SORT_IN_TEMPDB = OFF, DROP_EXISTING = OFF, 
				ONLINE = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON)
			";
		dprint((strlen($sql) > 100) ? (substr($sql, 0, 100) . "...") : $sql);
		sql_execute($sql); # no need to audit
	}
	else
	{
		$sql = "DELETE FROM JOB_PAYMENT WHERE IMPORTED = $sqlTrue";
		dprint($sql);
		sql_execute($sql); # no need to audit
	}
}

function import_col_letters()
{
	# Before calling this function:
	#	- Export COLLECT/AUTO_LS.DBF as AUTO_LS.csv using DBF Manager with default settings.
	# Required:
	#	c/AUTO_LS.csv
	
	# Import 31/12/16:
	#	import_2016_12_31_1748.log
	#	2016-12-31 17:48:47/   POST=Array
	#	(
	#	    [task] => i_col_letters
	#	    [tc] => 
	#	    [other] => 
	#	)
	#	This crashed without any obvious cause after a few hours; last log entry:
	#		2016-12-31 19:47:55/1 input_count = 1530000
	#	So set: $incremental = true
	#	Ran again.
	#	import_2017_01_01_0840.log
	#	2017-01-01 08:40:03/1 Max DT = "2013-11-25 00:00:00.000" from 
	#		SELECT MAX(JL_POSTED_DT) FROM JOB_LETTER WHERE (26 <= LETTER_TYPE_ID) AND (IMPORTED = 1)
	#	2017-01-01 08:56:59/1 Imported 168357 letters from 1717085 inputs (fget count = 1717087)
	#			(7 inputs referred to non-existent job)
	#			Letter Types Not Found: 0: Array()
	#	
	
	# Server:
	#	28/11/16
	#		**INCREMENTAL** 2016-11-28 09:37:37/1 Imported 207,744 letters from 1,692,075 inputs (fget count = 1,692,077)
	#												(7 inputs referred to non-existent job)
	#												Letter Types Not Found: 0
	#		Didn't crash on incremental run.
	#		SELECT COUNT(*) FROM JOB_LETTER WHERE 26 <= LETTER_TYPE_ID AND IMPORTED=1 --> 1,172,189

	#	24/11/16 and 25/11/16:
	#		This function is crashing when $input_count is between 1,540,000 and  1,550,000; reason unknown.
	#		But then it crashed at some point after 1,400,000!! So it's not a data issue.
	#		The input count should get to 1,692,075.
	#		It has never worked before on the server because a bug was causing it to exit after an input count of 800K,
	#		but it works on my local PC (see below).
	#		When it crashes, the number of collection letters inserted is 1,035,981 (it should get to 1,182,906).
	#		More debug-ridden test started at 2016-11-25 21:54:02.

	# Local PC:
	# 2016-11-24 16:39:11/1 Imported 1,182,906 letters from 1,692,075 inputs (fget count = 1,692,077)
	#				(509,169 inputs referred to non-existent job)
	#				Letter Types Not Found: 0
	
	# 2016-09-27 18:56:34/1 Imported 323855 letters from 799999 inputs (476144 inputs referred to non-existent job)
	#	- **BUG** loop exited after 800,000 but there are more letters that this!!
		
	# 2016-01-07 18:35:55/1 Imported 328397 letters from 799999 inputs (471602 inputs referred to non-existent job)
	#	- **BUG** loop exited after 800,000 but there are more letters that this!!

	global $col_nums; # for csvfield()
	global $crlf;
	global $input_count; # for csvfield()
	global $one_record; # for csvfield()
	global $sqlNow;
	global $sqlTrue;
	
	$enabled = false; #
	if (!$enabled)
	{
		dprint("This import function is disabled", true);
		return;
	}

	$incremental = false;
	#$incremental = true;#
	
	print "<h3>Importing Collection Letters " . ($incremental ? "**INCREMENTAL**" : '') . "</h3>";
	
	set_time_limit(10 * 60 * 60); # 10 hours
	
	if (!$incremental)
		init_col_letters();

	$sys = 'c'; # collect

	$stop_after = 0;#
	$stop_margin = 0;
	$input_count = 0;
	$fget_count = 0;
	$output_count = 0;
	$job_not_found = 0;
	$letter_types_not_found = array();
	
	$max_dt = '';
	if ($incremental)
	{
		$sql = "SELECT MAX(JL_POSTED_DT) FROM JOB_LETTER WHERE (26 <= LETTER_TYPE_ID) AND (IMPORTED = $sqlTrue)";
		sql_execute($sql);
		while (($newArray = sql_fetch()) != false)
			$max_dt = $newArray[0];
		dlog("Max DT = \"$max_dt\" from $sql");
		
		$sql = "DELETE FROM JOB_LETTER WHERE (26 <= LETTER_TYPE_ID) AND (IMPORTED = $sqlTrue) AND (JL_POSTED_DT='$max_dt')";
		dprint($sql);
		sql_execute($sql); # no need to audit
	}
	
	$dirname = "import-vilcol/{$sys}";
	$csv = "AUTO_LS.csv";
	$fp_csv = fopen("$dirname/$csv", 'r');
	if ($fp_csv)
	{
		sql_encryption_preparation('JOB_LETTER');
		$col_nums = array();
		
		$while_safety = 0; # to avoid infinite-looping
		while (true)
		{
			$verbose = false;
			if ((0 < $stop_after) && ($input_count <= $stop_after) && (($stop_after - $input_count) < $stop_margin))
				$verbose = true;
			
			$while_safety++;
			if ($while_safety > 10000000) # 10,000,000 letters
				break; # while(true)
				
			$one_record = fgetcsv($fp_csv);
			$fget_count++;
			
			if ($verbose)
				dlog((($input_count == 0) ? "Headers: " : "Letter: ") . print_r($one_record,1));#
			if ($input_count == 0)
			{
				foreach ($one_record as $num => $field)
					$col_nums[$field] = $num;
				if ($verbose)
					dlog("col_nums: " . print_r($col_nums,1));#
			}
			elseif ($one_record && (count($one_record) > 0))
			{
				if (csvfield('DATE', 'uk') != '')
				{
					$jl_posted_dt = csvfield('DATE', 'uk');
					if ($incremental && ($jl_posted_dt < $max_dt))
					{
						$input_count++;
						if ((($input_count % 100000) == 0) || ((1500000 <= $input_count) && (($input_count % 10000) == 0))) dlog("input_count = $input_count");
						continue;
					}
					$jl_posted_dt = "'{$jl_posted_dt}'";
				}
				else 
					$jl_posted_dt = '';
					
				$sql = "SELECT JOB_ID FROM JOB WHERE J_SEQUENCE = " . (1 * csvfield('SEQUENCE'));
				if ($verbose)
					dlog($sql);
				sql_execute($sql);
				$job_id = -1;
				while (($newArray = sql_fetch()) != false)
					$job_id = $newArray[0];
				if (!(0 < $job_id))
				{
					# There are very many records which fall into this case,
					# so let's assume that they are jobs that have been archived,
					# and not report an error.
					#dlog("*=* JOB_ID not found from Sequence \"" . csvfield('SEQUENCE') . "\"");
					$input_count++;
					if ((($input_count % 100000) == 0) || ((1500000 <= $input_count) && (($input_count % 10000) == 0))) dlog("input_count = $input_count");
					$job_not_found++;
					continue;
				}
				
				$csv_letter = csvfield('LETTER');
				$letter_type_id = letter_type_id_from_name($csv_letter);
				if (!(0 < $letter_type_id))
				{
					dlog("*=* Letter Type ID not found from letter name \"$csv_letter\"");
					$input_count++;
					if ((($input_count % 100000) == 0) || ((1500000 <= $input_count) && (($input_count % 10000) == 0))) dlog("input_count = $input_count");
					if (array_key_exists($csv_letter, $letter_types_not_found))
						$letter_types_not_found[$csv_letter]++;
					else
						$letter_types_not_found[$csv_letter] = 1;
					continue;
				}
				
				$jl_text = sql_encrypt("(Imported from old system: letter text not available.)", '', 'JOB_LETTER');
				
				$fields = "JOB_ID,  LETTER_TYPE_ID,  IMPORTED, JL_ADDED_DT, JL_APPROVED_DT, JL_TEXT ";
				$values = "$job_id, $letter_type_id, $sqlTrue, $sqlNow,     '1977-01-01',   $jl_text";
				
				if ($jl_posted_dt)
				{
					$fields .= ", JL_POSTED_DT";
					$values .= ", $jl_posted_dt";
				}
					
				$sql = "INSERT INTO JOB_LETTER ($fields) VALUES ($values)";
				if ($verbose)
					dlog($sql);
				$job_letter_id = sql_execute($sql);
				if ($verbose)
					dlog("-> ID $job_letter_id");
				$output_count++;
			}
			else 
			{
				dlog("End of Letters");
				break; # while(true)
			}
			
			$input_count++;
			if ((($input_count % 100000) == 0) || ((1500000 <= $input_count) && (($input_count % 10000) == 0))) dlog("input_count = $input_count");
			if ((0 < $stop_after) && ($stop_after < $input_count))
				break; # while(true)
				
		} # while(true)

		$letter_count = $input_count - 1;
		dlog("Imported $output_count letters from $letter_count inputs (fget count = $fget_count) <br>$crlf
				($job_not_found inputs referred to non-existent job) <br>$crlf
				Letter Types Not Found: " . count($letter_types_not_found) . ": " . print_r($letter_types_not_found,1));
		
		fclose($fp_csv);
	}
	else
	{
		if (!$fp_csv)
			dlog("*=* Failed to fopen(\"$dirname/$csv\",'r')");
	}

} # import_col_letters()

function import_phones()
{
	# Collect only: PHONES.DBF
	
	# Before calling this function:
	#	- Export COLLECT/PHONES.DBF as PHONES.csv using DBF Manager with default settings.
	# Required:
	#	c/PHONES.csv

	# Import 31/12/16:
	#	import_2017_01_01_0905.log
	#	2017-01-01 09:21:44/1 *=* PHONE record empty (input_count=295249) Record=<br>Array
	#	(
	#		[0] => 1357230
	#		[1] => 
	#		[2] => 
	#	)
	#	2017-01-01 09:21:47/1 *=* PHONE record empty (input_count=295825) Record=<br>Array
	#	(
	#		[0] => 1363747
	#		[1] => 
	#		[2] => 
	#	)
	#	2017-01-01 09:23:00/1 *=* PHONE record empty (input_count=310104) Record=<br>Array
	#	(
	#		[0] => 1364782
	#		[1] => 
	#		[2] => 
	#	)
	#	2017-01-01 09:23:23/1 *=* PHONE record empty (input_count=315157) Record=<br>Array
	#	(
	#		[0] => 1371779
	#		[1] => 
	#		[2] => 
	#	)
	#	2017-01-01 09:33:46/1 *=* PHONE record empty (input_count=447878) Record=<br>Array
	#	(
	#		[0] => 1507788
	#		[1] => 
	#		[2] => Home Phone
	#	)
	#	2017-01-01 09:35:47/1 End of Phones
	#	2017-01-01 09:35:47/1 Imported 316678 phones from 474878 records
	#	SELECT COUNT(*) FROM JOB_PHONE >> 893,382
		
	
	# 2016-01-08 09:37:23/1 Imported 286128 phones from 446797 records

	# 2016-09-27 22:13:08/1 Imported 294115 phones from 452170 records

	global $col_nums; # for csvfield()
	global $input_count; # for csvfield()
	global $one_record; # for csvfield()
	#global $sqlNow;
	global $sqlTrue;
	
	$enabled = false; #
	if (!$enabled)
	{
		dprint("This import function is disabled", true);
		return;
	}
	
	print "<h3>Importing Collection Phone Numbers</h3>";
	
	set_time_limit(10 * 60); # 10 mins
	
	init_phones();

	$sys = 'c'; # collect

	$stop_after = 0;#
	$stop_margin = 0;
	$input_count = 0;
	$output_count = 0;
	
	$dirname = "import-vilcol/{$sys}";
	$csv = "PHONES.csv";
	$fp_csv = fopen("$dirname/$csv", 'r');
	if ($fp_csv)
	{
		sql_encryption_preparation('JOB_PHONE');
		$col_nums = array();
		
		$while_safety = 0; # to avoid infinite-looping
		while (true)
		{
			$verbose = false;
			if ((0 < $stop_after) && ($input_count <= $stop_after) && (($stop_after - $input_count) < $stop_margin))
				$verbose = true;
			$print_buffer = array();
			if ($verbose)
				$print_buffer[] = "Starting record, input_count=$input_count";
				
			$while_safety++;
			if ($while_safety > 800000) # 800,000 phones
				break; # while(true)
				
			$one_record = fgetcsv($fp_csv);
			if ($verbose)
				$print_buffer[] = ((($input_count == 0) ? "Headers: " : "Phone: ") . print_r($one_record,1));#
			if ($input_count == 0)
			{
				foreach ($one_record as $num => $field)
					$col_nums[$field] = $num;
				if ($verbose)
					$print_buffer[] = ("col_nums: " . print_r($col_nums,1));#
			}
			elseif ($one_record && (count($one_record) > 0))
			{
				if (csvfield('PHONE') != '')
				{
					$sql = "SELECT JOB_ID FROM JOB WHERE J_VILNO = " . (1 * csvfield('VILNO'));
					if ($verbose)
						$print_buffer[] = $sql;
					sql_execute($sql);
					$jobs = array();
					while (($newArray = sql_fetch()) != false)
						$jobs[] = $newArray[0];
					if (count($jobs) == 0)
					{
						$print_buffer[] = ("*=* JOB_ID not found from VILNO \"" . csvfield('VILNO') . "\"");
						$input_count++;
						if ((0 < $stop_after) && ($stop_after < $input_count))
							break; # while(true)
						continue;
					}
					
					$jp_phone = sql_encrypt(csvfield('PHONE'), '', 'JOB_PHONE');
					$jp_descr = sql_encrypt(csvfield('DESC'), '', 'JOB_PHONE');
					
					foreach ($jobs as $job_id)
					{
						$fields = "JOB_ID,  JOB_SUBJECT_ID, JP_PHONE,  IMPORTED, IMP_PH,   JP_DESCR";
						$values = "$job_id, NULL,           $jp_phone, $sqlTrue, $sqlTrue, $jp_descr";
						
						$sql = "INSERT INTO JOB_PHONE ($fields) VALUES ($values)";
						if ($verbose)
							$print_buffer[] = $sql;
						$job_phone_id = sql_execute($sql);
						$output_count++;
						if ($verbose)
							$print_buffer[] = ("-> ID $job_phone_id");
					}
				}
				else 
					dlog("*=* PHONE record empty (input_count=$input_count) Record=<br>" . print_r($one_record,1));
				
				foreach ($print_buffer as $print_line)
					dlog($print_line);
			}
			else 
			{
				dlog("End of Phones");
				break; # while(true)
			}
			
			$input_count++;
			if ((0 < $stop_after) && ($stop_after < $input_count))
				break; # while(true)
				
		} # while(true)

		$phone_count = $input_count - 1;
		dlog("Imported $output_count phones from $phone_count records");
		
		fclose($fp_csv);
	}
	else
	{
		if (!$fp_csv)
			dlog("*=* Failed to fopen(\"$dirname/$csv\",'r')");
	}

} # import_phones()

function import_col_2015()
{
	# Collect only: import COLLECT_2015.csv (from an export of COLLECT.DBF) into COLLECT_DBF_2015 database table.
	# Note there seem to be no trace jobs with a sequence of -1 so a TRACE_DBF_2015 isn't needed.
	
	# Before calling this function:
	#	- Export the Jan 2015 version of COLLECT.DBF as COLLECT_2015.csv using DBF Manager with default settings.
	# Required:
	#	c/COLLECT_2015.csv

	# Import 31/12/16:
	
	
	
	global $col_nums; # for csvfield()
	global $input_count; # for csvfield()
	global $j_sequence; # for csvfield()
	global $one_record; # for csvfield()
	global $tc;
		
	$enabled = false; #
	if (!$enabled)
	{
		dprint("This import function is disabled", true);
		return;
	}
	if ($tc != 'c')
	{
		dlog("*=* import_col_2015() - illegal tc \"$tc\"");
		return;
	}

	print "<h3>Importing COLLECT_2015 (" . (($tc == 'c') ? "Collect" : "Trace") . " system)</h3>";
	
	$start_time = time();
	set_time_limit(60 * 60 * 8); # 8 hours
	# The call to set_time_limit() doesn't appear to help on my PC, so let's try these too:
	ini_set('max_execution_time', 60 * 60 * 8); # 8 hours (still has no effect on my PC)
	ini_set('max_input_time', 60 * 60 * 8); # 8 hours (still has no effect on my PC)
	ini_set('mysql.connect_timeout', 60 * 60 * 8); # 8 hours (still has no effect on my PC)
	ini_set('default_socket_timeout', 60 * 60 * 8); # 8 hours (still has no effect on my PC)
	
	init_col_2015();
	
	$stop_after = 0;#
	$stop_margin = 0;
	$input_count = 0;
	
	$sys = $tc; # 'c'
	$dirname = "import-vilcol/{$sys}";
	$sys_txt = "Collections";

	$csv = "COLLECT_2015.csv";
	$fp_csv = fopen("$dirname/$csv", 'r');
	if ($fp_csv)
	{
		$col_nums = array();
		
		$seq_min = 0;
		$seq_max = 0;
		$seq_zero_count = 0;
		$seq_zero_replacement = 101;
		$job_count = 0;
		
		$while_safety = 0; # to avoid infinite-looping
		while (true)
		{
			$verbose = false;
			if ((0 < $stop_after) && ($input_count <= $stop_after) && (($stop_after - $input_count) < $stop_margin))
				$verbose = true;
			
			$while_safety++;
			if ($while_safety > 800000) # 800,000 jobs
			{
				dlog("*=* (Sys=$sys) Safety Ejection!");
				break; # while(true)
			}

			if ($verbose)
				dprint("=== Record $input_count ===");
				
			$one_record = fgetcsv($fp_csv);
			if ($input_count > 0) # skip header record
			{
				if ($one_record && (count($one_record) > 0)) # test for end-of-records
				{
					$seq_raw = csvfield('SEQUENCE');
					$j_sequence = 1 * $seq_raw;
					if ($j_sequence > 0)
					{
						if (($seq_min == 0) || ($j_sequence < $seq_min))
							$seq_min = $j_sequence;
						if (($seq_max == 0) || ($seq_max < $j_sequence))
							$seq_max = $j_sequence;
					}
					else
					{
						$j_sequence = $seq_zero_replacement++;
						$seq_zero_count++;
						if ($seq_zero_count <= 1000)
							dlog("*=* (Sys=$sys) Non-numeric or illegal SEQUENCE \"$seq_raw\" changed to $j_sequence " .
									"(record $input_count, VILNO=\"" . csvfield('VILNO') . "\", \$seq_zero_count=$seq_zero_count)");
					}
				}
				else 
				{
					dlog("End of C2015 Jobs");
					break; # while(true)
				}
				
				$old_verbose = $verbose;
				if (($j_sequence == 90149542) || ($j_sequence == 90493801)
						#|| (($one_record[0] == -1) && (trim($one_record[8]) == '54B MORAY ROAD')))
						|| in_fix_aux_list($one_record))
				{
					#$verbose = true; #
					if ($verbose)
						dlog("*=* Found sequence=$j_sequence. col_nums:<br>" . print_r($col_nums,1) . "<br>" . 
								"Record:<br>" . print_r($one_record,1));
					for ($ii = 88; 36 <= $ii; $ii--)
						$one_record[$ii] = $one_record[$ii-4];
					$bits = explode(',', str_replace('"', '', $one_record[31]));
					$one_record[31] = str_replace('\\', '', $bits[0]);
					$one_record[32] = $bits[1];
					$one_record[33] = $bits[2];
					$one_record[34] = $bits[3];
					$one_record[35] = $bits[4];
					if ($verbose)
						dlog("Cleaned Record:<br>" . print_r($one_record,1));
				}
				elseif ($j_sequence == 730508)
				{
					#$verbose = true; #
					if ($verbose)
					{
						dlog("*=* Found sequence=$j_sequence. col_nums:<br>" . print_r($col_nums,1) . "<br>" . 
								"Record:<br>" . print_r($one_record,1));
					}
					$one_record[70] = str_replace('foren\",m', 'forenm', $one_record[70]);
					$one_record[71] = str_replace('"', '', $one_record[71]);
					if ($verbose)
						dlog("Cleaned:<br>" . print_r($one_record,1));
				}
				elseif ($j_sequence == 90503059)
				{
					#$verbose = true; #
					if ($verbose)
						dlog("*=* Found sequence=$j_sequence. col_nums:<br>" . print_r($col_nums,1) . "<br>" . 
								"Record:<br>" . print_r($one_record,1));
					for ($ii = 88; 15 <= $ii; $ii--)
						$one_record[$ii] = $one_record[$ii-1];
					$bits = explode(',', str_replace('"', '', $one_record[13]));
					$one_record[13] = str_replace('\\', '', $bits[0]);
					$one_record[14] = $bits[1];
					if ($verbose)
						dlog("Cleaned Record:<br>" . print_r($one_record,1));
				}
				elseif (($j_sequence == 90694238) || ($j_sequence == 90700518))
				{
					#$verbose = true; #
					if ($verbose)
						dlog("*=* Found sequence=$j_sequence. col_nums:<br>" . print_r($col_nums,1) . "<br>" . 
								"Record:<br>" . print_r($one_record,1));
					for ($ii = 88; 28 <= $ii; $ii--)
						$one_record[$ii] = $one_record[$ii-7];
					$bits = explode(',', str_replace('"', '', $one_record[20]));
					$one_record[20] = str_replace('\\', '', $bits[0]);
					$one_record[21] = $bits[1];
					$one_record[22] = $bits[2];
					$one_record[23] = $bits[3];
					$one_record[24] = $bits[4];
					$one_record[25] = $bits[5];
					$one_record[26] = $bits[6];
					$one_record[27] = $bits[7];
					if ($verbose)
						dlog("Cleaned Record:<br>" . print_r($one_record,1));
				}
				elseif (($j_sequence == 90707120) || ($j_sequence == 90738552))
				{
					#$verbose = true; #
					if ($verbose)
						dlog("*=* Found sequence=$j_sequence. col_nums:<br>" . print_r($col_nums,1) . "<br>" . 
								"Record:<br>" . print_r($one_record,1));
					$vlf = $one_record[81];
					if ($verbose)
						dlog("VLF=\"$vlf\"");
					for ($ii = 87; 28 <= $ii; $ii--)
						$one_record[$ii] = $one_record[$ii-7];
					$bits = explode(',', str_replace('"', '', $one_record[20]));
					$one_record[20] = str_replace('\\', '', $bits[0]);
					$one_record[21] = $bits[1];
					$one_record[22] = $bits[2];
					$one_record[23] = $bits[3];
					$one_record[24] = $bits[4];
					$one_record[25] = $bits[5];
					$one_record[26] = $bits[6];
					$one_record[27] = $bits[7];
					$one_record[88] = $vlf;
					if ($verbose)
						dlog("Cleaned Record:<br>" . print_r($one_record,1));
				}
				elseif (($j_sequence == 90714469) || ($j_sequence == 90717737))
				{
					#$verbose = true; #
					if ($verbose)
						dlog("*=* Found sequence=$j_sequence. col_nums:<br>" . print_r($col_nums,1) . "<br>" . 
								"Record:<br>" . print_r($one_record,1));
					$vlf = $one_record[87];
					if ($verbose)
						dlog("VLF=\"$vlf\"");
					for ($ii = 87; 28 <= $ii; $ii--)
						$one_record[$ii] = $one_record[$ii-1];
					$bits = explode(',', str_replace('"', '', $one_record[26]));
					$one_record[26] = str_replace('\\', '', $bits[0]);
					$one_record[27] = $bits[1];
					$one_record[88] = $vlf;
					if ($verbose)
						dlog("Cleaned Record:<br>" . print_r($one_record,1));
				}
				elseif ($j_sequence == 90723905)
				{
					#$verbose = true; #
					if ($verbose)
						dlog("*=* Found sequence=$j_sequence. col_nums:<br>" . print_r($col_nums,1) . "<br>" . 
								"Record:<br>" . print_r($one_record,1));
					$vlf = $one_record[83];
					if ($verbose)
						dlog("VLF=\"$vlf\"");
					for ($ii = 87; 26 <= $ii; $ii--)
						$one_record[$ii] = $one_record[$ii-5];
					$bits = explode(',', str_replace('"', '', $one_record[20]));
					$one_record[20] = str_replace('\\', '', $bits[0]);
					$one_record[21] = $bits[1];
					$one_record[22] = $bits[2];
					$one_record[23] = $bits[3];
					$one_record[24] = $bits[4];
					$one_record[25] = $bits[5];
					$one_record[88] = $vlf;
					if ($verbose)
						dlog("Cleaned Record:<br>" . print_r($one_record,1));
				}
				#if ($j_sequence != 90707120)#
				#	continue;
				$verbose = $old_verbose;
			}
				
			if ((0 < $stop_after) && ($stop_after < $input_count))
			{
				$input_count++;
				continue;
			}
						
			if ($verbose)
				dprint((($input_count == 0) ? "Headers: " : "Job: ") . print_r($one_record,1));#
			if ($input_count == 0)
			{
				# Header record
				foreach ($one_record as $num => $field)
					$col_nums[$field] = $num;
				if ($verbose)
					dprint("col_nums: " . print_r($col_nums,1));#
			}
			else
			{
				# Data record
				
				if (csvfield('CLOSEDATE', 'us') != '')
					$j_closed_dt = "'" . csvfield('CLOSEDATE', 'us') . "'";
				else 
					$j_closed_dt = 'NULL';
				
				$j_vilno = 1 * csvfield('VILNO');
				if (!($j_vilno > 0))
				{
					if ($seq_zero_count <= 1000)
						dlog("*=* (Sys=$sys) Non-numeric or illegal VILNO \"" . csvfield('VILNO') . "\" (record $input_count, " .
								"VILNO=\"" . csvfield('VILNO') . "\", SEQUENCE=\"" . csvfield('SEQUENCE') . "\")");
					$j_vilno = 0;
				}
				
				$js_lastname = quote_smart(csvfield('LASTNAME'), true);
				$js_firstname = quote_smart(csvfield('FIRSTNAME'), true);
				$js_company = quote_smart(csvfield('COMPNAME'), true);
				$homeadd1 = quote_smart(csvfield('HOMEADD1'), true);
				$jp_email = quote_smart(csvfield('EMAIL'), true);
				$jp_mobile = quote_smart(csvfield('MOBILE'), true);
				
				$clid = 1 * csvfield('CLID');
				$client2_id = client_id_from_code($clid);
				if (!($client2_id > 0))
				{
					dlog("*=* (Sys=$sys) Unrecognised CLIENT CODE=\"" . csvfield('CLID') . "\"" .
							" (record $input_count; VilNo=$j_vilno Seq=$j_sequence) \$one_record=" . print_r($one_record,1));
					$client2_id = 0;
				}
					
				$client_ref = quote_smart(csvfield('CLIREF'), true);
				
				if (csvfield('DATEREC', 'us') != '')
					$j_opened_dt = "'" . csvfield('DATEREC', 'us') . "'";
				else 
					$j_opened_dt = 'NULL';
				
				# --- SQL Insertions - COLLECT_DBF_2015 --------------------------------------------

				$fields = "SEQUENCE,    VILNO,    LASTNAME,     FIRSTNAME,     COMPNAME,    HOMEADD1,  EMAIL,     MOBILE,     ";
				$values = "$j_sequence, $j_vilno, $js_lastname, $js_firstname, $js_company, $homeadd1, $jp_email, $jp_mobile, ";
				
				$fields .= "CLOSEDATE,    CLID,  CLIREF,      DATEREC";
				$values .= "$j_closed_dt, $clid, $client_ref, $j_opened_dt";

				$sql = "INSERT INTO COLLECT_DBF_2015 ($fields) VALUES ($values)";
				if ($verbose)
					dprint($sql);
				$job_id = sql_execute($sql); # no need to audit
				if ($verbose)
					dprint("-> ID $job_id");
				$job_count++;
			}
			
			$input_count++;
			if ((0 < $stop_after) && ($stop_after < $input_count))
			{
				dlog("Stopping after $stop_after jobs");
				#break; # while(true)
			}
			
		} # while(true)

		$time_taken = 1.0 * (time() - $start_time) / 60.0;
		$per_rec = 60.0 * $time_taken / (1.0 * $job_count / 1000.0);
		dlog("Imported $job_count C2015 jobs from a $sys_txt file of " . ($input_count-1) . " jobs (and 1 header record).<br>" .
				"Input file had min sequence of $seq_min, max of $seq_max, and had $seq_zero_count zero sequences.<br>" .
				"Time taken: $time_taken mins ($per_rec secs per 1000 jobs)");
		
		fclose($fp_csv);
	}
	else
	{
		if (!$fp_csv)
			dlog("*=* (Sys=$sys) Failed to fopen(\"$dirname/$csv\",'r')");
	}
} # import_col_2015()

function import_arrange()
{
	# Collect only: ARRANGE.DBF
	
	# Before calling this function:
	#	- Export COLLECT/ARRANGE.DBF as ARRANGE.csv using DBF Manager with default settings.
	# Required:
	#	c/ARRANGE.csv
	
	# After calling this function: import_arrange_2() should be called at some point.
	
	# Import 31/12/16:
	#	import_2017_01_01_0940.log
	#	Imported 11218 arrangements from 11896 records
	#	Copied 22164 arrangements from 22163 JOB table records
	
	
	# 08/01/16: Imported 7949 arrangements from 8189 records
	#			Copied 19433 arrangements from 19432 JOB table records
	
	# 27/09/16:
	#	Imported 10489 arrangements from 11134 records
	#	Copied 21538 arrangements from 21537 JOB table records
	
	global $col_nums; # for csvfield()
	global $input_count; # for csvfield()
	global $one_record; # for csvfield()
	global $sqlTrue;
	
	$enabled = false; #
	if (!$enabled)
	{
		dprint("This import function is disabled", true);
		return;
	}
	
	print "<h3>Importing Collection Arrangements</h3>";
	
	set_time_limit(10 * 60); # 10 mins
	
	init_arrange();

	$sys = 'c'; # collect

	$stop_after = 0;#
	$stop_margin = 0;
	$input_count = 0;
	$output_count = 0;
	
	$dirname = "import-vilcol/{$sys}";
	$csv = "ARRANGE.csv";
	$fp_csv = fopen("$dirname/$csv", 'r');
	if ($fp_csv)
	{
		$col_nums = array();
		
		$while_safety = 0; # to avoid infinite-looping
		while (true)
		{
			$print_buffer = array();
			$verbose = false;
			if ((0 < $stop_after) && ($input_count <= $stop_after) && (($stop_after - $input_count) <= $stop_margin))
				$verbose = true;
			if ($verbose)
				$print_buffer[] = "Starting record, input_count=$input_count";
				
			$while_safety++;
			if ($while_safety > 800000) # 800,000 arrangements
				break; # while(true)
				
			$one_record = fgetcsv($fp_csv);
			if ($verbose)
				$print_buffer[] = ((($input_count == 0) ? "Headers: " : "Arrangement: ") . print_r($one_record,1));#
			if ($input_count == 0)
			{
				foreach ($one_record as $num => $field)
					$col_nums[$field] = $num;
				if ($verbose)
					$print_buffer[] = ("col_nums: " . print_r($col_nums,1));#
			}
			elseif ($one_record && (count($one_record) > 0))
			{
				$sql = "SELECT JOB_ID FROM JOB WHERE (J_VILNO = " . (1 * csvfield('VILNO')) . ") AND (JC_JOB=$sqlTrue)";
				if ($verbose)
					$print_buffer[] = $sql;
				sql_execute($sql);
				$jobs = array();
				while (($newArray = sql_fetch()) != false)
					$jobs[] = $newArray[0];
				if (count($jobs) == 0)
				{
					$print_buffer[] = ("*=* JOB_ID not found from VILNO \"" . csvfield('VILNO') . "\"");
					$input_count++;
					if ((0 < $stop_after) && ($stop_after < $input_count))
						break; # while(true)
					continue;
				}
				elseif (count($jobs) > 1)
				{
					$print_buffer[] = ("*=* Arrangement will be added to more than one job with VILNO \"" . csvfield('VILNO') . "\"");
					$input_count++;
					if ((0 < $stop_after) && ($stop_after < $input_count))
						break; # while(true)
				}
				
				if (csvfield('DATE'))
				{
					$ja_dt = csvfield('DATE', 'uk');
					$time = csvfield('TIME');
					if ($time)
					{
						$temp = explode(':', $time);
						if (count($temp) == 3)
							$ja_dt .= " $time";
					}
					$ja_dt = "'" . $ja_dt . "'";
				}
				else
					$ja_dt = 'NULL';
				$colcyc = strtolower(csvfield('COLCYC'));
				if ($colcyc == 'immediate')
					$ja_instal_freq = "'I'";
				elseif ($colcyc == 'quarterly')
					$ja_instal_freq = "'Q'";
				elseif ($colcyc == 'month')
					$ja_instal_freq = "'M'";
				elseif ($colcyc == 'two weeks')
					$ja_instal_freq = "'F'";
				elseif ($colcyc == 'week')
					$ja_instal_freq = "'W'";
				else
					$ja_instal_freq = 'NULL';
				$ja_instal_amt = 1.0 * csvfield('AMOUNTCYC');
				$ja_total = 1.0 * csvfield('TOTAL');
				if (csvfield('STARTCOL', 'uk'))
					$ja_instal_dt_1 = "'" . csvfield('STARTCOL', 'uk') . "'";
				else
					$ja_instal_dt_1 = 'NULL';
				$paymeth = strtolower(csvfield('PAYMETH'));
				if ($paymeth == 'allpay')
					$ja_payment_method_id = 1;
				elseif ($paymeth == 'cash')
					$ja_payment_method_id = 3;
				elseif ($paymeth == 'cheque')
					$ja_payment_method_id = 4;
				elseif ($paymeth == 'credit card')
					$ja_payment_method_id = 24;
				elseif ($paymeth == 'debit card')
					$ja_payment_method_id = 25;
				elseif ($paymeth == 'direct')
					$ja_payment_method_id = 21;
				elseif ($paymeth == 'direct debit')
					$ja_payment_method_id = 23;
				elseif ($paymeth == 'dma')
					$ja_payment_method_id = 6;
				elseif ($paymeth == 'door')
					$ja_payment_method_id = 7;
				elseif ($paymeth == 'gpb')
					$ja_payment_method_id = 8;
				elseif ($paymeth == 'po')
					$ja_payment_method_id = 10;
				elseif ($paymeth == 'standing order')
					$ja_payment_method_id = 26;
				elseif ($paymeth == '')
					$ja_payment_method_id = 'NULL';
				else
				{
					$print_buffer[] = ("*=* ARRANGEMENT payment method \"" . csvfield('PAYMETH') . "\" not recognised; VILNO \"" . csvfield('VILNO') . "\"");
					$ja_payment_method_id = 'NULL';
				}

				$z_depdate = (csvfield('DEPDATE') ? ("'" . csvfield('DEPDATE', 'uk') . "'") : 'NULL');
				$z_depamount = 1.0 * csvfield('DEPAMOUNT');
				$z_remamount = 1.0 * csvfield('REMAMOUNT');
				
				foreach ($jobs as $job_id)
				{
					$fields = "JOB_ID,  JA_DT,  JA_INSTAL_FREQ,  JA_INSTAL_AMT,  JA_INSTAL_DT_1,  JA_PAYMENT_METHOD_ID,  JA_TOTAL, ";
					$values = "$job_id, $ja_dt, $ja_instal_freq, $ja_instal_amt, $ja_instal_dt_1, $ja_payment_method_id, $ja_total, ";

					$fields .= "IMPORTED, Z_DEPDATE,  Z_DEPAMOUNT,  Z_REMAMOUNT";
					$values .= "$sqlTrue, $z_depdate, $z_depamount, $z_remamount";
					
					$sql = "INSERT INTO JOB_ARRANGE ($fields) VALUES ($values)";
					if ($verbose)
					{
						$print_buffer[] = $sql;
						foreach ($print_buffer as $print_line)
							dprint($print_line);
						$print_buffer = array();
					}
					$job_arrange_id = sql_execute($sql);
					$output_count++;
					if ($verbose)
						$print_buffer[] = ("-> ID $job_arrange_id");
				}
			}
			else 
			{
				dprint("End of Arrangements");
				break; # while(true)
			}
			foreach ($print_buffer as $print_line)
				dprint($print_line);
			
			$input_count++;
			if ((0 < $stop_after) && ($stop_after < $input_count))
			{
				dprint("Breaking out \$stop_after=$stop_after, \$input_count=$input_count");
				break; # while(true)
			}
		} # while(true)

		$data_count = $input_count - 1;
		dprint("Imported $output_count arrangements from $data_count records");
		
		fclose($fp_csv);
		

		# ----------------------------------------------------------------------
		# Now copy arrangements from JOB table to JOB_ARRANGE, for Collect jobs only that 
		# have an instalment set up in the JOB record.
		# ----------------------------------------------------------------------
		
		$input_count = 0;
		$output_count = 0;
		
		$sql = "SELECT JOB_ID FROM JOB WHERE (JC_JOB=$sqlTrue) AND (JC_INSTAL_AMT > 0)";
		$job_recs = array();
		sql_execute($sql);
		while (($newArray = sql_fetch()) != false)
			$job_recs[] = $newArray[0];
		foreach ($job_recs as $job_id)
		{
			$sql = "SELECT JC_INSTAL_FREQ, JC_INSTAL_AMT, JC_INSTAL_DT_1, JC_PAYMENT_METHOD_ID, JC_TOTAL_AMT
					FROM JOB WHERE JOB_ID=$job_id";
			sql_execute($sql);
			while (($newArray = sql_fetch_assoc()) != false)
				$job_rec = $newArray;
			$ja_instal_freq = "'" . $job_rec['JC_INSTAL_FREQ'] . "'";
			$ja_instal_amt = 1.0 * $job_rec['JC_INSTAL_AMT'];
			$ja_instal_dt_1 = "'" . $job_rec['JC_INSTAL_DT_1'] . "'";
			$ja_payment_method_id = 1 * $job_rec['JC_PAYMENT_METHOD_ID'];
			$ja_total = 1.0 * $job_rec['JC_TOTAL_AMT'];
			
			$fields = "JOB_ID,  JA_DT, JA_INSTAL_FREQ,  JA_INSTAL_AMT,  JA_INSTAL_DT_1,  JA_PAYMENT_METHOD_ID,  JA_TOTAL,  IMPORTED";
			$values = "$job_id, NULL,  $ja_instal_freq, $ja_instal_amt, $ja_instal_dt_1, $ja_payment_method_id, $ja_total, $sqlTrue";

			$sql = "INSERT INTO JOB_ARRANGE ($fields) VALUES ($values)";
			if ($verbose)
			{
				$print_buffer[] = $sql;
				foreach ($print_buffer as $print_line)
					dprint($print_line);
				$print_buffer = array();
			}
			$job_arrange_id = sql_execute($sql);
			$output_count++;
			if ($verbose)
				$print_buffer[] = ("-> ID $job_arrange_id");
			
			$input_count++;
		}
		$data_count = $input_count - 1;
		dprint("Copied $output_count arrangements from $data_count JOB table records");
	}
	else
	{
		if (!$fp_csv)
			dlog("*=* Failed to fopen(\"$dirname/$csv\",'r')");
	}

} # import_arrange()

function import_arrange_2()
{
	# This should be called after import_arrange() but not before.
	
	# Import 31/12/16:
	#	import_2017_01_01_0948.log
	#	Found 33382 arrangements
	#	Found 8240 arrangements with a payment method
	#	Found 7039 jobs
	#	Updated 7039 jobs

	
	# 27/09/16:
	#		Found 32027 arrangements
	#		Found 7506 arrangements with a payment method
	#		Found 6361 jobs
	#		Updated 6361 jobs
	
	$enabled = false; #
	if (!$enabled)
	{
		dprint("This import function is disabled", true);
		return;
	}

	print "<h3>Arrange-2: Fixing arrangements</h3>";
	
	# Get the most recent payment method from JOB_ARRANGE and put it into JOB.JC_PAYMENT_METHOD_ID
	
	$sql = "SELECT COUNT(*) FROM JOB_ARRANGE";
	$count = sql_select_single($sql);
	dprint($sql);
	dprint("Found $count arrangements");
	
	$sql = "SELECT COUNT(*) FROM JOB_ARRANGE WHERE 0 < JA_PAYMENT_METHOD_ID";
	$count = sql_select_single($sql);
	dprint($sql);
	dprint("Found $count arrangements with a payment method");
	
	$sql = "SELECT JOB_ID, MAX(JOB_ARRANGE_ID) AS MAX_JOB_ARRANGE_ID FROM JOB_ARRANGE 
			WHERE 0 < JA_PAYMENT_METHOD_ID GROUP BY JOB_ID ORDER BY JOB_ID";
	dprint($sql);
	$job_recs = array();
	sql_execute($sql);
	while (($newArray = sql_fetch_assoc()) != false)
		$job_recs[$newArray['JOB_ID']] = $newArray['MAX_JOB_ARRANGE_ID'];
	dprint("Found " . count($job_recs) . " jobs");
	
	$count = 0;
	foreach ($job_recs as $job_id => $arrange_id)
	{
		$method_id = sql_select_single("SELECT JA_PAYMENT_METHOD_ID FROM JOB_ARRANGE WHERE JOB_ARRANGE_ID=$arrange_id");
		if (0 < $method_id)
		{
			$sql = "UPDATE JOB SET JC_PAYMENT_METHOD_ID=$method_id WHERE JOB_ID=$job_id";
			if ($count < 20)
				dprint($sql);
			sql_execute($sql); # no need to audit
			$count++;
		}
	}
	dprint("Updated $count jobs");
	
} # import_arrange_2()

function import_ledger()
{
	# Before calling this function:
	#	- Export TRACE/LEDGER.DBF as LEDGER.csv using DBF Manager with default settings.
	#	- Export COLLECT/LEDGER.DBF as LEDGER.csv using DBF Manager with default settings.
	# Required:
	#	t/LEDGER.csv
	#	c/LEDGER.csv
	
	# Import 31/12/16:
	#	Trace Invoices:
	#	import_2017_01_01_0951.log
	#	2017-01-01 09:51:18/1 *=* (Sys=t) Unrecognised CLID "420" - skipping record (record 3010)
	#	col_nums: Array
	#	(
	#	    [DOCNO] => 0
	#	    [DOCTYPE] => 1
	#	    [DATE] => 2
	#	    [CLID] => 3
	#	    [NETCOST] => 4
	#	    [VATCOST] => 5
	#	    [RECD] => 6
	#	    [EOM] => 7
	#	    [S_INVS] => 8
	#	    [PSTART] => 9
	#	    [PEND] => 10
	#	    [COMPLETE] => 11
	#	)
	#	record: Array
	#	(
	#	    [0] => 11833
	#	    [1] => I
	#	    [2] => 20/09/1994
	#	    [3] => 420
	#	    [4] => 125
	#	    [5] => 21.88
	#	    [6] => 146.88
	#	    [7] => True
	#	    [8] => no
	#	    [9] => 
	#	    [10] => 
	#	    [11] => Y
	#	)
	#	2017-01-01 09:51:52/1 *=* (Sys=t) Non-numeric or illegal DOCNO "0" changed to -101 (record 10400)
	#	2017-01-01 09:52:16/1 *=* (Sys=t) Non-numeric or illegal DOCNO "0" changed to -102 (record 16967)
	#	2017-01-01 09:52:21/1 *=* (Sys=t) Non-numeric or illegal DOCNO "0" changed to -103 (record 18270)
	#	2017-01-01 09:52:22/1 *=* (Sys=t) Non-numeric or illegal DOCNO "0" changed to -104 (record 18527)
	#	2017-01-01 09:52:23/1 *=* (Sys=t) Non-numeric or illegal DOCNO "0" changed to -105 (record 18926)
	#	2017-01-01 09:52:27/1 *=* (Sys=t) Non-numeric or illegal DOCNO "0" changed to -106 (record 20039)
	#	2017-01-01 09:52:55/1 *=* (Sys=t) Non-numeric or illegal DOCNO "0" changed to -107 (record 26913)
	#	2017-01-01 09:53:51/1 *=* (Sys=t) Non-numeric or illegal DOCNO "0" changed to -108 (record 38111)
	#	2017-01-01 09:54:38/1 *=* (Sys=t) Non-numeric or illegal DOCNO "0" changed to -109 (record 47421)
	#	2017-01-01 09:54:47/1 *=* (Sys=t) Non-numeric or illegal DOCNO "0" changed to -110 (record 49090)
	#	2017-01-01 09:54:48/1 *=* (Sys=t) Non-numeric or illegal DOCNO "0" changed to -111 (record 49291)
	#	2017-01-01 09:55:04/1 *=* (Sys=t) Non-numeric or illegal DOCNO "0" changed to -112 (record 52631)
	#	2017-01-01 09:55:20/1 *=* (Sys=t) Non-numeric or illegal DOCNO "0" changed to -113 (record 55704)
	#	2017-01-01 09:55:29/1 *=* (Sys=t) Non-numeric or illegal DOCNO "0" changed to -114 (record 57342)
	#	2017-01-01 09:55:53/1 *=* (Sys=t) Non-numeric or illegal DOCNO "0" changed to -115 (record 62090)
	#	2017-01-01 09:55:58/1 *=* (Sys=t) Non-numeric or illegal DOCNO "0" changed to -116 (record 63146)
	#	2017-01-01 09:56:02/1 *=* (Sys=t) Non-numeric or illegal DOCNO "0" changed to -117 (record 63966)
	#	2017-01-01 09:56:03/1 *=* (Sys=t) Non-numeric or illegal DOCNO "0" changed to -118 (record 64245)
	#	2017-01-01 09:56:03/1 *=* (Sys=t) Non-numeric or illegal DOCNO "0" changed to -119 (record 64250)
	#	2017-01-01 09:56:17/1 *=* (Sys=t) Non-numeric or illegal DOCNO "0" changed to -120 (record 66867)
	#	2017-01-01 09:56:17/1 *=* (Sys=t) Non-numeric or illegal DOCNO "0" changed to -121 (record 66981)
	#	2017-01-01 09:56:21/1 *=* (Sys=t) Non-numeric or illegal DOCNO "0" changed to -122 (record 67599)
	#	2017-01-01 09:56:23/1 *=* (Sys=t) Non-numeric or illegal DOCNO "0" changed to -123 (record 68035)
	#	2017-01-01 09:56:27/1 *=* (Sys=t) Non-numeric or illegal DOCNO "0" changed to -124 (record 68928)
	#	2017-01-01 09:56:32/1 *=* (Sys=t) Non-numeric or illegal DOCNO "0" changed to -125 (record 69861)
	#	2017-01-01 09:56:50/1 *=* (Sys=t) Non-numeric or illegal DOCNO "0" changed to -126 (record 73343)
	#	2017-01-01 09:57:12/1 *=* (Sys=t) Non-numeric or illegal DOCNO "0" changed to -127 (record 77803)
	#	2017-01-01 09:57:29/1 *=* (Sys=t) Non-numeric or illegal DOCNO "0" changed to -128 (record 81102)
	#	2017-01-01 09:57:37/1 *=* (Sys=t) Non-numeric or illegal DOCNO "0" changed to -129 (record 82785)
	#	2017-01-01 09:57:37/1 *=* (Sys=t) Non-numeric or illegal DOCNO "0" changed to -130 (record 82796)
	#	2017-01-01 09:57:43/1 *=* (Sys=t) Non-numeric or illegal DOCNO "0" changed to -131 (record 83871)
	#	2017-01-01 09:57:43/1 *=* (Sys=t) Non-numeric or illegal DOCNO "0" changed to -132 (record 83994)
	#	2017-01-01 09:57:59/1 *=* (Sys=t) Non-numeric or illegal DOCNO "0" changed to -133 (record 86978)
	#	2017-01-01 09:58:01/1 *=* (Sys=t) Non-numeric or illegal DOCNO "0" changed to -134 (record 87382)
	#	2017-01-01 09:58:01/1 *=* (Sys=t) Non-numeric or illegal DOCNO "0" changed to -135 (record 87390)
	#	2017-01-01 09:58:11/1 *=* (Sys=t) Non-numeric or illegal DOCNO "0" changed to -136 (record 89478)
	#	2017-01-01 09:58:11/1 *=* (Sys=t) Non-numeric or illegal DOCNO "0" changed to -137 (record 89500)
	#	2017-01-01 09:58:13/1 *=* (Sys=t) Non-numeric or illegal DOCNO "0" changed to -138 (record 89834)
	#	2017-01-01 09:58:13/1 *=* (Sys=t) Non-numeric or illegal DOCNO "0" changed to -139 (record 89897)
	#	2017-01-01 09:58:17/1 *=* (Sys=t) Non-numeric or illegal DOCNO "0" changed to -140 (record 90778)
	#	2017-01-01 09:58:21/1 *=* (Sys=t) Non-numeric or illegal DOCNO "0" changed to -141 (record 91704)
	#	2017-01-01 09:58:23/1 *=* (Sys=t) Non-numeric or illegal DOCNO "0" changed to -142 (record 92421)
	#	2017-01-01 09:58:24/1 *=* (Sys=t) Non-numeric or illegal DOCNO "0" changed to -143 (record 92515)
	#	2017-01-01 09:58:24/1 *=* (Sys=t) Non-numeric or illegal DOCNO "0" changed to -144 (record 92642)
	#	2017-01-01 09:58:25/1 *=* (Sys=t) Non-numeric or illegal DOCNO "0" changed to -145 (record 92794)
	#	2017-01-01 09:58:27/1 *=* (Sys=t) Non-numeric or illegal DOCNO "0" changed to -146 (record 93396)
	#	2017-01-01 09:58:34/1 *=* (Sys=t) Non-numeric or illegal DOCNO "0" changed to -147 (record 95348)
	#	2017-01-01 09:58:39/1 *=* (Sys=t) Non-numeric or illegal DOCNO "0" changed to -148 (record 96396)
	#	2017-01-01 09:58:41/1 *=* (Sys=t) Non-numeric or illegal DOCNO "0" changed to -149 (record 96827)
	#	2017-01-01 09:58:43/1 *=* (Sys=t) Non-numeric or illegal DOCNO "0" changed to -150 (record 97231)
	#	2017-01-01 09:58:44/1 *=* (Sys=t) Non-numeric or illegal DOCNO "0" changed to -151 (record 97343)
	#	2017-01-01 09:58:47/1 *=* (Sys=t) Non-numeric or illegal DOCNO "0" changed to -152 (record 97952)
	#	2017-01-01 09:58:53/1 *=* (Sys=t) Non-numeric or illegal DOCNO "0" changed to -153 (record 99073)
	#	2017-01-01 09:59:00/1 *=* (Sys=t) Non-numeric or illegal DOCNO "0" changed to -154 (record 100460)
	#	2017-01-01 09:59:00/1 *=* (Sys=t) Non-numeric or illegal DOCNO "0" changed to -155 (record 100478)
	#	2017-01-01 09:59:01/1 *=* (Sys=t) Non-numeric or illegal DOCNO "0" changed to -156 (record 100737)
	#	2017-01-01 09:59:01/1 *=* (Sys=t) Non-numeric or illegal DOCNO "0" changed to -157 (record 100791)
	#	2017-01-01 09:59:20/1 *=* (Sys=t) Non-numeric or illegal DOCNO "0" changed to -158 (record 104603)
	#	2017-01-01 09:59:20/1 *=* (Sys=t) Non-numeric or illegal DOCNO "0" changed to -159 (record 104608)
	#	2017-01-01 09:59:23/1 *=* (Sys=t) Non-numeric or illegal DOCNO "0" changed to -160 (record 105199)
	#	2017-01-01 09:59:23/1 *=* (Sys=t) Non-numeric or illegal DOCNO "0" changed to -161 (record 105221)
	#	2017-01-01 09:59:35/1 *=* (Sys=t) Non-numeric or illegal DOCNO "0" changed to -162 (record 107527)
	#	2017-01-01 09:59:37/1 *=* (Sys=t) Non-numeric or illegal DOCNO "0" changed to -163 (record 108010)
	#	2017-01-01 09:59:37/1 *=* (Sys=t) Non-numeric or illegal DOCNO "0" changed to -164 (record 108035)
	#	2017-01-01 09:59:43/1 *=* (Sys=t) Non-numeric or illegal DOCNO "0" changed to -165 (record 109162)
	#	2017-01-01 09:59:44/1 *=* (Sys=t) Non-numeric or illegal DOCNO "0" changed to -166 (record 109315)
	#	2017-01-01 09:59:44/1 *=* (Sys=t) Non-numeric or illegal DOCNO "0" changed to -167 (record 109317)
	#	2017-01-01 09:59:45/1 *=* (Sys=t) Non-numeric or illegal DOCNO "0" changed to -168 (record 109554)
	#	2017-01-01 09:59:46/1 *=* (Sys=t) Non-numeric or illegal DOCNO "0" changed to -169 (record 109844)
	#	2017-01-01 09:59:48/1 *=* (Sys=t) Non-numeric or illegal DOCNO "0" changed to -170 (record 110197)
	#	2017-01-01 09:59:51/1 *=* (Sys=t) Non-numeric or illegal DOCNO "0" changed to -171 (record 110715)
	#	2017-01-01 09:59:53/1 *=* (Sys=t) Non-numeric or illegal DOCNO "0" changed to -172 (record 111151)
	#	2017-01-01 09:59:54/1 *=* (Sys=t) Non-numeric or illegal DOCNO "0" changed to -173 (record 111252)
	#	2017-01-01 09:59:58/1 *=* (Sys=t) Non-numeric or illegal DOCNO "0" changed to -174 (record 112117)
	#	2017-01-01 10:00:01/1 *=* (Sys=t) Non-numeric or illegal DOCNO "0" changed to -175 (record 113159)
	#	2017-01-01 10:00:03/1 *=* (Sys=t) Non-numeric or illegal DOCNO "0" changed to -176 (record 113683)
	#	2017-01-01 10:00:04/1 *=* (Sys=t) Non-numeric or illegal DOCNO "0" changed to -177 (record 114087)
	#	2017-01-01 10:00:07/1 *=* (Sys=t) Non-numeric or illegal DOCNO "0" changed to -178 (record 114987)
	#	2017-01-01 10:00:08/1 *=* (Sys=t) Non-numeric or illegal DOCNO "0" changed to -179 (record 115355)
	#	2017-01-01 10:00:09/1 *=* (Sys=t) Non-numeric or illegal DOCNO "0" changed to -180 (record 115697)
	#	2017-01-01 10:00:10/1 *=* (Sys=t) Non-numeric or illegal DOCNO "0" changed to -181 (record 116059)
	#	2017-01-01 10:00:10/1 *=* (Sys=t) Non-numeric or illegal DOCNO "0" changed to -182 (record 116134)
	#	2017-01-01 10:00:12/1 *=* (Sys=t) Non-numeric or illegal DOCNO "0" changed to -183 (record 116750)
	#	2017-01-01 10:00:13/1 *=* (Sys=t) Non-numeric or illegal DOCNO "0" changed to -184 (record 116954)
	#	2017-01-01 10:00:13/1 *=* (Sys=t) Non-numeric or illegal DOCNO "0" changed to -185 (record 117018)
	#	2017-01-01 10:00:17/1 *=* (Sys=t) Non-numeric or illegal DOCNO "0" changed to -186 (record 118390)
	#	2017-01-01 10:00:20/1 *=* (Sys=t) Non-numeric or illegal DOCNO "0" changed to -187 (record 119032)
	#	2017-01-01 10:00:23/1 *=* (Sys=t) Non-numeric or illegal DOCNO "0" changed to -188 (record 119727)
	#	2017-01-01 10:00:28/1 *=* (Sys=t) Non-numeric or illegal DOCNO "0" changed to -189 (record 121208)
	#	2017-01-01 10:00:29/1 *=* (Sys=t) Non-numeric or illegal DOCNO "0" changed to -190 (record 121484)
	#	2017-01-01 10:00:33/1 *=* (Sys=t) Non-numeric or illegal DOCNO "0" changed to -191 (record 122498)
	#	2017-01-01 10:00:34/1 *=* (Sys=t) Non-numeric or illegal DOCNO "0" changed to -192 (record 122754)
	#	2017-01-01 10:00:36/1 *=* (Sys=t) Non-numeric or illegal DOCNO "0" changed to -193 (record 123181)
	#	2017-01-01 10:00:38/1 *=* (Sys=t) Non-numeric or illegal DOCNO "0" changed to -194 (record 123493)
	#	2017-01-01 10:00:40/1 *=* (Sys=t) Non-numeric or illegal DOCNO "0" changed to -195 (record 123935)
	#	2017-01-01 10:00:41/1 *=* (Sys=t) Non-numeric or illegal DOCNO "0" changed to -196 (record 124108)
	#	2017-01-01 10:00:45/1 *=* (Sys=t) Non-numeric or illegal DOCNO "0" changed to -197 (record 124879)
	#	2017-01-01 10:00:46/1 *=* (Sys=t) Non-numeric or illegal DOCNO "0" changed to -198 (record 124941)
	#	2017-01-01 10:00:49/1 *=* (Sys=t) Non-numeric or illegal DOCNO "0" changed to -199 (record 125731)
	#	2017-01-01 10:00:57/1 *=* (Sys=t) Non-numeric or illegal DOCNO "0" changed to -200 (record 127235)
	#	2017-01-01 10:01:01/1 *=* (Sys=t) Non-numeric or illegal DOCNO "0" changed to -201 (record 127945)
	#	2017-01-01 10:01:02/1 *=* (Sys=t) Non-numeric or illegal DOCNO "0" changed to -202 (record 128262)
	#	2017-01-01 10:01:03/1 *=* (Sys=t) Non-numeric or illegal DOCNO "0" changed to -203 (record 128529)
	#	2017-01-01 10:01:04/1 *=* (Sys=t) Non-numeric or illegal DOCNO "0" changed to -204 (record 128774)
	#	2017-01-01 10:01:04/1 *=* (Sys=t) Non-numeric or illegal DOCNO "0" changed to -205 (record 128777)
	#	2017-01-01 10:01:04/1 *=* (Sys=t) Non-numeric or illegal DOCNO "0" changed to -206 (record 128892)
	#	2017-01-01 10:01:05/1 *=* (Sys=t) Non-numeric or illegal DOCNO "0" changed to -207 (record 129130)
	#	2017-01-01 10:01:06/1 *=* (Sys=t) Non-numeric or illegal DOCNO "0" changed to -208 (record 129359)
	#	2017-01-01 10:01:06/1 *=* (Sys=t) Non-numeric or illegal DOCNO "0" changed to -209 (record 129362)
	#	2017-01-01 10:01:10/1 *=* (Sys=t) Non-numeric or illegal DOCNO "0" changed to -210 (record 130524)
	#	2017-01-01 10:01:11/1 *=* (Sys=t) Non-numeric or illegal DOCNO "0" changed to -211 (record 130650)
	#	2017-01-01 10:01:11/1 *=* (Sys=t) Non-numeric or illegal DOCNO "0" changed to -212 (record 130686)
	#	2017-01-01 10:01:11/1 *=* (Sys=t) Non-numeric or illegal DOCNO "0" changed to -213 (record 130745)
	#	2017-01-01 10:01:12/1 *=* (Sys=t) Non-numeric or illegal DOCNO "0" changed to -214 (record 130975)
	#	2017-01-01 10:01:12/1 *=* (Sys=t) Non-numeric or illegal DOCNO "0" changed to -215 (record 131008)
	#	2017-01-01 10:01:12/1 *=* (Sys=t) Non-numeric or illegal DOCNO "0" changed to -216 (record 131016)
	#	2017-01-01 10:01:13/1 *=* (Sys=t) Non-numeric or illegal DOCNO "0" changed to -217 (record 131245)
	#	2017-01-01 10:01:13/1 *=* (Sys=t) Non-numeric or illegal DOCNO "0" changed to -218 (record 131317)
	#	2017-01-01 10:01:14/1 *=* (Sys=t) Non-numeric or illegal DOCNO "0" changed to -219 (record 131599)
	#	2017-01-01 10:01:16/1 *=* (Sys=t) Non-numeric or illegal DOCNO "0" changed to -220 (record 132036)
	#	2017-01-01 10:01:16/1 *=* (Sys=t) Non-numeric or illegal DOCNO "0" changed to -221 (record 132094)
	#	2017-01-01 10:01:16/1 *=* (Sys=t) Non-numeric or illegal DOCNO "0" changed to -222 (record 132129)
	#	2017-01-01 10:01:18/1 *=* (Sys=t) Non-numeric or illegal DOCNO "0" changed to -223 (record 132638)
	#	2017-01-01 10:01:18/1 *=* (Sys=t) Non-numeric or illegal DOCNO "0" changed to -224 (record 132693)
	#	2017-01-01 10:01:19/1 *=* (Sys=t) Non-numeric or illegal DOCNO "0" changed to -225 (record 132954)
	#	2017-01-01 10:01:23/1 *=* (Sys=t) Non-numeric or illegal DOCNO "0" changed to -226 (record 133769)
	#	2017-01-01 10:01:24/1 *=* (Sys=t) Non-numeric or illegal DOCNO "0" changed to -227 (record 134115)
	#	2017-01-01 10:01:24/1 *=* (Sys=t) Non-numeric or illegal DOCNO "0" changed to -228 (record 134131)
	#	2017-01-01 10:01:24/1 *=* (Sys=t) Non-numeric or illegal DOCNO "0" changed to -229 (record 134143)
	#	2017-01-01 10:01:26/1 *=* (Sys=t) Non-numeric or illegal DOCNO "0" changed to -230 (record 134542)
	#	2017-01-01 10:01:31/1 *=* (Sys=t) Non-numeric or illegal DOCNO "0" changed to -231 (record 136008)
	#	2017-01-01 10:01:32/1 *=* (Sys=t) Non-numeric or illegal DOCNO "0" changed to -232 (record 136133)
	#	2017-01-01 10:01:32/1 *=* (Sys=t) Non-numeric or illegal DOCNO "0" changed to -233 (record 136329)
	#	2017-01-01 10:01:32/1 *=* (Sys=t) Non-numeric or illegal DOCNO "0" changed to -234 (record 136334)
	#	2017-01-01 10:01:35/1 *=* (Sys=t) Non-numeric or illegal DOCNO "0" changed to -235 (record 136992)
	#	2017-01-01 10:01:36/1 *=* (Sys=t) Non-numeric or illegal DOCNO "0" changed to -236 (record 137390)
	#	2017-01-01 10:01:37/1 *=* (Sys=t) Non-numeric or illegal DOCNO "0" changed to -237 (record 137686)
	#	2017-01-01 10:01:37/1 *=* (Sys=t) Non-numeric or illegal DOCNO "0" changed to -238 (record 137694)
	#	2017-01-01 10:01:37/1 *=* (Sys=t) Non-numeric or illegal DOCNO "0" changed to -239 (record 137696)
	#	2017-01-01 10:01:38/1 *=* (Sys=t) Non-numeric or illegal DOCNO "0" changed to -240 (record 137887)
	#	2017-01-01 10:01:38/1 *=* (Sys=t) Non-numeric or illegal DOCNO "0" changed to -241 (record 137908)
	#	2017-01-01 10:01:38/1 *=* (Sys=t) Non-numeric or illegal DOCNO "0" changed to -242 (record 137991)
	#	2017-01-01 10:01:38/1 *=* (Sys=t) Non-numeric or illegal DOCNO "0" changed to -243 (record 138029)
	#	2017-01-01 10:01:38/1 *=* (Sys=t) Non-numeric or illegal DOCNO "0" changed to -244 (record 138035)
	#	2017-01-01 10:01:41/1 *=* (Sys=t) Non-numeric or illegal DOCNO "0" changed to -245 (record 138744)
	#	2017-01-01 10:01:41/1 *=* (Sys=t) Non-numeric or illegal DOCNO "0" changed to -246 (record 138747)
	#	2017-01-01 10:01:44/1 *=* (Sys=t) Non-numeric or illegal DOCNO "0" changed to -247 (record 139417)
//	2017-01-01 10:01:44/1 *=* (Sys=t) Non-numeric or illegal DOCNO "0" changed to -248 (record 139492)
//	2017-01-01 10:01:44/1 *=* (Sys=t) Non-numeric or illegal DOCNO "0" changed to -249 (record 139642)
//	2017-01-01 10:01:44/1 *=* (Sys=t) Non-numeric or illegal DOCNO "0" changed to -250 (record 139644)
//	2017-01-01 10:01:44/1 *=* (Sys=t) Non-numeric or illegal DOCNO "0" changed to -251 (record 139648)
//	2017-01-01 10:01:46/1 *=* (Sys=t) Non-numeric or illegal DOCNO "0" changed to -252 (record 140012)
//	2017-01-01 10:01:46/1 *=* (Sys=t) Non-numeric or illegal DOCNO "0" changed to -253 (record 140042)
//	2017-01-01 10:01:46/1 *=* (Sys=t) Non-numeric or illegal DOCNO "0" changed to -254 (record 140130)
//	2017-01-01 10:01:48/1 *=* (Sys=t) Non-numeric or illegal DOCNO "0" changed to -255 (record 140599)
//	2017-01-01 10:01:48/1 *=* (Sys=t) Non-numeric or illegal DOCNO "0" changed to -256 (record 140666)
//	2017-01-01 10:01:48/1 *=* (Sys=t) Non-numeric or illegal DOCNO "0" changed to -257 (record 140734)
//	2017-01-01 10:01:49/1 *=* (Sys=t) Non-numeric or illegal DOCNO "0" changed to -258 (record 140847)
//	2017-01-01 10:01:49/1 *=* (Sys=t) Non-numeric or illegal DOCNO "0" changed to -259 (record 140905)
//	2017-01-01 10:01:49/1 *=* (Sys=t) Non-numeric or illegal DOCNO "0" changed to -260 (record 140959)
//	2017-01-01 10:01:49/1 *=* (Sys=t) Non-numeric or illegal DOCNO "0" changed to -261 (record 140960)
//	2017-01-01 10:01:49/1 *=* (Sys=t) Non-numeric or illegal DOCNO "0" changed to -262 (record 140961)
//	2017-01-01 10:01:49/1 *=* (Sys=t) Non-numeric or illegal DOCNO "0" changed to -263 (record 140964)
//	2017-01-01 10:01:52/1 *=* (Sys=t) Non-numeric or illegal DOCNO "0" changed to -264 (record 141612)
//	2017-01-01 10:01:54/1 *=* (Sys=t) Non-numeric or illegal DOCNO "0" changed to -265 (record 142163)
//	2017-01-01 10:01:54/1 *=* (Sys=t) Non-numeric or illegal DOCNO "0" changed to -266 (record 142294)
//	2017-01-01 10:01:55/1 *=* (Sys=t) Non-numeric or illegal DOCNO "0" changed to -267 (record 142444)
//	2017-01-01 10:01:55/1 *=* (Sys=t) Non-numeric or illegal DOCNO "0" changed to -268 (record 142448)
//	2017-01-01 10:01:57/1 *=* (Sys=t) Non-numeric or illegal DOCNO "0" changed to -269 (record 143092)
//	2017-01-01 10:01:58/1 *=* (Sys=t) Non-numeric or illegal DOCNO "0" changed to -270 (record 143223)
//	2017-01-01 10:01:59/1 *=* (Sys=t) Non-numeric or illegal DOCNO "0" changed to -271 (record 143514)
//	2017-01-01 10:01:59/1 *=* (Sys=t) Non-numeric or illegal DOCNO "0" changed to -272 (record 143515)
//	2017-01-01 10:01:59/1 *=* (Sys=t) Non-numeric or illegal DOCNO "0" changed to -273 (record 143732)
//	2017-01-01 10:02:03/1 *=* (Sys=t) Non-numeric or illegal DOCNO "0" changed to -274 (record 144660)
//	2017-01-01 10:02:03/1 *=* (Sys=t) Non-numeric or illegal DOCNO "0" changed to -275 (record 144662)
//	2017-01-01 10:02:03/1 *=* (Sys=t) Non-numeric or illegal DOCNO "0" changed to -276 (record 144840)
//	2017-01-01 10:02:04/1 *=* (Sys=t) Non-numeric or illegal DOCNO "0" changed to -277 (record 145134)
//	2017-01-01 10:02:04/1 *=* (Sys=t) Non-numeric or illegal DOCNO "0" changed to -278 (record 145138)
//	2017-01-01 10:02:06/1 *=* (Sys=t) Non-numeric or illegal DOCNO "0" changed to -279 (record 145574)
//	2017-01-01 10:02:09/1 *=* (Sys=t) Non-numeric or illegal DOCNO "0" changed to -280 (record 146406)
//	2017-01-01 10:02:10/1 *=* (Sys=t) Non-numeric or illegal DOCNO "0" changed to -281 (record 146574)
//	2017-01-01 10:02:11/1 *=* (Sys=t) Non-numeric or illegal DOCNO "0" changed to -282 (record 146853)
//	2017-01-01 10:02:11/1 *=* (Sys=t) Non-numeric or illegal DOCNO "0" changed to -283 (record 146999)
//	2017-01-01 10:02:11/1 *=* (Sys=t) Non-numeric or illegal DOCNO "0" changed to -284 (record 147025)
//	2017-01-01 10:02:12/1 *=* (Sys=t) Non-numeric or illegal DOCNO "0" changed to -285 (record 147228)
//	2017-01-01 10:02:14/1 *=* (Sys=t) Non-numeric or illegal DOCNO "0" changed to -286 (record 147603)
//	2017-01-01 10:02:15/1 *=* (Sys=t) Non-numeric or illegal DOCNO "0" changed to -287 (record 148131)
//	2017-01-01 10:02:16/1 *=* (Sys=t) Non-numeric or illegal DOCNO "0" changed to -288 (record 148219)
//	2017-01-01 10:02:17/1 *=* (Sys=t) Non-numeric or illegal DOCNO "0" changed to -289 (record 148487)
//	2017-01-01 10:02:17/1 *=* (Sys=t) Non-numeric or illegal DOCNO "0" changed to -290 (record 148496)
//	2017-01-01 10:02:17/1 *=* (Sys=t) Non-numeric or illegal DOCNO "0" changed to -291 (record 148498)
//	2017-01-01 10:02:18/1 *=* (Sys=t) Non-numeric or illegal DOCNO "0" changed to -292 (record 148702)
//	2017-01-01 10:02:18/1 *=* (Sys=t) Non-numeric or illegal DOCNO "0" changed to -293 (record 148858)
//	2017-01-01 10:02:23/1 *=* (Sys=t) Non-numeric or illegal DOCNO "0" changed to -294 (record 149916)
//	2017-01-01 10:02:23/1 *=* (Sys=t) Non-numeric or illegal DOCNO "0" changed to -295 (record 149960)
//	2017-01-01 10:02:23/1 *=* (Sys=t) Non-numeric or illegal DOCNO "0" changed to -296 (record 150043)
//	2017-01-01 10:02:26/1 *=* (Sys=t) Non-numeric or illegal DOCNO "0" changed to -297 (record 150768)
//	2017-01-01 10:02:26/1 *=* (Sys=t) Non-numeric or illegal DOCNO "0" changed to -298 (record 150783)
//	2017-01-01 10:02:29/1 *=* (Sys=t) Non-numeric or illegal DOCNO "0" changed to -299 (record 151466)
//	2017-01-01 10:02:30/1 *=* (Sys=t) Non-numeric or illegal DOCNO "0" changed to -300 (record 151626)
//	2017-01-01 10:02:31/1 *=* (Sys=t) Non-numeric or illegal DOCNO "0" changed to -301 (record 151863)
//	2017-01-01 10:02:31/1 *=* (Sys=t) Non-numeric or illegal DOCNO "0" changed to -302 (record 151893)
//	2017-01-01 10:02:33/1 *=* (Sys=t) Non-numeric or illegal DOCNO "0" changed to -303 (record 152391)
//	2017-01-01 10:02:33/1 *=* (Sys=t) Non-numeric or illegal DOCNO "0" changed to -304 (record 152490)
//	2017-01-01 10:02:36/1 *=* (Sys=t) Non-numeric or illegal DOCNO "0" changed to -305 (record 153162)
//	2017-01-01 10:02:37/1 *=* (Sys=t) Non-numeric or illegal DOCNO "0" changed to -306 (record 153372)
//	2017-01-01 10:02:37/1 *=* (Sys=t) Non-numeric or illegal DOCNO "0" changed to -307 (record 153546)
//	2017-01-01 10:02:39/1 *=* (Sys=t) Non-numeric or illegal DOCNO "0" changed to -308 (record 154016)
//	2017-01-01 10:02:40/1 *=* (Sys=t) Non-numeric or illegal DOCNO "0" changed to -309 (record 154157)
//	2017-01-01 10:02:40/1 *=* (Sys=t) Non-numeric or illegal DOCNO "0" changed to -310 (record 154311)
//	2017-01-01 10:02:41/1 *=* (Sys=t) Non-numeric or illegal DOCNO "0" changed to -311 (record 154526)
//	2017-01-01 10:02:41/1 *=* (Sys=t) Non-numeric or illegal DOCNO "0" changed to -312 (record 154578)
//	2017-01-01 10:02:42/1 *=* (Sys=t) Non-numeric or illegal DOCNO "0" changed to -313 (record 154883)
//	2017-01-01 10:02:42/1 *=* (Sys=t) Non-numeric or illegal DOCNO "0" changed to -314 (record 154887)
//	2017-01-01 10:02:46/1 *=* (Sys=t) Non-numeric or illegal DOCNO "0" changed to -315 (record 155691)
//	2017-01-01 10:02:47/1 *=* (Sys=t) Non-numeric or illegal DOCNO "0" changed to -316 (record 156161)
//	2017-01-01 10:02:48/1 *=* (Sys=t) Non-numeric or illegal DOCNO "0" changed to -317 (record 156230)
//	2017-01-01 10:02:49/1 *=* (Sys=t) Non-numeric or illegal DOCNO "0" changed to -318 (record 156539)
//	2017-01-01 10:02:50/1 *=* (Sys=t) Non-numeric or illegal DOCNO "0" changed to -319 (record 156753)
//	2017-01-01 10:02:50/1 *=* (Sys=t) Non-numeric or illegal DOCNO "0" changed to -320 (record 156754)
//	2017-01-01 10:02:51/1 *=* (Sys=t) Non-numeric or illegal DOCNO "0" changed to -321 (record 156794)
//	2017-01-01 10:02:55/1 *=* (Sys=t) Non-numeric or illegal DOCNO "0" changed to -322 (record 157703)
//	2017-01-01 10:02:55/1 *=* (Sys=t) Non-numeric or illegal DOCNO "0" changed to -323 (record 157729)
//	2017-01-01 10:02:56/1 *=* (Sys=t) Non-numeric or illegal DOCNO "0" changed to -324 (record 157776)
//	2017-01-01 10:02:56/1 *=* (Sys=t) Non-numeric or illegal DOCNO "0" changed to -325 (record 157813)
//	2017-01-01 10:02:57/1 *=* (Sys=t) Non-numeric or illegal DOCNO "0" changed to -326 (record 158189)
//	2017-01-01 10:02:58/1 *=* (Sys=t) Non-numeric or illegal DOCNO "0" changed to -327 (record 158351)
//	2017-01-01 10:03:01/1 *=* (Sys=t) Non-numeric or illegal DOCNO "0" changed to -328 (record 158803)
//	2017-01-01 10:03:02/1 *=* (Sys=t) Non-numeric or illegal DOCNO "0" changed to -329 (record 159122)
//	2017-01-01 10:03:02/1 *=* (Sys=t) Non-numeric or illegal DOCNO "0" changed to -330 (record 159129)
//	2017-01-01 10:03:03/1 *=* (Sys=t) Non-numeric or illegal DOCNO "0" changed to -331 (record 159436)
//	2017-01-01 10:03:03/1 *=* (Sys=t) Non-numeric or illegal DOCNO "0" changed to -332 (record 159451)
//	2017-01-01 10:03:03/1 *=* (Sys=t) Non-numeric or illegal DOCNO "0" changed to -333 (record 159452)
//	2017-01-01 10:03:05/1 *=* (Sys=t) Non-numeric or illegal DOCNO "0" changed to -334 (record 159821)
//	2017-01-01 10:03:05/1 *=* (Sys=t) Non-numeric or illegal DOCNO "0" changed to -335 (record 159822)
//	2017-01-01 10:03:06/1 *=* (Sys=t) Non-numeric or illegal DOCNO "0" changed to -336 (record 159929)
//	2017-01-01 10:03:07/1 *=* (Sys=t) Non-numeric or illegal DOCNO "0" changed to -337 (record 160261)
//	2017-01-01 10:03:08/1 *=* (Sys=t) Non-numeric or illegal DOCNO "0" changed to -338 (record 160411)
//	2017-01-01 10:03:12/1 *=* (Sys=t) Non-numeric or illegal DOCNO "0" changed to -339 (record 161217)
//	2017-01-01 10:03:12/1 *=* (Sys=t) Non-numeric or illegal DOCNO "0" changed to -340 (record 161291)
//	2017-01-01 10:03:14/1 *=* (Sys=t) Non-numeric or illegal DOCNO "0" changed to -341 (record 161669)
//	2017-01-01 10:03:14/1 *=* (Sys=t) Non-numeric or illegal DOCNO "0" changed to -342 (record 161672)
//	2017-01-01 10:03:15/1 *=* (Sys=t) Non-numeric or illegal DOCNO "0" changed to -343 (record 161762)
//	2017-01-01 10:03:15/1 *=* (Sys=t) Non-numeric or illegal DOCNO "0" changed to -344 (record 161781)
//	2017-01-01 10:03:16/1 *=* (Sys=t) Non-numeric or illegal DOCNO "0" changed to -345 (record 162087)
//	2017-01-01 10:03:28/1 *=* (Sys=t) Non-numeric or illegal DOCNO "0" changed to -346 (record 164444)
//	2017-01-01 10:03:28/1 *=* (Sys=t) Non-numeric or illegal DOCNO "0" changed to -347 (record 164532)
//	2017-01-01 10:03:29/1 *=* (Sys=t) Non-numeric or illegal DOCNO "0" changed to -348 (record 164684)
//	2017-01-01 10:03:29/1 *=* (Sys=t) Non-numeric or illegal DOCNO "0" changed to -349 (record 164838)
//	2017-01-01 10:03:30/1 *=* (Sys=t) Non-numeric or illegal DOCNO "0" changed to -350 (record 165032)
//	2017-01-01 10:03:31/1 *=* (Sys=t) Non-numeric or illegal DOCNO "0" changed to -351 (record 165179)
//	2017-01-01 10:03:31/1 *=* (Sys=t) Non-numeric or illegal DOCNO "0" changed to -352 (record 165293)
//	2017-01-01 10:03:31/1 *=* (Sys=t) Non-numeric or illegal DOCNO "0" changed to -353 (record 165366)
//	2017-01-01 10:03:32/1 *=* (Sys=t) Non-numeric or illegal DOCNO "0" changed to -354 (record 165559)
//	2017-01-01 10:03:32/1 *=* (Sys=t) Non-numeric or illegal DOCNO "0" changed to -355 (record 165561)
//	2017-01-01 10:03:33/1 *=* (Sys=t) Non-numeric or illegal DOCNO "0" changed to -356 (record 165865)
//	2017-01-01 10:03:34/1 *=* (Sys=t) Non-numeric or illegal DOCNO "0" changed to -357 (record 165960)
//	2017-01-01 10:03:37/1 *=* (Sys=t) Non-numeric or illegal DOCNO "0" changed to -358 (record 167013)
//	2017-01-01 10:03:38/1 *=* (Sys=t) Non-numeric or illegal DOCNO "0" changed to -359 (record 167041)
//	2017-01-01 10:03:39/1 *=* (Sys=t) Non-numeric or illegal DOCNO "0" changed to -360 (record 167426)
//	2017-01-01 10:03:40/1 *=* (Sys=t) Non-numeric or illegal DOCNO "0" changed to -361 (record 167684)
//	2017-01-01 10:03:46/1 *=* (Sys=t) Non-numeric or illegal DOCNO "0" changed to -362 (record 169171)
//	2017-01-01 10:03:49/1 *=* (Sys=t) Non-numeric or illegal DOCNO "0" changed to -363 (record 169836)
//	2017-01-01 10:03:52/1 *=* (Sys=t) Non-numeric or illegal DOCNO "0" changed to -364 (record 170508)
//	2017-01-01 10:03:52/1 *=* (Sys=t) Non-numeric or illegal DOCNO "0" changed to -365 (record 170543)
//	2017-01-01 10:03:54/1 *=* (Sys=t) Non-numeric or illegal DOCNO "0" changed to -366 (record 170846)
//	2017-01-01 10:03:56/1 *=* (Sys=t) Non-numeric or illegal DOCNO "0" changed to -367 (record 171235)
//	2017-01-01 10:03:56/1 *=* (Sys=t) Non-numeric or illegal DOCNO "0" changed to -368 (record 171268)
//	2017-01-01 10:03:57/1 *=* (Sys=t) Non-numeric or illegal DOCNO "0" changed to -369 (record 171474)
//	2017-01-01 10:03:57/1 *=* (Sys=t) Non-numeric or illegal DOCNO "0" changed to -370 (record 171533)
//	2017-01-01 10:03:58/1 *=* (Sys=t) Non-numeric or illegal DOCNO "0" changed to -371 (record 171561)
//	2017-01-01 10:03:58/1 *=* (Sys=t) Non-numeric or illegal DOCNO "0" changed to -372 (record 171681)
//	2017-01-01 10:04:01/1 *=* (Sys=t) Non-numeric or illegal DOCNO "0" changed to -373 (record 172271)
//	2017-01-01 10:04:01/1 *=* (Sys=t) Non-numeric or illegal DOCNO "0" changed to -374 (record 172470)
//	2017-01-01 10:04:09/1 *=* (Sys=t) Non-numeric or illegal DOCNO "0" changed to -375 (record 174043)
//	2017-01-01 10:04:17/1 *=* (Sys=t) Non-numeric or illegal DOCNO "0" changed to -376 (record 175662)
//	2017-01-01 10:04:22/1 *=* (Sys=t) Non-numeric or illegal DOCNO "0" changed to -377 (record 176606)
//	2017-01-01 10:04:24/1 *=* (Sys=t) Non-numeric or illegal DOCNO "0" changed to -378 (record 177017)
//	2017-01-01 10:04:28/1 *=* (Sys=t) Non-numeric or illegal DOCNO "0" changed to -379 (record 177900)
//	2017-01-01 10:04:40/1 *=* (Sys=t) Non-numeric or illegal DOCNO "0" changed to -380 (record 180095)
//	2017-01-01 10:04:44/1 *=* (Sys=t) Non-numeric or illegal DOCNO "0" changed to -381 (record 180995)
//	2017-01-01 10:04:45/1 *=* (Sys=t) Non-numeric or illegal DOCNO "0" changed to -382 (record 181141)
//	2017-01-01 10:04:47/1 *=* (Sys=t) Non-numeric or illegal DOCNO "0" changed to -383 (record 181487)
//	2017-01-01 10:04:47/1 *=* (Sys=t) Non-numeric or illegal DOCNO "0" changed to -384 (record 181579)
//	2017-01-01 10:04:53/1 *=* (Sys=t) Non-numeric or illegal DOCNO "0" changed to -385 (record 182838)
//	2017-01-01 10:04:53/1 *=* (Sys=t) Non-numeric or illegal DOCNO "0" changed to -386 (record 182839)
//	2017-01-01 10:04:57/1 *=* (Sys=t) Non-numeric or illegal DOCNO "0" changed to -387 (record 183550)
//	2017-01-01 10:05:02/1 *=* (Sys=t) Non-numeric or illegal DOCNO "0" changed to -388 (record 184666)
//	2017-01-01 10:05:08/1 *=* (Sys=t) Non-numeric or illegal DOCNO "0" changed to -389 (record 185866)
//	2017-01-01 10:05:08/1 *=* (Sys=t) Non-numeric or illegal DOCNO "0" changed to -390 (record 185918)
//	2017-01-01 10:05:11/1 *=* (Sys=t) Non-numeric or illegal DOCNO "0" changed to -391 (record 186485)
//	2017-01-01 10:05:18/1 *=* (Sys=t) Non-numeric or illegal DOCNO "0" changed to -392 (record 187965)
//	2017-01-01 10:05:22/1 *=* (Sys=t) Non-numeric or illegal DOCNO "0" changed to -393 (record 188614)
//	2017-01-01 10:05:22/1 *=* (Sys=t) Non-numeric or illegal DOCNO "0" changed to -394 (record 188615)
//	2017-01-01 10:05:22/1 *=* (Sys=t) Non-numeric or illegal DOCNO "0" changed to -395 (record 188616)
//	2017-01-01 10:05:22/1 *=* (Sys=t) Non-numeric or illegal DOCNO "0" changed to -396 (record 188701)
//	2017-01-01 10:05:22/1 *=* (Sys=t) Non-numeric or illegal DOCNO "0" changed to -397 (record 188702)
//	2017-01-01 10:05:22/1 *=* (Sys=t) Non-numeric or illegal DOCNO "0" changed to -398 (record 188703)
//	2017-01-01 10:05:22/1 *=* (Sys=t) Non-numeric or illegal DOCNO "0" changed to -399 (record 188704)
//	2017-01-01 10:05:22/1 *=* (Sys=t) Non-numeric or illegal DOCNO "0" changed to -400 (record 188705)
//	2017-01-01 10:05:22/1 *=* (Sys=t) Non-numeric or illegal DOCNO "0" changed to -401 (record 188757)
//	2017-01-01 10:05:22/1 *=* (Sys=t) Non-numeric or illegal DOCNO "0" changed to -402 (record 188758)
//	2017-01-01 10:05:22/1 *=* (Sys=t) Non-numeric or illegal DOCNO "0" changed to -403 (record 188760)
//	2017-01-01 10:05:22/1 *=* (Sys=t) Non-numeric or illegal DOCNO "0" changed to -404 (record 188761)
//	2017-01-01 10:05:23/1 *=* (Sys=t) Non-numeric or illegal DOCNO "0" changed to -405 (record 188883)
//	2017-01-01 10:05:25/1 *=* (Sys=t) Non-numeric or illegal DOCNO "0" changed to -406 (record 189233)
//	2017-01-01 10:05:30/1 *=* (Sys=t) Non-numeric or illegal DOCNO "0" changed to -407 (record 190247)
//	2017-01-01 10:05:33/1 *=* (Sys=t) Non-numeric or illegal DOCNO "0" changed to -408 (record 190834)
//	2017-01-01 10:05:36/1 *=* (Sys=t) Non-numeric or illegal DOCNO "0" changed to -409 (record 191425)
//	2017-01-01 10:05:40/1 *=* (Sys=t) Non-numeric or illegal DOCNO "0" changed to -410 (record 192205)
//	2017-01-01 10:05:41/1 *=* (Sys=t) Non-numeric or illegal DOCNO "0" changed to -411 (record 192430)
//	2017-01-01 10:05:44/1 *=* (Sys=t) Non-numeric or illegal DOCNO "0" changed to -412 (record 192950)
//	2017-01-01 10:05:46/1 *=* (Sys=t) Non-numeric or illegal DOCNO "0" changed to -413 (record 193398)
//	2017-01-01 10:05:51/1 *=* (Sys=t) Non-numeric or illegal DOCNO "0" changed to -414 (record 194462)
//	2017-01-01 10:06:02/1 *=* (Sys=t) Non-numeric or illegal DOCNO "0" changed to -415 (record 196579)
//	2017-01-01 10:06:10/1 *=* (Sys=t) Non-numeric or illegal DOCNO "0" changed to -416 (record 198314)
//	2017-01-01 10:06:11/1 *=* (Sys=t) Non-numeric or illegal DOCNO "0" changed to -417 (record 198583)
//	2017-01-01 10:06:22/1 *=* (Sys=t) Non-numeric or illegal DOCNO "0" changed to -418 (record 200740)
//	2017-01-01 10:06:33/1 *=* (Sys=t) Non-numeric or illegal DOCNO "0" changed to -419 (record 202966)
//	2017-01-01 10:06:38/1 *=* (Sys=t) Non-numeric or illegal DOCNO "0" changed to -420 (record 203892)
//	2017-01-01 10:06:38/1 *=* (Sys=t) Illegal DOCTYPE "" - skipping record (record 203892)<br>col_nums: Array
//	(
//		[DOCNO] => 0
//		[DOCTYPE] => 1
//		[DATE] => 2
//		[CLID] => 3
//		[NETCOST] => 4
//		[VATCOST] => 5
//		[RECD] => 6
//		[EOM] => 7
//		[S_INVS] => 8
//		[PSTART] => 9
//		[PEND] => 10
//		[COMPLETE] => 11
//	)
//	<br>record: Array
//	(
//		[0] => 0
//		[1] => 
//		[2] => 19/10/2016
//		[3] => 0
//		[4] => 0
//		[5] => 0
//		[6] => 0
//		[7] => False
//		[8] => 
//		[9] => 
//		[10] => 
//		[11] => 
//	)
//
//	2017-01-01 10:06:40/1 *=* (Sys=t) Non-numeric or illegal DOCNO "0" changed to -421 (record 204287)
//	2017-01-01 10:06:40/1 *=* (Sys=t) Illegal DOCTYPE "" - skipping record (record 204287)<br>col_nums: Array
//	(
//		[DOCNO] => 0
//		[DOCTYPE] => 1
//		[DATE] => 2
//		[CLID] => 3
//		[NETCOST] => 4
//		[VATCOST] => 5
//		[RECD] => 6
//		[EOM] => 7
//		[S_INVS] => 8
//		[PSTART] => 9
//		[PEND] => 10
//		[COMPLETE] => 11
//	)
//	<br>record: Array
//	(
//		[0] => 0
//		[1] => 
//		[2] => 08/11/2016
//		[3] => 0
//		[4] => 0
//		[5] => 0
//		[6] => 0
//		[7] => False
//		[8] => 
//		[9] => 
//		[10] => 
//		[11] => 
//	)
	#	2017-01-01 10:06:44/1 Imported 205104 invoices from a Traces file of 205107 invoices (and 1 header record).
	#	Input file had min DOCNO of 182, max of 205773, and had 321 zero DOCNO.
	#	Time taken: 15.683333333333 mins (4.5879163741321 secs per 1000 invoices)
	#	Collect Invoices:
	#	import_2017_01_01_1012.log
	#	2017-01-01 10:13:27/1 Imported 16486 invoices from a Collections file of 16486 invoices (and 1 header record).
	#	Input file had min DOCNO of 1, max of 115914, and had 0 zero DOCNO.
	#	Time taken: 1.4 mins (5.0952323183307 secs per 1000 invoices)
	
	
	# Traces:
	# Imported 190918 invoices from a Traces file of 190919 invoices (and 1 header record).
	# Input file had min DOCNO of 182, max of 192169, and had 308 zero DOCNO.
	# Time taken: 11.166666666667 mins (3.5093600393886 secs per 1000 invoices)
	
	# Collect:
	# Imported 12120 invoices from a Collections file of 12121 invoices (and 1 header record).
	# Input file had min DOCNO of 1, max of 111835, and had 1 zero DOCNO.
	# Time taken: 0.68333333333333 mins (3.3828382838284 secs per 1000 invoices)
	
	# Traces 27/09/16:
	#		Imported 203418 invoices from a Traces file of 203419 invoices (and 1 header record).
	#		Input file had min DOCNO of 182, max of 204143, and had 319 zero DOCNO.
	#		Time taken: 16.95 mins (4.9995575612778 secs per 1000 invoices)
	
	# Collect 27/09/16:
	#		Imported 15856 invoices from a Collections file of 15856 invoices (and 1 header record).
	#		Input file had min DOCNO of 1, max of 115309, and had 0 zero DOCNO.
	#		Time taken: 1.0833333333333 mins (4.0993945509586 secs per 1000 invoices)
	
	global $col_nums; # for csvfield()
	global $csvfield_error;
	global $input_count; # for csvfield()
	global $one_record; # for csvfield()
	global $sqlFalse;
	global $sqlTrue;
	global $tc;
		
	$enabled = false; #
	if (!$enabled)
	{
		dprint("This import function is disabled", true);
		return;
	}
	if (($tc != 'c') && ($tc != 't') && ($tc != '*'))
	{
		dlog("*=* import_ledger() - illegal tc \"$tc\"", true);
		return;
	}

	print "<h3>Importing Ledger (" . (($tc == 'c') ? "Collect" : "Trace") . " system)</h3>";
	
	$start_time = time();
	set_time_limit(60 * 60 * 8); # 8 hours
	
	if ($tc == '*')
	{
		init_ledger($tc);
		dprint("Old Invoices all deleted");
		return;
	}
	
	# File (Jan 2015) has 190,919 records.
	
	$stop_after = 0;#
	$stop_margin = 0;
	$input_count = 0;
	
	# Process file in two halves on local PC to avoid script timing out!
	$first_half = true;
	$second_half = true;
	#$first_half = true;
	#$second_half = false;#
	#$first_half = false;#
	#$second_half = true;
	
	$sys = $tc; # Either 't' or 'c' from this point
	$dirname = "import-vilcol/{$sys}";
	if ($sys == 't')
	{
		$sys_txt = "Traces";
		$csv = "LEDGER.csv";
	}
	else
	{
		$sys_txt = "Collections";
		$csv = "LEDGER.csv";
	}
	
	$fp_csv = fopen("$dirname/$csv", 'r');
	if ($fp_csv)
	{
		#sql_encryption_preparation('x');
		
		$col_nums = array();
		
		$inv_min = 0;
		$inv_max = 0;
		$inv_zero_count = 0;
		$inv_zero_replacement = -101;
		$inv_count = 0;
		
		$while_safety = 0; # to avoid infinite-looping
		while (true)
		{
			$verbose = false;
			if ((0 < $stop_after) && ($input_count <= $stop_after) && (($stop_after - $input_count) < $stop_margin))
				$verbose = true;
			
			$while_safety++;
			if ($while_safety > 800000) # 800,000 docs
			{
				dlog("*=* (Sys=$sys) Safety Ejection!");
				break; # while(true)
			}

			if ($verbose)
				dprint("=== Record $input_count ===");
				
			$one_record = fgetcsv($fp_csv);
			if ($input_count > 0) # skip header record
			{
				if ($one_record && (count($one_record) > 0)) # test for end-of-records
				{
					$inv_raw = csvfield('DOCNO');
					$inv_num = 1 * $inv_raw;
					if ($inv_num > 0)
					{
						if (($inv_min == 0) || ($inv_num < $inv_min))
							$inv_min = $inv_num;
						if (($inv_max == 0) || ($inv_max < $inv_num))
							$inv_max = $inv_num;
					}
					else
					{
						$inv_num = $inv_zero_replacement--;
						dlog("*=* (Sys=$sys) Non-numeric or illegal DOCNO \"$inv_raw\" changed to $inv_num " .
									"(record $input_count)");
						$inv_zero_count++;
					}
				}
				else 
				{
					dlog("End of Ledger");
					break; # while(true)
				}
				
				if ($input_count < 100000)
				{
					if (!$first_half)
					{
						$input_count++;
						continue;
					}
				}
				else
				{
					if (!$second_half)
					{
						$input_count++;
						continue;
					}
				}
			}
				
			if ((0 < $stop_after) && ($stop_after < $input_count))
			{
				$input_count++;
				continue;
			}
						
			if ($verbose)
				dprint((($input_count == 0) ? "Headers: " : "Invoice: ") . print_r($one_record,1));
			if ($input_count == 0)
			{
				# Header record
				foreach ($one_record as $num => $field)
					$col_nums[$field] = $num;
				if ($verbose)
					dprint("col_nums: " . print_r($col_nums,1));#
			}
			else
			{
				# Data record
				
				# Note that for Traces ledger, this contains pre-2010 collections ledger records too.
				
				$inv_type = strtoupper(csvfield('DOCTYPE'));
				if ( ! (($inv_type == 'I') || ($inv_type == 'C') || (($sys == 't') && ($inv_type == 'F'))))
				{
					dlog("*=* (Sys=$sys) Illegal DOCTYPE \"$inv_type\" - skipping record (record $input_count)<br>" .
							"col_nums: " . print_r($col_nums,1) . "<br>" .
							"record: " . print_r($one_record,1));
					$input_count++;
					continue; # Abandon this data record, move to next one
				}
				$inv_type = "'$inv_type'";
				
				$inv_dt = csvfield('DATE', 'uk', true);
				if ($csvfield_error)
				{
					dlog("*=* (Sys=$sys) Illegal DATE \"" . csvfield('DATE') . "\" - skipping record (record $input_count)<br>" .
							"Error = \"$csvfield_error\"<br>" .
							"col_nums: " . print_r($col_nums,1) . "<br>" .
							"record: " . print_r($one_record,1));
					$input_count++;
					continue; # Abandon this data record, move to next one
				}
				$inv_dt = "'$inv_dt'";
				
				$client2_id = client_id_from_code(csvfield('CLID'));
				if (!($client2_id > 0))
				{
					dlog("*=* (Sys=$sys) Unrecognised CLID \"" . csvfield('CLID') . "\" - skipping record (record $input_count)<br>" .
							"col_nums: " . print_r($col_nums,1) . "<br>" .
							"record: " . print_r($one_record,1));
					$input_count++;
					continue; # Abandon this data record, move to next one
				}
				
				$inv_net = 1.0 * csvfield('NETCOST');
				
				$inv_vat = 1.0 * csvfield('VATCOST');
				
				$inv_rx = 1.0 * csvfield('RECD');
				
				$z_eom = "'" . csvfield('EOM') . "'";
				
				$s_invs = strtolower(csvfield('S_INVS'));
				if ($s_invs == 'col')
				{
					$inv_s_invs = 'C';
					$inv_stmt = $sqlTrue;
					#$inv_sys = 'C';
					$inv_sys = strtoupper($sys); # 20/07/16
				}
				elseif ($s_invs == 'gen')
				{
					$inv_s_invs = 'G';
					$inv_stmt = $sqlFalse;
					$inv_sys = 'G';
				}
				elseif ($s_invs == 'no')
				{
					$inv_s_invs = 'N';
					$inv_stmt = $sqlFalse;
					#$inv_sys = 'T';
					$inv_sys = strtoupper($sys); # 20/07/16
				}
				elseif ($s_invs == 'yes')
				{
					$inv_s_invs = 'Y';
					$inv_stmt = $sqlTrue;
					#$inv_sys = 'C';
					$inv_sys = strtoupper($sys); # 20/07/16
				}
				else
				{
					dlog("*=* (Sys=$sys) Unrecognised S_INVS \"" . csvfield('S_INVS') . "\" - skipping record (record $input_count)<br>" .
							"col_nums: " . print_r($col_nums,1) . "<br>" .
							"record: " . print_r($one_record,1));
					$input_count++;
					continue; # Abandon this data record, move to next one
				}
				
				$inv_s_invs = "'$inv_s_invs'";
				$inv_sys = "'$inv_sys'";
				$inv_sys_imp = "'" . strtoupper($sys) . "'"; # T or C
				
				$inv_start_dt = '';
				$inv_end_dt = '';
				if (	(($sys == 't') && (($s_invs == 'col') || ($s_invs == 'yes'))) ||
						(($sys == 'c') && ($s_invs == 'col'))
				   )
				{
					if (csvfield('PSTART'))
					{
						$inv_start_dt = csvfield('PSTART', 'uk', true);
						if ($csvfield_error)
						{
							dlog("*=* (Sys=$sys) Illegal PSTART \"" . csvfield('PSTART') . "\" - skipping record (record $input_count)<br>" .
									"Error = \"$csvfield_error\"<br>" .
									"col_nums: " . print_r($col_nums,1) . "<br>" .
									"record: " . print_r($one_record,1));
							$input_count++;
							continue; # Abandon this data record, move to next one
						}
					}
					if (csvfield('PEND'))
					{
						$inv_end_dt = csvfield('PEND', 'uk', true);
						if ($csvfield_error)
						{
							dlog("*=* (Sys=$sys) Illegal PEND \"" . csvfield('PEND') . "\" - skipping record (record $input_count)<br>" .
									"Error = \"$csvfield_error\"<br>" .
									"col_nums: " . print_r($col_nums,1) . "<br>" .
									"record: " . print_r($one_record,1));
							$input_count++;
							continue; # Abandon this data record, move to next one
						}
					}
				}
				else
				{
					if (csvfield('PSTART'))
					{
						dlog("*=* (Sys=$sys) Unexpected presence of PSTART \"" . csvfield('PSTART') . "\" - skipping record (record $input_count)<br>" .
								"col_nums: " . print_r($col_nums,1) . "<br>" .
								"record: " . print_r($one_record,1));
						$input_count++;
						continue; # Abandon this data record, move to next one
					}
					if (csvfield('PEND'))
					{
						dlog("*=* (Sys=$sys) Unexpected presence of PEND \"" . csvfield('PEND') . "\" - skipping record (record $input_count)<br>" .
								"col_nums: " . print_r($col_nums,1) . "<br>" .
								"record: " . print_r($one_record,1));
						$input_count++;
						continue; # Abandon this data record, move to next one
					}
				}
				if ($inv_start_dt)
					$inv_start_dt = "'$inv_start_dt'";
				else
					$inv_start_dt = "NULL";
				if ($inv_end_dt)
					$inv_end_dt = "'$inv_end_dt'";
				else
					$inv_end_dt = "NULL";
					
				$inv_complete = (yesno2bool('COMPLETE') ? $sqlTrue : $sqlFalse);
				
				
				
				# --- SQL Insertions - INVOICE --------------------------------------------

				$fields = "INV_SYS,  INV_SYS_IMP,  INV_NUM,  INV_TYPE,  INV_DT,  INV_DUE_DT,   CLIENT2_ID,  INV_NET,  INV_VAT,  INV_RX,  ";
				$values = "$inv_sys, $inv_sys_imp, $inv_num, $inv_type, $inv_dt, '2020-01-01', $client2_id, $inv_net, $inv_vat, $inv_rx, ";
				
				$fields .= "INV_S_INVS,  INV_STMT,  INV_START_DT,  INV_END_DT,  INV_COMPLETE,  INV_APPROVED_DT, ";
				$values .= "$inv_s_invs, $inv_stmt, $inv_start_dt, $inv_end_dt, $inv_complete, '1971-01-01',    ";

				$fields .= "IMPORTED, Z_EOM";
				$values .= "$sqlTrue, $z_eom";

				
				$sql = "INSERT INTO INVOICE ($fields) VALUES ($values)";
				if ($verbose)
					dprint($sql);
				$invoice_id = sql_execute($sql); # no need to audit
				if ($verbose)
					dprint("-> ID $invoice_id");
				$inv_count++;
					
			} # else data record
			
			$input_count++;
			if ((0 < $stop_after) && ($stop_after < $input_count))
			{
				dlog("Stopping after $stop_after invoices");
				#break; # while(true)
			}
			#if (($input_count % 100) == 0)
			#	dlog("Read $input_count records so far...");#
			
		} # while(true)

		$time_taken = 1.0 * (time() - $start_time) / 60.0;
		$per_rec = 60.0 * $time_taken / (1.0 * $inv_count / 1000.0);
		dlog("Imported $inv_count invoices from a $sys_txt file of " . ($input_count-1) . " invoices (and 1 header record).<br>" .
				"Input file had min DOCNO of $inv_min, max of $inv_max, and had $inv_zero_count zero DOCNO.<br>" .
				"Time taken: $time_taken mins ($per_rec secs per 1000 invoices)");
		
		fclose($fp_csv);
	}
	else
		dlog("*=* (Sys=$sys) Failed to fopen(\"$dirname/$csv\",'r')");

} # import_ledger()

function import_genbill()
{
	# Before calling this function:
	#	- Export TRACE/GENBILL.DBF as GENBILL.csv using DBF Manager with default settings.
	#	- Export COLLECT/GENBILL.DBF as GENBILL.csv using DBF Manager with default settings.
	# Required:
	#	t/GENBILL.csv
	#	c/GENBILL.csv
	
	# Import 31/12/16:
	#	Traces:
	#	import_2017_01_01_1014.log
	#	2017-01-01 10:15:01/1 *=* (Sys=t) Invoice not found from DOCNO "164709" - skipping record (record 3973)
	#	Error: No invoices found from
	#	SELECT INVOICE_ID FROM INVOICE WHERE (OBSOLETE=0) AND (INV_NUM=164709) AND (INV_SYS='G') AND (INV_TYPE='I') AND (INV_SYS_IMP='T')
	#	col_nums: Array
//		(
//			[DESC] => 0
//			[COST] => 1
//			[CLID] => 2
//			[DOCNO] => 3
//			[DOCTYPE] => 4
//		)
//		<br>record: Array
//		(
//			[0] => Batch 7 6 Traces @ £25 per
//			[1] => 150
//			[2] => 3190
//			[3] => 164709
//			[4] => I
//		)
	#	2017-01-01 10:15:01/1 *=* (Sys=t) Invoice not found from DOCNO "164710" - skipping record (record 3974)
	#	2017-01-01 10:15:10/1 *=* (Sys=t) Invoice not found from DOCNO "8267" - skipping record (record 5376)
	#	2017-01-01 10:15:10/1 *=* (Sys=t) Invoice not found from DOCNO "8267" - skipping record (record 5377)
	#	2017-01-01 10:15:10/1 *=* (Sys=t) Invoice not found from DOCNO "204971" - skipping record (record 5393)
	#	2017-01-01 10:15:10/1 *=* (Sys=t) Invoice not found from DOCNO "204971" - skipping record (record 5394)
	#	2017-01-01 10:15:10/1 *=* (Sys=t) Invoice not found from DOCNO "204971" - skipping record (record 5395)
	#	2017-01-01 10:15:10/1 Imported 5435 bills from a Traces file of 5442 bills (and 1 header record).
	#	Input file had min DOCNO of 281, max of 205754, and had 0 zero DOCNO.
	#	Time taken: 0.61666666666667 mins (6.8077276908924 secs per 1000 invoices)
	#	Collections:
	#	import_2017_01_01_1017.log
	#	2017-01-01 10:18:02/1 Imported 670 bills from a Collections file of 670 bills (and 1 header record).
	#	Input file had min DOCNO of 1, max of 115130, and had 0 zero DOCNO.
	#	Time taken: 0.083333333333333 mins (7.4626865671642 secs per 1000 invoices)

	
	# Traces 27/09/16:
	#		Imported 1394 bills from a Traces file of 5353 bills (and 1 header record).
	#		Input file had min DOCNO of 281, max of 204143, and had 0 zero DOCNO.
	#		Time taken: 0.45 mins (19.368723098996 secs per 1000 invoices)
	
	# Collect 27/09/16:
	#		Imported 643 bills from a Collections file of 643 bills (and 1 header record).
	#		Input file had min DOCNO of 1, max of 115130, and had 0 zero DOCNO.
	#		Time taken: 0.066666666666667 mins (6.2208398133748 secs per 1000 invoices)
	
	global $col_nums; # for csvfield()
	global $input_count; # for csvfield()
	global $invoice_error;
	global $one_record; # for csvfield()
	global $sqlTrue;
	global $tc;
		
	$enabled = false; #
	if (!$enabled)
	{
		dprint("This import function is disabled", true);
		return;
	}
	if (($tc != 'c') && ($tc != 't'))
	{
		dlog("*=* import_genbill() - illegal tc \"$tc\"", true);
		return;
	}
	if ($tc == 'c')
		$bl_sys = 'C';
	else
		$bl_sys = 'R';
	
	print "<h3>Importing Gen-Bill (" . (($tc == 'c') ? "Collect" : "Trace") . " system)</h3>";
	
	$start_time = time();
	set_time_limit(60 * 60 * 8); # 8 hours
	
	init_genbill($tc, $bl_sys);
	
	$stop_after = 0;#
	$stop_margin = 0;
	$input_count = 0;
	
	$sys = $tc; # Either 't' or 'c'
	$dirname = "import-vilcol/{$sys}";
	if ($sys == 't')
	{
		$sys_txt = "Traces";
		$csv = "GENBILL.csv";
	}
	else
	{
		$sys_txt = "Collections";
		$csv = "GENBILL.csv";
	}
	
	$fp_csv = fopen("$dirname/$csv", 'r');
	if ($fp_csv)
	{
		#sql_encryption_preparation('x');
		
		$col_nums = array();
		
		$inv_min = 0;
		$inv_max = 0;
		$inv_zero_count = 0;
		$inv_count = 0;
		
		$while_safety = 0; # to avoid infinite-looping
		while (true)
		{
			$verbose = false;
			if ((0 < $stop_after) && ($input_count <= $stop_after) && (($stop_after - $input_count) < $stop_margin))
				$verbose = true;
			
			$while_safety++;
			if ($while_safety > 800000)
			{
				dlog("*=* (Sys=$sys) Safety Ejection!");
				break; # while(true)
			}

			if ($verbose)
				dprint("=== Record $input_count ===");
				
			$one_record = fgetcsv($fp_csv);
			if ($input_count > 0) # skip header record
			{
				if ($one_record && (count($one_record) > 0)) # test for end-of-records
				{
					$inv_raw = csvfield('DOCNO');
					$inv_num = 1 * $inv_raw;
					if ($inv_num > 0)
					{
						if (($inv_min == 0) || ($inv_num < $inv_min))
							$inv_min = $inv_num;
						if (($inv_max == 0) || ($inv_max < $inv_num))
							$inv_max = $inv_num;
					}
					else
					{
						dlog("*=* (Sys=$sys) Non-numeric or illegal DOCNO \"$inv_raw\" - skipping record (record $input_count)<br>" .
								"col_nums: " . print_r($col_nums,1) . "<br>" .
								"record: " . print_r($one_record,1));
						$inv_zero_count++;
						$input_count++;
						continue;
					}
				}
				else 
				{
					dlog("End of GenBill");
					break; # while(true)
				}
			}
				
			if ((0 < $stop_after) && ($stop_after < $input_count))
			{
				$input_count++;
				continue;
			}
						
			if ($verbose)
				dprint((($input_count == 0) ? "Headers: " : "Billing: ") . print_r($one_record,1));
			if ($input_count == 0)
			{
				# Header record
				foreach ($one_record as $num => $field)
					$col_nums[$field] = $num;
				if ($verbose)
					dprint("col_nums: " . print_r($col_nums,1));#
			}
			else
			{
				# Data record
				
				$bl_descr = quote_smart(csvfield('DESC'), true);
				
				$bl_cost = 1.0 * csvfield('COST');
				
				$z_clid = "'" . csvfield('CLID') . "'";
				
				$z_doctype = strtoupper(csvfield('DOCTYPE'));
				if ( ! (($z_doctype == 'I') || ($z_doctype == 'C')) )
				{
					dlog("*=* (Sys=$sys) Illegal DOCTYPE \"$z_doctype\" - skipping record (record $input_count)<br>" .
							"col_nums: " . print_r($col_nums,1) . "<br>" .
							"record: " . print_r($one_record,1));
					$input_count++;
					continue; # Abandon this data record, move to next one
				}

				$sys_imp = strtoupper($tc);
				$invoice_id = invoice_id_from_docno($inv_num, 'G', $z_doctype, $sys_imp);
				if ($invoice_id == 0)
				{
					dlog("*=* (Sys=$sys) Invoice not found from DOCNO \"$inv_raw\" - skipping record (record $input_count)<br>" .
							"Error: $invoice_error<br>" .
							"col_nums: " . print_r($col_nums,1) . "<br>" .
							"record: " . print_r($one_record,1));
					$input_count++;
					continue; # Abandon this data record, move to next one
				}
				
				$z_doctype = "'$z_doctype'";
				
				
				# --- SQL Insertions - INV_BILLING --------------------------------------------

				$fields = "INVOICE_ID,  INV_NUM,  JOB_ID, BL_SYS,    BL_SYS_IMP, BL_DESCR,  BL_COST,  ";
				$values = "$invoice_id, $inv_num, NULL,   '$bl_sys', '$sys_imp', $bl_descr, $bl_cost, ";
				
				$fields .= "BL_LPOS, BL_LETTER_DT, IMPORTED, Z_CLID,  Z_DOCTYPE,  Z_S_INVS, Z_NSECS";
				$values .= "NULL,    NULL,         $sqlTrue, $z_clid, $z_doctype, NULL,     NULL";

				
				$sql = "INSERT INTO INV_BILLING ($fields) VALUES ($values)";
				if ($verbose)
					dprint($sql);
				$inv_billing_id = sql_execute($sql); # no need to audit
				if ($verbose)
					dprint("-> ID $inv_billing_id");
				$inv_count++;
					
			} # else data record
			
			$input_count++;
			if ((0 < $stop_after) && ($stop_after < $input_count))
			{
				dlog("Stopping after $stop_after bills");
				#break; # while(true)
			}
			
		} # while(true)

		$time_taken = 1.0 * (time() - $start_time) / 60.0;
		$per_rec = 60.0 * $time_taken / (1.0 * $inv_count / 1000.0);
		dlog("Imported $inv_count bills from a $sys_txt file of " . ($input_count-1) . " bills (and 1 header record).<br>" .
				"Input file had min DOCNO of $inv_min, max of $inv_max, and had $inv_zero_count zero DOCNO.<br>" .
				"Time taken: $time_taken mins ($per_rec secs per 1000 invoices)");
		
		fclose($fp_csv);
	}
	else
		dlog("*=* (Sys=$sys) Failed to fopen(\"$dirname/$csv\",'r')");

} # import_genbill()

function import_billing()
{
	# Before calling this function:
	#	- Export TRACE/BILLING.DBF as BILLING.csv using DBF Manager with default settings.
	# Required:
	#	t/BILLING.csv
	
	# Import 31/12/16:
	# Traces:
	#	import_2017_01_01_1019.log
	#	2017-01-01 10:19:42/1 *=* (Sys=t) Non-numeric or illegal INVNO/CREDNO/DOCTYPE "14620"/"497"/"C" - skipping (1) record (record 13923)<br>col_nums: Array
//		(
//			[SEQUENCE] => 0
//			[DESC] => 1
//			[COST] => 2
//			[S_INVS] => 3
//			[LETDATE] => 4
//			[CLID] => 5
//			[INVNO] => 6
//			[CREDNO] => 7
//			[DOCTYPE] => 8
//			[NSECS] => 9
//			[LPOS] => 10
//		)
//		<br>record: Array
//		(
//			[0] => 140027
//			[1] => Retrace 1
//			[2] => -30
//			[3] => no
//			[4] => 21/12/1995
//			[5] => 268
//			[6] => 14620
//			[7] => 500
//			[8] => C
//			[9] => 188388722
//			[10] => 1
//		)
//		2017-01-01 10:19:42/1 *=* (Sys=t) Non-numeric or illegal INVNO/CREDNO/DOCTYPE "14621"/"497"/"C" - skipping (1) record (record 13925)<br>col_nums: Array
//		(
//			[SEQUENCE] => 0
//			[DESC] => 1
//			[COST] => 2
//			[S_INVS] => 3
//			[LETDATE] => 4
//			[CLID] => 5
//			[INVNO] => 6
//			[CREDNO] => 7
//			[DOCTYPE] => 8
//			[NSECS] => 9
//			[LPOS] => 10
//		)
//		<br>record: Array
//		(
//			[0] => 140081
//			[1] => Retrace 1
//			[2] => -30
//			[3] => no
//			[4] => 21/12/1995
//			[5] => 498
//			[6] => 14621
//			[7] => 501
//			[8] => C
//			[9] => 188388724
//			[10] => 1
//		)
//		2017-01-01 10:19:45/1 *=* (Sys=t) Non-numeric or illegal INVNO/CREDNO/DOCTYPE "16067"/"583"/"C" - skipping (1) record (record 15794)<br>col_nums: Array
//		(
//			[SEQUENCE] => 0
//			[DESC] => 1
//			[COST] => 2
//			[S_INVS] => 3
//			[LETDATE] => 4
//			[CLID] => 5
//			[INVNO] => 6
//			[CREDNO] => 7
//			[DOCTYPE] => 8
//			[NSECS] => 9
//			[LPOS] => 10
//		)
//		<br>record: Array
//		(
//			[0] => 145440
//			[1] => Retrace 1
//			[2] => -25
//			[3] => no
//			[4] => 16/07/1996
//			[5] => 489
//			[6] => 16067
//			[7] => 585
//			[8] => C
//			[9] => 206359670
//			[10] => 1
//		)
//		2017-01-01 10:24:27/1 *=* (Sys=t) Non-numeric or illegal INVNO/CREDNO/DOCTYPE "140713"/"0"/"C" - skipping (1) record (record 180381)<br>col_nums: Array
//		(
//			[SEQUENCE] => 0
//			[DESC] => 1
//			[COST] => 2
//			[S_INVS] => 3
//			[LETDATE] => 4
//			[CLID] => 5
//			[INVNO] => 6
//			[CREDNO] => 7
//			[DOCTYPE] => 8
//			[NSECS] => 9
//			[LPOS] => 10
//		)
//		<br>record: Array
//		(
//			[0] => 665677
//			[1] => Retrace 1
//			[2] => -30
//			[3] => no
//			[4] => 19/12/2006
//			[5] => 1584
//			[6] => 140713
//			[7] => 6226
//			[8] => C
//			[9] => 535389309
//			[10] => 1
//		)
//		2017-01-01 10:24:44/1 *=* (Sys=t) Non-numeric or illegal INVNO/CREDNO/DOCTYPE "144535"/"6366"/"C" - skipping (1) record (record 189915)<br>col_nums: Array
//		(
//			[SEQUENCE] => 0
//			[DESC] => 1
//			[COST] => 2
//			[S_INVS] => 3
//			[LETDATE] => 4
//			[CLID] => 5
//			[INVNO] => 6
//			[CREDNO] => 7
//			[DOCTYPE] => 8
//			[NSECS] => 9
//			[LPOS] => 10
//		)
//		<br>record: Array
//		(
//			[0] => 675912
//			[1] => Retrace 1
//			[2] => -30
//			[3] => no
//			[4] => 15/06/2007
//			[5] => 1584
//			[6] => 144535
//			[7] => 6369
//			[8] => C
//			[9] => 550764446
//			[10] => 1
//		)
//		2017-01-01 10:32:22/1 Imported 40300 bills from a Traces Billing file of 290779 bills (and 1 header record).
//		Input file had min INVNO/CREDNO of 182, max of 205773, and had 5 zero XNOs.
//		250474 bills had a sequence number that couldn't be found in the database of jobs.
//		0 bills had an invoice number that couldn't be found in the database of invoices.
//		0 bills had a DOC/CREDIT number that couldn't be found in the database of invoices.
//		Time taken: 13.083333333333 mins (19.478908188586 secs per 1000 bills)
	
	# Note that this only imports records into INV_BILLING where BL_SYS_IMP=T and BL_SYS=T.
	# Result of import on 08/06/16 after fixing bug to reject fewer billing records:
	#		Imported 47737 bills from a Traces Billing file of 273522 bills (and 1 header record).
	#		Input file had min INVNO/CREDNO of 182, max of 192169, and had 5 zero XNOs.
	#		225780 bills had a sequence number that couldn't be found in the database of jobs.
	#		0 bills had an invoice number that couldn't be found in the database of invoices.
	#		0 bills had a DOC/CREDIT number that couldn't be found in the database of invoices.
	#		Time taken: 8.3833333333333 mins (10.536900098456 secs per 1000 bills)
	#		
	#		SELECT COUNT(*) FROM INV_BILLING
	#			52931
	#			
	#		SELECT B.BL_SYS_IMP, B.BL_SYS, I.INV_TYPE, COUNT(*) 
	#		FROM INV_BILLING AS B LEFT JOIN INVOICE AS I ON I.INVOICE_ID=B.INVOICE_ID
	#		GROUP BY B.BL_SYS_IMP, B.BL_SYS, I.INV_TYPE
	#			C	C	C		295
	#			C	C	I		59
	#			T	R	C		1655
	#			T	R	I		3185
	#			T	T	NULL	492
	#			T	T	C		659
	#			T	T	I		46586
	#		and 492 + 659 + 46586 = 47737
	
	# 27/09/16:
	#		Imported 38241 bills from a Traces Billing file of 288727 bills (and 1 header record).
	#		Input file had min INVNO/CREDNO of 182, max of 204142, and had 5 zero XNOs.
	#		250474 bills had a sequence number that couldn't be found in the database of jobs.
	#		7 bills had an invoice number that couldn't be found in the database of invoices.
	#		0 bills had a DOC/CREDIT number that couldn't be found in the database of invoices.
	#		Time taken: 9.25 mins (14.51321879658 secs per 1000 bills)
	
	global $col_nums; # for csvfield()
	global $input_count; # for csvfield()
	global $invoice_error;
	global $one_record; # for csvfield()
	global $sqlTrue;
		
	$enabled = false; #
	if (!$enabled)
	{
		dprint("This import function is disabled", true);
		return;
	}
	print "<h3>Importing Trace Billing</h3>";
	
	$start_time = time();
	set_time_limit(60 * 60 * 8); # 8 hours
	
	init_billing();
	
	$stop_after = 0;#
	$stop_margin = 0;
	$input_count = 0;
	$output_count = 0;
	
	$sys = 't';
	$dirname = "import-vilcol/{$sys}";
	$csv = "BILLING.csv";
	$fp_csv = fopen("$dirname/$csv", 'r');
	if ($fp_csv)
	{
		$col_nums = array();
		
		$inv_min = 0;
		$inv_max = 0;
		$inv_zero_count = 0;
		$inv_bad = 0;
		$seq_bad = 0;
		$doc_bad = 0;
		
		$while_safety = 0; # to avoid infinite-looping
		while (true)
		{
			$print_buffer = array();
			$verbose = false;
			if ((0 < $stop_after) && ($output_count < $stop_after) && (($stop_after - $output_count) <= $stop_margin))
				$verbose = true;
			
			$while_safety++;
			if ($while_safety > 800000) # 800,000 bills
			{
				dlog("*=* (Sys=$sys) Safety Ejection!");
				break; # while(true)
			}

			if ($verbose)
				$print_buffer[] = "=== Record $input_count ===";
				
			$one_record = fgetcsv($fp_csv);
			if ($input_count > 0) # skip header record
			{
				if ($one_record && (count($one_record) > 0)) # test for end-of-records
				{
					$skip = 0;
					$z_doctype = strtoupper(csvfield('DOCTYPE'));
					
					$invno = csvfield('INVNO');
					if (($invno !== '') && ($invno !== '0') && (0 < (1 * $invno)))
					{
						if ($z_doctype == 'I')
							$inv_num = 1 * $invno;
						else
							$skip = 1;
					}
					else
					{
						$credno = csvfield('CREDNO');
						if (($credno !== '') && ($credno !== '0') && (0 < (1 * $credno)))
						{
							if ($z_doctype == 'C')
								$inv_num = 1 * $credno;
							else
								$skip = 2;
						}
						else
						{
							if (($z_doctype == 'F') || ($z_doctype == ''))
								$inv_num = 0; # this is OK
							else
								$skip = 3;
						}
					}
					if ($skip)
					{
						dlog("*=* (Sys=$sys) Non-numeric or illegal INVNO/CREDNO/DOCTYPE \"$invno\"/\"$credno\"/\"$z_doctype\" - skipping ($skip) record (record $input_count)<br>" .
								"col_nums: " . print_r($col_nums,1) . "<br>" .
								"record: " . print_r($one_record,1));
						$inv_zero_count++;
						$input_count++;
						continue;
					}
					if (0 < $inv_num)
					{
						if (($inv_min == 0) || ($inv_num < $inv_min))
							$inv_min = $inv_num;
						if (($inv_max == 0) || ($inv_max < $inv_num))
							$inv_max = $inv_num;
					}
				}
				else 
				{
					dlog("End of Billing");
					break; # while(true)
				}
			}
				
			if ((0 < $stop_after) && ($stop_after <= $output_count))
			{
				$input_count++;
				continue;
			}
						
			if ($verbose)
				$print_buffer[] = (($input_count == 0) ? "Headers: " : "Billing: ") . print_r($one_record,1);
			if ($input_count == 0)
			{
				# Header record
				foreach ($one_record as $num => $field)
					$col_nums[$field] = $num;
				if ($verbose)
					$print_buffer[] = "col_nums: " . print_r($col_nums,1);#
			}
			else
			{
				# Data record
				
				$job_id = job_id_from_sequence(1 * csvfield('SEQUENCE'));
				if ( ! ($job_id > 0) )
				{
//					dlog("*=* (Sys=$sys) JOB_ID not found from SEQUENCE \"" . csvfield('SEQUENCE') . "\" - skipping record (record $input_count)<br>" .
//							"col_nums: " . print_r($col_nums,1) . "<br>" .
//							"record: " . print_r($one_record,1));
					$seq_bad++;
					$input_count++;
					continue; # Abandon this data record, move to next one
				}
				
				if ($verbose)
				{
					foreach ($print_buffer as $pbline)
						dprint($pbline);
				}
				
				$bl_descr = quote_smart(csvfield('DESC'), true);
				
				$bl_cost = 1.0 * csvfield('COST');
				
				$z_s_invs = "'" . csvfield('S_INVS') . "'";
				
				$bl_letter_dt = (csvfield('LETDATE', 'uk') ? ("'" . csvfield('LETDATE', 'uk') . "'") : 'NULL');
				
				$z_clid = "'" . csvfield('CLID') . "'";
				
				$z_nsecs = ((csvfield('NSECS') == '') ? 'NULL' : (1 * csvfield('NSECS')));
				
				$bl_lpos = ((csvfield('LPOS') == '') ? 'NULL' : (1 * csvfield('LPOS')));
				
				if (0 < $inv_num)
				{
					$invoice_id = invoice_id_from_docno($inv_num, 'T', $z_doctype, 'T');
					if ($invoice_id == 0)
					{
						# It is possible that an invoice was imported from the old Trace system but was considered to
						# be a Collect invoice (e.g. DOCNO 123248, July 2005).
						# So look now for a Collect invoice imported from the old Traces system.
						# This is possibly only the case for T/C jobs.
						$invoice_id = invoice_id_from_docno($inv_num, 'C', $z_doctype, 'T');
					}
					if ($invoice_id == 0)
					{
						dlog("*=* (Sys=$sys) Invoice not found from INVNO/CREDNO/DOCTYPE \"$invno\"/\"$credno\"/\"$z_doctype\" - skipping record (record $input_count)<br>" .
								"Error: $invoice_error<br>" .
								"col_nums: " . print_r($col_nums,1) . "<br>" .
								"record: " . print_r($one_record,1));
						$input_count++;
						$inv_bad++;
						continue; # Abandon this data record, move to next one
					}
				}
				else
					$invoice_id = 'NULL';
				
				$z_doctype = "'$z_doctype'";
				
				# --- SQL Insertions - INV_BILLING --------------------------------------------

				$fields = "INVOICE_ID,  INV_NUM,  JOB_ID,  BL_SYS, BL_SYS_IMP, BL_DESCR,  BL_COST,  ";
				$values = "$invoice_id, $inv_num, $job_id, 'T',    'T',        $bl_descr, $bl_cost, ";
				
				$fields .= "BL_LPOS,  BL_LETTER_DT,  IMPORTED, Z_CLID,  Z_DOCTYPE,  Z_S_INVS,  Z_NSECS";
				$values .= "$bl_lpos, $bl_letter_dt, $sqlTrue, $z_clid, $z_doctype, $z_s_invs, $z_nsecs";

				
				$sql = "INSERT INTO INV_BILLING ($fields) VALUES ($values)";
				if ($verbose)
					dprint($sql);
				$inv_billing_id = sql_execute($sql); # no need to audit
				if ($verbose)
					dprint("-> ID $inv_billing_id");
				$output_count++;
					
			} # else data record
			
			$input_count++;
			if ((0 < $stop_after) && ($stop_after <= $output_count))
			{
				dlog("Stopping after $stop_after bills");
				break; # while(true)
			}
			
		} # while(true)

		$time_taken = 1.0 * (time() - $start_time) / 60.0;
		$per_rec = 60.0 * $time_taken / (1.0 * $output_count / 1000.0);
		dlog("Imported $output_count bills from a Traces Billing file of " . ($input_count-1) . " bills (and 1 header record).<br>" .
				"Input file had min INVNO/CREDNO of $inv_min, max of $inv_max, and had $inv_zero_count zero XNOs.<br>" .
				"$seq_bad bills had a sequence number that couldn't be found in the database of jobs.<br>" .
				"$inv_bad bills had an invoice number that couldn't be found in the database of invoices.<br>" .
				"$doc_bad bills had a DOC/CREDIT number that couldn't be found in the database of invoices.<br>" .
				"Time taken: $time_taken mins ($per_rec secs per 1000 bills)");
		
		fclose($fp_csv);
	}
	else
		dlog("*=* (Sys=$sys) Failed to fopen(\"$dirname/$csv\",'r')");

} # import_billing()

function import_recp()
{
	# Before calling this function:
	#	- Export TRACE/RECEIPTS.DBF as RECEIPTS.csv using DBF Manager with default settings.
	#	- Export COLLECT/RECEIPTS.DBF as RECEIPTS.csv using DBF Manager with default settings.
	# Required:
	#	t/RECEIPTS.csv
	#	c/RECEIPTS.csv
	
	# Import 31/12/16:
	# Trace:
	#	import_2017_01_01_1041.log
	#	2017-01-01 10:41:23/1 *=* (Sys=t) Illegal CLID "420" - skipping record (record 1088)<br>col_nums: Array
	#	(
	#	    [CLID] => 0
	#	    [DATEREC] => 1
	#	    [AMOUNT] => 2
	#	    [TYPE] => 3
	#	    [COMPLETE] => 4
	#	    [RECP_NO] => 5
	#	)
	#	<br>record: Array
	#	(
	#	    [0] => 420
	#	    [1] => 01/11/1994
	#	    [2] => 146.88
	#	    [3] => R
	#	    [4] => Y
	#	    [5] => 0
	#	)
	#	2017-01-01 10:46:57/1 Imported 70637 receipts from a Traces file of 70638 receipts (and 1 header record).
	#	Input file had min DOCNO of 1, max of 71419, and had 2020 zero RECP_NO.
	#	1 receipts had client code that was not found in the database of clients.
	#	Time taken: 5.6666666666667 mins (4.8133414499483 secs per 1000 receipts)
	# Collect:
	#	import_2017_01_01_1048.log
	#	2017-01-01 10:49:30/1 Imported 8260 receipts from a Collections file of 8260 receipts (and 1 header record).
	#	Input file had min DOCNO of 1, max of 8797, and had 0 zero RECP_NO.
	#	0 receipts had client code that was not found in the database of clients.
	#	Time taken: 0.65 mins (4.7215496368039 secs per 1000 receipts)

	
	# Trace 27/09/16:
	#		Imported 69935 receipts from a Traces file of 69936 receipts (and 1 header record).
	#		Input file had min DOCNO of 1, max of 70697, and had 2020 zero RECP_NO.
	#		1 receipts had client code that was not found in the database of clients.
	#		Time taken: 5.5666666666667 mins (4.7758633016372 secs per 1000 receipts)
	
	# Collect 27/09/16:
	#		Imported 7949 receipts from a Collections file of 7949 receipts (and 1 header record).
	#		Input file had min DOCNO of 1, max of 8461, and had 0 zero RECP_NO.
	#		0 receipts had client code that was not found in the database of clients.
	#		Time taken: 0.63333333333333 mins (4.7804755315134 secs per 1000 receipts)
	
	global $col_nums; # for csvfield()
	global $input_count; # for csvfield()
	global $one_record; # for csvfield()
	global $sqlFalse;
	global $sqlTrue;
	global $tc;
		
	$enabled = false; #
	if (!$enabled)
	{
		dprint("This import function is disabled", true);
		return;
	}
	if (($tc != 'c') && ($tc != 't'))
	{
		dlog("*=* import_recp() - illegal tc \"$tc\"", true);
		return;
	}
	
	print "<h3>Importing Receipts (" . (($tc == 'c') ? "Collect" : "Trace") . " system)</h3>";
	
	$start_time = time();
	set_time_limit(60 * 60 * 8); # 8 hours
	
	init_recp($tc);
	
	$stop_after = 0;#
	$stop_margin = 0;
	$input_count = 0;
	$output_count = 0;
	
	$sys = $tc; # Either 't' or 'c'
	$dirname = "import-vilcol/{$sys}";
	if ($sys == 't')
	{
		$sys_txt = "Traces";
		$csv = "RECEIPTS.csv";
	}
	else
	{
		$sys_txt = "Collections";
		$csv = "RECEIPTS.csv";
	}
	
	$fp_csv = fopen("$dirname/$csv", 'r');
	if ($fp_csv)
	{
		#sql_encryption_preparation('x');
		
		$col_nums = array();
		
		$inv_min = 0;
		$inv_max = 0;
		$inv_zero_count = 0;
		$inv_zero_replacement = -101;
		$clid_bad = 0;
		
		$while_safety = 0; # to avoid infinite-looping
		while (true)
		{
			$print_buffer = array();
			$verbose = false;
			if ((0 < $stop_after) && ($output_count < $stop_after) && (($stop_after - $output_count) <= $stop_margin))
				$verbose = true;
			
			$while_safety++;
			if ($while_safety > 800000) # 800,000 receipts
			{
				dlog("*=* (Sys=$sys) Safety Ejection!");
				break; # while(true)
			}

			if ($verbose)
				$print_buffer[] = "=== Record $input_count ===";
				
			$one_record = fgetcsv($fp_csv);
			if ($input_count > 0) # skip header record
			{
				if ($one_record && (count($one_record) > 0)) # test for end-of-records
				{
					$inv_raw = csvfield('RECP_NO');
					$inv_num = 1 * $inv_raw;
					if ($inv_num > 0)
					{
						if (($inv_min == 0) || ($inv_num < $inv_min))
							$inv_min = $inv_num;
						if (($inv_max == 0) || ($inv_max < $inv_num))
							$inv_max = $inv_num;
					}
					else
					{
						$inv_num = $inv_zero_replacement--;
//						dlog("*=* (Sys=$sys) Non-numeric or illegal RECP_NO \"$inv_raw\" - skipping record (record $input_count)<br>" .
//								"col_nums: " . print_r($col_nums,1) . "<br>" .
//								"record: " . print_r($one_record,1));
						$inv_zero_count++;
//						$input_count++;
//						continue;
					}
				}
				else 
				{
					dlog("End of Receipts");
					break; # while(true)
				}
			}
				
			if ((0 < $stop_after) && ($stop_after <= $output_count))
			{
				$input_count++;
				continue;
			}
						
			if ($verbose)
				$print_buffer[] = (($input_count == 0) ? "Headers: " : "Receipt: ") . print_r($one_record,1);
			if ($input_count == 0)
			{
				# Header record
				foreach ($one_record as $num => $field)
					$col_nums[$field] = $num;
				if ($verbose)
					$print_buffer[] = "col_nums: " . print_r($col_nums,1);#
			}
			else
			{
				# Data record
				
//				$rc_num = 1 * csvfield('RECP_NO');
//				if ( ! ($rc_num > 0) )
//				{
//					dlog("*=* (Sys=$sys) Illegal RECP_NO \"" . csvfield('RECP_NO') . "\" - skipping record (record $input_count)<br>" .
//							"col_nums: " . print_r($col_nums,1) . "<br>" .
//							"record: " . print_r($one_record,1));
//					$input_count++;
//					continue; # Abandon this data record, move to next one
//				}

				if ($verbose)
				{
					foreach ($print_buffer as $pbline)
						dprint($pbline);
				}
				
				$client2_id = client_id_from_code(1 * csvfield('CLID'));
				if ( ! ($client2_id > 0) )
				{
					dlog("*=* (Sys=$sys) Illegal CLID \"" . csvfield('CLID') . "\" - skipping record (record $input_count)<br>" .
							"col_nums: " . print_r($col_nums,1) . "<br>" .
							"record: " . print_r($one_record,1));
					$input_count++;
					$clid_bad++;
					continue; # Abandon this data record, move to next one
				}
				
				$rc_dt = ((csvfield('DATEREC', 'uk') == '') ? 'NULL' : ("'" . csvfield('DATEREC', 'uk') . "'"));
				
				$rc_amount = 1.0 * csvfield('AMOUNT');

				$rc_adjust = 'NULL';
				if (strtolower(csvfield('TYPE')) == 'r')
					$rc_adjust = $sqlFalse;
				elseif (strtolower(csvfield('TYPE')) == 'a')
					$rc_adjust = $sqlTrue;
				else
				{
					dlog("*=* (Sys=$sys) Illegal TYPE \"" . csvfield('TYPE') . "\" - skipping record (record $input_count)<br>" .
							"col_nums: " . print_r($col_nums,1) . "<br>" .
							"record: " . print_r($one_record,1));
					$input_count++;
					continue; # Abandon this data record, move to next one
				}
				
				$z_complete = "'" . csvfield('COMPLETE') . "'";
				
				$rc_sys_imp = "'" . strtoupper($tc) . "'";
				
				
				# --- SQL Insertions - INV_RECP --------------------------------------------

				$fields = "RC_NUM,   CLIENT2_ID,  RC_DT,  RC_AMOUNT,  RC_ADJUST,  IMPORTED, RC_SYS_IMP,  Z_COMPLETE";
				$values = "$inv_num, $client2_id, $rc_dt, $rc_amount, $rc_adjust, $sqlTrue, $rc_sys_imp, $z_complete";
				
				$sql = "INSERT INTO INV_RECP ($fields) VALUES ($values)";
				if ($verbose)
					dprint($sql);
				$inv_recp_id = sql_execute($sql); # no need to audit
				if ($verbose)
					dprint("-> ID $inv_recp_id");
				$output_count++;
					
			} # else data record
			
			$input_count++;
			if ((0 < $stop_after) && ($stop_after <= $output_count))
			{
				dlog("Stopping after $stop_after receipts");
				#break; # while(true)
			}
			
		} # while(true)

		$time_taken = 1.0 * (time() - $start_time) / 60.0;
		$per_rec = 60.0 * $time_taken / (1.0 * $output_count / 1000.0);
		dlog("Imported $output_count receipts from a $sys_txt file of " . ($input_count-1) . " receipts (and 1 header record).<br>" .
				"Input file had min DOCNO of $inv_min, max of $inv_max, and had $inv_zero_count zero RECP_NO.<br>" .
				"$clid_bad receipts had client code that was not found in the database of clients.<br>" .
				"Time taken: $time_taken mins ($per_rec secs per 1000 receipts)");
		
		fclose($fp_csv);
	}
	else
		dlog("*=* (Sys=$sys) Failed to fopen(\"$dirname/$csv\",'r')");

} # import_recp()

function init_alloc($tc)
{
	$sql = "DELETE FROM INV_ALLOC WHERE (AL_SYS_IMP='" . strtoupper($tc) . "')";
	dprint((strlen($sql) > 100) ? (substr($sql, 0, 100) . "...") : $sql);
	sql_execute($sql); # no need to audit
	
} # init_alloc()

function import_alloc()
{
	# Before calling this function:
	#	- Export TRACE/R_XREF.DBF as R_XREF.csv using DBF Manager with default settings.
	#	- Export COLLECT/R_XREF.DBF as R_XREF.csv using DBF Manager with default settings.
	# Required:
	#	t/R_XREF.csv
	#	c/R_XREF.csv
	
	# Import 31/12/16:
	#	Trace:
	#	import_2017_01_01_1050.log
	#	2017-01-01 10:50:55/1 *=* (Sys=t) Receipt not found from RECP_NO "1352" - skipping record (record 2881)<br>Error R/T: No receipts found<br>Error A/T: No adjustments found<br>Error R/C: No receipts found<br>Error A/C: No adjustments found<br>col_nums: Array
//		(
//			[DOCNO] => 0
//			[DOCTYPE] => 1
//			[RECP_NO] => 2
//			[AMOUNT] => 3
//			[DOCAMOUNT] => 4
//		)
//		<br>record: Array
//		(
//			[0] => 16436
//			[1] => I
//			[2] => 1352
//			[3] => 35.25
//			[4] => 35.25
//		)
//		2017-01-01 10:50:55/1 *=* (Sys=t) Receipt not found from RECP_NO "1352" - skipping record (record 2882)<br>Error R/T: No receipts found<br>Error A/T: No adjustments found<br>Error R/C: No receipts found<br>Error A/C: No adjustments found<br>col_nums: Array
//		(
//			[DOCNO] => 0
//			[DOCTYPE] => 1
//			[RECP_NO] => 2
//			[AMOUNT] => 3
//			[DOCAMOUNT] => 4
//		)
//		<br>record: Array
//		(
//			[0] => 608
//			[1] => C
//			[2] => 1352
//			[3] => -35.25
//			[4] => -35.25
//		)
//		2017-01-01 10:51:52/1 *=* (Sys=t) Receipt not found from RECP_NO "4135" - skipping record (record 8423)<br>Error R/T: No receipts found<br>Error A/T: No adjustments found<br>Error R/C: No receipts found<br>Error A/C: No adjustments found<br>col_nums: Array
//		(
//			[DOCNO] => 0
//			[DOCTYPE] => 1
//			[RECP_NO] => 2
//			[AMOUNT] => 3
//			[DOCAMOUNT] => 4
//		)
//		<br>record: Array
//		(
//			[0] => 20420
//			[1] => I
//			[2] => 4135
//			[3] => 23.5
//			[4] => 23.5
//		)
//		2017-01-01 10:51:52/1 *=* (Sys=t) Receipt not found from RECP_NO "4135" - skipping record (record 8424)<br>Error R/T: No receipts found<br>Error A/T: No adjustments found<br>Error R/C: No receipts found<br>Error A/C: No adjustments found<br>col_nums: Array
//		(
//			[DOCNO] => 0
//			[DOCTYPE] => 1
//			[RECP_NO] => 2
//			[AMOUNT] => 3
//			[DOCAMOUNT] => 4
//		)
//		<br>record: Array
//		(
//			[0] => 819
//			[1] => C
//			[2] => 4135
//			[3] => -23.5
//			[4] => -23.5
//		)
//		2017-01-01 10:51:52/1 *=* (Sys=t) Receipt not found from RECP_NO "4159" - skipping record (record 8448)<br>Error R/T: No receipts found<br>Error A/T: No adjustments found<br>Error R/C: No receipts found<br>Error A/C: No adjustments found<br>col_nums: Array
//		(
//			[DOCNO] => 0
//			[DOCTYPE] => 1
//			[RECP_NO] => 2
//			[AMOUNT] => 3
//			[DOCAMOUNT] => 4
//		)
//		<br>record: Array
//		(
//			[0] => 22100
//			[1] => I
//			[2] => 4159
//			[3] => 29.38
//			[4] => 29.38
//		)
//		2017-01-01 10:51:52/1 *=* (Sys=t) Receipt not found from RECP_NO "4159" - skipping record (record 8449)<br>Error R/T: No receipts found<br>Error A/T: No adjustments found<br>Error R/C: No receipts found<br>Error A/C: No adjustments found<br>col_nums: Array
//		(
//			[DOCNO] => 0
//			[DOCTYPE] => 1
//			[RECP_NO] => 2
//			[AMOUNT] => 3
//			[DOCAMOUNT] => 4
//		)
//		<br>record: Array
//		(
//			[0] => 931
//			[1] => C
//			[2] => 4159
//			[3] => -29.38
//			[4] => -29.38
//		)
//		2017-01-01 10:52:01/1 *=* (Sys=t) Receipt not found from RECP_NO "4480" - skipping record (record 9165)<br>Error R/T: No receipts found<br>Error A/T: No adjustments found<br>Error R/C: No receipts found<br>Error A/C: No adjustments found<br>col_nums: Array
//		(
//			[DOCNO] => 0
//			[DOCTYPE] => 1
//			[RECP_NO] => 2
//			[AMOUNT] => 3
//			[DOCAMOUNT] => 4
//		)
//		<br>record: Array
//		(
//			[0] => 21793
//			[1] => I
//			[2] => 4480
//			[3] => 32.31
//			[4] => 32.31
//		)
//		2017-01-01 10:52:01/1 *=* (Sys=t) Receipt not found from RECP_NO "4480" - skipping record (record 9166)<br>Error R/T: No receipts found<br>Error A/T: No adjustments found<br>Error R/C: No receipts found<br>Error A/C: No adjustments found<br>col_nums: Array
//		(
//			[DOCNO] => 0
//			[DOCTYPE] => 1
//			[RECP_NO] => 2
//			[AMOUNT] => 3
//			[DOCAMOUNT] => 4
//		)
//		<br>record: Array
//		(
//			[0] => 924
//			[1] => C
//			[2] => 4480
//			[3] => -32.31
//			[4] => -32.31
//		)
//		2017-01-01 10:52:01/1 *=* (Sys=t) Receipt not found from RECP_NO "4481" - skipping record (record 9167)<br>Error R/T: No receipts found<br>Error A/T: No adjustments found<br>Error R/C: No receipts found<br>Error A/C: No adjustments found<br>col_nums: Array
//		(
//			[DOCNO] => 0
//			[DOCTYPE] => 1
//			[RECP_NO] => 2
//			[AMOUNT] => 3
//			[DOCAMOUNT] => 4
//		)
//		<br>record: Array
//		(
//			[0] => 18478
//			[1] => I
//			[2] => 4481
//			[3] => 32.31
//			[4] => 32.31
//		)
//		2017-01-01 10:52:01/1 *=* (Sys=t) Receipt not found from RECP_NO "4481" - skipping record (record 9168)<br>Error R/T: No receipts found<br>Error A/T: No adjustments found<br>Error R/C: No receipts found<br>Error A/C: No adjustments found<br>col_nums: Array
//		(
//			[DOCNO] => 0
//			[DOCTYPE] => 1
//			[RECP_NO] => 2
//			[AMOUNT] => 3
//			[DOCAMOUNT] => 4
//		)
//		<br>record: Array
//		(
//			[0] => 687
//			[1] => C
//			[2] => 4481
//			[3] => -32.31
//			[4] => -32.31
//		)
//		2017-01-01 11:20:57/1 Imported 162926 allocs from a Traces file of 170854 allocs (and 1 header record).
//		Input file had 0 allocs with a bad DOCTYPE.
//		Input file had 5 allocs with a bad invoice number.
//		Input file had 7923 allocs with a bad receipt number.
//		Input file had 0 allocs with bad invoice and receipt numbers.
//		Time taken: 30.566666666667 mins (11.256644120644 secs per 1000 invoices)
	# Collect:
	#	import_2017_01_01_1127.log
	#	2017-01-01 11:29:03/1 *=* (Sys=c) Receipt not found from RECP_NO "4135" - skipping record (record 8284)<br>Error R/C: No receipts found<br>Error A/C: No adjustments found<br>Error R/T: No receipts found<br>Error A/T: No adjustments found<br>col_nums: Array
//		(
//			[DOCNO] => 0
//			[DOCTYPE] => 1
//			[RECP_NO] => 2
//			[AMOUNT] => 3
//			[DOCAMOUNT] => 4
//		)
//		<br>record: Array
//		(
//			[0] => 108777
//			[1] => I
//			[2] => 4135
//			[3] => 258.43
//			[4] => 258.43
//		)
//		2017-01-01 11:29:03/1 *=* (Sys=c) Receipt not found from RECP_NO "4135" - skipping record (record 8285)<br>Error R/C: No receipts found<br>Error A/C: No adjustments found<br>Error R/T: No receipts found<br>Error A/T: No adjustments found<br>col_nums: Array
//		(
//			[DOCNO] => 0
//			[DOCTYPE] => 1
//			[RECP_NO] => 2
//			[AMOUNT] => 3
//			[DOCAMOUNT] => 4
//		)
//		<br>record: Array
//		(
//			[0] => 49
//			[1] => C
//			[2] => 4135
//			[3] => -258.43
//			[4] => -258.43
//		)
//		2017-01-01 11:29:04/1 *=* (Sys=c) Receipt not found from RECP_NO "4159" - skipping record (record 8344)<br>Error R/C: No receipts found<br>Error A/C: No adjustments found<br>Error R/T: No receipts found<br>Error A/T: No adjustments found<br>col_nums: Array
//		(
//			[DOCNO] => 0
//			[DOCTYPE] => 1
//			[RECP_NO] => 2
//			[AMOUNT] => 3
//			[DOCAMOUNT] => 4
//		)
//		<br>record: Array
//		(
//			[0] => 108828
//			[1] => I
//			[2] => 4159
//			[3] => 65.7
//			[4] => 65.7
//		)
//		2017-01-01 11:29:04/1 *=* (Sys=c) Receipt not found from RECP_NO "4159" - skipping record (record 8345)<br>Error R/C: No receipts found<br>Error A/C: No adjustments found<br>Error R/T: No receipts found<br>Error A/T: No adjustments found<br>col_nums: Array
//		(
//			[DOCNO] => 0
//			[DOCTYPE] => 1
//			[RECP_NO] => 2
//			[AMOUNT] => 3
//			[DOCAMOUNT] => 4
//		)
//		<br>record: Array
//		(
//			[0] => 55
//			[1] => C
//			[2] => 4159
//			[3] => -65.7
//			[4] => -65.7
//		)
//		2017-01-01 11:29:12/1 *=* (Sys=c) Receipt not found from RECP_NO "4480" - skipping record (record 8774)<br>Error R/C: No receipts found<br>Error A/C: No adjustments found<br>Error R/T: No receipts found<br>Error A/T: No adjustments found<br>col_nums: Array
//		(
//			[DOCNO] => 0
//			[DOCTYPE] => 1
//			[RECP_NO] => 2
//			[AMOUNT] => 3
//			[DOCAMOUNT] => 4
//		)
//		<br>record: Array
//		(
//			[0] => 109353
//			[1] => I
//			[2] => 4480
//			[3] => 32.04
//			[4] => 32.04
//		)
//		2017-01-01 11:29:12/1 *=* (Sys=c) Receipt not found from RECP_NO "4480" - skipping record (record 8775)<br>Error R/C: No receipts found<br>Error A/C: No adjustments found<br>Error R/T: No receipts found<br>Error A/T: No adjustments found<br>col_nums: Array
//		(
//			[DOCNO] => 0
//			[DOCTYPE] => 1
//			[RECP_NO] => 2
//			[AMOUNT] => 3
//			[DOCAMOUNT] => 4
//		)
//		<br>record: Array
//		(
//			[0] => 83
//			[1] => C
//			[2] => 4480
//			[3] => -32.04
//			[4] => -32.04
//		)
//		2017-01-01 11:29:12/1 *=* (Sys=c) Receipt not found from RECP_NO "4481" - skipping record (record 8776)<br>Error R/C: No receipts found<br>Error A/C: No adjustments found<br>Error R/T: No receipts found<br>Error A/T: No adjustments found<br>col_nums: Array
//		(
//			[DOCNO] => 0
//			[DOCTYPE] => 1
//			[RECP_NO] => 2
//			[AMOUNT] => 3
//			[DOCAMOUNT] => 4
//		)
//		<br>record: Array
//		(
//			[0] => 109351
//			[1] => I
//			[2] => 4481
//			[3] => 985.24
//			[4] => 985.24
//		)
//		2017-01-01 11:29:12/1 *=* (Sys=c) Receipt not found from RECP_NO "4481" - skipping record (record 8777)<br>Error R/C: No receipts found<br>Error A/C: No adjustments found<br>Error R/T: No receipts found<br>Error A/T: No adjustments found<br>col_nums: Array
//		(
//			[DOCNO] => 0
//			[DOCTYPE] => 1
//			[RECP_NO] => 2
//			[AMOUNT] => 3
//			[DOCAMOUNT] => 4
//		)
//		<br>record: Array
//		(
//			[0] => 84
//			[1] => C
//			[2] => 4481
//			[3] => -985.24
//			[4] => -985.24
//		)
//		2017-01-01 11:29:28/1 *=* (Sys=c) Receipt not found from RECP_NO "5147" - skipping record (record 10196)<br>Error R/C: No receipts found<br>Error A/C: No adjustments found<br>Error R/T: No receipts found<br>Error A/T: No adjustments found<br>col_nums: Array
//		(
//			[DOCNO] => 0
//			[DOCTYPE] => 1
//			[RECP_NO] => 2
//			[AMOUNT] => 3
//			[DOCAMOUNT] => 4
//		)
//		<br>record: Array
//		(
//			[0] => 110415
//			[1] => I
//			[2] => 5147
//			[3] => 12
//			[4] => 12
//		)
//		2017-01-01 11:29:28/1 *=* (Sys=c) Receipt not found from RECP_NO "5147" - skipping record (record 10197)<br>Error R/C: No receipts found<br>Error A/C: No adjustments found<br>Error R/T: No receipts found<br>Error A/T: No adjustments found<br>col_nums: Array
//		(
//			[DOCNO] => 0
//			[DOCTYPE] => 1
//			[RECP_NO] => 2
//			[AMOUNT] => 3
//			[DOCAMOUNT] => 4
//		)
//		<br>record: Array
//		(
//			[0] => 110446
//			[1] => I
//			[2] => 5147
//			[3] => 26.4
//			[4] => 26.4
//		)
//		2017-01-01 11:30:36/1 Imported 15932 allocs from a Collections file of 15954 allocs (and 1 header record).
//		Input file had 0 allocs with a bad DOCTYPE.
//		Input file had 0 allocs with a bad invoice number.
//		Input file had 22 allocs with a bad receipt number.
//		Input file had 0 allocs with bad invoice and receipt numbers.
//		Time taken: 2.9 mins (10.921416018077 secs per 1000 invoices)
	
	
	# Trace 27/09/16:
	#		2016-09-28 00:56:37/1 Imported 30296 allocs from a Traces file of 169327 allocs (and 1 header record).
	#		Input file had 0 allocs with a bad DOCTYPE.
	#		Input file had 131135 allocs with a bad invoice number.
	#		Input file had 1654 allocs with a bad receipt number.
	#		Input file had 6242 allocs with bad invoice and receipt numbers.
	#		Time taken: 41.566666666667 mins (82.321098494851 secs per 1000 invoices)
	
	# Collect 27/09/16:
	#		Imported 15428 allocs from a Collections file of 15450 allocs (and 1 header record).
	#		Input file had 0 allocs with a bad DOCTYPE.
	#		Input file had 0 allocs with a bad invoice number.
	#		Input file had 22 allocs with a bad receipt number.
	#		Input file had 0 allocs with bad invoice and receipt numbers.
	#		Time taken: 2.4833333333333 mins (9.6577651024112 secs per 1000 invoices)
	
	global $col_nums; # for csvfield()
	global $invoice_error;
	global $input_count; # for csvfield()
	global $one_record; # for csvfield()
	global $sqlTrue;
	global $tc;
	
	$enabled = false; #
	if (!$enabled)
	{
		dprint("This import function is disabled", true);
		return;
	}
	if (($tc != 'c') && ($tc != 't'))
	{
		dlog("*=* import_alloc() - illegal tc \"$tc\"", true);
		return;
	}

	print "<h3>Importing R_XREF / Allocations (" . (($tc == 'c') ? "Collect" : "Trace") . " system)</h3>";
	
	$start_time = time();
	set_time_limit(60 * 60 * 8); # 8 hours
	
	init_alloc($tc);
	
	$stop_after = 0;#
	$stop_margin = 0;
	$input_count = 0;
	$output_count = 0;
	$bad_type = 0;
	$bad_inv = 0;
	$bad_recp = 0;
	$bad_both = 0;
	
	$sys = $tc; # Either 't' or 'c' from this point
	$dirname = "import-vilcol/{$sys}";
	if ($sys == 't')
	{
		$sys_txt = "Traces";
		$csv = "R_XREF.csv";
		$SYS = 'T';
		$OTHER_SYS = 'C';
	}
	else
	{
		$sys_txt = "Collections";
		$csv = "R_XREF.csv";
		$SYS = 'C';
		$OTHER_SYS = 'T';
	}
	
	$fp_csv = fopen("$dirname/$csv", 'r');
	if ($fp_csv)
	{
		#sql_encryption_preparation('x');
		
		$col_nums = array();
		
		$while_safety = 0; # to avoid infinite-looping
		while (true)
		{
			$print_buffer = array();
			$verbose = false;
			if ((0 < $stop_after) && ($output_count <= $stop_after) && (($stop_after - $output_count) <= $stop_margin))
				$verbose = true;
			#if ((150 <= $input_count) && ($input_count < 160))#
			#	$verbose = true;
			
			$while_safety++;
			if ($while_safety > 800000) # 800,000 allocs
			{
				if ($verbose)
				{
					foreach ($print_buffer as $pbline)
						dprint($pbline);
					$print_buffer = array();
				}
				dlog("*=* (Sys=$sys) Safety Ejection!");
				break; # while(true)
			}

			if ($verbose)
				$print_buffer[] = "=== Record $input_count ===";
				
			$one_record = fgetcsv($fp_csv);
			if ($input_count > 0) # skip header record
			{
				if ( ! ($one_record && (count($one_record) > 0)) ) # test for end-of-records
				{
					if ($verbose)
					{
						foreach ($print_buffer as $pbline)
							dprint($pbline);
						$print_buffer = array();
					}
					dlog("End of XREF");
					break; # while(true)
				}
			}
				
			if ((0 < $stop_after) && ($stop_after <= $output_count))
			{
				$input_count++;
				continue;
			}
						
			if ($verbose)
				$print_buffer[] = (($input_count == 0) ? "Headers: " : "Alloc: ") . print_r($one_record,1);
			elseif ((0 < $stop_after) && ($input_count == 0))
				dprint("Headers: " . print_r($one_record,1));
			if ($input_count == 0)
			{
				# Header record
				foreach ($one_record as $num => $field)
					$col_nums[$field] = $num;
				if ($verbose)
					$print_buffer[] = "col_nums: " . print_r($col_nums,1);#
			}
			else
			{
				# Data record
				
				$z_doctype = strtoupper(csvfield('DOCTYPE'));
				if ( ! (($z_doctype == 'I') || ($z_doctype == 'C')) )
				{
					if ($verbose)
					{
						foreach ($print_buffer as $pbline)
							dprint($pbline);
						$print_buffer = array();
					}
					dlog("*=* (Sys=$sys) Illegal DOCTYPE \"$z_doctype\" - skipping record (record $input_count)<br>" .
							"col_nums: " . print_r($col_nums,1) . "<br>" .
							"record: " . print_r($one_record,1));
					$input_count++;
					$bad_type++;
					continue; # Abandon this data record, move to next one
				}

				$is_bad_inv = false;
				$is_bad_recp = false;
				
				$inv_num = 1 * csvfield('DOCNO');
				$invoice_id = invoice_id_from_docno($inv_num, $SYS, $z_doctype, $SYS);
				$error_i1 = $invoice_error;
				if ($invoice_id == 0)
				{
					# Try a General invoice instead
					$invoice_id = invoice_id_from_docno($inv_num, 'G', $z_doctype, $SYS);
					$error_i2 = $invoice_error;
				}
				if (($invoice_id == 0) && ($sys == 't'))
				{
					# It is possible that an invoice was imported from the old Trace system but was considered to
					# be a Collect invoice (e.g. DOCNO 123248, July 2005).
					# So look now for a Collect invoice imported from the old Traces system.
					# This is possibly only the case for T/C jobs.
					$invoice_id = invoice_id_from_docno($inv_num, 'C', $z_doctype, 'T');
					$error_i3 = $invoice_error;
				}
				if ($invoice_id == 0)
					$is_bad_inv = true;
				
				$recp_num = 1 * csvfield('RECP_NO');
				$inv_recp_id = receipt_id_from_docno($recp_num, 'R', $SYS);
				$error_r1 = $invoice_error;
				if ($inv_recp_id == 0)
				{
					$inv_recp_id = receipt_id_from_docno($recp_num, 'A', $SYS);
					$error_r2 = $invoice_error;
					if ($inv_recp_id == 0)
					{
						$inv_recp_id = receipt_id_from_docno($recp_num, 'R', $OTHER_SYS);
						$error_r3 = $invoice_error;
						if ($inv_recp_id == 0)
						{
							$inv_recp_id = receipt_id_from_docno($recp_num, 'A', $OTHER_SYS);
							$error_r4 = $invoice_error;
						}
					}
				}
				if ($inv_recp_id == 0)
					$is_bad_recp = true;
				
				if ($is_bad_inv || $is_bad_recp)
				{
					if (($bad_inv + $bad_recp + $bad_both) < 10)
					{
						if ($verbose)
						{
							foreach ($print_buffer as $pbline)
								dprint($pbline);
							$print_buffer = array();
						}
						if ($is_bad_inv)
							dlog("*=* (Sys=$sys) Invoice not found from DOCNO \"" . csvfield('DOCNO') . "\" - skipping record (record $input_count)<br>" .
								"Error 1: $error_i1<br>" .
								"Error 2: $error_i2<br>" .
								"Error 3: $error_i3<br>" .
								"col_nums: " . print_r($col_nums,1) . "<br>" .
								"record: " . print_r($one_record,1));
						if ($is_bad_recp)
						{
							dlog("*=* (Sys=$sys) Receipt not found from RECP_NO \"" . csvfield('RECP_NO') . "\" - skipping record (record $input_count)<br>" .
								"Error R/$SYS: $error_r1<br>" .
								"Error A/$SYS: $error_r2<br>" .
								"Error R/$OTHER_SYS: $error_r3<br>" .
								"Error A/$OTHER_SYS: $error_r4<br>" .
								"col_nums: " . print_r($col_nums,1) . "<br>" .
								"record: " . print_r($one_record,1));
						}
					}
					$input_count++;
					if ($is_bad_inv)
					{
						if ($is_bad_recp)
							$bad_both++;
						else
							$bad_inv++;
					}
					else
						$bad_recp++;
					continue; # Abandon this data record, move to next one
				}
				
				$al_amount = 1.0 * csvfield('AMOUNT');
				if ($al_amount == 0.0)
				{
					if ($verbose)
					{
						foreach ($print_buffer as $pbline)
							dprint($pbline);
						$print_buffer = array();
					}
					dlog("*=* (Sys=$sys) Allocation has zero amount, DOCNO \"" . csvfield('DOCNO') . "\", " .
									"RECP_NO \"" . csvfield('RECP_NO') . "\" - skipping record (record $input_count)<br>" .
							"col_nums: " . print_r($col_nums,1) . "<br>" .
							"record: " . print_r($one_record,1));
				}
				
				$z_doctype = "'$z_doctype'";
				$z_docamount = 1.0 * csvfield('DOCAMOUNT');
				
				if ($verbose)
				{
					foreach ($print_buffer as $pbline)
						dprint($pbline);
					$print_buffer = array();
				}
				
				# --- SQL Insertions - INV_ALLOC --------------------------------------------

				$fields = "INVOICE_ID,  INV_RECP_ID,  AL_AMOUNT,  IMPORTED, AL_SYS_IMP, Z_DOCTYPE,  Z_DOCAMOUNT";
				$values = "$invoice_id, $inv_recp_id, $al_amount, $sqlTrue, '$SYS',     $z_doctype, $z_docamount";
				
				$sql = "INSERT INTO INV_ALLOC ($fields) VALUES ($values)";
				if ($verbose)
					dprint($sql);
				$invoice_id = sql_execute($sql); # no need to audit
				if ($verbose)
					dprint("-> ID $invoice_id");
				$output_count++;
					
			} # else data record
			
			$input_count++;
			if ((0 < $stop_after) && ($stop_after <= $output_count))
			{
				dlog("Stopping after $stop_after invoices");
				#break; # while(true)
			}
			#if (($input_count % 100) == 0)
			#	dlog("Read $input_count records so far...");#
			
		} # while(true)

		$time_taken = 1.0 * (time() - $start_time) / 60.0;
		$per_rec = 60.0 * $time_taken / (1.0 * $output_count / 1000.0);
		dlog("Imported $output_count allocs from a $sys_txt file of " . ($input_count-1) . " allocs (and 1 header record).<br>" .
				"Input file had $bad_type allocs with a bad DOCTYPE.<br>" .
				"Input file had $bad_inv allocs with a bad invoice number.<br>" .
				"Input file had $bad_recp allocs with a bad receipt number.<br>" .
				"Input file had $bad_both allocs with bad invoice and receipt numbers.<br>" .
				"Time taken: $time_taken mins ($per_rec secs per 1000 invoices)");
		
		fclose($fp_csv);
	}
	else
		dlog("*=* (Sys=$sys) Failed to fopen(\"$dirname/$csv\",'r')");
	
} # import_alloc()

function print_dump()
{
	global $print_buffer; # for print_dump()
	global $verbose; # for print_dump()
	
	if ($verbose)
	{
		foreach ($print_buffer as $pbline)
			dlog($pbline);
		$print_buffer = array();
	}
}

function payment_method_from_text_local($type)
{
	$type = strtolower($type);
	if (strpos($type, 'allpay') !== false)
		return 1;
	if ((strpos($type, 'giro') !== false) && (strpos($type, 'standing') !== false))
		return 2;
	if ($type == 'cash')
		return 3;
	if ($type == 'cheque')
		return 4;
	if ((strpos($type, 'credit') !== false) && (strpos($type, 'debit') !== false))
		return 5;
	if ($type == 'dma')
		return 6;
	if ($type == 'door')
		return 7;
	if ($type == 'gpb')
		return 8;
	if ((strpos($type, 'balance') !== false) && (strpos($type, 'correction') !== false))
		return 9;
	if ($type == 'po')
		return 10;
	if (($type == '1') || ($type == '2') || ($type == '3') || ($type == '4') || ($type == '5') || 
		($type == '6') || ($type == '7') || ($type == '8') || ($type == '9'))
		return 10 + (1 * $type);
	if ($type == '43')
		return 20;
	return 0;
}

function import_payments()
{
	# Collect only: PAYMENTS.DBF
	
	# Before calling this function:
	#	- Export COLLECT/PAYMENTS.DBF as PAYMENTS.csv using DBF Manager with default settings.
	# Required:
	#	c/PAYMENTS.csv

	# Import 31/12/16:
	#	import_2017_01_01_1132.log
	#	2017-01-01 11:32:35/1 *=* JOB_ID not found from SEQUENCE "90000001" - skipping record (record 1)<br>col_nums: Array
//		(
//			[SEQUENCE] => 0
//			[DATE] => 1
//			[ROUTE] => 2
//			[AMOUNT] => 3
//			[INVNO] => 4
//			[CLID] => 5
//			[PERCENT] => 6
//			[BOUNCED] => 7
//			[TYPE] => 8
//		)
//		<br>record: Array
//		(
//			[0] => 90000001
//			[1] => 25/11/2004
//			[2] => To us
//			[3] => 5
//			[4] => 999999
//			[5] => 1580
//			[6] => 41
//			[7] => N
//			[8] => Cash
//		)
//		2017-01-01 11:32:36/1 *=* DATE "28/10/2091" converted from "2091-10-28" to "1991-10-28" (record 66)<br>col_nums: Array
//		(
//			[SEQUENCE] => 0
//			[DATE] => 1
//			[ROUTE] => 2
//			[AMOUNT] => 3
//			[INVNO] => 4
//			[CLID] => 5
//			[PERCENT] => 6
//			[BOUNCED] => 7
//			[TYPE] => 8
//		)
//		<br>record: Array
//		(
//			[0] => 90000006
//			[1] => 28/10/2091
//			[2] => To us
//			[3] => 5
//			[4] => 999999
//			[5] => 362
//			[6] => 40
//			[7] => N
//			[8] => Cheque
//		)
//		2017-01-01 11:32:36/1 *=* DATE "22/11/2091" converted from "2091-11-22" to "1991-11-22" (record 67)<br>col_nums: Array
//		(
//			[SEQUENCE] => 0
//			[DATE] => 1
//			[ROUTE] => 2
//			[AMOUNT] => 3
//			[INVNO] => 4
//			[CLID] => 5
//			[PERCENT] => 6
//			[BOUNCED] => 7
//			[TYPE] => 8
//		)
//		<br>record: Array
//		(
//			[0] => 90000006
//			[1] => 22/11/2091
//			[2] => To us
//			[3] => 10
//			[4] => 999999
//			[5] => 362
//			[6] => 40
//			[7] => N
//			[8] => Cheque
//		)
//		2017-01-01 11:32:37/1 *=* DATE "01/01/1980" converted from "1980-01-01" to "" (record 4969)<br>col_nums: Array
//		(
//			[SEQUENCE] => 0
//			[DATE] => 1
//			[ROUTE] => 2
//			[AMOUNT] => 3
//			[INVNO] => 4
//			[CLID] => 5
//			[PERCENT] => 6
//			[BOUNCED] => 7
//			[TYPE] => 8
//		)
//		<br>record: Array
//		(
//			[0] => 90001972
//			[1] => 01/01/1980
//			[2] => To us
//			[3] => 24.56
//			[4] => 999999
//			[5] => 0
//			[6] => 0
//			[7] => N
//			[8] => Out of Balance Correction
//		)
//		2017-01-01 11:33:06/1 *=* DATE "01/01/1980" converted from "1980-01-01" to "" (record 84136)<br>col_nums: Array
//		(
//			[SEQUENCE] => 0
//			[DATE] => 1
//			[ROUTE] => 2
//			[AMOUNT] => 3
//			[INVNO] => 4
//			[CLID] => 5
//			[PERCENT] => 6
//			[BOUNCED] => 7
//			[TYPE] => 8
//		)
//		<br>record: Array
//		(
//			[0] => 90034328
//			[1] => 01/01/1980
//			[2] => To us
//			[3] => -14
//			[4] => 999999
//			[5] => 0
//			[6] => 0
//			[7] => N
//			[8] => Out of Balance Correction
//		)
//		2017-01-01 12:39:15/1 *=* PAYMENT_ROUTE not found from ROUTE "1" - (record 883445)<br>col_nums: Array
//		(
//			[SEQUENCE] => 0
//			[DATE] => 1
//			[ROUTE] => 2
//			[AMOUNT] => 3
//			[INVNO] => 4
//			[CLID] => 5
//			[PERCENT] => 6
//			[BOUNCED] => 7
//			[TYPE] => 8
//		)
//		<br>record: Array
//		(
//			[0] => 90476636
//			[1] => 23/02/2012
//			[2] => 1
//			[3] => 0
//			[4] => 0
//			[5] => 0
//			[6] => 0
//			[7] => N
//			[8] => 
//		)
//		2017-01-01 13:04:19/1 Imported 489731 payments from a file of 1040493 payments (and 1 header record).<br>
//		Input file had 550761 payments with a bad SEQUENCE (78886 sequences were unfound).<br>
//		Input file had 0 payments with a bad INVNO (0 invno were unfound).<br>
//		Input file had 1 payments with a bad ROUTE.<br>
//		Input file had 0 payments with a bad TYPE.<br>
//		$count_1980=1349.<br>
//		$count_2090=112033.<br>
//		Years:<br>
//				1990:        IN:456        OUT:0<br>
//				1991:        IN:4897        OUT:10<br>
//				1992:        IN:9633        OUT:16<br>
//				1993:        IN:17354        OUT:39<br>
//				1994:        IN:19257        OUT:59<br>
//				1995:        IN:15276        OUT:97<br>
//				1996:        IN:13157        OUT:83<br>
//				1997:        IN:10368        OUT:85<br>
//				1998:        IN:10889        OUT:72<br>
//				1999:        IN:11443        OUT:85<br>
//				2000:        IN:22974        OUT:185<br>
//				2001:        IN:47916        OUT:363<br>
//				2002:        IN:76871        OUT:3155<br>
//				2003:        IN:76534        OUT:8586<br>
//				2004:        IN:70534        OUT:14767<br>
//				2005:        IN:84536        OUT:22345<br>
//				2006:        IN:104734        OUT:41446<br>
//				2007:        IN:78547        OUT:51971<br>
//				2008:        IN:55027        OUT:45351<br>
//				2009:        IN:40201        OUT:31839<br>
//				2010:        IN:53721        OUT:53657<br>
//				2011:        IN:51602        OUT:51598<br>
//				2012:        IN:40504        OUT:40504<br>
//				2013:        IN:40957        OUT:40957<br>
//				2014:        IN:28359        OUT:28359<br>
//				2015:        IN:27613        OUT:27611<br>
//				2016:        IN:25783        OUT:25775<br>
//		Time taken: 91.733333333333 mins (11.238822945658 secs per 1000 invoices)
	
	
	# 17/11/16:
	#		Imported 491470 payments from a file of 1032377 payments (and 1 header record).
	#		Input file had 540906 payments with a bad SEQUENCE (78598 sequences were unfound).
	#		Input file had 0 payments with a bad INVNO (0 invno were unfound).
	#		Input file had 1 payments with a bad ROUTE.
	#		Input file had 0 payments with a bad TYPE.
	#		$count_1980=1349.
	#		$count_2090=112033.
	#		Years:
	#		2004: IN:70534 OUT:16068
	#		2000: IN:22974 OUT:298
	#		2005: IN:84536 OUT:23587
	#		2006: IN:104734 OUT:42604
	#		2007: IN:78547 OUT:53092
	#		2008: IN:55027 OUT:45878
	#		2009: IN:40201 OUT:31874
	#		2010: IN:53721 OUT:53666
	#		2002: IN:76871 OUT:4729
	#		1991: IN:4897 OUT:10
	#		1992: IN:9633 OUT:141
	#		1993: IN:17354 OUT:165
	#		1994: IN:19257 OUT:141
	#		1995: IN:15276 OUT:145
	#		1996: IN:13157 OUT:100
	#		1997: IN:10368 OUT:104
	#		1998: IN:10889 OUT:238
	#		2001: IN:47916 OUT:801
	#		1999: IN:11443 OUT:267
	#		1990: IN:456 OUT:0
	#		2003: IN:76534 OUT:10141
	#		2011: IN:51602 OUT:51600
	#		2012: IN:40504 OUT:40504
	#		2013: IN:40957 OUT:40957
	#		2014: IN:28359 OUT:28359
	#		2015: IN:27613 OUT:27611
	#		2016: IN:17665 OUT:17657
	#		2019: IN:2 OUT:2
	#		Time taken: 94.316666666667 mins (11.514436282988 secs per 1000 invoices)

	# 28/09/16:
	#		2016-09-28 02:26:23/1 Imported 491470 payments from a file of 1032377 payments (and 1 header record).
	#		Input file had 540906 payments with a bad SEQUENCE (78598 sequences were unfound).
	#		Input file had 0 payments with a bad INVNO (0 invno were unfound).
	#		Input file had 1 payments with a bad ROUTE.
	#		Input file had 0 payments with a bad TYPE.
	#		$count_1980=1349.
	#		$count_2090=112033.
	#		Time taken: 78.066666666667 mins (9.5305918977761 secs per 1000 invoices)
	
	global $col_nums; # for csvfield()
	global $crlf;
	global $id_ROUTE_cspent;
	global $id_ROUTE_direct;
	global $id_ROUTE_fwd;
	global $id_ROUTE_tous;
	global $input_count; # for csvfield()
	global $one_record; # for csvfield()
	global $print_buffer; # for print_dump()
	global $sqlFalse;
	global $sqlTrue;
	global $verbose; # for print_dump()
	
	$enabled = false; #
	if (!$enabled)
	{
		dprint("This import function is disabled", true);
		return;
	}
	
	print "<h3>Importing Collection Payments</h3>";
	
	$start_time = time();
	set_time_limit(60 * 60 * 8); # 8 hours
	
	init_payments(false);
	#init_payments(true);#

	$sys = 'c'; # collect

	$stop_after_i = 0;#
	$stop_after_o = 0;#
	$stop_margin = 0;
	$input_count = 0;
	$output_count = 0;
	$bad_seq = 0;
	$bad_route = 0;
	$bad_docno = 0;
	$bad_method = 0;
	$count_1980 = 0;
	$count_2090 = 0;
	
	$dirname = "import-vilcol/{$sys}";
	$csv = "PAYMENTS.csv";
	$fp_csv = fopen("$dirname/$csv", 'r');
	if ($fp_csv)
	{
		$col_nums = array();
		$seq_ignore = array();
		$inv_ignore = array();
		$year_counts = array();
		
		$while_safety = 0; # to avoid infinite-looping
		while (true)
		{
			$verbose = false;
			if (
					((0 < $stop_after_i) && ($input_count <= $stop_after_i) && (($stop_after_i - $input_count) < $stop_margin))
					||
					((0 < $stop_after_o) && ($output_count < $stop_after_o) && (($stop_after_o - $output_count) <= $stop_margin))
				)
				$verbose = true;
			$print_buffer = array();
			if ($verbose)
				$print_buffer[] = "Starting record, input_count=$input_count";
				
			$while_safety++;
			if ($while_safety > 2000000) # 2,000,000 payments
			{
				print_dump();
				dlog("*=* (Sys=$sys) Safety Ejection!");
				break; # while(true)
			}
			
			$one_record = fgetcsv($fp_csv);
			if ($verbose)
				$print_buffer[] = ((($input_count == 0) ? "Headers: " : "Phone: ") . print_r($one_record,1));#
			elseif (((0 < $stop_after_i) || (0 < $stop_after_o)) && ($input_count == 0))
				dprint("Headers: " . print_r($one_record,1));
			
			if ($input_count == 0)
			{
				foreach ($one_record as $num => $field)
					$col_nums[$field] = $num;
				if ($verbose)
					$print_buffer[] = ("col_nums: " . print_r($col_nums,1));#
				print_dump();
			}
			elseif ($one_record && (count($one_record) > 0))
			{
				
//				if ((1 * csvfield('SEQUENCE')) == 90717774) #
//					$verbose = true;
//				else
//					continue;
				
				#$sql = "UPDATE STUFF SET STUFF=" . quote_smart(print_r($one_record,1));#
				#sql_execute($sql); # no need to audit
				
				$skip_record = false;
				
				$year = 0;
				$temp = csvfield('DATE', 'uk');
				if ($temp != '')
				{
					$col_dt_rx = $temp;
					if ($col_dt_rx == '1980-01-01')
					{
						$count_1980++;
						$temp = $col_dt_rx;
						$col_dt_rx = '';
						if ($count_1980 < 3)
							dlog("*=* DATE \"" . csvfield('DATE') . "\" converted from \"$temp\" to \"$col_dt_rx\" (record $input_count)<br>" .
									"col_nums: " . print_r($col_nums,1) . "<br>" .
									"record: " . print_r($one_record,1));
					}
					elseif ('2090-01-01' < $col_dt_rx)
					{
						$count_2090++;
						$temp = $col_dt_rx;
						$col_dt_rx = str_replace('209', '199', $col_dt_rx);
						if ($count_2090 < 3)
							dlog("*=* DATE \"" . csvfield('DATE') . "\" converted from \"$temp\" to \"$col_dt_rx\" (record $input_count)<br>" .
									"col_nums: " . print_r($col_nums,1) . "<br>" .
									"record: " . print_r($one_record,1));
					}
				}
				else
					$col_dt_rx = '';
				if ($col_dt_rx != '')
				{
					$year = substr($col_dt_rx, 0, 4);
					#if ($input_count < 4)
					#	dprint("year=\"$year\"");
					$year = 1 * $year;
					if (!array_key_exists($year, $year_counts))
						$year_counts[$year] = array('IN' => 0, 'OUT' => 0);
					$year_counts[$year]['IN'] = $year_counts[$year]['IN'] + 1;
					$col_dt_rx = "'" . $col_dt_rx . "'";
				}
				else
					$col_dt_rx = 'NULL';
				$z_date = "'" . csvfield('DATE') . "'";

				$seq = 1 * csvfield('SEQUENCE');
				if (in_array($seq, $seq_ignore))
				{
					$bad_seq++;
					$skip_record = true;
				}
				if (!$skip_record)
				{
					$job_id = job_id_from_sequence($seq);
					if ( ! ($job_id > 0) )
					{
						if (count($seq_ignore) == 0)
						{
							print_dump();
							dlog("*=* JOB_ID not found from SEQUENCE \"" . csvfield('SEQUENCE') . "\" - skipping record (record $input_count)<br>" .
										"col_nums: " . print_r($col_nums,1) . "<br>" .
										"record: " . print_r($one_record,1));
						}
						$seq_ignore[] = $seq;
						$bad_seq++;
						$skip_record = true;
					}
				}
				if (!$skip_record)
				{
					$invno = 1 * csvfield('INVNO');
//					if (in_array($invno, $inv_ignore))
//					{
//						$bad_docno++;
//						$skip_record = true;
//					}
				}
				if (!$skip_record)
				{
					$invoice_id = invoice_id_from_docno($invno, 'C', 'I', 'C');
					if ( ! ($invoice_id > 0) )
					{
						$invoice_id = invoice_id_from_docno($invno, 'C', 'C', 'C');
						if ( ! ($invoice_id > 0) )
						{
//							#if (count($inv_ignore) == 0)
//							#{
//								print_dump();
//								dlog("*=* INVOICE_ID not found from INVNO \"" . csvfield('INVNO') . "\" - skipping record (record $input_count)<br>" .
//											"col_nums: " . print_r($col_nums,1) . "<br>" .
//											"record: " . print_r($one_record,1));
//							#}
//							$inv_ignore[] = $invno;
//							$bad_docno++;
//							$skip_record = true;
							$invoice_id = 'NULL';
						}
					}
				}
				if (!$skip_record)
				{
					$route = strtolower(csvfield('ROUTE'));
					if (((strpos($route,"to") !== false)) && ((strpos($route,"us") !== false)))
						$payment_route_id = $id_ROUTE_tous;
					elseif (((strpos($route,"forward") !== false)) || ((strpos($route,"fwd") !== false)))
						$payment_route_id = $id_ROUTE_fwd;
					elseif (strpos($route,"direct") !== false)
						$payment_route_id = $id_ROUTE_direct;
					elseif (strpos($route,"spent") !== false)
						$payment_route_id = $id_ROUTE_cspent;
					else  
					{
						print_dump();
						dlog("*=* PAYMENT_ROUTE not found from ROUTE \"" . csvfield('ROUTE') . "\" - (record $input_count)<br>" .
									"col_nums: " . print_r($col_nums,1) . "<br>" .
									"record: " . print_r($one_record,1));
						$input_count++;
						$bad_route++;
						$payment_route_id = 'NULL';
					}
				}				
				if (!$skip_record)
				{
					$col_amt_rx = 1.0 * csvfield('AMOUNT');

					$z_clid = 1 * csvfield('CLID');

					$col_percent = 1.0 * csvfield('PERCENT');

					$col_bounced = (yesno2bool(csvfield('BOUNCED')) === true) ? $sqlTrue : $sqlFalse;

					if (csvfield('TYPE') == '')
						$col_payment_method_id = 'NULL';
					else
					{
						$col_payment_method_id = payment_method_from_text_local(csvfield('TYPE'));
						if ($col_payment_method_id == 0)
						{
							print_dump();
							dlog("*=* Payment Method not found from TYPE \"" . csvfield('TYPE') . "\" - (record $input_count)<br>" .
										"col_nums: " . print_r($col_nums,1) . "<br>" .
										"record: " . print_r($one_record,1));
							$input_count++;
							$bad_method++;
							$col_payment_method_id = 'NULL';
						}
					}
				}
				if (!$skip_record)
				{
					print_dump();

					# --- SQL Insertions - INV_ALLOC --------------------------------------------

					$fields = "JOB_ID,  COL_DT_RX,  COL_PAYMENT_ROUTE_ID, COL_AMT_RX,  COL_PERCENT,  COL_BOUNCED,  ";
					$values = "$job_id, $col_dt_rx, $payment_route_id,    $col_amt_rx, $col_percent, $col_bounced, ";

					$fields .= "COL_PAYMENT_METHOD_ID,  INVOICE_ID,  IMPORTED, Z_DATE,  Z_CLID";
					$values .= "$col_payment_method_id, $invoice_id, $sqlTrue, $z_date, $z_clid";

					$sql = "INSERT INTO JOB_PAYMENT ($fields) VALUES ($values)";
					if ($verbose)
						$print_buffer[] = $sql;
					if ($verbose)
						dlog($sql);
					$job_payment_id = sql_execute($sql);
					if ($verbose)
						dlog("-> ID $job_payment_id");
					$output_count++;
					if ($year)
						$year_counts[$year]['OUT'] = $year_counts[$year]['OUT'] + 1;
				}
			} # elseif ($one_record && (count($one_record) > 0))
			else 
			{
				print_dump();
				dlog("End of Payments");
				break; # while(true)
			}
			
			$input_count++;
			if (	(0 < $stop_after_i) && ($stop_after_i < $input_count) ||
					(0 < $stop_after_o) && ($stop_after_o <= $output_count)
			   )
			{
				dlog("Stopping after $stop_after_i inputs and $stop_after_o outputs");
				if (0 < $stop_after_i)
					break; # while(true)
			}
			if (($input_count % 10000) == 0)
				dlog("Read " . number_with_commas($input_count) . " records so far (last date: $z_date ($col_dt_rx))...");#
				
		} # while(true)

		$year_text = "Years:<br>$crlf";
		foreach ($year_counts as $year => $inout)
			$year_text .= "        $year:        IN:{$inout['IN']}        OUT:{$inout['OUT']}<br>$crlf";
		
		$time_taken = 1.0 * (time() - $start_time) / 60.0;
		$per_rec = ($output_count ? (60.0 * $time_taken / (1.0 * $output_count / 1000.0)) : -1);
		dlog("Imported $output_count payments from a file of " . ($input_count-1) . " payments (and 1 header record).<br>$crlf" .
				"Input file had $bad_seq payments with a bad SEQUENCE (" . count($seq_ignore) . " sequences were unfound).<br>$crlf" .
				"Input file had $bad_docno payments with a bad INVNO (" . count($inv_ignore) . " invno were unfound).<br>$crlf" .
				"Input file had $bad_route payments with a bad ROUTE.<br>$crlf" .
				"Input file had $bad_method payments with a bad TYPE.<br>$crlf" .
				"\$count_1980=$count_1980.<br>$crlf" .
				"\$count_2090=$count_2090.<br>$crlf" .
				"$year_text<br>$crlf" .
				"Time taken: $time_taken mins ($per_rec secs per 1000 invoices)");
		
		fclose($fp_csv);
	}
	else
	{
		if (!$fp_csv)
			dlog("*=* Failed to fopen(\"$dirname/$csv\",'r')");
	}

} # import_payments()

function import_tdx()
{
	# Collect only: TDX_ACT.DBF
	
	# Before calling this function:
	#	- Export COLLECT/TDX_ACT.DBF as TDX_ACT.csv using DBF Manager with default settings.
	# Required:
	#	c/TDX_ACT.csv

	# Import 31/12/16:
	#	import_2017_01_01_1340.log
	#	Crashed after about two hours.
	#		SELECT TOP 1 JOB_ACT_ID, JA_DT FROM JOB_ACT ORDER BY JOB_ACT_ID DESC
	#		>>	1127857		2016-01-22 11:19:03.000
	#	Second run: $incremental = true.
	#	import_2017_01_01_1546.log
	#	2017-01-01 15:46:33/1 import_tdx(): SELECT TOP 1 JOB_ACT_ID, JA_DT FROM JOB_ACT ORDER BY JOB_ACT_ID DESC
	#	2017-01-01 15:46:33/1 max_ja_id=1127857, max_ja_dt=2016-01-22 11:19:03.000
	#	2017-01-01 15:46:33/1 import_tdx(): DELETE FROM JOB_ACT WHERE JOB_ACT_ID=1127857
//		2017-01-01 15:47:00/1 First incremental record (input_count=1103501) Record=<br>Array
//		(
//			[0] => 1505991
//			[1] => LSE
//			[2] => 15/04/2016
//			[3] => 10:25:13
//		)
	#	2017-01-01 16:03:50/1 Read 1,290,000 records so far, wrote 162,555 records so far...
	#		+ 1,127,857 - 1 = 1,290,411 (the excess is probably due to more than one job having the same VILNo).
	#	2017-01-01 16:08:41/1 Imported 205087 tdxs (205059 unique job tdxs) from 1349595 records
	#		SELECT TOP 1 JOB_ACT_ID, JA_DT FROM JOB_ACT ORDER BY JOB_ACT_ID DESC
	#		>>	1332944		2016-12-30 12:50:11.000

	
	# 2016-02-17 14:33:07/1 Imported 935790 tdxs (935268 unique job tdxs) from 935469 records
	# TDX_ACT.DBF has 935469 records.

	# 2016-09-28 07:16:33/1 Imported 1262947 tdxs (1262159 unique job tdxs) from 1262536 records
	
	global $col_nums; # for csvfield()
	global $csvfield_error;
	global $input_count; # for csvfield()
	global $one_record; # for csvfield()
	global $print_buffer; # for print_dump()
	global $sqlTrue;
	global $verbose; # for print_dump()
	
	$enabled = false; #
	if (!$enabled)
	{
		dprint("This import function is disabled", true);
		return;
	}
	
	$incremental = false;
	#$incremental = true;
	
	print "<h3>Importing Collection TDX Activity" . ($incremental ? "**INCREMENTAL**" : '') . "</h3>";
	
	set_time_limit(4 * 60 * 60); # 4 hours
	
	$max_ja_id = 0;
	$max_ja_dt = '';
	if ($incremental)
	{
		# We MUST use JOB_ACT_ID to find the latest date.
		$sql = "SELECT TOP 1 JOB_ACT_ID, JA_DT FROM JOB_ACT ORDER BY JOB_ACT_ID DESC";
		dlog("import_tdx(): $sql");
		sql_execute($sql);
		while (($newArray = sql_fetch()) != false)
		{
			$max_ja_id = $newArray[0];
			$max_ja_dt = $newArray[1]; # e.g. "2014-02-14 11:22:58.000"
		}
		dlog("max_ja_id=$max_ja_id, max_ja_dt=$max_ja_dt");
		$sql = "DELETE FROM JOB_ACT WHERE JOB_ACT_ID=$max_ja_id";
		dlog("import_tdx(): $sql");
		sql_execute($sql);
	}
	else
		init_tdx();

	$sys = 'c'; # collect

	$stop_after = 0;#
	$stop_margin = 0;
	$input_count = 0;
	$output_count = 0;
	$output_count_uq = 0; # unique job outputs
	
	$dirname = "import-vilcol/{$sys}";
	$csv = "TDX_ACT.csv";
	$fp_csv = fopen("$dirname/$csv", 'r');
	if ($fp_csv)
	{
		$col_nums = array();
		
		$while_safety = 0; # to avoid infinite-looping
		while (true)
		{
			$verbose = false;
			if ((0 < $stop_after) && ($input_count <= $stop_after) && (($stop_after - $input_count) < $stop_margin))
				$verbose = true;
			$print_buffer = array();
			if ($verbose)
				$print_buffer[] = "Starting record, input_count=$input_count";
				
			$while_safety++;
			if ($while_safety > 4000000) # 4,000,000 activities
			{
				dlog("*=* import_tdx(): EMERGENCY BREAK OUT \$while_safety=$while_safety, \$input_count=$input_count, \$output_count=$output_count, \$output_count_uq=$output_count_uq");
				print_dump();
				break; # while(true)
			}
			#if (($while_safety % 10000) == 0)
			#	log_write("import_tdx(): \$while_safety=$while_safety, \$input_count=$input_count, \$output_count=$output_count, \$output_count_uq=$output_count_uq");#
			
			$one_record = fgetcsv($fp_csv);
			if ($verbose)
				$print_buffer[] = ((($input_count == 0) ? "Headers: " : "TDX: ") . print_r($one_record,1));#
			if ($input_count == 0)
			{
				foreach ($one_record as $num => $field)
					$col_nums[$field] = $num;
				if ($verbose)
					$print_buffer[] = ("col_nums: " . print_r($col_nums,1));#
			}
			elseif ($one_record && (count($one_record) > 0))
			{
				if (csvfield('VILNO') != '')
				{
					$ja_dt = csvfield('DATE', 'uk', true);
					if ((!$ja_dt) || $csvfield_error)
					{
						$print_buffer[] = ("*=* Date Value not found from DATE \"" . csvfield('DATE') . "\"");
						$input_count++;
						print_dump();
						if ((0 < $stop_after) && ($stop_after < $input_count))
							break; # while(true)
						continue;
					}
					
					$time = csvfield('TIME');
					if ($time)
					{
						$temp = explode(':', $time);
						if (count($temp) == 3)
						{
							# Need to get rid of alpha chars from time!
							$temp[0] = 1 * $temp[0];
							$temp[1] = 1 * $temp[1];
							$temp[2] = 1 * $temp[2];
							# Also get rid of impossible numbers!
							if (23 < $temp[0])
								$temp[0] = 23;
							if (59 < $temp[1])
								$temp[1] = 59;
							if (59 < $temp[2])
								$temp[2] = 59;
							$time = implode(':', $temp);
							$ja_dt_raw = "{$ja_dt} {$time}";
							$ja_dt = "'{$ja_dt_raw}'";
						}
						else
							$time = '';
					}
					if (!$time)
					{
						$print_buffer[] = ("*=* Time Value not found from TIME \"" . csvfield('TIME') . "\"");
						$input_count++;
						print_dump();
						if ((0 < $stop_after) && ($stop_after < $input_count))
							break; # while(true)
						continue;
					}
					
					if ((!$incremental) || ($max_ja_dt <= $ja_dt_raw))
					{
						if ($incremental && ($output_count == 0))
							dlog("First incremental record (input_count=$input_count) Record=<br>" . print_r($one_record,1));
						
						$sql = "SELECT JOB_ID FROM JOB WHERE J_VILNO = " . (1 * csvfield('VILNO'));
						if ($verbose)
							$print_buffer[] = $sql;
						sql_execute($sql);
						$jobs = array();
						while (($newArray = sql_fetch()) != false)
							$jobs[] = $newArray[0];
						if (count($jobs) == 0)
						{
							$print_buffer[] = ("*=* JOB_ID not found from VILNO \"" . csvfield('VILNO') . "\"");
							$input_count++;
							print_dump();
							if ((0 < $stop_after) && ($stop_after < $input_count))
								break; # while(true)
							continue;
						}

						$sql = "SELECT ACTIVITY_ID FROM ACTIVITY_SD WHERE ACT_TDX=" . quote_smart(csvfield('ACT'));
						if ($verbose)
							$print_buffer[] = $sql;
						sql_execute($sql);
						$activity_id = 0;
						while (($newArray = sql_fetch()) != false)
							$activity_id = $newArray[0];
						if ( ! (0 < $activity_id) )
						{
							$print_buffer[] = ("*=* ACTIVITY_ID not found from ACT \"" . csvfield('ACT') . "\"");
							$input_count++;
							print_dump();
							if ((0 < $stop_after) && ($stop_after < $input_count))
								break; # while(true)
							continue;
						}

						$done_uq = false;
						foreach ($jobs as $job_id)
						{
							$fields = "JOB_ID,  ACTIVITY_ID,  JA_DT,  QC_EXPORT_ID, IMPORTED";
							$values = "$job_id, $activity_id, $ja_dt, NULL,         $sqlTrue";

							$sql = "INSERT INTO JOB_ACT ($fields) VALUES ($values)";
							if ($verbose)
								$print_buffer[] = $sql;
							$job_act_id = sql_execute($sql);
							$output_count++;
							if (!$done_uq)
							{
								$output_count_uq++;
								$done_uq = true;
							}
							if ($verbose)
								$print_buffer[] = ("-> ID $job_act_id");
						}
					} # if ((!$incremental) || ($max_ja_dt <= $ja_dt_raw))
				}
				else 
					dlog("*=* TDX_ACT record has no VILNO (input_count=$input_count) Record=<br>" . print_r($one_record,1));

				if (($input_count % 10000) == 0)
					dlog("Read " . number_with_commas($input_count) . " records so far, wrote $output_count records so far...");#
			}
			else 
			{
				dlog("End of TDX");
				print_dump();
				break; # while(true)
			}

			$input_count++;
			print_dump();
			if ((0 < $stop_after) && ($stop_after < $input_count))
				break; # while(true)
				
		} # while(true)

		$tdx_count = $input_count - 1;
		dlog("Imported $output_count tdxs ($output_count_uq unique job tdxs) from $tdx_count records");
		
		fclose($fp_csv);
	}
	else
	{
		if (!$fp_csv)
			dlog("*=* Failed to fopen(\"$dirname/$csv\",'r')");
	}

} # import_tdx()

function import_trans()
{
	# Import TDX Transaction IDs, also setting C-Numbers.
	# Before calling this function:
	#	- Check that we have already exported COLLECT/COLLECT.DBF as COLLECT.csv - see import_jobs()
	# Required:
	#	c/COLLECT.csv
	
	# Import 31/12/16:
	#	import_2017_01_01_1612.log
	#	SELECT JC_TRANS_CNUM, COUNT(*) FROM JOB GROUP BY JC_TRANS_CNUM
	#	2017-01-01 16:18:50/1 End of Jobs, input_count=473203, trans_count=103549
	#	2017-01-01 16:18:50/1 Imported 103549 transaction codes from a Collections file of 473202 jobs (and 1 header record).
	#	Time taken: 6.5333333333333 mins (3.7856473746729 secs per 1000 jobs)
	
	# 28/09/16:
	#		2016-09-28 07:27:43/1 End of Jobs, input_count=438937, trans_count=103549
	#		2016-09-28 07:27:43/1 Imported 103549 transaction codes from a Collections file of 438936 jobs (and 1 header record).<br>Time taken: 5.6 mins (3.2448406068625 secs per 1000 jobs)
	
	global $col_nums; # for csvfield()
	global $input_count; # for csvfield()
	global $j_sequence; # for csvfield()
	global $one_record; # for csvfield()
	global $tc;
		
	$enabled = false; #
	if (!$enabled)
	{
		dprint("This import function is disabled", true);
		return;
	}
	if ($tc != 'c')
	{
		dlog("*=* import_trans() - illegal tc \"$tc\"");
		return;
	}

	print "<h3>Importing Transaction IDs (Collect system)</h3>";
	
	$start_time = time();
	set_time_limit(60 * 60 * 8); # 8 hours
	
	init_trans();
	
	$stop_after = 0;#
	$stop_margin = 0;
	$input_count = 0;
	
	$sys = $tc; # 'c'
	$dirname = "import-vilcol/{$sys}";
	{
		$sys_txt = "Collections";
		$csv = "COLLECT.csv";
	}
	
	sql_encryption_preparation('JOB');
	
	$fp_csv = fopen("$dirname/$csv", 'r');
	if ($fp_csv)
	{
		$col_nums = array();
		$trans_count = 0;
		
		$while_safety = 0; # to avoid infinite-looping
		while (true)
		{
			$verbose = false;
			if ((0 < $stop_after) && ($input_count <= $stop_after) && (($stop_after - $input_count) <= $stop_margin))
				$verbose = true;
			
			$while_safety++;
			if ($while_safety > 800000) # 800,000 jobs
			{
				dlog("*=* (Sys=$sys) Safety Ejection!");
				break; # while(true)
			}

			if ($verbose)
				dprint("=== Record $input_count ===");
				
			$one_record = fgetcsv($fp_csv);
			$j_sequence = -1;
			if ($input_count > 0) # skip header record
			{
				if ($one_record && (count($one_record) > 0)) # test for end-of-records
				{
					$seq_raw = csvfield('SEQUENCE');
					$j_sequence = 1 * $seq_raw;
				}
				else 
				{
					dlog("End of Jobs, input_count=$input_count, trans_count=$trans_count");
					break; # while(true)
				}
				
//				if ($j_sequence == 90730472)#
//				{
//					$verbose = true;
//					dprint("=== Record $input_count ===");
//				}
				
				$old_verbose = $verbose;
				if (($j_sequence == 90149542) || ($j_sequence == 90493801)
						#|| (($one_record[0] == -1) && (trim($one_record[8]) == '54B MORAY ROAD')))
						|| in_fix_aux_list($one_record))
				{
					for ($ii = 88; 36 <= $ii; $ii--)
						$one_record[$ii] = $one_record[$ii-4];
					$bits = explode(',', str_replace('"', '', $one_record[31]));
					$one_record[31] = str_replace('\\', '', $bits[0]);
					$one_record[32] = $bits[1];
					$one_record[33] = $bits[2];
					$one_record[34] = $bits[3];
					$one_record[35] = $bits[4];
				}
				elseif ($j_sequence == 730508)
				{
					$one_record[70] = str_replace('foren\",m', 'forenm', $one_record[70]);
					$one_record[71] = str_replace('"', '', $one_record[71]);
				}
				elseif ($j_sequence == 90503059)
				{
					for ($ii = 88; 15 <= $ii; $ii--)
						$one_record[$ii] = $one_record[$ii-1];
					$bits = explode(',', str_replace('"', '', $one_record[13]));
					$one_record[13] = str_replace('\\', '', $bits[0]);
					$one_record[14] = $bits[1];
				}
				elseif (($j_sequence == 90694238) || ($j_sequence == 90700518))
				{
					for ($ii = 88; 28 <= $ii; $ii--)
						$one_record[$ii] = $one_record[$ii-7];
					$bits = explode(',', str_replace('"', '', $one_record[20]));
					$one_record[20] = str_replace('\\', '', $bits[0]);
					$one_record[21] = $bits[1];
					$one_record[22] = $bits[2];
					$one_record[23] = $bits[3];
					$one_record[24] = $bits[4];
					$one_record[25] = $bits[5];
					$one_record[26] = $bits[6];
					$one_record[27] = $bits[7];
				}
				elseif (($j_sequence == 90707120) || ($j_sequence == 90738552))
				{
					$vlf = $one_record[81];
					for ($ii = 87; 28 <= $ii; $ii--)
						$one_record[$ii] = $one_record[$ii-7];
					$bits = explode(',', str_replace('"', '', $one_record[20]));
					$one_record[20] = str_replace('\\', '', $bits[0]);
					$one_record[21] = $bits[1];
					$one_record[22] = $bits[2];
					$one_record[23] = $bits[3];
					$one_record[24] = $bits[4];
					$one_record[25] = $bits[5];
					$one_record[26] = $bits[6];
					$one_record[27] = $bits[7];
					$one_record[88] = $vlf;
				}
				elseif (($j_sequence == 90714469) || ($j_sequence == 90717737))
				{
					$vlf = $one_record[87];
					for ($ii = 87; 28 <= $ii; $ii--)
						$one_record[$ii] = $one_record[$ii-1];
					$bits = explode(',', str_replace('"', '', $one_record[26]));
					$one_record[26] = str_replace('\\', '', $bits[0]);
					$one_record[27] = $bits[1];
					$one_record[88] = $vlf;
				}
				elseif ($j_sequence == 90723905)
				{
					$vlf = $one_record[83];
					for ($ii = 87; 26 <= $ii; $ii--)
						$one_record[$ii] = $one_record[$ii-5];
					$bits = explode(',', str_replace('"', '', $one_record[20]));
					$one_record[20] = str_replace('\\', '', $bits[0]);
					$one_record[21] = $bits[1];
					$one_record[22] = $bits[2];
					$one_record[23] = $bits[3];
					$one_record[24] = $bits[4];
					$one_record[25] = $bits[5];
					$one_record[88] = $vlf;
				}
				#if ($j_sequence != 90707120)#
				#	continue;
				$verbose = $old_verbose;
				
//				if (csvfield('CLIREF') == 'L459478')#
//				{
//					$verbose = true;
//					dprint("=== Record $input_count === Found \"" . csvfield('CLIREF') . "\"");
//				}
			}
		
//			if ((0 < $stop_after) && ($stop_after < $input_count))
//			{
//				$input_count++;
//				continue;
//			}

			if ($verbose)
				dprint((($input_count == 0) ? "Headers: " : "Job: ") . print_r($one_record,1));#
			if ($input_count == 0)
			{
				# Header record
				foreach ($one_record as $num => $field)
					$col_nums[$field] = $num;
				if ($verbose)
					dprint("col_nums: " . print_r($col_nums,1));#
			}
			else
			{
				# Data record

				# We can be pretty sure that CLIREF is filled in when AN1 has a value.
				$client_ref = csvfield('CLIREF');
				if ($client_ref)
				{
					$jc_trans_id = csvfield('AN1');
					if ($jc_trans_id && (strpos($jc_trans_id, ' ') === false))
					{
						$client_ref = quote_smart($client_ref, true);
						$sql = "SELECT JOB_ID FROM JOB WHERE J_SEQUENCE = $j_sequence";
						if ($verbose)
							dprint($sql);
						sql_execute($sql);
						$job_id = 0;
						while (($newArray = sql_fetch()) != false)
							$job_id = $newArray[0];

						if ($job_id)
						{
							if ($trans_count < $stop_margin)
							{
								#$verbose = true;
								dprint("=== Record $input_count === (trans_count=$trans_count)");
								dprint((($input_count == 0) ? "Headers: " : "Job: ") . print_r($one_record,1));#
							}
							$jc_trans_id = quote_smart($jc_trans_id, true);
							$sql= "UPDATE JOB SET JC_TRANS_ID=$jc_trans_id, JC_TRANS_CNUM='C1234' WHERE JOB_ID=$job_id";
							if ($verbose)
								dprint($sql);
							sql_execute($sql); # no need to audit
							$trans_count++;
						}
					}
				}
			}
			
			$input_count++;
			if (($input_count % 100000) == 0)
				dlog("import_trans($sys): done $input_count input records so far");
			if ((0 < $stop_after) && ($stop_after < $trans_count))
			{
				#dlog("Stopping after $stop_after jobs");
				#break; # while(true)
			}
			
		} # while(true)

		$time_taken = 1.0 * (time() - $start_time) / 60.0;
		$per_rec = ($trans_count ? (60.0 * $time_taken / (1.0 * $trans_count / 1000.0)) : 0.0);
		dlog("Imported $trans_count transaction codes from a $sys_txt file of " . ($input_count-1) . " jobs (and 1 header record).<br>" .
				"Time taken: $time_taken mins ($per_rec secs per 1000 jobs)");
		
		fclose($fp_csv);
	}
	else
		dlog("*=* (Sys=$sys) Failed to fopen(\"$dirname/$csv\",'r')");

} # import_trans()

function import_jobs_amt()
{
	# Import COLLECT.DBF/AMOUNTOWED into JOB.JC_TOTAL_AMT.
	# Before calling this function:
	#	- Check that we have already exported COLLECT/COLLECT.DBF as COLLECT.csv - see import_jobs()
	# Required:
	#	c/COLLECT.csv
	
	# Import 31/12/16:
	#	import_2016_12_31_1714.log
	#	2016-12-31 17:14:35/   POST=Array
	#	(
	#	    [task] => i_jobs_amt
	#	    [tc] => c
	#	    [other] => 
	#	)
	#	2016-12-31 17:34:11/1 Imported 311938 AMOUNTOWED from a Collections file of 473202 jobs (and 1 header record).
	#	Time taken: 19.6 mins (3.7699799319095 secs per 1000 jobs)
	
	# 27/09/16 on server:
	# Imported 277322 AMOUNTOWED from a Collections file of 438936 jobs (and 1 header record).
	# Time taken: 17.4 mins (3.7645769178067 secs per 1000 jobs)
	# 2016-09-27 17:49:23/1 Imported 277322 AMOUNTOWED from a Collections file of 438936 jobs (and 1 header record).<br>Time taken: 17.4 mins (3.7645769178067 secs per 1000 jobs)

	global $col_nums; # for csvfield()
	global $input_count; # for csvfield()
	global $j_sequence; # for csvfield()
	global $one_record; # for csvfield()
	global $tc;
		
	$enabled = false; #
	if (!$enabled)
	{
		dprint("This import function is disabled", true);
		return;
	}
	if ($tc != 'c')
	{
		dlog("*=* import_jobs_amt() - illegal tc \"$tc\"");
		return;
	}

	print "<h3>Importing COLLECT.DBF/AMOUNTOWED into JOB.JC_TOTAL_AMT (Collect system)</h3>";
	
	$start_time = time();
	set_time_limit(60 * 60 * 8); # 8 hours
	
	$stop_after = 0;#
	$stop_margin = 0;
	$input_count = 0;
	
	$sys = $tc; # 'c'
	$dirname = "import-vilcol/{$sys}";
	{
		$sys_txt = "Collections";
		$csv = "COLLECT.csv";
	}
	
	sql_encryption_preparation('JOB');
	
	$fp_csv = fopen("$dirname/$csv", 'r');
	if ($fp_csv)
	{
		$col_nums = array();
		$amt_count = 0;
		
		$while_safety = 0; # to avoid infinite-looping
		while (true)
		{
			$verbose = false;
			if ((0 < $stop_after) && ($input_count <= $stop_after) && (($stop_after - $input_count) <= $stop_margin))
				$verbose = true;
			
			$while_safety++;
			if ($while_safety > 800000) # 800,000 jobs
			{
				dlog("*=* (Sys=$sys) Safety Ejection!");
				break; # while(true)
			}

			if ($verbose)
				dprint("=== Record $input_count ===");
				
			$one_record = fgetcsv($fp_csv);
			$j_sequence = -1;
			if ($input_count > 0) # skip header record
			{
				if ($one_record && (count($one_record) > 0)) # test for end-of-records
				{
					$seq_raw = csvfield('SEQUENCE');
					$j_sequence = 1 * $seq_raw;
				}
				else 
				{
					dlog("End of Jobs, input_count=$input_count, trans_count=$amt_count");
					break; # while(true)
				}
				
				$old_verbose = $verbose;
				if (($j_sequence == 90149542) || ($j_sequence == 90493801)
						#|| (($one_record[0] == -1) && (trim($one_record[8]) == '54B MORAY ROAD')))
						|| in_fix_aux_list($one_record))
				{
					for ($ii = 88; 36 <= $ii; $ii--)
						$one_record[$ii] = $one_record[$ii-4];
					$bits = explode(',', str_replace('"', '', $one_record[31]));
					$one_record[31] = str_replace('\\', '', $bits[0]);
					$one_record[32] = $bits[1];
					$one_record[33] = $bits[2];
					$one_record[34] = $bits[3];
					$one_record[35] = $bits[4];
				}
				elseif ($j_sequence == 730508)
				{
					$one_record[70] = str_replace('foren\",m', 'forenm', $one_record[70]);
					$one_record[71] = str_replace('"', '', $one_record[71]);
				}
				elseif ($j_sequence == 90503059)
				{
					for ($ii = 88; 15 <= $ii; $ii--)
						$one_record[$ii] = $one_record[$ii-1];
					$bits = explode(',', str_replace('"', '', $one_record[13]));
					$one_record[13] = str_replace('\\', '', $bits[0]);
					$one_record[14] = $bits[1];
				}
				elseif (($j_sequence == 90694238) || ($j_sequence == 90700518))
				{
					for ($ii = 88; 28 <= $ii; $ii--)
						$one_record[$ii] = $one_record[$ii-7];
					$bits = explode(',', str_replace('"', '', $one_record[20]));
					$one_record[20] = str_replace('\\', '', $bits[0]);
					$one_record[21] = $bits[1];
					$one_record[22] = $bits[2];
					$one_record[23] = $bits[3];
					$one_record[24] = $bits[4];
					$one_record[25] = $bits[5];
					$one_record[26] = $bits[6];
					$one_record[27] = $bits[7];
				}
				elseif (($j_sequence == 90707120) || ($j_sequence == 90738552))
				{
					$vlf = $one_record[81];
					for ($ii = 87; 28 <= $ii; $ii--)
						$one_record[$ii] = $one_record[$ii-7];
					$bits = explode(',', str_replace('"', '', $one_record[20]));
					$one_record[20] = str_replace('\\', '', $bits[0]);
					$one_record[21] = $bits[1];
					$one_record[22] = $bits[2];
					$one_record[23] = $bits[3];
					$one_record[24] = $bits[4];
					$one_record[25] = $bits[5];
					$one_record[26] = $bits[6];
					$one_record[27] = $bits[7];
					$one_record[88] = $vlf;
				}
				elseif (($j_sequence == 90714469) || ($j_sequence == 90717737))
				{
					$vlf = $one_record[87];
					for ($ii = 87; 28 <= $ii; $ii--)
						$one_record[$ii] = $one_record[$ii-1];
					$bits = explode(',', str_replace('"', '', $one_record[26]));
					$one_record[26] = str_replace('\\', '', $bits[0]);
					$one_record[27] = $bits[1];
					$one_record[88] = $vlf;
				}
				elseif ($j_sequence == 90723905)
				{
					$vlf = $one_record[83];
					for ($ii = 87; 26 <= $ii; $ii--)
						$one_record[$ii] = $one_record[$ii-5];
					$bits = explode(',', str_replace('"', '', $one_record[20]));
					$one_record[20] = str_replace('\\', '', $bits[0]);
					$one_record[21] = $bits[1];
					$one_record[22] = $bits[2];
					$one_record[23] = $bits[3];
					$one_record[24] = $bits[4];
					$one_record[25] = $bits[5];
					$one_record[88] = $vlf;
				}
				$verbose = $old_verbose;
			}
		
			if ($verbose)
				dprint((($input_count == 0) ? "Headers: " : "Job: ") . print_r($one_record,1));#
			if ($input_count == 0)
			{
				# Header record
				foreach ($one_record as $num => $field)
					$col_nums[$field] = $num;
				if ($verbose)
					dprint("col_nums: " . print_r($col_nums,1));#
			}
			else
			{
				# Data record

				$sql = "SELECT JOB_ID FROM JOB WHERE J_SEQUENCE = $j_sequence";
				if ($verbose)
					dprint($sql);
				sql_execute($sql);
				$job_id = 0;
				while (($newArray = sql_fetch()) != false)
					$job_id = $newArray[0];

				if ($job_id)
				{
					$jc_total_amt = 1.0 * csvfield('AMOUNTOWED');
					$sql= "UPDATE JOB SET JC_TOTAL_AMT=$jc_total_amt WHERE JOB_ID=$job_id";
					if ($verbose)
						dprint($sql);
					sql_execute($sql); # no need to audit
					$amt_count++;
				}
			}
			
			$input_count++;
			if (($input_count % 10000) == 0)
				dlog("import_jobs_amt($sys): done $input_count input records so far");
			if ((0 < $stop_after) && ($stop_after < $amt_count))
			{
				#dlog("Stopping after $stop_after jobs");
				#break; # while(true)
			}
			
		} # while(true)

		$time_taken = 1.0 * (time() - $start_time) / 60.0;
		$per_rec = ($amt_count ? (60.0 * $time_taken / (1.0 * $amt_count / 1000.0)) : 0.0);
		dlog("Imported $amt_count AMOUNTOWED from a $sys_txt file of " . ($input_count-1) . " jobs (and 1 header record).<br>" .
				"Time taken: $time_taken mins ($per_rec secs per 1000 jobs)");
		
		fclose($fp_csv);
	}
	else
		dlog("*=* (Sys=$sys) Failed to fopen(\"$dirname/$csv\",'r')");
	
} # import_jobs_amt()

function import_tcflags()
{
	# Set CLIENT2.C_TRACE and C_COLLECT flags.
	# Before calling this function:
	#	- The clients must already have been imported.
	# Required:
	#	Nothing
	
	# Import 31/12/16:
	#	import_2017_01_01_1634.log
	#	2017-01-01 16:34:17/1 Updated 5335 client records.
	#	Time taken: 0.05 mins (0.56232427366448 secs per 1000 records)

	
	# 2016-09-28 07:28:21/1 Updated 5193 client records.<br>Time taken: 0.05 mins (0.57770075101098 secs per 1000 records)
	
	global $sqlTrue;
	
	$enabled = false; #
	if (!$enabled)
	{
		dprint("This import function is disabled", true);
		return;
	}

	print "<h3>Setting CLIENT2.C_TRACE and C_COLLECT flags</h3>";
	
	$start_time = time();
	set_time_limit(60 * 60 * 8); # 8 hours
	
	init_tcflags();
	
	$sql = "SELECT DISTINCT CLIENT2_ID FROM JOB WHERE JT_JOB=$sqlTrue";
	sql_execute($sql);
	$clients = array();
	while (($newArray = sql_fetch()) != false)
		$clients[] = $newArray[0];
	$count_t = count($clients);
	dprint("$sql: found $count_t Trace clients");
	
	$sql = "UPDATE CLIENT2 SET C_TRACE=$sqlTrue WHERE CLIENT2_ID IN (" . implode(',',$clients) . ")";
	dprint($sql);
	sql_execute($sql);
	dprint("Updated Trace clients");

	$sql = "SELECT DISTINCT CLIENT2_ID FROM JOB WHERE JC_JOB=$sqlTrue";
	sql_execute($sql);
	$clients = array();
	while (($newArray = sql_fetch()) != false)
		$clients[] = $newArray[0];
	$count_c = count($clients);
	dprint("$sql: found $count_c Collect clients");
	
	$sql = "UPDATE CLIENT2 SET C_COLLECT=$sqlTrue WHERE CLIENT2_ID IN (" . implode(',',$clients) . ")";
	dprint($sql);
	sql_execute($sql);
	dprint("Updated Collect clients");

	$sql = "UPDATE CLIENT2 SET INV_STMT_FREQ='M'";
	dprint($sql);
	sql_execute($sql);
	dprint("Updated Statement Frequency");

	$count_all = $count_t + $count_c;
	$time_taken = 1.0 * (time() - $start_time) / 60.0;
	$per_rec = ($count_all ? (60.0 * $time_taken / (1.0 * $count_all / 1000.0)) : 0.0);
	dlog("Updated $count_all client records.<br>" .
			"Time taken: $time_taken mins ($per_rec secs per 1000 records)");

} # import_tcflags()

function init_primary_flags()
{
	global $sqlFalse;
	global $sqlTrue;
	
	$sql = "UPDATE JOB_PHONE SET JP_PRIMARY_P=$sqlFalse, JP_PRIMARY_E=$sqlFalse WHERE IMPORTED = $sqlTrue";
	dprint($sql);
	sql_execute($sql); # no need to audit
	
} # init_primary_flags()

function primary_phones()
{
	
	# Set JOB_PHONE.JP_PRIMARY_P and JP_PRIMARY_E flags from existing JOB_PHONE data.
	# Before calling this function:
	#	- The JOB_PHONE data must already have been imported.
	# Required:
	#	Nothing
	
	# Import 31/12/16:
	#	import_2017_01_01_1636.log
	#	SELECT JP_PRIMARY_P, COUNT(*) FROM JOB_PHONE GROUP BY JP_PRIMARY_P
	#	SELECT JP_PRIMARY_E, COUNT(*) FROM JOB_PHONE GROUP BY JP_PRIMARY_E
	#	2017-01-01 17:28:40/1 Updated 326511 JOB_PHONE records.
	#	Time taken: 52.3 mins (9.6107022428035 secs per 1000 records)

	
	# 2016-09-28 08:20:08/1 Updated 292105 JOB_PHONE records.<br>Time taken: 47.416666666667 mins (9.7396484140977 secs per 1000 records)
	
	global $sqlTrue;
	
	$enabled = false; #
	if (!$enabled)
	{
		dprint("This import function is disabled", true);
		return;
	}

	print "<h3>Setting JP_PRIMARY_P and JP_PRIMARY_E flags</h3>";
	
	$start_time = time();
	set_time_limit(60 * 60 * 8); # 8 hours
	
	init_primary_flags();
	
	$sql = "SELECT DISTINCT JOB_ID FROM JOB_PHONE";
	sql_execute($sql);
	$jobs = array();
	while (($newArray = sql_fetch()) != false)
		$jobs[] = $newArray[0];
	$count = count($jobs);
	dprint("$sql: found $count Jobs in JOB_PHONE");
	
//	$sql_count_p = 0;
//	$sql_count_e = 0;
	$input_count = 0;
	foreach ($jobs as $job_id)
	{
		$sql = "SELECT TOP 1 JOB_PHONE_ID FROM JOB_PHONE 
				WHERE (JOB_ID=$job_id) AND (JP_PHONE IS NOT NULL) 
				ORDER BY JOB_PHONE_ID";
		sql_execute($sql);
		$job_phone_id = 0;
		while (($newArray = sql_fetch()) != false)
			$job_phone_id = $newArray[0];
		if (0 < $job_phone_id)
		{
			$sql = "UPDATE JOB_PHONE SET JP_PRIMARY_P=$sqlTrue WHERE JOB_PHONE_ID=$job_phone_id";
//			if ($sql_count_p < 10)
//				dprint($sql); #
			sql_execute($sql); # no need to audit
//			$sql_count_p++;
		}

		$sql = "SELECT TOP 1 JOB_PHONE_ID FROM JOB_PHONE 
				WHERE (JOB_ID=$job_id) AND (JP_EMAIL IS NOT NULL) 
				ORDER BY JOB_PHONE_ID";
		sql_execute($sql);
		$job_phone_id = 0;
		while (($newArray = sql_fetch()) != false)
			$job_phone_id = $newArray[0];
		if (0 < $job_phone_id)
		{
			$sql = "UPDATE JOB_PHONE SET JP_PRIMARY_E=$sqlTrue WHERE JOB_PHONE_ID=$job_phone_id";
//			if ($sql_count_e < 10)
//				dprint($sql); #
			sql_execute($sql); # no need to audit
//			$sql_count_e++;
		}
		
		$input_count++;
		if (($input_count % 10000) == 0)
			dlog("primary_phones(): done $input_count jobs so far (out of $count jobs)");
	}
	
	$time_taken = 1.0 * (time() - $start_time) / 60.0;
	$per_rec = ($count ? (60.0 * $time_taken / (1.0 * $count / 1000.0)) : 0.0);
	dlog("Updated $count JOB_PHONE records.<br>" .
			"Time taken: $time_taken mins ($per_rec secs per 1000 records)");

} # primary_phones()

function import_jobs_ltr_more()
{
	# Import COLLECT.DBF/MORELET into JOB.JC_LETTER_MORE.
	# Before calling this function:
	#	- Check that we have already exported COLLECT/COLLECT.DBF as COLLECT.csv - see import_jobs()
	# Required:
	#	c/COLLECT.csv
	
	# Import 31/12/16:
	#	import_2017_01_01_1734.log
	#	SELECT JC_LETTER_MORE, COUNT(*) FROM JOB GROUP BY JC_LETTER_MORE
	#		-doesn't change much if at all
	#	2017-01-01 17:53:17/1 End of Jobs, input_count=473203, trans_count=311938
	#	2017-01-01 17:53:17/1 Imported 311938 MORELET from a Collections file of 473202 jobs (and 1 header record).
	#	Time taken: 19 mins (3.6545723829735 secs per 1000 jobs)
	
	
	# 2016-09-28 09:16:32/1 Imported 277322 MORELET from a Collections file of 438936 jobs (and 1 header record).
	# Time taken: 18.3 mins (3.9592964135554 secs per 1000 jobs)
	
	global $col_nums; # for csvfield()
	global $input_count; # for csvfield()
	global $j_sequence; # for csvfield()
	global $one_record; # for csvfield()
	global $tc;
		
	$enabled = false; #
	if (!$enabled)
	{
		dprint("This import function is disabled", true);
		return;
	}
	if ($tc != 'c')
	{
		dlog("*=* import_jobs_amt() - illegal tc \"$tc\"");
		return;
	}

	print "<h3>Importing COLLECT.DBF/MORELET into JOB.JC_LETTER_MORE (Collect system)</h3>";
	
	$start_time = time();
	set_time_limit(60 * 60 * 8); # 8 hours
	
	$stop_after = 0;#
	$stop_margin = 0;
	$input_count = 0;
	
	$sys = $tc; # 'c'
	$dirname = "import-vilcol/{$sys}";
	{
		$sys_txt = "Collections";
		$csv = "COLLECT.csv";
	}
	
	sql_encryption_preparation('JOB');
	
	$fp_csv = fopen("$dirname/$csv", 'r');
	if ($fp_csv)
	{
		$col_nums = array();
		$amt_count = 0;
		
		$while_safety = 0; # to avoid infinite-looping
		while (true)
		{
			$verbose = false;
			if ((0 < $stop_after) && ($input_count <= $stop_after) && (($stop_after - $input_count) <= $stop_margin))
				$verbose = true;
			
			$while_safety++;
			if ($while_safety > 800000) # 800,000 jobs
			{
				dlog("*=* (Sys=$sys) Safety Ejection!");
				break; # while(true)
			}

			if ($verbose)
				dprint("=== Record $input_count ===");
				
			$one_record = fgetcsv($fp_csv);
			$j_sequence = -1;
			if ($input_count > 0) # skip header record
			{
				if ($one_record && (count($one_record) > 0)) # test for end-of-records
				{
					$seq_raw = csvfield('SEQUENCE');
					$j_sequence = 1 * $seq_raw;
				}
				else 
				{
					dlog("End of Jobs, input_count=$input_count, trans_count=$amt_count");
					break; # while(true)
				}
				
				$old_verbose = $verbose;
				if (($j_sequence == 90149542) || ($j_sequence == 90493801)
						#|| (($one_record[0] == -1) && (trim($one_record[8]) == '54B MORAY ROAD')))
						|| in_fix_aux_list($one_record))
				{
					for ($ii = 88; 36 <= $ii; $ii--)
						$one_record[$ii] = $one_record[$ii-4];
					$bits = explode(',', str_replace('"', '', $one_record[31]));
					$one_record[31] = str_replace('\\', '', $bits[0]);
					$one_record[32] = $bits[1];
					$one_record[33] = $bits[2];
					$one_record[34] = $bits[3];
					$one_record[35] = $bits[4];
				}
				elseif ($j_sequence == 730508)
				{
					$one_record[70] = str_replace('foren\",m', 'forenm', $one_record[70]);
					$one_record[71] = str_replace('"', '', $one_record[71]);
				}
				elseif ($j_sequence == 90503059)
				{
					for ($ii = 88; 15 <= $ii; $ii--)
						$one_record[$ii] = $one_record[$ii-1];
					$bits = explode(',', str_replace('"', '', $one_record[13]));
					$one_record[13] = str_replace('\\', '', $bits[0]);
					$one_record[14] = $bits[1];
				}
				elseif (($j_sequence == 90694238) || ($j_sequence == 90700518))
				{
					for ($ii = 88; 28 <= $ii; $ii--)
						$one_record[$ii] = $one_record[$ii-7];
					$bits = explode(',', str_replace('"', '', $one_record[20]));
					$one_record[20] = str_replace('\\', '', $bits[0]);
					$one_record[21] = $bits[1];
					$one_record[22] = $bits[2];
					$one_record[23] = $bits[3];
					$one_record[24] = $bits[4];
					$one_record[25] = $bits[5];
					$one_record[26] = $bits[6];
					$one_record[27] = $bits[7];
				}
				elseif (($j_sequence == 90707120) || ($j_sequence == 90738552))
				{
					$vlf = $one_record[81];
					for ($ii = 87; 28 <= $ii; $ii--)
						$one_record[$ii] = $one_record[$ii-7];
					$bits = explode(',', str_replace('"', '', $one_record[20]));
					$one_record[20] = str_replace('\\', '', $bits[0]);
					$one_record[21] = $bits[1];
					$one_record[22] = $bits[2];
					$one_record[23] = $bits[3];
					$one_record[24] = $bits[4];
					$one_record[25] = $bits[5];
					$one_record[26] = $bits[6];
					$one_record[27] = $bits[7];
					$one_record[88] = $vlf;
				}
				elseif (($j_sequence == 90714469) || ($j_sequence == 90717737))
				{
					$vlf = $one_record[87];
					for ($ii = 87; 28 <= $ii; $ii--)
						$one_record[$ii] = $one_record[$ii-1];
					$bits = explode(',', str_replace('"', '', $one_record[26]));
					$one_record[26] = str_replace('\\', '', $bits[0]);
					$one_record[27] = $bits[1];
					$one_record[88] = $vlf;
				}
				elseif ($j_sequence == 90723905)
				{
					$vlf = $one_record[83];
					for ($ii = 87; 26 <= $ii; $ii--)
						$one_record[$ii] = $one_record[$ii-5];
					$bits = explode(',', str_replace('"', '', $one_record[20]));
					$one_record[20] = str_replace('\\', '', $bits[0]);
					$one_record[21] = $bits[1];
					$one_record[22] = $bits[2];
					$one_record[23] = $bits[3];
					$one_record[24] = $bits[4];
					$one_record[25] = $bits[5];
					$one_record[88] = $vlf;
				}
				$verbose = $old_verbose;
			}
		
			if ($verbose)
				dprint((($input_count == 0) ? "Headers: " : "Job: ") . print_r($one_record,1));#
			if ($input_count == 0)
			{
				# Header record
				foreach ($one_record as $num => $field)
					$col_nums[$field] = $num;
				if ($verbose)
					dprint("col_nums: " . print_r($col_nums,1));#
			}
			else
			{
				# Data record

				$sql = "SELECT JOB_ID FROM JOB WHERE J_SEQUENCE = $j_sequence";
				if ($verbose)
					dprint($sql);
				sql_execute($sql);
				$job_id = 0;
				while (($newArray = sql_fetch()) != false)
					$job_id = $newArray[0];

				if ($job_id)
				{
					$morelet = csvfield('MORELET');
					$jc_letter_more = strtolower($morelet);
					if ($jc_letter_more[0] == 'y')
						$jc_letter_more = 1;
					else
						$jc_letter_more = 0;
					if ($verbose)
						dprint("Job ID $job_id: MORELET=\"$morelet\", JC_LETTER_MORE=\"$jc_letter_more\"");
					$sql= "UPDATE JOB SET JC_LETTER_MORE=$jc_letter_more WHERE JOB_ID=$job_id";
					if ($verbose)
						dprint($sql);
					sql_execute($sql); # no need to audit
					$amt_count++;
				}
			}
			
			$input_count++;
			if (($input_count % 10000) == 0)
				dlog("import_jobs_ltr_more($sys): done $input_count input records so far");
			if ((0 < $stop_after) && ($stop_after < $amt_count))
			{
				#dlog("Stopping after $stop_after jobs");
				#break; # while(true)
			}
			
		} # while(true)

		$time_taken = 1.0 * (time() - $start_time) / 60.0;
		$per_rec = ($amt_count ? (60.0 * $time_taken / (1.0 * $amt_count / 1000.0)) : 0.0);
		dlog("Imported $amt_count MORELET from a $sys_txt file of " . ($input_count-1) . " jobs (and 1 header record).<br>" .
				"Time taken: $time_taken mins ($per_rec secs per 1000 jobs)");
		
		fclose($fp_csv);
	}
	else
		dlog("*=* (Sys=$sys) Failed to fopen(\"$dirname/$csv\",'r')");

	
} # import_jobs_ltr_more()

function import_trace_letters()
{
	# For trace jobs, update the JOB_LETTER.LETTER_TYPE_ID which is wrongly set to NULL by the import_jobs() function.
	# Trace letters: LETTER_TYPE_ID is 1 to 25. Collection letters: 26 to 90.
	# Each Trace job can have more than one letter, e.g. VilNo 567875 has 3 letters: trace, service and attend.
	# But it seems the DBV only has one letter.
	# So here, we will give the first letter type to the DBV letter although it might have been better to 
	# assign the last letter type to the DBV letter.
	# Z_JOB.LET1 et al indicate the letter type, so this information needs to be copied into the JOB_LETTER table
	# for the respective letter record.
	
	# Import 31/12/16:
	#	import_2017_01_01_1805.log
	#	SELECT LETTER_TYPE_ID, COUNT(*) FROM JOB_LETTER GROUP BY LETTER_TYPE_ID
	#	2017-01-01 18:24:21/1 Updated 47506 JOB_LETTER records from a total of 47506 letters.
	#	Inserted 3535 new JOB_LETTER records.
	#	There were 1518 JOB_LETTER records with no LET1 / LET2 / etc in old data.
	#	Time taken: 18.516666666667 mins (23.386519597525 secs per 1000 letters)
	
	
	# Updated 45086 JOB_LETTER records from a total of 45086 letters.
	#		Inserted 3280 new JOB_LETTER records.
	#		There were 1463 JOB_LETTER records with no LET1 / LET2 / etc in old data.
	#		Time taken: 17.75 mins (23.621523311006 secs per 1000 letters)
	
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
	#global $id_LETTER_TYPE_retrc1_foc; # settings.php
	global $id_LETTER_TYPE_retrc2_no; # settings.php
	global $id_LETTER_TYPE_retrc2_yes; # settings.php
	#global $id_LETTER_TYPE_retrc2_foc; # settings.php
	global $id_LETTER_TYPE_retrc3_no; # settings.php
	global $id_LETTER_TYPE_retrc3_yes; # settings.php
	#global $id_LETTER_TYPE_retrc3_foc; # settings.php
	global $id_LETTER_TYPE_tc_no; # settings.php
	global $id_LETTER_TYPE_tc_yes; # settings.php
	global $id_LETTER_TYPE_tm_no; # settings.php
	global $id_LETTER_TYPE_tm_yes; # settings.php
	global $id_LETTER_TYPE_attend_no; # settings.php
	global $id_LETTER_TYPE_attend_yes; # settings.php
	global $sqlNow; # settings.php
	global $sqlTrue; # settings.php

	$this_function = "import_trace_letters()";
	dprint("$this_function - Enter");
	
	$enabled = false; #
	if (!$enabled)
	{
		dprint("This import function is disabled", true);
		return;
	}
	
	print "<h3>Updating JOB_LETTER.LETTER_TYPE_ID and JL_POSTED_DT for Trace jobs...</h3>";
	
	$start_time = time();
	set_time_limit(60 * 60 * 8); # 8 hours
	
	# Find all letters for Trace jobs that have a NULL letter type
	$sql = "SELECT L.JOB_LETTER_ID, J.JOB_ID, J.JT_SUCCESS
			FROM JOB_LETTER AS L INNER JOIN JOB AS J ON J.JOB_ID=L.JOB_ID AND L.LETTER_TYPE_ID IS NULL AND J.JT_JOB=1
			ORDER BY J.JOB_ID, L.JOB_LETTER_ID";
	dprint($sql);#
	sql_execute($sql); # about 57,000 records
	$letters = array();
	while (($newArray = sql_fetch()) != false)
	{
		if (($newArray[2] == 1) || ($newArray[2] == -1))
			$success = 1;
		else
			$success = 0;
		$letters[$newArray[0]] = array($newArray[1], $success);
	}
	dprint("Found " . count($letters) . " letters");#
	
	$letter_count = 0; # number of JOB_LETTER records processed
	$update_count = 0; # number of JOB_LETTER records updated (not inserted)
	$insert_count = 0; # number of JOB_LETTER records inserted
	$no_letter = 0; # number of JOB_LETTER records that have no LET1, LET2 etc in old data.
	$test_count = 0;#
	
	foreach ($letters as $job_letter_id => $info)
	{
		list($job_id, $success) = $info;
		$updated_this_job = false;
		$found_yes = false;
		#if ((0 < $test_count) || ($letter_count < 10))
		#	dprint("* Job $job_id *");
		for ($x = 1; $x <= 11; $x++)
		{
			$sql = "SELECT Z_T_LET{$x}, Z_T_DATE{$x} FROM JOB_Z WHERE JOB_ID=$job_id";
			#if ((0 < $test_count) || ($letter_count < 10))
			#	dprint($sql);#
			sql_execute($sql);
			while (($newArray = sql_fetch()) != false)
			{
				$letx = strtolower(trim($newArray[0]));
				$datex = trim($newArray[1]);
			}
			if ($letx == 'yes')
			{
				# We have found the letter type for this job's letter.
				$found_yes = true;
				switch ($x)
				{
					case 1: $letter_type_id = ($success ? $id_LETTER_TYPE_trace_yes : $id_LETTER_TYPE_trace_no); break;
					case 2: $letter_type_id = ($success ? $id_LETTER_TYPE_means_yes : $id_LETTER_TYPE_means_no); break;
					case 3: $letter_type_id = ($success ? $id_LETTER_TYPE_repo_yes : $id_LETTER_TYPE_repo_no); break;
					case 4: $letter_type_id = ($success ? $id_LETTER_TYPE_other_yes : $id_LETTER_TYPE_other_no); break;
					case 5: $letter_type_id = ($success ? $id_LETTER_TYPE_serv_yes : $id_LETTER_TYPE_serv_no); break;
					case 6: $letter_type_id = ($success ? $id_LETTER_TYPE_retrc1_yes : $id_LETTER_TYPE_retrc1_no); break;
					case 7: $letter_type_id = ($success ? $id_LETTER_TYPE_retrc2_yes : $id_LETTER_TYPE_retrc2_no); break;
					case 8: $letter_type_id = ($success ? $id_LETTER_TYPE_retrc3_yes : $id_LETTER_TYPE_retrc3_no); break;
					case 9: $letter_type_id = ($success ? $id_LETTER_TYPE_tc_yes : $id_LETTER_TYPE_tc_no); break;
					case 10: $letter_type_id = ($success ? $id_LETTER_TYPE_tm_yes : $id_LETTER_TYPE_tm_no); break;
					case 11: $letter_type_id = ($success ? $id_LETTER_TYPE_attend_yes : $id_LETTER_TYPE_attend_no); break;
					default: $letter_type_id = 'NULL'; break;
				}
				# Test the date syntax
				$temp = date_to_epoch($datex);
				if (($temp != '') && (0 < $temp)) # not interested in dates before 1970
					$jl_posted_dt = "'{$datex}'";
				else
					$jl_posted_dt = 'NULL';
				
				if (!$updated_this_job)
				{
					# Set the letter type and posted date for the existing JOB_LETTER record
					$sql = "UPDATE JOB_LETTER SET LETTER_TYPE_ID=$letter_type_id, JL_POSTED_DT=$jl_posted_dt 
							WHERE JOB_LETTER_ID=$job_letter_id";
					if ((0 < $test_count) || ($letter_count < 10))
						dprint("Job $job_id: $sql");
					sql_execute($sql); # no need to audit
					$updated_this_job = true;
					$update_count++;
				}
				else
				{
					# Add a record to JOB_LETTER but without the letter text.
					$fields = "JOB_ID,  LETTER_TYPE_ID,  JL_ADDED_DT, JL_APPROVED_DT, JL_POSTED_DT,  IMPORTED";
					$values = "$job_id, $letter_type_id, $sqlNow,     '1977-01-01',   $jl_posted_dt, $sqlTrue";
					$sql = "INSERT INTO JOB_LETTER ($fields) VALUES ($values)";
					if ((0 < $test_count) || ($letter_count < 10))
						dprint("Job $job_id: $sql");
					sql_execute($sql);
					$insert_count++;
				}
			}
		} # for($x)
		
		$letter_count++;
		if (!$found_yes)
		{
			$no_letter++;
			if ((0 < $test_count) || ($letter_count < 10))
				dprint("Job $job_id: No LET1/LET2/etc found.");
			
			# Let's assume this one is a trace letter
			$letter_type_id = ($success ? $id_LETTER_TYPE_trace_yes : $id_LETTER_TYPE_trace_no);
			$sql = "UPDATE JOB_LETTER SET LETTER_TYPE_ID=$letter_type_id, JL_POSTED_DT=NULL
					WHERE JOB_LETTER_ID=$job_letter_id";
			if ((0 < $test_count) || ($letter_count < 10))
				dprint("Job $job_id: $sql");
			sql_execute($sql); # no need to audit
			$updated_this_job = true;
			$update_count++;
		}
		if (($letter_count % 10000) == 0)
			dlog("import_trace_letters(): done $letter_count input letters so far");
		
		if ((0 < $test_count) && ($test_count < $letter_count))
			break;
	} # foreach ($letters)
	
	$time_taken = 1.0 * (time() - $start_time) / 60.0;
	$per_rec = ($letter_count ? (60.0 * $time_taken / (1.0 * $letter_count / 1000.0)) : 0.0);
	dlog("Updated $update_count JOB_LETTER records from a total of $letter_count letters.<br>" .
			"Inserted $insert_count new JOB_LETTER records.<br>" .
			"There were $no_letter JOB_LETTER records with no LET1 / LET2 / etc in old data.<br>" .
			"Time taken: $time_taken mins ($per_rec secs per 1000 letters)");
	dprint("$this_function - Exit");
} # import_trace_letters()

function set_job_closed()
{
	# Set JOB.JOB_CLOSED from other JOB fields.
	# Derived from a combination of J_COMPLETE, J_CLOSED_DT, JC_JOB/JC_JOB_STATUS_ID (but not JT_JOB/JT_SUCCESS).
	
	# Import 31/12/16:
	#	import_2017_01_01_1829.log
	#	1: SELECT COUNT(*) FROM JOB
	#	1: count_all = 520739
	#	2: SELECT COUNT(*) FROM JOB WHERE (JOB_CLOSED=0)
	#	2: count_open = 520739
	#	3: SELECT COUNT(*) FROM JOB WHERE (JOB_CLOSED=0) AND (JT_JOB=1)
	#	3: count_open_t = 47548
	#	4: SELECT COUNT(*) FROM JOB WHERE (JOB_CLOSED=0) AND (JC_JOB=1)
	#	4: count_open_c = 473191
	#	5: neither t nor c = 0
	#	6: UPDATE JOB SET JOB_CLOSED=1 WHERE (JOB_CLOSED=0) AND (JT_JOB=1) AND ( (J_COMPLETE=1) OR ((J_CLOSED_DT IS NOT NULL) AND (J_CLOSED_DT <> '')) )
	#	6: SELECT COUNT(*) FROM JOB WHERE (JOB_CLOSED=0) AND (JT_JOB=1)
	#	6: count_open_t2 = 37 (reduction of 47511)
	#	7: UPDATE JOB SET JOB_CLOSED=1 WHERE (JOB_CLOSED=0) AND (JC_JOB=1) AND ( ((J_CLOSED_DT IS NOT NULL) AND (J_CLOSED_DT <> '')) )
	#	7: SELECT COUNT(*) FROM JOB WHERE (JOB_CLOSED=0) AND (JC_JOB=1)
	#	7: count_open_c2 = 46255 (reduction of 426936)
	#	8: SELECT COUNT(*) FROM JOB
	#	8: SELECT COUNT(*) FROM JOB WHERE JOB_CLOSED=0
	#	8: SELECT COUNT(*) FROM JOB WHERE JOB_CLOSED=1
	#	8: count_all = 520739, count_open = 46292, count_closed = 474447 (sum 520739)

	
	global $sqlFalse;
	global $sqlTrue;
	
	# --- 1 ---
	
	$count_all = 0;
	$sql = "SELECT COUNT(*) FROM JOB";
	dprint("1: $sql");
	sql_execute($sql);
	while (($newArray = sql_fetch()) != false)
		$count_all = $newArray[0];
	dprint("1: count_all = $count_all");
	
	# --- 2 ---
	
	$count_open = 0;
	$sql = "SELECT COUNT(*) FROM JOB WHERE (JOB_CLOSED=$sqlFalse)";
	dprint("2: $sql");
	sql_execute($sql);
	while (($newArray = sql_fetch()) != false)
		$count_open = $newArray[0];
	dprint("2: count_open = $count_open");
	
	# --- 3 ---
	
	$count_open_t = 0;
	$sql = "SELECT COUNT(*) FROM JOB WHERE (JOB_CLOSED=$sqlFalse) AND (JT_JOB=$sqlTrue)";
	dprint("3: $sql");
	sql_execute($sql);
	while (($newArray = sql_fetch()) != false)
		$count_open_t = $newArray[0];
	dprint("3: count_open_t = $count_open_t");
	
	# --- 4 ---
	
	$count_open_c = 0;
	$sql = "SELECT COUNT(*) FROM JOB WHERE (JOB_CLOSED=$sqlFalse) AND (JC_JOB=$sqlTrue)";
	dprint("4: $sql");
	sql_execute($sql);
	while (($newArray = sql_fetch()) != false)
		$count_open_c = $newArray[0];
	dprint("4: count_open_c = $count_open_c");
	
	# --- 5 ---
	
	dprint("5: neither t nor c = " . ($count_open - $count_open_t - $count_open_c));
	
	# --- 6 ---
	
	$sql = "UPDATE JOB SET JOB_CLOSED=$sqlTrue WHERE (JOB_CLOSED=$sqlFalse) AND (JT_JOB=$sqlTrue) AND 
			(  (J_COMPLETE=1) OR ((J_CLOSED_DT IS NOT NULL) AND (J_CLOSED_DT <> ''))  )";
	dprint("6: $sql");
	sql_execute($sql);
	
	$count_open_t2 = 0;
	$sql = "SELECT COUNT(*) FROM JOB WHERE (JOB_CLOSED=$sqlFalse) AND (JT_JOB=$sqlTrue)";
	dprint("6: $sql");
	sql_execute($sql);
	while (($newArray = sql_fetch()) != false)
		$count_open_t2 = $newArray[0];
	dprint("6: count_open_t2 = $count_open_t2 (reduction of " . ($count_open_t - $count_open_t2) . ")");
	
	# --- 7 ---
	
	$use_statuses = false;
	$statuses = array();
	if ($use_statuses)
	{
		$sql = "SELECT JOB_STATUS_ID FROM JOB_STATUS_SD WHERE J_STTS_CLOSED=$sqlTrue";
		dprint("7: $sql");
		sql_execute($sql);
		while (($newArray = sql_fetch()) != false)
			$statuses[] = $newArray[0];
		dprint("7: statuses = " . print_r($statuses,1));
	}	
	$sql = "UPDATE JOB SET JOB_CLOSED=$sqlTrue WHERE (JOB_CLOSED=$sqlFalse) AND (JC_JOB=$sqlTrue) AND 
			(	((J_CLOSED_DT IS NOT NULL) AND (J_CLOSED_DT <> '')) 
				" . ($use_statuses ? (" OR (JC_JOB_STATUS_ID IN (" . implode(',', $statuses) . "))") : '') . "
			)";
			#(J_COMPLETE=1) OR 
	dprint("7: $sql");
	sql_execute($sql);
	
	$count_open_c2 = 0;
	$sql = "SELECT COUNT(*) FROM JOB WHERE (JOB_CLOSED=$sqlFalse) AND (JC_JOB=$sqlTrue)";
	dprint("7: $sql");
	sql_execute($sql);
	while (($newArray = sql_fetch()) != false)
		$count_open_c2 = $newArray[0];
	dprint("7: count_open_c2 = $count_open_c2 (reduction of " . ($count_open_c - $count_open_c2) . ")");
	
	# --- 8 ---

	$count_all = 0;
	$sql = "SELECT COUNT(*) FROM JOB";
	dprint("8: $sql");
	sql_execute($sql);
	while (($newArray = sql_fetch()) != false)
		$count_all = $newArray[0];

	$sql = "SELECT COUNT(*) FROM JOB WHERE JOB_CLOSED=$sqlFalse";
	dprint("8: $sql");
	sql_execute($sql);
	while (($newArray = sql_fetch()) != false)
		$count_open = $newArray[0];
	
	$sql = "SELECT COUNT(*) FROM JOB WHERE JOB_CLOSED=$sqlTrue";
	dprint("8: $sql");
	sql_execute($sql);
	while (($newArray = sql_fetch()) != false)
		$count_closed = $newArray[0];
	
	dprint("8: count_all = $count_all, count_open = $count_open, count_closed = $count_closed (sum " . ($count_open + $count_closed) . ")");
	
} # set_job_closed()

function set_review_dt()
{
	set_time_limit(10 * 60); # 10 mins

	# Import 31/12/16:
	#	import_2017_01_01_1832.log
	#	Found 54 jobs
	
	$sql = "SELECT JOB_ID, JC_REVIEW_D_OLD FROM JOB 
			WHERE (JC_REVIEW_D_OLD IS NOT NULL) AND (JC_REVIEW_D_OLD <> 0) AND (JC_REVIEW_DT IS NULL)
			ORDER BY JOB_ID";
	sql_execute($sql);
	$jobs = array();
	while (($newArray = sql_fetch()) != false)
		$jobs[$newArray[0]] = $newArray[1];
	dprint("Found " . count($jobs) . " jobs");
	
	$count = 0;
	foreach ($jobs as $id => $mmyy)
	{
		$dt = date_from_mmyy($mmyy);
		$dt = ($dt ? "'$dt'" : "NULL");
		$sql = "UPDATE JOB SET JC_REVIEW_DT=$dt WHERE JOB_ID=$id";
		#if ($count < 20)
		#	dprint($sql);
		sql_execute($sql, false); # no need to audit
		$count++;
	}
} # set_review_dt()

function set_inv_paid()
{
	# This function needs to be run more than once, until it has finished the whole job.
	# We need to this manually before the first run:
	# UPDATE INVOICE SET INV_PAID=NULL
	#$sql = "UPDATE INVOICE SET INV_PAID=NULL";
	#sql_execute($sql);
	
	set_time_limit(4 * 60); # 4 mins

	# Import 31/12/16:
	#	import_2017_01_01_1833.log
	#	2017-01-01 18:33:35/1 There are 0 invoices that need processing
	#	2017-01-01 18:33:35/1 Will process 0 invoices
	#	2017-01-01 18:33:35/1 Updated 0 invoices
	
	
	$where = "WHERE (INV_PAID IS NULL) AND (0 < INV_NET)";
	
	$sql = "SELECT COUNT(*) FROM INVOICE $where";
	sql_execute($sql);
	$count = 0;
	while (($newArray = sql_fetch()) != false)
		$count = $newArray[0];
	dlog("There are $count invoices that need processing");

	$max = 30000;
	if ($count < $max)
		$top = '';
	else
		$top = "TOP $max";
	$sql = "SELECT $top INVOICE_ID, INV_RX FROM INVOICE $where";
	sql_execute($sql);
	$invoices = array();
	while (($newArray = sql_fetch()) != false)
		$invoices[$newArray[0]] = 1.0 * $newArray[1];
	dlog("Will process " . count($invoices) . " invoices");

	$count = 0;	
	foreach ($invoices as $id => $rx)
	{
		$sql = "SELECT SUM(COALESCE(AL_AMOUNT,0.0)) FROM INV_ALLOC WHERE INVOICE_ID=$id";
		sql_execute($sql);
		$alloc = 0.0;
		while (($newArray = sql_fetch()) != false)
			$alloc += (1.0 * $newArray[0]);
		
		$paid = max($rx, $alloc);
		$sql = "UPDATE INVOICE SET INV_PAID=$paid WHERE INVOICE_ID=$id";
		sql_execute($sql);
		$count++;
	}
	dlog("Updated $count invoices");
	
} # set_inv_paid()

function set_target_dt()
{
	$enabled = false; #
	if (!$enabled)
	{
		dprint("This import function is disabled", true);
		return;
	}
	
	$sql = "UPDATE JOB SET J_TARGET_DT=DATEADD(DAY,30,J_OPENED_DT)";
	dprint($sql);
	sql_execute($sql);
}

function set_job_groups()
{
	# Import 31/12/16:
	#	import_2016_12_31_1738.log
	#	2016-12-31 17:38:28/   POST=Array
	#	(
	#	    [task] => i_job_groups
	#	    [tc] => *
	#	    [other] => 
	#	)
	
	$enabled = false; #
	if (!$enabled)
	{
		dprint("This import function is disabled", true);
		return;
	}
	
	$sql = "SELECT J_VILNO, COUNT(*) FROM JOB GROUP BY J_VILNO HAVING (1 < COUNT(*)) ORDER BY COUNT(*) DESC";
	sql_execute($sql);
	$vilnos = array();
	while (($newArray = sql_fetch()) != false)
		$vilnos[1 * $newArray[0]] = 1 * $newArray[1];
	#dprint("vilnos=" . print_r($vilnos,1));#

	foreach ($vilnos as $j_vilno => $count)
	{
		$sql = "SELECT JOB_ID FROM JOB WHERE J_VILNO=$j_vilno";
		sql_execute($sql);
		$jobs = array();
		while (($newArray = sql_fetch()) != false)
			$jobs[] = $newArray[0];
		
		if (0 < count($jobs))
		{
			$sql = "INSERT INTO JOB_GROUP (JG_NAME) VALUES ('$j_vilno')";
			$group_id = sql_execute($sql);
			$sql = "UPDATE JOB SET JOB_GROUP_ID=$group_id WHERE JOB_ID IN (" . implode(',', $jobs) . ")";
			dprint($sql);
			sql_execute($sql); # no need to audit
		}
		$count=$count; # keep code-checker quiet
	}
	
} # set_job_groups()

function init_job_reports()
{
	$sql = "UPDATE JOB SET JT_LET_REPORT=NULL"; # Update all jobs not just Trace jobs
	dprint($sql);
	sql_execute($sql);
}

function import_job_reports()
{
	# Before calling this function:
	#	- Import jobs
	# Required:
	#	t/TRACES.csv
	
	# Import Trace jobs' REP1, REP2, etc into JOB.JT_LET_REPORT.
	
	# Import 31/12/16:
	#	import_2016_12_31_1742.log
	#	2016-12-31 17:42:19/   POST=Array
	#	(
	#	    [task] => i_job_reports
	#	    [tc] => t
	#	    [other] => 
	#	)
	#	016-12-31 17:44:45/1 Imported 45675 reports (from 45675 jobs that had a report) from a Traces file of 47549 jobs (and 1 header record).
	#	Time taken: 2.4333333333333 mins (3.1964969896004 secs per 1000 jobs)
	
	# Imported 43307 reports (from 43307 jobs that had a report) from a Traces file of 45159 jobs (and 1 header record).
	# Time taken: 0.91666666666667 mins (1.2700025400051 secs per 1000 jobs)
	
	# 2016-09-27 18:03:16/1 Imported 43307 reports (from 43307 jobs that had a report) from a Traces file of 45159 jobs (and 1 header record).
	# Time taken: 2.35 mins (3.2558246934676 secs per 1000 jobs)

	global $col_nums; # for csvfield()
	global $crlf;
	global $input_count; # for csvfield()
	global $j_sequence; # for csvfield()
	global $one_record; # for csvfield()
	global $tc;
		
	$enabled = false; #
	if (!$enabled)
	{
		dprint("This import function is disabled", true);
		return;
	}
	if ($tc != 't')
	{
		dlog("*=* import_job_reports() - illegal tc \"$tc\"");
		return;
	}

	print "<h3>Importing Job Reports (Trace system)</h3>";
	
	$start_time = time();
	set_time_limit(60 * 60 * 8); # 8 hours
	
	init_job_reports();
	
	$stop_after = 0;#
	$stop_margin = 0;
	$input_count = 0;
	
	$sys = 't';
	$dirname = "import-vilcol/{$sys}";
	$sys_txt = "Traces";
	$csv = "TRACES.csv";

	$fp_csv = fopen("$dirname/$csv", 'r');
	if ($fp_csv)
	{
		sql_encryption_preparation('JOB');
		
		$col_nums = array();
		
		$job_count = 0;
		$report_count = 0;
		
		$while_safety = 0; # to avoid infinite-looping
		while (true)
		{
			$verbose = false;
			if ((0 < $stop_after) && ($input_count <= $stop_after) && (($stop_after - $input_count) < $stop_margin))
				$verbose = true;
			
			$while_safety++;
			if ($while_safety > 800000) # 800,000 jobs
			{
				dlog("*=* (Sys=$sys) Safety Ejection!");
				break; # while(true)
			}

			if ($verbose)
				dprint("=== Record $input_count ===");
				
			$one_record = fgetcsv($fp_csv);
			if ($input_count > 0) # skip header record
			{
				if ($one_record && (count($one_record) > 0)) # test for end-of-records
				{
					$seq_raw = csvfield('SEQUENCE');
					$j_sequence = 1 * $seq_raw;
				}
				else 
				{
					dlog("End of Jobs");
					break; # while(true)
				}
				
				$old_verbose = $verbose;
				if (($j_sequence == 90149542) || ($j_sequence == 90493801)
						#|| (($one_record[0] == -1) && (trim($one_record[8]) == '54B MORAY ROAD')))
						|| in_fix_aux_list($one_record))
				{
					#$verbose = true; #
					if ($verbose)
						dlog("*=* Found sequence=$j_sequence. col_nums:<br>" . print_r($col_nums,1) . "<br>" . 
								"Record:<br>" . print_r($one_record,1));
					for ($ii = 88; 36 <= $ii; $ii--)
						$one_record[$ii] = $one_record[$ii-4];
					$bits = explode(',', str_replace('"', '', $one_record[31]));
					$one_record[31] = str_replace('\\', '', $bits[0]);
					$one_record[32] = $bits[1];
					$one_record[33] = $bits[2];
					$one_record[34] = $bits[3];
					$one_record[35] = $bits[4];
					if ($verbose)
						dlog("Cleaned Record:<br>" . print_r($one_record,1));
				}
				elseif ($j_sequence == 730508)
				{
					#$verbose = true; #
					if ($verbose)
					{
						dlog("*=* Found sequence=$j_sequence. col_nums:<br>" . print_r($col_nums,1) . "<br>" . 
								"Record:<br>" . print_r($one_record,1));
					}
					$one_record[70] = str_replace('foren\",m', 'forenm', $one_record[70]);
					$one_record[71] = str_replace('"', '', $one_record[71]);
					if ($verbose)
						dlog("Cleaned:<br>" . print_r($one_record,1));
				}
				elseif ($j_sequence == 90503059)
				{
					#$verbose = true; #
					if ($verbose)
						dlog("*=* Found sequence=$j_sequence. col_nums:<br>" . print_r($col_nums,1) . "<br>" . 
								"Record:<br>" . print_r($one_record,1));
					for ($ii = 88; 15 <= $ii; $ii--)
						$one_record[$ii] = $one_record[$ii-1];
					$bits = explode(',', str_replace('"', '', $one_record[13]));
					$one_record[13] = str_replace('\\', '', $bits[0]);
					$one_record[14] = $bits[1];
					if ($verbose)
						dlog("Cleaned Record:<br>" . print_r($one_record,1));
				}
				elseif (($j_sequence == 90694238) || ($j_sequence == 90700518))
				{
					#$verbose = true; #
					if ($verbose)
						dlog("*=* Found sequence=$j_sequence. col_nums:<br>" . print_r($col_nums,1) . "<br>" . 
								"Record:<br>" . print_r($one_record,1));
					for ($ii = 88; 28 <= $ii; $ii--)
						$one_record[$ii] = $one_record[$ii-7];
					$bits = explode(',', str_replace('"', '', $one_record[20]));
					$one_record[20] = str_replace('\\', '', $bits[0]);
					$one_record[21] = $bits[1];
					$one_record[22] = $bits[2];
					$one_record[23] = $bits[3];
					$one_record[24] = $bits[4];
					$one_record[25] = $bits[5];
					$one_record[26] = $bits[6];
					$one_record[27] = $bits[7];
					if ($verbose)
						dlog("Cleaned Record:<br>" . print_r($one_record,1));
				}
				elseif (($j_sequence == 90707120) || ($j_sequence == 90738552))
				{
					#$verbose = true; #
					if ($verbose)
						dlog("*=* Found sequence=$j_sequence. col_nums:<br>" . print_r($col_nums,1) . "<br>" . 
								"Record:<br>" . print_r($one_record,1));
					$vlf = $one_record[81];
					if ($verbose)
						dlog("VLF=\"$vlf\"");
					for ($ii = 87; 28 <= $ii; $ii--)
						$one_record[$ii] = $one_record[$ii-7];
					$bits = explode(',', str_replace('"', '', $one_record[20]));
					$one_record[20] = str_replace('\\', '', $bits[0]);
					$one_record[21] = $bits[1];
					$one_record[22] = $bits[2];
					$one_record[23] = $bits[3];
					$one_record[24] = $bits[4];
					$one_record[25] = $bits[5];
					$one_record[26] = $bits[6];
					$one_record[27] = $bits[7];
					$one_record[88] = $vlf;
					if ($verbose)
						dlog("Cleaned Record:<br>" . print_r($one_record,1));
				}
				elseif (($j_sequence == 90714469) || ($j_sequence == 90717737))
				{
					#$verbose = true; #
					if ($verbose)
						dlog("*=* Found sequence=$j_sequence. col_nums:<br>" . print_r($col_nums,1) . "<br>" . 
								"Record:<br>" . print_r($one_record,1));
					$vlf = $one_record[87];
					if ($verbose)
						dlog("VLF=\"$vlf\"");
					for ($ii = 87; 28 <= $ii; $ii--)
						$one_record[$ii] = $one_record[$ii-1];
					$bits = explode(',', str_replace('"', '', $one_record[26]));
					$one_record[26] = str_replace('\\', '', $bits[0]);
					$one_record[27] = $bits[1];
					$one_record[88] = $vlf;
					if ($verbose)
						dlog("Cleaned Record:<br>" . print_r($one_record,1));
				}
				elseif ($j_sequence == 90723905)
				{
					#$verbose = true; #
					if ($verbose)
						dlog("*=* Found sequence=$j_sequence. col_nums:<br>" . print_r($col_nums,1) . "<br>" . 
								"Record:<br>" . print_r($one_record,1));
					$vlf = $one_record[83];
					if ($verbose)
						dlog("VLF=\"$vlf\"");
					for ($ii = 87; 26 <= $ii; $ii--)
						$one_record[$ii] = $one_record[$ii-5];
					$bits = explode(',', str_replace('"', '', $one_record[20]));
					$one_record[20] = str_replace('\\', '', $bits[0]);
					$one_record[21] = $bits[1];
					$one_record[22] = $bits[2];
					$one_record[23] = $bits[3];
					$one_record[24] = $bits[4];
					$one_record[25] = $bits[5];
					$one_record[88] = $vlf;
					if ($verbose)
						dlog("Cleaned Record:<br>" . print_r($one_record,1));
				}
				#if ($j_sequence != 90707120)#
				#	continue;
				$verbose = $old_verbose;
			}
				
			if ((0 < $stop_after) && ($stop_after < $input_count))
			{
				$input_count++;
				continue;
			}
						
			if ($verbose)
				dprint((($input_count == 0) ? "Headers: " : "Job: ") . print_r($one_record,1));#
			if ($input_count == 0)
			{
				# Header record
				foreach ($one_record as $num => $field)
					$col_nums[$field] = $num;
				if ($verbose)
					dprint("col_nums: " . print_r($col_nums,1));#
			}
			elseif (0 < $j_sequence)
			{
				# Data record
				
				$report = csvfield('REP1'); # agent's report from REP1 to REP9; see also $jl_text
				if (csvfield('REP2'))
					$report .= (($report ? $crlf : '') . csvfield('REP2'));
				if (csvfield('REP3'))
					$report .= (($report ? $crlf : '') . csvfield('REP3'));
				if (csvfield('REP4'))
					$report .= (($report ? $crlf : '') . csvfield('REP4'));
				if (csvfield('REP5'))
					$report .= (($report ? $crlf : '') . csvfield('REP5'));
				if (csvfield('REP6'))
					$report .= (($report ? $crlf : '') . csvfield('REP6'));
				if (csvfield('REP7'))
					$report .= (($report ? $crlf : '') . csvfield('REP7'));
				if (csvfield('REP8'))
					$report .= (($report ? $crlf : '') . csvfield('REP8'));
				if (csvfield('REP9'))
					$report .= (($report ? $crlf : '') . csvfield('REP9'));
				if (csvfield('REP10'))
					$report .= (($report ? $crlf : '') . csvfield('REP10'));
				if (csvfield('REP11'))
					$report .= (($report ? $crlf : '') . csvfield('REP11'));
				if ($report)
				{
					$job_count++;
					
					if ($verbose)
						dlog("Report (REP1 to REP11):<br>$report");
					
					$sql = "SELECT JOB_ID FROM JOB WHERE J_SEQUENCE=$j_sequence";
					sql_execute($sql);
					$job_id = 0;
					while (($newArray = sql_fetch()) != false)
						$job_id = 1 * $newArray[0];
					
					if (0 < $job_id)
					{
						$jt_let_report = sql_encrypt(str_replace('  ', ' ', str_replace($crlf, ' ', $report)), false, 'JOB');
						$sql = "UPDATE JOB SET JT_LET_REPORT=$jt_let_report WHERE JOB_ID=$job_id";
						if ($verbose)
							dlog($sql);
						sql_execute($sql);
						$report_count++;
					}
					else
						dlog("Job ID not found from sequence $j_sequence");
				}
									
			} # elseif (0 < $j_sequence)
			
			$input_count++;
			if ((0 < $stop_after) && ($stop_after < $input_count))
			{
				dlog("Stopping after $stop_after jobs");
				#break; # while(true)
			}
			
		} # while(true)

		$time_taken = 1.0 * (time() - $start_time) / 60.0;
		$per_rec = 60.0 * $time_taken / (1.0 * $job_count / 1000.0);
		dlog("Imported $report_count reports (from $job_count jobs that had a report) from a $sys_txt file of " . ($input_count-1) . " jobs (and 1 header record).<br>" .
				"Time taken: $time_taken mins ($per_rec secs per 1000 jobs)");
		
		fclose($fp_csv);
	}
	else
	{
		if (!$fp_csv)
			dlog("*=* (Sys=$sys) Failed to fopen(\"$dirname/$csv\",'r')");
	}
	
} # import_job_reports()

function set_pay_percent()
{
	# After import, about 10% of collection payments have a zero commission percentage.
	# For those, set the percentage to the client record's percentage
	
	# Import 31/12/16:
	#	import_2017_01_01_1905.log
	#	Found 51601 payments
	
	
	# 17/11/16:
	#	Found 53053 payments
	
	$sql = "SELECT P.JOB_PAYMENT_ID, J.JC_PERCENT, C.COMM_PERCENT
			FROM JOB_PAYMENT AS P
			INNER JOIN JOB AS J ON J.JOB_ID=P.JOB_ID
			INNER JOIN CLIENT2 AS C ON C.CLIENT2_ID=J.CLIENT2_ID
			WHERE P.COL_PERCENT IS NULL OR P.COL_PERCENT=0.0";
	dprint($sql);
	sql_execute($sql);
	$payments = array();
	while (($newArray = sql_fetch()) != false)
	{
		$id = $newArray[0];
		$pc = 1.0 * $newArray[1];
		if ($pc == 0.0)
			$pc = 1.0 * $newArray[2];
		$payments[] = array($id, $pc);
	}
	if ($payments)
	{
		dprint("Found " . count($payments) . " payments");
		foreach ($payments as $one)
		{
			$pc = 1.0 * $one[1];
			if ($pc)
			{
				$sql = "UPDATE JOB_PAYMENT SET COL_PERCENT=$pc WHERE JOB_PAYMENT_ID={$one[0]}";
				#dprint($sql);
				sql_execute($sql); # no need to audit
			}
			#else
			#	dprint("Client has zero percent");
		}
	}
	else
		dprint("No payments found");
	
} # set_pay_percent()

function set_pay_percent_adj()
{
	# After import, about 10% of collection payments have a zero commission percentage.
	# For adjustments (rather than payments), set the percentage to zero.
	
	# Import 31/12/16:
	#	Actually done on 03/01/17.
	#	
	
	global $id_ROUTE_cspent; # COL_PAYMENT_ROUTE_ID for "adjustment"
	
	$sql = "UPDATE JOB_PAYMENT SET COL_PERCENT=0.0 WHERE COL_PAYMENT_ROUTE_ID=$id_ROUTE_cspent";
	dprint($sql);
	sql_execute($sql);
	
} # set_pay_percent_adj()

function set_j_available()
{
	# Import 31/12/16:
	#	import_2017_01_01_1907.log
	#	Count = 46289
	
	$sql = "SELECT COUNT(*) FROM JOB WHERE OBSOLETE=0 AND JOB_CLOSED=0 AND J_AVAILABLE=0 AND 0 < J_USER_ID";
	dprint($sql);
	sql_execute($sql);
	$count = 0;
	while (($newArray = sql_fetch()) != false)
		$count = $newArray[0];
	dprint("Count = $count");
	
	$sql = "UPDATE JOB SET J_AVAILABLE=1 WHERE OBSOLETE=0 AND JOB_CLOSED=0 AND J_AVAILABLE=0 AND 0 < J_USER_ID";
	dprint($sql);
	sql_execute($sql);
	
} # set_j_available()

function init_jobs_incr_c()
{
	# Called from import_jobs() when incremental import (of collection jobs) is done.
	# Find the most recently added collection job to the JOB table and delete it and all of its associated table records.
	# Then import_jobs() will skip all records before that one from the input file.
	
	global $incr_sequence;
	global $incr_vilno;
	global $sqlTrue;
	
	$sql = "SELECT COUNT(*) FROM JOB WHERE JC_JOB=$sqlTrue";
	$count = 1 * sql_select_single($sql);
	dlog("init_jobs_incr_c(): found $count Collect jobs from: $sql");
	
	$sql = "SELECT TOP 1 JOB_ID, J_VILNO, J_SEQUENCE FROM JOB WHERE JC_JOB=$sqlTrue ORDER BY JOB_ID DESC";
	sql_execute($sql);
	$job_id = 0;
	$j_vilno = 0;
	$j_sequence = 0;
	while (($newArray = sql_fetch()) != false)
	{
		$job_id = $newArray[0];
		$j_vilno = $newArray[1];
		$j_sequence = $newArray[2];
	}
	if ($job_id <= 0)
	{
		dlog("init_jobs_incr_c(): last job not found from $sql");
		return -1;
	}
	dlog("init_jobs_incr_c(): found last job (JOB_ID $job_id, J_VILNO $j_vilno, J_SEQUENCE $j_sequence) from: $sql");

	$sql = "DELETE FROM JOB_Z WHERE JOB_ID=$job_id";
	dlog("init_jobs_incr_c(): $sql");
	sql_execute($sql); # no need to audit
	
	$sql = "DELETE FROM JOB_LETTER WHERE JOB_ID=$job_id";
	dlog("init_jobs_incr_c(): $sql");
	sql_execute($sql); # no need to audit
	
	$sql = "DELETE FROM JOB_PHONE WHERE JOB_ID=$job_id";
	dlog("init_jobs_incr_c(): $sql");
	sql_execute($sql); # no need to audit
	
	$sql = "DELETE FROM JOB_NOTE WHERE JOB_ID=$job_id";
	dlog("init_jobs_incr_c(): $sql");
	sql_execute($sql); # no need to audit
	
	$sql = "DELETE FROM JOB_SUBJECT WHERE JOB_ID=$job_id";
	dlog("init_jobs_incr_c(): $sql");
	sql_execute($sql); # no need to audit
	
	$sql = "DELETE FROM JOB WHERE JOB_ID=$job_id";
	dlog("init_jobs_incr_c(): $sql");
	sql_execute($sql); # no need to audit

	$incr_vilno = $j_vilno;
	$incr_sequence = $j_sequence;
	return 0; # success
	
} # init_jobs_incr_c()

function create_feedback()
{
	
	if (sql_table_exists('FEEDBACK'))
	{
		dprint("FEEDBACK table already exists!");
		return;
	}

	$sql = "CREATE TABLE [dbo].[FEEDBACK](
				[FEEDBACK_ID] [int] IDENTITY(1,1) NOT NULL,
				[F_ADDED_ID] [int] NOT NULL,
				[F_ADDED_DT] [datetime] NOT NULL,
				[F_NATURE] [int] NULL,
				[F_TEXT] [varchar](4000) NULL,
				[F_RESPONSE] [varchar](4000) NULL,
				[F_RESOLVED_DT] [datetime] NULL,
			 CONSTRAINT [PK_FEEDBACK] PRIMARY KEY CLUSTERED 
			(
				[FEEDBACK_ID] ASC
			)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON) ON [PRIMARY]
			) ON [PRIMARY]
		";
	dprint((strlen($sql) > 100) ? (substr($sql, 0, 100) . "...") : $sql);
	sql_execute($sql); # no need to audit

} # create_feedback()

function set_inv_due_dt()
{
	# 06/03/17 - Feedback #192, Steve wants due date to be one calendar month after invoice date.
	
	global $sqlFalse;
	
	$sql = "SELECT INVOICE_ID, INV_DT FROM INVOICE
			WHERE (IMPORTED=$sqlFalse) AND (OBSOLETE=$sqlFalse) AND (INV_TYPE='I') AND (INV_PAID < (INV_NET+INV_VAT))
			";
	dprint($sql);
	sql_execute($sql);
	$invs = array();
	while (($newArray = sql_fetch()) != false)
		$invs[] = $newArray;
	dprint("Found " . count($invs) . " invoices");
	
	$count = 0;
	foreach ($invs as $one)
	{
		if ($one[0] && $one[1])
		{
			$issued_ep = date_to_epoch($one[1]);
			$due_ep = date_add_months_kdb($issued_ep, 1); # add on one month
			$due_dt = date_from_epoch(true, $due_ep, false, false, true);
			$sql = "UPDATE INVOICE SET INV_DUE_DT='$due_dt' WHERE (INVOICE_ID={$one[0]})";
			if ($count < 10)
				dprint($sql);
			sql_execute($sql);
			$count++;
		}
	}
	dprint("Updated $count invoices");

//	# For all imported invoices where the INV_DUE_DT is '2020-01-01', set it to one month after the invoice date.
//
//	# Import 31/12/16:
//	#	import_2017_01_01_1910.log
//	#	no output
//	
//	$sql = "UPDATE INVOICE SET INV_DUE_DT=DATEADD(day,30,INV_DT)
//			WHERE (INV_DUE_DT = '2020-01-01') AND (IMPORTED=1) AND (INV_TYPE='I') AND ('1980-01-01'<=INV_DT) AND (INV_DT<='2016-12-31')
//			";
//	dprint($sql);
//	sql_execute($sql);
//	
//	$sql = "UPDATE INVOICE SET INV_DUE_DT=NULL WHERE (INV_TYPE<>'I')
//			";
//	dprint($sql);
//	sql_execute($sql);
	
} # set_inv_due_dt()

function set_inv_paid_method_2()
{
	# Before using this function, do the following:
	#		UPDATE INVOICE SET INV_PAID=0.0
	# Then run this function repeatedly until there are no records that needed updating.
	# It takes about 4 mins each time.
	
	# Import 31/12/16:
	#	UPDATE INVOICE SET INV_PAID=0.0
	#	(221590 row(s) affected)
	#	First run:
	#	import_2017_01_01_1836.log
	#	2017-01-01 18:36:54/   POST=Array
	#	(
	#		[task] => i_inv_paid_2
	#		[tc] => *
	#		[other] => 
	#	)
	#	Found 50000 invoices with IDs ranging from 1 to 54703.
	#	Out of 50000 invoices, updated 49997
	#	Second run:
	#	import_2017_01_01_1847.log
	#	Found 50000 invoices with IDs ranging from 20038 to 113298.
	#	Out of 50000 invoices, updated 49987
	#	Third run:
	#	import_2017_01_01_1856.log
	#	Found 50000 invoices with IDs ranging from 20038 to 183375.
	#	Out of 50000 invoices, updated 49963
	#	Fourth run:
	#	import_2017_01_01_1900.log
	#	2017-01-01 19:00:50/1 Found 32140 invoices with IDs ranging from 20038 to 221590.
	#	2017-01-01 19:03:08/1 Out of 32140 invoices, updated 31264
	#	Fifth run:
	#	import_2017_01_01_1903.log
	#	2017-01-01 19:03:32/1 Found 876 invoices with IDs ranging from 20038 to 221590.
	#	2017-01-01 19:03:33/1 Out of 876 invoices, updated 0
	#	Sixth run:
	#	-same as fifth run
	
	
	# For all invoices.
	# For each invoice, find all allocations of receipts to that invoice, sum the allocation amounts, and set INVOICE.INV_PAID to that sum.
	# This should be more reliable than set_inv_paid()

	set_time_limit(60 * 60); # 1 hour
	
	$sql = "SELECT TOP 50000 INVOICE_ID, INV_RX, INV_PAID FROM INVOICE WHERE INV_PAID=0.0 AND INV_NET>0.0 ";
	#$sql .= "AND CLIENT2_ID=5002 ";#
	#$sql .= "AND 5000<CLIENT2_ID AND CLIENT2_ID<5100 ";#
	$sql .= "ORDER BY INVOICE_ID";
	dlog($sql);#
	sql_execute($sql);
	$invs = array();
	$min_id = 0;
	$max_id = 0;
	while (($newArray = sql_fetch()) != false)
	{
		if (($min_id == 0) || ($newArray[0] < $min_id))
			$min_id = $newArray[0];
		if (($max_id == 0) || ($max_id < $newArray[0]))
			$max_id = $newArray[0];
		$invs[$newArray[0]] = array('INV_RX' => 1.0 * $newArray[1], 'INV_PAID' => 1.0 * $newArray[2]);
	}
	$num_invs = count($invs);
	dlog("Found $num_invs invoices with IDs ranging from $min_id to $max_id.");
	
	$upd_count = 0;
	foreach ($invs as $invoice_id => $iv)
	{
		$sql = "SELECT AL_AMOUNT FROM INV_ALLOC WHERE INVOICE_ID=$invoice_id";
		sql_execute($sql);
		$amount = 0.0;
		while (($newArray = sql_fetch()) != false)
			$amount += 1.0 * $newArray[0];
		$amount = round($amount,2);
		$inv_rx = round($iv['INV_RX'],2);
		$paid_2 = max($amount, $inv_rx);
		$inv_paid = round($iv['INV_PAID'],2);
		
		if ($inv_paid != $paid_2)
		{
			#dprint("Invoice $invoice_id: amount=$amount, inv_rx=$inv_rx, paid_2=$paid_2, inv_paid=$inv_paid");#
			$sql = "UPDATE INVOICE SET INV_PAID=$paid_2 WHERE INVOICE_ID=$invoice_id";
			#dprint($sql);#
			sql_execute($sql);
			$upd_count++;
		}
	} # foreach ($invs)
	
	dlog("Out of $num_invs invoices, updated $upd_count");
	
} # set_inv_paid_method_2()

function set_client_letter_link()
{
	# Import 31/12/16:
	#	import_2017_01_01_1911.log
	#	SELECT COUNT(*) FROM CLIENT_LETTER_LINK
	#	>>	735750
	
	
	global $letter_id;
	global $sqlFalse;
	
	set_time_limit(10 * 60 * 60); # 10 hours

	dlog("set_client_letter_link(): letter_id=$letter_id");
	
	$add_one_letter = ((0 < $letter_id) ? true : false);
	
	if ($add_one_letter)
		$letters = array($letter_id);
	else
	{
		$sql = "DELETE FROM CLIENT_LETTER_LINK";
		dlog($sql);
		sql_execute($sql);

		$sql = "SELECT LETTER_TYPE_ID FROM LETTER_TYPE_SD WHERE OBSOLETE=$sqlFalse";
		sql_execute($sql);
		$letters = array();
		while (($newArray = sql_fetch()) != false)
			$letters[] = $newArray[0];
	}
	dlog("letters=" . print_r($letters,1));#

	$sql = "SELECT CLIENT2_ID FROM CLIENT2 WHERE C_ARCHIVED=$sqlFalse";
	sql_execute($sql);
	$clients = array();
	while (($newArray = sql_fetch()) != false)
		$clients[] = $newArray[0];
	#dlog("clients=" . print_r($clients,1));#
	
	$done = 0;
	foreach ($clients as $client2_id)
	{
		foreach ($letters as $letter_type_id)
		{
			$sql = "INSERT INTO CLIENT_LETTER_LINK (CLIENT2_ID, LETTER_TYPE_ID) VALUES ($client2_id, $letter_type_id)";
			if ($done < 10)
				dlog($sql);
			sql_execute($sql);
			$done++;
		}
	}
	
	dlog("Done $done clients");
	
} # set_client_letter_link()

function import_letter_sequences()
{
	# Collect only: AUTO_L.DBF
	
	# Before calling this function:
	#	- Export COLLECT/AUTO_L.DBF as AUTO_L.csv using DBF Manager with default settings.
	# Required:
	#	c/AUTO_L.csv
	
	# Import 31/12/16:
	#	import_2017_01_01_1954.log
	#	2017-01-01 19:54:47/1 *=* CLIENT2_ID not found from CLID "-767" - (record 1)<br>col_nums: Array
	#	(
	#		[CLID] => 0
	#		[ABOVE_AMT] => 1
	#		[USER] => 2
	#		[NEXT_LET] => 3
	#		[DAYS_AFT] => 4
	#		[SEQ] => 5
	#	)
	#	<br>record: Array
	#	(
	#		[0] => -767
	#		[1] => 0
	#		[2] => Allocated
	#		[3] => Letter 1
	#		[4] => 14
	#		[5] => 1
	#	)
	#	2017-01-01 19:54:47/1 *=* CLIENT2_ID not found from CLID "-1" - (record 6)<br>col_nums: Array
	#	(
	#		[CLID] => 0
	#		[ABOVE_AMT] => 1
	#		[USER] => 2
	#		[NEXT_LET] => 3
	#		[DAYS_AFT] => 4
	#		[SEQ] => 5
	#	)
	#	<br>record: Array
	#	(
	#		[0] => -1
	#		[1] => 750
	#		[2] => Vilcol
	#		[3] => Letter 1
	#		[4] => 14
	#		[5] => 1
	#	)
	#	2017-01-01 19:54:49/1 Imported 355 sequences from a file of 377 sequences (and 1 header record).<br>
	#	Input file had 11 sequences with a bad CLID Array
	#	(
	#		[0] => -767
	#		[1] => -1
	#	)
	#	Input file had 0 sequences with a bad NEXT_LET.<br>
	
	
	global $col_nums; # for csvfield()
	global $crlf;
	global $input_count; # for csvfield()
	global $one_record; # for csvfield()
	global $print_buffer; # for print_dump()
	global $verbose; # for print_dump()
	
	$enabled = false; #
	if (!$enabled)
	{
		dprint("This import function is disabled", true);
		return;
	}
	
	print "<h3>Importing Collection Letter Sequences</h3>";
	
	#$start_time = time();
	set_time_limit(60 * 60 * 8); # 8 hours
	
	init_letter_sequences();

	$sys = 'c'; # collect

	$stop_after_i = 0;#
	$stop_after_o = 0;#
	$stop_margin = 0;
	$input_count = 0;
	$output_count = 0;
	$bad_client = 0;
	$bad_letter = 0;
	
	$dirname = "import-vilcol/{$sys}";
	$csv = "AUTO_L.csv";
	$fp_csv = fopen("$dirname/$csv", 'r');
	if ($fp_csv)
	{
		$col_nums = array();
		$bad_clients = array();
		
		$while_safety = 0; # to avoid infinite-looping
		while (true)
		{
			$verbose = false;
			if (
					((0 < $stop_after_i) && ($input_count <= $stop_after_i) && (($stop_after_i - $input_count) < $stop_margin))
					||
					((0 < $stop_after_o) && ($output_count < $stop_after_o) && (($stop_after_o - $output_count) <= $stop_margin))
				)
				$verbose = true;
			$print_buffer = array();
			if ($verbose)
				$print_buffer[] = "Starting record, input_count=$input_count";
				
			$while_safety++;
			if ($while_safety > 1000) # 1,000 sequences
			{
				print_dump();
				dlog("*=* (Sys=$sys) Safety Ejection!");
				break; # while(true)
			}
			
			$one_record = fgetcsv($fp_csv);
			if ($verbose)
				$print_buffer[] = ((($input_count == 0) ? "Headers: " : "Phone: ") . print_r($one_record,1));#
			elseif (((0 < $stop_after_i) || (0 < $stop_after_o)) && ($input_count == 0))
				dprint("Headers: " . print_r($one_record,1));
			
			if ($input_count == 0)
			{
				foreach ($one_record as $num => $field)
					$col_nums[$field] = $num;
				if ($verbose)
					$print_buffer[] = ("col_nums: " . print_r($col_nums,1));#
				print_dump();
			}
			elseif ($one_record && (count($one_record) > 0))
			{
				$clid = csvfield('CLID');
				$known_bad = (in_array($clid, $bad_clients) ? true : false);
				
				$next_letter = csvfield('NEXT_LET');
				$days = 1 * csvfield('DAYS_AFT');
				$seq = 1 * csvfield('SEQ');
				
				if ($known_bad)
					$client2_id = 0;
				else
					$client2_id = client_id_from_code($clid);
				if (0 < $client2_id)
				{
					$letter_id = letter_type_id_from_name($next_letter);
					if (0 < $letter_id)
					{
						print_dump();

						# --- SQL Insertions - INV_ALLOC --------------------------------------------

						$fields = "CLIENT2_ID,  SEQ_NUM, LETTER_TYPE_ID, SEQ_DAYS";
						$values = "$client2_id, $seq,    $letter_id,     $days";

						$sql = "INSERT INTO LETTER_SEQ ($fields) VALUES ($values)";
						if ($verbose)
							$print_buffer[] = $sql;
						if ($verbose)
							dlog($sql);
						$letter_seq_id = sql_execute($sql);
						if ($verbose)
							dlog("-> ID $letter_seq_id");
						$output_count++;
					}
					else
					{
						print_dump();
						dlog("*=* LETTER ID not found from NEXT_LET \"" . csvfield('NEXT_LET') . "\" - (record $input_count)<br>" .
									"col_nums: " . print_r($col_nums,1) . "<br>" .
									"record: " . print_r($one_record,1));
						$input_count++;
						$bad_letter++;
					}
				}
				else
				{
					if (!$known_bad)
					{
						print_dump();
						dlog("*=* CLIENT2_ID not found from CLID \"" . csvfield('CLID') . "\" - (record $input_count)<br>" .
									"col_nums: " . print_r($col_nums,1) . "<br>" .
									"record: " . print_r($one_record,1));
						$bad_clients[] = $clid;
					}
					$input_count++;
					$bad_client++;
				}
			}
			else 
			{
				print_dump();
				dlog("End of Sequences");
				break; # while(true)
			}
			
			$input_count++;
			if (	(0 < $stop_after_i) && ($stop_after_i < $input_count) ||
					(0 < $stop_after_o) && ($stop_after_o <= $output_count)
			   )
			{
				dlog("Stopping after $stop_after_i inputs and $stop_after_o outputs");
				if (0 < $stop_after_i)
					break; # while(true)
			}
				
		} # while(true)

		dlog("Imported $output_count sequences from a file of " . ($input_count-1) . " sequences (and 1 header record).<br>$crlf" .
				"Input file had $bad_client sequences with a bad CLID " . print_r($bad_clients,1) . "<br>$crlf" .
				"Input file had $bad_letter sequences with a bad NEXT_LET.<br>$crlf");
		
		fclose($fp_csv);
	}
	else
	{
		if (!$fp_csv)
			dlog("*=* Failed to fopen(\"$dirname/$csv\",'r')");
	}
} # import_letter_sequences()

function in_fix_aux_list($one_r)
{
	global $fix_aux;
	
	foreach ($fix_aux as $fix_one)
	{
		$seq = $one_r[0];
		$road = trim($one_r[8]);
		if (($fix_one[0] == $seq) && ($fix_one[1] == $road))
			return true;
	}
	return false;
}

function fix_col_notes()
{
	# If a sequence number is entered ($fix_col_seq) then this will write the note into the log file.

	# Add collector notes from DBV to JOB_NOTE; collection jobs only.
	# This is an INCREMENTAL operation.
	# For each job found from COLLECT.csv, if a JOB_NOTE record is found with the same JOB_ID as the csv job,
	# and IMP_2=1, then the job is skipped.
	
	# Import 31/12/16:
	# Actually done on 03/01/17, after the system went live.
	# Kept crashing on local PC, but on server it completed successfully in one pass, adding 298,280 records to JOB_NOTE, one per job.
	# Time taken: 74.1 mins.
	
	global $col_nums;
	global $fix_col_seq;
	global $input_count; # from caller e.g. import_jobs()
	global $j_sequence; # from import_jobs()
	global $one_record;
	global $sqlNow;
	global $sqlTrue;
	global $USER;
	
	#$test_seq = 90143521; # DBF has SEQUENCE=-1 and VILNO=0 and HOMEADD1="16 VILLAFIELD DRIVE" and VLF=<blank> (VilNo should be 273645)
	#$test_seq = 90143504; # DBF has SEQUENCE=90143504 and VILNO=273627 and HOMEADD1="70A HERMITAGE LANE" and VLF="üÚ£Çâß"
	# Last note before this function was called: JOB_NOTE_ID=520745. Beware: live system will have new notes after this one.
	#$test_done = false;

	$enabled = false; #
	if (0 < $fix_col_seq) $enabled = true;#
	if (!$enabled)
	{
		dlog("This import function is disabled", true);
		return;
	}
	
	print "<h3>Fix Collection Notes</h3>";
	print "<p>\$fix_col_seq=$fix_col_seq</p>";
	
	$start_time = time();
	set_time_limit(60 * 60 * 8); # 8 hours
	
	$stop_after = 0;#
	$stop_margin = 0;
	$input_count = 0;
	
	$sys = 'c'; # Either 't' or 'c' from this point
	$dirname = "import-vilcol/{$sys}";
	$sys_txt = "Collections";

	$dbf = "COLLECT.DBF"; # In files dated Jan 2015, there are 427,058 jobs. Takes 161 mins to import on RDR server.
	$dbv = "COLLECT.DBV";
	$csv = "COLLECT.csv";

	# File offsets into DBF
	$dbf_firstoff = 2883; # offset into DBF of first data record (after the header record)
	$dbf_reclen = 1460; # length of each data record in DBF

	$fp_dbf = fopen("$dirname/$dbf", 'r');
	$fp_dbv = fopen("$dirname/$dbv", 'r');
	$fp_csv = fopen("$dirname/$csv", 'r');
	if ($fp_dbf && $fp_dbv && $fp_csv)
	{
		sql_encryption_preparation('JOB_NOTE');
		
		$col_nums = array();
		
		$seq_min = 0;
		$seq_max = 0;
		$seq_zero_count = 0;
		$seq_zero_replacement = 101;
		$job_count = 0;
		$blank_count = 0;
		$notes_count = 0;
		
		$while_safety = 0; # to avoid infinite-looping
		while (true)
		{
			$verbose = false;
			if ((0 < $stop_after) && ($input_count <= $stop_after) && (($stop_after - $input_count) < $stop_margin))
				$verbose = true;
			
			$while_safety++;
			if ($while_safety > 800000) # 800,000 jobs
			{
				dlog("*=* (Sys=$sys) Safety Ejection!");
				break; # while(true)
			}

			if ($verbose)
				dlog("=== Record $input_count ===");
				
			$one_record = fgetcsv($fp_csv);
			
			# Check for a blank data record
			if (($input_count > 0) && $one_record && (count($one_record) > 0))
			{
				$blank_rec = true;
				foreach ($one_record as $or)
				{
					if (($or != 0) && (trim($or) != ''))
					{
						$blank_rec = false;
						break; # from foreach()
					}
				}
				if ($blank_rec)
				{
					$input_count++;
					$blank_count++;
					continue; # around while(true)
				}
			}
			
			if ($input_count > 0) # skip header record
			{
				$vilno_2015 = 0;
				$sequence_2015 = 0;
				$vs_2015 = 0;
				
				if ($one_record && (count($one_record) > 0)) # test for end-of-records
				{
					$seq_raw = csvfield('SEQUENCE');
					$j_sequence = 1 * $seq_raw;
					if ($j_sequence == -1)
					{
						# This might be a job that's been archived leaving it with sequence = -1 and vilno = 0.
						# Look it up in the COLLECT_DBF_2015 table to try and find it's correct sequence number and vilno.
						$where = array();
						if (csvfield('DATEREC', 'uk') != '')
							$where[] = "DATEREC=" . quote_smart(csvfield('DATEREC', 'uk'), true);
						else 
							$where[] = "DATEREC IS NULL";
						$where[] = "LASTNAME=" . quote_smart(csvfield('LASTNAME'), true);
						$where[] = "CLIREF=" . quote_smart(csvfield('CLIREF'), true);
						$where[] = "CLID=" . csvfield('CLID');
						$sql = "SELECT TOP 1 VILNO, SEQUENCE FROM COLLECT_DBF_2015 WHERE (" . implode(') AND (', $where) . ")";
						if ($verbose)
							dlog($sql);
						sql_execute($sql);
						while (($newArray = sql_fetch()) != false)
						{
							$vilno_2015 = 1 * $newArray[0];
							$sequence_2015 = 1 * $newArray[1];
						}
						if ((0 < $vilno_2015) && (0 < $sequence_2015))
						{
							$j_sequence = $sequence_2015;
							$vs_2015 = 1;
							if ($verbose)
								dlog("Found V=$vilno_2015 & S=$sequence_2015 from: $sql");
						}
						else
						{
							$vilno_2015 = 0;
							$sequence_2015 = 0;
						}
					}
					if ($j_sequence > 0)
					{
						if (($seq_min == 0) || ($j_sequence < $seq_min))
							$seq_min = $j_sequence;
						if (($seq_max == 0) || ($seq_max < $j_sequence))
							$seq_max = $j_sequence;
					}
					else
					{
						$j_sequence = $seq_zero_replacement++;
						$seq_zero_count++;
						if ($seq_zero_count <= 1000)
							dlog("*=* (Sys=$sys) Non-numeric or illegal SEQUENCE \"$seq_raw\" changed to $j_sequence " .
									"(record $input_count, VILNO=\"" . csvfield('VILNO') . "\", \$seq_zero_count=$seq_zero_count)");
					}
				}
				else 
				{
					dlog("End of Jobs");
					break; # while(true)
				}
				
				$old_verbose = $verbose;
				
				if (($j_sequence == 90149542) || ($j_sequence == 90493801)# || ($j_sequence == 70940)) 70940 only for import of 20/09/16
					#|| (($one_record[0] == -1) && (trim($one_record[8]) == '54B MORAY ROAD'))
					|| in_fix_aux_list($one_record)
					)
				{
					# See also $fix_list
					#$verbose = true; #
					if ($verbose)
						dlog("*=* Found sequence=$j_sequence. col_nums:<br>" . print_r($col_nums,1) . "<br>" . 
								"Record:<br>" . print_r($one_record,1));
					for ($ii = 88; 36 <= $ii; $ii--)
						$one_record[$ii] = $one_record[$ii-4];
					$bits = explode(',', str_replace('"', '', $one_record[31]));
					$one_record[31] = str_replace('\\', '', $bits[0]);
					$one_record[32] = $bits[1];
					$one_record[33] = $bits[2];
					$one_record[34] = $bits[3];
					$one_record[35] = $bits[4];
					if ($verbose)
						dlog("Cleaned Record:<br>" . print_r($one_record,1));
				}
				elseif ($j_sequence == 730508)
				{
					# See also $fix_list
					#$verbose = true; #
					if ($verbose)
					{
						dlog("*=* Found sequence=$j_sequence. col_nums:<br>" . print_r($col_nums,1) . "<br>" . 
								"Record:<br>" . print_r($one_record,1));
					}
					$one_record[70] = str_replace('foren\",m', 'forenm', $one_record[70]);
					$one_record[71] = str_replace('"', '', $one_record[71]);
					if ($verbose)
						dlog("Cleaned:<br>" . print_r($one_record,1));
				}
				elseif ($j_sequence == 90503059)
				{
					# See also $fix_list
					#$verbose = true; #
					if ($verbose)
						dlog("*=* Found sequence=$j_sequence. col_nums:<br>" . print_r($col_nums,1) . "<br>" . 
								"Record:<br>" . print_r($one_record,1));
					for ($ii = 88; 15 <= $ii; $ii--)
						$one_record[$ii] = $one_record[$ii-1];
					$bits = explode(',', str_replace('"', '', $one_record[13]));
					$one_record[13] = str_replace('\\', '', $bits[0]);
					$one_record[14] = $bits[1];
					if ($verbose)
						dlog("Cleaned Record:<br>" . print_r($one_record,1));
				}
				elseif (($j_sequence == 90694238) || ($j_sequence == 90700518))
				{
					# See also $fix_list
					#$verbose = true; #
					if ($verbose)
						dlog("*=* Found sequence=$j_sequence. col_nums:<br>" . print_r($col_nums,1) . "<br>" . 
								"Record:<br>" . print_r($one_record,1));
					for ($ii = 88; 28 <= $ii; $ii--)
						$one_record[$ii] = $one_record[$ii-7];
					$bits = explode(',', str_replace('"', '', $one_record[20]));
					$one_record[20] = str_replace('\\', '', $bits[0]);
					$one_record[21] = $bits[1];
					$one_record[22] = $bits[2];
					$one_record[23] = $bits[3];
					$one_record[24] = $bits[4];
					$one_record[25] = $bits[5];
					$one_record[26] = $bits[6];
					$one_record[27] = $bits[7];
					if ($verbose)
						dlog("Cleaned Record:<br>" . print_r($one_record,1));
				}
				elseif (($j_sequence == 90707120) || ($j_sequence == 90738552))
				{
					# See also $fix_list
					#$verbose = true; #
					if ($verbose)
						dlog("*=* Found sequence=$j_sequence. col_nums:<br>" . print_r($col_nums,1) . "<br>" . 
								"Record:<br>" . print_r($one_record,1));
					$vlf = $one_record[81];
					if ($verbose)
						dlog("VLF=\"$vlf\"");
					for ($ii = 87; 28 <= $ii; $ii--)
						$one_record[$ii] = $one_record[$ii-7];
					$bits = explode(',', str_replace('"', '', $one_record[20]));
					$one_record[20] = str_replace('\\', '', $bits[0]);
					$one_record[21] = $bits[1];
					$one_record[22] = $bits[2];
					$one_record[23] = $bits[3];
					$one_record[24] = $bits[4];
					$one_record[25] = $bits[5];
					$one_record[26] = $bits[6];
					$one_record[27] = $bits[7];
					$one_record[88] = $vlf;
					if ($verbose)
						dlog("Cleaned Record:<br>" . print_r($one_record,1));
				}
				elseif (($j_sequence == 90714469) || ($j_sequence == 90717737))
				{
					# See also $fix_list
					#$verbose = true; #
					if ($verbose)
						dlog("*=* Found sequence=$j_sequence. col_nums:<br>" . print_r($col_nums,1) . "<br>" . 
								"Record:<br>" . print_r($one_record,1));
					$vlf = $one_record[87];
					if ($verbose)
						dlog("VLF=\"$vlf\"");
					for ($ii = 87; 28 <= $ii; $ii--)
						$one_record[$ii] = $one_record[$ii-1];
					$bits = explode(',', str_replace('"', '', $one_record[26]));
					$one_record[26] = str_replace('\\', '', $bits[0]);
					$one_record[27] = $bits[1];
					$one_record[88] = $vlf;
					if ($verbose)
						dlog("Cleaned Record:<br>" . print_r($one_record,1));
				}
				elseif ($j_sequence == 90723905)
				{
					# See also $fix_list
					#$verbose = true; #
					if ($verbose)
						dlog("*=* Found sequence=$j_sequence. col_nums:<br>" . print_r($col_nums,1) . "<br>" . 
								"Record:<br>" . print_r($one_record,1));
					$vlf = $one_record[83];
					if ($verbose)
						dlog("VLF=\"$vlf\"");
					for ($ii = 87; 26 <= $ii; $ii--)
						$one_record[$ii] = $one_record[$ii-5];
					$bits = explode(',', str_replace('"', '', $one_record[20]));
					$one_record[20] = str_replace('\\', '', $bits[0]);
					$one_record[21] = $bits[1];
					$one_record[22] = $bits[2];
					$one_record[23] = $bits[3];
					$one_record[24] = $bits[4];
					$one_record[25] = $bits[5];
					$one_record[88] = $vlf;
					if ($verbose)
						dlog("Cleaned Record:<br>" . print_r($one_record,1));
				}
				#if ($j_sequence != 90707120)#
				#	continue;
				$verbose = $old_verbose;
			} # if ($input_count > 0) # skip header record
				
			if ((0 < $stop_after) && ($stop_after < $input_count))
			{
				$input_count++;
				continue;
			}
						
			if ($verbose)
				dlog((($input_count == 0) ? "Headers: " : "Job: ") . print_r($one_record,1));#
			if ($input_count == 0)
			{
				# Header record
				foreach ($one_record as $num => $field)
					$col_nums[$field] = $num;
				if ($verbose)
					dlog("col_nums: " . print_r($col_nums,1));#
			}

			if (0 < $input_count)
			{
				# Data record

				if ((!$fix_col_seq) || ($fix_col_seq == $j_sequence))
				{
					#if (420000 < $input_count) { #

					$job_id = job_id_from_sequence($j_sequence);
					$sql = "SELECT COUNT(*) FROM JOB_NOTE WHERE (JOB_ID=$job_id) AND (IMP_2=$sqlTrue)";
					$skip_count = 0;
					sql_execute($sql);
					while (($newArray = sql_fetch()) != false)
						$skip_count = 1 * $newArray[0];
					if (($skip_count == 0) || ($fix_col_seq == $j_sequence))
					{

		//				if ($j_sequence == $test_seq)#
		//				{
		//					dlog("*=* Found TEST sequence=$j_sequence. col_nums:<br>" . print_r($col_nums,1) . "<br>" . 
		//							"Record:<br>" . print_r($one_record,1));
		//					$verbose = true;#

						if ($fix_col_seq == $j_sequence)
							$verbose = true;

						# --- CSV and DBF integrity check
						# 
						# Check we can access the same sequence number in DBF 	
						$dbf_offset = $dbf_firstoff + (($input_count - 1) * $dbf_reclen);
						if ($verbose) dlog("DBF OFF = $dbf_offset (record $input_count)");#
						fseek($fp_dbf, $dbf_offset);
						$dbf_seq = fread($fp_dbf, 8);
						if ($verbose) dlog("CSV SEQ: \"$seq_raw\"=\"$j_sequence\". DBF SEQ: \"$dbf_seq\".");#
						if ($dbf_seq != $seq_raw)
						{
							dlog("*=* ABORTING! (Sys=$sys) CSV/DBF mismatch: CSV SEQ=\"$seq_raw\", DBF=\"$dbf_seq\", " .
									"DBF OFF = $dbf_offset (record $input_count, j_sequence=$j_sequence)");
							dlog("...one_record=" . print_r($one_record,1));
							return;
						}
						elseif ($verbose)
							dlog(":-) CSV/DBF matched OK");

						$dbf_offset += ($dbf_reclen - 6 - 1); # This should now point to the VLF field.
						if ($verbose) dlog("DBF OFF for VLF = $dbf_offset = HEX " . dechex($dbf_offset) . " (record $input_count)");#
						fseek($fp_dbf, $dbf_offset);
						$vlf = fread($fp_dbf, 6);

						$vlf_ascii = "" . ord($vlf[0]) . "," . ord($vlf[1]) . "," . ord($vlf[2]) . "," . 
											ord($vlf[3]) . "," . ord($vlf[4]) . "," . ord($vlf[5]);
						$vlf_hex = "" . dechex(ord($vlf[0])) . "," . dechex(ord($vlf[1])) . "," . dechex(ord($vlf[2])) . "," . 
											dechex(ord($vlf[3])) . "," . dechex(ord($vlf[4])) . "," . dechex(ord($vlf[5]));
						if ($verbose) dlog("VLF ASCII = \"$vlf_ascii\", HEX = \"$vlf_hex\"");#

						$dbv_offset = vlf_convert_raw($vlf);
						if ($verbose) dlog("VLF/{$input_count}: \"$vlf\" = \"$vlf_ascii\" -> offset=\"$dbv_offset\"");#

						if ($dbv_offset > 0)
						{
							$notes_count++;
							$notes_text = '';

							fseek($fp_dbv, $dbv_offset);
							$dbv_readlen = 50000;
							$dbv_buffer = fread($fp_dbv, $dbv_readlen);
							$gotlen = strlen($dbv_buffer);
							if ($gotlen < 8)
							{
								if ($verbose) dlog("*=* (Sys=$sys) DBV Read problem: VLF: \"$vlf\" = \"$vlf_ascii\" -> " .
									"offset=\"$dbv_offset\". " . (($gotlen > 0) ? "Only $gotlen" : "No") . " bytes read. " .
									"\$input_count = $input_count. No Notes extracted.");
							}
							else 
							{
								# First 6 bytes are size of Pascal TDBVStru structure.
								if ($verbose)
								{
									$six = "" . ord($dbv_buffer[0]) . "," . ord($dbv_buffer[1]) . "," . ord($dbv_buffer[2]) . "," . 
												ord($dbv_buffer[3]) . "," . ord($dbv_buffer[4]) . "," . ord($dbv_buffer[5]);
									if ($verbose) dlog("DBV 6-pack = \"$six\"");
								}
								$dbv_len = 0;
								$dbv_len += ord($dbv_buffer[0]);
								$dbv_len += (ord($dbv_buffer[1]) << 8);
								$dbv_len += (ord($dbv_buffer[2]) << 16);
								$dbv_len += (ord($dbv_buffer[3]) << 32);
								#if ((0 < $stop_after) && ($input_count <= $stop_after) && 
								#											(($stop_after - $input_count) < $stop_margin))
									if ($verbose) dlog("DBV Notes length = $dbv_len");#
								$notes_text = substr($dbv_buffer, 6, $dbv_len);
								if ((0 < $stop_after) && ($input_count <= $stop_after) && 
																			(($stop_after - $input_count) < $stop_margin))
									if ($verbose) dlog("DBV Notes/1(" . strlen($notes_text) . ")=\"$notes_text\"");
								$notes_text = trim($notes_text);
								if ($verbose) dlog("DBV Notes/2(" . strlen($notes_text) . ")=\"$notes_text\"");#

								if (!$fix_col_seq)
								{
									if (4000 < strlen($notes_text))
										$notes_text = substr($notes_text, -4000);

									# --- ADD NOTES TO JOB_NOTE TABLE ---

									$j_note = sql_encrypt($notes_text, '', 'JOB_NOTE');

									$fields = "JOB_ID,  J_NOTE,  IMPORTED, IMP_2,    JN_ADDED_ID,        JN_ADDED_DT";
									$values = "$job_id, $j_note, $sqlTrue, $sqlTrue, {$USER['USER_ID']}, $sqlNow";

									$sql = "INSERT INTO JOB_NOTE ($fields) VALUES ($values)";
									if ($verbose) dlog($sql);
									$job_note_id = sql_execute($sql);
									if ($verbose) dlog("-> ID $job_note_id");
								}
							}
						}
						else 
						{
							if ($verbose) dlog("*=* (Sys=$sys) No DBV offset: VLF: \"$vlf\" = \"$vlf_ascii\" -> offset=\"$dbv_offset\". " .
												"\$input_count = $input_count", true);
							if ($verbose)
								dlog("Job (Seq=$j_sequence) has no notes"); # Not an error
						}

						#$test_done = true;
						#} # if ($j_sequence == $test_seq)
					} # if ($skip_count == 0)				
					#} # if (420000 < $input_count)
				}
			} # if (0 < $input_count)
			$input_count++;
			if (($input_count % 10000) == 0)
				dlog("fix_col_notes($sys): done $input_count input records so far");
			if ((0 < $stop_after) && ($stop_after < $input_count))
			{
				dlog("Stopping after $stop_after jobs");
				#break; # while(true)
			}
			#if ($test_done)
			#	break;
		} # while (true)

		$time_taken = 1.0 * (time() - $start_time) / 60.0;
		$per_rec = 60.0 * $time_taken / (1.0 * $input_count / 1000.0);
		dlog("Imported $job_count jobs from a $sys_txt file of " . ($input_count-1) . " jobs (and 1 header record).<br>" .
				"Input file had min sequence of $seq_min, max of $seq_max, <br>" .
				"had $blank_count blank records and had $seq_zero_count zero sequences.<br>" .
				"Time taken: $time_taken mins ($per_rec secs per 1000 jobs)");
		
		fclose($fp_dbf);
		fclose($fp_dbv);
		fclose($fp_csv);
	} # if ($fp_dbf && $fp_dbv && $fp_csv)
	else
	{
		if (!$fp_dbf)
			dlog("*=* (Sys=$sys) Failed to fopen(\"$dirname/$dbf\",'r')");
		if (!$fp_dbv)
			dlog("*=* (Sys=$sys) Failed to fopen(\"$dirname/$dbv\",'r')");
		if (!$fp_csv)
			dlog("*=* (Sys=$sys) Failed to fopen(\"$dirname/$csv\",'r')");
	}

} # fix_col_notes()

function dump_col_notes()
{
	# ** This function is INCREMENTAL ** - if you want to run it from scratch then do this first: UPDATE JOB SET JC_IMP_NOTES_VMAX=NULL
	
	# Dump collector notes from DBV to JOB.JC_IMP_NOTES_VMAX; collection jobs only.
	# This is because fix_col_notes() would have truncated each note to 4000 characters.
	# For each job found from COLLECT.csv, if JOB.JC_IMP_NOTES_VMAX is NULL, then the csv job is skipped.
	
	# Import 31/12/16:
	#	import_2017_01_10_1449.log
	#	Actually done 10/01/17 after go-live date.
	#	Imported 0 jobs from a Collections file of 473202 jobs (and 1 header record).
	#	Input file had min sequence of 90000002, max of 90902920, had 11 blank records and had 9 zero sequences.
	#	Time taken: 48.983333333333 mins (6.2108651044055 secs per 1000 jobs)
	
	global $col_nums;
	global $input_count; # from caller e.g. import_jobs()
	global $j_sequence; # from import_jobs()
	global $one_record;
	
	$enabled = false; #
	if (!$enabled)
	{
		dlog("This import function is disabled", true);
		return;
	}
	
	print "<h3>Dump Collection Notes</h3>";
	
	$start_time = time();
	set_time_limit(60 * 60 * 8); # 8 hours
	
	$stop_after = 0;#
	$stop_margin = 0;
	$input_count = 0;
	$first_job_id = 0;
	
	$sys = 'c'; # Either 't' or 'c' from this point
	$dirname = "import-vilcol/{$sys}";
	$sys_txt = "Collections";

	$dbf = "COLLECT.DBF"; # In files dated Jan 2015, there are 427,058 jobs. Takes 161 mins to import on RDR server.
	$dbv = "COLLECT.DBV";
	$csv = "COLLECT.csv";

	# File offsets into DBF
	$dbf_firstoff = 2883; # offset into DBF of first data record (after the header record)
	$dbf_reclen = 1460; # length of each data record in DBF

	$fp_dbf = fopen("$dirname/$dbf", 'r');
	$fp_dbv = fopen("$dirname/$dbv", 'r');
	$fp_csv = fopen("$dirname/$csv", 'r');
	if ($fp_dbf && $fp_dbv && $fp_csv)
	{
		$col_nums = array();
		
		$seq_min = 0;
		$seq_max = 0;
		$seq_zero_count = 0;
		$seq_zero_replacement = 101;
		$job_count = 0;
		$blank_count = 0;
		$notes_count = 0;
		
		$while_safety = 0; # to avoid infinite-looping
		while (true)
		{
			$verbose = false;
			if ((0 < $stop_after) && ($input_count <= $stop_after) && (($stop_after - $input_count) < $stop_margin))
				$verbose = true;
			
			$while_safety++;
			if ($while_safety > 800000) # 800,000 jobs
			{
				dlog("*=* (Sys=$sys) Safety Ejection!");
				break; # while(true)
			}

			if ($verbose)
				dlog("=== Record $input_count ===");
				
			$one_record = fgetcsv($fp_csv);
			
			# Check for a blank data record
			if (($input_count > 0) && $one_record && (count($one_record) > 0))
			{
				$blank_rec = true;
				foreach ($one_record as $or)
				{
					if (($or != 0) && (trim($or) != ''))
					{
						$blank_rec = false;
						break; # from foreach()
					}
				}
				if ($blank_rec)
				{
					$input_count++;
					$blank_count++;
					continue; # around while(true)
				}
			}
			
			if ($input_count > 0) # skip header record
			{
				$vilno_2015 = 0;
				$sequence_2015 = 0;
				$vs_2015 = 0;
				
				if ($one_record && (count($one_record) > 0)) # test for end-of-records
				{
					$seq_raw = csvfield('SEQUENCE');
					$j_sequence = 1 * $seq_raw;
					if ($j_sequence == -1)
					{
						# This might be a job that's been archived leaving it with sequence = -1 and vilno = 0.
						# Look it up in the COLLECT_DBF_2015 table to try and find it's correct sequence number and vilno.
						$where = array();
						if (csvfield('DATEREC', 'uk') != '')
							$where[] = "DATEREC=" . quote_smart(csvfield('DATEREC', 'uk'), true);
						else 
							$where[] = "DATEREC IS NULL";
						$where[] = "LASTNAME=" . quote_smart(csvfield('LASTNAME'), true);
						$where[] = "CLIREF=" . quote_smart(csvfield('CLIREF'), true);
						$where[] = "CLID=" . csvfield('CLID');
						$sql = "SELECT TOP 1 VILNO, SEQUENCE FROM COLLECT_DBF_2015 WHERE (" . implode(') AND (', $where) . ")";
						if ($verbose)
							dlog($sql);
						sql_execute($sql);
						while (($newArray = sql_fetch()) != false)
						{
							$vilno_2015 = 1 * $newArray[0];
							$sequence_2015 = 1 * $newArray[1];
						}
						if ((0 < $vilno_2015) && (0 < $sequence_2015))
						{
							$j_sequence = $sequence_2015;
							$vs_2015 = 1;
							if ($verbose)
								dlog("Found V=$vilno_2015 & S=$sequence_2015 from: $sql");
						}
						else
						{
							$vilno_2015 = 0;
							$sequence_2015 = 0;
						}
					}
					if ($j_sequence > 0)
					{
						if (($seq_min == 0) || ($j_sequence < $seq_min))
							$seq_min = $j_sequence;
						if (($seq_max == 0) || ($seq_max < $j_sequence))
							$seq_max = $j_sequence;
					}
					else
					{
						$j_sequence = $seq_zero_replacement++;
						$seq_zero_count++;
						if ($seq_zero_count <= 1000)
							dlog("*=* (Sys=$sys) Non-numeric or illegal SEQUENCE \"$seq_raw\" changed to $j_sequence " .
									"(record $input_count, VILNO=\"" . csvfield('VILNO') . "\", \$seq_zero_count=$seq_zero_count)");
					}
				}
				else 
				{
					dlog("End of Jobs");
					break; # while(true)
				}
				
				$old_verbose = $verbose;
				
				if (($j_sequence == 90149542) || ($j_sequence == 90493801)# || ($j_sequence == 70940)) 70940 only for import of 20/09/16
					#|| (($one_record[0] == -1) && (trim($one_record[8]) == '54B MORAY ROAD'))
					|| in_fix_aux_list($one_record)
					)
				{
					# See also $fix_list
					#$verbose = true; #
					if ($verbose)
						dlog("*=* Found sequence=$j_sequence. col_nums:<br>" . print_r($col_nums,1) . "<br>" . 
								"Record:<br>" . print_r($one_record,1));
					for ($ii = 88; 36 <= $ii; $ii--)
						$one_record[$ii] = $one_record[$ii-4];
					$bits = explode(',', str_replace('"', '', $one_record[31]));
					$one_record[31] = str_replace('\\', '', $bits[0]);
					$one_record[32] = $bits[1];
					$one_record[33] = $bits[2];
					$one_record[34] = $bits[3];
					$one_record[35] = $bits[4];
					if ($verbose)
						dlog("Cleaned Record:<br>" . print_r($one_record,1));
				}
				elseif ($j_sequence == 730508)
				{
					# See also $fix_list
					#$verbose = true; #
					if ($verbose)
					{
						dlog("*=* Found sequence=$j_sequence. col_nums:<br>" . print_r($col_nums,1) . "<br>" . 
								"Record:<br>" . print_r($one_record,1));
					}
					$one_record[70] = str_replace('foren\",m', 'forenm', $one_record[70]);
					$one_record[71] = str_replace('"', '', $one_record[71]);
					if ($verbose)
						dlog("Cleaned:<br>" . print_r($one_record,1));
				}
				elseif ($j_sequence == 90503059)
				{
					# See also $fix_list
					#$verbose = true; #
					if ($verbose)
						dlog("*=* Found sequence=$j_sequence. col_nums:<br>" . print_r($col_nums,1) . "<br>" . 
								"Record:<br>" . print_r($one_record,1));
					for ($ii = 88; 15 <= $ii; $ii--)
						$one_record[$ii] = $one_record[$ii-1];
					$bits = explode(',', str_replace('"', '', $one_record[13]));
					$one_record[13] = str_replace('\\', '', $bits[0]);
					$one_record[14] = $bits[1];
					if ($verbose)
						dlog("Cleaned Record:<br>" . print_r($one_record,1));
				}
				elseif (($j_sequence == 90694238) || ($j_sequence == 90700518))
				{
					# See also $fix_list
					#$verbose = true; #
					if ($verbose)
						dlog("*=* Found sequence=$j_sequence. col_nums:<br>" . print_r($col_nums,1) . "<br>" . 
								"Record:<br>" . print_r($one_record,1));
					for ($ii = 88; 28 <= $ii; $ii--)
						$one_record[$ii] = $one_record[$ii-7];
					$bits = explode(',', str_replace('"', '', $one_record[20]));
					$one_record[20] = str_replace('\\', '', $bits[0]);
					$one_record[21] = $bits[1];
					$one_record[22] = $bits[2];
					$one_record[23] = $bits[3];
					$one_record[24] = $bits[4];
					$one_record[25] = $bits[5];
					$one_record[26] = $bits[6];
					$one_record[27] = $bits[7];
					if ($verbose)
						dlog("Cleaned Record:<br>" . print_r($one_record,1));
				}
				elseif (($j_sequence == 90707120) || ($j_sequence == 90738552))
				{
					# See also $fix_list
					#$verbose = true; #
					if ($verbose)
						dlog("*=* Found sequence=$j_sequence. col_nums:<br>" . print_r($col_nums,1) . "<br>" . 
								"Record:<br>" . print_r($one_record,1));
					$vlf = $one_record[81];
					if ($verbose)
						dlog("VLF=\"$vlf\"");
					for ($ii = 87; 28 <= $ii; $ii--)
						$one_record[$ii] = $one_record[$ii-7];
					$bits = explode(',', str_replace('"', '', $one_record[20]));
					$one_record[20] = str_replace('\\', '', $bits[0]);
					$one_record[21] = $bits[1];
					$one_record[22] = $bits[2];
					$one_record[23] = $bits[3];
					$one_record[24] = $bits[4];
					$one_record[25] = $bits[5];
					$one_record[26] = $bits[6];
					$one_record[27] = $bits[7];
					$one_record[88] = $vlf;
					if ($verbose)
						dlog("Cleaned Record:<br>" . print_r($one_record,1));
				}
				elseif (($j_sequence == 90714469) || ($j_sequence == 90717737))
				{
					# See also $fix_list
					#$verbose = true; #
					if ($verbose)
						dlog("*=* Found sequence=$j_sequence. col_nums:<br>" . print_r($col_nums,1) . "<br>" . 
								"Record:<br>" . print_r($one_record,1));
					$vlf = $one_record[87];
					if ($verbose)
						dlog("VLF=\"$vlf\"");
					for ($ii = 87; 28 <= $ii; $ii--)
						$one_record[$ii] = $one_record[$ii-1];
					$bits = explode(',', str_replace('"', '', $one_record[26]));
					$one_record[26] = str_replace('\\', '', $bits[0]);
					$one_record[27] = $bits[1];
					$one_record[88] = $vlf;
					if ($verbose)
						dlog("Cleaned Record:<br>" . print_r($one_record,1));
				}
				elseif ($j_sequence == 90723905)
				{
					# See also $fix_list
					#$verbose = true; #
					if ($verbose)
						dlog("*=* Found sequence=$j_sequence. col_nums:<br>" . print_r($col_nums,1) . "<br>" . 
								"Record:<br>" . print_r($one_record,1));
					$vlf = $one_record[83];
					if ($verbose)
						dlog("VLF=\"$vlf\"");
					for ($ii = 87; 26 <= $ii; $ii--)
						$one_record[$ii] = $one_record[$ii-5];
					$bits = explode(',', str_replace('"', '', $one_record[20]));
					$one_record[20] = str_replace('\\', '', $bits[0]);
					$one_record[21] = $bits[1];
					$one_record[22] = $bits[2];
					$one_record[23] = $bits[3];
					$one_record[24] = $bits[4];
					$one_record[25] = $bits[5];
					$one_record[88] = $vlf;
					if ($verbose)
						dlog("Cleaned Record:<br>" . print_r($one_record,1));
				}
				#if ($j_sequence != 90707120)#
				#	continue;
				$verbose = $old_verbose;
			} # if ($input_count > 0) # skip header record
				
			if ((0 < $stop_after) && ($stop_after < $input_count))
			{
				$input_count++;
				continue;
			}
						
			if ($verbose)
				dlog((($input_count == 0) ? "Headers: " : "Job: ") . print_r($one_record,1));#
			if ($input_count == 0)
			{
				# Header record
				foreach ($one_record as $num => $field)
					$col_nums[$field] = $num;
				if ($verbose)
					dlog("col_nums: " . print_r($col_nums,1));#
			}

			if (0 < $input_count)
			{
				# Data record

				$job_id = job_id_from_sequence($j_sequence);
				if (0 < $job_id)
				{
					$sql = "SELECT COUNT(*) FROM JOB WHERE (JOB_ID=$job_id) AND (JC_IMP_NOTES_VMAX IS NOT NULL)";
					$not_null_count = 0;
					sql_execute($sql);
					while (($newArray = sql_fetch()) != false)
						$not_null_count = 1 * $newArray[0];
					if ($not_null_count == 0)
					{
						if ($first_job_id == 0)
						{
							$first_job_id = $job_id;
							$verbose = true;
							dlog("Starting with JOB_ID $job_id, Sequence $j_sequence, \$input_count = $input_count", true);
						}
						
						# --- CSV and DBF integrity check
						# 
						# Check we can access the same sequence number in DBF 	
						$dbf_offset = $dbf_firstoff + (($input_count - 1) * $dbf_reclen);
						if ($verbose) dlog("DBF OFF = $dbf_offset (record $input_count)");#
						fseek($fp_dbf, $dbf_offset);
						$dbf_seq = fread($fp_dbf, 8);
						if ($verbose) dlog("CSV SEQ: \"$seq_raw\"=\"$j_sequence\". DBF SEQ: \"$dbf_seq\".");#
						if ($dbf_seq != $seq_raw)
						{
							dlog("*=* ABORTING! (Sys=$sys) CSV/DBF mismatch: CSV SEQ=\"$seq_raw\", DBF=\"$dbf_seq\", " .
									"DBF OFF = $dbf_offset (record $input_count, j_sequence=$j_sequence)");
							dlog("...one_record=" . print_r($one_record,1));
							return;
						}
						elseif ($verbose)
							dlog(":-) CSV/DBF matched OK");

						$dbf_offset += ($dbf_reclen - 6 - 1); # This should now point to the VLF field.
						if ($verbose) dlog("DBF OFF for VLF = $dbf_offset = HEX " . dechex($dbf_offset) . " (record $input_count)");#
						fseek($fp_dbf, $dbf_offset);
						$vlf = fread($fp_dbf, 6);

						$vlf_ascii = "" . ord($vlf[0]) . "," . ord($vlf[1]) . "," . ord($vlf[2]) . "," . 
											ord($vlf[3]) . "," . ord($vlf[4]) . "," . ord($vlf[5]);
						$vlf_hex = "" . dechex(ord($vlf[0])) . "," . dechex(ord($vlf[1])) . "," . dechex(ord($vlf[2])) . "," . 
											dechex(ord($vlf[3])) . "," . dechex(ord($vlf[4])) . "," . dechex(ord($vlf[5]));
						if ($verbose) dlog("VLF ASCII = \"$vlf_ascii\", HEX = \"$vlf_hex\"");#

						$dbv_offset = vlf_convert_raw($vlf);
						if ($verbose) dlog("VLF/{$input_count}: \"$vlf\" = \"$vlf_ascii\" -> offset=\"$dbv_offset\"");#

						$notes_text = '';
						if ($dbv_offset > 0)
						{
							$notes_count++;

							fseek($fp_dbv, $dbv_offset);
							$dbv_readlen = 100000;
							$dbv_buffer = fread($fp_dbv, $dbv_readlen);
							$gotlen = strlen($dbv_buffer);
							if ($gotlen < 8)
							{
								if ($verbose) dlog("*=* (Sys=$sys) DBV Read problem: VLF: \"$vlf\" = \"$vlf_ascii\" -> " .
									"offset=\"$dbv_offset\". " . (($gotlen > 0) ? "Only $gotlen" : "No") . " bytes read. " .
									"\$input_count = $input_count. No Notes extracted.");
							}
							else 
							{
								# First 6 bytes are size of Pascal TDBVStru structure.
								if ($verbose)
								{
									$six = "" . ord($dbv_buffer[0]) . "," . ord($dbv_buffer[1]) . "," . ord($dbv_buffer[2]) . "," . 
												ord($dbv_buffer[3]) . "," . ord($dbv_buffer[4]) . "," . ord($dbv_buffer[5]);
									if ($verbose) dlog("DBV 6-pack = \"$six\"");
								}
								$dbv_len = 0;
								$dbv_len += ord($dbv_buffer[0]);
								$dbv_len += (ord($dbv_buffer[1]) << 8);
								$dbv_len += (ord($dbv_buffer[2]) << 16);
								$dbv_len += (ord($dbv_buffer[3]) << 32);
								#if ((0 < $stop_after) && ($input_count <= $stop_after) && 
								#											(($stop_after - $input_count) < $stop_margin))
									if ($verbose) dlog("DBV Notes length = $dbv_len");#
								$notes_text = substr($dbv_buffer, 6, $dbv_len);
								if ((0 < $stop_after) && ($input_count <= $stop_after) && 
																			(($stop_after - $input_count) < $stop_margin))
									if ($verbose) dlog("DBV Notes/1(" . strlen($notes_text) . ")=\"$notes_text\"");
								$notes_text = trim($notes_text);
								if ($verbose) dlog("DBV Notes/2(" . strlen($notes_text) . ")=\"$notes_text\"");#
							}
						}
						else 
						{
							if ($verbose) dlog("*=* (Sys=$sys) No DBV offset: VLF: \"$vlf\" = \"$vlf_ascii\" -> offset=\"$dbv_offset\". " .
												"\$input_count = $input_count", true);
							if ($verbose)
								dlog("Job (Seq=$j_sequence) has no notes"); # Not an error
						}
						
						$sql = "UPDATE JOB SET JC_IMP_NOTES_VMAX=" . ($notes_text ? quote_smart($notes_text, true) : "''") . " WHERE JOB_ID=$job_id";
						if ($verbose) dlog($sql);
						sql_execute($sql);
						if ($verbose) dlog("...done update ($job_id)");
						
						$job_count++;
					} # if ($not_null_count == 0)
				}
				else
				{
					dlog("*=* JOB_ID not found from Sequence $j_sequence, \$input_count = $input_count", true);
					dlog("...one_record=" . print_r($one_record,1));
				}
			} # if (0 < $input_count)
			$input_count++;
			if (($input_count % 10000) == 0)
				dlog("fix_col_notes($sys): done $input_count input records so far");
			if ((0 < $stop_after) && ($stop_after < $input_count))
			{
				dlog("Stopping after $stop_after jobs");
				#break; # while(true)
			}
			#if ($test_done)
			#	break;
		} # while (true)

		$time_taken = 1.0 * (time() - $start_time) / 60.0;
		$per_rec = 60.0 * $time_taken / (1.0 * $input_count / 1000.0);
		dlog("Imported $job_count jobs from a $sys_txt file of " . ($input_count-1) . " jobs (and 1 header record).<br>" .
				"Input file had min sequence of $seq_min, max of $seq_max, <br>" .
				"had $blank_count blank records and had $seq_zero_count zero sequences.<br>" .
				"Time taken: $time_taken mins ($per_rec secs per 1000 jobs)");
		
		fclose($fp_dbf);
		fclose($fp_dbv);
		fclose($fp_csv);
	} # if ($fp_dbf && $fp_dbv && $fp_csv)
	else
	{
		if (!$fp_dbf)
			dlog("*=* (Sys=$sys) Failed to fopen(\"$dirname/$dbf\",'r')");
		if (!$fp_dbv)
			dlog("*=* (Sys=$sys) Failed to fopen(\"$dirname/$dbv\",'r')");
		if (!$fp_csv)
			dlog("*=* (Sys=$sys) Failed to fopen(\"$dirname/$csv\",'r')");
	}

} # dump_col_notes()

function set_target_fees()
{
	# Import 31/12/16:
	# Actually done on 09/01/17, after the system went live.
	#	import_2017_01_09_0919.log
	#	8176 clients need targets set, from a total of 8194 clients.
	#	2017-01-09 09:20:25/1 Set target fees for 8176 clients.
	#	Time taken: 1.2666666666667 mins (9.2954990215264 secs per 1000 jobs)

	$enabled = false; #
	if (!$enabled)
	{
		dlog("This import function is disabled", true);
		return;
	}
	
	print "<h3>Set Target Fees</h3>";
	
	$start_time = time();
	set_time_limit(60 * 60 * 8); # 8 hours
	
	$sql = "SELECT COUNT(*) FROM CLIENT2";
	sql_execute($sql);
	$client_count = 0;
	while (($newArray = sql_fetch()) != false)
		$client_count = $newArray[0];
	
	$sql = "SELECT CLIENT2_ID FROM CLIENT2 WHERE CLIENT2_ID NOT IN (SELECT CLIENT2_ID FROM CLIENT_TARGET_LINK)";
	sql_execute($sql);
	$tclients = array();
	while (($newArray = sql_fetch()) != false)
		$tclients[] = $newArray[0];
	$tcount = count($tclients);
	
	print "<p>$tcount clients need targets set, from a total of $client_count clients</p>";
	
	$output_count = 0;
	foreach ($tclients as $client2_id)
	{
		$sql = "INSERT INTO CLIENT_TARGET_LINK (CLIENT2_ID, JOB_TARGET_ID, CT_FEE) VALUES ($client2_id, 1, 44.0)";
		if ($output_count < 10) dlog($sql);
		sql_execute($sql); # no need to audit
		$sql = "INSERT INTO CLIENT_TARGET_LINK (CLIENT2_ID, JOB_TARGET_ID, CT_FEE) VALUES ($client2_id, 2, 55.0)";
		if ($output_count < 10) dlog($sql);
		sql_execute($sql); # no need to audit
		$sql = "INSERT INTO CLIENT_TARGET_LINK (CLIENT2_ID, JOB_TARGET_ID, CT_FEE) VALUES ($client2_id, 3, 90.0)";
		if ($output_count < 10) dlog($sql);
		sql_execute($sql); # no need to audit
		$sql = "INSERT INTO CLIENT_TARGET_LINK (CLIENT2_ID, JOB_TARGET_ID, CT_FEE) VALUES ($client2_id, 4, 100.0)";
		if ($output_count < 10) dlog($sql);
		sql_execute($sql); # no need to audit
		$output_count++;
	}

	$time_taken = 1.0 * (time() - $start_time) / 60.0;
	$per_rec = 60.0 * $time_taken / (1.0 * $tcount / 1000.0);
	dlog("Set target fees for $tcount clients.<br>" .
		"Time taken: $time_taken mins ($per_rec secs per 1000 jobs)");
	
} # set_target_fees()

function import_front_details()
{
	# ** This function is INCREMENTAL ** - if you want to run it from scratch then do this first: UPDATE JOB SET J_FRONT_DETAILS=NULL

	# Import "Front Details" for all jobs.
	# Copy WORKADD1 to WORKADD5 to JOB.J_FRONT_DETAILS.
	
	# Import 31/12/16:
	#	Actually done 11/01/17 after go-live date.
	#	Trace:
	#		import_2017_01_11_1057.log
	#		2017-01-11 11:00:42/1 Imported 30 details from 47548 jobs from a Traces file of 47549 jobs (and 1 header record).
	#		Input file had min sequence of 473, max of 798347, <br>had 1 blank records and had 0 zero sequences.
	#		Time taken: 3.0833333333333 mins (3.8906414300736 secs per 1000 jobs)
	#	Collect:
	#		import_2017_01_11_1101.log
	#		2017-01-11 11:39:21/1 Imported 130251 details from 473191 jobs from a Collections file of 473202 jobs (and 1 header record).
	#		Input file had min sequence of 90000002, max of 90902920, <br>had 11 blank records and had 9 zero sequences.
	#		Time taken: 37.716666666667 mins (4.7823027326539 secs per 1000 jobs)
	
	global $col_nums;
	global $input_count; # from caller e.g. import_jobs()
	global $j_sequence; # from import_jobs()
	global $one_record;
	global $tc;
	
	$enabled = false; #
	if (!$enabled)
	{
		dlog("This import function is disabled", true);
		return;
	}
	
	print "<h3>Import \"Front Detail\"</h3>";
	
	if (($tc != 'c') && ($tc != 't'))
	{
		dlog("*=* import_front_details() - illegal tc \"$tc\"");
		return;
	}
	
	$start_time = time();
	set_time_limit(60 * 60 * 8); # 8 hours
	
	$stop_after = 0;#
	$stop_margin = 0;
	$input_count = 0;
	$first_job_id = 0;
	
	$sys = $tc; # Either 't' or 'c' from this point
	$dirname = "import-vilcol/{$sys}";
	if ($sys == 't')
	{
		$sys_txt = "Traces";

		#$dbf = "TRACES.DBF"; # In files dated Jan 2015, there are 57,808 jobs. Takes 24 mins to import on RDR server.
		#$dbv = "TRACES.DBV";
		$csv = "TRACES.csv";
		
		# File offsets into DBF
		#$dbf_firstoff = 3683; # offset into DBF of first data record (after the header record)
		#$dbf_reclen = 3780; # length of each data record in DBF
	}
	else
	{
		$sys_txt = "Collections";

		#$dbf = "COLLECT.DBF"; # In files dated Jan 2015, there are 427,058 jobs. Takes 161 mins to import on RDR server.
		#$dbv = "COLLECT.DBV";
		$csv = "COLLECT.csv";
		
		# File offsets into DBF
		#$dbf_firstoff = 2883; # offset into DBF of first data record (after the header record)
		#$dbf_reclen = 1460; # length of each data record in DBF
	}

	#$fp_dbf = fopen("$dirname/$dbf", 'r');
	#$fp_dbv = fopen("$dirname/$dbv", 'r');
	$fp_csv = fopen("$dirname/$csv", 'r');
	#if ($fp_dbf && $fp_dbv && $fp_csv)
	if ($fp_csv)
	{
		$col_nums = array();
		
		$seq_min = 0;
		$seq_max = 0;
		$seq_zero_count = 0;
		$seq_zero_replacement = 101;
		$job_count = 0;
		$blank_count = 0;
		$notes_count = 0;
		
		$while_safety = 0; # to avoid infinite-looping
		while (true)
		{
			$verbose = false;
			if ((0 < $stop_after) && ($input_count <= $stop_after) && (($stop_after - $input_count) < $stop_margin))
				$verbose = true;
			
			$while_safety++;
			if ($while_safety > 800000) # 800,000 jobs
			{
				dlog("*=* (Sys=$sys) Safety Ejection!");
				break; # while(true)
			}

			if ($verbose)
				dlog("=== Record $input_count ===");
				
			$one_record = fgetcsv($fp_csv);
			
			# Check for a blank data record
			if (($input_count > 0) && $one_record && (count($one_record) > 0))
			{
				$blank_rec = true;
				foreach ($one_record as $or)
				{
					if (($or != 0) && (trim($or) != ''))
					{
						$blank_rec = false;
						break; # from foreach()
					}
				}
				if ($blank_rec)
				{
					$input_count++;
					$blank_count++;
					continue; # around while(true)
				}
			}
			
			if ($input_count == 0) # header record
				dlog("First record: " . print_r($one_record,1), true);
			else
			{
				$vilno_2015 = 0;
				$sequence_2015 = 0;
				$vs_2015 = 0;
				
				if ($one_record && (count($one_record) > 0)) # test for end-of-records
				{
					$seq_raw = csvfield('SEQUENCE');
					$j_sequence = 1 * $seq_raw;
					if ($j_sequence == -1)
					{
						# This might be a job that's been archived leaving it with sequence = -1 and vilno = 0.
						# Look it up in the COLLECT_DBF_2015 table to try and find it's correct sequence number and vilno.
						$where = array();
						if (csvfield('DATEREC', 'uk') != '')
							$where[] = "DATEREC=" . quote_smart(csvfield('DATEREC', 'uk'), true);
						else 
							$where[] = "DATEREC IS NULL";
						$where[] = "LASTNAME=" . quote_smart(csvfield('LASTNAME'), true);
						$where[] = "CLIREF=" . quote_smart(csvfield('CLIREF'), true);
						$where[] = "CLID=" . csvfield('CLID');
						$sql = "SELECT TOP 1 VILNO, SEQUENCE FROM COLLECT_DBF_2015 WHERE (" . implode(') AND (', $where) . ")";
						if ($verbose)
							dlog($sql);
						sql_execute($sql);
						while (($newArray = sql_fetch()) != false)
						{
							$vilno_2015 = 1 * $newArray[0];
							$sequence_2015 = 1 * $newArray[1];
						}
						if ((0 < $vilno_2015) && (0 < $sequence_2015))
						{
							$j_sequence = $sequence_2015;
							$vs_2015 = 1;
							if ($verbose)
								dlog("Found V=$vilno_2015 & S=$sequence_2015 from: $sql");
						}
						else
						{
							$vilno_2015 = 0;
							$sequence_2015 = 0;
						}
					}
					if ($j_sequence > 0)
					{
						if (($seq_min == 0) || ($j_sequence < $seq_min))
							$seq_min = $j_sequence;
						if (($seq_max == 0) || ($seq_max < $j_sequence))
							$seq_max = $j_sequence;
					}
					else
					{
						$j_sequence = $seq_zero_replacement++;
						$seq_zero_count++;
						if ($seq_zero_count <= 1000)
							dlog("*=* (Sys=$sys) Non-numeric or illegal SEQUENCE \"$seq_raw\" changed to $j_sequence " .
									"(record $input_count, VILNO=\"" . csvfield('VILNO') . "\", \$seq_zero_count=$seq_zero_count)");
					}
				}
				else 
				{
					dlog("End of Jobs");
					break; # while(true)
				}
				
				$old_verbose = $verbose;
				
				if (($j_sequence == 90149542) || ($j_sequence == 90493801)# || ($j_sequence == 70940)) 70940 only for import of 20/09/16
					#|| (($one_record[0] == -1) && (trim($one_record[8]) == '54B MORAY ROAD'))
					|| in_fix_aux_list($one_record)
					)
				{
					# See also $fix_list
					#$verbose = true; #
					if ($verbose)
						dlog("*=* Found sequence=$j_sequence. col_nums:<br>" . print_r($col_nums,1) . "<br>" . 
								"Record:<br>" . print_r($one_record,1));
					for ($ii = 88; 36 <= $ii; $ii--)
						$one_record[$ii] = $one_record[$ii-4];
					$bits = explode(',', str_replace('"', '', $one_record[31]));
					$one_record[31] = str_replace('\\', '', $bits[0]);
					$one_record[32] = $bits[1];
					$one_record[33] = $bits[2];
					$one_record[34] = $bits[3];
					$one_record[35] = $bits[4];
					if ($verbose)
						dlog("Cleaned Record:<br>" . print_r($one_record,1));
				}
				elseif ($j_sequence == 730508)
				{
					# See also $fix_list
					#$verbose = true; #
					if ($verbose)
					{
						dlog("*=* Found sequence=$j_sequence. col_nums:<br>" . print_r($col_nums,1) . "<br>" . 
								"Record:<br>" . print_r($one_record,1));
					}
					$one_record[70] = str_replace('foren\",m', 'forenm', $one_record[70]);
					$one_record[71] = str_replace('"', '', $one_record[71]);
					if ($verbose)
						dlog("Cleaned:<br>" . print_r($one_record,1));
				}
				elseif ($j_sequence == 90503059)
				{
					# See also $fix_list
					#$verbose = true; #
					if ($verbose)
						dlog("*=* Found sequence=$j_sequence. col_nums:<br>" . print_r($col_nums,1) . "<br>" . 
								"Record:<br>" . print_r($one_record,1));
					for ($ii = 88; 15 <= $ii; $ii--)
						$one_record[$ii] = $one_record[$ii-1];
					$bits = explode(',', str_replace('"', '', $one_record[13]));
					$one_record[13] = str_replace('\\', '', $bits[0]);
					$one_record[14] = $bits[1];
					if ($verbose)
						dlog("Cleaned Record:<br>" . print_r($one_record,1));
				}
				elseif (($j_sequence == 90694238) || ($j_sequence == 90700518))
				{
					# See also $fix_list
					#$verbose = true; #
					if ($verbose)
						dlog("*=* Found sequence=$j_sequence. col_nums:<br>" . print_r($col_nums,1) . "<br>" . 
								"Record:<br>" . print_r($one_record,1));
					for ($ii = 88; 28 <= $ii; $ii--)
						$one_record[$ii] = $one_record[$ii-7];
					$bits = explode(',', str_replace('"', '', $one_record[20]));
					$one_record[20] = str_replace('\\', '', $bits[0]);
					$one_record[21] = $bits[1];
					$one_record[22] = $bits[2];
					$one_record[23] = $bits[3];
					$one_record[24] = $bits[4];
					$one_record[25] = $bits[5];
					$one_record[26] = $bits[6];
					$one_record[27] = $bits[7];
					if ($verbose)
						dlog("Cleaned Record:<br>" . print_r($one_record,1));
				}
				elseif (($j_sequence == 90707120) || ($j_sequence == 90738552))
				{
					# See also $fix_list
					#$verbose = true; #
					if ($verbose)
						dlog("*=* Found sequence=$j_sequence. col_nums:<br>" . print_r($col_nums,1) . "<br>" . 
								"Record:<br>" . print_r($one_record,1));
					$vlf = $one_record[81];
					if ($verbose)
						dlog("VLF=\"$vlf\"");
					for ($ii = 87; 28 <= $ii; $ii--)
						$one_record[$ii] = $one_record[$ii-7];
					$bits = explode(',', str_replace('"', '', $one_record[20]));
					$one_record[20] = str_replace('\\', '', $bits[0]);
					$one_record[21] = $bits[1];
					$one_record[22] = $bits[2];
					$one_record[23] = $bits[3];
					$one_record[24] = $bits[4];
					$one_record[25] = $bits[5];
					$one_record[26] = $bits[6];
					$one_record[27] = $bits[7];
					$one_record[88] = $vlf;
					if ($verbose)
						dlog("Cleaned Record:<br>" . print_r($one_record,1));
				}
				elseif (($j_sequence == 90714469) || ($j_sequence == 90717737))
				{
					# See also $fix_list
					#$verbose = true; #
					if ($verbose)
						dlog("*=* Found sequence=$j_sequence. col_nums:<br>" . print_r($col_nums,1) . "<br>" . 
								"Record:<br>" . print_r($one_record,1));
					$vlf = $one_record[87];
					if ($verbose)
						dlog("VLF=\"$vlf\"");
					for ($ii = 87; 28 <= $ii; $ii--)
						$one_record[$ii] = $one_record[$ii-1];
					$bits = explode(',', str_replace('"', '', $one_record[26]));
					$one_record[26] = str_replace('\\', '', $bits[0]);
					$one_record[27] = $bits[1];
					$one_record[88] = $vlf;
					if ($verbose)
						dlog("Cleaned Record:<br>" . print_r($one_record,1));
				}
				elseif ($j_sequence == 90723905)
				{
					# See also $fix_list
					#$verbose = true; #
					if ($verbose)
						dlog("*=* Found sequence=$j_sequence. col_nums:<br>" . print_r($col_nums,1) . "<br>" . 
								"Record:<br>" . print_r($one_record,1));
					$vlf = $one_record[83];
					if ($verbose)
						dlog("VLF=\"$vlf\"");
					for ($ii = 87; 26 <= $ii; $ii--)
						$one_record[$ii] = $one_record[$ii-5];
					$bits = explode(',', str_replace('"', '', $one_record[20]));
					$one_record[20] = str_replace('\\', '', $bits[0]);
					$one_record[21] = $bits[1];
					$one_record[22] = $bits[2];
					$one_record[23] = $bits[3];
					$one_record[24] = $bits[4];
					$one_record[25] = $bits[5];
					$one_record[88] = $vlf;
					if ($verbose)
						dlog("Cleaned Record:<br>" . print_r($one_record,1));
				}
				#if ($j_sequence != 90707120)#
				#	continue;
				$verbose = $old_verbose;
			} # if ($input_count > 0) # skip header record
				
			if ((0 < $stop_after) && ($stop_after < $input_count))
			{
				$input_count++;
				continue;
			}
						
			if ($verbose)
				dlog((($input_count == 0) ? "Headers: " : "Job: ") . print_r($one_record,1));#
			if ($input_count == 0)
			{
				# Header record
				foreach ($one_record as $num => $field)
					$col_nums[$field] = $num;
				if ($verbose)
					dlog("col_nums: " . print_r($col_nums,1));#
			}

			if (0 < $input_count)
			{
				# Data record

				$job_id = job_id_from_sequence($j_sequence);
				if (0 < $job_id)
				{
					$sql = "SELECT COUNT(*) FROM JOB WHERE (JOB_ID=$job_id) AND (J_FRONT_DETAILS IS NOT NULL)";
					$not_null_count = 0;
					sql_execute($sql);
					while (($newArray = sql_fetch()) != false)
						$not_null_count = 1 * $newArray[0];
					if ($not_null_count == 0)
					{
						if ($first_job_id == 0)
						{
							$first_job_id = $job_id;
							$verbose = true;
							dlog("Starting with JOB_ID $job_id, Sequence $j_sequence, \$input_count = $input_count, input record =<br>" . print_r($one_record,1), true);
						}
						
						$details_text = '';
						$temp = csvfield('WORKADD1');
						if ($temp)
							$details_text .= ($details_text ? ' ' : '') . $temp;
						$temp = csvfield('WORKADD2');
						if ($temp)
							$details_text .= ($details_text ? ' ' : '') . $temp;
						$temp = csvfield('WORKADD3');
						if ($temp)
							$details_text .= ($details_text ? ' ' : '') . $temp;
						$temp = csvfield('WORKADD4');
						if ($temp)
							$details_text .= ($details_text ? ' ' : '') . $temp;
						$temp = csvfield('WORKADD5');
						if ($temp)
							$details_text .= ($details_text ? ' ' : '') . $temp;
						
						$sql = "UPDATE JOB SET J_FRONT_DETAILS=" . ($details_text ? quote_smart($details_text, true) : "''") . " WHERE JOB_ID=$job_id";
						if ($verbose) dlog($sql);
						sql_execute($sql);
						if ($verbose) dlog("...done update ($job_id)");
						if ($details_text)
							$notes_count++;
					} # if ($not_null_count == 0)
					$job_count++;
				}
				else
				{
					dlog("*=* JOB_ID not found from Sequence $j_sequence, \$input_count = $input_count", true);
					dlog("...one_record=" . print_r($one_record,1));
				}
			} # if (0 < $input_count)
			$input_count++;
			if (($input_count % 10000) == 0)
				dlog("import_front_details($sys): done $input_count input records so far");
			#if ($first_job_id) #
			#	break;
			if ((0 < $stop_after) && ($stop_after < $input_count))
			{
				dlog("Stopping after $stop_after jobs");
				#break; # while(true)
			}
			#if ($test_done)
			#	break;
		} # while (true)

		$time_taken = 1.0 * (time() - $start_time) / 60.0;
		$per_rec = 60.0 * $time_taken / (1.0 * $input_count / 1000.0);
		dlog("Imported $notes_count details from $job_count jobs from a $sys_txt file of " . ($input_count-1) . " jobs (and 1 header record).<br>" .
				"Input file had min sequence of $seq_min, max of $seq_max, <br>" .
				"had $blank_count blank records and had $seq_zero_count zero sequences.<br>" .
				"Time taken: $time_taken mins ($per_rec secs per 1000 jobs)");
		
		#fclose($fp_dbf);
		#fclose($fp_dbv);
		fclose($fp_csv);
	} # if ($fp_dbf && $fp_dbv && $fp_csv)
	else
	{
		#if (!$fp_dbf)
		#	dlog("*=* (Sys=$sys) Failed to fopen(\"$dirname/$dbf\",'r')");
		#if (!$fp_dbv)
		#	dlog("*=* (Sys=$sys) Failed to fopen(\"$dirname/$dbv\",'r')");
		if (!$fp_csv)
			dlog("*=* (Sys=$sys) Failed to fopen(\"$dirname/$csv\",'r')");
	}

} # import_front_details()

function import_next_letter()
{
	# Mend "next letter" after it was messed up by a buggy auto_letter.php
	# For all collection jobs:
	#	- get audit list of JOB.JC_LETTER_TYPE_ID
	#	- if all audit items were done by SYSTEM user
	#		then get "old" letter type of first audit item
	#		and set "next letter" to that

	# 2017-01-23 20:01:40/1 Min,max = 47549,524081
	# 2017-01-23 21:13:28/1 Finished, JOB_ID=524081, updated 9241 jobs

	set_time_limit(12 * 60 * 60); # 12 hours
	
	$debug_one_job = 0;#493132;#
	$debug_limit = 0;#10;#
	$jobs_found = 0;
	
	$min_id = sql_select_single("SELECT MIN(JOB_ID) FROM JOB WHERE JC_JOB=1");
	$max_id = sql_select_single("SELECT MAX(JOB_ID) FROM JOB WHERE JC_JOB=1");
	dlog("Min,max = $min_id,$max_id");
	
	for ($job_id = $min_id; $job_id <= $max_id; $job_id++)
	{
		if ($job_id == 47992)
			$verbose = true;
		else
			$verbose = false;
		
		if ($verbose || (($job_id % 10000) == 0))
			dlog("Now on JOB_ID $job_id");
		
		if ($debug_one_job && ($job_id != $debug_one_job))
			continue;
		
		sql_encryption_preparation('AUDIT');
		$sql = "SELECT A.AUDIT_ID, A.CHANGE_DT, A.CHANGE_ID, " . sql_decrypt('OLD_VAL', '', true) . ", " . sql_decrypt('NEW_VAL', '', true) . "
				FROM AUDIT AS A
				INNER JOIN JOB AS J ON A.TABLE_NAME='JOB' AND A.ID_NAME='JOB_ID' AND A.ID_VALUE=J.JOB_ID AND A.FIELD_NAME='JC_LETTER_TYPE_ID' 
				WHERE J.JOB_ID=$job_id AND J.JC_JOB=1 AND J.OBSOLETE=0
				ORDER BY A.AUDIT_ID";
		if ($verbose)
			dlog($sql);
		sql_execute($sql);
		$audits = array();
		$real_user_count = 0;
		$first_letter = 0;
		while (($newArray = sql_fetch_assoc()) != false)
		{
			if (!$first_letter)
				$first_letter = 1 * $newArray['OLD_VAL'];
			if ($newArray['CHANGE_ID'] != -1)
				$real_user_count++;
			$audits[] = $newArray;
		}
		if ($verbose)
			dlog("audits=" . print_r($audits,1));
		
		if ($audits)
		{
			if ($verbose || $debug_one_job)
				dprint("Audits=" . print_r($audits,1) . "\r\nReal user count = $real_user_count, first_letter=$first_letter");

			if ($real_user_count == 0)
			{
				$jobs_found++;
				if (!$first_letter)
					$first_letter = 'NULL';
				# Set the field to NULL first, so that we force an audit record to be created for setting it to $first_letter
				for ($force_audit = 1; $force_audit <= 2; $force_audit++)
				{
					if ($force_audit == 1)
						$letter_type = 'NULL';
					else
						$letter_type = $first_letter;
					$sql = "UPDATE JOB SET JC_LETTER_TYPE_ID=$letter_type WHERE JOB_ID=$job_id";
					if ($verbose || ($jobs_found <= 10))
						dlog("Job number $jobs_found: $sql");
					if ($debug_one_job || $debug_limit)
						dprint($sql);
					else
					{
						audit_setup_job($job_id, 'JOB', 'JOB_ID', $job_id, 'JC_LETTER_TYPE_ID', $letter_type);
						sql_execute($sql, true); # audited
						if ($verbose || ($jobs_found <= 10))
							dlog("...executed and audited");
					}
				}
			}
		}
		if ($debug_limit && ($debug_limit <= $jobs_found))
		{
			dprint("Breaking out...");
			$job_id++;
			break;
		}	
	} # for ($job_id)
	$job_id--;
	
	dlog("Finished, JOB_ID=$job_id, updated $jobs_found jobs");
	
} # import_next_letter()

function import_letter_sequences_fix()
{
	# Remove duplicate letter sequences
	
	$sql = "SELECT CLIENT2_ID, SEQ_NUM, COUNT(*) FROM LETTER_SEQ GROUP BY CLIENT2_ID, SEQ_NUM HAVING 1<COUNT(*)
			ORDER BY CLIENT2_ID, SEQ_NUM";
	sql_execute($sql);
	$seqs = array();
	while (($newArray = sql_fetch()) != false)
		$seqs[] = $newArray;
	dlog("seqs = " . print_r($seqs,1));
	
	foreach ($seqs as $one)
	{
		$client2_id = $one[0];
		$seq_num = $one[1];
		$sql = "SELECT LETTER_SEQ_ID FROM LETTER_SEQ WHERE CLIENT2_ID=$client2_id AND SEQ_NUM=$seq_num"; # should return 2 records
		sql_execute($sql);
		$seq_id = 0;
		while (($newArray = sql_fetch()) != false)
			$seq_id = $newArray[0];
		$sql = "DELETE FROM LETTER_SEQ WHERE LETTER_SEQ_ID=$seq_id";
		dlog($sql);
		audit_setup_gen('LETTER_SEQ', 'LETTER_SEQ_ID', $seq_id, '', '');
		sql_execute($sql, true); #audited
	}
} # import_letter_sequences_fix()

function set_job_percent()
{
	# Some jobs no longer have the commission rate of their client.
	# For these jobs, set the commission rate to that of theic client.
	# For the payments on those jobs, do what?
	# Best to generate a list and ask Jim what needs doing.
	
	global $ar;
	global $sqlTrue;
	
	sql_encryption_preparation('JOB_SUBJECT');
	
	$top = 0;#
	$sql = "SELECT " . ($top ? "TOP $top" : '') . "
				C.C_CODE, J.J_VILNO, J.J_SEQUENCE, C.COMM_PERCENT, J.JC_PERCENT,
				" . sql_decrypt('S.JS_FIRSTNAME', '', true) . ", " . sql_decrypt('S.JS_LASTNAME', '', true) . ",
				" . sql_decrypt('S.JS_COMPANY', '', true) . "
			FROM JOB AS J INNER JOIN CLIENT2 AS C ON C.CLIENT2_ID=J.CLIENT2_ID
			LEFT JOIN JOB_SUBJECT AS S ON S.JOB_ID=J.JOB_ID AND S.JS_PRIMARY=$sqlTrue
			WHERE J.JC_JOB=$sqlTrue AND J.JC_PERCENT<>C.COMM_PERCENT
			ORDER BY C.C_CODE, J.J_VILNO, J.J_SEQUENCE
			";
	sql_execute($sql);
	$jobs = array();
	while (($newArray = sql_fetch_assoc()) != false)
	{
		$newArray['COMM_PERCENT'] = 1.0 * $newArray['COMM_PERCENT'];
		$newArray['JC_PERCENT'] = 1.0 * $newArray['JC_PERCENT'];
		$jobs[] = $newArray;
	}
	dlog("jobs = " . print_r($jobs,1));
	
	if ($jobs)
	{
		print "
		<table class=\"spaced_table\">
		<tr>
			<th>Client</th><th>VILNo</th><th>Sequence</th><th>Client Commission</th><th>Job Commission</th><th>Subject</th>
		</tr>
		";
		foreach ($jobs as $one)
		{
			$subject = trim("{$one['JS_FIRSTNAME']} {$one['JS_LASTNAME']} {$one['JS_COMPANY']}");
			print "
			<tr>
				<td $ar>{$one['C_CODE']}</td>		<td $ar>{$one['J_VILNO']}</td>		<td $ar>{$one['J_SEQUENCE']}</td>
				<td $ar>{$one['COMM_PERCENT']}</td>	<td $ar>{$one['JC_PERCENT']}</td>	<td $ar>$subject</td>
			</tr>
			";
		}
		print "
		</table>
		";
	}
	else
		print "<p>All collection jobs have the same commission percentage as their client</p>";
	
} # set_job_percent()

function import_trans_assignment_ids()
{
	# Import TDX Assignment IDs, from COLLECT.DBF AN2 field.
	# See Feedback #125.
	# Before calling this function:
	#	- Check that we have already exported COLLECT/COLLECT.DBF as COLLECT.csv - see import_jobs()
	#	- Check that import_trans() has already been run.
	# Required:
	#	c/COLLECT.csv
	
	# Import 07/04/17:
	#	import_2017_04_07_1752.log
	#	Imported 103342 transaction codes from a Collections file of 473202 jobs (and 1 header record).
	#	Time taken: 6.8833333333333 mins (3.9964390083412 secs per 1000 jobs)
	
	global $col_nums; # for csvfield()
	global $input_count; # for csvfield()
	global $j_sequence; # for csvfield()
	global $one_record; # for csvfield()
	global $tc;
		
	$enabled = false; #
	if (!$enabled)
	{
		dprint("This import function is disabled", true);
		return;
	}
	if ($tc != 'c')
	{
		dlog("*=* import_trans_assignment_id() - illegal tc \"$tc\"");
		return;
	}

	print "<h3>Importing TDX Assignment IDs (Collect system)</h3>";
	
	$start_time = time();
	set_time_limit(60 * 60 * 8); # 8 hours
	
	init_trans_assignment_ids();
	
	$stop_after = 0;#
	$stop_margin = 0;
	$input_count = 0;
	
	$sys = $tc; # 'c'
	$dirname = "import-vilcol/{$sys}";
	{
		$sys_txt = "Collections";
		$csv = "COLLECT.csv";
	}
	
	sql_encryption_preparation('JOB');
	
	$fp_csv = fopen("$dirname/$csv", 'r');
	if ($fp_csv)
	{
		$col_nums = array();
		$trans_count = 0;
		
		$while_safety = 0; # to avoid infinite-looping
		while (true)
		{
			$verbose = false;
			#if ((0 < $stop_after) && ($input_count <= $stop_after) && (($stop_after - $input_count) <= $stop_margin))
			#	$verbose = true;
			if ((0 < $stop_after) && ($trans_count <= $stop_after) && (($stop_after - $trans_count) <= $stop_margin))
				$verbose = true;
			
			$while_safety++;
			if ($while_safety > 800000) # 800,000 jobs
			{
				dlog("*=* (Sys=$sys) Safety Ejection!");
				break; # while(true)
			}

			if ($verbose)
				dprint("=== Record $input_count ===");
				
			$one_record = fgetcsv($fp_csv);
			$j_sequence = -1;
			if ($input_count > 0) # skip header record
			{
				if ($one_record && (count($one_record) > 0)) # test for end-of-records
				{
					$seq_raw = csvfield('SEQUENCE');
					$j_sequence = 1 * $seq_raw;
				}
				else 
				{
					dlog("End of Jobs, input_count=$input_count, trans_count=$trans_count");
					break; # while(true)
				}
				
//				if ($j_sequence == 90730472)#
//				{
//					$verbose = true;
//					dprint("=== Record $input_count ===");
//				}
				
				$old_verbose = $verbose;
				if (($j_sequence == 90149542) || ($j_sequence == 90493801)
						#|| (($one_record[0] == -1) && (trim($one_record[8]) == '54B MORAY ROAD')))
						|| in_fix_aux_list($one_record))
				{
					for ($ii = 88; 36 <= $ii; $ii--)
						$one_record[$ii] = $one_record[$ii-4];
					$bits = explode(',', str_replace('"', '', $one_record[31]));
					$one_record[31] = str_replace('\\', '', $bits[0]);
					$one_record[32] = $bits[1];
					$one_record[33] = $bits[2];
					$one_record[34] = $bits[3];
					$one_record[35] = $bits[4];
				}
				elseif ($j_sequence == 730508)
				{
					$one_record[70] = str_replace('foren\",m', 'forenm', $one_record[70]);
					$one_record[71] = str_replace('"', '', $one_record[71]);
				}
				elseif ($j_sequence == 90503059)
				{
					for ($ii = 88; 15 <= $ii; $ii--)
						$one_record[$ii] = $one_record[$ii-1];
					$bits = explode(',', str_replace('"', '', $one_record[13]));
					$one_record[13] = str_replace('\\', '', $bits[0]);
					$one_record[14] = $bits[1];
				}
				elseif (($j_sequence == 90694238) || ($j_sequence == 90700518))
				{
					for ($ii = 88; 28 <= $ii; $ii--)
						$one_record[$ii] = $one_record[$ii-7];
					$bits = explode(',', str_replace('"', '', $one_record[20]));
					$one_record[20] = str_replace('\\', '', $bits[0]);
					$one_record[21] = $bits[1];
					$one_record[22] = $bits[2];
					$one_record[23] = $bits[3];
					$one_record[24] = $bits[4];
					$one_record[25] = $bits[5];
					$one_record[26] = $bits[6];
					$one_record[27] = $bits[7];
				}
				elseif (($j_sequence == 90707120) || ($j_sequence == 90738552))
				{
					$vlf = $one_record[81];
					for ($ii = 87; 28 <= $ii; $ii--)
						$one_record[$ii] = $one_record[$ii-7];
					$bits = explode(',', str_replace('"', '', $one_record[20]));
					$one_record[20] = str_replace('\\', '', $bits[0]);
					$one_record[21] = $bits[1];
					$one_record[22] = $bits[2];
					$one_record[23] = $bits[3];
					$one_record[24] = $bits[4];
					$one_record[25] = $bits[5];
					$one_record[26] = $bits[6];
					$one_record[27] = $bits[7];
					$one_record[88] = $vlf;
				}
				elseif (($j_sequence == 90714469) || ($j_sequence == 90717737))
				{
					$vlf = $one_record[87];
					for ($ii = 87; 28 <= $ii; $ii--)
						$one_record[$ii] = $one_record[$ii-1];
					$bits = explode(',', str_replace('"', '', $one_record[26]));
					$one_record[26] = str_replace('\\', '', $bits[0]);
					$one_record[27] = $bits[1];
					$one_record[88] = $vlf;
				}
				elseif ($j_sequence == 90723905)
				{
					$vlf = $one_record[83];
					for ($ii = 87; 26 <= $ii; $ii--)
						$one_record[$ii] = $one_record[$ii-5];
					$bits = explode(',', str_replace('"', '', $one_record[20]));
					$one_record[20] = str_replace('\\', '', $bits[0]);
					$one_record[21] = $bits[1];
					$one_record[22] = $bits[2];
					$one_record[23] = $bits[3];
					$one_record[24] = $bits[4];
					$one_record[25] = $bits[5];
					$one_record[88] = $vlf;
				}
				#if ($j_sequence != 90707120)#
				#	continue;
				$verbose = $old_verbose;
				
//				if (csvfield('CLIREF') == 'L459478')#
//				{
//					$verbose = true;
//					dprint("=== Record $input_count === Found \"" . csvfield('CLIREF') . "\"");
//				}
			}
		
//			if ((0 < $stop_after) && ($stop_after < $input_count))
//			{
//				$input_count++;
//				continue;
//			}

			if ($verbose)
				dprint((($input_count == 0) ? "Headers: " : "Job: ") . print_r($one_record,1));#
			if ($input_count == 0)
			{
				# Header record
				foreach ($one_record as $num => $field)
					$col_nums[$field] = $num;
				if ($verbose)
					dprint("col_nums: " . print_r($col_nums,1));#
			}
			else
			{
				# Data record

				# We can be pretty sure that CLIREF is filled in when AN1 has a value.
				$client_ref = csvfield('CLIREF');
				if ($client_ref)
				{
					$tdx_assignment_id = csvfield('AN2');
					if ($tdx_assignment_id)# && (strpos($tdx_assignment_id, ' ') === false) && (strpos($tdx_assignment_id, '-') !== false))
					{
						$client_ref = quote_smart($client_ref, true);
						$sql = "SELECT JOB_ID FROM JOB WHERE J_SEQUENCE = $j_sequence";
						if ($verbose)
							dprint($sql);
						sql_execute($sql);
						$job_id = 0;
						while (($newArray = sql_fetch()) != false)
							$job_id = $newArray[0];

						if ($job_id)
						{
							if ($trans_count < $stop_margin)
							{
								#$verbose = true;
								dlog("=== Record $input_count === (trans_count=$trans_count)");
								dlog((($input_count == 0) ? "Headers: " : "Job: ") . print_r($one_record,1));#
							}
							$tdx_assignment_id = quote_smart($tdx_assignment_id, true);
							$sql= "UPDATE JOB SET JC_TRANS_CNUM=$tdx_assignment_id WHERE JOB_ID=$job_id";
							#if ($verbose)
							if ($trans_count < 100)
								dlog($sql);
							sql_execute($sql); # no need to audit
							$trans_count++;
						}
					}
				}
			}
			
			$input_count++;
			if (($input_count % 100000) == 0)
				dlog("import_trans($sys): done $input_count input records so far");
			if ((0 < $stop_after) && ($stop_after < $trans_count))
			{
				#dlog("Stopping after $stop_after jobs");
				#break; # while(true)
			}
			
		} # while(true)

		$time_taken = 1.0 * (time() - $start_time) / 60.0;
		$per_rec = ($trans_count ? (60.0 * $time_taken / (1.0 * $trans_count / 1000.0)) : 0.0);
		dlog("Imported $trans_count transaction codes from a $sys_txt file of " . ($input_count-1) . " jobs (and 1 header record).<br>" .
				"Time taken: $time_taken mins ($per_rec secs per 1000 jobs)");
		
		fclose($fp_csv);
	}
	else
		dlog("*=* (Sys=$sys) Failed to fopen(\"$dirname/$csv\",'r')");

} # import_trans_assignment_id()

function ftp_test()
{
	dprint("ftp_test()");
	
	$ftp_server = "***";
	$ftp_user_name = "***";
	$ftp_user_pass = "***";
	$source_file = "favicon.ico";
	$destination_file = "server_favicon.ico";
	
	dprint("Connecting to \"$ftp_server\"...");
	$conn_id = ftp_ssl_connect($ftp_server);
	if ($conn_id)
	{
		dprint("...connected, conn_id=$conn_id");
		
		dprint("Logging in as user \"$ftp_user_name\" (with password too)...");
		$login_result = ftp_login($conn_id, $ftp_user_name, $ftp_user_pass);
		if ($login_result)
		{
			dprint("...logged in, login_result=$login_result");
			ftp_pasv($conn_id, true); # needed for SSL FTP
			
			dprint("Uploading \"$source_file\" to \"$destination_file\"...");
			$upload = ftp_put($conn_id, $destination_file, $source_file, FTP_BINARY);
			if ($upload)
				dprint("...uploaded OK");
			else
				dprint("...upload failed");
		}
		else
			dprint("...login failed");
		
		ftp_close($conn_id); 	
	}
	else
		dprint("...connection failed");
	
} # ftp_test()

function postcode_outcodes()
{
	set_time_limit(60 * 60 * 8); # 8 hours
	
	$max_id = sql_select_single("SELECT MAX(JOB_SUBJECT_ID) FROM JOB_SUBJECT");
	dprint("max_id=$max_id");
	
	sql_encryption_preparation('JOB_SUBJECT');
//	$s_count = 0;
	for ($chunk_min = 1; $chunk_min <= $max_id; $chunk_min += 10000)
	{
		$chunk_max = $chunk_min + 9999;
		$sql = "SELECT JOB_SUBJECT_ID, " . sql_decrypt('JS_ADDR_PC', '', true). ", " . sql_decrypt('NEW_ADDR_PC', '', true). "
				FROM JOB_SUBJECT WHERE $chunk_min <= JOB_SUBJECT_ID AND JOB_SUBJECT_ID <= $chunk_max";
		dprint($sql);
		sql_execute($sql);
		$subjects = array();
		while (($newArray = sql_fetch_assoc()) != false)
			$subjects[] = $newArray;
		dprint("Found " . count($subjects) . " subjects");
		foreach ($subjects as $one_s)
		{
			$job_subject_id = $one_s['JOB_SUBJECT_ID'];
			$js_outcode = postcode_outcode($one_s['JS_ADDR_PC'], true);
			if ($js_outcode)
			{
				$sql = "UPDATE JOB_SUBJECT SET JS_OUTCODE='{$js_outcode}' WHERE JOB_SUBJECT_ID=$job_subject_id";
//				if ($s_count < 20)
//					dprint("ID $job_subject_id: PC=\"{$one_s['JS_ADDR_PC']}\" Outcode=\"{$js_outcode}\" SQL=$sql");
//				$s_count ++;
				sql_execute($sql);
			}
			$new_outcode = postcode_outcode($one_s['NEW_ADDR_PC'], true);
			if ($new_outcode)
			{
				$sql = "UPDATE JOB_SUBJECT SET NEW_OUTCODE='{$new_outcode}' WHERE JOB_SUBJECT_ID=$job_subject_id";
//				if ($s_count < 20)
//					dprint("ID $job_subject_id: PC=\"{$one_s['NEW_ADDR_PC']}\" Outcode=\"{$new_outcode}\" SQL=$sql");
//				$s_count ++;
				sql_execute($sql);
			}
		}
	}
	
	
} # postcode_outcodes()

function laravel_preprocess()
{
	global $laravel_do_client_2;
	global $laravel_do_job;
	global $laravel_do_letter;
	global $laravel_do_subject;
	global $sql_ignore_error; # 29/06/22

	$msg = "Hello from laravel_preprocess() <br>\n\n" .
			($laravel_do_client_2 ? "\$laravel_do_client_2" : '') . " <br>\n\n" .
			($laravel_do_job ? "\$laravel_do_job" : '') . " <br>\n\n" .
			($laravel_do_letter ? "\$laravel_do_letter" : '') . " <br>\n\n" .
			($laravel_do_subject ? "\$laravel_do_subject" : '') . " <br>\n\n" .
			"";
	dprint($msg, true);
	log_write($msg);

	set_time_limit(60 * 60 * 8); # 8 hours
	$sql_ignore_error = true;

	if ($laravel_do_client_2)
	{
		$max_id = sql_select_single("SELECT MAX(CLIENT2_ID) FROM CLIENT2");
		dprint("CLIENT2_ID: max_id=$max_id", true);
	
		sql_encryption_preparation('C_ADDR_3');
		sql_encryption_preparation('C_ADDR_4');
		sql_encryption_preparation('C_ADDR_5');
		$record_count = 0;
		$chunk_size = 200;
		for ($chunk_min = 1; $chunk_min <= $max_id; $chunk_min += $chunk_size)
		{
			$chunk_max = $chunk_min + ($chunk_size -1 );
			$sql = "SELECT CLIENT2_ID, " . sql_decrypt('C_ADDR_3', '', true). ", " . sql_decrypt('C_ADDR_4', '', true). ", " . sql_decrypt('C_ADDR_5', '', true). "
					FROM CLIENT2 WHERE $chunk_min <= CLIENT2_ID AND CLIENT2_ID <= $chunk_max";
			dprint($sql, true);
			sql_execute($sql);
			$records = array();
			while (($newArray = sql_fetch_assoc()) != false)
				$records[] = $newArray;
			#dprint("Found " . count($records) . " clients", true);
			$loop_count = 0;
			foreach ($records as $one_rec)
			{
				$sub_count = 0;
				if ($one_rec['C_ADDR_3'])
				{
					$sql = "UPDATE CLIENT2 SET C_ADDR_3_DEC=" . quote_smart($one_rec['C_ADDR_3'],true) . " WHERE CLIENT2_ID={$one_rec['CLIENT2_ID']}";
					if (($loop_count < 10) && ($record_count < 10))
						dprint($sql, true);
					$sub_count++;
					sql_execute($sql);
				}
				if ($one_rec['C_ADDR_4'])
				{
					$sql = "UPDATE CLIENT2 SET C_ADDR_4_DEC=" . quote_smart($one_rec['C_ADDR_4'],true) . " WHERE CLIENT2_ID={$one_rec['CLIENT2_ID']}";
					if (($loop_count < 10) && ($record_count < 10))
						dprint($sql, true);
					$sub_count++;
					sql_execute($sql);
				}
				if ($one_rec['C_ADDR_5'])
				{
					$sql = "UPDATE CLIENT2 SET C_ADDR_5_DEC=" . quote_smart($one_rec['C_ADDR_5'],true) . " WHERE CLIENT2_ID={$one_rec['CLIENT2_ID']}";
					if (($loop_count < 10) && ($record_count < 10))
						dprint($sql, true);
					$sub_count++;
					sql_execute($sql);
				}
				if ($sub_count)
					$loop_count++;
			} # foreach ($records as $one_rec)
			$record_count += $loop_count;
			dprint("...done $loop_count clients in this loop. Done $record_count clients in total.", true);
		} # for ($chunk_min = 1; ...)
	} # if ($laravel_do_client_2)

	if ($laravel_do_job)
	{
		$max_id = sql_select_single("SELECT MAX(JOB_ID) FROM JOB");
		dprint("JOB_ID: max_id=$max_id", true);
	
		sql_encryption_preparation('JT_LET_REPORT');
		$record_count = 0;
		$chunk_size = 10000;
		for ($chunk_min = 1; $chunk_min <= $max_id; $chunk_min += $chunk_size)
		{
			$chunk_max = $chunk_min + ($chunk_size -1 );
			$sql = "SELECT JOB_ID, " . sql_decrypt('JT_LET_REPORT', '', true) . "
					FROM JOB WHERE $chunk_min <= JOB_ID AND JOB_ID <= $chunk_max";
			dprint($sql, true);
			sql_execute($sql);
			$records = array();
			while (($newArray = sql_fetch_assoc()) != false)
				$records[] = $newArray;
			#dprint("Found " . count($records) . " jobs", true);
			$loop_count = 0;
			foreach ($records as $one_rec)
			{
				$sub_count = 0;
				if ($one_rec['JT_LET_REPORT'])
				{
					$sql = "UPDATE JOB SET JT_LET_REPORT_DEC=" . quote_smart($one_rec['JT_LET_REPORT'],true) . " WHERE JOB_ID={$one_rec['JOB_ID']}";
					if (($loop_count < 10) && ($record_count < 10))
						dprint($sql, true);
					$sub_count++;
					sql_execute($sql);
				}
				if ($sub_count)
					$loop_count++;
			} # foreach ($records as $one_rec)
			$record_count += $loop_count;
			dprint("...done $loop_count jobs in this loop. Done $record_count jobs in total.", true);
		} # for ($chunk_min = 1; ...)
	} # if ($laravel_do_job)

	if ($laravel_do_letter)
	{
		# This can run multiple times.
		# Before the first run, all JL_TEXT_DEC and JL_TEXT_2_DEC fields should be set to NULL.
		# Every record that is processed will leave JL_TEXT_DEC and JL_TEXT_2_DEC non-NULL.
		# Thus, this script can be run more than once, only processing records with a NULL JL_TEXT_DEC each time.
		
		#$min_id = sql_select_single("SELECT MIN(JOB_LETTER_ID) FROM JOB_LETTER WHERE JL_TEXT_DEC IS NULL");
		$min_id = intval(sql_select_single("SELECT MAX(JOB_LETTER_ID) FROM JOB_LETTER WHERE JL_TEXT_DEC IS NOT NULL")) + 1;
		$max_max_id = sql_select_single("SELECT MAX(JOB_LETTER_ID) FROM JOB_LETTER WHERE JL_TEXT_DEC IS NULL");

		# 10,000 takes about 30 mins.
		$super_chunk_size = 0.5 * 10000;

		$max_id = min($max_max_id, $min_id + $super_chunk_size - 1);
		dprint("JOB_LETTER_ID: min_id=$min_id, max_id=$max_id (from $max_max_id)", true);

		sql_encryption_preparation('JL_TEXT');
		sql_encryption_preparation('JL_TEXT_2');
		$record_count = 0;
		$blank_count = 0;
		$chunk_size = 100;
		for ($chunk_min = $min_id; $chunk_min <= $max_id; $chunk_min += $chunk_size)
		{
			$chunk_max = $chunk_min + $chunk_size - 1;
			$sql = "SELECT JOB_LETTER_ID, " . sql_decrypt('JL_TEXT', '', true) . ", " . sql_decrypt('JL_TEXT_2', '', true) . "
					FROM JOB_LETTER WHERE ($chunk_min <= JOB_LETTER_ID) AND (JOB_LETTER_ID <= $chunk_max)";
			dprint($sql, true);
			sql_execute($sql);
			$records = array();
			while (($newArray = sql_fetch_assoc()) != false)
				$records[] = $newArray;
			#dprint("Found " . count($records) . " jobs", true);
			$loop_count = 0;
			foreach ($records as $one_rec)
			{
				if ($one_rec['JL_TEXT'])
				{
					$new_dec = quote_smart($one_rec['JL_TEXT'],true);
					$new_brief = "'stuff'";
				}
				else
				{
					$new_dec = "''";
					$new_brief = $new_dec;
				}
				$sql = "UPDATE JOB_LETTER SET JL_TEXT_DEC=$new_dec WHERE JOB_LETTER_ID={$one_rec['JOB_LETTER_ID']}";
				$sql_brief = "UPDATE JOB_LETTER SET JL_TEXT_DEC=$new_brief WHERE JOB_LETTER_ID={$one_rec['JOB_LETTER_ID']}";
				if (($loop_count < 10) && ($record_count < 10))
					dprint($sql_brief, true);
				sql_execute($sql);

				if ($one_rec['JL_TEXT_2'])
				{
					$new_dec = quote_smart($one_rec['JL_TEXT_2'],true);
					$new_brief = "'stuff'";
				}
				else
				{
					$new_dec = "''";
					$new_brief = $new_dec;
				}
				$sql = "UPDATE JOB_LETTER SET JL_TEXT_2_DEC=$new_dec WHERE JOB_LETTER_ID={$one_rec['JOB_LETTER_ID']}";
				$sql_brief = "UPDATE JOB_LETTER SET JL_TEXT_2_DEC=$new_brief WHERE JOB_LETTER_ID={$one_rec['JOB_LETTER_ID']}";
				if (($loop_count < 10) && ($record_count < 10))
					dprint($sql_brief, true);
				sql_execute($sql);

				$loop_count++;
			} # foreach ($records as $one_rec)
			$record_count += $loop_count;
			$msg = "...done $loop_count letters in this loop (IDs $chunk_min to $chunk_max). Done $record_count letters in total.";
			dprint($msg, true);
			log_write($msg);
		} # for ($chunk_min = 1; ...)
		$msg = "JOB_LETTER, IDs $min_id to $max_id, done $record_count letters.";
		dprint($msg, true);
		log_write($msg);
	} # if ($laravel_do_letter)

	if ($laravel_do_subject)
	{
		# This can run multiple times.
		# Before the first run, all JS_LASTNAME_DEC, JS_ADDR_1_DEC and JS_ADDR_2_DEC fields should be set to NULL.
		# Every record that is processed will leave JS_LASTNAME_DEC, JS_ADDR_1_DEC and JS_ADDR_2_DEC non-NULL.
		# Thus, this script can be run more than once, only processing records with a NULL JS_ADDR_1_DEC each time.
		
		#$min_id = sql_select_single("SELECT MIN(JOB_SUBJECT_ID) FROM JOB_SUBJECT WHERE JS_LASTNAME_DEC IS NULL");
		$min_id = intval(sql_select_single("SELECT MAX(JOB_SUBJECT_ID) FROM JOB_SUBJECT WHERE JS_LASTNAME_DEC IS NOT NULL")) + 1;
		$max_max_id = sql_select_single("SELECT MAX(JOB_SUBJECT_ID) FROM JOB_SUBJECT WHERE JS_LASTNAME_DEC IS NULL");

		# 10,000 takes about 40 mins.
		$super_chunk_size = 0.5 * 10000;

		$max_id = min($max_max_id, $min_id + $super_chunk_size - 1);
		dprint("JOB_SUBJECT_ID: min_id=$min_id, max_id=$max_id (from $max_max_id)", true);

		sql_encryption_preparation('JS_LASTNAME');
		sql_encryption_preparation('JS_ADDR_1');
		sql_encryption_preparation('JS_ADDR_2');
		$record_count = 0;
		$blank_count = 0;
		$chunk_size = 100;
		for ($chunk_min = $min_id; $chunk_min <= $max_id; $chunk_min += $chunk_size)
		{
			$chunk_max = $chunk_min + $chunk_size - 1;
			$sql = "SELECT JOB_SUBJECT_ID, " . sql_decrypt('JS_LASTNAME', '', true) . ", " . sql_decrypt('JS_ADDR_1', '', true) . ", " . sql_decrypt('JS_ADDR_2', '', true) . "
					FROM JOB_SUBJECT WHERE ($chunk_min <= JOB_SUBJECT_ID) AND (JOB_SUBJECT_ID <= $chunk_max)";
			dprint($sql, true);
			sql_execute($sql);
			$records = array();
			while (($newArray = sql_fetch_assoc()) != false)
				$records[] = $newArray;
			#dprint("Found " . count($records) . " jobs", true);
			$loop_count = 0;
			foreach ($records as $one_rec)
			{
				if ($one_rec['JS_LASTNAME'])
				{
					$new_dec = quote_smart($one_rec['JS_LASTNAME'],true);
					$new_brief = "'stuff'";
				}
				else
				{
					$new_dec = "''";
					$new_brief = $new_dec;
				}
				$sql = "UPDATE JOB_SUBJECT SET JS_LASTNAME_DEC=$new_dec WHERE JOB_SUBJECT_ID={$one_rec['JOB_SUBJECT_ID']}";
				$sql_brief = "UPDATE JOB_SUBJECT SET JS_LASTNAME_DEC=$new_brief WHERE JOB_SUBJECT_ID={$one_rec['JOB_SUBJECT_ID']}";
				if (($loop_count < 10) && ($record_count < 10))
					dprint($sql_brief, true);
				sql_execute($sql);

				if ($one_rec['JS_ADDR_1'])
				{
					$new_dec = quote_smart($one_rec['JS_ADDR_1'],true);
					$new_brief = "'stuff'";
				}
				else
				{
					$new_dec = "''";
					$new_brief = $new_dec;
				}
				$sql = "UPDATE JOB_SUBJECT SET JS_ADDR_1_DEC=$new_dec WHERE JOB_SUBJECT_ID={$one_rec['JOB_SUBJECT_ID']}";
				$sql_brief = "UPDATE JOB_SUBJECT SET JS_ADDR_1_DEC=$new_brief WHERE JOB_SUBJECT_ID={$one_rec['JOB_SUBJECT_ID']}";
				if (($loop_count < 10) && ($record_count < 10))
					dprint($sql_brief, true);
				sql_execute($sql);

				if ($one_rec['JS_ADDR_2'])
				{
					$new_dec = quote_smart($one_rec['JS_ADDR_2'],true);
					$new_brief = "'stuff'";
				}
				else
				{
					$new_dec = "''";
					$new_brief = $new_dec;
				}
				$sql = "UPDATE JOB_SUBJECT SET JS_ADDR_2_DEC=$new_dec WHERE JOB_SUBJECT_ID={$one_rec['JOB_SUBJECT_ID']}";
				$sql_brief = "UPDATE JOB_SUBJECT SET JS_ADDR_2_DEC=$new_brief WHERE JOB_SUBJECT_ID={$one_rec['JOB_SUBJECT_ID']}";
				if (($loop_count < 10) && ($record_count < 10))
					dprint($sql_brief, true);
				sql_execute($sql);

				$loop_count++;
			} # foreach ($records as $one_rec)
			$record_count += $loop_count;
			$msg = "...done $loop_count subjects in this loop (IDs $chunk_min to $chunk_max). Done $record_count subjects in total.";
			dprint($msg, true);
			log_write($msg);
		} # for ($chunk_min = 1; ...)
		$msg = "JOB_SUBJECT, IDs $min_id to $max_id, done $record_count subjects.";
		dprint($msg, true);
		log_write($msg);
	} # if ($laravel_do_subject)

} # laravel_preprocess()

function laravel_reset()
{
	global $laravel_do_client_2;
	global $laravel_do_job;
	global $laravel_do_letter;
	global $laravel_do_subject;

	$msg = "Hello from laravel_reset() <br>\n\n" .
			($laravel_do_client_2 ? "\$laravel_do_client_2" : '') . " <br>\n\n" .
			($laravel_do_job ? "\$laravel_do_job" : '') . " <br>\n\n" .
			($laravel_do_letter ? "\$laravel_do_letter" : '') . " <br>\n\n" .
			($laravel_do_subject ? "\$laravel_do_subject" : '') . " <br>\n\n" .
			"";
	dprint($msg, true);
	log_write($msg);

	set_time_limit(60 * 60 * 8); # 8 hours

	if ($laravel_do_client_2)
		$fields = array('C_ADDR_3', 'C_ADDR_4', 'C_ADDR_5');

	elseif ($laravel_do_job)
		$fields = array('JT_LET_REPORT');

	elseif ($laravel_do_letter)
		$fields = array('JL_TEXT', 'JL_TEXT_2');

	elseif ($laravel_do_subject)
		$fields = array('JS_LASTNAME', 'JS_ADDR_1', 'JS_ADDR_2');

	else
		$fields = array();
		
	foreach ($fields as $onef)
	{
		$sql = "UPDATE CLIENT2 SET {$onef}_DEC=NULL";
		dprint($sql, true);
		sql_execute($sql);
	}

} # laravel_reset()

?>
