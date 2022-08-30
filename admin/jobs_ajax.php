<?php

include_once("settings.php");
include_once("library.php");
include_once("lib_pdf.php");
global $cookie_user_id;
global $id_JOB_TYPE_trc;
global $id_JOB_TYPE_mns;
global $id_JOB_TYPE_tm;
global $id_JOB_TYPE_tc;
global $id_JOB_TYPE_rt1;
global $id_JOB_TYPE_rt2;
global $id_JOB_TYPE_rt3;
global $id_JOB_TYPE_svc;
global $id_JOB_TYPE_att;
global $id_JOB_TYPE_rpo;
global $id_JOB_TYPE_etr;
global $id_JOB_TYPE_oth;
global $id_ROUTE_cspent;
global $safe_amp;
global $safe_slash;
global $sqlFalse;
global $sqlTrue;
global $sqlNow;

# Set user_id (id of logged-in user) for auditing
$USER = array('USER_ID' => xprint($_COOKIE[$cookie_user_id], false), 'U_DEBUG' => 0);

$operation = get_val('op');
if (($operation == 'u') || ($operation == 'usu') || ($operation == 'uph') || ($operation == 'uno') || 
	($operation == 'ule') || ($operation == 'upa') || ($operation == 'uact') || ($operation == 'ubi')) # update data in database
{
	$data_type = get_val('ty'); # see jobs.php / javascript() / update_job() for list of data types
	if (($data_type == 'x') || ($data_type == 'd') || ($data_type == 'm') || ($data_type == 'p') || 
		($data_type == 'n') || ($data_type == 't') || ($data_type == 'e') || ($data_type == 'i'))
	{
		$job_id = get_val('job_id', true); # always JOB.JOB_ID
		$id_value = get_val('i', true); # e.g. JOB.JOB_ID or JOB_SUBJECT.JOB_SUBJECT_ID
		if ((0 < $job_id) && (0 < $id_value))
		{
			$field_name = get_val('n');
			if ($field_name)
			{
				sql_connect();
				$abort = '';
				$reload = false;
				$close_job = false;
				
				if ($operation == 'usu')
				{
					$table = 'JOB_SUBJECT';
					$id_name = 'JOB_SUBJECT_ID';
				}
				elseif ($operation == 'uph')
				{
					$table = 'JOB_PHONE';
					$id_name = 'JOB_PHONE_ID';
				}
				elseif ($operation == 'uno')
				{
					$table = 'JOB_NOTE';
					$id_name = 'JOB_NOTE_ID';
				}
				elseif ($operation == 'ule')
				{
					$table = 'JOB_LETTER';
					$id_name = 'JOB_LETTER_ID';
				}
				elseif ($operation == 'upa')
				{
					$table = 'JOB_PAYMENT';
					$id_name = 'JOB_PAYMENT_ID';
				}
				elseif ($operation == 'uact')
				{
					$table = 'JOB_ACT';
					$id_name = 'JOB_ACT_ID';
				}
				elseif ($operation == 'ubi')
				{
					$table = 'INV_BILLING';
					$id_name = 'INV_BILLING_ID';
				}
				else
				{
					$table = 'JOB';
					$id_name = 'JOB_ID';
				}
				
				$field_name = strtoupper($field_name);
				$field_value_raw = trim(str_replace($safe_slash, '/', str_replace($safe_amp, '&', get_val2('v'))));

				if ($table == 'JOB_ACT')
				{
					$len = strlen('JA_DT');
					if (substr($field_name, 0, $len) == 'JA_DT')
					{
						$sql = "SELECT JA_DT FROM JOB_ACT WHERE JOB_ACT_ID=$id_value";
						$ja_dt = '';
						sql_execute($sql);
						while (($newArray = sql_fetch()) != false)
							$ja_dt = $newArray[0];
						
						$bits = explode(' ', $ja_dt);
						if (count($bits) == 2)
						{
							$act_date = $bits[0];
							$act_time = $bits[1];
						}
						else
						{
							$act_date = '';
							$act_time = '';
						}
						
						$new_fn = substr($field_name, $len+1, 1);
						if ($new_fn == 'D')
							$act_date = $field_value_raw;
						elseif ($new_fn == 'T')
							$act_time = $field_value_raw;
						else
						{
							print "Failed to parse JA_DT name \"$field_name\"";
							$abort = true;
						}
						if (!$abort)
						{
							$field_name = 'JA_DT';
							$field_value_raw = trim("{$act_date} {$act_time}");
						}
					}
				}
				
				$group_id = 0; # group id of jobs with given VILNO
				$g_jobs = array(); # list of jobs with given VILNO that are not already in a group
				if (($table == 'JOB') && ($field_name == 'GROUP_ADDITION'))
				{
					# Look for an existing group containing the job(s) named by the given Vilno.
					$vilno = intval($field_value_raw);
					if (0 < $vilno)
					{
						$sql = "SELECT JOB_ID, JOB_GROUP_ID FROM JOB WHERE J_VILNO=$vilno";
						sql_execute($sql);
						while (($newArray = sql_fetch_assoc()) != false)
						{
							if (0 < $newArray['JOB_GROUP_ID'])
								$group_id = $newArray['JOB_GROUP_ID']; # in theory there could be more than one, but keep it simple for now
							else
								$g_jobs[] = $newArray['JOB_ID'];
						}
						if ($group_id || $g_jobs)
						{
							if (!$group_id)
							{
								# Create a new job group
								$sql = "INSERT INTO JOB_GROUP (JG_NAME) VALUES (NULL)";
								audit_setup_gen('JOB_GROUP', 'JOB_GROUP_ID', 0, '', '');
								$group_id = sql_execute($sql, true); # audited
							}
							if (0 < $group_id)
							{
								if ($g_jobs)
								{
									# Put the jobs with the given VILNO into that group
									foreach ($g_jobs as $g_jid)
									{
										$sql = "UPDATE JOB SET JOB_GROUP_ID=$group_id WHERE JOB_ID=$g_jid";
										audit_setup_job($g_jid, 'JOB', 'JOB_ID', $g_jid, 'JOB_GROUP_ID', $group_id);
										sql_execute($sql, true); # audited
									}
								}
								$sql = "UPDATE JOB SET JOB_GROUP_ID=$group_id WHERE JOB_ID=$job_id";
								audit_setup_job($job_id, 'JOB', 'JOB_ID', $job_id, 'JOB_GROUP_ID', $group_id);
								sql_execute($sql, true); # audited
							}
							else
								$abort = "Failed to create a Job Group";
						}
						else
							$abort = "No jobs were found with VILNO \"$vilno\"";
					}
					else
						$abort = "cancel"; # cancel rather than error
				}
				
				$return = 'ok';
				if (!$abort)
				{
					$now = date_now_sql();
					
					if (!$group_id)
					{
						if ($field_value_raw == '__NULL__')
						{
							$field_value_unq = 'NULL';
							$field_value = 'NULL';
						}
						else 
						{
							$field_value_unq = $field_value_raw;
							$force_quotes = false;
							if (($data_type == 'x') || ($data_type == 'd') || ($data_type == 'e') || ($data_type == 'i'))
								$force_quotes = true;
							$field_value = quote_smart($field_value_unq, $force_quotes);
						}

						if (encrypted_field($table, $field_name))
						{
							sql_encryption_preparation($table);
							$field_value = sql_encrypt($field_value, true, $table);
						}
						#print "#$field_value#";#

						if (($table == 'JOB') && ($field_name == 'JT_REPORT_APPR'))
						{
							# Trace Job report: approved or not.
							# $field_value is either 1 or 0 (tickbox) that we need to tranlsate into datetime or null.
							# If approved then check we have a JOB_LETTER record, otherwise check we don't.

							$reload = true;

							# Set JT_REPORT_APPR first
							$appr = ($field_value ? $now : 'NULL');
							$appr_sql  = ($field_value ? "'$now'" : 'NULL');
							$sql = "UPDATE JOB SET JT_REPORT_APPR=$appr_sql WHERE JOB_ID=$job_id";
							audit_setup_job($job_id, 'JOB', 'JOB_ID', $job_id, 'JT_REPORT_APPR', $appr);
							sql_execute($sql, true); # audited

							# Check/alter JOB_LETTER record(s)
							$sql = "SELECT JOB_LETTER_ID FROM JOB_LETTER WHERE JOB_ID=$job_id AND OBSOLETE=$sqlFalse";
							sql_execute($sql);
							$letters = array();
							while (($newArray = sql_fetch()) != false)
								$letters[] = $newArray[0];

							if ($field_value == 1)
							{
								if (count($letters) == 0)
								{
									$sql = "SELECT JT_JOB_TYPE_ID, JT_SUCCESS FROM JOB WHERE JOB_ID=$job_id";
									sql_execute($sql);
									$job_type_id = 0;
									$jt_success = 0;
									while (($newArray = sql_fetch()) != false)
									{
										$job_type_id = $newArray[0];
										$jt_success = $newArray[1];
									}

									$sql = "SELECT LETTER_TYPE_ID FROM LETTER_TYPE_SD 
											WHERE JT_T_JOB_TYPE_ID=$job_type_id AND JT_T_SUCC=$jt_success AND OBSOLETE=$sqlFalse";
									$letter_type_id = 0;
									while (($newArray = sql_fetch()) != false)
										$letter_type_id = $newArray[0];

									$sql = "INSERT INTO JOB_LETTER (JOB_ID, LETTER_TYPE_ID, JL_ADDED_DT, IMPORTED)
															VALUES ($job_id, $letter_type_id, $sqlNow, $sqlFalse)";
									audit_setup_job($job_id, 'JOB_LETTER', 'JOB_LETTER_ID', 0, '', '');
									sql_execute($sql, true); # audited
								}
							}
							else
							{
								if (0 < count($letters))
								{
									foreach ($letters as $jl_id)
									{
										$sql = "UPDATE JOB_LETTER SET OBSOLETE=$sqlTrue WHERE JOB_LETTER_ID=$jl_id";
										audit_setup_job($job_id, 'JOB_LETTER', 'JOB_LETTER_ID', $jl_id, 'OBSOLETE', $sqlTrue);
										sql_execute($sql, true); # audited
										sql_update_letter($job_id, $jl_id);
									}
								}
							}
						}
						elseif (($table == 'JOB_LETTER') && ($field_name == 'JL_APPROVED_DT'))
						{
							if ($field_value_raw == 0)
							{
								# User has "un-approved" the trace job letter
								$sql = "UPDATE $table SET $field_name=NULL WHERE $id_name=$id_value";
								audit_setup_job($job_id, $table, $id_name, $id_value, $field_name, 'NULL');
								sql_execute($sql, true); # audited
							}
						}
						elseif (($table == 'JOB') || ($table == 'JOB_SUBJECT') || ($table == 'JOB_PHONE') || ($table == 'JOB_NOTE') || 
							($table == 'JOB_LETTER') || ($table == 'JOB_PAYMENT') || ($table == 'JOB_ACT') || ($table == 'INV_BILLING'))
						{
							# --- Default Action ---
							
							if (($table == 'JOB') && ($field_name == 'J_TARGET_DT'))
							{
								$bits = explode(' ', trim($field_value_raw));
								if ((strlen($bits[0]) == 10) && ($bits[0][4] == '-') && ($bits[0][7] == '-'))
								{
									$time = str_replace(':', '', str_replace('.', '', $bits[1]));
									if (strlen($time) == 3)
										$time = "0{$time}";
									$time = $time[0] . $time[1] . ':' . $time[2] . $time[3];
									if (strlen($time) == 5)
									{
										$field_value_raw = $bits[0] . ' ' . $time;
										$field_value_unq = $field_value_raw;
										$field_value = quote_smart($field_value_unq, true);
									}
									else
										$abort = "Invalid time";
								}
								else
									$abort = "Invalid date";
							}
							
							$sql = "UPDATE $table SET $field_name=$field_value WHERE $id_name=$id_value";
							#print "*$sql*";#
							audit_setup_job($job_id, $table, $id_name, $id_value, $field_name, $field_value_unq);
							sql_execute($sql, true); # audited
						}
						
						if (  (($table == 'JOB_PAYMENT') && ($field_name == 'COL_AMT_RX')) || 
							  (($table == 'JOB') && ($field_name == 'JC_PAID_SO_FAR'))
							)
							sql_update_paid_so_far($job_id);
						
						if (($table == 'JOB') && ($field_name == 'J_COMPLETE') && ($field_value == 1))
							$close_job = true;
						
						if ($table == 'JOB_SUBJECT')
						{
							if ($field_name == 'JS_ADDR_PC')
							{
								# Feedback #23
								$js_outcode = postcode_outcode($field_value_raw, true);
								$sql = "UPDATE JOB_SUBJECT SET JS_OUTCODE=" . ($js_outcode ? "'$js_outcode'" : 'NULL') . " WHERE JOB_SUBJECT_ID=$id_value";
								audit_setup_job($job_id, 'JOB_SUBJECT', 'JOB_SUBJECT_ID', $id_value, 'JS_OUTCODE', $js_outcode);
								sql_execute($sql, true); # audited
							}
							elseif ($field_name == 'NEW_ADDR_PC')
							{
								# Feedback #23
								$new_outcode = postcode_outcode($field_value_raw, true);
								$sql = "UPDATE JOB_SUBJECT SET NEW_OUTCODE=" . ($new_outcode ? "'$new_outcode'" : 'NULL') . " WHERE JOB_SUBJECT_ID=$id_value";
								audit_setup_job($job_id, 'JOB_SUBJECT', 'JOB_SUBJECT_ID', $id_value, 'NEW_OUTCODE', $new_outcode);
								sql_execute($sql, true); # audited
							}
						}
						
						if (($table == 'JOB') && ($field_name == 'CLIENT2_ID'))
						{
							# Change job's commission percent to that of the new client (Feedback #193).
							$sql = "SELECT COMM_PERCENT FROM CLIENT2 WHERE CLIENT2_ID=$field_value";
							#print "#$sql#";#
							sql_execute($sql);
							$comm_percent = 0.0;
							while (($newArray = sql_fetch()) != false)
								$comm_percent = floatval($newArray[0]);
							
							$sql = "SELECT JC_PERCENT FROM JOB WHERE JOB_ID=$job_id";
							#print "#$sql#";#
							sql_execute($sql);
							$jc_percent = 0.0;
							while (($newArray = sql_fetch()) != false)
								$jc_percent = floatval($newArray[0]);
							
							if ($comm_percent != $jc_percent)
							{
								$sql = "UPDATE JOB SET JC_PERCENT=$comm_percent WHERE JOB_ID=$job_id";
								#print "#$sql#";#
								audit_setup_job($job_id, 'JOB', 'JOB_ID', $job_id, 'JC_PERCENT', $comm_percent);
								sql_execute($sql, true); # audited
							}
						}
						if (($table == 'JOB') && ($field_name == 'J_USER_ID'))
						{
							$sql = "SELECT J_USER_DT, J_AVAILABLE FROM JOB WHERE JOB_ID=$job_id";
							sql_execute($sql);
							$j_user_dt = '';
							$j_available = 0;
							while (($newArray = sql_fetch()) != false)
							{
								$j_user_dt = $newArray[0];
								$j_available = $newArray[1];
							}
							if ($j_available != 1)
							{
								$sql = "UPDATE JOB SET J_AVAILABLE=$sqlTrue WHERE JOB_ID=$job_id";
								#print "*$sql*";#
								audit_setup_job($job_id, 'JOB', 'JOB_ID', $job_id, 'J_AVAILABLE', $sqlTrue);
								sql_execute($sql, true); # audited
							}
							if (!$j_user_dt)
							{
								$sql = "UPDATE JOB SET J_USER_DT='$now' WHERE JOB_ID=$job_id";
								#print "*$sql*";#
								audit_setup_job($job_id, 'JOB', 'JOB_ID', $job_id, 'J_USER_DT', $now);
								sql_execute($sql, true); # audited
								
								$return = "udt|$now";
							}
						}
						
						if (($table == 'JOB') && ($field_name == 'JT_JOB_TYPE_ID'))
						{
							# WRONG: If fees are not set up, copy them from client record
							# RIGHT: Copy fees from the client record whenever the job type is changed
							
							$update_fees = false;
							$update_target = false;
							
							switch ($field_value_raw)
							{
								case $id_JOB_TYPE_trc: $client_y = 'TR_FEE'; $client_n = 'NT_FEE'; break;
								case $id_JOB_TYPE_mns: $client_y = 'MN_FEE'; $client_n = 'NT_FEE'; break;
								case $id_JOB_TYPE_tm:  $client_y = 'TM_FEE'; $client_n = 'NT_FEE'; break;
								case $id_JOB_TYPE_tc:  $client_y = 'TC_FEE'; $client_n = 'NT_FEE'; break;
								case $id_JOB_TYPE_rt1: $client_y = '';       $client_n = 'NT_FEE'; break;
								case $id_JOB_TYPE_rt2: $client_y = '';       $client_n = 'NT_FEE'; break;
								case $id_JOB_TYPE_rt3: $client_y = '';       $client_n = 'NT_FEE'; break;
								case $id_JOB_TYPE_svc: $client_y = 'SV_FEE'; $client_n = 'NT_FEE'; break;
								case $id_JOB_TYPE_att: $client_y = 'AT_FEE'; $client_n = 'NT_FEE'; break;
								case $id_JOB_TYPE_rpo: $client_y = 'RP_FEE'; $client_n = 'NT_FEE'; break;
								case $id_JOB_TYPE_etr: $client_y = 'ET_FEE'; $client_n = 'NT_FEE'; break;
								case $id_JOB_TYPE_oth: $client_y = '';       $client_n = 'NT_FEE'; break;
								default:               $client_y = 'TR_FEE'; $client_n = 'NT_FEE'; break;
								
							}
							$sql = "SELECT J.JT_FEE_Y, J.JT_FEE_N, " . ($client_y ? "C.{$client_y}" : "0.0") . ", C.{$client_n}
									FROM JOB AS J INNER JOIN CLIENT2 AS C ON C.CLIENT2_ID=J.CLIENT2_ID
									WHERE J.JOB_ID=$job_id";
							#print "*$field_value_raw*$sql*";#
							sql_execute($sql);
							#$fee_y = 0.0;
							#$fee_n = 0.0;
							$cf_y = 0.0;
							$cf_n = 0.0;
							while (($newArray = sql_fetch()) != false)
							{
								#$fee_y = 1.0 * $newArray[0];
								#$fee_n = 1.0 * $newArray[1];
								$cf_y = floatval($newArray[2]);
								$cf_n = floatval($newArray[3]);
							}
							#if (($fee_y == 0) && ($fee_n == 0))
							#{
								$sql = "UPDATE JOB SET JT_FEE_Y=$cf_y WHERE JOB_ID=$job_id";
								#print "*$sql*";#
								audit_setup_job($job_id, 'JOB', 'JOB_ID', $job_id, 'JT_FEE_Y', $cf_y);
								sql_execute($sql, true); # audited
								
								$sql = "UPDATE JOB SET JT_FEE_N=$cf_n WHERE JOB_ID=$job_id";
								#print "*$sql*";#
								audit_setup_job($job_id, 'JOB', 'JOB_ID', $job_id, 'JT_FEE_N', $cf_n);
								sql_execute($sql, true); # audited
								
								$update_fees = true;
							#}
							
							# If target date is not set, get days from job type table
							$j_target_dt = sql_select_single("SELECT J_TARGET_DT FROM JOB WHERE JOB_ID=$job_id");
							if (!$j_target_dt)
							{
								$j_opened_dt = sql_select_single("SELECT J_OPENED_DT FROM JOB WHERE JOB_ID=$job_id");
								if (!$j_opened_dt)
									$j_opened_dt = date_now_sql();
								$days = sql_select_single("SELECT JT_DAYS FROM JOB_TYPE_SD WHERE JOB_TYPE_ID=$field_value");
								$ep = date_to_epoch($j_opened_dt) + ($days * 24 * 60 * 60);
								$j_target_dt = date_from_epoch(false, $ep, false, false, true);
								
								$sql = "UPDATE JOB SET J_TARGET_DT='$j_target_dt' WHERE JOB_ID=$job_id";
								#print "*$sql*";#
								audit_setup_job($job_id, 'JOB', 'JOB_ID', $job_id, 'J_TARGET_DT', $j_target_dt);
								sql_execute($sql, true); # audited
								
								$update_target = true;
								$j_target_dt = date_for_sql($j_target_dt, true, true, true, false, false, false, false, true);
								#print "*$j_target_dt*";#
							}
							
							if ($update_fees && $update_target)
								$return = "feesandtarget|" . number_format($cf_y,2) . "|" . number_format($cf_n,2) . "|" . $j_target_dt;
							elseif ($update_fees)
								$return = "fees|" . number_format($cf_y,2) . "|" . number_format($cf_n,2);
							elseif ($update_target)
								$return = "target|$j_target_dt";
						} # if (($table == 'JOB') && ($field_name == 'JT_JOB_TYPE_ID'))
						
						elseif (($table == 'JOB') && ($field_name == 'JT_JOB_TARGET_ID') && (0 < $field_value))
						{
							# User has specified target time in hours, so reset target date
							$j_opened_dt = sql_select_single("SELECT J_OPENED_DT FROM JOB WHERE JOB_ID=$job_id");
							if (!$j_opened_dt)
								$j_opened_dt = date_now_sql();
							$hours = sql_select_single("SELECT JTA_TIME FROM JOB_TARGET_SD WHERE JOB_TARGET_ID=$field_value");
							if (0 < $hours)
							{
								$ep = date_to_epoch($j_opened_dt) + ($hours * 60 * 60);
								$j_target_dt = date_from_epoch(false, $ep, false, false, true);

								$sql = "UPDATE JOB SET J_TARGET_DT='$j_target_dt' WHERE JOB_ID=$job_id";
								#print "*$sql*";#
								audit_setup_job($job_id, 'JOB', 'JOB_ID', $job_id, 'J_TARGET_DT', $j_target_dt);
								sql_execute($sql, true); # audited

								$update_target = true;
								$j_target_dt = date_for_sql($j_target_dt, true, true, true, false, false, false, false, true);

								$client2_id = sql_select_single("SELECT CLIENT2_ID FROM JOB WHERE JOB_ID=$job_id");
								$fee = sql_select_single("SELECT CT_FEE FROM CLIENT_TARGET_LINK WHERE CLIENT2_ID=$client2_id AND JOB_TARGET_ID=$field_value");
								$sql = "UPDATE JOB SET JT_FEE_Y=$fee WHERE JOB_ID=$job_id";
								audit_setup_job($job_id, 'JOB', 'JOB_ID', $job_id, 'JT_FEE_Y', $fee);
								sql_execute($sql, true); # audited
								
								$return = "feesandtarget|" . number_format($fee,2) . "|" . number_format(0.0,2) . "|" . $j_target_dt;
							}							
						} # elseif (($table == 'JOB') && ($field_name == 'JT_JOB_TARGET_ID'))

//						elseif (($table == 'JOB') && ($field_name == 'J_TURN_H') && (0 < $field_value))
//						{
//							# User has specified turn-around time in hours, so reset target date
//							$j_opened_dt = sql_select_single("SELECT J_OPENED_DT FROM JOB WHERE JOB_ID=$job_id");
//							if (!$j_opened_dt)
//								$j_opened_dt = date_now_sql();
//							$ep = date_to_epoch($j_opened_dt) + ($field_value * 60 * 60);
//							$j_target_dt = date_from_epoch(false, $ep, false, false, true);
//
//							$sql = "UPDATE JOB SET J_TARGET_DT='$j_target_dt' WHERE JOB_ID=$job_id";
//							#print "*$sql*";#
//							audit_setup_job($job_id, 'JOB', 'JOB_ID', $job_id, 'J_TARGET_DT', $j_target_dt);
//							sql_execute($sql, true); # audited
//
//							$update_target = true;
//							$j_target_dt = date_for_sql($j_target_dt, true, true, true, false, false, false, false, true);
//							$return = "target|$j_target_dt";
//							
//						} # elseif (($table == 'JOB') && ($field_name == 'J_TURN_H'))
					} # if (!$group_id)
					
					sql_update_job($job_id);
					
					foreach ($g_jobs as $g_jid)
						sql_update_job($g_jid);
					
					if (($field_name == 'JC_INSTAL_AMT') || ($field_name == 'JC_INSTAL_DT_1') || ($field_name == 'JC_INSTAL_FREQ') || 
						($field_name == 'JC_PAYMENT_METHOD_ID'))
					{
						# Also update the most recent JOB_ARRANGE record for this job, which is a copy of the JOB record details
						$sql = "SELECT MAX(JOB_ARRANGE_ID) FROM JOB_ARRANGE WHERE JOB_ID=$job_id";
						$job_arrange_id = 0;
						sql_execute($sql);
						while (($newArray = sql_fetch()) != false)
							$job_arrange_id = $newArray[0];
						if (0 < $job_arrange_id)
						{
							$fname = str_replace('JC_', 'JA_', $field_name);
							$sql = "UPDATE JOB_ARRANGE SET $fname=$field_value WHERE JOB_ARRANGE_ID=$job_arrange_id";
							#print "*$sql*";#
							audit_setup_job($job_id, 'JOB_ARRANGE', 'JOB_ARRANGE_ID', $job_arrange_id, $fname, $field_value_unq);
							sql_execute($sql, true); # audited
						}
					}
					
					if ($table == 'JOB_NOTE')
					{
						$sql = "UPDATE JOB_NOTE SET JN_UPDATED_ID={$USER['USER_ID']} WHERE JOB_NOTE_ID=$id_value";
						#print "*$sql*";#
						audit_setup_job($job_id, 'JOB_NOTE', 'JOB_NOTE_ID', $id_value, 'JN_UPDATED_ID', $USER['USER_ID']);
						sql_execute($sql, true); # audited
						
						$sql = "UPDATE JOB_NOTE SET JN_UPDATED_DT='$now' WHERE JOB_NOTE_ID=$id_value";
						#print "*$sql*";#
						audit_setup_job($job_id, 'JOB_NOTE', 'JOB_NOTE_ID', $id_value, 'JN_UPDATED_DT', $now);
						sql_execute($sql, true); # audited
					}
					
					if ($table == 'JOB_LETTER')
					{
						$sql = "UPDATE JOB_LETTER SET JL_UPDATED_ID={$USER['USER_ID']} WHERE JOB_LETTER_ID=$id_value";
						#print "*$sql*";#
						audit_setup_job($job_id, 'JOB_LETTER', 'JOB_LETTER_ID', $id_value, 'JL_UPDATED_ID', $USER['USER_ID']);
						sql_execute($sql, true); # audited
						
						sql_update_letter($job_id, $id_value);
					}
					
					if ($table == 'JOB_PAYMENT')
					{
						sql_update_adjustment($job_id);
						if ($field_name == 'COL_PERCENT')
						{
							$sql = "SELECT COL_AMT_RX FROM JOB_PAYMENT WHERE JOB_PAYMENT_ID=$id_value";
							$amt = 0.0;
							sql_execute($sql);
							while (($newArray = sql_fetch()) != false)
								$amt = floatval($newArray[0]);
							$pc = floatval($field_value);
							$com = money_format_kdb(0.01 * floatval($amt) * $pc, true, true, true);
							$return = $com;
						}
						if (($field_name == 'COL_PAYMENT_ROUTE_ID') && ($field_value == $id_ROUTE_cspent))
						{
							$sql = "UPDATE JOB_PAYMENT SET COL_PERCENT=0.0 WHERE JOB_PAYMENT_ID=$id_value";
							#print "*$sql*";#
							audit_setup_job($job_id, 'JOB_PAYMENT', 'JOB_PAYMENT_ID', $id_value, 'COL_PERCENT', 0.0);
							sql_execute($sql, true); # audited
						}
					}
				}

				if ($abort && ($abort != 'cancel'))
					print "-1|$abort";
				elseif ($reload)
					print "2|$return";
				elseif ($close_job)
					print "3|$return";
				else
					print "1|$return";
				sql_disconnect();
			}
			else 
				print "-1|jobs_ajax.php: no field name specified";
		}
		else 
			print "-1|jobs_ajax.php: bad job/obj id (job=$job_id, obj=$id_value)";
	} # if ($data_type...)
	else 
		print "-1|jobs_ajax.php: bad data type";
}
else if ($operation == 'sub_lkp')
{
	# Find matching subjects using the first and last names
	$id_value = get_val('i', true); # JOB_SUBJECT.JOB_SUBJECT_ID
	$firstname = trim(str_replace($safe_slash, '/', str_replace($safe_amp, '&', get_val2('fn'))));
	$lastname = trim(str_replace($safe_slash, '/', str_replace($safe_amp, '&', get_val2('ln'))));
	if ($id_value && (3 <= strlen($lastname)))
	{
		sql_connect();
		sql_encryption_preparation('JOB_SUBJECT');
		$sql = "SELECT JS.JOB_SUBJECT_ID, JS.JS_TITLE, " . sql_decrypt('JS.JS_FIRSTNAME', '', true) . ", " . sql_decrypt('JS.JS_LASTNAME', '', true) . ",
					JO.JOB_ID, JO.J_VILNO
				FROM JOB_SUBJECT AS JS INNER JOIN JOB AS JO ON JO.JOB_ID=JS.JOB_ID
				WHERE (JS.JOB_SUBJECT_ID<>$id_value) AND (" . sql_decrypt('JS.JS_LASTNAME') . "='" . addslashes_kdb($lastname) . "')";
		if ($firstname)
			$sql .= " AND (" . sql_decrypt('JS.JS_FIRSTNAME') . "='" . addslashes_kdb($firstname) . "')";
		sql_execute($sql);
		while (($newArray = sql_fetch_assoc()) != false)
		{
			$name = $newArray['JS_TITLE'];
			if ($newArray['JS_FIRSTNAME'])
				$name = ($name ? "$name " : '') . $newArray['JS_FIRSTNAME'];
			if ($newArray['JS_LASTNAME'])
				$name = ($name ? "$name " : '') . $newArray['JS_LASTNAME'];
			print "<span id=\"match_s_{$newArray['JOB_SUBJECT_ID']}\" style=\"cursor:pointer\" " .
						"onclick=\"goto_job({$newArray['JOB_ID']},{$newArray['J_VILNO']})\">" .
						"$name (Job {$newArray['J_VILNO']})</span><br>";
		}
		sql_disconnect();
	}
	else
		print '';
}
elseif ($operation == 'print')
{
	$job_letter_ids = get_val('i');
	if ($job_letter_ids)
	{
		sql_connect();
		$job_letter_ids = explode(',', $job_letter_ids);
		$pdf_files = array();
		$no_vilno = array();
		$no_pdfs = array();
		foreach ($job_letter_ids as $job_letter_id)
		{
			$sql = "SELECT J_VILNO, J_SEQUENCE
					FROM JOB AS J INNER JOIN JOB_LETTER AS L ON L.JOB_ID=J.JOB_ID AND L.JOB_LETTER_ID=$job_letter_id";
			sql_execute($sql);
			$j_vilno = -1;
			$j_sequence = -1;
			while (($newArray = sql_fetch()) != false)
			{
				$j_vilno = $newArray[0];
				$j_sequence = $newArray[1];
			}
			if ((0 <= $j_vilno) && (0 < $j_sequence))
			{
				# Example $pdf_f: v1512278/letter_1512278_90868332_1928967_20161216_133733.pdf
				$pdf_f = str_replace("$csv_dir/", '', 
										pdf_link('jl', "v{$j_vilno}", "{$j_vilno}_{$j_sequence}_{$job_letter_id}", true)); # relative to httpdocs/admin
				#print $pdf_f;
				$pdf_files[] = $pdf_f;
				
				## Get a link to HTML file instead of PHP
				#$htm_file = pdf_link('jl', "v{$j_vilno}", "{$j_vilno}_{$j_sequence}_{$job_letter_id}", true, true);
				##print "== $htm_file ==";#
			}
			else
				$no_vilno[] = $job_letter_id;
		}
		
		#REVIEW How do we print 500 files?
		if (global_debug())
		{
		#print "Printing: $csv_dir/" . implode(", $csv_dir/", $pdf_files) . ". ";
		
		# /usr/bin/pdftk myfile1.pdf myfile2.pdf myfile3.pdf output newfile.pdf
		# Real example:
		# /usr/bin/pdftk csvex/v1512559/letter_1512559_90868613_3696542_20161212_154816.pdf csvex/v1511930/letter_1511930_90867984_3696557_20161221_142231.pdf output csvex/pdftk_20161221_172351.pdf
		$input_files = "$csv_dir/" . implode(" $csv_dir/", $pdf_files);
		$output_file = "$csv_dir/pdftk_" . strftime('%Y%m%d_%H%M%S') . ".pdf";
		$pdftk = "/usr/bin/pdftk $input_files output $output_file";
		#print "Calling exec($pdftk). ";
		$exec_output = array();
		$exec_return = exec($pdftk, $exec_output);
		#print "exec_return=\"$exec_return\", exec_output=" . print_r($exec_output,1) . ". ";
		#REVIEW We need auto_letter.php to delete pdftk output files that are older than 7 days.
		}
		
		#print "ok";
		print "Work in progress...";
		sql_disconnect();
	}
	else
		print("jobs_ajax: no letter IDs to print");
}
else 
	print "-1|jobs_ajax.php: operation \"$operation\" unrecognised";

?>
