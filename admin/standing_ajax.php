<?php

include_once("settings.php");
include_once("library.php");
global $cookie_user_id;
global $id_USER_ROLE_none;
global $safe_amp;
global $safe_slash;

# Set user_id (id of logged-in user) for auditing
$USER = array('USER_ID' => xprint($_COOKIE[$cookie_user_id],false), 'U_DEBUG' => 0);

$operation = get_val('op');
if ($operation == 'u') # update data in database
{
	$table = '';
	$idname = '';
	$item_type = get_val('t'); # e.g. 'activity' for Activity Codes
	switch ($item_type)
	{
		case 'activity': 
			$table = 'ACTIVITY_SD';
			$idname = 'ACTIVITY_ID';
			break;
		case 'adjust': 
			$table = 'ADJUSTMENT_SD';
			$idname = 'ADJUSTMENT_ID';
			break;
		case 'client_group': 
			$table = 'CLIENT_GROUP';
			$idname = 'CLIENT_GROUP_ID';
			break;
		case 'job_status': 
			$table = 'JOB_STATUS_SD';
			$idname = 'JOB_STATUS_ID';
			break;
		case 'job_target': 
			$table = 'JOB_TARGET_SD';
			$idname = 'JOB_TARGET_ID';
			break;
		case 'job_type': 
			$table = 'JOB_TYPE_SD';
			$idname = 'JOB_TYPE_ID';
			break;
		case 'letter': 
			$table = 'LETTER_TYPE_SD';
			$idname = 'LETTER_TYPE_ID';
			break;
		case 'letter_sequence': 
			$table = 'LETTER_SEQ';
			$idname = 'LETTER_SEQ_ID';
			break;
		case 'pay_meth': 
			$table = 'PAYMENT_METHOD_SD';
			$idname = 'PAYMENT_METHOD_ID';
			break;
		case 'pay_route': 
			$table = 'PAYMENT_ROUTE_SD';
			$idname = 'PAYMENT_ROUTE_ID';
			break;
		case 'rep_field': 
			$table = 'REPORT_FIELD_SD';
			$idname = 'REPORT_FIELD_ID';
			break;
		case 'misc_info': 
			$table = 'MISC_INFO';
			$idname = 'MISC_INFO_ID';
			break;
		case 'user_role': 
			$table = 'USER_ROLE_SD';
			$idname = 'USER_ROLE_ID';
			break;
		default:
			break;
	}
	if ($table)
	{
		$edit_item_id = get_val('i', true);
		if ($edit_item_id > 0)
		{
			$field_name = get_val('n');
			if ($field_name)
			{
				sql_connect();
				$abort = '';

				$field_name = strtoupper($field_name);
				$field_value_raw = trim(str_replace($safe_slash, '/', str_replace($safe_amp, '&', get_val2('v'))));
				
				switch ($table)
				{
					case 'ACTIVITY_SD':
						if (($field_name == 'ACT_TDX') && ($field_value_raw == ''))
							$abort = "Please enter an Activity Code";
						break;
					case 'ADJUSTMENT_SD':
						if (($field_name == 'ADJUSTMENT') && ($field_value_raw == ''))
							$abort = "Please enter an Adjustment Reason";
						break;
					case 'CLIENT_GROUP':
						if (($field_name == 'GROUP_NAME') && ($field_value_raw == ''))
							$abort = "Please enter a Group Name";
						break;
					case 'JOB_STATUS_SD': 
						if (($field_name == 'J_STATUS') && ($field_value_raw == ''))
							$abort = "Please enter a Status Code";
						break;
					case 'JOB_TARGET_SD': 
						if ($field_name == 'JTA_NAME')
						{
							if ($field_value_raw == '')
								$abort = "Please enter a Target Name";
						}
						elseif ($field_name == 'JTA_TIME')
						{
							$temp = intval($field_value_raw);
							if ($temp <= 0)
								$abort = "Please enter a Time";
						}
						elseif ($field_name == 'JOB_TYPE_ID')
						{
							$temp = intval($field_value_raw);
							if ($temp <= 0)
								$abort = "Please enter a Job Type";
						}
						elseif ($field_name == 'JTA_FEE')
						{
							$temp = intval($field_value_raw);
							if ($temp < 0)
								$abort = "Please enter a Fee";
						}
						break;
					case 'JOB_TYPE_SD': 
						if ($field_name == 'JT_TYPE')
						{
							if ($field_value_raw == '')
								$abort = "Please enter a Job Type";
						}
						elseif ($field_name == 'JT_CODE')
						{
							if ($field_value_raw == '')
								$abort = "Please enter a Job Code";
						}
						elseif ($field_name == 'JT_FEE')
						{
							$temp = intval($field_value_raw);
							if ($temp < 0)
								$abort = "Please enter a Fee";
						}
						break;
					case 'LETTER_TYPE_SD': 
						if ($field_value_raw == '')
							$abort = "Please enter some text";
						break;
					case 'PAYMENT_METHOD_SD': 
						if ($field_name == 'PAYMENT_METHOD')
						{
							if ($field_value_raw == '')
								$abort = "Please enter a Payment Method";
						}
						break;
					case 'PAYMENT_ROUTE_SD': 
						if ($field_name == 'PAYMENT_ROUTE')
						{
							if ($field_value_raw == '')
								$abort = "Please enter a Payment Route";
						}
						elseif ($field_name == 'PR_CODE')
						{
							$sql = "SELECT COUNT(*) FROM PAYMENT_ROUTE_SD WHERE PR_CODE=" . quote_smart($field_value_raw, true) . " 
																					AND PAYMENT_ROUTE_ID<>$edit_item_id";
							sql_execute($sql);
							$count = 0;
							while (($newArray = sql_fetch()) != false)
								$count = $newArray[0];
							if (0 < $count)
								$abort = "That code is already in use. Please use another.";
						}
						break;
					case 'REPORT_FIELD_SD': 
						if ($field_name == 'RF_CODE')
						{
							if ($field_value_raw == '')
								$abort = "Please enter a Field Code";
						}
						elseif ($field_name == 'RF_DESCR')
						{
							if ($field_value_raw == '')
								$abort = "Please enter a Field Description";
						}
						break;
					case 'USER_ROLE_SD': 
						if ($field_name == 'UR_ROLE')
						{
							if ($field_value_raw == '')
								$abort = "Please enter a Role Name";
						}
						break;
					default:
						break;
				}
				
				if (!$abort)
				{
					if ($field_value_raw == '__NULL__')
					{
						$field_value_unq = 'NULL';
						$field_value = 'NULL';
					}
					else 
					{
						$field_value_unq = $field_value_raw;
						$field_value = quote_smart($field_value_unq, true);
					}

					if (encrypted_field($table, $field_name))
					{
						sql_encryption_preparation($table);
						$field_value = sql_encrypt($field_value, true, $table);
					}
					#print "#$field_value#";#
					$sql = "UPDATE $table SET $field_name=$field_value WHERE $idname=$edit_item_id";
					#print "*$sql*";#
					audit_setup_gen($table, $idname, $edit_item_id, $field_name, $field_value_unq);
					sql_execute($sql, true); # audited
				}

				if ($abort)
					print $abort;
				else
					print "ok";
				sql_disconnect();
			}
			else 
				print "standing_ajax.php: no field name specified";
		}
		else 
			print "standing_ajax.php: bad edit item id";
	}
	else 
		print "standing_ajax.php: bad item type \"$item_type\"";
}
else 
	print "standing_ajax.php: operation \"$operation\" unrecognised";

?>
