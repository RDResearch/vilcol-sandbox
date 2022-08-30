<?php

include_once("settings.php");
include_once("library.php");
global $onkeydown;
global $onload;
global $admin_domain;

if (get_val('page_task') == 'logout')
	set_login_cookie(0);

$onload = "onload=\"document.getElementById('id_admin_username').focus();\""; # used by header.php
$onkeydown = ''; # used by admin_header.php
include_once("header.php");
#print "<p>COOKIE=" . print_r($_COOKIE,1) . "</p>";
$rem = (isset($_COOKIE['r2_remember']) ? xprint($_COOKIE['r2_remember'],false,1) : '');
?>

<SCRIPT LANGUAGE="Javascript">
// Note: navigator.appName returns 'Netscape' for Firefox!!
//if (navigator.appName.indexOf('Microsoft') != -1)
//	alert('This system is best used via Firefox');
var capsState = false;
function capsCheck(event)
{
	var x = event.getModifierState("CapsLock");
	if (x !== capsState)
	{
		capsState = x;
		document.getElementById('capsBox').style.visibility = (capsState ? 'visible' : 'hidden');
	}
}
</SCRIPT>

<TABLE CELLPADDING="4" CELLSPACING="0" BORDER="0" WIDTH="100%" HEIGHT="100%">
<TR>
<TD ALIGN="CENTER" VALIGN="MIDDLE">
	
	<FORM NAME="form_login" ACTION="home.php" METHOD="POST">
	<TABLE CELLPADDING="2" CELLSPACING="0" BORDER="0">
	<TR>
		<TD COLSPAN="2" ALIGN="CENTER">
			<B><FONT ID="eleven"><?php print "$admin_domain" ?></FONT><BR>System Login</B>
			<BR><BR>
		</TD>
	</TR>
	<TR>
	<TD align="right">User Name:</TD>
	<TD><INPUT TYPE="text" NAME="admin_username" SIZE="15" MAXLENGTH="50" ID="id_admin_username" 
		VALUE="<?php print $rem; ?>"></TD>
	<TD WIDTH="200">&nbsp;</TD>
	</TR>

	<TR>
	<TD align="right">User Password:</TD>
	<TD><INPUT TYPE="password" AUTOCOMPLETE="off" NAME="admin_password" ID="admin_password" SIZE="15" MAXLENGTH="20" onkeydown="capsCheck(event)"></TD>
	<TD ROWSPAN="2"><INPUT TYPE="text" ID="capsBox" VALUE="CAPS LOCK is ON" READONLY TABINDEX="99" style="color:red; visibility:hidden;" ></TD>
	</TR>

	<TR>
	<TD align="right">Application Password:</TD>
	<TD><INPUT TYPE="password" AUTOCOMPLETE="off" NAME="app_pw" ID="app_pw" SIZE="15" MAXLENGTH="20" onkeydown="capsCheck(event)"></TD>
	</TR>

	<TR>
	<TD></TD>
	<TD><input type="checkbox" name="remember" value="1" <?php print ($rem ? 'checked' : ''); ?>>Remember Me</TD>
	</TR>

	<TR>
	<TD>&nbsp;</TD>
	<TD><INPUT TYPE="submit" VALUE="Login"></TD>
	</TR>
	</TABLE>
	</FORM>

</TD>
</TR>
</TABLE>

<?php
if ($rem)
	print "
	<script type=\"text/javascript\">
	window.onload = document.getElementById('admin_password').focus();
	</script>
	";
include("footer.php");
?>
