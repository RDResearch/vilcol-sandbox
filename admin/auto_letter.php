<?php

$script_check = true; # bypass script_check.php
include_once("settings.php");
include_once("library.php");
include_once("lib_pdf.php");
global $auto_letter; # settings.php
global $system_user_id;
global $unix_path;

ini_set('max_execution_time', 3590); # just under one hour, because two cronjobs run at 2am and 3am

# Don't need $cronjob as we are now using curl.exe instead of php.exe
#$cronjob = true; # is set to false in settings.php

$debug = false; #(isset($_GET['d']) ? true : false);
#$debug = true;#
$do_create = true;
#$do_create = false;#
$only_letter_1 = 0;
#$only_letter_1 = 26; # Letter 1 #
#$only_letter_1 = 27; # Letter 2 #
#$only_letter_1 = 28; # Letter 3 #
#$only_letter_1 = 29; # Contact #
#$only_letter_1 = 30; # Demand #

$only_imported = false; # Only create letters for imported jobs
$auto_letter = true; # used by lib_vilcol.php / add_collect_letter()

#$jobs_limit = 5000; # This should be a number that can be processed in under an hour, due to max_execution_time setting above
#$jobs_limit = 1500; # This should be a number that can be processed before the script runs out of memory!
	# From log file:
	# 2017-02-16 05:10:37/-1 Created 1400 letters so far...
	# 2017-02-16 05:11:09/-1 Created 1500 letters so far...
	# From cron email:
	# Fatal error: Allowed memory size of 134217728 bytes exhausted (tried to allocate 122880 bytes) in /var/www/vhosts/vilcoldb.com/httpdocs/admin/dompdf_060/dompdf/lib/fonts/Times-Roman.afm.php on line 264

$only_one_job = 0;
#$only_one_job = 559865;#

# Letters should only be created for jobs that have a status beginning with 'A' (see email from Jim 25/01/17 11:49)
$statuses_starting_a = "3,2,4,1,27,5";

# Set user_id (id of logged-in user) for auditing
$USER = array('USER_ID' => $system_user_id, 'U_DEBUG' => 0);

sql_connect();
log_open($debug ? "auto_letter.log" : ($unix_path . '/auto_letter.log'));
auto_main();
log_close();
sql_disconnect();

/*

SELECT COUNT(*) FROM JOB_LETTER WHERE JL_AL=1

SELECT L.LETTER_TYPE_ID, T.LETTER_NAME, COUNT(*)
FROM JOB_LETTER AS L LEFT JOIN LETTER_TYPE_SD AS T ON T.LETTER_TYPE_ID=L.LETTER_TYPE_ID
WHERE L.JL_AL=1 GROUP BY L.LETTER_TYPE_ID, T.LETTER_NAME ORDER BY L.LETTER_TYPE_ID

LETTER_TYPE_ID	Letter Name
	26			Letter 1
	27			Letter 2
	28			Letter 3
	29			Contact

25/01/17:
UPDATE JOB SET JOB.JC_LETTER_MORE=1, JOB.JC_LETTER_TYPE_ID=26, JOB.JC_JOB_STATUS_ID=1 WHERE JOB.JOB_ID IN (
	SELECT J.JOB_ID
	FROM JOB AS J INNER JOIN CLIENT2 AS C ON C.CLIENT2_ID=J.CLIENT2_ID
	WHERE (C.C_CODE IN (1602,6393,5603,5602,8259,8263)) AND ('2017-01-01' <= J.J_OPENED_DT)
	)

 */

function auto_main()
{
	# Find out which collection jobs need another letter created and adding to the "pending letters" queue.

	global $debug;
	global $do_create;
	global $id_LETTER_TYPE_letter_1;
	#global $id_JOB_STATUS_act; # JOB_STATUS_SD.JOB_STATUS_ID for "ACT"
#	global $jobs_limit;
	global $only_imported;
	global $only_letter_1;
	global $only_one_job;
	global $sqlFalse;
	global $sqlTrue;
	global $statuses_starting_a;

	$log_verbose = false;
	
//	$max_et = 60 * 60 * 4; # 4 hours
//	dlog("Setting max_execution_time to $max_et...", true);
//
//	set_time_limit($max_et); # This might not be working when run as a cron job using php.exe - use curl.exe instead
//	dlog("ini_get('max_execution_time') returns: " . ini_get('max_execution_time'), true);

	if ($debug)
		dlog("auto_letter.php/auto_main() - Enter", true);

	if ($log_verbose) dlog("init_data()...", true);
	init_data(); # sets $id_JOB_STATUS_act

	$two_years_ago = "'" . (intval(strftime_rdr('%Y')) - 2) . strftime_rdr("-%m-%d") . "'";
	$where_one_job = ($only_one_job ? " AND (J.JOB_ID=$only_one_job) " : ''); # Always: ''
	$where_imported = ($only_imported ? " AND (J.IMPORTED=1) " : ''); # Always: ''

	if ($log_verbose) dlog("SELECT/1...", true);
	if ($debug)
	{
		$sql = "
			SELECT COUNT(*)
			FROM JOB AS J
			WHERE (J.JC_JOB=$sqlTrue) AND (J.JOB_CLOSED=$sqlFalse) AND (J.J_ARCHIVED=$sqlFalse) $where_one_job $where_imported
			";
		dprint($sql,true);#
		sql_execute($sql);
		$count = 0;
		while (($newArray = sql_fetch()) != false)
			$count = $newArray[0];
		if ($log_verbose) dlog("Found " . number_with_commas($count, false) . " open collection jobs", true);
	}

	if ($log_verbose) dlog("SELECT/2...", true);
	if ($debug)
	{
		$sql = "
			SELECT DISTINCT(J.JOB_ID)
			FROM JOB AS J
			LEFT JOIN JOB_ACT AS A ON A.JOB_ID=J.JOB_ID
			WHERE (J.JC_JOB=$sqlTrue) AND (J.JOB_CLOSED=$sqlFalse) AND (J.J_ARCHIVED=$sqlFalse)
				AND ( ($two_years_ago <= J.J_OPENED_DT) OR ($two_years_ago <= J.J_UPDATED_DT) OR ($two_years_ago <= A.JA_DT) ) $where_one_job $where_imported
			";
		dprint($sql,true);#
		sql_execute($sql);
		$jobs = array();
		while (($newArray = sql_fetch()) != false)
			$jobs[] = $newArray[0];
		if ($log_verbose) dlog("Found " . number_with_commas(count($jobs), false) . " open collection jobs that: " .
				"(i) were received within last two years, or " .
				"(ii) were updated within the last two years, or " .
				"(iii) have activity recorded within the last two years.<br>",
				 true);
		#dprint(print_r($jobs,1),true);#
	}

	if ($log_verbose) dlog("SELECT/3...", true);
	if ($debug)
	{
		$sql = "
			SELECT DISTINCT(J.JOB_ID)
			FROM JOB AS J
			LEFT JOIN JOB_ACT AS A ON A.JOB_ID=J.JOB_ID
			WHERE (J.JC_JOB=$sqlTrue) AND (J.JOB_CLOSED=$sqlFalse) AND (J.J_ARCHIVED=$sqlFalse)
				AND (J.JC_JOB_STATUS_ID IN ($statuses_starting_a))
				AND ( ($two_years_ago <= J.J_OPENED_DT) OR ($two_years_ago <= J.J_UPDATED_DT) OR ($two_years_ago <= A.JA_DT) ) $where_one_job $where_imported
			";
				#AND (J.JC_JOB_STATUS_ID=$id_JOB_STATUS_act)
		dprint($sql,true);#
		sql_execute($sql);
		$jobs = array();
		while (($newArray = sql_fetch()) != false)
			$jobs[] = $newArray[0];
		if ($log_verbose) dlog("Found " . number_with_commas(count($jobs), false) . " open \"ACT\" collection jobs that: " .
				"(i) were received within last two years, or " .
				"(ii) were updated within the last two years, or " .
				"(iii) have activity recorded within the last two years.<br>",
				 true);
		#dprint(print_r($jobs,1),true);#
	}

//	$auto_tick_more_letters = false; #
//	if ($auto_tick_more_letters)
//	{
//		dlog("SELECT/AUTO_TICK_MORE_LETTERS...", true);
//		$sql = "
//			SELECT DISTINCT(J.JOB_ID)
//			FROM JOB AS J
//			LEFT JOIN JOB_ACT AS A ON A.JOB_ID=J.JOB_ID
//			WHERE (J.JC_JOB=$sqlTrue) AND (J.JOB_CLOSED=$sqlFalse) AND (J.J_ARCHIVED=$sqlFalse)
//				AND (J.JC_JOB_STATUS_ID IN ($statuses_starting_a))
//				AND ( ($two_years_ago <= J.J_OPENED_DT) OR ($two_years_ago <= J.J_UPDATED_DT) OR ($two_years_ago <= A.JA_DT) )
//				AND (J.JC_LETTER_MORE=0) $where_one_job $where_imported
//			";
//				#AND (J.JC_JOB_STATUS_ID=$id_JOB_STATUS_act)
//		#dprint($sql,true);#
//		sql_execute($sql);
//		$jobs = array();
//		while (($newArray = sql_fetch()) != false)
//			$jobs[] = $newArray[0];
//		dlog("Found " . number_with_commas(count($jobs), false) . " open \"ACT\" collection jobs that: " .
//				"(i) were received within last two years, or " .
//				"(ii) were updated within the last two years, or " .
//				"(iii) have activity recorded within the last two years.<br>" .
//				"AND have \"More Letters\" NOT ticked",
//				 true);
//		#dprint(print_r($jobs,1),true);#
//
//	}
//	else
//	{

	if ($log_verbose) dlog("SELECT/4...", true);
	if ($debug)
	{
		$sql = "
			SELECT DISTINCT J.JOB_ID, J.CLIENT2_ID, J.JC_LETTER_TYPE_ID
			FROM JOB AS J
			LEFT JOIN JOB_ACT AS A ON A.JOB_ID=J.JOB_ID
			WHERE (J.JC_JOB=$sqlTrue) AND (J.JOB_CLOSED=$sqlFalse) AND (J.J_ARCHIVED=$sqlFalse)
				AND (J.JC_JOB_STATUS_ID IN ($statuses_starting_a))
				AND ( ($two_years_ago <= J.J_OPENED_DT) OR ($two_years_ago <= J.J_UPDATED_DT) OR ($two_years_ago <= A.JA_DT) )
				AND (J.JC_LETTER_MORE=1) AND (0 < J.JC_LETTER_TYPE_ID) $where_one_job $where_imported
			";
				#AND (J.JC_JOB_STATUS_ID=$id_JOB_STATUS_act)
		dprint($sql,true);#
		sql_execute($sql);
		$jobs = array();
		while (($newArray = sql_fetch()) != false)
			$jobs[] = $newArray[0];
		if ($log_verbose) dlog("Found " . number_with_commas(count($jobs), false) . " open \"ACT\" collection jobs that: " .
				"(i) were received within last two years, or " .
				"(ii) were updated within the last two years, or " .
				"(iii) have activity recorded within the last two years<br>" .
				"and have a \"Next Letter\" waiting to be created.<br>",
				 true);
		#dprint(substr(print_r($jobs,1),0,100) . "...",true);#
	}

	if ($log_verbose) dlog("SELECT/5...", true);
	$sql = "
		SELECT DISTINCT J.JOB_ID, J.CLIENT2_ID, J.JC_LETTER_TYPE_ID, J.JC_LETTER_DELAY
		FROM JOB AS J
		LEFT JOIN JOB_ACT AS A ON A.JOB_ID=J.JOB_ID
		WHERE (J.JC_JOB=$sqlTrue) AND (J.JOB_CLOSED=$sqlFalse) AND (J.J_ARCHIVED=$sqlFalse)
				AND (J.JC_JOB_STATUS_ID IN ($statuses_starting_a))
			AND ( ($two_years_ago <= J.J_OPENED_DT) OR ($two_years_ago <= J.J_UPDATED_DT) OR ($two_years_ago <= A.JA_DT) )
			AND (J.JC_LETTER_MORE=1) AND (0 < J.JC_LETTER_TYPE_ID) $where_one_job $where_imported
		";
			#AND (J.JC_JOB_STATUS_ID=$id_JOB_STATUS_act)
	if ($log_verbose) dlog($sql,true);
	sql_execute($sql);
	$jobs = array();
	while (($newArray = sql_fetch()) != false)
		$jobs[$newArray[0]] = array($newArray[1], $newArray[2], intval($newArray[3]));
	if ($log_verbose) dlog("Found " . number_with_commas(count($jobs), false) . " open \"ACT\" collection jobs that: " . # [limit=$jobs_limit]
			"(i) were received within last two years, or " .
			"(ii) were updated within the last two years, or " .
			"(iii) have activity recorded within the last two years<br>" .
			"and have a \"Next Letter\" waiting to be created.<br>",
			 true);

	$letters = array();
	if ($log_verbose) dlog("foreach (jobs (" . count($jobs) . "))...", true);
	$jobs_with_pending_letters = array();
	foreach ($jobs as $job_id => $one_job)
	{
		$client2_id = $one_job[0];
		$next_letter_id_job = intval($one_job[1]); # the letter to create next, according to the job

		# Check to see if there are any pending letters i.e. letters that have been created but not yet sent.
		# If there are, then we can't create another letter yet.
		$sql = "SELECT COUNT(*)
				FROM JOB_LETTER
				WHERE JOB_ID=$job_id AND OBSOLETE=$sqlFalse AND JL_POSTED_DT IS NULL AND JL_EMAIL_ID IS NULL";
		$pending_count = 0;
		sql_execute($sql);
		while (($newArray = sql_fetch()) != false)
			$pending_count = $newArray[0];
		if ($pending_count)
		{
			$jobs_with_pending_letters[] = $job_id;
			continue;
		}

		# Select the most recently sent letter
		list($ms_top, $my_limit) = sql_top_limit(1);
		$sql = "SELECT $ms_top JL.JOB_LETTER_ID, JL.LETTER_TYPE_ID, JL.JL_POSTED_DT, EM.EM_DT
				FROM JOB_LETTER AS JL
				LEFT JOIN EMAIL AS EM ON EM.EMAIL_ID=JL.JL_EMAIL_ID
				WHERE (JL.JOB_ID=$job_id) AND (JL.OBSOLETE=0) AND
						(  ((JL.JL_POSTED_DT IS NOT NULL) AND (JL.JL_POSTED_DT <> '')) OR ((EM.EM_DT IS NOT NULL) AND (EM.EM_DT <> ''))  )
				ORDER BY JL.JL_ADDED_DT DESC
				$my_limit";
		sql_execute($sql);
		$found_letter = false;
		while (($newArray = sql_fetch_assoc()) != false)
		{
			$found_letter = true;
			$one_letter = array('JOB_ID' => $job_id, 'CLIENT2_ID' => $client2_id, 'LAST_LETTER_ID' => $newArray['JOB_LETTER_ID'],
								'LAST_LETTER_TYPE_ID' => intval($newArray['LETTER_TYPE_ID']), 'LAST_SENT_DT' => '', 'DAYS_SINCE' => 0,
								'SEQ_DAYS' => 0, 'NEXT_LETTER_ID_JOB' => $next_letter_id_job, 'JC_LETTER_DELAY' => $one_job[2]);
			if ($newArray['JL_POSTED_DT'])
			{
				if ($newArray['EM_DT'])
				{
					if ($newArray['JL_POSTED_DT'] < $newArray['EM_DT'])
						$one_letter['LAST_SENT_DT'] = $newArray['EM_DT'];
					else
						$one_letter['LAST_SENT_DT'] = $newArray['JL_POSTED_DT'];
				}
				else
					$one_letter['LAST_SENT_DT'] = $newArray['JL_POSTED_DT'];
			}
			else
				$one_letter['LAST_SENT_DT'] = $newArray['EM_DT'];
			$then = date_sql_to_epoch($one_letter['LAST_SENT_DT']);
			$one_letter['DAYS_SINCE'] = (time() - $then) / (60 * 60 * 24);
			if (($only_letter_1 == 0) || ($only_letter_1 == $one_letter['NEXT_LETTER_ID_JOB']))
			$letters[] = $one_letter;
		}
		if (!$found_letter)
		{
			$one_letter = array('JOB_ID' => $job_id, 'CLIENT2_ID' => $client2_id, 'LAST_LETTER_ID' => 0,
								'LAST_LETTER_TYPE_ID' => 0, 'LAST_SENT_DT' => '', 'DAYS_SINCE' => 999,
								'SEQ_DAYS' => 0, 'NEXT_LETTER_ID_JOB' => $next_letter_id_job, 'JC_LETTER_DELAY' => $one_job[2]);
			if (($only_letter_1 == 0) || ($only_letter_1 == $one_letter['NEXT_LETTER_ID_JOB']))
			$letters[] = $one_letter;
		}
	} # foreach ($jobs)

	$jobs = array(); # discard it
	if ($log_verbose) dlog("Found " . number_with_commas(count($letters), false) . ($only_letter_1 ? " \"Letter X's\" ($only_letter_1) " : '') . " letters for open \"ACT\" collection jobs that: " .
			"(i) were received within last two years, or " .
			"(ii) were updated within the last two years, or " .
			"(iii) have activity recorded within the last two years<br>" .
			"and have a \"Next Letter\" waiting to be created<br>" .
			"and have no letters currently pending (created but not yet sent).<br>" .
			"But also found " . count($jobs_with_pending_letters) . " jobs with pending letters so can't create new letter.<br>",
			 true);
	if (count($letters) < 10)
	{
		if ($log_verbose) dlog("Found less than 10 letters: " . print_r($letters,1), true);
	}
	#dlog("Jobs with pending letters: " . print_r($jobs_with_pending_letters,1));#
	#dprint(substr(print_r($letters,1),0,900) . "...",true);#

	if ($log_verbose) dlog("for (letters) / 1...", true);
	# Filter $letters to only include those where there is a next letter in the sequence.
	$letters2 = array();
	$letters_count = count($letters);

	# Feedback #220 22/05/17: I don't understand why we are clipping letters count to $job_limit, so now removed:
	#if ($jobs_limit)
	#	$letters_count = min($letters_count, $jobs_limit);

	for ($ii = 0; $ii < $letters_count; $ii++)
	{
		$one_letter = $letters[$ii];
		if (!$one_letter['CLIENT2_ID'])
		{
			dlog("No CLIENT2_ID!!<br>\$one_letter=" . print_r($one_letter,1), true);
			return;
		}
		$master_sql = "SELECT SEQ_NUM, LETTER_TYPE_ID, SEQ_DAYS
						FROM LETTER_SEQ
						WHERE (CLIENT2_ID=XXX) AND (OBSOLETE=0)
						ORDER BY SEQ_NUM";

		$sql = str_replace('XXX', $one_letter['CLIENT2_ID'], $master_sql);
		#if ($ii < 3) dprint("ii=$ii, sql/1=$sql",true);#
		if ($debug && $only_one_job) dprint("ii=$ii, sql/1=$sql",true);#
		sql_execute($sql);
		$sequence = array();
		while (($newArray = sql_fetch_assoc()) != false)
			$sequence[] = $newArray;
		if (count($sequence) == 0)
		{
			$sql = str_replace('XXX', 0, $master_sql);
			#if ($ii < 3) dprint("ii=$ii, sql/2=$sql",true);#
			if ($debug && $only_one_job) dprint("ii=$ii, sql/2=$sql",true);#
			sql_execute($sql);
			while (($newArray = sql_fetch_assoc()) != false)
				$sequence[] = $newArray;
		}
		if ($debug && $only_one_job)
		{
			dlog("\$one_letter/1=" . print_r($one_letter,1), true);
			dlog("\$sequence=" . print_r($sequence,1), true);
		}

		$one_letter['SEQ_DAYS'] = 0;
		$one_letter['NEXT_LETTER_ID_SEQ'] = 0; # the letter to create next, according to the sequence
		# Note that if NEXT_LETTER_ID_SEQ differs to NEXT_LETTER_ID_JOB then the latter will be used.
		$one_letter['FUTURE_LETTER_ID'] = 0; # what the job's "Next Letter" will be set to, after we have created the next letter
		if (0 < count($sequence))
		{
			# Look for LAST_LETTER_TYPE_ID (letter type of letter last sent) in the sequence => $seq_line_last_letter,
			# and for NEXT_LETTER_ID_JOB (letter type of letter that job says should be sent next) in the sequence => $seq_line_next_letter;
			# if both found and $seq_line_next_letter < $seq_line_last_letter, then act as if there is no previous letter.
			# This happens for example after a bulk address change import, where the Next Letter is reset to Letter 1.
			$seq_line_last_letter = 0;
			$seq_line_next_letter = 0;
			if ((0 < $one_letter['LAST_LETTER_TYPE_ID']) && (0 < $one_letter['NEXT_LETTER_ID_JOB']))
			{
				for ($jj = 0; $jj < count($sequence); $jj++)
				{
					if ($sequence[$jj]['LETTER_TYPE_ID'] == $one_letter['LAST_LETTER_TYPE_ID']) # this sequence line matches previously sent letter
					{
						$seq_line_last_letter = $sequence[$jj]['SEQ_NUM'];
						break;
					}
				}
				for ($jj = 0; $jj < count($sequence); $jj++)
				{
					if ($sequence[$jj]['LETTER_TYPE_ID'] == $one_letter['NEXT_LETTER_ID_JOB']) # this sequence line matches letter to be sent next
					{
						$seq_line_next_letter = $sequence[$jj]['SEQ_NUM'];
						break;
					}
				}
			}
			if ($debug && $only_one_job)
				dlog("\$seq_line_last_letter=$seq_line_last_letter, \$seq_line_next_letter=$seq_line_next_letter", true);

			if (($one_letter['LAST_LETTER_TYPE_ID'] == 0) # there is no previous letter
					||
					( (0 < $seq_line_last_letter) && (0 < $seq_line_next_letter) && ($seq_line_next_letter < $seq_line_last_letter))
				)
			{
				if ($debug && $only_one_job)
					dlog("There is no previous letter", true);
				$one_letter['SEQ_DAYS'] = 1;
				$one_letter['NEXT_LETTER_ID_SEQ'] = $sequence[0]['LETTER_TYPE_ID']; # the letter to create next, according to the sequence
				$one_letter['FUTURE_LETTER_ID'] = $sequence[1]['LETTER_TYPE_ID'];
			}
			else
			{
				$last_letter_found_in_seq = false;
				for ($jj = 0; $jj < count($sequence); $jj++)
				{
					if ($sequence[$jj]['LETTER_TYPE_ID'] == $one_letter['LAST_LETTER_TYPE_ID']) # this sequence line matches previously sent letter
					{
						# We have found an entry in the sequence for the letter than has already been sent.
						$last_letter_found_in_seq = true;
						$one_letter['SEQ_DAYS'] = $sequence[$jj]['SEQ_DAYS'];
						if (($jj+1) < count($sequence))
						{
							$one_letter['NEXT_LETTER_ID_SEQ'] = $sequence[$jj+1]['LETTER_TYPE_ID']; # the letter to create next, according to the sequence
							if ($one_letter['NEXT_LETTER_ID_SEQ'] == $one_letter['NEXT_LETTER_ID_JOB'])
							{
								if (($jj+2) < count($sequence))
									$one_letter['FUTURE_LETTER_ID'] = $sequence[$jj+2]['LETTER_TYPE_ID'];
							}
							else
								$one_letter['FUTURE_LETTER_ID'] = $one_letter['NEXT_LETTER_ID_SEQ'];
						}
						break;
					}
				}
				if (!$last_letter_found_in_seq)
				{
					for ($jj = 0; $jj < count($sequence); $jj++)
					{
						if ($sequence[$jj]['LETTER_TYPE_ID'] == $one_letter['NEXT_LETTER_ID_JOB'])
						{
							# We have found an entry in the sequence for the letter that should be created next.
							$last_letter_found_in_seq = true;
							if (0 < $jj)
							{
								$one_letter['SEQ_DAYS'] = $sequence[$jj-1]['SEQ_DAYS'];
								$one_letter['NEXT_LETTER_ID_SEQ'] = $sequence[$jj]['LETTER_TYPE_ID']; # the letter to create next, according to the sequence
								if ($one_letter['NEXT_LETTER_ID_SEQ'] == $one_letter['NEXT_LETTER_ID_JOB'])
								{
									if (($jj+1) < count($sequence))
										$one_letter['FUTURE_LETTER_ID'] = $sequence[$jj+1]['LETTER_TYPE_ID'];
								}
								else
									$one_letter['FUTURE_LETTER_ID'] = $one_letter['NEXT_LETTER_ID_SEQ'];
							}
							break;
						}
					}
				}
			}
		}
		if ($debug && $only_one_job)
			dlog("\$one_letter/2=" . print_r($one_letter,1), true);
		if ((0 < $one_letter['SEQ_DAYS']) && (($one_letter['SEQ_DAYS'] + $one_letter['JC_LETTER_DELAY']) <= $one_letter['DAYS_SINCE']))
		{
			# We need to create the next letter (NEXT_LETTER_ID) and set the future next letter
			$one_2 = array('JOB_ID' => $one_letter['JOB_ID'], 'NEXT_LETTER_ID' => $one_letter['NEXT_LETTER_ID_JOB'],
								'FUTURE_LETTER_ID' => $one_letter['FUTURE_LETTER_ID']);
			if ($debug && $only_one_job)
				dlog("...need to create, \$one_2=" . print_r($one_2,1), true);
			$letters2[] = $one_2;
		}
		elseif ($debug && $only_one_job)
			dlog("...don't need to create because this failed: { 0 < SEQ_DAYS ({$one_letter['SEQ_DAYS']}) AND SEQ_DAYS+JC_LETTER_DELAY <= DAYS_SINCE ({$one_letter['SEQ_DAYS']}+{$one_letter['JC_LETTER_DELAY']} <= {$one_letter['DAYS_SINCE']} }", true);
	}
	$letters = array(); # discard it
	if ($log_verbose) dlog("Found " . number_with_commas(count($letters2), false) . " letters from open \"ACT\" collection jobs that: " .
			"(i) were received within last two years, or " .
			"(ii) were updated within the last two years, or " .
			"(iii) have activity recorded within the last two years<br>" .
			"and have a \"Next Letter\" waiting to be created<br>" .
			"and have no letters currently pending (created but not yet sent)<br>" .
			"and have a next letter due to be created now according to the Sequence.<br>",
			 true);
	if (count($letters2) < 10)
	{
		if ($log_verbose) dlog("Found less than 10 letters: " . print_r($letters2,1), true);
	}

	if ($debug)
	{
		$ii_max = count($letters2);
		$ii_inc = $ii_max / 100;
		if ($ii_inc < 1)
			$ii_inc = 1;
		$samples = array();
		for ($ii = 0; $ii < $ii_max; $ii += $ii_inc)
		{
			$ii = floor($ii);
			$sql = "SELECT C.C_CODE, J.J_VILNO, J.J_OPENED_DT, J.J_UPDATED_DT, J.JC_LETTER_MORE, L.LETTER_NAME
					FROM JOB AS J INNER JOIN CLIENT2 AS C ON C.CLIENT2_ID=J.CLIENT2_ID
					LEFT JOIN LETTER_TYPE_SD AS L ON L.LETTER_TYPE_ID=J.JC_LETTER_TYPE_ID
					WHERE J.JOB_ID={$letters2[$ii]['JOB_ID']}";
			sql_execute($sql);
			while (($newArray = sql_fetch_assoc()) != false)
				$samples[] = $newArray;
			#dlog("Sample found (ii=$ii): " . print_r($letters2[$ii],1), true);
		}
		dlog("Samples: " . print_r($samples,1), true);
	}
	#dprint(substr(print_r($letters2,1),0,900) . "...",true);#
//	foreach ($letters2 as $one_letter)
//	{
//		if ($one_letter['FUTURE_LETTER_ID'])
//			dprint(print_r($one_letter,1),true);#
//	}

//	dlog("---return---", true);
//	return;#

  if (!$debug)
  {
	if ($log_verbose) dlog("for (letters) / 2...", true);
	# Create letters
	global $job_id;
	$created = 0;
	$created_letter_1 = 0;
	foreach ($letters2 as $one_letter)
	{
		# E.g. $one_letter = Array ( [JOB_ID] => 1270159 [NEXT_LETTER_ID] => 27 [FUTURE_LETTER_ID] => 28 )
		# We need to create the next letter and automatically approve it, then we set JOB.JC_LETTER_TYPE_ID to the future letter id.
		# If the future id is zero then we need to untick "more letters".
		#
		#$debug2 = (($debug && ($created < 10)) ? true : false);
		if ($log_verbose)
			$debug2 = (($created < 10) ? true : false);
		else
			$debug2 = false;
		
		$job_id = $one_letter['JOB_ID'];
		#if (0 < $one_letter['FUTURE_LETTER_ID']) # only do ones with a future
		#{
		if ($debug2) dlog("Creating letter type {$one_letter['NEXT_LETTER_ID']} for job $job_id (future letter is {$one_letter['FUTURE_LETTER_ID']})...",true);#
		$job_letter_id = ($do_create ?
							add_collect_letter($one_letter['NEXT_LETTER_ID']) # this creates PDF & HTML files and approves the letter automatically
							: -1);
		if ($debug2 && $do_create) dlog("... created JOB_LETTER_ID=$job_letter_id",true);#

		$future_letter_id = ((0 < $one_letter['FUTURE_LETTER_ID']) ? $one_letter['FUTURE_LETTER_ID'] : 'NULL');
		$sql = "UPDATE JOB SET JC_LETTER_TYPE_ID=$future_letter_id WHERE JOB_ID=$job_id";
		if ($debug2 && $do_create) dlog("... $sql",true);#
		audit_setup_job($job_id, 'JOB', 'JOB_ID', $job_id, 'JC_LETTER_TYPE_ID', $future_letter_id);
		if ($do_create)
			sql_execute($sql, true); # audited
		$sql = "UPDATE JOB SET JC_LETTER_DELAY=0 WHERE JOB_ID=$job_id";
		if ($debug2 && $do_create) dlog("... $sql",true);#
		audit_setup_job($job_id, 'JOB', 'JOB_ID', $job_id, 'JC_LETTER_DELAY', 0);
		if ($do_create)
			sql_execute($sql, true); # audited
		if ($debug2 && $do_create) dlog("... done UPDATE",true);#

		$created++;
		if ($one_letter['NEXT_LETTER_ID'] == $id_LETTER_TYPE_letter_1)
			$created_letter_1++;

		if (($created % 100) == 0)
		{
			if ($log_verbose) dlog("Created $created letters so far...", true);
		}

		#} # only do ones with a future

		#if (0 < $created)
		#	break;
		#if (0 < $created_letter_1) break; #
	}
	dlog(($do_create ? "Created" : "Would have created") . " $created letters",true);
  }

//	} # else not if ($auto_tick_more_letters)

	if ($debug)
		dlog("auto_letter.php/auto_main() - Exit", true);

} # auto_main()

?>
