<?php

/*

Port MS SQL Server database to MySQL database. From 06/06/19.

Live port: 03/08/19, see Word doc "Vilcol port".

Procedure:
 		- Click button to delete all MySQL tables.
 		- Click button to tick all tables.
		- Click button to port all ticked.
 		- Once screen comes back (after 110 minutes), refresh screen to port all ticked again (no need to untick or retick any tables).
 		- Keep doing this until all tables ported - all MySQL tables should have same number of records as MS SQL tables.
 		- Note that this script makes no changes whatsoever to MS SQL database tables.

*/

/*

OLD NOTES:

10/07/19: It looks like this will have to be run in stages due to it taking approx 10 to 14 hours, and the server timing out.
 		E.g. do AUDIT table first, then do all other tables together.

Error during overnight run of port-all on 09/07/19 and 10/07/19 :
 		[Tue Jul 09 23:22:51 2019] [warn] [client 82.28.213.108] (104)Connection reset by peer: mod_fcgid: error reading data from FastCGI server, referer: https://www.vilcoldb.com/admin/port.php
 		[Tue Jul 09 23:22:51 2019] [warn] [client 82.28.213.108] (104)Connection reset by peer: mod_fcgid: ap_pass_brigade failed in handle_request_ipc function, referer: https://www.vilcoldb.com/admin/port.php
See also:
		https://stackoverflow.com/questions/12153518/connection-reset-by-peer-mod-fcgid-error-reading-data-from-fastcgi-server
Apache config file:
 		/etc/httpd/conf/httpd.conf
Added the following relating to mod_fcgid:
			# Added by Kevin Beckett 27/06/14:
			<VirtualHost *:80>
				ServerAlias https://drakedirect.co.uk
				RedirectMatch permanent ^/(.*) https://www.drakedirect.co.uk/$1
				# Added by KB 10/07/19 for Vilcol port.php - set timeout to 10 hours
				<IfModule mod_fcgid.c>
					FcgidBusyTimeout 36000
				</IfModule>
				# End of KB 10/07/19
			</VirtualHost>
			# End by KB 27/06/14
Graceful restart:
		apachectl -k graceful
Show all loaded Apache modules (might be useful):
		apachectl -D DUMP_MODULES
Show all vhosts and their config files (not useful):
		apachectl -D DUMP_VHOSTS

Ticket to Rackspace (timeouts, cronjob, memory leaks):
		https://my.rackspace.com/portal/ticket/detail/190710-02286

Running as a cronjob from 15/07/19:
		/opt/plesk/php/7.2/bin/php -c /var/www/vhosts/system/vilcoldb.com/etc/php.ini -f '/var/www/vhosts/vilcoldb.com/httpdocs/admin/port.php' 2>> /var/www/vhosts/vilcoldb.com/httpdocs/admin/port.log
NOT running as a cronjob from 16/07/19!

*/

//if (array_key_exists('web', $_GET))
//{
//	$local_cronjob = false; # variable only exists in this script, not global.
//	# Task will be defined by $_POST (or possibly by $_GET)
//}
//else
//{
//    $local_cronjob = true; # variable only exists in this script, not global.
//    # No args are supplied i.e. $_GET and $_POST are empty, so the task is defined now:
//    $_GET['t'] = 'audit';
//    #phpinfo();
//}

global $script_check;
//if ($local_cronjob)
//	$script_check = true; # bypass script_check.php
include_once("settings.php");
//global $cronjob; # set to false in settings.php
//if ($local_cronjob)
//	$cronjob = true;

include_once("library.php");
include_once("lib_users.php");
global $denial_message;
global $navi_1_system;
global $navi_2_sys_port;
global $unix_path;
global $USER; # set by admin_verify()

# ------------------------------------------------------------------------------

error_reporting(E_ALL);

$time_start = time();
$time_threshold = 110 * 60; # 110 minutes (note the server will time-out after 120 minutes so we must stop well before then)
#$time_threshold = 30 * 60;#
#$incremental_run = true; #($cronjob ? false : true); # add to tables already there

$log_root = ''; #($cronjob ? "{$unix_path}/" : '');
log_open("{$log_root}import-vilcol/port_" . strftime('%Y_%m_%d_%H%M') . ".log");
log_write(post_values() . chr(13) . chr(10) . "GET=" . print_r($_GET,1));
dlog("time_start=$time_start(" . date_from_epoch(false,$time_start) . ")");
#log_write("memory_limit=" . ini_get('memory_limit'));

sql_connect();
my_sql_local_connect();

//if ($cronjob)
//{
//    #ini_set('memory_limit', '-1');
//    #log_write("memory_limit/2=" . ini_get('memory_limit'));
//}
//else
	admin_verify(); # writes to $USER

$tables = array(
			'--- Standing Data ---',
			'ACTIVITY_SD',
			'ADJUSTMENT_SD',
			'JOB_STATUS_SD',
			'JOB_TARGET_SD',
			'JOB_TYPE_SD',
			'LETTER_TYPE_SD',
			'MISC_INFO',
			'PAYMENT_METHOD_SD',
			'PAYMENT_ROUTE_SD',
			'REPORT_FIELD_SD',
			'USER_PERMISSION_SD',
			'USER_ROLE_SD',
			'--- Users ---',
			'USERV',
			'USER_PERM_LINK',
			'USER_ROLE_PERM_LINK',
			'SALESPERSON',
			'--- Clients ---',
			'CLIENT2',
			'CLIENT_CONTACT',
			'CLIENT_CONTACT_PHONE',
			'CLIENT_GROUP',
			'CLIENT_LETTER_LINK',
			'CLIENT_NOTE',
			'CLIENT_REPORT',
			'CLIENT_TARGET_LINK',
			'CLIENT_Z',
			'--- Jobs ---',
			'JOB',
			'JOB_ACT',
			'JOB_ARRANGE',
			'JOB_GROUP',
			'JOB_LETTER',
			'JOB_NOTE',
			'JOB_PAYMENT',
			'JOB_PHONE',
			'JOB_SUBJECT',
			'JOB_Z',
			'ADDRESS_HISTORY',
			'COLLECT_DBF_2015',
			'EMAIL',
			'LETTER_SEQ',
			'--- Accounts ---',
			'INV_ALLOC',
			'INV_BILLING',
			'INV_RECP',
			'INVOICE',
			'--- Reports ---',
			'REPORT',
			'REPORT_CLIENT_LINK',
			'REPORT_FIELD_LINK',
			'--- Audit ---',
			'AUDIT',
			'FEEDBACK',
			# I'm assuming that the following tables are not needed:
			#	STUFF
			#	TASK_REQUEST
			);

$id_names = array(
			'ACTIVITY_SD' => 'ACTIVITY_ID',
			'ADJUSTMENT_SD' => 'ADJUSTMENT_ID',
			'JOB_STATUS_SD' => 'JOB_STATUS_ID',
			'JOB_TARGET_SD' => 'JOB_TARGET_ID',
			'JOB_TYPE_SD' => 'JOB_TYPE_ID',
			'LETTER_TYPE_SD' => 'LETTER_TYPE_ID',
			'MISC_INFO' => 'MISC_INFO_ID',
			'PAYMENT_METHOD_SD' => 'PAYMENT_METHOD_ID',
			'PAYMENT_ROUTE_SD' => 'PAYMENT_ROUTE_ID',
			'REPORT_FIELD_SD' => 'REPORT_FIELD_ID',
			'USER_PERMISSION_SD' => 'USER_PERMISSION_ID',
			'USER_ROLE_SD' => 'USER_ROLE_ID',
			'USERV' => 'USER_ID',
			'USER_PERM_LINK' => 'USER_PERM_LINK_ID',
			'USER_ROLE_PERM_LINK' => 'USER_ROLE_PERM_LINK_ID',
			'SALESPERSON' => 'SALESPERSON_ID',
			'CLIENT2' => 'CLIENT2_ID',
			'CLIENT_CONTACT' => 'CLIENT_CONTACT_ID',
			'CLIENT_CONTACT_PHONE' => 'CLIENT_CONTACT_PHONE_ID',
			'CLIENT_GROUP' => 'CLIENT_GROUP_ID',
			'CLIENT_LETTER_LINK' => 'CLIENT_LETTER_LINK_ID',
			'CLIENT_NOTE' => 'CLIENT_NOTE_ID',
			'CLIENT_REPORT' => 'CLIENT_REPORT_ID',
			'CLIENT_TARGET_LINK' => 'CLIENT_TARGET_LINK_ID',
			'CLIENT_Z' => 'CLIENT2_ID',
			'JOB' => 'JOB_ID',
			'JOB_ACT' => 'JOB_ACT_ID',
			'JOB_ARRANGE' => 'JOB_ARRANGE_ID',
			'JOB_GROUP' => 'JOB_GROUP_ID',
			'JOB_LETTER' => 'JOB_LETTER_ID',
			'JOB_NOTE' => 'JOB_NOTE_ID',
			'JOB_PAYMENT' => 'JOB_PAYMENT_ID',
			'JOB_PHONE' => 'JOB_PHONE_ID',
			'JOB_SUBJECT' => 'JOB_SUBJECT_ID',
			'JOB_Z' => 'JOB_ID',
			'ADDRESS_HISTORY' => 'ADDRESS_HISTORY_ID',
			'COLLECT_DBF_2015' => 'COLLECT_DBF_2015_ID',
			'EMAIL' => 'EMAIL_ID',
			'LETTER_SEQ' => 'LETTER_SEQ_ID',
			'INV_ALLOC' => 'INV_ALLOC_ID',
			'INV_BILLING' => 'INV_BILLING_ID',
			'INV_RECP' => 'INV_RECP_ID',
			'INVOICE' => 'INVOICE_ID',
			'REPORT' => 'REPORT_ID',
			'REPORT_CLIENT_LINK' => 'REPORT_CLIENT_LINK_ID',
			'REPORT_FIELD_LINK' => 'REPORT_FIELD_LINK_ID',
			'AUDIT' => 'AUDIT_ID',
			'FEEDBACK' => 'FEEDBACK_ID',
			);

# Tables that don't need to be deleted once they've been ported successfully.
$static_tables = array('AUDIT', 'CLIENT_Z', 'JOB_Z');

//if ($cronjob)
//    data_processing();
//else
if ($USER['IS_ENABLED'])
{
	if (global_debug())
	{
		$navi_1_system = true; # settings.php; used by navi_1_heading()
		$navi_2_sys_port = true; # settings.php; used by navi_2_heading()
		$onload = "onload=\"set_scroll();\"";
		screen_layout();
	}
	else
		print "<p>$denial_message</p>";
}
else
	print "<p>" . server_php_self() . ": login is not enabled</p>";

my_sql_local_disconnect();
sql_disconnect();
log_close();

# ------------------------------------------------------------------------------

function screen_content()
{
	print "<h3>System Administration</h3>";
	navi_2_heading(); # secondary navigation buttons
	javascript();
	print "
	<h3>Port Database from MS SQL Server to MySQL</h3>
	";
	dprint(post_values());
	data_processing();
	display_tables();

} # screen_content()

function screen_content_2()
{
	# This is required by screen_layout()
} # screen_content_2()

function data_processing()
{
	#global $incremental_run;
	global $my_sql_local_conn;
	global $mysql_server;
	global $row_counts;
	global $static_tables;
	global $tables;
	global $time_start;
	global $total_rows_done;
	global $total_rows_to_do;

	if ($mysql_server)
	{
		dprint("The system is already using MySQL!!", true);
		return;
	}
	#dprint("The system is using MS SQL Server");

	$row_counts = array();
	foreach ($tables as $one)
	{
		if (substr($one,0,3) != '---')
		{
			$old_count = intval(sql_select_single("SELECT COUNT(*) FROM $one"));
			$row_counts[$one] = $old_count;
		}
	}
	arsort($row_counts);
	#dprint("row_counts=" . print_r($row_counts,1));#

	$task = post_val('task');
	$table = post_val('table');
	if (!$task)
	{
		$table = strtoupper(get_val('t'));
		if ($table == 'ALL')
			$task = 'port_all';
		elseif ($table)
			$task = 'port_one';
	}
	$do_list = array();

	if ($task == 'port_one')
	{
		if (in_array($table, $tables))
			$do_list[] = $table;
		elseif ($table != '')
			dprint("*=* Error Invalid table/1 \"$table\" *=*", true);
		else
			dprint("*=* Error No table *=*", true);
	}
	elseif ($task == 'port_ticked')
	{
		foreach ($row_counts as $one => $rcount)
		{
			if (array_key_exists("tck_{$one}", $_POST) && ($_POST["tck_{$one}"] == 1))
				$do_list[] = $one;
			$rcount=0;#keep code-checker quiet
		}
	}
	elseif ($task == 'port_all')
	{
		foreach ($row_counts as $one => $rcount)
		{
			$do_list[] = $one;
			$rcount=0;#keep code-checker quiet
		}
	}
	elseif ($task == 'delete_all_tables')
	{
		foreach ($tables as $table)
		{
			if (substr($table,0,3) != '---')
			{
				dlog("Dropping table $table ...");
				mysqli_query($my_sql_local_conn, "DROP TABLE $table"); # Drop MySQL table
			}
		}
	}
	elseif ($task == 'delete_most_tables')
	{
		foreach ($tables as $table)
		{
			if (substr($table,0,3) != '---')
			{
				if (in_array($table, $static_tables))
					dlog("Preserving table $table ...");
				else
				{
					dlog("Dropping table $table ...");
					mysqli_query($my_sql_local_conn, "DROP TABLE $table"); # Drop MySQL table
				}
			}
		}
	}
	elseif ($task == 'create_all_tables')
	{
		foreach ($tables as $table)
		{
			if (substr($table,0,3) != '---')
			{
				dlog("Checking table $table ...");
				$mysql_count = 0;
				$my_result = mysqli_query($my_sql_local_conn, "SELECT COUNT(*) FROM $table");
				if ($my_result !== false)
				{
					$stuff = mysqli_fetch_array($my_result, MYSQLI_NUM);
					if (is_array($stuff))
						$mysql_count = intval($stuff[0]);
				}
				if ($mysql_count == 0)
				{
					dlog("Creating table $table ...");
					create_mysql_table($table, 0);
				}
			}
		}
	}
	elseif ($task != '')
		dprint("*=* Error Invalid task/3 \"$task\" *=*", true);

	$total_rows_done = 0;
	$total_rows_to_do = 0;
	if ($do_list)
	{
		#dprint("do_list = " . print_r($do_list,1));#
		set_time_limit(10 * 60 * 60); # 10 hours
		dlog("Porting the following tables: " . print_r($do_list,1));
		foreach ($do_list as $table)
		{
			$total_rows_to_do += $row_counts[$table];#[0]; # MS SQL table's row count
			#if (!$incremental_run)
			#	mysqli_query($my_sql_local_conn, "DROP TABLE $table"); # Drop MySQL table
		}
		dlog("Total MS SQL row count: " . number_with_commas($total_rows_to_do));
		foreach ($do_list as $table)
		{
			dlog("---------------------------------------------------------------------------------------------------------------------------");
			$rc = port_table($table, $row_counts[$table]);
			if ($rc == -2)
			{
				dlog("---------------------------------------------------------------------------------------------------------------------------");
				dlog("Stopping porting on -2");
				break;
			}
		}
		$time_now = time();
		$time_spent = $time_now - $time_start;
		dlog("---------------------------------------------------------------------------------------------------------------------------");
		dlog("Finished porting at $time_now(" . date_from_epoch(false,$time_now) . "), time_spent=" . number_with_commas($time_spent) . "s=" . number_with_commas($time_spent/60) . "m=" . ($time_spent/3600) . "h");
	}
} # data_processing()

function display_tables()
{
	global $ac;
	global $ar;
	global $at;
	global $my_sql_local_conn;
	global $mysql_server;
	global $row_counts;
	global $tables;

	$table_info = array();
	$table_row_counts = array();
	foreach ($tables as $one)
	{
		if (substr($one,0,3) == '---')
		{
			$tname = "<b>$one</b>";
			$one_table = "";
			$one_old = '';
			$one_new = '';
		}
		else
		{
			//$save_time = false; # reduce page-load time
			//if ($save_time && in_array($one, array('AUDIT','CLIENT2','CLIENT_CONTACT','CLIENT_CONTACT_PHONE','CLIENT_LETTER_LINK')))
			//{
			//    $one_table = input_button($one, "port_one(this)"); #, $style);
			//    $tname = input_tickbox($one, "tck_{$one}", 1, array_key_exists("tck_{$one}", $_POST));
			//    $one_old = '---';
			//    $one_new = '---';
			//}
			//else
			//{
			$tname = input_tickbox($one, "tck_{$one}", 1, array_key_exists("tck_{$one}", $_POST), '', $mysql_server ? 'disabled' : '');
			$old_count = $row_counts[$one]; #intval(sql_select_single("SELECT COUNT(*) FROM $one"));
			$table_row_counts[$one] = $old_count;
			$one_old = number_with_commas($old_count);
			$my_result = mysqli_query($my_sql_local_conn, "SELECT COUNT(*) FROM $one");
			if ($my_result === false)
			{
				$new_count = 0;
				$one_new = 'Absent';
			}
			else
			{
				$stuff = mysqli_fetch_array($my_result, MYSQLI_NUM);
				if (is_array($stuff))
					$new_count = intval($stuff[0]);
				else
					$new_count = 0;
				$one_new = number_with_commas($new_count);
			}
			if ((0 < $old_count) && ($old_count != $new_count))
			{
				$col = ( ((0 < $new_count) && (($new_count % 100) == 0)) ? 'blue' : 'red');
				$one_new = "<span style=\"color:{$col}\">***** $one_new</span>";
			}
			$one_table = input_button($one, "port_one(this)", $mysql_server ? 'disabled' : ''); #, $style);
			//}
		}
		$table_info[$one] = array('ONE_TABLE' => $one_table, 'TNAME' => $tname, 'ONE_OLD' => $one_old, 'ONE_NEW' => $one_new);
	}

	$html_table_1 = "
	<table border=\"1\">
		<tr>
			<th>" . input_button('Tick All', 'tick_all()', $mysql_server ? 'disabled' : '') . "
												" . input_button('Port all Ticked', 'port_ticked()', $mysql_server ? 'disabled' : '') . "</th>
				<th>&nbsp; MS.S.Svr &nbsp;</th><th>&nbsp; MySQL &nbsp;</th>
		</tr>
		";
	foreach ($table_info as $one => $info)
	{
		$ax = ((strpos($info['TNAME'],'---') !== false) ? $ac : '');
		$html_table_1 .= "
			<tr>
				<td $ax>{$info['TNAME']}</td><td $ar>{$info['ONE_OLD']}</td><td $ar>{$info['ONE_NEW']}</td>
			</tr>
			";
	}
	$html_table_1 .= "
			<tr>
				<td $ac>" . input_button('Tick All', 'tick_all()', $mysql_server ? 'disabled' : '') . "
												" . input_button('Port all Ticked', 'port_ticked()', $mysql_server ? 'disabled' : '') . "</td>
					<td $ar></td><td $ar></td>
			</tr>
			";
	$html_table_1 .= "
	</table>
	";

	$html_table_2 = "
	<table border=\"1\">
		<tr>
			<th>In Alphabetical Order</th><th>&nbsp; MS.S.Svr &nbsp;</th><th>&nbsp; MySQL &nbsp;</th>
		</tr>
		";
	ksort($table_info);
	foreach ($table_info as $one => $info)
	{
		if (strpos($info['TNAME'], '---') === false)
		{
			$html_table_2 .= "
				<tr>
					<td $ac>{$info['ONE_TABLE']}</td><td $ar>{$info['ONE_OLD']}</td><td $ar>{$info['ONE_NEW']}</td>
				</tr>
				";
		}
	}
	$html_table_2 .= "
	</table>
	";

	$html_table_3 = "
	<table border=\"1\">
		<tr>
			<th>In Row-count Order</th><th>&nbsp; MS.S.Svr &nbsp;</th><th>&nbsp; MySQL &nbsp;</th>
		</tr>
		";
	arsort($table_row_counts);
	#dprint(print_r($table_row_counts,1));#
	foreach ($table_row_counts as $one => $row_count)
	{
		$info = $table_info[$one];
		$html_table_3 .= "
			<tr>
				<td>{$info['TNAME']}</td><td $ar>{$info['ONE_OLD']}</td><td $ar>{$info['ONE_NEW']}</td>
			</tr>
			";
		$row_count=$row_count;#keep code-checker quiet
	}
	$html_table_3 .= "
	</table>
	";

	print "
	<form name=\"form_main\" action=\"" . server_php_self() . "?web\" method=\"post\">
	" . input_hidden('task', '') . "
	" . input_hidden('table', '') . "
	<table>
	" .
	"
	<tr>
	    <td>" . input_button('Delete all tables', "delete_all_tables()", $mysql_server ? 'disabled' : '') . "
			" . input_button('Delete most tables', "delete_most_tables()", $mysql_server ? 'disabled' : '') . "
			" . input_button('Create all tables', "create_all_tables()", $mysql_server ? 'disabled' : '') . "
		</td>
	</tr>
	" .
	"
	<tr>
		<td $at>$html_table_1</td><td width=\"20\"></td><td $at>$html_table_3</td><td width=\"20\"></td><td $at>$html_table_2</td>
	</tr>
	</table>
	</form><!--form_main-->
	";

} # display_tables()

function javascript()
{
	print "
	<script type=\"text/javascript\">

	" .
	"
	function delete_all_tables()
	{
	    if (confirm('Do you really want to DELETE ALL tables?'))
	    {
	        document.form_main.task.value = 'delete_all_tables';
	        document.form_main.submit();
	    }
	}

	function delete_most_tables()
	{
	    if (confirm('Do you really want to delete MOST tables?'))
	    {
	        document.form_main.task.value = 'delete_most_tables';
	        document.form_main.submit();
	    }
	}
	
	function create_all_tables()
	{
	    if (confirm('Do you really want to Create All tables?'))
	    {
	        document.form_main.task.value = 'create_all_tables';
	        document.form_main.submit();
	    }
	}

	function port_one(control)
	{
		var table = control.value;
		if (confirm('Do you want to port ' + table + '?'))
		{
			document.form_main.task.value = 'port_one';
			document.form_main.table.value = table;
			document.form_main.submit();
		}
	}

	function port_ticked()
	{
		var inputs = document.getElementsByTagName('input');
		var ticked = [];
		var tix = 0;
		for (var i = 0; i < inputs.length; i++)
		{
			if (inputs[i].type == 'checkbox')
			{
				if (inputs[i].name.substring(0,4) == 'tck_')
				{
					if (inputs[i].checked)
						ticked[tix++] = 1;
				}
			}
		}
		if (0 < ticked.length)
		{
			if (confirm('Do you want to port all the ticked tables?'))
			{
				document.form_main.task.value = 'port_ticked';
				document.form_main.table.value = 'ticked';
				document.form_main.submit();
			}
		}
		else
			alert('No tables are ticked!');
	}

	function tick_all()
	{
		var inputs = document.getElementsByTagName('input');
		var count_y = 0;
		var count_n = 0;
		var chkd = false;
		for (pass = 0; pass < 2; pass++)
		{
			if (pass == 1)
			{
				if (0 < count_n)
					chkd = true;
				else
					chkd = false;
			}
			for (var i = 0; i < inputs.length; i++)
			{
				if (inputs[i].type == 'checkbox')
				{
					if (inputs[i].name.substring(0,4) == 'tck_')
					{
						if (pass == 0)
						{
							if (inputs[i].checked)
								count_y++;
							else
								count_n++;
						}
						else
							inputs[i].checked = chkd;
					}
				}
			}
		}
	}

	</script>
	";
}

function my_sql_local_connect()
{
	global $my_sql_local_conn;
	global $site_local;
	global $sql_database;
	global $sql_host;
	global $sql_password;
	global $sql_username;

	if ($site_local)
		$my_sql_local_conn = mysqli_connect('localhost', 'root', 'invinoveritas', 'vilcoldb');
	else
	{
		if ((!$sql_host) && (!$sql_username) && (!$sql_password) && (!$sql_database)) # Hack in credentials
		{
			$sql_host = "localhost";
			$sql_username = "vilcoldb_user";
			$sql_password = "i38DYqB2T3st";
			$sql_database = "VILCOLDB";
		}
		$my_sql_local_conn = mysqli_connect($sql_host, $sql_username, $sql_password, $sql_database);
		$details = "mysqli_connect(\"$sql_host\", \"$sql_username\", \"$sql_password\", \"$sql_database\")";
	}
	$rc = false;
	if ($my_sql_local_conn)
	{
		#dlog("Connected to \"$sql_host\", \"$sql_username\", \"*****\", \"$sql_database\"", true);
		$rc = true;
	}
	else
		dlog("my_sql_local_connect(): mysqli_connect() failed" . chr(13) . chr(10). $details);
	return $rc;
}

function my_sql_local_disconnect()
{
	global $my_sql_local_conn;

	mysqli_close($my_sql_local_conn);
	$my_sql_local_conn = '';
}

function create_mysql_table($table, $mysql_count)
{
	global $fields_date; # array
	global $fields_encrypted; # array
	global $fields_number; # array
	global $fields_nullable; # array
	global $id_name;
	#global $incremental_run;
	global $my_sql_local_conn;
	global $select_fields; # string, not array

	$rc = false;
	$create_fields = '';
	switch ($table)
	{
		case 'ACTIVITY_SD':
			$id_name = 'ACTIVITY_ID';
			$create_fields = "
				`$id_name` INT(11) NOT NULL AUTO_INCREMENT,
				`ACT_TDX` VARCHAR(10) NOT NULL,
				`ACT_QUALCO` VARCHAR(10) NULL,
				`ACT_DSHORT` VARCHAR(200) NULL,
				`ACT_DLONG` VARCHAR(1000) NULL,
				`SHORT_LIST` VARCHAR(1) NULL,
				`DIALLER_EV` TINYINT(1) NOT NULL DEFAULT 0,
				`ACT_NON_MAN` TINYINT(1) NOT NULL DEFAULT 0,
				`OBSOLETE` TINYINT(1) NOT NULL DEFAULT 0,
				PRIMARY KEY (`$id_name`),
				UNIQUE KEY ACT_TDX (ACT_TDX)
 				";
			$select_fields = array($id_name, 'ACT_TDX', 'ACT_QUALCO', 'ACT_DSHORT', 'ACT_DLONG', 'SHORT_LIST', 'DIALLER_EV', 'ACT_NON_MAN', 'OBSOLETE');
			$fields_number = array($id_name, 'DIALLER_EV', 'ACT_NON_MAN', 'OBSOLETE');
			$fields_date = array();
			$fields_encrypted = array();
			$fields_nullable = array('ACT_QUALCO', 'ACT_DSHORT', 'ACT_DLONG', 'SHORT_LIST');
			break;
		case 'ADDRESS_HISTORY':
			$id_name = 'ADDRESS_HISTORY_ID';
			$create_fields = "
				`$id_name` INT(11) NOT NULL AUTO_INCREMENT,
				`JOB_SUBJECT_ID` INT(11) NOT NULL,
				`AD_FROM_DT` DATETIME NOT NULL,
				`AD_TO_DT` DATETIME NOT NULL,
				`ADDR_1` BLOB NULL,
				`ADDR_2` BLOB NULL,
				`ADDR_3` BLOB NULL,
				`ADDR_4` BLOB NULL,
				`ADDR_5` BLOB NULL,
				`ADDR_PC` BLOB NULL,
				`AD_NOTES` BLOB NULL,
				PRIMARY KEY (`$id_name`),
				KEY JOB_SUBJECT_ID (JOB_SUBJECT_ID)
 				";
			$select_fields = array($id_name, 'JOB_SUBJECT_ID', 'AD_FROM_DT', 'AD_TO_DT', 'ADDR_1', 'ADDR_2', 'ADDR_3', 'ADDR_4', 'ADDR_5', 'ADDR_PC',
									'AD_NOTES');
			$fields_number = array($id_name, 'JOB_SUBJECT_ID');
			$fields_date = array('AD_FROM_DT', 'AD_TO_DT');
			$fields_encrypted = array('ADDR_1', 'ADDR_2', 'ADDR_3', 'ADDR_4', 'ADDR_5', 'ADDR_PC', 'AD_NOTES');
			$fields_nullable = array('ADDR_1', 'ADDR_2', 'ADDR_3', 'ADDR_4', 'ADDR_5', 'ADDR_PC', 'AD_NOTES');
			break;
		case 'ADJUSTMENT_SD':
			$id_name = 'ADJUSTMENT_ID';
			$create_fields = "
				`$id_name` INT(11) NOT NULL AUTO_INCREMENT,
				`ADJUSTMENT` VARCHAR(100) NOT NULL,
				`SORT_ORDER` INT(11) NOT NULL,
				`OBSOLETE` TINYINT(1) NOT NULL DEFAULT 0,
				PRIMARY KEY (`$id_name`),
				UNIQUE KEY ADJUSTMENT (ADJUSTMENT)
 				";
			$select_fields = array($id_name, 'ADJUSTMENT', 'SORT_ORDER', 'OBSOLETE');
			$fields_number = array($id_name, 'SORT_ORDER', 'OBSOLETE');
			$fields_date = array();
			$fields_encrypted = array();
			$fields_nullable = array();
			break;
		case 'AUDIT':
			# Note: field FROM_MYSQL is not present in the MS SQL database, should be set to 0 for records ported from MS SQL
			$id_name = 'AUDIT_ID';
			$create_fields = "
				`$id_name` INT(11) NOT NULL AUTO_INCREMENT,
				`CHANGE_DT` DATETIME NOT NULL,
				`CHANGE_ID` INT(11) NOT NULL,
				`CLIENT2_ID` INT(11) NULL,
				`JOB_ID` INT(11) NULL,
				`USER_ID` INT(11) NULL,
				`FILE_EVENT` VARCHAR(1) NULL,
				`LOGIN_EVENT` TINYINT(1) NULL,
				`TABLE_NAME` VARCHAR(255) NOT NULL,
				`ID_NAME` VARCHAR(255) NOT NULL,
				`ID_VALUE` INT(11) NOT NULL,
				`S_NUM` INT(11) NULL,
				`FIELD_NAME` VARCHAR(255) NOT NULL,
				`OLD_VAL` BLOB NULL,
				`NEW_VAL` BLOB NULL,
				`FROM_MYSQL` TINYINT(1) NOT NULL,
				PRIMARY KEY (`$id_name`),
				KEY CHANGE_DT (CHANGE_DT),
				KEY CHANGE_ID (CHANGE_ID),
				KEY FIELD_NAME (FIELD_NAME),
				KEY ID_NAME (ID_NAME),
				KEY ID_VALUE (ID_VALUE),
				KEY TABLE_NAME (TABLE_NAME)
 				";
			$select_fields = array(		$id_name, 'CHANGE_DT', 'CHANGE_ID', 'CLIENT2_ID', 'JOB_ID', 'USER_ID', 'FILE_EVENT', 'LOGIN_EVENT',
										'TABLE_NAME', 'ID_NAME', 'ID_VALUE', 'S_NUM', 'FIELD_NAME', 'OLD_VAL', 'NEW_VAL');
			$fields_number = array(		$id_name, 'CHANGE_ID', 'CLIENT2_ID', 'JOB_ID', 'USER_ID', 'LOGIN_EVENT',
										'ID_VALUE', 'S_NUM');
			$fields_date = array(		'CHANGE_DT');
			$fields_encrypted = array(	'OLD_VAL', 'NEW_VAL');
			$fields_nullable = array(	'CLIENT2_ID', 'JOB_ID', 'USER_ID', 'FILE_EVENT', 'LOGIN_EVENT',
										'S_NUM', 'OLD_VAL', 'NEW_VAL');
			break;
		case 'CLIENT_CONTACT':
			$id_name = 'CLIENT_CONTACT_ID';
			$create_fields = "
				`$id_name` INT(11) NOT NULL AUTO_INCREMENT,
				`CLIENT2_ID` INT(11) NOT NULL,
				`CC_TITLE` VARCHAR(10) NULL,
				`CC_FIRSTNAME` BLOB NULL,
				`CC_LASTNAME` BLOB NOT NULL,
				`CC_MAIN` TINYINT(1) NOT NULL DEFAULT 0,
				`CC_INV` TINYINT(1) NOT NULL DEFAULT 0,
				`CC_REP` TINYINT(1) NOT NULL DEFAULT 0,
				`CC_POSITION` BLOB NULL,
				`CC_EMAIL_1` BLOB NULL,
				`CC_EMAIL_2` BLOB NULL,
				`CC_AD_AS_CL` TINYINT(1) NOT NULL DEFAULT 1,
				`CC_ADDR_1` BLOB NULL,
				`CC_ADDR_2` BLOB NULL,
				`CC_ADDR_3` BLOB NULL,
				`CC_ADDR_4` BLOB NULL,
				`CC_ADDR_5` BLOB NULL,
				`CC_ADDR_PC` BLOB NULL,
				`IMPORTED` TINYINT(1) NOT NULL,
				`OBSOLETE` TINYINT(1) NOT NULL DEFAULT 0,
				PRIMARY KEY (`$id_name`),
				KEY CC_MAIN (CC_MAIN),
				KEY CLIENT2_ID (CLIENT2_ID)
 				";
			$select_fields = array($id_name, 'CLIENT2_ID', 'CC_TITLE', 'CC_FIRSTNAME', 'CC_LASTNAME', 'CC_MAIN', 'CC_INV', 'CC_REP', 'CC_POSITION',
									'CC_EMAIL_1', 'CC_EMAIL_2', 'CC_AD_AS_CL',
									'CC_ADDR_1', 'CC_ADDR_2', 'CC_ADDR_3', 'CC_ADDR_4', 'CC_ADDR_5', 'CC_ADDR_PC', 'IMPORTED', 'OBSOLETE');
			$fields_number = array($id_name, 'CLIENT2_ID', 'CC_MAIN', 'CC_INV', 'CC_REP', 'CC_AD_AS_CL', 'IMPORTED', 'OBSOLETE');
			$fields_date = array();
			$fields_encrypted = array('CC_FIRSTNAME', 'CC_LASTNAME', 'CC_POSITION', 'CC_EMAIL_1', 'CC_EMAIL_2',
										'CC_ADDR_1', 'CC_ADDR_2', 'CC_ADDR_3', 'CC_ADDR_4', 'CC_ADDR_5', 'CC_ADDR_PC');
			$fields_nullable = array('CC_TITLE', 'CC_FIRSTNAME', 'CC_POSITION', 'CC_EMAIL_1', 'CC_EMAIL_2',
										'CC_ADDR_1', 'CC_ADDR_2', 'CC_ADDR_3', 'CC_ADDR_4', 'CC_ADDR_5', 'CC_ADDR_PC');
			break;
		case 'CLIENT_CONTACT_PHONE':
			$id_name = 'CLIENT_CONTACT_PHONE_ID';
			$create_fields = "
				`$id_name` INT(11) NOT NULL AUTO_INCREMENT,
				`CLIENT_CONTACT_ID` INT(11) NOT NULL,
				`CP_PHONE` BLOB NULL,
				`CP_DESCR` BLOB NULL,
				`CP_MAIN` TINYINT(1) NOT NULL,
				`IMPORTED` TINYINT(1) NOT NULL,
				`OBSOLETE` TINYINT(1) NOT NULL DEFAULT 0,
				PRIMARY KEY (`$id_name`),
				KEY CLIENT_CONTACT_ID (CLIENT_CONTACT_ID),
				KEY CP_MAIN (CP_MAIN)
 				";
			$select_fields = array($id_name, 'CLIENT_CONTACT_ID', 'CP_PHONE', 'CP_DESCR', 'CP_MAIN', 'IMPORTED', 'OBSOLETE');
			$fields_number = array($id_name, 'CLIENT_CONTACT_ID', 'CP_MAIN', 'IMPORTED', 'OBSOLETE');
			$fields_date = array();
			$fields_encrypted = array('CP_PHONE', 'CP_DESCR');
			$fields_nullable = array('CP_PHONE', 'CP_DESCR');
			break;
		case 'CLIENT_GROUP':
			$id_name = 'CLIENT_GROUP_ID';
			$create_fields = "
				`$id_name` INT(11) NOT NULL AUTO_INCREMENT,
				`GROUP_NAME` BLOB NOT NULL,
				`OBSOLETE` TINYINT(1) NOT NULL DEFAULT 0,
				PRIMARY KEY (`$id_name`)
 				";
			$select_fields = array($id_name, 'GROUP_NAME', 'OBSOLETE');
			$fields_number = array($id_name, 'OBSOLETE');
			$fields_date = array();
			$fields_encrypted = array('GROUP_NAME');
			$fields_nullable = array();
			break;
		case 'CLIENT_LETTER_LINK':
			$id_name = 'CLIENT_LETTER_LINK_ID';
			$create_fields = "
				`$id_name` INT(11) NOT NULL AUTO_INCREMENT,
				`CLIENT2_ID` INT(11) NOT NULL,
				`LETTER_TYPE_ID` INT(11) NOT NULL,
				PRIMARY KEY (`$id_name`),
				KEY CLIENT2_ID (CLIENT2_ID)
 				";
			$select_fields = array($id_name, 'CLIENT2_ID', 'LETTER_TYPE_ID');
			$fields_number = array($id_name, 'CLIENT2_ID', 'LETTER_TYPE_ID');
			$fields_date = array();
			$fields_encrypted = array();
			$fields_nullable = array();
			break;
		case 'CLIENT_NOTE':
			$id_name = 'CLIENT_NOTE_ID';
			$create_fields = "
				`$id_name` INT(11) NOT NULL AUTO_INCREMENT,
				CLIENT2_ID INT(11) NOT NULL,
				CN_NOTE BLOB NOT NULL,
				IMPORTED TINYINT(1) NOT NULL,
				CN_ADDED_ID INT(11) NOT NULL,
				CN_ADDED_DT DATETIME NOT NULL,
				CN_UPDATED_ID INT(11) NULL,
				CN_UPDATED_DT DATETIME NULL,
				PRIMARY KEY (`$id_name`),
				KEY CLIENT2_ID (CLIENT2_ID)
 				";
			$select_fields = array($id_name, 'CLIENT2_ID', 'CN_NOTE', 'IMPORTED',
									'CN_ADDED_ID', 'CN_ADDED_DT', 'CN_UPDATED_ID', 'CN_UPDATED_DT');
			$fields_number = array($id_name, 'CLIENT2_ID', 'IMPORTED', 'CN_ADDED_ID', 'CN_UPDATED_ID');
			$fields_date = array('CN_ADDED_DT', 'CN_UPDATED_DT');
			$fields_encrypted = array('CN_NOTE');
			$fields_nullable = array('CN_UPDATED_ID', 'CN_UPDATED_DT');
			break;
		case 'CLIENT_REPORT':
			$id_name = 'CLIENT_REPORT_ID';
			$create_fields = "
				`$id_name` INT(11) NOT NULL AUTO_INCREMENT,
				CLIENT2_ID INT(11) NOT NULL,
				REPORT_NAME VARCHAR(100) NOT NULL,
				REPORT_DT DATETIME NULL,
				REPORT_FILENAME VARCHAR(100) NULL,
				REPORT_GEN_ID INT(11) NULL,
				REPORT_GEN_DT DATETIME NULL,
				REPORT_SENT_ID INT(11) NULL,
				REPORT_SENT_DT DATETIME NULL,
				PRIMARY KEY (`$id_name`),
				KEY CLIENT2_ID (CLIENT2_ID)
 				";
			$select_fields = array($id_name, 'CLIENT2_ID', 'REPORT_NAME', 'REPORT_DT', 'REPORT_FILENAME', 'REPORT_GEN_ID', 'REPORT_GEN_DT',
												'REPORT_SENT_ID', 'REPORT_SENT_DT');
			$fields_number = array($id_name, 'CLIENT2_ID', 'REPORT_GEN_ID', 'REPORT_SENT_ID');
			$fields_date = array('REPORT_DT', 'REPORT_GEN_DT', 'REPORT_SENT_DT');
			$fields_encrypted = array();
			$fields_nullable = array('REPORT_DT', 'REPORT_FILENAME', 'REPORT_GEN_ID', 'REPORT_GEN_DT', 'REPORT_SENT_ID', 'REPORT_SENT_DT');
			break;
		case 'CLIENT_TARGET_LINK':
			$id_name = 'CLIENT_TARGET_LINK_ID';
			$create_fields = "
				`$id_name` INT(11) NOT NULL AUTO_INCREMENT,
				CLIENT2_ID INT(11) NOT NULL,
				JOB_TARGET_ID INT(11) NOT NULL,
				CT_FEE DECIMAL(10, 2) NULL,
				PRIMARY KEY (`$id_name`),
				KEY CLIENT2_ID (CLIENT2_ID),
				KEY JOB_TARGET_ID (JOB_TARGET_ID)
 				";
			$select_fields = array($id_name, 'CLIENT2_ID', 'JOB_TARGET_ID', 'CT_FEE');
			$fields_number = array($id_name, 'CLIENT2_ID', 'JOB_TARGET_ID', 'CT_FEE');
			$fields_date = array();
			$fields_encrypted = array();
			$fields_nullable = array('CT_FEE');
			break;
		case 'CLIENT_Z':
			$id_name = 'CLIENT2_ID';
			$create_fields = "
				CLIENT2_ID INT(11) NOT NULL,
				Z_ACC INT(11) NULL,
				Z_AP_AMOUNT DECIMAL(10, 2) NULL,
				Z_AP_CODE VARCHAR(3) NULL,
				Z_AP_DEF VARCHAR(3) NULL,
				Z_CS_DEL INT(11) NULL,
				Z_CSPENT DATETIME NULL,
				Z_DI_DEL INT(11) NULL,
				Z_DIRECT DATETIME NULL,
				Z_FO_DEL INT(11) NULL,
				Z_FORWARDED DATETIME NULL,
				Z_JOBS INT(11) NULL,
				Z_LASTREP DATETIME NULL,
				Z_MARK INT(11) NULL,
				Z_NOTRACES VARCHAR(3) NULL,
				Z_REP_ORD VARCHAR(1) NULL,
				Z_RETR INT(11) NULL,
				Z_TO_US DATETIME NULL,
				Z_TU_DEL INT(11) NULL,
				Z_VPS VARCHAR(8) NULL,
				UNIQUE KEY (CLIENT2_ID)
 				";
			$select_fields = array('CLIENT2_ID', 'Z_ACC', 'Z_AP_AMOUNT', 'Z_AP_CODE', 'Z_AP_DEF', 'Z_CS_DEL', 'Z_CSPENT',
									'Z_DI_DEL', 'Z_DIRECT', 'Z_FO_DEL', 'Z_FORWARDED', 'Z_JOBS', 'Z_LASTREP', 'Z_MARK', 'Z_NOTRACES',
									'Z_REP_ORD', 'Z_RETR', 'Z_TO_US', 'Z_TU_DEL', 'Z_VPS');
			$fields_number = array('CLIENT2_ID', 'Z_ACC', 'Z_AP_AMOUNT', 'Z_CS_DEL', 'Z_DI_DEL', 'Z_FO_DEL', 'Z_JOBS',
									'Z_MARK', 'Z_RETR', 'Z_TU_DEL');
			$fields_date = array('Z_CSPENT', 'Z_DIRECT', 'Z_FORWARDED', 'Z_LASTREP', 'Z_TO_US');
			$fields_encrypted = array();
			$fields_nullable = array('Z_ACC', 'Z_AP_AMOUNT', 'Z_AP_CODE', 'Z_AP_DEF', 'Z_CS_DEL', 'Z_CSPENT',
									'Z_DI_DEL', 'Z_DIRECT', 'Z_FO_DEL', 'Z_FORWARDED', 'Z_JOBS', 'Z_LASTREP', 'Z_MARK', 'Z_NOTRACES',
									'Z_REP_ORD', 'Z_RETR', 'Z_TO_US', 'Z_TU_DEL', 'Z_VPS');
			break;
		case 'CLIENT2':
			$id_name = 'CLIENT2_ID';
			$create_fields = "
				`$id_name` INT(11) NOT NULL AUTO_INCREMENT,
				`C_CODE` INT(11) NOT NULL,
				`CLIENT_GROUP_ID` INT(11) NULL,
				`C_INDIVIDUAL` TINYINT(1) NOT NULL DEFAULT 0,
				`C_AGENCY` TINYINT(1) NOT NULL DEFAULT 0,
				`C_CO_NAME` BLOB NULL,
				`C_ADDR_1` BLOB NULL,
				`C_ADDR_2` BLOB NULL,
				`C_ADDR_3` BLOB NULL,
				`C_ADDR_4` BLOB NULL,
				`C_ADDR_5` BLOB NULL,
				`C_ADDR_PC` BLOB NULL,
				`ALPHA_CODE` VARCHAR(10) NULL,
				`C_TRACE` TINYINT(1) NOT NULL DEFAULT 0,
				`C_COLLECT` TINYINT(1) NOT NULL DEFAULT 0,
				`COMM_PERCENT` DECIMAL(10,3) NULL,
				`TR_FEE` DECIMAL(10,2) NULL,
				`NT_FEE` DECIMAL(10,2) NULL,
				`TC_FEE` DECIMAL(10,2) NULL,
				`RP_FEE` DECIMAL(10,2) NULL,
				`SV_FEE` DECIMAL(10,2) NULL,
				`MN_FEE` DECIMAL(10,2) NULL,
				`TM_FEE` DECIMAL(10,2) NULL,
				`AT_FEE` DECIMAL(10,2) NULL,
				`ET_FEE` DECIMAL(10,2) NULL,
				`DEDUCT_AS` TINYINT(1) NOT NULL DEFAULT 0,
				`S_INVS_TRACE` TINYINT(1) NOT NULL DEFAULT 0,
				`SALESPERSON_ID` INT(11) NULL,
				`SALESPERSON_TXT` VARCHAR(200) NULL,
				`CREATED_DT` DATETIME NOT NULL,
				`UPDATED_DT` DATETIME NULL,
				`IMPORTED` TINYINT(1) NOT NULL,
				`C_BANK_NAME` BLOB NULL,
				`C_BANK_SORTCODE` BLOB NULL,
				`C_BANK_ACC_NUM` BLOB NULL,
				`C_BANK_ACC_NAME` BLOB NULL,
				`C_BANK_COUNTRY` VARCHAR(100) NULL,
				`C_BANK_SWIFT` BLOB NULL,
				`C_BANK_IBAN` BLOB NULL,
				`C_BACS` TINYINT(1) NOT NULL DEFAULT 0,
				`INV_EMAILED` TINYINT(1) NOT NULL DEFAULT 0,
				`INV_EMAIL_NAME` BLOB NULL,
				`INV_EMAIL_ADDR` BLOB NULL,
				`INV_COMBINE` TINYINT(1) NOT NULL DEFAULT 0,
				`INV_BRANCH_COMB` TINYINT(1) NOT NULL DEFAULT 0,
				`INV_STMT_FREQ` VARCHAR(1) NULL,
				`INV_NEXT_STMT_DT` DATETIME NULL,
				`C_ARCHIVED` TINYINT(1) NOT NULL DEFAULT 0,
				`C_VAT` TINYINT(1) NOT NULL DEFAULT 0,
				`C_GROUP_HO` TINYINT(1) NOT NULL DEFAULT 0,
				`C_CLOSEOUT` TINYINT(1) NOT NULL DEFAULT 0,
				`PORTAL_PUSH` TINYINT(1) NOT NULL DEFAULT 0,
				PRIMARY KEY (`$id_name`),
				KEY C_ARCHIVED (C_ARCHIVED),
				KEY C_CO_NAME (C_CO_NAME (100)),
				KEY C_CODE (C_CODE),
				KEY PORTAL_PUSH (PORTAL_PUSH)
 				";
			$select_fields = array(		$id_name, 'C_CODE', 'CLIENT_GROUP_ID', 'C_INDIVIDUAL', 'C_AGENCY',
										'C_CO_NAME', 'C_ADDR_1', 'C_ADDR_2', 'C_ADDR_3', 'C_ADDR_4', 'C_ADDR_5', 'C_ADDR_PC', 'ALPHA_CODE', 'C_TRACE',
										'C_COLLECT', 'COMM_PERCENT', 'TR_FEE', 'NT_FEE', 'TC_FEE', 'RP_FEE', 'SV_FEE', 'MN_FEE', 'TM_FEE', 'AT_FEE',
										'ET_FEE', 'DEDUCT_AS', 'S_INVS_TRACE', 'SALESPERSON_ID', 'SALESPERSON_TXT', 'CREATED_DT', 'UPDATED_DT', 'IMPORTED',
										'C_BANK_NAME', 'C_BANK_SORTCODE', 'C_BANK_ACC_NUM', 'C_BANK_ACC_NAME', 'C_BANK_COUNTRY', 'C_BANK_SWIFT',
										'C_BANK_IBAN','C_BACS', 'INV_EMAILED', 'INV_EMAIL_NAME', 'INV_EMAIL_ADDR', 'INV_COMBINE', 'INV_BRANCH_COMB',
										'INV_STMT_FREQ', 'INV_NEXT_STMT_DT', 'C_ARCHIVED', 'C_VAT', 'C_GROUP_HO', 'C_CLOSEOUT', 'PORTAL_PUSH');
			$fields_number = array(		$id_name, 'C_CODE', 'CLIENT_GROUP_ID', 'C_INDIVIDUAL', 'C_AGENCY', 'C_TRACE', 'C_COLLECT',
										'COMM_PERCENT', 'TR_FEE', 'NT_FEE', 'TC_FEE', 'RP_FEE', 'SV_FEE', 'MN_FEE', 'TM_FEE', 'AT_FEE', 'ET_FEE',
										'DEDUCT_AS', 'S_INVS_TRACE', 'SALESPERSON_ID', 'IMPORTED', 'C_BACS', 'INV_EMAILED', 'INV_COMBINE', 'INV_BRANCH_COMB',
										'C_ARCHIVED', 'C_VAT', 'C_GROUP_HO', 'C_CLOSEOUT', 'PORTAL_PUSH');
			$fields_date = array(		'CREATED_DT', 'UPDATED_DT', 'INV_NEXT_STMT_DT');
			$fields_encrypted = array(	'C_CO_NAME', 'C_ADDR_1', 'C_ADDR_2', 'C_ADDR_3', 'C_ADDR_4', 'C_ADDR_5', 'C_ADDR_PC',
										'C_BANK_NAME', 'C_BANK_SORTCODE', 'C_BANK_ACC_NUM', 'C_BANK_ACC_NAME', 'C_BANK_SWIFT', 'C_BANK_IBAN',
										'INV_EMAIL_NAME', 'INV_EMAIL_ADDR');
			$fields_nullable = array(	'CLIENT_GROUP_ID',
										'C_CO_NAME', 'C_ADDR_1', 'C_ADDR_2', 'C_ADDR_3', 'C_ADDR_4', 'C_ADDR_5', 'C_ADDR_PC', 'ALPHA_CODE',
										'COMM_PERCENT', 'TR_FEE', 'NT_FEE', 'TC_FEE', 'RP_FEE', 'SV_FEE', 'MN_FEE', 'TM_FEE', 'AT_FEE', 'ET_FEE',
										'SALESPERSON_ID', 'SALESPERSON_TXT', 'UPDATED_DT',
										'C_BANK_NAME', 'C_BANK_SORTCODE', 'C_BANK_ACC_NUM', 'C_BANK_ACC_NAME', 'C_BANK_COUNTRY', 'C_BANK_SWIFT',
										'C_BANK_IBAN', 'INV_EMAIL_NAME', 'INV_EMAIL_ADDR', 'INV_STMT_FREQ', 'INV_NEXT_STMT_DT');
			break;
		case 'COLLECT_DBF_2015':
			# Note: this table was only used during the import of old DBF tables into the RDR database, so it's not really needed.
			$id_name = 'COLLECT_DBF_2015_ID';
			$create_fields = "
				`$id_name` INT(11) NOT NULL AUTO_INCREMENT,
				SEQUENCE INT(11) NOT NULL,
				VILNO INT(11) NOT NULL,
				LASTNAME VARCHAR(100) NULL,
				FIRSTNAME VARCHAR(100) NULL,
				COMPNAME VARCHAR(100) NULL,
				HOMEADD1 VARCHAR(100) NULL,
				EMAIL VARCHAR(100) NULL,
				MOBILE VARCHAR(100) NULL,
				CLOSEDATE DATE NULL,
				CLID INT(11) NULL,
				CLIREF VARCHAR(100) NULL,
				DATEREC DATE NULL,
				PRIMARY KEY (`$id_name`)
 				";
			$select_fields = array($id_name, 'SEQUENCE', 'VILNO', 'LASTNAME', 'FIRSTNAME', 'COMPNAME', 'HOMEADD1', 'EMAIL',
									'MOBILE', 'CLOSEDATE', 'CLID', 'CLIREF', 'DATEREC');
			$fields_number = array($id_name, 'SEQUENCE', 'VILNO', 'CLID');
			$fields_date = array('CLOSEDATE', 'DATEREC');
			$fields_encrypted = array();
			$fields_nullable = array('LASTNAME', 'FIRSTNAME', 'COMPNAME', 'HOMEADD1', 'EMAIL', 'MOBILE', 'CLOSEDATE', 'CLID',
										'CLIREF', 'DATEREC');
			break;
		case 'EMAIL':
			$id_name = 'EMAIL_ID';
			$create_fields = "
				`$id_name` INT(11) NOT NULL AUTO_INCREMENT,
				JOB_ID INT(11) NULL,
				CLIENT2_ID INT(11) NULL,
				EM_DT DATETIME NOT NULL,
				EM_TO BLOB NOT NULL,
				EM_CC BLOB NULL,
				EM_BCC BLOB NULL,
				EM_SUBJECT BLOB NULL,
				EM_MESSAGE BLOB NULL,
				EM_ATTACH VARCHAR(1000) NULL,
				OBSOLETE TINYINT(1) NOT NULL DEFAULT 0,
				INVOICE_ID INT(11) NULL,
				PRIMARY KEY (`$id_name`)
 				";
			$select_fields = array($id_name, 'JOB_ID', 'CLIENT2_ID', 'EM_DT', 'EM_TO', 'EM_CC', 'EM_BCC', 'EM_SUBJECT', 'EM_MESSAGE',
									'EM_ATTACH', 'OBSOLETE', 'INVOICE_ID');
			$fields_number = array($id_name, 'JOB_ID', 'CLIENT2_ID', 'OBSOLETE', 'INVOICE_ID');
			$fields_date = array('EM_DT');
			$fields_encrypted = array('EM_TO', 'EM_CC', 'EM_BCC', 'EM_SUBJECT', 'EM_MESSAGE');
			$fields_nullable = array('JOB_ID', 'CLIENT2_ID', 'EM_CC', 'EM_BCC', 'EM_SUBJECT', 'EM_MESSAGE', 'EM_ATTACH',
										'INVOICE_ID');
			break;
		case 'FEEDBACK':
			$id_name = 'FEEDBACK_ID';
			$create_fields = "
				`$id_name` INT(11) NOT NULL AUTO_INCREMENT,
				F_ADDED_ID INT(11) NOT NULL,
				F_ADDED_DT DATETIME NOT NULL,
				F_NATURE INT(11) NULL,
				F_TEXT VARCHAR(4000) NULL,
				F_RESPONSE VARCHAR(4000) NULL,
				F_RESOLVED_DT DATETIME NULL,
				PRIMARY KEY (`$id_name`)
 				";
			$select_fields = array($id_name, 'F_ADDED_ID', 'F_ADDED_DT', 'F_NATURE', 'F_TEXT', 'F_RESPONSE', 'F_RESOLVED_DT');
			$fields_number = array($id_name, 'F_ADDED_ID', 'F_NATURE');
			$fields_date = array('F_ADDED_DT', 'F_RESOLVED_DT');
			$fields_encrypted = array();
			$fields_nullable = array('F_NATURE', 'F_TEXT', 'F_RESPONSE', 'F_RESOLVED_DT');
			break;
		case 'INV_ALLOC':
			$id_name = 'INV_ALLOC_ID';
			$create_fields = "
				`$id_name` INT(11) NOT NULL AUTO_INCREMENT,
				INVOICE_ID INT(11) NOT NULL,
				INV_RECP_ID INT(11) NOT NULL,
				AL_AMOUNT DECIMAL(10, 2) NOT NULL,
				IMPORTED TINYINT(1) NOT NULL DEFAULT 0,
				AL_SYS_IMP VARCHAR(1) NULL,
				Z_DOCTYPE VARCHAR(1) NULL,
				Z_DOCAMOUNT DECIMAL(10, 2) NULL,
				PRIMARY KEY (`$id_name`),
				KEY INV_RECP_ID (INV_RECP_ID),
				KEY INVOICE_ID (INVOICE_ID)
 				";
			$select_fields = array($id_name, 'INVOICE_ID', 'INV_RECP_ID', 'AL_AMOUNT', 'IMPORTED', 'AL_SYS_IMP',
									'Z_DOCTYPE', 'Z_DOCAMOUNT');
			$fields_number = array($id_name, 'INVOICE_ID', 'INV_RECP_ID', 'AL_AMOUNT', 'IMPORTED', 'Z_DOCAMOUNT');
			$fields_date = array();
			$fields_encrypted = array();
			$fields_nullable = array('AL_SYS_IMP', 'Z_DOCTYPE', 'Z_DOCAMOUNT');
			break;
		case 'INV_BILLING':
			$id_name = 'INV_BILLING_ID';
			$create_fields = "
				`$id_name` INT(11) NOT NULL AUTO_INCREMENT,
				INVOICE_ID INT(11) NULL,
				INV_NUM INT(11) NULL,
				JOB_ID INT(11) NULL,
				BL_SYS VARCHAR(1) NULL,
				BL_SYS_IMP VARCHAR(1) NULL,
				BL_DESCR VARCHAR(1000) NULL,
				BL_COST DECIMAL(10, 2) NULL,
				BL_LPOS INT(11) NULL,
				BL_LETTER_DT DATETIME NULL,
				IMPORTED TINYINT(1) NOT NULL DEFAULT 0,
				OBSOLETE TINYINT(1) NOT NULL DEFAULT 0,
				Z_CLID INT(11) NULL,
				Z_DOCTYPE VARCHAR(1) NULL,
				Z_S_INVS VARCHAR(3) NULL,
				Z_NSECS INT(11) NULL,
				PRIMARY KEY (`$id_name`),
				KEY INVOICE_ID (INVOICE_ID),
				KEY JOB_ID (JOB_ID),
				KEY INV_NUM (INV_NUM)
 				";
			$select_fields = array($id_name, 'INVOICE_ID', 'INV_NUM', 'JOB_ID', 'BL_SYS', 'BL_SYS_IMP', 'BL_DESCR', 'BL_COST',
									'BL_LPOS', 'BL_LETTER_DT', 'IMPORTED', 'OBSOLETE', 'Z_CLID', 'Z_DOCTYPE', 'Z_S_INVS',
									'Z_NSECS');
			$fields_number = array($id_name, 'INVOICE_ID', 'INV_NUM', 'JOB_ID', 'BL_COST', 'BL_LPOS', 'IMPORTED', 'OBSOLETE',
									'Z_CLID', 'Z_NSECS');
			$fields_date = array('BL_LETTER_DT');
			$fields_encrypted = array();
			$fields_nullable = array('INVOICE_ID', 'INV_NUM', 'JOB_ID', 'BL_SYS', 'BL_SYS_IMP', 'BL_DESCR', 'BL_COST', 'BL_LPOS',
										'BL_LETTER_DT', 'Z_CLID', 'Z_DOCTYPE', 'Z_S_INVS', 'Z_NSECS');
			break;
		case 'INV_RECP':
			$id_name = 'INV_RECP_ID';
			$create_fields = "
				`$id_name` INT(11) NOT NULL AUTO_INCREMENT,
				RC_NUM INT(11) NOT NULL,
				CLIENT2_ID INT(11) NOT NULL,
				RC_DT DATETIME NULL,
				RC_AMOUNT DECIMAL(10, 2) NOT NULL,
				RC_ADJUST TINYINT(1) NOT NULL DEFAULT 0,
				RC_NOTES BLOB NULL,
				IMPORTED TINYINT(1) NOT NULL DEFAULT 0,
				RC_SYS_IMP VARCHAR(1) NULL,
				OBSOLETE TINYINT(1) NOT NULL DEFAULT 0,
				Z_COMPLETE VARCHAR(1) NULL,
				PRIMARY KEY (`$id_name`),
				KEY CLIENT2_ID (CLIENT2_ID),
				KEY RC_NUM (RC_NUM)
 				";
			$select_fields = array($id_name, 'RC_NUM', 'CLIENT2_ID', 'RC_DT', 'RC_AMOUNT', 'RC_ADJUST', 'RC_NOTES', 'IMPORTED',
									'RC_SYS_IMP', 'OBSOLETE', 'Z_COMPLETE');
			$fields_number = array($id_name, 'RC_NUM', 'CLIENT2_ID', 'RC_AMOUNT', 'RC_ADJUST', 'IMPORTED', 'OBSOLETE');
			$fields_date = array('RC_DT');
			$fields_encrypted = array('RC_NOTES');
			$fields_nullable = array('RC_DT', 'RC_NOTES', 'RC_SYS_IMP', 'Z_COMPLETE');
			break;
		case 'INVOICE':
			$id_name = 'INVOICE_ID';
			$create_fields = "
				`$id_name` INT(11) NOT NULL AUTO_INCREMENT,
				INV_NUM INT(11) NOT NULL,
				INV_SYS VARCHAR(1) NOT NULL,
				INV_SYS_IMP VARCHAR(1) NULL,
				INV_TYPE VARCHAR(1) NOT NULL,
				INV_DT DATETIME NOT NULL,
				INV_DUE_DT DATETIME NULL,
				CLIENT2_ID INT(11) NOT NULL,
				INV_NET DECIMAL(10, 2) NOT NULL DEFAULT 0,
				INV_VAT DECIMAL(10, 2) NOT NULL DEFAULT 0,
				INV_RX DECIMAL(10, 2) NULL,
				INV_PAID DECIMAL(10, 2) NOT NULL DEFAULT 0,
				INV_STMT TINYINT(1) NOT NULL DEFAULT 0,
				INV_S_INVS VARCHAR(1) NULL,
				INV_START_DT DATETIME NULL,
				INV_END_DT DATETIME NULL,
				INV_EMAIL_ID INT(11) NULL,
				INV_POSTED_DT DATETIME NULL,
				INV_COMPLETE TINYINT(1) NOT NULL DEFAULT 0,
				INV_APPROVED_DT DATETIME NULL,
				INV_NOTES BLOB NULL,
				LINKED_ID INT(11) NULL,
				OBSOLETE TINYINT(1) NOT NULL DEFAULT 0,
				IMPORTED TINYINT(1) NOT NULL DEFAULT 0,
				Z_EOM VARCHAR(5) NULL,
				PRIMARY KEY (`$id_name`),
				KEY CLIENT2_ID (CLIENT2_ID),
				KEY INV_DT (INV_DT),
				KEY INV_NUM (INV_NUM),
				KEY LINKED_ID (LINKED_ID)
 				";
			$select_fields = array($id_name, 'INV_NUM', 'INV_SYS', 'INV_SYS_IMP', 'INV_TYPE', 'INV_DT', 'INV_DUE_DT', 'CLIENT2_ID', 'INV_NET', 'INV_VAT',
									'INV_RX', 'INV_PAID', 'INV_STMT', 'INV_S_INVS', 'INV_START_DT', 'INV_END_DT', 'INV_EMAIL_ID', 'INV_POSTED_DT',
									'INV_COMPLETE', 'INV_APPROVED_DT', 'INV_NOTES', 'LINKED_ID', 'OBSOLETE', 'IMPORTED', 'Z_EOM');
			$fields_number = array($id_name, 'INV_NUM', 'CLIENT2_ID', 'INV_NET', 'INV_VAT', 'INV_RX', 'INV_PAID', 'INV_STMT', 'INV_EMAIL_ID',
									'INV_COMPLETE', 'LINKED_ID', 'OBSOLETE', 'IMPORTED');
			$fields_date = array('INV_DT', 'INV_DUE_DT', 'INV_START_DT', 'INV_END_DT', 'INV_POSTED_DT', 'INV_APPROVED_DT');
			$fields_encrypted = array('INV_NOTES');
			$fields_nullable = array('INV_SYS_IMP', 'INV_DUE_DT', 'INV_RX', 'INV_S_INVS', 'INV_START_DT', 'INV_END_DT', 'INV_EMAIL_ID', 'INV_POSTED_DT',
									'INV_APPROVED_DT', 'INV_NOTES', 'LINKED_ID', 'Z_EOM');
			break;
		case 'JOB':
			$id_name = 'JOB_ID';
			$create_fields = "
				" . /*  0 */ "
				`$id_name` INT(11) NOT NULL AUTO_INCREMENT,
				CLIENT2_ID INT(11) NOT NULL,
				CLIENT_REF BLOB NULL,
				JOB_CLOSED TINYINT(1) NOT NULL DEFAULT 0,
				J_COMPLETE INT(11) NULL,
				J_VILNO INT(11) NOT NULL,
				J_SEQUENCE INT(11) NOT NULL,
				J_INHOUSE TINYINT(1) NOT NULL DEFAULT 0,
				J_USER_ID INT(11) NULL,
				J_USER_DT DATETIME NULL,
				" . /* 10 */ "
				J_OPENED_DT DATETIME NULL,
				J_UPDATED_DT DATETIME NULL,
				J_CLOSED_DT DATETIME NULL,
				J_CLOSED_ID INT(11) NULL,
				J_DIARY_DT DATETIME NULL,
				J_DIARY_TXT BLOB NULL,
				J_TURN_H INT(11) NULL,
				J_TURN_D INT(11) NULL,
				J_TARGET_DT DATETIME NULL,
				J_REFERRER BLOB NULL,
				" . /* 20 */ "
				J_S_INVS TINYINT(1) NOT NULL,
				JOB_GROUP_ID INT(11) NULL,
				J_AVAILABLE TINYINT(1) NOT NULL DEFAULT 0,
				IMPORTED TINYINT(1) NOT NULL,
				VS_2015 TINYINT(1) NOT NULL DEFAULT 0,
				J_ARCHIVED TINYINT(1) NOT NULL DEFAULT 0,
				OBSOLETE TINYINT(1) NOT NULL DEFAULT 0,
				JT_JOB TINYINT(1) NOT NULL,
				JT_SUCCESS INT(11) NULL,
				JT_CREDIT INT(11) NULL,
				" . /* 30 */ "
				JT_JOB_TYPE_ID INT(11) NULL,
				JT_JOB_TARGET_ID INT(11) NULL,
				JT_FEE_Y DECIMAL(12, 2) NULL,
				JT_FEE_N DECIMAL(12, 2) NULL,
				JT_TM_T_FEE DECIMAL(12, 2) NULL,
				JT_TM_T_COMP TINYINT(1) NOT NULL DEFAULT 0,
				JT_TM_M_COMP TINYINT(1) NOT NULL DEFAULT 0,
				JT_BACK_DT DATETIME NULL,
				JT_PROPERTY INT(11) NULL,
				JT_AMOUNT DECIMAL(12, 2) NULL,
				" . /* 40 */ "
				JT_LET_REPORT BLOB NULL,
				JT_REPORT_APPR DATETIME NULL,
				JT_LET_PEND TINYINT(1) NOT NULL DEFAULT 0,
				JT_LET_PRINT TINYINT(1) NOT NULL DEFAULT 0,
				JC_JOB TINYINT(1) NOT NULL,
				JC_TC_JOB TINYINT(1) NOT NULL DEFAULT 0,
				JC_JOB_STATUS_ID INT(11) NULL,
				JC_PERCENT DECIMAL(12, 3) NULL,
				JC_TOTAL_AMT DECIMAL(19, 4) NULL,
				JC_PAYMENT_METHOD_ID INT(11) NULL,
				" . /* 50 */ "
				JC_INSTAL_FREQ VARCHAR(1) NULL,
				JC_INSTAL_AMT DECIMAL(19, 4) NULL,
				JC_INSTAL_DT_1 DATETIME NULL,
				JC_PAID_SO_FAR DECIMAL(12, 2) NULL,
				JC_PAID_IN_FULL TINYINT(1) NOT NULL DEFAULT 0,
				JC_ADJUSTMENT DECIMAL(12, 2) NULL,
				JC_ADJ_OPTION VARCHAR(1) NULL,
				JC_ADJ_TEXT VARCHAR(1000) NULL,
				JC_REVIEW_DT DATETIME NULL,
				JC_REVIEW_D_OLD INT(11) NULL,
				" . /* 60 */ "
				JC_LETTER_MORE TINYINT(1) NOT NULL DEFAULT 0,
				JC_LETTER_TYPE_ID INT(11) NULL,
				JC_IN_PROGRESS_CU1 TINYINT(1) NOT NULL DEFAULT 0,
				JC_SUBJ_PAID_CU2 TINYINT(1) NOT NULL DEFAULT 0,
				JC_PAID_DT_CU2 DATETIME NULL,
				JC_PAID_AMT_CU2 DECIMAL(12, 2) NULL,
				JC_PDCHEQUES_CU3 TINYINT(1) NOT NULL DEFAULT 0,
				JC_PDC_TXT_CU3 VARCHAR(500) NULL,
				JC_SUBJ_CONT_CU4 TINYINT(1) NOT NULL DEFAULT 0,
				JC_AGREED_CU5 TINYINT(1) NOT NULL DEFAULT 0,
				" . /* 70 */ "
				JC_AGR_TXT_CU5 VARCHAR(500) NULL,
				JC_NEW_ADR_CU6 TINYINT(1) NOT NULL DEFAULT 0,
				JC_ADDR_1_CU6 BLOB NULL,
				JC_ADDR_2_CU6 BLOB NULL,
				JC_ADDR_3_CU6 BLOB NULL,
				JC_ADDR_4_CU6 BLOB NULL,
				JC_ADDR_5_CU6 BLOB NULL,
				JC_ADDR_PC_CU6 BLOB NULL,
				JC_NO_ADDR_CU7 TINYINT(1) NOT NULL DEFAULT 0,
				JC_FAIL_PROM_CU8 TINYINT(1) NOT NULL DEFAULT 0,
				" . /* 80 */ "
				JC_NOT_RESP_CU9 TINYINT(1) NOT NULL DEFAULT 0,
				JC_ADD_NOTES_CU10 TINYINT(1) NOT NULL DEFAULT 0,
				JC_AN1_CU10 BLOB NULL,
				JC_AN2_CU10 BLOB NULL,
				JC_AN3_CU10 BLOB NULL,
				JC_MIN_SETT DECIMAL(12, 2) NULL,
				JC_IR_ADDR_1 BLOB NULL,
				JC_IR_ADDR_2 BLOB NULL,
				JC_IR_ADDR_3 BLOB NULL,
				JC_IR_ADDR_4 BLOB NULL,
				" . /* 90 */ "
				JC_IR_ADDR_5 BLOB NULL,
				JC_IR_ADDR_PC BLOB NULL,
				JC_TRANS_ID VARCHAR(100) NULL,
				JC_IMP_NOTES_VMAX TEXT NULL,
				J_FRONT_DETAILS VARCHAR(200) NULL,
				JC_REASON BLOB NULL,
				JC_REASON_2 BLOB NULL,
				JC_LETTER_DELAY INT(11) NULL DEFAULT 0,
				J_BULK TINYINT(1) NOT NULL DEFAULT 0,
				" . /* 99 */ "
				JC_TRANS_CNUM VARCHAR(100) NULL,
				PRIMARY KEY (`$id_name`),
				KEY CLIENT2_ID (CLIENT2_ID),
				KEY J_ARCHIVED (J_ARCHIVED),
				KEY J_COMPLETE (J_COMPLETE),
				KEY J_USER_ID (J_USER_ID),
				KEY JC_JOB (JC_JOB),
				KEY JOB_CLOSED (JOB_CLOSED),
				KEY OBSOLETE (OBSOLETE),
				KEY JC_JOB_STATUS_ID (JC_JOB_STATUS_ID),
				KEY JT_JOB_TYPE_ID (JT_JOB_TYPE_ID),
				KEY JT_JOB (JT_JOB),
				KEY J_SEQUENCE (J_SEQUENCE),
				KEY J_VILNO (J_VILNO),
				KEY JOB_ARCH_OBJ (J_ARCHIVED, OBSOLETE),
				KEY JOB_GRP_OBS_ID (JOB_GROUP_ID, OBSOLETE, JOB_ID)
 				";
				# JC_IMP_NOTES TEXT NULL,
				# REASON2 VARCHAR(4000) NULL,
				# Note: fields REASON2 and JC_IMP_NOTES are in local DB but not live DB.
			$select_fields = array(		$id_name, 'CLIENT2_ID', 'CLIENT_REF', 'JOB_CLOSED', 'J_COMPLETE', 'J_VILNO', 'J_SEQUENCE', 'J_INHOUSE',
										'J_USER_ID', 'J_USER_DT', 'J_OPENED_DT', 'J_UPDATED_DT', 'J_CLOSED_DT', 'J_CLOSED_ID', 'J_DIARY_DT',
										'J_DIARY_TXT', 'J_TURN_H', 'J_TURN_D', 'J_TARGET_DT', 'J_REFERRER', 'J_S_INVS', 'JOB_GROUP_ID', 'J_AVAILABLE',
										'IMPORTED', 'VS_2015', 'J_ARCHIVED', 'OBSOLETE', 'JT_JOB', 'JT_SUCCESS', 'JT_CREDIT', 'JT_JOB_TYPE_ID',
										'JT_JOB_TARGET_ID', 'JT_FEE_Y', 'JT_FEE_N', 'JT_TM_T_FEE', 'JT_TM_T_COMP', 'JT_TM_M_COMP', 'JT_BACK_DT',
										'JT_PROPERTY', 'JT_AMOUNT', 'JT_LET_REPORT', 'JT_REPORT_APPR', 'JT_LET_PEND', 'JT_LET_PRINT', 'JC_JOB',
										'JC_TC_JOB', 'JC_JOB_STATUS_ID', 'JC_PERCENT', 'JC_TOTAL_AMT', 'JC_PAYMENT_METHOD_ID', 'JC_INSTAL_FREQ',
										'JC_INSTAL_AMT', 'JC_INSTAL_DT_1', 'JC_PAID_SO_FAR', 'JC_PAID_IN_FULL', 'JC_ADJUSTMENT', 'JC_ADJ_OPTION',
										'JC_ADJ_TEXT', 'JC_REVIEW_DT', 'JC_REVIEW_D_OLD', 'JC_LETTER_MORE', 'JC_LETTER_TYPE_ID',
										'JC_IN_PROGRESS_CU1', 'JC_SUBJ_PAID_CU2', 'JC_PAID_DT_CU2', 'JC_PAID_AMT_CU2', 'JC_PDCHEQUES_CU3',
										'JC_PDC_TXT_CU3', 'JC_SUBJ_CONT_CU4', 'JC_AGREED_CU5', 'JC_AGR_TXT_CU5', 'JC_NEW_ADR_CU6', 'JC_ADDR_1_CU6',
										'JC_ADDR_2_CU6', 'JC_ADDR_3_CU6', 'JC_ADDR_4_CU6', 'JC_ADDR_5_CU6', 'JC_ADDR_PC_CU6', 'JC_NO_ADDR_CU7',
										'JC_FAIL_PROM_CU8', 'JC_NOT_RESP_CU9', 'JC_ADD_NOTES_CU10', 'JC_AN1_CU10', 'JC_AN2_CU10', 'JC_AN3_CU10',
										'JC_MIN_SETT', 'JC_IR_ADDR_1', 'JC_IR_ADDR_2', 'JC_IR_ADDR_3', 'JC_IR_ADDR_4', 'JC_IR_ADDR_5', 'JC_IR_ADDR_PC',
										'JC_TRANS_ID', 'JC_IMP_NOTES_VMAX', 'J_FRONT_DETAILS', 'JC_REASON', 'JC_REASON_2',
										'JC_LETTER_DELAY', 'J_BULK', 'JC_TRANS_CNUM');
			$fields_number = array(		$id_name, 'CLIENT2_ID', 'JOB_CLOSED', 'J_COMPLETE', 'J_VILNO', 'J_SEQUENCE', 'J_INHOUSE', 'J_USER_ID',
										'J_CLOSED_ID', 'J_TURN_H', 'J_TURN_D', 'J_S_INVS', 'JOB_GROUP_ID', 'J_AVAILABLE', 'IMPORTED', 'VS_2015',
										'J_ARCHIVED', 'OBSOLETE', 'JT_JOB', 'JT_SUCCESS', 'JT_CREDIT', 'JT_JOB_TYPE_ID', 'JT_JOB_TARGET_ID', 'JT_FEE_Y',
										'JT_FEE_N', 'JT_TM_T_FEE', 'JT_TM_T_COMP', 'JT_TM_M_COMP', 'JT_PROPERTY', 'JT_AMOUNT', 'JT_LET_PEND',
										'JT_LET_PRINT', 'JC_JOB', 'JC_TC_JOB', 'JC_JOB_STATUS_ID', 'JC_PERCENT', 'JC_TOTAL_AMT', 'JC_PAYMENT_METHOD_ID',
										'JC_INSTAL_AMT', 'JC_PAID_SO_FAR', 'JC_PAID_IN_FULL', 'JC_ADJUSTMENT', 'JC_REVIEW_D_OLD', 'JC_LETTER_MORE',
										'JC_LETTER_TYPE_ID', 'JC_IN_PROGRESS_CU1', 'JC_SUBJ_PAID_CU2', 'JC_PAID_AMT_CU2', 'JC_PDCHEQUES_CU3',
										'JC_SUBJ_CONT_CU4', 'JC_AGREED_CU5', 'JC_NEW_ADR_CU6', 'JC_NO_ADDR_CU7', 'JC_FAIL_PROM_CU8', 'JC_NOT_RESP_CU9',
										'JC_ADD_NOTES_CU10', 'JC_MIN_SETT', 'JC_LETTER_DELAY', 'J_BULK');
			$fields_date = array(		'J_USER_DT', 'J_OPENED_DT', 'J_UPDATED_DT', 'J_CLOSED_DT', 'J_DIARY_DT', 'J_TARGET_DT', 'JT_BACK_DT',
										'JT_REPORT_APPR', 'JC_INSTAL_DT_1', 'JC_REVIEW_DT', 'JC_PAID_DT_CU2');
			$fields_encrypted = array(	'CLIENT_REF', 'J_DIARY_TXT', 'J_REFERRER', 'JT_LET_REPORT', 'JC_ADDR_1_CU6', 'JC_ADDR_2_CU6', 'JC_ADDR_3_CU6',
										'JC_ADDR_4_CU6', 'JC_ADDR_5_CU6', 'JC_ADDR_PC_CU6', 'JC_AN1_CU10', 'JC_AN2_CU10', 'JC_AN3_CU10', 'JC_IR_ADDR_1',
										'JC_IR_ADDR_2', 'JC_IR_ADDR_3', 'JC_IR_ADDR_4', 'JC_IR_ADDR_5', 'JC_IR_ADDR_PC', 'JC_REASON', 'JC_REASON_2');
			$fields_nullable = array(	'CLIENT_REF', 'J_COMPLETE', 'J_USER_ID', 'J_USER_DT', 'J_OPENED_DT', 'J_UPDATED_DT', 'J_CLOSED_DT', 'J_CLOSED_ID',
										'J_DIARY_DT', 'J_DIARY_TXT', 'J_TURN_H', 'J_TURN_D', 'J_TARGET_DT', 'J_REFERRER', 'JOB_GROUP_ID', 'JT_SUCCESS',
										'JT_CREDIT', 'JT_JOB_TYPE_ID', 'JT_JOB_TARGET_ID', 'JT_FEE_Y', 'JT_FEE_N', 'JT_TM_T_FEE', 'JT_BACK_DT',
										'JT_PROPERTY', 'JT_AMOUNT', 'JT_LET_REPORT', 'JT_REPORT_APPR', 'JC_JOB_STATUS_ID', 'JC_PERCENT', 'JC_TOTAL_AMT',
										'JC_PAYMENT_METHOD_ID', 'JC_INSTAL_FREQ', 'JC_INSTAL_AMT', 'JC_INSTAL_DT_1', 'JC_PAID_SO_FAR', 'JC_ADJUSTMENT',
										'JC_ADJ_OPTION', 'JC_ADJ_TEXT', 'JC_REVIEW_DT', 'JC_REVIEW_D_OLD', 'JC_LETTER_TYPE_ID', 'JC_PAID_DT_CU2',
										'JC_PAID_AMT_CU2', 'JC_PDC_TXT_CU3', 'JC_AGR_TXT_CU5', 'JC_ADDR_1_CU6', 'JC_ADDR_2_CU6', 'JC_ADDR_3_CU6',
										'JC_ADDR_4_CU6', 'JC_ADDR_5_CU6', 'JC_ADDR_PC_CU6', 'JC_AN1_CU10', 'JC_AN2_CU10', 'JC_AN3_CU10', 'JC_MIN_SETT',
										'JC_IR_ADDR_1', 'JC_IR_ADDR_2', 'JC_IR_ADDR_3', 'JC_IR_ADDR_4', 'JC_IR_ADDR_5', 'JC_IR_ADDR_PC', 'JC_TRANS_ID',
										'JC_IMP_NOTES_VMAX', 'J_FRONT_DETAILS', 'JC_REASON', 'JC_REASON_2', 'JC_LETTER_DELAY',
										'JC_TRANS_CNUM');
			break;
		case 'JOB_ACT':
			$id_name = 'JOB_ACT_ID';
			$create_fields = "
				`$id_name` INT(11) NOT NULL AUTO_INCREMENT,
				JOB_ID INT(11) NOT NULL,
				ACTIVITY_ID INT(11) NOT NULL,
				JA_DT DATETIME NULL,
				JA_NOTE VARCHAR(2000) NULL,
				QC_EXPORT_ID INT(11) NULL,
				IMPORTED TINYINT(1) NULL DEFAULT 0,
				PRIMARY KEY (`$id_name`),
				KEY ACTIVITY_ID (ACTIVITY_ID),
				KEY JOB_ID (JOB_ID)
 				";
			$select_fields = array($id_name, 'JOB_ID', 'ACTIVITY_ID', 'JA_DT', 'JA_NOTE', 'QC_EXPORT_ID', 'IMPORTED');
			$fields_number = array($id_name, 'JOB_ID', 'ACTIVITY_ID', 'QC_EXPORT_ID', 'IMPORTED');
			$fields_date = array('JA_DT');
			$fields_encrypted = array();
			$fields_nullable = array('JA_DT', 'JA_NOTE', 'QC_EXPORT_ID');
			break;
		case 'JOB_ARRANGE':
			$id_name = 'JOB_ARRANGE_ID';
			$create_fields = "
				`$id_name` INT(11) NOT NULL AUTO_INCREMENT,
				JOB_ID INT(11) NOT NULL,
				JA_DT DATETIME NULL,
				JA_INSTAL_FREQ VARCHAR(1) NULL,
				JA_INSTAL_AMT DECIMAL(19, 4) NULL,
				JA_INSTAL_DT_1 DATETIME NULL,
				JA_PAYMENT_METHOD_ID INT(11) NULL,
				JA_METHOD_NUMBER BLOB NULL,
				JA_TOTAL DECIMAL(19, 4) NULL,
				IMPORTED TINYINT(1) NOT NULL,
				Z_DEPDATE DATETIME NULL,
				Z_DEPAMOUNT DECIMAL(12, 2) NULL,
				Z_REMAMOUNT DECIMAL(12, 2) NULL,
				PRIMARY KEY (`$id_name`),
				KEY JOB_ID (JOB_ID)
 				";
			$select_fields = array($id_name, 'JOB_ID', 'JA_DT', 'JA_INSTAL_FREQ', 'JA_INSTAL_AMT', 'JA_INSTAL_DT_1', 'JA_PAYMENT_METHOD_ID', 'JA_METHOD_NUMBER',
									'JA_TOTAL', 'IMPORTED', 'Z_DEPDATE', 'Z_DEPAMOUNT', 'Z_REMAMOUNT');
			$fields_number = array($id_name, 'JOB_ID', 'JA_INSTAL_AMT', 'JA_PAYMENT_METHOD_ID', 'JA_TOTAL', 'IMPORTED', 'Z_DEPAMOUNT', 'Z_REMAMOUNT');
			$fields_date = array('JA_DT', 'JA_INSTAL_DT_1', 'Z_DEPDATE');
			$fields_encrypted = array('JA_METHOD_NUMBER');
			$fields_nullable = array('JA_DT', 'JA_INSTAL_FREQ', 'JA_INSTAL_AMT', 'JA_INSTAL_DT_1', 'JA_PAYMENT_METHOD_ID', 'JA_METHOD_NUMBER', 'JA_TOTAL',
									'Z_DEPDATE', 'Z_DEPAMOUNT', 'Z_REMAMOUNT');
			break;
		case 'JOB_GROUP':
			$id_name = 'JOB_GROUP_ID';
			$create_fields = "
				`$id_name` INT(11) NOT NULL AUTO_INCREMENT,
				JG_NAME VARCHAR(100) NULL,
				PRIMARY KEY (`$id_name`)
 				";
			$select_fields = array($id_name, 'JG_NAME');
			$fields_number = array($id_name);
			$fields_date = array();
			$fields_encrypted = array();
			$fields_nullable = array('JG_NAME');
			break;
		case 'JOB_LETTER':
			$id_name = 'JOB_LETTER_ID';
			$create_fields = "
				`$id_name` INT(11) NOT NULL AUTO_INCREMENT,
				JOB_ID INT(11) NOT NULL,
				LETTER_TYPE_ID INT(11) NULL,
				JL_ADDED_DT DATETIME NULL,
				JL_APPROVED_DT DATETIME NULL,
				JL_CREATED_DT DATETIME NULL,
				JL_EMAIL_ID INT(11) NULL,
				JL_POSTED_DT DATETIME NULL,
				JL_TEXT BLOB NULL,
				JL_TEXT_2 BLOB NULL,
				JL_UPDATED_ID INT(11) NULL,
				JL_UPDATED_DT DATETIME NULL,
				IMPORTED TINYINT(1) NOT NULL,
				OBSOLETE TINYINT(1) NOT NULL DEFAULT 0,
				JL_AL TINYINT(1) NOT NULL DEFAULT 0,
				JL_EMAIL_RESENDS VARCHAR(2000) NULL,
				PRIMARY KEY (`$id_name`),
				KEY JOB_ID (JOB_ID)
 				";
			$select_fields = array($id_name, 'JOB_ID', 'LETTER_TYPE_ID', 'JL_ADDED_DT', 'JL_APPROVED_DT', 'JL_CREATED_DT', 'JL_EMAIL_ID', 'JL_POSTED_DT',
									'JL_TEXT', 'JL_TEXT_2', 'JL_UPDATED_ID', 'JL_UPDATED_DT', 'IMPORTED', 'OBSOLETE', 'JL_AL', 'JL_EMAIL_RESENDS');
			$fields_number = array($id_name, 'JOB_ID', 'LETTER_TYPE_ID', 'JL_EMAIL_ID', 'JL_UPDATED_ID', 'IMPORTED', 'OBSOLETE', 'JL_AL');
			$fields_date = array('JL_ADDED_DT', 'JL_APPROVED_DT', 'JL_CREATED_DT', 'JL_POSTED_DT', 'JL_UPDATED_DT');
			$fields_encrypted = array('JL_TEXT', 'JL_TEXT_2');
			$fields_nullable = array('LETTER_TYPE_ID', 'JL_ADDED_DT', 'JL_APPROVED_DT', 'JL_CREATED_DT', 'JL_EMAIL_ID', 'JL_POSTED_DT', 'JL_TEXT',
									'JL_TEXT_2', 'JL_UPDATED_ID', 'JL_UPDATED_DT', 'JL_EMAIL_RESENDS');
			break;
		case 'JOB_NOTE':
			$id_name = 'JOB_NOTE_ID';
			$create_fields = "
				`$id_name` INT(11) NOT NULL AUTO_INCREMENT,
				JOB_ID INT(11) NOT NULL,
				J_NOTE BLOB NOT NULL,
				IMPORTED TINYINT(1) NOT NULL,
				JN_ADDED_ID INT(11) NOT NULL,
				JN_ADDED_DT DATETIME NOT NULL,
				JN_UPDATED_ID INT(11) NULL,
				JN_UPDATED_DT DATETIME NULL,
				IMP_2 TINYINT(1) NULL,
				PRIMARY KEY (`$id_name`),
				KEY JOB_ID (JOB_ID)
 				";
			$select_fields = array($id_name, 'JOB_ID', 'J_NOTE', 'IMPORTED', 'JN_ADDED_ID', 'JN_ADDED_DT', 'JN_UPDATED_ID', 'JN_UPDATED_DT', 'IMP_2');
			$fields_number = array($id_name, 'JOB_ID', 'IMPORTED', 'JN_ADDED_ID', 'JN_UPDATED_ID', 'IMP_2');
			$fields_date = array('JN_ADDED_DT', 'JN_UPDATED_DT');
			$fields_encrypted = array('J_NOTE');
			$fields_nullable = array('JN_UPDATED_ID', 'JN_UPDATED_DT', 'IMP_2');
			break;
		case 'JOB_PAYMENT':
			$id_name = 'JOB_PAYMENT_ID';
			$create_fields = "
				`$id_name` INT(11) NOT NULL AUTO_INCREMENT,
				JOB_ID INT(11) NOT NULL,
				COL_PERCENT DECIMAL(12, 3) NULL,
				COL_AMT_DUE DECIMAL(19, 4) NULL,
				COL_AMT_RX DECIMAL(19, 4) NULL,
				COL_PAYMENT_METHOD_ID INT(11) NULL,
				COL_METHOD_NUMBER BLOB NULL,
				COL_DT_DUE DATETIME NULL,
				COL_DT_RX DATETIME NULL,
				COL_PAYMENT_ROUTE_ID INT(11) NULL,
				COL_BOUNCED TINYINT(11) NOT NULL DEFAULT 0,
				COL_NOTES BLOB NULL,
				ADJUSTMENT_ID INT(11) NULL,
				INVOICE_ID INT(11) NULL,
				IMPORTED TINYINT(11) NOT NULL DEFAULT 0,
				OBSOLETE TINYINT(11) NOT NULL DEFAULT 0,
				Z_CLID INT(11) NULL,
				Z_DATE VARCHAR(10) NULL,
				PRIMARY KEY (`$id_name`),
				KEY JOB_ID (JOB_ID)
 				";
			$select_fields = array($id_name, 'JOB_ID', 'COL_PERCENT', 'COL_AMT_DUE', 'COL_AMT_RX', 'COL_PAYMENT_METHOD_ID', 'COL_METHOD_NUMBER',
									'COL_DT_DUE', 'COL_DT_RX', 'COL_PAYMENT_ROUTE_ID', 'COL_BOUNCED', 'COL_NOTES', 'ADJUSTMENT_ID', 'INVOICE_ID',
									'IMPORTED', 'OBSOLETE', 'Z_CLID', 'Z_DATE');
			$fields_number = array($id_name, 'JOB_ID', 'COL_PERCENT', 'COL_AMT_DUE', 'COL_AMT_RX', 'COL_PAYMENT_METHOD_ID', 'COL_PAYMENT_ROUTE_ID',
									'COL_BOUNCED', 'ADJUSTMENT_ID', 'INVOICE_ID', 'IMPORTED', 'OBSOLETE', 'Z_CLID');
			$fields_date = array('COL_DT_DUE', 'COL_DT_RX');
			$fields_encrypted = array('COL_METHOD_NUMBER', 'COL_NOTES');
			$fields_nullable = array('COL_PERCENT', 'COL_AMT_DUE', 'COL_AMT_RX', 'COL_PAYMENT_METHOD_ID', 'COL_METHOD_NUMBER', 'COL_DT_DUE', 'COL_DT_RX',
									'COL_PAYMENT_ROUTE_ID', 'COL_NOTES', 'ADJUSTMENT_ID', 'INVOICE_ID', 'Z_CLID', 'Z_DATE');
			break;
		case 'JOB_PHONE':
			$id_name = 'JOB_PHONE_ID';
			$create_fields = "
				`$id_name` INT(11) NOT NULL AUTO_INCREMENT,
				JOB_ID INT(11) NOT NULL,
				JOB_SUBJECT_ID INT(11) NULL,
				JP_PHONE BLOB NULL,
				JP_PRIMARY_P TINYINT(1) NOT NULL DEFAULT 0,
				JP_EMAIL BLOB NULL,
				JP_PRIMARY_E TINYINT(1) NOT NULL DEFAULT 0,
				JP_DESCR BLOB NULL,
				IMPORTED TINYINT(1) NOT NULL,
				IMP_PH TINYINT(1) NOT NULL,
				OBSOLETE TINYINT(1) NOT NULL DEFAULT 0,
				PRIMARY KEY (`$id_name`),
				KEY JOB_ID (JOB_ID),
				KEY JOB_SUBJECT_ID (JOB_SUBJECT_ID)
 				";
			$select_fields = array($id_name, 'JOB_ID', 'JOB_SUBJECT_ID', 'JP_PHONE', 'JP_PRIMARY_P', 'JP_EMAIL', 'JP_PRIMARY_E', 'JP_DESCR',
									'IMPORTED', 'IMP_PH', 'OBSOLETE');
			$fields_number = array($id_name, 'JOB_ID', 'JOB_SUBJECT_ID', 'JP_PRIMARY_P', 'JP_PRIMARY_E', 'IMPORTED', 'IMP_PH', 'OBSOLETE');
			$fields_date = array();
			$fields_encrypted = array('JP_PHONE', 'JP_EMAIL', 'JP_DESCR');
			$fields_nullable = array('JOB_SUBJECT_ID', 'JP_PHONE', 'JP_EMAIL', 'JP_DESCR');
			break;
		case 'JOB_STATUS_SD':
			$id_name = 'JOB_STATUS_ID';
			$create_fields = "
				`$id_name` INT(11) NOT NULL AUTO_INCREMENT,
				`J_STATUS` VARCHAR(100) NOT NULL,
				`J_STTS_DESCR` VARCHAR(200) NULL,
				`J_STTS_CLOSED` TINYINT(1) NOT NULL,
				`OBSOLETE` TINYINT(1) NOT NULL DEFAULT 0,
				PRIMARY KEY (`$id_name`)
 				";
			$select_fields = array($id_name, 'J_STATUS', 'J_STTS_DESCR', 'J_STTS_CLOSED', 'OBSOLETE');
			$fields_number = array($id_name, 'J_STTS_CLOSED', 'OBSOLETE');
			$fields_date = array();
			$fields_encrypted = array();
			$fields_nullable = array('J_STTS_DESCR');
			break;
		case 'JOB_SUBJECT':
			$id_name = 'JOB_SUBJECT_ID';
			$create_fields = "
				`$id_name` INT(11) NOT NULL AUTO_INCREMENT,
				JOB_ID INT(11) NOT NULL,
				JS_PRIMARY TINYINT(1) NOT NULL,
				JS_TITLE VARCHAR(10) NULL,
				JS_FIRSTNAME BLOB NULL,
				JS_LASTNAME BLOB NOT NULL,
				JS_COMPANY BLOB NULL,
				JS_DOB DATETIME NULL,
				JS_ADDR_1 BLOB NULL,
				JS_ADDR_2 BLOB NULL,
				JS_ADDR_3 BLOB NULL,
				JS_ADDR_4 BLOB NULL,
				JS_ADDR_5 BLOB NULL,
				JS_ADDR_PC BLOB NULL,
				JS_BANK_NAME BLOB NULL,
				JS_BANK_SORTCODE BLOB NULL,
				JS_BANK_ACC_NUM BLOB NULL,
				JS_BANK_ACC_NAME BLOB NULL,
				JS_BANK_COUNTRY VARCHAR(100) NULL,
				NEW_ADDR_1 BLOB NULL,
				NEW_ADDR_2 BLOB NULL,
				NEW_ADDR_3 BLOB NULL,
				NEW_ADDR_4 BLOB NULL,
				NEW_ADDR_5 BLOB NULL,
				NEW_ADDR_PC BLOB NULL,
				IMPORTED TINYINT(1) NOT NULL,
				OBSOLETE TINYINT(1) NOT NULL DEFAULT 0,
				JS_OUTCODE VARCHAR(10) NULL,
				NEW_OUTCODE VARCHAR(10) NULL,
				PRIMARY KEY (`$id_name`),
				KEY JOB_ID (JOB_ID),
				KEY JS_OUTCODE (JS_OUTCODE),
				KEY JS_LASTNAME (JS_LASTNAME(50)),
				KEY NEW_OUTCODE (NEW_OUTCODE)
 				";
			$select_fields = array($id_name, 'JOB_ID', 'JS_PRIMARY', 'JS_TITLE', 'JS_FIRSTNAME', 'JS_LASTNAME', 'JS_COMPANY', 'JS_DOB',
									'JS_ADDR_1', 'JS_ADDR_2', 'JS_ADDR_3', 'JS_ADDR_4', 'JS_ADDR_5', 'JS_ADDR_PC', 'JS_BANK_NAME', 'JS_BANK_SORTCODE',
									'JS_BANK_ACC_NUM', 'JS_BANK_ACC_NAME', 'JS_BANK_COUNTRY', 'NEW_ADDR_1', 'NEW_ADDR_2', 'NEW_ADDR_3', 'NEW_ADDR_4',
									'NEW_ADDR_5', 'NEW_ADDR_PC', 'IMPORTED', 'OBSOLETE', 'JS_OUTCODE', 'NEW_OUTCODE');
			$fields_number = array($id_name, 'JOB_ID', 'JS_PRIMARY', 'IMPORTED', 'OBSOLETE');
			$fields_date = array('JS_DOB');
			$fields_encrypted = array('JS_FIRSTNAME', 'JS_LASTNAME', 'JS_COMPANY', 'JS_ADDR_1', 'JS_ADDR_2', 'JS_ADDR_3', 'JS_ADDR_4', 'JS_ADDR_5',
									'JS_ADDR_PC', 'JS_BANK_NAME', 'JS_BANK_SORTCODE', 'JS_BANK_ACC_NUM', 'JS_BANK_ACC_NAME', 'NEW_ADDR_1', 'NEW_ADDR_2',
									'NEW_ADDR_3', 'NEW_ADDR_4', 'NEW_ADDR_5', 'NEW_ADDR_PC');
			$fields_nullable = array('JS_TITLE', 'JS_FIRSTNAME', 'JS_COMPANY', 'JS_DOB', 'JS_ADDR_1', 'JS_ADDR_2', 'JS_ADDR_3', 'JS_ADDR_4',
									'JS_ADDR_5', 'JS_ADDR_PC', 'JS_BANK_NAME', 'JS_BANK_SORTCODE', 'JS_BANK_ACC_NUM', 'JS_BANK_ACC_NAME',
									'JS_BANK_COUNTRY', 'NEW_ADDR_1', 'NEW_ADDR_2', 'NEW_ADDR_3', 'NEW_ADDR_4', 'NEW_ADDR_5', 'NEW_ADDR_PC',
									'JS_OUTCODE', 'NEW_OUTCODE');
			break;
		case 'JOB_TARGET_SD':
			$id_name = 'JOB_TARGET_ID';
			$create_fields = "
				`$id_name` INT(11) NOT NULL AUTO_INCREMENT,
				`JOB_TYPE_ID` INT(11) NOT NULL,
				`JTA_NAME` VARCHAR(100) NOT NULL,
				`JTA_TIME` INT(11) NOT NULL,
				`JTA_FEE` DECIMAL(10,2) NULL,
				`OBSOLETE` TINYINT(1) NOT NULL DEFAULT 0,
				PRIMARY KEY (`$id_name`)
 				";
			$select_fields = array($id_name, 'JOB_TYPE_ID', 'JTA_NAME', 'JTA_TIME', 'JTA_FEE', 'OBSOLETE');
			$fields_number = array($id_name, 'JOB_TYPE_ID', 'JTA_TIME', 'JTA_FEE', 'OBSOLETE');
			$fields_date = array();
			$fields_encrypted = array();
			$fields_nullable = array('JTA_FEE');
			break;
		case 'JOB_TYPE_SD':
			$id_name = 'JOB_TYPE_ID';
			$create_fields = "
				`$id_name` INT(11) NOT NULL AUTO_INCREMENT,
				`JT_TYPE` VARCHAR(100) NOT NULL,
				`JT_CODE` VARCHAR(10) NOT NULL,
				`JT_FEE` DECIMAL(10,2) NULL,
				`JT_DAYS` INT(11) NULL,
				`OBSOLETE` TINYINT(1) NOT NULL DEFAULT 0,
				PRIMARY KEY (`$id_name`),
				UNIQUE KEY JT_CODE (JT_CODE),
				UNIQUE KEY JT_TYPE (JT_TYPE)
 				";
			$select_fields = array($id_name, 'JT_TYPE', 'JT_CODE', 'JT_FEE', 'JT_DAYS', 'OBSOLETE');
			$fields_number = array($id_name, 'JT_FEE', 'JT_DAYS', 'OBSOLETE');
			$fields_date = array();
			$fields_encrypted = array();
			$fields_nullable = array('JT_FEE', 'JT_DAYS');
			break;
		case 'JOB_Z':
			$id_name = 'JOB_ID';
			$create_fields = "
				`$id_name` INT(11) NOT NULL,
				Z_C_AMOUNTOUT DECIMAL(12, 2) NULL,
				Z_X_AUTO VARCHAR(3) NULL,
				Z_C_COLDATE DATETIME NULL,
				Z_C_CONTLET INT NULL,
				Z_C_DEMLET INT NULL,
				Z_C_L_CONTLET DATETIME NULL,
				Z_C_L_DEMLET DATETIME NULL,
				Z_C_LCOLDATE DATETIME NULL,
				Z_C_LCRDATE DATETIME NULL,
				Z_C_LET1 VARCHAR(3) NULL,
				Z_C_LET1DATE DATETIME NULL,
				Z_C_LET2 VARCHAR(3) NULL,
				Z_C_LET2DATE DATETIME NULL,
				Z_C_LET3 VARCHAR(3) NULL,
				Z_C_LET3DATE DATETIME NULL,
				Z_C_LETPEND VARCHAR(8) NULL,
				Z_C_NUMCOL INT NULL,
				Z_C_PASTPAYCD DATETIME NULL,
				Z_T_LET1 VARCHAR(3) NULL,
				Z_T_DATE1 DATETIME NULL,
				Z_T_LET2 VARCHAR(3) NULL,
				Z_T_DATE2 DATETIME NULL,
				Z_T_LET3 VARCHAR(3) NULL,
				Z_T_DATE3 DATETIME NULL,
				Z_T_LET4 VARCHAR(3) NULL,
				Z_T_DATE4 DATETIME NULL,
				Z_T_LET5 VARCHAR(3) NULL,
				Z_T_DATE5 DATETIME NULL,
				Z_T_LET6 VARCHAR(3) NULL,
				Z_T_DATE6 DATETIME NULL,
				Z_T_LET7 VARCHAR(3) NULL,
				Z_T_DATE7 DATETIME NULL,
				Z_T_LET8 VARCHAR(3) NULL,
				Z_T_DATE8 DATETIME NULL,
				Z_T_LET9 VARCHAR(3) NULL,
				Z_T_DATE9 DATETIME NULL,
				Z_T_LET10 VARCHAR(3) NULL,
				Z_T_DATE10 DATETIME NULL,
				Z_T_LET11 VARCHAR(3) NULL,
				Z_T_DATE11 DATETIME NULL,
				Z_T_LET12 VARCHAR(3) NULL,
				Z_T_DATE12 DATETIME NULL,
				Z_T_LETDATE DATETIME NULL,
				Z_T_REP1 VARCHAR(66) NULL,
				Z_T_REP2 VARCHAR(66) NULL,
				Z_T_REP3 VARCHAR(66) NULL,
				Z_T_REP4 VARCHAR(66) NULL,
				Z_T_REP5 VARCHAR(66) NULL,
				Z_T_REP6 VARCHAR(66) NULL,
				Z_T_REP7 VARCHAR(66) NULL,
				Z_T_REP8 VARCHAR(66) NULL,
				Z_T_REP9 VARCHAR(66) NULL,
				Z_T_REP10 VARCHAR(66) NULL,
				Z_T_REP11 VARCHAR(66) NULL,
				UNIQUE KEY (`$id_name`)
 				";
			$select_fields = array($id_name, 'Z_C_AMOUNTOUT', 'Z_X_AUTO', 'Z_C_COLDATE', 'Z_C_CONTLET', 'Z_C_DEMLET', 'Z_C_L_CONTLET', 'Z_C_L_DEMLET',
									'Z_C_LCOLDATE', 'Z_C_LCRDATE', 'Z_C_LET1', 'Z_C_LET1DATE', 'Z_C_LET2', 'Z_C_LET2DATE', 'Z_C_LET3', 'Z_C_LET3DATE',
									'Z_C_LETPEND', 'Z_C_NUMCOL', 'Z_C_PASTPAYCD', 'Z_T_LET1', 'Z_T_DATE1', 'Z_T_LET2', 'Z_T_DATE2', 'Z_T_LET3',
									'Z_T_DATE3', 'Z_T_LET4', 'Z_T_DATE4', 'Z_T_LET5', 'Z_T_DATE5', 'Z_T_LET6', 'Z_T_DATE6', 'Z_T_LET7', 'Z_T_DATE7',
									'Z_T_LET8', 'Z_T_DATE8', 'Z_T_LET9', 'Z_T_DATE9', 'Z_T_LET10', 'Z_T_DATE10', 'Z_T_LET11', 'Z_T_DATE11', 'Z_T_LET12',
									'Z_T_DATE12', 'Z_T_LETDATE', 'Z_T_REP1', 'Z_T_REP2', 'Z_T_REP3', 'Z_T_REP4', 'Z_T_REP5', 'Z_T_REP6', 'Z_T_REP7',
									'Z_T_REP8', 'Z_T_REP9', 'Z_T_REP10', 'Z_T_REP11');
			$fields_number = array($id_name, 'Z_C_AMOUNTOUT', 'Z_C_CONTLET', 'Z_C_DEMLET', 'Z_C_NUMCOL');
			$fields_date = array('Z_C_COLDATE', 'Z_C_L_CONTLET', 'Z_C_L_DEMLET', 'Z_C_LCOLDATE', 'Z_C_LCRDATE', 'Z_C_LET1DATE', 'Z_C_LET2DATE',
									'Z_C_LET3DATE', 'Z_C_PASTPAYCD', 'Z_T_DATE1', 'Z_T_DATE2', 'Z_T_DATE3', 'Z_T_DATE4', 'Z_T_DATE5', 'Z_T_DATE6',
									'Z_T_DATE7', 'Z_T_DATE8', 'Z_T_DATE9', 'Z_T_DATE10', 'Z_T_DATE11', 'Z_T_DATE12', 'Z_T_LETDATE');
			$fields_encrypted = array();
			$fields_nullable = array('Z_C_AMOUNTOUT', 'Z_X_AUTO', 'Z_C_COLDATE', 'Z_C_CONTLET', 'Z_C_DEMLET', 'Z_C_L_CONTLET', 'Z_C_L_DEMLET',
									'Z_C_LCOLDATE', 'Z_C_LCRDATE', 'Z_C_LET1', 'Z_C_LET1DATE', 'Z_C_LET2', 'Z_C_LET2DATE', 'Z_C_LET3', 'Z_C_LET3DATE',
									'Z_C_LETPEND', 'Z_C_NUMCOL', 'Z_C_PASTPAYCD', 'Z_T_LET1', 'Z_T_DATE1', 'Z_T_LET2', 'Z_T_DATE2', 'Z_T_LET3',
									'Z_T_DATE3', 'Z_T_LET4', 'Z_T_DATE4', 'Z_T_LET5', 'Z_T_DATE5', 'Z_T_LET6', 'Z_T_DATE6', 'Z_T_LET7', 'Z_T_DATE7',
									'Z_T_LET8', 'Z_T_DATE8', 'Z_T_LET9', 'Z_T_DATE9', 'Z_T_LET10', 'Z_T_DATE10', 'Z_T_LET11', 'Z_T_DATE11', 'Z_T_LET12',
									'Z_T_DATE12', 'Z_T_LETDATE', 'Z_T_REP1', 'Z_T_REP2', 'Z_T_REP3', 'Z_T_REP4', 'Z_T_REP5', 'Z_T_REP6', 'Z_T_REP7',
									'Z_T_REP8', 'Z_T_REP9', 'Z_T_REP10', 'Z_T_REP11');
			break;
		case 'LETTER_SEQ':
			$id_name = 'LETTER_SEQ_ID';
			$create_fields = "
				`$id_name` INT(11) NOT NULL AUTO_INCREMENT,
				CLIENT2_ID INT(11) NOT NULL,
				SEQ_NUM INT(11) NOT NULL,
				LETTER_TYPE_ID INT(11) NOT NULL,
				SEQ_DAYS INT(11) NOT NULL,
				OBSOLETE TINYINT(1) NOT NULL DEFAULT 0,
				PRIMARY KEY (`$id_name`),
				KEY CLIENT2_ID (CLIENT2_ID)
 				";
			$select_fields = array($id_name, 'CLIENT2_ID', 'SEQ_NUM', 'LETTER_TYPE_ID', 'SEQ_DAYS', 'OBSOLETE');
			$fields_number = array($id_name, 'CLIENT2_ID', 'SEQ_NUM', 'LETTER_TYPE_ID', 'SEQ_DAYS', 'OBSOLETE');
			$fields_date = array();
			$fields_encrypted = array();
			$fields_nullable = array();
			break;
		case 'LETTER_TYPE_SD':
			$id_name = 'LETTER_TYPE_ID';
			$create_fields = "
				`$id_name` INT(11) NOT NULL AUTO_INCREMENT,
				`LETTER_NAME` VARCHAR(100) NOT NULL,
				`JT_T_JOB_TYPE_ID` INT(11) NULL,
				`LETTER_DESCR` VARCHAR(500) NULL,
				`LETTER_TEMPLATE` VARCHAR(6000) NULL,
				`LETTER_EM_SUBJECT` VARCHAR(200) NULL,
				`LETTER_EM_BODY` VARCHAR(1000) NULL,
				`JT_T_SUCC` INT(11) NULL,
				`JT_T_OPEN` VARCHAR(1000) NULL,
				`JT_T_CLOSE` VARCHAR(1000) NULL,
				`LT_NON_MAN` TINYINT(1) NOT NULL DEFAULT 0,
				`LT_AUTO_APP` TINYINT(1) NOT NULL DEFAULT 0,
				`OBSOLETE` TINYINT(1) NOT NULL DEFAULT 0,
				PRIMARY KEY (`$id_name`),
				UNIQUE KEY LETTER_NAME (`LETTER_NAME`)
 				";
			$select_fields = array(		$id_name, 'LETTER_NAME', 'JT_T_JOB_TYPE_ID', 'LETTER_DESCR', 'LETTER_TEMPLATE', 'LETTER_EM_SUBJECT',
										'LETTER_EM_BODY', 'JT_T_SUCC', 'JT_T_OPEN', 'JT_T_CLOSE', 'LT_NON_MAN', 'LT_AUTO_APP', 'OBSOLETE');
			$fields_number = array(		$id_name, 'JT_T_JOB_TYPE_ID', 'JT_T_SUCC', 'LT_NON_MAN', 'LT_AUTO_APP', 'OBSOLETE');
			$fields_date = array();
			$fields_encrypted = array();
			$fields_nullable = array(	'JT_T_JOB_TYPE_ID', 'LETTER_DESCR', 'LETTER_TEMPLATE', 'LETTER_EM_SUBJECT', 'LETTER_EM_BODY', 'JT_T_SUCC',
										'JT_T_OPEN', 'JT_T_CLOSE');
			break;
		case 'MISC_INFO':
			$id_name = 'MISC_INFO_ID';
			$create_fields = "
				`$id_name` INT(11) NOT NULL AUTO_INCREMENT,
				MISC_SYS VARCHAR(1) NULL,
				MISC_KEY VARCHAR(100) NOT NULL,
				MISC_DESCR VARCHAR(1000) NULL,
				MISC_TYPE VARCHAR(1) NOT NULL,
				VALUE_INT INT(11) NULL,
				VALUE_DEC DECIMAL(19, 4) NULL,
				VALUE_TXT VARCHAR(1000) NULL,
				VALUE_DT DATETIME NULL,
				VALUE_ENC BLOB NULL,
				PRIMARY KEY (`$id_name`),
				UNIQUE KEY MISC_KEY (MISC_KEY)
 				";
			$select_fields = array($id_name, 'MISC_SYS', 'MISC_KEY', 'MISC_DESCR', 'MISC_TYPE', 'VALUE_INT', 'VALUE_DEC', 'VALUE_TXT', 'VALUE_DT', 'VALUE_ENC');
			$fields_number = array($id_name, 'VALUE_INT', 'VALUE_DEC');
			$fields_date = array('VALUE_DT');
			$fields_encrypted = array('VALUE_ENC');
			$fields_nullable = array('MISC_SYS', 'MISC_DESCR', 'VALUE_INT', 'VALUE_DEC', 'VALUE_TXT', 'VALUE_DT', 'VALUE_ENC');
			break;
		case 'PAYMENT_METHOD_SD':
			$id_name = 'PAYMENT_METHOD_ID';
			$create_fields = "
 				`$id_name` INT(11) NOT NULL AUTO_INCREMENT,
				`PAYMENT_METHOD` VARCHAR(100) NOT NULL,
				`TDX_CODE` VARCHAR(10) NOT NULL,
				`SORT_ORDER` INT(11) NULL,
				`OBSOLETE` TINYINT(1) NOT NULL DEFAULT 0,
				PRIMARY KEY (`$id_name`),
				UNIQUE KEY PAYMENT_METHOD (PAYMENT_METHOD)
 				";
			$select_fields = array($id_name, 'PAYMENT_METHOD', 'TDX_CODE', 'SORT_ORDER', 'OBSOLETE');
			$fields_number = array($id_name, 'SORT_ORDER', 'OBSOLETE');
			$fields_date = array();
			$fields_encrypted = array();
			$fields_nullable = array('SORT_ORDER');
			break;
		case 'PAYMENT_ROUTE_SD':
			$id_name = 'PAYMENT_ROUTE_ID';
			$create_fields = "
 				`$id_name` INT(11) NOT NULL AUTO_INCREMENT,
				`PAYMENT_ROUTE` VARCHAR(100) NOT NULL,
				`PR_CODE` VARCHAR(10) NOT NULL,
				`SORT_ORDER` INT(11) NULL,
				`OBSOLETE` TINYINT(1) NOT NULL DEFAULT 0,
				PRIMARY KEY (`$id_name`),
				UNIQUE KEY PAYMENT_ROUTE (PAYMENT_ROUTE),
				UNIQUE KEY PR_CODE (PR_CODE)
 				";
			$select_fields = array($id_name, 'PAYMENT_ROUTE', 'PR_CODE', 'SORT_ORDER', 'OBSOLETE');
			$fields_number = array($id_name, 'SORT_ORDER', 'OBSOLETE');
			$fields_date = array();
			$fields_encrypted = array();
			$fields_nullable = array('SORT_ORDER');
			break;
		case 'REPORT':
			$id_name = 'REPORT_ID';
			$create_fields = "
				`$id_name` INT(11) NOT NULL AUTO_INCREMENT,
				REP_NAME VARCHAR(100) NOT NULL,
				REP_USER_ID INT(11) NULL,
				CLIENT_PER_SHEET TINYINT(1) NOT NULL DEFAULT 0,
				SUBT_BY_YEAR TINYINT(1) NOT NULL DEFAULT 0,
				MONTH_RCVD TINYINT(1) NOT NULL DEFAULT 0,
				REP_ANALYSIS TINYINT(1) NOT NULL,
				REP_COLLECT TINYINT(1) NOT NULL,
				REP_JOB_STATUS INT(11) NOT NULL DEFAULT 0,
				REP_PAYMENTS INT(11) NOT NULL DEFAULT 0,
				RUN_RX_DT_FR DATETIME NULL,
				RUN_RX_DT_TO DATETIME NULL,
				RUN_P1_DT_FR DATETIME NULL,
				RUN_P1_DT_TO DATETIME NULL,
				REP_TEMP TINYINT(1) NOT NULL,
				CREATED_DT DATETIME NULL,
				EDITED_DT DATETIME NULL,
				OBSOLETE TINYINT(1) NOT NULL DEFAULT 0,
				PRIMARY KEY (`$id_name`),
				UNIQUE KEY REP_NAME (REP_NAME),
				KEY REP_USER_ID (REP_USER_ID)
 				";
			$select_fields = array($id_name, 'REP_NAME', 'REP_USER_ID', 'CLIENT_PER_SHEET', 'SUBT_BY_YEAR', 'MONTH_RCVD', 'REP_ANALYSIS', 'REP_COLLECT',
									'REP_JOB_STATUS', 'REP_PAYMENTS', 'RUN_RX_DT_FR', 'RUN_RX_DT_TO', 'RUN_P1_DT_FR', 'RUN_P1_DT_TO', 'REP_TEMP',
									'CREATED_DT', 'EDITED_DT', 'OBSOLETE');
			$fields_number = array($id_name, 'REP_USER_ID', 'CLIENT_PER_SHEET', 'SUBT_BY_YEAR', 'MONTH_RCVD', 'REP_ANALYSIS', 'REP_COLLECT',
									'REP_JOB_STATUS', 'REP_PAYMENTS', 'REP_TEMP', 'OBSOLETE');
			$fields_date = array('RUN_RX_DT_FR', 'RUN_RX_DT_TO', 'RUN_P1_DT_FR', 'RUN_P1_DT_TO', 'CREATED_DT', 'EDITED_DT');
			$fields_encrypted = array();
			$fields_nullable = array('REP_USER_ID', 'RUN_RX_DT_FR', 'RUN_RX_DT_TO', 'RUN_P1_DT_FR', 'RUN_P1_DT_TO', 'CREATED_DT', 'EDITED_DT');
			break;
		case 'REPORT_CLIENT_LINK':
			$id_name = 'REPORT_CLIENT_LINK_ID';
			$create_fields = "
				`$id_name` INT(11) NOT NULL AUTO_INCREMENT,
				REPORT_ID INT(11) NOT NULL,
				CLIENT2_ID INT(11) NOT NULL,
				SORT_ORDER INT(11) NULL,
				PRIMARY KEY (`$id_name`),
				KEY CLIENT2_ID (CLIENT2_ID),
				KEY REPORT_ID (REPORT_ID)
 				";
			$select_fields = array($id_name, 'REPORT_ID', 'CLIENT2_ID', 'SORT_ORDER');
			$fields_number = array($id_name, 'REPORT_ID', 'CLIENT2_ID', 'SORT_ORDER');
			$fields_date = array();
			$fields_encrypted = array();
			$fields_nullable = array('SORT_ORDER');
			break;
		case 'REPORT_FIELD_LINK':
			$id_name = 'REPORT_FIELD_LINK_ID';
			$create_fields = "
				`$id_name` INT(11) NOT NULL AUTO_INCREMENT,
				REPORT_ID INT(11) NOT NULL,
				REPORT_FIELD_ID INT(11) NOT NULL,
				SORT_ORDER INT(11) NULL,
				PRIMARY KEY (`$id_name`),
				KEY REPORT_FIELD_ID (REPORT_FIELD_ID),
				KEY REPORT_ID (REPORT_ID)
 				";
			$select_fields = array($id_name, 'REPORT_ID', 'REPORT_FIELD_ID', 'SORT_ORDER');
			$fields_number = array($id_name, 'REPORT_ID', 'REPORT_FIELD_ID', 'SORT_ORDER');
			$fields_date = array();
			$fields_encrypted = array();
			$fields_nullable = array('SORT_ORDER');
			break;
		case 'REPORT_FIELD_SD':
			$id_name = 'REPORT_FIELD_ID';
			$create_fields = "
 				`$id_name` INT(11) NOT NULL AUTO_INCREMENT,
				`RF_CODE` VARCHAR(20) NOT NULL,
				`RF_DESCR` VARCHAR(200) NULL,
				`RF_LONG` TEXT NULL,
				`RF_SEL` TINYINT(1) NOT NULL,
				`RF_ANALYSIS` TINYINT(1) NOT NULL,
				`RF_JOB_DETAIL` TINYINT(1) NOT NULL,
				`RF_COLL` TINYINT(1) NOT NULL,
				`RF_TRACE` TINYINT(1) NOT NULL,
				`SORT_ORDER` INT(11) NOT NULL,
				PRIMARY KEY (`$id_name`),
				UNIQUE KEY RF_CODE (RF_CODE)
 				";
			$select_fields = array(		$id_name, 'RF_CODE', 'RF_DESCR', 'RF_LONG', 'RF_SEL', 'RF_ANALYSIS', 'RF_JOB_DETAIL', 'RF_COLL', 'RF_TRACE',
										'SORT_ORDER');
			$fields_number = array(		$id_name, 'RF_SEL', 'RF_ANALYSIS', 'RF_JOB_DETAIL', 'RF_COLL', 'RF_TRACE', 'SORT_ORDER');
			$fields_date = array();
			$fields_encrypted = array();
			$fields_nullable = array(	'RF_DESCR', 'RF_LONG');
			break;
		case 'SALESPERSON':
			$id_name = 'SALESPERSON_ID';
			$create_fields = "
				`$id_name` INT(11) NOT NULL AUTO_INCREMENT,
				CLIENT2_ID INT(11) NOT NULL,
				SP_USER_ID INT(11) NULL,
				SP_TXT VARCHAR(200) NULL,
				SP_DT DATETIME NULL,
				PRIMARY KEY (`$id_name`),
				KEY CLIENT2_ID (CLIENT2_ID)
 				";
			$select_fields = array($id_name, 'CLIENT2_ID', 'SP_USER_ID', 'SP_TXT', 'SP_DT');
			$fields_number = array($id_name, 'CLIENT2_ID', 'SP_USER_ID');
			$fields_date = array('SP_DT');
			$fields_encrypted = array();
			$fields_nullable = array('SP_USER_ID', 'SP_TXT', 'SP_DT');
			break;
		case 'USER_PERM_LINK':
			$id_name = 'USER_PERM_LINK_ID';
			$create_fields = "
				`$id_name` INT(11) NOT NULL AUTO_INCREMENT,
				`USER_ID` INT(11) NOT NULL,
				`USER_PERMISSION_ID` INT(11) NOT NULL,
				PRIMARY KEY (`$id_name`),
				KEY USER_ID (USER_ID)
 				";
			$select_fields = array($id_name, 'USER_ID', 'USER_PERMISSION_ID');
			$fields_number = array($id_name, 'USER_ID', 'USER_PERMISSION_ID');
			$fields_date = array();
			$fields_encrypted = array();
			$fields_nullable = array();
			break;
		case 'USER_PERMISSION_SD':
			$id_name = 'USER_PERMISSION_ID';
			$create_fields = "
				`$id_name` INT(11) NOT NULL AUTO_INCREMENT,
				`UP_SYS` VARCHAR(1) NOT NULL,
				`UP_CODE` VARCHAR(20) NOT NULL,
				`UP_PERM` VARCHAR(100) NOT NULL,
				`SORT_ORDER` INT(11) NOT NULL,
				`OBSOLETE` TINYINT(1) NOT NULL DEFAULT 0,
				PRIMARY KEY (`$id_name`),
				UNIQUE KEY UP_CODE (UP_CODE),
				UNIQUE KEY UP_PERM (UP_PERM)
 				";
			$select_fields = array($id_name, 'UP_SYS', 'UP_CODE', 'UP_PERM', 'SORT_ORDER', 'OBSOLETE');
			$fields_number = array($id_name, 'SORT_ORDER', 'OBSOLETE');
			$fields_date = array();
			$fields_encrypted = array();
			$fields_nullable = array();
			break;
		case 'USER_ROLE_PERM_LINK':
			$id_name = 'USER_ROLE_PERM_LINK_ID';
			$create_fields = "
				`$id_name` INT(11) NOT NULL AUTO_INCREMENT,
				`USER_ROLE_ID` INT(11) NOT NULL,
				`USER_PERMISSION_ID` INT(11) NOT NULL,
				PRIMARY KEY (`$id_name`),
				KEY USER_ROLE_ID (USER_ROLE_ID)
 				";
			$select_fields = array($id_name, 'USER_ROLE_ID', 'USER_PERMISSION_ID');
			$fields_number = array($id_name, 'USER_ROLE_ID', 'USER_PERMISSION_ID');
			$fields_date = array();
			$fields_encrypted = array();
			$fields_nullable = array();
			break;
		case 'USER_ROLE_SD':
			$id_name = 'USER_ROLE_ID';
			$create_fields = "
				`$id_name` INT(11) NOT NULL AUTO_INCREMENT,
				`UR_ROLE` VARCHAR(100) NOT NULL,
				`UR_CODE` VARCHAR(100) NOT NULL,
				`UR_COMMENT` TEXT NULL,
				`SORT_ORDER` INT(11) NOT NULL,
				`OBSOLETE` TINYINT(1) NOT NULL DEFAULT 0,
				PRIMARY KEY (`$id_name`),
				UNIQUE KEY UR_CODE (UR_CODE),
				UNIQUE KEY UR_ROLE (UR_ROLE)
 				";
			$select_fields = array($id_name, 'UR_ROLE', 'UR_CODE', 'UR_COMMENT', 'SORT_ORDER', 'OBSOLETE');
			$fields_number = array($id_name, 'SORT_ORDER', 'OBSOLETE');
			$fields_date = array();
			$fields_encrypted = array();
			$fields_nullable = array();
			break;
		case 'USERV':
			$id_name = 'USER_ID';
			$create_fields = "
				`$id_name` INT(11) NOT NULL AUTO_INCREMENT,
				`SHADOW_ID` INT(11) NULL,
				`USERNAME` BLOB NOT NULL,
				`ORIG_USERNAME_C` BLOB NULL,
				`ORIG_USERNAME_T` BLOB NULL,
				`PASSWORD` BLOB NOT NULL,
				`U_FIRSTNAME` VARCHAR(100) NOT NULL,
				`U_LASTNAME` BLOB NOT NULL,
				`U_INITIALS` VARCHAR(10) NOT NULL,
				`U_EMAIL` BLOB NULL,
				`USER_ROLE_ID_C` INT(11) NULL,
				`USER_ROLE_ID_T` INT(11) NULL,
				`USER_ROLE_ID_A` INT(11) NULL,
				`IS_ENABLED` TINYINT(1) NOT NULL DEFAULT 1,
				`IS_LOCKED_OUT` TINYINT(1) NOT NULL DEFAULT 0,
				`FAILED_LOGINS` INT(11) NOT NULL DEFAULT 0,
				`CREATED_DT` DATETIME NOT NULL,
				`U_FIRST_DT` DATETIME NULL,
				`U_LAST_DT` DATETIME NULL,
				`U_NOTES` BLOB NULL,
				`U_SALES` TINYINT(1) NOT NULL DEFAULT 0,
				`U_SALES_ISH` TINYINT(1) NOT NULL DEFAULT 0,
				`U_IMPORTED` TINYINT(1) NOT NULL DEFAULT 0,
				`U_SYSTEM` TINYINT(1) NOT NULL DEFAULT 0,
				`U_HOUSE` TINYINT(1) NOT NULL DEFAULT 0,
				`U_HISTORIC` TINYINT(1) NOT NULL DEFAULT 0,
				`U_REP_CUSTOM` TINYINT(1) NOT NULL DEFAULT 0,
				`U_REP_GLOBAL` TINYINT(1) NOT NULL DEFAULT 0,
				`U_REPORT_ID` INT(11) NULL,
				`U_REP_ANALYSIS` TINYINT(1) NOT NULL DEFAULT 0,
				`U_REP_COLLECT` TINYINT(1) NOT NULL DEFAULT 0,
				`U_LAST_SCREEN` VARCHAR(50) NULL,
				`U_DEBUG` TINYINT(1) NOT NULL DEFAULT 0,
				`U_JOB_ID` INT(11) NULL,
				`Z_C_T_LEVEL` VARCHAR(5) NULL,
				`Z_C_RESTRICT` VARCHAR(3) NULL,
				`Z_C_C_LEVEL` VARCHAR(5) NULL,
				`Z_C_TRAY` VARCHAR(5) NULL,
				`Z_C_FILENAME` VARCHAR(8) NULL,
				`Z_T_T_LEVEL` VARCHAR(5) NULL,
				`Z_T_RESTRICT` VARCHAR(3) NULL,
				`Z_T_C_LEVEL` VARCHAR(5) NULL,
				`Z_T_TRAY` VARCHAR(5) NULL,
				`Z_T_FILENAME` VARCHAR(8) NULL,
				`USER_KEY` INT(11) NULL,
				`UKEY_DT` DATETIME NULL,
				`CLIENT2_ID` INT(11) NULL,
				`PORTAL_PUSH` TINYINT(1) NOT NULL DEFAULT 0,
				PRIMARY KEY (`$id_name`),
				KEY CLIENT2_ID (CLIENT2_ID),
				KEY PORTAL_PUSH (PORTAL_PUSH),
				UNIQUE KEY U_INITIALS (U_INITIALS),
				UNIQUE KEY USERNAME (USERNAME(20))
				";
			$select_fields = array(		$id_name, 'SHADOW_ID', 'USERNAME', 'ORIG_USERNAME_C', 'ORIG_USERNAME_T', 'PASSWORD', 'U_FIRSTNAME', 'U_LASTNAME',
										'U_INITIALS', 'U_EMAIL', 'USER_ROLE_ID_C', 'USER_ROLE_ID_T', 'USER_ROLE_ID_A', 'IS_ENABLED', 'IS_LOCKED_OUT',
										'FAILED_LOGINS', 'CREATED_DT', 'U_FIRST_DT', 'U_LAST_DT', 'U_NOTES', 'U_SALES', 'U_SALES_ISH', 'U_IMPORTED',
										'U_SYSTEM', 'U_HOUSE', 'U_HISTORIC', 'U_REP_CUSTOM', 'U_REP_GLOBAL', 'U_REPORT_ID', 'U_REP_ANALYSIS',
										'U_REP_COLLECT', 'U_LAST_SCREEN', 'U_DEBUG', 'U_JOB_ID', 'Z_C_T_LEVEL', 'Z_C_RESTRICT', 'Z_C_C_LEVEL', 'Z_C_TRAY',
										'Z_C_FILENAME', 'Z_T_T_LEVEL', 'Z_T_RESTRICT', 'Z_T_C_LEVEL', 'Z_T_TRAY', 'Z_T_FILENAME', 'USER_KEY', 'UKEY_DT',
										'CLIENT2_ID', 'PORTAL_PUSH');
			$fields_number = array(		$id_name, 'SHADOW_ID', 'USER_ROLE_ID_C', 'USER_ROLE_ID_T', 'USER_ROLE_ID_A', 'IS_ENABLED', 'IS_LOCKED_OUT',
										'FAILED_LOGINS', 'U_SALES', 'U_SALES_ISH', 'U_IMPORTED', 'U_SYSTEM', 'U_HOUSE', 'U_HISTORIC', 'U_REP_CUSTOM',
										'U_REP_GLOBAL', 'U_REPORT_ID', 'U_REP_ANALYSIS', 'U_REP_COLLECT', 'U_DEBUG', 'U_JOB_ID', 'USER_KEY', 'CLIENT2_ID',
										'PORTAL_PUSH');
			$fields_date = array(		'CREATED_DT', 'U_FIRST_DT', 'U_LAST_DT', 'UKEY_DT');
			$fields_encrypted = array(	'USERNAME', 'ORIG_USERNAME_C', 'ORIG_USERNAME_T', 'PASSWORD', 'U_LASTNAME',
										'U_EMAIL', 'U_NOTES');
			$fields_nullable = array(	'SHADOW_ID', 'ORIG_USERNAME_C', 'ORIG_USERNAME_T', 'U_EMAIL', 'USER_ROLE_ID_C', 'USER_ROLE_ID_T', 'USER_ROLE_ID_A',
										'U_FIRST_DT', 'U_LAST_DT', 'U_NOTES', 'U_REPORT_ID', 'U_LAST_SCREEN', 'U_JOB_ID', 'Z_C_T_LEVEL', 'Z_C_RESTRICT',
										'Z_C_C_LEVEL', 'Z_C_TRAY', 'Z_C_FILENAME', 'Z_T_T_LEVEL', 'Z_T_RESTRICT', 'Z_T_C_LEVEL', 'Z_T_TRAY', 'Z_T_FILENAME',
										'USER_KEY', 'UKEY_DT', 'CLIENT2_ID');
			break;
		default:
			dlog("*=* Error Invalid table/4 \"$table\" *=*");
			break;
	} # switch ($table)

	if ($create_fields)
	{
		if (0 < $mysql_count) #($incremental_run && )
			$rc = true;
		else
		{
			mysqli_query($my_sql_local_conn, "DROP TABLE $table");
			$sql = "CREATE TABLE $table ($create_fields) ENGINE=INNODB  DEFAULT CHARSET=UTF8 AUTO_INCREMENT=1";
			#dprint($sql);#
			$my_result = mysqli_query($my_sql_local_conn, $sql);
			if ($my_result)
				$rc = true;
			else
				dlog("*=* SQL Create failed!<br>$sql<br>" . mysqli_error($my_sql_local_conn));
		}

		$temp = array();
		foreach ($select_fields as $sf)
		{
			if (in_array($sf, $fields_number))
				$temp[] = $sf;
			elseif (in_array($sf, $fields_date))
				$temp[] = $sf;
			elseif (in_array($sf, $fields_encrypted))
				$temp[] = (in_array($sf, $fields_nullable) ? ("CASE WHEN $sf IS NULL THEN '_NULL_' ELSE " . sql_decrypt($sf) . " END") : sql_decrypt($sf)) .
							" AS $sf";
			else
				$temp[] = (in_array($sf, $fields_nullable) ? ("CASE WHEN $sf IS NULL THEN '_NULL_' ELSE $sf END AS $sf") : $sf);
		}
		$select_fields = implode(', ', $temp); # for selecting from MS SQL table
	}
	else
		$select_fields = '';
	return $rc;
} # create_mysql_table()

function port_table($table, $r_count)
{
	# Return 0 for success or other as error code

//	global $cronjob; # settings.php, set at top of this script too
	global $fields_date; # array, from create_mysql_table()
	global $fields_encrypted; # array, from create_mysql_table()
	global $fields_number; # array, from create_mysql_table()
	global $fields_nullable; # array, from create_mysql_table()
	global $id_name; # from create_mysql_table()
	global $id_names; # hard-coded at top of script
	#global $incremental_run;
	global $my_sql_local_conn;
	global $select_fields; # string, not array, from create_mysql_table()
	global $site_local;
	global $sql_key;
	global $time_start;
	global $time_threshold;
	global $total_rows_done;
	global $total_rows_to_do;

	if ($site_local)
	    $debug_max = 0;
	else
	    $debug_max = 0;
	//if (in_array($table, array('CLIENT2', 'CLIENT_CONTACT', 'CLIENT_CONTACT_PHONE', 'CLIENT_NOTE', 'JOB_GROUP')))
	//    $debug_max = 0;
	//elseif ($site_local)
	//    $debug_max = 1000;#
	//else
	//    $debug_max = 10000;#
	#if (($debug_max == 0) && in_array($table, array('AUDIT', 'CLIENT_LETTER_LINK')))#
	#	$debug_max = 1000;
	#$debug_print = false;#

	$dlog_messages = array();
	$mysql_count = 0; # for $incremental_run
	$mysql_id_max = 0; # for $incremental_run
	#if ($incremental_run)
	#{
		$dlog_messages[] = "Incremental run on table $table...";
		$my_result = mysqli_query($my_sql_local_conn, "SELECT COUNT(*) FROM $table");
		if ($my_result !== false)
		{
			$stuff = mysqli_fetch_array($my_result, MYSQLI_NUM);
			if (is_array($stuff))
			{
				$mysql_count = intval($stuff[0]);
				if (0 < $mysql_count)
				{
					$sql = "SELECT MAX({$id_names[$table]}) FROM $table";
					#dlog("MySQL: $sql");
					$my_result = mysqli_query($my_sql_local_conn, $sql);
					$stuff = mysqli_fetch_array($my_result, MYSQLI_NUM);
					$mysql_id_max = intval($stuff[0]);
				}
			}
		}
		$my_result = null;
		//$dlog_messages[] = "...\$mysql_count=" . number_with_commas($mysql_count) . ", \$mysql_id_max=" . number_with_commas($mysql_id_max) . "...";
	#}
	$dlog_messages[] = "Creating MySQL table $table...";
	$created = create_mysql_table($table, $mysql_count);

	$diff_count = $r_count - $mysql_count;
	$abort = 0; # abort port if $abort == -1 or -2
	if ($created && (0 < $diff_count))
	{
		foreach ($dlog_messages as $dl)
			dlog($dl);
		dlog("Porting MS SQL table $table to MySQL (MS SQL has " . number_with_commas($r_count) . " rows)...");
		#if ($incremental_run)
			dlog("...MySQL has " . number_with_commas($mysql_count) . " rows (max ID " . number_with_commas($mysql_id_max) . "); " . number_with_commas($diff_count) . " still to go...");
		if (0 < count($fields_encrypted))
			sql_encryption_preparation($table);

		$table_is_audit = (($table == 'AUDIT') ? true : false);
		$table_is_collect_dbf_2015 = (($table == 'COLLECT_DBF_2015') ? true : false);
		$table_is_feedback = (($table == 'FEEDBACK') ? true : false);
		$table_is_inv_billing = (($table == 'INV_BILLING') ? true : false);
		$table_is_local_job = (($site_local && ($table == 'JOB')) ? true : false);
		$table_is_job_z = (($table == 'JOB_Z') ? true : false);

		if (10000 <= $diff_count)
			$log_points = array(intval($diff_count / 10), intval(2 * $diff_count / 10), intval(3 * $diff_count / 10), intval(4 * $diff_count / 10),
								intval(5 * $diff_count / 10), intval(6 * $diff_count / 10), intval(7 * $diff_count / 10),
								intval(8 * $diff_count / 10), intval(9 * $diff_count / 10));
		else
			$log_points = array(intval($diff_count / 2));
		$temp = array();
		foreach ($log_points as $lp)
			$temp[] = number_with_commas($lp);
		dlog("log_points=" . print_r($temp,1));
		$temp = null;

		$step_count = 0;
		$insert_count = 0;
		$sql = "SELECT MAX($id_name) FROM $table";
		#dlog("MS SQL: $sql");
		$id_max = intval(sql_select_single($sql));
		if (0 < $id_max)
		{
			#if ($table_is_audit && $cronjob)
			#	$step_size = 100;
			#else
			$step_size = 1000;
			$step_count = 0;
			if (0 < $mysql_id_max) #($incremental_run && )
				$start_id = $mysql_id_max + 1;
			else
				$start_id = 0;
			dlog("start_id for $table is " . number_with_commas($start_id));
			$loop_entered = false;
			$time_now = 0;
			$time_spent = 0;
			for ($id = $start_id; $id <= $id_max; $id += $step_size)
			{
				$loop_entered = true;
				$step_count++;
				#$loop_debug = (($table_is_audit && (145712 <= $insert_count) && ($insert_count <= 145715)) ? true : false);
				$sql = "SELECT $select_fields FROM $table WHERE $id <= $id_name AND $id_name < " . ($id + $step_size) . " ORDER BY $id_name";
				#if ($loop_debug)
				#	dlog("AUDIT: \$insert_count=$insert_count, SQL=$sql");
				#if ($debug_print) dprint($sql);#
				sql_execute($sql);
				$recs = array();
				while (($newArray = sql_fetch_assoc()) != false)
					$recs[] = $newArray;
				sql_free_result();
				if ($recs)
				{
					$txt = "Step #{$step_count}: Found " . count($recs) . " record(s)";
					if (count($recs) < 20)
						$txt .= ": " . print_r($recs,1);
					#if ($debug_print) dprint($txt);
					#if ($loop_debug)
					#	dlog("AUDIT: \$insert_count=$insert_count, $txt");
				}
				#else
				#	if ($debug_print) dprint("Step #{$step_count}: Found no records");

				foreach ($recs as $fields_selected)
				{
					$insert_fields = array();
					$insert_values = array();

					#if ($debug_print) dprint("Found: " . print_r($fields_selected,1));#
					foreach ($fields_selected as $field => $value)
					{
						$insert_fields[] = $field;
						if (in_array($field, $fields_number))
						{
							#if ($debug_print && ($debug_max==1)) dprint("Is a number");
							$is_null = ((in_array($field, $fields_nullable) && (!is_numeric($value))) ? true : false);
							$insert_values[] = ($is_null ? 'NULL' : "{$value}");
						}
						elseif (in_array($field, $fields_date))
						{
							#if ($debug_print && ($debug_max==1)) dprint("Is a date");
							$is_null = ((in_array($field, $fields_nullable) && ($value == '')) ? true : false);
							$insert_values[] = ($is_null ? 'NULL' : "'{$value}'");
						}
						elseif (in_array($field, $fields_encrypted))
						{
							#if ($debug_print && ($debug_max==1)) dprint("Is encrypted");
							$is_null = ((in_array($field, $fields_nullable) && ($value == '_NULL_')) ? true : false);
							if ($is_null)
								$insert_values[] = 'NULL';
							else
							{
								$value_esc = mysqli_real_escape_string($my_sql_local_conn, stripslashes($value));
								$insert_values[] = "AES_ENCRYPT('{$value_esc}','$sql_key')";
							}
						}
						else
						{
							$is_null = ((in_array($field, $fields_nullable) && ($value == '_NULL_')) ? true : false);
							if ($is_null)
							{
								#if ($debug_print && ($debug_max==1)) dprint("Is null");
								$insert_values[] = 'NULL';
							}
							else
							{
								#if ($debug_print && ($debug_max==1)) dprint("Is text");
								$value_esc = mysqli_real_escape_string($my_sql_local_conn, stripslashes($value));
								$insert_values[] = "'{$value_esc}'";
							}
						}
					} # foreach ($fields_selected)
					if ($table_is_audit)
					{
						$insert_fields[] = "FROM_MYSQL";
						$insert_values[] = "0";
					}
					elseif ($table_is_collect_dbf_2015)
					{
						if ($insert_values[0] == "48")
						{
							$insert_values[6] = preg_replace('/[\x00-\x1F\x7F-\xFF]/', '', $insert_values[6]); # HOMEADD1
						}
					}
					elseif ($table_is_feedback)
					{
						$insert_values[4] = preg_replace('/[\x00-\x1F\x7F-\xFF]/', '', $insert_values[4]); # F_TEXT
						$insert_values[5] = preg_replace('/[\x00-\x1F\x7F-\xFF]/', '', $insert_values[5]); # F_RESPONSE
					}
					elseif ($table_is_inv_billing)
					{
						$insert_values[6] = preg_replace('/[\x00-\x1F\x7F-\xFF]/', '', $insert_values[6]); # BL_DESCR
					}
					elseif ($table_is_local_job)
					{
						$insert_values[57] = preg_replace('/[\x00-\x1F\x7F-\xFF]/', '', $insert_values[57]); # JC_ADJ_TEXT
						$insert_values[67] = preg_replace('/[\x00-\x1F\x7F-\xFF]/', '', $insert_values[67]); # JC_PDC_TXT_CU3
						$insert_values[70] = preg_replace('/[\x00-\x1F\x7F-\xFF]/', '', $insert_values[70]); # JC_AGR_TXT_CU5
						$insert_values[92] = preg_replace('/[\x00-\x1F\x7F-\xFF]/', '', $insert_values[92]); # JC_TRANS_ID
						$insert_values[93] = preg_replace('/[\x00-\x1F\x7F-\xFF]/', '', $insert_values[93]); # JC_IMP_NOTES_VMAX
						$insert_values[94] = preg_replace('/[\x00-\x1F\x7F-\xFF]/', '', $insert_values[94]); # J_FRONT_DETAILS
						$insert_values[99] = preg_replace('/[\x00-\x1F\x7F-\xFF]/', '', $insert_values[99]); # JC_TRANS_CNUM
					}
					elseif ($table_is_job_z)
					{
						for ($ii = 44; $ii <= 54; $ii++) # Z_T_REP1 to Z_T_REP11
							$insert_values[$ii] = preg_replace('/[\x00-\x1F\x7F-\xFF]/', '', $insert_values[$ii]); # Z_T_REP1 to Z_T_REP11
					}
					#if ($debug_print && ($debug_max==1)) dprint("insert_values=" . print_r($insert_values,1));
					$sql = "INSERT INTO $table (" . implode(',', $insert_fields) . ") VALUES (" . implode(',', $insert_values) . ")";
					$insert_fields = null;
					$insert_values = null;
					#if ($debug_print) dprint($sql);
					if (mysqli_query($my_sql_local_conn, $sql))
						$insert_count++;
					else
					{
						dlog("*=* SQL Insert failed! **<br>$sql<br>" . mysqli_error($my_sql_local_conn));
						$abort++;
						//if (  (100 < $abort)  ||  ((10 < $abort) && ($insert_count < (2 * $abort)))  )
						if (0 < $abort)
						{
							dlog("Stopping after too many errors: $abort errors (and $insert_count successful inserts)");
							$abort = -1;
						}
					}
					if (in_array($insert_count, $log_points))
						dlog("...inserted " . number_with_commas($insert_count) . " so far...");
					if ((0 < $debug_max) && ($debug_max <= $insert_count))
					{
						dlog("Stopping after reaching debug_max of $debug_max");
						$abort = -1;
					}
					if ($abort == -1)
						break;
				} # foreach ($recs)
				$recs = null;

				$time_now = time();
				$time_spent = $time_now - $time_start;
				if ($time_threshold < $time_spent)
				{
					dlog("Stopping after reaching max duration of " . number_with_commas($time_threshold));
					$abort = -2;
				}

				if ($abort < 0)
					break;
			} # for ($id)
			if ($loop_entered)
				dlog("...time_now=$time_now, time_spent=" . number_with_commas($time_spent));
		}
		$total_rows_done += ($mysql_count + $insert_count);
		$percent = round(100.0 * floatval($total_rows_done) / floatval($total_rows_to_do), 2);

		$my_result = mysqli_query($my_sql_local_conn, "SELECT COUNT(*) FROM $table");
		$stuff = mysqli_fetch_array($my_result, MYSQLI_NUM);
		$mysql_count_2 = intval($stuff[0]);
		$diff_2 = $mysql_count_2 - $mysql_count;

		dlog("Inserted " . number_with_commas($insert_count) . " records into $table (in $step_count steps; new row count up by " . number_with_commas($diff_2) . " records) -- " .
				"{$percent}% done (" . number_with_commas($total_rows_done) . " out of " . number_with_commas($total_rows_to_do) . ")");
	} # if ($created && (0 < $diff_count))
	elseif ($created)
		dlog("Table $table doesn't need porting");
	else
		dlog("Table $table failed to be created *****************************************");
	return $abort;

} # port_table()

?>
