<?php

#session_start();

include_once('ip_address.php');

$ip_local = "127.0.0.1";
$ip_rdr_kdb = "80.195.207.143"; # IP address of KDB at RDR from 13/10/20

$screen_delay = false; # delay printing of HTML til all processing done
$screen_html = ''; # used if $screen_delay is true

# First, do security checks on scripts and IP addresses
$script_debug = false;#
global $script_check; # a cronjob script may set this to true to bypass the tests
#$script_check = true; # enable this line to bypass the tests - NOTE: also need to rename .htaccess
if (!$script_check)
	include_once('script_check.php');
if ($script_check)
{
	if ($script_debug)
		print '<p>Check OK</p>';
}
else
{
	if (!$script_debug)
		print '<meta http-equiv="refresh" content="0; url=https://www.google.co.uk/search?q=no+entry+sign&tbm=isch" />';
	exit;
}

# We have passed the security checks

ini_set('display_errors','On');
date_default_timezone_set("Europe/London");

include_once('site.php');
global $mysql_server; # site.php
global $site_live; # site.php
global $site_local; # site.php
global $site_forge; # site.php
global $sql_ignore_error; # 29/06/22
global $visual_studio; # site.php

$login_debug = false;#
$cronjob = false;
$time_tests = false;
$auto_letter = false;

if ($site_local)
{
	# My Windows PC
	$protocol = "http";
	$port = ($visual_studio ? ':1234' : '');
	$root_domain = 'vilcol';
	$admin_dir = 'web';
	#$site_domain = "localhost:8080/vilcol/web"; # Win 7 Phoe PC
	#$site_domain = "localhost/vilcol2/web"; # Win 10 Toshiba Laptop
	$site_domain = "localhost{$port}/" . ($visual_studio ? '' : "{$root_domain}/") . "{$admin_dir}"; # Win 10 Phoe PC
	$unix_subdomain = "C:\\Apache24\\htdocs\\{$root_domain}";
	$unix_path = "{$unix_subdomain}\\{$admin_dir}";
	$admin_domain = $site_domain;
	$csv_dir = "csvex"; # folder name containing csv files that are exported from some screen
	$slash = "\\";
	$cookie_secure = false;
}
//elseif ($site_rdr)
//{
//	# Test/Demo site on RDR Linux server
//	$protocol = "http";
//	$root_domain = 'rdresearch.co.uk';
//	$admin_dir = 'viltest';
//	$site_domain = "www.{$root_domain}";
//	$unix_subdomain = "/var/www/vhosts/$root_domain";
//	$unix_path = "$unix_subdomain/httpdocs/{$admin_dir}";
//	$admin_domain = "{$site_domain}/{$admin_dir}";
//	$csv_dir = "csvex"; # folder name containing csv files that are exported from some screen
//	$slash = "/";
//}
elseif ($site_live)
{
	# Live site on RDR Linux server
	$protocol = "http";
	$root_domain = 'vilcoldb.com';
	$admin_dir = 'admin';
	$site_domain = "www.{$root_domain}";
	$unix_subdomain = "/var/www/vhosts/$root_domain";
	$unix_path = "$unix_subdomain/httpdocs/{$admin_dir}";
	$admin_domain = "{$site_domain}/{$admin_dir}";
	$csv_dir = "csvex"; # folder name containing csv files that are exported from some screen
	$slash = "/";
//	# Live site on Vilcol Windows server
//	$protocol = "http";
//	$root_domain = 'vilcol';
//	$site_domain = "localhost:8080/{$root_domain}/web";
//	$unix_subdomain = "C:\\Program Files (x86)\\Zend\\Apache2\\htdocs\\{$root_domain}";
//	$admin_dir = 'web';
//	$unix_path = "{$unix_subdomain}\\{$admin_dir}";
//	$admin_domain = $site_domain;
//	$csv_dir = "csvex"; # folder name containing csv files that are exported from some screen
//	$slash = "\\";
	$cookie_secure = true;
}
elseif ($site_forge)
{
	# Live site on RDR Forge server
	$protocol = "http";
	$root_domain = 'vilcoldbl.com';
	$admin_dir = 'admin';
	$site_domain = "www.{$root_domain}";
	$unix_subdomain = "/home/forge/$root_domain";
	$unix_path = "$unix_subdomain/{$admin_dir}";
	$admin_domain = "{$site_domain}/{$admin_dir}";
	$csv_dir = "csvex"; # folder name containing csv files that are exported from some screen
	$slash = "/";
	$cookie_secure = true;
}

$site_url = "{$protocol}://$site_domain";
$admin_url = "{$protocol}://$admin_domain";
$admin_unix_path = "{$unix_path}";
$site_title = "Vilcol";
$page_title = $site_title;
$csv_path = str_replace("\\", "\\\\", "{$admin_unix_path}{$slash}{$csv_dir}{$slash}");
$login_successful = 0; # 0 = login untried; 1 = login successful; -1 = login unsuccessful
$reps_subdir = 'reps';

$my_sql_conn = ''; # MySQL
$ms_sql_conn = ''; # MS SQL Server
if ($mysql_server)
{
	# MySQL server
	$sqlFalse = 'FALSE'; # can also use '0'
	$sqlTrue = 'TRUE'; # can also use '1'
	$sqlNow = 'NOW()';
	$lcase = 'LOWER';
	$sqlLen = 'CHAR_LENGTH';
	# See also sql_top_limit(), sql_date_add(), sql_date_diff(), sql_convert().
}
else
{
	# MS SQL Server
	$sqlFalse = '0';
	$sqlTrue = '1';
	$sqlNow = 'GETDATE()';
	$lcase = 'LOWER'; # or LCASE
	$sqlLen = 'LEN';
	# See also sql_top_limit(), sql_date_add(), sql_date_diff(), sql_convert().
}
$sql_ignore_error = false;

if ($site_local)
{
	if ($mysql_server)
	{
		$sql_host = 'localhost';
		$sql_username = 'root';
		$sql_password = 'invinoveritas';
		$sql_database = 'vilcoldb';
	}
	else
	{
		$ms_sql_host = ($visual_studio ? 'VILTEST32' : 'VILTEST64'); # On Lara PC: System DSN (64-bit) for SQL Server, Server=OPTIPLEX\SQLEXPRESS, Database=VILCOLDB, User=viltestdb.
										# Delete user viltestdb and recreate it. 21/05/19.
		$ms_sql_username = 'viltestdb';
		$ms_sql_password = 'i38DYqB2T3st';
	}
}
elseif ($site_live)
{
	if ($mysql_server)
	{
		$sql_host = 'localhost';
		$sql_username = 'vilcoldb_user';
		$sql_password = 'i38DYqB2T3st';
		$sql_database = 'VILCOLDB';
	}
	else
	{
		$ms_sql_host = '575775-app3';
		$ms_sql_username = 'vilcoldb_user';
		$ms_sql_password = 'i38DYqB2T3st';
	}
}
elseif ($site_forge)
{
	$sql_host = 'vilcol-rds.cjszchdkthyc.eu-west-2.rds.amazonaws.com';
	$sql_username = 'aws_admin';
	$sql_password = 'yllJHiYHS7HGkqeirUTR';
	$sql_database = 'vilcoldb';
}

$sql_key = "R3p0mAn";
$enc_prep_tables = array();

$vat_number = '528 0207 70';
$bank_line_1 = 'Barclays Bank PLC';
$bank_line_2 = 'Walton-on-Thames';
$bank_line_2b = 'Name: Village Investigations Ltd';
$bank_line_3 = 'Sort Code: 20-90-56';
$bank_line_4 = 'A/C No: 80904899';

$cookie_user_id = "vcl_uid";
$customer_domain = "vilcol.com"; # not the admin site, but the customer's public-facing site
$customer_url = "http://www.$customer_domain";

$email_kevin = 'kevin@rdresearch.co.uk';
$email_service = 'service@vilcol.com';
$email_collections = 'collections@vilcol.com';
$emailName_collections = 'Vilcol Collections';
$email_ccare = 'customercare@vilcol.com';
$emailName_ccare = 'Vilcol Customer Care';
$email_trace = 'Denise@vilcol.com';
$emailName_trace = 'Vilcol Tracing';
$email_accounts = 'Adam@vilcol.com';
$emailName_accounts = 'Vilcol Accounts';

$system_user_id = -1; # alternative to USERV.USER_ID when 'user' is the system doing something automatically
$super_user_id = 1; # KDB's USERV.USER_ID
$steve_user_id = 7; # Steve Rowland's USERV.USER_ID

$screen_width = 1245; # 1245 pixels so that screen fits on a 1280x1024 browser even with a vertical scrollbar
$no_header = false; # header.php
$login_timeout = 7200; # 2 hours
#$login_timeout = 10; # 10 seconds

$pwd_min = 8; # same as pwd_min in js_main.js
$pwd_max = 20; # same as pwd_max in js_main.js
$password_policy = # same as password_policy in js_main.js
	"Passwords must be between $pwd_min and $pwd_max characters long with mixed-case letters and numbers";
$password_wrong_limit = 6; # 6 times wrong in a row leads to lock-out

$cr = chr(13);
$lf = chr(10);
$crlf = $cr . $lf;
$nl2br_br = '<br>'; # Used by nl2br_kdb() and br2nl_kdb()

$phpExcel_ext = "xls";
$excel_integer_format = '#,##0';
$excel_integer_format_no_comma = '0';
$excel_decimal_format = '0.00';
$excel_int_or_dec_format = "IntOrDec";
$excel_currency_format = '£#,##0.00';
$excel_date_format = 'dd/mm/yyyy';

$V_Text_Size = 6000; # Size of VARBINARY() data type used for CLIENT_NOTE.CN_NOTE and JOB_NOTE.JL_TEXT
$v_text_clip = 4000; # Max text size that we insert into CLIENT_NOTE.CN_NOTE and JOB_NOTE.JL_TEXT
$upload_max_size = 600 * 1024 * 1024; # 600 MB

$button_colour = '#a61d3b'; # RGB=166,29,59
$colpdf_colour = '#8e5055'; # RGB=142,80,85
$tr_colour_1 = '#bee1ef';
$tr_colour_2 = '#dbeff7';
$tr_colour_warn_1 = '#ffc6c6';
$tr_colour_warn_2 = '#ffa4a4';
$style_l = "style=\"text-align:left;\"";
$style_r = "style=\"text-align:right;\"";
$dis_style_l = "disabled $style_l"; #color:{$nearly_black};
$dis_style_r = "disabled $style_r"; #color:{$nearly_black};
$bold_colour = "#0082be"; # text colour for input boxes, background colour for non-current tabs
$ro_colour = "#004160"; # text colour for read-only input boxes
$grey = "style=\"color:gray;\"";
$grey_colour = '#808080';
$reports_title_width = 630;

# Main screens:
$navi_1_home = false;
$navi_1_clients = false;
$navi_1_jobs = false;
$navi_1_finance = false;
$navi_1_reports = false;
$navi_1_system = false;

# Sub-screens for Finance screen:
$navi_2_fin_ledger = false; # view invoices & receipts
$navi_2_fin_receipts = false; # post receipts & adjustments
$navi_2_fin_general = false; # post general invoices & credits
$navi_2_fin_stmt = false; # statement invoicing
$navi_2_fin_summaries = false; # view summaries
$navi_2_fin_bulkpay = false; # bulk payments

# Sub-screens for System screen:
#$navi_2_sys_roles = false;
$navi_2_sys_users = false;
$navi_2_sys_portal = false; # Portal users / Client users
$navi_2_sys_purge = false;
$navi_2_sys_import = false;
$navi_2_sys_port = false;
$navi_2_sys_vold = false;
$navi_2_sys_standing = false;
$navi_2_sys_feedback = false;
$navi_2_sys_audit = false;
$navi_2_sys_mailres = false;

$id_USER_ROLE_developer = 1; # USER_ROLE_SD.ROLE_ID for "Developer"
$id_USER_ROLE_manager = 2; # USER_ROLE_SD.ROLE_ID for "Manager"
$id_USER_ROLE_super = 3; # USER_ROLE_SD.ROLE_ID for "Supervisor"
$id_USER_ROLE_rev = 4; # USER_ROLE_SD.ROLE_ID for "Reviewer"
$id_USER_ROLE_agent = 5; # USER_ROLE_SD.ROLE_ID for "Agent"
$id_USER_ROLE_none = 6; # USER_ROLE_SD.ROLE_ID for "None"
$id_ROUTE_tous = 1; # PAYMENT_ROUTE_SD.PAYMENT_ROUTE_ID for "To us" (or "Vilcol" or "To Vilcol")
$id_ROUTE_fwd = 2; # PAYMENT_ROUTE_SD.PAYMENT_ROUTE_ID for "Forwarded"
$id_ROUTE_direct = 3; # PAYMENT_ROUTE_SD.PAYMENT_ROUTE_ID for "Direct"
$id_ROUTE_cspent = 4; # PAYMENT_ROUTE_SD.PAYMENT_ROUTE_ID for "Cash spent" (or "Returned" or "Adjustment")
$PAYMENT_ROUTES = array(); # see lib_vilcol.php / init_data()

$id_JOB_TYPE_trc = 1; # JOB_TYPE_SD.JOB_TYPE_ID for "TRC"
$id_JOB_TYPE_mns = 2; # JOB_TYPE_SD.JOB_TYPE_ID for "MNS"
$id_JOB_TYPE_tm = 3; # JOB_TYPE_SD.JOB_TYPE_ID for "T/M"
$id_JOB_TYPE_tc = 4; # JOB_TYPE_SD.JOB_TYPE_ID for "T/C"
$id_JOB_TYPE_rt1 = 5; # JOB_TYPE_SD.JOB_TYPE_ID for "RT1"
$id_JOB_TYPE_rt2 = 6; # JOB_TYPE_SD.JOB_TYPE_ID for "RT2"
$id_JOB_TYPE_rt3 = 7; # JOB_TYPE_SD.JOB_TYPE_ID for "RT3"
$id_JOB_TYPE_svc = 8; # JOB_TYPE_SD.JOB_TYPE_ID for "SVC"
$id_JOB_TYPE_att = 9; # JOB_TYPE_SD.JOB_TYPE_ID for "ATT"
$id_JOB_TYPE_rpo = 10; # JOB_TYPE_SD.JOB_TYPE_ID for "RPO"
$id_JOB_TYPE_oth = 11; # JOB_TYPE_SD.JOB_TYPE_ID for "OTH"
$id_JOB_TYPE_etr = 12; # JOB_TYPE_SD.JOB_TYPE_ID for "ETR"
$id_JOB_TYPE_maximum_built_in = 12; # maximum ID for all built-in job types
$ids_JOB_TYPE_retraces = array(); # JOB_TYPE_SD.JOB_TYPE_ID for Retrace job types - init_data()

$id_JOB_STATUS_act = 0; # JOB_STATUS_SD.JOB_STATUS_ID for "ACT" - init_data()
$id_JOB_STATUS_rcr = 0; # JOB_STATUS_SD.JOB_STATUS_ID for "RCR" - init_data()

$id_ACTIVITY_lse = 19; # ACTIVITY_SD.ACTIVITY_ID for "LSE"
$id_ACTIVITY_par = 25; # ACTIVITY_SD.ACTIVITY_ID for "PAR"

# Trace Letters:
$id_LETTER_TYPE_trace_no = 1; # LETTER_TYPE_SD.LETTER_TYPE_ID for "Trace/no"
$id_LETTER_TYPE_trace_yes = 2; # LETTER_TYPE_SD.LETTER_TYPE_ID for "Trace/yes"
$id_LETTER_TYPE_means_no = 3; # LETTER_TYPE_SD.LETTER_TYPE_ID for "Means/no"
$id_LETTER_TYPE_means_yes = 4; # LETTER_TYPE_SD.LETTER_TYPE_ID for "Means/yes"
$id_LETTER_TYPE_repo_no = 5; # LETTER_TYPE_SD.LETTER_TYPE_ID for "Repo/no"
$id_LETTER_TYPE_repo_yes = 6; # LETTER_TYPE_SD.LETTER_TYPE_ID for "Repo/yes"
$id_LETTER_TYPE_other_no = 7; # LETTER_TYPE_SD.LETTER_TYPE_ID for "Other/no"
$id_LETTER_TYPE_other_yes = 8; # LETTER_TYPE_SD.LETTER_TYPE_ID for "Other/yes"
$id_LETTER_TYPE_serv_no = 9; # LETTER_TYPE_SD.LETTER_TYPE_ID for "Service/no"
$id_LETTER_TYPE_serv_yes = 10; # LETTER_TYPE_SD.LETTER_TYPE_ID for "Service/yes"
$id_LETTER_TYPE_retrc1_no = 11; # LETTER_TYPE_SD.LETTER_TYPE_ID for "Retrace(1)/no"
$id_LETTER_TYPE_retrc1_yes = 12; # LETTER_TYPE_SD.LETTER_TYPE_ID for "Retrace(1)/yes"
$id_LETTER_TYPE_retrc1_foc = 13; # LETTER_TYPE_SD.LETTER_TYPE_ID for "Retrace(1)/FOC"
$id_LETTER_TYPE_retrc2_no = 14; # LETTER_TYPE_SD.LETTER_TYPE_ID for "Retrace(2)/no"
$id_LETTER_TYPE_retrc2_yes = 15; # LETTER_TYPE_SD.LETTER_TYPE_ID for "Retrace(2)/yes"
$id_LETTER_TYPE_retrc2_foc = 16; # LETTER_TYPE_SD.LETTER_TYPE_ID for "Retrace(2)/FOC"
$id_LETTER_TYPE_retrc3_no = 17; # LETTER_TYPE_SD.LETTER_TYPE_ID for "Retrace(3)/no"
$id_LETTER_TYPE_retrc3_yes = 18; # LETTER_TYPE_SD.LETTER_TYPE_ID for "Retrace(3)/yes"
$id_LETTER_TYPE_retrc3_foc = 19; # LETTER_TYPE_SD.LETTER_TYPE_ID for "Retrace(3)/FOC"
$id_LETTER_TYPE_tc_no = 20; # LETTER_TYPE_SD.LETTER_TYPE_ID for "T/C/no"
$id_LETTER_TYPE_tc_yes = 21; # LETTER_TYPE_SD.LETTER_TYPE_ID for "T/C/yes"
$id_LETTER_TYPE_tm_no = 22; # LETTER_TYPE_SD.LETTER_TYPE_ID for "T/M/no"
$id_LETTER_TYPE_tm_yes = 23; # LETTER_TYPE_SD.LETTER_TYPE_ID for "T/M/yes"
$id_LETTER_TYPE_attend_no = 24; # LETTER_TYPE_SD.LETTER_TYPE_ID for "Attend/no"
$id_LETTER_TYPE_attend_yes = 25; # LETTER_TYPE_SD.LETTER_TYPE_ID for "Attend/yes"

# Collection Letters:
$id_LETTER_TYPE_letter_1 = 26; # LETTER_TYPE_SD.LETTER_TYPE_ID for "Letter 1"
$id_LETTER_TYPE_letter_2 = 27; # LETTER_TYPE_SD.LETTER_TYPE_ID for "Letter 2"
$id_LETTER_TYPE_letter_3 = 28; # LETTER_TYPE_SD.LETTER_TYPE_ID for "Letter 3"
$id_LETTER_TYPE_contact = 29; # LETTER_TYPE_SD.LETTER_TYPE_ID for "Contact" letter
$id_LETTER_TYPE_demand = 30; # LETTER_TYPE_SD.LETTER_TYPE_ID for "Demand" letter
$id_LETTER_TYPE_swalp = 90; # LETTER_TYPE_SD.LETTER_TYPE_ID for "Swalp" letter
$id_LETTER_TYPE_max = $id_LETTER_TYPE_swalp;

$letter_system = ''; # valid values: '', 'c' or 't'; used by pdf_create()

$systems = array('g' => 'General', 't' => 'Trace', 'c' => 'Collect');
$SYSTEMS = array('G' => 'General', 'T' => 'Trace', 'C' => 'Collect');

$sz_date = 8; # size of input textbox for date
$td_w_date = 116; # size of <td> with box of size $sz_date and calendar icon

$col2 = "colspan=\"2\"";
$col3 = "colspan=\"3\"";
$col4 = "colspan=\"4\"";
$col5 = "colspan=\"5\"";
$col6 = "colspan=\"6\"";
$col7 = "colspan=\"7\"";
$col8 = "colspan=\"8\"";
$col9 = "colspan=\"9\"";
$col10 = "colspan=\"10\"";
$col11 = "colspan=\"11\"";
$col12 = "colspan=\"12\"";
$col13 = "colspan=\"13\"";
$col16 = "colspan=\"16\"";
$col22 = "colspan=\"22\"";

$ar = "align=\"right\"";
$tar = "style=\"text-align:right;\"";
$al = "align=\"left\"";
$ac = "align=\"center\"";
$at = "valign=\"top\"";
$ab = "valign=\"bottom\"";
$em_dash = "&#8212;";

$js_esc_quote = "\\" . "'"; # escaped quote \' used sometimes in javascript

# Roles
$role_dev = 'DEV';
$role_man = 'MAN';
$role_sup = 'SUP';
$role_rev = 'REV';
$role_agt = 'AGT';
$role_non = 'NON';

# Permissions
$perm_t_us_v = 'T_US_V';
$perm_t_us_e = 'T_US_E';
$perm_t_au_v = 'T_AU_V';
$perm_c_us_v = 'C_US_V';
$perm_c_us_e = 'C_US_E';
$perm_c_au_v = 'C_AU_V';
#$perm_clients = 'PM_CLIENTS';
#$perm_jobs = 'PM_JOBS';
#$perm_ledger = 'PM_LEDGER';
#$perm_reports = 'PM_REPORTS';
#$perm_users = 'PM_USERS';

$denial_message = "<p>You do not have permission to view this screen</p>";

# "£" appears in $_POST as unicode character decimal 194,163 or hex c2,a3 but we strip out £ before posted.
$safe_pound = "__SAFE_POUND__"; # convert £ to this before Ajax call, and convert back when call received
$uni_pound = "u00a3"; # unicode representation of a £
$pound_194 = chr(194);
$ascii_pound = 163; # £ symbol read in from Enterprise spreadsheet; see also pound_clean()
$safe_amp = "_^amp^_";
$safe_slash = "_^sla^_";

$file_event_upload = 'U';
$file_event_create = 'C';
$file_event_delete = 'D';

# Conditions for testing whether a job is open or closed. Requires JOB alias of J and a link to JOB_STATUS_SD AS ST.
#$condition_open =   "  (J.J_COMPLETE=0 OR J.J_COMPLETE IS NULL) AND (J.J_CLOSED_DT IS NULL)     AND ( ((J.JC_JOB=1) AND (ST.J_STTS_CLOSED=0)) OR ((J.JT_JOB=1) AND (J.JT_SUCCESS IS NULL)) )    ";
#$condition_closed = "( (J.J_COMPLETE=1)                         OR  (J.J_CLOSED_DT IS NOT NULL) OR    ((J.JC_JOB=1) AND (ST.J_STTS_CLOSED=1)) OR ((J.JT_JOB=1) AND (J.JT_SUCCESS IS NOT NULL)) )";
#$condition_join_js = "LEFT JOIN JOB_STATUS_SD AS ST ON ST.JOB_STATUS_ID=J.JC_JOB_STATUS_ID";

# List for <select> box that needs yes, no and n/a options.
$ynna_list = array(1 => 'Yes', 0 => 'No', -1 => 'N/A');
$yn_list = array(1 => 'Yes', 0 => 'No');
$ynfoc_list = array(0 => 'No', 1 => 'Yes', -1 => 'FOC');
$success_return = -3; # for JT_SUCCESS='Returned to Agent'
$ynfrns_list = array(0 => 'No', 1 => 'Yes', -1 => 'FOC', $success_return => 'Returned', 'NULL' => 'Not set');
$ynfrxns_list = array(0 => 'No', 1 => 'Yes', -1 => 'FOC', $success_return => 'Returned', -2 => 'N/Y/F/R', 'NULL' => 'Not set');
$ynpend_list = array(0 => 'No', 1 => 'Yes', -1 => 'Review', -9 => 'No/Review');

$today = strftime("%Y-%m-%d");
$mktime_year_limit = 2037; # mktime() fails with a year bigger than this (KDB 09/08/16)

$months_long = array(
1 => 'January',
2 => 'February',
3 => 'March',
4 => 'April',
5 => 'May',
6 => 'June',
7 => 'July',
8 => 'August',
9 => 'September',
10 => 'October',
11 => 'November',
12 => 'December'
);

$months_short = array(
1 => 'Jan',
2 => 'Feb',
3 => 'Mar',
4 => 'Apr',
5 => 'May',
6 => 'Jun',
7 => 'Jul',
8 => 'Aug',
9 => 'Sep',
10 => 'Oct',
11 => 'Nov',
12 => 'Dec'
);

$country_list = array(
	'AD' => 'ANDORRA',
	'AE' => 'UNITED ARAB EMIRATES',
	'AF' => 'AFGHANISTAN',
	'AG' => 'ANTIGUA & BARBUDA',
	'AI' => 'ANGUILLA',
	'AL' => 'ALBANIA',
	'AM' => 'ARMENIA',
	'AN' => 'NETHERLANDS ANTILLES',
	'AO' => 'ANGOLA',
	'AQ' => 'ANTARCTICA',
	'AR' => 'ARGENTINA',
	'AS' => 'AMERICAN SAMOA',
	'AT' => 'AUSTRIA',
	'AU' => 'AUSTRALIA',
	'AW' => 'ARUBA',
	'AX' => 'ALAND ISLANDS',
	'AZ' => 'AZERBAIJAN',
	'BA' => 'BOSNIA AND HERZEGOVINA',
	'BB' => 'BARBADOS',
	'BD' => 'BANGLADESH',
	'BE' => 'BELGIUM',
	'BF' => 'BURKINA FASO',
	'BG' => 'BULGARIA',
	'BH' => 'BAHRAIN',
	'BI' => 'BURUNDI',
	'BJ' => 'BENIN',
	'BL' => 'SAINT BARTHELEMY',
	'BM' => 'BERMUDA',
	'BN' => 'BRUNEI',
	'BO' => 'BOLIVIA',
	'BQ' => 'BONAIRE, SAINT EUSTATIUS & SAB',
	'BR' => 'BRAZIL',
	'BS' => 'BAHAMAS',
	'BT' => 'BHUTAN',
	'BV' => 'BOUVET ISLANDS',
	'BW' => 'BOTSWANA',
	'BY' => 'BELARUS',
	'BZ' => 'BELIZE',
	'CA' => 'CANADA',
	'CC' => 'COCOS (KEELING) ISLANDS',
	'CD' => 'CONGO, THE DEMOCRATIC REPUBLIC',
	'CF' => 'CENTRAL AFRICAN REPUBLIC',
	'CG' => 'CONGO',
	'CH' => 'SWITZERLAND',
	'CI' => 'COTE D\'IVOIRE',
	'CK' => 'COOK ISLANDS',
	'CL' => 'CHILE',
	'CM' => 'CAMEROON',
	'CN' => 'CHINA',
	'CO' => 'COLOMBIA',
	'CR' => 'COSTA RICA',
	'CU' => 'CUBA',
	'CV' => 'CAPE VERDE',
	'CW' => 'CURACAO',
	'CX' => 'CHRISTMAS ISLANDS',
	'CY' => 'CYPRUS',
	'CZ' => 'CZECH REPUBLIC',
	'DE' => 'GERMANY',
	'DJ' => 'DJIBOUTI',
	'DK' => 'DENMARK',
	'DM' => 'DOMINICA',
	'DO' => 'DOMINICAN REPUBLIC',
	'DZ' => 'ALGERIA',
	'EC' => 'ECUADOR',
	'EE' => 'ESTONIA',
	'EG' => 'EGYPT',
	'EH' => 'WESTERN SAHARA',
	'ER' => 'ERITREA, THE STATE OF',
	'ES' => 'SPAIN',
	'ET' => 'ETHIOPIA',
	'EU' => 'Europe',
	'FI' => 'FINLAND',
	'FJ' => 'FIJI',
	'FK' => 'FALKLAND ISLANDS',
	'FM' => 'MICRONESIA (FEDERAL STATES OF)',
	'FO' => 'FAROE ISLANDS',
	'FR' => 'FRANCE',
	'GA' => 'GABON',
	'GB' => 'United Kingdom',
	'GD' => 'GRENADA',
	'GE' => 'GEORGIA',
	'GF' => 'FRENCH GUYANA',
	'GG' => 'Guernsey',
	'GH' => 'GHANA',
	'GI' => 'GIBRALTAR',
	'GL' => 'GREENLAND',
	'GM' => 'GAMBIA',
	'GN' => 'GUINEA',
	'GP' => 'GUADELOUPE',
	'GQ' => 'EQUATORIAL GUINEA',
	'GR' => 'GREECE',
	'GS' => 'SOUTH GEORGIA & SANDWICH',
	'GT' => 'GUATEMALA',
	'GU' => 'GUAM',
	'GW' => 'GUINEA-BISSAU',
	'GY' => 'GUYANA',
	'HK' => 'HONG KONG',
	'HM' => 'HEARD & MACDONALD ISLANDS',
	'HN' => 'HONDURAS',
	'HR' => 'CROATIA',
	'HT' => 'HAITI',
	'HU' => 'HUNGARY',
	'ID' => 'INDONESIA',
	'IE' => 'IRELAND',
	'IL' => 'ISRAEL',
	'IM' => 'Isle of Man',
	'IN' => 'INDIA',
	'IO' => 'BRITISH INDIAN OCEAN TERRITORY',
	'IQ' => 'IRAQ',
	'IR' => 'IRAN',
	'IS' => 'ICELAND',
	'IT' => 'ITALY',
	'JE' => 'Jersey',
	'JM' => 'JAMAICA',
	'JO' => 'JORDAN',
	'JP' => 'JAPAN',
	'KE' => 'KENYA',
	'KG' => 'KYRGYZSTAN',
	'KH' => 'CAMBODIA',
	'KI' => 'KIRIBATI',
	'KM' => 'COMORO ISLANDS',
	'KN' => 'ST KITTS & NEVIS',
	'KP' => 'NORTH KOREA',
	'KR' => 'SOUTH KOREA',
	'KW' => 'KUWAIT',
	'KY' => 'CAYMAN ISLANDS',
	'KZ' => 'KAZAKHSTAN',
	'LA' => 'LAOS',
	'LB' => 'LEBANON',
	'LC' => 'ST LUCIA',
	'LI' => 'LIECHTENSTEIN',
	'LK' => 'SRI LANKA',
	'LR' => 'LIBERIA',
	'LS' => 'LESOTHO',
	'LT' => 'LITHUANIA',
	'LU' => 'LUXEMBOURG',
	'LV' => 'LATVIA',
	'LY' => 'LIBYA',
	'MA' => 'MOROCCO',
	'MC' => 'MONACO',
	'MD' => 'MOLDOVA',
	'ME' => 'REPUBLIC OF MONTENEGRO',
	'MF' => 'SAINT MARTIN',
	'MG' => 'MADAGASCAR',
	'MH' => 'MARSHALL ISLANDS',
	'MK' => 'MACEDONIA',
	'ML' => 'MALI',
	'MM' => 'MYANMAR',
	'MN' => 'MONGOLIA',
	'MO' => 'MACAO',
	'MP' => 'NORTHERN MARIANA ISLANDS',
	'MQ' => 'MARTINIQUE',
	'MR' => 'MAURITANIA',
	'MS' => 'MONTSERRAT',
	'MT' => 'MALTA',
	'MU' => 'MAURITIUS',
	'MV' => 'MALDIVES',
	'MW' => 'MALAWI',
	'MX' => 'MEXICO',
	'MY' => 'MALAYSIA',
	'MZ' => 'MOZAMBIQUE',
	'NA' => 'NAMIBIA',
	'NC' => 'NEW CALEDONIA',
	'NE' => 'NIGER',
	'NF' => 'NORFOLK ISLANDS',
	'NG' => 'NIGERIA',
	'NI' => 'NICARAGUA',
	'NL' => 'NETHERLANDS',
	'NO' => 'NORWAY',
	'NP' => 'NEPAL',
	'NR' => 'NAURU',
	'NU' => 'NIUE',
	'NZ' => 'NEW ZEALAND',
	'OM' => 'OMAN',
	'PA' => 'PANAMA',
	'PE' => 'PERU',
	'PF' => 'FRENCH POLYNESIA',
	'PG' => 'PAPUA NEW GUINEA, INDEPENDENT',
	'PH' => 'PHILIPPINES',
	'PK' => 'PAKISTAN',
	'PL' => 'POLAND',
	'PM' => 'ST PIERRE & MIQUELON',
	'PN' => 'PITCAIRN',
	'PR' => 'PUERTO RICO',
	'PS' => 'PALESTINIAN TERRITORY (OCCUPIE',
	'PT' => 'PORTUGAL',
	'PW' => 'PALAU',
	'PY' => 'PARAGUAY',
	'QA' => 'QATAR',
	'RE' => 'REUNION',
	'RO' => 'ROMANIA',
	'RS' => 'REPUBLIC OF SERBIA',
	'RU' => 'RUSSIA',
	'RW' => 'RWANDA',
	'SA' => 'SAUDI ARABIA',
	'SB' => 'SOLOMON ISLANDS',
	'SC' => 'SEYCHELLES',
	'SD' => 'NORTH SUDAN',
	'SE' => 'SWEDEN',
	'SG' => 'SINGAPORE',
	'SH' => 'SAINT HELENA',
	'SI' => 'SLOVENIA',
	'SJ' => 'SVALBARD & JAN MAYEN ISLANDS',
	'SK' => 'SLOVAKIA',
	'SL' => 'SIERRE LEONE',
	'SM' => 'SAN MARINO',
	'SN' => 'SENEGAL',
	'SO' => 'SOMALIA, FEDERAL REPUBLIC OF',
	'SR' => 'SURINAME',
	'SS' => 'SOUTH SUDAN',
	'ST' => 'SAO TOME/PRINCIPE',
	'SV' => 'EL SALVADOR',
	'SX' => 'SINT MAARTEN',
	'SY' => 'SYRIA',
	'SZ' => 'SWAZILAND',
	'TC' => 'TURKS & CAICOS ISLANDS',
	'TD' => 'CHAD',
	'TF' => 'FRENCH SOUTHERN TERRITORIES',
	'TG' => 'TOGO',
	'TH' => 'THAILAND',
	'TJ' => 'TAJIKISTAN',
	'TK' => 'TOKELAU',
	'TL' => 'TIMOR-LESTE',
	'TM' => 'TURKMENISTAN',
	'TN' => 'TUNISIA',
	'TO' => 'TONGA',
	'TR' => 'TURKEY',
	'TT' => 'TRINIDAD/TOBAGO',
	'TV' => 'TUVALU',
	'TW' => 'TAIWAN',
	'TZ' => 'TANZANIA',
	'UA' => 'UKRAINE',
	'UG' => 'UGANDA',
	'UM' => 'UNITED STATES MINOR OUTLYING',
	'US' => 'UNITED STATES OF AMERICA',
	'UY' => 'URUGUAY',
	'UZ' => 'UZBEKISTAN',
	'VA' => 'HOLY SEE (VATICAN CITY STATE)',
	'VC' => 'ST VINCENT & THE GRENADINES',
	'VE' => 'VENEZUELA',
	'VG' => 'BRITISH VIRGIN ISLANDS',
	'VI' => 'US VIRGIN ISLANDS',
	'VN' => 'VIETNAM',
	'VU' => 'VANUATU',
	'WF' => 'WALLIS & FUTUNA ISLANDS',
	'WS' => 'SAMOA',
	'XE' => 'TARGET1 BANKS IN TARGET2',
	'XX' => 'ALL COUNTRIES',
	'YE' => 'YEMEN',
	'YT' => 'MAYOTTE',
	'ZA' => 'SOUTH AFRICA',
	'ZM' => 'ZAMBIA',
	'ZW' => 'ZIMBABWE'
);

?>
