<?php

global $script_debug; # settings.php
global $customer_ip; # ip_address.php
global $customer_ip_2; # ip_address.php

#print "<pre>" . print_r($_SERVER,1) . "</pre>";#

# All scripts that are part of the system must be listed in this array.
$allowed_scripts = array(
'audit.php',
'auto_letter.php',
'auto_pdf.php',
'auto_portal.php',
'bulkact.php',
'bulkaddr.php',
'bulkclient.php',
'bulkdata8.php',
'bulkjobs.php',
'bulknotes.php',
'bulknotesdated.php',
'bulkpay.php',
'bulkreset.php',
'bulkstatus.php',
'bulkuser.php',
'clients.php',
'clients_ajax.php',
'csv_dl.php',
'feedback.php',
'finance.php',
'home.php',
'general.php',
'import.php',
'jobs.php',
'jobs_ajax.php',
'ledger.php',
'ledger_ajax.php',
'login.php',
'mailres.php',
'port.php',
'portal_ajax.php',
'purge.php',
'receipts.php',
'reports.php',
'reports_ajax.php',
'reports_custom.php',
'standing.php',
'standing_ajax.php',
'stmt_inv.php',
'summaries.php',
'system.php',
'users.php',
'users_ajax.php',
'users_portal.php',
'viewfile.php'
);

# Direct scripts are ones that the user can type into the URL bar in their browser.
$allowed_direct_scripts = array(
'auto_letter.php',
'home.php',
'login.php',
'port.php',
);

# For some reason I can't work out,
# when reports_custom.php (in Edit Report mode) is refreshed with F5,
# the HTTP_REFERER is blank,
# so we need to make a special case for this.
$allowed_nosource_scripts = array(
'reports_custom.php',
);

# Define allowed server and client IP addresses.
# Note: from Jan 2016, the Win7 PC is now a Win10 PC!
$localhost = '127.0.0.1'; # Jan 2016
#$localhost_server = '127.0.0.1'; # Jan 2016
#$localhost_lan = '127.0.0.*'; # Jan 2016
$apache2019 = '::1'; # Both SERVER_ADDR and REMOTE_ADDR on my local PC when upgraded Apache 2.4 on 05/03/19.
$kdb_server_phoe = '192.168.0.2'; # Internal IP address of Win7 server for local PCs: the Phoe Win7 PC, Nov 2015
$kdb_server_vivo = '192.168.0.10'; # Internal IP address of Win10 server for local PCs: the Vivo Win10 PC, Jan 2017
#$kdb_client_fuji = '192.168.0.3'; # Internal IP address of the Fuji XP client PC, Nov 2015
$kdb_client_lan = '192.168.0.*'; # Internal IP address of PCs on KDB's LAN, Nov 2015
#$rdr_server = '172.24.16.137'; # Internal IP address of RDR server for remote PCs, Nov 2015
$rdr_server = '172.24.16.141'; # Internal IP address of RDR server for remote PCs, 31/08/19
$rdr_server_ded = '172.24.16.140'; # Internal IP address (dedicated) of RDR server for remote PCs, 23/06/17
$rdr_client_kdb = $ip_rdr_kdb; # External IP address of KDB's LAN
#$rdr_client_kdb_n = '51.7.174.83'; # External IP address of KDB's "N" office LAN, 11/01/17
#$rdr_client_richard = '86.174.193.240'; # External IP address of Richard Bransden's LAN, 29/03/17
#$rdr_client_andy = '86.149.105.93'; # External IP address of Andy Fisher's LAN, 18/12/15
$rdr_client_rs = '94.236.7.190'; # For testing by Rackspace staff
$vil_server = '...'; # Internal IP address of Vilcol server for local and remote PCs, dd/mm/yy
$vil_client_kdb = $rdr_client_kdb; # External IP address of KDB's LAN, dd/mm/yy
#$vil_client_andy = $rdr_client_andy; # External IP address of Andy Fisher's LAN, dd/mm/yy
$vil_client_lan = '...*'; # Internal IP address of PC on Vilcol's LAN, dd/mm/yy
$rdr_server_forge = '10.0.1.53'; # Forge server, internal IP address, from 30/08/22
$vil_client_oak = '195.224.171.194'; # Oak Lodge from 30/08/22

$allowed_ips = array(	# $_SERVER['SERVER_ADDR'], $_SERVER['REMOTE_ADDR']
						array($localhost, $localhost),
						#array($localhost_server, $localhost_lan),
						array($apache2019, $apache2019),
						array('', $apache2019), # for Visual Studio

						#array($kdb_server_phoe, $kdb_client_fuji),
						#array($kdb_server_phoe, $kdb_client_lan),

						array($kdb_server_vivo, $kdb_client_lan),

						array($rdr_server, $rdr_client_kdb),
						#array($rdr_server, $rdr_client_kdb_n),
						#array($rdr_server, $rdr_client_richard),
						#array($rdr_server, $rdr_client_andy),
						array($rdr_server, $customer_ip),
						array($rdr_server, $customer_ip_2),
						array($rdr_server, $rdr_client_rs),

						array($rdr_server_ded, $rdr_client_kdb),
						#array($rdr_server_ded, $rdr_client_richard),
						array($rdr_server_ded, $customer_ip),
						array($rdr_server_ded, $customer_ip_2),
						array($rdr_server_ded, $rdr_client_rs),

						#array($vil_server, $vil_client_kdb),
						#array($vil_server, $vil_client_andy),
						#array($vil_server, $vil_client_lan),
						
						array($rdr_server_forge, $rdr_client_kdb),
						array($rdr_server_forge, $vil_client_oak),
						array($rdr_server_forge, '82.47.38.95'),
					);

function script_name_2($a) # this is a copy of library.php/script_name()
{
	if ($a == '')
		return 'noScript';
	$pos = strrpos($a, '\\');
	if ($pos !== false)
	{
		$bits = explode('\\', $a);
		$a = $bits[count($bits)-1];
	}
	$pos = strrpos($a, '/');
	if ($pos === false)
		$pos = 0;
	else
		$pos++;
	$b = substr($a, $pos);
	$pos = strpos($b, '?');
	if ($pos !== false)
		$b = substr($b, 0, $pos);
	return strtolower($b);
}

if ($script_debug)
{
	print "<pre>HTTP_REFERER=\"{$_SERVER['HTTP_REFERER']}\"</pre>";
	print "<pre>HTTPS_REFERER=\"{$_SERVER['HTTPS_REFERER']}\"</pre>";
}
$source = ((in_array('HTTP_REFERER', $_SERVER) || isset($_SERVER['HTTP_REFERER'])) ?
														script_name_2($_SERVER['HTTP_REFERER']) : 'noRef');
if ($script_debug)
	print "<pre>\$source=\"$source\"</pre>";

if ($script_debug)
	print "<pre>SCRIPT_FILENAME=\"{$_SERVER['SCRIPT_FILENAME']}\"
			<br>REQUEST_URI=\"{$_SERVER['REQUEST_URI']}\"
			<br>SCRIPT_NAME=\"{$_SERVER['SCRIPT_NAME']}\"
			<br>PHP_SELF=\"{$_SERVER['PHP_SELF']}\"</pre>";

$dest_1 = script_name_2($_SERVER['SCRIPT_FILENAME']);
$dest_2 = script_name_2($_SERVER['REQUEST_URI']);
$dest_3 = script_name_2($_SERVER['SCRIPT_NAME']);
$dest_4 = script_name_2($_SERVER['PHP_SELF']);
if ($script_debug)
	print "<pre>\$dest_1/2/3/4=\"$dest_1\",\"$dest_2\",\"$dest_3\",\"$dest_4\"</pre>";

$script_check = false; # our caller tests this
$in_allowed_scripts =
	(
		in_array($source, $allowed_scripts) &&
		in_array($dest_1, $allowed_scripts) && in_array($dest_2, $allowed_scripts) &&
		in_array($dest_3, $allowed_scripts) && in_array($dest_4, $allowed_scripts)
	);
$in_allowed_direct_scripts =
	(
		(($source == 'noScript') || ($source == 'noRef')) &&
		in_array($dest_1, $allowed_direct_scripts) && in_array($dest_2, $allowed_direct_scripts) &&
		in_array($dest_3, $allowed_direct_scripts) && in_array($dest_4, $allowed_direct_scripts)
	);
$in_allowed_nosource_scripts =
	(
		($source == 'noScript') &&
		in_array($dest_1, $allowed_nosource_scripts) && in_array($dest_2, $allowed_nosource_scripts) &&
		in_array($dest_3, $allowed_nosource_scripts) && in_array($dest_4, $allowed_nosource_scripts)
	);
if ($in_allowed_scripts || $in_allowed_direct_scripts || $in_allowed_nosource_scripts)
{
	if ($script_debug)
	{
		if ($in_allowed_scripts)
			print "<p>In allowed_scripts</p>";
		if ($in_allowed_direct_scripts)
			print "<p>In allowed_direct_scripts</p>";
		if ($in_allowed_nosource_scripts)
			print "<p>In allowed_nosource_scripts</p>";
		print "<pre>SERVER_ADDR=\"{$_SERVER['SERVER_ADDR']}\", REMOTE_ADDR=\"{$_SERVER['REMOTE_ADDR']}\"</pre>";
	}

	foreach ($allowed_ips as $pair)
	{
		if ($pair[0] == $_SERVER['SERVER_ADDR'])
		{
			$pair_1 = explode('.', $pair[1]);
			if ((4 <= count($pair_1)) && ($pair_1[3] == '*'))
			{
				$client = explode('.', $_SERVER['REMOTE_ADDR']);
				if (($client[0] == $pair_1[0]) && ($client[1] == $pair_1[1]) && ($client[2] == $pair_1[2]))
					$script_check = true;
			}
			elseif ($pair[1] == $_SERVER['REMOTE_ADDR'])
				$script_check = true;
		}
		if ($script_debug)
		{
			if ($script_check)
				print "<p>User IP pair ({$_SERVER['SERVER_ADDR']},{$_SERVER['REMOTE_ADDR']}) matches allowed pair ({$pair[0]},{$pair[1]})</p>";
			else
				print "<p>User IP pair ({$_SERVER['SERVER_ADDR']},{$_SERVER['REMOTE_ADDR']}) not a match for allowed pair ({$pair[0]},{$pair[1]})</p>";
		}
		if ($script_check)
			break;
	}
	if (!$script_check)
	{
		if (($_SERVER['DOCUMENT_ROOT'] == 'C:/Apache24/htdocs') && ($_SERVER['SERVER_ADMIN'] == 'admin@example.com'))
		{
			# This is Vivo PC
			$script_check = true;
			if ($script_debug)
				print "<p>Vivo PC detected</p>";
		}
	}
}
elseif ($script_debug)
{
	if (!$in_allowed_scripts)
		print "<p>$source, $dest_1, $dest_2, $dest_3, $dest_4 not in allowed_scripts<br>" . print_r($allowed_scripts,1) . "</p>";
	if (!$in_allowed_direct_scripts)
		print "<p>$source, $dest_1, $dest_2, $dest_3, $dest_4 not in allowed_direct_scripts<br>" . print_r($allowed_direct_scripts,1) . "</p>";
	if (!$in_allowed_nosource_scripts)
		print "<p>$source, $dest_1, $dest_2, $dest_3, $dest_4 not in allowed_nosource_scripts<br>" . print_r($allowed_nosource_scripts,1) . "</p>";
}

if ($script_debug)
	print "<p>The man from Del Monte says: " . ($script_check ? 'Yes' : 'No') . "</p>";

?>
