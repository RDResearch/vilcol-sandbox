<?php

include_once("settings.php");
include_once("library.php");
global $navi_1_finance;
global $USER; # set by admin_verify()

log_open("vilcol.log");

sql_connect();

admin_verify(); # writes to $USER

if ($USER['IS_ENABLED'])
{
	if (role_check('a', $role_man))
	{
		$navi_1_finance = true; # settings.php; used by navi_1_heading()
		$onload = "onload=\"set_scroll();\"";
		$page_title_2 = 'Finance - Vilcol';
		screen_layout();
	}
	else 
		print "<p>Sorry, you do not have access to this screen.";
}
else 
	print "<p>" . server_php_self() . ": login is not enabled</p>";
	
sql_disconnect();
log_close();

function screen_content()
{
	print "<h3>Finance</h3>";
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
