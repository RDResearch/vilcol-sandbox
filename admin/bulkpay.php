<?php

include_once("settings.php");
include_once("library.php");
global $denial_message;
global $navi_1_finance;
global $navi_2_fin_bulkpay;
global $role_man;
global $USER; # set by admin_verify()

log_open("vilcol.log");

sql_connect();

admin_verify(); # writes to $USER

if ($USER['IS_ENABLED'])
{
	if (role_check('*', $role_man))
	{
		$navi_1_finance = true; # settings.php; used by navi_1_heading()
		$navi_2_fin_bulkpay = true; # settings.php; used by navi_2_heading()
		$onload = "onload=\"set_scroll();\"";
		$page_title_2 = 'Bulk Pay - Vilcol';
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
	global $errors;
	#global $method_codes;
	global $page_title_2;
	global $payments;
	#global $route_codes;

	print "<h3>Finance</h3>";
	navi_2_heading(); # secondary navigation buttons
	print "<h3>Bulk Payments from Subjects (Collection Jobs)</h3>";

	dprint(post_values());
	dprint("FILES=" . xprint(print_r($_FILES,1), false, 1));
	set_time_limit(60 * 60 * 1); # 1 hour

	get_route_codes();
	get_method_codes();

	if (isset($_POST['upload_marker']))
	{
		$file = get_uploaded_file();
		if ($file)
		{
			$payments = array();
			$errors = array();
			import_csv($file);
			if ($errors)
				print_errors();
			print_payments();
		}
	}

	javascript();

	print_form();

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

function get_route_codes()
{
	global $route_codes;
	global $sqlFalse;

	$sql = "SELECT PR_CODE FROM PAYMENT_ROUTE_SD WHERE OBSOLETE=$sqlFalse ORDER BY SORT_ORDER";
	sql_execute($sql);
	$route_codes = array();
	while (($newArray = sql_fetch()) != false)
		$route_codes[] = strtoupper($newArray[0]);
} # get_route_codes()

function get_method_codes()
{
	global $method_codes;
	global $sqlFalse;

	$sql = "SELECT TDX_CODE FROM PAYMENT_METHOD_SD WHERE OBSOLETE=$sqlFalse ORDER BY SORT_ORDER";
	sql_execute($sql);
	$method_codes = array();
	while (($newArray = sql_fetch()) != false)
	{
		$code = strtoupper($newArray[0]);
		if (!in_array($code, $method_codes))
			$method_codes[] = $code;

		#$count = count($method_codes);
		#if ($count < 6)
		#	$method_codes[] = $newArray[0];
		#elseif ($count == 6)
		#	$method_codes[] = "etc...";
	}
} # get_method_codes()

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

	function start_upload()
	{
		bits = document.csvupload.uploaded.value.split('.');
		if (bits[bits.length-1].toLowerCase() == 'csv')
		{
			if (document.csvupload.uploaded.value.length > 0)
			{
				document.getElementById('span_result').innerHTML = '';
				please_wait_on_submit();
				document.csvupload.submit();
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
	#global $sqlFalse;
	global $method_codes;
	global $route_codes;
	global $upload_result;

	print "
	<div id=\"div_guide\" style=\"width:600px;\">
	You can upload a CSV file of payments by subjects.<br>
	These will then be automatically added to the respective collection jobs.<br>
	The columns of the CSV file should be:
	<ul style=\"margin-top:0px;\">
		<li>VIL Number</li>
		<li>Amount Paid</li>
		<li>Date Paid</li>
		<li>Payment Route (" . implode(',', $route_codes) . ")</li>
		<li>Payment Method (" . implode(',', $method_codes) . ")</li>
	</ul>
	</div><!--div_guide-->
	";

	print "
	<div id=\"div_upload\" style=\"width:600px; border:1px solid black; padding-left:10px;\">
	<span id=\"span_result\">$upload_result</span>
	<p>Please browse your system to choose a \".csv\" document to upload.</p>
	<form id=\"csvupload\" method=\"post\" name=\"csvupload\" class=\"upload\" action=\"\" enctype=\"multipart/form-data\">
		<input name=\"uploaded\" type=\"file\" size=\"50\" />
		<input type=\"button\" value=\"Upload\" onclick=\"start_upload();\"/>
		<br>
		<p>The file must be comma-delimited, have a \".csv\" extension and its size is limited to 614,400 MB.<br>
				<span style=\"color:red;\">...and it should NOT have a header line in it containing column/field names</span></p>
		<input type=\"hidden\" name=\"upload_marker\" value=\"uploading\" />
	</form>
	</div><!--div_upload-->
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
		dprint("get_uploaded_file(): uploaded_file=$uploaded_file");
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
				dprint("get_uploaded_file(): calling move_uploaded_file($src, $dest)");
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

	dprint("import_csv($file)");

	$fhan = fopen($file, 'r');
	if (!$fhan)
	{
		$txt = "import_csv($file): fopen('r') failed";
		dprint($txt, true);
		$errors[] = array('LNO' => -1, 'LINE' => array(), 'ERROR' => $txt);
		return;
	}

	$lix = 1;
	while (($line = fgetcsv($fhan)) !== false)
	{
		dprint("line=" . print_r($line,1));
		$rc = payment_add($line, $lix);
		if ($rc)
			$errors[] = array('LNO' => $lix, 'LINE' => $line, 'ERROR' => $rc);
		$lix++;
	}
	dprint("EOF");

	dprint("Errors=" . print_r($errors,1));

	fclose($fhan);

} # import_csv()

function payment_add($line, $lix)
{
	global $id_ACTIVITY_par;
	global $method_codes;
	global $payments;
	global $route_codes;
	global $sqlFalse;
	global $sqlTrue;

	$fix = 0; # field index

	# Get newest collection job that has the given VILNo
	$vilno = intval(trim($line[$fix]));
	list($ms_top, $my_limit) = sql_top_limit(1);
	$sql = "SELECT $ms_top JOB_ID, JOB_CLOSED FROM JOB WHERE (J_VILNO=$vilno) AND (JC_JOB=$sqlTrue) AND (OBSOLETE=$sqlFalse) ORDER BY JOB_ID DESC $my_limit";
	sql_execute($sql);
	$job_id = 0;
	#$job_closed = 0; # i.e. false
	while (($newArray = sql_fetch()) != false)
	{
		$job_id = $newArray[0];
		#$job_closed = 1 * $newArray[1]; # boolean
	}
	if (!$job_id)
		return "No collection job with VILNo \"{$line[$fix]}\" was found in the database";
	#if ($job_closed)
	#	return "Collection job is CLOSED (VILNo \"{$line[$fix]}\")";
	$fix++;

	# Get Amount paid
	$adjustment = false;
	$amount = floatval(trim(str_replace('£', '', str_replace(',', '', $line[$fix]))));
	if ($amount == 0.0)
		return "The amount \"{$line[$fix]}\" is zero (or equivalent)";
	if ($amount < 0.0)
	{
		$temp_payment_route = strtoupper($line[$fix+2]);
		if ($temp_payment_route == 'AD')
			$adjustment = true;
		if (!$adjustment)
			return "The amount \"{$line[$fix]}\" is negative (payment route \"$temp_payment_route\")";
	}
	$fix++;

	# Get date paid
	$date = $line[$fix];
	# If $date contains '-' assume it is already SQL format yyyy-mm-dd
	if (strpos($date, '-') !== false)
	{
		$bits = explode('-', $date);
		if (count($bits) != 3)
			return "The date \"{$line[$fix]}\" needs to have 3 parts";
	}
	else
		# $date doesn't contain '-'
		$date = date_for_sql($date, false, false);
	if ((!$date) || ($date == 'NULL'))
		return "The date \"{$line[$fix]}\" is invalid";
	$ep = date_to_epoch($date);
	if ($ep == '')
		return "The date \"{$line[$fix]}\" is not a real date";
	$date = date_from_epoch(false, $ep, false, false, true);
	$fix++;

	# Get payment route
	$route = strtoupper($line[$fix]);
	if (!in_array($route, $route_codes))
		return "The payment route \"{$line[$fix]}\" is not recognised";
	if ($route == 'AD')
		$adjustment = true;
	$sql = "SELECT PAYMENT_ROUTE_ID FROM PAYMENT_ROUTE_SD WHERE (PR_CODE=" . quote_smart($route) . ") AND (OBSOLETE=$sqlFalse)";
	sql_execute($sql);
	$payment_route_id = 0;
	while (($newArray = sql_fetch()) != false)
		$payment_route_id = $newArray[0];
	if (!$payment_route_id)
		return "The payment route \"{$line[$fix]}\" was not found in the database";
	$fix++;

	# Get payment method
	$method = strtoupper($line[$fix]);
	if (!in_array($method, $method_codes))
		return "The payment method \"{$line[$fix]}\" is not recognised";
	$sql = "SELECT PAYMENT_METHOD_ID FROM PAYMENT_METHOD_SD WHERE (TDX_CODE=" . quote_smart($method) . ") AND (OBSOLETE=$sqlFalse)";
	sql_execute($sql);
	$payment_method_id = 0;
	while (($newArray = sql_fetch()) != false)
		$payment_method_id = $newArray[0];
	if (!$payment_method_id)
		return "The payment method \"{$line[$fix]}\" was not found in the database";
	$fix++;

	dprint("Found JOB_ID $job_id, Amount $amount, Date $date, Route $payment_route_id($route), Method $payment_method_id($method)");

	# Add payment to job
	if ($adjustment)
		$col_percent = 0.0;
	else
	{
		$sql = "SELECT JC_PERCENT FROM JOB WHERE JOB_ID=$job_id";
		$col_percent = 0.0;
		sql_execute($sql);
		while (($newArray = sql_fetch()) != false)
			$col_percent = floatval($newArray[0]);
	}

	$fields = "JOB_ID,  COL_AMT_RX, COL_DT_RX, COL_PAYMENT_ROUTE_ID, COL_PAYMENT_METHOD_ID, COL_PERCENT";
	$values = "$job_id, $amount,    '$date',   $payment_route_id,    $payment_method_id,    $col_percent";

	$sql = "INSERT INTO JOB_PAYMENT ($fields) VALUES ($values)";
	dprint("payment_add(): $sql");#
	audit_setup_job($job_id, 'JOB_PAYMENT', 'JOB_PAYMENT_ID', 0, '', '');
	$job_payment_id = sql_execute($sql, true); # audited
	dprint("payment_add(): \$job_payment_id=$job_payment_id");#

	sql_add_activity($job_id, 1, $id_ACTIVITY_par, false); # don't call sql_update_job()

	sql_update_paid_so_far($job_id, false); # don't call sql_update_job()

	# Feedback #142 25/09/18 - set diary date to one month from today
	sql_encryption_preparation('JOB');
	$j_diary_dt_raw = date_from_epoch(true, time() + (30 * 24 * 60 * 60), false, false, true); # One month from today
	$j_diary_dt = "'{$j_diary_dt_raw}'";
	$sql = "UPDATE JOB SET J_DIARY_DT=$j_diary_dt WHERE JOB_ID=$job_id";
	audit_setup_job($job_id, 'JOB', 'JOB_ID', $job_id, 'J_DIARY_DT', $j_diary_dt_raw);
	sql_execute($sql, true); # audited

	sql_update_job($job_id);

	$payments[] = array('LNO' => $lix, 'VILNO' => $vilno, 'JOB_ID' => $job_id, 'AMOUNT' => $amount, 'DATE' => $date,
						'ROUTE' => "$route (ID $payment_route_id)", 'METHOD' => "$method (ID $payment_method_id)",
						'JOB_PAYMENT_ID' => $job_payment_id);
	return ''; # success

} # payment_add()

function print_payments()
{
	global $ar;
	global $payments;

	print "
	<h3 style=\"color:blue;\">The following payments have been added to the database:</h3>
	<table class=\"spaced_table\">
	<tr>
		<th>CSV Line</th><th>VILNo</th><th>Amount</th><th>Date</th><th>Route</th><th>Method</th><th>Job ID</th><th>Job Payment ID</th>
	</tr>
	";
	foreach ($payments as $one)
	{
		print "
		<tr>
			<td $ar>{$one['LNO']}</td>
			<td $ar>{$one['VILNO']}</td>
			<td $ar>{$one['AMOUNT']}</td>
			<td $ar>{$one['DATE']}</td>
			<td>{$one['ROUTE']}</td>
			<td>{$one['METHOD']}</td>
			<td $ar>{$one['JOB_ID']}</td>
			<td $ar>{$one['JOB_PAYMENT_ID']}</td>
		</tr>
		";
	}
	print "
	</table>
	<hr>
	<br>
	<br>
	";
} # print_payments()

?>
