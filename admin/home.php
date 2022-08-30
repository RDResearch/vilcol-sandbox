<?php

include_once("settings.php");
include_once("library.php");
global $navi_1_home;
global $USER; # set by admin_verify()

log_open("vilcol.log");

sql_connect();

if ((isset($_POST['admin_username'])) && (isset($_POST['admin_password'])))
{
	$logged_in = true;
	admin_login();
}
else
	$logged_in = false;

admin_verify(); # writes to $USER

if ($USER['IS_ENABLED'])
{
	# If the user has just logged in, rather than clicking on the "Home" tab,
	# and we have a record of the last screen that they were on,
	# then go to that screen now.
	# Ideally this would only happen if they had been logged out through inactivity.
	if ($logged_in && $USER['U_LAST_SCREEN'])
	{
		# This no longer works now that we are under https://www.vilcoldb.com because there is no longer a $_SERVER['HTTP_REFERER'],
		# in fact there is nothing in $_SERVER to say that we came from login.php. --27/09/16.
		#header("Location: $admin_url/{$USER['U_LAST_SCREEN']}");
		#return;
	}

	$navi_1_home = true; # settings.php; used by navi_1_heading()
	$onload = "onload=\"set_scroll();\"";
	screen_layout();
}
else
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
	foreach ($_POST as $key => $val)
	{
		if (($key == 'admin_password') || ($key == 'app_pw'))
			$val = '******';
		$post2[xprint($key,false,1)] = xprint($val,false,1);
	}
	dprint("POST = " . print_r($post2,1));

	if (!user_debug())
		return;

	#$count = sql_select_single("SELECT COUNT(*) FROM AUDIT");
	#print "Hello<br>Using " . ($mysql_server ? "MySQL" : "MS SQL") . " Server for Database<br>COUNT(AUDIT) = $count";

	//javascript();

	//if (false)
	//{
	//    print "
	//    <form name=\"form_home\" action=\"" . server_php_self() . "\" method=\"post\">
	//        " . input_textbox('test_box', post_val('test_box'), 20, 1000) . "
	//        <br>
	//        <input type=\"Submit\">
	//    </form>
	//    ";
	//}

//	dprint("Outcode = \"" . postcode_outcode(post_val('test_box'),true) . "\"");

//	$dates = array('2000-01-01', '2000-01-31', '2000-02-01', '2000-02-28', '2017-01-31');
//	foreach ($dates as $dt)
//		print "<p>$dt --- " . strftime("%Y-%m-%d", date_add_months_kdb(date_to_epoch($dt), 1)) . "</p>";

//	$em = post_val('test_box');
//	if ($em)
//	{
//		$ev = email_valid($em);
//		dprint("$em is " . ($ev ? "Valid" : "Invalid"));
//	}


//# Spell-Checker
//# See also:
//#	http://php.net/manual/en/function.pspell-new.php
//#	https://github.com/LPology/Javascript-PHP-Spell-Checker
//#	http://www.1001line.net/spell-check/
//#	https://www.tinymce.com/docs/get-started/spell-checking/
//# RS ticket:
//#	https://my.rackspace.com/portal/ticket/detail/160728-07249
//
//	print "
//	<textarea id=\"text_box\" name=\"text_box\" rows=\"20\" cols=\"40\"></textarea>
//	<input type=\"button\" id=\"spellcheck_button\" value=\"Check Spelling\">
//
//	<script>
//	var checker = new sc.SpellChecker({
//		button: 'spellcheck_button', // HTML element that will open the spell checker when clicked
//		textInput: 'text_box', // HTML field containing the text to spell check
//		action: 'spell/spellcheck.php' // URL of the server side script
//		});
//	</script>
//	";

//		$word = post_val('vlf_trace_dec');
//		$ukdict = pspell_new("en", "british", null, null, PSPELL_FAST);
//		if (pspell_check($ukdict, $word))
//			print "\"$word\" is spelt correctly :)";
//		else
//			print "\"$word\" is wrongly spelt :(";

//	return;



//	form_search();
//	if ($search_clicked || $auto_search)
//	{
//		sql_search();
//		search_results();
//	}


//	elseif ($vlf_clicked)
//	{
//		dprint(dprint(post_values());#
//
////		$mons = post_val('vlf_trace_dec', true);
////		$ep = date_add_months_kdb(time(), $mons);
////		dprint("Today (" . time() . ") plus $mons months = ($ep) = " . date_from_epoch(true, $ep, false, true, true));
////		return;
//
//		$offset = 0;
//		if ($vlf_client_dec)
//			$offset = vlf_convert_dec_list($vlf_client_dec);
//			# e.g.
//			# 130 136 138 128 173 224
//			# 242 133 128 128 211 224
//			# 187 139 138 128 217 224
//		elseif ($vlf_client_hex)
//			$offset = vlf_convert_hex_list($vlf_client_hex);
//		elseif ($vlf_collect_dec)
//			$offset = vlf_convert_dec_list($vlf_collect_dec);
//			# e.g.
//			# 219 202 128 128 138 227
//		elseif ($vlf_trace_dec)
//			$offset = vlf_convert_dec_list($vlf_trace_dec);
//		else
//			print "<p>Enter a VLF<p>";
//		if ($offset > 0)
//		{
//			if ($vlf_client_dec || $vlf_client_hex)
//				$file = "files/CLIENTS.DBV";
//			elseif ($vlf_trace_dec)
//				$file = "files/TRACES.DBV";
//			else
//				$file = "files/COLLECT.DBV";
//			print "<p>Offset: $offset ... File: \"$file\"</p>";
//			$fp = fopen($file,"r");
//			fseek($fp, $offset);
//			$readsize = 50000;
//			$buffer = fread($fp, $readsize);
//			# First 6 bytes are size of Pascal TDBVStru structure.
//			$six = "" . ord($buffer[0]) . "," . ord($buffer[1]) . "," . ord($buffer[2]) . "," .
//						ord($buffer[3]) . "," . ord($buffer[4]) . "," . ord($buffer[5]);
//			$len = 0;
//			$len += ord($buffer[0]);
//			$len += (ord($buffer[1]) << 8);
//			$len += (ord($buffer[2]) << 16);
//			$len += (ord($buffer[3]) << 32);
//			#$signature = ord($buffer[4]);
//			#$compression = ord($buffer[5]);
//			print "First six bytes: $six<br>";
//			print "Remainder($len):<br><hr><pre>" . substr($buffer, 6, $len) . "</pre><hr>";
//			#print "Whole buffer($readsize):<hr><pre>$buffer</pre><hr>";
//			fclose($fp);
//		}
//	}

//	#$safe_user = implode('###', $USER);
//	role_check('c', 'dev');
//	role_check('c', 'man');
//	role_check('c', 'sup');
//	role_check('c', 'std');
//	role_check('t', 'dev');
//	role_check('t', 'man');
//	role_check('t', 'sup');
//	role_check('t', 'std');
//	$USER['USER_ID'] = 7;#
//	role_check('c', 'dev');
//	role_check('c', 'man');
//	role_check('c', 'sup');
//	role_check('c', 'std');
//	role_check('t', 'dev');
//	role_check('t', 'man');
//	role_check('t', 'sup');
//	role_check('t', 'std');
//	$USER['USER_ID'] = 8;
//	role_check('c', 'dev');
//	role_check('c', 'man');
//	role_check('c', 'sup');
//	role_check('c', 'std');
//	role_check('t', 'dev');
//	role_check('t', 'man');
//	role_check('t', 'sup');
//	role_check('t', 'std');
//	#$USER = explode('###', $safe_user);
}

function screen_content_2()
{
	# This is required by screen_layout()
} # screen_content_2()

//function form_search()
//{
////	global $auto_search;
////	global $em_dash;
//	global $sc_text;
//	global $search_clicked;
//	global $vlf_clicked;
//	global $vlf_client_dec;
//	global $vlf_client_hex;
//	global $vlf_collect_dec;
//	global $vlf_trace_dec;
//
//	$search_clicked = (post_val('search_clicked', true) ? true : false);
//	$vlf_clicked = (post_val('vlf_clicked', true) ? true : false);
//
//	if ($search_clicked)
//		$sc_text = post_val('sc_text', false, false, false, true);
//	elseif ($vlf_clicked)
//	{
//		$vlf_client_dec = post_val('vlf_client_dec');
//		$vlf_client_hex = '';
//		$vlf_collect_dec = '';
//		$vlf_trace_dec = '';
//		if (!$vlf_client_dec)
//		{
//			$vlf_client_hex = post_val('vlf_client_hex');
//			if (!$vlf_client_hex)
//			{
//				$vlf_collect_dec = post_val('vlf_collect_dec');
//				if (!$vlf_collect_dec)
//					$vlf_trace_dec = post_val('vlf_trace_dec');
//			}
//		}
//	}
//
//	$gap_width = 20;
//
////	#$form_action = $_SERVER['PHP_SELF'];
////	$form_action = "clients.php";# auto-redirection
////	print "
////	<h3>Vilcol System $em_dash Home screen</h3>
////	<form name=\"form_search\" action=\"$form_action\" method=\"post\">
////		" . input_hidden('search_clicked', '1') . "
////	<table name=\"table_search\">
////	<tr>
////		<td>Search for:</td><td>" . input_textbox('sc_text', $sc_text) . "</td>
////		<td style=\"width:{$gap_width}px\"></td>
////	</tr>
////	<tr>
////		<td colspan=\"5\">" . input_button('Search', 'do_search();') . "&nbsp;&nbsp;
////			" .input_button('Clear', 'clear_criteria();') . "
////		</td>
////	</tr>
////	</table><!--table_search-->
////	</form><!--form_search-->
////	";
//
//	if (user_debug())
//	{
//		print "
//		<h3>VLF</h3>
//		<form name=\"form_vlf\" action=\"" . server_php_self() . "\" method=\"post\">
//			" . input_hidden('vlf_clicked', '1') . "
//		<table name=\"table_vlf\">
//		<tr>
//			<td>Clients &ndash; VLF Decimal:</td><td>" . input_textbox('vlf_client_dec', $vlf_client_dec, 30) . "</td>
//			<td style=\"width:{$gap_width}px\"></td>
//		</tr>
//		<tr>
//			<td>Clients &ndash; VLF Hex:</td><td>" . input_textbox('vlf_client_hex', $vlf_client_hex, 30) . "</td>
//			<td style=\"width:{$gap_width}px\"></td>
//		</tr>
//		<tr>
//			<td>Collections &ndash; VLF Decimal:</td><td>" . input_textbox('vlf_collect_dec', $vlf_collect_dec, 30) . "</td>
//			<td style=\"width:{$gap_width}px\"></td>
//		</tr>
//		<tr>
//			<td>Traces &ndash; VLF Decimal:</td><td>" . input_textbox('vlf_trace_dec', $vlf_trace_dec, 30) . "</td>
//			<td style=\"width:{$gap_width}px\"></td>
//		</tr>
//		<tr>
//			<td colspan=\"5\">" . input_button('Convert', 'do_vlf();') . "</td>
//		</tr>
//		</table><!--table_vlf-->
//		</form><!--form_vlf-->
//		";
//	}
//}

function javascript()
{
	print "
	<script type=\"text/javascript\">

//	function do_search()
//	{
//		//document.form_search.search_clicked.value = '1';
//		document.form_search.submit();
//	}
//
//	function do_vlf()
//	{
//		document.form_vlf.submit();
//	}
//
//	function clear_criteria()
//	{
//		document.form_search.sc_text.value = '';
//	}

	</script>
	";
}

//function sql_search()
//{
//	global $criteria_used; # set here
//	global $sc_text;
//
//	$criteria_used = false;
////	$where = array();
//	if ($sc_text)
//	{
////		$where[] = 	"(" . sql_decrypt('A1.LAST_NAME') . " LIKE $sc_text) OR " .
////					"(" . sql_decrypt("S1.ADDR_1") . "LIKE $sc_text) OR " .
////					"(S1.ADDR_2 LIKE $sc_text) OR " .
////					"(S1.ADDR_3 LIKE $sc_text) OR " .
////					"(S1.ADDR_4 LIKE $sc_text) OR " .
////					"(S1.POSTCODE LIKE $sc_text) OR " .
////					"(" . sql_decrypt("CO.CO_NAME") . "LIKE $sc_text)";
//		$criteria_used = true;
//	}
//}

//function search_results()
//{
////	global $criteria_used; # set by sql_search()
////	global $tr_colour_1;
////	global $tr_colour_2;
//
//}

?>
