<?php

# Used by portal_ajax.php
# Functions for accessing the portal database from the main system.

include_once('site.php');

$por_sql_conn = '';
$por_sql_result = '';
$por_sql_key = "R73gw9B"; # The DCE key for the test portal. The real portal might or might not use the Vilcol key.

# *NOTE* This bunch needs to be the same as DCE's portal.php
$task_type_new_job    = 'NJ';
$task_type_job_status = 'JS';
$task_type_csv        = 'CS';
$task_type_account    = 'AC';
$task_types = array(
		$task_type_new_job => 'New Job',
		$task_type_job_status => 'Job Status',
		$task_type_csv => 'CSV Upload',
		$task_type_account => 'Account Statement'
	);
# End of bunch

function por_sql_connect()
{
	global $por_sql_conn;
	global $site_local; # site.php
	
	if ($site_local)
	{
		$sql_username = '';
		$sql_password = '';
	}
	else
	{
		$sql_username = 'dceswaffham';
		$sql_password = 'b1g*Tube5!';
	}
	$sql_host = 'localhost';
	$sql_database = 'dcephpdb';
	$por_sql_conn = mysqli_connect($sql_host, $sql_username, $sql_password, $sql_database);
	#log_write("mysqli_connect($sql_host, $sql_username, $sql_password, $sql_database)");#
	if ($por_sql_conn)
		return true;
	return false;
	
} # por_sql_connect()

function por_sql_disconnect()
{
	global $por_sql_conn;
	
	mysqli_close($por_sql_conn);
	$por_sql_conn = '';
} # por_sql_disconnect()

function por_sql_execute($sql, $audit_it=false)
{
	global $por_sql_conn;
	global $por_sql_result;
	global $USER;
	
	$problem = '';
	if ($por_sql_conn)
	{
		$por_sql_result = mysqli_query($por_sql_conn, $sql);
		if (!$por_sql_result)
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
	$sql = por_sql_dekey($sql);
	$error = ($por_sql_conn ? sql_dekey(mysqli_errno($por_sql_conn)) : -9999);
	if ($problem == 'con')
		$problem = "No DB connection exists";
	elseif ($problem == 'exe')
		$problem = "SQL Exec Error = \"$error\" = \"" . sql_dekey(mysqli_error($por_sql_conn)) . "\"";
	else 
		$problem = '?';
	$problem = "sql_execute($sql): $problem";
	if (log_write($problem, true))
		print("SQL ERROR (" . strftime_rdr('%Y-%m-%d %H:%M:%S') . "/{$USER['USER_ID']}) - check log file");
	else
		print("SQL ERROR (" . strftime_rdr('%Y-%m-%d %H:%M:%S') . "/{$USER['USER_ID']}) - $problem");
	log_close();
	return -1;
	
} # por_sql_execute()

function por_sql_fetch_assoc()
{
	global $por_sql_result;

	return mysqli_fetch_array($por_sql_result, MYSQLI_ASSOC);
	
} # por_sql_fetch_assoc()

function por_sql_decrypt($field_name, $type, $add_alias)
{
	global $por_sql_key;

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
	return "CONVERT(AES_DECRYPT($field_name,'$por_sql_key'),$type)$alias";
} # por_sql_decrypt()

function por_sql_encrypt($field_value, $got_quotes)
{
	global $por_sql_key;
	
	if ($field_value == 'NULL')
		return $field_value;
	if (!$got_quotes)
		$field_value = "'$field_value'";
	return "AES_ENCRYPT($field_value,'$por_sql_key')";
} # por_sql_encrypt

function por_sql_dekey($sql)
{
	# Obscure encryption key before debug-prints

	global $por_sql_key;
	
	$subkey = substr($por_sql_key, 0, 4);
	return str_replace($subkey, '_k_', str_replace($por_sql_key,'_key_',$sql));
}

?>
