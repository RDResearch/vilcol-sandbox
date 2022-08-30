<?php

include_once("settings.php");
include_once("library.php");
global $cookie_user_id;
global $safe_amp;
global $safe_slash;
global $sqlFalse;
global $sqlNow;

# Set user_id (id of logged-in user) for auditing
$USER = array('USER_ID' => xprint($_COOKIE[$cookie_user_id], false), 'U_DEBUG' => 0);

$operation = get_val('op');
if (($operation == 'u') || ($operation == 'ucc') || ($operation == 'uph') || ($operation == 'unt') || ($operation == 'uta')) # update data in database
{
	$data_type = get_val('ty'); # see clients.php / javascript() / update_client() for list of data types
	if (($data_type == 'x') || ($data_type == 'd') || ($data_type == 'm') || ($data_type == 'p') || 
		($data_type == 'n') || ($data_type == 't') || ($data_type == 'e'))
	{
		$id_value = get_val('i', true); # e.g. CLIENT2.CLIENT2_ID
		if (0 < $id_value)
		{
			$field_name = get_val('n');
			if ($field_name)
			{
				if (substr($field_name, 0, strlen('ltrtype_')) == 'ltrtype_')
				{
					# LETTER_TYPE turned on or off for CLIENT_LETTER_LINK. E.g. $operation = ltrtype_12_y
					$ltrtype = true;
					$bits = explode('_', $field_name);
					$letter_type_id = intval($bits[1]); # e.g. 12
					$ltr_yn = $bits[2]; # e.g. y
				}
				else
					$ltrtype = false;
				#print "*ltrtype=$ltrtype*";#

				sql_connect();
				$abort = '';
				$client2_id = 0;
				$job_target_id = 0;
				#$target_error = '';
				$fees_returned = '';
				
				if ($ltrtype)
				{
					$table = 'CLIENT_LETTER_LINK';
					$id_name = 'CLIENT_LETTER_LINK_ID';
					$client2_id = $id_value;
				}
				elseif ($operation == 'ucc')
				{
					$table = 'CLIENT_CONTACT';
					$id_name = 'CLIENT_CONTACT_ID';
					$sql = "SELECT CLIENT2_ID FROM CLIENT_CONTACT WHERE CLIENT_CONTACT_ID=$id_value";
					sql_execute($sql);
					while (($newArray = sql_fetch()) != false)
						$client2_id = $newArray[0];
				}
				elseif ($operation == 'uph')
				{
					$table = 'CLIENT_CONTACT_PHONE';
					$id_name = 'CLIENT_CONTACT_PHONE_ID';
					$sql = "SELECT CC.CLIENT2_ID FROM CLIENT_CONTACT AS CC 
							INNER JOIN CLIENT_CONTACT_PHONE AS PH ON PH.CLIENT_CONTACT_ID=CC.CLIENT_CONTACT_ID
							WHERE PH.CLIENT_CONTACT_PHONE_ID=$id_value";
					sql_execute($sql);
					while (($newArray = sql_fetch()) != false)
						$client2_id = $newArray[0];
				}
				elseif ($operation == 'unt')
				{
					$table = 'CLIENT_NOTE';
					$id_name = 'CLIENT_NOTE_ID';
					$sql = "SELECT CLIENT2_ID FROM CLIENT_NOTE WHERE CLIENT_NOTE_ID=$id_value";
					sql_execute($sql);
					while (($newArray = sql_fetch()) != false)
						$client2_id = $newArray[0];
				}
				elseif ($operation == 'uta')
				{
					$table = 'CLIENT_TARGET_LINK';
					$id_name = 'CLIENT_TARGET_LINK_ID';
					$client2_id = floor($id_value / 10000);
					$job_target_id = $id_value - ($client2_id * 10000);
				}
				else
				{
					$table = 'CLIENT2';
					$id_name = 'CLIENT2_ID';
					$client2_id = $id_value;
				}
				
				$field_name = strtoupper($field_name);
				$field_value_raw = trim(str_replace($safe_slash, '/', str_replace($safe_amp, '&', get_val2('v'))));

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
						$force_quotes = false;
						if (($data_type == 'x') || ($data_type == 'd') || ($data_type == 'e'))
							$force_quotes = true;
						$field_value = quote_smart($field_value_unq, $force_quotes);
					}

					if (encrypted_field($table, $field_name))
					{
						sql_encryption_preparation($table);
						$field_value = sql_encrypt($field_value, true, $table);
					}
					#print "*$field_value";#
					
					if ($table == 'CLIENT_TARGET_LINK')
					{
						# Link between CLIENT2 and JOB_TARGET_SD
						$sql = "SELECT $id_name FROM $table WHERE CLIENT2_ID=$client2_id AND JOB_TARGET_ID=$job_target_id";
						$count = 0;
						$link_id = 0;
						sql_execute($sql);
						while (($newArray = sql_fetch()) != false)
						{
							$link_id = $newArray[0];
							$count++;
						}
						#print "*$client2_id*$job_target_id*$count*$field_value_raw*";#
						
						if ($field_name == 'CT_FEE_TCK')
						{
							# Tickbox for whether a certain target fee is to be linked (or not) to the client.
							if ($field_value_raw == 1)
							{
								# Ticked
								if ($count <= 0)
								{
									$sql = "SELECT JTA_FEE FROM JOB_TARGET_SD WHERE JOB_TARGET_ID=$job_target_id";
									$fee = 0.0;
									sql_execute($sql);
									while (($newArray = sql_fetch()) != false)
										$fee = floatval($newArray[0]);
									$sql = "INSERT INTO $table (CLIENT2_ID, JOB_TARGET_ID, CT_FEE) VALUES ($client2_id, $job_target_id, $fee)";
									audit_setup_client($client2_id, $table, $id_name, 0, '', '');
									sql_execute($sql, true); # audited
								}
							}
							else
							{
								# Unticked
								if (0 < $count)
								{
									$ids = array();
									$sql = "SELECT $id_name FROM $table WHERE CLIENT2_ID=$client2_id AND JOB_TARGET_ID=$job_target_id";
									sql_execute($sql);
									while (($newArray = sql_fetch()) != false)
										$ids[] = $newArray[0];
									foreach ($ids as $id)
									{
										$sql = "DELETE FROM $table WHERE $id_name=$id";
										audit_setup_client($client2_id, $table, $id_name, $id, '', '');
										sql_execute($sql, true); # audited
									}
								}
							}
						}
						elseif ($field_name == 'CT_FEE')
						{
							# The actual fee amount
							if ($link_id == 0)
							{
								$ct_fee = floatval($field_value);
								$sql = "INSERT INTO $table (CLIENT2_ID, JOB_TARGET_ID, CT_FEE) VALUES ($client2_id, $job_target_id, $ct_fee)";
								audit_setup_client($client2_id, $table, $id_name, 0, '', '');
								sql_execute($sql, true); # audited
							}
							else
							{
								$sql = "UPDATE $table SET $field_name=$field_value WHERE $id_name=$link_id";
								audit_setup_client($client2_id, $table, $id_name, $link_id, $field_name, $field_value);
								sql_execute($sql, true); # audited
							}
							#else
							#	$target_error = "$job_target_id|This target is not linked to the client so the fee amount cannot be saved";
						}
					}
					elseif ($table == 'CLIENT_LETTER_LINK')
					{
						$sql = "SELECT COUNT(*) FROM $table WHERE CLIENT2_ID=$client2_id AND LETTER_TYPE_ID=$letter_type_id";
						#print "*$sql*";#
						$count = 0;
						sql_execute($sql);
						while (($newArray = sql_fetch()) != false)
							$count = $newArray[0];
						#print "*$client2_id*$job_target_id*$count*$field_value_raw*";#
						if (($ltr_yn == 'n') && ($field_value_raw == 1))
						{
							# User has ticked the box in the "Not selected" column, so we need to add this letter type.
							if ($count <= 0)
							{
								$sql = "INSERT INTO $table (CLIENT2_ID, LETTER_TYPE_ID) VALUES ($client2_id, $letter_type_id)";
								audit_setup_client($client2_id, $table, $id_name, 0, '', '');
								sql_execute($sql, true); # audited
							}
						}
						elseif (($ltr_yn == 'y') && ($field_value_raw == 0))
						{
							# User has unticked the box in the "Selected" column, so we need to remove this letter type.
							if (0 < $count)
							{
								$ids = array();
								$sql = "SELECT $id_name FROM $table WHERE CLIENT2_ID=$client2_id AND LETTER_TYPE_ID=$letter_type_id";
								sql_execute($sql);
								while (($newArray = sql_fetch()) != false)
									$ids[] = $newArray[0];
								foreach ($ids as $id)
								{
									$sql = "DELETE FROM $table WHERE $id_name=$id";
									audit_setup_client($client2_id, $table, $id_name, $id, '', '');
									sql_execute($sql, true); # audited
								}
							}
						}
						else
						{
							print "clients_ajax.php: {$operation}: mismatch between ltr_yn ($ltr_yn) and tick value ($field_value_raw)";
							$abort = true;
						}
					}
					else
					{
						if (($table == 'CLIENT2') && ($field_name == 'SALESPERSON_ID'))
						{
							$old_salesperson = '';
							$new_salesperson = '';
							sql_encryption_preparation('CLIENT2');
							$sql = "SELECT U.U_FIRSTNAME, " . sql_decrypt('U.U_LASTNAME', '', true) . ", C.SALESPERSON_TXT
									FROM CLIENT2 AS C LEFT JOIN USERV AS U ON C.SALESPERSON_ID=U.USER_ID
									WHERE C.CLIENT2_id=$client2_id";
							sql_execute($sql);
							while (($newArray = sql_fetch()) != false)
							{
								$old_salesperson = trim("{$newArray[0]} {$newArray[1]}");
								if (!$old_salesperson)
									$old_salesperson= $newArray[2];
								if (!$old_salesperson)
									$old_salesperson= "(no-name)";
							}
							$sql = "SELECT U.U_FIRSTNAME, " . sql_decrypt('U.U_LASTNAME', '', true) . "
									FROM USERV AS U 
									WHERE (U.CLIENT2_ID IS NULL) AND U.USER_ID=$field_value";
							sql_execute($sql);
							while (($newArray = sql_fetch()) != false)
								$new_salesperson = trim("{$newArray[0]} {$newArray[1]}");
						}
						
						# === THIS IS THE MAIN UPDATE BIT ================
						
						$sql = "UPDATE $table SET $field_name=$field_value WHERE $id_name=$id_value";
						#print "*$sql*";#
						audit_setup_client($client2_id, $table, $id_name, $id_value, $field_name, $field_value_unq);
						sql_execute($sql, true); # audited

						if (($table == 'CLIENT2') && in_array($field_name, array('C_CODE', 'C_CO_NAME', 'C_TRACE', 'C_COLLECT', 'C_ARCHIVE')))
						{
							$sql = "UPDATE CLIENT2 SET PORTAL_PUSH=1 WHERE CLIENT2_ID=$id_value";
							audit_setup_client($client2_id, 'CLIENT2', 'CLIENT2_ID', $id_value, 'PORTAL_PUSH', 1);
							sql_execute($sql, true); # audited
						}
					}
					
					$now = date_now_sql();
					#print "*$now*";#
					
					if (($table == 'CLIENT2') && ($field_name == 'SALESPERSON_ID'))
					{
						$sql = "INSERT INTO SALESPERSON (CLIENT2_ID, SP_USER_ID, SP_DT) VALUES ($id_value, $field_value_raw, $sqlNow)";
						audit_setup_client($client2_id, 'SALESPERSON', 'SALESPERSON_ID', 0, '', '');
						sql_execute($sql, true); # audited
						
						sql_encryption_preparation('CLIENT_NOTE');
						$cn_note = "Salesperson changed from " . addslashes_kdb($old_salesperson) . " to " . addslashes_kdb($new_salesperson);
						$sql = "INSERT INTO CLIENT_NOTE (CLIENT2_ID, CN_NOTE, CN_ADDED_ID, CN_ADDED_DT, IMPORTED) VALUES ($client2_id,
								" . sql_encrypt($cn_note, false, 'CLIENT_NOTE') . ", {$USER['USER_ID']}, $sqlNow, $sqlFalse)";
						audit_setup_client($client2_id, 'CLIENT_NOTE', 'CLIENT_NOTE_ID', 0, '', '');
						sql_execute($sql, true); # audited
					}
					elseif (($table == 'CLIENT_NOTE') && ($field_name == 'CN_NOTE'))
					{
						$sql = "UPDATE $table SET CN_UPDATED_ID={$USER['USER_ID']} WHERE $id_name=$id_value";
						#print "*$sql*";#
						audit_setup_client($client2_id, $table, $id_name, $id_value, 'CN_UPDATED_ID', $USER['USER_ID']);
						sql_execute($sql, true); # audited
						
						$sql = "UPDATE $table SET CN_UPDATED_DT='$now' WHERE $id_name=$id_value";
						#print "*$sql*";#
						audit_setup_client($client2_id, $table, $id_name, $id_value, 'CN_UPDATED_DT', $now);
						sql_execute($sql, true); # audited
					}
					elseif (($table == 'CLIENT2') && (($field_name == 'C_TRACE') || ($field_name == 'C_COLLECT')) && ($field_value_unq == 1))
						$fees_returned = check_tc_settings($id_value, $field_name);
					elseif (global_debug() && #REVIEW: very old code waiting to go live
							($table == 'CLIENT2') && ($field_name == 'COMM_PERCENT'))
					{
						$sql = "SELECT JOB_ID FROM JOB WHERE JC_JOB=1 AND CLIENT2_ID=$client2_id AND (COALESCE(JC_PERCENT,0)=0)";
						sql_execute($sql);
						$zero_jobs = array();
						while (($newArray = sql_fetch()) != false)
							$zero_jobs[] = $newArray[0];
						foreach ($zero_jobs as $zero_job_id)
						{
							$sql = "UPDATE JOB SET JC_PERCENT=$field_value WHERE JOB_ID=$zero_job_id";
							#print "*$sql*";#
							audit_setup_job($zero_job_id, 'JOB', 'JOB_ID', $zero_job_id, 'JC_PERCENT', $field_value_unq);
							sql_execute($sql, true); # audited
						}
					}
					
					if (!$abort)
					{
						sql_update_client($client2_id);
//						$sql = "UPDATE CLIENT2 SET UPDATED_DT='$now' WHERE CLIENT2_ID=$client2_id";
//						#print "*$sql*";#
//						audit_setup_client($client2_id, 'CLIENT2', 'CLIENT2_ID', $client2_id, 'UPDATED_DT', $now);
//						sql_execute($sql, true); # audited
					}
				}

				if ($abort)
					print "-1|$abort";
				elseif ($fees_returned)
					print "1|$fees_returned";
				#elseif ($target_error)
				#	print "-2|$target_error";
				else
					print "1|ok";
				sql_disconnect();
			}
			else 
				print "-1|clients_ajax.php: no field name specified";
		}
		else 
			print "-1|clients_ajax.php: bad client/etc_id";
	} # if ($data_type...)
	else 
		print "-1|clients_ajax.php: bad data type";
}
elseif ($operation == 'fc') # find client name from code
{
	$client_code = get_val('c');
	$cat_code = str_replace(',', '', $client_code);
	if (0 < intval($cat_code))
	{
		sql_connect();
		$bits = explode(',', $client_code);
		$client_name = array();
		foreach ($bits as $one_code)
		{
			$one_name = client_name_from_code($one_code);
			if ($one_name)
			{
				if (!in_array($one_name, $client_name))
					$client_name[] = $one_name;
			}
		}
		$client_name = implode(', ', $client_name);
		sql_disconnect();
		if ($client_name)
			print "1|$client_name";
		else
			print "0|clients_ajax.php: found no client name for client code \"" . get_val('c') . "\"";
	}
	else
		print "-1|clients_ajax.php: client code \"" . get_val('c') . "\" must be integer greater than zero";
}
elseif ($operation == 'find') # find one or more clients from code, name or alphacode; each separated by pipe '|', fields separated by '^'
{
	$txt = get_val2('t');
	$return = '';
	
	if ($txt != '')
	{
		sql_connect();
		$where = '';
		$sql_enc_prep_done = false;
		
		# Cater for the user resubmitting the last result: "<code> - <name>"
		$bits = explode(' - ', $txt);
		if (count($bits) == 2)
			$txt = $bits[0];
		
		if (is_numeric_kdb($txt, false, false, false))
		{
			$c_code = intval($txt);
			$where = "C_CODE=$c_code";
		}
		else
		{
			$sc_client_id = (($txt[0] == '*') ? intval(trim(str_replace('*', '', $txt))) : 0);
			if ($sc_client_id > 0)
				$where = "CLIENT2_ID=$sc_client_id";
			else
			{
				sql_encryption_preparation('CLIENT2');
				$sql_enc_prep_done = true;
				$sc_client = addslashes_kdb($txt);
				$sql = "SELECT CLIENT2_ID FROM CLIENT2 WHERE (" . sql_decrypt('C_CO_NAME') . " LIKE '%{$sc_client}%') OR " .
						"(ALPHA_CODE='" . addslashes_kdb($sc_client) . "') AND C_ARCHIVED=$sqlFalse";
				#dprint($sql);#
				sql_execute($sql);
				$cids = array();
				while (($newArray = sql_fetch()) != false)
					$cids[] = $newArray[0];
				if ($cids)
					$where = "CLIENT2_ID IN (" . implode(',', $cids) . ")";
			}
		}
		
		if ($where)
		{
			if (!$sql_enc_prep_done)
				sql_encryption_preparation('CLIENT2');
			$sql = "SELECT CLIENT2_ID, C_CODE, " . sql_decrypt('C_CO_NAME', '', true) . "
					FROM CLIENT2 WHERE $where";
			$found = array();
			sql_execute($sql);
			while (($newArray = sql_fetch_assoc()) != false)
				$found[] = "{$newArray['CLIENT2_ID']}^{$newArray['C_CODE']}^{$newArray['C_CO_NAME']}";
			$return = implode('|', $found);
		}
		sql_disconnect();
	}
	print $return;
}
else if ($operation == 'cli_lkp')
{
	# Find matching clients using client code or client name
	$id_value = get_val('i', true); # e.g. CLIENT2.CLIENT2_ID
	$c_code = get_val('c');
	$c_co_name = trim(str_replace($safe_slash, '/', str_replace($safe_amp, '&', get_val2('n'))));
	if ($id_value && ($c_code || $c_co_name))
	{
		sql_connect();
		sql_encryption_preparation('CLIENT2');
		$sql = "SELECT CLIENT2_ID, C_CODE, " . sql_decrypt('C_CO_NAME', '', true) . "
				FROM CLIENT2
				WHERE (CLIENT2_ID<>$id_value)";
		$where = array();
		if ($c_code)
			$where[] = "C_CODE='$c_code'";
		if ($c_co_name)
			$where[] = sql_decrypt('C_CO_NAME') . "='" . addslashes_kdb($c_co_name) . "'";
		$where = "(" . implode(') OR (', $where) . ")";
		if ($where)
			$sql .= " AND ($where)";
		$sql .= "ORDER BY C_CODE";
		sql_execute($sql);
		while (($newArray = sql_fetch_assoc()) != false)
			print "<span style=\"cursor:pointer\" " .
						"onclick=\"goto_client({$newArray['CLIENT2_ID']},'{$newArray['C_CODE']}')\">" .
						"{$newArray['C_CODE']} - {$newArray['C_CO_NAME']}</span><br>";
		sql_disconnect();
	}
	else
		print '';
}
else 
	print "-1|clients_ajax.php: operation \"$operation\" unrecognised";

function check_tc_settings($client2_id, $field_name)
{
	# The user has just ticked either C_TRACE ("is a traces client") or C_COLLECT ("is a collections client")
	
	global $id_LETTER_TYPE_attend_yes;
	global $id_LETTER_TYPE_max;
	global $id_LETTER_TYPE_trace_no;
	
	$fees_returned = array();
	$must_return = false;
	
	if ($field_name == 'C_TRACE')
	{
		# 1. If a new client, set trace fees to default values (see client 8195)
		# 2. If a new client, set target fees to default values (see client 8195)
		# 3. If a new client, select all trace letters.
		
		# 1. If a new client, set trace fees to default values
		# "New client" implied by no fees currently.
		
		$fees = array('TR_FEE' => 35.0, 'NT_FEE' => 0.0, 'TC_FEE' => 35.0, 'RP_FEE' => 500.0, 'SV_FEE' => 115.0,
						'MN_FEE' => 35.0, 'TM_FEE' => 60.0, 'AT_FEE' => 115.0, 'ET_FEE' => 1.0);
		$get_fees = array();
		foreach ($fees as $fee => $val)
			$get_fees[] = "COALESCE($fee,0.0)";
		$val=$val; # keep code-checker quiet
		
		$sql = "SELECT " . implode(' + ', $get_fees) . " FROM CLIENT2 WHERE CLIENT2_ID=$client2_id";
		$fee_sum = 0.0;
		#print("[[$sql]]");#
		sql_execute($sql);
		while (($newArray = sql_fetch()) != false)
			$fee_sum = floatval($newArray[0]);
		if ($fee_sum == 0.0)
		{
			$must_return = true;
			foreach ($fees as $fee => $val)
			{
				$fees_returned[] = $val;
				if ($val == 0.0)
					$val = 'NULL';
				$sql = "UPDATE CLIENT2 SET $fee=$val WHERE CLIENT2_ID=$client2_id";
				#print("[[$sql]]");#
				audit_setup_client($client2_id, 'CLIENT2', 'CLIENT2_ID', $client2_id, $fee, $val);
				sql_execute($sql, true); # audited
			}
		}
		else
		{
			foreach ($fees as $fee => $val)
				$fees_returned[] = '-'; # this means "fee not changed"
		}
		
		# 2. If a new client, set target fees to default values (see client 8195)
		# "New client" implied by no target fees currently.
		
		$sql = "SELECT COUNT(*) FROM CLIENT_TARGET_LINK WHERE CLIENT2_ID=$client2_id";
		$target_count = 0;
		sql_execute($sql);
		while (($newArray = sql_fetch()) != false)
			$target_count = intval($newArray[0]);
		if ($target_count == 0)
		{
			$must_return = true;

			$id = 1;
			$fee = 44.0;
			$fees_returned[] = $fee;
			$sql = "INSERT INTO CLIENT_TARGET_LINK (CLIENT2_ID, JOB_TARGET_ID, CT_FEE) VALUES ($client2_id, $id, $fee)";
			audit_setup_client($client2_id, 'CLIENT_TARGET_LINK', 'CLIENT_TARGET_LINK_ID', 0, '', '');
			sql_execute($sql, true); # audited

			$id = 2;
			$fee = 55.0;
			$fees_returned[] = $fee;
			$sql = "INSERT INTO CLIENT_TARGET_LINK (CLIENT2_ID, JOB_TARGET_ID, CT_FEE) VALUES ($client2_id, $id, $fee)";
			audit_setup_client($client2_id, 'CLIENT_TARGET_LINK', 'CLIENT_TARGET_LINK_ID', 0, '', '');
			sql_execute($sql, true); # audited

			$id = 3;
			$fee = 90.0;
			$fees_returned[] = $fee;
			$sql = "INSERT INTO CLIENT_TARGET_LINK (CLIENT2_ID, JOB_TARGET_ID, CT_FEE) VALUES ($client2_id, $id, $fee)";
			audit_setup_client($client2_id, 'CLIENT_TARGET_LINK', 'CLIENT_TARGET_LINK_ID', 0, '', '');
			sql_execute($sql, true); # audited

			$id = 4;
			$fee = 100.0;
			$fees_returned[] = $fee;
			$sql = "INSERT INTO CLIENT_TARGET_LINK (CLIENT2_ID, JOB_TARGET_ID, CT_FEE) VALUES ($client2_id, $id, $fee)";
			audit_setup_client($client2_id, 'CLIENT_TARGET_LINK', 'CLIENT_TARGET_LINK_ID', 0, '', '');
			sql_execute($sql, true); # audited
		}
		else
		{
			$fees_returned[] = '-'; # this means "fee not changed"
			$fees_returned[] = '-'; # this means "fee not changed"
			$fees_returned[] = '-'; # this means "fee not changed"
			$fees_returned[] = '-'; # this means "fee not changed"
		}
		
		# 3. If a new client, select all trace letters.
		# "New client" implied by no selected trace letters currently.
		
		# Letter types 1 to 25 for trace
		$sql = "SELECT COUNT(*) FROM CLIENT_LETTER_LINK 
				WHERE CLIENT2_ID=$client2_id AND $id_LETTER_TYPE_trace_no <= LETTER_TYPE_ID AND LETTER_TYPE_ID <= $id_LETTER_TYPE_attend_yes";
		$letter_count = 0;
		sql_execute($sql);
		while (($newArray = sql_fetch()) != false)
			$letter_count = intval($newArray[0]);
		if ($letter_count == 0)
		{
			$must_return = true;
			$lt_list = array();
			
			for ($lt_id = $id_LETTER_TYPE_trace_no; $lt_id <= $id_LETTER_TYPE_attend_yes; $lt_id++)
			{
				$lt_list[] = $lt_id;
				$sql = "INSERT INTO CLIENT_LETTER_LINK (CLIENT2_ID, LETTER_TYPE_ID) VALUES ($client2_id, $lt_id)";
				audit_setup_client($client2_id, 'CLIENT_LETTER_LINK', 'CLIENT_LETTER_LINK_ID', 0, '', '');
				sql_execute($sql, true); # audited
			}
			$fees_returned[] = implode('~', $lt_list);
		}
		else
			$fees_returned[] = '-'; # this means "trace letters not changed"
		
		$fees_returned = ($must_return ? ("fees_t|" . implode('|', $fees_returned)) : '');
	}
	elseif ($field_name == 'C_COLLECT')
	{
		# If a new client, select all collect letters.
		# "New client" implied by no selected collect letters currently.

		# Letter types 26 and up, for collect
		$sql = "SELECT COUNT(*) FROM CLIENT_LETTER_LINK 
				WHERE CLIENT2_ID=$client2_id AND $id_LETTER_TYPE_attend_yes < LETTER_TYPE_ID";
		$letter_count = 0;
		sql_execute($sql);
		while (($newArray = sql_fetch()) != false)
			$letter_count = intval($newArray[0]);
		if ($letter_count == 0)
		{
			$must_return = true;
			$lt_list = array();
			
			for ($lt_id = ($id_LETTER_TYPE_attend_yes+1); $lt_id <= $id_LETTER_TYPE_max; $lt_id++)
			{
				$lt_list[] = $lt_id;
				$sql = "INSERT INTO CLIENT_LETTER_LINK (CLIENT2_ID, LETTER_TYPE_ID) VALUES ($client2_id, $lt_id)";
				audit_setup_client($client2_id, 'CLIENT_LETTER_LINK', 'CLIENT_LETTER_LINK_ID', 0, '', '');
				sql_execute($sql, true); # audited
			}
			$fees_returned[] = implode('~', $lt_list);
		}
		else
			$fees_returned[] = '-'; # this means "collect letters not changed"
	
		$fees_returned = ($must_return ? ("fees_c|" . implode('|', $fees_returned)) : '');
	}
	return $fees_returned;
	
} # check_tc_settings()

?>
