<?php

include_once("settings.php");
include_once("library.php");
global $denial_message;
global $navi_1_clients;
global $role_rev;
global $USER; # set by admin_verify()

log_open("vilcol.log");

sql_connect();

admin_verify(); # writes to $USER

if ($USER['IS_ENABLED'])
{
	if (role_check('*', $role_rev))
	{
		$navi_1_clients = true; # settings.php; used by navi_1_heading()
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
	global $added_jobs;
	global $c_code;
	global $client2_id;
	global $errors;
	global $jobs_tc;
	
	dprint(post_values());
	dprint("FILES=" . xprint(print_r($_FILES,1), false, 1));
	set_time_limit(60 * 60 * 1); # 1 hour

	$client2_id = post_val('client2_id', true);
	if (!(0 < $client2_id))
	{
		dprint("No Client Specified", true);
		return;
	}
	
	$code_and_name = client_name_from_id($client2_id);
	$c_code = $code_and_name['C_CODE'];
	$jobs_tc = post_val('jobs_tc');
	get_agent_initials();
	
	print "<h3>Clients Screen</h3>";

	print "<h3>Bulk Upload of Jobs for Client $c_code &mdash; {$code_and_name['C_CO_NAME']}</h3>";
	#print input_button('Back', "back_to_client($client2_id, $c_code)");
	
	if (isset($_POST['upload_marker']))
	{
		if (!$jobs_tc)
		{
			dprint("Trc/Col flag not Specified", true);
			return;
		}
		$file = get_uploaded_file();
		if ($file)
		{
			$added_jobs = array();
			$errors = array();
			import_csv($file);
			if ($errors)
				print_errors();
			print_added_jobs();
			sql_update_client($client2_id);
		}
	}
	
	javascript();
	
	print_form();
}

function screen_content_2()
{
	# This is required by screen_layout()
} # screen_content_2()

function print_errors()
{
	global $ar;
	global $col3;
	global $errors;
	
	print "
	<h3 style=\"color:red;\">The following errors were encountered upon import of the file:</h3>
	<table class=\"spaced_table\">
	<tr>
		<th>CSV Line</th><th>Contents of CSV Line</th><th>Error</th>
	</tr>
	";
	foreach ($errors as $one)
	{
		if ($one['LNO'] == -1)
			print "
			<tr><td $col3>{$one['ERROR']}</td></tr>
			";
		else
			print "
			<tr>
				<td $ar>{$one['LNO']}</td><td>" . print_r($one['LINE'],1) . "</td><td>{$one['ERROR']}</td>
			</tr>
			";
	}
	print "
	</table>
	<hr>
	<br>
	<br>
	";
} # print_errors()

function javascript()
{
//	global $safe_amp;
//	global $safe_slash;
//	global $task;
//	global $uni_pound;
	
	print "
	<script type=\"text/javascript\">
	
	function reload_page()
	{
		please_wait_on_submit();
		document.form_main.submit();
	}
	
//	function back_to_client(c2id,ccode)
//	{
//		document.form_client.client2_id.value = c2id;
//		document.form_client.sc_text.value = ccode;
//		document.form_client.task.value = 'view';
//		please_wait_on_submit();
//		document.form_client.submit();
//	}
	
	function start_upload()
	{
		bits = document.csvupload.uploaded.value.split('.');
		if (bits[bits.length-1].toLowerCase() == 'csv')
		{
			if (document.csvupload.uploaded.value.length > 0)
			{
				var jt = document.getElementById('user_job_type_id').value;
				if ((jt == -1) || (0 < jt))
				{
					document.csvupload.cu_job_type_id.value = jt;
					document.getElementById('span_result').innerHTML = '';
					please_wait_on_submit();
					document.csvupload.submit();
				}
				else
					alert('Please specify a job type');
			}
			else
				alert('Please browse to a file and select it before clicking the Upload button');
		}
		else
			alert('Please select a file with a \".csv\" filename extension');
	}
	
	</script>
	";
}

function print_form()
{
	global $agent_initials;
	global $at;
	global $c_code;
	global $client2_id;
	global $jobs_tc;
	global $upload_result;
	
	print "
	<form name=\"form_main\" action=\"" . server_php_self() . "\" method=\"post\">
		" . input_hidden('client2_id', $client2_id) . "
	Please select either Trace Jobs or Collect Jobs:<br>
	" . input_radio('jobs_tc', array('Trace Jobs' => 't', 'Collect Jobs' => 'c'), $jobs_tc, '<br>', "onclick=\"reload_page()\"") . "
	<br>
	</form><!--form_main-->
	";
	
	if ($jobs_tc)
	{
		if ($jobs_tc == 't')
		{
			$tc_text = 'Trace';
			$job_types = sql_get_job_types(true, false);
			print "Job Type of all jobs in CSV file: " . input_select('user_job_type_id', $job_types, '') . "<br><br>";
		}
		else
		{
			$tc_text = 'Collect';
			$job_types = array();
			print input_hidden('user_job_type_id', -1);
		}
		print "
		<div id=\"div_guide\" style=\"width:600px;\">
		You can now upload a CSV file of $tc_text jobs.<br>
		<div id=\"div_upload\" style=\"width:600px; border:1px solid black; padding-left:10px;\">
		<span id=\"span_result\">$upload_result</span>
		<p>Please browse your system to choose a \".csv\" document to upload.</p>
		<form id=\"csvupload\" method=\"post\" name=\"csvupload\" class=\"upload\" action=\"\" enctype=\"multipart/form-data\">
			" . input_hidden('client2_id', $client2_id) . "
			" . input_hidden('jobs_tc', $jobs_tc) . "
			" . input_hidden('cu_job_type_id', '') . "
			<input name=\"uploaded\" type=\"file\" size=\"50\" /> 
			<input type=\"button\" value=\"Upload\" onclick=\"start_upload();\"/> 
			<br>
			<p>The file must be comma-delimited, have a \".csv\" extension and its size is limited to 614,400 MB.<br>
				<span style=\"color:red;\">...and it should NOT have a header line in it containing column/field names</span></p>
			<input type=\"hidden\" name=\"upload_marker\" value=\"uploading\" />
		</form>
		</div><!--div_upload-->

		<br>
		<table class=\"basic_table\">
		<tr>
			<td $at>
			The columns of the CSV file should be:
			<ul style=\"margin-top:0px;\">
				";
				print "
					<li>Title (Mr, Ms, etc)</li>
					<li>First name</li>
					<li>Last name</li>
					<li>Company name (if applicable)</li>
					<li>Address line 1</li>
					<li>Address line 2</li>
					<li>Address line 3</li>
					<li>Address line 4</li>
					<li>Address postcode</li>
					<li>Telephone (land-line)</li>
					<li>Mobile number</li>
					<li>Account number (client reference)</li>
					<li>DOB</li>
					<li>Email</li>
					<li>Outstanding Amount/Balance</li>
					<li>Any other telephone numbers</li>
					<li>Client code (should be $c_code)</li>
					<li>User code (agent initials) or blank</li>
					<li>Reason for debit (this may<br>spread onto other columns)</li>
					";
//				if ($jobs_tc == 't')
//					print "
//					<li>Title (Mr, Ms, etc)</li>
//					<li>First name</li>
//					<li>Last name</li>
//					<li>Company name (if applicable)</li>
//					<li>Address line 1</li>
//					<li>Address line 2</li>
//					<li>Address line 3</li>
//					<li>Address line 4</li>
//					<li>Address postcode</li>
//					<li>Telephone (land-line)</li>
//					<li>Mobile number</li>
//					<li>Account number (client reference)</li>
//					<li>Notes line 1</li>
//					<li>Notes line 2</li>
//					<li>Notes line 3</li>
//					<li>Notes line 4</li>
//					<li>Client code (should be $c_code)</li>
//					<li>Outstanding Amount (for T&amp;C)</li>
//					";
//				else
//					print "
//					<li>Title (Mr, Ms, etc)</li>
//					<li>First name</li>
//					<li>Last name</li>
//					<li>Company name (if applicable)</li>
//					<li>Address line 1</li>
//					<li>Address line 2</li>
//					<li>Address line 3</li>
//					<li>Address line 4</li>
//					<li>Address postcode</li>
//					<li>Telephone</li>
//					<li>Date of Birth</li>
//					<li>Account number (client reference)</li>
//					<li>End date</li>
//					<li>Balance outstanding</li>
//					<li>Client code (should be $c_code)</li>
//					<li>User code (agent initials) or blank</li>
//					<li>Reason for debit (this may<br>spread onto other columns)</li>
//					";
				print "
				</ul>
			</td>
			";
//			if ($jobs_tc == 'c')
//			{
				print "
				<td width=\"150\"></td>
				<td $at>
					<div style=\"border:1px grey dotted; padding:5px;\">
					Agent Initials:
					<table class=\"basic_table\">
					";
					foreach ($agent_initials as $ini => $name)
						print "<tr><td>$ini</td><td width=\"5\"></td><td>$name</td></tr>
							";
					print "
					</table>
					</div>
				</td>
				";
//			}
			print "
		</tr>
		</table>
		</div><!--div_guide-->
		";
	}
	
	print "
	<form name=\"form_client\" action=\"clients.php\" method=\"post\" target=\"_blank\" rel=\"noopener\">
		" . input_hidden('client2_id', '') . "
		" . input_hidden('sc_text', '') . "
		" . input_hidden('task', '') . "
	</form><!--form_client-->
	";
	
} # print_form()

function get_uploaded_file()
{
	global $csv_dir; # csvex in settings.php
	global $upload_max_size;
	global $upload_result;
	global $uploaded_file;
	
	$input_dir = check_dir("$csv_dir/bulk");
	$ts = date("Y_m_d" . "\\" . "_H_i_s"); // To timestamp the file name
	$rc = '';
	$uploaded_file = '';
	$upload_ok = false;
	
	if (isset($_FILES['uploaded']))
	{
		if ($_FILES['uploaded']['error'] == UPLOAD_ERR_OK)
		{
			if (0 < strlen(basename($_FILES['uploaded']['name'])))
			{
				if (strtolower(substr(basename($_FILES['uploaded']['name']), -4, 4)) == '.csv')
				{
					if ($_FILES['uploaded']['size'] < $upload_max_size)
					{
						$uploaded_file = xprint($_FILES['uploaded']['name'], false, 1);
						if (!$uploaded_file)
							dprint("Upload failed: basename(FILES[uploaded][name]) is empty");
					}
					else
						dprint("get_uploaded_file(): Upload failed: files size of " . xprint($_FILES['uploaded']['size'], false, 1) . " exceeds max of $upload_max_size");
				}
				else
					dprint("get_uploaded_file(): Upload failed: file name '" . basename(xprint($_FILES['uploaded']['name'], false, 1)) . "' does not end in '.csv'");
			}
			else
				dprint("get_uploaded_file(): Upload failed: strlen(basename(FILES[uploaded][name]))=" . strlen(basename(xprint($_FILES['uploaded']['name'], false, 1))));
		}
		else
			dprint("get_uploaded_file(): Upload failed: FILES[uploaded][error]=" . xprint($_FILES['uploaded']['error'], false, 1));
	}
	
	if ($uploaded_file)
	{
		#dprint("get_uploaded_file(): uploaded_file=$uploaded_file");#
		$dest = "$input_dir";
		if (is_dir($dest))
		{
			$ts_name = "/{$ts}_{$uploaded_file}";
			$src = xprint($_FILES['uploaded']['tmp_name'], false, 1);
			if (is_uploaded_file($src))
			{
				$dest .= $ts_name;
				# NOTE WELL: move_uploaded_file() requires domain's 'safe mode' to be turned off in
				# Plesk / setup / services / php support
				#dprint("get_uploaded_file(): calling move_uploaded_file($src, $dest)");#
				$rc = move_uploaded_file($src,$dest);
				if ($rc)
					$upload_ok = true;
				else
					dprint("get_uploaded_file(): Upload aborted: move_uploaded_file($src, $dest) returned ($rc)");
			}
			else
				dprint("get_uploaded_file(): Upload aborted: '$src' is NOT an uploaded file");

		}
		else
			dprint("get_uploaded_file(): Upload aborted: cannot find dir '$dest'");
	}

	if ($upload_ok)
		$upload_result = "File Uploaded OK";
	else
		$upload_result = "*** Error *** File failed to upload ***";
	$upload_result = "<h3><span style=\"color:blue;\">$upload_result</span></h3>";

	return ($rc ? $dest : '');
	
} # get_uploaded_file()

function import_csv($file)
{
	global $errors;
	
	#dprint("import_csv($file)");#

	$fhan = fopen($file, 'r');
	if (!$fhan)
	{
		$txt = "import_csv($file): fopen('r') failed";
		dprint($txt, true);
		$errors[] = array('LNO' => -1, 'LINE' => array(), 'ERROR' => $txt);
		return;
	}
	
	sql_encryption_preparation('JOB');
	sql_encryption_preparation('JOB_SUBJECT');
	
	$lix = 1;
	while (($line = fgetcsv($fhan)) !== false)
	{
		#dprint("line=" . print_r($line,1));#
		$rc = job_add($line, $lix);
		if ($rc)
			$errors[] = array('LNO' => $lix, 'LINE' => $line, 'ERROR' => $rc);
		$lix++;
	}
	#dprint("EOF");#

	#dprint("Errors=" . print_r($errors,1));#
	
	fclose($fhan);
	
} # import_csv()

function job_add($line, $lix)
{
	global $agent_initials;
	global $ascii_pound;
	global $c_code;
	global $client2_id;
	global $crlf;
	global $id_JOB_STATUS_act;
	global $id_LETTER_TYPE_letter_1;
	global $job_types_sel; # from init_data()
	global $jobs;
	global $jobs_tc;
	global $pound_194;
	global $sqlFalse;
	global $sqlTrue;
	global $USER;
	
	#dprint("job_add(line,$lix) jobs_tc=$jobs_tc");#
	
	$fix = 0; # field index
	$max = count($line) - 1; # max field index
	if ($max < 0)
		return '';#"Blank line";

	$empty = true;
	foreach ($line as $fld)
	{
		if ($fld !== '')
		{
			$empty = false;
			break;
		}
	}
	if ($empty)
		return '';#"Empty line";
	
	$js_title = get_field($line, $fix++, $max);
	$js_firstname = get_field($line, $fix++, $max);
	$js_lastname = get_field($line, $fix++, $max);
	$js_company = get_field($line, $fix++, $max);
	$js_addr_1 = get_field($line, $fix++, $max);
	$js_addr_2 = get_field($line, $fix++, $max);
	$js_addr_3 = get_field($line, $fix++, $max);
	$js_addr_4 = get_field($line, $fix++, $max);
	$js_addr_5 = '';
	$js_addr_pc = get_field($line, $fix++, $max);
	$js_outcode = postcode_outcode($js_addr_pc);
	$jp_phone = get_field($line, $fix++, $max);
	$jp_phone_descr = 'Land-line';
//	if ($jobs_tc == 't')
//	{
		$subject_mobile = get_field($line, $fix++, $max);
		$mobile_descr = 'Mobile';
//		$dob = 'NULL';
//	}
//	else
//	{
//		$dob_raw = get_field($line, $fix++, $max);
//		if ($dob_raw)
//		{
//			$dob = date_for_sql($dob_raw, false, false); # returns 'yyyy-mm-dd' or NULL
//			if ((!$dob) || ($dob == 'NULL'))
//				return "The Date of Birth \"$dob_raw\" is not a valid date";
//		}
//		else
//			$dob = 'NULL';
//	}
	$client_ref = get_field($line, $fix++, $max);
	$sql = "SELECT JOB_ID FROM JOB WHERE CLIENT2_ID=$client2_id AND (" . sql_decrypt('CLIENT_REF') . "=" . quote_smart($client_ref, true) . ")";
	#dprint($sql);#
	$dup_job = sql_select_single($sql);
	#dprint("dup_job = $dup_job");#
	if ($dup_job)
	{
		$sql = str_replace('SELECT JOB_ID', 'SELECT J_VILNO', $sql);
		$dup_vilno = sql_select_single($sql);
		return "The Client Ref $client_ref is already in the system for job $dup_vilno (ID $dup_job)";
	}
	
	$dob_raw = get_field($line, $fix++, $max);
	if ($dob_raw)
	{
		$dob = date_for_sql($dob_raw, false, false); # returns 'yyyy-mm-dd' or NULL
		if ((!$dob) || ($dob == 'NULL'))
			return "The Date of Birth \"$dob_raw\" is not a valid date";
	}
	else
		$dob = 'NULL';
	$email_addr = get_field($line, $fix++, $max);
//	if ($jobs_tc == 't')
//	{
//		$notes_1 = get_field($line, $fix++, $max);
//		$notes_2 = get_field($line, $fix++, $max);
//		$notes_3 = get_field($line, $fix++, $max);
//		$notes_4 = get_field($line, $fix++, $max);
//		$j_note = $notes_1;
//		if ($notes_2)
//			$j_note .= (($j_note ? $crlf : '') . $notes_2);
//		if ($notes_3)
//			$j_note .= (($j_note ? $crlf : '') . $notes_3);
//		if ($notes_4)
//			$j_note .= (($j_note ? $crlf : '') . $notes_4);
//	}
//	else
//	{
//		$raw_date = get_field($line, $fix++, $max); # REVIEW: is "End Date" the next review date?
//		# If date contains '-' assume it is already SQL format yyyy-mm-dd
//		if (strpos($raw_date, '-') !== false)
//		{
//			$bits = explode('-', $raw_date);
//			if (count($bits) != 3)
//				return "The End Date \"$raw_date\" needs to have 3 parts";
//			$jc_review_dt = $raw_date;
//		}
//		else # date doesn't contain '-'
//			$jc_review_dt = date_for_sql($raw_date, false, false);
//		if ((!$jc_review_dt) || ($jc_review_dt == 'NULL'))
//			return "The End Date \"$raw_date\" is invalid";
//		$ep = date_to_epoch($jc_review_dt);
//		if ($ep == '')
//			return "The End Date \"$raw_date\" is not a real date";
//		$jc_review_dt = date_from_epoch(false, $ep, false, false, true);
		
		$raw_amt = get_field($line, $fix++, $max);
		$jc_total_amt = trim(str_replace($pound_194, '', str_replace(chr($ascii_pound), '', str_replace('£', '', str_replace(',', '', $raw_amt)))));
		#dprint("total_amt=\"$jc_total_amt\"");#
		$jc_total_amt = floatval($jc_total_amt);
		if (($jobs_tc == 'c') && ($jc_total_amt == 0.0))
			return "The Balance Outstanding \"$raw_amt\" is zero (or equivalent)";
		if ($jc_total_amt < 0.0)
			return "The Balance Outstanding \"$raw_amt\" is negative";
//	}
	
	$other_phones = explode(',', get_field($line, $fix++, $max));
	
	$csv_c_code = get_field($line, $fix++, $max);
	if ($csv_c_code != $c_code)
		return "The Client Code \"$csv_c_code\" should be \"$c_code\"";

	$j_user_id = 'NULL';
//	$jt_amount = 'NULL';
//	if ($jobs_tc == 'c')
//	{
		$u_initials = get_field($line, $fix++, $max);
		if ($u_initials && (strtolower($u_initials) != "collect"))
		{
			if (!array_key_exists(strtoupper($u_initials), $agent_initials))
				return "The agent initials \"$u_initials\" are not recognised";
			$j_user_id = get_agent_id($u_initials);
			if (!$j_user_id)
				return "No agent was found with initials \"$u_initials\"";
		}

		$jc_reason_2 = '';
		while ($fix <= 1000) # don't allow infinite looping
		{
			$temp = get_field($line, $fix++, $max);
			if ($temp !== '')
				$jc_reason_2 .= ($temp . $crlf);
			else
				break;
		}
//	}
//	else
//	{
//		$raw_amt = get_field($line, $fix++, $max);
//		if ($raw_amt !== '')
//			$jt_amount = 1.0 * trim(str_replace('£', '', str_replace(',', '', $raw_amt)));
//	}
	
//	dprint("Found<br>
//			Title=\"$js_title\"<br>
//			Firstname=\"$js_firstname\"<br>
//			Lastname=\"$js_lastname\"<br>
//			Company=\"$js_company\"<br>
//			Addr 1=\"$js_addr_1\"<br>
//			Addr 2=\"$js_addr_2\"<br>
//			Addr 3=\"$js_addr_3\"<br>
//			Addr 4=\"$js_addr_4\"<br>
//			Postcode=\"$js_addr_pc\"<br>
//			Phone=\"$jp_phone\"<br>
//			" . (($jobs_tc == 't') ? "Mobile=\"$subject_mobile\"<br>" : '') . "
//			Client Ref=\"$client_ref\"<br>
//			" . (($jobs_tc == 't') ? "Notes 1=\"$notes_1\"<br>
//										Notes 2=\"$notes_2\"<br>
//										Notes 3=\"$notes_3\"<br>
//										Notes 4=\"$notes_4\"<br>"
//									: 
//									"End Date=\"$jc_review_dt\"<br>
//										Balance=\"$jc_total_amt\"<br>"
//									) . "
//			CCode=\"$csv_c_code\"<br>
//			" . (($jobs_tc == 't') ? ''
//									: 
//									"Initials=\"$u_initials (ID $j_user_id)\"<br>
//										Reason=\"$jc_reason_2\"<br>"
//									) . "
//			");#

	sql_encryption_preparation('JOB');
	sql_encryption_preparation('JOB_SUBJECT');
	sql_encryption_preparation('JOB_PHONE');
	sql_encryption_preparation('JOB_NOTE');
	
	$client_ref_sql = sql_encrypt($client_ref, false, 'JOB');
	if ($jobs_tc == 't')
	{
		$j_s_invs = (intval(sql_select_single("SELECT S_INVS_TRACE FROM CLIENT2 WHERE CLIENT2_ID=$client2_id")) ? $sqlTrue : $sqlFalse);
		$tr_fee = floatval(sql_select_single("SELECT TR_FEE FROM CLIENT2 WHERE CLIENT2_ID=$client2_id"));
		$nt_fee = floatval(sql_select_single("SELECT NT_FEE FROM CLIENT2 WHERE CLIENT2_ID=$client2_id"));
		$jt_job_type_id = post_val('cu_job_type_id', true);
		$job_type = $job_types_sel[$jt_job_type_id];
		$jc_review_dt_sql = 'NULL';
		$jc_reason_sql = sql_encrypt($jc_reason_2, false, 'JOB');
		$comm_percent = 0.0;
	}
	else
	{
		$j_s_invs = $sqlTrue;
		$tr_fee = 'NULL';
		$nt_fee = 'NULL';
		$jt_job_type_id = 'NULL';
		$job_type = 'NULL';
		//$jc_review_dt_sql = "'{$jc_review_dt}'";
		$jc_review_dt_sql = 'NULL';
		$jc_reason_sql = sql_encrypt($jc_reason_2, false, 'JOB');
		$comm_percent = floatval(sql_select_single("SELECT COMM_PERCENT FROM CLIENT2 WHERE CLIENT2_ID=$client2_id"));
	}
	
	$j_diary_dt = "'" . date_from_epoch(true, time() + (24 * 60 * 60), false, false, true) . "'";
	$j_diary_txt = sql_encrypt('Review', false, 'JOB');
	
	$now = date_now_sql();
	$now_sql = "'" . date_now_sql() . "'";

	# --- INSERT JOB ---
	
	$j_vilno = vilno_next();
	$j_sequence = sequence_next();
	
	$fields = "CLIENT2_ID,  CLIENT_REF,      J_AVAILABLE, J_VILNO,  J_SEQUENCE,  J_USER_ID,  J_USER_DT, J_OPENED_DT, J_S_INVS,  J_BULK,   IMPORTED,  JC_REASON_2,    ";
	$values = "$client2_id, $client_ref_sql, $sqlTrue,    $j_vilno, $j_sequence, $j_user_id, $now_sql,  $now_sql,    $j_s_invs, $sqlTrue, $sqlFalse, $jc_reason_sql, ";
	
	if ($jobs_tc == 't')
	{
		$fields .= "JT_JOB,   JC_JOB,    JT_JOB_TYPE_ID,  JT_AMOUNT,     JT_FEE_Y, JT_FEE_N ";
		$values .= "$sqlTrue, $sqlFalse, $jt_job_type_id, $jc_total_amt, $tr_fee,  $nt_fee  ";
	}
	else
	{
		$fields .= "JT_JOB,    JC_JOB,   JC_REVIEW_DT,      JC_TOTAL_AMT,  J_DIARY_DT,  J_DIARY_TXT,  JC_JOB_STATUS_ID,   ";
		$values .= "$sqlFalse, $sqlTrue, $jc_review_dt_sql, $jc_total_amt, $j_diary_dt, $j_diary_txt, $id_JOB_STATUS_act, ";

		$fields .= "JC_LETTER_MORE, JC_LETTER_TYPE_ID,        JC_PERCENT";
		$values .= "$sqlTrue,       $id_LETTER_TYPE_letter_1, $comm_percent";
	}
	
	$sql = "INSERT INTO JOB ($fields) VALUES ($values)";
	dprint("job_add(): $sql");#
	audit_setup_gen('JOB', 'JOB_ID', 0, '', '');
	$job_id = sql_execute($sql, true); # audited
	dprint("job_add(): \$job_id=$job_id");#

	# --- INSERT SUBJECT ---
	
	$js_title_sql = quote_smart($js_title);
	$js_firstname_sql = sql_encrypt($js_firstname, false, 'JOB_SUBJECT');
	$js_lastname_sql = sql_encrypt($js_lastname, false, 'JOB_SUBJECT');
	$js_company_sql = sql_encrypt($js_company, false, 'JOB_SUBJECT');
	$js_addr_1_sql = sql_encrypt($js_addr_1, false, 'JOB_SUBJECT');
	$js_addr_2_sql = sql_encrypt($js_addr_2, false, 'JOB_SUBJECT');
	$js_addr_3_sql = sql_encrypt($js_addr_3, false, 'JOB_SUBJECT');
	$js_addr_4_sql = sql_encrypt($js_addr_4, false, 'JOB_SUBJECT');
	$js_addr_5_sql = sql_encrypt($js_addr_5, false, 'JOB_SUBJECT');
	$js_addr_pc_sql = sql_encrypt($js_addr_pc, false, 'JOB_SUBJECT');
	$js_outcode_sql = ($js_outcode ? "'$js_outcode'" : 'NULL');
	
	$fields = "JOB_ID,  JS_PRIMARY,  JS_TITLE,      JS_FIRSTNAME,      JS_LASTNAME,      JS_COMPANY,      JS_DOB, ";
	$values = "$job_id, $sqlTrue,    $js_title_sql, $js_firstname_sql, $js_lastname_sql, $js_company_sql, $dob,   ";
	
	$fields .= "JS_ADDR_1,      JS_ADDR_2,      JS_ADDR_3,      JS_ADDR_4,      JS_ADDR_5,      JS_ADDR_PC,      JS_OUTCODE,      IMPORTED";
	$values .= "$js_addr_1_sql, $js_addr_2_sql, $js_addr_3_sql, $js_addr_4_sql, $js_addr_5_sql, $js_addr_pc_sql, $js_outcode_sql, $sqlFalse";
	
	$sql = "INSERT INTO JOB_SUBJECT ($fields) VALUES ($values)";
	dprint("job_add(): $sql");#
	audit_setup_job($job_id, 'JOB_SUBJECT', 'JOB_SUBJECT_ID', 0, '', '');
	$job_subject_id = sql_execute($sql, true); # audited
	dprint("job_add(): \$job_subject_id=$job_subject_id");#
	
	# --- INSERT PHONE ---
	
	if ($jp_phone)
	{
		$jp_phone_sql = sql_encrypt($jp_phone, false, 'JOB_PHONE');
		$descr_sql = sql_encrypt($jp_phone_descr, false, 'JOB_PHONE');

		$fields = "JOB_ID,  JOB_SUBJECT_ID,  JP_PHONE,      JP_PRIMARY_P, JP_DESCR,   IMPORTED,  IMP_PH";
		$values = "$job_id, $job_subject_id, $jp_phone_sql, $sqlTrue,     $descr_sql, $sqlFalse, $sqlFalse";

		$sql = "INSERT INTO JOB_PHONE ($fields) VALUES ($values)";
		dprint("job_add(): $sql");#
		audit_setup_job($job_id, 'JOB_PHONE', 'JOB_PHONE_ID', 0, '', '');
		$job_phone_id = sql_execute($sql, true); # audited
		dprint("job_add(): \$job_phone_id/1=$job_phone_id");#
	}
	
//	if ($jobs_tc == 't')
//	{
	if ($subject_mobile)
	{
		$jp_phone_sql = sql_encrypt($subject_mobile, false, 'JOB_PHONE');
		$descr_sql = sql_encrypt($mobile_descr, false, 'JOB_PHONE');

		$fields = "JOB_ID,  JOB_SUBJECT_ID,  JP_PHONE,      JP_PRIMARY_P, JP_DESCR,   IMPORTED,  IMP_PH";
		$values = "$job_id, $job_subject_id, $jp_phone_sql, $sqlFalse,    $descr_sql, $sqlFalse, $sqlFalse";

		$sql = "INSERT INTO JOB_PHONE ($fields) VALUES ($values)";
		dprint("job_add(): $sql");#
		audit_setup_job($job_id, 'JOB_PHONE', 'JOB_PHONE_ID', 0, '', '');
		$job_phone_id = sql_execute($sql, true); # audited
		dprint("job_add(): \$job_phone_id/2=$job_phone_id");#
	}
//	}
	
	if ($email_addr)
	{
		$jp_email_sql = sql_encrypt($email_addr, false, 'JOB_PHONE');

		$fields = "JOB_ID,  JOB_SUBJECT_ID,  JP_EMAIL,      JP_PRIMARY_E, IMPORTED,  IMP_PH";
		$values = "$job_id, $job_subject_id, $jp_email_sql, $sqlTrue,     $sqlFalse, $sqlFalse";

		$sql = "INSERT INTO JOB_PHONE ($fields) VALUES ($values)";
		dprint("job_add(): $sql");#
		audit_setup_job($job_id, 'JOB_PHONE', 'JOB_PHONE_ID', 0, '', '');
		$job_phone_id = sql_execute($sql, true); # audited
		dprint("job_add(): \$job_phone_id/email=$job_phone_id");#
	}
	
	$other_phones_txt = array();
	if ($other_phones)
	{
		$ii = 3;
		foreach ($other_phones as $ophone)
		{
			$ophone = trim($ophone);
			if ($ophone)
			{
				$jp_phone_sql = sql_encrypt($ophone, false, 'JOB_PHONE');

				$fields = "JOB_ID,  JOB_SUBJECT_ID,  JP_PHONE,      JP_PRIMARY_P, IMPORTED,  IMP_PH";
				$values = "$job_id, $job_subject_id, $jp_phone_sql, $sqlFalse,    $sqlFalse, $sqlFalse";

				$sql = "INSERT INTO JOB_PHONE ($fields) VALUES ($values)";
				dprint("job_add(): $sql");#
				audit_setup_job($job_id, 'JOB_PHONE', 'JOB_PHONE_ID', 0, '', '');
				$job_phone_id = sql_execute($sql, true); # audited
				dprint("job_add(): \$job_phone_id/{$ii}=$job_phone_id");#
				
				$ii++;
				$other_phones_txt[] = $ophone;
			}
		}
	}
	$other_phones_txt = implode(', ', $other_phones_txt);
	
//	# --- INSERT NOTES ---
	
	$j_note_sql = sql_encrypt('Imported from CSV', false, 'JOB_NOTE');
	$fields = "JOB_ID,  J_NOTE,      JN_ADDED_ID,        JN_ADDED_DT, IMPORTED";
	$values = "$job_id, $j_note_sql, {$USER['USER_ID']}, $now_sql,    $sqlFalse";
	$sql = "INSERT INTO JOB_NOTE ($fields) VALUES ($values)";
	dprint("job_add(): $sql");#
	audit_setup_job($job_id, 'JOB_NOTE', 'JOB_NOTE_ID', 0, '', '');
	$job_note_id = sql_execute($sql, true); # audited
	dprint("job_add(): \$job_note_id=$job_note_id");#
	
//	if ($jobs_tc == 't')
//	{
//		$j_note_sql = sql_encrypt($j_note, false, 'JOB_NOTE');
//		
//		$fields = "JOB_ID,  J_NOTE,      JN_ADDED_ID,        JN_ADDED_DT, IMPORTED";
//		$values = "$job_id, $j_note_sql, {$USER['USER_ID']}, $now_sql,    $sqlFalse";
//
//		$sql = "INSERT INTO JOB_NOTE ($fields) VALUES ($values)";
//		dprint("job_add(): $sql");#
//		audit_setup_job($job_id, 'JOB_NOTE', 'JOB_NOTE_ID', 0, '', '');
//		$job_note_id = sql_execute($sql, true); # audited
//		dprint("job_add(): \$job_note_id=$job_note_id");#
//	}
	
	# --- UPDATE JOBS LIST ---
	
	$agent = ($j_user_id ? (strtoupper($u_initials) . " (ID $j_user_id)") : '');
	$jobs[] = array('LNO' => $lix, 'VILNO' => $j_vilno, 'SEQUENCE' => $j_sequence, 'JOB_ID' => $job_id, 'JOB_TYPE' => $job_type,
					'CLIENT' => "$c_code (ID $client2_id)", 'CLIENT_REF' => $client_ref, 'USER' => $agent, 
					'PLACEMENT' => $now,
					'OUTSTANDING' => $jc_total_amt, 'REASON' => $jc_reason_2,
					'TITLE' => $js_title, 'FIRSTNAME' => $js_firstname, 'LASTNAME' => $js_lastname, 'COMPANY' => $js_company, 'DOB' => $dob,
					'ADDR_1' => $js_addr_1, 'ADDR_2' => $js_addr_2, 'ADDR_3' => $js_addr_3, 'ADDR_4' => $js_addr_4, 'ADDR_PC' => $js_addr_pc, 
					'PHONE' => $jp_phone, 'MOBILE' => $subject_mobile, 'EMAIL' => $email_addr, 'OTHER_PHONES' => $other_phones_txt
					);
//	if ($jobs_tc == 't')
//	{
//		$jobs[] = array('LNO' => $lix, 'VILNO' => $j_vilno, 'SEQUENCE' => $j_sequence, 'JOB_ID' => $job_id, 'JOB_TYPE' => $job_type,
//						'CLIENT' => "$c_code (ID $client2_id)", 'CLIENT_REF' => $client_ref, 'USER' => '', 
//						'PLACEMENT' => $now,
//						'END_DATE' => '', 'OUTSTANDING' => '', 'REASON' => '',
//						'TITLE' => $js_title, 'FIRSTNAME' => $js_firstname, 'LASTNAME' => $js_lastname, 'COMPANY' => $js_company,
//						'ADDR_1' => $js_addr_1, 'ADDR_2' => $js_addr_2, 'ADDR_3' => $js_addr_3, 'ADDR_4' => $js_addr_4, 'ADDR_PC' => $js_addr_pc, 
//						'PHONE' => $jp_phone, 'MOBILE' => $subject_mobile, 'NOTES' => $j_note
//						);
//	}
//	else
//	{
//		$agent = strtoupper($u_initials) . " (ID $j_user_id)";
//		$jobs[] = array('LNO' => $lix, 'VILNO' => $j_vilno, 'SEQUENCE' => $j_sequence, 'JOB_ID' => $job_id, 'JOB_TYPE' => $job_type,
//						'CLIENT' => "$c_code (ID $client2_id)", 'CLIENT_REF' => $client_ref, 'USER' => $agent, 
//						'PLACEMENT' => $now, 
//						'END_DATE' => $jc_review_dt, 'OUTSTANDING' => $jc_total_amt, 'REASON' => $jc_reason_2,
//						'TITLE' => $js_title, 'FIRSTNAME' => $js_firstname, 'LASTNAME' => $js_lastname, 'COMPANY' => $js_company,
//						'ADDR_1' => $js_addr_1, 'ADDR_2' => $js_addr_2, 'ADDR_3' => $js_addr_3, 'ADDR_4' => $js_addr_4, 'ADDR_PC' => $js_addr_pc, 
//						'PHONE' => $jp_phone, 'MOBILE' => '', 'NOTES' => ''
//						);
//	}
	
	return ''; # success
	
} # job_add()

function print_added_jobs()
{
	global $ar;
	global $jobs;
	
	if ($jobs)
	{
		print "
		<h3 style=\"color:blue;\">The following jobs have been added to the database:</h3>
		<table class=\"spaced_table\">
		<tr>
			<th>CSV Line</th><th>VILNo</th><th>Sequence</th><th>JOB_ID</th><th>Client</th><th>Client Ref</th><th>Agent</th>
			<th>Job Type</th><th>Received</th><th>Outstanding</th><th>Reason</th>
			<th>Title</th><th>Firstname</th><th>Surname</th><th>Addr 1</th><th>Addr 2</th><th>Addr 3</th><th>Addr 4</th><th>Addr PC</th>
			<th>Phone</th><th>Mobile</th><th>Email</th><th>Other phones</th>
		</tr>
		";
//			<th>CSV Line</th><th>VILNo</th><th>Sequence</th><th>JOB_ID</th><th>Client</th><th>Client Ref</th><th>Agent</th>
//			<th>Job Type</th><th>Received</th><th>End Date</th><th>Outstanding</th><th>Reason</th>
//			<th>Title</th><th>Firstname</th><th>Surname</th><th>Addr 1</th><th>Addr 2</th><th>Addr 3</th><th>Addr 4</th><th>Addr PC</th>
//			<th>Phone</th><th>Mobile</th><th>Notes</th>
		foreach ($jobs as $one)
		{
			print "
			<tr>
				<td $ar>{$one['LNO']}</td>
				<td $ar>{$one['VILNO']}</td>
				<td $ar>{$one['SEQUENCE']}</td>
				<td $ar>{$one['JOB_ID']}</td>
				<td>{$one['CLIENT']}</td>
				<td>{$one['CLIENT_REF']}</td>
				<td>{$one['USER']}</td>
				<td>{$one['JOB_TYPE']}</td>
				<td>{$one['PLACEMENT']}</td>
				<td $ar>{$one['OUTSTANDING']}</td>
				<td>{$one['REASON']}</td>
				<td>{$one['TITLE']}</td>
				<td>{$one['FIRSTNAME']}</td>
				<td>{$one['LASTNAME']}</td>
				<td>{$one['ADDR_1']}</td>
				<td>{$one['ADDR_2']}</td>
				<td>{$one['ADDR_3']}</td>
				<td>{$one['ADDR_4']}</td>
				<td>{$one['ADDR_PC']}</td>
				<td>{$one['PHONE']}</td>
				<td>{$one['MOBILE']}</td>
				<td>{$one['EMAIL']}</td>
				<td>{$one['OTHER_PHONES']}</td>
			</tr>
			";
//			print "
//			<tr>
//				<td $ar>{$one['LNO']}</td>
//				<td $ar>{$one['VILNO']}</td>
//				<td $ar>{$one['SEQUENCE']}</td>
//				<td $ar>{$one['JOB_ID']}</td>
//				<td>{$one['CLIENT']}</td>
//				<td>{$one['CLIENT_REF']}</td>
//				<td>{$one['USER']}</td>
//				<td>{$one['JOB_TYPE']}</td>
//				<td>{$one['PLACEMENT']}</td>
//				<td>{$one['END_DATE']}</td>
//				<td $ar>{$one['OUTSTANDING']}</td>
//				<td>{$one['REASON']}</td>
//				<td>{$one['TITLE']}</td>
//				<td>{$one['FIRSTNAME']}</td>
//				<td>{$one['LASTNAME']}</td>
//				<td>{$one['ADDR_1']}</td>
//				<td>{$one['ADDR_2']}</td>
//				<td>{$one['ADDR_3']}</td>
//				<td>{$one['ADDR_4']}</td>
//				<td>{$one['ADDR_PC']}</td>
//				<td>{$one['PHONE']}</td>
//				<td>{$one['MOBILE']}</td>
//				<td>{$one['NOTES']}</td>
//			</tr>
//			";
		}
		print "
		</table>
		<hr>
		";
	}
	else
		print "<h4 style=\"color:red;\">No jobs were added to the database</h4>";
	print "
	<br>
	<br>
	";
} # print_added_jobs()

function get_agent_id($initials)
{
	$sql = "SELECT USER_ID " . agent_sql() . " AND (U.U_INITIALS=" . quote_smart(strtoupper($initials)) . ")";
	#dprint($sql);#
	sql_execute($sql);
	$user_id = 0;
	while (($newArray = sql_fetch()) != false)
		$user_id = $newArray[0];
	return $user_id;
	
} # get_agent_id()

function get_field($line, $fix, $max)
{
	if ($fix <= $max)
		return trim($line[$fix]);
	return '';
} # get_field()

?>
