<?php

$script_check = true; # bypass script_check.php
include_once("settings.php");
include_once("library.php");
global $crlf;
global $unix_path;

ini_set('max_execution_time', 600); # 10 minutes

#$log_filename = "auto_letter.log"; # manual run
$log_filename = $unix_path . '/auto_letter.log'; # cronjob run
#print "auto_pdf.php opening \"$log_filename\" for logging...{$crlf}";
log_open($log_filename);

sql_connect();
auto_main();
sql_disconnect();

log_close();

function auto_main()
{
	global $crlf;
	global $csv_dir;
//	global $debug;
//	global $do_create;
//	global $id_LETTER_TYPE_letter_1;
//	#global $id_JOB_STATUS_act; # JOB_STATUS_SD.JOB_STATUS_ID for "ACT"
//#	global $jobs_limit;
//	global $only_imported;
//	global $only_letter_1;
//	global $only_one_job;
//	global $sqlFalse;
//	global $sqlTrue;
//	global $statuses_starting_a;
	
	$log_verbose = false;
	
//	$max_et = 60 * 60 * 4; # 4 hours
//	dprint("Setting max_execution_time to $max_et...", true);
//	
//	set_time_limit($max_et); # This might not be working when run as a cron job using php.exe - use curl.exe instead
//	dprint("ini_get('max_execution_time') returns: " . ini_get('max_execution_time'), true);
	
	#dlog("auto_pdf.php/auto_main() - Enter", true);
	#emlog("auto_pdf.php/auto_main() - Enter");
	
	#dprint("init_data()...", true);
	init_data(); # sets $id_JOB_STATUS_act

	#$do_unlink = false;
	$do_unlink = true;
	
	$files = scandir($csv_dir);
	$bulks = array();
	$client_dirs = array();
	$reps = array();
	$searches = array();
	$vilno_dirs = array();
	$unknowns = array();
	
	foreach ($files as $onefile)
	{
		if ($onefile == 'bulk')
			$bulks[] = $onefile;
		elseif ((substr($onefile,0,1) == 'c') && (0 < intval(substr($onefile,1))))
			$client_dirs[] = $onefile;
		elseif ($onefile == 'reps')
			$reps[] = $onefile;
		elseif ($onefile == 'search')
			$searches[] = $onefile;
		elseif ((substr($onefile,0,1) == 'v') && (0 < intval(substr($onefile,1))))
			$vilno_dirs[] = $onefile;
		else
			$unknowns[] = $onefile;
	}
//	dprint("\$files=" . print_r($files,1), true);
//	dprint("\$bulks=" . print_r($bulks,1), true);
//	dprint("\$client_dirs=" . print_r($client_dirs,1), true);
//	dprint("\$reps=" . print_r($reps,1), true);
//	dprint("\$searches=" . print_r($searches,1), true);
//	dprint("\$vilno_dirs=" . print_r($vilno_dirs,1), true);
//	dprint("\$unknowns=" . print_r($unknowns,1), true);

	$count_rest = count($bulks) + count($client_dirs) + count($reps) + count($searches) + count($vilno_dirs) + count($unknowns);
	#dlog("count(files)=" . count($files) . ", count(rest)=$count_rest" . ", count(vilno_dirs)=" . count($vilno_dirs), true);
	if ($log_verbose) emlog("count(files)=" . count($files) . ", count(rest)=$count_rest" . ", count(vilno_dirs)=" . count($vilno_dirs));
	
	$total_size = 0;
	$total_count = 0;
	$purge_size = 0;
	$purge_count = 0;
	$deletions = array();
	
	sort($vilno_dirs);
	foreach ($vilno_dirs as $onedir)
	{
		# Example of $onedir: v123456
		
		#$ep_period = 60 * 60 * 24 * 7; # one week in epoch time
		$ep_period = 60 * 60 * 24 * 124; # 124 days (approx 4 months) in epoch time, for "3 calendar months"
		#$ep_period = 60 * 60 * 24 * 365; # one year in epoch time
		$now = time();
		$threshold_date = $now - $ep_period;
		
		$vilno_files = scandir("$csv_dir/$onedir");
		$pdfs = array();
		foreach ($vilno_files as $onefile)
		{
			# Example of $onefile: letter_1248171_90604199_1247467_20170102_130928.pdf
			
			if (substr(strtolower($onefile), -4, 4) == '.pdf')
				$pdfs[] = $onefile;
		}
		#dprint("Files in $onedir: " . print_r($vilno_files,1), true);
		if ($pdfs)
		{
			#dprint("PDFs in $onedir: " . print_r($pdfs,1), true);
			#$stats = array();
			#$purges = array();
			
			sort($pdfs);
			foreach ($pdfs as $onefile)
			{
				# Example of $onedir: v123456
				# Example of $onefile: letter_1248171_90604199_1247467_20170102_130928.pdf
			
				$fullname = "$csv_dir/$onedir/$onefile";
				$stat = stat($fullname);
				$total_size += intval($stat['size']);
				$total_count++;
				
				$bits = explode('_', $onefile);
				if (count($bits) == 6)
				{
					$date = $bits[4];
					$file_date = mktime(0, 0, 0, intval(substr($date,4,2)), intval(substr($date,6,2)), intval(substr($date,0,4)));
				}
				else
					$file_date = $stat['ctime'];
					#$file_date = filectime($fullname);
				#$stats[$onefile] = array('size' => $stat['size'], 'ctime' => $file_date, 'ctime2' => date_from_epoch(true,$file_date));
				if ($file_date < $threshold_date)
				{
					# PDF file is older than the threshold date.
					#$purges[$onefile] = array('size' => $stat['size'], 'ctime' => $file_date, 'ctime2' => date_from_epoch(true,$file_date));
					$purge_size += intval($stat['size']);
					$purge_count++;
					$deletions[] = $fullname;
				}
			}
			#dprint("Stats in $onedir: " . print_r($stats,1), true);
			#if ($purges)
			#	dprint("Purges in $onedir: " . print_r($purges,1), true);
		}
	} # foreach ($vilno_dirs)

	#dlog("count(\$deletions)=" . count($deletions) . ", \$purge_count=$purge_count", true);
	if ($log_verbose) emlog("count(\$deletions)=" . count($deletions) . ", \$purge_count=$purge_count");
	else emlog("\$purge_count=$purge_count");
	
	#sort($deletions);
	#dlog("The following files will be deleted:{$crlf}<br>" . implode("{$crlf}<br>", $deletions) . "{$crlf}<br>", true);
	if ($do_unlink)
	{
		foreach ($deletions as $onefile)
			unlink($onefile);
	}
	
	$diff_size = $total_size - $purge_size;
	$diff_count = $total_count - $purge_count;
	$total_gb = round($total_size / (1024 * 1024 * 1024), 3);
	$purge_gb = round($purge_size / (1024 * 1024 * 1024), 3);
	$diff_gb = round($diff_size / (1024 * 1024 * 1024), 3);
	#dlog("Total size: {$total_gb}GB ($total_count files, $total_size bytes),{$crlf}<br> " .
	#		"Purge size: {$purge_gb}GB ($purge_count files, $purge_size bytes),{$crlf}<br>" .
	#		"Untouched: {$diff_gb}GB ($diff_count files, $diff_size bytes)", 
	#		true);
	if ($log_verbose) emlog("Total size: {$total_gb}GB ($total_count files, $total_size bytes),{$crlf}" .
			"Purge size: {$purge_gb}GB ($purge_count files, $purge_size bytes),{$crlf}" .
			"Untouched: {$diff_gb}GB ($diff_count files, $diff_size bytes)"
			);
	if ($log_verbose) emlog("The following files have been deleted:{$crlf}" . implode("{$crlf}", $deletions) . "{$crlf}*End*");
	
	#dlog("auto_pdf.php/auto_main() - Exit", true);
	#emlog("auto_pdf.php/auto_main() - Exit");
	
} # auto_main()

function emlog($a)
{
	# This is for printing to the standard output which the server will then email to me as cronjob output,
	# but also writing to the log file.

	global $crlf;

	print $a . $crlf;
	log_write($a);
}

?>
