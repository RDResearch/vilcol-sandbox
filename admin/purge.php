<?php

/*

Database purging, 12/02/21.
The database is currently 15 GB big, with the AUDIT table taking up 8 GB.
I’m looking at purging some of the largest AUDIT records that are older than 2 years, and setting this up as a weekly task.

*/

global $script_check;
include_once("settings.php");

include_once("library.php");
include_once("lib_users.php");
global $denial_message;
global $navi_1_system;
global $navi_2_sys_purge;
global $unix_path;
global $USER; # set by admin_verify()

# ------------------------------------------------------------------------------

error_reporting(E_ALL);

$time_start = time();
$time_threshold = 110 * 60; # 110 minutes

$log_root = '';
#log_open("{$log_root}purge_" . strftime('%Y_%m_%d_%H%M') . ".log");
#log_write(post_values() . chr(13) . chr(10) . "GET=" . print_r($_GET,1));
dlog("time_start=$time_start(" . date_from_epoch(false,$time_start) . ")");
#log_write("memory_limit=" . ini_get('memory_limit'));

sql_connect();

admin_verify(); # writes to $USER

if ($USER['IS_ENABLED'])
{
	if (global_debug())
	{
		$navi_1_system = true; # settings.php; used by navi_1_heading()
		$navi_2_sys_purge = true; # settings.php; used by navi_2_heading()
		$onload = "onload=\"set_scroll();\"";
		$page_title_2 = 'Purge - Vilcol';
		screen_layout();
	}
	else
		print "<p>$denial_message</p>";
}
else
	print "<p>" . server_php_self() . ": login is not enabled</p>";

sql_disconnect();
log_close();

# ------------------------------------------------------------------------------

function screen_content()
{
	global $page_title_2;
	
	print "<h3>System Administration</h3>";
	navi_2_heading(); # secondary navigation buttons
	javascript();
	print "
	<h3>Purge Database</h3>
	";
	dprint(post_values());

	set_time_limit(60 * 60 * 1); # 1 hour

	# We have to do this in batches of year, and each year in batches of 10,000, to avoid running out of memory.
	
	$qualifiers['limit'] = 0;
	$grand_total_count = 0;
	$grand_total_size = 0;
	$first_loop = true;
	$this_year = intval(strftime("%Y")); # e.g.2021
	#$just_creations = "AND ((OLD_VAL IS NULL) OR (" . sql_decrypt('OLD_VAL') . " = '')) AND (NEW_VAL IS NOT NULL)";
	
	for ($year = 2016; $year <= ($this_year - 2); $year++)
	{
		$qualifiers['date_from'] = ($first_loop ? "01/01/1970" : "01/01/{$year}");
		$qualifiers['date_to'] = "31/12/{$year}";
		
		print "<p>Searching AUDIT table for changes to table JOB_LETTER, field JL_TEXT, 
						from {$qualifiers['date_from']} to {$qualifiers['date_to']}
						" . ($qualifiers['limit'] ? ", limited to {$qualifiers['limit']} records" : '') . "
						...</p>";

		$sql = "SELECT AUDIT_ID
				FROM AUDIT 
				WHERE (" . date_for_sql($qualifiers['date_from'], false) . " <= CHANGE_DT)
					AND (CHANGE_DT < " . date_for_sql($qualifiers['date_to'], false, false, false, true) . ") 
					AND (TABLE_NAME='JOB_LETTER') AND (FIELD_NAME='JL_TEXT')
				";
				# . $just_creations
		#dprint($sql);#
		sql_execute($sql);
		$ids = array();
		while (($newArray = sql_fetch_assoc()) != false)
			$ids[] = $newArray['AUDIT_ID'];
		$year_count = count($ids);
		print "<p>. . . . . found " . number_with_commas($year_count) . " AUDIT records.</p>";

		$increment = 10000;
		$total_sub_counts = 0;
		$total_size = 0;
		for ($ix = 0; $ix < $year_count; $ix += $increment)
		{
			$sub_ids = array();
			for ($i2 = 0; ($i2 < $increment) && (($ix + $i2) < $year_count); $i2++)
			{
				$sub_ids[] = $ids[$ix + $i2];
			}
			$sql = "SELECT " . sql_decrypt('OLD_VAL','',true) . ", " . sql_decrypt('NEW_VAL','',true) . "
					FROM AUDIT 
					WHERE AUDIT_ID IN (" . implode(',', $sub_ids) . ")";
			#dprint($sql);#
			sql_execute($sql);
			$sub_count = 0;
			$size = 0;
			while (($newArray = sql_fetch_assoc()) != false)
			{
				$sub_count++;
				$size += 58; # approx size of all fields in record apart from OLD_VAL and NEW_VAL.
				$size += strlen($newArray['OLD_VAL']);
				$size += strlen($newArray['NEW_VAL']);
			}
			$total_sub_counts += $sub_count;
			$total_size += $size;
			print "<p>. . . . . found $sub_count AUDIT records. Size: " . number_with_commas($size) . ". Done $total_sub_counts / $year_count records.</p>";
		}
		$ids = '';
		$grand_total_count += $total_sub_counts;
		$grand_total_size += $total_size;
		print "<h4>Found $total_sub_counts records, size " . number_with_commas($total_size) . " bytes, from {$qualifiers['date_from']} to {$qualifiers['date_to']}.</h4>";
		$first_loop = false;
	} # for ($year)
	print "<h2>Grand total: " . number_with_commas($grand_total_count) . " records, size " . number_with_commas($grand_total_size) . " bytes.</h2>";
	
	print "
	<script type=\"text/javascript\">
	document.getElementById('page_title').innerHTML = '$page_title_2';
	</script>
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

	</script>
	";
}

?>
