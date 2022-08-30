<?php

# included by library.php

function sql_connect()
{
	global $mysql_server; # settings.php
	return ($mysql_server ? my_sql_connect() : ms_sql_connect());
}

function sql_disconnect()
{
	global $mysql_server; # settings.php
	return ($mysql_server ? my_sql_disconnect() : ms_sql_disconnect());
}

function sql_execute($sql, $audit_it=false)
{
	global $mysql_server; # settings.php
	global $time_tests; # settings.php

	if ($time_tests)
		$t_start = time();
	$rc = ($mysql_server ? my_sql_execute($sql, $audit_it) : ms_sql_execute($sql, $audit_it));
	if ($time_tests)
	{
		$t_length = time() - $t_start;
		log_write("sql_execute() took $t_length seconds to run: $sql", true, true);
	}
	return $rc;
}

function sql_free_result()
{
	global $ms_sql_result;
	global $mysql_server; # settings.php

	if (!$mysql_server)
		odbc_free_result($ms_sql_result);
}

function sql_fetch()
{
	global $mysql_server; # settings.php
	return ($mysql_server ? my_sql_fetch() : ms_sql_fetch());
}

function sql_fetch_assoc()
{
	global $mysql_server; # settings.php
	return ($mysql_server ? my_sql_fetch_assoc() : ms_sql_fetch_assoc());
}

function sql_encrypt($field_value, $got_quotes=false, $ms_table='')
{
	global $mysql_server; # settings.php
	return ($mysql_server ? my_sql_encrypt($field_value, $got_quotes) :
							ms_sql_encrypt($field_value, $got_quotes, $ms_table));
}

function sql_decrypt($field_name, $type='', $add_alias=false)
{
	global $mysql_server; # settings.php
	return ($mysql_server ? my_sql_decrypt($field_name, $type, $add_alias) :
							ms_sql_decrypt($field_name, $type, $add_alias));
}

function sql_invalid_cursor_state_fix()
{
	global $mysql_server; # settings.php
	return ($mysql_server ? my_sql_invalid_cursor_state_fix() : ms_sql_invalid_cursor_state_fix());
}

function my_sql_connect()
{
	# NOTE: This should only be called from sql_connect()

	global $my_sql_conn;
	global $sql_database;
	global $sql_host;
	global $sql_password;
	global $sql_username;

	$rc = false;
	$my_sql_conn = mysqli_connect($sql_host, $sql_username, $sql_password, $sql_database);
	if ($my_sql_conn)
		$rc = true;
	else
		log_write("my_sql_connect(): mysqli_connect() failed", true); # only if open
	return $rc;
}

function my_sql_disconnect()
{
	# NOTE: This should only be called from sql_disconnect()

	global $my_sql_conn;

	mysqli_close($my_sql_conn);
	$my_sql_conn = '';
}

function my_sql_execute($sql, $audit_it)
{
	# NOTE: This should only be called from sql_execute()

	global $my_sql_conn;
	global $sql_ignore_error; # 29/06/22
	global $sql_result;
	global $USER;

	$problem = '';
	if ($my_sql_conn)
	{
		$sql_result = mysqli_query($my_sql_conn, $sql);
		if (!$sql_result)
			$problem = 'exe';
	}
	else
		$problem = 'con';

	if (!$problem)
	{
		if (strtolower(substr($sql, 0, strlen("insert"))) == "insert")
			$insert_id = sql_lastID();
		else
			$insert_id = 0;
		if ($audit_it)
			audit_add_record($insert_id); # recursion
		return $insert_id;
	}

	# Problem was found - log it then exit
	if (!$sql_ignore_error)
	{
		$sql = sql_dekey($sql);
		$error = ($my_sql_conn ? sql_dekey(mysqli_errno($my_sql_conn)) : -9999);
		if ($problem == 'con')
			$problem = "No DB connection exists";
		elseif ($problem == 'exe')
			$problem = "SQL Exec Error = \"$error\" = \"" . sql_dekey(mysqli_error($my_sql_conn)) . "\"";
		else
			$problem = '?';
		$problem = "sql_execute($sql): $problem";
		if (log_write($problem, true))
			print("SQL ERROR (" . strftime('%Y-%m-%d %H:%M:%S') . "/{$USER['USER_ID']}) - check log file");
		else
			print("SQL ERROR (" . strftime('%Y-%m-%d %H:%M:%S') . "/{$USER['USER_ID']}) - $problem");
		log_close();
		exit();
	}
}

function my_sql_fetch()
{
	# NOTE: This should only be called from sql_fetch()

	global $sql_result;

	return mysqli_fetch_array($sql_result, MYSQLI_NUM);
}

function my_sql_fetch_assoc()
{
	# NOTE: This should only be called from sql_fetch_assoc()

	global $sql_result;

	return mysqli_fetch_array($sql_result, MYSQLI_ASSOC);
}

function my_sql_encrypt($field_value, $got_quotes)
{
	# NOTE: This should only be called from sql_encrypt()

	global $sql_key;
	if ($field_value == 'NULL')
		return $field_value;
	if (!$got_quotes)
		#$field_value = "'$field_value'";
		$field_value = quote_smart($field_value, true);
	return "AES_ENCRYPT($field_value,'$sql_key')";
}

function my_sql_decrypt($field_name, $type, $add_alias)
{
	# NOTE: This should only be called from sql_decrypt()

	# Return a string that can be directly inserted into SQL that decrypts a field.

	global $sql_key;

	if (!$type)
		$type = 'CHAR';

	$alias = '';
	if ($add_alias)
	{
		$bits = explode('.', $field_name); # separate table name from field name
		if (count($bits) == 1)
			$alias = $bits[0];
		else
			$alias = $bits[1];
		$alias = " AS $alias";
	}
	return "CONVERT(AES_DECRYPT($field_name,'$sql_key'),$type)$alias";
}

function sql_dekey($sql)
{
	# Obscure encryption key before debug-prints

	global $sql_key;

	$subkey = substr($sql_key, 0, 4);
	return str_replace($subkey, '_k_', str_replace($sql_key,'_key_',$sql));
}

//function sql_drop_table($tablename)
//{
//	if (sql_table_exists($tablename))
//	{
//		$sql = "DROP TABLE $tablename";
//		log_write($sql, true);
//		sql_execute($sql);
//	}
//}

function sql_table_exists($tablename, $get_count=false)
{
	global $mysql_server; # settings.php
	return ($mysql_server ? my_sql_table_exists($tablename, $get_count) : ms_sql_table_exists($tablename, $get_count));
}

function sql_object_exists($objectname)
{
	global $mysql_server; # settings.php
	return ($mysql_server ? my_sql_object_exists($objectname) : ms_sql_object_exists($objectname));
}

function my_sql_table_exists($tablename, $get_count)
{
	# NOTE: This should only be called from sql_table_exists()

	# Returns boolean true if table called $tablename exists in the MySQL database, else false.
	# If table exists and $get_count is true, then return one more than the number of records in
	# the table (it has to be one more, so we won't return zero, which will look like false i.e.
	# like the table doesn't exist!)

	$ret = false;
	if ($tablename)
	{
		$sql = "SHOW TABLES LIKE '$tablename'";
		sql_execute($sql);
		while (($newArray = sql_fetch()) != false)
		{
			if (strtolower($newArray[0]) == strtolower($tablename))
				$ret = true;
		}
		if ($ret && $get_count)
		{
			$sql = "SELECT COUNT(*) FROM $tablename";
			sql_execute($sql);
			while (($newArray = sql_fetch()) != false)
				$ret = 1 + $newArray[0]; # see notes above
		}
	}
	return $ret;
}

function my_sql_object_exists($objectname)
{
	# NOTE: This should only be called from sql_object_exists()

	$objectname=$objectname; # keep code-checker quiet
	return false; # function not yet implemented
}

function ms_sql_object_exists($objectname)
{
	# NOTE: This should only be called from sql_object_exists()

	$ret = false;
	if ($objectname)
	{
		$sql = "SELECT [name] FROM sys.objects WHERE [name]='$objectname'";
		sql_execute($sql);
		while (($newArray = sql_fetch()) != false)
		{
			if ($newArray[0] == $objectname)
				$ret = true;
		}
	}
	return $ret;
}

function ms_sql_table_exists($tablename, $get_count)
{
	# NOTE: This should only be called from sql_table_exists()

	# If table exists and $get_count is true, then return one more than the number of records in
	# the table (it has to be one more, so we won't return zero, which will look like false i.e.
	# like the table doesn't exist!)

	$ret = false;
	if ($tablename)
	{
		$sql = "SELECT [name] FROM sys.objects WHERE object_id = OBJECT_ID(N'[dbo].[$tablename]') AND type in (N'U')";
		sql_execute($sql);
		while (($newArray = sql_fetch()) != false)
		{
			if ($newArray[0] == $tablename)
				$ret = true;
		}
		if ($ret && $get_count)
		{
			$sql = "SELECT COUNT(*) FROM $tablename";
			execute_sql($sql);
			while (($newArray = sql_fetch_array()) != false)
				$ret = 1 + $newArray[0]; # see notes above
		}
	}
	return $ret;
}


//function sql_table_field_names($table)
//{
//	$sql = "SHOW COLUMNS FROM $table";
//	sql_execute($sql);
//	$fields = array();
//	while (($newArray = sql_fetch_assoc()) != false)
//		$fields[] = $newArray['Field'];
//	return $fields;
//}

function ms_sql_connect()
{
	# NOTE: This should only be called from sql_connect()

	global $login_debug; # settings.php
	global $ms_sql_conn;
	global $ms_sql_host;
	global $ms_sql_password;
	global $ms_sql_username;
	global $sql_key;

	if ($login_debug)
		log_write("Calling odbc_connect($ms_sql_host, $ms_sql_username, $ms_sql_password)");
	$ms_sql_conn = odbc_connect($ms_sql_host, $ms_sql_username, $ms_sql_password, SQL_CURSOR_FORWARD_ONLY);
	if ($ms_sql_conn)
	{
		$sql = "OPEN MASTER KEY DECRYPTION BY PASSWORD = '$sql_key'";
		#log_write($sql);#
		#dprint($sql, true);#
		sql_execute($sql);
	}
	else
		log_write("ms_sql_connect(): odbc_connect($ms_sql_host, $ms_sql_username) failed", true); # only if open
	#else
	#	log_write("ms_sql_connect(): odbc_connect() returned " . print_r($ms_sql_conn,1), true); # only if open
	return $ms_sql_conn;
}

function ms_sql_disconnect()
{
	# NOTE: This should only be called from sql_disconnect()

	global $ms_sql_conn;

	sql_execute("CLOSE ALL SYMMETRIC KEYS");
	odbc_close($ms_sql_conn);
	$ms_sql_conn = '';
}

function ms_sql_execute($sql, $audit_it)
{
	# NOTE: This should only be called from sql_execute()

	global $ms_sql_conn;
	global $ms_sql_result;
	global $USER;

	$problem = '';
	if ($ms_sql_conn)
	{
		$ms_sql_result = odbc_exec($ms_sql_conn, $sql);
		if (!$ms_sql_result)
			$problem = 'exe';
	}
	else
		$problem = 'con';

	if (!$problem)
	{
		if (strtolower(substr($sql, 0, strlen("insert"))) == "insert")
			$insert_id = sql_lastID();
		else
			$insert_id = 0;
		if ($audit_it)
			audit_add_record($insert_id); # recursion
		return $insert_id;
	}

	# Problem was found - log it then exit

	$sql = sql_dekey($sql);
	print $sql;
	$error = sql_dekey("eNum=(" . odbc_error() . ") eTxt=(" . odbc_errormsg() . ")");
	if ($problem == 'con')
		$problem = "No MS-SQL DB connection exists";
	elseif ($problem == 'exe')
		$problem = "MS-SQL Exec Error: $error";
	else
		$problem = '?';
	log_write("ms_sql_execute($sql): $problem", true); # only if open
	log_close();
	print("MS-SQL ERROR (" . strftime('%Y-%m-%d %H:%M:%S') . "/{$USER['USER_ID']}) - check log file");
	exit();
}

function ms_sql_fetch()
{
	# NOTE: This should only be called from sql_fetch()

	global $ms_sql_result;
	#global $sql_fetch_debug;

	#if ($sql_fetch_debug)
	#	dprint("\$ms_sql_result=$ms_sql_result=" . print_r($ms_sql_result,1) . ", num_rows=" . odbc_num_rows($ms_sql_result) . ", num_fields=" . odbc_num_fields($ms_sql_result));

	if (
		(0 <= odbc_num_rows($ms_sql_result)) &&
		(0 <= odbc_num_fields($ms_sql_result)) &&
		odbc_fetch_row($ms_sql_result)
		)
	{
		$result_array = array();
		$fi_max = odbc_num_fields($ms_sql_result);
		for ($fi = 1; $fi <= $fi_max; $fi++)
			$result_array[] = odbc_result($ms_sql_result, $fi);
		return $result_array;
	}
	else
		return false;
}

function ms_sql_fetch_assoc()
{
	# NOTE: This should only be called from sql_fetch_assoc()

	global $ms_sql_result;
	#global $time_tests; # settings.php

	#if ($time_tests)
	#	$t_start = time();
	$rc = odbc_fetch_array($ms_sql_result);
	#if ($time_tests)
	#{
	#	$t_length = time() - $t_start;
	#	log_write("ms_sql_fetch_assoc() took $t_length seconds to run", true, true);
	#}
	return $rc;
}

function ms_sql_encrypt($field_value, $got_quotes, $ms_table)
{
	# NOTE: This should only be called from sql_encrypt()

	if (!$ms_table)
		return 'ENCRYPT ERROR NO TABLE';
	if ($field_value == 'NULL')
		return $field_value;
	if (!$got_quotes)
		$field_value = quote_smart($field_value, true);
	return "ENCRYPTBYKEY(KEY_GUID('{$ms_table}_KEY'), $field_value)";
}

function ms_sql_decrypt($field_name, $type, $add_alias)
{
	# NOTE: This should only be called from sql_decrypt()

	global $v_text_clip; # settings.php

	if (!$type)
		$type = "VARCHAR($v_text_clip)";

	$alias = '';
	if ($add_alias)
	{
		$bits = explode('.', $field_name); # separate table name from field name
		if (count($bits) == 1)
			$alias = $bits[0];
		else
			$alias = $bits[1];
		$alias = " AS $alias";
	}

	return "CONVERT($type, DECRYPTBYKEY($field_name)) $alias";
}

function ms_sql_invalid_cursor_state_fix()
{
	# NOTE: This should only be called from sql_invalid_cursor_state_fix()

	global $ms_sql_result;

	$solution = 1;
	if ($solution == 1)
		odbc_free_result($ms_sql_result);
	elseif ($solution == 2)
	{
		disconnect_sql();
		connect_sql();
	}
}

function my_sql_invalid_cursor_state_fix()
{
	# NOTE: This should only be called from sql_invalid_cursor_state_fix()
	return;
}

function select_all_decrypted($table, $order_by=false, $not_deleted=false)
{
	# Return a SQL command that will return "*" from $table but with encrypted fields decrypted.

	if (encrypted_table($table))
		sql_encryption_preparation($table);

	$sql = "SELECT ";
	$fields = sql_table_field_names($table);
	$fields2 = array();
	$found_deleted = false;
	foreach ($fields as $one)
	{
		$do_field = true;
		if ($one == 'DELETED')
		{
			$found_deleted = true;
			if ($not_deleted)
				$do_field = false;
		}
		if ($do_field)
		{
			if (encrypted_field($table, $one))
				$fields2[] = sql_decrypt($one, '', true);
			else
				$fields2[] = $one;
		}
	}
	$sql .= implode(', ', $fields2) . " FROM $table WHERE "; # caller may add WHERE clauses
	if ($not_deleted && $found_deleted)
		$sql .= "(DELETED=0) ";
	else
		$sql .= "(1=1) "; # caller may add more WHERE clauses
	if ($order_by)
		$sql .= "ORDER BY $order_by";
	return $sql;
}

function sqla($x)
{
	# This function is needed for SQL Server.
	# If you do "SELECT A.FRED AS BOB" in SQL Server, it returns a field called FRED, not BOB.
	# This problem does not occur with MySQL.
	# So we have to replace "A.FRED" with something more complicated, to force SQL Server to use the alias.
	# KDB 25/11/15
	global $mysql_server; # settings.php
	return ($mysql_server ? $x : "CASE WHEN $x IS NULL THEN NULL ELSE $x END");
}

function date_format_sql($field_name, $time='', $no_as=false, $pretty=false)
{
	# $pretty: 1st December 2010 or 20th December 2010 (or "Dec" for MS SQL Server)
	# else: 01/12/2010 or 20/12/2010
	global $mysql_server; # settings.php

	$bits = explode('.', $field_name);
	$out = $bits[count($bits)-1];
	if ($mysql_server)
	{
		$format = ($pretty ? "%D %M %Y" : "%d/%m/%Y");
		if ($time == 'hm')
			$format .= " %H:%i";
		elseif ($time == 'hms')
			$format .= " %H:%i:%S";
		$date_string = "DATE_FORMAT($field_name, '$format')";
		$date_string=$date_string; # keep stupid code-checker quiet
	}
	else
	{
		$format = ($pretty ? "106" : "103");
		if ($time == 'hm')
			$time_format = "108"; # hh:mm:ss - can't find hh:mm for SQL Server
		elseif ($time == 'hms')
			$time_format = "108";
		else
			$time_format = '';
		if ($time_format == '')
			$date_string = "CONVERT(VARCHAR(50), $field_name, $format)";
		else
			#$date_string = "CONCAT(CONVERT(VARCHAR(50), $field_name, $format), \" \", " .
			#				"CONVERT(VARCHAR(10), $field_name, $time_format))";
			$date_string = "CONVERT(VARCHAR(50), $field_name, $format) + ' ' + CONVERT(VARCHAR(10), $field_name, $time_format)";
	}
	return $date_string . ($no_as ? '' : " AS $out");
}

//function sql_limits($limit)
//{
//    # Return strings to insert into SQL statement to limit number of records returned.

//    global $limit_ms;
//    global $limit_my;
//    global $mysql_server; # settings.php

//    if ($mysql_server)
//    {
//        $limit_ms = '';
//        $limit_my = "LIMIT $limit";
//    }
//    else
//    {
//        $limit_ms = "TOP $limit";
//        $limit_my = '';
//    }
//}

function sql_lastID()
{
	global $ms_sql_conn;
	global $mysql_server; # settings.php
	global $my_sql_conn;

	if ($mysql_server)
	{
		return mysqli_insert_id($my_sql_conn);
	}
	else
	{
		$result = odbc_exec($ms_sql_conn, "SELECT @@IDENTITY AS ID");
		$array = odbc_fetch_array($result);
    	return $array['ID'];
	}
}

function sql_encryption_preparation($table)
{
	global $enc_prep_tables; # settings.php
	global $mysql_server; # settings.php
	global $time_tests;

	if ($time_tests) log_write("Entering sql_encryption_preparation($table)", false, true);
	if (!$mysql_server)
	{
		if (!in_array($table, $enc_prep_tables))
		{
			# MS SQL Server - prepare for decryption
			$sql = "OPEN SYMMETRIC KEY {$table}_KEY DECRYPTION BY CERTIFICATE EncryptTestCert";
			#log_write($sql);#
			#dprint($sql);#
			if ($time_tests) log_write("   ...executing: $sql", false, true);
			sql_execute($sql);
			$enc_prep_tables[] = $table;
		}
	}
	if ($time_tests) log_write("   ...exit sql_encryption_preparation($table)", false, true);
}

function sql_select_single($sql)
{
	sql_execute($sql);
	$temp = '';
	while (($newArray = sql_fetch()) != false)
		$temp = $newArray[0];
	sql_free_result();
	return $temp;
}

function sql_top_limit($num)
{
	global $mysql_server; # settings.php
	if ($mysql_server)
	{
		$ms_top = "";
		$my_limit = "LIMIT $num";
	}
	else
	{
		$ms_top = "TOP $num";
		$my_limit = "";
	}
	return array($ms_top, $my_limit);
}

function sql_date_add($date, $unit, $count)
{
	# Return SQL that will add $count $unit's to $date.
	# E.g. to add 30 days to table field MY_DATE, call: sql_date_add('MY_DATE', 'DAY', 30);
	# Return one of:
	#	MySQL:		DATE_ADD(MY_DATE, INTERVAL 30 DAY)
	#	MS SQL:		DATEADD(DAY, 30, MY_DATE)

	global $mysql_server; # settings.php
	if ($mysql_server)
		$rc = "DATE_ADD($date, INTERVAL $count $unit)";
	else
		$rc = "DATEADD($unit, $count, $date)";
	return $rc;
}

function sql_date_diff($date_1, $date_2)
{
	# Return SQL that will return the number of days between two dates. Note: only DAY is supported, not YEAR or MONTH etc.
	# E.g. to get the number of days between table fields MY_DT1 and MY_DT2, call: sql_date_diff('MY_DT1', 'MY_DT2');
	# Maths: Result = $date_2 - $date_1; i.e. +ve if $date_2 is a date after $date_1, otherwise -ve.
	# Return one of:
	#	MySQL:		DATEDIFF(MY_DT2, MY_DT1)
	#	MS SQL:		DATEDIFF(DAY, MY_DT1, MY_DT2)

	global $mysql_server; # settings.php
	if ($mysql_server)
		$rc = "DATEDIFF($date_2, $date_1)";
	else
		$rc = "DATEDIFF(DAY, $date_1, $date_2)";
	return $rc;
}

function sql_convert($field, $type, $ms_char_size)
{
	# Return SQL that will convert the given table field into the given type.
	# E.g. to convert the table field MY_NUM into a string (a 100-character string in MS SQL), call: sql_convert('MY_NUM', 'CHAR', 100);
	# Return one of:
	#	MySQL:		CONVERT(MY_NUM, CHAR)
	#	MS SQL:		CONVERT(VARCHAR(100), MY_NUM)


	global $mysql_server; # settings.php

	$type = strtoupper($type);
	if ($mysql_server)
	{
		if ($type == 'VARCHAR')
			$type = 'CHAR';
		$rc = "CONVERT($field, $type)";
	}
	else
	{
		if (($type == 'VARCHAR') || ($type == 'CHAR'))
		{
			$ms_char_size = intval($ms_char_size);
			if ($ms_char_size <= 0)
				$ms_char_size = 50;
			$type = "VARCHAR($ms_char_size)";
		}
		$rc = "CONVERT($type, $field)";
	}
	return $rc;
}

?>
