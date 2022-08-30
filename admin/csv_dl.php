<?php

# Used by reports.php 21/01/16

include_once("settings.php");
include_once("library.php");

#log_open("download.log");

sql_connect();
#log_write("Connected to SQL");

admin_verify();

if ($USER['IS_ENABLED'])
{
	#log_write("Logged in");
	if (($_POST['full_fname']) && ($_POST['short_fname']))
	{
		#log_write("Filenames found in POST: '" . post_val('short_fname') . "' and '" . post_val('full_fname') . "'");
		header('Content-type: application/download');
		header("Content-Disposition: attachment; filename=" . post_val2('short_fname'));
		#log_write("calling readfile('" . post_val('full_fname') . "')");
		ob_end_flush();
		$rfrc = readfile(post_val2('full_fname'));
		#log_write("readfile('" . post_val('full_fname') . "') returned '$rfrc'");
		#if ($rfrc === false)
		#	log_write("Failed to download " . post_val('full_fname') . "");
		#elseif ($rfrc == 0)
		#	log_write("Download of '" . post_val('full_fname') . "' returned zero bytes");
	}
	#else
	#	log_write("No file specified");
}
#else
#	log_write("No Login");

sql_disconnect();

#log_close();
	
?>
