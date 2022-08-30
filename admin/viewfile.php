<?php

include_once("settings.php");
include_once("library.php");
global $denial_message;
global $navi_1_system;
global $navi_2_sys_vold;
global $role_man;
global $USER; # set by admin_verify()

log_open("vilcol.log");

sql_connect();

admin_verify(); # writes to $USER

if ($USER['IS_ENABLED'])
{
	if (role_check('*', $role_man))
	{
		$navi_1_system = true; # settings.php; used by navi_1_heading()
		$navi_2_sys_vold = true; # settings.php; used by navi_2_heading()
		$onload = "onload=\"set_scroll();\"";
		screen_layout();
	}
	else 
		print "<p>$denial_message</p>";
}
else 
	print "<p>" . server_php_self() . ": login is not enabled</p>";
	
sql_disconnect();
log_close();

#===================================================================================================================


function screen_content()
{
	print "<h3>System Administration</h3>";
	navi_2_heading(); # secondary navigation buttons
	print "<h3>Vilcol DFV/DBV/TXT Examiner</h3>";
	
	if (count($_POST) == 0)
	{
		$file_selected = '';
		$convert = 1;
		$pretty = 1;
		$bytes = 1000000;
	}
	else
	{
		$file_selected = post_val('file_selected');
		$convert = (isset($_POST['convert']) ? intval(post_val('convert')) : 0);
		$pretty = (isset($_POST['pretty']) ? intval(post_val('pretty')) : 0);
		$bytes = (isset($_POST['bytes']) ? intval(post_val('bytes')) : 20000);
	}
	
	$code_format_1 = "<span style=\"color:brown\">{";
	$code_format_2 = "}</span>";
	$common_codes = array(
		3 => array("{$code_format_1}EOTEXT{$code_format_2}", "<hr style=\"color:red\">"), # end of text
		4 => array("{$code_format_1}EOTRANS{$code_format_2}", "<span style=\"color:red\">EOTRANS</span>"), # end of transmission
		10 => array("{$code_format_1}LF{$code_format_2}", '<br>'), # line feed
		13 => array("{$code_format_1}CR{$code_format_2}", '<br>'), # carriage return
		);
		
	#------------------------------------------------------------------------------------------------
	
	$dirname = 'files-vilcol'; #/Vilcol Collect';
	$dir = opendir($dirname);
	if (!$dir)
	{
		print "<p>** Could not open '$dirname' directory **</p>";
		exit;
	}
	$filelist = array();
	while (($file = readdir($dir)) != false)
	{
		if (	(strpos(strtolower($file), ".dbf") !== false) ||
				(strpos(strtolower($file), ".dbv") !== false) ||
				(strpos(strtolower($file), ".txt") !== false)
		   )
		{
			$filelist[] = $file;
			if ((!$file_selected) && ($file == 'CLIENTS.DBF'))
				$file_selected = $file;
		}
	}
	sort($filelist);
	closedir($dir);
	
	#------------------------------------------------------------------------------------------------
	
	print "
	<br>
	<form name=\"main_form\" action=\"" . server_php_self() . "\" method=\"post\">
	Please select file: <select name=\"file_selected\">
	";
	foreach ($filelist as $file)
		print "<option value=\"$file\"" . (($file == $file_selected) ? 'selected' : '') . ">$file</option>
				";
	print "
	</select>
	&nbsp;&nbsp;&nbsp;
	Number of bytes to read: <input type=\"textbox\" name=\"bytes\" value=\"$bytes\">
	&nbsp;&nbsp;&nbsp;
	<input type=\"checkbox\" name=\"convert\" value=\"1\" " . ($convert ? 'checked' : '') . ">Convert
	&nbsp;&nbsp;&nbsp;
	<input type=\"checkbox\" name=\"pretty\" value=\"1\" " . ($pretty ? 'checked' : '') . ">Pretty
	&nbsp;&nbsp;&nbsp;
	<input type=\"submit\" value=\"Examine File\">
	</form>
	";
	
	#------------------------------------------------------------------------------------------------
	
	$file = $file_selected; # 'ARCC2005.DBV';
	if (!$file)
		exit;
	//$is_dbf = ((strpos(strtolower($file), ".dbf") !== false) ? true : false);
	
	$fp = fopen("$dirname/$file", 'r');
	if (!$fp)
	{
		print "<p>** Could not open $file **</p>";
		exit;
	}
	
	#------------------------------------------------------------------------------------------------
	
	$buffer = fread($fp, $bytes);
	if (!$buffer)
	{
		print "<p>** Could not read $bytes bytes from $file **</p>";
		exit;
	}
	
	#------------------------------------------------------------------------------------------------
	
	$blen = strlen($buffer);
	$filesize = filesize($file);
	$prev_ascii = true;
	print "
	<p>
	First " . min($filesize, $bytes) . " bytes from $file is shown below. Full size of file is $filesize.<br>
	" . #($is_dbf ? "This is a DBF file.<br>" : '') . "
	"
	<span style=\"color:blue\">Plain text is in blue. </span>
	<span style=\"color:black\">Binary data (ASCII codes) is in black.
	<br>
	If \"Convert\" ticked then common codes are converted to brown name e.g. 13 >> <span style=\"color:brown\">{CR}</span>.</span>
	<br>
	If \"Pretty\" ticked then EOTEXT shown as red line, CR LF shown as line break.
	</p>
	<hr>
	<pre>
	<br><span style=\"color:blue\">
	";
	//if ($is_dbf)
	//{
	//	#$lines = explode($cr, $buffer);
	//	$lines = explode($lf, $buffer);
	//	#$lines = explode($crlf, $buffer);
	//	$line_num = 0;
	//	foreach ($lines as $one_line)
	//	{
	//		print "Line $line_num:<br>";
	//		$fields = explode('	', $one_line);
	//		foreach ($fields as $one_field)
	//		{
	//			print "<span style=\"color:blue\">$one_field<br></span>";
	//		}
	//		$line_num++;
	//	}
	//}
	//else
	//{
		for ($ii = 0; $ii < $blen; $ii++)
		{
			$ch = $buffer[$ii];
			$ascii = ord($ch);
			if ((32 <= $ascii) && ($ascii <= 126))
			{
				if (!$prev_ascii)
					print "</span><br><span style=\"color:blue\">";
				print $ch;
				$prev_ascii = true;
			}
			else 
			{
				if ($prev_ascii)
					print "</span><br>" . ($pretty ? '' : '<span style=\"color:red\">Binary:</span>') . 
							"<span style=\"color:black\">";
				print " ";
				if (($convert || $pretty) && array_key_exists($ascii, $common_codes))
					print $common_codes[$ascii][$pretty ? 1 : 0];
				else
					print $ascii;
				$prev_ascii = false;
			}
		}
	//}
	print "</span>
	<br>
	</pre>
	<hr>";
}

function screen_content_2()
{
	# This is required by screen_layout()
} # screen_content_2()

#------------------------------------------------------------------------------------------------

?>
