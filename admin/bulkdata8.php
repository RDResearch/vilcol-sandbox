<?php

include_once("settings.php");
include_once("library.php");
global $denial_message;
global $navi_1_jobs;
global $role_man;
global $USER; # set by admin_verify()

log_open("vilcol.log");

sql_connect();

admin_verify(); # writes to $USER

if ($USER['IS_ENABLED'])
{
	$navi_1_jobs = true; # settings.php; used by navi_1_heading()
	$onload = "onload=\"set_scroll();\"";
	$page_title_2 = 'Data 8 Import - Vilcol';
	screen_layout();
}
else
	print "<p>" . server_php_self() . ": login is not enabled</p>";

sql_disconnect();
log_close();

function screen_content()
{
	global $errors;
	global $phones;
	global $page_title_2;

	print "<h3>Bulk Import of Telephone Numbers from Data 8 CSV</h3>";

	dprint(post_values());
	dprint("FILES=" . xprint(print_r($_FILES,1), false, 1));
	set_time_limit(60 * 60 * 1); # 1 hour

	if (isset($_POST['upload_marker']))
	{
		$file = get_uploaded_file();
		if ($file)
		{
			$phones = array();
			$errors = array();
			import_csv($file);
			if ($errors)
				print_errors();
			print_phones();
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
	global $upload_result;

	print "
	<div id=\"div_guide\" style=\"width:600px;\">
	You can upload a CSV file of jobs with phone numbers from Data 8 (for Collection jobs only).<br>
	The phone numbers will then be automatically added to the respective jobs.<br>
	The columns of the CSV file should be:
	<ul style=\"margin-top:0px;\">
		<li>\"Blank\" (VIL Number)</li>
		<li>\"Full Name\"</li>
		<li>\"Home Address [1]\"</li>
		<li>\"Home Address [2]\"</li>
		<li>\"Home Address [3]\"</li>
		<li>\"Home Address [4]\"</li>
		<li>\"Home Address [5]\"</li>
		<li>\"Post Code\"</li>
		<li>(no column title) - phone number(s)</li>
	</ul>
	(Note that the name and address columns are ignored when imported.)
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

	sql_encryption_preparation('JOB_PHONE');

	$lix = 1;
	while (($line = fgetcsv($fhan)) !== false)
	{
		dprint("line=" . print_r($line,1));
		$rc = phone_add($line, $lix);
		if ($rc)
			$errors[] = array('LNO' => $lix, 'LINE' => $line, 'ERROR' => $rc);
		$lix++;
	}
	dprint("EOF");

	dprint("Errors=" . print_r($errors,1));

	fclose($fhan);

} # import_csv()

function phone_add($line, $lix)
{
	global $phones;
	global $job_id;
	global $sqlFalse;
	global $sqlTrue;

	$fix = 0; # field index

	# Get newest job that has the given VILNo
	$vilno = intval(trim($line[$fix]));
	list($ms_top, $my_limit) = sql_top_limit(1);
	$sql = "SELECT $ms_top JOB_ID, JC_JOB FROM JOB WHERE (J_VILNO=$vilno) AND (OBSOLETE=$sqlFalse) ORDER BY JOB_ID DESC $my_limit";
	sql_execute($sql);
	$job_id = 0;
	$jc_job = 0;
	while (($newArray = sql_fetch()) != false)
	{
		$job_id = $newArray[0];
		$jc_job = intval($newArray[1]);
	}
	if (!$job_id)
		return "No job with VILNo \"{$line[$fix]}\" was found in the database";
	if (!$jc_job)
		return "The job with VILNo \"{$line[$fix]}\" is not a Collection job";
	$fix++;

	$full_name = trim($line[$fix++]);
	$addr_1 = trim($line[$fix++]);
	$addr_2 = trim($line[$fix++]);
	$addr_3 = trim($line[$fix++]);
	$addr_4 = trim($line[$fix++]);
	$addr_5 = trim($line[$fix++]);
	$postcode = trim($line[$fix++]);

	# The phone number is in $line[8] after Vilno, FullName, Address-1 to -5 and Postcode
	$fix = 8;
	$jp_phone = '';
	if ($fix < count($line))
		$jp_phone = trim($line[$fix]);
	else
		return ''; # success  "The job with VILNo \"{$line[$fix]}\" does not have a column position $fix";

	if (!$jp_phone)
		return ''; # success - the phone number field is blank

	$primary = $sqlFalse;
	$sql = "SELECT COUNT(*) FROM JOB_PHONE WHERE JOB_ID=$job_id AND OBSOLETE=$sqlFalse";
	sql_execute($sql);
	$count = 0;
	while (($newArray = sql_fetch()) != false)
		$count = $newArray[0];
	if ($count == 0)
		$primary = $sqlTrue;

	$sql = "SELECT COUNT(*) FROM JOB_PHONE WHERE JOB_ID=$job_id AND OBSOLETE=$sqlFalse AND " .
				"(" . sql_decrypt('JP_PHONE') . " = " . quote_smart($jp_phone, true) . ")";
	sql_execute($sql);
	$count = 0;
	while (($newArray = sql_fetch()) != false)
		$count = $newArray[0];
	if ($count == 0)
	{
		$jp_phone_sql = sql_encrypt($jp_phone, false, 'JOB_PHONE');
		$descr_sql = sql_encrypt("From Data 8 " . date_now(), false, 'JOB_PHONE');

		$fields = "JOB_ID,  JOB_SUBJECT_ID, JP_PHONE,      JP_PRIMARY_P, JP_DESCR,   IMPORTED,  IMP_PH";
		$values = "$job_id, NULL,           $jp_phone_sql, $primary,     $descr_sql, $sqlFalse, $sqlFalse";

		$sql = "INSERT INTO JOB_PHONE ($fields) VALUES ($values)";
		dprint("phone_add(): $sql");#
		audit_setup_job($job_id, 'JOB_PHONE', 'JOB_PHONE_ID', 0, '', '');
		$job_phone_id = sql_execute($sql, true); # audited
		dprint("phone_add(): \$job_phone_id/1=$job_phone_id");#

		$phones[] = array('LNO' => $lix, 'VILNO' => $vilno, 'JOB_ID' => $job_id, 'FULL_NAME' => $full_name,
			'ADDR_1' => $addr_1, 'ADDR_2' => $addr_2, 'ADDR_3' => $addr_3, 'ADDR_4' => $addr_4, 'ADDR_5' => $addr_5, 'POSTCODE' => $postcode,
			'DATA_8_PHONE' => $jp_phone);
	}

	return ''; # success

} # phone_add()

function print_phones()
{
	global $ar;
	global $phones;

	if ($phones)
	{
		print "
		<h3 style=\"color:blue;\">The following phone numbers have been added to the database:</h3>
		<table class=\"spaced_table\">
		<tr>
			<th>CSV Line</th><th>VILNo</th><th>Full Name</th><th>Address 1</th><th>Address 2</th><th>Address 3</th><th>Address 4</th><th>Address 5</th><th>Postcode</th><th>Job ID</th><th>Data 8 Phone</th>
		</tr>
		";
		foreach ($phones as $one)
		{
			print "
			<tr>
				<td $ar>{$one['LNO']}</td>
				<td $ar>{$one['VILNO']}</td>
				<td>{$one['FULL_NAME']}</td>
				<td>{$one['ADDR_1']}</td>
				<td>{$one['ADDR_2']}</td>
				<td>{$one['ADDR_3']}</td>
				<td>{$one['ADDR_4']}</td>
				<td>{$one['ADDR_5']}</td>
				<td>{$one['POSTCODE']}</td>
				<td $ar>{$one['JOB_ID']}</td>
				<td>{$one['DATA_8_PHONE']}</td>
			</tr>
			";
		}
		print "
		</table>
		<hr>
		";
	}
	else
		print "
		<h3 style=\"color:blue;\">No phone numbers have been added to the database.</h3>
			";
	print "
	<br>
	<br>
	";
} # print_phones()

?>
