<?php

include_once("settings.php");
include_once("library.php");
global $cookie_user_id;

# Set user_id (id of logged-in user) for auditing
$USER = array('USER_ID' => xprint($_COOKIE[$cookie_user_id],false), 'U_DEBUG' => 0);

$operation = get_val('op');
if ($operation == 'fn') # find report name (reports_custom.php)
{
	$rep_name = get_val('n');
	$report_id = get_val('i', true);
	sql_connect();
	$sql = "SELECT COUNT(*) FROM REPORT WHERE REP_NAME=" . quote_smart($rep_name, true);
	if (0 < $report_id)
		$sql .= " AND REPORT_ID <> $report_id";
	$count = 0;
	sql_execute($sql);
	while (($newArray = sql_fetch()) != false)
		$count = $newArray[0];
	sql_disconnect();
	print "1|$count";
}
elseif ($operation == 'ur') # update report (reports_custom.php)
{
	$edit_report_id = get_val('i', true);
	if ($edit_report_id > 0)
	{
		$field_name = get_val('n');
		if ($field_name)
		{
			sql_connect();
			
			$field_name = strtoupper($field_name);
			$field_value_unq = get_val2('v');
			if (($field_name == 'REP_USER_ID') && ($field_value_unq == 0))
				$field_value_unq = '__NULL__';
			if ($field_value_unq == '__NULL__')
			{
				$field_value_unq = 'NULL';
				$field_value = 'NULL';
			}
			else 
				$field_value = quote_smart($field_value_unq, true);
			
			if ($field_name == 'REP_NAME')
			{
				# This field is unique in the table
				$sql = "SELECT REPORT_ID, REP_TEMP FROM REPORT WHERE REP_NAME=$field_value";
				sql_execute($sql);
				$this_id = $edit_report_id;
				$this_temp = -1;
				$other_id = -1;
				$other_temp = -1;
				while (($newArray = sql_fetch_assoc()) != false)
				{
					if ($newArray['REPORT_ID'] == $edit_report_id)
						$this_temp = $newArray['REP_TEMP'];
					else
					{
						$other_id = $newArray['REPORT_ID'];
						$other_temp = $newArray['REP_TEMP'];
					}
				}
				if ($other_temp == 1)
				{
					# Change the name of the other temporary report
					$sql = "UPDATE REPORT SET REP_NAME='TEMP ID $other_id' WHERE REPORT_ID=$other_id";
					sql_execute($sql); # no need to audit
				}
				elseif (0 < $other_id)
				{
					# Reject this name
					print "Sorry, that report name already exists";
					return;
				}
			}
			
			#print "#$field_value#";#
			$sql = "UPDATE REPORT SET $field_name=$field_value WHERE REPORT_ID=$edit_report_id";
			#print "*$sql*";#
			audit_setup_gen($edit_report_id, 'REPORT', 'REPORT_ID', $edit_report_id, $field_name, $field_value_unq);
			sql_execute($sql, true); # audited
			if (($field_name == 'REP_ANALYSIS') || ($field_name == 'REP_COLLECT'))
			{
				# Either: we are switching from an Analysis report to a Job Detail report (or vice-versa);
				# Or: we are switching from a Trace report to a Collection report (or vice-versa).
				# The linked fields will now be invalid, so remove them.
				$sql = "DELETE FROM REPORT_FIELD_LINK WHERE REPORT_ID=$edit_report_id";
				sql_execute($sql); # no need to audit
				print "do_refresh";
			}
			else
				print "ok";
			
			sql_disconnect();
		}
		else 
			print "reports_ajax.php: no field name specified";
	}
	else 
		print "reports_ajax.php: bad edit report id";
}
else 
	print "-1|reports_ajax.php: operation \"$operation\" unrecognised";

?>
