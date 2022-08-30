<?php

# Used in Vilcol from 03/08/16. Before that, unused. Originally, used for testing mail sending.
#
# In Vilcol, all outgoing emails should be sent by either mail_wrapper() or mail_pm() (the latter can send HTML+Plain content and attachments).

include_once("lib_pm.php"); # for mail_pm()

//function mail_wrapper($mailto, $subject, $message, $headers)
//{
//	# This is a wrapper for the standard mail() function.
//	if (global_debug())
//	{
//		$mailto = 'kevinbeckett1@gmail.com';
//		#dprint("*** MAIL DISABLED FOR KEVIN ***");
//	}
//	else
//		mail($mailto, $subject, $message, $headers);
//}

function email_job_letter()
{
	# This function is called from jobs.php which must establish $job_id before calling this function.

	global $csv_dir;
	global $email_collections;
	global $email_trace;
	global $emailName_collections;
	global $emailName_trace;
	global $id_ACTIVITY_lse;
	global $job_id; # from jobs.php
	global $sqlFalse;
	global $sqlTrue;

	$abort = false;
	$mailto = post_val('email_main_addr');
	$subject = post_val('email_main_subject');
	$message = nl2br(post_val('email_main_message', false, false, false, 1));
	$letter_id = post_val('letter_id', true);

	$sql = "SELECT J.J_VILNO, J.J_SEQUENCE, L.JOB_LETTER_ID, L.JL_APPROVED_DT, L.JL_CREATED_DT, J.JT_JOB
			FROM JOB AS J INNER JOIN JOB_LETTER AS L ON L.JOB_ID=J.JOB_ID
			WHERE J.JOB_ID=$job_id AND L.JOB_LETTER_ID=$letter_id";
	#dprint($sql);#
	sql_execute($sql);
	$letter_filename = '';
	$jt_job = 0;
	$vilno = '';
	while (($newArray = sql_fetch_assoc()) != false)
	{
		$vilno = $newArray['J_VILNO'];
		$dt0 = ($newArray['JL_CREATED_DT'] ? $newArray['JL_CREATED_DT'] : $newArray['JL_APPROVED_DT']);
		$timestamp = str_replace(' ', '_', str_replace('-', '', str_replace(':', '', str_replace('.000', '', trim($dt0)))));
		$letter_filename = "letter_{$vilno}_{$newArray['J_SEQUENCE']}_{$newArray['JOB_LETTER_ID']}_{$timestamp}.pdf";
		$jt_job = intval($newArray['JT_JOB']);
	}
	$letter_path = "{$csv_dir}/v{$vilno}/{$letter_filename}";
	if (file_exists($letter_path))
	{
		#dprint("Found \"$letter_path\"");#
	}
	else
	{
		dprint("Cannot find Letter \"$letter_path\"", true);
		$abort = true;
	}

	if ($jt_job)
	{
		# This is only done for Trace jobs. It is possible to not have an invoice for a trace job.
		list($ms_top, $my_limit) = sql_top_limit(1);
		$sql = "SELECT $ms_top I.INVOICE_ID, I.INV_NUM, I.INV_APPROVED_DT, C.C_CODE
				FROM INVOICE AS I INNER JOIN INV_BILLING AS B ON B.INVOICE_ID=I.INVOICE_ID
				LEFT JOIN CLIENT2 AS C ON C.CLIENT2_ID=I.CLIENT2_ID
				WHERE B.JOB_ID=$job_id AND B.OBSOLETE=$sqlFalse AND I.INV_APPROVED_DT IS NOT NULL
				$my_limit ";
		#dprint($sql);#
		sql_execute($sql);
		$invoice_id = 0;
		$invoice_filename = '';
		$c_code = '';
		while (($newArray = sql_fetch_assoc()) != false)
		{
			$invoice_id = $newArray['INVOICE_ID'];
			$timestamp = str_replace(' ', '_', str_replace('-', '', str_replace(':', '', str_replace('.000', '', trim($newArray['INV_APPROVED_DT'])))));
			$invoice_filename = "invoice_{$newArray['INV_NUM']}_{$timestamp}.pdf";
			$c_code = $newArray['C_CODE'];
		}
		$invoice_dir = check_dir("{$csv_dir}/c{$c_code}");
		$invoice_path = "{$invoice_dir}/{$invoice_filename}";
		#dprint("\$c_code=$c_code, \$invoice_dir=$invoice_dir, \$invoice_path=$invoice_path");#
		if ((0 < $invoice_id) && (!file_exists($invoice_path)))
		{
			dprint("Cannot find Invoice \"$invoice_path\"", true);
			$abort = true;
		}
	}

	if ($abort)
		dprint("** Email aborted **", true);
	else
	{
		if ($jt_job)
		{
			if (0 < $invoice_id)
			{
				$attachment_file = array($letter_path, $invoice_path);
				$attachment_name = array('Trace Letter.pdf', 'Trace Invoice.pdf');
			}
			else
			{
				$attachment_file = $letter_path;
				$attachment_name = 'Trace Letter.pdf';
			}
			$email_from = $email_trace;
			$emailName_from = $emailName_trace;
		}
		else
		{
			$attachment_file = $letter_path;
			$attachment_name = 'Collection Letter.pdf';
			$email_from = $email_collections;
			$emailName_from = $emailName_collections;
		}
		mail_pm($mailto, $subject, $message, $email_from, $emailName_from, $attachment_file, $attachment_name,
					'', '', '', true);

		sql_encryption_preparation('EMAIL');
		$em_to = sql_encrypt($mailto, false, 'EMAIL');
		$em_subject = sql_encrypt($subject, false, 'EMAIL');
		$em_message = sql_encrypt($message, false, 'EMAIL');
		$em_attach = ($jt_job ? "'v{$vilno}/{$letter_filename}|c{$c_code}/{$invoice_filename}'" : "'v{$vilno}/{$letter_filename}'");
		$now = date_now_sql();
		$invoice_id_sql = ($invoice_id ? $invoice_id : 'NULL');

		$fields = "JOB_ID,  CLIENT2_ID, EM_DT,  EM_TO,   EM_CC, EM_BCC, EM_SUBJECT,  EM_MESSAGE,  EM_ATTACH,  INVOICE_ID";
		$values = "$job_id, NULL,       '$now', $em_to,  NULL,  NULL,   $em_subject, $em_message, $em_attach, $invoice_id_sql";

		$sql = "INSERT INTO EMAIL ($fields) VALUES ($values)";
		dprint($sql);
		audit_setup_job($job_id, 'EMAIL', 'EMAIL_ID', 0, '', '');
		$new_email_id = sql_execute($sql, true); # audited

		# If the job letter has been emailed before, then make that old email record obsolete.
		$old_email_id = intval(sql_select_single("SELECT JL_EMAIL_ID FROM JOB_LETTER WHERE JOB_LETTER_ID=$letter_id"));
		if (0 < $old_email_id)
		{
			$sql = "UPDATE EMAIL SET OBSOLETE=$sqlTrue WHERE EMAIL_ID=$old_email_id";
			dprint($sql);
			audit_setup_job($job_id, 'EMAIL', 'EMAIL_ID', $old_email_id, 'OBSOLETE', $sqlTrue);
			sql_execute($sql, true); # audited
		}

		$sql = "UPDATE JOB_LETTER SET JL_EMAIL_ID=$new_email_id WHERE JOB_LETTER_ID=$letter_id";
		dprint($sql);
		audit_setup_job($job_id, 'JOB_LETTER', 'JOB_LETTER_ID', $letter_id, 'JL_EMAIL_ID', $new_email_id);
		sql_execute($sql, true); # audited

		sql_add_activity($job_id, 1, $id_ACTIVITY_lse, false); # don't call sql_update_job()

		sql_update_letter($job_id, $letter_id);

		if (($jt_job) && (0 < $invoice_id))
		{
			# If the invoice has been emailed before, then make that old email record obsolete.
			$old_email_id = intval(sql_select_single("SELECT INV_EMAIL_ID FROM INVOICE WHERE INVOICE_ID=$invoice_id"));
			if (0 < $old_email_id)
			{
				$sql = "UPDATE EMAIL SET OBSOLETE=$sqlTrue WHERE EMAIL_ID=$old_email_id";
				dprint($sql);
				audit_setup_job($job_id, 'EMAIL', 'EMAIL_ID', $old_email_id, 'OBSOLETE', $sqlTrue);
				sql_execute($sql, true); # audited
			}

			$sql = "UPDATE INVOICE SET INV_EMAIL_ID=$new_email_id WHERE INVOICE_ID=$invoice_id";
			dprint($sql);
			audit_setup_job($job_id, 'INVOICE', 'INVOICE_ID', $invoice_id, 'INV_EMAIL_ID', $new_email_id);
			sql_execute($sql, true); # audited
		}

		if ($jt_job)
		{
			$sql = "UPDATE JOB SET J_COMPLETE=1 WHERE JOB_ID=$job_id";
			dprint($sql);
			audit_setup_job($job_id, 'JOB', 'JOB_ID', $job_id, 'J_COMPLETE', 1);
			sql_execute($sql, true); # audited

			sql_close_job($job_id);
		}

		sql_update_job($job_id);
	}

} # email_job_letter()

//function email_job_letter_resend($letter_id, $resend_to)
//{
//	# Resend an email that was sent earlier.
//	# Trace job, letter and/or invoice.
//	# Return empty string for success.
//
//	global $csv_dir;
//	global $email_ccare;
//	global $emailName_ccare;
//	global $id_ACTIVITY_lse;
//	global $job_id;
//
//	$debug = false;#
//
//	$email_id = sql_select_single("SELECT JL_EMAIL_ID FROM JOB_LETTER WHERE JOB_LETTER_ID=$letter_id AND JOB_ID=$job_id");
//	if (!$email_id)
//		return "Email ID not found for letter $letter_id and job $job_id";
//
//	sql_encryption_preparation('EMAIL');
//	$sql = "SELECT " . sql_decrypt('EM_TO', '', true) . ", " . sql_decrypt('EM_CC', '', true) . ", " . sql_decrypt('EM_BCC', '', true) . ",
//				" . sql_decrypt('EM_SUBJECT', '', true) . ", " . sql_decrypt('EM_MESSAGE', '', true) . ", EM_ATTACH
//			FROM EMAIL WHERE EMAIL_ID=$email_id AND JOB_ID=$job_id";
//	sql_execute($sql);
//	$em_to = '';
//	$em_info = array();
//	while (($newArray = sql_fetch_assoc()) != false)
//	{
//		$em_to = $newArray['EM_TO'];
//		$em_info = $newArray;
//	}
//	if (!$em_to)
//		return "No \"To\" address was found for Email ID $email_id";
//	$new_to = (($em_to != $resend_to) ? true : false);
//	$em_to = $resend_to;
//
//	if ($debug) dprint("email_job_letter_resend() letter_id=$letter_id, job_id=$job_id, email_id=$email_id, email info = " . print_r($em_info,1));
//
//	$bits = explode('|', $em_info['EM_ATTACH']);
//	$letter_path = "{$csv_dir}/{$bits[0]}";
//	$invoice_path = "{$csv_dir}/{$bits[1]}";
//	if (!file_exists($letter_path))
//	{
//		if ($debug) dprint("Letter \"$letter_path\" not found");
//		$letter_path = '';
//	}
//	if (!file_exists($invoice_path))
//	{
//		if ($debug) dprint("Invoice \"$invoice_path\" not found");
//		$invoice_path = '';
//	}
//	$attachment_file = array();
//	$attachment_name = array();
//	if ($letter_path)
//	{
//		$attachment_file[] = $letter_path;
//		$attachment_name[] = 'Trace Letter.pdf';
//	}
//	if ($invoice_path)
//	{
//		$attachment_file[] = $invoice_path;
//		$attachment_name[] = 'Trace Invoice.pdf';
//	}
//	if ($debug) dprint("calling mail_pm()... letter=\"$letter_path\", invoice=\"$invoice_path\"...");
//	mail_pm($em_to, $em_info['EM_SUBJECT'], $em_info['EM_MESSAGE'], $email_ccare, $emailName_ccare, $attachment_file, $attachment_name,
//					'', '', '', true);
//
//	$resends = sql_select_single("SELECT JL_EMAIL_RESENDS FROM JOB_LETTER WHERE JOB_LETTER_ID=$letter_id");
//	if ($resends)
//		$resends .= "|";
//	$resends .= strftime('%d/%m/%y %H:%M') . ($new_to ? "($em_to)" : '');
//	$sql = "UPDATE JOB_LETTER SET JL_EMAIL_RESENDS='$resends' WHERE JOB_LETTER_ID=$letter_id";
//	audit_setup_job($job_id, 'JOB_LETTER', 'JOB_LETTER_ID', $letter_id, 'JL_EMAIL_RESENDS', $resends);
//	sql_execute($sql, true); # audited
//
//	sql_add_activity($job_id, 1, $id_ACTIVITY_lse, false); # don't call sql_update_job()
//	sql_update_job($job_id);
//
//	if ($debug) dprint("returning success");
//	return "";
//
//} # email_job_letter_resend()

function email_job_letter_resend_207($letter_id, $resend_to, $resend_subject, $resend_message, $resend_letter, $resend_invoice, $resend_invoice_id)
{
	# Feedback #207 16/12/18

	# Resend an email that was sent earlier.
	# Trace job, letter and/or invoice.
	# Return empty string for success.

	global $csv_dir;
	global $email_trace;
	global $emailName_trace;
	global $id_ACTIVITY_lse;
	global $job_id;
	global $sqlTrue;

	$debug = false;#

	$email_id = sql_select_single("SELECT JL_EMAIL_ID FROM JOB_LETTER WHERE JOB_LETTER_ID=$letter_id AND JOB_ID=$job_id");
	if (!$email_id)
		return "Email ID not found for letter $letter_id and job $job_id";

	$em_to = $resend_to;
	$em_info = array('EM_SUBJECT' => $resend_subject, 'EM_MESSAGE' => $resend_message);

	if ($debug) dprint("email_job_letter_resend_207() letter_id=$letter_id, job_id=$job_id, email_id=$email_id, " .
						"invoice_id=$resend_invoice_id, email info = " . print_r($em_info,1));

	$letter_path = "{$csv_dir}/{$resend_letter}";
	$invoice_path = "{$csv_dir}/{$resend_invoice}";
	if (!file_exists($letter_path))
	{
		if ($debug) dprint("Letter \"$letter_path\" not found");
		$letter_path = '';
	}
	if (!file_exists($invoice_path))
	{
		if ($debug) dprint("Invoice \"$invoice_path\" not found");
		$invoice_path = '';
	}
	$attachment_file = array();
	$attachment_name = array();
	if ($letter_path)
	{
		$attachment_file[] = $letter_path;
		$attachment_name[] = 'Trace Letter.pdf';
	}
	if ($invoice_path)
	{
		$attachment_file[] = $invoice_path;
		$attachment_name[] = 'Trace Invoice.pdf';
	}
	if ($debug) dprint("calling mail_pm()... letter=\"$letter_path\", invoice=\"$invoice_path\"...");

	mail_pm($em_to, $em_info['EM_SUBJECT'], $em_info['EM_MESSAGE'], $email_trace, $emailName_trace, $attachment_file, $attachment_name,
					'', '', '', true);

	sql_encryption_preparation('EMAIL');
	$em_to = sql_encrypt($em_to, false, 'EMAIL');
	$em_subject = sql_encrypt($em_info['EM_SUBJECT'], false, 'EMAIL');
	$em_message = sql_encrypt($em_info['EM_MESSAGE'], false, 'EMAIL');
	$em_attach = "'{$resend_letter}|{$resend_invoice}'";
	$now = date_now_sql();
	$invoice_id_sql = ($resend_invoice_id ? $resend_invoice_id : 'NULL');

	$fields = "JOB_ID,  CLIENT2_ID, EM_DT,  EM_TO,   EM_CC, EM_BCC, EM_SUBJECT,  EM_MESSAGE,  EM_ATTACH,  INVOICE_ID";
	$values = "$job_id, NULL,       '$now', $em_to,  NULL,  NULL,   $em_subject, $em_message, $em_attach, $invoice_id_sql";

	$sql = "INSERT INTO EMAIL ($fields) VALUES ($values)";
	dprint($sql);
	audit_setup_job($job_id, 'EMAIL', 'EMAIL_ID', 0, '', '');
	$new_email_id = sql_execute($sql, true); # audited

	# If the job letter has been emailed before, then make that old email record obsolete.
	$old_email_id = intval(sql_select_single("SELECT JL_EMAIL_ID FROM JOB_LETTER WHERE JOB_LETTER_ID=$letter_id"));
	if (0 < $old_email_id)
	{
		$sql = "UPDATE EMAIL SET OBSOLETE=$sqlTrue WHERE EMAIL_ID=$old_email_id";
		dprint($sql);
		audit_setup_job($job_id, 'EMAIL', 'EMAIL_ID', $old_email_id, 'OBSOLETE', $sqlTrue);
		sql_execute($sql, true); # audited
	}

	$sql = "UPDATE JOB_LETTER SET JL_EMAIL_ID=$new_email_id WHERE JOB_LETTER_ID=$letter_id";
	dprint($sql);
	audit_setup_job($job_id, 'JOB_LETTER', 'JOB_LETTER_ID', $letter_id, 'JL_EMAIL_ID', $new_email_id);
	sql_execute($sql, true); # audited

	if (0 < $resend_invoice_id)
	{
		# If the invoice has been emailed before, then make that old email record obsolete.
		$old_email_id = intval(sql_select_single("SELECT INV_EMAIL_ID FROM INVOICE WHERE INVOICE_ID=$resend_invoice_id"));
		if (0 < $old_email_id)
		{
			$sql = "UPDATE EMAIL SET OBSOLETE=$sqlTrue WHERE EMAIL_ID=$old_email_id";
			dprint($sql);
			audit_setup_job($job_id, 'EMAIL', 'EMAIL_ID', $old_email_id, 'OBSOLETE', $sqlTrue);
			sql_execute($sql, true); # audited
		}

		$sql = "UPDATE INVOICE SET INV_EMAIL_ID=$new_email_id WHERE INVOICE_ID=$resend_invoice_id";
		dprint($sql);
		audit_setup_job($job_id, 'INVOICE', 'INVOICE_ID', $resend_invoice_id, 'INV_EMAIL_ID', $new_email_id);
		sql_execute($sql, true); # audited
	}

	sql_add_activity($job_id, 1, $id_ACTIVITY_lse, false); # don't call sql_update_job()
	sql_update_job($job_id);

	if ($debug) dprint("returning success");
	return "";

} # email_job_letter_resend_207()

?>
