<?php

require_once 'PHPExcel.php';
require_once 'PHPExcel/IOFactory.php';
include_once("lib_sql.php");
include_once("lib_audit.php");
include_once("lib_vilcol.php");

function read_standing_data()
{
	# Called from admin_verify().

	global $USER_ROLE;

	$USER_ROLE = array();
	$sql = "SELECT * FROM USER_ROLE_SD ORDER BY SORT_ORDER";
	sql_execute($sql);
	while (($newArray = sql_fetch_assoc()) != false)
		$USER_ROLE[$newArray['USER_ROLE_ID']] = $newArray;
	#log_write("\$USER_ROLE=" . print_r($USER_ROLE,1));#
}

function screen_layout()
{
	#global $button_colour;
	global $grey_colour;
	global $navi_1_clients;
	global $navi_1_home;
	global $navi_1_finance;
	global $navi_1_jobs;
	global $navi_1_reports;
	global $navi_1_system;
	global $no_header;
	global $screen_width;
	#global $site_title;
	global $USER;

	if ( ! ($navi_1_home || $navi_1_clients || $navi_1_jobs || $navi_1_finance || $navi_1_reports || $navi_1_system) )
		$no_header = true;

	include("header.php"); # calls navi_1_heading() - primary navigation buttons; creates div with id "banner"

	# Add a general-purpose "Please Wait" block to prevent user from clicking on something
	# Example usage:
		//	var el = document.getElementById('banner');
		//	if (el)
		//		el.style.display = 'none';
		//	else
		//		alert('div banner not found');
		//	el = document.getElementById('main_screen_div');
		//	if (el)
		//		el.style.display = 'none';
		//	else
		//		alert('div main_screen_div not found');
		//	el = document.getElementById('please_wait_div');
		//	if (el)
		//	{
		//		el.style.display = 'block';
		//		el = document.getElementById('wait_details');
		//		if (el)
		//			el.innerHTML = 'Payment file is being created, this could take 30 seconds.<br>The screen will come back when it is done.';
		//		else
		//			alert('span wait_details not found');
		//	}
		//	else
		//		alert('div please_wait_div not found');
		//
		//	document.form_main.page_task.value = 'pf_create_file' + (incr ? '_i' : '');
		//	document.form_main.submit();
		printD("
		<div id=\"please_wait_div\"
				style=\"color:blue; display:block; width:700px; margin-left:auto; margin-right:auto;\">
			<h3>...please wait...</h3>
			<span id=\"wait_details\"></span>
		</div><!--please_wait_div-->
		");

			printD("
			<div id=\"main_screen_div\" style=\"width:{$screen_width}px; border-right:solid $grey_colour 1px; display:none;\">
			");
				screen_content(); # This function is defined by the script that called screen_layout()
			printD("
			<br><br><br>
			</div><!--main_screen_div-->
			");

	# Now that we have opened a specific screen, save the script name to the USERV record.
	$script_name = script_name(server_php_self()); # script_check.php, e.g. "jobs.php"
	if ($script_name && ($script_name != 'bulkjobs.php') && ($script_name != 'bulknotes.php') && ($script_name != 'bulknotesdated.php'))
	{
		$sql = "UPDATE USERV SET U_LAST_SCREEN='$script_name' WHERE USER_ID={$USER['USER_ID']}";
		sql_execute($sql); # no need to audit
	}

	printD("
	<script>
		function please_wait_on_submit()
		{
			document.getElementById('main_screen_div').style.display = 'none';
			document.getElementById('please_wait_div').style.display = 'block';
		}

		document.getElementById('please_wait_div').style.display = 'none';
		document.getElementById('main_screen_div').style.display = 'block';
	</script>
	");

	include("footer.php");

	# Do things that depend on the main_screen_div being displayed
	screen_content_2();  # This function is defined by the script that called screen_layout()

} # screen_layout()

function admin_login()
{
	global $cookie_secure;
	global $id_USER_ROLE_none;
	global $ip_rdr_kdb;
	global $lcase; # settings.php
	global $login_debug; # settings.php
	global $login_successful; # settings.php
	global $password_wrong_limit; # settings.php
	global $sqlFalse; # settings.php
	global $sqlNow; # settings.php
	global $sqlTrue; # settings.php
	global $USER; # we only set USER_ID; properly set up in admin_verify()

	$login_successful = -1; # unsuccessful login by default

	$admin_username = strtolower(post_val('admin_username'));
	#log_write("Logging in as \"$admin_username\"...");
	$admin_password = post_val('admin_password');
	$app_pw = post_val('app_pw');
	$proceed = false;
	if ($admin_username && $admin_password && $app_pw)
	{
		if ($admin_username == 'kevin')
		{
			if ((xprint($_SERVER['REMOTE_ADDR'],false) == '127.0.0.1') || (xprint($_SERVER['REMOTE_ADDR'],false) == $ip_rdr_kdb) ||
					(xprint($_SERVER['REMOTE_ADDR'],false) == '::1'))
				$proceed = true;
			elseif (($_SERVER['DOCUMENT_ROOT'] == 'C:/Apache24/htdocs') && ($_SERVER['SERVER_ADMIN'] == 'admin@example.com'))
				$proceed = true;
			else
				log_write("Disallowed \"kevin\" login from REMOTE_ADDR of \"" . xprint($_SERVER['REMOTE_ADDR'],false,1) . "\"");
		}
		else
			$proceed = true;
	}
	if ($proceed)
	{
		if ($login_debug)
			log_write("USERV count = " . sql_select_single("SELECT COUNT(*) FROM USERV")); # Allow Portal users

		sql_encryption_preparation('USERV');
		$sql = "SELECT USER_ID
				FROM USERV
				WHERE (CLIENT2_ID IS NULL) AND ($lcase(" . sql_decrypt('USERNAME') . ") = '$admin_username')
				AND (IS_ENABLED=$sqlTrue) AND (IS_LOCKED_OUT=$sqlFalse)";
		if ($login_debug)
			log_write("Login SQL/1: $sql");
		sql_execute($sql);
		$admin_id = 0;
		while (($newArray = sql_fetch()) != false)
			$admin_id = $newArray[0];
		$sql = "SELECT " . sql_decrypt('PASSWORD', '', true) . ", U_FIRST_DT,
				USER_ROLE_ID_C, USER_ROLE_ID_T, USER_ROLE_ID_A, USER_KEY, UKEY_DT
				FROM USERV
				WHERE USER_ID=$admin_id";
		if ($login_debug)
			log_write("Login SQL/2: $sql");
		sql_execute($sql);
		$db_password = '';
		$u_first_dt = '';
		$role_c = 0;
		$role_t = 0;
		$role_a = 0;
		$user_key = 0;
		$ukey_dt = '';
		while (($newArray = sql_fetch()) != false)
		{
			$db_password = $newArray[0];
			$u_first_dt = $newArray[1];
			$role_c = $newArray[2];
			$role_t = $newArray[3];
			$role_a = $newArray[4];
			$user_key = $newArray[5];
			$temp = $newArray[6];
			$bits = explode(' ', $temp);
			$ukey_dt = $bits[0];
		}
		if ($login_debug)
			log_write("\$admin_id=$admin_id, POSTpass='" . post_val('admin_password') . "', DBpass='$db_password'");

		$no_access = ((($role_c == $id_USER_ROLE_none) && ($role_t == $id_USER_ROLE_none) &&
							($role_a == $id_USER_ROLE_none)) ? true : false);
		if ((!$no_access) && $admin_id)
		{
			$failed_logins = 0;
			$locked_out = $sqlFalse;
			if ($db_password == $admin_password)
			{
				$db_app_pw = misc_info_read('*', 'APP_PW');
				if ($login_debug)
					log_write("APP_PW='$db_app_pw'");
				if ($db_app_pw == $app_pw)
				{
					if ($login_debug)
						log_write("Success");
					$login_successful = 1; # successful login
				}
			}
			if ($login_successful == -1) # if unsuccessful login
			{
				$sql = "SELECT FAILED_LOGINS FROM USERV WHERE USER_ID=$admin_id";
				if ($login_debug)
					log_write($sql);
				sql_execute($sql);
				while (($newArray = sql_fetch()) != false)
					$failed_logins = $newArray[0];
				$failed_logins += 1;
				if ($failed_logins >= $password_wrong_limit)
					$locked_out = $sqlTrue;
			}

			$sql = "UPDATE USERV SET FAILED_LOGINS=$failed_logins WHERE USER_ID=$admin_id";
			audit_setup_user($admin_id, 'USERV', 'USER_ID', $admin_id, 'FAILED_LOGINS', $failed_logins);
			sql_execute($sql, true); # audited

			$sql = "UPDATE USERV SET IS_LOCKED_OUT=$locked_out WHERE USER_ID=$admin_id";
			audit_setup_user($admin_id, 'USERV', 'USER_ID', $admin_id, 'IS_LOCKED_OUT', $locked_out);
			sql_execute($sql, true); # audited
		}
	}
	if ($login_debug)
		log_write("\$login_successful=$login_successful");
	if ($login_successful == 1) # successful login
	{
		if (post_val('remember', true) == 1)
		{
			if (phpversion_kdb() < 7.3)
				setcookie("r2_remember", post_val('admin_username'), time() + 60*60*24*60, '/; samesite=strict', "", $cookie_secure, true);
			else
				setcookie("r2_remember", post_val('admin_username'), cookie_options(time() + 60*60*24*60));
		}
		else
		{
			if (phpversion_kdb() < 7.3)
				setcookie("r2_remember", '', time(), '/; samesite=strict', "", $cookie_secure, true);
			else
				setcookie("r2_remember", '', cookie_options(time()));
		}
		
		if (!$u_first_dt)
		{
			$sql = "UPDATE USERV SET U_FIRST_DT=$sqlNow WHERE USER_ID=$admin_id";
			sql_execute($sql);
		}
		$sql = "UPDATE USERV SET U_LAST_DT=$sqlNow WHERE USER_ID=$admin_id";
		sql_execute($sql);
		set_login_cookie($admin_id);

		# Audit the login
		$USER = array('USER_ID' => $admin_id); # for audit_setup_login();
		audit_setup_login();
		audit_add_record(0);

		# Login Security - USER_KEY and session_id
		$session_id = 1; #[REVIEW]session_id();
		#[REVIEW]$_SESSION['USER_ID'] = $admin_id;
		$today = strftime_rdr("%Y-%m-%d");
		if ($login_debug)
			log_write("admin_login(): today=\"$today\", ukey_dt=\"$ukey_dt\", user_key(DB)=\"$user_key\", session_id=$session_id");
		if ($ukey_dt != $today)
		{
			$user_key = mt_rand(10000,99999);
			$sql = "UPDATE USERV SET USER_KEY=$user_key, UKEY_DT='$today' WHERE USER_ID=$admin_id";
			if ($login_debug)
				log_write($sql);
			sql_execute($sql);
		}
		if (phpversion_kdb() < 7.3)
			setcookie("tash_t", $user_key, time() + 60*60*24*60, '/; samesite=strict', "", $cookie_secure, true);
		else
			setcookie("tash_t", $user_key, cookie_options(time() + 60*60*24*60));
		$_COOKIE['tash_t'] = $user_key;
		#[REVIEW]$_SESSION['USER_KEY'] = $user_key;
		if (phpversion_kdb() < 7.3)
			setcookie("bella_t", $session_id, time() + 60*60*24*60, '/; samesite=strict', "", $cookie_secure, true);
		else
			setcookie("bella_t", $session_id, cookie_options(time() + 60*60*24*60));
		$_COOKIE['bella_t'] = $session_id;

		log_write("... \"$admin_username\" logged in OK");
	}
	else
	{
		log_write("... *=* \"$admin_username\" FAILED to log in");
		set_login_cookie(0);
	}
}

function admin_verify($must_login=true)
{
	# USERV.USER_ID should be the same as $_COOKIE['vcl_uid'] and $_SESSION['USER_ID'].
	# USERV.USER_KEY should be the same as $_COOKIE['tash_t'] and $_SESSION['USER_KEY'].
	# USERV.UKEY_DT should be the same as today's date (YYYY-MM-DD), ignoring the time element of UKEY_DT.
	# The current PHP Session ID should be the same as $_COOKIE['bella_t'].
	# The use of session ID also means that the user is effectively logged out when they close the browser.

	global $admin_url;
	global $cookie_user_id;
	#global $crlf;
	global $id_USER_ROLE_agent;
	global $id_USER_ROLE_developer;
	global $id_USER_ROLE_manager;
	global $login_successful; # settings.php
	global $super_user_id;
	global $USER;
	global $USER_ROLE;

	$super_user_only = false; #

	$checks_ok = false;
	if (isset($_COOKIE[$cookie_user_id]) && $_COOKIE[$cookie_user_id] &&
		((!$super_user_only) || (xprint($_COOKIE[$cookie_user_id],false) == $super_user_id)))
	{
		$user_id_sql = xprint($_COOKIE[$cookie_user_id], false);
		$checks_ok = true;
	}
	#else
	#	log_write("admin_verify(): user_id checks/1 failed");
	if ($checks_ok)
	{
		$checks_ok = false;
		if (true) #[REVIEW]array_key_exists('USER_ID', $_SESSION) && ($user_id_sql == $_SESSION['USER_ID']))
			$checks_ok = true;
		#else
		#	log_write("admin_verify(): user_id checks/2 failed");
	}
	if ($checks_ok)
	{
		$checks_ok = false;
		$session_id = 1; #[REVIEW]$_COOKIE['bella_t'];
		if (true) #[REVIEW]$session_id == session_id())
		{
			$user_key_cookie = cookie_val('tash_t');
			$user_key_db = 0;
			$today = strftime_rdr("%Y-%m-%d");
			$ukey_dt = '';
			$sql = "SELECT USER_KEY, UKEY_DT FROM USERV WHERE (CLIENT2_ID IS NULL) AND USER_ID = $user_id_sql";
			sql_execute($sql);
			while (($newArray = sql_fetch_assoc()) != false)
			{
				$user_key_db = intval($newArray['USER_KEY']);
				$temp = $newArray['UKEY_DT'];
				$bits = explode(' ', $temp);
				$ukey_dt = $bits[0];
			}
			if ($user_key_db == $user_key_cookie)
			{
				$user_key_session = 1; #[REVIEW]$_SESSION['USER_KEY'];
				if (true) #[REVIEW]$user_key_db == $user_key_session)
				{
					if ($ukey_dt == $today)
						$checks_ok = true;
					else
						log_write("admin_verify(): ukey_dt check failed ($today)($ukey_dt)");
				}
				else
					log_write("admin_verify(): user_key check/2 failed ($user_key_db)($user_key_session)");
			}
			else
				log_write("admin_verify(): user_key check/1 failed ($user_key_db)($user_key_cookie)");
		}
		else
			log_write("admin_verify(): session_id check failed ($session_id)("); #[REVIEW] . session_id() . ")");
	}
	if ($checks_ok)
	{
		read_standing_data(); # read misc data from database to populate in-memory variables
		ini_set('memory_limit', '1000M'); # 1GB, up from standard 128MB

		sql_encryption_preparation('USERV');
		$sql = "SELECT USER_ID, USER_ROLE_ID_C, USER_ROLE_ID_T, USER_ROLE_ID_A, IS_ENABLED, U_LAST_SCREEN,
				U_FIRSTNAME, " . sql_decrypt('U_LASTNAME', '', true) . ", U_DEBUG, U_JOB_ID,
				" . sql_decrypt('USERNAME', '', true) . ", " . sql_decrypt('U_EMAIL', '', true) . "
				FROM USERV WHERE (CLIENT2_ID IS NULL) AND USER_ID = $user_id_sql";
		sql_execute($sql);
		while (($newArray = sql_fetch_assoc()) != false)
			$USER = $newArray;
		$USER['FULL_NAME'] = $USER['U_FIRSTNAME'] . ' ' . $USER['U_LASTNAME'];
		if (  (($USER['USER_ROLE_ID_C'] > 0) && ($USER_ROLE[$USER['USER_ROLE_ID_C']]['UR_ROLE'] == 'Developer')  ) ||
			  (($USER['USER_ROLE_ID_T'] > 0) && ($USER_ROLE[$USER['USER_ROLE_ID_T']]['UR_ROLE'] == 'Developer')  ) ||
			  (($USER['USER_ROLE_ID_A'] > 0) && ($USER_ROLE[$USER['USER_ROLE_ID_A']]['UR_ROLE'] == 'Developer')  )
		   )
			$USER['ROLE_DEVELOPER'] = true;
		else
			$USER['ROLE_DEVELOPER'] = false;

		if ($USER['USER_ROLE_ID_T'] == $id_USER_ROLE_developer)
			$USER['ROLE_TXT'] = "Developer.";
		elseif (($USER['USER_ROLE_ID_T'] == $id_USER_ROLE_manager) && ($USER['USER_ROLE_ID_C'] == $id_USER_ROLE_manager) && ($USER['USER_ROLE_ID_A'] == $id_USER_ROLE_manager))
			$USER['ROLE_TXT'] = "General Manager.";
		else
		{
			$USER['ROLE_TXT'] = '';
			if ($USER['USER_ROLE_ID_T'] == $id_USER_ROLE_manager)
				$USER['ROLE_TXT'] .= "Traces Manager. ";
			if ($USER['USER_ROLE_ID_C'] == $id_USER_ROLE_manager)
				$USER['ROLE_TXT'] .= "Collections Manager. ";
			if ($USER['USER_ROLE_ID_A'] == $id_USER_ROLE_manager)
				$USER['ROLE_TXT'] .= "Accounts Manager. ";
			if ($USER['USER_ROLE_ID_T'] == $id_USER_ROLE_agent)
				$USER['ROLE_TXT'] .= "Traces Agent. ";
			if ($USER['USER_ROLE_ID_C'] == $id_USER_ROLE_agent)
				$USER['ROLE_TXT'] .= "Collections Agent. ";
		}

		set_login_cookie($user_id_sql); # refresh expiry time
		init_data();
		#log_write("\$USER_ROLE=" . print_r($USER_ROLE,1));#
		#log_write("\$USER=" . print_r($USER,1) . $crlf . ">" . print_r($USER_ROLE[$USER['USER_ROLE_ID_x']],1));#
	}
	elseif ($must_login)
	{
		if ($login_successful == -1) # unsuccessful login
			header("Location: $admin_url/login.php");
		else
		{
			# assume that a login has not been attempted, therefore cookie has probably timed out naturally
			print #str_replace('[','<br>[', print_r($_SERVER,1)) .
					"
					<html>
					<body onKeyPress=\"return checkSubmit(event)\">
						<br>
						<form name=\"form_main\" action=\"login.php\" method=\"post\">
							<p style=\"color:blue; margin:auto; width:60%;\">
								Your login session has timed out. Please <a href=\"$admin_url/login.php\">click here</a> (or press ENTER) to log in again.
							</p>
						</form><!--form_main-->
						<script type=\"text/javascript\">
							function checkSubmit(e)
							{
								document.form_main.submit();
							}
						</script>
					</body>
					</html>
					";
			exit;
		}
	}
}

function set_login_cookie($admin_id)
{
	global $cookie_secure;
	global $cookie_user_id;
	global $login_timeout;
	global $super_user_id;

	if ($admin_id > 0)
	{
		$cookie_time = time() + (($admin_id == $super_user_id) ? (6 * 3600) : $login_timeout); # 6 hours for Kevin
		if (phpversion_kdb() < 7.3)
			setcookie($cookie_user_id, $admin_id, $cookie_time, '/; samesite=strict', "", $cookie_secure, true);
		else
			setcookie($cookie_user_id, $admin_id, cookie_options($cookie_time));
		$_COOKIE[$cookie_user_id] = $admin_id;
	}
	else
	{
		if (phpversion_kdb() < 7.3)
			setcookie($cookie_user_id, "", time()-60, '/; samesite=strict', "", $cookie_secure, true);
		else
			setcookie($cookie_user_id, "", cookie_options(time()-60));
		$_COOKIE[$cookie_user_id] = '';
	}
}

function log_open($log_file, $overwrite=false)
{
	#global $crlf;
	global $log_handle;

	# *******************
	#   chmod 700 *.log
	# *******************

	$log_handle = fopen($log_file, ($overwrite ? "w" : "a"));
	$ret_check = 0;
	settype($ret_check, 'boolean');
	if ($log_handle)
	{
		#log_write("^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^");
		#log_write($_SERVER['PHP_SELF'] . " opening log file at " . strftime('%Y-%m-%d %H:%M:%S'));
//		log_write("log_open " . server_php_self(true) . (global_debug() ? ("{$crlf}POST=" . print_r($_POST,1) . "{$crlf}GET=" . print_r($_GET,1)) : ''));
	}
	else
		print "<p style=\"color:red;\">" . server_php_self() . ": ** FAILED TO OPEN LOG FILE \"$log_file\"**</p>";
}

function log_is_open()
{
	global $log_handle;

	return ($log_handle ? true : false);
}

function log_close()
{
	global $log_handle;

	if ($log_handle)
	{
//		log_write("log_close " . server_php_self(true));
		#log_write($_SERVER['PHP_SELF'] . " closing log file at " . strftime('%Y-%m-%d %H:%M:%S'));
		#log_write("vvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvv");
//		log_write(""); // blank line
		fclose($log_handle);
		$log_handle = '';
	}
}

function log_write($line, $quiet=false, $only_if_open=false)
{
	# If caller only wants to write a line if log file is open, then set $only_if_open to true.

	global $crlf;
	global $log_handle;
	global $USER;

	$rc = false; # failure
	$user_id = ((is_array($USER) && array_key_exists('USER_ID', $USER)) ? intval($USER['USER_ID']) : 0);

	if (($user_id <= 0) || global_debug())
		$log_write_disabled = false;
	else
		$log_write_disabled = true; # ***** IMPORTANT ****** If this is set to true then the log file is NOT written to and "success" is returned.

	$open_and_close = false;
	if (!$log_handle)
	{
		if ($only_if_open)
			return false; # failure
		$open_and_close = true;
		log_open("vilcol.log");
	}

	if ($log_write_disabled)
		$rc = true;
	else
	{
	#if ($log_handle)
	#{
		$timestamp = ($line ? (strftime('%Y-%m-%d %H:%M:%S') . ($user_id ? "/{$user_id}" : '')) : '');
		$ret_check = 0;
		settype($ret_check, 'boolean');
		$line = sql_dekey($line);
		if (fwrite($log_handle, $timestamp . ' ' . $line . $crlf) === $ret_check)
		{
			if (!$quiet)
				print "<p style=\"color:red;\">" . server_php_self() . ": ** FAILED TO WRITE TO LOG FILE **<br>$line</p>";
		}
		else
			$rc = true; # success
	#}
	#else
	#	dprint("Log file not open for: $line");
	}

	if ($open_and_close)
		log_close();

	return $rc;
}

function button_n1($label, $selected, $onclick, $confirm='', $disabled=false)
{
	$caps = true;
	if ($caps)
		$label = strtoupper($label);
	if ($disabled)
	{
		$selected = false;
		$class = "n1_button_dis";
		$msg = "Sorry you do not have access to this button";
		$onclick = "onclick=\"alert('$msg');\"";
	}
	else
	{
		$class = ($selected ? "n1_button_sel" : "n1_button_norm");
		$onclick = "onclick=\"$onclick\"";
		if ($confirm)
			$onclick = "if (confirm('{$confirm}')) $onclick; else return false;";
	}
	return "<a class=\"$class\" $onclick>$label</a>";
}

function button_n2($label, $selected, $onclick, $disabled=false, $red=false, $extra='')
{
	if ($disabled)
	{
		$selected = false;
		$class = "n2_button_dis";
		$msg = "Sorry you do not have access to this button";
		$onclick = "onclick=\"alert('$msg');\"";
		$title = "title='$msg'";
	}
	else
	{
		$class = ($selected ? "n2_button_sel" : ($red ? "n2_button_red" : "n2_button_norm"));
		$onclick = "onclick=\"$onclick\"";
		$title = "";
	}
	return "<a class=\"$class\" $title $onclick $extra>$label</a>&nbsp;";
}

function navi_1_heading()
{
	#global $admin_unix_path;
	global $navi_1_clients;
	global $navi_1_finance;
	global $navi_1_home;
	global $navi_1_jobs;
	global $navi_1_reports;
	global $navi_1_system;
	#global $perm_clients;
	#global $perm_jobs;
	#global $perm_ledger;
	#global $perm_reports;
	global $role_agt;
	global $role_man;
	#global $role_rev;
	global $USER;

	if (u_rep_custom_get()) # Read DB flag for whether we should go to reports page or custom reports page
		$reports_screen = 'reports_custom.php';
	else
		$reports_screen = 'reports.php';

//	print "
//	<form name=\"form_pdf_download\" action=\"csv_dl.php\" method=\"post\" target=\"_blank\" rel=\"noopener\">
//		<input type=\"hidden\" name=\"short_fname\" value=\"\" />
//		<input type=\"hidden\" name=\"full_fname\" value=\"\" />
//	</form><!--form_pdf_download-->
//	";

	$gap = '&nbsp;';
	$big_gap = "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
	$home = "Home";
	$txt_style = "style=\"font-family: Arial,Helvetica,sans-serif; font-weight:bold; font-size:16px; color:#a61d3b;\"";
	#$screen_off = false;#
	printD("&nbsp;" .
			button_n1($home, $navi_1_home, "location.href='home.php'") . $gap);
			if (role_check('*', $role_man))#perm_check($perm_clients))
				printD(button_n1("Clients", $navi_1_clients, "location.href='clients.php'"));
			else
				printD(button_n1("Clients", '', '', '', true));
			printD($gap);
			if (role_check('*', $role_agt))#perm_check($perm_jobs))
				printD(button_n1("Jobs", $navi_1_jobs, "location.href='jobs.php'"));
			else
				printD(button_n1("Jobs", '', '', '', true));
			printD($gap);
			if (role_check('*', $role_man)) #if (perm_check($perm_reports))
				printD(button_n1("Reports", $navi_1_reports, "location.href='$reports_screen'"));
			else
				printD(button_n1("Reports", '', '', '', true));
			printD($gap);
			if (role_check('*', $role_man))#perm_check($perm_ledger))
				printD(button_n1("Finance", $navi_1_finance, "location.href='finance.php'"));
			else
				printD(button_n1("Finance", '', '', '', true));
			printD($gap);
			if (role_check('*', $role_man))#perm_check($perm_ledger))
				printD(button_n1("System", $navi_1_system, "location.href='system.php'") . $gap);
			else
				printD(button_n1("System", '', '', '', true));
			#print "
			#	<a href=\"guide.pdf\" target=\"_blank\" rel=\"noopener\" $txt_style>Help</a>";#REVIEW make live?
			printD($big_gap .
			"<span $txt_style title=\"{$USER['ROLE_TXT']}\">{$USER['USERNAME']}</span>" .
			$gap .
			button_n1("Logout", false, "location.href='login.php?page_task=logout'", 'Are you sure you want to log out of the system?'));
//			"<span style=\"font-family: Arial,Helvetica,sans-serif;\">Logged in as:&nbsp;</span>" .
//			"<span style=\"font-family: Arial,Helvetica,sans-serif;font-weight:bold;\">" . $USER['USERNAME']. "</span>" .
//			$gap .
//			button_n1("Logout", false, "location.href='login.php?page_task=logout'",
//						'Are you sure you want to log out of the system?');

//	print "
//	<script>
//		function pdf_download(sf,lf)
//		{
//			//alert(fname);
//			document.form_pdf_download.short_fname.value = sf;
//			document.form_pdf_download.full_fname.value = '$admin_unix_path/' + lf;
//			document.form_pdf_download.submit();
//		}
//	</script>
//	";

} # navi_1_heading()

function navi_2_heading()
{
	# Called (optionally) by each individual screen's screen_content() function, e.g. standing.php

	global $navi_1_finance;
	global $navi_1_system;

	if ($navi_1_finance)
		navi_2_finance(); # library.php
	elseif ($navi_1_system)
		navi_2_system(); # library.php
}

function navi_3_heading()
{
	# Called (optionally) by each individual screen, e.g. loan.php

}

function navi_2_system()
{
	# Called from library.php / navi_2_heading()

	global $navi_2_sys_audit;
	global $navi_2_sys_feedback;
	global $navi_2_sys_import;
	global $navi_2_sys_mailres;
	global $navi_2_sys_port;
	global $navi_2_sys_portal;
	global $navi_2_sys_purge;
	#global $navi_2_sys_roles;
	global $navi_2_sys_standing;
	global $navi_2_sys_users;
	global $navi_2_sys_vold;
	global $role_man;
	#global $USER;

	$can_view_audit = role_check('*', $role_man);
	$can_view_users = role_check('*', $role_man);
	$can_view_standing = role_check('*', $role_man);
	$can_feedback = true;
	$can_import_data = (global_debug() ? true : false);
	$can_view_old = (global_debug() ? true : false);#role_check('*', $role_man);
	$can_test_mail = (global_debug() ? true : false);

	print "
	<table name=\"table_navi_2\" cellspacing=\"3\" border=\"0\">
	<tr>
		";
		print "
		<td>
		";
			if ($can_feedback)
				print button_n2("Feedback", $navi_2_sys_feedback, "location.href='feedback.php';");
			else
				print button_n2("Feedback", '', '', true);
			print "
		</td>
		";
		print "
		<td>
		";
			if ($can_view_audit)
				print button_n2("Audit", $navi_2_sys_audit, "location.href='audit.php';");
			else
				print button_n2("Audit", '', '', true);
			print "
		</td>
		";
		print "
		<td>
		";
			if ($can_view_users)
				print button_n2("Users", $navi_2_sys_users, "location.href='users.php';");
			else
				print button_n2("Users", '', '', true);
			print "
		</td>
		";
		if (user_is_super_user())
		{
			print "
			<td>
			";
				if ($can_view_users)
					print button_n2("Portal Users", $navi_2_sys_portal, "location.href='users_portal.php';");
				else
					print button_n2("Portal Users", '', '', true);
				print "
			</td>
			";
		}
//		print "
//		<td>
//			";
//			if ($can_edit_users)
//				print button_n2("User Roles", $navi_2_sys_roles, "location.href='roles.php';");
//			else
//				print button_n2("User Roles", '', '', true);
//			print "
//		</td>
//		";
		print "
		<td>
		";
			if ($can_view_standing)
				print button_n2("Standing Data", $navi_2_sys_standing, "location.href='standing.php';");
			else
				print button_n2("Standing Data", '', '', true);
			print "
		</td>
		";
		if (global_debug())
		{
			print "
			<td>
			";
				if ($can_view_old)
					print button_n2("View Old Data Files", $navi_2_sys_vold, "location.href='viewfile.php';");
				else
					print button_n2("View Old Data Files", '', '', true);
				print "
			</td>
			";
			print "
			<td>
			";
				if ($can_import_data)
					print button_n2("Import Old", $navi_2_sys_import, "location.href='import.php';");
				else
					print button_n2("Import Old", '', '', true);
				print "
			</td>
			";
			print "
			<td>
			";
				if ($can_import_data)
					print button_n2("Port", $navi_2_sys_port, "location.href='port.php?web';");
				else
					print button_n2("Port", '', '', true);
				print "
			</td>
			";
			print "
			<td>
			";
				if ($can_test_mail)
					print button_n2("Test PM Mail", '', "location.href='mailtest_pm.php';");
				else
					print button_n2("Test PM Mail", '', '', true);
				print "
			</td>
			";
			print "
			<td>
			";
				if ($can_test_mail)
					print button_n2("Mail Research", $navi_2_sys_mailres, "location.href='mailres.php';");
				else
					print button_n2("Mail Research", '', '', true);
				print "
			</td>
			";
		}
		if (user_is_super_user())
		{
			print "
			<td>
			";
				print button_n2("Purge", $navi_2_sys_purge, "location.href='purge.php';");
				print "
			</td>
			";
		}
		print "
	</tr>
	</table><!--table_navi_2-->
	";
}

function navi_2_finance()
{
	# Called from library.php / navi_2_heading()

	global $navi_2_fin_bulkpay;
	global $navi_2_fin_general;
	global $navi_2_fin_ledger;
	global $navi_2_fin_receipts;
	global $navi_2_fin_stmt;
	global $navi_2_fin_summaries;
	global $role_man;
	#global $USER;

	$can_view_ledger = role_check('*', $role_man);
	$can_view_summaries = role_check('*', $role_man);
	$can_post_receipts = role_check('*', $role_man);
	$can_post_general = role_check('*', $role_man);
	$can_bulk_pay = role_check('*', $role_man);

	print "
	<table name=\"table_navi_2\" cellspacing=\"3\" border=\"0\">
	<tr>
		";
		print "
		<td>
		";
			if ($can_view_ledger)
				print button_n2("View Invoices &amp; Receipts", $navi_2_fin_ledger, "location.href='ledger.php';");
			else
				print button_n2("View Invoices &amp; Receipts", '', '', true);
			print "
		</td>
		";
		print "
		<td>
		";
			if ($can_view_summaries)
				print button_n2("View Summaries", $navi_2_fin_summaries, "location.href='summaries.php';");
			else
				print button_n2("View Summaries", '', '', true);
			print "
		</td>
		";
		print "
		<td>
		";
			if ($can_post_receipts)
				print button_n2("Post Receipts &amp; Adjustments", $navi_2_fin_receipts, "location.href='receipts.php';");
			else
				print button_n2("Post Receipts &amp; Adjustments", '', '', true);
			print "
		</td>
		";
		print "
		<td>
		";
			if ($can_post_general)
				print button_n2("Post General Invoices &amp; Credits", $navi_2_fin_general, "location.href='general.php';");
			else
				print button_n2("Post General Invoices &amp; Credits", '', '', true);
			print "
		</td>
		";
		print "
		<td>
		";
			if ($can_bulk_pay)
				print button_n2("Bulk Payments", $navi_2_fin_bulkpay, "location.href='bulkpay.php';");
			else
				print button_n2("Bulk Payments", '', '', true);
			print "
		</td>
		";
		print "
		<td>
			" . button_n2("Statement Invoicing", $navi_2_fin_stmt, "location.href='stmt_inv.php';") . "
		</td>
		";
		print "
	</tr>
	</table><!--table_navi_2-->
	";
}

function input_button($label, $onclick, $extra='', $id='')
{
	if (strpos($extra, 'cursor') === false)
	{
		$cursor = ((stripos($extra, 'disabled') === false) ? "cursor:pointer;" : '');
		$patt_1 = 'style="';
		$patt_2 = "style='";
		if (strpos($extra, $patt_1) !== false)
			$extra = str_replace($patt_1, $patt_1 . $cursor, $extra);
		elseif (strpos($extra, $patt_2) !== false)
			$extra = str_replace($patt_2, $patt_2 . $cursor, $extra);
		else
			$extra .= " $patt_2{$cursor}'";
	}
	return "<input type=\"button\" " . ($id ? "id=\"$id\"" : '') . " value=\"$label\" onclick=\"$onclick\" $extra>";
}

function input_tickbox($label, $name, $value, $checked, $onclick='', $extra='', $gap=false)
{
	if (strpos($extra, 'color:') === false)
		$extra = input_add_colour($extra); # this has no effect when tickbox is disabled
	if ((strpos($extra, 'visibility:hidden') !== false) || (strpos($extra, 'display:none') !== false))
		$label = "";
	return "<input type=\"checkbox\" name=\"$name\" id=\"$name\" value=\"" . xprint($value,false,1) . "\" " . ($checked ? 'checked ' : '') .
			"onclick=\"$onclick\" $extra>" . ($label ? (($gap ? ' ' : '') . $label) : '');
}

function input_hidden($name, $value)
{
	return "<input type=\"hidden\" name=\"$name\" id=\"$name\" value=\"" . xprint($value,false,1) . "\">";
}

function input_add_colour($extra, $label="")
{
	global $bold_colour;
	global $ro_colour;

	$bold = $bold_colour;
	$ro_col = $ro_colour;

	if (strpos($extra, "kolourred") !== false)
		$colour = "color:red;";
	elseif ((strpos($extra, "disabled") !== false) || (strpos($extra, "readonly") !== false))
		$colour = "color:{$ro_col};";
	else
		$colour = "color:{$bold};";
	$colour_style = ($colour ? "style=\"$colour\"" : '');
	if ($label)
		return "<span $colour_style>$label</span>";

	if ($colour)
	{
		if (($pos = strpos($extra, "style=\"")) !== false)
		{
			$pos += strlen("style=\"");
			$temp = substr($extra, 0, $pos);
			$temp .= $colour;
			$temp .= substr($extra, $pos);
			$extra = $temp;
		}
		else
			$extra .= " style=\"$colour\"";
	}

	return $extra;
}

function input_textbox($name, $value, $size=0, $maxlength=0, $extra='', $idsuffix='')
{
	$value = str_replace('"', '&quot;', $value);
	$extra = input_add_colour($extra);
	return "<input type=\"textbox\" name=\"$name\" id=\"$name{$idsuffix}\" value=\"" . xprint($value,false,1) . "\" " .
			($size ? " size=\"$size\"" : '') . " maxlength=\"" . ($maxlength ? $maxlength : 255) . "\"" . " $extra>";
}

function input_password($name, $value, $size=0, $maxlength=0, $extra='')
{
	$extra = input_add_colour($extra);
	return "<input type=\"password\" name=\"$name\" id=\"$name\" value=\"" . xprint($value,false,1) . "\" " .
			($size ? " size=\"$size\"" : '') . " maxlength=\"" . ($maxlength ? $maxlength : 255) . "\"" . " $extra>";
}

function input_textarea($name, $rows, $cols, $value, $extra='')
{
	$value = str_replace('"', '&quot;', $value);
	$extra = input_add_colour($extra);
	return "<textarea name=\"$name\" id=\"$name\" rows=\"$rows\" cols=\"$cols\" $extra>" . xprint($value,false,1) . "</textarea>";
}

function input_select($name, $list, $val_sel, $extra='', $no_def=false, $txtSelect=true)
{
//	if ($name == 'jt_success')
//	{
//		$type = gettype($val_sel);
//		if ($val_sel)
//			dprint("input_select($name): type=\"$type\" val_sel is something: \"$val_sel\"");
//		elseif ($val_sel === '')
//			dprint("input_select($name): type=\"$type\" val_sel is blank/1");
//		elseif ($val_sel === "")
//			dprint("input_select($name): type=\"$type\" val_sel is blank/2");
//		elseif ($val_sel === 0)
//			dprint("input_select($name): type=\"$type\" val_sel is zero");
//		elseif ($val_sel === '0')
//			dprint("input_select($name): type=\"$type\" val_sel is nought");
//		elseif ($val_sel === false)
//			dprint("input_select($name): type=\"$type\" val_sel is false");
//		else
//			dprint("input_select($name): type=\"$type\" val_sel is empty");
//	}

	$extra = input_add_colour($extra);
	$rc = "<select name=\"$name\" id=\"$name\" $extra>";
	if (!$no_def)
	{
		$txt = ($txtSelect ? 'Select...' : '');
		$rc .= "
		<option value=\"\">" . xprint($txt,false,1) . "</option>
		";
	}
	if (count($list) > 0)
	{
		$sel_null = ((gettype($val_sel) == 'NULL') ? true : false);
		foreach ($list as $val => $txt)
		{
			$selected = '';
			if ($val)
			{
				if ($val_sel == $val)
					$selected = 'selected';
			}
			elseif (($val === '0') || ($val === 0))
			{
				if (($val_sel === '0') || ($val_sel === 0))
					$selected = 'selected';
			}
			elseif ($val === '')
			{
				if ($sel_null || ($val_sel === ''))
					$selected = 'selected';
			}
			$rc .= "
			<option value=\"$val\" $selected>$txt</option>";
		}
	}
	$rc .= "
	</select><!--$name-->
	";
	return $rc;
}

function input_radio($name, $values, $current_val, $gap='', $extra='', $extra_array='')
{
	# $values is array of [ text => value ] e.g. [ "Red" => 'r' ]
	# But if 'text' contains a pipe symbol then the bit before the pipe is the text, and the bit after is the id
	# e.g. [ "Red|rad_r" => 'r' ] would result in
	#		<input type="radio" name="whatever" id="rad_r" value="r"> Red

	$extra = input_add_colour($extra);
	$rc = '';
	$ii = 0;
	$max = count($values);
	foreach ($values as $text => $val)
	{
		$selected = (($current_val == $val) ? true : false);
		$bits = explode('|', $text);
		$txt = $bits[0];
		$id = (isset($bits[1]) ? $bits[1] : '');
		$xa = (($extra_array && array_key_exists($ii, $extra_array)) ? $extra_array[$ii] : '');
		$rc .= "<input type=\"radio\" name=\"$name\" value=\"$val\" " . ($id ? "id=\"$id\" " : '') .
				($selected ? 'checked' : '') . " $extra $xa> " .
				($selected ? input_add_colour($extra, $txt) : $txt);
		$ii++;
		if ($ii < $max)
			$rc .= $gap;
	}
	return $rc;
}

function post_val($name, $is_int=false, $is_date=false, $is_float=false, $clean=2)#$dont_clean=false)
{
	# Return $_POST[$name]
	# $clean:	0 = allow anything
	#			1 = allow all but < and >
	#			2 = allow all but < > & " ' and /

	if (isset($_POST[$name]))
	{
		if (($clean != 0) && ($clean != 1))
			$clean = 2;

		#$allow_some = $dont_clean;
		$val = trim(xprint($_POST[$name],false,$clean));#$allow_some));

		# Don't do the following because it messes up the POST data that is
		# used to create a PDF document.
		# KDB 11/04/14.
//		$val = urldecode($val);
//		if (function_exists('filter_var') )
//			$val = filter_var($val, FILTER_SANITIZE_STRING);
//		else
//		{
//			$val = preg_replace('/(\<script)(.*?)(script>)/si', '', $val);
//			$val = htmlentities( strip_tags($val) );
//		}

		if ($is_int)
			$val = intval(str_replace(' ', '', $val));
		elseif ($is_date)
			$val = date_valid(str_replace(' ', '', $val));
		elseif ($is_float)
			$val = floatval(str_replace(' ', '', str_replace(',', '', str_replace('£', '', $val))));
		elseif ($clean == 2)#!$dont_clean)
			$val = str_replace("'", " ", str_replace('"', ' ', str_replace(';', ' ',
					str_replace('/*', '  ', str_replace('--', '  ', $val)))));
		return $val;
	}
	else
		return ($is_int ? 0 : false);
}

function post_val2($name, $is_int=false)
{
	# This is like post_val() but it doesn't disallow [&], ['] or ["] characters -- see xprint()
	$is_date = false;
	$is_float = false;
	$clean = 1;
	return post_val($name, $is_int, $is_date, $is_float, $clean);
}

function get_val($name, $is_int=false, $is_date=false, $clean=2)#$allow_some=false)
{
	# Return $_GET[$name]
	# $clean:	0 = allow anything
	#			1 = allow all but < and >
	#			2 = allow all but < > & " ' and /
	if (isset($_GET[$name]))
	{
		if (($clean != 0) && ($clean != 1))
			$clean = 2;

		$val = trim(xprint($_GET[$name],false,$clean));#$allow_some));
		if ($is_int)
			$val = intval($val);
		elseif ($is_date)
			$val = date_valid($val);
		return $val;
	}
	else
		return ($is_int ? 0 : '');
}

function get_val2($name)
{
	# This is like get_val() but it doesn't disallow [&], ['] or ["] characters -- see xprint()
	$is_int = false;
	$is_date = false;
	$clean = 1;
	return get_val($name, $is_int, $is_date, $clean);
}

function cookie_val($name)
{
	if (isset($_COOKIE[$name]))
	{
		$val = trim($_COOKIE[$name]);
		return $val;
	}
	else
		return '';
}

function array_val($array, $index, $is_int=false, $make_safe_or_null=false)
{
	if (isset($array[$index]))
	{
		$val = $array[$index];
		if ($is_int)
			$val = intval($val);
		elseif ($make_safe_or_null)
		{
			if ($val)
			{
        		$val = trim(stripslashes_kdb($val));
		    	$val = addslashes_kdb($val);
				$val = "'{$val}'";
			}
			else
				$val = 'NULL';
		}
		return $val;
	}
	else
		return ($is_int ? 0 : ($make_safe_or_null ? 'NULL' : ''));
}

function global_debug()
{
	return user_debug(1);
}

function user_is_super_user()
{
	return user_debug(1);
}

function user_debug($test=0)
{
	# $test==0: return true if USERV.U_DEBUG is true for current user.
	# $test==1: return true if current user is Kevin.
	# $test==2: return true if both {USERV.U_DEBUG is true for current user} and {current user is Kevin}.
	# $test==anything else: as for zero

	global $super_user_id; # Kevin's ID
	global $USER;

	if ($USER)
	{
		if ($test == 1)
		{
			if ($USER['USER_ID'] == $super_user_id)
				return true;
		}
		elseif ($test == 2)
		{
			if (  ($USER['U_DEBUG'] == 1) && ($USER['USER_ID'] == $super_user_id)  )
				return true;
		}
		else
		{
			if ($USER['U_DEBUG'] == 1)
				return true;
		}
	}
	return false;
} # user_debug()

function dprint($line, $force=false, $colour='')
{
	if (user_debug() || $force)
	{
		if ($colour == '')
		{
			if ($force)
				$colour = 'blue';
			else
				$colour = 'red';
		}
		$line = str_replace('<br>', '__BR__', $line);
		$line = str_replace('<', '&lt;', $line);
		$line = str_replace('__BR__', '<br>', $line);
		printD("<p style=\"color:{$colour};\">" . sql_dekey($line) . "</p>");
	}
}

function dlog($line, $force=false)
{
	dprint($line, $force);
	log_write($line);
}

function number_with_commas($num, $dp2=true, $force_dp2=false, $neg_brackets=false)
{
	# With input of e.g. 123456 return "123,456". With decimals: 123456.78 --> 123,456.78.
	# If $dp2 is true and there is a fractional part, then force fractional part to 2 decimal places.
	# If both $dp2 and $force_dp2 are true and there is not a fractional part, then set fractional part to "00".
	# If $neg_brackets is true then show negative numbers with brackets around them.

	if ($num < 0.0)
	{
		$neg = true;
		$num = (-1.0) * $num;
	}
	else
		$neg = false;

	if ($dp2)
		$num = round(floatval($num), 2);

	$bits = explode('.', $num);
	$num = "{$bits[0]}";
	if (isset($bits[1]))
	{
		if ($dp2)
			$fraction = '.' . substr($bits[1] . '00', 0, 2);
		else
			$fraction = '.' . $bits[1];
	}
	elseif ($force_dp2)
		$fraction = '.00';
	else
		$fraction = '';

	$num2 = '';
	while (true)
	{
		if (strlen($num) <= 3)
		{
			$num2 = $num . $num2;
			break;
		}
		else
		{
			$num2 = "," . substr($num, -3, 3) . $num2;
			$num = substr($num, 0, strlen($num) - 3);
		}
	}

	$return = $num2 . $fraction;

	if ($neg)
	{
		if ($neg_brackets)
			$return = "({$return})";
		else
			$return = "-{$return}";
	}
	return $return;
}

function calendar_icon($cal_id, $hidden=false) #, $disabled=false)
{
	$style = "cursor: pointer; border: 1px solid red;";
	if ($hidden)
		$style .= "display: none;";
	$html = "
	<img src=\"js_calendar/img.gif\" id=\"{$cal_id}_trigger\" ";
	#if (!$disabled)
		$html .= "style=\"$style\" title=\"Date selector\" " .
				"onmouseover=\"this.style.background='red';\" onmouseout=\"this.style.background=''\"";
	$html .= "/>";
	return $html;
}

function calendar_setup($cal_id)
{
	return "
	if (document.getElementById('$cal_id'))
		Calendar.setup({
                    inputField     :    \"$cal_id\",   		// id of the input field
                    ifFormat       :    \"%d/%m/%Y\",      	// format of the input field
                    button         :    \"{$cal_id}_trigger\",// trigger for the calendar (button ID)
                    align          :    \"Tl\",           	// alignment (defaults to \"Bl\")
                    singleClick    :    true
                });
    else
    	alert('calendar_setup($cal_id): cannot find element with that ID');
	";
} # calendar_setup();

function quote_smart($value, $force_quotes=false, $add_quotes=true, $add_like=false, $null_if_empty=false)
{
	# If $add_quotes then put quotes around value unless it is numeric,
	# else don't put quotes around it.
	# If $force quotes then put quotes around value even if it is numeric, and ignore $add_quotes.
	# If $add_like then add '%' to each end of $value, for use in a SQL 'LIKE' comparison

	# If !$force_quotes and $add_quotes, and value is pure number beginning with a worthless zero e.g. 012
	# then put quotes around it.
	if ((!$force_quotes) && $add_quotes && ($value != '') && is_numeric($value))
	{
		$val2 = trim("{$value}");
		if ($val2[0] == '0')
		{
			$val2 = intval($value);
			$val2 = "{$val2}";
			if ($val2[0] != '0')
				$force_quotes = true;
		}
	}

	$value = trim(stripslashes_kdb($value));
	if ($force_quotes || (!is_numeric($value)))
	{
    	$value = addslashes_kdb($value);
		if ($add_like)
			$value = "%{$value}%";
		$quote = (($add_quotes || $force_quotes) ? "'" : "");
		if ($null_if_empty && ($value == ''))
			$value = "NULL";
		else
			$value = $quote . $value . $quote;
	}
	return $value;
}

function addslashes_kdb($a)
{
	global $mysql_server; # settings.php
	global $my_sql_conn;

	return ($mysql_server ?
			mysqli_real_escape_string($my_sql_conn, $a)
			:
    		str_replace("'", "''", $a)
    		);
}

function stripslashes_kdb($a)
{
	global $mysql_server; # settings.php

	return ($mysql_server ?
			stripslashes($a)
			:
    		str_replace("''", "'", $a)
    		);
}

function date_for_sql_nqnt($date, $add_day=false)
{
	# Convert dd/mm/yyyy into SQL date with No Quotes and No Time
	return date_for_sql($date, false, false, false, false, $add_day, true, true);
}

function date_for_sql($date, $reverse=false, $keep_time=true, $twodigityear=false,
		$longdate=false, $add_day=false, $no_quotes=false, $no_time=false, $drop_seconds=false, $mmm_yyyy=false,
		$drop_time_if_zero=false)
{
	# Convert readable date to/from sql date
	# $mmm_yyyy can only be used when $reverse==true

	global $months_long;
	global $months_short;

	if (!$reverse)
	{
		if ($date)
		{
			# Convert dd/mm/yyyy to 'yyyy-mm-dd' (with optional suffix hh:mm:ss)
			# NB: quotes are put around the date before it is returned.
			$date_time = explode(' ', $date);
			$date = $date_time[0];
			$time = ((count($date_time) > 1) ? $date_time[1] : '');
			$bits = explode('/', $date);
			if (count($bits) == 3)
			{
				list ($date_mday, $date_mon, $date_year) = $bits;
				if ((1 <= $date_mday) && ($date_mday <= 31) && (1 <= $date_mon) && ($date_mon <= 12))
				{
					$date_year = year_2_to_4($date_year); # We want 4-digit years in the database

					if ($add_day)
					{
						# Add a day to the date.
						# This is useful when selecting within a range of dates in SQL,
						# e.g. WHERE ($dt1 <= THE_DATE) AND (THE_DATE <= $dt2)
						# We add a day to $dt2 and then change condition to ... (THE_DATE < $dt2)
						$old_mon = intval($date_mon);
						$old_mday = intval($date_mday);
						$old_year = intval($date_year);
						$tim = mktime(0, 0, 0, $old_mon, $old_mday, $old_year);
						$tim += (24 * 60 * 60);
						$date_mon = date('m', $tim);
						$date_mday = date('d', $tim);
						$date_year = date('Y', $tim);
						if (intval($date_mday) == $old_mday)
						{
							# Bug fix for when the clocks go back an hour
							$new_mday = intval($date_mday);
							if ((1 <= $old_mday) && ($old_mday <= 31) && (1 <= $new_mday) && ($new_mday <= 31))
							{
								$tim = mktime(0, 0, 0, $old_mon, $old_mday, $old_year);
								$tim += (25 * 60 * 60); # 25 hours to take account of clocks going back
								$date_mon = date('m', $tim);
								$date_mday = date('d', $tim);
								$date_year = date('Y', $tim);
							}	
						}
					}
					$quote = ($no_quotes ? '' : "'");

					$date_mon = substr("0{$date_mon}", -2);
					$date_mday = substr("0{$date_mday}", -2);

					$date = "$quote$date_year-$date_mon-$date_mday";
					if (!$no_time)
					{
						if ($keep_time && $time)
							$date .= " $time";
						else
							$date .= " 00:00:00";
					}
					$date .= $quote;
				}
				else
					$date = 'NULL';
			}
			else
				$date = 'NULL';
		}
		else
			$date = 'NULL';
	}
	else
	{
		if ($date)
		{
			# Convert yyyy-mm-dd to dd/mm/yyyy (with optional suffix hh:mm:ss)
			# Assume date comes from the database and so year will be 4 digits.
			$date_time = explode(' ', $date);
			$date = $date_time[0];
			$time = ((count($date_time) > 1) ? $date_time[1] : '');
			$date_bits = explode('-', $date);
			if (count($date_bits) == 3)
			{
				list ($date_year, $date_mon, $date_mday) = $date_bits;
				if ($mmm_yyyy)
				{
					$date = strtoupper($months_short[intval($date_mon)]) . '-' . "$date_year";
				}
				else
				{
					if ($twodigityear)
						$date_year = substr($date_year,-2);
					if ($longdate)
						$date = "$date_mday " . $months_long[intval($date_mon)] . " $date_year";
					else
						$date = "$date_mday/$date_mon/$date_year";
				}
				if ($keep_time && $time)
				{
					$bits = explode('.', $time);
					$time = $bits[0]; # this converts e.g. "13:44:54.743" into "13:44:54".
					if ($drop_time_if_zero)
					{
						if (substr($time, 0, strlen('00:00:00')) == '00:00:00')
							$keep_time = false;
					}
					if ($keep_time)
					{
						if ($drop_seconds)
						{
							$bits = explode(':', $time);
							unset($bits[2]);
							$time = implode(':', $bits);
						}
						$date .= " $time";
					}
				}
			}
			else
				$date = '';
		}
		else
			$date = '';
	}
	return $date;
}

function date_month_from_prefix($prefix)
{
	# $prefix is e.g. jan, feb, mar.
	global $months_long;

	$prefix = strtolower($prefix);
	foreach ($months_long as $ix => $txt)
	{
		if (strtolower(substr($txt, 0, 3)) == $prefix)
			return $ix;
	}
	return -1;
}

function year_2_to_4($year)
{
	# Convert a 2-digit year to a 4-digit year.
	# This is in a function so that it is done the same way throughout the project.
	# KDB July 2009
	$that_year = 30 + date('y'); # e.g. 51 if year is 2021 (added KDB 09/03/20)
	if ((0 <= $year) && ($year <= $that_year))
		$year += 2000;
	elseif (($that_year < $year) && ($year <= 99))
		$year += 1900;
	return $year;
}

function date_sql_valid($sql_dt)
{
	$dp = date_parse($sql_dt);
	if ($dp !== false)
	{
		if ((0 < $dp['year']) && (0 < $dp['month']) && (0 < $dp['day']))
			return true;
	}
	return false;
}

function date_valid($date)
{
	# $date should be d/m/y or d/m (and current year is automatically added); all numeric, no words.
	# Separator may be / or - or .
	# Return dd/mm/yyyy if valid date or '' if not
	$seps = array('/', '-', '.');
	$out = '';
	foreach ($seps as $sep)
	{
		$bits = explode($sep, $date);
		if (count($bits) == 2)
			$bits[2] = strftime('%Y'); # this year
		if (count($bits) == 3)
		{
			$day = intval($bits[0]);
			if ((1 <= $day) && ($day <= 31))
			{
				$day = sprintf("%02d", $day);
				$month = intval($bits[1]);
				if ((1 <= $month) && ($month <= 12))
				{
					$month = sprintf("%02d", $month);
					$year = intval($bits[2]);
					if ((1 <= $year) && ($year <= 3000))
					{
						$year = sprintf("%04d", year_2_to_4($year));
						$out = "{$day}/{$month}/{$year}";
						break;
					}
				}
			}
		}
	}
	return $out;
}

function nl2br_kdb($a)
{
	global $cr;
	global $crlf;
	global $lf;
	global $nl2br_br;
	return str_replace($lf, $nl2br_br, str_replace($cr, $nl2br_br, str_replace($crlf, $nl2br_br, $a)));
}

function br2nl_kdb($a)
{
	global $crlf;
	return str_replace('<br>', $crlf, str_replace('<br/>', $crlf, str_replace('<br />', $crlf, $a)));
}

function javascript_calendars($calendar_names, $return_html=false)
{
	$html = '';
	if ($calendar_names)
	{
		$html .= "
		<script type=\"text/javascript\">
		";
		foreach ($calendar_names as $one)
			$html .= calendar_setup($one);
		$html .= "
		</script>
		";
	}
	if ($return_html)
		return $html;
	printD($html);
	return '';
}

function role_check($system, $ur_code, $user_id=0)
{
	# Before allowing user access to a screen, test their role to see if they authority to do so.
	# Some screens are system-independent (e.g. Audit screen) and some are specific to a system (e.g. Trace Jobs).
	# For system-indpendent screens, $system is '*'.
	# Otherwise, $system is 'c' or 't' or 'a' (case-insensitive).
	# $ur_code is USER_ROLE_SD.UR_CODE: dev/man/sup/rev/agt/non.
	# Each UR_CODE is system-independent (systems C, T and A).
	# By default this checks the currently logged in user, but $user_id allows another user to be checked.

	global $role_agt;
	global $role_debug;
	global $role_dev;
	global $role_man;
	global $role_rev;
	global $role_sup;
	global $USER;

	$debug = $role_debug;
	if ($debug) dprint("role_check(\$system=$system, \$ur_code=$ur_code, \$user_id=$user_id)", true);

	$system = strtoupper($system);
	$ur_code = strtoupper($ur_code);
	$c_pass = false; # collect system, whether role check is successful
	$t_pass = false; # trace system, whether role check is successful
	$a_pass = false; # accounts system, whether role check is successful

	if (!$user_id)
		$user_id = $USER['USER_ID'];

	if ($ur_code && ($user_id > 0))
	{
		if (($system == 'C') || ($system == '*'))
		{
			$c_role = ''; # collect system, role of current user
			$sql = "SELECT R.UR_CODE FROM USERV U INNER JOIN USER_ROLE_SD R ON R.USER_ROLE_ID=U.USER_ROLE_ID_C
					WHERE (CLIENT2_ID IS NULL) AND U.USER_ID=$user_id";
			if ($debug) dprint($sql, true);
			sql_execute($sql);
			while (($newArray = sql_fetch()) != false)
				$c_role = $newArray[0];
			if ($debug) dprint("-> $c_role", true);

			$c_test = array(); # collect system, roles to test against
			if ($ur_code == $role_dev)
				$c_test = array($role_dev);
			elseif ($ur_code == $role_man)
				$c_test = array($role_dev, $role_man);
			elseif ($ur_code == $role_man)
				$c_test = array($role_dev, $role_man, $role_man);
			elseif ($ur_code == $role_rev)
				$c_test = array($role_dev, $role_man, $role_sup, $role_rev);
			elseif ($ur_code == $role_agt)
				$c_test = array($role_dev, $role_man, $role_sup, $role_rev, $role_agt);

			if ($c_role && $c_test && in_array($c_role, $c_test))
				$c_pass = true;
		}

		if (($system == 'T') || ($system == '*'))
		{
			$t_role = ''; # trace system, role of current user
			$sql = "SELECT R.UR_CODE FROM USERV U INNER JOIN USER_ROLE_SD R ON R.USER_ROLE_ID=U.USER_ROLE_ID_T
					WHERE (CLIENT2_ID IS NULL) AND U.USER_ID=$user_id";
			if ($debug) dprint($sql, true);
			sql_execute($sql);
			while (($newArray = sql_fetch()) != false)
				$t_role = $newArray[0];
			if ($debug) dprint("-> $t_role", true);

			$t_test = array(); # trace system, roles to test against
			if ($ur_code == $role_dev)
				$t_test = array($role_dev);
			elseif ($ur_code == $role_man)
				$t_test = array($role_dev, $role_man);
			elseif ($ur_code == $role_sup)
				$t_test = array($role_dev, $role_man, $role_sup);
			elseif ($ur_code == $role_rev)
				$t_test = array($role_dev, $role_man, $role_sup, $role_rev);
			elseif ($ur_code == $role_agt)
				$t_test = array($role_dev, $role_man, $role_sup, $role_rev, $role_agt);

			if ($t_role && $t_test && in_array($t_role, $t_test))
				$t_pass = true;
		}

		if (($system == 'A') || ($system == '*'))
		{
			$a_role = ''; # accounts system, role of current user
			$sql = "SELECT R.UR_CODE FROM USERV U INNER JOIN USER_ROLE_SD R ON R.USER_ROLE_ID=U.USER_ROLE_ID_A
					WHERE (CLIENT2_ID IS NULL) AND U.USER_ID=$user_id";
			if ($debug) dprint($sql, true);
			sql_execute($sql);
			while (($newArray = sql_fetch()) != false)
				$a_role = $newArray[0];
			if ($debug) dprint("-> $a_role", true);

			$a_test = array(); # accounts system, roles to test against
			if ($ur_code == $role_dev)
				$a_test = array($role_dev);
			elseif ($ur_code == $role_man)
				$a_test = array($role_dev, $role_man);
			elseif ($ur_code == $role_sup)
				$a_test = array($role_dev, $role_man, $role_sup);
			elseif ($ur_code == $role_rev)
				$a_test = array($role_dev, $role_man, $role_sup, $role_rev);
			elseif ($ur_code == $role_agt)
				$a_test = array($role_dev, $role_man, $role_sup, $role_rev, $role_agt);

			if ($a_role && $a_test && in_array($a_role, $a_test))
				$a_pass = true;
		}
	}
	if ($debug) dprint("c=$c_pass, t=$t_pass, a=$a_pass", true);
	if ($c_pass || $t_pass || $a_pass)
		return true;
	return false;
}

function perm_check($up_code) # user permission code
{
	# * THIS FUNCTION IS NOT YET IN USE *

	# Each UP_CODE is specific to either Trace or Collect so no need to specify system when calling this function.

	global $USER;

	if ($up_code && ($USER['USER_ID'] > 0))
	{
		$sql = "SELECT COUNT(*)
				FROM USER_PERM_LINK AS K INNER JOIN USER_PERMISSION_SD AS P ON P.USER_PERMISSION_ID=K.USER_PERMISSION_ID
				WHERE (K.USER_ID={$USER['USER_ID']}) AND (P.UP_CODE='$up_code')";
		sql_execute($sql);
		$count = -1;
		while (($newArray = sql_fetch()) != false)
			$count = $newArray[0];
		if ($count > 0)
			return true;
	}
	return false;
}

function money_format_kdb($num, $pence=true, $html=true, $force_dp2=false)
{
	$num = floatval($num);
	if ($num < 0)
	{
		$neg = true;
		$num = -1.0 * $num;
	}
	else
		$neg = false;
	# 19/11/14: Found out that sprint('%.02f', $num) doesn't do rounding, it just truncates!
	#return ($neg ? '-' : '') . ($html ? '&pound;' : '£') .
	#	number_with_commas(sprintf($pence ? '%.02f' : '%.0f', 1.0 * $num), true, $force_dp2);
	if ($pence)
	{
		$num = (100.0 * $num) + 0.5;
		$num = floor($num);
		$num = 1.0 * $num / 100.0;
	}
	else
	{
		$num = (1.0 * $num) + 0.5;
		$num = floor($num);
		$num = 1.0 * $num / 1.0;
	}
	return ($neg ? '-' : '') . ($html ? '&pound;' : '£') . number_with_commas($num, true, $force_dp2);
}

function date_to_epoch($sql_dt, $keep_time=true, $add_days=0, $keep_date=true)
{
	# Given a date/datetime $sql_dt in SQL format e.g. 2010-11-01 14:50:44, return number of seconds since UNIX epoch.
	# On error, return 01/01/1800 00:00:00.
	# If $keep_time is false then set time to 00:00:00.
	# If $add_days is non-zero then add that number of days to the returned value.
	# If $keep_date is false then only return the time component.
	# Some arguments are mutually exclusive.

	$tim = '';
	if ($sql_dt)
	{
		$dp = date_parse($sql_dt);
		#dprint("parsed=" . print_r($dp,1));#
		if ($dp !== false)
		{
			if ($keep_date)
			{
				if ($keep_time)
					$tim = mktime($dp['hour'], $dp['minute'], $dp['second'], $dp['month'], $dp['day'], $dp['year']);
					#$tim = mktime(1, 3, 5, 12, 10, 2037);
				else
					$tim = mktime(0, 0, 0, $dp['month'], $dp['day'], $dp['year']);
				if ($add_days)
					$tim += (24 * 60 * 60 * $add_days);
			}
			else
				$tim = (3600 * $dp['hour']) + (60 * $dp['minute']) + $dp['second'];
		}
		else
			$tim = mktime(0, 0, 0, 1, 1, 1800);
	}
	else
		$tim = mktime(0, 0, 0, 1, 1, 1800);
	return  $tim;

} # date_to_epoch()

function date_from_epoch($date_only=false, $then_ep='', $twodigityear=true, $first_day=false, $to_sql=false)
{
	# Convert epoch date into readable (or sql) date

	if ($to_sql)
	{
		# Convert epoch date into sql date
		$tim = ($date_only ? '' : " %H:%M:%S");
		return strftime("%Y-%m-" . ($first_day ? '01' : '%d') . $tim, $then_ep);
	}
	else
		# Convert epoch date into readable date
		return date_now($date_only, $then_ep, $twodigityear, $first_day);
}

function date_to_filename()
{
	return strftime("%Y_%m_%d_%H_%M_%S");
}

function date_plus_days($days, $dmy='')
{
	$days = intval($days);
	$date_ep = '';
	if ($days)
	{
		if ($dmy)
		{
			$temp = date_for_sql_nqnt($dmy);
			$start = date_to_epoch($temp);
		}
		else
			$start = time();
		$date_ep = $start + ($days * 24 * 60 * 60);
	}
	return date_now(true, $date_ep, false); # dd/mm/yyyy, no time
}

function date_now_sql($just_date=false, $plus_days=0)
{
	$start = time();
	if ($plus_days)
		$start += ($plus_days * 24 * 60 * 60);
	return strftime("%Y-%m-%d" . ($just_date ? '' : " %H:%M:%S"), $start); # YYYY-MM-DD hh:mm:ss
}

function date_last_month($day_shift=0, $first=false, $last=false, $month_count=1)
{
	# Return epoch of exactly one calendar month ago, then shifted by $day_shift: but only +1 implemented so far.
	# But if $first==true then return the first day of last month.
	# Or if $last == true then return the last day of last month.

	$now = explode('-', strftime("%d-%m-%Y"));
	$day = intval($now[0]);
	$mon = intval($now[1]);
	$yer = intval($now[2]);

	if ($last)
	{
		$day = 1;
		for ($mc = $month_count-1; 0 < $mc; $mc--)
		{
			$mon--;
			if ($mon < 1)
			{
				$mon = 12;
				$yer--;
			}
		}
		$last_month = mktime(0, 0, 0, $mon, $day, $yer); # first day of current month
		$last_month -= (60 * 60 * 24); # last day of previous month
	}
	else
	{
		for ($mc = $month_count; 0 < $mc; $mc--)
		{
			$mon--;
			if ($mon < 1)
			{
				$mon = 12;
				$yer--;
			}
		}
		if ($first)
			$day = 1;
		elseif ($day_shift == 1)
		{
			if ($mon == 2)
			{
				if (($yer % 4) == 0)
					$max_day = 29;
				else
					$max_day = 28;
			}
			elseif (($mon == 4) || ($mon == 6) || ($mon == 9) || ($mon == 11))
				$max_day = 30;
			else
				$max_day = 31;

			$day++;
			if ($max_day < $day)
			{
				$day = 1;
				$mon++;
				if (12 < $mon)
				{
					$mon = 1;
					$yer++;
				}
			}
		} # $day_shift
		$last_month = mktime(0, 0, 0, $mon, $day, $yer);
	}
	return $last_month;

} # date_last_month()

function date_now($date_only=false, $then_ep='', $twodigityear=true, $first_day=false, $words=false, $pretty=false)
{
	# Convert epoch date into readable date
	# Standard output:	06/03/14 23:59:59
	# If $words:		06-Mar-14 23:59:59
	# If $pretty:		6th March 2014 23:59:59 ($twodigityear and $words are ignored)
	# $first_day:		sets day to "01"

	if ($pretty)
	{
		$d = ($then_ep ? strftime('%d', $then_ep) : strftime('%d')); # 01 to 31
		$d = first_etc(intval($d)); # 1st 2nd 3rd etc to 31st
		$m = 'B';
		$y = 'Y';
		$sep = ' ';
	}
	else
	{
		$d = '%d';
		$m = ($words ? 'b' : 'm');
		$y = ($twodigityear ? 'y' : 'Y');
		$sep = ($words ? '-' : '/');
	}
	$date_part = "{$d}{$sep}%$m{$sep}%$y";
	$time_part = ($date_only ? '' : " %H:%M:%S");
	$datetime = $date_part . $time_part;
	$rc = ($then_ep ? strftime($datetime, $then_ep) : strftime($datetime));
	if ($first_day)
	{
		$rc = substr($rc, strpos($rc, $sep)); # change dd/mm/yy to /mm/yy
		$rc = ($pretty ? "1st" : "01") . $rc;
	}
	return $rc;
}

function delete_files($path, $ext, $age, $prefix='', $write_to_log='')
{
	# Delete all files with extension $ext that are at least $age seconds old from the dir $path.
	# If $prefix is specified then only files with names that begin with $prefix are deleted.

	$local_debug = false; #

	if ($ext)
	{
		if ($age)
		{
			if ($path)
			{
				$dirhan = opendir($path);
				if ($dirhan)
				{
					$nfound = 0;
					$ep_threshold = time() - $age;
					$dt_threshold = date_from_epoch(false, $ep_threshold);
					$prefix = strtolower($prefix);
					$plen = strlen($prefix);
					$ext = strtolower($ext);
					$elen = strlen($ext) + 1; # 1 for '.'

					if ($local_debug)
						dprint("Looking in $path", 1);
					while (($filename = readdir($dirhan)) !== false)
					{
						if ($local_debug)
							dprint("Found candidate file $filename, prefix=\"$prefix\", plen=$plen, front=\"" .
								strtolower(substr($filename, 0, $plen)) . "\", ext=\"$ext\", elen=$elen, end=\"" .
								strtolower(substr($filename, -$elen, $elen)) . "\"", 1);
						if ( (($prefix == '') || (strtolower(substr($filename, 0, $plen)) == $prefix)) &&
								(strtolower(substr($filename, -$elen, $elen)) == ".$ext"))
						{
							$ep = filectime("$path/$filename");
							$dt = date_from_epoch(false, $ep);
							if ($local_debug)
								dprint("Found $filename dated $dt, threshold is $dt_threshold",1);
							$nfound++;
							if ($ep < $ep_threshold)
							{
								$delfile = "$path/$filename";
								if ($write_to_log)
									log_write("Deleting file \"$delfile\"");
								if ($local_debug)
									dprint("Purging file $filename ($delfile)",1);
								else
									unlink($delfile);
							}
							elseif ($local_debug)
								dprint("Leaving file $filename",1);
						}
					}
					if (!$nfound)
						dprint("Information: whilst purging old CSV files, no files matching filename pattern " .
								"\"{$prefix}*.$ext\" were found",1); #  (looking in $path)
				}
				else
					dprint("failed to opendir($path)", 1);
			}
			else
				dprint("delete_files(): No path specified.",1);
		}
		else
			dprint("delete_files(): No age specified.",1);
	}
	else
		dprint("delete_files(): No ext specified.",1);

} # delete_files()

function is_date($a)
{
	# Crude function
	$bits = explode('-', $a);
	if (count($bits) == 1)
		$bits = explode('/', $a);
	if ((count($bits) == 3) && is_numeric($bits[0]) && is_numeric($bits[1]) && is_numeric($bits[2]))
		return true;
	return false;
}

function address_from_bits($a1, $a2, $a3, $a4, $pc, $newlines)
{
	global $crlf;

	$add = array();
	if ($a1)
		$add[] = $a1;
	if ($a2)
		$add[] = $a2;
	if ($a3)
		$add[] = $a3;
	if ($a4)
		$add[] = $a4;
	if ($pc)
		$add[] = $pc;
	return implode($newlines ? $crlf : ', ', $add);
}

function javascript_alert($txt)
{
	print "
	<script type=\"text/javascript\">
	alert('$txt');
	</script>
	";
}

function filename_from_freetext($ft)
{
	$new = '';
	$len = strlen($ft);
	for ($ii=0; $ii < $len; $ii++)
	{
		$ch = $ft[$ii];
		if ((('a' <= $ch) && ($ch <= 'z')) || (('A' <= $ch) && ($ch <= 'Z')) || (('0' <= $ch) && ($ch <= '9')))
			$new .= $ch;
		else
			$new .= '_';
	}
	return $new;
}

function first_etc($n)
{
	if (intval($n) <= 0)
		return $n;
	# Note: won't work properly for numbers > 100 e.g. 111, 112, 113, 211, 212, 213, etc
	if (($n == 1) || (($n > 20) && (($n % 10) == 1)))
		return $n . 'st';
	if (($n == 2) || (($n > 20) && (($n % 10) == 2)))
		return $n . 'nd';
	if (($n == 3) || (($n > 20) && (($n % 10) == 3)))
		return $n . 'rd';
	return $n . 'th';
}

function csv_filename($type, $table_name='')
{
	global $USER;

	$fname = "{$type}_{$USER['USER_ID']}";
	if ($table_name)
		$fname .= "_{$table_name}";
	$fname .= ".csv";
	return $fname;
}

function csv_open($dir, $fname, $mode)
{
	$fp = fopen("{$dir}/{$fname}", $mode);
	if ($fp)
		return $fp;
	return false;
}

function csv_close($fp)
{
	fclose($fp);
}

function csv_write($fp, $ary, $max_fields=0, $quotes=0)
{
	# Write array of data to csv file
	# If $max_fields > 0 then only write that many fields.
	# If $quotes==0 then don't put quotes around field unless it contains ("), (') or (,).
	# If $quotes==1 then always put quotes around field.
	# If $quotes==2 then always put quotes around field if it is numeric
	#		- intended to prevent deletion of leading zeroes when load in Excel but didn't help :(
	# If $quotes==-1 then never put quotes around field.

	global $cr;
	global $crlf;
	global $lf;

	if (!$fp)
		return "fp is null";

	$f_count = count($ary);
	if (($max_fields > 0) && ($max_fields < $f_count))
		$f_count = $max_fields;

	$new_ary = array();
	$f_ii = 0;
	foreach ($ary as $name => $f_val)
	{
		if ($f_ii < $f_count)
		{
			# Don't have line feeds in the field, it doesn't work well with Excel
			$f_val = str_replace($crlf, ' ', $f_val);
			$f_val = str_replace($cr, ' ', $f_val);
			$f_val = str_replace($lf, ' ', $f_val);

			if (($quotes != -1) &&
				(	($quotes == 1) ||
					(strpos($f_val,'"') !== false) || (strpos($f_val,"'") !== false) || (strpos($f_val,',') !== false) ||
					( ($quotes == 2) && (is_numeric_kdb($f_val)))
				)
			   )
				$f_val = '"' . str_replace('"', '""', $f_val) . '"';

			$new_ary[$f_ii++] = $f_val;
		}
		else
			break;
		$name=$name;#keep code-checker quiet
	}

	$line = implode(',', $new_ary) . $crlf;
	$line_len = strlen($line);

	$failure_code = false;
	settype($failure_code, 'boolean');
	if (fwrite($fp, $line, $line_len) == $failure_code)
		return "FAILED TO WRITE TO OUTPUT FILE: $line";
	return '';

} # csv_write()

#==============================================================================
function is_numeric_kdb($str_in, $allow_neg=true, $allow_dec=true, $allow_zero=true)
{
	$digits = '0123456789';
	$result = true;
	$found_dec = false;

	$len_in = strlen($str_in);
	if ($len_in == 0)
		return false;

	for ($ii = 0; ($ii < $len_in) && $result; $ii++)
	{
		$char = substr($str_in, $ii, 1);
		if (strpos($digits, $char) === false)
		{
			if ($char == '-')
			{
				if ((!$allow_neg) || ($ii > 0))
					$result = false;
			}
			else if ($char == '.')
			{
				if ((!$allow_dec) || $found_dec)
					$result = false;
				else
					$found_dec = true;
			}
			else
				$result = false;
		}
	}
	if ((!$allow_zero) && (!$str_in))
		$result = false;
	return $result;
}

function count_lines($txt)
{
	global $cr;
	global $crlf;
	global $lf;

	$txt = str_replace($lf, $cr, str_replace($crlf, $cr, $txt));
	$bits = explode($cr, $txt);
	return count($bits);
}

function wrap_html_text($txt, $len)
{
	# Given some text (not containing HTML markup or JS code etc),
	# wrap it onto multiple lines if its length > $len;
	# but break it on spaces, or if this is not possible, break it at $len.

	$truncate = true;
	if ($truncate)
	{
		return substr($txt, 0, $len);
	}

	if (strlen($txt) > $len)
	{
		$right = $txt; # we will move text from $right to $txt
		$txt = '';
		for ($ii=0; $ii<1000; $ii) # put limit on loop so can't loop infinitely
		{
			$left = substr($right, 0, $len); # left end of $right
			$right = substr($right, $len); # right end of $right
			$pos = strrpos($left, ' '); # find last space in left end
			if ($pos !== false)
			{
				$right = substr($left, $pos+1) . $right; # prefix $right with text after space in $left
				$left = substr($left, 0, $pos); # remove same text from $left
			}
			$txt .= $left . "<br>";
			if (strlen($right <= $len))
			{
				$txt .= $right;
				break;
			}
		}
	}
	return $txt;
}

function ascii_codes($a, $plus_txt=false)
{
	$len = strlen($a);
	$out = '';
	for ($ii = 0; $ii < $len; $ii++)
	{
		$ch = $a[$ii];
		$out .= ($out ? ',' : '') . ($plus_txt ? "$ch=" : '') . ord($ch);
	}
	return $out;
}

function printable_chars($a)
{
	$len = strlen($a);
	$out = '';
	for ($ii = 0; $ii < $len; $ii++)
	{
		$ch = $a[$ii];
		$ascii = ord($ch);
		if (($ascii == 9) ||
			((32 <= $ascii) && ($ascii <= 126)))
			$out .= $ch;
		else
			$out .= ' ';
	}
	return $out;
}

function date_add_months_kdb($ep, $mons)
{
	# This is used in preference to strtotime("+{$mons} months", $ep)
	# KDB 10/11/14

	$add_y = floor(floatval($mons) / 12.0); # number of years to add
	$add_m = intval($mons) - (12 * $add_y); # number of months to add

	$yyyymmdd = strftime("%Y %m %d", $ep);
	list($yy, $mm, $dd) = explode(' ', $yyyymmdd);
	$yy = intval($yy);
	$mm = intval($mm);
	$dd = intval($dd);

	$yy = $yy + $add_y;
	$mm = $mm + $add_m;
	if ($mm > 12)
	{
		$yy++;
		$mm = $mm - 12;
	}

	if ($mm == 2) # Feb
	{
		if ($dd > 28)
			$dd = 28;
	}
	elseif (($mm == 4) || ($mm == 6) || ($mm == 9) || ($mm == 11))
	{
		if ($dd > 30)
			$dd = 30;
	}

	$ep2 = mktime(0,0,0,$mm,$dd,$yy);
	#dprint("$yyyymmdd + $mons (+ $add_y Y and $add_m M) = $yy $mm $dd");
	return $ep2;
}

function date_pretty_last_day($sql_dt)
{
	# $sql_dt = e.g. "2013-04-22"
	$yyyy = intval(substr($sql_dt, 0, 4));
	$mm = intval(substr($sql_dt, 5, 2));
	$mm++;
	if ($mm == 13)
	{
		$mm = 1;
		$yyyy++;
	}
	#dprint("$yyyy, $mm");
	$ep = mktime(0, 0, 0, $mm, 1, $yyyy); # 1st day of the following month
	$ep -= (12 * 3600); # go back 12 hours to get to the previous day
	$pretty = strftime("%d-%b-%y", $ep); # e.g. "30-Apr-13"
	return $pretty;
}

function date_pretty_day($sql_dt, $shift=0, $just_month=false, $long_year=false, $month_and_year=false)
{
	$yyyy = intval(substr($sql_dt, 0, 4)); # e.g. "2013-04-10"
	$mm = intval(substr($sql_dt, 5, 2));
	$dd = intval(substr($sql_dt, 8, 2));
	$ep = mktime(0, 0, 0, $mm, $dd, $yyyy) + $shift;
	if ($month_and_year)
		$pretty = strftime("%b " . ($long_year ? "%Y" : "%y"), $ep); # e.g. "Apr 13"
	else
		$pretty = strftime($just_month ? "%b" : ("%d-%b-" . ($long_year ? "%Y" : "%y")), $ep); # e.g. "10-Apr-13"
	return $pretty;
}

function date_pretty_month($sql_dt)
{
	return date_pretty_day($sql_dt, 0, true);
}

function date_pretty_month_and_year($sql_dt)
{
	return date_pretty_day($sql_dt, 0, false, true, true);
}

function date_pretty_day_before($sql_dt)
{
	return date_pretty_day($sql_dt, -12 * 3600);
}

function date_sql_from_ints($y, $m, $d)
{
	return substr("000" . $y, -4, 4) . "-" . substr("0" . $m, -2, 2) . "-" . substr("0" . $d, -2, 2);
}

function truncate_text($txt, $len, $add_dots=false)
{
	if (strlen($txt) > $len)
		$txt = substr($txt, 0, $len) . ($add_dots ? '...' : '');
	return $txt;
}

function is_postcode($txt, $remove_spaces=false)
{
	global $pm_match;

	if ($remove_spaces)
		$txt = str_replace(' ', '', $txt);
	$pm_match = '';
	$pm_matches = array();
	if (preg_match("/[a-z][a-z]?[0-9][0-9]?[ ]*[0-9][a-z][a-z]$/i", trim($txt), $pm_matches) === 1)
	{
		$pm_match = $pm_matches[0];
		return true;
	}
	if (preg_match("/[a-z][a-z]?[0-9][a-z][ ]*[0-9][a-z][a-z]$/i", trim($txt), $pm_matches) === 1)
	{
		$pm_match = $pm_matches[0];
		return true;
	}
	return false;
}

function postcode_outcode($txt, $remove_spaces=false)
{
	$outcode = '';
	if (is_postcode($txt, $remove_spaces))
	{
		$txt2 = str_replace(' ', '', $txt);
		$outcode = strtoupper(substr($txt2, 0, strlen($txt2) - 3));
	}
	return $outcode;
}

function misc_info_read($sys, $key, $raw_dt=false)
{
	# $sys should be 'c', 't' or '*' (case-insensitive)
	# $key is case-insensitive
	# If $raw_dt is true then return the raw date value, not converted

	//global $mysql_server;

	$debug = false; #
	$sys = strtoupper($sys);
	$key = strtoupper($key);
	$rc = '*mi-ERROR*';
	$null = '*mi-NULL*';
	if (($sys == 'C') || ($sys == 'T') || ($sys == '*'))
	{
		if ($debug)
			dprint("C/T/* OK", true);
		//if ($mysql_server)
		//{
		//    $convert_int = "CONVERT(VALUE_INT,CHAR)";
		//    $convert_dec = "CONVERT(VALUE_DEC,CHAR)";
		//    $convert_txt = "VALUE_TXT";
		//    $convert_dt = "CONVERT(VALUE_DT,CHAR)";
		//    $convert_enc = sql_decrypt('VALUE_ENC');
		//}
		//else
		//{
		//    $convert_int = "CONVERT(VARCHAR(100),VALUE_INT)";
		//    $convert_dec = "CONVERT(VARCHAR(100),VALUE_DEC)";
		//    $convert_txt = "VALUE_TXT";
		//    $convert_dt = "CONVERT(VARCHAR(100),VALUE_DT)";
		//    $convert_enc = sql_decrypt('VALUE_ENC');
		//}
		$convert_int = sql_convert("VALUE_INT", "CHAR", 100);
		$convert_dec = sql_convert("VALUE_DEC", "CHAR", 100);
		$convert_txt = "VALUE_TXT";
		$convert_dt = sql_convert("VALUE_DT", "CHAR", 100);
		$convert_enc = sql_decrypt('VALUE_ENC');

		sql_encryption_preparation('MISC_INFO');
		$sql = "SELECT " .
				($raw_dt ? "VALUE_DT" :
					("
					CASE WHEN VALUE_INT IS NULL THEN '$null' ELSE $convert_int  END AS OUTPUT_INT,
					CASE WHEN VALUE_DEC IS NULL THEN '$null' ELSE $convert_dec  END AS OUTPUT_DEC,
					CASE WHEN VALUE_TXT IS NULL THEN '$null' ELSE $convert_txt  END AS OUTPUT_TXT,
					CASE WHEN VALUE_DT  IS NULL THEN '$null' ELSE $convert_dt   END AS OUTPUT_DT,
					CASE WHEN VALUE_ENC IS NULL THEN '$null' ELSE $convert_enc  END AS OUTPUT_ENC
					")) . "
				FROM MISC_INFO
				WHERE (MISC_SYS" . (($sys == '*') ? ' IS NULL' : "='$sys'") . ") AND (MISC_KEY='$key')";
		if ($debug)
			dprint($sql, true);
		sql_execute($sql);
		while (($newArray = sql_fetch_assoc()) != false)
		{
			if ($raw_dt)
			{
				$rc = $newArray['VALUE_DT'];
				if ($debug)
					dprint("Raw VALUE_DT=\"$rc\"", true);
			}
			elseif ($newArray['OUTPUT_INT'] != $null)
			{
				$rc = intval($newArray['OUTPUT_INT']);
				if ($debug)
					dprint("OUTPUT_INT=\"$rc\"", true);
			}
			elseif ($newArray['OUTPUT_DEC'] != $null)
			{
				$rc = floatval($newArray['OUTPUT_DEC']);
				if ($debug)
					dprint("OUTPUT_DEC=\"$rc\"", true);
			}
			elseif ($newArray['OUTPUT_TXT'] != $null)
			{
				$rc = "{$newArray['OUTPUT_TXT']}";
				if ($debug)
					dprint("OUTPUT_TXT=\"$rc\"", true);
			}
			elseif ($newArray['OUTPUT_DT'] != $null)
			{
				$rc = "{$newArray['OUTPUT_DT']}";
				if ($debug)
					dprint("OUTPUT_DT=\"$rc\"", true);
			}
			elseif ($newArray['OUTPUT_ENC'] != $null)
			{
				$rc = "{$newArray['OUTPUT_ENC']}";
				if ($debug)
					dprint("OUTPUT_ENC=\"$rc\"", true);
			}
			else
			{
				if ($debug)
					dprint("all null!", true);
			}
		}
	}
	return $rc;
}

function misc_info_write($sys, $key, $type, $value, $descr='', $audit=true)
{
	# $sys should be 'c', 't' or '*' (case-insensitive)
	# $key is case-insensitive
	# $descr is only used if we are inserting a new record
	# $type should be int, dec, txt or dt (case-insensitive)
	# $audit should only be false if called from the import script

	global $audit_debug;

	$sys = strtoupper($sys);
	$key = strtoupper($key);
	$type = strtoupper($type);
	if (	(($sys == 'C') || ($sys == 'T') || ($sys == '*')) &&
			(($type == 'INT') || ($type == 'DEC') || ($type == 'TXT') || ($type == 'DT'))
		)
	{
		$s_sys = (($sys == '*') ? 'NULL' : "'$sys'");
		$s_key = quote_smart($key);
		$s_descr = quote_smart($descr);
		$s_int = (($type == 'INT') ? intval($value) : 'NULL');
		$s_dec = (($type == 'DEC') ? floatval($value) : 'NULL');
		$s_txt = (($type == 'TXT') ? quote_smart($value) : 'NULL');
		switch ($type)
		{
			case 'INT': $misc_type = "'I'"; break;
			case 'DEC': $misc_type = "'F'"; break;
			case 'DT': $misc_type = "'D'"; break;
			default: $misc_type = "'T'"; break;
		}
		if ($type == 'DT')
		{
			$s_dt = date_to_epoch($value); # convert SQL time into Epoch seconds (or 01/01/1800 if not valid date)
			$s_dt = "'" . date_from_epoch(false, $s_dt, false, false, true) . "'"; # Epoch back to SQL time
		}
		else
			$s_dt = 'NULL';

		$sql = "SELECT MISC_INFO_ID, MISC_SYS FROM MISC_INFO WHERE MISC_KEY=$s_key";
		if ($audit_debug) dprint($sql);
		sql_execute($sql);
		$misc_info_id = -1;
		$db_sys = '';
		while (($newArray = sql_fetch()) != false)
		{
			$misc_info_id = $newArray[0];
			$db_sys = $newArray[1];
		}
		if ($misc_info_id <= 0)
		{
			$sql = "INSERT INTO MISC_INFO (MISC_SYS, MISC_KEY, MISC_DESCR, MISC_TYPE,  VALUE_INT, VALUE_DEC, VALUE_TXT, VALUE_DT)
								VALUES (   $s_sys,   $s_key,   $s_descr,   $misc_type, $s_int,    $s_dec,    $s_txt,    $s_dt)";
			if ($audit_debug) dprint($sql);
			dprint($sql);
			if ($audit)
				audit_setup_gen('MISC_INFO', 'MISC_INFO_ID', 0, '', '');
			sql_execute($sql, $audit);
			$misc_info_id = sql_lastID();
		}
		else
		{
			if ($db_sys != $sys)
			{
				if (($db_sys != '') || ($sys != '*'))
				{
					dprint("*** Error: system mismatch with misc_info_write($sys, $key, $type, $value, ...) ID=$misc_info_id");
					return;
				}
			}

			# We must set all the non-$type fields to NULL to ensure data integrity
			$sql = "UPDATE MISC_INFO SET VALUE_INT=$s_int WHERE MISC_INFO_ID=$misc_info_id";
			#if ($type == 'INT')
			#	dprint($sql);
			if ($audit_debug) dprint($sql);
			if ($audit)
				audit_setup_gen('MISC_INFO', 'MISC_INFO_ID', $misc_info_id, 'VALUE_INT', $s_int);
			sql_execute($sql, $audit);

			$sql = "UPDATE MISC_INFO SET VALUE_DEC=$s_dec WHERE MISC_INFO_ID=$misc_info_id";
			#if ($type == 'DEC')
			#	dprint($sql);
			if ($audit_debug) dprint($sql);
			if ($audit)
				audit_setup_gen('MISC_INFO', 'MISC_INFO_ID', $misc_info_id, 'VALUE_DEC', $s_dec);
			sql_execute($sql, $audit);

			$sql = "UPDATE MISC_INFO SET VALUE_TXT=$s_txt WHERE MISC_INFO_ID=$misc_info_id";
			#if ($type == 'TXT')
			#	dprint($sql);
			if ($audit_debug) dprint($sql);
			if ($audit)
				audit_setup_gen('MISC_INFO', 'MISC_INFO_ID', $misc_info_id, 'VALUE_TXT', $s_txt);
			sql_execute($sql, $audit);

			$sql = "UPDATE MISC_INFO SET VALUE_DT=$s_dt WHERE MISC_INFO_ID=$misc_info_id";
			#if ($type == 'DT')
			#	dprint($sql);
			if ($audit_debug) dprint($sql);
			if ($audit)
				audit_setup_gen('MISC_INFO', 'MISC_INFO_ID', $misc_info_id, 'VALUE_DT', $s_dt);
			sql_execute($sql, $audit);
		}
	}
}

function yesno2bool($a, $blank_is_no=true, $others='')
{
	$a = strtolower($a);
	if (($a == 'yes') || ($a == 'y'))
		return true;
	if (($a == 'no') || ($a == 'n') || ($blank_is_no && ($a === '')))
		return false;
	if ($others)
	{
		foreach ($others as $key => $val)
		{
			if ($a == $key)
				return $val;
		}
	}
	return -1;
}

function password_test($pwd) # Should be same as password_test() in js_main.js
{
	# Check that the password pwd satisfies the Password Policy using globals $pwd_min, $pwd_max and $password_policy.
	# If so, return an empty string.
	# Else return the policy.

	global $password_policy; # settings.php
	global $pwd_max; # settings.php
	global $pwd_min; # settings.php

	$count_n = 0; // count of numbers
	$count_u = 0; // count of upper case
	$count_l = 0; // count of lower case
	$len = strlen($pwd);
	if ($pwd && ($pwd_min <= $len) && ($len <= $pwd_max))
	{
		for ($ii=0; $ii < $len; ++$ii)
		{
			$ch = substr($pwd, $ii, 1);
			if (('0' <= $ch) && ($ch <= '9'))
				$count_n++;
			if (('A' <= $ch) && ($ch <= 'Z'))
				$count_u++;
			if (('a' <= $ch) && ($ch <= 'z'))
				$count_l++;
		}
	}
	if (($count_n > 0) && ($count_u > 0) && ($count_l > 0))
		return '';
	return $password_policy;
}

function money_or_blank($money)
{
	if (floatval($money) > 0)
		return money_format_kdb($money, true, true, true);
	return '';
}

function title_first_last($title, $first, $last, $default='')
{
	$return = $title;
	if ($first)
		$return .= ($return ? ' ' : '') . $first;
	if ($last)
		$return .= ($return ? ' ' : '') . $last;
	if ($return == '')
		$return = $default;
	return $return;
}

function server_php_self($just_name=false)
{
	$self = xprint($_SERVER['PHP_SELF'],false,1);
	if ($just_name)
	{
		$bits = explode('/', $self);
		$self = $bits[count($bits)-1];
	}
    return $self;
}

function pound_clean($a, $method)
{
	global $ascii_pound;

	if ($method == 1)
		return str_replace(chr($ascii_pound), '&pound;', $a);
	elseif ($method == 2)
	{
		# Get rid of corrupt pound sign: replace 194,163 with just &pound;
		$strlen = strlen($a);
		$new_text = '';
		for ($ii=0; $ii < $strlen; $ii++)
		{
			$done = false;
			$chr1 = $a[$ii];
			if (($ii+1) < $strlen)
			{
				$ord1 = ord($chr1);
				$chr2 = $a[$ii+1];
				$ord2 = ord($chr2);
				if (($ord1 == 194) && ($ord2 == 163))
				{
					#$new_text .= chr(163); # this doesn't work
					$new_text .= '&pound;';
					$ii++;
					$done = true;
				}
			}
			if (!$done)
				$new_text .= $chr1;
		}
		return $new_text;
	}
	else
		return 'pound_clean-error';
}

function excel_next_column($colm)
{
	# $colm should be column label e.g. 'A' or 'B' or 'DF' etc
	# Return the next column label e.g. 'B' or 'C' or 'DG' etc

	$base = ord('A') - 1;
	$ret = '';

	if (strlen($colm) == 1)
	{
		$cnum = ord($colm) - $base; # 1 to 26
		$cnum++; # next column
		#dprint("In/1=$colm, cnum=$cnum");#
		if ($cnum <= 26)
		{
			$ret = chr($base + $cnum);
			#dprint("x=$colm, c=$cnum, out=$ret");#
			return $ret;
		}
	}
	else
	{
		$cn_1 = ord($colm[0]) - $base; # 1 to 26
		$cn_2 = ord($colm[1]) - $base; # 1 to 26
		$cnum = ($cn_1 * 26) + $cn_2;
		$cnum++; # next column
		#dprint("In/2=$colm, cnum=$cnum");#
	}

	$cn_1 = floor(($cnum-1) / 26);
	$cn_2 = (($cnum-1) % 26) + 1;
	$out_1 = chr($base + $cn_1);
	$out_2 = chr($base + $cn_2);
	$ret = "{$out_1}{$out_2}";
	#dprint("x=$colm, c=$cnum=($cn_1,$cn_2)=($out_1,$out_2), out=$ret");#
	return $ret;
}

function excel_output_see_function_phpExcel_output()
{
	# This function is not used, it just refers to phpExcel_output()
}

function phpExcel_output($subdir, $xfile, $sheets)
{
	# Create an Excel spreadsheet (version Excel5, extension ".xls").
	# See required include files at the top of this script.
	# $xfile is filename with no path and no extension, the path is always $csv_path/$subdir.
	# $sheets is an array of one or more worksheets, where each element of $sheets should contain:
	#      $title_short: this is written onto the worksheet tab i.e. worksheet name.
	#      $top_lines: this is zero or more lines that are writen to the worksheet before the $headings.
	#      $headings: these are the column headings.
	#      $datalines: these are written after $headings.
	#      $bottom_lines: are written after $datalines - set to '' if not required.
	#      $formats: if a particular cell (in $datalines) is to be formatted specially, then add it to $formats,
	#          e.g. $formats = array('C' => $excel_currency_format) will make column C have that format but all other columns are "general" format.

	global $csv_path;
	global $excel_currency_format;
	global $excel_debug;
	global $excel_decimal_format;
	global $excel_int_or_dec_format;
	global $excel_integer_format;
	global $phpExcel_ext; # settings.php: "xls"

	$xfile .= ".$phpExcel_ext";
	if ($excel_debug) dlog("Enter phpExcel_output(). Writing " . count($sheets) . " sheets to Excel file \"$xfile\" (under \"$subdir\")");

	# List of characters that cannot be used in the title (tab name) of the worksheet
	$illegal_title_characters = array('*', '/'); # probably not a complete set
	$max_title_len = 31; # title (tab name) of a worksheet must not have more characters than this

	# Create new PHPExcel object
	$objPHPExcel = new PHPExcel();

	$sheet_no = 0; # sheet number; first sheet is zero
	foreach ($sheets as $sheet)
	{
		list($title_short, $top_lines, $headings, $datalines, $formats, $bottom_lines) = $sheet;
		if ($excel_debug) dlog("Sheet $sheet_no: $title_short");

		if (0 < $sheet_no)
			$objPHPExcel->createSheet($sheet_no);
		$objPHPExcel->setActiveSheetIndex($sheet_no);

		# Replace illegal characters
		foreach ($illegal_title_characters as $itc)
			$title_short = str_replace($itc, '.', $title_short);
		$title_short = str_replace('[', '(', str_replace(']', ')', $title_short));

		if ($max_title_len < strlen($title_short))
			$title_short = substr($title_short, 0, $max_title_len);
		#dprint("Setting sheet $sheet_no title to \"$title_short\"...");#
		$objPHPExcel->getActiveSheet()->setTitle($title_short);

		if ($excel_debug) dlog("...sheet $sheet_no: processing top_lines");
		$x_row = 1;
		foreach ($top_lines as $tline)
		{
			$x_col = 'A';
			$colrow = "{$x_col}{$x_row}";
			foreach ($tline as $cell)
			{
				if (($x_row == 1) && (strpos($cell, '##') === false))
				{
					$objPHPExcel->getActiveSheet()->getStyle($colrow)->getFont()->setSize(20);
					$objPHPExcel->getActiveSheet()->getStyle($colrow)->getFont()->setBold(true);
				}
				else
				{
					if (strpos($cell, '##BOLD##') !== false)
					{
						$cell = str_replace('##BOLD##', '', $cell);
						$objPHPExcel->getActiveSheet()->getStyle($colrow)->getFont()->setBold(true);
					}
					if (strpos($cell, '##CURR##') !== false)
					{
						$cell = str_replace('##CURR##', '', $cell);
						$objPHPExcel->getActiveSheet()->getStyle($colrow)->getNumberFormat()->setFormatCode($excel_currency_format);
					}
				}
				$objPHPExcel->getActiveSheet()->setCellValue($colrow, $cell);
				$x_col = excel_next_column($x_col);
				#$x_col = chr(ord($x_col)+1); # Won't work past 'Z'
				$colrow = "{$x_col}{$x_row}";
			}
			$x_row++;
		}

		if ($excel_debug) dlog("...sheet $sheet_no: processing headings");
		$x_col = 'A';
		$colrow = "{$x_col}{$x_row}";
		foreach ($headings as $cell)
		{
			$objPHPExcel->getActiveSheet()->getStyle($colrow)->getFont()->setBold(true);
			$objPHPExcel->getActiveSheet()->setCellValue($colrow, $cell);
			$x_col = excel_next_column($x_col);
			#$x_col = chr(ord($x_col)+1); # Won't work past 'Z'
			$colrow = "{$x_col}{$x_row}";
		}
		$x_row++;

		if ($excel_debug) dlog("...sheet $sheet_no: processing datalines");
		$rec_count = 0;
		foreach ($datalines as $dline)
		{
			$x_col = 'A';
			$colrow = "{$x_col}{$x_row}";
			if ($excel_debug) dlog("... ...DL $rec_count col=\"$x_col\", colrow=\"$colrow\", count(dline)=" . count($dline));
			$excel_debug_2 = (($excel_debug && (25700 < $rec_count)) ? true : false);
			foreach ($dline as $cell)
			{
				if ($excel_debug_2) dlog("... ... ... Cell Block start: col=\"$x_col\", colrow=\"$colrow\"");
				if (strpos($cell, '##TXT##') !== false)
				{
					$cell = str_replace('##TXT##', '', $cell);
					# Don't set a format for this cell
				}
				elseif (strpos($cell, '##BOLD##') !== false)
				{
					$cell = str_replace('##BOLD##', '', $cell);
					$objPHPExcel->getActiveSheet()->getStyle($colrow)->getFont()->setBold(true);
				}
				elseif ($formats && array_key_exists($x_col, $formats))
				{
					$fmt = $formats[$x_col];
					if ($fmt == $excel_int_or_dec_format)
					{
						if (floor(floatval($cell)) < floatval($cell))
							$fmt = $excel_decimal_format;
						else
							$fmt = $excel_integer_format;
					}
					if ($excel_debug_2) dlog("... ... ... ... setting format to \"$fmt\"");
					$objPHPExcel->getActiveSheet()->getStyle($colrow)->getNumberFormat()->setFormatCode($fmt);
				}
				if ($excel_debug_2) dlog("... ... ... ... setting value to \"$cell\"");
				$objPHPExcel->getActiveSheet()->setCellValue($colrow, $cell);
				if ($excel_debug_2) dlog("... ... ... ... calling excel_next_column(\"$x_col\")");
				$x_col = excel_next_column($x_col);
				if ($excel_debug_2) dlog("... ... ... Cell Block end: col=\"$x_col\"");
				#$x_col = chr(ord($x_col)+1); # Won't work past 'Z'
				$colrow = "{$x_col}{$x_row}";
				if ($excel_debug_2) dlog("... ... ... Cell Block end: colrow=\"$colrow\"");
			}
			$x_row++;
			$rec_count++;
		}

		if ($excel_debug) dlog("...sheet $sheet_no: processing bottom_lines");
		if ($bottom_lines)
		{
			foreach ($bottom_lines as $bline)
			{
				$x_col = 'A';
				$colrow = "{$x_col}{$x_row}";
				foreach ($bline as $cell)
				{
					$objPHPExcel->getActiveSheet()->setCellValue($colrow, $cell);
					$x_col = excel_next_column($x_col);
					#$x_col = chr(ord($x_col)+1); # Won't work past 'Z'
					$colrow = "{$x_col}{$x_row}";
				}
				$x_row++;
			}
		}

		if ($excel_debug) dlog("...sheet $sheet_no: moving to next sheet");
		$sheet_no++;
	} # foreach ($sheets)
	if ($excel_debug) dlog("Done sheets");

	# Write to file on disk
	if ($excel_debug) dlog("Calling createWriter()...");
	$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
	if ($excel_debug) dlog("Calling check_dir() on \"$csv_path$subdir\"...");
	$pdf_dir = check_dir($csv_path . $subdir);
	if ($pdf_dir)
	{
		if ($excel_debug) dlog("Calling save() to \"$pdf_dir/$xfile\"...");
		$objWriter->save($pdf_dir . '/' . $xfile);
	}
	else
	{
		dlog("*** phpExcel_output() could not create directory \"{$csv_path}{$subdir}\"");
	}
	if ($excel_debug) dlog("Exiting phpExcel_output(): wrote $rec_count data lines to \"" . $pdf_dir . '/' . $xfile . "\"");

} # phpExcel_output()

function months_range($month_start, $month_end, $yyyymm, $reverse=false)
{
	# If $yyyymm==true then inputs are YYYY-MM otherwise YYYY-MM-01
	# If $reverse==true then range is newest to oldest.

	# Strip out quotes and strip off the time element if it exists (YYYY-MM-DD HH:MM:SS)
	$bits = explode(' ', trim(str_replace("'", "", $month_start)));
	$month_start = $bits[0];
	$bits = explode(' ', trim(str_replace("'", "", $month_end)));
	$month_end = $bits[0];

	$months = array($month_start);

	if ($yyyymm)
	{
		$month_start .= "-01";
		$month_end .= "-01";
	}

	$safety = 10000;
	$month = $month_start;
	while (true)
	{
		$safety--;
		if ($safety <= 0)
		{
			dprint("*=* months_range($month_start, $month_end): Safety Exit from while(true)!!");
			break;
		}
		list($yr, $mn, $dy) = explode('-', $month);
		$mn++;
		if ($mn < 10)
			$mn = "0$mn";
		elseif ($mn == 13)
		{
			$yr++;
			$mn = "01";
		}
		$month = "$yr-$mn-$dy";
		if (substr($month,0,7) <= substr($month_end,0,7)) # Compare just YYYY-MM
			$months[] = ($yyyymm ? substr($month, 0, strlen('yyyy-mm')) : $month);
		else
		{
			#dprint("Ended on month \"$month\"");#
			break;
		}
	}
	if ($reverse)
		return array_reverse($months);
	return $months;
} # months_range()

function date_first($dt1, $dt2)
{
	return date_first_or_last($dt1, $dt2, 1);
}

function date_last($dt1, $dt2)
{
	return date_first_or_last($dt1, $dt2, -1);
}

function date_first_or_last($dt1, $dt2, $fol)
{
	# $dt1 and $dt2 are SQL format: YYYY-MM-DD HH:MM:SS (or blank)
	# $fol==1 => find first date
	# $fol==-1 => find last date
	if (1 <= $fol)
		$fol = true;
	else
		$fol = false;
	$DT = '';
	if ($dt1 && $dt2)
	{
		if ($dt1 <= $dt2)
			$DT = ($fol ? $dt1 : $dt2);
		else
			$DT = ($fol ? $dt2 : $dt1);
	}
	elseif ($dt1)
		$DT = $dt1;
	elseif ($dt2)
		$DT = $dt2;
	return $DT;
}

function phone_to_text($ph)
{
	# For export to Excel. If phone number is just digits starting with zero, stick in a space. Prevents Excel discarding leading zero.
	$temp = trim($ph);
	if ((5 < strlen($temp)) && ($temp[0] === '0') && is_numeric_kdb($temp, false, false, false))
		$ph = substr($temp, 0, 5) . " " . substr($temp, 5);
	return $ph;
}

function date_from_mmyy($mmyy)
{
	# Take a 3 or 4 digit myy or mmyy and convert it into yyyy-mm-01
	$dt = '';
	$len = strlen($mmyy);
	if (($len == 3) || ($len == 4))
	{
		$yy = intval(substr($mmyy, -2, 2));
		if ((0 <= $yy) && ($yy <= 99))
		{
			$that_year = 30 + date('y'); # e.g. 51 if year is 2021 (added KDB 09/03/20)
			$yyyy = (($yy <= $that_year) ? (2000 + $yy) : (1900 + $yy));
			$mm = intval(substr($mmyy, 0, ($len==3) ? 1 : 2));
			if ((1 <= $mm) && ($mm <= 12))
			{
				if ($mm < 10)
					$mm = "0{$mm}";
				$dt = "$yyyy-$mm-01";
				#dprint("date_from_mmyy($mmyy) --> \"$dt\" via \"$yy\", \"$yyyy\", \"$mm\"");
			}
		}
	}
	return $dt;
} # date_from_mmyy()

function date_n_years_ago($years_ago)
{
	$dd = strftime("%d");
	$mm = strftime("%m");
	$yyyy = strftime("%Y") - $years_ago;
	$dt = "{$yyyy}-{$mm}-{$dd}";
	return $dt;
} # date_n_years_ago()

function email_valid($email)
{
	$email = trim($email);
	if (!$email)
		return false;
	if (strpos($email, ' ') !== false)
		return false;
	return (bool)preg_match(
                    '/^(?!(?>(?1)"?(?>\\\[ -~]|[^"])"?(?1)){255,})(?!(?>(?1)"?(?>\\\[ -~]|[^"])"?(?1)){65,}@)' .
                    '((?>(?>(?>((?>(?>(?>\x0D\x0A)?[\t ])+|(?>[\t ]*\x0D\x0A)?[\t ]+)?)(\((?>(?2)' .
                    '(?>[\x01-\x08\x0B\x0C\x0E-\'*-\[\]-\x7F]|\\\[\x00-\x7F]|(?3)))*(?2)\)))+(?2))|(?2))?)' .
                    '([!#-\'*+\/-9=?^-~-]+|"(?>(?2)(?>[\x01-\x08\x0B\x0C\x0E-!#-\[\]-\x7F]|\\\[\x00-\x7F]))*' .
                    '(?2)")(?>(?1)\.(?1)(?4))*(?1)@(?!(?1)[a-z0-9-]{64,})(?1)(?>([a-z0-9](?>[a-z0-9-]*[a-z0-9])?)' .
                    '(?>(?1)\.(?!(?1)[a-z0-9-]{64,})(?1)(?5)){0,126}|\[(?:(?>IPv6:(?>([a-f0-9]{1,4})(?>:(?6)){7}' .
                    '|(?!(?:.*[a-f0-9][:\]]){8,})((?6)(?>:(?6)){0,6})?::(?7)?))|(?>(?>IPv6:(?>(?6)(?>:(?6)){5}:' .
                    '|(?!(?:.*[a-f0-9]:){6,})(?8)?::(?>((?6)(?>:(?6)){0,4}):)?))?(25[0-5]|2[0-4][0-9]|1[0-9]{2}' .
                    '|[1-9]?[0-9])(?>\.(?9)){3}))\])(?1)$/isD',
                    $email
                );
} # email_valid()

function date_sql_to_epoch($sql_dt)
{
	$yyyy = intval(substr($sql_dt, 0, 4));
	$mm = intval(substr($sql_dt, 5, 2));
	$dd = intval(substr($sql_dt, 8, 2));
	$HH = intval(substr($sql_dt, 11, 2));
	$MM = intval(substr($sql_dt, 14, 2));
	$SS = intval(substr($sql_dt, 17, 2));
	$ep = mktime($HH, $MM, $SS, $mm, $dd, $yyyy);
	#dprint("\"$sql_dt\" -- \"$yyyy~$mm~$dd $HH~$MM~$SS\"");#
	return $ep;
} # date_sql_to_epoch()

function check_dir($dir)
{
	global $cronjob; # settings.php and auto_xxx.php
	global $unix_path;

	$orig_dir = $dir;
	if ($cronjob)
	{
		if (strpos($dir, $unix_path) === false)
			$dir = "{$unix_path}/{$dir}";
	}
	if (!file_exists($dir))
	{
		if ($cronjob)
			log_write("check_dir(\"$orig_dir\") calling mkdir(\"$dir\")...");
		mkdir($dir);
		if (!file_exists($dir))
			return '';
	}
	return $dir;
} # check_dir()

function post_values()
{
	return "POST=" . xprint(print_r($_POST,1),false,1);
}

function xprint($a, $print_it=true, $clean=2)#$allow_some=false)#, $bypass=false)
{
	# Help combat XSS
	# $clean:	0 = allow anything
	#			1 = allow all but < and >
	#			2 = allow all but < > & " ' and /
	if ($clean == 0)
		$b = $a;
	else
	{
		$b = str_replace("] =>", "_ARRAY_VALUE_", $a);
		if ($clean == 1)
			$b = str_replace("<", "&lt;",
				 str_replace(">", "&gt;",
				$b));
		else
			$b = str_replace("&", "&amp;",
				 str_replace("<", "&lt;",
				 str_replace(">", "&gt;",
				 str_replace("\"", "&quot;",
				 str_replace("'", "&#x27;",
				 str_replace("/", "&#x2F;",
				$b))))));
		$b = str_replace("_ARRAY_VALUE_", "] =>", $b);
	}
	if ($print_it)
		print $b;
	else
		return $b;
} # xprint()

function script_name($a)
{
	if ($a == '')
		return 'noScript';
	$pos = strrpos($a, '/');
	if ($pos === false)
		$pos = 0;
	else
		$pos++;
	$b = substr($a, $pos);
	$pos = strpos($b, '?');
	if ($pos !== false)
		$b = substr($b, 0, $pos);
	return strtolower($b);
}

function printD($a)
{
	# Delayed print
	# If $screen_delay is true then don't print $a but instead add it to $screen_html

	global $screen_delay; # settings.php - see also $screen_html
	global $screen_html; # settings.php - see also $screen_delay

	if ($screen_delay)
		$screen_html .= $a;
	else
		print $a;
}

function cookie_options($time)
{
	# Used in call to setcookie(), from 06/10/20, for PHP v7.4
	global $cookie_secure;
	return array('expires' => $time, 'path' => '/', 'domain' => '', 'secure' => $cookie_secure, 'httponly' => true, 'samesite' => 'strict');
}

function phpversion_kdb()
{
	$bits = explode('.', phpversion());
	$num = floatval($bits[0]) + (floatval($bits[1]) / 10.0); # e.g. 7.2 from "7.2.33" or 7.4 from "7.4.11"
	return $num;
}

function strftime_rdr($format)
{
	return (new DateTime)->format(str_replace('%', '', $format));
}

?>
