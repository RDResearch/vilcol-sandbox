<?php

include_once("settings.php");
include_once("library.php");
global $navi_1_system;
global $navi_2_sys_feedback;
global $USER; # set by admin_verify()

log_open("vilcol.log");

sql_connect();

admin_verify(); # writes to $USER

if ($USER['IS_ENABLED'])
{
	$navi_1_system = true; # settings.php; used by navi_1_heading()
	$navi_2_sys_feedback = true; # settings.php; used by navi_2_heading()
	$onload = "onload=\"set_scroll();\"";
	$page_title_2 = 'Feedback - Vilcol';
	screen_layout();
}
else 
	print "<p>" . server_php_self() . ": login is not enabled</p>";
	
sql_disconnect();
log_close();

function screen_content()
{
	global $f_text;
	global $f_nature;
	global $f_resolved_tck;
	global $f_response;
	global $feedback_id;
	global $page_title_2;
	global $sc_f_id;
	global $sc_nature;
	global $sc_resolved;
	global $sc_text;
	global $sc_text_andor;
	global $sc_text_words;
	global $super_user_id;
	global $USER;
	
	print "<h3>System Administration</h3>";
	navi_2_heading(); # secondary navigation buttons
	print "<h3>Feedback Module</h3>";
	
	dprint(post_values());
	javascript();
	
	$sc_nature = post_val('sc_nature', true); # search criteria
	$sc_resolved = post_val('sc_resolved'); # search criteria
	if ($sc_resolved == '')
		$sc_resolved = 0;
	else
		$sc_resolved = intval($sc_resolved);
	$sc_f_id = post_val('sc_f_id', true);
	if (0 < $sc_f_id)
		$sc_resolved = -1;
	$sc_text = post_val2('sc_text');
	$sc_text_words = array();
	if ($sc_text)
	{
		$bits = explode(' ', $sc_text);
		foreach ($bits as $one)
		{
			if ($one)
				$sc_text_words[] = $one;
		}
		if (count($sc_text_words) == 0)
			$sc_text = '';
	}
	$sc_text_andor = post_val('sc_text_andor');
	
	$feedback_id = post_val('feedback_id', true);
	$f_text = post_val2('f_text');
	$f_nature = post_val('f_nature', true);
	$f_response = post_val2('f_response');
	$f_resolved_tck = post_val('f_resolved_tck', true);

	$task = post_val('task');
	
	if (($task == 'edit') && ($feedback_id > 0))
		print_edit_feedback();
	else 
	{
		if ($task == 'create')
		{
			$new_id = sql_insert();
			send_mail($new_id, true);
			javascript_alert("Your feedback has been sent");
		}
		elseif ($task == 'update')
		{
			sql_update();
			if ($USER['USER_ID'] != $super_user_id) # don't send mail if updated by KDB
				send_mail($feedback_id, false);
			#javascript_alert("The feedback has been updated");
			#print "<h3>The feedback has been updated</h3>";
		}
		elseif ($task == 'search')
		{
			# Default, no action.
		}
		print_create_and_list();
	}

	print "
	<script type=\"text/javascript\">
	document.getElementById('page_title').innerHTML = '$page_title_2';
	</script>
	";
}

function screen_content_2()
{
	# This is required by screen_layout()
} # screen_content_2()

function javascript()
{
	
	print "
	<script type=\"text/javascript\">
	
	function edit_feedback(feedback_id)
	{
		if (feedback_id > 0)
		{
			document.form_launch_edit.feedback_id.value = feedback_id;
			document.form_launch_edit.task.value = 'edit';
			please_wait_on_submit();
			document.form_launch_edit.submit();
		}
	}
	
	</script>
	";
}

#===================================================================================================================
function print_create_and_list()
{
	global $crlf;
	global $f_nature;
	global $sc_f_id;
	global $sc_nature;
	global $sc_resolved;
	global $sc_text;
	global $sc_text_andor;
	global $sc_text_words;
	global $tr_colour_1;
	global $tr_colour_2;
	
	#dprint("**$sc_resolved**");#
	$view_list = true;
		
	if (global_debug())
	{
	print "
	<form name=\"form_feedback\" method=\"post\" action=\"" . server_php_self() . "\">
	<table name=\"table_feedback\" class=\"spaced_table\" border=\"0\"><!---->
	<tr>
		<td width=\"20%\" style=\"vertical-align:top; font-weight:bold; font-size:12pt; width:268px; height:30px;\">
			Feedback Form
		</td>
	</tr>
	<tr>
		<td style=\"vertical-align:top;\">
			<p><b>Nature:</b></p>
			<select name=\"f_nature\">" . nature_options('submit', $f_nature ? $f_nature : '') . "</select>
		</td>
		<td>
			Please enter your comments here:
			<br>
			<textarea name=\"f_text\" cols=\"70\" rows=\"15\"></textarea>
			<input type=\"hidden\" name=\"task\" value=\"create\">
			<br>
			<input type=\"submit\" value=\"Send email to the Support Team\">
		</td>
	</tr>
	</table><!--table_feedback-->
	</form><!--form_feedback-->
	";
	}
	else
	{
		print "<h3>The add-feedback function is no longer available</h3>";
		print "<a href=\"http://www.rdresearch.co.uk\">Click here to contact Support</a>";
	}
	
	if ($view_list)
	{
		$can_edit = true;
		$notes_width = 350;
		$sc_resolved_all = -1;
		$sc_resolved_no = 0;
		$sc_resolved_yes = 1;
		$gap = 20;
		$sc_no_resp = post_val('sc_no_resp', true);
		
		print "
		<table name=\"table_old_feedback\" class=\"spaced_table\">
		<tr>
		<td colspan=\"2\">
			<h3>Feedback so far (most recent first): <span id=\"items_found\"></span></h3>
			<form name=\"form_list\" action=\"" . server_php_self() . "\" method=\"post\">
			<table class=\"spaced_table\" name=\"table_criteria\">
				<tr>
					<td valign=\"middle\">
						<select name=\"sc_nature\">" .
						nature_options('list', $sc_nature ? $sc_nature : '') . "
						</select>
					</td>
					<td width=\"$gap\">
					</td>
					<td>
						<input type=\"radio\" name=\"sc_resolved\" value=\"$sc_resolved_yes\" " . 
							(($sc_resolved == $sc_resolved_yes) ? 'checked' : '') . ">Fixed&nbsp;&nbsp;&nbsp;
						<input type=\"radio\" name=\"sc_resolved\" value=\"$sc_resolved_no\" " . 
							(($sc_resolved == $sc_resolved_no) ? 'checked' : '') . ">Unfixed&nbsp;&nbsp;&nbsp;
						<input type=\"radio\" name=\"sc_resolved\" value=\"$sc_resolved_all\" " . 
							(($sc_resolved == $sc_resolved_all) ? 'checked' : '') . ">Either
					</td>
					<td width=\"$gap\">
					</td>
					<td>
						Item No.
						<input type=\"text\" name=\"sc_f_id\" value=\"" . ($sc_f_id ? $sc_f_id : '') . "\" size=\"3\" maxlength=\"5\">
					</td>
					<td width=\"$gap\">
					<td>
						Empty Response
						<input type=\"checkbox\" name=\"sc_no_resp\" value=\"1\" " . ($sc_no_resp ? 'checked' : '') . ">
					</td>
					<td width=\"$gap\">
					</td>
					<td>
						Text search
						<input type=\"text\" name=\"sc_text\" value=\"$sc_text\" size=\"20\" maxlength=\"200\">
					</td>
				</tr>
				<tr>
					<td><input type=\"submit\" value=\"Search on criteria\">
					</td>
					<td colspan=\"7\"></td>
					<td><input type=\"radio\" name=\"sc_text_andor\" value=\"and\" " . (($sc_text_andor=='and') ? 'checked' : '') . ">And
						&nbsp;&nbsp;<input type=\"radio\" name=\"sc_text_andor\" value=\"or\" " . (($sc_text_andor=='or') ? 'checked' : '') . ">Or</td>
				</tr>
			</table><!--table_criteria-->
			<input name=\"task\" type=\"hidden\" value=\"search\">
			</form><!--form_list-->
			<table class=\"spaced_table\" name=\"table_list\">
				<tr><th>No.</th><th>Nature</th><th>Fixed</th><th>Reported</th><th>Worker</th>" .
					"<th width=\"$notes_width\">Comments</th><th width=\"$notes_width\">Response</th></tr>";
		$sql = "SELECT F.F_ADDED_DT, U.U_FIRSTNAME, " . sql_decrypt('U.U_LASTNAME') . ", " .
					"F.F_ADDED_ID, F.F_TEXT, " .
					"F.F_NATURE, F.F_RESOLVED_DT, " .
					"REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(F_RESPONSE, CHAR(226), ''), CHAR(157), ''), CHAR(163), '&pound;'), '??', ''''), '?', '''') AS F_RESPONSE, " .
					"F.FEEDBACK_ID " .
					"FROM FEEDBACK F INNER JOIN USERV U ON U.USER_ID=F.F_ADDED_ID " .
					"WHERE (1=1) ";
		$criteria_used = ((($sc_nature > 0) || ($sc_resolved >= 0) || ($sc_f_id > 0) || 
							($sc_no_resp > 0) || $sc_text) ? true : false);
		if ($criteria_used)
		{
			if (0 < $sc_nature)
				$sql .= "AND (F_NATURE = $sc_nature) ";
			if ($sc_resolved == 0)
				$sql .= "AND (F_RESOLVED_DT IS NULL) ";
			elseif ($sc_resolved == 1)
				$sql .= "AND (F_RESOLVED_DT IS NOT NULL) ";
			if ($sc_f_id > 0)
				$sql .= "AND (F.FEEDBACK_ID=$sc_f_id) ";
			if ($sc_no_resp)
				$sql .= "AND ((F.F_RESPONSE IS NULL) OR (F.F_RESPONSE='')) ";
			if ($sc_text)
			{
				if (post_val('text_andor') == 'and')
					$link = 'AND';
				else
					$link = 'OR';
				$subsql_1 = '';
				foreach ($sc_text_words as $one)
					$subsql_1 .= ($subsql_1 ? " $link " : '') . "(F.F_TEXT LIKE '%$one%')";
				$subsql_2 = '';
				foreach ($sc_text_words as $one)
					$subsql_2 .= ($subsql_2 ? " $link " : '') . "(F.F_RESPONSE LIKE '%$one%')";
				$sql .= "AND (($subsql_1) OR ($subsql_2)) ";
			}
		}
		
		$sql .=	"ORDER BY F.F_ADDED_DT DESC, F.FEEDBACK_ID DESC";
		dprint($sql);
		sql_execute($sql);

		$trstyle = ($can_edit ? "style=\"cursor: pointer;\"" : '');
		$none_found = true;
		$items_found = 0;
		while (($newArray = sql_fetch()) != false)
		{
			$f_nature = nature_from_int($newArray[5], false);
			$f_resolved_bool = ($newArray[6] ? true : false);
			$f_resolved_yn = ($f_resolved_bool ? 'Yes' : 'No');
			if ($f_resolved_bool)
				$f_resolved_yn .= "<br>" . str_replace(' ', '<br>', date_for_sql(str_replace('.000','',$newArray[6]), true));
			$f_reported = str_replace(' ', '<br>', date_for_sql(str_replace('.000','',$newArray[0]), true, true, true));
			$f_worker = "$newArray[1]<br>$newArray[2]<br>(ID $newArray[3])";
			$f_comments = str_replace($crlf, '<br>', stripslashes($newArray[4]));
			$f_response = str_replace($crlf, '<br>', stripslashes($newArray[7]));
			
			$lines = explode('<br>', $f_comments);
			foreach ($lines as &$oneline)
			{
				$bits = explode(' ', $oneline);
				$wordmax = 30;
				foreach ($bits as &$onebit)
					if (strlen($onebit) > $wordmax)
					{
						$part1 = substr($onebit,0,$wordmax);
						$part2 = substr($onebit,$wordmax);
						if (strlen($part2) > $wordmax)
						{
							$part2a = substr($part2,0,$wordmax);
							$part2b = substr($part2,$wordmax);
							if (strlen($part2b) > $wordmax)
							{
								$part2b1 = substr($part2b,0,$wordmax);
								$part2b2 = substr($part2b,$wordmax);
								$part2b = $part2b1 . '<br>' . $part2b2;
							}
							$part2 = $part2a . '<br>' . $part2b;
						}
						$onebit = $part1 . '<br>' . $part2;
					}
				$oneline = implode(' ', $bits);
			}
			$f_comments = implode('<br>', $lines);

			$lines = explode('<br>', $f_response);
			foreach ($lines as &$oneline)
			{
				$bits = explode(' ', $oneline);
				$wordmax = 30;
				foreach ($bits as &$onebit)
					if (strlen($onebit) > $wordmax)
					{
						$part1 = substr($onebit,0,$wordmax);
						$part2 = substr($onebit,$wordmax);
						if (strlen($part2) > $wordmax)
						{
							$part2a = substr($part2,0,$wordmax);
							$part2b = substr($part2,$wordmax);
							if (strlen($part2b) > $wordmax)
							{
								$part2b1 = substr($part2b,0,$wordmax);
								$part2b2 = substr($part2b,$wordmax);
								$part2b = $part2b1 . '<br>' . $part2b2;
							}
							$part2 = $part2a . '<br>' . $part2b;
						}
						$onebit = $part1 . '<br>' . $part2;
					}
				$oneline = implode(' ', $bits);
			}
			$f_response = implode('<br>', $lines);

			$f_id = $newArray[8];
			$click = ($can_edit ? "onClick=\"JavaScript:edit_feedback($f_id);\"" . " style=\"cursor: pointer;\"" : '');
			$click .= " valign=\"top\"";
			$trcol = $tr_colour_1;
			print "
				<tr bgcolor=\"$trcol\" $trstyle " .
									"onmouseover=\"this.className='Highlight'\" onmouseout=\"this.className='Normal'\">	
					<td $click>$f_id</td>
					<td $click>$f_nature</td>
					<td $click>$f_resolved_yn</td>
					<td $click>$f_reported</td>
					<td $click>$f_worker</td>
					<td $click>$f_comments</td>
					<td $click><div style=\"max-height:300px; overflow-y:scroll;\">$f_response</div></td>
				</tr>";
			$trcol = (($trcol == $tr_colour_1) ? $tr_colour_2 : $tr_colour_1);
			$none_found = false;
			$items_found++;
		}
				
		print "
			</table><!--table_list-->
			
			<form name=\"form_launch_edit\" action=\"" . server_php_self() . "\" method=\"post\">
				<input type=\"hidden\" name=\"feedback_id\" value=\"\">
				<input type=\"hidden\" name=\"task\" value=\"\">
			</form>
			";
		if ($none_found && $criteria_used)
			print "<p>No records found using that search criteria</p>";
		print "
		</td></tr>
		</table><!--table_old_feedback-->
		
		<script type=\"text/javascript\">
		document.getElementById('items_found').innerHTML = '$items_found';
		</script>
		";
		
	} # if ($view_list)
		
} # print_create_and_list()
	
#===================================================================================================================
function nature_options($source, $sel=-99)
{
	#dprint("**$source,$sel**");#
	# The two functions nature_options() and nature_from_int() should match each other
	$sel_m1 = '';
	$sel_1 = '';
	$sel_2 = '';
	$sel_3 = '';
	$sel_4 = '';
	switch ($sel)
	{
		case -1: $sel_m1 = 'selected'; break;
		case 1: $sel_1 = 'selected'; break;
		case 2: $sel_2 = 'selected'; break;
		case 3: $sel_3 = 'selected'; break;
		case 4: $sel_4 = 'selected'; break;
		default:
			if ($source == 'list')
				$sel_m1 = 'selected';
			elseif ($source == 'submit')
				$sel_3 = 'selected';
			break;
	}
	$any = (($source == 'list') ? "<option value=\"-1\" $sel_m1>" . nature_from_int(-1, true) . "</option>" : '');
	return $any .
		"<option value=\"1\" $sel_1>" . nature_from_int(1, true) . "</option>" .
		"<option value=\"2\" $sel_2>" . nature_from_int(2, true) . "</option>" .
		"<option value=\"3\" $sel_3>" . nature_from_int(3, true) . "</option>" .
		"<option value=\"4\" $sel_4>" . nature_from_int(4, true) . "</option>"
		;
} # nature_options()
	
#===================================================================================================================
function nature_from_int($ni, $long)
{
	# The two functions nature_options() and nature_from_int() should match each other
	switch ($ni)
	{
		case -1:
			return ($long ? "All" : "All");
		case 1:
			return ($long ? "Urgent issue" : "Urgent");
		case 2:
			return ($long ? "Pressing issue" : "Pressing");
		case 3:
			return ($long ? "Important issue" : "Important");
		case 4:
			return ($long ? "Desirable feature or change" : "Desirable");
		default:
			return "Unknown f_nature \"$ni\"";
	}
} # nature_from_int()

#===================================================================================================================
function print_edit_feedback()
{
	global $crlf;
	global $feedback_id;
	
	$sql = "SELECT F.F_ADDED_ID, F.F_ADDED_DT, U.U_FIRSTNAME, " . sql_decrypt('U.U_LASTNAME') . ", F.F_TEXT, " .
			"F.F_NATURE, F.F_RESOLVED_DT, " .
			"REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(F_RESPONSE, CHAR(226), ''), CHAR(157), ''), CHAR(163), '&pound;'), '??', ''''), '?', '''') AS F_RESPONSE " .
			"FROM FEEDBACK F INNER JOIN USERV U ON F.F_ADDED_ID=U.USER_ID " .
			"WHERE FEEDBACK_ID=" . quote_smart($feedback_id);
	sql_execute($sql);
	
	while (($newArray = sql_fetch()) != false)
	{
		$f_nature = nature_from_int($newArray[5], true);
		$f_resolved_dt = date_for_sql(str_replace('.000', '', $newArray[6]), true);
		$f_resolved_yn = ($newArray[6] ? 'checked' : '');
		$f_reported = date_for_sql(str_replace('.000', '', $newArray[1]), true);
		$f_worker = "$newArray[2] $newArray[3] (ID $newArray[0])";
		$f_comments = str_replace($crlf, '<br>', stripslashes($newArray[4]));
		$f_response = str_replace($crlf, '<br>', stripslashes($newArray[7]));
	}

	print "
		<form name=\"form_edit_feedback\" action=\"" . server_php_self() . "\" method=\"post\">
		<table class=\"spaced_table\" name=\"table_edit\">
		<tr><td style=\"vertical-align:middle; font-weight:bold; font-size:12pt; width:268px; height:30px;\" colspan=\"2\">
				Edit Feedback
			</td>
		</tr>
		<tr><td>Feedback ID No.</td><td>$feedback_id</td></tr>
		<tr><td>Nature of Issue</td><td>$f_nature</td></tr>
		<tr><td>Resolved</td><td><input name=\"f_resolved_tck\" type=\"checkbox\" value=\"1\" $f_resolved_yn " . (global_debug() ? '' : 'disabled') . "></td></tr>
		<tr><td>Resolved on</td><td>$f_resolved_dt</td></tr>
		<tr><td>Reported by</td><td>$f_worker</td></tr>
		<tr><td>Reported on</td><td>$f_reported</td></tr>
		<tr><td valign=\"top\">Original Comments</td><td>$f_comments</td></tr>
		<tr><td valign=\"top\">Response so far</td><td>$f_response</td></tr>
		<tr><td valign=\"top\">Response update</td><td>
				<textarea name=\"f_response\" cols=\"50\" rows=\"15\"></textarea></td></tr>
		</table><!--table_edit-->
		<input name=\"feedback_id\" type=\"hidden\" value=\"$feedback_id\">
		<input name=\"task\" type=\"hidden\" value=\"update\">
		";
		if (global_debug())
			print "
		<input type=\"submit\" value=\"Update\">
		";
		print "
		</form><!--form_edit_feedback-->";
} # print_edit_feedback()

#===================================================================================================================
function send_mail($new_id, $creating)
{
	global $f_text;
	global $cr;
	global $crlf;
	global $lf;
	global $f_nature;
	global $f_response;
	global $site_domain;
	global $USER;
	
	$mailto = 'kevin@rdresearch.co.uk';
	$mailcc = '';
	$mailbcc = '';

	dprint("send_mail($new_id): mailto=$mailto, mailbcc=$mailbcc");
		
	$admin_email = "feedback@$site_domain"; # This is the default if user has no email address specified
	$admin_reply = 'noreply@rdresearch.co.uk';
	$sql = "SELECT U_FIRSTNAME, " . sql_decrypt('U_LASTNAME') . ", " . sql_decrypt('U_EMAIL') . " " .
			"FROM USERV WHERE (CLIENT2_ID IS NULL) AND USER_ID=" . $USER['USER_ID'];
	sql_execute($sql);
	while (($newArray = sql_fetch()) != false)
	{
		$user_name = "$newArray[0] $newArray[1] (ID {$USER['USER_ID']})";
		if (email_valid($newArray[2]))
		{
			$admin_email = $newArray[2];
			$admin_reply = $admin_email;
		}
	}

	$recip = $mailto;
	$headers = '';
	if ($mailcc)
		$headers .= "Cc: $mailcc\r\n";
	if ($mailbcc)
		$headers .= "Bcc: $mailbcc\r\n";
	$headers .= "From: $admin_email\r\n";
	$headers .= "Reply-To: $admin_reply\r\n";
	$headers.= "Content-Type: text/html; charset=ISO-8859-1 ";
	$headers .= "MIME-Version: 1.0 "; 
	
	$subject = "Vilcol Feedback #$new_id - " . 
		($creating ? nature_from_int($f_nature, false) : 'Update');
	
	$message = "Feedback (No. #$new_id) on $site_domain<br>\r\n" .
				"from $user_name ($admin_email)<br>\r\n" .
				"Nature: " . nature_from_int($f_nature, true) . 
				"<br>\r\n<br>\r\n" . 
				($creating ? '' : 
					"*THIS IS AN UPDATE TO THE FEEDBACK ITEM*<br>\r\n" . 
					"<br>\r\n") .
				str_replace($cr, '<br>', str_replace($lf, '<br>', str_replace($crlf, '<br>', 
					urldecode($f_text ? $f_text : $f_response))));
	
	dprint("Calling mail('$recip', '$subject', <pre>$message</pre>, '$headers'");
	mail($recip, $subject, $message, $headers);
	
} # send_mail()
	
#===================================================================================================================
function sql_insert()
{
	global $f_text;
	global $f_nature;
	global $USER;

	$time_added = date_for_sql(strftime_rdr("%d/%m/%y %H:%M:%S"));

	$sql = "INSERT INTO FEEDBACK (F_ADDED_ID, F_ADDED_DT, F_TEXT, F_NATURE) VALUES (" .
			$USER['USER_ID'] . ", $time_added, " . quote_smart($f_text) . ", " . quote_smart($f_nature) . ")";
	dprint($sql);
	$new_id = sql_execute($sql); # no need to audit
	return $new_id;
	
} # sql_insert()
	
#===================================================================================================================
function sql_update()
{
	global $feedback_id;
	global $f_resolved_tck;
	global $f_response;
	global $USER;

	$time_str = strftime_rdr("%d/%m/%y %H:%M:%S");
	
	$new_response = '';
	if ($f_response)
	{
		$sql = "SELECT U_FIRSTNAME, " . sql_decrypt('U_LASTNAME') . " FROM USERV WHERE (CLIENT2_ID IS NULL) AND USER_ID=" . $USER['USER_ID'];
		sql_execute($sql);
		$user_name = '';
		while (($newArray = sql_fetch()) != false)
			$user_name = "$newArray[0] $newArray[1] (ID {$USER['USER_ID']})";
		if (!$user_name)
			$user_name = "(User ID {$USER['USER_ID']})";

		$sql = "SELECT F_RESPONSE FROM FEEDBACK WHERE FEEDBACK_ID=" . quote_smart($feedback_id);
		sql_execute($sql);
		while (($newArray = sql_fetch()) != false)
		{
			$old_response = $newArray[0];
			$new_response = ($old_response ? "$old_response<br>" : '');
			$new_response .= "<br>Response by $user_name<br>on $time_str >><br>$f_response<br>";
		}
	}
	
	$sql = "UPDATE FEEDBACK SET F_RESOLVED_DT=" . ($f_resolved_tck ? date_for_sql($time_str) : 'NULL');
	if ($new_response)
		$sql .= ", F_RESPONSE=" . quote_smart(addslashes_kdb($new_response));
	$sql .= " WHERE FEEDBACK_ID=" . quote_smart($feedback_id);
	dprint($sql);
	sql_execute($sql);
	
} # sql_update()


?>
