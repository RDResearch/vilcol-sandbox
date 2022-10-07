<?php

include_once("settings.php");
include_once("library.php");
global $navi_1_home;
global $USER; # set by admin_verify()

log_open("vilcol.log");

sql_connect();

if ((isset($_POST['admin_username'])) && (isset($_POST['admin_password']))) {
    $logged_in = true;
    admin_login();
} else
    $logged_in = false;

admin_verify(); # writes to $USER

if ($USER['IS_ENABLED']) {
    # If the user has just logged in, rather than clicking on the "Home" tab,
    # and we have a record of the last screen that they were on,
    # then go to that screen now.
    # Ideally this would only happen if they had been logged out through inactivity.
    if ($logged_in && $USER['U_LAST_SCREEN']) {
        # This no longer works now that we are under https://www.vilcoldb.com because there is no longer a $_SERVER['HTTP_REFERER'],
        # in fact there is nothing in $_SERVER to say that we came from login.php. --27/09/16.
        #header("Location: $admin_url/{$USER['U_LAST_SCREEN']}");
        #return;
    }

    $navi_1_home = true; # settings.php; used by navi_1_heading()
    $onload = "onload=\"set_scroll();\"";
    screen_layout();
} else
    print "<p>" . server_php_self() . ": login is not enabled</p>";

sql_disconnect();
log_close();

function screen_content()
{
    #global $mysql_server;
//	global $auto_search;
//	global $search_clicked;
//	#global $USER;#
//	global $vlf_clicked;
//	global $vlf_client_dec;
//	global $vlf_client_hex;
//	global $vlf_collect_dec;
//	global $vlf_trace_dec;

    #dprint(post_values());#
    $post2 = array();
    foreach ($_POST as $key => $val) {
        if (($key == 'admin_password') || ($key == 'app_pw'))
            $val = '******';
        $post2[xprint($key, false, 1)] = xprint($val, false, 1);
    }
    dprint("POST = " . print_r($post2, 1));

    if (!user_debug()) {

        return;
    }

}

