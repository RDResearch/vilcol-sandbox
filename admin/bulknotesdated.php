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
	$page_title_2 = 'Dated Notes - Vilcol';
	screen_layout();
}
else
	print "<p>" . server_php_self() . ": login is not enabled</p>";

sql_disconnect();
log_close();

function screen_content()
{
	global $errors;
	global $notes;
	global $page_title_2;

	print "<h3>Bulk Import of Dated Notes from CSV</h3>";

	dprint(post_values());
	dprint("FILES=" . xprint(print_r($_FILES,1), false, 1));
	set_time_limit(60 * 60 * 1); # 1 hour

	if (isset($_POST['upload_marker']))
	{
		$file = get_uploaded_file();
		if ($file)
		{
			$notes = array();
			$errors = array();
			import_csv($file);
			if ($errors)
				print_errors();
			print_notes();
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
	You can upload a CSV file of dated notes on jobs.<br>
	The notes will then be automatically added to the respective jobs.<br>
	The columns of the CSV file should be:
	<ul style=\"margin-top:0px;\">
		<li>VIL Number</li>
		<li>Note Date</li>
		<li>Note Text</li>
		<li>Letter Date</li>
		<li>Letter Type:<br>
			<pre>
LETTER_TYPE_ID	LETTER_NAME
      26	Letter 1
      27	Letter 2
      28	Letter 3
      29	Contact
      30	Demand
      31	Misc. 1
      32	Misc. 2
      33	Misc. 3
      34	Misc. 4
      35	Misc. 5
      36	Misc. 6
      37	Misc. 7
      38	Misc. 8
      39	Misc. 9
      40	Misc. 10
      41	Misc. 11
      42	Misc. 12
      43	Misc. 13
      44	Misc. 14
      45	Misc. 15
      46	Misc. 16
      47	Misc. 17
      48	Misc. 18
      49	Misc. 19
      50	Misc. 20
      51	Misc. 21
      52	Misc. 22
      53	Misc. 23
      54	Misc. 24
      55	Misc. 25
      56	Misc. 26
      57	Misc. 27
      58	Misc. 28
      59	Misc. 29
      60	Misc. 30
      61	Misc. 31
      62	Misc. 32
      63	Misc. 33
      64	Misc. 34
      65	Misc. 35
      66	Misc. 36
      67	Misc. 37
      68	Misc. 38
      69	Misc. 39
      70	Misc. 40
      71	Misc. 41
      72	Misc. 42
      73	Misc. 43
      74	Misc. 44
      75	Misc. 45
      76	Misc. 46
      77	Misc. 47
      78	Misc. 48
      79	Misc. 49
      80	Misc. 50
      81	Allpay
      82	Debit Ca
      83	Discount
      84	High Dis
      85	Rcpt
      86	Refusal
      87	Reve
      88	Reveiw l
      89	Standing
      90	Swalp
      91	Freehand
</pre>
</li>
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
		$rc = note_add($line, $lix);
		if ($rc)
			$errors[] = array('LNO' => $lix, 'LINE' => $line, 'ERROR' => $rc);
		$lix++;
	}
	dprint("EOF");

	dprint("Errors=" . print_r($errors,1));

	fclose($fhan);

} # import_csv()

function note_add($line, $lix)
{
	global $job_id; # this is needed by add_collect_letter()
	global $notes;
	global $sqlFalse;
	global $sqlTrue;

	$fix = 0; # field index

	# Get newest collection job that has the given VILNo
	$vilno = intval(trim($line[$fix]));
	if (0 < $vilno)
	{
		list($ms_top, $my_limit) = sql_top_limit(1);
		$sql = "SELECT $ms_top JOB_ID FROM JOB WHERE (J_VILNO=$vilno) AND (JC_JOB=$sqlTrue) AND (OBSOLETE=$sqlFalse) ORDER BY JOB_ID DESC $my_limit";
		sql_execute($sql);
		$job_id = 0;
		while (($newArray = sql_fetch()) != false)
			$job_id = $newArray[0];
		if (!$job_id)
			return "No job with VILNo \"{$line[$fix]}\" was found in the database";
	}
	else
		return "VILNo \"{$line[$fix]}\" was not recognised";
	$fix++;

	# Get Note Date
	$error_note_date = '';
	if (!array_key_exists($fix, $line))
		$error_note_date = "The note date column is missing";
	else
	{
		$note_date = $line[$fix];
		if ((!$note_date) || ($note_date == 'NULL'))
			$error_note_date = "The note date \"{$line[$fix]}\" is blank";
		else
		{
			$note_date = date_for_sql($note_date, false, true, false, false, false, true);
			if ((!$note_date) || ($note_date == 'NULL'))
				$error_note_date = "The note date \"{$line[$fix]}\" is not recognised as a date";
			else
			{
				if (intval(substr($note_date,0,4)) < 1980)
					$error_note_date = "The note date \"{$line[$fix]}\" is before 1980";
			}
		}
	}
	$fix++;

	# Get Note Text
	$error_note_text = '';
	if (!array_key_exists($fix, $line))
		$error_note_text = "The note text column is missing";
	else
	{
		$note_text = $line[$fix];
		if ((!$note_text) || ($note_text == 'NULL'))
			$error_note_text = "The note text \"{$line[$fix]}\" is invalid";
	}
	$fix++;

	# Get Letter Date
	$error_letter_date = '';
	if (!array_key_exists($fix, $line))
		$error_letter_date = "The letter date column is missing";
	else
	{
		$letter_date = $line[$fix];
		if ((!$letter_date) || ($letter_date == 'NULL'))
			$error_letter_date = "The letter date \"{$line[$fix]}\" is blank";
		else
		{
			$letter_date = date_for_sql($letter_date, false, true, false, false, false, true);
			if ((!$letter_date) || ($letter_date == 'NULL'))
				$error_letter_date = "The letter date \"{$line[$fix]}\" is not recognised as a date";
			else
			{
				if (intval(substr($letter_date,0,4)) < 1980)
					$error_letter_date = "The letter date \"{$line[$fix]}\" is before 1980";
			}
		}
	}
	$fix++;

	# Get Letter Type
	$error_letter_type = '';
	if (!array_key_exists($fix, $line))
		$error_letter_type = "The letter type column is missing";
	else
	{
		$letter_type = $line[$fix];
		if ((!$letter_type) || ($letter_type == 'NULL'))
			$error_letter_type = "The letter type \"{$line[$fix]}\" is invalid";
		else
		{
			$letter_type = intval($letter_type);
			if (!((26 <= $letter_type) && ($letter_type <= 91)))
				$error_letter_type = "The letter type \"{$line[$fix]}\" is outside the valid range 26 to 91";
		}
	}
	$fix++;

	if ($error_note_date || $error_note_text)
	{
		$note_date = '';
		$note_text = '';
	}
	if ($error_letter_date || $error_letter_type)
	{
		$letter_date = '';
		$letter_type = '';
	}
	if (($error_note_date || $error_note_text) && ($error_letter_date || $error_letter_type))
	{
		$return = '';
		if ($error_note_date)
			$return .= ($return ? '<br>' : '') . $error_note_date;
		if ($error_note_text)
			$return .= ($return ? '<br>' : '') . $error_note_text;
		if ($error_letter_date)
			$return .= ($return ? '<br>' : '') . $error_letter_date;
		if ($error_letter_type)
			$return .= ($return ? '<br>' : '') . $error_letter_type;
		return $return;
//		if ($error_note_date)
//			return $error_note_date;
//		if ($error_note_text)
//			return $error_note_text;
//		if ($error_letter_date)
//			return $error_letter_date;
//		if ($error_letter_type)
//			return $error_letter_type;
	}

	dprint("Found JOB_ID $job_id, Note \"$note_date\"-\"$note_text\", \"$letter_date\"-\"$letter_type\"");

	if ($note_date && $note_text)
	{
		# Add note to job
		$job_note_id = sql_add_note($job_id, $note_text, true, $note_date);
	}
	else
		$job_note_id = '';

	if ($letter_date && $letter_type)
	{
		# Add letter to job
		$job_letter_id = add_collect_letter($letter_type, 0, $letter_date); # this gets $job_id from global
	}
	else
		$job_letter_id = '';

	$notes[] = array('LNO' => $lix, 'VILNO' => $vilno, 'JOB_ID' => $job_id, 'NOTE_DATE' => $note_date, 'NOTE_TEXT' => $note_text, 'LETTER_DATE' => $letter_date, 'LETTER_TYPE' => $letter_type, 'JOB_NOTE_ID' => $job_note_id, 'JOB_LETTER_ID' => $job_letter_id);
	return ''; # success

} # note_add()

function print_notes()
{
	global $ar;
	global $notes;

	print "
	<h3 style=\"color:blue;\">The following notes have been added to the database:</h3>
	<table class=\"spaced_table\">
	<tr>
		<th>CSV Line</th><th>VILNo</th><th>Note Date</th><th>Note Text</th><th>Letter Date</th><th>Letter Type</th><th>Job ID</th><th>Job Note ID</th><th>Letter ID</th>
	</tr>
	";
	foreach ($notes as $one)
	{
		print "
		<tr>
			<td $ar>{$one['LNO']}</td>
			<td $ar>{$one['VILNO']}</td>
			<td>{$one['NOTE_DATE']}</td>
			<td>{$one['NOTE_TEXT']}</td>
			<td>{$one['LETTER_DATE']}</td>
			<td>{$one['LETTER_TYPE']}</td>
			<td $ar>{$one['JOB_ID']}</td>
			<td $ar>{$one['JOB_NOTE_ID']}</td>
			<td $ar>{$one['JOB_LETTER_ID']}</td>
		</tr>
		";
	}
	print "
	</table>
	<hr>
	<br>
	<br>
	";
} # print_notes()

?>
