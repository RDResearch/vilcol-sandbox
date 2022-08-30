<?php

# Called from auto_portal.php

include_once("settings.php");
include_once("library.php");
include_once("lib_portal.php");

error_reporting(E_ALL);

$debug = true; #[REVIEW]
$output = '';
$error = '';
$operation = stripcslashes($_GET['op']);
if ($operation == 'check')
{
	if (sql_connect()) # main system database
	{
		$output .= "<p>Connected to main DB</p>"
			;

		if (por_sql_connect())
		{
			$output .= "<p>Connected to portal DB</p>"
				;

			$output .= "<h2 style=\"color:blue\">Stage 1 - Clients</h2>";
			pa_check_clients();

			$output .= "<h2 style=\"color:blue\">Stage 2 - Users</h2>";
			pa_check_users();

			$output .= "<h2 style=\"color:blue\">Stage 3 - Tasks</h2>";
			pa_check_tasks();

			por_sql_disconnect();
			$output .= "<p>Disconnected from portal DB</p>"
				;
		}
		else
		{
			$error = 'error';
			$output .= "<p style=\"color:red;\">FAILED TO CONNECT TO PORTAL DATABASE</p>"
				;
		}
		sql_disconnect();
		$output .= "<p>Disconnected from main DB</p>"
			;
	}
	else
	{
		$error = 'error';
		$output .= "<p style=\"color:red;\">FAILED TO CONNECT TO MAIN DATABASE</p>"
			;
	}

} # check

//elseif ($operation == 'copy_users')
//{
//	include_once("settings.php");
//	include_once("library.php");
//	global $portal_users;
//	global $tr_colour_1;
//	global $tr_colour_2;
//
//	sql_connect(); # main system
//	sql_get_portal_users();
//	sql_disconnect(); # main system
//
//	$grey = "style=\"color:gray;\"";
//	$output .= "
//	<table name=\"table_users\" class=\"spaced_table\" border=\"0\">
//	<tr>
//		<th $col2>Client</th><th>Login</th><th>Name</th><th>Init.</th><th>Email</th><th>Enabled</th><th>Locked Out</th><th $grey>DB ID</th>
//	</tr>
//	";
//	$trcol = $tr_colour_1;
//	foreach ($portal_users as $one_user)
//	{
//		$enabled = ($one_user['IS_ENABLED'] ? 'Yes' : 'No');
//		$locked = ($one_user['IS_LOCKED_OUT'] ? 'Yes' : 'No');
//		$blocked = (((!$one_user['IS_ENABLED']) || $one_user['IS_LOCKED_OUT']) ? true : false);
//		$output .= "
//			<tr bgcolor=\"$trcol\" onmouseover=\"this.className='Highlight'\" onmouseout=\"this.className='Normal'\"" .
//					($blocked ? "style=\"color:red;\"" : '') . ">
//				<td $ar>{$one_user['C_CODE']}</td>
//				<td>{$one_user['C_CO_NAME']}</td>
//				<td>{$one_user['USERNAME']}</td>
//				<td>{$one_user['U_FIRSTNAME']} {$one_user['U_LASTNAME']}</td>
//				<td>{$one_user['U_INITIALS']}</td>
//				<td>{$one_user['U_EMAIL']}</td><td>$enabled</td><td>$locked</td>
//				<td $grey $ar>{$one_user['USER_ID']}</td>
//			</tr>
//			";
//			$trcol = (($trcol == $tr_colour_1) ? $tr_colour_2 : $tr_colour_1);
//	}
//	$output .= "
//	</table><!--table_users-->
//	";
//} # copy_users

else
{
	$error = 'error';
	$output .= "portal_ajax.php: operation \"$operation\" unrecognised";
}

if (!$error)
	$error = 'ok';
print "{$error}|{$output}";

function pa_check_clients()
{
	global $debug;
	global $output;
	global $site_local; # site.php

	# === First, get clients from main system database that need to be copied to portal database ========================


	$exclude_codes = '';
	$sql = "SELECT C_CODE, COUNT(*)
			FROM CLIENT2
			GROUP BY C_CODE
			HAVING 1 < COUNT(*)";
	#$output .= "<p>$sql</p>";
	sql_execute($sql); # main system database
	$exclude_codes = array();
	while (($newArray = sql_fetch_assoc()) != false)
		$exclude_codes[] = $newArray['C_CODE'];
	if ($exclude_codes)
		$exclude_codes = "(CL.C_CODE NOT IN(" . implode(',', $exclude_codes) . "))";
	else
		$exclude_codes = " ";

	sql_encryption_preparation('CLIENT2'); # main system database
	list($ms_top, $my_limit) = sql_top_limit(6);
	$sql = "SELECT " . ($debug ? "$ms_top " : '') . "CL.CLIENT2_ID, CL.C_CODE, " . sql_decrypt('CL.C_CO_NAME', '', true) . ",
					CL.C_TRACE, CL.C_COLLECT, CL.C_ARCHIVED, CL.PORTAL_PUSH
			FROM CLIENT2 AS CL
			WHERE PORTAL_PUSH=1 " . ($exclude_codes ? "AND $exclude_codes" : '') .
				($debug ? " AND (CL.C_CODE <= " . ($site_local ? '101' : '10600') . ") " : '') . "
			ORDER BY C_CODE " . ($debug ? $my_limit : '') . "
			";
	$clients = array();
	if ($debug)
		$output .= "<p>" . por_sql_dekey($sql) . "</p>";
	sql_execute($sql); # main system database
	while (($newArray = sql_fetch_assoc()) != false)
		$clients[] = $newArray;
	if ($debug)
		$output .= "<p>Found " . count($clients) . " clients: " . print_r($clients,1) . "</p>";

	# === Second, copy clients to portal database ========================

	$pushed = array();
	foreach ($clients as $one_client)
	{
		$client2_id = $one_client['CLIENT2_ID'];
		$c_enabled = ($one_client['C_ARCHIVED'] ? '0' : '1');

		$sql = "SELECT COUNT(*) AS COUNT_STAR FROM POR_CLIENT2 WHERE CLIENT2_ID=$client2_id";
		if (por_sql_execute($sql) == -1)
			$output .= "<p>SQL Error from: $sql</p>";
		$count = 0;
		while (($newArray = por_sql_fetch_assoc()) != false)
			$count = $newArray['COUNT_STAR'];
		if ($count)
		{
			$sql = "UPDATE POR_CLIENT2 SET
						C_CODE = {$one_client['C_CODE']},
						C_CO_NAME = " . por_sql_encrypt($one_client['C_CO_NAME'], false) . ",
						C_TRACE = {$one_client['C_TRACE']},
						C_COLLECT = {$one_client['C_COLLECT']},
						C_ENABLED = $c_enabled
					WHERE CLIENT2_ID=$client2_id";
			if ($debug)
				$output .= "<p>" . por_sql_dekey($sql) . "</p>";
			if (por_sql_execute($sql) == -1)
				$output .= "<p>SQL Error from: $sql</p>";

			$pushed[] = $client2_id;
		}
		else
		{
			$sql = "INSERT INTO POR_CLIENT2 (CLIENT2_ID,  C_CODE,                      C_CO_NAME,                                            C_TRACE,                  C_COLLECT,                  C_ENABLED  )
									VALUES  ($client2_id, {$one_client['C_CODE']}, " . por_sql_encrypt($one_client['C_CO_NAME'], false) . ", {$one_client['C_TRACE']}, {$one_client['C_COLLECT']}, $c_enabled )
					";
			if ($debug)
				$output .= "<p>" . por_sql_dekey($sql) . "</p>";
			if (por_sql_execute($sql) == -1)
				$output .= "<p>SQL Error from: $sql</p>";

			$pushed[] = $client2_id;
		}

	} # foreach ($clients as $one_client)

	# === Third, set PORTAL_PUSH to zero on main database =================================

	foreach ($pushed as $client2_id)
	{
		$sql = "UPDATE CLIENT2 SET PORTAL_PUSH=0 WHERE CLIENT2_ID=$client2_id";
		if ($debug)
			$output .= "<p>$sql</p>";
		audit_setup_client($client2_id, 'CLIENT2', 'CLIENT2_ID', $client2_id, 'PORTAL_PUSH', 0);
		sql_execute($sql, true); # audited
	}

} # pa_check_clients()

function pa_check_users()
{
	# Find users on main database that are portal users i.e. CLIENT2_ID NOT NULL and need copying i.e. PORTAL_PUSH=1.
	# Only copy them if their client exists on the portal database.
	# Set PORTAL_PUSH=0 after successful copy.

	global $debug;
	global $output;

	# === First, get portal users from main system database that need to be copied to portal database ========================

	sql_encryption_preparation('USERV'); # main system database
	$sql = "SELECT USER_ID, CLIENT2_ID, " . sql_decrypt('USERNAME', '', true) . ", " . sql_decrypt('PASSWORD', '', true) . ",
					U_FIRSTNAME, " . sql_decrypt('U_LASTNAME', '', true) . ", U_INITIALS, " . sql_decrypt('U_EMAIL', '', true) . ",
					IS_ENABLED, IS_LOCKED_OUT, FAILED_LOGINS, CREATED_DT, U_FIRST_DT, U_LAST_DT, U_DEBUG, USER_KEY, UKEY_DT, PORTAL_PUSH
			FROM USERV
			WHERE CLIENT2_ID IS NOT NULL AND PORTAL_PUSH=1
			ORDER BY USER_ID";
	$users = array();
	if ($debug)
		$output .= "<p>" . por_sql_dekey($sql) . "</p>";
	sql_execute($sql); # main system database
	while (($newArray = sql_fetch_assoc()) != false)
		$users[] = $newArray;
	if ($debug)
	{
		#$output .= "<p>Found " . count($users) . " users: " . print_r($users,1) . "</p>";
		$output .= "<p>Found " . count($users) . " users that need pushing</p>";
	}

	# === Second, copy users to portal database ========================

	$pushed = array();
	$no_client_count = 0;
	foreach ($users as $one_user)
	{
		$user_id = $one_user['USER_ID'];
		$client2_id = $one_user['CLIENT2_ID'];
		$u_firstname = ($one_user['U_FIRSTNAME'] ? quote_smart($one_user['U_FIRSTNAME'], true) : 'NULL');
		$u_initials = quote_smart($one_user['U_INITIALS'], true);

		# Only process user if their client is already in the database
		if ($client2_id < 0)
			$count = 1; # Special users: Kevin (-1), Gina (-2) and Steve (-3)
		else
		{
			$sql = "SELECT COUNT(*) AS COUNT_STAR FROM POR_CLIENT2 WHERE CLIENT2_ID=$client2_id";
			if (por_sql_execute($sql) == -1)
				$output .= "<p>SQL Error from: $sql</p>";
			$count = 0;
			while (($newArray = por_sql_fetch_assoc()) != false)
				$count = $newArray['COUNT_STAR'];
		}
		if ($count)
		{
			$sql = "SELECT COUNT(*) AS COUNT_STAR FROM POR_USERV WHERE USER_ID=$user_id";
			if (por_sql_execute($sql) == -1)
				$output .= "<p>SQL Error from: $sql</p>";
			$count = 0;
			while (($newArray = por_sql_fetch_assoc()) != false)
				$count = $newArray['COUNT_STAR'];
			if ($count)
			{
				$sql = "UPDATE POR_USERV SET
							CLIENT2_ID = $client2_id,
							USERNAME = " . por_sql_encrypt($one_user['USERNAME'], false) . ",
							PASSWORD = " . por_sql_encrypt($one_user['PASSWORD'], false) . ",
							U_FIRSTNAME = $u_firstname,
							U_LASTNAME = " . por_sql_encrypt($one_user['U_LASTNAME'], false) . ",
							U_INITIALS = $u_initials,
							U_EMAIL = " . por_sql_encrypt($one_user['U_EMAIL'], false) . ",
							IS_ENABLED = {$one_user['IS_ENABLED']},
							IS_LOCKED_OUT = {$one_user['IS_LOCKED_OUT']},
							U_DEBUG = {$one_user['U_DEBUG']}
						WHERE USER_ID=$user_id";
				if ($debug)
					$output .= "<p>" . por_sql_dekey($sql) . "</p>";
				if (por_sql_execute($sql) == -1)
					$output .= "<p>SQL Error from: $sql</p>";

				$pushed[] = $user_id;
			}
			else
			{
				$sql = "INSERT INTO POR_USERV (USER_ID,  CLIENT2_ID,                    USERNAME,                                              PASSWORD,                                          U_FIRSTNAME,      U_LASTNAME,                                          U_INITIALS,      U_EMAIL,                                          IS_ENABLED,                IS_LOCKED_OUT,                FAILED_LOGINS, CREATED_DT,  U_FIRST_DT, U_LAST_DT, U_DEBUG,                USER_KEY, UKEY_DT, U_ROLE)
									VALUES	  ($user_id, {$one_user['CLIENT2_ID']}, " . por_sql_encrypt($one_user['USERNAME'], false) . ", " . por_sql_encrypt($one_user['PASSWORD'], false) . ", $u_firstname, " . por_sql_encrypt($one_user['U_LASTNAME'], false) . ", $u_initials, " . por_sql_encrypt($one_user['U_EMAIL'], false) . ", {$one_user['IS_ENABLED']}, {$one_user['IS_LOCKED_OUT']}, 0,             NOW(),       NULL,       NULL,      {$one_user['U_DEBUG']}, 0,        NULL,    'U'   )
						";
				if ($debug)
					$output .= "<p>" . por_sql_dekey($sql) . "</p>";
				if (por_sql_execute($sql) == -1)
					$output .= "<p>SQL Error from: $sql</p>";

				$pushed[] = $user_id;
			}
		}
		else
		{
			if ($debug)
				$output .= "<p>User's client not in portal database so user not copied: " . print_r($one_user,1) . "</p>";
			$no_client_count++;
		}
	} # foreach ($users as $one_user)

	if ($debug)
	{
		$pushed_count = count($pushed);
		if ($pushed_count)
		{
			$output .= "<p>" . count($pushed) . " users pushed to portal";
			if ($no_client_count)
				$output .= " (but $no_client_count users not pushed because client not in portal database)";
			$output .= "</p>";
		}
		else
		{
			$output .= "<p>No users pushed to portal";
			if ($no_client_count)
				$output .= " ($no_client_count users not pushed because client not in portal database)";
			$output .= "</p>";
		}
	}

	# === Third, set PORTAL_PUSH to zero on main database =================================

	foreach ($pushed as $user_id)
	{
		$sql = "UPDATE USERV SET PORTAL_PUSH=0 WHERE USER_ID=$user_id";
		if ($debug)
			$output .= "<p>$sql</p>";
		audit_setup_user($user_id, 'USERV', 'USER_ID', $user_id, 'PORTAL_PUSH', 0);
		sql_execute($sql, true); # audited
	}

} # pa_check_users()

function pa_check_tasks()
{
	global $debug;
	global $output;
	global $task_type_account;
	global $task_type_csv;
	global $task_type_job_status;
	global $task_type_new_job;

	$loop_max = 100; # prevent infinite-looping
	$done_count = 0;
	for ($loopy = 0; $loopy < $loop_max; $loopy++)
	{
		$sql = "SELECT TREQ_STATUS, COUNT(*) AS S_COUNT
				FROM POR_TASK_REQUEST
				WHERE TREQ_STATUS IN ('N','P')
				GROUP BY TREQ_STATUS";
		if (por_sql_execute($sql) == -1)
			$output .= "<p>SQL Error from: $sql</p>";
		$count_n = 0;
		$count_p = 0;
		while (($newArray = por_sql_fetch_assoc()) != false)
		{
			if ($newArray['TREQ_STATUS'] == 'N')
				$count_n = $newArray['S_COUNT'];
			elseif ($newArray['TREQ_STATUS'] == 'P')
				$count_p = $newArray['S_COUNT'];
		}
		if (($count_n == 0) || (0 < $count_p))
		{
			if ($debug)
				$output .= "<p>Aborting pa_check_tasks() (loopy=$loopy) because count_n=$count_n and count_p=$count_p</p>";
			break; # out of: for ($loopy)
		}

		$sql = "UPDATE POR_TASK_REQUEST SET TREQ_STATUS='P' WHERE TREQ_STATUS='N'";
		if ($debug)
			$output .= "<p>After count_n=$count_n: $sql</p>";
		if (por_sql_execute($sql) == -1)
			$output .= "<p>SQL Error from: $sql</p>";

		$sql = "SELECT T.TASK_REQUEST_ID, T.TREQ_TYPE, " . por_sql_decrypt('T.TREQ_DATA', '', true) . ", " . por_sql_decrypt('T.TREQ_CSV', '', true) . ",
						T.TREQ_DT, T.TREQ_ST_DT, T.TREQ_STATUS, " . por_sql_decrypt('T.TREQ_OUTPUT', '', true) . ",
						T.TREQ_USER_ID, " . por_sql_decrypt('U.USERNAME', '', true) . "
				FROM POR_TASK_REQUEST AS T
				INNER JOIN POR_USERV AS U ON U.USER_ID=T.TREQ_USER_ID
				WHERE T.TREQ_STATUS='P'
				ORDER BY T.TASK_REQUEST_ID;";
		#$output .= "<p>" . por_sql_dekey($sql) . "</p>";
		#$output .= "<p>Executing...</p>";
		if (por_sql_execute($sql) == -1)
			$output .= "<p>SQL Error from: $sql</p>";
		#$output .= "<p>Fetching...</p>";
		$task_requests = array();
		while (($newArray = por_sql_fetch_assoc()) != false)
			$task_requests[] = $newArray;
		#$output .= "<p>Found: " . print_r($task_requests,1) . "</p>";

		if ($task_requests)
		{
			$output .= "
			<p>All Pending task requests in POR_TASK_REQUEST (" . count($task_requests) . "):</p>
			<table border=\"1\">
			<tr>
				<th>Type</th><th>Submitted</th><th>Status</th><th>When</th><th>Data</th><th>CSV</th><th>User</th><th>Output</th><th>T.R.ID</th>
			</tr>
			";
			foreach ($task_requests as $one_tr)
			{
				$data = explode('	', $one_tr['TREQ_DATA']);
				$data = implode('<br>', $data);
				$output .= "
				<tr>
					<td>{$one_tr['TREQ_TYPE']}</td>
					<td>{$one_tr['TREQ_DT']}</td>
					<td>{$one_tr['TREQ_STATUS']}</td>
					<td>{$one_tr['TREQ_ST_DT']}</td>
					<td>{$data}</td>
					<td>{$one_tr['TREQ_CSV']}</td>
					<td>{$one_tr['USERNAME']} ({$one_tr['TREQ_USER_ID']})</td>
					<td>{$one_tr['TREQ_OUTPUT']}</td>
					<td>{$one_tr['TASK_REQUEST_ID']}</td>
				</tr>
				";
			}
			$output .= "
			</table>
			";

			foreach ($task_requests as $one_tr)
			{
				if ($one_tr['TREQ_TYPE'] == $task_type_new_job)
				{
					process_new_job($one_tr);
					$done_count++;
				}
				elseif ($one_tr['TREQ_TYPE'] == $task_type_job_status)
				{
					process_job_status($one_tr);
					$done_count++;
				}
				elseif ($one_tr['TREQ_TYPE'] == $task_type_csv)
				{
					process_csv($one_tr);
					$done_count++;
				}
				elseif ($one_tr['TREQ_TYPE'] == $task_type_account)
				{
					process_account($one_tr);
					$done_count++;
				}
				else
				{
					$output .= "<p style=\"color:red;\">ERROR: task request type unknown: " . print_r($one_tr,1) . "</p>";
					process_error($one_tr);
				}
			}
		}
		else
		{
			$output .= "<p style=\"red\">ERROR: count_n=$count_n, all changed to P, but now have found no task requests in POR_TASK_REQUEST!</p>";
			break; # out of: for ($loopy)
		}

	} # for ($loopy)
	$output .= "<p>pa_check_tasks() finished, loopy=$loopy, done_count=$done_count</p>";

} # pa_check_tasks()

function process_new_job($task_req)
{
	global $output;

	$output .= "<p>process_new_job(" . print_r($task_req,1) . ")</p>";
} # process_new_job()

function process_job_status($task_req)
{
	global $output;

	$output .= "<p>process_job_status(" . print_r($task_req,1) . ")</p>";
} # process_job_status()

function process_csv($task_req)#[REVIEW]
{
	global $output;

	$output .= "<p>process_csv(" . print_r($task_req,1) . ")</p>";

	# Copy record and put into audit trail
	# [REVIEW]
	task_insert($task_req);

	# Get name of CSV file from $task_req.
	# Read that CSV file line by line.



} # process_csv()

function process_account($task_req)
{
	global $output;

	$output .= "<p>process_account(" . print_r($task_req,1) . ")</p>";
} # process_account()

function process_error($task_req)
{
	global $output;

	$output .= "<p>process_error(" . print_r($task_req,1) . ")</p>";

	$sql = "UPDATE POR_TASK_REQUEST SET TREQ_STATUS='X' WHERE TASK_REQUEST_ID={$task_req['TASK_REQUEST_ID']}";
	$output .= "<p>Error task: $sql</p>";
	if (por_sql_execute($sql) == -1)
		$output .= "<p>SQL Error from: $sql</p>";

} # process_error()

function task_insert($task_req)
{
	# $task_req is a task copied from client database's POR_TASK_REQUEST.
	# Insert this into TASK_REQUEST, and audit the insertion.

} # task_insert()

?>
