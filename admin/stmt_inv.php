<?php

include_once("settings.php");
include_once("library.php");
include_once("lib_pdf.php");

global $denial_message;
global $navi_1_finance;
global $navi_2_fin_stmt;
global $role_man;
global $USER; # set by admin_verify()

$subdir = 'search';

log_open("vilcol.log");

sql_connect();

admin_verify(); # writes to $USER

if ($USER['IS_ENABLED'])
{
	$navi_1_finance = true; # settings.php; used by navi_1_heading()
	$navi_2_fin_stmt = true; # settings.php; used by navi_2_heading()
	$onload = "onload=\"set_scroll();\"";
	$page_title_2 = 'Invoices - Vilcol';
	screen_layout();
}
else 
	print "<p>" . server_php_self() . ": login is not enabled</p>";
	
sql_disconnect();
log_close();

function screen_content()
{
	global $page_title_2;

	print "<h3>Finance</h3>";
	navi_2_heading(); # secondary navigation buttons
	print "<h3>Statement Invoicing</h3>";
	
	dprint(post_values());
	
	print "
		<b>To see clients who need invoicing:</b><br>
			Go to \"Reports\" then \"Fixed Reports\" then<br>
			either: Collect / View Statistics: \"Statement Invoices\"<br>
			or: Trace / View Statistics: \"Statement Invoices\"<br>
			(noting the date criteria on that screen).
			<br><br>
		
		<b>To search for clients with un-invoiced trace/collect jobs:</b><br>
			Go to \"Clients\" and search for<br>
			either: \"Un-invoiced trace statement billing\"
			or: \"Un-invoiced collection payments\".
			<br><br>
			
		<b>To create statement invoices:</b><br>
			Either: start from \"Reports\" as detailed above<br>
			or: start from \"Clients\" as detailed above<br>
			then use the \"Create Trace Statements\" and/or \"Create Collect Statements\" buttons.
			<br><br>
			
		<b>To send out statement invoices:</b><br>
			(After having created the invoices).<br>
			Go to \"Finance\" then \"View Invoices &amp; Receipts\",<br>
			set \"Statements\" to \"Statement\" then click on \"Invoices not sent\".<br>
			This will show all statement invoices that have not been sent yet.<br>
			Tick the \"Send\" tickboxes for the invoices you want to send out,<br>
			then click the \"Send Invoices\" button which will email out the invoices for clients that have email addresses.
			<br><br>
			
		<b>Setup:</b><br>
			Each client has the following attributes:
			<ul style=\"margin:0\">
				<li>Whether they require statement invoices (a tickbox)</li>
				<li>Statement Frequency (a drop-down list of Daily, Weekly, Fortnightly, Monthly, Two-monthly or Quarterly)</li>
				<li>Next Statement Date (when the next invoice should be issued)</li>
				<li>Previous Statement Date (when the last invoices was issued)</li>
			</ul>
			which are set on the \"Edit Client\" screen.<br>
			Also on the \"View/Edit Client\" screen are:<br>
			<ul style=\"margin:0\">
				<li>A summary list of un-invoiced trace billings.</li>
				<li>A summary list of un-invoiced subject payments.</li>
			</ul>
			<br>
		";
	
	print "
	<script type=\"text/javascript\">
	document.getElementById('page_title').innerHTML = '$page_title_2';
	</script>
	";
	
} # screen_content()

function screen_content_2()
{
	# This is required by screen_layout()
} # screen_content_2()

?>


