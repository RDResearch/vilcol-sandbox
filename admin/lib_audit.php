<?php

# A list of all database tables in the system, and for each one, a list of the encrypted fields in it
$audit_tables = array(
		'ACTIVITY_SD' => array(),
		'ADDRESS_HISTORY' => array('ADDR_1', 'ADDR_2', 'ADDR_3', 'ADDR_4', 'ADDR_5', 'ADDR_PC', 'AD_NOTES'),
		'ADJUSTMENT_SD' => array(),
		'AUDIT' => array('OLD_VAL', 'NEW_VAL'),
		'CLIENT_CONTACT' => array('CC_FIRSTNAME', 'CC_LASTNAME', 'CC_POSITION', 'CC_EMAIL_1', 'CC_EMAIL_2',
						'CC_ADDR_1', 'CC_ADDR_2', 'CC_ADDR_3', 'CC_ADDR_4', 'CC_ADDR_5', 'CC_ADDR_PC'),
		'CLIENT_CONTACT_PHONE' => array('CP_PHONE', 'CP_DESCR'),
		'CLIENT_GROUP' => array('GROUP_NAME'),
		'CLIENT_LETTER_LINK' => array(),
		'CLIENT_NOTE' => array('CN_NOTE'),
		'CLIENT_REPORT' => array(),
		'CLIENT_TARGET_LINK' => array(),
		'CLIENT_Z' => array(),
		'CLIENT2' => array('C_CO_NAME', 'C_ADDR_1', 'C_ADDR_2', 'C_ADDR_3', 'C_ADDR_4', 'C_ADDR_5', 'C_ADDR_PC',
						'C_BANK_NAME', 'C_BANK_SORTCODE', 'C_BANK_ACC_NUM', 'C_BANK_ACC_NAME', 'C_BANK_SWIFT', 'C_BANK_IBAN',
						'INV_EMAIL_NAME', 'INV_EMAIL_ADDR'),
		'EMAIL' => array('EM_TO', 'EM_CC', 'EM_BCC', 'EM_SUBJECT', 'EM_MESSAGE', '', ''),
		'INV_ALLOC' => array(),
		'INV_BILLING' => array(),
		'INV_RECP' => array('RC_NOTES'),
		'INVOICE' => array('INV_NOTES'),
		'JOB' => array('CLIENT_REF', 'J_DIARY_TXT', 'JC_ADDR_1_CU6', 'JC_ADDR_2_CU6', 'JC_ADDR_3_CU6',
						'JC_ADDR_4_CU6', 'JC_ADDR_5_CU6', 'JC_ADDR_PC_CU6', 'JC_AN1_CU10', 'JC_AN2_CU10', 'JC_AN3_CU10',
						'JC_IR_ADDR_1', 'JC_IR_ADDR_2', 'JC_IR_ADDR_3', 'JC_IR_ADDR_4', 'JC_IR_ADDR_5', 'JC_IR_ADDR_PC',
						'JC_REASON_2', 'JT_LET_REPORT'),
						#, 'J_REFERRER'
		'JOB_ACT' => array(),
		'JOB_ARRANGE' => array('JA_METHOD_NUMBER'),
		'JOB_GROUP' => array(),
		'JOB_LETTER' => array('JL_TEXT', 'JL_TEXT_2'),
		'JOB_NOTE' => array('J_NOTE'),
		'JOB_PAYMENT' => array('COL_METHOD_NUMBER', 'COL_NOTES'),
		'JOB_PHONE' => array('JP_PHONE', 'JP_EMAIL', 'JP_DESCR'),
		'JOB_STATUS_SD' => array(),
		'JOB_SUBJECT' => array('JS_FIRSTNAME', 'JS_LASTNAME', 'JS_COMPANY', 'JS_ADDR_1', 'JS_ADDR_2', 'JS_ADDR_3', 'JS_ADDR_4',
						'JS_ADDR_5', 'JS_ADDR_PC', 'JS_BANK_NAME', 'JS_BANK_SORTCODE', 'JS_BANK_ACC_NUM', 'JS_BANK_ACC_NAME',
						'NEW_ADDR_1', 'NEW_ADDR_2', 'NEW_ADDR_3', 'NEW_ADDR_4', 'NEW_ADDR_5', 'NEW_ADDR_PC'),
		'JOB_TARGET_SD' => array(),
		'JOB_TYPE_SD' => array(),
		'JOB_Z' => array(),
		'LETTER_TYPE_SD' => array(),
		'MISC_INFO' => array('VALUE_ENC'),
		'PAYMENT_METHOD_SD' => array(),
		'PAYMENT_ROUTE_SD' => array(),
		'REPORT' => array(),
		'REPORT_CLIENT_LINK' => array(),
		'REPORT_FIELD_LINK' => array(),
		'REPORT_FIELD_SD' => array(),
		'SALESPERSON' => array(),
		'USER_PERM_LINK' => array(),
		'USER_PERMISSION_SD' => array(),
		'USER_ROLE_PERM_LINK' => array(),
		'USER_ROLE_SD' => array(),
		'USERV' => array('USERNAME', 'ORIG_USERNAME_C', 'ORIG_USERNAME_T', 'PASSWORD', 'U_LASTNAME', 'U_EMAIL', 'U_NOTES')
		);

function au_initialise()
{
	global $AUDIT; # structure

	# Note that USER_ID is for the USERV record that is changed, not the person who is currently logged in.
	# CHANGE_ID is used for the person who is currently logged in.
	# FILE_EVENT is used if a file is uploaded/created/deleted; no DB table is involved in this case.
	# LOGIN_EVENT is used when a user logs in; no DB table is involved in this case.

	$AUDIT = array(	'USER_ID' => 0,
					'JOB_ID' => 0,
					'CLIENT2_ID' => 0,
					'FILE_EVENT' => '',
					'LOGIN_EVENT' => false,
					'TABLE_NAME' => '',
					'ID_NAME' => '',
					'ID_VALUE' => 0,
					'FIELD_NAME' => '',
					'OLD_VAL' => '',
					'NEW_VAL' => '',
					'DELETING' => false,
					'DELETED_RECORD' => '',
					'CHANGE_ID' => 0
					);
}

function au_user_id($user_id)
{
	global $AUDIT; # structure
	$user_id = intval($user_id);
	if ($user_id < 0)
		$user_id = 0;
	$AUDIT['USER_ID'] = $user_id;
}

function au_job_id($job_id)
{
	global $AUDIT; # structure
	$job_id = intval($job_id);
	if ($job_id < 0)
		$job_id = 0;
	$AUDIT['JOB_ID'] = $job_id;
}

function au_client_id($client2_id)
{
	global $AUDIT; # structure
	$client2_id = intval($client2_id);
	if ($client2_id < 0)
		$client2_id = 0;
	$AUDIT['CLIENT2_ID'] = $client2_id;
}

function au_table_name($table_name)
{
	global $AUDIT; # structure
	$AUDIT['TABLE_NAME'] = $table_name;
}

function au_id_name($id_name)
{
	global $AUDIT; # structure
	$AUDIT['ID_NAME'] = $id_name;
}

function au_id_value($id_value)
{
	global $AUDIT; # structure
	$id_value = intval($id_value);
	if ($id_value < 0)
		$id_value = 0;
	$AUDIT['ID_VALUE'] = $id_value;
}

function au_field_name($field_name)
{
	global $AUDIT; # structure
	$AUDIT['FIELD_NAME'] = $field_name;
}

function au_old_val()
{
	global $AUDIT; # structure

	if (encrypted_field($AUDIT['TABLE_NAME'], $AUDIT['FIELD_NAME']))
	{
		sql_encryption_preparation($AUDIT['TABLE_NAME']);
		$field_name = sql_decrypt($AUDIT['FIELD_NAME']);
	}
	else
		$field_name = $AUDIT['FIELD_NAME'];

	$sql = "SELECT $field_name FROM {$AUDIT['TABLE_NAME']} WHERE {$AUDIT['ID_NAME']} = {$AUDIT['ID_VALUE']}";
	#print "#$sql#";#
	sql_execute($sql);
	$old_field_value = '';
	while (($newArray = sql_fetch()) != false)
		$old_field_value = $newArray[0];
	$AUDIT['OLD_VAL'] = $old_field_value;
}

function au_new_val($new_val)
{
	global $AUDIT; # structure
	$AUDIT['NEW_VAL'] = $new_val;
}

function au_setup($table_name, $id_name, $id_value, $field_name, $new_val)
{
	global $AUDIT;

	au_table_name($table_name);
	au_id_name($id_name);
	if ($id_value == 0)
	{
		# Inserting a new record.
		# sql_execute() will pass new ID to audit_add_record().
	}
	elseif ($field_name == '')
	{
		# Deleting a record.
		au_id_value($id_value);
		$AUDIT['DELETING'] = true;

		$sql = "SELECT * FROM {$AUDIT['TABLE_NAME']} WHERE {$AUDIT['ID_NAME']}={$AUDIT['ID_VALUE']}";
		sql_execute($sql);
		while (($newArray = sql_fetch_assoc()) != false)
			$AUDIT['DELETED_RECORD'] = $newArray;
	}
	else
	{
		# Updating an existing record
		au_id_value($id_value);
		au_field_name($field_name);
		au_old_val();
		au_new_val($new_val);
	}
}

function audit_setup_gen($table_name, $id_name, $id_value, $field_name, $new_val)
{
	# For changes that are not linked to a user
	au_initialise();
	if (($table_name == 'JOB') && ($id_name == 'JOB_ID') && (0 < $id_value))
		au_job_id($id_value);
	elseif (($table_name == 'CLIENT2') && ($id_name == 'CLIENT2_ID') && (0 < $id_value))
		au_client_id($id_value);
	elseif (($table_name == 'USERV') && ($id_name == 'USER_ID') && (0 < $id_value))
		au_user_id($id_value);
	au_setup($table_name, $id_name, $id_value, $field_name, $new_val);
}

function audit_setup_login()
{
	# For when a user logs in

	global $AUDIT; # structure
	global $USER; # set up by admin_login() but only contains USER_ID, nothing else.

	au_initialise();
	$AUDIT['LOGIN_EVENT'] = true;
	$AUDIT['CHANGE_ID'] = $USER['USER_ID'];
	$AUDIT['USER_ID'] = $USER['USER_ID'];
}

function audit_setup_user($user_id, $table_name, $id_name, $id_value, $field_name, $new_val)
{
	# For changes that are linked to a user
	au_initialise();
	au_user_id($user_id);
	au_setup($table_name, $id_name, $id_value, $field_name, $new_val);
}

function audit_setup_job($job_id, $table_name, $id_name, $id_value, $field_name, $new_val)
{
	# For changes that are linked to a job
	au_initialise();
	au_job_id($job_id);
	au_setup($table_name, $id_name, $id_value, $field_name, $new_val);
}

function audit_setup_client($client2_id, $table_name, $id_name, $id_value, $field_name, $new_val)
{
	# For changes that are linked to a client2
	au_initialise();
	au_client_id($client2_id);
	au_setup($table_name, $id_name, $id_value, $field_name, $new_val);
}

function au_file_event($file_event)
{
	global $AUDIT;
	global $file_event_upload;
	global $file_event_create;
	global $file_event_delete;

	if (($file_event == $file_event_upload) || ($file_event == $file_event_create) || ($file_event == $file_event_delete))
		$AUDIT['FILE_EVENT'] = $file_event;
}

function audit_add_record($insert_id)
{
	global $AUDIT; # structure
	global $mysql_server; # settings.php
	global $new_audit_id;
	global $sqlFalse; # settings.php
	global $sqlNow; # settings.php
	global $sqlTrue; # settings.php
	global $USER; # for LOGIN_EVENT, this only contains USER_ID

	sql_encryption_preparation('AUDIT');

	if ($AUDIT['FILE_EVENT'])
	{
		$inserting = false;
		$deleting = false;
		# Note that CHANGE_ID will have been set by audit_setup_file()
	}
	elseif ($AUDIT['LOGIN_EVENT'])
	{
		$inserting = false;
		$deleting = false;
		# Note that CHANGE_ID will have been set by audit_setup_login()
		$AUDIT['TABLE_NAME'] = xprint($_SERVER['REMOTE_ADDR'], false, 1);
		$AUDIT['ID_NAME'] = xprint($_SERVER['HTTP_REFERER'], false, 1);
	}
	else
	{
		# If a record is being inserted then $insert_id will be the new ID, otherwise (e.g. updating) it will be zero.
		$inserting = (($insert_id > 0) ? true : false);
		$deleting = $AUDIT['DELETING'];

		if ($inserting)
		{
			if (($AUDIT['TABLE_NAME'] == 'JOB') && (!$AUDIT['JOB_ID']))
				$AUDIT['JOB_ID'] = $insert_id;
			elseif (($AUDIT['TABLE_NAME'] == 'CLIENT2') && (!$AUDIT['CLIENT2_ID']))
				$AUDIT['CLIENT2_ID'] = $insert_id;
//			elseif (($AUDIT['TABLE_NAME'] == 'CONTACT') && (!$AUDIT['CONTACT_ID']))
//				$AUDIT['CONTACT_ID'] = $insert_id;
			elseif (($AUDIT['TABLE_NAME'] == 'USERV') && (!$AUDIT['USER_ID']))
				$AUDIT['USER_ID'] = $insert_id;
		}

	//	if (!$AUDIT['CHANGE_ID'])
	//	{
			if ($USER && $USER['USER_ID'])
				$AUDIT['CHANGE_ID'] = $USER['USER_ID']; # the person who is currently logged in
			else
				$AUDIT['CHANGE_ID'] = 0; # zero means "system" i.e. not a person
	//	}
	}

	$login_event = ($AUDIT['LOGIN_EVENT'] ? $sqlTrue : $sqlFalse);

	$fields = "CHANGE_DT, CHANGE_ID,             USER_ID,             JOB_ID,             CLIENT2_ID,             ";
	$values = "$sqlNow,   {$AUDIT['CHANGE_ID']}, {$AUDIT['USER_ID']}, {$AUDIT['JOB_ID']}, {$AUDIT['CLIENT2_ID']}, ";
//	$fields = "CHANGE_DT, CHANGE_ID,             LOAN_ID,             CONTACT_ID,             USER_ID,             ";
//	$values = "$sqlNow,   {$AUDIT['CHANGE_ID']}, {$AUDIT['LOAN_ID']}, {$AUDIT['CONTACT_ID']}, {$AUDIT['USER_ID']}, ";

	$fields .= "FILE_EVENT,               LOGIN_EVENT,  TABLE_NAME,               ID_NAME               ";
	$values .= "'{$AUDIT['FILE_EVENT']}', $login_event, '{$AUDIT['TABLE_NAME']}', '{$AUDIT['ID_NAME']}' ";
//	$fields .= "FILE_EVENT,               TABLE_NAME,               ID_NAME,               ANO,             IS_FROM_R1";
//	$values .= "'{$AUDIT['FILE_EVENT']}', '{$AUDIT['TABLE_NAME']}', '{$AUDIT['ID_NAME']}', {$AUDIT['ANO']}, 0         ";

	if ($mysql_server)
	{
		$fields .= ",               FROM_MYSQL";
		$values .= ", 1";
	}

	if ($inserting)
	{
		# A new record has been inserted into table $AUDIT['TABLE_NAME'] and has the ID value $insert_id.

		$fields .= ", ID_VALUE,   ";
		$values .= ", $insert_id, ";

		# First, we need to get a list of field names
		$sql = ($mysql_server ?
				"SHOW COLUMNS IN {$AUDIT['TABLE_NAME']}"
				:
					("SELECT " . sqla('c.name') . " AS Field
						FROM sys.columns c
						INNER JOIN sys.types t ON c.user_type_id = t.user_type_id
						LEFT OUTER JOIN sys.index_columns ic ON ic.object_id = c.object_id AND ic.column_id = c.column_id
						LEFT OUTER JOIN sys.indexes i ON ic.object_id = i.object_id AND ic.index_id = i.index_id
					WHERE c.object_id = OBJECT_ID('{$AUDIT['TABLE_NAME']}')")
				);
		sql_execute($sql);
		$columns = array();
		while (($newArray = sql_fetch_assoc()) != false)
			$columns[] = $newArray['Field'];
		#log_write(print_r($columns,1));

		# Check which field names are for encrypted fields
		$col_list = '';
		foreach ($columns as $one)
		{
			if ($col_list)
				$col_list .= ", ";
			if (encrypted_field($AUDIT['TABLE_NAME'], $one))
				$col_list .= sql_decrypt($one, '', true);
			else
				$col_list .= $one;
		}

		if (encrypted_table($AUDIT['TABLE_NAME']))
			sql_encryption_preparation($AUDIT['TABLE_NAME']);
		$sql = "SELECT $col_list FROM {$AUDIT['TABLE_NAME']} WHERE {$AUDIT['ID_NAME']}=$insert_id";
		sql_execute($sql);
		$new_record = '';
		while (($newArray = sql_fetch_assoc()) != false)
			$new_record = $newArray;
		#log_write(print_r($new_record,1));
		foreach ($new_record as $field_name => $new_val)
		{
			if ($new_val)
			{
				$old_val = sql_encrypt('(new record)', false, 'AUDIT');
				$new_val = sql_encrypt(quote_smart($new_val,
													($mysql_server ? false : true)), # must have quotes for MS SQL Server
										true, 'AUDIT'); # true => it has quotes

				$fields2 = $fields . "FIELD_NAME,    OLD_VAL,  NEW_VAL";
				$values2 = $values . "'$field_name', $old_val, $new_val";
				$sql = "INSERT INTO AUDIT ($fields2) VALUES ($values2)";
				#log_write(print_r($AUDIT,1));
				#log_write($sql); #
				$new_audit_id = sql_execute($sql);
				#log_write("New Audit Record (i): $new_audit_id from $sql");#
			}
		}
		#$field_names = sql_table_field_names($AUDIT['TABLE_NAME']);
		#foreach ($field_names as $fname)
	}
	elseif ($deleting)
	{
		# An existing record in $AUDIT['TABLE_NAME'] has been deleted. A copy of it should be in $AUDIT['DELETED_RECORD'].

		$fields .= ", ID_VALUE,             ";
		$values .= ", {$AUDIT['ID_VALUE']}, ";

		foreach ($AUDIT['DELETED_RECORD'] as $field_name => $old_val)
		{
			if ($old_val)
			{
				$old_val = sql_encrypt(quote_smart($old_val,
													($mysql_server ? false : true)), # must have quotes for MS SQL Server
										true, 'AUDIT'); # true => it has quotes
				$new_val = sql_encrypt('(record deleted)', false, 'AUDIT');

				$fields2 = $fields . "FIELD_NAME,    OLD_VAL,  NEW_VAL";
				$values2 = $values . "'$field_name', $old_val, $new_val";
				$sql = "INSERT INTO AUDIT ($fields2) VALUES ($values2)";
				$new_audit_id = sql_execute($sql);
				#log_write("New Audit Record (d): $new_audit_id from $sql");#
			}
		}
	}
	elseif ($AUDIT['FILE_EVENT'])
	{
		# A file has been uploaded / created / deleted.

		$sql = "INSERT INTO AUDIT ($fields) VALUES ($values)";
		#log_write(print_r($AUDIT,1));#
		#log_write($sql);#
		$new_audit_id = sql_execute($sql);
		#log_write("New Audit Record (f): $new_audit_id from $sql");#
	}
	elseif ($AUDIT['LOGIN_EVENT'])
	{
		# The user has just logged in.

		$fields .= ", ID_VALUE, FIELD_NAME";
		$values .= ", -1,       ''        ";
		$sql = "INSERT INTO AUDIT ($fields) VALUES ($values)";
		#log_write(print_r($AUDIT,1));#
		#log_write($sql);#
		$new_audit_id = sql_execute($sql);
		#log_write("New Audit Record (f): $new_audit_id from $sql");#
	}
	else
	{
		# An existing record in $AUDIT['TABLE_NAME'] was updated.

		if (($AUDIT['OLD_VAL'] === $AUDIT['NEW_VAL']) ||
			(($AUDIT['OLD_VAL'] === '0') && ($AUDIT['NEW_VAL'] === $sqlFalse)) ||
			(($AUDIT['OLD_VAL'] === '1') && ($AUDIT['NEW_VAL'] === $sqlTrue)))
			return;

		$old_val = sql_encrypt(quote_smart($AUDIT['OLD_VAL'],
											($mysql_server ? false : true)), # must have quotes for MS SQL Server
								true, 'AUDIT');
		$new_val = sql_encrypt(quote_smart($AUDIT['NEW_VAL'],
											($mysql_server ? false : true)), # must have quotes for MS SQL Server
								true, 'AUDIT');

		$fields .= ", ID_VALUE,             FIELD_NAME,               OLD_VAL,  NEW_VAL";
		$values .= ", {$AUDIT['ID_VALUE']}, '{$AUDIT['FIELD_NAME']}', $old_val, $new_val";

		$sql = "INSERT INTO AUDIT ($fields) VALUES ($values)";
		#log_write(print_r($AUDIT,1));#
		#log_write($sql); #
		$new_audit_id = sql_execute($sql);
		#log_write("New Audit Record (u): $new_audit_id from $sql");#
	}
}

function sql_get_audit_all($qualifiers='')
{
	global $mysql_server; # settings.php
	global $sqlTrue;

	$where = '';
	$limit = 1000;
	if ($qualifiers)
	{
		$where = array();

		if ((isset($qualifiers['date_from'])) && $qualifiers['date_from'])
			$where[] = date_for_sql($qualifiers['date_from']) . " <= AU.CHANGE_DT";
		if ((isset($qualifiers['date_to'])) && $qualifiers['date_to'])
			$where[] = "AU.CHANGE_DT <= " . date_for_sql($qualifiers['date_to'], false, true, false, false, true);
		if (isset($qualifiers['change_id']) && ((intval($qualifiers['change_id']) > 0) || ($qualifiers['change_id'] == -1))) # -1 for "(new system)"
			$where[] = "AU.CHANGE_ID=" . intval($qualifiers['change_id']);

		if ((isset($qualifiers['table'])) && $qualifiers['table'])
		{
			$where[] = "AU.TABLE_NAME=" . quote_smart($qualifiers['table']);
			if (isset($qualifiers['table_id']) && (intval($qualifiers['table_id']) > 0))
				$where[] = "AU.ID_VALUE=" . intval($qualifiers['table_id']);
			if (isset($qualifiers['field_name']) && ($qualifiers['field_name'] != ''))
				$where[] = "AU.FIELD_NAME=" . quote_smart($qualifiers['field_name'],true);
		}
		
		if (isset($qualifiers['old_new_not_null']) && ($qualifiers['old_new_not_null'] == 1))
			$where[] = "((AU.OLD_VAL IS NOT NULL) OR (AU.NEW_VAL IS NOT NULL))";
		
//		if ((isset($qualifiers['filename'])) && $qualifiers['filename'])
//			$where[] = "(AU.FILE_EVENT IS NOT NULL) AND " .
//						"(AU.TABLE_NAME LIKE " . quote_smart($qualifiers['filename'], false, true, true) . ")";

		if (isset($qualifiers['job_id']) && (intval($qualifiers['job_id']) > 0))
			$where[] = "AU.JOB_ID=" . intval($qualifiers['job_id']);
		if (isset($qualifiers['client2_id']) && (intval($qualifiers['client2_id']) > 0))
			$where[] = "AU.CLIENT2_ID=" . intval($qualifiers['client2_id']);
//		if (isset($qualifiers['contact_id']) && ((1 * $qualifiers['contact_id']) > 0))
//			$where[] = "AU.CONTACT_ID=" . (1 * $qualifiers['contact_id']);
		if (isset($qualifiers['user_id']) && (intval($qualifiers['user_id']) > 0))
			$where[] = "AU.USER_ID=" . intval($qualifiers['user_id']);

		if (array_key_exists('logins', $qualifiers) && ($qualifiers['logins'] == 1))
		{
			$where[] = "AU.LOGIN_EVENT=$sqlTrue";
			# Sneaky trick: if "logins" is set then use Table ID field for IP address, which is held in table name field.
			if (isset($qualifiers['table_id']) && ($qualifiers['table_id'] != ''))
				$where[] = "AU.TABLE_NAME='{$qualifiers['table_id']}'";
		}

		if ($where)
			$where = "WHERE (" . implode(') AND (', $where) . ")";
		else
			$where = '';

		if (isset($qualifiers['limit']))
			$limit = $qualifiers['limit'];
	}

	sql_encryption_preparation('AUDIT');
	$ms_top = '';
	$my_limit = '';
	if (0 < $limit)
		list($ms_top, $my_limit) = sql_top_limit($limit);
	$sql = "SELECT $ms_top AU.AUDIT_ID, AU.USER_ID, AU.FILE_EVENT, AU.LOGIN_EVENT, AU.JOB_ID, AU.CLIENT2_ID,
				" . date_format_sql("AU.CHANGE_DT",'hms') . ",
				" . sql_decrypt("US.USERNAME") . " AS CHANGED_BY,
				AU.TABLE_NAME, AU.ID_NAME, AU.ID_VALUE, AU.FIELD_NAME,
				" . sql_decrypt("AU.OLD_VAL", '', true) . ", " . sql_decrypt("AU.NEW_VAL", '', true) . ", ";
	if ($mysql_server)
		$sql .= "FROM_MYSQL";
	else
		$sql .= "0 AS FROM_MYSQL";
	$sql .= "
			FROM AUDIT AS AU
			LEFT JOIN USERV AS US ON US.USER_ID=AU.CHANGE_ID
			$where
			ORDER BY AU.AUDIT_ID DESC $my_limit";
		#AU.LOAN_ID, AU.CONTACT_ID, AU.IS_FROM_R1,
		#CASE AU.IS_FROM_R1 WHEN 1 THEN 'Old Rialto' ELSE 'Rialto II' END AS SOURCE,
		#" . sql_decrypt('AU.R1_DESCR', '', true) . ", AU.ANO,
	dprint($sql);
	sql_execute($sql);
	$audit = array();
	while (($newArray = sql_fetch_assoc()) != false)
		$audit[] = $newArray;

	return $audit;
}

//function sql_get_audit_loan($loan_id)
//{
//	$source = false;
//	sql_encryption_preparation('AUDIT');
//	$sql = "SELECT AU.AUDIT_ID, " . date_format_sql("AU.CHANGE_DT", 'hms') . ",
//				" . sql_decrypt("US.USERNAME") . " AS CHANGED_BY,
//				AU.IS_FROM_R1, CASE AU.IS_FROM_R1 WHEN 1 THEN 'Old Rialto' ELSE '' END AS SOURCE, AU.FILE_EVENT,
//				" . sql_decrypt('AU.R1_DESCR', '', true) . ",
//				AU.TABLE_NAME, AU.ID_NAME, AU.ID_VALUE, AU.ANO, AU.FIELD_NAME,
//				" . sql_decrypt("AU.OLD_VAL", '', true) . ", " . sql_decrypt("AU.NEW_VAL", '', true) . "
//			FROM AUDIT AS AU
//			LEFT JOIN USERV AS US ON US.USER_ID=AU.CHANGE_ID
//			WHERE (AU.LOAN_ID=$loan_id)
//			ORDER BY AU.AUDIT_ID DESC";
//	sql_execute($sql);
//	$audit = array();
//	while (($newArray = sql_fetch_assoc()) != false)
//	{
//		$audit[] = $newArray;
//		if ($newArray['SOURCE'])
//			$source = true;
//	}
//	return array($audit, $source);
//}

function audit_get_tables()
{
	global $audit_tables;

	$tables = array();
	foreach ($audit_tables as $table_name => $field_list)
	{
		if ($table_name != 'AUDIT')
			$tables[$table_name] = $table_name;
	}
	$field_list=$field_list; # keep code-checker quiet
	return $tables;
}

function encrypted_field($table, $field)
{
	global $audit_tables;

	if (array_key_exists($table, $audit_tables))
	{
		if ($field == '*')
		{
			if (count($audit_tables[$table]) > 0)
				return true;
			else
				return false;
		}
		else
		{
			if (in_array($field, $audit_tables[$table]))
				return true;
			else
				return false;
		}
	}
	return false;
}

function encrypted_table($table)
{
	# Say whether the named table has any encrypted fields
	return encrypted_field($table, '*');
}

?>
