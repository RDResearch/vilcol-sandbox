<?php

include_once("settings.php");
include_once("library.php");
global $navi_1_system;
global $USER; # set by admin_verify()

log_open("vilcol.log");

sql_connect();

admin_verify(); # writes to $USER

if ($USER['IS_ENABLED'])
{
	$navi_1_system = true; # settings.php; used by navi_1_heading()
	$onload = "onload=\"set_scroll();\"";
	$page_title_2 = 'System - Vilcol';
	screen_layout();
}
else 
	print "<p>" . server_php_self() . ": login is not enabled</p>";
	
sql_disconnect();
log_close();

function screen_content()
{
	print "<h3>System Administration</h3>";
	navi_2_heading(); # secondary navigation buttons
	javascript();
}

function screen_content_2()
{
	# This is required by screen_layout()
} # screen_content_2()

function javascript()
{
	global $page_title_2;

	print "
	<script type=\"text/javascript\">
	
	document.getElementById('page_title').innerHTML = '$page_title_2';

	</script>
	";
}

?>
