<?php

include_once("settings.php");
include_once("library.php");
include_once("lib_pdf.php");
include_once("lib_mail.php");

include_once(__DIR__.'/vendor/autoload.php');

use iio\libmergepdf\Merger;
use iio\libmergepdf\Pages;
use Spatie\Async\Pool;

global $denial_message;
global $navi_1_jobs;
global $role_agt;
global $time_tests; # settings.php
global $USER; # set by admin_verify()

$subdir = 'search';

log_open("vilcol.log");

sql_connect();

admin_verify(); # writes to $USER


sql_disconnect();
log_close();

if(isset($_POST['submit']))
{
    mass_print_letters();
}

header('Location: ' . $_SERVER['HTTP_REFERER']);

function mass_print_letters($ticked_jobs, $upload_app=false)
{
    # $ticked_jobs is an array of JOB_LETTER.JOB_LETTER_ID

    global $csv_dir;
    global $job_id; # set here, used by add_collect_letter()
    global $tunnel_ftp_ip;

    $letter_id_list = implode(', ', $ticked_jobs);

    dprint("mass_print_letters($letter_id_list)");


    # --- Delete and Recreate
    # PDF Recreate 28/09/17:
    # From 28/09/17, we need to delete and recreate all the PDFs. Do this in the same way that auto_letter.php does it using add_collect_letter().
    $sql = "SELECT J.J_VILNO, J.J_SEQUENCE, L.JOB_LETTER_ID, L.JL_APPROVED_DT, L.JOB_ID, L.LETTER_TYPE_ID
				FROM JOB_LETTER AS L INNER JOIN JOB AS J ON J.JOB_ID=L.JOB_ID
				WHERE L.JOB_LETTER_ID IN ($letter_id_list)
				";
    sql_execute($sql);
    $letters = array();
    while (($newArray = sql_fetch_assoc()) != false)
    {
        $dt0 = str_replace(' ', '_', str_replace(':', '', str_replace('-', '', str_replace('.000', '', $newArray['JL_APPROVED_DT']))));
        $newArray['FILENAME'] = "$csv_dir/v{$newArray['J_VILNO']}/" .
            "letter_{$newArray['J_VILNO']}_{$newArray['J_SEQUENCE']}_{$newArray['JOB_LETTER_ID']}_{$dt0}.pdf";
        $letters[] = $newArray;
    }
    $letter_count = count($letters);
    for ($lix = 0; $lix < $letter_count; $lix++)
    {
        $job_letter_id = $letters[$lix]['JOB_LETTER_ID']; # used by add_collect_letter()
        $job_id = $letters[$lix]['JOB_ID']; # used by add_collect_letter()
        $letter_type_id = $letters[$lix]['LETTER_TYPE_ID']; # used by add_collect_letter()

        # Delete existing PDF
        if (file_exists($letters[$lix]['FILENAME']))
        {
            #dprint("Deleting \"{$letters[$lix]['FILENAME']}\"...");#
            unlink($letters[$lix]['FILENAME']);
        }

        # Recreate PDF
        add_collect_letter($letter_type_id, $job_letter_id); # also uses global $job_id

    } # for ($lix)
    # --- Delete and Recreate


    # Now start again

    $sql = "SELECT J.J_VILNO, J.J_SEQUENCE, L.JOB_LETTER_ID, L.JL_APPROVED_DT, L.JL_CREATED_DT
			FROM JOB_LETTER AS L INNER JOIN JOB AS J ON J.JOB_ID=L.JOB_ID
			WHERE L.JOB_LETTER_ID IN ($letter_id_list)
			";
    sql_execute($sql);
    $letters = array();
    $pdfs = array();
    while (($newArray = sql_fetch_assoc()) != false)
    {
        $dt1 = ($newArray['JL_CREATED_DT'] ? $newArray['JL_CREATED_DT'] : $newArray['JL_APPROVED_DT']);
        $dt2 = str_replace(' ', '_', str_replace(':', '', str_replace('-', '', str_replace('.000', '', $dt1))));
        $newArray['FILENAME'] = "$csv_dir/v{$newArray['J_VILNO']}/" .
            "letter_{$newArray['J_VILNO']}_{$newArray['J_SEQUENCE']}_{$newArray['JOB_LETTER_ID']}_{$dt2}.pdf";
        $letters[] = $newArray;
        $pdfs[] = $newArray['FILENAME'];
    }

    //FIXME - MERGE HERE

    log_open("vilcol.log");
    log_write("Begin merge");

    error_log("Starting merge");

    $date = new DateTime();
    $current_time = $date->format('Y-m-dTH-i-s');

    $file_name = "massprint/massPrint-".(string)$current_time;

    // asynchronus merging
    $pool = Pool::create();

    $count = 0;
    foreach (array_chunk($pdfs, 100) as $key=>$pdf_chunk){
        log_write("Merging ".$count);
        $pool->add(function () use ($pdf_chunk, $key, $file_name, $count){
            $merger = new Merger;

            foreach($pdf_chunk as $pdf){
                $merger->addFile($pdf);
            }

            $createdPdf = $merger->merge();

            // append count amount
            $file_name .= "-".$count."-".($count+100).".pdf";

            $myfile = fopen($file_name, "wb");
            $txt = $createdPdf;
            fwrite($myfile, $txt);
            fclose($myfile);

        })->then(function($output){
            echo ('Merge completed');
        });
        $count = $count + 100;
    }

    //	$pool->wait();

    return;

    log_write('Created merges');

    // $dprint = "Letters:<br>";
    // $letter_ix = 0;
    // foreach ($letters as $one_ltr)
    // {
    // 	$dprint .= str_replace("[FILENAME]", "<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;[FILENAME]", print_r($one_ltr,1) . "<br>");
    // 	$dprint .= "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;=={$pdfs[$letter_ix]}<br>";
    // 	$letter_ix++;
    // }
    // dprint($dprint);

//	log_write('Creating button');
//
    // Link works - was a typo in the url
    print("
	<span>Mass printer - merged $count letters into 1 PDF</span>
	<form target=\"_blank\" action=\"https://vilcoldbl.com/admin/$file_name\">
		<input type=\"submit\" value=\"Click to open\" />
	</form>
	");
//
//	log_write('Button made');


// //	$server_names = array('ftp.village.com', '169.0.0.5', '81.5.144.205');
// //	$ftp_server = $server_names[1];
// 	$ftp_server = $tunnel_ftp_ip; #'169.0.0.5';
// 	// $ftp_user_name = "kevin"; FIX ME - uncommented to stop ftp
// 	// $ftp_user_pass = "D0omBar#14"; - uncommented to stop ftp
// //	$ssl_ftp = false;
// 	$ftp_log_debug = false;#

// //	dlog("Connecting to \"$ftp_server\" (SSL:" . ($ssl_ftp ? 'yes' : 'no') . ") ...");
// 	dlog("Connecting to \"$ftp_server\" (SSL:yes) ...");
// //	if ($ssl_ftp)
// //		$conn_id = ftp_ssl_connect($ftp_server);
// //	else
// 		$conn_id = ftp_connect($ftp_server);
// 	if ($conn_id)
// 	{
// 		dlog("...connected, conn_id=$conn_id");

// 		dlog("Logging in as user \"$ftp_user_name\" (with password too)...");
// 		$login_result = ftp_login($conn_id, $ftp_user_name, $ftp_user_pass);
// 		if ($login_result)
// 		{
// 			dlog("...logged in, login_result=$login_result");
// //			if ($ssl_ftp)
// //			{
// //				dlog("Setting passive mode ON...");
// //				ftp_pasv($conn_id, true); # needed for SSL FTP
// //			}
// //			else
// //			{
// 				dlog("Setting passive mode OFF...");
// 				ftp_pasv($conn_id, 0);
// //			}
// 			$debug_max = 0;#
// 			$upload_count = 0;
// 			foreach ($pdfs as $one_pdf)
// 			{
// 				$uploaded = false;
// 				if ((!$debug_max) || ($upload_count < $debug_max))
// 				{
// 					$source_file = $one_pdf;
// 					$pos = strrpos($one_pdf, "/");
// 					if (($pos !== false) && ($pos < strlen($one_pdf)))
// 						$destination_file = substr($one_pdf, $pos+1);
// 					else
// 						$destination_file = $one_pdf;
// 					if ($ftp_log_debug)
// 					{
// 						$msg = "Uploading \"$source_file\" to \"$destination_file\"...";
// 						if ($upload_count < 10)
// 							dlog($msg);
// 						else
// 							dprint($msg);
// 					}
// 					$upload = ftp_put($conn_id, $destination_file, $source_file, FTP_BINARY);
// 					if ($upload)
// 					{
// 						$msg = "...uploaded $source_file OK (count=" . ($upload_count+1) . ")";
// 						$uploaded = true;
// 					}
// 					else
// 						$msg = "...upload failed";
// 					if ($ftp_log_debug)
// 					{
// 						if ($upload_count < 10)
// 							dlog($msg);
// 						else
// 							dprint($msg);
// 					}
// 				}
// 				if ($uploaded)
// 					$upload_count++;
// 			}
// 			$msg = "$upload_count PDF files have been uploaded to the Vilcol print server";
// 			log_write($msg);
// 			dprint($msg, true, 'blue');

// 			if (global_debug() && $upload_app)
// 			{
// 				log_write("Uploading new app...");
// 				$upload = ftp_put($conn_id, "app.zip", "csvex/app.zip", FTP_BINARY);
// 				if ($upload)
// 				{
// 					$msg = "...uploaded new app OK";
// 					$uploaded = true;
// 				}
// 				else
// 					$msg = "...upload of new app failed";
// 				log_write($msg);
// 			}
// 		}
// 		else
// 			dlog("...login failed");

// 		dlog("...closing connection");
// 		ftp_close($conn_id);
// 	}
// 	else
// 	{
// 		dprint("*** CONNECTION TO VILCOL FTP SERVER ($ftp_server) HAS FAILED ***", true);
// 		dlog("...connection failed");
// 	}

} # mass_print_letters())