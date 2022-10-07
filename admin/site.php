<?php

# This should be false on the live server, and true on the local development machine.

//error_reporting(E_ALL ^ E_NOTICE ^ E_DEPRECATED ^ E_WARNING);

$site_local = false; # My Win 10 Fuji PC
$site_live = false; # RDR Linux server (vilcoldb.com)
$site_forge = true; # RDR Forge server

if ($site_local)
{
	$mysql_server = 1;
	$visual_studio = 0;
	//$mysql_server = true; # MySQL server
	//$mysql_server = false; # MS SQL Server
	//$visual_studio = true; # Local PC, runs within Visual Studio
	//$visual_studio = false; # Local PC, runs in browser outside of Visual Studio
}
else
{
	$mysql_server = true; # MySQL server
	#$mysql_server = false; # MS SQL Server
	$visual_studio = false;
}

?>
